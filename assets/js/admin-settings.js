/* global jQuery, ajaxurl */
jQuery(document).ready(function ($) {
	'use strict';

	// Toggle vertical tabs collapse
	$(document).on(
		'click',
		".twp-collapse-btn[data-twp-toggle='vtabs']",
		function () {
			const $grid = $(this).closest('.twp-2col');
			if ($grid.length) {
				$grid.toggleClass('is-collapsed');
			}
		}
	);

	// Only run on the Turnstile settings tab
	let turnstileToken = null;
	function getEffectiveSiteKey() {
		// Prefer field value if present, else localized constant-based value
		const $input = $('input[name="turnstilewp_settings[tswp_site_key]"]');
		const v = $input.length ? $input.val() : '';
		if ( ! v && window.turnstilewp && window.turnstilewp.siteKey) {
			return window.turnstilewp.siteKey;
		}
		return v;
	}
	function getEffectiveSecretKey() {
		const $input = $('input[name="turnstilewp_settings[tswp_secret_key]"]');
		const v = $input.length ? $input.val() : '';
		if ( ! v && window.turnstilewp && window.turnstilewp.secretKey) {
			return window.turnstilewp.secretKey;
		}
		return v;
	}

	// Callback for Turnstile widget
	window.turnstilewpTokenCallback = function (token) {
		turnstileToken = token;
		$('#turnstilewp-verify-keys').prop('disabled', ! token);
	};

	// Render Turnstile widget if preview container exists (original logic)
	if (
		$('#cf-turnstile-preview').length &&
		typeof window.turnstile !== 'undefined'
	) {
		window.turnstile.render('#cf-turnstile-preview', {
			sitekey: getEffectiveSiteKey(),
			theme: 'auto',
			callback: window.turnstilewpTokenCallback,
		});
	}

	// Verify API Keys
	$(document).on('click', '#turnstilewp-verify-keys', function (e) {
		e.preventDefault();

		const siteKey = getEffectiveSiteKey();
		const secretKey = getEffectiveSecretKey();

		if ( ! siteKey || ! secretKey) {
			$('.turnstilewp-verification-status .message')
				.text('Please enter both Site Key and Secret Key.')
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
		const $siteKeyField = $(
			'input[name="turnstilewp_settings[tswp_site_key]"]'
		);
		const $secretKeyField = $(
			'input[name="turnstilewp_settings[tswp_secret_key]"]'
		);
		const $previewBox = $('.turnstilewp-preview-box');

		// Show spinner
		$button.after('<span class="turnstilewp-spinner"></span>');
		$button.prop('disabled', true);
		$message.removeClass('error success').text('');

		// Make AJAX request
		$.ajax({
			url: window.turnstilewp ? window.turnstilewp.ajaxurl : ajaxurl,
			type: 'POST',
			data: {
				action: 'turnstilewp_verify_keys',
				nonce: window.turnstilewp ? window.turnstilewp.nonce : '',
				site_key: siteKey,
				secret_key: secretKey,
				response: turnstileToken,
			},
			success(response) {
				if (response.success) {
					// Update status indicator
					$statusIndicator
						.removeClass('unverified')
						.addClass('verified')
						.text('Keys Verified');

					// Update message
					$message
						.removeClass('error')
						.addClass('success')
						.html(
							'<div class="twp-status-indicator-box verified">' +
								'<span class="twp-status-indicator" style="color:#46b450;font-weight:bold;"><span class="dashicons dashicons-yes-alt"></span>' +
								'Success! Turnstile is working correctly with your API keys.' +
								'</span></div>'
						);

					// Update preview box
					$previewBox.html(
						'<div class="twp-status-indicator-box verified">' +
							'<span class="twp-status-indicator" style="color:#46b450;font-weight:bold;"><span class="dashicons dashicons-yes-alt"></span>' +
							'Success! Turnstile is working correctly with your API keys.' +
							'</span></div>'
					);

					// Make fields readonly
					$siteKeyField.prop('readonly', true);
					$secretKeyField.prop('readonly', true);
				} else {
					$statusIndicator
						.removeClass('verified')
						.addClass('unverified')
						.text('Keys Not Verified');
					$message.text(response.data.message).addClass('error');
				}
			},
			error() {
				$statusIndicator
					.removeClass('verified')
					.addClass('unverified')
					.text('Keys Not Verified');
				$message
					.text('Verification failed. Please try again.')
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
				$('#turnstilewp-verify-keys').prop('disabled', true);
			},
		});
	});
});
