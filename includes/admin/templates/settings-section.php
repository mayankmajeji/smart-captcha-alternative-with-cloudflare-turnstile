<?php
/**
 * Settings Section Template
 *
 * @package SmartCT
 * 
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * Template variables are scoped to this file and do not pollute the global namespace.
 */

if ( ! defined('WPINC') ) {
	die;
}

use SmartCT\Settings;

require_once SMARTCT_PLUGIN_DIR . 'includes/settings/field-renderer.php';

$tab = $tab ?? '';
$settings = $settings ?? new Settings();
$fields_structure = $fields_structure ?? $settings->get_fields_structure();
$values = $values ?? $settings->get_settings();
$fields = $fields_structure[ $tab ] ?? array();

?>
<div class="smartct-admin-layout">
	<div class="smartct-section" id="section-<?php echo esc_attr($tab); ?>">
		<div class="smartct-sub-section">
			<?php foreach ( $fields as $section_id => $section_fields ) : ?>
				<?php smartct_render_setting_fields_grouped(array_values($section_fields), $values); ?>
			<?php endforeach; ?>
		</div>
		<div class="smartct-actions" style="margin-top:16px;">
			<?php submit_button(); ?>
		</div>
	</div>
	<aside class="smartct-admin-layout-sidebar">
		<?php require_once SMARTCT_PLUGIN_DIR . 'includes/admin/templates/sidebar.php'; ?>
	</aside>
</div>
