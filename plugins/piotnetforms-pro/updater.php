<?php

class Piotnetforms_Updater {

	private $check_info_url = 'https://piotnetforms.com/connect/v1/get_info.php';

	private $plugin_slug;

	private $slug;

	private $license_key;

	public function __construct( $plugin_slug, $license_key = '' ) {
		$this->license_key = $license_key;

		$this->plugin_slug = $plugin_slug;
		list ($t1, $t2)    = explode( '/', $plugin_slug );
		$this->slug        = str_replace( '.php', '', $t2 );

		// define the alternative API for updating checking
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );

		// Define the alternative response for information checking
		add_filter( 'plugins_api', [ &$this, 'check_info' ], 10, 3 );
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		if ( $this->plugin_slug === 'piotnetforms-pro/piotnetforms-pro.php' ) {
			// Get the remote version
			$data = $this->get_data_from_remote( 'check_update' );
			if ( !empty( $data ) ) {
				$info = $data['info'];
				if ( version_compare( PIOTNETFORMS_PRO_VERSION, $info['new_version'], '<' ) ) {
					$obj              = new stdClass();
					$obj->slug        = $this->slug;
					$obj->new_version = $info['new_version'];
					$obj->url         = $info['url'];
					$obj->plugin      = $this->plugin_slug;
					$obj->package     = $info['package'];
					if ( isset( $info['tested'] ) ) {
						$obj->tested = $info['tested'];
					}
					$transient->response[ $this->plugin_slug ] = $obj;
				}
			}
		}

		return $transient;
	}

	public function check_info( $obj, $action, $arg ) {
		if ( ( $action === 'query_plugins' || $action === 'plugin_information' ) && isset( $arg->slug ) && $arg->slug === $this->slug ) {
			$data = $this->get_data_from_remote( 'check_info' );
			if ( ! empty( $data ) ) {
				return $data['info'];
			}
		}
		return $obj;
	}

	private static function get_domain( $url ) {
		return preg_replace( '/^www\./', '', wp_parse_url( $url, PHP_URL_HOST ) );
	}

	public function get_data_from_remote( $action = 'check_info' ) {
		$domain = $this->get_domain( get_option( 'siteurl' ) );

		$params = [
			'body' => [
				'domain'       => $domain,
				'pro_version'  => PIOTNETFORMS_PRO_VERSION,
				'free_version' => PIOTNETFORMS_VERSION,
				'wp_version'   => get_bloginfo( 'version' ),
				'php_version'  => PHP_VERSION,
				'action'       => $action,
				'license_key'  => $this->license_key,
			],
		];

		$request = wp_remote_post( $this->check_info_url, $params );
		if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			return json_decode( $request['body'], true );
		}

		return false;
	}
}
