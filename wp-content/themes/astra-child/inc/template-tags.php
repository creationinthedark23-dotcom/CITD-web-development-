<?php
/**
 * Template tags.
 *
 * Small, single-purpose helpers used by the journal templates. Every function
 * that returns markup escapes its own output; every function that returns data
 * returns unescaped values for the caller to escape.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The publication's masthead name.
 *
 * @return string
 */
function citd_journal_name() {
	return (string) apply_filters( 'citd_journal_name', get_theme_mod( 'citd_journal_name', __( 'The Creation Journal', 'citd-journal' ) ) );
}

/**
 * The publisher line shown under the masthead.
 *
 * @return string
 */
function citd_journal_publisher() {
	return (string) apply_filters( 'citd_journal_publisher', get_theme_mod( 'citd_journal_publisher', __( 'Creation in the Dark Holdings', 'citd-journal' ) ) );
}

/**
 * URL of the journal archive.
 *
 * @return string
 */
function citd_journal_archive_url() {
	$url = get_post_type_archive_link( CITD_JOURNAL_CPT );

	return $url ? $url : home_url( '/' . CITD_JOURNAL_SLUG . '/' );
}

/**
 * Reading time in whole minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function citd_journal_reading_time( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id ) {
		return 0;
	}

	$override = get_post_meta( $post_id, '_citd_reading_override', true );

	if ( '' !== $override && (int) $override > 0 ) {
		return (int) $override;
	}

	$stored = (int) get_post_meta( $post_id, '_citd_reading_time', true );

	if ( $stored > 0 ) {
		return $stored;
	}

	// Fall back to an on-the-fly calculation for content imported without a
	// save_post pass.
	$words   = citd_journal_count_words( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) ) );
	$minutes = max( 1, (int) ceil( $words / citd_journal_words_per_minute() ) );

	return $minutes;
}

/**
 * Human-readable reading time, e.g. "12 min read".
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function citd_journal_reading_time_label( $post_id = null ) {
	$minutes = citd_journal_reading_time( $post_id );

	if ( ! $minutes ) {
		return '';
	}

	/* translators: %s: number of minutes. */
	return sprintf( _n( '%s min read', '%s min read', $minutes, 'citd-journal' ), number_format_i18n( $minutes ) );
}

/**
 * Word count for an article.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function citd_journal_word_count( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$stored  = (int) get_post_meta( $post_id, '_citd_word_count', true );

	if ( $stored > 0 ) {
		return $stored;
	}

	return citd_journal_count_words( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) ) );
}

/**
 * The article eyebrow, falling back to the primary topic name.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function citd_journal_eyebrow( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$eyebrow = trim( (string) get_post_meta( $post_id, '_citd_eyebrow', true ) );

	if ( '' !== $eyebrow ) {
		return $eyebrow;
	}

	$topic = citd_journal_primary_topic( $post_id );

	return $topic ? $topic->name : '';
}

/**
 * The article deck / standfirst, falling back to the excerpt.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function citd_journal_deck( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$deck    = trim( (string) get_post_meta( $post_id, '_citd_deck', true ) );

	if ( '' !== $deck ) {
		return $deck;
	}

	$excerpt = get_the_excerpt( $post_id );

	return $excerpt ? wp_strip_all_tags( $excerpt ) : '';
}

/**
 * Hero treatment key for an article.
 *
 * @param int|null $post_id Post ID.
 * @return string One of standard|immersive|split|typographic.
 */
function citd_journal_hero_style( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$style   = (string) get_post_meta( $post_id, '_citd_hero_style', true );
	$allowed = array( 'standard', 'immersive', 'split', 'typographic' );

	if ( ! in_array( $style, $allowed, true ) ) {
		$style = 'standard';
	}

	if ( ! has_post_thumbnail( $post_id ) ) {
		$style = 'typographic';
	}

	return $style;
}

/**
 * Executive summary points.
 *
 * @param int|null $post_id Post ID.
 * @return array List of strings.
 */
function citd_journal_summary_points( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$raw     = (string) get_post_meta( $post_id, '_citd_executive_summary', true );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	$lines  = preg_split( '/\r\n|\r|\n/', $raw );
	$points = array();

	foreach ( $lines as $line ) {
		// Tolerate editors who paste in bullets or dashes.
		$line = trim( preg_replace( '/^\s*(?:[-–—•*·]|\d+[.)])\s*/u', '', $line ) );

		if ( '' !== $line ) {
			$points[] = $line;
		}
	}

	return $points;
}

/**
 * Parsed references.
 *
 * Each line may end with a URL, which is split out and rendered as a link.
 *
 * @param int|null $post_id Post ID.
 * @return array List of arrays with `text` and `url` keys.
 */
