<?php

// example usage of gpoll_widget_override filter to allow widgets to override form settings
/*
add_filter('gpoll_widget_override' , 'gpoll_widget_override');

function gpoll_widget_override($form_ids){
     array_push($form_ids, 7, 9);
     return $form_ids;
}
*/


// The wp_cron job will take over the calculation of the results if 5 seconds is not long enough at the time of form submission/update.
// The default schedule is set to hourly to avoid heavy loads on the server but you can change this frequency by using the gform_polls_cron_schedule.
// The format follows the format required by the WordPress cron_schedules filter. http://codex.wordpress.org/Function_Reference/wp_get_schedules
// Important: the Polls Add-On must be deactivated and reactivated in order to reschedule the task.
/*
add_filter( 'gform_polls_cron_schedule', 'gform_polls_cron_add_twice_hourly' );
function gform_polls_cron_add_twice_hourly($schedules) {
    // Adds twice hourly to the existing schedules.
    $schedules['twicehourly'] = array(
        'interval' => 1800, // number of seconds in the interval
        'display' => 'Twice Hourly'
    );
    return $schedules;
}
*/

// By default the percentage will round to the nearest whole number
// This can be overridden with the gform_polls_percentage_precision hook

/*
add_filter('gform_polls_percentage_precision', 'gform_polls_custom_precision', 10, 2);
function gform_polls_custom_precision($precision, $form_id){
    return 1;
}
*/


//------------------------------------------

defined( 'ABSPATH' ) || die();

GFForms::include_addon_framework();

require_once( 'pollwidget.php' );

register_deactivation_hook( __FILE__, array( 'GFPolls', 'remove_wp_cron_task' ) );

class GFPolls extends GFAddOn {

	private static $_instance = null;

	public $_version = GF_POLLS_VERSION;
	protected $_min_gravityforms_version = '1.9.15.12';
	protected $_slug = 'gravityformspolls';
	protected $_path = 'gravityformspolls/polls.php';
	protected $_full_path = __FILE__;
	protected $_url = 'http://www.gravityforms.com';
	protected $_title = 'Gravity Forms Polls Add-On';
	protected $_short_title = 'Polls';
	protected $_enable_rg_autoupgrade = true;
	private $_form_meta_by_id = array();
	public $gpoll_add_scripts;

	/**
	 * Members plugin integration
	 */
	protected $_capabilities = array(
		'gravityforms_polls',
		'gravityforms_polls_uninstall',
		'gravityforms_polls_results',
		'gravityforms_polls_settings',
		'gravityforms_polls_form_settings'
	);

	/**
	 * Permissions
	 */
	protected $_capabilities_settings_page = 'gravityforms_polls_settings';
	protected $_capabilities_form_settings = 'gravityforms_polls_form_settings';
	protected $_capabilities_uninstall = 'gravityforms_polls_uninstall';

	/**
	 * Get an instance of this class.
	 *
	 * @return GFPolls
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFPolls();
		}

		return self::$_instance;
	}

	private function __clone() {
	} /* do nothing */

	/**
	 * Handles anything which requires early initialization.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-gf-field-poll.php' );

			add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
			add_filter( 'gform_export_field_value', array( $this, 'display_entries_field_value' ), 10, 4 );
			add_action( 'gform_polls_cron', array( $this, 'wp_cron_task' ) );
		}
	}

	/**
	 * Handles hooks and loading of language files.
	 *
	 * @since 3.3 Register Polls block.
	 */
	public function init() {

		// add a special class to poll fields so we can identify them later
		add_action( 'gform_field_css_class', array( $this, 'add_custom_class' ), 10, 3 );

		if ( $this->get_custom_cron_schedule() ) {
			add_filter( 'cron_schedules', array( $this, 'cron_add_custom_schedule' ) );
		}

		// add wp_cron job if it's not already scheduled
		if ( ! wp_next_scheduled( 'gform_polls_cron' ) ) {
			wp_schedule_event( time(), $this->get_cron_recurrence(), 'gform_polls_cron' );
		}

		add_filter( 'gform_shortcode_polls', array( $this, 'poll_shortcode' ), 10, 3 );

		add_filter( 'gform_pre_render', array( $this, 'pre_render' ) );

		// shuffle choices if configured
		add_filter( 'gform_field_content', array( $this, 'render_poll_field_content' ), 10, 5 );

		add_action( 'gform_validation', array( $this, 'form_validation' ) );

		// update the cache
		add_action( 'gform_entry_created', array( $this, 'entry_created' ), 10, 2 );

		// maybe display results on confirmation
		add_filter( 'gform_confirmation', array( $this, 'display_confirmation' ), 10, 4 );

		add_shortcode( 'gfpolls_total', array( $this, 'poll_total_shortcode' ) );

		// merge tags
		add_filter( 'gform_replace_merge_tags', array( $this, 'render_merge_tag' ), 10, 7 );

		add_filter( 'gform_entry_field_value', array( $this, 'display_poll_on_entry_detail' ), 10, 4 );

		// Integration with the feed add-ons as of GF 1.9.15.12; for add-ons which don't override get_field_value().
		add_filter( 'gform_addon_field_value', array( $this, 'addon_field_value' ), 10, 5 );

		// AWeber 2.3 and newer use the gform_addon_field_value hook, only use the gform_aweber_field_value hook with older versions.
		if ( defined( 'GF_AWEBER_VERSION' ) && version_compare( GF_AWEBER_VERSION, '2.3', '<' ) ) {
			add_filter( 'gform_aweber_field_value', array( $this, 'display_entries_field_value' ), 10, 4 );
		}

		// Mailchimp Add-On integration
		add_filter( 'gform_mailchimp_field_value', array( $this, 'display_entries_field_value' ), 10, 4 );

		// Campaign Monitor Add-On integration
		add_filter( 'gform_campaignmonitor_field_value', array( $this, 'display_entries_field_value' ), 10, 4 );

		// Zapier Add-On integration
		add_filter( 'gform_zapier_field_value', array( $this, 'display_entries_field_value' ), 10, 4 );

		// Register Polls block.
		if ( class_exists( 'GF_Blocks' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . '/includes/class-gf-block-polls.php' );
		}

		parent::init();
	}

	/**
	 * Initialize the admin specific hooks.
	 */
	public function init_admin() {

		//form editor
		add_action( 'gform_field_standard_settings', array( $this, 'poll_field_settings' ), 10, 2 );
		add_filter( 'gform_tooltips', array( $this, 'add_poll_field_tooltips' ) );
		add_action( 'gform_after_save_form', array( $this, 'after_save_form' ), 10, 2 );

		//display poll results on entry list
		add_filter( 'gform_entries_field_value', array( $this, 'display_entries_field_value' ), 10, 4 );

		//update the cache
		add_action( 'gform_after_update_entry', array( $this, 'entry_updated' ), 10, 2 );
		add_action( 'gform_update_status', array( $this, 'update_entry_status' ), 10, 2 );

		// contacts
		add_filter( 'gform_contacts_tabs_contact_detail', array( $this, 'add_tab_to_contact_detail' ), 10, 2 );
		add_action( 'gform_contacts_tab_polls', array( $this, 'contacts_tab' ) );

		// Adds the polls action to the shortcode builder UI
		add_filter( 'gform_shortcode_builder_actions', array( $this, 'add_polls_shortcode_ui_action' ) );

		parent::init_admin();
	}

	/**
	 * Initialize the AJAX hooks.
	 */
	public function init_ajax() {

		add_action( 'wp_ajax_gpoll_ajax', array( $this, 'gpoll_ajax' ) );
		add_action( 'wp_ajax_nopriv_gpoll_ajax', array( $this, 'gpoll_ajax' ) );

		parent::init_ajax();
	}

