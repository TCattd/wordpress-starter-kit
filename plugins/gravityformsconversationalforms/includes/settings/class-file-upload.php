<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings;

use Gravity_Forms\Gravity_Forms\Settings\Fields;
use \GFCommon;
use \GFFormsModel;

defined( 'ABSPATH' ) || die();

class FileUpload extends Fields\Base {

	/**
	 * Field type.
	 *
	 * @since 2.5
	 *
	 * @var string
	 */
	public $type = 'file_upload';

	/**
	 * Allowed file types
	 *
	 * @var string[]
	 */
	public $allowed_types = array(
		'gif',
		'jpg',
		'jpeg',
		'png',
	);

	/**
	 * Max width of upload.
	 *
	 * @var string
	 */
	public $max_width = '800';

	/**
	 * Max height of upload.
	 *
	 * @var string
	 */
	public $max_height = '400';

	/**
	 * Max File Size
	 *
	 * @var int
	 */
	public $max_file_size = 0;

	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 2.5
	 *
	 * @return string
	 */
	public function markup() {

		// Get value.
		$value = $this->get_value();

		// Prepare after_input.
		// Dynamic after_input content should use a callable to render.
		if ( isset( $this->after_input ) && is_callable( $this->after_input ) ) {
			$this->after_input = call_user_func( $this->after_input, $value, $this );
		}

		// Prepare markup.
		// Display description.
		$html = $this->get_description();

		$props = array(
			'allowedFileTypes' => $this->allowed_types,
			'id'               => 'file_upload_' . esc_attr( $this->settings->get_input_name_prefix() ) . '_' . esc_attr( $this->name ),
			'maxHeight'        => $this->max_height,
			'maxWidth'         => $this->max_width,
			'name'             => esc_attr( $this->settings->get_input_name_prefix() ) . '_' . esc_attr( $this->name ),
			'fileURL'          => empty( $value ) ? $this->default_value : $value,
			'customClasses'    => array( $this->class ),
			'externalManager'  => true,
			'i18n'             => array(
				'click_to_upload' => __( 'Click to upload', 'gravityforms' ),
				'drag_n_drop'     => __( 'or drag and drop', 'gravityforms' ),
				'max'             => __( 'recommended size:', 'gravityforms' ),
				'or'              => __( 'or', 'gravityforms' ),
				'replace'         => __( 'Replace', 'gravityforms' ),
				'delete'          => __( 'Delete', 'gravityforms' ),
			),
		);

		$html .= sprintf(
			'<span data-js="gform-input--file-upload" data-js-props="%s"></span>',
			esc_attr( json_encode( $props ) )
		);

		// Insert after input markup.

		$html .= $this->get_error_icon();

		$html .= isset( $this->after_input ) ? $this->after_input : '';

		return $html;
	}

	// # DATA METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get the value of the field.
	 *
	 * @return array|bool|mixed|string
	 */
	public function get_value() {
		$value = parent::get_value();

		if ( ! $_POST ) {
			return $value;
		}

		if ( ! empty( $value ) ) {
			return $value;
		}

		/**
		 * If the request is a $_POST request and the page is displaying, it means there was an error
		 * in the form validation. Files have not yet populated the global $files array, so we need
		 * to grab the value from the posted array instead.
		 */
		$posted = $this->settings->get_posted_values();
		$name   = $this->get_parsed_name();

		if ( isset( $posted[ $name . '_file_url' ] ) ) {
			return $posted[ $name . '_file_url' ];
		}

		return $value;
	}

	/**
	 * Save the data from $_FILES after running through various validation, security, and
	 * sanitization steps.
	 *
	 * NOTE: This is largely copied from the File Upload field from Gravity Forms core.
	 *
	 * @since 1.0
	 *
	 * @param array             $field_values
	 * @param array|bool|string $field_value
	 *
	 * @return array
	 */
	public function save_field( $field_values, $field_value ) {
		$name  = $this->get_parsed_name();
		$value = $this->get_single_file_value( rgget( 'id' ), esc_attr( $this->settings->get_input_name_prefix() ) . '_' . esc_attr( $this->name ) );

		if ( ! $value ) {
			$field_values[ $name ] = isset( $field_values[ $name . '_file_url' ] ) ? $field_values[ $name . '_file_url' ] : '';
			return $field_values;
		}

		return parent::save_field( $field_values, $value );
	}

