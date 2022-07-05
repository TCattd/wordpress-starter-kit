<?php

/**
 * Include the Repeater Field module files.
 *
 * It includes the files only if all the files are present because there are dependencies between them.
 *
 */
$wppb_module_settings = get_option( 'wppb_module_settings', 'not_found' );
if( $wppb_module_settings != 'not_found' ) {
    if ( isset( $wppb_module_settings['wppb_repeaterFields'] ) && ( $wppb_module_settings['wppb_repeaterFields'] == 'show' ) ) {
        $file_names = array( 'repeater-field.php', 'admin/repeater-functions.php', 'admin/repeater-manage-fields.php', 'admin/repeater-mustache-tag.php' );
        $file_missing = false;
        foreach( $file_names as $file_name) {
            if ( ! file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons/repeater-field/' . $file_name )) {
                $file_missing = true;
            }
        }

        if ( ! $file_missing ){
            foreach( $file_names as $file_name ) {
                include_once( WPPB_PAID_PLUGIN_DIR . '/add-ons/repeater-field/' . $file_name );
            }
        }
    }
}
