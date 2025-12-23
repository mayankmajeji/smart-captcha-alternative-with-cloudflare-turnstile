<?php

namespace TurnstileWP\Tests\Unit;

use TurnstileWP\Verify;
use Codeception\Test\Unit;

class VerifyTest extends Unit
{
	protected $tester;

	protected $verify;

	protected function _before()
	{
		$this->verify = new Verify();
	}

	public function testVerifyTokenReturnsFalseForEmptyToken()
	{
		$result = $this->verify->verify_token('');
		$this->assertFalse($result);
	}

	public function testVerifyTokenMethodExists()
	{
		$this->assertTrue(method_exists($this->verify, 'verify_token'));
	}

	public function testVerifyTokenAcceptsCustomSecretKey()
	{
		// Test that method accepts optional custom secret key parameter
		$result = $this->verify->verify_token('test_token', 'custom_secret');
		// Should return false for invalid token, but method should execute
		$this->assertIsBool($result);
	}
}

