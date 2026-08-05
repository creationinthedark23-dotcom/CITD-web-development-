<?php
/**
 * Newsletter signup.
 *
 * Works without JavaScript by posting to admin-post.php. journal.js upgrades it
 * to an inline AJAX submission.
 *
 * @package CITD_Journal
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$citd_notice = citd_journal_subscription_notice();
// A stable, canonical URL to return to after the no-JavaScript round trip.
if ( is_singular() ) {
	$citd_source = get_permalink();
} elseif ( is_tax() ) {
	$citd_term   = get_queried_object();
	$citd_link   = $citd_term instanceof WP_Term ? get_term_link( $citd_term ) : '';
	$citd_source = ( $citd_link && ! is_wp_error( $citd_link ) ) ? $citd_link : citd_journal_archive_url();
} else {
	$citd_source = citd_journal_archive_url();
}
?>

<section class="journal-newsletter" id="journal-newsletter" aria-labelledby="journal-newsletter-title">
	<div class="journal-shell">
		<div class="journal-newsletter__inner">

			<div class="journal-newsletter__copy">
				<p class="journal-newsletter__eyebrow">
					<span class="journal-rule" aria-hidden="true"></span>
					<?php echo esc_html( citd_journal_name() ); ?>
				</p>

				<h2 class="journal-newsletter__title" id="journal-newsletter-title">
					<?php echo esc_html( citd_journal_option( 'citd_journal_newsletter_title' ) ); ?>
				</h2>

				<p class="journal-newsletter__body">
					<?php echo esc_html( citd_journal_option( 'citd_journal_newsletter_body' ) ); ?>
				</p>
			</div>

			<form
				class="journal-newsletter__form"
				id="journal-newsletter-form"
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				novalidate
			>
				<input type="hidden" name="action" value="citd_journal_subscribe">
				<input type="hidden" name="source" value="<?php echo esc_url( $citd_source ); ?>">
				<?php wp_nonce_field( 'citd_journal_newsletter', 'citd_journal_newsletter_nonce' ); ?>

				<div class="journal-field">
					<label class="journal-field__label" for="journal-newsletter-name">
						<?php esc_html_e( 'Name', 'citd-journal' ); ?>
						<span class="journal-field__optional"><?php esc_html_e( 'optional', 'citd-journal' ); ?></span>
					</label>
					<input
						class="journal-field__input"
						type="text"
						id="journal-newsletter-name"
						name="name"
						autocomplete="name"
					>
				</div>

				<div class="journal-field">
					<label class="journal-field__label" for="journal-newsletter-email">
						<?php esc_html_e( 'Email address', 'citd-journal' ); ?>
					</label>
					<input
						class="journal-field__input"
						type="email"
						id="journal-newsletter-email"
						name="email"
						required
						autocomplete="email"
						inputmode="email"
						aria-describedby="journal-newsletter-status"
					>
				</div>

				<!--
					Honeypot. Hidden from sighted users with CSS and from assistive
					technology with aria-hidden; genuine submissions leave it empty.
				-->
				<div class="journal-field journal-field--honeypot" aria-hidden="true">
					<label for="journal-newsletter-website"><?php esc_html_e( 'Website', 'citd-journal' ); ?></label>
					<input type="text" id="journal-newsletter-website" name="website" tabindex="-1" autocomplete="off">
				</div>

				<div class="journal-field journal-field--consent">
					<input class="journal-field__checkbox" type="checkbox" id="journal-newsletter-consent" name="consent" value="1" required>
					<label class="journal-field__consent-label" for="journal-newsletter-consent">
						<?php echo esc_html( citd_journal_option( 'citd_journal_newsletter_consent' ) ); ?>
					</label>
				</div>

				<button class="journal-button journal-button--solid journal-newsletter__submit" type="submit">
					<span data-submit-label><?php esc_html_e( 'Subscribe', 'citd-journal' ); ?></span>
					<?php echo citd_journal_icon( 'arrow-right', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
				</button>

				<p
					class="journal-newsletter__status<?php echo $citd_notice ? ( $citd_notice['success'] ? ' is-success' : ' is-error' ) : ''; ?>"
					id="journal-newsletter-status"
					role="status"
					aria-live="polite"
				>
					<?php echo $citd_notice ? esc_html( $citd_notice['message'] ) : ''; ?>
				</p>

				<p class="journal-newsletter__legal">
					<?php esc_html_e( 'We store your address only to send the journal. Unsubscribe from any issue.', 'citd-journal' ); ?>
				</p>
			</form>
		</div>
	</div>
</section>
