<?php

$delay = wppb_toolbox_get_settings( 'forms', 'redirect-delay-timer' );

if ( is_numeric( $delay ) ) {
    add_filter( 'wppb_edit_profile_redirect_delay', 'wppb_toolbox_register_redirect_delay_duration', 10, 3 );
    add_filter( 'wppb_register_redirect_delay', 'wppb_toolbox_register_redirect_delay_duration', 10, 3 );
}

function wppb_toolbox_register_redirect_delay_duration( $default, $user, $args ) {
    $delay = wppb_toolbox_get_settings( 'forms', 'redirect-delay-timer' );

    if ( $delay === false )
        return $default;


	return $delay;
}
