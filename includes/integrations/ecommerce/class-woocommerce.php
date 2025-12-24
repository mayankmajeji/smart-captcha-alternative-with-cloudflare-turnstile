<?php

/**
 * WooCommerce Integration for SmartCT
 *
 * @package SmartCT
 * @subpackage SmartCT/integrations
 */

namespace SmartCT\Integrations;

use SmartCT\Turnstile;
use SmartCT\Settings;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Class Turnstile_WooCommerce
 */
class Turnstile_WooCommerce
{

	/**
	 * Plugin settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Initialize the WooCommerce integration
	 */
	public function __construct()
	{
		// Only load if WooCommerce is active
		if (! $this->is_woocommerce_active()) {
			return;
		}

		// Initialize settings
		$this->settings = new Settings();

		// Register WooCommerce settings fields into centralized system (only when WooCommerce is active)
		add_filter('smartct_settings', array($this, 'register_settings_fields'));

		$this->init_hooks();
	}

	/**
	 * Check if WooCommerce is active
	 *
	 * @return bool
	 */
	private function is_woocommerce_active(): bool
	{
		return class_exists('WooCommerce');
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void
	{
		// Enqueue scripts
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Checkout integration
		if ($this->get_setting_bool('smartct_woo_checkout', 'woo_checkout', false)) {
			$this->init_checkout_hooks();
		}

		// Login integration
		if ($this->get_setting_bool('smartct_woo_login', 'woo_login', false)) {
			$this->init_login_hooks();
		}

		// Registration integration
		if ($this->get_setting_bool('smartct_woo_register', 'woo_register', false)) {
			$this->init_register_hooks();
		}

		// Password reset integration
		if ($this->get_setting_bool('smartct_woo_reset', 'woo_reset_password', false)) {
			$this->init_reset_hooks();
		}

		// Pay order integration
		if ($this->get_setting_bool('smartct_woo_pay_order', 'woo_pay_for_order', false)) {
			$this->init_pay_order_hooks();
		}
	}

	/**
	 * Enqueue required scripts
	 */
	public function enqueue_scripts(): void
	{
		// Check if we're on any WooCommerce-related page
		$is_woo_page = false;
		if (function_exists('is_woocommerce')) {
			$is_woo_page = is_woocommerce() || is_checkout() || is_account_page() || is_cart();
		} else {
			// Fallback if WooCommerce functions aren't loaded yet
			$is_woo_page = is_checkout() || is_account_page() || is_cart();
		}

		if (! $is_woo_page) {
			return;
		}

		if (! defined('SMARTCT_PLUGIN_URL') || ! defined('SMARTCT_VERSION')) {
			return;
		}

		// Ensure Turnstile API is available on Woo pages
		wp_enqueue_script(
			'cloudflare-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
			array(),
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
			null,
			true
		);

		wp_enqueue_script(
			'turnstile-woo',
			SMARTCT_PLUGIN_URL . 'assets/js/woocommerce.js',
			array('jquery', 'cloudflare-turnstile'),
			SMARTCT_VERSION,
			true
		);

		// Enqueue Turnstile styles for WooCommerce pages
		wp_enqueue_style(
			'smartct-frontend',
			SMARTCT_PLUGIN_URL . 'assets/css/turnstile.css',
			array(),
			SMARTCT_VERSION
		);

		// WooCommerce iframe styles are in assets/css/plugins/_woocommerce.scss
		// Dynamic styling is also handled in woocommerce.js for AJAX-loaded forms

		// Localize script for AJAX functionality
		wp_localize_script('turnstile-woo', 'smartctWoo', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('turnstile-woo-nonce'),
			'siteKey' => (string) (new \SmartCT\Settings())->get_option('smartct_site_key', ''),
		));
	}


	/**
	 * Initialize login hooks
	 */
	private function init_login_hooks(): void
	{
		add_action('woocommerce_login_form', array($this, 'render_login_field'));
		add_filter('authenticate', array($this, 'validate_login'), 21, 1);
	}

	/**
	 * Initialize registration hooks
	 */
	private function init_register_hooks(): void
	{
		add_action('woocommerce_register_form', array($this, 'render_register_field'));
		add_action('woocommerce_register_post', array($this, 'validate_register'), 10, 3);
	}

