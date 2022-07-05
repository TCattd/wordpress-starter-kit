<?php

add_filter('wppb_send_credentials_checkbox_logic', 'wppb_toolbox_send_credentials_enabled', 10, 2);
function wppb_toolbox_send_credentials_enabled($requestdata, $form){
   return '<input id="send_credentials_via_email" type="hidden" name="send_credentials_via_email" value="sending"/>';
}
