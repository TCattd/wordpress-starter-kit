<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_addon_framework();

class GFSignature extends GFAddOn {

	protected $_version = GF_SIGNATURE_VERSION;
	protected $_min_gravityforms_version = '1.9.14';
	protected $_slug = 'gravityformssignature';
	protected $_path = 'gravityformssignature/signature.php';
	protected $_full_path = __FILE__;
	protected $_url = 'http://www.gravityforms.com';
	protected $_title = 'Gravity Forms Signature Add-On';
	protected $_short_title = 'Signature';
	protected $_enable_rg_autoupgrade = true;

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSignature
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFSignature();
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
			require_once 'includes/class-gf-signature-image.php';
			require_once 'includes/class-gf-field-signature.php';
			add_action( 'parse_request', array( $this, 'display_signature' ) );
		}
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();

		add_filter( 'gform_merge_tag_filter', array( $this, 'merge_tag_filter' ), 10, 5 );
		add_action( 'gform_delete_lead', array( $this, 'delete_entry' ) );
		add_action( 'gform_delete_entries', array( $this, 'delete_entries' ), 10, 2 );
		add_action( 'gravityforms_cron', array( $this, 'add_index_file' ) );

	}

	/**
	 * Initialize the admin specific hooks.
	 */
	public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_appearance_settings', array( $this, 'field_settings' ), 10, 2 );
	}

	/**
	 * Initialize the AJAX hooks.
	 */
	public function init_ajax() {
		parent::init_ajax();

		add_action( 'wp_ajax_gf_delete_signature', array( $this, 'ajax_delete_signature' ) );
	}

	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 3.9.1
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' );

	}

	/**
	 * Enqueue styles.
	 *
	 * @since 4.4
	 * @return array $styles
	 */
	public function styles() {

		$base_url = $this->get_base_url();
		$min      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'  => 'gform_signature_foundation',
				'src'     => $base_url . "/assets/css/dist/theme{$min}.css",
				'version' => $this->_version,
				'enqueue' => array(
					array( $this, 'should_enqueue_frontend_script' ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}


	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {

		$min  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		$form = $this->get_current_form();

		$scripts = array(
			array(
				'handle'  => 'gform_masked_input',
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				)
			),
			array(
				'handle'    => 'super_signature_script',
				'src'       => $this->get_base_url() . '/includes/super_signature/ss.js',
				'version'   => $this->_version,
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
				'enqueue'   => array(
					array( $this, 'should_enqueue_frontend_script' ),
				),
			),
			array(
				'handle'  => 'super_signature_base64',
				'src'     => $this->get_base_url() . '/includes/super_signature/base64.js',
				'version' => $this->get_version(),
				'enqueue'   => array(
					array( $this, 'should_enqueue_frontend_script' ),
				),
			),
			array(
				'handle'    => 'gform_signature_frontend',
				'src'       => $this->get_base_url() . "/js/frontend{$min}.js",
				'version'   => $this->get_version(),
				'deps'      => array( 'jquery', 'super_signature_script', 'super_signature_base64' ),
				'in_footer' => true,
				'enqueue'   => array(
					array( $this, 'should_enqueue_frontend_script' ),
				),
				'strings'   => array(
					'lockedReset' => wp_strip_all_tags( __( 'Reset to re-sign.', 'gravityformssignature' ) ),
				),
			),
			array(
				'handle'    => 'gform_signature_delete_signature',
				'src'       => $this->get_base_url() . "/js/gfsignature_delete_signature{$min}.js",
				'version'   => $this->get_version(),
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
				'enqueue' => array(
					array( $this, 'should_enqueue_delete_script' ),
				),
				'strings'   => array(
					'confirm_delete' => esc_html__( "Would you like to delete this file? 'Cancel' to stop. 'OK' to delete", 'gravityformssignature' ),
					'delete_nonce'   => wp_create_nonce( 'gf_delete_signature' ),
					'form_id'        => ! empty( $form['id'] ) ? absint( $form['id'] ) : false,
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Frontend scripts should only be enqueued if we're not on a GF admin page and the form contains our field type.
	 *
	 * @since 4.4
	 *
	 * @param array $form The current form object.
	 *
	 * @return bool
	 */
	public function should_enqueue_frontend_script( $form ) {

		$is_frontend         = GFForms::get_page() === false;
		$has_signature_field = ! rgempty( GFFormsModel::get_fields_by_type( $form, array( 'signature' ) ) );

		return ( $is_frontend || $this->is_entry_edit() ) && $has_signature_field;
	}

	/**
	 * Delete signature script should only be enqueued on the entry edit page on forms with a Signature field.
	 *
	 * @since 4.4
	 *
	 * @param array $form The current form object.
	 *
	 * @return bool
	 */
	public function should_enqueue_delete_script( $form ) {

		$has_signature_field = ! rgempty( GFFormsModel::get_fields_by_type( $form, array( 'signature' ) ) );

		return $this->is_entry_edit() && $has_signature_field;
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
		$signature_tooltips = array(
			'signature_background_color' => '<h6>' . esc_html__( 'Background Color', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Select the color to be used for the background of the signature area.', 'gravityformssignature' ),
			'signature_border_color'     => '<h6>' . esc_html__( 'Border Color', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Select the color to be used for the border around the signature area.', 'gravityformssignature' ),
			'signature_pen_color'        => '<h6>' . esc_html__( 'Pen Color', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Select the color of the pen to be used for the signature.', 'gravityformssignature' ),
			'signature_box_width'        => '<h6>' . esc_html__( 'Width', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Enter the width for the signature area in pixels.', 'gravityformssignature' ),
			'signature_border_style'     => '<h6>' . esc_html__( 'Border Style', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Select the border style to be used around the signature area.', 'gravityformssignature' ),
			'signature_pen_size'         => '<h6>' . esc_html__( 'Pen Size', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Select the width of the pen to be used for the signature.', 'gravityformssignature' ),
			'signature_border_width'     => '<h6>' . esc_html__( 'Border Width', 'gravityformssignature' ) . '</h6>' . esc_html__( 'Select the border width to be used around the signature area.', 'gravityformssignature' ),
			'signature_message'          => '<h6>' . esc_html__( 'Message', 'gravityformssignature' ) . '</h6>' . esc_html__( "Write the message you would like to be sent. You can insert fields submitted by the user by selecting them from the 'Insert Variable' drop down.", 'gravityformssignature' )
		);

		return array_merge( $tooltips, $signature_tooltips );
	}

	/**
	 * Add the custom settings for the Signature field to the Appearance tab.
	 *
	 * @param int $position The position the settings should be located at.
	 * @param int $form_id The ID of the form currently being edited.
	 */
	public function field_settings( $position, $form_id ) {

		if ( $position == 0 ) {
			?>
			<li class="background_color_setting field_setting gform_setting_left_half">
				<label for="field_signature_background_color" class="section_label">
					<?php esc_html_e( 'Background Color', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_background_color' ) ?>
				</label>
				<?php GFFormDetail::color_picker( 'field_signature_background_color', 'SetSignatureBackColor' ) ?>
			</li>
			<li class="border_color_setting field_setting gform_setting_right_half">
				<label for="field_signature_border_color" class="section_label">
					<?php esc_html_e( 'Border Color', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_border_color' ) ?>
				</label>
				<?php GFFormDetail::color_picker( 'field_signature_border_color', 'SetSignatureBorderColor' ) ?>
			</li>
			<li class="border_width_setting field_setting gform_setting_left_half">
				<label for="field_signature_border_width" class="section_label">
					<?php esc_html_e( 'Border Width', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_border_width' ) ?>
				</label>
				<select id="field_signature_border_width" onchange="SetSignatureBorderWidth(jQuery(this).val());">
					<option value="0"><?php esc_html_e( 'None', 'gravityformssignature' ) ?></option>
					<option value="1"><?php esc_html_e( 'Small', 'gravityformssignature' ) ?></option>
					<option value="2"><?php esc_html_e( 'Medium', 'gravityformssignature' ) ?></option>
					<option value="3"><?php esc_html_e( 'Large', 'gravityformssignature' ) ?></option>
				</select>
			</li>
			<li class="border_style_setting field_setting gform_setting_right_half">
				<label for="field_signature_border_style" class="section_label">
					<?php esc_html_e( 'Border Style', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_border_style' ) ?>
				</label>
				<select id="field_signature_border_style" onchange="SetSignatureBorderStyle(jQuery(this).val());">
					<option value="dotted"><?php esc_html_e( 'Dotted', 'gravityformssignature' ) ?></option>
					<option value="dashed"><?php esc_html_e( 'Dashed', 'gravityformssignature' ) ?></option>
					<option value="groove"><?php esc_html_e( 'Groove', 'gravityformssignature' ) ?></option>
					<option value="ridge"><?php esc_html_e( 'Ridge', 'gravityformssignature' ) ?></option>
					<option value="inset"><?php esc_html_e( 'Inset', 'gravityformssignature' ) ?></option>
					<option value="outset"><?php esc_html_e( 'Outset', 'gravityformssignature' ) ?></option>
					<option value="double"><?php esc_html_e( 'Double', 'gravityformssignature' ) ?></option>
					<option value="solid"><?php esc_html_e( 'Solid', 'gravityformssignature' ) ?></option>
				</select>
			</li>

			<li class="pen_color_setting field_setting gform_setting_left_half">
				<label for="field_signature_pen_color" class="section_label">
					<?php esc_html_e( 'Pen Color', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_pen_color' ) ?>
				</label>
				<?php GFFormDetail::color_picker( 'field_signature_pen_color', 'SetSignaturePenColor' ) ?>
			</li>
			<li class="pen_size_setting field_setting gform_setting_right_half">
				<label for="field_signature_pen_size" class="section_label">
					<?php esc_html_e( 'Pen Size', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_pen_size' ) ?>
				</label>
				<select id="field_signature_pen_size" onchange="SetSignaturePenSize(jQuery(this).val());">
					<option value="1"><?php esc_html_e( 'Small', 'gravityformssignature' ) ?></option>
					<option value="2"><?php esc_html_e( 'Medium', 'gravityformssignature' ) ?></option>
					<option value="3"><?php esc_html_e( 'Large', 'gravityformssignature' ) ?></option>
				</select>
			</li>
			<li class="box_width_setting field_setting">
				<label for="field_signature_box_width" class="section_label">
					<?php esc_html_e( 'Field Width', 'gravityformssignature' ); ?>
					<?php gform_tooltip( 'signature_box_width' ) ?>
				</label>
				<input id="field_signature_box_width" type="text" class="small"
				       onkeyup="SetSignatureBoxWidth(jQuery(this).val());"
				       onchange="SetSignatureBoxWidth(jQuery(this).val());"/> px
			</li>

		<?php
		}
	}

	// # ENTRY DETAIL PAGE ----------------------------------------------------------------------------------------------

	/**
	 * Used with the gform_admin_pre_render hook to include the functionality for the delete signature link.
	 *
	 * @deprecated 4.1 No replacement.
	 *
	 * @param array $form The current form object.
	 *
	 * @return array
	 */
	public function edit_lead_script( $form ) {
		_deprecated_function( __METHOD__, '4.1' );
		return $form;
	}

	// # MERGE TAGS -----------------------------------------------------------------------------------------------------

	/**
	 * Enable use of the gform_signature_show_in_all_fields hook to prevent the signature image being included in the all_fields output.
	 *
	 * @param string $value The value of the field currently being processed.
	 * @param string $merge_tag The merge tag (i.e. all_field) or the field/input ID when processing a merge tag for an individual field.
	 * @param string $options The merge tag modifiers. e.g. "value,nohidden" would be the modifiers for {all_fields:value,nohidden}.
	 * @param GF_Field $field The field currently being processed.
	 * @param mixed $raw_field_value The fields raw value before it was processed by $field->get_value_entry_detail().
	 *
	 * @return string
	 */
	public function merge_tag_filter( $value, $merge_tag, $options, $field, $raw_field_value ) {

		if ( $merge_tag == 'all_fields' && $field->type == 'signature' ) {

			$show_in_all_fields = apply_filters( 'gform_signature_show_in_all_fields', true, $field, $options, $value );
			if ( ! $show_in_all_fields ) {
				return $raw_field_value;
			}

		}

		return $value;
	}

	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * Returns the URL for the specified signature.
	 *
	 * @deprecated 4.0
	 *
	 * @param string $filename The filename for this signature.
	 *
	 * @return string
	 */
	public function get_signature_url( $filename ) {
		_deprecated_function( __METHOD__, '3.10', 'GF_Signature_Url::get_url()' );

		$path_info = pathinfo( $filename );
		$filename  = $path_info['filename'];

		return site_url() . "?page=gf_signature&signature={$filename}";
	}

	/**
	 * Display the signature on it's own page.
	 *
	 * @param WP $wp The current WordPress instance.
	 */
	public function display_signature( $wp ) {
		$data = $this->get_query_data();

		if ( empty( $data ) ) {
			return;
		}

		$signature = new GF_Signature_Image( $this, $data['filename'], $data['form_id'], $data['field_id'], $data['transparent'], $data['download'], $data['hash'] );
		$signature->maybe_output();
	}

	/**
	 * Returns the string to be used as the primary query var in the signature URL.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_query_var() {
		return 'gf-signature';
	}

	/**
	 * Gets data from the query string which identifies the signature to be output.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	public function get_query_data() {
		if ( empty( $_GET ) ) {
			return array();
		}

		$filename    = $this->get_legacy_filename();
		$transparent = rgget( 't' ) === '1';

		if ( ! empty( $filename ) ) {
			return array(
				'filename'    => $filename,
				'transparent' => $transparent,
				'hash'        => '',
				'form_id'     => 0,
				'field_id'    => 0,
				'download'    => false,
			);
		}

		$data = array(
			'filename'    => rgget( $this->get_query_var() ),
			'transparent' => $transparent,
			'hash'        => rgget( 'hash' ),
			'form_id'     => absint( rgget( 'form-id' ) ),
			'field_id'    => absint( rgget( 'field-id' ) ),
			'download'    => rgget( 'dl' ) === '1',
		);

		return $this->has_required_query_data( $data ) ? $data : array();
	}

	/**
	 * Determines if the required query arguments have values.
	 *
	 * @since 4.0
	 *
	 * @param array $data The data retrieved from the query string.
	 *
	 * @return bool
	 */
	private function has_required_query_data( $data ) {
		if ( empty( $data['filename'] ) ) {
			return false;
		}

		if ( empty( $data['hash'] ) ) {
			$this->log_debug( __METHOD__ . "(): Aborting ({$data['filename']}); empty hash." );

			return false;
		}

		if ( empty( $data['form_id'] ) ) {
			$this->log_debug( __METHOD__ . "(): Aborting ({$data['filename']}); invalid form-id." );

			return false;
		}

		if ( empty( $data['field_id'] ) ) {
			$this->log_debug( __METHOD__ . "(): Aborting ({$data['filename']}); invalid field-id." );

			return false;
		}

		return true;
	}

	/**
	 * Returns the filename from the legacy query string.
	 *
	 * @since 4.0
	 *
	 * @return string|null
	 */
	public function get_legacy_filename() {
		if ( rgget( 'page' ) !== 'gf_signature' ) {
			return '';
		}

		return rgget( 'signature' );
	}


	/**
	 * Maybe save the signature.
	 *
	 * @param string $input_name The input name to use when accessing the $_POST.
	 * @param string $name_prefix The text to use as the filename prefix.
	 *
	 * @return string
	 */
	public function save_signature( $input_name, $name_prefix = '' ) {
		require_once( $this->get_base_path() . '/includes/super_signature/license.php' );
		if ( ! function_exists( 'GetSignatureImage' ) ) {
			$this->log_error( __METHOD__ . '(): Aborting; GetSignatureImage() does not exist.' );

			return '';
		}

		$signature_data = rgpost( $input_name );

		// Remove undefined parameters.
		$signature_data = base64_decode( $signature_data );
		$signature_data = str_replace( 'undefined', '', $signature_data );
		$signature_data = base64_encode( $signature_data );

		$image = GetSignatureImage( $signature_data );
		if ( ! $image ) {
			$this->log_error( __METHOD__ . '(): Aborting; unable to create image from signature data.' );

			return '';
		}

		$folder = $this->get_signatures_folder();

		if ( ! wp_mkdir_p( $folder ) ) {
			$this->log_error( __METHOD__ . '(): Aborting; unable to create folder.' );

			return '';
		}

		// Add index.html to prevent directory browsing.
		$this->add_index_file();

		$filename = $name_prefix . uniqid( '', true ) . '.png';
		$path     = $folder . $filename;
		$result   = imagepng( $image, $path, 4 );
		imagedestroy( $image );

		if ( ! $result ) {
			$this->log_error( __METHOD__ . '(): Aborting; unable to save file.' );

			return '';
		}

		return $filename;
	}

	/**
	 * Used by the gform_delete_entry hook to delete any signatures for the entry currently being deleted.
	 *
	 * @param integer $lead_id The ID of the current entry.
	 */
	public function delete_entry( $lead_id ) {

		$lead = RGFormsModel::get_lead( $lead_id );
		$form = RGFormsModel::get_form_meta( $lead['form_id'] );

		if ( ! is_array( $form['fields'] ) ) {
			return;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field->type == 'signature' ) {
				/**
				 * Enables the ability to disable deletion of the signature file or trigger deletion at a later time.
				 *
				 * @since 3.7.1
				 *
				 * @param bool    $delete_file Defaults to true.
				 * @param array   $form        The Form object.
				 * @param integer $lead_id     The ID of the current entry.
				 * @param integer $field_id    The ID of the current field.
				 */
				$delete_signature = apply_filters( 'gform_signature_delete_file_pre_delete_entry', true, $form, $lead_id, $field->id );
				if ( $delete_signature ) {
					$this->delete_signature( $lead, $field->id );
				}
			}
		}

	}

	/**
	 * Used by the gform_delete_entries hook to delete any signatures for the entries currently being deleted.
	 *
	 * @param int $form_id The ID of the form for which the entries are being deleted.
	 */
	public function delete_entries( $form_id, $status ) {

		$form             = RGFormsModel::get_form_meta( $form_id, $status );
		$signature_fields = GFAPI::get_fields_by_type( $form, 'signature' );

		if ( ! empty( $signature_fields ) ) {
			global $wpdb;

			foreach ( $signature_fields as $field ) {

				$input_id_min = (float) $field->id - 0.0001;
				$input_id_max = (float) $field->id + 0.0001;


				$status_filter = '';
				if ( ! empty( $status ) ) {
					$status_filter = $wpdb->prepare( ' AND status=%s', $status );
				}

				if ( version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ) {
					$lead_details_table_name = GFFormsModel::get_lead_details_table_name();
					$lead_table_name = GFFormsModel::get_lead_table_name();

					$filenames = $wpdb->get_col( $wpdb->prepare( "SELECT ld.value FROM {$lead_details_table_name} ld 
																	  	INNER JOIN {$lead_table_name} l ON l.id = ld.lead_id
																		WHERE ld.form_id=%d AND ld.field_number BETWEEN %s AND %s {$status_filter}", $form_id, $input_id_min, $input_id_max ) );

				} else {
					$entry_meta_table_name = GFFormsModel::get_entry_meta_table_name();

					$filenames = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM {$entry_meta_table_name} em
																		INNER JOIN {$wpdb->prefix}gf_entry e ON e.id = em.entry_id
																		WHERE em.form_id=%d AND em.meta_key=%s {$status_filter}", $form_id, $field->id ) );
				}

				if ( is_array( $filenames ) ) {
					foreach ( $filenames as $filename ) {
						$this->delete_signature_file( $filename );
					}
				}
			}
		}
	}

	/**
	 * Handler for the gf_delete_signature AJAX request.
	 */
	public function ajax_delete_signature() {

		check_ajax_referer( 'gf_delete_signature', 'gf_delete_signature' );

		$lead_id  = intval( $_POST['lead_id'] );
		$field_id = intval( $_POST['field_id'] );

		if ( ! $this->delete_signature( $lead_id, $field_id ) ) {
			esc_html_e( 'There was an issue deleting this signature.', 'gravityformssignature' );
		}

		die();
	}

	/**
	 * Initiates deletion of the signature file and updates the entry to remove the filename.
	 *
	 * @param integer|array $lead_id The ID of the current entry, or the full Entry object.
	 * @param integer $field_id The ID of the current field.
	 *
	 * @return bool
	 */
	public function delete_signature( $lead_id, $field_id ) {

		$lead = is_array( $lead_id ) ? $lead_id : RGFormsModel::get_lead( $lead_id );

		$this->delete_signature_file( rgar( $lead, $field_id ) );

		return GFAPI::update_entry_field( $lead['id'], $field_id, '' );
	}

	/**
	 * Deletes the signature file from the uploads directory.
	 *
	 * @param string $filename The signature filename.
	 */
	public function delete_signature_file( $filename ) {

		$folder    = $this->get_signatures_folder();
		$file_path = $folder . $filename;

		//Prevent files from being deleted from folders other than the signature upload folder
		$is_valid_dir = trailingslashit( dirname( $file_path ) ) == $folder;

		if ( file_exists( $file_path ) && $is_valid_dir ) {
			unlink( $file_path );
		}

	}

	/**
	 * Checks HTTP_USER_AGENT for Internet Explorer
	 *
	 * @since unknown
	 *
	 * @return int
	 */
	public function is_ie() {
		return preg_match( '/MSIE|Internet Explorer|Trident|Edge/i', getenv( 'HTTP_USER_AGENT' ) );
	}

	/**
	 * Returns the current database version of Gravtiy Forms.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public static function get_gravityforms_db_version() {

		if ( method_exists( 'GFFormsModel', 'get_database_version' ) ) {
			$db_version = GFFormsModel::get_database_version();
		} else {
			$db_version = GFForms::$version;
		}

		return $db_version;
	}

	/**
	 * Add index file to signatures folder.
	 *
	 * @since  3.4.2
	 * @access public
	 *
	 * @uses   GFCommon::recursive_add_index_file()
	 * @uses   GFSignature::get_signatures_folder()
	 */
	public function add_index_file() {

		// Get folder path.
		$folder = $this->get_signatures_folder();

		GFCommon::recursive_add_index_file( $folder );

	}

	/**
	 * Get path to signatures folder.
	 *
	 * @since  3.4.2
	 * @access public
	 *
	 * @uses   GFFormsModel::get_upload_root()
	 *
	 * @return string
	 */
	public static function get_signatures_folder() {

		return GFFormsModel::get_upload_root() . 'signatures/';

	}

}
