<?php
/**
 * Plugin Name: Profile Builder
 * Plugin URI: https://www.cozmoslabs.com/wordpress-profile-builder/
 * Description: Login, registration and edit profile shortcodes for the front-end. Also you can choose what fields should be displayed or add new (custom) ones both in the front-end and in the dashboard.
 * Version: 3.8.5
 * Author: Cozmoslabs
 * Author URI: https://www.cozmoslabs.com/
 * Text Domain: profile-builder
 * Domain Path: /translation
 * License: GPL2
 * Elementor tested up to: 3.8.1
 * Elementor Pro tested up to: 3.8.1
 *
 * == Copyright ==
 * Copyright 2014 Cozmoslabs (www.cozmoslabs.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Check if another version of Profile Builder is activated, to prevent fatal errors*/
function wppb_plugin_init() {
    if (function_exists('wppb_return_bytes')) {
        function wppb_admin_notice()
        {
            ?>
            <div class="error">
                <p>
                    <?php
                    /* translators: %s is the plugin version name */
                    echo wp_kses_post( sprintf( __( '%s is also activated. You need to deactivate it before activating this version of the plugin.', 'profile-builder'), PROFILE_BUILDER ) );
                    ?>
                </p>
            </div>
        <?php
        }
        function wppb_plugin_deactivate() {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            unset($_GET['activate']);
        }

        add_action('admin_notices', 'wppb_admin_notice');
        add_action( 'admin_init', 'wppb_plugin_deactivate' );
    } else {

        /**
         * Convert memory value from ini file to a readable form
         *
         * @since v.1.0
         *
         * @return integer
         */
        function wppb_return_bytes($val)
        {
            return wp_convert_hr_to_bytes($val);
        }

        /* include notices class */
        if ( file_exists(WPPB_PLUGIN_DIR . '/assets/lib/class_notices.php') )
            include_once(WPPB_PLUGIN_DIR . '/assets/lib/class_notices.php');

        /* include review class */
        if (file_exists(WPPB_PLUGIN_DIR . '/admin/review.php')){
            include_once(WPPB_PLUGIN_DIR . '/admin/review.php');
            $wppb_review_request = new WPPB_Review_Request ();
        }

        /**
         * Initialize the translation for the Plugin.
         *
         * @since v.1.0
         *
         * @return null
         */
        function wppb_init_translation(){
            $current_theme = wp_get_theme();
            if( !empty( $current_theme->stylesheet ) && file_exists( get_theme_root().'/'. $current_theme->stylesheet .'/local_pb_lang' ) )
                load_plugin_textdomain( 'profile-builder', false, basename( dirname( __FILE__ ) ).'/../../themes/'.$current_theme->stylesheet.'/local_pb_lang' );
            else
                load_plugin_textdomain( 'profile-builder', false, basename(dirname(__FILE__)) . '/translation/' );
        }

        add_action('init', 'wppb_init_translation', 8);


        /**
         * Required files
         *
         *
         */
        include_once(WPPB_PLUGIN_DIR . '/assets/lib/wck-api/wordpress-creation-kit.php');
        include_once(WPPB_PLUGIN_DIR . '/features/upgrades/upgrades.php');
        include_once(WPPB_PLUGIN_DIR . '/features/functions.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/admin-functions.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/basic-info.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/general-settings.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/advanced-settings/advanced-settings.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/admin-bar.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/private-website.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/manage-fields.php');
        include_once(WPPB_PLUGIN_DIR . '/admin/pms-cross-promotion.php');
        //include_once(WPPB_PLUGIN_DIR . '/admin/feedback.php');//removed in version 2.9.7
        include_once(WPPB_PLUGIN_DIR . '/features/email-confirmation/email-confirmation.php');
        include_once(WPPB_PLUGIN_DIR . '/features/email-confirmation/class-email-confirmation.php');

        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/features/admin-approval/admin-approval.php' ) ) {
            include_once(WPPB_PAID_PLUGIN_DIR . '/features/admin-approval/admin-approval.php');
            include_once(WPPB_PAID_PLUGIN_DIR . '/features/admin-approval/class-admin-approval.php');
        }

        if ( wppb_conditional_fields_exists() ) {
            include_once(WPPB_PAID_PLUGIN_DIR . '/features/conditional-fields/conditional-fields.php');
        }

        include_once(WPPB_PLUGIN_DIR . '/features/login-widget/login-widget.php');
        include_once(WPPB_PLUGIN_DIR . '/features/roles-editor/roles-editor.php');
        include_once(WPPB_PLUGIN_DIR . '/features/content-restriction/content-restriction.php');

        /* include 2fa class */
        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/features/two-factor-authentication/class-two-factor-authentication.php' ) ){
            include_once( WPPB_PAID_PLUGIN_DIR . '/features/two-factor-authentication/class-two-factor-authentication.php' );
            new WPPB_Two_Factor_Authenticator();
        }

        if (file_exists(WPPB_PLUGIN_DIR . '/update/class-edd-sl-plugin-updater.php')) {
            include_once(WPPB_PLUGIN_DIR . '/update/class-edd-sl-plugin-updater.php');
            include_once(WPPB_PLUGIN_DIR . '/admin/register-version.php');
        }

        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons/add-ons.php' ) ) {

            include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/add-ons.php');
            include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/repeater-field/repeater-module.php');
            include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/custom-redirects/custom-redirects.php');
            include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/multiple-forms/multiple-forms.php');
            include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/userlisting.php');

            $wppb_module_settings = get_option('wppb_module_settings');
            if (isset($wppb_module_settings['wppb_userListing']) && ($wppb_module_settings['wppb_userListing'] == 'show')) {
                add_shortcode('wppb-list-users', 'wppb_user_listing_shortcode');
            } else
                add_shortcode('wppb-list-users', 'wppb_list_all_users_display_error');

            if( !file_exists( WPPB_PLUGIN_DIR . '/features/email-customizer/email-customizer.php' ) ){

                include_once( WPPB_PAID_PLUGIN_DIR . '/add-ons/email-customizer/email-customizer.php' );

                $wppb_email_customizer_activate = 'hide';
                if ( ( !empty( $wppb_module_settings['wppb_emailCustomizer'] ) && $wppb_module_settings['wppb_emailCustomizer'] == 'show' ) || ( !empty( $wppb_module_settings['wppb_emailCustomizerAdmin'] ) && $wppb_module_settings['wppb_emailCustomizerAdmin'] == 'show' ) )
                    $wppb_email_customizer_activate = 'show';

                if ( $wppb_email_customizer_activate == 'show' )
                    include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/email-customizer/admin-email-customizer.php');

                if ( $wppb_email_customizer_activate == 'show' )
                    include_once(WPPB_PAID_PLUGIN_DIR . '/add-ons/email-customizer/user-email-customizer.php');
            }

        }

        // Email Customizer is in free since 3.8.1
        if( file_exists( WPPB_PLUGIN_DIR . '/features/email-customizer/email-customizer.php' ) ){
            include_once( WPPB_PLUGIN_DIR . '/features/email-customizer/email-customizer.php' );
            include_once( WPPB_PLUGIN_DIR . '/features/email-customizer/admin-email-customizer.php' );
            include_once( WPPB_PLUGIN_DIR . '/features/email-customizer/user-email-customizer.php' );
        }

        include_once(WPPB_PLUGIN_DIR . '/admin/add-ons.php');
        include_once(WPPB_PLUGIN_DIR . '/assets/misc/plugin-compatibilities.php');

        /* added recaptcha and user role field since version 2.6.2 */
        include_once(WPPB_PLUGIN_DIR . '/front-end/default-fields/recaptcha/recaptcha.php'); //need to load this here for displaying reCAPTCHA on Login and Recover Password forms

        //Elementor Widgets
        if ( did_action( 'elementor/loaded' ) ) {
            if (file_exists(WPPB_PLUGIN_DIR . 'assets/misc/elementor/class-elementor.php'))
                include_once WPPB_PLUGIN_DIR . 'assets/misc/elementor/class-elementor.php';
        }

        //Blocks
        global $wp_version;
        if ( version_compare( $wp_version, "5.0.0", ">=" ) ) {
            if( file_exists( WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/manage-blocks.php' ) )
                include_once WPPB_PLUGIN_DIR . '/assets/misc/gutenberg-blocks/manage-blocks.php';
        }

        //Elementor Content Restriction
        global $content_restriction_activated;
        if ( $content_restriction_activated == 'yes' && did_action( 'elementor/loaded' ) ) {
            if( file_exists( WPPB_PLUGIN_DIR . 'features/content-restriction/class-elementor-content-restriction.php' ) )
                include_once WPPB_PLUGIN_DIR . 'features/content-restriction/class-elementor-content-restriction.php';
        }

        //Include Free Add-ons
        $wppb_free_add_ons_settings = get_option( 'wppb_free_add_ons_settings', array() );
        if( !empty( $wppb_free_add_ons_settings ) ){

            if( isset( $wppb_free_add_ons_settings['custom-css-classes-on-fields'] ) && $wppb_free_add_ons_settings['custom-css-classes-on-fields'] ){
                if( file_exists( WPPB_PLUGIN_DIR . 'add-ons-free/custom-css-classes-on-fields/custom-css-classes-on-fields.php' ) )
                    include_once WPPB_PLUGIN_DIR . 'add-ons-free/custom-css-classes-on-fields/custom-css-classes-on-fields.php';
            }

            if( isset( $wppb_free_add_ons_settings['gdpr-communication-preferences'] ) && $wppb_free_add_ons_settings['gdpr-communication-preferences'] ){
                if( file_exists( WPPB_PLUGIN_DIR . 'add-ons-free/gdpr-communication-preferences/gdpr-communication-preferences.php' ) )
                    include_once WPPB_PLUGIN_DIR . 'add-ons-free/gdpr-communication-preferences/gdpr-communication-preferences.php';
            }

            if( isset( $wppb_free_add_ons_settings['import-export'] ) && $wppb_free_add_ons_settings['import-export'] ){
                if( file_exists( WPPB_PLUGIN_DIR . 'add-ons-free/import-export/import-export.php' ) )
                    include_once WPPB_PLUGIN_DIR . 'add-ons-free/import-export/import-export.php';
            }

            if( isset( $wppb_free_add_ons_settings['labels-edit'] ) && $wppb_free_add_ons_settings['labels-edit'] ){
                if( file_exists( WPPB_PLUGIN_DIR . 'add-ons-free/labels-edit/labels-edit.php' ) )
                    include_once WPPB_PLUGIN_DIR . 'add-ons-free/labels-edit/labels-edit.php';
            }

            if( isset( $wppb_free_add_ons_settings['maximum-character-length'] ) && $wppb_free_add_ons_settings['maximum-character-length'] ){
                if( file_exists( WPPB_PLUGIN_DIR . 'add-ons-free/maximum-character-length/maximum-character-length.php' ) )
                    include_once WPPB_PLUGIN_DIR . 'add-ons-free/maximum-character-length/maximum-character-length.php';
            }

        }

        //Include Advanced Add-ons

        if( defined( 'WPPB_PAID_PLUGIN_DIR' ) ){

            $wppb_advanced_add_ons_settings = get_option( 'wppb_advanced_add_ons_settings', array() );
            if( !empty( $wppb_advanced_add_ons_settings ) ){

                if( isset( $wppb_advanced_add_ons_settings['buddypress'] ) && $wppb_advanced_add_ons_settings['buddypress'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/buddypress/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/buddypress/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['social-connect'] ) && $wppb_advanced_add_ons_settings['social-connect'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/social-connect/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/social-connect/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['woocommerce'] ) && $wppb_advanced_add_ons_settings['woocommerce'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/woocommerce/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/woocommerce/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['multi-step-forms'] ) && $wppb_advanced_add_ons_settings['multi-step-forms'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/multi-step-forms/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/multi-step-forms/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['mailchimp-integration'] ) && $wppb_advanced_add_ons_settings['mailchimp-integration'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/mailchimp-integration/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/mailchimp-integration/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['bbpress'] ) && $wppb_advanced_add_ons_settings['bbpress'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/bbpress/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/bbpress/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['campaign-monitor'] ) && $wppb_advanced_add_ons_settings['campaign-monitor'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/campaign-monitor/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/campaign-monitor/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['field-visibility'] ) && $wppb_advanced_add_ons_settings['field-visibility'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/field-visibility/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/field-visibility/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['edit-profile-approved-by-admin'] ) && $wppb_advanced_add_ons_settings['edit-profile-approved-by-admin'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/edit-profile-approved-by-admin/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/edit-profile-approved-by-admin/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['custom-profile-menus'] ) && $wppb_advanced_add_ons_settings['custom-profile-menus'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/custom-profile-menus/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/custom-profile-menus/index.php';
                }

                if( isset( $wppb_advanced_add_ons_settings['mailpoet-integration'] ) && $wppb_advanced_add_ons_settings['mailpoet-integration'] ){
                    if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/mailpoet-integration/index.php' ) )
                        include_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/mailpoet-integration/index.php';
                }

            }

            /**
             * Include add-on files that contain activation hooks even when add-ons are deactivated
             *
             * Necessary in order to perform actions during the operation of activation or deactivation of that add-on
             */
            if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/buddypress/buddypress-activator.php') )
                require_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/buddypress/buddypress-activator.php';

            if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/campaign-monitor/campaign-monitor-activator.php') )
                require_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/campaign-monitor/campaign-monitor-activator.php';

            if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/field-visibility/field-visibility-activator.php') )
                require_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/field-visibility/field-visibility-activator.php';

            if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/mailchimp/mailchimp-activator.php') )
                require_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/mailchimp/mailchimp-activator.php';

            if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/multi-step-forms/multi-step-forms-activator.php') )
                require_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/multi-step-forms/multi-step-forms-activator.php';

            if( file_exists( WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/woocommerce/woocommerce-activator.php') )
                require_once WPPB_PAID_PLUGIN_DIR . '/add-ons-advanced/woocommerce/woocommerce-activator.php';

        }


        /**
         * Add explanatory message on the plugins page when updates are not available
         *
         */
        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists(WPPB_PLUGIN_DIR . '/update/class-edd-sl-plugin-updater.php') ) {

            if ( class_exists('WPPB_EDD_SL_Plugin_Updater') ) {

                $serial = wppb_get_serial_number();

                if( ! function_exists('get_plugin_data') ){
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }

                $plugin_data       = get_plugin_data( WPPB_PAID_PLUGIN_DIR . '/index.php', false );
                $pb_plugin_version = ( $plugin_data && $plugin_data['Version'] ) ? $plugin_data['Version'] : '3.7.6' ;

                if( PROFILE_BUILDER == 'Profile Builder Pro' )
                    $pb_cl_plugin_id = '30695';
                else if( PROFILE_BUILDER == 'Profile Builder Basic' )
                    $pb_cl_plugin_id = '30697';
                else if( PROFILE_BUILDER == 'Profile Builder Elite' )
                    $pb_cl_plugin_id = '416191';
                else if( PROFILE_BUILDER == 'Profile Builder Unlimited' )
                    $pb_cl_plugin_id = '30699';

                // setup the updater
                $wppb_edd_updater = new WPPB_EDD_SL_Plugin_Updater('https://cozmoslabs.com', WPPB_PAID_PLUGIN_DIR . '/index.php', array(
                        'version'   => $pb_plugin_version,   // current version number
                        'license'   => $serial,         
                        'item_name' => PROFILE_BUILDER,      // name of this plugin
                        'item_id'   => $pb_cl_plugin_id,
                        'author'    => 'Cozmoslabs',         // author of this plugin
                        'beta'      => false
                    )
                );
                    
            }

            function wppb_plugin_update_message( $plugin_data, $new_data ) {
                
                $wppb_profile_builder_serial        = wppb_get_serial_number();
                $wppb_profile_builder_serial_status = wppb_get_serial_number_status();

                if( empty( $wppb_profile_builder_serial ) ){

                    echo '<br />' . wp_kses_post( sprintf( __('To enable updates, please enter your serial number on the <a href="%s">Register Version</a> page. If you don\'t have a serial number, please see <a href="%s" target="_blank">details & pricing</a>.', 'profile-builder' ), esc_url( admin_url('admin.php?page=profile-builder-register') ), 'https://www.cozmoslabs.com/wordpress-profile-builder/?utm_source=wpbackend&utm_medium=wppb-plugins-page&utm_campaign=WPPB' ) );

                } else if( $wppb_profile_builder_serial_status == 'expired' ) {

                    echo '<br />' . wp_kses_post( sprintf( __('To enable updates, your licence needs to be renewed. Please go to the <a href="%s">Cozmoslabs Account</a> page and login to renew.', 'profile-builder' ), 'https://www.cozmoslabs.com/account/' ) );

                }

            }
            add_action( 'in_plugin_update_message-' . strtolower( str_replace( ' ', '-', PROFILE_BUILDER ) ) . '/index.php', 'wppb_plugin_update_message', 10, 2 );
        }


// these settings are important, so besides running them on page load, we also need to do a check on plugin activation
        add_action('init', 'wppb_generate_default_settings_defaults');    //prepoulate general settings
        add_action('init', 'wppb_prepopulate_fields');                    //prepopulate manage fields list

    }
} //end wppb_plugin_init
add_action( 'plugins_loaded', 'wppb_plugin_init' );

/**
 * Definitions
 *
 *
 */
define('PROFILE_BUILDER_VERSION', '3.8.5' );
define('WPPB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPPB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPPB_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPPB_TRANSLATE_DIR', WPPB_PLUGIN_DIR . '/translation');
define('WPPB_TRANSLATE_DOMAIN', 'profile-builder');

// Determine which plugin version is active
$active_plugins         = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
$active_network_plugins = get_site_option('active_sitewide_plugins');

if ( in_array( 'profile-builder-pro/index.php', $active_plugins ) || isset( $active_network_plugins['profile-builder-pro/index.php'] ) ){
    
    define('PROFILE_BUILDER', 'Profile Builder Pro');
    define('WPPB_PAID_PLUGIN_DIR', WP_PLUGIN_DIR . '/profile-builder-pro' );
    define('WPPB_PAID_PLUGIN_URL', plugins_url() . '/profile-builder-pro/' );

} elseif ( in_array( 'profile-builder-dev/index.php', $active_plugins ) || isset( $active_network_plugins['profile-builder-dev/index.php'] ) ){
    
    define('PROFILE_BUILDER', 'Profile Builder Pro');
    define('WPPB_PAID_PLUGIN_DIR', WP_PLUGIN_DIR . '/profile-builder-dev' );
    define('WPPB_PAID_PLUGIN_URL', plugins_url() . '/profile-builder-dev/' );
    define('PROFILE_BUILDER_PAID_VERSION', 'dev' );

} elseif ( in_array( 'profile-builder-elite/index.php', $active_plugins ) || isset( $active_network_plugins['profile-builder-elite/index.php'] ) ){
    
    define('PROFILE_BUILDER', 'Profile Builder Elite');
    define('WPPB_PAID_PLUGIN_DIR', WP_PLUGIN_DIR . '/profile-builder-elite' );
    define('WPPB_PAID_PLUGIN_URL', plugins_url() . '/profile-builder-elite/' );

} elseif ( in_array( 'profile-builder-unlimited/index.php', $active_plugins ) || isset( $active_network_plugins['profile-builder-unlimited/index.php'] ) ){
    
    define('PROFILE_BUILDER', 'Profile Builder Unlimited');
    define('WPPB_PAID_PLUGIN_DIR', WP_PLUGIN_DIR . '/profile-builder-unlimited' );
    define('WPPB_PAID_PLUGIN_URL', plugins_url() . '/profile-builder-unlimited/' );

} elseif ( in_array( 'profile-builder-hobbyist/index.php', $active_plugins ) || isset( $active_network_plugins['profile-builder-hobbyist/index.php'] ) ){
    
    define('PROFILE_BUILDER', 'Profile Builder Basic');
    define('WPPB_PAID_PLUGIN_DIR', WP_PLUGIN_DIR . '/profile-builder-hobbyist' );
    define('WPPB_PAID_PLUGIN_URL', plugins_url() . '/profile-builder-hobbyist/' );

} else
    define('PROFILE_BUILDER', 'Profile Builder Free');

// This needs to be loaded here since we try to plug some functions, not suited for plugins_loaded hook
if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/upload/upload_helper_functions.php'))
    include_once( WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/upload/upload_helper_functions.php');
else if ( file_exists( WPPB_PLUGIN_DIR . '/front-end/default-fields/upload/upload_helper_functions.php' ) )
    include_once( WPPB_PLUGIN_DIR . '/front-end/default-fields/upload/upload_helper_functions.php');

/* add a redirect when plugin is activated */
if( !function_exists( 'wppb_activate_plugin_redirect' ) ){
    function wppb_activate_plugin_redirect( $plugin ) {
        if( !wp_doing_ajax() && $plugin == plugin_basename( __FILE__ ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=profile-builder-basic-info' ) );
            exit();
        }
    }
    add_action( 'activated_plugin', 'wppb_activate_plugin_redirect' );
}
