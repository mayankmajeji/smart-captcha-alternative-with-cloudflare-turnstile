<?php

/**
 * Core WordPress integration
 *
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP;

/**
 * Class Core_WP
 */
class Core_WP {

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Verify instance
	 *
	 * @var Verify
	 */
	private Verify $verify;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = new Settings();
		$this->verify = new Verify();

		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		// Login form
		if ( $this->settings->get_option('tswp_enable_login') ) {
			add_action('login_form', array( $this, 'render_turnstile_field' ));
			add_filter('authenticate', array( $this, 'verify_login' ), 30, 3);
		}

		// Registration form
		if ( $this->settings->get_option('tswp_enable_register') ) {
			add_action('register_form', array( $this, 'render_turnstile_field' ));
			add_action('registration_errors', array( $this, 'verify_registration' ), 10, 3);
		}

		// Lost password form
		if ( $this->settings->get_option('tswp_enable_lost_password') ) {
			add_action('lostpassword_form', array( $this, 'render_turnstile_field' ));
			add_action('lostpassword_post', array( $this, 'verify_lost_password' ));
		}

		// Comment form
		if ( $this->settings->get_option('tswp_enable_comments') ) {
			// Hook to add Turnstile right before submit button (works for both logged-in and logged-out users)
			add_filter('comment_form_submit_field', array( $this, 'add_turnstile_before_submit' ), 10, 2);
			add_action('preprocess_comment', array( $this, 'verify_comment' ));
		}
	}

	/**
	 * Render Turnstile field using the new dynamic method
	 */
	public function render_turnstile_field(): void {
		if ( ! get_option('turnstilewp_keys_verified', 0) ) {
			return;
		}
		if ( ! $this->should_show_turnstile() ) {
			return;
		}

		$turnstile = new \TurnstileWP\Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => $this->get_form_context(),
			'unique_id' => uniqid(),
			'class'     => 'turnstilewp-core-form',
		));
	}

	/**
	 * Add Turnstile widget before submit button in comment form
	 *
	 * @param string $submit_field The submit field HTML.
	 * @param array  $args         Comment form arguments.
	 * @return string
	 */
	public function add_turnstile_before_submit( string $submit_field, array $args ): string {
		if ( ! get_option('turnstilewp_keys_verified', 0) ) {
			return $submit_field;
		}
		if ( ! $this->should_show_turnstile() ) {
			return $submit_field;
		}

		ob_start();
		$turnstile = new \TurnstileWP\Turnstile();
		$turnstile->render_dynamic(array(
			'form_name' => 'wordpress-comment',
			'unique_id' => uniqid(),
			'class'     => 'turnstilewp-core-form',
		));
		$turnstile_html = ob_get_clean();

		// Prepend Turnstile widget before submit button
		return '<div class="turnstilewp-comment-before-submit" style="margin-bottom: 1em;">' . $turnstile_html . '</div>' . $submit_field;
	}

	/**
	 * Helper to determine the form context for Turnstile rendering
	 */
	private function get_form_context(): string {
		// Use current filter to determine context
		$current_filter = current_filter();
		switch ( $current_filter ) {
			case 'login_form':
				return 'wordpress-login';
			case 'register_form':
				return 'wordpress-register';
			case 'lostpassword_form':
				return 'wordpress-lost-password';
			case 'comment_form_after_fields':
			case 'comment_form_logged_in_after':
				return 'wordpress-comment';
			default:
				return 'wordpress-form';
		}
	}

	/**
	 * Verify login
	 *
	 * @param \WP_User|\WP_Error|null $user     WP_User or WP_Error object from a previous callback.
	 * @param string                  $username Username.
	 * @param string                  $password Password.
	 * @return \WP_User|\WP_Error|null
	 */
	public function verify_login( $user, string $username, string $password ) {
		if ( ! $this->should_show_turnstile() ) {
			return $user;
		}

		if ( ! $this->verify_token() ) {
			return new \WP_Error(
				'turnstile_failed',
				__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile')
			);
		}

		return $user;
	}

	/**
	 * Verify registration
	 *
	 * @param \WP_Error $errors               A WP_Error object containing any errors encountered during registration.
	 * @param string    $sanitized_user_login User's username after it has been sanitized.
	 * @param string    $user_email           User's email.
	 * @return \WP_Error
	 */
	public function verify_registration( \WP_Error $errors, string $sanitized_user_login, string $user_email ): \WP_Error {
		if ( ! $this->should_show_turnstile() ) {
			return $errors;
		}

		if ( ! $this->verify_token() ) {
			$errors->add(
				'turnstile_failed',
				__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile')
			);
		}

		return $errors;
	}

	/**
	 * Verify lost password
	 *
	 * @param \WP_Error $errors A WP_Error object containing any errors encountered.
	 * @return \WP_Error
	 */
	public function verify_lost_password( \WP_Error $errors ): \WP_Error {
		if ( ! $this->should_show_turnstile() ) {
			return $errors;
		}

		if ( ! $this->verify_token() ) {
			$errors->add(
				'turnstile_failed',
				__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile')
			);
		}

		return $errors;
	}

	/**
	 * Verify comment
	 *
	 * @param array $commentdata Comment data.
	 * @return array
	 */
	public function verify_comment( array $commentdata ): array {
		if ( ! $this->should_show_turnstile() ) {
			return $commentdata;
		}

		if ( ! $this->verify_token() ) {
			wp_die(
				esc_html__('Turnstile verification failed. Please try again.', 'smart-cloudflare-turnstile'),
				esc_html__('Comment Submission Error', 'smart-cloudflare-turnstile'),
				array( 'response' => 403 )
			);
		}

		return $commentdata;
	}

	/**
	 * Verify Turnstile token
	 *
	 * @return bool
	 */
	private function verify_token(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
		$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';
		return $this->verify->verify_token($token);
	}

	/**
	 * Check if Turnstile should be shown
	 *
	 * @return bool
	 */
	private function should_show_turnstile(): bool {
		// Don't show for logged-in users unless configured
		if ( is_user_logged_in() ) {
			// Use Settings class to get the option (handles tswp_ prefix automatically)
			$show_for_logged_in = $this->settings->get_option('tswp_show_for_logged_in', false);
			if ( ! $show_for_logged_in ) {
				return false;
			}
		}

		// Allow developers to filter this
		return apply_filters('turnstilewp_should_show', true);
	}
}

