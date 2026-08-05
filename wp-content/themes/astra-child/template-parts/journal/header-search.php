<?php
/**
 * Journal search results header.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_query = get_search_query();
?>

<section class="journal-hero journal-hero--compact" aria-labelledby="journal-hero-title">
	<div class="journal-shell">
		<nav class="journal-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'citd-journal' ); ?>">
			<ol class="journal-breadcrumbs__list">
				<li class="journal-breadcrumbs__item">
					<a href="<?php echo esc_url( citd_journal_archive_url() ); ?>"><?php echo esc_html( citd_journal_name() ); ?></a>
				</li>
				<li class="journal-breadcrumbs__item" aria-current="page">
					<span><?php esc_html_e( 'Search', 'citd-journal' ); ?></span>
				</li>
			</ol>
		</nav>

		<p class="journal-hero__eyebrow">
			<span class="journal-rule" aria-hidden="true"></span>
			<?php esc_html_e( 'Search', 'citd-journal' ); ?>
		</p>

		<h1 class="journal-hero__title journal-hero__title--compact" id="journal-hero-title">
			<?php echo esc_html( $citd_query ); ?>
		</h1>

		<form class="journal-search__form journal-search__form--inline" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="journal-search-field-inline"><?php esc_html_e( 'Search the journal', 'citd-journal' ); ?></label>
			<?php echo citd_journal_icon( 'search', array( 'class' => 'journal-search__icon', 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
			<input
				class="journal-search__input"
				type="search"
				id="journal-search-field-inline"
				name="s"
				value="<?php echo esc_attr( $citd_query ); ?>"
				placeholder="<?php esc_attr_e( 'Search articles, topics, authors', 'citd-journal' ); ?>"
			>
			<input type="hidden" name="journal" value="1">
			<button class="journal-search__submit" type="submit"><?php esc_html_e( 'Search', 'citd-journal' ); ?></button>
		</form>
	</div>
</section>
