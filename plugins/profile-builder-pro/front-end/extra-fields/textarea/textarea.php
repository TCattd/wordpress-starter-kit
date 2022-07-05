<?php
/* handle field output */
function wppb_textarea_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Textarea' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_textarea_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location != 'register' )
		    $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-content'] );
		else
            $input_value = ( isset( $field['default-content'] ) ? trim( $field['default-content'] ) : '' );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
						
			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<textarea rows="'.$field['row-count'].'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', '', $field ) .'" class="custom_field_textarea '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" id="'.$field['meta-name'].'" wrap="virtual" '. $extra_attr .'>'. esc_textarea( wp_unslash( $input_value ) ) .'</textarea>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

		}else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<textarea rows="'.$field['row-count'].'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', '', $field ) .'" class="custom_field_textarea" id="'.$field['meta-name'].'" wrap="virtual" '. $extra_attr .'>'. esc_textarea( $input_value ) .'</textarea>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
		}

		$filter_types = array( $field['meta-name'], $field['id'] );
		foreach( $filter_types as $filter_type ){
			$output = apply_filters( 'wppb_'.$form_location.'_textarea_custom_field_'.$filter_type, $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
		}

		return $output;
	}
}
add_filter( 'wppb_output_form_field_textarea', 'wppb_textarea_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_textarea', 'wppb_textarea_handler', 10, 6 );


/* handle field save */
function wppb_save_textarea_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Textarea' ){
		if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
			update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_textarea_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_textarea_value', 10, 4 );


/* handle field validation */
function wppb_check_textarea_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Textarea' ){
        if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
	}

    return $message;
}
add_filter( 'wppb_check_form_field_textarea', 'wppb_check_textarea_value', 10, 4 );