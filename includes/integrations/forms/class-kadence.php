<?php

/**
 * Kadence Forms Integration for SmartCT
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

class Kadence {

	/**
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * @var Verify
	 */
	private Verify $verify;

	public function __construct() {
		if ( ! $this->is_kadence_active() ) {
			return;
		}

		$this->settings = new Settings();
		$this->verify = new Verify();

		// Register settings fields in centralized system
		add_filter('smartct_settings', array( $this, 'register_settings_fields' ));

		// Ensure Turnstile script is enqueued with render=auto for Kadence forms
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_turnstile_script' ), 20);

		// Inject widget into Kadence block forms using render_block filter
		add_filter('render_block', array( $this, 'inject_widget' ), 10, 2);

		// Validate submission
		add_action('kadence_blocks_form_verify_nonce', array( $this, 'validate_submission' ), 10, 1);

		// Expose status to Dashboard "Other Integrations"
		add_filter('smartct_integrations', array( $this, 'register_dashboard_status' ));
	}

	/**
	 * Enqueue Turnstile script with render=auto for Kadence forms
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function enqueue_turnstile_script(): void {
		$enabled = (bool) $this->settings->get_option('smartct_kadence_enable', false);
		if ( ! $enabled ) {
			return;
		}

		$site_key = $this->settings->get_option('smartct_site_key');
		if ( empty($site_key) ) {
			return;
		}

		// Enqueue with render=auto (auto-renders widgets with cf-turnstile class)
		// Only enqueue if not already enqueued
		if ( ! wp_script_is('cloudflare-turnstile', 'enqueued') ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js?render=auto', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
				array(),
				// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
				null,
				true
			);
		}
	}

	/**
	 * Check if Kadence Blocks is active
	 *
	 * @return bool
	 */
	private function is_kadence_active(): bool {
		return function_exists('kadence_blocks') || defined('KADENCE_BLOCKS_VERSION');
	}

	/**
	 * Register settings fields for Kadence under Form Plugins tab
	 */
	public function register_settings_fields( array $fields ): array {
		$fields[] = array(
			'field_id'    => 'smartct_kadence_enable',
			'label'       => __('Enable on Kadence Forms', 'smart-cloudflare-turnstile'),
			'description' => __('Add Turnstile verification to Kadence Advanced Form blocks.', 'smart-cloudflare-turnstile'),
			'type'        => 'checkbox',
			'default'     => false,
			'tab'         => 'form_plugins',
			'section'     => 'kadence',
			'priority'    => 10,
			'group'       => 'kadence',
			'group_title' => '<h2>Kadence Forms</h2>',
		);

		return $fields;
	}

	/**
	 * Inject Turnstile widget into Kadence block content
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block data.
	 * @return string
	 */
	public function inject_widget( string $block_content, array $block ): string {
		$enabled = (bool) $this->settings->get_option('smartct_kadence_enable', false);
		if ( ! $enabled ) {
			return $block_content;
		}

		// Only process on frontend
		if ( is_admin() ) {
			return $block_content;
		}

		// Validate block structure
		if ( empty($block_content) || ! is_array($block) || empty($block['blockName']) ) {
			return $block_content;
		}

		$block_name = (string) $block['blockName'];
		
		// Only process Kadence form blocks
		// Check for both possible block names
		if ( $block_name !== 'kadence/advanced-form' && $block_name !== 'kadence/form' && strpos($block_name, 'kadence') === false ) {
			return $block_content;
		}
		
		// Additional check: ensure this is actually a form block (has form tag in content)
		if ( strpos($block_content, '<form') === false ) {
			return $block_content;
		}

		// Avoid duplicate injection
		if ( strpos($block_content, 'cf-turnstile') !== false ) {
			return $block_content;
		}

		// Ensure Turnstile script is enqueued
		$site_key = $this->settings->get_option('smartct_site_key');
		if ( empty($site_key) ) {
			return $block_content;
		}

		// Generate unique ID for this widget (matching reference plugin format)
		$unique_id = wp_rand();
		$unique_suffix = '-kadence-' . $unique_id;

		// Use output buffering to capture widget HTML (matching reference plugin approach)
		ob_start();

		// Get widget settings
		$theme = $this->settings->get_option('smartct_theme', 'auto');
		$size = $this->settings->get_option('smartct_widget_size', 'normal');
		$language = $this->settings->get_option('smartct_language', 'auto');
		$appearance = $this->settings->get_option('smartct_appearance_mode', 'always');
		
		// Build widget HTML matching reference plugin format exactly
		// Note: ID format is cf-turnstile{unique_id} (no hyphen between turnstile and unique_id)
		?>
		<div id="cf-turnstile<?php echo esc_attr($unique_suffix); ?>" class="cf-turnstile" data-sitekey="<?php echo esc_attr($site_key); ?>" data-theme="<?php echo esc_attr($theme); ?>" data-size="<?php echo esc_attr($size); ?>" data-language="<?php echo esc_attr($language); ?>" data-appearance="<?php echo esc_attr($appearance); ?>" data-action="kadence-<?php echo esc_attr($unique_id); ?>" data-retry="auto" data-retry-interval="1000" data-refresh-expired="auto"></div>
		<?php
		
		$widget = ob_get_clean();

		// Clean up widget HTML (remove extra line breaks for tight Kadence layout)
		$widget = preg_replace('/<br.*?>/', '', $widget);

		// Try multiple patterns to find the submit button (matching reference plugin approach)
		
		// Pattern 1: Match reference plugin pattern - any element with wp-block-kadence-advanced-form-submit or kb-button class
		$pattern1 = '/(<[^>]*class=("|\')[^"\"]*(wp-block-kadence-advanced-form-submit|kb-button)[^"\"]*("|\')[^>]*>)/';
		if ( preg_match($pattern1, $block_content) ) {
			return preg_replace($pattern1, $widget . '$1', $block_content, 1);
		}

		// Pattern 2: Look for submit field wrapper div (kb-submit-field) - insert widget at start of wrapper
		$pattern2 = '/(<div[^>]*class=("|\')[^"\"]*kb-submit-field[^"\"]*("|\')[^>]*>)/i';
		if ( preg_match($pattern2, $block_content) ) {
			return preg_replace($pattern2, '$1' . $widget, $block_content, 1);
		}

		// Pattern 3: Look for button with kb-adv-form-submit-button class
		$pattern3 = '/(<button[^>]*class=("|\')[^"\"]*kb-adv-form-submit-button[^"\"]*("|\')[^>]*>)/i';
		if ( preg_match($pattern3, $block_content) ) {
			return preg_replace($pattern3, $widget . '$1', $block_content, 1);
		}

		// Pattern 4: Look for any button with type="submit" inside the form
		$pattern4 = '/(<button[^>]*type=("|\')submit\\2[^>]*>)/i';
		if ( preg_match($pattern4, $block_content) ) {
			return preg_replace($pattern4, $widget . '$1', $block_content, 1);
		}

		// Final fallback: inject before closing form tag (always works if form tag exists)
		$pos = strripos($block_content, '</form>');
		if ( $pos !== false ) {
			return substr_replace($block_content, $widget, $pos, 0);
		}

		// Last resort: append to block content
		return $block_content . $widget;
	}

	/**
	 * Validate Kadence form submission with Turnstile
	 *
	 * @param string $nonce The nonce value.
	 * @return string
	 */
	public function validate_submission( string $nonce ): string {
		$enabled = (bool) $this->settings->get_option('smartct_kadence_enable', false);
		if ( ! $enabled ) {
			return $nonce;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
		$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

		if ( empty($token) ) {
			wp_die(esc_html__('Please complete the Turnstile verification.', 'smart-cloudflare-turnstile'));
		}

		$valid = $this->verify->verify_token($token);
		if ( ! $valid ) {
			wp_die(esc_html__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile'));
		}

		return $nonce;
	}

	/**
	 * Add status row to Dashboard "Other Integrations"
	 */
	public function register_dashboard_status( array $items ): array {
		$enabled = (bool) $this->settings->get_option('smartct_kadence_enable', false);
		$items[] = array(
			'label' => 'Kadence Forms',
			'enabled' => $enabled,
			'configure_url' => admin_url('admin.php?page=smartct-integrations&integration_tab=form_plugins'),
		);
		return $items;
	}
}
