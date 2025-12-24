/* global jQuery, ajaxurl, smartct, smartctSystemInfo */
jQuery(document).ready(function ($) {
	'use strict';

	// ========================================
	// System Info Copy to Clipboard
	// ========================================
	const systemInfoBtn = document.getElementById('twp-copy-system-info');
	if (systemInfoBtn && window.smartctSystemInfo) {
		systemInfoBtn.addEventListener('click', function () {
			const data = window.smartctSystemInfo;
			const info = [
				'Smart Cloudflare Turnstile: v' + data.plugin_version,
				'WordPress: v' + data.wp_version,
				'PHP: v' + data.php_version,
				'WooCommerce: ' + data.wc_version,
				'Memory Limit: ' + data.memory_limit,
			].join('\n');

			function showCopied() {
				const msg = document.getElementById('twp-copy-system-info-msg');
				if (msg) {
					msg.style.display = 'inline';
					setTimeout(function () {
						msg.style.display = 'none';
					}, 1500);
				}
			}

			function fallbackCopy(text) {
				const ta = document.createElement('textarea');
				ta.value = text;
				ta.setAttribute('readonly', '');
				ta.style.position = 'absolute';
				ta.style.left = '-9999px';
				document.body.appendChild(ta);
				ta.select();
				try {
					document.execCommand('copy');
					showCopied();
				} catch (err) {
					console.error('Copy failed', err);
				}
				document.body.removeChild(ta);
			}

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard
					.writeText(info)
					.then(showCopied, function () {
						fallbackCopy(info);
					});
			} else {
				fallbackCopy(info);
			}
		});
	}

	// ========================================
	// FAQ Accordion
	// ========================================
	const faqQuestions = document.querySelectorAll('.faq-question');
	const faqAnswers = document.querySelectorAll('.faq-answer');
	if (faqQuestions.length && faqAnswers.length) {
		faqQuestions.forEach(function (q, idx) {
			// Click handler
			q.addEventListener('click', function () {
				const expanded = q.getAttribute('aria-expanded') === 'true';

				// Collapse all
				faqQuestions.forEach(function (qq, i) {
					qq.setAttribute('aria-expanded', 'false');
					if (faqAnswers[i]) {
						faqAnswers[i].style.display = 'none';
					}
				});

				// Expand this one if it was not already open
				if (!expanded && faqAnswers[idx]) {
					q.setAttribute('aria-expanded', 'true');
					faqAnswers[idx].style.display = 'block';
				}
			});

			// Keyboard handler
			q.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					q.click();
				}
			});

			// Start collapsed
			if (faqAnswers[idx]) {
				faqAnswers[idx].style.display = 'none';
			}
		});
	}

	// ========================================
	// Settings Page Accordion
	// ========================================
	const accordionHeaders = document.querySelectorAll(
		'.smartct-accordion-header'
	);
	if (accordionHeaders.length) {
		accordionHeaders.forEach(function (header) {
			header.addEventListener('click', function () {
				const item = this.closest('.smartct-accordion-item');
				if (!item) return;

				const isOpen = item.classList.contains('open');
				const arrow = item.querySelector(
					'.smartct-accordion-arrow'
				);

				// Close all accordion items
				document
					.querySelectorAll('.smartct-accordion-item')
					.forEach(function (i) {
						i.classList.remove('open');
						const a = i.querySelector(
							'.smartct-accordion-arrow'
						);
						if (a) {
							a.innerHTML = '&#9660;'; // Down arrow
						}
					});

				// Open this one if it wasn't already open
				if (!isOpen) {
					item.classList.add('open');
					if (arrow) {
						arrow.innerHTML = '&#9650;'; // Up arrow
					}
				}
			});
		});
	}

	// ========================================
	// Toggle vertical tabs collapse
	// ========================================
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

	// ========================================
	// Toggle debug mode visibility
	// ========================================
	$('#smartct_debug_mode').on('change', function () {
		const $debugSection = $('.smartct-debug-section');
		if ($(this).is(':checked')) {
			$debugSection.slideDown();
		} else {
			$debugSection.slideUp();
		}
	});

	// ========================================
	// Toggle logged-in users visibility
	// ========================================
	$('#smartct_show_for_logged_in').on('change', function () {
		const $loggedInSection = $('.smartct-logged-in-section');
		if ($(this).is(':checked')) {
			$loggedInSection.slideDown();
		} else {
			$loggedInSection.slideUp();
		}
	});

	// ========================================
	// API Key Verification & Management
	// ========================================
	let turnstileToken = null;
	function getEffectiveSiteKey() {
		// Prefer field value if present, else localized constant-based value
		const $input = $('input[name="smartct_settings[smartct_site_key]"]');
		const v = $input.length ? $input.val() : '';
		if (!v && window.smartct && window.smartct.siteKey) {
			return window.smartct.siteKey;
		}
		return v;
	}
	function getEffectiveSecretKey() {
		const $input = $('input[name="smartct_settings[smartct_secret_key]"]');
		const v = $input.length ? $input.val() : '';
		if (!v && window.smartct && window.smartct.secretKey) {
			return window.smartct.secretKey;
		}
		return v;
	}

	// Callback for Turnstile widget
	window.smartctTokenCallback = function (token) {
		turnstileToken = token;
		$('#smartct-verify-keys').prop('disabled', !token);
	};

	// Render Turnstile widget if preview container exists (original logic)
	if (
		$('#cf-turnstile-preview').length &&
		typeof window.turnstile !== 'undefined'
	) {
		window.turnstile.render('#cf-turnstile-preview', {
			sitekey: getEffectiveSiteKey(),
			theme: 'auto',
			callback: window.smartctTokenCallback,
		});
	}

	// Verify API Keys
	$(document).on('click', '#smartct-verify-keys', function (e) {
		e.preventDefault();

		const siteKey = getEffectiveSiteKey();
		const secretKey = getEffectiveSecretKey();

		if (!siteKey || !secretKey) {
			$('.smartct-verification-status .message')
				.text('Please enter both Site Key and Secret Key.')
				.addClass('error');
			return;
		}

		if (!turnstileToken) {
			$('.smartct-verification-status .message')
				.text('Please complete the Turnstile challenge.')
				.addClass('error');
			return;
		}

		const $button = $(this);
		const $status = $('.smartct-verification-status');
		const $message = $status.find('.message');
		const $statusIndicator = $status.find('.status-indicator');
		const $siteKeyField = $(
			'input[name="smartct_settings[smartct_site_key]"]'
		);
		const $secretKeyField = $(
			'input[name="smartct_settings[smartct_secret_key]"]'
		);
		const $previewBox = $('.smartct-preview-box');

		// Show spinner
		$button.after('<span class="smartct-spinner"></span>');
		$button.prop('disabled', true);
		$message.removeClass('error success').text('');

		// Make AJAX request
		$.ajax({
			url: window.smartct ? window.smartct.ajaxurl : ajaxurl,
			type: 'POST',
			data: {
				action: 'smartct_verify_keys',
				nonce: window.smartct ? window.smartct.nonce : '',
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
				$status.find('.smartct-spinner').remove();
				$button.prop('disabled', false);
				// Reset the widget for another try
				if (window.turnstile && $('.cf-turnstile').length) {
					window.turnstile.reset();
				}
				turnstileToken = null;
				$('#smartct-verify-keys').prop('disabled', true);
			},
		});
	});

	// ========================================
	// Remove API Keys
	// ========================================
	$(document).on('click', '#smartct-remove-keys', function (e) {
		e.preventDefault();

		const $button = $(this);
		const $status = $('.smartct-verification-status');
		const $spinner = $status.find('.spinner');
		const $message = $status.find('.message');
		const $statusIndicator = $status.find('.status-indicator');
		const $siteKeyField = $(
			'input[name="smartct_settings[smartct_site_key]"]'
		);
		const $secretKeyField = $(
			'input[name="smartct_settings[smartct_secret_key]"]'
		);

		// Show spinner
		$spinner.addClass('is-active');
		$button.prop('disabled', true);
		$message.removeClass('error success').text('');

		// Make AJAX request
		$.ajax({
			url: window.smartct ? window.smartct.ajaxurl : ajaxurl,
			type: 'POST',
			data: {
				action: 'smartct_remove_keys',
				nonce: window.smartct ? window.smartct.nonce : '',
			},
			success(response) {
				if (response.success) {
					$statusIndicator
						.removeClass('verified')
						.addClass('unverified')
						.text('Keys Not Verified');
					$message.text(response.data.message).addClass('success');
					// Clear and enable the input fields
					$siteKeyField.val('').prop('readonly', false);
					$secretKeyField.val('').prop('readonly', false);
					// Replace remove button with verify button
					$button.replaceWith(
						'<button type="button" class="button button-secondary" id="smartct-verify-keys">Verify Keys</button>'
					);
				} else {
					$message.text(response.data.message).addClass('error');
				}
			},
			error() {
				$message.text('Key removal failed.').addClass('error');
			},
			complete() {
				$spinner.removeClass('is-active');
				$button.prop('disabled', false);
			},
		});
	});
});
