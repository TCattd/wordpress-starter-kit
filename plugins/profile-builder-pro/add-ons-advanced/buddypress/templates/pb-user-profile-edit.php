<?php
/**
 * This will overwrite the default BuddyPress user Profile Edit template
 *
 * It will display the Profile Builder 'Edit Profile' form set under BuddyPress add-on settings tab
 */

do_action( 'bp_before_profile_edit_content' );


$wppb_buddypress_settings = get_option( 'wppb_buddypress_settings' );

if ( !empty($wppb_buddypress_settings) )
    //create Edit Profile form slug from name
    $edit_profile_form = rawurldecode( sanitize_title_with_dashes( remove_accents( $wppb_buddypress_settings['EditProfileForm'] ) ) );
else
    //use default Edit Profile
    $edit_profile_form = 'wppb-edit-profile';

$edit_profile_form = apply_filters('wppb_bdp_edit_profile_form_name', $edit_profile_form);

if ( function_exists( 'bp_current_user_id' ) && empty( $_GET['edit_user'] ) ) {
    $_GET['edit_user'] = bp_current_user_id();
}
echo do_shortcode('[wppb-edit-profile form_name="'. $edit_profile_form .'"]');


do_action( 'bp_after_profile_edit_content' );
