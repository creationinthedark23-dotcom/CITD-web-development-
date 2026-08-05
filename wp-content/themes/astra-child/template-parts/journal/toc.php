<?php
/**
 * Sticky table of contents.
 *
 * Rendered as a <nav> on wide viewports and collapsed into a <details>
 * disclosure on narrow ones — the same markup serves both, so there is no
 * duplicate list for screen readers to walk twice.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type array $headings Collected H2/H3 headings.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_headings = isset( $args['headings'] ) ? (array) $args['headings'] : array();

if ( empty( $citd_headings ) ) {
	return;
}
?>

<nav class="journal-toc" id="journal-contents" aria-labelledby="journal-toc-title" data-toc>
	<details class="journal-toc__disclosure" open>
		<summary class="journal-toc__summary">
			<span class="journal-toc__title" id="journal-toc-title"><?php esc_html_e( 'Contents', 'citd-journal' ); ?></span>
			<span class="journal-toc__chevron" aria-hidden="true"></span>
		</summary>

		<ol class="journal-toc__list">
			<?php foreach ( $citd_headings as $citd_heading ) : ?>
				<li class="journal-toc__item journal-toc__item--level-<?php echo esc_attr( $citd_heading['level'] ); ?>">
					<a class="journal-toc__link" href="#<?php echo esc_attr( $citd_heading['id'] ); ?>" data-toc-link="<?php echo esc_attr( $citd_heading['id'] ); ?>">
						<span class="journal-toc__marker" aria-hidden="true"></span>
						<span class="journal-toc__label"><?php echo esc_html( $citd_heading['text'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</details>
</nav>
