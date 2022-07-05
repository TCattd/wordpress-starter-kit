<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_password_repeat_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	$item_title = apply_filters( 'wppb_'.$form_location.'_password_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	
	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? ( $form_location != 'edit_profile' ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' ) : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="passw2">' . $item_title.$error_mark . '</label>
			<input class="text-input '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="passw2" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="password" id="passw2" value="" autocomplete="off" '. $extra_attr .'/>';

        /* add the HTML for the visibility toggle */
        $output .= wppb_password_visibility_toggle_html();

        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';
	}
		
	return apply_filters( 'wppb_'.$form_location.'_repeat_password', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-repeat-password', 'wppb_password_repeat_handler', 10, 6 );


/* handle field validation */
function wppb_check_repeat_password_value( $message, $field, $request_data, $form_location ){
	if ( $form_location == 'register' ){
		if ( ( isset( $request_data['passw2'] ) && ( trim( $request_data['passw2'] ) == '' ) ) && ( $field['required'] == 'Yes' ) )
			return wppb_required_field_error($field["field-title"]);
		
		elseif ( !isset( $request_data['passw2'] ) && ( $field['required'] == 'Yes' ) )
			return wppb_required_field_error($field["field-title"]);
			
		elseif ( isset( $request_data['passw1'] ) && isset( $request_data['passw2'] ) && ( trim( $request_data['passw1'] ) != trim( $request_data['passw2'] ) ) && ( $field['required'] == 'Yes' ) )
			return __( "The passwords do not match", "profile-builder" );
	
	}elseif ( $form_location == 'edit_profile' ){
		if ( isset( $request_data['passw1'] ) && isset( $request_data['passw2'] ) && ( trim( $request_data['passw1'] ) != trim( $request_data['passw2'] ) ) )
			return __( "The passwords do not match", "profile-builder" );
	}

    return $message;
}
add_filter( 'wppb_check_form_field_default-repeat-password', 'wppb_check_repeat_password_value', 10, 4 );