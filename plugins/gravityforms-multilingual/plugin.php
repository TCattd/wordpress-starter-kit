<?php
/**
 * Plugin Name: Gravity Forms Multilingual
 * Plugin URI: https://wpml.org/documentation/related-projects/gravity-forms-multilingual/?utm_source=plugin&utm_medium=gui&utm_campaign=gfml
 * Description: Add multilingual support for Gravity Forms
 * Author: OnTheGoSystems
 * Author URI: http://www.onthegosystems.com/
 * Version: 1.6.3
 * Plugin Slug: gravityforms-multilingual
 *
 * @package WPML\gfml
 */

if ( defined( 'GRAVITYFORMS_MULTILINGUAL_VERSION' ) ) {
	return;
}

define( 'GRAVITYFORMS_MULTILINGUAL_VERSION', '1.6.3' );
define( 'GRAVITYFORMS_MULTILINGUAL_PATH', dirname( __FILE__ ) );

require_once GRAVITYFORMS_MULTILINGUAL_PATH . '/classes/class-wpml-gfml-plugin-activation.php';
$wpml_gfml_activation = new WPML_GFML_Plugin_Activation();
$wpml_gfml_activation->register_callback();

add_action( 'wpml_loaded', 'gfml_init' );

function gfml_init() {
	if ( ! class_exists( 'WPML_Core_Version_Check' ) ) {
		require_once GRAVITYFORMS_MULTILINGUAL_PATH . '/vendor/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-core-version-check.php';
	}

	if ( ! WPML_Core_Version_Check::is_ok( GRAVITYFORMS_MULTILINGUAL_PATH . '/wpml-dependencies.json' ) ) {
		return;
	}

	require_once GRAVITYFORMS_MULTILINGUAL_PATH . '/vendor/autoload.php';

	add_action( 'wpml_gfml_has_requirements', 'load_gfml' );

	new WPML_GFML_Requirements();
}

/**
 * Load the plugin if WPML-Core is installed
 */
function load_gfml() {
	if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		require GRAVITYFORMS_MULTILINGUAL_PATH . '/inc/gfml-string-name-helper.class.php';
		require GRAVITYFORMS_MULTILINGUAL_PATH . '/inc/gravity-forms-multilingual.class.php';

		require GRAVITYFORMS_MULTILINGUAL_PATH . '/inc/gfml-tm-api.class.php';

		$api                         = new GFML_TM_API();
		$GLOBALS['wpml_gfml_tm_api'] = $api;

		$hooks = new GFML_Hooks( $api );
		$hooks->init();

		global $sitepress;
		$current_language = $sitepress->get_current_language();
		new WPML_GFML_Filter_Field_Meta( $current_language );

		$wpml_gfml_filter_country_field = new WPML_GFML_Filter_Country_Field();
		$wpml_gfml_filter_country_field->add_hooks();

		do_action( 'wpml_gfml_tm_api_loaded', $GLOBALS['wpml_gfml_tm_api'] );
	}
}

/**
 * Disable the normal wpml admin language switcher for gravity forms.
 *
 * @param string $state
 *
 * @return bool
 */
function gfml_disable_wpml_admin_lang_switcher( $state ) {
	global $pagenow;

	if ( 'admin.php' === $pagenow && 'gf_edit_forms' === filter_input( INPUT_GET, 'page' ) ) {
		$state = false;
	}

	return $state;
}

add_filter( 'wpml_show_admin_language_switcher', 'gfml_disable_wpml_admin_lang_switcher' );

/**
 * GFML Quiz compatibility
 * Instantiate the plugin after GFML
 * to get to inject the instance of $gfml_tm_api
 *
 * @param GFML_TM_API $gfml_tm_api
 */
function wpml_gf_quiz_init( $gfml_tm_api ) {
	if ( ! defined( 'GF_QUIZ_VERSION' ) || version_compare( ICL_SITEPRESS_VERSION, '3.2', '<' ) ) {
		return;
	}

	$wpml_gf_quiz = new WPML_GF_Quiz( $gfml_tm_api );
	$wpml_gf_quiz->add_hooks();
}
add_action( 'wpml_gfml_tm_api_loaded', 'wpml_gf_quiz_init' );

function wpml_gf_survey_init( $gfml_tm_api ) {
	if ( ! defined( 'GF_SURVEY_VERSION' ) ) {
		return;
	}

	$gf_survey = new WPML_GF_Survey( $gfml_tm_api, new GFML_String_Name_Helper() );
	$gf_survey->add_hooks();
}
add_action( 'wpml_gfml_tm_api_loaded', 'wpml_gf_survey_init' );

function wpml_gf_user_registration_init( $gfml_tm_api ) {
	if ( ! defined( 'GF_USER_REGISTRATION_VERSION' ) ) {
		return;
	}

	global $sitepress;
	if ( $sitepress ) {
		$gf_user_registration = new \GFML\Compatibility\UserRegistration\Hooks( $sitepress );
		$gf_user_registration->addHooks();
	}
}
add_action( 'wpml_gfml_tm_api_loaded', 'wpml_gf_user_registration_init' );

function wpml_gf_gravity_flow_init( $gfml_tm_api ) {
	if ( ! defined( 'GRAVITY_FLOW_VERSION' ) ) {
		return;
	}

	$gf_gravity_flow = new \GFML\Compatibility\FeedAddon\GravityFlow( $gfml_tm_api, Gravity_Flow::get_instance() );
	$gf_gravity_flow->addHooks();
}
add_action( 'wpml_gfml_tm_api_loaded', 'wpml_gf_gravity_flow_init' );

$wpml_gfml_activation = new WPML_GFML_Plugin_Activation();
$wpml_gfml_activation->register_callback();
