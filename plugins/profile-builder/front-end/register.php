<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Function that changes the auto generated password with the one selected by the user.
 */
function wppb_signup_password_random_password_filter( $password ) {
	global $wpdb;

	$key = ( !empty( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : null );
	$key = ( !empty( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : $key );

	if ( !empty( $_POST['user_pass'] ) )// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$password = $_POST['user_pass'];// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	elseif ( !is_null( $key ) ) {
		$signup = ( is_multisite() ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->signups . " WHERE activation_key = %s", $key ) ) : $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->base_prefix . "signups WHERE activation_key = %s", $key ) ) );
		
		if ( empty( $signup ) || $signup->active ) {
			//bad key or already active
		} else {
			//check for password in signup meta
			$meta = unserialize( $signup->meta );
			
			if ( !empty($meta['user_pass']) )
                $password = $meta['user_pass'];
		}
	}
	
	return apply_filters( 'wppb_generated_random_password', $password, $key );
}
add_filter( 'random_password', 'wppb_signup_password_random_password_filter' );

/**
 * Activate a signup.
 *
 *
 * @param string $key The activation key provided to the user.
 * @return array An array containing information about the activated user and/or blog
 */
function wppb_activate_signup( $key ) {
	global $wpdb;
	$bloginfo = get_bloginfo( 'name' );
	$wppb_general_settings = get_option( 'wppb_general_settings' );

    $login_after_register = ( isset( $wppb_general_settings['automaticallyLogIn'] ) ? $wppb_general_settings['automaticallyLogIn'] : apply_filters( 'wppb_automatically_login_after_register', 'No' ) );

	$signup = ( is_multisite() ? $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->signups WHERE activation_key = %s", $key) ) : $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->base_prefix."signups WHERE activation_key = %s", $key ) ) );

    $user_login = ( ( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) ) ? trim( $signup->user_email ) : trim( $signup->user_login ) );

    $user_email = esc_sql( $signup->user_email );
    /* the password is in hashed form in the signup table so we will add it later */
    $password = NULL;

    $user_id = username_exists( $user_login );

	if ( empty( $signup ) )
		return apply_filters( 'wppb_register_activate_user_error_message1', '<p class="error">'.__( 'Invalid activation key!', 'profile-builder' ).'</p>');

	if ( $signup->active )
		if ( empty( $signup->domain ) )
			return apply_filters( 'wppb_register_activate_user_error_message2', '<p class="wppb-success">'.__( 'This username is now active!', 'profile-builder' ).'</p>', $user_id );

	$meta = unserialize( $signup->meta );

    if( strcasecmp($login_after_register, 'Yes') === 0 ) {
        $login_after_register = true;
    } elseif( isset( $meta [ 'wppb_login_after_register_'.$meta['user_login'] ] ) ) {
        $login_after_register = $meta [ 'wppb_login_after_register_'.$meta['user_login'] ];
        unset( $meta [ 'wppb_login_after_register_'.$meta['user_login'] ] );
    } else {
        $login_after_register = false;
    }

	if ( !$user_id )
		$user_id = wppb_create_user( $user_login, $password, $user_email );
	else
		$user_already_exists = true;

	if ( ! $user_id )
		return apply_filters( 'wppb_register_activate_user_error_message4', '<p class="error">'.__('Could not create user!', 'profile-builder').'</p>' );
		
	elseif ( isset( $user_already_exists ) && ( $user_already_exists == true ) )
		return apply_filters( 'wppb_register_activate_user_error_message5', '<p class="error">'.__( 'This username is already activated!', 'profile-builder' ).'</p>' );
	
	else{
		$inserted_user = ( is_multisite() ? $wpdb->update( $wpdb->signups, array( 'active' => 1, 'activated' => current_time( 'mysql', true ) ), array( 'activation_key' => $key ) ) : $wpdb->update( $wpdb->base_prefix.'signups', array( 'active' => 1, 'activated' => current_time( 'mysql', true ) ), array( 'activation_key' => $key ) ) );

		wppb_add_meta_to_user_on_activation( $user_id, '', $meta );
		
		// if admin approval is activated, then block the user until he gets approved
		$wppb_generalSettings = get_option('wppb_general_settings');
		if( wppb_get_admin_approval_option_value() === 'yes' ){
            wppb_update_user_status_to_pending( $user_id, $wppb_generalSettings );
		}

        if ( !isset( $wppb_generalSettings['adminApproval'] ) )
            $wppb_generalSettings['adminApproval'] = 'no';

        /* copy the hashed password from signup meta to wp user table */
        if( !empty( $meta['user_pass'] ) ){
            /* we might still have the base64 encoded password in signups and not the hash */
            if( base64_encode(base64_decode($meta['user_pass'], true)) === $meta['user_pass'] )
                $meta['user_pass'] = wp_hash_password( $meta['user_pass'] );

            $wpdb->update( $wpdb->users, array('user_pass' => $meta['user_pass'] ), array('ID' => $user_id) );
			wp_cache_delete( $user_id, 'users' );
        }

		wppb_notify_user_registration_email($bloginfo, $user_login, $user_email, 'sending', $password, wppb_get_admin_approval_option_value() );

		do_action( 'wppb_activate_user', $user_id, $password, $meta );

		if( $inserted_user ) {
            // CHECK FOR REDIRECT
            $redirect_url = wppb_get_redirect_url( 'normal', 'after_success_email_confirmation', '', $user_login );

            $redirect_delay = apply_filters( 'wppb_success_email_confirmation_redirect_delay', 3, $user_id );
            $redirect_message = wppb_build_redirect( $redirect_url, $redirect_delay, 'after_success_email_confirmation' );

			$success_message = apply_filters( 'wppb_success_email_confirmation', '<p class="wppb-success">' . __( 'Your email was successfully confirmed.', 'profile-builder' ) . '</p><!-- .success -->', $user_id );
            $admin_approval_message = apply_filters( 'wppb_email_confirmation_with_admin_approval', '<p class="alert">' . __( 'Before you can access your account, an administrator needs to approve it. You will be notified via email.', 'profile-builder' ) . '</p>', $user_id );

            $wppb_general_settings = get_option( 'wppb_general_settings', 'false' );

            if ( wppb_get_admin_approval_option_value() === 'yes' ){
				$user_data = get_userdata( $user_id );

				if( $wppb_general_settings != 'not_found' && ! empty( $wppb_general_settings['adminApprovalOnUserRole'] ) ) {
					foreach( $user_data->roles as $role ) {
						if( in_array( $role, $wppb_general_settings['adminApprovalOnUserRole'] ) ) {
							return $success_message . $admin_approval_message . ( ! empty ( $redirect_message ) ? $redirect_message : '' );
						} else {
							wp_set_object_terms( $user_id, NULL, 'user_status' );
							clean_object_term_cache( $user_id, 'user_status' );

                            if( $login_after_register ) {
                                if( empty( $redirect_url ) ) {
                                    $redirect_url = wppb_curpageurl();
                                }
                                $redirect_message = wppb_activate_signup_autologin_redirect_url($user_id, $redirect_url, $redirect_delay);
                            }

							return $success_message . ( ! empty ( $redirect_message ) ? $redirect_message : '' );
						}
					}
				} else {
					return $success_message . $admin_approval_message . ( ! empty ( $redirect_message ) ? $redirect_message : '' );
				}
            } else {
				wp_set_object_terms( $user_id, NULL, 'user_status' );
				clean_object_term_cache( $user_id, 'user_status' );

                if( $login_after_register ) {
                    if( empty( $redirect_url ) ) {
                        $redirect_url = wppb_curpageurl();
                    }
                    $redirect_message = wppb_activate_signup_autologin_redirect_url($user_id, $redirect_url, $redirect_delay);
                }

                return $success_message . ( ! empty ( $redirect_message ) ? $redirect_message : '' );
            }
        } else {
			return apply_filters('wppb_register_failed_user_activation', '<p class="error">'. __('There was an error while trying to activate the user.', 'profile-builder') .'</p><!-- .error -->');
        }
	}		
}

