<?php
/**
 * Profile Builder - BuddyPress Add-on
 * License: GPL2
 */
/*  Copyright 2019 Cozmoslabs (www.cozmoslabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Completely disable Profile Builder on sub-sites other than main Blog
 *
 * This happens only when all 3 plugins are network active: BuddyPress, PB and this PB - BP Add-on.
 *
 * @since v.1.0.0
 *
 * @return null
 */
function wppb_in_bdp_remove_pb_settings_from_subsites(){
    if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    $pb_is_network_active = false;
    $pb_plugins = apply_filters( 'wppb_bdp_pb_plugin_files', array( 'profile-builder-pro/index.php', 'profile-builder-hobbyist/index.php', 'profile-builder/index.php', 'profile-builder-2.0/index.php', 'profile-builder-basic/index.php', 'profile-builder-elite/index.php', 'profile-builder-unlimited/index.php' ) );
    foreach ( $pb_plugins as $pb_plugin ){
        if ( is_plugin_active_for_network( $pb_plugin ) ){
            $pb_is_network_active = true;
        }
    }

    if ( ! is_main_site( get_current_blog_id() ) && is_plugin_active_for_network( 'buddypress/bp-loader.php' ) && $pb_is_network_active ){
        // disable PB for this site
        remove_action( 'plugins_loaded', 'wppb_plugin_init' );
    }
}
if ( is_multisite() ) {
    add_action('plugins_loaded', 'wppb_in_bdp_remove_pb_settings_from_subsites', 9);
}


/**
 * Initialize everything if PB and BP are active
 *
 * @since v.1.0.0
 *
 * @return null
 */
