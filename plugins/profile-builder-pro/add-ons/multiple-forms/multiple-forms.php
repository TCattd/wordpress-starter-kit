<?php
$wppb_module_settings = get_option( 'wppb_module_settings', 'not_found' );
if ( $wppb_module_settings != 'not_found' ){
	if ( isset( $wppb_module_settings['wppb_multipleEditProfileForms'] ) && ( $wppb_module_settings['wppb_multipleEditProfileForms'] == 'show' ) && file_exists( WPPB_PAID_PLUGIN_DIR.'/add-ons/multiple-forms/edit-profile-forms.php' ) )
		include_once( WPPB_PAID_PLUGIN_DIR.'/add-ons/multiple-forms/edit-profile-forms.php' );

	if ( isset( $wppb_module_settings['wppb_multipleRegistrationForms'] ) && ( $wppb_module_settings['wppb_multipleRegistrationForms'] == 'show' ) && file_exists( WPPB_PAID_PLUGIN_DIR.'/add-ons/multiple-forms/register-forms.php' ) )
		include_once( WPPB_PAID_PLUGIN_DIR.'/add-ons/multiple-forms/register-forms.php' );

	if ( ( isset( $wppb_module_settings['wppb_multipleEditProfileForms'] ) && ( $wppb_module_settings['wppb_multipleEditProfileForms'] == 'show' ) ) || ( isset( $wppb_module_settings['wppb_multipleRegistrationForms'] ) && ( $wppb_module_settings['wppb_multipleRegistrationForms'] == 'show' ) ) )
		add_filter( 'wppb_change_form_fields', 'wppb_multiple_forms_change_fields', 10, 2 );
}

/**
 * Function that is applied on "wppb_change_form_fields" filter to change the manage fields( all the fields defined  ) according to the current form.
 *
 *
 * @param array $fields All the fields from the manage fields section.
 * @return array $args the arguments array for the form
 */
function wppb_multiple_forms_change_fields( $fields, $args ){
	//if we have a edit_profile form set up the post type and meta name accordingly
	if( $args['form_type'] == 'edit_profile' ){
		$meta_name = 'wppb_epf_fields';

	}elseif( $args['form_type'] == 'register' ){
		$meta_name = 'wppb_rf_fields';
	}

    // let's get the fields that we should display on that form
    if( isset( $args['ID'], $meta_name ) ) {
        $this_forms_fields = get_post_meta($args['ID'], $meta_name, true);
        if (!empty($this_forms_fields)) {
            $this_forms_fields_ids = array();
            $returned_fields = array();

            // keep the ids of those fields as they are the "unique key" and we will search for them in all the fields
            foreach ($this_forms_fields as $this_forms_field) {
                $this_forms_fields_ids[] = $this_forms_field['id'];
            }

            // rearrange the fields based on the ids we got before. sort them and remove the ones we don't need.
            if (!empty($this_forms_fields_ids) && !empty($fields)) {
                foreach ($this_forms_fields_ids as $this_forms_fields_id) {
                    foreach ($fields as $field) {
                        if ($field['id'] == $this_forms_fields_id) {
                            $returned_fields[] = $field;
                        }
                    }
                }
            }
        }
    }

	// if we have any rearranged fields return them else we return the original fields
    if( !empty( $returned_fields ) )
		return $returned_fields;

	else
		return $fields;
}


/**
 * Prepopulate the 2 CPT's with the required fields at the moment somebody tries to create a new Register form or Edit Profile form
 *
 * @since v.2.0
 *
 * @param integer $post_id
 * @param object $post
 *
 * @return void
 */
function wppb_add_prepopulated_default_fields( $post_id, $post ){
	if ( 'auto-draft' == $post->post_status ) {
		$all_fields = get_option ( 'wppb_manage_fields' );
		$all_fields_new = array();

		foreach ( $all_fields as $key => $value )
			array_push( $all_fields_new, array( 'field' => wppb_field_format( $value['field-title'], $value['field'] ), 'id' => $value['id'] ) );

		if ( 'wppb-rf-cpt' == $post->post_type ) {
            // Remove "Display name publicly as" from register forms
            foreach( $all_fields_new as $key => $value ) {
                if( strpos( $value['field'], 'Display name publicly as' ) !== false ) {
                    unset( $all_fields_new[$key] );
                }
            }
            $all_fields_new = array_values( $all_fields_new );
            add_post_meta( $post->ID, 'wppb_rf_fields', $all_fields_new );

        } elseif ( 'wppb-epf-cpt' == $post->post_type ){
			// remove reCAPTCHA and Validation fields from the list
			foreach ( $all_fields_new as $key => $value ){
				if ( strpos ( $value['field'], '( reCAPTCHA )' ) !== false || strpos ( $value['field'], '( Validation )' ) !== false ) {
					unset( $all_fields_new[$key] );
				}
			}
            $all_fields_new = array_values( $all_fields_new );
			add_post_meta( $post->ID, 'wppb_epf_fields', $all_fields_new );
		}
	}
}
add_action( 'wp_insert_post', 'wppb_add_prepopulated_default_fields', 10, 2 );