function citd_journal_references( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$raw     = (string) get_post_meta( $post_id, '_citd_references', true );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	$lines      = preg_split( '/\r\n|\r|\n/', $raw );
	$references = array();

	foreach ( $lines as $line ) {
		$line = trim( preg_replace( '/^\s*(?:[-–—•*·]|\d+[.)])\s*/u', '', $line ) );

		if ( '' === $line ) {
			continue;
		}

		$url = '';

		if ( preg_match( '#(https?://[^\s<>"\']+)\s*$#i', $line, $matches ) ) {
			$url  = esc_url_raw( rtrim( $matches[1], '.,;' ) );
			$line = trim( substr( $line, 0, - strlen( $matches[1] ) ) );
			$line = rtrim( $line, " \t-–—|" );
		}

		if ( '' === $line && '' !== $url ) {
			$line = $url;
		}

		$references[] = array(
			'text' => $line,
			'url'  => $url,
		);
	}

	return $references;
}

/**
 * The primary topic for an article.
 *
 * Uses the term with the most articles so that the label on a card is the most
 * meaningful one, and falls back to the first assigned term.
 *
 * @param int|null $post_id Post ID.
 * @return WP_Term|null
 */
function citd_journal_primary_topic( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$terms   = get_the_terms( $post_id, CITD_JOURNAL_TAX_TOPIC );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	usort(
		$terms,
		static function ( $a, $b ) {
			if ( $a->parent !== $b->parent ) {
				// Prefer the most specific term.
				return $b->parent <=> $a->parent;
			}

			return $a->term_id <=> $b->term_id;
		}
	);

	return $terms[0];
}

/**
 * The issue term for an article.
 *
 * @param int|null $post_id Post ID.
 * @return WP_Term|null
 */
function citd_journal_issue_term( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$terms   = get_the_terms( $post_id, CITD_JOURNAL_TAX_ISSUE );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	return $terms[0];
}

/**
 * Display label for an issue term, e.g. "Issue 04 · Spring 2026".
 *
 * @param WP_Term|null $term Issue term.
 * @return string
 */
function citd_journal_issue_label( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$number = (int) get_term_meta( $term->term_id, 'citd_issue_number', true );
	$date   = (string) get_term_meta( $term->term_id, 'citd_issue_date', true );

	$parts = array();

	if ( $number > 0 ) {
		/* translators: %s: zero-padded issue number. */
		$parts[] = sprintf( __( 'Issue %s', 'citd-journal' ), str_pad( (string) $number, 2, '0', STR_PAD_LEFT ) );
	} else {
		$parts[] = $term->name;
	}

	if ( '' !== $date ) {
		$parts[] = $date;
	}

	return implode( ' · ', $parts );
}

/**
 * Issue terms, newest first.
 *
 * Ordering by a numeric term meta normally drops terms that have no value for
 * that key, because WP_Term_Query INNER JOINs the meta table. The OR/NOT EXISTS
 * clause keeps unnumbered issues in the result set instead of silently hiding
 * them.
 *
 * @param int $number Maximum number of terms. 0 for all.
 * @return array List of WP_Term objects.
 */
