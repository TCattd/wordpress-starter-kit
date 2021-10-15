<?php
/**
 * Main Enfocer class
 *
 * @link       https://actitud.xyz
 * @since      1.3.0
 *
 * @package    Fuerte_Wp
 * @subpackage Fuerte_Wp/includes
 * @author     Esteban Cuevas <esteban@attitude.cl>
 */

// No access outside WP
defined( 'ABSPATH' ) || die();

/**
 * Main Fuerte-WP Class
 */
class Fuerte_Wp_Enforcer
{
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	public $pagenow;
	public $fuertewp;
	public $current_user;
	public $config;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//$this->config = $this->config_setup();
	}

	/**
	 * Access this plugin instance
	 */
	public static function get_instance()
	{
		/**
		 * To run like:
		 * add_action( 'plugins_loaded', array( Fuerte_Wp_Enforcer::get_instance(), 'init' ) );
		 */
		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Init the plugin
	 */
	public function run()
	{
		$this->enforcer();
	}

	/**
	 * Config Setup
	 */
	private function config_setup()
	{
		global $fuertewp, $current_user;

		if ( file_exists( ABSPATH . 'wp-config-fuerte.php' ) && is_array( $fuertewp ) && ! empty ( $fuertewp ) ) {
			return $fuertewp;
		}

		// If Fuerte-WP hasn't been init yet
		if ( ! fuertewp_option_exists( '_fuertewp_status' ) ) {
			if ( ! isset( $current_user ) || empty ( $current_user) ) {
				$current_user = wp_get_current_user();
			}

			// Load default sample config and sets defaults
			$fuertewp_pre = $fuertewp;
			if ( file_exists( FUERTEWP_PATH . 'config-sample/wp-config-fuerte.php' ) ) {
				require_once FUERTEWP_PATH . 'config-sample/wp-config-fuerte.php';
				$defaults = $fuertewp;
				$fuertewp = $fuertewp_pre;

				// status
				carbon_set_theme_option( 'fuertewp_status', 'enabled' );

				// super_users
				carbon_set_theme_option( 'fuertewp_super_users', $current_user->user_email );

				// general
				foreach( $defaults['general'] as $key => $value ) {
					carbon_set_theme_option( 'fuertewp_' . $key, $value );
				}

				// tweaks
				foreach( $defaults['tweaks'] as $key => $value ) {
					carbon_set_theme_option( 'fuertewp_tweaks_' . $key, $value );
				}

				// emails
				foreach( $defaults['emails'] as $key => $value ) {
					carbon_set_theme_option( 'fuertewp_emails_' . $key, $value );
				}

				// restrictions
				foreach( $defaults['restrictions'] as $key => $value ) {
					carbon_set_theme_option( 'fuertewp_restrictions_' . $key, $value );
				}

				// restricted_scripts
				$value = implode( PHP_EOL, $defaults['restricted_scripts'] );
				carbon_set_theme_option( 'fuertewp_restricted_scripts', $value );

				// restricted_pages
				$value = implode( PHP_EOL, $defaults['restricted_pages'] );
				carbon_set_theme_option( 'fuertewp_restricted_pages', $value );

				// removed_menus
				$value = implode( PHP_EOL, $defaults['removed_menus'] );
				carbon_set_theme_option( 'fuertewp_removed_menus', $value );

				// removed_submenus
				$value = implode( PHP_EOL, $defaults['removed_submenus'] );
				carbon_set_theme_option( 'fuertewp_removed_submenus', $value );

				// removed_adminbar_menus
				$value = implode( PHP_EOL, $defaults['removed_adminbar_menus'] );
				carbon_set_theme_option( 'fuertewp_removed_adminbar_menus', $value );

				unset( $defaults, $fuertewp_pre, $value );
			}
		}

		// Get options from cache
		if ( false === ( $fuertewp = get_transient( 'fuertewp_cache_config' ) ) ) {
			// status
			$status                    = carbon_get_theme_option( 'fuertewp_status' );

			// general
			$super_users               = carbon_get_theme_option( 'fuertewp_super_users' );
			$access_denied_message     = carbon_get_theme_option( 'fuertewp_access_denied_message' );
			$recovery_email            = carbon_get_theme_option( 'fuertewp_recovery_email' );
			$sender_email              = carbon_get_theme_option( 'fuertewp_sender_email' );
			$autoupdate_core           = carbon_get_theme_option( 'fuertewp_autoupdate_core' ) == 'yes';
			$autoupdate_plugins        = carbon_get_theme_option( 'fuertewp_autoupdate_plugins' ) == 'yes';
			$autoupdate_themes         = carbon_get_theme_option( 'fuertewp_autoupdate_themes' ) == 'yes';
			$autoupdate_translations   = carbon_get_theme_option( 'fuertewp_autoupdate_translations' ) == 'yes';

			// tweaks
			$use_site_logo_login       = carbon_get_theme_option( 'fuertewp_tweaks_use_site_logo_login' ) == 'yes';

			// emails
			$fatal_error                               = carbon_get_theme_option( 'fuertewp_emails_fatal_error' ) == 'yes';
			$automatic_updates                         = carbon_get_theme_option( 'fuertewp_emails_automatic_updates' ) == 'yes';
			$comment_awaiting_moderation               = carbon_get_theme_option( 'fuertewp_emails_comment_awaiting_moderation' ) == 'yes';
			$comment_has_been_published                = carbon_get_theme_option( 'fuertewp_emails_comment_has_been_published' ) == 'yes';
			$user_reset_their_password                 = carbon_get_theme_option( 'fuertewp_emails_user_reset_their_password' ) == 'yes';
			$user_confirm_personal_data_export_request = carbon_get_theme_option( 'fuertewp_emails_user_confirm_personal_data_export_request' ) == 'yes';
			$new_user_created                          = carbon_get_theme_option( 'fuertewp_emails_new_user_created' ) == 'yes';
			$network_new_site_created                  = carbon_get_theme_option( 'fuertewp_emails_network_new_site_created' ) == 'yes';
			$network_new_user_site_registered          = carbon_get_theme_option( 'fuertewp_emails_network_new_user_site_registered' ) == 'yes';
			$network_new_site_activated                = carbon_get_theme_option( 'fuertewp_emails_network_new_site_activated' ) == 'yes';

			// restrictions
			$disable_xmlrpc            = carbon_get_theme_option( 'fuertewp_restrictions_disable_xmlrpc' ) == 'yes';
			$disable_admin_create_edit = carbon_get_theme_option( 'fuertewp_restrictions_disable_admin_create_edit' ) == 'yes';
			$disable_app_passwords     = carbon_get_theme_option( 'fuertewp_restrictions_disable_app_passwords' ) == 'yes';
			$disable_weak_passwords    = carbon_get_theme_option( 'fuertewp_restrictions_disable_weak_passwords' ) == 'yes';
			$force_strong_passwords    = carbon_get_theme_option( 'fuertewp_restrictions_force_strong_passwords' ) == 'yes';
			$disable_admin_bar_roles   = carbon_get_theme_option( 'fuertewp_restrictions_disable_admin_bar_roles' );
			$restrict_permalinks       = carbon_get_theme_option( 'fuertewp_restrictions_restrict_permalinks' );
			$restrict_acf              = carbon_get_theme_option( 'fuertewp_restrictions_restrict_acf' );
			$disable_theme_editor      = carbon_get_theme_option( 'fuertewp_restrictions_disable_theme_editor' ) == 'yes';
			$disable_plugin_editor     = carbon_get_theme_option( 'fuertewp_restrictions_disable_plugin_editor' ) == 'yes';
			$disable_theme_install     = carbon_get_theme_option( 'fuertewp_restrictions_disable_theme_install' ) == 'yes';
			$disable_plugin_install    = carbon_get_theme_option( 'fuertewp_restrictions_disable_plugin_install' ) == 'yes';

			// restricted_scripts
			$restricted_scripts = explode( PHP_EOL, carbon_get_theme_option( 'fuertewp_restricted_scripts' ) );

			// restricted_pages
			$restricted_pages = explode( PHP_EOL, carbon_get_theme_option( 'fuertewp_restricted_pages' ) );

			// removed_menus
			$removed_menus = explode( PHP_EOL, carbon_get_theme_option( 'fuertewp_removed_menus' ) );

			// removed_submenus
			$removed_submenus = explode( PHP_EOL, carbon_get_theme_option( 'fuertewp_removed_submenus' ) );

			// removed_adminbar_menus
			$removed_adminbar_menus = explode( PHP_EOL, carbon_get_theme_option( 'fuertewp_removed_adminbar_menus' ) );

			// Main config array, mimics wp-config-fuerte.php
			$fuertewp = [
				'status'      => $status,
				'super_users' => $super_users,
				'general'     => [
					'access_denied_message'         => $access_denied_message,
					'recovery_email'                => $recovery_email,
					'sender_email'                  => $sender_email,
					'autoupdate_core'               => $autoupdate_core,
					'autoupdate_plugins'            => $autoupdate_plugins,
					'autoupdate_themes'             => $autoupdate_themes,
					'autoupdate_translations'       => $autoupdate_translations,
				],
				'tweaks'     => [
					'use_site_logo_login'           => $use_site_logo_login,
				],
				'restrictions' => [
					'disable_xmlrpc'                => $disable_xmlrpc,
					'disable_admin_create_edit'     => $disable_admin_create_edit,
					'disable_app_passwords'         => $disable_app_passwords,
					'disable_weak_passwords'        => $disable_weak_passwords,
					'force_strong_passwords'        => $force_strong_passwords,
					'disable_admin_bar_roles'       => $disable_admin_bar_roles,
					'restrict_permalinks'           => $restrict_permalinks,
					'restrict_acf'                  => $restrict_acf,
					'disable_theme_editor'          => $disable_theme_editor,
					'disable_plugin_editor'         => $disable_plugin_editor,
					'disable_theme_install'         => $disable_theme_install,
					'disable_plugin_install'        => $disable_plugin_install,
				],
				'emails' => [
					'fatal_error'                               => $fatal_error,
					'automatic_updates'                         => $automatic_updates,
					'comment_awaiting_moderation'               => $comment_awaiting_moderation,
					'comment_has_been_published'                => $comment_has_been_published,
					'user_reset_their_password'                 => $user_reset_their_password,
					'user_confirm_personal_data_export_request' => $user_confirm_personal_data_export_request,
					'new_user_created'                          => $new_user_created,
					'network_new_site_created'                  => $network_new_site_created,
					'network_new_user_site_registered'          => $network_new_user_site_registered,
					'network_new_site_activated'                => $network_new_site_activated,
				],
				'restricted_scripts'     => $restricted_scripts,
				'restricted_pages'       => $restricted_pages,
				'removed_menus'          => $removed_menus,
				'removed_submenus'       => $removed_submenus,
				'removed_adminbar_menus' => $removed_adminbar_menus,
			];

			// Store our processed config inside a transient, with long expiration date. Cache auto-clears when Fuerte-WP options are saved.
			set_transient( 'fuertewp_cache_config', $fuertewp, 30 * DAY_IN_SECONDS );
		}

		return $fuertewp;
	}

	/**
	 * Enforcer method
	 */
	protected function enforcer()
	{
		global $pagenow, $current_user;

		$fuertewp = $this->config_setup();

		if ( ! isset( $current_user ) ) {
			$current_user = wp_get_current_user();
		}

		if ( $fuertewp['status'] != 'enabled' ) {
			return;
		}

		/**
		 * Themes & Plugins auto updates
		 */
		if ( isset( $fuertewp['general']['autoupdate_core'] ) && true === $fuertewp['general']['autoupdate_core'] ) {
			add_filter( 'auto_update_core', '__return_true', 9999 );
			add_filter( 'allow_minor_auto_core_updates', '__return_true', 9999 );
			add_filter( 'allow_major_auto_core_updates', '__return_true', 9999 );
		}

		if ( isset( $fuertewp['general']['autoupdate_plugins'] ) && true === $fuertewp['general']['autoupdate_plugins'] ) {
			add_filter( 'auto_update_plugin', '__return_true', 9999 );
		}

		if ( isset( $fuertewp['general']['autoupdate_themes'] ) && true === $fuertewp['general']['autoupdate_themes'] ) {
			add_filter( 'auto_update_theme', '__return_true', 9999 );
		}

		if ( isset( $fuertewp['general']['autoupdate_translations'] ) && true === $fuertewp['general']['autoupdate_translations'] ) {
			add_filter( 'autoupdate_translations', '__return_true', 9999 );
		}

		/**
		 * Disable XML-RPC API
		 */
		if ( isset( $fuertewp['restrictions']['disable_xmlrpc'] ) && true === $fuertewp['restrictions']['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false', 9999 );
		}

		/**
		 * Change recovery mode email
		 */
		add_filter( 'recovery_mode_email', array(__CLASS__, 'recovery_email_address'), 9999 );

		/**
		 * Change WP sender email address
		 */
		add_filter( 'wp_mail_from', array(__CLASS__, 'sender_email_address'), 9999 );
		add_filter( 'wp_mail_from_name', array(__CLASS__, 'sender_email_address'), 9999 );

		/**
		 * Disable WP notification emails
		 */
		if ( isset( $fuertewp['emails']['comment_awaiting_moderation'] ) && false === $fuertewp['emails']['comment_awaiting_moderation'] ) {
			add_filter( 'notify_moderator', '__return_false', 9999 );
		}

		if ( isset( $fuertewp['emails']['comment_has_been_published'] ) && false === $fuertewp['emails']['comment_has_been_published'] ) {
			add_filter( 'notify_post_author', '__return_false', 9999 );
		}

		if ( isset( $fuertewp['emails']['user_reset_their_password'] ) && false === $fuertewp['emails']['user_reset_their_password'] ) {
			remove_action( 'after_password_reset', 'wp_password_change_notification', 9999 );
		}

		if ( isset( $fuertewp['emails']['user_confirm_personal_data_export_request'] ) && false === $fuertewp['emails']['user_confirm_personal_data_export_request'] ) {
			remove_action( 'user_request_action_confirmed', '_wp_privacy_send_request_confirmation_notification', 9999 );
		}

		if ( isset( $fuertewp['emails']['automatic_updates'] ) && false === $fuertewp['emails']['automatic_updates'] ) {
			add_filter( 'auto_core_update_send_email', '__return_false', 9999 );
			add_filter( 'send_core_update_notification_email', '__return_false', 9999 );
			add_filter( 'auto_plugin_update_send_email', '__return_false' );
			add_filter( 'auto_theme_update_send_email', '__return_false' );
		}

		if ( isset( $fuertewp['emails']['new_user_created'] ) && false === $fuertewp['emails']['new_user_created'] ) {
			remove_action( 'register_new_user', 'wp_send_new_user_notifications', 9999 );
			remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications', 9999 );
			remove_action( 'network_site_new_created_user', 'wp_send_new_user_notifications', 9999 );
			remove_action( 'network_site_users_created_user', 'wp_send_new_user_notifications', 9999 );
			remove_action( 'network_user_new_created_user', 'wp_send_new_user_notifications', 9999 );
		}

		if ( isset( $fuertewp['emails']['network_new_site_created'] ) && false === $fuertewp['emails']['network_new_site_created'] ) {
			add_filter( 'send_new_site_email', '__return_false', 9999 );
		}

		if ( isset( $fuertewp['emails']['network_new_user_site_registered'] ) && false === $fuertewp['emails']['network_new_user_site_registered'] ) {
			add_filter( 'wpmu_signup_blog_notification', '__return_false', 9999 );
		}

		if ( isset( $fuertewp['emails']['network_new_site_activated'] ) && false === $fuertewp['emails']['network_new_site_activated'] ) {
			remove_action( 'wp_initialize_site', 'newblog_notify_siteadmin', 9999 );
		}

		if ( isset( $fuertewp['emails']['fatal_error'] ) && false === $fuertewp['emails']['fatal_error'] ) {
			define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );
		}

		// Check if current user should be affected by Fuerte-WP
		if ( ! in_array( strtolower( $current_user->user_email ), $fuertewp['super_users'] ) || defined( 'FUERTEWP_FORCE' ) && true === FUERTEWP_FORCE ) {
			// Everywhere tweaks (wp-admin or not)
			// Custom Javascript
			add_filter( 'admin_footer', array(__CLASS__, 'custom_javascript'), 9999 );
			add_filter( 'login_head', array(__CLASS__, 'custom_javascript'), 9999 );

			// Custom CSS
			add_filter( 'admin_head', array(__CLASS__, 'custom_css'), 9999 );
			add_filter( 'login_head', array(__CLASS__, 'custom_css'), 9999 );

			// Custom login logo
			add_action( 'login_enqueue_scripts', array(__CLASS__, 'custom_login_logo'), 9999 );
			add_action( 'login_headerurl', array(__CLASS__, 'custom_login_url'), 9999 );
			add_action( 'login_headertitle', array(__CLASS__, 'custom_login_title'), 9999 );

			// wp-admin only tweaks
			if ( is_admin() ) {
				// Fuerte-WP self-protect
				$this->self_protect();

				// Disable Theme Editor
				if ( isset( $fuertewp['restrictions']['disable_theme_editor'] ) && true === $fuertewp['restrictions']['disable_theme_editor'] ) {
					if ( $pagenow == 'theme-editor.php' ) {
						$this->access_denied();
					}
				}

				// Disable Plugin Editor
				if ( isset( $fuertewp['restrictions']['disable_plugin_editor'] ) && true === $fuertewp['restrictions']['disable_plugin_editor'] ) {
					if ( $pagenow == 'plugin-editor.php' ) {
						$this->access_denied();
					}
				}

				// Both? Theme and Plugin Editor?
				if ( ( isset( $fuertewp['restrictions']['disable_theme_editor'] ) && true === $fuertewp['restrictions']['disable_theme_editor'] ) && ( isset( $fuertewp['restrictions']['disable_plugin_editor'] ) && true === $fuertewp['restrictions']['disable_plugin_editor'] ) ) {
					define( 'DISALLOW_FILE_EDIT', true );
				}

				// Disable Theme Install
				if ( isset( $fuertewp['restrictions']['disable_theme_install'] ) && true === $fuertewp['restrictions']['disable_theme_install'] ) {
					if ( $pagenow == 'theme-install.php' ) {
						$this->access_denied();
					}
				}

				// Disable Plugin Install
				if ( isset( $fuertewp['restrictions']['disable_plugin_install'] ) && true === $fuertewp['restrictions']['disable_plugin_install'] ) {
					if ( $pagenow == 'plugin-install.php' ) {
						$this->access_denied();
					}
				}

				// Both? New Themes and Plugins Installations?
				// First, let's check if wp's scheduler trigger auto-updates without this. Just to be safe. This definition is just an extra security step anyways. The main restiction is already happening before, for theme-install.php and plugin-install.php.
				/* if ( ( isset( $fuertewp['restrictions']['disable_theme_install'] ) && true === $fuertewp['restrictions']['disable_theme_install'] ) && ( isset( $fuertewp['restrictions']['disable_plugin_install'] ) && true === $fuertewp['restrictions']['disable_plugin_install'] ) ) {
					define( 'DISALLOW_FILE_MODS', true );
				} */

				// Disable Application Passwords
				if ( isset( $fuertewp['restrictions']['disable_app_passwords'] ) && true === $fuertewp['restrictions']['disable_app_passwords'] ) {
					add_filter( 'wp_is_application_passwords_available', '__return_false', 9999 );
				}

				// Remove menu items
				add_filter( 'admin_menu', array( __CLASS__, 'remove_menus' ), 9999 );

				// Remove adminbar menu items
				add_filter( 'admin_bar_menu', array( __CLASS__, 'remove_adminbar_menus' ), 9999 );

				// Disallow create/edit admin users
				if ( isset( $fuertewp['restrictions']['disable_admin_create_edit'] ) && true === $fuertewp['restrictions']['disable_admin_create_edit'] ) {
					add_filter( 'editable_roles', array( __CLASS__, 'create_edit_role_check' ), 9999 );
				}

				// Disallowed wp-admin scripts
				if ( isset( $fuertewp['restricted_scripts'] ) && in_array( $pagenow, $fuertewp['restricted_scripts'] ) && ! wp_doing_ajax() ) {
					$this->access_denied();
				}

				// Disallowed wp-admin pages
				if ( isset( $fuertewp['restricted_pages'] ) && isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $fuertewp['restricted_pages'] ) && !wp_doing_ajax() ) {
					$this->access_denied();
				}

				// No user switching
				if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'switch_to_user' ) {
					$this->access_denied();
				}

				// No protected users editing
				if ( $pagenow == 'user-edit.php' ) {
					if ( isset( $_REQUEST['user_id'] ) && !empty( $_REQUEST['user_id'] ) ) {
						$user_info = get_userdata( $_REQUEST['user_id'] );

						if ( in_array( strtolower( $user_info->user_email ), $fuertewp['super_users'] ) ) {
							$this->access_denied();
						}
					}
				}

				// No protected users deletion
				if ( $pagenow == 'users.php' ) {
					if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' ) {

						if ( isset( $_REQUEST['users'] ) ) {
							// Single user
							foreach ( $_REQUEST['users'] as $user ) {
								$user_info = get_userdata( $user );

								if ( in_array( strtolower( $user_info->user_email ), $fuertewp['super_users'] ) ) {
									$this->access_denied();
								}
							}
						} elseif ( isset( $_REQUEST['user'] ) ) {
							// Batch deletion
							$user_info = get_userdata( $_REQUEST['user'] );

							if ( in_array( strtolower( $user_info->user_email ), $fuertewp['super_users'] ) ) {
								$this->access_denied();
							}
						}
					}
				}

				if ( isset( $fuertewp['restrictions']['restrict_acf'] ) && true === $fuertewp['restrictions']['restrict_acf'] ) {
					// No ACF editor menu
					add_filter( 'acf/settings/show_admin', '__return_false', 9999 );

					if ( in_array( $pagenow, ['post.php'] ) && isset( $_GET['post'] ) && 'acf-field-group' === get_post_type( $_GET['post'] ) ) {
						$this->access_denied();
					}

					if ( in_array($pagenow, ['edit.php', 'post-new.php'] ) && isset( $_GET['post_type'] ) && 'acf-field-group' === $_GET['post_type'] ) {
						$this->access_denied();
					}
				}

				if ( isset( $fuertewp['restrictions']['restrict_permalinks'] ) && true === $fuertewp['restrictions']['restrict_permalinks'] ) {
					// No Permalinks config access
					if ( in_array( $pagenow, ['options-permalink.php'] ) ) {
						$this->access_denied();
					}

					add_action( 'admin_menu', function() {
						remove_submenu_page( 'options-general.php', 'options-permalink.php' );
					}, 9999 );
				}
			} // is_admin()

			// Outside wp-admin tweaks
			if ( ! is_admin() ) {
				// Disable admin bar for certain roles
				if ( isset( $fuertewp['restrictions']['disable_admin_bar_roles'] ) && ! empty( $fuertewp['restrictions']['disable_admin_bar_roles'] ) ) {
					if ( is_array( $fuertewp['restrictions']['disable_admin_bar_roles'] ) ) {
						// Loop and disable if user has a defined role
						foreach ( $fuertewp['restrictions']['disable_admin_bar_roles'] as $role ) {
							if ( true === $this->has_role( $role ) ) {
								add_filter( 'show_admin_bar', '__return_false', 9999 );
							}
						}
					}
				}
			} // !is_admin()
		} // user affected by Fuerte-WP
	}

	/**
	 * Fuerte-WP self-protection
	 */
	private function self_protect()
	{
		global $pagenow;

		// Remove Fuerte-WP from admin menu
		add_action( 'admin_menu', function() {
			remove_submenu_page( 'options-general.php', 'crb_carbon_fields_container_fuerte-wp.php' );
		}, 9999 );

		// Prevent direct deactivation
		if ( isset( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'deactivate'
			&& $pagenow == 'plugins.php'
			&& isset( $_REQUEST['plugin'] )
			&& stripos( $_REQUEST['plugin'], 'fuerte-wp' ) !== false
		) {
			$this->access_denied();
		}

		// Check if a non super-user is accessing our plugin options
		if ( $pagenow == 'options-general.php' && isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'crb_carbon_fields_container_fuerte-wp.php' ) {
			$this->access_denied();
		}

		// Hide deactivation link
		add_filter( 'plugin_action_links', function ( $actions, $plugin_file ) {
			if ( plugin_basename( FUERTEWP_PLUGIN_BASE ) === $plugin_file ) {
				unset( $actions['deactivate'] );
			}

			return $actions;
		}, 9999, 2 );
	}

	/**
	 * Prints and ends WP execution with "Access denied" message
	 */
	protected function access_denied()
	{
		global $fuertewp;

		if ( ! isset( $fuertewp['general']['access_denied_message'] ) || empty( $fuertewp['general']['access_denied_message'] ) ) {
			$fuertewp['general']['access_denied_message'] = 'Access denied.';
		}

		wp_die( $fuertewp['general']['access_denied_message'] );
		return false;
	}

	/**
	 * Set WP sender email address
	 *
	 * @return string    Email address
	 */
	static function sender_email_address()
	{
		global $fuertewp;

		if ( ! isset ( $fuertewp['general']['sender_email'] ) || empty( $fuertewp['general']['sender_email'] ) ) {
			$sender_email_address = 'no-reply@' . parse_url( home_url() )['host'];
		} else {
			$sender_email_address = $fuertewp['general']['sender_email'];
		}

		return $sender_email_address;
	}

	/**
	 * Change WP recovery email adresss
	 *
	 * @return string    Email address
	 */
	static function recovery_email_address()
	{
		global $fuertewp, $pagenow, $current_user;

		if ( ! isset ( $fuertewp['general']['recovery_email'] ) || empty( $fuertewp['general']['recovery_email'] ) ) {
			$recovery_email = 'dev@' . parse_url( home_url() )['host'];
		} else {
			$recovery_email = $fuertewp['general']['recovery_email'];
		}

		$email_data['to'] = $recovery_email;

		return $email_data;
	}

	/**
	 * Remove wp-admin menus
	 */
	static function remove_menus()
	{
		global $fuertewp;

		if ( isset( $fuertewp['restricted_scripts'] ) && ! empty( $fuertewp['restricted_scripts'] ) ) {
			foreach ( $fuertewp['restricted_scripts'] as $item ) {
				if ( substr( $item, 0, 2 ) === '//' ) {
					// Commented item, skip it
					continue;
				}

				remove_menu_page( $item );
			}
		}

		if ( isset( $fuertewp['removed_menus'] ) && ! empty( $fuertewp['removed_menus'] ) ) {
			foreach ( $fuertewp['removed_menus'] as $slug ) {
				remove_menu_page( $slug );
			}
		}

		if ( isset( $fuertewp['removed_submenus'] ) && ! empty( $fuertewp['removed_submenus'] ) ) {
			$submenu_parts = [];
			foreach ( $fuertewp['removed_submenus'] as $item ) {
				$submenu_parts = explode( '|', $item );

				remove_submenu_page( $submenu_parts[0], $submenu_parts[1] );
			}
		}
	}

	/**
	 * Remove adminbar menus (nodes)
	 */
	static function remove_adminbar_menus( $wp_admin_bar )
	{
		global $fuertewp;

		if ( isset( $fuertewp['removed_adminbar_menus'] ) && ! empty( $fuertewp['removed_adminbar_menus'] ) ) {
			foreach ( $fuertewp['removed_adminbar_menus'] as $item ) {
				$wp_admin_bar->remove_node( $item );
			}

			define( 'UPDRAFTPLUS_ADMINBAR_DISABLE', true );
		}
	}

	/**
	 * Check if a role can be created/edited
	 *
	 * @return array    Roles array, without administrator role
	 */
	static function create_edit_role_check( $roles )
	{
		unset( $roles['administrator'] );

		return $roles;
	}

	/**
	 * Check current user role
	 * https://wordpress.org/support/article/roles-and-capabilities/
	 *
	 * @return bool    True if it has the role
	 */
	static function has_role( $role = 'subscriber' )
	{
		$user = wp_get_current_user();

		if ( in_array( $role, (array) $user->roles ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Custom Javascript at footer
	 */
	static function custom_javascript()
	{
		global $fuertewp;
	?>
		<script type="text/javascript">
			document.addEventListener("DOMContentLoaded", function() {
			<?php
			// Disable typing a custom password (new user, profile edit, lost password).
			// Needed outside wp-admin, because reset password screen
			if ( isset( $fuertewp['restrictions']['force_strong_passwords'] ) && true === $fuertewp['restrictions']['force_strong_passwords'] ) :
			?>
				if (document.body.classList.contains('user-new-php') ||
					document.body.classList.contains('user-edit-php') ||
					document.body.classList.contains('login') ||
					document.body.classList.contains('profile-php')) {
					document.getElementById('pass1').setAttribute('readonly', 'readonly');
				}
			<?php
			endif;
			?>
			});
		</script>
	<?php
	}

	/**
	 * Custom CSS at header
	 */
	static function custom_css()
	{
		global $fuertewp;
	?>
		<style type="text/css">
		<?php
		// Hides "Confirm use of weak password" checkbox on weak password, forcing a medium one at the very minimum.
		// Needed outside wp-admin, because reset password screen
		if ( isset( $fuertewp['restrictions']['disable_weak_passwords'] ) && true === $fuertewp['restrictions']['disable_weak_passwords'] ) :
		?>
			.pw-weak { display: none !important; }
		<?php
		endif;
		?>

		<?php
		// Hides ACF cog that allow users access ACF editable meta boxes UI
		if ( isset( $fuertewp['restrictions']['restrict_acf'] ) && true === $fuertewp['restrictions']['restrict_acf'] ) :
		?>
			.wp-admin h3.hndle.ui-sortable-handle a.acf-hndle-cog { display: none !important; visibility: hidden !important; }
		<?php
		endif;
		?>
		</style>
	<?php
	}

	/**
	 * WP Login custom logo
	 */
	static function custom_login_logo()
	{
		global $fuertewp;

		if ( isset( $fuertewp['tweaks']['use_site_logo_login'] ) && true === $fuertewp['tweaks']['use_site_logo_login'] ) {
			if ( ! has_custom_logo() ) {
				return;
			}

			?>
			<style type="text/css">
				#login h1 a, .login h1 a {
					background-image: url(<?php echo esc_url( wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) ); ?>);
					background-repeat: no-repeat;
					padding-bottom: 20px;
				}
			</style>
			<?php
		}
	}

	/**
	 * WP Login custom logo URL
	 *
	 * @return string    Blog URL
	 */
	static function custom_login_url()
	{
		global $fuertewp;

		if ( isset( $fuertewp['tweaks']['use_site_logo_login'] ) && true === $fuertewp['tweaks']['use_site_logo_login'] ) {
			return home_url();
		}
	}

	/**
	 * WP Login custom logo title
	 *
	 * @return string    Blog name
	 */
	static function custom_login_title()
	{
		global $fuertewp;

		if ( isset( $fuertewp['tweaks']['use_site_logo_login'] ) && true === $fuertewp['tweaks']['use_site_logo_login'] ) {
			return get_bloginfo( 'name' );
		}
	}

	// Work in Progress...
	static function recommended_plugins()
	{
		global $fuertewp, $pagenow;

		$show_notice            = false;
		$plugin_recommendations = [];

		if ( ! isset( $fuertewp['recommended_plugins'] ) || empty( $fuertewp['recommended_plugins'] ) ) {
			return;
		}

		if ( current_user_can( 'activate_plugins' ) && ( ! wp_doing_ajax() ) ) {
			if ( is_array( $fuertewp['recommended_plugins'] ) ) {
				foreach ( $fuertewp['recommended_plugins'] as $plugin ) {
					if ( ! is_plugin_active( $plugin ) && ! is_plugin_active_for_network( $plugin ) ) {
						$show_notice              = true;
						$plugin_recommendations[] = $plugin;
					}
				}
			}
		}

		if ( true === $show_notice && ( $pagenow == 'plugins.php' || ( isset($_REQUEST['page'] ) && $_REQUEST['page'] == 'wc-settings' ) || $pagenow == 'options-general.php' ) ) {
			//add_action( 'admin_notices', 'fuertewp_recommended_plugins_notice' );
		}
	}
} // Class Fuerte_Wp_Enforcer
