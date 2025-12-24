<?php

/**
 * Settings Class
 *
 * @package SmartCT
 */

declare(strict_types=1);

namespace SmartCT\Settings;

class Settings {

	/**
	 * Option name
	 *
	 * @var string
	 */
	private string $option_name = 'smartct_settings';

	/**
	 * Sensitive fields that should be encrypted
	 *
	 * @var array
	 */
	private array $sensitive_fields = array(
		'secret_key',
	);

	/**
	 * Get all options
	 */
	public function get_all_options(): array {
		$options = get_option($this->option_name, array());

		// Decrypt sensitive fields
		foreach ( $this->sensitive_fields as $field ) {
			if ( isset($options[ $field ]) ) {
				$options[ $field ] = $this->decrypt_value($options[ $field ]);
			}
		}

		return $options;
	}

	/**
	 * Get option
	 */
	public function get_option( string $key, $default = '' ) {
		$options = $this->get_all_options();
		$value = $options[ $key ] ?? $default;

		// Decrypt if it's a sensitive field
		if ( in_array($key, $this->sensitive_fields, true) ) {
			$value = $this->decrypt_value($value);
		}

		return $value;
	}

	/**
	 * Update option
	 */
	public function update_option( string $key, $value ): bool {
		$options = $this->get_all_options();

		// Encrypt if it's a sensitive field
		if ( in_array($key, $this->sensitive_fields, true) ) {
			$value = $this->encrypt_value($value);
		}

		$options[ $key ] = $value;
		return update_option($this->option_name, $options);
	}

	/**
	 * Delete option
	 */
	public function delete_option( string $key ): bool {
		$options = $this->get_all_options();
		if ( isset($options[ $key ]) ) {
			unset($options[ $key ]);
			return update_option($this->option_name, $options);
		}
		return false;
	}

	/**
	 * Encrypt value
	 */
	private function encrypt_value( string $value ): string {
		if ( empty($value) ) {
			return '';
		}

		$key = $this->get_encryption_key();
		$method = 'aes-256-cbc';
		$ivlen = openssl_cipher_iv_length($method);
		$iv = openssl_random_pseudo_bytes($ivlen);

		$encrypted = openssl_encrypt($value, $method, $key, 0, $iv);
		if ( $encrypted === false ) {
			return '';
		}

		return base64_encode($iv . $encrypted);
	}

	/**
	 * Decrypt value
	 */
	private function decrypt_value( string $value ): string {
		if ( empty($value) ) {
			return '';
		}

		$key = $this->get_encryption_key();
		$method = 'aes-256-cbc';
		$ivlen = openssl_cipher_iv_length($method);

		$decoded = base64_decode($value);
		if ( $decoded === false ) {
			return '';
		}

		$iv = substr($decoded, 0, $ivlen);
		$encrypted = substr($decoded, $ivlen);

		$decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
		if ( $decrypted === false ) {
			return '';
		}

		return $decrypted;
	}

	/**
	 * Get encryption key
	 */
	private function get_encryption_key(): string {
		$key = defined('AUTH_KEY') ? AUTH_KEY : wp_salt('auth');
		return hash('sha256', $key, true);
	}
}
