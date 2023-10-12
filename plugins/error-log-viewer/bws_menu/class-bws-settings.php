<?php
/**
 * Displays the content on the plugin settings page
 *
 * @package BestWebSoft
 * @since 1.9.8
 */

if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
	class Bws_Settings_Tabs {
		private $tabs;
		private $pro_plugin_is_activated = false;
		private $custom_code_args        = array();
		private $bws_plugin_link         = '';

		public $plugin_basename;
		public $prefix;
		public $wp_slug;

		public $options;
		public $default_options;
		public $is_network_options;
		public $plugins_info  = array();
		public $hide_pro_tabs = false;
		public $demo_data;

		public $is_pro = false;
		public $pro_page;
		public $bws_license_plugin;
		public $link_key;
		public $link_pn;
		public $is_trial = false;
		public $licenses;
		public $trial_days;
		public $bws_hide_pro_option_exist = true;

		public $forbid_view            = false;
		public $change_permission_attr = '';

		public $version;
		public $upload_dir;
		public $all_plugins;
		public $is_multisite;

		public $doc_link;
		public $doc_video_link;

		/**
		 * Constructor.
		 *
		 * The child class should call this constructor from its own constructor to override
		 * the default $args.
		 *
		 * @access public
		 *
		 * @param array|string $args
		 */
		public function __construct( $args = array() ) {
			global $wp_version;

			$args = wp_parse_args(
				$args,
				array(
					'plugin_basename'    => '',
					'prefix'             => '',
					'plugins_info'       => array(),
					'default_options'    => array(),
					'options'            => array(),
					'is_network_options' => false,
					'tabs'               => array(),
					'doc_link'           => '',
					'doc_video_link'     => '',
					'wp_slug'            => '',
					'demo_data'          => false,
					/* if this is free version and pro exist */
					'link_key'           => '',
					'link_pn'            => '',
					'trial_days'         => false,
					'licenses'           => array(),
				)
			);

			$args['plugins_info']['Name'] = str_replace( ' by BestWebSoft', '', $args['plugins_info']['Name'] );

			$this->plugin_basename = $args['plugin_basename'];
			$this->prefix          = $args['prefix'];
			$this->plugins_info    = $args['plugins_info'];
			$this->options         = $args['options'];
			$this->default_options = $args['default_options'];
			$this->wp_slug         = $args['wp_slug'];
			$this->demo_data       = $args['demo_data'];

			$this->tabs               = $args['tabs'];
			$this->is_network_options = $args['is_network_options'];

			$this->doc_link       = $args['doc_link'];
			$this->doc_video_link = $args['doc_video_link'];

			$this->link_key   = $args['link_key'];
			$this->link_pn    = $args['link_pn'];
			$this->trial_days = $args['trial_days'];
			$this->licenses   = $args['licenses'];

			$this->pro_page = $this->bws_license_plugin = '';
			/* get $bws_plugins */
			require dirname( __FILE__ ) . '/product_list.php';
			if ( isset( $bws_plugins[ $this->plugin_basename ] ) ) {
				if ( isset( $bws_plugins[ $this->plugin_basename ]['pro_settings'] ) ) {
					$this->pro_page           = $bws_plugins[ $this->plugin_basename ]['pro_settings'];
					$this->bws_license_plugin = $bws_plugins[ $this->plugin_basename ]['pro_version'];
				}

				$this->bws_plugin_link = substr( $bws_plugins[ $this->plugin_basename ]['link'], 0, strpos( $bws_plugins[ $this->plugin_basename ]['link'], '?' ) );

				if ( ! empty( $this->link_key ) && ! empty( $this->link_pn ) ) {
					$this->bws_plugin_link .= '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info['Version'] . '&wp_v=' . $wp_version;
				}
			}

			$this->hide_pro_tabs = bws_hide_premium_options_check( $this->options );
			$this->version       = '1.0.0';
			$this->is_multisite  = is_multisite();

			if ( empty( $this->pro_page ) && array_key_exists( 'license', $this->tabs ) ) {
				$this->is_pro                                        = true;
				$this->licenses[ $this->plugins_info['TextDomain'] ] = array(
					'name'     => $this->plugins_info['Name'],
					'slug'     => $this->plugins_info['TextDomain'],
					'basename' => $this->plugin_basename,
				);
			} else {
				$this->licenses[ $this->plugins_info['TextDomain'] ] = array(
					'name'         => $this->plugins_info['Name'],
					'slug'         => $this->plugins_info['TextDomain'],
					'pro_slug'     => substr( $this->bws_license_plugin, 0, stripos( $this->bws_license_plugin, '/' ) ),
					'basename'     => $this->plugin_basename,
					'pro_basename' => $this->bws_license_plugin,
				);
			}
		}

		/**
		 * Displays the content of the "Settings" on the plugin settings page
		 *
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_content() {
			global $bstwbsftwppdtplgns_options;
			if ( array_key_exists( 'custom_code', $this->tabs ) ) {
				/* get args for `custom code` tab */
				$this->get_custom_code();
			}

			$save_results = $this->save_all_tabs_options();

			$this->display_messages( $save_results );
			if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				bws_form_restore_default_confirm( $this->plugin_basename );
			} elseif ( isset( $_POST['bws_handle_demo'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				$this->demo_data->bws_demo_confirm();
			} else {
				bws_show_settings_notice(); ?>
				<form class="bws_form" method="post" action="" enctype="multipart/form-data">
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content" style="position: relative;">
								<?php $this->display_tabs(); ?>
							</div><!-- #post-body-content -->
							<div id="postbox-container-1" class="postbox-container">
								<div class="meta-box-sortables ui-sortable">
									<div id="submitdiv" class="postbox">
										<h3 class="hndle"><?php esc_html_e( 'Information', 'bestwebsoft' ); ?></h3>
										<div class="inside">
											<div class="submitbox" id="submitpost">
												<div id="minor-publishing">
													<div id="misc-publishing-actions">
														<?php
														/**
														 * Action - Display additional content for #misc-publishing-Actions
														 */
														do_action( __CLASS__ . '_information_postbox_top' );
														?>
														<?php
														if ( $this->is_pro ) {
															if ( isset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $this->plugin_basename ] ) || empty( $bstwbsftwppdtplgns_options['time_out'] ) || ! array_key_exists( $this->plugin_basename, $bstwbsftwppdtplgns_options['time_out'] ) ) {
																$license_type   = 'Pro';
																$license_status = __( 'Inactive', 'bestwebsoft' ) . ' <a href="#' . $this->prefix . '_license_tab" class="bws_trigger_tab_click">' . __( 'Learn More', 'bestwebsoft' ) . '</a>';
															} else {
																$finish = strtotime( $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] );
																$today  = strtotime( gmdate( 'm/d/Y' ) );
																if ( isset( $bstwbsftwppdtplgns_options['trial'][ $this->plugin_basename ] ) ) {
																	$license_type = 'Trial Pro';

																	if ( $finish < $today ) {
																		$license_status = __( 'Expired', 'bestwebsoft' );
																	} else {
																		$daysleft       = floor( ( $finish - $today ) / ( 60 * 60 * 24 ) );
																		$license_status = sprintf( __( '%s day(-s) left', 'bestwebsoft' ), $daysleft );
																	}
																	$license_status .= '. <a target="_blank" href="' . esc_url( $this->plugins_info['PluginURI'] ) . '">' . __( 'Upgrade to Pro', 'bestwebsoft' ) . '</a>';
																} else {
																	$license_type = isset( $bstwbsftwppdtplgns_options['nonprofit'][ $this->plugin_basename ] ) ? 'Nonprofit Pro' : 'Pro';
																	if ( ! empty( $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] ) && $finish < $today ) {
																		$license_status = sprintf( __( 'Expired on %s', 'bestwebsoft' ), $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] ) . '. <a target="_blank" href="https://support.bestwebsoft.com/entries/53487136">' . __( 'Renew Now', 'bestwebsoft' ) . '</a>';
																	} else {
																		$license_status = __( 'Active', 'bestwebsoft' );
																	}
																}
															}
															?>
															<div class="misc-pub-section">
																<strong><?php esc_html_e( 'License', 'bestwebsoft' ); ?>:</strong> <?php echo esc_attr( $license_type ); ?>
															</div>
															<div class="misc-pub-section">
																<strong><?php esc_html_e( 'Status', 'bestwebsoft' ); ?>:</strong> <?php echo esc_attr( $license_status ); ?>
															</div><!-- .misc-pub-section -->
														<?php } ?>
														<div class="misc-pub-section">
															<strong><?php esc_html_e( 'Version', 'bestwebsoft' ); ?>:</strong> <?php echo esc_attr( $this->plugins_info['Version'] ); ?>
														</div><!-- .misc-pub-section -->
														<?php
														/**
														 * Action - Display additional content for #misc-publishing-Actions
														 */
														do_action( __CLASS__ . '_information_postbox_bottom' );
														?>
													</div>
													<div class="clear"></div>
												</div>
												<div id="major-publishing-Actions">
													<div id="publishing-Action">
														<input type="hidden" name="<?php echo esc_attr( $this->prefix ); ?>_form_submit" value="submit" />
														<input id="bws-submit-button" type="submit" class="button button-primary button-large" value="<?php esc_html_e( 'Save Changes', 'bestwebsoft' ); ?>" />
														<?php wp_nonce_field( $this->plugin_basename, 'bws_nonce_name' ); ?>
													</div>
													<div class="clear"></div>
												</div>
											</div>
										</div>
									</div>
									<?php
									/**
									 * Action - Display custom metabox
									 */
									do_action( __CLASS__ . '_display_metabox' );

									if ( function_exists( 'bws_affiliate_postbox' ) ) {
										bws_affiliate_postbox();
									}
									?>
								</div>
							</div>
							<div id="postbox-container-2" class="postbox-container">
								<?php
								/**
								 * Action - Display additional content for #postbox-container-2
								 */
								do_action( __CLASS__ . '_display_second_postbox' );
								?>
								<div class="submit">
									<input type="submit" class="button button-primary button-large" value="<?php esc_html_e( 'Save Changes', 'bestwebsoft' ); ?>" />
									<?php wp_nonce_field( $this->plugin_basename, 'bws_nonce_name' ); ?>
								</div>
								<?php
								if ( ! empty( $this->wp_slug ) ) {
									bws_plugin_reviews_block( $this->plugins_info['Name'], $this->wp_slug );}
								?>
							</div>
						</div>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Displays the Tabs
		 *
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_tabs() {
			?>
			<div id="bws_settings_tabs_wrapper">
				<ul id="bws_settings_tabs">
					<?php $this->display_tabs_list(); ?>
				</ul>
				<?php $this->display_tabs_content(); ?>
				<div class="clear"></div>
				<input type="hidden" name="bws_active_tab" value="<?php
					if ( isset( $_REQUEST['bws_active_tab'] ) ) {
						echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['bws_active_tab'] ) ) );
					}
				?>" />
			</div>
			<?php
		}

		/**
		 * Displays the list of tabs
		 *
		 * @access private
		 * @return void
		 */
		private function display_tabs_list() {
			foreach ( $this->tabs as $tab_slug => $data ) {
				if ( ! empty( $data['is_pro'] ) && $this->hide_pro_tabs ) {
					continue;
				}
				$tab_class = 'bws-tab-' . $tab_slug;
				if ( ! empty( $data['is_pro'] ) ) {
					$tab_class .= ' bws_pro_tab';
				}
				if ( ! empty( $data['class'] ) ) {
					$tab_class .= ' ' . $data['class'];
				}
				?>
				<li class="<?php echo esc_attr( $tab_class ); ?>" data-slug="<?php echo esc_attr( $tab_slug ); ?>">
					<a href="#<?php echo esc_attr( $this->prefix ); ?>_<?php echo esc_attr( $tab_slug ); ?>_tab">
						<span><?php echo esc_html( $data['label'] ); ?></span>
					</a>
				</li>
				<?php
			}
		}

		/**
		 * Displays the content of tabs
		 *
		 * @access private
		 * @param  string $tab_slug
		 * @return void
		 */
		public function display_tabs_content() {
			foreach ( $this->tabs as $tab_slug => $data ) {
				if ( ! empty( $data['is_pro'] ) && $this->hide_pro_tabs ) {
					continue;
				}
				?>
				<div class="bws_tab ui-tabs-panel ui-widget-content ui-corner-bottom" id="<?php echo esc_attr( $this->prefix . '_' . $tab_slug . '_tab' ); ?>" aria-labelledby="ui-id-2" role="tabpanel" aria-hidden="false" style="display: block;">
					<?php
					$tab_slug = str_replace( '-', '_', $tab_slug );
					if ( method_exists( $this, 'tab_' . $tab_slug ) ) {
						call_user_func( array( $this, 'tab_' . $tab_slug ) );
						do_action_ref_array( __CLASS__ . '_after_tab_' . $tab_slug, array( &$this ) );
					}
					?>
				</div>
				<?php
			}
		}

		/**
		 * Save all options from all tabs and display errors\messages
		 *
		 * @access public
		 * @param  void
		 * @return array
		 */
		public function save_all_tabs_options() {
			$message = $notice = $error = '';
			/* Restore default settings */
			if ( isset( $_POST['bws_restore_confirm'] ) && check_admin_referer( $this->plugin_basename, 'bws_settings_nonce_name' ) ) {
				$this->restore_options();
				$message = __( 'All plugin settings were restored.', 'bestwebsoft' );
				/* Go Pro - check license key */
			} elseif ( isset( $_POST['bws_license_submit'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				$result = $this->save_options_license_key();
				if ( ! empty( $result['empty_field_error'] ) ) {
					$error = $result['empty_field_error'];
				}
				if ( ! empty( $result['error'] ) ) {
					$error = $result['error'];
				}
				if ( ! empty( $result['message'] ) ) {
					$message = $result['message'];
				}
				if ( ! empty( $result['notice'] ) ) {
					$notice = $result['notice'];
				}
				/* check demo data */
			} else {
				$demo_result = ! empty( $this->demo_data ) ? $this->demo_data->bws_handle_demo_data() : false;
				if ( false !== $demo_result ) {
					if ( ! empty( $demo_result ) && is_array( $demo_result ) ) {
						$error   = $demo_result['error'];
						$message = $demo_result['done'];
						if ( ! empty( $demo_result['done'] ) && ! empty( $demo_result['options'] ) ) {
							$this->options = $demo_result['options'];
						}
					}
					/* Save options */
				} elseif ( ! isset( $_REQUEST['bws_restore_default'] ) && ! isset( $_POST['bws_handle_demo'] ) && isset( $_REQUEST[ $this->prefix . '_form_submit' ] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
					/* save tabs */
					$result = $this->save_options();
					if ( ! empty( $result['error'] ) ) {
						$error = $result['error'];
					}
					if ( ! empty( $result['message'] ) ) {
						$message = $result['message'];
					}
					if ( ! empty( $result['notice'] ) ) {
						$notice = $result['notice'];
					}

					if ( '' === $this->change_permission_attr ) {
						/* save `misc` tab */
						$result = $this->save_options_misc();
						if ( ! empty( $result['notice'] ) ) {
							$notice .= $result['notice'];
						}
					}
				}
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display error\message\notice
		 *
		 * @access public
		 * @param  $save_results - array with error\message\notice
		 * @return void
		 */
		public function display_messages( $save_results ) {
			/**
			 * Action - Display custom error\message\notice
			 */
			do_action( __CLASS__ . '_display_custom_messages', $save_results );
			?>
			<div class="updated fade inline" 
			<?php
			if ( empty( $save_results['message'] ) ) {
				echo 'style="display:none"';}
			?>
			><p><strong><?php echo esc_html( $save_results['message'] ); ?></strong></p></div>
			<div class="updated bws-notice inline" 
			<?php
			if ( empty( $save_results['notice'] ) ) {
				echo 'style="display:none"';}
			?>
			><p><strong><?php echo esc_html( $save_results['notice'] ); ?></strong></p></div>
			<div class="error inline" 
			<?php
			if ( empty( $save_results['error'] ) ) {
				echo 'style="display:none"';}
			?>
			><p><strong><?php echo esc_html( $save_results['error'] ); ?></strong></p></div>
			<?php
		}

		/**
		 * Save plugin options to the database
		 *
		 * @access public
		 * @param  ab
		 * @return array    The Action results
		 * @abstract
		 */
		public function save_options() {
			die( 'function Bws_Settings_Tabs::save_options() must be over-ridden in a sub-class.' );
		}

		/**
		 * Get 'custom_code' status and content
		 *
		 * @access private
		 */
		private function get_custom_code() {
			global $bstwbsftwppdtplgns_options, $wp_filesystem;

			$this->custom_code_args = array(
				'is_css_active' => false,
				'content_css'   => '',
				'css_writeable' => false,
				'is_php_active' => false,
				'content_php'   => '',
				'php_writeable' => false,
				'is_js_active'  => false,
				'content_js'    => '',
				'js_writeable'  => false,
			);

			if ( ! $this->upload_dir ) {
				$this->upload_dir = wp_upload_dir();
			}

			$folder = $this->upload_dir['basedir'] . '/bws-custom-code';

			if ( $this->is_multisite ) {
				$this->custom_code_args['blog_id'] = get_current_blog_id();
			}

			WP_Filesystem();

			foreach ( array( 'css', 'php', 'js' ) as $extension ) {
				$file      = 'bws-custom-code.' . $extension;
				$real_file = $folder . '/' . $file;
				
				if ( $wp_filesystem->exists( $real_file ) ) {
					update_recently_edited( $real_file );
					$this->custom_code_args[ "content_{$extension}" ] = $wp_filesystem->get_contents( $real_file );//file_get_contents( $real_file );
					if ( ( $this->is_multisite && isset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] ) ) ||
						 ( ! $this->is_multisite && isset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] ) ) ) {
						$this->custom_code_args[ "is_{$extension}_active" ] = true;
					}
					if ( is_writeable( $real_file ) ) {
						$this->custom_code_args[ "{$extension}_writeable" ] = true;
					}
				} else {
					$this->custom_code_args[ "{$extension}_writeable" ] = true;
					if ( 'php' === $extension ) {
						$this->custom_code_args[ "content_{$extension}" ] = '<?php' . "\n" . "if ( ! defined( 'ABSPATH' ) ) exit;" . "\n" . "if ( ! defined( 'BWS_GLOBAL' ) ) exit;" . "\n\n" . '/* Start your code here */' . "\n";
					}
				}
			}
		}

		/**
		 * Display 'custom_code' tab
		 *
		 * @access private
		 */
		private function tab_custom_code() {
			global $pdfprnt_options;
			?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Custom Code', 'bestwebsoft' ); ?></h3>
			<?php
			$this->help_phrase();
			$bws_hide_premium = bws_hide_premium_options_check( $pdfprnt_options );
			?>
			<hr>
			<h4><?php esc_html_e( 'The ability to add custom code is avaliable in the Pro version', 'bestwebsoft' ); ?></h4>
			<?php
			if ( ! $bws_hide_premium ) {
				?>
				<div class="bws_pro_version_bloc pdfprnt-pro-feature">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="Close"></button>
						<div class="bws_table_bg"></div>
						<div class="bws_pro_version">
							<?php
							if ( ! current_user_can( 'edit_plugins' ) ) {
								echo '<p>' . esc_html__( 'You do not have sufficient permissions to edit plugins for this site.', 'bestwebsoft' ) . '</p>';
								return;
							}

							$list = array(
								'css' => array(
									'description'     => __( 'These styles will be added to the header on all pages of your site.', 'bestwebsoft' ),
									'learn_more_link' => 'https://developer.mozilla.org/en-US/docs/Web/Guide/CSS/Getting_started',
								),
								'php' => array(
									'description'     => sprintf( __( 'This PHP code will be hooked to the %s Action and will be printed on front end only.', 'bestwebsoft' ), '<a href="https://codex.wordpress.org/Plugin_API/Action_Reference/init" target="_blank"><code>init</code></a>' ),
									'learn_more_link' => 'https://php.net/',
								),
								'js'  => array(
									'description'     => __( 'These code will be added to the header on all pages of your site.', 'bestwebsoft' ),
									'learn_more_link' => 'https://developer.mozilla.org/en-US/docs/Web/JavaScript',
								),
							);

							foreach ( $list as $extension => $extension_data ) {
								$name = 'js' === $extension ? 'JavaScript' : strtoupper( $extension );
								?>
								<p><big>
										<strong><?php echo esc_html( $name ); ?></strong>
										<?php
										if ( ! $this->custom_code_args[ "{$extension}_writeable" ] ) {
											echo '(' . esc_html__( 'Browsing', 'bestwebsoft' ) . ')';}
										?>
									</big>
								</p>
								<p class="bws_info">
									<label>
										<input type="checkbox" value="1" 
											<?php
											if ( $this->custom_code_args[ "is_{$extension}_active" ] ) {
												echo 'checked';}
											?>
										 />
										<?php printf( esc_html__( 'Activate custom %s code.', 'bestwebsoft' ), esc_html( $name ) ); ?>
									</label>
								</p>
								<textarea cols="70" rows="25" id="bws_newcontent_<?php echo esc_attr( $extension ); ?>" disabled="disabled"><?php
									if ( isset( $this->custom_code_args[ "content_{$extension}" ] ) ) {
										echo esc_html( stripslashes_deep( $this->custom_code_args[ "content_{$extension}" ] ) ); }
									?></textarea>
								<p class="bws_info">
									<?php echo esc_html( $extension_data['description'] ); ?>
									<br>
									<a href="<?php echo esc_url( $extension_data['learn_more_link'] ); ?>" target="_blank">
										<?php printf( esc_html__( 'Learn more about %s', 'bestwebsoft' ), esc_html__( $name ) ); ?>
									</a>
								</p>
								<?php
							}
							?>
						</div>
					</div>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/pdf-print/?k=d9da7c9c2046bed8dfa38d005d4bffdb&amp;pn=101&amp;v=2.3.1&amp;wp_v=5.8.3" target="_blank" title="PDF &amp; Print">Upgrade to Pro</a>
						<div class="clear"></div>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="bws_pro_version_tooltip">
					<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/pdf-print/?k=d9da7c9c2046bed8dfa38d005d4bffdb&amp;pn=101&amp;v=2.3.1&amp;wp_v=5.8.3" target="_blank" title="PDF &amp; Print">Upgrade to Pro</a>
					<div class="clear"></div>
				</div>
				<?php
			}
		}

		/**
		 * Display 'misc' tab
		 *
		 * @access private
		 */
		private function tab_misc() {
			global $bstwbsftwppdtplgns_options;
			?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Miscellaneous Settings', 'bestwebsoft' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<?php
			/**
			 * Action - Display custom options on the Import / Export' tab
			 */
			do_action( __CLASS__ . '_additional_misc_options' );

			if ( ! $this->forbid_view && ! empty( $this->change_permission_attr ) ) {
				?>
				<div class="error inline bws_visible"><p><strong><?php esc_html_e( 'Notice', 'bestwebsoft' ); ?>:</strong> <strong><?php printf( esc_html__( 'It is prohibited to change %1$s settings on this site in the %2$s network settings.', 'bestwebsoft' ), esc_html( $this->plugins_info['Name'] ), esc_html( $this->plugins_info['Name'] ) ); ?></strong></p></div>
				<?php
			}
			if ( $this->forbid_view ) {
				?>
				<div class="error inline bws_visible"><p><strong><?php esc_html_e( 'Notice', 'bestwebsoft' ); ?>:</strong> <strong><?php printf( esc_html__( 'It is prohibited to view %1$s settings on this site in the %2$s network settings.', 'bestwebsoft' ), esc_html( $this->plugins_info['Name'] ), esc_html( $this->plugins_info['Name'] ) ); ?></strong></p></div>
			<?php } else { ?>
				<table class="form-table">
					<?php
					/**
					 * Action - Display custom options on the 'misc' tab
					 */
					do_action( __CLASS__ . '_additional_misc_options_affected' );
					if ( ! empty( $this->pro_page ) && $this->bws_hide_pro_option_exist ) {
						?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Pro Options', 'bestwebsoft' ); ?></th>
							<td>
								<label>
									<input <?php echo esc_attr( wp_kses_data( $this->change_permission_attr ) ); ?> name="bws_hide_premium_options_submit" type="checkbox" value="1" 
										<?php
										if ( ! $this->hide_pro_tabs ) {
											echo 'checked="checked "';}
										?>
									/>
									<span class="bws_info"><?php esc_html_e( 'Enable to display plugin Pro options.', 'bestwebsoft' ); ?></span>
								</label>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Track Usage', 'bestwebsoft' ); ?></th>
						<td>
							<label>
								<input <?php echo esc_attr( wp_kses_data( $this->change_permission_attr ) ); ?> name="bws_track_usage" type="checkbox" value="1" 
									<?php
									if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
										echo 'checked="checked "';}
									?>
								/>
								<span class="bws_info"><?php esc_html_e( 'Enable to allow tracking plugin usage anonymously in order to make it better.', 'bestwebsoft' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Default Settings', 'bestwebsoft' ); ?></th>
						<td>
							<input<?php echo esc_attr( wp_kses_data( $this->change_permission_attr ) ); ?> name="bws_restore_default" type="submit" class="button" value="<?php esc_html_e( 'Restore Settings', 'bestwebsoft' ); ?>" />
							<div class="bws_info"><?php esc_html_e( 'This will restore plugin settings to defaults.', 'bestwebsoft' ); ?></div>
						</td>
					</tr>
				</table>
				<?php
			}
		}

		/**
		 * Display 'Import / Export' tab
		 *
		 * @access private
		 */
		public function tab_import_export() {
			?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Import / Export', 'bestwebsoft' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<?php
			/**
			 * Action - Display custom options on the Import / Export' tab
			 */
			do_action( __CLASS__ . '_additional_import_export_options' );

			if ( ! $this->forbid_view && ! empty( $this->change_permission_attr ) ) {
				?>
				<div class="error inline bws_visible"><p><strong><?php esc_html_e( 'Notice', 'bestwebsoft' ); ?>:</strong> <strong><?php printf( esc_html__( 'It is prohibited to change %1$s settings on this site in the %2$s network settings.', 'bestwebsoft' ), esc_html( $this->plugins_info['Name'] ), esc_html( $this->plugins_info['Name'] ) ); ?></strong></p></div>
				<?php
			}
			if ( $this->forbid_view ) {
				?>
				<div class="error inline bws_visible"><p><strong><?php esc_html_e( 'Notice', 'bestwebsoft' ); ?>:</strong> <strong><?php printf( esc_html__( 'It is prohibited to view %1$s settings on this site in the %2$s network settings.', 'bestwebsoft' ), esc_html( $this->plugins_info['Name'] ), esc_html( $this->plugins_info['Name'] ) ); ?></strong></p></div>
			<?php } else { ?>
				<table class="form-table">
					<?php
					/**
					 * Action - Display custom options on the Import / Export' tab
					 */
					do_action( __CLASS__ . '_additional_import_export_options_affected' );
					?>
				</table>
				<?php
			}
		}

		/**
		 * Save plugin options to the database
		 *
		 * @access private
		 */
		private function save_options_misc() {
			global $bstwbsftwppdtplgns_options, $wp_version;
			$notice = '';

			/* hide premium options */
			if ( ! empty( $this->pro_page ) ) {
				if ( isset( $_POST['bws_hide_premium_options'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
					$hide_result         = bws_hide_premium_options( $this->options );
					$this->hide_pro_tabs = true;
					$this->options       = $hide_result['options'];
					if ( ! empty( $hide_result['message'] ) ) {
						$notice = $hide_result['message'];
					}
					if ( $this->is_network_options ) {
						update_site_option( $this->prefix . '_options', $this->options );
					} else {
						update_option( $this->prefix . '_options', $this->options );
					}
				} elseif ( isset( $_POST['bws_hide_premium_options_submit'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
					if ( ! empty( $this->options['hide_premium_options'] ) ) {
						$key = array_search( get_current_user_id(), $this->options['hide_premium_options'] );
						if ( false !== $key ) {
							unset( $this->options['hide_premium_options'][ $key ] );
						}
						if ( $this->is_network_options ) {
							update_site_option( $this->prefix . '_options', $this->options );
						} else {
							update_option( $this->prefix . '_options', $this->options );
						}
					}
					$this->hide_pro_tabs = false;
				} else {
					if ( empty( $this->options['hide_premium_options'] ) ) {
						$this->options['hide_premium_options'][] = get_current_user_id();
						if ( $this->is_network_options ) {
							update_site_option( $this->prefix . '_options', $this->options );
						} else {
							update_option( $this->prefix . '_options', $this->options );
						}
					}
					$this->hide_pro_tabs = true;
				}
			}
			/* Save 'Track Usage' option */
			if ( isset( $_POST['bws_track_usage'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				if ( empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
					$bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] = true;
					$track_usage = true;
				}
			} else {
				if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
					unset( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] );
					false;
					$track_usage = false;
				}
			}
			if ( isset( $track_usage ) ) {
				$usage_id = ! empty( $bstwbsftwppdtplgns_options['track_usage']['usage_id'] ) ? $bstwbsftwppdtplgns_options['track_usage']['usage_id'] : false;
				/* send data */
				$options      = array(
					'timeout'    => 3,
					'body'       => array(
						'url'        => get_bloginfo( 'url' ),
						'wp_version' => $wp_version,
						'is_active'  => $track_usage,
						'product'    => $this->plugin_basename,
						'version'    => $this->plugins_info['Version'],
						'usage_id'   => $usage_id,
					),
					'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				);
				$raw_response = wp_remote_post( 'https://bestwebsoft.com/wp-content/plugins/products-statistics/track-usage/', $options );

				if ( ! is_wp_error( $raw_response ) && 200 === intval( wp_remote_retrieve_response_code( $raw_response ) ) ) {
					$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );

					if ( is_array( $response ) &&
						! empty( $response['usage_id'] ) &&
						$response['usage_id'] !== $usage_id ) {
						$bstwbsftwppdtplgns_options['track_usage']['usage_id'] = $response['usage_id'];
					}
				}

				if ( $this->is_multisite ) {
					update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
				} else {
					update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
				}
			}

			return compact( 'notice' );
		}

		/**
		 *
		 */
		public function tab_license() {
			global $wp_version, $bstwbsftwppdtplgns_options;
			?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'License Key', 'bestwebsoft' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<?php
			foreach ( $this->licenses as $single_license ) {
				$pro_plugin_name = ( strpos( $single_license['name'], 'Pro' ) ) ? $single_license['name'] : $single_license['name'] . ' Pro';
				if ( ! empty( $this->pro_page ) || ! empty( $single_license['pro_basename'] ) ) {

					if ( $this->pro_plugin_is_activated && ( empty( $single_license['pro_basename'] ) || isset( $this->bws_license_plugin ) ) ) {
						$url = 'https://bestwebsoft.com/wp-content/plugins/paid-products/plugins/downloads/?bws_first_download=' . $this->bws_license_plugin . '&bws_license_key=' . $bstwbsftwppdtplgns_options[ $this->bws_license_plugin ] . '&download_from=5';
						?>
						<table class="form-table">
							<tr>
								<th scope="row"><?php echo wp_kses_data( $pro_plugin_name ) . ' License'; ?></th>
								<td>
									<p>
										<strong><?php esc_html_e( 'Your Pro plugin is ready', 'bestwebsoft' ); ?></strong>
										<br>
										<?php esc_html_e( 'Your plugin has been zipped, and now is ready to download.', 'bestwebsoft' ); ?>
									</p>
									<p>
										<a class="button button-secondary" target="_parent" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Download Now', 'bestwebsoft' ); ?></a>
									</p>
									<br>
									<p>
										<strong><?php esc_html_e( 'Need help installing the plugin?', 'bestwebsoft' ); ?></strong>
										<br>
										<a target="_blank" href="https://bestwebsoft.com/documentation/how-to-install-a-wordpress-product/how-to-install-a-wordpress-plugin/"><?php esc_html_e( 'How to install WordPress plugin from your admin Dashboard (ZIP archive)', 'bestwebsoft' ); ?></a>
									</p>
									<br>					
									<p>
										<strong><?php esc_html_e( 'Get Started', 'bestwebsoft' ); ?></strong>
										<br>
										<a target="_blank" href="https://bestwebsoft.com/documentation/"><?php esc_html_e( 'Documentation', 'bestwebsoft' ); ?></a>
										<br>
										<a target="_blank" href="https://www.youtube.com/user/bestwebsoft"><?php esc_html_e( 'Video Instructions', 'bestwebsoft' ); ?></a>
										<br>
										<a target="_blank" href="https://support.bestwebsoft.com"><?php esc_html_e( 'Knowledge Base', 'bestwebsoft' ); ?></a>
									</p>
								</td>
							</tr>
						</table>
						<?php
					} else {
						$attr = $license_key = '';
						if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $this->bws_license_plugin ]['count'] ) &&
							'5' < $bstwbsftwppdtplgns_options['go_pro'][ $this->bws_license_plugin ]['count'] &&
							$bstwbsftwppdtplgns_options['go_pro'][ $this->bws_license_plugin ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) {
							$attr = 'disabled="disabled"';
						}

						if ( ! empty( $single_license['pro_basename'] ) ) {
							$license_key = ! empty( $bstwbsftwppdtplgns_options[ $single_license['pro_basename'] ] ) ? $bstwbsftwppdtplgns_options[ $single_license['pro_basename'] ] : '';
						}
						?>
						<table class="form-table">
							<tr>
								<th scope="row"><?php echo esc_html( $pro_plugin_name ) . ' License'; ?></th>
								<td>
									<input <?php echo wp_kses_data( $attr ); ?> type="text" name="bws_license_key_<?php echo esc_attr( ( ! empty( $single_license['pro_slug'] ) ) ? $single_license['pro_slug'] : $single_license['slug'] ); ?>" value="<?php echo esc_attr( $license_key ); ?>" />
									<input <?php echo wp_kses_data( $attr ); ?> type="hidden" name="bws_license_plugin_<?php echo esc_attr( ( ! empty( $single_license['pro_slug'] ) ) ? $single_license['pro_slug'] : $single_license['slug'] ); ?>" value="<?php echo esc_attr( ( ! empty( $single_license['pro_slug'] ) ) ? $single_license['pro_slug'] : $single_license['slug'] ); ?>" />
									<input <?php echo wp_kses_data( $attr ); ?> type="submit" class="button button-secondary" name="bws_license_submit" value="<?php esc_html_e( 'Activate', 'bestwebsoft' ); ?>" />
									<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'bws_license_key_nonce' ); ?>" />
									<div class="bws_info">
										<?php printf( esc_html__( 'Enter your license key to activate %s and get premium plugin features.', 'bestwebsoft' ), '<a href="' . esc_url( $this->bws_plugin_link ) . '" target="_blank" title="' . esc_html( $pro_plugin_name ) . '">' . esc_html( $pro_plugin_name ) . '</a>' ); ?>
									</div>
									<?php if ( '' !== $attr ) { ?>
										<p><?php esc_html_e( 'Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.', 'bestwebsoft' ); ?></p>
										<?php
									}
									if ( false !== $this->trial_days ) {
										echo '<p>' . esc_html__( 'or', 'bestwebsoft' ) . ' <a href="' . esc_url( $this->plugins_info['PluginURI'] . 'trial/?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info['Version'] . '&wp_v=' . $wp_version ) . '" target="_blank">' . sprintf( esc_html__( 'Start Your Free %s-Day Trial Now', 'bestwebsoft' ), esc_attr( $this->trial_days ) ) . '</a></p>';
									}
									?>
								</td>
							</tr>
						</table>
						<?php
					}
				} else {
					global $bstwbsftwppdtplgns_options;
					$license_key = ( isset( $bstwbsftwppdtplgns_options[ $single_license['basename'] ] ) ) ? $bstwbsftwppdtplgns_options[ $single_license['basename'] ] : '';
					?>
					<table class="form-table">
						<tr>
							<th scope="row"><?php echo esc_html( $pro_plugin_name ) . ' License'; ?></th>
							<td>
								<input type="text" maxlength="100" name="bws_license_key_<?php echo esc_attr( $single_license['slug'] ); ?>" value="<?php echo esc_attr( $license_key ); ?>" />
								<input type="submit" class="button button-secondary" name="bws_license_submit" value="<?php esc_html_e( 'Check license key', 'bestwebsoft' ); ?>" />
								<div class="bws_info">
									<?php esc_html_e( 'If necessary, you can check if the license key is correct or reenter it in the field below.', 'bestwebsoft' ); ?>
								</div>
							</td>
						</tr>
					</table>
					<?php
				}
			}
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Manage License Settings', 'bestwebsoft' ); ?></th>
					<td>
						<a class="button button-secondary" href="https://bestwebsoft.com/client-area" target="_blank"><?php esc_html_e( 'Login to Client Area', 'bestwebsoft' ); ?></a>
						<div class="bws_info">
							<?php esc_html_e( 'Manage active licenses, download BWS products, and view your payment history using BestWebSoft Client Area.', 'bestwebsoft' ); ?>
						</div>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Save plugin options to the database
		 *
		 * @access private
		 * @param  ab
		 * @return array    The Action results
		 */
		private function save_options_license_key() {
			global $wp_version, $bstwbsftwppdtplgns_options, $wp_filesystem;
			/*$empty_field_error - added to avoid error when 1 field is empty while another field contains license key*/

			$error = $message = $empty_field_error = '';

			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bws_license_key_nonce' ) ) {
					die( __( 'Security check', 'bestwebsoft' ) );
			} else {

				foreach ( $this->licenses as $single_license ) {
					$bws_license_key = ( isset( $_POST[ ( ! empty( $single_license['pro_slug'] ) ) ? 'bws_license_key_' . $single_license['pro_slug'] : 'bws_license_key_' . $single_license['slug'] ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ ( ! empty( $single_license['pro_slug'] ) ) ? 'bws_license_key_' . $single_license['pro_slug'] : 'bws_license_key_' . $single_license['slug'] ] ) ) : '';
					if ( '' !== $bws_license_key ) {
						if ( strlen( $bws_license_key ) !== 18 ) {
							$error = __( 'Wrong license key', 'bestwebsoft' );
						} else {
							/* CHECK license key */
							if ( $this->is_pro && empty( $single_license['pro_basename'] ) ) {
								delete_transient( 'bws_plugins_update' );
								if ( ! $this->all_plugins ) {
									if ( ! function_exists( 'get_plugins' ) ) {
										require_once ABSPATH . 'wp-admin/includes/plugin.php';
									}
									$this->all_plugins = get_plugins();
								}
								$current = get_site_transient( 'update_plugins' );

								if ( ! empty( $this->all_plugins ) && ! empty( $current ) && isset( $current->response ) && is_array( $current->response ) ) {
									$to_send = array();
									$to_send['plugins'][ $single_license['basename'] ]                       = $this->all_plugins[ $single_license['basename'] ];
									$to_send['plugins'][ $single_license['basename'] ]['bws_license_key']    = $bws_license_key;
									$to_send['plugins'][ $single_license['basename'] ]['bws_illegal_client'] = true;
									$options      = array(
										'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
										'body'       => array( 'plugins' => serialize( $to_send ) ),
										'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
									);
									$raw_response = wp_remote_post( 'https://bestwebsoft.com/wp-content/plugins/paid-products/plugins/pro-license-check/1.0/', $options );

									if ( is_wp_error( $raw_response ) || 200 !== intval( wp_remote_retrieve_response_code( $raw_response ) ) ) {
										$error = __( 'Something went wrong. Please try again later. If the error appears again, please contact us', 'bestwebsoft' ) . ': <a href=https://support.bestwebsoft.com>BestWebSoft</a>. ' . __( 'We are sorry for inconvenience.', 'bestwebsoft' );
									} else {
										$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
										if ( is_array( $response ) && ! empty( $response ) ) {
											foreach ( $response as $single_response ) {
												if ( 'wrong_license_key' === $single_response->package ) {
													$error = __( 'Wrong license key.', 'bestwebsoft' );
												} elseif ( 'wrong_domain' === $single_response->package ) {
													$error = __( 'This license key is bound to another site.', 'bestwebsoft' );
												} elseif ( 'time_out' === $single_response->package ) {
													$message = __( 'This license key is valid, but Your license has expired. If you want to update our plugin in future, you should extend the license.', 'bestwebsoft' );
												} elseif ( 'you_are_banned' === $single_response->package ) {
													$error = __( 'Unfortunately, you have exceeded the number of available tries.', 'bestwebsoft' );
												} elseif ( 'duplicate_domen_for_trial' === $single_response->package ) {
													$error = __( 'Unfortunately, the Pro Trial licence was already installed to this domain. The Pro Trial license can be installed only once.', 'bestwebsoft' );
												}
												if ( empty( $error ) ) {
													if ( empty( $message ) ) {
														if ( isset( $single_response->trial ) ) {
															$message = __( 'The Pro Trial license key is valid.', 'bestwebsoft' );
														} else {
															$message = __( 'The license key is valid.', 'bestwebsoft' );
														}

														if ( ! empty( $single_response->time_out ) ) {
															$message .= ' ' . __( 'Your license will expire on', 'bestwebsoft' ) . ' ' . $single_response->time_out . '.';
														} else {
															/* lifetime */
															$single_response->time_out = null;
														}

														if ( isset( $single_response->trial ) && $this->is_trial ) {
															$message .= ' ' . sprintf( __( 'In order to continue using the plugin it is necessary to buy a %s license.', 'bestwebsoft' ), '<a href="' . esc_url( $this->plugins_info['PluginURI'] . '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info['Version'] . '&wp_v=' . $wp_version ) . '" target="_blank" title="' . $this->plugins_info['Name'] . '">Pro</a>' );
														}
													}

													if ( isset( $single_response->trial ) ) {
														$bstwbsftwppdtplgns_options['trial'][ $single_license['basename'] ] = 1;
													} else {
														unset( $bstwbsftwppdtplgns_options['trial'][ $single_license['basename'] ] );
													}

													if ( isset( $single_response->nonprofit ) ) {
														$bstwbsftwppdtplgns_options['nonprofit'][ $single_license['basename'] ] = 1;
													} else {
														unset( $bstwbsftwppdtplgns_options['nonprofit'][ $single_license['basename'] ] );
													}

													if ( ! isset( $bstwbsftwppdtplgns_options[ $single_license['basename'] ] ) || $bstwbsftwppdtplgns_options[ $single_license['basename'] ] !== $bws_license_key ) {
														$bstwbsftwppdtplgns_options[ $single_license['basename'] ] = $bws_license_key;

														WP_Filesystem();
														if ( $wp_filesystem->put_contents( dirname( dirname( __FILE__ ) ) . '/license_key.txt', $bws_license_key, 0755 ) ) {
															$update_option = true;
														}
													}

													if ( isset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $single_license['basename'] ] ) ) {
														unset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $single_license['basename'] ] );
														$update_option = true;
													}

													if ( ! isset( $bstwbsftwppdtplgns_options['time_out'][ $single_license['basename'] ] ) || $bstwbsftwppdtplgns_options['time_out'][ $single_license['basename'] ] !== $single_response->time_out ) {
														$bstwbsftwppdtplgns_options['time_out'][ $single_license['basename'] ] = $single_response->time_out;
														$update_option = true;
													}

													if ( isset( $update_option ) ) {
														if ( $this->is_multisite ) {
															update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
														} else {
															update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
														}
													}
												}
											}
										} else {
											$error = __( 'Something went wrong. Please try again later. If the error appears again, please contact us', 'bestwebsoft' ) . ' <a href=https://support.bestwebsoft.com>BestWebSoft</a>. ' . __( 'We are sorry for inconvenience.', 'bestwebsoft' );
										}
									}
								}
								/* Go Pro */
							} else {
								$slug = ! empty( $single_license['pro_slug'] ) ? 'bws_license_plugin_' . $single_license['pro_slug'] : 'bws_license_plugin_' . $single_license['slug'];
								$bws_license_plugin = isset( $_POST[ $slug ] ) ? sanitize_text_field( wp_unslash( $_POST[ $slug ] ) ) : '';
								if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] ) && $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) {
									$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] + 1;
								} else {
									$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = 1;
									$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time']  = time();
								}

								/* download Pro */
								if ( ! $this->all_plugins ) {
									if ( ! function_exists( 'get_plugins' ) ) {
										require_once ABSPATH . 'wp-admin/includes/plugin.php';
									}
									$this->all_plugins = get_plugins();
								}

								if ( ! array_key_exists( $bws_license_plugin, $this->all_plugins ) ) {
									$current = get_site_transient( 'update_plugins' );
									if ( ! empty( $current ) && isset( $current->response ) && is_array( $current->response ) ) {
										$to_send                                   = array();
										$to_send['plugins'][ $bws_license_plugin ] = array();
										$to_send['plugins'][ $bws_license_plugin ]['bws_license_key']    = $bws_license_key;
										$to_send['plugins'][ $bws_license_plugin ]['bws_illegal_client'] = true;
										$options      = array(
											'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
											'body'       => array( 'plugins' => serialize( $to_send ) ),
											'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
										);
										$raw_response = wp_remote_post( 'https://bestwebsoft.com/wp-content/plugins/paid-products/plugins/pro-license-check/1.0/', $options );

										if ( is_wp_error( $raw_response ) || 200 !== intval( wp_remote_retrieve_response_code( $raw_response ) ) ) {
											$error = __( 'Something went wrong. Please try again later. If the error appears again, please contact us', 'bestwebsoft' ) . ': <a href="https://support.bestwebsoft.com">BestWebSoft</a>. ' . __( 'We are sorry for inconvenience.', 'bestwebsoft' );
										} else {
											$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
											if ( is_array( $response ) && ! empty( $response ) ) {
												foreach ( $response as $single_response ) {
													if ( 'wrong_license_key' === $single_response->package ) {
														$error = __( 'Wrong license key.', 'bestwebsoft' );
													} elseif ( 'wrong_domain' === $single_response->package ) {
														$error = __( 'This license key is bound to another site.', 'bestwebsoft' );
													} elseif ( 'you_are_banned' === $single_response->package ) {
														$error = __( 'Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.', 'bestwebsoft' );
													} elseif ( 'time_out' === $single_response->package ) {
														$error = sprintf( __( 'Unfortunately, Your license has expired. To continue getting top-priority support and plugin updates, you should extend it in your %s.', 'bestwebsoft' ), ' <a href="https://bestwebsoft.com/client-area">Client Area</a>' );
													} elseif ( 'duplicate_domen_for_trial' === $single_response->package ) {
														$error = __( 'Unfortunately, the Pro licence was already installed to this domain. The Pro Trial license can be installed only once.', 'bestwebsoft' );
													}
												}
												if ( empty( $error ) ) {
													$bws_license_plugin = ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'];

													$bstwbsftwppdtplgns_options[ $bws_license_plugin ] = $bws_license_key;
													$this->pro_plugin_is_activated                     = true;
												}
											} else {
												$error = __( 'Something went wrong. Try again later or upload the plugin manually. We are sorry for inconvenience.', 'bestwebsoft' );
											}
										}
									}
								} else {
									$bstwbsftwppdtplgns_options[ $bws_license_plugin ] = $bws_license_key;
									/* activate Pro */
									if ( ! is_plugin_active( $bws_license_plugin ) ) {
										if ( $this->is_multisite && is_plugin_active_for_network( ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'] ) ) {
											/* if multisite and free plugin is network activated */
											$network_wide = true;
										} else {
											/* activate on a single blog */
											$network_wide = false;
										}
										activate_plugin( $bws_license_plugin, null, $network_wide );
										$this->pro_plugin_is_activated = true;
									}
								}
								/* add 'track_usage' for Pro version */
								if ( ! empty( $bstwbsftwppdtplgns_options['track_usage'][ ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'] ] ) &&
									 empty( $bstwbsftwppdtplgns_options['track_usage'][ $bws_license_plugin ] ) ) {
									$bstwbsftwppdtplgns_options['track_usage'][ $bws_license_plugin ] = $bstwbsftwppdtplgns_options['track_usage'][ ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'] ];
								}

								if ( $this->is_multisite ) {
									update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
								} else {
									update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
								}

								if ( $this->pro_plugin_is_activated ) {
									delete_transient( 'bws_plugins_update' );
								}
							}
						}
					} else {
						$empty_field_error = __( 'Please, enter Your license key', 'bestwebsoft' );
					}
				}
			}
			return compact( 'error', 'message', 'empty_field_error' );
		}

		/**
		 * Display help phrase
		 *
		 * @access public
		 * @param  void
		 * @return html    The Action results
		 */
		public function help_phrase() {
			/*pls */
			echo '<div class="bws_tab_description">' . esc_html__( 'Need Help?', 'bestwebsoft' ) . ' ';
			if ( '' !== $this->doc_link ) {
				echo '<a href="' . esc_url( $this->doc_link ) . '" target="_blank">' . esc_html__( 'Read the Instruction', 'bestwebsoft' );
			} else {
				echo '<a href="https://support.bestwebsoft.com/hc/en-us/" target="_blank">' . esc_html__( 'Visit Help Center', 'bestwebsoft' );
			}
			if ( '' !== $this->doc_video_link ) {
				echo '</a> ' . esc_html__( 'or', 'bestwebsoft' ) . ' <a href="' . esc_url( $this->doc_video_link ) . '" target="_blank">' . esc_html__( 'Watch the Video', 'bestwebsoft' );
			}
			echo '</a></div>';
			/* pls*/
		}

		public function bws_pro_block_links() {
			global $wp_version;
			?>
			<div class="bws_pro_version_tooltip">
				<a class="bws_button" href="<?php echo esc_url( $this->plugins_info['PluginURI'] ); ?>?k=<?php echo esc_attr( $this->link_key ); ?>&amp;pn=<?php echo esc_attr( $this->link_pn ); ?>&amp;v=<?php echo esc_attr( $this->plugins_info['Version'] ); ?>&amp;wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="<?php echo esc_html( $this->plugins_info['Name'] ); ?>"><?php esc_html_e( 'Upgrade to Pro', 'bestwebsoft' ); ?></a>
				<?php if ( false !== $this->trial_days ) { ?>
					<span class="bws_trial_info">
						<?php esc_html_e( 'or', 'bestwebsoft' ); ?>
						<a href="<?php echo esc_url( $this->plugins_info['PluginURI'] . '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info['Version'] . '&wp_v=' . $wp_version ); ?>" target="_blank" title="<?php echo esc_html( $this->plugins_info['Name'] ); ?>"><?php esc_html_e( 'Start Your Free Trial', 'bestwebsoft' ); ?></a>
					</span>
				<?php } ?>
				<div class="clear"></div>
			</div>
			<?php
		}

		/**
		 * Restore plugin options to defaults
		 *
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function restore_options() {
			unset(
				$this->default_options['first_install'],
				$this->default_options['suggest_feature_banner'],
				$this->default_options['display_settings_notice']
			);
			/**
			 * filter - Change default_options array OR process custom functions
			 */
			$this->options = apply_filters( __CLASS__ . '_additional_restore_options', $this->default_options );
			if ( $this->is_network_options ) {
				$this->options['network_apply']  = 'default';
				$this->options['network_view']   = '1';
				$this->options['network_change'] = '1';
				update_site_option( $this->prefix . '_options', $this->options );
			} else {
				update_option( $this->prefix . '_options', $this->options );
			}
		}

		public function add_request_feature() {
			?>
			<div id="bws_request_feature" class="widget-access-link">
				<button type="button" class="button" ><?php esc_html_e( 'Request a Feature', 'bestwebsoft' ); ?></button>
			</div>
			<?php
			$modal_html = '<div class="bws-modal bws-modal-deactivation-feedback bws-modal-request-feature">
				<div class="bws-modal-dialog">
					<div class="bws-modal-body">
						<h2>' . sprintf( esc_html__( 'How can we improve %s?', 'bestwebsoft' ), $this->plugins_info['Name'] ) . '</h2>
						<div class="bws-modal-panel active">
							<p>' . esc_html__( 'We look forward to hear your ideas.', 'bestwebsoft' ) . '</p>
							<p>
								<textarea placeholder="' . esc_html__( 'Describe your idea', 'bestwebsoft' ) . '..."></textarea>
							</p>
							<label class="bws-modal-anonymous-label">
								<input type="checkbox" /> ' . esc_html__( 'Send website data and allow to contact me back', 'bestwebsoft' ) . '
							</label>
						</div>
					</div>
					<div class="bws-modal-footer">
						<a href="#" class="button disabled bws-modal-button button-primary">' . esc_html__( 'Submit', 'bestwebsoft' ) . '</a>
						<span class="bws-modal-processing hidden">' . esc_html__( 'Processing', 'bestwebsoft' ) . '...</span>
						<span class="bws-modal-thank-you hidden">' . esc_html__( 'Thank you!', 'bestwebsoft' ) . '</span>
						<div class="clear"></div>
					</div>
				</div>
			</div>';

			$script = '(function($) {
				var modalHtml = ' . wp_json_encode( $modal_html ) . ",
					\$modal = $( modalHtml );
				
				\$modal.appendTo( $( 'body' ) );

				$( '#bws_request_feature .button' ).on( 'click', function() {
					/* Display the dialog box.*/
					\$modal.addClass( 'active' );
					$( 'body' ).addClass( 'has-bws-modal' );				
				});

				\$modal.on( 'keypress', 'textarea', function( evt ) {
					BwsModalEnableButton();
				});

				\$modal.on( 'click', '.bws-modal-footer .button', function( evt ) {
					evt.preventDefault();

					if ( $( this ).hasClass( 'disabled' ) ) {
						return;
					}
					var info = \$modal.find( 'textarea' ).val();

					if ( info.length == 0 ) {
						return;
					}

					var _parent = $( this ).parents( '.bws-modal:first' ),
						_this =  $( this );

					var is_anonymous = ( \$modal.find( '.bws-modal-anonymous-label' ).find( 'input' ).is( ':checked' ) ) ? 0 : 1;

					$.ajax({
						url	   : ajaxurl,
						method	: 'POST',
						data	  : {
							'Action'			: 'bws_submit_request_feature_action',
							'plugin'			: '" . $this->plugin_basename . "',
							'info'				: info,
							'is_anonymous'		: is_anonymous,
							'bws_ajax_nonce'	: '" . wp_create_nonce( 'bws_ajax_nonce' ) . "'
						},
						beforeSend: function() {
							_parent.find( '.bws-modal-footer .bws-modal-button' ).hide();
							_parent.find( '.bws-modal-footer .bws-modal-processing' ).show();
							_parent.find( 'textarea, input' ).attr( 'disabled', 'disabled' );
						},
						complete  : function( message ) {
							_parent.find( '.bws-modal-footer .bws-modal-processing' ).hide();
							_parent.find( '.bws-modal-footer .bws-modal-thank-you' ).show();
						}
					});
				});

				/* If the user has clicked outside the window, cancel it. */
				\$modal.on( 'click', function( evt ) {
					var \$target = $( evt.target );

					/* If the user has clicked anywhere in the modal dialog, just return. */
					if ( \$target.hasClass( 'bws-modal-body' ) || \$target.hasClass( 'bws-modal-footer' ) ) {
						return;
					}

					/* If the user has not clicked the close button and the clicked element is inside the modal dialog, just return. */
					if ( ! \$target.hasClass( 'bws-modal-button-close' ) && ( \$target.parents( '.bws-modal-body' ).length > 0 || \$target.parents( '.bws-modal-footer' ).length > 0 ) ) {
						return;
					}

					/* Close the modal dialog */
					\$modal.removeClass( 'active' );
					$( 'body' ).removeClass( 'has-bws-modal' );

					return false;
				});

				function BwsModalEnableButton() {
					\$modal.find( '.bws-modal-button' ).removeClass( 'disabled' ).show();
					\$modal.find( '.bws-modal-processing' ).hide();
				}

				function BwsModalDisableButton() {
					\$modal.find( '.bws-modal-button' ).addClass( 'disabled' );
				}

				function BwsModalShowPanel() {
					\$modal.find( '.bws-modal-panel' ).addClass( 'active' );
				}
			})(jQuery);";

			/* add script in FOOTER */
			wp_register_script( 'bws-request-feature-dialog', '', array( 'jquery' ), '2.4.2', true );
			wp_enqueue_script( 'bws-request-feature-dialog' );
			wp_add_inline_script( 'bws-request-feature-dialog', sprintf( $script ) );
		}
	}
}

