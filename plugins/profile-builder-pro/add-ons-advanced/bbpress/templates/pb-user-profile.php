<?php
/**
 * This will overwrite the default bbPress user Profile template
 *
 * Instead, it will display the Profile Builder 'Single-userlisting' set under bbPress add-on settings tab
 */
do_action( 'wppb_bbp_template_before_user_profile' );

$wppb_bbpress_settings = get_option( 'wppb_bbpress_settings' );

// get user id from url
$id = get_query_var('bbp_user_id');

if ( !empty($wppb_bbpress_settings) )
    //create User Listing slug from name
    $userlisting = rawurldecode( sanitize_title_with_dashes( remove_accents( $wppb_bbpress_settings['UserListing'] ) ) );
else
    //use default User Listing
    $userlisting = 'userlisting';

if ( !empty($id) ){
    echo do_shortcode('[wppb-list-users single name="'.$userlisting.'" id="'.$id.'"]');
}

do_action( 'wppb_bbp_template_after_user_profile' );