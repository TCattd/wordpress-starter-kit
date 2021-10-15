<?php
/**
 * Fuerte-WP Helpers
 *
 * @link       https://actitud.xyz
 * @since      1.3.0
 *
 * @package    Fuerte_Wp
 * @subpackage Fuerte_Wp/includes
 * @author     Esteban Cuevas <esteban@attitude.cl>
 */

// No access outside WP
defined( 'ABSPATH' ) || die();

/**
 * Get WordPress admin users
 */
function fuertewp_get_admin_users() {
	$users  = get_users( array( 'role__in' => array( 'administrator' ) ) );
	$admins = [];

	foreach ( $users as $user ) {
		$admins[$user->user_email] = $user->user_email;
	}

	return $admins;
}

/**
 * Get a list of WordPress roles
 */
function fuertewp_get_wp_roles() {
	global $wp_roles;

	$roles          = $wp_roles->roles;
	// https://developer.wordpress.org/reference/hooks/editable_roles/
	$editable_roles = apply_filters( 'editable_roles', $roles );

	// We only need the role slug (id) and name
	$returned_roles = [];

	foreach( $editable_roles as $id => $role ) {
		$returned_roles[$id] = $role['name'];
	}

	return $returned_roles;
}

/**
 * Check if an option exists
 *
 * https://core.trac.wordpress.org/ticket/51699
 */
function fuertewp_option_exists( $option_name, $site_wide = false ) {
	global $wpdb;

	return $wpdb->query( $wpdb->prepare( "SELECT * FROM ". ($site_wide ? $wpdb->base_prefix : $wpdb->prefix). "options WHERE option_name ='%s' LIMIT 1", $option_name ) );
}
