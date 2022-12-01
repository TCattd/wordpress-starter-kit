<?php

require_once( __DIR__.'/helper/functions.php' );
add_action( 'wp_ajax_piotnetforms_sendinblue_get_list', 'piotnetforms_sendinblue_get_list' );
add_action( 'wp_ajax_nopriv_piotnetforms_sendinblue_get_list', 'piotnetforms_sendinblue_get_list' );

function piotnetforms_sendinblue_get_list() {
	$api_key = $_REQUEST['apiKey'];
	$helper = new piotnetforms_Helper();
	if ( $api_key == 'false' ) {
		$api_key = get_option( 'piotnetforms-addons-for-elementor-pro-sendinblue-api-key' );
	}
	if ( $api_key ) {
		$lists = json_decode( $helper->piotnetforms_sendinblue_get_list( $api_key ) )->lists;
		echo '<h3 class="piotnetforms-sendinblue-title">Lists:</h3>';
		foreach ( $lists as $key => $val ) {
			echo '<div class="piotnetforms-sendinblue-item"><label>'.$val->name.'</label><div class="piotnetforms-sendinblue-item-id"><input type="text" value="'.$val->id.'" readonly></div></div>';
		}
	}
	wp_die();
}
