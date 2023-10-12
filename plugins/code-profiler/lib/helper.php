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
// Various constants

$upload_dir = wp_upload_dir();
define('CODE_PROFILER_UPLOAD_DIR', $upload_dir['basedir'] .'/code-profiler');
define('CODE_PROFILER_LOG', CODE_PROFILER_UPLOAD_DIR .'/log.php');
define('CODE_PROFILER_TMP_IOSTATS_LOG', 'iostats.tmp');
define('CODE_PROFILER_TMP_SUMMARY_LOG', 'summary.tmp');
define('CODE_PROFILER_TMP_TICKS_LOG', 'ticks.tmp');
define('CODE_PROFILER_TMP_DISKIO_LOG', 'diskio.tmp');
define('CODE_PROFILER_TMP_CONNECTIONS_LOG', 'connections.tmp');
define('CODE_PROFILER_UPDATE_NOTICE', '<div class="updated notice is-dismissible"><p>%s</p></div>');
define('CODE_PROFILER_ERROR_NOTICE', '<div class="error notice is-dismissible"><p>%s</p></div>');
global $wp_version;
if (! defined('CODE_PROFILER_UA') ) { // UA signatures can be user-defined in the wp-config.php
	define ('CODE_PROFILER_UA', [
		esc_html__('Desktop', 'code-profiler') => [
			'Firefox'			=> 'Mozilla/5.0 (Linux x86_64; rv:110.0) Gecko/20100101 Firefox/110.0',
			'Chrome'				=> 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko)'.
										' Chrome/111.0.0.0 Safari/537.36',
			'Edge'				=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML,'.
										' like Gecko) Chrome/111.0.0.0 Safari/537.36 Edg/110.0.1587.63'
		],
		esc_html__('Mobile', 'code-profiler') => [
			'Android Phone'	=> 'Mozilla/5.0 (Android 13; Mobile; rv:68.0) Gecko/68.0 Firefox/110.0',
			'Android Tablet'	=> 'Mozilla/5.0 (Linux; Android 13.0; SAMSUNG-SM-T377A Build/NMF26X)'.
									' AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Mobile Safari/537.36',
			'iPhone'				=> 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15'.
									' (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1',
			'iPad'				=> 'Mozilla/5.0 (iPad; CPU OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15'.
									' (KHTML, like Gecko) GSA/213.0.449417121 Mobile/15E148 Safari/605.1.15'
		],
		esc_html__('Bot', 'code-profiler')    => [
			'Google Bot'		=> 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
			'WordPress'			=> 'Mozilla/5.0 (compatible; CodeProfiler for WordPress/'. $wp_version .
										'; https://code-profiler.com/)'
		]

	] );
}

if (! defined('CODE_PROFILER_MUPLUGIN') ) {
	// MU plugin's name can be defined in the wp-config.php
	define('CODE_PROFILER_MUPLUGIN', '0----code-profiler.php');
}
define('CODE_PROFILER_ACCURACY', [
	1		=> __('Highest (default)', 'code-profiler'),
	5		=> __('High', 'code-profiler'),
	10		=> __('Moderate', 'code-profiler'),
	15		=> __('Low', 'code-profiler'),
	20		=> __('Lowest', 'code-profiler')
]);
// =====================================================================
// Prevent cURL timeout if a plugin changes its timeout options.

function code_profiler_curl_timeout( $handle, $parsed_args, $url ) {

	if ( isset( $_REQUEST['action'] ) &&
		$_REQUEST['action'] == 'codeprofiler_start_profiler') {

		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 300 );
		curl_setopt( $handle, CURLOPT_TIMEOUT, 300 );
	}
}
add_action('http_api_curl', 'code_profiler_curl_timeout', 1000, 3 );

// =====================================================================
// Update version in the DB and check if options must be updated.

