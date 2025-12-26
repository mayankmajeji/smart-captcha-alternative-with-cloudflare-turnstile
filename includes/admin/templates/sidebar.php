<?php

/**
 * Admin Sidebar Template (Reusable)
 *
 * @package SmartCT
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if (! defined('WPINC')) {
	die;
}
?>
<div class="smartct-admin-sidebar">
	<!-- Sidebar content: Add navigation, info, or widgets here -->
	<div class="smartct-sidebar-section">
		<h3><?php esc_html_e('Environment', 'smart-cloudflare-turnstile'); ?></h3>
		<ul>
			<?php
			global $wp_version;
			$php_version = PHP_VERSION;
			$wc_active = class_exists('WooCommerce');
			$wc_version = $wc_active ? get_option('woocommerce_version') : '';
			?>
			<li><?php
				// translators: %s: Plugin version number
				echo esc_html(sprintf(__('Plugin: v%s', 'smart-cloudflare-turnstile'), defined('SMARTCT_VERSION') ? SMARTCT_VERSION : ''));
				?></li>
			<li><?php
				// translators: %s: WordPress version number
				echo esc_html(sprintf(__('WordPress: v%s', 'smart-cloudflare-turnstile'), $wp_version));
				?></li>
			<li><?php
				// translators: %s: PHP version number
				echo esc_html(sprintf(__('PHP: v%s', 'smart-cloudflare-turnstile'), $php_version));
				?></li>
			<li><?php
				// translators: %s: WooCommerce version number or "Not detected" message
				echo esc_html(sprintf(__('WooCommerce: %s', 'smart-cloudflare-turnstile'), $wc_active ? 'v' . $wc_version : __('Not detected', 'smart-cloudflare-turnstile')));
				?></li>
			<li><?php
				// translators: %s: Memory limit value
				echo esc_html(sprintf(__('Memory Limit: %s', 'smart-cloudflare-turnstile'), (string) ini_get('memory_limit')));
				?></li>
		</ul>
		<p style="margin-top:.5em;">
			<button type="button" class="button" id="twp-copy-system-info"><?php esc_html_e('Copy System Info', 'smart-cloudflare-turnstile'); ?></button>
			<span id="twp-copy-system-info-msg" style="margin-left:8px;color:#46b450;display:none;"><?php esc_html_e('Copied!', 'smart-cloudflare-turnstile'); ?></span>
		</p>
	</div>
	<div class="smartct-sidebar-section">
		<h3>Plugin Navigation</h3>
		<ul>
			<li><a href="admin.php?page=smartct-settings">Settings</a></li>
			<li><a href="admin.php?page=smartct-integrations">Integrations</a></li>
			<li><a href="admin.php?page=smartct-tools">Tools</a></li>
			<li><a href="admin.php?page=smartct-faqs">FAQs</a></li>
		</ul>
	</div>
	<div class="smartct-sidebar-section">
		<h3>Need Help?</h3>
		<p>Visit the <a href="https://wordpress.org/support/plugin/smart-captcha-alternative-with-cloudflare-turnstile/" target="_blank" rel="noopener">support forum</a> or <a href="https://github.com/mayankmajeji/smart-cloudflare-turnstile" target="_blank" rel="noopener">GitHub</a>.</p>
	</div>
</div>
<?php
// System info copy functionality is enqueued via admin-system-info.js in class-init.php
?>