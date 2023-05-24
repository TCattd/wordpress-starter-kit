<?php

namespace LLAR\Core;

use Exception;
use IXR_Error;
use LLAR\Core\Http\Http;
use WP_Error;
use WP_User;

if( !defined( 'ABSPATH' ) ) exit;

class LimitLoginAttempts {
	/**
	* Admin options page slug
	* @var string
	*/
	private $_options_page_slug = 'limit-login-attempts';

	/**
	* Errors messages
	*
	* @var array
	*/
	public $_errors = array();

	/**
	* Additional login errors messages that we need to show
	*
	* @var array
	*/
	public $other_login_errors = array();

	/**
     * Current app object
     *
	 * @var CloudApp
	 */
	public static $cloud_app = null;

	public function __construct() {

	    Config::init();
		Http::init();

		$this->hooks_init();
        $this->cloud_app_init();

		(new Shortcodes())->register();
		(new Ajax())->register();
	}

	/**
	* Register wp hooks and filters
	*/
	public function hooks_init() {

		add_action( 'plugins_loaded', array( $this, 'setup' ), 9999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'login_page_enqueue' ) );
		add_filter( 'limit_login_whitelist_ip', array( $this, 'check_whitelist_ips' ), 10, 2 );
		add_filter( 'limit_login_whitelist_usernames', array( $this, 'check_whitelist_usernames' ), 10, 2 );
		add_filter( 'limit_login_blacklist_ip', array( $this, 'check_blacklist_ips' ), 10, 2 );
		add_filter( 'limit_login_blacklist_usernames', array( $this, 'check_blacklist_usernames' ), 10, 2 );

		add_filter( 'illegal_user_logins', array( $this, 'register_user_blacklist' ), 999 );
		add_filter( 'um_custom_authenticate_error_codes', array( $this, 'ultimate_member_register_error_codes' ) );

		// TODO: Temporary turn off the holiday warning.
		//add_action( 'admin_notices', array( $this, 'show_enable_notify_notice' ) );

		add_action( 'admin_notices', array( $this, 'show_leave_review_notice' ) );

		add_action( 'admin_print_scripts-toplevel_page_limit-login-attempts', array( $this, 'load_admin_scripts' ) );
		add_action( 'admin_print_scripts-settings_page_limit-login-attempts', array( $this, 'load_admin_scripts' ) );
		add_action( 'admin_print_scripts-index.php', array( $this, 'load_admin_scripts' ) );

		add_action( 'admin_init', array( $this, 'dashboard_page_redirect' ), 9999 );
		add_action( 'admin_init', array( $this, 'setup_cookie' ), 10 );

		add_action( 'login_footer', array( $this, 'login_page_gdpr_message' ) );
		add_action( 'login_footer', array( $this, 'login_page_render_js' ), 9999 );
		add_action( 'wp_footer', array( $this, 'login_page_render_js' ), 9999 );

