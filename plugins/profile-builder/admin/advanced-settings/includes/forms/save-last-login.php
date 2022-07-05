<?php

add_action( 'wp_login', 'wppb_toolbox_count_user_logins', 20, 2 );
function wppb_toolbox_count_user_logins( $user_login, $user ) {
	if ( empty($user->ID) || !function_exists( 'wp_timezone' ) ) return;

    $now = new DateTime( 'now', wp_timezone() );
    update_user_meta( $user->ID, 'last_login_date', apply_filters( 'wppb_convert_date_format', $now->format( 'Y-m-d H:i:s' ) ) );
}
