<?php
/*
we eliminated the independent option for admin email customizer so we need to tie it to the user email customizer option for turning off,
for on it doesn't matter because we have a || relationship
*/
add_filter( 'pre_update_option_wppb_module_settings', 'wppb_update_admin_email_customizer_value', 10, 2 );
function wppb_update_admin_email_customizer_value( $new_value, $old_value ){
	if( isset($old_value['wppb_emailCustomizerAdmin']) && $old_value['wppb_emailCustomizerAdmin'] == 'show' && isset($new_value['wppb_emailCustomizer']) && $new_value['wppb_emailCustomizer'] == 'hide' ){
		$new_value['wppb_emailCustomizerAdmin'] = 'hide';
	}
	return $new_value;
}
