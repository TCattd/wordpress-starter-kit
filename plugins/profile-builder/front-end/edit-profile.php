<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
	wp_update_user only attempts to clear and reset cookies if it's updating the password.
	The php function setcookie(), used in both the cookie-clearing and cookie-resetting functions, 
	adds to the page headers and therefore must be called within the first php tag on the page, 
	and before the WordPress get_header() function. Since wp_update_user needs this, it must be at the beginning of the page as well.
*/
/* set action to login user after password changed in edit profile */
add_action( 'init', 'wppb_autologin_after_password_changed' );
function wppb_autologin_after_password_changed(){
    if( isset( $_POST['action'] ) && $_POST['action'] === 'edit_profile' ){
        if( isset( $_POST['passw1'] ) && !empty( $_POST['passw1'] ) && !empty( $_POST['form_name'] ) && isset(  $_POST['edit_profile_'. $_POST['form_name'] .'_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( $_POST['edit_profile_'. $_POST['form_name'] .'_nonce_field'] ), 'wppb_verify_form_submission' ) ){

            /* all the error checking filters are defined in each field file so we need them here */
            if ( file_exists ( WPPB_PLUGIN_DIR.'/front-end/default-fields/default-fields.php' ) )
                require_once( WPPB_PLUGIN_DIR.'/front-end/default-fields/default-fields.php' );
                
            if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists ( WPPB_PAID_PLUGIN_DIR.'/front-end/extra-fields/extra-fields.php' ) )
                require_once( WPPB_PAID_PLUGIN_DIR.'/front-end/extra-fields/extra-fields.php' );

            /* we get the form_name through $_POST so we can apply correctly the filter so we generate the correct fields in the current form  */
            $form_fields = apply_filters( 'wppb_change_form_fields', get_option( 'wppb_manage_fields' ), array( 'form_type'=> 'edit_profile', 'form_fields' => array(), 'form_name' => sanitize_text_field( $_POST['form_name'] ), 'role' => '', 'ID' => Profile_Builder_Form_Creator::wppb_get_form_id_from_form_name( sanitize_text_field( $_POST['form_name'] ), 'edit_profile' ), 'context' => 'edit_profile_auto_login_after_password_change' ) );
            if( !empty( $form_fields ) ){

                $edited_user_id = get_current_user_id();
                if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
                    if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) ){
                        $edited_user_id = absint( $_GET['edit_user'] );
                    }
                }   
                
                /* check for errors in the form through the filters */
                $output_field_errors = array();
                foreach( $form_fields as $field ){
                    //this is not perfect because we don't know the role attribute for the form here so we send it as '' in the filter, but as of v 2.9.0 it is not needed anywhere so we're good
                    $error_for_field = apply_filters( 'wppb_check_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ), '', $field, $_POST, 'edit_profile', '', $edited_user_id );
                    if( !empty( $error_for_field ) )
                        $output_field_errors[$field['id']] = '<span class="wppb-form-error">' . $error_for_field  . '</span>';
                }

                /* if we have no errors change the password */
                if( empty( $output_field_errors ) ) {

                    $user_id = get_current_user_id();
                    if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
                        if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) ){
                            $user_id = absint( $_GET['edit_user'] );
                        }
                    }

                    if( !isset( $_GET['edit_user'] ) ) {
                        wp_clear_auth_cookie();
                        /* set the new password for the user */
                        wp_set_password($_POST['passw1'], $user_id);//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                        // Here we calculate the expiration length of the current auth cookie and compare it to the default expiration.
                        // If it's greater than this, then we know the user checked 'Remember Me' when they logged in.
                        $logged_in_cookie = wp_parse_auth_cookie('', 'logged_in');
                        /** This filter is documented in wp-includes/pluggable.php */
                        $default_cookie_life = apply_filters('auth_cookie_expiration', (2 * DAY_IN_SECONDS), $user_id, false);
                        $remember = (($logged_in_cookie['expiration'] - time()) > $default_cookie_life);

                        wp_set_auth_cookie($user_id, $remember, '', wp_get_session_token() );
                    }
                    else{
                        wp_set_password($_POST['passw1'], $user_id); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                    }

                    /* log out of other sessions or all sessions if the admin is editing the profile */
                    $sessions = WP_Session_Tokens::get_instance( $user_id );
                    if ( $user_id === get_current_user_id() ) {                        
                        $sessions->destroy_others( wp_get_session_token() );
                    } else {                        
                        $sessions->destroy_all();                        
                    }
                    
                }
            }
        }
    }
}
		
		
function wppb_front_end_profile_info( $atts ){
	// get value set in the shortcode as parameter, still need to default to something else than empty string
	extract( shortcode_atts( array( 'form_name' => 'unspecified', 'redirect_url' => '', 'redirect_priority' => 'normal' ), $atts, 'wppb-edit-profile' ) );

    $form = new Profile_Builder_Form_Creator( array( 'form_type' => 'edit_profile', 'form_name' => $form_name, 'redirect_url' => $redirect_url, 'redirect_priority' => $redirect_priority ) );

    return $form;
}