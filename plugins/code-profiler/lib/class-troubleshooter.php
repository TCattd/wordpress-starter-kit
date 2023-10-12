<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined('ABSPATH') ) { die('Forbidden'); }

// =====================================================================

class CP_Troubleshooter {

	/********************************************************************
	 * Array to handle all results
	 */
	private $buffer = [];

	/********************************************************************
	 * Initialize.
	 */
	public function __construct() {

		$this->get_system_info('sysinfo');
		$this->cp_info('code-profiler');
		$this->function_exists('code-profiler');
		$this->check_wpdb('wpdb');
		$this->check_proxy('proxy');
		$this->get_all_plugins('plugins');
		$this->get_theme('themes');
		$this->get_cp_config();

		echo esc_textarea( print_r( $this->buffer, true ) );
	}

	/********************************************************************
	 * Retrieve system info:
	 * * PHP version
	 * * PHP SAPI
	 * * HTTP server
	 * * Opcode cache
	 * * PHP directives (time limit, memory available, temp dir etc)
	 * * PHP last error
	 * * WordPress
	 */
	private function get_system_info( $key ) {

		$this->buffer[ $key ]['OS'] = php_uname();

		if (! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$this->buffer[ $key ]['HTTP server'] = $_SERVER['SERVER_SOFTWARE'];
		} else {
			$this->buffer[ $key ]['HTTP server'] = 'N/A';
		}

		$this->buffer[ $key ]['PHP'] = strtoupper( PHP_SAPI ) .' '. PHP_VERSION;

		if ( extension_loaded('Zend OPcache') ) {
			$this->buffer[ $key ]['Opcode cache'] = sprintf(
				'Zend OPcache (%s)', ini_get('opcache.enable') ? 'enabled':'disabled'
			);
		} elseif ( extension_loaded('wincache') ) {
			$this->buffer[ $key ]['Opcode cache'] = sprintf(
				'wincache (%s)', ini_get('wincache.fcenabled') ? 'enabled':'disabled'
			);
		}

		$this->buffer[ $key ]['Memory limit']			= ini_get('memory_limit');
		$this->buffer[ $key ]['Peak limit']				= number_format( memory_get_peak_usage() );
		$this->buffer[ $key ]['Max execution time']	= ini_get('max_execution_time') .'s';
		$this->buffer[ $key ]['Disabled functions']	= ini_get('disable_functions');
		$this->buffer[ $key ]['Display errors']		= ini_get('display_errors');

		$tmp = ini_get('sys_temp_dir');
		if (! empty( $tmp ) ) {
			if ( is_writable( $tmp ) ) {
				$tmp .= ' (writable)';
			} else {
				$tmp .= ' (not writable!)';
			}
		}
		$tmp = $this->shorten_path( $tmp );
		$this->buffer[ $key ]['Temp directory']	= $tmp;
		$this->buffer[ $key ]['Log errors']			= ini_get('log_errors');

		$error_log	= ini_get('error_log');
		$filesize	= 0;
		if ( is_file( $error_log ) ) {
			$filesize = filesize( $error_log );
		}
		$this->buffer[ $key ]['Error log']	= "$error_log (". number_format( $filesize ) ." bytes)";

		$this->buffer[ $key ]['Last error']	= error_get_last();
		if ( isset( $this->buffer[ $key ]['Last error']['file'] ) ) {
			$this->buffer[ $key ]['Last error']['file'] = $this->shorten_path( $this->buffer[ $key ]['Last error']['file'] );
		}

		// WordPress
		if ( defined('WP_MEMORY_LIMIT') ) {
			$this->buffer[ $key ]['WordPress']['WP_MEMORY_LIMIT']	= WP_MEMORY_LIMIT;
		} else {
			$this->buffer[ $key ]['WordPress']['WP_MEMORY_LIMIT']	= 'N/A';
		}
		if ( defined('WP_MAX_MEMORY_LIMIT') ) {
			$this->buffer[ $key ]['WordPress']['WP_MAX_MEMORY_LIMIT']	= WP_MAX_MEMORY_LIMIT;
		} else {
			$this->buffer[ $key ]['WordPress']['WP_MAX_MEMORY_LIMIT']	= 'N/A';
		}
		global $wp_version;
		$this->buffer[ $key ]['WordPress']['version'] = $wp_version;
		$wp_debug = '';
		if ( defined('WP_DEBUG') ) {
			$wp_debug = WP_DEBUG;
		}
		$this->buffer[ $key ]['WordPress']['WP_DEBUG'] = $wp_debug;
		$wp_debug	= '';
		$filesize	= 0;
		if ( defined('WP_DEBUG_LOG') ) {
			$wp_debug = WP_DEBUG_LOG;
		}
		if ( is_file( $wp_debug ) ) {
			$filesize	= filesize( $error_log );
			$wp_debug	= $this->shorten_path( $wp_debug ) ." (". number_format( $filesize ) ." bytes)";
		}
		$this->buffer[ $key ]['WordPress']['WP_DEBUG_LOG'] = $wp_debug;

	}

	/********************************************************************
	 * Verify if some important PHP functions are available.
	 */
	private function function_exists( $key ) {

		$required_functions = [
			'register_shutdown_function'	=> 'shutdown',
			'register_tick_function'		=> 'tick',
			'stream_wrapper_unregister'	=> 'unregister',
			'stream_wrapper_register'		=> 'register',
			'stream_wrapper_restore'		=> 'restore'
		];

		foreach ( $required_functions as $function => $value ) {
			$this->buffer[ $key ][ $value ] = function_exists( $function );
		}
	}