//function that generates the redirect message when the user should be automatically logged in
function wppb_activate_signup_autologin_redirect_url( $user_id, $redirect_url, $redirect_delay ){
    $nonce = wp_create_nonce( 'autologin-'. $user_id .'-'. (int)( time() / 60 ) );

    $redirect_url = remove_query_arg( 'activation_key' ,$redirect_url );

    $redirect_url = apply_filters( 'wppb_login_after_reg_redirect_url', $redirect_url );
    $redirect_url = add_query_arg( array( 'autologin' => 'true', 'uid' => $user_id, '_wpnonce' => $nonce ), $redirect_url );

    return wppb_build_redirect( $redirect_url, $redirect_delay, 'after_success_email_confirmation' );
}

//function to display the registration page
function wppb_front_end_register( $atts ){
	extract( shortcode_atts( array( 'role' => get_option( 'default_role' ), 'form_name' => 'unspecified', 'redirect_url' => '', 'logout_redirect_url' => '', 'automatic_login' => '', 'redirect_priority' => 'normal' ), $atts, 'wppb-register' ) );
	
    $form = new Profile_Builder_Form_Creator( array( 'form_type' => 'register', 'form_name' => $form_name, 'role' => ( is_object( get_role( $role ) ) ? $role : get_option( 'default_role' ) ) , 'redirect_url' => $redirect_url, 'logout_redirect_url' => $logout_redirect_url, 'automatic_login' => $automatic_login, 'redirect_priority' => $redirect_priority ) );

    return $form;
}

// function to choose whether to display the registration page or the validation message
function wppb_front_end_register_handler( $atts ){
	return ( isset( $_GET['activation_key'] ) ? wppb_activate_signup ( sanitize_text_field( $_GET['activation_key'] ) ) : wppb_front_end_register( $atts ) );
}

add_action( 'user_register', 'wppbc_disable_admin_approval_for_user_role', 99, 1 );
function wppbc_disable_admin_approval_for_user_role( $user_id ) {
	if ( current_user_can( 'delete_users' ) ) {
		wp_set_object_terms( $user_id, NULL, 'user_status' );
		clean_object_term_cache( $user_id, 'user_status' );
	}
}

/* authors and contributors shouldn't be allowed to create pages with the register shortcode in them */
add_filter( 'the_content', 'wppb_maybe_remove_register_shortcode' );
function wppb_maybe_remove_register_shortcode( $content ){
    if ( has_shortcode( $content, 'wppb-register' ) ){
        $author_id = get_the_author_meta( 'ID' );
        if( !empty( $author_id ) ){
            if( !user_can( $author_id, 'edit_others_posts' ) ) {
                remove_shortcode('wppb-register');
            }
        }
    }

    return $content;
}

/* custom redirect after registration on wp default register form */
function wppb_default_registration_redirect( $user_id ) {

    $user_data = get_userdata( $user_id );

    // CHECK FOR REDIRECT
    if( isset( $_POST['redirect_to']  ) )
        $_POST['redirect_to'] = apply_filters( 'wppb_after_registration_redirect_url', wppb_get_redirect_url( 'normal', 'after_registration', esc_url_raw( $_POST['redirect_to'] ), $user_data ) );

}
add_action( 'register_new_user', 'wppb_default_registration_redirect' );