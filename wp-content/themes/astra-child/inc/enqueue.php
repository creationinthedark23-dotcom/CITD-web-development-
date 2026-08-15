<?php
/**
 * Asset loading.
 *
 * The publication's CSS and JS are only loaded on journal templates so that the
 * rest of the site is unaffected. Fonts are served from a single Google Fonts
 * request with `display=swap`, preconnected, and can be swapped for self-hosted
 * files through the `citd_journal_fonts_url` filter.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the webfont request URL.
 *
 * Three variable families, latin + latin-ext, one request:
 *   Fraunces      — display headlines (optical size + weight axes).
 *   Newsreader    — long-form body copy.
 *   Inter         — interface, metadata, eyebrows, buttons.
 *
 * Return an empty string from the filter to disable remote fonts entirely; the
 * stylesheet falls back to a curated system stack.
 *
 * @return string
 */
function citd_journal_fonts_url() {
	$families = array(
		'Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700',
		'Newsreader:opsz,wght@6..72,300;6..72,400;6..72,500;6..72,600',
		'Inter:wght@400;500;600;700',
	);

	// Google Fonts v2 expects one `family` parameter per family and relies on
	// literal `:`, `@`, `,` and `;` characters, so the query string is composed
	// directly rather than through add_query_arg().
	$query = array();

	foreach ( $families as $family ) {
		$query[] = 'family=' . str_replace( ' ', '+', $family );
	}

	$query[] = 'display=swap';

	$url = 'https://fonts.googleapis.com/css2?' . implode( '&', $query );

	/**
	 * Filter the webfont stylesheet URL.
	 *
	 * @param string $url Fully-formed font stylesheet URL. Empty disables fonts.
	 */
	return apply_filters( 'citd_journal_fonts_url', $url );
}

/**
 * Emit preconnect hints for the font host before the stylesheet request.
 *
 * @param array  $urls          Resource URLs.
 * @param string $relation_type Link relation.
 * @return array
 */
function citd_journal_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type || ! citd_journal_is_journal_context() ) {
		return $urls;
	}

	if ( '' === citd_journal_fonts_url() ) {
		return $urls;
	}

	$urls[] = array(
		'href' => 'https://fonts.gstatic.com',
		'crossorigin' => 'anonymous',
	);
	$urls[] = array( 'href' => 'https://fonts.googleapis.com' );

	return $urls;
}
add_filter( 'wp_resource_hints', 'citd_journal_resource_hints', 10, 2 );

/**
 * Register and enqueue front-end assets.
 *
 * @return void
 */
