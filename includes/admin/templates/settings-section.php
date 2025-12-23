<?php
/**
 * Settings Section Template
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

require_once dirname(__DIR__, 2) . '/settings/field-renderer.php';

$tab = $tab ?? '';
$settings = $settings ?? new Settings();
$fields_structure = $fields_structure ?? $settings->get_fields_structure();
$values = $values ?? $settings->get_settings();
$fields = $fields_structure[ $tab ] ?? array();

?>
<div class="turnstilewp-admin-layout">
	<div class="turnstilewp-section" id="section-<?php echo esc_attr($tab); ?>">
		<div class="turnstilewp-sub-section">
			<?php foreach ( $fields as $section_id => $section_fields ) : ?>
				<?php render_setting_fields_grouped(array_values($section_fields), $values); ?>
			<?php endforeach; ?>
		</div>
		<div class="turnstilewp-actions" style="margin-top:16px;">
			<?php submit_button(); ?>
		</div>
	</div>
	<aside class="turnstilewp-admin-layout-sidebar">
		<?php require_once __DIR__ . '/sidebar.php'; ?>
	</aside>
</div>
