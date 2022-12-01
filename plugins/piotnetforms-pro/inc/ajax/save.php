<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'wp_ajax_piotnetforms_save', 'piotnetforms_save' );

	const DATA_VERSION_PIOTNET = 1;

	function piotnetforms_save() {
		$post_id = $_POST['post_id'];
		$version = empty( get_post_meta( $post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $post_id, '_piotnetforms_version', true );

		$response = [
			'post_id' => $post_id,
			'reload' => false,
			'error_message' => null,
		];

		if ( ! is_user_logged_in() || ! current_user_can( 'edit_others_posts' ) ) {
			$response['error_message'] = "You haven't enough permission to edit this post.";
			echo json_encode( $response );
			wp_die();
			return;
		}

		if ( isset( $_POST['piotnetforms_data'] ) ) {
			$raw_data = stripslashes( $_POST['piotnetforms_data'] );
			$data = json_decode( $raw_data );

			$last_error = json_last_error();
			if ( $last_error > 0 ) {
				$response['error_message'] = "Can't process data. Code " . json_last_error();
				echo json_encode( $response );
				wp_die();
				return;
			}

			$data->version = $version;
			$data_str = json_encode( $data );
			update_post_meta( $post_id, '_piotnetforms_data', wp_slash( $data_str ) );

			if ( isset( $_POST['piotnetforms_global_settings'] ) ) {
				$raw_global_settings = stripslashes( $_POST['piotnetforms_global_settings'] );

				$global_settings_data = json_decode( $raw_global_settings );
				$last_error = json_last_error();
				if ( $last_error > 0 ) {
					$response['error_message'] = "Can't process Global Setting data. Code " . json_last_error();
					echo json_encode( $response );
					wp_die();
					return;
				}

				update_option( 'piotnetforms_global_settings', wp_slash( $raw_global_settings ) );
			}

			if ( isset( $_POST['piotnetforms_single_settings'] ) ) {
				$raw_single_settings = stripslashes( $_POST['piotnetforms_single_settings'] );
				$raw_single_settings_data = json_decode( $raw_single_settings, true );

				$last_error = json_last_error();
				if ( $last_error > 0 ) {
					$response['error_message'] = "Can't process Form Setting data. Code " . json_last_error();
					echo json_encode( $response );
					wp_die();
					return;
				}

				update_post_meta( $post_id, '_piotnetforms_single_settings', wp_slash( $raw_single_settings ) );

				if ( !empty( $raw_single_settings_data['single-settings']['fields']['form_id'] ) ) {
					$raw_data = get_post_meta( $post_id, '_piotnetforms_data', true );
					$form_id_old = get_post_meta( $post_id, '_piotnetforms_form_id', true );
					$form_id = $raw_single_settings_data['single-settings']['fields']['form_id'];
					if ( !empty( get_post_meta( $post_id, '_piotnetforms_form_id', true ) ) ) {
						$data_str = str_replace( '"form_id":"' . $form_id_old, '"form_id":"' . $form_id, $raw_data );
						$data_str = str_replace( '"piotnetforms_booking_form_id":"' . $form_id_old, '"piotnetforms_booking_form_id":"' . $form_id, $data_str );
						$data_str = str_replace( '"piotnetforms_woocommerce_checkout_form_id":"' . $form_id_old, '"piotnetforms_woocommerce_checkout_form_id":"' . $form_id, $data_str );
						$data_str = str_replace( '"piotnetforms_conditional_logic_form_form_id":"' . $form_id_old, '"piotnetforms_conditional_logic_form_form_id":"' . $form_id, $data_str );
						$data_str = str_replace( '"piotnetforms_repeater_form_id":"' . $form_id_old, '"piotnetforms_repeater_form_id":"' . $form_id, $data_str );
						update_post_meta( $post_id, '_piotnetforms_data', wp_slash( $data_str ) );
					}

					update_post_meta( $post_id, '_piotnetforms_form_id', $form_id );
				}

				if ( !empty( $raw_single_settings_data['single-settings']['fields']['form_title'] ) ) {
					$my_post_update = [
						'ID'          => $post_id,
						'post_title'  => $raw_single_settings_data['single-settings']['fields']['form_title'],
					];
					wp_update_post( $my_post_update );
				}

				$response['reload'] = true;
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

			if ( isset( $_POST['piotnet-global-css'] ) ) {
				$global_css      = $_POST['piotnet-global-css'];
				$global_css_version = intval( get_option( 'piotnet-global-css-version' ) ) + 1;
				update_option( 'piotnet-global-css-version', $global_css_version );
				$file = fopen( $upload_dir . 'global.css', 'wb' );
				fwrite( $file, stripslashes( $global_css ) );
				fclose( $file );
			}
		}

		if ( empty( get_post_meta( $post_id, '_piotnetforms_version', true ) ) ) {
			// $post_title = get_the_title($post_id);

			// $my_post_update = [
			// 	'ID'          => $post_id,
			// 	'post_title'  => ! empty( $post_title ) ? $post_title : ( 'Piotnet Forms #' . $post_id ),
			// 	'post_status' => 'publish',
			// ];
			// wp_update_post( $my_post_update );

			// update_post_meta( $post_id, '_piotnetforms_form_id', get_the_title($post_id) );
		}

		echo json_encode( $response );

		wp_die();
	}
