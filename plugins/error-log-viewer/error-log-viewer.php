<?php
/**
Plugin Name: Error Log Viewer by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/error-log-viewer/
Description: Get latest error log messages to diagnose website problems. Define and fix issues faster.
Author: BestWebSoft
Text Domain: error-log-viewer
Domain Path: /languages
Version: 1.1.2
Author URI: https://bestwebsoft.com/
License: GNU General Public License V3
 */

/**
	@Copyright 2021  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Add WordPress page 'bws_panel' and sub-page of this plugin to admin-panel.
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_admin_menu' ) ) {
	function rrrlgvwr_admin_menu() {
		$settings = add_menu_page( esc_html__( 'Error Log Viewer Settings', 'error-log-viewer' ), 'Error Log Viewer', 'manage_options', 'rrrlgvwr.php', 'rrrlgvwr_settings_page' );

		add_submenu_page( 'rrrlgvwr.php', esc_html__( 'Error Log Viewer Settings', 'error-log-viewer' ), esc_html__( 'Settings', 'error-log-viewer' ), 'manage_options', 'rrrlgvwr.php', 'rrrlgvwr_settings_page' );

		$monitor = add_submenu_page( 'rrrlgvwr.php', esc_html__( 'Log Monitor', 'error-log-viewer' ), esc_html__( 'Log Monitor', 'error-log-viewer' ), 'manage_options', 'rrrlgvwr-monitor.php', 'rrrlgvwr_monitor_page' );

		add_submenu_page( 'rrrlgvwr.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'rrrlgvwr-bws-panel', 'bws_add_menu_render' );

		add_action( 'load-' . $monitor, 'rrrlgvwr_add_tabs' );
		add_action( 'load-' . $settings, 'rrrlgvwr_add_tabs' );
	}
}

/**
 * Internationalization
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_plugins_loaded' ) ) {
	function rrrlgvwr_plugins_loaded() {
		load_plugin_textdomain( 'error-log-viewer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Plugin initialization
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_init' ) ) {
	function rrrlgvwr_init() {
		global $rrrlgvwr_plugin_info;

		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $rrrlgvwr_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$rrrlgvwr_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $rrrlgvwr_plugin_info, '4.5' );
	}
}

/**
 * Admin init
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_admin_init' ) ) {
	function rrrlgvwr_admin_init() {
		/* Add variable for bws_menu */
		global $bws_plugin_info, $rrrlgvwr_plugin_info, $rrrlgvwr_ten_mb;

		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id'      => '301',
				'version' => $rrrlgvwr_plugin_info['Version'],
			);
		}

		/* Call register settings function */
		if ( isset( $_GET['page'] ) && ( 'rrrlgvwr.php' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) || 'rrrlgvwr-monitor.php' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) {
			rrrlgvwr_settings();
		}

		$rrrlgvwr_ten_mb = 10485760;
	}
}

/**
 * Register settings for plugin
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_settings' ) ) {
	function rrrlgvwr_settings() {
		global $rrrlgvwr_options, $rrrlgvwr_plugin_info, $rrrlgvwr_periods, $rrrlgvwr_php_error_path;

		/* Install the option defaults */
		if ( ! get_option( 'rrrlgvwr_options' ) ) {
			$options_default = rrrlgvwr_get_default_options();
			add_option( 'rrrlgvwr_options', $options_default );
		}

		/* Get options from the database */
		$rrrlgvwr_options = get_option( 'rrrlgvwr_options' );

		if ( empty( $rrrlgvwr_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$rrrlgvwr_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Array merge incase this version has added new options */
		if ( ! isset( $rrrlgvwr_options['plugin_option_version'] ) || $rrrlgvwr_options['plugin_option_version'] !== $rrrlgvwr_plugin_info['Version'] ) {
			$options_default                            = rrrlgvwr_get_default_options();
			$options_default['display_settings_notice'] = 0;

			$rrrlgvwr_options                          = array_merge( $options_default, $rrrlgvwr_options );
			$rrrlgvwr_options['plugin_option_version'] = $rrrlgvwr_plugin_info['Version'];
			update_option( 'rrrlgvwr_options', $rrrlgvwr_options );
		}

		$rrrlgvwr_periods        = array(
			60      => __( 'Minutes', 'error-log-viewer' ),
			3600    => __( 'Hours', 'error-log-viewer' ),
			86400   => __( 'Days', 'error-log-viewer' ),
			604800  => __( 'Weeks', 'error-log-viewer' ),
			2592000 => __( 'Months', 'error-log-viewer' ),
		);
		$rrrlgvwr_php_error_path = ini_get( 'error_log' );
	}
}

