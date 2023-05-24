<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/templates/myaccount-edit-profile.php
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );

	$wppb_woosync_settings = get_option( 'wppb_woosync_settings');

	//Check if there is a specific Edit Profile form we need to display
	if ( $wppb_woosync_settings['EditProfileForm'] == 'wppb-default-edit-profile' ){

	    $redirect_url = apply_filters( 'wppb_woo_myaccount_edit_account_redirect_url', get_permalink( wc_get_page_id( 'myaccount' ) ) );

	    echo do_shortcode( '[wppb-edit-profile redirect_url="' .esc_url($redirect_url). '" ]' );
	}
	else
	    echo do_shortcode( '[wppb-edit-profile form_name="' . apply_filters('wppb_woo_edit_profile_form_name', Wordpress_Creation_Kit_PB::wck_generate_slug($wppb_woosync_settings['EditProfileForm'])) . '"]');

do_action( 'woocommerce_after_edit_account_form' ); ?>
