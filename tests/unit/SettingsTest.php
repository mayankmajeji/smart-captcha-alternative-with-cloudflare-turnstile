<?php

namespace SmartCT\Tests\Unit;

use SmartCT\Settings;
use Codeception\Test\Unit;

class SettingsTest extends Unit
{
	protected $tester;

	protected $settings;

	protected function _before()
	{
		$this->settings = new Settings();
	}

	public function testGetSettingsReturnsArray()
	{
		$settings = $this->settings->get_settings();
		$this->assertIsArray($settings);
	}

	public function testGetOptionReturnsDefaultValue()
	{
		$value = $this->settings->get_option('nonexistent_key', 'default_value');
		$this->assertEquals('default_value', $value);
	}

	public function testGetOptionReturnsSiteKeyDefault()
	{
		$value = $this->settings->get_option('site_key', '');
		$this->assertIsString($value);
	}

	public function testGetOptionReturnsSecretKeyDefault()
	{
		$value = $this->settings->get_option('secret_key', '');
		$this->assertIsString($value);
	}

	public function testGetOptionHandlesLegacyKeys()
	{
		// Test that legacy keys without tswp_ prefix work
		$value = $this->settings->get_option('site_key');
		$this->assertIsString($value);
	}

	public function testGetOptionHandlesPrefixedKeys()
	{
		// Test that prefixed keys work
		$value = $this->settings->get_option('tswp_site_key');
		$this->assertIsString($value);
	}

	public function testGetFieldsStructureReturnsArray()
	{
		$structure = $this->settings->get_fields_structure();
		$this->assertIsArray($structure);
	}

	public function testAddDefaultOptionsMethodExists()
	{
		$this->assertTrue(method_exists($this->settings, 'add_default_options'));
	}

	public function testRegisterSettingsMethodExists()
	{
		$this->assertTrue(method_exists($this->settings, 'register_settings'));
	}

	public function testSanitizeSettingsMethodExists()
	{
		$this->assertTrue(method_exists($this->settings, 'sanitize_settings'));
	}

	public function testSanitizeSettingsHandlesTextInput()
	{
		$input = array('tswp_site_key' => '<script>alert("xss")</script>');
		$sanitized = $this->settings->sanitize_settings($input);
		$this->assertNotContains('<script>', $sanitized['tswp_site_key']);
	}

	public function testSanitizeSettingsHandlesCheckboxInput()
	{
		$input = array('tswp_enable_login' => '1');
		$sanitized = $this->settings->sanitize_settings($input);
		$this->assertEquals(1, $sanitized['tswp_enable_login']);
	}
}

