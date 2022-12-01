<?php

// Make sure Gravity Forms is active and already loaded.
if ( ! class_exists( 'GFForms' ) ) {
	die();
}

// The Add-On Framework is not loaded by default.
// Use the following function to load the appropriate files.
GFForms::include_feed_addon_framework();

/**
 * GF_Partial_Entries
 *
 *
 * @copyright   Copyright (c) 2015-2016, Rocketgenius
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class GF_Partial_Entries extends GFFeedAddOn {

	protected $_multiple_feeds = false;

	// The following class variables are used by the Framework.
	// They are defined in GFAddOn and should be overridden.

	// The version number is used for example during add-on upgrades.
	protected $_version = GF_PARTIAL_ENTRIES_VERSION;

	// The Framework will display an appropriate message on the plugins page if necessary
	protected $_min_gravityforms_version = '2.3';

	// A short, lowercase, URL-safe unique identifier for the add-on.
	// This will be used for storing options, filters, actions, URLs and text-domain localization.
	protected $_slug = 'gravityformspartialentries';

	// Relative path to the plugin from the plugins folder.
	protected $_path = 'gravityformspartialentries/partialentries.php';

	// Full path the the plugin.
	protected $_full_path = __FILE__;

	// Title of the plugin to be used on the settings page, form settings and plugins page.
	protected $_title = 'Gravity Forms Partial Entries';

	// Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
	protected $_short_title = 'Partial Entries';

	protected $_capabilities = array(
		'gravityformspartialentries_uninstall',
		'gravityformspartialentries_settings',
		'gravityformspartialentries_form_settings',
	);

	protected $_capabilities_settings_page = 'gravityformspartialentries_settings';
	protected $_capabilities_form_settings = 'gravityformspartialentries_form_settings';
	protected $_capabilities_uninstall = 'gravityformspartialentries_uninstall';
	protected $_enable_rg_autoupgrade = true;

	private static $_instance = null;

	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new GF_Partial_Entries();
		}

		return self::$_instance;
	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 1.4.2
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' );

	}

	public function init() {
		parent::init();

		add_action( 'gform_enqueue_scripts', array( $this, 'action_gform_enqueue_scripts' ), 10, 2 );
		add_filter( 'heartbeat_received', array( $this, 'filter_heartbeat_received' ), 10, 2 );
		add_filter( 'heartbeat_nopriv_received', array( $this, 'filter_heartbeat_received' ), 10, 2 );
		add_action( 'gform_entry_id_pre_save_lead', array( $this, 'filter_gform_entry_id_pre_save_lead' ), 9, 2 );
		add_action( 'gform_is_duplicate', array( $this, 'filter_gform_is_duplicate' ), 10, 4 );
		add_action( 'gform_post_process', array( $this, 'action_gform_post_process' ), 10, 3 );
	}

	public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_filter_links_entry_list', array( $this, 'filter_gform_filter_links_entry_list' ), 10, 3 );

		add_filter( 'gform_entries_field_value', array( $this, 'filter_gform_entries_field_value' ), 10, 4 );
		add_filter( 'gform_entry_info', array( $this, 'action_gform_entry_info' ), 10, 2 );

		if ( ! method_exists( 'GFForms', 'set_screen_options' ) ) {
			// Required for Gravity Forms < 2.0
			if ( $this->is_entry_list() && ! isset( $_GET['filter'] ) ) {
				$default_filter = $this->get_default_filter();
				if ( $default_filter !== 'all' ) {
					$url = add_query_arg( array( 'filter' => $default_filter ) );
					$url = esc_url_raw( $url );
					wp_safe_redirect( $url );
				}
			}
			add_filter( 'screen_settings', array( $this, 'show_screen_options' ), 10, 2 );
			add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 11, 3 );
			add_action( 'load-forms_page_gf_entries', array( $this, 'load_screen_options' ) );
			add_filter( 'gform_entry_page_size', array( $this, 'filter_gform_entry_page_size' ) );
		}

	}

	public function init_ajax() {
		parent::init_ajax();
		add_filter( 'gform_filter_links_entry_list', array( $this, 'filter_gform_filter_links_entry_list' ), 10, 3 );
	}

	/**
	 * Target for the gform_enqueue_scripts filter.
	 *
	 * @param $form
	 * @param $is_ajax
	 */
	public function action_gform_enqueue_scripts( $form, $is_ajax ) {

		$form_id = absint( $form['id'] );

		$default_warning_message = $this->get_default_warning_message();

		$feed_settings = $this->get_feed_settings( $form['id'] );
		if ( ! rgar( $feed_settings, 'enable' ) ) {
			return;
		}

		$warning_message = rgar( $feed_settings, 'warning_message' );

		if ( empty( $warning_message ) ) {
			$warning_message = $default_warning_message;
		}

		$warning_message = apply_filters( 'gform_partialentries_warning_message', $warning_message );
		$warning_message = apply_filters( 'gform_partialentries_warning_message_' . $form_id, $warning_message );

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		wp_enqueue_script( 'gf_partial_entries', $this->get_base_url() . "/js/partial-entries{$min}.js", array(
			'heartbeat',
			'jquery'
		), $this->_version, true );
		wp_localize_script( 'gf_partial_entries', 'gf_partial_entries_strings_' . $form_id, array(
			'warningMessage' => $warning_message,
		) );

		add_filter( 'gform_form_tag', array( $this, 'filter_gform_form_tag' ), 10, 2 );
	}

	/**
	 * Set up the scripts.
	 *
	 * @return array
	 */
	public function scripts() {
		$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		$scripts = array(
			array(
				'handle'  => 'google_charts',
				'src'     => 'https://www.google.com/jsapi',
				'version' => GFCommon::$version,
				'enqueue' => array(
					array( 'admin_page' => array( 'entry_list', 'entry_view' ) ),
				),
			),
			array(
				'handle'  => 'gf_partial_entries_admin',
				'src'     => $this->get_base_url() . "/js/admin{$min}.js",
				'deps'    => array( 'jquery', 'google_charts' ),
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'entry_list', 'entry_view' ) ),
				),
			),
			array(
				'handle'  => 'gf_partial_entries_feed_settings',
				'src'     => $this->get_base_url() . "/js/feed-settings{$min}.js",
				'deps'    => array( 'jquery' ),
				'version' => $this->_version,
				'enqueue' => array(
					array( 'query' => 'page=gf_edit_forms&view=settings&subview=gravityformspartialentries&id=_notempty_' ),
				),
				'strings' => array(
					'fields'         => wp_strip_all_tags( __( 'Fields', 'gravityformspartialentries' ) ),
					'allFields'      => wp_strip_all_tags( __( 'All Fields', 'gravityformspartialentries' ) ),
					'requiredFields' => wp_strip_all_tags( __( 'Required Fields', 'gravityformspartialentries' ) ),
					'progress'       => wp_strip_all_tags( __( 'Progress', 'gravityformspartialentries' ) ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the feed settings config.
	 *
	 * @return array
	 */
	function feed_settings_fields() {
		$enabled_dependency = array(
			'live'   => true,
			'fields' => array(
				array(
					'field' => 'enable_partial',
				),
			),
		);

		if ( ! $this->is_gravityforms_supported( '2.5-rc-2' ) ) {
			$enabled_dependency = array(
				'field'  => 'enable',
				'values' => array( 1 ),
			);
		}

		return array(
			array(
				'title'  => __( 'Partial Entries', 'gravityformspartialentries' ),
				'fields' => array(
					array(
						'name'     => 'enable_partial',
						'label'    => esc_html__( 'Partial Entries', 'gravityformspartialentries' ),
						'type'     => 'checkbox',
						'onclick' => ! $this->is_gravityforms_supported( '2.5-rc-2' ) ? "jQuery(this).closest('form').submit();" : null,
						'choices'  => array(
							array(
								'label' => esc_html__( 'Enable', 'gravityformspartialentries' ),
								'name'  => 'enable',
							),
						),
					),
					array(
						'name'       => 'fields_message',
						'label'      => '',
						'type'       => 'fields_message',
						'dependency' => $enabled_dependency,
					),
					array(
						'name'          => 'warning_message',
						'label'         => esc_html__( 'Warning Message', 'gravityformspartialentries' ),
						'class'         => 'fieldwidth-3 fieldheight-2',
						'type'          => 'textarea',
						'default_value' => $this->get_default_warning_message(),
						'tooltip'       => esc_html__( "In the interest of transparency and out of respect for users' privacy this notice will appear at the top of the form below the description.", 'gravityformspartialentries' ),
						'required'      => true,
						'dependency'    => $enabled_dependency,
					),
					array(
						'name'           => 'enable',
						'label'          => esc_html__( 'Conditional Logic', 'gravityformspartialentries' ),
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable', 'gravityformspartialentries' ),
						'instructions'   => esc_html__( 'Add/update partial entry if', 'gravityformspartialentries' ),
						'dependency'     => $enabled_dependency,
					),
				),
			),
		);
	}

	/**
	 * Output the threshold setting.
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function settings_threshold( $field ) {
		$percent_choices = array();
		for ( $i = 10; $i <= 100; $i += 10 ) {
			$percent_choices[] = array( 'label' => $i . '%', 'value' => $i );
		}

		$percent_field = array(
			'name'          => 'threshold_percent',
			'type'          => 'select',
			'default_value' => 10,
			'choices'       => $percent_choices,
		);

		$type_field = array(
			'name'          => 'threshold_type',
			'type'          => 'select',
			'default_value' => 'all_fields',
			'choices'       => array(
				array( 'label' => esc_html__( 'all fields', 'gravityflow' ), 'value' => 'all_fields' ),
				array( 'label' => esc_html__( 'required fields', 'gravityflow' ), 'value' => 'required_fields' ),
			),
		);

		$percent_field_html = $this->settings_select( $percent_field, false );
		$type_field_html    = $this->settings_select( $type_field, false );

		$html = sprintf( esc_html_x( '%s of %s', 'Threshold setting', 'gravityformspartialentries' ), $percent_field_html, $type_field_html );

		echo $html;

		return $html;
	}

	/**
	 * Configure entry meta.
	 *
	 * @param array $entry_meta
	 * @param int $form_id
	 *
	 * @return array
	 */
	public function get_entry_meta( $entry_meta, $form_id ) {

		$form          = GFAPI::get_form( $form_id );
		$feed_settings = $this->get_feed_settings( $form_id );
		if ( ! rgar( $feed_settings, 'enable' ) ) {
			return $entry_meta;
		}

		$entry_meta['partial_entry_id'] = array(
			'label'             => __( 'Partial Entry ID', 'gravityformspartialentries' ),
			'is_numeric'        => false,
			'is_default_column' => false,
		);

		$entry_meta['partial_entry_percent'] = array(
			'label'                      => __( 'Progress', 'gravityformspartialentries' ),
			'is_numeric'                 => true,
			'is_default_column'          => true,
			'update_entry_meta_callback' => array( $this, 'callback_update_partial_entry_percent_entry_meta' ),
			'filter'                     => array(
				'operators' => array( 'is', 'isnot', '>' ),
				'choices'   => array(
					array( 'text' => 'Complete', 'value' => '', 'operators' => array( 'is', 'isnot' ) ),
					array( 'text' => '30%', 'value' => '30', 'operators' => array( '>' ) ),
					array( 'text' => '60%', 'value' => '60', 'operators' => array( '>' ) ),
				),
			),
		);

		$entry_meta['required_fields_percent_complete'] = array(
			'label'                      => esc_html__( 'Progress: required fields', 'gravityformspartialentries' ),
			'is_numeric'                 => true,
			'is_default_column'          => false,
			'update_entry_meta_callback' => array( $this, 'callback_update_partial_entry_percent_entry_meta' ),
		);


		if ( rgars( $form, 'save/enabled' ) ) {
			$entry_meta['resume_token'] = array(
				'label'             => __( 'Save and Continue Token', 'gravityformspartialentries' ),
				'is_numeric'        => false,
				'is_default_column' => false,
			);
			$entry_meta['resume_url']   = array(
				'label'             => __( 'Save and Continue URL', 'gravityformspartialentries' ),
				'is_numeric'        => false,
				'is_default_column' => false,
			);
			$entry_meta['date_saved']   = array(
				'label'             => __( 'Saved', 'gravityformspartialentries' ),
				'is_numeric'        => false,
				'is_default_column' => true,
			);
		}

		return $entry_meta;
	}

	/**
	 * Target for the callback_update_partial_entry_percent_entry_meta callback.
	 *
	 * @param $key
	 * @param $entry
	 * @param $form
	 *
	 * @return string
	 */
	public function callback_update_partial_entry_percent_entry_meta( $key, $entry, $form ) {
		return '';
	}


	/**
	 * Saves the entry if the conditions are met.
	 *
	 * @param $form_id
	 *
	 * @return bool|string
	 */
	function maybe_save_partial_entry( $form_id ) {

		if ( ! isset( $_POST['partial_entry_id'] ) ) {
			return false;
		}

		require_once( GFCommon::get_base_path() . '/form_display.php' );

		$this->log_debug( __METHOD__ . '() Running.' );

		$partial_entry_id = sanitize_key( rgpost( 'partial_entry_id' ) );

		$form = GFAPI::get_form( $form_id );

		// Ensure that the honeypot field id is calculated correctly when the gform_review_page filter has been used to add the extra page and html fields.
		$form = GFFormDisplay::maybe_add_review_page( $form );

		$form = apply_filters( 'gform_pre_render', $form, false, array() );
		$form = apply_filters( 'gform_pre_render_' . $form['id'], $form, false, array() );

		// create lead
		// Save uuid with entry

		$failed_honeypot = false;

		// Get the honeypot field if enabled and check if it's empty.
		if ( rgar( $form, 'enableHoneypot' ) ) {
			$honeypot_id     = GFFormDisplay::get_max_field_id( $form ) + 1;
			$failed_honeypot = ! rgempty( "input_{$honeypot_id}" );
		}

		// If honeypot contains a value, don't save partial entry.
		if ( $failed_honeypot ) {
			$this->log_debug( __METHOD__ . '() Aborting: Honeypot Validation Failed' );

			return false;
		}

		$partial_entry = $this->create_partial_entry( $form );

		if ( empty( $partial_entry ) ) {
			return false;
		}

		if ( $partial_entry['partial_entry_percent'] == 0 ) {
			$this->log_debug( __METHOD__ . '() Aborting; 0% completed.' );

			return false;
		}

		$notification_event = 'partial_entry_saved';

		if ( empty( $partial_entry_id ) || $partial_entry_id == 'pending' ) {
			$partial_entry_id                  = GFFormsModel::get_uuid();
			$partial_entry['partial_entry_id'] = $partial_entry_id;
			$result                            = GFAPI::add_entry( $partial_entry );

			if ( is_wp_error( $result ) ) {
				$message = 'Error: ' . $result->get_error_message();
			} else {
				$partial_entry['id'] = $result;
				$message             = "Success. partial_entry_id = {$partial_entry_id}. Entry ID = {$result}.";
			}
			$this->log_debug( __METHOD__ . '(): Saving new partial entry. ' . $message );
		} else {

			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array(
					array( 'key' => 'partial_entry_id', 'value' => $partial_entry_id ),
				),
			);

			$entries = GFAPI::get_entries( $form_id, $search_criteria );

			$partial_entry['partial_entry_id'] = $partial_entry_id;

			if ( empty( $entries ) ) {
				$this->log_debug( __METHOD__ . '(): Partial entry not found.' );

				//$partial_entry_id = GFFormsModel::get_uuid();
				$result = GFAPI::add_entry( $partial_entry );

				if ( is_wp_error( $result ) ) {
					$message = 'Error: ' . $result->get_error_message();
				} else {
					$partial_entry['id'] = $result;
					$message             = "Success. partial_entry_id = {$partial_entry_id}. Entry ID = {$result}.";
				}
				$this->log_debug( __METHOD__ . '(): Saving new partial entry. ' . $message );
			} else {
				$notification_event  = 'partial_entry_updated';
				$saved_entry         = $entries[0];
				$partial_entry['id'] = $saved_entry['id'];
				$partial_entry       = $this->filter_partial_entry_before_update( $partial_entry, $saved_entry, $form );
				$result              = GFAPI::update_entry( $partial_entry );

				if ( is_wp_error( $result ) ) {
					$message = 'Error: ' . $result->get_error_message();
				} else {
					$message = "Success. Entry ID = {$partial_entry['id']}.";
				}
				$this->log_debug( __METHOD__ . '(): Updating existing partial entry. ' . $message );
			}
		}

		if ( ! is_wp_error( $result ) && $result ) {
			GFAPI::send_notifications( $form, $partial_entry, $notification_event );

			$event = str_replace( 'partial_', '', $notification_event );

			/**
			 * Perform a custom action when the partial entry has been saved or updated.
			 *
			 * The dynamic part of the hook name contains the event which occurred; entry_saved or entry_updated.
			 *
			 * @param array $partial_entry The partial entry object.
			 * @param array $form The form object used to process this partial entry.
			 *
			 * @see https://www.gravityhelp.com/documentation/article/gform_partialentries_post_event/
			 */
			do_action( "gform_partialentries_post_{$event}", $partial_entry, $form );
		}

		return $partial_entry_id;
	}

	/**
	 * Allow overriding the partial entry values before updating.
	 *
	 * @since 1.6.1
	 *
	 * @param array $partial_entry The partial entry array that contains new values.
	 * @param array $saved_entry   The original entry.
	 * @param array $form          The current form being processed.
	 *
	 * @return array The filtered partial entry array.
	 */
	public function filter_partial_entry_before_update( $partial_entry, $saved_entry, $form ) {
		/**
		 * Filters partial entry values before update.
		 *
		 * @since 1.6.1
		 *
		 * @param array $partial_entry The partial entry array that contains new values.
		 * @param array $saved_entry   The original entry.
		 * @param array $form          The current form being processed.
		 */
		return gf_apply_filters( array( 'gform_partialentries_pre_update', $form['id'] ), $partial_entry, $saved_entry, $form );
	}

	/**
	 * Creates a partial entry - fields that fail validation will return empty values.
	 *
	 * @param $form
	 *
	 * @return array|false The partial entry
	 */
	public function create_partial_entry( $form ) {

		$form_id = absint( $form['id'] );

		$feed_settings = $this->get_feed_settings( $form_id );
		$enabled       = rgar( $feed_settings, 'enable' );
		if ( ! $enabled ) {
			$this->log_debug( __METHOD__ . '() Aborting; Not enabled.' );

			return false;
		}

		$fields_completed          = 0;
		$required_fields_completed = 0;
		$total_fields              = 0;
		$required_fields           = 0;

		$unset_keys   = array();
		$ignore_types = array(
			'fileupload',
			'creditcard',
		);

		foreach ( $form['fields'] as $key => $field ) {
			/* @var GF_Field $field */
			if ( $field->type === 'page' || $field->type === 'section' ) {
				// Keeping the field so the logic can be evaluated but omitting it from the counts.
				continue;
			} elseif ( $field->displayOnly || in_array( $field->get_input_type(), $ignore_types ) ) {
				$unset_keys[] = $key;
			} else {
				$total_fields ++;
				if ( $field->isRequired ) {
					$required_fields ++;
				}
				if ( $field->noDuplicates ) {
					$field->noDuplicates = false;
				}
			}
		}

		if ( $total_fields == 0 ) {
			$this->log_debug( __METHOD__ . '() Aborting; No applicable fields to save.' );

			return false;
		}

		foreach ( $unset_keys as $key ) {
			unset( $form['fields'][ $key ] );
		}

		$form['fields'] = array_values( $form['fields'] );

		if ( rgpost( 'action' ) == 'heartbeat' ) {
			$this->log_debug( __METHOD__ . '(): is heartbeat; running GFFormDisplay::validate().' );
			$page_number = GFFormDisplay::get_source_page( $form['id'] );
			GFFormDisplay::validate( $form, array(), $page_number, $page_number );
		}

		$entry = GFFormsModel::create_lead( $form );

		foreach ( $form['fields'] as $field ) {

			if ( $field->displayOnly || $field->isRequired && $field->is_value_submission_empty( $form_id ) ) {
				continue;
			}
			/* @var GF_Field $field */
			$inputs = in_array( $field->get_input_type(), array(
				'date',
				'time',
			) ) ? $field->inputs : $field->get_entry_inputs();
			if ( $field->failed_validation ) {
				if ( is_array( $inputs ) ) {
					foreach ( $inputs as $input ) {
						if ( isset( $entry[ $input['id'] ] ) ) {
							$entry[ $input['id'] ] = '';
						}
					}
				} else {
					if ( isset( $entry[ (string) $field->id ] ) ) {
						$entry[ (string) $field->id ] = '';
					}
				}
			} else {
				if ( is_array( $inputs ) ) {
					foreach ( $inputs as $input ) {
						$value = GFFormsModel::get_prepared_input_value( $form, $field, $entry, $input['id'] );
						if ( ! empty( $value ) ) {
							$fields_completed ++;
							if ( $field->isRequired ) {
								$required_fields_completed ++;
							}
							continue 2;
						}
					}
				} else {
					if ( isset( $entry[ (string) $field->id ] ) && ! empty( $entry[ (string) $field->id ] ) ) {
						$fields_completed ++;
						if ( $field->isRequired ) {
							$required_fields_completed ++;
						}
					}
				}
			}
		}

		$entry['required_fields_percent_complete'] = ( $required_fields > 0 ) ? (int) ( $required_fields_completed / $required_fields * 100 ) : 0;
		$entry['partial_entry_percent']            = (int) ( $fields_completed / $total_fields * 100 );

		$feed_id = $this->get_default_feed_id( $form_id );
		$feed    = $this->get_feed( $feed_id );

		if ( ! $this->is_feed_condition_met( $feed, $form, $entry ) ) {
			$this->log_debug( __METHOD__ . '() Aborting; Condition not met.' );

			return false;
		}

		if ( rgars( $form, 'save/enabled' ) && $resume_token = rgpost( 'gform_resume_token' ) ) {
			$entry['resume_token'] = $resume_token;
			$saved_entry_details   = GFFormsModel::get_draft_submission_values( $resume_token );
			$source_url            = $saved_entry_details['source_url'];
			$source_url            = add_query_arg( array( 'gf_token' => $resume_token ), $source_url );
			$resume_url            = esc_url_raw( $source_url );
			$entry['resume_url']   = $resume_url;
			$entry['date_saved']   = $saved_entry_details['date_created'];
		}

		$entry_meta = GFFormsModel::get_entry_meta( $form_id );
		if ( is_array( $entry_meta ) ) {
			$keys_to_skip = array( 'required_fields_percent_complete', 'partial_entry_percent' );
			foreach ( $entry_meta as $key => $item ) {
				if ( ! in_array( $key, $keys_to_skip ) && isset( $item['update_entry_meta_callback'] ) ) {
					$entry[ $key ] = call_user_func_array( $item['update_entry_meta_callback'], array(
						$key,
						$entry,
						$form
					) );
				}
			}
		}

		return $entry;

	}

	/**
	 * Target for the gform_entries_field_value filter. Returns the value for the entry list.
	 *
	 * @param $value
	 * @param $form_id
	 * @param $field_id
	 * @param $entry
	 *
	 * @return string
	 */
	public function filter_gform_entries_field_value( $value, $form_id, $field_id, $entry ) {

		switch ( $field_id ) {
			case 'partial_entry_percent' :
			case 'required_fields_percent_complete' :
				$partial_entry_id = rgar( $entry, 'partial_entry_id' );
				if ( empty( $partial_entry_id ) ) {
					$html = '<i class="fa fa-check gf_valid"></i>';
				} else {
					$html = sprintf( '<div title="%d%%" id="gform_partial_entry_percent_%d" data-percentage="%d" class="gform_partial_entry_percent" style="width: 25px; height: 25px; display:inline-block;"></div>', $value, $entry['id'], $value );
				}
				break;
			case 'date_saved' :
				if ( empty( $value ) ) {
					$html = $value;

				} else {
					$date = GFCommon::format_date( $value );
					$link = empty( $entry['resume_url'] ) ? '' : sprintf( '<a target="_blank" href="%s" title="%s"><i class="fa fa-save"></i></a>', rgar( $entry, 'resume_url' ), esc_html__( 'Open saved form', 'gravityforms' ) );
					$html = $link . ' ' . $date;
				}

				break;
			default :
				$html = $value;

		}

		return $html;
	}

	/**
	 * Target for the gform_entry_id_pre_save_lead filter. If a partial entry exists use its ID and delete partial entry meta.
	 *
	 * @param $entry_id
	 * @param $form
	 *
	 * @return int
	 */
	public function filter_gform_entry_id_pre_save_lead( $entry_id, $form ) {
		$partial_entry_id = sanitize_key( rgpost( 'partial_entry_id' ) );

		if ( ! empty( $partial_entry_id ) ) {
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array(
					array( 'key' => 'partial_entry_id', 'value' => $partial_entry_id ),
				),
			);

			$entries = GFAPI::get_entries( $form['id'], $search_criteria );
			if ( count( $entries ) > 0 ) {
				$entry    = $entries[0];
				$entry_id = absint( $entry['id'] );
				$result   = GFAPI::update_entry( array( 'id' => $entry_id, 'currency' => $entry['currency'] ) );
				$this->log_debug( __METHOD__ . '() update result: ' . print_r( $result, true ) );
				gform_delete_meta( $entry_id, 'partial_entry_id' );
				gform_delete_meta( $entry_id, 'date_saved' );
				gform_delete_meta( $entry_id, 'resume_url' );
				gform_delete_meta( $entry_id, 'resume_token' );

				// Flush the cached field display states so conditional logic is re-evaluated using the posted values.
				GFCache::flush();
				add_filter( 'gform_use_post_value_for_conditional_logic_save_entry', '__return_true' );
			}
		}

		return $entry_id;
	}

	/**
	 * Target for the heartbeat_received filter. Handles the transport of the partial entry data.
	 *
	 * @param $response
	 * @param $data
	 *
	 * @return mixed
	 */
	public function filter_heartbeat_received( $response, $data ) {
		if ( isset( $data['gf-partial_entries-heartbeat'] ) ) {
			$orginal_post                       = $_POST;
			$forms_values                       = json_decode( $data['gf-partial_entries-heartbeat'], true );
			$response['gf-partial-entries-ids'] = array();
			foreach ( $forms_values as $form_id => $form_values ) {
				$post_values = array();
				foreach ( $form_values as $form_value ) {
					$key = $form_value['name'];
					if ( strpos( $key, '.' ) !== false ) {
						$key = str_replace( '.', '_', $key );
					}
					if ( strpos( $key, '[]' ) !== false ) {
						$key = str_replace( '[]', '', $key );
						if ( ! isset( $post_values[ $key ] ) ) {
							$post_values[ $key ] = array();
						}
						$post_values[ $key ][] = $form_value['value'];
					} else {
						$post_values[ $key ] = $form_value['value'];
					}
				}
				$_POST            = array_merge_recursive( $orginal_post, $post_values );
				$partial_entry_id = $this->maybe_save_partial_entry( $form_id );
				if ( ! empty( $partial_entry_id ) ) {
					$response['gf-partial-entries-ids'][ $form_id ] = $partial_entry_id;
				}
			}
		}

		return $response;
	}

	/**
	 * Target for the gform_entry_info action. Displays the progress information on the entry detail page.
	 *
	 * @param $form_id
	 * @param $entry
	 */
	public function action_gform_entry_info( $form_id, $entry ) {
		if ( ! $this->is_enabled( $form_id ) ) {
			return;
		}

		$partial_entry_id = rgar( $entry, 'partial_entry_id' );
		if ( empty( $partial_entry_id ) ) {
			$progress = '<i class="fa fa-check gf_valid"></i>';
		} else {
			$progress = sprintf( '<div title="%d%%" id="gform_partial_entry_percent_%d" data-percentage="%d" class="gform_partial_entry_percent" style="width: 25px; height: 25px; display:inline-block;position:relative;top:7px;"></div>', $entry['partial_entry_percent'], $entry['id'], $entry['partial_entry_percent'] );
		}
		printf( esc_html__( 'Progress: %s', 'gravityformspartialentries' ), $progress );

		$date_saved = rgar( $entry, 'date_saved' );

		if ( ! empty( $date_saved ) ) {
			$date = GFCommon::format_date( $date_saved );
			$link = empty( $entry['resume_url'] ) ? '' : sprintf( '<a target="_blank" href="%s" title="%s"><i class="fa fa-save"></i></a>', rgar( $entry, 'resume_url' ), esc_html__( 'Open saved form', 'gravityforms' ) );
			echo '<div style="margin-top:10px;">' . sprintf( esc_html__( 'Last Saved: %s ', 'gravityformspartialentries' ), $date ) . $link . '</div>';
		}
	}

	/**
	 * Target for the gform_form_tag filter. Adds the partial entry ID as a hidden field.
	 *
	 * @param $form_tag
	 * @param $form
	 *
	 * @return string
	 */
	public function filter_gform_form_tag( $form_tag, $form ) {
		$feed_settings = $this->get_feed_settings( $form['id'] );
		if ( rgar( $feed_settings, 'enable' ) ) {
			if ( $token = rgget( 'gf_token' ) ) {
				$search_criteria = array(
					'field_filters' => array(
						array( 'key' => 'resume_token', 'value' => $token ),
						array( 'key' => 'partial_entry_id', 'operator' => 'isnot', 'value' => '' )
					),
				);
				$entries         = GFAPI::get_entries( $form['id'], $search_criteria );

				$partial_entry_id = isset( $entries[0] ) ? $entries[0]['partial_entry_id'] : 'pending';
			} else {
				$partial_entry_id = ! empty( $_POST['partial_entry_id'] ) ? $_POST['partial_entry_id'] : 'pending';
			}

			$partial_entry_id = esc_attr( $partial_entry_id );
			$form_id          = absint( $form['id'] );
			$form_tag         .= sprintf( '<input id=partial_entry_id_%d class="partial_entry_id" type=hidden name="partial_entry_id" value="%s" data-form_id="%d"/>', $form_id, esc_attr( $partial_entry_id ), $form_id );
		}

		return $form_tag;
	}

	/**
	 * Target for the gform_filter_links_entry_list filter. Adds the config for the filters on the entry list.
	 *
	 * @param $filter_links
	 * @param $form
	 * @param $include_counts
	 *
	 * @return mixed
	 */
	public function filter_gform_filter_links_entry_list( $filter_links, $form, $include_counts ) {
		if ( ! $this->is_enabled( $form['id'] ) ) {
			return $filter_links;
		}

		$form_id = absint( $form['id'] );

		$complete_count = 0;
		$partial_count  = 0;

		if ( $include_counts ) {
			global $wpdb;
			$lead_table_name       = self::get_entry_table_name();
			$lead_detail_meta_name = self::get_entry_meta_table_name();

			$entry_id_column = version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? 'lead_id' : 'entry_id';

			$sql            = $wpdb->prepare(
				"SELECT
                    (SELECT count(DISTINCT(l.id)) FROM $lead_table_name l LEFT JOIN $lead_detail_meta_name m ON l.id=m.{$entry_id_column} AND m.meta_key = 'partial_entry_percent' WHERE l.form_id=%d AND l.status='active' AND ( m.meta_value = '' OR m.meta_value IS NULL ) ) as complete,
                    (SELECT count(DISTINCT(l.id)) FROM $lead_table_name l INNER JOIN $lead_detail_meta_name m ON l.id=m.{$entry_id_column} WHERE l.form_id=%d AND l.status='active' AND m.meta_key = 'partial_entry_percent' AND m.meta_value > 1 ) as partial
					",
				$form_id, $form_id
			);
			$results        = $wpdb->get_results( $sql, ARRAY_A );
			$complete_count = $results[0]['complete'];
			$partial_count  = $results[0]['partial'];
		}

		$new_filter_links = array(
			array(
				'id'            => 'complete',
				'count'         => $complete_count,
				'label'         => esc_html_x( 'Complete', 'Entry List', 'gravityforms' ),
				'field_filters' => array(
					array(
						'key'   => 'partial_entry_percent',
						'value' => '',
					),
				),
			),
			array(
				'id'            => 'partial',
				'count'         => $partial_count,
				'label'         => esc_html_x( 'Partial', 'Entry List', 'gravityforms' ),
				'field_filters' => array(
					array(
						'key'      => 'partial_entry_percent',
						'operator' => '>',
						'value'    => 1,
					),
				),
			),
		);

		array_splice( $filter_links, 1, 0, $new_filter_links );

		return $filter_links;
	}

	/**
	 * Is Partial Entries enabled for the given form?
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_enabled( $form_id ) {
		$feed_settings = $this->get_feed_settings( $form_id );

		return (bool) rgar( $feed_settings, 'enable' );
	}

	/**
	 * Displays the screen options on the entry list for Gravity Forms < 2.0
	 *
	 * @param $status
	 * @param $args
	 *
	 * @return string
	 */
	public function show_screen_options( $status, $args ) {

		$return = $status;
		if ( $args->base == 'forms_page_gf_entries' ) {

			if ( ! $this->is_entry_list() ) {
				return $return;
			}

			$screen_options = $this->get_screen_options();

			$per_page = $screen_options['per_page'];

			$filters = $this->get_filter_links();

			$selected_filter = $this->get_default_filter();

			$radios_arr = array();
			foreach ( $filters as $filter ) {
				$id           = esc_attr( $filter['id'] );
				$label        = esc_attr( $filter['label'] );
				$checked      = checked( $filter['id'], $selected_filter, false );
				$radios_arr[] = sprintf( '<input type="radio" name="gform_default_filter" value="%s" id="gform_default_filter_%s" %s /><label for="gform_default_filter_%s">%s</label>', $id, $id, $checked, $id, $label );
			}

			$radios_str = join( "\n", $radios_arr );

			$filter_title  = esc_html__( 'Default Filter', 'gravityformspartialentries' );
			$entries_label = esc_html__( 'Number of entries per page:', 'gravityformspartialentries' );

			$button = get_submit_button( esc_html__( 'Apply', 'gravityformspartialentries' ), 'button', 'screen-options-apply', false );
			$return .= "
            <h5>{$filter_title}</h5>
            <div class='metabox-prefs'>
            <div>
				{$radios_str}
            </div>
            </div>
            <div class='screen-options'>
            	<label for='gform_per_page%s'>{$entries_label}</label>
            	<input type='number' step='1' min='1' max='100' class='screen-per-page' name='gform_per_page'
					id='gform_per_page' maxlength='3' value='{$per_page}' />
            	<input type='hidden' name='wp_screen_options[option]' value='gform_entries_screen_options' />
            	<input type='hidden' name='wp_screen_options[value]' value='yes' />
			</div>

            <br class='clear'>
            $button";
		}

		return $return;

	}

	/**
	 * Sets the screen options for the entry list on Gravity Forms < 2.0
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return array
	 */
	function set_screen_options( $status, $option, $value ) {
		$return = $value;
		if ( 'gform_entries_screen_options' == $option ) {
			$return                   = array();
			$return['default_filter'] = $_POST['gform_default_filter'];
			$return['per_page']       = $_POST['gform_per_page'];
		}

		return $return;
	}

	/**
	 * Target for the gform_entry_page_size filter.
	 * Required for the entry list on Gravity Forms < 2.0
	 *
	 * @param $per_page
	 *
	 * @return mixed
	 */
	public function filter_gform_entry_page_size( $per_page ) {

		$option_values = $this->get_screen_options();

		return $option_values['per_page'];
	}

	/**
	 * Returns the filter links for the entry list. Required for Gravity Forms < 2.0.
	 *
	 * @return array|mixed|string|void
	 */
	public function get_filter_links() {
		$forms   = RGFormsModel::get_forms( null, 'title' );
		$form_id = rgget( 'id' );

		if ( sizeof( $forms ) == 0 ) {
			return '';
		} else {
			if ( empty( $form_id ) ) {
				$form_id = $forms[0]->id;
			}
		}

		$form = GFAPI::get_form( $form_id );

		require_once( GFCommon::get_base_path() . '/entry_list.php' );

		$filters = GFEntryList::get_filter_links( $form, false );

		return $filters;
	}

	/**
	 * Returns the default filter. Required for Gravity Forms < 2.0.
	 *
	 * @return string
	 */
	public function get_default_filter() {

		$filters = $this->get_filter_links();

		$option_values = $this->get_screen_options();

		// If the filter is not available for the form then use 'all'
		$selected_filter = 'all';
		foreach ( $filters as $filter ) {
			if ( $option_values['default_filter'] == $filter['id'] ) {
				$selected_filter = $option_values['default_filter'];
				break;
			}
		}

		return $selected_filter;
	}

	/**
	 * Returns the screen options for the entry list. Required for Gravity Forms < 2.0.
	 *
	 * @return array|mixed
	 */
	public function get_screen_options() {
		$default_values = array(
			'per_page'       => 20,
			'default_filter' => 'all',
		);

		$option_values = get_user_option( 'gform_entries_screen_options' );

		if ( empty( $option_values ) ) {
			$option_values = array();
		}
		$option_values = array_merge( $default_values, $option_values );

		return $option_values;

	}

	/**
	 * Returns the feed settings.
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	public function get_feed_settings( $form_id ) {
		$feed_id = $this->get_default_feed_id( $form_id );
		if ( ! $feed_id ) {
			return array();
		}

		$feed = $this->get_feed( $feed_id );

		$feed_settings = $feed['meta'];

		return $feed_settings;
	}

	/**
	 * A dedicated feed condition setting which includes some custom values as entry meta.
	 *
	 * @param $field
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function settings_feed_condition( $field, $echo = true ) {

		$checkbox_label = isset( $field['checkbox_label'] ) ? $field['checkbox_label'] : esc_html__( 'Enable Condition', 'gravityforms' );

		$checkbox_field           = array(
			'name'    => 'feed_condition_conditional_logic',
			'type'    => 'checkbox',
			'choices' => array(
				array(
					'label' => $checkbox_label,
					'name'  => 'feed_condition_conditional_logic',
				),
			),
			'onclick' => 'ToggleConditionalLogic( false, "feed_condition" );',
		);
		$conditional_logic_object = $this->get_setting( 'feed_condition_conditional_logic_object' );
		$form_id                  = rgget( 'id' );
		$form                     = GFFormsModel::get_form_meta( $form_id );
		if ( $conditional_logic_object ) {
			$conditional_logic = json_encode( GFFormsModel::trim_conditional_logic_values_from_element( $conditional_logic_object, $form ) );
		} else {
			$conditional_logic = '{}';
		}

		$hidden_field = array(
			'name'  => 'feed_condition_conditional_logic_object',
			'value' => $conditional_logic,
		);
		$instructions = isset( $field['instructions'] ) ? $field['instructions'] : esc_html__( 'Process this feed if', 'gravityforms' );
		$html         = $this->settings_checkbox( $checkbox_field, false );
		$html         .= $this->settings_hidden( $hidden_field, false );
		$html         .= '<div id="feed_condition_conditional_logic_container"><!-- dynamically populated --></div>';

		$percent_choices = array();
		for ( $i = 10; $i <= 100; $i += 10 ) {
			$percent_choices[] = array( 'text' => $i . '%', 'value' => $i );
		}

		$entry_meta['partial_entry_percent'] = array(
			'label'  => __( 'Progress: all fields', 'gravityflow' ),
			'filter' => array(
				'operators' => array( '>', 'is' ),
				'choices'   => $percent_choices,
			),
		);

		$entry_meta['required_fields_percent_complete'] = array(
			'label'  => esc_html__( 'Progress: required fields', 'gravityflow' ),
			'filter' => array(
				'operators' => array( '>', 'is' ),
				'choices'   => $percent_choices,
			),
		);

		$html .= '<script type="text/javascript">' .
		         'var entry_meta = ' . json_encode( $entry_meta ) . ';' .
		         'var feedCondition = new FeedConditionObj({' .
		         'strings: { objectDescription: "' . esc_attr( $instructions ) . '" },' .
		         'logicObject: ' . $conditional_logic .
		         '}); </script>';

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Target for the gform_post_process action. Saves a partial entry if conditions are met.
	 *
	 * @param $form
	 * @param $page_number
	 * @param $source_page_number
	 */
	function action_gform_post_process( $form, $page_number, $source_page_number ) {
		$submission_info = GFFormDisplay::$submission[ $form['id'] ];

		$saved_for_later = rgar( $submission_info, 'saved_for_later' );

		// Save partial entry on validation failure, save (and continue) and page change - not on submission.
		if ( ! rgar( $submission_info, 'is_valid' ) || $saved_for_later || $page_number > 0 ) {

			if ( $saved_for_later ) {
				$_POST['gform_resume_token'] = rgar( $submission_info, 'resume_token' );
			}

			$partial_entry_id = $this->maybe_save_partial_entry( $form['id'] );

			if ( ! empty( $partial_entry_id ) ) {
				$_POST['partial_entry_id'] = $partial_entry_id;
			}
		}
	}

	/**
	 * Returns the default warning message to display above the form.
	 *
	 * @return string
	 */
	function get_default_warning_message() {
		return esc_html__( 'Please note that your information is saved on our server as you enter it.', 'gravityformspartialentries' );
	}

	/**
	 * Custom setting to display warning messages if certain field types exist on the form.
	 *
	 * @param $field
	 */
	function settings_fields_message( $field ) {
		$form_id = rgget( 'id' );
		$form    = GFAPI::get_form( $form_id );
		$fields  = $form['fields'];
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return;
		}
		$display_fileupload_warning = $display_creditcard_warning = false;
		/* @var GF_Field[] $fields */
		foreach ( $fields as $field ) {
			$input_type = $field->get_input_type();
			if ( $input_type == 'fileupload' ) {
				$display_fileupload_warning = true;
			}
			if ( $input_type == 'creditcard' ) {
				$display_creditcard_warning = true;
			}
		}

		if ( $display_fileupload_warning ) {
			?>
            <div class="delete-alert alert_red"><i class="fa fa-exclamation-triangle gf_invalid"></i>
				<?php esc_html_e( 'File Uploads will not be included in the partial entries.', 'gravityformspartialentries' ); ?>
            </div>
			<?php
		}

		if ( $display_creditcard_warning ) {
			?>
            <div class="delete-alert alert_red"><i class="fa fa-exclamation-triangle gf_invalid"></i>
				<?php esc_html_e( 'The Credit Card field values will not be included in the partial entries.', 'gravityformspartialentries' ); ?>
            </div>
			<?php
		}
	}

	/**
	 * Add supported notification events.
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return array
	 */
	public function supported_notification_events( $form ) {

		if ( ! $this->is_enabled( $form['id'] ) ) {
			return false;
		}

		return array(
			'partial_entry_saved'   => esc_html__( 'Partial Entries: Saved', 'gravityformspartialentries' ),
			'partial_entry_updated' => esc_html__( 'Partial Entries: Updated', 'gravityformspartialentries' ),
		);
	}

	/**
	 * Callback for the gform_is_duplicate filter.
	 *
	 * @param $is_duplicate
	 * @param $form_id
	 * @param $field
	 * @param $value
	 *
	 * @return bool
	 */
	public function filter_gform_is_duplicate( $is_duplicate, $form_id, $field, $value ) {

		if ( empty( $_POST['partial_entry_id'] ) ) {
			return $is_duplicate;
		}

		if ( ! $this->is_enabled( $form_id ) ) {
			return $is_duplicate;
		}


		// Lookup entry id

		switch ( GFFormsModel::get_input_type( $field ) ) {
			case 'time':
				$value = sprintf( "%02d:%02d %s", $value[0], $value[1], $value[2] );
				break;
			case 'date':
				$value = GFFormsModel::prepare_date( $field->dateFormat, $value );
				break;
			case 'number':
				$value = GFCommon::clean_number( $value, $field->numberFormat );
				break;
			case 'phone':
				$value = str_replace( array( ')', '(', '-', ' ' ), '', $value );
				break;
			case 'email':
				$value = is_array( $value ) ? rgar( $value, 0 ) : $value;
				break;
		}

		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
				array(
					'key'      => $field->id,
					'operator' => '=',
					'value'    => $value,
				),
				$search_criteria['field_filters'][] = array(
					'key'      => 'partial_entry_percent',
					'operator' => '=',
					'value'    => '',
				),
			),
		);

		$entries = GFAPI::get_entries( $form_id, $search_criteria );

		if ( empty( $entries ) ) {
			return false;
		}

		return $is_duplicate;
	}

	/**
	 * Returns the table name for the entry headers.
	 *
	 * @return string
	 * @since 1.0.8
	 *
	 */
	public static function get_entry_table_name() {
		return version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? GFFormsModel::get_lead_table_name() : GFFormsModel::get_entry_table_name();
	}

	/**
	 * Returns the table name for the entry meta.
	 *
	 * @return string
	 * @since 1.0.8
	 *
	 */
	public static function get_entry_meta_table_name() {
		return version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? GFFormsModel::get_lead_meta_table_name() : GFFormsModel::get_entry_meta_table_name();
	}

	/**
	 * Returns the current version of the Gravity Forms database.
	 *
	 * @return string
	 * @since 1.0.8
	 *
	 */
	public static function get_gravityforms_db_version() {

		if ( method_exists( 'GFFormsModel', 'get_database_version' ) ) {
			$db_version = GFFormsModel::get_database_version();
		} else {
			$db_version = GFForms::$version;
		}

		return $db_version;
	}
}
