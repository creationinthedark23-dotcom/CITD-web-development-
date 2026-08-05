<?php
/**
 * Related articles.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type array $ids Related post IDs.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_ids = isset( $args['ids'] ) ? array_filter( array_map( 'intval', (array) $args['ids'] ) ) : array();

if ( empty( $citd_ids ) ) {
	return;
}
?>

<section class="journal-section journal-section--related" aria-labelledby="journal-related-title">
	<div class="journal-shell">
		<header class="journal-sectionhead">
			<h2 class="journal-sectionhead__title" id="journal-related-title">
				<?php esc_html_e( 'Related reading', 'citd-journal' ); ?>
			</h2>
			<p class="journal-sectionhead__count">
				<a href="<?php echo esc_url( citd_journal_archive_url() ); ?>">
					<?php esc_html_e( 'All articles', 'citd-journal' ); ?>
				</a>
			</p>
		</header>

		<div class="journal-grid journal-grid--three">
			<?php
			foreach ( $citd_ids as $citd_related_id ) {
				get_template_part(
					'template-parts/journal/card',
					null,
					array(
						'post_id' => $citd_related_id,
						'image'   => 'citd-card',
					)
				);
			}
			?>
		</div>
	</div>
</section>
