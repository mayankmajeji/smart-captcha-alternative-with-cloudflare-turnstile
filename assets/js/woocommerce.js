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
	window.turnstileWooCheckoutCallback = function () {
		enableButton($('form.checkout').find('#place_order'));
	};
	window.turnstileWooLoginCallback = function () {
		enableButton(
			$('form.woocommerce-form-login').find(
				'button[type="submit"], input[type="submit"], .woocommerce-Button[name="login"]'
			)
		);
	};
	window.turnstileWooRegisterCallback = function () {
		enableButton(
			$('form.woocommerce-form-register').find(
				'button[type="submit"], input[type="submit"], .woocommerce-Button[name="register"]'
			)
		);
	};
	window.turnstileWooResetCallback = function () {
		enableButton(
			$('form.woocommerce-ResetPassword').find(
				'button[type="submit"], input[type="submit"]'
			)
		);
	};
	window.turnstileWooPayOrderCallback = function () {
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
		} else if (typeof turnstile !== 'undefined' && ! hasWidget) {
			// Try to inject for Checkout Block area if present
			const $actions = $(
				'.wc-block-checkout__actions, .wc-block-components-checkout-place-order-button'
			).first();
			if (
				$actions.length &&
				window.turnstileWoo &&
				window.turnstileWoo.siteKey
			) {
				// Avoid duplicate inject
				if ( ! $actions.prev('.turnstilewp-injected').length) {
					const mount = $(
						'<div class="turnstilewp-injected" style="margin:10px 0;"></div>'
					);
					$actions.before(mount);
					try {
						window.turnstile.render(mount.get(0), {
							sitekey: window.turnstileWoo.siteKey,
							callback: window.turnstileWooCheckoutCallback,
						});
						$submitButton.prop('disabled', true);
					} catch (e) {
}
				}
			}
			// If still no widget/mount, don't interfere
			if ( ! $('.cf-turnstile').length) {
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
})(jQuery);
