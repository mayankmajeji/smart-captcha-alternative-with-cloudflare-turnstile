/* global jQuery, turnstilewp */
/**
 * Admin JavaScript for TurnstileWP
 */
jQuery(document).ready(function ($) {
	'use strict';

	// Toggle debug mode visibility
	$('#turnstilewp_debug_mode').on('change', function () {
		const $debugSection = $('.turnstilewp-debug-section');
		if ($(this).is(':checked')) {
			$debugSection.slideDown();
		} else {
			$debugSection.slideUp();
		}
	});

	// Toggle logged-in users visibility
	$('#turnstilewp_show_for_logged_in').on('change', function () {
		const $loggedInSection = $('.turnstilewp-logged-in-section');
		if ($(this).is(':checked')) {
			$loggedInSection.slideDown();
		} else {
			$loggedInSection.slideUp();
		}
	});

	// Verify API Keys (delegated event binding)
	$(document).on('click', '#turnstilewp-verify-keys', function (e) {
		e.preventDefault();

		const $siteKeyField = $('input[name="turnstilewp_settings[site_key]"]');
		const $secretKeyField = $(
			'input[name="turnstilewp_settings[secret_key]"]'
		);
		const siteKey = $siteKeyField.val();
		const secretKey = $secretKeyField.val();

		if ( ! siteKey || ! secretKey) {
			$('.turnstilewp-verification-status .message')
				.text(turnstilewp.i18n.bothKeysRequired)
				.addClass('error');
			return;
		}

		const $button = $(this);
		const $status = $('.turnstilewp-verification-status');
		// Remove any existing spinner
		$status.find('.turnstilewp-spinner').remove();
		const $spinner = $("<span class='turnstilewp-spinner'></span>");
		const $message = $status.find('.message');
		const $statusIndicator = $status.find('.status-indicator');

		// Show spinner
		$button.after($spinner);
		$button.prop('disabled', true);
		$message.removeClass('error success').text('');

		// Make AJAX request
		$.ajax({
			url: turnstilewp.ajaxurl,
			type: 'POST',
			data: {
				action: 'turnstilewp_verify_keys',
				nonce: turnstilewp.nonce,
				site_key: siteKey,
				secret_key: secretKey,
			},
			success(response) {
				if (response.success) {
					$status.replaceWith(
						'<div class="turnstilewp-verification-status">' +
							'<span class="status-indicator verified">' +
							turnstilewp.i18n.keysVerified +
							'</span>' +
							'<span class="message success" style="display: flex; align-items: center; font-size: 1.3em; color: #218838; font-weight: bold;">' +
							'<span style="font-size: 2em; margin-right: 10px;">&#10004;</span>' +
							'Success! Turnstile is working correctly with your API keys.' +
							'</span>' +
							'</div>'
					);
					$siteKeyField.prop('readonly', true);
					$secretKeyField.prop('readonly', true);
				} else {
					$statusIndicator
						.removeClass('verified')
						.addClass('unverified')
						.text(turnstilewp.i18n.keysNotVerified);
					$message.text(response.data.message).addClass('error');
				}
			},
			error() {
				$statusIndicator
					.removeClass('verified')
					.addClass('unverified')
					.text(turnstilewp.i18n.keysNotVerified);
				$message
					.text(turnstilewp.i18n.verificationFailed)
					.addClass('error');
			},
			complete() {
				// Always remove spinner
				$status.find('.turnstilewp-spinner').remove();
				$button.prop('disabled', false);
			},
		});
	});

	// Remove API Keys
	$(document).on('click', '#turnstilewp-remove-keys', function (e) {
		e.preventDefault();

		const $button = $(this);
		const $status = $('.turnstilewp-verification-status');
		const $spinner = $status.find('.spinner');
		const $message = $status.find('.message');
		const $statusIndicator = $status.find('.status-indicator');
		const $siteKeyField = $('input[name="turnstilewp_settings[site_key]"]');
		const $secretKeyField = $(
			'input[name="turnstilewp_settings[secret_key]"]'
		);

		// Show spinner
		$spinner.addClass('is-active');
		$button.prop('disabled', true);
		$message.removeClass('error success').text('');

		// Make AJAX request
		$.ajax({
			url: turnstilewp.ajaxurl,
			type: 'POST',
			data: {
				action: 'turnstilewp_remove_keys',
				nonce: turnstilewp.nonce,
			},
			success(response) {
				if (response.success) {
					$statusIndicator
						.removeClass('verified')
						.addClass('unverified')
						.text(turnstilewp.i18n.keysNotVerified);
					$message.text(response.data.message).addClass('success');
					// Clear and enable the input fields
					$siteKeyField.val('').prop('readonly', false);
					$secretKeyField.val('').prop('readonly', false);
					// Replace remove button with verify button
					$button.replaceWith(
						'<button type="button" class="button button-secondary" id="turnstilewp-verify-keys">' +
							turnstilewp.i18n.verifyKeys +
							'</button>'
					);
				} else {
					$message.text(response.data.message).addClass('error');
				}
			},
			error() {
				$message.text(turnstilewp.i18n.removalFailed).addClass('error');
			},
			complete() {
				$spinner.removeClass('is-active');
				$button.prop('disabled', false);
			},
		});
	});

	// --- Turnstile Admin Verification ---
	let turnstileToken = null;

	// Callback for Turnstile widget
	window.turnstilewpTokenCallback = function (token) {
		turnstileToken = token;
		$('#turnstilewp-test-response').prop('disabled', ! token);
	};

	// Force re-render the widget after callback is registered
	$(function () {
		if (window.turnstile && $('.cf-turnstile-widget').length) {
			$('.cf-turnstile-widget').each(function () {
				// Only render if no widget is present
				if ($(this).find('iframe').length === 0) {
					window.turnstile.render(this, {
						sitekey: $(this).data('sitekey'),
						theme: $(this).data('theme'),
						callback: window.turnstilewpTokenCallback,
					});
				}
			});
		}
	});

	// Disable Test Response button initially
	$(document).on('ready', function () {
		$('#turnstilewp-test-response').prop('disabled', true);
	});

	// When the Test Response button is clicked
	$(document).on('click', '#turnstilewp-test-response', function (e) {
		e.preventDefault();
		const $siteKeyField = $('input[name="turnstilewp_settings[site_key]"]');
		const $secretKeyField = $(
			'input[name="turnstilewp_settings[secret_key]"]'
		);
		const siteKey = $siteKeyField.val();
		const secretKey = $secretKeyField.val();

		if ( ! siteKey || ! secretKey) {
			$('.turnstilewp-verification-status .message')
				.text(turnstilewp.i18n.bothKeysRequired)
				.addClass('error');
			return;
		}
		if ( ! turnstileToken) {
			$('.turnstilewp-verification-status .message')
				.text('Please complete the Turnstile challenge.')
				.addClass('error');
			return;
		}

		const $button = $(this);
		const $status = $('.turnstilewp-verification-status');
		const $message = $status.find('.message');
		const $statusIndicator = $status.find('.status-indicator');

		// Show spinner
		$button.after('<span class="turnstilewp-spinner"></span>');
		$button.prop('disabled', true);
		$message.removeClass('error success').text('');

		// Make AJAX request
		$.ajax({
			url: turnstilewp.ajaxurl,
			type: 'POST',
			data: {
				action: 'turnstilewp_verify_keys',
				nonce: turnstilewp.nonce,
				site_key: siteKey,
				secret_key: secretKey,
				response: turnstileToken,
			},
			success(response) {
				if (response.success) {
					$status.replaceWith(
						'<div class="turnstilewp-verification-status">' +
							'<span class="status-indicator verified">' +
							turnstilewp.i18n.keysVerified +
							'</span>' +
							'<span class="message success" style="display: flex; align-items: center; font-size: 1.3em; color: #218838; font-weight: bold;">' +
							'<span style="font-size: 2em; margin-right: 10px;">&#10004;</span>' +
							'Success! Turnstile is working correctly with your API keys.' +
							'</span>' +
							'</div>'
					);
					$siteKeyField.prop('readonly', true);
					$secretKeyField.prop('readonly', true);
				} else {
					$statusIndicator
						.removeClass('verified')
						.addClass('unverified')
						.text(turnstilewp.i18n.keysNotVerified);
					$message.text(response.data.message).addClass('error');
				}
			},
			error() {
				$statusIndicator
					.removeClass('verified')
					.addClass('unverified')
					.text(turnstilewp.i18n.keysNotVerified);
				$message
					.text(turnstilewp.i18n.verificationFailed)
					.addClass('error');
			},
			complete() {
				$status.find('.turnstilewp-spinner').remove();
				$button.prop('disabled', false);
				// Reset the widget for another try
				if (window.turnstile && $('.cf-turnstile').length) {
					window.turnstile.reset();
				}
				turnstileToken = null;
				$('#turnstilewp-test-response').prop('disabled', true);
			},
		});
	});
});
