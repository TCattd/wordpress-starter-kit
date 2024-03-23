<?php
/**
 * Language class
 *
 * @since 1.0
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Lang extends Instance {
	/**
	 * Init hook
	 * @since  1.4.7
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) ) ;
	}

	/**
	 * Plugin loaded hooks
	 * @since 1.4.7
	 */
	public function plugins_loaded() {
		load_plugin_textdomain( 'dologin', false, 'dologin/lang/' ) ;
	}

	public static function msg( $tag, $num = null ) {
		switch ( $tag ) {
			case 'try_after' :
				$msg = sprintf( __( 'Please try after %ds.', 'dologin' ), $num );
				break;

			case 'not_phone_set_curr' :
				$msg = __( 'No Dologin Security phone number set under your profile.', 'dologin' );
				break;

			case 'not_phone_set_user' :
				$msg = __( 'No phone number under this user profile.', 'dologin' );
				break;

			case 'not_2fa_set_user' :
				$msg = __( 'No 2FA set under this user profile.', 'dologin' );
				break;

			case 'empty_u_p' :
				$msg = __( 'Empty username/password.', 'dologin' );
				break;

			case 'not_in_whitelist' :
				$msg = __( 'Your IP is not in the whitelist.', 'dologin' );
				break;

			case 'in_blacklist' :
				$msg = __( 'Your IP is in the blacklist.', 'dologin' );
				break;

			case 'max_retries_hit' :
				$msg = __( 'Too many failed login attempts. Please try later.', 'dologin' );
				break;

			case 'under_protected' :
				$msg = __( 'ON', 'dologin' );
				break;

			case 'max_retries' :
				$msg = sprintf( __( '%s attempt(s) remaining.', 'dologin' ), '<strong>' . $num . '</strong>' );
				break;

			case 'dynamic_code_missing' :
				$msg = __( 'Dynamic code is required.', 'dologin' );
				break;

			case 'dynamic_code_wrong' :
				$msg = __( 'Dynamic code is not correct.', 'dologin' );
				break;

			case 'captcha_missing' :
				$msg = __( 'Please check the reCAPTCHA box.', 'dologin' );
				break;

			// @see https://developers.google.com/recaptcha/docs/verify
			case 'missing-input-response' :
				$msg = __( 'The secret parameter is missing.', 'dologin' );
				break;

			case 'invalid-input-response' :
				$msg = __( 'The secret parameter is invalid or malformed.', 'dologin' );
				break;

			case 'missing-input-secret' :
				$msg = __( 'The response parameter is missing.', 'dologin' );
				break;

			case 'invalid-input-secret' :
				$msg = __( 'The response parameter is invalid or malformed.', 'dologin' );
				break;

			case 'bad-request' :
				$msg = __( 'The request is invalid or malformed.', 'dologin' );
				break;

			case 'timeout-or-duplicate' :
				$msg = __( 'The response is no longer valid: either is too old or has been used previously.', 'dologin' );
				break;

			default:
				$msg = 'unknown msg: ' . $tag;
				break;
		}

		return '<strong>' . __( 'DoLogin Security', 'dologin' ) . '</strong>: ' . $msg;
	}
}
