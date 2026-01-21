<?php

/**
 * MailPoet Integration for SmartCT
 *
 * @package SmartCT
 * @subpackage SmartCT/integrations
 */

namespace SmartCT\Integrations;

use SmartCT\Settings;
use SmartCT\Turnstile;
use SmartCT\Verify;

if ( ! defined('ABSPATH') ) {
	exit;
}

class MailPoet {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * @var Verify
	 */
	private Verify $verify;

	public function __construct() {
		if ( ! $this->is_mailpoet_active() ) {
			return;
		}

		$this->settings = new Settings();
		$this->verify = new Verify();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Enqueue JavaScript for Gutenberg block forms
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));

		// Also try the filter for widget-based forms (legacy support)
		add_filter('mailpoet_form_widget_post_process', array( $this, 'inject_widget' ));

		// Validate submission
		add_action('mailpoet_subscription_before_subscribe', array( $this, 'validate_submission' ), 10, 3);

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	/**
	 * Check if MailPoet is active
	 *
	 * @return bool
	 */
	private function is_mailpoet_active(): bool {
		return class_exists('\MailPoet\API\API') || defined('MAILPOET_VERSION');
	}

	/**
	 * Register settings fields for MailPoet under Newsletters tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_mailpoet_enable',
			'label'       => __('Enable on MailPoet', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to MailPoet subscription forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'newsletters',
			'section'     => 'mailpoet',
			'priority'    => 10,
			'group'       => 'mailpoet',
			'group_title' => '<h2>MailPoet</h2>',
		);

		return $fields;
	}

	/**
	 * Enqueue JavaScript for MailPoet Gutenberg block forms
	 */
	public function enqueue_scripts(): void {
		// Check if MailPoet integration is enabled
		$enabled = (bool) $this->settings->get_option('smartct_mailpoet_enable', false);
		if ( ! $enabled ) {
			return;
		}

		// Only enqueue on frontend
		if ( is_admin() ) {
			return;
		}

		// Check if MailPoet is actually active
		if ( ! $this->is_mailpoet_active() ) {
			return;
		}

		// Check if site key is set (required for Turnstile to work)
		$site_key = $this->settings->get_option('smartct_site_key');
		if ( empty($site_key) ) {
			return;
		}

		// Skip on 404 pages and other non-content pages where forms won't appear
		if ( is_404() || is_search() ) {
			return;
		}

		// Use minified version in production
		$suffix = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
		
		// Enqueue MailPoet integration script
		wp_enqueue_script(
			'smartct-mailpoet',
			SMARTCT_PLUGIN_URL . 'assets/js/mailpoet' . $suffix . '.js',
			array( 'jquery' ),
			SMARTCT_VERSION,
			true
		);

		// Localize script with site key for JavaScript rendering
		wp_localize_script(
			'smartct-mailpoet',
			'smartctMailPoet',
			array(
				'siteKey' => $site_key,
			)
		);
	}

	/**
	 * Inject Turnstile widget into MailPoet form HTML (legacy widget support)
	 *
	 * @param string $form_html The form HTML.
	 * @return string
	 */
	public function inject_widget( string $form_html ): string {
		$enabled = (bool) $this->settings->get_option('smartct_mailpoet_enable', false);
		if ( ! $enabled ) {
			return $form_html;
		}

		// Capture widget HTML output
		ob_start();
		$turnstile = new Turnstile();
		$turnstile->render('mailpoet');
		$widget = ob_get_clean();

		if ( ! $widget ) {
			return $form_html;
		}

		// Insert before the submit button (MailPoet uses class="mailpoet_submit")
		$pattern = '/(<input[^>]*class="mailpoet_submit"[^>]*>)/';
		if ( preg_match($pattern, $form_html) ) {
			$form_html = preg_replace($pattern, $widget . '$1', $form_html, 1);
		} else {
			// Fallback: append at the end
			$form_html .= $widget;
		}

		return $form_html;
	}

	/**
	 * Validate MailPoet submission with Turnstile
	 *
	 * @param array  $data       Subscription data.
	 * @param array  $segment_ids Segment IDs.
	 * @param object $form       Form object.
	 * @return void
	 * @throws \MailPoet\UnexpectedValueException If validation fails.
	 */
	public function validate_submission( $data, $segment_ids, $form ): void {
		$enabled = (bool) $this->settings->get_option('smartct_mailpoet_enable', false);
		if ( ! $enabled ) {
			return;
		}

		// MailPoet posts fields under $_POST['data'][...], but also check $_POST directly
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
		$posted_data = ( isset($_POST['data']) && is_array($_POST['data']) ) ? $_POST['data'] : array();
		$token = isset($posted_data['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($posted_data['cf-turnstile-response'])) : '';

		// Fallback: check $_POST directly
		if ( empty($token) && isset($_POST['cf-turnstile-response']) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
			$token = sanitize_text_field(wp_unslash($_POST['cf-turnstile-response']));
		}

		if ( empty($token) ) {
			throw new \MailPoet\UnexpectedValueException(__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
		}

		$valid = $this->verify->verify_token($token);
		if ( ! $valid ) {
			throw new \MailPoet\UnexpectedValueException(__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile'));
		}
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('smartct_mailpoet_enable', false);
		$items[] = array(
			'label' => 'MailPoet',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=newsletters'),
		);
		return $items;
	}
}
