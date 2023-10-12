<?php
/**
 * Deprecated functions for BestWebSoft plugins
 */

/**
 * Function add BWS Plugins page
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @return void
 */
if ( ! function_exists( 'bws_general_menu' ) ) {
	function bws_general_menu() {
		global $menu, $bws_general_menu_exist;

		if ( ! $bws_general_menu_exist ) {
			/* we check also menu exist in global array as in old plugins $bws_general_menu_exist variable not exist */
			foreach ( $menu as $value_menu ) {
				if ( 'bws_panel' === $value_menu[2] ) {
					$bws_general_menu_exist = true;
					return;
				}
			}

			add_menu_page( 'BWS Panel', 'BWS Panel', 'manage_options', 'bws_panel', 'bws_add_menu_render', 'none', '1001' );

			add_submenu_page( 'bws_panel', esc_html__( 'Plugins', 'bestwebsoft' ), esc_html__( 'Plugins', 'bestwebsoft' ), 'manage_options', 'bws_panel', 'bws_add_menu_render' );
			add_submenu_page( 'bws_panel', esc_html__( 'Themes', 'bestwebsoft' ), esc_html__( 'Themes', 'bestwebsoft' ), 'manage_options', 'bws_themes', 'bws_add_menu_render' );
			add_submenu_page( 'bws_panel', esc_html__( 'System Status', 'bestwebsoft' ), esc_html__( 'System Status', 'bestwebsoft' ), 'manage_options', 'bws_system_status', 'bws_add_menu_render' );

			$bws_general_menu_exist = true;
		}
	}
}

/**
 * Function process submit on the `Go Pro` tab
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo Remove function after 01.01.2021
 */
if ( ! function_exists( 'bws_go_pro_tab_check' ) ) {
	function bws_go_pro_tab_check( $plugin_basename, $plugin_options_name = false, $is_network_option = false ) {
		global $bstwbsftwppdtplgns_options;
		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
		}
		if ( ! isset( $bstwbsftwppdtplgns_options['deprecated_function']['bws_go_pro_tab_check'] ) ) {
			$get_debug_backtrace = debug_backtrace();
			$file                = ( ! empty( $get_debug_backtrace[0]['file'] ) ) ? $get_debug_backtrace[0]['file'] : '';
			$bstwbsftwppdtplgns_options['deprecated_function']['bws_go_pro_tab_check'] = array(
				'file' => $file,
			);
			if ( is_multisite() ) {
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			} else {
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			}
		}
	}
}

/**
 * Function display 'Custom code' tab
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo Remove function after 01.01.2021
 */
if ( ! function_exists( 'bws_custom_code_tab' ) ) {
	function bws_custom_code_tab() {
		global $bstwbsftwppdtplgns_options;
		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
		}
		if ( ! isset( $bstwbsftwppdtplgns_options['deprecated_function']['bws_custom_code_tab'] ) ) {
			$get_debug_backtrace = debug_backtrace();
			$file                = ( ! empty( $get_debug_backtrace[0]['file'] ) ) ? $get_debug_backtrace[0]['file'] : '';
			$bstwbsftwppdtplgns_options['deprecated_function']['bws_custom_code_tab'] = array(
				'file' => $file,
			);
			if ( is_multisite() ) {
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			} else {
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			}
		}
	}
}

/**
 * Function check license key for Pro plugins version
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo Remove function after 01.01.2021
 */
if ( ! function_exists( 'bws_check_pro_license' ) ) {
	function bws_check_pro_license( $plugin_basename, $trial_plugin = false ) {
		global $bstwbsftwppdtplgns_options;
		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
		}
		if ( ! isset( $bstwbsftwppdtplgns_options['deprecated_function']['bws_custom_code_tab'] ) ) {
			$get_debug_backtrace = debug_backtrace();
			$file                = ( ! empty( $get_debug_backtrace[0]['file'] ) ) ? $get_debug_backtrace[0]['file'] : '';
			$bstwbsftwppdtplgns_options['deprecated_function']['bws_custom_code_tab'] = array(
				'file' => $file,
			);
			if ( is_multisite() ) {
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			} else {
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			}
		}
	}
}

/**
 * Function display block for checking license key for Pro plugins version
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
 */
if ( ! function_exists( 'bws_check_pro_license_form' ) ) {
	function bws_check_pro_license_form( $plugin_basename ) {
		global $bstwbsftwppdtplgns_options;
		$license_key = ( isset( $bstwbsftwppdtplgns_options[ $plugin_basename ] ) ) ? $bstwbsftwppdtplgns_options[ $plugin_basename ] : ''; ?>
		<div class="clear"></div>
		<form method="post" action="">
			<p><?php echo esc_html_e( 'If necessary, you can check if the license key is correct or reenter it in the field below. You can find your license key on your personal page - Client Area - on our website', 'bestwebsoft' ) . ' <a href="https://bestwebsoft.com/client-area">https://bestwebsoft.com/client-area</a> ' . esc_html__( '(your username is the email address specified during the purchase). If necessary, please submit "Lost your password?" request.', 'bestwebsoft' ); ?></p>
			<p>
				<input type="text" maxlength="100" name="bws_license_key" value="<?php echo esc_attr( $license_key ); ?>" />
				<input type="hidden" name="bws_license_submit" value="submit" />
				<input type="submit" class="button" value="<?php esc_html_e( 'Check license key', 'bestwebsoft' ); ?>" />
				<?php wp_nonce_field( $plugin_basename, 'bws_license_nonce_name' ); ?>
			</p>
		</form>
		<?php
	}
}

