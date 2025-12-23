<?php

/**
 * Single Settings File - All-in-One Example
 * This demonstrates how all settings functionality COULD be combined
 *
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP;

/**
 * All-in-One Settings Class
 * Combines data layer, business logic, UI controller, and rendering
 */
class Single_Settings
{

	// === DATA LAYER PROPERTIES ===
	private const OPTION_NAME = 'turnstilewp_settings';
	private array $sensitive_fields = array('secret_key');

	// === BUSINESS LOGIC PROPERTIES ===
	private array $defaults = array(
		'site_key' => '',
		'secret_key' => '',
		'theme' => 'auto',
		'show_for_logged_in' => false,
		'enable_login' => true,
		'enable_register' => true,
		'enable_lost_password' => true,
		'enable_comments' => true,
		'debug_mode' => false,
		'keys_verified' => false,
		'woo_login' => false,
		'woo_register' => false,
		'woo_reset_password' => false,
		'woo_checkout' => false,
	);

	// === UI CONTROLLER PROPERTIES ===
	private array $tabs = array();
	private string $current_tab = 'general';

	/**
	 * Constructor - Initialize everything
	 */
	public function __construct()
	{
		$this->init_hooks();
		$this->setup_tabs();
		$this->add_default_options();
	}

	// === DATA LAYER METHODS ===

	/**
	 * Get all options with decryption
	 */
	public function get_all_options(): array
	{
		$options = get_option(self::OPTION_NAME, array());

		// Decrypt sensitive fields
		foreach ($this->sensitive_fields as $field) {
			if (isset($options[$field])) {
				$options[$field] = $this->decrypt_value($options[$field]);
			}
		}

		return wp_parse_args($options, $this->defaults);
	}

	/**
	 * Get single option
	 */
	public function get_option(string $key, $default = null)
	{
		$options = $this->get_all_options();
		return $options[$key] ?? $default;
	}

	/**
	 * Update option with encryption
	 */
	public function update_option(string $key, $value): bool
	{
		$options = $this->get_all_options();

		// Encrypt if sensitive field
		if (in_array($key, $this->sensitive_fields, true)) {
			$value = $this->encrypt_value($value);
		}

		$options[$key] = $value;
		return update_option(self::OPTION_NAME, $options);
	}

	/**
	 * Encrypt sensitive values
	 */
	private function encrypt_value(string $value): string
	{
		if (empty($value)) {
			return '';
		}

		$key = $this->get_encryption_key();
		$method = 'aes-256-cbc';
		$ivlen = openssl_cipher_iv_length($method);
		$iv = openssl_random_pseudo_bytes($ivlen);

		$encrypted = openssl_encrypt($value, $method, $key, 0, $iv);
		if ($encrypted === false) {
			return '';
		}

		return base64_encode($iv . $encrypted);
	}

	/**
	 * Decrypt sensitive values
	 */
	private function decrypt_value(string $value): string
	{
		if (empty($value)) {
			return '';
		}

		$key = $this->get_encryption_key();
		$method = 'aes-256-cbc';
		$ivlen = openssl_cipher_iv_length($method);

		$decoded = base64_decode($value);
		if ($decoded === false) {
			return '';
		}

		$iv = substr($decoded, 0, $ivlen);
		$encrypted = substr($decoded, $ivlen);

		$decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
		return $decrypted === false ? '' : $decrypted;
	}

	/**
	 * Get encryption key
	 */
	private function get_encryption_key(): string
	{
		$key = defined('AUTH_KEY') ? AUTH_KEY : wp_salt('auth');
		return hash('sha256', $key, true);
	}

	// === BUSINESS LOGIC METHODS ===

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void
	{
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('wp_ajax_turnstilewp_verify_keys', array($this, 'verify_keys_ajax'));
	}

	/**
	 * Add default options
	 */
	private function add_default_options(): void
	{
		if (! get_option(self::OPTION_NAME)) {
			add_option(self::OPTION_NAME, $this->defaults);
		}
	}

