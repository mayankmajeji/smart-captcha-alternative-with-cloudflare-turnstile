<?php

/**
 * Token verification class
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT;

/**
 * Class Verify
 */
class Verify
{

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->settings = new Settings();
	}

	/**
	 * Verify Turnstile token
	 *
	 * @param string $token Turnstile token.
	 * @param string|null $custom_secret_key Optional custom secret key (for admin verification).
	 * @return bool
	 */
	public function verify_token(string $token, ?string $custom_secret_key = null): bool
	{
		if (empty($token)) {
			return false;
		}

		$secret_key = $custom_secret_key ?: $this->settings->get_option('smartct_secret_key');
		if (empty($secret_key)) {
			return false;
		}

		$appearance_mode = $this->settings->get_option('smartct_appearance_mode', 'always');
		if ($appearance_mode === 'interaction_only') {
			$appearance_mode = 'interaction-only';
		}
		$response = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'body' => array(
					'secret' => $secret_key,
					'response' => $token,
					'remoteip' => \SmartCT\get_client_ip(),
					'appearance' => $appearance_mode,
				),
			)
		);

		if (is_wp_error($response)) {
			$this->log_error('Verification request failed: ' . $response->get_error_message());
			return false;
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (! is_array($data)) {
			$this->log_error('Invalid response from Cloudflare: ' . $body);
			return false;
		}

		if (! isset($data['success']) || ! $data['success']) {
			$this->log_error('Verification failed: ' . ($data['error-codes'][0] ?? 'Unknown error'));
			return false;
		}

		return true;
	}

	/**
	 * Log error message if debug mode is enabled
	 *
	 * @param string $message Error message.
	 */
	private function log_error(string $message): void
	{
		if ($this->settings->get_option('smartct_debug_mode')) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log -- Intentional debug logging when debug mode is enabled
			// error_log('[SmartCT] ' . $message);
		}
	}
}
