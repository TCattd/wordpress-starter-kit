<?php
/**
 * This will overwrite the default BuddyPress user Registration template
 *
 * It will display the Profile Builder 'Registration' form set under BuddyPress add-on settings tab
 */

do_action( 'bp_before_register_page' );


$wppb_buddypress_settings = get_option( 'wppb_buddypress_settings' );

if ( !empty($wppb_buddypress_settings) )
    //create Registration form slug from name
    $registration_form = rawurldecode( sanitize_title_with_dashes( remove_accents( $wppb_buddypress_settings['RegistrationForm'] ) ) );
else
    //use default Registration
    $registration_form = 'wppb-register';

echo do_shortcode('[wppb-register form_name="'. $registration_form .'"]');

/**
 * Fires at the bottom of the BuddyPress member registration page template.
 *
 * @since 1.1.0
 */
do_action( 'bp_after_register_page' );