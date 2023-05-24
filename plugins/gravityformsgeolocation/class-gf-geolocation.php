<?php

namespace Gravity_Forms\Gravity_Forms_Geolocation;

use \GFForms;
use \GFAddOn;
use \GF_Field_Address;

defined( 'ABSPATH' ) || die();

// Include the Gravity Forms Add-On Framework.
GFForms::include_addon_framework();

/**
 * Gravity Forms Gravity Forms Geolocation Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Gravity Forms
 * @copyright Copyright (c) 2022, Gravity Forms
 */
class GF_Geolocation extends GFAddOn {

	// Strings
	const SETTING_GOOGLE_PLACES_API_KEY           = 'google_places_api_key';
	const SETTING_COLLECT_SUBMITTER_LOCATION      = 'google_collect_user_location';
	const SETTING_GOOGLE_PLACES_PREVENT_CONFLICTS = 'google_places_prevent_conflicts';

	/**
	 * Defines the version of the Gravity Forms Geolocation Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version.
	 */
	protected $_version = GF_GEOLOCATION_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = GF_GEOLOCATION_MIN_GF_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsgeolocation';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsgeolocation/geolocation.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the title of this add-on.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the add-on.
	 */
	protected $_title = 'Gravity Forms Geolocation Add-On';

	/**
	 * Defines the short title of the add-on.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Geolocation';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capabilities needed for the Geolocation Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_geolocation' );

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_geolocation';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_geolocation';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @var    GF_Geolocation $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Saves an API instance for Google Authorization.
	 *
	 * @since  1.0
	 * @var    GF_Google_Places_API $api null until instance is set.
	 */
	protected $api = null;

	/**
	 * Cached value of whether the environment meets minimum requirements or not.
	 *
	 * @since 1.0
	 * @var   array|null $_meets_minimum_requirements null until value is set.
	 */
	private $_meets_minimum_requirements = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @since  1.0
	 *
	 * @return GF_Geolocation $_instance An instance of this class
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	// # INITIALIZATION METHODS --------------------------------------------------------------------------------------------

