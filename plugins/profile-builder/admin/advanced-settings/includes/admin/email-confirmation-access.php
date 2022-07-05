<?php

function wppb_toolbox_rehook_email_confirmation_page() {
	remove_action( 'admin_menu', 'wppb_add_ec_submenu_page' );

    add_submenu_page( 'users.php', 'Unconfirmed Email Address', 'Unconfirmed Email Address', 'delete_users', 'unconfirmed_emails', 'wppb_unconfirmed_email_address_custom_menu_page' );
    remove_submenu_page( 'users.php', 'unconfirmed_emails' ); //hide the page in the admin menu
}
add_action( 'admin_menu', 'wppb_toolbox_rehook_email_confirmation_page', 5 );

function wppb_change_email_confirmation_user_capability($cap){
    return 'delete_users';
}
add_filter( 'wppb_email_confirmation_user_capability', 'wppb_change_email_confirmation_user_capability');