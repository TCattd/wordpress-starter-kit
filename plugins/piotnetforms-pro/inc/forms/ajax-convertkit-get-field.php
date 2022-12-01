<?php

add_action( 'wp_ajax_piotnetforms_convertkit_get_fields', 'piotnetforms_convertkit_get_fields' );
add_action( 'wp_ajax_nopriv_piotnetforms_convertkit_get_fields', 'piotnetforms_convertkit_get_fields' );

function piotnetforms_convertkit_get_fields() {
	$api_key = $_REQUEST['api_key'];
	$html = '';
	if ( $api_key == 'false' ) {
		$api_key = get_option( 'piotnetforms-convertkit-api-key' );
	}
	if ( $api_key != false ) {
		$curl = curl_init();
		curl_setopt_array( $curl, [
		CURLOPT_URL => 'https://api.convertkit.com/v3/custom_fields?api_key='.$api_key,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		] );
		$response = curl_exec( $curl );
		curl_close( $curl );
		$fields = json_decode( $response )->custom_fields;
		if ( !empty( $fields ) ) {
			$html .= '<h3 class="piotnetforms-converkit-title">Tags Name</h3>';
			$html .= '<div class="piotnetforms-convertkit-field"><label>Email</label><div><input type="text" value="email" readonly></div></div>';
			$html .= '<div class="piotnetforms-convertkit-field"><label>First Name</label><div><input type="text" value="first_name" readonly></div></div>';
			foreach ( $fields as $index => $item ) {
				$html .= '<div class="piotnetforms-convertkit-field"><label>'.$item->label.'</label><div><input type="text" value="'.$item->key.'" readonly></div></div>';
			}
			echo $html;
		}
	} else {
		echo 'Please enter the API key.';
	}
	wp_die();
}
