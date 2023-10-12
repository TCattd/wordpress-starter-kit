<?php
/*
Plugin Name: Code Profiler
Plugin URI: https://code-profiler.com/
Description: A profiler to measure the performance of your WordPress plugins and themes.
Author: Jerome Bruandet ~ NinTechNet Ltd.
Author URI: https://nintechnet.com/
Version: 1.6.4
Network: true
License: GPLv3 or later
Text Domain: code-profiler
Domain Path: /languages
*/

define('CODE_PROFILER_VERSION', '1.6.4');
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================
// Menu functions
require __DIR__ .'/lib/menu.php';
// Helper (can be already loaded by the MU plugin)
require_once __DIR__ .'/lib/helper.php';
// AJAX calls
require __DIR__ .'/lib/ajax.php';
// ===================================================================== 2023-06-17
// Activation: make sure the blog meets the requirements.

function code_profiler_activate() {

	if (! defined('WP_CLI') && ! current_user_can('activate_plugins') ) {
		exit( esc_html__('Your are not allowed to activate plugins.',
			'code-profiler')
		);
	}

	global $wp_version;
	if ( version_compare( $wp_version, '5.0', '<') ) {
		exit( sprintf(
			esc_html__('Code Profiler requires WordPress %s or greater but '.
			'your current version is %s.', 'code-profiler'),
			'5.0',
			esc_html( $wp_version )
		) );
	}

	if ( version_compare( PHP_VERSION, '7.1', '<') ) {
		exit( sprintf(
			esc_html__('Code Profiler requires PHP 7.1 or greater but your '.
			'current version is %s.',
			'code-profiler'),
			esc_html( PHP_VERSION )
		) );
	}

	// If the pro version is active, we refuse to run and throw an error message
	if ( is_plugin_active('code-profiler-pro/index.php') ) {
		exit( esc_html__('Code Profiler Pro is active on this site, '.
			'please disable it first if you want to run the free version.',
			'code-profiler')
		);
	}

	// Verify/create our storage folder
	code_profiler_check_uploadsdir();

	// If we don't have any options yet, create them
	$cp_options = get_option('code-profiler');
	if ( $cp_options === false ) {
		code_profiler_default_options();
	}
}

register_activation_hook( __FILE__, 'code_profiler_activate');

// ===================================================================== 2023-06-17
// Deactivation.

function code_profiler_deactivate() {

	if (! defined('WP_CLI') && ! current_user_can('activate_plugins') ) {
		exit( esc_html__('Your are not allowed to deactivate plugins.',
			'code-profiler')
		);
	}

	// Remove the MU plugin
	if ( file_exists( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN ) ) {
		unlink( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN );
	}
}

register_deactivation_hook( __FILE__, 'code_profiler_deactivate');

// ===================================================================== 2023-06-14
// Create Profiler's menu.

function code_profiler_admin_menu() {

	// In a MU environment, only the superadmin can run Code Profiler
	if (! is_super_admin() ) {
		return;
	}

	add_menu_page(
		'Code Profiler',
		'Code Profiler',
		'manage_options',
		'code-profiler',
		'code_profiler_main_menu',
		plugins_url('/static/dashicon-16x16.png', __FILE__ )
	);
}

add_action('admin_menu', 'code_profiler_admin_menu');

// =====================================================================
// Register scripts and styles.

