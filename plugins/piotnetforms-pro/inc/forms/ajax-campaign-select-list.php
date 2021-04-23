<?php
    add_action( 'wp_ajax_piotnetforms_campaign_select_list', 'piotnetforms_campaign_select_list' );
	add_action( 'wp_ajax_nopriv_piotnetforms_campaign_select_list', 'piotnetforms_campaign_select_list' );

	function piotnetforms_campaign_select_list() {
		$url = $_POST['campaign_url'];
        $campaign_key = $_POST['campaign_key'];
        if($url == 'false' && $campaign_key == 'false'){
            $url = get_option('piotnetforms-activecampaign-api-url');
            $campaign_key = get_option('piotnetforms-activecampaign-api-key');
        }
        
        $params = array(
            'api_key'      => $campaign_key,
            'api_action'   => 'list_list',
            'api_output'   => 'serialize',
            'ids'          => '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20',
            'full'         => 1,
        );
        $query = "";
        foreach( $params as $key => $value ) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');
        $url = rtrim($url, '/ ');
        if ( !function_exists('curl_init') ) die('CURL not supported. (introduced in PHP 4.0.2)');

        if ( $params['api_output'] == 'json' && !function_exists('json_decode') ) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }

        $api = $url . '/admin/api.php?' . $query;

        $request = curl_init($api);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $response = (string)curl_exec($request);

        curl_close($request);

        if ( !$response ) {
            die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }

        $result = unserialize($response);
        foreach ($result as $key => $value){
            if(isset($value['id']) && isset($value['name'])){
                echo '<div class="piotnetforms-ajax-active-campaign-list"><label>'.$value['name'].'</label>';
                echo '<div class="piotnetforms-active-campaign-list__item"><input type="text" value="'.$value['id'].'" readonly/></div></div>';
            }
        }
		wp_die(); 
	}