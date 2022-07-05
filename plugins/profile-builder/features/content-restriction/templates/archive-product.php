<?php
/**
 * Template used to overwrite the main shop page which is a post type archive
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

<?php
/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );
?>

<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

    <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

<?php endif; ?>

<?php
/**
 * Display restriction message
 *
 */

$post_id = wc_get_page_id( 'shop' );

if ($post_id != -1) {
    $shop_post = get_post( $post_id );
    if( is_user_logged_in() ) {
        $message = wppb_content_restriction_process_content_message( 'logged_in', get_current_user_id(), $post_id );
        echo do_shortcode( apply_filters( 'wppb_content_restriction_message_logged_in', $message, $shop_post->post_content, $shop_post, get_current_user_id() ) );
    }
    else{
        $message = wppb_content_restriction_process_content_message( 'logged_out', get_current_user_id(), $post_id );
        echo do_shortcode( apply_filters( 'wppb_content_restriction_message_logged_out', $message, $shop_post->post_content, $shop_post, get_current_user_id() ) );
    }
}
?>

<?php
/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );
?>

<?php
/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );
?>

<?php get_footer( 'shop' ); ?>
