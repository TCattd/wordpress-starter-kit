<?php

/**
 * The code that runs during plugin activation.
 */

if( !function_exists( 'wppb_in_cmi_activation' ) ){

    function wppb_in_cmi_activation( $addon ) {
        // @TODO: hook doesn't run right now
        if( $addon == 'campaign-monitor' ){

            if( get_option( 'wppb_cmi_api_key_validated', 'not_found' ) == 'not_found' )
                add_option( 'wppb_cmi_api_key_validated', false );
        }

    }
    add_action( 'wppb_add_ons_activate', 'wppb_in_cmi_activation', 10, 1);

}
