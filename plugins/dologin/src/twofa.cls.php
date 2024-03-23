<?php
/**
 * 2FA class
 *
 * @since 3.5
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class TwoFA extends Instance {
	private $_dry_run = false;

	/**
	 * Maybe save user 2fa status
	 *
	 * @since 3.5
	 */
	public function maybe_save_2fa() {
		if ( empty( $_POST['dologin-2fa-code'] ) ) return;
		if ( empty( $_POST['dologin-2fa-secret'] ) ) return;
		check_admin_referer( 'dologin-set2fa' );

		$lib = new lib\Two_FA_Lib;
		if ( !$lib->verifyCode($_POST['dologin-2fa-secret'], $_POST['dologin-2fa-code'], 1) ) {
			GUI::error(__('Code verification failed!', 'dologin'));
			return;
		}

		// Set user's 2FA secret
		if ($this->current_status()) {
			GUI::error(__('You have set your 2FA secret before!', 'dologin'));
			return;
		}

		$uid = get_current_user_id();
		update_user_meta($uid, '2fa', $_POST['dologin-2fa-secret']);

		GUI::succeed(__('Congratulations! Your 2FA is successfully enabled!', 'dologin'));

		wp_redirect( $_SERVER[ 'HTTP_REFERER' ] );
		exit;
	}

	/**
	 * Show GUI notice if missing 2fa in user profile
	 *
	 * @since 3.5
	 */
	public function gui_notice() {
		$current_user_2fa = $this->current_status();
		if ( ! $current_user_2fa && Conf::val( '2fa' ) ) {
			$installer = new Installer();
			if (!$installer->dash_notifier_is_plugin_active('doqrcode')) {
				$install_link = Util::build_url( Router::ACTION_INSTALLER, Installer::TYPE_INSTALL_3RD, false, null, array( 'plugin' => 'doqrcode' ) );
				$desc = __('You need to install the following plugin to enable 2FA', 'dologin' ) . ': <a href="' . $install_link . '">WordPress QR Code generator (click to install)</a>';
				GUI::error( '<h2>' . DOLOGIN_LOGO . __('Dologin Notice') . '</h2>' . $desc);
				return;
			}

			$lib = new lib\Two_FA_Lib;
			$secret = $lib->createSecret();
			$qrcode = do_shortcode( "[qrcode size='8' margin='3']" . $lib->getQRCodeGoogleUrl(get_bloginfo('name'), $secret) . '[/qrcode]' );
			$form = '<form action="' . menu_page_url( 'dologin', false ) . '" method="post"><input type="hidden" name="dologin-2fa-secret" value="' . $secret . '" />'
					. wp_nonce_field( 'dologin-set2fa', '_wpnonce', true, false )
					. __('Code', 'dologin' )
					. ': <input type="text" name="dologin-2fa-code" />'
					. get_submit_button( __('Enable 2FA', 'dologin') )
					. '</form>';

			$desc = __('Please scan this barcode w/ your phone 2FA app (e.g. Google Authenticator) and type the code in 2FA app below.', 'dologin');
			if ( Conf::val( '2fa_force' ) ) $desc .= '<br/><font color="red">' . __( 'You need to setup your 2FA before enabling this setting to avoid yourself being blocked from next time login.', 'dologin' ) . '</font>';

			GUI::error( '<h2>' . DOLOGIN_LOGO . __('Dologin Notice') . '</h2>' . $desc . '<br />' . $qrcode . $form );
		}
	}

	/**
	 * Return current usre's 2fa status
	 *
	 * @since 3.5
	 */
	public function current_status() {
		$uid = get_current_user_id();
		$code = get_user_meta( $uid, '2fa', true );
		return $code;
	}

	/**
	 * Check if is dry run or not
	 *
	 * @since  3.5
	 */
	public static function is_dry_run() {
		return self::cls()->_dry_run;
	}

	/**
	 * Verify code after u+p authenticated
	 *
	 * @since  3.5
	 */
	public function authenticate( $user, $username, $password ) {
		global $wpdb;

		defined( 'debug' ) && debug( 'auth' );

		if ( $this->_dry_run ) {
			defined( 'debug' ) && debug( 'bypassed due to dryrun' );
			return $user;
		}

		if ( empty( $username ) || empty( $password ) ) {
			defined( 'debug' ) && debug( 'bypassed due to lack of u/p' );
			return $user;
		}

		if ( is_wp_error( $user ) ) {
			defined( 'debug' ) && debug( 'bypassed due to is_wp_error already' );
			return $user;
		}

		// If 2fa is optional and the user doesn't have phone set, bypass
		$code = get_user_meta( $user->ID, '2fa', true );
		if ( ! $code ) {
			defined( 'debug' ) && debug( 'no 2fa set' );
			if ( ! Conf::val( '2fa_force' ) ) {
				defined( 'debug' ) && debug( 'bypassed due to no force_2fa check' );
				return $user;
			}
		}

		$error = new \WP_Error();

		// Validate dynamic code
		if ( empty( $_POST[ 'dologin-two_factor_code' ] ) ) {
			$error->add( 'dynamic_code_missing', Lang::msg( 'dynamic_code_missing' ) );
			define( 'DOLOGIN_ERR', true );
			defined( 'debug' ) && debug( '❌ 2fa missing' );
			return $error;
		}

		$lib = new lib\Two_FA_Lib;
		$res = $lib->verifyCode( $code, $_POST[ 'dologin-two_factor_code' ], 1 );
		if ( !$res ) {
			$error->add( 'dynamic_code_wrong', Lang::msg( 'dynamic_code_wrong' ) );
			define( 'DOLOGIN_ERR', true );
			defined( 'debug' ) && debug( '❌ 2fa wrong' );
			return $error;
		}

		defined( 'debug' ) && debug( '✅ auth successfully' );

		return $user;
	}

	/**
	 * Check if has enabled 2fa or not
	 *
	 * @since  3.5
	 */
	public function check() {
		global $wpdb;

		if ( ! Conf::val( '2fa' ) ) {
			return REST::ok( array( 'bypassed' => 1 ) );
		}

		$field_u = 'log';
		$field_p = 'pwd';
		if ( isset( $_POST[ 'woocommerce-login-nonce' ] ) ) {
			$field_u = 'username';
			$field_p = 'password';
		}

		if ( empty( $_POST[ $field_u ] ) || empty( $_POST[ $field_p ] ) ) {
			return REST::err( Lang::msg( 'empty_u_p' ) );
		}

		// Verify u & p first
		$this->_dry_run = true;
		$user = wp_authenticate( $_POST[ $field_u ], $_POST[ $field_p ] );
		$this->_dry_run = false;
		if ( is_wp_error( $user ) ) {
			return REST::err( $user->get_error_message() );
		}

		// Search if the user has enabled 2fa or not
		$twofa = get_user_meta( $user->ID, '2fa', true );

		if ( ! $twofa ) {
			if ( ! Conf::val( '2fa_force' ) ) {
				defined( 'debug' ) && debug( 'bypassed due to no 2fa set' );
				return REST::ok( array( 'bypassed' => 1 ) );
			}
			return REST::err( Lang::msg( 'no_2fa_set_user' ) );
		}

		return REST::ok( array( 'info' => __('Please provide the code from your 2FA app', 'dologin') ) );
	}
}