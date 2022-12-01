<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Function which returns the same field-format over and over again.
 *
 * @since v.2.0
 *
 * @param string $field
 * @param string $field_title
 *
 * @return string
 */
function wppb_field_format ( $field_title, $field ){

	return trim( $field_title ).' ( '.trim( $field ).' )';
}


/**
 * Add a notification for either the Username or the Email field letting the user know that, even though it is there, it won't do anything
 *
 * @since v.2.0
 *
 * @param string $form
 * @param integer $id
 * @param string $value
 *
 * @return string $form
 */

function wppb_manage_fields_display_field_title_slug( $form ){
    // add a notice to fields
	global $wppb_results_field;
    switch ($wppb_results_field){
        case 'Default - Username':
            $wppb_generalSettings = get_option( 'wppb_general_settings', 'not_found' );
            if ( $wppb_generalSettings != 'not_found' && $wppb_generalSettings['loginWith'] == 'email' ) {
                $form .= '<div id="wppb-login-email-nag" class="wppb-backend-notice">' . sprintf(__('Login is set to be done using the Email. This field will NOT appear in the front-end! ( you can change these settings under the "%s" tab )', 'profile-builder'), '<a href="' . admin_url('admin.php?page=profile-builder-general-settings') . '" target="_blank">' . __('General Settings', 'profile-builder') . '</a>') . '</div>';
            }
            break;
        case 'Default - Display name publicly as':
            $form .= '<div id="wppb-display-name-nag" class="wppb-backend-notice">' . __( 'Display name publicly as - only appears on the Edit Profile page!', 'profile-builder' ) . '</div>';
            break;
        case 'Default - Blog Details':
            $form .= '<div id="wppb-blog-details-nag" class="wppb-backend-notice">' . __( 'Blog Details - only appears on the Registration page!', 'profile-builder' ) . '</div>';
            break;
    }

    return $form;
}

add_filter( 'wck_after_content_element', 'wppb_manage_fields_display_field_title_slug' );

/**
 * Check if field type is 'Default - Display name publicly as' so we can add a notification for it
 *
 * @since v.2.2
 *
 * @param string $wck_element_class
 * @param string $meta
 * @param array $results
 * @param integer $element_id
 *
 */
function wppb_manage_fields_display_name_notice( $wck_element_class, $meta, $results, $element_id ) {
	global $wppb_results_field;

	$wppb_results_field = $results[$element_id]['field'];
}
add_filter( 'wck_element_class_wppb_manage_fields', 'wppb_manage_fields_display_name_notice', 10, 4 );



/**
 * Function that adds a custom class to the existing container
 *
 * @since v.2.0
 *
 * @param string $update_container_class - the new class name
 * @param string $meta - the name of the meta
 * @param array $results
 * @param integer $element_id - the ID of the element
 *
 * @return string
 */
function wppb_update_container_class( $update_container_class, $meta, $results, $element_id ) {
	$wppb_element_type = Wordpress_Creation_Kit_PB::wck_generate_slug( $results[$element_id]["field"] );

	return "class='wck_update_container update_container_$meta update_container_$wppb_element_type element_type_$wppb_element_type'";
}
add_filter( 'wck_update_container_class_wppb_manage_fields', 'wppb_update_container_class', 10, 4 );


/**
 * Function that adds a custom class to the existing element
 *
 * @since v.2.0
 *
 * @param string $element_class - the new class name
 * @param string $meta - the name of the meta
 * @param array $results
 * @param integer $element_id - the ID of the element
 *
 * @return string
 */
function wppb_element_class( $element_class, $meta, $results, $element_id ){
	$wppb_element_type = Wordpress_Creation_Kit_PB::wck_generate_slug( $results[$element_id]["field"] );

	return "class='element_type_$wppb_element_type added_fields_list'";
}
add_filter( 'wck_element_class_wppb_manage_fields', 'wppb_element_class', 10, 4 );

/**
 * Functions to check password length and strength
 *
 * @since v.2.0
 */
