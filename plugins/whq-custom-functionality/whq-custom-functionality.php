<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://whooohq.com
 * @since             1.0.0
 * @package           Whq_Custom_Functionality
 *
 * @wordpress-plugin
 * Plugin Name:       WHQ Custom Functionality
 * Plugin URI:        https://whooohq.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            WhoooHQ
 * Author URI:        https://whooohq.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       whq-custom-functionality
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WHQ_CUSTOM_FUNCTIONALITY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-whq-custom-functionality-activator.php
 */
function activate_whq_custom_functionality() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-whq-custom-functionality-activator.php';
	Whq_Custom_Functionality_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-whq-custom-functionality-deactivator.php
 */
function deactivate_whq_custom_functionality() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-whq-custom-functionality-deactivator.php';
	Whq_Custom_Functionality_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_whq_custom_functionality' );
register_deactivation_hook( __FILE__, 'deactivate_whq_custom_functionality' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-whq-custom-functionality.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_whq_custom_functionality() {

	$plugin = new Whq_Custom_Functionality();
	$plugin->run();

}
run_whq_custom_functionality();
