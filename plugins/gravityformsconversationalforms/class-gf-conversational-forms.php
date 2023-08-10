<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms;

use \GFCommon;
use Gravity_Forms\Gravity_Forms\GF_Service_Container;
use Gravity_Forms\Gravity_Forms\Settings\Fields;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\Util\Colors;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Framework\GF_Style_Layer;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\GFCF_Style_Layers_Provider;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Layers\GF_Conversational_Forms_Fluent_Style_Handler;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Layers\Views\Form_View;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Layers\Views\Conversational_Field_View;
use \GFFormsModel;

\GFForms::include_addon_framework();

/**
 * Main AddOn class.
 *
 * @since 1.0
 */
class GF_Conversational_Forms extends \GFAddOn {

	protected $_version                  = GF_CF_VERSION;
	protected $_min_gravityforms_version = GF_CF_MIN_GF_VERSION;
	protected $_slug                     = 'gravityformsconversationalforms';
	protected $_path                     = 'gravityformsconversationalforms/conversationalforms.php';
	protected $_full_path                = __FILE__;
	protected $_title                    = 'Gravity Forms Conversational Forms Add-On';
	protected $_short_title              = 'Conversational Forms';
	protected $_enable_rg_autoupgrade    = true;

	protected $_enable_theme_layer = true;

	/**
	 * @var object|null $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	private static $_compat_check = null;

	/**
	 * @var GF_Service_Container
	 */
	protected $container;

	/**
	 * The query variable used when permalinks are set to plain.
	 */
	public $query_var = 'gf_conversational';

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return GF_Conversational_Forms $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	// # INITIALIZATION METHODS --------------------------------------------------------------------------------------------

	/**
	 * Memoize result of compat check to avoid running multiple times.
	 *
	 * @param string $min_gravityforms_version
	 *
	 * @return bool
	 */
	public function is_gravityforms_supported( $min_gravityforms_version = '' ) {
		if ( ! is_null( self::$_compat_check ) ) {
			return self::$_compat_check;
		}

		self::$_compat_check = parent::is_gravityforms_supported( $min_gravityforms_version );

		return self::$_compat_check;
	}

	/**
	 * Require any files and initialize any methods required before init.
	 *
	 * @since 1.0
	 *
	 * @return mixed|void
	 */
	public function pre_init() {
		parent::pre_init();

		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		// Layers
		require_once( dirname( __FILE__ ) . '/includes/theme-layers/layers/views/class-form-view.php' );
		require_once( dirname( __FILE__ ) . '/includes/theme-layers/layers/views/class-conversational-field-markup.php' );

		//settings framework
		require_once( \GFCommon::get_base_path() . '/includes/settings/class-fields.php' );

		// Fields
		require_once( dirname( __FILE__ ) . '/includes/settings/class-permalink.php' );
		require_once( dirname( __FILE__ ) . '/includes/settings/class-file-upload.php' );
		require_once( dirname( __FILE__ ) . '/includes/settings/class-range.php' );
		require_once( dirname( __FILE__ ) . '/includes/settings/class-swatch.php' );

		Fields::register( 'permalink', 'Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings\Permalink' );
		Fields::register( 'file_upload', 'Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings\FileUpload' );
		Fields::register( 'range', 'Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings\Range' );
		Fields::register( 'swatch', 'Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings\Swatch' );
	}

	/**
	 * Initialize the hooks and filters needed for Front-End display.
	 *
	 * @since 1.0
	 *
	 * @return mixed|void
	 */
	public function init_frontend() {
		parent::init_frontend();

		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		add_filter( 'gform_full_screen_form_for_display', array( $this, 'check_if_full_screen_enabled' ), 10, 3 );
		add_filter( 'gform_full_screen_template_path', array( $this, 'get_full_screen_template_path' ), 10, 1 );
		add_filter( 'gform_target_page', array( $this, 'filter_target_page' ), 10, 2 );
		add_filter( 'gform_pre_render' , array( $this, 'customize_form_settings' ), 10, 1 );

		add_action( 'wp_head', function() {
			if ( has_action( 'wp_head', '_block_template_viewport_meta_tag' ) !== false ) {
				return;
			}

			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
		}, 0 );

		add_filter( 'gform_confirmation_anchor', '__return_false' );

		add_filter( 'gform_submit_button', function( $button, $form ) {
			if ( is_admin() || ! $this->is_conversational_form( $form ) ) {
				return $button;
			}

			/* Translators: &#9166;: Symbol for enter key on keyboard. */
			$field_nav_text = esc_html__( 'Press Enter', 'gravityformsconversationalforms' );

			return '<div class="gform-conversational__field-form-footer-submit">' . $button . '<span class="gform-conversational__field-nav-helper-text">' . $field_nav_text . '<span class="gform-conversational__field-nav-helper-icon gform-orbital-icon gform-orbital-icon--arrow-back" aria-hidden="true"></span></span></div>';
		}, 999, 2 );
	}

	/**
	 * Initialize the hooks and filters needed for admin display.
	 *
	 * @since 1.0
	 *
	 * @return mixed|void
	 */
	public function init_admin() {
		parent::init_admin();

		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		add_filter( 'gform_form_actions', array( $this, 'filter_form_actions' ), 10, 2 );
	}

	/**
	 * Initialize the hooks and filters needed for admin and front end.
	 *
	 * @since 1.0
	 *
	 * @return mixed|void
	 */
	public function init(){
		parent::init();

		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		add_action( 'wp_ajax_nopriv_gfcf_validate_field', array( $this, 'validate_field' ) );
		add_action( 'wp_ajax_gfcf_validate_field', array( $this, 'validate_field' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'localize_admin_scripts' ) );
		add_action( 'gform_enqueue_scripts', array( $this, 'localize_frontend_scripts' ), 1000, 2 );

		add_action( 'gform_post_form_duplicated', array( $this, 'prevent_duplicate_permalinks' ), 10, 2 );

		add_filter( 'gform_form_theme_slug', array( $this, 'customize_form_theme_slug' ), 10, 2 );

		add_filter( 'wp_unique_post_slug_is_bad_flat_slug', array( $this, 'is_bad_flat_slug' ), 10, 4 );
		add_filter( 'wp_unique_post_slug_is_bad_hierarchical_slug', array( $this, 'is_bad_hierarchical_slug' ), 10, 5 );

		if ( $this->is_plain_permalinks() ) {
			add_filter( 'query_vars', array( $this, 'add_slug_query_var' ) );
		}
	}

