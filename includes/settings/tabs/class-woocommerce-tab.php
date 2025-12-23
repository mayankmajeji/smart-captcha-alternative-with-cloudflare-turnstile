<?php

/**
 * WooCommerce Settings Tab
 *
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP\Settings\Tabs;

use TurnstileWP\Settings\Tab;

class WooCommerce_Tab extends Tab {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'woocommerce';
		$this->label = __('WooCommerce', 'smart-cloudflare-turnstile');
		$this->icon = 'dashicons-cart';
		$this->priority = 30;
		$this->sections = array(
			'general' => array(
				'title' => __('General Settings', 'smart-cloudflare-turnstile'),
				'description' => __('Configure Turnstile for WooCommerce general forms.', 'smart-cloudflare-turnstile'),
			),
			'checkout' => array(
				'title' => __('Checkout Settings', 'smart-cloudflare-turnstile'),
				'description' => __('Configure Turnstile for WooCommerce checkout.', 'smart-cloudflare-turnstile'),
			),
			'pay_for_order' => array(
				'title' => __('Pay For Order Settings', 'smart-cloudflare-turnstile'),
				'description' => __('Configure Turnstile for WooCommerce Pay For Order page.', 'smart-cloudflare-turnstile'),
			),
		);
	}

	/**
	 * Get tab content
	 */
	public function get_content(): string {
		ob_start();
?>
		<div class="turnstilewp-tab-content">
			<div class="turnstilewp-section">
				<h2><?php esc_html_e('WooCommerce Integration', 'smart-cloudflare-turnstile'); ?></h2>
				<p><?php esc_html_e('Configure how Turnstile integrates with WooCommerce forms.', 'smart-cloudflare-turnstile'); ?></p>
			</div>
		</div>
<?php
		return ob_get_clean();
	}
}
