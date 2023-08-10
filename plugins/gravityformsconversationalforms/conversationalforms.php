<?php
/*
Plugin Name: Gravity Forms Conversational Forms Add-On
Plugin URI: https://gravityforms.com
Description: Create conversational-style forms from your Gravity Forms.
Version: 1.0.0
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-3.0+
Text Domain: gravityformsconversationalforms
Domain Path: /languages
------------------------------------------------------------------------
Copyright 2023 Rocketgenius Inc.
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses.
*/

defined( 'ABSPATH' ) || die();

// Defines the current version of the Gravity Forms Conversational Forms Add-On.
define( 'GF_CF_VERSION', '1.0.0' );

// Defines the minimum version of Gravity Forms required to run Gravity Forms Conversational Forms Add-On.
define( 'GF_CF_MIN_GF_VERSION', '2.7.11' );

/**
 * Path to CF root folder.
 *
 * @since 1.0
 */
define( 'GF_CF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// After Gravity Forms is loaded, load the Add-On.
add_action( 'gform_loaded', array( 'GF_Conversational_Forms_Bootstrap', 'load_addon' ), 5 );

/**
 * Loads the Gravity Forms Conversational Forms Add-On.
 *
 * Includes the main class and registers it with GFAddOn.
 *
 * @since 1.0
 */
class GF_Conversational_Forms_Bootstrap {

	/**
	 * Loads the required files.
	 *
	 * @since  1.0
	 */
	public static function load_addon() {

		// Requires the class file.
		require_once GF_CF_PLUGIN_PATH . '/class-gf-conversational-forms.php';

		// Require Colors Util
		require_once GF_CF_PLUGIN_PATH . '/includes/util/colors/class-color-modifier.php';

		// Registers the class name with GFAddOn.
		GFAddOn::register( \Gravity_Forms\Gravity_Forms_Conversational_Forms\GF_Conversational_Forms::class );
	}

}
