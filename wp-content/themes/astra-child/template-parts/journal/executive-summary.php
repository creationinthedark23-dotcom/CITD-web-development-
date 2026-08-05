<?php
/**
 * Executive summary panel.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type array $points Summary lines.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_points = isset( $args['points'] ) ? (array) $args['points'] : array();

if ( empty( $citd_points ) ) {
	return;
}
?>

<aside class="journal-summary" aria-labelledby="journal-summary-title" data-reveal>
	<div class="journal-shell">
		<div class="journal-summary__inner">
			<div class="journal-summary__head">
				<p class="journal-summary__eyebrow">
					<span class="journal-rule" aria-hidden="true"></span>
					<?php esc_html_e( 'In brief', 'citd-journal' ); ?>
				</p>
				<h2 class="journal-summary__title" id="journal-summary-title">
					<?php esc_html_e( 'Executive summary', 'citd-journal' ); ?>
				</h2>
			</div>

			<ol class="journal-summary__list">
				<?php foreach ( $citd_points as $citd_index => $citd_point ) : ?>
					<li class="journal-summary__item">
						<span class="journal-summary__number" aria-hidden="true">
							<?php echo esc_html( str_pad( (string) ( $citd_index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?>
						</span>
						<span class="journal-summary__text"><?php echo esc_html( $citd_point ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</aside>
