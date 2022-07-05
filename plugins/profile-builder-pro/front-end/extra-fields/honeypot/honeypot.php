<?php
/* handle field output */
function wppb_honeypot_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Honeypot' && $form_location != 'back_end' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_input_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $error_mark = array_key_exists( $field['id'], $field_check_errors ) ? '<span class="wppb-required">*</span>' : '';

        $output = '
            <label for="'.$field['meta-name'].'" style="display:none">'.$item_title.$error_mark.'</label>
            <input class="wppb-honeypot '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="'.$field['meta-name'].'" maxlength="'. apply_filters( 'wppb_maximum_character_length', 250, $field ) .'" type="text" id="'.$field['meta-name'].'" autocomplete="off" style="display:none" value="" '. $extra_attr .'/>';

        return apply_filters( 'wppb_'.$form_location.'_honeypot_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
    }
}
add_filter( 'wppb_output_form_field_honeypot', 'wppb_honeypot_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_honeypot', 'wppb_honeypot_handler', 10, 6 );

/* handle field validation */
function wppb_check_honeypot_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'Honeypot' ){
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) !== '' ) ){
            return __( 'The hidden Honeypot field must be empty.', 'profile-builder' );
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_honeypot', 'wppb_check_honeypot_value', 10, 4 );