	/**
	 * Initialize admin-specific hooks.
	 *
	 * @since 1.0
	 */
	public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_filter( 'gform_entry_field_value', array( $this, 'entry_field_value' ), 10, 4 );
		add_action( 'gform_field_advanced_settings', array( $this, 'field_settings' ), 10, 2 );
		add_filter( 'gform_get_field_value', array( $this, 'add_coords_to_lead_value' ), 10, 3 );
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'add_map_metabox_to_entry_detail' ), 10, 3 );
	}

	/**
	 * Initialize frontend-specific hooks.
	 *
	 * @since 1.0
	 */
	public function init_frontend() {
		parent::init_frontend();

		add_filter( 'gform_field_content', array( $this, 'filter_field_content' ), 10, 3 );
		add_filter( 'gform_field_container', array( $this, 'filter_field_container' ), 10, 3 );
		add_filter( 'gform_form_tag', array( $this, 'add_submitter_location_field' ), 10, 2 );
		add_action( 'gform_after_submission', array( $this, 'save_location_as_entry_meta' ), 10, 2 );
	}

	/**
	 * Initialize plugin.
	 *
	 * @since 1.0
	 */
	public function init() {
		parent::init();

		add_filter( 'gform_gf_field_create', array( $this, 'filter_field_create' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'avoid_maps_conflicts' ), 999, 0 );
	}

	/**
	 * Initializes the Google Places API.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function initialize_api() {
		// If the API is not initialized, set it up.
		if ( is_null( $this->api ) ) {
			// Initialize Google Places API library.
			if ( ! class_exists( 'GF_Google_Places_API' ) ) {
				require_once( 'includes/class-gf-google-places-api.php' );
			}

			$this->api = new GF_Google_Places_API( $this, $this->get_google_places_api_key() );
		}


		return $this->api->validate_api_key();
	}

	// # SCRIPT AND STYLE METHODS --------------------------------------------------------------------------------------------

	/**
	 * Register styles.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function styles() {
		$meets_requirements = $this->meets_minimum_requirements();
		if ( ! $meets_requirements['meets_requirements'] ) {
			return parent::styles();
		}

		$min                     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		$admin_enqueue_condition = is_admin() ? array( array( 'admin_page' => array( 'entry_view' ) ) ) : array( function() { return false; } );

		$styles = array(
			array(
				'handle'    => 'gfcf_admin',
				'src'       => $this->get_base_url() . "/assets/css/dist/admin{$min}.css",
				'version'   => $this->_version,
				'in_footer' => false,
				'enqueue'   => $admin_enqueue_condition,
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * Register scripts.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function scripts() {
		$meets_requirements = $this->meets_minimum_requirements();
		if ( ! $meets_requirements['meets_requirements'] ) {
			return parent::scripts();
		}

		$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		$api_key = $this->get_google_places_api_key();

		$frontend_enqueue_condition = ! is_admin() ? array( array( $this, 'frontend_script_callback' ) ) : array( function() { return false; } );
		$admin_enqueue_condition    = is_admin() ? array( array( 'admin_page' => array( 'form_editor', 'entry_view' ) ) ) : array( function() { return false; } );
		$maps_condition = ! is_admin() ? $frontend_enqueue_condition : $admin_enqueue_condition;

		$scripts = array(
			array(
				'handle'    => 'gform_geolocation_maps_api_js',
				'src'       => 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places',
				'version'   => $this->_version,
				'deps'      => array(),
				'in_footer' => true,
				'enqueue'   => $maps_condition,
			),
			array(
				'handle'    => 'gform_geolocation_vendor_theme_js',
				'src'       => trailingslashit( $this->get_base_url() ) . "assets/js/dist/vendor-theme{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'gform_geolocation_maps_api_js', 'gform_gravityforms_theme' ),
				'in_footer' => true,
				'enqueue'   => $frontend_enqueue_condition,
			),
			array(
				'handle'    => 'gform_geolocation_theme_js',
				'src'       => trailingslashit( $this->get_base_url() ) . "assets/js/dist/scripts-theme{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'gform_geolocation_vendor_theme_js', 'gform_gravityforms_theme' ),
				'in_footer' => true,
				'enqueue'   => $frontend_enqueue_condition,
			),
			array(
				'handle'    => 'gform_geolocation_vendor_admin_js',
				'src'       => trailingslashit( $this->get_base_url() ) . "assets/js/dist/vendor-admin{$min}.js",
				'version'   => $this->_version,
				'deps'      => array(),
				'in_footer' => true,
				'enqueue'   => $admin_enqueue_condition,
			),
			array(
				'handle'    => 'gform_geolocation_admin_js',
				'src'       => trailingslashit( $this->get_base_url() ) . "assets/js/dist/scripts-admin{$min}.js",
				'version'   => $this->_version,
				'deps'      => array(),
				'in_footer' => true,
				'enqueue'   => $admin_enqueue_condition,
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * If the "avoid conflicts" setting is enabled, attempt to locate and use an existing Google Maps
	 * library instead of enqueueing our own. This can help prevent conflicts that arise from multiple
	 * instances of the library being enqueued on a single page.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function avoid_maps_conflicts() {
		if ( ! $this->get_plugin_setting( self::SETTING_GOOGLE_PLACES_PREVENT_CONFLICTS ) ) {
			return;
		}

		require_once( 'includes/class-gf-google-maps-resolver.php' );

		$deduped = new GF_Google_Maps_Resolver();
		$deduped->resolve( 'gform_geolocation_maps_api_js', array( 'places' ) );
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
		return 'gform-icon--place';
	}

	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => esc_html__( 'Form Submission Geolocation Settings', 'gravityformsgeolocation' ),
				'fields' => array(
					array(
						'label' => esc_html__( 'Disable Location Collection for This Form', 'gravityformsgeolocation' ),
						'type'  => 'toggle',
						'name'  => self::SETTING_COLLECT_SUBMITTER_LOCATION,
					),
				)
			)
		);
	}

	/**
	 * Define plugin settings fields.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'       => esc_html__( 'Geolocation Settings', 'gravityformsgeolocation' ),
				'description' => sprintf(
					// translator: %1$s is the opening link tag, %2$s is the closing link tag.
					esc_html__(
						'Provide an improved user experience for your address fields by using geolocation to suggest address options as you type.  If you don\'t have a Google Places API key, you can %1$screate one here.%2$s',
						'gravityformsgeolocation'
					),
					'<a href="https://developers.google.com/maps/documentation/places/web-service" target="_blank">',
					'</a>',
				),
				'fields'      => array(
					array(
						'label'             => esc_html__( 'Google Places API Key', 'gravityformsgeolocation' ),
						'type'              => 'text',
						'name'              => self::SETTING_GOOGLE_PLACES_API_KEY,
						'tooltip'           => esc_html__(
							'Enter your Google Places API key. If you do not have one, you can create one in the link above.',
							'gravityformsgeolocation'
						),
						'class'             => 'small',
						'feedback_callback' => array( $this, 'validate_google_places_api_key' )
					),
				),
			),
			array(
				'title'       => esc_html__( 'Form Submission Geolocation Settings', 'gravityformsgeolocation' ),
				'description' => esc_html__(
					'Attempt to detect and store the Submitting User\'s location on form submission and display it as a map on the entry detail page. This can be disabled per form in the Form Geolocation Settings.',
					'gravityformsgeolocation'
				),
				'fields'      => array(
					array(
						'label'         => esc_html__( 'Collect Submitter Location Data on All Forms', 'gravityformsgeolocation' ),
						'type'          => 'toggle',
						'name'          => self::SETTING_COLLECT_SUBMITTER_LOCATION,
						'default_value' => true,
						'tooltip'       => esc_html__(
							'If enabled, location data for the user submitting the form will be collected and displayed on the Entry Detail page.',
							'gravityformsgeolocation'
						),
					),
				)
			),
			array(
				'title'       => esc_html__( 'Google Maps Conflicts', 'gravityformsgeolocation' ),
				'description' => esc_html__( 'If you have other plugins enabled that load the Google Maps Javascript libary, you can enable this setting to avoid conflicts when Geolocation is enabled. Unless you are experiencing javscript conflicts, it is safest to leave this setting disabled.', 'gravityformsgeolocation' ),
				'fields'      => array(
					array(
						'label'   => esc_html__( 'Prevent Google Maps Script Conflicts', 'gravityformsgeolocation' ),
						'type'    => 'toggle',
						'name'    => self::SETTING_GOOGLE_PLACES_PREVENT_CONFLICTS,
						'tooltip' => esc_html__(
							'If another plugin is using the Google Maps library, it can cause javascript conflicts. Enable this setting to use the existing library and avoid conflicts when Geolocation is enabled.',
							'gravityformsgeolocation'
						),
					),
				),
			),
		);
	}

	// # FIELD SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Add the tooltips for the field.
	 *
	 * @param array $tooltips An associative array of tooltips where the key is the tooltip name and the value is the tooltip.
	 *
	 * @return array
	 */
	public function tooltips( $tooltips ) {
		$geolocation_tooltips = array(
			'geolocation_suggestions' => '<strong>' . esc_html__( 'Geolocation Suggestions', 'gravityformsgeolocation' ) . '</strong>' . esc_html__( 'Enable this setting to allow this field to be populated using the options suggested by the geolocation service.', 'gravityformsgeolocation' ),
		);

		return array_merge( $tooltips, $geolocation_tooltips );
	}

	/**
	 * Add the custom settings for the Geolocation plugin to the Advanced tab.
	 *
	 * @param int $position The position the settings should be located at.
	 * @param int $form_id The ID of the form currently being edited.
	 */
	public function field_settings( $position, $form_id ) {
		$meets_requirements = $this->meets_minimum_requirements();
		if ( ! $meets_requirements['meets_requirements'] ) {
			return parent::scripts();
		}

		if ( $position !== 175 ) {
			return;
		}

		?>
		<li class="geolocation_suggestions_setting field_setting">
			<?php if ( $this->validate_google_places_api_key() ) : ?>
				<input type="checkbox" id="ggeolocation-enable-geolocation-suggestions" />
				<label for="ggeolocation-enable-geolocation-suggestions" class="inline"><?php esc_html_e( 'Enable Geolocation Suggestions', 'gravityformsgeolocation' ); ?><?php gform_tooltip( 'geolocation_suggestions' ); ?></label>
			<?php else : ?>
				<div class="gform-alert gform-alert--theme-primary gform-alert--error gform-alert--inline">
					<span class="gform-alert__icon gform-icon gform-icon--circle-notice-fine"></span>
					<div class="gform-alert__message-wrap">
						<p class="gform-alert__message">
							<?php
							// translators: the placeholders here represent opening and closing <a> tags.
							printf( __( 'Geolocation suggestions cannot be enabled without a %svalid Google Places API Key.%s', 'gravityformsgeolocation' ), '<a target="_blank" href="' . admin_url( 'admin.php?page=gf_settings&subview=gravityformsgeolocation' ) . '">', '</a>' );
							?>
						</p>
					</div>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}

	// # FIELD MODIFICATION -------------------------------------------------------------------------------------------------

	public function add_submitter_location_field( $form_string, $form ) {
		$is_edit = rgar( $_REQUEST, 'context' );

		if ( ! empty( $is_edit ) ) {
			return $form_string;
		}

		$location_enabled = $this->is_user_location_enabled( $form );

		if ( ! $location_enabled ) {
			return $form_string;
		}

		return $form_string . '<input name="geolocation_submitter_location" id="geolocation_submitter_location" type="hidden" data-js="geolocation_submitter_location"/>';
	}

	public function save_location_as_entry_meta( $entry, $form ) {
		$value = rgar( $_POST, 'geolocation_submitter_location' );

		if ( empty( $value ) ) {
			return;
		}

		$data = json_decode( stripslashes( $value ), true );

		if ( ! empty( $data['message'] ) ) {
			gform_add_meta( $entry['id'], 'geolocation_submitter_location_err', $data['message'] );
			return;
		}

		if ( empty( $data['lat'] ) || empty( $data['lng'] ) ) {
			return;
		}

		gform_add_meta( $entry['id'], 'geolocation_submitter_location_lat', $data['lat'] );
		gform_add_meta( $entry['id'], 'geolocation_submitter_location_lng', $data['lng'] );
	}

	/**
	 * Modify address field with latitude and longitude if geolocation is enabled.
	 *
	 * @param \GF_Field $field      Field to modify.
	 * @param array     $properties Properties of the field.
	 *
	 * @return \GF_Field
	 */
	public function filter_field_create( $field, $properties ) {
		// Return early if field is not valid geolocation.
		if ( ! $this->is_valid_geolocation_field( $field ) ) {
			return $field;
		}

		// Check if inputs is an array, if not return early.
		if ( ! is_array( $field->inputs ) ) {
			return $field;
		}

		// Loop through inputs to see if latitude and longitude fields already exist.
		$latitude_exists  = false;
		$longitude_exists = false;
		$latitude_id      = "{$field->id}.geolocation_latitude";
		$longitude_id     = "{$field->id}.geolocation_longitude";
		foreach ( $field->inputs as $input ) {
			if ( $latitude_exists && $longitude_exists ) {
				continue;
			}
			if ( $latitude_id === $input['id'] ) {
				$latitude_exists = true;
			}
			if ( $longitude_id === $input['id'] ) {
				$longitude_exists = true;
			}
		}

		// If latitude and longitude inputs already exist, return early.
		if ( $latitude_exists && $longitude_exists ) {
			return $field;
		}

		// Add new latitude and longitude inputs.
		$inputs     = $field->inputs;
		$new_inputs = array();
		if ( ! $latitude_exists ) {
			$new_inputs[] = array(
				'id'       => $latitude_id,
				'label'    => esc_html__( 'Latitude', 'gravityforms' ),
				'name'     => '',
				'isHidden' => true,
			);
		}
		if ( ! $longitude_exists ) {
			$new_inputs[] = array(
				'id'       => $longitude_id,
				'label'    => esc_html__( 'Longitude', 'gravityforms' ),
				'name'     => '',
				'isHidden' => true,
			);
		}

		$field->inputs = array_merge( $inputs, $new_inputs );

		return $field;
	}

	/**
	 * Modify address field with latitude and longitude inputs if geolocation is enabled.
	 *
	 * @param string    $content The input markup.
	 * @param \GF_Field $field   The field to be displayed.
	 * @param mixed     $value   Value for the field.
	 *
	 * @return string The filtered field content.
	 */
	public function filter_field_content( $content, $field, $value ) {
		// Return early if field is not valid geolocation.
		if ( ! $this->is_valid_geolocation_field( $field ) ) {
			return $content;
		}

		// Grab ids and form.
		$form_id  = $field->formId;
		$field_id = $field->id;

		$is_entry_detail = $field->is_entry_detail();
		$is_form_editor  = $field->is_form_editor();
		$input_id        = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$field_id" : 'input_' . $form_id . "_$field_id";

		// Get markup and split closing div from the rest of the markup.
		preg_match( '/^([\S\s]*)(<\/div>)$/', $content, $matches );

		// Make sure matches are there, if not, return early.
		if ( 3 !== count( $matches ) ) {
			return $content;
		}

		// Get latitude and longitude inputs.
		$latitude_field_id  = "{$field_id}.geolocation_latitude";
		$longitude_field_id = "{$field_id}.geolocation_longitude";

		// Get latitude and longitude values.
		$latitude_value  = esc_attr( rgget( $latitude_field_id, $value ) );
		$longitude_value = esc_attr( rgget( $longitude_field_id, $value ) );

		// Set latitude and longitude input ids.
		$latitude_input_id  = "{$input_id}_geolocation_latitude";
		$longitude_input_id = "{$input_id}_geolocation_longitude";

		// Create hidden inputs for latitude and longitude.
		$latitude  = sprintf( "<input type='hidden' class='gform_hidden' name='input_%s' id='%s' value='%s'/>", $latitude_field_id, $latitude_input_id, $latitude_value );
		$longitude = sprintf( "<input type='hidden' class='gform_hidden' name='input_%s' id='%s' value='%s'/>", $longitude_field_id, $longitude_input_id, $longitude_value );

		// Add placeholder to address line 1 input if it exists.
		$before_lat_lng_markup = preg_replace( '/[\'\"]input_[\d]+\.1[\'\"]/', '$0 placeholder=""', $matches[1] );

		// Build markup with latitude and longitude inputs.
		$markup = $before_lat_lng_markup . $latitude . $longitude . $matches[2];

		return $markup;
	}

	/**
	 * Modify the field container to add the data-js attribute.
	 *
	 * @since 1.0
	 *
	 * @param string    $container The field container.
	 * @param \GF_Field $field     The field to be displayed.
	 * @param array     $form      The form object.
	 *
	 * @return string The filtered field container.
	 */
	public function filter_field_container( $container, $field, $form ) {
		// Return early if field is not valid geolocation.
		if ( ! $this->is_valid_geolocation_field( $field ) ) {
			return $container;
		}

		// Add data attr to wrapper.
		$data_js = ' data-js="geolocation-enabled"';
		return preg_replace( '/>\{FIELD_CONTENT\}/', $data_js . '$0', $container );
	}

	// # FIELD ENTRY ---------------------------------------------------------------------------------------------------

	/**
	 * Add geolocation coordinates to the lead value.
	 *
	 * @since 1.0
	 *
	 * @param array     $value The input value.
	 * @param array     $lead  The entry lead.
	 * @param \GF_Field $field The field to add coordinates to.
	 *
	 * @return array The filtered value.
	 */
	public function add_coords_to_lead_value( $value, $lead, $field ) {
		// Return early if field is not valid geolocation.
		if ( ! $this->is_valid_geolocation_field( $field ) ) {
			return $value;
		}

		// Return early if value is not array.
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$loc_vals = $this->get_lat_long_from_meta( $field, $lead );

		if ( empty( $loc_vals['lat_val'] ) && empty( $loc_vals['long_val'] ) ) {
			return $value;
		}

		$value[ $loc_vals['latitude_id'] ]  = $loc_vals['lat_val'];
		$value[ $loc_vals['longitude_id'] ] = $loc_vals['long_val'];

		return $value;
	}

	/**
	 * Filter entry field value to add geolocation coordinates.
	 *
	 * @since 1.0
	 *
	 * @param string    $display_value The display value of the entry.
	 * @param \GF_Field $field         The field being displayed.
	 * @param array     $lead          The entry lead.
	 * @param array     $form          The form object.
	 *
	 * @return string The filtered display value.
	 */
	public function entry_field_value( $display_value, $field, $lead, $form ) {
		// Return early if field is not valid geolocation.
		if ( ! $this->is_valid_geolocation_field( $field ) ) {
			return $display_value;
		}

		// Format for entry is always html.
		$line_break = '<br />';

		// Get latitude and longitude values.
		$value           = \RGFormsModel::get_lead_field_value( $lead, $field );
		$latitude_id     = "{$field->id}.geolocation_latitude";
		$longitude_id    = "{$field->id}.geolocation_longitude";
		$latitude_value  = trim( rgget( $latitude_id, $value ) );
		$longitude_value = trim( rgget( $longitude_id, $value ) );

		// If latitude and longitude values are empty, nothing to do here, return early.
		if ( empty( $latitude_value ) && empty( $longitude_value ) ) {
			return $display_value;
		}

		// Translators: %s is the latitude value.
		$latitude  = ! empty( $latitude_value ) ? $line_break . esc_html( sprintf( __( 'Latitude: %s', 'gravityformsgeolocation' ), $latitude_value ) ) : '';
		// Translators: %s is the longitude value.
		$longitude = ! empty( $longitude_value ) ? $line_break . esc_html( sprintf( __( 'Longitude: %s', 'gravityformsgeolocation' ), $longitude_value ) ) : '';

		// Get matches for address entry.
		preg_match( '/^([\S\s]*)(<br\/><a[\S\s]*$)/', $display_value, $matches );

		// If matches is empty, there is no map link, just add latitude and longitude.
		if ( empty( $matches ) ) {
			return $display_value . $latitude . $longitude;
		}

		// Add latitude and longitude before the map link.
		return $matches[1] . $latitude . $longitude . $matches[2];
	}

	/**
	 * For forms which contain a geolocated address field, display a location metabox with
	 * a rendered map as content.
	 *
	 * @since 1.0
	 *
	 * @param array $metaboxes The current metaboxes being added to the screen.
	 * @param array $entry     The current entry being evaluated.
	 * @param array $form      The current form being evaluated.
	 *
	 * @return array
	 */
	public function add_map_metabox_to_entry_detail( $metaboxes, $entry, $form ) {
		$lat = gform_get_meta( $entry['id'], 'geolocation_submitter_location_lat' );
		$lng = gform_get_meta( $entry['id'], 'geolocation_submitter_location_lng' );
		$err = gform_get_meta( $entry['id'], 'geolocation_submitter_location_err' );

		if ( empty( $err ) && ( empty( $lat ) || empty( $lng ) ) ) {
			return $metaboxes;
		}

		$callback_args = empty( $err ) ? array( 'lat' => $lat, 'lng' => $lng ) : array( 'err' => $err );

		$metaboxes['gfcf_user_location_map'] = array(
			'title'         => __( 'User Location', 'gravityformsgeolocation' ),
			'callback'      => array( $this, 'render_location_metabox' ),
			'context'       => 'normal',
			'priority'      => 'core',
			'callback_args' => $callback_args,
		);

		return $metaboxes;
	}

	/**
	 * Render the user locatin metabox.
	 *
	 * @since 1.0
	 *
	 * @param array $args    The arguments sent to the metabox.
	 * @param array $metabox The values registered for the metabox.
	 *
	 * @return void
	 */
	public function render_location_metabox( $args, $metabox ) {
		$values = $metabox['args'];

		if ( ! empty( $values['err'] ) ) {
			printf( '<div class="gfcf-map-error-wrapper"><p><strong>%s</strong><p><p><em>%s</em></p></div>', __( 'There was an error fetching the user location. See message below:', 'gravityformsgeolocation' ), $values['err'] );
			return;
		}

		$table = '<table class="gform-table">';
		$table .= '<tbody><tr>';
		$table .= sprintf( '<td><strong>%s</strong></td>', __( 'Lat/Long', 'gravityformsgeolocation' ) );
		$table .= sprintf( '<td>%s, %s</td>', $values['lat'], $values['lng'] );
		$table .= '</tr></tbody>';
		$table .= '</table>';

		printf( '<div class="gcfc-map-wrapper" data-js="geolocation-map-root" data-lat="%s" data-long="%s"></div>%s', $values['lat'], $values['lng'], $table );
	}

	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * @inheritDoc
	 */
	public function meets_minimum_requirements() {
		if ( is_null( $this->_meets_minimum_requirements ) ) {
			$this->_meets_minimum_requirements = parent::meets_minimum_requirements();
		}

		return $this->_meets_minimum_requirements;
	}

	/**
	 * Determine if user location gathering is enabled for the given form.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form object being evaluated.
	 *
	 * @return bool
	 */
	private function is_user_location_enabled( $form ) {
		$form_settings = $this->get_form_settings( $form );

		$disabled = empty( $form_settings ) ? false : rgar( $form_settings, self::SETTING_COLLECT_SUBMITTER_LOCATION, false );

		if ( $disabled ) {
			return false;
		}

		$global = $this->get_plugin_setting( self::SETTING_COLLECT_SUBMITTER_LOCATION );

		return (bool) $global;
	}

	/**
	 * Get the lat/long values for the given field from the associated lead.
	 *
	 * @since 1.0
	 *
	 * @param object $field The field being evaluated.
	 * @param array  $lead  The lead being evaluated.
	 *
	 * @return array
	 */
	private function get_lat_long_from_meta( $field, $lead ) {
		$latitude_id  = "{$field->id}.geolocation_latitude";
		$longitude_id = "{$field->id}.geolocation_longitude";

		$lat_val  = gform_get_meta( $lead['id'], $latitude_id );
		$long_val = gform_get_meta( $lead['id'], $longitude_id );

		return array( 'lat_val' => $lat_val, 'long_val' => $long_val, 'latitude_id' => $latitude_id, 'longitude_id' => $longitude_id );
	}

	/**
	 * Check if the field is a valid geolocation field.
	 *
	 * @since 1.0
	 *
	 * @param \GF_Field $field The field to check.
	 *
	 * @return bool Whether the field is a valid geolocation field.
	 */
	private function is_valid_geolocation_field( $field ) {
		// Return early if field type is not set.
		if ( ! isset( $field->type ) ) {
			return false;
		}

		// Return early if field type is not address.
		if ( 'address' !== $field->type ) {
			return false;
		}

		// Return early if geolocation is not enabled.
		if ( empty( $field->ggeolocationEnableGeolocationSuggestions ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the form has an address field and geolocation suggestions is enabled.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function frontend_script_callback( $form ) {
		if ( $this->is_user_location_enabled( $form ) ) {
			return true;
		}

		$fields = \GFAPI::get_fields_by_type( $form, array( 'address' ) );

		// No address field, return false.
		if ( empty( count( $fields ) ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( $field->is_administrative() && ! $field->allowsPrepopulate && ! GFForms::get_page() ) {
				continue;
			}

			// Check if geolocation setting is empty.
			if ( empty( $field->ggeolocationEnableGeolocationSuggestions ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get the Google Places API key.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_google_places_api_key() {
		$api_key = $this->get_plugin_setting( self::SETTING_GOOGLE_PLACES_API_KEY );

		if ( 'string' !== gettype( $api_key ) ) {
			return '';
		};

		return $api_key;
	}

	/**
	 * Validates the Google Places API key.
	 *
	 * @since 1.0
	 *
	 * @return bool|null
	 */
	public function validate_google_places_api_key() {
		if ( empty( $this->get_google_places_api_key() ) ) {
			return null;
		}

		return $this->initialize_api();
	}

}