	/**
	 * The Polls add-on does not support logging.
	 *
	 * @param array $plugins The plugins which support logging.
	 *
	 * @return array
	 */
	public function set_logging_supported( $plugins ) {

		return $plugins;

	}

	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'  => 'gpoll_form_editor_js',
				'src'     => $this->get_base_url() . "/js/gpoll_form_editor{$min}.js",
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'strings' => array(
					'firstChoice'  => wp_strip_all_tags( __( 'First Choice', 'gravityformspolls' ) ),
					'secondChoice' => wp_strip_all_tags( __( 'Second Choice', 'gravityformspolls' ) ),
					'thirdChoice'  => wp_strip_all_tags( __( 'Third Choice', 'gravityformspolls' ) ),
				),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),
			array(
				'handle'  => 'gpoll_form_settings_js',
				'src'     => $this->get_base_url() . "/js/gpoll_form_settings{$min}.js",
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_settings' ),
						'tab'        => 'gravityformspolls',
					),
				),
			),
			array(
				'handle'   => 'gpoll_js',
				'src'      => $this->get_base_url() . "/js/gpoll{$min}.js",
				'version'  => $this->_version,
				'deps'     => array( 'jquery' ),
				'callback' => array( $this, 'localize_scripts' ),
				'enqueue'  => array(
					array( 'field_types' => array( 'poll' ) ),
				),
			),
		);

		$merge_tags = $this->get_merge_tags();

		if ( ! empty( $merge_tags ) ) {
			$scripts[] = array(
				'handle'  => 'gpoll_merge_tags',
				'src'     => $this->get_base_url() . "/js/gpoll_merge_tags{$min}.js",
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_settings' ) ),
				),
				'strings' => array(
					'merge_tags' => $merge_tags,
				),
			);
		}

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'  => 'gpoll_form_editor_css',
				'src'     => $this->get_base_url() . "/css/gpoll_form_editor{$min}.css",
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				)
			),
			array(
				'handle'  => 'gpoll_css',
				'src'     => $this->get_base_url() . "/css/gpoll{$min}.css",
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'poll' ) ),
					array( 'admin_page' => array( 'form_editor', 'results', 'entry_view', 'entry_detail' ) ),
				)
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * Localize the strings used by the scripts.
	 */
	public function localize_scripts() {

		// Get current page protocol
		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
		// Output admin-ajax.php URL with same protocol as current page
		$params = array(
			'ajaxurl'   => admin_url( 'admin-ajax.php', $protocol ),
			'imagesUrl' => $this->get_base_url() . '/images'
		);
		wp_localize_script( 'gpoll_js', 'gpollVars', $params );

		//localisable strings for the js file
		$strings = array(
			'viewResults'   => esc_html__( 'View results', 'gravityformspolls' ),
			'backToThePoll' => esc_html__( 'Back to the poll', 'gravityformspolls' )

		);
		wp_localize_script( 'gpoll_js', 'gpoll_strings', $strings );

	}


	// # RESULTS --------------------------------------------------------------------------------------------------------

	/**
	 * Configure the survey results page.
	 *
	 * @return array
	 */
	public function get_results_page_config() {
		return array(
			'title'        => esc_html__( 'Poll Results', 'gravityformspolls' ),
			'capabilities' => array( 'gravityforms_polls_results' ),
			'callbacks'    => array(
				'fields' => array( $this, 'results_fields' )
			)
		);
	}

	/**
	 * Get all the poll fields for the current form.
	 *
	 * @param array $form The current form object.
	 *
	 * @return GF_Field[]
	 */
	public function results_fields( $form ) {
		return GFAPI::get_fields_by_type( $form, array( 'poll' ) );
	}


	// # MERGE TAGS -----------------------------------------------------------------------------------------------------

	/**
	 * Add the result merge tags to the merge tag drop downs in the admin.
	 *
	 * @deprecated 3.8 Use GFPolls::get_merge_tags();
	 *
	 * @param array $form The current form.
	 *
	 * @return array
	 */
	public function add_merge_tags( $form ) {
		_deprecated_function( __METHOD__, '3.8', 'GFPolls::get_merge_tags()' );
		return $form;
	}

	/**
	 * Get the merge tags to add to the merge tag drop downs in the admin.
	 *
	 * @return array
	 */
	private function get_merge_tags() {
		$form = $this->get_current_form();

		if ( ! $form ) {
			return array();
		}

		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );

		if ( empty( $poll_fields ) ) {
			return array();
		}

		$merge_tags = array();

		foreach ( $poll_fields as $field ) {
			$field_id     = $field->id;
			$field_label  = $field->label;
			$group        = $field->isRequired ? 'required' : 'optional';
			$merge_tags[] = array(
				'group' => $group,
				'label' => $field_label . esc_html__( ': Poll Results', 'gravityformspolls' ),
				'tag'   => "{gpoll:field={$field_id}}",
			);
		}

		$merge_tags[] = array(
			'group' => 'other',
			'label' => esc_html__( 'All Poll Results', 'gravityformspolls' ),
			'tag'   => '{all_poll_results}',
		);

		return $merge_tags;
	}

	/**
	 * Replace the result merge tags.
	 *
	 * @param string $text The current text in which merge tags are being replaced.
	 * @param array $form The current form object.
	 * @param array $entry The current entry object.
	 * @param bool $url_encode Whether or not to encode any URLs found in the replaced value.
	 * @param bool $esc_html Whether or not to encode HTML found in the replaced value.
	 * @param bool $nl2br Whether or not to convert newlines to break tags.
	 * @param string $format The format requested for the location the merge is being used. Possible values: html, text or url.
	 *
	 * @return string
	 */
	public function render_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		if ( empty( $entry ) || empty( $form ) ) {
			return $text;
		}

		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( empty ( $poll_fields ) ) {
			return $text;
		}

		$enqueue_scripts = false;
		$form_id         = $form['id'];

		preg_match_all( "/{all_poll_results(:(.*?))?}/", $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {

			$full_tag       = $match[0];
			$options_string = isset( $match[2] ) ? $match[2] : '';
			$options        = shortcode_parse_atts( $options_string );

			extract(
				shortcode_atts(
					array(
						'field'       => 0,
						'style'       => 'green',
						'percentages' => 'true',
						'counts'      => 'true',
					), $options
				)
			);
			$percentages     = strtolower( $percentages ) == 'false' ? false : true;
			$counts          = strtolower( $counts ) == 'false' ? false : true;
			$results         = $this->gpoll_get_results( $form_id, $field, $style, $percentages, $counts, $entry );
			$results_summary = $results['summary'];
			$new_value       = $results_summary;

			$text = str_replace( $full_tag, $new_value, $text );

			$enqueue_scripts = true;
		}

		preg_match_all( "/\{gpoll:(.*?)\}/", $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$full_tag = $match[0];

			$options_string = isset( $match[1] ) ? $match[1] : '';
			$options        = shortcode_parse_atts( $options_string );

			extract(
				shortcode_atts(
					array(
						'field'       => 0,
						'style'       => 'green',
						'percentages' => 'true',
						'counts'      => 'true',
					), $options
				)
			);

			$percentages     = strtolower( $percentages ) == 'false' ? false : true;
			$counts          = strtolower( $counts ) == 'false' ? false : true;
			$results         = $this->gpoll_get_results( $form_id, $field, $style, $percentages, $counts, $entry );
			$results_summary = $results['summary'];
			$new_value       = $results_summary;

			$text = str_replace( $full_tag, $new_value, $text );

			$enqueue_scripts = true;

		}

		if ( $enqueue_scripts ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'gpoll_css' );
			wp_enqueue_style( 'gpoll_form_editor_css' );
			wp_enqueue_script( 'gpoll_js' );
			$this->localize_scripts();
		}

		return $text;

	}

	// # FORM RENDER & SUBMISSION ---------------------------------------------------------------------------------------

	/**
	 * Helper for retrieving a form setting.
	 *
	 * @param array $form The current form object.
	 * @param string $setting_name The property to be retrieved.
	 *
	 * @return bool|string
	 */
	public function get_form_setting( $form, $setting_name ) {
		if ( false === empty( $form ) ) {
			$settings = $this->get_form_settings( $form );

			// check for legacy form settings from a form exported from a previous version pre-framework
			if ( empty( $settings ) && isset( $form['gpollDisplayResults'] ) ) {
				$this->upgrade_form_settings( $form );
			}

			if ( isset( $settings[ $setting_name ] ) ) {
				$setting_value = $settings[ $setting_name ];
				if ( $setting_value == '1' ) {
					$setting_value = true;
				} elseif ( $setting_value == '0' ) {
					$setting_value = false;
				}

				return $setting_value;
			}
		}

		$setting_value = '';
		//default values
		switch ( $setting_name ) {
			case 'displayResults' :
			case 'showResultsLink' :
			case 'showPercentages' :
			case 'showCounts' :
				$setting_value = true;
				break;
			case 'blockRepeatVoters' :
				$setting_value = false;
				break;
			case 'style' :
				$setting_value = 'green';
				break;
			case 'cookie' :
				$setting_value = '1 month';
				break;
		}

		return $setting_value;
	}

	/**
	 * Updates the form object with the appropriate css classes and if necessary configures the select placeholder.
	 *
	 * @param array $form The for currently being processed for display.
	 *
	 * @return array
	 */
	public function pre_render( $form ) {

		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( ! empty ( $poll_fields ) ) {
			$form_css          = 'gpoll_enabled';
			$show_results_link = $this->get_form_setting( $form, 'showResultsLink' );

			if ( $show_results_link ) {
				$form_css .= ' gpoll_show_results_link';
			}

			$block_repeat_voters = $this->get_form_setting( $form, 'blockRepeatVoters' );

			if ( $block_repeat_voters && rgget( 'gf_page' ) != 'preview' ) {
				$form_css .= ' gpoll_block_repeat_voters';
			}

			$form['cssClass'] = empty( $form['cssClass'] ) ? $form_css . ' gpoll' : $form_css . ' ' . $form['cssClass'];

			foreach ( $form['fields'] as &$field ) {
				if ( $field->type != 'poll' ) {
					continue;
				}

				if ( $field->get_input_type() == 'select' && empty( $field->placeholder ) ) {
					$field->placeholder = esc_html__( 'Select one', 'gravityformspolls' );
				}
			}
		}

		return $form;
	}


	/**
	 * If necessary perform validation to prevent repeat voting.
	 *
	 * @param array $validation_result Contains the validation result, the form object, and the failed validation page number.
	 *
	 * @return array
	 */
	public function form_validation( $validation_result ) {
		$form        = $validation_result['form'];
		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( empty ( $poll_fields ) ) {
			return $validation_result;
		}
		$form_setting_block_repeat = $this->get_form_setting( $form, 'blockRepeatVoters' );
		$field_values              = wp_parse_args( rgpost( 'gform_field_values' ) );
		if ( $form_setting_block_repeat || ( isset( $field_values['gpoll_enabled'] ) && $field_values['gpoll_enabled'] == '1' && isset( $field_values['gpoll_cookie'] ) && false === empty( $field_values['gpoll_cookie'] ) ) ) {
			$form_id = rgar( $form, 'id' );
			if ( isset ( $_COOKIE[ 'gpoll_form_' . $form_id ] ) ) {
				// set the form validation to false
				$validation_result['is_valid'] = false;
				foreach ( $form['fields'] as &$field ) {
					if ( $field->type == 'poll' ) {
						$field->failed_validation  = true;
						$field->validation_message = esc_html__( 'Repeat voting is not allowed', 'gravityformspolls' );
					}
				}
				$validation_result['form'] = $form;
			}
		}

		return $validation_result;
	}

	/**
	 * If necessary randomize the field choices.
	 *
	 * @param string $content The field content to be filtered.
	 * @param GF_Field $field The field currently being processed for display.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param int $entry_id The ID of the entry if the field is being displayed on the entry detail page.
	 * @param int $form_id The ID of the current form.
	 *
	 * @return string
	 */
	public function render_poll_field_content( $content, $field, $value, $entry_id, $form_id ) {

		if (
			$this->is_form_editor()
			|| $entry_id !== 0
			|| $field->type !== 'poll'
			|| ! $this->should_randomize_choices( $entry_id, $field )
		) {
			return $content;
		}

		// Pass the HTML for the choices through DOMDocument to make sure we get the complete node.
		$dom     = new DOMDocument();
		$content = '<?xml version="1.0" encoding="UTF-8"?>' . $content;
		// Clean content from new line characters.
		$content = str_replace( '&#13;', ' ', $content );
		$content = trim( preg_replace( '/\s\s+/', ' ', $content ) );
		$loader  = libxml_disable_entity_loader( true );
		$errors  = libxml_use_internal_errors( true );
		$dom->loadHTML( $content );
		libxml_clear_errors();
		libxml_use_internal_errors( $errors );
		libxml_disable_entity_loader( $loader );
		$content = $dom->saveXML( $dom->documentElement );

		$nodes = $this->get_choices_to_randomize( $dom, $form_id, $field );

		// Cycle through the answers elements and reorder them randomly.
		$temp_str1 = 'gpoll_shuffle_placeholder1';
		$temp_str2 = 'gpoll_shuffle_placeholder2';
		for ( $i = $nodes->length - 1; $i >= 0; $i -- ) {
			$n = rand( 0, $i );
			if ( $i <> $n ) {
				$i_str = $dom->saveXML( $nodes->item( $i ) );
				$n_str = $dom->saveXML( $nodes->item( $n ) );
				// Make sure we are not shuffling any of the following:
				// select all option for checkboxes, select one placeholder for dropdown or other for radio buttons.
				$no_shuffle_strings = array( 'gchoice_select_all', 'gf_placeholder', 'gf_other_choice' );
				if ( str_replace( $no_shuffle_strings, '', $i_str ) !== $i_str ||
					str_replace( $no_shuffle_strings, '', $n_str ) !== $n_str ) {
					continue;
				}

				$content = str_replace( $i_str, $temp_str1, $content );
				$content = str_replace( $n_str, $temp_str2, $content );
				$content = str_replace( $temp_str2, $i_str, $content );
				$content = str_replace( $temp_str1, $n_str, $content );
			}
		}

		// Snip off the tags that DOMdocument adds.
		$content = str_replace( '<html><body>', '', $content );
		$content = str_replace( '</body></html>', '', $content );


		return $content;
	}

	/**
	 * Check if field choices should be randomized.
	 *
	 * @since 3.8.1
	 *
	 * @param int    $lead_id The Lead ID.
	 * @param object $field   GF Field Object.
	 *
	 * @return bool If choices should be randomized.
	 */
	private function should_randomize_choices( $lead_id, $field ) {
		return (
			! $this->is_form_editor()
			&& ! rgpost( 'action' ) // Don't randomize if we have just changed an option in the form editor.
			&& $lead_id === 0
			&& $field->type == 'poll'
			&& $field->enableRandomizeChoices
		);
	}

	/**
	 * Extract the choices from the field markup so that we can randomize them.
	 *
	 * @since 3.8.1
	 *
	 * @param object $dom   DOMDocument of the field markup
	 * @param int $form_id  Form ID
	 * @param object $field Field object
	 *
	 * @return object Nodes comprising the field choices that need to be randomized.
	 */
	private function get_choices_to_randomize( $dom, $form_id, $field ) {
		$is_legacy = method_exists( 'GFCommon', 'is_legacy_markup_enabled' ) ? GFCommon::is_legacy_markup_enabled( $form_id ) : true;
		$element   = $is_legacy ? 'li' : 'div';

		if ( 'select' === $field->inputType ) {
			return $dom->getElementsByTagName( 'select' )->item( 0 )->childNodes;
		} else {
			$xpath = new DOMXpath( $dom );
			return $xpath->query( "//" . $element . "[contains(@class,'gchoice')]" );
		}
	}

	/**
	 * If necessary update the confirmation to include the results.
	 *
	 * @param string|array $confirmation The forms current confirmation.
	 * @param array $form The form currently being processed.
	 * @param array $lead The entry currently being processed.
	 * @param bool $ajax Indicates if AJAX is enabled for this form.
	 *
	 * @return string|array
	 */
	public function display_confirmation( $confirmation, $form, $lead, $ajax ) {
		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );

		if ( empty ( $poll_fields ) ) {
			return $confirmation;
		}

		$form_id              = $form['id'];
		$display_confirmation = false;
		$display_results      = false;
		$override             = false;

		$field_values = array();
		if ( isset( $_POST['gform_field_values'] ) ) {
			$field_values = wp_parse_args( $_POST['gform_field_values'] );
		}

		// shortcode attributes override form settings
		if ( rgar( $field_values, 'gpoll_enabled' ) == '1' ) {

			$field_values      = wp_parse_args( $_POST['gform_field_values'] );
			$show_results_link = rgar( $field_values, 'gpoll_show_results_link' );
			$show_results_link = $show_results_link == '1' ? true : false;
			$style             = rgar( $field_values, 'gpoll_style' );
			$percentages       = rgar( $field_values, 'gpoll_percentages' );
			$percentages       = $percentages == '1' ? true : false;
			$counts            = rgar( $field_values, 'gpoll_counts' );
			$counts            = $counts == '1' ? true : false;
			$cookie            = rgar( $field_values, 'gpoll_cookie' );

			$display_results = rgar( $field_values, 'gpoll_display_results' );
			$display_results = $display_results == '1' ? true : false;

			$display_confirmation = rgar( $field_values, 'gpoll_confirmation' );
			$display_confirmation = $display_confirmation == '1' ? true : false;

			$checksum = rgar( $field_values, 'gpoll_checksum' );
			if ( $checksum == $this->generate_checksum( $display_results, $show_results_link, $cookie, $display_confirmation, $percentages, $counts, $style ) ) {
				$override = true;
			}

		}


		if ( false === $override ) {
			$style       = $this->get_form_setting( $form, 'style' );
			$percentages = $this->get_form_setting( $form, 'showPercentages' );
			$counts      = $this->get_form_setting( $form, 'showCounts' );

			$display_results      = $this->get_form_setting( $form, 'displayResults' );
			$display_confirmation = true;
		}

		$submitted_fields = array();
		foreach ( $poll_fields as $field ) {
			$field_id    = $field->id;
			$entry_value = RGFormsModel::get_lead_field_value( $lead, $field );
			if ( is_array( $entry_value ) ) {
				$entry_value = implode( '', $entry_value );
			}
			if ( false === empty( $entry_value ) ) {
				$submitted_fields[] = $field_id;
			}
		}

		if ( $display_confirmation && $display_results ) {
			//confirmation message plus results

			//override in the case of headers already sent or ajax = true
			if ( is_array( $confirmation ) && array_key_exists( 'redirect', $confirmation ) ) {
				$confirmation = '';
			}

			//override confirmation if it's a redirect
			$str_pos = strpos( $confirmation, 'gformRedirect' );
			if ( false !== $str_pos ) {
				$confirmation = '';
			}

			$has_confirmation_wrapper = false !== strpos( $confirmation, 'gform_confirmation_wrapper' ) ? true : false;

			if ( $has_confirmation_wrapper ) {
				$confirmation = substr( $confirmation, 0, strlen( $confirmation ) - 6 );
			} //remove the closing div of the wrapper

			$has_confirmation_message = false !== strpos( $confirmation, 'gform_confirmation_message' ) ? true : false;

			if ( $has_confirmation_message ) {
				$confirmation = substr( $confirmation, 0, strlen( $confirmation ) - 6 );
			} //remove the closing div of the message
			else {
				$confirmation .= "<div id='gforms_confirmation_message' class='gform_confirmation_message_{$form_id}'>";
			}

			$results = $this->gpoll_get_results( $form['id'], $submitted_fields, $style, $percentages, $counts, $lead );
			$confirmation .= $results['summary'] . '</div>';

			if ( $has_confirmation_wrapper ) {
				$confirmation .= '</div>';
			}

		} elseif ( ! $display_confirmation && $display_results ) {

			//only the results without the confirmation message
			$results = $this->gpoll_get_results( $form['id'], $submitted_fields, $style, $percentages, $counts, $lead );

			$results_summary = $results['summary'];
			$confirmation    = sprintf( "<div id='gforms_confirmation_message' class='gform_confirmation_message_{$form_id}'>%s</div>", $results_summary );

		} elseif ( ! $display_confirmation && ! $display_results ) {
			$confirmation = "<div id='gforms_confirmation_message' class='gform_confirmation_message_{$form_id}'></div>";
		}

		return $confirmation;
	} // end function gpoll_confirmation

	/**
	 * If necessary set a cookie to block repeat submissions.
	 *
	 * @param array $entry The current entry object.
	 * @param array $form The current form object.
	 */
	public function after_submission( $entry, $form ) {
		if ( rgget( 'gf_page' ) == 'preview' ) {
			return;
		}

		$set_cookie   = false;
		$cookie       = '';
		$field_values = array();
		if ( isset( $_POST['gform_field_values'] ) ) {
			$field_values = wp_parse_args( $_POST['gform_field_values'] );
		}

		$override = false;

		if ( rgar( $field_values, 'gpoll_enabled' ) == '1' ) {
			$show_results_link = rgar( $field_values, 'gpoll_show_results_link' );
			$show_results_link = $show_results_link == '1' ? true : false;
			$style             = rgar( $field_values, 'gpoll_style' );
			$percentages       = rgar( $field_values, 'gpoll_percentages' );
			$percentages       = $percentages == '1' ? true : false;
			$counts            = rgar( $field_values, 'gpoll_counts' );
			$counts            = $counts == '1' ? true : false;
			$cookie            = $field_values['gpoll_cookie'];

			$display_results = rgar( $field_values, 'gpoll_display_results' );
			$display_results = $display_results == '1' ? true : false;

			$display_confirmation = rgar( $field_values, 'gpoll_confirmation' );
			$display_confirmation = $display_confirmation == '1' ? true : false;

			$checksum = rgar( $field_values, 'gpoll_checksum' );
			if ( $checksum == $this->generate_checksum( $display_results, $show_results_link, $cookie, $display_confirmation, $percentages, $counts, $style ) ) {
				$set_cookie = true;
				$override   = true;
			}
		}

		if ( false === $override ) {
			if ( $this->get_form_setting( $form, 'blockRepeatVoters' ) ) {
				$set_cookie = true;
				$cookie     = $this->get_form_setting( $form, 'cookie' );
			}
		}

		if ( $set_cookie ) {
			$form_id    = $form['id'];
			$lead_id    = $entry['id'];
			$server_tz  = date_default_timezone_get();
			$browser_tz = rgar( $_COOKIE, 'gpoll-timezone' ); // in hours
			if ( false === empty( $browser_tz ) ) {
				date_default_timezone_set( $browser_tz );
			}
			$cookie_expiration = strtotime( $cookie );
			date_default_timezone_set( $server_tz );
			setcookie( 'gpoll_form_' . $form_id, $lead_id, $cookie_expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
	}


	// # ENTRY RELATED --------------------------------------------------------------------------------------------------

	/**
	 * If the field is a Poll type radio, select or checkbox then replace the choice value with the choice text.
	 *
	 * @param string $value The field value.
	 * @param GF_Field|null $field The field object being processed or null.
	 *
	 * @return string
	 */
	public function maybe_format_field_values( $value, $field ) {

		if ( is_object( $field ) && $field->type == 'poll' ) {
			switch ( $field->inputType ) {
				case 'radio' :
				case 'select' :
					return RGFormsModel::get_choice_text( $field, $value );

				case 'checkbox' :
					if ( is_array( $value ) ) {
						foreach ( $value as &$choice ) {
							if ( ! empty( $choice ) ) {
								$choice = RGFormsModel::get_choice_text( $field, $choice );
							}
						}
					} else {
						foreach ( $field->choices as $choice ) {
							$val   = rgar( $choice, 'value' );
							$text  = rgar( $choice, 'text' );
							$value = str_replace( $val, $text, $value );
						}
					}
			}
		}

		return $value;
	}

	/**
	 * Format the Poll field values so they use the choice text instead of values before being passed to the third-party.
	 *
	 * @param string $value The field value.
	 * @param array $form The form currently being processed.
	 * @param array $entry The entry object currently being processed.
	 * @param string $field_id The ID of the field currently being processed.
	 *
	 * @return string
	 */
	public function addon_field_value( $value, $form, $entry, $field_id, $slug ) {
		if ( ! empty( $value ) ) {
			$field = RGFormsModel::get_field( $form, $field_id );

			return $this->maybe_format_field_values( $value, $field );
		}

		return $value;
	}

	/**
	 * Format the Poll field values so they use the choice text instead of values.
	 *
	 * Used for the entry list page, the AWeber, Campaign Monitor, and MailChimp add-ons.
	 *
	 * @param string|array $value The field value.
	 * @param int $form_id The ID of the form currently being processed.
	 * @param string $field_id The ID of the field currently being processed.
	 * @param array $entry The entry object currently being processed.
	 *
	 * @return string|array
	 */
	public function display_entries_field_value( $value, $form_id, $field_id, $entry ) {
		if ( ! empty( $value ) ) {
			global $_form_metas;

			if ( ! isset( $_form_metas[ $form_id ] ) ) {
				$_form_metas[ $form_id ] = RGFormsModel::get_form_meta( $form_id );
			}

			$form_meta = $_form_metas[ $form_id ];

			$field = RGFormsModel::get_field( $form_meta, $field_id );

			return $this->maybe_format_field_values( $value, $field );
		}

		return $value;
	}

	/**
	 * Format the Poll field values for display on the entry detail page and print entry.
	 *
	 * @since unknown
	 *
	 * @param string|array $value The field value.
	 * @param GF_Field $field The field currently being processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return string|array
	 */
	public function display_poll_on_entry_detail( $value, $field, $entry, $form ) {

		if ( $field instanceof GF_Field && $field->type == 'poll' ) {
			if ( $field->is_entry_detail() ) {
				$results                 = $this->gpoll_get_results( $form['id'], $field->id, 'green', true, true, $entry );
				$new_value               = sprintf( '<div class="gpoll_entry">%s</div>', rgar( $results, 'summary' ) );
				$this->gpoll_add_scripts = true;

				//if original response is not in results display below
				$selected_values  = $this->get_selected_values( $form, $field->id, $entry );
				$possible_choices = $this->get_possible_choices( $form, $field->id );
				foreach ( $selected_values as $selected_value ) {
					if ( ! in_array( $selected_value, $possible_choices ) ) {
						$escaped_value = $this->get_escaped_original_answer_value( $field, $value, $selected_values );
						$new_value = sprintf( '%s<h2>%s</h2>%s', $new_value, esc_html__( 'Original Response', 'gravityformspolls' ), $escaped_value );
						break;
					}
				}

				return $new_value;
			} elseif ( is_array( $field->choices ) ) {
				if ( $field->inputType == 'checkbox' ) {
					foreach ( $field->choices as $choice ) {
						$val   = rgar( $choice, 'value' );
						$text  = rgar( $choice, 'text' );
						$value = str_replace( $val, $text, $value );
					}
				} else {
					$value = RGFormsModel::get_choice_text( $field, $value );
				}
			}
		}

		return $value;
	}

	/**
	 * Get the properly-escaped value to display in the Original Answer section in an Entry.
	 *
	 * @since 3.7
	 *
	 * @param GF_Field $field
	 * @param string   $value
	 * @param array    $selected_values
	 *
	 * return string
	 */
	protected function get_escaped_original_answer_value( $field, $value, $selected_values ) {
		if ( $field->poll_field_type !== 'checkbox' ) {
			return esc_html( $value );
		}

		// Checkbox $value will be HTML <ul> markup - re-create it using escaped values.
		$escaped = array_map( 'esc_html', $selected_values );

		return $field->get_value_entry_detail( $escaped );
	}

	/**
	 * Maybe update the poll results cache when the entry status is changed.
	 *
	 * @param int $entry_id The ID of the entry which was updated.
	 */
	public function update_entry_status( $entry_id ) {
		$entry       = RGFormsModel::get_lead( $entry_id );
		$form_id     = $entry['form_id'];
		$form        = GFFormsModel::get_form_meta( $form_id );
		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( empty ( $poll_fields ) ) {
			return;
		}

		$this->maybe_update_cache( $form_id );
	}

	/**
	 * Maybe update the poll results cache when the entry is updated.
	 *
	 * @param array $form The current form object.
	 * @param int $entry_id The ID of the entry which was updated.
	 */
	public function entry_updated( $form, $entry_id ) {
		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( empty ( $poll_fields ) ) {
			return;
		}

		$form_id = $form['id'];
		$this->maybe_update_cache( $form_id );
	}

	// # TO FRAMEWORK MIGRATION -----------------------------------------------------------------------------------------

	/**
	 * Checks if a previous version was installed and if the form settings need migrating to the framework structure.
	 *
	 * @param string $previous_version The version number of the previously installed version.
	 */
	public function upgrade( $previous_version ) {
		$previous_is_pre_addon_framework = version_compare( $previous_version, '1.5.4', '<' );

		if ( $previous_is_pre_addon_framework ) {
			$forms = GFFormsModel::get_forms();
			foreach ( $forms as $form ) {
				$form_meta = GFFormsModel::get_form_meta( $form->id );
				$this->upgrade_form_settings( $form_meta );
			}
		}
	}

	/**
	 * Migrates the polls related form settings to the new structure.
	 *
	 * @param array $form The form object currently being processed.
	 */
	private function upgrade_form_settings( $form ) {
		if ( false === isset( $form['gpollDisplayResults'] ) ) {
			return;
		}

		$legacy_form_settings = array(
			'gpollDisplayResults'    => 'displayResults',
			'gpollShowResultsLink'   => 'showResultsLink',
			'gpollShowPercentages'   => 'showPercentages',
			'gpollShowCounts'        => 'showCounts',
			'gpollBlockRepeatVoters' => 'blockRepeatVoters',
			'gpollStyle'             => 'style',
			'gpollCookie'            => 'cookie',
		);

		$new_settings = array();
		foreach ( $legacy_form_settings as $legacy_key => $new_key ) {
			if ( isset( $form[ $legacy_key ] ) ) {
				$new_settings[ $new_key ] = $form[ $legacy_key ];
				unset( $form[ $legacy_key ] );
			}
		}
		if ( false === empty( $new_settings ) ) {
			$form[ $this->_slug ] = $new_settings;
			GFFormsModel::update_form_meta( $form['id'], $form );
		}
	}


	// # FORM SETTINGS --------------------------------------------------------------------------------------------------

	/**
	 * Add the form settings tab.
	 *
	 * @param array $tabs The tabs to be displayed on the form settings page.
	 * @param int $form_id The ID of the current form.
	 *
	 * @return array
	 */
	public function add_form_settings_menu( $tabs, $form_id ) {
		$form        = $this->get_form_meta( $form_id );
		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( ! empty( $poll_fields ) ) {
			$tabs[] = array(
				'name'         => 'gravityformspolls',
				'label'        => esc_html__( 'Polls', 'gravityformspolls' ),
				'capabilities' => array( $this->_capabilities_form_settings ),
				'icon'         => $this->form_settings_icon(),
			);
		}

		return $tabs;
	}

	/**
	 * The settings fields to be rendered on the form settings page.
	 *
	 * @param array $form The current form object.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {

		// check for legacy form settings from a form exported from a previous version pre-framework
		$page = rgget( 'page' );
		if ( 'gf_edit_forms' == $page && false === empty( $form_id ) ) {
			$settings = $this->get_form_settings( $form );
			if ( empty( $settings ) && isset( $form['gpollDisplayResults'] ) ) {
				$this->upgrade_form_settings( $form );
			}
		}

		return array(
			array(
				'title'  => esc_html__( 'Poll Settings', 'gravityformspolls' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Results', 'gravityformspolls' ),
						'type'    => 'checkbox',
						'name'    => 'displayResults',
						'tooltip' => '<h6>' . esc_html__( 'Results', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Select this option to display the results of submitted poll fields after the form is submitted.', 'gravityformspolls' ),
						'choices' => array(
							0 => array(
								'label'         => esc_html__( 'Display results of submitted poll fields after voting', 'gravityformspolls' ),
								'name'          => 'displayResults',
								'default_value' => $this->get_form_setting( array(), 'displayResults' )
							)
						)
					),
					array(
						'label'   => esc_html__( 'Results Link', 'gravityformspolls' ),
						'type'    => 'checkbox',
						'name'    => 'showResultsLink',
						'tooltip' => '<h6>' . esc_html__( 'Results Link', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Select this option to add a link to the form which allows the visitor to see the results without voting.', 'gravityformspolls' ),
						'choices' => array(
							0 => array(
								'label'         => esc_html__( 'Add a poll results link to the form', 'gravityformspolls' ),
								'name'          => 'showResultsLink',
								'default_value' => $this->get_form_setting( array(), 'showResultsLink' ),
							)
						)
					),
					array(
						'label'   => esc_html__( 'Percentages', 'gravityformspolls' ),
						'type'    => 'checkbox',
						'name'    => 'showPercentages',
						'tooltip' => '<h6>' . esc_html__( 'Show Percentages', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Show the percentage of the total votes for each choice.', 'gravityformspolls' ),
						'choices' => array(
							0 => array(
								'label'         => esc_html__( 'Display percentages', 'gravityformspolls' ),
								'name'          => 'showPercentages',
								'default_value' => $this->get_form_setting( array(), 'showPercentages' )
							)
						)
					),
					array(
						'label'   => esc_html__( 'Counts', 'gravityformspolls' ),
						'type'    => 'checkbox',
						'name'    => 'showCounts',
						'tooltip' => '<h6>' . esc_html__( 'Show Counts', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Show the total number of votes for each choice.', 'gravityformspolls' ),
						'choices' => array(
							0 => array(
								'label'         => esc_html__( 'Display counts', 'gravityformspolls' ),
								'name'          => 'showCounts',
								'default_value' => $this->get_form_setting( array(), 'showCounts' )
							)
						)
					),
					array(
						'label'         => esc_html__( 'Style', 'gravityformspolls' ),
						'name'          => 'style',
						'default_value' => $this->get_form_setting( array(), 'style' ),
						'type'          => 'select',
						'choices'       => array(
							array( 'label' => esc_html__( 'Green', 'gravityformspolls' ), 'value' => 'green' ),
							array( 'label' => esc_html__( 'Blue', 'gravityformspolls' ), 'value' => 'blue' ),
							array( 'label' => esc_html__( 'Red', 'gravityformspolls' ), 'value' => 'red' ),
							array( 'label' => esc_html__( 'Orange', 'gravityformspolls' ), 'value' => 'orange' ),
						)
					),
					array(
						'label'   => esc_html__( 'Block repeat voters', 'gravityformspolls' ),
						'type'    => 'block_repeat_voters',
						'name'    => 'blockRepeatVoters',
						'tooltip' => '<h6>' . esc_html__( 'Block Repeat Voters', 'gravityformspolls' ) . '</h6>' . esc_html__( "Choose whether to allow visitors to vote more than once. Repeat voting is controlled by storing a cookie on the visitor's computer.", 'gravityformspolls' ),
					),
				)
			),
		);
	}

	/**
	 * Define the properties and output the markup for the block_repeat_voters field type.
	 */
	public function settings_block_repeat_voters() {
		$this->settings_radio(
			array(
				'label'         => esc_html__( 'Repeat Voters', 'gravityformspolls' ),
				'name'          => 'blockRepeatVoters',
				'class'         => 'gpoll-block-repeat-voters',
				'default_value' => $this->get_form_setting( array(), 'blockRepeatVoters' ) ? '1' : '0',
				'type'          => 'radio',
				'choices'       => array(
					array( 'label' => esc_html__( "Don't block repeat voting", 'gravityformspolls' ), 'value' => '0' ),
					array( 'label' => esc_html__( 'Block repeat voting using cookie', 'gravityformspolls' ), 'value' => '1' ),
				)
			)
		);

		esc_html_e( 'Expires: ', 'gravityformspolls' );
		$this->settings_select(
			array(
				'name'          => 'cookie',
				'default_value' => $this->get_form_setting( array(), 'cookie' ),
				'type'          => 'select',
				'choices'       => array(
					array( 'label' => esc_html__( 'Never', 'gravityformspolls' ), 'value' => '20 years' ),
					array( 'label' => esc_html__( '1 hour', 'gravityformspolls' ), 'value' => '1 hour' ),
					array( 'label' => esc_html__( '6 hours', 'gravityformspolls' ), 'value' => '6 hours' ),
					array( 'label' => esc_html__( '12 hours', 'gravityformspolls' ), 'value' => '12 hours' ),
					array( 'label' => esc_html__( '1 day', 'gravityformspolls' ), 'value' => '1 day' ),
					array( 'label' => esc_html__( '1 week', 'gravityformspolls' ), 'value' => '1 week' ),
					array( 'label' => esc_html__( '1 month', 'gravityformspolls' ), 'value' => '1 month' ),
				)
			)
		);
	}


	/**
	 * Customizes the icon displayed on the form settings screen.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function form_settings_icon() {
		return 'gform-icon--poll';
	}

	// # FIELD SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Add the gpoll_field class to the Poll field.
	 *
	 * @param string $classes The CSS classes to be filtered, separated by empty spaces.
	 * @param GF_Field $field The field currently being processed.
	 * @param array $form The form currently being processed.
	 *
	 * @return string
	 */
	public function add_custom_class( $classes, $field, $form ) {
		if ( $field->type == 'poll' ) {
			$classes .= ' gpoll_field';
		}

		return $classes;
	}

	/**
	 * Add the tooltips for the Poll field.
	 *
	 * @param array $tooltips An associative array of tooltips where the key is the tooltip name and the value is the tooltip.
	 *
	 * @return array
	 */
	public function add_poll_field_tooltips( $tooltips ) {
		//form settings
		$tooltips['gpoll_form_settings_display_results']      = '<h6>' . esc_html__( 'Display Results After Voting', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Select this to display the results of submitted poll fields.', 'gravityformspolls' );
		$tooltips['gpoll_form_settings_display_confirmation'] = '<h6>' . esc_html__( 'Display Confirmation', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Select this option to display the form confirmation message after the visitor has voted.', 'gravityformspolls' );
		$tooltips['gpoll_form_settings_show_results_link']    = '<h6>' . esc_html__( 'Show Results Link', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Add a link to the form which allows the visitor to see the results without voting.', 'gravityformspolls' );
		$tooltips['gpoll_form_settings_show_percentages']     = '<h6>' . esc_html__( 'Show Percentages', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Show the percentage of the total votes for each choice.', 'gravityformspolls' );
		$tooltips['gpoll_form_settings_show_counts']          = '<h6>' . esc_html__( 'Show Counts', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Show the total number of votes for each choice.', 'gravityformspolls' );
		$tooltips['gpoll_form_settings_repeat_voters']        = '<h6>' . esc_html__( 'Repeat Voters', 'gravityformspolls' ) . '</h6>' . esc_html__( "Choose whether to allow visitors to vote more than once. Repeat voting is controlled by storing a cookie on the visitor's computer.", 'gravityformspolls' );

		$tooltips['form_poll_question']           = '<h6>' . esc_html__( 'Poll Question', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Enter the question you would like to ask the user. The user can then answer the question by selecting from the available choices.', 'gravityformspolls' );
		$tooltips['form_poll_field_type']         = '<h6>' . esc_html__( 'Poll Type', 'gravityformspolls' ) . '</h6>' . esc_html__( "Select the field type you'd like to use for the poll.", 'gravityformspolls' );
		$tooltips['form_field_randomize_choices'] = '<h6>' . esc_html__( 'Randomize Choices', 'gravityformspolls' ) . '</h6>' . esc_html__( 'Check the box to randomize the order in which the choices are displayed to the user. This setting affects only voting - it will not affect the order of the results.', 'gravityformspolls' );

		return $tooltips;
	}

	/**
	 * Add the custom settings for the Poll fields to the fields general tab.
	 *
	 * @param int $position The position the settings should be located at.
	 * @param int $form_id The ID of the form currently being edited.
	 */
	public function poll_field_settings( $position, $form_id ) {

		//create settings on position 25 (right after Field Label)
		if ( $position == 25 ) {
			?>

			<li class="poll_question_setting field_setting">
				<label for="poll_question" class="section_label">
					<?php esc_html_e( 'Poll Question', 'gravityformspolls' ); ?>
					<?php gform_tooltip( 'form_poll_question' ); ?>
				</label>
				<input type="text" id="poll_question" class="fieldwidth-3" onkeyup="SetFieldLabel(this.value)"
				       size="35"/>
			</li>

			<li class="poll_field_type_setting field_setting">
				<label for="poll_field_type" class="section_label">
					<?php esc_html_e( 'Poll Type', 'gravityformspolls' ); ?>
					<?php gform_tooltip( 'form_poll_field_type' ); ?>
				</label>
				<select id="poll_field_type"
				        onchange="if(jQuery(this).val() == '') return; jQuery('#field_settings').slideUp(function(){StartChangePollType(jQuery('#poll_field_type').val());});">
					<option value="select"><?php esc_html_e( 'Drop Down', 'gravityformspolls' ); ?></option>
					<option value="radio"><?php esc_html_e( 'Radio Buttons', 'gravityformspolls' ); ?></option>
					<option value="checkbox"><?php esc_html_e( 'Checkboxes', 'gravityformspolls' ); ?></option>

				</select>

			</li>

			<?php
		} elseif ( $position == 1368 ) {
			//right after the other_choice_setting
			?>
			<li class="randomize_choices_setting field_setting">

				<input type="checkbox" id="field_randomize_choices"
				       onclick="var value = jQuery(this).is(':checked'); SetFieldProperty('enableRandomizeChoices', value);"/>
				<label for="field_randomize_choices" class="inline">
					<?php esc_html_e( 'Randomize order of choices', 'gravityformspolls' ); ?>
					<?php gform_tooltip( 'form_field_randomize_choices' ) ?>
				</label>

			</li>
			<?php
		}
	}


	// # AJAX FUNCTIONS ------------------------------------------------------------------------------------------------

	/**
	 * Handler for the gpoll_ajax AJAX request.
	 * Returns the json encoded result for processing by gpoll.js.
	 */
	public function gpoll_ajax() {
		$output = array();

		$form_id = absint( rgpost( 'formId' ) );
		$form    = RGFormsModel::get_form_meta( $form_id );

		$preview_results = rgpost( 'previewResults' );
		$preview_results = $preview_results == '1' ? true : false;

		$has_voted = isset ( $_COOKIE[ 'gpoll_form_' . $form_id ] );
		$override  = false;
		if ( rgpost( 'override' ) == 1 ) {
			$show_results_link = rgpost( 'showResultsLink' ) == '1' ? true : false;

			$display_results = rgpost( 'displayResults' ) == '1' ? true : false;
			$confirmation    = rgpost( 'confirmation' ) == '1' ? true : false;
			$percentages     = rgpost( 'percentages' ) == '1' ? true : false;
			$counts          = rgpost( 'counts' ) == '1' ? true : false;
			$cookie_duration = urldecode( rgpost( 'cookieDuration' ) );
			$style           = rgpost( 'style' );
			$checksum        = rgpost( 'checksum' );
			if ( $checksum == $this->generate_checksum( $display_results, $show_results_link, $cookie_duration, $confirmation, $percentages, $counts, $style ) ) {
				$override = true;
			}
		}

		if ( false === $override ) {
			$show_results_link   = $this->get_form_setting( $form, 'showResultsLink' );
			$display_results     = $this->get_form_setting( $form, 'displayResults' );
			$confirmation        = true;
			$percentages         = $this->get_form_setting( $form, 'showPercentages' );
			$counts              = $this->get_form_setting( $form, 'showCounts' );
			$style               = $this->get_form_setting( $form, 'style' );
			$block_repeat_voters = $this->get_form_setting( $form, 'blockRepeatVoters' );

			if ( $block_repeat_voters ) {
				$cookie_duration = $this->get_form_setting( $form, 'cookie' );
			} else {
				$cookie_duration = '';
			}
		}


		$can_vote          = ( ! $has_voted ) || ( empty( $cookie_duration ) && $has_voted );
		$output['canVote'] = $can_vote;

		if ( $preview_results || ( false === $can_vote ) ) {

			if ( '' === $show_results_link ) {
				$show_results_link = true;
			}

			if ( ( $preview_results && $show_results_link ) || $display_results ) {
				$results             = $this->gpoll_get_results( $form_id, '0', $style, $percentages, $counts );
				$results_summary     = $results['summary'];
				$output['resultsUI'] = $results_summary;
			} else {
				if ( $confirmation ) {
					require_once( GFCommon::get_base_path() . '/form_display.php' );
					$output['resultsUI'] = GFFormDisplay::handle_confirmation( $form, null );
				} else {
					$output['resultsUI'] = '';
				}
			}
		} else {
			$output['resultsUI'] = '';
		}

		/**
		 * Allows the Ajax response to be overridden.
		 *
		 * @since 3.2.2
		 *
		 * @param array $response {
		 *     An associative array containing the properties to be returned in the Ajax response.
		 *
		 *     @type bool   canVote   Indicates if the user is allowed to vote.
		 *     @type string resultsUI The results HTML, confirmation, or an empty string.
		 * }
		 * @param array $form The form for which the Ajax request was made.
		 */
		$response = gf_apply_filters( array( 'gform_polls_results_ajax_response', $form_id ), $output, $form );

		echo json_encode( $response );
		die();

	}


	// # CRON JOB & RESULTS CACHE --------------------------------------------------------------------------------------

	/**
	 * Adds once weekly to the existing cron schedules.
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array
	 */
	public function cron_add_custom_schedule( $schedules ) {
		$custom_schedules = $this->get_custom_cron_schedule();
		$schedules        = array_merge( $schedules, $custom_schedules );

		return $schedules;
	}


	/**
	 * Used for the second parameter of the wp_schedule_event function. How often the event should recur.
	 *
	 * @return string
	 */
	public function get_cron_recurrence() {

		$custom_schedule = $this->get_custom_cron_schedule();

		if ( empty( $custom_schedule ) ) {
			$recurrence = 'hourly';
		} else {
			$recurrence = current( array_keys( $custom_schedule ) );
		}

		return $recurrence;
	}


	/**
	 * Helper to allow the gform_polls_cron_schedule filter to be used to override the default cron schedule.
	 *
	 * @return array
	 */
	public function get_custom_cron_schedule() {

		/**
		 * Allows modification to the cron schedule for Polls
		 *
		 * @param array $schedule An array to allow modifications to the cron schedule or create a custom cron schedule
		 */
		$schedule = apply_filters( 'gform_polls_cron_schedule', array() );

		return $schedule;
	}

	/**
	 * Unschedule the cron when the plugin is deactivated.
	 */
	public static function remove_wp_cron_task() {
		wp_clear_scheduled_hook( 'gform_polls_cron' );
	}

	/**
	 * Called only by the wp_cron task.
	 */
	public function wp_cron_task() {
		$forms = GFFormsModel::get_forms( true );
		foreach ( $forms as $form ) {
			$form_id     = $form->id;
			$form_meta   = $this->get_form_meta( $form_id );
			$poll_fields = GFAPI::get_fields_by_type( $form_meta, array( 'poll' ) );
			if ( empty ( $poll_fields ) )
				continue;

			$data_tmp = GFCache::get( 'gpoll_data_tmp_' . $form_id );
			if ( false === $data_tmp ) {
				$data = GFCache::get( 'gpoll_data_' . $form_id );
				if ( false == $data || rgar( $data, 'incomplete' ) || false === isset( $data['execution_time'] ) || rgar( $data, 'expired' ) ) {
					$data = $this->gpoll_get_data( $form_id );
					$this->maybe_continue_cache_rebuild( $data, $form_id );
				}
			} else {
				$data = $this->gpoll_get_data( $form_id, $data_tmp );
				$this->maybe_continue_cache_rebuild( $data, $form_id );
			}
		}
	}

	/**
	 * Called only by the wp_cron job.
	 *
	 * @param array $data
	 * @param int $form_id The form ID.
	 */
	public function maybe_continue_cache_rebuild( $data, $form_id ) {
		if ( rgar( $data, 'incomplete' ) ) {
			GFCache::set( 'gpoll_data_tmp_' . $form_id, $data, true );
		} else {
			GFCache::set( 'gpoll_data_' . $form_id, $data, true );
			GFCache::delete( 'gpoll_data_tmp_' . $form_id );
		}
	}

	/**
	 * Called on entry created, entry updated, entry status changed and form saved.
	 * Not called by the wp_cron job.
	 *
	 * @param int $form_id The form ID.
	 */
	public function maybe_update_cache( $form_id ) {
		$key  = 'gpoll_data_' . $form_id;
		$data = GFCache::get( $key );
		if ( false === $data ) {
			// nothing in the cache so start building it
			$this->update_cache( $form_id );
		} else {
			if ( rgar( $data, 'execution_time' ) < 5 ) {
				// update the cache now if the last execution was under 5 seconds
				$this->update_cache( $form_id );
			} else {
				// mark the cache expired so the wp_cron job will begin recalculation
				$data['expired'] = true;
				GFCache::set( $key, $data, true );
			}
		}
	}


	/**
	 * Update the results cache when an entry is created.
	 *
	 * @param array $entry The entry which was created.
	 * @param array $form The current form.
	 */
	public function entry_created( $entry, $form ) {
		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( empty ( $poll_fields ) ) {
			return;
		}

		//update cache
		$form_id = $form['id'];
		$this->maybe_update_cache( $form_id );

	}

	/**
	 * Update the results cache when a form is saved.
	 *
	 * @param array $form The current form.
	 * @param bool $is_new True if this is a new form being created. False if this is an existing form being updated.
	 */
	public function after_save_form( $form, $is_new ) {

		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );
		if ( empty ( $poll_fields ) ) {
			return;
		}
		//update cache
		$form_id = $form['id'];

		$this->maybe_update_cache( $form_id );
	}


	/**
	 * Cache the form meta.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return mixed
	 */
	public function get_form_meta( $form_id ) {
		$form_metas = $this->_form_meta_by_id;

		if ( empty( $form_metas ) ) {
			$form_ids = array();
			$forms    = RGFormsModel::get_forms();
			foreach ( $forms as $form ) {
				$form_ids[] = $form->id;
			}

			if ( method_exists( 'GFFormsModel', 'get_form_meta_by_id' ) )
				$form_metas = GFFormsModel::get_form_meta_by_id( $form_ids );
			else
				$form_metas = GFFormsModel::get_forms_by_id( $form_ids ); //backwards compatiblity with <1.7

			$this->_form_meta_by_id = $form_metas;
		}
		foreach ( $form_metas as $form_meta ) {
			if ( $form_meta['id'] == $form_id )
				return $form_meta;
		}

	}


	/**
	 * Update the results cache.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return array
	 */
	public function update_cache( $form_id ) {

		$gpoll_data = $this->gpoll_get_data( $form_id );
		GFCache::set( 'gpoll_data_' . $form_id, $gpoll_data, true );

		return $gpoll_data;
	}

	/**
	 * Cycles through all entries, counts responses and returns an associative array with the data for each field.
	 * It's then optionally cached later according to the user settings.
	 *
	 * @param int $form_id The form ID.
	 * @param array $gpoll_data The poll results data.
	 *
	 * @return array
	 */
	public function gpoll_get_data( $form_id, $gpoll_data = array() ) {
		$time_start         = microtime( true );
		$max_execution_time = 20; //seconds
		$totals             = RGFormsModel::get_form_counts( $form_id );
		$total              = $totals['total'];

		$form_meta   = RGFormsModel::get_form_meta( $form_id );
		$form_meta   = apply_filters( "gform_polls_form_pre_results_$form_id", apply_filters( 'gform_polls_form_pre_results', $form_meta ) );
		$poll_fields = array();

		foreach ( $form_meta['fields'] as $field ) {
			if ( $field->type !== 'poll' ) {
				continue;
			}
			$poll_fields[] = clone $field;
		}


		$offset        = 0;
		$page_size     = 200;
		$field_counter = 0;

		$search_criteria = array(
			'status' => 'active',
		);

		$sorting = array(
			'key' => 'id',
			'direction' => 'DESC',
			'is_numeric' => false,
		);

		if ( empty( $gpoll_data ) ) {

			//first build list of fields to count and later count the entries
			//it's split up this way to avoid a timeout on large resultsets

			foreach ( $poll_fields as $poll_field ) {

				$fieldid = $poll_field->id;

				$gpoll_field_data = array(
					'field_label' => $poll_field->label,
					'field_id'    => $fieldid,
					'type'        => $poll_field->type,
					'inputType'   => $poll_field->inputType,
				);

				$gpoll_data['fields'][ $field_counter ] = $gpoll_field_data;

				$gpoll_input_data = array();

				//for checkboxes
				if ( $poll_field->inputType == 'checkbox' ) {
					$input_counter = 0;
					foreach ( $poll_field->inputs as $input ) {
						$inputid = str_replace( '.', '_', $input['id'] );

						$gpoll_input_data = array(
							'input_id' => "#choice_{$inputid}",
							'label'    => $input['label'],
						);
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $input_counter ] = $gpoll_input_data;
						$input_counter += 1;
					}
				} else {
					//for radio & dropdowns

					$choice_counter = 0;
					if ( $poll_field->enableOtherChoice ) {
						$choice_index                      = count( $poll_field->choices );
						$choices                           = $poll_field->choices;
						$choices[ $choice_index ]['text']  = esc_html__( 'Other', 'gravityformspolls' );
						$choices[ $choice_index ]['value'] = 'gpoll_other';
						$poll_field->choices               = $choices;
					}

					foreach ( $poll_field->choices as $choice ) {
						$gpoll_input_data                                                = array(
							'input_id' => "#choice_{$fieldid}_{$choice_counter}",
							'label'    => $choice['text'],
						);
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $choice_counter ] = $gpoll_input_data;
						$choice_counter += 1;
					}
				}
				$field_counter += 1;
				$i = $offset;

			}
		} else {
			$i = $gpoll_data['offset'];
			unset( $gpoll_data['offset'] );
		}

		$precision = apply_filters( 'gform_polls_percentage_precision', 0, $form_id );

		//get leads in groups of $page_size to avoid timeouts
		while ( $i <= $total ) {
			$field_counter = 0;

			$get_leads_time_start = microtime( true );

			$paging = array(
				'offset' => $i,
				'page_size' => $page_size,
			);

			$entries = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging );

			$get_leads_time_end   = microtime( true );
			$get_leads_time       = $get_leads_time_end - $get_leads_time_start;

			//loop through each field currently on the form and count the entries for each choice
			foreach ( $poll_fields as $poll_field ) {

				if ( isset ( $gpoll_data['fields'][ $field_counter ]['total_entries'] ) ) {
					$field_total_entries = $gpoll_data['fields'][ $field_counter ]['total_entries'];
				} else {
					$field_total_entries = 0;
				}

				foreach ( $entries as $entry ) {
					$entry_value = RGFormsModel::get_lead_field_value( $entry, $poll_field );

					if ( false === empty( $entry_value ) )
						$field_total_entries ++;
				}
				$gpoll_data['fields'][ $field_counter ]['total_entries'] = $field_total_entries;

				$gpoll_input_data = array();

				// checkboxes store entries differently to radio & dropdowns
				if ( $poll_field->inputType == 'checkbox' ) {
					//for checkboxes

					// loop through all the choices and count the entries for each choice
					$input_counter = 0;
					foreach ( $poll_field->inputs as $input ) {

						// running total of entries for each set of entries
						if ( isset ( $gpoll_data['fields'][ $field_counter ]['inputs'][ $input_counter ]['total_entries'] ) ) {
							$total_entries = $gpoll_data['fields'][ $field_counter ]['inputs'][ $input_counter ]['total_entries'];
						} else {
							$total_entries = 0;
						}
						$entry_index = 1;

						// loop through all the entries and count the entries for the choice
						foreach ( $entries as $entry ) {

							// loop through each item in the lead object and pick out the entries for this field id
							foreach ( $entry as $key => $entry_value ) {

								// checkboxes store the key as [field number].[input index] (e.g. 2.1 or 2.2)
								// so convert to integer to identify all the responses inside the lead object for this field id
								if ( intval( $key ) == $poll_field->id ) {
									//compare the user's response with the current choice
									if ( $entry_value == $poll_field->choices[ $input_counter ]['value'] ) {
										// found a response for this choice so continue to the next lead
										$total_entries ++;
										break;
									}
								}
							}
							$entry_index += 1;
						}

						//calculate the ratio of total number of responses counted to the total number of entries for this form
						$ratio = 0;

						if ( $field_total_entries != 0 ) {
							$ratio = round( ( $total_entries / $field_total_entries * 100 ), $precision );
						}

						//store the data
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $input_counter ]['value']         = $poll_field->choices[ $input_counter ]['value'];
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $input_counter ]['total_entries'] = $total_entries;
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $input_counter ]['ratio']         = $ratio;
						$input_counter += 1;
					}
				} else {
					// for radio & dropdowns

					$choice_counter = 0;

					// loop through each choice and count the responses
					foreach ( $poll_field->choices as $choice ) {

						// running total of entries for each set of entries
						if ( isset ( $gpoll_data['fields'][ $field_counter ]['inputs'][ $choice_counter ]['total_entries'] ) ) {
							$total_entries = $gpoll_data['fields'][ $field_counter ]['inputs'][ $choice_counter ]['total_entries'];
						} else {
							$total_entries = 0;
						}

						// count responses for 'Other'
						if ( rgar( $choice, 'value') == 'gpoll_other' ) {
							$possible_choices = array();
							foreach ( $poll_field->choices as $possible_choice ) {
								array_push( $possible_choices, rgar( $possible_choice, 'value') );
							}

							foreach ( $entries as $entry ) {
								$entry_value = RGFormsModel::get_lead_field_value( $entry, $poll_field );

								if ( ! empty( $entry_value ) && ! in_array( $entry_value, $possible_choices ) ) {
									$total_entries ++;
								}
							}
						} else {

							// count entries
							foreach ( $entries as $entry ) {
								$entry_value = RGFormsModel::get_lead_field_value( $entry, $poll_field );
								if ( $entry_value === rgar( $choice, 'value' ) ) {
									$total_entries ++;
								}

							}
						}

						// calculate the ratio of total number of responses counted to the total number of entries for this form
						$ratio = 0;
						if ( $field_total_entries != 0 ) {
							$ratio = round( ( $total_entries / $field_total_entries * 100 ), $precision );
						}


						//store the data
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $choice_counter ]['value']         = rgar( $choice, 'value' );
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $choice_counter ]['total_entries'] = $total_entries;
						$gpoll_data['fields'][ $field_counter ]['inputs'][ $choice_counter ]['ratio']         = $ratio;
						$choice_counter += 1;
					}
				}

				$field_counter += 1;
			}
			$i += $page_size;
			$time_end                     = microtime( true );
			$execution_time               = ( $time_end - $time_start );
			$gpoll_data['execution_time'] = isset( $gpoll_data['execution_time'] ) ? $gpoll_data['execution_time'] + $execution_time : $execution_time;
			$gpoll_data['incomplete']     = false;
			if ( $execution_time + $get_leads_time > $max_execution_time ) {
				$gpoll_data['incomplete'] = true;
				$gpoll_data['offset']     = $i;
				break;
			}
		} //end while
		return $gpoll_data;
	} // end function gpoll_get_data

	/**
	 * Returns the results in an array of HTML formatted data.
	 *
	 * @param int $formid The form ID.
	 * @param string $display_field
	 * @param string $style
	 * @param bool $show_percentages
	 * @param bool $show_counts
	 * @param array $lead
	 *
	 * @return array
	 */
	public function gpoll_get_results( $formid, $display_field = '0' /* zero = all fields */, $style = 'green', $show_percentages = true, $show_counts = true, $lead = array() ) {

		$gpoll_output = array();
		$gpoll_data   = array();

		// each bar will receive this HTML formatting
		$bar_html = "<div class='gpoll_wrapper {$style}'><div class='gpoll_ratio_box'><div class='gpoll_ratio_label'>%s</div></div><div class='gpoll_bar'>";
		$bar_html .= "<span class='gpoll_bar_juice' data-origwidth='%s' style='width: %s%%'><span class='gpoll_bar_count'>%s</span></span></div></div>";


		// if data is cached then pull the data out of the cache

		if ( false === ( $gpoll_data = GFCache::get( 'gpoll_data_' . $formid ) ) ) {

			// cache has timed out so get the data again and cache it again
			$gpoll_data = $this->update_cache( $formid );
		}


		// build HTML output

		$gpoll_output['summary'] = "<div class='gpoll_container'>";
		$field_counter           = 0;

		// loop through polls data field by field
		foreach ( $gpoll_data['fields'] as $field ) {

			$fieldid = $field['field_id'];

			// only build html for the field(s) specified in the parameter. 0 = all fields
			if ( is_array( $display_field ) ) {
				if ( false === in_array( $fieldid, $display_field ) )
					continue;
			} elseif ( $display_field != '0' && $fieldid != $display_field ) {
				continue;
			}


			// build 2 sections: summary and individual fields
			$field_number = $field_counter + 1;
			$gpoll_output['summary'] .= "<div class='gpoll_field'>";
			$gpoll_output['summary'] .= "<div class='gpoll_field_label_container'>";

			$gpoll_output['summary'] .= "<div class='gpoll_field_label'>";
			$gpoll_output['summary'] .= $field['field_label'] . '</div></div>';

			// the individual fields HTML was used in the past but not used now.
			// it was used to display results 'inline' with the form (i.e. form input then the bar below)
			// I've left it because it may be useful either to designers or for a future use
			$gpoll_output['fields'][ $field_counter ]['field_id'] = $field['field_id'];
			$gpoll_output['fields'][ $field_counter ]['type']     = $field['type'];

			$selected_values = array();

			// if the lead object is passed then prepare to highlight the selected choices
			if ( ! empty ( $lead ) ) {
				$form_meta = RGFormsModel::get_form_meta( $formid );
				// collect all the responses in the lead for this field
				$selected_values = $this->get_selected_values( $form_meta, $fieldid, $lead );

				//collect all the choices that are currently possible in the field

				$possible_choices = $this->get_possible_choices( $form_meta, $fieldid );

				$form_meta_field = RGFormsModel::get_field( $form_meta, $fieldid );

				// if the 'other' option is selected for this field
				// add the psuedo-value 'gpoll_other' if responses are found that are not in the list of possible choices
				if ( $form_meta_field->enableOtherChoice ) {

					foreach ( $selected_values as $selected_value ) {
						if ( ! in_array( $selected_value, $possible_choices ) )
							array_push( $selected_values, 'gpoll_other' );
					}
				}
			}

			// loop through all the inputs in this field (poll data field not form object field) and build the HTML for the bar
			$input_counter = 0;
			foreach ( $field['inputs'] as $input ) {

				//highlight the selected value by adding a class to the label
				$selected_class = '';
				if ( in_array( rgar( $input, 'value' ), $selected_values ) ) {
					$selected_class .= ' gpoll_value_selected';
				}

				//build the bar and add it to the summary
				$gpoll_output['summary'] .= sprintf( "<div class='gpoll_choice_label%s'>%s</div>", $selected_class, rgar( $input, 'label' ) );
				$ratio            = rgar( $input, 'ratio' );
				$count            = $show_counts === true ? rgar( $input, 'total_entries' ) : '';
				$percentage_label = $show_percentages === true ? $ratio . '%' : '';
				$input_html       = sprintf( $bar_html, $percentage_label, $ratio, $ratio, $count );
				$gpoll_output['summary'] .= $input_html;

				//add the bar HTML to the fields array ready to output alongside the summary
				$input_data                                                       = array(
					'input_id'      => rgar( $input, 'input_id' ),
					'label'         => rgar( $input, 'label' ),
					'total_entries' => $input['total_entries'],
					'ratio'         => $input['ratio'],
					'bar_html'      => $input_html,
				);
				$gpoll_output['fields'][ $field_counter ]['inputs'][ $input_counter ] = $input_data;

				$input_counter += 1;
			}
			$gpoll_output['summary'] .= '</div>';
			$field_counter += 1;
		}
		$gpoll_output['summary'] .= '</div>';

		return $gpoll_output;

	} //end function gpoll_get_results

	/**
	 * Collect all the responses in the lead for this field and returns an array.
	 *
	 * @param array $form_meta The current form meta.
	 * @param int $fieldid The current field ID.
	 * @param array $lead The current entry.
	 *
	 * @return array
	 */
	public function get_selected_values( $form_meta, $fieldid, $lead ) {

		$selected_values = array();

		//pick out the field we need from the fields collection in the form object
		//and add the selected values to the selected_values array
		if ( is_array( $form_meta['fields'] ) ) {
			foreach ( $form_meta['fields'] as $field ) {
				if ( $field->id == $fieldid ) {
					if ( $field->inputType == 'checkbox' ) {
						for ( $i = 1; $i <= count( $field->inputs ); $i ++ ) {
							$lead_index = 0;
							$lead_index = $fieldid . '.' . $i;
							if ( isset( $lead[ $lead_index ] ) && ! empty( $lead[ $lead_index ] ) )
								array_push( $selected_values, $lead[ $lead_index ] );
						}
					} else {
						for ( $i = 1; $i <= count( $field->choices ); $i ++ ) {
							$lead_index = $fieldid;
							if ( isset( $lead[ $lead_index ] ) && ! empty( $lead[ $lead_index ] ) )
								array_push( $selected_values, $lead[ $lead_index ] );
						}
					}
					break;
				}
			}
		}

		return $selected_values;
	}

	/**
	 * Get the choice values for the specified field.
	 *
	 * @param array $form_meta The current form meta.
	 * @param int $fieldid The current field ID.
	 *
	 * @return array
	 */
	public function get_possible_choices( $form_meta, $fieldid ) {

		$possible_choices = array();

		//pick out the field we need from the fields collection in the form object
		//and add the possible choices to the possible_choices array
		if ( is_array( $form_meta['fields'] ) ) {
			foreach ( $form_meta['fields'] as $field ) {
				if ( $field->id == $fieldid ) {
					foreach ( $field->choices as $possible_choice ) {
						array_push( $possible_choices, rgar( $possible_choice, 'value' ) );
					}

					return $possible_choices;
				}
			}
		}
	}


	// # SHORTCODES ----------------------------------------------------------------------------------------------------

	/**
	 * Displays the form and specifies hidden form values to enable and configure the poll.
	 * If the cookie is already set then display the results.
	 *
	 * @param $string
	 * @param $attributes
	 * @param $content
	 *
	 * @return mixed|null|string|void
	 */
	function poll_shortcode( $string, $attributes, $content ) {

		extract(
			shortcode_atts(
				array(
					'title'             => true,
					'description'       => true,
					'confirmation'      => false,
					'id'                => 0,
					'name'              => '',
					'field_values'      => '',
					'ajax'              => false,
					'disable_scripts'   => false,
					'tabindex'          => 1,
					'mode'              => 'poll',
					'field'             => 0,
					'style'             => 'green',
					'display_results'   => true,
					'show_results_link' => true,
					'percentages'       => true,
					'counts'            => true,
					'cookie'            => '',

				), $attributes
			)
		);


		$currentDate            = strtotime( 'now' );
		$cookie                 = strtolower( $cookie );
		$cookie_expiration_date = date( strtotime( $cookie ) );

		$confirmation = strtolower( $confirmation ) == 'false' ? false : true;
		if ( ! empty( $cookie ) && $cookie_expiration_date <= $currentDate ) {
			return sprintf( esc_html__( 'Gravity Forms Polls Add-on Shortcode error: Please enter a valid date or time period for the cookie expiration cookie_expiration_date: %s', 'gravityformspolls' ), $cookie_expiration_date );
		}

		$percentages       = strtolower( $percentages ) == 'false' ? false : true;
		$counts            = strtolower( $counts ) == 'false' ? false : true;
		$display_results   = strtolower( $display_results ) == 'false' ? false : true;
		$show_results_link = strtolower( $show_results_link ) == 'false' ? false : true;

		$title           = strtolower( $title ) == 'false' ? false : true;
		$description     = strtolower( $description ) == 'false' ? false : true;
		$ajax            = strtolower( $ajax ) == 'true' ? true : false;
		$disable_scripts = strtolower( $disable_scripts ) == 'true' ? true : false;

		$return = true;

		$poll_ui = $this->build_poll_ui( $id, $field, $style, $mode, $percentages, $counts, $title, $description, $confirmation, $show_results_link, $ajax, $cookie, $display_results, $field_values, $disable_scripts, $tabindex, $return );

		return $poll_ui;

	} // end function poll_shortcode

	public function build_poll_ui( $form_id, $field_id, $style, $mode, $percentages, $counts, $title, $description, $confirmation, $show_results_link, $ajax, $cookie, $display_results, $field_values, $disable_scripts, $tabindex, $return = true ) {

		if ( ! $this->should_render_form( $form_id ) ) {
			return;
		}

		$form = RGFormsModel::get_form_meta( $form_id );

		if ( empty( $form ) ) {
			return;
		}

		$poll_fields = GFAPI::get_fields_by_type( $form, array( 'poll' ) );

		if ( empty( $poll_fields ) ) {
			return;
		}

		$this->gpoll_add_scripts = true;

		if ( $mode == 'results' ) {

			$results = $this->gpoll_get_results( $form_id, $field_id, $style, $percentages, $counts );
			$output  = $results['summary'];


		} else {
			$checksum          = $this->generate_checksum( $display_results, $show_results_link, $cookie, $confirmation, $percentages, $counts, $style );
			$show_results_link = false === $show_results_link ? 0 : 1;

			$field_values = htmlspecialchars_decode( $field_values );
			$field_values = str_replace( '&#038;', '&', $field_values );

			$percentages     = $percentages === false ? 0 : 1;
			$counts          = $counts === false ? 0 : 1;
			$display_results = $display_results ? 1 : 0;

			if ( $disable_scripts === false ) {
				RGForms::print_form_scripts( $form, $ajax );
			}


			if ( $field_values != '' ) {
				$field_values .= '&';
			}
			$field_values .= "gpoll_enabled=1&gpoll_field={$field_id}&gpoll_style={$style}&gpoll_display_results={$display_results}&gpoll_show_results_link={$show_results_link}&gpoll_cookie={$cookie}&gpoll_confirmation={$confirmation}&gpoll_percentages={$percentages}&gpoll_counts={$counts}&gpoll_checksum={$checksum}";

			parse_str( $field_values, $field_value_array );
			$field_value_array = stripslashes_deep( $field_value_array );

			$output = RGForms::get_form( $form_id, $title, $description, false, $field_value_array, $ajax, $tabindex );


		}

		if ( false === $return ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Decides if the form ui should be rendered or not.
	 *
	 * @since 3.9
	 *
	 * @param int $form_id The form id.
	 *
	 * @return bool
	 */
	private function should_render_form( $form_id ) {
		$form = GFAPI::get_form( $form_id );
		return $form && rgar( $form, 'is_active' );
	}

	private function generate_checksum( $display_results, $show_results_link, $cookie, $confirmation, $percentages, $counts, $style ) {
		$checksum_vars = array( $display_results, $show_results_link, $cookie, $confirmation, $percentages, $counts, $style );

		return wp_hash( crc32( base64_encode( serialize( $checksum_vars ) ) ) );

	}

	public function poll_total_shortcode( $atts, $content = null ) {

		extract(
			shortcode_atts(
				array(
					'id' => '1',
				), $atts
			)
		);

		$totals = RGFormsModel::get_form_counts( $id );
		$total  = $totals['total'];

		return $total;
	}

	function add_polls_shortcode_ui_action( $actions ) {
		$actions[] = array(
			'polls' => array(
				'label' => esc_html__( 'Polls', 'gravityformspolls' ),
				'attrs' => array(
					array(
						'label'   => esc_html__( 'Style', 'gravityformspolls' ),
						'attr'    => 'style',
						'type'    => 'select',
						'default' => 'green',
						'options' => array(
							'green'  => esc_html__( 'Green', 'gravityformspolls' ),
							'red'    => esc_html__( 'Red', 'gravityformspolls' ),
							'orange' => esc_html__( 'Orange', 'gravityformspolls' ),
							'blue'   => esc_html__( 'Blue', 'gravityformspolls' ),
						),
						'tooltip' => esc_html__( 'The Add-On currently supports 4 built-in styles: red, green, orange, blue. Defaults to "green".', 'gravityformspolls' )
					),
					array(
						'label'   => esc_html__( 'Mode', 'gravityformspolls' ),
						'attr'    => 'mode',
						'type'    => 'select',
						'default' => 'poll',
						'options' => array(
							'poll'    => 'Poll',
							'results' => 'Results',
						)
					),
					array(
						'label'   => esc_html__( 'Cookie', 'gravityformspolls' ),
						'attr'    => 'cookie',
						'type'    => 'text',
						'tooltip' => esc_html__( 'Enables blocking of repeat voters. You enable this by passing a defined time period. Available time periods are: 1 day, 1 week, 1 month, or a specific date in the YYYY-MM-DD date format. Defaults to an empty string, which means no repeat voters are blocked.', 'gravityformspolls' )
					),
					array(
						'label'   => esc_html__( 'Results link', 'gravityformspolls' ),
						'attr'    => 'show_results_link',
						'type'    => 'checkbox',
						'default' => 'true',
						'tooltip' => esc_html__( 'Display a link to view poll results without submitting the form? Supported values are: true, false. Defaults to "true".', 'gravityformspolls' )
					),
					array(
						'label'   => esc_html__( 'Display Results', 'gravityformspolls' ),
						'attr'    => 'display_results',
						'type'    => 'checkbox',
						'default' => 'true',
						'tooltip' => esc_html__( 'Display poll results automatically when the form is submitted? Supported values are: true, false. Defaults to "true".', 'gravityformspolls' )
					),
					array(
						'label'   => esc_html__( 'Display Percentages', 'gravityformspolls' ),
						'attr'    => 'percentages',
						'type'    => 'checkbox',
						'default' => 'true',
						'tooltip' => esc_html__( 'Display results percentages as part of results? Supported values are: true, false. Defaults to "true".', 'gravityformspolls' )
					),
					array(
						'label'   => esc_html__( 'Display Counts', 'gravityformspolls' ),
						'attr'    => 'counts',
						'type'    => 'checkbox',
						'default' => 'true',
						'tooltip' => esc_html__( 'Display number of times each choice has been selected when displaying results? Supported values are: true, false. Defaults to "true".', 'gravityformspolls' )
					),
				),
			)

		);

		return $actions;
	}

	// # CONTACTS INTEGRATION -------------------------------------------------------------------------------------------

	public function add_tab_to_contact_detail( $tabs, $contact_id ) {
		if ( $contact_id > 0 ) {
			$tabs[] = array( 'name' => 'polls', 'label' => __( 'Poll Entries', 'gravityformspolls' ) );
		}

		return $tabs;
	}

	public function contacts_tab( $contact_id ) {

		if ( false === empty( $contact_id ) ) :
			$search_criteria['status'] = 'active';
			$search_criteria['field_filters'][] = array( 'type'  => 'meta',
			                                             'key'   => 'gcontacts_contact_id',
			                                             'value' => $contact_id
			);
			$form_ids                           = array();
			$forms                              = GFFormsModel::get_forms( true );
			foreach ( $forms as $form ) {
				$form_meta   = GFFormsModel::get_form_meta( $form->id );
				$poll_fields = GFAPI::get_fields_by_type( $form_meta, array( 'poll' ) );
				if ( ! empty( $poll_fields ) ) {
					$form_ids[] = $form->id;
				}
			}

			if ( empty( $form_ids ) ) {
				return;
			}
			$entries                   = GFAPI::get_entries( $form_ids, $search_criteria );

			if ( empty( $entries ) ) :
				esc_html_e( 'This contact has not submitted any poll entries yet.', 'gravityformspolls' );

			else : ?>

				<h3><span><?php esc_html_e( 'Poll Entries', 'gravityformspolls' ) ?></span></h3>
				<div>
					<table id="gcontacts-entry-list" class="widefat">
						<tr class="gcontacts-entries-header">
							<td>
								<?php esc_html_e( 'Entry Id', 'gravityformspolls' ) ?>
							</td>
							<td>
								<?php esc_html_e( 'Date', 'gravityformspolls' ) ?>
							</td>
							<td>
								<?php esc_html_e( 'Form', 'gravityformspolls' ) ?>
							</td>
						</tr>
						<?php
						foreach ( $entries as $entry ) {
							$form_id    = $entry['form_id'];
							$form       = GFFormsModel::get_form_meta( $form_id );
							$form_title = rgar( $form, 'title' );
							$entry_id   = $entry['id'];
							$entry_date = GFCommon::format_date( rgar( $entry, 'date_created' ), false );
							$entry_url  = admin_url( "admin.php?page=gf_entries&view=entry&id={$form_id}&lid={$entry_id}" );

							?>
							<tr>
								<td>
									<a href="<?php echo $entry_url; ?>"><?php echo $entry_id; ?></a>
								</td>
								<td>
									<?php echo $entry_date; ?>
								</td>
								<td>
									<?php echo $form_title; ?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				</div>
				<?php
			endif;
		endif;
	}

} //end class GFPolls
