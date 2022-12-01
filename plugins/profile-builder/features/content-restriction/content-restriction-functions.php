<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Verifies whether the current post or the post with the provided id has any restrictions in place */
function wppb_content_restriction_is_post_restricted( $post_id = null ) {

    //fixes some php warnings with Onfleek theme
    if( is_array( $post_id ) || empty( $post_id ) )
        $post_id = null;

    global $post, $wppb_show_content, $wppb_is_post_restricted_arr;

    // If we have a cached result, return it
    if( isset( $wppb_is_post_restricted_arr[$post_id] ) ) {
        return $wppb_is_post_restricted_arr[$post_id];
    }

    $post_obj = $post;

    if( ! is_null( $post_id ) ) {
        $post_obj = get_post( $post_id );
    }

    // This filter was added in order to take advantage of the existing functions that hook to the_content and check to see if the post is restricted or not
    $t = apply_filters( 'wppb_content_restriction_post_check', '', $post_obj );

    // Cache the result for further usage
    if( $wppb_show_content === false ) {
        $wppb_is_post_restricted_arr[$post_id] = true;
    } else {
        $wppb_is_post_restricted_arr[$post_id] = false;
    }

    return $wppb_is_post_restricted_arr[$post_id];

}

/* Returns the restriction message added by the admin in the settings page or a default message if the first one is missing */
function wppb_get_restriction_content_message( $message_type = '', $post_id = 0 ) {

    $wppb_content_restriction_settings = get_option( 'wppb_content_restriction_settings', 'not_found' );
    $wppb_content_restriction_message  = '';

    if( $message_type == 'logged_out' ) {
        $wppb_content_restriction_message = ( ( $wppb_content_restriction_settings != 'not_found' && ! empty( $wppb_content_restriction_settings['message_logged_out'] ) ) ? $wppb_content_restriction_settings['message_logged_out'] : __( 'You must be logged in to view this content.', 'profile-builder' ) );
    } elseif ( $message_type == 'logged_in' ) {
        $wppb_content_restriction_message = ( ( $wppb_content_restriction_settings != 'not_found' && ! empty( $wppb_content_restriction_settings['message_logged_in'] ) ) ? $wppb_content_restriction_settings['message_logged_in'] : __( 'This content is restricted for your user role.', 'profile-builder' ) );
    } elseif ( $message_type == 'purchasing_restricted' ) {
        $wppb_content_restriction_message = ( ( $wppb_content_restriction_settings != 'not_found' && ! empty( $wppb_content_restriction_settings['purchasing_restricted'] ) ) ? $wppb_content_restriction_settings['purchasing_restricted'] : __( 'This product cannot be purchased by your user role.', 'profile-builder' ) );
    } else {
        $wppb_content_restriction_message = apply_filters( 'wppb_get_restriction_content_message_default', $wppb_content_restriction_message, $message_type, $wppb_content_restriction_settings );
    }

    $custom_message_enabled = get_post_meta( $post_id, 'wppb-content-restrict-messages-enabled', true );

    if( ! empty( $post_id ) && ! empty( $custom_message_enabled ) ) {
        $custom_message = get_post_meta( $post_id, 'wppb-content-restrict-message-' . $message_type, true );

        if( ! empty( $custom_message ) ) {
            $wppb_content_restriction_message = $custom_message;
        }
    }

    return wp_kses_post( $wppb_content_restriction_message );

}

/* Returns the restriction message with any tags processed */
function wppb_content_restriction_process_content_message( $type, $user_ID, $post_id = 0 ) {

    $message    = wppb_get_restriction_content_message( $type, $post_id );
    $user_info  = get_userdata( $user_ID );
    $message    = wppb_content_restriction_merge_tags( $message, $user_info, $post_id );

    return '<span class="wppb-frontend-restriction-message wppb-content-restriction-message">'. $message .'</span>';

}

