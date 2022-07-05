<?php

    /*
     * Function that ads the new Campaign Monitor field to the fields list
     * and also the list of fields that skip the meta-name check
     *
     * @since v.1.0.0
     *
     * @param array $fields     - The names of all the fields
     *
     * @return array
     *
     */
    function wppb_in_cmi_manage_field_types( $fields ) {
        $fields[] = 'Campaign Monitor Subscribe';

        return $fields;
    }
    add_filter( 'wppb_manage_fields_types', 'wppb_in_cmi_manage_field_types' );
    add_filter( 'wppb_skip_check_for_fields', 'wppb_in_cmi_manage_field_types' );


    /*
     * Function adds the Campaign Monitor lists select options in the field property from Manage Fields
     *
     * @since v.1.0.0
     *
     * @param array $fields - The current field properties
     *
     * @return array        - The field properties that now include the Campaign Monitor properties
     *
     */
    function wppb_in_cmi_manage_fields( $fields ) {

        $wppb_cmi_api_key_validated = get_option('wppb_cmi_api_key_validated', false);
        $wppb_cmi_settings = get_option('wppb_cmi_settings');

        $wppb_cmi_lists = array();

        if( !isset( $wppb_cmi_settings['api_key'] ) || empty( $wppb_cmi_settings['api_key'] ) ) {

            $fields[] = array( 'type' => 'checkbox', 'slug' => 'campaign-monitor-lists', 'title' => __( 'Campaign Monitor List', 'profile-builder' ), 'options' => $wppb_cmi_lists, 'description' => sprintf( __( 'Please enter a Campaign Monitor API key <a href="%s">here</a>.', 'profile-builder' ), admin_url('admin.php?page=profile-builder-campaign-monitor') ) );

        } elseif ( $wppb_cmi_api_key_validated == false ) {

            $fields[] = array( 'type' => 'checkbox', 'slug' => 'campaign-monitor-lists', 'title' => __( 'Campaign Monitor', 'profile-builder' ), 'options' => $wppb_cmi_lists, 'description' => __( "Something went wrong. Either the API key is invalid or we could not connect to Campaign Monitor to validate the key.", 'profile-builder' ) );

        } else {

            if (isset($wppb_cmi_settings['client']['lists']) && !empty($wppb_cmi_settings['client']['lists'])) {

                $wppb_cmi_lists[] = '%' . __( 'Select a list...', 'profile-builder' ) . '%';

                foreach ($wppb_cmi_settings['client']['lists'] as $wppb_cmi_list_id => $wppb_cmi_list ) {
                    $wppb_cmi_lists[] = '%' . $wppb_cmi_list['name'] . '%' . $wppb_cmi_list_id;
                }
            }

            if( !empty($wppb_cmi_lists) ) {
                $fields[] = array( 'type' => 'select', 'slug' => 'campaign-monitor-lists', 'title' => __( 'Campaign Monitor List', 'profile-builder' ), 'options' => $wppb_cmi_lists, 'description' => __( "Select in which Campaign Monitor list you wish to add a new subscriber", 'profile-builder' ) );
                $fields[] = array( 'type' => 'checkbox', 'slug' => 'campaign-monitor-hide-field', 'title' => __( 'Hide on Edit Profile', 'profile-builder' ), 'options' => array( '%Yes%yes' ), 'description' => __( "If checked this field will not be displayed on edit profile forms", 'profile-builder' ) );
            } else {
                $fields[] = array( 'type' => 'checkbox', 'slug' => 'campaign-monitor-lists', 'title' => __( 'Campaign Monitor List', 'profile-builder' ), 'options' => $wppb_cmi_lists, 'description' => __( "We couldn't find any lists in your Campaign Monitor settings.", 'profile-builder' ) );
            }

        }

        return $fields;
    }
    add_filter( 'wppb_manage_fields', 'wppb_in_cmi_manage_fields' );


    /*
     * Function that checks if the user selected at least one list from the Campaign Monitor list options
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_cmi_check_extra_manage_field( $message, $posted_values ) {

        if( $posted_values['field'] == 'Campaign Monitor Subscribe' ) {
            if( empty( $posted_values['campaign-monitor-lists'] ) ) {
                $message .= __( "Please select at least one Campaign Monitor list \n", 'profile-builder' );
            }
        }

        return $message;
    }
    add_filter( 'wppb_check_extra_manage_fields', 'wppb_in_cmi_check_extra_manage_field', 10, 2 );


    /*
     * Function that removes the field from the user-listing moustache variables
     *
     * @since v.1.0.3
     *
     */
    function wppb_in_cmi_strip_moustache_var( $wppb_manage_fields ) {

        if( is_array( $wppb_manage_fields ) ) {
            foreach( $wppb_manage_fields as $key => $field ) {
                if( $field['field'] == 'Campaign Monitor Subscribe' ) {
                    unset( $wppb_manage_fields[$key] );
                }
            }
        }

        return $wppb_manage_fields;
    }
    add_filter( 'wppb_userlisting_merge_tags', 'wppb_in_cmi_strip_moustache_var' );