<?php
namespace dologin;
defined( 'WPINC' ) || exit;

use WP_CLI;

/**
 * Passwordless API CLI
 */
class CLI extends Instance {
	public function __construct() {
		defined( 'debug' ) && debug( 'CLI init' );
	}

	/**
	 * List all passwordless links
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # List all passwordless link
	 *     $ wp dologin list
	 *
	 */
	public function list() {
		$list = $this->cls( 'Admin' )->pswdless_log();
		foreach ( $list as $k => $v ) {
			$list[ $k ] = (array) $v;
			$user = get_user_by( 'id', $v->user_id );
			$list[ $k ][ 'Login_Name' ] = $user ? $user->user_login : 'N/A';
			$list[ $k ][ 'Expiration' ] = $v->expired_at > time() ? Util::readable_time( $v->expired_at - time(), 3600, false ) : __( 'Expired', 'dologin' );
			$list[ $k ][ 'Created_At' ] = Util::readable_time( $v->dateline );
		}
		if ( $list ) {
			WP_CLI\Utils\format_items( 'table', $list, array( 'id', 'Login_Name', 'Expiration', 'Created_At', 'onetime', 'active', 'count' ) );
		}
	}

	/**
	 * Generate a passwordless link for one username
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate a passwordless link for one username
	 *     $ wp dologin gen root
	 *
	 */
	public function gen( $args ) {
		$uname = $args[ 0 ];
		$user = get_user_by( 'login', $uname );
		if ( ! $user ) {
			WP_CLI::error( __( 'No related user.', 'dologin' ) );
			return;
		}

		$link = $this->cls( 'Pswdless' )->gen_link( 'CLI-' . $user->display_name, $user->ID, true );
		WP_CLI::success( 'Link generated: ' . $link );
	}

	/**
	 * Delete a passwordless link w/ the ID in list
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # Delete the passwordless link (ID is 5)
	 *     $ wp dologin del 5
	 *
	 */
	public function del( $args ) {
		$id = $args[ 0 ];
		if ( ! $id ) {
			WP_CLI::error( __( 'No ID to delete.', 'dologin' ) );
			return;
		}

		$this->cls( 'Pswdless' )->del_link( $id );
		WP_CLI::success( 'Delete passwordless link successfully. ID = ' . $id );
	}
}