// Register core WordPress form fields for centralized settings
add_filter('turnstilewp_settings', function ( $fields ) {
	// Login & Registration Section
	$fields[] = array(
		'field_id'    => 'enable_login',
		'label'       => __('Enable on Login', 'smart-cloudflare-turnstile'),
		'description' => __('Enable Turnstile on the WordPress login form.', 'smart-cloudflare-turnstile'),
		'type'        => 'checkbox',
		'tab'         => 'default_wordpress_forms',
		'section'     => 'login_registration',
		'group'       => 'login_form',
		'group_title' => '<h2>Login Form</h2>',
		'priority'    => 10,
		'default'     => true,
	);
	$fields[] = array(
		'field_id'    => 'enable_register',
		'label'       => __('Enable on Registration', 'smart-cloudflare-turnstile'),
		'description' => __('Enable Turnstile on the WordPress registration form.', 'smart-cloudflare-turnstile'),
		'type'        => 'checkbox',
		'tab'         => 'default_wordpress_forms',
		'section'     => 'login_registration',
		'group'       => 'registration_form',
		'group_title' => '<h2>Registration Form</h2>',
		'priority'    => 20,
		'default'     => true,
	);
	$fields[] = array(
		'field_id'    => 'enable_lost_password',
		'label'       => __('Enable on Lost Password', 'smart-cloudflare-turnstile'),
		'description' => __('Enable Turnstile on the lost password form.', 'smart-cloudflare-turnstile'),
		'type'        => 'checkbox',
		'tab'         => 'default_wordpress_forms',
		'section'     => 'login_registration',
		'group'       => 'lost_password_form',
		'group_title' => '<h2>Lost Password Form</h2>',
		'priority'    => 30,
		'default'     => true,
	);
	// Comments Section
	$fields[] = array(
		'field_id'    => 'enable_comments',
		'label'       => __('Enable on Comments', 'smart-cloudflare-turnstile'),
		'description' => __('Enable Turnstile on the WordPress comment form.', 'smart-cloudflare-turnstile'),
		'type'        => 'checkbox',
		'tab'         => 'default_wordpress_forms',
		'section'     => 'comments',
		'group'       => 'comments_form',
		'group_title' => '<h2>Comments Form</h2>',
		'priority'    => 10,
		'default'     => true,
	);
	// Move "Show for Logged-in Users" under Comments Form group to sync styling
	$fields[] = array(
		'field_id'    => 'show_for_logged_in',
		'label'       => __('Show for Logged-in Users', 'smart-cloudflare-turnstile'),
		'description' => __('Show Turnstile for logged-in users on all forms.', 'smart-cloudflare-turnstile'),
		'type'        => 'checkbox',
		'tab'         => 'default_wordpress_forms',
		'section'     => 'comments',
		'group'       => 'comments_form',
		'priority'    => 20,
		'default'     => false,
	);
	return $fields;
});
