<?php

namespace SmartCT\Tests\Unit;

use SmartCT\Loader;
use Codeception\Test\Unit;

class LoaderTest extends Unit
{
	protected $tester;

	public function testRegisterMethodExists()
	{
		$this->assertTrue(method_exists(Loader::class, 'register'));
	}

	public function testAutoloadMethodExists()
	{
		$this->assertTrue(method_exists(Loader::class, 'autoload'));
	}

	public function testLoadIntegrationsMethodExists()
	{
		$this->assertTrue(method_exists(Loader::class, 'load_integrations'));
	}

	public function testAutoloadHandlesSmartCTNamespace()
	{
		// Test that autoloader handles SmartCT namespace
		$class = 'SmartCT\\Settings';
		$this->assertTrue(class_exists($class));
	}
}

