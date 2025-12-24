<?php

/**
 * Contact Form 7 Integration for SmartCT
 *
 * @package SmartCT
 * @subpackage SmartCT/integrations
 */

namespace SmartCT\Integrations;

use SmartCT\Settings;
use SmartCT\Turnstile;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Contact_Form7 {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	public function __construct() {
		if ( ! $this->is_cf7_active() ) {
			return;
		}

		$this->settings = new Settings();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Inject widget into CF7 forms (late to ensure current form is set)
		add_filter('wpcf7_form_elements', array( $this, 'inject_widget' ), 99);

		// Validate submission
		add_filter('wpcf7_spam', array( $this, 'validate_submission' ), 10, 2);

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_cf7_active(): bool {
		return defined('WPCF7_VERSION') || function_exists('wpcf7');
	}

	/**
	 * Register settings fields for Contact Form 7 under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_cf7_enable',
			'label'       => __('Enable on Contact Form 7', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Contact Form 7 forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'contact_form_7',
			'priority'    => 10,
			'group'       => 'cf7',
			'group_title' => '<h2>Contact Form 7</h2>',
		);

		$fields[] = array(
			'field_id'    => 'smartct_cf7_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Where to display the widget within the form.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_form'    => __('After Form', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'before_submit',
			'tab'         => 'form_plugins',
			'section'     => 'contact_form_7',
			'priority'    => 20,
			'group'       => 'cf7',
		);

		return $fields;
	}

	/**
	 * Inject Turnstile widget into CF7 form HTML
	 */
	public function inject_widget( string $form_html ): string {
		$enabled = (bool) $this->settings->get_option('smartct_cf7_enable', false);
		if ( ! $enabled ) {
			return $form_html;
		}

		$position = (string) $this->settings->get_option('smartct_cf7_position', 'before_submit');

		// Capture widget HTML output
		ob_start();
		$turnstile = new Turnstile();
		$turnstile->render('contact-form-7');
		$widget = ob_get_clean();

		if ( ! $widget ) {
			return $form_html;
		}

		// Simple injection strategy: append before submit or at the end of form HTML
		if ( $position === 'before_submit' ) {
			// Try to insert before the first submit button; fallback to append at end
			$pattern = '/(<input[^>]*type=("|\')submit\\2[^>]*>|<button[^>]*type=("|\')submit\\3[^>]*>.*?<\\/button>)/i';
			if ( preg_match($pattern, $form_html) ) {
				$form_html = preg_replace($pattern, $widget . '$1', $form_html, 1);
			} else {
				$form_html .= $widget;
			}
		} else {
			$form_html .= $widget;
		}

		return $form_html;
	}

	/**
	 * Validate CF7 submission with Turnstile
	 *
	 * @param bool                         $is_spam
	 * @param \WPCF7_Submission|\WP_Error $submission
	 * @return bool
	 */
	public function validate_submission( $is_spam, $submission ) {
		$enabled = (bool) $this->settings->get_option('smartct_cf7_enable', false);
		if ( ! $enabled ) {
			return $is_spam;
		}

		$turnstile = new Turnstile();
		$valid = $turnstile->verify();
		if ( ! $valid ) {
			// Mark as spam/invalid
			return true;
		}
		return $is_spam;
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('smartct_cf7_enable', false);
		$items[] = array(
			'label' => 'Contact Form 7',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
