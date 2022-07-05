<?php
/**
 * Class that adds a misc notice
 *
 * @since v.2.0
 *
 * @return void
 */
class WPPB_Add_General_Notices{
    public $notificationId = '';
    public $notificationMessage = '';
    public $notificationClass = '';
    public $startDate = '';
    public $endDate = '';

    function __construct( $notificationId, $notificationMessage, $notificationClass = 'updated' , $startDate = '', $endDate = '' ){
        $this->notificationId = $notificationId;
        $this->notificationMessage = $notificationMessage;
        $this->notificationClass = $notificationClass;

        if( !empty( $startDate ) && time() < strtotime( $startDate ) )
            return;

        if( !empty( $endDate ) && time() > strtotime( $endDate ) )
            return;

        add_action( 'admin_notices', array( $this, 'add_admin_notice' ) );
        add_action( 'admin_init', array( $this, 'dismiss_notification' ) );
    }


    // Display a notice that can be dismissed in case the serial number is inactive
    function add_admin_notice() {
        global $current_user ;
        global $pagenow;

        $user_id = $current_user->ID;
        do_action( $this->notificationId.'_before_notification_displayed', $current_user, $pagenow );

        if ( current_user_can( 'manage_options' ) ){
            // Check that the user hasn't already clicked to ignore the message
            if ( ! get_user_meta($user_id, $this->notificationId.'_dismiss_notification' ) ) {
                echo wp_kses_post( apply_filters($this->notificationId.'_notification_message','<div class="'. $this->notificationClass .'" >'.$this->notificationMessage.'</div>', $this->notificationMessage) );
            }
            do_action( $this->notificationId.'_notification_displayed', $current_user, $pagenow );
        }
        do_action( $this->notificationId.'_after_notification_displayed', $current_user, $pagenow );
    }

    function dismiss_notification() {
        global $current_user;

        $user_id = $current_user->ID;

        do_action( $this->notificationId.'_before_notification_dismissed', $current_user );

        // If user clicks to ignore the notice, add that to their user meta
        if ( isset( $_GET[$this->notificationId.'_dismiss_notification']) && '0' == $_GET[$this->notificationId.'_dismiss_notification'] )
            add_user_meta( $user_id, $this->notificationId.'_dismiss_notification', 'true', true );

        do_action( $this->notificationId.'_after_notification_dismissed', $current_user );
    }
}

