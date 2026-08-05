<?php
/**
 * Article body processing.
 *
 * The rendered content is parsed once per request. During that pass the theme:
 *
 *   1. Gives every H2/H3 a stable, unique, human-readable id.
 *   2. Collects those headings so the sticky contents rail can be printed
 *      before the body markup in the DOM.
 *   3. Wraps tables in a horizontally scrollable, focusable container.
 *   4. Hardens images: lazy loading, async decoding, intrinsic dimensions.
 *   5. Flags the first paragraph so it can carry the opening drop cap.
 *   6. Marks external links and adds the appropriate rel attributes.
 *
 * The result is memoised per post so the templates can call it freely.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parse and cache the processed article body.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return array {
 *     @type string $html     Processed body HTML.
 *     @type array  $headings List of headings: id, text, level.
 * }
 */
function citd_journal_get_article( $post_id = null ) {
	static $cache = array();

	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id ) {
		return array(
			'html'     => '',
			'headings' => array(),
		);
	}

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	// get_the_content() with an explicit post runs generate_postdata(), so
	// <!--nextpage--> splitting is honoured whether or not we are in the loop.
	$raw = get_the_content( null, false, $post_id );

	if ( '' === $raw ) {
		$raw = get_post_field( 'post_content', $post_id );
	}

	/** This filter is documented in wp-includes/post-template.php */
	$html = apply_filters( 'the_content', $raw );
	$html = str_replace( ']]>', ']]&gt;', $html );

	$result = citd_journal_process_html( $html );

	$cache[ $post_id ] = $result;

	return $result;
}

/**
 * Run the DOM pass over a block of article HTML.
 *
 * @param string $html Rendered content.
 * @return array
 */
function citd_journal_process_html( $html ) {
	$fallback = array(
		'html'     => $html,
		'headings' => array(),
	);

	if ( '' === trim( $html ) || ! class_exists( 'DOMDocument' ) ) {
		return $fallback;
	}

	$dom = new DOMDocument( '1.0', 'UTF-8' );

	$previous = libxml_use_internal_errors( true );

	$loaded = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="citd-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded ) {
		return $fallback;
	}

	$xpath = new DOMXPath( $dom );

	// DOMDocument::getElementById() is unreliable for HTML fragments, so the
	// wrapper is located explicitly.
	$found = $xpath->query( '//*[@id="citd-root"]' );
	$root  = ( $found && $found->length ) ? $found->item( 0 ) : $dom->documentElement;

	if ( ! $root instanceof DOMElement ) {
		return $fallback;
	}

	$headings = citd_journal_index_headings( $dom, $xpath, $root );

	citd_journal_wrap_tables( $dom, $xpath, $root );
	citd_journal_harden_media( $xpath, $root );
	citd_journal_mark_opening_paragraph( $xpath, $root );
	citd_journal_mark_external_links( $xpath, $root );

	$output = '';

	foreach ( $root->childNodes as $child ) {
		$output .= $dom->saveHTML( $child );
	}

	return array(
		'html'     => $output,
		'headings' => $headings,
	);
}

/**
 * Give headings stable ids and return the contents index.
 *
 * @param DOMDocument $dom     Document.
 * @param DOMXPath    $xpath   XPath helper.
 * @param DOMElement  $root    Root container.
 * @return array
 */
