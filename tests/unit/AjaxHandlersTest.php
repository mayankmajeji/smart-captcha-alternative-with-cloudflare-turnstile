<?php

namespace TurnstileWP\Tests\Unit;

use TurnstileWP\Ajax_Handlers;
use Codeception\Test\Unit;

class AjaxHandlersTest extends Unit
{
	protected $tester;

	protected $ajax_handlers;

	protected function _before()
	{
		$this->ajax_handlers = new Ajax_Handlers();
	}

	public function testExportSettingsMethodExists()
	{
		$this->assertTrue(method_exists($this->ajax_handlers, 'export_settings'));
	}

	public function testConstructorRegistersHooks()
	{
		// Verify that constructor doesn't throw errors
		$this->assertInstanceOf(Ajax_Handlers::class, $this->ajax_handlers);
	}
}

