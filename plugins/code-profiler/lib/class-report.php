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


class CodeProfiler_Report {

	private $last_slug;
	private $last_type;
	private $last_time		= 0;
	private $themes			= [];
	private $plugins			= [];
	private $composer			= [];
	private $summary_list	= [];
	private $cx_buffer		= [];
	private $parsed_data		= 0;
	private $plugins_dir;
	private $themes_dir;
	private $mu_dir;
	private $total_plugins;
	private $total_io;
	private $profile_name;
	private $microtime;
	private $tmp_iostats;
	private $tmp_summary;
	private $tmp_diskio;
	private $tmp_connections;
	private $tmp_ticks;


	/**
	 * Initialize
	 */
	public function __construct( $profile_name, $microtime ) {

		$this->tmp_summary 		= CODE_PROFILER_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_TMP_SUMMARY_LOG;
		$this->tmp_iostats		= CODE_PROFILER_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_TMP_IOSTATS_LOG;
		$this->tmp_ticks   		= CODE_PROFILER_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_TMP_TICKS_LOG;
		$this->tmp_diskio 		= CODE_PROFILER_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_TMP_DISKIO_LOG;
		$this->tmp_connections	= CODE_PROFILER_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_TMP_CONNECTIONS_LOG;
		// Used for naming, not metrics
		$this->microtime = $microtime;

		// Make sure all files are there
		if (! file_exists( $this->tmp_ticks ) ) {
			$cp_error = 1;
		} elseif (! file_exists( $this->tmp_iostats ) ) {
			$cp_error = 2;
		}
		if (! empty( $cp_error ) ) {
			$error = sprintf(
				esc_html__('Cannot create the report: the profiler did not '.
				'generate a data file (#%s). Make sure the following directory '.
				'is writable: %s. If you are using a caching plugin or an '.
				'opcode cache, try to disable it. You may also find more '.
				'details about the error in the "Log" tab.', 'code-profiler'),
				$cp_error,
				CODE_PROFILER_UPLOAD_DIR .'/'
				);
			$this->return_error( $error );
		}

		// Total data analyzed
		$this->parsed_data += filesize( $this->tmp_iostats );
		$this->parsed_data += filesize( $this->tmp_ticks );

		$this->profile_name	= $profile_name;
		$this->plugins_dir	= preg_quote( realpath( WP_PLUGIN_DIR ) );
		$this->themes_dir		= preg_quote( realpath( WP_CONTENT_DIR .'/themes') );
		$this->mu_dir			= preg_quote( realpath( WPMU_PLUGIN_DIR ) );
	}


	/**
	 * Filter and save the profiler's data
	 */
	public function prepare_report() {

		$this->parse_ticks();
		$this->get_plugins_theme_name();
		$this->save_iostats();
		$this->save_connections();
		$this->save_data();
		$this->save_diskio();
		$this->save_composer();

		code_profiler_log_info( sprintf(
			__('Volume of code and data analyzed: %1$sMB (%2$s plugins and '.
			'1 theme) in %4$ss - Memory used: %3$sMB', 'code-profiler'),
			number_format( $this->parsed_data / 1024 / 1024, 2 ),
			(int) $this->total_plugins,
			number_format( memory_get_peak_usage( false ) / 1024 / 1024, 2 ),
			number_format( microtime( true ) - $this->microtime, 2 )
		));

		return $this->microtime;
	}


	/**
	 * Return a json-encoded error for AJAX, write to log and quit.
	 */
	private function return_error( $error ) {

		$response['message'] = $error;
		$response['status']  = 'error';
		code_profiler_log_error( $error );
		wp_send_json( $response );
	}