function wppb_in_bdp_plugin_init() {
    /*
     * Make sure Profile Builder plugin is installed and active before doing anything
     */
    if( function_exists( 'wppb_return_bytes' ) ) {

        /*
         * Define plugin path
         */
        define('WPPB_IN_BDP_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
        define('WPPB_IN_BDP_PLUGIN_URL', plugin_dir_url(__FILE__) );

        /*
         * Define constants
         */
        define('WPPB_IN_BDP_VISIBILITY_OPTION_NAME', 'wppb_profile_visibility_levels');


        /*
         * Include the file for creating the BuddyPress subpage under Profile Builder menu
         */
        if (file_exists(WPPB_IN_BDP_PLUGIN_DIR . '/includes/buddypress-page.php'))
            include_once(WPPB_IN_BDP_PLUGIN_DIR . '/includes/buddypress-page.php');

        /*
         * Include the file for creating the BuddyPress subpage that handles BP field import
         */
        if (file_exists(WPPB_IN_BDP_PLUGIN_DIR . '/includes/import-bp-fields.php'))
            include_once(WPPB_IN_BDP_PLUGIN_DIR . '/includes/import-bp-fields.php');


        /*
         * Include the DOM parsing library
         */
        if (file_exists(WPPB_IN_BDP_PLUGIN_DIR . '/assets/lib/simple_html_dom.php'))
            include_once(WPPB_IN_BDP_PLUGIN_DIR . '/assets/lib/simple_html_dom.php');

        /*
         *  Makes sure the 'is_plugin_active_for_network' function is defined
         */
        if (!function_exists('is_plugin_active_for_network'))
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );


        /*
         *  Check if BuddyPress plugin is active before doing anything
         */
        if ( ( in_array( 'buddypress/bp-loader.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) || ( is_plugin_active_for_network('buddypress/bp-loader.php') ) )  {


            /**
             * Function that enqueues the necessary scripts in the front end area
             *
             * @since v.1.0.0
             *
             * @return null
             */
            function wppb_in_bdp_scripts_and_styles_front_end() {
                wp_enqueue_style('wppb-buddypress-integration-frontend-style', WPPB_IN_BDP_PLUGIN_URL . 'assets/css/wppb-bdp-style-front-end.css');
                global $wppb_shortcode_on_front;
                if( !empty( $wppb_shortcode_on_front ) && $wppb_shortcode_on_front === true ) {
                    wp_enqueue_script( 'wppb-bdp-front-end', WPPB_IN_BDP_PLUGIN_URL . '/assets/js/wppb-bdp-front-end.js', array( 'jquery' ) );
                }
            }
            add_action( 'wp_footer', 'wppb_in_bdp_scripts_and_styles_front_end' );


            /**
             * Function that enqueues the necessary scripts in the admin area
             *
             * @since v.1.0.0
             *
             * @return null
             *
             */
            function wppb_in_bdp_scripts_and_styles_admin( $hook ) {
                if ( 'profile-builder_page_manage-fields' == $hook && ! isset( $_GET['wppb_rpf_repeater_meta_name'] ) ){
                    wp_enqueue_script( 'wppb-bdp-integration', WPPB_IN_BDP_PLUGIN_URL . '/assets/js/wppb-bdp-admin.js', array( 'jquery' ), false, true );
                }
                if ( ( 'user-edit.php' == $hook ) || ( 'profile.php' == $hook ) || ( 'post.php' == $hook ) || ( 'profile-builder_page_profile-builder-buddypress' == $hook ) ) {
                    wp_enqueue_style('wppb-buddypress-integration-backend-style', WPPB_IN_BDP_PLUGIN_URL . 'assets/css/wppb-bdp-style-back-end.css');
                }
            }
            add_action( 'admin_enqueue_scripts', 'wppb_in_bdp_scripts_and_styles_admin', 20 );

            /*
             * Include the file managing Field Visibility features
             */
            if (file_exists(WPPB_IN_BDP_PLUGIN_DIR . '/includes/field-visibility.php'))
                include_once(WPPB_IN_BDP_PLUGIN_DIR . '/includes/field-visibility.php');

            /*
             * Include the file managing mustache vars for Userlisting and Email Customizer
             */
            if (file_exists(WPPB_IN_BDP_PLUGIN_DIR . '/includes/mustache-vars.php'))
                include_once(WPPB_IN_BDP_PLUGIN_DIR . '/includes/mustache-vars.php');


            /**
             * Remove PB avatar because BP uses its own avatar functionality
             *
             * @since v.1.0.0
             *
             * @return null
             */
            function wppb_in_bdp_remove_pb_avatar(){
                remove_filter( 'wppb_output_form_field_avatar', 'wppb_avatar_handler', 10 );
            }
            add_filter( 'wppb_output_form_field_avatar', 'wppb_in_bdp_remove_pb_avatar', 5 );

            function wppb_in_bdp_remove_pb_avatar_from_backend(){
                remove_filter( 'wppb_admin_output_form_field_avatar', 'wppb_avatar_handler', 10 );
            }
            add_filter( 'wppb_admin_output_form_field_avatar', 'wppb_in_bdp_remove_pb_avatar_from_backend', 5 );

            function wppb_in_bdp_remove_pb_avatar_save(){
                remove_action( 'wppb_backend_save_form_field', 'wppb_save_avatar_value', 10 );
            }
            add_action( 'wppb_save_form_field', 'wppb_in_bdp_remove_pb_avatar_save', 5 );

            function wppb_in_bdp_remove_pb_avatar_save_from_backend(){
                remove_action( 'wppb_save_form_field', 'wppb_save_avatar_value', 10 );
            }
            add_action( 'wppb_save_form_field', 'wppb_in_bdp_remove_pb_avatar_save_from_backend', 5 );

            function wppb_in_bdp_remove_pb_avatar_check(){
                remove_filter( 'wppb_check_form_field_avatar', 'wppb_check_avatar_value', 10 );
            }
            add_filter( 'wppb_check_form_field_avatar', 'wppb_in_bdp_remove_pb_avatar_check', 5);


            /**
             * Add a notification for removing Avatar in Manage Fields
             *
             * @since v.1.0.0
             *
             * @param string $form
             *
             * @return string $form
             */
            function wppb_in_manage_fields_display_avatar_notice( $form ){
                // add a notice to 'Avatar' field
                global $wppb_results_field;
                if ( $wppb_results_field == 'Avatar' )
                    $form .= '<div id="wppb-avatar-nag" class="wppb-backend-notice">' . __( 'Profile Builder Avatar field is disabled to allow use of BuddyPress Avatar.', 'profile-builder' ) . '</div>';

                return $form;
            }
            add_filter( 'wck_after_content_element', 'wppb_in_manage_fields_display_avatar_notice' );


            /**
             * Register the Profile Builder - BuddyPress Add-on template stack.
             *
             * @since v.1.0.0
             *
             * @return string /templates directory path
             */
            function wppb_in_bdp_get_template_directory(){
                return WPPB_IN_BDP_PLUGIN_DIR . '/templates';
            }

            if ( function_exists('bp_register_template_stack') ) {
                bp_register_template_stack('wppb_in_bdp_get_template_directory', 10);
            }


            /**
             * Add PB - BuddyPress Add-on templates for Registration, Edit Profile and Single Userlisting to the list of templates used by BuddyPress.
             *
             * Here we're basically overwriting the default BuddyPress user Profile and Edit pages with the ones created via Profile Builder and selected under BuddyPress add-on settings tab.
             *
             * @since v.1.0.0
             *
             * @return $templates - array of templates used by BuddyPress
             */
            function wppb_in_bdp_filter_template_parts($templates, $slug, $name){

                $wppb_buddypress_settings = get_option( 'wppb_buddypress_settings', 'not_found');

                if ($wppb_buddypress_settings != 'not_found') {
                    wppb_in_buddypress_settings_defaults();
                }
                switch( $slug ){

                    // Filter was triggered on BuddyPress user Profile Settings Profile Visibility page
                    case 'members/single/settings/profile':
                    // Filter was triggered on BuddyPress user Profile Edit page
                    case 'members/single/profile/edit':
                        // Check settings to see if there is an Edit-profile form set to replace the default BuddyPress "Edit" tab content
                        if (!empty($wppb_buddypress_settings['EditProfileForm'])) {
                            //Overwrite default BuddyPress profile Edit template with the Edit-profile form set in PB
                            $key = array_search('members/single/profile/edit.php', $templates);
                            $templates[$key] = 'pb-user-profile-edit.php';
                            $key = array_search('members/single/settings/profile.php', $templates);
                            $templates[$key] = 'pb-user-profile-edit.php';
                        }
                        break;

                    // Filter was triggered on BuddyPress user Profile View page
                    case 'members/single/profile/profile-loop':
                        // Check settings to see if there is a Single-userlisting set to replace the default BuddyPress "Profile" tab content
                        if (!empty($wppb_buddypress_settings['UserListing'])) {
                            //Overwrite default BuddyPress user Profile template with the Single-userlisting set in PB
                            $key = array_search('members/single/profile/profile-loop.php', $templates);
                            $templates[$key] = 'pb-user-profile-view.php';
                        }
                        break;

                    // Filter was triggered on BuddyPress user Registration page
                    case 'members/register':
                        // Check settings to see if there is a Single-userlisting set to replace the default BuddyPress "Profile" tab content
                        if (!empty($wppb_buddypress_settings['RegistrationForm'])) {
                            //Overwrite default BuddyPress user Profile template with the Single-userlisting set in PB
                            $key = array_search('members/register.php', $templates);
                            $templates[$key] = 'pb-user-registration.php';
                        }
                        break;

                    // Filter was triggered on BuddyPress Members page
                    case 'members/index':
                        // Check settings to see if there is a Single-userlisting set to replace the default BuddyPress "Profile" tab content
                        if (!empty($wppb_buddypress_settings['AllUserListing'])) {
                            //Overwrite default BuddyPress user Profile template with the Single-userlisting set in PB
                            $key = array_search('members/index.php', $templates);
                            $templates[$key] = 'pb-user-listing.php';
                        }
                        break;
                }

                return $templates;

            }
            add_filter( 'bp_get_template_part' , 'wppb_in_bdp_filter_template_parts', 10, 3);

            /**
             * Function that keeps User Meta for Last Active updated by action.
             *
             * Used in Userlisting sorting by last active
             *
             * @since 1.0.0
             *
             * @param $user_id
             * @param $time
             *
             * @return null
             */
            function wppb_in_bdp_update_last_activity_usermeta( $user_id, $time ){
                update_user_meta( $user_id, 'wppb_bdp_last_activity', $time );
            }
            add_action( 'bp_core_user_updated_last_activity', 'wppb_in_bdp_update_last_activity_usermeta', 10, 2 );


            /**
             * Redirect various BP pages to PB equivalent
             *
             * @since 1.0.0
             *
             * @return null
             */
            function wppb_in_bdp_redirect_to_manage_fields(){
                global $pagenow;
                $wppb_generalSettings = get_option( 'wppb_general_settings' );
                if ( isset( $wppb_generalSettings['emailConfirmation'] ) && $wppb_generalSettings['emailConfirmation'] == 'yes' ) {
                    $redirect_array = array(
                        'bp-profile-setup'  => site_url( '/wp-admin/admin.php?page=manage-fields' ),
                        'bp-signups'        => site_url( '/wp-admin/users.php?page=unconfirmed_emails' )
                    );
                    if ( $pagenow == 'users.php' && isset( $_GET['page'] ) && array_key_exists  ( sanitize_text_field( $_GET['page'] ), $redirect_array ) ) {
                        wp_safe_redirect( $redirect_array[ sanitize_text_field ( $_GET['page'] ) ] );
                    }
                }
            }
            add_action( 'admin_init', 'wppb_in_bdp_redirect_to_manage_fields' );


            /**
             * emove Manage signups if email confirmation is off.
             *
             * @since 1.0.0
             *
             * @return null
             */
            function wppb_in_bdp_remove_manage_signups_when_ec_off(){
                $wppb_generalSettings = get_option( 'wppb_general_settings' );
                if ( isset( $wppb_generalSettings['emailConfirmation'] ) && $wppb_generalSettings['emailConfirmation'] == 'no' ) {
                    remove_submenu_page('users.php', 'bp-signups');
                }
            }
            add_action( 'admin_menu', 'wppb_in_bdp_remove_manage_signups_when_ec_off', 999 );

        }

        else {
            /**
             * Display notice if BuddyPress is not active
             *
             * @since 1.0.0
             *
             * @return null
             */
            function wppb_in_bdp_admin_notice() {
                ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e( 'BuddyPress needs to be installed and activated for Profile Builder - BuddyPress Integration Add-on to work as expected!', 'profile-builder' ); ?></p>
                </div>
                <?php
            }
            add_action( 'admin_notices', 'wppb_in_bdp_admin_notice' );
        }
    }
}
add_action( 'plugins_loaded', 'wppb_in_bdp_plugin_init', 11 );

/**
 * Set Default Settings if not present
 *
 * Default Registration, Edit Profile, Single Userlisting and All Userlisting
 *
 * @since 1.0.0
 *
 * @return null
 */
function wppb_in_buddypress_settings_defaults(){
    $wppb_buddypress_settings = get_option( 'wppb_buddypress_settings', 'not_found' );

    // set default values
    if ( $wppb_buddypress_settings == 'not_found' ) {

        // Add a Customized Userlisting that uses bp_visibility attribute.
        $ul_post_id = wp_insert_post( array( 'post_title' => 'Default Userlisting for BuddyPress', 'post_status' => 'publish', 'post_author' => get_current_user_id(), 'post_type' => 'wppb-ul-cpt', 'post_content' => 'Default Userlisting for BuddyPress integration' ), true );
        wppb_in_bdp_set_default_userlisting_templates( $ul_post_id );

        $registration_form = 'wppb-default-register';
        $edit_profile_form = 'wppb-default-edit-profile';
        $user_listing = 'Default Userlisting for BuddyPress';
        $all_user_listing = 'yes';

        update_option('wppb_buddypress_settings', array(
            'UserListing' => $user_listing,
            'AllUserListing' => $all_user_listing,
            'EditProfileForm' => $edit_profile_form,
            'RegistrationForm' => $registration_form,
        ));
    }
}


/**
 * Resets User listing template
 *
 * @since v.1.0.0
 *
 * @param $ul_post_id
 *
 * @return null
 */
function wppb_in_bdp_set_default_userlisting_templates( $ul_post_id ){
    $ul_post_id = (int) $ul_post_id;
    update_post_meta( $ul_post_id, 'wppb-ul-templates', wppb_in_bdp_generate_allUserlisting_content() );
    update_post_meta( $ul_post_id, 'wppb-single-ul-templates', wppb_in_bdp_generate_singleUserlisting_content() );

    update_post_meta( $ul_post_id, 'wppb_ul_page_settings', array( array(
        'default-sorting-criteria' => 'wppb_bdp_last_activity',
        'default-sorting-order' => 'desc',
        'roles-to-display' => '*',
        'number-of-userspage' => 20,
        'avatar-size-all-userlisting' => 40,
        'avatar-size-single-userlisting' => 60,
        'visible-only-to-logged-in-users' => '',
        'visible-to-following-roles' => '*'
    ) ) );
}

/**
 * Function that generates the default template for all user listing
 *
 * @since v.1.0.0
 *
 * @return string default_all_userlisting_template
 */
function wppb_in_bdp_generate_allUserlisting_content(){
    return '{{{extra_search_all_fields}}} <br>
{{{bp_my_friends_tab}}}
<span class="wppb-buddypress-sort-by-text">Sort by:</span>
<ul class="wppb-buddypress-sort-by">
  <li> {{{sort_bp_last_active}}} </li>
  <li> {{{sort_display_name}}} </li>
  <li> {{{sort_registration_date}}} </li>
</ul>
<table class="wppb-table wppb-buddypress-table">
	<tbody>
		{{#users}}
		<tr>
          <td data-label="Avatar" class="wppb-buddypress-avatar"><a href="{{{more_info_url}}}">
            <img src="{{{bp_avatar}}}" class="avatar" width="50" height="50" alt="Profile picture of {{meta_display_name}}">
          </a></td>
		  <td data-label="Name" class="wppb-name"><a href="{{{more_info_url}}}">{{meta_display_name}}</a>{{{bp_latest_update}}}<br>
            <i>{{bp_last_active}}</i></td>
		  <td data-label="Friends" class="wppb-buddypress-friends">{{{bp_add_friend}}}</td>
		</tr>
		{{/users}}
	</tbody>
</table>
{{{pagination}}}';
}


/**
 * Function that generates the default template for single user listing
 *
 * @since v.1.0.0
 *
 * @return string default_single_userlisting_template
 */
function wppb_in_bdp_generate_singleUserlisting_content(){
    return '
<ul class="wppb-profile">
  <li>
    <h3>' . __( 'Name', 'profile-builder' ) . '</h3>
  </li>
  <li>
    <label>' . __( 'Username:', 'profile-builder' ) . '</label>
    <span>{{meta_user_name}}</span>
  </li>
  <li bp_visibility="first_name" user_id="{{user_id}}">
    <label>' . __( 'First Name:', 'profile-builder' ) . '</label>
    <span>{{meta_first_name}}</span>
  </li>
  <li bp_visibility="last_name" user_id="{{user_id}}">
    <label>' . __( 'Last Name:', 'profile-builder' ) . '</label>
    <span>{{meta_last_name}}</span>
  </li>
  <li bp_visibility="nickname" user_id="{{user_id}}">
    <label>' . __( 'Nickname:', 'profile-builder' ) . '</label>
    <span>{{meta_nickname}}</span>
  </li>
  <li>
    <label>' . __( 'Display name:', 'profile-builder' ) . '</label>
	<span>{{meta_display_name}}</span>
  </li>
  <li bp_visibility="website" user_id="{{user_id}}">
    <h3>' . __( 'Contact Info', 'profile-builder' ) . '</h3>
  </li>
  <li bp_visibility="website" user_id="{{user_id}}">
  	<label>' . __( 'Website:', 'profile-builder' ) . '</label>
	<span>{{meta_website}}</span>
  </li>
  <li bp_visibility="biographical_info" user_id="{{user_id}}">
    <h3>' . __( 'About Yourself', 'profile-builder' ) . '</h3>
  </li>
  <li bp_visibility="biographical_info" user_id="{{user_id}}">
	<label>' . __( 'Biographical Info:', 'profile-builder' ) . '</label>
	<span>{{{meta_biographical_info}}}</span>
  </li>
</ul>';
}