function citd_journal_index_headings( $dom, $xpath, $root ) {
	unset( $dom );

	$headings = array();
	$used     = array();
	$nodes    = $xpath->query( './/h2 | .//h3', $root );

	if ( ! $nodes ) {
		return $headings;
	}

	foreach ( $nodes as $node ) {
		$text = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );

		if ( '' === $text ) {
			continue;
		}

		$id = $node->getAttribute( 'id' );

		if ( '' === $id ) {
			$id = sanitize_title( $text );
		}

		if ( '' === $id ) {
			$id = 'section';
		}

		$base    = $id;
		$counter = 2;

		while ( isset( $used[ $id ] ) ) {
			$id = $base . '-' . $counter;
			++$counter;
		}

		$used[ $id ] = true;

		$node->setAttribute( 'id', $id );

		$existing_class = $node->getAttribute( 'class' );
		$node->setAttribute( 'class', trim( $existing_class . ' journal-heading' ) );
		$node->setAttribute( 'tabindex', '-1' );

		$headings[] = array(
			'id'    => $id,
			'text'  => $text,
			'level' => (int) substr( $node->nodeName, 1 ),
		);
	}

	return $headings;
}

/**
 * Wrap tables so wide data scrolls inside its own container rather than
 * breaking the page layout. The wrapper is focusable and labelled so keyboard
 * and screen-reader users can reach the overflow.
 *
 * @param DOMDocument $dom   Document.
 * @param DOMXPath    $xpath XPath helper.
 * @param DOMElement  $root  Root container.
 * @return void
 */
function citd_journal_wrap_tables( $dom, $xpath, $root ) {
	$tables = $xpath->query( './/table', $root );

	if ( ! $tables ) {
		return;
	}

	// Snapshot the list first: the DOM is mutated inside the loop.
	$list = array();

	foreach ( $tables as $table ) {
		$list[] = $table;
	}

	foreach ( $list as $table ) {
		$parent = $table->parentNode;

		if ( ! $parent ) {
			continue;
		}

		if ( $parent instanceof DOMElement && false !== strpos( $parent->getAttribute( 'class' ), 'journal-table' ) ) {
			continue;
		}

		$wrapper = $dom->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'journal-table' );
		$wrapper->setAttribute( 'role', 'region' );
		$wrapper->setAttribute( 'tabindex', '0' );
		$wrapper->setAttribute( 'aria-label', __( 'Table, scrollable', 'citd-journal' ) );

		$parent->replaceChild( $wrapper, $table );
		$wrapper->appendChild( $table );
	}
}

/**
 * Apply loading, decoding and sizing hints to inline media.
 *
 * @param DOMXPath   $xpath XPath helper.
 * @param DOMElement $root  Root container.
 * @return void
 */
function citd_journal_harden_media( $xpath, $root ) {
	$images = $xpath->query( './/img', $root );

	if ( $images ) {
		foreach ( $images as $image ) {
			if ( '' === $image->getAttribute( 'loading' ) ) {
				$image->setAttribute( 'loading', 'lazy' );
			}

			if ( '' === $image->getAttribute( 'decoding' ) ) {
				$image->setAttribute( 'decoding', 'async' );
			}

			if ( '' === $image->getAttribute( 'alt' ) && '' === $image->getAttribute( 'role' ) ) {
				// An image with no alternative text is decorative by definition;
				// make that explicit rather than leaving assistive technology to
				// announce the file name.
				$image->setAttribute( 'alt', '' );
				$image->setAttribute( 'role', 'presentation' );
			}

			$class = $image->getAttribute( 'class' );
			$image->setAttribute( 'class', trim( $class . ' journal-media__img' ) );
		}
	}

	$iframes = $xpath->query( './/iframe', $root );

	if ( $iframes ) {
		foreach ( $iframes as $iframe ) {
			if ( '' === $iframe->getAttribute( 'loading' ) ) {
				$iframe->setAttribute( 'loading', 'lazy' );
			}

			if ( '' === $iframe->getAttribute( 'title' ) ) {
				$iframe->setAttribute( 'title', __( 'Embedded content', 'citd-journal' ) );
			}
		}
	}
}

/**
 * Tag the first paragraph of the article so CSS can set the opening drop cap.
 *
 * @param DOMXPath   $xpath XPath helper.
 * @param DOMElement $root  Root container.
 * @return void
 */
