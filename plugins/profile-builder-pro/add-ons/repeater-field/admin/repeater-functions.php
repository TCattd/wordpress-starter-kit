<?php

/**
 * Get the repeater field limit
 *
 * @param $repeater_field
 * @param $user_id
 * @param string $role
 * @return mixed|void
 */
function wppb_rpf_get_limit( $repeater_field, $user_id, $role = '' ){
    $limit = 0;

    if ( isset ( $repeater_field['rpf-enable-limit'] ) && $repeater_field['rpf-enable-limit'] == 'yes' ) {
        if ( ! empty ($role) ) {
            // we are on register form. this is the role being set to the user
            $limit = wppb_rpf_get_limit_for_role($repeater_field, array($role));
        }else if ( !empty ( $user_id) ) {
            // edit profile form or user activation on email confirmation
            $user = new WP_User($user_id);
            if (!empty($user->roles) && is_array($user->roles)) {
                $limit = wppb_rpf_get_limit_for_role($repeater_field, $user->roles);
            }
        }
    }

    return apply_filters('wppb_rpf_limit', $limit, $repeater_field, $user_id, $role );
}


/**
 * Get the repeater field limit for a specific role
 *
 * @param $repeater_field
 * @param $roles
 * @return int
 */
function wppb_rpf_get_limit_for_role( $repeater_field, $roles ) {
    $limit = 0;
    if ( !empty( $repeater_field['rpf-role-limit'] ) ) {
        $option = json_decode($repeater_field['rpf-role-limit'], true);
    }

    if ( !empty( $option['rules'] ) ){
        foreach ( $roles as $role) {
            foreach ($option['rules'] as $rule) {
                if ( $role == $rule['role'] ) {
                    if (empty ($rule['value']) || $rule['value'] == '0') {
                        // if we find a specific rule that guarantees unlimited fields for a role then there's no point in further checking
                        return 0;
                    } else if ( $limit < intval( $rule['value'] ) ) {
                        $limit = $rule['value'];
                    }
                }
            }
        }
    }

    // $limit is 0 if no specific rules were found above.
    if ( !empty ( $repeater_field['rpf-limit'] ) && $limit == 0 ) {
        $limit = intval ( $repeater_field['rpf-limit'] );
    }

    return $limit;
}


/**
 * Removes empty appended fields
 *
 * Clears the $_REQUEST array of empty appended groups and returns the corrected number of groups.
 *
 * @param string                    $rpf_meta_name              Repeater field meta-name
 * @param array                     $repeater_group             Array of fields of the current Repeater field. Fields do not have indexed meta-names.
 * @param int                       $count_repeater_group       Number of fields contained in the $repeater group.
 * @param array                     $request_data               Request data array.
 * @return array                    $request_data               Request array cleared of empty repeater groups
 */
