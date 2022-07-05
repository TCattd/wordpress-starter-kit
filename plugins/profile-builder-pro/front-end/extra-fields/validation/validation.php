<?php
/* handle field output */
function wppb_validation_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Validation' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_validation_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : '' );

        if ( $form_location == 'register' ){
            $error_mark = '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>';

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<input class="extra_field_input '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="'.$field['meta-name'].'" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        }

        return apply_filters( 'wppb_'.$form_location.'_validation_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_validation', 'wppb_validation_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_validation', 'wppb_validation_handler', 10, 6 );

/* handle field save */
function wppb_save_validation_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'Validation' ){
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
            update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_validation_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_validation_value', 10, 4 );

/* handle field validation */
function wppb_check_validation_value( $message, $field, $request_data, $form_location ){

    if( $form_location != 'register' )
        return $message;

    if( $field['field'] == 'Validation' ){

        // Field must not be empty
        if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
            return wppb_required_field_error($field["field-title"]);
        }

        // Check if the provided value matches the allowable values
        if( !empty( $field['validation-possible-values'] ) ) {

            $allowed_values = explode( ',', $field['validation-possible-values'] );

            if( !in_array( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ], $allowed_values ) ) {

                if( !empty( $field['custom-error-message'] ) )
                    return $field['custom-error-message'];
                else
                    return wppb_required_field_error($field["field-title"]);
            }

        }

    }

    return $message;
}
add_filter( 'wppb_check_form_field_validation', 'wppb_check_validation_value', 10, 4 );