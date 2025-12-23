<?php
/**
 * Centralized Settings Page Template
 *
 * @package TurnstileWP
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

// If this file is called directly, abort.
if ( ! defined('WPINC') ) {
	die;
}

use TurnstileWP\Settings;

require_once dirname(__DIR__, 2) . '/includes/settings/field-renderer.php';

$settings = new Settings();
$fields_structure = $settings->get_fields_structure();
$values = $settings->get_settings();

// Build tab list from fields structure
$tabs = array();
foreach ( $fields_structure as $tab_id => $sections ) {
	$tabs[ $tab_id ] = ucwords(str_replace('_', ' ', $tab_id));
}
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation doesn't require nonce verification
$current_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : array_key_first($tabs);

$site_key = $values['site_key'] ?? '';
$secret_key = $values['secret_key'] ?? '';
$keys_verified = get_option('turnstilewp_keys_verified', 0);
?>
<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
<div class="turnstilewp-body">
<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<div class="turnstilewp-info">
		<h2><?php esc_html_e('About Smart Cloudflare Turnstile', 'smart-cloudflare-turnstile'); ?></h2>
		<p>
			<?php esc_html_e('Smart Cloudflare Turnstile is a lightweight and privacy-first integration of Cloudflare Turnstile with WordPress forms.', 'smart-cloudflare-turnstile'); ?>
		</p>
		<p>
			<?php
			printf(
				/* translators: %s: Cloudflare Turnstile URL */
				esc_html__('To get started, you need to sign up for a Cloudflare account and get your site key and secret key from the %s.', 'smart-cloudflare-turnstile'),
				'<a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener noreferrer">' . esc_html__('Cloudflare Turnstile dashboard', 'smart-cloudflare-turnstile') . '</a>'
			);
			?>
		</p>
	</div>

	<?php settings_errors('turnstilewp_settings_errors'); ?>

	<form method="post" action="options.php">
		<?php settings_fields('turnstilewp_settings'); ?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
				<a href="?page=turnstilewp&tab=<?php echo esc_attr($tab_id); ?>"
					class="nav-tab 
					<?php
					if ( $current_tab === $tab_id ) {
echo 'nav-tab-active';}
?>
">
					<?php echo esc_html($tab_label); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<div class="turnstilewp-tabs">
			<?php if ( isset($fields_structure[ $current_tab ]) ) : ?>
				<?php foreach ( $fields_structure[ $current_tab ] as $section_id => $fields ) : ?>
					<?php
					// For Turnstile tab, output the widget preview above the API key fields
					if ( $current_tab === 'turnstile_settings' && $section_id === 'api_settings' ) :
						if ( ! empty($site_key) && ! empty($secret_key) ) :
					?>
							<div class="turnstilewp-preview-box">
								<?php if ( ! $keys_verified ) : ?>
									<div class="twp-status-indicator-box unverified">
										<strong style="color:#d00;"><?php esc_html_e('API keys have been updated. Please test the Turnstile API response below.', 'smart-cloudflare-turnstile'); ?></strong><br>
										<span style="color:#666;"><?php esc_html_e('Turnstile will not be added to any forms until the test is successfully complete.', 'smart-cloudflare-turnstile'); ?></span>
										<div style="margin:1.5em 0;">
											<div id="cf-turnstile-preview"></div>
										</div>
										<button type="button" class="button button-primary" id="turnstilewp-verify-keys"><?php esc_html_e('Verify Keys', 'smart-cloudflare-turnstile'); ?></button>
										<span id="turnstilewp-verify-spinner" class="spinner" style="float:none;vertical-align:middle;"></span>
										<div id="turnstilewp-verify-message" style="margin-top:1em;"></div>
									</div>
								<?php else : ?>
									<div class="twp-status-indicator-box verified">
										<span class="twp-status-indicator" style="color:#46b450;font-weight:bold;"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Success! Turnstile is working correctly with your API keys.', 'smart-cloudflare-turnstile'); ?></span>
									</div>
								<?php endif; ?>
							</div>
					<?php
					endif;
					endif;
					?>
					<div class="turnstilewp-section" id="section-<?php echo esc_attr($section_id); ?>">
						<h3><?php echo esc_html(ucwords(str_replace('_', ' ', $section_id))); ?></h3>
						<div class="turnstilewp-sub-section">
							<?php foreach ( $fields as $field ) : ?>
								<?php render_setting_field($field, $values[ $field['field_id'] ] ?? $field['default'] ?? ''); ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

	<?php submit_button(); ?>
	</form>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('.turnstilewp-accordion-header').forEach(function(header) {
			header.addEventListener('click', function() {
				var item = this.closest('.turnstilewp-accordion-item');
				var open = item.classList.contains('open');
				document.querySelectorAll('.turnstilewp-accordion-item').forEach(function(i) {
					i.classList.remove('open');
					i.querySelector('.turnstilewp-accordion-arrow').innerHTML = '&#9660;';
				});
				if (!open) {
					item.classList.add('open');
					item.querySelector('.turnstilewp-accordion-arrow').innerHTML = '&#9650;';
				}
			});
		});
	});
</script>
