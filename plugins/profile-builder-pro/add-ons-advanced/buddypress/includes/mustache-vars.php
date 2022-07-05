<?php

/**
 * Add BuddyPress user profile info as mustache tags to User Listing
 *
 * @since 1.0.0
 *
 * @param $merge_tags
 * @param $type
 *
 * @return array
 */
function wppb_in_bdp_add_mustache_vars_to_userlisting( $merge_tags, $type ){
    if ( $type == 'meta' ){
        $merge_tags[] = array( 'name' => 'bp_avatar', 'type' => 'bp_avatar', 'unescaped' => true, 'label' => __( 'BuddyPress Avatar', 'profile-builder' ) );
        $merge_tags[] = array( 'name' => 'bp_cover_photo', 'type' => 'bp_cover_photo', 'unescaped' => true, 'label' => __( 'BuddyPress Cover Photo', 'profile-builder' ) );
        $merge_tags[] = array( 'name' => 'bp_last_active', 'type' => 'bp_last_active', 'label' => __( 'BuddyPress Last Active', 'profile-builder' ) );

        if ( function_exists ( 'bp_is_active' ) ){
            if ( bp_is_active( 'friends' ) ) {
                $merge_tags[] = array( 'name' => 'bp_add_friend', 'type' => 'bp_add_friend', 'unescaped' => true, 'label' => __('BuddyPress Add Friend Button', 'profile-builder' ) );
            }
            if ( bp_is_active( 'activity' ) ) {
                $merge_tags[] = array( 'name' => 'bp_latest_update', 'type' => 'bp_latest_update', 'unescaped' => true, 'label' => __('BuddyPress Latest Update', 'profile-builder' ) );
            }
        }
    }
    if ( $type == 'sort' ) {
        $merge_tags[] = array('name' => 'sort_bp_last_active', 'type' => 'sort_tag', 'unescaped' => true, 'label' => __('BuddyPress Last Active', 'profile-builder'));
    }

    return $merge_tags;
}
add_filter( 'wppb_userlisting_get_merge_tags', 'wppb_in_bdp_add_mustache_vars_to_userlisting', 10, 2);  //add tags to User Listing


/**
 * Add BuddyPress user profile info as mustache tags to Email Customizer
 *
 * @since 1.0.0
 *
 * @param $merge_tags
 *
 * @return array
 */
function wppb_in_bdp_add_mustache_vars_to_email_customizer( $merge_tags ){
    $merge_tags[] = array( 'name' => 'bp_avatar', 'type' => 'bp_avatar', 'unescaped' => true, 'label' => __( 'BuddyPress Avatar', 'profile-builder' ) );
    $merge_tags[] = array( 'name' => 'bp_cover_photo', 'type' => 'bp_cover_photo', 'unescaped' => true, 'label' => __( 'BuddyPress Cover Photo', 'profile-builder' ) );
    return $merge_tags;
}
add_filter( 'wppb_email_customizer_get_merge_tags', 'wppb_in_bdp_add_mustache_vars_to_email_customizer' ); //add tags to Email Customizer


/**
 * Display content in User Listing & Email Customizer for "BuddyPress Avatar" user tag
 *
 * @since 1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_values
 *
 * @return string
 */
function wppb_in_bdp_handle_tag_bp_avatar( $value, $name, $children, $extra_values){
    if ( function_exists('bp_core_fetch_avatar') && function_exists( 'bp_displayed_user_id' ) ){
        $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : bp_displayed_user_id() );
        return bp_core_fetch_avatar( array ( 'item_id' => $user_id, 'html' => false, 'type' => 'full' ) );
    }
}
add_filter( 'mustache_variable_bp_avatar', 'wppb_in_bdp_handle_tag_bp_avatar', 10, 4 );


/**
 * Display content in User Listing & Email Customizer for "BuddyPress Cover Photo" user tag
 *
 * @since 1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_values
 *
 * @return string
 */
function wppb_in_bdp_handle_tag_bp_cover_photo( $value, $name, $children, $extra_values){
    if ( function_exists( 'bp_attachments_get_attachment' ) && function_exists( 'bp_displayed_user_id' ) ){
        $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : bp_displayed_user_id() );
        return bp_attachments_get_attachment( 'url', array( 'item_id' => $user_id ) );
    }
}
add_filter( 'mustache_variable_bp_cover_photo', 'wppb_in_bdp_handle_tag_bp_cover_photo', 10, 4 );


