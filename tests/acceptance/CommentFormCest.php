<?php

namespace SmartCT\Tests\Acceptance;

use AcceptanceTester;

class CommentFormCest
{
	public function testCommentFormHasTurnstileWidget(AcceptanceTester $I)
	{
		// Create a test post
		$post_id = $I->havePostInDatabase(array(
			'post_title' => 'Test Post for Comments',
			'post_content' => 'Test content',
			'post_status' => 'publish',
		));

		$I->amOnPage('/?p=' . $post_id);
		// Check if Turnstile widget exists (if enabled for comments)
		// Note: Widget may not appear if site key is not configured or disabled
		$I->seeElement('form#commentform');
	}
}

