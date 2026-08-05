<?php
/**
 * Article hero.
 *
 * Four treatments, selected per article in the Presentation meta box:
 *   standard     Title block above a wide image.
 *   immersive    Full-bleed image with the title overlaid.
 *   split        Title beside the image on wide viewports.
 *   typographic  Title only. Used automatically when there is no hero image.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_id      = get_the_ID();
$citd_style   = citd_journal_hero_style( $citd_id );
$citd_eyebrow = citd_journal_eyebrow( $citd_id );
$citd_deck    = citd_journal_deck( $citd_id );
$citd_credit  = (string) get_post_meta( $citd_id, '_citd_hero_credit', true );
$citd_issue   = citd_journal_issue_term( $citd_id );
$citd_author  = citd_journal_author();
$citd_reading = citd_journal_reading_time_label( $citd_id );
$citd_has_img = has_post_thumbnail( $citd_id ) && 'typographic' !== $citd_style;

/**
 * Render the shared title block. Used by every treatment so the semantics stay
 * identical regardless of the visual arrangement.
 *
 * @return void
 */
$citd_render_titleblock = static function () use ( $citd_eyebrow, $citd_deck, $citd_issue ) {
	?>
	<?php citd_journal_breadcrumbs(); ?>

	<?php if ( '' !== $citd_eyebrow ) : ?>
		<p class="journal-articlehead__eyebrow">
			<span class="journal-rule" aria-hidden="true"></span>
			<?php echo esc_html( $citd_eyebrow ); ?>
			<?php if ( $citd_issue ) : ?>
				<span class="journal-articlehead__issue">
					<?php echo esc_html( citd_journal_issue_label( $citd_issue ) ); ?>
				</span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<h1 class="journal-articlehead__title"><?php the_title(); ?></h1>

	<?php if ( '' !== $citd_deck ) : ?>
		<p class="journal-articlehead__deck"><?php echo esc_html( $citd_deck ); ?></p>
	<?php endif; ?>
	<?php
};

/**
 * Render the byline strip.
 *
 * @return void
 */
$citd_render_meta = static function () use ( $citd_author, $citd_reading, $citd_id ) {
	?>
	<div class="journal-articlemeta">
		<?php if ( ! empty( $citd_author ) ) : ?>
			<div class="journal-articlemeta__author">
				<?php echo citd_journal_author_portrait( $citd_author, 48 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
				<p class="journal-articlemeta__names">
					<span class="journal-articlemeta__by"><?php esc_html_e( 'By', 'citd-journal' ); ?></span>
					<a class="journal-articlemeta__name" href="<?php echo esc_url( $citd_author['url'] ); ?>" rel="author">
						<?php echo esc_html( $citd_author['name'] ); ?>
					</a>
					<?php if ( '' !== $citd_author['role'] ) : ?>
						<span class="journal-articlemeta__role"><?php echo esc_html( $citd_author['role'] ); ?></span>
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>

		<div class="journal-articlemeta__facts">
			<?php echo citd_journal_dateline( $citd_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template tag. ?>

			<?php if ( get_the_modified_date( 'Ymd' ) !== get_the_date( 'Ymd' ) ) : ?>
				<span class="journal-articlemeta__updated">
					<?php
					printf(
						/* translators: %s: formatted date. */
						esc_html__( 'Updated %s', 'citd-journal' ),
						esc_html( get_the_modified_date( get_option( 'date_format' ) ) )
					);
					?>
				</span>
			<?php endif; ?>

			<?php if ( '' !== $citd_reading ) : ?>
				<span class="journal-articlemeta__reading">
					<?php echo citd_journal_icon( 'clock', array( 'class' => 'journal-icon journal-icon--sm', 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					<span><?php echo esc_html( $citd_reading ); ?></span>
				</span>
			<?php endif; ?>

			<span class="journal-articlemeta__remaining" data-time-remaining hidden></span>
		</div>
	</div>
	<?php
};

/**
 * Render the hero image with its credit.
 *
 * @param string $size Registered image size.
 * @return void
 */
$citd_render_image = static function ( $size ) use ( $citd_id, $citd_credit ) {
	$attachment_id = get_post_thumbnail_id( $citd_id );

	if ( ! $attachment_id ) {
		return;
	}

	$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	?>
	<figure class="journal-articlehero__figure">
		<?php
		echo wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(
				'class'         => 'journal-articlehero__img',
				'alt'           => $alt,
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			)
		);
		?>
		<?php if ( '' !== $citd_credit ) : ?>
			<figcaption class="journal-articlehero__credit"><?php echo esc_html( $citd_credit ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
};
?>

<header class="journal-articlehero journal-articlehero--<?php echo esc_attr( $citd_style ); ?>">

	<?php if ( 'immersive' === $citd_style && $citd_has_img ) : ?>

		<div class="journal-articlehero__canvas">
			<?php $citd_render_image( 'citd-hero' ); ?>
			<div class="journal-articlehero__scrim" aria-hidden="true"></div>
			<div class="journal-shell journal-articlehero__overlay">
				<div class="journal-articlehead">
					<?php $citd_render_titleblock(); ?>
				</div>
			</div>
		</div>

		<div class="journal-shell">
			<?php $citd_render_meta(); ?>
		</div>

	<?php elseif ( 'split' === $citd_style && $citd_has_img ) : ?>

		<div class="journal-shell">
			<div class="journal-articlehero__split">
				<div class="journal-articlehead">
					<?php $citd_render_titleblock(); ?>
					<?php $citd_render_meta(); ?>
				</div>
				<div class="journal-articlehero__splitmedia">
					<?php $citd_render_image( 'citd-feature' ); ?>
				</div>
			</div>
		</div>

	<?php else : ?>

		<div class="journal-shell">
			<div class="journal-articlehead">
				<?php $citd_render_titleblock(); ?>
				<?php $citd_render_meta(); ?>
			</div>
		</div>

		<?php if ( $citd_has_img ) : ?>
			<div class="journal-shell journal-shell--wide">
				<?php $citd_render_image( 'citd-hero' ); ?>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</header>