/**
 * Display content in User Listing & Email Customizer for "BuddyPress Friend Button" user tag
 *
 * @since 1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_values
 *
 * @return string
 */
function wppb_in_bdp_handle_tag_bp_add_friend( $value, $name, $children, $extra_values){
    if ( function_exists( 'bp_get_add_friend_button' ) && function_exists( 'bp_displayed_user_id' ) ){
        $user_id = (!empty($extra_values['user_id']) ? $extra_values['user_id'] : bp_displayed_user_id());
        return bp_get_add_friend_button($user_id);
    }
}
add_filter( 'mustache_variable_bp_add_friend', 'wppb_in_bdp_handle_tag_bp_add_friend', 10, 4 );


/**
 * Display content in User Listing & Email Customizer for "BuddyPress Last Activity" user tag
 *
 * @since 1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_values
 *
 * @return string
 */
function wppb_in_bdp_handle_tag_bp_last_active( $value, $name, $children, $extra_values){
    if ( function_exists( 'bp_displayed_user_id' ) ){
        $user_id = (!empty($extra_values['user_id']) ? $extra_values['user_id'] : bp_displayed_user_id() );
        $last_activity = get_user_meta($user_id, 'wppb_bdp_last_activity', true);
        if (empty ($last_activity)) {
            return __('Never active', 'profile-builder');
        } else {
            if (function_exists('bp_core_time_since')) {
                return bp_core_time_since($last_activity);
            } else {
                return $last_activity;
            }
        }
    }
}
add_filter( 'mustache_variable_bp_last_active', 'wppb_in_bdp_handle_tag_bp_last_active', 10, 4 );


/**
 * Display content in User Listing & Email Customizer for "BuddyPress Latest Update" user tag
 *
 * @since 1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_values
 *
 * @return string
 */
function wppb_in_bdp_handle_tag_bp_latest_update( $value, $name, $children, $extra_values){
    if ( function_exists( 'bp_displayed_user_id' ) ) {
        $user_id = (!empty($extra_values['user_id']) ? $extra_values['user_id'] : bp_displayed_user_id());
        return wppb_in_bdp_get_member_latest_update($user_id);
    }
}
add_filter( 'mustache_variable_bp_latest_update', 'wppb_in_bdp_handle_tag_bp_latest_update', 10, 4 );


/**
 * Get the latest update for $user_id
 *
 * @since 1.0.0
 *
 * @param $user_id
 *
 * @return false|string
 */
function wppb_in_bdp_get_member_latest_update( $user_id ) {
    $args = apply_filters( 'wppb_bdp_latest_update_args', array(
        'length'    => 225,
        'view_link' => true
    ) );


    $latest_update = get_user_meta( $user_id, 'bp_latest_update', true );
    if ( !function_exists( 'bp_is_active' ) || !bp_is_active( 'activity' ) || !function_exists( 'bp_create_excerpt' ) || !function_exists ( 'bp_activity_get_permalink' ) || empty( $latest_update) || !$update = maybe_unserialize( $latest_update )){
        return false;
    }

    $update_content = apply_filters( 'bp_get_activity_latest_update_excerpt', trim( strip_tags( bp_create_excerpt( $update['content'], $args['length'] ) ) ), $args );
    $update_content = sprintf( _x( ' - &quot;%s&quot;', 'member latest update in member directory', 'buddypress' ), $update_content ); //phpcs:ignore

    // If $view_link is true and the text returned by bp_create_excerpt() is different from the original text (ie it's
    // been truncated), add the "View" link.
    if ( $args['view_link'] && ( $update_content != $latest_update['content'] ) ) {
        $view = __( 'View', 'buddypress' ); //phpcs:ignore

        $update_content .= '<span class="wppb-bdp-activity-read-more"><a href="' . bp_activity_get_permalink( $latest_update['id'] ) . '" rel="nofollow">' . $view . '</a></span>';
    }
    return apply_filters( 'bp_get_member_latest_update', $update_content, $args );
}


/**
 * Function that returns the link for BuddyPress profile
 *
 * @since v.1.0.0
 *
 * @param $link
 * @param $url
 * @param $user_info
 *
 * @return string
 */
