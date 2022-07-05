<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Hijack the content when restrictions are set on a single post */
function wppb_content_restriction_filter_content( $content, $post = null ) {

    global $user_ID, $wppb_show_content, $pms_show_content;

    if( is_null( $post ) ) {
        global $post;
    }

    /*
     * Defining this variable:
     *
     * $wppb_show_content can have 3 states: null, true and false
     *
     * - if the state is "null" the $content is showed, but it did not go through any actual filtering
     * - if the state is "true" the $content is showed, but it did go through filters that explicitly said the $content should be shown
     * - if the state is "false" the $content is not showed, it is replaced with a restriction message, thus it explicitly says that it was filtered and access is denied to it
     *
     */
    $wppb_show_content = null;

    // Show for administrators
    if( current_user_can( 'manage_options' ) ) {
        return $content;
    }

    // Check if any PMS restriction should take place. PMS restrictions have priority
    if( $pms_show_content === false ) {
        return $content;
    }

    // Get user roles that have access to this post
    if ( isset( $post ) && isset( $post->ID ) ) {
        $user_status = get_post_meta($post->ID, 'wppb-content-restrict-user-status', true);
        $post_user_roles = get_post_meta($post->ID, 'wppb-content-restrict-user-role');
    }

    if( empty( $user_status ) && empty( $post_user_roles ) ) {
        return $content;
    } else if( $user_status == 'loggedin' ) {
        if( is_user_logged_in() ) {
            if( ! empty( $post_user_roles ) ) {
                $user_data = get_userdata( $user_ID );

                foreach( $post_user_roles as $post_user_role ) {
                    foreach( $user_data->roles as $role ) {
                        if( $post_user_role == $role ) {
                            $wppb_show_content = true;
                            return $content;
                        }
                    }
                }

                $wppb_show_content = false;

                $message = wppb_content_restriction_process_content_message( 'logged_in', $user_ID, $post->ID );

                return do_shortcode( apply_filters( 'wppb_content_restriction_message_logged_in', $message, $content, $post, $user_ID ) );
            } else {
                return $content;
            }
        } else {
            // If user is not logged in prompt the correct message
            $wppb_show_content = false;

            $message = wppb_content_restriction_process_content_message( 'logged_out', $user_ID, $post->ID );

            return do_shortcode( apply_filters( 'wppb_content_restriction_message_logged_out', $message, $content, $post, $user_ID ) );
        }
    }

    return $content;

}
add_filter( 'the_content', 'wppb_content_restriction_filter_content', 12, 2 );
add_filter( 'wppb_content_restriction_post_check', 'wppb_content_restriction_filter_content', 10, 2 );

/**
 * Function that checks if a post id is restricted with profile builder
 * @param $post_id
 * @return bool true for when the post is restricted and false for when it's not
 */
