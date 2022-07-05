<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Function that checks if a user is approved before reseting the password
 *
 * @param string $data either the user login or the users email
 * @param string $what what field we query for when getting the user
 */
function wppb_check_for_unapproved_user( $data, $what ){
	$message = '';

	$wppb_generalSettings = get_option( 'wppb_general_settings' );
	if( wppb_get_admin_approval_option_value() === 'yes' ){
		$user = ( ( $what == 'user_email' ) ? get_user_by( 'email', $data ) : get_user_by( 'login', $data ) );

		if ( wp_get_object_terms( $user->data->ID, 'user_status' ) ){
            $message = '<strong>'. __('ERROR', 'profile-builder') . '</strong>: ' . __('Your account has to be confirmed by an administrator before you can use the "Password Reset" feature.', 'profile-builder');
            $message = apply_filters('wppb_recover_password_unapporved_user', $message);
		}
	}

	return $message;
}

/**
 * Function that retrieves the unique user key from the database. If we don't have one we generate one and add it to the database
 *
 * @param string $requested_user_login the user login
 *
 */
function wppb_retrieve_activation_key( $requested_user_login ){
	global $wpdb;

	$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $requested_user_login ) );

	if ( empty( $key ) ) {

		// Generate something random for a key...
		$key = wp_generate_password( 20, false );
		do_action('wppb_retrieve_password_key', $requested_user_login, $key);

		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $requested_user_login));
	}

	return $key;
}

 /**
 * Function that creates a generate new password form
 *
 * @param array $post_data $_POST
 *
 */