function code_profiler_init_update() {

	$cp_options = get_option('code-profiler');
	if ( empty( $cp_options['version'] ) ||
		version_compare( $cp_options['version'], CODE_PROFILER_VERSION, '<') ) {

		$cp_options['version'] = CODE_PROFILER_VERSION;

		// Version 1.1
		if (! isset( $cp_options['enable_wpcli'] ) ) {
			$cp_options['enable_wpcli'] = 1;
		}

		// Version 1.2
		if (! isset( $cp_options['disable_wpcron'] ) ) {
			$cp_options['disable_wpcron'] = 1;
		}

		// Version 1.3.1
		if (! isset( $cp_options['http_response'] ) ) {
			$cp_options['http_response'] = '^(?:3|4|5)\d{2}$';
		}

		// Version 1.5
		if (! isset( $cp_options['accuracy'] ) ) {
			$cp_options['accuracy'] = 1;
		}

		// Update version in the DB
		update_option('code-profiler', $cp_options );
	}

}
add_action('admin_init', 'code_profiler_init_update');

// =====================================================================
// Verify or create our storage folder in the uploads directory.
// Call during activation and access to the plugin.

function code_profiler_check_uploadsdir() {

	if (! file_exists( CODE_PROFILER_UPLOAD_DIR ) ) {
		mkdir( CODE_PROFILER_UPLOAD_DIR, 0755 );
	}
	if (! is_writable( CODE_PROFILER_UPLOAD_DIR ) ) {
		// PHP running as an Apache module?
		chmod( CODE_PROFILER_UPLOAD_DIR, 0777 );
	}
	if (! file_exists( CODE_PROFILER_UPLOAD_DIR .'/index.html') ) {
		touch( CODE_PROFILER_UPLOAD_DIR .'/index.html');
	}
	// For Apache & Litespeed
	if (! file_exists( CODE_PROFILER_UPLOAD_DIR .'/.htaccess') ) {
		file_put_contents(
			CODE_PROFILER_UPLOAD_DIR .'/.htaccess',
			'Require all denied'
		);
	}
	// Make sure there's a MU plugin directory
	if (! is_dir( WPMU_PLUGIN_DIR ) ) {
		wp_mkdir_p( WPMU_PLUGIN_DIR );
	}


	if (! file_exists( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN ) ) {

		if (! is_writable( WPMU_PLUGIN_DIR ) ) {
			wp_die(
				sprintf(
					__('Error: The MU folder is read only, please make it writable: %s', 'code-profiler'),
					esc_html( WPMU_PLUGIN_DIR ) .'/'
				)
			);
		}

		if (! file_exists( __DIR__ .'/mu.plugin') ) {
			code_profiler_log_error( sprintf(
				esc_html__('Cannot find the MU plugin: %s', 'code-profiler'),
				__DIR__ .'/mu.plugin'
			));
		} else {
			$res = copy( __DIR__ .'/mu.plugin', WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN );
			if ( $res === false ) {
				code_profiler_log_error( sprintf(
					esc_html__('Cannot copy the MU plugin to %s', 'code-profiler'),
					WPMU_PLUGIN_DIR
				));
			}
		}
	} else {
		// Update MU plugin it if needed
		if ( md5_file( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN ) !== md5_file( __DIR__ .'/mu.plugin') ) {
			copy( __DIR__ .'/mu.plugin', WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN );
		}
	}

	// Log file
	if (! file_exists( CODE_PROFILER_LOG ) ) {
		file_put_contents( CODE_PROFILER_LOG, "<?php exit; ?>\n"	);
	}

}

// =====================================================================
// Check the PHP memory limit and return a suggested size
// for the profiler's buffer.

function code_profiler_suggested_memory() {

	$memory_limit = ini_get('memory_limit');

	if ('-1' == $memory_limit ) {
		// Return max size
		return 10;
	}

	if ( preg_match('/^(\d+)([PTGMK])$/i', $memory_limit, $match ) ) {
		$bytes = (int) $match[1];
		switch ( strtoupper( $match[2] ) ) {
			case 'P':
				$bytes *= 1024;
			case 'T':
				$bytes *= 1024;
			case 'G':
				$bytes *= 1024;
			case 'M':
				$bytes *= 1024;
			case 'K':
				$bytes *= 1024;
		}
		// 256 MB
		if ( $bytes >= 268435456 ) {
			return 10;
		// 128 MB
		} elseif ( $bytes >= 134217728 ) {
			return 7;
		// 64 MB
		} elseif ( $bytes >= 67108864 ) {
			return 4;
		// <64 MB
		} else {
			return 1;
		}
	}
	// Don't know :/
	return 5;
}

// =====================================================================
// Create the default options.

