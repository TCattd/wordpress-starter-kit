<?php

/**
 *
 * @link              https://nitin247.com/plugin/remove-social-id/
 * @since             1.0
 * @package           Remove_Social_ID
 *
 * @wordpress-plugin
 * Plugin Name:       Remove Social ID for WP
 * Plugin URI:        https://wordpress.org/plugins/remove-social-id/
 * Description:       Remove Social ID for WordPress removes querystring fblid and redirects the URL for your WordPress site.
 * Version:           1.0
 * Author:            Nitin Prakash
 * Author URI:        https://nitin247.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       remove_social_id
 * Domain Path:       /languages
 * Requires PHP:      5.6
 * WC requires at least: 5.0
 * WC tested up to: 7.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || die( 'WordPress Error! Opening plugin file directly' );

define( 'REMOVE_CLID_FOR_WORDPRESS_VERSION', '1.0.0' );

function remove_social_id_redirect_page() {

	if ( isset( $_SERVER['HTTPS'] ) &&
		( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ) ||
		isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) &&
		$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	$currenturl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	if ( strpos( $currenturl, 'fbclid' ) ) {
		$stripped_url = remove_social_id_strip_clid( $currenturl );
		wp_redirect( $stripped_url );
		exit;
	}

}

add_action( 'template_redirect', 'remove_social_id_redirect_page', 5 );

function remove_social_id_strip_clid( $url ) {
		$patterns = array(
			'/(\?|&)fbclid=[^&]*$/' => '',
			'/\?fbclid=[^&]*&/'     => '?',
			'/&fbclid=[^&]*&/'      => '&',
		);

		$search  = array_keys( $patterns );
		$replace = array_values( $patterns );

		return preg_replace( $search, $replace, $url );
}

