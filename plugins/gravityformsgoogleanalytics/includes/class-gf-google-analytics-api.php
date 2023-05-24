<?php

namespace Gravity_Forms\Gravity_Forms_Google_Analytics;

defined( 'ABSPATH' ) || die();

use WP_Error;
/**
 * Gravity Forms Google Analytics Add-On.
 *
 * @since     1.0.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2019, Rocketgenius
 */

/**
 * Helper class for retrieving the Google Analytics API validation.
 */
class GF_Google_Analytics_API {

	/**
	 * Google Analytics API URL.
	 *
	 * @since  1.0
	 * @var    string $ga_api_url Google Analytics API URL.
	 */
	protected $ga_api_url = 'https://www.googleapis.com/analytics/v3/';

	/**
	 * Google Tag Manager API URL.
	 *
	 * @since  1.0
	 * @var    string $gtm_api_url Google Tag Manager API URL.
	 */
	protected $gtm_api_url = 'https://www.googleapis.com/tagmanager/v2/';

	/**
	 * Google Analytics API token.
	 *
	 * @since  1.0
	 * @var    string $token Google Analytics Token.
	 */
	protected $token = null;

	/**
	 * Add-on instance.
	 *
	 * @var GF_Google_Analytics
	 */
	private $addon;

	/**
	 * Initialize API library.
	 *
	 * @since  1.0
	 *
	 * @param GF_Google_Analytics $addon GF_Google_Analytics instance.
	 * @param string              $token Google Analytics API token.
	 */
	public function __construct( $addon, $token = null ) {
		$this->token = $token;
		$this->addon = $addon;
	}

	/**
	 * Make API request.
	 *
	 * @since  1.0
	 *
	 * @param string $path       Request path.
	 * @param string $mode       ga or gtm for Google Analytics or Tag Manager.
	 * @param array  $body       Body arguments.
	 * @param string $method     Request method. Defaults to GET.
	 * @param string $return_key Array key from response to return. Defaults to null (return full response).
	 *
	 * @return array|WP_Error
	 */
	private function make_request( $path = '', $mode = 'ga', $body = array(), $method = 'GET', $return_key = null ) {

		// Log API call succeed.
		gf_google_analytics()->log_debug( __METHOD__ . '(): Making request to: ' . $path );

		// Get API Key.
		$token = $this->token;

		// Get mode.
		$api_url = '';
		switch ( $mode ) {
			case 'ga':
				$api_url = $this->ga_api_url;
				break;
			case 'gtm':
				$api_url = $this->gtm_api_url;
				break;
			default:
				return new WP_Error( 'google_analytics_invalid_mode', esc_html__( 'The API mode supplied is not supported by the Google Analytics API.', 'gravityformsgoogleanalytics' ), array() );
		}

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
			$request_url     = $api_url . $path;
			$args['body']    = empty( $body ) ? '' : $body;
			$args['headers'] = array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json;ver=1.0',
				'Content-Type'  => 'application/json; charset=UTF-8',
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

		$response_body           = gf_google_analytics()->maybe_decode_json( wp_remote_retrieve_body( $response ) );
		$retrieved_response_code = $response['response']['code'];

		if ( 200 !== $retrieved_response_code ) {
			$error_message = rgars( $response_body, 'error/message', "Expected response code: 200. Returned response code: {$retrieved_response_code}." );
			$error_code    = rgars( $response_body, 'error/errors/reason', 'google_analytics_api_error' );

			gf_google_analytics()->log_error( __METHOD__ . '(): Unable to validate with the Google Analytics API: ' . $error_message );

			return new WP_Error( $error_code, $error_message, $retrieved_response_code );
		}

		return $response_body;
	}