function wppb_check_content_restriction_on_post_id( $post_id ){
    global $user_ID;

    // Get user roles that have access to this post
    $user_status        = get_post_meta( $post_id, 'wppb-content-restrict-user-status', true );
    $post_user_roles    = get_post_meta( $post_id, 'wppb-content-restrict-user-role' );

    if( empty( $user_status ) && empty( $post_user_roles ) ) {
        return false;
    } else if( $user_status == 'loggedin' ) {
        if( is_user_logged_in() ) {
            if( ! empty( $post_user_roles ) ) {
                $user_data = get_userdata( $user_ID );
                foreach( $post_user_roles as $post_user_role ) {
                    foreach( $user_data->roles as $role ) {
                        if( $post_user_role == $role ) {
                            return false;
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    return false;
}


/* Checks to see if the attachment image is restricted and returns false instead of the image if it is restricted */
function wppb_content_restriction_filter_attachment_image_src( $image, $attachment_id ) {

    if( is_admin() ) {
        return $image;
    }

    if( wppb_content_restriction_is_post_restricted( $attachment_id ) ) {
        return false;
    }

    return $image;

}
add_filter( 'wp_get_attachment_image_src', 'wppb_content_restriction_filter_attachment_image_src', 10, 2 );

/* Checks to see if the attachment is restricted and returns false instead of the metadata if it is restricted */
function wppb_content_restriction_filter_attachment_metadata( $data, $attachment_id ) {

    if( is_admin() ) {
        return $data;
    }

    if( wppb_content_restriction_is_post_restricted( $attachment_id ) ) {
        return false;
    }

    return $data;

}
add_filter( 'wp_get_attachment_metadata', 'wppb_content_restriction_filter_attachment_metadata', 10, 2 );

/* Checks to see if the attachment thumb is restricted and returns false instead of the thumb url if it is restricted */
function wppb_content_restriction_filter_attachment_thumb_url( $url, $attachment_id ) {

    if( is_admin() ) {
        return $url;
    }

    if( wppb_content_restriction_is_post_restricted( $attachment_id ) ) {
        return false;
    }

    return $url;

}
add_filter( 'wp_get_attachment_thumb_url', 'wppb_content_restriction_filter_attachment_thumb_url', 10, 2 );

/* Checks to see if the attachment is restricted and returns an empty string instead of the attachment url if it is restricted*/
function wppb_content_restriction_filter_attachment_url( $url, $attachment_id ) {

    if( is_admin() ) {
        return $url;
    }

    if( wppb_content_restriction_is_post_restricted( $attachment_id ) ) {
        return '';
    }

    return $url;

}
add_filter( 'wp_get_attachment_url', 'wppb_content_restriction_filter_attachment_url', 10, 2 );
add_filter( 'attachment_link', 'wppb_content_restriction_filter_attachment_url', 10, 2 );

/* Formats the error messages to display accordingly to the WYSIWYG editor */
function wppb_content_restriction_message_wpautop( $message = '' ) {

    if( ! empty( $message ) ) {
        $message = wpautop( $message );
    }

    return apply_filters( 'wppb_content_restriction_message_wpautop' ,$message );

}
add_filter( 'wppb_content_restriction_message_logged_in', 'wppb_content_restriction_message_wpautop', 30, 1 );
add_filter( 'wppb_content_restriction_message_logged_out', 'wppb_content_restriction_message_wpautop', 30, 1 );

/* Adds a preview of the restricted post before the default restriction messages */
function wppb_content_restriction_add_post_preview( $message, $content, $post, $user_ID ) {

    $preview        = '';
    $settings       = get_option( 'wppb_content_restriction_settings' );
    $preview_option = ( ! empty( $settings['post_preview'] ) ? $settings['post_preview'] : '' );

    if( empty( $preview_option ) || $preview_option == 'none' ) {
        return $message;
    }

    $post_content = $content;

    // Trim the content
    if( $preview_option == 'trim-content' ) {
        $length = ( ! empty( $settings['post_preview_length'] ) ? (int) $settings['post_preview_length'] : 0 );

        if( $length !== 0 ) {
            // Do shortcodes on the content
            $post_content = do_shortcode( $post_content );

            // Trim the preview
            $preview = wp_trim_words( $post_content, $length, apply_filters( 'wppb_content_restriction_post_preview_more', __( '&hellip;', 'profile-builder' ) ) );
        }
    }

    // More tag
    if( $preview_option == 'more-tag' ) {
        $content_parts = get_extended( $post->post_content );

        if( ! empty( $content_parts['extended'] ) ) {
            $preview = $content_parts['main'];
        }
    }

    // Return the preview
    return wpautop( $preview ) . $message;

}
add_filter( 'wppb_content_restriction_message_logged_in', 'wppb_content_restriction_add_post_preview', 30, 4 );
add_filter( 'wppb_content_restriction_message_logged_out', 'wppb_content_restriction_add_post_preview', 30, 4 );


if( function_exists( 'wc_get_page_id' ) ) {
    /**
     * Restrict the Shop page
     *
     * @param $template The shop page template to return
     * @return string
     */
    function wppb_woo_restrict_shop_page($template){

        // check if we're on the Shop page (set under WooCommerce Settings -> Products -> Display)
        if (is_post_type_archive('product') || is_page(wc_get_page_id('shop'))) {

            // get the ID of the shop page
            $post_id = wc_get_page_id('shop');

            if (($post_id != -1) && wppb_check_content_restriction_on_post_id($post_id)) {

                $shop_page = get_post($post_id);

                setup_postdata($shop_page);

                $template = WPPB_PLUGIN_DIR . 'features/content-restriction/templates/archive-product.php';

                wp_reset_postdata();
            }

        }

        return $template;
    }
    add_filter('template_include', 'wppb_woo_restrict_shop_page', 40);


    /* restrict products content  */
    add_action( 'woocommerce_before_single_product', 'wppb_woo_product_restriction_start' );
    function wppb_woo_product_restriction_start(){
        ob_start();
    }

    add_action( 'woocommerce_after_single_product', 'wppb_woo_product_restriction_end' );
    function wppb_woo_product_restriction_end(){
        $product_content = ob_get_contents();
        ob_end_clean();
        remove_filter('the_content', 'wpautop');
        echo apply_filters( 'the_content', $product_content );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        add_filter('the_content', 'wpautop');
    }
}

/* if the Static Posts Page has a restriction on it hijack the query */
add_action( 'template_redirect', 'wppb_content_restriction_posts_page_handle_query', 1 );
function wppb_content_restriction_posts_page_handle_query(){
    if( is_home() ){
        $posts_page_id = get_option( 'page_for_posts' );
        if( $posts_page_id ) {
            if (wppb_check_content_restriction_on_post_id($posts_page_id)) {
                wppb_content_restriction_force_page($posts_page_id);
            }
        }
    }
}


/* if the Static Posts Page has a restriction on it hijack the template back to the Page Template */
add_filter( 'template_include', 'wppb_content_restriction_posts_page_template', 100 );
function wppb_content_restriction_posts_page_template( $template ){
    if( is_home() ){
        $posts_page_id = get_option( 'page_for_posts' );
        if( $posts_page_id ) {
            if (wppb_check_content_restriction_on_post_id($posts_page_id)) {
                $template = get_page_template();
            }
        }
    }
    return $template;
}

/* Change the query to a single post */
function wppb_content_restriction_force_page( $posts_page_id ){
    if( $posts_page_id ) {
        global $wp_query, $post;
        $post = get_post($posts_page_id);
        $wp_query->posts = array($post);
        $wp_query->post_count = 1;
        $wp_query->is_singular = true;
        $wp_query->is_singule = true;
        $wp_query->is_archive = false;
    }
}

// add callback to function that hides comments if post content is restricted
function wppb_comments_hide_callback_function( $args ) {
    global $post;

    if ( empty( $post->ID ) ) return $args;

    if( wppb_content_restriction_is_post_restricted( $post->ID ) )
        $args[ 'callback' ] = 'wppb_comments_restrict_view';

    return $args;
}

// display restriction message if post content is restricted
function wppb_comments_restrict_view( $comment, $args, $depth ) {
    static $message_shown = false;

    if ( !$message_shown ) {

        if ( is_user_logged_in() )
            printf( '<p>%s</p>', esc_html( apply_filters( 'wppb_comments_restriction_message_user_role', __( 'Comments are restricted for your user role.', 'profile-builder' ) ) ) );
        else
            printf( '<p>%s</p>', esc_html( apply_filters( 'wppb_comments_restriction_message_logged_out', __( 'You must be logged in to view the comments.', 'profile-builder' ) ) ) );

        $message_shown = true;
    }
}

// restrict replying for restricted posts
function wppb_comments_restrict_replying( $open, $post_id ) {
    // Show for administrators
    if( current_user_can( 'manage_options' ) && is_admin() )
        return $open;

    if( wppb_content_restriction_is_post_restricted( $post_id ) )
        return false;

    return $open;
}

$wppb_cr_settings = get_option( 'wppb_content_restriction_settings' );

// add filter to hide comments and replies if post content is restricted
if ( isset( $wppb_cr_settings[ 'contentRestriction' ] ) && $wppb_cr_settings[ 'contentRestriction' ] == 'yes' && apply_filters( 'wppb_enable_comment_restriction', true ) ) {
    add_filter( 'comments_open', 'wppb_comments_restrict_replying', 20, 2 );
    add_filter( 'wp_list_comments_args', 'wppb_comments_hide_callback_function', 999 );
}

if( !function_exists( 'pms_exclude_restricted_comments' ) ){
    add_filter( 'the_comments', 'wppb_exclude_restricted_comments', 10, 2 );
    function wppb_exclude_restricted_comments( $comments, $query ){
        if( !empty( $comments ) && !current_user_can( 'manage_options' ) ){
            $user_id = get_current_user_id();
            foreach ( $comments as $key => $comment ){
                $post = get_post( $comment->comment_post_ID );
                if( ( $post->post_type == 'private-page' && $user_id != (int)$post->post_author ) || ( function_exists( 'wppb_content_restriction_is_post_restricted' ) && wppb_content_restriction_is_post_restricted( $comment->comment_post_ID ) ) || ( function_exists( 'pms_is_post_restricted' ) && pms_is_post_restricted( $comment->comment_post_ID ) ) ){
                    unset( $comments[$key] );
                }
            }
        }
        return $comments;
    }
}