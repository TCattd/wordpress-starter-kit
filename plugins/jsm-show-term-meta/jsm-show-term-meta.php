<?php
/*
 * Plugin Name: JSM Show Term Metadata
 * Text Domain: jsm-show-term-meta
 * Domain Path: /languages
 * Plugin URI: https://surniaulula.com/extend/plugins/jsm-show-term-meta/
 * Assets URI: https://jsmoriss.github.io/jsm-show-term-meta/assets/
 * Author: JS Morisset
 * Author URI: https://surniaulula.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: Show term metadata in a metabox when editing terms - a great tool for debugging issues with term metadata.
 * Requires PHP: 7.2.34
 * Requires At Least: 5.5
 * Tested Up To: 6.2.2
 * Version: 3.1.1
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes and/or incompatible API changes (ie. breaking changes).
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2016-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'JsmStm' ) ) {

	class JsmStm {

		private static $instance = null;	// JsmStm class object.

		public function __construct() {

			if ( ! is_admin() ) return;	// This is an admin-only plugin.

			$plugin_dir = trailingslashit( dirname( __FILE__ ) );

			require_once $plugin_dir . 'lib/config.php';
			JsmStmConfig::set_constants( __FILE__ );

			JsmStmConfig::require_libs( __FILE__ );

			add_action( 'init', array( $this, 'init_textdomain' ) );
			add_action( 'init', array( $this, 'init_objects' ) );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain() {

			load_plugin_textdomain( 'jsm-show-term-meta', false, 'jsm-show-term-meta/languages/' );
		}

		public function init_objects() {

			new JsmStmCompat();
			new JsmStmScript();
			new JsmStmTerm();
		}
	}

	JsmStm::get_instance();
}
