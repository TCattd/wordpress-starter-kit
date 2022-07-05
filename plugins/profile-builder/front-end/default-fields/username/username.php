<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_username_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_username_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	
	$input_value = ( ( $form_location == 'edit_profile' ) ? get_the_author_meta( 'user_login', $user_id ) : '' );
	
	$input_value = ( ( trim( $input_value ) == '' ) ? $field['default-value'] : $input_value );
		
	$input_value = ( isset( $request_data['username'] ) ? trim( $request_data['username'] ) : $input_value );

	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';
		
		$readonly = ( ( $form_location == 'edit_profile' ) ? ' disabled="disabled"' : '' );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="username">'.$item_title.$error_mark.'</label>
			<input class="text-input default_field_username '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="username" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="username" value="'. esc_attr( $input_value ) .'" '.$readonly.' '. $extra_attr .'/>';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';
	}
		
	return apply_filters( 'wppb_'.$form_location.'_username', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-username', 'wppb_username_handler', 10, 6 );


/* handle field validation */
function wppb_check_username_value( $message, $field, $request_data, $form_location ){
	global $wpdb;

    if( $field['required'] == 'Yes' ){
        if( ( isset( $request_data['username'] ) && ( trim( $request_data['username'] ) == '' ) ) || ( $form_location == 'register' && !isset( $request_data['username'] ) ) ){
            return wppb_required_field_error($field["field-title"]);
        }

    }

    if( !empty( $request_data['username'] ) ){
        if( $form_location == 'register' ) {
            if( username_exists($request_data['username'] ) ){
                return __('This username already exists.', 'profile-builder') . '<br/>' . __('Please try a different one!', 'profile-builder');
            }
            if (!validate_username($request_data['username'])) {
                return __('This username is invalid because it uses illegal characters.', 'profile-builder') . '<br/>' . __('Please enter a valid username.', 'profile-builder');
            }
        }

        $wppb_generalSettings = get_option('wppb_general_settings');
        if ( $wppb_generalSettings['emailConfirmation'] == 'yes'  ){

            if( is_multisite() && $request_data['username'] != preg_replace( '/\s+/', '', $request_data['username'] ) ){
                return __( 'This username is invalid because it uses illegal characters.', 'profile-builder' ) .'<br/>'. __( 'Please enter a valid username.', 'profile-builder' );
            }

            $userSignup = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."signups WHERE user_login = %s", $request_data['username'] ) );
            if ( !empty( $userSignup ) ){
                return __( 'This username is already reserved to be used soon.', 'profile-builder') .'<br/>'. __( 'Please try a different one!', 'profile-builder' );
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-username', 'wppb_check_username_value', 10, 4 );


/* handle field save */
function wppb_userdata_add_username( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - Username', $form_args ) ) {
        if (isset($global_request['username']))
            $userdata['user_login'] = sanitize_user(trim($global_request['username']));
    }

	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_username', 10, 3 );