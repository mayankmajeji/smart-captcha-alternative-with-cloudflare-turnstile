<?php

/**
 * FAQs Page Template
 *
 * @package TurnstileWP
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if (! defined('WPINC')) {
	die;
}
// System info
$plugin_data = get_file_data(
	TURNSTILEWP_PLUGIN_DIR . 'turnstilewp.php',
	array(
		'Version' => 'Version',
	)
);
$plugin_version = $plugin_data['Version'] ?? '';
$theme = wp_get_theme();
?>
<div class="turnstilewp-page turnstilewp-page--support">
	<?php require_once __DIR__ . '/header.php'; ?>
	<div class="turnstilewp-faqs">
		<div class="turnstilewp-admin-wrapper">
			<div class="turnstilewp-section" id="section-support">
				<div class="turnstilewp-sub-section">
					<div class="turnstilewp-faq-card">
						<div id="faq-content" class="turnstilewp-faq-accordion" role="tablist">
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-1" aria-controls="faq-a-1" aria-expanded="false" tabindex="0"><?php esc_html_e('Do I need a Cloudflare account to use this plugin?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-1" aria-labelledby="faq-q-1" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('Yes. Cloudflare Turnstile requires a free Cloudflare account to generate a Site Key and Secret Key. These keys are used to securely validate requests between your site and Cloudflare.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-2" aria-controls="faq-a-2" aria-expanded="false" tabindex="0"><?php esc_html_e('Which forms are supported?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-2" aria-labelledby="faq-q-2" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('The plugin integrates with WordPress core forms, WooCommerce forms, and popular form plugins, including:', 'smart-cloudflare-turnstile'); ?></p>
									<p><strong><?php esc_html_e('WordPress Core:', 'smart-cloudflare-turnstile'); ?></strong><br>
										<?php esc_html_e('Login, Registration, Lost Password, and Comments', 'smart-cloudflare-turnstile'); ?></p>
									<p><strong><?php esc_html_e('WooCommerce:', 'smart-cloudflare-turnstile'); ?></strong><br>
										<?php esc_html_e('Login, Registration, Reset Password, Checkout, and Pay for Order pages', 'smart-cloudflare-turnstile'); ?></p>
									<p><strong><?php esc_html_e('Form Plugins:', 'smart-cloudflare-turnstile'); ?></strong><br>
										<?php esc_html_e('Contact Form 7, WPForms, Ninja Forms, Fluent Forms, Formidable Forms, Forminator, Everest Forms, and SureForms', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('Each integration can be enabled or disabled individually.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-3" aria-controls="faq-a-3" aria-expanded="false" tabindex="0"><?php esc_html_e('Does this plugin replace CAPTCHA solutions like reCAPTCHA?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-3" aria-labelledby="faq-q-3" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('Yes. Cloudflare Turnstile is designed as a modern alternative to traditional CAPTCHAs. It protects forms from bots without visual challenges, image selection, or user interaction in most cases.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-4" aria-controls="faq-a-4" aria-expanded="false" tabindex="0"><?php esc_html_e('Is this plugin free?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-4" aria-labelledby="faq-q-4" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('Yes. This plugin is completely free and does not include paid plans, upsells, or tracking.', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('Cloudflare Turnstile itself is also a free service.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-5" aria-controls="faq-a-5" aria-expanded="false" tabindex="0"><?php esc_html_e('Does it collect or store user data?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-5" aria-labelledby="faq-q-5" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('No. The plugin does not store personal data or user interaction data.', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('Verification is performed server-side against Cloudflare\'s API, and responses are not logged unless debug logging is explicitly enabled by the site owner.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-6" aria-controls="faq-a-6" aria-expanded="false" tabindex="0"><?php esc_html_e('Is this plugin GDPR-friendly?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-6" aria-labelledby="faq-q-6" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('Cloudflare states that Turnstile does not use cookies, does not track users for advertising, and does not perform fingerprinting.', 'smart-cloudflare-turnstile'); ?></p>
									<p>
										<?php
										printf(
											/* translators: 1: Cloudflare GDPR link, 2: Cloudflare DPA link */
											esc_html__('For full compliance details, please review Cloudflare\'s %1$s and %2$s.', 'smart-cloudflare-turnstile'),
											'<a href="https://www.cloudflare.com/gdpr/" target="_blank" rel="noopener">' . esc_html__('GDPR documentation', 'smart-cloudflare-turnstile') . '</a>',
											'<a href="https://www.cloudflare.com/cloudflare-customer-dpa/" target="_blank" rel="noopener">' . esc_html__('Data Processing Addendum', 'smart-cloudflare-turnstile') . '</a>'
										);
										?>
									</p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-7" aria-controls="faq-a-7" aria-expanded="false" tabindex="0"><?php esc_html_e('Why do I see a console warning or 401 error related to Turnstile?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-7" aria-labelledby="faq-q-7" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('Some browsers may log warnings related to unsupported browser features used by Cloudflare (such as Private Access Tokens). These messages can usually be ignored and do not affect form validation or security.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-8" aria-controls="faq-a-8" aria-expanded="false" tabindex="0"><?php esc_html_e('The Turnstile widget is not showing on my form. What should I check?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-8" aria-labelledby="faq-q-8" role="tabpanel" aria-hidden="true" style="display:none;">
									<p><?php esc_html_e('Please verify the following:', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('• Your Site Key and Secret Key are entered correctly', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('• The relevant form integration is enabled in plugin settings', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('• Page or form caching is not interfering with dynamic scripts', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('• The Turnstile script is not being blocked by another security or optimization plugin', 'smart-cloudflare-turnstile'); ?></p>
									<p><?php esc_html_e('If the issue persists, enable debug logging and check your site\'s error logs.', 'smart-cloudflare-turnstile'); ?></p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-9" aria-controls="faq-a-9" aria-expanded="false" tabindex="0"><?php esc_html_e('Where can I get help or report issues?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-9" aria-labelledby="faq-q-9" role="tabpanel" aria-hidden="true" style="display:none;">
									<p>
										<?php
										printf(
											/* translators: %s: WordPress.org support forum link */
											esc_html__('Support is provided through the %s.', 'smart-cloudflare-turnstile'),
											'<a href="https://wordpress.org/support/plugin/smart-cloudflare-turnstile/" target="_blank" rel="noopener">' . esc_html__('official WordPress.org plugin support forum', 'smart-cloudflare-turnstile') . '</a>'
										);
										?>
									</p>
									<p>
										<?php
										printf(
											/* translators: %s: GitHub repository link */
											esc_html__('Bug reports and development discussions can also be submitted via the %s.', 'smart-cloudflare-turnstile'),
											'<a href="https://github.com/mayankmajeji/smart-cloudflare-turnstile" target="_blank" rel="noopener">' . esc_html__('plugin\'s GitHub repository', 'smart-cloudflare-turnstile') . '</a>'
										);
										?>
									</p>
								</div>
							</div>
							<div class="faq-block">
								<h3 class="faq-question" role="tab" id="faq-q-10" aria-controls="faq-a-10" aria-expanded="false" tabindex="0"><?php esc_html_e('How can I report a security vulnerability?', 'smart-cloudflare-turnstile'); ?><span class="faq-icon"><?php require __DIR__ . '/icons/caret-icon.php'; ?></span></h3>
								<div class="faq-answer" id="faq-a-10" aria-labelledby="faq-q-10" role="tabpanel" aria-hidden="true" style="display:none;">
									<p>
										<?php
										printf(
											/* translators: %s: GitHub repository link */
											esc_html__('If you discover a security issue, please report it responsibly via the %s or through a recognized vulnerability disclosure program. Do not post security issues publicly.', 'smart-cloudflare-turnstile'),
											'<a href="https://github.com/mayankmajeji/smart-cloudflare-turnstile" target="_blank" rel="noopener">' . esc_html__('plugin\'s GitHub repository', 'smart-cloudflare-turnstile') . '</a>'
										);
										?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		var questions = document.querySelectorAll('.faq-question');
		var answers = document.querySelectorAll('.faq-answer');
		questions.forEach(function(q, idx) {
			q.addEventListener('click', function() {
				var expanded = q.getAttribute('aria-expanded') === 'true';
				// Collapse all
				questions.forEach(function(qq, i) {
					qq.setAttribute('aria-expanded', 'false');
					answers[i].style.display = 'none';
				});
				// Expand this one if it was not already open
				if (!expanded) {
					q.setAttribute('aria-expanded', 'true');
					answers[idx].style.display = 'block';
				}
			});
			q.addEventListener('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					q.click();
					e.preventDefault();
				}
			});
			// Start collapsed
			answers[idx].style.display = 'none';
		});
	});
</script>