	/**
	 * Initialize password reset hooks
	 */
	private function init_reset_hooks(): void
	{
		add_action('woocommerce_lostpassword_form', array($this, 'render_reset_field'));
		add_action('woocommerce_reset_password', array($this, 'validate_reset'), 10, 1);
	}

	/**
	 * Initialize pay order hooks
	 */
	private function init_pay_order_hooks(): void
	{
		add_action('woocommerce_pay_order_before_submit', array($this, 'render_pay_order_field'));
		add_action('woocommerce_before_pay_action', array($this, 'validate_pay_order'), 10, 2);
	}

	/**
	 * Register WooCommerce settings fields into the centralized settings via filter
	 *
	 * @param array $fields
	 * @return array
	 */
	public function register_settings_fields(array $fields): array
	{
		$config_file = defined('SMARTCT_PLUGIN_DIR')
			? SMARTCT_PLUGIN_DIR . 'includes/settings/fields-woocommerce.php'
			: '';

		if (! $config_file || ! file_exists($config_file)) {
			return $fields;
		}

		$config = include $config_file;
		if (! is_array($config) || empty($config['woocommerce']['fields']) || ! is_array($config['woocommerce']['fields'])) {
			return $fields;
		}

		$wc_fields = $config['woocommerce']['fields'];
		$groups_added = array();

		// Map each field into the centralized structure expected by Settings::organize_fields()
		$priority = 10;
		foreach ($wc_fields as $field_id => $def) {
			$section = 'general';
			if (
				strpos($field_id, 'checkout') !== false
				|| in_array($field_id, array('turnstile_guest_only', 'turnstile_excluded_payment_methods', 'smartct_guest_only', 'smartct_excluded_payment_methods'), true)
			) {
				$section = 'checkout';
			} elseif (strpos($field_id, 'pay_order') !== false) {
				$section = 'pay_for_order';
			}

			// Derive logical groups for consistent styling
			$group = '';
			$group_title = '';
			if ($section === 'general') {
				if ($field_id === 'smartct_woo_login') {
					$group = 'wc_login_form';
					$group_title = '<h2>' . esc_html__('Login Form', 'smart-cloudflare-turnstile') . '</h2>';
				} elseif ($field_id === 'smartct_woo_register') {
					$group = 'wc_registration_form';
					$group_title = '<h2>' . esc_html__('Registration Form', 'smart-cloudflare-turnstile') . '</h2>';
				} elseif ($field_id === 'smartct_woo_reset') {
					$group = 'wc_lost_password_form';
					$group_title = '<h2>' . esc_html__('Lost Password Form', 'smart-cloudflare-turnstile') . '</h2>';
				}
			} elseif ($section === 'checkout') {
				$group = 'wc_checkout';
				$group_title = '<h2>' . esc_html__('Checkout', 'smart-cloudflare-turnstile') . '</h2>';
			} elseif ($section === 'pay_for_order') {
				$group = 'wc_pay_for_order';
				$group_title = '<h2>' . esc_html__('Pay For Order', 'smart-cloudflare-turnstile') . '</h2>';
			}

			$field_entry = array(
				'field_id'    => $field_id,
				'label'       => $def['label'] ?? '',
				'description' => $def['description'] ?? '',
				'type'        => $def['type'] ?? 'text',
				'options'     => $def['options'] ?? array(),
				'default'     => $def['default'] ?? '',
				'tab'         => 'woocommerce',
				'section'     => $section,
				'priority'    => $priority,
			);
			if ($group) {
				$field_entry['group'] = $group;
				if (empty($groups_added[$group]) && $group_title) {
					$field_entry['group_title'] = $group_title;
					$groups_added[$group] = true;
				}
			}
			$fields[] = $field_entry;
			$priority += 10;
		}

		return $fields;
	}

