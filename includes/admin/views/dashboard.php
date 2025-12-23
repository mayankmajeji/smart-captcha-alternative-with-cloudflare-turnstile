<?php
/**
 * Dashboard Page
 *
 * @package TurnstileWP
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if ( ! defined('WPINC') ) {
	die;
}

use TurnstileWP\Settings;

$settings      = new Settings();
$values        = $settings->get_settings();
$keys_verified = (int) get_option('turnstilewp_keys_verified', 0) === 1;
// Respect constants via get_option()
$site_key      = (string) $settings->get_option('tswp_site_key', '');
$secret_key    = (string) $settings->get_option('tswp_secret_key', '');
$has_keys      = ( $site_key !== '' && $secret_key !== '' );

$masked_site   = $site_key ? ( substr($site_key, 0, 4) . '…' . substr($site_key, -4) ) : '';
$last_rotation = get_option('turnstilewp_keys_rotated_at', '');

// Core integrations
$core_integrations = array(
	'Login'         => (bool) ( $values['enable_login'] ?? false ),
	'Registration'  => (bool) ( $values['enable_register'] ?? false ),
	'Lost Password' => (bool) ( $values['enable_lost_password'] ?? false ),
	'Comments'      => (bool) ( $values['enable_comments'] ?? false ),
);

// WooCommerce integrations
$wc_active = class_exists('WooCommerce');
$wc_integrations = array(
	'Login'          => (bool) $settings->get_option('tswp_woo_login', ( $values['woo_login'] ?? false )),
	'Registration'   => (bool) $settings->get_option('tswp_woo_register', ( $values['woo_register'] ?? false )),
	'Reset Password' => (bool) $settings->get_option('tswp_woo_reset', ( $values['woo_reset_password'] ?? false )),
	'Checkout'       => (bool) $settings->get_option('tswp_woo_checkout', ( $values['woo_checkout'] ?? false )),
	'Pay for Order'  => (bool) $settings->get_option('tswp_woo_pay_order', ( $values['woo_pay_for_order'] ?? false )),
);
$wc_version = $wc_active ? get_option('woocommerce_version') : '';

global $wp_version;
$php_version = PHP_VERSION;

// Allow third-parties to register extra integration status rows
// Expected shape: [ [ 'label' => 'Contact Form 7', 'enabled' => true, 'configure_url' => 'admin.php?page=...' ], ... ]
$external_integrations = apply_filters('turnstilewp_integrations', array());

?>
<div class="turnstilewp-page turnstilewp-page--dashboard">
	<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
	<div class="turnstilewp-body">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

		<div class="turnstilewp-admin-layout">
			<div class="turnstilewp-section">

				<div class="turnstilewp-sub-section">
					<h2><?php esc_html_e('Status', 'smart-cloudflare-turnstile'); ?></h2>
					<div class="twp-status-cards" style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
						<div class="twp-card" style="background:#fff;border:1px solid #e5e5e5;padding:16px;border-radius:6px;">
							<strong><?php esc_html_e('API Keys', 'smart-cloudflare-turnstile'); ?></strong>
							<p style="margin:.5em 0 0;">
								<?php if ( $has_keys ) : ?>
									<?php
									// translators: %s: Masked site key (partially hidden for security)
									echo esc_html(sprintf(__('Site Key: %s', 'smart-cloudflare-turnstile'), $masked_site));
									?><br>
									<?php if ( $keys_verified ) : ?>
										<span style="color:#46b450;"><?php esc_html_e('Verified', 'smart-cloudflare-turnstile'); ?></span>
									<?php else : ?>
										<span style="color:#d63638;"><?php esc_html_e('Not Verified', 'smart-cloudflare-turnstile'); ?></span>
									<?php endif; ?>
								<?php else : ?>
									<span style="color:#d63638;"><?php esc_html_e('Missing keys', 'smart-cloudflare-turnstile'); ?></span>
								<?php endif; ?>
							</p>
							<p style="margin:.25em 0 0;color:#757575;">
								<?php
								echo esc_html__(
									'Last Key Rotation:',
									'smart-cloudflare-turnstile'
								);
								echo ' ';
								echo $last_rotation
									? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $last_rotation))
									: esc_html__('Never rotated', 'smart-cloudflare-turnstile');
								?>
							</p>
							<p style="margin-top:.75em;">
								<a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp-settings')); ?>">
									<?php esc_html_e('Open Settings', 'smart-cloudflare-turnstile'); ?>
								</a>
								<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp-settings')); ?>">
									<?php esc_html_e('Edit Keys', 'smart-cloudflare-turnstile'); ?>
								</a>
								<a class="button" href="https://github.com/mayankmajeji/turnstilewp" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e('Documentation', 'smart-cloudflare-turnstile'); ?>
								</a>
							</p>
						</div>

						<div class="twp-card" style="background:#fff;border:1px solid #e5e5e5;padding:16px;border-radius:6px;">
							<strong><?php esc_html_e('Core Forms', 'smart-cloudflare-turnstile'); ?></strong>
							<ul style="margin:.5em 0 0;padding-left:18px;">
								<?php foreach ( $core_integrations as $label => $enabled ) : ?>
									<li>
										<?php echo esc_html($label); ?>:
										<?php if ( $enabled ) : ?>
											<span style="color:#46b450;"><?php esc_html_e('On', 'smart-cloudflare-turnstile'); ?></span>
										<?php else : ?>
											<span style="color:#757575;"><?php esc_html_e('Off', 'smart-cloudflare-turnstile'); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
							<p style="margin-top:.75em;">
								<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp-integrations&integration_tab=default_wordpress_forms')); ?>">
									<?php esc_html_e('Configure', 'smart-cloudflare-turnstile'); ?>
								</a>
							</p>
						</div>

						<?php if ( $wc_active ) : ?>
							<div class="twp-card" style="background:#fff;border:1px solid #e5e5e5;padding:16px;border-radius:6px;">
								<strong><?php esc_html_e('WooCommerce', 'smart-cloudflare-turnstile'); ?></strong>
								<p style="margin:.5em 0 0;">
									<?php
									// translators: %s: WooCommerce version number
									echo esc_html(sprintf(__('Active (v%s)', 'smart-cloudflare-turnstile'), $wc_version));
									?>
								</p>
								<ul style="margin:.5em 0 0;padding-left:18px;">
									<?php foreach ( $wc_integrations as $label => $enabled ) : ?>
										<li>
											<?php echo esc_html($label); ?>:
											<?php if ( $enabled ) : ?>
												<span style="color:#46b450;"><?php esc_html_e('On', 'smart-cloudflare-turnstile'); ?></span>
											<?php else : ?>
												<span style="color:#757575;"><?php esc_html_e('Off', 'smart-cloudflare-turnstile'); ?></span>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
								<p style="margin-top:.75em;">
									<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=turnstilewp-integrations&integration_tab=woocommerce')); ?>">
										<?php esc_html_e('Configure', 'smart-cloudflare-turnstile'); ?>
									</a>
								</p>
							</div>
						<?php endif; ?>

						<?php if ( ! empty($external_integrations) && is_array($external_integrations) ) : ?>
							<div class="twp-card" style="background:#fff;border:1px solid #e5e5e5;padding:16px;border-radius:6px;">
								<strong><?php esc_html_e('Other Integrations', 'smart-cloudflare-turnstile'); ?></strong>
								<ul style="margin:.5em 0 0;padding-left:18px;">
									<?php foreach ( $external_integrations as $integration ) : ?>
										<?php
										$label = isset($integration['label']) ? (string) $integration['label'] : '';
										$enabled = ! empty($integration['enabled']);
										$url = isset($integration['configure_url']) ? (string) $integration['configure_url'] : '';
										?>
										<?php if ( $label ) : ?>
											<li>
												<?php echo esc_html($label); ?>:
												<?php if ( $enabled ) : ?>
													<span style="color:#46b450;"><?php esc_html_e('On', 'smart-cloudflare-turnstile'); ?></span>
												<?php else : ?>
													<span style="color:#757575;"><?php esc_html_e('Off', 'smart-cloudflare-turnstile'); ?></span>
												<?php endif; ?>
												<?php if ( $url ) : ?>
													<a class="button button-small" style="margin-left:6px;" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Configure', 'smart-cloudflare-turnstile'); ?></a>
												<?php endif; ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

					</div>
				</div>

				<div class="turnstilewp-sub-section" style="margin-top:24px;">
					<h2><?php esc_html_e('Quick Start', 'smart-cloudflare-turnstile'); ?></h2>
					<table class="widefat striped" style="max-width:800px;">
						<tbody>
							<tr>
								<td>🔑 <?php esc_html_e('Add Site Key & Secret Key', 'smart-cloudflare-turnstile'); ?></td>
								<td><?php esc_html_e('Required for verification', 'smart-cloudflare-turnstile'); ?></td>
								<td><?php echo $has_keys ? '✅' : '⚠️'; ?></td>
							</tr>
							<tr>
								<td>🧩 <?php esc_html_e('Enable at least one integration', 'smart-cloudflare-turnstile'); ?></td>
								<td><?php esc_html_e('e.g., Login or Checkout', 'smart-cloudflare-turnstile'); ?></td>
								<td>
									<?php
									$any_core = in_array(true, array_values($core_integrations), true);
									$any_wc = in_array(true, array_values($wc_integrations), true);
									echo ( $any_core || $any_wc ) ? '✅' : '⚠️';
									?>
								</td>
							</tr>
							<tr>
								<td>🧠 <?php esc_html_e('Test a challenge', 'smart-cloudflare-turnstile'); ?></td>
								<td><?php esc_html_e('Verify success response', 'smart-cloudflare-turnstile'); ?></td>
								<td><?php echo $keys_verified ? '✅' : '⚠️'; ?></td>
							</tr>
							<tr>
								<td>📖 <?php esc_html_e('Review documentation', 'smart-cloudflare-turnstile'); ?></td>
								<td><?php esc_html_e('Learn about hooks and settings', 'smart-cloudflare-turnstile'); ?></td>
								<td><a href="https://github.com/mayankmajeji/turnstilewp" target="_blank" rel="noopener noreferrer">🔗</a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