	/**
	 * Get the value for a single file being uploaded, based on the input name.
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 * @param $input_name
	 *
	 * @return string
	 */
	public function get_single_file_value( $form_id, $input_name ) {
		global $_gf_uploaded_files;

		GFCommon::log_debug( __METHOD__ . '(): Starting.' );

		if ( empty( $_gf_uploaded_files ) ) {
			$_gf_uploaded_files = array();
		}

		if ( ! isset( $_gf_uploaded_files[ $input_name ] ) ) {

			//check if file has already been uploaded by previous step
			$file_info     = GFFormsModel::get_temp_filename( $form_id, $input_name );
			$temp_filename = rgar( $file_info, 'temp_filename', '' );
			$temp_filepath = GFFormsModel::get_upload_path( $form_id ) . '/tmp/' . $temp_filename;

			if ( $file_info && file_exists( $temp_filepath ) ) {
				GFCommon::log_debug( __METHOD__ . '(): File already uploaded to tmp folder, moving.' );
				$_gf_uploaded_files[ $input_name ] = $this->move_temp_file( $form_id, $file_info );
			} else if ( ! empty( $_FILES[ $input_name ]['name'] ) ) {
				GFCommon::log_debug( __METHOD__ . '(): calling upload_file' );
				$_gf_uploaded_files[ $input_name ] = $this->upload_file( $form_id, $_FILES[ $input_name ] );
			} else {
				GFCommon::log_debug( __METHOD__ . '(): No file uploaded. Exiting.' );
			}
		}

		return rgget( $input_name, $_gf_uploaded_files );
	}

	/**
	 * Take a temporary file and upload it to the server.
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public function upload_file( $form_id, $file ) {
		GFCommon::log_debug( __METHOD__ . '(): Uploading file: ' . $file['name'] );
		$target = GFFormsModel::get_file_upload_path( $form_id, $file['name'] );
		if ( ! $target ) {
			GFCommon::log_debug( __METHOD__ . '(): FAILED (Upload folder could not be created.)' );

			return 'FAILED (Upload folder could not be created.)';
		}
		GFCommon::log_debug( __METHOD__ . '(): Upload folder is ' . print_r( $target, true ) );

		if ( move_uploaded_file( $file['tmp_name'], $target['path'] ) ) {
			GFCommon::log_debug( __METHOD__ . '(): File ' . $file['tmp_name'] . ' successfully moved to ' . $target['path'] . '.' );
			$this->set_permissions( $target['path'] );

			return $target['url'];
		} else {
			GFCommon::log_debug( __METHOD__ . '(): FAILED (Temporary file ' . $file['tmp_name'] . ' could not be copied to ' . $target['path'] . '.)' );

			return 'FAILED (Temporary file could not be copied.)';
		}
	}

	/**
	 * Set correct permissions on the uploaded file (or directory).
	 *
	 * @since 1.0
	 *
	 * @param $path
	 *
	 * @return void
	 */
	private function set_permissions( $path ) {
		GFCommon::log_debug( __METHOD__ . '(): Setting permissions on: ' . $path );

		GFFormsModel::set_permissions( $path );
	}

	/**
	 * Move the temp file to the uploads directory.
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 * @param $tempfile_info
	 *
	 * @return mixed|string
	 */
	public function move_temp_file( $form_id, $tempfile_info ) {

		$target = GFFormsModel::get_file_upload_path( $form_id, $tempfile_info['uploaded_filename'] );
		$source = GFFormsModel::get_upload_path( $form_id ) . '/tmp/' . wp_basename( $tempfile_info['temp_filename'] );

		GFCommon::log_debug( __METHOD__ . '(): Moving temp file from: ' . $source );

		if ( rename( $source, $target['path'] ) ) {
			GFCommon::log_debug( __METHOD__ . '(): File successfully moved.' );
			$this->set_permissions( $target['path'] );

			return $target['url'];
		} else {
			GFCommon::log_debug( __METHOD__ . '(): FAILED (Temporary file could not be moved.)' );

			return 'FAILED (Temporary file could not be moved.)';
		}
	}

	// # VALIDATION METHODS --------------------------------------------------------------------------------------------

