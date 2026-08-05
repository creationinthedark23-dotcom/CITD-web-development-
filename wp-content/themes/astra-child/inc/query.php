<?php
/**
 * Query tuning for the journal archive.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Number of articles in the archive grid, per page.
 *
 * @return int
 */
function citd_journal_archive_per_page() {
	/**
	 * Filter the archive page size.
	 *
	 * @param int $per_page Articles per page.
	 */
	return max( 1, (int) apply_filters( 'citd_journal_archive_per_page', 12 ) );
}

/**
 * The ID of the article promoted to the top of /journal/.
 *
 * Resolution order:
 *   1. The most recently published article flagged "Feature as the latest issue".
 *   2. The most recently published article.
 *
 * @return int Post ID, or 0 when the journal is empty.
 */
function citd_journal_featured_id() {
	static $resolved = null;

	if ( null !== $resolved ) {
		return $resolved;
	}

	$cached = get_transient( 'citd_journal_featured_id' );

	if ( false !== $cached ) {
		$resolved = (int) $cached;

		return $resolved;
	}

	$args = array(
		'post_type'              => CITD_JOURNAL_CPT,
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_term_cache' => false,
		'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_citd_featured',
				'value'   => '1',
				'compare' => '=',
			),
		),
	);

	$ids = get_posts( $args );

	if ( empty( $ids ) ) {
		unset( $args['meta_query'] );
		$ids = get_posts( $args );
	}

	$resolved = ! empty( $ids ) ? (int) $ids[0] : 0;

	set_transient( 'citd_journal_featured_id', $resolved, HOUR_IN_SECONDS );

	return $resolved;
}

/**
 * Invalidate the featured-article cache whenever the journal changes.
 *
 * @return void
 */
function citd_journal_flush_featured_cache() {
	delete_transient( 'citd_journal_featured_id' );
}
add_action( 'save_post_' . CITD_JOURNAL_CPT, 'citd_journal_flush_featured_cache' );
add_action( 'deleted_post', 'citd_journal_flush_featured_cache' );
add_action( 'trashed_post', 'citd_journal_flush_featured_cache' );
add_action( 'untrashed_post', 'citd_journal_flush_featured_cache' );

/**
 * Tune the main query on the journal archive.
 *
 * The featured article occupies its own slot at the top of page one, so it is
 * excluded from the grid there but remains in the paginated list.
 *
 * @param WP_Query $query Query being prepared.
 * @return void
 */
function citd_journal_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$is_archive = $query->is_post_type_archive( CITD_JOURNAL_CPT );
	$is_tax     = $query->is_tax( array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ) );

	if ( ! $is_archive && ! $is_tax ) {
		return;
	}

	$query->set( 'posts_per_page', citd_journal_archive_per_page() );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );

	if ( $is_archive ) {
		$paged = max( 1, (int) $query->get( 'paged' ) );

		if ( 1 === $paged ) {
			$featured = citd_journal_featured_id();

			if ( $featured ) {
				$query->set( 'post__not_in', array( $featured ) );
			}
		}
	}
}
add_action( 'pre_get_posts', 'citd_journal_pre_get_posts' );

/**
 * Restrict the journal search form to publications.
 *
 * @param WP_Query $query Query being prepared.
 * @return void
 */
function citd_journal_scope_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	// The journal search form submits `journal=1`; without it, site search is
	// left untouched.
	if ( empty( $_GET['journal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$query->set( 'post_type', CITD_JOURNAL_CPT );
	$query->set( 'posts_per_page', citd_journal_archive_per_page() );
}
add_action( 'pre_get_posts', 'citd_journal_scope_search' );

/**
 * Use the journal archive template for journal searches and taxonomies so the
 * publication keeps one consistent index layout.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function citd_journal_template_include( $template ) {
	if ( is_tax( array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ) ) ) {
		$archive = locate_template( 'archive-publication.php' );

		if ( $archive ) {
			return $archive;
		}
	}

	if ( is_search() && ! empty( $_GET['journal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$archive = locate_template( 'archive-publication.php' );

		if ( $archive ) {
			return $archive;
		}
	}

	return $template;
}
add_filter( 'template_include', 'citd_journal_template_include', 20 );

/**
 * Provide the "previous / next article" pair for the single template.
 *
 * Adjacency follows publication date across the whole journal rather than
 * within a taxonomy, so readers always move along the editorial timeline.
 *
 * @return array {
 *     @type WP_Post|null $previous Older article.
 *     @type WP_Post|null $next     Newer article.
 * }
 */
function citd_journal_adjacent_articles() {
	$previous = get_previous_post( false );
	$next     = get_next_post( false );

	return array(
		'previous' => $previous instanceof WP_Post ? $previous : null,
		'next'     => $next instanceof WP_Post ? $next : null,
	);
}
