/**
 * MailPoet Integration for SmartCT
 * Handles Turnstile widget injection and token submission for MailPoet Gutenberg forms
 *
 * @since 1.1.0
 * @author Mayank Majeji
 * @date 2025-01-21
 */
(function () {
	'use strict';

	/**
	 * Initialize MailPoet Turnstile integration
	 */
	function initMailPoetTurnstile() {
		// Early exit if Turnstile is not available
		if (typeof window.turnstile === 'undefined') {
			return;
		}

		// Early exit if site key is not available
		if (
			typeof smartctMailPoet === 'undefined' ||
			!smartctMailPoet.siteKey
		) {
			return;
		}

		// Find all MailPoet forms
		const forms = document.querySelectorAll('form.mailpoet_form');

		if (!forms.length) {
			return;
		}

		forms.forEach(function (form) {
			// Skip if already processed
			if (form.dataset.smartctProcessed === 'true') {
				return;
			}

			// Find submit button
			const submitButton = form.querySelector(
				'input[type="submit"].mailpoet_submit, button[type="submit"].mailpoet_submit'
			);

			if (!submitButton) {
				return;
			}

			// Check if Turnstile widget already exists
			if (form.querySelector('.cf-turnstile')) {
				form.dataset.smartctProcessed = 'true';
				return;
			}

			// Create container for Turnstile widget
			const turnstileContainer = document.createElement('div');
			turnstileContainer.className = 'smartct-mailpoet-turnstile';
			turnstileContainer.style.marginBottom = '15px';

			// Insert before submit button
			submitButton.parentNode.insertBefore(
				turnstileContainer,
				submitButton
			);

			// Render Turnstile widget if available
			if (typeof window.turnstile !== 'undefined') {
				// Get site key from localized script data or form data attribute
				const siteKey =
					(typeof smartctMailPoet !== 'undefined' &&
						smartctMailPoet.siteKey) ||
					turnstileContainer.closest('form').dataset.sitekey ||
					document.querySelector('script[data-sitekey]')?.dataset
						.sitekey ||
					'';

				if (siteKey) {
					try {
						window.turnstile.render(turnstileContainer, {
							sitekey: siteKey,
							callback: function (token) {
								// Token is automatically available in the form
							},
							'error-callback': function () {
								console.warn(
									'Turnstile error for MailPoet form'
								);
							},
						});
					} catch (e) {
						console.warn(
							'Failed to render Turnstile for MailPoet:',
							e
						);
					}
				}
			}

			// Handle form submission - copy token to MailPoet's nested data structure
			form.addEventListener('submit', function (event) {
				const tokenInput = form.querySelector(
					'input[name="cf-turnstile-response"]'
				);

				if (tokenInput && tokenInput.value) {
					// Remove existing nested token input if any
					const existingNested = form.querySelector(
						'input[name="data[cf-turnstile-response]"]'
					);
					if (existingNested) {
						existingNested.remove();
					}

					// Create hidden input with nested name for MailPoet
					const nestedTokenInput = document.createElement('input');
					nestedTokenInput.type = 'hidden';
					nestedTokenInput.name = 'data[cf-turnstile-response]';
					nestedTokenInput.value = tokenInput.value;
					form.appendChild(nestedTokenInput);
				}
			});

			// Mark as processed
			form.dataset.smartctProcessed = 'true';
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMailPoetTurnstile);
	} else {
		initMailPoetTurnstile();
	}

	// Also initialize after a delay to catch dynamically loaded forms
	setTimeout(initMailPoetTurnstile, 1000);

	// Watch for dynamically added forms (for Gutenberg blocks)
	if (typeof MutationObserver !== 'undefined') {
		const observer = new MutationObserver(function (mutations) {
			let shouldCheck = false;
			mutations.forEach(function (mutation) {
				if (mutation.addedNodes.length) {
					mutation.addedNodes.forEach(function (node) {
						if (
							node.nodeType === 1 &&
							(node.classList.contains('mailpoet_form') ||
								node.querySelector('form.mailpoet_form'))
						) {
							shouldCheck = true;
						}
					});
				}
			});
			if (shouldCheck) {
				setTimeout(initMailPoetTurnstile, 100);
			}
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	}
})();
