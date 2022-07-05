<?php
/* handle field output */
function wppb_phone_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Phone' ){
		wp_enqueue_script( 'wppb-jquery-inputmask', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/phone/jquery.inputmask.bundle.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION, true );
		wp_enqueue_script( 'wppb-phone-script', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/phone/script-phone.js', array( 'wppb-jquery-inputmask' ), PROFILE_BUILDER_VERSION, true );

		$phone_data = json_encode( array( 'phone_data'	=>	wppb_make_phone_number_format( $field, $request_data ) ) );

		$item_title = apply_filters( 'wppb_'.$form_location.'_phone_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

		if( $form_location != 'register' )
			$input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-value'] );
		else
			$input_value = ( isset( $field['default-value'] ) ? trim( $field['default-value'] ) : '' );

		$input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		$phone_format_description = __( 'Required phone number format: ', 'profile-builder' ) . $field['phone-format'] .'<br>';
		$phone_format_description = apply_filters( 'wppb_phone_format_description', $phone_format_description );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<input data-phone-format="'. esc_attr( $phone_data ) .'" class="extra_field_phone '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="'.$field['meta-name'].'" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';

			if( ! empty( $field['phone-format'] ) || ! empty( $item_description ) ) {
				$output .= '<span class="wppb-description-delimiter">'. ( ! empty( $field['phone-format'] ) ? $phone_format_description : '' ) . ( ! empty( $item_description ) ? $item_description : '' ) .'</span>';
			}
		}else{
			$item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<input data-phone-format="'. esc_attr( $phone_data ) .'" class="custom_field_phone" size="45" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="'.$field['meta-name'].'" value="'. esc_attr( $input_value ) .'" '. $extra_attr .'/>
							<span class="description">'. ( ! empty( $field['phone-format'] ) ? $phone_format_description : '' ) . $item_description. '</span>
						</td>
					</tr>
				</table>';
		}

		return apply_filters( 'wppb_'.$form_location.'_phone_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_phone', 'wppb_phone_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_phone', 'wppb_phone_handler', 10, 6 );

/* handle field save */
function wppb_save_phone_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Phone' ){
		if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
			update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_phone_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_phone_value', 10, 4 );

/* handle field validation */
function wppb_check_phone_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Phone' ){
		if( $field['required'] == 'Yes' ){
			if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
				return wppb_required_field_error($field["field-title"]);
			}
		}

		if( ! empty( $field['phone-format'] ) ) {
			$is_phone_error = wppb_check_phone_number_format( $field, $request_data );
			if( ! empty( $is_phone_error ) ) {
				return wppb_phone_field_error( $field["field-title"] );
			}
		}
	}

	return $message;
}
add_filter( 'wppb_check_form_field_phone', 'wppb_check_phone_value', 10, 4 );

/* handle phone number validation */
function wppb_check_phone_number_format( $field, $request_data ) {
	if( ! empty( $request_data[$field['meta-name']] ) ) {
		$phone_nb = array();
		$length = strlen( $request_data[$field['meta-name']] );

		for( $i=0; $i < $length; $i++ ) {
			$phone_nb[$i] = $request_data[$field['meta-name']][$i];

			if( $request_data[$field['meta-name']][$i] == '_' ) {
				return 'phone_error';
				break;
			}
		}
	}

	/*if( $phone_nb != 0 ) {
		$dynamic_regex = '/^';
		foreach( $phone_nb as $value ) {
			$available_characters = array( '(', ')', '-', '+', '.', ' ' );
			switch( $value ) {
				case in_array( $value, $available_characters ) :
					$dynamic_regex .= '\D';
					break;
				default :
					$dynamic_regex .= '\d';
			}
		}
		$dynamic_regex .= '$/';
	}

	if( isset( $dynamic_regex ) ) {
		if ( ! preg_match( $dynamic_regex, $request_data[$field['meta-name']] ) ) {
			ddumpdie( $request_data[$field['meta-name']] );
		}
	}*/

	return '';
}

/* handle phone number format */
function wppb_make_phone_number_format( $field, $request_data ) {
	if( ! empty( $field['phone-format'] ) ) {
		$available_characters = array( '#', '(', ')', '-', '+', '.', ' ' );
		$phone_nb_chars = array();
		$length = strlen( $field['phone-format'] );

		for( $i=0; $i < $length; $i++ ) {
			$phone_nb_chars[$i] = $field['phone-format'][$i];

			if( ! in_array( $field['phone-format'][$i], $available_characters ) ) {
				$phone_nb_chars = 0;
				break;
			}
		}
	} else {
		$phone_nb_chars = 0;
	}

	return $phone_nb_chars;
}