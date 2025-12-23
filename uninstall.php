<?php

/**
 * Uninstall TurnstileWP
 *
 * @package TurnstileWP
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	exit;
}

// Delete plugin options
delete_option('turnstilewp_settings');

// Clear any transients we've set
delete_transient('turnstilewp_debug_log');
