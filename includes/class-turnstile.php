<?php

/**
 * Turnstile Settings Integration
 *
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP;

class Turnstile {

	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct() {
		$this->settings = new Settings();

		// Enqueue Turnstile script where needed
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_script' ));
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_script' ));
	}

	/**
	 * Enqueue Turnstile script
	 */
	public function enqueue_script(): void {
		// Only enqueue if site key is set
		$site_key = $this->settings->get_option('tswp_site_key');
		if ( empty($site_key) ) {
			return;
		}
	wp_enqueue_script(
		'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js', // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile API must be loaded from their CDN per terms of service
		array(),
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN script, version controlled by Cloudflare
		null,
		true
	);
	}

	/**
	 * Render Turnstile widget
	 *
	 * @param string $action The action name for this widget
	 */
	public function render( string $action ): void {
		$site_key = $this->settings->get_option('tswp_site_key');
		if ( empty($site_key) ) {
			return;
		}

		$theme = $this->settings->get_option('tswp_theme', 'auto');
		$size = $this->settings->get_option('tswp_widget_size', 'normal');
		$language = $this->settings->get_option('tswp_language', 'auto');
		$appearance = $this->settings->get_option('tswp_appearance_mode', 'always');

		printf(
			'<div class="cf-turnstile" data-sitekey="%s" data-theme="%s" data-size="%s" data-language="%s" data-appearance="%s" data-action="%s"></div>',
			esc_attr($site_key),
			esc_attr($theme),
			esc_attr($size),
			esc_attr($language),
			esc_attr($appearance),
			esc_attr($action)
		);
	}

	/**
	 * Verify Turnstile response
	 *
	 * @param string|null $token The Turnstile response token
	 * @return bool
	 */
	public function verify( ?string $token = null ): bool {
		if ( empty($token) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile token itself provides CSRF protection
			$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : null;
		}

		if ( empty($token) ) {
			return false;
		}

		$secret_key = $this->settings->get_option('tswp_secret_key');
		if ( empty($secret_key) ) {
			return false;
		}

		$response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
			'body' => array(
				'secret' => $secret_key,
				'response' => $token,
				'remoteip' => \TurnstileWP\get_client_ip(),
			),
		));

		if ( is_wp_error($response) ) {
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		return isset($body['success']) && $body['success'] === true;
	}

	/**
	 * Render Turnstile widget dynamically (extensible, for all integrations)
	 *
	 * @param array $args
	 * @return void
	 */
	public function render_dynamic( array $args = array() ): void {
		$defaults = array(
			'button_id'   => '',
			'callback'    => '',
			'form_name'   => '',
			'unique_id'   => '',
			'class'       => '',
			'extra_attrs' => array(),
		);
		$args = array_merge($defaults, $args);

		// Allow disabling via filter
		if ( apply_filters('turnstilewp_widget_disable', false, $args) ) {
			return;
		}

		// Whitelist check (implement your own logic or hook)
		if ( function_exists('turnstilewp_is_whitelisted') && turnstilewp_is_whitelisted() ) {
			return;
		}

		$site_key   = $this->settings->get_option('tswp_site_key');
		$theme      = $this->settings->get_option('tswp_theme', 'auto');
		$language   = $this->settings->get_option('tswp_language', 'auto');
		$size       = $this->settings->get_option('tswp_widget_size', 'normal');
		$appearance = $this->settings->get_option('tswp_appearance_mode', 'always');

		// Allow pre-render hook
		do_action('turnstilewp_before_field', $args);

?>
		<div
			id="cf-turnstile<?php echo esc_attr($args['unique_id']); ?>"
			class="cf-turnstile<?php echo $args['class'] ? ' ' . esc_attr($args['class']) : ''; ?>"
			data-sitekey="<?php echo esc_attr($site_key); ?>"
			data-theme="<?php echo esc_attr($theme); ?>"
			data-language="<?php echo esc_attr($language); ?>"
			data-size="<?php echo esc_attr($size); ?>"
			data-appearance="<?php echo esc_attr($appearance); ?>"
			data-action="<?php echo esc_attr($args['form_name']); ?>"
			<?php if ( $args['callback'] ) : ?>
			data-callback="<?php echo esc_attr($args['callback']); ?>"
			<?php endif; ?>
			<?php
			// Output any extra attributes
			foreach ( $args['extra_attrs'] as $attr => $val ) {
				printf(' %s="%s"', esc_attr($attr), esc_attr($val));
			}
			?>
			>
		</div>
<?php

		// Allow post-render hook
		do_action('turnstilewp_after_field', $args);
	}
}

// Instantiate the Turnstile integration
new Turnstile();

