<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'init', 'wppb_process_login' );
function wppb_process_login(){

	if( !isset($_REQUEST['wppb_login']) )
		return;

	do_action( 'login_init' );
	do_action( "login_form_login" );
	do_action( 'wppb_process_login_start' );

	if( !isset( $_POST['CSRFToken-wppb'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['CSRFToken-wppb'] ), 'wppb_login' ) )
	    return;

	$secure_cookie = '';
	// If the user wants ssl but the session is not ssl, force a secure cookie.
	if ( !empty($_POST['log']) && !force_ssl_admin() ) {
		$user_name = sanitize_user($_POST['log']);
		$user = get_user_by( 'login', $user_name );

		if ( ! $user && strpos( $user_name, '@' ) ) {
			$user = get_user_by( 'email', $user_name );
		}

		if ( $user ) {
			if ( get_user_option('use_ssl', $user->ID) ) {
				$secure_cookie = true;
				force_ssl_admin(true);
			}
		}
	}

	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = esc_url_raw( $_REQUEST['redirect_to'] );
	}

	$user = wp_signon( array(), $secure_cookie );

	if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
		if ( headers_sent() ) {
			/* translators: 1: Browser cookie documentation URL, 2: Support forums URL */
			$user = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.', 'profile-builder' ),
				'https://codex.wordpress.org/Cookies', 'https://wordpress.org/support/' ) );
		}
	}

	$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : '';
	/**
	 * Filters the login redirect URL.
	 */
	$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );

	do_action( 'wppb_process_login_end' );

	if ( !is_wp_error($user) ) {
		if ( $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) {
			// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
			if ( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin( $user->ID ) )
				$redirect_to = user_admin_url();
			elseif ( is_multisite() && !$user->has_cap('read') )
				$redirect_to = get_dashboard_url( $user->ID );
			elseif ( !$user->has_cap('edit_posts') )
				$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();

			wp_redirect( $redirect_to );
			exit();
		}
		wp_safe_redirect($redirect_to);
		exit();
	}
	else{
		wp_safe_redirect($redirect_to);
		exit();
	}
}
/**
 * Provides a simple login form
 *
 * The login format HTML is echoed by default. Pass a false value for `$echo` to return it instead.
 *
 * @param array $args {
 *     Optional. Array of options to control the form output. Default empty array.
 *
 *     @type bool   $echo                      Whether to display the login form or return the form HTML code.
 *                                             Default true (echo).
 *     @type string $redirect                  URL to redirect to. Must be absolute, as in "https://example.com/mypage/".
 *                                             Default is to redirect back to the request URI.
 *     @type string $form_id                   ID attribute value for the form. Default 'loginform'.
 *     @type string $label_username            Label for the username or email address field. Default 'Username or Email Address'.
 *     @type string $label_username            Label for the username or email address field. Default 'Username or Email Address'.
 *     @type string $login_username_input_type Type of input field for the username or email address.
 *     @type string $label_remember            Label for the remember field. Default 'Remember Me'.
 *     @type string $label_log_in              Label for the submit button. Default 'Log In'.
 *     @type string $id_username               ID attribute value for the username field. Default 'user_login'.
 *     @type string $id_password               ID attribute value for the password field. Default 'user_pass'.
 *     @type string $id_remember               ID attribute value for the remember field. Default 'rememberme'.
 *     @type string $id_submit                 ID attribute value for the submit button. Default 'wp-submit'.
 *     @type bool   $remember                  Whether to display the "rememberme" checkbox in the form.
 *     @type string $value_username            Default value for the username field. Default empty.
 *     @type bool   $value_remember            Whether the "Remember Me" checkbox should be checked by default.
 *                                             Default false (unchecked).
 *
 * }
 * @return string|void String when retrieving.
 */
