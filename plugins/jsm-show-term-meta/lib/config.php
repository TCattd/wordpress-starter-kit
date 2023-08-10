<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'JsmStmConfig' ) ) {

	class JsmStmConfig {

		public static $cf = array(
			'plugin' => array(
				'jsmstm' => array(			// Plugin acronym.
					'version'     => '3.1.1',	// Plugin version.
					'slug'        => 'jsm-show-term-meta',
					'base'        => 'jsm-show-term-meta/jsm-show-term-meta.php',
					'text_domain' => 'jsm-show-term-meta',
					'domain_path' => '/languages',
					'admin_l10n'  => 'jsmstmAdminPageL10n',
				),
			),
		);

		public static function get_version( $add_slug = false ) {

			$info =& self::$cf[ 'plugin' ][ 'jsmstm' ];

			return $add_slug ? $info[ 'slug' ] . '-' . $info[ 'version' ] : $info[ 'version' ];
		}

		public static function get_config() {

			return self::$cf;
		}

		public static function set_constants( $plugin_file ) {

			if ( defined( 'JSMSTM_VERSION' ) ) {	// Define constants only once.

				return;
			}

			$info =& self::$cf[ 'plugin' ][ 'jsmstm' ];

			$nonce_key = defined( 'NONCE_KEY' ) ? NONCE_KEY : '';

			/*
			 * Define fixed constants.
			 */
			define( 'JSMSTM_FILEPATH', $plugin_file );
			define( 'JSMSTM_NONCE_NAME', md5( $nonce_key . var_export( $info, $return = true ) ) );
			define( 'JSMSTM_PLUGINBASE', $info[ 'base' ] );	// Example: jsm-show-term-meta/jsm-show-term-meta.php.
			define( 'JSMSTM_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_file ) ) ) );
			define( 'JSMSTM_PLUGINSLUG', $info[ 'slug' ] );	// Example: jsm-show-term-meta.
			define( 'JSMSTM_URLPATH', trailingslashit( plugins_url( '', $plugin_file ) ) );
			define( 'JSMSTM_VERSION', $info[ 'version' ] );
		}

		/*
		 * Load all essential library files.
		 *
		 * Avoid calling is_admin() here as it can be unreliable this early in the load process - some plugins that operate
		 * outside of the standard WordPress load process do not define WP_ADMIN as they should (which is required by
		 * is_admin() this early in the WordPress load process).
		 */
		public static function require_libs( $plugin_file ) {

			require_once JSMSTM_PLUGINDIR . 'lib/com/util.php';
			require_once JSMSTM_PLUGINDIR . 'lib/com/util-metabox.php';
			require_once JSMSTM_PLUGINDIR . 'lib/com/util-wp.php';
			require_once JSMSTM_PLUGINDIR . 'lib/compat.php';
			require_once JSMSTM_PLUGINDIR . 'lib/script.php';
			require_once JSMSTM_PLUGINDIR . 'lib/term.php';

			add_filter( 'jsmstm_load_lib', array( __CLASS__, 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $success = false, $filespec = '', $classname = '' ) {

			if ( false !== $success ) {

				return $success;
			}

			if ( ! empty( $classname ) ) {

				if ( class_exists( $classname ) ) {

					return $classname;
				}
			}

			if ( ! empty( $filespec ) ) {

				$file_path = JSMSTM_PLUGINDIR . 'lib/' . $filespec . '.php';

				if ( file_exists( $file_path ) ) {

					require_once $file_path;

					if ( empty( $classname ) ) {

						return SucomUtil::sanitize_classname( 'jsmstm' . $filespec, $allow_underscore = false );
					}

					return $classname;
				}
			}

			return $success;
		}
	}
}
