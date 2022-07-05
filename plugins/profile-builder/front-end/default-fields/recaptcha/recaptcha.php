<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _wppb_encodeQS($data)
{
    $req = "";
    foreach ($data as $key => $value) {
        $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
    }
    // Cut the last '&'
    $req=substr($req, 0, strlen($req)-1);
    return $req;
}



/**
 * Submits an HTTP GET to a reCAPTCHA server
 * @param string $path
 * @param array $data
 */
function _wppb_submitHTTPGet($path, $data)
{
    $req = _wppb_encodeQS($data);
    $response = wp_remote_get($path . $req);

    if ( ! is_wp_error( $response ))
        return $response["body"];
}

/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 * @param string $error The error given by reCAPTCHA (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

 * @return string - The HTML to be embedded in the user's form.
 */
function wppb_recaptcha_get_html ( $pubkey, $form_name='' ){
    global $wppb_recaptcha_forms; // is the counter for the number of forms that have recaptcha so we always have unique ids on the element
    if( is_null( $wppb_recaptcha_forms ) )
        $wppb_recaptcha_forms = 0;
    $wppb_recaptcha_forms++;

    $field = wppb_get_recaptcha_field();

    if ( empty($pubkey) )
        echo '<span class="error">'. esc_html__("To use reCAPTCHA you must get an API key from", "profile-builder"). " <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a></span><br/><br/>";

    // extra class needed for Invisible reCAPTCHA html
    $invisible_class = '';
    if ( isset($field['recaptcha-type']) && ($field['recaptcha-type'] == 'invisible') ) {
        $invisible_class = 'wppb-invisible-recaptcha';
    }

    // reCAPTCHA html for all forms and we make sure we have a unique id for v2
    return '<div id="wppb-recaptcha-element-'.$form_name.$wppb_recaptcha_forms.'" class="wppb-recaptcha-element '.$invisible_class.'"></div>';
}



/**
 *  Add reCAPTCHA scripts to both front-end PB forms (with support for multiple forms) as well as Default WP forms
 */
function wppb_recaptcha_script_footer(){
    $field = wppb_get_recaptcha_field();
    /* if we do not have a recaptcha field don't do nothing */
    if( empty( $field ) )
        return;

    //do not add script if there is no shortcode
    global $wppb_shortcode_on_front;
    if( current_filter() == 'wp_footer' && ( !isset( $wppb_shortcode_on_front ) || $wppb_shortcode_on_front === false ) )
        return;

    //do not add script if the html for the field has not been added
    global $wppb_recaptcha_present;
    if( !isset( $wppb_recaptcha_present ) || $wppb_recaptcha_present === false )
        return;

    //we don't have jquery on the backend
    if( current_filter() != 'wp_footer' ) {
        wp_print_scripts('jquery');
    }else if(!wp_script_is('jquery')){
        wp_print_scripts('jquery');
    }

    //get site key
    $pubkey = '';
    if( isset( $field['public-key'] ) ) {
        $pubkey = sanitize_text_field( $field['public-key'] );
    }

    // Check if we have a reCAPTCHA type
    if ( !isset($field['recaptcha-type']) )
        $field['recaptcha-type'] = 'v2' ;

    /*for invisible recaptcha we have extra parameters and the selector is different. v2 is initialized on the id of the div
    that must be unique and invisible is on the submit button of the forms that have the div */
    if( $field['recaptcha-type'] === 'invisible' ) {
        $callback_conditions = 'jQuery("input[type=\'submit\']", jQuery( ".wppb-recaptcha-element" ).closest("form") )';
        $invisible_parameters = '"callback" : wppbInvisibleRecaptchaOnSubmit,"size": "invisible"';
    }else {
        $callback_conditions = 'jQuery(".wppb-recaptcha-element")';
        $invisible_parameters = '';
    }
    //the section below is properly escaped or the variables contain static strings
    // phpcs:disable
    echo '
    <script>
        var wppbRecaptchaCallback = function() {
            if( typeof window.wppbRecaptchaCallbackExecuted == "undefined" ){//see if we executed this before
                '.$callback_conditions.'.each(function(){
                    recID = grecaptcha.render( jQuery(this).attr("id"), {
                        "sitekey" : "' . $pubkey . '",
                        "error-callback": wppbRecaptchaInitializationError,
                        '.$invisible_parameters.'
                     });
                });
                window.wppbRecaptchaCallbackExecuted = true;//we use this to make sure we only run the callback once
            }
        };

        /* the callback function for when the captcha does not load propperly, maybe network problem or wrong keys  */
        function wppbRecaptchaInitializationError(){
            window.wppbRecaptchaInitError = true;
        ';

    if( $field['recaptcha-type'] === 'invisible' ) {
        echo '
            /* make sure that if the invisible recaptcha did not load properly ( network error or wrong keys ) we can still submit the form */
            jQuery("input[type=\'submit\']", jQuery( ".wppb-recaptcha-element" ).closest("form") ).on("click", function(e){
                        jQuery(this).closest("form").submit();
                });
            ';
    }

    echo '
            //add a captcha field so we do not just let the form submit if we do not have a captcha response
            jQuery( ".wppb-recaptcha-element" ).after(\''. wp_nonce_field( 'wppb_recaptcha_init_error', 'wppb_recaptcha_load_error', false, false ) .'\');
        }

        /* compatibility with other plugins that may include recaptcha with an onload callback. if their script loads first then our callback will not execute so call it explicitly  */
        jQuery( window ).on( "load", function() {
            wppbRecaptchaCallback();
        });
    </script>';
    // phpcs:enable
    if( $field['recaptcha-type'] === 'invisible' ) {
        echo '<script>
            /* success callback for invisible recaptcha. it submits the form that contains the right token response */
            function wppbInvisibleRecaptchaOnSubmit(token){

                var elem = jQuery(".g-recaptcha-response").filter(function(){
                    return jQuery(this).val() === token;
                });

                var submitForm = true

                /* dont submit form if PMS gateway is Stripe */
                if( jQuery(".pms_pay_gate[type=radio]").length > 0 ){
                    jQuery(".pms_pay_gate").each( function(){
                        if( jQuery(this).is(":checked") && ( jQuery(this).val() == "stripe_intents" || jQuery(this).val() == "stripe" ) )
                            submitForm = false
                    })
                } else if( jQuery(".pms_pay_gate[type=hidden]").length > 0 ) {

                    if( jQuery(".pms_pay_gate[type=hidden]").val() == "stripe_intents" || jQuery(".pms_pay_gate[type=hidden]").val() == "stripe" )
                        submitForm = false

                }

                if( submitForm ){
                    var form = elem.closest("form");
                    form.submit();
                } else {
                    jQuery(document).trigger( "wppb_invisible_recaptcha_success", jQuery( ".form-submit input[type=\'submit\']", elem.closest("form") ) )
                }
            }
        </script>';
    }

	$lang = '&hl=en';
    $locale = get_locale();
    if(!empty($locale)) {
        $locale_parts = explode('_',$locale);
	    $lang = '&hl='.urlencode($locale_parts[0]);
    }

    $source = apply_filters( 'wppb_recaptcha_custom_field_source', 'www.google.com' );

    echo '<script src="https://'. esc_attr( $source ) .'/recaptcha/api.js?onload=wppbRecaptchaCallback&render=explicit'.esc_attr( $lang ).'" async defer></script>';
}
add_action('wp_footer', 'wppb_recaptcha_script_footer', 9999);
add_action('login_footer', 'wppb_recaptcha_script_footer');
add_action('register_form', 'wppb_recaptcha_script_footer');
add_action('lost_password', 'wppb_recaptcha_script_footer');


/**
 * A wppb_ReCaptchaResponse is returned from wppb_recaptcha_check_answer()
 */
class wppb_ReCaptchaResponse {
    var $is_valid;
}


/**
 * Calls an HTTP POST function to verify if the user's answer was correct
 * @param string $privkey
 * @param string $remoteip
 * @param string $response
 * @return wppb_ReCaptchaResponse
 */
function wppb_recaptcha_check_answer ( $privkey, $remoteip, $response ){

    if ( $remoteip == null || $remoteip == '' )
        echo '<span class="error">'. esc_html__("For security reasons, you must pass the remote ip to reCAPTCHA!", "profile-builder") .'</span><br/><br/>';

    // Discard empty solution submissions
    if ($response == null || strlen($response) == 0) {
        $recaptchaResponse = new wppb_ReCaptchaResponse();

        if( isset( $_POST['wppb_recaptcha_load_error'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_recaptcha_load_error'] ), 'wppb_recaptcha_init_error' ) )
            $recaptchaResponse->is_valid = true;
        else
            $recaptchaResponse->is_valid = false;

        return $recaptchaResponse;
    }

    $source = apply_filters( 'wppb_recaptcha_custom_field_source', 'www.google.com' );

    $getResponse = _wppb_submitHTTPGet(
        "https://".$source."/recaptcha/api/siteverify?",
        array (
            'secret' => $privkey,
            'remoteip' => $remoteip,
            'response' => $response
        )
    );

    $answers = json_decode($getResponse, true);
    $recaptchaResponse = new wppb_ReCaptchaResponse();
    if (trim($answers ['success']) == true) {
        $recaptchaResponse->is_valid = true;
    } else {
        $recaptchaResponse->is_valid = false;
    }
    return $recaptchaResponse;

}

/* the function to display error message on the registration page */
function wppb_validate_captcha_response( $publickey, $privatekey ){
    if (isset($_POST['g-recaptcha-response'])){
        $recaptcha_response_field = sanitize_textarea_field( $_POST['g-recaptcha-response'] );
    }
    else {
        $recaptcha_response_field = '';
    }
    if( isset( $_SERVER["REMOTE_ADDR"] ) )
        $resp = wppb_recaptcha_check_answer($privatekey, sanitize_text_field( $_SERVER["REMOTE_ADDR"] ), $recaptcha_response_field );

    if ( !empty( $_POST ) && isset( $resp ) )
        return ( ( !$resp->is_valid ) ? false : true );
}

/* the function to add reCAPTCHA to the registration form of PB */
function wppb_recaptcha_handler ( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'reCAPTCHA' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_recaptcha_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        wppb_recaptcha_set_default_values();
        if ( ($form_location == 'register') && ( isset($field['captcha-pb-forms']) ) && (strpos($field['captcha-pb-forms'],'pb_register') !== false) ) {
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            global $wppb_recaptcha_present;
            $wppb_recaptcha_present = true;

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $publickey = trim( $field['public-key'] );
            $privatekey = trim( $field['private-key'] );

            if ( empty( $publickey ) || empty( $privatekey ) )
                return '<span class="custom_field_recaptcha_error_message" id="'.$field['meta-name'].'_error_message">'.apply_filters( 'wppb_'.$form_location.'_recaptcha_custom_field_'.$field['id'].'_error_message', __("To use reCAPTCHA you must get an API public key from:", "profile-builder"). '<a href="https://www.google.com/recaptcha/admin/create">https://www.google.com/recaptcha/admin/create</a>' ).'</span>';

            if ( empty($field['recaptcha-type']) || ($field['recaptcha-type'] == 'v2') ) {
                $output = '<label for="recaptcha_response_field">' . $item_title . $error_mark . '</label>' . wppb_recaptcha_get_html($publickey, 'pb_register');
                if (!empty($item_description))
                    $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';
            }
            else {
                // html for Invisible reCAPTCHA
                $output = wppb_recaptcha_get_html($publickey, 'pb_register');
            }


            return $output;

        }
    }
}
add_filter( 'wppb_output_form_field_recaptcha', 'wppb_recaptcha_handler', 10, 6 );


/* handle reCAPTCHA field validation on PB Register form */
function wppb_check_recaptcha_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'reCAPTCHA' ){
        if ( ( $form_location == 'register' ) && ( isset($field['captcha-pb-forms']) ) && (strpos($field['captcha-pb-forms'],'pb_register') !== false) ) {

            /* theme my login plugin executes the register_errors hook on the frontend on all pages so on our register forms we might have already a recaptcha response
            so do not verify it again or it will fail  */
            global $wppb_recaptcha_response;
            if (!isset($wppb_recaptcha_response)){
                $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );
            }
            if ( (  $wppb_recaptcha_response == false ) && ( $field['required'] == 'Yes' ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }
    return $message;
}
add_filter( 'wppb_check_form_field_recaptcha', 'wppb_check_recaptcha_value', 10, 4 );

// Get the reCAPTCHA field information
function wppb_get_recaptcha_field(){
    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
    $field = '';
    if ( $wppb_manage_fields != 'not_found' ) {
        foreach ($wppb_manage_fields as $value) {
            if ($value['field'] == 'reCAPTCHA')
                $field = $value;
        }
    }
    return $field;
}

/* Display reCAPTCHA on PB Recover Password form */
function wppb_display_recaptcha_recover_password( $output ){
    $field = wppb_get_recaptcha_field();

    if ( !empty($field) ) {
        $publickey = trim($field['public-key']);
        $item_title = apply_filters('wppb_recover_password_recaptcha_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
        $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);

        // check where reCAPTCHA should display and add reCAPTCHA html
        if ( isset($field['captcha-pb-forms']) && ( strpos( $field['captcha-pb-forms'],'pb_recover_password' ) !== false ) ) {

            global $wppb_recaptcha_present;
            $wppb_recaptcha_present = true;

            if ( empty($field['recaptcha-type']) || ($field['recaptcha-type'] == 'v2') ) {
                $recaptcha_output = '<label for="recaptcha_response_field">' . $item_title . '</label>' . wppb_recaptcha_get_html($publickey, 'pb_recover_password');
                if (!empty($item_description))
                    $recaptcha_output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                $output = str_replace('</ul>', '<li class="wppb-form-field wppb-recaptcha">' . $recaptcha_output . '</li>' . '</ul>', $output);
            }
            else {
                // output Invisible reCAPTCHA html
                $output = str_replace('</ul>', '<li class="wppb-form-field wppb-recaptcha">' . wppb_recaptcha_get_html($publickey, 'pb_recover_password') . '</li>' . '</ul>', $output);
            }
        }
    }
    return $output;
}
add_filter('wppb_recover_password_generate_password_input','wppb_display_recaptcha_recover_password');

/*  Function that changes the messageNo from the Recover Password form  */
function wppb_recaptcha_change_recover_password_message_no($messageNo) {

        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'recover_password') {
            $field = wppb_get_recaptcha_field();
            if (!empty($field)) {

                global $wppb_recaptcha_response;
                if (!isset($wppb_recaptcha_response)) $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );

                if ( isset($field['captcha-pb-forms']) && (strpos($field['captcha-pb-forms'], 'pb_recover_password') !== false) ) {

                    if ( ($wppb_recaptcha_response == false ) && ( $field['required'] == 'Yes' ) )
                        $messageNo = '';
                }
            }
        }

        return $messageNo;
}
add_filter('wppb_recover_password_message_no', 'wppb_recaptcha_change_recover_password_message_no');

/*  Function that adds the reCAPTCHA error message on the Recover Password form  */
function wppb_recaptcha_recover_password_displayed_message1( $message ) {
    $field = wppb_get_recaptcha_field();

    if ( !empty($field) ){
        global $wppb_recaptcha_response;
        if (!isset($wppb_recaptcha_response)) $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );

        if ( isset($field['captcha-pb-forms']) && ( strpos( $field['captcha-pb-forms'],'pb_recover_password' ) !== false ) && ( $wppb_recaptcha_response == false )) {

            // This message is also altered by the plugin-compatibilities.php file, in regards to Captcha plugin ( function wppb_captcha_recover_password_displayed_message1 )
            if (($message == '<p class="wppb-warning">wppb_recaptcha_error</p>') || ($message == '<p class="wppb-warning">wppb_captcha_error</p>'))
                $message = '<p class="wppb-warning">' . wppb_recaptcha_field_error($field["field-title"]) . '</p>';
            else
                $message = $message . '<p class="wppb-warning">' . wppb_recaptcha_field_error($field["field-title"]) . '</p>';

            }
        }

    return $message;
}
add_filter('wppb_recover_password_displayed_message1', 'wppb_recaptcha_recover_password_displayed_message1');

/*  Function that changes the default success message to wppb_recaptcha_error if the reCAPTCHA doesn't validate
    so that we can change the message displayed with the wppb_recover_password_displayed_message1 filter  */
function wppb_recaptcha_recover_password_sent_message_1($message) {

        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'recover_password') {
            $field = wppb_get_recaptcha_field();

            if (!empty($field)) {
                global $wppb_recaptcha_response;
                if (!isset($wppb_recaptcha_response)) $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );

                if ( isset($field['captcha-pb-forms']) && ( strpos($field['captcha-pb-forms'], 'pb_recover_password') !== false ) && ( $wppb_recaptcha_response == false ) ){
                    $message = 'wppb_recaptcha_error';
                }
            }

        }

        return $message;
}
add_filter('wppb_recover_password_sent_message1', 'wppb_recaptcha_recover_password_sent_message_1');