function wppb_login_form( $args = array() ) {

    $default_redirect = '';
    if( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) )
        $default_redirect = esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	$defaults = array(
		'echo'                      => true,
		// Default 'redirect' value takes the user back to the request URI.
		'redirect'                  => $default_redirect,
		'form_id'                   => 'wppb-loginform',
		'label_username'            => __( 'Username or Email Address', 'profile-builder' ),
        'login_username_input_type' => 'text',
		'label_password'            => __( 'Password', 'profile-builder' ),
		'label_remember'            => __( 'Remember Me', 'profile-builder' ),
		'label_log_in'              => __( 'Log In', 'profile-builder' ),
		'id_username'               => 'user_login',
		'id_password'               => 'user_pass',
		'id_remember'               => 'rememberme',
		'id_submit'                 => 'wp-submit',
		'remember'                  => true,
		'value_username'            => '',
		// Set 'value_remember' to true to default the "Remember me" checkbox to checked.
		'value_remember'            => false,
	);

	/**
	 * Filters the default login form output arguments.
	 */
	$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );

	/**
	 * Filters content to display at the top of the login form.
	 */
	$login_form_top = apply_filters( 'login_form_top', '', $args );

	/**
	 * Filters content to display in the middle of the login form.
	 */
	$login_form_middle = apply_filters( 'login_form_middle', '', $args );

	/**
	 * Filters content to display at the bottom of the login form.
	 */
	$login_form_bottom = apply_filters( 'login_form_bottom', '', $args );

	if( in_the_loop() )
		$form_location = 'page';
	else
		$form_location = 'widget';

	// if an error is being shown pass the original referer forward
    if( isset( $_GET['wppb_referer_url'] ) ){
        $wppb_referer_url = esc_url_raw ( $_GET['wppb_referer_url'] );
    } else {
        $wppb_referer_url = esc_url_raw ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
    }

	$form = '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="'. esc_url( wppb_curpageurl() ) .'" method="post">
			' . $login_form_top . '
			<p class="login-username">
				<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
				<input type="' . esc_attr( $args['login_username_input_type'] ) . '" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" size="20" />
			</p>
			<p class="login-password">
				<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
				<input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" size="20" '. apply_filters( 'wppb_login_password_extra_attributes', '' ) .'/>';

    /* add the HTML for the visibility toggle */
    $form .= wppb_password_visibility_toggle_html();

    $form .='
			</p>
			' . $login_form_middle . '
			' . ( $args['remember'] ? '<p class="login-remember"><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /><label for="' . esc_attr( $args['id_remember'] ) . '">' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="'. esc_attr( apply_filters( 'wppb_login_submit_class', "button button-primary" ) ) . '" value="' . esc_attr( $args['label_log_in'] ) . '" />
				<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
			</p>
			<input type="hidden" name="wppb_login" value="true"/>
			<input type="hidden" name="wppb_form_location" value="'. esc_attr( $form_location ) .'"/>
			<input type="hidden" name="wppb_request_url" value="'. esc_url( wppb_curpageurl() ).'"/>
			<input type="hidden" name="wppb_lostpassword_url" value="'.esc_url( $args['lostpassword_url'] ).'"/>
			<input type="hidden" name="wppb_redirect_priority" value="'. esc_attr( isset( $args['redirect_priority'] ) ? $args['redirect_priority'] : '' ) .'"/>
			<input type="hidden" name="wppb_referer_url" value="'. esc_url( $wppb_referer_url ) .'"/>
			'. wp_nonce_field( 'wppb_login', 'CSRFToken-wppb', true, false ) .'
			<input type="hidden" name="wppb_redirect_check" value="true"/>
			' . $login_form_bottom . '
		</form>';

	if ( $args['echo'] )
		echo $form; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* escaped above */
	else
		return $form;
}