function code_profiler_default_options() {

	$buffer = code_profiler_suggested_memory();

	$cp_options = [
		'show_paths' 			=> 'relative',
		'display_name'			=> 'full',
		'truncate_name'		=> 30,
		'chart_type'			=> 'x',
		'chart_max_plugins'	=> 25,
		'hide_empty_value'	=> 1,
		'table_max_rows'		=> 30,
		'warn_composer'		=> 1,
		'enable_wpcli'			=> 1,
		'disable_wpcron'		=> 1,
		'http_response'		=> '^(?:3|4|5)\d{2}$',
		'accuracy'				=> 1,
		'buffer'					=> (int) $buffer
	];

	update_option('code-profiler', $cp_options );

}

// ===================================================================== 2023-06-14
// Create a profile name.

function code_profiler_profile_name() {

	return date('Y-m-d_') . substr( time(), 4 );
}

// ===================================================================== 2023-06-14
// Disable PHP display_errors so that notice, warning and error messages
// don't show up in the AJAX response.

function code_profiler_hide_errors() {

	ini_set('display_errors', 0 );
}

// =====================================================================
// Disable opcode cache.

function code_profiler_disable_opcode() {

	try {
		if (extension_loaded('Zend OPcache')) {
			ini_set('opcache.enable', 0);
		} elseif ( extension_loaded('wincache') ) {
			ini_set('wincache.fcenabled', 0);
		}
		set_time_limit(0);

	} catch ( Exception $e ) { }

	$cp_options = get_option('code-profiler');
	if (! empty( $cp_options['disable_wpcron'] ) ) {
		if (! defined('DISABLE_WP_CRON') ) {
			define('DISABLE_WP_CRON', true );
		}
	}

}
// =====================================================================
// Verify the security key when running the profile.

function code_profiler_verify_key() {

	$response = [
		'status'		=> 'error',
		'message'	=> __('Security keys do not match. Reload the page and try again (%s)', 'code-profiler')
	];

	$cp_options = get_option('code-profiler');

	if ( empty( $cp_options['hash'] ) ) {
		$response['message'] = sprintf( $response['message'], '#1');

	} else {
		if ( empty( $_REQUEST['profiler_key'] ) ) {
			$response['message'] = sprintf( $response['message'], '#2');

		} else {
			if ( $cp_options['hash'] == sha1( $_REQUEST['profiler_key'] ) ) {
				return;
			} else {
				$response['message'] = sprintf( $response['message'], '#3');
			}
		}
	}
	wp_send_json( $response );

}

// =====================================================================
// Write message to the log.
// Log level can be a combination of INFO (1), WARN (2), ERROR (4)
// and DEBUG (8).

function code_profiler_write2log( $message, $level, $create ) {

	if ( empty( $create ) ) {
		file_put_contents(
			CODE_PROFILER_LOG,
			time() ."~~$level~~$message\n",
			FILE_APPEND
		);
	} else {
		file_put_contents(
			CODE_PROFILER_LOG,
			time() ."~~$level~~$message\n"
		);
	}
}

function code_profiler_log_info(  $string, $create = 0 ) {
	code_profiler_write2log( $string, 1, $create );
}
function code_profiler_log_warn(  $string, $create = 0 ) {
	code_profiler_write2log( $string, 2, $create );
}
function code_profiler_log_error( $string, $create = 0 ) {
	code_profiler_write2log( $string, 4, $create );
}
function code_profiler_log_debug( $string, $create = 0 ) {
	code_profiler_write2log( $string, 8, $create );
}

// =====================================================================
// Verify if a profile exists and return its full path.

function code_profiler_get_profile_path( $id, $type = 'slugs') {

	$return = false;

	if (! empty( $id ) && preg_match('/^\d{10}\.\d+$/', $id ) ) {
		$glob = glob( CODE_PROFILER_UPLOAD_DIR ."/$id.*.$type.profile" );
		if ( is_array( $glob ) && ! empty( $glob[0] ) ) {
			if ( preg_match( "`/$id\.(.+?).$type.profile$`", $glob[0], $match ) ) {

				return CODE_PROFILER_UPLOAD_DIR. "/$id.{$match[1]}";
			}
		}
	}
	return false;
}

// =====================================================================
// Open, parse and return the content of a profile file.

