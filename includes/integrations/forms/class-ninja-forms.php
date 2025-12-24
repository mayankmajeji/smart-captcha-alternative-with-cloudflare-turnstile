<?php

/**
 * Ninja Forms Integration for SmartCT
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

class Ninja_Forms {

	/**
	 * @var Settings
	 */
	private Settings $settings;
	/**
	 * Ensure we render only once per request
	 *
	 * @var bool
	 */
	private bool $rendered = false;

	public function __construct() {
		if ( ! $this->is_nf_active() ) {
			return;
		}

		$this->settings = new Settings();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Render after the form (below submit button) using the after_form_display ACTION (passes $form_id)
		add_action('ninja_forms_after_form_display', array( $this, 'render_after_form' ), 999, 1);

		// Safety: remove any legacy hooks that might still be attached from previous versions
		remove_filter('ninja_forms_display_before_fields', array( $this, 'filter_before_fields' ), 20);
		remove_filter('ninja_forms_display_after_fields', array( $this, 'filter_after_fields' ), 20);
		remove_action('ninja_forms_display_before_fields', array( $this, 'render_before_submit' ), 20);
		remove_action('ninja_forms_display_after_fields', array( $this, 'render_after_submit' ), 20);
		remove_action('ninja_forms_display_after_form', array( $this, 'render_after_form' ), 20);

		// Validation: add error if verification fails
		add_action('ninja_forms_before_submission', array( $this, 'validate_submission' ));

		// Expose to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_nf_active(): bool {
		return defined('NINJA_FORMS_VERSION') || class_exists('Ninja_Forms') || function_exists('Ninja_Forms');
	}

	/**
	 * Register settings fields for Ninja Forms under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_nf_enable',
			'label'       => __('Enable on Ninja Forms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Ninja Forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'ninja_forms',
			'priority'    => 10,
			'group'       => 'ninja_forms',
			'group_title' => '<h2>Ninja Forms</h2>',
		);

		// Position option removed; we always render before the submit button for Ninja Forms.

		return $fields;
	}

	// Render after the form markup (action callback)
	public function render_after_form( $form_id ): void {
		if ( $this->rendered ) {
			return;
		}
		$enabled = (bool) $this->settings->get_option('smartct_nf_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('ninja-forms');
		$this->rendered = true;
	}


	// Placeholder for validation pending finalization of NF processing API usage
	public function validate_submission(): void {
		$enabled = (bool) $this->settings->get_option('smartct_nf_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$turnstile = new Turnstile();
		if ( $turnstile->verify() ) {
			return;
		}
		$message = __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile');
		if ( function_exists('ninja_forms_add_error') ) {
			// Add a general error
			ninja_forms_add_error('turnstile', $message);
		}
	}

	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_nf_enable', false);
		$items[] = array(
			'label' => 'Ninja Forms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
