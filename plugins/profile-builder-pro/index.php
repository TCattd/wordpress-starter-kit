<?php
/*
Plugin Name: Profile Builder Pro
Plugin URI: https://www.cozmoslabs.com/wordpress-profile-builder/
Description: Get the best out of Profile Builder and enjoy fully customizable login, registration, and edit profile forms, along with front-end user listing, multiple registration & edit profile forms, custom redirects, email customizer, and more.
Version: 3.7.6
Author: Cozmoslabs
Author URI: https://www.cozmoslabs.com/
Text Domain: profile-builder
Domain Path: /translation
License: GPL2

== Copyright ==
Copyright 2014 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

register_activation_hook(__FILE__, 'wppb_pro_activate');
function wppb_pro_activate( $network_wide ) {
    if( !function_exists('is_plugin_active') )
        include_once( ABSPATH . '/wp-admin/includes/plugin.php' );

    $did_upgrade = get_option( 'wppb_repackage_initial_upgrade', false );

    if( $did_upgrade == 'yes' ){
        if( is_plugin_active('profile-builder-elite/index.php') || is_plugin_active('profile-builder-basic/index.php') || is_plugin_active('profile-builder-unlimited/index.php') || is_plugin_active('profile-builder-dev/index.php') ){
            set_transient( 'wppb_deactivate_pro', true );
        }
    }
}

add_action('admin_notices', 'wppb_pro_admin_notice');
add_action('network_admin_notices', 'wppb_pro_admin_notice');
function wppb_pro_admin_notice(){

    // Notification that the current version cannot be activated because there is another active version conflicting
    $wppb_deactivate_pro = get_transient( 'wppb_deactivate_pro' );
    if( $wppb_deactivate_pro ){

        $other_plugin_name = '';
        if( is_plugin_active('profile-builder-elite/index.php') )
            $other_plugin_name = 'Profile Builder - Elite';
        else if( is_plugin_active('profile-builder-basic/index.php') )
            $other_plugin_name = 'Profile Builder - Basic';
        else if( is_plugin_active('pprofile-builder-unlimited/index.php') )
            $other_plugin_name = 'Profile Builder - Unlimited';
        ?>
        <div class="error">
            <p>
                <?php
                /* translators: %s is the plugin version name */
                echo wp_kses_post(  sprintf( __( '%s is also activated. You need to deactivate it before activating this version of the plugin.', 'profile-builder' ), $other_plugin_name ) );
                ?>
            </p>
        </div>
        <?php
        delete_transient( 'wppb_deactivate_pro' );
    }

    // Notifications for base plugin missing or actions done from this notice
    $did_upgrade = get_option( 'wppb_repackage_initial_upgrade', false );

    if( $did_upgrade != false ){

        if( !defined( 'PROFILE_BUILDER_VERSION' ) ){
            echo '<div class="notice notice-info is-dismissible"><p>';
            echo '<strong>Profile Builder Pro</strong></p><p>';
            if( !wppb_pro_is_plugin_installed( 'profile-builder/index.php' ) )
                echo wp_kses_post( sprintf( __( 'In order for this plugin to work please install and activate the %s plugin.', 'profile-builder' ), '<strong>Profile Builder</strong>' ) );
            else
                echo wp_kses_post( sprintf( __( 'In order for this plugin to work please activate the %s plugin.', 'profile-builder' ), '<strong>Profile Builder</strong>' ) );
            echo '</p>';
            echo '<p><a href="' . esc_url( add_query_arg( array( 'action' => 'wppb_install_wppb_plugin', 'nonce' => wp_create_nonce( 'wppb_install_wppb_plugin' ) ) ) ) . '" type="button" class="button-primary">' . ( !wppb_pro_is_plugin_installed( 'profile-builder/index.php' ) ? esc_html__( 'Install & Activate', 'profile-builder' ) : esc_html__( 'Activate', 'profile-builder' ) ) . '</a></p>';
            echo '</div>';
        } else {
            if( version_compare( PROFILE_BUILDER_VERSION, '3.7.2', '<' ) ){
                echo '<div class="notice notice-info is-dismissible"><p>';
                echo wp_kses_post( sprintf(__('Please update the %s plugin to at least version %s in order for %s to work properly', 'profile-builder'), '<strong>Profile Builder</strong>', '<strong>3.7.2</strong>', '<strong>Profile Builder Pro</strong>' ) );
                echo '</p></div>';
            }
        }
    }
}