Class WPPB_Plugin_Notifications {

    public $notifications = array();
    private static $_instance = null;
    private $prefix = 'wppb';
    private $menu_slug = 'profile-builder';
    public $pluginPages = array( 'profile-builder-', 'manage-fields', 'wppb-', 'admin-email-customizer', 'user-email-customizer' );

    protected function __construct() {
        add_action( 'admin_init', array( $this, 'dismiss_admin_notifications' ), 200 );
        add_action( 'admin_init', array( $this, 'add_admin_menu_notification_counts' ), 1000 );
        add_action( 'admin_init', array( $this, 'remove_other_plugin_notices' ), 1001 );
    }


    function dismiss_admin_notifications() {
        if( ! empty( $_GET[$this->prefix.'_dismiss_admin_notification'] ) ) {
            $notifications = self::get_instance();
            $notifications->dismiss_notification( sanitize_text_field( $_GET[$this->prefix.'_dismiss_admin_notification'] ) );
        }

    }

    function add_admin_menu_notification_counts() {

        global $menu, $submenu;

        $notifications = WPPB_Plugin_Notifications::get_instance();

        if( ! empty( $menu ) ) {
            foreach( $menu as $menu_position => $menu_data ) {
                if( ! empty( $menu_data[2] ) && $menu_data[2] == $this->menu_slug ) {
                    $menu_count = $notifications->get_count_in_menu();
                    if( ! empty( $menu_count ) )
                        $menu[$menu_position][0] .= '<span class="update-plugins '.$this->prefix.'-update-plugins"><span class="plugin-count">' . $menu_count . '</span></span>';
                }
            }
        }

        if( ! empty( $submenu[$this->menu_slug] ) ) {
            foreach( $submenu[$this->menu_slug] as $menu_position => $menu_data ) {
                $menu_count = $notifications->get_count_in_submenu( $menu_data[2] );

                if( ! empty( $menu_count ) )
                    $submenu[$this->menu_slug][$menu_position][0] .= '<span class="update-plugins '.$this->prefix.'-update-plugins"><span class="plugin-count">' . $menu_count . '</span></span>';
            }
        }
    }

    /* handle other plugin notifications on our plugin pages */
    function remove_other_plugin_notices(){

        //remove all notifications from start page
        if( isset( $_GET['page'] ) && ( $_GET['page'] == 'profile-builder-basic-info' || ( $_GET['page'] == 'profile-builder-add-ons' && !isset( $_GET['cl_add_ons_listing_success'] ) ) ) ) {//on addons page we use notices to display success mesage so we can't remove it in that case
            remove_all_actions('admin_notices');
        }

        /* remove all other plugin notifications except our own from the rest of the PB pages */
        if( $this->is_plugin_page() ) {
            global $wp_filter;
            if (!empty($wp_filter['admin_notices'])) {
                if (!empty($wp_filter['admin_notices']->callbacks)) {
                    foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks_level) {
                        if (!empty($callbacks_level)) {
                            foreach ($callbacks_level as $key => $callback) {
                                if( is_array( $callback['function'] ) ){
                                    if( is_object($callback['function'][0])) {//object here
                                        if (strpos(get_class($callback['function'][0]), 'PMS_') !== 0 && strpos(get_class($callback['function'][0]), 'WPPB_') !== 0 && strpos(get_class($callback['function'][0]), 'TRP_') !== 0 && strpos(get_class($callback['function'][0]), 'WCK_') !== 0) {
                                            unset($wp_filter['admin_notices']->callbacks[$priority][$key]);//unset everything that doesn't come from our plugins
                                        }
                                    }
                                } else if( is_string( $callback['function'] ) ){//it should be a function name
                                    if (strpos($callback['function'], 'pms_') !== 0 && strpos($callback['function'], 'wppb_') !== 0 && strpos($callback['function'], 'trp_') !== 0 && strpos($callback['function'], 'wck_') !== 0) {
                                        unset($wp_filter['admin_notices']->callbacks[$priority][$key]);//unset everything that doesn't come from our plugins
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    /**
     *
     *
     */
    public static function get_instance() {
        if( is_null( self::$_instance ) )
            self::$_instance = new WPPB_Plugin_Notifications();

        return self::$_instance;
    }


    /**
     *
     *
     */
    public function add_notification( $notification_id = '', $notification_message = '', $notification_class = 'update-nag', $count_in_menu = true, $count_in_submenu = array() ) {

        if( empty( $notification_id ) )
            return;

        if( empty( $notification_message ) )
            return;

        global $current_user;

        if( get_user_meta( $current_user->ID, $notification_id . '_dismiss_notification' ) )
            return;

        $this->notifications[$notification_id] = array(
            'id' 	  		   => $notification_id,
            'message' 		   => $notification_message,
            'class'   		   => $notification_class,
            'count_in_menu'    => $count_in_menu,
            'count_in_submenu' => $count_in_submenu
        );


        if( $this->is_plugin_page() ) {
            new WPPB_Add_General_Notices( $notification_id, $notification_message, $notification_class );
        }

    }


    /**
     *
     *
     */
    public function get_notifications() {
        return $this->notifications;
    }


    /**
     *
     *
     */
    public function get_notification( $notification_id = '' ) {

        if( empty( $notification_id ) )
            return null;

        $notifications = $this->get_notifications();

        if( ! empty( $notifications[$notification_id] ) )
            return $notifications[$notification_id];
        else
            return null;

    }


    /**
     *
     *
     */
    public function dismiss_notification( $notification_id = '' ) {
        global $current_user;
        add_user_meta( $current_user->ID, $notification_id . '_dismiss_notification', 'true', true );
    }


    /**
     *
     *
     */
    public function get_count_in_menu() {
        $count = 0;

        foreach( $this->notifications as $notification ) {
            if( ! empty( $notification['count_in_menu'] ) )
                $count++;
        }

        return $count;
    }


    /**
     *
     *
     */
    public function get_count_in_submenu( $submenu = '' ) {

        if( empty( $submenu ) )
            return 0;

        $count = 0;

        foreach( $this->notifications as $notification ) {
            if( empty( $notification['count_in_submenu'] ) )
                continue;

            if( ! is_array( $notification['count_in_submenu'] ) )
                continue;

            if( ! in_array( $submenu, $notification['count_in_submenu'] ) )
                continue;

            $count++;
        }

        return $count;

    }


    /**
     *
     *
     */
    public function is_plugin_page() {
        if( !empty( $this->pluginPages ) ){
            foreach ( $this->pluginPages as $pluginPage ){
                if( ! empty( $_GET['page'] ) && false !== strpos( sanitize_text_field( $_GET['page'] ), $pluginPage ) )
                    return true;

                if( ! empty( $_GET['post_type'] ) && false !== strpos( sanitize_text_field( $_GET['post_type'] ), $pluginPage ) )
                    return true;

                if( ! empty( $_GET['post'] ) && false !== strpos( get_post_type( (int)$_GET['post'] ), $pluginPage ) )
                    return true;
            }
        }

        return false;
    }

}