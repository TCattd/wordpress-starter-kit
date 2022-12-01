<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/*
Plugin Name: Gravity Forms Partial Entries Add-On
Plugin URI: https://gravityforms.com
Description: Adds support for partial submissions.
Version: 1.7
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-2.0+
Text Domain: gravityformspartialentries
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2016-2020 Rocketgenius Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'GF_PARTIAL_ENTRIES_VERSION', '1.7' );

add_action( 'gform_loaded', array( 'GF_Partial_Entries_Bootstrap', 'load' ), 5 );

class GF_Partial_Entries_Bootstrap {

	public static function load() {
		require_once( 'class-gf-partial-entries.php' );
		GFAddOn::register( 'GF_Partial_Entries' );
	}
}
