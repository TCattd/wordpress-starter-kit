<?php
/* Hide all profile fields in backend */
add_action('admin_init', 'wppb_toolbox_remove_all_fields_from_backend');
function wppb_toolbox_remove_all_fields_from_backend(){
	remove_action( 'show_user_profile', 'display_profile_extra_fields_in_admin', 10 );
	remove_action( 'edit_user_profile', 'display_profile_extra_fields_in_admin', 10 );
	remove_action( 'personal_options_update', 'save_profile_extra_fields_in_admin', 10 );
	remove_action( 'edit_user_profile_update', 'save_profile_extra_fields_in_admin', 10 );
	remove_action( 'user_profile_update_errors', 'wppb_validate_backend_fields', 10, 3 );
}
