<?php
/**
 * Latest issue — the featured article.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type int $post_id Featured post ID.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;

if ( ! $citd_id ) {
	return;
}

$citd_topic   = citd_journal_primary_topic( $citd_id );
$citd_issue   = citd_journal_issue_term( $citd_id );
$citd_deck    = citd_journal_deck( $citd_id );
$citd_link    = get_permalink( $citd_id );
$citd_author  = citd_journal_author( (int) get_post_field( 'post_author', $citd_id ) );
$citd_reading = citd_journal_reading_time_label( $citd_id );
$citd_summary = citd_journal_summary_points( $citd_id );
?>

<section class="journal-section journal-section--featured" aria-labelledby="journal-featured-title">
	<div class="journal-shell">
		<header class="journal-sectionhead">
			<h2 class="journal-sectionhead__title" id="journal-featured-title">
				<?php esc_html_e( 'Latest issue', 'citd-journal' ); ?>
			</h2>

			<?php if ( $citd_issue ) : ?>
				<p class="journal-sectionhead__count">
					<a href="<?php echo esc_url( get_term_link( $citd_issue ) ); ?>">
						<?php echo esc_html( citd_journal_issue_label( $citd_issue ) ); ?>
					</a>
				</p>
			<?php endif; ?>
		</header>

		<article class="journal-featured<?php echo has_post_thumbnail( $citd_id ) ? '' : ' journal-featured--textual'; ?>">

			<?php if ( has_post_thumbnail( $citd_id ) ) : ?>
				<a class="journal-featured__media" href="<?php echo esc_url( $citd_link ); ?>" tabindex="-1" aria-hidden="true">
					<?php
					echo wp_get_attachment_image(
						get_post_thumbnail_id( $citd_id ),
						'citd-feature',
						false,
						array(
							'class'         => 'journal-featured__img',
							'alt'           => '',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
							'sizes'         => '(min-width: 1100px) 58vw, 100vw',
						)
					);
					?>
				</a>
			<?php endif; ?>

			<div class="journal-featured__body">
				<p class="journal-featured__eyebrow">
					<?php if ( $citd_topic ) : ?>
						<a class="journal-featured__topic" href="<?php echo esc_url( get_term_link( $citd_topic ) ); ?>">
							<?php echo esc_html( $citd_topic->name ); ?>
						</a>
					<?php endif; ?>
					<?php echo citd_journal_dateline( $citd_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template tag. ?>
				</p>

				<h3 class="journal-featured__title">
					<a href="<?php echo esc_url( $citd_link ); ?>"><?php echo esc_html( get_the_title( $citd_id ) ); ?></a>
				</h3>

				<?php if ( '' !== $citd_deck ) : ?>
					<p class="journal-featured__deck"><?php echo esc_html( wp_trim_words( $citd_deck, 46, '…' ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $citd_summary ) ) : ?>
					<ul class="journal-featured__points">
						<?php foreach ( array_slice( $citd_summary, 0, 3 ) as $citd_point ) : ?>
							<li><?php echo esc_html( wp_trim_words( $citd_point, 18, '…' ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="journal-featured__foot">
					<?php if ( ! empty( $citd_author ) ) : ?>
						<p class="journal-featured__byline">
							<span class="journal-featured__by"><?php esc_html_e( 'By', 'citd-journal' ); ?></span>
							<span class="journal-featured__author"><?php echo esc_html( $citd_author['name'] ); ?></span>
							<?php if ( '' !== $citd_author['role'] ) : ?>
								<span class="journal-featured__role"><?php echo esc_html( $citd_author['role'] ); ?></span>
							<?php endif; ?>
						</p>
					<?php endif; ?>

					<a class="journal-button" href="<?php echo esc_url( $citd_link ); ?>">
						<span><?php esc_html_e( 'Read the article', 'citd-journal' ); ?></span>
						<?php echo citd_journal_icon( 'arrow-right', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					</a>

					<?php if ( '' !== $citd_reading ) : ?>
						<p class="journal-featured__reading"><?php echo esc_html( $citd_reading ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</article>
	</div>
</section>
