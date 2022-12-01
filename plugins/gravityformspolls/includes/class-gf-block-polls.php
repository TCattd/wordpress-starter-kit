<?php

// If Gravity Forms Block is not available, do not run.
if ( ! class_exists( 'GF_Block' ) || ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Polls Block.
 *
 * @since 3.3
 *
 * Class GF_Block_Polls
 */
class GF_Block_Polls extends GF_Block {

	/**
	 * Contains an instance of this block, if available.
	 *
	 * @since  3.3
	 * @access private
	 * @var    GF_Block_Polls $_instance If available, contains an instance of this block.
	 */
	private static $_instance = null;

	/**
	 * Block type.
	 *
	 * @var string
	 */
	public $type = 'gravityforms/polls';

	/**
	 * Handle of primary block script.
	 *
	 * @var string
	 */
	public $script_handle = 'gform_editor_block_polls';

	/**
	 * Block attributes.
	 *
	 * @var array
	 */
	public $attributes = array(
		'formId'                     => array( 'type' => 'integer' ),
		'mode'                       => array( 'type' => 'string' ),
		'title'                      => array( 'type' => 'boolean' ),
		'description'                => array( 'type' => 'boolean' ),
		'ajax'                       => array( 'type' => 'boolean' ),
		'tabindex'                   => array( 'type' => 'integer' ),
		'formPreview'                => array( 'type' => 'boolean' ),
		'style'                      => array( 'type' => 'string' ),
		'cookie'                     => array( 'type' => 'string' ),
		'cookieDate'                 => array( 'type' => 'string' ),
		'displayCounts'              => array( 'type' => 'boolean' ),
		'displayResultsLink'         => array( 'type' => 'boolean' ),
		'displayResultsOnSubmission' => array( 'type' => 'boolean' ),
		'displayPercentages'         => array( 'type' => 'boolean' ),
	);

	/**
	 * Get instance of this class.
	 *
	 * @since  3.3
	 * @static
	 *
	 * @return GF_Block
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Register needed hooks.
	 *
	 * @since 3.3
	 */
	public function init() {

		parent::init();

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_form_scripts' ) );

	}





	// # SCRIPT / STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Register scripts for block.
	 *
	 * @since  3.3
	 *
	 * @return array
	 */
	public function scripts() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		return array(
			array(
				'handle'    => $this->script_handle,
				'src'       => gf_polls()->get_base_url() . "/js/blocks{$min}.js",
				'deps'      => array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor' ),
				'version'   => $min ? gf_polls()->get_version() : filemtime( gf_polls()->get_base_path() . '/js/blocks.js' ),
				'callback'  => array( $this, 'localize_script' ),
				'in_footer' => true,
			),
		);

	}

	/**
	 * Localize core block script.
	 *
	 * @since  3.3
	 *
	 * @param array $script Script arguments.
	 */
	public function localize_script( $script = array() ) {

		wp_localize_script(
			$script['handle'],
			'gform_block_polls',
			array(
				'forms' => $this->get_forms(),
			)
		);

	}

	/**
	 * Register styles for block.
	 *
	 * @since  3.3
	 *
	 * @return array
	 */
	public function styles() {

		// Prepare styling dependencies.
		$deps = array( 'wp-edit-blocks' );

		// Add Gravity Forms styling if CSS is enabled.
		if ( '1' !== get_option( 'rg_gforms_disable_css', false ) ) {
			$deps = array_merge( $deps, array( 'gforms_formsmain_css', 'gforms_ready_class_css', 'gforms_browsers_css' ) );
		}

		return array(
			array(
				'handle'  => 'gpoll_css',
				'src'     => gf_polls()->get_base_url() . '/css/gpoll.css',
				'deps'    => $deps,
				'version' => gf_polls()->_version,
			),
			array(
				'handle'  => 'gform_editor_block_poll',
				'src'     => gf_polls()->get_base_url() . '/css/blocks.min.css',
				'deps'    => array( 'gpoll_css' ),
				'version' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? filemtime( gf_polls()->get_base_path() . '/css/blocks.min.css' ) : gf_polls()->_version,
			),
		);

	}

	/**
	 * Parse current post's blocks for Gravity Forms Polls block and enqueue required form scripts.
	 *
	 * @since  3.3
	 * @access public
	 */
	public function maybe_enqueue_form_scripts() {

		global $wp_query;

		if ( ! isset( $wp_query->posts ) || ! is_array( $wp_query->posts ) ) {
			return;
		}

		foreach ( $wp_query->posts as $post ) {

			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$blocks = parse_blocks( $post->post_content );

			foreach ( $blocks as $block ) {
				if ( $block['blockName'] == $this->type && rgars( $block, 'attrs/formId' ) ) {
					require_once( GFCommon::get_base_path() . '/form_display.php' );
					$form = GFAPI::get_form( rgars( $block, 'attrs/formId' ) );
					GFFormDisplay::enqueue_form_scripts( $form, rgars( $block, 'attrs/ajax' ) );
				}
			}

		}

	}





	// # BLOCK RENDER --------------------------------------------------------------------------------------------------

	/**
	 * Display block contents on frontend.
	 *
	 * @since  3.3
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string|null
	 */
	public function render_block( $attributes = array() ) {

		// Prepare attributes.
		$form_id     = rgar( $attributes, 'formId' ) ? $attributes['formId'] : false;
		$title       = isset( $attributes['title'] ) ? $attributes['title'] : true;
		$description = isset( $attributes['description'] ) ? $attributes['description'] : true;
		$ajax        = isset( $attributes['ajax'] ) ? $attributes['ajax'] : false;
		$tabindex    = isset( $attributes['tabindex'] ) ? $attributes['tabindex'] : 0;

		$mode                       = 'results' === rgar( $attributes, 'mode' ) ? 'results' : 'poll';
		$style                      = rgar( $attributes, 'style' ) ? $attributes['style'] : 'green';
		$cookie                     = 'date' === rgar( $attributes, 'cookie' ) ? ( rgar( $attributes, 'cookieDate' ) ? $attributes['cookieDate'] : '' )  : rgar( $attributes, 'cookie' );
		$display_counts             = isset( $attributes['displayCounts'] ) ? $attributes['displayCounts'] : true;
		$display_results_link       = isset( $attributes['displayResultsLink'] ) ? $attributes['displayResultsLink'] : true;
		$display_results_submission = isset( $attributes['displayResultsOnSubmission'] ) ? $attributes['displayResultsOnSubmission'] : true;
		$display_percentages        = isset( $attributes['displayPercentages'] ) ? $attributes['displayPercentages'] : true;

		// If form ID was not provided or form does not exist, return.
		if ( ! $form_id || ( $form_id && ! GFAPI::get_form( $form_id ) ) ) {
			return '';
		}

		// Prepare preview for editor.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && rgget( 'context' ) === 'edit' ) {
			return gf_polls()->build_poll_ui( $form_id, 0, $style, $mode, $display_percentages, $display_counts, $title, $description, true, $display_results_link, $ajax, $cookie, $display_results_submission, '', true, $tabindex, true );
		}

		return sprintf(
			'[gravityform action="polls" id="%d" title="%s" description="%s" ajax="%s" disable_scripts="%s" tabindex="%d" mode="%s" style="%s" cookie="%s" counts="%s" show_results_link="%s" display_results="%s" percentages="%s"]',
			$attributes['formId'], // Form ID
			$title ? 'true' : 'false', // Display Title
			$description ? 'true' : 'false', // Display Description
			$ajax ? 'true' : 'false', // Enable AJAX
			( ( is_admin() && 'edit' === rgget( 'action' ) ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ? 'true' : 'false', // Disable Scripts
			$tabindex, // Tabindex
			$mode, // Display Mode
			$style, // Style
			$cookie, // Cookie
			$display_counts ? 'true' : 'false', // Display Counts
			$display_results_link ? 'true' : 'false', // Display Results Link
			$display_results_submission ? 'true' : 'false', // Display Results On Submission
			$display_percentages ? 'true' : 'false' // Display Percentages
		);

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get list of forms for Block control.
	 *
	 * @since  3.3
	 *
	 * @return array
	 */
	public function get_forms() {

		// Initialize forms array.
		$forms = array();

		// Load GFFormDisplay class.
		if ( ! class_exists( 'GFFormDisplay' ) ) {
			require_once GFCommon::get_base_path() . '/form_display.php';
		}

		// Get form objects.
		$form_objects = GFAPI::get_forms();

		// Loop through forms, add form if has Poll fields.
		foreach ( $form_objects as $form ) {

			// If form does not have Poll fields, skip.
			if ( ! GFAPI::get_fields_by_type( $form, 'poll' ) ) {
				continue;
			}

			// Add form to array.
			$forms[] = array(
				'id'                  => $form['id'],
				'title'               => $form['title'],
				'hasConditionalLogic' => GFFormDisplay::has_conditional_logic( $form ),
			);

		}

		return $forms;

	}

}

// Register block.
if ( true !== ( $registered = GF_Blocks::register( GF_Block_Polls::get_instance() ) ) && is_wp_error( $registered ) ) {

	// Log that block could not be registered.
	gf_polls()->log_error( 'Unable to register block; ' . $registered->get_error_message() );

}
