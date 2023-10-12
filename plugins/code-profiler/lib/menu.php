<?php
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

if (! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

// =====================================================================
// Show the selected tab and page.

function code_profiler_main_menu() {

	// Verify/create our storage folder each time we access the main page
	code_profiler_check_uploadsdir();

	$tab = [
		'profiler',
		'profiles_list',
		'settings',
		'log',
		'faq',
		'support'
	];
	// Set to default if input doesn't match what we're looking for
	if (! isset( $_GET['cptab'] ) || ! in_array( $_GET['cptab'], $tab ) ) {
		$_GET['cptab'] = 'profiler';
	}
	$cprofiler_menu = "code_profiler_menu_{$_GET['cptab']}";
	call_user_func( $cprofiler_menu );

}

// =====================================================================
// Display (in)active tabs.

function code_profiler_display_tabs( $which ) {

	$t1 = ''; $t2 = ''; $t3 = '';
	$t4 = ''; $t5 = ''; $t6 = '';

	if ( $which == 1 ) {
		$t1 = ' nav-tab-active';
		// Don't highlight any tab if we're looking at a profile
	} elseif ( $which == 2 && ! isset( $_REQUEST['section'] ) ) {
		$t2 = ' nav-tab-active';
	} elseif ( $which == 3 ) {
		$t3 = ' nav-tab-active';
	} elseif ( $which == 4 ) {
		$t4 = ' nav-tab-active';
	} elseif ( $which == 5 ) {
		$t5 = ' nav-tab-active';
	} elseif ( $which == 6 ) {
		$t6 = ' nav-tab-active';
	}
	?>
	<h1>Code Profiler</h1>

	<h2 class="nav-tab-wrapper wp-clearfix">
		<a href="?page=code-profiler&cptab=profiler" class="nav-tab <?php
			echo esc_html( $t1 ) ?>"><?php esc_html_e( 'Profiler', 'code-profiler' ) ?></a>
		<a href="?page=code-profiler&cptab=profiles_list&orderby=date&order=desc" class="nav-tab <?php
			echo esc_html( $t2 ) ?>"><?php esc_html_e( 'Profiles List', 'code-profiler' ) ?></a>
		<a href="?page=code-profiler&cptab=settings" class="nav-tab <?php
			echo esc_html( $t3 ) ?>"><?php esc_html_e( 'Settings', 'code-profiler' ) ?></a>
		<a href="?page=code-profiler&cptab=log" class="nav-tab <?php
			echo esc_html( $t4 ) ?>"><?php esc_html_e( 'Log', 'code-profiler' ) ?></a>
		<a href="?page=code-profiler&cptab=faq" class="nav-tab <?php
			echo esc_html( $t5 ) ?>"><?php esc_html_e( 'FAQ', 'code-profiler' ) ?></a>
		<a href="?page=code-profiler&cptab=support" class="nav-tab <?php
			echo esc_html( $t6 ) ?>"><?php esc_html_e( 'Support', 'code-profiler' ) ?></a>
		<div style="text-align:center;">
			<a href="https://code-profiler.com/" target="_blank" rel="noopener noreferrer" class="button-primary"><?php
				esc_html_e('Explore the Pro version', 'code-profiler' );
			?> »</a>
			&nbsp;&nbsp;&nbsp;
			<a href="https://wordpress.org/support/view/plugin-reviews/code-profiler?rate=5#postform" target="_blank" rel="noopener noreferrer" class="button-secondary"><span class="dashicons dashicons-star-half" style="vertical-align:sub;"></span>&nbsp;<?php
				esc_html_e('Rate it on WordPress.org', 'code-profiler' );
			?> »</a>
		</div>
	</h2>
	<?php
}

// =====================================================================
// Profiler page.

function code_profiler_menu_profiler() {

	echo '<div class="wrap">';
	require_once 'menu_profiler.php';
	echo '</div>';

}
// =====================================================================
// Profiles List page.

function code_profiler_menu_profiles_list() {

	echo '<div class="wrap">';
	require_once 'menu_profiles_list.php';
	echo '</div>';

}
// =====================================================================
// Profiler settings.

function code_profiler_menu_settings() {

	echo '<div class="wrap">';
	require_once 'menu_settings.php';
	echo '</div>';

}
// =====================================================================
// Log page.

function code_profiler_menu_log() {


	echo '<div class="wrap">';
	require_once 'menu_log.php';
	echo '</div>';

}
// =====================================================================
// FAQ page.

function code_profiler_menu_faq() {

	echo '<div class="wrap">';
	require_once 'menu_faq.php';
	echo '</div>';

}
// =====================================================================
// Support page.

function code_profiler_menu_support() {

	echo '<div class="wrap">';
	require_once 'menu_support.php';
	echo '</div>';

}
// =====================================================================
// Pro page.

function code_profiler_menu_pro() {

	echo '<div class="wrap">';
	require_once 'menu_pro.php';
	echo '</div>';

}
// =====================================================================
// About page.

function code_profiler_menu_about() {

	echo '<div class="wrap">';
	require_once 'menu_about.php';
	echo '</div>';

}

// =====================================================================
// EOF
