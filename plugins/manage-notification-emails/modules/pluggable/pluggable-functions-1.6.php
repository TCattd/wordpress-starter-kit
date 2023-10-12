<?php
/**
 
 STOP SENDING NOTIFICATION MAILS TO THE USERS 
 version 1.5.4
 added: filters for new user notifications
 removed: pluggable function wp_new_user_notification. Not needed > WP 6.1
 fixed: Email automatic plugin update notification to admin option
 version 1.5.2
 added: Email automatic plugin update notification to admin option
 added: Email automatic theme update notification to admin option
 since 1.5.1
 updated: the core pluggable function wp_new_user_notification
 added: passing through the $deprecated and $notify
 fixed notice of $deprecated
 */

if (!defined('ABSPATH')) die();

$famne_options = FAMNE::get_option( 'famne_options' );

FAMNE::AddModule('pluggable',array(
    'name' => 'Pluggable',
    'version'=>'1.5.4'
));

if (!function_exists('dont_send_password_change_email') ) :
/**
 * Email password change notification to registered user.
 *
*/
//echo "dont_send_password_change_email";
function dont_send_password_change_email( $send=false, $user='', $userdata='')
{
    
    global $famne_options;
    
    if (is_array($user)) $user = (object) $user;

    if (!empty($famne_options['wp_password_change_notification']) ) :

        // send a copy of password change notification to the admin
        // but check to see if it's the admin whose password we're changing, and skip this
        if ( 0 !== strcasecmp( $user->user_email, get_option( 'admin_email' ) ) ) {
            $message = sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "\r\n";
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), $blogname), $message);
        }    
    
    endif;
    
    if (empty($famne_options['send_password_change_email']) ) :   
        return false;
    else :
        return true;
    endif;
}
add_filter('send_password_change_email', 'dont_send_password_change_email',1,3);
endif;


if (empty($famne_options['send_email_change_email']) ) :
/**
 * Email users e-mail change notification to registered user.
 *
*/
    add_filter('send_email_change_email', '__return_false',1,3);
endif;


if (empty($famne_options['wp_new_user_notification_to_admin'])) 
{
/**
 *  Notify admin of new user registration.
 *
*/
    add_filter('wp_send_new_user_notification_to_admin', '__return_false', 1 ,1);
}

if (empty($famne_options['wp_new_user_notification_to_user'])) 
{
/**
 *  Notify user of new user registration.
 *
*/
    add_filter('wp_send_new_user_notification_to_user', '__return_false', 1, 1);
    add_filter('wpmu_welcome_user_notification', '__return_false', 10, 2);
}


if (empty($famne_options['wp_notify_postauthor']) && !function_exists('wp_notify_postauthor') ) :
/**
 * Notify an author (and/or others) of a comment/trackback/pingback on a post.
*/
function wp_notify_postauthor( $comment_id, $deprecated = null ) {}
endif;

if (empty($famne_options['wp_notify_moderator']) && !function_exists('wp_notify_moderator') ) :
/**
 * Notifies the moderator of the blog about a new comment that is awaiting approval.
*/
function wp_notify_moderator($comment_id) {}
endif;




if (empty($famne_options['wp_password_change_notification']) && !function_exists('wp_password_change_notification') ) :
/**
 * Notify the blog admin of a user changing password, normally via email.
 */
function wp_password_change_notification($user) {}


endif;



if ((empty($famne_options['send_password_forgotten_email']) || empty($famne_options['send_password_admin_forgotten_email'])) && !function_exists('dont_send_password_forgotten_email') ) :
/**
 * Email forgotten password notification to registered user.
 *
*/
function dont_send_password_forgotten_email( $send=true, $user_id=0 )
{
    global $famne_options;
    
    $is_administrator = fa_user_is_administrator($user_id);
    
    if ($is_administrator && empty($famne_options['send_password_admin_forgotten_email']))
    {
        // stop sending admin forgot email     
		return false;
    }
    if (!$is_administrator && empty($famne_options['send_password_forgotten_email']))
    {
        // stop sending user forgot email 
		return false;
    }
    // none of the above so give the default status back
    return $send;
}
add_filter('allow_password_reset', 'dont_send_password_forgotten_email',1,3);
endif;




if (empty($famne_options['auto_core_update_send_email']) && !function_exists('fa_dont_sent_auto_core_update_emails') ) :
    /**
     * Send email when WordPress automatic updated.
     *
    */
    
    
    function fa_dont_sent_auto_core_update_emails( $send, $type, $core_update, $result ) {
        if ( ! empty( $type ) && $type == 'success' ) {
            return false;
        }
        return true;
    }
    add_filter( 'auto_core_update_send_email', 'fa_dont_sent_auto_core_update_emails', 10, 4 );
endif;


function fa_user_is_administrator($user_id=0)
{
    $user = new WP_User( intval($user_id) );
    $is_administrator = false;
    if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
        foreach ( $user->roles as $role )
            if ( strtolower($role) == 'administrator') $is_administrator = true;
    }
    return $is_administrator;
}



if (empty($famne_options['auto_plugin_update_send_email']) ) :
    /**
     * Email automatic plugin update notification to admin.
     *
    */
    //echo "auto_plugin_update_send_email off";
    function fa_auto_plugin_update_send_email($notifications_enabled,$update_results_plugins)
    {
        $notifications_enabled = false;
        foreach ( $update_results_plugins as $update_result ) {
            // do we have a failed update?
            if ( true !== $update_result->result ) $notifications_enabled = true;
        }
        return $notifications_enabled;
    }

    add_filter( 'auto_plugin_update_send_email', 'fa_auto_plugin_update_send_email',10,2 );
endif;


if (empty($famne_options['auto_theme_update_send_email']) ) :
    /**
     * Email automatic theme update notification to admin.
     *
    */
    //echo "auto_theme_update_send_email off";
    function fa_auto_theme_update_send_email($notifications_enabled,$update_results_theme)
    {
        $notifications_enabled = false;

        foreach ( $update_results_theme as $update_result ) {
            // do we have a failed update?
            if ( true !== $update_result->result ) $notifications_enabled = true;
        }
        return $notifications_enabled;
    }

    add_filter( 'auto_theme_update_send_email', 'fa_auto_theme_update_send_email',10,2 );
endif;