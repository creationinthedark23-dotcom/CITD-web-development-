<?php
/**
 * Archive hero — the publication masthead.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_total = wp_count_posts( CITD_JOURNAL_CPT );
$citd_count = $citd_total ? (int) $citd_total->publish : 0;

$citd_latest_issue = citd_journal_get_issues( 1 );
$citd_issue        = ! empty( $citd_latest_issue ) ? $citd_latest_issue[0] : null;
?>

<section class="journal-hero" aria-labelledby="journal-hero-title">
	<div class="journal-shell">
		<p class="journal-hero__eyebrow">
			<span class="journal-rule" aria-hidden="true"></span>
			<?php
			printf(
				/* translators: %s: publisher name. */
				esc_html__( 'Published by %s', 'citd-journal' ),
				esc_html( citd_journal_publisher() )
			);
			?>
		</p>

		<h1 class="journal-hero__title" id="journal-hero-title">
			<?php echo esc_html( citd_journal_name() ); ?>
		</h1>

		<p class="journal-hero__standfirst">
			<?php echo esc_html( citd_journal_option( 'citd_journal_hero_standfirst' ) ); ?>
		</p>

		<dl class="journal-hero__facts">
			<div class="journal-hero__fact">
				<dt><?php esc_html_e( 'Articles', 'citd-journal' ); ?></dt>
				<dd><?php echo esc_html( number_format_i18n( $citd_count ) ); ?></dd>
			</div>

			<?php if ( $citd_issue ) : ?>
				<div class="journal-hero__fact">
					<dt><?php esc_html_e( 'Current issue', 'citd-journal' ); ?></dt>
					<dd>
						<a href="<?php echo esc_url( get_term_link( $citd_issue ) ); ?>">
							<?php echo esc_html( citd_journal_issue_label( $citd_issue ) ); ?>
						</a>
					</dd>
				</div>
			<?php endif; ?>

			<div class="journal-hero__fact">
				<dt><?php esc_html_e( 'Frequency', 'citd-journal' ); ?></dt>
				<dd><?php esc_html_e( 'Quarterly, with field notes in between', 'citd-journal' ); ?></dd>
			</div>
		</dl>

		<p class="journal-hero__cta">
			<a class="journal-button journal-button--ghost" href="#journal-newsletter">
				<span><?php esc_html_e( 'Receive the journal', 'citd-journal' ); ?></span>
				<?php echo citd_journal_icon( 'arrow-right', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
			</a>
		</p>
	</div>
</section>