function code_profiler_get_profile_data( $file, $type = 'slugs') {

	$buffer = [];

	$fh = fopen( "$file.$type.profile", 'rb');

	if ( $fh === false ) {
		$err = sprintf(
			CODE_PROFILER_ERROR_NOTICE,
			sprintf(
				esc_html__('Unable to open profile file: %s.', 'code-profiler'),
				esc_html( "$file.$type.profile" )
			)
		);
		$buffer['error'] = $err;
		return $buffer;
	}

	while (! feof( $fh ) ) {
		$buffer[] = fgetcsv( $fh, 1000, "\t" );
	}

	fclose( $fh );

	if ( empty( $buffer ) ) {
		$err = sprintf(
			CODE_PROFILER_ERROR_NOTICE,
			sprintf(
				esc_html__('Profile file is empty or corrupted: %s.', 'code-profiler'),
				esc_html( "$file.$type.profile" )
			)
		);
		$buffer['error'] = $err;
		return $buffer;
	}

	// Get rid of the empty field created by fgetcsv
	return array_filter( $buffer );
}

// =====================================================================
// Exit with a json-encoded error message except if we're running
// from WP CLI

function code_profiler_wp_send_json( $response ) {

	if ( defined('WP_CLI') && WP_CLI ) {
		WP_CLI::error( $response['message'] );
	}
	wp_send_json( $response );
}

// =====================================================================
// Retrieve the summary stats for a given profile.

function code_profiler_getsummarystats( $profile_path, $type = 'html') {

	if ( $type == 'html') {
		$open		= '<ul><li>&#10148; ';
		$div		= '</li><li>&#10148; ';
		$close	= '</li></ul>';
	} else {
		// WP-CLI
		$open		= " \u{27A4} ";
		$div		= "\n \u{27A4} ";
		$close	= "\n\n";
	}

	if (! file_exists( "$profile_path.summary.profile" ) ) {
		return false;
	}
	$decode	= json_decode( file_get_contents( "$profile_path.summary.profile" ) );

	$string	= $open;

	if ( empty( $decode->items ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = (int) --$decode->items;
	}
	$string .= sprintf(
		__('%s plugins and 1 theme', 'code-profiler'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->time ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( $decode->time, 4 );
	}
	$string .= sprintf(
		__('Execution time: %ss', 'code-profiler'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->memory ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( $decode->memory, 2);
	}
	$string .= sprintf(
		__('Peak memory: %s MB', 'code-profiler'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->io ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( $decode->io );
	}
	$string .= sprintf(
		__('File I/O operations: %s', 'code-profiler'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->queries ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( $decode->queries );
	}
	$string .= sprintf(
		__('SQL queries: %s', 'code-profiler'),
		$tmp
	);

	$string .= $close;

	return $string;
}

// =====================================================================
// Remove *tmp files left after an HTTP error (4xx or 5xx).

function code_profiler_cleantmpfiles() {

	$glob = glob( CODE_PROFILER_UPLOAD_DIR .'/*.tmp');
	if ( is_array( $glob ) ) {
		$count = 0;
		foreach( $glob as $file ) {
			$count++;
			unlink( $file );
		}
		if ( $count ) {
			code_profiler_log_info( sprintf(
				__('Deleting %s temporary files found in the profiles folder', 'code-profiler'),
				(int) $count
			) );
		}
	}
}

// =====================================================================
// Clear the log if it's bigger than 100KB.

function code_profiler_clearlog() {

	if ( file_exists( CODE_PROFILER_LOG ) && filesize( CODE_PROFILER_LOG ) > 100000 ) {
		file_put_contents( CODE_PROFILER_LOG, "<?php exit; ?>\n"	);
	}
}

// ===================================================================== 2023-06-15
// We don't want to be bothered by other themes/plugins' admin notices.

add_action('admin_head', 'code_profiler_hide_admin_notices');

function code_profiler_hide_admin_notices() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'code-profiler') {
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
		add_filter('admin_footer_text', 'code_profiler_admin_footer');
	}
}

function code_profiler_admin_footer () {
    echo '<span id="footer-thankyou">'.
		sprintf(
			/* Translators: %s are the '<a href="">' and '</a>' anchors */
			esc_html(
				'Thank you for using %sCode Profiler%s.',
				'code-profiler'
			),
			'<a href="https://code-profiler.com" target="_blank">',
			'</a>'
		).
		'</span>';
}

// =====================================================================
// EOF
