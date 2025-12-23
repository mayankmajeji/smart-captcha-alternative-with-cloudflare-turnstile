<?php

namespace TurnstileWP\Tests\Unit;

use TurnstileWP\Loader;
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

	public function testAutoloadHandlesTurnstileWPNamespace()
	{
		// Test that autoloader handles TurnstileWP namespace
		$class = 'TurnstileWP\\Settings';
		$this->assertTrue(class_exists($class));
	}
}