/* Return the restriction message to be displayed to the user. If the current post is not restricted / it was not checked to see if it is restricted an empty string is returned */
function wppb_content_restriction_get_post_message( $post_id = 0 ) {

    global $post, $user_ID, $wppb_show_content;

    if( ! empty( $post_id ) ) {
        $post = get_post( $post_id );
    }

    // If the $wppb_show_content global is different than false then the post is either not restricted or not processed for restriction
    if( $wppb_show_content !== false ) {
        return '';
    }

    if( ! is_user_logged_in() ) {
        $message_type = 'logged_out';
    } else {
        $message_type = 'logged_in';
    }

    $message_type = apply_filters( 'wppb_get_restricted_post_message_type', $message_type );

    $message = wppb_content_restriction_process_content_message( $message_type, $user_ID, $post->ID );

    // Filter the restriction message before returning it
    $message = apply_filters( 'wppb_restriction_message_' . $message_type, $message, $post->post_content, $post, $user_ID );

    return do_shortcode( $message );

}

/* Checks to see if the current post is restricted and if any redirect URLs are in place the user is redirected to the URL with the highest priority */
function wppb_content_restriction_post_redirect() {
    // try not to overwrite $post. Can have side-effects with other plugins.
    global $post;
    $woo_shop_or_post = $post;

    if( function_exists( 'wc_get_page_id' ) ) {//redirect restriction for woocommerce shop page
        if ( !is_singular() && !( is_post_type_archive('product') || is_page(wc_get_page_id('shop')) ) ){
            return;
        }

        if( is_post_type_archive('product') || is_page(wc_get_page_id('shop')) ){
            $woo_shop_or_post = get_post( wc_get_page_id('shop') );
        }
    }
    else {
        if (!is_singular()) {
            return;
        }
    }

    if ( !($woo_shop_or_post instanceof WP_Post) ){
        return;
    }

    $woo_shop_or_post->ID = apply_filters( 'wppb_restricted_post_redirect_post_id', $woo_shop_or_post->ID );

    $redirect_url             = '';
    $post_restriction_type    = get_post_meta( $woo_shop_or_post->ID, 'wppb-content-restrict-type', true );
    $settings                 = get_option( 'wppb_content_restriction_settings', array() );
    $general_restriction_type = ( ! empty( $settings['restrict_type'] ) ? $settings['restrict_type'] : 'message' );

    if( $post_restriction_type !== 'redirect' && $general_restriction_type !== 'redirect' ) {
        return;
    }

    if( ! in_array( $post_restriction_type, array( 'default', 'redirect' ) ) ) {
        return;
    }

    if( ! wppb_content_restriction_is_post_restricted( $woo_shop_or_post->ID ) ) {
        return;
    }

    // Get the redirect URL from the post meta if enabled
    if( $post_restriction_type === 'redirect' ) {
        $post_redirect_url_enabled = get_post_meta( $woo_shop_or_post->ID, 'wppb-content-restrict-custom-redirect-url-enabled', true );
        $post_redirect_url         = get_post_meta( $woo_shop_or_post->ID, 'wppb-content-restrict-custom-redirect-url', true );

        $redirect_url = ( ! empty( $post_redirect_url_enabled ) && ! empty( $post_redirect_url ) ? $post_redirect_url : '' );
    }


    // If the post doesn't have a custom redirect URL set, get the default from the Settings page
    if( empty( $redirect_url ) ) {
        $redirect_url = ( ! empty( $settings['redirect_url'] ) ? $settings['redirect_url'] : '' );
    }

    if( empty( $redirect_url ) ) {
        return;
    }

    // To avoid a redirect loop we break in case the redirect URL is the same as the current page URL
    $current_url = wppb_curpageurl();

    if( $current_url == $redirect_url ) {
        return;
    }

    // Pass the correct referer URL forward
    $redirect_url = add_query_arg( array( 'wppb_referer_url' => urlencode( wppb_curpageurl() ) ), wppb_add_missing_http( $redirect_url ) );

    // Redirect
    nocache_headers();
    wp_redirect( apply_filters( 'wppb_restricted_post_redirect_url', $redirect_url ) );
    exit;
    
}
add_action( 'template_redirect', 'wppb_content_restriction_post_redirect' );

