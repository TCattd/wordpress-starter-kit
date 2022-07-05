<?php

/**
 * Add one group of fields for each Repeater field so that they get processed tags.
 * Used for User Listing and Email Customizer
 *
 * @param $manage_fields
 * @param string $type
 * @return array
 */
function wppb_rpf_include_rpf_fields( $manage_fields, $type = '' ){
    if ( empty( $manage_fields ) ) {
        return $manage_fields;
    }
    $repeater_groups = array();
    $manage_fields = array_values( $manage_fields );
    foreach( $manage_fields as $key => $value ){
        if ( $manage_fields[$key]['field'] == "Repeater" ){
            $repeater_group = get_option( $manage_fields[$key]['meta-name'], 'not_set' );
            if ( $type == 'sort' ){
                unset ($manage_fields[$key]);
            }
            if ( $repeater_group != 'not_set' ){
                $repeater_groups = array_merge ( $repeater_groups, $repeater_group );
            }else{
                unset($manage_fields[$key]);
            }
        }
    }
    $manage_fields = array_merge( $manage_fields, $repeater_groups );
    return $manage_fields;
}
add_filter( 'wppb_userlisting_merge_tags','wppb_rpf_include_rpf_fields', 10, 2 );
add_filter( 'wppb_email_customizer_get_fields','wppb_rpf_include_rpf_fields', 10 );



/**
 * Add one group of fields for each Repeater field so that they get processed SORT tags.
 * Used for User Listing and Email Customizer
 *
 * @param $manage_fields
 * @return array
 */
function wppb_rpf_sort_change_form_fields( $manage_fields ){
    return wppb_rpf_include_rpf_fields( $manage_fields, $type = 'sort' );
}
add_filter( 'wppb_sort_change_form_fields', 'wppb_rpf_sort_change_form_fields', 10 );


/**
 * Call the function to merge Userlisting tags while specifying meta prefix
 *
 * @param $merge_tags
 * @return array
 */
function wppb_rpf_userlisting_get_merge_tags( $merge_tags ){
    return wppb_rpf_merge_tags( $merge_tags, 'meta_' );
}
add_filter( 'wppb_userlisting_get_merge_tags', 'wppb_rpf_userlisting_get_merge_tags' );


/**
 * Call the function to merge Email Customizer tags
 *
 * @param $merge_tags
 * @return array
 */
function wppb_rpf_email_customizer_get_merge_tags( $merge_tags ){
    return wppb_rpf_merge_tags( $merge_tags );
}
add_filter( 'wppb_email_customizer_get_merge_tags', 'wppb_rpf_email_customizer_get_merge_tags' );


/**
 * Move the repeater groups inside the children attribute of the repeater fields
 *
 * @param $merge_tags
 * @param string $tag_prefix
 * @return array
 */
function wppb_rpf_merge_tags( $merge_tags, $tag_prefix = '' ){

    $rpf_group_meta_names = wppb_rpf_get_all_rpf_meta_names( $tag_prefix );
    if ( empty ( $rpf_group_meta_names ) ) {
        return $merge_tags;
    }
    for ( $key = 0; $key < count($merge_tags); $key++ ){
        if ( empty ($merge_tags[$key]) ){
            continue;
        }
        if( $merge_tags[$key]['type'] == 'sort_tag' &&  !( strpos( $merge_tags[$key]['name'], 'sort_wppb_repeater_field' ) === false )  ){
            unset ( $merge_tags[$key] );
            continue;
        }
        if ( !( strpos( $merge_tags[$key]['name'], $tag_prefix . 'wppb_repeater_field' ) === false ) ){
            $rpf_meta_name = preg_replace("/$tag_prefix/", '', $merge_tags[$key]['name'], 1 );
            if ( empty ( $rpf_group_meta_names[$rpf_meta_name] ) ) {
                continue;
            }
            $merge_tags[$key]['type'] = 'loop_tag';
            $merge_tags[$key]['name'] = $rpf_group_meta_names[$rpf_meta_name]['title-slug'];
            $merge_tags = wppb_rpf_move_children_inside_loop_tag( $merge_tags, $key, $rpf_group_meta_names[$rpf_meta_name]['children'], $tag_prefix, $rpf_meta_name );
        }
    }
    $merge_tags = array_values( $merge_tags );
    return $merge_tags;
}


/**
 * Move the repeater group inside the children attribute of the repeater field
 *
 * @param $merge_tags
 * @param $rpf_key
 * @param $rpf_group_meta_names
 * @param $tag_prefix
 * @param $rpf_meta_name
 * @return mixed
 */
function wppb_rpf_move_children_inside_loop_tag( $merge_tags, $rpf_key, $rpf_group_meta_names, $tag_prefix, $rpf_meta_name ){

    foreach ( $rpf_group_meta_names as $meta_name ){
        foreach ( $merge_tags as $key => $value ){
            if ( preg_replace("/$tag_prefix/", '', $merge_tags[$key]['name'], 1 ) == $meta_name && $key !== $rpf_key) {
                $merge_tags[$key]['rpf_meta_name'] = $rpf_meta_name;
                $merge_tags[$rpf_key]['children'][] = $merge_tags[$key];
                unset ( $merge_tags[$key] );
                break;
            }
        }
    }

    return $merge_tags;
}


