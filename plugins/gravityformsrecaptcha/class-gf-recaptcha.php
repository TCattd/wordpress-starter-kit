<?php

namespace Gravity_Forms\Gravity_Forms_RECAPTCHA;

defined( 'ABSPATH' ) || die();

use GFForms;
use GFAddOn;
use GF_Fields;
use GFAPI;
use GFFormDisplay;
use GFFormsModel;
use Gravity_Forms\Gravity_Forms_RECAPTCHA\Settings;

// Include the Gravity Forms Add-On Framework.
GFForms::include_addon_framework();

/**
 * Gravity Forms Gravity Forms Recaptcha Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Gravity Forms
 * @copyright Copyright (c) 2021, Gravity Forms
 */
class GF_RECAPTCHA extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @var    GF_RECAPTCHA $_instance If available, contains an instance of this class
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Gravity Forms Recaptcha Add-On.
	 *
	 * @since  1.0
	 * @var    string $_version Contains the version.
	 */
	protected $_version = GF_RECAPTCHA_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = GF_RECAPTCHA_MIN_GF_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsrecaptcha';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsrecaptcha/recaptcha.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this add-on can be found.
	 *
	 * @since  1.0
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://gravityforms.com';

	/**
	 * Defines the title of this add-on.
	 *
	 * @since  1.0
	 * @var    string $_title The title of the add-on.
	 */
	protected $_title = 'Gravity Forms reCAPTCHA Add-On';

	/**
	 * Defines the short title of the add-on.
	 *
	 * @since  1.0
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'reCAPTCHA';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capabilities needed for the Gravity Forms Recaptcha Add-On
	 *
	 * @since  1.0
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_recaptcha', 'gravityforms_recaptcha_uninstall' );

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_recaptcha';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_recaptcha';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_recaptcha_uninstall';

	/**
	 * Class instance.
	 *
	 * @var RECAPTCHA_API
	 */
	private $api;

	/**
	 * Object responsible for verifying tokens.
	 *
	 * @var Token_Verifier
	 */
	private $token_verifier;

	/**
	 * Prefix for add-on assets.
	 *
	 * @since 1.0
	 * @var string
	 */
	private $asset_prefix = 'gforms_recaptcha_';

	/**
	 * Wrapper class for plugin settings.
	 *
	 * @since 1.0
	 * @var Settings\Plugin_Settings
	 */
	private $plugin_settings;

	/**
	 * GF_Field_RECAPTCHA instance.
	 *
	 * @since 1.0
	 * @var GF_Field_RECAPTCHA
	 */
	private $field;

	/**
	 * Possible disabled states for v3.
	 *
	 * disabled: reCAPTCHA is disabled in feed settings.
	 * disconnected: No valid v3 site and secret keys are saved.
	 *
	 * @var array
	 */
	private $v3_disabled_states = array(
		'disabled',
		'disconnected',
	);

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @since  1.0
	 *
	 * @return GF_RECAPTCHA $_instance An instance of the GF_RECAPTCHA class
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof GF_RECAPTCHA ) {
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
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-gf-field-recaptcha.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-recaptcha-api.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-token-verifier.php';

		$this->api             = new RECAPTCHA_API();
		$this->token_verifier  = new Token_Verifier( $this, $this->api );
		$this->plugin_settings = new Settings\Plugin_Settings( $this, $this->token_verifier );
		$this->field           = new GF_Field_RECAPTCHA();

		GF_Fields::register( $this->field );

		add_filter( 'gform_settings_menu', array( $this, 'replace_core_recaptcha_menu_item' ) );

		parent::pre_init();
	}

	/**
	 * Replaces the core recaptcha settings menu item with the addon settings menu item.
	 *
	 * @param array $settings_tabs Registered settings tabs.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function replace_core_recaptcha_menu_item( $settings_tabs ) {
		// Get tab names with the same index as is in the settings tabs.
		$tabs = array_combine( array_keys( $settings_tabs ), array_column( $settings_tabs, 'name' ) );

		// Bail if for some reason this add-on is not registered as a settings tab.
		if ( ! in_array( $this->_slug, $tabs ) ) {
			return $settings_tabs;
		}

		$prepared_tabs = array_flip( $tabs );

		$settings_tabs[ rgar( $prepared_tabs, 'recaptcha' ) ]['name'] = $this->_slug;
		unset( $settings_tabs[ rgar( $prepared_tabs, $this->_slug ) ] );

		return $settings_tabs;
	}

	/**
	 * Register initialization hooks.
	 *
	 * @since  1.0
	 */
	public function init() {
		parent::init();

		if ( ! $this->is_gravityforms_supported( $this->_min_gravityforms_version ) ) {
			return;
		}

		// Enqueue shared scripts that need to run everywhere, instead of just on forms pages.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_recaptcha_script' ) );

		// Add Recaptcha field to the form output.
		add_filter( 'gform_form_tag', array( $this, 'add_recaptcha_input' ), 50, 2  );

		// Register a custom metabox for the entry details page.
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'register_meta_box' ), 10, 3 );

		add_filter( 'gform_entry_is_spam', array( $this, 'check_for_spam_entry' ), 10, 3 );
		add_filter( 'gform_validation', array( $this, 'validate_submission' ) );
	}

	/**
	 * Register admin initialization hooks.
	 *
	 * @since 1.0
	 */
	public function init_admin() {
		parent::init_admin();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_recaptcha_script' ) );
	}

	/**
	 * Validate the secret key on the plugin settings screen.
	 *
	 * @since 1.0
	 */
	public function init_ajax() {
		parent::init_ajax();

		add_action( 'wp_ajax_verify_secret_key', array( $this->plugin_settings, 'verify_v3_keys' ) );
	}

	/**
	 * Register scripts.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'    => "{$this->asset_prefix}frontend",
				'src'       => $this->get_script_url( 'frontend' ),
				'version'   => $this->_version,
				'deps'      => array( 'jquery', "{$this->asset_prefix}recaptcha" ),
				'in_footer' => true,
				'enqueue'   => array(
					array( $this, 'frontend_script_callback' ),
				),
			),
		);

		// Prevent plugin settings from loading on the frontend. Remove this condition to see it in action.
		if ( is_admin() ) {
			$scripts[] = array(
				'handle'  => "{$this->asset_prefix}plugin_settings",
				'src'     => $this->get_script_url( 'plugin_settings' ),
				'version' => $this->_version,
				'deps'    => array( 'jquery', "{$this->asset_prefix}recaptcha" ),
				'enqueue' => array(
					'admin_page' => array( 'plugin_settings' ),
					'tab'        => $this->_slug,
				),
			);
		}

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Get the URL for a JavaScript file.
	 *
	 * @since 1.0
	 *
	 * @param string $filename The name of the script to return.
	 *
	 * @return string
	 */
	private function get_script_url( $filename ) {
		$base_path = $this->get_base_path() . '/js';
		$base_url  = $this->get_base_url() . '/js';

		// Production scripts.
		if ( is_readable( "{$base_path}/{$filename}.min.js" ) && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
			return "{$base_url}/{$filename}.min.js";
		}

		// Uncompiled scripts.
		if ( is_readable( "{$base_path}/src/{$filename}.js" ) ) {
			return "{$base_url}/src/{$filename}.js";
		}

		// Compiled dev scripts.
		return "{$base_url}/{$filename}.js";
	}

	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Define plugin settings fields.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return $this->plugin_settings->get_fields();
	}

	/**
	 * Initialize the plugin settings.
	 *
	 * This method overrides the add-on framework because we need to retrieve the values for reCAPTCHA v2 from core
	 * and populate them if they exist. Since the Plugin_Settings class houses all of the logic related to the plugin
	 * settings screen, we need to pass the return value of this method's parent to delegate that responsibility.
	 *
	 * In a future release, once reCAPTCHA logic is migrated into this add-on, we
	 * should be able to safely remove this override.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_plugin_settings() {
		return $this->plugin_settings->get_settings( parent::get_plugin_settings() );
	}

	/**
	 * Callback to update plugin settings on save.
	 *
	 * We override this method in order to save values for reCAPTCHA v2 with their original keys in the options table.
	 * In a future release, we'll eventually migrate all previous reCAPTCHA logic into this add-on, at which time we
	 * should be able to remove this method altogether.
	 *
	 * @since 1.0
	 *
	 * @param array $settings The settings to update.
	 */
	public function update_plugin_settings( $settings ) {
		$this->plugin_settings->update_settings( $settings );
		parent::update_plugin_settings( $settings );
	}

	/**
	 * The settings page icon.
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_menu_icon() {
		return 'gform-icon--recaptcha';
	}

	/**
	 * Add the recaptcha field to the end of the form.
	 *
	 * @since 1.0
	 *
	 * @depecated 1.1
	 *
	 * @param array $form The form array.
	 *
	 * @return array
	 */
	public function add_recaptcha_field( $form ) {
		return $form;
	}

	/**
	 * Add the recaptcha input to the form.
	 *
	 * @since 1.1
	 *
	 * @param string $form_tag The form tag.
	 * @param array  $form     The form array.
	 *
	 * @return string
	 */
	public function add_recaptcha_input( $form_tag, $form ) {
		if ( empty( $form_tag ) || $this->is_disabled_by_form_setting( $form ) || ! $this->initialize_api() ) {
			return $form_tag;
		}

		return $form_tag . $this->field->get_field_input( $form );
	}

	// # FORM SETTINGS

	/**
	 * Register a form settings tab for reCAPTCHA v3.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form data.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => 'reCAPTCHA Settings',
				'fields' => array(
					array(
						'type'    => 'checkbox',
						'name'    => 'disable-recaptchav3',
						'choices' => array(
							array(
								'name'          => 'disable-recaptchav3',
								'label'         => __( 'Disable reCAPTCHA v3 for this form.', 'gravityformsrecaptcha' ),
								'default_value' => 0,
							),
						),
					),
				),
			),
		);
	}

	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get the instance of the Token_Verifier class.
	 *
	 * @since 1.0
	 *
	 * @return Token_Verifier
	 */
	public function get_token_verifier() {
		return $this->token_verifier;
	}

	/**
	 * Get the instance of the Plugin_Settings class.
	 *
	 * @return Settings\Plugin_Settings
	 */
	public function get_plugin_settings_instance() {
		return $this->plugin_settings;
	}

	/**
	 * Initialize the connection to the reCAPTCHA API.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function initialize_api() {
		$site_key   = $this->plugin_settings->get_recaptcha_key( 'site_key_v3' );
		$secret_key = $this->plugin_settings->get_recaptcha_key( 'secret_key_v3' );

		if ( ! ( $site_key && $secret_key ) ) {
			$this->log_debug( __METHOD__ . '(): missing v3 key configuration. Please check the add-on settings.' );
			return false;
		}

		if ( '1' !== $this->get_plugin_setting( 'recaptcha_keys_status_v3' ) ) {
			$this->log_debug( __METHOD__ . '(): could not initialize reCAPTCHA v3 because site and/or secret key is invalid.' );
			return false;
		}

		$this->log_debug( __METHOD__ . '(): Initializing API.' );
		return true;
	}

	/**
	 * Check to determine whether the reCAPTCHA script is needed on a page.
	 *
	 * The script is needed on every page of the front-end if we're able to initialize the API because we've already
	 * verified that the v3 site and secret keys are valid.
	 *
	 * On the back-end, we only want to load this on the settings page, and it should be available regardless of the
	 * status of the keys.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function requires_recaptcha_script() {
		return is_admin() ? $this->is_plugin_settings( $this->_slug ) : $this->initialize_api();
	}

	/**
	 * Custom enqueuing of the external reCAPTCHA script.
	 *
	 * This script is enqueued via the normal WordPress process because, on the front-end, it's needed on every
	 * single page of the site in order for reCAPTCHA to properly score the interactions leading up to the form
	 * submission.
	 *
	 * @since 1.0
	 * @see GF_RECAPTCHA::init()
	 */
	public function enqueue_recaptcha_script() {
		if ( ! $this->requires_recaptcha_script() ) {
			return;
		}

		$script_url = add_query_arg(
			'render',
			$this->plugin_settings->get_recaptcha_key( 'site_key_v3' ),
			'https://www.google.com/recaptcha/api.js'
		);

		wp_enqueue_script(
			"{$this->asset_prefix}recaptcha",
			$script_url,
			array( 'jquery' ),
			$this->_version,
			true
		);

		wp_localize_script(
			"{$this->asset_prefix}recaptcha",
			"{$this->asset_prefix}recaptcha_strings",
			array(
				'site_key' => $this->plugin_settings->get_recaptcha_key( 'site_key_v3' ),
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( "{$this->_slug}_verify_token_nonce" ),
			)
		);

		if ( $this->get_plugin_setting( 'disable_badge_v3' ) !== '1' ) {
			return;
		}

		// Add inline JS to disable the badge.
		wp_add_inline_script(
			"{$this->asset_prefix}recaptcha",
			'(function($){grecaptcha.ready(function(){$(\'.grecaptcha-badge\').css(\'visibility\',\'hidden\');});})(jQuery);'
		);
	}

	/**
	 * Callback to determine whether to render the frontend script.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form array.
	 *
	 * @return bool
	 */
	public function frontend_script_callback( $form ) {
		return $form && ! is_admin();
	}

	/**
	 * Sets up additional data points for sorting on the entry.
	 *
	 * @since 1.0
	 *
	 * @param array $entry_meta The entry metadata.
	 * @param int   $form_id The ID of the form.
	 *
	 * @return array
	 */
	public function get_entry_meta( $entry_meta, $form_id ) {
		$entry_meta[ "{$this->_slug}_score" ] = array(
			'label'                      => __( 'reCAPTCHA Score', 'gravityformsrecaptcha' ),
			'is_numeric'                 => true,
			'update_entry_meta_callback' => array( $this, 'update_entry_meta' ),
			'is_default_column'          => true,
			'filter'                     => array(
				'operators' => array( 'is', '>', '<' ),
			),
		);

		return $entry_meta;
	}

	/**
	 * Save the Recaptcha metadata values to the entry.
	 *
	 * @since 1.0
	 *
	 * @see   GF_RECAPTCHA::get_entry_meta()
	 *
	 * @param string $key   The entry meta key.
	 * @param array  $entry The entry data.
	 * @param array  $form  The form data.
	 *
	 * @return float|void
	 */
	public function update_entry_meta( $key, $entry, $form ) {
		if ( $key !== "{$this->_slug}_score" ) {
			return;
		}

		if ( $this->is_disabled_by_form_setting( $form ) ) {
			$this->log_debug( __METHOD__ . '(): reCAPTCHA v3 disabled on form ' . rgar( $form, 'id' ) );
			return 'disabled';
		}

		if ( ! $this->initialize_api() ) {
			return 'disconnected';
		}

		return $this->token_verifier->get_score();
	}

	/**
	 * Registers a metabox on the entry details screen.
	 *
	 * @since 1.0
	 *
	 * @param array $metaboxes Gravity Forms registered metaboxes.
	 * @param array $entry     The entry array.
	 * @param array $form      The form array.
	 *
	 * @return array
	 */
	public function register_meta_box( $metaboxes, $entry, $form ) {
		$score = $this->get_score_from_entry( $entry );

		if ( ! $score ) {
			return $metaboxes;
		}

		$metaboxes[ $this->_slug ] = array(
			'title'    => esc_html__( 'reCAPTCHA', 'gravityformsrecaptcha' ),
			'callback' => array( $this, 'add_recaptcha_meta_box' ),
			'context'  => 'side',
		);

		return $metaboxes;
	}

	/**
	 * Callback to output the entry details metabox.
	 *
	 * @since 1.0
	 * @see   GF_RECAPTCHA::register_meta_box()
	 *
	 * @param array $data An array containing the form and entry data.
	 */
	public function add_recaptcha_meta_box( $data ) {
		$score = $this->get_score_from_entry( rgar( $data, 'entry' ) );

		printf(
			'<div><p>%s: %s</p><p><a href="%s">%s</a></p></div>',
			esc_html__( 'Score', 'gravityformsrecaptcha' ),
			esc_html( $score ),
			esc_html( 'https://docs.gravityforms.com/captcha/' ),
			esc_html__( 'Click here to learn more about reCAPTCHA.', 'gravityformsrecaptcha' )
		);
	}

	/**
	 * Callback to gform_entry_is_spam that determines whether to categorize this entry as such.
	 *
	 * @since 1.0
	 *
	 * @see   GF_RECAPTCHA::init();
	 *
	 * @param bool  $is_spam Whether the entry is spam.
	 * @param array $form    The form data.
	 * @param array $entry   The entry data.
	 *
	 * @return bool
	 */
	public function check_for_spam_entry( $is_spam, $form, $entry ) {
		if ( $is_spam || $this->is_disabled_by_form_setting( $form ) || ! $this->initialize_api() || $this->is_preview() ) {
			return $is_spam;
		}

		$is_spam = (float) $this->get_score_from_entry( $entry ) <= $this->get_spam_score_threshold();
		$this->log_debug( __METHOD__ . '(): Is submission considered spam? ' . ( $is_spam ? 'Yes.' : 'No.' ) );

		return $is_spam;
	}

	/**
	 * Get the Recaptcha score from the entry details.
	 *
	 * @since 1.0
	 *
	 * @param array $entry The entry array.
	 *
	 * @return float|string
	 */
	private function get_score_from_entry( $entry ) {
		$score = rgar( $entry, "{$this->_slug}_score" );

		if ( in_array( $score, $this->v3_disabled_states, true ) ) {
			return $score;
		}

		return $score ? (float) $score : $this->token_verifier->get_score();
	}

	/**
	 * The score that determines whether the entry is spam.
	 *
	 * Hard-coded for now, but this will eventually be an option within the add-on.
	 *
	 * @since 1.0
	 *
	 * @return float
	 */
	private function get_spam_score_threshold() {
		$value = (float) $this->get_plugin_setting( 'score_threshold_v3' );
		if ( empty( $value ) ) {
			$value = 0.5;
		}
		$this->log_debug( __METHOD__ . '(): ' . $value );

		return $value;
	}

	/**
	 * Determine whether a given form has disabled reCAPTCHA within its settings.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form data.
	 *
	 * @return bool
	 */
	private function is_disabled_by_form_setting( $form ) {
		return empty( $form['id'] ) || '1' === rgar( $this->get_form_settings( $form ), 'disable-recaptchav3' );
	}

	/**
	 * Validate the form submission.
	 *
	 * @since 1.0
	 *
	 * @param array $submission_data The submitted form data.
	 *
	 * @return array
	 */
	public function validate_submission( $submission_data ) {
		$this->log_debug( __METHOD__ . '(): Validating form (#' . rgars( $submission_data, 'form/id' ) . ') submission.' );

		if (
			! $this->initialize_api()
			|| $this->is_disabled_by_form_setting( rgar( $submission_data, 'form' ) )
			|| $this->is_preview()
		) {
			$this->log_debug( __METHOD__ . '(): Validation skipped. reCAPTCHA v3 is misconfigured, disabled, or the form was submitted in preview mode.' );

			return $submission_data;
		}

		$this->log_debug( __METHOD__ . '(): Validating reCAPTCHA v3.' );

		return $this->field->validation_check( $submission_data );
	}

}