/**
 * Function for getting_default_options
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_get_default_options' ) ) {
	function rrrlgvwr_get_default_options() {
		global $rrrlgvwr_plugin_info;

		$sitename = isset( $_SERVER['SERVER_NAME'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ) : '';
		if ( 'www.' === substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$from_email = 'wordpress@' . $sitename;

		$default_options = array(
			'plugin_option_version'   => $rrrlgvwr_plugin_info['Version'],
			'php_error_log_visible'   => 0,
			'lines_count'             => 10,
			'confirm_filesize'        => 0,
			'error_log_path'          => '',
			'count_visible_log'       => 0,
			'frequency_send'          => 1,
			'send_email'              => 0,
			'to_email'                => 'custom',
			'email_user'              => '',
			'email'                   => $from_email,
			'hour_day'                => 3600,
			'display_settings_notice' => 1,
			'suggest_feature_banner'  => 1,
			'display_method'          => 'lines',
			'date_from'               => '',
			'date_to'                 => '',
		);

		return $default_options;
	}
}

/**
 * Function register settings page
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_settings_page' ) ) {
	function rrrlgvwr_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
			require_once dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php';
		}
		require_once dirname( __FILE__ ) . '/includes/class-rrrlgvwr-settings.php';
		$page = new Rrrlgvwr_Settings_Tabs( plugin_basename( __FILE__ ) );
		if ( method_exists( $page, 'add_request_feature' ) ) {
			$page->add_request_feature();
		} ?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Error Log Viewer Settings', 'error-log-viewer' ); ?></h1>
			<?php $page->display_content(); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'rrrlgvwr_monitor_page' ) ) {
	function rrrlgvwr_monitor_page() {
		global $rrrlgvwr_options, $rrrlgvwr_php_error_path, $rrrlgvwr_ten_mb, $wp_filesystem;
		$error = $message = $notice = '';

		$home_path          = ( '/' === substr( get_home_path(), strlen( get_home_path() ) - 1 ) ) ? substr( get_home_path(), 0, strlen( get_home_path() ) - 1 ) : get_home_path();
		$wp_error_files     = rrrlgvwr_find_log_files( $home_path );
		$php_error_log_name = basename( $rrrlgvwr_php_error_path );

		WP_Filesystem();

		/* Show selected file with necessary settings */
		if ( isset( $_POST['rrrlgvwr_submit_show_content'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'rrrlgvwr_nonce_name' ) ) {
			if ( isset( $_POST['rrrlgvwr_select_log'] ) ) {
				$rrrlgvwr_options['error_log_path']   = in_array( sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_select_log'] ) ), $wp_error_files ) || $rrrlgvwr_php_error_path === sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_select_log'] ) ) ? sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_select_log'] ) ) : $wp_error_files[0];
				$rrrlgvwr_options['confirm_filesize'] = $wp_filesystem->exists( $rrrlgvwr_options['error_log_path'] ) ? $wp_filesystem->size( $rrrlgvwr_options['error_log_path'] ) : 0;
			}
			$rrrlgvwr_options['lines_count'] = isset( $_POST['rrrlgvwr_lines_count'] ) ? intval( $_POST['rrrlgvwr_lines_count'] ) : 0;
			update_option( 'rrrlgvwr_options', $rrrlgvwr_options );
		}

		/* Save content from textarea into the file in saved_logs */
		if ( isset( $_POST['rrrlgvwr_save_content'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'rrrlgvwr_nonce_name' ) ) {
			if ( isset( $_POST['rrrlgvwr_newcontent'] ) && ! empty( $_POST['rrrlgvwr_newcontent'] ) ) {
				$save_mes = rrrlgvwr_save_file( $_POST['rrrlgvwr_newcontent'] );
				if ( empty( $save_mes ) ) {
					$message = __( 'File was saved successfully', 'error-log-viewer' );
				} else {
					$error = __( "Plugin couldn't save the file. Try to change permissions to the directory, or try again", 'error-log-viewer' );
				}
			}
		}

		/* Clear selected log file */
		if ( isset( $_POST['rrrlgvwr_clear_file'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'rrrlgvwr_nonce_name' ) ) {
			$clear_mes = rrrlgvwr_clear_file( sanitize_text_field( $_POST['rrrlgvwr_clear_file_name'] ) );
			if ( empty( $clear_mes ) ) {
				$message = __( 'File was cleared successfully', 'error-log-viewer' );
			} else {
				$error = $clear_mes;
			}
		}

		/* Custom wp list table class */
		$saved_logs = new Error_Log_Saved_Files();
		if ( $saved_logs->current_action() ) {
			$saved_logs_action = $saved_logs->current_action();
		} else {
			$saved_logs_action = isset( $_REQUEST['saved_logs_action'] ) ? sanitize_key( $_REQUEST['saved_logs_action'] ) : '';
		}

		if ( $saved_logs_action === 'delete' && ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'rrrlgvwr_nonce_name' ) || check_admin_referer( plugin_basename( __FILE__ ), 'rrrlgvwr_nonce_name' ) ) ) {
			$rrrlgvwr_check_del  = isset( $_REQUEST['rrrlgvwr_check_del'] ) ? ( is_array( $_REQUEST['rrrlgvwr_check_del'] ) ? array_map( 'sanitize_text_field',  $_REQUEST['rrrlgvwr_check_del'] ) : sanitize_text_field( $_REQUEST['rrrlgvwr_check_del'] ) ) : 0;
			$rrrlgvwr_check_dels = is_array( $rrrlgvwr_check_del ) ? $rrrlgvwr_check_del : array( $rrrlgvwr_check_del );
			$saved_log           = glob( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . '*.txt' ); /* Array of saved files in saved_logs */
			$saved_log_name      = str_replace( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR, '', $saved_log ); /* Array of file's name in saved_logs */

			foreach ( $rrrlgvwr_check_dels as $check_del ) {
				$check_del = sanitize_key( $check_del );
				if ( in_array( $check_del . '.txt', $saved_log_name ) && $wp_filesystem->exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . $check_del . '.txt' ) ) {
					if ( $wp_filesystem->rmdir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . $check_del . '.txt' ) ) {
						$message = __( 'File(s) was deleted successfully', 'error-log-viewer' );
					} else {
						$error = sprintf( __( "Couldn't delete file %s, change permission to the 'wp-content' or 'error-log-viewer' plugin directory", 'error-log-viewer' ), ( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . $check_del . '.txt' ) );
					}
				} else {
					$error = sprintf( __( "Couldn't delete file %s, the file is not exist", 'error-log-viewer' ), ( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . $check_del . '.txt' ) );
				}
			}
		}

		$saved_log      = glob( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . '*.txt' ); /* Array of saved files in saved_logs */
		$saved_log_name = str_replace( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR, '', $saved_log ); /* Array of file's name in saved_logs */

		$saved_logs_data = array();
		foreach ( $saved_log_name as $name ) {
			$date              = str_replace( '_', '-', substr( $name, 0, strpos( $name, '-' ) ) ) . '&#010;' . str_replace( '_', ':', substr( $name, strpos( $name, '-' ) + 1, strpos( $name, '_log' ) - ( strpos( $name, '-' ) + 1 ) ) );
			$saved_logs_data[] = array(
				'name'  => $name,
				'check' => substr( $name, 0, strpos( $name, '.' ) ),
				'title' => sprintf( '<a target="_blank" class="row-title" href="' . esc_url( plugin_dir_url( __FILE__ ) . 'saved_logs/' . $name ) . '">%1$s</a>', esc_html__( 'Saved log from', 'error-log-viewer' ) ),
				'date'  => $date,
				'size'  => rrrlgvwr_file_size( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . $name ),
			);
		}

		$saved_logs->rrrlgvwr_saved_file = $saved_logs_data;
		$saved_logs->prepare_items();

		if ( isset( $_POST['rrrlgvwr_submit_show_content'] ) && isset( $_POST['rrrlgvwr_show_content'] ) ) {
			switch ( sanitize_key( $_POST['rrrlgvwr_show_content'] ) ) {
				case 'lines':
					$rrrlgvwr_options['display_method'] = 'lines';
					break;
				case 'date':
					$rrrlgvwr_options['display_method'] = 'date';
					$rrrlgvwr_options['date_from']      = isset( $_POST['rrrlgvwr_from'] ) ? sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_from'] ) ) : '';
					$rrrlgvwr_options['date_to']        = isset( $_POST['rrrlgvwr_to'] ) ? sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_to'] ) ) : '';
					break;
				case 'all':
					$rrrlgvwr_options['display_method'] = 'all';
					break;
			}
			update_option( 'rrrlgvwr_options', $rrrlgvwr_options );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Log Monitor', 'error-log-viewer' ); ?></h1>
			<div class="updated fade below-h2" 
			<?php
			if ( empty( $message ) || '' !== $error ) {
				echo 'style="display:none"';}
			?>
			><p><strong><?php echo esc_html( $message ); ?></strong></p></div>
			<div class="error below-h2" 
			<?php
			if ( '' === $error ) {
				echo 'style="display:none"';}
			?>
			><p><strong><?php echo esc_html( $error ); ?></strong></p></div>
			<?php if ( ! empty( $rrrlgvwr_options['file_path'] ) ) { ?>
				<form method="post" action="admin.php?page=rrrlgvwr-monitor.php">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'File', 'error-log-viewer' ); ?></th>
							<?php if ( isset( $rrrlgvwr_options['count_visible_log'] ) && $rrrlgvwr_options['count_visible_log'] > 1 ) { ?>
								<td>
									<select name="rrrlgvwr_select_log">
										<?php if ( intval( $rrrlgvwr_options['php_error_log_visible'] ) === 1 ) : ?>
											<option value="<?php echo esc_attr( $rrrlgvwr_php_error_path ); ?>"
												<?php
												if ( $rrrlgvwr_options['error_log_path'] === $rrrlgvwr_php_error_path ) {
													echo 'selected="selected"';}
												?>
											><?php echo esc_html( $php_error_log_name ); ?></option>
											<?php
										endif;
										foreach ( $wp_error_files as $key => $file ) {
											$name    = str_replace( substr( $file, 0, strripos( $file, '/' ) + 1 ), '', $file );
											$subname = substr( $name, 0, strpos( $name, '.' ) );
											$subname = $key . '_' . $subname . '_visible';
											if ( isset( $rrrlgvwr_options[ $subname ] ) && intval( $rrrlgvwr_options[ $subname ] ) === 1 ) {
												if ( $file === $rrrlgvwr_php_error_path && 1 === intval( $rrrlgvwr_options['php_error_log_visible'] ) ) {
													continue;
												}
												?>
												<option value="<?php echo esc_attr( $file ); ?>"
													<?php
													if ( $rrrlgvwr_options['error_log_path'] === $file ) {
														echo 'selected="selected"';}
													?>
												><?php echo esc_attr( $name ); ?></option>
												<?php
											}
										}
										?>
									</select>
								</td>
							<?php } elseif ( isset( $rrrlgvwr_options['count_visible_log'] ) && intval( $rrrlgvwr_options['count_visible_log'] ) === 1 ) { ?>
								<td>
									<span>
										<?php
										if ( 1 === intval( $rrrlgvwr_options['php_error_log_visible'] ) ) {
											echo esc_html( $php_error_log_name );
											$rrrlgvwr_options['error_log_path'] = $rrrlgvwr_php_error_path;
											update_option( 'rrrlgvwr_options', $rrrlgvwr_options );
										} else {
											foreach ( $wp_error_files as $key => $file ) {
												$name    = str_replace( substr( $file, 0, strripos( $file, '/' ) + 1 ), '', $file );
												$subname = substr( $name, 0, strpos( $name, '.' ) );
												$subname = $key . '_' . $subname . '_visible';
												if ( isset( $rrrlgvwr_options[ $subname ] ) && 1 === intval( $rrrlgvwr_options[ $subname ] ) ) {
													echo esc_html( $name );
													$rrrlgvwr_options['error_log_path'] = $file;
													update_option( 'rrrlgvwr_options', $rrrlgvwr_options );
												}
											}
										}
										?>
									</span>
								</td>
							<?php } ?>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Show', 'error-log-viewer' ); ?></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php esc_html_e( 'Show', 'error-log-viewer' ); ?></span></legend>
									<label for="rrrlgvwr-show-line">
										<input type="radio" name="rrrlgvwr_show_content" id="rrrlgvwr-show-line" value="lines" <?php checked( 'lines', $rrrlgvwr_options['display_method'] ); ?> />
										<span><?php esc_html_e( 'last', 'error-log-viewer' ); ?></span>
										<input type="number" min="1" name="rrrlgvwr_lines_count" value="<?php echo esc_attr( $rrrlgvwr_options['lines_count'] ); ?>" />
										<span><?php esc_html_e( 'lines', 'error-log-viewer' ); ?></span>
									</label><br>
									<label for="rrrlgvwr-show-date">
										<input type="radio" name="rrrlgvwr_show_content" id="rrrlgvwr-show-date" value="date" <?php checked( 'date', $rrrlgvwr_options['display_method'] ); ?> />
										<span><?php esc_html_e( 'log from', 'error-log-viewer' ); ?></span>
										<input type="text" id="rrrlgvwr-from" name="rrrlgvwr_from" value="<?php echo esc_attr( $rrrlgvwr_options['date_from'] ); ?>" />
										<span class="rrrlgvwr-indent"><?php esc_html_e( 'to', 'error-log-viewer' ); ?></span>
										<input type="text" id="rrrlgvwr-to" name="rrrlgvwr_to" value="<?php echo esc_attr( $rrrlgvwr_options['date_to'] ); ?>" />
									</label><br>
									<div class="hide-if-js rrrlgvwr-date-search-mes">
										<p>
											<span><?php esc_html_e( 'JavaScript is disable on your site. To search logs by dates, please enter in the first and second field dates among which you want see the log. Your entry should look like', 'error-log-viewer' ); ?></span>
											<code>07/10/2015<span class="rrrlgvwr-indent"><?php esc_html_e( 'to', 'error-log-viewer' ); ?></span>07/19/2015.</code>
											<span><?php esc_html_e( 'The first two numbers means month, the second two numbers means day of months, the last four numbers means year', 'error-log-viewer' ); ?></span>
										</p>
									</div>
									<label for="rrrlgvwr-show-all">
										<input type="radio" name="rrrlgvwr_show_content" id="rrrlgvwr-show-all" value="all" <?php checked( 'all', $rrrlgvwr_options['display_method'] ); ?> />
										<span><?php esc_html_e( 'full file', 'error-log-viewer' ); ?></span>
									</label>
									<?php if ( file_exists( $rrrlgvwr_options['error_log_path'] ) && filesize( $rrrlgvwr_options['error_log_path'] ) > $rrrlgvwr_ten_mb ) { ?>
										<div class="hide-if-js rrrlgvwr-date-search-mes">
											<p><?php esc_html_e( 'File size is more than 10 Mb. Be careful to check this option', 'error-log-viewer' ); ?></p>
										</div>
									<?php }; ?>
								</fieldset>
							</td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" name="rrrlgvwr_submit_show_content" class="button button-primary" value="<?php esc_html_e( 'View', 'error-log-viewer' ); ?>" />
						<?php if ( ! empty( $rrrlgvwr_options['error_log_path'] ) && $rrrlgvwr_options['error_log_path'] === $rrrlgvwr_php_error_path ) { ?>
							<span class="rrrlgvwr-indent"><?php esc_html_e( 'or', 'error-log-viewer' ); ?></span>
							<input type="submit" name="rrrlgvwr_save_content" class="button" value="<?php esc_html_e( 'Save as TXT file', 'error-log-viewer' ); ?>" />
						<?php } ?>
					</p>
					<textarea id="rrrlgvwr_textarea_content" name="rrrlgvwr_newcontent" class="large-text code" rows="16" readonly="readonly"><?php
						if ( isset( $_POST['rrrlgvwr_submit_show_content'] ) && isset( $_POST['rrrlgvwr_show_content'] ) ) {
							switch ( sanitize_key( $_POST['rrrlgvwr_show_content'] ) ) {
								case 'lines':
									esc_textarea( rrrlgvwr_read_last_lines( $rrrlgvwr_options['error_log_path'], $rrrlgvwr_options['lines_count'] ) );
									break;
								case 'date':
									$first_date = isset( $_POST['rrrlgvwr_from'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_from'] ) ) ) : time();
									$last_date  = isset( $_POST['rrrlgvwr_to'] ) ? strtotime( sanitize_text_field( wp_unslash( $_POST['rrrlgvwr_to'] ) ) ) : time();
									esc_textarea( rrrlgvwr_read_lines_by_date( $rrrlgvwr_options['error_log_path'], $first_date, $last_date ) );
									break;
								case 'all':
									esc_textarea( rrrlgvwr_read_full_file( $rrrlgvwr_options['error_log_path'] ) );
									break;
							}
						} else {
							esc_textarea( rrrlgvwr_read_last_lines( $rrrlgvwr_options['error_log_path'], $rrrlgvwr_options['lines_count'] ) );
						}
						?>
						</textarea>
					<table class="form-table">
						<tr>
							<th scope="row">
								<span><?php esc_html_e( 'File', 'error-log-viewer' ); ?></span>
								<?php if ( false !== strpos( $rrrlgvwr_options['error_log_path'], $home_path ) ) { ?>
									<a target="_blank" href="<?php echo str_replace( $home_path, get_home_url(), $rrrlgvwr_options['error_log_path'] ); ?>"><?php echo esc_html( basename( $rrrlgvwr_options['error_log_path'] ) ); ?></a>
								<?php } else {
									echo esc_html( basename( $rrrlgvwr_options['error_log_path'] ) );
								} ?>
								<span>
									<?php
									if ( file_exists( $rrrlgvwr_options['error_log_path'] ) ) {
										if ( 0 === filesize( $rrrlgvwr_options['error_log_path'] ) ) {
											esc_html_e( 'The file is empty.', 'error-log-viewer' );
										} else {
											echo esc_html__( 'with size', 'error-log-viewer' ) . ' ' . rrrlgvwr_file_size( $rrrlgvwr_options['error_log_path'] );
										}
										echo ' ' . esc_html__( 'Last update', 'error-log-viewer' ) . ': ' . gmdate( 'Y-m-d H:i:s', filemtime( $rrrlgvwr_options['error_log_path'] ) );
									}
									?>
								<span>
							</th>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" class="button button-primary" name="rrrlgvwr_clear_file" id="rrrlgvwr-clear-file" value="<?php esc_html_e( 'Clear log file', 'error-log-viewer' ); ?>" />
						<input type="hidden" value="<?php echo esc_attr( $rrrlgvwr_options['error_log_path'] ); ?>" name="rrrlgvwr_clear_file_name" />
					</p>
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'rrrlgvwr_nonce_name' ); ?>
				</form>
				<?php if ( $rrrlgvwr_php_error_path === $rrrlgvwr_options['error_log_path'] ) { ?>
					<h3 class="title"><?php esc_html_e( 'Saved log files', 'error-log-viewer' ); ?></h3>
					<form class="rrrlgvwr-saved-logs-table" method="post" action="admin.php?page=rrrlgvwr-monitor.php">
						<?php
						$saved_logs->display();
						wp_nonce_field( plugin_basename( __FILE__ ), 'rrrlgvwr_nonce_name' );
						?>
					</form>
				<?php } ?>
			<?php } else { ?>
				<p>
					<?php
					printf(
						esc_html__( 'Please enable log files in the %1$sError Log Viewer settings page%2$s to manage them.', 'error-log-viewer' ),
						'<a href="admin.php?page=rrrlgvwr.php">',
						'</a>'
					);
					?>
				</p>
			<?php } ?>
		</div>
		<?php
	}
}

