<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Function that creates the "Register your version" submenu page
 *
 * @since v.2.0
 *
 * @return void
 */

if( !is_multisite() ){
    function wppb_register_your_version_submenu_page(){
        if ( PROFILE_BUILDER != 'Profile Builder Free' )
            add_submenu_page('profile-builder', __('Register Your Version', 'profile-builder'), __('Register Version', 'profile-builder'), 'manage_options', 'profile-builder-register', 'wppb_register_your_version_content');
    }
    add_action('admin_menu', 'wppb_register_your_version_submenu_page', 29);
} else {
    function wppb_multisite_register_your_version_page() {
        if ( PROFILE_BUILDER != 'Profile Builder Free' )
            add_menu_page(__('Profile Builder Register', 'profile-builder'), __('Profile Builder Register', 'profile-builder'), 'manage_options', 'profile-builder-register', 'wppb_register_your_version_content', WPPB_PLUGIN_URL . 'assets/images/pb-menu-icon.png');
    }
    add_action('network_admin_menu', 'wppb_multisite_register_your_version_page', 29);
}


/**
 * Function that adds content to the "Register your Version" submenu page
 *
 * @since v.2.0
 *
 * @return string
 */
function wppb_register_your_version_content() {
    ?>
    <div class="wrap wppb-wrap">
        <?php wppb_serial_form(); ?>
    </div>
<?php
}

/**
 * Function that creates the "Register your version" form 
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_serial_form(){
    $status  = wppb_get_serial_number_status();
    $license = wppb_get_serial_number();
    ?>
    <div id="wppb-register-version-page" class="wrap">
        <h2>
            <?php esc_html_e( "Register your version of Profile Builder", 'profile-builder' ); ?>
            <a href="https://www.cozmoslabs.com/docs/profile-builder-2/basic-information-installation/?utm_source=wpbackend&utm_medium=pb-documentation&utm_campaign=PBDocs#Register_your_version" target="_blank" data-code="f223" class="wppb-docs-link dashicons dashicons-editor-help"></a>
        </h2>

        <p><?php esc_html_e( 'Now that you acquired a copy of Profile Builder Pro, you should take the time and register it with the serial number you received.', 'profile-builder' ) ?></p>

        <p><?php esc_html_e( 'If you register this version of Profile Builder, you\'ll receive information regarding upgrades, patches, and technical support.', 'profile-builder' ) ?></p>

        <div class="wppb-serial-wrap">
            <form method="post" action="<?php echo !is_multisite() ? 'options.php' : 'edit.php'; ?>">
                <?php settings_fields( 'wppb_license_key' ); ?>

                <label for="wppb_license_key"><?php esc_html_e( 'License key', 'profile-builder' ); ?></label>

                <div class="wppb-serial-wrap__holder">
                    <input id="wppb_license_key" name="wppb_license_key" type="password" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
                    <?php wp_nonce_field( 'wppb_license_nonce', 'wppb_license_nonce' ); ?>

                    <?php if( $status !== false && $status == 'valid' ) {
                        $button_name =  'wppb_edd_license_deactivate';
                        $button_value = __('Deactivate License', 'profile-builder' );

                        if( empty( $details['invalid'] ) )
                            echo '<span title="'. esc_html__( 'Active on this site', 'profile-builder' ) .'" class="wppb-active-license dashicons dashicons-yes"></span>';
                        else
                            echo '<span title="'. esc_html__( 'Your license is invalid', 'profile-builder' ) .'" class="wppb-invalid-license dashicons dashicons-warning"></span>';

                    } else {
                        $button_name =  'wppb_edd_license_activate';
                        $button_value = __('Activate License', 'profile-builder');
                    }
                    ?>
                    <input type="submit" class="button-secondary" name="<?php echo esc_attr( $button_name ); ?>" value="<?php echo esc_attr( $button_value ); ?>"/>
                </div>
            </form>
        </div>
    </div>

    <?php
}

/**
 * Retrieve saved license key
 */
function wppb_get_serial_number(){

    $license = get_option( 'wppb_license_key', false );

    // try to grab the license from the old options if it's not available
    if( empty( $license ) ){
        $versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	    if( in_array( PROFILE_BUILDER, $versions ) )
            $version = 'pro';
        elseif ( PROFILE_BUILDER == 'Profile Builder Hobbyist' || PROFILE_BUILDER == 'Profile Builder Basic' )
            $version = 'hobbyist';

        $old_license = get_option( 'wppb_profile_builder_'.$version.'_serial' );

        if( !empty( $old_license ) ){
            update_option( 'wppb_license_key', $old_license );
            $license = $old_license;
        }

    }

    return $license;

}

