<?php

namespace LLAR\Core\Http;

class HttpTransportCurl implements HttpTransportInterface {

	/**
	 * @param $url
	 * @param array $options
	 *
	 * @return array
	 */
	public function get( $url, $options = array() ) {

		if( !empty( $options['data'] ) ) {
			$query_str = http_build_query( $options['data'] );
			$url .= "?{$query_str}";
		}

		$headers = !empty( $options['headers'] ) ? $options['headers'] : array();

		return $this->request( $url, 'GET', $headers );
	}

	/**
	 * @param $url
	 * @param array $options
	 *
	 * @return array
	 */
	public function post( $url, $options = array() ) {

		$headers = !empty( $options['headers'] ) ? $options['headers'] : array();
		$data = !empty( $options['data'] ) ? $options['data'] : array();

		return $this->request( $url, 'POST', $headers, $data );
	}

	/**
	 * @param $url
	 * @param $method
	 * @param array $headers
	 * @param array $data
	 *
	 * @return array
	 */
	private function request( $url, $method, $headers = array(), $data = array() ) {

		$handle = curl_init( $url );
		
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );

		if( $method === 'POST' ) {
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode( $data, JSON_FORCE_OBJECT ) );
		}

		if ( !empty( $headers ) ) {
			curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		}

		$response = curl_exec( $handle );
		$response_status = curl_getinfo( $handle, CURLINFO_HTTP_CODE );

		curl_close( $handle );

		return array(
			'data'      => $response,
			'status'    => intval( $response_status ),
			'error'     => !$response ? curl_error( $handle ) : null
		);
	}
}