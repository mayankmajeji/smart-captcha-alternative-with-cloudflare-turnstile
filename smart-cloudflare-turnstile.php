<?php

/**
 * Plugin Name: Smart CAPTCHA Alternative with Cloudflare Turnstile
 * Plugin URI: https://wordpress.org/plugins/smart-captcha-alternative-with-cloudflare-turnstile
 * Description: Lightweight and privacy-first integration of Cloudflare Turnstile with WordPress forms.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Mayank Majeji
 * Author URI: https://profiles.wordpress.org/mayankmajeji/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-cloudflare-turnstile
 * Domain Path: /i18n/languages
 *
 * @package SmartCT
 */

declare(strict_types=1);

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

// Plugin version
define('SMARTCT_VERSION', '1.0.0');
define('SMARTCT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMARTCT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMARTCT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once SMARTCT_PLUGIN_DIR . 'includes/class-loader.php';

// Initialize the plugin
function smartct_init()
{
	// Load text domain
	// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Kept for compatibility with non-WordPress.org distributions and to ensure translations load correctly in all environments
	load_plugin_textdomain('smart-cloudflare-turnstile', false, dirname(SMARTCT_PLUGIN_BASENAME) . '/i18n/languages');

	// Initialize main plugin class as singleton
	\SmartCT\Init::get_instance()->init();
}

add_action('plugins_loaded', 'smartct_init');

// Activation hook
register_activation_hook(__FILE__, function () {
	// Activation tasks will be handled by Init class
	require_once SMARTCT_PLUGIN_DIR . 'includes/class-init.php';
	\SmartCT\Init::get_instance()->activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
	// Deactivation tasks will be handled by Init class
	require_once SMARTCT_PLUGIN_DIR . 'includes/class-init.php';
	\SmartCT\Init::get_instance()->deactivate();
});
