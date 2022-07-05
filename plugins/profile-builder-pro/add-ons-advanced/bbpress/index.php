<?php
/**
 * Profile Builder - bbPress Add-on
 * License: GPL2
 */
/*  Copyright 2017 Cozmoslabs (www.cozmoslabs.com)

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
/*
* Make sure Profile Builder plugin is installed and active before doing anything
*/
function wppb_in_bbp_plugin_init() {
    if( function_exists( 'wppb_return_bytes' ) ) {

        /*
        * Define plugin path
        */
        define('WPPB_IN_BBP_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));


        /*
         * Include the file for creating the bbPress subpage under Profile Builder menu
         */
        if ( file_exists( WPPB_IN_BBP_PLUGIN_DIR . '/bbpress-page.php' ) )
            include_once( WPPB_IN_BBP_PLUGIN_DIR . '/bbpress-page.php' );

        /*
         *  Makes sure the 'is_plugin_active_for_network' function is defined
         */
        if (!function_exists('is_plugin_active_for_network'))
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );


        /*
         *  Check if bbPress forum plugin is active before doing anything
         */
        if ( ( in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) || ( is_plugin_active_for_network('bbpress/bbpress.php') ) )  {


            /*
             *  Add style to front-end
             */
            function wppb_in_bbp_add_plugin_stylesheet_front_end() {

                wp_register_style( 'wppb_bbp_stylesheet', plugin_dir_url(__FILE__) . 'assets/css/style.css',  array('wppb_stylesheet'));
                wp_enqueue_style( 'wppb_bbp_stylesheet' );

            }
            add_action('wp_enqueue_scripts' , 'wppb_in_bbp_add_plugin_stylesheet_front_end');


            /**
             * Register the Profile Builder - bbPress Add-on template stack.
             *
             * @since v.1.0
             *
             * @return /templates directory path
             */
            function wppb_in_bbp_get_template_directory(){
                return WPPB_IN_BBP_PLUGIN_DIR . '/templates';
            }
            if ( function_exists('bbp_register_template_stack') )
                bbp_register_template_stack( 'wppb_in_bbp_get_template_directory', 10 );


            /**
             * Add PB - bbPress Add-on templates for Edit Profile and Single Userlisting to the list of templates used by bbPress.
             * Here we're basically overwriting the default bbPress user Profile and Edit pages with the ones created via Profile Builder and selected under bbPress add-on settings tab.
             *
             * @since v.1.0
             *
             * @return $templates - array of templates used by bbPress
             */
            function wppb_in_bbp_filter_template_parts($templates, $slug, $name){

                $wppb_bbpress_settings = get_option( 'wppb_bbpress_settings', 'not_found');

                // If Hobbyist or Free version, update option to no Userlisting & default Edit Profile form
                if ( !defined('PROFILE_BUILDER') || !in_array( PROFILE_BUILDER, array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' ) ) )
                    update_option( 'wppb_bbpress_settings', array( 'UserListing' => '', 'EditProfileForm' => 'wppb-default-edit-profile' ) );

                if ($wppb_bbpress_settings != 'not_found') {

                    // Check if filter was triggered on bbPress user Edit page
                    if ($slug . '-' . $name . '.php' == 'form-user-edit.php') {

                        // Check settings to see if there is an Edit-profile form set to replace the default bbPress "Edit" tab content
                        if (!empty($wppb_bbpress_settings['EditProfileForm'])) {

                            //Overwrite default bbPress profile Edit template with the Edit-profile form set in PB
                            $key = array_search('form-user-edit.php', $templates);
                            $templates[$key] = 'pb-form-user-edit.php';
                        }

                    }

                    // Check if filter was triggered on bbPress user Profile page
                    if ($slug . '-' . $name . '.php' == 'user-profile.php') {

                        // Check settings to see if there is a Single-userlisting set to replace the default bbPress "Profile" tab content
                        if (!empty($wppb_bbpress_settings['UserListing'])) {

                            //Overwrite default bbPress user Profile template with the Single-userlisting set in PB
                            $key = array_search('user-profile.php', $templates);
                            $templates[$key] = 'pb-user-profile.php';
                        }

                    }

                    // Check if filter was triggered by the bbPress Login template`
                    if ($slug . '-' . $name . '.php' === 'form-user-login.php') {

                        // Check settings to see if the Profile Builder Login form is set to replace the default bbPress template
                        if (!empty($wppb_bbpress_settings['Login']) && $wppb_bbpress_settings['Login']==='yes') {

                            //Overwrite default bbPress Login template
                            $key = array_search('form-user-login.php', $templates);
                            $templates[$key] = 'pb-user-login.php';
                        }

                    }

                }
                return $templates;

            }
            add_filter('bbp_get_template_part' , 'wppb_in_bbp_filter_template_parts', 10, 3);



            /*
             * Add bbPress user profile info as mustache tags to User Listing (Forum Role, Topics Started & Replies Created)
             */
            add_filter('wppb_userlisting_get_merge_tags', 'wppb_in_bbp_add_bbpress_userlisting_tags', 10, 2);  //add tags to User Listing
            function wppb_in_bbp_add_bbpress_userlisting_tags( $tags, $type = '' ){

                // check if they are sort tags
                if ($type == 'sort' ){
                    $tags[] = array('name' => 'sort_bbp_topics_started', 'type' => 'sort_tag', 'unescaped' => true, 'label' => __('Topics Started', 'profile-builder'));
                    $tags[] = array('name' => 'sort_bbp_replies_created', 'type' => 'sort_tag', 'unescaped' => true, 'label' => __('Replies Created', 'profile-builder'));
                }

                else {
                    $tags[] = array('name' => 'bbp_forum_role', 'type' => 'bbp_forum_role', 'label' => __('Forum Role', 'profile-builder'));
                    $tags[] = array('name' => 'bbp_topics_started', 'type' => 'bbp_topics_started', 'label' => __('Topics Started', 'profile-builder'));
                    $tags[] = array('name' => 'bbp_replies_created', 'type' => 'bbp_replies_created', 'label' => __('Replies Created', 'profile-builder'));
                }

            return $tags;
            }


            /*
             * Add bbPress user profile info as mustache tags to Email Customizer (Forum Role, Topics Started & Replies Created)
             */
            add_filter('wppb_email_customizer_get_merge_tags', 'wppb_in_bbp_add_bbpress_ec_tags'); //add tags to Email Customizer
            function wppb_in_bbp_add_bbpress_ec_tags( $tags ){

                $tags[] = array('name' => 'bbp_forum_role', 'type' => 'bbp_forum_role', 'label' => __('Forum Role', 'profile-builder'));
                $tags[] = array('name' => 'bbp_topics_started', 'type' => 'bbp_topics_started', 'label' => __('Topics Started', 'profile-builder'));
                $tags[] = array('name' => 'bbp_replies_created', 'type' => 'bbp_replies_created', 'label' => __('Replies Created', 'profile-builder'));

                return $tags;

            }

            /* Display content in User Listing & Email Customizer for bbPress "Forum Role" user tag */
            add_filter( 'mustache_variable_bbp_forum_role', 'wppb_in_bbp_handle_tag_bbp_forum_role', 10, 4 );
            function wppb_in_bbp_handle_tag_bbp_forum_role( $value, $name, $children, $extra_values){

                if ( function_exists('bbp_get_user_display_role') ){

                    $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : get_query_var( 'author' ) );
                    return bbp_get_user_display_role( $user_id);
                }

            }

            /* Display content in User Listing & Email Customizer for bbPress "Topics Started" user tag */
            add_filter( 'mustache_variable_bbp_topics_started', 'wppb_in_bbp_handle_tag_bbp_topics_started', 10, 4 );
            function wppb_in_bbp_handle_tag_bbp_topics_started( $value, $name, $children, $extra_values){

                if ( function_exists('bbp_get_user_topic_count_raw') ){

                    $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : get_query_var( 'author' ) );
                    // Get forum topic count for a specific user
                    return bbp_get_user_topic_count_raw( $user_id );

                }

            }

            /* Display content in User Listing & Email Customizer for bbPress "Replies Created" user tag */
            add_filter( 'mustache_variable_bbp_replies_created', 'wppb_in_bbp_handle_tag_bbp_replies_created', 10, 4 );
            function wppb_in_bbp_handle_tag_bbp_replies_created( $value, $name, $children, $extra_values){

                if ( function_exists('bbp_get_user_reply_count_raw') ){

                    $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : get_query_var( 'author' ) );
                    return bbp_get_user_reply_count_raw( $user_id );
                }

            }

            /* Add sorting support for bbPress tags ( Topics Started & Replies Created) in Userlisting */
            add_filter('mustache_variable_sort_tag', 'wppb_in_bbp_userlisting_sort_tags', 12, 4);
            function wppb_in_bbp_userlisting_sort_tags( $value, $name, $children, $extra_info ){

                if ( $name == 'sort_bbp_topics_started')
                    return '<a href="'.wppb_get_new_url( 'bbp_topics_started', $extra_info ).'" class="sortLink " id="sortLink1201">'.apply_filters( 'sort_bbp_topics_started', __('Topics Started', 'profile-builder') ).'</a>';
                elseif ( $name == 'sort_bbp_replies_created' )
                    return '<a href="'.wppb_get_new_url( 'bbp_replies_created', $extra_info ).'" class="sortLink " id="sortLink1202">'.apply_filters( 'sort_bbp_replies_created', __('Replies Created', 'profile-builder') ).'</a>';

                return $value;
            }

            /* Enable sorting support by bbPress "Topics Started" and "Replies Created" in UserListing */
            function wppb_in_bbp_user_query_modifications( $query ){
                global $userlisting_args;
                global $wpdb;

                /* hopefully it won't get applied to other user queries */
                if( !empty( $userlisting_args ) ){

                    if ( isset( $_REQUEST['setSortingCriteria'] ) && trim( $_REQUEST['setSortingCriteria'] ) !== '' ) //phpcs:ignore
                        $sorting_criteria = sanitize_text_field( $_REQUEST['setSortingCriteria'] );
                    else
                        $sorting_criteria = $userlisting_args[0]['default-sorting-criteria'];

                    if ( isset( $_REQUEST['setSortingOrder'] ) && trim( $_REQUEST['setSortingOrder'] ) !== '' ) //phpcs:ignore
                        $sorting_order = sanitize_text_field( $_REQUEST['setSortingOrder'] );
                    else
                        $sorting_order = $userlisting_args[0]['default-sorting-order'];


                    switch( $sorting_criteria ){

                        case "bbp_replies_created":
                            // Replace query from in order to take into account replies cpt, not posts
                            $query->query_from = str_replace( "post_type = 'post'", "post_type = 'reply'", $query->query_from );
                            break;

                        case "bbp_topics_started":
                            // Replace query from in order to take into account topics cpt, not posts
                            $query->query_from = str_replace( "post_type = 'post'", "post_type = 'topic'", $query->query_from );
                            break;
                    }

                }
                return $query;

            }
            add_filter( 'pre_user_query', 'wppb_in_bbp_user_query_modifications', 11 );

            /*
             * Change query args for Userlisting sorting by Topics Started and Replies Created
             * */
            function wppb_in_bbp_change_user_query_args( $args ){

                if ( !empty($_REQUEST['setSortingCriteria']) && ( $_REQUEST['setSortingCriteria'] == 'bbp_topics_started' || $_REQUEST['setSortingCriteria'] == 'bbp_replies_created' ) ) {

                    // make sure the args['orderby'] is not user_meta, but post_count (we're counting posts from a certain CPT->replies, topics)
                    $args['orderby'] = 'post_count';
                    unset( $args['meta_key'] );
                }

                return $args;

            }
            add_filter('wppb_userlisting_user_query_args', 'wppb_in_bbp_change_user_query_args', 11);




        }

        else {
            /*
             * Display notice if bbPress is not active
             */
            function wppb_in_bbp_admin_notice() {
                ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e( 'bbPress needs to be installed and activated for Profile Builder - bbPress Integration Add-on to work as expected!', 'profile-builder' ); ?></p>
                </div>
            <?php
            }
            add_action( 'admin_notices', 'wppb_in_bbp_admin_notice' );
        }


        /**
         * Replace the default bbPress templates for Forums that are restricted
         *
         */
        function wppb_in_bbp_restrict_template_parts_forum( $templates, $slug, $name ) {

            $forum_id = bbp_get_forum_id();

            if( empty( $forum_id ) )
                return $templates;

            if( ! wppb_check_content_restriction_on_post_id( $forum_id ) )
                return $templates;

            // Check if current template is for the single forum content
            $key = array_search( 'content-single-forum.php', $templates );

            if( false !== $key )
                $templates[$key] = 'pb-restricted-post-message.php';

            return $templates;

        }
        if( function_exists( 'wppb_check_content_restriction_on_post_id' ) ) {
            add_filter('bbp_get_template_part', 'wppb_in_bbp_restrict_template_parts_forum', 10, 3);
        }

        /**
         * Replace the default bbPress templates for Topics that are restricted
         *
         */
        function wppb_in_bbp_restrict_template_parts_topic( $templates, $slug, $name ) {

            $topic_id = bbp_get_topic_id();
            $forum_id = bbp_get_forum_id();

            if( empty( $topic_id ) || empty( $forum_id ) )
                return $templates;

            if( ! wppb_check_content_restriction_on_post_id( $topic_id ) && ! wppb_check_content_restriction_on_post_id( $forum_id ) )
                return $templates;

            /**
             * Hide the entire topic and replies
             *
             */
            $key = array_search( 'content-single-topic.php' , $templates );
            if( false !== $key )
                $templates[$key] = 'pb-restricted-post-message.php';

            return $templates;

        }
        if( function_exists( 'wppb_check_content_restriction_on_post_id' ) ) {
            add_filter('bbp_get_template_part', 'wppb_in_bbp_restrict_template_parts_topic', 10, 3);
        }

        /**
         * Replace the default bbPress templates for Replies that are restricted
         *
         */
        function wppb_in_bbp_restrict_template_parts_reply( $templates, $slug, $name ) {

            $reply_id = bbp_get_reply_id();
            $topic_id = bbp_get_topic_id();
            $forum_id = bbp_get_forum_id();

            if( empty( $reply_id ) || empty( $topic_id ) || empty( $forum_id )  )
                return $templates;

            if( wppb_check_content_restriction_on_post_id( $reply_id ) || wppb_check_content_restriction_on_post_id( $topic_id ) || wppb_check_content_restriction_on_post_id( $forum_id ) ) {
                // Check if current template is for the single reply content
                $key = array_search('content-single-reply.php', $templates);
                if (false !== $key)
                    $templates[$key] = 'pb-restricted-post-message.php';

                // Overwrite the replies with an empty template
                $key = array_search('loop-single-reply.php', $templates);
                if (false !== $key)
                    $templates[$key] = 'pb-empty-template.php';
            }

            return $templates;

        }
        if( function_exists( 'wppb_check_content_restriction_on_post_id' ) ) {
            add_filter('bbp_get_template_part', 'wppb_in_bbp_restrict_template_parts_reply', 10, 3);
        }

    }
}
add_action( 'plugins_loaded', 'wppb_in_bbp_plugin_init', 11 );
