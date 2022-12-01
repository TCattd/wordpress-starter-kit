<?php

	add_action( 'wp_ajax_piotnetforms_campaign_select_list', 'piotnetforms_campaign_select_list' );
	add_action( 'wp_ajax_nopriv_piotnetforms_campaign_select_list', 'piotnetforms_campaign_select_list' );

	function piotnetforms_campaign_select_list() {
		$url = $_POST['campaign_url'];
		$campaign_key = $_POST['campaign_key'];
		if ( $url == 'false' && $campaign_key == 'false' ) {
			$url = get_option( 'piotnetforms-activecampaign-api-url' );
			$campaign_key = get_option( 'piotnetforms-activecampaign-api-key' );
		}
		$curl = curl_init();
		curl_setopt_array( $curl, [
		CURLOPT_URL => $url.'/api/3/lists',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			'Accept: application/json',
			'Api-Token: '.$campaign_key,
		],
		] );
		$response = curl_exec( $curl );
		curl_close( $curl );
		$response = json_decode( $response );
		$response = $response->lists;
		if ( !$response ) {
			die( 'Nothing was returned. Do you have a connection to Email Marketing server?' );
		}
		foreach ( $response as $key => $value ) {
			echo '<div class="piotnetforms-ajax-active-campaign-list"><label>'.$value->name.'</label>';
			echo '<div class="piotnetforms-active-campaign-list__item"><input type="text" value="'.$value->id.'" readonly/></div></div>';
		}
		wp_die();
	}
