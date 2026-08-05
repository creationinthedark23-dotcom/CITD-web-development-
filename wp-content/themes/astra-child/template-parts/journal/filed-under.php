<?php
/**
 * "Filed under" — every topic and issue an article belongs to.
 *
 * The card and hero only ever show the primary topic; this strip is where the
 * complete classification lives.
 *
 * @package CITD_Journal
 * @since   1.0.0
 *
 * @var array $args {
 *     @type int $post_id Post ID.
 * }
 */

defined( 'ABSPATH' ) || exit;

$citd_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();

if ( ! $citd_id ) {
	return;
}

$citd_terms = array();

foreach ( array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ) as $citd_taxonomy ) {
	$citd_assigned = get_the_terms( $citd_id, $citd_taxonomy );

	if ( ! is_wp_error( $citd_assigned ) && ! empty( $citd_assigned ) ) {
		$citd_terms = array_merge( $citd_terms, $citd_assigned );
	}
}

if ( empty( $citd_terms ) ) {
	return;
}
?>

<nav class="journal-filed" aria-labelledby="journal-filed-label">
	<p class="journal-filed__label" id="journal-filed-label">
		<?php esc_html_e( 'Filed under', 'citd-journal' ); ?>
	</p>

	<ul class="journal-filed__list">
		<?php foreach ( $citd_terms as $citd_term ) : ?>
			<li>
				<?php
				echo citd_journal_topic_pill( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template tag.
					$citd_term,
					CITD_JOURNAL_TAX_ISSUE === $citd_term->taxonomy ? 'journal-pill--issue' : ''
				);
				?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
