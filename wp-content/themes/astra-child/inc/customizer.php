<?php
/**
 * Customizer settings for The Creation Journal.
 *
 * Everything an editor might reasonably want to change without touching code:
 * masthead wording, the newsletter panel, footer copy and social profiles.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values, shared between the Customizer and the templates.
 *
 * @return array
 */
function citd_journal_defaults() {
	return array(
		'citd_journal_name'                => __( 'The Creation Journal', 'citd-journal' ),
		'citd_journal_publisher'           => __( 'Creation in the Dark Holdings', 'citd-journal' ),
		'citd_journal_hero_standfirst'     => __( 'Long-form research, analysis and field notes on how ventures are built in uncertainty.', 'citd-journal' ),
		'citd_journal_newsletter_title'    => __( 'Receive the journal', 'citd-journal' ),
		'citd_journal_newsletter_body'     => __( 'A single email when a new issue is published. No marketing, no forwarding of your address, one click to leave.', 'citd-journal' ),
		'citd_journal_newsletter_consent'  => __( 'Yes, send me new issues of The Creation Journal.', 'citd-journal' ),
		'citd_journal_footer_statement'    => __( 'The Creation Journal is the publication arm of Creation in the Dark Holdings. We write about capital, craft and the long work of building.', 'citd-journal' ),
		'citd_journal_footer_copyright'    => '',
		'citd_journal_social_linkedin'     => '',
		'citd_journal_social_x'            => '',
		'citd_journal_social_facebook'     => '',
		'citd_journal_twitter_handle'      => '',
		'citd_journal_contact_email'       => '',
		'citd_journal_logo_id'             => 0,
		'citd_journal_default_share_image' => 0,
	);
}

/**
 * Read a journal setting with its documented default.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function citd_journal_option( $key ) {
	$defaults = citd_journal_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	return get_theme_mod( $key, $default );
}

/**
 * Register the Customizer panel.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function citd_journal_customize_register( $wp_customize ) {
	$defaults = citd_journal_defaults();

	$wp_customize->add_panel(
		'citd_journal',
		array(
			'title'       => __( 'The Creation Journal', 'citd-journal' ),
			'description' => __( 'Masthead, newsletter and footer settings for the publication.', 'citd-journal' ),
			'priority'    => 25,
		)
	);

	/* ---------------------------------------------------------------------
	 * Masthead
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'citd_journal_masthead',
		array(
			'title' => __( 'Masthead', 'citd-journal' ),
			'panel' => 'citd_journal',
		)
	);

	$text_settings = array(
		'citd_journal_name'            => array(
			'label'   => __( 'Publication name', 'citd-journal' ),
			'section' => 'citd_journal_masthead',
			'type'    => 'text',
		),
		'citd_journal_publisher'       => array(
			'label'   => __( 'Publisher line', 'citd-journal' ),
			'section' => 'citd_journal_masthead',
			'type'    => 'text',
		),
		'citd_journal_hero_standfirst' => array(
			'label'       => __( 'Archive standfirst', 'citd-journal' ),
			'description' => __( 'The sentence beneath the masthead on /journal/.', 'citd-journal' ),
			'section'     => 'citd_journal_masthead',
			'type'        => 'textarea',
		),
	);

	/* ---------------------------------------------------------------------
	 * Newsletter
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'citd_journal_newsletter',
		array(
			'title' => __( 'Newsletter', 'citd-journal' ),
			'panel' => 'citd_journal',
		)
	);

	$text_settings['citd_journal_newsletter_title']   = array(
		'label'   => __( 'Heading', 'citd-journal' ),
		'section' => 'citd_journal_newsletter',
		'type'    => 'text',
	);
	$text_settings['citd_journal_newsletter_body']    = array(
		'label'   => __( 'Supporting copy', 'citd-journal' ),
		'section' => 'citd_journal_newsletter',
		'type'    => 'textarea',
	);
	$text_settings['citd_journal_newsletter_consent'] = array(
		'label'       => __( 'Consent checkbox label', 'citd-journal' ),
		'description' => __( 'Explicit opt-in wording stored with each subscription.', 'citd-journal' ),
		'section'     => 'citd_journal_newsletter',
		'type'        => 'textarea',
	);

	/* ---------------------------------------------------------------------
	 * Footer
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'citd_journal_footer',
		array(
			'title' => __( 'Footer', 'citd-journal' ),
			'panel' => 'citd_journal',
		)
	);

	$text_settings['citd_journal_footer_statement'] = array(
		'label'   => __( 'Footer statement', 'citd-journal' ),
		'section' => 'citd_journal_footer',
		'type'    => 'textarea',
	);
	$text_settings['citd_journal_footer_copyright'] = array(
		'label'       => __( 'Copyright line', 'citd-journal' ),
		'description' => __( 'Leave empty to generate "© <year> <publisher>. All rights reserved."', 'citd-journal' ),
		'section'     => 'citd_journal_footer',
		'type'        => 'text',
	);
	$text_settings['citd_journal_contact_email']    = array(
		'label'   => __( 'Contact email', 'citd-journal' ),
		'section' => 'citd_journal_footer',
		'type'    => 'text',
	);

	foreach ( $text_settings as $key => $control ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => 'textarea' === $control['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control( $key, $control );
	}

	/* ---------------------------------------------------------------------
	 * Social
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'citd_journal_social',
		array(
			'title' => __( 'Social profiles', 'citd-journal' ),
			'panel' => 'citd_journal',
		)
	);

	$url_settings = array(
		'citd_journal_social_linkedin' => __( 'LinkedIn URL', 'citd-journal' ),
		'citd_journal_social_x'        => __( 'X / Twitter URL', 'citd-journal' ),
		'citd_journal_social_facebook' => __( 'Facebook URL', 'citd-journal' ),
	);

	foreach ( $url_settings as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'citd_journal_social',
				'type'    => 'url',
			)
		);
	}

	$wp_customize->add_setting(
		'citd_journal_twitter_handle',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'citd_journal_twitter_handle',
		array(
			'label'       => __( 'X / Twitter handle', 'citd-journal' ),
			'description' => __( 'Used for the twitter:site card attribution. Without the @.', 'citd-journal' ),
			'section'     => 'citd_journal_social',
			'type'        => 'text',
		)
	);

	/* ---------------------------------------------------------------------
	 * Imagery
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'citd_journal_imagery',
		array(
			'title' => __( 'Imagery', 'citd-journal' ),
			'panel' => 'citd_journal',
		)
	);

	$wp_customize->add_setting(
		'citd_journal_logo_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'citd_journal_logo_id',
			array(
				'label'       => __( 'Publisher logo', 'citd-journal' ),
				'description' => __( 'Used in structured data. A square or wide PNG or SVG works best.', 'citd-journal' ),
				'section'     => 'citd_journal_imagery',
				'mime_type'   => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'citd_journal_default_share_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'citd_journal_default_share_image',
			array(
				'label'       => __( 'Default share image', 'citd-journal' ),
				'description' => __( 'Shown when an article has no hero image. Ideally 1200 × 630.', 'citd-journal' ),
				'section'     => 'citd_journal_imagery',
				'mime_type'   => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'citd_journal_customize_register' );