	/**
	 * Validate posted field value.
	 *
	 * @since 2.5
	 *
	 * @param string $value Posted field value.
	 */
	public function do_validation( $value ) {

		$file_names = array();
		$input_name = esc_attr( $this->settings->get_input_name_prefix() ) . '_' . esc_attr( $this->name );
		GFCommon::log_debug( __METHOD__ . '(): Validating field ' . $input_name );

		$allowed_extensions = GFCommon::clean_extensions( $this->allowed_types );

		if ( ! empty( $_FILES[ $input_name ] ) ) {
			$max_upload_size_in_bytes = isset( $this->max_file_size ) && $this->max_file_size > 0 ? $this->max_file_size * 1048576 : wp_max_upload_size();
			$max_upload_size_in_mb    = $max_upload_size_in_bytes / 1048576;
			if ( ! empty( $_FILES[ $input_name ]['name'] ) && $_FILES[ $input_name ]['error'] > 0 ) {
				$uploaded_file_name = isset( GFFormsModel::$uploaded_files[ $form['id'] ][ $input_name ] ) ? GFFormsModel::$uploaded_files[ $form['id'] ][ $input_name ] : '';
				if ( empty( $uploaded_file_name ) ) {
					switch ( $_FILES[ $input_name ]['error'] ) {
						case UPLOAD_ERR_INI_SIZE:
						case UPLOAD_ERR_FORM_SIZE:
							GFCommon::log_debug( __METHOD__ . '(): File ' . $_FILES[ $input_name ]['name'] . ' exceeds size limit. Maximum file size: ' . $max_upload_size_in_mb . 'MB' );
							$fileupload_validation_message = sprintf( esc_html__( 'File exceeds size limit. Maximum file size: %dMB', 'gravityforms' ), $max_upload_size_in_mb );
							break;
						default:
							GFCommon::log_debug( __METHOD__ . '(): The following error occurred while uploading - ' . $_FILES[ $input_name ]['error'] );
							$fileupload_validation_message = sprintf( esc_html__( 'There was an error while uploading the file. Error code: %d', 'gravityforms' ), $_FILES[ $input_name ]['error'] );
					}

					$this->set_error( $fileupload_validation_message );

					return;
				}
			} elseif ( $_FILES[ $input_name ]['size'] > 0 && $_FILES[ $input_name ]['size'] > $max_upload_size_in_bytes ) {
				GFCommon::log_debug( __METHOD__ . '(): File ' . $_FILES[ $input_name ]['name'] . ' exceeds size limit. Maximum file size: ' . $max_upload_size_in_mb . 'MB' );
				$this->set_error( sprintf( esc_html__( 'File exceeds size limit. Maximum file size: %dMB', 'gravityforms' ), $max_upload_size_in_mb ) );

				return;
			}

			/**
			 * A filter to allow or disallow whitelisting when uploading a file
			 *
			 * @param bool false To set upload whitelisting to true or false (default is false, which means it is enabled)
			 */
			$whitelisting_disabled = apply_filters( 'gform_file_upload_whitelisting_disabled', false );

			if ( ! empty( $_FILES[ $input_name ]['name'] ) && ! $whitelisting_disabled ) {
				$check_result = GFCommon::check_type_and_ext( $_FILES[ $input_name ] );
				if ( is_wp_error( $check_result ) ) {
					$this->failed_validation = true;
					GFCommon::log_debug( sprintf( '%s(): %s; %s', __METHOD__, $check_result->get_error_code(), $check_result->get_error_message() ) );
					$this->set_error( esc_html__( 'The uploaded file type is not allowed.', 'gravityforms' ) );

					return;
				}
			}

			$single_file_name = $_FILES[ $input_name ]['name'];
			$file_names       = array( array( 'uploaded_filename' => $single_file_name ) );
		}

		foreach ( $file_names as $file_name ) {
			GFCommon::log_debug( __METHOD__ . '(): Validating file upload for ' . $file_name['uploaded_filename'] );
			$info = pathinfo( rgar( $file_name, 'uploaded_filename' ) );

			if ( empty( $allowed_extensions ) ) {
				if ( GFCommon::file_name_has_disallowed_extension( rgar( $file_name, 'uploaded_filename' ) ) ) {
					GFCommon::log_debug( __METHOD__ . '(): The file has a disallowed extension, failing validation.' );
					$this->set_error( esc_html__( 'The uploaded file type is not allowed.', 'gravityforms' ) );
				}
			} else {
				if ( ! empty( $info['basename'] ) && ! GFCommon::match_file_extension( rgar( $file_name, 'uploaded_filename' ), $allowed_extensions ) ) {
					GFCommon::log_debug( __METHOD__ . '(): The file is of a type that cannot be uploaded, failing validation.' );
					$this->set_error(
						sprintf(
							esc_html__( 'The uploaded file type is not allowed. Must be one of the following: %s', 'gravityforms' ),
							strtolower(
								implode( ', ', GFCommon::clean_extensions( $this->allowed_types ) )
							)
						)
					);
				}
			}
		}

		GFCommon::log_debug( __METHOD__ . '(): Validation complete.' );
	}

}
