<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! defined( 'JSMSPM_PLUGINDIR' ) ) {

	die( 'Do. Or do not. There is no try.' );
}

if ( ! class_exists( 'JsmSpmScript' ) ) {

	class JsmSpmScript {

		public function __construct() {

			$doing_ajax = SucomUtilWP::doing_ajax();

			if ( ! $doing_ajax ) {

				if ( is_admin() ) {

					add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ), 1000 );

					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				}
			}
		}

		public function enqueue_block_editor_assets() {

			if ( SucomUtil::is_post_page() ) {

				$src = JSMSPM_URLPATH . 'js/jquery-block-editor.min.js';

				/*
				 * The 'wp-editor' dependency should not be enqueued together with the new widgets block editor.
				 */
				$deps = array( 'wp-data', 'wp-editor', 'wp-edit-post', 'sucom-admin-page' );

				/*
				 * The 'jsmspm-block-editor' script, with its 'wp-edit-post' dependency, must be loaded in the
				 * footer to work around a bug in the NextGEN Gallery featured image picker. If the script is
				 * loaded in the header, with a dependency on 'wp-edit-post', the NextGEN Gallery featured image
				 * picker does not load.
				 */
				$in_footer = true;

				wp_register_script( 'jsmspm-block-editor', $src, $deps, JSMSPM_VERSION, $in_footer );

				wp_enqueue_script( 'jsmspm-block-editor' );
			}
		}

		public function admin_enqueue_scripts( $hook_name ) {

			$this->admin_register_page_scripts( $hook_name );
		}

		public function admin_register_page_scripts( $hook_name ) {

			$cf = JsmSpmConfig::get_config();

			$admin_l10n = $cf[ 'plugin' ][ 'jsmspm' ][ 'admin_l10n' ];

			// The version number should match the version in js/com/jquery-admin-page.js.
			wp_register_script( 'sucom-admin-page', JSMSPM_URLPATH . 'js/com/jquery-admin-page.min.js',
				$deps = array( 'jquery' ), '20230704', $in_footer = true );

			wp_localize_script( 'sucom-admin-page', $admin_l10n, $this->get_admin_page_script_data() );

			wp_enqueue_script( 'sucom-admin-page' );
		}

		public function get_admin_page_script_data() {

			return array(
				'_ajax_nonce'   => wp_create_nonce( JSMSPM_NONCE_NAME ),
				'_ajax_actions' => array(
					'delete_jsmspm_meta' => 'delete_jsmspm_meta',
					'metabox_postboxes'  => array(
						'jsmspm' => 'get_metabox_postbox_id_jsmspm_inside',
					),
				),
				'_del_failed_transl' => __( 'Unable to delete meta key \'{1}\' for post ID {0}.', 'jsm-show-post-meta' ),
			);
		}
	}
}
