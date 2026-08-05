<?php
/**
 * Author card.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_author = citd_journal_author();

if ( empty( $citd_author ) || '' === $citd_author['name'] ) {
	return;
}

$citd_count = (int) count_user_posts( $citd_author['id'], CITD_JOURNAL_CPT, true );

$citd_socials = array_filter(
	array(
		'linkedin' => $citd_author['linkedin'],
		'x'        => $citd_author['x'],
		'external' => $citd_author['website'],
	)
);
?>

<section class="journal-section journal-section--author" aria-labelledby="journal-author-title">
	<div class="journal-shell">
		<div class="journal-author" data-reveal>
			<div class="journal-author__media">
				<?php echo citd_journal_author_portrait( $citd_author, 128 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
			</div>

			<div class="journal-author__body">
				<p class="journal-author__eyebrow">
					<span class="journal-rule" aria-hidden="true"></span>
					<?php esc_html_e( 'About the author', 'citd-journal' ); ?>
				</p>

				<h2 class="journal-author__name" id="journal-author-title">
					<a href="<?php echo esc_url( $citd_author['url'] ); ?>" rel="author">
						<?php echo esc_html( $citd_author['name'] ); ?>
					</a>
				</h2>

				<?php if ( '' !== $citd_author['role'] || '' !== $citd_author['org'] ) : ?>
					<p class="journal-author__role">
						<?php
						echo esc_html(
							implode(
								' · ',
								array_filter( array( $citd_author['role'], $citd_author['org'] ) )
							)
						);
						?>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $citd_author['bio'] ) : ?>
					<div class="journal-author__bio">
						<?php echo wp_kses_post( wpautop( citd_journal_kses_bio( $citd_author['bio'] ) ) ); ?>
					</div>
				<?php endif; ?>

				<div class="journal-author__foot">
					<a class="journal-author__more" href="<?php echo esc_url( $citd_author['url'] ); ?>">
						<span>
							<?php
							printf(
								/* translators: %s: number of articles. */
								esc_html( _n( '%s article in the journal', '%s articles in the journal', $citd_count, 'citd-journal' ) ),
								esc_html( number_format_i18n( $citd_count ) )
							);
							?>
						</span>
						<?php echo citd_journal_icon( 'arrow-right', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
					</a>

					<?php if ( ! empty( $citd_socials ) ) : ?>
						<ul class="journal-author__links">
							<?php foreach ( $citd_socials as $citd_network => $citd_url ) : ?>
								<li>
									<a class="journal-iconbtn journal-iconbtn--ghost" href="<?php echo esc_url( $citd_url ); ?>" rel="noopener nofollow">
										<?php echo citd_journal_icon( $citd_network, array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
										<span class="screen-reader-text">
											<?php
											printf(
												/* translators: 1: author name, 2: destination. */
												esc_html__( '%1$s on %2$s', 'citd-journal' ),
												esc_html( $citd_author['name'] ),
												esc_html( 'external' === $citd_network ? __( 'the web', 'citd-journal' ) : ucfirst( $citd_network ) )
											);
											?>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
