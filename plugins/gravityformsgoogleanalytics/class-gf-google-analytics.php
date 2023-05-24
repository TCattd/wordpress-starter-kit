<?php

namespace Gravity_Forms\Gravity_Forms_Google_Analytics;

defined( 'ABSPATH' ) || die();

use GFForms;
use GFFeedAddOn;
use GFCommon;
use Gravity_Forms\Gravity_Forms_Google_Analytics\GF_Google_Analytics_Pagination;
use GFAPI;
use GFCache;
use Gravity_Forms\Gravity_Forms_Google_Analytics\Settings;
use GFLogging;
use WP_Error;

// Include the Gravity Forms Feed Add-On Framework.
GFForms::include_feed_addon_framework();

/**
 * Gravity Forms Google Analytics Add-On.
 *
 * @since     1.0.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2019, Rocketgenius
 */
class GF_Google_Analytics extends GFFeedAddOn {
	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @var    GF_Google_Analytics $_instance If available, contains an instance of this class
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Gravity Forms Google Analytics Add-On.
	 *
	 * @since  1.0
	 * @var    string $_version Contains the version.
	 */
	protected $_version = GF_GOOGLE_ANALYTICS_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = GF_GOOGLE_ANALYTICS_MIN_GF_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsgoogleanalytics';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsgoogleanalytics/googleanalytics.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;
	/**
	 * Wrapper class for plugin settings.
	 *
	 * @since 1.0
	 * @var Settings\Plugin_Settings
	 */
	private $plugin_settings;
	/**
	 * Wrapper class for form settings.
	 *
	 * @since 1.0
	 * @var Settings\Form_Settings
	 */
	private $form_settings;
	/**
	 * Defines the URL where this add-on can be found.
	 *
	 * @since  1.0
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'http://gravityforms.com';

	/**
	 * Defines the title of this add-on.
	 *
	 * @since  1.0
	 * @var    string $_title The title of the add-on.
	 */
	protected $_title = 'Gravity Forms Google Analytics Add-On';

	/**
	 * Defines the short title of the add-on.
	 *
	 * @since  1.0
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Google Analytics';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capabilities needed for the Google Analytics Add-On
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_googleanalytics', 'gravityforms_googleanalytics_uninstall' );

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_googleanalytics';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_googleanalytics';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_googleanalytics_uninstall';

	/**
	 * Stores the plugin's options
	 *
	 * @since 1.0.0
	 * @var array $options
	 */
	private static $options = false;

	/**
	 * Defines the entry id if the page is redirected.
	 *
	 * @since  1.0.0
	 *
	 * @var    int Entry id for the feed.
	 */
	private $conversion_entryid = 0;

	/**
	 * Defines the category if the page is redirected.
	 *
	 * @since  1.0.0
	 *
	 * @var    string Event category for the feed.
	 */
	private $conversion_category = '';

	/**
	 * Defines the label if the page is redirected.
	 *
	 * @since  1.0.0
	 *
	 * @var    string Event label for the feed.
	 */
	private $conversion_label = '';

	/**
	 * Defines the action if the page is redirected.
	 *
	 * @since  1.0.0
	 *
	 * @var    string Event action for the feed.
	 */
	private $conversion_action = '';

	/**
	 * Saves an API instance for Google Authorization.
	 *
	 * @since  1.0.0
	 *
	 * @var    GF_Google_Analytics_API null into object is set.
	 */
	protected $api = null;

	/**
	 * Sets whether an account has a connection error (i.e., GA or GTM are not installed for an account).
	 *
	 * @since  1.0.0
	 *
	 * @var    bool True if error, false if not.
	 */
	private $connect_error = false;

	/**
	 * Get an instance of this class.
	 *
	 * @since  1.0.0
	 *
	 * @return GF_Google_Analytics
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;

	}

	/**
	 * Run add-on pre-initialization processes.
	 *
	 * @since 1.0
	 */
	public function pre_init() {
		require_once plugin_dir_path( __FILE__ ) . '/includes/settings/class-plugin-settings.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/settings/class-form-settings.php';
		$this->plugin_settings = new Settings\Plugin_Settings( $this );
		$this->form_settings   = new Settings\Form_Settings( $this );

		// Run late to override other filters like the one in Gravity Forms.
		add_filter( 'auto_update_plugin', array( $this, 'disable_ga_auto_update' ), 1000, 2 );

		parent::pre_init();
	}

	/**
	 * Performs enqueuing tasks for JS, redirection, event save state, and pagination.
	 *
	 * @since  1.0.0
	 */
	public function init() {
		parent::init();
		add_action( 'gform_confirmation', array( &$this, 'append_confirmation_url' ), 10, 4 );
		add_action( 'wp_head', array( $this, 'maybe_install_analytics' ) );
		add_action( 'wp_head', array( $this, 'maybe_install_tag_manager_header' ) );

		add_action( 'wp_body_open', array( $this, 'maybe_install_tag_manager_body' ) );

		// General pagination.
		add_action( 'gform_post_paging', array( $this, 'pagination' ), 10, 3 );

		// GTM UTM Variable Tracking Script.
		add_action( 'wp_enqueue_scripts', array( $this, 'load_utm_gtm_script' ) );

		// Load GA script on confirmation page if redirected.
		add_action( 'wp_enqueue_scripts', array( $this, 'load_ga_scripts_on_confirmation' ) );
	}

	/**
	 * Add Admin handlers.
	 */
	public function init_admin() {
		$this->plugin_settings->maybe_update_auth_tokens();

		add_action( 'admin_notices', array( $this, 'display_ga4_upgrade_notice' ) );

		parent::init_admin();
	}

	/**
	 * Add Ajax handlers.
	 */
	public function init_ajax() {
		add_action( 'wp_ajax_nopriv_get_entry_meta', array( $this, 'ajax_get_entry_meta' ) );
		add_action( 'wp_ajax_get_entry_meta', array( $this, 'ajax_get_entry_meta' ) );
		add_action( 'wp_ajax_nopriv_save_entry_meta', array( $this, 'ajax_save_entry_meta' ) );
		add_action( 'wp_ajax_save_entry_meta', array( $this, 'ajax_save_entry_meta' ) );
		add_action( 'wp_ajax_save_google_analytics_data', array( $this, 'ajax_save_google_analytics_data' ) );
		add_action( 'wp_ajax_save_google_tag_manager_data', array( $this, 'ajax_save_google_tag_manager_data' ) );
		add_action( 'wp_ajax_get_ga_views', array( $this, 'ajax_get_ga_views' ) );
		add_action( 'wp_ajax_create_analytics_goal', array( $this, 'ajax_create_analytics_goal' ) );
		add_action( 'wp_ajax_update_analytics_goal', array( $this, 'ajax_update_analytics_goal' ) );
		add_action( 'wp_ajax_get_gtm_workspaces', array( $this, 'ajax_get_gtm_workspaces' ) );
		add_action( 'wp_ajax_get_gtm_containers', array( $this, 'ajax_get_gtm_containers' ) );
		add_action( 'wp_ajax_redirect_to_api', array( $this, 'ajax_redirect_to_api' ) );
		add_action( 'wp_ajax_disconnect_account', array( $this, 'ajax_disconnect_account' ) );

		parent::init_ajax();
	}

	/**
	 * Initializes the Google Analytics API if credentials are valid.
	 *
	 * @since  1.0
	 *
	 * @return bool|null API initialization state. Returns null if no authentication token is provided.
	 */
	public function initialize_api() {

		// If the API is already initializes, return true.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		// Initialize Google Analytics API library.
		if ( ! class_exists( 'GF_Google_Analytics_API' ) ) {
			require_once 'includes/class-gf-google-analytics-api.php';
		}

		// Get the authentication token.
		$auth_token = self::get_options( 'auth_token' );
		$mode       = self::get_options( '', 'mode' );
		if ( empty( $mode ) ) {
			$mode = self::get_options( '', 'tempmode' );
		}
		$token        = isset( $auth_token['token'] ) ? $auth_token['token'] : '';
		$date_created = isset( $auth_token['date_created'] ) ? $auth_token['date_created'] : 0;

		// If the authentication token is not set, return null.
		if ( empty( $auth_token ) || rgblank( $token ) || 'unset' === $mode || rgblank( $mode ) ) {
			return null;
		}

		// Initialize a new Google Analytics API instance.
		$google_analytics_api = new GF_Google_Analytics_API( $this, $token );
		if ( time() > ( $date_created + 3600 ) ) { // Access token expires in 1 hour = 3600 seconds.

			// Log that authentication test failed.
			$this->log_debug( __METHOD__ . '(): API tokens expired, start refreshing.' );

			// Refresh token.
			$auth_response = $google_analytics_api->refresh_token( $auth_token['refresh'] );

			if ( ! is_wp_error( $auth_response ) ) {
				$auth_settings = array(
					'token'        => rgars( $auth_response, 'token/access_token' ),
					'refresh'      => rgars( $auth_response, 'token/refresh_token' ),
					'date_created' => rgars( $auth_response, 'token/created' ),
				);
				// Save plugin settings.
				$this->update_options( $auth_settings, 'auth_token' );
				$this->log_debug( __METHOD__ . '(): API access token has been refreshed.' );
			} else {
				$this->log_debug( __METHOD__ . '(): API access token failed to be refreshed; ' . $auth_response->get_error_message() );
				return false;
			}
		}

		// Assign Google Analytics API instance to the Add-On instance.
		$this->api = $google_analytics_api;

		return true;

	}

	public function disable_ga_auto_update( $do_update, $item ) {

		// Disable automatic updates for Google Analytics.
		if ( $item->slug === $this->_slug ) {
			$this->log_debug( __METHOD__ . '(): Automatic update for this version of the Google Analytics add-on has been disabled.' );

			return false;
		}

		return $do_update;
	}

	/**
	 * Displays a notice about the upcoming GA4 update that will require user action.
	 *
	 * @since 1.3
	 *
	 * @return void
	 */
	public function display_ga4_upgrade_notice() {

		$message = __(
			'<p><strong>Important: Updates are coming soon for the Gravity Forms Google Analytics Add-On.</strong></p>
			<p>On July 1, 2023, Google\'s Universal Analytics (UA) properties will stop processing any data. In order to address this, Gravity Forms will be releasing version 2.0 of the Google Analytics Add-On, which will send data to Google Analytics 4 (GA4) instead of UA.</p>
			<p>We have disabled automatic updates for 2.0, as installing it will require you to make changes to ensure your data continues to send properly. For more information about this change, <a href="https://docs.gravityforms.com/google-universal-analytics-deprecation-warning" target="_blank">read what you need to do to prepare</a>.</p>',
			'gravityformsgoogleanalytics'
		);

		$notice = array(
			'key'  => $this->get_slug() . '_ga4_update_notice2_' . gmdate( 'Y' ) . gmdate( 'z' ),
			'type' => 'warning',
			'text' => $message,
		);

		GFCommon::display_dismissible_message( array( $notice ) );
	}

	/**
	 * Outputs admin scripts to handle form submission in back-end.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function scripts() {
		$settings = $this->get_plugin_settings();
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || rgget( 'gform_debug' ) ? '' : '.min';

		// Get the Tracker.
		$settings['ajaxurl'] = admin_url( 'admin-ajax.php' );
		$settings['nonce']   = wp_create_nonce( 'gforms_google_analytics_confirmation' );

		$scripts = array(
			array(
				'handle'    => 'google_analytics_admin',
				'src'       => $this->get_base_url() . "/js/google-analytics-admin{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'jquery', 'wp-ajax-response' ),
				'strings'   => array(
					'update_settings'    => wp_strip_all_tags( __( 'Update Settings', 'gravityformsgoogleanalytics' ) ),
					'disconnect'         => wp_strip_all_tags( __( 'Disconnecting', 'gravityformsgoogleanalytics' ) ),
					'redirect'           => wp_strip_all_tags( __( 'Redirecting...', 'gravityformsgoogleanalytics' ) ),
					'connect'            => wp_strip_all_tags( __( 'Connect', 'gravityformsgoogleanalytics' ) ),
					'connecting'         => wp_strip_all_tags( __( 'Connecting', 'gravityformsgoogleanalytics' ) ),
					'workspace_required' => wp_strip_all_tags( __( 'You must select a Workspace', 'gravityformsgoogleanalytics' ) ),
					'view_required'      => wp_strip_all_tags( __( 'You must select a View', 'gravityformsgoogleanalytics' ) ),
					'ga_required'        => wp_strip_all_tags( __( 'You must select a Google Analytics account', 'gravityformsgoogleanalytics' ) ),
					'gtm_required'       => wp_strip_all_tags( __( 'You must select a Tag Manager Account', 'gravityformsgoogleanalytics' ) ),
					'spinner'            => GFCommon::get_base_url() . '/images/spinner.svg',
				),
				'in_footer' => true,
				'enqueue'   => array(
					array(
						'query' => 'page=gf_settings&subview=gravityformsgoogleanalytics',
					),
				),
			),
			array(
				'handle'    => 'gforms_google_analytics_feed',
				'src'       => $this->get_base_url() . "/js/google-analytics-feed{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'jquery', 'thickbox' ),
				'in_footer' => true,
				'enqueue'   => array(
					array( 'query' => 'page=gf_edit_forms&view=settings&subview=gravityformsgoogleanalytics' ),
				),
				'strings'   => array(
					'edit'                    => wp_strip_all_tags( __( 'Select Goal', 'gravityformsgoogleanalytics' ) ),
					'goalcreation'            => wp_strip_all_tags( __( 'Gravity Forms Google Analytics', 'gravityformsgoogleanalytics' ) ),
					'usegoal'                 => wp_strip_all_tags( __( 'Use This Goal', 'gravityformsgoogleanalytics' ) ),
					'creategoal'              => wp_strip_all_tags( __( 'Create Goal', 'gravityformsgoogleanalytics' ) ),
					'creating'                => wp_strip_all_tags( __( 'Creating Goal...', 'gravityformsgoogleanalytics' ) ),
					'saving'                  => wp_strip_all_tags( __( 'Saving Goal...', 'gravityformsgoogleanalytics' ) ),
					'savinganduse'            => wp_strip_all_tags( __( 'Save and Use Goal', 'gravityformsgoogleanalytics' ) ),
					'goalcreated'             => wp_strip_all_tags( __( 'Goal Created!', 'gravityformsgoogleanalytics' ) ),
					'required'                => wp_strip_all_tags( __( 'This field is required.', 'gravityformsgoogleanalytics' ) ),
					'spinner'                 => GFCommon::get_base_url() . '/images/spinner.gif',
					'action'                  => 'submission',
					'category'                => 'form',
					'label'                   => '{form_title} ID: {form_id}',
					'goal'                    => rgget( 'id' ) ? wp_strip_all_tags( __( 'Form Submission:', 'gravityformsgoogleanalytics' ) . ' ' . GFAPI::get_form( rgget( 'id' ) )['title'] ) : '',
					'pagination_action'       => 'pagination',
					'pagination_category'     => 'form',
					'pagination_label'        => '{form_title}::{source_page_number}::{current_page_number}',
					'pagination_goal'         => rgget( 'id' ) ? wp_strip_all_tags( __( 'Pagination:', 'gravityformsgoogleanalytics' ) . ' ' . GFAPI::get_form( rgget( 'id' ) )['title'] ) : '',
					'pagination_savinganduse' => wp_strip_all_tags( __( 'Save and Use Goal', 'gravityformsgoogleanalytics' ) ),
					'pagination_creategoal'   => wp_strip_all_tags( __( 'Create Goal', 'gravityformsgoogleanalytics' ) ),
					'pagination_edit'         => wp_strip_all_tags( __( 'Select Goal', 'gravityformsgoogleanalytics' ) ),
					'pagination_savegoal'     => wp_strip_all_tags( __( 'Save Goal', 'gravityformsgoogleanalytics' ) ),
				),
			),
			array(
				'handle'    => 'gforms_google_analytics_frontend',
				'src'       => $this->get_base_url() . "/js/google-analytics{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'jquery', 'wp-ajax-response' ),
				'in_footer' => true,
				'callback'  => array( $this, 'localize_scripts' ),
				'strings'   => $settings,
				'enqueue'   => array(
					array( $this, 'frontend_script_callback' ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Localize scripts in frontend js.
	 *
	 * @since  1.3
	 *
	 * @return void
	 */
	public function localize_scripts() {
		wp_localize_script(
			'gforms_google_analytics_frontend',
			'gforms_google_analytics_data',
			array(
				'loggingEnabled' => $this->is_logging_enabled(),
			)
		);
	}

