<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_password_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){


	$item_title = apply_filters( 'wppb_'.$form_location.'_password_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );

	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? ( $form_location != 'edit_profile' ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' ) : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="passw1">' . $item_title.$error_mark . '</label>
			<input class="text-input '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="passw1" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="password" id="passw1" value="" autocomplete="off" '. $extra_attr .'/>';

        /* add the HTML for the visibility toggle */
        $output .= wppb_password_visibility_toggle_html();

        if( ! empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .' '. wppb_password_length_text() .' '. wppb_password_strength_description() .'</span>';
        else
            $output .= '<span class="wppb-description-delimiter">'. wppb_password_length_text() .' '. wppb_password_strength_description() .'</span>';

        /* if we have active the password strength checker */
        $output .= wppb_password_strength_checker_html();

	}
		
	return apply_filters( 'wppb_'.$form_location.'_password', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-password', 'wppb_password_handler', 10, 6 );

/* handle field validation */
function wppb_check_password_value( $message, $field, $request_data, $form_location ){

	if ( $form_location == 'register' ){
		if ( ( isset( $request_data['passw1'] ) && ( trim( $request_data['passw1'] ) == '' ) ) && ( $field['required'] == 'Yes' ) )
			return wppb_required_field_error($field["field-title"]);
		
		elseif ( !isset( $request_data['passw1'] ) && ( $field['required'] == 'Yes' ) )
			return wppb_required_field_error($field["field-title"]);
	}

    if ( isset( $request_data['passw1'] ) && trim( $request_data['passw1'] ) != '' ){
        $wppb_generalSettings = get_option( 'wppb_general_settings' );

        if( wppb_check_password_length( $request_data['passw1'] ) )
            return '<br/>'. sprintf( __( "The password must have the minimum length of %s characters", "profile-builder" ), $wppb_generalSettings['minimum_password_length'] );


        if( wppb_check_password_strength() ){
            return '<br/>' . sprintf( __( "The password must have a minimum strength of %s", "profile-builder" ), wppb_check_password_strength() );
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-password', 'wppb_check_password_value', 10, 4 );

/* handle field save */
function wppb_userdata_add_password( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - Password', $form_args ) ) {
        if (isset($global_request['passw1']) && (trim($global_request['passw1']) != ''))
            $userdata['user_pass'] = trim($global_request['passw1']);
    }

	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_password', 10, 3 );