<?php

/**
 * Login Auth class
 *
 * @since 1.0
 */

namespace dologin;

defined('WPINC') || exit;

class Auth extends Instance
{
	const TYPE_CLEAR_LOG = 'clear_log';

	private $_tb;
	private $__data;

	protected function __construct()
	{
		$this->__data = $this->cls('Data');
		$this->_tb = $this->__data->tb('failure');
	}

	/**
	 * Init
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init()
	{
		add_action('login_head', array($this, 'login_head'));
		add_filter('authenticate', array($this, 'authenticate'), 2, 3);
		// Save phone number for new reg
		add_filter('register_new_user', array($this, 'register_new_user'));

		// Recaptcha validation
		add_filter('registration_errors', array($this, 'registration_errors'));
		add_filter('lostpassword_errors', array($this, 'lostpassword_errors'));

		if (Conf::val('2fa')) {
			add_filter('authenticate', array($this->cls('TwoFA'), 'authenticate'), 30, 3); // Need to be after WP auth check
		}
		// elseif ( Conf::val( 'sms' ) ) {
		// 	add_filter( 'authenticate', array( $this->cls( 'SMS' ), 'authenticate' ), 30, 3 ); // Need to be after WP auth check
		// }

		add_action('wp_login_failed', array($this, 'wp_login_failed'));

		// XMLRPC
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			add_action('init', array($this, 'check_xmlrpc'));
		}

		// Add notices for XMLRPC request
		add_filter('xmlrpc_login_error', array($this, 'xmlrpc_error_msg'));
	}

	/**
	 * Check recaptcha for register
	 *
	 * @since 1.9
	 * @access public
	 */
	public function registration_errors($errors)
	{
		if (Conf::val('gg') && Conf::val('recapt_register')) {
			try {
				$this->cls('Captcha')->authenticate(); // Need to be before WP auth check
			} catch (\Exception $ex) {
				$err_code = $ex->getMessage();
				defined('debug') && debug('❌ reCAPTCHA error: ' . $err_code);

				$errors->add('captcha_err', Lang::msg($err_code));
			}
		}

		return $errors;
	}

	/**
	 * Check recaptcha for lost password request
	 *
	 * @since 1.9
	 * @access public
	 */
	public function lostpassword_errors($errors)
	{
		if (Conf::val('gg') && Conf::val('recapt_forget')) {
			try {
				$this->cls('Captcha')->authenticate(); // Need to be before WP auth check
			} catch (\Exception $ex) {
				$err_code = $ex->getMessage();
				defined('debug') && debug('❌ reCAPTCHA error: ' . $err_code);

				$errors->add('captcha_err', Lang::msg($err_code));
			}
		}

		return $errors;
	}

	/**
	 * Save phone number
	 *
	 * @since 1.8
	 * @access public
	 */
	public function register_new_user($uid)
	{
		if (empty($_POST['phone_number'])) {
			return;
		}

		$num = preg_replace('/\D/', '', $_POST['phone_number']);

		if (!$num) {
			return;
		}

		// Save phone
		update_user_meta($uid, 'phone_number', $num);
	}

	/**
	 * Login page display messages
	 *
	 * @since  1.0
	 * @access public
	 */
	public function login_head()
	{
		global $error;

		if (defined('DOLOGIN_ERR')) {
			return;
		}

		// check whitelist
		if (!$this->try_whitelist()) {
			$error .= Lang::msg('not_in_whitelist');
			return;
		}

		// check blacklist
		if ($this->try_blacklist()) {
			$error .= Lang::msg('in_blacklist');
			return;
		}

		// Check if has login error
		if ($err_msg = $this->_has_login_err(true)) {
			$error .= $err_msg;
			return;
		}
	}

	/**
	 * Check if has login error limit
	 *
	 * @since  1.0
	 * @access private
	 */
	private function _has_login_err($msg_only = false, $duration_rate = false, $retry_rate = false)
	{
		global $wpdb;

		$ip = IP::me();
		if (Conf::val('gdpr')) {
			$ip = md5($ip);
		}

		$duration = intval(Conf::val('duration')) * 60;
		if ($duration_rate) {
			$duration *= $duration_rate;
		}

		$q = "SELECT COUNT(*) FROM `$this->_tb` WHERE ip = %s AND dateline > %s";
		$err_count = $wpdb->get_var($wpdb->prepare($q, array($ip, time() - $duration)));

		if (!$err_count) {
			return false;
		}

		$max_retries = Conf::val('max_retries');
		if ($retry_rate) {
			$max_retries *= $retry_rate;
		}

		// Block visit
		if ($err_count < $max_retries) {
			if ($msg_only) {
				return Lang::msg('max_retries', $max_retries - $err_count);
			}
			return false;
		}

		// Can try but has failure
		return Lang::msg('max_retries_hit');
	}

