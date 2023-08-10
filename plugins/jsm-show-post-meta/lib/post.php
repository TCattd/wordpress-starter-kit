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

if ( ! class_exists( 'JsmSpmPost' ) ) {

	class JsmSpmPost {

		public function __construct() {

			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 1000, 2 );
			add_action( 'wp_ajax_get_metabox_postbox_id_jsmspm_inside', array( $this, 'ajax_get_metabox' ) );
			add_action( 'wp_ajax_delete_jsmspm_meta', array( $this, 'ajax_delete_meta' ) );
		}

		public function add_meta_boxes( $post_type, $post_obj ) {

			if ( ! isset( $post_obj->ID ) ) {	// Exclude links.

				return;
			}

			$show_meta_cap = apply_filters( 'jsmspm_show_metabox_capability', 'manage_options', $post_obj );
			$can_show_meta = current_user_can( $show_meta_cap, $post_obj->ID );

			if ( ! $can_show_meta ) {

				return;

			} elseif ( ! apply_filters( 'jsmspm_show_metabox_post_type', true, $post_type ) ) {

				return;
			}

			$metabox_id      = 'jsmspm';
			$metabox_title   = __( 'Post Metadata', 'jsm-show-post-meta' );
			$metabox_screen  = $post_type;
			$metabox_context = 'normal';
			$metabox_prio    = 'low';
			$callback_args   = array(	// Second argument passed to the callback function / method.
				'__block_editor_compatible_meta_box' => true,
			);

			add_meta_box( $metabox_id, $metabox_title, array( $this, 'show_metabox' ), $metabox_screen, $metabox_context, $metabox_prio, $callback_args );
		}

		public function show_metabox( $post_obj ) {

			echo $this->get_metabox( $post_obj );
		}

		public function get_metabox( $post_obj ) {

			if ( empty( $post_obj->ID ) ) {

				return;
			}

			$cf          = JsmSpmConfig::get_config();
			$post_meta   = get_metadata( 'post', $post_obj->ID );
			$skip_keys   = array( '/^_encloseme/' );
			$metabox_id  = 'jsmspm';
			$admin_l10n  = $cf[ 'plugin' ][ 'jsmspm' ][ 'admin_l10n' ];

			$titles = array(
				'key'   => __( 'Key', 'jsm-show-post-meta' ),
				'value' => __( 'Value', 'jsm-show-post-meta' ),
			);

			return SucomUtilMetabox::get_table_metadata( $post_meta, $skip_keys, $post_obj, $post_obj->ID, $metabox_id, $admin_l10n, $titles );
		}

		public function ajax_get_metabox() {

			$doing_ajax = SucomUtilWP::doing_ajax();

			if ( ! $doing_ajax ) {	// Just in case.

				return;

			} elseif ( SucomUtil::get_const( 'DOING_AUTOSAVE' ) ) {

				die( -1 );
			}

			check_ajax_referer( JSMSPM_NONCE_NAME, '_ajax_nonce', $die = true );

			if ( empty( $_POST[ 'post_id' ] ) ) {

				die( -1 );
			}

			$post_id = $_POST[ 'post_id' ];

			$post_obj = SucomUtil::get_post_object( $post_id );

			if ( ! is_object( $post_obj ) ) {

				die( -1 );

			} elseif ( empty( $post_obj->post_type ) ) {

				die( -1 );

			} elseif ( empty( $post_obj->post_status ) ) {

				die( -1 );
			}

			$metabox_html = $this->get_metabox( $post_obj );

			die( $metabox_html );
		}

		public function ajax_delete_meta() {

			$doing_ajax = SucomUtilWP::doing_ajax();

			if ( ! $doing_ajax ) {	// Just in case.

				return;
			}

			check_ajax_referer( JSMSPM_NONCE_NAME, '_ajax_nonce', $die = true );

			if ( empty( $_POST[ 'obj_id' ] ) || empty( $_POST[ 'meta_key' ] ) ) {

				die( -1 );
			}

			/*
			 * Note that the $table_row_id value must match the value used in SucomUtilMetabox::get_table_metadata(),
			 * so that jQuery can hide the table row after a successful delete.
			 */
			$metabox_id   = 'jsmspm';
			$obj_id       = sanitize_key( $_POST[ 'obj_id' ] );
			$meta_key     = sanitize_key( $_POST[ 'meta_key' ] );
			$table_row_id = sanitize_key( $metabox_id . '_' . $obj_id . '_' . $meta_key );
			$post_obj     = get_post( $obj_id );
			$del_meta_cap = apply_filters( 'jsmspm_delete_meta_capability', 'manage_options', $post_obj );
			$can_del_meta = current_user_can( $del_meta_cap, $obj_id );

			if ( ! $can_del_meta ) {

				die( -1 );
			}

			if ( delete_metadata( 'post', $obj_id, $meta_key ) ) {

				die( $table_row_id );
			}

			die( false );	// Show delete failed message.
		}
	}
}
