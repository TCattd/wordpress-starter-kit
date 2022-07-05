<?php

function wppb_toolbox_rehook_admin_approval_page() {
	remove_action( 'admin_menu', 'wppb_add_au_submenu_page' );

	add_submenu_page( 'users.php', 'Admin Approval', 'Admin Approval', 'delete_users', 'admin_approval', 'wppb_approved_unapproved_users_custom_menu_page' );
	remove_submenu_page( 'users.php', 'admin_approval' );
}
add_action( 'admin_menu', 'wppb_toolbox_rehook_admin_approval_page', 5 );

function wppb_change_admin_approval_user_capability($cap){
    return 'delete_users';
}
add_filter( 'wppb_admin_approval_user_capability', 'wppb_change_admin_approval_user_capability');