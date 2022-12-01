<?php
/* the avatar field relies on the upload field  */

/* handle field output */
function wppb_avatar_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Avatar' ){

        $field['meta-name'] = Wordpress_Creation_Kit_PB::wck_generate_slug( $field['meta-name'] );

        /* media upload add here, this should be added just once even if called multiple times */
        wp_enqueue_media();
        /* propper way to dequeue. add to functions file in theme or custom plugin
         function wppb_dequeue_script() {
            wp_script_is( 'wppb-upload-script', 'enqueued' ); //true
            wp_dequeue_script( 'wppb-upload-script' );
        }
        add_action( 'get_footer', 'wppb_dequeue_script' );
         */
        $upload_script_vars = array(
            'nonce'            => wp_create_nonce( 'wppb_woo_simple_upload' ),
            'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
            'remove_link_text' => __( 'Remove', 'profile-builder' )
        );

        wp_enqueue_script( 'wppb-upload-script', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/upload/upload.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
        wp_localize_script( 'wppb-upload-script', 'wppb_upload_script_vars', $upload_script_vars );

		$wppb_generalSettings = get_option( 'wppb_general_settings' );

		if ( ( isset( $wppb_generalSettings['extraFieldsLayout'] ) && ( $wppb_generalSettings['extraFieldsLayout'] == 'default' ) ) )
			wp_enqueue_style( 'profile-builder-upload-css', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/upload/upload.css', false, PROFILE_BUILDER_VERSION );

        $item_title = apply_filters( 'wppb_'.$form_location.'_avatar_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        if( $form_location != 'register' ) {
            if( empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
                $input_value = ( (wppb_user_meta_exists($user_id, $field['meta-name']) != null) ? get_user_meta($user_id, $field['meta-name'], true) : '');
            else
                $input_value = $request_data[wppb_handle_meta_name( $field['meta-name'] )];

            if( !empty( $input_value ) && !is_numeric( $input_value ) ){
                /* we have a file url and we need to change it into an attachment */
                // Check the type of file. We'll use this as the 'post_mime_type'.
                $wp_upload_dir = wp_upload_dir();
                $file_path = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir["basedir"], $input_value );
                //on windows os we might have \ instead of / so change them
                $file_path = str_replace( "\\", "/", $file_path );
                $file_type = wp_check_filetype( basename( $input_value ), null );
                $attachment = array(
                    'guid' => $input_value,
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $input_value ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                // Insert the attachment.
                $input_value = wp_insert_attachment( $attachment, $input_value, 0 );
                if( !empty( $input_value ) ) {
                    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    // Generate the metadata for the attachment, and update the database record.
                    $attach_data = wp_generate_attachment_metadata($input_value, $file_path);
                    wp_update_attachment_metadata($input_value, $attach_data);
                    /* save the new attachment instead of the url */
                    update_user_meta( $user_id, $field['meta-name'], $input_value );
                }
            }
        }
        else
            $input_value = !empty( $_POST[$field['meta-name']] ) ? sanitize_text_field( $_POST[$field['meta-name']] ) : '';

        if ( $form_location != 'back_end' ){
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

            $output = '<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>';
            $output .= wppb_make_upload_button( $field, $input_value, $extra_attr );
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';
        }else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>';
            $output .= wppb_make_upload_button( $field, $input_value );
            $output .='<br/><span class="wppb-description-delimiter">'.$item_description;
            $output .= '
						</td>
					</tr>
				</table>';
        }

		return apply_filters( 'wppb_'.$form_location.'_avatar_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_avatar', 'wppb_avatar_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_avatar', 'wppb_avatar_handler', 10, 6 );


/* handle field save */
function wppb_save_avatar_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Avatar' ){
        $field['meta-name'] = Wordpress_Creation_Kit_PB::wck_generate_slug( $field['meta-name'] );
        if ( isset( $field[ 'simple-upload' ] ) && $field[ 'simple-upload' ] == 'yes' && ( !isset( $field[ 'woocommerce-checkout-field' ] ) || $field[ 'woocommerce-checkout-field' ] !== 'Yes' ) ) {
            //Save data in the case the simple upload field is used
            $field_name = 'simple_upload_' . wppb_handle_meta_name( $field[ 'meta-name' ] );
            if( isset( $_FILES[ $field_name ] ) ) {
                if ( !( isset( $field[ 'conditional-logic-enabled' ] ) && $field[ 'conditional-logic-enabled' ] == 'yes' && !isset( $request_data[ wppb_handle_meta_name( $field[ 'meta-name' ] ) ] ) ) ){
                    if ( isset( $_FILES[ $field_name ][ 'size' ] ) && $_FILES[ $field_name ][ 'size' ] == 0 ){
                        if ( isset( $request_data[ wppb_handle_meta_name( $field[ 'meta-name' ] ) ] ) ){
                            update_user_meta( $user_id, $field[ 'meta-name' ], sanitize_text_field( $request_data[ wppb_handle_meta_name( $field[ 'meta-name' ] ) ] ) );
                        }
                    }
                    else{
                        $attachment_id = $request_data[ $field[ 'meta-name' ] ];
                        update_user_meta( $user_id, $field[ 'meta-name' ], absint( $attachment_id ) );
                        if ( $attachment_id !== '' ) {
                            wp_update_post(array(
                                'ID' => absint(trim($attachment_id)),
                                'post_author' => $user_id
                            ));
                        }
                    }
                }
            }
        }
        else{
            //Save data in the case the WordPress upload is used
            if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                update_user_meta( $user_id, $field['meta-name'], sanitize_text_field( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) );
            }
        }
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_avatar_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_avatar_value', 10, 4 );

/**
 * Function that saves an attachment from the simple upload version of the Avatar field
 * @param $field_name
 * @return string|WP_Error
 */
function wppb_avatar_save_simple_upload_file ( $field_name ){

    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    $upload_overrides = array( 'test_form' => false );

    if( isset( $_FILES[$field_name] ) )
    $file = wp_handle_upload( $_FILES[$field_name], $upload_overrides );

    if ( isset( $file[ 'error' ] ) ) {
        return new WP_Error( 'upload_error', $file[ 'error' ] );
    }
    $filename = isset( $_FILES[ $field_name ][ 'name' ] ) ? sanitize_text_field( $_FILES[ $field_name ][ 'name' ] ) : '';
    $wp_filetype = wp_check_filetype( $filename, null );
    $attachment = array(
        'post_mime_type'    => $wp_filetype[ 'type' ],
        'post_title'        => $filename,
        'post_content'      => '',
        'post_status'       => 'inherit'
    );

    $attachment_id = wp_insert_attachment( $attachment, $file[ 'file' ] );

    if (!is_wp_error($attachment_id) && is_numeric($attachment_id)) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        return trim($attachment_id);
    } else {
        return '';
    }
}