	/**
	 * Save all data to disk
	 */
	private function save_data() {

		$slugs_buffer						= '';
		$this->summary_list['time']	= 0;

		$slugs_tsv		= CODE_PROFILER_UPLOAD_DIR ."/{$this->microtime}.".
							"{$this->profile_name}.slugs.profile";
		if ( file_exists( $slugs_tsv ) ) { unlink( $slugs_tsv ); }

		$summary_json	= CODE_PROFILER_UPLOAD_DIR ."/{$this->microtime}.".
								"{$this->profile_name}.summary.profile";
		if ( file_exists( $summary_json ) ) { unlink( $summary_json ); }

		foreach( $this->plugins as $content => $content_array ) {
			$this->total_plugins++;
			// Slugs
			$this->summary_list['time'] += $content_array['time'];
			if ( isset( $this->cx_buffer['plugin'][ $content ] ) ) {
				$time = $this->convert_2_seconds(
					$content_array['time'] + $this->cx_buffer['plugin'][ $content ]
				);
				$this->summary_list['time'] += $this->cx_buffer['plugin'][ $content ];
			} else {
				$time = $this->convert_2_seconds( $content_array['time'] );
			}
			if ( empty( $content_array['name'] ) ) {
				// E.g., a plugin without a folder
				$content_array['name'] = $content;
			}
			if ( isset( $content_array['mu'] ) ) {
				$plugin = 'mu-plugin';
			} else {
				$plugin = 'plugin';
			}
			$slugs_buffer .= "$content\t$time\t{$content_array['name']}\t$plugin\n";
		}
		foreach( $this->themes as $content => $content_array ) {
			// Slugs
			$this->summary_list['time'] += $content_array['time'];
			if ( isset( $this->cx_buffer['theme'][ $content ] ) ) {
				$time = $this->convert_2_seconds(
					$content_array['time'] + $this->cx_buffer['theme'][ $content ]
				);
				$this->summary_list['time'] += $this->cx_buffer['theme'][ $content ];
			} else {
				$time = $this->convert_2_seconds( $content_array['time'] );
			}
			$slugs_buffer .= "$content\t$time\t{$content_array['name']}\ttheme\n";
		}

		$error = '';
		if ( empty( $slugs_buffer ) ) {
			$error = esc_html__('Data is empty: no plugins or themes found', 'code-profiler');
		}
		if ( $error ) {
			$this->return_error( $error );
		}

		// Save data
		file_put_contents( $slugs_tsv, $slugs_buffer );

		// Summary
		$s = json_decode( file_get_contents( $this->tmp_summary ), true );
		unlink( $this->tmp_summary );
		if ( empty( $s['memory'] ) ) {
			$this->summary_list['memory'] = '-';
		} else {
			$this->summary_list['memory'] = $s['memory'] / 1024 / 1024;
		}
		if ( empty( $s['queries'] ) ) {
			$this->summary_list['queries']= '-';
		} else {
			$this->summary_list['queries']= $s['queries'];
		}
		if ( empty( $this->total_io ) ) {
			$this->summary_list['io']		= '-';
		} else {
			$this->summary_list['io']		= $this->total_io;
		}
		$this->summary_list['items']		= $this->total_plugins + 1; // Add the theme
		$this->summary_list['time']		= $this->convert_2_seconds( $this->summary_list['time'] );
		$this->summary_list['time']		= number_format( $this->summary_list['time'], 4 );

		// Save Code Profiler, PHP and WordPress' versions
		global $wp_version;
		$this->summary_list['versions']	= [
			'wp'	=> $wp_version,
			'cp'	=> CODE_PROFILER_VERSION,
			'php'	=> PHP_VERSION
		];

		file_put_contents( $summary_json, json_encode( $this->summary_list ) );

	}


	/**
	 * Convert microtime (PHP<7.3) or hrtime (PHP>=7.3) to seconds
	 */
	private function convert_2_seconds( $time ) {

		if ( $time < 0 || empty( $time ) ) {
			return '0';
		}
		if ( strpos( $time, '.' ) !== false ) {
			// Looks like microtime
			$time = number_format( $time, 6 );
		} else {
			// hrtime
			$time = number_format( $time / 1000000000, 6 );
		}
		return $time;
	}


	/**
	 * Check if a slug is from a plugin or theme.
	 */
	private function plugin_or_theme( $script ) {

		$res = [
			'theme' => '',
			'plugin' => '',
			'script' => ''
		];

		if ( preg_match("`^{$this->plugins_dir}[\\\/](?:(.+?)[\\\/]|([^\\\/]+\.php)$)`", $script, $slug ) ) {
			if (! empty( $slug[1] ) ) {
				$res['plugin'] = $slug[1];
				$res['script'] = $script;
			} elseif (! empty( $slug[2] ) ) {
				$res['plugin'] = $slug[2];
				$res['script'] = $script;
			}

		} elseif ( preg_match("`^{$this->themes_dir}[\\\/]([^\\\/]+)[\\\/]`", $script, $slug ) ) {
			$res['theme']  = $slug[1];
			$res['script'] = $script;

		} elseif ( preg_match("`^{$this->mu_dir}[\\\/]([^\\\/]+\.php)$`", $script, $slug ) ) {
			$res['plugin']	= $slug[1];
			$res['script'] = $script;
		}
		return $res;
	}


