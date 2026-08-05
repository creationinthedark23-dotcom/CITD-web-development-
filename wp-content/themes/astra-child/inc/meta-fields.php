<?php
/**
 * Editorial metadata for publications.
 *
 * Everything the article template needs beyond core fields lives here. The
 * fields are registered with the REST API so they are available to the block
 * editor, and are also rendered as a grouped meta box for editors who prefer
 * the sidebar workflow.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions, keyed by meta key.
 *
 * type      One of: text, textarea, number, checkbox, select.
 * label     Field label.
 * help      Description shown beneath the control.
 * default   Default value.
 * choices   Options for select fields.
 * group     Meta box the field belongs to.
 *
 * @return array
 */
function citd_journal_meta_fields() {
	return array(
		'_citd_eyebrow'           => array(
			'type'  => 'text',
			'label' => __( 'Eyebrow', 'citd-journal' ),
			'help'  => __( 'Short editorial label above the headline, for example "Field Note" or "Research".', 'citd-journal' ),
			'group' => 'presentation',
		),
		'_citd_deck'              => array(
			'type'  => 'textarea',
			'label' => __( 'Deck / standfirst', 'citd-journal' ),
			'help'  => __( 'One or two sentences under the headline. This is the single most important line on the page after the title.', 'citd-journal' ),
			'rows'  => 3,
			'group' => 'presentation',
		),
		'_citd_hero_credit'       => array(
			'type'  => 'text',
			'label' => __( 'Hero image credit', 'citd-journal' ),
			'help'  => __( 'Photographer, illustrator or source. Displayed beneath the hero image.', 'citd-journal' ),
			'group' => 'presentation',
		),
		'_citd_hero_style'        => array(
			'type'    => 'select',
			'label'   => __( 'Hero treatment', 'citd-journal' ),
			'help'    => __( 'How the article opens.', 'citd-journal' ),
			'default' => 'standard',
			'choices' => array(
				'standard'  => __( 'Standard — title above a wide image', 'citd-journal' ),
				'immersive' => __( 'Immersive — full-bleed image with overlaid title', 'citd-journal' ),
				'split'     => __( 'Split — title beside the image', 'citd-journal' ),
				'typographic' => __( 'Typographic — title only, no image', 'citd-journal' ),
			),
			'group'   => 'presentation',
		),
		'_citd_featured'          => array(
			'type'  => 'checkbox',
			'label' => __( 'Feature as the latest issue', 'citd-journal' ),
			'help'  => __( 'Promotes this article to the large slot at the top of /journal/. Only the most recently published featured article is used.', 'citd-journal' ),
			'group' => 'presentation',
		),
		'_citd_toc'               => array(
			'type'    => 'checkbox',
			'label'   => __( 'Show table of contents', 'citd-journal' ),
			'help'    => __( 'Builds a sticky contents rail from the H2 and H3 headings in the article.', 'citd-journal' ),
			'default' => 1,
			'group'   => 'presentation',
		),
		'_citd_executive_summary' => array(
			'type'  => 'textarea',
			'label' => __( 'Executive summary', 'citd-journal' ),
			'help'  => __( 'One takeaway per line. Rendered as the numbered summary panel before the article body. Leave empty to hide the panel.', 'citd-journal' ),
			'rows'  => 6,
			'group' => 'summary',
		),
		'_citd_reading_override'  => array(
			'type'  => 'number',
			'label' => __( 'Reading time override (minutes)', 'citd-journal' ),
			'help'  => __( 'Leave empty to calculate automatically from the article length.', 'citd-journal' ),
			'min'   => 0,
			'group' => 'summary',
		),
		'_citd_references'        => array(
			'type'  => 'textarea',
			'label' => __( 'References', 'citd-journal' ),
			'help'  => __( 'One reference per line. Any URL at the end of a line becomes a link, for example: Porter, M. "Competitive Strategy." Free Press, 1980. https://example.com/paper', 'citd-journal' ),
			'rows'  => 8,
			'group' => 'references',
		),
	);
}

/**
 * Register every field with the REST API and the meta registry.
 *
 * @return void
 */
