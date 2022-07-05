<?php

    /*
     * Function that ads the new MailChimp field to the fields list
     * and also the list of fields that skip the meta-name check
     *
     * @since v.1.0.0
     *
     * @param array $fields     - The names of all the fields
     *
     * @return array
     *
     */
    function wppb_in_mci_manage_field_types( $fields ) {
        $fields[] = 'MailChimp Subscribe';

        return $fields;
    }
    add_filter( 'wppb_manage_fields_types', 'wppb_in_mci_manage_field_types' );
    add_filter( 'wppb_skip_check_for_fields', 'wppb_in_mci_manage_field_types' );


    /*
     * Function adds the MailChimp lists checkbox options in the field property from Manage Fields
     *
     * @since v.1.0.0
     *
     * @param array $fields - The current field properties
     *
     * @return array        - The field properties that now include the MailChimp properties
     *
     */
    function wppb_in_mci_manage_fields( $fields ) {

        $settings  = get_option('wppb_mci_settings');
        $key_valid = get_option('wppb_mailchimp_api_key_validated', false);

        $wppb_mci_lists = array();

        if( !isset( $settings['api_key'] ) || empty( $settings['api_key'] ) ) {

            $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailchimp-lists', 'title' => __( 'MailChimp List', 'profile-builder' ), 'options' => $wppb_mci_lists, 'description' => sprintf( __( 'Please enter a MailChimp API key <a href="%s">here</a>.', 'profile-builder' ), admin_url('admin.php?page=profile-builder-mailchimp') ) );

        } elseif ( $key_valid == false ) {

            $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailchimp-lists', 'title' => __( 'MailChimp List', 'profile-builder' ), 'options' => $wppb_mci_lists, 'description' => __( "Something went wrong. Either the API key is invalid or we could not connect to MailChimp to validate the key.", 'profile-builder' ) );

        } else {

            if (isset($settings['lists']) && !empty($settings['lists'])) {

                $wppb_mci_lists[] = '%' . __( 'Select a list...', 'profile-builder' ) . '%';

                foreach ($settings['lists'] as $mci_list_id => $mci_list) {
                    $wppb_mci_lists[] = '%' . $mci_list['name'] . '%' . $mci_list_id;
                }
            }

            if( !empty($wppb_mci_lists) ) {
                $fields[] = array( 'type' => 'select', 'slug' => 'mailchimp-lists', 'title' => __( 'MailChimp List', 'profile-builder' ), 'options' => $wppb_mci_lists, 'description' => __( "Select in which MailChimp list you wish to add a new subscriber", 'profile-builder' ) );
                $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailchimp-default-checked', 'title' => __( 'Checked by Default', 'profile-builder' ), 'options' => array( '%Yes%yes' ), 'description' => __( "If checked the Subscribe checkbox in the front-end will be checked by default on register forms", 'profile-builder' ) );
                $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailchimp-hide-field', 'title' => __( 'Hide on Edit Profile', 'profile-builder' ), 'options' => array( '%Yes%yes' ), 'description' => __( "If checked this field will not be displayed on edit profile forms", 'profile-builder' ) );
            } else {
                $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailchimp-lists', 'title' => __( 'MailChimp List', 'profile-builder' ), 'options' => $wppb_mci_lists, 'description' => __( "We couldn't find any lists in your MailChimp settings.", 'profile-builder' ) );
            }

        }

        return $fields;
    }
    add_filter( 'wppb_manage_fields', 'wppb_in_mci_manage_fields' );


    /*
     * Function that checks if the user selected at least one list from the MailChimp list options
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_mci_check_extra_manage_field( $message, $posted_values ) {

        if( $posted_values['field'] == 'MailChimp Subscribe' ) {
            if( empty( $posted_values['mailchimp-lists'] ) ) {
                $message .= __( "Please select at least one MailChimp list \n", 'profile-builder' );
            }
        }

        return $message;
    }
    add_filter( 'wppb_check_extra_manage_fields', 'wppb_in_mci_check_extra_manage_field', 10, 2 );


    /*
     * Function that removes the field from the user-listing moustache variables
     *
     * @since v.1.0.3
     *
     */
    function wppb_in_mci_strip_moustache_var( $wppb_manage_fields ) {

        if( is_array( $wppb_manage_fields ) ) {
            foreach( $wppb_manage_fields as $key => $field ) {
                if( $field['field'] == 'MailChimp Subscribe' ) {
                    unset( $wppb_manage_fields[$key] );
                }
            }
        }

        return $wppb_manage_fields;
    }
    add_filter( 'wppb_userlisting_merge_tags', 'wppb_in_mci_strip_moustache_var' );



    /*
     * Function that returns a strip down array of the manage fields
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_mci_get_manage_fields() {

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
                    $field['field'] != 'Select (User Role)' &&
                    $field['field'] != 'Subscription Plans'

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