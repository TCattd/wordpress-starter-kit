<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function wppb_block_editor_enqueue()
{
    wp_enqueue_style('wppb_block_stylesheet', plugin_dir_url( __FILE__ ) . 'assets/css/gutenberg-blocks.css', PROFILE_BUILDER_VERSION);

    wp_enqueue_style('wppb_stylesheet', WPPB_PLUGIN_URL . 'assets/css/style-front-end.css', array('wp-edit-blocks'), PROFILE_BUILDER_VERSION);

    //Select
    wp_enqueue_script('wppb_select2_js', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array('jquery'), PROFILE_BUILDER_VERSION);
    wp_enqueue_style('wppb_select2_css', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION);


    if (defined('WPPB_PAID_PLUGIN_URL')) {
        if (defined('WPPB_PAID_PLUGIN_URL')) {
            //Select2
            wp_enqueue_style('wppb_sl2_css', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/select2/select2.css', false, PROFILE_BUILDER_VERSION);
            wp_enqueue_style('wppb-select-cpt-style', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/select-cpt/style-front-end.css', array(), PROFILE_BUILDER_VERSION);

            //Upload
            wp_enqueue_style('wppb-upload-css', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/upload/upload.css', false, PROFILE_BUILDER_VERSION);

            //Multi-Step Forms compatibility
            wp_enqueue_style('wppb-msf-style-frontend', WPPB_PAID_PLUGIN_URL . 'add-ons-advanced/multi-step-forms/assets/css/frontend-multi-step-forms.css', array(), PROFILE_BUILDER_VERSION);
        }

        //Userlisting
        wp_enqueue_script('wppb-userlisting-js', WPPB_PAID_PLUGIN_URL . 'add-ons/user-listing/userlisting.js', array('jquery', 'jquery-touch-punch'), PROFILE_BUILDER_VERSION, true);
        wp_localize_script('wppb-userlisting-js', 'wppb_userlisting_obj', array('pageSlug' => wppb_get_users_pagination_slug()));
        wp_enqueue_style('wppb-ul-slider-css', WPPB_PAID_PLUGIN_URL . 'add-ons/user-listing/jquery-ui-slider.min.css', array(), PROFILE_BUILDER_VERSION);
        wp_enqueue_script('jquery-ui-slider');

        //Social Connect
        wp_enqueue_style('wppb-sc-frontend-style', WPPB_PAID_PLUGIN_URL . 'add-ons-advanced/social-connect/assets/css/wppb_sc_main_frontend.css', false, PROFILE_BUILDER_VERSION);
    }
}
add_action( 'enqueue_block_editor_assets', 'wppb_block_editor_enqueue' );

function register_layout_category( $categories ) {

    $categories[] = array(
        'slug'  => 'wppb-block',
        'title' => 'Profile Builder'
    );

    return $categories;
}

if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
    add_filter( 'block_categories_all', 'register_layout_category' );
} else {
    add_filter( 'block_categories', 'register_layout_category' );
}

include_once(WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/login.php');
include_once(WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/register.php');
include_once(WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/edit-profile.php');
include_once(WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/recover-password.php');

if( defined( 'WPPB_PAID_PLUGIN_URL' ) && wppb_check_if_add_on_is_active( 'wppb_userListing' ) ) {
    include_once(WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/user-listing.php');
}