<?php
/**
 * References list.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type array $references Parsed references, each with `text` and `url`.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_references = isset( $args['references'] ) ? (array) $args['references'] : array();

if ( empty( $citd_references ) ) {
	return;
}
?>

<section class="journal-references" aria-labelledby="journal-references-title" data-reveal>
	<h2 class="journal-references__title" id="journal-references-title">
		<?php esc_html_e( 'References', 'citd-journal' ); ?>
	</h2>

	<ol class="journal-references__list">
		<?php foreach ( $citd_references as $citd_index => $citd_reference ) : ?>
			<li class="journal-references__item" id="ref-<?php echo esc_attr( $citd_index + 1 ); ?>">
				<span class="journal-references__number" aria-hidden="true">
					<?php echo esc_html( number_format_i18n( $citd_index + 1 ) ); ?>
				</span>
				<span class="journal-references__text">
					<?php echo esc_html( $citd_reference['text'] ); ?>
					<?php if ( '' !== $citd_reference['url'] ) : ?>
						<a class="journal-references__link" href="<?php echo esc_url( $citd_reference['url'] ); ?>" rel="noopener nofollow">
							<span><?php echo esc_html( wp_parse_url( $citd_reference['url'], PHP_URL_HOST ) ); ?></span>
							<?php echo citd_journal_icon( 'external', array( 'class' => 'journal-icon journal-icon--sm', 'size' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
						</a>
					<?php endif; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
