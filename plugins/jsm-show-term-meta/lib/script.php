<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! defined( 'JSMSTM_PLUGINDIR' ) ) {

	die( 'Do. Or do not. There is no try.' );
}

if ( ! class_exists( 'JsmStmScript' ) ) {

	class JsmStmScript {

		public function __construct() {

			$doing_ajax = SucomUtilWP::doing_ajax();

			if ( ! $doing_ajax ) {

				if ( is_admin() ) {

					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				}
			}
		}

		public function admin_enqueue_scripts( $hook_name ) {

			$this->admin_register_page_scripts( $hook_name );
		}

		public function admin_register_page_scripts( $hook_name ) {

			$cf = JsmStmConfig::get_config();

			$admin_l10n = $cf[ 'plugin' ][ 'jsmstm' ][ 'admin_l10n' ];

			// The version number should match the version in js/com/jquery-admin-page.js.
			wp_register_script( 'sucom-admin-page', JSMSTM_URLPATH . 'js/com/jquery-admin-page.min.js',
				$deps = array( 'jquery' ), '20230704', $in_footer = true );

			wp_localize_script( 'sucom-admin-page', $admin_l10n, $this->get_admin_page_script_data() );

			wp_enqueue_script( 'sucom-admin-page' );
		}

		public function get_admin_page_script_data() {

			return array(
				'_ajax_nonce'   => wp_create_nonce( JSMSTM_NONCE_NAME ),
				'_ajax_actions' => array(
					'delete_jsmstm_meta' => 'delete_jsmstm_meta',
				),
				'_del_failed_transl' => __( 'Unable to delete meta key \'{1}\' for term ID {0}.', 'jsm-show-term-meta' ),
			);
		}
	}
}
