<?php

use Gravity_Forms\Gravity_Forms_Geolocation\GF_Geolocation;

/*
Plugin Name: Gravity Forms Geolocation Add-On
Plugin URI: https://gravityforms.com
Description: Allows for geolocation suggestions when filling an address field.
Version: 1.0
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-3.0+
Text Domain: gravityformsgeolocation
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2022 Rocketgenius Inc.

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

// Defines the current version of the Gravity Forms Geolocation Add-On.
define( 'GF_GEOLOCATION_VERSION', '1.0' );

// Defines the minimum version of Gravity Forms required to run Gravity Forms Geolocation Add-On.
define( 'GF_GEOLOCATION_MIN_GF_VERSION', '2.6' );

// After Gravity Forms is loaded, load the Add-On.
add_action( 'gform_loaded', array( 'GF_Geolocation_Bootstrap', 'load_addon' ), 5 );

/**
 * Loads the Gravity Forms Geolocation Add-On.
 *
 * Includes the main class and registers it with GFAddOn.
 *
 * @since 1.0
 */
class GF_Geolocation_Bootstrap {

	/**
	 * Loads the required files.
	 *
	 * @since  1.0
	 */
	public static function load_addon() {

		// Requires the class file.
		require_once plugin_dir_path( __FILE__ ) . '/class-gf-geolocation.php';

		// Registers the class name with GFAddOn.
		GFAddOn::register( GF_Geolocation::class );
	}

}

/**
 * Returns an instance of the GF_Geolocation class
 *
 * @since  1.0
 * @return GF_Geolocation An instance of the GF_Geolocation class
 */
function gf_geolocation() {
	if ( class_exists( 'Gravity_Forms\Gravity_Forms_Geolocation\GF_Geolocation' ) ) {
		return GF_Geolocation::get_instance();
	} else {
		return null;
	}
}