		if( !Config::get( 'hide_dashboard_widget' ) )
		    add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );

		register_activation_hook( LLA_PLUGIN_FILE, array( $this, 'activation' ) );
	}

	/**
	 * Runs when the plugin is activated
	 */
	public function activation() {

		if( !Config::get( 'activation_timestamp' ) ) {

            set_transient( 'llar_dashboard_redirect', true, 30 );
		}
	}

	public function setup_cookie() {

		if (empty($_GET['page']) || $_GET['page'] !== $this->_options_page_slug) return;

		$cookie_name = 'llar_menu_alert_icon_shown';

		if (empty($_COOKIE[$cookie_name])) {
			setcookie($cookie_name, '1', strtotime( 'tomorrow' ));
		}
	}

	public function register_dashboard_widgets() {

	    if( !current_user_can( 'manage_options' ) ) return;

		wp_add_dashboard_widget(
            'llar_stats_widget',
            __( 'Limit Login Attempts Reloaded', 'limit-login-attempts-reloaded' ),
            array( $this, 'dashboard_widgets_content' ),
            null,
            null,
            'normal',
            'high'
        );
    }

    public function dashboard_widgets_content() {
		include_once( LLA_PLUGIN_DIR . 'views/admin-dashboard-widgets.php' );
    }

	/**
	 * Redirect to dashboard page after installed
	 */
	public function dashboard_page_redirect() {

	    if( ! get_transient( 'llar_dashboard_redirect' ) || isset( $_GET['activate-multi'] ) || is_network_admin() ) {
	        return;
        }

		delete_transient( 'llar_dashboard_redirect' );

	    wp_redirect( admin_url( 'index.php?page=' . $this->_options_page_slug ) );
	    exit();
    }

	/**
	* Hook 'plugins_loaded'
	*/
	public function setup() {

		if( ! ( $activation_timestamp = Config::get( 'activation_timestamp' ) ) ) {

			// Write time when the plugin is activated
			Config::update( 'activation_timestamp', time() );
		}

		if( ! ( $activation_timestamp = Config::get( 'notice_enable_notify_timestamp' ) ) ) {

			// Write time when the plugin is activated
			Config::update( 'notice_enable_notify_timestamp', strtotime( '-32 day' ) );
		}

		if( version_compare( Helpers::get_wordpress_version(), '5.5', '<' ) ) {
			Config::update( 'auto_update_choice', 0 );
        }

		// Load languages files
		load_plugin_textdomain( 'limit-login-attempts-reloaded', false, plugin_basename( dirname( __FILE__ ) ) . '/../languages' );

		// Check if installed old plugin
		$this->check_original_installed();

		// Setup default plugin options
		//$this->sanitize_options();

		add_action( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
		add_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999, 2 );

		add_filter( 'shake_error_codes', array( $this, 'failure_shake' ) );
		add_action( 'login_errors', array( $this, 'fixup_error_messages' ) );

		if ( Helpers::is_network_mode() ) {
			add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );

			if( Config::get( 'show_warning_badge' ) )
			    add_action( 'network_admin_menu', array( $this, 'network_setting_menu_alert_icon' ) );
		}

		if ( Helpers::allow_local_options() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			if( Config::get( 'show_warning_badge' ) )
			    add_action( 'admin_menu', array( $this, 'setting_menu_alert_icon' ) );
		}

		// Add notices for XMLRPC request
		add_filter( 'xmlrpc_login_error', array( $this, 'xmlrpc_error_messages' ) );

		// Add notices to woocommerce login page
		add_action( 'wp_head', array( $this, 'add_wc_notices' ) );

		/*
		* This action should really be changed to the 'authenticate' filter as
		* it will probably be deprecated. That is however only available in
		* later versions of WP.
		*/
		add_action( 'wp_authenticate', array( $this, 'track_credentials' ), 10, 2 );
		add_action( 'authenticate', array( $this, 'authenticate_filter' ), 5, 3 );

		/**
		 * BuddyPress unactivated user account message fix
         * Wordfence error message fix
		 */
		add_action( 'authenticate', array( $this, 'authenticate_filter_errors_fix' ), 35, 3 );

		add_filter( 'plugin_action_links_' . LLA_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
	}

	public function login_page_gdpr_message() {

	    if( ! Config::get( 'gdpr' ) || isset( $_REQUEST['interim-login'] ) ) return;

	    ?>
            <div id="llar-login-page-gdpr">
                <div class="llar-login-page-gdpr__message"><?php echo do_shortcode( stripslashes( Config::get( 'gdpr_message' ) ) ); ?></div>
            </div>
        <?php
    }

	public function login_page_render_js() {
	    global $limit_login_just_lockedout;

	    if( $limit_login_just_lockedout ||
            ( Config::get( 'active_app' ) === 'local' && !$this->is_limit_login_ok() ) ||
            ( self::$cloud_app && !empty( self::$cloud_app->get_errors() ) )
        ) return;

	    $is_wp_login_page = isset( $_POST['log'] );
	    $is_woo_login_page = ( function_exists( 'is_account_page' ) && is_account_page() && isset( $_POST['username'] ) );
	    $is_um_login_page = ( function_exists( 'um_is_core_page' ) && um_is_core_page( 'login' ) && !empty( $_POST ) );

		if( ( $is_wp_login_page || $is_woo_login_page || $is_um_login_page ) ) : ?>
        <script>
            ;(function($) {
                var ajaxUrlObj = new URL('<?php echo admin_url( 'admin-ajax.php' ); ?>');
                ajaxUrlObj.protocol = location.protocol;

                $.post(ajaxUrlObj.toString(), {
                    action: 'get_remaining_attempts_message',
                    sec: '<?php echo wp_create_nonce( "llar-action" ); ?>'
                }, function(response) {
                    if(response.success && response.data) {
                        $('#login_error').append("<br>" + response.data);
                        $('.um-notice.err').append("<br>" + response.data);
                        $('.woocommerce-error').append("<li>(" + response.data + ")</li>");
                    }
                })
            })(jQuery)
        </script>
        <?php
        endif;
    }

	public function add_action_links( $actions ) {

		$actions = array_merge( array(
			'<a href="' . $this->get_options_page_uri() . '">' . __( 'Dashboard', 'limit-login-attempts-reloaded' ) . '</a>',
			'<a href="' . $this->get_options_page_uri( 'settings' ) . '">' . __( 'Settings', 'limit-login-attempts-reloaded' ) . '</a>',
		), $actions );

		if(Config::get( 'active_app' ) === 'local') {

			$actions = array_merge( array(
				'<a href="https://www.limitloginattempts.com/info.php?from=plugin-plugins" target="_blank" style="font-weight: bold;">' . __( 'Upgrade to Premium', 'limit-login-attempts-reloaded' ) . '</a>',
			), $actions );
		}

		return $actions;
	}

	public function cloud_app_init() {

		if( Config::get( 'active_app' ) === 'custom' && $config = Config::get( 'app_config' ) ) {

			self::$cloud_app = new CloudApp( $config );
		}
    }

	public function load_admin_scripts() {

	    if( !empty( $_REQUEST['page'] ) && $_REQUEST['page'] !== $this->_options_page_slug ) return;

		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_style('llar-jquery-ui', LLA_PLUGIN_URL.'assets/css/jquery-ui.css');

		wp_enqueue_script( 'llar-charts', LLA_PLUGIN_URL . 'assets/js/chart.umd.js' );
	}

	public function check_whitelist_ips( $allow, $ip ) {
		return Helpers::ip_in_range( $ip, (array) Config::get( 'whitelist' ) );
	}

	public function check_whitelist_usernames( $allow, $username ) {
		return in_array( $username, (array) Config::get( 'whitelist_usernames' ) );
	}

	public function check_blacklist_ips( $allow, $ip ) {
		return Helpers::ip_in_range( $ip, (array) Config::get( 'blacklist' ) );
	}

	public function check_blacklist_usernames( $allow, $username ) {
		return in_array( $username, (array) Config::get( 'blacklist_usernames' ) );
	}

	/**
	 * @param $blacklist
	 * @return array|null
	 */
	public function register_user_blacklist($blacklist) {

		$black_list_usernames = Config::get( 'blacklist_usernames' );

		if(!empty($black_list_usernames) && is_array($black_list_usernames)) {
			$blacklist += $black_list_usernames;
		}

		return $blacklist;
	}

	/**
	* @param $error IXR_Error
	*
	* @return IXR_Error
	*/
	public function xmlrpc_error_messages( $error ) {

		if ( ! class_exists( 'IXR_Error' ) ) {
			return $error;
		}

		if( $login_error = $this->get_message() ) {

			return new IXR_Error( 403, strip_tags( $login_error ) );
        }

		return $error;
	}

	/**
	* Errors on WooCommerce account page
	*/
	public function add_wc_notices() {

		global $limit_login_just_lockedout, $limit_login_nonempty_credentials, $limit_login_my_error_shown;

		if ( ! function_exists( 'is_account_page' ) || ! function_exists( 'wc_add_notice' ) || !$limit_login_nonempty_credentials ) {
			return;
		}

		/*
		* During lockout we do not want to show any other error messages (like
		* unknown user or empty password).
		*/
		if ( empty( $_POST ) && ! $this->is_limit_login_ok() && ! $limit_login_just_lockedout ) {
			if ( is_account_page() ) {
				wc_add_notice( $this->error_msg(), 'error' );
			}
		}

	}

	/**
	 * @param $user
	 * @param $username
	 * @param $password
	 *
	 * @return WP_Error | WP_User
	 * @throws Exception
	 */
	public function authenticate_filter( $user, $username, $password ) {

		if(!session_id()) {
			session_start();
		}

		if ( ! empty( $username ) && ! empty( $password ) ) {

		    if( self::$cloud_app && $response = self::$cloud_app->acl_check( array(
				    'ip'        => Helpers::get_all_ips(),
				    'login'     => $username,
				    'gateway'   => Helpers::detect_gateway()
			    ) ) ) {

			    if( $response['result'] === 'deny' ) {

					unset($_SESSION['login_attempts_left']);

					remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );
					remove_filter( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
					remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );

			        // Remove default WP authentication filters
					remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
					remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );

					$err = __( '<strong>ERROR</strong>: Too many failed login attempts.', 'limit-login-attempts-reloaded' );

					$time_left = ( !empty( $acl_result['time_left'] ) ) ? $acl_result['time_left'] : 0;
					if( $time_left ) {

						if ( $time_left > 60 ) {
							$time_left = ceil( $time_left / 60 );
							$err .= ' ' . sprintf( _n( 'Please try again in %d hour.', 'Please try again in %d hours.', $time_left, 'limit-login-attempts-reloaded' ), $time_left );
						} else {
							$err .= ' ' . sprintf( _n( 'Please try again in %d minute.', 'Please try again in %d minutes.', $time_left, 'limit-login-attempts-reloaded' ), $time_left );
						}
                    }

				    self::$cloud_app->add_error( $err );

					$user = new WP_Error();
					$user->add( 'username_blacklisted', $err );

					if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {

						header('HTTP/1.0 403 Forbidden');
						exit;
					}
                }
                else if( $response['result'] === 'pass' ) {

					remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );
					remove_filter( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
					remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );
                }

            } else {

				$ip = $this->get_address();

				// Check if username is blacklisted
				if ( ! $this->is_username_whitelisted( $username ) && ! $this->is_ip_whitelisted( $ip ) &&
					( $this->is_username_blacklisted( $username ) || $this->is_ip_blacklisted( $ip ) )
				) {

				    unset($_SESSION['login_attempts_left']);

					remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );
					remove_filter( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
					remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );

					// Remove default WP authentication filters
					remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
					remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );

					$user = new WP_Error();
					$user->add( 'username_blacklisted', "<strong>ERROR:</strong> Too many failed login attempts." );

					if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {

						header('HTTP/1.0 403 Forbidden');
						exit;
                    }

				} elseif ( $this->is_username_whitelisted( $username ) || $this->is_ip_whitelisted( $ip ) ) {

					remove_filter( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
					remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );
					remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );

				}
            }
		}

		return $user;
	}

	/**
     * Fix displaying the errors of other plugins
	 *
	 * @param $user
	 * @param $username
	 * @param $password
	 * @return mixed
	 */
	public function authenticate_filter_errors_fix( $user, $username, $password ) {

		if ( ! empty( $username ) && ! empty( $password ) ) {

		    if( is_wp_error($user) ) {

		        // BuddyPress errors
                if( in_array('bp_account_not_activated', $user->get_error_codes() ) ) {

					$this->other_login_errors[] = $user->get_error_message('bp_account_not_activated');
				}
                // Wordfence errors
                else if( in_array('wfls_captcha_verify', $user->get_error_codes() ) ) {

					$this->other_login_errors[] = $user->get_error_message('wfls_captcha_verify');
				}
            }

		}
		return $user;
	}

	public function ultimate_member_register_error_codes( $codes ) {

	    if( !is_array( $codes ) ) return $codes;

		$codes[] = 'too_many_retries';
		$codes[] = 'username_blacklisted';

		return $codes;
	}

	/**
	* Check if the original plugin is installed
	*/
	private function check_original_installed()
	{
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		if ( is_plugin_active('limit-login-attempts/limit-login-attempts.php') )
		{
			deactivate_plugins( 'limit-login-attempts/limit-login-attempts.php', true );
			//add_action('plugins_loaded', 'limit_login_setup', 99999);
			remove_action( 'plugins_loaded', 'limit_login_setup', 99999 );
		}
	}

	/**
	* Enqueue js and css
	*/
	public function enqueue() {

	    $plugin_data = get_plugin_data( LLA_PLUGIN_DIR . 'limit-login-attempts-reloaded.php' );

		wp_enqueue_style( 'lla-main', LLA_PLUGIN_URL . 'assets/css/limit-login-attempts.css', array(), $plugin_data['Version'] );
//		wp_enqueue_script( 'lla-main', LLA_PLUGIN_URL . 'assets/js/limit-login-attempts.js', array(), $plugin_data['Version'] );

		if( !empty( $_REQUEST['page'] ) && $_REQUEST['page'] === $this->_options_page_slug ) {

			wp_enqueue_style( 'lla-jquery-confirm', LLA_PLUGIN_URL . 'assets/css/jquery-confirm.min.css' );
			wp_enqueue_script( 'lla-jquery-confirm', LLA_PLUGIN_URL . 'assets/js/jquery-confirm.min.js' );
        }

	}

	public function login_page_enqueue() {

	    $plugin_data = get_plugin_data( LLA_PLUGIN_DIR . 'limit-login-attempts-reloaded.php' );

		wp_enqueue_style( 'llar-login-page-styles', LLA_PLUGIN_URL . 'assets/css/login-page-styles.css', array(), $plugin_data['Version'] );
        wp_enqueue_script( 'jquery' );
	}

	/**
	* Add admin options page
	*/
	public function network_admin_menu()
	{
		add_submenu_page( 'settings.php', 'Limit Login Attempts', 'Limit Login Attempts' . $this->menu_alert_icon(), 'manage_options', $this->_options_page_slug, array( $this, 'options_page' ) );
	}

	public function admin_menu() {

		if( Config::get( 'show_top_level_menu_item' ) ) {

			add_menu_page(
                'Limit Login Attempts',
                'Limit Login Attempts' . $this->menu_alert_icon(),
                'manage_options',
                $this->_options_page_slug,
                array( $this, 'options_page' ),
				'data:image/svg+xml;base64,' . base64_encode($this->get_svg_logo_content())
            );
        }

		add_options_page( 'Limit Login Attempts', 'Limit Login Attempts' . $this->menu_alert_icon(), 'manage_options', $this->_options_page_slug, array( $this, 'options_page' ) );
	}

	public function get_svg_logo_content() {
	    return file_get_contents( LLA_PLUGIN_DIR . 'assets/img/logo.svg' );
    }

    private function menu_alert_icon() {

		if( !empty( $_COOKIE['llar_menu_alert_icon_shown'] ) ||
            Config::get( 'active_app' ) !== 'local' ||
            !Config::get( 'show_warning_badge' ) )
		        return '';

		$retries_count = 0;
        $retries_stats = Config::get( 'retries_stats' );

	    if( $retries_stats ) {
		    foreach ( $retries_stats as $key => $count ) {
			    if( is_numeric( $key ) && $key > strtotime( '-24 hours' ) ) {
				    $retries_count += $count;
			    }
                elseif( !is_numeric( $key ) && date_i18n( 'Y-m-d' ) === $key ) {
				    $retries_count += $count;
			    }
		    }
	    }

        if( $retries_count < 100 ) return '';

	    return ' <span class="update-plugins count-1 llar-alert-icon-animation"><span class="plugin-count">!</span></span>';
    }

    public function setting_menu_alert_icon() {
		global $menu;
		if( !Config::get( 'show_top_level_menu_item' ) && !empty( $menu[80][0] ) ) {
		    
			$menu[80][0] .= $this->menu_alert_icon();
		}
	}

	public function network_setting_menu_alert_icon() {
		global $menu;
		if( !empty( $menu[25][0] ) ) {

			$menu[25][0] .= $this->menu_alert_icon();
		}
	}

	/**
	 * Get the correct options page URI
	 *
	 * @param bool $tab
	 * @return mixed
	 */
	public function get_options_page_uri($tab = false)
	{

		if ( is_network_admin() )
			$uri = network_admin_url( 'settings.php?page=' . $this->_options_page_slug );
		else
		    $uri = menu_page_url( $this->_options_page_slug, false );

		if( !empty( $tab ) ) {

		    $uri = add_query_arg( 'tab', $tab, $uri );
        }

		return $uri;
	}

	/**
	* Check if it is ok to login
	*
	* @return bool
	*/
	public function is_limit_login_ok() {

		$ip = $this->get_address();

		/* Check external whitelist filter */
		if ( $this->is_ip_whitelisted( $ip ) ) {
			return true;
		}

		/* lockout active? */
		$lockouts = Config::get( 'lockouts' );

		return ( !is_array( $lockouts ) || !isset( $lockouts[ $ip ] ) || time() >= $lockouts[$ip] );
	}

	/**
	* Action when login attempt failed
	*
	* Increase nr of retries (if necessary). Reset valid value. Setup
	* lockout if nr of retries are above threshold. And more!
	*
	* A note on external whitelist: retries and statistics are still counted and
	* notifications done as usual, but no lockout is done.
	*
	* @param $username
	*/
	public function limit_login_failed( $username ) {

		if(!session_id()) {
			session_start();
		}

		$_SESSION['login_attempts_left'] = 0;

		if( self::$cloud_app && $response = self::$cloud_app->lockout_check( array(
				'ip'        => Helpers::get_all_ips(),
				'login'     => $username,
                'gateway'   => Helpers::detect_gateway()
            ) ) ) {

		    if( $response['result'] === 'allow' ) {

				$_SESSION['login_attempts_left'] = intval( $response['attempts_left'] );

            } elseif( $response['result'] === 'deny' ) {

		        global $limit_login_just_lockedout;
		        $limit_login_just_lockedout = true;

		        $err = __( '<strong>ERROR</strong>: Too many failed login attempts.', 'limit-login-attempts-reloaded' );

		        $time_left = ( !empty( $response['time_left'] ) ) ? $response['time_left'] : 0;
				if ( $time_left > 60 ) {
					$time_left = ceil( $time_left / 60 );
					$err .= ' ' . sprintf( _n( 'Please try again in %d hour.', 'Please try again in %d hours.', $time_left, 'limit-login-attempts-reloaded' ), $time_left );
				} else {
					$err .= ' ' . sprintf( _n( 'Please try again in %d minute.', 'Please try again in %d minutes.', $time_left, 'limit-login-attempts-reloaded' ), $time_left );
				}

			    self::$cloud_app->add_error( $err );
            }

		} else {

			$ip = $this->get_address();

			/* if currently locked-out, do not add to retries */
			$lockouts = Config::get( 'lockouts' );

			if ( ! is_array( $lockouts ) ) {
				$lockouts = array();
			}

			if ( isset( $lockouts[ $ip ] ) && time() < $lockouts[ $ip ] ) {
				return;
			}

			/* Get the arrays with retries and retries-valid information */
			$retries = Config::get( 'retries' );
			$valid   = Config::get( 'retries_valid' );
			$retries_stats = Config::get( 'retries_stats' );

			if ( ! is_array( $retries ) ) {
				$retries = array();
				Config::add( 'retries', $retries );
			}

			if ( ! is_array( $valid ) ) {
				$valid = array();
				Config::add( 'retries_valid', $valid );
			}

			if ( ! is_array( $retries_stats ) ) {
				$retries_stats = array();
				Config::add( 'retries_stats', $retries_stats );
			}

			$date_key = strtotime( date( 'Y-m-d H:00:00' ) );
            if(!empty($retries_stats[$date_key])) {

				$retries_stats[$date_key]++;
			} else {

				$retries_stats[$date_key] = 1;
            }
			Config::update( 'retries_stats', $retries_stats );

			/* Check validity and add one to retries */
			if ( isset( $retries[ $ip ] ) && isset( $valid[ $ip ] ) && time() < $valid[ $ip ]) {
				$retries[ $ip ] ++;
			} else {
				$retries[ $ip ] = 1;
			}
			$valid[ $ip ] = time() + Config::get( 'valid_duration' );

			/* lockout? */
			if ( $retries[ $ip ] % Config::get( 'allowed_retries' ) != 0 ) {
				/*
				* Not lockout (yet!)
				* Do housecleaning (which also saves retry/valid values).
				*/
				$this->cleanup( $retries, null, $valid );

				$_SESSION['login_attempts_left'] = $this->calculate_retries_remaining();

				return;
			}

			/* lockout! */
			$whitelisted = $this->is_ip_whitelisted( $ip );
			$retries_long = Config::get( 'allowed_retries' ) * Config::get( 'allowed_lockouts' );

			/*
			* Note that retries and statistics are still counted and notifications
			* done as usual for whitelisted ips , but no lockout is done.
			*/
			if ( $whitelisted ) {
				if ( $retries[ $ip ] >= $retries_long ) {
					unset( $retries[ $ip ] );
					unset( $valid[ $ip ] );
				}
			} else {
				global $limit_login_just_lockedout;
				$limit_login_just_lockedout = true;

				/* setup lockout, reset retries as needed */
				if ( (isset($retries[ $ip ]) ? $retries[ $ip ] : 0) >= $retries_long ) {
					/* long lockout */
					$lockouts[ $ip ] = time() + Config::get( 'long_duration' );
					unset( $retries[ $ip ] );
					unset( $valid[ $ip ] );
				} else {
					/* normal lockout */
					$lockouts[ $ip ] = time() + Config::get( 'lockout_duration' );
				}
			}

			/* do housecleaning and save values */
			$this->cleanup( $retries, $lockouts, $valid );

			/* do any notification */
			$this->notify( $username );

			/* increase statistics */
			$total = Config::get( 'lockouts_total' );
			if ( $total === false || ! is_numeric( $total ) ) {
				Config::add( 'lockouts_total', 1 );
			} else {
				Config::update( 'lockouts_total', $total + 1 );
			}
		}
	}

	/**
	 * Handle notification in event of lockout
	 *
	 * @param $user
	 * @return bool|void
	 */
	public function notify( $user ) {

		if( is_object( $user ) ) {
            return false;
		}

		$this->notify_log( $user );

		$args = explode( ',', Config::get( 'lockout_notify' ) );

		if ( empty( $args ) ) {
			return;
		}

		if( in_array( 'email', $args ) ) {
			$this->notify_email( $user );
		}
	}

	/**
	* Email notification of lockout to admin (if configured)
	*
	* @param $user
	*/
	public function notify_email( $user ) {
		$ip = $this->get_address();
		$retries = Config::get( 'retries' );

		if ( ! is_array( $retries ) ) {
			$retries = array();
		}

		/* check if we are at the right nr to do notification */
		if ( isset( $retries[ $ip ] )
                &&
            ( ( intval($retries[$ip]) / Config::get( 'allowed_retries' ) ) % Config::get( 'notify_email_after' ) ) != 0 ) {

			return;
		}

		/* Format message. First current lockout duration */
		if ( !isset( $retries[ $ip ] ) ) {
			/* longer lockout */
			$count    = Config::get( 'allowed_retries' )
						* Config::get( 'allowed_lockouts' );
			$lockouts = Config::get( 'allowed_lockouts' );
			$time     = round( Config::get( 'long_duration' ) / 3600 );
			$when     = sprintf( _n( '%d hour', '%d hours', $time, 'limit-login-attempts-reloaded' ), $time );
		} else {
			/* normal lockout */
			$count    = $retries[ $ip ];
			$lockouts = floor( ($count) / Config::get( 'allowed_retries' ) );
			$time     = round( Config::get( 'lockout_duration' ) / 60 );
			$when     = sprintf( _n( '%d minute', '%d minutes', $time, 'limit-login-attempts-reloaded' ), $time );
		}

		if( $custom_admin_email = Config::get( 'admin_notify_email' ) ) {

			$admin_email = $custom_admin_email;
		} else {

			$admin_email = get_site_option( 'admin_email' );
		}

		$admin_name = '';

		global $wpdb;

		$res = $wpdb->get_col( $wpdb->prepare( "
                SELECT u.display_name
                FROM $wpdb->users AS u
                LEFT JOIN $wpdb->usermeta AS m ON u.ID = m.user_id
                WHERE u.user_email = %s
                AND m.meta_key LIKE 'wp_capabilities'
                AND m.meta_value LIKE '%administrator%'",
                $admin_email
            )
        );

		if( $res ) {
		    $admin_name = $res[0];
        }

        $site_domain = str_replace( array( 'http://', 'https://' ), '', home_url() );
		$blogname = Helpers::use_local_options() ? get_option( 'blogname' ) : get_site_option( 'site_name' );
		$blogname = htmlspecialchars_decode( $blogname, ENT_QUOTES );

		$plugin_data = get_plugin_data( LLA_PLUGIN_DIR . 'limit-login-attempts-reloaded.php' );

		$subject = sprintf(
			__( "Failed login by IP %s", 'limit-login-attempts-reloaded' ),
			$ip
		);

		ob_start();
		include LLA_PLUGIN_DIR . 'views/emails/failed-login.php';
		$email_body = ob_get_clean();

		$placeholders = array(
            '{name}'                => $admin_name,
            '{domain}'              => $site_domain,
            '{attempts_count}'      => $count,
            '{lockouts_count}'      => $lockouts,
            '{ip_address}'          => $ip,
            '{username}'            => $user,
            '{blocked_duration}'    => $when,
            '{dashboard_url}'       => admin_url( 'options-general.php?page=' . $this->_options_page_slug ),
            '{premium_url}'         => 'https://www.limitloginattempts.com/info.php?from=plugin-lockout-email&v=' . $plugin_data['Version'],
            '{llar_url}'            => 'https://www.limitloginattempts.com/?from=plugin-lockout-email&v=' . $plugin_data['Version'],
            '{unsubscribe_url}'     => admin_url( 'options-general.php?page=' . $this->_options_page_slug . '&tab=settings' ),
        );

		$email_body = str_replace(
            array_keys( $placeholders ),
            array_values( $placeholders ),
            $email_body
        );

		Helpers::send_mail_with_logo( $admin_email, $subject, $email_body );
	}

	/**
	* Logging of lockout (if configured)
	*
	* @param $user_login
	*
	* @internal param $user
	*/
	public function notify_log( $user_login ) {

		if ( ! $user_login ) {
			return;
		}

		$log = $option = Config::get( 'logged' );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		$ip = $this->get_address();

		/* can be written much simpler, if you do not mind php warnings */
		if ( !isset( $log[ $ip ] ) )
			$log[ $ip ] = array();

		if ( !isset( $log[ $ip ][ $user_login ] ) )
			$log[ $ip ][ $user_login ] = array( 'counter' => 0 );

		elseif ( !is_array( $log[ $ip ][ $user_login ] ) )
			$log[ $ip ][ $user_login ] = array(
				'counter' => $log[ $ip ][ $user_login ],
			);

		$log[ $ip ][ $user_login ]['counter']++;
		$log[ $ip ][ $user_login ]['date'] = time();

		$log[ $ip ][ $user_login ]['gateway'] = Helpers::detect_gateway();

		if ( $option === false ) {
			Config::add( 'logged', $log );
		} else {
			Config::update( 'logged', $log );
		}
	}

	/**
	* Check if IP is whitelisted.
	*
	* This function allow external ip whitelisting using a filter. Note that it can
	* be called multiple times during the login process.
	*
	* Note that retries and statistics are still counted and notifications
	* done as usual for whitelisted ips , but no lockout is done.
	*
	* Example:
	* function my_ip_whitelist($allow, $ip) {
	*    return ($ip == 'my-ip') ? true : $allow;
	* }
	* add_filter('limit_login_whitelist_ip', 'my_ip_whitelist', 10, 2);
	*
	* @param null $ip
	*
	* @return bool
	*/
	public function is_ip_whitelisted( $ip = null ) {

		if ( is_null( $ip ) ) {
			$ip = $this->get_address();
		}

		$whitelisted = apply_filters( 'limit_login_whitelist_ip', false, $ip );

		return ( $whitelisted === true );
	}

	public function is_username_whitelisted( $username ) {

		if ( empty( $username ) ) {
			return false;
		}

		$whitelisted = apply_filters( 'limit_login_whitelist_usernames', false, $username );

		return ( $whitelisted === true );
	}

	public function is_ip_blacklisted( $ip = null ) {

		if ( is_null( $ip ) ) {
			$ip = $this->get_address();
		}

		$blacklisted = apply_filters( 'limit_login_blacklist_ip', false, $ip );

		return ( $blacklisted === true );
	}

	public function is_username_blacklisted( $username ) {

		if ( empty( $username ) ) {
			return false;
		}

		$whitelisted = apply_filters( 'limit_login_blacklist_usernames', false, $username );

		return ( $whitelisted === true );
	}

	/**
	 * Filter: allow login attempt? (called from wp_authenticate())
	 *
	 * @param $user WP_User
	 * @param $password
	 *
	 * @return WP_Error|WP_User
	 */
	public function wp_authenticate_user( $user, $password ) {

	    if( is_wp_error( $user ) ) {
	        return $user;
        }

	    $user_login = '';

	    if( is_a( $user, 'WP_User' ) ) {
	        $user_login = $user->user_login;
        } else if( !empty($user) && !is_wp_error($user) ) {
            $user_login = $user;
        }

		if ( $this->check_whitelist_ips( false, $this->get_address() ) ||
			$this->check_whitelist_usernames( false, $user_login ) ||
			$this->is_limit_login_ok()
		) {

			return $user;
		}

		$error = new WP_Error();

		global $limit_login_my_error_shown;
		$limit_login_my_error_shown = true;

		if ( $this->is_username_blacklisted( $user_login ) || $this->is_ip_blacklisted( $this->get_address() ) ) {
			$error->add( 'username_blacklisted', "<strong>ERROR:</strong> Too many failed login attempts." );
		} else {
			// This error should be the same as in "shake it" filter below
			$error->add( 'too_many_retries', $this->error_msg() );
		}

		return $error;
	}

	/**
	* Filter: add this failure to login page "Shake it!"
	*
	* @param $error_codes
	*
	* @return array
	*/
	public function failure_shake( $error_codes ) {
		$error_codes[] = 'too_many_retries';
		$error_codes[] = 'username_blacklisted';

		return $error_codes;
	}

	/**
	* Keep track of if user or password are empty, to filter errors correctly
	*
	* @param $user
	* @param $password
	*/
	public function track_credentials( $user, $password ) {
		global $limit_login_nonempty_credentials;

		$limit_login_nonempty_credentials = ( ! empty( $user ) && ! empty( $password ) );
	}

	/**
	* Construct informative error message
	*
	* @return string
	*/
	public function error_msg() {
		$ip       = $this->get_address();
		$lockouts = Config::get( 'lockouts' );
        $a = $this->checkKey($lockouts, $ip);
        $b = $this->checkKey($lockouts, $this->getHash($ip));

		$msg = __( '<strong>ERROR</strong>: Too many failed login attempts.', 'limit-login-attempts-reloaded' ) . ' ';

		if (
            ! is_array( $lockouts ) ||
            ( ! isset( $lockouts[ $ip ] ) && ! isset( $lockouts[$this->getHash($ip)]) ) ||
            (time() >= $a && time() >= $b)
        ){
			/* Huh? No timeout active? */
			$msg .= __( 'Please try again later.', 'limit-login-attempts-reloaded' );

			return $msg;
		}

		$when = ceil( ( ($a > $b ? $a : $b) - time() ) / 60 );
		if ( $when > 60 ) {
			$when = ceil( $when / 60 );
			$msg .= sprintf( _n( 'Please try again in %d hour.', 'Please try again in %d hours.', $when, 'limit-login-attempts-reloaded' ), $when );
		} else {
			$msg .= sprintf( _n( 'Please try again in %d minute.', 'Please try again in %d minutes.', $when, 'limit-login-attempts-reloaded' ), $when );
		}

		return $msg;
	}

	/**
	* Fix up the error message before showing it
	*
	* @param $content
	*
	* @return string
	*/
	public function fixup_error_messages( $content ) {
		global $limit_login_just_lockedout, $limit_login_nonempty_credentials, $limit_login_my_error_shown;

		$error_msg = $this->get_message();

		if ( $limit_login_nonempty_credentials ) {

			$content = '';

		    if($this->other_login_errors) {

                foreach ($this->other_login_errors as $msg) {
                    $content .= $msg . "<br />\n";
                }

            } else if( !$limit_login_just_lockedout ) {

				/* Replace error message, including ours if necessary */
				if( !empty( $_REQUEST['log'] ) && is_email( $_REQUEST['log'] ) ) {
					$content = __( '<strong>ERROR</strong>: Incorrect email address or password.', 'limit-login-attempts-reloaded' ) . "<br />\n";
				} else{
					$content = __( '<strong>ERROR</strong>: Incorrect username or password.', 'limit-login-attempts-reloaded' ) . "<br />\n";
				}
            }

			if ( $error_msg ) {

		        $content .= ( !empty( $content ) ) ? "<br />\n" : '';
				$content .= $error_msg . "<br />\n";
			}
		}

		return $content;
	}

	public function fixup_error_messages_wc( \WP_Error $error ) {
		$error->add( 1, __( 'WC Error' ) );
	}

	/**
	* Return current (error) message to show, if any
	*
	* @return string
	*/
	public function get_message() {

	    if( self::$cloud_app ) {
		    $app_errors = self::$cloud_app->get_errors();

	        return !empty( $app_errors ) ? implode( '<br>', $app_errors ) : '';
        } else {

			/* Check external whitelist */
			if ( $this->is_ip_whitelisted() ) {
				return '';
			}

			/* Is lockout in effect? */
			if ( ! $this->is_limit_login_ok() ) {
				return $this->error_msg();
			}

			return '';
        }
	}

	private function calculate_retries_remaining() {
		$remaining = 0;

		$ip      = $this->get_address();
		$retries = Config::get( 'retries' );
		$valid   = Config::get( 'retries_valid' );
		$a = $this->checkKey($retries, $ip);
		$b = $this->checkKey($retries, $this->getHash($ip));
		$c = $this->checkKey($valid, $ip);
		$d = $this->checkKey($valid, $this->getHash($ip));

		/* Should we show retries remaining? */
		if ( ! is_array( $retries ) || ! is_array( $valid ) ) {
			/* no retries at all */
			return $remaining;
		}
		if (
			(! isset( $retries[ $ip ] ) && ! isset( $retries[ $this->getHash($ip) ] )) ||
			(! isset( $valid[ $ip ] ) && ! isset( $valid[ $this->getHash($ip) ] )) ||
			( time() > $c && time() > $d )
		) {
			/* no: no valid retries */
			return $remaining;
		}
		if (
			( $a % Config::get( 'allowed_retries' ) ) == 0 &&
			( $b % Config::get( 'allowed_retries' ) ) == 0
		) {
			/* no: already been locked out for these retries */
			return $remaining;
		}

		$remaining = max( ( Config::get( 'allowed_retries' ) - ( ($a + $b) % Config::get( 'allowed_retries' ) ) ), 0 );
        return intval($remaining);
	}

	/**
	 * Get correct remote address
	 *
	 * @return string
	 *
	 */
	public function get_address() {
        return Helpers::detect_ip_address( Config::get( 'trusted_ip_origins' ) );
	}

	/**
	* Clean up old lockouts and retries, and save supplied arrays
	*
	* @param null $retries
	* @param null $lockouts
	* @param null $valid
	*/
	public function cleanup( $retries = null, $lockouts = null, $valid = null ) {
		$now      = time();
		$lockouts = ! is_null( $lockouts ) ? $lockouts : Config::get( 'lockouts' );

		$log = Config::get( 'logged' );

		/* remove old lockouts */
		if ( is_array( $lockouts ) ) {
			foreach ( $lockouts as $ip => $lockout ) {
				if ( $lockout < $now ) {
					unset( $lockouts[ $ip ] );

					if( is_array( $log ) && isset( $log[ $ip ] ) ) {
						foreach ( $log[ $ip ] as $user_login => &$data ) {

						    if( !is_array( $data ) ) $data = array();

							$data['unlocked'] = true;
						}
					}
				}
			}
			Config::update( 'lockouts', $lockouts );
		}

		Config::update( 'logged', $log );

		/* remove retries that are no longer valid */
		$valid   = ! is_null( $valid ) ? $valid : Config::get( 'retries_valid' );
		$retries = ! is_null( $retries ) ? $retries : Config::get( 'retries' );
		if ( ! is_array( $valid ) || ! is_array( $retries ) ) {
			return;
		}

		foreach ( $valid as $ip => $lockout ) {
			if ( $lockout < $now ) {
				unset( $valid[ $ip ] );
				unset( $retries[ $ip ] );
			}
		}

		/* go through retries directly, if for some reason they've gone out of sync */
		foreach ( $retries as $ip => $retry ) {
			if ( ! isset( $valid[ $ip ] ) ) {
				unset( $retries[ $ip ] );
			}
		}

		$retries_stats = Config::get( 'retries_stats' );

		if($retries_stats) {

			foreach( $retries_stats as $key => $count ) {

				if( ( is_numeric( $key ) && $key < strtotime( '-8 day' ) ) ||
                    ( !is_numeric( $key ) && strtotime( $key ) < strtotime( '-8 day' ) ) ) {
					unset($retries_stats[$key]);
				}
			}

			Config::update( 'retries_stats', $retries_stats );
        }

		Config::update( 'retries', $retries );
		Config::update( 'retries_valid', $valid );
	}

	/**
	* Render admin options page
	*/
	public function options_page() {

	    if( !empty( $_GET['tab'] ) && $_GET['tab'] === 'settings' ) {
		    Config::use_local_options( !is_network_admin() );
        }

		$this->cleanup();

		if( !empty( $_POST ) ) {

			check_admin_referer( 'limit-login-attempts-options' );

            if ( is_network_admin() )
                Config::update( 'allow_local_options', !empty( $_POST['allow_local_options'] ) );

            elseif ( Helpers::is_network_mode() )
                Config::update( 'use_local_options', empty( $_POST['use_global_options'] ) );

            /* Should we clear log? */
            if( isset( $_POST[ 'clear_log' ] ) )
            {
                Config::update( 'logged', array() );
                $this->show_message( __( 'Cleared IP log', 'limit-login-attempts-reloaded' ) );
            }

            /* Should we reset counter? */
            if( isset( $_POST[ 'reset_total' ] ) )
            {
                Config::update( 'lockouts_total', 0 );
                $this->show_message( __( 'Reset lockout count', 'limit-login-attempts-reloaded' ) );
            }

            /* Should we restore current lockouts? */
            if( isset( $_POST[ 'reset_current' ] ) )
            {
                Config::update( 'lockouts', array() );
                $this->show_message( __( 'Cleared current lockouts', 'limit-login-attempts-reloaded' ) );
            }

            /* Should we update options? */
            if( isset( $_POST[ 'llar_update_dashboard' ] ) ) {

                $white_list_ips = ( !empty( $_POST['lla_whitelist_ips'] ) ) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_whitelist_ips']) ) ) : array();

                if( !empty( $white_list_ips ) ) {
                    foreach( $white_list_ips as $key => $ip ) {
                        if( '' == $ip ) {
                            unset( $white_list_ips[ $key ] );
                        }
                    }
                }
                Config::update('whitelist', $white_list_ips );

                $white_list_usernames = ( !empty( $_POST['lla_whitelist_usernames'] ) ) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_whitelist_usernames']) ) ) : array();

                if( !empty( $white_list_usernames ) ) {
                    foreach( $white_list_usernames as $key => $ip ) {
                        if( '' == $ip ) {
                            unset( $white_list_usernames[ $key ] );
                        }
                    }
                }
                Config::update('whitelist_usernames', $white_list_usernames );

                $black_list_ips = ( !empty( $_POST['lla_blacklist_ips'] ) ) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_blacklist_ips']) ) ) : array();

                if( !empty( $black_list_ips ) ) {
                    foreach( $black_list_ips as $key => $ip ) {
                        $range = array_map('trim', explode('-', $ip) );
                        if ( count( $range ) > 1 && (float)sprintf("%u",ip2long($range[0])) > (float)sprintf("%u",ip2long($range[1]))) {
                            $this->show_message( sprintf (__( 'The %s IP range is invalid', 'limit-login-attempts-reloaded' ),$ip));
                        }
                        if( '' == $ip ) {
                            unset( $black_list_ips[ $key ] );
                        }
                    }
                }
                Config::update('blacklist', $black_list_ips );

                $black_list_usernames = ( !empty( $_POST['lla_blacklist_usernames'] ) ) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_blacklist_usernames']) ) ) : array();

                if( !empty( $black_list_usernames ) ) {
                    foreach( $black_list_usernames as $key => $ip ) {
                        if( '' == $ip ) {
                            unset( $black_list_usernames[ $key ] );
                        }
                    }
                }
                Config::update('blacklist_usernames', $black_list_usernames );

	            Config::sanitize_options();

                $this->show_message( __( 'Settings saved.', 'limit-login-attempts-reloaded' ) );
            }
            elseif( isset( $_POST[ 'llar_update_settings' ] ) ) {

                /* Should we support GDPR */
                if( isset( $_POST[ 'gdpr' ] ) ) {

                    Config::update( 'gdpr', 1 );
                } else {

                    Config::update( 'gdpr', 0 );
                }

                Config::update('show_top_level_menu_item', ( isset( $_POST['show_top_level_menu_item'] ) ? 1 : 0 ) );
                Config::update('hide_dashboard_widget', ( isset( $_POST['hide_dashboard_widget'] ) ? 1 : 0 ) );
                Config::update('show_warning_badge', ( isset( $_POST['show_warning_badge'] ) ? 1 : 0 ) );

                Config::update('allowed_retries',    (int)$_POST['allowed_retries'] );
                Config::update('lockout_duration',   (int)$_POST['lockout_duration'] * 60 );
                Config::update('valid_duration',     (int)$_POST['valid_duration'] * 3600 );
                Config::update('allowed_lockouts',   (int)$_POST['allowed_lockouts'] );
                Config::update('long_duration',      (int)$_POST['long_duration'] * 3600 );
                Config::update('notify_email_after', (int)$_POST['email_after'] );
                Config::update('gdpr_message',       sanitize_textarea_field( Helpers::deslash( $_POST['gdpr_message'] ) ) );
                Config::update('admin_notify_email', sanitize_email( $_POST['admin_notify_email'] ) );

	            Config::update('active_app', sanitize_text_field( $_POST['active_app'] ) );

                $trusted_ip_origins = ( !empty( $_POST['lla_trusted_ip_origins'] ) )
                    ? array_map( 'trim', explode( ',', sanitize_text_field( $_POST['lla_trusted_ip_origins'] ) ) )
                    : array();

                if( !in_array( 'REMOTE_ADDR', $trusted_ip_origins ) ) {

                    $trusted_ip_origins[] = 'REMOTE_ADDR';
                }

                Config::update('trusted_ip_origins', $trusted_ip_origins );

                $notify_methods = array();

                if( isset( $_POST[ 'lockout_notify_email' ] ) ) {
                    $notify_methods[] = 'email';
                }
                Config::update('lockout_notify', implode( ',', $notify_methods ) );

	            Config::sanitize_options();

                if( !empty( $_POST['llar_app_settings'] ) && self::$cloud_app ) {

                    if( ( $app_setup_code = Config::get( 'app_setup_code' ) ) && $setup_result = CloudApp::setup( strrev( $app_setup_code ) ) ) {

                        if( $setup_result['success'] && $active_app_config = $setup_result['app_config'] ) {

							foreach ( $_POST['llar_app_settings'] as $key => $value ) {

								if( array_key_exists( $key, $active_app_config['settings'] ) ) {

									if( !empty( $active_app_config['settings'][$key]['options'] ) &&
										!in_array( $value, $active_app_config['settings'][$key]['options'] ) ) {

										continue;
									}

									$active_app_config['settings'][$key]['value'] = $value;
								}
							}

							Config::update( 'app_config', $active_app_config );
                        }
                    }
                }

                $this->show_message( __( 'Settings saved.', 'limit-login-attempts-reloaded' ) );
            }
		}

		include_once( LLA_PLUGIN_DIR . 'views/options-page.php' );
	}

	/**
	* Show error message
	*
	* @param $msg
	*/
	public function show_message($msg, $is_error = false) {
		Helpers::show_message( $msg, $is_error );
	}

    /**
     * returns IP with its md5 value
     */
    private function getHash($str)
    {
        return md5($str);
    }

    /**
     * @param $arr - array
     * @param $k - key
     * @return int array value at given index or zero
     */
    private function checkKey($arr, $k)
    {
        return isset($arr[$k]) ? $arr[$k] : 0;
    }

	public function show_leave_review_notice() {

		$screen = get_current_screen();

		if(isset($_COOKIE['llar_review_notice_shown'])) {

			Config::update('review_notice_shown', true);
			@setcookie('llar_review_notice_shown', '', time() - 3600, '/');
		}

        if ( !current_user_can('manage_options') ||
            Config::get('review_notice_shown') ||
            !in_array( $screen->base, array( 'dashboard', 'plugins', 'toplevel_page_limit-login-attempts' ) ) ) return;

        $activation_timestamp = Config::get('activation_timestamp');

		if ( $activation_timestamp && $activation_timestamp < strtotime("-1 month") ) { ?>

			<div id="message" class="updated fade notice is-dismissible llar-notice-review">
                <div class="llar-review-image">
                    <img width="80px" src="<?php echo LLA_PLUGIN_URL?>assets/img/icon-256x256.png" alt="review-logo">
                </div>
				<div class="llar-review-info">
				    <p><?php _e('Hey <strong>Limit Login Attempts Reloaded</strong> user!', 'limit-login-attempts-reloaded'); ?></p>
                    <!--<p><?php _e('A <strong>crazy idea</strong> we wanted to share! What if we put an image from YOU on the <a href="https://wordpress.org/plugins/limit-login-attempts-reloaded/" target="_blank">LLAR page</a>?! (<a href="https://wordpress.org/plugins/hello-dolly/" target="_blank">example</a>) A drawing made by you or your child would cheer people up! Send us your drawing by <a href="mailto:wpchef.me@gmail.com" target="_blank">email</a> and we like it, we\'ll add it in the next release. Let\'s have some fun!', 'limit-login-attempts-reloaded'); ?></p> Also, -->
                    <p><?php _e('We would really like to hear your feedback about the plugin! Please take a couple minutes to write a few words <a href="https://wordpress.org/support/plugin/limit-login-attempts-reloaded/reviews/#new-post" target="_blank">here</a>. Thank you!', 'limit-login-attempts-reloaded'); ?></p>

                    <ul class="llar-buttons">
						<li><a href="#" class="llar-review-dismiss" data-type="dismiss"><?php _e('Don\'t show again', 'limit-login-attempts-reloaded'); ?></a></li>
                        <li><i class=""></i><a href="#" class="llar-review-dismiss button" data-type="later"><?php _e('Maybe later', 'limit-login-attempts-reloaded'); ?></a></li>
						<li><a class="button button-primary" target="_blank" href="https://wordpress.org/support/plugin/limit-login-attempts-reloaded/reviews/#new-post"><?php _e('Leave a review', 'limit-login-attempts-reloaded'); ?></a></li>
                    </ul>
                </div>
			</div>
            <script type="text/javascript">
                (function($){

                    $(document).ready(function(){
                        $('.llar-review-dismiss').on('click', function(e) {
                            e.preventDefault();

                            var type = $(this).data('type');

                            $.post(ajaxurl, {
                                action: 'dismiss_review_notice',
                                type: type,
                                sec: '<?php echo wp_create_nonce( "llar-action" ); ?>'
                            });

                            $(this).closest('.llar-notice-review').remove();
                        });

                        $(".llar-notice-review").on("click", ".notice-dismiss", function (event) {
                            createCookie('llar_review_notice_shown', '1', 30);
                        });

                        function createCookie(name, value, days) {
                            var expires;

                            if (days) {
                                var date = new Date();
                                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                                expires = "; expires=" + date.toGMTString();
                            } else {
                                expires = "";
                            }
                            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
                        }
                    });

                })(jQuery);
            </script>
			<?php
		}
	}

	public function show_enable_notify_notice() {

		$screen = get_current_screen();

		if(isset($_COOKIE['llar_enable_notify_notice_shown'])) {

			Config::update('enable_notify_notice_shown', true);
			@setcookie('llar_enable_notify_notice_shown', '', time() - 3600, '/');
		}

		$active_app = Config::get( 'active_app' );
		$notify_methods = explode( ',', Config::get( 'lockout_notify' ) );

        if ( $active_app !== 'local' ||
             in_array( 'email', $notify_methods ) ||
             !current_user_can('manage_options') ||
             Config::get('enable_notify_notice_shown') ||
             $screen->parent_base === 'edit' ) return;

        $activation_timestamp = Config::get('notice_enable_notify_timestamp');

		if ( $activation_timestamp && $activation_timestamp < strtotime("-1 month") ) {

			$review_activation_timestamp = Config::get('activation_timestamp');
			if ( $review_activation_timestamp && $review_activation_timestamp < strtotime("-1 month") ) {
				Config::update( 'activation_timestamp', time() );
            }

		    ?>

			<div id="message" class="updated fade notice is-dismissible llar-notice-notify">
                <div class="llar-review-image">
                    <span class="dashicons dashicons-warning"></span>
                </div>
				<div class="llar-review-info">
				    <p><?php _e('You have been upgraded to the latest version of <strong>Limit Login Attempts Reloaded</strong>.<br> ' .
                            'Due to increased security threats around the holidays, we recommend turning on email ' .
                            'notifications when you receive a failed login attempt.', 'limit-login-attempts-reloaded'); ?></p>

                    <ul class="llar-buttons">
                        <li><a class="button button-primary llar-ajax-enable-notify" target="_blank" href="#"><?php _e('Yes, turn on email notifications', 'limit-login-attempts-reloaded'); ?></a></li>
                        <li><a href="#" class="llar-notify-notice-dismiss button" data-type="later"><?php _e('Remind me a month from now', 'limit-login-attempts-reloaded'); ?></a></li>
                        <li><a href="#" class="llar-notify-notice-dismiss" data-type="dismiss"><?php _e('Don\'t show this message again', 'limit-login-attempts-reloaded'); ?></a></li>
                    </ul>
                </div>
			</div>
            <script type="text/javascript">
                (function($){

                    $(document).ready(function(){
                        $('.llar-notify-notice-dismiss').on('click', function(e) {
                            e.preventDefault();

                            var type = $(this).data('type');

                            $.post(ajaxurl, {
                                action: 'dismiss_notify_notice',
                                type: type,
                                sec: '<?php echo wp_create_nonce( "llar-action" ); ?>'
                            });

                            $(this).closest('.llar-notice-notify').remove();
                        });

                        $(".llar-notice-notify").on("click", ".notice-dismiss", function (e) {
                            createCookie('llar_enable_notify_notice_shown', '1', 30);
                        });

                        $(".llar-ajax-enable-notify").on("click", function (e) {
							e.preventDefault();

							$.post(ajaxurl, {
								action: 'enable_notify',
								sec: '<?php echo wp_create_nonce( "llar-action" ); ?>'
							}, function(response){

								if(response.success) {
									$(".llar-notice-notify .llar-review-info p").text('You are all set!');
									$(".llar-notice-notify .llar-buttons").remove();
                                }

                            });
                        });

                        function createCookie(name, value, days) {
                            var expires;

                            if (days) {
                                var date = new Date();
                                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                                expires = "; expires=" + date.toGMTString();
                            } else {
                                expires = "";
                            }
                            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
                        }
                    });

                })(jQuery);
            </script>
			<?php
		}
	}
}