/**
 * Returns an array of all meta-names of the group of fields for every Repeater field
 *
 * @param $tag_prefix
 * @return array
 */
function wppb_rpf_get_all_rpf_meta_names( $tag_prefix ) {
    $manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
    if ( empty( $manage_fields ) ) {
        return array();
    }

    $rpf_group_meta_names = array();
    foreach( $manage_fields as $field ) {
        if ( $field['field'] == "Repeater" ) {
            $repeater_group = get_option( $field['meta-name'], 'not_set' );
            if ( $repeater_group != 'not_set' && count($repeater_group) != 0) {
                $rpf_group_meta_names[$field['meta-name']]['title-slug'] = $tag_prefix . Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field-title'] );

                foreach ($repeater_group as $rpf_field) {
                    $rpf_group_meta_names[$field['meta-name']]['children'][] = $rpf_field['meta-name'];
                    $rpf_group_meta_names[$field['meta-name']]['children'][] = $rpf_field['meta-name'] . '_labels';
                    if ( $rpf_field['field'] === 'Upload' ) {
                        $rpf_group_meta_names[$field['meta-name']]['children'][] = $rpf_field['meta-name'] . '_id';
                    }
                }
            }
        }
    }
    return $rpf_group_meta_names;

}


/**
 * Process Repeater field loop tag for User Listing and Email Customizer
 *
 * Calls each children's mustache variable with the indexed meta-names
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_info
 * @return array
 */
function wppb_rpf_user_meta_repeater( $value, $name, $children, $extra_info ) {
    if ( empty ( $children[0]['rpf_meta_name'] ) ){
        return $value;
    }else{
        $rpf_meta_name = $children[0]['rpf_meta_name'];
    }

    $user_id = (!empty($extra_info['user_id'])) ? $extra_info['user_id'] : wppb_get_query_var('username');

    $extra_groups_count = 0;
    if ( !empty ( $extra_info['email_confirmation_unserialized_data'] ) ) {
        if ( ! empty ( $extra_info['email_confirmation_unserialized_data'][ $rpf_meta_name . '_extra_groups_count' ] ) ) {
            $extra_groups_count = $extra_info['email_confirmation_unserialized_data'][ $rpf_meta_name . '_extra_groups_count' ];
        }
    }else{
        $user_meta = get_user_meta( $user_id, $rpf_meta_name . '_extra_groups_count', true );
        if( $user_meta != false ) {
            $extra_groups_count = $user_meta;
        }
    }

    /*
     * Limit is 0 if unlimited. We subtract 1 from the limit because extra_groups_count is the total number of groups submitted minus 1.
     * The limit is also enforced in the front-end.
     */
    $rpf_limit = wppb_rpf_get_limit( wppb_get_field_by_id_or_meta( $rpf_meta_name ), $user_id );
    if ( 0 < $rpf_limit && (($rpf_limit -1)  < $extra_groups_count) ) {
        $extra_groups_count = $rpf_limit - 1;
    }

    $children_vals = array();
    $tag_prefix = '';
    if ( !empty( $extra_info['userlisting_form_id'] ) ){
        $tag_prefix = 'meta_';
    }

    for( $i = 0; $i <= $extra_groups_count; $i++ ){
        foreach ($children as $child) {
            $child_meta_name = preg_replace( '/_labels$/', '', $child['name'], 1 );
            if ( $child_meta_name == $child['name'] ) {
                $child_meta_name = ($i == 0) ? $child_meta_name : $child_meta_name . "_" . $i;
            }else{
                $child_meta_name = ($i == 0) ? $child_meta_name : $child_meta_name . "_" . $i . '_labels';
            }

            $child_meta_name =  preg_replace("/$tag_prefix/", '', $child_meta_name, 1);
            $children_vals[$i][$child['name']] = apply_filters('mustache_variable_' . $child['type'], '', $child_meta_name, array(), $extra_info);
        }
    }

    return $children_vals;
}
add_filter( 'mustache_variable_loop_tag', 'wppb_rpf_user_meta_repeater', 20, 4 );


/* BuddyPress integration */
function wppb_rpf_inner_field_visibility_options( $field, $field_meta_name, $displayed_user_id, $current_user_id ){
    if ( empty ( $field ) ) {
        // includes Repeater subfields
        $manage_fields = apply_filters('wppb_form_fields', get_option('wppb_manage_fields'), array('context' => 'mustache_variable', 'user_id' => $displayed_user_id ) );
        foreach ( $manage_fields as $manage_field ) {
            if ($manage_field['meta-name'] == $field_meta_name) {
                $field = $manage_field;
                if ( !empty ( $field['wppb-rpf-meta-data']['wppb-rpf-meta-name'] ) ){
                    $rp_field = wppb_get_field_by_id_or_meta ( $field['wppb-rpf-meta-data']['wppb-rpf-meta-name'] );
                    // parent Repeater field will only be used for getting the BuddyPress settings
                    return $rp_field;
                }
            }
        }
    }
    return $field;
}
add_filter( 'wppb_bdp_field_for_visibility', 'wppb_rpf_inner_field_visibility_options', 10, 4 );