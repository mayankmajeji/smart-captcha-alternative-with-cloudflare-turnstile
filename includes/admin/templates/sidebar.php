<?php
/**
 * Admin Sidebar Template (Reusable)
 *
 * @package TurnstileWP
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if ( ! defined('WPINC') ) {
	die;
}
?>
<div class="turnstilewp-admin-sidebar">
	<!-- Sidebar content: Add navigation, info, or widgets here -->
	<div class="turnstilewp-sidebar-section">
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
				echo esc_html(sprintf(__('Plugin: v%s', 'smart-cloudflare-turnstile'), defined('TURNSTILEWP_VERSION') ? TURNSTILEWP_VERSION : ''));
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
	<div class="turnstilewp-sidebar-section">
		<h3>Plugin Navigation</h3>
		<ul>
			<li><a href="admin.php?page=turnstilewp">Dashboard</a></li>
			<li><a href="admin.php?page=turnstilewp-settings">Settings</a></li>
			<li><a href="admin.php?page=turnstilewp-integrations">Integrations</a></li>
			<li><a href="admin.php?page=turnstilewp-tools">Tools</a></li>
			<li><a href="admin.php?page=turnstilewp-faqs">FAQs</a></li>
		</ul>
	</div>
	<div class="turnstilewp-sidebar-section">
		<h3>Need Help?</h3>
		<p>Visit the <a href="https://wordpress.org/support/plugin/turnstilewp/" target="_blank" rel="noopener">support forum</a> or <a href="https://github.com/mayankmajeji/turnstilewp" target="_blank" rel="noopener">GitHub</a>.</p>
	</div>
</div>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		var btn = document.getElementById('twp-copy-system-info');
		if (!btn) return;
		btn.addEventListener('click', function() {
			var info = [
				'Smart Cloudflare Turnstile: v' + (<?php echo wp_json_encode(defined('TURNSTILEWP_VERSION') ? TURNSTILEWP_VERSION : ''); ?>),
				'WordPress: v' + (<?php echo wp_json_encode($wp_version ?? ''); ?>),
				'PHP: v' + (<?php echo wp_json_encode(PHP_VERSION); ?>),
				'WooCommerce: ' + (<?php echo wp_json_encode(class_exists('WooCommerce') ? ( 'v' . get_option('woocommerce_version') ) : 'Not detected'); ?>),
				'Memory Limit: ' + (<?php echo wp_json_encode( (string) ini_get('memory_limit')); ?>)
			].join('\n');
			function showCopied() {
					var msg = document.getElementById('twp-copy-system-info-msg');
					if (msg) {
						msg.style.display = 'inline';
					setTimeout(function() { msg.style.display = 'none'; }, 1500);
					}
			}
			function fallbackCopy(text) {
				var ta = document.createElement('textarea');
				ta.value = text;
				ta.setAttribute('readonly', '');
				ta.style.position = 'absolute';
				ta.style.left = '-9999px';
				document.body.appendChild(ta);
				ta.select();
				try {
					var ok = document.execCommand('copy');
					document.body.removeChild(ta);
					if (ok) showCopied();
				} catch (e) {
					document.body.removeChild(ta);
				}
			}
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(info).then(showCopied).catch(function() {
					fallbackCopy(info);
				});
			} else {
				fallbackCopy(info);
			}
		});
	});
</script>
