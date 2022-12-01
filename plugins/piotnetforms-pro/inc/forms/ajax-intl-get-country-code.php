<?php

	add_action( 'wp_ajax_piotnetforms_get_country_code', 'piotnetforms_get_country_code' );
	add_action( 'wp_ajax_nopriv_piotnetforms_get_country_code', 'piotnetforms_get_country_code' );

	function piotnetforms_get_country_code() {
		$ipaddress = '';
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} elseif ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}

		$url = 'http://ip-api.com/php/'.$ipaddress;
		$query = wp_remote_get( $url, ['timeout' => 5] );
		if ( is_wp_error( $query ) ) {
			echo 'error';
		} else {
			$response =  @unserialize( wp_remote_retrieve_body( $query ) );
			if ( $response && $response['status'] == 'success' ) {
				echo $response['countryCode'];
			} else {
				echo 'error';
			}
		}

		wp_die();
	}
