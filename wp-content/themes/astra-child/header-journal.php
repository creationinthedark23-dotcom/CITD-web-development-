<?php
/**
 * Journal header.
 *
 * Loaded with get_header( 'journal' ) from the publication templates. Replaces
 * the Astra header entirely so the journal owns its own masthead, navigation
 * and reading-progress instrumentation.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_is_article = is_singular( CITD_JOURNAL_CPT );
$citd_archive    = citd_journal_archive_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="color-scheme" content="light dark">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="journal-skip-link" href="#journal-main"><?php esc_html_e( 'Skip to content', 'citd-journal' ); ?></a>
<?php if ( $citd_is_article && citd_journal_show_toc() ) : ?>
	<a class="journal-skip-link" href="#journal-contents"><?php esc_html_e( 'Skip to contents', 'citd-journal' ); ?></a>
<?php endif; ?>

<?php if ( $citd_is_article ) : ?>
	<div
		class="journal-progress"
		id="journal-progress"
		role="progressbar"
		aria-label="<?php esc_attr_e( 'Reading progress', 'citd-journal' ); ?>"
		aria-valuemin="0"
		aria-valuemax="100"
		aria-valuenow="0"
		hidden
	>
		<div class="journal-progress__bar" id="journal-progress-bar"></div>
	</div>
<?php endif; ?>

<header class="journal-masthead" id="journal-masthead" data-masthead>
	<div class="journal-masthead__inner">

		<div class="journal-masthead__identity">
			<?php if ( is_post_type_archive( CITD_JOURNAL_CPT ) ) : ?>
				<span class="journal-wordmark journal-wordmark--static" aria-current="page">
					<span class="journal-wordmark__name"><?php echo esc_html( citd_journal_name() ); ?></span>
				</span>
			<?php else : ?>
				<a class="journal-wordmark" href="<?php echo esc_url( $citd_archive ); ?>">
					<span class="journal-wordmark__name"><?php echo esc_html( citd_journal_name() ); ?></span>
				</a>
			<?php endif; ?>
			<span class="journal-wordmark__publisher">
				<?php
				printf(
					/* translators: %s: publisher name. */
					esc_html__( 'Published by %s', 'citd-journal' ),
					esc_html( citd_journal_publisher() )
				);
				?>
			</span>
		</div>

		<?php if ( $citd_is_article ) : ?>
			<p class="journal-masthead__now" data-masthead-title aria-hidden="true">
				<?php echo esc_html( wp_trim_words( get_the_title(), 12, '…' ) ); ?>
			</p>
		<?php endif; ?>

		<nav class="journal-nav" id="journal-nav" aria-label="<?php esc_attr_e( 'Journal', 'citd-journal' ); ?>">
			<?php
			if ( has_nav_menu( 'journal_primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'journal_primary',
						'container'      => false,
						'menu_class'     => 'journal-nav__list',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			} else {
				$citd_topics = get_terms(
					array(
						'taxonomy'   => CITD_JOURNAL_TAX_TOPIC,
						'hide_empty' => true,
						'number'     => 5,
						'orderby'    => 'count',
						'order'      => 'DESC',
					)
				);

				if ( ! is_wp_error( $citd_topics ) && ! empty( $citd_topics ) ) {
					echo '<ul class="journal-nav__list">';

					foreach ( $citd_topics as $citd_topic ) {
						$citd_current = is_tax( CITD_JOURNAL_TAX_TOPIC, $citd_topic->term_id );

						printf(
							'<li class="journal-nav__item"><a class="journal-nav__link" href="%1$s"%2$s>%3$s</a></li>',
							esc_url( get_term_link( $citd_topic ) ),
							$citd_current ? ' aria-current="page"' : '',
							esc_html( $citd_topic->name )
						);
					}

					echo '</ul>';
				}
			}
			?>
		</nav>

		<div class="journal-masthead__tools">
			<button
				class="journal-iconbtn"
				type="button"
				id="journal-search-toggle"
				aria-expanded="false"
				aria-controls="journal-search"
			>
				<?php echo citd_journal_icon( 'search', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Search the journal', 'citd-journal' ); ?></span>
			</button>

			<button
				class="journal-iconbtn journal-iconbtn--theme"
				type="button"
				id="journal-theme-toggle"
				aria-pressed="false"
			>
				<span class="journal-iconbtn__icon journal-iconbtn__icon--sun">
					<?php echo citd_journal_icon( 'sun', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</span>
				<span class="journal-iconbtn__icon journal-iconbtn__icon--moon">
					<?php echo citd_journal_icon( 'moon', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</span>
				<span class="screen-reader-text" data-theme-label><?php esc_html_e( 'Switch to dark theme', 'citd-journal' ); ?></span>
			</button>

			<button
				class="journal-iconbtn journal-iconbtn--menu"
				type="button"
				id="journal-menu-toggle"
				aria-expanded="false"
				aria-controls="journal-drawer"
			>
				<span class="journal-iconbtn__icon journal-iconbtn__icon--menu">
					<?php echo citd_journal_icon( 'menu', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</span>
				<span class="journal-iconbtn__icon journal-iconbtn__icon--close">
					<?php echo citd_journal_icon( 'close', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</span>
				<span class="screen-reader-text" data-menu-label><?php esc_html_e( 'Open menu', 'citd-journal' ); ?></span>
			</button>
		</div>
	</div>

	<div class="journal-search" id="journal-search" hidden>
		<form class="journal-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="journal-search-field"><?php esc_html_e( 'Search the journal', 'citd-journal' ); ?></label>
			<?php echo citd_journal_icon( 'search', array( 'class' => 'journal-search__icon', 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
			<input
				class="journal-search__input"
				type="search"
				id="journal-search-field"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'Search articles, topics, authors', 'citd-journal' ); ?>"
				autocomplete="off"
			>
			<input type="hidden" name="journal" value="1">
			<button class="journal-search__submit" type="submit"><?php esc_html_e( 'Search', 'citd-journal' ); ?></button>
		</form>
	</div>
</header>

<div class="journal-drawer" id="journal-drawer" hidden>
	<nav class="journal-drawer__nav" aria-label="<?php esc_attr_e( 'Journal menu', 'citd-journal' ); ?>">
		<?php
		if ( has_nav_menu( 'journal_primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'journal_primary',
					'container'      => false,
					'menu_class'     => 'journal-drawer__list',
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);
		}

		$citd_drawer_topics = get_terms(
			array(
				'taxonomy'   => CITD_JOURNAL_TAX_TOPIC,
				'hide_empty' => true,
				'orderby'    => 'name',
			)
		);

		if ( ! is_wp_error( $citd_drawer_topics ) && ! empty( $citd_drawer_topics ) ) {
			echo '<p class="journal-drawer__label">' . esc_html__( 'Topics', 'citd-journal' ) . '</p>';
			echo '<ul class="journal-drawer__list">';

			foreach ( $citd_drawer_topics as $citd_drawer_topic ) {
				printf(
					'<li><a href="%1$s">%2$s<span class="journal-drawer__count">%3$s</span></a></li>',
					esc_url( get_term_link( $citd_drawer_topic ) ),
					esc_html( $citd_drawer_topic->name ),
					esc_html( number_format_i18n( $citd_drawer_topic->count ) )
				);
			}

			echo '</ul>';
		}
		?>

		<?php if ( has_nav_menu( 'journal_secondary' ) ) : ?>
			<p class="journal-drawer__label"><?php esc_html_e( 'More', 'citd-journal' ); ?></p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'journal_secondary',
					'container'      => false,
					'menu_class'     => 'journal-drawer__list',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		<?php endif; ?>
	</nav>
</div>
