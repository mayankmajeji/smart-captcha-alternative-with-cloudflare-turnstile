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
 * Get client IP address with proper fallback and sanitization
 *
 * This function checks multiple SERVER variables to determine the client's IP address,
 * with proper sanitization and validation. It handles proxy scenarios (HTTP_X_FORWARDED_FOR)
 * and direct connections (REMOTE_ADDR).
 *
 * @since 1.0.0
 * @return string Sanitized client IP address, or empty string if not available
 */
function get_client_ip(): string {
	$ip = '';

	// Check HTTP_CLIENT_IP first (least common but highest priority)
	if ( ! empty($_SERVER['HTTP_CLIENT_IP']) ) {
		$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
	} elseif ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
		// For proxied requests, get the first IP in the chain
		$forwarded = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
		// Handle comma-separated list of IPs
		$ip_list = explode(',', $forwarded);
		$ip = trim($ip_list[0]);
	} elseif ( ! empty($_SERVER['REMOTE_ADDR']) ) {
		$ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
	}

	/**
	 * Filter the detected client IP address
	 *
	 * @since 1.0.0
	 * @param string $ip The detected IP address
	 */
	return apply_filters('smartct_client_ip', $ip);
}
