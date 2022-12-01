<?php
/*
Plugin Name: Gravity Forms reCAPTCHA Add-On
Plugin URI: https://gravityforms.com
Description: Enhance Gravity Forms with support for Google reCAPTCHA.
Version: 1.1
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-3.0+
Text Domain: gravityformsrecaptcha
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2021 Rocketgenius Inc.

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

use Gravity_Forms\Gravity_Forms_RECAPTCHA\GF_RECAPTCHA;

// Defines the current version of the Gravity Forms Recaptcha Add-On.
define( 'GF_RECAPTCHA_VERSION', '1.1' );

// Defines the minimum version of Gravity Forms required to run Gravity Forms Recaptcha Add-On.
define( 'GF_RECAPTCHA_MIN_GF_VERSION', '2.5-rc-1' );

// After Gravity Forms is loaded, load the Add-On.
add_action( 'gform_loaded', array( 'GF_RECAPTCHA_Bootstrap', 'load_addon' ), 5 );

/**
 * Loads the Gravity Forms reCAPTCHA Add-On.
 *
 * Includes the main class and registers it with GFAddOn.
 *
 * @since 1.0
 */
class GF_RECAPTCHA_Bootstrap {

	/**
	 * Loads the required files.
	 *
	 * @since  1.0
	 */
	public static function load_addon() {

		// Requires the class file.
		require_once plugin_dir_path( __FILE__ ) . '/class-gf-recaptcha.php';

		// Registers the class name with GFAddOn.
		GFAddOn::register( 'Gravity_Forms\Gravity_Forms_RECAPTCHA\GF_RECAPTCHA' );
	}

}

/**
 * Returns an instance of the GF_RECAPTCHA class
 *
 * @since  1.0
 *
 * @return GF_RECAPTCHA|bool An instance of the GF_RECAPTCHA class
 */
function gf_recaptcha() {
	return class_exists( 'Gravity_Forms\Gravity_Forms_RECAPTCHA\GF_RECAPTCHA' ) ? GF_RECAPTCHA::get_instance() : false;
}
