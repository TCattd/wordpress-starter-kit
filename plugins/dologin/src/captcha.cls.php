<?php
/**
 * Captcha class
 *
 * @since 1.6
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Captcha extends Instance {
	/**
	 * Display recaptcha
	 *
	 * @since  1.6
	 */
	public function show() {
		wp_register_script( 'dologin_google_api', 'https://www.google.com/recaptcha/api.js', array(), null );
		wp_enqueue_script( 'dologin_google_api' );

		echo '<div class="g-recaptcha dologin-captcha" data-sitekey="' . Conf::val( 'gg_pub_key' ) . '"></div>';
	}

	/**
	 * Validate recaptcha
	 *
	 * @since  1.6
	 */
	public function authenticate() {
		// Validate
		if ( empty( $_POST[ 'g-recaptcha-response' ] ) ) {
			throw new \Exception( 'captcha_missing' );
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' 	=> Conf::val( 'gg_priv_key' ),
			'response' 	=> $_POST[ 'g-recaptcha-response' ],
			'remoteip' => IP::me(),
		);

		$res = wp_remote_post( $url, array( 'body' => $data, 'timeout' => 15, 'sslverify' => false ) );

		if ( is_wp_error( $res ) ) {
			$error_message = $res->get_error_message();
			throw new \Exception( $error_message );
		}

		$res = json_decode( $res[ 'body' ], true );

		if ( empty( $res[ 'success' ] ) ) {
			$err_code = ! empty( $res[ 'error-codes' ][ 0 ] ) ? $res[ 'error-codes' ][ 0 ] : 'error' ;

			throw new \Exception( $err_code );
		}

		defined( 'debug' ) && debug( '✅ passed' );
	}
}