	/**
	 * Retrieve a refresh token when the original expires.
	 *
	 * @since 1.0.0
	 *
	 * @param string $refresh_token Refresh token to re-authorize.
	 *
	 * @return array|WP_Error
	 */
	public function refresh_token( $refresh_token ) {
		// Connect to Gravity Form's API.
		$response = wp_remote_post(
			$this->addon->get_gravity_api_url( '/auth/googleanalytics/refresh' ),
			array(
				'body' => array(
					'refresh_token' => rawurlencode( $refresh_token ),
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$response_body = gf_google_analytics()->maybe_decode_json( wp_remote_retrieve_body( $response ) );

		$retrieved_response_code = $response['response']['code'];
		if ( 200 !== absint( $retrieved_response_code ) || ! rgars( $response_body, 'token/access_token' ) ) {
			$error_message = "Expected response code: 200. Returned response code: {$retrieved_response_code}.";

			gf_google_analytics()->log_error( __METHOD__ . '(): Unable to Reauthorize refresh token: ' . $error_message );
			gf_google_analytics()->log_error( __METHOD__ . '(): Response body: ' . var_export( $response_body, true ) );

			return new WP_Error( 'google_analytics_api_error', $error_message, $retrieved_response_code );
		}
		return $response_body;
	}

	/**
	 * Retrieve Google Analytics goals based on the view.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body        Body information.
	 * @param string $account_id  Account ID for analytics.
	 * @param string $property_id The property ID to retrieve goals for.
	 * @param string $view        The Google Anaytics view.
	 *
	 * @return array|WP_Error
	 */
	public function get_goals( $body, $account_id, $property_id, $view ) {
		return $this->make_request(
			sprintf(
				'management/accounts/%s/webproperties/%s/profiles/%s/goals',
				$account_id,
				$property_id,
				$view
			),
			'ga',
			$body
		);
	}

	/**
	 * Update an existing Google Analytics goal.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body        Body information.
	 * @param string $account_id  Account ID for analytics.
	 * @param string $property_id The property ID to retrieve goals for.
	 * @param string $view        The Google Anaytics view.
	 * @param sring  $goal_id     The Goal ID to update.
	 *
	 * @return array|WP_Error
	 */
	public function update_goal( $body, $account_id, $property_id, $view, $goal_id ) {
		return $this->make_request(
			sprintf(
				'management/accounts/%s/webproperties/%s/profiles/%s/goals/%s',
				$account_id,
				$property_id,
				$view,
				$goal_id
			),
			'ga',
			$body,
			'PUT'
		);
	}

	/**
	 * Create a Google Analytics Goal.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body        Body information.
	 * @param string $account_id  Account ID for analytics.
	 * @param string $property_id The property ID to retrieve goals for.
	 * @param string $profile_id  The property ID for the Analytics account.
	 *
	 * @return array|WP_Error
	 */
	public function create_goal( $body, $account_id, $property_id, $profile_id ) {
		return $this->make_request(
			sprintf(
				'management/accounts/%s/webproperties/%s/profiles/%s/goals',
				$account_id,
				$property_id,
				$profile_id
			),
			'ga',
			$body,
			'POST'
		);
	}

	/**
	 * Retrieve a profile ID for Google Analytics
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body       Body information.
	 * @param string $account_id Account ID for analytics.
	 * @param string $ua_code    The Google Analytics UA ID.
	 *
	 * @return array|WP_Error
	 */
	public function get_profile_id( $body, $account_id, $ua_code ) {
		return $this->make_request(
			sprintf(
				'management/accounts/%s/webproperties/%s/profiles',
				$account_id,
				$ua_code
			),
			'ga',
			$body
		);
	}

	/**
	 * Retrieve views for Google Analytics
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body       Body information.
	 * @param string $account_id Account ID for analytics.
	 * @param string $ua_code    The Google Analytics UA ID.
	 *
	 * @return array|WP_Error
	 */
	public function get_views( $body, $account_id, $ua_code ) {
		return $this->make_request(
			sprintf(
				'management/accounts/%s/webproperties/%s/profiles',
				$account_id,
				$ua_code
			),
			'ga',
			$body
		);
	}

	/**
	 * Retrieve a child account for Google Analytics
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Full path to Child account.
	 * @param array  $body Body information.
	 *
	 * @return array|WP_Error
	 */
	public function get_child_account( $path, $body ) {
		$path = str_replace( $this->ga_api_url, '', $path );
		return $this->make_request(
			$path,
			'ga',
			$body
		);
	}

	/**
	 * Get a list of Google Analytics accounts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $body Body information.
	 *
	 * @return array|WP_Error
	 */
	public function get_analytics_accounts( $body ) {
		return $this->make_request(
			'management/accounts',
			'ga',
			$body
		);
	}

	/**
	 * Get a list of tag manager accounts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $body Body information.
	 *
	 * @return array|WP_Error
	 */
	public function get_tag_manager_account( $body ) {
		return $this->make_request(
			'accounts',
			'gtm',
			$body
		);
	}

	/**
	 * Get a list of tag manager containers.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body Body information.
	 * @param string $account_id Account to retrieve containers for.
	 *
	 * @return array|WP_Error
	 */
	public function get_tag_manager_containers( $body, $account_id ) {
		return $this->make_request(
			sprintf( 'accounts/%s/containers', $account_id ),
			'gtm',
			$body
		);
	}

	/**
	 * Get a list of tag manager workspaces.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body Body information.
	 * @param string $path Account path to request.
	 *
	 * @return array|WP_Error
	 */
	public function get_tag_manager_workspaces( $body, $path ) {
		return $this->make_request(
			sprintf( '%s/workspaces', $path ),
			'gtm',
			$body
		);
	}

	/**
	 * Get a list of tag manager variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body      Body information.
	 * @param string $path      Account path to request.
	 * @param string $workspace The workspace to retrieve variables for.
	 *
	 * @return array|WP_Error
	 */
	public function get_tag_manager_variables( $body, $path, $workspace ) {
		return $this->make_request(
			sprintf( '%s/workspaces/%s/variables', $path, $workspace ),
			'gtm',
			$body
		);
	}

	/**
	 * Create a variable within Tag Manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body      Body information.
	 * @param string $path      Account path to request.
	 * @param string $workspace The workspace to retrieve variables for.
	 *
	 * @return array|WP_Error
	 */
	public function save_google_tag_manager_variable( $body, $path, $workspace ) {
		return $this->make_request(
			sprintf( '%s/workspaces/%s/variables', $path, $workspace ),
			'gtm',
			$body,
			'POST'
		);
	}

	/**
	 * Create a trigger within Tag Manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body      Body information.
	 * @param string $path      Account path to request.
	 * @param string $workspace The workspace to retrieve variables for.
	 *
	 * @return array|WP_Error
	 */
	public function create_google_tag_manager_trigger( $body, $path, $workspace ) {
		return $this->make_request(
			sprintf( '%s/workspaces/%s/triggers', $path, $workspace ),
			'gtm',
			$body,
			'POST'
		);
	}

	/**
	 * Create a tag within Tag Manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body      Body information.
	 * @param string $path      Account path to request.
	 * @param string $workspace The workspace to retrieve variables for.
	 *
	 * @return array|WP_Error
	 */
	public function create_tag_manager_tag( $body, $path, $workspace ) {
		return $this->make_request(
			sprintf( '%s/workspaces/%s/tags', $path, $workspace ),
			'gtm',
			$body,
			'POST'
		);
	}

	/**
	 * Create a new container version within Tag Manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body      Body information.
	 * @param string $path      Account path to request.
	 * @param string $workspace The workspace to retrieve variables for.
	 *
	 * @return array|WP_Error
	 */
	public function save_update_tag_manager_version( $body, $path, $workspace ) {
		return $this->make_request(
			sprintf( '%s/workspaces/%s:create_version', $path, $workspace ),
			'gtm',
			$body,
			'POST'
		);
	}

	/**
	 * Publish container within Tag Manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $body      Body information.
	 * @param string $path      Account path to request.
	 * @param string $version   The version to publish.
	 *
	 * @return array|WP_Error
	 */
	public function publish_google_tag_manager_container( $body, $path, $version ) {
		return $this->make_request(
			sprintf( '%s/versions/%s:publish', $path, $version ),
			'gtm',
			$body,
			'POST'
		);
	}
}
