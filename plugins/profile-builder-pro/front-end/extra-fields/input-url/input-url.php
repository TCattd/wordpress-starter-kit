<?php
/* handle field output */
function wppb_input_url_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'URL' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_input_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

		$extra_attr = apply_filters( 'wppb_extra_attribute', 'placeholder="https://example.com"', $field, $form_location );

        if( $form_location != 'register' )
		    $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-value'] );
		else
            $input_value = ( isset( $field['default-value'] ) ? trim( $field['default-value'] ) : '' );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<input class="extra_field_input '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="url" id="'.$field['meta-name'].'" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

		}else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<input class="custom_field_input" size="45" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="url" id="'.$field['meta-name'].'" value="'. esc_attr( $input_value ) .'" '. $extra_attr .'/>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
		}

		return apply_filters( 'wppb_'.$form_location.'_input_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_url', 'wppb_input_url_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_url', 'wppb_input_url_handler', 10, 6 );

/* handle field save */
function wppb_save_input_url_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'URL' ){
		if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
			update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_input_url_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_input_url_value', 10, 4 );

/* handle field validation */
function wppb_check_input_url_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'URL' ){
		if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }

		if ( !empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {
            if ( !preg_match( '/^(https?|ftp):\/\/[^\s\/$.?#].[^\s]*$/', $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {
                return __( 'You must enter a valid URL.', 'profile-builder' );
            }
        }
	}

    return $message;
}
add_filter( 'wppb_check_form_field_url', 'wppb_check_input_url_value', 10, 4 );
