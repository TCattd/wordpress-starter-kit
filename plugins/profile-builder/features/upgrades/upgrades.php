<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once ( WPPB_PLUGIN_DIR.'/features/upgrades/upgrades-functions.php' );

/**
 * Function that assures backwards compatibility for all future versions, where this is needed
 *
 * @since v.1.3.13
 *
 * @return void
 */
function wppb_update_patch(){
	if ( !get_option( 'wppb_version' ) ) {
		add_option( 'wppb_version', '1.3.13' );
		
		do_action( 'wppb_set_initial_version_number', PROFILE_BUILDER_VERSION );
	}

	$wppb_version = get_option( 'wppb_version' );
	
	do_action( 'wppb_before_default_changes', PROFILE_BUILDER_VERSION, $wppb_version );
	
	if ( version_compare( PROFILE_BUILDER_VERSION, $wppb_version, '>' ) ) {
        $paid_versions = array( 'Profile Builder Pro', 'Profile Builder Hobbyist', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Basic' );

		if ( in_array( PROFILE_BUILDER, $paid_versions ) ){

			/* stopped creating them on 01.02.2016 */
			/*$upload_dir = wp_upload_dir();
			wp_mkdir_p( $upload_dir['basedir'].'/profile_builder' );
			wp_mkdir_p( $upload_dir['basedir'].'/profile_builder/attachments/' );
			wp_mkdir_p( $upload_dir['basedir'].'/profile_builder/avatars/' );*/
			
			// Flush the rewrite rules and add them, if need be, the proper way.
			if ( function_exists( 'wppb_flush_rewrite_rules' ) )
				wppb_flush_rewrite_rules();
			
			wppb_pro_hobbyist_v1_3_13();
		}
    
        $pro_versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited' );

		if ( in_array( PROFILE_BUILDER, $pro_versions ) ){
			wppb_pro_v1_3_15();
		}
		
		update_option( 'wppb_version', PROFILE_BUILDER_VERSION );
	}

	//this should run only once, mainly if the old version is < 2.0 (can be anything)
	if ( version_compare( $wppb_version, 2.0, '<' ) ) {
        $all_versions = array( 'Profile Builder Pro', 'Profile Builder Hobbyist', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Basic', 'Profile Builder Free' );

		if ( in_array( PROFILE_BUILDER, $all_versions ) ){
			wppb_pro_hobbyist_free_v2_0();
		}
		
        $pro_versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited' );

		if ( in_array( PROFILE_BUILDER, $pro_versions ) ){
			wppb_pro_userlisting_compatibility_upgrade();
			wppb_pro_email_customizer_compatibility_upgrade();
		}
	}

	// this should run only once, mainly if the old version is < 2.2.5 (can be anything)
	if ( version_compare( $wppb_version, '2.2.5', '<' ) ) {
        $pro_versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited' );

		if ( in_array( PROFILE_BUILDER, $pro_versions ) ){
			wppb_new_custom_redirects_compatibility();
		}
	}

    if ( version_compare( $wppb_version, '2.2.5', '<=' ) ) {
        if( is_multisite() ){
            $wppb_general_settings = get_option( 'wppb_general_settings', 'not_set' );
			if ( $wppb_general_settings != 'not_set' ) {
				$wppb_general_settings['emailConfirmation'] = 'yes';
				update_option('wppb_general_settings', $wppb_general_settings);
			}
        }

    }
	
	do_action ( 'wppb_after_default_changes', PROFILE_BUILDER_VERSION, $wppb_version );	
}
add_action ( 'init', 'wppb_update_patch' );


/**
 *  before disabling the old plugin addons save their status in the db
 */
add_action( 'plugins_loaded', 'wppb_save_old_add_ons_status', 11 );
function wppb_save_old_add_ons_status(){
    $old_addon_list = wppb_get_old_addons_slug_list();
    $old_addons_status = get_option( 'wppb_old_add_ons_status', array() );

    //if it's triggered in the frontend we need this include
    if( !function_exists('is_plugin_active') )
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    foreach( $old_addon_list as $addon_slug ){
        if( !isset( $old_addons_status[$addon_slug] ) ) {//don't change the status, just take the first run through
            if ( is_plugin_active($addon_slug) )
                $old_addons_status[$addon_slug] = true;
            else
                $old_addons_status[$addon_slug] = false;
        }
    }

    update_option( 'wppb_old_add_ons_status', $old_addons_status );
}


/**
 * Deactivate the old addons as plugins
 */
add_action( 'plugins_loaded', 'wppb_disable_old_add_ons', 12 );
function wppb_disable_old_add_ons(){

    //if it's triggered in the frontend we need this include
    if( !function_exists('is_plugin_active') )
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    $old_addons_list = wppb_get_old_addons_slug_list();
    $deactivated_addons = 0;
    foreach( $old_addons_list as $addon_slug ){
        if( is_plugin_active($addon_slug) ){
            if( is_multisite() ){
                if( is_plugin_active_for_network($addon_slug) )
                    deactivate_plugins($addon_slug, true);
                else
                    deactivate_plugins($addon_slug, true, false);
            }
            else {
                deactivate_plugins($addon_slug, true);
            }
            $deactivated_addons++;
        }
    }
    if ( isset( $_GET['activate'] ) && $deactivated_addons === 1 ){
        add_action( 'load-plugins.php',
            function(){
                add_action( 'in_admin_header',
                    function(){
                        add_filter( 'gettext', 'wppb_disable_old_add_ons_notice', 99, 3 );
                    }
                );
            }
        );
    } elseif ( isset( $_GET['activate-multi'] ) && $deactivated_addons !== 0 ){
        add_action( 'admin_notices', 'wppb_disable_old_add_ons_notice_multi' );
    }
}

function wppb_disable_old_add_ons_notice( $translated_text, $untranslated_text, $domain )
{
    $old = array(
        "Plugin activated."
    );

    $new = "This Profile Builder add-on has been migrated to the main plugin and is no longer used. You can delete it.";

    if ( in_array( $untranslated_text, $old, true ) )
    {
        $translated_text = $new;
        remove_filter( current_filter(), __FUNCTION__, 99 );
    }
    return $translated_text;
}

function wppb_disable_old_add_ons_notice_multi() {
    ?>
    <div id="message" class="updated notice is-dismissible">
        <p><?php esc_html_e( 'This Profile Builder add-on has been migrated to the main plugin and is no longer used. You can delete it.', 'profile-builder' ); ?></p>
    </div>
    <?php
}

add_action( 'plugins_loaded', 'wppb_generate_new_free_add_ons_setting', 13 );
function wppb_generate_new_free_add_ons_setting(){
    $wppb_free_add_ons_settings = get_option( 'wppb_free_add_ons_settings', array() );
    if( empty( $wppb_free_add_ons_settings ) ){

        $old_addons_list = wppb_get_old_addons_slug_list();
        foreach( $old_addons_list as $addon_slug ){

            switch ($addon_slug) {
                case 'pb-add-on-customization-toolbox/index.php':
                    if( !wppb_was_addon_active_as_plugin( $addon_slug ) ){
                        //it might have been active at some point and we need to remove the settings so they don't get activated in Advance Settings
                        $toolbox_settings = array( 'wppb_toolbox_forms_settings', 'wppb_toolbox_fields_settings', 'wppb_toolbox_userlisting_settings', 'wppb_toolbox_shortcodes_settings', 'wppb_toolbox_admin_settings' );
                        foreach( $toolbox_settings as $toolbox_setting ){
                            delete_option( $toolbox_setting );
                        }
                    }
                    break;
                case 'pb-add-on-gdpr-communication-preferences/pb-gdpr-communication-preferences.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_free_add_ons_settings['gdpr-communication-preferences'] = true;
                    else
                        $wppb_free_add_ons_settings['gdpr-communication-preferences'] = false;
                    break;
                case 'pb-add-on-labels-edit/pble.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_free_add_ons_settings['labels-edit'] = true;
                    else
                        $wppb_free_add_ons_settings['labels-edit'] = false;
                    break;
                case 'pb-add-on-maximum-character-length/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_free_add_ons_settings['maximum-character-length'] = true;
                    else
                        $wppb_free_add_ons_settings['maximum-character-length'] = false;
                    break;
                case 'pb-add-on-custom-css-classes-on-fields/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_free_add_ons_settings['custom-css-classes-on-fields'] = true;
                    else
                        $wppb_free_add_ons_settings['custom-css-classes-on-fields'] = false;
                    break;
                case 'pb-add-on-import-export/pbie.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_free_add_ons_settings['import-export'] = true;
                    else
                        $wppb_free_add_ons_settings['import-export'] = false;
                    break;
            }


        }

        add_option( 'wppb_free_add_ons_settings', $wppb_free_add_ons_settings );
    }
}


add_action( 'plugins_loaded', 'wppb_generate_new_advanced_add_ons_setting', 13 );
function wppb_generate_new_advanced_add_ons_setting(){
    $wppb_advanced_add_ons_settings = get_option( 'wppb_advanced_add_ons_settings', array() );
    if( empty( $wppb_advanced_add_ons_settings ) ){

        $old_addons_list = wppb_get_old_addons_slug_list();
        foreach( $old_addons_list as $addon_slug ){

            switch ($addon_slug) {
                case 'pb-add-on-buddypress/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['buddypress'] = true;
                    else
                        $wppb_advanced_add_ons_settings['buddypress'] = false;
                    break;
                case 'pb-add-on-social-connect/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['social-connect'] = true;
                    else
                        $wppb_advanced_add_ons_settings['social-connect'] = false;
                    break;
                case 'pb-add-on-woocommerce/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['woocommerce'] = true;
                    else
                        $wppb_advanced_add_ons_settings['woocommerce'] = false;
                    break;
                case 'pb-add-on-multi-step-forms/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['multi-step-forms'] = true;
                    else
                        $wppb_advanced_add_ons_settings['multi-step-forms'] = false;
                    break;
                case 'pb-add-on-mailchimp-integration/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['mailchimp-integration'] = true;
                    else
                        $wppb_advanced_add_ons_settings['mailchimp-integration'] = false;
                    break;
                case 'pb-add-on-bbpress/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['bbpress'] = true;
                    else
                        $wppb_advanced_add_ons_settings['bbpress'] = false;
                    break;
                case 'pb-add-on-campaign-monitor/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['campaign-monitor'] = true;
                    else
                        $wppb_advanced_add_ons_settings['campaign-monitor'] = false;
                    break;
                case 'pb-add-on-field-visibility/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['field-visibility'] = true;
                    else
                        $wppb_advanced_add_ons_settings['field-visibility'] = false;
                    break;
                case 'pb-add-on-edit-profile-approved-by-admin/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['edit-profile-approved-by-admin'] = true;
                    else
                        $wppb_advanced_add_ons_settings['edit-profile-approved-by-admin'] = false;
                    break;
                case 'pb-add-on-custom-profile-menus/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['custom-profile-menus'] = true;
                    else
                        $wppb_advanced_add_ons_settings['custom-profile-menus'] = false;
                    break;
                case 'pb-add-on-mailpoet-integration/index.php':
                    if( wppb_was_addon_active_as_plugin( $addon_slug ) )
                        $wppb_advanced_add_ons_settings['mailpoet-integration'] = true;
                    else
                        $wppb_advanced_add_ons_settings['mailpoet-integration'] = false;
                    break;
            }


        }

        add_option( 'wppb_advanced_add_ons_settings', $wppb_advanced_add_ons_settings );
    }
}