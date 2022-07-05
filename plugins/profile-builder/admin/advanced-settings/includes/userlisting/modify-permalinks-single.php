<?php

function wppb_toolbox_flush_rewrite_rules() {
    $base = wppb_toolbox_get_settings( 'userlisting', 'modify-permalinks-single' );

    if ( $base == false ) return;

	$rules           = get_option( 'rewrite_rules' );
    $frontpage_id    = get_option( 'page_on_front' );

    if ( !isset($rules['(.+?)/'.$base.'/([^/]+)']) ||
        !isset($rules['(.?.+?)/' . wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$'] ) ||
        ( !empty( $frontpage_id ) && !isset( $rules[wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$'] ) ) ) {
        global $wp_rewrite;

		$wp_rewrite->flush_rules();
	}
}
add_action( 'wp_loaded', 'wppb_toolbox_flush_rewrite_rules' );

function wppb_toolbox_insert_userlisting_rule( $rules ) {
    $base = wppb_toolbox_get_settings( 'userlisting', 'modify-permalinks-single' );

    if ( $base == false ) return $rules;

    $wppb_addonOptions = get_option('wppb_module_settings');

    if( $wppb_addonOptions['wppb_userListing'] == 'show' ) {
        $new_rules = array();

        //user rule
        $new_rules['(.+?)/'. $base .'/([^/]+)'] = 'index.php?pagename=$matches[1]&username=$matches[2]';

        //users-page rule
        $frontpage_id = get_option('page_on_front');
        if (!empty($frontpage_id)) {
            $new_rules[wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$'] = 'index.php?&page_id=' . $frontpage_id . '&wppb_page=$matches[1]';
        }

        $new_rules['(.?.+?)/' . wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$'] = 'index.php?pagename=$matches[1]&wppb_page=$matches[2]';

        $rules = $new_rules + $rules;
    }

    return $rules;

}
add_filter( 'rewrite_rules_array', 'wppb_toolbox_insert_userlisting_rule' );

add_action('init', 'wppb_toolbox_remove_ul_rewrite_rules');
function wppb_toolbox_remove_ul_rewrite_rules() {
    remove_action( 'wp_loaded', 'wppb_flush_rewrite_rules' );
    remove_filter( 'rewrite_rules_array', 'wppb_insert_userlisting_rule' );
}

add_filter( 'wppb_userlisting_more_info_link_structure2', 'wppb_toolbox_modify_more_info_link', 20, 3 );
add_filter( 'wppb_userlisting_more_info_link_structure3', 'wppb_toolbox_modify_more_info_link', 20, 3 );
function wppb_toolbox_modify_more_info_link( $final_url, $url, $user_info ) {
    $base = wppb_toolbox_get_settings( 'userlisting', 'modify-permalinks-single' );

    if ( $base == false ) return $final_url;

    if ( apply_filters( 'wppb_userlisting_get_user_by_id', true ) )
        $new_url = trailingslashit( $url ) . $base . '/' . $user_info->ID;
    else
        $new_url = trailingslashit( $url ) . $base . '/' . $user_info;

    return $new_url;
}