/**
 * Retrieve license key status
 */
function wppb_get_serial_number_status(){
    return get_option( 'wppb_license_status' );
}

/**
 * Class that adds a notice when either the serial number wasn't found, or it has expired
 *
 * @since v.2.0
 *
 * @return void
 */
class WPPB_add_notices{
	public $pluginPrefix = '';
	public $pluginName = '';
	public $notificaitonMessage = '';
	public $pluginSerialStatus = '';

	function __construct( $pluginPrefix, $pluginName, $notificaitonMessage, $pluginSerialStatus ){
		$this->pluginPrefix = $pluginPrefix;
		$this->pluginName = $pluginName;
		$this->notificaitonMessage = $notificaitonMessage;
		$this->pluginSerialStatus = $pluginSerialStatus;

		add_action( 'admin_notices', array( $this, 'add_admin_notice' ) );
		add_action( 'admin_init', array( $this, 'dismiss_notification' ) );
	}


	// Display a notice that can be dismissed in case the serial number is inactive
	function add_admin_notice() {
		global $current_user ;
		global $pagenow;

		$user_id = $current_user->ID;

		do_action( $this->pluginPrefix.'_before_notification_displayed', $current_user, $pagenow );

		if ( current_user_can( 'manage_options' ) ){

				$plugin_serial_status = get_option( $this->pluginSerialStatus );
				if ( $plugin_serial_status != 'found' ){

				    //we want to show the expiration notice on our plugin pages even if the user dismissed it on the rest of the site
                    $force_show = false;
                    if ( $plugin_serial_status == 'expired' ) {
                        $notification_instance = WPPB_Plugin_Notifications::get_instance();
                        if ($notification_instance->is_plugin_page()){
                            $force_show = true;
                        }
                    }

					// Check that the user hasn't already clicked to ignore the message
					if ( ! get_user_meta($user_id, $this->pluginPrefix.'_dismiss_notification' ) || $force_show ) {
						echo wp_kses_post( $finalMessage = apply_filters($this->pluginPrefix.'_notification_message','<div class="error wppb-serial-notification" >'.$this->notificaitonMessage.'</div>', $this->notificaitonMessage) );
					}
				}

				do_action( $this->pluginPrefix.'_notification_displayed', $current_user, $pagenow, $plugin_serial_status );

		}

		do_action( $this->pluginPrefix.'_after_notification_displayed', $current_user, $pagenow );

	}

	function dismiss_notification() {
		global $current_user;

		$user_id = $current_user->ID;

		do_action( $this->pluginPrefix.'_before_notification_dismissed', $current_user );

		// If user clicks to ignore the notice, add that to their user meta
		if ( isset( $_GET[$this->pluginPrefix.'_dismiss_notification']) && '0' == $_GET[$this->pluginPrefix.'_dismiss_notification'] )
			add_user_meta( $user_id, $this->pluginPrefix.'_dismiss_notification', 'true', true );

		do_action( $this->pluginPrefix.'_after_notification_dismissed', $current_user );
	}
}

