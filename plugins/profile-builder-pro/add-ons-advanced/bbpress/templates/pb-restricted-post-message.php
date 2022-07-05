<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

$reply_id = bbp_get_reply_id();
$topic_id = bbp_get_topic_id();
$forum_id = bbp_get_forum_id();

if( ! empty( $forum_id ) )
    $post_id = $forum_id;

if( ! empty( $reply_id ) || ! empty( $topic_id ) )
    $post_id = $topic_id;

if( ! empty( $post_id ) ) {
    if( is_user_logged_in() )
        echo wppb_get_restriction_content_message('logged_in', $post_id); //phpcs:ignore
    else
        echo wppb_get_restriction_content_message('logged_out', $post_id); //phpcs:ignore
}