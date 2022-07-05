<?php
/* handle field output */
function wppb_agree_to_terms_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Checkbox (Terms and Conditions)' ){
		if ( $form_location == 'register' ){
			$item_title = apply_filters( 'wppb_'.$form_location.'_toa_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
			$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );
			
			$input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : '' );

			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

			$output = '
				<label for="'.$field['meta-name'].'">
				<input value="agree" name="'.$field['meta-name'].'" id="'.$field['meta-name'].'" type="checkbox" class="custom_field_toa" '. $extra_attr .' ';
				
				if ( isset( $input_value ) && ( $input_value == 'agree' ) )
					$output .= ' checked="yes"';
				
				$output .= ' />'.$item_title.$error_mark.'</label>';

			$output .= '<span class="agree_to_terms">'.trim( html_entity_decode ( $item_description ) ).'</span>';

			return apply_filters( 'wppb_'.$form_location.'_toa_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
		}
	}
}
add_filter( 'wppb_output_form_field_checkbox-terms-and-conditions', 'wppb_agree_to_terms_handler', 10, 6 );
//add_filter( 'wppb_admin_output_form_field_checkbox-terms-and-conditions', 'wppb_agree_to_terms_handler', 10, 6 );


/* handle field save */
function wppb_save_toa_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Checkbox (Terms and Conditions)' ){
		if ( $form_location == 'register' ){
			if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
				update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
		}
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_toa_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_toa_value', 10, 4 );


/* handle field validation */
function wppb_check_toa_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Checkbox (Terms and Conditions)' ){
		if ( $form_location == 'register' ){
			if ( ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) && ( $field['required'] == 'Yes' ) ) || ( !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( $field['required'] == 'Yes' ) ) ){
				return wppb_required_field_error($field["field-title"]);
			}
		}
	}
    return $message;
}
add_filter( 'wppb_check_form_field_checkbox-terms-and-conditions', 'wppb_check_toa_value', 10, 4 );