/* save file when ec is enabled */
function wppb_avatar_add_upload_for_user_signup( $field_value, $field, $request_data ){

    // Save the uploaded file
    // It will have no author until the user's email is confirmed
    if( $field['field'] == 'Avatar' ) {
        if( isset( $field[ 'simple-upload' ] ) && $field[ 'simple-upload' ] === 'yes' && ( !isset( $field[ 'woocommerce-checkout-field' ] ) || $field[ 'woocommerce-checkout-field' ] !== 'Yes' ) ) {
            $field_name = 'simple_upload_' . $field['meta-name'];

            if (isset($_FILES[$field_name]) &&
                isset($_FILES[$field_name]['size']) && $_FILES[$field_name]['size'] !== 0 &&
                !(wppb_belongs_to_repeater_with_conditional_logic($field) && !isset($request_data[wppb_handle_meta_name($field['meta-name'])])) &&
                !(isset($field['conditional-logic-enabled']) && $field['conditional-logic-enabled'] == 'yes' && !isset($request_data[wppb_handle_meta_name($field['meta-name'])])) &&
                wppb_valid_simple_upload($field, $_FILES[$field_name])) { /* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */ /* no need here */
                return wppb_save_simple_upload_file($field_name);
            }
        } else {
            $attachment_id = $request_data[wppb_handle_meta_name( $field['meta-name'] )];
            if ( isset( $attachment_id ) ) {
                return absint( trim( $attachment_id ) );
            }
        }
    }

    return '';
}
add_filter( 'wppb_add_to_user_signup_form_field_avatar', 'wppb_avatar_add_upload_for_user_signup', 10, 3 );

/* handle simple upload at the WooCommerce Checkout */
function wppb_woo_simple_avatar(){
    check_ajax_referer( 'wppb_woo_simple_upload', 'nonce' );
    if ( isset($_POST["name"]) ) {
        echo json_encode( wppb_avatar_save_simple_upload_file( sanitize_text_field( $_POST["name"] ) ) );
    }
    wp_die();
}
add_action( 'wp_ajax_nopriv_wppb_woo_simple_avatar', 'wppb_woo_simple_avatar' );
add_action( 'wp_ajax_wppb_woo_simple_avatar', 'wppb_woo_simple_avatar' );

/* handle field validation */
function wppb_check_avatar_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Avatar' ){
        if( $field['required'] == 'Yes' ){
            $field['meta-name'] = Wordpress_Creation_Kit_PB::wck_generate_slug( $field['meta-name'] );
            if ( isset( $field[ 'simple-upload' ] ) && $field[ 'simple-upload' ] == 'yes' && ( !isset( $field[ 'woocommerce-checkout-field' ] ) || $field[ 'woocommerce-checkout-field' ] !== 'Yes' ) ) {
                //Check the required field in case simple upload is used
                $field_name = 'simple_upload_' . wppb_handle_meta_name( $field[ 'meta-name' ] );
                if ( (!isset( $_FILES[ $field_name ] ) || ( isset( $_FILES[ $field_name ] ) && isset( $_FILES[ $field_name ][ 'size' ] ) && $_FILES[ $field_name ][ 'size' ] == 0 ) || !wppb_valid_simple_upload( $field, $_FILES[ $field_name ] ) ) && isset( $request_data[ $field[ 'meta-name' ] ] ) && empty( $request_data[ $field[ 'meta-name' ] ] ) ){ /* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */ /* no need here for wppb_valid_simple_upload() */
                    return wppb_required_field_error( $field[ 'field-title' ] );
                }
            }
            else{
                //Check the required field in case the WordPress upload is used
                if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                    return wppb_required_field_error($field["field-title"]);
                }
            }
        }
	}
    return $message;
}
add_filter( 'wppb_check_form_field_avatar', 'wppb_check_avatar_value', 10, 4 );


/* register image size defined in avatar field */
add_action( 'after_setup_theme', 'wppb_add_avatar_image_sizes' );
function wppb_add_avatar_image_sizes() {
    if ( isset($_REQUEST['action']) && ( ( 'upload-attachment' == $_REQUEST['action'] && isset($_REQUEST['wppb_upload']) && 'true' == $_REQUEST['wppb_upload'] ) || 'wppb_woo_simple_avatar' == $_REQUEST['action'] ) ) {

        $all_fields = get_option('wppb_manage_fields');
        if( !empty( $all_fields ) ) {
            foreach ($all_fields as $field) {
                if( $field['field'] == 'Avatar' ) {
                    wppb_add_avatar_sizes( $field );
                }
            }
        }

        wppb_userlisting_avatar();
    }
}