function citd_journal_enqueue_assets() {
	$parent_handle = 'astra-theme-css';

	// Always keep the parent stylesheet chain intact.
	wp_enqueue_style(
		'citd-journal-base',
		CITD_JOURNAL_URI . '/style.css',
		wp_style_is( $parent_handle, 'registered' ) ? array( $parent_handle ) : array(),
		CITD_JOURNAL_VERSION
	);

	if ( ! citd_journal_is_journal_context() ) {
		// Everywhere except the publication: the holding company design system.
		// The two stylesheets are never loaded together, so their class
		// namespaces (citd-* and journal-*) can never collide.
		wp_enqueue_style(
			'citd-site',
			CITD_JOURNAL_URI . '/assets/css/citd-site.css',
			array( 'citd-journal-base' ),
			CITD_JOURNAL_VERSION
		);

		wp_enqueue_script(
			'citd-site',
			CITD_JOURNAL_URI . '/assets/js/citd-site.js',
			array(),
			CITD_JOURNAL_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		return;
	}

	$fonts_url = citd_journal_fonts_url();

	if ( '' !== $fonts_url ) {
		wp_enqueue_style( 'citd-journal-fonts', $fonts_url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Versioning is encoded in the remote URL.
	}

	wp_enqueue_style(
		'citd-journal',
		CITD_JOURNAL_URI . '/assets/css/journal.css',
		array( 'citd-journal-base' ),
		CITD_JOURNAL_VERSION
	);

	wp_enqueue_style(
		'citd-journal-print',
		CITD_JOURNAL_URI . '/assets/css/journal-print.css',
		array( 'citd-journal' ),
		CITD_JOURNAL_VERSION,
		'print'
	);

	wp_enqueue_script(
		'citd-journal',
		CITD_JOURNAL_URI . '/assets/js/journal.js',
		array(),
		CITD_JOURNAL_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script(
		'citd-journal',
		'CITDJournal',
		array(
			'ajaxUrl'      => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'nonce'        => wp_create_nonce( 'citd_journal_newsletter' ),
			'action'       => 'citd_journal_subscribe',
			'isArticle'    => is_singular( CITD_JOURNAL_CPT ),
			'wordsPerMin'  => citd_journal_words_per_minute(),
			'strings'      => array(
				'copied'          => __( 'Link copied to clipboard', 'citd-journal' ),
				'copyFailed'      => __( 'Press Ctrl or Cmd + C to copy the link', 'citd-journal' ),
				'menuOpen'        => __( 'Open menu', 'citd-journal' ),
				'menuClose'       => __( 'Close menu', 'citd-journal' ),
				'themeToLight'    => __( 'Switch to light theme', 'citd-journal' ),
				'themeToDark'     => __( 'Switch to dark theme', 'citd-journal' ),
				'minutesLeft'     => __( '%s min left', 'citd-journal' ),
				'almostDone'      => __( 'Less than a minute left', 'citd-journal' ),
				'finished'        => __( 'End of article', 'citd-journal' ),
				'subscribing'     => __( 'Subscribing…', 'citd-journal' ),
				'subscribe'       => __( 'Subscribe', 'citd-journal' ),
				'invalidEmail'    => __( 'Enter a valid email address.', 'citd-journal' ),
				'requiredConsent' => __( 'Please confirm you would like to receive the journal.', 'citd-journal' ),
				'networkError'    => __( 'We could not reach the server. Please try again.', 'citd-journal' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'citd_journal_enqueue_assets', 20 );

/**
 * Print the no-flash theme bootstrap.
 *
 * This must run before the first paint, so it is inlined in <head> rather than
 * loaded from journal.js. It resolves the stored preference, falls back to the
 * operating-system setting, and stamps `data-theme` on <html>.
 *
 * @return void
 */
function citd_journal_theme_bootstrap() {
	if ( ! citd_journal_is_journal_context() ) {
		return;
	}

	$script = <<<'JS'
(function () {
	try {
		var stored = window.localStorage.getItem('citd-journal-theme');
		var root = document.documentElement;
		if (stored === 'dark' || stored === 'light') {
			root.setAttribute('data-theme', stored);
		} else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			root.setAttribute('data-theme', 'dark');
		} else {
			root.setAttribute('data-theme', 'light');
		}
	} catch (e) {
		document.documentElement.setAttribute('data-theme', 'light');
	}
	document.documentElement.classList.add('has-js');
})();
JS;

	echo "<script id=\"citd-journal-theme-bootstrap\">\n" . $script . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, self-contained script.
}
add_action( 'wp_head', 'citd_journal_theme_bootstrap', 1 );

/**
 * Preload the article hero image so the largest contentful paint is not gated
 * behind CSS parsing.
 *
 * @return void
 */
function citd_journal_preload_hero() {
	if ( ! is_singular( CITD_JOURNAL_CPT ) || ! has_post_thumbnail() ) {
		return;
	}

	$id = get_post_thumbnail_id();

	if ( ! $id ) {
		return;
	}

	$src = wp_get_attachment_image_src( $id, 'citd-hero' );

	if ( ! $src ) {
		return;
	}

	$srcset = wp_get_attachment_image_srcset( $id, 'citd-hero' );

	printf(
		'<link rel="preload" as="image" href="%1$s"%2$s imagesizes="100vw" fetchpriority="high">' . "\n",
		esc_url( $src[0] ),
		$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : ''
	);
}
add_action( 'wp_head', 'citd_journal_preload_hero', 2 );

/**
 * Load editorial styles inside wp-admin so the metadata panels match the
 * publication's design language.
 *
 * @param string $hook Current admin page.
 * @return void
 */
function citd_journal_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || CITD_JOURNAL_CPT !== $screen->post_type ) {
		return;
	}

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	wp_enqueue_style(
		'citd-journal-admin',
		CITD_JOURNAL_URI . '/assets/css/journal-admin.css',
		array(),
		CITD_JOURNAL_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'citd_journal_admin_assets' );
