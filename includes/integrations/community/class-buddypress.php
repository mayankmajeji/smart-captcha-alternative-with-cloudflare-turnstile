<?php

/**
 * BuddyPress Integration for SmartCT
 *
 * @package SmartCT
 * @subpackage SmartCT/integrations
 * @since 1.1.0
 * @author Mayank Majeji
 * @date 2025-01-21
 */

namespace SmartCT\Integrations;

use SmartCT\Settings;
use SmartCT\Turnstile;
use SmartCT\Verify;

if ( ! defined('ABSPATH') ) {
	exit;
}

class BuddyPress {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * @var Verify
	 */
	private Verify $verify;

	public function __construct() {
		if ( ! $this->is_buddypress_active() ) {
			return;
		}

		$this->settings = new Settings();
		$this->verify = new Verify();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Registration hooks
		if ( $this->settings->get_option('smartct_buddypress_register', false) ) {
			add_action('bp_before_registration_submit_buttons', array( $this, 'render_register_widget' ));
			add_action('bp_signup_validate', array( $this, 'validate_registration' ));
		}

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	/**
	 * Check if BuddyPress is active
	 *
	 * @return bool
	 */
	private function is_buddypress_active(): bool {
		return function_exists('buddypress') || class_exists('BuddyPress');
	}

	/**
	 * Register settings fields for BuddyPress under Community tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_buddypress_register',
			'label'       => __('Enable on Registration', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification when users register through BuddyPress.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'community',
			'section'     => 'buddypress',
			'priority'    => 10,
			'group'       => 'buddypress',
			'group_title' => '<h2>BuddyPress</h2>',
		);

		return $fields;
	}

	/**
	 * Render Turnstile widget for registration
	 */
	public function render_register_widget(): void {
		$turnstile = new Turnstile();
		$turnstile->render('buddypress-register');
	}

	/**
	 * Validate BuddyPress registration
	 */
	public function validate_registration(): void {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Validating REQUEST_METHOD existence
		if ( ! isset($_SERVER['REQUEST_METHOD']) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			wp_die(
				'<p><strong>' . esc_html__('ERROR:', 'smart-cloudflare-turnstile') . '</strong> ' . esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile') . '</p>',
				'smart-cloudflare-turnstile',
				array(
					'response' => 403,
					'back_link' => 1,
				)
			);
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
		$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

		if ( empty($token) ) {
			wp_die(
				'<p><strong>' . esc_html__('ERROR:', 'smart-cloudflare-turnstile') . '</strong> ' . esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile') . '</p>',
				'smart-cloudflare-turnstile',
				array(
					'response' => 403,
					'back_link' => 1,
				)
			);
			return;
		}

		$valid = $this->verify->verify_token($token);
		if ( ! $valid ) {
			wp_die(
				'<p><strong>' . esc_html__('ERROR:', 'smart-cloudflare-turnstile') . '</strong> ' . esc_html__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile') . '</p>',
				'smart-cloudflare-turnstile',
				array(
					'response' => 403,
					'back_link' => 1,
				)
			);
		}
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('smartct_buddypress_register', false);

		$items[] = array(
			'label' => 'BuddyPress',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=community'),
		);
		return $items;
	}
}