function citd_journal_get_issues( $number = 0 ) {
	$terms = get_terms(
		array(
			'taxonomy'   => CITD_JOURNAL_TAX_ISSUE,
			'hide_empty' => true,
			'number'     => (int) $number,
			'orderby'    => 'meta_value_num',
			'order'      => 'DESC',
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => 'citd_issue_number',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'citd_issue_number',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return $terms;
}

/**
 * Author profile data for the author card.
 *
 * @param int|null $user_id User ID. Defaults to the current post's author.
 * @return array
 */
function citd_journal_author( $user_id = null ) {
	$user_id = $user_id ? (int) $user_id : (int) get_the_author_meta( 'ID' );

	if ( ! $user_id ) {
		return array();
	}

	$org = (string) get_user_meta( $user_id, 'citd_org', true );

	if ( '' === $org ) {
		$org = citd_journal_publisher();
	}

	$portrait_id = (int) get_user_meta( $user_id, 'citd_portrait_id', true );

	return array(
		'id'          => $user_id,
		'name'        => get_the_author_meta( 'display_name', $user_id ),
		'role'        => (string) get_user_meta( $user_id, 'citd_role', true ),
		'org'         => $org,
		'bio'         => (string) get_the_author_meta( 'description', $user_id ),
		'url'         => get_author_posts_url( $user_id ),
		'linkedin'    => (string) get_user_meta( $user_id, 'citd_linkedin', true ),
		'x'           => (string) get_user_meta( $user_id, 'citd_x', true ),
		'website'     => (string) get_user_meta( $user_id, 'citd_website', true ),
		'portrait_id' => $portrait_id,
	);
}

/**
 * Render an author portrait, preferring the uploaded image over the Gravatar.
 *
 * @param array $author Author data from citd_journal_author().
 * @param int   $size   Rendered size in CSS pixels.
 * @return string
 */
function citd_journal_author_portrait( $author, $size = 96 ) {
	if ( empty( $author ) ) {
		return '';
	}

	if ( ! empty( $author['portrait_id'] ) ) {
		$image = wp_get_attachment_image(
			$author['portrait_id'],
			'citd-portrait',
			false,
			array(
				'class'    => 'journal-author__portrait',
				'alt'      => '',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		if ( $image ) {
			return $image;
		}
	}

	return get_avatar(
		$author['id'],
		$size,
		'',
		'',
		array(
			'class'         => 'journal-author__portrait',
			'extra_attr'    => 'loading="lazy" decoding="async"',
			'force_display' => true,
		)
	);
}

/**
 * Share destinations for an article.
 *
 * @param int|null $post_id Post ID.
 * @return array List of arrays with `key`, `label`, `url` and `popup` keys.
 */
function citd_journal_share_links( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$url     = get_permalink( $post_id );
	$title   = get_the_title( $post_id );
	$deck    = citd_journal_deck( $post_id );

	$encoded_url   = rawurlencode( $url );
	$encoded_title = rawurlencode( wp_strip_all_tags( $title ) );

	$links = array(
		array(
			'key'   => 'linkedin',
			'label' => __( 'Share on LinkedIn', 'citd-journal' ),
			'short' => __( 'LinkedIn', 'citd-journal' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded_url,
			'popup' => true,
		),
		array(
			'key'   => 'x',
			'label' => __( 'Share on X', 'citd-journal' ),
			'short' => __( 'X', 'citd-journal' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . $encoded_url . '&text=' . $encoded_title,
			'popup' => true,
		),
		array(
			'key'   => 'facebook',
			'label' => __( 'Share on Facebook', 'citd-journal' ),
			'short' => __( 'Facebook', 'citd-journal' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url,
			'popup' => true,
		),
		array(
			'key'   => 'email',
			'label' => __( 'Share by email', 'citd-journal' ),
			'short' => __( 'Email', 'citd-journal' ),
			'url'   => 'mailto:?subject=' . $encoded_title . '&body=' . rawurlencode( $deck . "\n\n" . $url ),
			'popup' => false,
		),
	);

	/**
	 * Filter the share destinations.
	 *
	 * @param array $links   Share links.
	 * @param int   $post_id Post ID.
	 */
	return apply_filters( 'citd_journal_share_links', $links, $post_id );
}

/**
 * Inline SVG icon.
 *
 * All icons are drawn on a 24×24 grid with a 1.5px stroke to match the
 * publication's hairline rules.
 *
 * @param string $name  Icon key.
 * @param array  $attrs Optional attributes: class, size.
 * @return string
 */
function citd_journal_icon( $name, $attrs = array() ) {
	$paths = array(
		'linkedin'   => '<path d="M6.5 9.5v8M6.5 6.2v.1M11 17.5v-4.3a2.7 2.7 0 0 1 5.4 0v4.3"/><rect x="3.2" y="3.2" width="17.6" height="17.6" rx="2.4"/>',
		'x'          => '<path d="M4 4l16 16M20 4L4 20"/>',
		'facebook'   => '<path d="M14.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5H17.7V3.6A21 21 0 0 0 15.3 3.5c-2.4 0-4 1.45-4 4.1v2.3H8.6V13h2.7v8z"/>',
		'email'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.6 6.6 7.3 5.5a2 2 0 0 0 2.2 0l7.3-5.5"/>',
		'link'       => '<path d="M10.6 13.4a3.6 3.6 0 0 0 5.1 0l2.6-2.6a3.6 3.6 0 1 0-5.1-5.1l-1.3 1.3"/><path d="M13.4 10.6a3.6 3.6 0 0 0-5.1 0l-2.6 2.6a3.6 3.6 0 1 0 5.1 5.1l1.3-1.3"/>',
		'arrow-right' => '<path d="M4 12h15"/><path d="m13 6 6 6-6 6"/>',
		'arrow-left' => '<path d="M20 12H5"/><path d="m11 6-6 6 6 6"/>',
		'arrow-up'   => '<path d="M12 20V5"/><path d="m6 11 6-6 6 6"/>',
		'search'     => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
		'close'      => '<path d="M6 6l12 12M18 6 6 18"/>',
		'menu'       => '<path d="M3.5 7h17M3.5 12h17M3.5 17h17"/>',
		'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.2 5.2l1.4 1.4M17.4 17.4l1.4 1.4M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4"/>',
		'moon'       => '<path d="M20 14.2A8.2 8.2 0 0 1 9.8 4 8.2 8.2 0 1 0 20 14.2Z"/>',
		'quote'      => '<path d="M9.5 6.5C6.9 8 5.5 10.2 5.5 13v4.5h5V12H8.2c.1-1.6.9-2.9 2.4-3.8Zm9 0C15.9 8 14.5 10.2 14.5 13v4.5h5V12h-2.3c.1-1.6.9-2.9 2.4-3.8Z"/>',
		'external'   => '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M18 13.5V19a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 4 19V8a1.5 1.5 0 0 1 1.5-1.5H11"/>',
		'check'      => '<path d="m4.5 12.5 5 5 10-11"/>',
		'clock'      => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5.2l3.2 2"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	$class = isset( $attrs['class'] ) ? $attrs['class'] : 'journal-icon';
	$size  = isset( $attrs['size'] ) ? (int) $attrs['size'] : 24;

	$fill_icons = array( 'facebook', 'quote' );
	$is_fill    = in_array( $name, $fill_icons, true );

	return sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="%3$s" stroke="%4$s" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%5$s</svg>',
		esc_attr( $class ),
		$size,
		$is_fill ? 'currentColor' : 'none',
		$is_fill ? 'none' : 'currentColor',
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, trusted path data.
	);
}

/**
 * Related article IDs, matched on shared topics then issue, newest first.
 *
 * @param int $post_id Post ID.
 * @param int $limit   Maximum number of articles.
 * @return array List of post IDs.
 */
function citd_journal_related_ids( $post_id, $limit = 3 ) {
	$post_id = (int) $post_id;
	$limit   = max( 1, (int) $limit );

	$topics = wp_get_post_terms( $post_id, CITD_JOURNAL_TAX_TOPIC, array( 'fields' => 'ids' ) );
	$issues = wp_get_post_terms( $post_id, CITD_JOURNAL_TAX_ISSUE, array( 'fields' => 'ids' ) );

	$found = array();

	$base_args = array(
		'post_type'              => CITD_JOURNAL_CPT,
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'post__not_in'           => array( $post_id ),
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'fields'                 => 'ids',
		'orderby'                => 'date',
		'order'                  => 'DESC',
	);

	if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) {
		$args             = $base_args;
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => CITD_JOURNAL_TAX_TOPIC,
				'field'    => 'term_id',
				'terms'    => $topics,
			),
		);

		$found = get_posts( $args );
	}

	if ( count( $found ) < $limit && ! is_wp_error( $issues ) && ! empty( $issues ) ) {
		$args                   = $base_args;
		$args['posts_per_page']  = $limit - count( $found );
		$args['post__not_in']    = array_merge( array( $post_id ), $found );
		$args['tax_query']       = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => CITD_JOURNAL_TAX_ISSUE,
				'field'    => 'term_id',
				'terms'    => $issues,
			),
		);

		$found = array_merge( $found, get_posts( $args ) );
	}

	if ( count( $found ) < $limit ) {
		$args                  = $base_args;
		$args['posts_per_page'] = $limit - count( $found );
		$args['post__not_in']   = array_merge( array( $post_id ), $found );

		$found = array_merge( $found, get_posts( $args ) );
	}

	/**
	 * Filter the related article IDs.
	 *
	 * @param array $found   Post IDs.
	 * @param int   $post_id Current post ID.
	 * @param int   $limit   Requested count.
	 */
	return apply_filters( 'citd_journal_related_ids', array_values( array_unique( $found ) ), $post_id, $limit );
}

/**
 * Render the dateline used across cards and articles.
 *
 * @param int|null $post_id Post ID.
 * @return string Escaped markup.
 */
function citd_journal_dateline( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	$published = get_the_date( 'c', $post_id );
	$label     = get_the_date( get_option( 'date_format' ), $post_id );

	return sprintf(
		'<time class="journal-dateline" datetime="%1$s">%2$s</time>',
		esc_attr( $published ),
		esc_html( $label )
	);
}

/**
 * Render a topic pill link.
 *
 * @param WP_Term|null $term  Topic term.
 * @param string       $class Extra class name.
 * @return string Escaped markup.
 */
function citd_journal_topic_pill( $term, $class = '' ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$link = get_term_link( $term );

	if ( is_wp_error( $link ) ) {
		return '';
	}

	$label = CITD_JOURNAL_TAX_ISSUE === $term->taxonomy
		? citd_journal_issue_label( $term )
		: $term->name;

	return sprintf(
		'<a class="journal-pill %1$s" href="%2$s">%3$s</a>',
		esc_attr( $class ),
		esc_url( $link ),
		esc_html( $label )
	);
}

/**
 * Render an article card.
 *
 * @param int   $post_id Post ID.
 * @param array $args    {
 *     @type string $size      Card scale: lead|standard|compact.
 *     @type string $image     Registered image size.
 *     @type bool   $show_deck Whether to print the deck.
 *     @type string $heading   Heading level, e.g. h3.
 *     @type int    $index     Position, used for the running number.
 *     @type bool   $eager     Load the image eagerly (above the fold).
 * }
 * @return void
 */
function citd_journal_card( $post_id, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'size'      => 'standard',
			'image'     => 'citd-card',
			'show_deck' => true,
			'heading'   => 'h3',
			'index'     => 0,
			'eager'     => false,
		)
	);

	$heading_tag = preg_match( '/^h[1-6]$/', $args['heading'] ) ? $args['heading'] : 'h3';
	$topic       = citd_journal_primary_topic( $post_id );
	$deck        = citd_journal_deck( $post_id );
	$permalink   = get_permalink( $post_id );
	$reading     = citd_journal_reading_time_label( $post_id );
	?>
	<article class="journal-card journal-card--<?php echo esc_attr( $args['size'] ); ?><?php echo has_post_thumbnail( $post_id ) ? '' : ' journal-card--textual'; ?>" data-reveal>
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<a class="journal-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
				<?php
				echo wp_get_attachment_image(
					get_post_thumbnail_id( $post_id ),
					$args['image'],
					false,
					array(
						'class'         => 'journal-card__img',
						'alt'           => '',
						'loading'       => $args['eager'] ? 'eager' : 'lazy',
						'decoding'      => 'async',
						'fetchpriority' => $args['eager'] ? 'high' : 'auto',
					)
				);
				?>
			</a>
		<?php endif; ?>

		<div class="journal-card__body">
			<div class="journal-card__meta">
				<?php if ( $args['index'] > 0 ) : ?>
					<span class="journal-card__index" aria-hidden="true"><?php echo esc_html( str_pad( (string) $args['index'], 2, '0', STR_PAD_LEFT ) ); ?></span>
				<?php endif; ?>
				<?php if ( $topic ) : ?>
					<span class="journal-card__topic"><?php echo esc_html( $topic->name ); ?></span>
				<?php endif; ?>
				<?php echo citd_journal_dateline( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template tag. ?>
			</div>

			<<?php echo esc_html( $heading_tag ); ?> class="journal-card__title">
				<a class="journal-card__link" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
			</<?php echo esc_html( $heading_tag ); ?>>

			<?php if ( $args['show_deck'] && '' !== $deck ) : ?>
				<p class="journal-card__deck"><?php echo esc_html( wp_trim_words( $deck, 'lead' === $args['size'] ? 40 : 24, '…' ) ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $reading ) : ?>
				<p class="journal-card__reading">
					<?php echo citd_journal_icon( 'clock', array( 'class' => 'journal-icon journal-icon--sm', 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					<span><?php echo esc_html( $reading ); ?></span>
				</p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Render breadcrumbs for the article header.
 *
 * @return void
 */
function citd_journal_breadcrumbs() {
	$topic = citd_journal_primary_topic();
	?>
	<nav class="journal-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'citd-journal' ); ?>">
		<ol class="journal-breadcrumbs__list">
			<li class="journal-breadcrumbs__item">
				<a href="<?php echo esc_url( citd_journal_archive_url() ); ?>"><?php echo esc_html( citd_journal_name() ); ?></a>
			</li>
			<?php if ( $topic ) : ?>
				<li class="journal-breadcrumbs__item">
					<a href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a>
				</li>
			<?php endif; ?>
			<li class="journal-breadcrumbs__item" aria-current="page">
				<span><?php echo esc_html( wp_trim_words( get_the_title(), 8, '…' ) ); ?></span>
			</li>
		</ol>
	</nav>
	<?php
}

/**
 * Social profile links configured for the publication.
 *
 * @return array
 */
function citd_journal_social_profiles() {
	$profiles = array(
		'linkedin' => get_theme_mod( 'citd_journal_social_linkedin', '' ),
		'x'        => get_theme_mod( 'citd_journal_social_x', '' ),
		'facebook' => get_theme_mod( 'citd_journal_social_facebook', '' ),
	);

	return array_filter( $profiles );
}
