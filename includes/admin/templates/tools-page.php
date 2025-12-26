<?php
/**
 * Tools Page Template
 *
 * @package SmartCT
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if ( ! defined('WPINC') ) {
	die;
}

// Check user permissions - only administrators can access plugin tools
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-cloudflare-turnstile' ) );
}

$tabs = array(
	'import' => __('Import Settings', 'smart-cloudflare-turnstile'),
	'export' => __('Export Settings', 'smart-cloudflare-turnstile'),
	'reset'  => __('Reset Settings', 'smart-cloudflare-turnstile'),
);
/**
 * Tools tab navigation security:
 * - Read-only display (actual actions require nonces in their forms)
 * - Protected by current_user_can('manage_options') at line 16
 * - Validated against $tabs array above
 */
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation with capability check and input validation
$current_tab = isset($_GET['tools_tab']) ? sanitize_key(wp_unslash($_GET['tools_tab'])) : 'import';
if ( ! array_key_exists($current_tab, $tabs) ) {
	$current_tab = 'import';
}
?>
<div class="smartct-page smartct-page--tools">
	<?php require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/header.php'; ?>
	<div class="smartct-body">
		<div class="twp-body-header">
			<div class="twp-bh-left">
				<img src="<?php echo esc_url(SMARTCT_PLUGIN_URL . 'assets/images/favicon.svg'); ?>" alt="Smart Cloudflare Turnstile" />
			</div>
			<div class="twp-bh-right">
				<h1><?php esc_html_e('Tools', 'smart-cloudflare-turnstile'); ?></h1>
				<p class="twp-page-desc"><?php esc_html_e('Import, export or reset plugin settings.', 'smart-cloudflare-turnstile'); ?></p>
			</div>
		</div>
		<?php settings_errors('smartct_tools'); ?>

		<div class="twp-2col">
			<aside class="twp-vtabs">
				<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
					<?php
					// Map to SVG partials
					$icon_partial = 'plugin-icon.php';
					if ( $tab_id === 'import' ) {
						$icon_partial = 'import-icon.php';
					} elseif ( $tab_id === 'export' ) {
						$icon_partial = 'export-icon.php';
					} elseif ( $tab_id === 'reset' ) {
						$icon_partial = 'reset-icon.php';
					}
					$icon_path = SMARTCT_PLUGIN_DIR . 'includes/admin/templates/icons/' . $icon_partial;
					?>
				<a class="twp-vtab <?php echo esc_attr( $current_tab === $tab_id ? 'is-active' : '' ); ?>"
					href="<?php echo esc_url(admin_url('admin.php?page=smartct-tools&tools_tab=' . urlencode( (string) $tab_id))); ?>">
						<span class="twp-vtab-icon">
							<?php
							if ( file_exists($icon_path) ) {
								include $icon_path;
							}
							?>
						</span>
						<span class="twp-vtab-text"><?php echo esc_html($tab_label); ?></span>
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

				<div class="smartct-section" id="section-tools">
					<div class="smartct-sub-section">
						<?php if ( $current_tab === 'import' ) : ?>
							<div class="smartct-field-group">
								<div class="smartct-group-title">
									<h2><?php esc_html_e('Import Settings', 'smart-cloudflare-turnstile'); ?></h2>
								</div>
								<div class="smartct-field">
									<div class="smartct-label">
										<label for="import_file">
											<strong><?php esc_html_e('Import Settings', 'smart-cloudflare-turnstile'); ?></strong>
										</label>
									</div>
									<div class="smartct-option">
										<form method="post" enctype="multipart/form-data">
											<?php wp_nonce_field('smartct_tools_import', 'smartct_tools_nonce'); ?>
											<input type="hidden" name="smartct_tools_action" value="import">
											<input type="file" name="import_file" accept="application/json" required>
											<div style="margin-top:8px;">
												<button type="submit" class="button button-primary" style="display:block;"><?php esc_html_e('Import', 'smart-cloudflare-turnstile'); ?></button>
											</div>
										</form>
									</div>
								</div>
							</div>
						<?php elseif ( $current_tab === 'export' ) : ?>
							<div class="smartct-field-group">
								<div class="smartct-group-title">
									<h2><?php esc_html_e('Export Settings', 'smart-cloudflare-turnstile'); ?></h2>
								</div>
								<div class="smartct-field">
									<div class="smartct-label">
										<label><strong><?php esc_html_e('Export Settings', 'smart-cloudflare-turnstile'); ?></strong></label>
									</div>
									<div class="smartct-option">
										<a href="
										<?php
										echo esc_url(
														add_query_arg(array(
															'action' => 'smartct_export_settings',
															'_wpnonce' => wp_create_nonce('smartct_tools_export'),
														), admin_url('admin-ajax.php'))
													);
													?>
													" class="button button-primary" style="display:inline-block;"><?php esc_html_e('Export', 'smart-cloudflare-turnstile'); ?></a>
									</div>
								</div>
							</div>
						<?php elseif ( $current_tab === 'reset' ) : ?>
							<div class="smartct-field-group">
								<div class="smartct-group-title">
									<h2><?php esc_html_e('Reset Settings', 'smart-cloudflare-turnstile'); ?></h2>
								</div>
								<div class="smartct-field">
									<div class="smartct-label">
										<label><strong><?php esc_html_e('Reset Settings', 'smart-cloudflare-turnstile'); ?></strong></label>
									</div>
									<div class="smartct-option">
										<form method="post" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to reset all settings to defaults?', 'smart-cloudflare-turnstile')); ?>');">
											<?php wp_nonce_field('smartct_tools_reset', 'smartct_tools_nonce'); ?>
											<input type="hidden" name="smartct_tools_action" value="reset">
											<button type="submit" class="button button-secondary"><?php esc_html_e('Reset All Settings', 'smart-cloudflare-turnstile'); ?></button>
										</form>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>
