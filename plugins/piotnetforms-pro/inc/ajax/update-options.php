<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	add_action( 'wp_ajax_piotnetforms_update_options', 'piotnetforms_update_options' );

	function piotnetforms_update_options() {
		if ( !empty( $_POST['function'] ) && !empty( $_POST['option_name'] ) ) {
			switch ( $_POST['function'] ) {
				case 'update':
					update_option( $_POST['option_name'], $_POST['option_value'] );
					break;

				case 'delete':
					delete_option( $_POST['option_name'] );
					break;
			}
		}

		wp_die();
	}
