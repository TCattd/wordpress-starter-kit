<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Functions Load
 *
 */

// whitelist options, you can add more register_settings changing the second parameter
function wppb_register_settings() {
	register_setting( 'wppb_option_group', 'wppb_default_settings' );
	register_setting( 'wppb_general_settings', 'wppb_general_settings', 'wppb_general_settings_sanitize' );
	register_setting( 'wppb_display_admin_settings', 'wppb_display_admin_settings' );
	register_setting( 'wppb_profile_builder_pro_serial', 'wppb_profile_builder_pro_serial' );
	register_setting( 'wppb_profile_builder_hobbyist_serial', 'wppb_profile_builder_hobbyist_serial' );
	register_setting( 'wppb_module_settings', 'wppb_module_settings' );
	register_setting( 'wppb_module_settings_description', 'wppb_module_settings_description' );
	register_setting( 'customRedirectSettings', 'customRedirectSettings' );
	register_setting( 'customUserListingSettings', 'customUserListingSettings' );
	register_setting( 'reCaptchaSettings', 'reCaptchaSettings' );
	register_setting( 'emailCustomizer', 'emailCustomizer' );
	register_setting( 'wppb_content_restriction_settings', 'wppb_content_restriction_settings' );
	register_setting( 'wppb_private_website_settings', 'wppb_private_website_settings' );
    register_setting( 'wppb_two_factor_authentication_settings', 'wppb_two_factor_authentication_settings' );
}




// WPML support
function wppb_icl_t( $context, $name, $value, $kses = false ){

	if( $kses === false ){
		if( function_exists( 'icl_t' ) )
			return icl_t( $context, $name, $value );
		else
			return $value;
	} else {
		if( function_exists( 'icl_t' ) )
			return wp_kses_post( icl_t( $context, $name, $value ) );
		else
			return wp_kses_post( $value );
	}

}

function wppb_icl_register_string( $context, $name, $value ) {
    if( function_exists( 'icl_register_string' )) {
        if ( $name === 'msf_previous_button_text_translation' || $name === 'msf_next_button_text_translation' )
            $wpml_default_lang = 'en';
        else
            $wpml_default_lang = apply_filters('wpml_default_language', NULL );
        $allow_empty_value = false;
        icl_register_string($context, $name , $value , $allow_empty_value , $wpml_default_lang );
    }
}


function wppb_add_plugin_stylesheet() {
	$wppb_generalSettings = get_option( 'wppb_general_settings' );

	if ( ( file_exists( WPPB_PLUGIN_DIR . '/assets/css/style-front-end.css' ) ) && ( isset( $wppb_generalSettings['extraFieldsLayout'] ) && ( $wppb_generalSettings['extraFieldsLayout'] == 'default' ) ) ){
		wp_register_style( 'wppb_stylesheet', WPPB_PLUGIN_URL . 'assets/css/style-front-end.css', array(), PROFILE_BUILDER_VERSION );
		wp_enqueue_style( 'wppb_stylesheet' );
    }
	if( is_rtl() ) {
		if ( ( file_exists( WPPB_PLUGIN_DIR . '/assets/css/rtl.css' ) ) && ( isset( $wppb_generalSettings['extraFieldsLayout'] ) && ( $wppb_generalSettings['extraFieldsLayout'] == 'default' ) ) ){
			wp_register_style( 'wppb_stylesheet_rtl', WPPB_PLUGIN_URL . 'assets/css/rtl.css', array(), PROFILE_BUILDER_VERSION );
			wp_enqueue_style( 'wppb_stylesheet_rtl' );
		}
	}
}


function wppb_show_admin_bar($content){
	global $current_user;

	$adminSettingsPresent = get_option('wppb_display_admin_settings','not_found');
	$show = null;

	if ($adminSettingsPresent != 'not_found' && $current_user->ID)
		foreach ($current_user->roles as $role_key) {
			if (empty($GLOBALS['wp_roles']->roles[$role_key]))
				continue;
			$role = $GLOBALS['wp_roles']->roles[$role_key];
			if (isset($adminSettingsPresent[$role['name']])) {
				if ($adminSettingsPresent[$role['name']] == 'show')
					$show = true;
				if ($adminSettingsPresent[$role['name']] == 'hide' && $show === null)
					$show = false;
			}
		}
	return $show === null ? $content : $show;
}


if(!function_exists('wppb_curpageurl')){
	function wppb_curpageurl(){
        $req_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : '';

        if( function_exists('wppb_get_abs_home') ) {
            $home_path = trim(parse_url(wppb_get_abs_home(), PHP_URL_PATH), '/');
            $home_path_regex = sprintf('|^%s|i', preg_quote($home_path, '|'));

            // Trim path info from the end and the leading home path from the front.
            $req_uri = ltrim($req_uri, '/');
            $req_uri = preg_replace($home_path_regex, '', $req_uri);
            $req_uri = trim(wppb_get_abs_home(), '/') . '/' . ltrim($req_uri, '/');
        }

        if ( function_exists('apply_filters') ) $req_uri = apply_filters('wppb_curpageurl', $req_uri);

        return $req_uri;
    }
}

/**
 * Return absolute home url as stored in database, unfiltered.
 *
 * @return string
 */
