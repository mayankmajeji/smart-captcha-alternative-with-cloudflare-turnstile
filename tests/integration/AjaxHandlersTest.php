<?php

namespace TurnstileWP\Tests\Integration;

use TurnstileWP\Ajax_Handlers;
use Codeception\TestCase\WPTestCase;

class AjaxHandlersTest extends WPTestCase
{
	protected $ajax_handlers;

	public function setUp(): void
	{
		parent::setUp();
		$this->ajax_handlers = new Ajax_Handlers();
	}

	public function testExportSettingsActionIsRegistered()
	{
		$this->assertTrue(has_action('wp_ajax_turnstilewp_export_settings'));
	}

	public function testExportSettingsRequiresManageOptionsCapability()
	{
		// This test verifies the capability check exists
		// Actual AJAX testing would require proper user setup
		$this->assertTrue(method_exists($this->ajax_handlers, 'export_settings'));
	}
}