/**
 * Called after the user has submitted his reason for deactivating the plugin.
 *
 * @since  2.1.3
 */
if ( ! function_exists( 'bws_submit_request_feature_action' ) ) {
	function bws_submit_request_feature_action() {
		global $bstwbsftwppdtplgns_options, $wp_version, $bstwbsftwppdtplgns_active_plugins, $current_user;

		if ( isset( $_REQUEST['bws_ajax_nonce'] ) ) {

			wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['bws_ajax_nonce'] ) ), 'bws_ajax_nonce' );

			$basename = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
			$info     = isset( $_REQUEST['info'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['info'] ) ) : '';

			if ( empty( $info ) || empty( $basename ) ) {
				exit;
			}

			$info         = substr( $info, 0, 255 );
			$is_anonymous = isset( $_REQUEST['is_anonymous'] ) && 1 === intval( $_REQUEST['is_anonymous'] );

			$options = array(
				'product' => $basename,
				'info'    => $info,
			);

			if ( ! $is_anonymous ) {
				if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
					$bstwbsftwppdtplgns_options = ( is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
				}

				if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['usage_id'] ) ) {
					$options['usage_id'] = $bstwbsftwppdtplgns_options['track_usage']['usage_id'];
				} else {
					$options['usage_id']   = false;
					$options['url']        = get_bloginfo( 'url' );
					$options['wp_version'] = $wp_version;
					$options['is_active']  = false;
					$options['version']    = $bstwbsftwppdtplgns_active_plugins[ $basename ]['Version'];
				}

				$options['email'] = $current_user->data->user_email;
			}

			/* send data */
			$raw_response = wp_remote_post(
				'https://bestwebsoft.com/wp-content/plugins/products-statistics/request-feature/',
				array(
					'method'  => 'POST',
					'body'    => $options,
					'timeout' => 15,
				)
			);

			if ( ! is_wp_error( $raw_response ) && 200 === intval( wp_remote_retrieve_response_code( $raw_response ) ) ) {
				if ( ! $is_anonymous ) {
					$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );

					if ( is_array( $response ) && ! empty( $response['usage_id'] ) && $response['usage_id'] !== $options['usage_id'] ) {
						$bstwbsftwppdtplgns_options['track_usage']['usage_id'] = $response['usage_id'];

						if ( is_multisite() ) {
							update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						} else {
							update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						}
					}
				}

				echo 'done';
			} else {
				echo wp_kses_data( $response->get_error_code() ) . ': ' . wp_kses_data( $response->get_error_message() );
			}
		}
		exit;
	}
}

add_action( 'wp_ajax_bws_submit_request_feature_action', 'bws_submit_request_feature_action' );