/* Display reCAPTCHA html on PB Login form */
function wppb_display_recaptcha_login_form($form_part, $args) {

    if( !isset( $args['form_id'] ) || $args['form_id'] != 'wppb-loginform' )
        return $form_part;

    $field = wppb_get_recaptcha_field();

    if ( !empty($field) ) {
        $item_title = apply_filters('wppb_login_recaptcha_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
        $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);

        if ( isset($field['captcha-pb-forms']) && ( strpos( $field['captcha-pb-forms'],'pb_login' ) !== false ) ) { // check where reCAPTCHA should display and add reCAPTCHA html

            global $wppb_recaptcha_present;
            $wppb_recaptcha_present = true;

            if ( empty($field['recaptcha-type']) || ($field['recaptcha-type'] == 'v2') ) {
                $recaptcha_output = '<label for="recaptcha_response_field">' . $item_title . '</label>' . wppb_recaptcha_get_html(trim($field['public-key']), 'pb_login');
                if (!empty($item_description))
                    $recaptcha_output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                $form_part .= '<div class="wppb-form-field wppb-recaptcha">' . $recaptcha_output . '</div>';
            }
            else {
                //output Invisible reCAPTCHA html
                $form_part .= wppb_recaptcha_get_html(trim($field['public-key']), 'pb_login');
            }
        }
    }

    return $form_part;
}
add_filter('login_form_middle', 'wppb_display_recaptcha_login_form', 10, 2);

