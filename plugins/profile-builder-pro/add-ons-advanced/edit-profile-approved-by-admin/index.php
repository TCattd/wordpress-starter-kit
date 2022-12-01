<?php
/*
Profile Builder - Edit Profile Approved by Admin Add-On
License: GPL2

== Copyright ==
Copyright 2014 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/


/*
 * Define plugin path and include dependencies
 *
 */
define('WPPBEPAA_IN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPPBEPAA_IN_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
* Function that adds the necessary filters in order to change the output of the fields
*
* @since v.1.0.0
*
*/
add_action( 'init', 'wppb_in_init_edit_profile_approval' );
function wppb_in_init_edit_profile_approval() {

    if( !class_exists('Wordpress_Creation_Kit_PB') )
        return;

    //handel fields visual output
    add_filter( 'wppb_extra_attribute', 'wppb_in_epaa_handle_field_visual_output', 20 , 3 );
    if( !current_user_can( 'manage_options' ) || !current_user_can( 'edit_users' ) ) {
        add_filter('wppb_output_after_form_field', 'wppb_in_epaa_handle_field_approval_message', 19, 5);
    }

    if( current_user_can( 'manage_options' ) || current_user_can( 'edit_users' ) ) {
        add_filter('wppb_output_after_form_field', 'wppb_in_epaa_handle_field_approval_button', 20, 5);
        add_filter('wppb_after_form_fields', 'wppb_in_epaa_buttons_for_admins', 20, 3 );
    }

    //handle field save
    add_filter( 'wppb_build_userdata', 'wppb_in_epaa_handle_default_fields_save', 99, 3 );
    add_filter( 'wppb_pre_save_form_field', 'wppb_in_epaa_handle_fields_with_meta_save', 1 , 5 );

    //handle what value is displayed
    add_filter( "get_user_metadata", 'wppb_in_epaa_maybe_show_alternative_value', 10, 4 );
    add_filter( "wppb_user_meta_exists_meta_name", 'wppb_in_epaa_check_alternative_value_exists', 20, 3 );
    add_filter( 'get_the_author_user_email', 'wppb_in_epaa_maybe_show_alternative_value_for_user_email', 99, 3 );
    add_filter( 'get_the_author_user_url', 'wppb_in_epaa_maybe_show_alternative_value_for_user_url', 99, 3 );

    //handle notification to admin
    add_action( 'wppb_edit_profile_success', 'wppb_in_epaa_send_notification_to_admin', 10, 3 );

    //show edit profile notification to user
    add_action( 'wppb_edit_profile_success', 'wppb_in_epaa_show_edit_profile_notification', 10, 3 );

    //add a meta when a user edits his profile and has unapproved fields
    add_action( 'wppb_edit_profile_success', 'wppb_in_epaa_add_awaiting_review_meta', 10, 3 );

    if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {

        //modify edit other user dropdown to show only users that have unapproved fields
        add_filter( 'wppb_edit_other_users_dropdown_query_args', 'wppb_in_epaa_show_in_dropdown_only_users_with_unapproved_fields' );
        //show links on edit profile form above the edit other users dropdown
        add_action('wppb_before_edit_profile_fields', 'wppb_in_epaa_approval_mode_links_for_admin', 5);

        if (isset($_REQUEST['wppb_epaa_review_users']) && $_REQUEST['wppb_epaa_review_users'] == 'true') {
            //make sure we show the dropdown
            add_filter('wppb_display_edit_other_users_dropdown', 'wppb_in_epaa_force_display_edit_other_user_dropdown');
            add_filter('wppb_edit_other_users_count_limit', 'wppb_in_epaa_force_edit_other_users_count_limit');
            //force show all fields on the edit profile form
            remove_filter('wppb_change_form_fields', 'wppb_in_multiple_forms_change_fields', 10, 2);
        }
    }

}

//set up ajax hooks
add_action( 'wp_ajax_wppb_epaa_approve_values', 'wppb_in_epaa_approve_values' );
/**
 * Function that approves the values and sends the notifications
 */
function wppb_in_epaa_approve_values(){
    if( current_user_can( 'manage_options' ) || current_user_can( 'edit_users' ) ){
        $field_ids = isset($_POST['fieldIDS']) ? sanitize_text_field( $_POST['fieldIDS'] ) : ''; //should be a comma separated list of ids
        if( !empty( $field_ids ) )
            $field_ids = explode(',', $field_ids);

        if( !empty( $_POST['unapprovedIDS'] ) )
            $unapproved_field_ids = sanitize_text_field( $_POST['unapprovedIDS'] ); //should be a comma separated list of ids
        else
            $unapproved_field_ids = array();

        $user_id = isset( $_POST['userID'] ) ? sanitize_text_field( $_POST['userID'] ) : '';

        if( !empty( $field_ids ) ){
            $all_fields = get_option( 'wppb_manage_fields' );
            if( !empty($all_fields) ){
                foreach ( $all_fields as $field ){
                    if( $field['field'] != 'Repeater' ) {
                        if (in_array($field['id'], $field_ids)) {

                            $meta_name = wppb_in_epaa_get_alternative_meta_for_field($field);
                            $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name($meta_name);
                            $alternative_meta_value = get_user_meta($user_id, $alternative_meta_name, true);

                            delete_user_meta($user_id, $alternative_meta_name);

                            if (empty($field['meta-name']) && $meta_name == 'website') {
                                $user_id = wp_update_user(array('ID' => $user_id, 'user_url' => $alternative_meta_value));
                            } else if (empty($field['meta-name']) && $meta_name == 'email') {

                                // check if email change request needs confirmation from user
                                if( apply_filters( 'wppb_email_confirmation_on_user_email_change', false )) {
                                    $new_email_data = array( 'email' => $alternative_meta_value, 'user_id' => $user_id );
                                    do_action('wppb_send_mail_address_change_request', $new_email_data );
                                } else {
                                    $user_id = wp_update_user(array('ID' => $user_id, 'user_email' => $alternative_meta_value));
                                }

                            } else {
                                update_user_meta($user_id, $meta_name, $alternative_meta_value);
                            }

                        }
                    }
                    else{//handle repeaters
                        $repeater_group = get_option( $field['meta-name'], 'not_set' );
                        if ( $repeater_group != 'not_set' ) {
                            $extra_groups_count = get_user_meta( $user_id, $field['meta-name'] . '_extra_groups_count', true );

                            for ( $i = 0; $i <= $extra_groups_count; $i++ ){
                                $indexed_repeater_group = wppb_rpf_add_meta_data($repeater_group, $field['meta-name'], 0, $i );
                                foreach( $indexed_repeater_group as $rp_field ){
                                    if (in_array($rp_field['id'], $field_ids)) {
                                        $meta_name = wppb_in_epaa_get_alternative_meta_for_field($rp_field);
                                        $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name($meta_name);
                                        $alternative_meta_value = get_user_meta($user_id, $alternative_meta_name, true);
                                        delete_user_meta($user_id, $alternative_meta_name);
                                        update_user_meta($user_id, $meta_name, $alternative_meta_value);
                                    }
                                }
                            }

                        }
                    }
                }

            }
        }
        //send email to user here
        wppb_in_epaa_send_notification_to_user( $user_id, $field_ids, $unapproved_field_ids );
        //remove the meta for the user when an admin reviews the profile
        delete_user_meta( $user_id, '_wppb_user_awaiting_review' );
        die('success');
    }
    die();
}




/*
 * Function adds the edit profile approval checkbox option in the field property from Form Fields
 *
 * @since v.1.0.0
 *
 * @param array $fields - The current field properties
 *
 * @return array        - The field properties that now include the visibility and user role visibility properties
 */
function wppb_in_epaa_properties_manage_field( $fields ) {

    $visibility_properties = array(
        array( 'type' => 'checkbox', 'slug' => 'edit-profile-approved-by-admin', 'title' => __( 'Requires Admin Approval on Edit Profile', 'profile-builder' ), 'options' => array( '%Yes%yes' ), 'description' => __( "Choose if this field requires an administrator to approve any modifications on the edit profile forms", 'profile-builder' ) )
    );

    foreach( $visibility_properties as $field_property )
        array_push( $fields, $field_property );

    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_in_epaa_properties_manage_field' );


/*
 * Function that enqueues the necessary scripts
 *
 * @since v.1.0.0
 */
function wppb_in_epaa_scripts_and_styles($hook) {
    if ( $hook == 'profile-builder_page_manage-fields' ) {
        wp_enqueue_script( 'wppb-epaa-js', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery', 'wppb-manage-fields-live-change'), PROFILE_BUILDER_VERSION );
    }
    wp_enqueue_script( 'wppb-epaa-frontend-js', plugin_dir_url(__FILE__) . 'assets/js/epaa-frontend.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );

    wp_localize_script( 'wppb-epaa-frontend-js', 'wppb_epaa', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

    wp_enqueue_style( 'wppb-epaa-style', plugin_dir_url(__FILE__) . 'assets/css/style.css' );
}
add_action( 'admin_enqueue_scripts', 'wppb_in_epaa_scripts_and_styles' );
add_action( 'wp_enqueue_scripts', 'wppb_in_epaa_scripts_and_styles' );


/*
* Function that maps default user fields to their hooks in Profile Builder
*
* @since v.1.1.6
*
* @return array
*/
function wppb_in_epaa_default_fields_with_no_meta() {
    return array(
        'Default - E-mail' => 'email',
        'Default - Website' => 'website',
    );
}

/**
 * Function that gets the alternative meta name without the prefix for every field
 * @param $field
 * @return mixed
 */
function wppb_in_epaa_get_alternative_meta_for_field( $field ){
    $filter_default_fields = wppb_in_epaa_default_fields_with_no_meta();

    //default fields don't have a meta name so we need to map it to the field value in the database users table
    if( array_key_exists($field['field'], $filter_default_fields ) ){
        $meta_name = $filter_default_fields[ $field['field'] ];
    }
    else if ( $field['field'] == 'Select (User Role)' ) {
        $meta_name = 'custom_field_user_role';
    }
    else {
        $meta_name = $field['meta-name'];
    }

    return $meta_name;
}

/**
 * Function that ads a prefix to the alternative meta. this is how it will be saved in the database
 * @param $meta_name
 * @return string
 */
function wppb_in_epaa_prefix_alternative_meta_name( $meta_name ){
    return '_wppb_epaa_'.$meta_name;
}

/**
 * Get the current user ID for edit profile
 *
 * @return int      $user_id    User Id
 */
function wppb_in_epaa_get_desired_user_id( ){

    if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
        if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) ){
            return absint( $_GET['edit_user'] );
        }
    }
    return get_current_user_id();
}


/*
 * Function that changes the displayed value for the user role visibility property of the field
 * to a representative icon
 *
 * @since v.1.0.0
 *
 * @param string $display_value     - The saved value of the field in <pre> tag
 *
 */
function wppb_in_change_display_value_to_icon_epaa( $display_value ) {
    $visible_string = str_replace( '<pre>', '', str_replace( '</pre>', '', $display_value ) );

    if( $visible_string === 'yes' ) {
        return '<span title="' . __('This field requires admin approval on edit profile', 'profile-builder') . '" class="wppb-manage-fields-dashicon dashicons dashicons-yes"></span>';
    }
    else{
        return '';
    }

    return $display_value;
}
add_filter( 'wck_pre_displayed_value_wppb_manage_fields_element_edit-profile-approved-by-admin', 'wppb_in_change_display_value_to_icon_epaa' );


/**
 * Function that ads the Approve all and Finish review buttons at the bottom of the form
 * @param $content
 * @param $form_location
 * @param $form_id
 * @return string
 */
function wppb_in_epaa_buttons_for_admins( $content, $form_location, $form_id ){
    if( $form_location === 'edit_profile' ){

        $found = false;

        $user_id = wppb_in_epaa_get_desired_user_id();

        $all_fields = get_option( 'wppb_manage_fields' );
        if( !empty($all_fields) ){
            foreach ( $all_fields as $field ){
                if( $field['field'] != 'Repeater' ) {
                    if (isset($field['edit-profile-approved-by-admin']) && $field['edit-profile-approved-by-admin'] === 'yes') {
                        $meta_name = wppb_in_epaa_get_alternative_meta_for_field($field);
                        $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name($meta_name);

                        if (!empty($alternative_meta_name)) {
                            $unapproved_value_exists = wppb_user_meta_exists($user_id, $alternative_meta_name);
                            if (!empty($unapproved_value_exists)) {
                                $found = true;
                            }
                        }
                    }
                }
                else{//handle repeaters
                    $repeater_group = get_option( $field['meta-name'], 'not_set' );
                    if ( $repeater_group != 'not_set' ) {
                        $extra_groups_count = get_user_meta( $user_id, $field['meta-name'] . '_extra_groups_count', true );

                        for ( $i = 0; $i <= $extra_groups_count; $i++ ){
                            $indexed_repeater_group = wppb_rpf_add_meta_data($repeater_group, $field['meta-name'], 0, $i );
                            foreach( $indexed_repeater_group as $rp_field ){
                                if (isset($rp_field['edit-profile-approved-by-admin']) && $rp_field['edit-profile-approved-by-admin'] === 'yes') {
                                    $meta_name = wppb_in_epaa_get_alternative_meta_for_field($rp_field);
                                    $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name($meta_name);

                                    if (!empty($alternative_meta_name)) {
                                        $unapproved_value_exists = wppb_user_meta_exists($user_id, $alternative_meta_name);
                                        if (!empty($unapproved_value_exists)) {
                                            $found = true;
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }

        if( $found ) {
            $content = '<p class="wppb-epaa-admin-actions"><button id="wppb-epaa-finish-review" data-wppb-epaa-approve-fields="" data-wppb-epaa-user-id="' . $user_id . '">' . __('Finish Review and Send Notifications', 'profile-builder') . '</button></p>' . $content;
            $content = '<p class="wppb-epaa-admin-actions"><button id="wppb-epaa-approve-all">' . __('Approve All', 'profile-builder') . '</button></p>' . $content;
        }
    }

    return $content;
}

function wppb_in_epaa_handle_field_approval_message( $output, $field, $form_id, $form_location, $called_from ){
    if( $form_location === 'edit_profile' ){
        if( isset( $field['edit-profile-approved-by-admin'] ) && $field['edit-profile-approved-by-admin'] === 'yes' ) {

            $user_id = wppb_in_epaa_get_desired_user_id();
            $meta_name = wppb_in_epaa_get_alternative_meta_for_field($field);
            $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name( $meta_name );

            if( !empty( $alternative_meta_name ) ) {
                $unapproved_value_exists = wppb_user_meta_exists( $user_id, $alternative_meta_name );
                if (!empty($unapproved_value_exists)) {
                    $output = '<span class="wppb-description-delimiter wppb-epaa-description">'. __( 'This field requires approval by an administrator', 'profile-builder' ) .'</span>' . $output;
                }
            }
        }
    }

    return $output;
}

/**
 * Function that ads the approval switch for each element for admins on edit profile
 * @param $output
 * @param $field
 * @param $form_id
 * @param $form_location
 * @param $called_from
 * @return string
 */
function wppb_in_epaa_handle_field_approval_button( $output, $field, $form_id, $form_location, $called_from ){
    if( $form_location === 'edit_profile' ){
        if( isset( $field['edit-profile-approved-by-admin'] ) && $field['edit-profile-approved-by-admin'] === 'yes' ) {

            $user_id = wppb_in_epaa_get_desired_user_id();
            $meta_name = wppb_in_epaa_get_alternative_meta_for_field($field);
            $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name( $meta_name );

            if( !empty( $alternative_meta_name ) ) {
                $unapproved_value_exists = wppb_user_meta_exists( $user_id, $alternative_meta_name );
                if (!empty($unapproved_value_exists)) {
                    $output = '<div class="wppb-epaa-actions"><span>'.__('Unapproved', 'profile-builder').'</span><label class="wppb-epaa-switch"><input type="checkbox" data-wppb-epaa-field-id="'. $field['id'] .'"><span class="wppb-epaa-slider"></span></label><span>'.__('Approved', 'profile-builder').'</span><div>' . $output;
                }
            }
        }
    }

    return $output;
}



/**
 * This function adds extra attributes to fields that have admin approval on them and have a value that needs to be approved
 * @param $extra_attributes
 * @param $field
 * @param $form_location
 * @return string
 */
function wppb_in_epaa_handle_field_visual_output( $extra_attributes, $field, $form_location ){

    if( $form_location === 'edit_profile' ){
        if( isset( $field['edit-profile-approved-by-admin'] ) && $field['edit-profile-approved-by-admin'] === 'yes' ){

            $user_id = wppb_in_epaa_get_desired_user_id();
            $meta_name = wppb_in_epaa_get_alternative_meta_for_field($field);
            $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name( $meta_name );

            if( !empty( $alternative_meta_name ) ) {
                $unapproved_value_exists = wppb_user_meta_exists( $user_id, $alternative_meta_name );

                if (!empty($unapproved_value_exists)) {
                    if (current_user_can('manage_options')) {
                        $extra_attributes = $extra_attributes . ' data-wppb-epaa-approval-action="true" data-wppb-epaa-field-id="'. $field['id'] .'"';
                    } else {
                        $extra_attributes = $extra_attributes . ' data-wppb-epaa-requires-approval="true" title="This field requires approval by an administrator"';
                    }
                }
            }
        }
    }

    return $extra_attributes;
}


/**
 * Function that takes care of saving default fields. It prevents the save to the actual field and saves it to an alternative meta
 * @param $userdata
 * @param $global_request
 * @param $form_args
 * @return mixed
 */
function wppb_in_epaa_handle_default_fields_save( $userdata, $global_request, $form_args ){
    if( $form_args['form_type'] === 'edit_profile' ){
        if ( !current_user_can('manage_options') ) {
            if (!empty($form_args['form_fields'])) {

                //the userdata that is saved in the meta uses get_user_meta eventually so we need to remove our filter for the old value
                remove_filter( "get_user_metadata", 'wppb_in_epaa_maybe_show_alternative_value', 10, 4 );

                /* set a global to know which fields that need approval have been modified */
                global $wppb_epaa_fields_awaiting_approval;
                if( !isset( $wppb_epaa_fields_awaiting_approval ) )
                    $wppb_epaa_fields_awaiting_approval = array();


                foreach ($form_args['form_fields'] as $field) {
                    if( strpos( $field["field"], 'Default' ) === 0 ) {//make sure this applies only to default fields
                        if (isset($field['edit-profile-approved-by-admin']) && $field['edit-profile-approved-by-admin'] === 'yes') {
                            $meta_name = wppb_in_epaa_get_alternative_meta_for_field($field);

                            if( $meta_name == 'email' ){
                               $user_prop = 'user_email';
                            }
                            else if( $meta_name == 'website' ){
                                $user_prop = 'user_url';
                            }
                            else{
                                $user_prop = $meta_name;
                            }


                            $new_value = $global_request[$meta_name];

                            $user_id = get_current_user_id();
                            $user_info = get_userdata($user_id);

                            //for user_prop that are stored in usermeta the $user_info->$user_prop actually does a get_user_meta in the end and it is filtered
                            if( $user_info->$user_prop != $new_value ) {
                                $alternative_meta_name = wppb_in_epaa_prefix_alternative_meta_name($meta_name);
                                update_user_meta($user_id, $alternative_meta_name, $new_value);
                                unset($userdata[$user_prop]);

                                $wppb_epaa_fields_awaiting_approval[] = array( $field, $new_value, $user_info->$user_prop );
                            }


                        }
                    }
                }
                //add the filter back
                add_filter( "get_user_metadata", 'wppb_in_epaa_maybe_show_alternative_value', 10, 4 );
            }
        }
    }

    return $userdata;
}


/**
 *
 * This function save the fields that have admin approval on them and have a meta key to a different meta.
 * This is only relevant for extra fields and not default fields as default fields save higher in the code and they will not pass the if( $old_meta_value != $new_value ) check
 * @param $bool if false won't save the extra field with meta
 * @param $field
 * @param $user_id
 * @param $request_data
 * @param $form_location
 * @return bool
 */
function wppb_in_epaa_handle_fields_with_meta_save( $bool, $field, $user_id, $request_data, $form_location ){

    if( $form_location === 'edit_profile' ) {
        if ( !current_user_can('manage_options') ) {

            //prevent any default fields to pass
            if( strpos( $field['field'], 'Default' ) === 0 ) {
                return $bool;
            }

            /* set a global to know which fields that need approval have been modified */
            global $wppb_epaa_fields_awaiting_approval;
            if( !isset( $wppb_epaa_fields_awaiting_approval ) )
                $wppb_epaa_fields_awaiting_approval = array();

            if( $field['field'] == 'Repeater' ){//handle repeater
                $repeater_group = get_option($field['meta-name'], 'not_set');
                if ($repeater_group == 'not_set'){
                    return $bool;
                }

                $rpf_limit = 0;
                if ( apply_filters( 'wppb_rpf_enforce_limit_on_save', false, $field ) ){
                    $rpf_limit = wppb_rpf_get_limit( $field, $user_id );
                }

                $extra_groups_count = esc_attr( $request_data[ $field['meta-name'] . '_extra_groups_count' ] );
                if ( 0 < $rpf_limit && (($rpf_limit -1)  < $extra_groups_count) ) {
                    $extra_groups_count = $rpf_limit - 1;
                }

                for ( $i = 0; $i <= $extra_groups_count; $i++ ){
                    $indexed_repeater_group = wppb_rpf_add_meta_data($repeater_group, $field['meta-name'], $rpf_limit, $i );
                    foreach( $indexed_repeater_group as $rp_field ){
                        wppb_in_epaa_handle_fields_with_meta_save( $bool, $rp_field, $user_id, $request_data, $form_location );
                    }
                }

            }
            else {
                if (isset($field['edit-profile-approved-by-admin']) && $field['edit-profile-approved-by-admin'] === 'yes') {

                    if ( empty( $field['meta-name'] ) )
                        $field['meta-name'] = wppb_in_epaa_get_alternative_meta_for_field( $field );

                    if (!empty($field['meta-name'])) {

                        if( isset( $request_data[wppb_handle_meta_name($field['meta-name'])] ) )
                            $new_value = ( is_array( $request_data[wppb_handle_meta_name($field['meta-name'])] ) ) ?
                                implode (',', $request_data[wppb_handle_meta_name($field['meta-name'])] ) :
                                $request_data[wppb_handle_meta_name($field['meta-name'])];

                        //enable the admin approval feature for simple upload
                        if ( ( $field[ 'field' ] == 'Upload' || $field[ 'field' ] == 'Avatar' ) && isset( $field[ 'simple-upload' ] ) && $field[ 'simple-upload' ] == 'yes' ){
                            $field_name = 'simple_upload_' . wppb_handle_meta_name( $field[ 'meta-name' ] );
                            if ( isset( $_FILES[ $field_name ] ) && isset( $_FILES[ $field_name ][ 'size' ] ) && $_FILES[ $field_name ][ 'size' ] > 0 ){
                                if ( !( wppb_belongs_to_repeater_with_conditional_logic( $field ) && !isset( $request_data[ wppb_handle_meta_name( $field[ 'meta-name' ] ) ] ) ) && !( isset( $field[ 'conditional-logic-enabled' ] ) && $field[ 'conditional-logic-enabled' ] == 'yes' && !isset( $request_data[ wppb_handle_meta_name( $field[ 'meta-name' ] ) ] ) ) ) {
                                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                    $file = wp_handle_upload( $_FILES[$field_name], array( 'test_form' => false ) );
                                    if ( !isset( $file[ 'error' ] ) ) {
                                        $filename = isset( $_FILES[ $field_name ][ 'name' ] ) ? sanitize_text_field( $_FILES[ $field_name ][ 'name' ] ) : '';
                                        $wp_filetype = wp_check_filetype( $filename, null );
                                        $attachment = array(
                                            'post_mime_type'    => $wp_filetype[ 'type' ],
                                            'post_title'        => $filename,
                                            'post_content'      => '',
                                            'post_status'       => 'inherit'
                                        );
                                        $new_value = wp_insert_attachment( $attachment, $file[ 'file' ] );
                                        if ( !is_wp_error( $new_value ) && is_numeric( $new_value ) ) {
                                            require_once( ABSPATH . 'wp-admin/includes/image.php' );
                                            $attachment_data = wp_generate_attachment_metadata( $new_value, $file[ 'file' ] );
                                            wp_update_attachment_metadata( $new_value, $attachment_data );
                                        }
                                    }
                                }
                            }
                        }

                        if (isset($new_value)) {

                            //check for old value to be different from the new one
                            /* get all post meta for the user id like it is done in get_user_meta() function  */
                            $meta_cache = wp_cache_get($user_id, 'user_meta');
                            if (!$meta_cache) {
                                $meta_cache = update_meta_cache('user', array($user_id));
                                $meta_cache = $meta_cache[$user_id];
                            }
                            //we always have the $single attribute in get_user_meta to true
                            if (isset($meta_cache[$field['meta-name']][0]))
                                $old_meta_value = maybe_unserialize($meta_cache[$field['meta-name']][0]);
                            else
                                $old_meta_value = '';

                            if ($old_meta_value != $new_value) { //the default fields with meta won't pass this as they save higher in the code
                                update_user_meta($user_id, wppb_in_epaa_prefix_alternative_meta_name($field['meta-name']), $new_value);

                                $wppb_epaa_fields_awaiting_approval[] = array($field, $new_value, $old_meta_value);

                                return false;
                            }
                        }
                    }
                }
            }
        }
    }

    return $bool;
}


/**
 * This function handles the display for the current user and for the admins for fields that have a value that needs approval.
 * It shows the value that needs approval instead of the actual one. This applies to fields that have a meta-name
 * @param $replace
 * @param $object_id
 * @param $meta_key
 * @param $single
 * @return array
 */
function wppb_in_epaa_maybe_show_alternative_value( $replace, $object_id, $meta_key, $single){
    global $wp_current_filter;

    /* if we don't have a meta_key don't do anything */
    if( empty( $meta_key ) )
        return $replace;

    /* prevent here infinite loops. for instance the DIVI theme had a filter inside current_user_can() for get_user_metadata (the filter on which this callback is placed) which created an infinite loop */
    $number_of_calls = array_keys($wp_current_filter, 'get_user_metadata');
    if( count( $number_of_calls ) > 1 )
        return $replace;

    //this applies only to logged in users specifically the admins and the user that is editing his profile
    if( !is_user_logged_in() )
        return $replace;

    if ( current_user_can('manage_options') || $object_id === get_current_user_id() ) {

        $unapproved_meta_key = wppb_in_epaa_prefix_alternative_meta_name($meta_key);
        $unapproved_value_exists = wppb_user_meta_exists($object_id, $unapproved_meta_key );
        if (!empty($unapproved_value_exists)) {
            /* get all post meta for the user id like it is done in get_user_meta() function  */
            $meta_cache = wp_cache_get($object_id, 'user_meta');
            if ( !$meta_cache ) {
                $meta_cache = update_meta_cache( 'user', array( $object_id ) );
                $meta_cache = $meta_cache[$object_id];
            }

            if ( !empty( $meta_cache ) ) {
                if ( isset($meta_cache[$unapproved_meta_key]) ) {
                   return array_map('maybe_unserialize', $meta_cache[$unapproved_meta_key]);
                }
            }
        }

    }

    return $replace;
}


/**
 * Maybe show a different value for the website field to the current user or an administrator
 * @param $value
 * @param $user_id
 * @param $original_user_id
 * @return mixed
 */
function wppb_in_epaa_maybe_show_alternative_value_for_user_url( $value, $user_id, $original_user_id ){
    //this applies only to logged in users specifically the admins and the user that is editing his profile
    if( !is_user_logged_in() )
        return $value;

    if ( current_user_can('manage_options') || $user_id === get_current_user_id() ) {
        $unapproved_value_exists = wppb_user_meta_exists($user_id, wppb_in_epaa_prefix_alternative_meta_name('website') );
        if (!empty($unapproved_value_exists)) {
            $alternative_value = get_user_meta( $user_id, wppb_in_epaa_prefix_alternative_meta_name('website'), true );
            return $alternative_value;
        }
    }

    return $value;
}

/**
 * Maybe show a different value for the email field to the current user or an administrator
 * @param $value
 * @param $user_id
 * @param $original_user_id
 * @return mixed
 */
function wppb_in_epaa_maybe_show_alternative_value_for_user_email( $value, $user_id, $original_user_id ){
    //this applies only to logged in users specifically the admins and the user that is editing his profile
    if( !is_user_logged_in() )
        return $value;

    if ( current_user_can('manage_options') || $user_id === get_current_user_id() ) {
        $unapproved_value_exists = wppb_user_meta_exists($user_id, wppb_in_epaa_prefix_alternative_meta_name('email') );
        if (!empty($unapproved_value_exists)) {
            $alternative_value = get_user_meta( $user_id, wppb_in_epaa_prefix_alternative_meta_name('email'), true );
            return $alternative_value;
        }
    }

    return $value;
}


/**
 * Function that send a notification to the users
 * @param $user_id
 */
function wppb_in_epaa_send_notification_to_user( $user_id, $field_ids, $unapproved_field_ids ){
    if( function_exists('wppb_mail') && apply_filters( 'wppb_epaa_send_notification_to_user', true ) ) {
        $user_info = get_userdata($user_id);

        if( empty( $field_ids ) )
            $field_ids = array();

        if( empty( $unapproved_field_ids ) )
            $unapproved_field_ids = array();

        $content = __( 'Your profile has been reviewed by an administrator', 'profile-builder' ).'<br>';

        $all_fields = get_option( 'wppb_manage_fields' );

        $approved_field_names = array();
        $unapproved_field_names = array();

        if( !empty($all_fields) ) {
            foreach ($all_fields as $field) {
                if (in_array($field['id'], $field_ids)) {
                    $approved_field_names[] = $field['field-title'];
                }
                if (in_array($field['id'], $unapproved_field_ids)) {
                    $unapproved_field_names[] = $field['field-title'];
                }
            }
        }

        $approved_field_names_list = implode( ', ', $approved_field_names );
        $unapproved_field_names_list = implode( ', ', $unapproved_field_names );

        if( !empty( $approved_field_names ) ){
            $content .= sprintf( __( 'Approved fields:%s', 'profile-builder' ), $approved_field_names_list ).'<br>';
        }

        if( !empty( $unapproved_field_names ) ){
            $content .= sprintf( __( 'Unapproved fields:%s', 'profile-builder' ), $unapproved_field_names_list ).'<br>';
        }

        $to = apply_filters( 'wppb_epaa_user_email_to', $user_info->user_email );
        $subject = apply_filters( 'wppb_epaa_user_email_subject', __( 'Your profile has been reviewed by an administrator', 'profile-builder' ), $user_id, $approved_field_names_list, $unapproved_field_names_list );
        $from = apply_filters( 'wppb_epaa_user_email_from_name', get_bloginfo( 'name' ) );
        $content = apply_filters( 'wppb_epaa_user_email_content', $content, $user_id, $approved_field_names_list, $unapproved_field_names_list);

        wppb_mail( $to, $subject, $content, $from, 'wppb_epaa_user_email' );
    }
}


/**
 * Function that send a notification to the administrator
 * @param $user_id
 */
function wppb_in_epaa_send_notification_to_admin( $request, $form_name, $user_id ){
    global $wppb_epaa_fields_awaiting_approval;
    if( function_exists('wppb_mail') && !empty( $wppb_epaa_fields_awaiting_approval ) && apply_filters( 'wppb_epaa_send_notification_to_admin', true ) ){

        $fields = '';
        foreach( $wppb_epaa_fields_awaiting_approval as $field_awaiting_approval ){
            $fields .= sprintf( __( 'Field %1$s changed from %2$s to %3$s', 'profile-builder'),
                    wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field_awaiting_approval[0]['id'].'_title_translation', $field_awaiting_approval[0]['field-title'] ),
                    is_array( $field_awaiting_approval[2] ) ? implode( ',', $field_awaiting_approval[2] ) : $field_awaiting_approval[2],
                    is_array( $field_awaiting_approval[1] ) ? implode( ',', $field_awaiting_approval[1] ) : $field_awaiting_approval[1]
                ). '<br>';
        }

        $userdata = get_userdata( $user_id );
        $content = sprintf( __( 'The user %1$s has updated their profile and some of the fields require admin approval:<br><br> %2$s', 'profile-builder' ), $userdata->user_login, $fields ).'<br><br>';
        $approve_url = '<a href="'. esc_url_raw( add_query_arg( array( 'edit_user' => $user_id, 'wppb_epaa_review_users' => 'true' ), wppb_in_epaa_determine_url_from_form_name($form_name) ) ) .'" target="_blank">here</a>';
        $content .= sprintf( __( 'Access this link to approve changes: %1$s', 'profile-builder' ), $approve_url );

        $to = apply_filters( 'wppb_epaa_admin_email_to', get_option('admin_email') );
        $subject = apply_filters( 'wppb_epaa_admin_email_subject', __( 'A user has updated their profile. Some fields need approval', 'profile-builder' ), $user_id, $fields, $approve_url );
        $from = apply_filters( 'wppb_epaa_admin_email_from_name', get_bloginfo( 'name' ) );
        $content = apply_filters( 'wppb_epaa_admin_email_content', $content, $user_id, $fields, $approve_url );

        wppb_mail( $to, $subject, $content, $from, 'wppb_epaa_admin_email' );
    }
}

/**
 * Function used to display a message at the top of the edit profile form for users
 * @param $request
 * @param $form_name
 * @param $user_id
 */
function wppb_in_epaa_show_edit_profile_notification( $request, $form_name, $user_id ){
    global $wppb_epaa_fields_awaiting_approval;
    if( !empty( $wppb_epaa_fields_awaiting_approval ) ){
        echo '<p class="wppb-epaa-warning">';
        $fields = '';
        foreach( $wppb_epaa_fields_awaiting_approval as $field_awaiting_approval ){
            $fields .= sprintf( __( 'Field %1$s requires approval from an administrator', 'profile-builder'),
                    wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field_awaiting_approval[0]['id'].'_title_translation', $field_awaiting_approval[0]['field-title'] )
                ). '<br>';
        }

        echo $fields; //phpcs:ignore

        echo '</p>';
    }
}

/**
 * Function to determine the url from a form_name attribute
 * @param $form_name
 * @return false|string
 */
function wppb_in_epaa_determine_url_from_form_name( $form_name ){
    if( $form_name != 'unspecified' ){
        $form_pages = get_posts( array( 'post_type' => 'page', 's' => '[wppb-edit-profile form_name="'.$form_name.'"' ) );
        if ( !empty( $form_pages ) && isset( $form_pages[0], $form_pages[0]->post_content )  ) {
            if( has_shortcode( $form_pages[0]->post_content, 'wppb-edit-profile' ) ) {
                return get_permalink($form_pages[0]->ID);
            }
        }
    }
    else{
        $all_edit_form_pages = get_posts( array( 'post_type' => 'page', 's' => '[wppb-edit-profile', 'posts_per_page' => -1, 'numberposts' => -1 ) );
        $specific_form_pages = get_posts( array( 'post_type' => 'page', 's' => '[wppb-edit-profile form_name=', 'posts_per_page' => -1, 'numberposts' => -1 ) );

        $default_form_pages = array_udiff( $all_edit_form_pages, $specific_form_pages, 'wppb_in_epaa_compare_objects' );
        if ( !empty( $default_form_pages ) ) {
            if( has_shortcode( $default_form_pages[0]->post_content, 'wppb-edit-profile' ) ) {
                return get_permalink($default_form_pages[0]->ID);
            }
        }

    }

    return '';
}

/**
 * Function that compares two post objects
 * @param $obj_a
 * @param $obj_b
 * @return mixed
 */
function wppb_in_epaa_compare_objects($obj_a, $obj_b) {
    return $obj_a->ID - $obj_b->ID;
}

//if a meta does not exist for a user ( just added the field for ex. ) we need to check if the alternative meta exists so we can show the alternative meta instead of empty or the default value
function wppb_in_epaa_check_alternative_value_exists( $result, $id, $meta_name ){
    if( !$result && strpos( $meta_name, '_wppb_epaa_' ) !== 0 ){
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s", $id, '_wppb_epaa_'.$meta_name ) );
    }
    return $result;
}

//force show the edit other users dropdown
function wppb_in_epaa_force_display_edit_other_user_dropdown($bool){
    return true;
}

//increase the number of the limit of users so we always show the edit other users dropdown
function wppb_in_epaa_force_edit_other_users_count_limit($number){
    return 1000000;
}

/**
 * Show links above edit other user dropdown so admins can edit users that require approval
 */
function wppb_in_epaa_approval_mode_links_for_admin(){
    echo '<p id="wppb-epaa-admin-review-links">';
    echo '<a href="'. esc_url_raw( add_query_arg( 'wppb_epaa_review_users', 'true', remove_query_arg( 'wppb_epaa_reviewed_users_with_unapproved_fields', wppb_curpageurl() ) ) ) .'">'. esc_html__( 'Show users that require review', 'profile-builder' ) .'</a>';
    echo ' | ';
    echo '<a href="'. esc_url_raw( add_query_arg( array( 'wppb_epaa_review_users' => 'true', 'wppb_epaa_reviewed_users_with_unapproved_fields' => 'true' ), wppb_curpageurl() ) ).'">'. esc_html__( 'Show reviewed users with unapproved fields', 'profile-builder' ) .'</a>';
    if( isset( $_REQUEST['wppb_epaa_review_users'] ) && $_REQUEST['wppb_epaa_review_users'] == 'true' ) {
        echo ' | ';
        echo '<a href="'. esc_url_raw( remove_query_arg( array( 'wppb_epaa_review_users', 'wppb_epaa_reviewed_users_with_unapproved_fields' ), wppb_curpageurl() ) ).'">'. esc_html__( 'Exit Review Mode', 'profile-builder' ) .'</a>';
    }
    echo '</p>';
}

/**
 * Add a user meta that marks that user that he is awaiting an admin review
 * @param $request
 * @param $form_name
 * @param $user_id
 */
function wppb_in_epaa_add_awaiting_review_meta( $request, $form_name, $user_id ){
    global $wppb_epaa_fields_awaiting_approval;
    if( !empty( $wppb_epaa_fields_awaiting_approval ) ){
        update_user_meta( $user_id, '_wppb_user_awaiting_review', $form_name );
    }
}


/**
 * Function that filters the query args for the "edit other users" dropdown on edit profile so we can filter users that need review or have been reviewed but still have unnaproved fiedls
 * @param $query_args
 * @return mixed
 */
function wppb_in_epaa_show_in_dropdown_only_users_with_unapproved_fields( $query_args ){

    if( isset( $_REQUEST['wppb_epaa_review_users'] ) && $_REQUEST['wppb_epaa_review_users'] == 'true' ) {

        //users that need review or have been reviewed but still have unnaproved fiedls
        if( isset( $_REQUEST['wppb_epaa_reviewed_users_with_unapproved_fields'] ) && $_REQUEST['wppb_epaa_reviewed_users_with_unapproved_fields'] == 'true' ) {
            global $wpdb;
            $users_that_have_unnaproved_fields = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key LIKE %s", '%_wppb_epaa_%'), ARRAY_A);
            $users_that_await_review = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s", '_wppb_user_awaiting_review'), ARRAY_A);
            if (!empty($users_that_have_unnaproved_fields)) {

                $users_that_have_unnaproved_fields_ids = array();
                $users_that_await_review_ids = array();

                foreach ($users_that_have_unnaproved_fields as $u) {
                    $users_that_have_unnaproved_fields_ids[] = $u['user_id'];
                }

                if( !empty( $users_that_await_review ) ){
                    foreach ($users_that_await_review as $u) {
                        $users_that_await_review_ids[] = $u['user_id'];
                    }
                }

                $reviewed_users_with_unapproved_fields_ids =  array_diff( $users_that_have_unnaproved_fields_ids, $users_that_await_review_ids );
                if( !empty( $reviewed_users_with_unapproved_fields_ids ) )//if include is empty it does nothing so we need to treat that case, because in that case we don't want to show any users
                    $query_args['include'] = $reviewed_users_with_unapproved_fields_ids;
                else
                    $query_args['meta_key'] = '__wppb_this_should_never_exist_2222';//this is hacky but through include or number args I can't eliminate all users
            }
            else{
                $query_args['meta_key'] = '__wppb_this_should_never_exist_2222';//this is hacky but through include or number args I can't eliminate all users
            }
        }
        else{//users that await approval
            $query_args['meta_key'] = '_wppb_user_awaiting_review';
        }

    }

    return $query_args;
}
