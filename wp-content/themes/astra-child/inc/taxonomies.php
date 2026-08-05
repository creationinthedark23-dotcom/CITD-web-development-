<?php
/**
 * Journal taxonomies.
 *
 * journal_topic — hierarchical editorial subject areas, surfaced as the
 *                 "Categories" index on the archive.
 * journal_issue — flat groupings for numbered issues or volumes.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register both journal taxonomies.
 *
 * @return void
 */
function citd_journal_register_taxonomies() {
	register_taxonomy(
		CITD_JOURNAL_TAX_TOPIC,
		array( CITD_JOURNAL_CPT ),
		array(
			'labels'            => array(
				'name'              => _x( 'Topics', 'taxonomy general name', 'citd-journal' ),
				'singular_name'     => _x( 'Topic', 'taxonomy singular name', 'citd-journal' ),
				'menu_name'         => __( 'Topics', 'citd-journal' ),
				'all_items'         => __( 'All Topics', 'citd-journal' ),
				'edit_item'         => __( 'Edit Topic', 'citd-journal' ),
				'view_item'         => __( 'View Topic', 'citd-journal' ),
				'update_item'       => __( 'Update Topic', 'citd-journal' ),
				'add_new_item'      => __( 'Add New Topic', 'citd-journal' ),
				'new_item_name'     => __( 'New Topic Name', 'citd-journal' ),
				'parent_item'       => __( 'Parent Topic', 'citd-journal' ),
				'parent_item_colon' => __( 'Parent Topic:', 'citd-journal' ),
				'search_items'      => __( 'Search Topics', 'citd-journal' ),
				'not_found'         => __( 'No topics found.', 'citd-journal' ),
				'back_to_items'     => __( '← Back to Topics', 'citd-journal' ),
			),
			'description'       => __( 'Editorial subject areas for The Creation Journal.', 'citd-journal' ),
			'public'            => true,
			'publicly_queryable' => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug'         => CITD_JOURNAL_SLUG . '/topic',
				'with_front'   => false,
				'hierarchical' => false,
			),
		)
	);

	register_taxonomy(
		CITD_JOURNAL_TAX_ISSUE,
		array( CITD_JOURNAL_CPT ),
		array(
			'labels'            => array(
				'name'          => _x( 'Issues', 'taxonomy general name', 'citd-journal' ),
				'singular_name' => _x( 'Issue', 'taxonomy singular name', 'citd-journal' ),
				'menu_name'     => __( 'Issues', 'citd-journal' ),
				'all_items'     => __( 'All Issues', 'citd-journal' ),
				'edit_item'     => __( 'Edit Issue', 'citd-journal' ),
				'view_item'     => __( 'View Issue', 'citd-journal' ),
				'update_item'   => __( 'Update Issue', 'citd-journal' ),
				'add_new_item'  => __( 'Add New Issue', 'citd-journal' ),
				'new_item_name' => __( 'New Issue Name', 'citd-journal' ),
				'search_items'  => __( 'Search Issues', 'citd-journal' ),
				'not_found'     => __( 'No issues found.', 'citd-journal' ),
				'back_to_items' => __( '← Back to Issues', 'citd-journal' ),
			),
			'description'       => __( 'Numbered issues or volumes of The Creation Journal.', 'citd-journal' ),
			'public'            => true,
			'publicly_queryable' => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug'       => CITD_JOURNAL_SLUG . '/issue',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'citd_journal_register_taxonomies', 4 );

/**
 * Register term metadata: a short standfirst for topic index cards and a
 * numeric sort key for issues.
 *
 * @return void
 */
function citd_journal_register_term_meta() {
	register_term_meta(
		CITD_JOURNAL_TAX_TOPIC,
		'citd_topic_standfirst',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'citd_journal_can_manage_terms',
		)
	);

	register_term_meta(
		CITD_JOURNAL_TAX_ISSUE,
		'citd_issue_number',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'citd_journal_can_manage_terms',
		)
	);

	register_term_meta(
		CITD_JOURNAL_TAX_ISSUE,
		'citd_issue_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'citd_journal_can_manage_terms',
		)
	);
}
add_action( 'init', 'citd_journal_register_term_meta', 6 );

/**
 * Authorisation callback for journal term meta.
 *
 * @return bool
 */
function citd_journal_can_manage_terms() {
	return current_user_can( 'manage_categories' );
}

/**
 * Render the extra term fields on the "add term" screens.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return void
 */
