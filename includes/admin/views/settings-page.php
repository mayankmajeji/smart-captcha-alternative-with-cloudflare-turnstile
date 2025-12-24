<?php

/**
 * Centralized Settings Page Template
 *
 * @package SmartCT
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

// Check user permissions - only administrators can access plugin settings
if (! current_user_can('manage_options')) {
	wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'smart-cloudflare-turnstile'));
}

use SmartCT\Settings;

require_once SMARTCT_PLUGIN_DIR . 'includes/settings/field-renderer.php';

$settings = new Settings();
$fields_structure = $settings->get_fields_structure();
$values = $settings->get_settings();

// Build tab list from fields structure
$tabs = array();
foreach ($fields_structure as $tab_id => $sections) {
	$tabs[$tab_id] = ucwords(str_replace('_', ' ', $tab_id));
}
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation doesn't require nonce verification
$current_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : array_key_first($tabs);

$site_key = $values['site_key'] ?? '';
$secret_key = $values['secret_key'] ?? '';
$keys_verified = get_option('smartct_keys_verified', 0);
?>
<?php require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
<div class="smartct-body">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<div class="smartct-info">
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

	<?php settings_errors('smartct_settings_errors'); ?>

	<form method="post" action="options.php">
		<?php settings_fields('smartct_settings'); ?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ($tabs as $tab_id => $tab_label) : ?>
				<a href="?page=smartct-settings&tab=<?php echo esc_attr($tab_id); ?>"
					class="nav-tab 
					<?php
					if ($current_tab === $tab_id) {
						echo 'nav-tab-active';
					}
					?>
">
					<?php echo esc_html($tab_label); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<div class="smartct-tabs">
			<?php if (isset($fields_structure[$current_tab])) : ?>
				<?php foreach ($fields_structure[$current_tab] as $section_id => $fields) : ?>
					<?php
					// For Turnstile tab, output the widget preview above the API key fields
					if ($current_tab === 'turnstile_settings' && $section_id === 'api_settings') :
						if (! empty($site_key) && ! empty($secret_key)) :
					?>
							<div class="smartct-preview-box">
								<?php if (! $keys_verified) : ?>
									<div class="twp-status-indicator-box unverified">
										<strong style="color:#d00;"><?php esc_html_e('API keys have been updated. Please test the Turnstile API response below.', 'smart-cloudflare-turnstile'); ?></strong><br>
										<span style="color:#666;"><?php esc_html_e('Turnstile will not be added to any forms until the test is successfully complete.', 'smart-cloudflare-turnstile'); ?></span>
										<div style="margin:1.5em 0;">
											<div id="cf-turnstile-preview"></div>
										</div>
										<button type="button" class="button button-primary" id="smartct-verify-keys"><?php esc_html_e('Verify Keys', 'smart-cloudflare-turnstile'); ?></button>
										<span id="smartct-verify-spinner" class="spinner" style="float:none;vertical-align:middle;"></span>
										<div id="smartct-verify-message" style="margin-top:1em;"></div>
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
					<div class="smartct-section" id="section-<?php echo esc_attr($section_id); ?>">
						<h3><?php echo esc_html(ucwords(str_replace('_', ' ', $section_id))); ?></h3>
						<div class="smartct-sub-section">
							<?php foreach ($fields as $field) : ?>
								<?php smartct_render_setting_field($field, $values[$field['field_id']] ?? $field['default'] ?? ''); ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<?php submit_button(); ?>
	</form>
</div>
<?php
// Accordion functionality is enqueued via admin-settings-accordion.js in class-init.php
?>