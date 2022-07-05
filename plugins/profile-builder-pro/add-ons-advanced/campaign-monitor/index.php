<?php
    /*
    Profile Builder - Campaign Monitor Add-On
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


    /*
     * Definitions and dependencies
     *
     */
    define('WPPBCMI_IN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ));
    define('WPPBCMI_IN_PLUGIN_URL', plugin_dir_url(__FILE__));

    // Increase the timeout to 15 seconds
    if (false === defined('WPPB_CS_REST_CALL_TIMEOUT')) {
        define('WPPB_CS_REST_CALL_TIMEOUT', 15);
    }

    // Include Campaign Monitor API files
    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_general.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_general.php' );

    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_clients.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_clients.php' );

    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_lists.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_lists.php' );

    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_subscribers.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/cmonitor/csrest_subscribers.php' );


    // Include the file for general functions
    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/admin/functions.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/admin/functions.php' );

    // Include the file for the Campaign Monitor Manage Fields
    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/admin/manage-fields.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/admin/manage-fields.php' );

    // Include the file for the Campaign Monitor sub-page
    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/admin/cmonitor-page.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/admin/cmonitor-page.php' );

    // Include the file for the Widget
    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/admin/widget.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/admin/widget.php' );

    // Include the file for the Campaign Monitor field
    if( file_exists( WPPBCMI_IN_PLUGIN_DIR . '/front-end/cmonitor-field.php' ) )
        include_once( WPPBCMI_IN_PLUGIN_DIR . '/front-end/cmonitor-field.php' );

    /*
     * Function that enqueues the necessary scripts in the admin area
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_cmi_scripts_and_styles_admin() {
        wp_register_script( 'wppb-cmonitor-integration', plugin_dir_url(__FILE__) . 'assets/js/main.js', array( 'jquery' ) );
        wp_enqueue_script( 'wppb-cmonitor-integration' );
        wp_enqueue_style( 'wppb-cmonitor-integration', plugin_dir_url(__FILE__) . 'assets/css/style-back-end.css' );
    }
    add_action( 'admin_enqueue_scripts', 'wppb_in_cmi_scripts_and_styles_admin' );


    /*
     * Function that enqueues the necessary scripts in the front end area
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_cmi_scripts_and_styles_front_end() {
        wp_enqueue_style( 'wppb-cmonitor-integration', plugin_dir_url(__FILE__) . 'assets/css/style-front-end.css' );
    }
    add_action( 'wp_enqueue_scripts', 'wppb_in_cmi_scripts_and_styles_front_end' );


    /*
     * Function that registers the settings for the Campaign Monitor options page
     *
     * @since v1.0.0
     *
     */
    function wppb_in_cmi_register_settings() {
        register_setting( 'wppb_cmi_settings', 'wppb_cmi_settings', 'wppb_in_cmi_settings_sanitize' );
    }
    if ( is_admin() ) {
        add_action('admin_init', 'wppb_in_cmi_register_settings');
    }
