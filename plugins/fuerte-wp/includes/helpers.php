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
		$admins[$user->user_email] = $user->user_login . '[' . $user->user_email . ']';
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

/**
 * Customizer disable Additional CSS editor.
 */
function fuertewp_customizer_remove_css_editor( $wp_customize ) {
	$wp_customize->remove_section('custom_css');
}

/**
 * REST API restrict access to logged in users only.
 * https://developer.wordpress.org/rest-api/frequently-asked-questions/#require-authentication-for-all-requests
 */
function fuertewp_restapi_loggedin_only( $result ) {
	// If a previous authentication check was applied,
	// pass that result along without modification.
	if ( true === $result || is_wp_error( $result ) ) {
		return $result;
	}

	// Exclude JWT auth token endpoints URLs
	if ( false !== stripos( $_SERVER['REQUEST_URI'], 'jwt-auth' ) ) {
		return $result;
	}

	// No authentication has been performed yet.
	// Return an error if user is not logged in.
	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_not_logged_in',
			__( 'You are not currently logged in.' ),
			array( 'status' => 401 )
		);
	}

	// Our custom authentication check should have no effect
	// on logged-in requests
	return $result;
}

/**
 * Remove WP XML-RPC methods, to fully disable it
 *
 * https://www.scottbrownconsulting.com/2020/03/two-ways-to-fully-disable-wordpress-xml-rpc/
 */
function fuertewp_remove_xmlrpc_methods( $methods ) {
	return array();
}