/**
 * Function process submit on the `Go Pro` tab for TRIAL
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
 */
if ( ! function_exists( 'bws_go_pro_from_trial_tab' ) ) {
	function bws_go_pro_from_trial_tab( $plugin_info, $plugin_basename, $page, $link_slug, $link_key, $link_pn, $trial_license_is_set = true ) {
		global $wp_version, $bstwbsftwppdtplgns_options;
		$bws_license_key = ( isset( $_POST['bws_license_key'] ) ) ? sanitize_text_field( wp_unslash( $_POST['bws_license_key'] ) ) : '';
		if ( $trial_license_is_set ) {
			?>
			<form method="post" action="">
				<p>
					<?php printf( esc_html__( 'In order to continue using the plugin it is necessary to buy a %s license.', 'bestwebsoft' ), esc_html( '<a href="https://bestwebsoft.com/products/wordpress/plugins/' . $link_slug . '/?k=' . $link_key . '&amp;pn=' . $link_pn . '&amp;v=' . $plugin_info['Version'] . '&amp;wp_v=' . $wp_version . '" target="_blank" title="' . $plugin_info['Name'] . '">Pro</a>' ) ); ?> <?php esc_html_e( 'After that, you can activate it by entering your license key.', 'bestwebsoft' ); ?>
					<br />
					<span class="bws_info">
						<?php esc_html_e( 'License key can be found in the', 'bestwebsoft' ); ?>
						<a href="https://bestwebsoft.com/wp-login.php">Client Area</a>
						<?php esc_html_e( '(your username is the email address specified during the purchase).', 'bestwebsoft' ); ?>
					</span>
				</p>
				<?php
				if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $plugin_basename ]['count'] ) &&
					'5' < $bstwbsftwppdtplgns_options['go_pro'][ $plugin_basename ]['count'] &&
					$bstwbsftwppdtplgns_options['go_pro'][ $plugin_basename ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) {
					?>
					<p>
						<input disabled="disabled" type="text" name="bws_license_key" value="" />
						<input disabled="disabled" type="submit" class="button-primary" value="<?php esc_html_e( 'Activate', 'bestwebsoft' ); ?>" />
					</p>
					<p><?php esc_html_e( 'Unfortunately, you have exceeded the number of available tries per day.', 'bestwebsoft' ); ?></p>
				<?php } else { ?>
					<p>
						<input type="text" maxlength="100" name="bws_license_key" value="" />
						<input type="hidden" name="bws_license_plugin" value="<?php echo esc_attr( $plugin_basename ); ?>" />
						<input type="hidden" name="bws_license_submit" value="submit" />
						<input type="submit" class="button-primary" value="<?php esc_html_e( 'Activate', 'bestwebsoft' ); ?>" />
						<?php wp_nonce_field( $plugin_basename, 'bws_license_nonce_name' ); ?>
					</p>
				<?php } ?>
			</form>
			<?php
		} else {
			$page_url = esc_url( self_admin_url( 'admin.php?page=' . $page ) );
			?>
			<p><?php esc_html_e( 'Congratulations! The Pro license of the plugin is activated successfully.', 'bestwebsoft' ); ?></p>
			<p>
				<?php esc_html_e( 'Please, go to', 'bestwebsoft' ); ?> <a href="<?php echo esc_url( $page_url ); ?>"><?php esc_html_e( 'the setting page', 'bestwebsoft' ); ?></a>
			</p>
			<?php
		}
	}
}


/**
 * Function display block for restoring default product settings
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
 */
if ( ! function_exists( 'bws_form_restore_default_settings' ) ) {
	function bws_form_restore_default_settings( $plugin_basename, $change_permission_attr = '' ) {
		?>
		<form method="post" action="">
			<p><?php esc_html_e( 'Restore all plugin settings to defaults', 'bestwebsoft' ); ?></p>
			<p>
				<input <?php echo esc_html( $change_permission_attr ); ?> type="submit" class="button" value="<?php esc_html_e( 'Restore settings', 'bestwebsoft' ); ?>" />
			</p>
			<input type="hidden" name="bws_restore_default" value="submit" />
			<?php wp_nonce_field( $plugin_basename, 'bws_settings_nonce_name' ); ?>
		</form>
		<?php
	}
}

/**
 * Function display GO PRO tab
 *
 * @deprecated 1.9.8 (15.12.2016)
 * @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
 */
