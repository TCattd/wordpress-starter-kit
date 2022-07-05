<?php

$wppb_general_settings = get_option( 'wppb_general_settings' );

if ( isset( $wppb_general_settings ) && $wppb_general_settings['emailConfirmation'] == 'yes' )
    add_filter('wppb_add_to_user_signup_form_meta', 'wppb_toolbox_capitalize_first_last', 20, 2);
else
    add_filter('wppb_build_userdata', 'wppb_toolbox_capitalize_first_last', 20, 2);

function wppb_toolbox_capitalize_first_last( $meta, $global_request ) {
	if ( isset( $global_request['first_name'] ) )
		$meta['first_name'] = ucwords( $global_request['first_name'] );

	if ( isset($global_request['last_name']) )
		$meta['last_name'] = ucwords( $global_request['last_name'] );

	return $meta;
}
