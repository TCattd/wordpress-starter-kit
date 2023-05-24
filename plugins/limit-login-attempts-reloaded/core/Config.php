<?php

namespace LLAR\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Config {

	private static $default_options = array(
		'gdpr'               => 0,
		'gdpr_message'       => '',

		/* Are we behind a proxy? */
		'client_type'        => LLA_DIRECT_ADDR,

		/* Lock out after this many tries */
		'allowed_retries'    => 4,

		/* Lock out for this many seconds */
		'lockout_duration'   => 1200, // 20 minutes

		/* Long lock out after this many lockouts */
		'allowed_lockouts'   => 4,

		/* Long lock out for this many seconds */
		'long_duration'      => 86400, // 24 hours,

		/* Reset failed attempts after this many seconds */
		'valid_duration'     => 86400, // 12 hours

		/* Also limit malformed/forged cookies? */
		'cookies'            => true,

		/* Notify on lockout. Values: '', 'log', 'email', 'log,email' */
		'lockout_notify'     => 'email',

		/* If notify by email, do so after this number of lockouts */
		'notify_email_after' => 3,

		'review_notice_shown'        => false,
		'enable_notify_notice_shown' => false,

		'whitelist'           => array(),
		'whitelist_usernames' => array(),
		'blacklist'           => array(),
		'blacklist_usernames' => array(),

		'active_app'               => 'local',
		'app_config'               => '',
		'show_top_level_menu_item' => true,
		'hide_dashboard_widget'    => false,
		'show_warning_badge'       => true,
		'onboarding_popup_shown'   => false,

		'logged'                => array(),
		'retries_valid'         => array(),
		'retries'               => array(),
		'lockouts'              => array(),
		'auto_update_choice'    => null,
	);

	private static $prefix = 'limit_login_';

	private static $use_local_options = true;

	public static function get_default_options() {
		return self::$default_options || array();
	}

	public static function use_local_options( $value ) {
		self::$use_local_options = $value;
	}

	public static function init() {
		self::init_defaults();
		self::$use_local_options = Helpers::use_local_options();
	}

	public static function init_defaults() {
		self::$default_options['gdpr_message'] = __( 'By proceeding you understand and give your consent that your IP address and browser information might be processed by the security plugins installed on this site.', 'limit-login-attempts-reloaded' );
	}

	/**
	 * @param $name
	 *
	 * @return false|string
	 */
	private static function format_option_name( $name ) {
		if ( ! $name ) {
			return false;
		}

		return self::$prefix . $name;
	}

	/**
	 * Get option by name
	 *
	 * @param $option_name
	 *
	 * @return null
	 */
	public static function get( $option_name ) {
		$func  = self::$use_local_options ? 'get_option' : 'get_site_option';
		$value = $func( self::format_option_name( $option_name ), null );

		if ( is_null( $value ) && isset( self::$default_options[ $option_name ] ) ) {
			$value = self::$default_options[ $option_name ];
		}

		return $value;
	}

	/**
	 * @param $option_name
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function update( $option_name, $value ) {
		$func = self::$use_local_options ? 'update_option' : 'update_site_option';

		return $func( self::format_option_name( $option_name ), $value );
	}

	/**
	 * @param $option_name
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function add( $option_name, $value ) {
		$func = self::$use_local_options ? 'add_option' : 'add_site_option';

		return $func( self::format_option_name( $option_name ), $value, '', 'no' );
	}

	/**
	 * @param $option_name
	 *
	 * @return mixed
	 */
	public static function delete( $option_name ) {
		$func = self::$use_local_options ? 'delete_option' : 'delete_site_option';

		return $func( self::format_option_name( $option_name ) );
	}

	/**
	 * Setup main options
	 */
	public static function sanitize_options() {
		$simple_int_options = array(
			'allowed_retries',
			'lockout_duration',
			'valid_duration',
			'allowed_lockouts',
			'long_duration',
			'notify_email_after'
		);

		foreach ( $simple_int_options as $option ) {
			$val = self::get( $option );
			if ( (int) $val != $val || (int) $val <= 0 ) {
				self::update( $option, 1 );
			}
		}

		if ( self::get( 'notify_email_after' ) > self::get( 'allowed_lockouts' ) ) {
			self::update( 'notify_email_after', self::get( 'allowed_lockouts' ) );
		}

		$args         = explode( ',', self::get( 'lockout_notify' ) );
		$args_allowed = explode( ',', LLA_LOCKOUT_NOTIFY_ALLOWED );
		$new_args     = array_intersect( $args, $args_allowed );

		self::update( 'lockout_notify', implode( ',', $new_args ) );

		$client_type = self::get( 'client_type' );

		if ( $client_type != LLA_DIRECT_ADDR && $client_type != LLA_PROXY_ADDR ) {
			self::update( 'client_type', LLA_DIRECT_ADDR );
		}
	}
}