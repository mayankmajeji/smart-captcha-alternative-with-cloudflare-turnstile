<?php
/**
 * Integrations Page Template
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

$settings = new Settings();
$fields_structure = $settings->get_fields_structure();
$values = $settings->get_settings();

// Tabs for integrations
$has_form_plugins = apply_filters(
	'smartct_has_form_plugins',
	(defined('WPCF7_VERSION') || function_exists('wpcf7')
		|| defined('WPFORMS_VERSION') || class_exists('WPForms') || function_exists('wpforms')
		|| defined('NINJA_FORMS_VERSION') || class_exists('Ninja_Forms') || function_exists('Ninja_Forms')
		|| defined('FLUENTFORM') || function_exists('wpFluentForm') || class_exists('\\FluentForm\\App\\Modules\\Component\\Component')
		|| defined('FRM_VERSION') || class_exists('FrmAppHelper') || function_exists('load_formidable_forms')
		|| defined('FORMINATOR_VERSION') || class_exists('\\Forminator') || function_exists('forminator')
		|| function_exists('evf') || class_exists('EverestForms') || defined('EVF_PLUGIN_FILE')
		|| defined('SRFM_SLUG') || class_exists('\\SRFM\\Inc\\Form_Submit'))
);
$has_woocommerce = class_exists('WooCommerce');
$integration_tabs = array(
	'default_wordpress_forms' => __('Default WordPress Forms', 'smart-cloudflare-turnstile'),
);
if ($has_woocommerce) {
	$integration_tabs['woocommerce'] = __('WooCommerce', 'smart-cloudflare-turnstile');
}
if ($has_form_plugins) {
	$integration_tabs['form_plugins'] = __('Form Plugins', 'smart-cloudflare-turnstile');
}
// Only show "Others" if there are fields registered for it
if (! empty($fields_structure['others'])) {
	$integration_tabs['others'] = __('Others', 'smart-cloudflare-turnstile');
}
/**
 * Tab navigation security model:
 * - This is a read-only operation (no state changes, no data modification)
 * - Page access is protected by current_user_can('manage_options') check above (line 17)
 * - Input is sanitized with sanitize_key() and validated against $integration_tabs array
 * - Invalid tabs default to 'default_wordpress_forms'
 * - Nonces are not used to preserve bookmarkability and prevent UX issues with expired nonces
 * - This follows WordPress core's admin navigation patterns (e.g., wp-admin/?page=X)
 */
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation with capability check and input validation
$current_tab = isset($_GET['integration_tab']) ? sanitize_key(wp_unslash($_GET['integration_tab'])) : 'default_wordpress_forms';
if (! $has_form_plugins && $current_tab === 'form_plugins') {
	$current_tab = 'default_wordpress_forms';
}
if ($current_tab === 'others' && empty($fields_structure['others'])) {
	$current_tab = 'default_wordpress_forms';
}
if (! $has_woocommerce && $current_tab === 'woocommerce') {
	$current_tab = 'default_wordpress_forms';
}
?>
<div class="smartct-page smartct-page--integrations">
	<?php require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
	<div class="smartct-body">
		<?php
		$twp_title = get_admin_page_title();
		$twp_desc  = __('Browse available services and filter by type.', 'smart-cloudflare-turnstile');
		require SMARTCT_PLUGIN_DIR . 'includes/admin/templates/body-header.php';
		?>
		<?php settings_errors('smartct_settings_errors'); ?>

		<?php
		// Left filters
		$filter_tabs = array(
			'all'          => __('All', 'smart-cloudflare-turnstile'),
			'form_plugins' => __('Form Plugins', 'smart-cloudflare-turnstile'),
		'membership'   => __('Membership', 'smart-cloudflare-turnstile'),
		'community'    => __('Community', 'smart-cloudflare-turnstile'),
		'others'       => __('Others', 'smart-cloudflare-turnstile'),
	);
	/**
	 * Filter tab navigation security:
	 * - Read-only operation for UI filtering
	 * - Protected by capability check at line 17
	 * - Validated against $filter_tabs array below
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation with capability check and input validation
	$current_filter = isset($_GET['filter_tab']) ? sanitize_key(wp_unslash($_GET['filter_tab'])) : 'all';
		if (! array_key_exists($current_filter, $filter_tabs)) {
			$current_filter = 'all';
		}

		// Service catalog with plugin mapping
		if (! function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = function_exists('get_plugins') ? get_plugins() : array();
		$is_plugin_installed = function (string $plugin_file) use ($all_plugins): bool {
			return array_key_exists($plugin_file, $all_plugins);
		};
		$is_plugin_active_cb = function (string $plugin_file): bool {
			return function_exists('is_plugin_active') ? is_plugin_active($plugin_file) : false;
		};
		$settings_base = admin_url('admin.php?page=smartct-settings');
		$logo_base = trailingslashit(SMARTCT_PLUGIN_URL . 'assets/images/integrations');
		$catalog = array(
			array(
				'key'          => 'woocommerce',
				'label'        => 'WooCommerce',
				'cat'          => 'membership',
				'plugin_slug'  => 'woocommerce',
				'plugin_file'  => 'woocommerce/woocommerce.php',
				'desc'         => __('A robust eCommerce platform for creating and managing online stores within WordPress.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=woocommerce',
				'logo'         => $logo_base . 'woocommerce.png',
			),
			// WordPress form plugins (each as separate service card)
			array(
				'key'          => 'contact-form-7',
				'label'        => 'Contact Form 7',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'contact-form-7',
				'plugin_file'  => 'contact-form-7/wp-contact-form-7.php',
				'desc'         => __('A simple, flexible plugin for creating basic contact forms using shortcodes.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'contact-form-7.png',
			),
			array(
				'key'          => 'wpforms',
				'label'        => 'WPForms',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'wpforms-lite',
				'plugin_file'  => 'wpforms-lite/wpforms.php',
				'desc'         => __('A beginner-friendly drag-and-drop form builder designed for quick, professional forms.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'wpforms.png',
			),
			array(
				'key'          => 'ninja-forms',
				'label'        => 'Ninja Forms',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'ninja-forms',
				'plugin_file'  => 'ninja-forms/ninja-forms.php',
				'desc'         => __('A modular form builder that lets you create customizable forms with an intuitive interface.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'ninja-forms.png',
			),
			array(
				'key'          => 'formidable-forms',
				'label'        => 'Formidable Forms',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'formidable',
				'plugin_file'  => 'formidable/formidable.php',
				'desc'         => __('A powerful advanced form builder focused on data-driven forms and complex applications.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'formidable-forms.png',
			),
			array(
				'key'          => 'forminator',
				'label'        => 'Forminator',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'forminator',
				'plugin_file'  => 'forminator/forminator.php',
				'desc'         => __('A modern, interactive form builder offering polls, quizzes, and payment-ready forms.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'forminator-forms.png',
			),
			array(
				'key'          => 'fluentforms',
				'label'        => 'Fluent Forms',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'fluentform',
				'plugin_file'  => 'fluentform/fluentform.php',
				'desc'         => __('A fast, lightweight form plugin known for its smooth UI and extensive integrations.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'fluent-forms.png',
			),
			array(
				'key'          => 'everest-forms',
				'label'        => 'Everest Forms',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'everest-forms',
				'plugin_file'  => 'everest-forms/everest-forms.php',
				'desc'         => __('A clean drag-and-drop form builder built for creating simple to advanced forms easily.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'everest-forms.png',
			),
			array(
				'key'          => 'sureforms',
				'label'        => 'SureForms',
				'cat'          => 'form_plugins',
				'plugin_slug'  => 'sureforms',
				'plugin_file'  => 'sureforms/sureforms.php',
				'desc'         => __('A performance-focused form builder offering a fast, Gutenberg-native form-building experience.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=form_plugins',
				'logo'         => $logo_base . 'sure-forms.png',
			),
			array(
				'key'          => 'default_wp',
				'label'        => __('WordPress Forms', 'smart-cloudflare-turnstile'),
				'cat'          => 'others',
				'plugin_slug'  => '', // core
				'plugin_file'  => '',
				'desc'         => __('Basic built-in forms primarily used for comments, login, and registration.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=default_wordpress_forms',
				'logo'         => $logo_base . 'wordpress.png',
			),
			array(
				'key'          => 'bbpress',
				'label'        => 'bbPress',
				'cat'          => 'community',
				'plugin_slug'  => 'bbpress',
				'plugin_file'  => 'bbpress/bbpress.php',
				'desc'         => __('A lightweight forum plugin that adds discussion boards seamlessly to WordPress sites.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=community',
				'logo'         => $logo_base . 'bbpress.png',
			),
			array(
				'key'          => 'buddypress',
				'label'        => 'BuddyPress',
				'cat'          => 'community',
				'plugin_slug'  => 'buddypress',
				'plugin_file'  => 'buddypress/bp-loader.php',
				'desc'         => __('A social networking plugin that adds community features like profiles, activity streams, and groups.', 'smart-cloudflare-turnstile'),
				'settings_url' => $settings_base . '&settings_tab=community',
				'logo'         => $logo_base . 'buddypress.png',
			),
		);
		?>

		<div class="twp-2col">
			<aside class="twp-vtabs">
				<?php foreach ($filter_tabs as $fid => $flabel) : ?>
					<?php
					// Map to SVG icon partials
					$icon_partial = 'plugin-icon.php';
					if ($fid === 'all') {
						$icon_partial = 'all-icon.php';
					}
					if ($fid === 'form_plugins') {
						$icon_partial = 'plugin-icon.php';
					}
					if ($fid === 'membership') {
						$icon_partial = 'membership-icon.php';
					}
					if ($fid === 'community') {
						$icon_partial = 'community-icon.php';
					}
					if ($fid === 'others') {
						$icon_partial = 'others-icon.php';
					}
					$icon_path = SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
					?>
				<a class="twp-vtab <?php echo esc_attr( $current_filter === $fid ? 'is-active' : '' ); ?>"
					href="<?php echo esc_url(admin_url('admin.php?page=smartct-integrations&filter_tab=' . urlencode((string) $fid))); ?>">
						<span class="twp-vtab-icon">
							<?php
							if (file_exists($icon_path)) {
								include $icon_path;
							}
							?>
						</span>
						<span class="twp-vtab-text"><?php echo esc_html($flabel); ?></span>
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
					<div></div>
				</div>

				<div class="smartct-sub-section">
					<div class="smartct-integrations turnstilwp-integrations">
						<div class="smartct-field-group">
							<div class="smartct-field">
								<div class="inside">
									<div class="twp-services-grid">
										<?php
										foreach ($catalog as $svc) {
											if ($current_filter !== 'all' && $svc['cat'] !== $current_filter) {
												continue;
											}
											$installed = ! empty($svc['plugin_file']) ? $is_plugin_installed($svc['plugin_file']) : true;
											$active    = ! empty($svc['plugin_file']) ? $is_plugin_active_cb($svc['plugin_file']) : true;
											$btn_label = '';
											$btn_url   = '';
											$is_coming_soon = ! empty($svc['coming_soon']) && $svc['coming_soon'] === true;

											// Check if integration is marked as "Coming soon"
											if ($is_coming_soon) {
												$active = false; // Don't show "Active" badge for coming soon integrations
												$btn_label = __('Coming Soon', 'smart-cloudflare-turnstile');
												$btn_url = '#';
											} elseif (! $installed && ! empty($svc['plugin_slug'])) {
												$btn_label = __('Install', 'smart-cloudflare-turnstile');
												$btn_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $svc['plugin_slug']), 'install-plugin_' . $svc['plugin_slug']);
											} elseif ($installed && ! $active && ! empty($svc['plugin_file'])) {
												$btn_label = __('Activate', 'smart-cloudflare-turnstile');
												$btn_url = wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . $svc['plugin_file']), 'activate-plugin_' . $svc['plugin_file']);
											} else {
												$btn_label = __('Settings', 'smart-cloudflare-turnstile');
												$btn_url   = $svc['settings_url'];
											}
										?>
											<div class="twp-service-card">
												<div class="twp-service-content">
													<div class="twp-service-head">
														<div class="twp-service-id">
															<?php if (! empty($svc['logo'])) : ?>
																<img class="twp-service-logo" src="<?php echo esc_url($svc['logo']); ?>" alt="<?php echo esc_attr($svc['label']); ?>" />
															<?php endif; ?>
															<strong><?php echo esc_html($svc['label']); ?></strong>
														</div>
														<?php if ($active && !$is_coming_soon) : ?>
															<span class="twp-service-badge is-active"><?php esc_html_e('Active', 'smart-cloudflare-turnstile'); ?></span>
														<?php endif; ?>
													</div>
													<div class="smartct-content">
														<?php echo esc_html($svc['desc']); ?>
													</div>
												</div>
												<div class="smartct-buttons">
													<?php if ($is_coming_soon) : ?>
														<span class="button button-secondary coming-soon-btn">
															<?php echo esc_html($btn_label); ?>
														</span>
													<?php else : ?>
														<a href="<?php echo esc_url($btn_url); ?>" class="button <?php echo esc_attr( ($btn_label === 'Settings') ? 'button-primary' : 'button-secondary' ); ?>">
															<?php echo esc_html($btn_label); ?>
														</a>
													<?php endif; ?>
												</div>
											</div>
										<?php
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>