<?php
/**
 * Main Settings Page Template
 *
 * @package SmartCT
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user permissions - only administrators can access plugin settings
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-cloudflare-turnstile' ) );
}

use SmartCT\Settings;

require_once SMARTCT_PLUGIN_DIR . 'includes/settings/field-renderer.php';

$settings = new Settings();
$fields_structure = $settings->get_fields_structure();
$values = $settings->get_settings();

// Only render fields from the 'turnstile_settings' tab
$fields = $fields_structure['turnstile_settings'] ?? array();

$site_key = $settings->get_option('smartct_site_key', '');
$secret_key = $settings->get_option('smartct_secret_key', '');
$keys_verified = get_option('smartct_keys_verified', 0);
?>
<div class="smartct-page smartct-page--settings">
	<?php require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
	<div class="smartct-body">
		<?php
		$twp_title = get_admin_page_title();
		$twp_desc  = __('Configure Turnstile settings and integrations.', 'smart-cloudflare-turnstile');
		require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
		?>
		<?php settings_errors('smartct_settings_errors'); ?>
		<?php
		// Build left tabs: Settings first, then integrations conditionally
		$settings_tabs = array(
			'turnstile_settings'      => __('Settings', 'smart-cloudflare-turnstile'),
			'default_wordpress_forms' => __('WordPress Forms', 'smart-cloudflare-turnstile'),
		);
		$has_form_plugins = apply_filters(
			'smartct_has_form_plugins',
			( defined('WPCF7_VERSION') || function_exists('wpcf7')
				|| defined('WPFORMS_VERSION') || class_exists('WPForms') || function_exists('wpforms')
				|| defined('NINJA_FORMS_VERSION') || class_exists('Ninja_Forms') || function_exists('Ninja_Forms')
				|| defined('FLUENTFORM') || function_exists('wpFluentForm') || class_exists('\\FluentForm\\App\\Modules\\Component\\Component')
				|| defined('FRM_VERSION') || class_exists('FrmAppHelper') || function_exists('load_formidable_forms')
				|| defined('FORMINATOR_VERSION') || class_exists('\\Forminator') || function_exists('forminator')
				|| function_exists('evf') || class_exists('EverestForms') || defined('EVF_PLUGIN_FILE')
				|| defined('SRFM_SLUG') || class_exists('\\SRFM\\Inc\\Form_Submit')
				|| function_exists('kadence_blocks') || defined('KADENCE_BLOCKS_VERSION') )
		);
		$has_woocommerce = class_exists('WooCommerce');
		if ( $has_woocommerce ) {
			$settings_tabs['woocommerce'] = __('WooCommerce', 'smart-cloudflare-turnstile');
		}
		// Form Plugins tab appears when there are form plugin fields registered OR when form plugins are detected
		if ( $has_form_plugins || ! empty( $fields_structure['form_plugins'] ) ) {
			$settings_tabs['form_plugins'] = __('Form Plugins', 'smart-cloudflare-turnstile');
		}

		// Community tab (e.g., bbPress) appears when there are community fields registered
		if ( ! empty( $fields_structure['community'] ) ) {
			$settings_tabs['community'] = __('Community', 'smart-cloudflare-turnstile');
		}

		// Newsletters tab (e.g., MailPoet) appears when there are newsletter fields registered
		if ( ! empty( $fields_structure['newsletters'] ) ) {
			$settings_tabs['newsletters'] = __('Newsletters', 'smart-cloudflare-turnstile');
		}

		if ( ! empty($fields_structure['others']) ) {
			$settings_tabs['others'] = __('Others', 'smart-cloudflare-turnstile');
		}
	/**
	 * Settings tab navigation security model:
	 * - Read-only display operation (actual settings changes go through options.php with nonces)
	 * - Protected by current_user_can('manage_options') at line 17
	 * - Input validated against $settings_tabs array below
	 * - Nonces not used to maintain bookmarkable URLs
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation with capability check and input validation
	$current_settings_tab = isset($_GET['settings_tab']) ? sanitize_key(wp_unslash($_GET['settings_tab'])) : 'turnstile_settings';
		if ( ! array_key_exists($current_settings_tab, $settings_tabs) ) {
			$current_settings_tab = 'turnstile_settings';
		}
		?>
		<div class="twp-2col">
			<aside class="twp-vtabs">
				<?php foreach ( $settings_tabs as $sid => $label ) : ?>
					<?php
					// Map tab id to icon partial
					$icon_partial = 'plugin-icon.php';
					if ( $sid === 'turnstile_settings' ) {
						$icon_partial = 'settings-icon.php';
					} elseif ( $sid === 'default_wordpress_forms' ) {
						$icon_partial = 'wordpress-icon.php';
					} elseif ( $sid === 'woocommerce' ) {
						$icon_partial = 'cart-icon.php';
					} elseif ( $sid === 'form_plugins' ) {
						$icon_partial = 'plugin-icon.php';
					} elseif ( $sid === 'community' ) {
						$icon_partial = 'community-icon.php';
					} elseif ( $sid === 'newsletters' ) {
						$icon_partial = 'plugin-icon.php';
					} elseif ( $sid === 'others' ) {
						$icon_partial = 'plugin-icon.php';
					}
					$icon_path = SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
					?>
				<a class="twp-vtab <?php echo esc_attr( $current_settings_tab === $sid ? 'is-active' : '' ); ?>"
					href="<?php echo esc_url(admin_url('admin.php?page=smartct-settings&settings_tab=' . urlencode( (string) $sid))); ?>">
						<span class="twp-vtab-icon">
							<?php
							if ( file_exists($icon_path) ) {
								include $icon_path;
							}
							?>
						</span>
						<span class="twp-vtab-text"><?php echo esc_html($label); ?></span>
					</a>
				<?php endforeach; ?>
			</aside>
			<section class="twp-2col-content">
				<div class="twp-toolbar">
					<button type="button" class="twp-collapse-btn" data-twp-toggle="vtabs">
						<span class="twp-collapse-icon icon-open" aria-hidden="true">
							<?php require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-close-icon.php'; ?>
						</span>
						<span class="twp-collapse-icon icon-close" aria-hidden="true" style="display:none;">
							<?php require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/panel-open-icon.php'; ?>
						</span>
					</button>
					<button type="submit" class="button button-primary" form="smartct-settings-form">
						<?php esc_html_e('Save Changes', 'smart-cloudflare-turnstile'); ?>
					</button>
				</div>
				<form id="smartct-settings-form" method="post" action="options.php">
					<?php settings_fields('smartct_settings'); ?>
					<div class="smartct-section" id="section-<?php echo esc_attr($current_settings_tab); ?>">
						<div class="smartct-sub-section">
							<?php if ( $current_settings_tab === 'turnstile_settings' && ! empty($site_key) && ! empty($secret_key) ) : ?>
								<div class="smartct-preview-box">
									<?php if ( ! $keys_verified ) : ?>
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
							<?php endif; ?>
							<?php
							// Render only the selected tab sections
							$selected_tab = $fields_structure[ $current_settings_tab ] ?? array();
							// Maintain special handling for API settings block from earlier
							$has_constants = defined('SMARTCT_SITE_KEY') || defined('SMARTCT_SECRET_KEY');
							// Special handling for API settings group only when on Settings tab
							if ( $current_settings_tab === 'turnstile_settings' && $has_constants ) {
								$section_fields_filtered = array();
								foreach ( $selected_tab as $section_id => $section_fields_arr ) {
									foreach ( $section_fields_arr as $k => $field ) {
										if ( ! in_array(( $field['field_id'] ?? '' ), array( 'smartct_site_key', 'smartct_secret_key' ), true) ) {
											$section_fields_filtered[ $k ] = $field;
										}
									}
								}
								$section_fields_filtered[] = array(
									'field_id' => 'smartct_wpconfig_notice',
									'type' => 'content',
									'content' => '<p style="margin:0; font-weight:600; color:#1e8c1e;"><span class="dashicons dashicons-lock"></span> ' . esc_html__('Using keys defined in wp-config.php. Be sure to test your forms to confirm they are working.', 'smart-cloudflare-turnstile') . '</p>',
									'group' => 'api_keys',
								);
								$const_site = defined('SMARTCT_SITE_KEY') ? SMARTCT_SITE_KEY : '';
								$const_secret = defined('SMARTCT_SECRET_KEY') ? SMARTCT_SECRET_KEY : '';
								$masked_secret = $const_secret !== '' ? substr($const_secret, 0, 10) . str_repeat('*', max(0, strlen($const_secret) - 10)) : '';
								$constants_table = '<div><table class="form-table" role="presentation" style="margin-top:0;"><tbody>'
									. '<tr valign="top"><th scope="row">' . esc_html__('Site Key', 'smart-cloudflare-turnstile') . '</th><td><p>' . esc_html($const_site) . '</p><input type="hidden" name="smartct_site_key" value=""></td></tr>'
									. '<tr valign="top"><th scope="row">' . esc_html__('Secret Key', 'smart-cloudflare-turnstile') . '</th><td><p>' . esc_html($masked_secret) . '</p><input type="hidden" name="smartct_secret_key" value=""></td></tr>'
									. '</tbody></table></div>';
								$section_fields_filtered[] = array(
									'field_id' => 'smartct_wpconfig_constants_table',
									'type' => 'content',
									'content' => $constants_table,
									'group' => 'api_keys',
								);
								smartct_render_setting_fields_grouped(array_values($section_fields_filtered), $values);
							} else {
								foreach ( $selected_tab as $section_id => $section_fields_arr ) {
									smartct_render_setting_fields_grouped(array_values($section_fields_arr), $values);
								}
							}
							?>
						</div>
					</div>
					<div class="twp-toolbar twp-toolbar-footer">
						<div></div>
						<?php submit_button(__('Save Changes', 'smart-cloudflare-turnstile'), 'primary', 'submit', false); ?>
					</div>
				</form>
			</section>
		</div>
	</div>
</div>