function wppb_rpf_remove_empty_appended_fields_for_repeater( $rpf_meta_name, $repeater_group, $count_repeater_group, $request_data ){
    $number_of_extra_groups_needed = intval( esc_attr( $request_data[ $rpf_meta_name . '_extra_groups_count' ] ) ) ;
    $meta_name_iterator = $number_of_extra_groups_needed;
    $stop = false;
    while( !$stop ){
        if ( $meta_name_iterator == 0 ){
            break;
        }
        for ( $iterator = 0; $iterator < $count_repeater_group; $iterator++ ) {
            $field = $repeater_group[$iterator];
            if ( $field[ 'field' ] == 'Upload' && isset( $field[ 'simple-upload' ] ) && $field[ 'simple-upload' ] == 'yes' ){
                if ( ( ( isset( $_FILES[ 'simple_upload_' . $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) && isset( $_FILES[ 'simple_upload_' . $field[ 'meta-name' ] . "_" . $meta_name_iterator ]['size'] ) && $_FILES[ 'simple_upload_' . $field[ 'meta-name' ] . "_" . $meta_name_iterator ][ 'size' ] > 0 ) || ( isset( $request_data[ $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) && !empty ( $request_data[ $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) ) ) ||
                    ( wppb_belongs_to_repeater_with_conditional_logic( $field ) && ( !isset( $request_data[ $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) && ( ( !isset( $_FILES[ 'simple_upload_' . $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) || ( isset( $_FILES[ 'simple_upload_' . $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) && $_FILES[ 'simple_upload_' . $field[ 'meta-name' ] . "_" . $meta_name_iterator ][ 'size' ] == 0 ) ) ) ) ) ){
                    $stop = true;
                    break;
                }
            }
            else{
                if ( ! ( isset( $request_data[ $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) && empty ( $request_data[ $field[ 'meta-name' ] . "_" . $meta_name_iterator ] ) ) ) {
                    $stop = true;
                    break;
                }
            }
        }

        if ( ! $stop ){
            for ( $iterator = 0; $iterator < $count_repeater_group; $iterator++ ) {
                $field = $repeater_group[$iterator];
                unset( $request_data[ $field[ 'meta-name' ] . "_" . $meta_name_iterator ] );
            }
            $number_of_extra_groups_needed --;
            $meta_name_iterator--;
        }
    }
    $request_data[ $rpf_meta_name . '_extra_groups_count' ] = $number_of_extra_groups_needed;
    return $request_data;
}


/**
 * Get the current user ID
 *
 * @return int      $user_id    User Id
 */
function wppb_rpf_get_desired_user_id( ){

    if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
        if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) ){
            return absint( $_GET['edit_user'] );
        }
    }
    return get_current_user_id();
}


/**
 * Index group of field and add meta-data
 *
 * @param array     $repeater_group     Group of unindexed fields
 * @param string    $rpf_meta_name      Repeater field meta-name
 * @param int       $rpf_limit          The limit for this repeater group
 * @param int       $rpf_limit          Set number to add.
 * @return array    $repeater_group     Group of indexed fields
 */
function wppb_rpf_add_meta_data( $repeater_group, $rpf_meta_name, $rpf_limit, $set_number ){
    $count_repeater_group = count ( $repeater_group );

    for ( $iterator = 0; $iterator < $count_repeater_group; $iterator++ ) {

        // 'wppb-rpf-meta-data' array is not actively used, but it may be useful on further use
        $repeater_group[$iterator]['wppb-rpf-meta-data']['wppb-rpf-meta-name'] = $rpf_meta_name;
        $repeater_group[$iterator]['wppb-rpf-meta-data']['wppb-rpf-set-number'] = $set_number;
        $repeater_group[$iterator]['wppb-rpf-meta-data']['wppb-rpf-limit'] = $rpf_limit;

        // template set
        if ( $set_number == '-1' ) {
            // Meta names with the suffix _0 are not saved in the database
            $repeater_group[$iterator]['meta-name'] = $repeater_group[$iterator]['meta-name'] . "_0";
            $repeater_group[$iterator]['id'] = $repeater_group[$iterator]['id'] . "_0";
            $repeater_group[$iterator]['wppb-rpf-meta-data']['wppb-rpf-set-number'] = 'template';
        }else if ( $set_number > 0 ) {
            $repeater_group[$iterator]['meta-name'] = $repeater_group[$iterator]['meta-name'] . "_" . $set_number;
            $repeater_group[$iterator]['id'] = $repeater_group[$iterator]['id'] . "_" . $set_number;
        }

        $repeater_group[$iterator] = apply_filters( 'wppb_rpf_add_extra_meta_data', $repeater_group[$iterator], $iterator, $rpf_meta_name, $rpf_limit, $set_number );
    }

    return $repeater_group;
}


/**
 * Return the increment of the sub-field
 *
 * @param $meta_name            Meta-name of subfield
 * @param $repeater_group       Repeater Group option
 * @return int|string           Increment number of the meta-name.
 */
function wppb_rpf_get_field_count( $meta_name, $repeater_group ){
    foreach( $repeater_group as $field ){
        $pattern = '/^' . $field['meta-name'] . '_[0-9]+$/';
        preg_match( $pattern, $meta_name, $matches );
        if ( count ($matches) > 0 ) {
            $start = strrpos($meta_name, '_' );
            $increment = substr( $meta_name, $start + 1 );
            return $increment;
        }
    }

    return 0;
}

/*
 * Return array of rules per field
 *
 * @param $field Repeater field
 * @return array JSON Array with limit and roles
 */
function wppb_rpf_limit_rules( $field ){
    if ( $field['rpf-enable-limit'] == 'yes' &&  !empty ( $field['rpf-role-limit'] ) ) {
        return $field['rpf-role-limit'];
    }else{
        return '';
    }

}


/**
 * Return array of role and PMS subscription IDs
 *
 * @param $field Repeater field
 * @return array JSON Array with limit and roles
 */
function wppb_rpf_get_pms_role_subscription_ids(){
    $role_subscription_ids = array();
    if ( function_exists( 'pms_get_subscription_plans' ) ){
        $subscription_plans = pms_get_subscription_plans();
        $i = 0;
        foreach ( $subscription_plans as $plan ){
            $role_subscription_ids[$i]['role'] = $plan->user_role;
            $role_subscription_ids[$i]['subscription_id'] = $plan->id;
            $i++;
        }
        return json_encode($role_subscription_ids);
    }else{
        return '';
    }
}