	/**
	 * Register the query var for the conversational form slug.
	 *
	 * @param $vars
	 * @return mixed|void
	 * @since 1.0
	 */
	public function add_slug_query_var( $vars ) {
		$vars[] = $this->query_var;
		return $vars;
	}

	/**
	 * Get the slug of the requested conversational form.
	 *
	 * @param $vars
	 * @return mixed|void
	 * @since 1.0
	 */
	public function get_requested_slug() {
		global $wp;

		if ( $this->is_plain_permalinks() && isset( $wp->query_vars[ $this->query_var ] ) ) {
			return strtolower( $wp->query_vars[ $this->query_var ] );
		} else {
			return strtolower( $wp->request );
		}
	}

	// # SCRIPT AND STYLE METHODS --------------------------------------------------------------------------------------------

	/**
	 * Add required scripts.
	 *
	 * @since 1.0
	 *
	 * @return array[]|mixed
	 */
	public function scripts() {
		$enqueue_condition = is_admin() ? array( 'admin_page' => array( 'form_settings', 'plugin_settings' ) ) : array( function() { return false; } );

		$scripts = array(
			array(
				'handle'    => 'gform_gfcf_vendor_admin_js',
				'src'       => $this->get_base_url() . '/assets/js/dist/vendor-admin.js',
				'version'   => $this->_version,
				'in_footer' => false,
				'enqueue'   => $enqueue_condition,
			),
			array(
				'handle'    => 'gform_gfcf_admin_js',
				'src'       => $this->get_base_url() . '/assets/js/dist/scripts-admin.js',
				'version'   => $this->_version,
				'in_footer' => false,
				'enqueue'   => $enqueue_condition,
			),
		);

		wp_enqueue_media();

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Add required styles.
	 *
	 * @since 1.0
	 *
	 * @return array[]|mixed
	 */
	public function styles() {
		$enqueue_condition = is_admin() ? array( 'admin_page' => array( 'form_settings', 'plugin_settings' ) ) : array( function() { return false; } );

		$styles = array(
			array(
				'handle'  => 'gfcf_admin_styles',
				'src'     => $this->get_base_url() . '/assets/css/dist/admin.css',
				'version' => $this->_version,
				'enqueue' => $enqueue_condition,
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	public function localize_admin_scripts() {
		$strings = array(
			'endpoints' => array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ),
		);
		wp_localize_script( 'gform_gfcf_admin_js', 'gfcf_admin_config', $strings );
	}

	public function localize_frontend_scripts( $form, $ajax ) {
		$strings = array(
			'endpoints' => array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ),
			'data'      => array(
				'animation_settings'     => array(
					'fields'     => array(
						'delay'         => 25,
						'distance_from' => 'calc(-50% + 50px)',
						'distance_to'   => 'calc(-50% + (var(--gform-convo-theme-nav-bar-placeholder) / 2))',
						'duration'      => 150,
						'easing'        => 'ease',
						'in_type'       => 'fadeIn translateY',
						'out_type'      => 'fadeOut',
					),
					'logoNavBar' => array(
						'distance_from' => 0,
						'distance_to'   => 0,
						'duration'      => 1200,
						'in_type'       => 'fadeIn',
					),
					'welcome'    => array(
						'in_distance_from' => '80px',
						'in_distance_to'   => '0px',
						'in_duration'      => 500,
						'in_type'          => 'fadeIn translateY',
						'out_distance_to'  => '0',
						'out_duration'     => 300,
						'out_type'         => 'fadeOut',
					),
					'enabled'    => true,
				),
				'is_conversational_form' => $this->is_conversational_form( $form ),
			),
			'i18n'      => array(
				'unknown_error'  => esc_html__( 'Unknown error. Please try again', 'gravityformsconversationalforms' ),
				'linebreaks_tip' => esc_html__( 'Use Shift + Enter to add line breaks', 'gravityformsconversationalforms' ),
				'empty_form'     => esc_html__( 'There was a problem with your submission. At least one field must be filled out.', 'gravityformsconversationalforms' ),
			),
		);

		/**
		 * Allows the conversational form's global JS data object to be modified.
		 *
		 * @since 1.0.0
		 *
		 * @param array $strings The global JS data object for the conversational form.
		 * @param int   $form_id The ID of the current form.
		 */
		$strings = gf_apply_filters( array( 'gform_gfcf_theme_config', $form['id'] ), $strings, $form['id'] );

		wp_localize_script( 'gform_conversational_scripts_theme', 'gfcf_theme_config', $strings );
	}

	/**
	 * Overrides the form theme slug returned for the conversational form.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function customize_form_theme_slug( $slug, $form ) {
		if ( \GFCommon::is_preview() ) {
		    return $slug;
		}

		global $wp;

		$full_screen_slug = rgars( $form, 'gf_theme_layers/form_full_screen_slug' );

		$current_slug = $this->get_requested_slug();

		if ( ! rgars( $form, 'gf_theme_layers/enable' ) || ( $current_slug != $full_screen_slug ) ) {
		    return $slug;
		}

		return 'orbital';
	}

	// # HELPER METHODS --------------------------------------------------------------------------------------------

	/**
	 * Check if the site is using plain permalinks.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_plain_permalinks() {
		return get_option( 'permalink_structure' ) == '';
	}

	/**
	 * When a matching slug is found for Front-End full-screen display,
	 * ensure that the Conversational Forms settings are enabled before
	 * rendering the template.
	 *
	 * @since 1.0
	 *
	 * @hook gform_full_screen_form_for_display 10 3
	 *
	 * @param $form_id
	 * @param $template
	 * @param $json_query
	 *
	 * @return int
	 */
	public function check_if_full_screen_enabled( $form_id, $template, $json_query ) {
		global $wp;

		$post_slug = $this->get_requested_slug();

		if ( empty( $post_slug ) ) {
			return null;
		}

		// Prevent empty values from hijacking the site homepage.
		if ( empty( $post_slug ) ) {
			return null;
		}

		$form_for_display = $json_query->query( $post_slug );

		if ( empty( $form_for_display ) ) {
			return $form_id;
		}

		$form = \GFFormsModel::get_form_meta( $form_for_display );

		if ( empty( $form['gf_theme_layers']['enable'] ) ) {
			return $form_id;
		}

		return $form_for_display;
	}

	/**
	 * Filter the full screen template path.
	 *
	 * @since 1.0
	 *
	 * @hook gform_full_screen_template_path 10 1
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function get_full_screen_template_path( $template ) {
		$new_template = dirname( __FILE__ ) . '/includes/theme-layers/layers/views/class-full-screen-template.php';

		return $new_template;
	}

	/**
	 * Retrieve the list of CSS Props from the Theme Layer for usage outside of the main
	 * output engines.
	 *
	 * @since 1.0
	 *
	 * @param       $form_id
	 * @param       $form
	 * @param false $selector
	 *
	 * @return mixed
	 */
	public static function conversational_style_css_props( $form_id, $form, $selector = false ) {
		$layers = \GFForms::get_service_container()->get( \Gravity_Forms\Gravity_Forms\Theme_Layers\GF_Theme_Layers_Provider::THEME_LAYERS );
		$layers  = array_filter( $layers, function ( $layer ) {
			return $layer->name() == 'gravityformsconversationalforms';
		} );

		$layer = array_shift( $layers );

		/**
		 * @var \Gravity_Forms\Gravity_Forms\Theme_Layers\Framework\Engines\Output_Engines\Form_CSS_Properties_Output_Engine $engine
		 */
		$engine    = $layer->output_engine_by_type( \Gravity_Forms\Gravity_Forms\Theme_Layers\Framework\Engines\Output_Engines\Form_CSS_Properties_Output_Engine::class );
		$css_props = $engine ? $engine->generate_props_block( $form_id, $form ) : '';

		if ( $selector ) {
			$css_props = preg_replace( '/<style>[^\{]+{/', '<style>' . $selector . ' {$1', $css_props );
		}

		return $css_props;
	}

	/**
	 * Helper method to get the URL for a given SVG.
	 *
	 * @since 1.0
	 *
	 * @param $name
	 *
	 * @return string
	 */
	private function get_svg_url( $name ) {
		return sprintf( '%s/assets/img/%s.svg', untrailingslashit( plugin_dir_url( __FILE__ ) ), $name );
	}

	/**
	 * Whether the current settings combine to enable Conversational Forms for a given instance
	 * of the form.
	 *
	 * @since 1.0
	 *
	 * @param $settings
	 *
	 * @return bool
	 */
	private function is_enabled( $settings ) {
		if ( ! isset( $settings['enable'] ) || ! $settings['enable'] ) {
			return false;
		}

		if ( ! $this->is_full_screen_page( $settings['form_full_screen_slug'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Helper method to determine if the current page/post matches the full-screen
	 * slug in the Form Settings.
	 *
	 * @since 1.0
	 *
	 * @param $full_screen_slug
	 *
	 * @return bool
	 */
	private function is_full_screen_page( $full_screen_slug ) {
		global $wp;

		$slug = $this->get_requested_slug();

		$slug = empty( $slug ) ? get_post_field( 'post_name', get_post() ) : $slug;

		return $slug == $full_screen_slug;
	}


	/**
	 * Helper method to get the relevant allowed file types for the file upload settings.
	 * Used to provide the supported file type helper text.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	private function get_file_upload_allowed_file_types() {
		// Allowed mime types from WordPress for the current user
		$allowed_mime_types = get_allowed_mime_types();

		// Allowed file types for the file upload settings
		$allowed_file_types = array( 'gif', 'jpg', 'jpeg', 'png' );

		// Add svg to the allowed file types helper text if allowed by WordPress
		if ( array_key_exists( 'svg', $allowed_mime_types ) || array_key_exists( 'svgz', $allowed_mime_types ) ) {
			$allowed_file_types[] = 'svg';
		}

		return $allowed_file_types;
	}

	public function sanitize_permalink( $field, $value ) {
		return sanitize_title( $value );
	}

	/**
	 * When a conversational form is duplicated, increment the slug to prevent duplicate permalinks.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id The form being duplicated.
	 * @param int $new_id  The new form.
	 *
	 * @return void
	 */
	public function prevent_duplicate_permalinks( $form_id, $new_id ) {
		$form_meta = \GFFormsModel::get_form_meta( $form_id );
		if ( rgars(  $form_meta, 'gf_theme_layers/form_full_screen_slug' ) ) {
			$full_screen_slug = $form_meta['gf_theme_layers']['form_full_screen_slug'];
			// if slug ends with '-' followed by a digit, increment the digit
			if ( preg_match( '/-\d+$/', $full_screen_slug ) ) {
				// if slug ends with -+digit, increment the digit
				$count = 1;
				$new_slug = $this->increment_slug( $full_screen_slug, $count );
				// If new slug is not unique, increment the count until a unique slug is created.
				while ( ! $this->is_unique_slug( $new_slug ) ) {
					$count++;
					$new_slug = $this->increment_slug( $full_screen_slug, $count );
				}
			} else {
				$count = 1;
				$new_slug = $full_screen_slug . '-' . $count;
				// If new slug is not unique, increment the count until a unique slug is created.
				while ( ! $this->is_unique_slug( $new_slug ) ) {
					$count++;
					$new_slug = $full_screen_slug . '-' . $count;
				}

			}

			$new_meta = \GFFormsModel::get_form_meta( $new_id );

			$new_meta['gf_theme_layers']['form_full_screen_slug'] = $new_slug;
			\GFFormsModel::update_form_meta( $new_id, $new_meta );
		}
	}

	/**
	 * Increase the number at the end of a slug.
	 *
	 * @since 1.0
	 *
	 * @param string $slug  Slug to increment
	 * @param int    $count Current count
	 *
	 * @return array|string|string[]|null
	 */
	public function increment_slug( $slug, $count ) {
		return preg_replace_callback( '/\d+$/', function( $matches ) use ( &$count ) {
			return $matches[0] + $count;
		}, $slug );
	}

	/**
	 * Check if another form is already using this slug.
	 *
	 * @since 1.0
	 *
	 * @param string $slug The slug to check.
	 *
	 * @return bool True if the slug is unique.
	 */
	public function is_unique_slug( $slug ) {
		$forms = \GFFormsModel::get_forms();
		foreach ( $forms as $form ) {
			$form_meta = \GFFormsModel::get_form_meta( $form->id );
			if ( rgars(  $form_meta, 'gf_theme_layers/form_full_screen_slug' ) && $form_meta['gf_theme_layers']['form_full_screen_slug'] == $slug ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Make sure WordPress doesn't give a post the same URL as a conversational form.
	 *
	 * @since 1.0.0
	 *
	 * @param $bad_slug
	 * @param $slug
	 * @param $post_type
	 *
	 * @return bool Whether the slug is bad.
	 */
	public function is_bad_flat_slug( $bad_slug, $slug, $post_type ) {
		return ! $this->is_unique_slug( $slug );
	}

	/**
	 * Make sure WordPress doesn't give a page the same URL as a conversational form.
	 *
	 * @since 1.0.0
	 *
	 * @param $bad_slug
	 * @param $slug
	 * @param $post_type
	 * @param $parent_post_id
	 *
	 * @return bool Whether the slug is bad.
	 */
	public function is_bad_hierarchical_slug( $bad_slug, $slug, $post_type, $parent_post_id ) {
		return ! $this->is_unique_slug( $slug );
	}

	/**
	 * Defines the various setting fields to display on the Form Settings screen for this theme layer.
	 *
	 * @since 1.0
	 *
	 * @return array[]
	 */
	public function theme_layer_settings_fields() {
		$file_upload_allowed_file_types = $this->get_file_upload_allowed_file_types();
		$form_id                        = rgget( 'id' );
		return array(
			'conversational_forms_general' => array(
				'title'       => __( 'Conversational Forms', 'gravityformsconversationalforms' ),
				'description' => $this->maybe_show_legacy_message( $form_id ),
				'fields'      => array(

					// Whether to enable the Conversational Form functionality for this form.
					// Note: all fields below are conditional on this being enabled.
					array(
						'name'          => 'enable',
						'label'         => __( 'Enable Conversational Page For Form', 'gravityformsconversationalforms' ),
						'description'   => __( 'This form will continue to display as normal when embedded anywhere on your site, but will display in distraction-free conversational mode at this URL.', 'gravityformsconversationalforms' ),
						'type'          => 'toggle',
						'default_value' => false,
						'disabled'      => GFCommon::is_legacy_markup_enabled( $form_id ),
						''
					),

					// The slug used to display the form as Full Screen.
					array(
						'name'                      => 'form_full_screen_slug',
						'label'                     => __( 'Permalink', 'gravityformsconversationalforms' ),
						'type'                      => 'permalink',
						'input_prefix'              => trailingslashit( site_url() ) . ( $this->is_plain_permalinks() ? '?' . $this->query_var . '=' : '' ),
						'action_button'             => true,
						'action_button_icon'        => 'external-link',
						'action_button_icon_prefix' => 'gform-icon',
						'action_button_text'        => __( 'View Form', 'gravityformsconversationalforms' ),
						'required'                  => true,
						'save_callback'             => array( $this, 'sanitize_permalink' ),
						'dependency'                => array(
							'live'   => true,
							'fields' => array(
								array(
									'field'  => 'enable',
									'values' => array( true, '1' ),
								),
							),
						),
					),
				),
			),

			'conversational_forms_design' => array(
				'title'       => __( 'Form Design', 'gravityformsconversationalforms' ),
				'fields'       => array(

					// The layout to use for the page (left, right, or full-width).
					array(
						'name'          => 'page_layout',
						'label'         => __( 'Layout', 'gravityformsconversationalforms' ),
						'type'          => 'radio',
						'image_select'  => true,
						'default_value' => 'center',
						'choices'       => array(
							array(
								'value' => 'left',
								'label' => __( 'Left Aligned', 'gravityformsconversationalforms' ),
								'icon'  => $this->get_svg_url( 'left-aligned' ),
							),
							array(
								'value' => 'center',
								'label' => __( 'Center', 'gravityformsconversationalforms' ),
								'icon'  => $this->get_svg_url( 'full-width' ),
							),
							array(
								'value' => 'right',
								'label' => __( 'Right Aligned', 'gravityformsconversationalforms' ),
								'icon'  => $this->get_svg_url( 'right-aligned' ),
							),
						),
					),

					// The background color to use for the form.
					array(
						'name'          => 'background_color',
						'label'         => __( 'Background Color', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(
							'#161616',
							'#fdfdff',
							'#204CE5',
							'#719e76',
							'#6868a9',
							'#e5a133',
							'#f5cb3c',
						),
						'allow_new'     => true,
						'default_value' => '#fdfdff',
					),

					// The color to use for form field accents.
					array(
						'name'          => 'accent_color',
						'label'         => __( 'Form Accent Color', 'gravityformsconversationalforms' ),
						'description'   => __( 'Used for various form elements, such as buttons and progress bars.', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(
							'#161616',
							'#204CE5',
							'#719e76',
							'#6868a9',
							'#e5a133',
							'#f5cb3c',
						),
						'allow_new'     => true,
						'default_value' => '#204CE5',
					),

					// The color to use for form field control accents.
					array(
						'name'          => 'accent_color_control',
						'label'         => __( 'Input Accent Color', 'gravityformsconversationalforms' ),
						'description'   => __( 'Used for aspects of individual form inputs, such as checkmarks and dropdown choices.', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(
							'#161616',
							'#204CE5',
							'#719e76',
							'#6868a9',
							'#e5a133',
							'#f5cb3c',
						),
						'allow_new'     => true,
						'default_value' => '#204CE5',
					),

					// The color to use for text.
					array(
						'name'          => 'text_color',
						'label'         => __( 'Text Color', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(
							'#161616',
							'#fdfdff',
						),
						'allow_new'     => true,
						'default_value' => '#161616',
					),

					// Whether to display a logo.
					// Note: all fields below are conditional on this being enabled.
					array(
						'name'          => 'enable_logo',
						'label'         => __( 'Enable Logo', 'gravityformsconversationalforms' ),
						'type'          => 'toggle',
						'default_value' => false,
					),

					// Defines an image to use as a logo on the intro screen and confirmation page.
					array(
						'name'          => 'logo',
						'label'         => __( 'Logo', 'gravityformsconversationalforms' ),
						'type'          => 'file_upload',
						'allowed_types' => $file_upload_allowed_file_types,
						'max_width'     => '140',
						'max_height'    => '80',
						'dependency'    => array(
							'live'   => true,
							'fields'  => array(
								array(
									'field'   => 'enable_logo',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// Defines an optional link to use for the logo on the intro screen and confirmation page.
					array(
						'name'          => 'logo_link',
						'label'         => __( 'Logo Link', 'gravityformsconversationalforms' ),
						'type'          => 'text',
						'dependency'    => array(
							'live'   => true,
							'fields'  => array(
								array(
									'field'   => 'enable_logo',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// Whether to customize the background settings for the form.
					// Note: all fields below are conditional on this being enabled.
					array(
						'name'          => 'enable_background_image_settings',
						'label'         => __( 'Enable Background Image', 'gravityformsconversationalforms' ),
						'type'          => 'toggle',
						'default_value' => false,
					),

					// An optional background image to use for the form.
					array(
						'name'          => 'background_image',
						'label'         => __( 'Background Image', 'gravityformsconversationalforms' ),
						'allowed_types' => $file_upload_allowed_file_types,
						'type'          => 'file_upload',
						'max_width'     => '3456',
						'max_height'    => '2234',
						'dependency'    => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_background_image_settings',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// Controls the opacity of a background overlay
					// 0% is fully-black, 100% is full brightness/no overlay.
					array(
						'name'                 => 'background_image_overlay_brightness',
						'label'                => __( 'Background Image Brightness', 'gravityformsconversationalforms' ),
						'description'          => __( 'Adjust the brightness of your background image to ensure your form elements are legible when displayed against it. (Lower values result in a darker background).', 'gravityformsconversationalforms' ),
						'type'                 => 'range',
						'min'                  => 0,
						'max'                  => 100,
						'step'                 => 1,
						'show_value'           => true,
						'value_suffix'          => '%',
						'value_input_position' => 'after',
						'default_value'        => 50,
						'dependency'           => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_background_image_settings',
									'values' => array( true, '1' ),
								),
							),
						),
					),
				),
				'dependency'   => array(
					'live'  => true,
					'fields' => array(
						array(
							'field'   => 'enable',
							'values' => array( true, '1' ),
						),
					),
				),
			),

			'conversational_forms_welcome_screen' => array(
				'title'       => __( 'Welcome Screen', 'gravityformsconversationalforms' ),
				'fields'       => array(

					// Whether to display a form welcome screen.
					// Note: all fields below are conditional on this being enabled.
					array(
						'name'          => 'enable_welcome_screen',
						'label'         => __( 'Enable Welcome Screen', 'gravityformsconversationalforms' ),
						'type'          => 'toggle',
						'default_value' => false,
					),

					// An optional title to display on the welcome screen.
					array(
						'name'       => 'welcome_screen_title',
						'label'      => __( 'Heading', 'gravityformsconversationalforms' ),
						'type'       => 'text',
						'dependency' => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_welcome_screen',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// An optional message to display on the welcome screen.
					array(
						'name'       => 'welcome_screen_message',
						'label'      => __( 'Description', 'gravityformsconversationalforms' ),
						'type'       => 'textarea',
						'dependency' => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_welcome_screen',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// The text to display for the "get started" button on the welcome screen.
					array(
						'name'          => 'welcome_screen_button_text',
						'label'         => __( 'Start Button Text', 'gravityformsconversationalforms' ),
						'required'      => true,
						'type'          => 'text',
						'default_value' => __( 'Start', 'gravityformsconversationalforms' ),
						'dependency'    => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_welcome_screen',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// An optional inline image to display on the welcome screen.
					array(
						'name'          => 'welcome_screen_image',
						'label'         => __( 'Image', 'gravityformsconversationalforms' ),
						'type'          => 'file_upload',
						'allowed_types' => $file_upload_allowed_file_types,
						'max_width'     => '1910',
						'max_height'    => '525',
						'dependency'    => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_welcome_screen',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// An optional alternate text to use for the inline image on the welcome screen.
					array(
						'name'       => 'welcome_screen_image_alt_text',
						'label'      => __( 'Image Alternate Text', 'gravityformsconversationalforms' ),
						'type'       => 'text',
						'required'   => false,
						'dependency' => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_welcome_screen',
									'values' => array( true, '1' ),
								),
							),
						),
					),
				),
				'dependency'    => array(
					'live'  => true,
					'fields' => array(
						array(
							'field'   => 'enable',
							'values' => array( true, '1' ),
						),
					),
				),
			),

			'conversational_forms_form_text_settings' => array(
				'title'      => __( 'Buttons', 'gravityformsconversationalforms' ),
				'fields'     => array(
					array(
						'name'          => 'continue_button_text',
						'label'         => __( 'Continue Button Text', 'gravityformsconversationalforms' ),
						'type'          => 'text',
						'required'      => true,
						'default_value' => esc_html__( 'Continue', 'gravityformsconversationalforms' ),
					),
				),
				'dependency' => array(
					'live'   => true,
					'fields' => array(
						array(
							'field'  => 'enable',
							'values' => array( true, '1' ),
						),
					),
				),
			),

			'conversational_forms_form_navigation' => array(
				'title'       => __( 'Footer', 'gravityformsconversationalforms' ),
				'fields'       => array(

					// The background color to use for the form navigation.
					array(
						'name'          => 'navigation_background_color',
						'label'         => __( 'Footer Background Color', 'gravityformsconversationalforms' ),
						'description'   => __( 'By default, this color is generated from the form accent color selected above. Use this setting if you want to override the automatically-generated color.', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(),
						'allow_new'     => true,
						'default_value' => '',
					),

					// Whether to display a progress bar or simple pagination for the form.
					// Note: all fields below are conditional on this being enabled.
					array(
						'name'          => 'enable_progress_bar',
						'label'         => __( 'Enable Progress Bar', 'gravityformsconversationalforms' ),
						'type'          => 'toggle',
						'default_value' => false,
					),

					// The style of progress bar label - either percentage (0%, etc) or proportion (1/5, 2/5, etc).
					array(
						'name'          => 'progress_bar_progression_type',
						'label'         => __( 'Progression Type', 'gravityformsconversationalforms' ),
						'default_value' => 'percentage',
						'type'          => 'radio',
						'horizontal'    => true,
						'choices'       => array(
							array(
								'value' => 'percentage',
								'label' => __( 'Percentage', 'gravityformsconversationalforms' ),
							),
							array(
								'value' => 'proportion',
								'label' => __( 'Proportion', 'gravityformsconversationalforms' ),
							),
						),
						'dependency'    => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_progress_bar',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// The background color to use for the progress bar. Overrides auto gen.
					array(
						'name'          => 'progress_bar_background_color',
						'label'         => __( 'Bar Background Color', 'gravityformsconversationalforms' ),
						'description'   => __( 'By default, this color is generated from the form accent color selected above. Use this setting if you want to override the automatically-generated color.', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(),
						'allow_new'     => true,
						'default_value' => '',
						'dependency'    => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_progress_bar',
									'values' => array( true, '1' ),
								),
							),
						),
					),

					// The foreground color to use for the progress bar. Overrides auto gen.
					array(
						'name'          => 'progress_bar_foreground_color',
						'label'         => __( 'Bar Foreground Color', 'gravityformsconversationalforms' ),
						'description'   => __( 'By default, this color is generated from the form accent color selected above. Use this setting if you want to override the automatically-generated color.', 'gravityformsconversationalforms' ),
						'type'          => 'swatch',
						'palette'       => array(),
						'allow_new'     => true,
						'default_value' => '',
						'dependency'    => array(
							'live'  => true,
							'fields' => array(
								array(
									'field'   => 'enable_progress_bar',
									'values' => array( true, '1' ),
								),
							),
						),
					),
				),
				'dependency'   => array(
					'live'  => true,
					'fields' => array(
						array(
							'field'   => 'enable',
							'values' => array( true, '1' ),
						),
					),
				),
			),
		);
	}

	/**
	 * Show an error message if the form is using legacy markup.
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 *
	 * @return string
	 */
	public function maybe_show_legacy_message( $form_id ) {
		if ( GFCommon::is_legacy_markup_enabled( $form_id ) ) {
			$message = esc_html__( 'This form is using legacy markup, which is not supported in Conversational Forms.  Please turn off Legacy Markup in the form settings to enable a Conversational Page for this form.', 'gravityformsconversationalforms' );
			return "<div class='gform-alert gform-alert--error'>
				<span class='gform-alert__icon gform-icon gform-icon--circle-error-fine' aria-hidden='true'></span>
				<div class='gform-alert__message-wrap'>{$message}</div>
				</div>";
		}

		return '';
	}

	/**
	 * The fields/views to override for this theme layer.
	 *
	 * @since 1.0
	 *
	 * @return string[]
	 */
	public function theme_layer_overridden_fields() {
		return array(
			'form' => Form_View::class,
			'all'  => Conversational_Field_View::class,
		);
	}

	/**
	 * The form CSS properties to output based on settings, block settings, or arbitrary conditions.
	 *
	 * These styles are output as a style block both at the top of every form wrapper, as well as
	 * at the top of the Full Screen template.
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 * @param $settings
	 * @param $block_settings
	 *
	 * @return array|null[]
	 */
	public function theme_layer_form_css_properties( $form_id, $settings, $block_settings ) {
		if ( ! $this->is_enabled( $settings ) ) {
			return array();
		}

		$color_modifier = new Colors\Color_Modifier();

		// Form Accent Color Handling
		$is_accent_dark          = $this->is_dark_color( $settings['accent_color'] );
		$accent_color_rgb        = $color_modifier->convert_hex_to_rgb( $settings['accent_color'] );
		$accent_color_presets    = $is_accent_dark ? array(
			// palette shade
			array( 's' => -0.03, 'l' => - 0.06 ),
			// --gform-convo-theme-progress-bar-background-color
			array( 's' => 0.21, 'l' => 0.37 ),
			// --gform-convo-theme-nav-background-color
			array( 's' => 0.21, 'l' => 0.455 ),
		) : array(
			// palette shade
			array( 's' => -0.03, 'l' => - 0.06 ),
			// --gform-convo-theme-progress-bar-background-color
			array( 's' => 0.21, 'l' => 0.37 ),
			// --gform-convo-theme-nav-background-color
			array( 's' => -0.5, 'l' => -0.4 ),
		);
		$accent_color_variations = $color_modifier->make_variations_from_rgb(
			$accent_color_rgb['r'],
			$accent_color_rgb['g'],
			$accent_color_rgb['b'],
			$accent_color_presets
		);

		// Input Accent Color Handling
		$accent_color_control            = isset( $settings['accent_color_control'] ) ? $settings['accent_color_control'] : $settings['accent_color'];
		$is_accent_control_dark          = $this->is_dark_color( $accent_color_control );
		$accent_color_control_rgb        = $color_modifier->convert_hex_to_rgb( $accent_color_control );
		$accent_color_control_presets    = array(
			// palette shade
			array( 's' => -0.03, 'l' => - 0.06 ),
		);
		$accent_color_control_variations = $color_modifier->make_variations_from_rgb(
			$accent_color_control_rgb['r'],
			$accent_color_control_rgb['g'],
			$accent_color_control_rgb['b'],
			$accent_color_control_presets
		);

		$color_palette = array(
			'primary'                => array(
				'color'              => $settings['accent_color'],
				'color-rgb'          => $this->darken_color( $settings['accent_color'], 0, 'rgb' ),
				'color-contrast'     => $is_accent_dark ? '#fdfdff' : '#161616',
				'color-contrast-rgb' => $is_accent_dark ? array( '253', '253', '255' ) : array( '22', '22', '22' ),
				'color-darker'       => $color_modifier->convert_rgb_to_hex( round( $accent_color_variations[0]['r'] ), round( $accent_color_variations[0]['g'] ), round( $accent_color_variations[0]['b'] ) ),
			),
			'inside-control-primary' => array(
				'color'              => $accent_color_control,
				'color-rgb'          => self::darken_color( $accent_color_control, 0, 'rgb' ),
				'color-contrast'     => $is_accent_control_dark ? '#fdfdff' : '#161616',
				'color-contrast-rgb' => $is_accent_control_dark ? array( '253', '253', '255' ) : array( '22', '22', '22' ),
				'color-darker'       => $color_modifier->convert_rgb_to_hex( round( $accent_color_control_variations[0]['r'] ), round( $accent_color_control_variations[0]['g'] ), round( $accent_color_control_variations[0]['b'] ) ),
			),
		);

		// text vars
		$description_text_rgb   = $color_modifier->convert_hex_to_rgb( $settings['text_color'] );
		$description_text_color = sprintf( 'rgba(%s, %s, %s, 0.8)', $description_text_rgb['r'], $description_text_rgb['g'], $description_text_rgb['b'] );

		// nav vars
		$nav_background_color_initial = ! empty( $settings['navigation_background_color'] )
			? $settings['navigation_background_color']
			: $color_modifier->convert_rgb_to_hex( round( $accent_color_variations[2]['r'] ), round( $accent_color_variations[2]['g'] ), round( $accent_color_variations[2]['b'] ) );

		$nav_background_color_rgb = $color_modifier->convert_hex_to_rgb( $nav_background_color_initial );
		$nav_background_color = sprintf( 'rgba(%s, %s, %s, %s)', $nav_background_color_rgb['r'], $nav_background_color_rgb['g'], $nav_background_color_rgb['b'], 0.9 );

		$progress_bar_label_color      = $this->is_dark_color( $nav_background_color_initial ) ? '#fdfdff' : '#161616';
		$progress_bar_background_color = ! empty( $settings['progress_bar_background_color'] )
			? $settings['progress_bar_background_color']
			: $color_modifier->convert_rgb_to_hex( round( $accent_color_variations[1]['r'] ), round( $accent_color_variations[1]['g'] ), round( $accent_color_variations[1]['b'] ) );
		$progress_bar_foreground_color = ! empty( $settings['progress_bar_foreground_color'] )
			? $settings['progress_bar_foreground_color']
			: $color_palette['primary']['color'];

		return array(
			/*
			 * Global CSS API | Gravity Forms
			 */

			// Global CSS API: Theme
			'gform-theme-color-primary'              => $color_palette['primary']['color'],
			'gform-theme-color-primary-rgb'          => implode( ', ', $color_palette['primary']['color-rgb'] ),
			'gform-theme-color-primary-contrast'     => $color_palette['primary']['color-contrast'],
			'gform-theme-color-primary-contrast-rgb' => implode( ', ', $color_palette['primary']['color-contrast-rgb'] ),
			'gform-theme-color-primary-darker'       => $color_palette['primary']['color-darker'],

			'gform-theme-color-inside-control-primary'              => $color_palette['inside-control-primary']['color'],
			'gform-theme-color-inside-control-primary-rgb'          => implode( ', ', $color_palette['inside-control-primary']['color-rgb'] ),
			'gform-theme-color-inside-control-primary-contrast'     => $color_palette['inside-control-primary']['color-contrast'],
			'gform-theme-color-inside-control-primary-contrast-rgb' => implode( ', ', $color_palette['inside-control-primary']['color-contrast-rgb'] ),
			'gform-theme-color-inside-control-primary-darker'       => $color_palette['inside-control-primary']['color-darker'],

			// Global CSS API | Control Colors
			'gform-theme-control-readonly-color'         => $settings['text_color'],
			'gform-theme-control-label-color-primary'    => $settings['text_color'],
			'gform-theme-control-label-color-secondary'  => $settings['text_color'],
			'gform-theme-control-label-color-tertiary'   => $description_text_color,
			'gform-theme-control-description-color'      => $description_text_color,
			'gform-theme-control-label-color-quaternary' => $description_text_color,

			/*
			 * Global CSS API | Conversational Forms Add-On
			 */

			// Global CSS API | Conversational Forms Add-On: Theme
			'gform-convo-theme-color-text'           => $settings['text_color'],
			'gform-convo-theme-color-text-secondary' => $description_text_color,

			// Global CSS API | Conversational Forms Add-On: Base
			'gform-convo-theme-background-color' => $settings['background_color'],

			// Global CSS API | Conversational Forms Add-On: Form - Navigation
			'gform-convo-theme-nav-background-color' => $nav_background_color,

			// Global CSS API | Conversational Forms Add-On: Form - Progress Bar
			'gform-convo-theme-progress-bar-label-color'               => $progress_bar_label_color,
			'gform-convo-theme-progress-bar-background-color'          => $progress_bar_background_color,
			'gform-convo-theme-progress-bar-background-color-progress' => $progress_bar_foreground_color,
		);
	}

	/**
	 * An array of styles to enqueue.
	 *
	 * @since 1.0
	 *
	 * @param $form
	 * @param $ajax
	 * @param $settings
	 * @param $block_settings
	 *
	 * @return array
	 */
	public function theme_layer_styles( $form, $ajax, $settings, $block_settings = array() ) {
		if ( ! $this->is_enabled( $settings ) ) {
			return array();
		}

		$base_url = plugins_url( '', __FILE__ );

		return array(
			'foundation' => array(
				array(
					'gravity_forms_conversational_foundation',
					"$base_url/assets/css/dist/theme-foundation.css"
				),
			),
			'framework' => array(
				array(
					'gravity_forms_conversational_theme',
					"$base_url/assets/css/dist/theme-framework.css"
				),
			),
		);
	}

	/**
	 * An array of scripts to enqueue.
	 *
	 * @since 1.0
	 *
	 * @param $form
	 * @param $ajax
	 * @param $settings
	 * @param $block_settings
	 *
	 * @return array
	 */
	public function theme_layer_scripts( $form, $ajax, $settings, $block_settings = array() ) {
		if ( ! $this->is_enabled( $settings ) ) {
			return array();
		}

		return array(
			array(
				'gform_conversational_scripts_theme',
				plugin_dir_url( __FILE__ ) . 'assets/js/dist/scripts-theme.js',
			),
			array(
				'gform_conversational_vendor_theme',
				plugin_dir_url( __FILE__ ) . 'assets/js/dist/vendor-theme.js',
			),
		);
	}

	/**
	 * Field validation AJAX handler.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function validate_field() {

		$form_id  = absint( rgpost( 'form_id' ) );
		$field_id = absint( rgpost( 'field_id' ) );

		\GFFormsModel::set_uploaded_files( $form_id );

		$result = \GFAPI::validate_field( $form_id, $field_id );

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_code() == 'not_supported' ? '' : $result->get_error_message();
		} elseif ( ! $result['is_valid'] ) {
			$error_message = ! empty( $result['message'] ) ? $result['message'] : esc_html__( 'Please enter a valid value.', 'gravityformsconversationalforms' );
		} else {
			$error_message = '';
		}

		wp_send_json_success( $error_message );
	}

	/**
	 * The icon to use for displaying on settings pages, etc.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function theme_layer_icon() {
		return 'gform-icon--conversational';
	}

	public function get_menu_icon() {
		return $this->theme_layer_icon();
	}

	/**
	 * Filter form actions to add additional actions.
	 *
	 * @since 1.0
	 *
	 * @param array  $form_actions Form actions to display for the form.
	 * @param string $form_id      The form ID.
	 *
	 * @return array The filtered form actions.
	 */
	public function filter_form_actions( $form_actions, $form_id ) {
		// Return early if on trash page.
		if ( rgget( 'filter' ) === 'trash' ) {
			return $form_actions;
		}

		// Make sure form ID exists.
		if ( empty( $form_id ) ) {
			return $form_actions;
		}

		$form = \GFFormsModel::get_form_meta( $form_id );

		// Return early if conversational forms is not enabled.
		if ( empty( $form['gf_theme_layers']['enable'] ) ) {
			return $form_actions;
		}

		// Return early if full screen slug is empty.
		if ( empty( $form['gf_theme_layers']['form_full_screen_slug'] ) ) {
			return $form_actions;
		}

		// Add conversational preview before duplicate.
		$form_actions['view_conversational'] = array(
			'label'        => esc_html__( 'View Conversational Form', 'gravityforms' ),
			'aria-label'   => esc_html__( 'View this conversational form', 'gravityforms' ),
			'url'          => trailingslashit( site_url() ) . $form['gf_theme_layers']['form_full_screen_slug'],
			'menu_class'   => 'gf_form_toolbar_view_conversational',
			'capabilities' => 'gravityforms_preview_forms',
			'target'       => '_blank',
			'priority'     => 650,
		);

		return $form_actions;
	}

	/**
	 * In forms with pages, the target page should always be 0 because pages don't matter.
	 *
	 * @since 1.0
	 *
	 * @param $target_page
	 * @param $form
	 *
	 * @return int
	 */
	public function filter_target_page( $target_page, $form ) {
		if ( $this->is_conversational_form( $form ) ) {
			return 0;
		}
		return $target_page;
	}

	/**
	 * Filter and customize the form based on conversational form requirements.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form array.
	 *
	 * @return array
	 */
	public function customize_form_settings( $form ) {
		if ( $this->is_conversational_form( $form ) ) {

			// Disable animated transitions because they interfere with conversational animations.
			$form['enableAnimation'] = 0;

			// Set the label placement to top_label if the form is a conversational form.
			$form['labelPlacement'] = 'top_label';
		}

		// Set a key to use as a conditional based on whether or not this is a conversational form.
		$form['is_conversational_form'] = $this->is_conversational_form( $form );

		return $form;
	}

	/**
	 * Detect if a color is dark against a passed threshold. Default is set at 465 in the range of 1 - 765.
	 *
	 * @since 1.0
	 *
	 * @param string $color     The color string to test, as a hex code (either 3 or 6 digits).
	 * @param float  $threshold The threshold to return true at in a range of 1 - 765.
	 *
	 * @return bool
	 */
	public function is_dark_color( $color = '', $threshold = 465 ) {
		$color_modifier = new Colors\Color_Modifier();
		$hex_color      = $color_modifier->sanitize_color_string( $color );

		return hexdec( substr( $hex_color, 0, 2 ) ) + hexdec( substr( $hex_color, 2, 2 ) ) + hexdec( substr( $hex_color, 4, 2 ) ) < $threshold;
	}

	/**
	 * Darken a given color string by a specific amount.
	 *
	 * @since 1.0
	 *
	 * @param string $color         The color string to modify, as a hex code (either 3 or 6 digits).
	 * @param float  $darken_amount The amount by which to modify the color, in steps.
	 * @param string $format        The format in which to return the color (hex or rgb)
	 *
	 * @return mixed
	 */
	public function darken_color( $color, $darken_amount, $format = 'hex' ) {
		$color_modifier = new Colors\Color_Modifier();

		if ( $darken_amount > 0 ) {
			$darken_amount *= -1;
		}

		return $color_modifier->modify( $color, $darken_amount, $format );
	}

	/**
	 * Check if the current form view is a conversational form.
	 *
	 * @since 1.0
	 *
	 * @param array $form The form array
	 *
	 * @return bool True if the current form view is a conversational form.
	 */
	public function is_conversational_form( $form ) {
		global $wp;

		$slug = $this->get_requested_slug();

		if ( ! empty( $form['gf_theme_layers']['enable'] ) &&
		     ! empty( $form['gf_theme_layers']['form_full_screen_slug'] ) &&
		     $form['gf_theme_layers']['form_full_screen_slug'] === $slug ) {

			return true;

		}

		return false;

	}
}