function code_profiler_enqueue( $hook ) {

	if (! is_super_admin() ) {
		return;
	}

	// Load files only if we're in Code Profiler's main page
	if ( $hook != 'toplevel_page_code-profiler') {
		return;
	}

	// We load Thickbox if we're viewing section 4 only
	if ( isset( $_REQUEST['section'] ) && $_REQUEST['section'] == 4 ) {
		$extra_js	= ['jquery', 'thickbox'];
		$extra_css	= ['thickbox'];
	} else {
		$extra_js	= ['jquery'];
		$extra_css	= null;
	}

	wp_enqueue_script(
		'code_profiler_javascript',
		plugin_dir_url( __FILE__ ) .'static/code-profiler.js',
		$extra_js,
		CODE_PROFILER_VERSION
	);

	wp_enqueue_script(
		'code-profiler_tiptip',
		plugin_dir_url( __FILE__ ) .'static/vendor/jquery.tipTip.js',
		['jquery'],
		CODE_PROFILER_VERSION
	);

	wp_enqueue_style(
		'code-profiler_style',
		plugin_dir_url( __FILE__ ) .'static/code-profiler.css',
		$extra_css,
		CODE_PROFILER_VERSION
	);

	// Enqueue Chart.js if we're viewing a profile
	if ( isset( $_REQUEST['action'] ) &&
		$_REQUEST['action'] == 'view_profile') {

		wp_enqueue_script(
			'code_profiler_charts',
			plugin_dir_url( __FILE__ ) .'static/vendor/chart.min.js',
			['jquery'],
			CODE_PROFILER_VERSION,
			// We load it in the footer, because some plugins loads it too
			// on every pages and that could mess with our pages
			true
		);
	}

	// JS i18n
	$code_profiler_i18n = [
		'missing_nonce' =>
			esc_attr__('Security nonce is missing, try to reload the page.',
			'code-profiler'),
		'missing_frontbackend' =>
			esc_attr__('Please select either the fontend or backend option.',
			'code-profiler'),
		'missing_profileid' =>
			esc_attr__('Missing profile identifier.', 'code-profiler'),
		'missing_profilename' =>
			esc_attr__('Please enter a name for this profile.',
			'code-profiler'),
		'missing_userauth' =>
			esc_attr__('Please select whether the profiler should run as '.
			'an authenticated user or not.', 'code-profiler'),
		'missing_username' =>
			esc_attr__('Please enter the name of the user.',
			'code-profiler'),
		'missing_post' =>
			esc_attr__('Please select a page to profile.',
			'code-profiler'),
		'unknown_error' =>
			esc_attr__('Unknown error returned by AJAX',
			'code-profiler'),
		'http_error' =>
			esc_attr__('The HTTP server returned the following error:',
			'code-profiler'),
		'timeout_error' =>
			esc_attr__('You can try to select a lower Accuracy and Precision'.
			' level in the Settings page', 'code-profiler'),
		'notfound_error' =>
			esc_attr__('The requested page does not seem to exist. Please '.
			'check the syntax of the URL you want to profile',
			'code-profiler'),
		'forbidden_error' =>
			esc_attr__('The server rejected and blocked the requested page. '.
			'Make sure you are allowed to access that page or that there is '.
			'no security plugin or application blocking it', 'code-profiler'),
		'internal_error' =>
			esc_attr__('This is an internal server error. Please check your '.
			'server, PHP and Code Profiler logs as they may contain more '.
			'details about the error', 'code-profiler'),
		'unknown_error' =>
			esc_attr__('An unknown error occurred:', 'code-profiler'),
		'preparing_report' =>
			esc_attr__('Preparing the report', 'code-profiler') .'...',
		'empty_log' =>
			esc_attr__('No records were found that match the specified '.
			'search criteria.', 'code-profiler'),
		'delete_log' =>
			esc_attr__('Delete log?', 'code-profiler'),
		'delete_profile' =>
			esc_attr__('Delete this profile?', 'code-profiler'),
		// Charts
		'exec_sec_plugins' =>
			esc_attr__('Execution time in seconds', 'code-profiler'),
		'pc_plugins' =>
			/* Translators: xx% of all plugins and the theme */
			esc_attr__('% of all plugins and the theme', 'code-profiler'),
		'exec_tot_plugins_1' =>
			esc_attr__('Plugins and theme execution time, in seconds',
			'code-profiler'),
		'chart_total' =>
			esc_attr__('total:', 'code-profiler'),
		'iolist_total_calls' =>
			esc_attr__('Total calls', 'code-profiler'),
		'io_calls' =>
			esc_attr__('File I/O operations', 'code-profiler'),
		'disk_io_bytes' =>
			esc_attr__('Total bytes', 'code-profiler'),
		'disk_io_title' =>
			esc_attr__('Total Disk I/O read and write, in bytes',
			'code-profiler'),
		'text_copied' =>
			esc_attr__('The text was successfully copied to the clipboard.',
			'code-profiler')

	];
	wp_localize_script(
		'code_profiler_javascript',
		'cpi18n',
		$code_profiler_i18n
	);
}

add_action('admin_enqueue_scripts', 'code_profiler_enqueue');

// =====================================================================
// Display the settings link in the "Plugins" page.

function code_profiler_settings_link( $links ) {

   $links[] = '<a href="'. get_admin_url( null, 'admin.php?page=code-profiler') .
					'">'.	esc_html__('Start Profiling', 'code-profiler'). '</a>';
	$links[] = '<a style="font-weight:700;color:#006393;" href="https://code-profiler.com/" target="_blank" rel="noopener noreferrer">'.
					esc_html__('Go Pro', 'code-profiler'). '</a>';
	return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'code_profiler_settings_link');

// =====================================================================
// WP CLI commands.

if ( defined('WP_CLI') && WP_CLI ) {
	require_once __DIR__ . '/lib/class-cli.php';
}
// =====================================================================
// EOF
