<?php
/*
Plugin Name: Reveal IDs
Version: 1.6.1
Plugin URI: https://www.schloebe.de/wordpress/reveal-ids-for-wp-admin-25-plugin/
Description: Reveals hidden IDs in Admin interface that have been removed with WordPress 2.5 (formerly known as Entry IDs in Manage Posts/Pages View for WP 2.5). See <a href="options-general.php?page=reveal-ids-for-wp-admin-25/reveal-ids-for-wp-admin-25.php">options page</a> for information.
Author: Oliver Schl&ouml;be
Author URI: https://www.schloebe.de/
Text Domain: reveal-ids-for-wp-admin-25
Domain Path: /languages

Copyright 2008-2023 Oliver Schlöbe (email : scripts@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The main plugin file
 *
 * @package WordPress_Plugins
 * @subpackage RevealIDsForWPAdmin
 */


/**
 * Define the plugin version
 */
define("RIDWPA_VERSION", "1.6.1");


/**
 * Define the global var RIDWPAISWP30, returning bool if WP 3.0 or higher is running
 */
define('RIDWPAISWP30', version_compare($GLOBALS['wp_version'], '2.9.999', '>'));


/**
* The RevealIDsForWPAdmin class
*
* @package 		WordPress_Plugins
* @subpackage 	RevealIDsForWPAdmin
* @since 		1.3.0
* @author 		scripts@schloebe.de
*/
class RevealIDsForWPAdmin {

	/**
	 * The textdomain_loaded var
	 *
	 * @var 		bool
	 * @since 		1.3.0
	 */
	var $textdomain_loaded;

	/**
 	* The RevealIDsForWPAdmin class constructor
 	* initializing required stuff for the plugin
 	*
	* PHP 5 Constructor
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function __construct() {
		$this->textdomain_loaded = false;

		if ( !RIDWPAISWP30 ) {
			add_action('admin_notices', array(&$this, 'require_wpversion_message'));
			return;
		}

		add_action('plugins_loaded', array(&$this, 'load_textdomain'));
		add_action('admin_init', array(&$this, 'init'));

		add_action('admin_head', array(&$this, 'add_css'));
	}



	/**
 	* The RevealIDsForWPAdmin class constructor
 	* initializing required stuff for the plugin
 	*
	* PHP 4 Compatible Constructor
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function RevealIDsForWPAdmin() {
		$this->__construct();
	}



	/**
 	* Initialize and load the plugin stuff
 	*
 	* @since 		1.3.0
 	* @uses 		$pagenow
 	* @author 		scripts@schloebe.de
 	*/
	function init() {
		global $wpversion, $pagenow;
		if ( !function_exists("add_action") ) return;

		if( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == 'reveal-ids-for-wp-admin-25/reveal-ids-for-wp-admin-25.php' )
			require_once(dirname (__FILE__) . '/' . 'authorplugins.inc.php');

		add_filter('manage_media_columns', array(&$this, 'column_add'));
		add_action('manage_media_custom_column', array(&$this, 'column_value'), 10, 2);

		add_filter('manage_link-manager_columns', array(&$this, 'column_add'));
		add_action('manage_link_custom_column', array(&$this, 'column_value'), 10, 2);

		add_action('manage_edit-link-categories_columns', array(&$this, 'column_add'));
		add_filter('manage_link_categories_custom_column', array(&$this, 'column_return_value'), 100, 3);

		foreach( get_taxonomies() as $taxonomy ) {
			if( isset($taxonomy) ) {
				add_action("manage_edit-" . $taxonomy . "_columns", array(&$this, 'column_add'));
				add_filter("manage_" . $taxonomy . "_custom_column", array(&$this, 'column_return_value'), 100, 3);
				if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') )
					add_filter("manage_edit-" . $taxonomy . "_sortable_columns", array(&$this, 'column_add_clean') );
			}
		}

		foreach( get_post_types() as $ptype ) {
			if( isset($ptype) ) {
				add_action("manage_edit-" . $ptype . "_columns", array(&$this, 'column_add'));
				add_filter("manage_" . $ptype . "_posts_custom_column", array(&$this, 'column_value'), 100, 3);
				if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') )
					add_filter("manage_edit-" . $ptype . "_sortable_columns", array(&$this, 'column_add_clean') );
			}
		}

		add_action('manage_users_columns', array(&$this, 'column_add'));
		add_filter('manage_users_custom_column', array(&$this, 'column_return_value'), 100, 3);
		if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') ) {
			add_action('manage_users-network_columns', array(&$this, 'column_add'));
			add_filter("manage_users_sortable_columns", array(&$this, 'column_add_clean') );
			add_filter("manage_users-network_sortable_columns", array(&$this, 'column_add_clean') );
		}

		add_action('manage_edit-comments_columns', array(&$this, 'column_add'));
		add_action('manage_comments_custom_column', array(&$this, 'column_value'), 100, 2);
		if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') )
			add_filter("manage_edit-comments_sortable_columns", array(&$this, 'column_add_clean') );

		if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') ) {
			add_action('manage_sites-network_columns', array(&$this, 'column_add'));
			add_filter('manage_sites_custom_column', array(&$this, 'column_value'), 100, 3);
		}
	}


	/**
	 * Adds a bit of CSS
	 *
	 * @since 1.3.0
	 * @author scripts@schloebe.de
	 */
	function add_css() {
		echo "\n" . '<style type="text/css">
	table.widefat th.column-ridwpaid {
		width: 80px;
	}

	table.widefat td.column-ridwpaid {
		word-wrap: normal;
	}
	</style>' . "\n";
	}


	/**
 	* Add the new 'ID' column
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function column_add($cols) {
		$cols['ridwpaid'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Reveal IDs Plugin', 'reveal-ids-for-wp-admin-25') . '">' . __('ID') . '</abbr>';
		return $cols;
	}


	/**
 	* Add the new 'ID' column without any HTMLy clutter
 	*
 	* @since 		1.4.0
 	* @author 		scripts@schloebe.de
 	*/
	function column_add_clean($cols) {
		$cols['ridwpaid'] = __('ID');
		return $cols;
	}


	/**
 	* Echo the ID for the column
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function column_value($column_name, $id) {
		if ($column_name == 'ridwpaid') echo $id;
	}


	/**
 	* Return the ID for the column
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function column_return_value($value, $column_name, $id) {
		if ($column_name == 'ridwpaid') $value = $id;
		return $value;
	}



	/**
 	* Initialize and load the plugin textdomain
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function load_textdomain() {
		if($this->textdomain_loaded) return;
		load_plugin_textdomain('reveal-ids-for-wp-admin-25', false, dirname(plugin_basename(__FILE__)) . '/languages/');
		$this->textdomain_loaded = true;
	}



	/**
 	* Checks for the version of WordPress,
 	* and adds a message to inform the user
 	* if required WP version is less than 3.0
 	*
 	* @since 		1.3.0
 	* @author 		scripts@schloebe.de
 	*/
	function require_wpversion_message() {
		echo "<div class='error fade'><p>" . sprintf(__("<strong>Reveal IDs</strong> 1.3.0 and above require at least WordPress 3.0! If you're still using a WP version prior to 3.0, please <a href='%s'>use Reveal IDs version 1.2.7</a>! Consider updating to the latest WP version for your own safety!", 'reveal-ids-for-wp-admin-25'), 'http://downloads.wordpress.org/plugin/reveal-ids-for-wp-admin-25.1.2.7.zip') . "</p></div>";
	}

}

if ( class_exists('RevealIDsForWPAdmin') && is_admin() ) {
	$RevealIDsForWPAdmin = new RevealIDsForWPAdmin();
}
?>