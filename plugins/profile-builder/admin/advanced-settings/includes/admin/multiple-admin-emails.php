<?php
function wppb_as_overwrite_mail_to( $to, $context ){
    $admin_email = get_option( 'admin_email' );
    $wppb_toolbox_admin_settings = get_option('wppb_toolbox_admin_settings');
    $wppb_admin_emails = array(
        'email_admin_recover_success',
        'wppb_epaa_admin_email',
        'email_admin_approve',
	    'email_admin_new_subscriber'
    );

    if( isset( $wppb_toolbox_admin_settings['admin-emails'] ) &&
        !empty( $wppb_toolbox_admin_settings['admin-emails'] ) &&
        $admin_email == $to &&
        in_array( $context, $wppb_admin_emails, true )){
        return $wppb_toolbox_admin_settings['admin-emails'];
    } else {
        return $to;
    }
}
add_filter('wppb_send_email_to', 'wppb_as_overwrite_mail_to', 10, 2);

