<?php
/* handle field output */
function wppb_multiple_select_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Select (Multiple)' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_multiple_select_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );
		$item_option_labels = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_labels_translation', $field['labels'], true );

        $select_labels = apply_filters( 'wppb_multiple_select_labels_array', explode( ',', $item_option_labels ), $field, $form_location, $user_id, $request_data );
        $select_values = apply_filters( 'wppb_multiple_select_options_array', explode( ',', $field['options'] ), $field, $form_location, $user_id, $request_data );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location != 'register' )
		    $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? array_map( 'trim', explode( ',', get_user_meta( $user_id, $field['meta-name'], true ) ) ) : array_map( 'trim', explode( ',', $field['default-options'] ) ) );
		else
            $input_value = ( isset( $field['default-options'] ) ? array_map( 'trim', explode( ',', $field['default-options'] ) ) : array() );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? array_map( 'trim', (array)$request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
						
			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<input type="hidden" value="" name="' . $field['meta-name'] . '">
				<select name="'.$field['meta-name'].'[]" id="'.$field['meta-name'].'" size="'.( count( $select_values ) > 10 ? count( $select_values ) / 2 : count( $select_values ) ).'" class="custom_field_multiple_select '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" multiple="multiple" '. $extra_attr .'>';

				foreach( $select_values as $key => $value){
					$output .= '<option value="'.trim( $value ).'" class="custom_field_multiple_select_option" name="'.trim( $value ).'_'.$field['id'].'" id="'.trim( $value ).'_'.$field['id'].'"';
					
					if ( in_array( trim( $value ), $input_value ) )
						$output .= ' selected';

					$output .= '>'.( ( !isset( $select_labels[$key] ) || !$select_labels[$key] ) ? trim( $select_values[$key] ) : trim( $select_labels[$key] ) ).'</option>';
				}
				
				$output .= '
				</select>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

		}else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<select name="'.$field['meta-name'].'[]" class="custom_field_multiple_select" id="'.$field['meta-name'].'" multiple="multiple" '. $extra_attr .'>';
							
							foreach( $select_values as $key => $value){
								$output .= '<option value="'.trim( $value ).'" size="'.( count( $select_values ) > 10 ? count( $select_values ) / 2 : count( $select_values ) ).'" class="custom_field_multiple_select_option" id="'.trim( $value ).'_'.$field['id'].'"';
								
								
								if ( in_array( trim( $value ), $input_value ) )
									$output .= ' selected';

								$output .= '>'.( ( !isset( $select_labels[$key] ) || !$select_labels[$key] ) ? trim( $select_values[$key] ) : trim( $select_labels[$key] ) ).'</option>';
							}
							
							$output .= '</select>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
		}
			
		return apply_filters( 'wppb_'.$form_location.'_multiple_select_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_select-multiple', 'wppb_multiple_select_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_select-multiple', 'wppb_multiple_select_handler', 10, 6 );


/* handle field save */
function wppb_save_multiple_select_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Select (Multiple)' ){
		if( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {
			$selected_values = wppb_process_multipl_select_value($field, $request_data);
			update_user_meta($user_id, $field['meta-name'], trim($selected_values, ','));
		}
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_multiple_select_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_multiple_select_value', 10, 4 );


function wppb_process_multipl_select_value( $field, $request_data ){
	$selected_values = '';
	if( !empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && is_array( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
		foreach ( $request_data[wppb_handle_meta_name( $field['meta-name'] )] as $key => $value )
			$selected_values .= $value.',';
	}
	
	return trim( $selected_values, ',' );
}


function wppb_add_multiple_select_for_user_signup( $field_value, $field, $request_data ){
	return wppb_process_multipl_select_value( $field, $request_data );
}
add_filter( 'wppb_add_to_user_signup_form_field_select-multiple', 'wppb_add_multiple_select_for_user_signup', 10, 3 );


/* handle field validation */
function wppb_check_multiple_select_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Select (Multiple)' && $field['required'] == 'Yes' ){
		if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && is_array( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){

			$selected_values = '';
			foreach ( $request_data[wppb_handle_meta_name( $field['meta-name'] )] as $key => $value )
				$selected_values .= $value.',';

			if ( trim( $selected_values, ',' ) == '' ){
				return wppb_required_field_error($field["field-title"]);
			}
		}
	}

    return $message;
}
add_filter( 'wppb_check_form_field_select-multiple', 'wppb_check_multiple_select_value', 10, 4 );