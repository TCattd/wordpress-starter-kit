<?php

/**
 * IP class
 *
 * @since 1.0
 */

namespace dologin;

defined('WPINC') || exit;

class IP extends Instance
{
	private $_visitor_geo_data = array();

	public static $PREFIX_SET = array(
		'continent',
		'continent_code',
		'country',
		'country_code',
		'subdivision',
		'subdivision_code',
		'city',
		'postal',
	);

	/**
	 * Get visitor's IP
	 *
	 * @since  1.0
	 * @access public
	 */
	public static function me()
	{
		$_ip = '';
		// if ( function_exists( 'apache_request_headers' ) ) {
		// 	$apache_headers = apache_request_headers();
		// 	$_ip = ! empty( $apache_headers['True-Client-IP'] ) ? $apache_headers['True-Client-IP'] : false;
		// 	if ( ! $_ip ) {
		// 		$_ip = ! empty( $apache_headers['X-Forwarded-For'] ) ? $apache_headers['X-Forwarded-For'] : false;
		// 		$_ip = explode( ',', $_ip );
		// 		$_ip = $_ip[ 0 ];
		// 	}

		// }

		if (!$_ip) {
			$_ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
		}

		if (strpos($_ip, ',')) {
			$_ip = explode(',', $_ip);
			$_ip = trim($_ip[0]);
		}

		return preg_replace('/^(\d+\.\d+\.\d+\.\d+):\d+$/', '\1', $_ip);
	}

	/**
	 * Get geolocation info of visitor IP
	 *
	 * @since 1.0
	 * @access public
	 */
	public static function geo($ip = false)
	{
		if (!$ip) {
			$ip = self::me();
		}

		$response = wp_remote_get("https://www.doapi.us/ip/$ip/json");

		$data = array();
		if (!is_wp_error($response)) {
			// return new \WP_Error( 'remote_get_fail', 'Failed to fetch geolocation info', array( 'status' => 404 ) );

			$data = $response['body'];

			$data = json_decode($data, true);
		}

		// Build geo data
		$geo_list = array('ip' => $ip);
		foreach (self::$PREFIX_SET as $tag) {
			$geo_list[$tag] = !empty($data[$tag]) ? trim($data[$tag]) : false;
		}

		return $geo_list;
	}

	/**
	 * Validate if hit the list
	 *
	 * @since  1.0
	 * @access public
	 */
	public function maybe_hit_rule($list, $ip_only = false)
	{
		if (!$this->_visitor_geo_data) {
			$this->_visitor_geo_data = $ip_only ? array('ip' => self::me()) : self::geo();
		}

		foreach ($list as $v) {
			// Drop comments
			if (strpos($v, '#') !== false) {
				$v = trim(substr($v, 0, strpos($v, '#')));
			}

			if (!$v) {
				continue;
			}

			$v = explode(',', $v);

			// Go through each rule
			foreach ($v as $v2) {
				$negative_match = false;

				if (!strpos($v2, ':')) { // Optional `ip:` case
					$curr_k = 'ip';
				} else {
					list($curr_k, $v2) = explode(':', $v2, 2);
					$curr_k = trim($curr_k);
					if (substr($curr_k, -1) === '!') {
						$negative_match = true;
						$curr_k = trim(substr($curr_k, 0, -1));
					}
				}

				$v2 = trim($v2);

				// Invalid rule
				if (!$v2) {
					continue 2;
				}

				// Rule set not match
				if (empty($this->_visitor_geo_data[$curr_k])) {
					continue 2;
				}

				$v2 = strtolower($v2);
				$visitor_v = strtolower($this->_visitor_geo_data[$curr_k]);
				$visitor_v = trim($visitor_v);

				// If has IP wildcard range, convert $v2
				if ($curr_k == 'ip' && strpos($v2, '*') !== false) {
					// If is same ip type (both are ipv4 or v6)
					$visitor_ip_type = \WP_Http::is_ip_address($visitor_v);
					if ($visitor_ip_type == \WP_Http::is_ip_address($v2)) {
						$ip_separator = $visitor_ip_type == 4 ? '.' : ':';
						$uip = explode($ip_separator, $visitor_v);
						$v2 = explode($ip_separator, $v2);
						foreach ($uip as $k3 => $v3) {
							if ($v2[$k3] == '*') {
								$v2[$k3] = $v3;
							}
						}
						$v2 = implode($ip_separator, $v2);
					}
				}

				if (!$negative_match && $visitor_v != $v2) {
					continue 2;
				}

				if ($negative_match && $visitor_v == $v2) {
					continue 2;
				}
			}

			return true;
		}

		return false;
	}
}