function wppb_create_recover_password_form( $user, $post_data ){
	?>
	<form enctype="multipart/form-data" method="post" id="wppb-recover-password" class="wppb-user-forms" action="<?php echo esc_url( wppb_curpageurl() ); ?>">
		<ul>
	<?php

        if( !empty( $post_data['passw1'] ) )
            $passw_one = $post_data['passw1'];
        else
            $passw_one = '';

        if( !empty( $post_data['passw2'] ) )
            $passw_two = $post_data['passw2'];
        else
            $passw_two = '';

		$password_label = __( 'Password', 'profile-builder' );
		$repeat_password_label = __( 'Repeat Password', 'profile-builder' );

		$recover_inputPassword = '
			<li class="wppb-form-field passw1">
				<label for="passw1">'. esc_html( $password_label ) .'</label>
				<input class="password" name="passw1" type="password" id="passw1" value="" autocomplete="off" title="'. esc_attr( wppb_password_length_text() ).'" '. apply_filters( 'wppb_recover_password_extra_attr', '', esc_html( $password_label ), 'password' ) .' />
				<span class="wppb-description-delimiter">'. wppb_password_length_text() .' '. wppb_password_strength_description() .'</span>'.
            /* if we have active the password strength checker */
            wppb_password_strength_checker_html().'
			</li><!-- .passw1 -->
			<input type="hidden" name="userData" value="'. esc_attr( $user->ID ).'"/>
			<li class="wppb-form-field passw2">
				<label for="passw2">'. esc_html( $repeat_password_label ) .'</label>
				<input class="password" name="passw2" type="password" id="passw2" value="" autocomplete="off" '. apply_filters( 'wppb_recover_password_extra_attr', '', esc_html( $repeat_password_label ), 'repeat_password' ) .' />
			</li><!-- .passw2 -->';
			
		echo apply_filters( 'wppb_recover_password_form_input', $recover_inputPassword, $passw_one, $passw_two, $user->ID ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
		</ul>
		<p class="form-submit">
			<?php $button_name = __('Reset Password', 'profile-builder'); ?>
			<input name="recover_password2" type="submit" id="wppb-recover-password-button" class="<?php echo esc_attr( apply_filters( 'wppb_recover_submit_class', "submit button" ) ); ?>" value="<?php echo esc_attr( apply_filters('wppb_recover_password_button_name1', $button_name) ); ?>" />
			<input name="action2" type="hidden" id="action2" value="recover_password2" />
			<input name="key" type="hidden" id="key" value="<?php echo esc_attr( isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '' ) ?>" />
		</p><!-- .form-submit -->
		<?php wp_nonce_field( 'verify_true_password_recovery2_'.$user->ID, 'password_recovery_nonce_field2' ); ?>
	</form><!-- #recover_password -->
	<?php
}

/**
 * Function that generates the recover password form
 *
 * @param WP_User $user the user object
 * @param array $post_data $_POST
 *
 */
 function wppb_create_generate_password_form( $post_data ){
	?>
	<form enctype="multipart/form-data" method="post" id="wppb-recover-password" class="wppb-user-forms" action="<?php echo esc_url( wppb_curpageurl() ); ?>">
	<?php
	$wppb_generalSettings = get_option( 'wppb_general_settings' );

	if( !empty( $wppb_generalSettings['loginWith'] ) && $wppb_generalSettings['loginWith'] == 'email' ){
		$recover_notification = '<p>' . __( 'Please enter your email address.', 'profile-builder' );
		$username_email_label = __( 'Email', 'profile-builder' );
	}
	else{
		$recover_notification = '<p>' . __( 'Please enter your username or email address.', 'profile-builder' );
		$username_email_label = __( 'Username or Email', 'profile-builder' );
	}

	$recover_notification .= '<br/>'.__( 'You will receive a link to create a new password via email.', 'profile-builder' ).'</p>';
	echo wp_kses_post( apply_filters( 'wppb_recover_password_message1', $recover_notification ) );

	$username_email = ( isset( $post_data['username_email'] ) ? $post_data['username_email'] : '' );

	$recover_input = '<ul>
			<li class="wppb-form-field wppb-username-email">
				<label for="username_email">'. esc_html( $username_email_label ) .'</label>
				<input class="text-input" name="username_email" type="text" id="username_email" value="'.esc_attr( trim( $username_email ) ).'" '. apply_filters( 'wppb_recover_password_extra_attr', '', esc_html( $username_email_label ), 'username_email' ) .' />
			</li><!-- .username_email --></ul>';
	echo apply_filters( 'wppb_recover_password_generate_password_input', $recover_input, trim( $username_email ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	<p class="form-submit">
		<?php $button_name = __('Get New Password', 'profile-builder'); ?>
		<input name="recover_password" type="submit" id="wppb-recover-password-button" class="<?php echo esc_attr( apply_filters( 'wppb_recover_submit_class', "submit button" ) );?>" value="<?php echo esc_attr( apply_filters('wppb_recover_password_button_name3', $button_name) ); ?>" />
		<input name="action" type="hidden" id="action" value="recover_password" />
	</p>
	<?php wp_nonce_field( 'verify_true_password_recovery', 'password_recovery_nonce_field' ); ?>
	</form>
	<?php
}

/**
 * Determine based on the PB login settings what to display in the email sent on password reset: username or email
 * @param $user
 * @return mixed
 */
function wppb_get_email_display_username($user){
    //Get general settings
    $wppb_generalSettings = get_option( 'wppb_general_settings' );

    if( $wppb_generalSettings['loginWith'] == 'username' || $wppb_generalSettings['loginWith'] == 'usernameemail' )
        $display_username_email = $user->user_login;
    else
        $display_username_email = $user->user_email;

    return $display_username_email;
}

/**
 * Send the email for the password recovery request
 * @param $user
 * @return bool|string|void
 */
function wppb_send_recovery_email( $user, $success ){

    if ( $success == 'wppb_recaptcha_error')
        return false;

    $requested_user_id = $user->ID;
    $requested_user_login = $user->user_login;
    $requested_user_email = $user->user_email;

    //search if there is already an activation key present, if not create one
    $key = wppb_retrieve_activation_key( $requested_user_login );

    $display_username_email = wppb_get_email_display_username($user);

    //send primary email message
    $recovery_email_message  = sprintf( __('Someone requested that the password be reset for the following account: <b>%1$s</b><br/>If this was a mistake, just ignore this email and nothing will happen.<br/>To reset your password, visit the following link:%2$s', 'profile-builder'), $display_username_email, '<a href="'.esc_url( add_query_arg( array( 'key' => $key ), wppb_curpageurl() ) ).'">'.esc_url( add_query_arg( array( 'key' => $key ), wppb_curpageurl() ) ).'</a>' );
    $recovery_email_message  = apply_filters( 'wppb_recover_password_message_content_sent_to_user1', $recovery_email_message, $requested_user_id, $requested_user_login, $requested_user_email );

    $recovery_email_message_title = sprintf(__('Password Reset from %1$s', 'profile-builder'), $blogname = get_option('blogname') );
    $recovery_email_message_title = apply_filters('wppb_recover_password_message_title_sent_to_user1', $recovery_email_message_title, $requested_user_login);

    $recovery_email_from = apply_filters ( 'wppb_recover_password_notification_email_from_field', get_bloginfo( 'name' ) );
    $recovery_email_context = 'email_user_recover';


    $sent = false;
    //send mail to the user notifying him of the reset request
    if (trim($recovery_email_message_title) != '') {
        $sent = wppb_mail($requested_user_email, $recovery_email_message_title, $recovery_email_message, $recovery_email_from, $recovery_email_context);
    }

    return $sent;

}

/**
 * Function that sends the successful password reset email to the user
 * @param $user
 * @param $new_pass
 */
function wppb_send_successful_password_reset_email( $user, $new_pass ){

    $display_username_email = wppb_get_email_display_username($user);

    //send secondary mail to the user containing the username and the new password
    $recovery_email_message  = __( 'You have successfully reset your password.', 'profile-builder' );
    $recovery_email_message  = apply_filters( 'wppb_recover_password_message_content_sent_to_user2', $recovery_email_message, $display_username_email, $new_pass, $user->ID );
    $recovery_email_message_title = sprintf( __('Password Successfully Reset for %1$s on %2$s', 'profile-builder' ), $display_username_email, $blogname = get_option('blogname') );
    $recovery_email_message_title = apply_filters( 'wppb_recover_password_message_title_sent_to_user2', $recovery_email_message_title, $display_username_email );
    $recovery_email_from = apply_filters ( 'wppb_recover_password_success_notification_email_from_field', get_bloginfo( 'name' ) );
    $recovery_email_context = 'email_user_recover_success';
    //send mail to the user notifying him of the reset request
    if ( trim( $recovery_email_message_title ) != '' )
        wppb_mail( $user->user_email, $recovery_email_message_title, $recovery_email_message, $recovery_email_from, $recovery_email_context );
}

/**
 * Function that sends an email to the admin after the password was reset
 * we disable the feature to send the admin a notification mail but can be still used using filters
 * @param $user
 */
function wppb_send_admin_password_reset_email( $user ){

    $display_username_email = wppb_get_email_display_username($user);

    $recovery_admin_email_message = sprintf( __( '%1$s has requested a password change via the password reset feature.<br/>His/her new password is:%2$s', 'profile-builder' ), $display_username_email, '' );
    $recovery_admin_email_message = apply_filters( 'wppb_recover_password_message_content_sent_to_admin', $recovery_admin_email_message, $display_username_email, '', $user->ID );
    //we disable the feature to send the admin a notification mail but can be still used using filters
    $recovery_admin_email_title = '';
    $recovery_admin_email_title = apply_filters( 'wppb_recover_password_message_title_sent_to_admin', $recovery_admin_email_title, $display_username_email );
    $recovery_email_from = apply_filters ( 'wppb_recover_password_success_notification_email_from_field', get_bloginfo( 'name' ) );
    $recovery_admin_email_context = 'email_admin_recover_success';
    //send mail to the admin notifying him of of a user with a password reset request
    if (trim($recovery_admin_email_title) != '')
        wppb_mail(get_option('admin_email'), $recovery_admin_email_title, $recovery_admin_email_message, $recovery_email_from, $recovery_admin_email_context);
}

/**
 * The function for the recover password shortcode
 *
 */
function wppb_front_end_password_recovery(){
    global $wppb_shortcode_on_front;
    $wppb_shortcode_on_front = true;
    global $wppb_password_recovery_shortcode_on_front;
    $wppb_password_recovery_shortcode_on_front = true;
    $password_email_sent = false;
    $password_changed_success = false;

    $output = '<div class="wppb_holder" id="wppb-recover-password-container">';

    global $wpdb;

    // check if the form is being displayed in the Elementor editor
    $is_elementor_edit_mode = false;
    if( class_exists ( '\Elementor\Plugin' ) ){
        $is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    if( is_user_logged_in() && !$is_elementor_edit_mode ) {
        return apply_filters('wppb_recover_password_already_logged_in', __('You are already logged in. You can change your password on the edit profile form.', 'profile-builder'));
    }

    //Get general settings
    $wppb_generalSettings = get_option( 'wppb_general_settings' );

    // If the user entered an email/username, process the request
    if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'recover_password' && isset( $_POST['password_recovery_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( $_POST['password_recovery_nonce_field'] ),'verify_true_password_recovery') ) {
        // filter must be applied on the $_POST variable so that the value returned to the form can be corrected too

        if( !empty( $_POST['username_email'] ) )
            $username_email = apply_filters( 'wppb_before_processing_email_from_forms', sanitize_text_field( $_POST['username_email'] ) );	//we get the raw data
        else
            $username_email = '';

        //check to see if it's an e-mail (and if this is valid/present in the database) or is a username

        // if we do not have an email in the posted date we try to get the email for that user
        if( !is_email( $username_email ) ){
            /* make sure it is a username */
            $username = sanitize_user( $username_email );
            if ( username_exists($username) ){
                $query = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login= %s", $username ) );
                if( !empty( $query[0] ) ){
                    $username_email = $query[0]->user_email;
                }
            }
            else{
                $warning = __( 'The username entered wasn\'t found in the database!', 'profile-builder').'<br/>'.__('Please check that you entered the correct username.', 'profile-builder' );
                $warning = apply_filters( 'wppb_recover_password_sent_message4', $warning );
                $output .= wppb_password_recovery_warning( $warning, 'wppb_recover_password_displayed_message1' );
            }
        }

        // we should have an email by this point
        if ( is_email( $username_email ) ){
            if ( email_exists( $username_email ) ){
                $warning = wppb_check_for_unapproved_user($username_email, 'user_email');
                if ($warning != ''){
                    $output .= wppb_password_recovery_warning( $warning, 'wppb_recover_password_displayed_message1' );
                }else{
                    $success = sprintf( __( 'Check your email for the confirmation link.', 'profile-builder'), $username_email );
                    $success = apply_filters( 'wppb_recover_password_sent_message1', $success, $username_email );

                    if ( $success != 'wppb_recaptcha_error')
                        $output .= wppb_password_recovery_success( $success, 'wppb_recover_password_displayed_message2' );

                    //verify e-mail validity
                    $query = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_email= %s", sanitize_email( $username_email ) ) );
                    if( !empty( $query[0] ) ){
                        $user = $query[0];

                        //send mail to the user notifying him of the reset request
                        $sent = wppb_send_recovery_email( $user, $success );
                        if ($sent === false){
                            $warning = '<b>'. __( 'ERROR', 'profile-builder' ) .': </b>' . sprintf( __( 'There was an error while trying to send the activation link to %1$s!', 'profile-builder' ), $username_email );
                            $warning = apply_filters( 'wppb_recover_password_sent_message_error_sending', $warning );
                            $output .= wppb_password_recovery_warning( $warning, 'wppb_recover_password_displayed_message1' );
                        }
                        else
                            $password_email_sent = true;

                    }

                }
            }elseif ( !email_exists( $username_email ) ){
                // check reCAPTCHA
                $warning = wppb_password_recovery_warning( '', 'wppb_recover_password_displayed_message1' );

                // if there is no reCAPTCHA error show the invalid email address error
                if( $warning === '' ) {
                    $warning = __('The email address entered wasn\'t found in the database!', 'profile-builder').'<br/>'.__('Please check that you entered the correct email address.', 'profile-builder');
                    $warning = apply_filters('wppb_recover_password_sent_message2', $warning);
                    $output .= '<p class="wppb-warning">'.$warning.'</p>';
                } else {
                    $output .= $warning;
                }
            }
        }
    }
    // If the user used the correct key-code, update his/her password
    elseif ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action2'] ) && $_POST['action2'] === 'recover_password2' && isset( $_POST['password_recovery_nonce_field2'] ) && isset( $_POST['userData'] ) && wp_verify_nonce( sanitize_text_field( $_POST['password_recovery_nonce_field2'] ), 'verify_true_password_recovery2_'.absint( sanitize_text_field( $_POST['userData'] ) ) ) ) {

        $password_change_message = '';

        if( ( !empty( $_POST['passw1'] ) && !empty( $_POST['passw2'] ) ) ){

            //get the login name and key and verify if they match the ones in the database
            if( isset( $_POST['key'] ) )
                $key = sanitize_text_field( $_POST['key'] );
            else
                $key = '';

			if( empty( $key ) ){
				$password_change_message = __('The key cannot be empty!', 'profile-builder');
				$output .= wppb_password_recovery_error( $password_change_message, 'wppb_recover_password_password_changed_message2' );
			}

            $user_object = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s", $key ) );
            if( empty( $user_object ) || ( !empty( $user_object ) && $user_object->ID === absint( $_POST['userData'] ) ) ){
                $password_change_message = __('Invalid key!', 'profile-builder');
                $output .= wppb_password_recovery_error( $password_change_message, 'wppb_recover_password_password_changed_message2' );
            }

            if( $_POST['passw1'] != $_POST['passw2'] ) {
                $password_change_message = __('The entered passwords don\'t match!', 'profile-builder');
                $output .= wppb_password_recovery_error( $password_change_message, 'wppb_recover_password_password_changed_message2' );
            }

            if( !empty( $wppb_generalSettings['minimum_password_length'] ) || ( isset( $_POST['wppb_password_strength'] ) && !empty( $wppb_generalSettings['minimum_password_strength'] ) ) ){
                if( wppb_check_password_length( $_POST['passw1'] ) ){//phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                    $password_change_message = sprintf( __( "The password must have the minimum length of %s characters", "profile-builder" ), $wppb_generalSettings['minimum_password_length'] ) . '<br/>';
                    $output .= wppb_password_recovery_error( $password_change_message, 'wppb_recover_password_password_changed_message2' );
                }
                if( wppb_check_password_strength() ){
                    $password_change_message = sprintf( __( "The password must have a minimum strength of %s", "profile-builder" ), wppb_check_password_strength() );
                    $output .= wppb_password_recovery_error( $password_change_message, 'wppb_recover_password_password_changed_message2' );
                }
            }

            if( empty($password_change_message) ){

                $password_change_message = __( 'Your password has been successfully changed!', 'profile-builder' );
                $output .= wppb_password_recovery_success( $password_change_message, 'wppb_recover_password_password_changed_message1' );
                $password_changed_success = true;



                $userID = absint( $_POST['userData'] );
                $new_pass = $_POST['passw1']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

                //update the new password and delete the key
                do_action( 'wppb_password_reset', $userID, $new_pass );
                wp_set_password( $new_pass, $userID );

                /* log out of all sessions on password reset */
                $sessions = WP_Session_Tokens::get_instance( $userID );
                $sessions->destroy_all();

                $user_info = get_userdata( $userID );

                //send email to user
                wppb_send_successful_password_reset_email( $user_info, $new_pass );

                //send email to admin
                wppb_send_admin_password_reset_email( $user_info );

                // CHECK FOR REDIRECT
                $redirect_url = wppb_get_redirect_url( 'normal', 'after_success_password_reset', '', sanitize_user( $user_info->user_login ) );

                //log the user in if the option was selected
                if ( apply_filters( 'wppb_recover_password_autologin', false ) ){
                    $nonce = wp_create_nonce( 'autologin-'. sanitize_user( $user_info->ID ) .'-'. (int)( time() / 60 ) );

                    //use the after_login redirect if no after_success_password_reset redirect is set
                    if( empty( $redirect_url ) ) {
                        $redirect_url = wppb_get_redirect_url( 'normal', 'after_login', '', sanitize_user( $user_info->user_login ) );
                        $redirect_url = apply_filters( 'wppb_after_recover_and_login', $redirect_url );
                    }
                    if( empty( $redirect_url ) ) {
                        $redirect_url = remove_query_arg( 'key', wppb_curpageurl() );
                    }

                    $redirect_url = add_query_arg( array( 'autologin' => 'true', 'uid' => sanitize_user( $user_info->ID ), '_wpnonce' => $nonce ), $redirect_url );
                }

                $redirect_delay = apply_filters( 'wppb_success_password_reset_redirect_delay', 3, sanitize_user( $user_info->user_login ) );
                $redirect_message = wppb_build_redirect( $redirect_url, $redirect_delay, 'after_success_password_reset' );

                if( isset( $redirect_message ) && ! empty( $redirect_message ) ) {
                    $output .= '<p>' . $redirect_message . '</p>';
                }
            }
        }
        else{
            $password_change_message .= __( "The password must not be empty!", "profile-builder" );
            $output .= wppb_password_recovery_error( $password_change_message, 'wppb_recover_password_password_changed_message2' );
        }
    }

    // use this action hook to add extra content before the password recovery form
    do_action( 'wppb_before_recover_password_fields' );


    //this is the part that shows the forms
    if( isset( $_GET['key'] ) ){

		$key = sanitize_text_field( $_GET['key'] );

        if( !empty( $key ) && !$password_changed_success ) {

            //get the login name and key and verify if they match the ones in the database
            $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s", $key ) );

            if( !empty( $user ) ) {
                ob_start();
                wppb_create_recover_password_form($user, $_POST);
                $output .= ob_get_contents();
                ob_end_clean();
            }
            else {
                $output .= wppb_password_recovery_error('<b>' . __('ERROR:', 'profile-builder') . '</b>' . __('Invalid key!', 'profile-builder'), 'wppb_recover_password_invalid_key_message');
            }

        } elseif ( !$password_changed_success && !$password_email_sent ) {
			ob_start();
			wppb_create_generate_password_form($_POST);
			$output .= ob_get_contents();
			ob_end_clean();
		}

    } else {
        if( !$password_email_sent ) {
            ob_start();
            wppb_create_generate_password_form($_POST);
            $output .= ob_get_contents();
            ob_end_clean();
        }
    }

    // use this action hook to add extra content after the password recovery form.
    do_action( 'wppb_after_recover_password_fields' );

    $output .= '</div>';
    return $output;
}

/* function for displaying success messages on the recover password page */
function wppb_password_recovery_success( $message, $filter ){
    return apply_filters( $filter, '<p class="wppb-success">'.$message.'</p>', $message );
}

/* function for displaying warning messages on the recover password page */
function wppb_password_recovery_warning( $message, $filter ){
    if( $message !== '' ) {
        return apply_filters( $filter, '<p class="wppb-warning">'.$message.'</p>', $message );
    } else {
        return apply_filters( $filter, '', $message );
    }
}

/* function for displaying error messages on the recover password page */
function wppb_password_recovery_error( $message, $filter ){
    return apply_filters( $filter, '<p class="wppb-error">'.$message.'</p>', $message );
}
