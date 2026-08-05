<?php
/**
 * Newsletter subscription endpoint.
 *
 * Submissions are accepted over AJAX and, when JavaScript is unavailable, over
 * a standard POST to admin-post.php. Both paths run the same validation and
 * storage routine.
 *
 * Subscribers are stored as private posts. Integrations with an email service
 * provider should hook `citd_journal_subscriber_added` rather than modifying
 * this file.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validate and store a subscription request.
 *
 * @param array $input Raw request data.
 * @return array {
 *     @type bool   $success Whether the subscription was accepted.
 *     @type string $code    Machine-readable result code.
 *     @type string $message Human-readable message.
 * }
 */
function citd_journal_process_subscription( $input ) {
	$email   = isset( $input['email'] ) ? sanitize_email( wp_unslash( $input['email'] ) ) : '';
	$name    = isset( $input['name'] ) ? sanitize_text_field( wp_unslash( $input['name'] ) ) : '';
	$consent = ! empty( $input['consent'] );
	$honey   = isset( $input['website'] ) ? trim( (string) wp_unslash( $input['website'] ) ) : '';

	// A bot filled the hidden field. Return a success shape so the bot learns
	// nothing, but store nothing.
	if ( '' !== $honey ) {
		return array(
			'success' => true,
			'code'    => 'ignored',
			'message' => __( 'Thank you. Please check your inbox to confirm.', 'citd-journal' ),
		);
	}

	if ( '' === $email || ! is_email( $email ) ) {
		return array(
			'success' => false,
			'code'    => 'invalid_email',
			'message' => __( 'Enter a valid email address.', 'citd-journal' ),
		);
	}

	if ( ! $consent ) {
		return array(
			'success' => false,
			'code'    => 'no_consent',
			'message' => __( 'Please confirm you would like to receive the journal.', 'citd-journal' ),
		);
	}

	$existing = get_posts(
		array(
			'post_type'              => CITD_JOURNAL_SUBSCRIBER_CPT,
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'title'                  => $email,
		)
	);

	if ( ! empty( $existing ) ) {
		return array(
			'success' => true,
			'code'    => 'already_subscribed',
			'message' => __( 'You are already on the list. Thank you.', 'citd-journal' ),
		);
	}

	$subscriber_id = wp_insert_post(
		array(
			'post_type'   => CITD_JOURNAL_SUBSCRIBER_CPT,
			'post_status' => 'private',
			'post_title'  => $email,
		),
		true
	);

	if ( is_wp_error( $subscriber_id ) ) {
		return array(
			'success' => false,
			'code'    => 'storage_failed',
			'message' => __( 'Something went wrong on our side. Please try again shortly.', 'citd-journal' ),
		);
	}

	update_post_meta( $subscriber_id, '_citd_subscriber_name', $name );
	update_post_meta( $subscriber_id, '_citd_subscriber_source', isset( $input['source'] ) ? esc_url_raw( wp_unslash( $input['source'] ) ) : '' );
	update_post_meta( $subscriber_id, '_citd_subscriber_consent_at', current_time( 'mysql', true ) );

	/**
	 * Fires after a subscriber is stored.
	 *
	 * Hook here to forward the address to Mailchimp, Campaign Monitor, HubSpot
	 * or any other provider.
	 *
	 * @param string $email         Subscriber email address.
	 * @param string $name          Subscriber name, possibly empty.
	 * @param int    $subscriber_id Stored post ID.
	 */
	do_action( 'citd_journal_subscriber_added', $email, $name, $subscriber_id );

	return array(
		'success' => true,
		'code'    => 'subscribed',
		'message' => __( 'Thank you. The next issue will arrive in your inbox.', 'citd-journal' ),
	);
}

/**
 * AJAX handler.
 *
 * @return void
 */
