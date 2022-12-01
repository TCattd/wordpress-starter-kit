<?php

require_once( __DIR__.'/helper/functions.php' );
add_action( 'wp_ajax_piotnetforms_paypal_get_plan', 'piotnetforms_paypal_get_plan' );
add_action( 'wp_ajax_nopriv_piotnetforms_paypal_get_plan', 'piotnetforms_paypal_get_plan' );

function piotnetforms_paypal_get_plan() {
	$sand_box = $_REQUEST['sandbox'];
	$paypal_url = $sand_box == 'yes' ? 'https://api-m.sandbox.paypal.com/' : 'https://api-m.paypal.com/';
	$client_id = get_option( 'piotnetforms-paypal-client-id' );
	$client_secret = get_option( 'piotnetforms-paypal-secret-id' );
	$helper = new Piotnetforms_Helper();
	$token = $helper->piotnetforms_paypal_get_token( $client_id, $client_secret, $paypal_url );
	$curl = curl_init();
	curl_setopt_array( $curl, [
	CURLOPT_URL => $paypal_url.'v1/billing/plans',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'GET',
	CURLOPT_HTTPHEADER => [
		'Content-Type: application/json',
		'Authorization: Bearer '.$token
	],
	] );
	$response = json_decode( curl_exec( $curl ) )->plans;
	curl_close( $curl );
	if ( !empty( $response ) ) {
		$html = '';
		foreach ( $response as $plan ) {
			$html .= '<div class="piotnetforms-paypal-plan-item"><label>'.$plan->name.'</label><div><input type="text" value="'.$plan->id.'" readonly></div></div>';
		}
		echo $html;
	} else {
		echo 'No plans have been created yet';
	}
	wp_die();
}
