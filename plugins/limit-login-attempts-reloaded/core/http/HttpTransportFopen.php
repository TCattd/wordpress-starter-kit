<?php

namespace LLAR\Core\Http;

class HttpTransportFopen implements HttpTransportInterface {

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

		$method = strtoupper( trim( $method ) );

		$request_data = null;
		if( !empty( $data ) ) {
			$request_data = json_encode( $data, JSON_FORCE_OBJECT );
		}

		$context = stream_context_create( array(
			'http' => array(
                'method'  => $method,
                'header'  => implode( "\r\n", $headers ),
                'content' => $request_data
            )
		));

		$fp = @fopen( $url, 'rb', false, $context );

		$error = null;
		$status = null;
		$response = null;

		if ( !$fp ) {

			if( !empty( $http_response_header[0] ) ) {
				list(, $code, $message ) = explode( ' ', $http_response_header[0], 3 );
				$error = $message;
				$status = $code;

			} else {
				$last_err = error_get_last();
				$error = !empty( $last_err['message'] ) ? $last_err['message'] : 'Unknown error!';
			}

		} else {
			list(, $code ) = explode( ' ', $http_response_header[0], 3 );
			$status = $code;

			$response = stream_get_contents( $fp );
		}

		return array(
			'data'      => $response,
			'status'    => intval( $status ),
			'error'     => $error
		);
	}
}