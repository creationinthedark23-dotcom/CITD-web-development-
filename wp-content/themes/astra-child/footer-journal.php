<?php
/**
 * Journal footer.
 *
 * Loaded with get_footer( 'journal' ).
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_copyright = citd_journal_option( 'citd_journal_footer_copyright' );

if ( '' === $citd_copyright ) {
	$citd_copyright = sprintf(
		/* translators: 1: current year, 2: publisher name. */
		__( '© %1$s %2$s. All rights reserved.', 'citd-journal' ),
		gmdate( 'Y' ),
		citd_journal_publisher()
	);
}

$citd_contact  = citd_journal_option( 'citd_journal_contact_email' );
$citd_profiles = citd_journal_social_profiles();
?>

<footer class="journal-footer" id="journal-footer">
	<div class="journal-footer__inner">

		<div class="journal-footer__brand">
			<p class="journal-footer__wordmark"><?php echo esc_html( citd_journal_name() ); ?></p>
			<p class="journal-footer__statement"><?php echo esc_html( citd_journal_option( 'citd_journal_footer_statement' ) ); ?></p>

			<?php if ( ! empty( $citd_profiles ) ) : ?>
				<ul class="journal-footer__social">
					<?php foreach ( $citd_profiles as $citd_network => $citd_url ) : ?>
						<li>
							<a class="journal-iconbtn journal-iconbtn--ghost" href="<?php echo esc_url( $citd_url ); ?>" rel="noopener">
								<?php echo citd_journal_icon( $citd_network, array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
								<span class="screen-reader-text">
									<?php
									printf(
										/* translators: %s: social network name. */
										esc_html__( '%s profile', 'citd-journal' ),
										esc_html( ucfirst( $citd_network ) )
									);
									?>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="journal-footer__columns">
			<nav class="journal-footer__column" aria-label="<?php esc_attr_e( 'Journal sections', 'citd-journal' ); ?>">
				<h2 class="journal-footer__heading"><?php esc_html_e( 'Sections', 'citd-journal' ); ?></h2>
				<?php
				$citd_footer_topics = get_terms(
					array(
						'taxonomy'   => CITD_JOURNAL_TAX_TOPIC,
						'hide_empty' => true,
						'number'     => 8,
						'orderby'    => 'count',
						'order'      => 'DESC',
					)
				);

				if ( ! is_wp_error( $citd_footer_topics ) && ! empty( $citd_footer_topics ) ) {
					echo '<ul class="journal-footer__list">';

					foreach ( $citd_footer_topics as $citd_footer_topic ) {
						printf(
							'<li><a href="%1$s">%2$s</a></li>',
							esc_url( get_term_link( $citd_footer_topic ) ),
							esc_html( $citd_footer_topic->name )
						);
					}

					echo '</ul>';
				} else {
					printf(
						'<ul class="journal-footer__list"><li><a href="%1$s">%2$s</a></li></ul>',
						esc_url( citd_journal_archive_url() ),
						esc_html__( 'All articles', 'citd-journal' )
					);
				}
				?>
			</nav>

			<?php
			$citd_issues = citd_journal_get_issues( 6 );

			if ( ! empty( $citd_issues ) ) :
				?>
				<nav class="journal-footer__column" aria-label="<?php esc_attr_e( 'Issues', 'citd-journal' ); ?>">
					<h2 class="journal-footer__heading"><?php esc_html_e( 'Issues', 'citd-journal' ); ?></h2>
					<ul class="journal-footer__list">
						<?php foreach ( $citd_issues as $citd_issue ) : ?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $citd_issue ) ); ?>">
									<?php echo esc_html( citd_journal_issue_label( $citd_issue ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'journal_footer' ) ) : ?>
				<nav class="journal-footer__column" aria-label="<?php esc_attr_e( 'About', 'citd-journal' ); ?>">
					<h2 class="journal-footer__heading"><?php esc_html_e( 'The Journal', 'citd-journal' ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'journal_footer',
							'container'      => false,
							'menu_class'     => 'journal-footer__list',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="journal-footer__column">
				<h2 class="journal-footer__heading"><?php esc_html_e( 'Contact', 'citd-journal' ); ?></h2>
				<ul class="journal-footer__list">
					<?php if ( '' !== $citd_contact ) : ?>
						<li><a href="mailto:<?php echo esc_attr( sanitize_email( $citd_contact ) ); ?>"><?php echo esc_html( sanitize_email( $citd_contact ) ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( citd_journal_publisher() ); ?></a></li>
					<li>
						<a href="<?php echo esc_url( get_post_type_archive_feed_link( CITD_JOURNAL_CPT ) ); ?>">
							<?php esc_html_e( 'RSS feed', 'citd-journal' ); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="journal-footer__base">
			<p class="journal-footer__copyright"><?php echo esc_html( $citd_copyright ); ?></p>

			<?php if ( has_nav_menu( 'journal_legal' ) ) : ?>
				<nav class="journal-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'citd-journal' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'journal_legal',
							'container'      => false,
							'menu_class'     => 'journal-footer__legal-list',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<button class="journal-totop" type="button" id="journal-totop">
				<?php echo citd_journal_icon( 'arrow-up', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				<span><?php esc_html_e( 'Back to top', 'citd-journal' ); ?></span>
			</button>
		</div>
	</div>
</footer>

<div class="journal-toast" id="journal-toast" role="status" aria-live="polite" aria-atomic="true"></div>

<?php wp_footer(); ?>
</body>
</html>
