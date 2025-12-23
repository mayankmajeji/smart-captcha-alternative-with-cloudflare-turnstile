<?php

namespace TurnstileWP\Tests\Integration;

use TurnstileWP\Init;
use TurnstileWP\Settings;
use Codeception\TestCase\WPTestCase;

class PluginActivationTest extends WPTestCase
{
	public function testPluginActivatesSuccessfully()
	{
		// Verify plugin is active
		$this->assertTrue(is_plugin_active('turnstilewp/turnstilewp.php'));
	}

	public function testDefaultSettingsAreCreated()
	{
		$settings = new Settings();
		$all_settings = $settings->get_settings();

		// Check that default settings exist
		$this->assertArrayHasKey('tswp_site_key', $all_settings);
		$this->assertArrayHasKey('tswp_secret_key', $all_settings);
		$this->assertArrayHasKey('tswp_theme', $all_settings);
	}

	public function testSettingsOptionExists()
	{
		$option = get_option('turnstilewp_settings');
		$this->assertIsArray($option);
	}

	public function testInitInstanceIsSingleton()
	{
		$instance1 = Init::get_instance();
		$instance2 = Init::get_instance();
		$this->assertSame($instance1, $instance2);
	}
}

