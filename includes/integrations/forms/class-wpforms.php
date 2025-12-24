<?php
/**
 * WPForms Integration for SmartCT
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

class WPForms {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	public function __construct() {
		if ( ! $this->is_wpforms_active() ) {
			return;
		}

		$this->settings = new Settings();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Render widget before/after submit (WPForms passes 1 param: $form_data)
		add_action('wpforms_display_submit_before', array( $this, 'render_before_submit' ), 20, 1);
		add_action('wpforms_display_submit_after', array( $this, 'render_after_submit' ), 20, 1);

		// Validate on submit
		add_action('wpforms_process', array( $this, 'validate_submission' ), 5, 3);

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_wpforms_active(): bool {
		return defined('WPFORMS_VERSION') || class_exists('WPForms') || function_exists('wpforms');
	}

	/**
	 * Register settings fields for WPForms under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_wpforms_enable',
			'label'       => __('Enable on WPForms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to WPForms forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'wpforms',
			'priority'    => 10,
			'group'       => 'wpforms',
			'group_title' => '<h2>WPForms</h2>',
		);

		$fields[] = array(
			'field_id'    => 'smartct_wpforms_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Where to display the widget within the form.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_submit'  => __('After Submit Button', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'before_submit',
			'tab'         => 'form_plugins',
			'section'     => 'wpforms',
			'priority'    => 20,
			'group'       => 'wpforms',
		);

		return $fields;
	}

	public function render_before_submit( $form_data ): void {
		$enabled = (bool) $this->settings->get_option('smartct_wpforms_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('smartct_wpforms_position', 'before_submit');
		if ( $position !== 'before_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('wpforms');
	}

	public function render_after_submit( $form_data ): void {
		$enabled = (bool) $this->settings->get_option('smartct_wpforms_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('turnstile_wpforms_position', 'before_submit');
		if ( $position !== 'after_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('wpforms');
	}

	/**
	 * Validate WPForms submission with Turnstile
	 *
	 * @param array $fields
	 * @param array $entry
	 * @param array $form_data
	 * @return void
	 */
	public function validate_submission( $fields, $entry, $form_data ): void {
		$enabled = (bool) $this->settings->get_option('turnstile_wpforms_enable', false);
		if ( ! $enabled ) {
			return;
		}

		$turnstile = new Turnstile();
		$valid = $turnstile->verify();
		if ( $valid ) {
			return;
		}

		// Add form-level error
		if ( function_exists('wpforms') ) {
			$fid = isset($form_data['id']) ? (int) $form_data['id'] : 0;
			if ( ! isset(wpforms()->process->errors[ $fid ]) ) {
				wpforms()->process->errors[ $fid ] = array();
			}
			wpforms()->process->errors[ $fid ]['header'] = __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile');
		}
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_wpforms_enable', false);
		$items[] = array(
			'label' => 'WPForms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
