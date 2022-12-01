<?php

add_action( 'wp_ajax_piotnetforms_hubspot_group_list', 'piotnetforms_hubspot_group_list' );
add_action( 'wp_ajax_nopriv_piotnetforms_hubspot_group_list', 'piotnetforms_hubspot_group_list' );

function piotnetforms_hubspot_group_list() {
	$url = 'https://api.hubapi.com/properties/v1/contacts/groups';
    $hubspot_access_token = get_option( 'piotnetforms-hubspot-access-token' );
    $admin_url = admin_url('admin.php?page=piotnetforms');

    if (!empty($hubspot_access_token)) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$hubspot_access_token
            ),
        ));
        $response = curl_exec( $curl );
        curl_close( $curl );
        $response = json_decode( $response );
        foreach ( $response as $item ) {
            echo '<div class="piotnetforms-hubspot-list__item" style="padding-top:5px;">
                        <label><strong>'.$item->displayName.'</strong></label>
                        <div class="piotnetforms-hubspot-list__item-value" style="padding-bottom:3px;">
                            <input type="text" value="'.$item->name.'" readonly>
                        </div>
                    </div>';
        }
    } else {
        echo 'Please enter your Access Token <a href="'.$admin_url.'">here</a>';
    }

	wp_die();
}
