<?php

namespace LLAR\Core;

use LLAR\Lib\CidrCheck;

if ( !defined( 'ABSPATH' ) ) exit;

class Helpers {

	/**
	 * @param string $msg
	 * @param bool $is_error
	 */
	public static function show_message( $msg = '', $is_error = false ) {
		if ( empty( $msg ) ) {
			return;
		}

		$class = $is_error ? 'error' : 'updated';
		echo '<div id="message" class="' . $class . ' fade"><p>' . $msg . '</p></div>';
	}

	/**
	 * @param $log
	 *
	 * @return array
	 */
	public static function sorted_log_by_date( $log ) {
		$new_log = array();

		if ( ! is_array( $log ) || empty( $log ) ) {
			return $new_log;
		}

		foreach ( $log as $ip => $users ) {

			if ( ! empty( $users ) ) {
				foreach ( $users as $user_name => $info ) {

					if ( is_array( $info ) ) { // For new plugin version
						$new_log[ $info['date'] ] = array(
							'ip'       => $ip,
							'username' => $user_name,
							'counter'  => $info['counter'],
							'gateway'  => ( isset( $info['gateway'] ) ) ? $info['gateway'] : '-',
							'unlocked' => ! empty( $info['unlocked'] ),
						);
					} else { // For old plugin version
						$new_log[0] = array(
							'ip'       => $ip,
							'username' => $user_name,
							'counter'  => $info,
							'gateway'  => '-',
							'unlocked' => false,
						);
					}

				}
			}

		}

		krsort( $new_log );

		return $new_log;
	}

	public static function get_countries_list() {

		if ( ! ( $countries = require LLA_PLUGIN_DIR . '/resources/countries.php' ) ) {

			return array();
		}

		asort( $countries );

		return $countries;
	}

	/**
	 * @param $ip
	 * @param $cidr
	 *
	 * @return bool
	 */
	public static function check_ip_cidr( $ip, $cidr ) {

		if ( ! $ip || ! $cidr ) {
			return false;
		}

		$cidr_checker = new CidrCheck();

		return $cidr_checker->match( $ip, $cidr );
	}

	/**
	 * Checks if the plugin is installed as Must Use plugin
	 *
	 * @return bool
	 */
	public static function is_mu() {

		return ( strpos( LLA_PLUGIN_DIR, 'mu-plugins' ) !== false );
	}

	/**
	 * @param $content
	 *
	 * @return string|string[]|null
	 */
	public static function deslash( $content ) {

		$content = preg_replace( "/\\\+'/", "'", $content );
		$content = preg_replace( '/\\\+"/', '"', $content );
		$content = preg_replace( '/\\\+/', '\\', $content );

		return $content;
	}

	public static function is_auto_update_enabled() {
		$auto_update_plugins = get_site_option( 'auto_update_plugins' );
		return is_array( $auto_update_plugins ) && in_array( LLA_PLUGIN_BASENAME, $auto_update_plugins );
	}

	public static function get_wordpress_version() {
		global $wp_version;
		return $wp_version;
	}

