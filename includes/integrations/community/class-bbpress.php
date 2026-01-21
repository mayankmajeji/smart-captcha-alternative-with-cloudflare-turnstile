<?php

/**
 * bbPress Integration for SmartCT
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

class bbPress {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * @var Verify
	 */
	private Verify $verify;

	public function __construct() {
		if ( ! $this->is_bbpress_active() ) {
			return;
		}

		$this->settings = new Settings();
		$this->verify = new Verify();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Topic creation hooks
		if ( $this->settings->get_option('smartct_bbpress_create', false) ) {
			add_action('bbp_theme_before_topic_form_submit_wrapper', array( $this, 'render_topic_widget' ));
			add_action('bbp_new_topic_pre_extras', array( $this, 'validate_topic' ));
		}

		// Reply creation hooks
		if ( $this->settings->get_option('smartct_bbpress_reply', false) ) {
			add_action('bbp_theme_before_reply_form_submit_wrapper', array( $this, 'render_reply_widget' ));
			add_action('bbp_new_reply_pre_extras', array( $this, 'validate_reply' ));
		}

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	/**
	 * Check if bbPress is active
	 *
	 * @return bool
	 */
	private function is_bbpress_active(): bool {
		return class_exists('bbPress') || function_exists('bbp_get_version');
	}

	/**
	 * Check if Turnstile should be shown (guest only check)
	 *
	 * @return bool
	 */
	private function should_show_turnstile(): bool {
		$guest_only = (bool) $this->settings->get_option('smartct_bbpress_guest_only', false);
		if ( $guest_only && is_user_logged_in() ) {
			return false;
		}
		return true;
	}

	/**
	 * Register settings fields for bbPress under Community tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_bbpress_create',
			'label'       => __('Enable on Topic Creation', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification when users create new topics.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'community',
			'section'     => 'bbpress',
			'priority'    => 10,
			'group'       => 'bbpress',
			'group_title' => '<h2>bbPress</h2>',
		);

		$fields[] = array(
			'field_id'    => 'smartct_bbpress_reply',
			'label'       => __('Enable on Reply Creation', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification when users create replies.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'community',
			'section'     => 'bbpress',
			'priority'    => 20,
			'group'       => 'bbpress',
		);

		$fields[] = array(
			'field_id'    => 'smartct_bbpress_guest_only',
			'label'       => __('Guest Users Only', 'smart-cloudflare-turnstile'),
			'description' => __('Only show Turnstile verification for guests (not logged-in users).', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'community',
			'section'     => 'bbpress',
			'priority'    => 30,
			'group'       => 'bbpress',
		);

		$fields[] = array(
			'field_id'    => 'smartct_bbpress_align',
			'label'       => __('Widget Alignment', 'smart-cloudflare-turnstile'),
			'description' => __('Choose the alignment of the Turnstile widget.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'left'  => __('Left', 'smart-cloudflare-turnstile'),
				'right' => __('Right', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'left',
			'tab'         => 'community',
			'section'     => 'bbpress',
			'priority'     => 40,
			'group'       => 'bbpress',
		);

		return $fields;
	}

	/**
	 * Render Turnstile widget for topic creation
	 */
	public function render_topic_widget(): void {
		if ( ! $this->should_show_turnstile() ) {
			return;
		}

		$align = (string) $this->settings->get_option('smartct_bbpress_align', 'left');
		$style = $align === 'right' ? '<style>#bbpress-forums #cf-turnstile { float: right; }</style>' : '';

		echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS styling

		$turnstile = new Turnstile();
		$turnstile->render('bbpress-topic');
	}

	/**
	 * Render Turnstile widget for reply creation
	 */
	public function render_reply_widget(): void {
		if ( ! $this->should_show_turnstile() ) {
			return;
		}

		$align = (string) $this->settings->get_option('smartct_bbpress_align', 'left');
		$style = $align === 'right' ? '<style>#bbpress-forums .cf-turnstile { float: right; }</style>' : '';

		echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS styling

		$turnstile = new Turnstile();
		$turnstile->render('bbpress-reply');
	}

	/**
	 * Validate topic creation
	 */
	public function validate_topic(): void {
		if ( ! $this->should_show_turnstile() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Validating REQUEST_METHOD existence
		if ( ! isset($_SERVER['REQUEST_METHOD']) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			bbp_add_error('bbp_topic_turnstile', esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
		$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

		if ( empty($token) ) {
			bbp_add_error('bbp_topic_turnstile', esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
			return;
		}

		$valid = $this->verify->verify_token($token);
		if ( ! $valid ) {
			bbp_add_error('bbp_topic_turnstile', esc_html__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile'));
		}
	}

	/**
	 * Validate reply creation
	 */
	public function validate_reply(): void {
		if ( ! $this->should_show_turnstile() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Validating REQUEST_METHOD existence
		if ( ! isset($_SERVER['REQUEST_METHOD']) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			bbp_add_error('bbp_reply_turnstile', esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
		$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

		if ( empty($token) ) {
			bbp_add_error('bbp_reply_turnstile', esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
			return;
		}

		$valid = $this->verify->verify_token($token);
		if ( ! $valid ) {
			bbp_add_error('bbp_reply_turnstile', esc_html__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile'));
		}
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$create_enabled = (bool) $this->settings->get_option('smartct_bbpress_create', false);
		$reply_enabled = (bool) $this->settings->get_option('smartct_bbpress_reply', false);
		$enabled = $create_enabled || $reply_enabled;

		$items[] = array(
			'label' => 'bbPress',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=community'),
		);
		return $items;
	}
}
