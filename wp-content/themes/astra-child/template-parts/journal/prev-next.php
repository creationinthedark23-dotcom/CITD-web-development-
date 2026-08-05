<?php
/**
 * Previous / next article navigation.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type WP_Post|null $previous Older article.
 *     @type WP_Post|null $next     Newer article.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_previous = isset( $args['previous'] ) ? $args['previous'] : null;
$citd_next     = isset( $args['next'] ) ? $args['next'] : null;

if ( ! $citd_previous && ! $citd_next ) {
	return;
}
?>

<nav class="journal-section journal-adjacent" aria-label="<?php esc_attr_e( 'Continue reading', 'citd-journal' ); ?>">
	<div class="journal-shell">
		<div class="journal-adjacent__grid">

			<?php if ( $citd_previous ) : ?>
				<a class="journal-adjacent__item journal-adjacent__item--prev" href="<?php echo esc_url( get_permalink( $citd_previous ) ); ?>" rel="prev">
					<span class="journal-adjacent__direction">
						<?php echo citd_journal_icon( 'arrow-left', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
						<span><?php esc_html_e( 'Previous article', 'citd-journal' ); ?></span>
					</span>
					<span class="journal-adjacent__title"><?php echo esc_html( get_the_title( $citd_previous ) ); ?></span>
					<span class="journal-adjacent__meta">
						<?php echo esc_html( get_the_date( get_option( 'date_format' ), $citd_previous ) ); ?>
					</span>
				</a>
			<?php else : ?>
				<span class="journal-adjacent__item journal-adjacent__item--empty" aria-hidden="true"></span>
			<?php endif; ?>

			<?php if ( $citd_next ) : ?>
				<a class="journal-adjacent__item journal-adjacent__item--next" href="<?php echo esc_url( get_permalink( $citd_next ) ); ?>" rel="next">
					<span class="journal-adjacent__direction">
						<span><?php esc_html_e( 'Next article', 'citd-journal' ); ?></span>
						<?php echo citd_journal_icon( 'arrow-right', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					</span>
					<span class="journal-adjacent__title"><?php echo esc_html( get_the_title( $citd_next ) ); ?></span>
					<span class="journal-adjacent__meta">
						<?php echo esc_html( get_the_date( get_option( 'date_format' ), $citd_next ) ); ?>
					</span>
				</a>
			<?php else : ?>
				<span class="journal-adjacent__item journal-adjacent__item--empty" aria-hidden="true"></span>
			<?php endif; ?>

		</div>
	</div>
</nav>
