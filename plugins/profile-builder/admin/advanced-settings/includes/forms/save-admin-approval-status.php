<?php

$wppb_general_settings = get_option( 'wppb_general_settings' );

if ( isset( $wppb_general_settings['adminApproval'] ) && $wppb_general_settings['adminApproval'] == 'yes' ) {
    add_filter( 'wppb_admin_approval_update_user_status', 'wppb_toolbox_update_admin_approval_status_in_usermeta', 20, 2 );
}

function wppb_toolbox_update_admin_approval_status_in_usermeta( $status, $user_id ){
    switch ( is_array($status) ? reset($status) : NULL ) {
        case 'pending':
            update_user_meta($user_id, 'wppb_approval_status', 'pending');
            break;
        case 'unapproved':
            update_user_meta($user_id, 'wppb_approval_status', 'unapproved');
            break;
        default:
            update_user_meta($user_id, 'wppb_approval_status', 'approved');
    }
    return $status;
}


