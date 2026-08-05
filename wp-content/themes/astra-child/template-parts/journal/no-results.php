<?php
/**
 * Empty state for the journal index, taxonomy archives and search.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="journal-section journal-empty" aria-labelledby="journal-empty-title">
	<div class="journal-shell">
		<div class="journal-empty__inner">
			<p class="journal-empty__eyebrow">
				<span class="journal-rule" aria-hidden="true"></span>
				<?php esc_html_e( 'Nothing here yet', 'citd-journal' ); ?>
			</p>

			<h2 class="journal-empty__title" id="journal-empty-title">
				<?php
				if ( is_search() ) {
					printf(
						/* translators: %s: search term. */
						esc_html__( 'No articles match “%s”.', 'citd-journal' ),
						esc_html( get_search_query() )
					);
				} else {
					esc_html_e( 'No articles have been published in this section.', 'citd-journal' );
				}
				?>
			</h2>

			<p class="journal-empty__body">
				<?php esc_html_e( 'Try a broader term, browse the full index, or subscribe and we will tell you when the next issue is out.', 'citd-journal' ); ?>
			</p>

			<p class="journal-empty__actions">
				<a class="journal-button" href="<?php echo esc_url( citd_journal_archive_url() ); ?>">
					<span><?php esc_html_e( 'Browse the journal', 'citd-journal' ); ?></span>
					<?php echo citd_journal_icon( 'arrow-right', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</a>
			</p>
		</div>
	</div>
</section>