function citd_journal_register_meta() {
	foreach ( citd_journal_meta_fields() as $key => $field ) {
		$type = 'string';

		if ( 'number' === $field['type'] ) {
			$type = 'integer';
		} elseif ( 'checkbox' === $field['type'] ) {
			$type = 'boolean';
		}

		register_post_meta(
			CITD_JOURNAL_CPT,
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => isset( $field['default'] ) ? $field['default'] : ( 'boolean' === $type ? false : ( 'integer' === $type ? 0 : '' ) ),
				'sanitize_callback' => 'citd_journal_sanitize_meta_' . $type,
				'auth_callback'     => 'citd_journal_can_edit_publications',
			)
		);
	}

	register_post_meta(
		CITD_JOURNAL_CPT,
		'_citd_reading_time',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'citd_journal_can_edit_publications',
		)
	);

	register_post_meta(
		CITD_JOURNAL_CPT,
		'_citd_word_count',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'citd_journal_can_edit_publications',
		)
	);
}
add_action( 'init', 'citd_journal_register_meta', 7 );

/**
 * Capability gate for publication metadata.
 *
 * @param bool   $allowed Whether the user can act.
 * @param string $meta_key Meta key.
 * @param int    $post_id Post ID.
 * @return bool
 */
function citd_journal_can_edit_publications( $allowed, $meta_key, $post_id ) {
	unset( $allowed, $meta_key );

	return current_user_can( 'edit_post', $post_id );
}

/**
 * Sanitise a string meta value, preserving line breaks.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function citd_journal_sanitize_meta_string( $value ) {
	return sanitize_textarea_field( (string) $value );
}

/**
 * Sanitise an integer meta value.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function citd_journal_sanitize_meta_integer( $value ) {
	return absint( $value );
}

/**
 * Sanitise a boolean meta value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function citd_journal_sanitize_meta_boolean( $value ) {
	return (bool) $value;
}

/**
 * Register the editorial meta boxes.
 *
 * @return void
 */
function citd_journal_add_meta_boxes() {
	add_meta_box(
		'citd-journal-presentation',
		__( 'Journal — Presentation', 'citd-journal' ),
		'citd_journal_render_meta_box',
		CITD_JOURNAL_CPT,
		'normal',
		'high',
		array( 'group' => 'presentation' )
	);

	add_meta_box(
		'citd-journal-summary',
		__( 'Journal — Executive Summary & Reading Time', 'citd-journal' ),
		'citd_journal_render_meta_box',
		CITD_JOURNAL_CPT,
		'normal',
		'default',
		array( 'group' => 'summary' )
	);

	add_meta_box(
		'citd-journal-references',
		__( 'Journal — References', 'citd-journal' ),
		'citd_journal_render_meta_box',
		CITD_JOURNAL_CPT,
		'normal',
		'default',
		array( 'group' => 'references' )
	);
}
add_action( 'add_meta_boxes_' . CITD_JOURNAL_CPT, 'citd_journal_add_meta_boxes' );

/**
 * Render one group of editorial fields.
 *
 * @param WP_Post $post Current post.
 * @param array   $box  Meta box arguments.
 * @return void
 */
function citd_journal_render_meta_box( $post, $box ) {
	$group  = isset( $box['args']['group'] ) ? $box['args']['group'] : 'presentation';
	$fields = citd_journal_meta_fields();

	wp_nonce_field( 'citd_journal_save_meta', 'citd_journal_meta_nonce' );

	echo '<div class="citd-fields">';

	foreach ( $fields as $key => $field ) {
		if ( $field['group'] !== $group ) {
			continue;
		}

		$value = get_post_meta( $post->ID, $key, true );

		if ( '' === $value && isset( $field['default'] ) && ! metadata_exists( 'post', $post->ID, $key ) ) {
			$value = $field['default'];
		}

		$id = 'citd-field-' . ltrim( $key, '_' );

		echo '<div class="citd-field citd-field--' . esc_attr( $field['type'] ) . '">';

		if ( 'checkbox' === $field['type'] ) {
			printf(
				'<label class="citd-field__toggle" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s><span>%4$s</span></label>',
				esc_attr( $id ),
				esc_attr( $key ),
				checked( (bool) $value, true, false ),
				esc_html( $field['label'] )
			);
		} else {
			printf(
				'<label class="citd-field__label" for="%1$s">%2$s</label>',
				esc_attr( $id ),
				esc_html( $field['label'] )
			);
		}

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea class="citd-field__control" id="%1$s" name="%2$s" rows="%3$d">%4$s</textarea>',
					esc_attr( $id ),
					esc_attr( $key ),
					isset( $field['rows'] ) ? (int) $field['rows'] : 4,
					esc_textarea( $value )
				);
				break;

			case 'number':
				printf(
					'<input class="citd-field__control citd-field__control--short" type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$d" step="1">',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $value ),
					isset( $field['min'] ) ? (int) $field['min'] : 0
				);
				break;

			case 'select':
				printf( '<select class="citd-field__control" id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $key ) );
				foreach ( $field['choices'] as $choice_value => $choice_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $choice_value ),
						selected( $value, $choice_value, false ),
						esc_html( $choice_label )
					);
				}
				echo '</select>';
				break;

			case 'checkbox':
				break;

			case 'text':
			default:
				printf(
					'<input class="citd-field__control" type="text" id="%1$s" name="%2$s" value="%3$s">',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $value )
				);
				break;
		}

		if ( ! empty( $field['help'] ) ) {
			printf( '<p class="citd-field__help">%s</p>', esc_html( $field['help'] ) );
		}

		echo '</div>';
	}

	if ( 'summary' === $group ) {
		$minutes = (int) get_post_meta( $post->ID, '_citd_reading_time', true );
		$words   = (int) get_post_meta( $post->ID, '_citd_word_count', true );

		printf(
			'<p class="citd-field__readout">%s</p>',
			esc_html(
				sprintf(
					/* translators: 1: word count, 2: reading time in minutes. */
					__( 'Last calculated: %1$s words, %2$s minutes.', 'citd-journal' ),
					number_format_i18n( $words ),
					number_format_i18n( $minutes )
				)
			)
		);
	}

	echo '</div>';
}

