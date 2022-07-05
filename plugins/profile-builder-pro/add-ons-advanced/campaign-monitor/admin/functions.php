<?php

    /*
     * Function that returns false if the api key is invalid or missing
     *
     * @param array $wppb_cmi_settings      - The settings saved for Campaign Monitor
     *
     * @return bool
     *
     */
    function wppb_in_cmi_get_api_key_status( $wppb_cmi_settings = array() ) {

        // Get saved settings if the parameter was not provided
        if( empty( $wppb_cmi_settings ) )
            $wppb_cmi_settings = get_option( 'wppb_cmi_settings' );

        // Let's say all is good
        $reponse = true;

        // Check to see if the key is valid
        if( get_option('wppb_cmi_api_key_validated', false) == false )
            $reponse = false;

        // Check to see if we saved a value for the key
        if( isset( $wppb_cmi_settings['api_key'] ) && empty( $wppb_cmi_settings['api_key'] ) )
            $reponse = false;

        return $reponse;
    }


    /*
     * Function that returns a strip down array of the manage fields
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_cmi_get_manage_fields() {

        $manage_fields = get_option( 'wppb_manage_fields' );

        foreach( $manage_fields as $key => $field ) {

            // Strip fields with empty meta-name, but keep the defaults
            if (
                empty( $field['meta-name'] ) &&
                (
                    $field['field'] != 'Default - E-mail' &&
                    $field['field'] != 'Default - Username' &&
                    $field['field'] != 'Default - Display name publicly as' &&
                    $field['field'] != 'Default - Website' &&
                    $field['field'] != 'Select (User Role)'

                )
            )
                unset( $manage_fields[ $key ] );

            // Strip WooCommerce fields from WooCommerce Add-On
            if( $field['meta-name'] == 'wppbwoo_billing' || $field['meta-name'] == 'wppbwoo_shipping' )
                unset( $manage_fields[ $key ] );

        }

        $manage_fields = array_values( $manage_fields );

        return $manage_fields;
    }


    /*
     * Function that returns the request name of a field
     *
     * @since v.1.0.0
     *
     * @param array $wppb_field  - The field from the manage fields option
     *
     * @return string
     *
     */
    function wppb_in_cmi_get_request_name( $wppb_field ) {

        switch( $wppb_field['field'] ) {
            case 'Default - Username':
                return 'username';
                break;
            case 'Default - E-mail':
                return 'email';
                break;
            case 'Default - Website':
                return 'website';
                break;
            case 'Default - Biographical Info':
                return 'description';
                break;
            case 'Default - Display name publicly as':
                return 'display_name';
                break;
            case 'Select (User Role)':
                return 'custom_field_user_role';
                break;
            default:
                return $wppb_field['meta-name'];
                break;
        }

    }