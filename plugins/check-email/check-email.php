<?php
/*
* Plugin Name: 				Check & Log Email
* Description: 				Check & Log email allows you to test if your WordPress installation is sending emails correctly and logs every email.
* Author: 					WPChill
* Version: 					1.0.7
* Author URI: 				https://wpchill.com/
* License: 					GPLv3 or later
* License URI:         		http://www.gnu.org/licenses/gpl-3.0.html
* Requires PHP: 	    	5.6
* Text Domain: 				check-email
* Domain Path: 				/languages
*
* Copyright 2015-2020 		Chris Taylor 		chris@stillbreathing.co.uk
* Copyright 2020 		    MachoThemes 		office@machothemes.com
* Copyright 2020 		    WPChill 			heyyy@wpchill.com
*
* NOTE:
* Chris Taylor transferred ownership rights on: 2020-06-19 07:52:03 GMT when ownership was handed over to MachoThemes
* The MachoThemes ownership period started on: 2020-06-19 07:52:03 GMT
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 3, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
	function check_email_compatibility_notice() {
		?>
		<div class="error">
			<p>
				<?php
				printf(
					esc_html__( 'Check & Log Email requires at least PHP 5.6 to function properly. Please upgrade PHP.', 'check-email' )
				);
				?>
			</p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'check_email_compatibility_notice' );

	/**
	 * Deactivate Email Log.
	 */
	function check_email_deactivate() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	add_action( 'admin_init', 'check_email_deactivate' );

	return;
}

/**
 * Load Check Email Log.
 */
function check_email_log( $plugin_file ) {
	global $check_email;

	$plugin_dir = plugin_dir_path( $plugin_file );

	// setup autoloader.
	require_once 'include/class-check-email-log-autoloader.php';

	$loader = new \CheckEmail\Check_Email_Log_Autoloader();
	$loader->add_namespace( 'CheckEmail', $plugin_dir . 'include' );

	if ( file_exists( $plugin_dir . 'tests/' ) ) {
		// if tests are present, then add them.
		$loader->add_namespace( 'CheckEmail', $plugin_dir . 'tests/wp-tests' );
	}

	$loader->add_file( $plugin_dir . 'include/Util/helper.php' );

	$loader->register();

	$check_email = new \CheckEmail\Core\Check_Email_Log( $plugin_file, $loader, new \CheckEmail\Core\DB\Check_Email_Table_Manager() );

	$check_email->add_loadie( new \CheckEmail\Core\Check_Email_Logger() );
        $check_email->add_loadie( new \CheckEmail\Core\Check_Email_Review() );
	$check_email->add_loadie( new \CheckEmail\Core\UI\Check_Email_UI_Loader() );

	$check_email->add_loadie( new \CheckEmail\Core\Request\Check_Email_Nonce_Checker() );
	$check_email->add_loadie( new \CheckEmail\Core\Request\Check_Email_Log_List_Action() );

    $capability_giver = new \CheckEmail\Core\Check_Email_Admin_Capability_Giver();
	$check_email->add_loadie( $capability_giver );
	$capability_giver->add_cap_to_admin();

	$check_email->add_loadie( new \CheckEmail\Core\Check_Email_From_Handler() );

	// `register_activation_hook` can't be called from inside any hook.
	register_activation_hook( $plugin_file, array( $check_email->table_manager, 'on_activate' ) );

	// Ideally the plugin should be loaded in a later event like `init` or `wp_loaded`.
	// But some plugins like EDD are sending emails in `init` event itself,
	// which won't be logged if the plugin is loaded in `wp_loaded` or `init`.
	add_action( 'plugins_loaded', array( $check_email, 'load' ), 101 );
}

function wpchill_check_email() {
	global $check_email;
	return $check_email;
}

check_email_log( __FILE__ );