	/********************************************************************
	 * Return the version and type (free/pro) of Code Profiler.
	 */
	private function cp_info( $key ) {

		if ( defined('CODE_PROFILER_PRO_VERSION') ) {
			$this->buffer[ $key ]['slug']		= 'code-profiler-pro';
			$this->buffer[ $key ]['version']	=	CODE_PROFILER_PRO_VERSION;

		} elseif ( defined('CODE_PROFILER_VERSION') ) {
			$this->buffer[ $key ]['slug']		= 'code-profiler';
			$this->buffer[ $key ]['version']	=	CODE_PROFILER_VERSION;

		} else {
			exit('Error: Code Profiler is not active. Please activate it and run this script again.');

		}

		// Data folder must be writable
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] .'/'. $this->buffer[ $key ]['slug'];

		$this->buffer[ $key ]['data_dir']['path']			= $this->shorten_path( $dir );
		$this->buffer[ $key ]['data_dir']['exists']		= file_exists( $dir );
		$this->buffer[ $key ]['data_dir']['writable']	= is_writable( $dir );
	}


	/********************************************************************
	 * Check if there are subclasses of the wpdb class and
	 * look for wp-content/db.php
	 */
	function check_wpdb( $key ) {

		foreach( get_declared_classes() as $class ) {
			$reflected = new ReflectionClass( $class );
			if ( $reflected->isSubclassOf('wpdb') ) {
				$script = $reflected->getFileName();
				$this->buffer[ $key ]['extends'][$class] = $this->shorten_path( $script );
			}
		}
		$db_php = $this->shorten_path( WP_CONTENT_DIR .'/db.php');
		$this->buffer[ $key ][ $db_php ] = file_exists( WP_CONTENT_DIR .'/db.php');
	}


	/********************************************************************
	 * Check for advanced cache
	 */
	private function check_cache( $key ) {

		$this->buffer[ $key ]['cache'] = defined('WP_CACHE') && file_exists(WP_CONTENT_DIR . '/advanced-cache.php');
	}


	/********************************************************************
	 * Check for reverse proxy or CDN
	 */
	private function check_proxy( $key ) {

		$this->buffer[ $key ] = [];
		$proxies = [
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_INCAP_CLIENT_IP'
		];

		foreach( $proxies as $proxy ) {
			if (! empty( $_SERVER[ $proxy ] ) ) {
				$this->buffer[ $key ][ $proxy ] = 1;
			}
		}

	}


	/********************************************************************
	 * Retrieve the list of all plugins and sort them
	 * (active or disabled).
	 */
	private function get_all_plugins( $key ) {

		if (! function_exists('get_plugins') ) {
			require_once ABSPATH .'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		foreach( $plugins as $k => $v ) {
			if ( $slug = substr( $k, 0, strpos( $k, '/') ) ) {
				$name = 'N/A'; $version = 'N/A'; $uri = 'N/A';
				if (! empty( $v['Name'] ) ) {
					$name = $v['Name'];
				}
				if (! empty( $v['Version'] ) ) {
					$version = $v['Version'];
				}
				if (! empty( $v['PluginURI'] ) ) {
					$uri = $v['PluginURI'];
				}
				if ( is_plugin_active( $k ) ) {
					$this->buffer[ $key ]['active'][ $slug ] = "$name - $version - $uri";

				} else {
					$this->buffer[ $key ]['inactive'][ $slug ] = "$name - $version - $uri";
				}
			}
		}

		$mu_plugins = get_mu_plugins();
		foreach( $mu_plugins as $k => $v ) {
			$name = 'N/A'; $version = 'N/A'; $uri = 'N/A';
			if (! empty( $v['Name'] ) ) {
				$name = $v['Name'];
			}
			if (! empty( $v['Version'] ) ) {
				$version = $v['Version'];
			}
			if (! empty( $v['PluginURI'] ) ) {
				$uri = $v['PluginURI'];
			}
			$this->buffer[ $key ]['mu-plugins'][ $k ] = "$name - $version - $uri";
		}

	}


	/********************************************************************
	 * Retrieve the active theme.
	 */
	private function get_theme( $key ) {

		$themes = wp_get_themes();
		$active = get_option('template');
		foreach( $themes as $k => $v ) {
			$name = 'N/A'; $version = 'N/A';
			if ( $v->Name ) {
				$name = $v->Name;
			}
			if ( $v->Version ) {
				$version = $v->Version;
			}
			if ( $k == $active ) {
				$this->buffer[ $key ]['active'][ $k ] = "$name - $version";
			} else {
				$this->buffer[ $key ]['inactive'][ $k ] = "$name - $version";
			}
		}
	}


	/********************************************************************
	 * Retrieve Code Profiler's configuration
	 */
	private function get_cp_config() {

		$cp_options = get_option('code-profiler');

		$list = [
			'hide_empty_value',
			'warn_composer',
			'enable_wpcli',
			'disable_wpcron',
			// 'disable_db-php',
			'http_response',
			// 'backtrace_limit',
			'accuracy',
			'buffer'
		];
		foreach( $list as $element ) {
			$this->buffer['options'][ $element ] = isset( $cp_options[ $element ] )? $cp_options[ $element ]:0;
		}
	}

	/********************************************************************
	 * Remove the ABSPATH from a path
	 */
	private function shorten_path( $path ) {

		return str_replace( ABSPATH, '', $path );
	}

}

// =====================================================================
// EOF