function wppb_in_bdp_userlisting_more_info_link_to_bp_profile( $link, $url, $user_info ){
    if ( function_exists( 'bp_core_get_user_domain' ) ) {
        return bp_core_get_user_domain($user_info->ID);
    }
    return $link;
}
add_filter( 'wppb_userlisting_more_info_link_structure1', 'wppb_in_bdp_userlisting_more_info_link_to_bp_profile', 20, 3 );
add_filter( 'wppb_userlisting_more_info_link_structure2', 'wppb_in_bdp_userlisting_more_info_link_to_bp_profile', 20, 3 );
add_filter( 'wppb_userlisting_more_info_link_structure3', 'wppb_in_bdp_userlisting_more_info_link_to_bp_profile', 20, 3 );


/**
 * Returns the sort tag for last active
 *
 * @since v.1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_info
 *
 * @return string
 */
function wppb_in_bdp_sort_last_active( $value, $name, $children, $extra_info ){
    if ( $name == 'sort_bp_last_active' && function_exists( 'wppb_get_new_url' ) ) {
        return '<a href="' . wppb_get_new_url('wppb_bdp_last_activity', $extra_info) . '" class="sortLink" id="sortLink' . 'BpLa' . '">' . __( 'Last Active', 'profile-builder' ) . '</a>';
    }
    return $value;
}
add_filter( 'mustache_variable_sort_tag', 'wppb_in_bdp_sort_last_active', 10, 4 );


/**
 * Add default sorting option criteria Last Active in Userlisting settings
 *
 * @since 1.0.0
 *
 * @param $sorting_criteria
 *
 * @return array
 */
function wppb_in_bdp_add_last_active_default_sorting_option( $sorting_criteria ){
    $sorting_criteria[] = '%'.__( 'BuddyPress Last Active', 'profile-builder' ).'%wppb_bdp_last_activity';
    return $sorting_criteria;
}
add_filter( 'wppb_default_sorting_criteria', 'wppb_in_bdp_add_last_active_default_sorting_option' );


/**
 * Add My Friends tab variable to Extra Functions tab in the right side of the Userlisting template
 *
 * @since 1.0.0
 *
 * @param $extra_functions
 *
 * @return array
 */
function wppb_in_bdp_userlisting_my_friends_tab_variable( $extra_functions ){
    $extra_functions[] = array( 'name' => 'bp_my_friends_tab', 'type' => 'bp_my_friends_tab', 'unescaped' => true, 'label' => __( 'My BuddyPress Friends Tab', 'profile-builder' ) );
    return $extra_functions;
}
add_filter( 'wppb_ul_extra_functions', 'wppb_in_bdp_userlisting_my_friends_tab_variable' );


/**
 * Add <Include> User query arg for Friends only
 *
 * @since 1.0.0
 *
 * @param $args
 *
 * @return array friend user Id
 */
function wppb_in_bdp_include_only_friends( $args ){
    if ( isset( $_GET['wppb_show_members'] ) && $_GET['wppb_show_members'] == 'friends' && function_exists( 'friends_get_friend_user_ids' ) ) {
        $current_user_id = get_current_user_id();
        if ($current_user_id != 0) {
            $friends = friends_get_friend_user_ids($current_user_id);
            if ($friends == 0) {
                $friends = array();
            }
            if (empty ($args['include'])) {
                $args['include'] = $friends;
            } else {
                $args['include'] = array_unique(array_merge($args['include'], $friends));
            }
        }
    }
    return $args;
}
add_filter( 'wppb_userlisting_user_query_args', 'wppb_in_bdp_include_only_friends' );


/**
 * Function that returns All Members | My Friends tab
 *
 * @since v.1.0.0
 *
 * @param $value
 * @param $name
 * @param $children
 * @param $extra_values
 *
 * @return string
 */
