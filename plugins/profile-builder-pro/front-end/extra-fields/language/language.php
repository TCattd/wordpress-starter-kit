<?php
/* handle field output */
function wppb_language_field_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Language' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_hidden_input_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location != 'register' )
		    $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-value'] );
		else
            $input_value = ( isset( $field['default-value'] ) ? trim( $field['default-value'] ) : '' );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		if ( apply_filters ( 'wppb_display_capability_level', current_user_can( 'manage_options' ) ) ){
			$input_type = 'text';
			$hidden_start = $hidden_end = '';

		}else{
			$input_type = 'hidden';
			$hidden_start = '<!--';
			$hidden_end = '-->';

		}

		if ( $form_location != 'back_end' ){
			$output = $hidden_start .'
				<label for="'.$field['meta-name'].'">'.$item_title.'</label>'. $hidden_end .'
				<input class="extra_field_hidden_input" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="'.$input_type.'" id="'.$field['meta-name'].'" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>
				'. $hidden_start .'<span class="wppb-description-delimiter">'.$item_description.'</span>'.$hidden_end;
		}else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = $hidden_start .'
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>'. $hidden_end .'
							<input class="custom_field_hidden_input" size="45" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="'.$input_type.'" id="'.$field['meta-name'].'" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>
							'. $hidden_start .'<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>'. $hidden_end;
		}

		return apply_filters( 'wppb_'.$form_location.'_hidden_input_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_language', 'wppb_language_field_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_language', 'wppb_language_field_handler', 10, 6 );


/* handle field save */
function wppb_save_language_field_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Language' && $form_location == 'register' ){
		update_user_meta( $user_id, $field['meta-name'], get_locale() );

		// also set the data in the default WordPress field
		update_user_meta( $user_id, 'locale', get_locale() );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_language_field_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_language_field_value', 10, 4 );
