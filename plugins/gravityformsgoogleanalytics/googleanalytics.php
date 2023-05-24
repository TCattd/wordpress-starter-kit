<?php
/*
Plugin Name: Gravity Forms Google Analytics Add-On
Plugin URI: http://gravityforms.com
Description: Integrates Gravity Forms with Google Analytics and Tag Manager
Version: 1.3
Author: Gravity Forms
Author URI: http://gravityforms.com
License: GPL-3.0+
Text Domain: gravityformsgoogleanalytics
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2019-2023 Rocketgenius Inc.

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

use Gravity_Forms\Gravity_Forms_Google_Analytics\GF_Google_Analytics;

// Defines the current version of the Gravity Forms Google Analytics Add-On.
define( 'GF_GOOGLE_ANALYTICS_VERSION', '1.3' );

// Defines the minimum version of Gravity Forms required to run Gravity Forms Google Analytics.
define( 'GF_GOOGLE_ANALYTICS_MIN_GF_VERSION', '2.5.7' );

// Allow advanced users to change the Gravity Forms Google Analytics Add-on API location.
// This will allow others to use their own Google Analytics API account to process conversions and permissions.
if ( ! defined( 'GF_GOOGLE_ANALYTICS_GA_AUTH_API' ) ) {
	define( 'GF_GOOGLE_ANALYTICS_GA_AUTH_API', 'https://dev.gravityapi.com/wp-json/gravityapi/v1/auth/googleanalytics' );
}

// After GF is loaded, load the add-on.
add_action( 'gform_loaded', array( 'GF_Google_Analytics_Bootstrap', 'load_addon' ), 5 );

/**
 * Loads the Gravity Forms Google Analytics Add-On.
 *
 * Includes the main class and registers it with GFAddOn.
 *
 * @since 1.0
 */
class GF_Google_Analytics_Bootstrap {

	/**
	 * Loads the required files.
	 *
	 * @since  1.0
	 */
	public static function load_addon() {

		// Requires the class file.
		require_once plugin_dir_path( __FILE__ ) . '/class-gf-google-analytics.php';

		// Registers the class name with GFAddOn.
		GFAddOn::register( 'Gravity_Forms\Gravity_Forms_Google_Analytics\GF_Google_Analytics' );
	}
}

/**
 * Returns an instance of the GF_Google_Analytics class
 *
 * @since  1.0
 * @return GF_Google_Analytics An instance of the GF_Google_Analytics class
 */
function gf_google_analytics() {
	if ( class_exists( 'Gravity_Forms\Gravity_Forms_Google_Analytics\GF_Google_Analytics' ) ) {
		return GF_Google_Analytics::get_instance();
	} else {
		return null;
	}
}
