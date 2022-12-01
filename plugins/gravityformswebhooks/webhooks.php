<?php
/**
Plugin Name: Gravity Forms Webhooks Add-On
Plugin URI: https://gravityforms.com
Description: Integrates Gravity Forms with third party services using custom webhooks.
Version: 1.5
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-2.0+
Text Domain: gravityformswebhooks
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2012-2020 Rocketgenius, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/

defined( 'ABSPATH' ) || die();

define( 'GF_WEBHOOKS_VERSION', '1.5' );

// If Gravity Forms is loaded, bootstrap the Webhooks Add-On.
add_action( 'gform_loaded', array( 'GF_Webhooks_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Webhooks_Bootstrap
 *
 * Handles the loading of the Webhooks Add-On and registers with the Add-On Framework.
 */
class GF_Webhooks_Bootstrap {

	/**
	 * If the Feed Add-On Framework exists, Webhooks Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-webhooks.php' );

		GFAddOn::register( 'GF_Webhooks' );

	}

}

/**
 * Returns an instance of the GF_Webhooks class
 *
 * @see    GF_Webhooks::get_instance()
 * @return object GF_Webhooks
 */
function gf_webhooks() {
	return GF_Webhooks::get_instance();
}
