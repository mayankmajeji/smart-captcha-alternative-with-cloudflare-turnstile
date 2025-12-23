<?php
/**
 * Header Template
 *
 * @package TurnstileWP
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if (! defined('WPINC')) {
	die;
}

// Get plugin version
$plugin_data = get_file_data(
	TURNSTILEWP_PLUGIN_DIR . 'turnstilewp.php',
	array(
		'Version' => 'Version',
		'Name'    => 'Plugin Name',
	)
);
$plugin_version = $plugin_data['Version'] ?? '';

// Resolve current page for active link
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading current page for navigation highlight only
$current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
$is_active = function (string $slug) use ($current_page): string {
	return $current_page === $slug ? ' is-active' : '';
};
?>
<div class="turnstilewp-header">
	<div class="twp-header-inner">
		<div class="twp-left">
			<a class="twp-logo" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp')); ?>">
				<img src="<?php echo esc_url(TURNSTILEWP_PLUGIN_URL . 'assets/images/favicon.svg'); ?>" alt="Smart Cloudflare Turnstile" />
				<span class="twp-brand">Smart Cloudflare Turnstile</span>
			</a>
		</div>
		<nav class="twp-nav">
			<a class="twp-nav-item<?php echo esc_attr($is_active('turnstilewp-settings')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp-settings')); ?>">Settings</a>
			<a class="twp-nav-item<?php echo esc_attr($is_active('turnstilewp-help')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp-help')); ?>">Support</a>
			<a class="twp-nav-item" href="https://github.com/mayankmajeji/turnstilewp" target="_blank" rel="noopener noreferrer">Documentation</a>
			<span class="twp-badge">FREE</span>
			<span class="twp-version">v<?php echo esc_html($plugin_version); ?></span>
			<a class="twp-nav-item twp-link-highlight" href="https://github.com/mayankmajeji/turnstilewp/issues/new/choose" target="_blank" rel="noopener noreferrer">Request Integration</a>
		</nav>
	</div>
</div>