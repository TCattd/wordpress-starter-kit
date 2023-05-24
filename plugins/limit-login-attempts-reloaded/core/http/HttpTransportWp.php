<?php

namespace LLAR\Core\Http;

class HttpTransportWp implements HttpTransportInterface {

	/**
	 * @param $url
	 * @param array $options
	 *
	 * @return array
	 */
	public function get( $url, $options = array() ) {
		$response = wp_remote_get( $url, array(
			'headers' 	=> !empty( $options['headers'] ) ? $this->format_headers( $options['headers'] ) : array(),
			'body' 		=> !empty( $options['data'] ) ? $options['data'] : array()
		) );

		return $this->prepare_response( $response );
	}

	/**
	 * @param $url
	 * @param array $options
	 *
	 * @return array
	 */
	public function post( $url, $options = array() ) {
		$response = wp_remote_post( $url, array(
			'headers' 	=> !empty( $options['headers'] ) ? $this->format_headers( $options['headers'] ) : array(),
			'body' 		=> !empty( $options['data'] ) ? json_encode( $options['data'], JSON_FORCE_OBJECT ) : null
		) );

		return $this->prepare_response( $response );
	}

	/**
	 * @param $response
	 *
	 * @return array
	 */
	private function prepare_response( $response ) {

		$return = array(
			'data'      => null,
			'status'    => 0,
			'error'     => null
		);

		if( is_wp_error( $response ) ) {
			$return['error'] = $response->get_error_message();
		} else {
			$return['data'] = wp_remote_retrieve_body( $response );
			$return['status'] = intval( wp_remote_retrieve_response_code( $response ) );
		}

		return $return;
	}

	/**
	 * @param array $headers
	 *
	 * @return array
	 */
	private function format_headers( $headers = array() ) {

		$formatted_headers = array();

		if( !empty( $headers ) ) {
			foreach ( $headers as $header ) {
				list( $name, $value ) = explode( ':', $header );

				$formatted_headers[ trim( $name ) ] = trim( $value );
			}
		}

		return $formatted_headers;
	}
}