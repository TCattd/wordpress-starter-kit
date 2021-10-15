<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://actitud.xyz
 * @since      1.3.0
 *
 * @package    Fuerte_Wp
 * @subpackage Fuerte_Wp/includes
 */

// No access outside WP
defined( 'ABSPATH' ) || die();

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.3.0
 * @package    Fuerte_Wp
 * @subpackage Fuerte_Wp/includes
 * @author     Esteban Cuevas <esteban@attitude.cl>
 */
class Fuerte_Wp_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.3.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'fuerte-wp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
