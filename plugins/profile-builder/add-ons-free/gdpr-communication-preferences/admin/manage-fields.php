<?php
/*
 * Function that adds the new GDPR Communication Preferences field to the fields list
 * and also the list of fields that skip the meta-name check
 *
 * @param array $fields     - The names of all the fields
 *
 * @return array
 *
 */
function wppb_gdprcp_manage_field_types( $fields ) {
    $fields[] = 'GDPR Communication Preferences';
    return $fields;
}
add_filter( 'wppb_manage_fields_types', 'wppb_gdprcp_manage_field_types' );


/* Function adds the GDPR Communication Preferences option to set a maximum selection size
*
* @param array $fields - The current field properties
*
*/
function wppb_gdprcp_manage_fields( $fields ) {
    $fields[] = array( 'type' => 'checkbox', 'slug' => 'gdpr-communication-preferences', 'title' => __( 'Communication Preferences', 'profile-builder' ), 'options' => array( '%'.__( 'Email', 'profile-builder' ).'%email', '%'.__( 'Telephone', 'profile-builder' ).'%phone', '%'.__( 'SMS', 'profile-builder' ).'%sms', '%'.__( 'Post', 'profile-builder' ).'%post' ), 'description' => __( "Select which communication preferences are available on your site ( drag and drop to re-order )", 'profile-builder' ) );
    $fields[] = array( 'type' => 'text', 'slug' => 'gdpr-communication-preferences-sort-order', 'title' => __( 'Communication Preferences Order', 'profile-builder' ), 'description' => __( "Save the communication preferences order", 'profile-builder' ) );
    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_gdprcp_manage_fields' );


/**
 * Function that calls the wppb_handle_gdprcp_field
 *
 * @param void
 *
 * @return string
 */
function wppb_gdprcp_sortable_order( $meta_name, $id, $element_id ){
    if ( $meta_name == 'wppb_manage_fields' ) {
        echo "<script type=\"text/javascript\">wppb_handle_gdprcp_field( '#container_wppb_manage_fields' );</script>";
    }
}
add_action("wck_after_adding_form", "wppb_gdprcp_sortable_order", 10, 3);
