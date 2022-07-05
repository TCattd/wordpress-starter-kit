<?php

add_shortcode( 'wppb-resend-activation', 'wppb_toolbox_resend_activation_url_handler' );
function wppb_toolbox_resend_activation_url_handler() {
    $button_name = __('Resend activation email', 'profile-builder');
    $output = '<form enctype="multipart/form-data" method="post" id="wppbc-resend_activation" class="wppb-user-forms" action="">';
    $output .= '
        <li>
        <label for="username_email" style="padding-right: 30px;">'.__( 'Email', 'profile-builder' ).'</label>
        <input  class="text-input" name="email" type="text" id="username_email" value="" />
        <div style="padding-top: 20px;">
            <input name="resend_activation" type="submit" id="wppbc-resend-activation-button" class="submit button" value="'. $button_name . '" />
            <input name="action" type="hidden" id="action" value="wppbc_resend_activation" />
    		<input type="hidden" name="wppb_nonce" value="'. esc_attr( wp_create_nonce( 'wppbc_resend_activation' ) ) . '" />
        </div>
        </li>
    </form>';

    return $output;
}

 add_action( 'init', 'wppb_toolbox_resend_activation_url', 999 );
 function wppb_toolbox_resend_activation_url() {
 	if( isset($_REQUEST['action']) && $_REQUEST['action'] === 'wppbc_resend_activation' && isset($_REQUEST['email'] ) && isset( $_REQUEST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['wppb_nonce'] ), 'wppbc_resend_activation' )) {
 		global $wpdb;
 		$sql_result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", sanitize_email( $_REQUEST['email'] ) ), ARRAY_A );

 		if ( $sql_result ){
 			wppb_signup_user_notification( trim( $sql_result['user_login'] ), trim( $sql_result['user_email'] ), $sql_result['activation_key'], $sql_result['meta'] );
 			echo '<script> alert("Activation email sent!");</script>';
 		}
 		else {
 			echo '<script> alert("No sign-up was made with that email!");</script>';
 		}
 	}
 }