/* Display reCAPTCHA html on default WP Login form */
function wppb_display_recaptcha_wp_login_form(){
    $field = wppb_get_recaptcha_field();

    if ( !empty($field) ) {
        $item_title = apply_filters('wppb_login_recaptcha_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
        $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);

        if ( isset($field['captcha-wp-forms']) && (strpos( $field['captcha-wp-forms'],'default_wp_login' ) !== false) ) { // check where reCAPTCHA should display and add reCAPTCHA html

            global $wppb_recaptcha_present;
            $wppb_recaptcha_present = true;

            if ( empty($field['recaptcha-type']) || ($field['recaptcha-type'] == 'v2') ) {
                $recaptcha_output = '<label for="recaptcha_response_field" style="padding-left:15px; padding-bottom:7px;">' . $item_title . '</label>' . wppb_recaptcha_get_html(trim($field['public-key']));
                if (!empty($item_description))
                    $recaptcha_output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                echo '<div class="wppb-form-field wppb-recaptcha" style="margin-left:-14px; margin-bottom: 15px;">' . $recaptcha_output . '</div>'; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* properly escaped when constructing the var */
            }
            else {
                // output Invisible reCAPTCHA html
                echo wppb_recaptcha_get_html( trim($field['public-key'])); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* properly escaped when constructing the var */
            }
        }
    }
}
add_action( 'login_form', 'wppb_display_recaptcha_wp_login_form' );