// Register Turnstile Settings fields for centralized settings
add_filter('turnstilewp_settings', function ( $fields ) {
	// API Settings Section
	$fields[] = array(
		'field_id'    => 'tswp_info_box',
		'type'        => 'content',
		'content'     => '<div class="turnstilewp-content">'
			. '<strong>' . sprintf(
				// translators: %s: Cloudflare dashboard URL
				__('You can get your site key and secret key from here: %s', 'smart-cloudflare-turnstile'),
				'<a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">https://dash.cloudflare.com/?to=/:account/turnstile</a>'
			) . '</strong>'
			. '</div>',
		'tab'         => 'turnstile_settings',
		'section'     => 'api_settings',
		'group'       => 'api_keys',
		'group_title' => '<h2>Cloudflare API Keys</h2>',
		'priority'    => 5,
	);
	$fields[] = array(
		'field_id'    => 'tswp_site_key',
		'label'       => __('Site Key', 'smart-cloudflare-turnstile'),
		'description' => __('Your Cloudflare Turnstile site key.', 'smart-cloudflare-turnstile'),
		'type'        => 'text',
		'tab'         => 'turnstile_settings',
		'section'     => 'api_settings',
		'priority'    => 10,
		'default'     => '',
		'group'       => 'api_keys',
	);
	$fields[] = array(
		'field_id'    => 'tswp_secret_key',
		'label'       => __('Secret Key', 'smart-cloudflare-turnstile'),
		'description' => __('Your Cloudflare Turnstile secret key.', 'smart-cloudflare-turnstile'),
		'type'        => 'password',
		'tab'         => 'turnstile_settings',
		'section'     => 'api_settings',
		'priority'    => 20,
		'default'     => '',
		'group'       => 'api_keys',
	);
	// Appearance Section
	$fields[] = array(
		'field_id'    => 'tswp_appearance_mode',
		'label'       => __('Appearance Mode', 'smart-cloudflare-turnstile'),
		'description' => __('Choose how the Turnstile widget appears.', 'smart-cloudflare-turnstile'),
		'type'        => 'select',
		'options'     => array(
			'always' => __('Always', 'smart-cloudflare-turnstile'),
			'interaction_only' => __('Interaction Only', 'smart-cloudflare-turnstile'),
		),
		'tab'         => 'turnstile_settings',
		'section'     => 'widget_options',
		'group'       => 'advanced_settings',
		'priority'    => 10,
		'default'     => 'always',
	);
	$fields[] = array(
		'field_id'    => 'tswp_widget_size',
		'label'       => __('Widget Size', 'smart-cloudflare-turnstile'),
		'description' => __('Choose the Turnstile widget size.', 'smart-cloudflare-turnstile'),
		'type'        => 'select',
		'options'     => array(
			'normal' => __('Normal (300px)', 'smart-cloudflare-turnstile'),
			'flexible' => __('Flexible (100%)', 'smart-cloudflare-turnstile'),
			'compact' => __('Compact (150px)', 'smart-cloudflare-turnstile'),
		),
		'tab'         => 'turnstile_settings',
		'group'       => 'advanced_settings',
		'section'     => 'widget_options',
		'priority'    => 20,
		'default'     => 'normal',
	);
	$fields[] = array(
		'field_id'    => 'tswp_theme',
		'label'       => __('Theme', 'smart-cloudflare-turnstile'),
		'description' => __('Choose the Turnstile widget theme.', 'smart-cloudflare-turnstile'),
		'type'        => 'select',
		'options'     => array(
			'auto'  => __('Auto', 'smart-cloudflare-turnstile'),
			'light' => __('Light', 'smart-cloudflare-turnstile'),
			'dark'  => __('Dark', 'smart-cloudflare-turnstile'),
		),
		'group'       => 'advanced_settings',
		'group_title' => '<h2>Advanced Settings</h2>',
		'tab'         => 'turnstile_settings',
		'section'     => 'widget_options',
		'priority'    => 5,
		'default'     => 'auto',
	);
	$fields[] = array(
		'field_id'    => 'tswp_language',
		'label'       => __('Language', 'smart-cloudflare-turnstile'),
		'description' => __('Widget language code.', 'smart-cloudflare-turnstile'),
		'type'        => 'select',
		'group'       => 'advanced_settings',
		'options'     => array(
			'auto' => __('Auto Detect', 'smart-cloudflare-turnstile'),
			'ar-eg' => __('Arabic (Egypt)', 'smart-cloudflare-turnstile'),
			'bg-bg' => __('Bulgarian (Bulgaria)', 'smart-cloudflare-turnstile'),
			'zh-cn' => __('Chinese (Simplified, China)', 'smart-cloudflare-turnstile'),
			'zh-tw' => __('Chinese (Traditional, Taiwan)', 'smart-cloudflare-turnstile'),
			'hr-hr' => __('Croatian (Croatia)', 'smart-cloudflare-turnstile'),
			'cs-cz' => __('Czech (Czech Republic)', 'smart-cloudflare-turnstile'),
			'da-dk' => __('Danish (Denmark)', 'smart-cloudflare-turnstile'),
			'nl-nl' => __('Dutch (Netherlands)', 'smart-cloudflare-turnstile'),
			'en-us' => __('English (United States)', 'smart-cloudflare-turnstile'),
			'fa-ir' => __('Farsi (Iran)', 'smart-cloudflare-turnstile'),
			'fi-fi' => __('Finnish (Finland)', 'smart-cloudflare-turnstile'),
			'fr-fr' => __('French (France)', 'smart-cloudflare-turnstile'),
			'de-de' => __('German (Germany)', 'smart-cloudflare-turnstile'),
			'el-gr' => __('Greek (Greece)', 'smart-cloudflare-turnstile'),
			'he-il' => __('Hebrew (Israel)', 'smart-cloudflare-turnstile'),
			'hi-in' => __('Hindi (India)', 'smart-cloudflare-turnstile'),
			'hu-hu' => __('Hungarian (Hungary)', 'smart-cloudflare-turnstile'),
			'id-id' => __('Indonesian (Indonesia)', 'smart-cloudflare-turnstile'),
			'it-it' => __('Italian (Italy)', 'smart-cloudflare-turnstile'),
			'ja-jp' => __('Japanese (Japan)', 'smart-cloudflare-turnstile'),
			'tlh'   => __("Klingon (Qo'noS)", 'smart-cloudflare-turnstile'),
			'ko-kr' => __('Korean (Korea)', 'smart-cloudflare-turnstile'),
			'lt-lt' => __('Lithuanian (Lithuania)', 'smart-cloudflare-turnstile'),
			'ms-my' => __('Malay (Malaysia)', 'smart-cloudflare-turnstile'),
			'nb-no' => __('Norwegian Bokmål (Norway)', 'smart-cloudflare-turnstile'),
			'pl-pl' => __('Polish (Poland)', 'smart-cloudflare-turnstile'),
			'pt-br' => __('Portuguese (Brazil)', 'smart-cloudflare-turnstile'),
			'ro-ro' => __('Romanian (Romania)', 'smart-cloudflare-turnstile'),
			'ru-ru' => __('Russian (Russia)', 'smart-cloudflare-turnstile'),
			'sr-ba' => __('Serbian (Bosnia and Herzegovina)', 'smart-cloudflare-turnstile'),
			'sk-sk' => __('Slovak (Slovakia)', 'smart-cloudflare-turnstile'),
			'sl-si' => __('Slovenian (Slovenia)', 'smart-cloudflare-turnstile'),
			'es-es' => __('Spanish (Spain)', 'smart-cloudflare-turnstile'),
			'sv-se' => __('Swedish (Sweden)', 'smart-cloudflare-turnstile'),
			'tl-ph' => __('Tagalog (Philippines)', 'smart-cloudflare-turnstile'),
			'th-th' => __('Thai (Thailand)', 'smart-cloudflare-turnstile'),
			'tr-tr' => __('Turkish (Turkey)', 'smart-cloudflare-turnstile'),
			'uk-ua' => __('Ukrainian (Ukraine)', 'smart-cloudflare-turnstile'),
			'vi-vn' => __('Vietnamese (Vietnam)', 'smart-cloudflare-turnstile'),
		),
		'tab'         => 'turnstile_settings',
		'section'     => 'widget_options',
		'priority'    => 6,
		'default'     => 'auto',
	);
	// Error Handling Section
	$fields[] = array(
		'field_id'    => 'tswp_custom_error_message',
		'label'       => __('Custom Error Message', 'smart-cloudflare-turnstile'),
		'description' => __('Custom message to display when Turnstile verification fails.', 'smart-cloudflare-turnstile'),
		'type'        => 'text',
		'tab'         => 'turnstile_settings',
		'section'     => 'error_handling',
		'group'       => 'message_settings',
		'group_title' => '<h2>Message Settings</h2>',
		'priority'    => 10,
		'default'     => '',
	);
	$fields[] = array(
		'field_id'    => 'tswp_extra_failure_message',
		'label'       => __('Extra Failure Message', 'smart-cloudflare-turnstile'),
		'description' => __('Additional message or instructions to display when verification fails.', 'smart-cloudflare-turnstile'),
		'type'        => 'textarea',
		'tab'         => 'turnstile_settings',
		'group'       => 'message_settings',
		'section'     => 'error_handling',
		'priority'    => 20,
		'default'     => '',
	);
	// Script Options Section
	$fields[] = array(
		'field_id'    => 'tswp_defer_script',
		'label'       => __('Defer Script', 'smart-cloudflare-turnstile'),
		'description' => __('Defer loading of the Turnstile script for improved performance.', 'smart-cloudflare-turnstile'),
		'type'        => 'checkbox',
		'tab'         => 'turnstile_settings',
		'group'       => 'script_options',
		'group_title' => '<h2>Script Options</h2>',
		'section'     => 'script_options',
		'priority'    => 10,
		'default'     => false,
	);
	return $fields;
});
