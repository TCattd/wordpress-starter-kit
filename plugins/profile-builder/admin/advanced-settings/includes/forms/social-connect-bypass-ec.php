<?php

add_filter( 'wppb_sc_email_confirmation_on_off', 'wppb_toolbox_sc_email_confirmation' );
function wppb_toolbox_sc_email_confirmation() {
    return 'off';
}