//Show reCAPTCHA error on Login form (both default and PB one)
function wppb_recaptcha_login_wp_error_message($user){
    //make sure you're on a Login form (WP or PB)
    if ( isset( $_POST['wp-submit'] ) && !is_wp_error($user) && !isset( $_POST['pms_login'] ) ) {

        $field = wppb_get_recaptcha_field();
        if ( !empty($field) ){
            global $wppb_recaptcha_response;

            if (!isset($wppb_recaptcha_response)) $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );

            //reCAPTCHA error for displaying on the PB login form
            if ( isset($_POST['wppb_login']) && ($_POST['wppb_login'] == true) ) {

                // it's a PB login form, check if we have a reCAPTCHA on it and display error if not valid
                if ((isset($field['captcha-pb-forms'])) && (strpos($field['captcha-pb-forms'], 'pb_login') !== false) && ($wppb_recaptcha_response == false)) {
                    $user = new WP_Error('wppb_recaptcha_error', __('Please enter a (valid) reCAPTCHA value', 'profile-builder'));
                    remove_filter( 'authenticate', 'wp_authenticate_username_password',  20, 3 );
                    remove_filter( 'authenticate', 'wp_authenticate_email_password',     20, 3 );
                }

            }
            else {
                //reCAPTCHA error for displaying on the default WP login form
                if (isset($field['captcha-wp-forms']) && (strpos($field['captcha-wp-forms'], 'default_wp_login') !== false) && ($wppb_recaptcha_response == false)) {
                    $user = new WP_Error('wppb_recaptcha_error', __('Please enter a (valid) reCAPTCHA value', 'profile-builder'));
                    remove_filter( 'authenticate', 'wp_authenticate_username_password',  20, 3 );
                    remove_filter( 'authenticate', 'wp_authenticate_email_password',     20, 3 );
                }

            }
        }
    }
    return $user;
}
add_filter('authenticate','wppb_recaptcha_login_wp_error_message', 9);

