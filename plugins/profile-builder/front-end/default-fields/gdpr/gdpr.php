<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_gdpr_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'GDPR Checkbox' ){
        if ( $form_location != 'back_end' ){
            $item_title = apply_filters( 'wppb_'.$form_location.'_gdpr_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
            $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

            if( $form_location != 'register' )
                $input_value = ((wppb_user_meta_exists($user_id, $field['meta-name']) != null) ? get_user_meta($user_id, $field['meta-name'], true) : '');
            else
                $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : '' );

            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

            $output = '
				<label for="'.$field['meta-name'].'">
				<input value="agree" name="'.$field['meta-name'].'" id="'.$field['meta-name'].'" type="checkbox" class="custom_field_gdpr" '. $extra_attr .' ';

            if ( isset( $input_value ) && ( $input_value == 'agree' ) )
                $output .= ' checked="yes"';

            $output .= ' />'.trim( html_entity_decode ( $item_description ) ).$error_mark.'</label>';

            return apply_filters( 'wppb_'.$form_location.'_gdpr_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
        }
    }
}
add_filter( 'wppb_output_form_field_gdpr-checkbox', 'wppb_gdpr_handler', 10, 6 );


/* handle field save */
function wppb_save_gdpr_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'GDPR Checkbox' ){
        if ( $form_location == 'register' || $form_location == 'edit_profile' ){
            if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
                update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
                //save the time when the user agreed
                update_user_meta( $user_id, 'gdpr_agreement_time', time() );
        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_gdpr_value', 10, 4 );

/* handle field validation */
function wppb_check_gdpr_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'GDPR Checkbox' ){
        if ( $form_location != 'back_end' ){
            $checked_values = '';

            if( isset( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) ) {
                
                if( is_array( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) )
                    $checked_values = implode( ',', $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] );
                else
                    $checked_values = $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ];

            }

            if ( ( $field['required'] == 'Yes' ) && empty( $checked_values ) )
                return wppb_required_field_error($field['field-title']);
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_gdpr-checkbox', 'wppb_check_gdpr_value', 10, 4 );

add_filter( 'wppb_field_css_class', 'wppb_gdpr_add_checkbox_class', 20, 2);
function wppb_gdpr_add_checkbox_class( $classes, $field ){
    if( $field['field'] == 'GDPR Checkbox' )
        $classes .= ' wppb-checkbox';

    return $classes;
}
