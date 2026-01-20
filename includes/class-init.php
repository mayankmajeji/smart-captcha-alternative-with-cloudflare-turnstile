<?php

/**
 * Plugin initialization class
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init
{

	/**
	 * Plugin settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Plugin verification instance
	 *
	 * @var Verify
	 */
	private Verify $verify;

	/**
	 * AJAX handlers instance
	 *
	 * @var Ajax_Handlers
	 */
	private Ajax_Handlers $ajax_handlers;

	/**
	 * Plugin admin screen IDs
	 * Note: WordPress converts dashes to underscores in screen IDs
	 *
	 * @var array
	 */
	private const PLUGIN_SCREEN_IDS = array(
		'toplevel_page_smartct-settings',
		'smartct-settings_page_smartct-settings',
		'smartct-settings_page_smartct-integrations',
		'smartct-settings_page_smartct-tools',
		'smartct-settings_page_smartct-help',
	);

	private static bool $admin_hooks_registered = false;

	private static ?Init $instance = null;

	/**
	 * Get the singleton instance
	 */
	public static function get_instance(): Init
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin
	 */
	public function init(): void
	{
		// Prevent double initialization
		if (defined('SMARTCT_INIT_DONE')) {
			return;
		}
		define('SMARTCT_INIT_DONE', true);

		// Load dependencies
		$this->load_dependencies();

		// Initialize components
		$this->settings = new Settings();
		$this->verify = new Verify();
		$this->ajax_handlers = new Ajax_Handlers();

		// Initialize integrations
		$this->init_integrations();

		// Hook into WordPress
		$this->init_hooks();
	}

	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies(): void
	{
		// Load common functions
		require_once SMARTCT_PLUGIN_DIR . 'includes/functions-common.php';
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void
	{
		// Admin hooks
		if (is_admin() && ! self::$admin_hooks_registered) {
			add_action('admin_menu', array($this->settings, 'add_admin_menu'));
			add_action('admin_init', array($this->settings, 'register_settings'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
			add_filter('admin_body_class', array($this, 'add_admin_body_class'));
			add_filter('plugin_action_links_' . SMARTCT_PLUGIN_BASENAME, array($this, 'add_plugin_action_links'));
			add_action('wp_ajax_smartct_verify_keys', array($this, 'verify_keys_ajax'));
			add_action('wp_ajax_smartct_remove_keys', array($this, 'remove_keys_ajax'));
			self::$admin_hooks_registered = true;
		}

		// Frontend hooks
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
		add_action('login_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets(): void
	{
		$screen = get_current_screen();
		if (! $screen) {
			return;
		}

		// Check if current screen is a plugin page (by menu slug)
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page slug for asset enqueuing only
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		$is_plugin_page = strpos($page, 'smartct-') === 0;

		if (! $is_plugin_page && ! in_array($screen->id, self::PLUGIN_SCREEN_IDS, true)) {
			return;
		}

		/**
		 * Enqueue Turnstile API for admin widget.
		 * 
		 * Note: This script MUST be loaded from Cloudflare's CDN as per their requirements.
		 * Self-hosting is not permitted and would violate Cloudflare's terms of service.
		 * The Turnstile API is required for challenge verification and cannot function without it.
		 * 
		 * Privacy: When enabled, this plugin sends limited data to Cloudflare for verification.
		 * Users are informed of this in the plugin description and documentation.
		 * 
		 * Filter: 'smartct_load_turnstile_script' can be used to prevent loading if needed.
		 */
		if (apply_filters('smartct_load_turnstile_script', true)) {
		wp_enqueue_script(
			'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
			array(),
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
			null,
			true
		);
		}

		wp_enqueue_style(
			'smartct-admin',
			SMARTCT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			(file_exists(SMARTCT_PLUGIN_DIR . 'assets/css/admin.css') ? filemtime(SMARTCT_PLUGIN_DIR . 'assets/css/admin.css') : SMARTCT_VERSION)
		);
		// Header/brand styles now compiled via admin.scss

		// Enqueue admin-settings.js for widget preview and AJAX
		wp_enqueue_script(
			'smartct-admin-settings',
			SMARTCT_PLUGIN_URL . 'assets/js/admin-settings.js',
			array('jquery', 'cloudflare-turnstile'),
			SMARTCT_VERSION,
			true
		);
		wp_localize_script(
			'smartct-admin-settings',
			'smartct',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('smartct_verify_keys'),
				'siteKey' => (new Settings())->get_option('smartct_site_key', ''),
				'secretKey' => (new Settings())->get_option('smartct_secret_key', ''),
			)
		);

		// Enqueue page-specific scripts
		$this->enqueue_admin_page_scripts($screen->id);
	}

	/**
	 * Enqueue page-specific admin scripts with localized data
	 *
	 * @param string $screen_id Current admin screen ID.
	 */
	private function enqueue_admin_page_scripts(string $screen_id): void
	{
		global $wp_version;

		// Prepare system info data for localization
		$system_info_data = array(
			'plugin_version' => defined('SMARTCT_VERSION') ? SMARTCT_VERSION : '',
			'wp_version'     => $wp_version ?? '',
			'php_version'    => PHP_VERSION,
			'wc_version'     => class_exists('WooCommerce') ? 'v' . get_option('woocommerce_version') : 'Not detected',
			'memory_limit'   => (string) ini_get('memory_limit'),
		);

		// Localize system info data to admin-settings.js (already enqueued in enqueue_admin_assets)
		// This data is used by the system info copy feature in the sidebar
		wp_localize_script('smartct-admin-settings', 'smartctSystemInfo', $system_info_data);
	}

	/**
	 * Add custom classes to the admin body on SmartCT plugin pages
	 *
	 * @param string $classes Existing admin body classes.
	 * @return string
	 */
	public function add_admin_body_class(string $classes): string
	{
		if (! function_exists('get_current_screen')) {
			return $classes;
		}
		$screen = get_current_screen();
		if (! $screen) {
			return $classes;
		}
		
		// Check if current page is a plugin page (by menu slug)
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page slug for display only
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		$is_plugin_page = strpos($page, 'smartct-') === 0;

		if ($is_plugin_page || in_array($screen->id, self::PLUGIN_SCREEN_IDS, true)) {
			// Generic plugin class + screen-specific class
			$classes .= ' smartct-admin smartct-screen-' . sanitize_html_class((string) $screen->id);
		}
		return $classes;
	}

	/**
	 * Add settings link to plugin action links
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_plugin_action_links(array $links): array
	{
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url(admin_url('admin.php?page=smartct-settings')),
			esc_html__('Settings', 'smart-cloudflare-turnstile')
		);
		// Add Settings link before Deactivate link
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets(): void
	{
		// Only load on pages where Turnstile is needed
		if (! $this->should_load_turnstile()) {
			return;
		}

		/**
		 * Enqueue Cloudflare Turnstile script.
		 * 
		 * Note: This script MUST be loaded from Cloudflare's CDN as per their requirements.
		 * Self-hosting is not permitted and would violate Cloudflare's terms of service.
		 * The Turnstile API is required for challenge verification and cannot function without it.
		 * 
		 * Privacy Notice: When Turnstile is enabled on a form, user interactions are sent to
		 * Cloudflare's servers for verification. This is clearly documented in the plugin
		 * description and settings page.
		 * 
		 * Data Sent to Cloudflare:
		 * - User interaction data (mouse movements, clicks)
		 * - Browser fingerprint
		 * - IP address
		 * - Challenge response token
		 * 
		 * Cloudflare Privacy Policy: https://www.cloudflare.com/privacypolicy/
		 * 
		 * Filter: 'smartct_load_turnstile_script' can be used to prevent loading if needed.
		 */
		if (apply_filters('smartct_load_turnstile_script', true)) {
		wp_enqueue_script(
			'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
			array(),
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
			null,
			true
		);
		}

		// Add small inline bootstrap to render placeholders (e.g., Formidable)
		$siteKey = $this->settings->get_option('smartct_site_key');
		if (! empty($siteKey)) {
			$inline = "(function(){function onReady(fn){if(document.readyState!=='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}onReady(function(){var phs=document.querySelectorAll('.smartct-fmd-placeholder');if(!phs.length){return;}phs.forEach(function(ph){var form=ph.closest('form');if(!form){return;}var btn=form.querySelector('.frm_button_submit');if(!btn){return;}var mount=document.createElement('div');mount.style.margin='10px 0';btn.parentNode.insertBefore(mount, btn);if(window.turnstile){try{window.turnstile.render(mount,{sitekey:'" . esc_js($siteKey) . "'});}catch(e){console&&console.warn&&console.warn('Turnstile render error',e);}}});});})();";
			wp_add_inline_script('cloudflare-turnstile', $inline, 'after');
		}

		// Enqueue our custom styles
		wp_enqueue_style(
			'smartct-frontend',
			SMARTCT_PLUGIN_URL . 'assets/css/turnstile.css',
			array(),
			SMARTCT_VERSION
		);

		// Note: localization not needed for inline bootstrap above
	}

	/**
	 * Check if Turnstile should be loaded
	 *
	 * @return bool
	 */
	private function should_load_turnstile(): bool
	{
		// Don't load for logged-in users unless specifically configured
		$settings = get_option('smartct_settings', array());
		if (is_user_logged_in() && (empty($settings['show_for_logged_in']) || ! $settings['show_for_logged_in'])) {
			return false;
		}

		// Check if we're on a page that needs Turnstile
		return is_login() ||
			is_registration_page() ||
			is_lost_password_page() ||
			is_comment_form_page();
	}

	/**
	 * Plugin activation
	 */
	public function activate(): void
	{
		// Initialize settings before using them
		$this->settings = new Settings();

		// Add default options
		$this->settings->add_default_options();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate(): void
	{
		// Cleanup tasks if needed
	}

	/**
	 * Handle AJAX key verification
	 */
	public function verify_keys_ajax(): void
	{
		// Verify nonce
		if (! check_ajax_referer('smartct_verify_keys', 'nonce', false)) {
			wp_send_json_error(array('message' => __('Security check failed.', 'smart-cloudflare-turnstile')));
		}

		// Verify user capabilities
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'smart-cloudflare-turnstile')));
		}

		// Get the keys from the request
		$site_key = sanitize_text_field(wp_unslash($_POST['site_key'] ?? ''));
		$secret_key = sanitize_text_field(wp_unslash($_POST['secret_key'] ?? ''));
		$token = sanitize_text_field(wp_unslash($_POST['response'] ?? ''));

		if (empty($site_key) || empty($secret_key)) {
			wp_send_json_error(array('message' => __('Please enter both Site Key and Secret Key.', 'smart-cloudflare-turnstile')));
		}
		if (empty($token)) {
			wp_send_json_error(array('message' => __('Please complete the Turnstile challenge.', 'smart-cloudflare-turnstile')));
		}

		// Use the Verify class to check the token with the provided secret key
		$verify = new Verify();
		$is_valid = $verify->verify_token($token, $secret_key);

		if ($is_valid) {
			$current_settings = get_option('smartct_settings', array());
			if (! is_array($current_settings)) {
				$current_settings = array();
			}
			$current_settings['smartct_site_key'] = $site_key;
			$current_settings['smartct_secret_key'] = $secret_key;
			update_option('smartct_settings', $current_settings);

			update_option('smartct_keys_verified', 1);

			wp_send_json_success(array('message' => __('Keys verified successfully.', 'smart-cloudflare-turnstile')));
		} else {
			wp_send_json_error(array('message' => __('Verification failed. Please check your keys and try again.', 'smart-cloudflare-turnstile')));
		}
	}

	/**
	 * Handle AJAX key removal
	 */
	public function remove_keys_ajax(): void
	{
		// Verify nonce
		if (! check_ajax_referer('smartct_verify_keys', 'nonce', false)) {
			wp_send_json_error(array('message' => __('Security check failed.', 'smart-cloudflare-turnstile')));
		}

		// Verify user capabilities
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'smart-cloudflare-turnstile')));
		}

		// Update the settings to remove verification
		$current_settings = get_option('smartct_settings', array());
		if (! is_array($current_settings)) {
			$current_settings = array();
		}
		$current_settings['smartct_site_key'] = '';
		$current_settings['smartct_secret_key'] = '';
		update_option('smartct_settings', $current_settings);
		update_option('smartct_keys_verified', 0);

		wp_send_json_success(array('message' => __('Keys removed successfully.', 'smart-cloudflare-turnstile')));
	}

	/**
	 * Initialize all integrations
	 */
	private function init_integrations(): void
	{
		// Core WordPress integration
		new \SmartCT\Core_WP();

		// WooCommerce integration (if WooCommerce is active)
		if (class_exists('WooCommerce')) {
			new \SmartCT\Integrations\Turnstile_WooCommerce();
		}

		// Contact Form 7 integration (if CF7 is active)
		if (defined('WPCF7_VERSION') || function_exists('wpcf7')) {
			new \SmartCT\Integrations\Contact_Form7();
		}

		// WPForms integration (if WPForms is active)
		if (defined('WPFORMS_VERSION') || class_exists('WPForms') || function_exists('wpforms')) {
			new \SmartCT\Integrations\WPForms();
		}

		// Ninja Forms integration (if Ninja Forms is active)
		if (defined('NINJA_FORMS_VERSION') || class_exists('Ninja_Forms') || function_exists('Ninja_Forms')) {
			new \SmartCT\Integrations\Ninja_Forms();
		}

		// Fluent Forms integration (if Fluent Forms is active)
		if (defined('FLUENTFORM') || function_exists('wpFluentForm') || class_exists('\FluentForm\App\Modules\Component\Component')) {
			new \SmartCT\Integrations\Fluent_Forms();
		}

		// Formidable Forms integration (if Formidable is active)
		if (defined('FRM_VERSION') || class_exists('FrmAppHelper') || function_exists('load_formidable_forms')) {
			new \SmartCT\Integrations\Formidable_Forms();
		}

		// Forminator integration (if Forminator is active)
		if (defined('FORMINATOR_VERSION') || class_exists('\Forminator') || function_exists('forminator')) {
			new \SmartCT\Integrations\Forminator_Forms();
		}

		// Everest Forms integration (if Everest Forms is active)
		if (function_exists('evf') || class_exists('EverestForms') || defined('EVF_PLUGIN_FILE')) {
			new \SmartCT\Integrations\Everest_Forms();
		}

		// SureForms integration (if SureForms is active)
		if (defined('SRFM_SLUG') || class_exists('\SRFM\Inc\Form_Submit')) {
			new \SmartCT\Integrations\Sure_Forms();
		}
	}
}
