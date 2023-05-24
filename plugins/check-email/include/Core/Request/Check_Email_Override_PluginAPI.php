<?php namespace CheckEmail\Core\Request;

use CheckEmail\Addon\API\EDDUpdater;
use CheckEmail\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Override WordPress Plugin API.
 * This is already done by EDD_SL_Plugin_Updater for Active add-on
 * and this class does it for all in active or yet to be installed add-ons.
 */
class Check_Email_Override_PluginAPI implements Loadie {

	public function load() {
		add_action( 'admin_init', array( $this, 'setup_updaters_for_inactive_addons' ) );

		add_filter( 'plugins_api_result', array( $this, 'add_version_to_plugin_api_response' ), 100, 3 );
	}

	public function setup_updaters_for_inactive_addons() {
		$check_email = wpchill_check_email();
		$licenser  = $check_email->get_licenser();

		if ( is_null( $licenser ) ) {
			return;
		}

		$inactive_addons = $licenser->get_addon_list()->get_inactive_addons();

		foreach ( $inactive_addons as $inactive_addon ) {
			$license_key = $licenser->get_addon_license_key( $inactive_addon->name );

			$updater = new EDDUpdater(
				$check_email->get_store_url(),
				$inactive_addon->file,
				array(
					'version'   => $inactive_addon->get_version(),
					'license'   => $license_key,
					'item_name' => $inactive_addon->name,
					'author'    => $inactive_addon->author,
				)
			);

			$licenser->add_updater( $updater );
		}
	}
        
	public function add_version_to_plugin_api_response( $response, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $response;
		}

		if ( ! isset( $args->slug ) || ( substr( $args->slug, 0, 10 ) != 'check-email-log-' ) ) {
			return $response;
		}

		if ( isset( $response->version ) ) {
			return $response;
		}

		if ( ! isset( $response->new_version ) ) {
			return $response;
		}

		$response->version = $response->new_version;

		return $response;
	}
}
