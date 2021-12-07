<?php
add_action( 'wp_ajax_piotnetforms_hubspot_contact_property_list', 'piotnetforms_hubspot_contact_property_list' );
add_action( 'wp_ajax_nopriv_piotnetforms_hubspot_contact_property_list', 'piotnetforms_hubspot_contact_property_list' );

function piotnetforms_hubspot_contact_property_list(){

    $hubspot_api = get_option('piotnetforms-hubspot-api-key');
    $group_name = $_REQUEST['group'];
    $url = 'https://api.hubapi.com/properties/v1/contacts/groups/named/'.$group_name.'?hapikey='.$hubspot_api.'&includeProperties=true';
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
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response);
    $result = $response->properties;
    foreach ( $result as $item ) {
        echo '<div class="piotnetforms-hubspot-list__group" style="padding-top:5px;">
                    <label><strong>'.$item->label.'</strong></label>
                    <div class="piotnetforms-hubspot-list__group-value" style="padding-bottom:3px;">
                        <input type="text" value="'.$item->name.'" readonly>
                    </div>
                </div>';
    }

    wp_die();
}