/**
 * Function find all log files in home WordPress directory
 *
 * @param string
 * @return array or string
 */
if ( ! function_exists( 'rrrlgvwr_find_log_files' ) ) {
	function rrrlgvwr_find_log_files( $directory ) {
		/* Home path directory */
		if ( file_exists( $directory ) ) {
			/* Function add in array all directory in home directory including subdir */
			rrrl_glob_recursive( $directory, $directories );
			$files = array();
			foreach ( $directories as $directory ) {
				foreach ( glob( $directory . '/*.log' ) as $file ) {
					$files[] = $file;
				}
			}
			return $files;
		} else {
			return printf( esc_html__( "Directory %s is not exists, or isn't readable", 'error-log-viewer' ), esc_attr( $directory ) );
		}
	}
}

/**
 * Function glob recursive, add in array all dir and subdir in home directory
 *
 * @param string, object
 * @return void
 */
if ( ! function_exists( 'rrrl_glob_recursive' ) ) {
	function rrrl_glob_recursive( $directory, &$directories = array() ) {
		foreach ( glob( $directory, GLOB_ONLYDIR | GLOB_NOSORT ) as $folder ) {
			$directories[] = $folder;
			rrrl_glob_recursive( $folder . '/*', $directories );
		}
	}
}

/**
 * Function count and round file size
 *
 * @param string
 * @return string
 */
