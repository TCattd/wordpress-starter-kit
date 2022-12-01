<?php
/* handle field output */
function wppb_checkbox_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Checkbox' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_checkbox_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );
		$item_option_labels = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_labels_translation', $field['labels'], true );

		$checkbox_labels = apply_filters( 'wppb_checkbox_labels_array', explode( ',', $item_option_labels ), $field, $form_location, $user_id, $request_data );
		$checkbox_values = apply_filters( 'wppb_checkbox_options_array', explode( ',', $field['options'] ), $field, $form_location, $user_id, $request_data );

        if( $form_location != 'register' )
		    $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? array_map( 'trim', explode( ',', stripslashes(get_user_meta( $user_id, $field['meta-name'], true )) ) ) : array_map( 'trim', explode( ',', $field['default-options'] ) ) );
        else
            $input_value = ( !empty( $field['default-options'] ) ? array_map( 'trim', explode( ',', $field['default-options'] ) ) : array() );

        if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) && !empty( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) )
            $input_value = $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ];

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>';
					$output .= '<ul class="wppb-checkboxes">';
						$output .= '<li class="wppb-hidden"><input type="hidden" value="" name="' . $field['meta-name'] . '"></li>';
					foreach( $checkbox_values as $key => $value ){
						$output .= '<li><input value="'.esc_attr( trim( $value ) ).'" class="custom_field_checkbox" name="' . $field['meta-name'] . '[]" id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" type="checkbox" '. $extra_attr .' ';

						if ( in_array( trim( $value ), $input_value ) )
							$output .= ' checked';

						$output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'.( ( !isset( $checkbox_labels[$key] ) || !$checkbox_labels[$key] ) ? trim( $checkbox_values[$key] ) : trim( $checkbox_labels[$key] ) ).'</label></li>';
					}
				$output .= '</ul>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

		}else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>';
                        $output .= '<ul class="wppb-checkboxes">';
                        $output .= '<li class="wppb-hidden"><input type="hidden" value="" name="' . $field['meta-name'] . '"></li>';
						foreach( $checkbox_values as $key => $value ){
							$output .= '<li><input value="'.esc_attr( trim( $value ) ).'" class="custom_field_checkbox '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="' . $field['meta-name'] . '[]" id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" type="checkbox"';

							if ( in_array( trim( $value ), $input_value ) )
								$output .= ' checked';

							$output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'.( ( !isset( $checkbox_labels[$key] ) || !$checkbox_labels[$key] ) ? trim( $checkbox_values[$key] ) : trim( $checkbox_labels[$key] ) ).'</label></li>';
						}

						$output .= '</ul>
						<span class="wppb-description-delimiter">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
		}

		return apply_filters( 'wppb_'.$form_location.'_checkbox_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_checkbox', 'wppb_checkbox_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_checkbox', 'wppb_checkbox_handler', 10, 6 );


/* handle field save */
function wppb_save_checkbox_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Checkbox' ){
		if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) ) {
			$checkbox_values = wppb_process_checkbox_value($field, $request_data);
			update_user_meta($user_id, $field['meta-name'], $checkbox_values);
		}
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_checkbox_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_checkbox_value', 10, 4 );


function wppb_process_checkbox_value( $field, $request_data ){
	$checkbox_values = '';

    if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) && is_array( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) )
        $checkbox_values = implode( ',', $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] );

	return trim( $checkbox_values, ',' );
}


function wppb_add_checkbox_for_user_signup( $field_value, $field, $request_data ){
	return wppb_process_checkbox_value( $field, $request_data );
}
add_filter( 'wppb_add_to_user_signup_form_field_checkbox', 'wppb_add_checkbox_for_user_signup', 10, 3 );


/* handle field validation */
function wppb_check_checkbox_value( $message, $field, $request_data, $form_location ){

	if( $field['field'] == 'Checkbox' ){
		$checked_values = '';

        if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) && is_array( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) )
            $checked_values = implode( ',', $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] );

		if ( ( $field['required'] == 'Yes' ) && empty( $checked_values ) ){
			return wppb_required_field_error($field["field-title"]);
		}

	}

    return $message;
}
add_filter( 'wppb_check_form_field_checkbox', 'wppb_check_checkbox_value', 10, 4 );