function citd_journal_mark_opening_paragraph( $xpath, $root ) {
	$paragraphs = $xpath->query(
		'.//p[normalize-space(.) != "" and not(ancestor::figure) and not(ancestor::blockquote) and not(ancestor::li) and not(ancestor::table) and not(ancestor::aside)]',
		$root
	);

	if ( ! $paragraphs || 0 === $paragraphs->length ) {
		return;
	}

	$first = $paragraphs->item( 0 );

	if ( ! $first instanceof DOMElement ) {
		return;
	}

	$text = trim( $first->textContent );

	// A drop cap only reads well on a substantial opening paragraph that starts
	// with a letter.
	if ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) < 120 : strlen( $text ) < 120 ) {
		return;
	}

	if ( ! preg_match( '/^\p{L}/u', $text ) ) {
		return;
	}

	$class = $first->getAttribute( 'class' );
	$first->setAttribute( 'class', trim( $class . ' journal-opening' ) );
}

/**
 * Mark links that leave the site and give them safe rel attributes.
 *
 * @param DOMXPath   $xpath XPath helper.
 * @param DOMElement $root  Root container.
 * @return void
 */
function citd_journal_mark_external_links( $xpath, $root ) {
	$links = $xpath->query( './/a[@href]', $root );

	if ( ! $links ) {
		return;
	}

	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	foreach ( $links as $link ) {
		$href = $link->getAttribute( 'href' );

		if ( '' === $href || 0 === strpos( $href, '#' ) || 0 === strpos( $href, '/' ) ) {
			continue;
		}

		if ( 0 === strpos( $href, 'mailto:' ) || 0 === strpos( $href, 'tel:' ) ) {
			continue;
		}

		$link_host = wp_parse_url( $href, PHP_URL_HOST );

		if ( ! $link_host || $link_host === $host ) {
			continue;
		}

		$class = $link->getAttribute( 'class' );
		$link->setAttribute( 'class', trim( $class . ' journal-link--external' ) );

		$rel = $link->getAttribute( 'rel' );
		$rel = array_filter( array_unique( array_merge( preg_split( '/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY ) ?: array(), array( 'noopener' ) ) ) );
		$link->setAttribute( 'rel', implode( ' ', $rel ) );
	}
}

/**
 * The processed body HTML for the current article.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function citd_journal_article_html( $post_id = null ) {
	$article = citd_journal_get_article( $post_id );

	return $article['html'];
}

/**
 * The contents index for the current article.
 *
 * @param int|null $post_id Post ID.
 * @return array
 */
function citd_journal_article_headings( $post_id = null ) {
	$article = citd_journal_get_article( $post_id );

	return $article['headings'];
}

/**
 * Whether the sticky contents rail should render for a given article.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function citd_journal_show_toc( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id ) {
		return false;
	}

	if ( metadata_exists( 'post', $post_id, '_citd_toc' ) && ! get_post_meta( $post_id, '_citd_toc', true ) ) {
		return false;
	}

	$headings = citd_journal_article_headings( $post_id );

	/**
	 * Filter whether to show the contents rail.
	 *
	 * @param bool  $show     Whether to show it.
	 * @param int   $post_id  Post ID.
	 * @param array $headings Collected headings.
	 */
	return (bool) apply_filters( 'citd_journal_show_toc', count( $headings ) >= 3, $post_id, $headings );
}

/**
 * Parse the article before wp_head() runs.
 *
 * Blocks and shortcodes enqueue their own styles while rendering. Priming the
 * parse here means those assets are registered in time to be printed in the
 * document head rather than arriving late in the footer.
 *
 * @return void
 */
function citd_journal_prime_article() {
	if ( ! is_singular( CITD_JOURNAL_CPT ) ) {
		return;
	}

	$post_id = (int) get_queried_object_id();

	if ( ! $post_id ) {
		return;
	}

	citd_journal_get_article( $post_id );
}
add_action( 'template_redirect', 'citd_journal_prime_article', 20 );
