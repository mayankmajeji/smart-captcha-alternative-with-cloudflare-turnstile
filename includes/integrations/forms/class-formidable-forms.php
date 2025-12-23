<?php

/**
 * Formidable Forms Integration for TurnstileWP
 *
 * @package TurnstileWP
 * @subpackage TurnstileWP/integrations
 */

namespace TurnstileWP\Integrations;

use TurnstileWP\Settings;
use TurnstileWP\Turnstile;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Formidable_Forms {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	public function __construct() {
		if ( ! $this->is_ff_active() ) {
			return;
		}

		$this->settings = new Settings();

		// Register settings fields in centralized system
		add_filter('turnstilewp_settings', array( $this, 'register_settings_fields' ));

		// Inject widget markup adjacent to submit button HTML (outside the <button>)
		add_filter('frm_submit_button_html', array( $this, 'filter_submit_button_html' ), 10, 2);
		// Validate
		add_filter('frm_validate_entry', array( $this, 'validate_entry' ), 10, 2);

		// Expose status to Dashboard "Other Integrations"
		add_filter('turnstilewp_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_ff_active(): bool {
		return defined('FRM_VERSION') || class_exists('FrmAppHelper') || function_exists('load_formidable_forms');
	}

	/**
	 * Register settings fields for Formidable Forms under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'tswp_formidable_enable',
			'label'       => __('Enable on Formidable Forms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Formidable Forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'formidable_forms',
			'priority'    => 10,
			'group'       => 'formidable_forms',
			'group_title' => '<h2>Formidable Forms</h2>',
		);

		$fields[] = array(
			'field_id'    => 'tswp_formidable_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Choose where to display the widget near the submit button.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_submit'  => __('After Submit Button', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'after_submit',
			'tab'         => 'form_plugins',
			'section'     => 'formidable_forms',
			'priority'    => 20,
			'group'       => 'formidable_forms',
		);

		return $fields;
	}

	// Removed frm_form_classes hook to avoid signature mismatch and fatal error

	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('tswp_formidable_enable', false);
		$items[] = array(
			'label' => 'Formidable Forms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=turnstilewp-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}

	/**
	 * Place widget markup outside the submit button
	 *
	 * @param string $button_html
	 * @param array  $args
	 * @return string
	 */
	public function filter_submit_button_html( $button_html, $args ) {
		$enabled = (bool) $this->settings->get_option('tswp_formidable_enable', false);
		if ( ! $enabled ) {
			return $button_html;
		}
		$position = (string) $this->settings->get_option('tswp_formidable_position', 'after_submit');

	// Ensure script present
	wp_enqueue_script(
		'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
		array(),
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
		null,
		true
	);

		$site_key = (string) $this->settings->get_option('tswp_site_key', '');
		if ( ! $site_key ) {
			return $button_html;
		}

		// Use Turnstile class to render widget properly
		ob_start();
		$turnstile = new Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'formidable-form',
			'unique_id' => isset($args['form']) && isset($args['form']->id) ? '-frm-' . $args['form']->id : uniqid(),
			'class'     => 'turnstilewp-formidable-form',
		));
		$widget = ob_get_clean();

		$widget_wrapper = '<div class="turnstilewp-formidable-container" style="display:block;margin:10px 0;">' . $widget . '</div>';

		if ( $position === 'before_submit' ) {
			return $widget_wrapper . $button_html;
		}
		return $button_html . $widget_wrapper;
	}

	/**
	 * Validate submission (Formidable)
	 *
	 * @param array $errors
	 * @param array $values
	 * @return array
	 */
	public function validate_entry( $errors, $values ) {
		$enabled = (bool) $this->settings->get_option('tswp_formidable_enable', false);
		if ( ! $enabled ) {
			return $errors;
		}
		$turnstile = new Turnstile();
		if ( $turnstile->verify() ) {
			return $errors;
		}
		$errors['turnstile'] = __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile');
		return $errors;
	}
}
