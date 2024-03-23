<?php
/**
 * Password less class
 *
 * @since 1.4
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Pswdless extends Instance {
	const TYPE_GEN = 'gen';
	const TYPE_LOCK = 'lock';
	const TYPE_DEL = 'del';
	const TYPE_TOGGLE_ONETIME = 'toggle_onetime';
	const TYPE_EXPIRE_7 = 'expire_7';

	private $_tb;
	protected function __construct() {
		$this->_tb = $this->cls( 'Data' )->tb( 'pswdless' );
	}

	/**
	 * Init
	 * @since  1.4
	 */
	public function init() {
		if ( ! empty( $_GET[ 'dologin' ] ) ) {
			add_action( 'init', array( $this, 'try_login' ) );
		}
	}

	/**
	 * Login
	 * @since  1.4
	 */
	public function try_login() {
		global $wpdb;

		$username = 'N/A';

		$info = explode( '.', $_GET[ 'dologin' ] );
		if ( empty( $info[ 0 ] ) || empty( $info[ 1 ] ) ) {
			return $this->_failed_login( $username );
			// exit( 'dologin_no_token' );
		}

		$pid = (int) $info[ 0 ];
		if ( $pid <= 0 ) {
			return $this->_failed_login( $username );
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$this->_tb` WHERE id = %d", $pid ) );
		if ( $row ) {
			$user_info = get_userdata( $row->user_id );
			$username = $user_info->user_login;
		}

		if ( ! $row || $row->hash != $info[ 1 ] ) {
			return $this->_failed_login( $username );
			// exit( 'dologin_err_hash' );
		}

		if ( $row->active != 1 ) {
			exit( 'dologin_link_used' );
		}

		if ( $row->expired_at < time() ) {
			exit( 'dologin_link_expired' );
		}

		// Show login confirm page
		if ( empty( $_POST[ 'confirmed' ] ) ) {
			require_once DOLOGIN_DIR . 'tpl/pswdless_cfm.tpl.php';
			exit;
		}

		// can login, update record first
		$q = "UPDATE `$this->_tb` SET last_used_at = %d, count = count + 1";
		if ( $row->onetime ) {
			$q .= ', active = 0 ';
		}
		$q .= ' WHERE id = %d';
		$wpdb->query( $wpdb->prepare( $q, array( time(), $pid ) ) );

		// Login
		wp_set_auth_cookie( $user_info->ID, false );
		do_action('wp_login', $user_info->user_login, $user_info );

		nocache_headers();

		Router::redirect( admin_url() );
	}

	/**
	 * Note failed login
	 * @since  1.4
	 */
	private function _failed_login( $username ) {
		do_action( 'wp_login_failed', $username );
	}

	/**
	 * Expiration set
	 *
	 * @since  1.4
	 */
	private function _expire_link() {
		global $wpdb;

		$pid = empty( $_GET[ 'dologin_id' ] ) ? 0 : (int) $_GET[ 'dologin_id' ];
		if ( $pid <= 0 ) {
			return;
		}

		$q = "UPDATE `$this->_tb` SET expired_at = GREATEST( expired_at, %d ) + 86400 * 7 WHERE id = %d";
		$wpdb->query( $wpdb->prepare( $q, time(), $pid ) );
	}

	/**
	 * Switch one time
	 *
	 * @since  1.4
	 */
	private function _onetime_link() {
		global $wpdb;

		$pid = empty( $_GET[ 'dologin_id' ] ) ? 0 : (int) $_GET[ 'dologin_id' ];
		if ( $pid <= 0 ) {
			return;
		}

		$q = "UPDATE `$this->_tb` SET onetime = ( onetime + 1 ) % 2 WHERE id = %d";
		$wpdb->query( $wpdb->prepare( $q, $pid ) );
	}

	/**
	 * Lock
	 *
	 * @since  1.4
	 */
	private function _lock_link() {
		global $wpdb;

		$pid = empty( $_GET[ 'dologin_id' ] ) ? 0 : (int) $_GET[ 'dologin_id' ];
		if ( $pid <= 0 ) {
			return;
		}

		$q = "UPDATE `$this->_tb` SET active = ( active + 1 ) % 2 WHERE id = %d";
		$wpdb->query( $wpdb->prepare( $q, $pid ) );
	}

	/**
	 * Delete
	 *
	 * @since  1.4.1
	 */
	public function del_link( $pid = false ) {
		global $wpdb;

		if ( ! $pid ) {
			if ( empty( $_GET[ 'dologin_id' ] ) ) {
				return;
			}

			$pid = $_GET[ 'dologin_id' ];
		}

		$pid = (int) $pid;
		if ( $pid <= 0 ) {
			return;
		}

		$q = "DELETE FROM `$this->_tb` WHERE id = %d";
		$wpdb->query( $wpdb->prepare( $q, $pid ) );
	}

	/**
	 * Generate link
	 *
	 * @since  1.4
	 * @access public
	 */
	public function gen_link( $src, $uid, $return_url = false ) {
		global $wpdb;

		$this->cls( 'Data' )->tb_create( 'pswdless' );

		$uid = (int) $uid;
		if ( $uid <= 0 ) {
			if ( $return_url ) {
				return 'Invalid User ID';
			}
			Router::redirect( admin_url( 'options-general.php?page=dologin' ) );
		}

		$hash = s::rrand( 32 );

		$q = "INSERT INTO `$this->_tb` SET user_id = %d, hash = %s, dateline = %d, onetime = 1, active = 1, src = %s, expired_at = %d";
		$wpdb->query( $wpdb->prepare( $q, array( $uid, $hash, time(), $src, time() + 86400 * 7 ) ) );
		$id = $wpdb->insert_id;

		if ( $return_url ) {
			return admin_url( '?dologin=' . $id . '.' . $hash );
		}

		Router::redirect( admin_url( 'options-general.php?page=dologin' ) );
	}

	/**
	 * Handler
	 *
	 * @since  1.4
	 */
	public function handler() {
		$type = Router::verify_type();

		switch ( $type ) {
			case self::TYPE_GEN:
				if ( ! empty( $_GET[ 'uid' ] ) ) {
					$user = wp_get_current_user();
					$this->gen_link( $user->display_name, (int) $_GET[ 'uid' ] );
				}
				break;

			case self::TYPE_LOCK:
				$this->_lock_link();
				break;

			case self::TYPE_DEL:
				$this->del_link();
				break;

			case self::TYPE_TOGGLE_ONETIME:
				$this->_onetime_link();
				break;

			case self::TYPE_EXPIRE_7:
				$this->_expire_link();
				break;

			default:
				break;
		}
	}

}