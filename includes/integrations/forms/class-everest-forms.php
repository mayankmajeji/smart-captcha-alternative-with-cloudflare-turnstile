<?php
/**
 * Everest Forms Integration for SmartCT
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

class Everest_Forms {

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

		// Render before/after submit button
		add_action('everest_forms_display_submit_before', array( $this, 'render_before_submit' ), 20, 1);
		add_action('everest_forms_display_submit_after', array( $this, 'render_after_submit' ), 20, 1);

		// Validate submission (inject initial error if Turnstile fails)
		add_filter('everest_forms_process_initial_errors', array( $this, 'validate_submission' ), 10, 2);

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_active(): bool {
		return function_exists('evf') || class_exists('EverestForms') || defined('EVF_PLUGIN_FILE');
	}

	/**
	 * Register settings fields for Everest Forms under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_everest_forms_enable',
			'label'       => __('Enable on Everest Forms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Everest Forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'everest_forms',
			'priority'    => 10,
			'group'       => 'everest_forms',
			'group_title' => '<h2>Everest Forms</h2>',
		);

		$fields[] = array(
			'field_id'    => 'smartct_everest_forms_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Where to display the widget within the form.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_submit'  => __('After Submit Button', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'before_submit',
			'tab'         => 'form_plugins',
			'section'     => 'everest_forms',
			'priority'    => 20,
			'group'       => 'everest_forms',
		);

		return $fields;
	}

	public function render_before_submit( $form_data ): void {
		$enabled = (bool) $this->settings->get_option('smartct_everest_forms_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('smartct_everest_forms_position', 'before_submit');
		if ( $position !== 'before_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('everest_forms');
	}

	public function render_after_submit( $form_data ): void {
		$enabled = (bool) $this->settings->get_option('smartct_everest_forms_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('turnstile_everest_forms_position', 'before_submit');
		if ( $position !== 'after_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('everest_forms');
	}

	/**
	 * Inject validation error if Turnstile check fails
	 *
	 * @param array $errors
	 * @param array $form_data
	 * @return array
	 */
	public function validate_submission( array $errors, array $form_data ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_everest_forms_enable', false);
		if ( ! $enabled ) {
			return $errors;
		}

		$turnstile = new Turnstile();
		$is_valid = $turnstile->verify();
		if ( $is_valid ) {
			return $errors;
		}

		$form_id = isset($form_data['id']) ? (int) $form_data['id'] : 0;
		if ( $form_id > 0 ) {
			if ( empty($errors[ $form_id ]) ) {
				$errors[ $form_id ] = array();
			}
		$errors[ $form_id ]['header'] = $this->settings->get_option('smartct_custom_error_message', __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));

		// Store validation error flag with proper prefix to avoid conflicts
		update_option('smartct_evf_validation_error', 'yes');
		}

		return $errors;
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_everest_forms_enable', false);
		$items[] = array(
			'label' => 'Everest Forms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