// Display reCAPTCHA html on default WP Recover Password form
function wppb_display_recaptcha_default_wp_recover_password() {
    $field = wppb_get_recaptcha_field();

    if (!empty($field)) {
        $publickey = trim($field['public-key']);
        $item_title = apply_filters('wppb_recover_password_recaptcha_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
        $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);

        if ( isset($field['captcha-wp-forms']) && (strpos( $field['captcha-wp-forms'], 'default_wp_recover_password') !== false) ) { // check where reCAPTCHA should display and add reCAPTCHA html

            global $wppb_recaptcha_present;
            $wppb_recaptcha_present = true;

            if ( empty($field['recaptcha-type']) || ($field['recaptcha-type'] == 'v2') ){
                $recaptcha_output = '<label for="recaptcha_response_field" style="padding-left:15px; padding-bottom:7px;">' . $item_title . '</label>' . wppb_recaptcha_get_html($publickey);
                if (!empty($item_description))
                    $recaptcha_output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                echo '<div class="wppb-form-field wppb-recaptcha" style="margin-left:-14px; margin-bottom: 15px;">' . $recaptcha_output . '</div>'; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* properly escaped when constructing the var */
            }
            else {
                // output Invisible reCAPTCHA html
                echo wppb_recaptcha_get_html($publickey); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* properly escaped when constructing the var */
            }
        }
    }
}
add_action('lostpassword_form','wppb_display_recaptcha_default_wp_recover_password');

