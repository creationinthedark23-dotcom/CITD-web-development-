<?php
/**
 * The journal index — /journal/
 *
 * Also serves the topic and issue taxonomy archives and the scoped journal
 * search, via citd_journal_template_include().
 *
 * Structure:
 *   1. Masthead hero (or taxonomy / search header)
 *   2. Latest issue — the featured article, given the full width of the page
 *   3. Topic index
 *   4. Grid of previous issues
 *   5. Pagination
 *   6. Newsletter
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'journal' );

$citd_is_index    = is_post_type_archive( CITD_JOURNAL_CPT );
$citd_is_search   = is_search();
$citd_page        = max( 1, (int) get_query_var( 'paged' ) );
$citd_featured_id = ( $citd_is_index && 1 === $citd_page ) ? citd_journal_featured_id() : 0;
?>

<main class="journal-main" id="journal-main" tabindex="-1">

	<?php
	if ( $citd_is_search ) {
		get_template_part( 'template-parts/journal/header', 'search' );
	} elseif ( $citd_is_index ) {
		get_template_part( 'template-parts/journal/hero', 'archive' );
	} else {
		get_template_part( 'template-parts/journal/header', 'taxonomy' );
	}
	?>

	<?php if ( $citd_featured_id ) : ?>
		<?php
		get_template_part(
			'template-parts/journal/featured',
			null,
			array( 'post_id' => $citd_featured_id )
		);
		?>
	<?php endif; ?>

	<?php if ( $citd_is_index ) : ?>
		<?php get_template_part( 'template-parts/journal/topics' ); ?>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>

		<section class="journal-section journal-section--grid" aria-labelledby="journal-grid-title">
			<div class="journal-shell">
				<header class="journal-sectionhead">
					<h2 class="journal-sectionhead__title" id="journal-grid-title">
						<?php
						if ( $citd_is_search ) {
							printf(
								/* translators: %s: search term. */
								esc_html__( 'Results for “%s”', 'citd-journal' ),
								esc_html( get_search_query() )
							);
						} elseif ( $citd_is_index ) {
							esc_html_e( 'Previous issues', 'citd-journal' );
						} else {
							esc_html_e( 'Articles', 'citd-journal' );
						}
						?>
					</h2>

					<p class="journal-sectionhead__count">
						<?php
						$citd_total = (int) $GLOBALS['wp_query']->found_posts;

						printf(
							/* translators: %s: number of articles. */
							esc_html( _n( '%s article', '%s articles', $citd_total, 'citd-journal' ) ),
							esc_html( number_format_i18n( $citd_total ) )
						);
						?>
					</p>
				</header>

				<div class="journal-grid">
					<?php
					$citd_index = ( $citd_page - 1 ) * citd_journal_archive_per_page();

					while ( have_posts() ) :
						the_post();
						++$citd_index;

						get_template_part(
							'template-parts/journal/card',
							null,
							array(
								'post_id' => get_the_ID(),
								'index'   => $citd_index,
								'eager'   => $citd_index <= 3,
							)
						);
					endwhile;
					?>
				</div>
			</div>
		</section>

		<?php get_template_part( 'template-parts/journal/pagination' ); ?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/journal/no-results' ); ?>

	<?php endif; ?>

	<?php get_template_part( 'template-parts/journal/newsletter' ); ?>

</main>

<?php
get_footer( 'journal' );