/* Function that searches and replaces merge tags with their values */
function wppb_content_restriction_merge_tags( $text, $user_info, $post_id = 0 ) {

    $merge_tags = apply_filters( 'wppb_content_restriction_merge_tags', array( 'display_name', 'unrestricted_user_roles', 'current_user_role' ) );

    if( ! empty( $merge_tags ) ) {
        foreach( $merge_tags as $merge_tag ) {
            $text = str_replace( '{{'. $merge_tag .'}}', apply_filters( 'wppb_content_restriction_merge_tag_'. $merge_tag, '', $user_info, $post_id ), $text );
        }
    }

    return $text;

}

/* Add functionality for display_name tag */
function wppb_content_restriction_tag_display_name( $value, $user_info, $post_id = 0 ) {

    if( ! empty( $user_info->display_name ) ) {
        return $user_info->display_name;
    } else if( ! empty( $user_info->user_login ) ) {
        return $user_info->user_login;
    } else {
        return '';
    }

}
add_filter( 'wppb_content_restriction_merge_tag_display_name', 'wppb_content_restriction_tag_display_name', 10, 3 );

/* Add functionality for unrestricted_user_roles tag */
function wppb_content_restriction_tag_unrestricted_user_roles( $value, $user_info, $post_id = 0 ) {

    if( $post_id != 0 ) {
        $unrestricted_user_roles = get_post_meta( $post_id, 'wppb-content-restrict-user-role' );

        if( ! empty( $unrestricted_user_roles ) ) {
            $user_roles = implode( ', ', $unrestricted_user_roles );

            return $user_roles;
        } else {
            return '';
        }
    } else {
        return '';
    }

}
add_filter( 'wppb_content_restriction_merge_tag_unrestricted_user_roles', 'wppb_content_restriction_tag_unrestricted_user_roles', 10, 3 );

/* Add functionality for current_user_role tag */
function wppb_content_restriction_tag_current_user_role( $value, $user_info, $post_id = 0 ) {

    if( ! empty( $user_info ) && ! empty( $user_info->roles ) ) {
        $user_role = implode( ', ', $user_info->roles );

        return $user_role;
    } else {
        return '';
    }

}
add_filter( 'wppb_content_restriction_merge_tag_current_user_role', 'wppb_content_restriction_tag_current_user_role', 10, 3 );

/* Content restriction shortcode */
function wppb_content_restriction_shortcode( $atts, $content = null ) {

    $args = shortcode_atts(
        array(
            'user_roles'    => array(),
            'display_to'    => '',
            'message'       => ''
        ),
        $atts
    );

    // Message to replace the content of checks do not match
    if( ! empty( $args['message'] ) ) {
        $message = '<span class="wppb-shortcode-restriction-message">' . $args['message'] . '</span>';
    } else {
        $type = ( is_user_logged_in() ? 'logged_in' : 'logged_out' );
        $message = '<span class="wppb-content-restriction-message">' . wpautop( wppb_get_restriction_content_message( $type ) ) . '</span>';
    }

    /*
     * Filter the message
     *
     * @param string $message   - the current message, whether it is the default one from the settings or
     *                            the one set in the shortcode attributes
     * @param array  $args      - the shortcode attributes
     *
     */
    $message = apply_filters( 'wppb_content_restriction_shortcode_message', $message, $args );

    if( is_user_logged_in() ) {
        // Show for administrators
        if( current_user_can( 'manage_options' ) ) {
            return do_shortcode( $content );
        }

        if( $args['display_to'] == 'not_logged_in' ) {
            return $message;
        }

        if( ! empty( $args['user_roles'] ) ) {
            $user_roles = array_map( 'trim', explode( ',', $args['user_roles'] ) );
            $user_data = get_userdata( get_current_user_id() );

            if( ! empty( $user_data->roles ) ) {
                $common_user_roles = array_intersect( $user_roles, $user_data->roles );

                if( ! empty( $common_user_roles ) ) {
                    return do_shortcode( $content );
                } else {
                    return $message;
                }
            }
        } else {
            return do_shortcode( $content );
        }
    } else {
        if( $args['display_to'] == 'not_logged_in' ) {
            return do_shortcode( $content );
        } else {
            return $message;
        }
    }

}
add_shortcode( 'wppb-restrict', 'wppb_content_restriction_shortcode' );
