<?php

/**
 * This will overwrite the default BuddyPress Members template
 *
 * It will display the Profile Builder User listing page set under BuddyPress add-on settings tab
 */

/**
 * Fires at the top of the members directory template file.
 *
 * @since 1.5.0
 */
do_action( 'bp_before_directory_members_page' );

$wppb_buddypress_settings = get_option( 'wppb_buddypress_settings' );

if ( !empty($wppb_buddypress_settings) )
    //create User Listing slug from name
    $userlisting = rawurldecode( sanitize_title_with_dashes( remove_accents( $wppb_buddypress_settings['UserListing'] ) ) );
else
    //use default User Listing
    $userlisting = 'userlisting';


echo do_shortcode('[wppb-list-users name="'. $userlisting .'"]');


/**
 * Fires at the bottom of the members directory template file.
 *
 * @since 1.5.0
 */
do_action( 'bp_after_directory_members_page' );