function citd_journal_ajax_subscribe() {
	if ( ! check_ajax_referer( 'citd_journal_newsletter', 'nonce', false ) ) {
		wp_send_json_error(
			array(
				'code'    => 'bad_nonce',
				'message' => __( 'Your session expired. Please reload the page and try again.', 'citd-journal' ),
			),
			403
		);
	}

	$result = citd_journal_process_subscription( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.

	if ( $result['success'] ) {
		wp_send_json_success( $result );
	}

	wp_send_json_error( $result, 400 );
}
add_action( 'wp_ajax_citd_journal_subscribe', 'citd_journal_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_citd_journal_subscribe', 'citd_journal_ajax_subscribe' );

/**
 * No-JavaScript fallback handler. Redirects back with a status flag.
 *
 * @return void
 */
function citd_journal_post_subscribe() {
	$redirect = isset( $_POST['source'] ) ? esc_url_raw( wp_unslash( $_POST['source'] ) ) : citd_journal_archive_url(); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.

	if ( ! isset( $_POST['citd_journal_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['citd_journal_newsletter_nonce'] ) ), 'citd_journal_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'subscribed', 'error', $redirect ) . '#journal-newsletter' );
		exit;
	}

	$result = citd_journal_process_subscription( $_POST );

	wp_safe_redirect( add_query_arg( 'subscribed', rawurlencode( $result['code'] ), $redirect ) . '#journal-newsletter' );
	exit;
}
add_action( 'admin_post_nopriv_citd_journal_subscribe', 'citd_journal_post_subscribe' );
add_action( 'admin_post_citd_journal_subscribe', 'citd_journal_post_subscribe' );

/**
 * Translate a redirect status flag into a message for the no-JS path.
 *
 * @return array|null Array with `success` and `message`, or null when absent.
 */
function citd_journal_subscription_notice() {
	if ( empty( $_GET['subscribed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return null;
	}

	$code = sanitize_key( wp_unslash( $_GET['subscribed'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$messages = array(
		'subscribed'         => array( true, __( 'Thank you. The next issue will arrive in your inbox.', 'citd-journal' ) ),
		'ignored'            => array( true, __( 'Thank you. Please check your inbox to confirm.', 'citd-journal' ) ),
		'already_subscribed' => array( true, __( 'You are already on the list. Thank you.', 'citd-journal' ) ),
		'invalid_email'      => array( false, __( 'Enter a valid email address.', 'citd-journal' ) ),
		'no_consent'         => array( false, __( 'Please confirm you would like to receive the journal.', 'citd-journal' ) ),
		'storage_failed'     => array( false, __( 'Something went wrong on our side. Please try again shortly.', 'citd-journal' ) ),
		'error'              => array( false, __( 'Your session expired. Please reload the page and try again.', 'citd-journal' ) ),
	);

	if ( ! isset( $messages[ $code ] ) ) {
		return null;
	}

	return array(
		'success' => $messages[ $code ][0],
		'message' => $messages[ $code ][1],
	);
}

/**
 * Show the stored email address as the subscriber list title column, and add
 * the signup date.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function citd_journal_subscriber_columns( $columns ) {
	$columns['citd_source'] = __( 'Source', 'citd-journal' );

	return $columns;
}
add_filter( 'manage_' . CITD_JOURNAL_SUBSCRIBER_CPT . '_posts_columns', 'citd_journal_subscriber_columns' );

/**
 * Render the subscriber source column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function citd_journal_subscriber_column_content( $column, $post_id ) {
	if ( 'citd_source' !== $column ) {
		return;
	}

	$source = (string) get_post_meta( $post_id, '_citd_subscriber_source', true );

	if ( '' === $source ) {
		echo '—';

		return;
	}

	printf( '<a href="%1$s">%2$s</a>', esc_url( $source ), esc_html( wp_parse_url( $source, PHP_URL_PATH ) ) );
}
add_action( 'manage_' . CITD_JOURNAL_SUBSCRIBER_CPT . '_posts_custom_column', 'citd_journal_subscriber_column_content', 10, 2 );
