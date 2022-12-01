<?php

require_once( __DIR__.'/helper/functions.php' );
add_action( 'wp_ajax_piotnetforms_mollie_get_payment', 'piotnetforms_mollie_get_payment' );
add_action( 'wp_ajax_nopriv_piotnetforms_mollie_get_payment', 'piotnetforms_mollie_get_payment' );

function piotnetforms_mollie_get_payment() {
	$mollie_api_key = get_option( 'piotnetforms-mollie-api-key' );
	$curl = curl_init();
	curl_setopt_array( $curl, [
	CURLOPT_URL => 'https://api.mollie.com/v2/payments/'.$_POST['payment_id'],
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'GET',
	CURLOPT_HTTPHEADER => [
		'Authorization: Bearer '.$mollie_api_key
	],
	] );
	$response = curl_exec( $curl );
	curl_close( $curl );
	echo $response;
	wp_die();
}
