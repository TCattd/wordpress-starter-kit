<?php
add_filter( 'wppb_sc_process_linkedin_response', 'wppb_in_sc_linkedin_response' );
function wppb_in_sc_linkedin_response( $platform_response ) {
    $platform_response['first_name'] = sanitize_text_field( $platform_response['firstName'] );
    $platform_response['last_name'] = sanitize_text_field( $platform_response['lastName'] );
    $platform_response['email'] = $platform_response['emailAddress'];

    return $platform_response;
}

/* Generate the LinkedIn button */
function wppb_in_sc_generate_linkedin_button( $form_ID ) {
    global $social_connect_instance;

    $class = 'wppb-sc-linkedin-login wppb-sc-button';
    global $pagenow;
    if( $pagenow == 'wp-login.php' ) {
        $class .= '-wp-default';
    }

    if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
        $class .= '-text';
    }

    $button = '';
    if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
        $button = '<div class="wppb-sc-buttons-text-div">';
    }
    $check_if_linked = get_user_meta( get_current_user_id(), '_wppb_linkedin_connect_id' );
    if( isset( $social_connect_instance->forms_type ) && $social_connect_instance->forms_type == 'edit_profile' && ! empty( $check_if_linked ) ) {
        $class .= ' wppb-sc-disabled-btn';
    }
    $button .= '<a class="' . $class . '" href="#" data-wppb_sc_form_id_linkedin="' . $form_ID . '">';
    $button .= '<i class="wppb-sc-icon-linkedin wppb-sc-icon"></i>';
    if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
        if( isset( $social_connect_instance->forms_type ) && $social_connect_instance->forms_type == 'edit_profile' ) {
            if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['linkedin-button-text-ep'] ) ) {
                $button .= wppb_icl_t( 'plugin profile-builder-pro', 'social_connect_linkedin_button_text_ep_translation', esc_attr( $social_connect_instance->wppb_social_connect_settings[0]['linkedin-button-text-ep'] ));
            } else {
                $button .= __( 'Link with LinkedIn', 'profile-builder' );
            }
        } else {
            if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['linkedin-button-text'] ) ) {
                $button .= wppb_icl_t( 'plugin profile-builder-pro', 'social_connect_linkedin_button_text_translation', esc_attr( $social_connect_instance->wppb_social_connect_settings[0]['linkedin-button-text'] ));
            } else {
                $button .= __( 'Sign in with LinkedIn', 'profile-builder' );
            }
        }
    }

    $button .= '</a>';
    if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
        $button .= '</div>';
    }

    return $button;
}

