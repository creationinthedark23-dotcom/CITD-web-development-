<?php
/**
 * Archive pagination.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_links = paginate_links(
	array(
		'mid_size'  => 1,
		'end_size'  => 1,
		'type'      => 'array',
		'prev_text' => citd_journal_icon( 'arrow-left', array( 'size' => 18 ) ) . '<span>' . esc_html__( 'Newer', 'citd-journal' ) . '</span>',
		'next_text' => '<span>' . esc_html__( 'Older', 'citd-journal' ) . '</span>' . citd_journal_icon( 'arrow-right', array( 'size' => 18 ) ),
	)
);

if ( empty( $citd_links ) ) {
	return;
}

$citd_allowed = array(
	'a'    => array(
		'href'         => array(),
		'class'        => array(),
		'aria-current' => array(),
		'aria-label'   => array(),
	),
	'span' => array(
		'class'        => array(),
		'aria-current' => array(),
	),
	'svg'  => array(
		'class'            => array(),
		'width'            => array(),
		'height'           => array(),
		'viewbox'          => array(),
		'fill'             => array(),
		'stroke'           => array(),
		'stroke-width'     => array(),
		'stroke-linecap'   => array(),
		'stroke-linejoin'  => array(),
		'aria-hidden'      => array(),
		'focusable'        => array(),
	),
	'path' => array( 'd' => array() ),
	'circle' => array(
		'cx' => array(),
		'cy' => array(),
		'r'  => array(),
	),
	'rect' => array(
		'x'      => array(),
		'y'      => array(),
		'width'  => array(),
		'height' => array(),
		'rx'     => array(),
	),
);
?>

<nav class="journal-pagination" aria-label="<?php esc_attr_e( 'Article pages', 'citd-journal' ); ?>">
	<div class="journal-shell">
		<ul class="journal-pagination__list">
			<?php foreach ( $citd_links as $citd_link ) : ?>
				<li class="journal-pagination__item">
					<?php echo wp_kses( $citd_link, $citd_allowed ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
