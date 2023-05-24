<?php

namespace Gravity_Forms\Gravity_Forms_Google_Analytics;

defined( 'ABSPATH' ) || die();

use GFFormsModel;

/**
 * Gravity Forms Google Analytics Measurement Protocol.
 *
 * @since     1.0.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2019, Rocketgenius
 */
class GF_Google_Analytics_Measurement_Protocol {
	/**
	 * The Endpoint for the Measurement Protocol
	 *
	 * @since 1.0.0
	 * @var string $endpoint The Measurement Protocol endpoint.
	 */
	private $endpoint = 'https://www.google-analytics.com/collect';

	/**
	 * The Client ID for the Measurement Protocol
	 *
	 * @since 1.0.0
	 * @var string $cid The Client ID.
	 */
	private $cid = '';

	/**
	 * The Tracking ID for the Measurement Protocol
	 *
	 * @since 1.0.0
	 * @var string $tid Tracking ID (UA-XXXX-YY).
	 */
	private $tid = '';

	/**
	 * The Measurement Protocol version
	 *
	 * @since 1.0.0
	 * @var string $v Protocol version.
	 */
	private $v = 1;

	/**
	 * The Measurement Protocol hit type
	 *
	 * @since 1.0.0
	 * @var string $t Hit Type.
	 */
	private $t = 'event';

	/**
	 * The event category
	 *
	 * @since 1.0.0
	 * @var string $ec Event category.
	 */
	private $ec = '';

	/**
	 * The event action
	 *
	 * @since 1.0.0
	 * @var string $ea Event action.
	 */
	private $ea = '';

	/**
	 * The event label
	 *
	 * @since 1.0.0
	 * @var string $el Event label.
	 */
	private $el = '';

	/**
	 * The event value
	 *
	 * @since 1.0.0
	 * @var string $ev Event value.
	 */
	private $ev = '';

	/**
	 * The document path
	 *
	 * @since 1.0.0
	 * @var string $dp The document path.
	 */
	private $dp = '';

	/**
	 * The document location
	 *
	 * @since 1.0.0
	 * @var string $dl The document location.
	 */
	private $dl = '';

	/**
	 * The document title
	 *
	 * @since 1.0.0
	 * @var string $dt The document title.
	 */
	private $dt = '';

	/**
	 * The document host name
	 *
	 * @since 1.0.0
	 * @var string $dh The document host name.
	 */
	private $dh = '';

	/**
	 * The IP Address of the user.
	 *
	 * @since 1.0.0
	 * @var string $uip The IP Address of the user.
	 */
	private $uip = '';

	/**
	 * Init function. Attempts to get the client's CID
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->cid = $this->create_client_id();
	}

	/**
	 * Sets the User's IP
	 *
	 * @since 1.0.0
	 *
	 * @param string $user_ip The user's IP address.
	 */
	public function set_user_ip_address( $user_ip ) {
		$this->uip = $user_ip;
	}

	/**
	 * Sets the event category
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_category The event category for conversions.
	 */
	public function set_event_category( $event_category ) {
		$this->ec = $event_category;
	}

	/**
	 * Sets the event action
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_action The event action for conversions.
	 */
	public function set_event_action( $event_action ) {
		$this->ea = $event_action;
	}

	/**
	 * Sets the event label
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_label The event label for conversions.
	 */
	public function set_event_label( $event_label ) {
		$this->el = $event_label;
	}

	/**
	 * Sets the event value
	 *
	 * @since 1.0.0
	 *
	 * @param string $event_value The event value for conversions.
	 */
	public function set_event_value( $event_value ) {
		$this->ev = $event_value;
	}

	/**
	 * Sets the document path
	 *
	 * @since 1.0.0
	 *
	 * @param string $document_path The path of the document.
	 */
	public function set_document_path( $document_path ) {
		$this->dp = $document_path;
	}

	/**
	 * Sets the document host
	 *
	 * @since 1.0.0
	 *
	 * @param string $document_host The host of the document.
	 */
	public function set_document_host( $document_host ) {
		$this->dh = $document_host;
	}

	/**
	 * Sets the document location
	 *
	 * @since 1.0.0
	 *
	 * @param string $document_location The location of the document.
	 */
	public function set_document_location( $document_location ) {
		$this->dl = $document_location;
	}

	/**
	 * Sets the document title
	 *
	 * @since 1.0.0
	 *
	 * @param string $document_title The document title for the page being submitted.
	 */
	public function set_document_title( $document_title ) {
		$this->dt = $document_title;
	}

	/**
	 * Sends the data to the measurement protocol
	 *
	 * @since 1.0.0
	 *
	 * @param string $ua_code The UA code to send the event to.
	 */
	public function send( $ua_code ) {

		// Get variables in wp_remote_post body format.
		$mp_vars = array(
			'cid',
			'v',
			't',
			'ec',
			'ea',
			'el',
			'ev',
			'dp',
			'dl',
			'dt',
			'dh',
			'uip',
		);
		$mp_body = array(
			'tid' => $ua_code,
		);
		foreach ( $mp_vars as $index => $mp_var ) {
			if ( empty( $this->{$mp_vars[ $index ]} ) ) {
				// Empty params cause the payload to fail in testing.
				continue;
			}
			$mp_body[ $mp_var ] = $this->{$mp_vars[ $index ]};
		}
		// Add Payload.
		$payload = add_query_arg( $mp_body, $this->endpoint );

		// Perform the POST.
		return wp_remote_get( esc_url_raw( $payload ) );

	}


	/**
	 * Create a GUID on Client specific values
	 *
	 * @since 1.0.0
	 *
	 * @return string New Client ID.
	 */
	private function create_client_id() {

		// collect user specific data.
		if ( isset( $_COOKIE['_ga'] ) ) {

			$ga_cookie = explode( '.', sanitize_text_field( wp_unslash( $_COOKIE['_ga'] ) ) );
			if ( isset( $ga_cookie[2] ) ) {

				// check if uuid.
				if ( $this->check_uuid( $ga_cookie[2] ) ) {

					// uuid set in cookie.
					return $ga_cookie[2];
				} elseif ( isset( $ga_cookie[2] ) && isset( $ga_cookie[3] ) ) {

					// google default client id.
					return $ga_cookie[2] . '.' . $ga_cookie[3];
				}
			}
		}

		// nothing found - return random uuid client id.
		return GFFormsModel::get_uuid();
	}

	/**
	 * Check if is a valid uuid v4
	 *
	 * @since 1.0.0
	 *
	 * @param string $uuid The UUID to check.
	 *
	 * @return bool If the UUID is valid
	 */
	private function check_uuid( $uuid ) {
		return preg_match( '#^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$#i', $uuid );
	}
}