function citd_journal_add_term_fields( $taxonomy ) {
	if ( CITD_JOURNAL_TAX_TOPIC === $taxonomy ) {
		?>
		<div class="form-field term-standfirst-wrap">
			<label for="citd_topic_standfirst"><?php esc_html_e( 'Standfirst', 'citd-journal' ); ?></label>
			<textarea id="citd_topic_standfirst" name="citd_topic_standfirst" rows="3" cols="40"></textarea>
			<p><?php esc_html_e( 'One sentence describing this topic. Shown on the topic index and at the top of the topic archive.', 'citd-journal' ); ?></p>
		</div>
		<?php
	}

	if ( CITD_JOURNAL_TAX_ISSUE === $taxonomy ) {
		?>
		<div class="form-field term-issue-number-wrap">
			<label for="citd_issue_number"><?php esc_html_e( 'Issue number', 'citd-journal' ); ?></label>
			<input type="number" id="citd_issue_number" name="citd_issue_number" value="" min="0" step="1">
			<p><?php esc_html_e( 'Used to order issues from newest to oldest.', 'citd-journal' ); ?></p>
		</div>
		<div class="form-field term-issue-date-wrap">
			<label for="citd_issue_date"><?php esc_html_e( 'Cover date', 'citd-journal' ); ?></label>
			<input type="text" id="citd_issue_date" name="citd_issue_date" value="" placeholder="<?php esc_attr_e( 'Spring 2026', 'citd-journal' ); ?>">
			<p><?php esc_html_e( 'Free-text cover date, for example "Spring 2026".', 'citd-journal' ); ?></p>
		</div>
		<?php
	}
}
add_action( CITD_JOURNAL_TAX_TOPIC . '_add_form_fields', 'citd_journal_add_term_fields' );
add_action( CITD_JOURNAL_TAX_ISSUE . '_add_form_fields', 'citd_journal_add_term_fields' );

/**
 * Render the extra term fields on the "edit term" screens.
 *
 * @param WP_Term $term     Term being edited.
 * @param string  $taxonomy Taxonomy slug.
 * @return void
 */
function citd_journal_edit_term_fields( $term, $taxonomy ) {
	if ( CITD_JOURNAL_TAX_TOPIC === $taxonomy ) {
		$standfirst = get_term_meta( $term->term_id, 'citd_topic_standfirst', true );
		?>
		<tr class="form-field term-standfirst-wrap">
			<th scope="row"><label for="citd_topic_standfirst"><?php esc_html_e( 'Standfirst', 'citd-journal' ); ?></label></th>
			<td>
				<textarea id="citd_topic_standfirst" name="citd_topic_standfirst" rows="3" cols="50"><?php echo esc_textarea( $standfirst ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One sentence describing this topic.', 'citd-journal' ); ?></p>
			</td>
		</tr>
		<?php
	}

	if ( CITD_JOURNAL_TAX_ISSUE === $taxonomy ) {
		$number = get_term_meta( $term->term_id, 'citd_issue_number', true );
		$date   = get_term_meta( $term->term_id, 'citd_issue_date', true );
		?>
		<tr class="form-field term-issue-number-wrap">
			<th scope="row"><label for="citd_issue_number"><?php esc_html_e( 'Issue number', 'citd-journal' ); ?></label></th>
			<td>
				<input type="number" id="citd_issue_number" name="citd_issue_number" value="<?php echo esc_attr( $number ); ?>" min="0" step="1">
				<p class="description"><?php esc_html_e( 'Used to order issues from newest to oldest.', 'citd-journal' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-issue-date-wrap">
			<th scope="row"><label for="citd_issue_date"><?php esc_html_e( 'Cover date', 'citd-journal' ); ?></label></th>
			<td>
				<input type="text" id="citd_issue_date" name="citd_issue_date" value="<?php echo esc_attr( $date ); ?>" placeholder="<?php esc_attr_e( 'Spring 2026', 'citd-journal' ); ?>">
			</td>
		</tr>
		<?php
	}
}
add_action( CITD_JOURNAL_TAX_TOPIC . '_edit_form_fields', 'citd_journal_edit_term_fields', 10, 2 );
add_action( CITD_JOURNAL_TAX_ISSUE . '_edit_form_fields', 'citd_journal_edit_term_fields', 10, 2 );

/**
 * Persist the extra term fields.
 *
 * @param int $term_id Term ID.
 * @return void
 */
function citd_journal_save_term_fields( $term_id ) {
	if ( ! citd_journal_can_manage_terms() ) {
		return;
	}

	// Nonce is verified by WordPress core before these hooks fire on the term
	// screens; the capability check above is the meaningful gate here.
	if ( isset( $_POST['citd_topic_standfirst'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta(
			$term_id,
			'citd_topic_standfirst',
			sanitize_text_field( wp_unslash( $_POST['citd_topic_standfirst'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);
	}

	if ( isset( $_POST['citd_issue_number'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta(
			$term_id,
			'citd_issue_number',
			absint( wp_unslash( $_POST['citd_issue_number'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);
	}

	if ( isset( $_POST['citd_issue_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta(
			$term_id,
			'citd_issue_date',
			sanitize_text_field( wp_unslash( $_POST['citd_issue_date'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);
	}
}
add_action( 'created_' . CITD_JOURNAL_TAX_TOPIC, 'citd_journal_save_term_fields' );
add_action( 'edited_' . CITD_JOURNAL_TAX_TOPIC, 'citd_journal_save_term_fields' );
add_action( 'created_' . CITD_JOURNAL_TAX_ISSUE, 'citd_journal_save_term_fields' );
add_action( 'edited_' . CITD_JOURNAL_TAX_ISSUE, 'citd_journal_save_term_fields' );
