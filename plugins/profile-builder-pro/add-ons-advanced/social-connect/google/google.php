<?php
/* Generate the Google button */
function wppb_in_sc_generate_google_button( $form_ID ) {
	global $social_connect_instance;

    if( empty( $social_connect_instance->wppb_social_connect_settings[0]['google-sign-in-button'] ) || ( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['google-sign-in-button'] ) && $social_connect_instance->wppb_social_connect_settings[0]['google-sign-in-button'] == 'old' ) ) {
        $class = 'wppb-sc-google-login wppb-sc-button';
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
        $check_if_linked = get_user_meta( get_current_user_id(), '_wppb_google_connect_id' );
        if( isset( $social_connect_instance->forms_type ) && $social_connect_instance->forms_type == 'edit_profile' && ! empty( $check_if_linked ) ) {
            $class .= ' wppb-sc-disabled-btn';
        }
        $button .= '<a class="' . $class . '" href="#" data-wppb_sc_form_id_google="' . $form_ID . '">';
        $button .= '<i class="wppb-sc-icon-google-plus wppb-sc-icon"></i>';
        if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
            if( isset( $social_connect_instance->forms_type ) && $social_connect_instance->forms_type == 'edit_profile' ) {
                if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['google-button-text-ep'] ) ) {
                    $button .= wppb_icl_t( 'plugin profile-builder-pro', 'social_connect_google_button_text_ep_translation', esc_attr( $social_connect_instance->wppb_social_connect_settings[0]['google-button-text-ep'] ));
                } else {
                    $button .= __( 'Link with Google', 'profile-builder' );
                }
            } else {
                if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['google-button-text'] ) ) {
                    $button .= wppb_icl_t( 'plugin profile-builder-pro', 'social_connect_google_button_text_translation', esc_attr( $social_connect_instance->wppb_social_connect_settings[0]['google-button-text'] ));
                } else {
                    $button .= __( 'Sign in with Google', 'profile-builder' );
                }
            }
        }

        $button .= '</a>';
        if( ! empty( $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] ) && $social_connect_instance->wppb_social_connect_settings[0]['buttons-style'] == 'text' ) {
            $button .= '</div>';
        }
    }
    else{
        $class = 'wppb-sc-google-login-new wppb-sc-button';
        global $pagenow;
        if( $pagenow == 'wp-login.php' ) {
            $class .= '-wp-default';
        }
        $button = '<a class="' . $class . '" href="#" data-wppb_sc_form_id_google="' . $form_ID . '"><i class="wppb-sc-icon-google-plus-new wppb-sc-icon"></i></a>';
    }

    return $button;
}