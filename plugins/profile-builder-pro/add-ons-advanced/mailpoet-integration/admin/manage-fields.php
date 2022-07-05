<?php

/**
 * Function that adds the new MailPoet field to the fields list
 * and also the list of fields that skip the meta-name check
 *
 * @since v.1.0.0
 *
 * @param array $fields     - The names of all the fields
 *
 * @return array
 *
 */
function wppb_in_mpi_manage_field_types( $fields ) {
    $fields[] = 'MailPoet Subscribe';

    return $fields;
}
add_filter( 'wppb_manage_fields_types', 'wppb_in_mpi_manage_field_types' );
add_filter( 'wppb_skip_check_for_fields', 'wppb_in_mpi_manage_field_types' );


/**
 * Function adds the MailPoet lists checkbox options in the field property from Manage Fields
 *
 * @since v.1.0.0
 *
 * @param array $fields - The current field properties
 *
 * @return array        - The field properties that now include the MailPoet properties
 *
 */
function wppb_in_mpi_manage_fields( $fields ) {

    $mailpoet_lists = wppb_in_mpi_get_lists();

    if ( isset ( $mailpoet_lists ) && !empty($mailpoet_lists)) {
        $wppb_mpi_lists[] = '%' . __( 'Select a list...', 'profile-builder' ) . '%';
        foreach($mailpoet_lists as $list){
            $wppb_mpi_lists[] = '%' . esc_attr( $list['name'] ) . '%' . esc_attr( $list['id'] );
        }
    }else{
        $no_list_found[] = '%' . __( 'No list was found.', 'profile-builder' ) . '%';
    }

    if( !empty($wppb_mpi_lists) ) {
        $fields[] = array( 'type' => 'select', 'slug' => 'mailpoet-lists', 'title' => __( 'MailPoet List', 'profile-builder' ), 'options' => $wppb_mpi_lists, 'description' => __( "Select in which MailPoet list you wish to add a new subscriber", 'profile-builder' ) );
        $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailpoet-default-checked', 'title' => __( 'Checked by Default', 'profile-builder' ), 'options' => array( '%Yes%yes' ), 'description' => __( "If checked the Subscribe checkbox in the front-end will be checked by default on register forms", 'profile-builder' ) );
        $fields[] = array( 'type' => 'checkbox', 'slug' => 'mailpoet-hide-field', 'title' => __( 'Hide on Edit Profile', 'profile-builder' ), 'options' => array( '%Yes%yes' ), 'description' => __( "If checked this field will not be displayed on edit profile forms", 'profile-builder' ) );
    } else if ( isset ($mailpoet_lists) ) {
        $fields[] = array('type' => 'select', 'slug' => 'mailpoet-lists', 'title' => __('MailPoet List', 'profile-builder'), 'options' => $no_list_found, 'description' => __("We couldn't find any lists in your MailPoet settings.", 'profile-builder'));
    }else{
        $fields[] = array('type' => 'select', 'slug' => 'mailpoet-lists', 'title' => __('MailPoet List', 'profile-builder'), 'options' => $no_list_found, 'description' => __("Please install and activate MailPoet plugin.", 'profile-builder'));
    }

    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_in_mpi_manage_fields' );


/**
 * Function that checks if the user selected at least one list from the MailPoet list options
 *
 * @since v.1.0.0
 *
 * @return string
 *
 */
function wppb_in_mpi_check_extra_manage_field( $message, $posted_values ) {

    if( $posted_values['field'] == 'MailPoet Subscribe' ) {
        if( empty( $posted_values['mailpoet-lists'] ) ) {
            $message .= __( "Please select at least one MailPoet list \n", 'profile-builder' );
        }
    }

    return $message;
}
add_filter( 'wppb_check_extra_manage_fields', 'wppb_in_mpi_check_extra_manage_field', 10, 2 );


/**
 * Function that removes the field from the user-listing moustache variables
 *
 * @since v.1.0.0
 *
 * @return array
 *
 */
function wppb_in_mpi_strip_moustache_var( $wppb_manage_fields ) {

    if( is_array( $wppb_manage_fields ) ) {
        foreach( $wppb_manage_fields as $key => $field ) {
            if( $field['field'] == 'MailPoet Subscribe' ) {
                unset( $wppb_manage_fields[$key] );
            }
        }
    }

    return $wppb_manage_fields;
}
add_filter( 'wppb_userlisting_merge_tags', 'wppb_in_mpi_strip_moustache_var' );
