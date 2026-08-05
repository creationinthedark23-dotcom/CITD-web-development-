<?php
/**
 * Search-engine and social metadata.
 *
 * The theme defers entirely to Yoast SEO, Rank Math, SEOPress or All in One SEO
 * when one of them is active, so nothing is emitted twice.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a dedicated SEO plugin is handling head metadata.
 *
 * @return bool
 */
function citd_journal_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| class_exists( 'The_SEO_Framework\\Load' );

	/**
	 * Filter whether the theme should stand down from emitting metadata.
	 *
	 * @param bool $active Whether an SEO plugin is active.
	 */
	return (bool) apply_filters( 'citd_journal_seo_plugin_active', $active );
}

/**
 * A clean, plain-text description for the current view.
 *
 * @return string
 */
function citd_journal_meta_description() {
	if ( is_singular( CITD_JOURNAL_CPT ) ) {
		$description = citd_journal_deck( get_the_ID() );

		if ( '' === $description ) {
			$description = wp_strip_all_tags( get_the_excerpt() );
		}

		return wp_trim_words( $description, 34, '…' );
	}

	if ( is_tax( array( CITD_JOURNAL_TAX_TOPIC, CITD_JOURNAL_TAX_ISSUE ) ) ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$standfirst = (string) get_term_meta( $term->term_id, 'citd_topic_standfirst', true );
			$text       = '' !== $standfirst ? $standfirst : wp_strip_all_tags( $term->description );

			if ( '' !== $text ) {
				return wp_trim_words( $text, 34, '…' );
			}

			return sprintf(
				/* translators: 1: term name, 2: publication name. */
				__( '%1$s — articles and analysis from %2$s.', 'citd-journal' ),
				$term->name,
				citd_journal_name()
			);
		}
	}

	return sprintf(
		/* translators: 1: publication name, 2: publisher name. */
		__( '%1$s is the digital publication of %2$s: long-form research, analysis and field notes.', 'citd-journal' ),
		citd_journal_name(),
		citd_journal_publisher()
	);
}

/**
 * The social sharing image for the current view.
 *
 * @return array|null Array with url, width, height and alt, or null.
 */
function citd_journal_social_image() {
	$attachment_id = 0;

	if ( is_singular( CITD_JOURNAL_CPT ) && has_post_thumbnail() ) {
		$attachment_id = get_post_thumbnail_id();
	} else {
		$custom = (int) get_theme_mod( 'citd_journal_default_share_image', 0 );

		if ( $custom ) {
			$attachment_id = $custom;
		}
	}

	if ( ! $attachment_id ) {
		return null;
	}

	$source = wp_get_attachment_image_src( $attachment_id, 'citd-hero' );

	if ( ! $source ) {
		return null;
	}

	return array(
		'url'    => $source[0],
		'width'  => (int) $source[1],
		'height' => (int) $source[2],
		'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * Emit canonical, description, Open Graph and Twitter card tags.
 *
 * @return void
 */
function citd_journal_head_meta() {
	if ( ! citd_journal_is_journal_context() || citd_journal_seo_plugin_active() ) {
		return;
	}

	$description = citd_journal_meta_description();
	$image       = citd_journal_social_image();
	$site_name   = citd_journal_name();

	if ( is_singular( CITD_JOURNAL_CPT ) ) {
		$title = get_the_title();
		$url   = get_permalink();
		$type  = 'article';
	} elseif ( is_tax() ) {
		$term  = get_queried_object();
		$title = $term instanceof WP_Term ? $term->name : $site_name;
		$url   = $term instanceof WP_Term ? get_term_link( $term ) : citd_journal_archive_url();
		$type  = 'website';
	} else {
		$title = $site_name;
		$url   = citd_journal_archive_url();
		$type  = 'website';
	}

	if ( is_wp_error( $url ) ) {
		$url = citd_journal_archive_url();
	}

	echo "\n" . '<!-- The Creation Journal metadata -->' . "\n";

	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );

	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) );

	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image['url'] ) );
		printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image['width'] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image['height'] );

		if ( '' !== $image['alt'] ) {
			printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $image['alt'] ) );
		}
	}

	if ( is_singular( CITD_JOURNAL_CPT ) ) {
		printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( 'c' ) ) );
		printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( 'c' ) ) );
		printf( '<meta property="article:author" content="%s">' . "\n", esc_attr( get_the_author_meta( 'display_name' ) ) );

		$topics = get_the_terms( get_the_ID(), CITD_JOURNAL_TAX_TOPIC );

		if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) {
			printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $topics[0]->name ) );

			foreach ( $topics as $topic ) {
				printf( '<meta property="article:tag" content="%s">' . "\n", esc_attr( $topic->name ) );
			}
		}
	}

	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );

	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image['url'] ) );
	}

	$twitter_handle = get_theme_mod( 'citd_journal_twitter_handle', '' );

	if ( $twitter_handle ) {
		printf( '<meta name="twitter:site" content="@%s">' . "\n", esc_attr( ltrim( $twitter_handle, '@' ) ) );
	}
}
add_action( 'wp_head', 'citd_journal_head_meta', 3 );

