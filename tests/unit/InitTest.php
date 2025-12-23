<?php

namespace TurnstileWP\Tests\Unit;

use TurnstileWP\Init;
use Codeception\Test\Unit;

class InitTest extends Unit
{
	protected $tester;

	public function testGetInstanceReturnsSingleton()
	{
		$instance1 = Init::get_instance();
		$instance2 = Init::get_instance();

		$this->assertSame($instance1, $instance2);
	}

	public function testInitMethodExists()
	{
		$instance = Init::get_instance();
		$this->assertTrue(method_exists($instance, 'init'));
	}

	public function testActivateMethodExists()
	{
		$instance = Init::get_instance();
		$this->assertTrue(method_exists($instance, 'activate'));
	}

	public function testDeactivateMethodExists()
	{
		$instance = Init::get_instance();
		$this->assertTrue(method_exists($instance, 'deactivate'));
	}
}

