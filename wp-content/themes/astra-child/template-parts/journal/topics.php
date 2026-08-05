<?php
/**
 * Topic index.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_topics = get_terms(
	array(
		'taxonomy'   => CITD_JOURNAL_TAX_TOPIC,
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);

if ( is_wp_error( $citd_topics ) || empty( $citd_topics ) ) {
	return;
}
?>

<section class="journal-section journal-section--topics" aria-labelledby="journal-topics-title">
	<div class="journal-shell">
		<header class="journal-sectionhead">
			<h2 class="journal-sectionhead__title" id="journal-topics-title">
				<?php esc_html_e( 'Categories', 'citd-journal' ); ?>
			</h2>
			<p class="journal-sectionhead__count">
				<?php esc_html_e( 'Read by subject', 'citd-journal' ); ?>
			</p>
		</header>

		<ul class="journal-topics">
			<?php foreach ( $citd_topics as $citd_topic ) : ?>
				<?php $citd_standfirst = (string) get_term_meta( $citd_topic->term_id, 'citd_topic_standfirst', true ); ?>
				<li class="journal-topics__item" data-reveal>
					<a class="journal-topics__link" href="<?php echo esc_url( get_term_link( $citd_topic ) ); ?>">
						<span class="journal-topics__name"><?php echo esc_html( $citd_topic->name ); ?></span>
						<span class="journal-topics__count" aria-hidden="true"><?php echo esc_html( number_format_i18n( $citd_topic->count ) ); ?></span>
						<span class="screen-reader-text">
							<?php
							printf(
								/* translators: %s: number of articles. */
								esc_html( _n( '%s article', '%s articles', (int) $citd_topic->count, 'citd-journal' ) ),
								esc_html( number_format_i18n( $citd_topic->count ) )
							);
							?>
						</span>
						<?php if ( '' !== $citd_standfirst ) : ?>
							<span class="journal-topics__standfirst"><?php echo esc_html( $citd_standfirst ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( is_active_sidebar( 'journal-aside' ) ) : ?>
			<div class="journal-aside">
				<?php dynamic_sidebar( 'journal-aside' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