function wppb_in_bdp_ul_my_friends_tab( $value, $name, $children, $extra_values ){
    if ( function_exists ( 'bp_get_total_member_count' ) ) {
        $total_member_count = bp_get_total_member_count();
    }else{
        $total_member_count = '';
    }
    $current_user_id = get_current_user_id();
    $friends_count = ( $current_user_id == 0 ) ? '' : get_user_meta( $current_user_id, 'total_friend_count', true );
    $tabs = apply_filters( 'wppb_bdp_members_tab', array(
        'all' => array(
                'label' => __( 'All Members', 'profile-builder' ),
                'count' => $total_member_count,
            ),
        'friends' => array(
                'label' => __( 'My Friends', 'profile-builder' ),
                'count' => $friends_count
            ),
        ));

    $output = '<ul class="wppb_bdp_members_and_friends_tab">';
    foreach ( $tabs as $key => $value ){
        if ( $key == 'friends' && ( $current_user_id == 0 || $friends_count == 0 ) ){
            continue;
        }
        $selected_class = '';
        if ( ( isset( $_GET['wppb_show_members'] ) && $_GET['wppb_show_members'] == $key ) || ( !isset( $_GET['wppb_show_members'] ) && $key == 'all' ) )
            $selected_class = 'wppb-bdp-active-tab';

        $sort_link = add_query_arg( array( 'wppb_show_members' => $key, 'page' => '1' ) , get_permalink() );
        $output .= '<li id="wppb_bdp_tab_' . $key . '" class="wppb-bdp-tab ' . $selected_class . '">';
        $output .= '<a href="' . $sort_link . '">' . $value['label'] . '<span id="wppb_bdp_tab_count" class="wppb-bdp-tab-count">' . $value['count'] . '</span></a>';
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_filter( 'mustache_variable_bp_my_friends_tab', 'wppb_in_bdp_ul_my_friends_tab', 10, 4 );


/**
 * Adds Link to documentation on buddypress visibility syntax
 *
 * @since v.1.0.0
 *
 * @param $mustache_var_group
 * @param $id
 * @param $post_type
 *
 * @return null
 */
function wppb_in_bdp_link_to_visibility_documentation( $mustache_var_group, $id, $post_type ){
    if ( $post_type == 'wppb-ul-cpt' ) {
        if ( ( $id == 'wppb-ul-templates' ) || ( $id == 'wppb-single-ul-templates' ) ) {
            echo '<a href="https://www.cozmoslabs.com/docs/profile-builder-2/add-ons/buddypress/#User_Listing_field_visibility_syntax" target="_blank">' . esc_html__( 'BuddyPress field visibility syntax', 'profile-builder' ) . '</a> <br>';
        }
    }
}
add_action( 'wppb_before_mustache_vars_display', 'wppb_in_bdp_link_to_visibility_documentation', 10, 3 );


/**
 * Reset template meta box content
 *
 * @since v.1.0.0
 *
 * @return null
 */
function wppb_in_bdp_ul_content(){
    global $post;
    $url = add_query_arg( array(
        'wppb-bdp-action'   => 'wppb-bdp-reset-ul-template',
    ), wppb_curpageurl() );
    $url = wp_nonce_url( $url, 'wppb-bdp-reset-ul-template-' . $post->ID );
    echo "<p><a class ='wppb-bdp-reset-template' href='" . esc_attr( $url ) . "' onclick=\"return confirm('" . esc_html__( 'Are you sure you want to reset this template?', 'profile-builder' ) . "')\">" . esc_html__( 'Reset to Default BuddyPress User Listing Templates', 'profile-builder' )  . "</a></p>";
    echo '<p>' . esc_html__( '<b>Note:</b> This action is not reversible. All modifications to this template will be lost!', 'profile-builder' ) . '</p>';
}


/**
 * Register Reset template metabox for Userlistings created by this add-on
 *
 * @since v.1.0.0
 *
 * @return null
 */
function wppb_in_bdp_ul_side_box(){
    global $post;
    if ( !empty( $post->post_content ) && $post->post_content == 'Default Userlisting for BuddyPress integration' ) {
        add_meta_box( 'wppb-bdp-ul-side', __('Reset template', 'profile-builder'), 'wppb_bdp_ul_content', 'wppb-ul-cpt', 'side', 'low' );
    }
}
add_action( 'add_meta_boxes', 'wppb_in_bdp_ul_side_box' );


/**
 * Reset template for Userlistings created by this add-on
 *
 * @since v.1.0.0
 *
 * @return null
 */
function wppb_in_bdp_reset_ul_template(){
    if ( current_user_can( 'manage_options' ) && isset( $_GET['wppb-bdp-action'] ) && ( sanitize_text_field( $_GET['wppb-bdp-action'] ) === 'wppb-bdp-reset-ul-template' ) && !empty ( $_GET['post'] ) && is_numeric( $_GET['post'] ) )  {
        check_admin_referer('wppb-bdp-reset-ul-template-' . sanitize_text_field( $_GET['post'] ) );
        wppb_in_bdp_set_default_userlisting_templates( sanitize_text_field( $_GET['post'] ));
        wp_safe_redirect( remove_query_arg( array( 'wppb-bdp-action', '_wpnonce' ), wppb_curpageurl() ) );
    }
}
add_action( 'admin_init', 'wppb_in_bdp_reset_ul_template' );
