<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	add_action( 'wp_ajax_piotnetforms_create_form', 'piotnetforms_create_form' );

	function piotnetforms_create_form() {
		$template = $_POST['template'];
		$title = $_POST['title'];

		if ( $template == 'blank' ) {
			$my_post = [
				'post_title' => !empty( $title ) ? $title : 'Piotnet Forms',
				'post_status' => 'publish',
				'post_type' => 'piotnetforms'
			];

			$post_id = wp_insert_post( $my_post );

			update_post_meta( $post_id, '_piotnetforms_version', PIOTNETFORMS_PRO_VERSION );

			$my_post_update = [
				'ID'          => $post_id,
				'post_title'  => !empty( $title ) ? $title : 'Piotnet Forms #' . $post_id,
				'post_status' => 'publish',
			];

			wp_update_post( $my_post_update );
		} else {
			$arrContextOptions=[
				'ssl'=>[
					'verify_peer'=>false,
					'verify_peer_name'=>false,
				],
			];

			$file_url = WP_PLUGIN_DIR . '/piotnetforms-pro/assets/forms/templates/' . $template . '/file.json';
			$file_content = file_get_contents( $file_url, false, stream_context_create( $arrContextOptions ) );
			$data = json_decode( $file_content, true );
			$post = [
				'post_title'  => !empty( $title ) ? $title : $data['title'],
				'post_status' => 'publish',
				'post_type'   => 'piotnetforms',
			];

			$post_id = wp_insert_post( $post );

			piotnetforms_do_import( $post_id, $data );
		}

		echo admin_url() . 'admin.php?page=piotnetforms&post=' . $post_id;

		wp_die();
	}
