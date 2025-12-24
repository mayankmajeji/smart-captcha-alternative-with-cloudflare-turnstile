/* global jQuery */
(function ($) {
	'use strict';

	// Utility to force-enable buttons
	function enableButton($btn) {
		$btn.prop('disabled', false)
			.removeAttr('disabled')
			.removeAttr('aria-disabled')
			.removeClass(
				'disabled is-disabled button-disabled woocommerce-Button--disabled'
			)
			.css('pointer-events', 'auto')
			.css('opacity', '1');
	}

	// Define global Turnstile callbacks up-front (must exist before widget fires)
	window.smartctWooCheckoutCallback = function () {
		enableButton($('form.checkout').find('#place_order'));
	};
	window.smartctWooLoginCallback = function () {
		enableButton(
			$('form.woocommerce-form-login').find(
				'button[type="submit"], input[type="submit"], .woocommerce-Button[name="login"]'
			)
		);
	};
	window.smartctWooRegisterCallback = function () {
		enableButton(
			$('form.woocommerce-form-register').find(
				'button[type="submit"], input[type="submit"], .woocommerce-Button[name="register"]'
			)
		);
	};
	window.smartctWooResetCallback = function () {
		enableButton(
			$('form.woocommerce-ResetPassword').find(
				'button[type="submit"], input[type="submit"]'
			)
		);
	};
	window.smartctWooPayOrderCallback = function () {
		enableButton($('#order_review').find('#place_order'));
	};

	// Initialize Turnstile for WooCommerce forms
	function initWooTurnstile() {
		// Checkout form
		if ($('form.checkout').length) {
			initCheckoutForm();
		}

		// Login form
		if ($('form.woocommerce-form-login').length) {
			initLoginForm();
		}

		// Registration form
		if ($('form.woocommerce-form-register').length) {
			initRegisterForm();
		}

		// Password reset form
		if ($('form.woocommerce-ResetPassword').length) {
			initResetForm();
		}

		// Pay order form
		if ($('form#order_review').length) {
			initPayOrderForm();
		}
	}

	// Initialize checkout form
	function initCheckoutForm() {
		const $form = $('form.checkout');
		const $submitButton = $form.find('#place_order');
		const hasWidget = $form.find('.cf-turnstile').length > 0;
		const $token = $form.find('input[name="cf-turnstile-response"]');

		// Disable submit button until Turnstile is completed
		if (typeof turnstile !== 'undefined' && hasWidget) {
			$submitButton.prop('disabled', true);

			// Enable submit button when Turnstile is completed
			// (callback already defined globally)
			if ($token.length) {
				if ($token.val()) {
					enableButton($submitButton);
				}
				$token.on('change', function () {
					if ($(this).val()) {
						enableButton($submitButton);
					}
				});
			}
		} else if (typeof turnstile !== 'undefined' && !hasWidget) {
			// Try to inject for Checkout Block area if present
			const $actions = $(
				'.wc-block-checkout__actions, .wc-block-components-checkout-place-order-button'
			).first();
			if (
				$actions.length &&
				window.smartctWoo &&
				window.smartctWoo.siteKey
			) {
				// Avoid duplicate inject
				if (!$actions.prev('.smartct-injected').length) {
					const mount = $(
						'<div class="smartct-injected" style="margin:10px 0;"></div>'
					);
					$actions.before(mount);
					try {
						window.turnstile.render(mount.get(0), {
							sitekey: window.smartctWoo.siteKey,
							callback: window.smartctWooCheckoutCallback,
						});
						$submitButton.prop('disabled', true);
					} catch (e) {}
				}
			}
			// If still no widget/mount, don't interfere
			if (!$('.cf-turnstile').length) {
				enableButton($submitButton);
			}
		} else {
			// No widget present; do not interfere with WooCommerce behavior
			enableButton($submitButton);
		}
	}

	// Initialize login form
	function initLoginForm() {
		const $form = $('form.woocommerce-form-login');
		const $submitButton = $form.find(
			'button[type="submit"], input[type="submit"], .woocommerce-Button[name="login"]'
		);
		const hasWidget = $form.find('.cf-turnstile').length > 0;
		const $token = $form.find('input[name="cf-turnstile-response"]');

		if (typeof turnstile !== 'undefined' && hasWidget) {
			$submitButton.prop('disabled', true);

			// (callback already defined globally)
			if ($token.length) {
				if ($token.val()) {
					enableButton($submitButton);
				}
				$token.on('change', function () {
					if ($(this).val()) {
						enableButton($submitButton);
					}
				});
			}
		} else {
			enableButton($submitButton);
		}
	}

	// Initialize registration form
	function initRegisterForm() {
		const $form = $('form.woocommerce-form-register');
		const $submitButton = $form.find(
			'button[type="submit"], input[type="submit"], .woocommerce-Button[name="register"]'
		);
		const hasWidget = $form.find('.cf-turnstile').length > 0;
		const $token = $form.find('input[name="cf-turnstile-response"]');

		if (typeof turnstile !== 'undefined' && hasWidget) {
			$submitButton.prop('disabled', true);

			// (callback already defined globally)
			if ($token.length) {
				if ($token.val()) {
					enableButton($submitButton);
				}
				$token.on('change', function () {
					if ($(this).val()) {
						enableButton($submitButton);
					}
				});
			}
		} else {
			enableButton($submitButton);
		}
	}

	// Initialize password reset form
	function initResetForm() {
		const $form = $('form.woocommerce-ResetPassword');
		const $submitButton = $form.find(
			'button[type="submit"], input[type="submit"]'
		);
		const hasWidget = $form.find('.cf-turnstile').length > 0;
		const $token = $form.find('input[name="cf-turnstile-response"]');

		if (typeof turnstile !== 'undefined' && hasWidget) {
			$submitButton.prop('disabled', true);

			// (callback already defined globally)
			if ($token.length) {
				if ($token.val()) {
					enableButton($submitButton);
				}
				$token.on('change', function () {
					if ($(this).val()) {
						enableButton($submitButton);
					}
				});
			}
		} else {
			enableButton($submitButton);
		}
	}

	// Initialize pay order form
	function initPayOrderForm() {
		const $form = $('#order_review');
		const $submitButton = $form.find('#place_order');
		const hasWidget = $form.find('.cf-turnstile').length > 0;
		const $token = $form.find('input[name="cf-turnstile-response"]');

		if (typeof turnstile !== 'undefined' && hasWidget) {
			$submitButton.prop('disabled', true);

			// (callback already defined globally)
			if ($token.length) {
				if ($token.val()) {
					enableButton($submitButton);
				}
				$token.on('change', function () {
					if ($(this).val()) {
						enableButton($submitButton);
					}
				});
			}
		} else {
			enableButton($submitButton);
		}
	}

	// Initialize on document ready
	$(document).ready(function () {
		initWooTurnstile();
	});

	// Re-initialize on WooCommerce AJAX events
	$(document.body).on('updated_checkout', function () {
		initWooTurnstile();
	});

	// ========================================
	// WooCommerce Turnstile iFrame Styling
	// Ensures iframes are properly styled on WooCommerce pages
	// ========================================
	const wooIframeSelectors = [
		'.woocommerce .cf-turnstile iframe',
		'.woocommerce-page .cf-turnstile iframe',
		'.woocommerce form .cf-turnstile iframe',
		'.woocommerce form.login .cf-turnstile iframe',
		'.woocommerce form.register .cf-turnstile iframe',
		'.woocommerce form.checkout .cf-turnstile iframe',
		'.woocommerce form.lost_reset_password .cf-turnstile iframe',
		'.woocommerce .woocommerce-form-login .cf-turnstile iframe',
		'.woocommerce .woocommerce-form-register .cf-turnstile iframe',
		'.woocommerce .woocommerce-checkout .cf-turnstile iframe',
		'.woocommerce-page form .cf-turnstile iframe',
		'.woocommerce-page form.login .cf-turnstile iframe',
		'.woocommerce-page form.register .cf-turnstile iframe',
	];

	function styleWooTurnstileIframes() {
		const iframes = document.querySelectorAll(
			wooIframeSelectors.join(', ')
		);
		iframes.forEach(function (iframe) {
			iframe.style.width = '100%';
			iframe.style.maxWidth = '100%';
		});
	}

	// Apply styles on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', styleWooTurnstileIframes);
	} else {
		styleWooTurnstileIframes();
	}

	// Apply styles repeatedly in case of late loading (e.g., AJAX loaded forms)
	setInterval(styleWooTurnstileIframes, 300);

	// Observe DOM for dynamically added iframes
	if (window.MutationObserver && document.body) {
		const observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				if (mutation.addedNodes.length) {
					styleWooTurnstileIframes();
				}
			});
		});
		observer.observe(document.body, { childList: true, subtree: true });
	}
})(jQuery);
