<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://whooohq.com
 * @since      1.0.0
 *
 * @package    Whq_Custom_Functionality
 * @subpackage Whq_Custom_Functionality/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Whq_Custom_Functionality
 * @subpackage Whq_Custom_Functionality/includes
 * @author     WhoooHQ <contacto@whooohq.com>
 */
class Whq_Custom_Functionality_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'whq-custom-functionality',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
