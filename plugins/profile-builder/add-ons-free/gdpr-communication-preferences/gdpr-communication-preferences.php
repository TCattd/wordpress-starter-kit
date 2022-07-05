<?php
/*
Description: Ads a GDPR Communication Preferences Field
*/

/* define plugin directory */
define( 'PBGCP_ADD_ON_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) );


/* load required files */
// Include the file that manages manage fields
if (file_exists(PBGCP_ADD_ON_DIR . '/admin/manage-fields.php'))
	include_once(PBGCP_ADD_ON_DIR . '/admin/manage-fields.php');
// Include the files for the custom field
if (file_exists(PBGCP_ADD_ON_DIR . '/front-end/gdpr-communication-preferences.php'))
	include_once(PBGCP_ADD_ON_DIR . '/front-end/gdpr-communication-preferences.php');

/* function that enqueues the necessary scripts */
function wppb_gdprcp_scripts_and_styles( $hook ) {
	if( $hook == 'profile-builder_page_manage-fields' ){
		wp_enqueue_script( 'wppb_gcp_main', plugin_dir_url(__FILE__) . 'assets/js/wppb-gcp-field.js', array( 'jquery', 'wppb-manage-fields-live-change' ) );
	}	
}
add_action( 'admin_enqueue_scripts', 'wppb_gdprcp_scripts_and_styles' );



