<?php
/**
 * Extended author profile fields used by the article author card.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Author profile field definitions.
 *
 * @return array
 */
function citd_journal_user_fields() {
	return array(
		'citd_role'       => array(
			'label' => __( 'Role / title', 'citd-journal' ),
			'help'  => __( 'For example: Partner, Strategy & Capital Markets.', 'citd-journal' ),
			'type'  => 'text',
		),
		'citd_org'        => array(
			'label' => __( 'Organisation', 'citd-journal' ),
			'help'  => __( 'Defaults to Creation in the Dark Holdings when left blank.', 'citd-journal' ),
			'type'  => 'text',
		),
		'citd_linkedin'   => array(
			'label' => __( 'LinkedIn URL', 'citd-journal' ),
			'help'  => __( 'Full profile URL.', 'citd-journal' ),
			'type'  => 'url',
		),
		'citd_x'          => array(
			'label' => __( 'X / Twitter URL', 'citd-journal' ),
			'help'  => __( 'Full profile URL.', 'citd-journal' ),
			'type'  => 'url',
		),
		'citd_website'    => array(
			'label' => __( 'Personal website', 'citd-journal' ),
			'help'  => __( 'Full URL.', 'citd-journal' ),
			'type'  => 'url',
		),
		'citd_portrait_id' => array(
			'label' => __( 'Portrait attachment ID', 'citd-journal' ),
			'help'  => __( 'Media library ID of a square portrait. Falls back to the Gravatar when empty.', 'citd-journal' ),
			'type'  => 'number',
		),
	);
}

/**
 * Render the extra fields on the profile screens.
 *
 * @param WP_User $user User being edited.
 * @return void
 */
function citd_journal_render_user_fields( $user ) {
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}

	wp_nonce_field( 'citd_journal_save_user', 'citd_journal_user_nonce' );
	?>
	<h2><?php esc_html_e( 'The Creation Journal — Contributor Profile', 'citd-journal' ); ?></h2>
	<table class="form-table" role="presentation">
		<tbody>
		<?php foreach ( citd_journal_user_fields() as $key => $field ) : ?>
			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				</th>
				<td>
					<input
						type="<?php echo esc_attr( $field['type'] ); ?>"
						id="<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>"
						class="regular-text"
					>
					<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
add_action( 'show_user_profile', 'citd_journal_render_user_fields' );
add_action( 'edit_user_profile', 'citd_journal_render_user_fields' );

/**
 * Save the extra profile fields.
 *
 * @param int $user_id User ID.
 * @return void
 */
function citd_journal_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	if ( ! isset( $_POST['citd_journal_user_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['citd_journal_user_nonce'] ) ), 'citd_journal_save_user' ) ) {
		return;
	}

	foreach ( citd_journal_user_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] );

		if ( 'url' === $field['type'] ) {
			$value = esc_url_raw( $raw );
		} elseif ( 'number' === $field['type'] ) {
			$value = absint( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( '' === $value || 0 === $value ) {
			delete_user_meta( $user_id, $key );
		} else {
			update_user_meta( $user_id, $key, $value );
		}
	}
}
add_action( 'personal_options_update', 'citd_journal_save_user_fields' );
add_action( 'edit_user_profile_update', 'citd_journal_save_user_fields' );

/**
 * Allow a limited set of inline formatting in author biographies.
 *
 * @param string $bio Raw biography.
 * @return string
 */
function citd_journal_kses_bio( $bio ) {
	return wp_kses(
		$bio,
		array(
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'rel'    => array(),
				'target' => array(),
			),
			'em'     => array(),
			'strong' => array(),
			'br'     => array(),
		)
	);
}