	/**
	 * Outputs admin styles to handle form submission in back-end.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function styles() {
		$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || rgget( 'gform_debug' ) ? '' : '.min';
		$styles = array(
			array(
				'handle'  => 'thickbox',
				'enqueue' => array(
					array( 'query' => 'page=gf_edit_forms&view=settings&subview=gravityformsgoogleanalytics' ),
				),
			),
			array(
				'handle'  => 'gfga_thickbox_settings',
				'src'     => $this->get_base_url() . "/css/gfga_thickbox{$min}.css",
				'version' => $this->_version,
				'deps'    => array( 'thickbox' ),
				'enqueue' => array(
					array( 'query' => 'page=gf_edit_forms&view=settings&subview=gravityformsgoogleanalytics' ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * Load a UTM tracking script for Google Tag Manager.
	 */
	public function load_utm_gtm_script() {
		$maybe_gtm_on = $this->get_plugin_setting( 'utm' );
		if ( 'on' === $maybe_gtm_on ) {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || rgget( 'gform_debug' ) ? '' : '.min';
			wp_enqueue_script(
				'gfga_utm_gtm',
				$this->get_base_url() . "/js/google-analytics-utm-tag-manager{$min}.js",
				array( 'jquery', 'wp-ajax-response' ),
				$this->_version,
				true
			);
		}
	}

