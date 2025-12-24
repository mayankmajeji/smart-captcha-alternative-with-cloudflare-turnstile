<?php

/**
 * Field Renderer Helper
 *
 * @package SmartCT
 */

if (! function_exists('smartct_render_setting_field')) {
	function smartct_render_setting_field($field, $value)
	{
		$type = $field['type'] ?? 'text';
		// Special case: content field type should be rendered as a standalone block
		if ($type === 'content') {
			if (! empty($field['content'])) {
				echo '<div class="smartct-content-field">' . wp_kses_post($field['content']) . '</div>';
			}
			return;
		}
		$id = esc_attr($field['field_id']);
		$label = esc_html($field['label'] ?? '');
		$desc = esc_html($field['description'] ?? '');
		$options = $field['options'] ?? array();
		$attrs = $field['attrs'] ?? array();
		$attr_str = '';
		foreach ($attrs as $k => $v) {
			$attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
		}
		echo '<div class="smartct-field smartct-field-type-' . esc_attr($type) . '">';
		// Label column
		echo '<div class="smartct-label">';
		// For all fields, show the label in the label column
		echo '<label for="' . esc_attr($id) . '"><strong>' . esc_html($label) . '</strong></label>';
		echo '</div>';
		// Option column
		echo '<div class="smartct-option">';
		echo '<div class="smartct-input">';
		switch ($type) {
			case 'text':
			case 'email':
			case 'url':
			case 'number':
				printf(
					'<input type="%s" id="%s" name="smartct_settings[%s]" value="%s" class="regular-text"%s />',
					esc_attr($type),
					esc_attr($id),
					esc_attr($id),
					esc_attr($value),
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr_str is already escaped HTML attributes
					$attr_str
				);
				break;
			case 'password':
				printf(
					'<input type="password" id="%s" name="smartct_settings[%s]" value="%s" class="regular-text"%s />',
					esc_attr($id),
					esc_attr($id),
					esc_attr($value),
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr_str is already escaped HTML attributes
					$attr_str
				);
				break;
			case 'checkbox':
				// Add a hidden field to ensure unchecked values are saved
				printf(
					'<input type="hidden" name="smartct_settings[%s]" value="0" />',
					esc_attr($id)
				);
				echo '<label class="smartct-toggle">';
				printf(
					'<input type="checkbox" id="%s" name="smartct_settings[%s]" value="1" %s%s />',
					esc_attr($id),
					esc_attr($id),
					checked($value, true, false),
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr_str is already escaped HTML attributes
					$attr_str
				);
				echo '<span class="smartct-toggle-slider"></span></label> ';
				break;
			case 'select':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr_str is already escaped HTML attributes
				echo '<select id="' . esc_attr($id) . '" name="smartct_settings[' . esc_attr($id) . ']"' . $attr_str . '>';
				foreach ($options as $opt_value => $opt_label) {
					printf('<option value="%s" %s>%s</option>', esc_attr($opt_value), selected($value, $opt_value, false), esc_html($opt_label));
				}
				echo '</select>';
				break;
			case 'textarea':
				printf(
					'<textarea id="%s" name="smartct_settings[%s]" rows="4" cols="50"%s>%s</textarea>',
					esc_attr($id),
					esc_attr($id),
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr_str is already escaped HTML attributes
					$attr_str,
					esc_textarea($value)
				);
				break;
				// Add more field types as needed
		}
		echo '</div>'; // .smartct-input
		if (! empty($desc)) {
			// Allow for multiple lines/paragraphs in description
			echo '<div class="smartct-description">' . wp_kses_post(wpautop($desc)) . '</div>';
		}
		// Optional note support
		if (! empty($field['note'])) {
			echo '<div class="smartct-note"><p>' . esc_html($field['note']) . '</p></div>';
		}
		echo '</div>';
		echo '</div>';
	}
}

if (! function_exists('smartct_render_setting_fields_grouped')) {
	function smartct_render_setting_fields_grouped($fields, $values)
	{
		$last_group = null;
		$open_group = false;
		$fields = array_values($fields); // Ensure numeric keys
		$count = count($fields);
		foreach ($fields as $idx => $field) {
			$group = $field['group'] ?? null;
			$next_field = $fields[$idx + 1] ?? null;
			$next_group = $next_field['group'] ?? null;
			if ($group && $group !== $last_group) {
				if ($open_group) {
					echo '</div>';
					$open_group = false;
				}
				// Group wrapper
				$group_classes = 'smartct-field-group';
				// For the specific field smartct_show_for_logged_in, mirror checkbox row classes
				if (! empty($field['field_id']) && $field['field_id'] === 'smartct_show_for_logged_in') {
					$group_classes .= ' smartct-field-group smartct-field smartct-field-type-checkbox';
				}
				echo '<div class="' . esc_attr($group_classes) . '">';
				// Output group title if present on the first field of the group
				if (! empty($field['group_title'])) {
					echo '<div class="smartct-group-title">' . wp_kses_post($field['group_title']) . '</div>';
				} elseif (! empty($field['field_id']) && $field['field_id'] === 'smartct_show_for_logged_in') {
					// Ensure a group-title element exists for this checkbox so styles apply uniformly
					echo '<div class="smartct-group-title"></div>';
				}
				$open_group = true;
			}
			// Special-case wrapper for standalone checkbox fields to sync styles with grouped rows
			if (empty($group) && ! empty($field['field_id']) && in_array($field['field_id'], array('smartct_show_for_logged_in', 'smartct_guest_only'), true)) {
				echo '<div class="smartct-field-group smartct-field smartct-field-type-checkbox">';
				echo '<div class="smartct-group-title"></div>';
				smartct_render_setting_field($field, $values[$field['field_id']] ?? $field['default'] ?? '');
				echo '</div>';
			} else {
				smartct_render_setting_field($field, $values[$field['field_id']] ?? $field['default'] ?? '');
			}
			if ($group && $group !== $next_group) {
				echo '</div>';
				$open_group = false;
			}
			$last_group = $group;
		}
		if ($open_group) {
			echo '</div>';
		}
	}
}
