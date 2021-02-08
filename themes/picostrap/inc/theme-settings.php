<?php
/**
 * Check and setup theme's default settings
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'picostrap_setup_theme_default_settings' ) ) {
	/**
	 * Store default theme settings in database.
	 */
	function picostrap_setup_theme_default_settings() {
		$defaults = picostrap_get_theme_default_settings();
		$settings = get_theme_mods();
		foreach ( $defaults as $setting_id => $default_value ) {
			// Check if setting is set, if not set it to its default value.
			if ( ! isset( $settings[ $setting_id ] ) ) {
				set_theme_mod( $setting_id, $default_value );
			}
		}
	}
}

if ( ! function_exists( 'picostrap_get_theme_default_settings' ) ) {
	/**
	 * Retrieve default theme settings.
	 *
	 * @return array
	 */
	function picostrap_get_theme_default_settings() {
		$defaults = array(
			'picostrap_posts_index_style' => 'default',   // Latest blog posts style.
			'picostrap_sidebar_position'  => 'right',     // Sidebar position.
			'picostrap_container_type'    => 'container', // Container width.
		);

		/**
		 * Filters the default theme settings.
		 *
		 * @param array $defaults Array of default theme settings.
		 */
		return apply_filters( 'picostrap_theme_default_settings', $defaults );
	}
}


// as per https://livecanvas.com/faq/which-themes-with-livecanvas/
function lc_theme_is_livecanvas_friendly(){}



//EXPORT CURRENT SETTINGS IN JSON
add_action("admin_init", function (){
	if(isset($_GET['ps_export_mods_to_json'])){		
		$theme = get_option( 'stylesheet' );
		$theme_mods= get_option( "theme_mods_$theme" );
		echo json_encode($theme_mods);	
		wp_die();	
	}
});


//IMPORT CURRENT SETTINGS FROM JSON
add_action("admin_init", function (){
	if(isset($_GET['ps_set_theme_mods_from_json'])) {		
		
		$json_theme_settings='{"0":false,"picostrap_posts_index_style":"default","picostrap_sidebar_position":"right","picostrap_container_type":"container","nav_menu_locations":{"primary":2},"custom_css_post_id":11,"SCSSvar_secondary":"#a3a5bd","SCSSvar_info":"#5c5f7f","SCSSvar_dark":"#2d2f40","picostrap_css_bundle_wp_relative_upload_path":"2020\/11\/styles-bundle-4.css","SCSSvar_font-family-base":"","SCSSvar_font-weight-base":"","SCSSvar_headings-font-family":"","SCSSvar_headings-font-weight":"700","picostrap_fonts_header_code":"","SCSSvar_enable-responsive-font-sizes":true,"SCSSvar_font-weight-bold":"","SCSSvar_enable-rounded":false,"picostrap_header_navbar_position":"d-none","singlepost_disable_comments":true,"picostrap_footer_text":"The picostrap theme is developed by the LiveCanvas team.\n\nThis page has been built with love and LiveCanvas, and is served by picostrap and WordPress on CloudWays","sidebars_widgets":{"time":1604243485,"data":{"wp_inactive_widgets":["archives-2","categories-2","meta-2"]}},"picostrap_disable_livereload":true,"picostrap_header_navbar_color_choice":"bg-dark","picostrap_header_navbar_color_scheme":"navbar-dark"}';

		$mods=json_decode($json_theme_settings);
		
		print_r($mods);wp_die();

		$theme = get_option( 'stylesheet' );
	
		update_option( "theme_mods_$theme", $mods );
	
		echo "Done";
		
		wp_die();	
	}
});


