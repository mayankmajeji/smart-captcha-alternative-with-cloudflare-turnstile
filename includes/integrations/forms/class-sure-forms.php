<?php
/**
 * SureForms Integration for SmartCT
 *
 * @package SmartCT
 * @subpackage SmartCT/integrations
 */
declare(strict_types=1);

namespace SmartCT\Integrations;

use SmartCT\Settings;
use SmartCT\Turnstile;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Sure_Forms {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	public function __construct() {
		if ( ! $this->is_active() ) {
			return;
		}

		$this->settings = new Settings();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Render widget before/after submit
		add_action('srfm_before_submit_button', array( $this, 'render_before_submit' ), 20, 1);
		add_action('srfm_after_submit_button', array( $this, 'render_after_submit' ), 20, 1);

		// Validate on submission (REST flow) as early as possible
		add_filter('srfm_before_fields_processing', array( $this, 'validate_submission' ), 5);

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_active(): bool {
		return defined('SRFM_SLUG') || class_exists('\\SRFM\\Inc\\Form_Submit');
	}

	/**
	 * Register settings fields for SureForms under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_sureforms_enable',
			'label'       => __('Enable on SureForms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to SureForms forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'sureforms',
			'priority'    => 10,
			'group'       => 'sureforms',
			'group_title' => '<h2>SureForms</h2>',
		);

		$fields[] = array(
			'field_id'    => 'smartct_sureforms_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Where to display the widget within the form.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_submit'  => __('After Submit Button', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'before_submit',
			'tab'         => 'form_plugins',
			'section'     => 'sureforms',
			'priority'    => 20,
			'group'       => 'sureforms',
		);

		return $fields;
	}

	public function render_before_submit( $form_id ): void {
		$enabled = (bool) $this->settings->get_option('smartct_sureforms_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('smartct_sureforms_position', 'before_submit');
		if ( $position !== 'before_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('sureforms');
	}

	public function render_after_submit( $form_id ): void {
		$enabled = (bool) $this->settings->get_option('smartct_sureforms_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('turnstile_sureforms_position', 'before_submit');
		if ( $position !== 'after_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('sureforms');
	}

	/**
	 * Validate SureForms submission with Turnstile
	 *
	 * Runs early in the REST submission flow. If invalid, returns an error response.
	 *
	 * @param array $form_data
	 * @return array
	 */
	public function validate_submission( $form_data ) {
		$enabled = (bool) $this->settings->get_option('turnstile_sureforms_enable', false);
		if ( ! $enabled || ! is_array($form_data) ) {
			return $form_data;
		}

		$turnstile = new Turnstile();
		$valid = $turnstile->verify();
		if ( $valid ) {
			return $form_data;
		}

		wp_send_json_error(array(
			'message' => $this->settings->get_option('smartct_custom_error_message', __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile')),
			'position' => 'header',
		));
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_sureforms_enable', false);
		$items[] = array(
			'label' => 'SureForms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
