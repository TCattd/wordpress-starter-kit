<?php
/**
 * This will overwrite the default BuddyPress user Profile template
 *
 * Instead, it will display the Profile Builder 'Single-userlisting' set under BuddyPress add-on settings tab
 */

/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
do_action( 'bp_before_profile_loop_content' );


$wppb_buddypress_settings = get_option( 'wppb_buddypress_settings' );

// get user id from url
if ( function_exists( 'bp_displayed_user_id' ) ) {
    $id = bp_displayed_user_id();
}

if ( !empty($wppb_buddypress_settings) )
    //create User Listing slug from name
    $userlisting = rawurldecode( sanitize_title_with_dashes( remove_accents( $wppb_buddypress_settings['UserListing'] ) ) );
else
    //use default User Listing
    $userlisting = 'userlisting';

if ( !empty($id) ){
    echo do_shortcode('[wppb-list-users single name="'.$userlisting.'" id="'. esc_attr( $id ).'"]');
}


/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
do_action( 'bp_after_profile_loop_content' );
