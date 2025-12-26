<?php

/**
 * Help Page Template
 *
 * @package SmartCT
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if (! defined('WPINC')) {
	die;
}

// Check user permissions - only administrators can access plugin pages
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-cloudflare-turnstile' ) );
}

// Gather environment info
global $wp_version;
$php_version = PHP_VERSION;
$wc_active = class_exists('WooCommerce');
$wc_version = $wc_active ? get_option('woocommerce_version') : '';
$plugin_ver = defined('SMARTCT_VERSION') ? SMARTCT_VERSION : '';

// Tabs for help
$help_tabs = array(
	'support'  => __('Support', 'smart-cloudflare-turnstile'),
	'faqs'     => __('FAQs', 'smart-cloudflare-turnstile'),
	'system'   => __('System Info', 'smart-cloudflare-turnstile'),
);
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation doesn't require nonce verification
$current_help_tab = isset($_GET['help_tab']) ? sanitize_key(wp_unslash($_GET['help_tab'])) : 'support';
if (! array_key_exists($current_help_tab, $help_tabs)) {
	$current_help_tab = 'support';
}
?>
<div class="smartct-page smartct-page--help">
	<?php require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
	<div class="smartct-body">
		<div class="twp-body-header">
			<div class="twp-bh-left">
				<img src="<?php echo esc_url(SMARTCT_PLUGIN_URL . 'assets/images/favicon.svg'); ?>" alt="Smart Cloudflare Turnstile" />
			</div>
			<div class="twp-bh-right">
				<h1><?php esc_html_e('Help', 'smart-cloudflare-turnstile'); ?></h1>
				<p class="twp-page-desc"><?php esc_html_e('Support resources, FAQs, system info and feature requests.', 'smart-cloudflare-turnstile'); ?></p>
			</div>
		</div>
		<div class="twp-2col">
			<aside class="twp-vtabs">
				<?php foreach ($help_tabs as $hid => $hlabel) : ?>
					<?php
					// Map to custom SVG icon partials
					$icon_partial = 'plugin-icon.php';
					if ($hid === 'support') {
						$icon_partial = 'support-icon.php';
					}
					if ($hid === 'faqs') {
						$icon_partial = 'faq-icon.php';
					}
					if ($hid === 'system') {
						$icon_partial = 'info-icon.php';
					}
					$icon_path = SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
					?>
				<a class="twp-vtab <?php echo esc_attr( $current_help_tab === $hid ? 'is-active' : '' ); ?>"
					href="<?php echo esc_url(admin_url('admin.php?page=smartct-help&help_tab=' . urlencode((string) $hid))); ?>">
						<span class="twp-vtab-icon">
							<?php
							if (file_exists($icon_path)) {
								include $icon_path;
							}
							?>
						</span>
						<span class="twp-vtab-text"><?php echo esc_html($hlabel); ?></span>
					</a>
				<?php endforeach; ?>
			</aside>
			<section class="twp-2col-content">
				<div class="twp-toolbar">
					<button type="button" class="twp-collapse-btn" data-twp-toggle="vtabs">
						<span class="twp-collapse-icon icon-open" aria-hidden="true">
							<?php require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-close-icon.php'; ?>
						</span>
						<span class="twp-collapse-icon icon-close" aria-hidden="true" style="display:none;">
							<?php require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-open-icon.php'; ?>
						</span>
					</button>
					<div></div>
				</div>

				<div class="smartct-section">
					<div class="smartct-sub-section">
						<?php if ($current_help_tab === 'support') : ?>
							<div class="smartct-field-group">
								<div class="smartct-field">
									<div class="inside">
										<div class="twp-support-cards">
											<div class="twp-support-card smartct-box">
												<div class="twp-support-card-content">
													<div class="twp-support-icon">
														<?php
														$icon = SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/wordpress-support-icon.php';
														if (file_exists($icon)) {
															include $icon;
														}
														?>
													</div>
													<h3><?php esc_html_e('Support', 'smart-cloudflare-turnstile'); ?></h3>
													<p><?php esc_html_e('Smart Cloudflare Turnstile is also available on WordPress.org where you can download the plugin, submit a bug ticket, and follow along with the updates.', 'smart-cloudflare-turnstile'); ?></p>
												</div>
												<div class="smartct-buttons">
													<a class="button button-primary" href="https://wordpress.org/plugins/smart-captcha-alternative-with-cloudflare-turnstile/" target="_blank" rel="noopener">
														<?php esc_html_e('Visit WordPress.org', 'smart-cloudflare-turnstile'); ?>
													</a>
												</div>
											</div>
											<div class="twp-support-card smartct-box">
												<div class="twp-support-card-content">
													<div class="twp-support-icon">
														<?php
														$icon = SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/github-icon.php';
														if (file_exists($icon)) {
															include $icon;
														}
														?>
													</div>
													<h3><?php esc_html_e('GitHub', 'smart-cloudflare-turnstile'); ?></h3>
													<p><?php esc_html_e('Smart Cloudflare Turnstile is also available on GitHub where you can browse the code, open a bug report, and follow along with development.', 'smart-cloudflare-turnstile'); ?></p>
												</div>
												<div class="smartct-buttons">
													<a class="button button-primary" href="https://github.com/mayankmajeji/smart-cloudflare-turnstile" target="_blank" rel="noopener">
														<?php esc_html_e('Visit GitHub', 'smart-cloudflare-turnstile'); ?>
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php elseif ($current_help_tab === 'faqs') : ?>
							<div class="smartct-field-group">
								<div class="smartct-group-title">
									<h2><?php esc_html_e('Frequently Asked Questions', 'smart-cloudflare-turnstile'); ?></h2>
								</div>
								<div class="smartct-faqs-wrapper">
									<div class="inside">
										<?php require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/faqs-page.php'; /* reuse accordion */ ?>
									</div>
								</div>
							</div>
						<?php elseif ($current_help_tab === 'system') : ?>
							<div class="smartct-field-group">
								<div class="smartct-group-title">
									<h2><?php esc_html_e('System Info', 'smart-cloudflare-turnstile'); ?></h2>
								</div>
								<div class="smartct-field">
									<div class="inside">
										<div class="twp-system-info">
											<ul>
												<li>
													<span class="twp-si-label"><?php esc_html_e('Plugin Version', 'smart-cloudflare-turnstile'); ?></span>
													<span class="twp-si-value"><?php
																				// translators: %s: Plugin version number
																				echo esc_html(sprintf(__('v%s', 'smart-cloudflare-turnstile'), $plugin_ver));
																				?></span>
												</li>
												<li>
													<span class="twp-si-label"><?php esc_html_e('WordPress', 'smart-cloudflare-turnstile'); ?></span>
													<span class="twp-si-value"><?php
																				// translators: %s: WordPress version number
																				echo esc_html(sprintf(__('v%s', 'smart-cloudflare-turnstile'), $wp_version));
																				?></span>
												</li>
												<li>
													<span class="twp-si-label"><?php esc_html_e('PHP', 'smart-cloudflare-turnstile'); ?></span>
													<span class="twp-si-value"><?php
																				// translators: %s: PHP version number
																				echo esc_html(sprintf(__('v%s', 'smart-cloudflare-turnstile'), $php_version));
																				?></span>
												</li>
												<li>
													<span class="twp-si-label"><?php esc_html_e('WooCommerce', 'smart-cloudflare-turnstile'); ?></span>
													<span class="twp-si-value"><?php
																				echo esc_html($wc_active ? sprintf(
																					// translators: %s: WooCommerce version number
																					__('v%s', 'smart-cloudflare-turnstile'),
																					$wc_version
																				) : __('Not detected', 'smart-cloudflare-turnstile'));
																				?></span>
												</li>
												<li>
													<span class="twp-si-label"><?php esc_html_e('Memory Limit', 'smart-cloudflare-turnstile'); ?></span>
													<span class="twp-si-value"><?php echo esc_html((string) ini_get('memory_limit')); ?></span>
												</li>
											</ul>
											<div class="smartct-buttons">
												<button type="button" class="button button-primary" id="twp-copy-system-info"><?php esc_html_e('Copy System Info', 'smart-cloudflare-turnstile'); ?></button>
												<span id="twp-copy-system-info-msg" style="margin-left:8px;color:#46b450;display:none;"><?php esc_html_e('Copied!', 'smart-cloudflare-turnstile'); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>
<?php
// System info copy functionality is enqueued via admin-system-info.js in class-init.php
?>