/* on add user and update profile from WP admin area */
add_action( 'user_profile_update_errors', 'wppb_password_check_on_profile_update', 0, 3 );
function wppb_password_check_on_profile_update( $errors, $update, $user ){
    wppb_password_check_extra_conditions( $errors, $user );
}

/* on reset password */
add_action( 'validate_password_reset', 'wppb_password_check_extra_conditions', 10, 2 );
function wppb_password_check_extra_conditions( $errors, $user ){
    $password = ( isset( $_POST[ 'pass1' ] ) && trim( $_POST[ 'pass1' ] ) ) ? $_POST[ 'pass1' ] : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

    if( $password && ( !isset( $_POST['pw_weak'] ) || $_POST['pw_weak'] != 'on' ) ){
        $wppb_generalSettings = get_option( 'wppb_general_settings' );

        if( !empty( $wppb_generalSettings['minimum_password_length'] ) ){
            if( strlen( $password ) < $wppb_generalSettings['minimum_password_length'] )
                $errors->add( 'pass', sprintf( __( '<strong>ERROR</strong>: The password must have the minimum length of %s characters', 'profile-builder' ), $wppb_generalSettings['minimum_password_length'] ) );
        }

        if( isset( $_POST['wppb_password_strength'] ) && !empty( $wppb_generalSettings['minimum_password_strength'] ) ){
            $password_strength_array = array( 'short' => 0, 'bad' => 1, 'good' => 2, 'strong' => 3 );
            $password_strength_text = array( 'short' => __( 'Very weak', 'profile-builder' ), 'bad' => __( 'Weak', 'profile-builder' ), 'good' => __( 'Medium', 'profile-builder' ), 'strong' => __( 'Strong', 'profile-builder' ) );

            foreach( $password_strength_text as $psr_key => $psr_text ){
                if( $psr_text == sanitize_text_field( $_POST['wppb_password_strength'] ) ){
                    $password_strength_result_slug = $psr_key;
                    break;
                }
            }

            if( !empty( $password_strength_result_slug ) ){
                if( $password_strength_array[$password_strength_result_slug] < $password_strength_array[$wppb_generalSettings['minimum_password_strength']] )
                    $errors->add( 'pass', sprintf( __( '<strong>ERROR</strong>: The password must have a minimum strength of %s', 'profile-builder' ), $password_strength_text[$wppb_generalSettings['minimum_password_strength']] ) );
            }
        }
    }

    return $errors;
}

/* we need to create a hidden field that contains the results of the password strength from the js strength tester */
add_action( 'admin_footer', 'wppb_add_hidden_password_strength_on_backend' );
add_action( 'login_footer', 'wppb_add_hidden_password_strength_on_backend' );
function wppb_add_hidden_password_strength_on_backend(){
    if( $GLOBALS['pagenow'] == 'profile.php' || $GLOBALS['pagenow'] == 'user-new.php' || ( $GLOBALS['pagenow'] == 'wp-login.php' && isset( $_GET['action'] ) && ( $_GET['action'] === 'rp' || $_GET['action'] === 'resetpass' ) ) ){
        $wppb_generalSettings = get_option( 'wppb_general_settings' );
        if( !empty( $wppb_generalSettings['minimum_password_strength'] ) ){
            ?>
            <script type="text/javascript">
                jQuery( document ).ready( function() {
                    var passswordStrengthResult = jQuery( '#pass-strength-result' );
                    // Check for password strength meter
                    if ( passswordStrengthResult.length ) {
                        // Attach submit event to form
                        passswordStrengthResult.parents( 'form' ).on( 'submit', function() {
                            // Store check results in hidden field
                            jQuery( this ).append( '<input type="hidden" name="wppb_password_strength" value="' + passswordStrengthResult.text() + '">' );
                        });
                    }
                });
            </script>
            <?php
        }
    }
}


/* Modify the Add Entry buttons for WCK metaboxes according to context */
add_filter( 'wck_add_entry_button', 'wppb_change_add_entry_button', 10, 2 );
function wppb_change_add_entry_button( $string, $meta ){
    if( $meta == 'wppb_manage_fields' || $meta == 'wppb_epf_fields' || $meta == 'wppb_rf_fields' ){
        return __( "Add Field", 'profile-builder' );
    }elseif( $meta == 'wppb_epf_page_settings' || $meta == 'wppb_rf_page_settings' || $meta == 'wppb_ul_page_settings' ){
        return __( "Save Settings", 'profile-builder' );
    }

    return $string;
}