	/**
	 * Retrieve the full name for all plugins and themes.
	 */
	private function get_plugins_theme_name() {

		// Get installed plugins
		if ( ! function_exists('get_plugins') ) {
			require_once ABSPATH .'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();
		foreach( $installed_plugins as $k => $v ) {
			if ( preg_match('`^([^\\\/]+)[\\\/]`', $k, $match ) ) {
				if ( isset( $this->plugins[ $match[1] ]['time'] ) ) {
					$this->plugins[ $match[1] ]['name'] = $v['Name'];
				}
			}
		}
		// Get installed themes
		if ( ! function_exists('wp_get_themes') ) {
			require_once ABSPATH .'wp-includes/theme.php';
		}
		$installed_themes = wp_get_themes();
		foreach( $installed_themes as $k => $v ) {
			if ( isset( $this->themes[ $k ]['time'] ) ) {
				$this->themes[ $k ]['name'] = $v->Name;
			}
		}
		// MU plugins
		$mu_plugins = get_mu_plugins();
		foreach( $mu_plugins as $k => $v ) {
			if ( isset( $this->plugins[ $k ]['time'] ) ) {
				$this->plugins[ $k ]['name']	= $v['Name'];
				$this->plugins[ $k ]['mu']		= 1;
			}
		}
	}


	/**
	 * Parse, filter and sort the data
	 */
	private function parse_ticks() {

		$fh = fopen( $this->tmp_ticks, 'rb');
		if ( $fh === false ) {
			$error = sprintf(
				esc_html__('Cannot open file for reading: %s', 'code-profiler'),
				$this->tmp_ticks
			);
			$this->return_error( $error );
		}
		while(! feof( $fh ) ) {
			$line = fgets( $fh );
			if ( preg_match( '/^(.+?)\t(.+?)\t(.+?)\t(.+)$/', $line, $match ) ) {

				$caller = $match[1]; $callee = $match[2];
				$start  = $match[3]; $stop   = $match[4];

				// Check if we have a plugin or theme
				$slug = $this->plugin_or_theme( $callee );
				if ( empty( $slug['theme'] ) && empty( $slug['plugin'] ) ) {
					$slug = $this->plugin_or_theme( $caller );
				}

				// Elapsed time since last tick
				if (! empty( $this->last_time ) && ! empty( $start ) ) {
					$elapse = $start - $this->last_time;
				} else {
					$elapse = 0;
				}

				// Plugin: update/create stats (time)

				// We have a slug
				if (! empty( $slug['theme'] ) || ! empty( $slug['plugin'] ) ) {
					// It's a theme
					if (! empty( $slug['theme'] ) ) {
						// Same theme as previous record, update its stats
						if ( $this->last_slug == $slug['theme'] ) {
							if ( isset( $this->themes[ $this->last_slug ]['time'] ) ) {
								$this->themes[ $this->last_slug ]['time'] += $elapse;
							}
						// Update the old record and create the new one if it doesn't exist
						} else {
							if (! empty( $this->last_slug ) ) {
								if ( isset( $this->themes[ $this->last_slug ]['time'] ) ) {
									$this->themes[ $this->last_slug ]['time'] += $elapse;
								}
							}
							if (! isset( $this->themes[ $slug['theme'] ]['time'] ) ) {
								$this->themes[ $slug['theme'] ]['time'] = 0;
							}
						}
					// It's a plugin
					} elseif (! empty( $slug['plugin'] ) ) {
						// Same plugin as previous record, update its stats
						if ( $this->last_slug == $slug['plugin'] ) {
							if ( isset( $this->plugins[ $this->last_slug ]['time'] ) ) {
								$this->plugins[ $this->last_slug ]['time'] += $elapse;
							}
						// Update the old one and create the new one if it doesn't exist
						} else {
							if (! empty( $this->last_slug ) ) {
								if ( isset( $this->plugins[ $this->last_slug ]['time'] ) ) {
									$this->plugins[ $this->last_slug ]['time'] += $elapse;
								}
							}
							if (! isset( $this->plugins[ $slug['plugin'] ]['time'] ) ) {
								$this->plugins[ $slug['plugin'] ]['time'] = 0;
							}
						}

						// Look for multiple copies of composer and warn the user
						if ( preg_match("`^{$this->plugins_dir}[\\\/](?:[^\\\/]+)[\\\/].+?[\\\/]composer[\\\/]autoload_real\.php`", $caller ) ) {
							// Save the slug first, we'll fetch the name later
							if (! isset( $this->composer[ $slug['plugin'] ] ) ) {
								$this->composer[ $slug['plugin'] ] = '';
							}
						}
					}

				// We don't have a slug: if there's an old record, update it
				} else {
					if (! empty( $this->last_type ) && ! empty( $this->last_slug ) ) {
						if ( $this->last_type == 'plugin' ) {
							if ( isset( $this->plugins[ $this->last_slug ]['time'] ) ) {
								$this->plugins[ $this->last_slug ]['time'] += $elapse;
							}
						} elseif ( $this->last_type == 'theme' ) {
							if ( isset( $this->themes[ $this->last_slug ]['time'] ) ) {
								$this->themes[ $this->last_slug ]['time'] += $elapse;
							}
						}
					}
				}

				// Update all last records
				if (! empty( $slug['theme'] ) ) {
					$this->last_type = 'theme';
					$this->last_slug = $slug['theme'];
				} elseif (! empty( $slug['plugin'] ) ) {
					$this->last_type = 'plugin';
					$this->last_slug = $slug['plugin'];
				} else {
					$this->last_type = '';
					$this->last_slug = '';
				}
				$this->last_time    = $stop;
			}
		}

		fclose( $fh );
		unlink( $this->tmp_ticks );
	}

	/********************************************************************
	 * Save IO stats
	 */
	private function save_iostats() {

		$buffer = json_decode ( file_get_contents( $this->tmp_iostats ) , true );

		if ( empty( $buffer ) ) {
			$error = sprintf(
				esc_html__('Cannot decode JSON-encode file (%s)', 'code-profiler'),
				'CODE_PROFILER_TMP_IOSTATS_LOG'
			);
			$this->return_error( $error );
		}

		$lines = '';
		foreach( $buffer as $key => $value ) {
			$lines .= "$key\t$value\n";
			$this->total_io += $value;
		}

		file_put_contents(
			CODE_PROFILER_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.iostats.profile",
			$lines
		);

		unlink( $this->tmp_iostats );
	}


	/**
	 * Save Read/Write stats
	 */
	private function save_diskio() {

		rename(
			$this->tmp_diskio,
			CODE_PROFILER_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.diskio.profile"
		);
	}


	 /**
	  * Save list of plugins using composer
	  */
	private function save_composer() {

		if (! empty( $this->composer ) ) {
			// Try to get the plugin's name
			foreach( $this->composer as $slug => $v ) {
				if ( isset( $this->plugins[ $slug ]['name'] ) ) {
					$this->composer[ $slug ] = $this->plugins[ $slug ]['name'];
				}
			}
			file_put_contents(
				CODE_PROFILER_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.composer.profile",
				json_encode( $this->composer )
			);
		}
	}

	/**
	 * External connections.
	 */
	private function save_connections() {

		if (! file_exists( $this->tmp_connections ) ) {
			return;
		}

		$connections = json_decode( file_get_contents( $this->tmp_connections ), true );
		unlink( $this->tmp_connections );

		if ( empty( $connections ) ) {
			return;
		}

		foreach( $connections as $connection => $array ) {
			$found = 0;

			foreach( $array['bt'] as $k =>$v ) {
				if ( isset( $v['file'] ) && isset( $v['function'] ) && isset( $v['line'] ) ) {
					if ( ! $found &&
						in_array( $v['function'], [
							'wp_remote_get',
							'wp_safe_remote_get',
							'wp_remote_post',
							'wp_safe_remote_post'
						] )
					) {
						$found = 1;

						$type = $this->plugin_or_theme( $v['file'] );
						if (! empty( $type['theme'] ) ) {
							// Save it for later use
							$this->cx_buffer['theme'][ $type['theme'] ] = $array['stop'] - $connection;

						} elseif (! empty( $type['plugin'] ) ) {
							// Save it for later use
							$this->cx_buffer['plugin'][ $type['plugin'] ] = $array['stop'] - $connection;
						}
					}
				}
			}
		}
	}

}

// =====================================================================
// EOF
