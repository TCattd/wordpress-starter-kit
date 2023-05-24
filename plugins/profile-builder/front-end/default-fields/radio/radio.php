<?php
/* handle field output */
function wppb_radio_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Radio' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_radio_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );
		$item_option_labels = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_labels_translation', $field['labels'], true );

		$radio_labels = apply_filters( 'wppb_radio_labels_array', explode( ',', $item_option_labels ), $field, $form_location, $user_id, $request_data );
		$radio_values = apply_filters( 'wppb_radio_options_array', explode( ',', $field['options'] ), $field, $form_location, $user_id, $request_data );

        if( $form_location != 'register' )
		    $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? stripslashes(get_user_meta( $user_id, $field['meta-name'], true )) : $field['default-option'] );
		else
            $input_value = ( isset( $field['default-option'] ) ? trim( $field['default-option'] ) : '' );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( stripslashes( $request_data[wppb_handle_meta_name( $field['meta-name'] )] )) : $input_value );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
						
			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>';
					$output .= '<ul class="wppb-radios">';
					foreach( $radio_values as $key => $value){
						$output .= '<li><input value="'.esc_attr( trim( $value ) ).'" class="custom_field_radio '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" name="'.$field['meta-name'].'" type="radio" '. $extra_attr .' ';
						
						if ( $input_value === trim( $value ) )
							$output .= ' checked';

						$output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'.( ( !isset( $radio_labels[$key] ) || !$radio_labels[$key] ) ? trim( $radio_values[$key] ) : trim( $radio_labels[$key] ) ).'</label></li>';
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

							foreach( $radio_values as $key => $value ){								
								$output .= '<li><input value="'.esc_attr( trim( $value ) ).'"  id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="custom_field_radio" name="'.$field['meta-name'].'" type="radio" '. $extra_attr .' ';
								
								if ( $input_value === trim( $value ) )
									$output .= ' checked';

								$output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'.( ( !isset( $radio_labels[$key] ) || !$radio_labels[$key] ) ? trim( $radio_values[$key] ) : trim( $radio_labels[$key] ) ).'</label></li>';
							}

							$output .= '
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
		}
			
		return apply_filters( 'wppb_'.$form_location.'_radio_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_radio', 'wppb_radio_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_radio', 'wppb_radio_handler', 10, 6 );


/* handle field save */
function wppb_save_radio_select_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Radio' ){
		if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
			update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_radio_select_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_radio_select_value', 10, 4 );


/* handle field validation */
function wppb_check_radio_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Radio' ){
		if ( !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( $field['required'] == 'Yes' ) ){
			return wppb_required_field_error($field["field-title"]);
		}
	}

    return $message;
}
add_filter( 'wppb_check_form_field_radio', 'wppb_check_radio_value', 10, 4 );