/**
 * Function that adds the internal ID of the given field on the RF and EPF CPT pages, via AJAX
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_handle_rf_epf_id_change(){

    if( !isset( $_POST['field'] ) || $_POST['field'] == '' )
        die('');

	$all_fields = get_option ( 'wppb_manage_fields', 'not_set' );

	if ( ( $all_fields != 'not_set' ) && ( ( is_array( $all_fields ) ) && ( !empty( $all_fields ) ) ) ){

		foreach ( $all_fields as $key => $value ){

			if( wppb_field_format( $value['field-title'], $value['field'] ) == stripslashes( sanitize_text_field( $_POST['field'] ) ) ){
				die( (string)$value['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

		}
	} else
		die('');

}
add_action( 'wp_ajax_wppb_handle_rf_epf_id_change', 'wppb_handle_rf_epf_id_change' );


/**
 * Function that iterates in both the EPF and RF CTP meta's on manage fields update
 *
 * @since v.2.0
 *
 * @param string $meta
 * @param integer $id
 * @param array $array_after_update
 * @param integer $element_id
 *
 * @return void
 */
function wppb_check_epf_rf_cptpms_update( $ep_r_posts, $cpt, $cpt_meta, $internal_id, $array_after_update ){
	foreach ( $ep_r_posts as $key => $value ){
		if ( $value->post_type == $cpt ){
			$post_meta = get_post_meta( $value->ID, $cpt_meta, true );

			if ( !empty( $post_meta ) ){
				foreach ( $post_meta as $this_post_meta_key => $this_post_meta_value ){
					if ( $this_post_meta_value['id'] == $internal_id ){
						$post_meta[$this_post_meta_key]['field'] = wppb_field_format( $array_after_update['field-title'], $array_after_update['field'] );
					}
				}

				update_post_meta( $value->ID, $cpt_meta, $post_meta );
			}
		}
	}
}


/**
 * Function that checks if, after an update, a change has been made to the lists, and if we need to make those changes to all the postmeta's also
 *
 * @since v.2.0
 *
 * @param string $meta
 * @param integer $id
 * @param array $array_after_update
 * @param integer $element_id
 *
 * @return void
 */
