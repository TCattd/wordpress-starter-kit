<?php

/* include the twitteroauth library */
require_once( "lib/twitteroauth.php" );

/* get settings for social connect */
$wppb_social_connect_settings = get_option( 'wppb_social_connect_settings' );

if( ! empty( $wppb_social_connect_settings[0]['twitter-api-key'] ) && ! empty( $wppb_social_connect_settings[0]['twitter-api-secret'] ) ) {

    define( "WPPB_IN_SC_TWITTER_CONSUMER_KEY", trim( $wppb_social_connect_settings[0]['twitter-api-key'] ) );
    define( "WPPB_IN_SC_TWITTER_CONSUMER_SECRET", trim( $wppb_social_connect_settings[0]['twitter-api-secret'] ) );
    define( "WPPB_IN_SC_TWITTER_OAUTH_CALLBACK", admin_url('admin-ajax.php?action=wppb_handle_oauth_callback' ) );

    /* we need first_name and last_name indices */
    add_filter( 'wppb_sc_process_twitter_response', 'wppb_in_sc_process_twitter_response' );
    function wppb_in_sc_process_twitter_response( $platform_response ) {
        if( ! empty( $platform_response['name'] ) ) {
            $name = urldecode( $platform_response['name'] );

            /* take a guess that first name is the name until space */
            $name = explode( ' ', $name );
            $platform_response['first_name'] = sanitize_text_field( $name[0] );

			$platform_response['last_name'] = '';
			$i = 1;
			while( ! empty( $name[$i] ) ) {
				$platform_response['last_name'] .= sanitize_text_field( $name[$i] ) . ' ';
				$i++;
			}
        }

        return $platform_response;
    }

    /* first ajax action called when clicking the twitter login button. we get the oauth token here */
    add_action( 'wp_ajax_wppb_sc_twitter_oauth_response', 'wppb_in_sc_twitter_oauth_response' );
    add_action( 'wp_ajax_nopriv_wppb_sc_twitter_oauth_response', 'wppb_in_sc_twitter_oauth_response' );
    function wppb_in_sc_twitter_oauth_response() {
        $connection = new WPPB_IN_TwitterOAuth( WPPB_IN_SC_TWITTER_CONSUMER_KEY, WPPB_IN_SC_TWITTER_CONSUMER_SECRET );
        $request_token = $connection->getRequestToken( WPPB_IN_SC_TWITTER_OAUTH_CALLBACK ); //get Request Token
        if( $request_token ) {
            $wppb_sc_security_token = array( 'request_token' => $request_token['oauth_token'], 'oauth_token_secret' => $request_token['oauth_token_secret'] );

            switch( $connection->http_code ) {
                case 200:
                    $url = $connection->getAuthorizeURL( $request_token['oauth_token'] );
                    //if successful redirect to Twitter .
                    wp_die( json_encode( array( 'redirect_to' => esc_url_raw( $url ), 'wppb_sc_security_token' => $wppb_sc_security_token ) ) );
                    break;
                default:
                    var_dump( $request_token );
                    wp_die( esc_html__( "Connection with twitter Failed", 'profile-builder' ) );
                    break;
            }

        } else { //error receiving request token
            wp_die( esc_html__( "Error Receiving Request Token", 'profile-builder' ) );
        }
        wp_die();
    }


    /* callback for twitter oauth. this is what is called after logging in the twitter popup (and approving the twitter app) */
    add_action( 'wp_ajax_wppb_handle_oauth_callback', 'wppb_in_handle_oauth_callback' );
    add_action( 'wp_ajax_nopriv_wppb_handle_oauth_callback', 'wppb_in_handle_oauth_callback' );
    function wppb_in_handle_oauth_callback() {
        if( isset( $_GET['oauth_token'] ) ) {
            $wppb_sc_security_token = json_decode( wp_unslash( $_COOKIE['wppb_sc_security_token'] ) ); //phpcs:ignore

            $connection = new WPPB_IN_TwitterOAuth( WPPB_IN_SC_TWITTER_CONSUMER_KEY, WPPB_IN_SC_TWITTER_CONSUMER_SECRET, $wppb_sc_security_token->request_token, $wppb_sc_security_token->oauth_token_secret );

			$access_token = $connection->getAccessToken( $_REQUEST['oauth_verifier'] ); //phpcs:ignore

			if( $access_token ) {
                $connection = new WPPB_IN_TwitterOAuth( WPPB_IN_SC_TWITTER_CONSUMER_KEY, WPPB_IN_SC_TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret'] );
				$params     = array( 'include_entities' => 'false', 'include_email' => 'true', 'skip_status' => 'true' );

				$content = $connection->get( 'account/verify_credentials', $params );

                if( $content && isset( $content->screen_name ) && isset( $content->name ) ) {
                    $user_query = new WP_User_Query( array( 'meta_key' => '_wppb_twitter_connect_id', 'meta_value' => intval( $content->id ) ) );

					if( ! empty( $user_query->results[0]->data->ID ) ) {
                        $cookie_content = array( "id" => intval( $content->id ) );
                    } else {
                        $cookie_content = array( "id" => intval( $content->id ), "name" => sanitize_text_field( $content->name ) );
                    }

                    if( isset( $content->email ) )
                        $cookie_content['email'] = $content->email;

                    setcookie( 'wppb_sc_twitter_data', json_encode( $cookie_content ), time() + (60), "/" );

                    ?>
                    <script>
                        if( window.opener ) {
                            window.opener.wppbTwitterLogin();
                            window.close();
                        }
                    </script>
                <?php


                } else {
                    echo "<h4> Login Error </h4>";
                }
            } else {
                echo "<h4> Login Error </h4>";
            }
        } else { //Error. redirect to Login Page.
            echo "<h4>" . esc_html__( 'Something went wrong. Please try again later!', 'profile-builder' ) . "</h4>";
        }
    }
}

function wppb_in_sc_generate_twitter_button( $form_ID ) {
    global $social_connect_instance;

    $class = 'wppb-sc-twitter-login wppb-sc-button';
    global $pagenow;
    if( $pagenow == 'wp-login.php' ) {
		$class .= '-wp-default';
	}

    if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) &&  $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
		$class .= '-text';
	}

	$button = '';
	if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
		$button = '<div class="wppb-sc-buttons-text-div">';
	}
	$check_if_linked = get_user_meta( get_current_user_id(), '_wppb_twitter_connect_id' );
	if( isset( $social_connect_instance->forms_type ) && $social_connect_instance->forms_type == 'edit_profile' && ! empty( $check_if_linked ) ) {
		$class .= ' wppb-sc-disabled-btn';
	}
	$button .= '<a class="' . $class . '" href="#" data-wppb_sc_form_id_twitter="' . $form_ID . '">';
    $button .= '<i class="wppb-sc-icon-twitter wppb-sc-icon"></i>';
    if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
		if( isset( $social_connect_instance->forms_type ) && $social_connect_instance->forms_type == 'edit_profile' ) {
			if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['twitter-button-text-ep'] ) ) {
				$button .= wppb_icl_t( 'plugin profile-builder-pro', 'social_connect_twitter_button_text_ep_translation', esc_attr( $social_connect_instance->wppb_social_connect_settings[0]['twitter-button-text-ep'] ));
			} else {
				$button .= __( 'Link with Twitter', 'profile-builder' );
			}
		} else {
			if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['twitter-button-text'] ) ) {
				$button .= wppb_icl_t( 'plugin profile-builder-pro', 'social_connect_twitter_button_text_translation', esc_attr( $social_connect_instance->wppb_social_connect_settings[0]['twitter-button-text'] ));
			} else {
				$button .= __( 'Sign in with Twitter', 'profile-builder' );
			}
		}
    }

    $button .= '</a>';
	if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
		$button .= '</div>';
	}

    return $button;
}