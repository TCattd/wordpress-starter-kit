<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! defined( 'JSMSUM_PLUGINDIR' ) ) {

	die( 'Do. Or do not. There is no try.' );
}

if ( ! class_exists( 'JsmSumUser' ) ) {

	class JsmSumUser {

		public function __construct() {

			add_action( 'edit_user_profile', array( $this, 'add_meta_boxes' ), 1000, 1 );
			add_action( 'show_user_profile', array( $this, 'add_meta_boxes' ), 1000, 1 );
			add_action( 'wp_ajax_delete_jsmsum_meta', array( $this, 'ajax_delete_meta' ) );
		}

		public function add_meta_boxes( $user_obj ) {

			if ( ! isset( $user_obj->ID ) ) {	// Just in case.

				return;
			}

			$screen        = get_current_screen();
			$show_meta_cap = apply_filters( 'jsmsum_show_metabox_capability', 'manage_options', $user_obj );
			$can_show_meta = current_user_can( $show_meta_cap, $user_obj->ID );

			if ( ! $can_show_meta ) {

				return;

			} elseif ( ! apply_filters( 'jsmsum_show_metabox_screen_base', true, $screen->base ) ) {

				return;
			}

			$metabox_id      = 'jsmsum';
			$metabox_title   = __( 'User Metadata', 'jsm-show-user-meta' );
			$metabox_screen  = 'jsm-show-user-meta';
			$metabox_context = 'normal';
			$metabox_prio    = 'low';
			$callback_args   = array(	// Second argument passed to the callback function / method.
				'__block_editor_compatible_meta_box' => true,
			);

			add_meta_box( $metabox_id, $metabox_title, array( $this, 'show_metabox' ), $metabox_screen, $metabox_context, $metabox_prio, $callback_args );

			echo '<div class="metabox-holder">' . "\n";

			do_meta_boxes( $metabox_screen, 'normal', $user_obj );

			echo "\n" . '</div><!-- .metabox-holder -->' . "\n";
		}

		public function show_metabox( $user_obj ) {

			echo $this->get_metabox( $user_obj );
		}

		public function get_metabox( $user_obj ) {

			if ( empty( $user_obj->ID ) ) {

				return;
			}

			$cf          = JsmSumConfig::get_config();
			$user_meta   = get_metadata( 'user', $user_obj->ID );
			$skip_keys   = array( '/^closedpostboxes_/', '/columnshidden$/', '/^meta-box-order_/', '/^metaboxhidden_/', '/^screen_layout_/' );
			$metabox_id  = 'jsmsum';
			$admin_l10n  = $cf[ 'plugin' ][ 'jsmsum' ][ 'admin_l10n' ];

			$titles = array(
				'key'   => __( 'Key', 'jsm-show-user-meta' ),
				'value' => __( 'Value', 'jsm-show-user-meta' ),
			);

			return SucomUtilMetabox::get_table_metadata( $user_meta, $skip_keys, $user_obj, $user_obj->ID, $metabox_id, $admin_l10n, $titles );
		}

		public function ajax_delete_meta() {

			$doing_ajax = SucomUtilWP::doing_ajax();

			if ( ! $doing_ajax ) {	// Just in case.

				return;
			}

			check_ajax_referer( JSMSUM_NONCE_NAME, '_ajax_nonce', $die = true );

			if ( empty( $_POST[ 'obj_id' ] ) || empty( $_POST[ 'meta_key' ] ) ) {

				die( -1 );
			}

			/*
			 * Note that the $table_row_id value must match the value used in SucomUtilMetabox::get_table_metadata(),
			 * so that jQuery can hide the table row after a successful delete.
			 */
			$metabox_id   = 'jsmsum';
			$obj_id       = sanitize_key( $_POST[ 'obj_id' ] );
			$meta_key     = sanitize_key( $_POST[ 'meta_key' ] );
			$table_row_id = sanitize_key( $metabox_id . '_' . $obj_id . '_' . $meta_key );
			$user_obj     = get_userdata( $obj_id );
			$del_meta_cap = apply_filters( 'jsmsum_delete_meta_capability', 'manage_options', $user_obj );
			$can_del_meta = current_user_can( $del_meta_cap, $obj_id );

			if ( ! $can_del_meta ) {

				die( -1 );
			}

			if ( delete_metadata( 'user', $obj_id, $meta_key ) ) {

				die( $table_row_id );
			}

			die( false );	// Show delete failed message.
		}
	}
}
