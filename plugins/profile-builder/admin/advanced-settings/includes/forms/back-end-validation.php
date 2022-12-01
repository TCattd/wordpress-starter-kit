<?php

add_action ('user_profile_update_errors', 'wppb_toolbox_remove_backend_profile_validation', 5);
function wppb_toolbox_remove_backend_profile_validation(){
	// old
	remove_action( 'user_profile_update_errors', 'wppb_validate_backend_fields', 10, 3);

	// new
	remove_action( 'user_profile_update_errors', 'wppb_validate_fields_in_admin', 10, 3);
}
