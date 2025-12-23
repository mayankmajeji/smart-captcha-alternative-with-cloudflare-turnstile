<?php

namespace TurnstileWP\Tests\Integration;

use TurnstileWP\Settings;
use Codeception\TestCase\WPTestCase;

class SettingsPersistenceTest extends WPTestCase
{
	protected $settings;

	public function setUp(): void
	{
		parent::setUp();
		$this->settings = new Settings();
	}

	public function testSettingsCanBeRetrieved()
	{
		$settings = $this->settings->get_settings();
		$this->assertIsArray($settings);
	}

	public function testSettingsCanBeUpdated()
	{
		$test_value = 'test_site_key_123';
		update_option('turnstilewp_settings', array('tswp_site_key' => $test_value));

		$retrieved = $this->settings->get_option('tswp_site_key');
		$this->assertEquals($test_value, $retrieved);
	}

	public function testSettingsSanitizationWorks()
	{
		$malicious_input = array(
			'tswp_site_key' => '<script>alert("xss")</script>test_key',
		);

		$sanitized = $this->settings->sanitize_settings($malicious_input);
		$this->assertNotContains('<script>', $sanitized['tswp_site_key']);
	}

	public function testLegacyKeyCompatibility()
	{
		// Test that legacy keys without tswp_ prefix still work
		$value = $this->settings->get_option('site_key');
		$this->assertIsString($value);
	}
}

