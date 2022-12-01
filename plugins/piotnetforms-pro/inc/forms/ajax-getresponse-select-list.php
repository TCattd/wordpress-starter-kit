<?php

	add_action( 'wp_ajax_piotnetforms_getresponse_select_list', 'piotnetforms_getresponse_select_list' );
	add_action( 'wp_ajax_nopriv_piotnetforms_getresponse_select_list', 'piotnetforms_getresponse_select_list' );

	function piotnetforms_getresponse_select_list() {
		$api = $_REQUEST['api'];
		if ( $api == 'false' ) {
			$api = get_option( 'piotnetforms-getresponse-api-key' );
		}
		$get_response_url_campaigns = 'https://api.getresponse.com/v3/campaigns/';
		$get_response_request_data = [
			'header' => [
			'Content-Type: application/json',
			'X-Auth-Token: api-key '.$_REQUEST['api'],
		 ]
		];
		$ch = curl_init( $get_response_url_campaigns );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'X-Auth-Token: api-key '.$api,
		] );
		$result = json_decode( curl_exec( $ch ) );
		foreach ( $result as $item ) {
			echo '<div class="piotnetforms-getresponse-list__inner"><label class="elementor-control-title">'.$item->name.'</label><div class="piotnetforms-getresponse-list__inner-item"><input type="text" value="'.$item->campaignId.'" readonly/></div></div>';
		}
		wp_die();
	}
