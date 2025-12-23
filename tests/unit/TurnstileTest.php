<?php

namespace TurnstileWP\Tests\Unit;

use TurnstileWP\Turnstile;
use Codeception\Test\Unit;

class TurnstileTest extends Unit
{
	protected $tester;

	protected $turnstile;

	protected function _before()
	{
		$this->turnstile = new Turnstile();
	}

	public function testRenderMethodExists()
	{
		$this->assertTrue(method_exists($this->turnstile, 'render'));
	}

	public function testRenderDynamicMethodExists()
	{
		$this->assertTrue(method_exists($this->turnstile, 'render_dynamic'));
	}

	public function testVerifyMethodExists()
	{
		$this->assertTrue(method_exists($this->turnstile, 'verify'));
	}

	public function testEnqueueScriptMethodExists()
	{
		$this->assertTrue(method_exists($this->turnstile, 'enqueue_script'));
	}

	public function testRenderDynamicAcceptsArgs()
	{
		$args = array(
			'form_name' => 'test_form',
			'unique_id' => 'test_123',
		);
		// Method should not throw exception
		ob_start();
		$this->turnstile->render_dynamic($args);
		$output = ob_get_clean();
		$this->assertIsString($output);
	}

	public function testVerifyReturnsBool()
	{
		$result = $this->turnstile->verify(null);
		$this->assertIsBool($result);
	}
}

