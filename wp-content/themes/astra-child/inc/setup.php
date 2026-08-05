<?php
/**
 * Theme setup: supports, image sizes, menus, localisation.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports, image sizes and navigation menus.
 *
 * @return void
 */
function citd_journal_setup() {
	load_child_theme_textdomain( 'citd-journal', CITD_JOURNAL_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	// Editorial crops. Widths are the 1x targets; WordPress serves srcset from
	// the full-size upload, so always upload hero images at 2560px or wider.
	add_image_size( 'citd-hero', 2400, 1350, true );        // 16:9 article hero.
	add_image_size( 'citd-feature', 1600, 1000, true );     // 8:5 featured card.
	add_image_size( 'citd-card', 1000, 625, true );         // 8:5 grid card.
	add_image_size( 'citd-card-portrait', 900, 1200, true ); // 3:4 editorial card.
	add_image_size( 'citd-thumb', 480, 300, true );          // Related / prev-next.
	add_image_size( 'citd-portrait', 480, 480, true );       // Author portrait.

	register_nav_menus(
		array(
			'journal_primary'   => __( 'Journal — Primary', 'citd-journal' ),
			'journal_secondary' => __( 'Journal — Utility', 'citd-journal' ),
			'journal_footer'    => __( 'Journal — Footer', 'citd-journal' ),
			'journal_legal'     => __( 'Journal — Legal', 'citd-journal' ),
		)
	);

	add_editor_style( 'assets/css/journal-editor.css' );
}
add_action( 'after_setup_theme', 'citd_journal_setup' );

/**
 * Expose human-readable labels for the custom image sizes in the media UI.
 *
 * @param array $sizes Existing size choices.
 * @return array
 */
function citd_journal_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'citd-hero'    => __( 'Journal — Hero (16:9)', 'citd-journal' ),
			'citd-feature' => __( 'Journal — Feature (8:5)', 'citd-journal' ),
			'citd-card'    => __( 'Journal — Card (8:5)', 'citd-journal' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'citd_journal_image_size_names' );

/**
 * Widen the srcset sizes attribute for full-bleed hero imagery so browsers do
 * not under-select a source on wide viewports.
 *
 * @param string $sizes Generated sizes attribute.
 * @param array  $size  Requested size array.
 * @return string
 */
function citd_journal_hero_sizes_attr( $sizes, $size ) {
	if ( is_array( $size ) && isset( $size[0] ) && (int) $size[0] >= 1600 ) {
		return '100vw';
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'citd_journal_hero_sizes_attr', 10, 2 );

/**
 * Register the journal sidebar used for supplementary archive modules.
 *
 * @return void
 */
function citd_journal_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Journal — Archive Aside', 'citd-journal' ),
			'id'            => 'journal-aside',
			'description'   => __( 'Optional modules shown beneath the topic index on the /journal/ archive.', 'citd-journal' ),
			'before_widget' => '<section id="%1$s" class="journal-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="journal-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'citd_journal_widgets_init' );

/**
 * Flush rewrite rules once after the theme is activated so that /journal/
 * resolves immediately without a manual permalink save.
 *
 * @return void
 */
function citd_journal_activate() {
	update_option( 'citd_journal_flush_rewrite', 1, false );
}
add_action( 'after_switch_theme', 'citd_journal_activate' );

/**
 * Perform the deferred rewrite flush on the next request.
 *
 * @return void
 */
function citd_journal_maybe_flush_rewrite() {
	if ( get_option( 'citd_journal_flush_rewrite' ) ) {
		flush_rewrite_rules( false );
		delete_option( 'citd_journal_flush_rewrite' );
	}
}
add_action( 'wp_loaded', 'citd_journal_maybe_flush_rewrite', 99 );

/**
 * Remove the WordPress emoji detection scripts on journal templates. They cost
 * a render-blocking inline script plus a DNS lookup and the publication does
 * not rely on them.
 *
 * @return void
 */
function citd_journal_dequeue_emoji() {
	if ( ! citd_journal_is_journal_context() ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}
add_action( 'wp', 'citd_journal_dequeue_emoji' );

/**
 * Trim Astra's default page shell on journal templates. The publication ships
 * its own header and footer, so the parent theme markup is suppressed to avoid
 * duplicate landmarks.
 *
 * @return void
 */
function citd_journal_disable_astra_shell() {
	if ( ! citd_journal_is_journal_context() ) {
		return;
	}

	add_filter( 'astra_page_layout', 'citd_journal_force_full_width', 99 );
	add_filter( 'astra_get_content_layout', 'citd_journal_force_page_builder', 99 );
}
add_action( 'wp', 'citd_journal_disable_astra_shell' );

/**
 * Force Astra's full-width layout on journal templates.
 *
 * @return string
 */
function citd_journal_force_full_width() {
	return 'no-sidebar';
}

/**
 * Force Astra's unstyled content layout on journal templates.
 *
 * @return string
 */
function citd_journal_force_page_builder() {
	return 'page-builder';
}

/**
 * Add journal-specific classes to the body element.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function citd_journal_body_class( $classes ) {
	if ( ! citd_journal_is_journal_context() ) {
		return $classes;
	}

	$classes[] = 'journal';

	if ( is_singular( CITD_JOURNAL_CPT ) ) {
		$classes[] = 'journal--article';
	}

	if ( is_post_type_archive( CITD_JOURNAL_CPT ) ) {
		$classes[] = 'journal--archive';
	}

	if ( is_tax( array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ) ) ) {
		$classes[] = 'journal--archive';
		$classes[] = 'journal--taxonomy';
	}

	return $classes;
}
add_filter( 'body_class', 'citd_journal_body_class' );

/**
 * Determine whether the current request is rendered by the journal templates.
 *
 * @return bool
 */
function citd_journal_is_journal_context() {
	if ( is_admin() ) {
		return false;
	}

	if ( is_singular( CITD_JOURNAL_CPT ) ) {
		return true;
	}

	if ( is_post_type_archive( CITD_JOURNAL_CPT ) ) {
		return true;
	}

	if ( is_tax( array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ) ) ) {
		return true;
	}

	if ( is_search() ) {
		$searched_type = get_query_var( 'post_type' );

		if ( CITD_JOURNAL_CPT === $searched_type || ( is_array( $searched_type ) && in_array( CITD_JOURNAL_CPT, $searched_type, true ) ) ) {
			return true;
		}
	}

	return false;
}