	/**
	 * Load GA scripts on the confirmation page if it is a redirect.
	 *
	 * @since 1.0
	 */
	public function load_ga_scripts_on_confirmation() {
		if ( rgget( 'gfaction' ) ) {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || rgget( 'gform_debug' ) ? '' : '.min';
			wp_enqueue_script(
				'gforms_google_analytics_frontend',
				$this->get_base_url() . "/js/google-analytics{$min}.js",
				array( 'jquery', 'wp-ajax-response' ),
				$this->_version,
				true
			);
			$settings = $this->get_plugin_settings();
			$settings['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$settings['nonce']   = wp_create_nonce( 'gforms_google_analytics_confirmation' );
			wp_localize_script( 'gforms_google_analytics_frontend', 'gforms_google_analytics_frontend_strings', $settings );
		}
	}

	/**
	 * Get Gravity API URL.
	 *
	 * @since 1.0
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function get_gravity_api_url( $path = '' ) {
		return ( defined( 'GRAVITY_API_URL' ) ? GRAVITY_API_URL : 'https://gravityapi.com/wp-json/gravityapi/v1' ) . $path;
	}

	/**
	 * Retrieve the plugin's options.
	 *
	 * Retrieve the plugin's options based on context.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $context      Context to retrieve options for. This is used as an array key.
	 * @param  string $key          Array key to retrieve.
	 * @param  bool   $force_reload Whether to retrieve cached options or forcefully retrieve from the database.
	 *
	 * @return mixed All options if no context, or associative array if context is set. Empty array if no options. String if $key is set.
	 */
	public static function get_options( $context = '', $key = false, $force_reload = false ) {
		// Try to get cached options.
		$options = self::$options;
		if ( false === $options || true === $force_reload ) {
			$options = get_option( 'gravityformsaddon_gravityformsgoogleanalytics_settings', array() );
		}

		// Store options.
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// Assign options for caching.
		self::$options = $options;

		if ( rgblank( $context ) && $key ) {
			if ( isset( $options[ $key ] ) ) {
				return $options[ $key ];
			} else {
				return '';
			}
		}

		// Attempt to get context.
		if ( ! empty( $context ) && is_string( $context ) ) {
			if ( array_key_exists( $context, $options ) ) {
				if ( false !== $key && is_string( $key ) ) {
					if ( isset( $options[ $context ][ $key ] ) ) {
						return $options[ $context ][ $key ];
					}
				} else {
					return (array) $options[ $context ];
				}
			} else {
				return array();
			}
		}

		return $options;
	}

	/**
	 * Save plugin options.
	 *
	 * Saves the plugin options based on context.  If no context is provided, updates all options.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $options Associative array of plugin options.
	 * @param string $context Array key of which options to update.
	 */
	public static function update_options( $options = array(), $context = '' ) {
		$options_to_save = self::get_options();

		if ( ! empty( $context ) && is_string( $context ) ) {
			$options_to_save[ $context ] = $options;
		} else {
			$options_to_save = $options;
		}
		update_option( 'gravityformsaddon_gravityformsgoogleanalytics_settings', $options_to_save );
		self::$options = $options_to_save;
	}

	/**
	 * Save feed settings.
	 *
	 * @since 1.0.0
	 * @param int $feed_id The Feed ID.
	 * @param int $form_id The Form ID.
	 *
	 * @return int
	 */
	public function maybe_save_feed_settings( $feed_id, $form_id ) {

		if ( ! rgpost( 'gform-settings-save' ) ) {
			return $feed_id;
		}

		check_admin_referer( $this->_slug . '_save_settings', '_' . $this->_slug . '_save_settings_nonce' );

		if ( ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			GFCommon::add_error_message( esc_html__( "You don't have sufficient permissions to update the form settings.", 'gravityformsgoogleanalytics' ) );
			return $feed_id;
		}

		// Store a copy of the previous settings for cases where action would only happen if value has changed.
		$feed = $this->get_feed( $feed_id );
		$this->set_previous_settings( $feed['meta'] );

		$settings = $this->get_posted_settings();
		$sections = $this->get_feed_settings_fields();
		$settings = $this->trim_conditional_logic_vales( $settings, $form_id );

		$is_valid = $this->validate_settings( $sections, $settings );

		if ( $is_valid ) {
			$settings = $this->filter_settings( $sections, $settings );
			$feed_id  = $this->save_feed_settings( $feed_id, $form_id, $settings );
			if ( $feed_id ) {
				GFCommon::add_message( $this->get_save_success_message( $sections ) );
			} else {
				GFCommon::add_error_message( $this->get_save_error_message( $sections ) );
			}
		} else {
			GFCommon::add_error_message( $this->get_save_error_message( $sections ) );
		}
		$redirect_url = add_query_arg(
			array(
				'page'    => 'gf_edit_forms',
				'view'    => 'settings',
				'subview' => 'gravityformsgoogleanalytics',
				'id'      => $form_id,
				'fid'     => $feed_id,
			),
			admin_url( 'admin.php' )
		);
		if ( 0 === absint( rgget( 'fid' ) ) ) {
			?>
			<script>
				setTimeout( function() {
					window.location.href = '<?php echo esc_url_raw( $redirect_url ); ?>';
				}, 1 );
			</script>
			<?php
		}
	}

	/**
	 * Installs GTAG Google Analytics if user has selected that option in settings.
	 *
	 * @since  1.0.0
	 */
	public function maybe_install_analytics() {
		$settings = $this->get_plugin_settings();
		if ( ! isset( $settings['ga'] ) ) {
			return;
		}
		if ( 'off' === $settings['ga'] ) {
			return;
		}

		$this->log_debug( __METHOD__ . '(): Loading Google Analytics GTAG settings: ' . print_r( $settings, true ) );

		// Attempt to get options.
		$ga_code = sanitize_text_field( $this->get_options( 'account', 'property' ) );

		// User has requested GA installation. Proceed.
		echo "\r\n";
		?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html( $ga_code ); ?>"></script> <?php //phpcs:ignore ?>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '<?php echo esc_html( $ga_code ); ?>');
		<?php
		/**
		 * Action: gform_googleanalytics_install_analytics
		 *
		 * Allow custom scripting for Google Analytics GTAG
		 *
		 * @since 1.0.0
		 *
		 * @param string $ga_code Google Analytics Property ID
		 */
		do_action( 'gform_googleanalytics_install_analytics', $ga_code );
		?>
	</script>
		<?php
	}

	/**
	 * Installs Google Tag Manager if user has selected that option in settings.
	 *
	 * @since  1.0.0
	 */
	public function maybe_install_tag_manager_header() {
		$settings = $this->get_plugin_settings();
		if ( ! isset( $settings['install_gtm'] ) ) {
			return;
		}
		if ( 'off' === $settings['install_gtm'] ) {
			return;
		}

		$this->log_debug( __METHOD__ . '(): Loading Google Tag Manager Installation Setting: ' . print_r( $settings, true ) );

		// Attempt to get options.
		$gtm_code = sanitize_text_field( $this->get_options( 'account', 'container_id' ) );
		if ( empty( $gtm_code ) ) {
			return;
		}

		// User has requested Tag Manager installation. Proceed.
		echo "\r\n";
		?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
					new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','<?php echo esc_html( $gtm_code ); ?>');
		<?php
		/**
		 * Action: gform_googleanalytics_install_tag_manager
		 *
		 * Allow custom scripting for Google Tag Manager
		 *
		 * @since 1.0.0
		 *
		 * @param string $gtm_code Google Tag Manager ID
		 */
		do_action( 'gform_googleanalytics_install_tag_manager', $gtm_code );
		?>
		</script>
		<!-- End Google Tag Manager -->
		<?php
	}

	/**
	 * Installs Google Tag Manager if user has selected that option in settings.
	 *
	 * @since  1.0.0
	 */
	public function maybe_install_tag_manager_body() {
		$settings = $this->get_plugin_settings();
		if ( ! isset( $settings['install_gtm'] ) ) {
			return;
		}
		if ( 'off' === $settings['install_gtm'] ) {
			return;
		}

		$gtm_code = sanitize_text_field( $this->get_options( 'account', 'container_id' ) );
		if ( empty( $gtm_code ) ) {
			return;
		}

		// User has requested Tag Manager installation. Proceed.
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript>
			<iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_html( $gtm_code ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
		</noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
		/**
		 * Action: gform_googleanalytics_install_tag_manager
		 *
		 * Allow custom scripting for Google Tag Manager
		 *
		 * @since 1.0.0
		 *
		 * @param string $gtm_code Google Tag Manager ID
		 */
		do_action( 'gform_googleanalytics_install_tag_manager', $gtm_code );
	}

	/**
	 * Check if goal attributes need to be manually configured for tag manager.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True if configuration is set to manual, otherwise false.
	 */
	public function manual_configuration() {
		$options = $this->get_options();
		return $options['account']['property'] === 'manual';
	}

	/**
	 * Creates a Google Analytics goal if none exists.
	 *
	 * @since  1.0.0
	 *
	 * @param string $event_category         The event category to create a goal for.
	 * @param string $event_action           The event action to create a goal for.
	 * @param string $event_label            The event label to create a goal for.
	 * @param string $goal_name              The name for this goal.
	 * @param bool   $create_goal            Whether to create the goal or not (useful for checking if the goal .exists).
	 * @param bool   $skip_goal_exists_check If true, skips goal checking for existing goal.
	 *
	 * @return array|WP_Error The response from the API. An array if successful, otherwise a WP_Error object.
	 */
	private function create_analytics_goal( $event_category, $event_action, $event_label, $goal_name, $create_goal = true, $skip_goal_exists_check = false ) {
		$options = $this->get_options();
		$mode    = $options['mode'];

		$custom_label = $event_label;

		// Check event label for default values.
		if ( '{form_title}::{source_page_number}::{current_page_number}' === $event_label ) {
			$event_label = '';
		}
		if ( '{form_title} ID: {form_id}' === $event_label ) {
			$event_label = '';
		}

		// Check event label for merge tags.
		if ( ! empty( $event_label ) && strstr( $event_label, '{' ) ) {
			$event_label = '';
			$this->log_debug( __METHOD__ . '(): Label has merge tags. Defaulting to empty for goal creation. Users can segment by label in Google Analytics' );
		}

		// Make sure values aren't empty.
		if ( empty( $event_category ) || empty( $event_action ) ) {
			$this->log_debug( __METHOD__ . '(): Goal creation aborted. Event category or action is empty' );
			return new WP_Error( 'missing_values', 'Event category and action cannot be empty' );
		}

		if ( $this->manual_configuration() ) {
			return array(
				'id'   => null,
				'kind' => 'analytics#goal',
			);
		}

		/**
		 * Filter: gform_googleanalytics_goal_name.
		 *
		 * Filter the goal name so it can be changed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $goal_name       Goal name for the analytics goal.
		 * @param string $mode            The mode the plugins is in (ga, gmp, gtm).
		 * @param string $event_category  Event category for this goal.
		 * @param string $event_action    Event action for this goal.
		 * @param string $event_label     Event label for this goal.
		 *
		 * @return string New goal name.
		 */
		$goal_name = apply_filters( 'gform_googleanalytics_goal_name', $goal_name, $mode, $event_category, $event_action, $event_label );

		$this->log_debug( __METHOD__ . '(): Creating Google Analytics Goal: ' . $goal_name );

		$body = array();

		// Retrieve profile ID.
		$profile_id = $this->get_profile_id(
			$options['account']['account_id'],
			$options['account']['property'],
			$options['account']['view']
		);
		$this->log_debug( __METHOD__ . '(): Retrieving Profile ID with response: ' . $profile_id );

		// Retrieve Goal information.
		$goal_response = $this->api->get_goals(
			$body,
			$options['account']['account_id'],
			$options['account']['property'],
			$profile_id
		);
		if ( is_wp_error( $goal_response ) ) {
			$this->log_debug( __METHOD__ . '(): Could not retrieve goal information' );
			return $goal_response;
		}

		// See if goal has already been created.
		$maybe_category_match = false;
		$maybe_action_match   = false;
		$maybe_label_match    = false;
		$goal_count           = 0;
		if ( isset( $goal_response['items'] ) ) {
			$goal_count = count( $goal_response['items'] ) + 1;
			foreach ( $goal_response['items'] as $item ) {
				$event_data = $item['eventDetails']['eventConditions'];
				foreach ( $event_data as $data ) {
					if ( 'CATEGORY' === $data['type'] && $event_category === $data['expression'] ) {
						$maybe_category_match = true;
						continue;
					}
					if ( 'ACTION' === $data['type'] && $event_action === $data['expression'] ) {
						$maybe_action_match = true;
						continue;
					}
					if ( 'LABEL' === $data['type'] && $event_label === $data['expression'] ) {
						$maybe_label_match = true;
					}
				}
			}
		} else {
			$goal_count = 1;
		}
		if ( ! $skip_goal_exists_check ) {
			// Goals match, return.
			if ( $maybe_category_match && $maybe_action_match ) {
				$this->log_debug( __METHOD__ . '(): Category and Action and Label already created. Aborting creating goal' );
				return true;
			}
		}

		// Goal doesn't exist and creating goals is false, so exit.
		if ( ! $create_goal ) {
			$this->log_debug( __METHOD__ . '(): Goal not created because automatic goal creation is turned off.' );
			return new WP_Error( 'goal_creation_disabled', 'Automatic goal creation is disabled.' );
		}

		// Create a new goal.
		$body_json = array(
			'active'       => true,
			'kind'         => 'analytics#goal',
			'type'         => 'EVENT',
			'eventDetails' => array(
				'useEventValue'   => true,
				'eventConditions' => array(
					array(
						'type'       => 'CATEGORY',
						'matchType'  => 'BEGINS_WITH',
						'expression' => $event_category,
					),
					array(
						'type'       => 'ACTION',
						'matchType'  => 'BEGINS_WITH',
						'expression' => $event_action,
					),
				),
			),
			'id'           => $this->get_next_goal_id( $this->get_goals( true ) ),
			'name'         => $goal_name,
		);
		if ( ! empty( $event_label ) ) {
			$body_json['eventDetails']['eventConditions'][] = array(
				'type'       => 'LABEL',
				'matchType'  => 'BEGINS_WITH',
				'expression' => $event_label,
			);
		}
		$body = wp_json_encode( $body_json );

		// Submit goal.
		$goal_insert_response = $this->api->create_goal(
			$body,
			$options['account']['account_id'],
			$options['account']['property'],
			$profile_id
		);
		if ( is_wp_error( $goal_insert_response ) ) {
			$this->log_debug( __METHOD__ . '(): Goal could not be created: ' . $goal_insert_response->get_error_code() . ': ' . $goal_insert_response->get_error_message() );
			return $goal_insert_response;
		}
		$this->log_debug( __METHOD__ . '(): Goal created.' );

		// Save Labels as option for goal.
		$label_goal_options                                      = get_option( 'gforms_google_analytics_goal_labels', array() );
		$label_goal_options[ $goal_insert_response['selfLink'] ] = sanitize_text_field( $custom_label );
		update_option(
			'gforms_google_analytics_goal_labels',
			$label_goal_options
		);

		return $goal_insert_response;
	}

	/**
	 * Get the next available goal ID for use in creating a new goal.
	 *
	 * @since  1.0.0
	 *
	 * @param array $goals The user's existing goals.
	 *
	 * @return int The next available numeric ID for the new goal.
	 */
	private function get_next_goal_id( $goals ) {
		$ids = array();
		if ( $goals ) {
			foreach ( $goals as $goal ) {
				array_push( $ids, rgar( $goal, 'goal_id' ) );
			}
			$range         = range( 1, 20 );
			$available_ids = array_diff( $range, $ids );
			return current( $available_ids );
		}
		return 1;
	}

	/**
	 * Updates an existing analytics goal.
	 *
	 * @since  1.0.0
	 *
	 * @param string $event_category         The new or existing event category.
	 * @param string $event_action           The new or existing event action.
	 * @param string $event_label            The new or existing event label.
	 * @param string $goal_name              The name for this goal.
	 * @param int    $goal_id                The ID of the goal to be updated.
	 *
	 * @return array|WP_Error The response from the API. An array if successful, otherwise a WP_Error object.
	 */
	private function update_analytics_goal( $event_category, $event_action, $event_label, $goal_name, $goal_id ) {
		$options      = $this->get_options();
		$mode         = $options['mode'];
		$custom_label = $event_label;

		// Check event label for default values.
		if ( '{form_title}::{source_page_number}::{current_page_number}' === $event_label ) {
			$event_label = '';
		}
		if ( '{form_title} ID: {form_id}' === $event_label ) {
			$event_label = '';
		}

		// Check event label for merge tags.
		if ( ! empty( $event_label ) && strstr( $event_label, '{' ) ) {
			$event_label = '';
			$this->log_debug( __METHOD__ . '(): Label has merge tags. Defaulting to empty for goal creation. Users can segment by label in Google Analytics' );
		}

		// Make sure values aren't empty.
		if ( empty( $event_category ) || empty( $event_action ) ) {
			$this->log_debug( __METHOD__ . '(): Goal creation aborted. Event category or action is empty.' );
			return new WP_Error( 'missing_values', 'Event Category or Action cannot be empty' );
		}

		/**
		 * Filter: gform_googleanalytics_goal_name.
		 *
		 * Filter the goal name so it can be changed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $goal_name       Goal name for the analytics goal.
		 * @param string $mode            The mode the plugins is in (ga, gmp, gtm).
		 * @param string $event_category  Event category for this goal.
		 * @param string $event_action    Event action for this goal.
		 * @param string $event_label     Event label for this goal.
		 *
		 * @return string New Goal name.
		 */
		$goal_name = apply_filters( 'gform_googleanalytics_goal_name', $goal_name, $mode, $event_category, $event_action, $event_label );
		$this->log_debug( __METHOD__ . '(): Updating Google Analytics Goal: ' . $goal_name );

		// Update a new goal.
		$body_json = array(
			'id'            => $goal_id,
			'accountId'     => $options['account']['account_id'],
			'webPropertyId' => $options['account']['property'],
			'profileId'     => $options['account']['view'],
			'goalId'        => $goal_id,
			'name'          => $goal_name,
			'type'          => 'event',
			'active'        => true,
			'eventDetails'  => array(
				'useEventValue'   => true,
				'eventConditions' => array(
					array(
						'type'       => 'CATEGORY',
						'matchType'  => 'BEGINS_WITH',
						'expression' => $event_category,
					),
					array(
						'type'       => 'ACTION',
						'matchType'  => 'BEGINS_WITH',
						'expression' => $event_action,
					),
				),
			),
		);
		if ( ! empty( $event_label ) ) {
			$body_json['eventDetails']['eventConditions'][] = array(
				'type'       => 'LABEL',
				'matchType'  => 'BEGINS_WITH',
				'expression' => $event_label,
			);
		}
		$body = wp_json_encode( $body_json );

		$goal_update_response = $this->api->update_goal(
			$body,
			$options['account']['account_id'],
			$options['account']['property'],
			$options['account']['view'],
			$goal_id
		);
		if ( is_wp_error( $goal_update_response ) ) {
			$this->log_debug( __METHOD__ . '(): Could not update goal: ' . $goal_id . ': ' . $goal_update_response->get_error_code() . ': ' . $goal_update_response->get_error_message() );
			return $goal_update_response;
		}

		// Save Labels as option for goal.
		$label_goal_options                                      = get_option( 'gforms_google_analytics_goal_labels', array() );
		$label_goal_options[ $goal_update_response['selfLink'] ] = sanitize_text_field( $custom_label );
		update_option(
			'gforms_google_analytics_goal_labels',
			$label_goal_options
		);

		return $goal_update_response;
	}

	/**
	 * Redirects to authentication screen for Google Analytics.
	 *
	 * @since  1.0.0
	 */
	public function authenticate_google_analytics() {
		$ga_options = get_option( 'gforms_google_analytics_ga' );
		$token      = isset( $ga_options['token'] ) ? $ga_options['token'] : false;
		if ( $token && ( isset( $ga_options['mode'] ) && 'ga' === $ga_options['mode'] ) ) {
			return;
		}
		$settings_mode = rgpost( '_gaddon_setting_mode' ) ? rgpost( '_gaddon_setting_mode' ) : 'gmp';
		$state         = array(
			'url'     => admin_url( 'admin.php' ),
			'page'    => 'gf_settings',
			'subview' => 'gravityformsgoogleanalytics',
			'mode'    => $settings_mode,
			'nonce'   => wp_create_nonce( 'gravityformsgoogleanalytics_ua' ),
		);
		$auth_url      = add_query_arg(
			array(
				'mode'        => $settings_mode,
				'redirect_to' => admin_url( 'admin.php' ),
				'state'       => base64_encode(
					json_encode(
						$state
					)
				),
				'license'     => GFCommon::get_key(),
			),
			$this->get_gravity_api_url( '/auth/googleanalytics' )
		);
		wp_safe_redirect( esc_url_raw( $auth_url ) );
		exit();
	}

	/**
	 * Redirects to authentication screen for Google Tag Manager.
	 *
	 * @since  1.0.0
	 */
	public function authenticate_google_tag_manager() {
		$ga_options = get_option( 'gforms_google_analytics_ga' );
		$token      = isset( $ga_options['token'] ) ? $ga_options['token'] : false;
		if ( $token && ( isset( $ga_options['mode'] ) && 'gtm' === $ga_options['mode'] ) ) {
			return;
		}
		$this->log_debug( __METHOD__ . '(): Before Authenticating With Tag Manager: ' . print_r( $ga_options, true ) );
		$state       = array(
			'url'     => admin_url( 'admin.php' ),
			'page'    => 'gf_settings',
			'subview' => 'gravityformsgoogleanalytics',
			'mode'    => 'gtm',
			'nonce'   => wp_create_nonce( 'gravityformsgoogleanalytics_ua' ),
		);
		$redirect_to = admin_url( 'admin.php' );
		$auth_url    = add_query_arg(
			array(
				'mode'        => 'gtm',
				'state'       => base64_encode(
					json_encode(
						$state
					)
				),
				'redirect_to' => $redirect_to,
				'license'     => GFCommon::get_key(),
			),
			$this->get_gravity_api_url( '/auth/googleanalytics' )
		);
		wp_safe_redirect( esc_url_raw( $auth_url ) );
		exit;
	}

	/**
	 * Retrieves a Google Analytics profile ID.
	 *
	 * @since  1.0.0
	 *
	 * @param int    $account_id  GA account number.
	 * @param string $ga_code     GA property code.
	 * @param string $view        GA View.
	 *
	 * @return string/WP_Error The GA profile ID.
	 */
	private function get_profile_id( $account_id, $ga_code, $view ) {
		if ( $this->initialize_api() ) {
			// Get profile ID.
			$body     = array();
			$response = $this->api->get_profile_id( $body, $account_id, $ga_code, $view );
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}
			$this->log_debug( __METHOD__ . '(): Retrieved Google Analytics profile ID' );
			$profile_id = '';
			foreach ( $response['items'] as $profile_data ) {
				if ( $view === $profile_data['id'] ) {
					$profile_id = $profile_data['id'];
					break;
				}
			}
			return $profile_id;
		}
		return '';
	}


	/**
	 * Initialize the pagination events.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form                The form arguments.
	 * @param int   $source_page_number  The original page number.
	 * @param int   $current_page_number The new page number.
	 */
	public function pagination( $form, $source_page_number, $current_page_number ) {

		if ( ! rgar( $form, 'gravityformsgoogleanalytics' ) && ! rgar( $form, 'gravityformsgoogleanalytics/google_analytics_pagination' ) ) {
			return;
		}

		if ( rgar( $form['gravityformsgoogleanalytics'], 'google_analytics_pagination' ) !== '1' ) {
			return;
		}

		if ( ! class_exists( 'GF_Google_Analytics_Pagination' ) ) {
			include_once 'includes/class-gf-google-analytics-pagination.php';
		}

		$settings = $this->get_options();
		if ( rgar( $settings, 'mode' ) ) {
			$mode = $settings['mode'];
		} else {
			$this->log_debug( __METHOD__ . '(): No tracking mode selected. Aborting pagination tracking.' );
			return;
		}
		$ua_code = $this->get_ua_code();

		$this->log_debug( __METHOD__ . '(): Before pagination event sent: ' . sprintf( '%s:%s:%d:%d', $ua_code, $mode, $source_page_number, $current_page_number ) );

		$pagination = GF_Google_Analytics_Pagination::get_instance();
		$pagination->paginate( $ua_code, $mode, $form, $source_page_number, $current_page_number );
	}

	/**
	 * Redirect to Gravity Forms API.
	 *
	 * @since 1.0.0
	 */
	public function ajax_redirect_to_api() {
		if ( ! wp_verify_nonce( rgpost( 'nonce' ), 'connect_google_analytics' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			wp_send_json_error(
				array(
					'errors'   => true,
					'redirect' => '',
				)
			);
		}

		$state = wp_create_nonce( 'gravityforms_googleanalytics_google_connect' );

		if ( get_transient( 'gravityapi_request_' . $this->get_slug() ) ) {
			delete_transient( 'gravityapi_request_' . $this->get_slug() );
		}

		set_transient( 'gravityapi_request_' . $this->get_slug(), $state, 10 * MINUTE_IN_SECONDS );
		$mode         = sanitize_text_field( rgpost( 'mode' ) );
		$action       = $mode == 'gtm' ? 'gtmselect' : 'gaselect';
		$settings_url = urlencode( admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug ) . '&action=' . $action );

		$auth_url = add_query_arg(
			array(
				'mode'        => sanitize_text_field( rgpost( 'mode' ) ),
				'redirect_to' => $settings_url,
				'state'       => $state,
				'license'     => GFCommon::get_key(),
			),
			$this->get_gravity_api_url( '/auth/googleanalytics' )
		);

		$options             = self::get_options();
		$options['tempmode'] = sanitize_text_field( rgpost( 'mode' ) ); // Temporary mode for storing what the user has selected for authorization.
		self::update_options( $options );

		$this->log_debug( "Redirecting to Gravity API: {$auth_url}" );

		wp_send_json_success(
			array(
				'errors'   => false,
				'redirect' => esc_url_raw( $auth_url ),
			)
		);
	}

	/**
	 * Update an analytics goal based on passed values.
	 *
	 * @since 1.0.0
	 */
	public function ajax_update_analytics_goal() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'create_analytics_goal' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_ga_error',
				wp_strip_all_tags( __( 'Permissions for form settings not met', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( ! $this->initialize_api() ) {
			$this->log_debug( __METHOD__ . '(): Could not initialize API' );
			$error = new WP_Error(
				'google_analytics_ga_error',
				wp_strip_all_tags( __( 'Could not initialize the Google Analytics API', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		$event_category = rgpost( 'eventcategory' );
		$event_action   = rgpost( 'eventaction' );
		$event_label    = rgpost( 'eventlabel' );
		$goal_name      = rgpost( 'goal' );
		$goal_id        = rgpost( 'goalId' );

		$request = $this->update_analytics_goal( $event_category, $event_action, $event_label, $goal_name, $goal_id );
		if ( $request && ! is_wp_error( $request ) ) {
			$return = array(
				'errors'         => false,
				'goal_updated'   => true,
				'goal_name'      => $goal_name,
				'event_category' => $event_category,
				'event_action'   => $event_action,
				'event_label'    => $event_label,
				'goal_id'        => $goal_id,
			);
		} else {
			wp_send_json_error( $request );
		}
		wp_send_json_success( $return );
	}

	/**
	 * Creates an analytics goal based on passed values.
	 *
	 * @since 1.0.0
	 */
	public function ajax_create_analytics_goal() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'create_analytics_goal' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_ga_error',
				wp_strip_all_tags( __( 'Permissions for form settings not met', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( ! $this->initialize_api() ) {
			$this->log_debug( __METHOD__ . '(): Could not initialize API' );
			$error = new WP_Error(
				'google_analytics_ga_error',
				wp_strip_all_tags( __( 'Could not initialize the Google Analytics API', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		$event_category = rgpost( 'eventcategory' );
		$event_action   = rgpost( 'eventaction' );
		$event_label    = rgpost( 'eventlabel' );
		$goal_name      = rgpost( 'goal' );
		$request        = $this->create_analytics_goal( $event_category, $event_action, $event_label, $goal_name, true, true );
		if ( $request && ! is_wp_error( $request ) ) {
			$return = array(
				'errors'       => false,
				'goal_created' => true,
				'option'       => sprintf(
					'<option value="%s" data-action="%s" data-label="%s" data-category="%s" data-goal-id="%s" selected="selected">%s</option>',
					esc_attr( $goal_name ),
					esc_attr( $event_action ),
					esc_attr( $event_label ),
					esc_attr( $event_category ),
					esc_attr( rgar( $request, 'id' ) ),
					esc_html( $goal_name )
				),
			);
		} else {
			wp_send_json_error( $request );
		}
		wp_send_json_success( $return );
	}

	/**
	 * Disconnects user from the Google Analytics API.
	 *
	 * @since 1.0.0
	 */
	public function ajax_disconnect_account() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'gforms_google_analytics_disconnect' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_disconnect_error',
				wp_strip_all_tags( __( 'Could not verify disconnect because permissions are not met', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( $this->initialize_api() ) {
			// Deleting option. Token expires in an hour, so no need to revoke it.
			delete_option( 'gravityformsaddon_gravityformsgoogleanalytics_settings' );
			wp_send_json_success( array() );
		}
		$error = new WP_Error(
			'google_analytics_disconnect_error',
			wp_strip_all_tags( __( 'Could not disconnect from the account', 'gravityformsgoogleanalytics' ) )
		);
		wp_send_json_error( $error );
	}

	/**
	 * Gets views for the selected account and GA code
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_ga_views() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'connect_google_analytics' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_ga_error',
				wp_strip_all_tags( __( 'Could not verify permissions for Google Analytics', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( $this->initialize_api() ) {
			$account_id = sanitize_text_field( rgpost( 'account_id' ) );
			$ga_code    = sanitize_text_field( rgpost( 'ga_code' ) );

			$body = array();

			$views = $this->api->get_views( $body, $account_id, $ga_code );
			if ( is_wp_error( $views ) ) {
				$this->log_debug( __METHOD__ . '(): Could not retrieve Google Analytics Views.' );
				die( '' );
			}

			// Output HTML.
			$html  = '';
			$html .= '<br /><select name="gaviews">';
			$html .= '<option value="">' . esc_html__( 'Select a view', 'gravityformsgoogleanalytics' ) . '</option>';
			foreach ( $views['items'] as $index => $item ) {
				$html .= sprintf(
					'<option value="%s" data-view-name="%s">%s</option>',
					esc_attr( $item['id'] ),
					esc_attr( $item['name'] ),
					esc_html( $item['name'] )
				);
			}
			$html .= '</select>';
			die( $html );
		}
		$error = new WP_Error(
			'google_analytics_ga_error',
			wp_strip_all_tags( __( 'Could not retrieve Google Analytics views', 'gravityformsgoogleanalytics' ) )
		);
		wp_send_json_error( $error );
	}

	/**
	 * Gets containers for the selected GTM account
	 *
	 * @since 1.1
	 */
	public function ajax_get_gtm_containers() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'connect_google_analytics' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_gtm_error',
				wp_strip_all_tags( __( 'Permissions for retrieving Tag Manager data not met', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( $this->initialize_api() ) {
			$account_id = rgpost( 'accountId' );
			$token      = rgpost( 'token' );

			// Test for variables already created.
			$body = array();

			// Get containers.
			$container_response = $this->api->get_tag_manager_containers( $body, $account_id );
			if ( is_wp_error( $container_response ) ) {
				$this->log_debug( __METHOD__ . '(): Error retrieving property containers.' );
				$error = new WP_Error(
					'google_analytics_gtm_error',
					$container_response->get_error_message()
				);
				wp_send_json_error( $error );
			}

			// Output HTML.
			$success = array( 'success' => true );
			$html    = '';
			$html   .= '<br /><select name="gacontainer" id="gacontainer">';
			$html   .= '<option value="">' . esc_html__( 'Select a Container', 'gravityformsgoogleanalytics' ) . '</option>';
			foreach ( $container_response['container'] as $container ) {
				$html .= sprintf(
					'<option data-account-id="%s" data-path="%s" data-token="%s" value="%s">%s</option>',
					esc_attr( $account_id ),
					esc_attr( $container['path'] ),
					esc_attr( $token ),
					esc_attr( $container['publicId'] ),
					esc_attr( $container['publicId'] )
				);
			}
			$html   .= '</select>';

			$success['body'] = $html;
			wp_send_json( $success );
		}
		$error = new WP_Error(
			'google_analytics_gtm_error',
			wp_strip_all_tags( __( 'Could not retrieve Tag Manager containers', 'gravityformsgoogleanalytics' ) )
		);
		wp_send_json_error( $error );
	}

	/**
	 * Gets workspaces for the selected GTM account
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_gtm_workspaces() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'connect_google_analytics' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_gtm_error',
				wp_strip_all_tags( __( 'Permissions for retrieving Tag Manager data not met', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( $this->initialize_api() ) {
			$path = rgpost( 'path' );

			// Test for variables already created.
			$body = array();

			// Get workspace ID.
			$response = $this->api->get_tag_manager_workspaces( $body, $path );
			if ( is_wp_error( $response ) ) {
				$error = new WP_Error(
					'google_analytics_gtm_error',
					wp_strip_all_tags( __( 'Could not retrieve Tag Manager workspaces', 'gravityformsgoogleanalytics' ) )
				);
				wp_send_json_error( $error );
			}

			// Output HTML.
			$success = array( 'success' => true );
			$html    = '';
			$html   .= '<select name="gaworkspace">';
			$html   .= '<option value="">' . esc_html__( 'Select a Workspace', 'gravityformsgoogleanalytics' ) . '</option>';
			foreach ( $response as $index => $workspaces ) {
				foreach ( $workspaces as $workspace ) {
					$html .= sprintf(
						'<option value="%s">%s</option>',
						esc_attr( $workspace['workspaceId'] ),
						esc_html( $workspace['name'] )
					);
				}
			}
			$html   .= '</select>';

			$success['body'] = $html;
			wp_send_json( $success );
		}
		$error = new WP_Error(
			'google_analytics_gtm_error',
			wp_strip_all_tags( __( 'Could not retrieve Tag Manager workspaces', 'gravityformsgoogleanalytics' ) )
		);
		wp_send_json_error( $error );
	}

	/**
	 * Updates plugin settings with the provided settings. Overrides parent to correctly update needed settings.
	 *
	 * @since 1.0
	 *
	 * @param array $settings Plugin settings to be saved.
	 */
	public function update_plugin_settings( $settings ) {
		$current_settings = $this->get_plugin_settings();
		if ( is_array( $current_settings ) ) {
			$settings = array_merge( $current_settings, $settings );
		}
		update_option( 'gravityformsaddon_' . $this->_slug . '_settings', $settings );
	}

	/**
	 * Saves analytics data to settings and provides redirect callback.
	 *
	 * @since  1.0.0
	 */
	public function ajax_save_google_analytics_data() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'connect_google_analytics' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_ga_error',
				wp_strip_all_tags( __( 'Permissions for saving Google Analytics data not met', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( $this->initialize_api() ) {
			$account_id   = rgpost( 'account_id' );
			$account_name = rgpost( 'account_name' );
			$ga_code      = rgpost( 'ga_code' );
			$token        = rgpost( 'token' );
			$refresh      = rgpost( 'refresh' );
			$view         = rgpost( 'view' );
			$view_name    = rgpost( 'view_name' );

			// Get profile id.
			$profile_id = $this->get_profile_id( $account_id, $ga_code, $view );
			if ( is_wp_error( $profile_id ) ) {
				$error = new WP_Error(
					'google_analytics_ga_error',
					wp_strip_all_tags( __( 'Could not retrieve the profile ID for Google Analytics', 'gravityformsgoogleanalytics' ) )
				);
				wp_send_json_error( $error );
			}

			$options               = $this->get_options();
			$options['auth_token'] = array(
				'token'        => sanitize_text_field( $token ),
				'refresh'      => sanitize_text_field( $refresh ),
				'date_created' => time(),
			);
			$options['mode']       = $this->get_options( '', 'tempmode' );
			$options['connected']  = true;
			$options['account']    = array(
				'account_id'   => sanitize_text_field( $account_id ),
				'account_name' => sanitize_text_field( $account_name ),
				'property_id'  => sanitize_text_field( $ga_code ),
				'profile_id'   => sanitize_text_field( $profile_id ),
				'property'     => sanitize_text_field( $ga_code ),
				'view'         => sanitize_text_field( $view ),
				'view_name'    => sanitize_text_field( $view_name ),
			);
			$this->log_debug( __METHOD__ . '(): Saving Analytics data' );

			// Save Google Analytics data.
			$options['gfgamode'] = $options['mode'];
			$this->update_plugin_settings( $options );

			// Build redirect url and return it.
			$redirect_url = add_query_arg(
				array(
					'page'    => 'gf_settings',
					'subview' => 'gravityformsgoogleanalytics',
				),
				admin_url( 'admin.php' )
			);
			die( esc_url_raw( $redirect_url ) );
		}
	}

	/**
	 * Saves analytics data to settings and provides redirect callback.
	 *
	 * @since  1.0.0
	 */
	public function ajax_save_google_tag_manager_data() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'connect_google_analytics' ) || ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
			$this->log_debug( __METHOD__ . '(): Permissions for form settings not met.' );
			$error = new WP_Error(
				'google_analytics_gtm_error',
				wp_strip_all_tags( __( 'Could not retrieve the correct permissions to set up Tag Manager', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		if ( $this->initialize_api() ) {
			$account_id         = rgpost( 'account_id' );
			$account_name       = rgpost( 'account_name' );
			$ga_code            = rgpost( 'ga_code' );
			$token              = rgpost( 'token' );
			$refresh            = rgpost( 'refresh' );
			$gtm_account        = rgpost( 'gtm_account_id' );
			$gtm_path           = rgpost( 'gtm_path' );
			$gtm_container      = rgpost( 'gtm_container' );
			$gtm_account_create = rgpost( 'gtm_auto_create' );
			$view               = rgpost( 'view' );
			$view_name          = rgpost( 'view_name' );
			$workspace          = rgpost( 'gtm_workspace' );

			// Get profile id.
			$profile_id = $this->get_profile_id( $account_id, $ga_code, $view );
			if ( is_wp_error( $profile_id ) ) {
				$error = new WP_Error(
					'google_analytics_gtm_error',
					wp_strip_all_tags( __( 'Could not retrieve the profile ID for Google Analytics.', 'gravityformsgoogleanalytics' ) )
				);
				wp_send_json_error( $error );
			}

			$options               = $this->get_options();
			$options['auth_token'] = array(
				'token'        => sanitize_text_field( $token ),
				'refresh'      => sanitize_text_field( $refresh ),
				'date_created' => time(),
			);
			$options['mode']       = 'gtm';
			$options['connected']  = true;
			$options['account']    = array(
				'account_id'   => sanitize_text_field( $account_id ),
				'account_name' => sanitize_text_field( $account_name ),
				'property_id'  => sanitize_text_field( $ga_code ),
				'container_id' => sanitize_text_field( $gtm_container ),
				'profile_id'   => sanitize_text_field( $profile_id ),
				'property'     => sanitize_text_field( $ga_code ),
				'view'         => sanitize_text_field( $view ),
				'view_name'    => sanitize_text_field( $view_name ),
				'workspace'    => sanitize_text_field( $workspace ),
			);

			$this->log_debug( __METHOD__ . '(): Begin Google Tag Manager saving.' );

			// Do not create GTM variables if user has opted out.
			$this->log_debug( __METHOD__ . '(): Google Tag Manager Opt Out Settings: ' . $gtm_account_create );
			if ( 'on' !== $gtm_account_create ) {
				$this->log_debug( __METHOD__ . '(): Google Tag Manager variables not created due to opt-out settings.' );
				$redirect_url = add_query_arg(
					array(
						'page'    => 'gf_settings',
						'subview' => 'gravityformsgoogleanalytics',
					),
					admin_url( 'admin.php' )
				);
				// Save Google Tag Manager data.
				$options['gfgamode'] = $options['mode'];
				$this->update_plugin_settings( $options );

				die( esc_url_raw( $redirect_url ) );
			}

			// Test for variables already created.
			$body = array();

			// Get workspace ID.
			$work_id_response = $this->api->get_tag_manager_workspaces( $body, $gtm_path );
			if ( is_wp_error( $work_id_response ) ) {
				$this->log_debug( __METHOD__ . '(): Could not retrieve Google Tag Manager workspaces.' );
				$error = new WP_Error(
					'google_analytics_gtm_error',
					wp_strip_all_tags( __( 'Could not retrieve the workspaces in Tag Manager.  Please check that your container, path, and workspace settings are correct.', 'gravityformsgoogleanalytics' ) )
				);
				wp_send_json_error( $error );
			}
			$work_id = $workspace;

			// Get variables.
			$dl_variable_response = $this->api->get_tag_manager_variables( $body, $gtm_path, $work_id );
			if ( is_wp_error( $dl_variable_response ) ) {
				$this->log_debug( __METHOD__ . '(): Could not retrieve Google Tag Manager variables.' );
				$error = new WP_Error(
					'google_analytics_gtm_error',
					wp_strip_all_tags( __( 'Could not retrieve the variables in Tag Manager', 'gravityformsgoogleanalytics' ) )
				);
				wp_send_json_error( $error );
			}
			$dl_variables_present = false;
			if ( isset( $dl_variable_response['variable'] ) ) {
				foreach ( $dl_variable_response['variable'] as $dl_variable ) {
					if ( 'DL - GFTrackCategory' === $dl_variable['name'] ) {
						$dl_variables_present = true;
						break;
					}
					if ( 'DL - GFTrackAction' === $dl_variable['name'] ) {
						$dl_variables_present = true;
						break;
					}
					if ( 'DL - GFTrackLabel' === $dl_variable['name'] ) {
						$dl_variables_present = true;
						break;
					}
					if ( 'DL - GFTrackValue' === $dl_variable['name'] ) {
						$dl_variables_present = true;
						break;
					}
				}
			}

			// Set new variables.
			if ( false === $dl_variables_present ) {

				$this->log_debug( __METHOD__ . '(): GTM variables not installed. Installing.' );

				$dl_variables = array(
					array(
						'name'       => 'DL - GFTrackCategory',
						'type'       => 'v',
						'variableId' => 'GFTrackCategory',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackCategory',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackAction',
						'type'       => 'v',
						'variableId' => 'GFTrackAction',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackAction',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackLabel',
						'type'       => 'v',
						'variableId' => 'GFTrackLabel',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackLabel',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackValue',
						'type'       => 'v',
						'variableId' => 'GFTrackValue',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackValue',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackSource',
						'type'       => 'v',
						'variableId' => 'GFTrackSource',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackSource',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackMedium',
						'type'       => 'v',
						'variableId' => 'GFTrackMedium',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackMedium',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackCampaign',
						'type'       => 'v',
						'variableId' => 'GFTrackCampaign',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackCampaign',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackContent',
						'type'       => 'v',
						'variableId' => 'GFTrackContent',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackContent',
							),
						),
					),
					array(
						'name'       => 'DL - GFTrackTerm',
						'type'       => 'v',
						'variableId' => 'GFTrackTerm',
						'parameter'  => array(
							array(
								'type'  => 'template',
								'key'   => 'name',
								'value' => 'GFTrackTerm',
							),
						),
					),
				);
				foreach ( $dl_variables as $dl_variable ) {
					$body                       = wp_json_encode( $dl_variable );
					$variable_creation_response = $this->api->save_google_tag_manager_variable( $body, $gtm_path, $work_id );
					if ( is_wp_error( $variable_creation_response ) ) {
						$this->log_debug( __METHOD__ . '(): Could not create variable in Tag Manager: ' . $dl_variable['name'] );
						$error = new WP_Error(
							'google_analytics_gtm_error',
							wp_strip_all_tags( __( 'Could not create the variables in Tag Manager', 'gravityformsgoogleanalytics' ) )
						);
						wp_send_json_error( $error );
					}
				}

				// Event JSON.
				$body             = '{
					"name": "GFTrackEvent",
					"type": "customEvent",
					"customEventFilter": [
					{
						"type": "equals",
						"parameter": [
						{
							"type": "template",
							"key": "arg0",
							"value": "{{_event}}"
						},
						{
							"type": "template",
							"key": "arg1",
							"value": "GFTrackEvent"
						}
						]
					}
					]
				}';
				$trigger_response = $this->api->create_google_tag_manager_trigger( $body, $gtm_path, $work_id );
				if ( is_wp_error( $trigger_response ) ) {
					$this->log_debug( __METHOD__ . '(): Could not create trigger in Tag Manager.' );
					$error = new WP_Error(
						'google_analytics_gtm_error',
						wp_strip_all_tags( __( 'Could not create the trigger in Tag Manager', 'gravityformsgoogleanalytics' ) )
					);
					wp_send_json_error( $error );
				}
				$trigger_id = isset( $trigger_response['triggerId'] ) ? $trigger_response['triggerId'] : 0;

				// Now let's create the tag JSON.
				$body = '{
					"name": "Gravity Forms Universal Analytics",
					"type": "ua",
					"parameter": [
					{
						"type": "template",
						"key": "trackingId",
						"value": "' . esc_js( $ga_code ) . '"
					},
					{
						"type": "template",
						"key": "trackType",
						"value": "TRACK_EVENT"
					},
					{
						"type": "template",
						"key": "eventCategory",
						"value": "{{DL - GFTrackCategory}}"
					},
					{
						"type": "template",
						"key": "eventAction",
						"value": "{{DL - GFTrackAction}}"
					},
					{
						"type": "template",
						"key": "eventLabel",
						"value": "{{DL - GFTrackLabel}}"
					},
					{
						"type": "template",
						"key": "eventValue",
						"value": "{{DL - GFTrackValue}}"
					},
					{
						"key": "fieldsToSet",
						"list": [
							{
								"map": [
									{
										"type": "template",
										"key": "fieldName",
										"value": "campaignSource"
									},
									{
										"type": "template",
										"key": "value",
										"value": "{{DL - GFTrackSource}}"
									}
								],
								"type": "map"
							},
							{
								"map": [
									{
										"type": "template",
										"key": "fieldName",
										"value": "campaignMedium"
									},
									{
										"type": "template",
										"key": "value",
										"value": "{{DL - GFTrackMedium}}"
									}
								],
								"type": "map"
							},
							{
								"map": [
									{
										"type": "template",
										"key": "fieldName",
										"value": "campaignName"
									},
									{
										"type": "template",
										"key": "value",
										"value": "{{DL - GFTrackCampaign}}"
									}
								],
								"type": "map"
							},
							{
								"map": [
									{
										"type": "template",
										"key": "fieldName",
										"value": "campaignContent"
									},
									{
										"type": "template",
										"key": "value",
										"value": "{{DL - GFTrackContent}}"
									}
								],
								"type": "map"
							},
							{
								"map": [
									{
										"type": "template",
										"key": "fieldName",
										"value": "campaignTerm"
									},
									{
										"type": "template",
										"key": "value",
										"value": "{{DL - GFTrackTerm}}"
									}
								],
								"type": "map"
							}
						],
						"type": "list"
					}
					],
					"firingTriggerId": [
					"' . esc_js( $trigger_id ) . '"
					],
				}';

				// Trigger created, now to set up the tag.
				$tag_response = $this->api->create_tag_manager_tag( $body, $gtm_path, $work_id );
				if ( is_wp_error( $tag_response ) ) {
					$this->log_debug( __METHOD__ . '(): Could not create tag in Tag Manager.' );
					$error = new WP_Error(
						'google_analytics_gtm_error',
						wp_strip_all_tags( __( 'Could not create tag in Tag Manager', 'gravityformsgoogleanalytics' ) )
					);
					wp_send_json_error( $error );
				}

				$this->log_debug( __METHOD__ . '(): GTM Tags installed.' );

				// Create new workspace version.
				$body                       = '{
					"name": "Gravity Forms Version",
					"notes": "Gravity Forms Update"
				}';
				$workspace_version_response = $this->api->save_update_tag_manager_version( $body, $gtm_path, $work_id );
				if ( is_wp_error( $workspace_version_response ) ) {
					$this->log_debug( __METHOD__ . '(): Could not create a new version for the container in Tag Manager.' );
					die( '' );
				}
				$version_id = $workspace_version_response['containerVersion']['containerVersionId'];

				// Make version live.
				$body             = array();
				$publish_response = $this->api->publish_google_tag_manager_container( $body, $gtm_path, $version_id );
				if ( is_wp_error( $publish_response ) ) {
					$this->log_debug( __METHOD__ . '(): Could not publish container for Tag Manager' );
					die( '' );
				}

				$this->log_debug( __METHOD__ . '(): Tag Manager Container published.' );

				// We are successful. Variables created, tags created, trigger created, and published.
			} else {
				$this->log_debug( __METHOD__ . '(): Tag Manager already installed.' );
			}

			// Save Google Analytics data.
			$this->update_options( $options );
			$this->update_plugin_settings(
				array(
					'gfgamode' => $options['mode'],
				)
			);

			// Build redirect url and return it.
			$redirect_url = add_query_arg(
				array(
					'page'    => 'gf_settings',
					'subview' => 'gravityformsgoogleanalytics',
				),
				admin_url( 'admin.php' )
			);
			die( esc_url_raw( $redirect_url ) );
		}
	}

	/**
	 * When a page is redirected, check if event is already sent via Ajax.
	 *
	 * @since  1.0.0
	 */
	public function ajax_get_entry_meta() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'gforms_google_analytics_confirmation' ) ) {
			wp_send_json_success( array( 'event_sent' => false ) );
		} else {
			$this->log_debug( __METHOD__ . '(): Nonce verification was not successful.' );
			$error = new WP_Error(
				'google_analytics_nonce_error',
				wp_strip_all_tags( __( 'Nonce validation failed.', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		$entry_id   = isset( $_REQUEST['entry_id'] ) ? absint( $_REQUEST['entry_id'] ) : null;
		$event_sent = gform_get_meta( $entry_id, 'GFConversionStatus' );
		if ( 'SENT' === $event_sent ) {
			wp_send_json_success( array( 'event_sent' => true ) );
		} else {
			wp_send_json_success( array( 'event_sent' => false ) );
		}
	}

	/**
	 * When a page is redirected, save entry meta to avoid duplicate events.
	 *
	 * @since  1.0.0
	 */
	public function ajax_save_entry_meta() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'gforms_google_analytics_confirmation' ) ) {
			wp_send_json_success( array( 'meta_saved' => false ) );
		} else {
			$this->log_debug( __METHOD__ . '(): Nonce verification was not successful.' );
			$error = new WP_Error(
				'google_analytics_nonce_error',
				wp_strip_all_tags( __( 'Nonce validation failed.', 'gravityformsgoogleanalytics' ) )
			);
			wp_send_json_error( $error );
		}
		$entry_id = isset( $_REQUEST['entry_id'] ) ? absint( $_REQUEST['entry_id'] ) : null;
		gform_update_meta( $entry_id, 'GFConversionStatus', 'SENT' );
		$event_sent = gform_get_meta( $entry_id, 'GFConversionStatus' );
		if ( 'SENT' === $event_sent ) {
			wp_send_json_success( array( 'meta_saved' => true ) );
		} else {
			wp_send_json_success( array( 'meta_saved' => false ) );
		}
	}

	/**
	 * Append event data to the URL.
	 *
	 * @since  1.0.0
	 *
	 * @param string|array $confirmation Confirmation for the form, can be a page redirect.
	 * @param object       $form         Form object.
	 * @param object       $entry        Current Entry.
	 * @param bool         $ajax         Whether the form was subitted via ajax.
	 *
	 * @return string|array $confirmation
	 */
	public function append_confirmation_url( $confirmation, $form, $entry, $ajax ) {
		if ( $ajax ) {
			return $confirmation;
		}
		if ( isset( $confirmation['redirect'] ) ) {
			$url = add_query_arg(
				array(
					'gfaction' => 'event_send',
					'category' => $this->conversion_category,
					'action'   => $this->conversion_action,
					'label'    => $this->conversion_label,
					'value'    => $this->conversion_value,
					'entryid'  => $this->conversion_entryid,
					'nonce'    => wp_create_nonce( 'gforms_google_analytics_confirmation' ),
				),
				$confirmation['redirect']
			);

			$confirmation['redirect'] = esc_url_raw( $url );
		}
		return $confirmation;
	}

	/**
	 * Remove unneeded settings.
	 *
	 * @since  1.0.0
	 */
	public function uninstall() {
		parent::uninstall();

		delete_option( 'gravityformsaddon_gravityformsgoogleanalytics_settings' );
		delete_option( 'gforms_google_analytics_goal_labels' );
		GFCache::delete( 'google_analytics_plugin_settings' );
	}

	/**
	 * Configures the settings for a connected option.
	 *
	 * Allows for disconnecting services and shows current status.
	 *
	 * @since  1.0.0
	 */
	public function settings_connected_label() {
		$options = $this->get_options();
		if ( ! isset( $options['mode'] ) ) {
			$options['mode'] = 'unset';
		}
		$disconnect_uri = esc_url_raw(
			add_query_arg(
				array(
					'page'    => 'gf_settings',
					'subview' => 'gravityformsgoogleanalytics',
					'action'  => 'gfgadisconnect',
					'nonce'   => wp_create_nonce( 'gforms_google_analytics_disconnect' ),
				),
				admin_url( 'admin.php' )
			)
		);
		$disconnect_link = sprintf( '<a href="%s" class="gfga-disconnect">%s</a> ', esc_url_raw( $disconnect_uri ), esc_html__( 'Disconnect', 'gravityformsgoogleanalytics' ) );
		if ( 'ga' === $options['mode'] ) {
			echo esc_html__( 'Google Analytics', 'gravityformsgoogleanalytics' ) . ' | ' . wp_kses_post( $disconnect_link );
		} elseif ( 'gtm' === $options['mode'] ) {
			echo esc_html__( 'Google Tag Manager', 'gravityformsgoogleanalytics' ) . ' | ' . wp_kses_post( $disconnect_link );
		} elseif ( 'gmp' === $options['mode'] ) {
			echo esc_html__( 'Google Measurement Protocol', 'gravityformsgoogleanalytics' ) . ' | ' . wp_kses_post( $disconnect_link );
		} else {
			echo esc_html__( 'Not connected', 'gravityformsgoogleanalytics' );
		}
	}

	/**
	 * Outputs the UA code if set.
	 *
	 * @since  1.0.0
	 */
	public function settings_uatext() {
		$options      = $this->get_options( 'account' );
		$account_name = isset( $options['account_name'] ) ? $options['account_name'] : '';
		if ( isset( $options['property'] ) && ! empty( $options['property'] ) ) {
			if ( $this->manual_configuration() ) {
				esc_html_e( 'Must be manually configured in tag manager', 'gravityformsgoogleanalytics' );
			} else {
				echo esc_html( $account_name ) . ' | ' . esc_html( $options['property'] );
			}
		} else {
			esc_html_e( 'Please authenticate against the Measurement Protocol, Google Analytics, or Tag Manager to get your GA code', 'gravityformsgoogleanalytics' );
		}
	}

	/**
	 * Outputs the mode in a hidden field.
	 *
	 * @since  1.0.0
	 */
	public function settings_uamode() {
		$mode = $this->get_options( '', 'mode' );
		printf( '<input type="hidden" value="%s" id="uamode" />', esc_attr( $mode ) );
	}

	/**
	 * Outputs the UA view if set.
	 *
	 * @since  1.0.0
	 */
	public function settings_uaview() {
		$options = $this->get_options( 'account' );
		if ( isset( $options['view'] ) && isset( $options['view_name'] ) ) {
			if ( $this->manual_configuration() ) {
				esc_html_e( 'Must be manually configured in tag manager', 'gravityformsgoogleanalytics' );
			} else {
				echo esc_html( $options['view_name'] ) . ' | ' . esc_html( $options['view'] );
			}
		} else {
			esc_html_e( 'Please authenticate to retrieve the view.', 'gravityformsgoogleanalytics' );
		}
	}

	/**
	 * Check if the form has an active Google Analytics feed and mode is valid.
	 *
	 * @since  1.0.0
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function frontend_script_callback( $form ) {

		if ( is_admin() ) {
			return false;
		}

		$settings = $this->get_plugin_settings();

		// Check for mode setting.
		if ( ! $this->is_frontend_scripts_mode_valid( $settings ) ) {
			return false;
		}

		// Load on a redirected page. Skip if measurement protocol is selected.
		$redirect_action = rgget( 'gfaction' );
		if ( $redirect_action ) {
			return true;
		}

		if ( ! $this->has_feed( $form['id'] ) && ! rgars( $form, 'gravityformsgoogleanalytics/pagination_gaeventcategory' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the mode is valid for frontend scripts
	 *
	 * @param array $settings The current plugin settings.
	 *
	 * @since  1.0.0
	 *
	 * @return bool false if there is no gfgamode or it is gmp, true otherwise.
	 */
	public function is_frontend_scripts_mode_valid( $settings ) {
		if ( ! isset( $settings['gfgamode'] ) || empty( $settings['gfgamode'] ) || 'gmp' === rgar( $settings, 'gfgamode' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the tokens from the auth payload, or settings if appropriate
	 *
	 * @since  1.0.0
	 *
	 * @param array $auth_payload the auth payload returned form Google.
	 * @param array $settings     an array of plugin settings.
	 *
	 * @return bool|array false if there is no auth payload or settings array, otherwise an array of auth tokens.
	 */
	public function maybe_get_tokens_from_auth_payload( $auth_payload, $settings ) {
		if ( empty( $auth_payload ) && ! rgar( $settings, 'auth_token' ) ) {
			return false;
		}

		$auth_tokens = array();

		if ( $auth_payload ) {
			$auth_tokens['token'] = rgar( $auth_payload, 'access_token' );
			$auth_tokens['refresh'] = rgar( $auth_payload, 'refresh_token' );
		} else {
			$auth_tokens['token']   = rgar( $settings['auth_token'], 'token' );
			$auth_tokens['refresh'] = rgar( $settings['auth_token'], 'refresh' );
		}

		return $auth_tokens;
	}

	/**
	 * Displays a Google Analytics Account box.
	 *
	 * @since  1.0.0
	 */
	public function settings_ga_select() {
		$auth_payload = $this->plugin_settings->get_auth_payload();

		if ( $this->initialize_api() ) {
			if ( ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
				return;
			}

			$auth_tokens = $this->maybe_get_tokens_from_auth_payload( $auth_payload, $this->get_plugin_settings() );

			if ( ! $auth_tokens ) {
				return false;
			}

			$token   = rgar( $auth_tokens, 'token' );
			$refresh = rgar( $auth_tokens, 'refresh' );

			$body = array();

			$response = $this->api->get_analytics_accounts( $body );

			if ( is_wp_error( $response ) ) {
				$this->log_debug( __METHOD__ . '(): Could not retrieve Google Analytics accounts' );
				return esc_html_e( 'It appears that you do not have a Google Analytics account for the account you selected.', 'gravityformsgoogleanalytics' );
			}

			if ( ! isset( $response['items'] ) ) {
				$this->connect_error = true;
				$gravity_url         = admin_url( 'admin.php?page=gf_settings&subview=gravityformsgoogleanalytics' );
				?>
				<style>
					#gform-settings-save {
						display: none;
					}
				</style>
				<p><?php esc_html_e( 'It appears that you do not have a Google Analytics account for the account you selected.', 'gravityformsgoogleanalytics' ); ?></p>
				<p><a class="button primary" href="<?php echo esc_url_raw( $gravity_url ); ?>"><?php esc_html_e( 'Return to Settings', 'gravityformsgoogleanalytics' ); ?></a></p>
				<?php
			} else {
				echo sprintf( '<input type="hidden" name="gfga_token" value="%s" />', esc_attr( $token ) );
				echo sprintf( '<input type="hidden" name="gfga_refresh" value="%s" />', esc_attr( $refresh ) );
				echo '<select name="gaproperty" id="gaproperty">';
				echo '<option value="">' . esc_html__( 'Select an account', 'gravityformsgoogleanalytics' ) . '</option>';
				foreach ( $response['items'] as $item ) {
					?>
					<optgroup label="<?php printf( '%s', esc_html( $item['name'] ) ); ?>">
						<?php
						$ga_child_url   = $item['childLink']['href'];
						$child_response = $this->api->get_child_account( $ga_child_url, $body );
						if ( is_wp_error( $child_response ) ) {
							return $child_response->get_error_message();
						}
						foreach ( $child_response['items'] as $child_item ) {
							printf(
								'<option value="%s" data-account-id="%s" data-ua-code="%s" data-token="%s" data-account-name="%s">%s %s</option>',
								esc_attr( $child_item['id'] ),
								esc_attr( $item['id'] ),
								esc_attr( $child_item['id'] ),
								esc_attr( rgar( $auth_payload, 'access_token' ) ),
								esc_html( $child_item['websiteUrl'] ),
								esc_html( $child_item['websiteUrl'] ),
								esc_html( $child_item['id'] )
							);
						}
						?>
					</optgroup>
					<?php
				}
				if ( rgget( 'action' ) === 'gtmselect' ) {
					?>
					<optgroup label="<?php esc_html_e( 'Don\'t see your account?', 'gravityformsgoogleanalytics' ); ?>">
					<option value="manual"><?php esc_html_e( 'I\'ll configure manually (required to use GA4)', 'gravityformsgoogleanalytics' ); ?></option>
					</optgroup>
					<?php
				}
				if ( rgget( 'action' ) === 'gaselect' ) {
					?>
					<optgroup label="<?php esc_html_e( 'Don\'t see your account?', 'gravityformsgoogleanalytics' ); ?>">
						<option value="self_config"><?php esc_html_e( 'I\'ll enter the details myself', 'gravityformsgoogleanalytics' ); ?></option>
					</optgroup>
					<?php
				}
				echo '</select>';
				?>
				<br /><div id="ga-views"></div>
				<?php
			}
		}
	}

	/**
	 * Displays a Google Tag Manager box.
	 *
	 * @since  1.0.0
	 */
	public function settings_gtm_select() {
		$auth_payload = $this->plugin_settings->get_auth_payload();
		if ( $this->initialize_api() || empty( $auth_payload ) ) {
			if ( ! $this->current_user_can_any( $this->_capabilities_form_settings ) ) {
				return;
			}

			$auth_tokens = $this->maybe_get_tokens_from_auth_payload( $auth_payload, $this->get_plugin_settings() );

			if ( ! $auth_tokens ) {
				return false;
			}

			$token   = rgar( $auth_tokens, 'token' );
			$refresh = rgar( $auth_tokens, 'refresh' );


			$body = array();

			$response = $this->api->get_tag_manager_account( $body );
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}

			if ( isset( $response['account'] ) ) {
				echo sprintf( '<input type="hidden" name="gfga_token" value="%s" />', esc_attr( $token ) );
				echo sprintf( '<input type="hidden" name="gfga_refresh" value="%s" />', esc_attr( $refresh ) );
				echo '<select name="gtmproperty" id="gtmproperty">';
				echo '<option value="">' . esc_html__( 'Select a Tag Manager Account', 'gravityformsgoogleanalytics' ) . '</option>';

				$google_tag_manager_array = array();

				foreach ( $response['account'] as $account ) {
					$google_tag_manager_array[ $account['name'] ] = array();
					?>
					<option data-account-name="<?php echo esc_attr( $account['name'] ); ?>" data-account-id="<?php echo esc_attr( $account['accountId'] ); ?>" data-token="<?php echo esc_attr( rgar( $auth_payload, 'access_token' ) ); ?>" value="<?php echo esc_attr( $account['name'] ); ?>"><?php echo esc_attr( $account['name'] ); ?></option>
					<?php
				}

				echo '</select>';
				?>
				<br /><div id="gtm-containers"></div>
				<br /><div id="gtm-workspaces"></div>
				<?php

				return;
			}

			// No GTM installed - Display a message.
			$this->connect_error = true;
			$gravity_url         = admin_url( 'admin.php?page=gf_settings&subview=gravityformsgoogleanalytics' );
			?>
			<style>
				#gform-settings-save {
					display: none;
				}
			</style>
			<p><?php esc_html_e( 'It appears that you do not have a Google Tag Manager account for the account you selected.', 'gravityformsgoogleanalytics' ); ?></p>
			<p><a class="button primary" href="<?php echo esc_url_raw( $gravity_url ); ?>"><?php esc_html_e( 'Return to Settings', 'gravityformsgoogleanalytics' ); ?></a></p>
			<?php
		}
	}

	/**
	 * Get Analytics Goals for the user based on the view selected.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Array of Existing event-based goals, or false if no goals exist or on error.
	 */
	public function get_goals( $all_goals = false ) {
		if ( ! $this->initialize_api() ) {
			return false;
		}

		$account = $this->get_options( 'account' );

		$body = array();

		// Retrieve Goals.
		$response = $this->api->get_goals(
			$body,
			$account['account_id'],
			$account['property_id'],
			$account['view']
		);
		if ( is_wp_error( $response ) ) {
			$this->log_debug( __METHOD__ . '(): Could not retrieve Google Analytics Goals.' );
			return false;
		}

		// Retrieve Goal Labels.
		$goal_labels = get_option( 'gforms_google_analytics_goal_labels', array() );

		$goal_information = array();
		if ( isset( $response['items'] ) ) {
			foreach ( $response['items'] as $goal_info ) {
				if ( 'EVENT' === $goal_info['type'] || $all_goals ) {
					$event_label    = isset( $goal_labels[ $goal_info['selfLink'] ] ) ? $goal_labels[ $goal_info['selfLink'] ] : '';
					$event_action   = '';
					$event_category = '';
					foreach ( $goal_info['eventDetails']['eventConditions'] as $condition ) {
						if ( 'CATEGORY' === $condition['type'] ) {
							$event_category = $condition['expression'];
						}
						if ( 'ACTION' === $condition['type'] ) {
							$event_action = $condition['expression'];
						}
						if ( 'LABEL' === $condition['type'] ) {
							$event_label = $condition['expression'];
						}
					}
					$goal_information[ $goal_info['id'] ] = array(
						'name'     => $goal_info['name'],
						'category' => $event_category,
						'action'   => $event_action,
						'label'    => $event_label,
						'goal_id'  => $goal_info['id'],
					);
				}
			}
		}

		if ( ! empty( $goal_information ) ) {
			return $goal_information;
		}
		return false;

	}

	/**
	 * Creates a placeholder for the Event Category.
	 *
	 * @since  1.0.0
	 */
	public function settings_event_category() {
		$this->get_event_setting( 'gaeventcategory' );
	}

	/**
	 * Creates a placeholder for the Event Action.
	 *
	 * @since  1.0.0
	 */
	public function settings_event_action() {
		$this->get_event_setting( 'gaeventaction' );
	}

	/**
	 * Creates a placeholder for the Event Label.
	 *
	 * @since  1.0.0
	 */
	public function settings_event_label() {
		$this->get_event_setting( 'gaeventlabel' );
	}

	/**
	 * Get the event setting or a placeholder if the setting is empty
	 *
	 * @param string $setting_name The name of the setting to retrieve.
	 *
	 * @since  1.0.0
	 */
	public function get_event_setting( $setting_name ) {
		$view = rgget( 'settingstype' );
		if ( $view === 'form' ) {
			$form = $this->get_current_form();
			if ( rgars( $form, 'gravityformsgoogleanalytics/pagination_' . $setting_name ) ) {
				$setting = 'pagination_' . $setting_name;
				echo '<p>' . esc_html( $form['gravityformsgoogleanalytics'][ $setting ] ) . '</p>';
			} else {
				echo '<p>' . esc_html__( 'empty', 'gravityformsgoogleanalytics' ) . '</p>';
			}
		} else {
			$feed = $this->get_current_feed();
			if ( isset( $feed['meta'][ $setting_name ] ) ) {
				echo '<p>' . esc_html( $feed['meta'][ $setting_name ] ) . '</p>';
			} else {
				echo '<p>' . esc_html__( 'empty', 'gravityformsgoogleanalytics' ) . '</p>';
			}
		}
	}

	/**
	 * Creates the select goal field type for the feed settings page.
	 *
	 * @since  1.0.0
	 */
	public function settings_select_goal() {
		$view = rgget( 'settingstype' );
		$feed = $this->get_current_feed();
		$form = $this->get_current_form();
		if ( $view === 'form' ) {
			if ( $this->manual_configuration() ) {
				if ( rgar( $form, 'gravityformsgoogleanalytics' ) && rgar( $form['gravityformsgoogleanalytics'], 'pagination_gaeventgoal' ) ) {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="set_values_popup">%s</a>',
						esc_html( $form['gravityformsgoogleanalytics']['pagination_gaeventgoal'] ),
						esc_html__( 'Set Values', 'gravityformsgoogleanalytics' )
					);
				} else {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="set_values_popup">%s</a>',
						esc_html__( 'No values saved', 'gravityformsgoogleanalytics' ),
						esc_html__( 'Set Values', 'gravityformsgoogleanalytics' )
					);
				}
			} else {
				if ( rgar( $form, 'gravityformsgoogleanalytics' ) && rgar( $form['gravityformsgoogleanalytics'], 'pagination_gaeventgoal' ) ) {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="select_goal_popup">%s</a>',
						esc_html( $form['gravityformsgoogleanalytics']['pagination_gaeventgoal'] ),
						esc_html__( 'Select Goal', 'gravityformsgoogleanalytics' )
					);
				} else {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="select_goal_popup">%s</a>',
						esc_html__( 'No goal selected', 'gravityformsgoogleanalytics' ),
						esc_html__( 'Select or create a new goal', 'gravityformsgoogleanalytics' )
					);
				}
			}
		} else {
			if ( $this->manual_configuration() ) {
				if ( rgar( $feed, 'meta' ) && rgar( $feed['meta'], 'gaeventgoal' ) ) {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="set_values_popup">%s</a>',
						esc_html( $feed['meta']['gaeventgoal'] ),
						esc_html__( 'Set Values', 'gravityformsgoogleanalytics' )
					);
				} else {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="set_values_popup">%s</a>',
						esc_html__( 'No values saved', 'gravityformsgoogleanalytics' ),
						esc_html__( 'Set Values', 'gravityformsgoogleanalytics' )
					);
				}
			} else {
				if ( rgar( $feed, 'meta' ) && rgar( $feed['meta'], 'gaeventgoal' ) ) {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="select_goal_popup">%s</a>',
						esc_html( $feed['meta']['gaeventgoal'] ),
						esc_html__( 'Select Goal', 'gravityformsgoogleanalytics' )
					);
				} else {
					printf(
						'<span id="selected_goal">%s</span> | <a href="#" id="select_goal_popup">%s</a>',
						esc_html__( 'No goal selected', 'gravityformsgoogleanalytics' ),
						esc_html__( 'Select or create a new goal', 'gravityformsgoogleanalytics' )
					);
				}
			}
		}

		if ( $this->manual_configuration() ) {
			$this->manual_config_modal();
		} else {
			$this->goal_selection_modal();
		}
	}

	/**
	 * A modal for manual configuration of event attributes.
	 *
	 * @since 1.0.0
	 */
	public function manual_config_modal() {
		$form = rgget( 'id' ) ? GFAPI::get_form( rgget( 'id' ) ) : null;
		?>
		<div id="thickbox_set_values">
			<div class="gform-addon-conversion-tracking-modal gform-addon-gtm-manual-config">
				<?php wp_nonce_field( 'create_analytics_goal', 'create_ga_goal' ); ?>
				<?php echo $this->manual_config_modal_fields( $form ); ?>
				<div class="gform-addon-conversion-tracking-modal__actions gform-settings-panel__content">
					<?php echo $this->save_buttons(); ?>
					<button id="close_manual_config" class="gform-button gform-button--white gform-button--size-xs">
						<?php echo esc_html__( 'Cancel', 'gravityformsgoogleanalytics' ); ?>
					</button>
				</div>
			</div>
		</div><!-- #thickbox_goal_select -->
		<?php
	}

	/**
	 * Creates the modal where the user can select, create, and edit feeds.
	 *
	 * @since  1.0.0
	 */
	public function goal_selection_modal() {
		$form    = rgget( 'id' ) ? GFAPI::get_form( rgget( 'id' ) ) : null;
		$goals   = $this->get_goals();
		$choices = array();
		if ( $goals ) {
			foreach ( $goals as $goal ) {
				$choices[] = array(
					'label'         => $goal['name'],
					'value'         => $goal['goal_id'],
					'data-action'   => $goal['action'],
					'data-label'    => $goal['label'],
					'data-category' => $goal['category'],
					'data-goal-id'  => $goal['goal_id'],
				);
			}
		}
		?>
		<div id="thickbox_goal_select">
			<div class="gform-addon-conversion-tracking-modal">
				<fieldset id="goal_select_edit" class="gform-settings-panel gform-settings-panel--with-title">
					<legend class="goal-creation gform-settings-panel__title gform-settings-panel__title--header">
						<span class="gform-settings-panel__title-wrapper"><?php echo esc_html__( 'Google Analytics Goal', 'gravityformsgoogleanalytics' ); ?></span>
						<button class="gform-settings-panel__title-action gform-button gform-button--primary gform-button--size-xs">
							<?php echo esc_html__( 'New Goal', 'gravityformsgoogleanalytics' ); ?>
						</button>
					</legend>
					<?php wp_nonce_field( 'create_analytics_goal', 'create_ga_goal' ); ?>
					<div class="gforms-goal-selection gform-settings-panel__content">
						<div class="gform-settings-field gform-settings-field__select">
							<div class="gform-settings-field__header">
								<label class="gform-settings-label" for="goal_select">
									<?php echo esc_html__( 'Select a Goal', 'gravityformsgoogleanalytics' ); ?>
								</label>
							</div>
							<span class="gform-settings-input__container">
								<select id="goal_select" name="_gaddon_setting_gagoalselect">
									<optgroup label="<?php echo esc_html__( 'Select a Goal', 'gravityformsgoogleanalytics' ); ?>" id="ga_goals">
										<?php
										foreach ( $choices as $choice ) {
											printf(
												'<option value="%s" data-action="%s" data-label="%s" data-category="%s" data-goal-id="%s" %s>%s</option>',
												esc_attr( $choice['data-goal-id'] ),
												esc_attr( $choice['data-action'] ),
												esc_attr( $choice['data-label'] ),
												esc_attr( $choice['data-category'] ),
												esc_attr( $choice['data-goal-id'] ),
												selected( isset( $feed['meta']['gagoalselect'] ) ? $feed['meta']['gagoalselect'] : false, $choice['data-goal-id'], false ),
												esc_html( $choice['label'] )
											);
										}
										?>
									</optgroup>
								</select>
							</span>
						</div>
				</fieldset>
				<?php echo $this->modal_fields( $form ); ?>
				<div class="gform-addon-conversion-tracking-modal__actions gform-settings-panel__content">
					<?php echo $this->save_buttons(); ?>
					<button id="cancel_goal" class="gform-button gform-button--white gform-button--size-xs">
						<?php echo esc_html__( 'Cancel', 'gravityformsgoogleanalytics' ); ?>
					</button>
				</div>
			</div>
		</div><!-- #thickbox_goal_select -->
		<?php
	}

	/**
	 * Create modal fields for manual configuration.
	 *
	 * @since  1.0.0
	 */
	public function manual_config_modal_fields( $form ) {
		?>
		<fieldset id="goal_labels" class="gform-settings-panel gform-settings-panel--with-title">
			<legend class="gform-settings-panel__title gform-settings-panel__title--header">
				<span class="gforms_goal_create_heading">
					<?php echo esc_html__( 'Set Event Values', 'gravityformsgoogleanalytics' ); ?>
				</span>
			</legend>
			<div class="gform-settings-panel__content">
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_goal_thickbox">
							<?php echo esc_html__( 'Goal Name', 'gravityformsgoogleanalytics' ); ?>
						</label>
					</div>
					<?php
					$this->settings_hidden(
						array(
							'name'          => 'gaeventgoalid_thickbox',
							'default_value' => '',
						)
					);
					$this->settings_text(
						array(
							'name'          => 'gaeventgoal_thickbox',
							'class'         => 'ga_event_goal_thickbox disabled',
							'default_value' => __( 'Submission:', 'gravityformsgoogleanalytics' ) . ' ' . $form['title'],
							'id'            => 'ga_event_goal_thickbox',
							'placeholder'   => __( 'Goal Name', 'gravityformsgoogleanalytics' ),
						)
					);
					?>
				</div>
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_category_thickbox">
							<?php echo esc_html__( 'Event Category', 'gravityformsgoogleanalytics' ); ?>
							<span class="required"><?php echo esc_html__( '(Required)', 'gravityformsgoogleanalytics' ); ?></span>
						</label>
					</div>
					<?php
					$this->settings_text(
						array(
							'name'          => 'gaeventcategory_thickbox',
							'class'         => 'ga_event_category_thickbox disabled',
							'default_value' => '',
							'id'            => 'ga_event_category_thickbox',
							'placeholder'   => __( 'Event Category', 'gravityformsgoogleanalytics' ),
							'required'      => true,
						)
					);
					?>
				</div>
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_action_thickbox">
							<?php echo esc_html__( 'Event Action', 'gravityformsgoogleanalytics' ); ?>
							<span class="required"><?php echo esc_html__( '(Required)', 'gravityformsgoogleanalytics' ); ?></span>
						</label>
					</div>
					<?php
					$this->settings_text(
						array(
							'name'          => 'gaeventaction_thickbox',
							'class'         => 'ga_event_action_thickbox disabled',
							'default_value' => '',
							'id'            => 'ga_event_action_thickbox',
							'placeholder'   => __( 'Event Action', 'gravityformsgoogleanalytics' ),
							'required'      => true,
						)
					);
					?>
				</div>
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_label_thickbox">
							<?php echo esc_html__( 'Event Label', 'gravityformsgoogleanalytics' ); ?>
						</label>
					</div>
					<?php
					$this->settings_text(
						array(
							'name'          => 'gaeventlabel_thickbox',
							'class'         => 'ga_event_label_thickbox disabled merge-tag-support mt-position-right',
							'default_value' => '{form_title} ID: {form_id}',
							'id'            => 'ga_event_label_thickbox',
							'placeholder'   => __( 'Event Label', 'gravityformsgoogleanalytics' ),
						)
					);
					?>
				</div>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Create the goal fields for the modal.
	 *
	 * @since  1.0.0
	 *
	 * @param object $form The current form.
	 */
	public function modal_fields( $form ) {
		?>
		<fieldset id="goal_labels" class="gform-settings-panel gform-settings-panel--with-title">
			<legend class="gform-settings-panel__title gform-settings-panel__title--header">
				<span class="gforms_goal_edit_heading">
					<?php echo esc_html__( 'Edit Google Analytics Goal', 'gravityformsgoogleanalytics' ); ?>
				</span>
				<span class="gforms_goal_create_heading">
					<?php echo esc_html__( 'Create a Google Analytics Goal', 'gravityformsgoogleanalytics' ); ?>
				</span>
			</legend>
			<div class="gform-settings-panel__content">
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_goal_thickbox">
							<?php echo esc_html__( 'Goal Name', 'gravityformsgoogleanalytics' ); ?>
						</label>
					</div>
					<?php
					$this->settings_hidden(
						array(
							'name'          => 'gaeventgoalid_thickbox',
							'default_value' => '',
						)
					);
					$this->settings_text(
						array(
							'name'          => 'gaeventgoal_thickbox',
							'class'         => 'ga_event_goal_thickbox disabled',
							'default_value' => __( 'Submission:', 'gravityformsgoogleanalytics' ) . ' ' . $form['title'],
							'id'            => 'ga_event_goal_thickbox',
							'placeholder'   => __( 'Goal Name', 'gravityformsgoogleanalytics' ),
						)
					);
					?>
				</div>
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_category_thickbox">
							<?php echo esc_html__( 'Event Category', 'gravityformsgoogleanalytics' ); ?>
							<span class="required"><?php echo esc_html__( '(Required)', 'gravityformsgoogleanalytics' ); ?></span>
						</label>
					</div>
					<?php
					$this->settings_text(
						array(
							'name'          => 'gaeventcategory_thickbox',
							'class'         => 'ga_event_category_thickbox disabled',
							'default_value' => '',
							'id'            => 'ga_event_category_thickbox',
							'placeholder'   => __( 'Event Category', 'gravityformsgoogleanalytics' ),
							'required'      => true,
						)
					);
					?>
				</div>
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_action_thickbox">
							<?php echo esc_html__( 'Event Action', 'gravityformsgoogleanalytics' ); ?>
							<span class="required"><?php echo esc_html__( '(Required)', 'gravityformsgoogleanalytics' ); ?></span>
						</label>
					</div>
					<?php
					$this->settings_text(
						array(
							'name'          => 'gaeventaction_thickbox',
							'class'         => 'ga_event_action_thickbox disabled',
							'default_value' => '',
							'id'            => 'ga_event_action_thickbox',
							'placeholder'   => __( 'Event Action', 'gravityformsgoogleanalytics' ),
							'required'      => true,
						)
					);
					?>
				</div>
				<div class="gform-settings-field gform-settings-field__text">
					<div class="gform-settings-field__header">
						<label class="gform-settings-label" for="ga_event_label_thickbox">
							<?php echo esc_html__( 'Event Label', 'gravityformsgoogleanalytics' ); ?>
						</label>
					</div>
					<?php
					$this->settings_text(
						array(
							'name'          => 'gaeventlabel_thickbox',
							'class'         => 'ga_event_label_thickbox disabled merge-tag-support mt-position-right',
							'default_value' => '{form_title} ID: {form_id}',
							'id'            => 'ga_event_label_thickbox',
							'placeholder'   => __( 'Event Label', 'gravityformsgoogleanalytics' ),
						)
					);
					?>
				</div>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Create the save buttons for the modal.
	 *
	 * @since  1.0.0
	 */
	public function save_buttons() {
		if ( ! $this->manual_configuration() ) {
			?>
		<input
			type="submit"
			name="gform-settings-save"
			value="<?php echo esc_html__( 'Select', 'gravityformsgoogleanalytics' ); ?>"
			class="gform-button gform-button--primary gform-button--size-xs gaddon-setting gaddon-submit"
			id="use_goal"
		>
		<button
			id="edit_goal"
			class="gform-button gform-button--white gform-button--size-xs"
		>
			<?php echo esc_html__( 'Edit', 'gravityformsgoogleanalytics' ); ?>
		</button>
		<input
			type="submit"
			name="gform-settings-save"
			value="<?php echo esc_html__( 'Save Goal', 'gravityformsgoogleanalytics' ); ?>"
			class="gform-button gform-button--primary gform-button--size-xs gaddon-setting gaddon-submit"
			id="update_goal"
		>
		<input
			type="submit"
			name="gform-settings-save"
			value="<?php echo esc_html__( 'Create New Goal', 'gravityformsgoogleanalytics' ); ?>"
			class="gform-button gform-button--primary gform-button--size-xs gaddon-setting gaddon-submit"
			id="create_goal"
		>
			<?php
		} else {
			?>
			<input
					type="submit"
					name="gform-settings-save"
					value="<?php echo esc_html__( 'Save Values', 'gravityformsgoogleanalytics' ); ?>"
					class="gform-button gform-button--primary gform-button--size-xs gaddon-setting gaddon-submit"
					id="set_event_values"
			>
			<?php
		}
	}

	/**
	 * Sets a nonce for Google Analytics and GTM
	 *
	 * @since  1.0.0
	 */
	public function settings_nonce_connect() {
		echo sprintf( '<input type="hidden" name="gfganonce" value="%s" />', esc_attr( wp_create_nonce( 'connect_google_analytics' ) ) );
	}

	/**
	 * Sets an action variable for Google Analytics.
	 *
	 * @since  1.0.0
	 */
	public function settings_ga_action() {
		echo '<input type="hidden" name="gfgaaction" value="ga" />';
	}

	/**
	 * Sets an action variable for Google Tag Manager.
	 *
	 * @since  1.0.0
	 */
	public function settings_gtm_action() {
		echo '<input type="hidden" name="gfgaaction" value="gtm" />';
	}

	/**
	 * Update auth tokens.
	 *
	 * @since  1.0.0
	 */
	public function plugin_settings_page() {

		$this->plugin_settings->maybe_update_auth_tokens();

		parent::plugin_settings_page();

	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @since  1.0.0
	 */
	public function plugin_settings_fields() {
		return $this->plugin_settings->get_fields();
	}

	/**
	 * Get the Google Analytics UA Code
	 *
	 * @since 1.0.0
	 *
	 * @return string/bool Returns string UA code, false otherwise
	 */
	private function get_ua_code() {
		$ua_code = $this->get_options( 'account', 'property' );

		$ua_regex = '/^UA-[0-9]{5,}-[0-9]{1,}$/';

		if ( is_string( $ua_code ) && preg_match( $ua_regex, $ua_code ) ) {
			return $ua_code;
		}
		return false;
	}

	/**
	 * Load UA Settings
	 *
	 * @since 1.0.0
	 *
	 * @return bool Returns true if UA ID is loaded, false otherwise
	 */
	private function load_ua_settings() {

		$this->ua_id = $this->get_ua_code();

		if ( false !== $this->ua_id ) {
			return true;
		}
		return false;
	}

	/**
	 * Get GA ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Returns found UA ID or empty string if not found.
	 */
	public function get_ga_id() {
		$this->load_ua_settings();
		if ( false === $this->ua_id ) {
			return '';
		} else {
			return $this->ua_id;
		}
	}

	/**
	 * Return Google Analytics GA Codes
	 *
	 * @since 1.0.0
	 *
	 * @param string $feed_ua     The UA code from the feed meta.
	 * @param string $settings_ua The UA code from the settings.
	 *
	 * @return array Array of GA codes
	 */
	private function get_ua_codes( $feed_ua, $settings_ua ) {
		$google_analytics_codes   = array();
		$google_analytics_codes[] = $this->get_options( 'account', 'property' );

		if ( ! empty( $feed_ua ) ) {
			$ga_ua = explode( ',', $feed_ua );
			if ( is_array( $ga_ua ) ) {
				foreach ( $ga_ua as &$value ) {
					$value = trim( $value );
				}
			}
			$google_analytics_codes = $ga_ua;
		}
		if ( $settings_ua ) {
			$google_analytics_codes[] = $settings_ua;
		}
		$google_analytics_codes = array_unique( $google_analytics_codes );
		return $google_analytics_codes;
	}

	/**
	 * Call form_settings from the form settings class.
	 *
	 * @since  1.0.0
	 *
	 * @param array $form  The current form.
	 */
	public function form_settings( $form ) {
		return $this->form_settings->form_settings_page( $form );
	}

	/**
	 * Call form_settings_fields from the form settings class.
	 *
	 * @since  1.0.0
	 *
	 * @param array $form  The current form.
	 */
	public function form_settings_fields( $form ) {
		return $this->form_settings->pagination_form_settings( $form );
	}

	/**
	 * Call feed_settings_fields from the form settings class.
	 *
	 * @since  1.0.0
	 */
	public function feed_settings_fields() {
		return $this->form_settings->get_feed_settings_fields();
	}

	/**
	 * Call feed_list_columns from the form settings class.
	 *
	 * @since  1.0.0
	 */
	public function feed_list_columns() {
		return $this->form_settings->feed_list_columns();
	}

	/**
	 * Set feed creation control.
	 *
	 * @since  1.0
	 *
	 * @return bool
	 */
	public function can_create_feed() {

		if ( $this->initialize_api() ) {
			return true;
		}

		return false;
	}

	/**
	 * Processes the feed.
	 *
	 * @since  1.0.0
	 *
	 * @param array $feed  The feed to process.
	 * @param array $entry The entry to process.
	 * @param array $form  The form the feed is coming from.
	 */
	public function process_feed( $feed, $entry, $form ) {

		// Replace merge tags.
		$event_vars = array( 'gaeventua', 'gaeventcategory', 'gaeventaction', 'gaeventlabel', 'gaeventvalue' );
		foreach ( $event_vars as $var ) {
			if ( ! empty( $feed['meta'][ $var ] ) ) {
				$value                = $feed['meta'][ $var ];
				$feed['meta'][ $var ] = GFCommon::replace_variables( $value, $form, $entry, false, false, true, 'text' );
			}
		}

		// Set entry ID.
		$this->conversion_entryid = $entry['id'];

		// Get all analytics codes to send.
		$google_analytics_codes = $this->get_ua_codes( isset( $feed['meta']['gaeventua'] ) ? $feed['meta']['gaeventua'] : '', $this->get_ga_id() );

		/**
		 * Filter: gform_googleanalytics_ua_ids
		 *
		 * Filter all outgoing UA IDs to send events to using the measurement protocol.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $google_analytics_codes UA codes
		 * @param object $form Gravity Form form object
		 * @param object $entry Gravity Form Entry Object
		 * @return array google anaylics codes
		 */
		$google_analytics_codes = apply_filters( 'gform_googleanalytics_ua_ids', $google_analytics_codes, $form, $entry );

		// Initialize the measurement protocol.
		if ( ! class_exists( 'GF_Google_Analytics_Measurement_Protocol' ) ) {
			include_once 'includes/class-gf-google-analytics-measurement-protocol.php';
		}
		$event = new GF_Google_Analytics_Measurement_Protocol();
		$event->init();

		// Set up some event defaults.
		$event->set_document_path( str_replace( home_url(), '', $entry['source_url'] ) );
		$event_url_parsed = wp_parse_url( home_url() );
		$event->set_document_host( $event_url_parsed['host'] );
		$event->set_document_location( esc_url( $entry['source_url'] ) );
		$ip_address = '127.0.0.1';
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		$event->set_user_ip_address( $ip_address );

		// Try to get the document title.
		global $post;
		$document_title = isset( $post ) && isset( $post->post_title ) ? sanitize_text_field( $post->post_title ) : esc_html__( 'No title found', 'gravityformsgoogleanalytics' );
		$event->set_document_title( $document_title );

		/**
		 * Filter: gform_googleanalytics_event_category
		 *
		 * Filter the event category dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param string $category Event Category
		 * @param object $form     Gravity Form form object
		 * @param object $entry    Gravity Form Entry Object
		 */
		$event_category = apply_filters( 'gform_googleanalytics_event_category', $feed['meta']['gaeventcategory'], $form, $entry );
		$event->set_event_category( $event_category );
		$this->conversion_category = $event_category;

		/**
		 * Filter: gform_googleanalytics_event_action
		 *
		 * Filter the event action dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param string $action Event Action
		 * @param object $form   Gravity Form form object
		 * @param object $entry  Gravity Form Entry Object
		 */
		$event_action = apply_filters( 'gform_googleanalytics_event_action', $feed['meta']['gaeventaction'], $form, $entry );
		$event->set_event_action( $event_action );
		$this->conversion_action = $event_action;

		/**
		 * Filter: gform_googleanalytics_event_label
		 *
		 * Filter the event label dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param string $label Event Label
		 * @param object $form  Gravity Form form object
		 * @param object $entry Gravity Form Entry Object
		 */
		$event_label = apply_filters( 'gform_googleanalytics_event_label', $feed['meta']['gaeventlabel'], $form, $entry );
		$event->set_event_label( $event_label );
		$this->conversion_label = $event_label;

		/**
		 * Filter: gform_googleanalytics_event_value
		 *
		 * Filter the event value dynamically
		 *
		 * @since 1.0.0
		 * @param int    $value Event Label
		 * @param object $form  Gravity Form form object
		 * @param object $entry Gravity Form Entry Object
		 */
		$event_value = apply_filters( 'gform_googleanalytics_event_value', $feed['meta']['gaeventvalue'], $form, $entry );
		if ( $event_value ) {
			// Event value must be a valid integer!
			$event_value = absint( round( GFCommon::to_number( $event_value ) ) );
			$event->set_event_value( $event_value );
			$this->conversion_value = $event_value;
		} else {
			$event_value = 0;
			$event->set_event_value( 0 );
			$this->conversion_value = 0;
		}

		$is_ajax_only = isset( $_REQUEST['gform_ajax'] ) ? true : false;
		$this->log_debug( __METHOD__ . '(): Attempting to send event with the following attributes: Action: "' . $event_action . '", Category: "' . $event_category . '", Label: "' . $event_label . '", Value: "' . $event_value . '"' );
		?>
<script type="text/javascript">
	if (typeof(Storage) !== "undefined" && false == <?php echo wp_json_encode( $is_ajax_only ); ?> ) {
		var gfEntryStorage = localStorage.getItem('googleAnalyticsFeeds');
		var eventObject = {};
		if ( null == gfEntryStorage ) {
			eventObject['<?php echo esc_js( $feed['id'] ); ?>'] = {
				entryId: '<?php echo esc_js( $entry['id'] ); ?>',
				entryCategory: '<?php echo esc_js( $event_category ); ?>',
				entryAction: '<?php echo esc_js( $event_action ); ?>',
				entryLabel: '<?php echo esc_js( $event_label ); ?>',
				entryValue: '<?php echo esc_js( $event_value ); ?>',
				utm_source: '<?php echo rgget( 'utm_source' ) ? esc_js( rgget( 'utm_source' ) ) : ''; ?>',
				utm_medium: '<?php echo rgget( 'utm_medium' ) ? esc_js( rgget( 'utm_medium' ) ) : ''; ?>',
				utm_campaign: '<?php echo rgget( 'utm_campaign' ) ? esc_js( rgget( 'utm_campaign' ) ) : ''; ?>',
				};
		} else {
			eventObject = JSON.parse(gfEntryStorage);
			eventObject['<?php echo esc_js( $feed['id'] ); ?>'] = {
				entryId: '<?php echo esc_js( $entry['id'] ); ?>',
				entryCategory: '<?php echo esc_js( $event_category ); ?>',
				entryAction: '<?php echo esc_js( $event_action ); ?>',
				entryLabel: '<?php echo esc_js( $event_label ); ?>',
				entryValue: '<?php echo esc_js( $event_value ); ?>',
				utm_source: '<?php echo rgget( 'utm_source' ) ? esc_js( rgget( 'utm_source' ) ) : ''; ?>',
				utm_medium: '<?php echo rgget( 'utm_medium' ) ? esc_js( rgget( 'utm_medium' ) ) : ''; ?>',
				utm_campaign: '<?php echo rgget( 'utm_campaign' ) ? esc_js( rgget( 'utm_campaign' ) ) : ''; ?>',
			};
		}
		localStorage.setItem( 'googleAnalyticsFeeds', JSON.stringify( eventObject ) );
	}
</script>
		<?php
		// Get mode and return if measurement protocol is not selected.
		$mode = $this->get_plugin_setting( 'gfgamode' );
		if ( 'gmp' === $mode ) {

			// Begin sending events using the measurement protocol.
			foreach ( $google_analytics_codes as $ua_code ) {
				$response = $event->send( $ua_code );
				if ( is_wp_error( $response ) ) {
					$this->log_debug( __METHOD__ . '(): Sending feed ID: ' . rgar( $feed, 'id' ) . ' with GMP failed: ' . $response->get_error_message() );
				}
			}
		} elseif ( 'ga' === $mode && $is_ajax_only ) {
			?>
			<script>
				window.parent.jQuery.gf_send_to_ga('<?php echo esc_js( $entry['id'] ); ?>', <?php echo esc_js( $event_value ); ?>,'<?php echo esc_js( $event_category ); ?>','<?php echo esc_js( $event_action ); ?>','<?php echo esc_js( $event_label ); ?>');
			</script>
			<?php
		} elseif ( 'gtm' === $mode && $is_ajax_only ) {
			?>
			<script>
				var utmVariables = localStorage.getItem('googleAnalyticsUTM');
				var utmSource = '',
					utmMedium = '',
					utmCampaign = '',
					utmTerm = '',
					utmContent = '';
				if ( null != utmVariables ) {
					utmVariables = JSON.parse( utmVariables );
					utmSource = utmVariables.source;
					utmMedium = utmVariables.medium;
					utmCampaign = utmVariables.campaign;
					utmTerm = utmVariables.term;
					utmContent = utmVariables.content;
				}
				window.parent.jQuery.gf_send_to_gtm(
					'<?php echo esc_js( $entry['id'] ); ?>',
					<?php echo esc_js( $event_value ); ?>,
					'<?php echo esc_js( $event_category ); ?>',
					'<?php echo esc_js( $event_action ); ?>',
					'<?php echo esc_js( $event_label ); ?>',
					utmSource,
					utmMedium,
					utmCampaign,
					utmTerm,
					utmContent
				);
			</script>
			<?php
		}
	}

	/**
	 * Retrieves the value for the feed item.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $feed   The Feed item.
	 * @param mixed $column The Feed column name.
	 *
	 * @return string The column value.
	 */
	public function get_column_value( $feed, $column ) {

		$value = '';

		if ( empty( $value ) ) {
			if ( isset( $feed[ $column ] ) ) {
				$value = $feed[ $column ];
			} elseif ( isset( $feed['meta'][ $column ] ) ) {
				$value = $feed['meta'][ $column ];
			}
		}
		return $value;
	}


	/**
	 * Get the default feed values
	 *
	 * @since 1.0.0
	 *
	 * @param string $property The property to retrieve the default value for.
	 *
	 * @return string Default values
	 */
	public function get_default_feed_value( $property ) {
		$value = '';
		switch ( $property ) {
			case 'gaeventcategory':
				$value = __( 'form', 'gravityformsgoogleanalytics' );
				break;
			case 'gaeventaction':
				$value = __( 'submission', 'gravityformsgoogleanalytics' );
				break;
			case 'gaeventlabel':
				$value = __( '{form_title} ID: {form_id}', 'gravityformsgoogleanalytics' );
				break;
			case 'gaeventvalue':
				$value = 0;
				break;
		}

		return $value;
	}

	/**
	 * Get the menu icon for this plugin.
	 *
	 * @since 1.0
	 *
	 * @return string the class for the plugin menu icon.
	 */
	public function get_menu_icon() {
		return $this->is_gravityforms_supported( '2.5-beta-4' ) ? 'gform-icon--analytics' : 'dashicons-admin-generic';
	}

	/**
	 * Determine if logging is enabled for GA.
	 *
	 * @since  1.3
	 *
	 * @return bool
	 */
	public function is_logging_enabled() {
		// Query string override to enable console logging if needed.
		if ( rgget( 'gfga_logging' ) == 1 ) {
			return true;
		}

		$ga_logging_settings = GFLogging::get_instance()->get_plugin_setting( 'gravityformsgoogleanalytics' );
		if ( rgempty( 'enable', $ga_logging_settings ) ) {
			return false;
		}

		return true;
	}
}
