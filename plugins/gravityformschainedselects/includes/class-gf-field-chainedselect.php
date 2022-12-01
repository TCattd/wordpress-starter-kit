<?php
if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Chained_Field_Select extends GF_Field {

	public $type = 'chainedselect';

	public function __construct( $data = array() ) {
		parent::__construct( $data );
		if ( ! has_filter( 'init', array( __class__, 'init' ) ) ) {
			add_action( 'init', array( __class__, 'init' ), 11 );
		}
	}

	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a dashicons class.
	 *
	 * @since 1.4.2
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return gf_chained_selects()->get_base_url() . '/images/menu-icon.svg';
	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @since 1.4.2
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'Allows populating drop downs dynamically and chaining multiple drop downs together.', 'gravityformschainedselects' );
	}

	public static function init() {

	    add_filter( 'wp_ajax_gform_get_next_chained_select_choices', array( __class__, 'get_next_chained_select_choices' ) );
		add_filter( 'wp_ajax_nopriv_gform_get_next_chained_select_choices', array( __class__, 'get_next_chained_select_choices' ) );

		add_filter( 'gform_form_post_get_meta', array( __class__, 'maybe_import_from_filter' ) );
		add_filter( 'gform_multifile_upload_field', array( __class__, 'create_custom_file_upload_field' ), 10, 3 );
		add_filter( 'gform_post_multifile_upload', array( __class__, 'import_choices_from_uploaded_file' ), 10, 5 );

		add_action( 'gform_field_standard_settings', array( __class__, 'output_standard_field_settings_markup' ) );
		add_action( 'gform_field_appearance_settings', array( __class__, 'output_appearance_field_settings_markup' ) );
		add_filter( 'gform_tooltips', array( __class__, 'register_tooltips' ) );
		add_filter( 'gform_is_value_match', array( __class__, 'is_value_match' ), 10, 6 );

	}

	public static function register_tooltips( $tooltips ) {
		$tooltips['gfcs_choices']           = sprintf( '<h6>%s</h6> %s', __( 'Choices', 'gravityformschainedselects' ), __( 'Upload a .csv file to import your choices.', 'gravityformschainedselects' ) );
		$tooltips['gfcs_bulk_add_disabled'] = sprintf( '<h6>%s</h6> %s', __( 'Bulk Add Disabled', 'gravityformschainedselects' ), __( 'Bulk Add is only available for the last Drop Down in a chain. It is best to use Bulk Add for each Drop Down as you build your chain.', 'gravityformschainedselects' ) );
		return $tooltips;
	}

	public static function create_custom_file_upload_field( $field, $form, $field_id ) {

		// The $field will be null for a newly created Chained Select field.
		$is_valid_field = $field == null || $field->get_input_type() == 'chainedselect';
		if ( ! $is_valid_field ) {
			return $field;
		}

		$field = new GF_Field_FileUpload( array(
			'id'                => $field_id, // $field may be null so always use $field_id
			'multipleFiles'     => true,
			'maxFiles'          => 1,
			'maxFileSize'       => '',
			'allowedExtensions' => 'csv',
			'isChainedSelect'   => true, // custom flag used to indicate that this is a custom upload field for a chained select
			'inputs'            => rgobj( $field, 'inputs' ),
		) );

		return $field;
	}

	public static function import_choices_from_uploaded_file( $form, $field, $uploaded_filename, $tmp_file_name, $file_path ) {

		// We create a custom file upload field as part of the upload process; check if our custom flag is set.
		if ( ! $field->isChainedSelect ) {
			return;
		}

		if ( ! wp_verify_nonce( rgpost( '_gform_file_upload_nonce_' . $form['id'] ), 'gform_file_upload_' . $form['id'] ) ) {
			GFAsyncUpload::die_error( 403, esc_html__( 'Permission denied.', 'gravityforms' ) );
		}

		gf_chained_selects()->log_debug( __METHOD__ . '(): Processing ' . $uploaded_filename );
		$import = self::import_choices( $file_path, $field );

		if ( is_wp_error( $import ) ) {
			gf_chained_selects()->log_error( __METHOD__ . '(): ' . $import->get_error_message() );
			$status_code = rgar( $import->get_error_data( $import->get_error_code() ), 'status_header', 500 );
			GFAsyncUpload::die_error( $status_code, $import->get_error_message() );
		}

		$output = array(
			'status' => 'ok',
			'data'   => array(
				'temp_filename'     => $tmp_file_name,
				'uploaded_filename' => str_replace( "\\'", "'", urldecode( $uploaded_filename ) ), //Decoding filename to prevent file name mismatch.
				'choices'           => $import['choices'],
				'inputs'            => $import['inputs'],
			)
		);

		$encoded = json_encode( $output );
		if ( $encoded === false ) {
			$json_error = json_last_error_msg();
			gf_chained_selects()->log_error( __METHOD__ . '(): ' . $json_error );
			GFAsyncUpload::die_error( 422, $json_error );
		}

		gf_chained_selects()->log_debug( __METHOD__ . '(): Processing completed.' );
		die( $encoded );

    }

    public static function import_choices( $path, $field ) {

	    if( self::is_choice_limit_exceeded( $path ) ) {
		    return new WP_Error( 'column_max_exceeded', __( 'One of your columns has exceeded the limit for unique values.', 'gravityformschainedselects' ), array( 'status_header' => 422 ) );
	    }

	    $choices = array();
	    $inputs = array();

	    $handle = fopen( $path, 'r' );
	    if( $handle !== false ) {

		    while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) {

			    // filter out empty rows
			    $row = array_filter( $row, 'strlen' );
			    if( empty( $row ) ) {
				    continue;
			    }

			    // save the headers as inputs
			    if( empty( $inputs ) ) {
				    $i = 1;
				    foreach( $row as $index => $item ) {
					    if( $i % 10 == 0 ) {
						    $i++;
					    }
					    $inputs[] = array(
						    'id'    => $field->id . '.' . $i,
						    'label' => wp_strip_all_tags( $item ),
						    'name'  => isset( $field->inputs[ $index ]['name'] ) ? $field->inputs[ $index ]['name'] : '',
					    );
					    $i++;
				    }
				    continue;
			    }

			    $parent = null;

			    foreach( $row as $item ) {

				    if( $parent === null ) {
					    $parent = &$choices;
				    }

				    if( ! isset( $parent[ $item ] ) ) {
				    	$item = self::sanitize_choice_value( trim( $item ) );
					    $parent[ $item ] = array(
						    'text'       => $item,
						    'value'      => $item,
						    'isSelected' => false,
						    'choices'    => array()
					    );
				    }

				    $parent = &$parent[ $item ]['choices'];

			    }

		    }

		    fclose( $handle );

	    }

	    // convert associative array to numeric indexes
	    self::array_values_recursive( $choices );

	    return compact( 'inputs', 'choices' );
    }

    public static function sanitize_choice_value( $value ) {
	    $allowed_protocols = wp_allowed_protocols();
	    $value = wp_kses_no_null( $value, array( 'slash_zero' => 'keep' ) );
	    $value = wp_kses_hook( $value, 'post', $allowed_protocols );
	    $value = wp_kses_split( $value, 'post', $allowed_protocols );
	    return $value;
    }

    public static function is_choice_limit_exceeded( $file_path ) {

	    $handle = fopen( $file_path, 'r' );
	    if( $handle === false ) {
            return null;
	    }

	    $uniques = array();
	    $limit   = apply_filters( 'gravityformschainedselects_column_unique_values_limit', 5000 );
	    $limit   = apply_filters( 'gform_chainedselects_column_unique_values_limit', $limit );

	    while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) {

		    // filter out empty rows
		    $row = array_filter( $row );
		    if( empty( $row ) ) {
			    continue;
		    }

		    // setup our $uniques based on header
		    if( empty( $uniques ) ) {
                $uniques = array_pad( array(), count( $row ), array() );
			    continue;
		    }

		    $parent = null;

		    foreach( $row as $column => $item ) {

		    	if( ! isset( $uniques[ $column ] ) ) {
		    		continue;
			    }

		        if( ! in_array( $item, $uniques[ $column ] ) ) {
	                $uniques[ $column ][] = $item;
                }

                if( count( $uniques[ $column ] ) > $limit ) {
                    return true;
                }

		    }

	    }

	    return false;
    }

	public static function array_values_recursive( &$choices, $prop = 'choices' ) {

		$choices = array_values( $choices );

		for( $i = 0; $i <= count( $choices ); $i++ ) {
			if( ! empty( $choices[ $i ][ $prop ] ) ) {
				$choices[ $i ][ $prop ] = self::array_values_recursive( $choices[ $i ][ $prop ], $prop );
			}
        }

		return $choices;
	}

	public static function maybe_import_from_filter( $form ) {

		if ( is_admin() && rgget( 'id' ) != $form['id'] ) {
			return $form;
		}

		gf_chained_selects()->log_debug( __METHOD__ . '(): running for form #' . $form['id'] );

		$has_change = false;

		foreach ( $form['fields'] as &$field ) {

			if ( $field->get_input_type() != 'chainedselect' ) {
				continue;
			}

			gf_chained_selects()->log_debug( __METHOD__ . '(): processing field #' . $field->id );

			$has_filter       = has_filter( 'gform_chainedselects_import_file' ) || has_filter( 'gform_chainedselects_import_file_' . $form['id'] ) || has_filter( 'gform_chainedselects_import_file_' . $form['id'] . '_' . $field->id );
			$has_field_change = ( $has_filter && ! $field->gfcsFilterEnabled ) || ( ! $has_filter && $field->gfcsFilterEnabled );

			// If filter is set, let's set a flag so we can lock down the field settings UI.
			$field->gfcsFilterEnabled = $has_filter;

			if ( ! $has_filter ) {
				if ( $has_field_change ) {
					$has_change                 = true;
					$field->gfcsFile            = null;
					$field->gfcsCacheKey        = null;
					$field->gfcsCacheExpiration = null;
				}
				gf_chained_selects()->log_debug( __METHOD__ . '(): skipping; filter not used.' );
				continue;
			}

			/**
			 * Provide an import file programmatically.
			 *
			 * This import file will override any previously uploaded file via the form settings.
			 *
			 * @since 1.0
			 *
			 * @param array $import_file {
			 *
			 *     An array of details for the file from which choices will be imported.
			 *
			 *     @var string $url        The URL of the file to be imported.
			 *     @var int    $expiration The number of seconds until the import file will be re-imported.
			 * }
			 */
			$import_details = gf_apply_filters( array( 'gform_chainedselects_import_file', $form['id'], $field->id ), array(
				'url'        => '',
				'expiration' => 60 * 60 * 24
			), $form, $field );

			if ( ! rgar( $import_details, 'url' ) ) {
				gf_chained_selects()->log_debug( __METHOD__ . '(): skipping; empty url.' );
				continue;
			}

			$cache_key = implode( '_', array(
				sanitize_title_with_dashes( $import_details['url'] ),
				intval( $import_details['expiration'] ),
			) );

			$now = time();

			// Check if we've recently pinged this URL.
			if ( $field->gfcsCacheKey == $cache_key && $now <= $field->gfcsCacheExpiration ) {
				gf_chained_selects()->log_debug( sprintf( '%s(): skipping; not expired. now: %d; gfcsCacheExpiration: %d; %d seconds until file can be reimported.', __METHOD__, $now, $field->gfcsCacheExpiration, $field->gfcsCacheExpiration - $now ) );
				continue;
			}

			$field->gfcsCacheKey        = $cache_key;
			$field->gfcsCacheExpiration = time() + $import_details['expiration'];

			$import = self::import_choices_from_remote_file( $import_details['url'], $field, $form );

			if ( is_wp_error( $import ) ) {
				gf_chained_selects()->log_debug( sprintf( '%s(): import failed. %s - %s', __METHOD__, $import->get_error_code(), $import->get_error_message() ) );

				if ( $field->gfcsFile == null ) {
					$field->inputs  = gf_chained_selects()->get_default_inputs();
					$field->choices = gf_chained_selects()->get_default_choices();
				}

				// There was an error fetching the file. Let's check the file again in 60 seconds.
				$field->gfcsCacheExpiration = time() + 60;

			} else {

				$has_change = true;

				$field->gfcsFile = $import['gfcsFile'];
				$field->inputs   = $import['inputs'];
				$field->choices  = $import['choices'];
				gf_chained_selects()->log_debug( __METHOD__ . '(): import complete.' );

			}

		}

		if ( $has_change ) {
			remove_filter( 'gform_form_post_get_meta', array( __class__, 'maybe_import_from_filter' ) );
			// Apparently this isn't always set before updating the form?
			$form['is_active'] = isset( $form['is_active'] ) ? $form['is_active'] : true;
			$result            = GFAPI::update_form( $form );
			add_filter( 'gform_form_post_get_meta', array( __class__, 'maybe_import_from_filter' ) );
			if ( is_wp_error( $result ) ) {
				gf_chained_selects()->log_debug( sprintf( '%s(): form update failed. %s - %s', __METHOD__, $result->get_error_code(), $result->get_error_message() ) );
			} else {
				gf_chained_selects()->log_debug( __METHOD__ . '(): form updated.' );
			}
		} else {
			gf_chained_selects()->log_debug( __METHOD__ . '(): no change to form.' );
		}

		return $form;
	}

    public static function import_choices_from_remote_file( $url, $field, $form ) {

	    $upload_dir = GFFormsModel::get_file_upload_path( $form['id'], sprintf( 'gfcs-field-%d-data.csv', $field->id ) );
	    $handle     = fopen( $upload_dir['path'], 'w+' );
	    $response   = wp_remote_get( $url );
	    $error      = false;

	    if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
		    $error = new WP_Error( 'file_inaccessible', __( 'File could not be loaded.', 'gravityformschainedselects' ) );
	    } else if( wp_remote_retrieve_header( $response, 'content-type' ) != 'text/csv' ) {
		    $error = new WP_Error( 'invalid_content_type', __( 'File is not a CSV file.', 'gravityformschainedselects' ) );
	    } else if ( empty( wp_remote_retrieve_body( $response ) ) ) {
		    $error = new WP_Error( 'empty', __( 'File is empty.', 'gravityformschainedselects' ) );
	    }

	    if( $error ) {
		    fclose( $handle );
		    return $error;
	    }

	    $content = wp_remote_retrieve_body( $response );
	    fwrite( $handle, $content, strlen( $content ) );

	    $stats = fstat( $handle );
	    fclose( $handle );

	    if( $stats['size'] > gf_chained_selects()->get_max_file_size() ) {
		    return new WP_Error( 'max_file_size_exceeded', __( 'File is too large.', 'gravityformschainedselects' ) );
	    }

	    $parsed_url = parse_url( $url );
	    $pathinfo   = pathinfo( $parsed_url['path'] );

	    $import = self::import_choices( $upload_dir['path'], $field );
	    if( is_wp_error( $import ) ) {
		    return $import;
	    }

	    $import['gfcsFile'] = array(
		    'dateUploaded' => time(),
		    'name' => $pathinfo['basename'],
		    'size' => $stats['size'],
		    'type' => 'text/csv',
            'isFromFilter' => true
	    );

	    return $import;
    }

	public static function get_next_chained_select_choices() {
		if ( ! wp_verify_nonce( rgpost( 'nonce' ), 'gform_get_next_chained_select_choices' ) ) {
			die();
		}
		$form_id  = rgpost( 'form_id' );
		$field_id = rgpost( 'field_id' );
		$form     = GFAPI::get_form( $form_id );
		$field    = GFFormsModel::get_field( $form, $field_id );
		$input_id      = rgpost( 'input_id' );
		$next_input_id = $field->get_next_input_id( $input_id );
		$value         = rgpost( 'value' );
		$choices       = $next_input_id ? $field->get_input_choices( $value, $next_input_id ) : array();

		// Sanitize values before they're sent to frontend script for output.
		// We might consider generating the full markup and passing that back but I originally went with passing the
		// choices to provide flexibility to the script.
		foreach( $choices as &$choice ) {
			$choice['value'] = esc_attr( $choice['value'] );
		}

		die( json_encode( $choices ) );
	}

	public static function output_standard_field_settings_markup( $position ) {

		if ( $position != 1362 ) {
			return;
		}

		?>

		<style type="text/css">
			.gfcs-processing { background: url(<?php echo GFCommon::get_base_url(); ?>/images/spinner.gif); }
			.gfcs-file-icon { background: url(<?php echo GFCommon::get_base_url(); ?>/images/doctypes/icon_xls.gif); }
		</style>

		<script>
			gform_chainedselects_file_upload_nonce = '<?php echo wp_create_nonce( 'gform_file_upload_' . rgget( 'id' ) ); ?>';
		</script>

		<li class="chained_choices_setting field_setting">

			<label for="gfcs_add_choices" class="section_label">
				<?php esc_html_e( 'Import Choices', 'gravityformschainedselects' ); ?>
				<?php gform_tooltip( 'gfcs_choices' ); ?>
			</label>

			<div id="gfcs-container">
				<div id="gfcs-progress"><?php esc_html_e( 'Your browser does not have Flash, Silverlight or HTML5 support.', 'gravityformschainedselects' ); ?></div>
				<div id="gfcs-drop">
					<span class="gform_drop_instructions"><?php esc_html_e( 'Drop your file here or ', 'gravityformschainedselects' ) ?> </span>
					<input id="pickfiles" type="button" value="<?php esc_attr_e( 'Select a file', 'gravityformschainedselects' ) ?>" class="button">
				</div>
				<div id="gfcs-sample">
					<?php printf( __( 'Download a sample file: %ssample.csv%s', 'gravityformschainedselects' ), '<a href="' . gf_chained_selects()->get_base_url() . '/assets/sample.csv" target="_blank">', '</a>' ); ?>
				</div>
			</div>

		</li>

		<?php
	}

	public static function output_appearance_field_settings_markup( $position ) {
		if ( $position != 400 ) {
			return;
		}
		?>

		<li class="chained_selects_alignment_setting field_setting">
			<label for="chained_selects_alignment" class="section_label"><?php esc_html_e( 'Drop Down Alignment', 'gravityformschainedselects' ); ?></label>
			<select id="chained_selects_alignment"
			        onchange="GFCSAdmin.updateAlignment( this.value );">
				<option value="horizontal"><?php esc_html_e( 'Horizontally (in a row)', 'gravityformschainedselects' ); ?></option>
				<option value="vertical"><?php esc_html_e( 'Vertically (in a column)', 'gravityformschainedselects' ); ?></option>
			</select>
		</li>

		<li class="chained_selects_hide_inactive_setting field_setting">
			<label for="chained_selects_hide_inactive" class="section_label"><?php esc_html_e( 'Drop Down Display', 'gravityformschainedselects' ); ?></label>
			<input type="checkbox" value="1" id="chained_selects_hide_inactive"
			       onclick="SetFieldProperty( 'chainedSelectsHideInactive', this.checked );"/>
			<label for="chained_selects_hide_inactive"
			       class="inline"><?php esc_html_e( 'Hide Inactive Drop Downs', 'gravityformschainedselects' ); ?></label>
		</li>

		<?php
	}

	public static function is_value_match( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {

		$is_input_specific = (int) $rule['fieldId'] != $rule['fieldId'];

		if ( ! $is_input_specific && $source_field instanceof GF_Chained_Field_Select ) {
			$target_values = explode( '/', $target_value );
			for ( $i = 0; $i < count( $target_values ); $i ++ ) {
				if ( $target_values[ $i ] == '*' ) {
					$target_values[ $i ] = $field_value[ $i ];
				}
			}
			$target_value = implode( '/', $target_values );
			$field_value  = implode( '/', $field_value );
			$is_match     = GFFormsModel::matches_operation( $field_value, $target_value, $operation );
		}

		return $is_match;
	}

	public function is_conditional_logic_supported() {
		return true;
	}

	public function get_form_editor_field_title() {
		return __( 'Chained Selects', 'gravityformschainedselects' );
	}

	public function get_form_editor_field_settings() {
		return array(
            'conditional_logic_field_setting',
            'prepopulate_field_setting',
            'error_message_setting',
            'label_setting',
            'admin_label_setting',
            'label_placement_setting',
            'sub_label_placement_setting',
            'rules_setting',
            'visibility_setting',
            'description_setting',
            'css_class_setting',
            'duplicate_setting',
            'chained_choices_setting',
            'chained_selects_alignment_setting',
            'chained_selects_hide_inactive_setting'
		);
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title()
		);
	}

	public function get_form_editor_inline_script_on_page_render() {

	    $set_default_values = sprintf( '
            function SetDefaultValues_%1$s( field ) {
                field.choices = gformChainedSelectData.defaultChoices;
                field.inputs  = GFCSAdmin.getDefaultInputs( field );
                field.chainedSelectsAlignment = "horizontal";  
                return field;
            };',
            $this->type
        );

	    $field_settings = sprintf( '
	        ( function( $ ) {
		        $( document ).bind( "gform_load_field_settings", function( event, field ) {
		            if( GetInputType( field ) == "%s" ) {
		                $( "#chained_selects_alignment" ).val( field.chainedSelectsAlignment );
		                $( "#chained_selects_hide_inactive" ).prop( "checked", field.chainedSelectsHideInactive );
		            }
		        } );
		    } )( jQuery );',
	        $this->type
	    );

		return implode( "\n", array( $set_default_values, $field_settings ) );
    }

	public function validate( $value, $form ) {
		if ( ! $this->isRequired ) {
			return;
		}
		// get all
		foreach ( $this->inputs as $index => $input ) {
			$input_value = rgar( $value, $input['id'] );
			// if no value is provided and there are choices avialable for this field, add a validation error
			if ( ! $input_value && ! $this->has_no_options( $value, $input ) ) {
				$this->failed_validation  = true;
				$this->validation_message = empty( $this->errorMessage ) ? __( 'This field is required. Please select a value for each option.', 'gravityformschainedselects' ) : $this->errorMessage;
			}
		}
	}

	public function get_field_input( $form, $value = '', $entry = null ) {

	    if ( $this->is_entry_detail() ) {
			return $this->get_entry_detail_field_input( $form, $value, $entry );
		} else if( $this->is_form_editor() ) {
	        // don't populate drop downs with choices in form editor to improve performance
		    $markup = '';
		    foreach ( $this->inputs as $index => $input ) {
			    $html_id = sprintf( 'input_%d_%s', $form['id'], str_replace( '.', '_', $input['id'] ) );
			    $markup .= sprintf(
				    "<span id='%s'>
                        <select name='input_%s' id='%s' disabled='disabled'>
                            <option value=''>%s</option>
                        </select>
                    </span>",
				    $html_id . '_container', $input['id'], $html_id, $input['label']
			    );
            }
            return "<div class='ginput_container ginput_chained_selects_container {$this->chainedSelectsAlignment}'>{$markup}</div>";
        }

		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$id       = $this->id;
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$logic_event   = gf_chained_selects()->is_gravityforms_supported( '2.4.15.5' ) ? '' : sprintf( 'onchange="gf_input_change( this, %d, %d );"', $form_id, $this->id );
		$disabled_attr = $is_form_editor ? 'disabled="disabled"' : '';
		$markup = '';

		foreach ( $this->inputs as $index => $input ) {
			$html_id   = sprintf( 'input_%d_%s', $form_id, str_replace( '.', '_', $input['id'] ) );
			$css_class = $this->has_no_options( $value, $input ) ? 'gf_no_options' : ''; // @alex: Do you think this name is confusing? has_no_choices isn't exactly the same thing in this context...
			$tabindex  = $this->get_tabindex();
			$atts      = array( $logic_event, $tabindex, $disabled_attr );
			$input_markup = sprintf(
					"<select name='input_%s' id='%s' class='%s' %s>%s</select>",
					$input['id'], $html_id, $css_class, implode( ' ', $atts ), $this->get_choices( $value, $input )
			);
			$input_container_markup = sprintf(
					"<span id='%s' class='%s'>
					%s
				</span>",
					$html_id . '_container', $css_class, $input_markup
			);
			$markup .= $input_container_markup;
		}
		$markup .= '<span class="gf_chain_complete" style="display:none;">&nbsp;</span>';
		$field_html_id = sprintf( 'input_%d_%d', $form_id, $this->id );
		$class_suffix  = $is_entry_detail ? '_admin' : '';
		$classes   = array(
				$this->chainedSelectsAlignment ? $this->chainedSelectsAlignment : 'horizontal',
				$this->size . $class_suffix,
				'gfield_chainedselect'
		);
		$css_class = esc_attr( trim( implode( ' ', $classes ) ) );
		$markup = sprintf( "<div class='ginput_container %s' id='%s'>%s</div>", $css_class, $field_html_id, $markup );

		return $markup;
	}

	public function get_entry_detail_field_input( $form, $value, $entry ) {
		$markup = '<div class="ginput_complex_admin">';
		foreach ( $this->inputs as $index => $input ) {
			$html_id  = sprintf( 'input_%d_%s', $form['id'], str_replace( '.', '_', $input['id'] ) );
			$tabindex = $this->get_tabindex();
			$atts     = array( $tabindex );
			$input_markup = sprintf(
					"<input name='input_%s' id='%s' class='%s' %s value='%s' />",
					$input['id'], $html_id, $css_class = '', implode( ' ', $atts ), $value[ $input['id'] ]
			);
			$label_markup = sprintf(
					"<label for='%s'>%s</label>",
					$html_id, GFCommon::get_label( $this, $input['id'], true )
			);
			$input_container_markup = sprintf(
					"<span id='%s' class='%s'>
					%s
					%s
				</span>",
					$html_id . '_container', $css_class = '', $input_markup, $label_markup
			);
			$markup .= $input_container_markup;
		}
		$markup .= '</div>';

		return $markup;
	}

	public function get_choices( &$value, $input ) {

		$field = clone $this;

		// temporarily adjust placeholder to current input's label to play nice with GFCommon::get_select_choices()
		$field->placeholder = $input['label'];
		$field->choices = $this->get_input_choices( $value, $input['id'] );

		if ( is_array( $field->choices ) ) {
			foreach( $field->choices as $choice ) {
				if ( rgar( $choice, 'isSelected' ) && ! rgar( $value, $input['id'] ) ) {
					$value[ $input['id'] ] = $choice['value'];
				}
			}
		}

		return GFCommon::get_select_choices( $field, rgar( $value, $input['id'], '' ) );
	}

	public function get_input_choices( $chain_value, $input_id = false, $depth = false, $choices = null, $full_chain_value = null ) {

		$full_chain_value = $full_chain_value !== null ? $full_chain_value : $chain_value;
		$value            = array_shift( $chain_value );
		$index            = $input_id ? $this->get_input_index( $input_id ) : 1; // @hack test this for input IDs greater than 10
		$depth            = $depth ? $depth : 1;
		$choices          = $choices === null ? $this->choices : ( empty( $choices ) ? array() : $choices );
		$input_choices    = array();

		if ( $depth % 10 == 0 ) {
			$depth ++;
		}

		if ( $depth == $index ) {
			$input_choices = $choices;
			if ( ! $this->is_form_editor() ) {
				$input_choices = gf_apply_filters( array(
					'gform_chained_selects_input_choices',
					$this->formId,
					$this->id,
					$index
				), $input_choices, $this->formId, $this, $input_id, $full_chain_value, $value, $index );
			}
		} else {
			foreach ( $choices as $choice ) {
				if ( $choice['value'] == $value ) {
					$input_choices = $this->get_input_choices( $chain_value, $input_id, $depth + 1, ! empty( $choice['choices'] ) ? $choice['choices'] : array(), $full_chain_value );
					break;
				}
			}
		}

		if ( empty( $input_choices ) && $this->get_previous_input_value( $input_id, $full_chain_value ) ) {
			if ( ! $this->is_form_editor() ) {
				$input_choices = gf_apply_filters( array(
					'gform_chained_selects_input_choices',
					$this->formId,
					$this->id,
					$index
				), $input_choices, $this->formId, $this, $input_id, $full_chain_value, $value, $index );
			}
			if ( empty( $input_choices ) ) {
				$input_choices = array(
					array(
						'text'       => __( 'No options', 'gravityformschainedselects' ),
						'value'      => '',
						'isSelected' => true,
						'noOptions'  => true
					)
				);
			}
		}

		return $input_choices;
	}

	public function has_no_options( $value, $input ) {
		$choices = $this->get_input_choices( $value, $input['id'] );

		return rgars( $choices, '0/noOptions' );
	}

	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		$filtered = is_array( $value ) ? array_filter( $value ) : '';
		if( empty( $filtered ) ) {
			return '';
		}

		$return = '';

		foreach( $this->inputs as $input ) {
			if( $format == 'html' ) {
				if( $this->is_entry_detail() ) {
					$return .= sprintf( '<div class="gfcs-value-row"><b style="display:block;">%s:</b> <span>%s</span></div>', $input['label'], $value[ $input['id'] ] );
				} else {
					$font_open  = $media == 'email' ? '<font style="font-family: sans-serif; font-size:12px;">' : '';
					$font_close = $media == 'email' ? '</font>' : '';
					$return .= sprintf( '<tr><td><b>%1$s%3$s:%2$s</b></td><td><span>%1$s%4$s%2$s</span></td></tr>', $font_open, $font_close, $input['label'], $value[ $input['id'] ] );
				}
			} else {
				$return .= sprintf( "%s: %s\n", $input['label'], $value[ $input['id'] ] );
			}
		}

		if( $format == 'html' ) {
			if( $this->is_entry_detail() ) {
				$return = sprintf( '<div class="gfcs-value">%s</div>', $return );
			} else {
				$return = sprintf( '<table class="gfcs-value">%s</table>', $return );
			}
		}

		return $return;
	}

	public function get_input_property( $input_id, $property_name ) {
		$input = GFFormsModel::get_input( $this, $this->id . '.' . (string) $input_id );

		return rgar( $input, $property_name );
	}

	public function sanitize_settings() {
		parent::sanitize_settings();
		if ( is_array( $this->inputs ) ) {
			foreach ( $this->inputs as &$input ) {
				if ( isset ( $input['choices'] ) && is_array( $input['choices'] ) ) {
					$input['choices'] = $this->sanitize_settings_choices( $input['choices'] );
				}
			}
		}
	}

	public function get_form_inline_script_on_page_render( $form ) {
		$script = sprintf( ';new GFChainedSelects( %d, %d, %d, "%s" );', $form['id'], $this->id, $this->chainedSelectsHideInactive, $this->chainedSelectsAlignment );

		return $script;
	}

	public function get_next_input_id( $current_input_id ) {
		$index      = $this->get_input_index( $current_input_id );
		$next_index = $index + 1;
		if ( $next_index % 10 == 0 ) {
			$next_index ++;
		}
		$next_input_id = sprintf( '%d.%d', intval( $current_input_id ), $next_index );
		// make sure the next input ID actually exists
		foreach ( $this->inputs as $input ) {
			if ( $input['id'] == $next_input_id ) {
				return $next_input_id;
			}
		}

		return false;
	}

	public function get_input_index( $input_id ) {
		$id_bits = explode( '.', $input_id );

		return (int) array_pop( $id_bits );
	}

	public function get_previous_input_value( $current_input_id, $full_chain_value ) {

		$input_id_bits = explode( '.', $current_input_id );

		list( $field_id, $input_index ) = array_pad( $input_id_bits, 2, null );

		$prev_input_id    = sprintf( '%s.%s', $field_id, $input_index - 1 );
		$prev_input_value = rgar( $full_chain_value, $prev_input_id );

		return $prev_input_value;
	}

	// # FIELD FILTER UI HELPERS ---------------------------------------------------------------------------------------

	/**
	 * Returns the sub-filters for the current field.
	 *
	 * @since
	 *
	 * @return array
	 */
	public function get_filter_sub_filters() {
		$sub_filters = array();
		$inputs      = $this->inputs;

		foreach ( $inputs as $input ) {
			$sub_filters[] = array(
				'key'             => rgar( $input, 'id' ),
				'text'            => rgar( $input, 'label' ),
				'preventMultiple' => false,
				'operators'       => $this->get_filter_operators(),
			);
		}

		return $sub_filters;
	}

	/**
	 * Returns the filter operators for the current field.
	 *
	 * @since
	 *
	 * @return array
	 */
	public function get_filter_operators() {
		$operators   = parent::get_filter_operators();
		$operators[] = 'contains';

		return $operators;
	}

}

GF_Fields::register( new GF_Chained_Field_Select() );
