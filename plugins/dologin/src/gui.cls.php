<?php
/**
 * GUI class
 *
 * @since 1.0
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class GUI extends Instance {
	const DB_MSG = 'dologin.msg';
	const NOTICE_BLUE = 'notice notice-info';
	const NOTICE_GREEN = 'notice notice-success';
	const NOTICE_RED = 'notice notice-error';
	const NOTICE_YELLOW = 'notice notice-warning';

	/**
	 * Init
	 *
	 * @since  1.3
	 * @access public
	 */
	public function init() {
		add_action( 'login_message', array( $this, 'login_message' ) );

		add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );

		// Register injection for phone number
		add_action( 'register_form', array( $this, 'register_form' ) );

		add_action( 'lostpassword_form', array( $this, 'lostpassword_form' ) );

		// Append js and set ajax url
		add_action( 'login_form', array( $this, 'login_form' ) );

		add_action( 'woocommerce_login_form', array( $this, 'login_enqueue_scripts' ) );
		add_action( 'woocommerce_login_form', array( $this, 'login_form' ) );
	}

	/**
	 * Enqueue js
	 *
	 * @since  1.3
	 * @access public
	 */
	public function login_enqueue_scripts() {
		if ( ! Util::is_login_page() ) {
			// return;
		}

		$this->enqueue_style();

		// JS is only for sms/2fa code
		if ( ! Conf::val( '2fa' ) && ! Conf::val( 'sms' ) ) {
			return;
		}

		wp_register_script( 'dologin', DOLOGIN_PLUGIN_URL . 'assets/login.js', array( 'jquery' ), Core::VER, false );

		$localize_data = array();
		$localize_data[ 'login_url' ] = get_rest_url( null, 'dologin/v1/' . ( Conf::val('2fa') ? '2fa' : 'sms' ) );
		wp_localize_script( 'dologin', 'dologin', $localize_data );

		wp_enqueue_script( 'dologin' );
	}

	/**
	 * Load style
	 *
	 * @since 1.3
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'dologin', DOLOGIN_PLUGIN_URL . 'assets/login.css', array(), Core::VER, 'all');
	}

	/**
	 * Load css/js for admin
	 *
	 * @since 2.0
	 */
	public function enqueue_admin() {
		// Only enqueue on dologin pages
		if( empty( $_GET[ 'page' ] ) || strpos( $_GET[ 'page' ], 'dologin' ) !== 0 ) {
			return;
		}

		$this->enqueue_style();

		wp_register_script( 'dologin_admin', DOLOGIN_PLUGIN_URL . 'assets/admin.js', array( 'jquery' ), Core::VER, false );

		$localize_data = array();
		$localize_data[ 'url_test_sms' ] = get_rest_url( null, 'dologin/v1/test_sms' );
		$localize_data[ 'url_myip' ] = get_rest_url( null, 'dologin/v1/myip' );
		$localize_data[ 'current_user_phone' ] = $this->cls( 'SMS' )->current_user_phone();
		wp_localize_script( 'dologin_admin', 'dologin_admin', $localize_data );

		wp_enqueue_script( 'dologin_admin' );

	}

	/**
	 * Display login form
	 *
	 * @since  1.3
	 * @access public
	 */
	public function login_form() {
		if ( Conf::val( 'sms' ) ) {
			echo '	<p id="dologin-process">
						Dologin Security:
						<span id="dologin-process-msg"></span>
					</p>
					<p id="dologin-dynamic_code">
						<label for="dologin-two_factor_code">' . __( 'Dynamic Code', 'dologin' ) . '</label>
						<br /><input type="text" name="dologin-two_factor_code" id="dologin-two_factor_code" autocomplete="off" />
					</p>
				';
		}

		if ( Conf::val( 'gg' ) ) {
			$this->cls( 'Captcha' )->show();
		}
	}

	/**
	 * Inject register form
	 *
	 * @since  1.9
	 * @access public
	 */
	public function register_form() {
		if ( Conf::val( 'sms_force' ) ) {
			echo '	<p>
						<label for="phone_number">' . __( 'Dologin Security Phone', 'dologin' ) . '</label>
						<input type="text" name="phone_number" id="phone_number" class="input" size="25" required />
					</p>
			';
		}

		if ( Conf::val( 'gg' ) && Conf::val( 'recapt_register' ) ) {
			$this->cls( 'Captcha' )->show();
		}
	}

	/**
	 * Inject lost password form
	 *
	 * @since  1.9
	 * @access public
	 */
	public function lostpassword_form() {
		if ( Conf::val( 'gg' ) && Conf::val( 'recapt_forget' ) ) {
			$this->cls( 'Captcha' )->show();
		}
	}

	/**
	 * Login default display messages
	 *
	 * @since  1.1
	 * @access public
	 */
	public function login_message( $msg ) {
		if ( defined( 'DOLOGIN_ERR' ) ) {
			return;
		}

		$msg .= '<div class="success">' . Lang::msg( 'under_protected' ) . '<img src="' . DOLOGIN_PLUGIN_URL . 'assets/shield.svg" class="dologin-shield"></div>';

		return $msg;
	}

	/**
	 * Register this setting to save
	 *
	 * @since  2.0
	 * @access public
	 */
	public function enroll( $id ) {
		echo '<input type="hidden" name="_settings-enroll[]" value="' . $id . '" />';
	}

	/**
	 * Build a textarea
	 *
	 * @since 2.0
	 * @access public
	 */
	public function build_textarea( $id, $cols = false, $val = null ) {
		if ( $val === null ) {
			$val = Conf::val( $id );

			if ( is_array( $val ) ) {
				$val = implode( "\n", $val );
			}
		}

		if ( ! $cols ) {
			$cols = 80;
		}

		$this->enroll( $id );

		echo "<textarea name='$id' rows='9' cols='$cols'>" . esc_textarea( $val ) . "</textarea>";
	}

	/**
	 * Build a text input field
	 *
	 * @since 2.0
	 * @access public
	 */
	public function build_input( $id, $cls = null, $val = null, $type = 'text' ) {
		if ( $val === null ) {
			$val = Conf::val( $id );
		}

		$label_id = preg_replace( '|\W|', '', $id );

		if ( $type == 'text' ) {
			$cls = "regular-text $cls";
		}

		$this->enroll( $id );

		echo "<input type='$type' class='$cls' name='$id' value='" . esc_textarea( $val ) ."' id='input_$label_id' /> ";
	}

	/**
	 * Build a switch div html snippet
	 *
	 * @since 1.2
	 * @access public
	 */
	public function build_switch( $id, $title_list = false ) {
		$this->enroll( $id );

		echo '<div class="dologin-switch">';

		if ( ! $title_list ) {
			$title_list = array(
				__( 'OFF', 'dologin' ),
				__( 'ON', 'dologin' ),
			);
		}

		foreach ( $title_list as $k => $v ) {
			$this->_build_radio( $id, $k, $v );
		}

		echo '</div>';
	}

	/**
	 * Build a radio input html codes and output
	 *
	 * @since 1.2
	 * @access private
	 */
	private function _build_radio( $id, $val, $txt ) {
		$id_attr = 'input_radio_' . preg_replace( '|\W|', '', $id ) . '_' . $val;

		if ( ! is_string( Conf::$_default_options[ $id ] ) ) {
			$checked = (int) Conf::val( $id, true ) === (int) $val ? ' checked ' : '';
		}
		else {
			$checked = Conf::val( $id, true ) === $val ? ' checked ' : '';
		}

		echo "<input type='radio' autocomplete='off' name='$id' id='$id_attr' value='$val' $checked /> <label for='$id_attr'>$txt</label>";
	}

	/**
	 * Builds a single msg.
	 *
	 * @access private
	 */
	private static function _build_msg( $color, $str ) {
		return '<div class="' . $color . ' is-dismissible"><p>'. $str . '</p></div>';
	}

	/**
	 * Display info notice
	 *
	 * @access public
	 */
	public static function info( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_BLUE, $msg, $echo );
	}

	/**
	 * Display note notice
	 *
	 * @access public
	 */
	public static function note( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_YELLOW, $msg, $echo );
	}

	/**
	 * Display success notice
	 *
	 * @access public
	 */
	public static function succeed( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_GREEN, $msg, $echo );
	}

	/**
	 * Display error notice
	 *
	 * @access public
	 */
	public static function error( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_RED, $msg, $echo );
	}

	/**
	 * Adds a notice to display on the admin page
	 *
	 * @access private
	 */
	private static function _add_notice( $color, $msg, $echo = false ) {
		// Bypass adding for CLI or cron
		if ( defined( 'DOING_CRON' ) ) {
			// WP CLI will show the info directly
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				$msg = strip_tags( $msg );
				if ( $color == self::NOTICE_RED ) {
					\WP_CLI::error( $msg );
				}
				else {
					\WP_CLI::success( $msg );
				}
			}
			return;
		}

		if ( $echo ) {
			echo self::_build_msg( $color, $msg );
			return;
		}

		$messages = get_option( self::DB_MSG );

		if ( is_array( $msg ) ) {
			foreach ( $msg as $str ) {
				$messages[] = self::_build_msg( $color, $str );
			}
		}
		else {
			$messages[] = self::_build_msg( $color, $msg );
		}
		update_option( self::DB_MSG, $messages );
	}

	/**
	 * Display admin msg
	 *
	 * @access public
	 */
	public function display_msg() {
		$this->cls('SMS')->gui_notice();
		$this->cls('TwoFA')->gui_notice();

		// One time msg
		$messages = get_option( self::DB_MSG );
		if( is_array( $messages ) ) {
			$messages = array_unique( $messages );

			$added_thickbox = false;
			foreach ($messages as $msg) {
				// Added for popup links
				if ( strpos( $msg, 'TB_iframe' ) && ! $added_thickbox ) {
					add_thickbox();
					$added_thickbox = true;
				}
				echo $msg;
			}
		}
		delete_option( self::DB_MSG );

	}

}