/**
 * Persist the editorial fields and refresh the derived reading time.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function citd_journal_save_meta( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( CITD_JOURNAL_CPT !== $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Always refresh the derived values, even on REST saves where the meta box
	// nonce is absent.
	citd_journal_refresh_reading_time( $post_id, $post );

	if ( ! isset( $_POST['citd_journal_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['citd_journal_meta_nonce'] ) ), 'citd_journal_save_meta' ) ) {
		return;
	}

	foreach ( citd_journal_meta_fields() as $key => $field ) {
		switch ( $field['type'] ) {
			case 'checkbox':
				if ( isset( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, 1 );
				} else {
					update_post_meta( $post_id, $key, 0 );
				}
				break;

			case 'number':
				$raw = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
				if ( '' === $raw ) {
					delete_post_meta( $post_id, $key );
				} else {
					update_post_meta( $post_id, $key, absint( $raw ) );
				}
				break;

			case 'select':
				$raw = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : '';
				if ( isset( $field['choices'][ $raw ] ) ) {
					update_post_meta( $post_id, $key, $raw );
				}
				break;

			case 'textarea':
				$raw = isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '';
				if ( '' === $raw ) {
					delete_post_meta( $post_id, $key );
				} else {
					update_post_meta( $post_id, $key, $raw );
				}
				break;

			case 'text':
			default:
				$raw = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
				if ( '' === $raw ) {
					delete_post_meta( $post_id, $key );
				} else {
					update_post_meta( $post_id, $key, $raw );
				}
				break;
		}
	}

	citd_journal_refresh_reading_time( $post_id, get_post( $post_id ) );
	delete_transient( 'citd_journal_featured_id' );
}
add_action( 'save_post', 'citd_journal_save_meta', 10, 2 );

/**
 * Recalculate and store the word count and reading time for an article.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function citd_journal_refresh_reading_time( $post_id, $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$text  = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$words = citd_journal_count_words( $text );

	$override = get_post_meta( $post_id, '_citd_reading_override', true );

	if ( '' !== $override && (int) $override > 0 ) {
		$minutes = (int) $override;
	} else {
		$minutes = max( 1, (int) ceil( $words / citd_journal_words_per_minute() ) );
	}

	update_post_meta( $post_id, '_citd_word_count', $words );
	update_post_meta( $post_id, '_citd_reading_time', $minutes );
}

/**
 * Reading speed used across the publication.
 *
 * @return int
 */
function citd_journal_words_per_minute() {
	/**
	 * Filter the assumed reading speed.
	 *
	 * @param int $wpm Words per minute.
	 */
	return max( 60, (int) apply_filters( 'citd_journal_words_per_minute', 225 ) );
}

/**
 * Count words in a string, with multibyte-safe handling for scripts that do
 * not separate words with spaces.
 *
 * @param string $text Plain text.
 * @return int
 */
function citd_journal_count_words( $text ) {
	$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );

	if ( '' === $text ) {
		return 0;
	}

	if ( function_exists( 'mb_strlen' ) && preg_match( '/[\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}]/u', $text ) ) {
		return (int) ceil( mb_strlen( $text ) / 2 );
	}

	return count( preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY ) );
}
