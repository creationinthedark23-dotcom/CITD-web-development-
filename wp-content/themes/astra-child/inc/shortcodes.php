<?php
/**
 * Editorial shortcodes.
 *
 * These exist so writers can place editorial furniture inside the body copy
 * without leaving the editor or reaching for a page builder.
 *
 *   [pullquote attribution="Jane Doe, CFO" align="right"]Quote text[/pullquote]
 *   [journal_stat value="38%" label="of firms report…" source="CITD, 2026"]
 *   [journal_note title="Method"]Explanatory aside[/journal_note]
 *   [journal_divider]
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pull quote.
 *
 * @param array       $atts    Shortcode attributes.
 * @param string|null $content Quote text.
 * @return string
 */
function citd_journal_shortcode_pullquote( $atts, $content = null ) {
	$atts = shortcode_atts(
		array(
			'attribution' => '',
			'align'       => 'wide',
		),
		$atts,
		'pullquote'
	);

	if ( null === $content || '' === trim( wp_strip_all_tags( $content ) ) ) {
		return '';
	}

	$align = in_array( $atts['align'], array( 'wide', 'left', 'right' ), true ) ? $atts['align'] : 'wide';

	$quote = wp_kses(
		do_shortcode( $content ),
		array(
			'em'     => array(),
			'strong' => array(),
			'a'      => array(
				'href' => array(),
				'rel'  => array(),
			),
			'br'     => array(),
		)
	);

	$html  = '<figure class="journal-pullquote journal-pullquote--' . esc_attr( $align ) . '" data-reveal>';
	$html .= citd_journal_icon( 'quote', array( 'class' => 'journal-pullquote__mark', 'size' => 28 ) );
	$html .= '<blockquote class="journal-pullquote__quote"><p>' . $quote . '</p></blockquote>';

	if ( '' !== $atts['attribution'] ) {
		$html .= '<figcaption class="journal-pullquote__attribution">' . esc_html( $atts['attribution'] ) . '</figcaption>';
	}

	$html .= '</figure>';

	return $html;
}
add_shortcode( 'pullquote', 'citd_journal_shortcode_pullquote' );

/**
 * Statistic callout.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function citd_journal_shortcode_stat( $atts ) {
	$atts = shortcode_atts(
		array(
			'value'  => '',
			'label'  => '',
			'source' => '',
		),
		$atts,
		'journal_stat'
	);

	if ( '' === $atts['value'] ) {
		return '';
	}

	$html  = '<aside class="journal-stat" data-reveal>';
	$html .= '<p class="journal-stat__value">' . esc_html( $atts['value'] ) . '</p>';

	if ( '' !== $atts['label'] ) {
		$html .= '<p class="journal-stat__label">' . esc_html( $atts['label'] ) . '</p>';
	}

	if ( '' !== $atts['source'] ) {
		$html .= '<p class="journal-stat__source">' . esc_html( $atts['source'] ) . '</p>';
	}

	$html .= '</aside>';

	return $html;
}
add_shortcode( 'journal_stat', 'citd_journal_shortcode_stat' );

/**
 * Editorial note / aside.
 *
 * @param array       $atts    Shortcode attributes.
 * @param string|null $content Note body.
 * @return string
 */
function citd_journal_shortcode_note( $atts, $content = null ) {
	$atts = shortcode_atts(
		array(
			'title' => __( 'Note', 'citd-journal' ),
		),
		$atts,
		'journal_note'
	);

	if ( null === $content || '' === trim( wp_strip_all_tags( $content ) ) ) {
		return '';
	}

	$body = wp_kses_post( do_shortcode( $content ) );

	$html  = '<aside class="journal-note" data-reveal>';
	$html .= '<p class="journal-note__title">' . esc_html( $atts['title'] ) . '</p>';
	$html .= '<div class="journal-note__body">' . $body . '</div>';
	$html .= '</aside>';

	return $html;
}
add_shortcode( 'journal_note', 'citd_journal_shortcode_note' );

/**
 * Section divider.
 *
 * @return string
 */
function citd_journal_shortcode_divider() {
	return '<hr class="journal-divider" aria-hidden="true">';
}
add_shortcode( 'journal_divider', 'citd_journal_shortcode_divider' );
