<?php
/**
 * Rest class
 *
 * @since 1.0
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class REST extends Instance {
	/**
	 * Init
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Register REST hooks
	 *
	 * @since  1.0
	 * @access public
	 */
	public function rest_api_init() {
		register_rest_route( 'dologin/v1', '/myip', array(
			'methods' => 'GET',
			'callback' => __CLASS__ . '::geoip',
			'permission_callback'	=> '__return_true',
		) );

		register_rest_route( 'dologin/v1', '/2fa', array(
			'methods' => 'POST',
			'callback' => array( $this, 'twofa' ),
			'permission_callback'	=> '__return_true',
		) );

		register_rest_route( 'dologin/v1', '/sms', array(
			'methods' => 'POST',
			'callback' => array( $this, 'sms' ),
			'permission_callback'	=> '__return_true',
		) );

		register_rest_route( 'dologin/v1', '/test_sms', array(
			'methods' => 'POST',
			'callback' => array( $this, 'test_sms' ),
			'permission_callback'	=> '__return_true',
		) );
	}

	/**
	 * Get GeoIP info
	 */
	public static function geoip() {
		return IP::geo();
	}

	/**
	 * Check 2fa
	 */
	public function twofa() {
		return $this->cls( 'TwoFA' )->check();
	}

	/**
	 * Send SMS
	 */
	public function sms() {
		return $this->cls( 'SMS' )->send();
	}

	/**
	 * Send test SMS
	 */
	public function test_sms() {
		return $this->cls( 'SMS' )->test_send();
	}

	/**
	 * Return content
	 */
	public static function ok( $data ) {
		$data[ '_res' ] = 'ok';
		return $data;
	}

	/**
	 * Return error
	 */
	public static function err( $msg ) {
		defined( 'debug' ) && debug( '❌ [err] ' . $msg );
		return array( '_res' => 'err', '_msg' => $msg );
	}

}
