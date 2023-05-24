<?php
/*
 * This file has the sole purpose to help solve compatibility issues with other plugins
 *
 */


    /****************************************************
     * Plugin Name: Captcha
     * Plugin URI: https://wordpress.org/plugins/captcha/
     ****************************************************/

    /*
     * Function that ads the Captcha HTML to Profile Builder login form
     *
     */
    if( function_exists('cptch_display_captcha_custom') ) {
        function wppb_captcha_add_form_login($form_part, $args) {

            $cptch_options = get_option('cptch_options');
            if( !empty( $cptch_options['cptch_login_form'] ) && 1 == $cptch_options['cptch_login_form'] )
                $form_part .= cptch_display_captcha_custom();
            elseif( !empty( $cptch_options['forms']['wp_login']['enable'] ) && $cptch_options['forms']['wp_login']['enable'] )
                $form_part .= cptch_display_captcha_custom();

            return $form_part;
        }

        add_filter('login_form_middle', 'wppb_captcha_add_form_login', 10, 2);
    }


    /*
     * Function that ads the Captcha HTML to Profile Builder form builder
     *
     */
    if( function_exists('cptch_display_captcha_custom') ) {

        function wppb_captcha_add_form_form_builder( $output, $form_location = '' ) {

            if ( $form_location == 'register' ) {
                $cptch_options = get_option('cptch_options');

                if (!empty( $cptch_options['cptch_register_form'] ) && 1 == $cptch_options['cptch_register_form']) {
                    $output = '<li>' . cptch_display_captcha_custom() . '</li>' . $output;
                }
                elseif( !empty( $cptch_options['forms']['wp_register']['enable'] ) && $cptch_options['forms']['wp_register']['enable'] )
                    $output = '<li>' . cptch_display_captcha_custom() . '</li>' . $output;
            }


            return $output;
        }

        add_filter( 'wppb_after_form_fields', 'wppb_captcha_add_form_form_builder', 10, 2 );
    }


    /*
     * Function that displays the Captcha error on register form
     *
     */
    if( function_exists('cptch_register_check') ) {

        function wppb_captcha_register_form_display_error() {

            $cptch_options = get_option('cptch_options');

            if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'register' && ( ( !empty( $cptch_options['cptch_register_form'] ) && 1 == $cptch_options['cptch_register_form'] ) || ( !empty( $cptch_options['forms']['wp_register']['enable'] ) && $cptch_options['forms']['wp_register']['enable'] ) ) ) {

                $result = cptch_register_check(new WP_Error());

                if ($result->get_error_message('captcha_error'))
                    echo '<p class="wppb-error">' . esc_html( $result->get_error_message('captcha_error') ) . '</p>';

            }

        }

        add_action('wppb_before_register_fields', 'wppb_captcha_register_form_display_error' );
    }

    /*
     * Function that validates captcha value on register form
     *
     */
    if( function_exists('cptch_register_check') ) {

        function wppb_captcha_register_form_check_value($output_field_errors) {

            $cptch_options = get_option('cptch_options');

            if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'register' && ( ( !empty( $cptch_options['cptch_register_form'] ) && 1 == $cptch_options['cptch_register_form'] ) || ( !empty( $cptch_options['forms']['wp_register']['enable'] ) && $cptch_options['forms']['wp_register']['enable'] ) ) ) {

                $result = cptch_register_check(new WP_Error() );

                if ($result->get_error_message('captcha_error'))
                    $output_field_errors[] = $result->get_error_message('captcha_error');
            }


            return $output_field_errors;
        }

        add_filter('wppb_output_field_errors_filter', 'wppb_captcha_register_form_check_value');
    }


    /*
     * Function that ads the Captcha HTML to PB custom recover password form
     *
     */
    if ( function_exists('cptch_display_captcha_custom') ) {

        function wppb_captcha_add_form_recover_password($output, $username_email = '') {

            $cptch_options = get_option('cptch_options');

            if (!empty( $cptch_options['cptch_lost_password_form'] ) && 1 == $cptch_options['cptch_lost_password_form']) {
                $output = str_replace('</ul>', '<li>' . cptch_display_captcha_custom() . '</li>' . '</ul>', $output);
            }
            elseif( !empty( $cptch_options['forms']['wp_lost_password']['enable'] ) && $cptch_options['forms']['wp_lost_password']['enable'] ){
                $output = str_replace('</ul>', '<li>' . cptch_display_captcha_custom() . '</li>' . '</ul>', $output);
            }


            return $output;
        }

        add_filter('wppb_recover_password_generate_password_input', 'wppb_captcha_add_form_recover_password', 10, 2);
    }

    /*
     * Function that changes the messageNo from the Recover Password form
     *
     */
    if( function_exists('cptch_register_check') ) {

        function wppb_captcha_recover_password_message_no($messageNo) {

            $cptch_options = get_option('cptch_options');

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'recover_password' && ( ( !empty( $cptch_options['cptch_lost_password_form'] ) && 1 == $cptch_options['cptch_lost_password_form'] ) || ( !empty( $cptch_options['forms']['wp_lost_password']['enable'] ) && $cptch_options['forms']['wp_lost_password']['enable'] ) ) ) {

                $result = cptch_register_check(new WP_Error());

                if ($result->get_error_message('captcha_error') || $result->get_error_message('captcha_error'))
                    $messageNo = '';

            }

            return $messageNo;
        }

        add_filter('wppb_recover_password_message_no', 'wppb_captcha_recover_password_message_no');
    }

    /*
     * Function that adds the captcha error message on Recover Password form
     *
     */
    if( function_exists('cptch_register_check') ) {

        function wppb_captcha_recover_password_displayed_message1($message) {

            $cptch_options = get_option('cptch_options');

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'recover_password' && ( ( !empty( $cptch_options['cptch_lost_password_form'] ) && 1 == $cptch_options['cptch_lost_password_form'] ) || ( !empty( $cptch_options['forms']['wp_lost_password']['enable'] ) && $cptch_options['forms']['wp_lost_password']['enable'] ) ) ) {

                $result = cptch_register_check(new WP_Error());
                $error_message = '';

                if ($result->get_error_message('captcha_error'))
                    $error_message = $result->get_error_message('captcha_error');

                if( empty($error_message) )
                    return $message;

                if ( ($message == '<p class="wppb-warning">wppb_captcha_error</p>') || ($message == '<p class="wppb-warning">wppb_recaptcha_error</p>') )
                    $message = '<p class="wppb-warning">' . $error_message . '</p>';
                else
                    $message = $message . '<p class="wppb-warning">' . $error_message . '</p>';

            }

            return $message;
        }

        add_filter('wppb_recover_password_displayed_message1', 'wppb_captcha_recover_password_displayed_message1');
    }


    /*
     * Function that changes the default success message to wppb_captcha_error if the captcha
     * doesn't validate so that we can change the message displayed with the
     * wppb_recover_password_displayed_message1 filter
     *
     */
    if( function_exists('cptch_register_check') ) {

        function wppb_captcha_recover_password_sent_message_1($message) {

            $cptch_options = get_option('cptch_options');

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'recover_password' && ( ( !empty( $cptch_options['cptch_lost_password_form'] ) && 1 == $cptch_options['cptch_lost_password_form'] ) || ( !empty( $cptch_options['forms']['wp_lost_password']['enable'] ) && $cptch_options['forms']['wp_lost_password']['enable'] ) ) ) {

                $result = cptch_register_check( new WP_Error() );

                if ($result->get_error_message('captcha_error') )
                    $message = 'wppb_captcha_error';

            }

            return $message;
        }

        add_filter('wppb_recover_password_sent_message1', 'wppb_captcha_recover_password_sent_message_1');
    }



	/****************************************************
	 * Plugin Name: Easy Digital Downloads
	 * Plugin URI: https://wordpress.org/plugins/easy-digital-downloads/
	 ****************************************************/

		/* Function that checks if a user is approved before loggin in, when admin approval is on */
		function wppb_check_edd_login_form( $auth_cookie, $expire, $expiration, $user_id, $scheme ) {
			$wppb_generalSettings = get_option('wppb_general_settings', 'not_found');

			if( $wppb_generalSettings != 'not_found' ) {
				if( wppb_get_admin_approval_option_value() === 'yes' ) {
					if( isset( $_REQUEST['edd_login_nonce'] ) ) {
						if( wp_get_object_terms( $user_id, 'user_status' ) ) {
							if( isset( $_REQUEST['edd_redirect'] ) ) {
								wp_redirect( esc_url_raw( $_REQUEST['edd_redirect'] ) );
								edd_set_error( 'user_unapproved', __('Your account has to be confirmed by an administrator before you can log in.', 'profile-builder') );
								edd_get_errors();
								edd_die();
							}
						}
					}
				}
			}
		}
		add_action( 'set_auth_cookie', 'wppb_check_edd_login_form', 10, 5 );
		add_action( 'set_logged_in_cookie', 'wppb_check_edd_login_form', 10, 5 );


        /****************************************************
         * Plugin Name: Page Builder by SiteOrigin && Yoast SEO
         * Plugin URI: https://wordpress.org/plugins/siteorigin-panels/  && https://wordpress.org/plugins/wordpress-seo/
         * When both plugins are activated SEO generates description tags that execute shortcodes because of the filter on "the_content" added by Page Builder when generating the excerpt
         ****************************************************/
        if( function_exists( 'siteorigin_panels_filter_content' ) ){
            add_action( 'wpseo_head', 'wppb_remove_siteorigin_panels_content_filter', 8 );
            function wppb_remove_siteorigin_panels_content_filter()
            {
                global $post;
                if( !empty( $post->post_content ) ) {
                    if (has_shortcode($post->post_content, 'wppb-register') || has_shortcode($post->post_content, 'wppb-edit-profile') || has_shortcode($post->post_content, 'wppb-login') || has_shortcode($post->post_content, 'wppb-list-users'))
                        remove_filter('the_content', 'siteorigin_panels_filter_content');
                }
            }

            add_filter( 'wpseo_head', 'wppb_add_back_siteorigin_panels_content_filter', 50 );
            function wppb_add_back_siteorigin_panels_content_filter()
            {
                global $post;
                if( !empty( $post->post_content ) ) {
                    if (has_shortcode($post->post_content, 'wppb-register') || has_shortcode($post->post_content, 'wppb-edit-profile') || has_shortcode($post->post_content, 'wppb-login') || has_shortcode($post->post_content, 'wppb-list-users'))
                        add_filter('the_content', 'siteorigin_panels_filter_content');
                }
            }
        }

        /****************************************************
         * Plugin Name: WPML
         * Compatibility with wp_login_form() that wasn't getting the language code in the site url
         ****************************************************/
        add_filter( 'site_url', 'wppb_wpml_login_form_compatibility', 10, 4 );
        function wppb_wpml_login_form_compatibility( $url, $path, $scheme, $blog_id ){
            global $wppb_login_shortcode;
            if( defined( 'ICL_LANGUAGE_CODE' ) && $wppb_login_shortcode ){
                if( $path == 'wp-login.php' ) {
                    if( !empty( $_GET['lang'] ) )
                        return add_query_arg('lang', ICL_LANGUAGE_CODE, $url);
                    else{
                        if( function_exists('curl_version') ) {
                            /* let's see if the directory structure exists for wp-login.php */
                            $headers = wp_remote_head( trailingslashit( get_home_url() ) . $path, array( 'timeout' => 2 ) );
                            if (!is_wp_error($headers)) {
                                if ($headers['response']['code'] == 200) {
                                    return trailingslashit( get_home_url() ) . $path;
                                }
                            }
                        }
                        return add_query_arg('lang', ICL_LANGUAGE_CODE, $url);
                    }
                }
            }
            return $url;
        }

        /****************************************************
         * Plugin Name: ACF
         * Compatibility with Role Editor where ACF includes it's own select 2 and a bit differently then the standard hooks
         ****************************************************/
        add_action( 'admin_enqueue_scripts', 'wppb_acf_and_user_role_select_2_compatibility' );
        function wppb_acf_and_user_role_select_2_compatibility(){
            $post_type = get_post_type();
            if( !empty( $post_type ) && $post_type == 'wppb-roles-editor' )
                remove_all_actions('acf/input/admin_enqueue_scripts');
        }

        /****************************************************
         * Theme Enfold
         * Compatibility with Enfold theme that removes the wp.media scripts from the frontend for some reason starting from version 4.3 From what I understand they only allow it on media formats or posts that contain media embeds
         ****************************************************/
        if( ! function_exists( 'av_video_assets_required' ) ){
            function av_video_assets_required(){
                return true;
            }
        }


        /****************************************************
         * Secupress Compatibility
         * Compatibility with Secupress plugin when activating Move the login and admin pages
         ****************************************************/
        if( isset( $_POST['wppb_login'] ) ) {
            remove_action('login_init', 'secupress_move_login_maybe_deny_login_page', 0);
            remove_action('secure_auth_redirect', 'secupress_move_login_maybe_deny_login_page', 0);
        }


        /****************************************************
         * Divi Pagebuilder Compatibility
         * Compatibility with Divi Pagebuilder and content restriction. Basically we try to force a restricted page that was created with the pagebuilder to display as a normal page
         ****************************************************/
        if( function_exists('et_setup_theme') ) {
            add_filter('get_post_metadata', 'wppb_divi_page_builder_compatibility', 100, 4);
            function wppb_divi_page_builder_compatibility( $metadata, $object_id, $meta_key, $single ){
                if (!is_admin()) {
                    if (isset($meta_key) && '_et_pb_use_builder' == $meta_key) {
                        if (wppb_content_restriction_filter_content('not_restricted', get_post($object_id)) != 'not_restricted') {
                            return 'off';
                        }
                    }

                    if (isset($meta_key) && '_wp_page_template' == $meta_key) {
                        if (wppb_content_restriction_filter_content('not_restricted', get_post($object_id)) != 'not_restricted') {
                            return 'default';
                        }
                    }
                }

                // Return original if the check does not pass
                return $metadata;

            }
        }


        /****************************************************
         * Plugin Name: xCRUD
         * Compatibility in terms of preventing jQuery to be loaded twice
         ****************************************************/
        if ( class_exists( 'Xcrud_config' ) ){
            Xcrud_config::$load_jquery = apply_filters( 'wppb_xcrud_jquery_compatibility', false );
        }

    /****************************************************
     * Plugin Name: bbPress Messages
     * Plugin URI: https://wordpress.org/plugins/bbp-messages/
     * This plugin relies on the 'bbp_template_before_user_profile' hook
     ****************************************************/
    if ( function_exists( 'bbp_messages_loaded' ) ){
        add_action( 'wppb_bbp_template_before_user_profile', 'wppb_bbp_messages_compatibility' );
        function wppb_bbp_messages_compatibility (){
            do_action( 'bbp_template_before_user_profile' );
        }
    }

	/****************************************************
	 * Plugin Name: LearnDash LMS
	 * This plugin hijacks the 'wp_login_failed' hook not allowing the PB login form to show errors
	 ****************************************************/
	if ( class_exists( 'Semper_Fi_Module' ) ){
		add_action( 'wppb_process_login_start', 'wppb_learndash_compatibility_login_start' );
		function wppb_learndash_compatibility_login_start (){
			remove_action( 'wp_login_failed', 'learndash_login_failed', 1 );
		}
		add_action( 'wppb_process_login_end', 'wppb_learndash_compatibility_login_end' );
		function wppb_learndash_compatibility_login_end (){
			add_action( 'wp_login_failed', 'learndash_login_failed', 1, 1 );
		}
	}

    /****************************************************
     * Plugin Name: MailPoet
     * By default MailPoet disables custom scripts and styles to prevent JavaScript and CSS conflicts with their interface
     * With these filters we can whitelist our styles and scripts
     ****************************************************/

    function wppb_mailpoet_conflict_resolver_whitelist_style($styles) {
        $current_file_path = explode('/',plugin_basename( __FILE__ ));
        $plugin_name = reset($current_file_path);
        array_push($styles, $plugin_name);
        return $styles;
    }
    add_filter('mailpoet_conflict_resolver_whitelist_style', 'wppb_mailpoet_conflict_resolver_whitelist_style');

    function wppb_mailpoet_conflict_resolver_whitelist_script($scripts) {
        $current_file_path = explode('/',plugin_basename( __FILE__ ));
        $plugin_name = reset($current_file_path);
        array_push($scripts, $plugin_name);
        return $scripts;
    }
    add_filter('mailpoet_conflict_resolver_whitelist_script', 'wppb_mailpoet_conflict_resolver_whitelist_script');

    /****************************************************
     * Plugin Name: Advanced Product Fields for Woocommerce
     * Plugin URI: https://wordpress.org/plugins/advanced-product-fields-for-woocommerce/
     * When both plugins are activated an '&&' operator from the JS code APF adds to product $content for its Datepicker is encoded
     ****************************************************/
    if( function_exists( 'SW_WAPF_PRO_auto_loader' ) ){
        function wppb_WAPF_compatibility( $content )
        {
            $content = str_replace( "&#038;&#038;", "&&", $content );
            return $content;
        }
        add_filter('the_content', 'wppb_WAPF_compatibility', 13, 1);
    }

    /****************************************************
     * Plugin Name: WooCommerce
     * Plugin URI: https://wordpress.org/plugins/woocommerce/
     * Don't allow WooCommerce to Login User after registration if PB Admin Approval is Active
     ****************************************************/
    if( wppb_get_admin_approval_option_value() === 'yes' ) {
        add_filter( 'woocommerce_registration_auth_new_customer', '__return_false' );
    }
