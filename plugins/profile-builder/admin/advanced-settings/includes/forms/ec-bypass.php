<?php

add_filter( 'wppb_email_confirmation_on_register', 'wppb_toolbox_bypass_email_confirmation', 2, 20 );
function wppb_toolbox_bypass_email_confirmation( $email_confirmation, $global_request ) {
    $forms = wppb_toolbox_get_settings( 'forms', 'ec-bypass' );

    if ( in_array( $global_request['form_name'], $forms ) )
        return 'no';

    return $email_confirmation;
}
