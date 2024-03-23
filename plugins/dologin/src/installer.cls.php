<?php
/**
 * Installer class
 *
 * @since 3.5
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Installer extends Instance {
	const TYPE_INSTALL_3RD = 'install_3rd';

	/**
	 * Auto install 3rd
	 *
	 * @since 3.5
	 */
    private function _install_3rd() {
        $this->dash_notifier_install_3rd();

        wp_redirect( $_SERVER[ 'HTTP_REFERER' ] );
		exit;
    }

    /**
	 * Detect if the plugin is active or not
	 *
	 * @since  1.0
	 */
	public function dash_notifier_is_plugin_active( $plugin ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugin_path = $plugin . '/' . $plugin . '.php';

		return is_plugin_active( $plugin_path );
	}

	/**
	 * Detect if the plugin is installed or not
	 *
	 * @since  1.0
	 */
	public function dash_notifier_is_plugin_installed( $plugin ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugin_path = $plugin . '/' . $plugin . '.php';

		$valid = validate_plugin( $plugin_path );

		return ! is_wp_error( $valid );
	}

	/**
	 * Grab a plugin info from WordPress
	 *
	 * @since  1.0
	 */
	public function dash_notifier_get_plugin_info( $slug ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$result = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Install the 3rd party plugin
	 *
	 * @since  1.0
	 */
	public function dash_notifier_install_3rd() {
		! defined( 'SILENCE_INSTALL' ) && define( 'SILENCE_INSTALL', true );

		$slug = ! empty( $_GET[ 'plugin' ] ) ? $_GET[ 'plugin' ] : false;

		// Check if plugin is installed already
		if ( ! $slug || $this->dash_notifier_is_plugin_active( $slug ) ) {
			return;
		}

		/**
		 * @see wp-admin/update.php
		 */
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/misc.php';

		$plugin_path = $slug . '/' . $slug . '.php';

		if ( ! $this->dash_notifier_is_plugin_installed( $slug ) ) {
			$plugin_info = $this->dash_notifier_get_plugin_info( $slug );
			if ( ! $plugin_info ) {
				return;
			}
			// Try to install plugin
			try {
				ob_start();
				$skin = new \Automatic_Upgrader_Skin();
				$upgrader = new \Plugin_Upgrader( $skin );
				$result = $upgrader->install( $plugin_info->download_link );
				ob_end_clean();
			} catch ( \Exception $e ) {
				return;
			}
		}

		if ( ! is_plugin_active( $plugin_path ) ) {
			activate_plugin( $plugin_path );
		}

	}

	/**
	 * Handler
	 *
	 * @since  3.5
	 */
	public function handler() {
		$type = Router::verify_type();

		switch ( $type ) {
			case self::TYPE_INSTALL_3RD:
				$this->_install_3rd();
				break;

			default:
				break;
		}
	}
}