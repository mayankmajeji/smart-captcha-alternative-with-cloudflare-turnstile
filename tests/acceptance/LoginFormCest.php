<?php

namespace TurnstileWP\Tests\Acceptance;

use AcceptanceTester;

class LoginFormCest
{
    public function testLoginFormHasTurnstileWidget(AcceptanceTester $I)
    {
        // This test assumes Turnstile is enabled for login forms
        $I->amOnPage('/wp-login.php');
        // Check if Turnstile widget container exists (if enabled)
        // Note: Widget may not appear if site key is not configured
        $I->seeElement('.cf-turnstile');
    }

    public function testLoginFormSubmitsWithTurnstile(AcceptanceTester $I)
    {
        // This test would require actual Turnstile completion
        // For now, just verify the form structure
        $I->amOnPage('/wp-login.php');
        $I->seeElement('form#loginform');
    }
}