function wppb_fields_list_update( $meta, $id, $array_after_update, $element_id ){
	if ( $meta == 'wppb_manage_fields' ){
		$all_fields = get_option ( $meta );
		$array_before_update = $all_fields[$element_id];

		if ( ( trim( $array_before_update['field'] ) != trim( $array_after_update['field'] ) ) || ( trim( $array_before_update['field-title'] ) != trim( $array_after_update['field-title'] ) ) ){
			$ep_r_posts = get_posts( array( 'posts_per_page' => -1, 'post_status' => apply_filters ( 'wppb_get_ep_r_posts', array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' ) ), 'post_type' => array( 'wppb-epf-cpt', 'wppb-rf-cpt' ) ) );

			wppb_check_epf_rf_cptpms_update( $ep_r_posts, 'wppb-rf-cpt', 'wppb_rf_fields', $array_before_update['id'], $array_after_update );
			wppb_check_epf_rf_cptpms_update( $ep_r_posts, 'wppb-epf-cpt', 'wppb_epf_fields', $array_after_update['id'], $array_after_update );
		}
	}
}
add_action ( 'wck_before_update_meta', 'wppb_fields_list_update', 10, 4 );

/**
 * Function that queries the 2 CPTs and checks whether or not the given cpt has <= 1 posts added
 *
 * @since v.2.0
 *
 * @return array
 */
function wppb_get_post_number ( $cpt, $action ){
	$this_cpt = get_posts( array( 'posts_per_page' => -1, 'post_status' => apply_filters ( 'wppb_check_'.$cpt.$action, array( 'any' ) ) , 'post_type' => $cpt ) );

	if ( count( $this_cpt ) <= 1 )
		return true;
	else
		return false;
}

/**
 * Function that prevents users to submit forms without a form title.
 *
 */
function wppb_multiple_forms_publish_admin_hook(){
	global $post;

	if ( is_admin() && ( ( $post->post_type == 'wppb-epf-cpt' ) || ( $post->post_type == 'wppb-rf-cpt' ) ) ){
		?>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(document).on( 'click', '#publish', function(){
					var post_title = jQuery( '#title' ).val();

					if ( post_title.trim() == '' ){
						alert ( '<?php esc_html_e( 'You need to specify the title of the form before creating it', 'profile-builder' ); ?>' );

						jQuery( '#ajax-loading' ).hide();
						jQuery( '.spinner' ).hide();
						jQuery( '#publish' ).removeClass( 'button-primary-disabled' );
						jQuery( '#save-post' ).removeClass('button-disabled' );

						return false;
					}

					return true;
				});
			});
		</script>
		<?php
	}
}
add_action( 'admin_head-post.php', 'wppb_multiple_forms_publish_admin_hook' );
add_action( 'admin_head-post-new.php', 'wppb_multiple_forms_publish_admin_hook' );


/* when deleting fields from manage-fields we need to delete them from the register/edit profile forms as well */
add_action( 'wck_before_remove_meta', 'wppb_delete_fields_from_forms_as_well', 10, 3 );
function wppb_delete_fields_from_forms_as_well( $meta, $id, $element_id ){
    if( $meta == 'wppb_manage_fields' ){
        $all_fields = get_option( $meta);
        $deleted_element = $all_fields[$element_id];
        $deleted_element_id = $deleted_element['id'];

        /* delete from forms */
        $form_types = array( 'register_forms' => 'rf', 'edit_forms' => 'epf' );
        foreach( $form_types as $form_type ){
            $args = array(
                            'post_type' => 'wppb-'.$form_type.'-cpt',
                            'numberposts' => -1,
                            'posts_per_page' => -1,
                            'post_status' => 'any'
                        );
            $all_forms = get_posts( $args );
            if( !empty( $all_forms ) ){
                foreach( $all_forms as $form ){
                    $fields_in_form = get_post_meta( $form->ID, 'wppb_'.$form_type.'_fields', true );
                    $delete_this_key = '';
                    if( !empty( $fields_in_form ) ){
                        foreach( $fields_in_form as $key => $form_field ){
                            if( $form_field['id'] == $deleted_element_id ){
                                $delete_this_key = $key;
                                break;
                            }
                        }

                        if( !empty( $delete_this_key ) ){
                            unset( $fields_in_form[$delete_this_key] );
                            $fields_in_form = array_values( $fields_in_form );
                            update_post_meta( $form->ID, 'wppb_'.$form_type.'_fields', $fields_in_form );
                        }
                    }
                }
            }
        }
    }
}


/**
 * Function that adds the data-id attribute to each option of the add new field to the list select drop-down
 *
 * @since v.2.0.2
 *
 * @return void
 */
function wppb_rf_epf_set_field_ids_on_field_select() {
    global $all_fields;
    $all_fields  = get_option ( 'wppb_manage_fields', 'not_set' );

    // remove certain fields from the Field drop-down on edit profile form
    if( ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wppb-epf-cpt' ) || ( isset( $_GET['post'] ) && get_post_type( absint( $_GET['post'] ) ) == 'wppb-epf-cpt'  ) || ( isset( $_POST['meta'] ) && $_POST['meta'] == 'wppb_epf_fields' ) ) {
        foreach( $all_fields as $key => $field ) {
			$unwanted_fields = array( 'reCAPTCHA', 'Validation' );
            if( in_array( $field['field'], $unwanted_fields) ) {
                unset( $all_fields[$key] );
            }
        }
        $all_fields = array_values( $all_fields );
    }

    // remove certain fields from the Field drop-down on register form
    if( ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wppb-rf-cpt' ) || ( isset( $_GET['post'] ) && get_post_type( absint( $_GET['post'] ) ) == 'wppb-rf-cpt'  ) || ( isset( $_POST['meta'] ) && $_POST['meta'] == 'wppb_rf_fields' ) ) {
        foreach( $all_fields as $key => $field ) {
            if( $field['field'] == 'Default - Display name publicly as' ) {
                unset( $all_fields[$key] );
            }
        }
        $all_fields = array_values( $all_fields );
    }

    if( $all_fields !== 'not_set ') {

        if( !function_exists( 'wppb_rf_epf_set_field_id_select_option' ) ) {
            function wppb_rf_epf_set_field_id_select_option( $content, $field_id ){
                global $all_fields;
                $output = str_replace( '<option', '<option ' . 'data-id="' . $all_fields[$field_id]['id'] . '"', $content );
                return $output;
            }
        }
        foreach($all_fields as $key => $value ) {
            add_filter('wck_select_wppb_epf_fields_field_option_' . $key, 'wppb_rf_epf_set_field_id_select_option', 10 ,2);
            add_filter('wck_select_wppb_rf_fields_field_option_' . $key, 'wppb_rf_epf_set_field_id_select_option', 10 ,2);
        }

    }
}
add_action( 'wck_before_add_form_wppb_rf_fields_element_0', 'wppb_rf_epf_set_field_ids_on_field_select');
add_action( 'wck_before_add_form_wppb_epf_fields_element_0', 'wppb_rf_epf_set_field_ids_on_field_select');
add_action( 'wck_before_adding_form_wppb_rf_fields', 'wppb_rf_epf_set_field_ids_on_field_select' );
add_action( 'wck_before_adding_form_wppb_epf_fields', 'wppb_rf_epf_set_field_ids_on_field_select' );


/**
 * Function that disables the select field options that are also present in the table bellow the drop-down,
 * - after the edit field is added
 * - after the list refreshes;
 * - after a field is refreshed
 * - after the forms is added;
 *
 * @since v.2.0.2
 *
 * @return void
 */
function wppb_rf_epf_disable_select_field_options() {
    echo "<script type=\"text/javascript\">wppb_disable_select_field_options();</script>";
}
add_action( "wck_ajax_add_form_wppb_rf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_ajax_add_form_wppb_epf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_refresh_list_wppb_rf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_refresh_list_wppb_epf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_refresh_entry_wppb_rf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_refresh_entry_wppb_epf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_after_adding_form_wppb_rf_fields", "wppb_rf_epf_disable_select_field_options" );
add_action( "wck_after_adding_form_wppb_epf_fields", "wppb_rf_epf_disable_select_field_options" );


/**
 * Function that calls a JS function that disables the Save Changes button when the Edit Form is added, if the field
 * drop-down selected option is also disabled
 *
 * @since v.2.0.2
 *
 * @return void
 */
function wppb_rf_epf_edit_field_disable_saving() {
    echo "<script type=\"text/javascript\">wppb_check_update_field_options_disabled();</script>";
}
add_action( "wck_after_adding_form_wppb_rf_fields", "wppb_rf_epf_edit_field_disable_saving" );
add_action( "wck_after_adding_form_wppb_epf_fields", "wppb_rf_epf_edit_field_disable_saving" );


/**
 * Function that calls a JS function that disables the Save Changes button in an edit panel,
 * if another field with same ID has been saved in another edit panel
 *
 * @since v.2.0.2
 *
 * @return void
 */
function wppb_rf_epf_edit_field_check_disabled_options() {
    echo "<script type=\"text/javascript\">wppb_check_options_disabled_edit_field();</script>";
}
add_action( "wck_refresh_entry_wppb_rf_fields", "wppb_rf_epf_edit_field_check_disabled_options" );
add_action( "wck_refresh_entry_wppb_epf_fields", "wppb_rf_epf_edit_field_check_disabled_options" );


/**
 * Function that modifies the table header in Edit Profile Forms and Register Forms
 *
 * @since v.2.0
 *
 * @param $list, $id
 *
 * @return string
 */
function wppb_multiple_forms_header( $list_header ){
    $delete_all_nonce = wp_create_nonce( 'wppb-delete-all-entries' );

    return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( '<pre>Title (Type)</pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. esc_html__( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete"><a id="wppb-delete-all-fields" class="wppb-delete-all-fields" onclick="wppb_rf_epf_delete_all_fields(event, this.id, \'' . esc_js($delete_all_nonce) . '\')" title="' . esc_html__('Delete all items', 'profile-builder') . '" href="#">'. esc_html__( 'Delete all', 'profile-builder' ) .'</a></th></tr></thead>'; // phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
}
add_action( 'wck_metabox_content_header_wppb_epf_fields', 'wppb_multiple_forms_header' );
add_action( 'wck_metabox_content_header_wppb_rf_fields', 'wppb_multiple_forms_header' );


/**
 * Function that removes all fields from the form through Ajax
 *
 * @since v.2.0.5
 *
 * @return void
 */
add_action("wp_ajax_wppb_rf_epf_delete_all_fields", 'wppb_rf_epf_delete_all_fields_callback' );
function wppb_rf_epf_delete_all_fields_callback(){
    check_ajax_referer( "wppb-delete-all-entries" );
    if( !empty($_POST['id']) )
        $post_id = absint( $_POST['id'] );
    else
        $post_id = '';

    if( !empty( $_POST['meta'] ) )
        $meta_name = sanitize_text_field( $_POST['meta'] );
    else
        $meta_name = '';

	do_action( 'wppb_before_remove_all_fields', $meta_name, $post_id );

    if( $meta_name == 'wppb_rf_fields' ) {
        $post_meta_array = get_post_meta( $post_id, $meta_name, true );

        foreach( $post_meta_array as $key => $item ) {
            if( !strpos( $item['field'], "( Default - Username )" ) && !strpos( $item['field'], "( Default - E-mail )" ) && !strpos( $item['field'], "( Default - Password )" ) ) {
                unset( $post_meta_array[$key] );
            }
        }

        $post_meta = array_values($post_meta_array);
    } else {
        $post_meta = '';
    }

    update_post_meta( $post_id, $meta_name, $post_meta);

    exit;
}