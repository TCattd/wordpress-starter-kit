<?php

$text = wppb_toolbox_get_settings( 'fields', 'send-credentials-text' );

if ( !empty( $text ) )
    add_filter( 'wppb_send_credentials_checkbox_logic', 'wppb_toolbox_send_credentials_checkbox_text', 10, 2 );

function wppb_toolbox_send_credentials_checkbox_text($requestdata, $form) {
    $text = wppb_toolbox_get_settings( 'fields', 'send-credentials-text' );

    return '<li class="wppb-form-field wppb-send-credentials-checkbox"><label for="send_credentials_via_email"><input id="send_credentials_via_email" type="checkbox" name="send_credentials_via_email" value="sending"'.( ( isset( $request_data['send_credentials_via_email'] ) && ( $request_data['send_credentials_via_email'] == 'sending' ) ) ? ' checked' : '' ).'/>'.
    esc_html( $text ).'</label></li>';
}
