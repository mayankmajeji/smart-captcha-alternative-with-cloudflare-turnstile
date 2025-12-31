<?php

/**
 * Tools Tab Class
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT\Settings\Tabs;

use SmartCT\Settings;

class Tools_Tab
{

	/**
	 * Render the Tools page
	 */
	public function render_tools_page(): void
	{
		$this->maybe_handle_export();
		if (! current_user_can('manage_options')) {
			return;
		}
		// Handle actions (import, export, reset)
		$this->handle_tools_actions();
		// Output the tools page template
		require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/tools-page.php';
	}

	/**
	 * Handle export via GET before any output
	 */
	public function maybe_handle_export(): void
	{
		if (
			isset($_GET['smartct_tools_action']) &&
			sanitize_text_field(wp_unslash($_GET['smartct_tools_action'])) === 'export' &&
			current_user_can('manage_options') &&
			isset($_GET['_wpnonce']) &&
			wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'smartct_tools_export')
		) {
			$settings = new \SmartCT\Settings();
			$this->export_settings($settings);
			exit;
		}
	}

	/**
	 * Handle Import, Export, and Reset actions
	 */
	private function handle_tools_actions(): void
	{
		// Early exit if no POST data
		if (empty($_POST)) {
			return;
		}

		// Verify user has permission FIRST
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'smart-cloudflare-turnstile'));
		}

		// Check if action is set before processing
		if (empty($_POST['smartct_tools_action'])) {
			return;
		}

		// Verify nonce exists BEFORE reading any other POST data
		if (! isset($_POST['smartct_tools_nonce'])) {
			wp_die(esc_html__('Security check failed: Missing nonce.', 'smart-cloudflare-turnstile'));
		}

		// Sanitize action to determine which nonce to verify
		$action = sanitize_text_field(wp_unslash($_POST['smartct_tools_action']));
		
		// Determine the correct nonce action based on the submitted action
		$nonce_action = '';
		switch ($action) {
			case 'import':
				$nonce_action = 'smartct_tools_import';
				break;
			case 'reset':
				$nonce_action = 'smartct_tools_reset';
				break;
			default:
				// Invalid action - fail securely
				wp_die(esc_html__('Security check failed: Invalid action.', 'smart-cloudflare-turnstile'));
		}

		// Verify the nonce for the specific action
		if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smartct_tools_nonce'])), $nonce_action)) {
			wp_die(esc_html__('Security check failed: Invalid nonce.', 'smart-cloudflare-turnstile'));
		}

		// Nonce and permissions verified - now process the action
		$settings = new Settings();

		switch ($action) {
			case 'import':
				$this->import_settings($settings);
				break;

			case 'reset':
				$this->reset_settings($settings);
				break;
		}
	}

	/**
	 * Export settings as JSON
	 */
	private function export_settings(Settings $settings): void
	{
		$data = $settings->get_settings();
		$json = wp_json_encode($data, JSON_PRETTY_PRINT);
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename=smartct-settings-export.json');
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output is already encoded by wp_json_encode()
		echo $json;
		exit;
	}

	/**
	 * Import settings from JSON
	 * 
	 * Note: Nonce verification is performed in the parent handle_tools_actions() method
	 * before this method is called, so phpcs warnings are suppressed.
	 */
	private function import_settings(Settings $settings): void
	{
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in handle_tools_actions()

		// Validate file upload exists
		if (! isset($_FILES['import_file']['tmp_name']) || empty($_FILES['import_file']['tmp_name'])) {
			add_settings_error('smartct_tools', 'import_error', __('No file uploaded.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		// Check for upload errors
		if (! isset($_FILES['import_file']['error']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
			add_settings_error('smartct_tools', 'import_error', __('File upload error.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		// Validate file type (must be JSON)
		if (! isset($_FILES['import_file']['name'])) {
			add_settings_error('smartct_tools', 'import_error', __('Invalid file upload.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		$file_type = wp_check_filetype(sanitize_file_name(wp_unslash($_FILES['import_file']['name'])));
		if ($file_type['ext'] !== 'json') {
			add_settings_error('smartct_tools', 'import_error', __('Invalid file type. Only JSON files are allowed.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		// Sanitize and validate the file path
		$tmp_name = sanitize_text_field(wp_unslash($_FILES['import_file']['tmp_name']));

		// Additional security: verify it's a valid uploaded file
		if (! is_uploaded_file($tmp_name)) {
			add_settings_error('smartct_tools', 'import_error', __('Security check failed.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading uploaded file
		$import = file_get_contents($tmp_name);

		if ($import === false) {
			add_settings_error('smartct_tools', 'import_error', __('Failed to read import file.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		$data = json_decode($import, true);

		if (! is_array($data)) {
			add_settings_error('smartct_tools', 'import_error', __('Invalid import file format.', 'smart-cloudflare-turnstile'), 'error');
			return;
		}

		// Update settings
		update_option('smartct_settings', $data);
		add_settings_error('smartct_tools', 'import_success', __('Settings imported successfully.', 'smart-cloudflare-turnstile'), 'updated');

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Reset all settings to defaults
	 */
	private function reset_settings(Settings $settings): void
	{
		delete_option('smartct_settings');
		$settings->add_default_options();
		add_settings_error('smartct_tools', 'reset_success', __('Settings have been reset to defaults.', 'smart-cloudflare-turnstile'), 'updated');
	}
}
