<?php

/**
 * Autoloader class for SmartCT
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT;

/**
 * Class Loader
 */
class Loader {

	/**
	 * Register the autoloader
	 */
	public static function register(): void {
		spl_autoload_register(array( self::class, 'autoload' ));

		// Load all integration files automatically
		self::load_integrations();

		// Ensure core Turnstile settings/filters are loaded (registers settings fields)
		$core_turnstile = SMARTCT_PLUGIN_DIR . 'includes/class-turnstile.php';
		if ( file_exists($core_turnstile) ) {
			require_once $core_turnstile;
		}
	}

	/**
	 * Autoload classes
	 *
	 * @param string $class The class name to load.
	 */
	public static function autoload( string $class ): void {
		// Only handle classes in our namespace
		if ( strpos($class, 'SmartCT\\') !== 0 ) {
			return;
		}

		// Remove namespace from class name
		$class = str_replace('SmartCT\\', '', $class);

		// Convert class name to file path
		$file = SMARTCT_PLUGIN_DIR . 'includes/class-' .
			strtolower(str_replace('_', '-', $class)) . '.php';

		// Load the file if it exists
		if ( file_exists($file) ) {
			require_once $file;
			return;
		}

		// Also check integrations directory (now under includes/)
		$file = SMARTCT_PLUGIN_DIR . 'includes/integrations/class-' .
			strtolower(str_replace('_', '-', $class)) . '.php';

		if ( file_exists($file) ) {
			require_once $file;
			return;
		}

		// Check categorized integration subdirectories
		$categories = array( 'core', 'ecommerce', 'forms', 'others', 'community', 'membership', 'newsletters' );
		foreach ( $categories as $category ) {
			$file = SMARTCT_PLUGIN_DIR . 'includes/integrations/' . $category . '/class-' .
				strtolower(str_replace('_', '-', $class)) . '.php';
			if ( file_exists($file) ) {
				require_once $file;
				return;
			}
		}
	}

	/**
	 * Load all integration files
	 */
	public static function load_integrations(): void {
		$integrations_dir = SMARTCT_PLUGIN_DIR . 'includes/integrations/';
		$categories = array( '.', 'ecommerce', 'forms', 'others', 'community', 'membership', 'newsletters' );

		if ( ! is_dir($integrations_dir) ) {
			return;
		}

		$files = array();
		foreach ( $categories as $category ) {
			$dir = rtrim($integrations_dir . ( $category === '.' ? '' : $category . '/' ), '/');
			if ( is_dir($dir) ) {
				$found = glob($dir . '/class-*.php') ?: array();
				if ( $found ) {
					$files = array_merge($files, $found);
				}
			}
		}

		if ( $files === false ) {
			return;
		}

		foreach ( $files as $file ) {
			require_once $file;
		}
	}
}

// Register the autoloader
Loader::register();