if ( ! function_exists( 'rrrlgvwr_file_size' ) ) {
	function rrrlgvwr_file_size( $path ) {
		global $rrrlgvwr_ten_mb;

		if ( file_exists( $path ) ) {
			if ( filesize( $path ) < $rrrlgvwr_ten_mb ) {
				return '&#8764;' . round( filesize( $path ) / 1024, 2 ) . '&nbsp;Kb&nbsp;';
			} else {
				return '&#8764;' . round( filesize( $path ) / 1024 / 1024, 2 ) . '&nbsp;Mb&nbsp;';
			}
		} else {
			return printf( esc_html__( 'File %s is not exists', 'error-log-viewer' ), esc_attr( $path ) );
		}
	}
}
/**
 * Function return size of directory with files
 *
 * @param string
 * @return string
 */
if ( ! function_exists( 'rrrlgvwr_path_size' ) ) {
	function rrrlgvwr_path_size( $path ) {
		global $rrrlgvwr_ten_mb;

		if ( file_exists( $path ) ) {
			$summ_size = 0;
			$dir       = scandir( $path );
			foreach ( $dir as $file ) {
				if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
					if ( is_dir( $path . DIRECTORY_SEPARATOR . $file ) ) {
						$summ_size += rrrlgvwr_path_size( $path . DIRECTORY_SEPARATOR . $file );
					} else {
						$summ_size += filesize( $path . DIRECTORY_SEPARATOR . $file );
					}
				}
			}
			if ( $summ_size < $rrrlgvwr_ten_mb ) {
				return '&#8764;' . round( $summ_size / 1024, 2 ) . '&nbsp;Kb&nbsp;';
			} else {
				return '&#8764;' . round( $summ_size / 1024 / 1024, 2 ) . '&nbsp;Mb&nbsp;';
			}
		} else {
			return printf( esc_html__( 'File %s is not exists', 'error-log-viewer' ), esc_attr( $path ) );
		}
	}
}

