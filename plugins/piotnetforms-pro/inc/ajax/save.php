<?php
	if ( ! defined( 'ABSPATH' ) ) { exit; }
	add_action( 'wp_ajax_piotnetforms_save', 'piotnetforms_save' );
	add_action( 'wp_ajax_nopriv_piotnetforms_save', 'piotnetforms_save' );

	const DATA_VERSION_PIOTNET = 1;

	function piotnetforms_save() {

		$post_id = $_POST['post_id'];

		if ( ! is_user_logged_in() || ! current_user_can( 'edit_others_posts' ) ) {
			print_r( 'permission_error' );
			return;
		}

		print_r( $post_id );

		if ( isset( $_POST['piotnetforms_data'] ) ) {
			$raw_data = stripslashes( $_POST['piotnetforms_data'] );
			$data = json_decode( $raw_data );

			$data->version = DATA_VERSION_PIOTNET;
			$data_str        = json_encode( $data );
			update_post_meta( $post_id, '_piotnetforms_data', wp_slash( $data_str ) );

			if (isset($_POST['piotnetforms_global_settings'])) {
				$raw_global_settings = stripslashes( $_POST['piotnetforms_global_settings'] );
				update_option( 'piotnetforms_global_settings', wp_slash( $raw_global_settings ) );
			}
		}

		if ( isset( $_POST['piotnet-widgets-css'] ) ) {
			$widgets_css      = $_POST['piotnet-widgets-css'];
			$revision_version = intval( get_post_meta( $post_id, '_piotnet-revision-version', true ) ) + 1;
			update_post_meta( $post_id, '_piotnet-revision-version', $revision_version );

			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = $upload_dir . '/piotnetforms/css/';

			$file = fopen( $upload_dir . $post_id . '.css', 'wb' );
			fwrite( $file, stripslashes( $widgets_css ) );
			fclose( $file );

			if (isset($_POST['piotnet-global-css'])) {
				$global_css      = $_POST['piotnet-global-css'];
				$global_css_version = intval( get_option( 'piotnet-global-css-version' ) ) + 1;
				update_option( 'piotnet-global-css-version', $global_css_version );
				$file = fopen( $upload_dir . 'global.css', 'wb' );
				fwrite( $file, stripslashes( $global_css ) );
				fclose( $file );
			}
		}

		$post_title = get_the_title($post_id);

		$my_post_update = [
			'ID'          => $post_id,
			'post_title'  => ! empty( $post_title ) ? $post_title : ( 'Piotnet Forms #' . $post_id ),
			'post_status' => 'publish',
		];
		wp_update_post( $my_post_update );

		update_post_meta( $post_id, '_piotnetforms_form_id', get_the_title($post_id) );

		wp_die();
	}
