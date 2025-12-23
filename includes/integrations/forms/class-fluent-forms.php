<?php
/**
 * Fluent Forms Integration for TurnstileWP
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

class Fluent_Forms {

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

		// Render widget after the form using Fluent Forms hook
		add_action('fluentform/after_form_render', array( $this, 'render_after_form' ), 999, 1);

		// Render widget before the submit button using Fluent Forms item render hook
		add_action('fluentform/render_item_submit_button', array( $this, 'render_before_submit' ), 1, 2);

		// Validate submission
		add_action('fluentform/before_insert_submission', array( $this, 'validate_submission' ), 10, 3);

		// Expose to Dashboard "Other Integrations"
		add_filter('turnstilewp_integrations', array( $this, 'register_dashboard_status' ));
	}

	private function is_ff_active(): bool {
		return defined('FLUENTFORM') || function_exists('wpFluentForm') || class_exists('\FluentForm\App\Modules\Component\Component');
	}

	/**
	 * Register settings fields for Fluent Forms under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'tswp_fluent_enable',
			'label'       => __('Enable on Fluent Forms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Fluent Forms.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'fluent_forms',
			'priority'    => 10,
			'group'       => 'fluent_forms',
			'group_title' => '<h2>Fluent Forms</h2>',
		);

		$fields[] = array(
			'field_id'    => 'tswp_fluent_position',
			'label'       => __('Widget Position', 'smart-cloudflare-turnstile'),
			'description' => __('Choose where to display the widget.', 'smart-cloudflare-turnstile'),
			'type'        => 'select',
			'options'     => array(
				'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				'after_form'    => __('After Form', 'smart-cloudflare-turnstile'),
			),
			'default'     => 'after_form',
			'tab'         => 'form_plugins',
			'section'     => 'fluent_forms',
			'priority'    => 20,
			'group'       => 'fluent_forms',
		);

		return $fields;
	}

	public function render_after_form( $form ): void {
		$enabled = (bool) $this->settings->get_option('tswp_fluent_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('tswp_fluent_position', 'after_form');
		if ( $position !== 'after_form' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('fluent-forms');
	}

	public function validate_submission( $insertData, $data, $form ): void {
		$enabled = (bool) $this->settings->get_option('tswp_fluent_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$turnstile = new Turnstile();
		if ( $turnstile->verify() ) {
			return;
		}
		$message = __('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile');
		if ( function_exists('wp_send_json_error') && wp_doing_ajax() ) {
			wp_send_json_error(array( 'message' => $message ), 400);
		}
		wp_die(esc_html($message));
	}

	public function render_before_submit( $element, $form ): void {
		$enabled = (bool) $this->settings->get_option('turnstile_fluent_enable', false);
		if ( ! $enabled ) {
			return;
		}
		$position = (string) $this->settings->get_option('turnstile_fluent_position', 'after_form');
		if ( $position !== 'before_submit' ) {
			return;
		}
		$turnstile = new Turnstile();
		$turnstile->render('fluent-forms');
	}

	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('turnstile_fluent_enable', false);
		$items[] = array(
			'label' => 'Fluent Forms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=turnstilewp-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
