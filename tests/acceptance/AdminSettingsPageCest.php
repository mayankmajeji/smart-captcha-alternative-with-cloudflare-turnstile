<?php

namespace SmartCT\Tests\Acceptance;

use AcceptanceTester;

class AdminSettingsPageCest
{
	public function testAdminCanAccessSettingsPage(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnAdminPage('admin.php?page=turnstilewp-settings');
		$I->see('SmartCT');
	}

	public function testSettingsPageHasSiteKeyField(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnAdminPage('admin.php?page=turnstilewp-settings');
		$I->seeElement('input[name*="site_key"]');
	}

	public function testSettingsPageHasSecretKeyField(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnAdminPage('admin.php?page=turnstilewp-settings');
		$I->seeElement('input[name*="secret_key"]');
	}

	public function testIntegrationsPageExists(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnAdminPage('admin.php?page=turnstilewp-integrations');
		$I->see('Integrations');
	}

	public function testToolsPageExists(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnAdminPage('admin.php?page=turnstilewp-tools');
		$I->see('Tools');
	}
}

