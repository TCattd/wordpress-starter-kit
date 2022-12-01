<?php

function wppb_toolbox_unique_display_name_edit_profile( $message, $field, $request_data, $form_location ) {

	if ( isset( $request_data['display_name']) )  {
		if ( isset( $_GET['edit_user'] ) ) {
            $user = get_userdata(sanitize_text_field($_GET['edit_user']));
        }
		else if ( isset( $request_data['user_id'] ) ) {
            $user = get_userdata($request_data['user_id']);
        }
        else {
            $user = wp_get_current_user();
        }

		if ( $request_data['display_name'] == $user->display_name )
			return $message;

		if ( wppb_toolbox_unique_display_name_check( $request_data['display_name'] ) ) {
            return __('This display name is already in use. Please choose another one.', 'profile-builder');
        }
	}

	return $message;

}
add_filter( 'wppb_check_form_field_default-display-name-publicly-as', 'wppb_toolbox_unique_display_name_edit_profile', 20, 4 );

function wppb_toolbox_unique_display_name_register( $display_name ){
    if ( isset( $_POST['action'] ) &&
        ( ( $_POST['action'] === 'register' ) ||
            ( isset( $_POST['todo'] ) && $_POST['action'] === 'wppb_handle_email_confirmation_cases' && $_POST['todo'] === 'confirm' )
        )
    ) {
        if (isset($display_name)) {
            if (wppb_toolbox_unique_display_name_check($display_name)) {
                $i = 1;
                while (wppb_toolbox_unique_display_name_check($display_name . ' ' . $i)) {
                    $i++;
                }
                $display_name = $display_name . ' ' . $i;
            }
        }
    }
    return $display_name;
}
add_filter( 'pre_user_display_name', 'wppb_toolbox_unique_display_name_register', 10 );

function wppb_toolbox_unique_display_name_check($display_name ){
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users WHERE display_name = %s", $display_name ) );

    if ( $count >= 1 ){
        return true;
    }
    return false;
}