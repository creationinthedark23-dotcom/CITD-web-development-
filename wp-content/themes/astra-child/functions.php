<?php
/**
 * Astra Child — The Creation Journal.
 *
 * Bootstrap file. Defines constants and loads every module that makes up the
 * publication. Nothing but wiring should live in this file.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme version. Bump on every deploy so that cache-busted asset URLs change.
 */
define( 'CITD_JOURNAL_VERSION', '1.0.0' );

/**
 * Absolute path to the child theme directory, without a trailing slash.
 */
define( 'CITD_JOURNAL_DIR', get_stylesheet_directory() );

/**
 * Public URI of the child theme directory, without a trailing slash.
 */
define( 'CITD_JOURNAL_URI', get_stylesheet_directory_uri() );

/**
 * Custom post type key for journal articles.
 */
define( 'CITD_JOURNAL_CPT', 'publication' );

/**
 * Archive / rewrite base. Produces /journal/ and /journal/article-name/.
 */
define( 'CITD_JOURNAL_SLUG', 'journal' );

/**
 * Taxonomy key for editorial topics (hierarchical, behaves like categories).
 */
define( 'CITD_JOURNAL_TAX_TOPIC', 'journal_topic' );

/**
 * Taxonomy key for issues / volumes (flat).
 */
define( 'CITD_JOURNAL_TAX_ISSUE', 'journal_issue' );

/**
 * Private post type used to store newsletter subscribers.
 */
define( 'CITD_JOURNAL_SUBSCRIBER_CPT', 'journal_subscriber' );

/**
 * Load a theme module from the /inc directory.
 *
 * @param string $module Module filename without the .php extension.
 * @return void
 */
function citd_journal_load_module( $module ) {
	$path = CITD_JOURNAL_DIR . '/inc/' . $module . '.php';

	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

/**
 * Modules, loaded in dependency order.
 *
 * setup               Theme supports, image sizes, nav menus, text domain.
 * enqueue             Stylesheet and script registration.
 * post-type-publication  The `publication` custom post type.
 * taxonomies          Topics and Issues.
 * meta-fields         Editorial metadata (deck, summary, references, …).
 * user-fields         Extended author profile fields.
 * content-processing  Heading IDs, table wrappers, image hardening, TOC data.
 * template-tags       Reading time, share URLs, card rendering helpers.
 * query               Archive query tuning and the featured-article resolver.
 * seo                 JSON-LD, Open Graph, Twitter cards, canonical.
 * newsletter          Subscription endpoint and storage.
 * shortcodes          [pullquote], [journal_stat], [journal_note].
 * customizer          Masthead, newsletter, footer and social settings.
 * site-output         Front-end corrections for Spectra blocks.
 */
$citd_journal_modules = array(
	'setup',
	'enqueue',
	'post-type-publication',
	'taxonomies',
	'meta-fields',
	'user-fields',
	'content-processing',
	'template-tags',
	'query',
	'seo',
	'newsletter',
	'shortcodes',
	'customizer',
	'site-output',
);

foreach ( $citd_journal_modules as $citd_journal_module ) {
	citd_journal_load_module( $citd_journal_module );
}

unset( $citd_journal_modules, $citd_journal_module );
