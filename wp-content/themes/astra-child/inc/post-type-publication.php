<?php
/**
 * The `publication` custom post type.
 *
 * Archive:  /journal/
 * Single:   /journal/article-name/
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the publication post type.
 *
 * @return void
 */
function citd_journal_register_post_type() {
	$labels = array(
		'name'                     => _x( 'Publications', 'post type general name', 'citd-journal' ),
		'singular_name'            => _x( 'Publication', 'post type singular name', 'citd-journal' ),
		'menu_name'                => _x( 'The Journal', 'admin menu', 'citd-journal' ),
		'name_admin_bar'           => _x( 'Publication', 'add new on admin bar', 'citd-journal' ),
		'add_new'                  => __( 'Add Article', 'citd-journal' ),
		'add_new_item'             => __( 'Add New Article', 'citd-journal' ),
		'new_item'                 => __( 'New Article', 'citd-journal' ),
		'edit_item'                => __( 'Edit Article', 'citd-journal' ),
		'view_item'                => __( 'View Article', 'citd-journal' ),
		'view_items'               => __( 'View Journal', 'citd-journal' ),
		'all_items'                => __( 'All Articles', 'citd-journal' ),
		'search_items'             => __( 'Search Articles', 'citd-journal' ),
		'parent_item_colon'        => __( 'Parent Article:', 'citd-journal' ),
		'not_found'                => __( 'No articles found.', 'citd-journal' ),
		'not_found_in_trash'       => __( 'No articles found in Trash.', 'citd-journal' ),
		'featured_image'           => __( 'Hero Image', 'citd-journal' ),
		'set_featured_image'       => __( 'Set hero image', 'citd-journal' ),
		'remove_featured_image'    => __( 'Remove hero image', 'citd-journal' ),
		'use_featured_image'       => __( 'Use as hero image', 'citd-journal' ),
		'archives'                 => __( 'Journal Archive', 'citd-journal' ),
		'insert_into_item'         => __( 'Insert into article', 'citd-journal' ),
		'uploaded_to_this_item'    => __( 'Uploaded to this article', 'citd-journal' ),
		'filter_items_list'        => __( 'Filter articles list', 'citd-journal' ),
		'items_list_navigation'    => __( 'Articles list navigation', 'citd-journal' ),
		'items_list'               => __( 'Articles list', 'citd-journal' ),
		'item_published'           => __( 'Article published.', 'citd-journal' ),
		'item_published_privately' => __( 'Article published privately.', 'citd-journal' ),
		'item_reverted_to_draft'   => __( 'Article reverted to draft.', 'citd-journal' ),
		'item_scheduled'           => __( 'Article scheduled.', 'citd-journal' ),
		'item_updated'             => __( 'Article updated.', 'citd-journal' ),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __( 'Long-form articles published in The Creation Journal.', 'citd-journal' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'show_in_rest'       => true,
		'rest_base'          => 'publications',
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-book-alt',
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
		'hierarchical'       => false,
		'has_archive'        => CITD_JOURNAL_SLUG,
		'rewrite'            => array(
			'slug'       => CITD_JOURNAL_SLUG,
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		),
		'query_var'          => true,
		'can_export'         => true,
		'delete_with_user'   => false,
		'supports'           => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'revisions',
			'custom-fields',
			'page-attributes',
		),
		'taxonomies'         => array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ),
	);

	register_post_type( CITD_JOURNAL_CPT, $args );
}
add_action( 'init', 'citd_journal_register_post_type', 5 );

/**
 * Register the private subscriber store used by the newsletter module.
 *
 * @return void
 */
function citd_journal_register_subscriber_post_type() {
	register_post_type(
		CITD_JOURNAL_SUBSCRIBER_CPT,
		array(
			'labels'              => array(
				'name'          => __( 'Journal Subscribers', 'citd-journal' ),
				'singular_name' => __( 'Journal Subscriber', 'citd-journal' ),
				'menu_name'     => __( 'Subscribers', 'citd-journal' ),
				'all_items'     => __( 'Subscribers', 'citd-journal' ),
				'search_items'  => __( 'Search Subscribers', 'citd-journal' ),
				'not_found'     => __( 'No subscribers yet.', 'citd-journal' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=' . CITD_JOURNAL_CPT,
			'show_in_rest'        => false,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
		)
	);
}
add_action( 'init', 'citd_journal_register_subscriber_post_type', 5 );

/**
 * Replace the default excerpt ellipsis with a typographic one.
 *
 * @return string
 */
function citd_journal_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'citd_journal_excerpt_more' );

/**
 * Longer auto-excerpts suit editorial card layouts.
 *
 * @param int $length Default word count.
 * @return int
 */
function citd_journal_excerpt_length( $length ) {
	if ( CITD_JOURNAL_CPT === get_post_type() ) {
		return 34;
	}

	return $length;
}
add_filter( 'excerpt_length', 'citd_journal_excerpt_length' );

/**
 * Add editorial columns to the admin list table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function citd_journal_admin_columns( $columns ) {
	$reordered = array();

	foreach ( $columns as $key => $label ) {
		$reordered[ $key ] = $label;

		if ( 'title' === $key ) {
			$reordered['citd_featured'] = __( 'Featured', 'citd-journal' );
			$reordered['citd_reading']  = __( 'Read', 'citd-journal' );
		}
	}

	return $reordered;
}
add_filter( 'manage_' . CITD_JOURNAL_CPT . '_posts_columns', 'citd_journal_admin_columns' );

/**
 * Render the custom admin columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function citd_journal_admin_column_content( $column, $post_id ) {
	if ( 'citd_featured' === $column ) {
		$is_featured = (bool) get_post_meta( $post_id, '_citd_featured', true );
		echo $is_featured
			? '<span aria-hidden="true">★</span><span class="screen-reader-text">' . esc_html__( 'Featured', 'citd-journal' ) . '</span>'
			: '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'Not featured', 'citd-journal' ) . '</span>';
	}

	if ( 'citd_reading' === $column ) {
		$minutes = (int) get_post_meta( $post_id, '_citd_reading_time', true );
		/* translators: %d: number of minutes. */
		echo $minutes ? esc_html( sprintf( _n( '%d min', '%d min', $minutes, 'citd-journal' ), $minutes ) ) : '—';
	}
}
add_action( 'manage_' . CITD_JOURNAL_CPT . '_posts_custom_column', 'citd_journal_admin_column_content', 10, 2 );
