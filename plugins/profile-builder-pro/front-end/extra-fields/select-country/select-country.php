<?php
/* handle field output */
function wppb_country_select_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Select (Country)' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_country_select_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $country_array = wppb_country_select_options( $form_location );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

		if( $form_location != 'register' ) {
			// change current user country meta_value with country ISO code
			$user_country_option = wppb_user_meta_exists( $user_id, $field['meta-name'] );

			$input_value = ( ( $user_country_option != null ) ? $country_array[stripslashes( get_user_meta( $user_id, $field['meta-name'], true ) )] : $country_array[$field['default-option-country']] );
		} else {
			$input_value = ( ! empty( $field['default-option-country'] ) ? $country_array[trim( $field['default-option-country'] )] : '' );
		}

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? $country_array[trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] )] : $input_value );

        $input_value = apply_filters( 'wppb_' . $form_location . '_country_select_custom_field_input_value', $input_value, $field, $form_location );

        if ( $form_location != 'back_end' ){
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<select name="'.$field['meta-name'].'" id="'.$field['meta-name'].'" class="custom_field_country_select '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" '. $extra_attr .'>';

			$extra_select_option = apply_filters( 'wppb_extra_select_option', '', $field, $item_title );
			if( ! empty( $extra_select_option ) ) {
				$output .= $extra_select_option;
				$country_array = array_filter( $country_array );
			}

            foreach( $country_array as $iso => $country ){

                $output .= '<option value="'.$iso.'"';

                if ( $input_value === $country )
                    $output .= ' selected';

                $output .= '>'.$country.'</option>';
            }

            $output .= '
				</select>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        }else{
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<select name="'.$field['meta-name'].'" class="custom_field_country_select" id="'.$field['meta-name'].'" '. $extra_attr .'>';

            foreach( $country_array as $iso => $country ){
                $output .= '<option value="'.$iso.'"';

                if ( $input_value === $country )
                    $output .= ' selected';

                $output .= '>'.$country.'</option>';
            }

            $output .= '</select>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters( 'wppb_'.$form_location.'_country_select_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_select-country', 'wppb_country_select_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_select-country', 'wppb_country_select_handler', 10, 6 );


/* handle field save */
function wppb_save_country_select_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'Select (Country)' ){
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
            update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_country_select_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_country_select_value', 10, 4 );


/* handle field validation */
function wppb_check_country_select_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'Select (Country)' ){
        if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_select-country', 'wppb_check_country_select_value', 10, 4 );