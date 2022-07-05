<?php
/**
 * This will overwrite the default bbPress user Profile Edit template
 *
 * It will display the Profile Builder 'Edit Profile' form set under bbPress add-on settings tab
 */

$wppb_bbpress_settings = get_option( 'wppb_bbpress_settings' );

if ( !empty($wppb_bbpress_settings) )
    //create Edit Profile form slug from name
    $edit_profile_form = rawurldecode( sanitize_title_with_dashes( remove_accents( $wppb_bbpress_settings['EditProfileForm'] ) ) );
else
    //use default Edit Profile
    $edit_profile_form = 'wppb-edit-profile';

echo do_shortcode('[wppb-edit-profile form_name="'. $edit_profile_form .'"]');