<?php
/*
Plugin Name: Gravity Forms Chained Selects Add-On
Plugin URI: https://gravityforms.com
Description: Adds the powerful Chained Selects field type, allowing you to chain multiple Drop Downs together (e.g. Make, Model, Year).
Version: 1.5
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-2.0+
Text Domain: gravityformschainedselects
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2009 - 2020 Rocketgenius Inc.

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
*/

defined( 'ABSPATH' ) || die();

define( 'GF_CHAINEDSELECTS_VERSION', '1.5' );

add_action( 'gform_loaded', array( 'GF_ChainedSelects_Bootstrap', 'load' ), 5 );

class GF_ChainedSelects_Bootstrap {

	public static function load() {
		
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}
		
		require_once( 'class-gf-chainedselects.php' );
		
		GFAddOn::register( 'GFChainedSelects' );
		
	}

}

function gf_chained_selects() {
	return GFChainedSelects::get_instance();
}