//initiate the Oauth actions in the popup window
add_action( 'wp_head', 'wppb_in_sc_listen_for_linkeind_login' );
function wppb_in_sc_listen_for_linkeind_login(){
    global $social_connect_instance;

    if( isset( $_GET['wppb_sc_linkedin_login'] ) && $_GET['wppb_sc_linkedin_login'] === 'true' ){
        if( isset( $_GET['state'] ) && $_GET['state'] === 'J57asfJJJ21231PPnq4' ){//we hardcoded this string in the js request
            if( isset( $_GET['code'] ) && !empty( $_GET['code'] ) ){



                //make a post request here with the code

                $url = 'https://www.linkedin.com/oauth/v2/accessToken';
                $data_access_token = array(
                    'grant_type' => 'authorization_code',
                    'code' => trim( sanitize_text_field($_GET['code'] ) ),
                    'redirect_uri' => esc_url(home_url('/?wppb_sc_linkedin_login=true')),
                    'client_id' => $social_connect_instance->wppb_social_connect_settings[0]['linkedin-client-id'],
                    'client_secret' => $social_connect_instance->wppb_social_connect_settings[0]['linkedin-client-secret']
                );
                $response = wp_remote_post($url, array(
                        'method' => 'POST',
                        'timeout' => 15,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'sslverify' => false,
                        'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
                        'body' => http_build_query($data_access_token)
                    )
                );

                if(!is_wp_error($response) && isset($response['response']['code']) && 200 === $response['response']['code']){
                    $body = json_decode(wp_remote_retrieve_body($response));
                    if(is_object($body) && isset($body->access_token)){

                        //get the email
                        $email = wp_remote_get('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))', array(
                                'method' => 'GET',
                                'timeout' => 15,
                                'headers' => array('Authorization' => "Bearer ".$body->access_token),
                            )
                        );
                        //get the other profile info
                        $profile_info = wp_remote_get('https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))', array(
                                'method' => 'GET',
                                'timeout' => 15,
                                'headers' => array('Authorization' => "Bearer ".$body->access_token),
                            )
                        );

                        if(!is_wp_error($profile_info) && isset($profile_info['response']['code']) && 200 === $profile_info['response']['code'] && !is_wp_error($email) && isset($email['response']['code']) && 200 === $email['response']['code']){
                            $profile_info_body = json_decode(wp_remote_retrieve_body($profile_info));
                            $email_body = json_decode(wp_remote_retrieve_body($email));
                            if(is_object($profile_info_body) && isset($profile_info_body->id) && $profile_info_body->id && is_object($email_body) && isset($email_body->elements)){

                                $profile_info_body = json_decode(json_encode($profile_info_body), true);
                                $email_body = json_decode(json_encode($email_body), true);
                                $id = isset($profile_info_body['id'])  ? $profile_info_body['id'] : null;
                                $first_name = isset($profile_info_body['firstName']) && isset($profile_info_body['firstName']['localized']) && isset($profile_info_body['firstName']['preferredLocale']) && isset($profile_info_body['firstName']['preferredLocale']['language']) && isset($profile_info_body['firstName']['preferredLocale']['country']) ? $profile_info_body['firstName']['localized'][$profile_info_body['firstName']['preferredLocale']['language'] . '_' . $profile_info_body['firstName']['preferredLocale']['country']] : '';
                                $last_name = isset($profile_info_body['lastName']) && isset($profile_info_body['lastName']['localized']) && isset($profile_info_body['lastName']['preferredLocale']) && isset($profile_info_body['lastName']['preferredLocale']['language']) && isset($profile_info_body['lastName']['preferredLocale']['country']) ? $profile_info_body['lastName']['localized'][$profile_info_body['lastName']['preferredLocale']['language'] . '_' . $profile_info_body['lastName']['preferredLocale']['country']] : '';
                                $email_address = isset($email_body['elements']) && is_array($email_body['elements']) && isset($email_body['elements'][0]['handle~']) && isset($email_body['elements'][0]['handle~']['emailAddress']) ? $email_body['elements'][0]['handle~']['emailAddress'] : '';

                                //build the platform data that we send in js to our main login function
                                $platform_data = array(
                                    'id' => $id,
                                    'firstName' => $first_name,
                                    'lastName' => $last_name,
                                    'emailAddress' => $email_address,
                                );


                                ?>
                                <style type="text/css">
                                    body{display:none;}
                                </style>
                                <script>
                                    var data = {
                                        'platform'                  : 'linkedin',
                                        'action'                    : 'wppb_sc_handle_login_click',
                                        'platform_response'         : <?php echo json_encode($platform_data); ?>,
                                        'wppb_sc_security_token'    : '<?php echo esc_js( $social_connect_instance->wppb_social_connect_settings[0]['linkedin-client-id'] ); ?>',//the security token that is set from js is the client id
                                        'wppb_sc_form_ID'           : localStorage.getItem( 'wppb_sc_form_ID_linkedin' )
                                    };

                                    //call the main login function in the parent window here
                                    if(window.opener){
                                        window.opener.wppbSCLogin( data, wppb_sc_linkedin_data, 'linkedin' );
                                        window.close();
                                    }else{
                                        window.wppbSCLogin( data, wppb_sc_linkedin_data, 'linkedin' );
                                    }
                                </script>
                                <?php
                            }
                        }

                    }
                }

            }
        }
    }
}