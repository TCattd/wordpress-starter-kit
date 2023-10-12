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
// Display Summary tab.

echo code_profiler_display_tabs( 3 );

// Save settings?
if (! empty( $_POST['save-settings'] ) ) {
	// Make sure we have security nonce
	if ( empty( $_POST['cp-save-settings'] ) || ! wp_verify_nonce($_POST['cp-save-settings'], 'cp_settings') ) {
		wp_nonce_ays('cp_settings');
	}
	$res = code_profiler_save_settings();
	if ( $res === true ) {
		printf( CODE_PROFILER_UPDATE_NOTICE, esc_html__('Your changes have been saved.', 'code-profiler') );
	}
	else {
		printf( CODE_PROFILER_ERROR_NOTICE, esc_html__('No changes were detected.', 'code-profiler') );
	}
}

// Fetch current options
$cp_options = get_option('code-profiler');

?>
<form method="post">
	<?php wp_nonce_field('cp_settings', 'cp-save-settings', 0); ?>
	<table class="form-table">
		<?php

		// Paths
		if ( empty( $cp_options['show_paths'] ) || ! in_array( $cp_options['show_paths'], ['absolute', 'relative' ] ) ) {
			$cp_options['show_paths'] = 'relative';
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e('Paths','code-profiler') ?></th>
			<td>
				<p><label><input type="radio" name="cp_options[show_paths]"<?php checked( $cp_options['show_paths'], 'absolute') ?> value="absolute" /><?php printf( esc_html__('Show absolute paths (e.g., %s)','code-profiler'), esc_html( ABSPATH ) .'wp-admin/index.php') ?></label></p>
				<p><label><input type="radio" name="cp_options[show_paths]"<?php checked( $cp_options['show_paths'], 'relative') ?> value="relative" /><?php printf( esc_html__('Show relative paths (e.g., %s)','code-profiler'), 'wp-admin/index.php') ?></label></p>
			</td>
		</tr>

		<?php
		// Name vs slug
		if ( empty( $cp_options['display_name'] ) || ! in_array( $cp_options['display_name'], ['full', 'slug' ] ) ) {
			$cp_options['display_name'] = 'full';
		}
		// Truncate names
		if ( empty( $cp_options['truncate_name'] ) || ! preg_match('/^\d+$/', ( $cp_options['truncate_name'] ) ) ) {
			$cp_options['truncate_name'] = 30;
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e('Plugins & Themes','code-profiler') ?></th>
			<td>
				<p><label><input type="radio" name="cp_options[display_name]"<?php checked( $cp_options['display_name'], 'full') ?> value="full" /><?php printf( esc_html__('Show real names (e.g., %s)','code-profiler'), 'Super Plugin Pro for WordPress') ?></label></p>
				<p><label><input type="radio" name="cp_options[display_name]"<?php checked( $cp_options['display_name'], 'slug') ?> value="slug" /><?php printf( esc_html__('Show slugs (e.g., %s)','code-profiler'), 'super-plugin-pro') ?></label></p>
				<br />
				<p><label><?php
					printf(
						esc_html__('Truncate names to %s characters', 'code-profiler'),
						'<input class="small-text" type="number" size="4" min="0" maxlength="100" name="cp_options[truncate_name]" value="'. (int) $cp_options['truncate_name'] .'" />'
					);
				?></label></p>
				<p class="description"><?php esc_html_e('A name longer than 30 characters will be difficult to read on most charts.', 'code-profiler') ?></p>
			</td>
		</tr>

		<?php
		// chart type
		if ( empty( $cp_options['chart_type'] ) || ! in_array( $cp_options['chart_type'], ['x', 'y' ] ) ) {
			$cp_options['chart_type'] = 'x';
		}
		// Max plugins to display
		if ( empty( $cp_options['chart_max_plugins'] ) || ! preg_match('/^\d+$/', ( $cp_options['chart_max_plugins'] ) ) ) {
			$cp_options['chart_max_plugins'] = 25;
		}
		// Empty values
		if (! empty( $cp_options['hide_empty_value'] ) ) {
			$cp_options['hide_empty_value'] = 1;
		} else {
			$cp_options['hide_empty_value'] = 0;
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e('Tables & Charts','code-profiler') ?></th>
			<td>
				<p><label><input type="radio" name="cp_options[chart_type]"<?php checked( $cp_options['chart_type'], 'x') ?> value="x" /><?php esc_html_e('Display horizontal charts by default','code-profiler') ?></label></p>
				<p><label><input type="radio" name="cp_options[chart_type]"<?php checked( $cp_options['chart_type'], 'y') ?> value="y" /><?php esc_html_e('Display vertical charts by default','code-profiler') ?></label></p>
				<p class="description"><?php esc_html_e('You can switch between vertical and horizontal by clicking the corresponding button located below each chart.', 'code-profiler') ?></p>

				<br />

				<p><label><?php
					printf(
						esc_html__('Do not display more than %s plugins on a chart', 'code-profiler'),
						'<input class="small-text" type="number" size="3" min="1" maxlength="100" name="cp_options[chart_max_plugins]" value="'. (int) $cp_options['chart_max_plugins'] .'" />'
					);
				?></label></p>
				<p class="description"><?php esc_html_e('More than 30 plugins will be difficult to read on most charts. Note that only plugins that have a negligible impact on your website\'s performance will be hidden.', 'code-profiler') ?></p>

				<br />

				<p><label><input type="checkbox" name="cp_options[hide_empty_value]"<?php checked( $cp_options['hide_empty_value'], '1') ?> /><?php esc_html_e('Hide items that have an empty value.','code-profiler') ?></label></p>

				<p class="description"><?php esc_html_e('This options will hide items that have an empty value on all graphs and tables.', 'code-profiler') ?></p>

			</td>
		</tr>

		<?php
		// Composer warning
		if (! empty( $cp_options['warn_composer'] ) ) {
			$cp_options['warn_composer'] = 1;
		} else {
			$cp_options['warn_composer'] = 0;
		}
		// WP-CLI integration
		if (! empty( $cp_options['enable_wpcli'] ) ) {
			$cp_options['enable_wpcli'] = 1;
		} else {
			$cp_options['enable_wpcli'] = 0;
		}
		// Disable WP-CRON
		if (! empty( $cp_options['disable_wpcron'] ) ) {
			$cp_options['disable_wpcron'] = 1;
		} else {
			$cp_options['disable_wpcron'] = 0;
		}

		// HTTP code to reject
		if (! empty( $cp_options['http_response'] ) ) {
			if (! preg_match( "/{$cp_options['http_response']}/", '300') ) {
				$http_response_300 = 0;
			} else {
				$http_response_300 = 1;
			}
			if (! preg_match( "/{$cp_options['http_response']}/", '400') ) {
				$http_response_400 = 0;
			} else {
				$http_response_400 = 1;
			}
			if (! preg_match( "/{$cp_options['http_response']}/", '500') ) {
				$http_response_500 = 0;
			} else {
				$http_response_500 = 1;
			}
		} else {
			$http_response_300 = 0;
			$http_response_400 = 0;
			$http_response_500 = 0;
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e('General Options', 'code-profiler') ?></th>
			<td>
				<p><label><input type="checkbox" name="cp_options[warn_composer]"<?php checked( $cp_options['warn_composer'], '1') ?> /><?php esc_html_e('Show a notice when several plugins are using Composer dependency manager.','code-profiler') ?></label></p>

				<p class="description"><?php esc_html_e('Consult the FAQ tab for more details about this option.', 'code-profiler') ?></p>

				<br />

				<p><label><input type="checkbox" name="cp_options[enable_wpcli]"<?php checked( $cp_options['enable_wpcli'], '1') ?> /><?php esc_html_e('Enable WP-CLI integration.','code-profiler') ?></label></p>
				<p class="description"><?php printf( esc_html__('Enter %s to display the available command line options.', 'code-profiler'), '<code>wp code-profiler help</code>') ?></p>

				<br />

				<p><label><input type="checkbox" name="cp_options[disable_wpcron]"<?php checked( $cp_options['disable_wpcron'], '1') ?> /><?php esc_html_e('Disable WP-Cron when running the profiler.','code-profiler') ?></label></p>

				<p class="description"><?php esc_html_e('This option will prevent WP-Cron to run scheduled tasks in the background that could affect the results of the profiler.', 'code-profiler') ?></p>

				<br />

				<p><?php esc_html_e('Stop the profiler and throw an error if the server returns any of the following HTTP status codes:', 'code-profiler') ?></p>
				<p><label><input type="checkbox" name="cp_options[http_response_300]" value="1"<?php checked( $http_response_300, '1') ?> /><?php esc_html_e('3xx redirection (301 Moved Permanently, 302 Found etc)','code-profiler') ?></label></p>
				<p><label><input type="checkbox" name="cp_options[http_response_400]" value="1"<?php checked( $http_response_400, '1') ?> /><?php esc_html_e('4xx client errors (400 Bad Request, 403 Forbidden, 404 Not Found etc)','code-profiler') ?></label></p>
				<p><label><input type="checkbox" name="cp_options[http_response_500]" value="1"<?php checked( $http_response_500, '1') ?> /><?php esc_html_e('5xx server errors (500 Internal Server Error, 503 Service Unavailable etc)','code-profiler') ?></label></p>
			</td>
		</tr>
		<?php
		// Accuracy
		if ( empty( $cp_options['accuracy'] ) || ! preg_match('/^(?:1|5|10|15|20)$/D', $cp_options['accuracy'] ) ) {
			$cp_options['accuracy'] = 1;
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e('Accuracy & Precision', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php
				printf(
					esc_attr__('When accuracy is set to the highest level, Code Profiler runs as a %s Tracing profiler %s, and when accuracy is set to a lower level, it runs as a %s Sampling profiler %s.', 'code-profiler'),
					'<em>', '</em>','<em>', '</em>'
				);
				echo '<br />';
				esc_attr_e('Selecting a high accuracy level is preferred and will work on most sites, but the profiling process will take longer to execute as opposed to choosing a lower accuracy level. If you have a slow WordPress site with a lot of plugins installed and your server or reverse proxy is timing out when Code Profiler is running (e.g., "503 Service Unavailable" or "504 Gateway Timeout" error), try to lower the accuracy level in order to speed up the profiling process and avoid the server timeout.', 'code-profiler');
				?>"></span></th>
			<td>
				<p><label><?php esc_html_e('Select the accuracy and precision level of the profiler:', 'code-profiler') ?> &nbsp;<select name="cp_options[accuracy]">
				<?php
				foreach( CODE_PROFILER_ACCURACY as $key => $value ) {
					echo '<option value="'. esc_attr( $key ) .'"'. selected( $cp_options['accuracy'], $key, false ) .'>'. esc_html( $value ) .'</option>';
				}
				?></select></label></p>
			</td>
		</tr>
		<?php
		$recommended = code_profiler_suggested_memory();
		// Buffer size
		if ( empty( $cp_options['buffer'] ) ) {
			$cp_options['buffer'] = 0;
		} elseif (! preg_match('/^(?:[1-9]|10)$/', $cp_options['buffer'] ) ) {
			$cp_options['buffer'] = (int) $recommended;
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e('Buffer size', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php
				esc_attr_e('When running, the profiler collects data and saves it to a memory buffer. When the buffer is full, the data is written to disk. If your site uses too much memory and throws a PHP memory error, you can try to lower that value.', 'code-profiler');
				?>"></span></th>
			<td>
				<p>
					<label>
						<select name="cp_options[buffer]">
						<?php
						echo '<option value="0"'. selected( $cp_options['buffer'], 0, false ) .'>'.
							esc_html__('Automatic', 'code-profiler') .
						'</option>';
						for ( $i = 1; $i <= 10; $i++ ) {
							echo '<option value="'. $i .'"'. selected( $cp_options['buffer'], $i, false ) .'>'.
							sprintf(
								/* Translators: 'MB' for MegaBytes */
								esc_html__('%s MB', 'code-profiler')
							, $i ) .
							'</option>';
						}
						?>
						</select>
					</label>
				</p>
				<p class="description"><?php
					printf(
						esc_html__('Recommended value based on your system configuration: %s MB', 'code-profiler'),
						(int) $recommended
					);
				?></p>
			</td>
		</tr>
	</table>

	<p><input type="submit" name="save-settings" class="button-primary" value="<?php esc_attr_e('Save Settings', 'code-profiler') ?>" /></p>

</form>
<?php

// ===================================================================== 2023-06-15
// Save and validate the settings.

function code_profiler_save_settings() {

	$cp_options = get_option('code-profiler');

	// Paths
	if ( empty( $_POST['cp_options']['show_paths'] ) ||
		! in_array( $_POST['cp_options']['show_paths'], ['absolute', 'relative' ] ) ) {

		$cp_options['show_paths'] = 'relative';
	} else {
		$cp_options['show_paths'] = sanitize_key( $_POST['cp_options']['show_paths'] );
	}

	// Name vs slug
	if ( empty( $_POST['cp_options']['display_name'] ) ||
		! in_array( $_POST['cp_options']['display_name'], ['full', 'slug' ] ) ) {

		$cp_options['display_name'] = 'full';
	} else {
		$cp_options['display_name'] = sanitize_key( $_POST['cp_options']['display_name'] );
	}

	// Truncate names
	if ( empty( $_POST['cp_options']['truncate_name'] ) ||
		! preg_match('/^\d+$/', ( $_POST['cp_options']['truncate_name'] ) ) ) {

		$cp_options['truncate_name'] = 30;
	} else {
		$cp_options['truncate_name'] = (int) $_POST['cp_options']['truncate_name'];
	}

	// chart type
	if ( empty( $_POST['cp_options']['chart_type'] ) ||
		! in_array( $_POST['cp_options']['chart_type'], ['x', 'y' ] ) ) {

		$cp_options['chart_type'] = 'x';
	} else {
		$cp_options['chart_type'] = sanitize_key( $_POST['cp_options']['chart_type'] );
	}

	// Max plugins to display
	if ( empty( $_POST['cp_options']['chart_max_plugins'] ) ||
		! preg_match('/^\d+$/', ( $_POST['cp_options']['chart_max_plugins'] ) ) ) {

		$cp_options['chart_max_plugins'] = 25;
	} else {
		$cp_options['chart_max_plugins'] = (int) $_POST['cp_options']['chart_max_plugins'];
	}

	// Empty values
	if (! empty( $_POST['cp_options']['hide_empty_value'] ) ) {
		$cp_options['hide_empty_value'] = 1;
	} else {
		$cp_options['hide_empty_value'] = 0;
	}

	// Composer warning
	if (! empty( $_POST['cp_options']['warn_composer'] ) ) {
		$cp_options['warn_composer'] = 1;
	} else {
		$cp_options['warn_composer'] = 0;
	}

	// WP-CLI integration
	if (! empty( $_POST['cp_options']['enable_wpcli'] ) ) {
		$cp_options['enable_wpcli'] = 1;
	} else {
		$cp_options['enable_wpcli'] = 0;
	}

	// WP-Cron
	if (! empty( $_POST['cp_options']['disable_wpcron'] ) ) {
		$cp_options['disable_wpcron'] = 1;
	} else {
		$cp_options['disable_wpcron'] = 0;
	}

	// HTTP status code
	$code 								= '';
	$cp_options['http_response']	= '';
	if (! empty( $_POST['cp_options']['http_response_300'] ) ) {
		$code .= '3|';
	}
	if (! empty( $_POST['cp_options']['http_response_400'] ) ) {
		$code .= '4|';
	}
	if (! empty( $_POST['cp_options']['http_response_500'] ) ) {
		$code .= '5';
	}
	$code = rtrim( $code, '|');
	if (! empty( $code ) ) {
		$cp_options['http_response'] = "^(?:$code)\d{2}$";
	}

	// Accuracy
	if ( empty( $_POST['cp_options']['accuracy'] ) ||
		! preg_match('/^(?:1|5|10|15|20)$/D', $_POST['cp_options']['accuracy'] ) ) {

		$cp_options['accuracy'] = 1;
	} else {
		$cp_options['accuracy'] = (int) $_POST['cp_options']['accuracy'];
	}

	// Buffer size
	if ( empty( $_POST['cp_options']['buffer'] ) ) {
		$cp_options['buffer'] = 0;
	} elseif (! preg_match('/^(?:[1-9]|10)$/D', $_POST['cp_options']['buffer'] ) ) {
		$cp_options['buffer'] = 10;
	} else {
		$cp_options['buffer'] = (int) $_POST['cp_options']['buffer'];
	}

	return update_option('code-profiler', $cp_options );

}

// =====================================================================
// EOF
