<?php
/**
 * Utility class
 *
 * @since 1.1
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Util extends Instance {
	/**
	 * Init Utility
	 *
	 * @since 1.1
	 * @access public
	 */
	public function init() {
		if ( Conf::val( 'auto_upgrade' ) ) {
			add_filter( 'auto_update_plugin', array( $this, 'auto_update' ), 10, 2 );
		}
	}

	/**
	 * Handle auto update
	 *
	 * @since 1.1
	 * @access public
	 */
	public function auto_update( $update, $item ) {
		if (!empty($item->slug) && $item->slug=='dologin') {
			$auto_v = self::version_check( 'auto_update_plugin' );

			if ( $auto_v && ! empty( $item->new_version ) && $auto_v === $item->new_version ) {
				return true;
			}
		}

		return $update; // Else, use the normal API response to decide whether to update or not
	}

	/**
	 * Builds an url with an action and a nonce.
	 *
	 * @since  1.4
	 * @access public
	 */
	public static function build_url( $action, $type = false, $is_ajax = false, $page = null, $append_arr = null ) {
		$prefix = '?';

		if ( ! $is_ajax ) {
			if ( $page ) {
				// If use admin url
				if ( $page === true ) {
					$page = 'admin.php';
				}
				else {
					if ( strpos( $page, '?' ) !== false ) {
						$prefix = '&';
					}
				}
				$combined = $page . $prefix . Router::ACTION . '=' . $action;
			}
			else {
				// Current page rebuild URL
				$params = $_GET;

				if ( ! empty( $params ) ) {
					if ( isset( $params[ 'DOLOGIN_ACTION' ] ) ) {
						unset( $params[ 'DOLOGIN_ACTION' ] );
					}
					if ( isset( $params[ '_wpnonce' ] ) ) {
						unset( $params[ '_wpnonce' ] );
					}
					if ( ! empty( $params ) ) {
						$prefix .= http_build_query( $params ) . '&';
					}
				}
				global $pagenow;
				$combined = $pagenow . $prefix . Router::ACTION . '=' . $action;
			}
		}
		else {
			$combined = 'admin-ajax.php?action=dologin_ajax&' . Router::ACTION . '=' . $action;
		}

		if ( is_network_admin() ) {
			$prenonce = network_admin_url( $combined );
		}
		else {
			$prenonce = admin_url( $combined );
		}
		$url = wp_nonce_url( $prenonce, $action, Router::NONCE );

		if ( $type ) {
			// Remove potential param `type` from url
			$url = parse_url( htmlspecialchars_decode( $url ) );
			parse_str( $url[ 'query' ], $query );

			$built_arr = array_merge( $query, array( Router::TYPE => $type ) );
			if ( $append_arr ) {
				$built_arr = array_merge( $built_arr, $append_arr );
			}
			$url[ 'query' ] = http_build_query( $built_arr );
			self::compatibility();
			$url = http_build_url( $url );
			$url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		}

		return $url;
	}

	/**
	 * Improve compatibility to PHP old versions
	 *
	 * @since  1.2.2
	 *
	 */
	public static function compatibility() {
		require_once DOLOGIN_DIR . 'lib/php-compatibility.func.php';
	}

	/**
	 * Check if is login page or not
	 *
	 * @since  1.3
	 * @access public
	 */
	public static function is_login_page() {
		$is_login_page = in_array( $GLOBALS[ 'pagenow' ], array( 'wp-login.php', 'wp-register.php' ), true );

		return apply_filters( 'dologin_is_login_page', $is_login_page );
	}

	/**
	 * Version check
	 *
	 * @since 1.1
	 * @access public
	 */
	public static function version_check( $tag ) {
		return false;
		// Check latest stable version allowed to upgrade
		$url = 'https://doapi.us/compatible_list/dologin?v=' . Core::VER . '&v2=' . ( defined( 'DOLOGIN_CUR_V' ) ? DOLOGIN_CUR_V : '' ) . '&src=' . $tag;

		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );
		if ( ! is_array( $response ) || empty( $response[ 'body' ] ) ) {
			return false;
		}

		return $response[ 'body' ];
	}

	/**
	 * Set seconds/timestamp to readable format
	 *
	 * @since  1.2
	 * @access public
	 */
	public static function readable_time( $seconds_or_timestamp, $timeout = 3600, $backward = true ) {
		if ( strlen( $seconds_or_timestamp ) == 10 ) {
			$seconds = time() - $seconds_or_timestamp;
			if ( $seconds > $timeout ) {
				return date( 'm/d/Y H:i:s', $seconds_or_timestamp + get_option( 'gmt_offset' ) * 60 * 60 );
			}
		}
		else {
			$seconds = $seconds_or_timestamp;
		}
		$res = '';
		if ( $seconds > 86400 ) {
			$num = floor( $seconds / 86400 );
			$res .= $num . 'd';
			$seconds %= 86400;
		}
		if ( $seconds > 3600 ) {
			if ( $res ) {
				$res .= ', ';
			}
			$num = floor( $seconds / 3600 );
			$res .= $num . 'h';
			$seconds %= 3600;
		}
		if ( $seconds > 60 ) {
			if ( $res ) {
				$res .= ', ';
			}
			$num = floor( $seconds / 60 );
			$res .= $num . 'm';
			$seconds %= 60;
		}
		if ( $seconds > 0 ) {
			if ( $res ) {
				$res .= ' ';
			}
			$res .= $seconds . 's';
		}
		if ( ! $res ) {
			return $backward ? __( 'just now', 'dologin' ) : __( 'right now', 'dologin' );
		}
		$res = $backward ? sprintf( __( ' %s ago', 'dologin' ), $res ) : $res;
		return $res;
	}

	/**
	 * Generate pagination
	 *
	 * @since 2.7
	 * @access public
	 */
	public static function pagination( $total, $limit, $return_offset = false )
	{
		$pagenum = isset( $_GET[ 'pagenum' ] ) ? absint( $_GET[ 'pagenum' ] ) : 1;

		$offset = ( $pagenum - 1 ) * $limit;
		$num_of_pages = ceil( $total / $limit );

		if ( $offset > $total ) {
			$offset = $total - $limit;
		}

		if ( $return_offset ) {
			return $offset;
		}

		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pagenum', '%#%' ),
			'format' => '',
			'prev_text' => __( '&laquo;', 'text-domain' ),
			'next_text' => __( '&raquo;', 'text-domain' ),
			'total' => $num_of_pages,
			'current' => $pagenum,
		) );

		return '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
	}

	/**
	 * Deactivate
	 *
	 * @since  1.1
	 * @access public
	 */
	public static function deactivate() {
		delete_transient( 'dologin_activation_redirect' );

		self::version_check( 'deactivate' );

		// Data::cls()->tables_del();
	}

	/**
	 * Uninstall clearance
	 *
	 * @since  1.1
	 * @access public
	 */
	public static function uninstall() {
		self::version_check( 'uninstall' );

		Data::cls()->tables_del();
	}

	/**
	 * Activation redirect
	 *
	 * @since  1.2.2
	 * @access public
	 */
	public static function activate() {
		if ( ! defined( 'SILENCE_INSTALL' ) ) {
			set_transient( 'dologin_activation_redirect', true, 30 );
		}

		Data::cls()->tables_create();
	}

}