// when email login is enabled we need to change the post data for the username
function wppb_change_login_with_email(){
    if( !empty( $_POST['log'] ) ){
		// only do this for our form
		if( isset( $_POST['wppb_login'] ) ){
			global $wpdb, $_POST, $wp_version;
			// apply filter to allow stripping slashes if necessary
			$_POST['log'] = apply_filters( 'wppb_before_processing_email_from_forms', sanitize_text_field( $_POST['log'] ) );

			/* since version 4.5 there is in the core the option to login with email so we don't need the bellow code but for backward compatibility we will keep it */
			if( version_compare( $wp_version, '4.5.0' ) >= 0 && apply_filters( 'wppb_allow_login_with_username_when_is_set_to_email', false ) )
				return;

			$wppb_generalSettings = get_option( 'wppb_general_settings' );

			// if this setting is active, the posted username is, in fact the user's email
			if( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'email' ) ){
				if( !is_email( $_POST['log'] ) && !apply_filters( 'wppb_allow_login_with_username_when_is_set_to_email', false ) ){
					$_POST['log'] = 'this_is_an_invalid_email' . time();
				}
				else {
					$username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM $wpdb->users WHERE user_email= %s LIMIT 1", sanitize_email($_POST['log'])));

					if (!empty($username))
						$_POST['log'] = $username;

					else {
						// if we don't have a username for the email entered we can't have an empty username because we will receive a field empty error
						$_POST['log'] = 'this_is_an_invalid_email' . time();
					}
				}
			}

			// if this setting is active, the posted username is, in fact the user's email or username
			if( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'usernameemail' ) ) {
				if( is_email( $_POST['log'] ) ) {
					$username = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE user_email= %s LIMIT 1", sanitize_email( $_POST['log'] ) ) );
				} else {
					$username = sanitize_user( $_POST['log'] );
				}

				if( !empty( $username ) )
					$_POST['log'] = $username;

				else {
					// if we don't have a username for the email entered we can't have an empty username because we will receive a field empty error
					$_POST['log'] = 'this_is_an_invalid_email'.time();
				}
			}
		}
	}
}
add_action( 'login_init', 'wppb_change_login_with_email' );

function wppb_resend_confirmation_email() {
    if( !isset( $_GET['wppb-action'] ) || $_GET['wppb-action'] != 'resend_email_confirmation' || !isset( $_GET['email'] ))
        return;

    $transient_check_key = Wordpress_Creation_Kit_PB::wck_generate_slug( sanitize_email( $_GET['email'] ));
    $transient_check = get_transient('wppb_confirmation_email_already_sent_'.$transient_check_key);

    if ( $transient_check === false ) {

        if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce(sanitize_text_field( $_GET['_wpnonce'] ), 'wppb_confirmation_url_nonce' ))
            return;

        include_once(plugin_dir_path(__FILE__) . '../features/email-confirmation/email-confirmation.php');

        if ( !class_exists('PB_Mustache_Generate_Template') && defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' ) )
            include_once( WPPB_PAID_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' );

        global $wpdb;
        $sql_result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", sanitize_email( $_GET['email'] )), ARRAY_A );

        // if the email address exists in wp_signups table, resend Confirmation Email and redirect to display notification
        if ( $sql_result ) {
            wppb_signup_user_notification( sanitize_text_field( $sql_result['user_login'] ), sanitize_email( $sql_result['user_email'] ), $sql_result['activation_key'], $sql_result['meta'] );
            $transient_key = Wordpress_Creation_Kit_PB::wck_generate_slug( sanitize_email( $_GET['email'] ));
            set_transient('wppb_confirmation_email_already_sent_' . $transient_key, true, 900 );
            $error_string = '<strong>' . __('SUCCESS', 'profile-builder') . '</strong>: ' . sprintf( __( 'Activation email sent to %s', 'profile-builder' ), sanitize_email( $_GET['email'] ));
            $wppb_success_message_nonce = wp_create_nonce( 'wppb_login_error_'.$error_string);
            $current_url = wppb_curpageurl();
            $arr_params = array('loginerror' => urlencode(base64_encode($error_string)), '_wpnonce' => $wppb_success_message_nonce, 'request_form_location' => 'page', 'wppb_message_type' => 'success');
            $redirect_to = add_query_arg($arr_params, $current_url);
            wp_safe_redirect($redirect_to);
            exit();
        }

    }
}
add_action('init', 'wppb_resend_confirmation_email');

function wppb_change_error_message($error_message) {
	
	if( isset( $_REQUEST['log'] ) ){
		global $wpdb;
		$check_user = sanitize_text_field( $_REQUEST['log'] );
		$wppb_generalSettings = get_option( 'wppb_general_settings' );

		if ( !empty( $wppb_generalSettings['emailConfirmation'] ) && $wppb_generalSettings['emailConfirmation'] === 'yes' ) {
			if ( is_email( $check_user ))
				$sql_result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", sanitize_email( $check_user )), ARRAY_A );
			else {
				$sql_result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_login = %s", sanitize_user( $check_user )), ARRAY_A );
				if ( $sql_result )
					$check_user = $sql_result['user_email'];
			}
		}

		// if the email address exists in wp_signups table, display message and link to resend Confirmation Email
		if ( isset($sql_result) ) {
			$confirmation_url_nonce = wp_create_nonce( 'wppb_confirmation_url_nonce' );
			$current_url = wppb_curpageurl();
			$arr_params = array('email' => sanitize_email( $check_user ), 'wppb-action' => 'resend_email_confirmation', '_wpnonce' => $confirmation_url_nonce);
			$confirmation_url = add_query_arg($arr_params, $current_url);
			$error_message = '<strong>' . __('ERROR', 'profile-builder') . '</strong>: ' . sprintf( __( 'You need to confirm your Email Address before logging in! To resend the Confirmation Email  %1$sclick here%2$s', 'profile-builder' ), '<a href="' . esc_url( $confirmation_url ) . '" title="Resend Confirmation Email">', '</a>.' );
		}
	}

    return $error_message;

}
add_filter('wppb_login_invalid_username_error_message', 'wppb_change_error_message');

/**
 * Remove email login when username login is selected
 * inspiration from https://wordpress.org/plugins/no-login-by-email-address/
 */
$wppb_generalSettings = get_option( 'wppb_general_settings' );
if( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'username' ) ) {
	function wppb_login_username_label()
	{
		add_filter('gettext', 'wppb_login_username_label_change', 20, 3);
		function wppb_login_username_label_change($translated_text, $text, $domain)
		{
			if ($text === 'Username or Email') {
				$translated_text = __( 'Username', 'profile-builder' );
			}
			return $translated_text;
		}
	}

	add_action('login_head', 'wppb_login_username_label');

	/**
	 * Filter wp_login_form username default
	 *
	 */
	function wppb_change_login_username_label($defaults)
	{
		$defaults['label_username'] = __( 'Username', 'profile-builder' );
		return $defaults;
	}

	add_filter('login_form_defaults', 'wppb_change_login_username_label');

	/**
	 * Remove email/password authentication
	 *
	 */
	remove_filter('authenticate', 'wp_authenticate_email_password', 20);
}

