<?php

/**
 * The code that runs during plugin activation.
 */

if( !function_exists( 'wppb_in_mci_activation' ) ){

    function wppb_in_mci_activation( $addon ) {

        if( $addon == 'mailchimp-integration' ){

            if( get_option( 'wppb_mailchimp_api_key_validated', 'not_found' ) == 'not_found' )
                add_option( 'wppb_mailchimp_api_key_validated', false );

        }

    }
    add_action( 'wppb_add_ons_activate', 'wppb_in_mci_activation', 10, 1);

}