// Verify and show reCAPTCHA errors for default WP Recover Password
function wppb_verify_recaptcha_default_wp_recover_password(){

    // If field 'username or email' is empty - return
    if( isset( $_REQUEST['user_login'] ) && "" === $_REQUEST['user_login'] )
        return;

    $field = wppb_get_recaptcha_field();
    if ( !empty($field) ){
        global $wppb_recaptcha_response;
        if (!isset($wppb_recaptcha_response)) $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );

    // If reCAPTCHA not entered or incorrect reCAPTCHA answer
        if ( isset( $_REQUEST['g-recaptcha-response'] ) && ( ( "" ===  $_REQUEST['g-recaptcha-response'] )  || ( $wppb_recaptcha_response == false ) ) ) {
            wp_die( esc_html__('Please enter a (valid) reCAPTCHA value','profile-builder') . '<br />' . esc_html__( "Click the BACK button on your browser, and try again.", 'profile-builder' ) ) ;
        }
    }
}
add_action('lostpassword_post','wppb_verify_recaptcha_default_wp_recover_password');

/* Display reCAPTCHA html on default WP Register form */
function wppb_display_recaptcha_default_wp_register(){
    $field = wppb_get_recaptcha_field();

    if (!empty($field)) {

            $publickey = trim($field['public-key']);
            $item_title = apply_filters('wppb_register_recaptcha_custom_field_' . $field['id'] . '_item_title', wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_title_translation', $field['field-title'], true));
            $item_description = wppb_icl_t('plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_description_translation', $field['description'], true);

            wppb_recaptcha_set_default_values();
            if (isset($field['captcha-wp-forms']) && (strpos($field['captcha-wp-forms'], 'default_wp_register') !== false)) { // check where reCAPTCHA should display and add reCAPTCHA html

                global $wppb_recaptcha_present;
                $wppb_recaptcha_present = true;

                if ( empty($field['recaptcha-type']) || ($field['recaptcha-type'] == 'v2') ) {
                    $recaptcha_output = '<label for="recaptcha_response_field" style="padding-left:15px; padding-bottom:7px;">' . $item_title . '</label>' . wppb_recaptcha_get_html($publickey);
                    if (!empty($item_description))
                        $recaptcha_output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                    echo '<div class="wppb-form-field wppb-recaptcha" style="margin-left:-14px; margin-bottom: 15px;">' . $recaptcha_output . '</div>'; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* properly escaped when constructing the var */
                }
                else {
                    // output reCAPTCHA html
                    echo wppb_recaptcha_get_html($publickey); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* properly escaped when constructing the var */
                }
            }
    }
}
add_action( 'register_form', 'wppb_display_recaptcha_default_wp_register' );

// Verify and show reCAPTCHA errors for default WP Register form
function wppb_verify_recaptcha_default_wp_register( $errors ){

    $field = wppb_get_recaptcha_field();
    if ( !empty($field) ){
        global $wppb_recaptcha_response;
        if (!isset($wppb_recaptcha_response)) $wppb_recaptcha_response = wppb_validate_captcha_response( trim( $field['public-key'] ), trim( $field['private-key'] ) );

        // If reCAPTCHA not entered or incorrect reCAPTCHA answer
        if ( isset( $_REQUEST['g-recaptcha-response'] ) && ( ( "" ===  $_REQUEST['g-recaptcha-response'] )  || ( $wppb_recaptcha_response == false ) ) ) {
            $errors->add( 'wppb_recaptcha_error', __('Please enter a (valid) reCAPTCHA value','profile-builder') );
        }
    }

return $errors;
}
add_filter('registration_errors','wppb_verify_recaptcha_default_wp_register');

// set default values in case there's already an existing reCAPTCHA field in Manage fields (when upgrading)
function wppb_recaptcha_set_default_values() {
    $manage_fields = get_option('wppb_manage_fields', 'not_set');
    if ($manage_fields != 'not_set') {
        foreach ($manage_fields as $key => $value) {
            if ($value['field'] == 'reCAPTCHA') {
                if ( !isset($value['captcha-pb-forms']) ) $manage_fields[$key]['captcha-pb-forms'] = 'pb_register';
                if ( !isset($value['captcha-wp-forms']) ) $manage_fields[$key]['captcha-wp-forms'] = 'default_wp_register';
                if ( !isset($value['recaptcha-type']) )   $manage_fields[$key]['recaptcha-type'] = 'v2';
            }
        }
        update_option('wppb_manage_fields', $manage_fields);
    }
}
