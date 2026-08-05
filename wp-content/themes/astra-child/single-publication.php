<?php
/**
 * A single journal article — /journal/article-name/
 *
 * Structure:
 *   1. Hero (four treatments, chosen per article)
 *   2. Executive summary
 *   3. Contents rail + body + share rail
 *   4. References
 *   5. Author card
 *   6. Previous / next
 *   7. Related articles
 *   8. Newsletter
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'journal' );

while ( have_posts() ) :
	the_post();

	$citd_id       = get_the_ID();
	$citd_summary  = citd_journal_summary_points( $citd_id );
	$citd_refs     = citd_journal_references( $citd_id );
	$citd_show_toc = citd_journal_show_toc( $citd_id );
	$citd_related  = citd_journal_related_ids( $citd_id, 3 );
	$citd_adjacent = citd_journal_adjacent_articles();
	?>

	<main class="journal-main journal-article" id="journal-main" tabindex="-1">
		<article class="journal-article__root" data-article>

			<?php get_template_part( 'template-parts/journal/article-hero' ); ?>

			<?php if ( ! empty( $citd_summary ) ) : ?>
				<?php
				get_template_part(
					'template-parts/journal/executive-summary',
					null,
					array( 'points' => $citd_summary )
				);
				?>
			<?php endif; ?>

			<div class="journal-layout<?php echo $citd_show_toc ? ' journal-layout--with-rail' : ''; ?>">
				<div class="journal-shell journal-layout__shell">

					<?php if ( $citd_show_toc ) : ?>
						<?php
						get_template_part(
							'template-parts/journal/toc',
							null,
							array( 'headings' => citd_journal_article_headings( $citd_id ) )
						);
						?>
					<?php endif; ?>

					<div class="journal-body" id="journal-body" data-article-body>
						<?php
						echo citd_journal_article_html( $citd_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already passed through the_content filters.

						wp_link_pages(
							array(
								'before'      => '<nav class="journal-pagelinks" aria-label="' . esc_attr__( 'Article pages', 'citd-journal' ) . '"><span>' . esc_html__( 'Pages:', 'citd-journal' ) . '</span>',
								'after'       => '</nav>',
								'link_before' => '<span>',
								'link_after'  => '</span>',
							)
						);
						?>

						<?php
						get_template_part(
							'template-parts/journal/filed-under',
							null,
							array( 'post_id' => $citd_id )
						);
						?>

						<?php if ( ! empty( $citd_refs ) ) : ?>
							<?php
							get_template_part(
								'template-parts/journal/references',
								null,
								array( 'references' => $citd_refs )
							);
							?>
						<?php endif; ?>

						<?php get_template_part( 'template-parts/journal/share', null, array( 'variant' => 'inline' ) ); ?>
					</div>

					<?php get_template_part( 'template-parts/journal/share', null, array( 'variant' => 'rail' ) ); ?>

				</div>
			</div>

			<?php get_template_part( 'template-parts/journal/author' ); ?>

			<?php
			get_template_part(
				'template-parts/journal/prev-next',
				null,
				$citd_adjacent
			);
			?>

			<?php if ( ! empty( $citd_related ) ) : ?>
				<?php
				get_template_part(
					'template-parts/journal/related',
					null,
					array( 'ids' => $citd_related )
				);
				?>
			<?php endif; ?>

		</article>

		<?php get_template_part( 'template-parts/journal/newsletter' ); ?>
	</main>

	<?php
endwhile;

get_footer( 'journal' );
