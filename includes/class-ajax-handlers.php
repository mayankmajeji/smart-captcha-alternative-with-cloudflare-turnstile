<?php

/**
 * AJAX handlers for SmartCT
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ajax_Handlers
 */
class Ajax_Handlers {

	/**
	 * Initialize AJAX handlers
	 */
	public function __construct() {
		add_action('wp_ajax_smartct_export_settings', array( $this, 'export_settings' ));
	}

	/**
	 * Export settings as JSON
	 */
	public function export_settings(): void {
		// Check permissions
		if ( ! current_user_can('manage_options') ) {
			wp_die('Unauthorized', 403);
		}

		// Verify nonce
		if ( ! isset($_GET['_wpnonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'smartct_tools_export') ) {
			wp_die('Invalid nonce', 403);
		}

		// Get settings and only export new, prefixed keys
		$settings = new Settings();
		$all = $settings->get_settings();
		$data = array();
		foreach ( $all as $key => $val ) {
			if ( strpos( (string) $key, 'smartct_') === 0 ) {
				$data[ $key ] = $val;
			}
		}

		// Prepare JSON response
		$json = wp_json_encode($data, JSON_PRETTY_PRINT);

		// Set headers for download
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename=smartct-settings-export.json');
		header('Content-Length: ' . strlen($json));

		// Output JSON and exit
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output is already encoded by wp_json_encode()
		echo $json;
		exit;
	}
}