/**
 * add links on plugin page
 */
add_filter( 'plugin_action_links', 'wppb_plugin_action_links', 10, 2 );
function wppb_plugin_action_links( $links, $file ) {
    if ( $file != WPPB_PLUGIN_BASENAME ) {
        return $links;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return $links;
    }

    $settings_link = sprintf( '<a href="%1$s">%2$s</a>',
        menu_page_url( 'profile-builder-general-settings', false ),
        esc_html( __( 'Settings', 'profile-builder' ) ) );

    array_unshift( $links, $settings_link );

    return $links;
}

/**
 * add links on plugin page
 */
add_filter( 'plugin_row_meta', 'wppb_plugin_row_meta', 10, 2 );
function wppb_plugin_row_meta( $links, $file ) {
    if ( WPPB_PLUGIN_BASENAME == $file ) {
        $row_meta = array(
            'docs'    => '<a href="' . esc_url( 'https://www.cozmoslabs.com/docs/profile-builder-2/' ) . '" target="_blank" aria-label="' . esc_attr__( 'View Profile Builder documentation', 'profile-builder' ) . '">' . esc_html__( 'Docs', 'profile-builder' ) . '</a>',
        );

        return array_merge( $links, $row_meta );
    }

    return (array) $links;
}



/* In plugin notifications */
add_action( 'admin_init', 'wppb_add_plugin_notifications' );
function wppb_add_plugin_notifications() {
    /* initiate the plugin notifications class */
    $notifications = WPPB_Plugin_Notifications::get_instance();
    /* this must be unique */
    // $notification_id = 'wppb_migrated_free_add_ons';

	// $message = '<p style="margin-top: 16px; font-size: 15px;">' . __( 'All the free add-ons have been migrated to the main plugin. Their old individual plugins have been disabled and you can delete them from your site if you were using them: <ul><li>Profile Builder - Custom CSS Classes on fields</li><li>Profile Builder - Customization Toolbox Add-On</li><li>Profile Builder - Email Confirmation Field</li><li>Profile Builder - GDPR Communication Preferences</li><li>Profile Builder - Import and Export Add-On</li><li>Profile Builder - Labels Edit Add-On</li><li>Profile Builder - Maximum Character Length Add-On</li><li>Profile Builder - Multiple Admin E-mails Add-On</li><li>Profile Builder - Placeholder Labels Add-On</li></ul>', 'profile-builder' ) . '</p>';
    // // be careful to use wppb_dismiss_admin_notification as query arg
    // $message .= '<p><a href="https://www.cozmoslabs.com/277540-profile-builder-enhancements-free-addons-now-part-of-main-plugin/" target="_blank" class="button-primary">' . __( 'See details', 'profile-builder' ) . '</a></p>';
    // $message .= '<a href="' . add_query_arg( array( 'wppb_dismiss_admin_notification' => $notification_id ) ) . '" type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'profile-builder' ) . '</span></a>';

    // $notifications->add_notification( $notification_id, $message, 'wppb-notice notice notice-info', false );

	if ( did_action( 'elementor/loaded' ) ) {

		$notification_id = 'wppb_elementor_styling_notice';

		$message  = '<img style="float: left; margin: 10px 12px 10px 0; max-width: 100px;" src="'.WPPB_PLUGIN_URL.'assets/images/elementor_logo.png" alt="Elementor Logo"/>';
		$message .= '<p style="margin-top: 16px; font-size: 15px;">' . sprintf( __( 'You can now style %s forms from the %s interface. To get started, add a form widget to a page through %s and go to the <strong>Style</strong> tab.', 'profile-builder' ), '<strong>Profile Builder</strong>', '<strong>Elementor</strong>', '<strong>Elementor</strong>') . '</p>';
		// be careful to use wppb_dismiss_admin_notification as query arg
		$message .= '<p><a href="https://www.cozmoslabs.com/docs/profile-builder-2/integration-with-elementor/" target="_blank" class="button-primary">' . __( 'See details', 'profile-builder' ) . '</a></p>';
		$message .= '<a href="' . add_query_arg( array( 'wppb_dismiss_admin_notification' => $notification_id ) ) . '" type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'profile-builder' ) . '</span></a>';

		$notifications->add_notification( $notification_id, $message, 'wppb-notice notice notice-info', false );

	}
}