/**
 * Function read X last lines from file
 *
 * @param string, number
 * @return string
 */
if ( ! function_exists( 'rrrlgvwr_read_last_lines' ) ) {
	function rrrlgvwr_read_last_lines( $file, $lines ) {
		global $wp_filesystem;
		WP_Filesystem();

		if ( $wp_filesystem->exists( $file ) ) {
			$file_lines = $wp_filesystem->get_contents_array( $file );
			$text = array_slice( $file_lines, -$lines, $lines );
			foreach ( $text as $line ) {
				echo $line;
			}
		} else {
			return printf( esc_html__( "Couldn't open the file %s. Make sure file is exists or is readable.", 'error-log-viewer' ), esc_attr( $file ) );
		}
	}
}

/**
 * Read log in file from date to date
 *
 * @param strind, date
 * @return string
 */
if ( ! function_exists( 'rrrlgvwr_read_lines_by_date' ) ) {
	function rrrlgvwr_read_lines_by_date( $file, $first_date, $last_date ) {
		global $wp_filesystem;
		WP_Filesystem();

		if ( $wp_filesystem->exists( $file ) ) {
			$file_lines = $wp_filesystem->get_contents_array( $file );
			$pattern    = '/\[(.*?)\s/';
			$pattern_2  = '/(\d{4}-\d{2}-\d{2}).*?\s/';
			$count_line = 0;
			$line_date  = 0;
			foreach ( $file_lines as $line ) {
				if ( preg_match( $pattern, $line, $matches ) ) {
					$line_date = $matches[1];
				} elseif ( preg_match( $pattern_2, $line, $matches ) ) {
					$line_date = $matches[1];
				}
				if ( strtotime( $line_date ) >= $first_date && strtotime( $line_date ) <= $last_date ) {
					$count_line ++;
					echo $line;
				} else {
					continue;
				}
			}
			if ( 0 === $count_line ) {
				printf( esc_html__( 'No log in search date from %1$s to %2$s', 'error-log-viewer' ), gmdate( 'Y-m-d', $first_date ), gdate( 'Y-m-d', $last_date ) );
			}
		} else {
			return printf( esc_html__( "Couldn't open the file %s. Make sure file is exists or is readable.", 'error-log-viewer' ), esc_attr( $file ) );
		}
	}
}

/**
 * Function read full file
 *
 * @param string
 * @return string
 */

if ( ! function_exists( 'rrrlgvwr_read_full_file' ) ) {
	function rrrlgvwr_read_full_file( $file ) {
		global $wp_filesystem;
		WP_Filesystem();

		if ( $wp_filesystem->exists( $file ) ) {
			$file_lines = $wp_filesystem->get_contents_array( $file );
			foreach ( $file_lines as $line ) {
				echo $line;
			}
		} else {
			return printf( esc_html__( "Couldn't open the file %s. Make sure file is exists or is readable.", 'error-log-viewer' ), esc_attr( $file ) );
		}
	}
}

/**
 * Save file in saved_logs
 *
 * @param string
 * @return string
 */

if ( ! function_exists( 'rrrlgvwr_save_file' ) ) {
	function rrrlgvwr_save_file( $content ) {
		global $wp_filesystem;
		if ( @is_writable( dirname( __FILE__ ) ) ) {
			WP_Filesystem();
			if ( ! $wp_filesystem->exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' ) ) {
				$wp_filesystem->mkdir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' );
			}
			if ( ! $wp_filesystem->put_contents( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'saved_logs' . DIRECTORY_SEPARATOR . gmdate( 'Y_m_d-h_i_s' ) . '_log.txt', $content, 0755 ) ) {
				return esc_html__( "Plugin couldn't saved the file, change permissions to the 'wp-content' or 'error-log-viewer' plugin directory, or try again", 'error-log-viewer' );
			}
		} else {
			return esc_html__( "Plugin couldn't open the 'error-log-viewer' plugin directory, try to change permission to the 'wp-content' or 'error-log-viewer' plugin directory and try again", 'error-log-viewer' );
		}
	}
}
/**
 * Clear file
 *
 * @param string
 * @return string
 */

if ( ! function_exists( 'rrrlgvwr_clear_file' ) ) {
	function rrrlgvwr_clear_file( $file ) {
		global $rrrlgvwr_php_error_path, $wp_filesystem;
		if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
			require_once dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php';
		}
		require_once dirname( __FILE__ ) . '/includes/class-rrrlgvwr-settings.php';
		$page = new Rrrlgvwr_Settings_Tabs( plugin_basename( __FILE__ ) );

		WP_Filesystem();

		if ( $wp_filesystem->exists( $file ) && ( $page->check_file( $file ) || $rrrlgvwr_php_error_path === $file ) ) {
			if ( ! $wp_filesystem->get_contents( $file ) ) {
				return sprintf( esc_html__( "Couldn't open the file %s. Make sure file is exists or is readable.", 'error-log-viewer' ), esc_attr( $file ) );
			} else {
				$wp_filesystem->put_contents( $file, '' );
			}
		} else {
			return sprintf( esc_html__( 'This file %s cannot be cleared.', 'error-log-viewer' ), esc_attr( $file ) );
		}
	}
}

/**
 * Edit .htaccess and create log file into the plugin log directory
 *
 * @param void
 * @return string
 */

