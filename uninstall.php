<?php

/**
 * Uninstall SmartCT
 *
 * @package SmartCT
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Delete plugin options
delete_option('smartct_settings');
delete_option('smartct_keys_verified');

// Clear any transients we've set
delete_transient('smartct_debug_log');
