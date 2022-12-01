<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../source/export.php';

add_action( 'wp_ajax_piotnetforms_export', 'piotnetforms_export' );

function piotnetforms_prepare_http_header( $post_id ) {
	header( 'Content-Type: application/download' );
	header( 'Content-Disposition: attachment;filename=piotnetforms-export-' . get_post_field( 'post_name', $post_id ) . '.json' );
}

function piotnetforms_export() {
	piotnetforms_prepare_http_header( $_GET['id'] );

	$output = fopen( 'php://output', 'w' ) or show_error( "Can't open php://output" );
	$data   = [];

	if ( ! isset( $_GET['id'] ) ) {
		$data['error'] = "can't find post id";
	} elseif ( ! is_user_logged_in() || ! current_user_can( 'edit_others_posts' ) ) {
		$data['error'] = 'permission error';
	} else {
		$post_id = intval( $_GET['id'] );
		$data    = piotnetforms_export_post( $post_id );
	}

	$raw = json_encode( $data );
	fwrite( $output, $raw );
	fclose( $output );

	wp_die();
}
