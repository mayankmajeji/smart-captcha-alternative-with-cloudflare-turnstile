<?php
/**
 * Forminator Integration for SmartCT
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

class Forminator_Forms {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	public function __construct() {
		if ( ! $this->is_active() ) {
		return;
		}
		$this->settings = new Settings();

		// Settings
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Placement: modify submit markup (before/after)
		add_filter('forminator_render_form_submit_markup', array( $this, 'filter_submit_markup' ), 10, 4);

		// Validation
		add_action('forminator_custom_form_submit_errors', array( $this, 'validate_submission' ), 10, 3);

		// Dashboard status
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_active(): bool {
		return defined('FORMINATOR_VERSION') || class_exists('\Forminator') || function_exists('forminator');
	}

	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_forminator_enable',
			'label'       => __('Enable on Forminator', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Forminator forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'forminator_forms',
			'priority'    => 10,
			'group'       => 'forminator_forms',
			'group_title' => '<h2>Forminator</h2>',
		);

		$fields[] = array(
			'field_id'    => 'smartct_forminator_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Choose where to display the widget.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_submit'  => __('After Submit Button', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'after_submit',
			'tab'         => 'form_plugins',
			'section'     => 'forminator_forms',
			'priority'    => 20,
			'group'       => 'forminator_forms',
		);

		return $fields;
	}

	/**
	 * Filter submit markup to inject Turnstile widget
	 *
	 * @param string $html
	 * @param int    $form_id
	 * @param int    $post_id
	 * @param string $nonce
	 * @return string
	 */
	public function filter_submit_markup( $html, $form_id, $post_id, $nonce ) {
		$enabled = (bool) $this->settings->get_option('smartct_forminator_enable', false);
		if ( ! $enabled ) {
			return $html;
		}
		$site_key = (string) $this->settings->get_option('smartct_site_key', '');
		if ( ! $site_key ) {
			return $html;
		}
		$position = (string) $this->settings->get_option('smartct_forminator_position', 'after_submit');

		// Ensure Turnstile API script is present
		wp_enqueue_script(
			'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
			array(),
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
			null,
			true
		);

		$widget = sprintf(
			'<div class="smartct-forminator-container" style="display:block;margin:10px 0;"><div class="cf-turnstile" data-sitekey="%s"></div></div>',
			esc_attr($site_key)
		);

		if ( $position === 'before_submit' ) {
			return $widget . $html;
		}
		return $html . $widget;
	}

	public function validate_submission( $submit_errors, $form_id, $field_data_array ) {
		$enabled = (bool) $this->settings->get_option('smartct_forminator_enable', false);
		if ( ! $enabled ) {
			return $submit_errors;
		}
		$turnstile = new Turnstile();
		if ( $turnstile->verify() ) {
			return $submit_errors;
		}
		if ( ! is_array($submit_errors) ) {
			$submit_errors = array();
		}
		$submit_errors[] = __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile');
		return $submit_errors;
	}

	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_forminator_enable', false);
		$items[] = array(
			'label' => 'Forminator',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