if ( ! function_exists( 'rrrlgvwr_edit_htaccess' ) ) {
	function rrrlgvwr_edit_htaccess() {
		global $wp_filesystem;
		$file   = get_home_path() . '.htaccess'; /* Path to the .htaccess file */
		$string = PHP_EOL . '# log php errors' . PHP_EOL . 'php_flag  log_errors on' . PHP_EOL . 'php_flag  log_errors on' . PHP_EOL . 'php_value error_log ' . plugin_dir_path( __FILE__ ) . 'log/php-errors.log' . PHP_EOL;
		/* Check is .htaccess writable */
		if ( @is_writable( $file ) ) {
			/* Check is .htacces already containes required string */
			WP_Filesystem();
			if ( false === strstr( $wp_filesystem->get_contents( $file ), $string ) ) {
				$htaccess = $wp_filesystem->get_contents( $file );
				$wp_filesystem->put_contents( $file, $htaccess . PHP_EOL . $string );

				if ( @is_writable( plugin_dir_path( __FILE__ ) ) ) {
					if ( ! $wp_filesystem->exists( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log' ) ) {
						$wp_filesystem->mkdir( plugin_dir_path( __FILE__ ) . 'log' );
						$wp_filesystem->put_contents( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log', '', 0755 );
					}
				} else {
					return esc_html__( "File '.htaccess' contains the following code, but plugin couldn't create 'php-errors.log', change permissions to the 'wp-content' or 'error-log-viewer' plugin directory, or create this file by yourself", 'error-log-viewer' );
				}
			} else {
				/* Check if writable plugin folder and create log file */
				if ( @is_writable( plugin_dir_path( __FILE__ ) ) ) {
					if ( ! $wp_filesystem->exists( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log' ) ) {
						$wp_filesystem->mkdir( plugin_dir_path( __FILE__ ) . 'log' );
						$wp_filesystem->put_contents( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log', '', 0755 );
					}
				} else {
					return esc_html__( "File '.htaccess' contains the following code, but plugin couldn't create 'php-errors.log', change permissions to the 'wp-content' or 'error-log-viewer' plugin directory, or create this file by yourself", 'error-log-viewer' );
				}
				return esc_html__( "File '.htaccess' already contains this code", 'error-log-viewer' );
			}
		} else {
			return esc_html__( "File '.htaccess' isn't available. Please change permissions to the file, or try the next method", 'error-log-viewer' );
		}
	}
}

/**
 * Edit wp-config.php via ini-set and create log file into the plugin log directory
 *
 * @param void
 * @return string
 */

if ( ! function_exists( 'rrrlgvwr_edit_wpconfig_iniset' ) ) {
	function rrrlgvwr_edit_wpconfig_iniset() {
		global $wp_filesystem;
		$file    = get_home_path() . 'wp-config.php';
		$pattern = "/define\(\s?'WP_DEBUG'\s?,\s?(false|true)\s?\);/";
		/* Required string */
		$string_iniset = PHP_EOL . "@ini_set('log_errors','On');" . PHP_EOL . "@ini_set('display_errors','Off');" . PHP_EOL . "@ini_set('error-log-viewer', '" . plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . "php-errors.log');" . PHP_EOL;
		$string_debug  = "define('WP_DEBUG', true);" . PHP_EOL . "define('WP_DEBUG_LOG', true);" . PHP_EOL . "define('WP_DEBUG_DISPLAY', false);" . PHP_EOL . "@ini_set('display_errors', 0);" . PHP_EOL;
		/* Check is wp-config writable*/
		if ( @is_writable( $file ) ) {
			/* Check is wp-config already containes required strings */
			WP_Filesystem();
			$file_content = $wp_filesystem->get_contents( $file );
			if ( strstr( $file_content, $string_iniset ) === false && strstr( $file_content, $string_debug ) === false ) {
				$file_lines = $wp_filesystem->get_contents_array( $file );
				foreach( $file_lines as $line_key => $line ) {
					if ( preg_match( $pattern, $line, $matches ) ) {
						$offset = $line_key;
					} elseif ( preg_match( '#/\* That\'s all, stop editing! Happy publishing\. \*/#', $line ) ) {
						$offset = $line_key;
						break;
					}
				}
				if ( ! empty( $offset ) ) {
					$new_text = array_slice( $file_lines, 0, $offset );
					$new_text[] = $string_iniset;
					$new_text[] = PHP_EOL;
					$count = count( $file_lines );
					$new_text = array_merge( $new_text, array_slice( $file_lines, -( $count - $offset ) ) );
					$wp_filesystem->put_contents( $file, implode( '', $new_text ) );
				}

				/* Check if writable plugin folder and create log file */
				if ( @is_writable( plugin_dir_path( __FILE__ ) ) ) {
					if ( ! $wp_filesystem->exists( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log' ) ) {
						$wp_filesystem->mkdir( plugin_dir_path( __FILE__ ) . 'log' );
						$wp_filesystem->put_contents( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log', '', 0755 );
					}
				} else {
					return esc_html__( "File 'wp-config.php' contains the following code, but plugin couldn't create 'php-errors.log', change permissions to the 'wp-content' or 'error-log-viewer' plugin directory, or create this file by yourself", 'error-log-viewer' );
				}
			} else {
				/* Check if writable plugin folder and create log file */
				if ( @is_writable( plugin_dir_path( __FILE__ ) ) ) {
					if ( ! $wp_filesystem->exists( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log' ) ) {
						$wp_filesystem->mkdir( plugin_dir_path( __FILE__ ) . 'log' );
						$wp_filesystem->put_contents( plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . 'php-errors.log', '', 0755 );
					}
				} else {
					return esc_html__( "File 'wp-config.php' contains the following code, but plugin couldn't create 'php-errors.log', change permissions to the 'wp-content' or 'error-log-viewer' plugin directory, or create this file by yourself", 'error-log-viewer' );
				}
				return esc_html__( "File 'wp-config.php' already contains this code", 'error-log-viewer' );
			}
		} else {
			return esc_html__( "Plugin couldn't open and rewritable 'wp-config.php', change permissions to the file, or try the next method", 'error-log-viewer' );
		}
	}
}
/**
 * Edit wp-config.php and create debug.log into the wp-content directory
 *
 * @param void
 * @return string
 */

if ( ! function_exists( 'rrrlgvwr_edit_wpconfig_debug' ) ) {
	function rrrlgvwr_edit_wpconfig_debug() {
		global $wp_filesystem;
		$file    = get_home_path() . 'wp-config.php';
		$pattern = "/define\(\s?'WP_DEBUG'\s?,\s?(false|true)\s?\);/";
		/* Required string */
		$string_iniset = PHP_EOL . "@ini_set('log_errors','On');" . PHP_EOL . "@ini_set('display_errors','Off');" . PHP_EOL . "@ini_set('error-log-viewer', '" . plugin_dir_path( __FILE__ ) . 'log' . DIRECTORY_SEPARATOR . "php-errors.log');" . PHP_EOL;
		$string_debug  = "define('WP_DEBUG', true);" . PHP_EOL . "define('WP_DEBUG_LOG', true);" . PHP_EOL . "define('WP_DEBUG_DISPLAY', false);" . PHP_EOL . "@ini_set('display_errors', 0);" . PHP_EOL;
		/* Check is wp-config writable*/
		if ( @is_writable( $file ) ) {
			WP_Filesystem();
			$file_content = $wp_filesystem->get_contents( $file );
			/* Check is wp-config already containes required strings */
			if ( false === strstr( $file_content, $string_iniset ) && false === strstr( $file_content, $string_debug ) ) {
				$file_lines = $wp_filesystem->get_contents_array( $file );
				foreach( $file_lines as $line_key => $line ) {
					if ( preg_match( $pattern, $line, $matches ) ) {
						$offset = $line_key;
					} elseif ( preg_match( '#/\* That\'s all, stop editing! Happy publishing\. \*/#', $line ) ) {
						$offset = $line_key;
						break;
					}
				}

				if ( ! empty( $offset ) ) {
					$new_text = array_slice( $file_lines, 0, $offset );
					$new_text[] = $string_debug;
					$new_text[] = PHP_EOL;
					$count = count( $file_lines );
					$new_text = array_merge( $new_text, array_slice( $file_lines, -( $count - $offset ) ) );
					$wp_filesystem->put_contents( $file, implode( '', $new_text ) );
				}

				/* Check if writable wp-content directory and create log file */
				if ( ! $wp_filesystem->put_contents( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'debug.log', '', 0755 ) ) {
					return esc_html__( "File 'wp-config.php' contains the following code, but plugin couldn't create 'debug.log', change permissions to the 'wp-content' directory, or create this file by yourself", 'error-log-viewer' );
				}
			} else {
				/* Check if writable wp-content directory and create log file */
				if ( ! $wp_filesystem->put_contents( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'debug.log', '', 0755 ) ) {
					return esc_html__( "File 'wp-config.php' contains the following code, but plugin couldn't create 'debug.log', change permissions to the 'wp-content' directory, or create this file by yourself", 'error-log-viewer' );
				}
				return esc_html__( "File 'wp-config.php' already contains this code", 'error-log-viewer' );
			}
		} else {
			return esc_html__( "Plugin couldn't open and rewritable 'wp-config.php', change permissions to the file, or try the next method", 'error-log-viewer' );
		}
	}
}

/**
 * Shedule options
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_shedule_activation' ) ) {
	function rrrlgvwr_shedule_activation() {
		if ( ! wp_next_scheduled( 'rrrlgvwr_shedule_event' ) ) {
			wp_schedule_event( time(), 'rrrlgvwr_interval', 'rrrlgvwr_shedule_event' );
		}
	}
}

/**
 * Message content
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_send_log' ) ) {
	function rrrlgvwr_send_log() {
		global $rrrlgvwr_options, $rrrlgvwr_periods;

		if ( empty( $rrrlgvwr_options ) ) {
			rrrlgvwr_settings();
		}

		$subject = sprintf( __( 'Saved file from %s', 'error-log-viewer' ), site_url() );
		$message = array();

		foreach ( $rrrlgvwr_options['file_path'] as $key => $file ) {
			if ( file_exists( $file ) ) {
				$name                = basename( $file );
				$subname             = '_' . $key . '_' . substr( $name, 0, strpos( $name, '.' ) );
				$change_wp_file_size = filesize( $file );

				if ( isset( $rrrlgvwr_options[ 'change_file_size' . $subname ] ) && $change_wp_file_size !== $rrrlgvwr_options[ 'change_file_size' . $subname ] ) {
					$message[] = __( 'During the last', 'error-log-viewer' ) . ' ' . $rrrlgvwr_options['frequency_send'] . ' ' . mb_strtolower( $rrrlgvwr_periods[ $rrrlgvwr_options['hour_day'] ] ) . ' ' . __( 'file', 'error-log-viewer' ) . ' ' . $name . ' ' . __( 'have been changed', 'error-log-viewer' );
				}
				$rrrlgvwr_options[ 'change_file_size' . $subname ] = $change_wp_file_size;
				update_option( 'rrrlgvwr_options', $rrrlgvwr_options );
			}
		}

		if ( isset( $rrrlgvwr_options['to_email'] ) && 'user' === $rrrlgvwr_options['to_email'] ) {
			$user  = get_user_by( 'login', $rrrlgvwr_options['email_user'] );
			$email = $user ? $user->data->user_email : '';
		} else {
			$email = $rrrlgvwr_options['email'];
		}

		if ( empty( $message ) ) {
			$message = __( 'No new errors on your site', 'error-log-viewer' );
		} else {
			$message = implode( "\n", array_unique( $message ) ) . "\n" . __( 'For more information go to the', 'error-log-viewer' ) . ' ' . admin_url( '/admin.php?page=rrrlgvwr-monitor.php' );
		}
		wp_mail( $email, $subject, $message );
	}
}

/**
 * Cron Shedules
 *
 * @param array
 * @return array
 */
if ( ! function_exists( 'rrrlgvwr_interval_schedule' ) ) {
	function rrrlgvwr_interval_schedule( $schedules ) {
		global $rrrlgvwr_options;
		if ( empty( $rrrlgvwr_options ) ) {
			rrrlgvwr_settings();
		}

		$interval                       = $rrrlgvwr_options['frequency_send'] * $rrrlgvwr_options['hour_day'];
		$schedules['rrrlgvwr_interval'] = array(
			'interval' => $interval,
			'display'  => esc_html__( 'Send Email Interval', 'error-log-viewer' ),
		);
		return $schedules;
	}
}

if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	/**
	 * Create class Error_Log_Saved_Files to display saved error log
	 */
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	if ( ! class_exists( 'Error_Log_Saved_Files' ) ) {
		class Error_Log_Saved_Files extends WP_List_Table {
			public $rrrlgvwr_saved_file;

			public function __construct() {
				parent::__construct(
					array(
						'singular' => 'log_file',
						'plural'   => 'log_files',
						'ajax'     => false,
					)
				);
			}

			public function get_columns() {
				return array(
					'cb'    => '<input type="checkbox" />', /* Render a checkbox instead of text */
					'title' => esc_html__( 'Link', 'error-log-viewer' ),
					'date'  => esc_html__( 'Date', 'error-log-viewer' ),
					'size'  => esc_html__( 'Size', 'error-log-viewer' ),
				);
			}

			public function column_default( $item, $column_name ) {
				switch ( $column_name ) {
					case 'title':
					case 'date':
					case 'size':
						return $item[ $column_name ];
					default:
						return print_r( $item, true ); /* Show the whole array for troubleshooting purposes */
				}
			}

			public function column_cb( $item ) {
				return sprintf( '<input type="checkbox" name="rrrlgvwr_check_del[]" value="%s" />', $item['check'] );
			}

			public function no_items() {
				printf( '<i>%s</i>', esc_html__( 'There are no saved files', 'error-log-viewer' ) );
			}

			public function get_sortable_columns() {
				return array(
					'date' => array( 'date', false ),
					'size' => array( 'size', false ),
				);
			}

			public function column_title( $item ) {
				$delete_url = sprintf( 'admin.php?page=rrrlgvwr-monitor.php&saved_logs_action=%1$s&rrrlgvwr_check_del=%2$s', 'delete', $item['check'] );
				/* Build row actions */
				$actions = array(
					'view'   => sprintf( '<a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'saved_logs/%1$s">%2$s</a>', esc_html( $item['name'] ), esc_html__( 'View', 'error-log-viewer' ) ),
					'delete' => sprintf( '<a href="' . wp_nonce_url( $delete_url, 'rrrlgvwr_nonce_name' ) . '">%1$s</a>', esc_html__( 'Delete', 'error-log-viewer' ) ),
				);
				/* Return the title contents */
				return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
			}

			public function get_bulk_actions() {
				return array(
					'delete' => esc_html__( 'Delete', 'error-log-viewer' ),
				);
			}

			public function prepare_items() {
				$columns               = $this->get_columns();
				$hidden                = array();
				$sortable              = $this->get_sortable_columns();
				$this->_column_headers = array( $columns, $hidden, $sortable );

				$per_page    = 5;
				$this->items = $this->rrrlgvwr_saved_file;

				$current_page = $this->get_pagenum();
				$total_items  = count( $this->rrrlgvwr_saved_file );
				$this->items  = array_slice( $this->rrrlgvwr_saved_file, ( ( $current_page - 1 ) * $per_page ), $per_page );

				$this->set_pagination_args(
					array(
						'total_items' => $total_items,
						'per_page'    => $per_page,
					)
				);
			}
		}
	}
}

/**
 * Sending mail about fatal error
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_handle_fatal_error' ) ) {
	function rrrlgvwr_handle_fatal_error() {
		$error = error_get_last();
		if ( null !== $error ) {
			$rrrlgvwr_options  = get_option( 'rrrlgvwr_options' );
			$fatal_error_types = array(
				E_ERROR,
				E_PARSE,
				E_CORE_ERROR,
				E_USER_ERROR,
				E_COMPILE_ERROR,
				E_RECOVERABLE_ERROR,
			);
			if ( isset( $rrrlgvwr_options['send_email'] ) && 1 === intval( $rrrlgvwr_options['send_email'] ) && isset( $error['type'] ) && in_array( $error['type'], $fatal_error_types, true ) ) {
				$subject = __( 'Fatal error on ', 'error-log-viewer' ) . site_url();
				$message = __( 'An unexpected fatal error occurred on the site.', 'error-log-viewer' ) . "\n" .
						   sprintf( __( 'Fatal error: %1$s in %2$s on line %3$d', 'error-log-viewer' ), $error['message'], $error['file'], $error['line'] );

				if ( ! function_exists( 'wp_mail' ) ) {
					require_once ABSPATH . 'wp-includes/pluggable.php';
				}

				if ( isset( $rrrlgvwr_options['to_email'] ) && 'user' === $rrrlgvwr_options['to_email'] ) {
					$user  = get_user_by( 'login', $rrrlgvwr_options['email_user'] );
					$email = $user ? $user->data->user_email : '';
				} else {
					$email = $rrrlgvwr_options['email'];
				}

				wp_mail( $email, $subject, esc_html( $message ) );
			}
		}
	}
}

/**
 * Enqueue script and styles
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_admin_head' ) ) {
	function rrrlgvwr_admin_head() {
		global $rrrlgvwr_options;

		/* css for displaing an icon */
		wp_enqueue_style( 'rrrlgvwr_icon_stylesheet', plugins_url( 'css/icon.css', __FILE__ ), array(), '1.1.2' );

		if ( isset( $_GET['page'] ) && ( 'rrrlgvwr.php' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) || 'rrrlgvwr-monitor.php' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) {
			wp_enqueue_script( 'rrrlgvwr_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), '1.1.2', true );
			wp_enqueue_style( 'rrrlgvwr_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), '1.1.2' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker-style', plugins_url( 'css/jquery-ui.css', __FILE__ ), array(), '1.10.4' );
			bws_enqueue_settings_scripts();

			wp_localize_script(
				'rrrlgvwr_script',
				'rrrlgvwr_confirm',
				array(
					'confirm_filesize' => $rrrlgvwr_options['confirm_filesize'],
					'confirm_mes'      => esc_html__( 'File size is more than 10 Mb. Are you sure you want to see full file?', 'error-log-viewer' ),
					'clear_mes'        => esc_html__( 'Are you sure you want to clear the file?', 'error-log-viewer' ),
				)
			);
		}
	}
}

/**
 * Function to add action links to the plugin menu
 *
 * @param array, file
 * @return array
 */
if ( ! function_exists( 'rrrlgvwr_plugin_action_links' ) ) {
	function rrrlgvwr_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row */
			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
			if ( $file === $this_plugin ) {
				$settings_link = '<a href="admin.php?page=rrrlgvwr.php">' . esc_html__( 'Settings', 'error-log-viewer' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/**
 * Function to add links to the plugin description on the plugins page
 *
 * @param array, tring
 * @return array
 */
if ( ! function_exists( 'rrrlgvwr_register_plugin_links' ) ) {
	function rrrlgvwr_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file === $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=rrrlgvwr.php">' . esc_html__( 'Settings', 'error-log-viewer' ) . '</a>';
				$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/201247209" target="_blank">' . esc_html__( 'FAQ', 'error-log-viewer' ) . '</a>';
				$links[] = '<a href="https://support.bestwebsoft.com">' . esc_html__( 'Support', 'error-log-viewer' ) . '</a>';
			}
		}
		return $links;
	}
}

/**
 * Add admin notices
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_admin_notices' ) ) {
	function rrrlgvwr_admin_notices() {
		global $hook_suffix, $rrrlgvwr_plugin_info;
		if ( 'plugins.php' === $hook_suffix ) {
			bws_plugin_banner_to_settings( $rrrlgvwr_plugin_info, 'rrrlgvwr_options', 'error-log-viewer', 'admin.php?page=rrrlgvwr.php' );
		}
		if ( isset( $_GET['page'] ) && 'rrrlgvwr.php' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			bws_plugin_suggest_feature_banner( $rrrlgvwr_plugin_info, 'rrrlgvwr_options', 'error-log-viewer' );
		}
	}
}

/**
 * Add help tab
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_add_tabs' ) ) {
	function rrrlgvwr_add_tabs() {
		$screen = get_current_screen();
		$args   = array(
			'id'      => 'rrrlgvwr',
			'section' => '201247209',
		);
		bws_help_tab( $screen, $args );
	}
}

/**
 * Deactivate shedule
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_shedule_deactivation' ) ) {
	function rrrlgvwr_shedule_deactivation() {
		wp_clear_scheduled_hook( 'rrrlgvwr_shedule_event' );
	}
}

/**
 * Register uninstall hook
 *
 * @param void
 * @return void
 */
if ( ! function_exists( 'rrrlgvwr_uninstall' ) ) {
	function rrrlgvwr_uninstall() {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'rrrlgvwr_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'rrrlgvwr_options' );
		}

		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

add_action( 'admin_menu', 'rrrlgvwr_admin_menu' );
add_action( 'init', 'rrrlgvwr_init' );
add_action( 'admin_init', 'rrrlgvwr_admin_init' );
/* Plugin Internationalization */
add_action( 'plugins_loaded', 'rrrlgvwr_plugins_loaded' );
/* Enqueue script and style */
add_action( 'admin_enqueue_scripts', 'rrrlgvwr_admin_head' );
/* Function to add action links to the plugin menu. */
add_filter( 'plugin_action_links', 'rrrlgvwr_plugin_action_links', 10, 2 );
/* Function to add links to the plugin description on the plugins page. */
add_filter( 'plugin_row_meta', 'rrrlgvwr_register_plugin_links', 10, 2 );
/* add admin notices */
add_action( 'admin_notices', 'rrrlgvwr_admin_notices' );
/* Activation shedule */
add_action( 'rrrlgvwr_shedule_event', 'rrrlgvwr_send_log' );
/* Cron shedules */
add_filter( 'cron_schedules', 'rrrlgvwr_interval_schedule' );

register_shutdown_function( 'rrrlgvwr_handle_fatal_error' );
register_deactivation_hook( __FILE__, 'rrrlgvwr_shedule_deactivation' );
register_uninstall_hook( __FILE__, 'rrrlgvwr_uninstall' );
