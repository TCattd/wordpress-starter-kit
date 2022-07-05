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
function wppb_select2_multiple_display_handler($output, $form_location, $field, $user_id, $field_check_errors, $request_data)
{
    if ($field['field'] == 'Select2 (Multiple)') {
        wp_enqueue_script('wppb_sl2_lib_js', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array('jquery'));
        wp_enqueue_style('wppb_sl2_lib_css', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css');

        wp_enqueue_script( 'wppb_sl2_js', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/select2/select2.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb_sl2_css', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/select2/select2.css', false, PROFILE_BUILDER_VERSION );

        $arguments = array();
        $arguments['maximumSelectionLength'] = 0;
        $arguments['maximumSelectionSize'] = 0; // Backwards compatibility with Select2 v.3.5.3, loaded by WooCommerce

        if (isset($field['select2-multiple-tags']) && ('yes' == $field['select2-multiple-tags'])) {
            $arguments['tags'] = true;
            add_filter('wppb_select2_multiple_options', 'wppb_select2_multiple_enable_tags_display', 10, 6);
        }

        if (isset($field['select2-multiple-limit']) && ('' != $field['select2-multiple-limit']) && is_numeric($field['select2-multiple-limit'])) {
            $arguments['maximumSelectionLength'] = $field['select2-multiple-limit'];
            $arguments['maximumSelectionSize'] = $field['select2-multiple-limit']; // Backwards compatibility with Select2 v.3.5.3, loaded by WooCommerce
        }

        $field['labels'] = apply_filters('wppb_select2_multiple_labels', $field['labels'], $form_location, $field, $user_id, $field_check_errors, $request_data);
        $field['options'] = apply_filters('wppb_select2_multiple_options', $field['options'], $form_location, $field, $user_id, $field_check_errors, $request_data);

        $arguments = apply_filters('wppb_select2_multiple_arguments', $arguments, $form_location, $field, $user_id, $field_check_errors, $request_data);

        $item_title = apply_filters('wppb_' . $form_location . '_select2_multiple_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
        $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);
        $item_option_labels = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_labels_translation', $field['labels'], true);

        $select2_labels = apply_filters('wppb_select2_multiple_labels_array', explode(',', $item_option_labels));
        $select2_values = apply_filters('wppb_select2_multiple_options_array', explode(',', $field['options']));

        $extra_attr = apply_filters('wppb_extra_attribute', '', $field, $form_location);
        if ($form_location != 'register')
            $input_value = ((wppb_user_meta_exists($user_id, $field['meta-name']) != null) ? ( is_array( get_user_meta($user_id, $field['meta-name'], true ) ) ? get_user_meta($user_id, $field['meta-name'], true) : array_map('trim', explode(',', get_user_meta($user_id, $field['meta-name'], true))) ) : array_map('trim', explode(',', $field['default-options'])));
        else
            $input_value = (isset($field['default-options']) ? array_map('trim', explode(',', $field['default-options'])) : array());

        $input_value = (isset($request_data[wppb_handle_meta_name($field['meta-name'])]) ? array_map('esc_attr', (array)$request_data[wppb_handle_meta_name($field['meta-name'])]) : $input_value);

        if ($form_location != 'back_end') {
            $error_mark = (($field['required'] == 'Yes') ? '<span class="wppb-required" title="' . wppb_required_field_error($field["field-title"]) . '">*</span>' : '');

            if (array_key_exists($field['id'], $field_check_errors))
                $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error($field["field-title"]) . '"/>';

            $output = '
				<label for="' . esc_attr($field['meta-name']) . '">' . $item_title . $error_mark . '</label>
				<input type="hidden" value="" name="' . esc_attr($field['meta-name']) . '">
				<select name="' . esc_attr($field['meta-name']) . '[]" id="' . esc_attr(wppb_handle_meta_name($field['meta-name'])) . '" class="custom_field_select2 custom_field_multiple_select2" multiple="multiple" ' . $extra_attr . ' data-wppb-select2-arguments=\'' . json_encode($arguments) . '\'>';

            foreach ($select2_values as $key => $value) {
                $output .= '<option value="' . esc_attr(trim($value)) . '" class="custom_field_select2_option ' . apply_filters('wppb_fields_extra_css_class', '', $field) . '" ';

                if (in_array(trim($value), $input_value))
                    $output .= ' selected';

                $output .= '>' . ((!isset($select2_labels[$key]) || !$select2_labels[$key]) ? esc_attr(trim($select2_values[$key])) : esc_attr(trim($select2_labels[$key]))) . '</option>';
            }

            $output .= '
				</select>';
            if (!empty($item_description))
                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

        } else {
            //comment this out for now...I don't think we need it anymore
            /*if ( class_exists( 'WooCommerce' ) ) {
                // WooCommerce loads an older version of Select2 library which does not support tags
                unset($arguments['tags']);
            }*/
            $item_title = (($field['required'] == 'Yes') ? $item_title . ' <span class="description">(' . __('required', 'profile-builder') . ')</span>' : $item_title);
            $output = '
				<table class="form-table wppb-select2">
					<tr>
						<th><label for="' . esc_attr($field['meta-name']) . '">' . $item_title . '</label></th>
						<td>
							<select name="' . esc_attr($field['meta-name']) . '[]" class="custom_field_select2" id="' . esc_attr(wppb_handle_meta_name($field['meta-name'])) . '" multiple="multiple" ' . $extra_attr . ' data-wppb-select2-arguments=\'' . json_encode($arguments) . '\'>';

            foreach ($select2_values as $key => $value) {
                $output .= '<option value="' . esc_attr(trim($value)) . '" class="custom_field_select2_option" ';

                if (in_array(trim($value), $input_value))
                    $output .= ' selected';

                $output .= '>' . ((!isset($select2_labels[$key]) || !$select2_labels[$key]) ? esc_attr(trim($select2_values[$key])) : esc_attr(trim($select2_labels[$key]))) . '</option>';
            }

            $output .= '</select>
							<span class="description">' . $item_description . '</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters('wppb_' . $form_location . '_select2_multiple_custom_field_' . $field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value);
    }
}

add_filter('wppb_output_form_field_select2-multiple', 'wppb_select2_multiple_display_handler', 10, 6);
add_filter('wppb_admin_output_form_field_select2-multiple', 'wppb_select2_multiple_display_handler', 10, 6);

/*
 * Handle field save.
 *
 * @since 1.0.2
 *
 * @param array $field
 * @param integer $user_id
 * @param array $request_data
 * @param string $form_location
 */
function wppb_select2_multiple_save_value($field, $user_id, $request_data, $form_location)
{
    if ($field['field'] == 'Select2 (Multiple)') {
        if (isset($request_data[wppb_handle_meta_name($field['meta-name'])])) {
            $selected_values = wppb_select2_multiple_process_value($field, $request_data);
            update_user_meta($user_id, sanitize_text_field($field['meta-name']), trim($selected_values, ','));
        }
    }
}

add_action('wppb_save_form_field', 'wppb_select2_multiple_save_value', 10, 4);
add_action('wppb_backend_save_form_field', 'wppb_select2_multiple_save_value', 10, 4);


function wppb_select2_multiple_process_value($field, $request_data)
{
    $selected_values = '';
    if (!empty($request_data[wppb_handle_meta_name($field['meta-name'])]) && is_array($request_data[wppb_handle_meta_name($field['meta-name'])])) {
        foreach ($request_data[wppb_handle_meta_name($field['meta-name'])] as $key => $value)
            $selected_values .= sanitize_text_field($value) . ',';
    }

    return trim($selected_values, ',');
}

/*
 * Handle field validation.
 *
 * @since 1.0.2
 *
 * @param string $message
 * @param array $field
 * @param array $request_data
 * @param $form_location
 * @return string Message to display on field validation
 */
function wppb_select2_multiple_check_value($message, $field, $request_data, $form_location)
{
    if ($field['field'] == 'Select2 (Multiple)') {
        if ($field['required'] == 'Yes') {
            if (isset($request_data[wppb_handle_meta_name($field['meta-name'])]) && is_array($request_data[wppb_handle_meta_name($field['meta-name'])])) {
                $selected_values = '';
                foreach ($request_data[wppb_handle_meta_name($field['meta-name'])] as $key => $value)
                    $selected_values .= $value . ',';

                if (trim($selected_values, ',') == '') {
                    return wppb_required_field_error($field["field-title"]);
                }
            } else {
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}

add_filter('wppb_check_form_field_select2-multiple', 'wppb_select2_multiple_check_value', 10, 4);



/*
 * Make sure we display extra tags in front-end
 *
 * @since 1.0.7
 *
 * @param $field_options
 * @param $form_location
 * @param $field
 * @param $user_id
 * @param $field_check_errors
 * @param $request_data
 * @return string
 */
function wppb_select2_multiple_enable_tags_display($field_options, $form_location, $field, $user_id, $field_check_errors, $request_data)
{
    if ($form_location == 'register') {
        return $field_options;
    }

    $user_values = get_user_meta($user_id, $field['meta-name'], true);
    if ($user_values == '') {
        return $field_options;
    }

    $user_values_array = explode(',', $user_values);
    $existing_values_array = explode(',', $field_options);

    $merged_arrays = array_merge(array_map('trim', $existing_values_array), $user_values_array);
    $new_values_array = array_unique($merged_arrays);
    $new_values = implode(',', $new_values_array);

    return $new_values;
}
