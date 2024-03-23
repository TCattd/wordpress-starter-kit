<?php
/**
 * Admin class
 *
 * @since 1.0
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Admin extends Instance {
	/**
	 * Init admin
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links_dologin/dologin.php', array( $this, 'add_plugin_links' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'admin_enqueue_scripts', array( $this->cls( 'GUI' ), 'enqueue_admin' ) );

		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widget' ) );
	}

	/**
	 * Register a dashboard widget
	 */
	public function dashboard_widget() {
		wp_add_dashboard_widget( 'dologin', __( 'DoLogin Security Overview', 'dologin' ), array( $this, 'widget_overview' ) );
	}

	/**
	 * Overview widget
	 */
	public function widget_overview() {
		require_once DOLOGIN_DIR . 'tpl/widget.tpl.php';
	}

	/**
	 * Admin setting page
	 *
	 * @since  1.0
	 * @access public
	 */
	public function admin_menu() {
		add_options_page( 'DoLogin Security', 'DoLogin Security', apply_filters( 'dologin_admin_menu_access', 'manage_options' ), 'dologin', array( $this, 'setting_page' ) );

		$this->cls('TwoFA')->maybe_save_2fa();
	}

	/**
	 * admin_init
	 *
	 * @since  1.2.2
	 * @access public
	 */
	public function admin_init() {
		if ( get_transient( 'dologin_activation_redirect' ) ) {
			delete_transient( 'dologin_activation_redirect' );
			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( menu_page_url( 'dologin', 0 ) );
			}
		}

		// Register user phone column
		add_filter( 'user_contactmethods', array( $this, 'user_contactmethods' ), 10, 1 );
		add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );

		add_action( 'admin_notices', array( $this->cls( 'GUI' ), 'display_msg' ) );
	}

	/**
	 * Add phone number col in user profile
	 *
	 * @since  1.3
	 */
	public function user_contactmethods( $contactmethods ) {
		if ( ! array_key_exists( 'phone_number', $contactmethods ) ) {
			$contactmethods[ 'phone_number' ] = __( 'Dologin Security Phone', 'dologin' );
		}
		if ( ! array_key_exists( '2fa', $contactmethods ) ) {
			$contactmethods[ '2fa' ] = __( 'Dologin 2FA Secret', 'dologin' );
		}
		return $contactmethods;
	}

	public function manage_users_columns( $column ) {
		if ( ! array_key_exists( 'phone_number', $column ) ) {
			$column[ 'phone_number' ] = __( 'Dologin Security Phone', 'dologin' );
		}
		if ( ! array_key_exists( '2fa', $column ) ) {
			$column[ '2fa' ] = __( 'Dologin 2FA', 'dologin' );
		}
		return $column;
	}

	public function manage_users_custom_column( $val, $column_name, $user_id ) {
		if ( $column_name == 'phone_number' ) {
			$val = substr( get_the_author_meta( 'phone_number', $user_id ), -4 );
			if ( $val ) {
				$val = '***' . $val . '<br>';
			}

			// Append gen link
			$val .= '<a href="' . Util::build_url( Router::ACTION_PSWD, Pswdless::TYPE_GEN, false, null, array( 'uid' => $user_id ) ) . '" class="button button-primary">' . __( 'Generate Login Link', 'dologin' ) . '</a>';

			return $val;
		}

		if ( $column_name == '2fa' ) {
			$val = get_the_author_meta( '2fa', $user_id );
			$val = $val ? 'Enabled' : '-';
		}

		return $val;
	}

	/**
	 * Plugin link
	 *
	 * @since  1.1
	 * @access public
	 */
	public function add_plugin_links( $links ) {
		$links[] = '<a href="' . menu_page_url( 'dologin', 0 ) . '">' . __( 'Settings', 'dologin' ) . '</a>';

		return $links;
	}

	/**
	 * Display and save options
	 *
	 * @since  1.0
	 * @access public
	 */
	public function setting_page() {
		$this->cls( 'Data' )->tb_create( 'failure' );
		$this->cls( 'Data' )->tb_create( 'sms' );
		$this->cls( 'Data' )->tb_create( 'pswdless' );

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'dologin' );

			$raw_data = self::cleanup_text( $_POST );

			// Save options
			$list = array() ;

			foreach ( $this->cls( 'Conf' )->get_options() as $id => $v ) {
				if ( $id == '_ver' ) {
					continue;
				}

				$list[ $id ] = ! empty( $raw_data[ $id ] ) ? $raw_data[ $id ] : false ;
			}

			// Special handler for list
			$list[ 'whitelist' ] = $this->_sanitize_list( $raw_data[ 'whitelist' ] );
			$list[ 'blacklist' ] = $this->_sanitize_list( $raw_data[ 'blacklist' ] );

			foreach ( $list as $id => $v ) {
				Conf::update( $id, $v );
			}

			GUI::succeed( __( 'Options saved successfully!', 'dologin' ), true );

			wp_redirect( $_SERVER[ 'HTTP_REFERER' ] );
			exit;
		}

		require_once DOLOGIN_DIR . 'tpl/entry.tpl.php';
	}

	/**
	 * Clean up the input string of any extra slashes/spaces.
	 *
	 * @access public
	 */
	public static function cleanup_text( $input )
	{
		if ( is_array( $input ) ) {
			return array_map( __CLASS__ . '::cleanup_text', $input );
		}

		return stripslashes( trim( $input ) );
	}

	/**
	 * Sanitize list
	 *
	 * @since  1.0
	 * @access public
	 */
	private function _sanitize_list( $list ) {
		if ( ! is_array( $list ) ) {
			$list = explode( "\n", trim( $list ) );
		}

		foreach ( $list as $k => $v ) {
			$list[ $k ] = implode( ', ', array_map( 'trim', explode( ',', $v ) ) );
		}

		return array_filter( $list );
	}

	/**
	 * Display pswdless
	 *
	 * @since  1.4
	 * @access public
	 */
	public function pswdless_log() {
		global $wpdb;

		$list = $wpdb->get_results( 'SELECT * FROM ' . $this->cls( 'Data' )->tb( 'pswdless' ) . ' ORDER BY id DESC' );
		foreach ( $list as $k => $v ) {
			$user_info = get_userdata( $v->user_id );
			$list[ $k ]->username = $user_info->user_login;
			$list[ $k ]->link = admin_url( '?dologin=' . $v->id . '.' . $v->hash );
		}

		return $list;
	}

	/**
	 * Display sms log
	 *
	 * @since  1.3
	 * @access public
	 */
	public function sms_log() {
		global $wpdb;
		return $wpdb->get_results( 'SELECT * FROM ' . $this->cls( 'Data' )->tb( 'sms' ) . ' ORDER BY id DESC LIMIT 10' );
	}
}