	/**
	 * Initialize checkout hooks
	 */
	private function init_checkout_hooks(): void
	{
		// Display Turnstile on checkout
		$position = (string) $this->get_setting_value('smartct_woo_checkout_position', 'woo_checkout_location', 'before_payment');
		switch ($position) {
			case 'before_payment':
				add_action('woocommerce_review_order_before_payment', array($this, 'render_checkout_field'));
				// WooCommerce Checkout Block: before Payment block
				add_filter('render_block_woocommerce/checkout-payment-block', array($this, 'render_block_prepend'), 999, 1);
				break;
			case 'after_payment':
				add_action('woocommerce_review_order_after_payment', array($this, 'render_checkout_field'));
				// WooCommerce Checkout Block: after Payment block
				add_filter('render_block_woocommerce/checkout-payment-block', array($this, 'render_block_append'), 999, 1);
				break;
			case 'before_billing':
				add_action('woocommerce_before_checkout_billing_form', array($this, 'render_checkout_field'));
				// WooCommerce Checkout Block: before Contact Information block
				add_filter('render_block_woocommerce/checkout-contact-information-block', array($this, 'render_block_prepend'), 999, 1);
				break;
			case 'after_billing':
				add_action('woocommerce_after_checkout_billing_form', array($this, 'render_checkout_field'));
				// WooCommerce Checkout Block: before Shipping Methods block (closest equivalent)
				add_filter('render_block_woocommerce/checkout-shipping-methods-block', array($this, 'render_block_prepend'), 999, 1);
				break;
			case 'before_submit':
				add_action('woocommerce_review_order_before_submit', array($this, 'render_checkout_field'));
				// WooCommerce Checkout Block: before Actions block
				add_filter('render_block_woocommerce/checkout-actions-block', array($this, 'render_block_prepend'), 999, 1);
				break;
		}

		// Validate checkout
		add_action('woocommerce_checkout_process', array($this, 'validate_checkout'));
		add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'validate_checkout_block'), 10, 2);

		// Clear session after order processing
		add_action('woocommerce_checkout_order_processed', array($this, 'clear_checkout_session'));
		add_action('woocommerce_store_api_checkout_order_processed', array($this, 'clear_checkout_session'));
	}

	/**
	 * Render checkout Turnstile field
	 */
	public function render_checkout_field(): void
	{
		if ($this->should_skip_turnstile()) {
			return;
		}
		// Allow only the div element with data attributes for Turnstile widget
		$allowed_html = array(
			'div' => array(
				'id'              => array(),
				'class'           => array(),
				'data-sitekey'    => array(),
				'data-theme'      => array(),
				'data-language'   => array(),
				'data-size'       => array(),
				'data-appearance' => array(),
				'data-action'     => array(),
				'data-callback'   => array(),
			),
		);
		echo wp_kses( $this->get_checkout_widget_markup(), $allowed_html );
	}

	/**
	 * Prepend widget to a WooCommerce Checkout Block section
	 *
	 * @param string $content
	 * @return string
	 */
	public function render_block_prepend(string $content): string
	{
		$widget = $this->get_checkout_widget_markup();
		return $widget . $content;
	}

	/**
	 * Append widget to a WooCommerce Checkout Block section
	 *
	 * @param string $content
	 * @return string
	 */
	public function render_block_append(string $content): string
	{
		$widget = $this->get_checkout_widget_markup();
		return $content . $widget;
	}

	/**
	 * Get widget markup for checkout contexts
	 *
	 * @return string
	 */
	private function get_checkout_widget_markup(): string
	{
		if ($this->should_skip_turnstile()) {
			return '';
		}
		ob_start();
		$turnstile = new Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'woocommerce-checkout',
			'callback'  => 'smartctWooCheckoutCallback',
			'unique_id' => '-wc-checkout',
		));
		return (string) ob_get_clean();
	}

	/**
	 * Render Turnstile field on login form
	 */
	public function render_login_field(): void
	{
		$turnstile = new Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'woocommerce-login',
			'callback'  => 'smartctWooLoginCallback',
			'unique_id' => '-wc-login',
		));
	}

	/**
	 * Render Turnstile field on registration form
	 */
	public function render_register_field(): void
	{
		$turnstile = new Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'woocommerce-register',
			'callback'  => 'smartctWooRegisterCallback',
			'unique_id' => '-wc-register',
		));
	}

	/**
	 * Render Turnstile field on password reset form
	 */
	public function render_reset_field(): void
	{
		$turnstile = new Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'woocommerce-reset',
			'callback'  => 'smartctWooResetCallback',
			'unique_id' => '-wc-reset',
		));
	}

	/**
	 * Render Turnstile field on pay order form
	 */
	public function render_pay_order_field(): void
	{
		$turnstile = new Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'woocommerce-pay-order',
			'callback'  => 'smartctWooPayOrderCallback',
			'unique_id' => '-wc-pay-order',
		));
	}

	/**
	 * Validate checkout
	 */
	public function validate_checkout(): void
	{
		if ($this->should_skip_turnstile()) {
			return;
		}

		$turnstile = new Turnstile();
		if (! $turnstile->verify()) {
			wc_add_notice(__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'), 'error');
		}
	}

	/**
	 * Validate checkout block
	 */
	public function validate_checkout_block($order, $request): void
	{
		if ($this->should_skip_turnstile()) {
			return;
		}

		$turnstile = new Turnstile();
		if (! $turnstile->verify()) {
			throw new \Exception(esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
		}
	}

	/**
	 * Validate login
	 */
	public function validate_login($user)
	{
		// Skip if not WooCommerce login
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification
		if (! isset($_POST['woocommerce-login-nonce'])) {
			return $user;
		}

		// Skip if already validated
		if (isset($_SESSION['turnstile_login_checked'])) {
			return $user;
		}

		$turnstile = new Turnstile();
		if (! $turnstile->verify()) {
			return new \WP_Error('turnstile_error', __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
		}

		$_SESSION['turnstile_login_checked'] = true;
		return $user;
	}

	/**
	 * Validate registration
	 */
	public function validate_register($errors, $username, $email)
	{
		$turnstile = new Turnstile();
		if (! $turnstile->verify()) {
			$errors->add('turnstile_error', __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
		}
		return $errors;
	}

	/**
	 * Validate password reset
	 */
	public function validate_reset($errors, $user)
	{
		$turnstile = new Turnstile();
		if (! $turnstile->verify()) {
			$errors->add('turnstile_error', __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
		}
		return $errors;
	}

	/**
	 * Validate pay order
	 */
	public function validate_pay_order($order)
	{
		$turnstile = new Turnstile();
		if (! $turnstile->verify()) {
			wc_add_notice(__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'), 'error');
		}
	}

	/**
	 * Clear checkout session
	 */
	public function clear_checkout_session(): void
	{
		if (isset($_SESSION['turnstile_checkout_checked'])) {
			unset($_SESSION['turnstile_checkout_checked']);
		}
	}

	/**
	 * Clear login session
	 */
	public function clear_login_session(): void
	{
		if (isset($_SESSION['turnstile_login_checked'])) {
			unset($_SESSION['turnstile_login_checked']);
		}
	}

	/**
	 * Check if Turnstile should be skipped
	 */
	private function should_skip_turnstile(): bool
	{
		// Skip if guest-only mode is enabled and user is logged in
		$guest_only = $this->get_setting_bool('turnstile_guest_only', 'woo_checkout_guest_only', false);
		if ($guest_only && is_user_logged_in()) {
			return true;
		}

		// Skip if payment method is excluded
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification
		if (isset($_POST['payment_method'])) {
			$excluded_methods = (array) $this->settings->get_option('turnstile_excluded_payment_methods', array());
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Payment method is validated against whitelist
			if (in_array(wp_unslash($_POST['payment_method']), $excluded_methods, true)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get boolean setting with fallback to legacy key
	 *
	 * @param string $primaryKey
	 * @param string $legacyKey
	 * @param bool   $default
	 * @return bool
	 */
	private function get_setting_bool(string $primaryKey, string $legacyKey, bool $default = false): bool
	{
		$value = $this->settings->get_option($primaryKey, null);
		if ($value === null || $value === '') {
			$value = $this->settings->get_option($legacyKey, $default);
		}
		return (bool) $value;
	}

	/**
	 * Get setting value with fallback to legacy key
	 *
	 * @param string $primaryKey
	 * @param string $legacyKey
	 * @param mixed  $default
	 * @return mixed
	 */
	private function get_setting_value(string $primaryKey, string $legacyKey, $default = null)
	{
		$value = $this->settings->get_option($primaryKey, null);
		if ($value === null || $value === '') {
			$value = $this->settings->get_option($legacyKey, $default);
		}
		return $value;
	}
}
