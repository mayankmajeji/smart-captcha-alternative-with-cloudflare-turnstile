<?php

namespace SmartCT\Tests\Integration;

use SmartCT\Settings;
use SmartCT\Turnstile;
use Codeception\TestCase\WPTestCase;

class FormIntegrationTest extends WPTestCase
{
	protected $settings;
	protected $turnstile;

	public function setUp(): void
	{
		parent::setUp();
		$this->settings = new Settings();
		$this->turnstile = new Turnstile();
	}

	public function testTurnstileScriptEnqueueActionExists()
	{
		$this->assertTrue(has_action('wp_enqueue_scripts'));
		$this->assertTrue(has_action('admin_enqueue_scripts'));
	}

	public function testSettingsFilterExists()
	{
		$this->assertTrue(has_filter('turnstilewp_settings'));
	}

	public function testWidgetDisableFilterExists()
	{
		$this->assertTrue(has_filter('turnstilewp_widget_disable'));
	}

	public function testBeforeFieldHookExists()
	{
		$this->assertTrue(has_action('turnstilewp_before_field'));
	}

	public function testAfterFieldHookExists()
	{
		$this->assertTrue(has_action('turnstilewp_after_field'));
	}
}

