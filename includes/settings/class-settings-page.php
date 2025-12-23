<?php

/**
 * Settings Page Class
 *
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP\Settings;

use TurnstileWP\Settings\Tabs\WooCommerce_Tab;

class Settings_Page
{

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Sensitive fields that should not be included in hidden fields
	 *
	 * @var array
	 */
	private array $sensitive_fields = array(
		'secret_key',
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->settings = new Settings();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void
	{
		add_action('admin_menu', array($this, 'add_menu_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Add menu page
	 */
	public function add_menu_page(): void
	{
		add_menu_page(
			__('Smart Cloudflare Turnstile Settings', 'smart-cloudflare-turnstile'),
			__('Smart Cloudflare Turnstile', 'smart-cloudflare-turnstile'),
			'manage_options',
			'turnstilewp-settings',
			array($this, 'render_page'),
			'dashicons-shield',
			30
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings(): void
	{
		register_setting('turnstilewp_settings', 'turnstilewp_settings', array(
			'type' => 'array',
			'sanitize_callback' => array($this, 'sanitize_settings'),
		));
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings(array $input): array
	{
		$sanitized = array();
		foreach ($input as $key => $value) {
			// Skip sensitive fields if they're empty (to preserve existing values)
			if (in_array($key, $this->sensitive_fields, true) && empty($value)) {
				continue;
			}
			$sanitized[$key] = sanitize_text_field($value);
		}
		return $sanitized;
	}

	/**
	 * Render settings page
	 */
	public function render_page(): void
	{
		if (! current_user_can('manage_options')) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation doesn't require nonce verification
		$active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
		$tabs = $this->get_tabs();
?>
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<h2 class="nav-tab-wrapper">
			<?php foreach ($tabs as $tab) : ?>
				<a href="?page=turnstilewp-settings&tab=<?php echo esc_attr($tab->get_id()); ?>"
					class="nav-tab <?php echo $active_tab === $tab->get_id() ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons <?php echo esc_attr($tab->get_icon()); ?>"></span>
					<?php echo esc_html($tab->get_label()); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<form method="post" action="options.php">
			<?php
			settings_fields('turnstilewp_settings');
			do_settings_sections('turnstilewp_settings');
			$this->render_tab_content($active_tab);
			submit_button();
			?>
		</form>
<?php
	}

	/**
	 * Get tabs
	 */
	private function get_tabs(): array
	{
		$tabs = array(
			new WooCommerce_Tab(),
			// Add other tabs here
		);

		usort($tabs, function ($a, $b) {
			return $a->get_priority() <=> $b->get_priority();
		});

		return $tabs;
	}

	/**
	 * Render tab content
	 */
	private function render_tab_content(string $active_tab): void
	{
		$tabs = $this->get_tabs();
		foreach ($tabs as $tab) {
			if ($tab->get_id() === $active_tab) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is already escaped by the tab
				echo $tab->get_content();
				break;
			}
		}
	}

	/**
	 * Get hidden fields for other tabs
	 */
	private function get_hidden_fields(string $current_tab): string
	{
		$output = '';
		$all_settings = $this->settings->get_all_options();

		foreach ($all_settings as $key => $value) {
			// Skip sensitive fields
			if (in_array($key, $this->sensitive_fields, true)) {
				continue;
			}

			// Skip fields from current tab
			if (strpos($key, $current_tab . '_') === 0) {
				continue;
			}

			$output .= sprintf(
				'<input type="hidden" name="turnstilewp_settings[%s]" value="%s" />',
				esc_attr($key),
				esc_attr($value)
			);
		}

		return $output;
	}
}