// login redirect filter. used to redirect from wp-login.php if it errors out
function wppb_login_redirect( $redirect_to, $requested_redirect_to, $user ){
    // custom redirect after login on default wp login form
    if( ! isset( $_POST['wppb_login'] ) && ! is_wp_error( $user ) ) {
		$original_redirect_to = $redirect_to;

        // we don't have an error make sure to remove the error from the query arg
        $redirect_to = remove_query_arg( 'loginerror', $redirect_to );

        // CHECK FOR REDIRECT
        $redirect_to = wppb_get_redirect_url( 'normal', 'after_login', $redirect_to, $user );
        $redirect_to = apply_filters( 'wppb_after_login_redirect_url', $redirect_to );

		if ( $redirect_to === '' ){
			$redirect_to = $original_redirect_to;
		}
    }

	// if login action initialized by our form
    if( isset( $_POST['wppb_login'] ) ){
		if( is_wp_error( $user ) ) {
            // if we don't have a successful login we must redirect to the url of the form, so make sure this happens
            if( isset( $_POST['wppb_request_url'] ) )
                $redirect_to = esc_url_raw( $_POST['wppb_request_url'] );
            if( isset( $_POST['wppb_form_location'] ) )
                $request_form_location = sanitize_text_field( $_POST['wppb_form_location'] );
            $error_string = $user->get_error_message();

            $wppb_generalSettings = get_option('wppb_general_settings');

            if (isset($wppb_generalSettings['loginWith'])) {

				$lost_pass_url = site_url('/wp-login.php?action=lostpassword');
                // if the Login shortcode has a lostpassword argument set, give the lost password error link that value
                if (!empty($_POST['wppb_lostpassword_url'])) {
                    $lost_pass_url = esc_url_raw( $_POST['wppb_lostpassword_url'] );
                    if ( wppb_check_missing_http( $lost_pass_url ) )
                        $lost_pass_url = "http://" . $lost_pass_url;
                }
                //apply filter to allow changing Lost your Password link
                $lost_pass_url = apply_filters('wppb_pre_login_url_filter', $lost_pass_url);

				/* start building the error string */
				if( in_array( $user->get_error_code(), array( 'empty_username', 'empty_password', 'invalid_username', 'incorrect_password' ) ) )
					$error_string = '<strong>' . __('ERROR', 'profile-builder') . '</strong>: ';


				if ( $user->get_error_code() == 'empty_password' ) {
					$error_string .= __( 'The password field is empty.', 'profile-builder' ) . ' ';
				}

                if ($user->get_error_code() == 'incorrect_password') {
                    $error_string .= __('The password you entered is incorrect.', 'profile-builder') . ' ';
                }

				if ( $user->get_error_code() == 'empty_username' ) {
					if ($wppb_generalSettings['loginWith'] == 'email')// if login with email is enabled change the word username with email
						$error_string .= __('The email field is empty.', 'profile-builder') . ' ';
					else if( $wppb_generalSettings['loginWith'] == 'usernameemail' )// if login with username and email is enabled change the word username with username or email
						$error_string .= __('The username/email field is empty', 'profile-builder') . ' ';
					else
						$error_string .= __('The username field is empty', 'profile-builder') . ' ';
				}

                if ($user->get_error_code() == 'invalid_username') {
                    if ($wppb_generalSettings['loginWith'] == 'email')// if login with email is enabled change the word username with email
                        $error_string .= __('Invalid email.', 'profile-builder') . ' ';
                    else if( $wppb_generalSettings['loginWith'] == 'usernameemail' )// if login with username and email is enabled change the word username with username or email
                        $error_string .= __('Invalid username or email.', 'profile-builder') . ' ';
                    else
                        $error_string .= __('Invalid username.', 'profile-builder') . ' ';

                    $error_string = apply_filters('wppb_login_invalid_username_error_message', $error_string);
                }

				if( $user->get_error_code() == 'incorrect_password' || $user->get_error_code() == 'invalid_username' && empty( $message_check = apply_filters('wppb_login_invalid_username_error_message', '' )))
					$error_string .= '<a href="' . esc_url( $lost_pass_url ) . '" title="' . __('Password Lost and Found.', 'profile-builder') . '">' . __('Lost your password?', 'profile-builder') . '</a>';

            }

            // if the error string is empty it means that none of the fields were completed
            if (empty($error_string) || ( in_array( 'empty_username', $user->get_error_codes() ) && in_array( 'empty_password', $user->get_error_codes() ) ) ) {
                $error_string = '<strong>' . __('ERROR', 'profile-builder') . '</strong>: ' . __('Both fields are empty.', 'profile-builder') . ' ';
                $error_string = apply_filters('wppb_login_empty_fields_error_message', $error_string);
            }

            $error_string = apply_filters('wppb_login_wp_error_message', $error_string, $user);
            $wppb_error_string_nonce = wp_create_nonce( 'wppb_login_error_'.$error_string );

            // encode the error string and send it as a GET parameter
            if ( isset($_POST['wppb_referer_url']) && $_POST['wppb_referer_url'] !== '' ) {
                $arr_params = array('loginerror' => urlencode(base64_encode($error_string)), '_wpnonce' => $wppb_error_string_nonce, 'request_form_location' => $request_form_location, 'wppb_referer_url' => urlencode(esc_url_raw( $_POST['wppb_referer_url'] )));
            } else {
                $arr_params = array('loginerror' => urlencode(base64_encode($error_string)), '_wpnonce' => $wppb_error_string_nonce, 'request_form_location' => $request_form_location);
            }

            if ($user->get_error_code() == 'wppb_login_auth') {
                $arr_params['login_auth'] = 'true';
            }

            $redirect_to = add_query_arg($arr_params, $redirect_to);
        }
		else{
			// we don't have an error make sure to remove the error from the query arg
			$redirect_to = remove_query_arg( 'loginerror', $redirect_to );

            // CHECK FOR REDIRECT
            if( isset( $_POST['wppb_redirect_priority'] ) )
                $redirect_to = wppb_get_redirect_url( sanitize_text_field( $_POST['wppb_redirect_priority'] ), 'after_login', $redirect_to, $user );

            $redirect_to = apply_filters( 'wppb_after_login_redirect_url', $redirect_to );

			// This should not be empty, if we don't have a redirect, set it to the current page URL
			if( empty( $redirect_to ) )
				$redirect_to = wppb_curpageurl();
		}
	}

    // if "wppb_message_type = success" is present the message will show up in a green box instead of red
    if ( isset( $_GET['wppb_message_type'] ) && $_GET['wppb_message_type'] == 'success' )
        $redirect_to = remove_query_arg( 'wppb_message_type', $redirect_to );

    return $redirect_to;
}
add_filter( 'login_redirect', 'wppb_login_redirect', 20, 3 );


