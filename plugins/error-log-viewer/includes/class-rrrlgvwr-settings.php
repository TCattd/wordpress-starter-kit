<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Rrrlgvwr_Settings_Tabs' ) ) {
	class Rrrlgvwr_Settings_Tabs extends Bws_Settings_Tabs {
		private $wp_error_files;

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $rrrlgvwr_options, $rrrlgvwr_plugin_info;

			$tabs = array(
				'settings'		=> array( 'label' => __( 'Settings', 'error-log-viewer' ) ),
				'notifications'	=> array( 'label' => __( 'Notifications', 'error-log-viewer' ) ),
				'misc'			=> array( 'label' => __( 'Misc', 'error-log-viewer' ) )
			);

			parent::__construct( array(
				'plugin_basename'		=> $plugin_basename,
				'plugins_info'			=> $rrrlgvwr_plugin_info,
				'prefix'            => 'rrrlgvwr',
				'default_options'		=> rrrlgvwr_get_default_options(),
				'options'           => $rrrlgvwr_options,
				'is_network_options'	=> is_network_admin(),
				'tabs'              => $tabs,
				'wp_slug'           => 'error-log-viewer',
				'doc_link'          => 'https://bestwebsoft.com/documentation/error-log-viewer/error-log-viewer-user-guide/'
			) );

			$this->wp_error_files = rrrlgvwr_find_log_files( ( '/' === substr( get_home_path(), strlen( get_home_path() )-1 )  ) ? substr( get_home_path(), 0, strlen( get_home_path() )-1 ) : get_home_path() );
		}

		public function save_options() {
			global $rrrlgvwr_periods, $rrrlgvwr_php_error_path;
			$message = $notice = $error = '';

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'rrrlgvwr_settings_nonce' ) ) {
				die( __( 'Security check', 'error-log-viewer' ) );
			} else {
				/* Check for monitor file and search changes for sending email */
				$this->options['count_visible_log']		= 0;
				$this->options['file_path']				= array();
				$this->options['php_error_log_visible']	= ( isset( $_POST['rrrlgvwr_php_visible'] ) ) ? 1 : 0;
				if ( intval( $this->options['php_error_log_visible'] ) === 1 ) {
					$this->options['file_path'][0]		= $rrrlgvwr_php_error_path;
					$this->options['count_visible_log']	+= $this->options['php_error_log_visible'];
				}
				foreach ( $this->wp_error_files as $key => $file ) {
					$name		= str_replace ( substr( $file, 0, strripos( $file, '/' )+1 ), '', $file );
					$subname	= substr( $name, 0, strpos( $name, '.' ) );
					$subname	= $key . "_" . $subname . '_visible';
					if ( is_readable( $file ) ) {
						$this->options[ $subname ] = ( isset( $_POST[ $subname ] ) ) ? 1 : 0;
						if ( $file === $rrrlgvwr_php_error_path && intval( $this->options['php_error_log_visible'] ) === 1 ) {
							continue;
						}
						if ( intval( $this->options[ $subname ] ) === 1 ) {
							$this->options['file_path'][ $key+1 ]	=	$file;
							$this->options['count_visible_log']		+=	$this->options[ $subname ];
						}
					} elseif ( ! is_readable( $file ) && isset( $_POST[ $subname ] ) )
						$error = sprintf( __( "File %s isn't readable, change permissions to the file", 'error-log-viewer' ), esc_attr( $name ) );

				}
				/** Create log if not exists */
				if ( isset( $_POST['rrrlgvwr_create_log'] ) ) {
					switch ( $_POST['rrrlgvwr_create_log'] ) {
						case 'htaccess':
							$create_mes		= rrrlgvwr_edit_htaccess();
							if ( empty( $create_mes ) ) {
								$message	= __( "File '.htaccess' updated successfully and plugin create 'php-errors.log' in plugin log folder", 'error-log-viewer' );
							} else {
								$error		= $create_mes;
							}
							break;
						case 'config_ini_set':
							$create_mes		= rrrlgvwr_edit_wpconfig_iniset();
							if ( empty( $create_mes ) ) {
								$message	= __( "File 'wp-config' updated successfully and plugin create 'php-errors.log' in plugin log folder", 'error-log-viewer' );
							} else {
								$error		= $create_mes;
							}
							break;
						case 'config_debug':
							$create_mes		= rrrlgvwr_edit_wpconfig_debug();
							if ( empty( $create_mes ) ) {
								$message	= __( "File 'wp-config' updated successfully and plugin create 'debug.log' in wp-content directory", 'error-log-viewer' );
							} else {
								$error		= $create_mes;
							}
							break;
					}
				}

				/** Sending email options */
				$this->options['send_email']	= isset( $_POST['rrrlgvwr_send_email'] ) ? 1 : 0;
				$this->options['to_email']	  = in_array( $_POST['rrrlgvwr_to_email'], array( 'user', 'custom' ) ) ? $_POST['rrrlgvwr_to_email'] : $this->options['to_email'];
				$this->options['email_user']	= get_user_by( 'login', sanitize_email( $_POST['rrrlgvwr_email_user'] ) ) ? sanitize_email( $_POST['rrrlgvwr_email_user'] ) : '';

				if ( 1 === intval( $this->options['send_email'] ) && $this->options['file_path'] ) {
					if ( is_email( $_POST['rrrlgvwr_email'] ) ) {
						$this->options['email']				= sanitize_email( $_POST['rrrlgvwr_email'] );
						$this->options['frequency_send']	= absint( sanitize_text_field( $_POST['rrrlgvwr_frequency_send'] ) );
						$this->options['hour_day']			= array_key_exists( $_POST['rrrlgvwr_hour_day'], $rrrlgvwr_periods ) ? absint( sanitize_text_field( $_POST['rrrlgvwr_hour_day'] ) ) : 3600;

						rrrlgvwr_shedule_activation();
					} else {
						$error = __( "Make sure that the email field isn't empty or you wrote an email correctly", 'error-log-viewer' );
					}
				} elseif ( 1 === intval( $this->options['send_email'] ) && array() === $this->options['file_path'] ) {
					rrrlgvwr_shedule_deactivation();
					$error = __( 'Select at least one log file', 'error-log-viewer' );
				} else {
					rrrlgvwr_shedule_deactivation();
				}

				if ( empty( $error ) ) {
					update_option( 'rrrlgvwr_options', $this->options );
					$message .= __( "Settings saved", 'gallery-plugin-pro' );
				}
			}

			return compact( 'message', 'notice', 'error' );
		}

		public function tab_settings() {
			global $rrrlgvwr_php_error_path;

			$error_logging_enabled	= ini_get( 'log_errors' ) && ( ini_get( 'log_errors' ) !== 'Off' );
			$name					= basename( $rrrlgvwr_php_error_path );

			if ( ! $error_logging_enabled ) {
				$php_error_mes = __( 'Error logging on your server is disabled. Try to logging via WordPress function.', 'error-log-viewer' );
			} elseif ( empty( $rrrlgvwr_php_error_path ) ) {
				$php_error_mes = __( 'Error log filename is not set. Try to logging via WordPress function.', 'error-log-viewer' );
			} elseif ( ( strpos( $rrrlgvwr_php_error_path, "/" ) === false ) && ( strpos( $rrrlgvwr_php_error_path, "\\" ) === false ) ) {
				$php_error_mes = sprintf( __( 'The current error_log value %s is not supported. Please change it to an absolute path.', 'error-log-viewer' ), esc_attr( $rrrlgvwr_php_error_path ) );
			} elseif ( ! is_readable( $rrrlgvwr_php_error_path ) ) {
				$php_error_mes = sprintf ( __( 'The log file %s does not exist or is inaccessible.', 'error-log-viewer' ), esc_attr( $rrrlgvwr_php_error_path ) );
			} else {
				$php_error_mes	= $rrrlgvwr_php_error_path;
			} ?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Error Log Viewer Settings', 'error-log-viewer' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<div class="bws_tab_sub_label"><?php esc_html_e( 'PHP Error Log', 'error-log-viewer' ); ?></div>
			<?php if ( $error_logging_enabled && ( ! empty( $rrrlgvwr_php_error_path ) ) && ( is_readable( $rrrlgvwr_php_error_path ) ) ) { ?>
				<table class="form-table">
					<tr>
						<th scope="row" class="th-full">
							<label>
								<input type="checkbox" name="rrrlgvwr_php_visible" <?php checked( $this->options['php_error_log_visible'] ); ?> />
								<?php echo esc_attr( $name ); ?>
							</label>
						</th>
						<td><?php echo esc_html( $php_error_mes ); ?></td>
						<?php if ( file_exists( $rrrlgvwr_php_error_path ) ) { ?>
							<td>
								<?php if ( 0 === filesize( $rrrlgvwr_php_error_path ) )
									esc_html_e( 'The file is empty', 'error-log-viewer' );
								else
									echo rrrlgvwr_file_size( $rrrlgvwr_php_error_path ); ?>
							</td>
							<td>
								<?php esc_html_e( 'Last update', 'error-log-viewer' );
								echo ': ' . gmdate( 'Y-m-d H:i:s', filemtime( $rrrlgvwr_php_error_path ) ); ?>
							</td>
						<?php } ?>
					</tr>
				</table>
			<?php } else { ?>
				<p><?php echo esc_html( $php_error_mes ); ?></p>
			<?php } ?>
			<div class="bws_tab_sub_label"><?php esc_html_e( 'WordPress Error Log', 'error-log-viewer' ); ?></div>
			<?php if ( 0 === count( $this->wp_error_files ) ) { ?>
				<p><?php esc_html_e( "Plugin didn't find log files in your Wordpress directory. You can create log file by yourself or using the plugin.", 'error-log-viewer' ); ?></p>
			<?php }
			if ( 0 === count( $this->wp_error_files ) ) { ?>
				<table class="form-table">
					<tr>
						<th scope="row" class="th-full">
							<span><?php esc_html_e( "Error logging via '.htaccess' using 'php_flag' and 'php_value'", 'error-log-viewer' ); ?></span>
						</th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="rrrlgvwr_create_log" value="htaccess" />
									<?php esc_html_e( "Add the following code in your '.htaccess' file and create 'php-errors.log' file in 'log' directory in the plugin folder", 'error-log-viewer' ); ?>
								</label>
							</fieldset>
							<p class="rrrlgvwr-pre"># log php errors<br>php_flag log_errors on<br>php_flag display_errors off<br>php_value error_log <?php echo dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR . "php-errors.log"; ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row" class="th-full">
							<span>
								<?php esc_html_e( "Error logging via 'wp-config.php' using 'ini_set'", 'error-log-viewer' ); ?>
							</span>
						</th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="rrrlgvwr_create_log" value="config_ini_set" />
									<?php esc_html_e( "Add the following code in the 'wp-config.php' file and create 'php-errors.log' file in 'log' directory in the plugin folder", 'error-log-viewer' ); ?>
								</label>
							</fieldset>
							<p class="rrrlgvwr-pre">@ini_set( 'log_errors','On' );<br>@ini_set( 'display_errors','Off' );<br>@ini_set( 'error-log-viewer', '<?php echo dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR . "php-errors.log"; ?>' );</p>
						</td>
					</tr>
					<tr>
						<th scope="row" class="th-full">
							<span>
								<?php esc_html_e( "Error logging via 'wp-config.php' using 'WP_DEBUG'", 'error-log-viewer' ); ?>
							</span>
						</th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="rrrlgvwr_create_log" value="config_debug" />
									<?php esc_html_e( "Add the following code in the 'wp-config.php' file and create 'debug.log' in the 'wp-content' directory", 'error-log-viewer' ); ?>
								</label>
							</fieldset>
							<p class="rrrlgvwr-pre">define('WP_DEBUG', true);<br>define('WP_DEBUG_LOG', true);<br>define('WP_DEBUG_DISPLAY', false);<br>@ini_set('display_errors', 0);</p>
						</td>
					</tr>
				</table>
				<p>
					<?php esc_html_e( "Files '.htaccess' and 'wp-config.php' are very important for normal working of your site. Please save them necessarily before changes. You can create custom file for logging and edit required file by yourself. See also", 'error-log-viewer' ); ?>:
					<a target="_blank" href="https://codex.wordpress.org/Debugging_in_WordPress"><?php esc_html_e( 'Debugging on WordPress', 'error-log-viewer' ); ?></a>,
					<a target="_blank" href="https://codex.wordpress.org/Editing_wp-config.php"><?php esc_html_e( 'Editing', 'error-log-viewer' ); ?> wp-config.php</a>
				</p>
			<?php } else { ?>
				<table class="form-table">
					<?php foreach ( $this->wp_error_files as $key => $file ) {
						$name		= basename( $file );
						$subname	= $key . '_' . pathinfo( $name, PATHINFO_FILENAME ) . '_visible'; ?>
						<tr>
							<th scope="row" class="th-full">
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $subname );?>" <?php if ( isset( $this->options[ $subname ] ) ) checked( $this->options[ $subname ] ); ?> />
									<?php echo esc_attr( $name ); ?>
								</label>
							</th>
							<td><?php echo esc_html( $file ); ?></td>
							<td>
								<?php if ( 0 === filesize( $file ) )
									esc_html_e( 'The file is empty', 'error-log-viewer' );
								else
									echo rrrlgvwr_file_size( $file ); ?>
							</td>
							<td>
								<?php esc_html_e( 'Last update', 'error-log-viewer' );
								echo ': ' . gmdate( 'Y-m-d H:i:s', filemtime( $file ) ); ?>
							</td>
						</tr>
					<?php } ?>
				</table>
				<?php 
			} 
			?>
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rrrlgvwr_settings_nonce' ); ?>" />
			<?php 
		}

		public function tab_notifications() {
			global $rrrlgvwr_periods; ?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Email Notification Settings', 'error-log-viewer' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Email Notification', 'error-log-viewer' ); ?></th>
					<td>
						<input class="bws_option_affect" data-affect-show="#rrrlgvwr-notifications-table" type="checkbox" name="rrrlgvwr_send_email" value="1" <?php checked( $this->options['send_email'] ); ?> />
						<span class="bws_info"><?php esc_html_e( 'Enable to receive email notifications.', 'error-log-viewer' ); ?></span>
					</td>
				</tr>
			</table>
			<table id="rrrlgvwr-notifications-table" class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( "Send Email Notifications to", 'error-log-viewer' ); ?></th>
					<td>
						<fieldset>
							<label><input class="bws_option_affect" data-affect-show="#rrrlgvwr-user-email" data-affect-hide="#rrrlgvwr-custom-email" type="radio" name="rrrlgvwr_to_email" value="user" <?php checked( 'user', $this->options['to_email'] ); ?> /> <?php esc_html_e( 'User', 'error-log-viewer' ); ?></label><br />
							<div id="rrrlgvwr-user-email">
								<select name="rrrlgvwr_email_user">
									<option disabled><?php esc_html_e( "Select a username", 'error-log-viewer' ); ?></option>
									<?php $rrrlgvwr_userslogin = get_users( 'blog_id=' . $GLOBALS['blog_id'] . '&role=administrator' );
									foreach ( $rrrlgvwr_userslogin as $key => $value ) {
										if ( $value->data->user_email !== '' ) { ?>
											<option value="<?php echo esc_attr( $value->data->user_login ); ?>" <?php selected( $this->options['email_user'], $value->data->user_login ); ?>><?php echo esc_attr( $value->data->user_login ); ?></option>
										<?php }
									} ?>
								</select>
							</div>
							<label><input class="bws_option_affect" data-affect-hide="#rrrlgvwr-user-email" data-affect-show="#rrrlgvwr-custom-email" type="radio" name="rrrlgvwr_to_email" value="custom" <?php checked( 'custom', $this->options['to_email'] ); ?> /> <?php esc_html_e( 'Custom email', 'error-log-viewer' ); ?></label><br />
							<div id="rrrlgvwr-custom-email">
								<input type="text" name="rrrlgvwr_email" value="<?php echo esc_attr( $this->options['email'] ); ?>" maxlength="500" /><br />
							</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Send Email', 'error-log-viewer' ); ?></th>
					<td>
						<fieldset>
							<span><?php esc_html_e( 'every', 'error-log-viewer'); ?></span>
							<input type="number" class="rrrlgvwr-middle" min="1" name="rrrlgvwr_frequency_send" value="<?php echo esc_attr( $this->options['frequency_send'] ); ?>" />
							<select name="rrrlgvwr_hour_day">
								<?php foreach ( $rrrlgvwr_periods as $seconds => $period ) { ?>
									<option value="<?php echo esc_html( $seconds ); ?>" <?php selected( $seconds, $this->options['hour_day'] ); ?>><?php echo  $period ?></option>
								<?php } ?>
							</select>
						</fieldset>
					</td>
				</tr>
			</table>
		<?php }

		public function check_file( $file ){
			if( ! in_array( $file, $this->wp_error_files ) ) {
				return false;
			}
			return true;
		}
	}
} ?>