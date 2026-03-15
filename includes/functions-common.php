<?php

/**
 * Common functions
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if current page is login page
 *
 * @return bool
 */
function is_login(): bool {
	return in_array($GLOBALS['pagenow'], array( 'wp-login.php' ), true);
}

/**
 * Check if current page is registration page
 *
 * @return bool
 */
function is_registration_page(): bool {
	if ( ! in_array($GLOBALS['pagenow'], array( 'wp-login.php' ), true) ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page context only, not processing form data
	$action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
	return $action === 'register';
}

/**
 * Check if current page is lost password page
 *
 * @return bool
 */
function is_lost_password_page(): bool {
	if ( ! in_array($GLOBALS['pagenow'], array( 'wp-login.php' ), true) ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page context only, not processing form data
	$action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
	return $action === 'lostpassword';
}

/**
 * Check if current page has comment form
 *
 * @return bool
 */
function is_comment_form_page(): bool {
	return is_singular() && comments_open();
}

/**
 * Get client IP address
 *
 * Uses REMOTE_ADDR which is set by the web server and cannot be spoofed by
 * the client. HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR are deliberately ignored
 * as they are user-controlled headers and can be trivially forged.
 *
 * Use the `smartct_client_ip` filter to override this behaviour on sites
 * running behind a trusted reverse proxy.
 *
 * @since 1.0.0
 * @return string Sanitized client IP address, or empty string if not available
 */
function get_client_ip(): string {
	$ip = ! empty($_SERVER['REMOTE_ADDR'])
		? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
		: '';

	/**
	 * Filter the detected client IP address.
	 *
	 * Sites behind a trusted reverse proxy can use this filter to read the
	 * real client IP from a verified header (e.g. HTTP_X_FORWARDED_FOR).
	 *
	 * @since 1.0.0
	 * @param string $ip The IP address from REMOTE_ADDR.
	 */
	return apply_filters('smartct_client_ip', $ip);
}