if( defined( 'WPPB_PAID_PLUGIN_DIR' ) ){

    // Switch to the main site
    if( is_multisite() && function_exists( 'switch_to_blog' )
        && function_exists( 'get_main_site_id' ))
        switch_to_blog( get_main_site_id() );

    $wppb_serial_number = wppb_get_serial_number();
    $wppb_serial_status = wppb_get_serial_number_status();
    $license_details    = get_option( 'wppb_license_details', false );

    if( is_multisite() && function_exists( 'restore_current_blog' ) )
        restore_current_blog();

    if( empty( $wppb_serial_number ) || $wppb_serial_status == 'missing' ) {

        if( !is_multisite() )
            $register_url = 'admin.php?page=profile-builder-register';
        else
            $register_url = network_admin_url( 'admin.php?page=profile-builder-register' );

        new WPPB_add_notices( 'wppb', 'profile_builder_pro', sprintf( '<p>' . __( 'Your <strong>Profile Builder</strong> serial number is invalid or missing. <br/>Please %1$sregister your copy%2$s to receive access to automatic updates and support. Need a license key? %3$sPurchase one now%4$s', 'profile-builder') . '</p>', "<a href='". esc_url( $register_url ) ."'>", "</a>", "<a href='https://www.cozmoslabs.com/wordpress-profile-builder/?utm_source=PB&utm_medium=dashboard&utm_campaign=PB-SN-Purchase' target='_blank' class='button-primary'>", "</a>" ), 'wppb_license_status' );

    }
    elseif ( $wppb_serial_status == 'expired' ){
        /* on our plugin pages do not add the dismiss button for the expired notification*/
        $notification_instance = WPPB_Plugin_Notifications::get_instance();
        if( $notification_instance->is_plugin_page() )
            $message = '<p>' . __( 'Your <strong>Profile Builder</strong> license has expired. <br/>Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support. %3$sRenew now %4$s', 'profile-builder') . '</p>';
        else
            $message = '<p>' . __( 'Your <strong>Profile Builder</strong> license has expired. <br/>Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support. %3$sRenew now %4$s %5$sDismiss%6$s', 'profile-builder') . '</p>';

        new WPPB_add_notices( 'wppb_expired', 'profile_builder_pro', sprintf( $message, "<a href='https://www.cozmoslabs.com/account/?utm_source=PB&utm_medium=dashboard&utm_campaign=PB-Renewal' target='_blank'>", "</a>", "<a href='https://www.cozmoslabs.com/account/?utm_source=PB&utm_medium=dashboard&utm_campaign=PB-Renewal' target='_blank' class='button-primary'>", "</a>", "<a href='". esc_url( add_query_arg( 'wppb_expired_dismiss_notification', '0' ) ) ."' class='wppb-dismiss-notification'>", "</a>" ), 'wppb_license_status' );
    }
    
    
    // Maybe add about to expire notice
    if( !empty( $license_details ) && !empty( $license_details->expires ) ){

        if( strtotime( $license_details->expires ) < strtotime( '+14 days' ) ){
            new WPPB_add_notices( 'wppb_about_to_expire', 'profile_builder_pro', sprintf( '<p>' . __( 'Your <strong>Profile Builder</strong> license is about to expire on %5$s. <br/>Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support. %3$sRenew now %4$s %6$sDismiss%7$s', 'profile-builder') . '</p>', "<a href='https://www.cozmoslabs.com/account/?utm_source=PB&utm_medium=dashboard&utm_campaign=PB-Renewal' target='_blank'>", "</a>", "<a href='https://www.cozmoslabs.com/account/?utm_source=PB&utm_medium=dashboard&utm_campaign=PB-Renewal' target='_blank' class='button-primary'>", "</a>", date_i18n( get_option( 'date_format' ), strtotime( $license_details->expires ) ), "<a href='". esc_url( add_query_arg( 'wppb_about_to_expire_dismiss_notification', '0' ) )."' class='wppb-dismiss-notification'>", "</a>" ), 'wppb_license_status' );
        }

    }

    if( isset( $license_details->license ) && $license_details->license == 'invalid' ){

        if( isset( $license_details->error ) && $license_details->error == 'no_activations_left' ){

            $activations_limit_message = '<p>' . sprintf( __( 'Your <strong>Profile Builder Basic</strong> license has reached its activation limit.<br> Upgrade now to <strong>Pro</strong> for unlimited activations and extra features like multiple registration and edit profile forms, userlisting, custom redirects and more. <a class="button-primary" href="%s">Upgrade now</a>', 'profile-builder' ), esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=WPPB&utm_content=add-on-page-license-activation-limit' ) ) . '</p>';

            $notification_instance = WPPB_Plugin_Notifications::get_instance();
            if( !$notification_instance->is_plugin_page() ) {//add the dismiss button only on other pages in admin
                $activations_limit_message .= sprintf(__(' %1$sDismiss%2$s', 'profile-builder'), "<a class='dismiss-right' href='" . esc_url(add_query_arg('wppb_basic_activations_limit_dismiss_notification', '0')) . "'>", "</a>");
                $force_show = false;
            } else {
                $force_show = true;//sets the forceShow parameter of WPPB_add_notices to true so we don't take into consideration the dismiss user meta
            }

            new WPPB_add_notices( 'wppb_basic_activations_limit',
                'profile_builder_basic',
                $activations_limit_message,
                'error',
                '',
                '',
                $force_show );
        }

    }

    

}

