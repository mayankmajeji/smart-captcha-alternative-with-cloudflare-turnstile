<?php

/**
 * Plugin settings class
 *
 * Handles all plugin settings with backwards-compatible key naming.
 * This class uses a dual-key naming pattern (tswp_* and legacy unprefixed keys)
 * to maintain backwards compatibility.
 *
 * @see SETTINGS-KEYS.md for detailed documentation on key naming patterns
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Option name in WordPress options table
	 */
	private const OPTION_NAME = 'turnstilewp_settings';

	/**
	 * Default settings
	 *
	 * @var array
	 */
	private array $defaults = array(
		'tswp_site_key' => '',
		'tswp_secret_key' => '',
		'tswp_theme' => 'auto',
		'tswp_show_for_logged_in' => false,
		'tswp_enable_login' => true,
		'tswp_enable_register' => true,
		'tswp_enable_lost_password' => true,
		'tswp_enable_comments' => true,
		'tswp_debug_mode' => false,
		'tswp_keys_verified' => false,
		// WooCommerce fields
		'tswp_woo_login' => false,
		'tswp_woo_register' => false,
		'tswp_woo_reset_password' => false,
		'tswp_woo_checkout' => false,
		'tswp_woo_checkout_guest_only' => false,
		'tswp_woo_checkout_location' => 'before_payment',
		'tswp_woo_pay_for_order' => false,
	);

	/**
	 * Centralized settings fields array
	 *
	 * @var array
	 */
	private array $fields = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->add_default_options();
		$this->register_centralized_fields();
	}

	/**
	 * Get plugin settings
	 *
	 * Returns all settings with the tswp_* prefix.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$settings = get_option(self::OPTION_NAME, array());
		return wp_parse_args($settings, $this->defaults);
	}

	/**
	 * Get a specific option
	 *
	 * Retrieves a setting value. All keys must use the tswp_* prefix.
	 * Constants defined in wp-config.php take priority over database values.
	 *
	 * Example:
	 *   $settings->get_option('tswp_site_key');
	 *
	 * @param string $key Option key (must include tswp_ prefix).
	 * @param mixed  $default Default value if option not found.
	 * @return mixed
	 */
	public function get_option( string $key, $default = null ) {
		// Constants override (allow keys defined in wp-config.php)
		$const_map = array(
			'tswp_site_key'   => 'TURNSTILEWP_SITE_KEY',
			'tswp_secret_key' => 'TURNSTILEWP_SECRET_KEY',
		);
		
		if ( isset($const_map[ $key ]) && defined($const_map[ $key ]) ) {
			return constant($const_map[ $key ]);
		}

		$settings = $this->get_settings();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Add default options
	 */
	public function add_default_options(): void {
		if ( ! get_option(self::OPTION_NAME) ) {
			add_option(self::OPTION_NAME, $this->defaults);
		}
	}

	/**
	 * Register settings
	 */
	public function register_settings(): void {
		register_setting(
			'turnstilewp_settings',
			self::OPTION_NAME,
			array(
				'type' => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest' => false,
				'error_bag' => 'turnstilewp_settings_errors',
			)
		);
		// No section/field registration here; handled by centralized system and renderer.
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Settings input.
	 * @return array
	 */
	public function sanitize_settings( $input ): array {
		if ( ! is_array($input) ) {
			$input = array();
		}
		// Ensure fields are up-to-date (integrations may hook fields later in the load order)
		$this->register_centralized_fields();
		$existing_settings = $this->get_settings();
		$sanitized = $existing_settings; // Start with all existing settings
		$fields_structure = $this->get_fields_structure();

		foreach ( $fields_structure as $tab_sections ) {
			foreach ( $tab_sections as $section_fields ) {
				foreach ( $section_fields as $field ) {
					$id = $field['field_id'];
					$type = $field['type'] ?? 'text';
					$default = $field['default'] ?? '';
					// Only update if present in POST
					if ( array_key_exists($id, $input) ) {
						$value = $input[ $id ];
						if ( ! empty($field['sanitize_callback']) && is_callable($field['sanitize_callback']) ) {
							$sanitized[ $id ] = call_user_func($field['sanitize_callback'], $value);
						} else {
							switch ( $type ) {
								case 'text':
								case 'select':
									$sanitized[ $id ] = sanitize_text_field($value);
									break;
								case 'multiselect':
									$sanitized[ $id ] = array_map('sanitize_text_field', is_array($value) ? $value : array());
									break;
								case 'checkbox':
									$sanitized[ $id ] = ( $value === '1' || $value === 1 || $value === true ) ? 1 : 0;
									break;
								case 'textarea':
									$sanitized[ $id ] = sanitize_textarea_field($value);
									break;
								case 'number':
									$sanitized[ $id ] = intval($value);
									break;
								case 'email':
									$sanitized[ $id ] = sanitize_email($value);
									break;
								case 'url':
									$sanitized[ $id ] = esc_url_raw($value);
									break;
								default:
									$sanitized[ $id ] = sanitize_text_field($value);
							}
						}
					}
				}
			}
		}

		// If keys changed or either key is empty (without constants), reset verification status
		$saved_site   = $sanitized['tswp_site_key'] ?? '';
		$saved_secret = $sanitized['tswp_secret_key'] ?? '';
		$prev_site    = $existing_settings['tswp_site_key'] ?? '';
		$prev_secret  = $existing_settings['tswp_secret_key'] ?? '';
		$has_const_site   = defined('TURNSTILEWP_SITE_KEY') && TURNSTILEWP_SITE_KEY;
		$has_const_secret = defined('TURNSTILEWP_SECRET_KEY') && TURNSTILEWP_SECRET_KEY;
		if (
			( ! $has_const_site || ! $has_const_secret ) &&
			( ( empty($saved_site) || empty($saved_secret) ) || ( $saved_site !== $prev_site || $saved_secret !== $prev_secret ) )
		) {
			update_option('turnstilewp_keys_verified', 0);
		}

		add_settings_error(
			'turnstilewp_settings_errors',
			'settings_updated',
			__('Settings saved successfully.', 'smart-cloudflare-turnstile'),
			'updated'
		);

		return $sanitized;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu(): void {
		$svg_path = TURNSTILEWP_PLUGIN_DIR . 'assets/images/turnstilewp-plugin-icon.svg';
		$svg_data = @file_get_contents($svg_path);
		$icon_data_uri = $svg_data ? ( 'data:image/svg+xml;base64,' . base64_encode($svg_data) ) : 'dashicons-shield-alt';

		// Top-level points to Settings
		add_menu_page(
			__('Smart Cloudflare Turnstile', 'smart-cloudflare-turnstile'),
			__('Smart Cloudflare Turnstile', 'smart-cloudflare-turnstile'),
			'manage_options',
			'turnstilewp-settings',
			array( $this, 'render_settings_main_page' ),
			$icon_data_uri,
			65
		);
		// Settings submenu (same slug as top-level)
		add_submenu_page(
			'turnstilewp-settings',
			__('Settings', 'smart-cloudflare-turnstile'),
			__('Settings', 'smart-cloudflare-turnstile'),
			'manage_options',
			'turnstilewp-settings',
			array( $this, 'render_settings_main_page' )
		);
		// Integrations submenu (cards)
		add_submenu_page(
			'turnstilewp-settings',
			__('Integrations', 'smart-cloudflare-turnstile'),
			__('Integrations', 'smart-cloudflare-turnstile'),
			'manage_options',
			'turnstilewp-integrations',
			array( $this, 'render_integrations_page' )
		);
		// Tools
		add_submenu_page(
			'turnstilewp-settings',
			__('Tools', 'smart-cloudflare-turnstile'),
			__('Tools', 'smart-cloudflare-turnstile'),
			'manage_options',
			'turnstilewp-tools',
			array( $this, 'render_tools_page' )
		);
		// Help
		add_submenu_page(
			'turnstilewp-settings',
			__('Help', 'smart-cloudflare-turnstile'),
			__('Help', 'smart-cloudflare-turnstile'),
			'manage_options',
			'turnstilewp-help',
			array( $this, 'render_help_page' )
		);
	}

	/**
	 * Render the new main settings page
	 */
	public function render_settings_main_page(): void {
		if ( ! current_user_can('manage_options') ) {
			return;
		}

		require_once TURNSTILEWP_PLUGIN_DIR . 'includes/admin/views/settings-main.php';
	}

	/**
	 * Collect and organize all settings fields via filter
	 */
	public function register_centralized_fields(): void {
		$fields = array();
		$fields = apply_filters('turnstilewp_settings', $fields);
		// Normalize field IDs to tswp_ prefix
		foreach ( $fields as &$field ) {
			if ( ! empty($field['field_id']) ) {
				$id = (string) $field['field_id'];
				// Strip legacy "turnstile_" prefix if present
				if ( strpos($id, 'turnstile_') === 0 ) {
					$id = substr($id, strlen('turnstile_'));
				}
				// Ensure tswp_ prefix
				if ( strpos($id, 'tswp_') !== 0 ) {
					$id = 'tswp_' . $id;
				}
				$field['field_id'] = $id;
			}
		}
		unset($field);
		$this->fields = $this->organize_fields($fields);
	}

	/**
	 * Organize fields by tab, section, and priority
	 *
	 * @param array $fields
	 * @return array
	 */
	private function organize_fields( array $fields ): array {
		$organized = array();
		foreach ( $fields as $field ) {
			$tab = $field['tab'] ?? 'general';
			$section = $field['section'] ?? 'default';
			$priority = $field['priority'] ?? 10;
			$field_id = $field['field_id'] ?? '';
			if ( ! $field_id ) {
				continue;
			}
			$organized[ $tab ][ $section ][ $priority . '_' . $field_id ] = $field;
		}
		// Sort by tab, section, then priority
		foreach ( $organized as $tab => &$sections ) {
			foreach ( $sections as $section => &$fields ) {
				ksort($fields, SORT_NATURAL);
			}
			unset($fields);
		}
		unset($sections);
		return $organized;
	}

	/**
	 * Get the full, organized fields structure for rendering
	 *
	 * @return array
	 */
	public function get_fields_structure(): array {
		return $this->fields;
	}

	// Placeholder renderers for new pages
	public function render_dashboard_page(): void {
		if ( ! current_user_can('manage_options') ) {
			return;
		}
		require_once TURNSTILEWP_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
	}
	public function render_integrations_page(): void {
		require_once TURNSTILEWP_PLUGIN_DIR . 'includes/admin/views/integrations-main.php';
	}
	public function render_tools_page(): void {
		// Use the Tools_Tab class to render the tools page
		require_once __DIR__ . '/settings/tabs/class-tools-tab.php';
		$tools_tab = new \TurnstileWP\Settings\Tabs\Tools_Tab();
		$tools_tab->render_tools_page();
	}
	public function render_faqs_page(): void {
		require_once TURNSTILEWP_PLUGIN_DIR . 'includes/admin/templates/faqs-page.php';
	}

	public function render_help_page(): void {
		require_once TURNSTILEWP_PLUGIN_DIR . 'includes/admin/templates/help-page.php';
	}
}
