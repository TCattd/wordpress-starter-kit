<?php
/* handle field output */
function wppb_gdprcp_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'GDPR Communication Preferences' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_gdpr_communication_preferences_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $checkbox_values = explode( ',', $field['gdpr-communication-preferences'] );
        $checkbox_labels = array( "email" => __('Email', 'profile-builder'), "sms" => __('SMS', 'profile-builder'), "phone" => __('Telephone', 'profile-builder'), "post" => __('Post', 'profile-builder') );


        if( $form_location != 'register' ) {
            if( wppb_user_meta_exists($user_id, $field['meta-name']) !== null ){
                $stored_value = get_user_meta($user_id, $field['meta-name'], true);
                if( is_array( $stored_value ) )//this should not be the case but we had a client that had this problem (possible conflict with other plugin but could not identify it)
                    $stored_value = implode( ',', $stored_value );
                $input_value = array_map('trim', explode(',', stripslashes($stored_value)));
            }
            else{
                $input_value = array_map('trim', explode(',', $field['default-options']));
            }
        }
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
            foreach( $checkbox_values as $key => $value ){
                $output .= '<li><input value="'.esc_attr( trim( $value ) ).'" class="custom_field_checkbox" name="' . $field['meta-name'] . '[]" id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" type="checkbox" '. $extra_attr .' ';

                if ( in_array( trim( $value ), $input_value ) )
                    $output .= ' checked';

                $output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'.( ( !isset( $checkbox_labels[ trim( $value )] ) || !$checkbox_labels[ trim( $value )] ) ? trim( $checkbox_values[$key] ) : trim( $checkbox_labels[ trim( $value )] ) ).'</label></li>';
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

            foreach( $checkbox_values as $key => $value ){
                $output .= '<li><input value="'.esc_attr( trim( $value ) ).'" class="custom_field_checkbox '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="' . $field['meta-name'] . '[]" id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" type="checkbox"';

                if ( in_array( trim( $value ), $input_value ) )
                    $output .= ' checked';

                $output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'.( ( !isset( $checkbox_labels[ trim( $value )] ) || !$checkbox_labels[ trim( $value )] ) ? trim( $checkbox_values[$key] ) : trim( $checkbox_labels[ trim( $value )] ) ).'</label></li>';
            }



            $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

                //display the history of the changes to the filed in  the admin area
                $gdpr_communication_preferences_history = get_user_meta( $user_id, 'gdpr_communication_preferences_history', true );
                if( !empty( $gdpr_communication_preferences_history ) ){
                    $output .= '<table class="form-table" style="max-width:700px;"><tbody>';
                    $output .= '<tr><th>'. __( 'Date', 'profile-builder' ) .'</th><th>'. __( 'Preference', 'profile-builder' ) .'</th></tr>';
                        foreach( $gdpr_communication_preferences_history as $date => $preff ){
                            $output .= '<tr><td style="padding: 5px 10px;">'. $date .'</td><td style="padding: 5px 10px;">'. $preff .'</td></tr>';
                        }
                    $output .= '</tbody></table>';
                }

            $output .=  '</td>
					</tr>
				</table>';




        }

        return apply_filters( 'wppb_'.$form_location.'_gcp_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_gdpr-communication-preferences', 'wppb_gdprcp_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_gdpr-communication-preferences', 'wppb_gdprcp_handler', 10, 6 );


/* handle field save */
function wppb_save_gdprcp_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'GDPR Communication Preferences' ){
        $prev_value = get_user_meta($user_id, $field['meta-name'], true );
        $checkbox_values = wppb_process_gdprcp_value( $field, $request_data );
        update_user_meta( $user_id, $field['meta-name'], $checkbox_values, $prev_value );
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_gdprcp_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_gdprcp_value', 10, 4 );


function wppb_process_gdprcp_value( $field, $request_data ){
    $checkbox_values = '';

    if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) )
        $checkbox_values = implode( ',', $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] );

    return trim( $checkbox_values, ',' );
}


function wppb_add_gdprcp_for_user_signup( $field_value, $field, $request_data ){
    return wppb_process_gdprcp_value( $field, $request_data );
}
add_filter( 'wppb_add_to_user_signup_form_field_gdpr_communication_preferences', 'wppb_add_gdprcp_for_user_signup', 10, 3 );


/* handle field validation */
function wppb_check_gdprcp_value( $message, $field, $request_data, $form_location ){

    if( $field['field'] == 'GDPR Communication Preferences' ){
        $checked_values = '';

        if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) )
            $checked_values = implode( ',', $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] );

        if ( ( $field['required'] == 'Yes' ) && empty( $checked_values ) ){
            return wppb_required_field_error($field["field-title"]);
        }

    }

    return $message;
}
add_filter( 'wppb_check_form_field_gdpr-communication-preferences', 'wppb_check_gdprcp_value', 10, 4 );


/**
 * Save the modifications done by the user in a new meta "gdpr_communication_preferences_history"
 * this is a feature of the gdpr communication preferences
 */
add_filter( 'update_user_metadata', 'wppb_gdprcp_save_gdpr_communication_preferences', 10, 5 );
function wppb_gdprcp_save_gdpr_communication_preferences( $null, $object_id, $meta_key, $meta_value, $prev_value ){
    if ( 'gdpr_communication_preferences' == $meta_key && ( $meta_value != $prev_value ) ) {
        $existing_values = get_user_meta( $object_id, 'gdpr_communication_preferences_history', true );
        if( empty( $existing_values ) )
            $existing_values = array();

        $existing_values[date('Y-m-d H:i:s')] = $meta_value;
        update_user_meta( $object_id, 'gdpr_communication_preferences_history', $existing_values );
    }

    return null; // this means: go on with the normal execution in meta.php
}