	/**
	 * @return bool
	 */
	public static function is_network_mode() {
		if ( !is_multisite() ) return false;

		require_once ABSPATH.'wp-admin/includes/plugin.php';

		return is_plugin_active_for_network( 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' );
	}

	/**
	 * @return bool
	 */
	public static function allow_local_options() {

		if( !self::is_network_mode() ) return true;

		return get_site_option( 'limit_login_allow_local_options', false );
	}

	/**
	 * @return bool
	 */
	public static function use_local_options() {

		if( !self::is_network_mode() ) return true;

		return get_site_option( 'limit_login_allow_local_options', false ) &&
		       get_option( 'limit_login_use_local_options', false );
	}

	/**
	 * @param $new_app_config
	 * @param false $update_created_at
	 *
	 * @return false
	 */
	public static function cloud_app_update_config( $new_app_config, $update_created_at = false ) {
		if( !$new_app_config ) return false;

		if( $active_app_config = Config::get( 'app_config' ) ) {

			foreach ( $active_app_config['settings'] as $key => $info ) {

				if( array_key_exists( $key, $new_app_config['settings'] ) ) {

					if( !empty( $new_app_config['settings'][$key]['options'] ) &&
					    !in_array( $info['value'], $new_app_config['settings'][$key]['options'] ) ) {

						continue;
					}

					$new_app_config['settings'][$key]['value'] = $info['value'];
				}
			}

		}

		if( $update_created_at )
			$new_app_config['created_at'] = time();

		Config::update( 'app_config', $new_app_config );
	}

	/**
	 * @param $filepath
	 *
	 * @return bool
	 */
	public static function is_writable( $filepath ) {
		return file_exists( $filepath ) && wp_is_writable( $filepath );
	}

	public static function ip_in_range( $ip, $list ) {

		foreach ( $list as $range ) {

			$range = array_map('trim', explode('-', $range) );
			if ( count( $range ) == 1 ) {

				// CIDR
				if( strpos( $range[0], '/' ) !== false && self::check_ip_cidr( $ip, $range[0] ) ) {

					return true;
				}
				// Single IP
				else if ( (string)$ip === (string)$range[0] ) {

					return true;
				}

			} else {

				$low = ip2long( $range[0] );
				$high = ip2long( $range[1] );
				$needle = ip2long( $ip );

				if ( $low === false || $high === false || $needle === false )
					continue;

				$low = (float)sprintf("%u",$low);
				$high = (float)sprintf("%u",$high);
				$needle = (float)sprintf("%u",$needle);

				if ( $needle >= $low && $needle <= $high )
					return true;
			}
		}

		return false;
	}

	public static function detect_ip_address( $trusted_ip_origins ) {
		if( empty( $trusted_ip_origins ) || !is_array( $trusted_ip_origins ) ) {

			$trusted_ip_origins = array();
		}

		if( !in_array( 'REMOTE_ADDR', $trusted_ip_origins ) ) {

			$trusted_ip_origins[] = 'REMOTE_ADDR';
		}

		$ip = '';
		foreach ( $trusted_ip_origins as $origin ) {

			if( isset( $_SERVER[$origin] ) && !empty( $_SERVER[$origin] ) ) {

				if( strpos( $_SERVER[$origin], ',' ) !== false ) {

					$origin_ips = explode( ',', $_SERVER[$origin] );
					$origin_ips = array_map( 'trim', $origin_ips );

					if( $origin_ips ) {

						foreach ($origin_ips as $check_ip) {

							if( self::is_ip_valid( $check_ip ) ) {

								$ip = $check_ip;
								break 2;
							}
						}
					}
				}

				if( self::is_ip_valid( $_SERVER[$origin] ) ) {

					$ip = $_SERVER[$origin];
					break;
				}
			}
		}

		$ip = preg_replace('/^(\d+\.\d+\.\d+\.\d+):\d+$/', '\1', $ip);

		return $ip;
	}

	public static function get_all_ips() {

		$ips = array();

		foreach ( $_SERVER as $key => $value ) {

			if( in_array( $key, array( 'SERVER_ADDR' ) ) ) continue;

			if( $valid_ip = self::is_ip_valid( $value ) ) {

				$ips[$key] = $valid_ip;
			}
		}

		if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && !array_key_exists( 'HTTP_X_FORWARDED_FOR', $ips ) ) {

			$ips['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return $ips;
	}

	public static function is_ip_valid( $ip ) {
		if( empty( $ip ) ) return false;

		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ?:
			filter_var( preg_replace('/^(\d+\.\d+\.\d+\.\d+):\d+$/', '\1', $ip ), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	public static function detect_gateway() {

		$gateway = 'wp_login';

		if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
			$gateway = 'wp_woo_login';
		} elseif ( isset( $GLOBALS['wp_xmlrpc_server'] ) && is_object( $GLOBALS['wp_xmlrpc_server'] ) ) {
			$gateway = 'wp_xmlrpc';
		}

		return $gateway;
	}

	public static function short_number($num) {
	    $units = array( '', 'K', 'M', 'B', 'T' );
	    for ($i = 0; $num >= 1000; $i++) {
	        $num /= 1000;
	    }
		return round($num, 1) . $units[$i];
	}

	public static function send_mail_with_logo( $to, $subject, $body ) {

		add_action( 'phpmailer_init', array( 'LLAR\Core\Helpers', 'add_attachments_to_php_mailer' ) );

		@wp_mail( $to, $subject, $body, array( 'content-type: text/html' ) );

		remove_action( 'phpmailer_init', array( 'LLAR\Core\Helpers', 'add_attachments_to_php_mailer' ) );
	}

	public static function add_attachments_to_php_mailer( &$phpmailer ) {
		$logo_path = LLA_PLUGIN_DIR . 'assets/img/logo.png';

		if( file_exists( $logo_path ) ) {
			$phpmailer->AddEmbeddedImage( $logo_path, 'logo' );
		}
	}
}