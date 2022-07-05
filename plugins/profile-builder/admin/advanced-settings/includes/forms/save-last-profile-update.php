<?php

add_action( 'wppb_edit_profile_success', 'wppb_toolbox_save_last_update_date', 20, 3 );
function wppb_toolbox_save_last_update_date( $http_request, $form_name, $user_id ) {
    if ( empty($user_id) || !function_exists( 'wp_timezone' ) ) return;

    $now = new DateTime( 'now', wp_timezone() );
	update_user_meta( $user_id, 'last_profile_update_date', apply_filters( 'wppb_convert_date_format', $now->format( 'Y-m-d H:i:s' ) ) );
}