	/**
	 * Register WordPress settings
	 */
	public function register_settings(): void
	{
		register_setting(
			'turnstilewp_settings',
			self::OPTION_NAME,
			array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_settings'),
				'show_in_rest' => false,
			)
		);
	}

	/**
	 * Sanitize settings input
	 */
	public function sanitize_settings(array $input): array
	{
		$sanitized = $this->get_all_options();

		foreach ($input as $key => $value) {
			// Skip empty sensitive fields to preserve existing
			if (in_array($key, $this->sensitive_fields, true) && empty($value)) {
				continue;
			}

			// Sanitize based on field type
			if (is_bool($this->defaults[$key] ?? false)) {
				$sanitized[$key] = ($value === '1' || $value === 1 || $value === true) ? 1 : 0;
			} else {
				$sanitized[$key] = sanitize_text_field($value);
			}
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
	 * AJAX handler for key verification
	 */
	public function verify_keys_ajax(): void
	{
		check_ajax_referer('turnstilewp_verify_keys', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Insufficient permissions.', 'smart-cloudflare-turnstile'));
		}

		$site_key = isset($_POST['site_key']) ? sanitize_text_field(wp_unslash($_POST['site_key'])) : '';
		$secret_key = isset($_POST['secret_key']) ? sanitize_text_field(wp_unslash($_POST['secret_key'])) : '';

		if (empty($site_key) || empty($secret_key)) {
			wp_send_json_error(__('Both site key and secret key are required.', 'smart-cloudflare-turnstile'));
		}

		// Simulate verification logic here
		$verified = $this->verify_turnstile_keys($site_key, $secret_key);

		if ($verified) {
			update_option('turnstilewp_keys_verified', 1);
			wp_send_json_success(__('Keys verified successfully!', 'smart-cloudflare-turnstile'));
		} else {
			wp_send_json_error(__('Key verification failed.', 'smart-cloudflare-turnstile'));
		}
	}

	/**
	 * Verify Turnstile keys with Cloudflare
	 */
	private function verify_turnstile_keys(string $site_key, string $secret_key): bool
	{
		// Implementation would go here
		return true; // Simplified for example
	}

	// === UI CONTROLLER METHODS ===

	/**
	 * Add admin menu page
	 */
	public function add_admin_menu(): void
	{
		add_menu_page(
			__('TurnstileWP', 'smart-cloudflare-turnstile'),
			__('TurnstileWP', 'smart-cloudflare-turnstile'),
			'manage_options',
			'smart-cloudflare-turnstile',
			array($this, 'render_settings_page'),
			'dashicons-shield-alt',
			65
		);
	}

	/**
	 * Setup tabs structure
	 */
	private function setup_tabs(): void
	{
		$this->tabs = array(
			'general' => __('General', 'smart-cloudflare-turnstile'),
			'wordpress' => __('WordPress', 'smart-cloudflare-turnstile'),
			'woocommerce' => __('WooCommerce', 'smart-cloudflare-turnstile'),
			'advanced' => __('Advanced', 'smart-cloudflare-turnstile'),
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation for displaying content only, no data modification
		$this->current_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
	}

	// === VIEW/RENDERING METHODS ===

	/**
	 * Render the main settings page
	 */
	public function render_settings_page(): void
	{
		if (! current_user_can('manage_options')) {
			return;
		}

		$values = $this->get_all_options();
?>
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

		<?php $this->render_info_section(); ?>
		<?php settings_errors('turnstilewp_settings_errors'); ?>

		<form method="post" action="options.php">
			<?php settings_fields('turnstilewp_settings'); ?>

			<?php $this->render_tabs(); ?>
			<?php $this->render_tab_content($values); ?>

			<?php submit_button(); ?>
		</form>

		<?php $this->render_scripts(); ?>
	<?php
	}

	/**
	 * Render info section
	 */
	private function render_info_section(): void
	{
	?>
		<div class="turnstilewp-info">
			<h2><?php esc_html_e('About TurnstileWP', 'smart-cloudflare-turnstile'); ?></h2>
			<p><?php esc_html_e('TurnstileWP is a lightweight and privacy-first integration of Cloudflare Turnstile with WordPress forms.', 'smart-cloudflare-turnstile'); ?></p>
		</div>
	<?php
	}

	/**
	 * Render navigation tabs
	 */
	private function render_tabs(): void
	{
	?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ($this->tabs as $tab_id => $tab_label) : ?>
				<a href="?page=turnstilewp&tab=<?php echo esc_attr($tab_id); ?>"
					class="nav-tab <?php echo $this->current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html($tab_label); ?>
				</a>
			<?php endforeach; ?>
		</h2>
	<?php
	}

	/**
	 * Render active tab content
	 */
	private function render_tab_content(array $values): void
	{
	?>
		<div class="turnstilewp-tabs">
			<?php
			switch ($this->current_tab) {
				case 'general':
					$this->render_general_tab($values);
					break;
				case 'WordPress':
					$this->render_wordpress_tab($values);
					break;
				case 'woocommerce':
					$this->render_woocommerce_tab($values);
					break;
				case 'advanced':
					$this->render_advanced_tab($values);
					break;
				default:
					$this->render_general_tab($values);
			}
			?>
		</div>
	<?php
	}

	/**
	 * Render general tab
	 */
	private function render_general_tab(array $values): void
	{
	?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e('Site Key', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="text" name="turnstilewp_settings[site_key]"
						value="<?php echo esc_attr($values['site_key']); ?>"
						class="regular-text" />
					<p class="description"><?php esc_html_e('Your Cloudflare Turnstile site key.', 'smart-cloudflare-turnstile'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('Secret Key', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="password" name="turnstilewp_settings[secret_key]"
						value="<?php echo esc_attr($values['secret_key']); ?>"
						class="regular-text" />
					<p class="description"><?php esc_html_e('Your Cloudflare Turnstile secret key.', 'smart-cloudflare-turnstile'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('Theme', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<select name="turnstilewp_settings[theme]">
						<option value="auto" <?php selected($values['theme'], 'auto'); ?>><?php esc_html_e('Auto', 'smart-cloudflare-turnstile'); ?></option>
						<option value="light" <?php selected($values['theme'], 'light'); ?>><?php esc_html_e('Light', 'smart-cloudflare-turnstile'); ?></option>
						<option value="dark" <?php selected($values['theme'], 'dark'); ?>><?php esc_html_e('Dark', 'smart-cloudflare-turnstile'); ?></option>
					</select>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Render WordPress tab
	 */
	private function render_wordpress_tab(array $values): void
	{
	?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e('Enable on Login', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[enable_login]"
						value="1" <?php checked($values['enable_login'], 1); ?> />
					<label><?php esc_html_e('Show Turnstile on login form', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('Enable on Registration', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[enable_register]"
						value="1" <?php checked($values['enable_register'], 1); ?> />
					<label><?php esc_html_e('Show Turnstile on registration form', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('Enable on Comments', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[enable_comments]"
						value="1" <?php checked($values['enable_comments'], 1); ?> />
					<label><?php esc_html_e('Show Turnstile on comment forms', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Render WooCommerce tab
	 */
	private function render_woocommerce_tab(array $values): void
	{
	?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e('WooCommerce Login', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[woo_login]"
						value="1" <?php checked($values['woo_login'], 1); ?> />
					<label><?php esc_html_e('Enable on WooCommerce login', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('WooCommerce Checkout', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[woo_checkout]"
						value="1" <?php checked($values['woo_checkout'], 1); ?> />
					<label><?php esc_html_e('Enable on WooCommerce checkout', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Render advanced tab
	 */
	private function render_advanced_tab(array $values): void
	{
	?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e('Debug Mode', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[debug_mode]"
						value="1" <?php checked($values['debug_mode'], 1); ?> />
					<label><?php esc_html_e('Enable debug logging', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('Show for Logged-in Users', 'smart-cloudflare-turnstile'); ?></th>
				<td>
					<input type="checkbox" name="turnstilewp_settings[show_for_logged_in]"
						value="1" <?php checked($values['show_for_logged_in'], 1); ?> />
					<label><?php esc_html_e('Show Turnstile even for logged-in users', 'smart-cloudflare-turnstile'); ?></label>
				</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Render JavaScript for the page
	 */
	private function render_scripts(): void
	{
	?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				// Key verification AJAX
				const verifyBtn = document.getElementById('turnstilewp-verify-keys');
				if (verifyBtn) {
					verifyBtn.addEventListener('click', function() {
						const siteKey = document.querySelector('input[name="turnstilewp_settings[site_key]"]').value;
						const secretKey = document.querySelector('input[name="turnstilewp_settings[secret_key]"]').value;

						if (!siteKey || !secretKey) {
							alert('<?php echo esc_js(__('Please enter both site key and secret key.', 'smart-cloudflare-turnstile')); ?>');
							return;
						}

						// AJAX verification logic would go here
						console.log('Verifying keys...');
					});
				}
			});
		</script>
<?php
	}
}

// Initialize the single settings class
if (is_admin()) {
	new Single_Settings();
}
