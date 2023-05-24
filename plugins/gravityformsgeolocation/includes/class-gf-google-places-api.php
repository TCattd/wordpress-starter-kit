<?php

namespace Gravity_Forms\Gravity_Forms_Geolocation;

defined( 'ABSPATH' ) || die();

class GF_Google_Places_API {

	/**
	 * Google Places API URL.
	 *
	 * @since 1.0
	 * @var   string $api_url Google Places API URL.
	 */
	protected $api_url = 'https://maps.googleapis.com/maps/api/place/';

	/**
	 * Google Places API key.
	 *
	 * @since  1.0
	 * @var    string $token Google Places API key.
	 */
	protected $api_key = '';

	/**
	 * Add-on instance.
	 *
	 * @since 1.0
	 * @var   GF_Geolocation
	 */
	private $addon;

	/**
	 * Initialize API Library.
	 *
	 * @since 1.0
	 *
	 * @param GF_Geolocation $addon   GF_Geolocation instance.
	 * @param string         $api_key Google Places API key.
	 */
	public function __construct( $addon, $api_key ) {
		$this->addon   = $addon;
		$this->api_key = $api_key;
	}

	/**
	 * Make API request.
	 *
	 * @since 1.0
	 *
	 * @param string $path       Request path.
	 * @param array  $body       Body arguments.
	 * @param string $method     Request method. Defaults to GET.
	 * @param string $return_key Array key from response to return. Defaults to null (return full response).
	 *
	 * @return array|WP_Error
	 */
	private function make_request( $path = '', $body = array(), $method = 'GET', $return_key = null ) {
		// Log API call succeed.
		gf_geolocation()->log_debug( __METHOD__ . '(): Making request to: ' . $path );

		// Get API Key.
		$api_key = $this->api_key;

		$args = array(
			'method'    => $method,
			/**
			 * Filters if SSL verification should occur.
			 *
			 * @param bool false If the SSL certificate should be verified. Defaults to false.
			 *
			 * @return bool
			 */
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			/**
			 * Sets the HTTP timeout, in seconds, for the request.
			 *
			 * @param int 30 The timeout limit, in seconds. Defaults to 30.
			 *
			 * @return int
			 */
			'timeout'   => apply_filters( 'http_request_timeout', 30 ),
		);
		if ( 'GET' === $method || 'POST' === $method || 'PUT' === $method ) {
			$request_url     = $this->api_url . $path;
			$args['body']    = empty( $body ) ? '' : $body;
			$args['headers'] = array(
				'Accept'       => 'application/json;ver=1.0',
				'Content-Type' => 'application/json; charset=UTF-8',
			);
		}

		// Execute request.
		$response = wp_remote_request(
			$request_url,
			$args
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body           = gf_geolocation()->maybe_decode_json( wp_remote_retrieve_body( $response ) );
		$retrieved_response_code = $response['response']['code'];

		if ( 200 !== $retrieved_response_code ) {
			$error_message = rgars( $response_body, 'error/message', "Expected response code: 200. Returned response code: {$retrieved_response_code}." );
			$error_code    = rgars( $response_body, 'error/errors/reason', 'google_places_api_error' );

			gf_geolocation()->log_error( __METHOD__ . '(): Unable to validate with the Google Places API: ' . $error_message );

			return new WP_Error( $error_code, $error_message, $retrieved_response_code );
		}

		return $response_body;
	}


	/**
	 * Validate the Google Places API key.
	 *
	 * @since 1.0
	 *
	 * @return bool Whether the API key is valid or not.
	 */
	public function validate_api_key() {
		$query_args = array(
			'input=Weaver',
			'types=address',
			"key={$this->api_key}",
		);

		$path = 'autocomplete/json?' . implode( '&', $query_args );

		$response = $this->make_request( $path );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return isset( $response['status'] ) && 'OK' === $response['status'];
	}

}
