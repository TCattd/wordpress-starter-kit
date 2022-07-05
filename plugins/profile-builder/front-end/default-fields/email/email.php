<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_email_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_email_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	$input_value = '';
	if( $form_location == 'edit_profile' )
		$input_value = get_the_author_meta( 'user_email', $user_id );
	
	if ( trim( $input_value ) == '' )
		$input_value = $field['default-value'];
		
	$input_value = ( isset( $request_data['email'] ) ? trim( $request_data['email'] ) : $input_value );
	// filter must be applied on the $input_value so that the value returned to the form can be corrected too
	$input_value = apply_filters( 'wppb_before_processing_email_from_forms' , $input_value );

	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $email_input_status = apply_filters( 'wppb_set_input_status', '' );

        $output = '
			<label for="email">'.$item_title.$error_mark.'</label>
			<input class="text-input default_field_email '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="email" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="email" id="email" value="'. esc_attr( $input_value ) .'" '. $extra_attr .' '. (( $form_location != 'register' ) ? $email_input_status : '') .' />';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';

        if( $form_location == 'edit_profile' && is_user_logged_in() ) {
            if ( $email_input_status == 'enabled' ) {
                $output .= '<span class="wppb-description-delimiter">' . __('If you change this, we will send you an email at your new address to confirm it. <br /><strong>The new address will not become active until confirmed.</strong>', 'profile-builder') . '</span>';
            }
        else if ( $email_input_status == 'disabled' ) {
            $current_url = wppb_curpageurl();
            $pending_request_nonce = wp_create_nonce( 'wppb_email_change_action_nonce' );
            $arr_params = array( 'wppb_email_change_action' => 'cancel_pending_email_address_change','_wpnonce' => $pending_request_nonce );
            $cancel_request_url = add_query_arg($arr_params, $current_url);
            $pending_new_email_address = apply_filters('wppb_new_email_address','');

                $output .= '<span class="wppb-description-delimiter">' . sprintf(__('There is a pending change request of your email to: %s', 'profile-builder'), '<strong>' . $pending_new_email_address . '</strong>');
                $output .= '<a style="float: right;" href="' . esc_url($cancel_request_url) . '">' . __('Cancel request', 'profile-builder') . '</a></span>';
            }
        }


	}
		
	return apply_filters( 'wppb_'.$form_location.'_email', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-e-mail', 'wppb_email_handler', 10, 6 );


/* handle field validation */
function wppb_check_email_value( $message, $field, $request_data, $form_location ){
	global $wpdb;
	// apply filter to allow stripping slashes if necessary
    if ( isset( $request_data['email'] ) ) {
        $request_data['email'] = apply_filters('wppb_before_processing_email_from_forms', $request_data['email']);
        if ((isset($request_data['email']) && (trim($request_data['email']) == '')) && ($field['required'] == 'Yes'))
            return wppb_required_field_error($field["field-title"]);

        if (isset($request_data['email']) && !is_email(trim($request_data['email']))) {
            return __('The email you entered is not a valid email address.', 'profile-builder');
        }

        if (empty($request_data['email'])) {
            return __('You must enter a valid email address.', 'profile-builder');
        }

        $wppb_generalSettings = get_option('wppb_general_settings');
        if (isset($wppb_generalSettings['emailConfirmation']) && ($wppb_generalSettings['emailConfirmation'] == 'yes')) {
            $user_signup = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s AND active=0", $request_data['email']));

            if (!empty($user_signup)) {
                if ($form_location == 'register') {
                    return __('This email is already reserved to be used soon.', 'profile-builder') . '<br/>' . __('Please try a different one!', 'profile-builder');
                } else if ($form_location == 'edit_profile') {
                    $current_user = wp_get_current_user();

                    if (!current_user_can('edit_users')) {
                        if ($current_user->user_email != $request_data['email'])
                            return __('This email is already reserved to be used soon.', 'profile-builder') . '<br/>' . __('Please try a different one!', 'profile-builder');
                    }
                }
            }
        }

        $users = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE user_email = %s", $request_data['email']));

        if (!empty($users)) {
            if ($form_location == 'register')
                return __('This email is already in use.', 'profile-builder') . '<br/>' . __('Please try a different one!', 'profile-builder');

            if ($form_location == 'edit_profile') {
                if( isset( $_SERVER['HTTP_REFERER'] ) )
                    $url_parts = parse_url(  esc_url_raw( $_SERVER['HTTP_REFERER'] ) );

                if (isset($url_parts['query'])) {
                    parse_str($url_parts['query'], $query);
                }

                if (isset($_GET['edit_user']) && !empty($_GET['edit_user'])) {
                    $current_user_id = absint($_GET['edit_user']);
                } elseif (defined('DOING_AJAX') && DOING_AJAX && isset($query['edit_user']) && !empty($query['edit_user'])) {
                    $current_user_id = $query['edit_user'];
                } else {
                    $current_user = wp_get_current_user();
                    $current_user_id = $current_user->ID;
                }
                foreach ($users as $user)
                    if ($user->ID != $current_user_id)
                        return __('This email is already in use.', 'profile-builder') . '<br/>' . __('Please try a different one!', 'profile-builder');
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-e-mail', 'wppb_check_email_value', 10, 4 );

/* handle field save */
function wppb_userdata_add_email( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - E-mail', $form_args ) ) {
        // apply filter to allow stripping slashes if necessary
        if (isset($global_request['email'])) {
            $global_request['email'] = apply_filters('wppb_before_processing_email_from_forms', $global_request['email']);
            $userdata['user_email'] = sanitize_text_field(trim($global_request['email']));
        }
    }

	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_email', 10, 3 );