/* hook to create pages for out forms when a user press the create pages/setup button */
add_action( 'admin_init', 'wppb_create_form_pages' );
function wppb_create_form_pages(){
    if( isset( $_GET['page'] ) && $_GET['page'] === 'profile-builder-basic-info' && isset( $_GET['wppb_create_pages'] ) && $_GET['wppb_create_pages'] === 'true' ){

        $wppb_pages_created = get_option( 'wppb_pages_created' );

        if( empty( $wppb_pages_created ) || ( isset( $_GET['wppb_force_create_pages'] ) && $_GET['wppb_force_create_pages'] === 'true' ) ) {
            $register_page = array(
                'post_title' => 'Register',
                'post_content' => '[wppb-register]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            $register_id = wp_insert_post($register_page);

            $edit_page = array(
                'post_title' => 'Edit Profile',
                'post_content' => '[wppb-edit-profile]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            $edit_id = wp_insert_post($edit_page);

            $login_page = array(
                'post_title' => 'Log In',
                'post_content' => '[wppb-login]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            $login_id = wp_insert_post($login_page);

            update_option('wppb_pages_created', 'true' );

            wp_safe_redirect( admin_url('edit.php?s=%5Bwppb-&post_status=all&post_type=page&action=-1&m=0&paged=1&action2=-1') );
        }
    }
}

/**
 * Function that prepares labels to pass to the WCK api...we have a quirk in wck api that labels are wrapped
 * in %label%...so if we want to have % inside the label for example 25% off...we will have a bad time
 * @param string $label
 * @return string|string[]
 */
function wppb_prepare_wck_labels( $label ){
    return trim( str_replace( '%', '&#37;', $label ) );
}

/**
 * Function that returns the reserved meta name list
 */
function wppb_get_reserved_meta_name_list( $all_fields, $posted_values ){

    $unique_meta_name_list = array( 'first_name', 'last_name', 'nickname', 'description' );

    // Default contact methods were removed in WP 3.6. A filter dictates contact methods.
    if ( apply_filters( 'wppb_remove_default_contact_methods', get_site_option( 'initial_db_version' ) < 23588 ) ){
        $unique_meta_name_list[] = 'aim';
        $unique_meta_name_list[] = 'yim';
        $unique_meta_name_list[] = 'jabber';
    }

    $add_reserved = true;

    $reserved_meta_names = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page',
        'comments_popup', 'custom', 'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug', 'embed', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'map', 'minute', 'monthnum', 'more',
        'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type',
        'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'status', 'subpost',
        'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'terms', 'theme', 'title', 'type', 'w', 'withcomments', 'withoutcomments', 'year' );

    $args = array(
        'public'   => true,
        '_builtin' => true
    );
    $post_types = get_post_types( $args, 'names', 'or' );
    $taxonomies = get_taxonomies( $args, 'names', 'or' );


    /*reserved meta names were added in PB 3.1.2 so to avoid the situation where someone updates an already existing field
     with a reserved name and gets an error check if it is an update or new field */
    if( !empty( $all_fields ) && !empty($posted_values['id'] ) && !empty($posted_values['meta-name']) ){
        foreach( $all_fields as $field ){
            if( $field['id'] === $posted_values['id'] && $field['meta-name'] === $posted_values['meta-name'] ){//it is an update
                $add_reserved = false;
            }
        }
    }

    if( $add_reserved )
        $unique_meta_name_list = array_merge( $unique_meta_name_list, $reserved_meta_names, $post_types, $taxonomies );

    return apply_filters ( 'wppb_unique_meta_name_list', $unique_meta_name_list );
}
