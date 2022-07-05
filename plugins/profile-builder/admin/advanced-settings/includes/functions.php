<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @param  string        $context   Name of the folder where the code file is placed (tab slug).
 * @param  string        $setting   Name of the file where the code is placed.
 * @return string|false             Returns false if settings are empty (shouldn't reach this point in that case anyway), else returns the setting.
 */
function wppb_toolbox_get_settings( $context, $setting ) {
    $option = 'wppb_toolbox_' . $context . '_settings';

    $settings = get_option( $option );

    if ( $settings == false ) return false;

    if ( isset( $settings[ $setting ] ) )
        return $settings[ $setting ];

    return false;
}
