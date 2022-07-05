<?php

function wppb_toolbox_unique_display_name( $message, $field, $request_data, $form_location ) {

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

		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users WHERE display_name = %s", $request_data['display_name'] ) );

		if ( $count >= 1 )
		 	return __( 'This display name is already in use. Please choose another one.', 'profile-builder' );
	}

	return $message;

}
add_filter( 'wppb_check_form_field_default-display-name-publicly-as', 'wppb_toolbox_unique_display_name', 20, 4 );
