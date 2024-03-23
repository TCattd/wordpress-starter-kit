<?php
/**
 * Router class
 *
 * @since 1.4
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Router extends Instance {
	const NONCE = 'dologin_nonce';
	const ACTION = 'dologin_action';
	const TYPE = 'dologin_type';
	const I = 'dologin_i';

	const ACTION_PSWD = 'pswdless';
	const ACTION_AUTH = 'auth';
	const ACTION_INSTALLER = 'installer';

	// List all handlers here
	private static $_HANDLERS = array(
		self::ACTION_PSWD,
		self::ACTION_AUTH,
		self::ACTION_INSTALLER,
	);

	private static $_action;

	/**
	 * Init
	 */
	public function init() {
		add_action( 'init', array( $this, 'handler' ) );
	}

	/**
	 * Auto handler in `after_user_init`
	 *
	 * @since  2.7
	 */
	public function handler() {
		$cls = $this->get_action();
		if ( ! $cls ) {
			return;
		}

		if ( ! in_array( $cls, self::$_HANDLERS ) ) {
			return;
		}

		$this->cls( $cls )->handler();
		self::redirect();
	}

	/**
	 * Redirect page and drop self params
	 *
	 * @since  1.4
	 */
	public static function redirect( $url = false ) {
		global $pagenow;
		$qs = '';
		if ( ! $url ) {
			if ( ! empty( $_GET ) ) {
				if ( isset( $_GET[ self::ACTION ] ) ) {
					unset( $_GET[ self::ACTION ] );
				}
				if ( isset( $_GET[ self::NONCE ] ) ) {
					unset( $_GET[ self::NONCE ] );
				}
				if ( isset( $_GET[ self::TYPE ] ) ) {
					unset( $_GET[ self::TYPE ] );
				}
				if ( isset( $_GET[ self::I ] ) ) {
					unset( $_GET[ self::I ] );
				}
				if ( ! empty( $_GET ) ) {
					$qs = '?' . http_build_query( $_GET );
				}
			}
			if ( is_network_admin() ) {
				$url = network_admin_url( $pagenow . $qs );
			}
			else {
				$url = admin_url( $pagenow . $qs );
			}
		}

		wp_redirect( $url );
		exit();
	}

	/**
	 * Parse action
	 *
	 * @since  1.4
	 */
	public function get_action() {
		if ( ! isset( self::$_action ) ) {
			self::$_action = false;
			$this->verify_action();
			if ( self::$_action ) {
				defined( 'debug' ) && debug( 'do_login action verified: ' . var_export( self::$_action, true ) );
			}

		}
		return self::$_action;
	}

	/**
	 * Verify action
	 *
	 * @since  1.4
	 */
	private function verify_action() {
		if ( empty( $_REQUEST[ Router::ACTION ] ) ) {
			return;
		}

		$action = $_REQUEST[ Router::ACTION ];

		if ( ! $this->verify_nonce( $action ) ) {
			return;
		}

		$_can_option = current_user_can( 'manage_options' );

		switch ( $action ) {
			case self::ACTION_PSWD:
			case self::ACTION_AUTH:
			case self::ACTION_INSTALLER:
				if ( $_can_option ) {
					self::$_action = $action;
				}
				return;

			default:
				defined( 'debug' ) && debug( 'do_login match falied: ' . $action );
				return;
		}

	}

	/**
	 * Verify nonce
	 *
	 * @since  1.4
	 */
	private function verify_nonce( $action ) {
		if ( ! isset( $_REQUEST[ Router::NONCE ] ) || ! wp_verify_nonce( $_REQUEST[ Router::NONCE ], $action ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get type value
	 *
	 * @since 1.4
	 * @access public
	 */
	public static function verify_type() {
		if ( empty( $_REQUEST[ self::TYPE ] ) ) {
			defined( 'debug' ) && debug( 'no type', 2 ) ;
			return false ;
		}

		defined( 'debug' ) && debug( 'parsed type: ' . $_REQUEST[ self::TYPE ], 2 ) ;

		return $_REQUEST[ self::TYPE ] ;
	}

}