<?php

/*
 * Handle field output.
 *
 * @param string $output Contains the HTML to display the select2 field.
 * @param string $form_location
 * @param array $field
 * @param integer $user_id
 * @param array $field_check_errors
 * @param array $request_data
 * @return string Filtered output of the HTML to display the select2 field.
 */
function wppb_select2_display_handler($output, $form_location, $field, $user_id, $field_check_errors, $request_data)
{
    if ($field['field'] == 'Select2') {
        wp_enqueue_script('wppb_sl2_lib_js', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array('jquery'));
        wp_enqueue_style('wppb_sl2_lib_css', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css');

        wp_enqueue_script( 'wppb_sl2_js', WPPB_PLUGIN_URL.'front-end/default-fields/select2/select2.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb_sl2_css', WPPB_PLUGIN_URL.'front-end/default-fields/select2/select2.css', false, PROFILE_BUILDER_VERSION );


        $field['labels'] = apply_filters('wppb_select2_labels', $field['labels'], $form_location, $field, $user_id, $field_check_errors, $request_data);
        $field['options'] = apply_filters('wppb_select2_options', $field['options'], $form_location, $field, $user_id, $field_check_errors, $request_data);
        $arguments = apply_filters('wppb_select2_arguments', array(), $form_location, $field);

        $item_title = apply_filters('wppb_' . $form_location . '_select2_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
        $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);
        $item_option_labels = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_labels_translation', $field['labels'], true);

        $select2_labels = apply_filters('wppb_select2_labels_array', explode(',', $item_option_labels));
        $select2_values = apply_filters('wppb_select2_options_array', explode(',', $field['options']));

        $extra_attr = apply_filters('wppb_extra_attribute', '', $field, $form_location);

        if ($form_location != 'register')
            $input_value = ((wppb_user_meta_exists($user_id, $field['meta-name']) != null) ? esc_attr(stripslashes(get_user_meta($user_id, $field['meta-name'], true))) : $field['default-option']);
        else
            $input_value = (!empty($field['default-option']) ? esc_attr(trim($field['default-option'])) : '');

        $input_value = (isset($request_data[wppb_handle_meta_name($field['meta-name'])]) ? esc_attr(stripslashes(trim($request_data[wppb_handle_meta_name($field['meta-name'])]))) : $input_value);

        if ($form_location != 'back_end') {
            $error_mark = (($field['required'] == 'Yes') ? '<span class="wppb-required" title="' . wppb_required_field_error($field["field-title"]) . '">*</span>' : '');

            if (array_key_exists($field['id'], $field_check_errors))
                $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error($field["field-title"]) . '"/>';

            $output = '
				<label for="' . esc_attr($field['meta-name']) . '">' . $item_title . $error_mark . '</label>
				<select name="' . esc_attr($field['meta-name']) . '" id="' . esc_attr($field['meta-name']) . '" class="custom_field_select2" ' . $extra_attr . ' data-wppb-select2-arguments=\'' . json_encode($arguments) . '\'>';

            $extra_select_option = apply_filters('wppb_extra_select_option', '', $field, $item_title);
            if (!empty($extra_select_option)) {
                $output .= $extra_select_option;
            }

            foreach ($select2_values as $key => $value) {
                $output .= '<option value="' . esc_attr(trim($value)) . '" class="custom_field_select2_option ' . apply_filters('wppb_fields_extra_css_class', '', $field) . '" ';

                if ($input_value === trim($value))
                    $output .= ' selected';

                $output .= '>' . ((!isset($select2_labels[$key]) || !$select2_labels[$key]) ? esc_attr(trim($select2_values[$key])) : esc_attr(trim($select2_labels[$key]))) . '</option>';
            }

            $output .= '
				</select>';
            if (!empty($item_description))
                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

        } else {
            $item_title = (($field['required'] == 'Yes') ? $item_title . ' <span class="description">(' . __('required', 'profile-builder') . ')</span>' : $item_title);
            $output = '
				<table class="form-table wppb-select2">
					<tr>
						<th><label for="' . esc_attr($field['meta-name']) . '">' . $item_title . '</label></th>
						<td>
							<select name="' . esc_attr($field['meta-name']) . '" class="custom_field_select2" id="' . esc_attr($field['meta-name']) . '" ' . $extra_attr . ' data-wppb-select2-arguments=\'' . json_encode($arguments) . '\'>';

            foreach ($select2_values as $key => $value) {
                $output .= '<option value="' . esc_attr(trim($value)) . '" class="custom_field_select2_option" ';

                if ($input_value === trim($value))
                    $output .= ' selected';

                $output .= '>' . ((!isset($select2_labels[$key]) || !$select2_labels[$key]) ? esc_attr(trim($select2_values[$key])) : esc_attr(trim($select2_labels[$key]))) . '</option>';
            }

            $output .= '</select>
							<span class="description">' . $item_description . '</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters('wppb_' . $form_location . '_select2_custom_field_' . $field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value);
    }
}

add_filter('wppb_output_form_field_select2', 'wppb_select2_display_handler', 10, 6);
add_filter('wppb_admin_output_form_field_select2', 'wppb_select2_display_handler', 10, 6);

/*
 * Handle field save.
 *
 * @since 1.0.0
 *
 * @param array $field
 * @param integer $user_id
 * @param array $request_data
 * @param string $form_location
 */
function wppb_select2_save_value($field, $user_id, $request_data, $form_location)
{
    if ($field['field'] == 'Select2') {
        if (isset($request_data[wppb_handle_meta_name($field['meta-name'])]))
            update_user_meta($user_id, sanitize_text_field($field['meta-name']), sanitize_text_field($request_data[wppb_handle_meta_name($field['meta-name'])]));
    }
}

add_action('wppb_save_form_field', 'wppb_select2_save_value', 10, 4);
add_action('wppb_backend_save_form_field', 'wppb_select2_save_value', 10, 4);

/*
 * Handle field validation.
 *
 * @since 1.0.0
 *
 * @param string $message
 * @param array $field
 * @param array $request_data
 * @param $form_location
 * @return string Message to display on field validation
 */
function wppb_select2_check_value($message, $field, $request_data, $form_location)
{
    if ($field['field'] == 'Select2') {
        if ($field['required'] == 'Yes') {
            if ((isset($request_data[wppb_handle_meta_name($field['meta-name'])]) && (trim($request_data[wppb_handle_meta_name($field['meta-name'])]) == '')) || !isset($request_data[wppb_handle_meta_name($field['meta-name'])])) {
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}

add_filter('wppb_check_form_field_select2', 'wppb_select2_check_value', 10, 4);


