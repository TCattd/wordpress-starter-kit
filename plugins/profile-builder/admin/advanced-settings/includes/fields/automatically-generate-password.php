<?php

$wppb_general_settings = get_option( 'wppb_general_settings' );

if ( isset( $wppb_general_settings ) && $wppb_general_settings['emailConfirmation'] == 'yes' ) {
    add_filter( 'wppb_register_user_email_message_with_admin_approval', 'wppb_toolbox_ec_generate_password', 20, 5 );
    add_filter( 'wppb_register_user_email_message_without_admin_approval', 'wppb_toolbox_ec_generate_password', 20, 5 );
}

add_filter( 'wppb_check_form_field_default-password', 'wppb_toolbox_check_password_value', 20, 4 );
add_filter( 'wppb_check_form_field_default-repeat-password', 'wppb_toolbox_check_password_value', 20, 4 );
function wppb_toolbox_check_password_value( $message, $field, $request_data, $form_location ){
	if ( $form_location == 'register' )
		return;

    return $message;
}

add_filter( 'wppb_register_password', '__return_empty_string', 10, 6 );
add_filter( 'wppb_register_repeat_password', '__return_empty_string', 10, 6 );
add_filter( 'wppb_send_password_in_default_email_message', '__return_true' );

add_filter('wppb_send_credentials_checkbox_logic', 'wppb_toolbox_send_credentials', 10, 2);
function wppb_toolbox_send_credentials($requestdata, $form){
	return '<input id="send_credentials_via_email" type="hidden" name="send_credentials_via_email" value="sending" />';
}

add_filter( 'wppb_build_userdata', 'wppb_toolbox_generate_password', 20, 2 );
function wppb_toolbox_generate_password( $userdata, $global_request ) {
	if ( $global_request['action'] == 'register' ) {
		$userdata['user_pass'] = wp_generate_password();

		return $userdata;
	}

	return $userdata;
}

function wppb_toolbox_ec_generate_password( $content, $email, $password, $user_message_subject, $context ) {
    if ( empty( $email ) ) return $content;

    $user = get_user_by( 'email', $email );

    if ( $user === false ) return $content;

    $password = wp_generate_password();

    wp_set_password( $password, $user->ID );

    $content = str_replace( 'Your selected password at signup', $password, $content );

    return $content;
}
