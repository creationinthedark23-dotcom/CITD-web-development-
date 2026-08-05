<?php
/**
 * Share controls.
 *
 * Two variants render from the same data:
 *   rail    A vertical rail pinned beside the article on wide viewports.
 *   inline  A horizontal row at the end of the body copy.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type string $variant Either "rail" or "inline".
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_variant = ( isset( $args['variant'] ) && 'rail' === $args['variant'] ) ? 'rail' : 'inline';
$citd_links   = citd_journal_share_links( get_the_ID() );
$citd_url     = get_permalink();
$citd_label   = 'rail' === $citd_variant
	? __( 'Share this article', 'citd-journal' )
	: __( 'Share', 'citd-journal' );
?>

<div class="journal-share journal-share--<?php echo esc_attr( $citd_variant ); ?>">
	<p class="journal-share__label" id="journal-share-label-<?php echo esc_attr( $citd_variant ); ?>">
		<?php echo esc_html( $citd_label ); ?>
	</p>

	<ul class="journal-share__list" aria-labelledby="journal-share-label-<?php echo esc_attr( $citd_variant ); ?>">
		<?php foreach ( $citd_links as $citd_link ) : ?>
			<li class="journal-share__item">
				<a
					class="journal-iconbtn journal-iconbtn--ghost journal-share__link"
					href="<?php echo esc_url( $citd_link['url'] ); ?>"
					<?php echo $citd_link['popup'] ? 'data-share-popup rel="noopener nofollow"' : 'rel="nofollow"'; ?>
				>
					<?php echo citd_journal_icon( $citd_link['key'], array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					<span class="screen-reader-text"><?php echo esc_html( $citd_link['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>

		<li class="journal-share__item">
			<button
				class="journal-iconbtn journal-iconbtn--ghost journal-share__copy"
				type="button"
				data-copy-link="<?php echo esc_url( $citd_url ); ?>"
			>
				<?php echo citd_journal_icon( 'link', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Copy link to this article', 'citd-journal' ); ?></span>
			</button>
		</li>
	</ul>
</div>
