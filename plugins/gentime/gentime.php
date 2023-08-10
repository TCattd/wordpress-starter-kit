<?php
/**
 * Plugin Name: GenTime
 * Plugin URI: https://wordpress.org/plugins/gentime/
 * Description: GenTime shows the page generation time in the WordPress admin bar.
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * Version: 1.1.0
 * License: GLPv3
 * Text Domain: gentime
 * Domain Path: /language
 */

add_action( 'plugins_loaded', 'gentime_locale_init' );
/**
 * Loads plugin locale 'gentime'.
 * File located in plugin folder gentime/language/
 *
 * @since 1.0.0
 */
function gentime_locale_init() {
	load_plugin_textdomain(
		'gentime',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/language'
	);
}

add_action( 'admin_bar_menu', 'gentime_admin_item', 912 );
/**
 * Adds admin node for the generation time.
 *
 * @since 1.0.0
 * @since 1.1.0 Added timer_float() test, which is more accurate.
 * @global object $wp_admin_bar
 *
 * @return void
 */
function gentime_admin_item() {

	if ( ! gentime_can_run() ) return;

	/**
	 * @param int $decimals The generation time decimals amount
	 * @since 1.0.0
	 */
	$decimals = (int) apply_filters( 'gentime_decimals', 3 );

	if ( function_exists( 'timer_float' ) ) {
		$time = timer_float();
		$time = number_format_i18n( $time, $decimals );
	} else {
		$time = timer_stop( 0, $decimals );
	}

	$args = [
		'id'    => 'gentime',
		'title' => '<span class="ab-icon"></span><span class="ab-label">' . $time . esc_html_x( 's', 'seconds', 'gentime' ) . '</span>',
		'href'  => '',
		'meta'  => [
			'title' => esc_attr__( 'Page Generation Time', 'gentime' ),
		],
	];

	$GLOBALS['wp_admin_bar']->add_node( $args );
}

add_action( 'wp_head', 'gentime_echo_css' );
add_action( 'admin_head', 'gentime_echo_css' );
/**
 * Echos a single line to output the clock in the admin bar next to the gentime.
 *
 * @since 1.0.0
 */
function gentime_echo_css() {
	gentime_can_run()
		and print( '<style>#wp-admin-bar-gentime .ab-icon:before{font-family:"dashicons";content:"\f469";top:2px}</style>' );
}

/**
 * Checks whether we can run the plugin. Memoizes the return value.
 *
 * @since 1.0.2
 *
 * @return bool
 */
function gentime_can_run() {
	static $cache = null;
	return isset( $cache )
		? $cache
		: ( $cache = function_exists( 'is_admin_bar_showing' ) && is_admin_bar_showing() && current_user_can( gentime_capability() ) );
}

/**
 * Returns the minimum gentime usage role.
 *
 * @since 1.0.0
 *
 * @return string
 */
function gentime_capability() {
	/**
	 * @since 1.0.0
	 * @param string $capability The minimum role for the admin bar item is shown to the user.
	 */
	return (string) apply_filters( 'gentime_minimum_role', 'install_plugins' );
}
