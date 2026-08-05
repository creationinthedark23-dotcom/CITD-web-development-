<?php
/**
 * Topic and issue archive header.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_term = get_queried_object();

if ( ! $citd_term instanceof WP_Term ) {
	return;
}

$citd_is_issue   = CITD_JOURNAL_TAX_ISSUE === $citd_term->taxonomy;
$citd_standfirst = (string) get_term_meta( $citd_term->term_id, 'citd_topic_standfirst', true );

if ( '' === $citd_standfirst ) {
	$citd_standfirst = wp_strip_all_tags( $citd_term->description );
}

$citd_title = $citd_is_issue ? citd_journal_issue_label( $citd_term ) : $citd_term->name;
?>

<section class="journal-hero journal-hero--compact" aria-labelledby="journal-hero-title">
	<div class="journal-shell">
		<nav class="journal-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'citd-journal' ); ?>">
			<ol class="journal-breadcrumbs__list">
				<li class="journal-breadcrumbs__item">
					<a href="<?php echo esc_url( citd_journal_archive_url() ); ?>"><?php echo esc_html( citd_journal_name() ); ?></a>
				</li>
				<li class="journal-breadcrumbs__item" aria-current="page">
					<span><?php echo esc_html( $citd_title ); ?></span>
				</li>
			</ol>
		</nav>

		<p class="journal-hero__eyebrow">
			<span class="journal-rule" aria-hidden="true"></span>
			<?php echo esc_html( $citd_is_issue ? __( 'Issue', 'citd-journal' ) : __( 'Topic', 'citd-journal' ) ); ?>
		</p>

		<h1 class="journal-hero__title journal-hero__title--compact" id="journal-hero-title">
			<?php echo esc_html( $citd_title ); ?>
		</h1>

		<?php if ( '' !== $citd_standfirst ) : ?>
			<p class="journal-hero__standfirst"><?php echo esc_html( $citd_standfirst ); ?></p>
		<?php endif; ?>

		<p class="journal-hero__count">
			<?php
			printf(
				/* translators: %s: number of articles. */
				esc_html( _n( '%s article', '%s articles', (int) $citd_term->count, 'citd-journal' ) ),
				esc_html( number_format_i18n( $citd_term->count ) )
			);
			?>
		</p>
	</div>
</section>