/* shortcode function */
function wppb_front_end_login( $atts ){
    global $wppb_shortcode_on_front;
    $wppb_shortcode_on_front = true;
    global $wppb_login_shortcode_on_front;
    $wppb_login_shortcode_on_front = true;
	/* define a global so we now we have the shortcode login present */
	global $wppb_login_shortcode;
	$wppb_login_shortcode = true;

    extract( shortcode_atts( array( 'display' => true, 'redirect' => '', 'redirect_url' => '', 'logout_redirect_url' => wppb_curpageurl(), 'register_url' => '', 'lostpassword_url' => '', 'redirect_priority' => 'normal', 'show_2fa_field' => '' ), $atts ) );

	$wppb_generalSettings = get_option('wppb_general_settings');

    // check if the form is being displayed in the Elementor editor
	$is_elementor_edit_mode = false;
    if( class_exists ( '\Elementor\Plugin' ) ){
        $is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

	if( !is_user_logged_in() || $is_elementor_edit_mode ){
		// set up the form arguments
		$form_args = array( 'echo' => false, 'id_submit' => 'wppb-submit' );

		// maybe set up the redirect argument
		if( ! empty( $redirect ) ) {
			$redirect_url = $redirect;
		}

        if ( ! empty( $redirect_url ) ) {
            if( $redirect_priority == 'top' ) {
                $form_args['redirect_priority'] = 'top';
            } else {
                $form_args['redirect_priority'] = 'normal';
            }

			$form_args['redirect'] = trim( $redirect_url );
		}

        $form_args['login_username_input_type'] = 'text';

		// change the label argument for username is login with email is enabled
		if ( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'email' ) ) {
            $form_args['label_username'] = __('Email', 'profile-builder');
            $form_args['login_username_input_type'] = 'email';
        }

        if ( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'username' ) ) {
            $form_args['label_username'] = __('Username', 'profile-builder');
        }

		// change the label argument for username on login with username or email when Username and Email is enabled
		if ( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'usernameemail' ) )
			$form_args['label_username'] = __( 'Username or Email', 'profile-builder' );

        // Check if 2fa is required
		if( class_exists( 'WPPB_Two_Factor_Authenticator' ) ){
			$wppb_auth = new WPPB_Two_Factor_Authenticator;
			$wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );
			if ( ( isset( $_GET['login_auth'] ) && $_GET['login_auth'] === 'true' ) ||
				( ( isset($wppb_two_factor_authentication_settings['enabled']) && $wppb_two_factor_authentication_settings['enabled'] === 'yes' ) && $show_2fa_field === 'yes' ) ){
				add_action( 'login_form_middle', array( $wppb_auth, 'auth_code_field') );
			}
		}

		// initialize our form variable
		$login_form = '';

		// display our login errors
		if( ( isset( $_GET['loginerror'] ) || isset( $_POST['loginerror'] ) ) && isset( $_GET['_wpnonce'] ) ){
		    $error_string = urldecode( base64_decode( isset( $_GET['loginerror'] ) ? sanitize_text_field( $_GET['loginerror'] ) : sanitize_text_field( $_POST['loginerror'] ) ) );
            if( wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'wppb_login_error_'. $error_string ) ) {
                if ( isset( $_GET['wppb_message_type'] ) && $_GET['wppb_message_type'] == 'success' )
                    $message_type = 'wppb-success';
                else $message_type = 'wppb-error';
                $loginerror = '<p class="'. $message_type .'">' . wp_kses_post($error_string) . '</p><!-- .error -->';
                if (isset($_GET['request_form_location'])) {
                    if ($_GET['request_form_location'] === 'widget' && !in_the_loop()) {
                        $login_form .= $loginerror;
                    } elseif ($_GET['request_form_location'] === 'page' && in_the_loop()) {
                        $login_form .= $loginerror;
                    }
                }
            }
		}
		// build our form
		$login_form .= '<div id="wppb-login-wrap" class="wppb-user-forms">';
        $form_args['lostpassword_url'] = $lostpassword_url;
		$login_form .= wppb_login_form( apply_filters( 'wppb_login_form_args', $form_args ) );

		if ((!empty($register_url)) || (!empty($lostpassword_url))) {
                $login_form .= '<p class="login-register-lost-password">';
                $i = 0;
                if (!empty($register_url)) {
                    if ( wppb_check_missing_http( $register_url ) ) $register_url = "http://" . $register_url;
                    $login_form .= '<a class="login-register" href="' . esc_url($register_url) . '">'. apply_filters('wppb_login_register_text', __('Register','profile-builder')) .'</a>';
                    $i++;
                }
                if (!empty($lostpassword_url)) {
                    if ($i != 0) $login_form .= ' | ';
                    if ( wppb_check_missing_http( $lostpassword_url ) ) $lostpassword_url = "http://" . $lostpassword_url;
                    $login_form .= '<a class="login-lost-password" href="'. esc_url($lostpassword_url) .'">'. apply_filters('wppb_login_lostpass_text', __('Lost your password?','profile-builder')) .'</a>';
                }
                $login_form .= '</p>';
        }

        $login_form .= apply_filters( 'wppb_login_form_bottom', '', $form_args );

        $login_form .= '</div>';
		return $login_form;

	}else{
		$user_ID = get_current_user_id();
		$wppb_user = get_userdata( $user_ID );

		if( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'email' ) )
			$display_name = $wppb_user->user_email;

		elseif($wppb_user->display_name !== '')
			$display_name = $wppb_user->user_login;

		else
			$display_name = $wppb_user->display_name;

		if( isset( $wppb_generalSettings['loginWith'] ) && ( $wppb_generalSettings['loginWith'] == 'usernameemail' ) )
			if( $wppb_user->user_login == Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $wppb_user->user_email ) ) )
			$display_name = $wppb_user->user_email;

		elseif($wppb_user->display_name !== '')
			$display_name = $wppb_user->user_login;

		else
			$display_name = $wppb_user->display_name;

		$logged_in_message = '<p class="wppb-alert">';

        // CHECK FOR REDIRECT
        $logout_redirect_url = wppb_get_redirect_url( $redirect_priority, 'after_logout', $logout_redirect_url, $wppb_user );
        $logout_redirect_url = apply_filters( 'wppb_after_logout_redirect_url', $logout_redirect_url );

        $logout_url = '<a href="'.wp_logout_url( $logout_redirect_url ).'" class="wppb-logout-url" title="'.__( 'Log out of this account', 'profile-builder' ).'">'. __('Log out &raquo;','profile-builder').'</a>';
		$logged_in_message .= sprintf(__( 'You are currently logged in as %1$s. %2$s', 'profile-builder' ), $display_name, $logout_url );

        $logged_in_message .= '</p><!-- .wppb-alert-->';

		return apply_filters( 'wppb_login_message', $logged_in_message, $wppb_user->ID, $display_name );
	}
}

function wppb_login_security_check( $user, $password ) {
	if( apply_filters( 'wppb_enable_csrf_token_login_form', false ) ){
		if (isset($_POST['wppb_login'])) {
			if (!isset($_POST['CSRFToken-wppb']) || !wp_verify_nonce( sanitize_text_field( $_POST['CSRFToken-wppb'] ), 'wppb_login')) {
				$errorMessage = __('You are not allowed to do this.', 'profile-builder');
				return new WP_Error('wppb_login_csrf_token_error', $errorMessage);
			}
		}
	}

    return $user;
}
add_filter( 'wp_authenticate_user', 'wppb_login_security_check', 10, 2 );
