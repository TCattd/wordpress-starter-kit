<?php
/* handle field output */
function wppb_number_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Number' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_number_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

		if( $form_location != 'register' )
			$input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-value'] );
		else
			$input_value = ( isset( $field['default-value'] ) ? trim( $field['default-value'] ) : '' );

		$input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		if ( $form_location != 'back_end' ) {
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<input class="extra_field_number '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" step="'. ( ! empty( $field['number-step-value'] ) ? $field['number-step-value'] : 'any' ) .'" type="number" min="'. ( ! empty( $field['min-number-value'] ) ? $field['min-number-value'] : ( $field['min-number-value'] == '0' ? '0' : '' ) ) .'" max="'. ( ! empty( $field['max-number-value'] ) ? $field['max-number-value'] : ( $field['max-number-value'] == '0' ? '0' : '' ) ) .'" id="'.$field['meta-name'].'" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
			if( ! empty( $item_description ) )
				$output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

		} else {
			$item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<input class="custom_field_number" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="number" step="'. ( ! empty( $field['number-step-value'] ) ? $field['number-step-value'] : 'any' ) .'" id="'.$field['meta-name'].'" value="'. esc_attr( $input_value ) .'" '. $extra_attr .'/>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
		}

		return apply_filters( 'wppb_'.$form_location.'_number_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_number', 'wppb_number_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_number', 'wppb_number_handler', 10, 6 );

/* handle field save */
function wppb_save_number_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Number' ){
		if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
			update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_number_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_number_value', 10, 4 );

/* handle field validation */
function wppb_check_number_value( $message, $field, $request_data, $form_location ) {
	if( $field['field'] == 'Number' ) {
		if( $field['required'] == 'Yes' ) {
			if( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
				return wppb_required_field_error($field["field-title"]);
			}
		}

		if( ! empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ! is_numeric( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {
			return __( 'Please enter numbers only', 'profile-builder' );
		}

		if( ! empty( $field['number-step-value'] ) && ! empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( sprintf( round( $request_data[wppb_handle_meta_name( $field['meta-name'] )] / $field['number-step-value'] ) ) != sprintf( $request_data[wppb_handle_meta_name( $field['meta-name'] )] / $field['number-step-value'] ) ) ) {
			return sprintf( __( 'Value must be a multiplier of %1$s', 'profile-builder' ), $field['number-step-value'] );
		}

		if( ( ! empty( $field['min-number-value'] ) || $field['min-number-value'] == '0' ) && ( ! empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) || $request_data[wppb_handle_meta_name( $field['meta-name'] )] == '0' ) && $request_data[wppb_handle_meta_name( $field['meta-name'] )] < $field['min-number-value'] ) {
			return sprintf( __( 'Value must be greater than or equal to %1$s', 'profile-builder' ), $field['min-number-value'] );
		}

		if( ( ! empty( $field['max-number-value'] ) || $field['max-number-value'] == '0' ) && ( ! empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) || $request_data[wppb_handle_meta_name( $field['meta-name'] )] == '0' ) && $request_data[wppb_handle_meta_name( $field['meta-name'] )] > $field['max-number-value'] ) {
			return sprintf( __( 'Value must be less than or equal to %1$s', 'profile-builder' ), $field['max-number-value'] );
		}
	}

	return $message;
}
add_filter( 'wppb_check_form_field_number', 'wppb_check_number_value', 10, 4 );