<?php
/*
Profile Builder - MailPoet Add-on
License: GPL2

== Copyright ==
Copyright 2016 Cozmoslabs (www.cozmoslabs.com)

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

/**
 * Define plugin path and include dependencies.
 *
 * @since 1.0.0
 *
 */
define('WPPBMPI_IN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ));
define('WPPBMPI_IN_PLUGIN_URL', plugin_dir_url(__FILE__) );

// Include the file with general functions
if (file_exists(WPPBMPI_IN_PLUGIN_DIR . '/admin/functions.php'))
    include_once(WPPBMPI_IN_PLUGIN_DIR . '/admin/functions.php');

// Include the file that manages manage fields
if (file_exists(WPPBMPI_IN_PLUGIN_DIR . '/admin/manage-fields.php'))
    include_once(WPPBMPI_IN_PLUGIN_DIR . '/admin/manage-fields.php');

// Include the files for the custom field
if (file_exists(WPPBMPI_IN_PLUGIN_DIR . '/front-end/mailpoet-field.php'))
    include_once(WPPBMPI_IN_PLUGIN_DIR . '/front-end/mailpoet-field.php');

/**
 * Function that enqueues the necessary scripts in the admin area
 *
 * @since v.1.0.0
 *
 */
function wppb_in_mpi_scripts_and_styles_admin($hook) {
    if ( 'profile-builder_page_manage-fields' != $hook ){
        return;
    }
    wp_enqueue_script( 'wppb-mailpoet-integration', WPPBMPI_IN_PLUGIN_URL . 'assets/js/main.js', array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', 'wppb_in_mpi_scripts_and_styles_admin' );

/**
 * Function that enqueues the necessary scripts in the front end area
 *
 * @since v.1.0.0
 *
 */
function wppb_in_mpi_scripts_and_styles_front_end() {
    global $wppb_shortcode_on_front;
    if( !empty( $wppb_shortcode_on_front ) && $wppb_shortcode_on_front === true ) {
        wp_enqueue_style('wppb-mailpoet-integration', WPPBMPI_IN_PLUGIN_URL . 'assets/css/style-front-end.css');
    }
}
add_action( 'wp_footer', 'wppb_in_mpi_scripts_and_styles_front_end' );

/**
 * Function that handles the visibility of the field
 *
 * @since v.1.0.0
 *
 * @param bool $display_field      - By default true, to continue displaying the field
 * @param array $field             - The current field
 * @param string $form_location    - The location of the form. It can be register, edit_profile and back_end
 * @param string $form_role        - The role that will be attributed by default to new users
 * @param int $user_id
 *
 * @return bool
 */
function wppb_in_mpi_handle_output_display_state( $display_field, $field, $form_location, $form_role, $user_id ) {
    if( $form_location == 'edit_profile' && $field['field'] == 'MailPoet Subscribe' && isset( $field['mailpoet-hide-field'] ) && $field['mailpoet-hide-field'] == 'yes' ) {
        $display_field = false;
    }

    return $display_field;
}
add_filter( 'wppb_output_display_form_field', 'wppb_in_mpi_handle_output_display_state', 10, 5 );


/**
 * Display notice if MailPoet Newsletters plugin is not active
 *
 * @since v.1.0.0
 *
 */
function wppb_in_mpi_admin_notice() {
    if ( wppb_in_mailpoet_installed() ) {
        return;
    }
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( 'MailPoet Newsletters needs to be installed and activated for Profile Builder - MailPoet Add-on to work!', 'profile-builder' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'wppb_in_mpi_admin_notice' );
