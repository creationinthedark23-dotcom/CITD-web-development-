<?php
/**
 * Front-end output corrections for Spectra blocks.
 *
 * Spectra's button block saves an anchor carrying `role="button"` and an empty
 * `aria-label`. Both are wrong on a link that navigates: the role misreports it
 * to assistive technology as a button, and an empty aria-label is a labelling
 * attribute with nothing in it.
 *
 * Editing the saved markup would put the blocks permanently into WordPress's
 * "unexpected or invalid content" state, so the fix is applied to the rendered
 * output instead. The stored block markup is untouched and the editor is
 * unaffected.
 *
 * @package CITD_Journal
 * @since   1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strip the incorrect attributes from Spectra button links.
 *
 * @param string $content Rendered block HTML.
 * @param array  $block   Parsed block.
 * @return string
 */
function citd_site_fix_button_semantics( $content, $block ) {
	if ( is_admin() || empty( $block['blockName'] ) ) {
		return $content;
	}

	$targets = array( 'uagb/buttons-child', 'uagb/info-box' );

	if ( ! in_array( $block['blockName'], $targets, true ) ) {
		return $content;
	}

	if ( false === strpos( $content, 'role="button"' ) && false === strpos( $content, 'aria-label=""' ) ) {
		return $content;
	}

	// Only ever touches these two exact attribute strings.
	$content = str_replace(
		array( ' role="button"', ' aria-label=""' ),
		'',
		$content
	);

	return $content;
}
add_filter( 'render_block', 'citd_site_fix_button_semantics', 10, 2 );

/**
 * Give Spectra's inline `onclick="return true;"` no work to do.
 *
 * The attribute is a no-op that Spectra emits on info-box call-to-action links.
 * Removing it drops an inline handler from every card without changing any
 * behaviour, which keeps the pages compatible with a stricter Content Security
 * Policy later on.
 *
 * @param string $content Rendered block HTML.
 * @param array  $block   Parsed block.
 * @return string
 */
function citd_site_drop_noop_onclick( $content, $block ) {
	if ( is_admin() || empty( $block['blockName'] ) || 'uagb/info-box' !== $block['blockName'] ) {
		return $content;
	}

	return str_replace( ' onclick="return true;"', '', $content );
}
add_filter( 'render_block', 'citd_site_drop_noop_onclick', 10, 2 );
