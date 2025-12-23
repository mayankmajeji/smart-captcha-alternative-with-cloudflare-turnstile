<?php

/**
 * WooCommerce Settings Fields
 *
 * @package TurnstileWP
 * @subpackage TurnstileWP/includes/settings
 */

if ( ! defined('ABSPATH') ) {
	exit;
}

return array(
	'woocommerce' => array(
		'title' => __('WooCommerce Settings', 'smart-cloudflare-turnstile'),
		'description' => __('Configure Turnstile settings for WooCommerce integration.', 'smart-cloudflare-turnstile'),
		'fields' => array(
			'tswp_woo_checkout' => array(
				'type' => 'checkbox',
				'label' => __('Enable on Checkout', 'smart-cloudflare-turnstile'),
				'description' => __('Enable Turnstile verification on the WooCommerce checkout page.', 'smart-cloudflare-turnstile'),
				'default' => false,
			),
			'tswp_woo_checkout_position' => array(
				'type' => 'select',
				'label' => __('Checkout Position', 'smart-cloudflare-turnstile'),
				'description' => __('Choose where to display the Turnstile widget on the checkout page.', 'smart-cloudflare-turnstile'),
				'default' => 'before_payment',
				'options' => array(
					'before_payment' => __('Before Payment Section', 'smart-cloudflare-turnstile'),
					'after_payment' => __('After Payment Section', 'smart-cloudflare-turnstile'),
					'before_billing' => __('Before Billing Details', 'smart-cloudflare-turnstile'),
					'after_billing' => __('After Billing Details', 'smart-cloudflare-turnstile'),
					'before_submit' => __('Before Submit Button', 'smart-cloudflare-turnstile'),
				),
				'dependency' => array(
					'field' => 'tswp_woo_checkout',
					'value' => true,
				),
			),
			'tswp_woo_login' => array(
				'type' => 'checkbox',
				'label' => __('Enable on Login', 'smart-cloudflare-turnstile'),
				'description' => __('Enable Turnstile verification on the WooCommerce login form.', 'smart-cloudflare-turnstile'),
				'default' => false,
			),
			'tswp_woo_register' => array(
				'type' => 'checkbox',
				'label' => __('Enable on Registration', 'smart-cloudflare-turnstile'),
				'description' => __('Enable Turnstile verification on the WooCommerce registration form.', 'smart-cloudflare-turnstile'),
				'default' => false,
			),
			'tswp_woo_reset' => array(
				'type' => 'checkbox',
				'label' => __('Enable on Password Reset', 'smart-cloudflare-turnstile'),
				'description' => __('Enable Turnstile verification on the WooCommerce password reset form.', 'smart-cloudflare-turnstile'),
				'default' => false,
			),
			'tswp_woo_pay_order' => array(
				'type' => 'checkbox',
				'label' => __('Enable on Pay Order', 'smart-cloudflare-turnstile'),
				'description' => __('Enable Turnstile verification on the WooCommerce pay order page.', 'smart-cloudflare-turnstile'),
				'default' => false,
			),
			'tswp_guest_only' => array(
				'type' => 'checkbox',
				'label' => __('Guest Only', 'smart-cloudflare-turnstile'),
				'description' => __('Only show Turnstile verification for guest users.', 'smart-cloudflare-turnstile'),
				'default' => false,
			),
			'tswp_excluded_payment_methods' => array(
				'type' => 'multiselect',
				'label' => __('Excluded Payment Methods', 'smart-cloudflare-turnstile'),
				'description' => __('Select payment methods to exclude from Turnstile verification.', 'smart-cloudflare-turnstile'),
				'default' => array(),
				'options' => function () {
					$payment_gateways = WC()->payment_gateways()->payment_gateways();
					$options = array();
					foreach ( $payment_gateways as $gateway ) {
						$options[ $gateway->id ] = $gateway->get_title();
					}
					return $options;
				},
				'dependency' => array(
					'field' => 'tswp_woo_checkout',
					'value' => true,
				),
			),
		),
	),
);