	/**
	 * Authenticate
	 *
	 * @since  1.0
	 * @access public
	 */
	public function authenticate($user, $username, $password)
	{
		if (empty($username) || empty($password)) {
			defined('debug') && debug('lack_of_u/p');
			return $user;
		}

		if (is_wp_error($user)) {
			defined('debug') && debug('error already');
			return $user;
		}

		$in_whitelist = $this->try_whitelist();
		// if ( $in_whitelist === 'hit' ) {
		// 	return $user;
		// }

		$error = new \WP_Error();

		if (!$in_whitelist) {
			if (Util::is_login_page()) { // woo login won't check this
				defined('debug') && debug('❌ not_in_whitelist');
				$error->add('not_in_whitelist', Lang::msg('not_in_whitelist'));
				define('DOLOGIN_ERR', true);
			}
		}

		if ($this->try_blacklist()) {
			defined('debug') && debug('❌ in_blacklist');
			$error->add('in_blacklist', Lang::msg('in_blacklist'));
			define('DOLOGIN_ERR', true);
		}

		if (!defined('DOLOGIN_ERR')) {
			if ($err_msg = $this->_has_login_err()) {
				defined('debug') && debug('❌ _has_login_err');
				$error->add('in_blacklist', $err_msg);
				define('DOLOGIN_ERR', true);
			}
		}

		// Google reCAPTCHA validate
		if (!defined('DOLOGIN_ERR') && Conf::val('gg')) { #  && SMS::is_dry_run()
			try {
				$this->cls('Captcha')->authenticate(); // Need to be before WP auth check
			} catch (\Exception $ex) {
				$err_code = $ex->getMessage();
				defined('debug') && debug('❌ reCAPTCHA error: ' . $err_code);

				$error->add('captcha_err', Lang::msg($err_code));
				define('DOLOGIN_ERR', true);
			}
		}

		if (defined('DOLOGIN_ERR')) {
			// bypass verifying user info
			remove_filter('authenticate', 'wp_authenticate_username_password', 20);
			remove_filter('authenticate', 'wp_authenticate_email_password', 20);
			return $error;
		}

		defined('debug') && debug('✅ passed');

		return $user;
	}

	/**
	 * Block XMLRPC if bad
	 *
	 * @since  1.2
	 * @access public
	 */
	public function check_xmlrpc()
	{
		if (is_user_logged_in()) {
			return;
		}

		if (!$this->try_whitelist() || $this->try_blacklist() || $this->_has_login_err()) {
			header('HTTP/1.0 403 Forbidden');
			exit;
		}
	}

	/**
	 * Valiadte XMLRPC
	 *
	 * @since  1.2
	 * @access public
	 */
	public function xmlrpc_error_msg($err)
	{
		if (!class_exists('IXR_Error')) {
			return $err;
		}

		if (!$this->try_whitelist()) {
			return new \IXR_Error(403, Lang::msg('not_in_whitelist'));
		}

		if ($this->try_blacklist()) {
			return new \IXR_Error(403, Lang::msg('in_blacklist'));
		}

		if ($err_msg = $this->_has_login_err()) {
			return new \IXR_Error(403, $err_msg);
		}

		return $err;
	}

	/**
	 * Log login failure
	 *
	 * @since  1.0
	 * @access public
	 */
	public function wp_login_failed($user)
	{
		global $wpdb;

		$ip = IP::me();

		// Parse Geo info
		$ip_geo_list = IP::geo($ip);
		unset($ip_geo_list['ip']);
		$ip_geo = array();
		foreach ($ip_geo_list as $k => $v) {
			$ip_geo[] = $k . ':' . $v;
		}
		$ip_geo = implode(', ', $ip_geo);

		// GDPR compliance
		if (Conf::val('gdpr')) {
			$ip = md5($ip);
		}

		// Parse gateway
		$gateway = 'WP Login';
		if (isset($_POST['woocommerce-login-nonce'])) {
			$gateway = 'WooCommerce';
		} elseif (isset($GLOBALS['wp_xmlrpc_server']) && is_object($GLOBALS['wp_xmlrpc_server'])) {
			$gateway = 'XMLRPC';
		}

		// If the are more than limited, bypass log
		$err_msg = $this->_has_login_err(false, 10);
		if (!$err_msg) {
			$q = "INSERT INTO `$this->_tb` SET ip = %s, ip_geo = %s, username = %s, gateway = %s, dateline = %s";
			$wpdb->query($wpdb->prepare($q, array($ip, $ip_geo, $user, $gateway, time())));
		}
	}

	/**
	 * Display log
	 *
	 * @since  2.7
	 * @access public
	 */
	public function history_list($limit, $offset = false)
	{
		global $wpdb;

		if ($offset === false) {
			$total = $this->count_list();
			$offset = Util::pagination($total, $limit, true);
		}

		$q = "SELECT * FROM `$this->_tb` ORDER BY id DESC LIMIT %d, %d";
		return $wpdb->get_results($wpdb->prepare($q, $offset, $limit));
	}

	/**
	 * Count the log list
	 */
	public function count_list()
	{
		global $wpdb;

		if (!$this->__data->tb_exist('failure')) {
			return false;
		}

		$q = "SELECT COUNT(*) FROM `$this->_tb`";
		return $wpdb->get_var($q);
	}

	/**
	 * Delete old log
	 */
	public function _clear_log()
	{
		global $wpdb;

		$q = "DELETE FROM `$this->_tb` WHERE dateline < %d";
		$count = $wpdb->query($wpdb->prepare($q, time() - 86400 * 30));

		GUI::succeed(sprintf(__('Cleared %d record(s) successfully!', 'dologin'), $count));
	}

	/**
	 * Validate if hit whitelist
	 *
	 * @since  1.0
	 * @access public
	 */
	private function try_whitelist()
	{
		$list = Conf::val('whitelist');
		if (!$list) {
			return true;
		}

		if ($this->cls('IP')->maybe_hit_rule($list)) {
			return 'hit';
		}

		return false;
	}

	/**
	 * Validate if hit blacklist
	 *
	 * @since  1.0
	 * @access public
	 */
	private function try_blacklist()
	{
		$list = Conf::val('blacklist');
		if (!$list) {
			return false;
		}

		if ($this->cls('IP')->maybe_hit_rule($list)) {
			return 'hit';
		}

		return false;
	}

	/**
	 * Handler
	 *
	 * @since  2.7
	 */
	public function handler()
	{
		$type = Router::verify_type();

		switch ($type) {
			case self::TYPE_CLEAR_LOG:
				$this->_clear_log();
				break;

			default:
				break;
		}
	}
}
