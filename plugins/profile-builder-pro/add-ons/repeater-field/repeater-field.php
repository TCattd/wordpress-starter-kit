<?php

/**
 * Handle Repeater field Output in Register and Edit-profile forms
 *
 * @param $output
 * @param $form_location
 * @param $field
 * @param $user_id
 * @param $field_check_errors
 * @param $global_request
 * @param string $role
 * @param string $form_creator_obj
 * @return string $output                   The HTML output for the Repeater Field
 */
function wppb_repeater_handler($output, $form_location, $field, $user_id, $field_check_errors, $global_request, $role = '', $form_creator_obj = ''){
    if ( $field['field'] == 'Repeater' ) {
        $repeater_group = get_option( $field['meta-name'], 'not_set' );
        if ( $repeater_group == 'not_set' ) {
            return '';
        }

        $rpf_limit = wppb_rpf_get_limit( $field, $user_id, $role );
        $extra_groups_count = 0;
        if ( !empty ( $global_request[ $field['meta-name'] . '_extra_groups_count' ] ) ) {
            $extra_groups_count = intval( esc_attr( $global_request[ $field['meta-name'] . '_extra_groups_count' ] ) );
        }else if ( $form_location == 'edit_profile' || $form_location == 'back_end' ) {
            $number_of_groups = get_user_meta($user_id, $field['meta-name'] . '_extra_groups_count', true);
            if ($number_of_groups != false) {
                $extra_groups_count = $number_of_groups;
            }
        }

        /*
         * Limit is 0 if unlimited. We subtract 1 from the limit because extra_groups_count is the total number of groups submitted minus 1.
         * The limit is also enforced in the front-end.
         */
        if (0 < $rpf_limit && (($rpf_limit - 1) < $extra_groups_count)) {
            $extra_groups_count = $rpf_limit - 1;
        }

        $limit_reached_message = ( empty ( $field['rpf-limit-reached-message'] ) ) ? '' : apply_filters( 'wppb_rpf_limit_reached_message', do_shortcode( $field['rpf-limit-reached-message'] ), $field, $user_id, $role, $rpf_limit, $extra_groups_count );

        $output .= '<input id="' . $field['meta-name'] . '_extra_groups_count" name="' . $field['meta-name'] . '_extra_groups_count" class="wppb-rpf-extra-groups-count" type="hidden" value=' . $extra_groups_count  . ' >';

        if ( $form_location != 'back_end' ) {
            $output .= '<div id="' . $field['meta-name'] . '-limit-reached-popup" class="wppb-rpf-overlay"><div class="wppb-rpf-popup"><a class="wppb-rpf-close">&times;</a>' . $limit_reached_message  .'</div></div>';
            for ($i = -1; $i <= $extra_groups_count; $i++) {
                $set_index = $i;
                if ($i == -1) {
                    // add a hidden set of fields with default values to be used when generating a new group. This will not be saved in the database
                    $set_index = 'template';
                }
                $output .= apply_filters('wppb_output_before_repeater_set', '<ul class="wppb-rpf-group wppb-rpf-singular-set wppb-rpf-name-' . $field['meta-name'] . '"  id="wppb-rpf-set-' . $field['meta-name'] . '_' . $set_index . '" data-wppb-rpf-set="' . $set_index . '" data-wppb-rpf-set-order="' . $set_index . '" >', $field);
                $output .= '<li class="wppb-rpf-action-wrap"><span class="wppb-rpf-action wppb-rpf-remove">&times;</span><span class="wppb-rpf-action wppb-rpf-add">+</span></li>';
                $output .= $form_creator_obj->wppb_output_form_fields($global_request, $field_check_errors, wppb_rpf_add_meta_data($repeater_group, $field['meta-name'], $rpf_limit, $i), 'repeater', true );
                $output .= apply_filters('wppb_output_after_repeater_set', '</ul>', $field);
            }
        }else{
            for ($i = 0; $i <= $extra_groups_count; $i++) {
                $indexed_repeater_group = wppb_rpf_add_meta_data($repeater_group, $field['meta-name'], $rpf_limit, $i);
                foreach( $indexed_repeater_group as $field ){
                    $output .= apply_filters( 'wppb_admin_output_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ), '', $form_location, $field, $user_id, $field_check_errors, $global_request );
                }
            }
        }

    }
    return apply_filters( 'wppb_'.$form_location.'_input_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $global_request, $extra_groups_count, $rpf_limit, $repeater_group, $role, $form_creator_obj );
}
add_filter( 'wppb_output_form_field_repeater', 'wppb_repeater_handler', 10, 8 );
add_filter( 'wppb_admin_output_form_field_repeater', 'wppb_repeater_handler', 10, 6 );


/**
 * Save Repeater field in Register and Edit-profile forms
 *
 * @param $field
 * @param $user_id
 * @param $request_data
 * @param $form_location
 */
function wppb_save_repeater_values($field, $user_id, $request_data, $form_location ){
    if ( $field['field'] == 'Repeater' ) {
        $repeater_group = get_option( $field['meta-name'], 'not_set' );
        if ( $repeater_group == 'not_set' ) {
            return;
        }

        $rpf_limit = 0;
        if ( apply_filters( 'wppb_rpf_enforce_limit_on_save', false, $field ) ){
            $rpf_limit = wppb_rpf_get_limit( $field, $user_id );
        }

        $extra_groups_count = esc_attr( $request_data[ $field['meta-name'] . '_extra_groups_count' ] );
        $extra_groups_count = is_numeric($extra_groups_count) ? $extra_groups_count : -1;
        /*
         * Limit is 0 if unlimited. We subtract 1 from the limit because extra_groups_count is the total number of groups submitted minus 1.
         * The limit is also enforced in the front-end.
         */
        if ( 0 < $rpf_limit && (($rpf_limit -1)  < $extra_groups_count) ) {
            $extra_groups_count = $rpf_limit - 1;
        }

        $old_extra_groups_count = get_user_meta( $user_id, $field['meta-name'] . '_extra_groups_count', true );
        $old_extra_groups_count = is_numeric($old_extra_groups_count) ? $old_extra_groups_count : 0;

        if( $extra_groups_count > -1 ) {
            update_user_meta($user_id, $field['meta-name'] . '_extra_groups_count', $extra_groups_count);
        }

        for ( $i = 0; $i <= $extra_groups_count; $i++ ){
            $indexed_repeater_group = wppb_rpf_add_meta_data($repeater_group, $field['meta-name'], $rpf_limit, $i );
            foreach( $indexed_repeater_group as $rp_field ){
                do_action( 'wppb_save_form_field', $rp_field, $user_id, $request_data, $form_location );
            }
        }

        //delete the meta in db for deleted rows
        if( $old_extra_groups_count > $extra_groups_count ){//check to see if there were deleted rows
            for( $i = $extra_groups_count +1; $i<= $old_extra_groups_count; $i++ ){
                $count_repeater_group = count ( $repeater_group );
                for ( $iterator = 0; $iterator < $count_repeater_group; $iterator++ ) {
                    delete_user_meta( $user_id, $repeater_group[$iterator]['meta-name'] . "_" . $i );
                }
            }
        }
    }
}
add_filter( 'wppb_save_form_field', 'wppb_save_repeater_values', 10, 4 );
add_filter( 'wppb_backend_save_form_field', 'wppb_save_repeater_values', 10, 4 );


/**
 * Include the repeater groups of fields in various places.
 *
 * For on User Activation, User Signup to be used for saving data.
 *
 * For edit_profile_auto_login_after_password_change, validate_backend, and validate_fronted for validating each field.
 *
 * @param array     $manage_fields  Array of fields.
 * @param array     $args           Arguments array.
 * @return array    $manage_fields  Array of fields containing the repeater groups of fields.
 */
function wppb_rpf_add_repeater_groups( $manage_fields, $args ){

    if ( !is_array($manage_fields) || empty( $args['context'] ) ||  ! in_array( $args['context'], array( 'user_activation', 'user_signup', 'edit_profile_auto_login_after_password_change', 'validate_backend', 'validate_frontend', 'mustache_variable', 'email_customizer', 'map_api_key', 'upload_helper', 'multi_step_forms' ) ) ) {
        return $manage_fields;
    }

    /* let's see if we already did this so we avoid unnecessary overload */
    foreach( $manage_fields as $check_field ){
        if( isset( $check_field['wppb-rpf-meta-data'] ) )
            return $manage_fields;
    }

    for ( $iterator = 0; $iterator < count( $manage_fields ); $iterator++ ){
        $field = $manage_fields[$iterator];
        if ( $field['field'] == "Repeater" ){
            $repeater_field = $manage_fields[$iterator];
            $repeater_group = get_option( $field['meta-name'], 'not_set' );
            if ( $repeater_group == 'not_set' ) {
                continue;
            }

            $extra_groups_count = 0;
            if ( $args['context'] == 'validate_frontend' || $args['context'] == 'multi_step_forms' ) {
                $error_for_field = apply_filters( 'wppb_check_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $repeater_field['field'] ), 'possible error message', $repeater_field, $args['global_request'], $args['form_type'], $args['role'], $args['user_id'] ) ;
                if ( $error_for_field == '' ){
                    // conditional logic functionality has cleared out Repeater errors which means it is hidden
                    continue;
                }
            }

            switch ( $args['context'] ) {
                case 'user_activation':
                    if (!empty($args['meta'][$repeater_field['meta-name'] . '_extra_groups_count'])) {
                        // $meta is not set if $extra_groups_count is 0
                        $extra_groups_count = $args['meta'][$repeater_field['meta-name'] . '_extra_groups_count'];
                    }
                    update_user_meta( $args['user_id'], $repeater_field['meta-name'] . '_extra_groups_count', $extra_groups_count );
                    break;

                case 'mustache_variable':
                    if ( !empty( $args['user_id'] ) ){
                        $extra_groups_count = get_user_meta( $args['user_id'], $repeater_field['meta-name'] . '_extra_groups_count', true );
                    }else if (!empty($args['meta'][$repeater_field['meta-name'] . '_extra_groups_count'])) {
                        $extra_groups_count = $args['meta'][$repeater_field['meta-name'] . '_extra_groups_count'];
                    }
                    break;

                case 'upload_helper':
                    $extra_groups_count = wppb_rpf_get_field_count( $args['upload_meta_name'], $repeater_group );
                    break;

                case 'multi_step_forms':
                    $extra_groups_count = sanitize_text_field( $args['extra_groups_count'] );
                    break;

                default:
                    if ( !empty( $_REQUEST[ $repeater_field['meta-name'] . '_extra_groups_count' ]) ) {
                        $extra_groups_count = sanitize_text_field( $_REQUEST[$repeater_field['meta-name'] . '_extra_groups_count'] );
                    }
            }

            if( is_numeric($extra_groups_count) ) {
                for ($i = $extra_groups_count; $i >= 0; $i--) {
                    array_splice($manage_fields, $iterator + 1, 0, wppb_rpf_add_meta_data($repeater_group, $repeater_field['meta-name'], 0, $i)); //limit is unnecessary at this stage
                }
            }
            $manage_fields = array_values( $manage_fields );
        }
    }

    return $manage_fields;
}
add_filter( 'wppb_form_fields', 'wppb_rpf_add_repeater_groups', 10, 2 );
add_filter( 'wppb_change_form_fields', 'wppb_rpf_add_repeater_groups', 20, 2 );


/**
 * Function that determines if we have at least a repeater field in Form Fields
 */
function wppb_rpf_repeaters_exist(){
    global $wppb_manage_fields;
    if( !isset( $wppb_manage_fields ) )
        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    if( is_array($wppb_manage_fields) ){
        foreach ($wppb_manage_fields as $field){
            if ( $field['field'] == 'Repeater' ){
                return true;
            }
        }
    }

    return false;
}


/**
 * Add extra_groups_count meta in the user signup array
 *
 * Extra groups count refers to the number of extra entries of a repeater field. In other words, the total number of repeater groups - 1.
 *
 * @param array $meta
 * @param array $global_request
 * @param string $role
 * @return array $meta
 */
function wppb_rpf_add_to_user_signup_repeater_count( $meta, $global_request, $role ){
    $manage_fields = get_option( 'wppb_manage_fields', 'not_set' );
    if ( $manage_fields != 'not_set' ){
        foreach ( $manage_fields as $field ){
            if ( $field['field'] == 'Repeater' ){
                if ( isset($global_request[$field['meta-name'] . '_extra_groups_count'] ) ){
                    $rpf_limit = 0;
                    if ( apply_filters( 'wppb_rpf_enforce_limit_on_email_confirmation', false, $field ) ){
                        $rpf_limit = wppb_rpf_get_limit( $field, '', $role );
                    }

                    $extra_groups_count = esc_attr( $global_request[$field['meta-name'] . '_extra_groups_count'] );

                    /*
                     * Limit is 0 if unlimited. We subtract 1 from the limit because extra_groups_count is the total number of groups submitted minus 1.
                     * The limit is also enforced in the front-end.
                     */
                    if ( 0 < $rpf_limit && (($rpf_limit - 1) < $extra_groups_count ) ) {
                        $extra_groups_count = $rpf_limit - 1;
                    }

                    $meta[$field['meta-name'] . '_extra_groups_count' ] = $extra_groups_count;

                }
                unset($meta[$field['meta-name']]);
            }
        }
    }
    return $meta;
}
add_filter( 'wppb_add_to_user_signup_form_meta', 'wppb_rpf_add_to_user_signup_repeater_count', 10, 3 );


/**
 * Add the meta-data to the <li> tag of the Repeater field.
 *
 * The information: data-wppb-rpf-meta-name, data-wppb-rpf-limit, data-wppb-rpf-remove-group-message
 *
 * @param $li_element
 * @param $field
 * @param $error_var
 * @param $role
 * @return mixed
 */
function wppb_rpf_add_attribute_data( $li_element, $field, $error_var, $role ){
    if ( $field['field'] == 'Repeater' ){
        $user_id = wppb_rpf_get_desired_user_id();
        $rpf_limit = wppb_rpf_get_limit( $field, $user_id, $role );
        $rpf_limit_rules = wppb_rpf_limit_rules( $field );
        $rpf_pms_value_roles = wppb_rpf_get_pms_role_subscription_ids();
        $rpf_general_limit = $field['rpf-limit'];

        $remove_repeater_group_message = apply_filters( 'wppb_rpf_remove_repeater_group_message', __( 'Are you sure you want to delete this?' ,'profile-builder' ), $field, $user_id, $rpf_limit );
        return str_replace( '>', ' data-wppb-rpf-meta-name="' . $field['meta-name'] . '" data-wppb-rpf-limit="' . $rpf_limit . '" data-wppb-rpf-limit-rules=\'' . $rpf_limit_rules . '\' data-wppb-rpf-pms-role-subscription-ids=\'' . $rpf_pms_value_roles . '\' data-wppb-rpf-general-limit=\'' . $rpf_general_limit . '\' data-wppb-rpf-remove-group-message="' . $remove_repeater_group_message . '" >', $li_element );
    }

    return $li_element;
}
add_filter( 'wppb_output_before_form_field', 'wppb_rpf_add_attribute_data', 10, 4 );


/**
 * Enqueues the necessary scripts in the front end area
 *
 */
function wppb_rpf_scripts_front_end() {
    global $wppb_shortcode_on_front;
    if( !empty( $wppb_shortcode_on_front ) && $wppb_shortcode_on_front === true ) {
        if( wppb_rpf_repeaters_exist() ) {
            wp_enqueue_script('wppb-rpf-front-end-js', WPPB_PAID_PLUGIN_URL . 'add-ons/repeater-field/assets/js/wppb-rpf-front-end.js', array('jquery'), PROFILE_BUILDER_VERSION, true);
        }
    }
}
add_action( 'wp_footer', 'wppb_rpf_scripts_front_end' );


/**
 * Enqueues the necessary styles in the front end area
 *
 */
function wppb_rpf_styles_front_end(){
    if( wppb_rpf_repeaters_exist() ) {
        wp_enqueue_style('wppb-rpf-front-end-css', WPPB_PAID_PLUGIN_URL . 'add-ons/repeater-field/assets/css/wppb-rpf-front-end.css', false, PROFILE_BUILDER_VERSION);
    }
}
add_action( 'wp_enqueue_scripts', 'wppb_rpf_styles_front_end' );


/**
 * Removes empty appended fields
 *
 * Clears the $_REQUEST array of empty appended groups and returns the corrected number of groups.
 *
 * @param $global_request
 * @param $args
 */
function wppb_rpf_remove_empty_appended_fields( $global_request, $args ) {
    if ( !empty( $args['form_fields'] ) ) {
        foreach ($args['form_fields'] as $field) {
            if ($field['field'] == "Repeater") {
                $repeater_group = get_option( $field['meta-name'], 'not_set' );
                if ( $repeater_group == 'not_set' ) {
                    continue;
                }
                $global_request = wppb_rpf_remove_empty_appended_fields_for_repeater( $field['meta-name'],$repeater_group, count($repeater_group), $global_request );
            }
        }
    }

    $_REQUEST = $global_request;
}
add_action ( 'wppb_before_saving_form_values', 'wppb_rpf_remove_empty_appended_fields', 9, 2 );


/**
 * Include the meta_key for each field part of the repeater groups.
 *
 * Used for the userlisting search all extra fields.
 *
 * It includes all the incremented meta keys, only if there is data stored in them.
 *
 * @param $user_meta_keys
 * @param $wppb_manage_fields
 * @param $wppb_exclude_search_fields
 * @param $searchText
 * @param $args
 * @return array
 */
function wppb_rpf_add_user_meta_keys( $user_meta_keys, $wppb_manage_fields, $wppb_exclude_search_fields, $searchText, $args ){
    global $wpdb;
    foreach( $wppb_manage_fields as $rpf_field ){
        if ($rpf_field['field'] == "Repeater") {
            if( in_array($rpf_field['meta-name'] , $wppb_exclude_search_fields ) ) {
                continue;
            }
            foreach( $user_meta_keys as $key => $value ){
                if ( $value == $rpf_field['meta-name'] ){
                    unset( $user_meta_keys[$key]);
                    $user_meta_keys = array_values($user_meta_keys);
                    break;
                }
            }
            $repeater_group = get_option($rpf_field['meta-name'], 'not_set');
            if ($repeater_group == 'not_set') {
                continue;
            }
            foreach ($repeater_group as $field) {
                $meta_name_underlined = $field['meta-name'];

                $results = $wpdb->get_results("SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE '{$meta_name_underlined}%' AND meta_value != ''", ARRAY_N);
                foreach ( $results as $result ){
                    if ( $result[0] == $field['meta-name']){
                        $user_meta_keys[] = $result[0];
                    }else{
                        $pattern = '/^' . $field['meta-name'] . '_[0-9]+$/';
                        preg_match( $pattern, $result[0], $matches );
                        if ( count ($matches) > 0 ) {
                            $user_meta_keys[] = $result[0];
                        }
                    }
                }
            }
        }
    }

    return $user_meta_keys;
}
add_filter( 'wppb_userlisting_search_in_user_meta_keys', 'wppb_rpf_add_user_meta_keys', 10, 5 );


/**
 * Exclude the Repeater field from the Default Sorting for Userlisting and from the Facet Menu settings
 *
 * @param $fields
 * @return array
 */
function wppb_rpf_exclude_repeater_from_facetlist( $fields ){
    $fields[] = 'Repeater';
    return $fields;
}
add_filter( 'wppb_exclude_field_list_userlisting_facet_menu_settings', 'wppb_rpf_exclude_repeater_from_facetlist' );
add_filter( 'wppb_exclude_field_list_userlisting_settings', 'wppb_rpf_exclude_repeater_from_facetlist' );


add_filter( 'wppb_rpf_enforce_limit_on_save', 'wppb_rpf_pms_enforce_limit_on_save' );


/**
 * Enforce RPF limit on save if PMS is disabled
 *
 * @param $enforce_limit
 * @return bool
 */
function wppb_rpf_pms_enforce_limit_on_save ( $enforce_limit ){
    /*
     * If PMS is enabled, leave the limit 0, so all the repeater fields are saved. The limit is then enforced only on display.
     * User roles are set by PMS only after the user becomes a member, so we should not enforce any limit at this point.
    */
    if (!function_exists('pms_get_subscription_plans')) {
        /*
         * Enforce the limit on save because PMS is disabled.
         */
        return true;
    }
}
