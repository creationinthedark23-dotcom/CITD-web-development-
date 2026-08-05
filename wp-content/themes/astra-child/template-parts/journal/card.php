<?php
/**
 * Article card.
 *
 * Thin wrapper around citd_journal_card() so the markup has a single source of
 * truth while remaining overridable from a child of this child theme.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type int    $post_id Post ID.
 *     @type int    $index   Running number shown on the card.
 *     @type bool   $eager   Whether to load the image eagerly.
 *     @type string $size    Card scale.
 *     @type string $image   Registered image size.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();

if ( ! $citd_id ) {
	return;
}

citd_journal_card(
	$citd_id,
	array(
		'size'      => isset( $args['size'] ) ? $args['size'] : 'standard',
		'image'     => isset( $args['image'] ) ? $args['image'] : 'citd-card',
		'index'     => isset( $args['index'] ) ? (int) $args['index'] : 0,
		'eager'     => ! empty( $args['eager'] ),
		'show_deck' => ! isset( $args['show_deck'] ) || $args['show_deck'],
		'heading'   => isset( $args['heading'] ) ? $args['heading'] : 'h3',
	)
);