/**
 * Emit JSON-LD structured data.
 *
 * Singles receive an Article graph with author, publisher, word count and
 * breadcrumbs. The archive receives a CollectionPage with an ItemList.
 *
 * @return void
 */
function citd_journal_structured_data() {
	if ( ! citd_journal_is_journal_context() ) {
		return;
	}

	$publisher = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => citd_journal_publisher(),
		'url'   => home_url( '/' ),
	);

	$logo_id = (int) get_theme_mod( 'citd_journal_logo_id', 0 );

	if ( $logo_id ) {
		$logo = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( $logo ) {
			$publisher['logo'] = array(
				'@type'  => 'ImageObject',
				'url'    => $logo[0],
				'width'  => (int) $logo[1],
				'height' => (int) $logo[2],
			);
		}
	}

	$graph = array();

	if ( is_singular( CITD_JOURNAL_CPT ) ) {
		$post_id = get_the_ID();

		$article = array(
			'@type'            => 'Article',
			'@id'              => get_permalink() . '#article',
			'headline'         => wp_strip_all_tags( get_the_title() ),
			'description'      => citd_journal_meta_description(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'wordCount'        => citd_journal_word_count( $post_id ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink(),
			),
			'isPartOf'         => array(
				'@type' => 'Periodical',
				'@id'   => citd_journal_archive_url() . '#periodical',
				'name'  => citd_journal_name(),
			),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name' ),
				'url'   => get_author_posts_url( (int) get_the_author_meta( 'ID' ) ),
			),
			'publisher'        => $publisher,
		);

		$image = citd_journal_social_image();

		if ( $image ) {
			$article['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $image['url'],
				'width'  => $image['width'],
				'height' => $image['height'],
			);
		}

		$topics = get_the_terms( $post_id, CITD_JOURNAL_TAX_TOPIC );

		if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) {
			$article['articleSection'] = wp_list_pluck( $topics, 'name' );
		}

		$graph[] = $article;

		$crumbs = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => citd_journal_name(),
				'item'     => citd_journal_archive_url(),
			),
		);

		$primary = citd_journal_primary_topic( $post_id );

		if ( $primary ) {
			$term_link = get_term_link( $primary );

			if ( ! is_wp_error( $term_link ) ) {
				$crumbs[] = array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $primary->name,
					'item'     => $term_link,
				);
			}
		}

		$crumbs[] = array(
			'@type'    => 'ListItem',
			'position' => count( $crumbs ) + 1,
			'name'     => wp_strip_all_tags( get_the_title() ),
		);

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink() . '#breadcrumbs',
			'itemListElement' => $crumbs,
		);
	} else {
		$items    = array();
		$position = 1;

		if ( have_posts() ) {
			global $wp_query;

			foreach ( $wp_query->posts as $item ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $position,
					'url'      => get_permalink( $item ),
					'name'     => wp_strip_all_tags( get_the_title( $item ) ),
				);

				++$position;
			}
		}

		$graph[] = array(
			'@type'       => 'CollectionPage',
			'@id'         => citd_journal_archive_url() . '#collection',
			'name'        => citd_journal_name(),
			'description' => citd_journal_meta_description(),
			'isPartOf'    => array(
				'@type' => 'Periodical',
				'@id'   => citd_journal_archive_url() . '#periodical',
				'name'  => citd_journal_name(),
			),
			'publisher'   => $publisher,
			'mainEntity'  => array(
				'@type'           => 'ItemList',
				'itemListElement' => $items,
			),
		);
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	/**
	 * Filter the JSON-LD payload before output.
	 *
	 * @param array $payload Structured data graph.
	 */
	$payload = apply_filters( 'citd_journal_structured_data', $payload );

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded.
	);
}
add_action( 'wp_head', 'citd_journal_structured_data', 20 );

/**
 * Give the journal archive a proper document title.
 *
 * @param array $parts Title parts.
 * @return array
 */
function citd_journal_document_title( $parts ) {
	if ( is_post_type_archive( CITD_JOURNAL_CPT ) ) {
		$parts['title'] = citd_journal_name();
	}

	return $parts;
}
add_filter( 'document_title_parts', 'citd_journal_document_title' );