if ( ! function_exists( 'bws_go_pro_tab_show' ) ) {
	function bws_go_pro_tab_show( $bws_hide_premium_options_check, $plugin_info, $plugin_basename, $page, $pro_page, $bws_license_plugin, $link_slug, $link_key, $link_pn, $pro_plugin_is_activated = false, $trial_days_number = false ) {
		global $wp_version, $bstwbsftwppdtplgns_options;
		$bws_license_key = ( isset( $_POST['bws_license_key'] ) ) ? sanitize_text_field( wp_unslash( $_POST['bws_license_key'] ) ) : '';
		if ( $pro_plugin_is_activated ) {
			$page_url = esc_url( self_admin_url( 'admin.php?page=' . $pro_page ) );
			?>
			<p><?php esc_html_e( 'Congratulations! Pro version of the plugin is  installed and activated successfully.', 'bestwebsoft' ); ?></p>
			<p>
				<?php esc_html_e( 'Please, go to', 'bestwebsoft' ); ?> <a href="<?php echo esc_url( $page_url ); ?>"><?php esc_html_e( 'the setting page', 'bestwebsoft' ); ?></a>
			</p>
			<?php
		} else {
			if ( $bws_hide_premium_options_check ) {
				?>
				<form method="post" action="">
					<p>
						<input type="hidden" name="bws_hide_premium_options_submit" value="submit" />
						<input type="submit" class="button" value="<?php esc_html_e( 'Show Pro features', 'bestwebsoft' ); ?>" />
						<?php wp_nonce_field( $plugin_basename, 'bws_license_nonce_name' ); ?>
					</p>
				</form>
			<?php } ?>
			<form method="post" action="">
				<p>
					<?php esc_html_e( 'Enter your license key to install and activate', 'bestwebsoft' ); ?>
					<a href="<?php echo esc_url( 'https://bestwebsoft.com/products/wordpress/plugins/' . $link_slug . '/?k=' . $link_key . '&pn=' . $link_pn . '&v=' . $plugin_info['Version'] . '&wp_v=' . $wp_version ); ?>" target="_blank" title="<?php echo esc_html( $plugin_info['Name'] ); ?> Pro">Pro</a>
					<?php esc_html_e( 'version of the plugin.', 'bestwebsoft' ); ?><br />
					<span class="bws_info">
						<?php esc_html_e( 'License key can be found in the', 'bestwebsoft' ); ?>
						<a href="https://bestwebsoft.com/wp-login.php">Client Area</a>
						<?php esc_html_e( '(your username is the email address specified during the purchase).', 'bestwebsoft' ); ?>
					</span>
				</p>
				<?php
				if ( false !== $trial_days_number ) {
					$trial_days_number = esc_html__( 'or', 'bestwebsoft' ) . ' <a href="https://bestwebsoft.com/products/wordpress/plugins/' . esc_attr( $link_slug ) . '/trial/" target="_blank">' . esc_html( sprintf( esc_html__( 'Start Your Free %s-Day Trial Now', 'bestwebsoft' ), $trial_days_number ) ) . '</a>';
				}
				if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] ) &&
					'5' < $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] &&
					$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) {
					?>
					<p>
						<input disabled="disabled" type="text" name="bws_license_key" value="<?php echo esc_attr( $bws_license_key ); ?>" />
						<input disabled="disabled" type="submit" class="button-primary" value="<?php esc_html_e( 'Activate', 'bestwebsoft' ); ?>" />
						<?php
						if ( false !== $trial_days_number ) {
							echo esc_html( $trial_days_number );
						}
						?>
					</p>
					<p><?php esc_html_e( 'Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.', 'bestwebsoft' ); ?></p>
				<?php } else { ?>
					<p>
						<input type="text" maxlength="100" name="bws_license_key" value="<?php echo esc_attr( $bws_license_key ); ?>" />
						<input type="hidden" name="bws_license_plugin" value="<?php echo esc_attr( $bws_license_plugin ); ?>" />
						<input type="hidden" name="bws_license_submit" value="submit" />
						<input type="submit" class="button-primary" value="<?php esc_html_e( 'Activate', 'bestwebsoft' ); ?>" />
						<?php
						if ( false !== $trial_days_number ) {
							echo esc_html( $trial_days_number );
						}
						wp_nonce_field( $plugin_basename, 'bws_license_nonce_name' );
						?>
					</p>
				<?php } ?>
			</form>
			<?php
		}
	}
}

/**
 * Function display GO PRO Banner (inline in 'admin_notices' action )
 *
 * @deprecated 2.2.5 (29.11.2019)
 * @todo Remove notice after 01.12.2021
 */
if ( ! function_exists( 'bws_plugin_banner' ) ) {
	function bws_plugin_banner( $plugin_info, $this_banner_prefix, $link_slug, $link_key, $link_pn, $banner_url_or_slug ) {
		/* the function is not longer use, but we need to store it */
	}
}

/**
 * Function display timeout PRO Banner (inline in 'admin_notices' action )
 *
 * @deprecated 2.2.5 (29.11.2019)
 * @todo Remove notice after 01.12.2021
 */
if ( ! function_exists( 'bws_plugin_banner_timeout' ) ) {
	function bws_plugin_banner_timeout( $plugin_key, $plugin_prefix, $plugin_name, $banner_url_or_slug = false ) {
		/* the function is not longer use, but we need to store it */
	}
}
