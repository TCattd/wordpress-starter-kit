<?php

namespace Gravity_Forms\Gravity_Forms_Moderation;

defined( 'ABSPATH' ) || die();

use GFForms;
use GFAddOn;
use GFCommon;
use GFAPI;
use GFFormsModel;
use GFCache;
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-gf-perspective-api.php' );

// Include the Gravity Forms Add-On Framework.
GFForms::include_addon_framework();


/**
 * Gravity Forms Moderation Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Gravity Forms
 * @copyright Copyright (c) 2021, Gravity Forms
 */
class GF_Moderation extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @var    GF_Moderation $_instance If available, contains an instance of this class
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Gravity Forms Moderation Add-On.
	 *
	 * @since  1.0
	 * @var    string $_version Contains the version.
	 */
	protected $_version = GF_MODERATION_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = GF_MODERATION_MIN_GF_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsmoderation';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsmoderation/moderation.php';

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
	protected $_title = 'Gravity Forms Moderation Add-On';

	/**
	 * Defines the short title of the add-on.
	 *
	 * @since  1.0
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Moderation';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capabilities needed for the Gravity Forms Moderation Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_moderation', 'gravityforms_moderation_uninstall' );

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_moderation';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_moderation';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_moderation_uninstall';

	/**
	 * The Perspective API class.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    GF_Perspective_API
	 */
	protected $api;

	/**
	 * Performs early initialization tasks.
	 *
	 * @since  1.0
	 */
	public function pre_init() {
		parent::pre_init();
		$this->api = new GF_Perspective_API( $this );
	}


	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @since  1.0
	 *
	 * @return GF_Moderation $_instance An instance of the GF_Moderation class
	 */
	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new GF_Moderation();
		}

		return self::$_instance;

	}

	/**
	 * Register initialization hooks.
	 *
	 * @since  1.0
	 */
	public function init() {

		parent::init();

		add_action( 'gform_after_submission', array( $this, 'maybe_handle_toxic_entry' ), 15, 2 );

	}

	/**
	 * Register initialization hooks on admin screens.
	 *
	 * @since  1.0
	 */
	public function init_admin() {

		parent::init_admin();

		add_filter( 'gform_filter_links_entry_list', array( $this, 'add_toxicity_to_entry_list_filter' ), 10, 4 );
		add_filter( 'gform_get_entries_args_entry_list', array( $this, 'filter_toxic_entries_list' ) );
		add_filter( 'gform_entry_field_value', array( $this, 'maybe_blur_field_entry_detail' ), 10, 4 );
		add_filter( 'gform_entries_field_value', array( $this, 'maybe_blur_field_entry_list' ), 10, 4 );
		add_filter( 'gform_entries_primary_column_filter', array( $this, 'maybe_blur_field_entry_list_primary_column' ), 10, 7 );
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'register_toxicity_details_meta_box' ), 10, 3 );
		add_filter( 'gform_display_field_select_columns_entry_list', array( $this, 'entry_list_score_details' ), 10, 3 );

	}

	/**
	 * Register initialization hooks on ajax requests.
	 *
	 * @since  1.0
	 */
	public function init_ajax() {

		parent::init_ajax();

		add_filter( 'gform_search_criteria_export_entries', array( $this, 'add_toxic_entries_to_entry_export' ) );
	}

	//--------------  Script enqueuing  ---------------
	/**
	 * Enqueue styles for the entries and entry detail pages
	 *
	 * @since 1.0
	 */
	public function styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'    => 'gforms_moderation',
				'src'       => $this->get_base_url() . "/css/gf-moderation-admin{$min}.css",
				'version'   => $this->_version,
				'in_footer' => true,
				'enqueue'   => array(
					array( 'query' => 'page=gf_entries' ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}


	/**
	 * Enqueue scripts for the entries and entry detail pages
	 *
	 * @since 1.0
	 */
	public function scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'    => 'gforms_moderation',
				'src'       => $this->get_base_url() . "/js/gf-moderation-admin{$min}.js",
				'deps'      => array( 'jquery' ),
				'version'   => $this->_version,
				'in_footer' => true,
				'enqueue'   => array(
					array( 'query' => 'page=gf_entries' ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Determines if the specified field has been marked as toxic. I.e. Field toxicity score > threshold.
	 *
	 * @since 1.0
	 *
	 * @param float $field_id The current field id.
	 * @param array $entry The current entry object.
	 * @param array $form  the current form object.
	 *
	 * @return bool Returns true if field value has been marked as toxic. Returns false otherwise.
	 */
	public function is_field_marked_toxic( $field_id, $entry, $form ) {

		$score = $this->get_toxicity_score( $entry['id'] );

		$threshold   = $this->get_form_threshold( $form );
		$field_score = floatval( rgars( $score, "fields/{$field_id}" ) );
		return $field_score > $threshold;
	}

	/**
	 * Gets the toxicity scores calculated for the specified entry, containing each field's score.
	 *
	 * @since 1.0
	 *
	 * @param int $entry_id The entry id to get scores from.
	 *
	 * @return array Returns the toxicity score detail for the specified entry id.
	 */
	public function get_toxicity_score( $entry_id ) {
		$cache_key = "toxicity_score_{$entry_id}";
		$score     = GFCache::get( $cache_key );
		if ( empty( $score ) ) {
			$score = gform_get_meta( $entry_id, 'gravityformsmoderation_score_details' );
			GFCache::set( $cache_key, $score );
		}

		$score = json_decode( $score, true );

		return empty( $score ) ? array() : $score;
	}

	/**
	 * Target of the gform_entry_field_value filter. If field is toxic, wraps value in markup that will cause it to be blurred.
	 *
	 * @since 1.0
	 *
	 * @param string $field_value Field value to be displayed, or blurred when displayed on the entry detail page.
	 * @param object $field The current field object.
	 * @param array  $entry The current entry object.
	 * @param array  $form The current form object.
	 *
	 * @return string If field is toxic, returns the field value wrapped in markup with "blurred UI". Otherwise, returns the plain field value.
	 */
	public function maybe_blur_field_entry_detail( $field_value, $field, $entry, $form ) {

		if ( $this->is_field_marked_toxic( $field->id, $entry, $form ) ) {
			$field_value = $this->blur_field( $field_value );
		}

		return $field_value;
	}

	/**
	 * Target of the gform_entries_field_value filter. If field is toxic, wraps value in markup that will cause it to be blurred on the entry list page.
	 *
	 * @since 1.0
	 *
	 * @param string $field_value Field value to be displayed, or blurred.
	 * @param int    $form_id The current form id.
	 * @param float  $field_id The current field id.
	 * @param array  $entry The current entry object.
	 *
	 * @return string If field is toxic, returns the field value wrapped in markup that will cause it to be blurred. Otherwise, returns the plain field value.
	 */
	public function maybe_blur_field_entry_list( $field_value, $form_id, $field_id, $entry ) {

		$form = GFAPI::get_form( $form_id );
		if ( $this->is_field_marked_toxic( intval( $field_id ), $entry, $form ) ) {
			$field_value = $this->blur_field( $field_value );
		}

		return $field_value;
	}

	/**
	 * Filter the link to the entry in the primary entry column.
	 *
	 * The primary entry column includes a link to the entry that the other columns do not.
	 *
	 * @param string     $column_value The column value to be filtered. Contains the field value wrapped in a link/a tag.
	 * @param int        $form_id      The ID of the current form.
	 * @param int|string $field_id     The ID of the field or the name of an entry column (i.e. date_created).
	 * @param array      $entry        The Entry object.
	 * @param string     $query_string The current page's query string.
	 * @param string     $edit_url     The url to the entry edit page.
	 * @param string     $value        The value of the field.
	 *
	 * @return mixed|string If field is toxic, returns the field value wrapped in markup that will cause it to be blurred. Otherwise, returns the plain field value.
	 */
	public function maybe_blur_field_entry_list_primary_column( $column_value, $form_id, $field_id, $entry, $query_string, $edit_url, $value ) {

		$form = GFAPI::get_form( $form_id );
		if ( $this->is_field_marked_toxic( intval( $field_id ), $entry, $form ) ) {
			$field            = GFFormsModel::get_field( $form, $field_id );
			$unfiltered_value = $field->get_value_entry_detail( $entry[$field_id] );
			$link             = '<a aria-label="' . esc_attr( $unfiltered_value ) . esc_attr__( ' (View)', 'gravityforms' ) . '" href="' . $edit_url . '">';
			$column_value     = $this->blur_field( $unfiltered_value, $link );
		}

		return $column_value;

	}

	/**
	 * Wraps field value in markup that will cause it to be blurred.
	 *
	 * @since 1.0
	 *
	 * @param string $field_value The field value to be blurred.
	 *
	 * @return string Returns the field value wrapped in markup that will cause it to be blurred.
	 */
	public function blur_field( $field_value, $link = '' ) {

		$close_link = $link == '' ? '' : '</a>';

		return '<div class="gform-moderation-text-wrapper">
			<button class="gform-moderation-hidden-toggle">
				<span class="gform-icon gform-icon--hidden" style="display: none;"></span>
				<span class="gform-visually-hidden gform-hidden-toggle__message gform-hidden-toggle__message--hidden">
					' . esc_html__( 'Reveal toxic text', 'gravityformsmoderation' ) . '
				</span>
				
				<span class="gform-common-icon gform-common-icon--eye"></span>
				<span class="gform-visually-hidden gform-hidden-toggle__message gform-hidden-toggle__message--visible" aria-hidden="true">
					' . esc_html__( 'Hide toxic text', 'gravityformsmoderation' ) . '
				</span>
			</button>
			<span class="gform-moderation-text gform-moderation-text__hidden" aria-hidden="true">' . $link . $field_value . $close_link . '</span>
		</div>';

	}

	/**
	 * Determines if the site's language is in the list of supported languages by the moderation API.
	 *
	 * @since 1.0
	 *
	 * @return bool True if the site language is supported by the moderation API. Returns false otherwise.
	 */
	public function is_current_language_supported() {
		return $this->api->is_language_supported( $this->get_locale() );
	}

	/**
	 * Displays the unsupported language alert.
	 *
	 * @since 1.0
	 */
	public function display_unsupported_language_alert() {
		printf(
			'<div class="gform-alert gform-alert--error" data-js="gform-alert">
						  <span
						    class="gform-alert__icon gform-icon gform-icon--circle-error-fine"
						    aria-hidden="true"
						  ></span>
						  <div class="gform-alert__message-wrap">
						    <p class="gform-alert__message">%s</p>
						  </div>
						</div>',
			esc_html__( "This site's language is not supported by the Moderation Add-On. Moderation is currently disabled.", 'gravityformsmoderation' )
		);
	}

	/**
	 * Displays the missing Perspective API Key alert.
	 *
	 * @since 1.0
	 */
	public function display_missing_perspective_key_alert() {

		// translators: variable is the name of the Add-On.
		$settings_label = sprintf( esc_html__( '%s Settings', 'gravityformsmoderation' ), $this->get_short_title() );
		$settings_link  = sprintf( '<a href="%s">%s</a>', esc_url( $this->get_plugin_settings_url() ), $settings_label );

		printf(
			'<div class="gform-alert gform-alert--notice" data-js="gform-alert">
						  <span
						    class="gform-alert__icon gform-icon gform-icon--circle-notice"
						    aria-hidden="true"
						  ></span>
						  <div class="gform-alert__message-wrap">
						    <p class="gform-alert__message">%s</p>
						  </div>
						</div>',
			// translators: variable is a link to Add-On settings.
			sprintf( esc_html__( 'To get started, please configure your %s.', 'gravityforms' ), $settings_link )
		);
	}

	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return $this->is_gravityforms_supported( '2.7-beta-1' ) ? 'gform-icon--abuse-filter' : 'dashicons-admin-generic';

	}

	/**
	 * Overrides parent function to display unsupported language message when appropriate.
	 *
	 * @since 1.0
	 */
	public function plugin_settings_page() {

		if ( ! $this->is_current_language_supported() ) {

			$this->display_unsupported_language_alert();
		}

		parent::plugin_settings_page();
	}

	/**
	 * Define plugin settings fields.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		$fields = array(
			array(
				'title'       => esc_html__( 'Perspective API Key', 'gravityformsmoderation' ),
				// Translators: 1. Opening link tag for link to Perspective API website, 2. Closing link tag, 3. Opening link tag for link to Perspective API key instructions, 4. Closing link tag.
				'description' => sprintf(
					esc_html__( 'Moderation uses the %sPerspective API%s to analyze form entries for abusive and toxic content. To use Moderation, you will need a Perspective API key.  You can %sget your API key from the Perspective API website%s.', 'gravityformsmoderation' ),
					'<a href="https://www.perspectiveapi.com/" target="_blank">',
					'</a>',
					'<a href="https://developers.perspectiveapi.com/s/docs-get-started" target="_blank">',
					'</a>'
				),
				'fields'     => array(
					array(
						'name'              => 'perspective_api_key',
						'type'              => 'text',
						'label'             => esc_html__( 'Perspective API Key', 'gravityformsmoderation' ),
						'feedback_callback' => array( $this, 'validate_key' ),
					),
				),
			),
			array(
				'title'       => esc_html__( 'Default Form Settings', 'gravityformsmoderation' ),
				'description' => esc_html__( 'These settings are the default for all forms, but can be changed for each form in the form settings.', 'gravityformsmoderation' ),
				'fields'      => array(
					$this->get_threshold_field(),
				),
			),
		);


		$attributes = $this->get_attributes_fields();

		if ( $attributes ) {
			$fields[1]['fields'][] = $attributes;
		}

		// Adding custom word settings and default toxicity action field.
		$fields[1]['fields'][] = $this->get_custom_words_field();
		$fields[1]['fields'][] = $this->get_toxicity_action_field();

		return $fields;

	}

	/**
	 * Create the threshold field.
	 *
	 * @since 1.0
	 *
	 * @return array[]  Array of settings fields.
	 */
	public function get_threshold_field() {
		return array(
			'name'                => 'threshold',
			'type'                => 'text',
			'label'               => esc_html__( 'Toxicity score threshold', 'gravityformsmoderation' ),
			'default_value'       => 0.5,
			'description'         => esc_html__( 'Entries are given a toxicity score between 0 (not toxic) and 1 (very toxic).  Set your own threshold to control what entries are marked as toxic.  Default value is 0.5.', 'gravityformsmoderation' ),
			'validation_callback' => function( $field, $value ) {
				if ( ! ( ( $value >= 0 ) && ( $value <= 1 ) ) ) {
					$field->set_error( 'The threshold must be a number between 0 and 1', 'gravityformsmoderation' );
				}
			},
		);
	}

	/**
	 * Creates the custom word field.
	 *
	 * @since 1.0
	 *
	 * @param string $default_value Default values to be populated.
	 *
	 * @return array Returns an array with the field's meta data.
	 */
	public function get_custom_words_field( $default_value = '' ) {

		return array(
			'name'          => 'custom_words',
			'type'          => 'textarea',
			'default_value' => $default_value,
			'label'         => esc_html__( 'Custom toxic words (optional)', 'gravityformsmoderation' ),
			'description'   => esc_html__( 'Enter a comma-separated list of words that will automatically mark an entry as toxic when present.', 'gravityformsmoderation' ),
		);
	}

	/**
	 * Create the fields to select what attributes to filter.
	 *
	 * @since 1.0
	 *
	 * @param array $form Current Form Object.
	 *
	 * @return array Array of settings choices.
	 */
	public function get_attributes_fields( $form = '' ) {
		$choices = $this->api->get_attribute_choices( $this->get_locale() );

		// Make sure this language has multiple attributes to choose from.
		if ( ! $choices ) {
			return array();
		}

		// If we are on a form settings page, use the plugin settings as the default value.
		if ( '' == $form ) {
			$default = array( 'toxicity' );
		} else {
			$default = $this->get_plugin_setting( 'filtered_attributes' ) ? : array( 'toxicity' );
		}

		return array(
			'name'          => 'filtered_attributes',
			'type'          => 'checkbox',
			'data_format'   => 'array',
			'label'         => esc_html__( 'Attributes to Filter', 'gravityformsmoderation' ),
			'description'   => esc_html__( 'Select what kind of toxicity to test for in your form entries.', 'gravityformsmoderation' ),
			'choices'       => $choices,
			'default_value' => $default,
		);
	}

	/**
	 * Creates the default toxicity action field.
	 *
	 * @since 1.0
	 *
	 * @param string $default_value Default values to be populated.
	 *
	 * @return array Returns an array with the field's meta data.
	 */
	public function get_toxicity_action_field() {
		$default_value = $this->get_plugin_setting( 'toxicity_action' );
		return array(
			'name'          => 'toxicity_action',
			'type'          => 'select',
			'choices' => array(
				array(
					'label' => esc_html__( 'Send to toxic box', 'gravityformsmoderation' ),
					'value'  => 'send_to_toxic',
				),
				array(
					'label' => esc_html__( 'Delete the entry', 'gravityformsmoderation' ),
					'value'  => 'delete_toxic',
				),
			),
			'default_value' => $default_value,
			'label'         => esc_html__( 'Action for toxic entries', 'gravityformsmoderation' ),
			'description'   => esc_html__( 'Determine what to do with an entry that is marked toxic.', 'gravityformsmoderation' ),
		);
	}

	/**
	 * Validate the API key.
	 *
	 * @since 1.0
	 *
	 * @param string $value Current key.
	 *
	 * @return bool
	 */
	public function validate_key( $value ) {
		if ( '' == $value ) {
			return;
		}

		$this->log_debug( __METHOD__ . sprintf( '(): Validating the API Key: %s', $value ) );

		$test_request = $this->api->check_api_key( $this->get_locale(), $value );
		if ( is_wp_error( $test_request ) ) {
			$this->log_error( __METHOD__ . sprintf( '(): API key validation failed.  code: %s; message: %s', $test_request->get_error_code(), $test_request->get_error_message() ) );
			return false;
		} else {
			return true;
		}
	}


	// # FORM SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Overrides parent function to display unsupported language message when appropriate.
	 *
	 * @since 1.0.
	 *
	 * @param array $form Current Form Object.
	 */
	public function form_settings( $form ) {

		if ( ! $this->is_current_language_supported() ) {
			$this->display_unsupported_language_alert();
		}

		if ( ! $this->get_plugin_setting( 'perspective_api_key' ) ) {
			$this->display_missing_perspective_key_alert();
			return;
		}

		if ( ! $this->get_settings_renderer() ) {
			$this->form_settings_init();
		}

		$renderer = $this->get_settings_renderer();
		if ( $renderer ) {
			$renderer->render();
		} else {
			printf( '<p>%s</p>', esc_html__( 'Unable to render form settings.', 'gravityforms' ) );
		}
	}

	/**
	 * Define form settings fields.
	 *
	 * @since  1.0
	 *
	 * @param array $form The current form object.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {

		$fields = array(
			array(
				'title'       => esc_html__( 'Moderation: Form Settings', 'gravityformsmoderation' ),
				'description' => esc_html__( 'These options apply only to this form.  You can set the default attributes for all forms on the add-on settings page.', 'gravityformsmoderation' ),
				'fields'      => array(
					array(
						'type'    => 'checkbox',
						'label'   => esc_html__( 'Disable moderation on this form', 'gravityformsmoderation' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Disable', 'gravityformsmoderation' ),
								'name'  => 'moderation_disabled',
							),
						),
					),
					$this->get_threshold_field(),
				),
			),
		);

		$attributes = $this->get_attributes_fields( $form );

		if ( $attributes ) {
			$fields[0]['fields'][] = $attributes;
		}

		// Adding custom word settings and default toxicity action field.
		$fields[0]['fields'][] = $this->get_custom_words_field( $this->get_plugin_setting( 'custom_words' ) );
		$fields[1]['fields'][] = $this->get_toxicity_action_field();

		return $fields;

	}


	// # PLUGIN METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Checks if an entry has toxic content.
	 *
	 * @since 1.0
	 *
	 * @param array $entry Entry details.
	 * @param array $form  Form details.
	 *
	 * @return array The aggregated toxicity score for the entry.
	 */
	public function get_entry_toxicity_score( $entry, $form ) {
		static $scores;

		if ( empty( $scores ) ) {
			$scores = array();
		}

		$entry_id = $entry['id'];

		// If score for this entry has already been calculated, use it.
		if ( ! empty( $scores[ $entry_id ] ) ) {
			return $scores[ $entry_id ];
		}

		$score = array(
			'fields' => array(),
			'total'  => 0,
		);

		$fields_to_analyze = $this->pick_fields_to_analyze( $form );

		if ( empty( $fields_to_analyze ) ) {
			$this->log_debug( __METHOD__ . '(): No fields to analyze for entry #' . $entry_id );

			return $score;
		}

		// Check custom words and see if any match.
		$custom_word_score = $this->get_custom_words_score( $form, $entry, $fields_to_analyze );

		// Run Perspective API.
		$moderation_api_score = $this->get_moderation_api_score( $form, $entry, $fields_to_analyze );

		$score = $this->merge_scores( $custom_word_score, $moderation_api_score );

		// Saving calculated score.
		$scores[ $entry_id ] = $score;

		return $score;
	}

	/**
	 * Determines if an entry is toxic.
	 *
	 * @since 1.0
	 *
	 * @param array $entry      Entry details.
	 * @param int   $threshold  The threshold for toxicity.
	 *
	 * @return bool True if the entry's toxicity score is higher than the threshold, otherwise false.
	 */
	public function is_entry_toxic( $entry, $threshold ) {
		$score = gform_get_meta( $entry['id'], "{$this->_slug}_score" );
		$this->log_debug( __METHOD__ . sprintf( '(): Threshold: %s; Result => ', $threshold ) . print_r( $score, true ) );

		if ( $score > $threshold ) {
			return true;
		}
		 else {
			 return false;
		 }
	}

	/**
	 * Take the appropriate action if an entry is determined to be toxic.
	 *
	 * @since 1.0
	 *
	 * @param array $entry Entry details.
	 * @param array $form  Form details.
	 *
	 * @return void.
	 */
	public function maybe_handle_toxic_entry( $entry, $form ) {
		$threshold = $this->get_form_threshold( $form );
		if ( $this->is_entry_toxic( $entry, $threshold ) ) {
			$toxicity_action = $this->get_form_toxicity_action( $form );
			switch ( $toxicity_action ) {
				case 'send_to_toxic':
					$this->filter_toxic_entry( $entry );
					break;
				case 'delete_toxic':
					$this->delete_toxic_entry( $entry );
					break;
			}
		}
	}

	/**
	 * Send the entry to the toxic box.
	 *
	 * @since 1.0
	 *
	 * @param array $entry Entry details.
	 *
	 * @return void.
	 */
	public function filter_toxic_entry( $entry ) {
		GFAPI::update_entry_property( $entry['id'], 'status', 'toxic' );
		$this->log_debug( __METHOD__ . '(): Entry #' . $entry['id'] . ' marked as toxic and move to the toxic box.' );
	}

	/**
	 * Delete the entry.
	 *
	 * @since 1.0
	 *
	 * @param array $entry Entry details.
	 *
	 * @return void.
	 */
	public function delete_toxic_entry( $entry ) {
		GFAPI::delete_entry( $entry['id'] );
		$this->log_debug( __METHOD__ . '(): Entry #' . $entry['id'] . ' marked as toxic and deleted.' );
	}

	/**
	 * Find the action to take for toxic entries.
	 *
	 * @since 1.0
	 *
	 * @param array $form The current form.
	 *
	 * @return string
	 */
	public function get_form_toxicity_action( $form ) {
		$form_settings = $this->get_form_settings( $form );
		if ( rgar( $form_settings, 'toxicity_action' ) ) {
			return $form_settings['toxicity_action'];
		} elseif ( $this->get_plugin_setting( 'toxicity_action' ) ) {
			return $this->get_plugin_setting( 'toxicity_action' );
		} else {
			return 'send_to_toxic';
		}
	}

	/**
	 * Merges the score from custom words with the moderation API, using the highest scores for each field and for the total.
	 *
	 * @since 1.0
	 *
	 * @param array $custom_word_score Scores calculated by the custom word check.
	 * @param array $moderation_api_score Scores calculated by the moderation API.
	 *
	 * @return array Returns a new array containing the field scores from custom words and from the moderation API
	 */
	public function merge_scores( $custom_word_score, $moderation_api_score ) {
		if ( ! is_array( $custom_word_score ) || ! is_array( $custom_word_score['fields'] ) ) {
			$custom_word_score = array(
				'fields' => array(),
				'total'  => 0,
			);
		}

		if ( ! is_array( $moderation_api_score ) || ! is_array( $moderation_api_score['fields'] ) ) {
			$moderation_api_score = array(
				'fields' => array(),
				'total'  => 0,
			);
		}

		// Merging associative arrays.
		$field_scores = $custom_word_score['fields'] + $moderation_api_score['fields'];
		foreach ( $field_scores as $key => $value ) {
			$field_scores[ $key ] = max( rgar( $custom_word_score['fields'], $key ), rgar( $moderation_api_score['fields'], $key ) );
		}

		$score = array(
			'fields' => $field_scores,
			'total'  => max( $custom_word_score['total'], $moderation_api_score['total'] ),
		);

		return $score;
	}

	/**
	 * Calls the moderation API and gets the toxicity scores for the specified $entry.
	 *
	 * @since 1.0
	 *
	 * @param array $form Current form object.
	 * @param array $entry Current entry object.
	 * @param array $fields_to_analyze Array of fields to be sent to the moderation API.
	 *
	 * @return array Returns the score computed by the moderation API.
	 */
	public function get_moderation_api_score( $form, $entry, $fields_to_analyze ) {

		$entry_id = $entry['id'];

		$this->log_debug( __METHOD__ . '(): Sending entry #' . $entry_id . ' to moderation API to analyze for toxicity.' );

		$analysis = array();
		$locale   = $this->get_locale( $entry );
		foreach ( $fields_to_analyze as $field ) {
			$field_value = $field->get_value_export( $entry );
			if ( empty( $field_value ) ) {
				continue;
			}

			$field_score = $this->api->analyze_text( $locale, $field_value, $form );
			if ( is_wp_error( $field_score ) ) {
				$this->log_error( __METHOD__ . sprintf( '(): Aborting; Unable to analyze value for %s(#%d - %s); code: %s; message: %s', $field->label, $field->id, $field->type, $field_score->get_error_code(), $field_score->get_error_message() ) );
				$field_score = 0;
			}

			$analysis[ (string) $field->id ] = $field_score;
		}

		$score = $this->aggregate_analysis( $analysis );

		$this->log_debug( __METHOD__ . '(): Result from moderation API => ' . print_r( $score, true ) );

		return $score;
	}

	/**
	 * Checks that the values of the specified fields do no contain any of the configured toxic words.
	 *
	 * @since 1.0
	 *
	 * @param array $form Current form object.
	 * @param array $entry Current entry object.
	 * @param array $fields_to_analyze Array of fields to be checked.
	 *
	 * @return array|false Returns a score array with a total of 1 if any field contains a toxic word. Returns false otherwise.
	 */
	public function get_custom_words_score( $form, $entry, $fields_to_analyze ) {

		$fields_score = array();

		// Check custom words.
		foreach ( $fields_to_analyze as $field ) {
			$field_value = $field->get_value_export( $entry );
			if ( empty( $field_value ) ) {
				continue;
			}

			if ( $this->has_toxic_words( $form, $field_value ) ) {
				$this->log_debug( __METHOD__ . sprintf( '(): Custom toxic word found in entry_id: %s', $entry['id'] ) );

				$fields_score[ (string) $field->id ] = 1;
			}
		}

		if ( count( $fields_score ) == 0 ) {
			return false;
		}

		return array(
			'fields' => $fields_score,
			'total'  => 1,
		);

	}

	/**
	 * Checks if the specified $field_value contains a toxic word.
	 *
	 * @since 1.0
	 *
	 * @param array  $form Current form object.
	 * @param string $field_value Field value to be checked.
	 *
	 * @return bool Returns true if the specified $field_value contains a toxic word. Returns false otherwise.
	 */
	public function has_toxic_words( $form, $field_value ) {

		$custom_word_setting = strtolower( $this->get_form_custom_words( $form ) );
		$custom_words        = empty( $custom_word_setting ) ? false : explode( ',', $custom_word_setting );
		if ( empty( $custom_words ) ) {
			return false;
		}

		$field_value = strtolower( $field_value );
		foreach ( $custom_words as $word ) {
			$word = trim( $word );
			if ( preg_match( "/\b{$word}\b/", $field_value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Take the detailed analysis from the API and aggregate the results for each toxicity attribute.
	 *
	 * @since 1.0
	 *
	 * @param array $analysis The analysis retrieved from the API.
	 *
	 * @return array
	 */
	public function aggregate_analysis( $analysis ) {
		$aggregate_analysis = array(
			'fields' => array(),
			'total'  => 0,
		);

		// Get the highest score for each field.
		foreach ( $analysis as $field_id => $field_scores ) {
			$field_score                               = is_array( $field_scores ) ? max( $field_scores ) : $field_scores;
			$aggregate_analysis['fields'][ $field_id ] = $field_score;
		}

		if ( empty( $aggregate_analysis['fields'] ) ) {
			return $aggregate_analysis;
		}

		// Get the highest score of all the fields.
		$aggregate_analysis['total'] = max( $aggregate_analysis['fields'] );

		return $aggregate_analysis;
	}

	/**
	 * Add "toxic" to the list of filters at the top of the entry list.
	 *
	 * @since 1.0
	 *
	 * @param array $filter_links   An array of properties for the filter links.
	 * @param array $form           The current form.
	 * @param bool  $include_counts Indicates if the entries should be counted.
	 *
	 * @return array
	 */
	public function add_toxicity_to_entry_list_filter( $filter_links, $form, $include_counts ) {
		$toxic_count    = $include_counts ? $this->count_toxic_entries( $form['id'] ) : 0;
		$filter_links[] = array(
			'id'            => 'toxic',
			'field_filters' => array(),
			'count'         => $toxic_count,
			'label'         => esc_html__( 'Toxic', 'gravityformsmoderation' ),
		);

		return $filter_links;
	}

	/**
	 * Add search parameter for "toxic" to the entries list.
	 *
	 * @since 1.0
	 *
	 * @param array $args Array of arguments that will be used when getting the entries to be displayed.
	 *
	 * @return array
	 */
	public function filter_toxic_entries_list( $args ) {
		if ( 'toxic' == rgar( $_GET, 'filter' ) ) {
			$args['search_criteria'] = array( 'status' => 'toxic' );
		}
		return $args;
	}

	/**
	 * Allows the entry export filter to return active entries as well as entries that have been marked as toxic.
	 *
	 * @param array $search_criteria The search criteria array to be filtered.
	 *
	 * @since 1.0
	 *
	 * @return array Returns the filtered search criteria array.
	 */
	public function add_toxic_entries_to_entry_export( $search_criteria ) {

		// Setting score filter as numeric.
		if( ! is_array( $search_criteria['field_filters'] ) ) {
			return $search_criteria;
		}

		foreach ( $search_criteria['field_filters'] as &$filter ) {
			if ( rgar( $filter, 'key' ) == 'gravityformsmoderation_score' ) {
				// Setting toxicity score as a numeric filter.
				$filter['is_numeric'] = true;

				// Adding toxic entries to the search if Toxicity score is used as a filter.
				$search_criteria['status'] = ['active', 'toxic'];

				break;
			}
		}
		return $search_criteria;
	}

	/**
	 * Add toxicity score to the entry meta.
	 *
	 * @since 1.0
	 *
	 * @param array $entry_meta The entry meta properties.
	 * @param int   $form_id    The ID of the current form.
	 *
	 * @return array
	 */
	public function get_entry_meta( $entry_meta, $form_id ) {
		$form = GFAPI::get_form( $form_id );

		$entry_meta[ "{$this->_slug}_score" ] = array(
			'label'                      => __( 'Toxicity Score', 'gravityformsmoderation' ),
			'is_numeric'                 => true,
			'update_entry_meta_callback' => array( $this, 'update_entry_meta' ),
			'is_default_column'          => ! $this->is_moderation_disabled( $form ),
			'filter'                     => array(
				'operators' => array( 'is', '>', '<' ),
			),
		);
		$entry_meta[ "{$this->_slug}_score_details" ] = array(
			'label'                      => __( 'Toxicity Score Details', 'gravityformsmoderation' ),
			'is_numeric'                 => false,
			'update_entry_meta_callback' => array( $this, 'update_entry_meta' ),
			'is_default_column'          => false,
		);

		return $entry_meta;
	}

	/**
	 * Remove the option to show the entry score details on the entry list page.
	 *
	 * @since 1.0
	 *
	 * @param bool   $display The value being filtered. True to display the field, false to hide it.
	 * @param object $field   The current field object.
	 * @param array  $form    The current form object.
	 *
	 * @return bool
	 */
	public function entry_list_score_details( $display, $field, $form ) {
		if ( "{$this->_slug}_score_details" == $field->id ) {
			return false;
		}

		return $display;
	}

	/**
	 * Save the toxicity score to the entry meta.
	 *
	 * @since 1.0
	 *
	 * @param string $key   The meta key being processed.
	 * @param array  $entry The entry being processed.
	 * @param array  $form  The form being processed.
	 *
	 * @return float|void
	 */
	public function update_entry_meta( $key, $entry, $form ) {
		if ( $this->is_moderation_disabled( $form ) ) {
			return;
		}

		$result = $this->get_entry_toxicity_score( $entry, $form );

		if ( ! $result ) {
			return;
		}

		if ( "{$this->_slug}_score" == $key ) {
			return round( $result['total'], 2 );
		} elseif ( "{$this->_slug}_score_details" == $key ) {
			return wp_json_encode( $result );
		}

	}


	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Gets the current site's locale. Allows locale to be filtered based on entry data.
	 *
	 * @param array $entry Current Entry object.
	 *
	 * @return string Returns the locale code.
	 */
	public function get_locale( $entry = null ) {
		/**
		 * Allows multi-language sites to change the locale based on entry data.
		 *
		 * @since 1.0.
		 *
		 * @param string $locale The locale being filtered.
		 * @param array $entry (Optional). During a form submission, contains the current Entry Object.
		 */
		$locale = apply_filters( 'gform_moderation_locale', get_locale(), $entry );
		return $locale;
	}

	/**
	 * Count how many toxic entries a form has.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id Current form id.
	 *
	 * @return false|mixed|string
	 */
	public function count_toxic_entries( $form_id ) {
		global $wpdb;

		$cache_key = 'form_counts_toxic_' . $form_id;

		$results = GFCache::get( $cache_key );

		if ( ! empty( $results ) ) {
			return $results;
		}

		$entry_table_name = GFFormsModel::get_entry_table_name();

		$sql = $wpdb->prepare(
			"SELECT
                    (SELECT count(DISTINCT(l.id)) FROM $entry_table_name l WHERE l.status='toxic' AND l.form_id=%d) as toxic",
			$form_id
		);

		$wpdb->timer_start();
		$results    = $wpdb->get_results( $sql, ARRAY_A );
		$time_total = $wpdb->timer_stop();
		if ( $time_total > 1 ) {
			GFCache::set( $cache_key, $results, true, 10 * MINUTE_IN_SECONDS );
		}

		return $results[0]['toxic'];
	}

	/**
	 * Given a form entry, return an array of fields that need to be analyzed.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form being processed.
	 *
	 * @return \GF_Field[]
	 */
	public function pick_fields_to_analyze( $form ) {
		/**
		 * Enables the types of fields that need to be analyzed to be overridden.
		 *
		 * @since 1.0
		 *
		 * @param array $field_types_to_analyze The types of fields to be analyzed for toxicity.
		 * @param array $form                   The form being processed.
		 */
		$field_types_to_analyze = apply_filters(
			'gform_moderation_field_types_to_analyze',
			array(
				'email',
				'name',
				'text',
				'textarea',
				'website',
				'address',
				'post_content',
				'post_custom_field',
				'post_excerpt',
				'post_title'
			),
			$form
		);

		$fields_to_analyze = array();
		foreach ( $form['fields'] as $field ) {
			if ( in_array( $field->get_input_type(), $field_types_to_analyze ) ) {
				$fields_to_analyze[] = $field;
			}
		}

		return $fields_to_analyze;
	}

	/**
	 * Find the toxicity threshold for this form.
	 *
	 * @since 1.0
	 *
	 * @param array $form The current form.
	 *
	 * @return float
	 */
	public function get_form_threshold( $form ) {
		$form_settings = $this->get_form_settings( $form );
		if ( rgar( $form_settings, 'threshold' ) ) {
			return floatval( $form_settings['threshold'] );
		} elseif ( $this->get_plugin_setting( 'threshold' ) ) {
			return floatval( $this->get_plugin_setting( 'threshold' ) );
		} else {
			// .5 is the default if the user hasn't set anything else.
			return 0.5;
		}
	}

	/**
	 * Gets the custom words configured for the specified form.
	 *
	 * @since 1.0
	 *
	 * @param array $form The current form object.
	 *
	 * @return string Returs the list of custom words.
	 */
	public function get_form_custom_words( $form ) {
		$form_settings = $this->get_form_settings( $form );
		$custom_words = isset( $form_settings['custom_words'] ) ? $form_settings['custom_words'] : $this->get_plugin_setting( 'custom_words' );
		return trim( $custom_words );
	}

	/**
	 * Register the toxicity details meta box on the entry details page.
	 *
	 * @since 1.0
	 *
	 * @param array $meta_boxes The properties for the meta boxes.
	 * @param array $entry The entry currently being viewed/edited.
	 * @param array $form The form object used to process the current entry.
	 *
	 * @return array
	 */
	public function register_toxicity_details_meta_box( $meta_boxes, $entry, $form ) {
		// If moderation is not disabled for the form, display the metabox.
		if ( ! $this->is_moderation_disabled( $form ) ) {
			$meta_boxes[ $this->_slug ] = array(
				'title'    => $this->get_short_title(),
				'callback' => array( $this, 'add_toxicity_details_meta_box' ),
				'context'  => 'side',
			);
		}

		return $meta_boxes;
	}

	/**
	 * Content for the toxicity details metabox.
	 *
	 * @since 1.0
	 *
	 * @param array $args An array containing the form and entry objects.
	 */
	public function add_toxicity_details_meta_box( $args ) {

		$form  = $args['form'];
		$entry = $args['entry'];

		$html = '';

		$meta = json_decode( gform_get_meta( $entry['id'], 'gravityformsmoderation_score_details' ), true );

		if ( ! $meta ) {
			return;
		}

		foreach ( $meta['fields'] as $field_id => $value ) {
			foreach ( $form['fields'] as $field ) {
				if ( $field_id === $field['id'] ) {
					$field_label = $field['label'];
				}
			}
			$html .= sprintf(
				'<div class="gform-moderation-toxicity-label">%s</div><div class="gform-moderation-toxicity-value">%s: %s</div><div class="field_toxicity_indicator"></div>',
				esc_html( $field_label ),
				__( 'Toxicity', 'gravityformsmoderation' ),
				esc_html( round( $value, 1 ) )
			);
		}

		echo $html;
	}

	/**
	 * Determine if moderation is disabled.
	 *
	 * @since 1.0
	 *
	 * @param  array $form The form object.
	 *
	 * @return bool True if moderation is disabled for the form, otherwise false.
	 */
	public function is_moderation_disabled( $form ) {
		$form_settings = $this->get_form_settings( $form );
		if ( rgar( $form_settings, 'moderation_disabled' ) ) {
			return true;
		}

		return false;
	}

}