if(!function_exists('wppb_get_abs_home')) {
    function wppb_get_abs_home(){
        global $wpdb;
        global $wppb_absolute_home;

        if( isset($wppb_absolute_home) ) {
            return $wppb_absolute_home;
        }

        // returns the unfiltered home_url by directly retrieving it from wp_options.
        $wppb_absolute_home = (!is_multisite() && defined('WP_HOME')
            ? WP_HOME
            : (is_multisite() && !is_main_site()
                ? (preg_match('/^(https)/', get_option('home')) === 1 ? 'https://'
                    : 'http://') . $wpdb->get_var("	SELECT CONCAT(b.domain, b.path)
                                                FROM {$wpdb->blogs} b
                                                WHERE blog_id = {$wpdb->blogid}
                                                LIMIT 1")

                : $wpdb->get_var("	SELECT option_value
                                                FROM {$wpdb->options}
                                                WHERE option_name = 'home'
                                                LIMIT 1"))
        );

        if (empty($wppb_absolute_home)) {
            $wppb_absolute_home = get_option("siteurl");
        }

        // always return absolute_home based on the http or https version of the current page request. This means no more redirects.
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $wppb_absolute_home = str_replace('http://', 'https://', $wppb_absolute_home);
        } else {
            $wppb_absolute_home = str_replace('https://', 'http://', $wppb_absolute_home);
        }

        return $wppb_absolute_home;
    }
}


if ( is_admin() ){

	// register the settings for the menu only display sidebar menu for a user with a certain capability, in this case only the "admin"
	add_action( 'admin_init', 'wppb_register_settings' );

	// display the same extra profile fields in the admin panel also
	if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists ( WPPB_PAID_PLUGIN_DIR.'/front-end/extra-fields/extra-fields.php' ) ){
		require_once( WPPB_PAID_PLUGIN_DIR.'/front-end/extra-fields/extra-fields.php' );

		add_action( 'show_user_profile', 'display_profile_extra_fields_in_admin', 10 );
		add_action( 'edit_user_profile', 'display_profile_extra_fields_in_admin', 10 );
        global $pagenow;
        if( $pagenow != 'user-new.php' )
            add_action( 'user_profile_update_errors', 'wppb_validate_backend_fields', 10, 3 );
		add_action( 'personal_options_update', 'save_profile_extra_fields_in_admin', 10 );
		add_action( 'edit_user_profile_update', 'save_profile_extra_fields_in_admin', 10 );
	}

	/* we need to include the fields here for conditional fields when they run through ajax, the extra-fields were already included above for backend forms */
    $wppb_generalSettings = get_option( 'wppb_general_settings' );
	if( wp_doing_ajax() && wppb_conditional_fields_exists() && isset( $wppb_generalSettings['conditional_fields_ajax'] ) && $wppb_generalSettings['conditional_fields_ajax'] === 'yes' ) {
        if (file_exists(WPPB_PLUGIN_DIR . '/front-end/default-fields/default-fields.php'))
            require_once(WPPB_PLUGIN_DIR . '/front-end/default-fields/default-fields.php');
    }

}else if ( !is_admin() ){
	// include the stylesheet
	add_action( 'wp_print_styles', 'wppb_add_plugin_stylesheet' );

	// include the menu file for the profile informations
	include_once( WPPB_PLUGIN_DIR.'/front-end/edit-profile.php' );
	include_once( WPPB_PLUGIN_DIR.'/front-end/class-formbuilder.php' );
	add_shortcode( 'wppb-edit-profile', 'wppb_front_end_profile_info' );

	// include the menu file for the login screen
	include_once( WPPB_PLUGIN_DIR.'/front-end/login.php' );
	add_shortcode( 'wppb-login', 'wppb_front_end_login' );

    // include the menu file for the logout screen
    include_once( WPPB_PLUGIN_DIR.'/front-end/logout.php' );
    add_shortcode( 'wppb-logout', 'wppb_front_end_logout' );

	// include the menu file for the register screen
	include_once( WPPB_PLUGIN_DIR.'/front-end/register.php' );
	add_shortcode( 'wppb-register', 'wppb_front_end_register_handler' );

	// include the menu file for the recover password screen
	include_once( WPPB_PLUGIN_DIR.'/front-end/recover.php' );
	add_shortcode( 'wppb-recover-password', 'wppb_front_end_password_recovery' );

	// set the front-end admin bar to show/hide
	add_filter( 'show_admin_bar' , 'wppb_show_admin_bar');

	// Shortcodes used for the widget area
	add_filter( 'widget_text', 'do_shortcode', 11 );
}


/**
 * Function that overwrites the default wp_mail function and sends out emails
 *
 * @since v.2.0
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $message_from
 *
 */
function wppb_mail( $to, $subject, $message, $message_from = null, $context = null, $headers = '' ) {
	$to = apply_filters( 'wppb_send_email_to', $to, $context );
	$send_email = apply_filters( 'wppb_send_email', true, $to, $subject, $message, $context );

	$message = apply_filters( 'wppb_email_message', $message, $context );

	$message = wppb_maybe_add_propper_html_tags_to_email( $message );

	do_action( 'wppb_before_sending_email', $to, $subject, $message, $send_email, $context );

	if ( $send_email ) {
		//we add this filter to enable html encoding
		add_filter('wp_mail_content_type', 'wppb_html_content_type' );

		$atts = apply_filters( 'wppb_mail', compact( 'to', 'subject', 'message', 'headers' ), $context );

		$sent = wp_mail( $atts['to'] , html_entity_decode( htmlspecialchars_decode( $atts['subject'], ENT_QUOTES ), ENT_QUOTES ), $atts['message'], $atts['headers'] );

		do_action( 'wppb_after_sending_email', $sent, $to, $subject, $message, $send_email, $context );

		return $sent;
	}

	return '';
}

/* return text/html as email content type. used in  wp_mail_content_type filter */
function wppb_html_content_type( $content_type ){
    return 'text/html';
}

/*
 * function that adds proper html tags to a html email message if they are missing
 */
function wppb_maybe_add_propper_html_tags_to_email( $message ){

    //check if we have html content
    if( $message !== wp_strip_all_tags( $message ) ){
        if( strpos( html_entity_decode( $message ), '<html' ) === false && strpos( html_entity_decode( $message ), '<body' ) === false ){
            $message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'. $message . '</body></html>';
        }
    }

    return $message;
}

function wppb_activate_account_check(){
	if ( ( isset( $_GET['activation_key'] ) ) && ( sanitize_text_field( $_GET['activation_key'] ) != '' ) ){
		global $post;
		$activation_key = sanitize_text_field( $_GET['activation_key'] );

		$wppb_generalSettings = get_option( 'wppb_general_settings' );
		$activation_landing_page_id = ( ( isset( $wppb_generalSettings['activationLandingPage'] ) && ( trim( $wppb_generalSettings['activationLandingPage'] ) != '' ) ) ? $wppb_generalSettings['activationLandingPage'] : 'not_set' );

		if ( $activation_landing_page_id != 'not_set' ){
			//an activation page was selected, but we still need to check if the current page doesn't already have the registration shortcode
			if ( strpos( $post->post_content, '[wppb-register' ) === false )
				add_filter( 'the_content', 'wppb_add_activation_message' );

		}elseif ( strpos( $post->post_content, '[wppb-register' ) === false ){
			//no activation page was selected, and the sent link pointed to the home url
            nocache_headers();
			wp_redirect( apply_filters( 'wppb_activatate_account_redirect_url', WPPB_PLUGIN_URL.'assets/misc/fallback-page.php?activation_key='.urlencode( $activation_key ).'&site_name='.urlencode( get_bloginfo( 'name' ) ).'&message='.urlencode( $activation_message = wppb_activate_signup( $activation_key ) ), $activation_key, $activation_message ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'wppb_activate_account_check' );


function wppb_add_activation_message( $content ){
    if( isset( $_GET['activation_key']  ) )
	    return wppb_activate_signup( sanitize_text_field( $_GET['activation_key'] ) ) . $content;
    else
        return $content;
}


// Create a new, top-level page
$args = array(
			'page_title'	=> 'Profile Builder',
			'menu_title'	=> 'Profile Builder',
			'capability'	=> 'manage_options',
			'menu_slug' 	=> 'profile-builder',
			'page_type'		=> 'menu_page',
			'position' 		=> '70.69',
			'priority' 		=> 1,
			'icon_url' 		=> WPPB_PLUGIN_URL . 'assets/images/pb-menu-icon.png'
		);
new WCK_Page_Creator_PB( $args );

/**
 * Remove the automatically created submenu page
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_remove_main_menu_page(){
	remove_submenu_page( 'profile-builder', 'profile-builder' );
}
add_action( 'admin_menu', 'wppb_remove_main_menu_page', 11 );

/**
 * Add scripts to the back-end CPT's to remove the slug from the edit page
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_print_cpt_script( $hook ){
	wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_style( 'wp-jquery-ui-dialog' );

	if ( $hook == 'profile-builder_page_manage-fields' ){
		wp_enqueue_script( 'wppb-manage-fields-live-change', WPPB_PLUGIN_URL . 'assets/js/jquery-manage-fields-live-change.js', array(), PROFILE_BUILDER_VERSION, true );
		wp_localize_script( 'wppb-manage-fields-live-change', 'wppb_fields_strings', array( 'gdpr_title' => __( 'GDPR Checkbox', 'profile-builder' ), 'gdpr_description' => __( 'I allow the website to collect and store the data I submit through this form.', 'profile-builder' ), 'honeypot_title' => __( 'Honeypot', 'profile-builder' ) ) );

		wp_enqueue_script( 'wppb-select2', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array(), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb-select2-style', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css', false, PROFILE_BUILDER_VERSION );
	}

	if ( $hook == 'admin_page_profile-builder-private-website' ){
		wp_enqueue_script( 'wppb-select2', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array(), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_script( 'wppb-select2-compat', WPPB_PLUGIN_URL . 'assets/js/select2-compat.js', array(), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb-select2-style', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css', false, PROFILE_BUILDER_VERSION );
	}

	if (( $hook == 'profile-builder_page_manage-fields' ) ||
		( $hook == 'profile-builder_page_profile-builder-basic-info' ) ||
		( $hook == 'profile-builder_page_profile-builder-add-ons' ) ||
		( $hook == 'profile-builder_page_profile-builder-general-settings' ) ||
		( $hook == 'profile-builder_page_profile-builder-admin-bar-settings' ) ||
		( $hook == 'profile-builder_page_profile-builder-register' ) ||
		( $hook == 'profile-builder_page_profile-builder-wppb_userListing' ) ||
		( $hook == 'profile-builder_page_custom-redirects' ) ||
		( $hook == 'profile-builder_page_profile-builder-wppb_emailCustomizer' ) ||//?what is this
		( $hook == 'profile-builder_page_profile-builder-wppb_emailCustomizerAdmin' ) ||//?what is this
		( $hook == 'profile-builder_page_profile-builder-add-ons' ) ||
		( $hook == 'profile-builder_page_profile-builder-woocommerce-sync' ) ||
        ( $hook == 'profile-builder_page_profile-builder-bbpress') ||
        ( $hook == 'profile-builder_page_admin-email-customizer') ||
        ( $hook == 'profile-builder_page_user-email-customizer') ||
        ( $hook == 'profile-builder_page_profile-builder-content_restriction' ) ||
        ( strpos( $hook, 'profile-builder_page_' ) === 0 ) ||
        ( $hook == 'edit.php' && ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'wppb-roles-editor' ) ) ||
		( $hook == 'admin_page_profile-builder-pms-promo') ||
		( $hook == 'admin_page_profile-builder-private-website') ) {
			wp_enqueue_style( 'wppb-back-end-style', WPPB_PLUGIN_URL . 'assets/css/style-back-end.css', false, PROFILE_BUILDER_VERSION );
	}

	if ( $hook == 'profile-builder_page_profile-builder-general-settings' )
		wp_enqueue_script( 'wppb-manage-fields-live-change', WPPB_PLUGIN_URL . 'assets/js/jquery-email-confirmation.js', array(), PROFILE_BUILDER_VERSION, true );

    if( ($hook == 'profile-builder_page_profile-builder-add-ons' ) ||
        ($hook == 'admin_page_profile-builder-pms-promo' ) ) {
        wp_enqueue_script('wppb-add-ons', WPPB_PLUGIN_URL . 'assets/js/jquery-pb-add-ons.js', array(), PROFILE_BUILDER_VERSION, true);
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'thickbox' );
    }

	if ( isset( $_GET['post_type'] ) || isset( $_GET['post'] ) ){
		if ( isset( $_GET['post_type'] ) )
			$post_type = sanitize_text_field( $_GET['post_type'] );

		elseif ( isset( $_GET['post'] ) )
			$post_type = get_post_type( absint( $_GET['post'] ) );

		if ( ( 'wppb-epf-cpt' == $post_type ) || ( 'wppb-rf-cpt' == $post_type ) || ( 'wppb-ul-cpt' == $post_type ) ){
			wp_enqueue_style( 'wppb-back-end-style', WPPB_PLUGIN_URL . 'assets/css/style-back-end.css', false, PROFILE_BUILDER_VERSION );
			wp_enqueue_script( 'wppb-epf-rf', WPPB_PLUGIN_URL . 'assets/js/jquery-epf-rf.js', array(), PROFILE_BUILDER_VERSION, true );
		}
		else if( 'wppb-roles-editor' == $post_type ){
			wp_enqueue_style( 'wppb-back-end-style', WPPB_PLUGIN_URL . 'assets/css/style-back-end.css', array(), PROFILE_BUILDER_VERSION );
		}
	}

    wp_enqueue_script( 'wppb-sitewide', WPPB_PLUGIN_URL . 'assets/js/jquery-pb-sitewide.js', array(), PROFILE_BUILDER_VERSION, true );

    wp_enqueue_style( 'wppb-serial-notice-css', WPPB_PLUGIN_URL . 'assets/css/serial-notice.css', false, PROFILE_BUILDER_VERSION );
}
add_action( 'admin_enqueue_scripts', 'wppb_print_cpt_script', 9 );

/**
 * Highlight the settings page under Profile Builder in the admin menu for these pages
 */
//add add_action( "admin_footer-$hook", "wppb_make_setting_menu_item_highlighted" ); for other pages that don't have a parent
add_action( "admin_footer-admin_page_profile-builder-private-website", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_profile-builder-admin-bar-settings", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_profile-builder-content_restriction", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_profile-builder-content_restriction", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_admin-email-customizer", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_user-email-customizer", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_profile-builder-toolbox-settings", "wppb_make_setting_menu_item_highlighted" );
add_action( "admin_footer-profile-builder_page_profile-builder-two-factor-authentication", "wppb_make_setting_menu_item_highlighted" );
function wppb_make_setting_menu_item_highlighted(){
	echo'<script type="text/javascript">
        jQuery(document).ready( function($) {
            $("#toplevel_page_profile-builder").addClass("current wp-has-current-submenu wp-menu-open");
            $("a[href=\'admin.php?page=profile-builder-general-settings\']").closest("li").addClass("current");
        });
        </script>';
}


//the function used to overwrite the avatar across the wp installation
function wppb_changeDefaultAvatar( $avatar, $id_or_email, $size, $default, $alt ){
	/* Get user info. */
	if(is_object($id_or_email)){
		$my_user_id = $id_or_email->user_id;

		if ($id_or_email instanceof WP_User){
            $my_user_id = $id_or_email->ID;
		}

	}elseif(is_numeric($id_or_email)){
		$my_user_id = $id_or_email;

	}elseif(!is_integer($id_or_email)){
		$user_info = get_user_by( 'email', $id_or_email );
		$my_user_id = ( is_object( $user_info ) ? $user_info->ID : '' );
	}else
		$my_user_id = $id_or_email;

	$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
	if ( $wppb_manage_fields != 'not_found' ){
		foreach( $wppb_manage_fields as $value ){
			if ( $value['field'] == 'Avatar'){
				$avatar_field = $value;
			}
		}
	}

	/* for multisite if we don't have an avatar try to get it from the main blog */
	if( is_multisite() && empty( $avatar_field ) ){
		switch_to_blog(1);
		$wppb_switched_blog = true;
		$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
		if ( $wppb_manage_fields != 'not_found' ){
			foreach( $wppb_manage_fields as $value ){
				if ( $value['field'] == 'Avatar'){
					$avatar_field = $value;
				}
			}
		}
	}

	if ( !empty( $avatar_field ) ){

		$customUserAvatar = get_user_meta( $my_user_id, Wordpress_Creation_Kit_PB::wck_generate_slug( $avatar_field['meta-name'] ), true );
		if( !empty( $customUserAvatar ) ){
			if( is_numeric( $customUserAvatar ) ){
				$img_attr = wp_get_attachment_image_src( $customUserAvatar, 'wppb-avatar-size-'.$size );
				if( $img_attr[3] === false ){
					$img_attr = wp_get_attachment_image_src( $customUserAvatar, 'thumbnail' );
					$avatar = "<img alt='{$alt}' src='{$img_attr[0]}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
				}
				else
					$avatar = "<img alt='{$alt}' src='{$img_attr[0]}' class='avatar avatar-{$size} photo avatar-default' height='{$img_attr[2]}' width='{$img_attr[1]}' />";
			}
			else {
				$customUserAvatar = get_user_meta($my_user_id, 'resized_avatar_' . $avatar_field['id'], true);
				$customUserAvatarRelativePath = get_user_meta($my_user_id, 'resized_avatar_' . $avatar_field['id'] . '_relative_path', true);

				if ((($customUserAvatar != '') || ($customUserAvatar != null)) && file_exists($customUserAvatarRelativePath)) {
					$avatar = "<img alt='{$alt}' src='{$customUserAvatar}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
				}
			}
		}

	}

	/* if we switched the blog restore it */
	if( is_multisite() && !empty( $wppb_switched_blog ) && $wppb_switched_blog )
		restore_current_blog();

	return $avatar;
}
add_filter( 'get_avatar', 'wppb_changeDefaultAvatar', 21, 5 );


//the function used to overwrite the avatar across the wp installation
function wppb_changeDefaultAvatarUrl( $url, $id_or_email, $args ){
	/* Get user info. */
	if(is_object($id_or_email)){
		$my_user_id = $id_or_email->user_id;

		if ($id_or_email instanceof WP_User){
			$my_user_id = $id_or_email->ID;
		}

	}elseif(is_numeric($id_or_email)){
		$my_user_id = $id_or_email;

	}elseif(!is_integer($id_or_email)){
		$user_info = get_user_by( 'email', $id_or_email );
		$my_user_id = ( is_object( $user_info ) ? $user_info->ID : '' );
	}else
		$my_user_id = $id_or_email;

	$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
	if ( $wppb_manage_fields != 'not_found' ){
		foreach( $wppb_manage_fields as $value ){
			if ( $value['field'] == 'Avatar'){
				$avatar_field = $value;
			}
		}
	}

	/* for multisite if we don't have an avatar try to get it from the main blog */
	if( is_multisite() && empty( $avatar_field ) ){
		switch_to_blog(1);
		$wppb_switched_blog = true;
		$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
		if ( $wppb_manage_fields != 'not_found' ){
			foreach( $wppb_manage_fields as $value ){
				if ( $value['field'] == 'Avatar'){
					$avatar_field = $value;
				}
			}
		}
	}

	$avatar_url = $url;

	if ( !empty( $avatar_field ) ){

		$customUserAvatar = get_user_meta( $my_user_id, Wordpress_Creation_Kit_PB::wck_generate_slug( $avatar_field['meta-name'] ), true );
		if( !empty( $customUserAvatar ) ){
			if( is_numeric( $customUserAvatar ) ){
				if( $img_attr = wp_get_attachment_image_src( $customUserAvatar, 'wppb-avatar-size-'.$args['size'] ) ) {
					if ( $img_attr[3] === false ) {
						$img_attr   = wp_get_attachment_image_src( $customUserAvatar, 'thumbnail' );
					}
					$avatar_url = $img_attr[0];
				}
			}
			else {
				$customUserAvatar = get_user_meta($my_user_id, 'resized_avatar_' . $avatar_field['id'], true);
				$customUserAvatarRelativePath = get_user_meta($my_user_id, 'resized_avatar_' . $avatar_field['id'] . '_relative_path', true);

				if ((($customUserAvatar != '') || ($customUserAvatar != null)) && file_exists($customUserAvatarRelativePath)) {
					$avatar_url = $customUserAvatar;
				}
			}
		}

	}

	/* if we switched the blog restore it */
	if( is_multisite() && !empty( $wppb_switched_blog ) && $wppb_switched_blog )
		restore_current_blog();

	return $avatar_url;
}
add_filter( 'get_avatar_url', 'wppb_changeDefaultAvatarUrl', 21, 5 );


//the function used to resize the avatar image; the new function uses a user ID as parameter to make pages load faster
function wppb_resize_avatar( $userID, $userlisting_size = null, $userlisting_crop = null ){
	// include the admin image API
	require_once( ABSPATH . '/wp-admin/includes/image.php' );

	// retrieve first a list of all the current custom fields
	$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
	if ( $wppb_manage_fields != 'not_found' ){
		foreach( $wppb_manage_fields as $value ){
			if ( $value['field'] == 'Avatar'){
				$avatar_field = $value;
			}
		}
	}

	/* for multisite if we don't have an avatar try to get it from the main blog */
	if( is_multisite() && empty( $avatar_field ) ){
		switch_to_blog(1);
		$wppb_switched_blog = true;
		$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
		if ( $wppb_manage_fields != 'not_found' ){
			foreach( $wppb_manage_fields as $value ){
				if ( $value['field'] == 'Avatar'){
					$avatar_field = $value;
				}
			}
		}
	}


	if ( !empty( $avatar_field ) ){

		// retrieve width and height of the image
		$width = $height = '';

		//this checks if it only has 1 component
		if ( is_numeric( $avatar_field['avatar-size'] ) ){
			$width = $height = $avatar_field['avatar-size'];

		}else{
			//this checks if the entered value has 2 components
			$sentValue = explode( ',', $avatar_field['avatar-size'] );
			$width = $sentValue[0];
			$height = $sentValue[1];
		}

		$width = ( !empty( $userlisting_size ) ? $userlisting_size : $width );
		$height = ( !empty( $userlisting_size ) ? $userlisting_size : $height );

		if( !strpos( get_user_meta( $userID, 'resized_avatar_'.$avatar_field['id'], true ), $width . 'x' . $height ) ) {
			// retrieve the original image (in original size)
			$avatar_directory_path = get_user_meta( $userID, 'avatar_directory_path_'.$avatar_field['id'], true );

			$image = wp_get_image_editor( $avatar_directory_path );
			if ( !is_wp_error( $image ) ) {
				do_action( 'wppb_before_avatar_resizing', $image, $userID, Wordpress_Creation_Kit_PB::wck_generate_slug( $avatar_field['meta-name'] ), $avatar_field['avatar-size'] );

				$crop = apply_filters( 'wppb_avatar_crop_resize', ( !empty( $userlisting_crop ) ? $userlisting_crop : false ) );

				$resize = $image->resize( $width, $height, $crop );

				if ($resize !== FALSE) {
					do_action( 'wppb_avatar_resizing', $image, $resize );

					$fileType = apply_filters( 'wppb_resized_file_extension', 'png' );

					$wp_upload_array = wp_upload_dir(); // Array of key => value pairs

					//create file(name); both with directory and url
					$fileName_dir = $image->generate_filename( NULL, $wp_upload_array['basedir'].'/profile_builder/avatars/', $fileType );

					if ( PHP_OS == "WIN32" || PHP_OS == "WINNT" )
						$fileName_dir = str_replace( '\\', '/', $fileName_dir );

					$fileName_url = str_replace( str_replace( '\\', '/', $wp_upload_array['basedir'] ), $wp_upload_array['baseurl'], $fileName_dir );

					//save the newly created (resized) avatar on the disc
					$saved_image = $image->save( $fileName_dir );

					if ( !is_wp_error( $saved_image ) ) {
						/* the image save sometimes doesn't save with the desired extension so we need to see with what extension it saved it with and
						if it differs replace the extension	in the path and url that we save as meta */
						$validate_saved_image = wp_check_filetype_and_ext( $saved_image['path'], $saved_image['path'] );
						$ext = substr( $fileName_dir,strrpos( $fileName_dir, '.', -1 ), strlen($fileName_dir) );
						if( !empty( $validate_saved_image['ext'] ) && $validate_saved_image['ext'] != $ext ){
							$fileName_url = str_replace( $ext, '.'.$validate_saved_image['ext'], $fileName_url );
							$fileName_dir = str_replace( $ext, '.'.$validate_saved_image['ext'], $fileName_dir );
						}

						update_user_meta( $userID, 'resized_avatar_'.$avatar_field['id'], $fileName_url );
						update_user_meta( $userID, 'resized_avatar_'.$avatar_field['id'].'_relative_path', $fileName_dir );

						do_action( 'wppb_after_avatar_resizing', $image, $fileName_dir, $fileName_url );
					}
				}
			}
		}
	}

	/* if we switched the blog restore it */
	if( is_multisite() && !empty( $wppb_switched_blog ) && $wppb_switched_blog )
		restore_current_blog();

}


if ( is_admin() ){
	// add a hook to delete the user from the _signups table if either the email confirmation is activated, or it is a wpmu installation
	function wppb_delete_user_from_signups_table($user_id) {
		global $wpdb;

		$userLogin = $wpdb->get_var( $wpdb->prepare( "SELECT user_login, user_email FROM " . $wpdb->users . " WHERE ID = %d LIMIT 1", $user_id ) );
		if ( is_multisite() )
			$delete = $wpdb->delete( $wpdb->signups, array( 'user_login' => $userLogin ) );
        else {
            $table_name = $wpdb->prefix . 'signups';
            $val = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
            if ( $val ){
                $delete = $wpdb->delete($wpdb->prefix . 'signups', array('user_login' => $userLogin));
            }
        }
	}

    $wppb_generalSettings = get_option( 'wppb_general_settings' );
    if ( !empty( $wppb_generalSettings['emailConfirmation'] ) && ( $wppb_generalSettings['emailConfirmation'] == 'yes' ) ) {
        if( is_multisite() )
            add_action( 'wpmu_delete_user', 'wppb_delete_user_from_signups_table' );
        else
            add_action('delete_user', 'wppb_delete_user_from_signups_table');
    }
}



// This function offers compatibility with the all in one event calendar plugin
function wppb_aioec_compatibility(){

	wp_deregister_script( 'jquery.tools-form');
}
add_action('admin_print_styles-users_page_ProfileBuilderOptionsAndSettings', 'wppb_aioec_compatibility');


function wppb_user_meta_exists( $id, $meta_name ){
	global $wpdb;

	return apply_filters( 'wppb_user_meta_exists_meta_name', $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s", $id, $meta_name ) ), $id, $meta_name );
}


// function to check if there is a need to add the http:// prefix
function wppb_check_missing_http( $redirectLink ) {
	return preg_match( '#^(?:[a-z\d]+(?:-+[a-z\d]+)*\.)+[a-z]+(?::\d+)?(?:/|$)#i', $redirectLink );
}

//function that adds missing http to a link
function wppb_add_missing_http( $link ){
	$http = '';
	if ( wppb_check_missing_http( $link ) ) { //if missing http(s)
		$http = 'http';
		if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
			$http .= "s";
		$http .= "://";
	}

	return $http . $link;
}


//function to output the password strength checker on frontend forms
function wppb_password_strength_checker_html(){
    $wppb_generalSettings = get_option( 'wppb_general_settings' );
    if( !empty( $wppb_generalSettings['minimum_password_strength'] ) ){
        $password_strength = '<span id="pass-strength-result">'.__('Strength indicator', 'profile-builder' ).'</span>
        <input type="hidden" value="" name="wppb_password_strength" id="wppb_password_strength"/>';
        return $password_strength;
    }
    return '';
}

//function to check password length check
function wppb_check_password_length( $password ){
    $wppb_generalSettings = get_option( 'wppb_general_settings' );
    if( !empty( $wppb_generalSettings['minimum_password_length'] ) ){
        if( strlen( $password ) < $wppb_generalSettings['minimum_password_length'] ){
            return true;
        }
        else
            return false;
    }
    return false;
}

//function to check password strength
function wppb_check_password_strength(){
    $wppb_generalSettings = get_option( 'wppb_general_settings' );
    if( isset( $_POST['wppb_password_strength'] ) && !empty( $wppb_generalSettings['minimum_password_strength'] ) ){
		$wppb_password_strength = sanitize_text_field( $_POST['wppb_password_strength'] );
        $password_strength_array = array( 'short' => 0, 'bad' => 1, 'good' => 2, 'strong' => 3 );
        $password_strength_text = array( 'short' => __( 'Very Weak', 'profile-builder' ), 'bad' => __( 'Weak', 'profile-builder' ), 'good' => __( 'Medium', 'profile-builder' ), 'strong' => __( 'Strong', 'profile-builder' ) );
        if( $password_strength_array[$wppb_password_strength] < $password_strength_array[$wppb_generalSettings['minimum_password_strength']] ){
            return $password_strength_text[$wppb_generalSettings['minimum_password_strength']];
        }
        else
            return false;
    }
    return false;
}

/* function to output password length requirements text */
function wppb_password_length_text(){
    $wppb_generalSettings = get_option( 'wppb_general_settings' );
    if( !empty( $wppb_generalSettings['minimum_password_length'] ) ){
        return sprintf(__('Minimum length of %d characters.', 'profile-builder'), $wppb_generalSettings['minimum_password_length']);
    }
    return '';
}

/* function to output password strength requirements text */
function wppb_password_strength_description() {
	$wppb_generalSettings = get_option( 'wppb_general_settings' );

	if( ! empty( $wppb_generalSettings['minimum_password_strength'] ) ) {
		$password_strength_text = array( 'short' => __( 'Very Weak', 'profile-builder' ), 'bad' => __( 'Weak', 'profile-builder' ), 'good' => __( 'Medium', 'profile-builder' ), 'strong' => __( 'Strong', 'profile-builder' ) );
		$password_strength_description = '<br>'. sprintf( __( 'The password must have a minimum strength of %s', 'profile-builder' ), $password_strength_text[$wppb_generalSettings['minimum_password_strength']] );

		return $password_strength_description;
	} else {
		return '';
	}
}

/**
 * Include password strength check scripts on frontend where we have shortcodes present
 */
add_action( 'wp_footer', 'wppb_enqueue_password_strength_check' );
function wppb_enqueue_password_strength_check() {
    global $wppb_shortcode_on_front;
    if( $wppb_shortcode_on_front ){
        $wppb_generalSettings = get_option( 'wppb_general_settings' );
        if( !empty( $wppb_generalSettings['minimum_password_strength'] ) ){
             wp_enqueue_script( 'password-strength-meter' );
        }
    }
}
add_action( 'wp_footer', 'wppb_password_strength_check', 102 );
function wppb_password_strength_check(){
    global $wppb_shortcode_on_front;
    if( $wppb_shortcode_on_front ){
        $wppb_generalSettings = get_option( 'wppb_general_settings' );
        if( !empty( $wppb_generalSettings['minimum_password_strength'] ) ){
            ?>
            <script type="text/javascript">
                function check_pass_strength() {
                    var pass1 = jQuery('#passw1').val(), pass2 = jQuery('#passw2').val(), strength;

                    jQuery('#pass-strength-result').removeClass('short bad good strong');
                    if ( ! pass1 ) {
                        jQuery('#pass-strength-result').html( pwsL10n.empty );
                        return;
                    }

                    strength = wp.passwordStrength.meter( pass1, wp.passwordStrength.userInputDisallowedList(), pass2 );

                    switch ( strength ) {
                        case 2:
                            jQuery('#pass-strength-result').addClass('bad').html( pwsL10n.bad );
                            jQuery('#wppb_password_strength').val('bad');
                            break;
                        case 3:
                            jQuery('#pass-strength-result').addClass('good').html( pwsL10n.good );
                            jQuery('#wppb_password_strength').val('good');
                            break;
                        case 4:
                            jQuery('#pass-strength-result').addClass('strong').html( pwsL10n.strong );
                            jQuery('#wppb_password_strength').val('strong');
                            break;
                        case 5:
                            jQuery('#pass-strength-result').addClass('short').html( pwsL10n.mismatch );
                            jQuery('#wppb_password_strength').val('short');
                            break;
                        default:
                            jQuery('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
                            jQuery('#wppb_password_strength').val('short');
                    }
                }
                jQuery( document ).ready( function() {
                    // Binding to trigger checkPasswordStrength
                    jQuery('#passw1').val('').on( 'keyup', check_pass_strength );
                    jQuery('#passw2').val('').on( 'keyup', check_pass_strength );
                    jQuery('#pass-strength-result').show();
                });
            </script>
        <?php
        }
    }
}

/**
 * Include toggle password visibility script on frontend where we have shortcodes present
 */
function wppb_password_visibility_toggle_html(){
    if( apply_filters( 'wppb_show_password_visibility_toggle', false ) ){
        return '
            <button type="button" class="wppb-toggle-pw wppb-show-pw hide-if-no-js" data-toggle="0" aria-label="Show password" tabindex="-1">
                <img src="'.WPPB_PLUGIN_URL.'/assets/images/eye-outline.svg" width="20px" height="20px" />
            </button>';
    }
    return '';
}

/**
 * Include toggle password visibility script on frontend where we have shortcodes present
 */
add_action( 'wp_footer', 'wppb_enqueue_password_visibility_toggle' );
function wppb_enqueue_password_visibility_toggle() {
    global $wppb_shortcode_on_front;
    if( $wppb_shortcode_on_front && apply_filters( 'wppb_show_password_visibility_toggle', false ) ){

        //load jQuery if needed
        if( !wp_script_is('jquery', 'done') ){
            wp_print_scripts('jquery');
        }

        ?>
        <script type="text/javascript">
            jQuery( document ).ready( function() {

                jQuery( "button.wppb-toggle-pw" ).on( "click", wppb_password_visibility_toggle );

				jQuery( 'button.wppb-toggle-pw' ).each( function( index, toggle ){

					var parent = jQuery( toggle ).parent()

					if( parent.hasClass( 'wppb-form-field' ) && jQuery( '.wppb-description-delimiter', parent ) ){

						jQuery( toggle ).css( 'top', parseInt( jQuery( toggle ).css('top') ) - ( jQuery( '.wppb-description-delimiter', parent ).outerHeight() / 2 ) )

					}

				})

            });
            function wppb_password_visibility_toggle() {
                var target_form_id = "#" + jQuery(this).closest('form').attr("id") + " ";

                var password_inputs = [ ".login-password input#user_pass", "input#passw1", "input#passw2" ]

                for ( var password_input of password_inputs ){
                    var input = jQuery( target_form_id + password_input );
                    var button = jQuery( target_form_id + "button.wppb-toggle-pw" );
                    var icon = jQuery( target_form_id + "button.wppb-toggle-pw img" );

                    if ( input.length ) {
                        if ("password" === input.attr("type")) {
                            input.attr("type", "text");
                            button.toggleClass("wppb-show-pw").toggleClass("wppb-hide-pw");
                            icon.attr("src", "<?php echo esc_attr( WPPB_PLUGIN_URL ); ?>/assets/images/eye-off-outline.svg");
                        } else {
                            input.attr("type", "password");
                            button.toggleClass("wppb-show-pw").toggleClass("wppb-hide-pw");
                            icon.attr("src", "<?php echo esc_attr( WPPB_PLUGIN_URL ); ?>/assets/images/eye-outline.svg");
                        }
                    }
                }
            }
        </script>
        <?php
    }
}

/**
 * Create functions for repeating error messages in front-end forms
 */
function wppb_required_field_error($field_title='') {
    $required_error = apply_filters('wppb_required_error' , __('This field is required','profile-builder') , $field_title);

    return $required_error;

}

/**
 * Function that returns a certain field (from manage_fields) by a given id or meta_name
 */
function wppb_get_field_by_id_or_meta( $id_or_meta ){

    $id = 0;
    $meta = '';

    if ( is_numeric($id_or_meta) )
        $id = $id_or_meta;
    else
        $meta = $id_or_meta;

    $fields = get_option('wppb_manage_fields', 'not_found');

    if ($fields != 'not_found') {

        foreach ($fields as $key => $field) {
            if ( (!empty($id)) && ($field['id'] == $id) )
                return $field;
            if ( (!empty($meta)) && ($field['meta-name'] == $meta) )
                return $field;
        }

    }

    return '';
}


/* Function for displaying reCAPTCHA error on Login and Recover Password forms */
function wppb_recaptcha_field_error($field_title='') {
    $recaptcha_error = apply_filters('wppb_recaptcha_error' , __('Please enter a (valid) reCAPTCHA value','profile-builder') , $field_title);

    return $recaptcha_error;

}
/* Function for displaying phone field error */
function wppb_phone_field_error( $field_title = '' ) {
	$phone_error = apply_filters( 'wppb_phone_error' , __( 'Incorrect phone number', 'profile-builder' ) , $field_title );

	return $phone_error;
}

/* Create a wrapper function for get_query_var */
function wppb_get_query_var( $varname ){
    //if we want the userlisting on front page ( is_front_page() ) apparently the way we register query vars does not work so we will just use a simple GET var
    if($varname === 'username' && !get_query_var( $varname ) && isset($_GET['wppb_username'])){
        return apply_filters( 'wppb_get_query_var_'.$varname, sanitize_user( $_GET['wppb_username'] ) );
    }

    return apply_filters( 'wppb_get_query_var_'.$varname, get_query_var( $varname ) );
}

/** @param string|array $key   Query key or keys to remove.
 */
function wppb_remove_query_arg( $key, $url = false ){
    $striped_url = remove_query_arg( $key, $url);

    //treat page key on frontpage case where it is transformed into a pretty permalink
    if( ( is_array($key) && in_array('wppb_page', $key) ) || ( !is_array($key) && 'wppb_page' === $key ) ){
        $striped_url = preg_replace( '/\/'.wppb_get_users_pagination_slug().'\/\d+\//', '/', $striped_url );
        $striped_url = preg_replace( '/\/'.wppb_get_users_pagination_slug().'\//', '/', $striped_url );
    }

    return $striped_url;
}

//function that generates tha pagination slug for userlisting
function wppb_get_users_pagination_slug(){
    return apply_filters('wppb_users_pagination_slug', 'users-page');
}

/* Filter the "Save Changes" button text, to make it translatable */
function wppb_change_save_changes_button($value){
    $value = __('Save Changes','profile-builder');
    return $value;
}
add_filter( 'wck_save_changes_button', 'wppb_change_save_changes_button', 10, 2);

/* Filter the "Cancel" button text, to make it translatable */
function wppb_change_cancel_button($value){
    $value = __('Cancel','profile-builder');
    return $value;
}
add_filter( 'wck_cancel_button', 'wppb_change_cancel_button', 10, 2);

/* ilter the "Delete" button text, to make it translatable */
function wppb_change_delete_button($value){
    $value = __('Delete','profile-builder');
    return $value;
}
add_filter( 'wck_delete_button', 'wppb_change_delete_button', 10, 2);

/*Filter the "Edit" button text, to make it translatable*/
function wppb_change_edit_button($value){
    $value = __('Edit','profile-builder');
    return $value;
}
add_filter( 'wck_edit_button', 'wppb_change_edit_button', 10, 2);

/*Filter the User Listing, Register Forms and Edit Profile forms metabox header content, to make it translatable*/
function wppb_change_metabox_content_header(){
  return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( 'Content', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
}
add_filter('wck_metabox_content_header_wppb_ul_page_settings', 'wppb_change_metabox_content_header', 1);
add_filter('wck_metabox_content_header_wppb_rf_page_settings', 'wppb_change_metabox_content_header', 1);
add_filter('wck_metabox_content_header_wppb_epf_page_settings', 'wppb_change_metabox_content_header', 1);


/*Filter default WordPress notices ("Post published. Post updated."), add post type name for User Listing, Registration Forms and Edit Profile Forms*/
function wppb_change_default_post_updated_messages($messages){
    global $post;
    $post_type = get_post_type($post->ID);
    $object = get_post_type_object($post_type);

    if ( ($post_type == 'wppb-rf-cpt')||($post_type == 'wppb-epf-cpt')||($post_type == 'wppb-ul-cpt') ){
        $messages['post'][1] = $object->labels->name . ' updated.';
        $messages['post'][6] = $object->labels->name . ' published.';
    }
    return $messages;
}
add_filter('post_updated_messages','wppb_change_default_post_updated_messages', 2);


/* for meta-names with spaces in them PHP converts the space to underline in the $_POST  */
function wppb_handle_meta_name( $meta_name ){
    $meta_name = trim( $meta_name );
    $meta_name = str_replace( ' ', '_', $meta_name );
    $meta_name = str_replace( '.', '_', $meta_name );
    return $meta_name;
}

/**
 * Function that checks if a field type exists in a form
 * @return bool
 */
function wppb_field_exists_in_form( $field_type, $form_args ){
    if( !empty( $form_args ) && !empty( $form_args['form_fields'] ) ){
        foreach( $form_args['form_fields'] as $field ){
            if( $field['field'] === $field_type ){
                return true;
            }
        }
    }

    return false;
}

// change User Registered date and time according to timezone selected in WordPress settings
function wppb_get_register_date() {

	$time_format = "Y-m-d G:i:s";
	$wppb_get_date = date_i18n( $time_format, false, true );

	if( apply_filters( 'wppb_return_local_time_for_register', false ) ){
		$wppb_get_date = date_i18n( $time_format );
	}

	return $wppb_get_date;
}

/**
 * Function that ads the gmt offset from the general settings to a unix timestamp
 * @param $timestamp
 * @return mixed
 */
function wppb_add_gmt_offset( $timestamp ) {
	if( apply_filters( 'wppb_add_gmt_offset', true ) ){
		$timestamp = $timestamp + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}
	return $timestamp;
}

/**
 * Add HTML tag 'required' to fields
 *
 * Add HTML tag 'required' for each field if the field is required. For browsers that don't support this HTML tag, we will still have the fallback.
 * Field type 'Checkbox' is explicitly excluded because there is no HTML support to check if at least one option is selected.
 * Other fields excluded are Avatar, Upload, Heading, ReCaptcha, WYSIWYG, Map.
 *
 * @since
 *
 * @param string $extra_attributes Extra attributes attached to the field HTML tag.
 * @param array $field Field description.
 * @return string $extra_attributes
 */
function wppb_add_html_tag_required_to_fields( $extra_attributes, $field, $form_location = '' ) {
	if ( $field['field'] != "Checkbox" && isset( $field['required'] ) && $field['required'] == 'Yes' ){
		if( !( ( $field['field'] == "Default - Password" || $field['field'] == "Default - Repeat Password" ) && $form_location == 'edit_profile' ) )
			$extra_attributes .= ' required ';
	}
	return $extra_attributes;
}
add_filter( 'wppb_extra_attribute', 'wppb_add_html_tag_required_to_fields', 10, 3 );

/**
 * Add HTML tag 'required' to WooCommerce fields
 *
 * Add HTML tag 'required' for each WooCommerce field if the field is required. For browsers that don't support this HTML tag, we will still have the fallback.
 * Does not work on 'State / County' field, if it becomes required later depending on the Country Value
 *
 * @since
 *
 * @param string $extra_attributes Extra attributes attached to the field HTML tag.
 * @param array $field Field description.
 * @return string $extra_attributes
 */
function wppb_add_html_tag_required_to_woo_fields( $extra_attributes, $field ) {
	if ( isset( $field['required'] ) && $field['required'] == 'Yes' ){
		$extra_attributes .= ' required ';
	}
	return $extra_attributes;
}
add_filter( 'wppb_woo_extra_attribute', 'wppb_add_html_tag_required_to_woo_fields', 10, 2 );


/**
 * Add jQuery script to remove required attribute for hidden fields
 *
 * If a field is hidden dynamically via conditional fields or WooSync 'Ship to a different address' checkbox, then the required field needs to be removed.
 * If a field is made visible again, add the required field back again.
 *
 * @since
 *
 * @param string $extra_attributes Extra attributes attached to the field HTML tag.
 * @param array $field Field description.
 * @return string $extra_attributes
 */
function wppb_manage_required_attribute() {
	global $wppb_shortcode_on_front;
	if ($wppb_shortcode_on_front) {
	    //check if jquery has been loaded yet because we need it at this point
        // we're checking if it's not admin because it brakes elementor otherwise.
        if( !wp_script_is('jquery', 'done') && !is_admin() ){
            wp_print_scripts('jquery');
        }
		?>
		<script type="text/javascript">
			jQuery(document).on( "wppbAddRequiredAttributeEvent", wppbAddRequired );
			function wppbAddRequired(event) {
				var element = wppbEventTargetRequiredElement( event.target );
				if( jQuery( element ).attr( "wppb_cf_temprequired" ) ){
					jQuery( element  ).removeAttr( "wppb_cf_temprequired" );
					jQuery( element  ).attr( "required", "required" );
				}
			}

			jQuery(document).on( "wppbRemoveRequiredAttributeEvent", wppbRemoveRequired );
			function wppbRemoveRequired(event) {
				var element = wppbEventTargetRequiredElement( event.target );
				if ( jQuery( element ).attr( "required" ) ) {
					jQuery( element ).removeAttr( "required" );
					jQuery( element ).attr( "wppb_cf_temprequired", "wppb_cf_temprequired" );
				}
			}

			jQuery(document).on( "wppbToggleRequiredAttributeEvent", wppbToggleRequired );
			function wppbToggleRequired(event) {
				if ( jQuery( event.target ).attr( "required" ) ) {
					jQuery( event.target ).removeAttr( "required" );
					jQuery( event.target ).attr( "wppb_cf_temprequired", "wppb_cf_temprequired" );
				}else if( jQuery( event.target ).attr( "wppb_cf_temprequired" ) ){
					jQuery( event.target ).removeAttr( "wppb_cf_temprequired" );
					jQuery( event.target ).attr( "required", "required" );
				}
			}

			function wppbEventTargetRequiredElement( htmlElement ){
				if ( htmlElement.nodeName == "OPTION" ){
					// <option> is the target element, so we need to get the parent <select>, in order to apply the required attribute
					return htmlElement.parentElement;
				}else{
					return htmlElement;
				}
			}

		</script>
		<?php
	}
}
add_action( 'wp_footer', 'wppb_manage_required_attribute', 99 );

function wpbb_specify_blog_details_on_signup_email( $message, $user_email, $user, $activation_key, $registration_page_url, $meta, $from_name, $context ){
	$meta = unserialize($meta);

	if ( is_multisite() && isset( $meta['wppb_create_new_site_checkbox'] ) && $meta['wppb_create_new_site_checkbox'] == 'yes' ) {
		$blog_details = wpmu_validate_blog_signup( $meta['wppb_blog_url'], $meta['wppb_blog_title'] );

		if ( empty($blog_details['errors']->errors['blogname']) && empty($blog_details['errors']->errors['blog_title'])) {
			$blog_path = $blog_details['domain'] . $blog_details['path'];
			$message .= __( '<br><br>Also, you will be able to visit your site at ', 'profile-builder' ) . '<a href="' . $blog_path . '">' . $blog_path . '</a>.';
		}
	}
	return $message;
}
add_filter( 'wppb_signup_user_notification_email_content', 'wpbb_specify_blog_details_on_signup_email', 5, 8 );

function wpbb_specify_blog_details_on_registration_email( $user_message_content, $email, $password, $user_message_subject, $context ){

	if ( is_multisite() ) {
		$user = get_user_by( 'email', $email );
		$blog_path = wppb_get_blog_url_of_user_id( $user->ID );
		if ( ! empty ( $blog_path ) ) {
			$user_message_content .= __( '<br><br>You can visit your site at ', 'profile-builder' ) . '<a href="' . $blog_path . '">' . $blog_path . '</a>.';
		}
	}
	return $user_message_content;

}
add_filter( 'wppb_register_user_email_message_without_admin_approval', 'wpbb_specify_blog_details_on_registration_email', 5, 5 );
add_filter( 'wppb_register_user_email_message_with_admin_approval', 'wpbb_specify_blog_details_on_registration_email', 5, 5 );


function wppb_get_blog_url_of_user_id( $user_id, $ignore_privacy = true ){
	$blog_id = get_user_meta( $user_id, 'primary_blog', true );
	if ( is_multisite() && !empty( $blog_id ) ){
		$blog_details = get_blog_details( $blog_id );
		if ( $ignore_privacy || $blog_details->public ) {
			return $blog_details->domain . $blog_details->path;
		}
	}
	return '';
}

function wppb_can_users_signup_blog(){
	if ( ! is_multisite() )
		return false;
	global $wpdb;
	$current_site           = get_current_site();
	$sitemeta_options_query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key = 'registration' AND site_id = %d", $current_site->id );
	$network_options_meta   = $wpdb->get_results( $sitemeta_options_query );

	if ( $network_options_meta[0]->meta_value == 'all' ){
		return true;
	}
	return false;
}

/**
 * Function that handle redirect URL
 *
 * @param	string				$redirect_priority	- it can be normal or top priority
 * @param	string				$redirect_type		- type of the redirect
 * @param	null|string			$redirect_url		- redirect URL if already set
 * @param	null|string|object	$user				- username, user email or user data
 * @param	null|string			$user_role			- user role
 *
 * @return	null|string	$redirect_url
 */
function wppb_get_redirect_url( $redirect_priority, $redirect_type, $redirect_url = NULL, $user = NULL, $user_role = NULL ) {
    if( empty($redirect_priority) ) {
        $redirect_priority = 'normal';
    }

	$versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	if( in_array( PROFILE_BUILDER, $versions ) ) {
		$wppb_module_settings = get_option( 'wppb_module_settings' );

		if( isset( $wppb_module_settings['wppb_customRedirect'] ) && $wppb_module_settings['wppb_customRedirect'] == 'show' && $redirect_priority != 'top' && function_exists( 'wppb_custom_redirect_url' ) ) {
			$redirect_url = wppb_custom_redirect_url( $redirect_type, $redirect_url, $user, $user_role );
		}
	}

	if( ! empty( $redirect_url ) ) {
		$redirect_url = ( wppb_check_missing_http( $redirect_url ) ? 'http://'. $redirect_url : $redirect_url );
	}

	return $redirect_url;
}

/**
 * Function that builds the redirect
 *
 * @param	string		$redirect_url	- redirect URL
 * @param	int			$redirect_delay	- redirect delay in seconds
 * @param	null|string	$redirect_type	- the type of the redirect
 * @param	null|array	$form_args		- form args if set
 *
 * @return	string	$redirect_message
 */
function wppb_build_redirect( $redirect_url, $redirect_delay, $redirect_type = NULL, $form_args = NULL ) {
	if( isset( $redirect_type ) ) {
		$redirect_url = apply_filters( 'wppb_'. $redirect_type .'_redirect', $redirect_url );
	}

	$redirect_message = '';

	if( ! empty( $redirect_url ) ) {
		$redirect_url = ( wppb_check_missing_http( $redirect_url ) ? 'http://'. $redirect_url : $redirect_url );

		if( $redirect_delay == 0 ) {
			$redirect_message = '<meta http-equiv="Refresh" content="'. $redirect_delay .';url='. $redirect_url .'" />';
		} else {
			$redirect_url_href = apply_filters( 'wppb_redirect_url', '<a href="'. $redirect_url .'">'. __( 'here', 'profile-builder' ) .'</a>', $redirect_url, $redirect_type, $form_args );
			$redirect_message = apply_filters( 'wppb_redirect_message_before_returning', '<p class="redirect_message">'. sprintf( wp_slash( __( 'You will soon be redirected automatically. If you see this page for more than %1$d seconds, please click %2$s.%3$s', 'profile-builder' ) ), $redirect_delay, $redirect_url_href, '<meta http-equiv="Refresh" content="'. $redirect_delay .';url='. $redirect_url .'" />' ) .'</p>', $redirect_url, $redirect_delay, $redirect_url_href, $redirect_type, $form_args );
		}
	}

	return $redirect_message;
}

/**
 * Function that strips the script tags from an input
 * @param $string
 * @return mixed
 */
function wppb_sanitize_value( $string ){
	return preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $string );
}

/**
 * Function that receives a user role and returns its label.
 * Returns the original role if not found.
 *
 * @since v.2.7.1
 *
 * @param string $role
 *
 * @return string
 */
function wppb_get_role_name($role){
    global $wp_roles;

    if ( array_key_exists( $role, $wp_roles->role_names ) )
        return $wp_roles->role_names[$role];

    return $role;
}

/**
 * Function that receives a user role label and returns its slug.
 * Returns the original role label if not found.
 *
 * @since v.3.5.6
 *
 * @param string $role
 *
 * @return string
 */
function wppb_get_role_slug($role) {
	global $wp_roles;

    foreach ( $wp_roles->role_names as $slug => $label ) {
        if ( $label === $role) {
            return $slug;
        }
    }

	return $role;
}

/**
 * Functionality for Private Website start
 */
add_action( 'template_redirect', 'wppb_private_website_functionality' );
add_action( 'login_init', 'wppb_private_website_functionality', 1 );
function wppb_private_website_functionality(){
	$wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
	if( $wppb_private_website_settings != 'not_found' ){
		if( $wppb_private_website_settings['private_website'] == 'yes' ){
			if( !is_user_logged_in() ){

				//force wp-login.php if you accidentally get locked out
				global $pagenow;
				if( $pagenow === 'wp-login.php' && isset( $_GET['wppb_force_wp_login'] ) )
					return;

				//go through paths first if they are set
                if( isset( $wppb_private_website_settings['allowed_paths'] ) && !empty( $wppb_private_website_settings['allowed_paths'] ) ){
                    $allowed_paths = explode( "\r\n", $wppb_private_website_settings['allowed_paths'] );
                    $parsed_url = wp_parse_url( wppb_curpageurl() );
                    if( !empty( $parsed_url['path'] ) ) {
                        $path = $parsed_url['path'];

                        foreach ($allowed_paths as $allowed_path) {
                            if (strpos($allowed_path, '*') === false) {
                                if (trim($path, "/") === trim($allowed_path, "/")) {
                                    return;
                                }
                            } else {
                                if (strpos(ltrim($path, "/"), trailingslashit(trim(str_replace('*', '', $allowed_path), "/"))) === 0) {
                                    return;
                                }
                            }
                        }
                    }
                }

				if( isset( $wppb_private_website_settings['allowed_pages'] ) )
					$allowed_pages = $wppb_private_website_settings['allowed_pages'];
				else{
					$allowed_pages = array();
				}

				$redirect_to_id = $wppb_private_website_settings['redirect_to'];
				if( !empty( $redirect_to_id ) ) {
					$redirect_url = get_permalink($redirect_to_id);
					$allowed_pages[] = $redirect_to_id;
				}
				else {
					//don't redirect if we are already on the wp-login.php page
					if( $pagenow === 'wp-login.php' ){
						return;
					}
					else
						$redirect_url = wp_login_url(wppb_curpageurl());
				}

				$redirect_url = apply_filters( 'wppb_private_website_redirect_url', $redirect_url );
				$allowed_pages = apply_filters( 'wppb_private_website_allowed_pages', $allowed_pages );

                global $post;
				if( !isset( $post ) || ( isset($post) && $post->ID == 0 ) ) {//added || ( isset($post) && $post->ID == 0 )  for a compatibility issue with BuddyPress where the ID was 0 for a page
				    if( function_exists('url_to_postid') ) {
                        $post_id = url_to_postid(wppb_curpageurl());//try to get the id from the actual url
                    }else {
                        $post_id = 0;
                    }
                }
				else
                    $post_id = $post->ID;

                if( ( !in_array( $post_id, $allowed_pages ) && $redirect_url !== strtok( wppb_curpageurl(), '?' ) ) || is_search() ){
                    nocache_headers();
                    if( apply_filters( 'wppb_private_website_redirect_add_query_args', true ) && current_filter() == 'template_redirect' ) {
                        $redirect_url = add_query_arg( 'wppb_referer_url', urlencode( esc_url( wppb_curpageurl() ) ), $redirect_url );
                    }
                    wp_safe_redirect( $redirect_url );
                    exit;
                }

			}
		}
	}
}

//add classes on the body to know if we have enabled private website options
add_filter( 'body_class', 'wppb_private_website_body_classes' );
function wppb_private_website_body_classes( $classes ){
    $wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
    if( $wppb_private_website_settings != 'not_found' ) {
        if ($wppb_private_website_settings['private_website'] == 'yes') {
            if (!is_user_logged_in()) {
                $classes[] = 'wppb-private-website';
                if ($wppb_private_website_settings['hide_menus'] == 'yes') {
                    $classes[] = 'wppb-private-website-hide-menus';
                }
            }
        }
    }

    return $classes;
}


/**
 * Disable RSS
 */
add_action('do_feed', 'wppb_disable_feed', 1);
add_action('do_feed_rdf', 'wppb_disable_feed', 1);
add_action('do_feed_rss', 'wppb_disable_feed', 1);
add_action('do_feed_rss2', 'wppb_disable_feed', 1);
add_action('do_feed_atom', 'wppb_disable_feed', 1);
add_action('do_feed_rss2_comments', 'wppb_disable_feed', 1);
add_action('do_feed_atom_comments', 'wppb_disable_feed', 1);
function wppb_disable_feed() {
	$wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
	if( $wppb_private_website_settings != 'not_found' ) {
		if ($wppb_private_website_settings['private_website'] == 'yes') {
			if (!is_user_logged_in()) {
				wp_die( wp_kses_post( sprintf( __('No feed available,please visit our <a href="%s">homepage</a>!', 'profile-builder' ), get_bloginfo('url') ) ) );
			}
		}
	}
}


/**
 * Disable REST
 */
//add_filter('rest_enabled', 'wppb_disable_rest'); // this is depracated
add_filter('rest_jsonp_enabled', 'wppb_disable_rest');
function wppb_disable_rest( $bool ){
	$wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
	if( $wppb_private_website_settings != 'not_found' ) {
		if ($wppb_private_website_settings['private_website'] == 'yes') {
		    if ( isset( $wppb_private_website_settings[ 'disable_rest_api' ] ) && $wppb_private_website_settings[ 'disable_rest_api' ] == 'no' ) {
		        return $bool;
            }
			if (!is_user_logged_in()) {
				return false;
			}
		}
	}
	return $bool;
}

/* I should test this to not create any problems */
add_filter('rest_authentication_errors', 'wppb_disable_rest_api_authentication', 10, 1 );
function wppb_disable_rest_api_authentication($result) {
    if (!empty($result)) {
        return $result;
    }

    $wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
    if( $wppb_private_website_settings != 'not_found' ) {
        if ($wppb_private_website_settings['private_website'] == 'yes') {
            if ( isset( $wppb_private_website_settings[ 'disable_rest_api' ] ) && $wppb_private_website_settings[ 'disable_rest_api' ] == 'no' ) {
                return $result;
            }
            if (!is_user_logged_in() && isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] !== "/wp-json/jwt-auth/v1/token" && $_SERVER['REQUEST_URI'] !== "/wp-json/jwt-auth/v1/token/validate") {
                return new WP_Error('rest_not_logged_in', __( 'You are not currently logged in.', 'profile-builder' ), array('status' => 401));
            }
        }
    }

    return $result;
}

/**
 * We can hide all menu items
 */
add_filter('wp_nav_menu', 'wppb_hide_menus');
add_filter('wp_page_menu', 'wppb_hide_menus');
function wppb_hide_menus( $menu ){
	$wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
	if( $wppb_private_website_settings != 'not_found' ) {
		if ($wppb_private_website_settings['private_website'] == 'yes') {
			if ( !is_user_logged_in() && ( !empty($wppb_private_website_settings['hide_menus']) &&  $wppb_private_website_settings['hide_menus'] == 'yes' ) ) {
				return '';
			}
		}
	}
	return $menu;
}

/**
 * Functionality for Private Website end
 */

/**
 * Functionality GDPR compliance start
 */

//hook into the wp export compatibility
add_filter( 'wp_privacy_personal_data_exporters', 'wppb_register_profile_builder_wp_exporter', 10 );
function wppb_register_profile_builder_wp_exporter( $exporters ) {
    $exporters['profile-builder'] = array(
        'exporter_friendly_name' => __( 'Profile Builder', 'profile-builder' ),
        'callback' => 'wppb_profile_builder_wp_exporter',
    );
    return $exporters;
}
/* function to add aour user meta to wp exporter */
function wppb_profile_builder_wp_exporter( $email_address, $page = 1 ) {

    $export_items = array();

    $form_fields = get_option( 'wppb_manage_fields' );

    if( !empty( $form_fields ) ) {
        $user = get_user_by( 'email', $email_address );
        if( $user ) {

            $item_id = "user-meta-{$user->ID}";
            $group_id = 'user-meta';
            $group_label = __('User Meta' , 'profile-builder' );
            $data = array();

            $all_meta_for_user = get_user_meta( $user->ID );
            if( !empty( $all_meta_for_user ) ) {
                foreach ($form_fields as $form_field) {

                    if (!empty($form_field['meta-name']) && strpos($form_field['field'], 'Default') === false) {
                        $user_meta_value = $all_meta_for_user[$form_field['meta-name']][0];
                        if( !empty( $user_meta_value ) ){


                            $data[] = array(
                                        'name' => $form_field['field-title'],
                                        'value' => $user_meta_value
                                      );
                        }
                    }
                }

                $export_items[] = array(
                    'group_id' => $group_id,
                    'group_label' => $group_label,
                    'item_id' => $item_id,
                    'data' => $data,
                );

            }
        }
    }


    return array(
        'data' => $export_items,
        'done' => true,
    );
}

/**
 * Hook to delete a user through the GDPR Delete button
 */
add_action( 'template_redirect', 'wppb_gdpr_delete_user') ;
function wppb_gdpr_delete_user() {

    if( isset( $_GET['wppb_user'] ) && ! empty( $_GET['wppb_user'] ) ) {

        $edited_user_id = get_current_user_id();
        if ( ( !is_multisite() && current_user_can('edit_users') ) || ( is_multisite() && current_user_can('manage_network') ) ) {
                $edited_user_id = absint($_GET['wppb_user']);
        }

        if (isset($_REQUEST['wppb_action']) && $_REQUEST['wppb_action'] == 'wppb_delete_user' && isset( $_REQUEST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['wppb_nonce'] ), 'wppb-user-own-account-deletion') && isset($_REQUEST['wppb_user']) && $edited_user_id == $_REQUEST['wppb_user']) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            $user = new WP_User( absint( $_REQUEST['wppb_user'] ) );

            if (!empty($user->roles)) {
                foreach ($user->roles as $role) {
                    if ($role != 'administrator') {
                        wp_delete_user( absint( $_REQUEST['wppb_user'] ) );
                    }
                }
            }

            $args = array('wppb_user', 'wppb_action', 'wppb_nonce');
            nocache_headers();
            wp_redirect(remove_query_arg($args));
        }
    }
}


/**
 * Functionality GDPR compliance end
 */

/**
 * Function that checks if Admin Approval is enabled
 */
function wppb_get_admin_approval_option_value(){
    $wppb_general_settings = get_option( 'wppb_general_settings', 'not_found' );
    if( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/features/admin-approval/admin-approval.php' ) && $wppb_general_settings != 'not_found' && !empty( $wppb_general_settings['adminApproval'] ) &&  $wppb_general_settings['adminApproval'] === 'yes' )
        return 'yes';
    else
        return 'no';
}

/**
 * Function that checks if conditional fields feature exists
 * @return bool
 */
function wppb_conditional_fields_exists(){
    if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/features/conditional-fields/conditional-fields.php' ) )
        return true;
    else
        return false;
}

/**
 * Add support for [wppb-embed] shortcode inside userlisting as the default [embed] is weird and doesn't work outside the_content
 */
add_shortcode('wppb-embed', 'wppb_embed');
function wppb_embed($atts, $content){
	$atts = shortcode_atts( array(
		'width' => '',
		'height' => ''
	), $atts, 'wppb-embed' );

	global $wp_embed;
	if(empty($atts['width']) || empty($atts['height'])){
		$content = $wp_embed->run_shortcode('[embed]'.$content.'[/embed]');
	} else {
		$content = $wp_embed->run_shortcode('[embed width="'.$atts['width'].'" height="'.$atts['height'].'"]'.$content.'[/embed]');
	}

	return $content;
}

/**
 * Function to determine if an add-on is active
 */

function wppb_check_if_add_on_is_active( $slug ){
    //the old modules part
    if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists(WPPB_PAID_PLUGIN_DIR . '/add-ons/add-ons.php')) {
        $wppb_module_settings = get_option('wppb_module_settings', 'not_found');
        if ($wppb_module_settings != 'not_found') {
            foreach ($wppb_module_settings as $add_on_slug => $status) {
                if ($slug == $add_on_slug) {
                    if ($status === 'hide')
                        return false;
                    elseif ($status === 'show')
                        return true;
                }
            }
        }
    }

    //the free addons part
    $wppb_free_add_ons_settings = get_option( 'wppb_free_add_ons_settings', array() );
    if ( !empty( $wppb_free_add_ons_settings ) ){
        foreach( $wppb_free_add_ons_settings as $add_on_slug => $status ){
            if( $slug == $add_on_slug ){
                return $status;
            }
        }
    }

    //the advanced addons part
    $wppb_advanced_add_ons_settings = get_option( 'wppb_advanced_add_ons_settings', array() );
    if ( !empty( $wppb_advanced_add_ons_settings ) ){
        foreach( $wppb_advanced_add_ons_settings as $add_on_slug => $status ){
            if( $slug == $add_on_slug ){
                return $status;
            }
        }
    }

    return false;
}