add_action( 'admin_init', 'wppb_pro_plugin_deactivate' );
function wppb_pro_plugin_deactivate() {
    $wppb_deactivate_pro = get_transient( 'wppb_deactivate_pro' );
    if( $wppb_deactivate_pro ){
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    unset($_GET['activate']);

}

function wppb_pro_add_plugin_action_links( $links ) {

    if ( current_user_can( 'manage_options' ) ) {

        $addons_url = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=profile-builder-add-ons' ), esc_html( __( 'Add-ons', 'profile-builder' ) ) );

        array_unshift( $links, $addons_url );

    }

    return $links;

}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wppb_pro_add_plugin_action_links' );

add_action( 'admin_init', 'wppb_pro_initial_upgrade', 1 );
function wppb_pro_initial_upgrade() {

    // Install & Activate Profile Builder Free on initial upgrade
    $did_upgrade = get_option( 'wppb_repackage_initial_upgrade', false );

    if( $did_upgrade === false ){

        $old_hobbyist_version = 'profile-builder-hobbyist/index.php';

        // Remove old Hobbyist & Pro versions
        if( wppb_pro_is_plugin_installed( $old_hobbyist_version ) ){

            deactivate_plugins( $old_hobbyist_version );
            delete_plugins( array( $old_hobbyist_version ) );

        }

        update_option( 'wppb_repackage_initial_upgrade', 'yes', false );

        // Free version
        wp_safe_redirect( add_query_arg( [
            'action' => 'wppb_install_wppb_plugin',
            'nonce'  => wp_create_nonce( 'wppb_install_wppb_plugin' )
        ] ) );
        exit;

    }

    wppb_pro_install_activate();

}

function wppb_pro_is_plugin_installed( $plugin_slug ){

    if ( !function_exists( 'get_plugins' ) )
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $all_plugins = get_plugins();

    if ( !empty( $all_plugins[ $plugin_slug ] ) )
        return true;

    return false;

}

function wppb_pro_install_free_plugin(){

    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();

    // do not output any messages
    $upgrader->skin = new Automatic_Upgrader_Skin();

    return $upgrader->install( 'https://downloads.wordpress.org/plugin/profile-builder.zip' );

}

function wppb_pro_install_activate(){

    if ( isset( $_REQUEST['wppb_install_wppb_plugin_success'] ) && $_REQUEST['wppb_install_wppb_plugin_success'] === 'true' ){
        return 'plugin_activated';
    }

    if (
        isset( $_REQUEST['action'] ) && !empty($_REQUEST['nonce']) && $_REQUEST['action'] === 'wppb_install_wppb_plugin' &&
        !isset( $_REQUEST['wppb_install_wppb_plugin_success']) &&
        current_user_can( 'manage_options' ) &&
        wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'wppb_install_wppb_plugin' )
    ) {
        
        $installed   = true;
        $plugin_slug = 'profile-builder/index.php';

        if ( !wppb_pro_is_plugin_installed( $plugin_slug ) )
            $installed = wppb_pro_install_free_plugin();

        if ( !is_wp_error( $installed ) && $installed ) {
            $activate = activate_plugin( $plugin_slug );

            if ( is_null( $activate ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=profile-builder-basic-info' ) );
                return 'plugin_activated';
            }
        }

        return 'error_activating';
    }

    return 'no_action_requested';

}

add_action( 'upgrader_process_complete', 'wppb_pro_on_plugin_update',10, 2);
function wppb_pro_on_plugin_update( $upgrader_object, $options ) {

    if( !isset( $options['action'] ) || !isset( $options['type'] ) )
        return;

    $did_upgrade = get_option( 'wppb_repackage_initial_upgrade', false );

    if( $did_upgrade === false ){
        $current_plugin_path_name = plugin_basename( __FILE__ );
    
        if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {

            foreach( $options['plugins'] as $plugin ) {

                if ( $plugin == $current_plugin_path_name ) {

                    $installed   = true;
                    $free_plugin_slug = 'profile-builder/index.php';

                    if ( !wppb_pro_is_plugin_installed( $free_plugin_slug ) )
                        $installed = wppb_pro_install_free_plugin();

                    if ( !is_wp_error( $installed ) && $installed )
                        $activate = activate_plugin( $free_plugin_slug );

                    update_option( 'wppb_repackage_initial_upgrade', 'yes', false );
                    
                    break;

                }

            }

        }
    }

}

add_action( 'automatic_updates_complete', 'wppb_pro_on_plugin_automatic_update' );
function wppb_pro_on_plugin_automatic_update( $results ) {

    $did_upgrade = get_option( 'wppb_repackage_initial_upgrade', false );

    if( $did_upgrade === false ){
        $current_plugin_path_name = plugin_basename( __FILE__ );

        foreach ( $results['plugin'] as $plugin ) {
            
            if ( ! empty( $plugin->item->slug ) && $current_plugin_path_name === $plugin->item->slug ) {

                $installed        = true;
                $free_plugin_slug = 'profile-builder/index.php';

                if ( !wppb_pro_is_plugin_installed( $free_plugin_slug ) )
                    $installed = wppb_pro_install_free_plugin();

                if ( !is_wp_error( $installed ) && $installed )
                    $activate = activate_plugin( $free_plugin_slug );

                update_option( 'wppb_repackage_initial_upgrade', 'yes', false );
                
                break;

            }

        }
    }

}