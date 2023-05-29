<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Delete user metas
$users = get_users( 'role=administrator' );
foreach ( $users as $user ) {
	delete_user_meta( $user->ID, 'dcf_dismissed_notices' );
}
