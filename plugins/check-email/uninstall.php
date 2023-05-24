<?php

// exit if WordPress is not uninstalling the plugin.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( is_multisite() ) {
	$sites = get_sites();

	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );
		check_email_delete_db_data();
		restore_current_blog();
	}
} else {
	check_email_delete_db_data();
}

function check_email_delete_db_data() {
	global $wpdb;

	$remove_data_on_uninstall = false;

	$option = get_option( 'check-email-log-core' );
	if ( is_array( $option ) && array_key_exists( 'remove_on_uninstall', $option ) &&
	     'true' === strtolower( $option['remove_on_uninstall'] ) ) {

		$remove_data_on_uninstall = true;
	}

	// This is hardcoded on purpose, since the entire plugin is not loaded during uninstall.
	$table_name = $wpdb->prefix . 'check_email_log';

	if ( $remove_data_on_uninstall ) {
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) == $table_name ) {
			$wpdb->query( "DROP TABLE $table_name" );
		}

		delete_option( 'check-email-log-db' );
		delete_option( 'check-email-log-core' );

		$roles = get_editable_roles();
		foreach ( $roles as $role_name => $role_obj ) {
			$role = get_role( $role_name );

			if ( ! is_null( $role ) ) {
				$role->remove_cap( 'manage_check_email' );
			}
		}
	}
}
