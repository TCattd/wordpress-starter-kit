<?php

function wppb_email_customizer_generate_merge_tags( $special_merge_tags = null ){

	$mustache_vars = array(
						array(
							'group-title' => __( 'Available Tags', 'profile-builder' ),
							'variables' => wppb_email_customizer_generate_default_meta_merge_tags( $special_merge_tags )
						),
						array(
							'group-title' => __( 'User Fields Tags', 'profile-builder' ),
							'variables' => wppb_email_customizer_generate_meta_merge_tags()
						)
					);

	return $mustache_vars;
}


function wppb_email_customizer_generate_default_meta_merge_tags( $special_merge_tags ){
	$merge_tags[] = array( 'name' => 'site_url', 'type' => 'ec_site_url', 'label' => __( 'Site Url', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => 'site_name', 'type' => 'ec_site_name', 'label' => __( 'Site Name', 'profile-builder' ) );

	if ( $special_merge_tags != 'email_confirmation' ){
		$merge_tags[] = array( 'name' => 'user_id', 'type' => 'ec_user_id', 'label' => __( 'User Id', 'profile-builder' )  );
	}

	$merge_tags[] = array( 'name' => 'username', 'type' => 'ec_username', 'label' => __( 'Username', 'profile-builder' )  );
	$merge_tags[] = array( 'name' => 'user_email', 'type' => 'ec_user_email', 'label' => __( 'Email', 'profile-builder' )  );
    $merge_tags[] = array( 'name' => 'password', 'type' => 'ec_password', 'label' => __( 'Password', 'profile-builder' )  );
    $merge_tags[] = array( 'name' => 'role', 'type' => 'ec_role', 'label' => __( 'User Role', 'profile-builder' )  );
	$merge_tags[] = array( 'name' => 'role_label', 'type' => 'ec_role_label', 'label' => __( 'User Role Label', 'profile-builder' )  );
	$merge_tags[] = array( 'name' => 'website', 'type' => 'ec_website', 'label' => __( 'Website', 'profile-builder' )  );
	$merge_tags[] = array( 'name' => 'reply_to', 'type' => 'ec_reply_to', 'label' => __( 'Reply To', 'profile-builder' )  );

	if ( wppb_can_users_signup_blog() ) {
		$merge_tags[] = array('name' => 'blog_url', 'type' => 'ec_blog_url', 'label' => __('Blog URL', 'profile-builder'));
	}

	if ( $special_merge_tags == 'email_confirmation' ){
		$merge_tags[] = array( 'name' => 'activation_key', 'type' => 'ec_activation_key', 'label' => __( 'Activation Key', 'profile-builder' )  );
		$merge_tags[] = array( 'name' => 'activation_url', 'type' => 'ec_activation_url', 'label' => __( 'Activation Url', 'profile-builder' )  );
		$merge_tags[] = array( 'name' => 'activation_link', 'type' => 'ec_activation_link', 'unescaped' => true, 'label' => __( 'Activation Link', 'profile-builder' )  );
	}

	if ( $special_merge_tags == 'password_reset' ){
		$merge_tags[] = array( 'name' => 'reset_key', 'type' => 'ec_reset_key', 'label' => __( 'Reset Key', 'profile-builder' )  );
		$merge_tags[] = array( 'name' => 'reset_url', 'type' => 'ec_reset_url', 'label' => __( 'Reset Url', 'profile-builder' )  );
		$merge_tags[] = array( 'name' => 'reset_link', 'type' => 'ec_reset_link', 'unescaped' => true, 'label' => __( 'Reset Link', 'profile-builder' )  );
	}

    if ( $special_merge_tags == 'change_email_address_request' ){
        $merge_tags[] = array( 'name' => 'user_email_change_link', 'type' => 'ec_user_email_change_link', 'unescaped' => true, 'label' => __( 'Change Email Address Link', 'profile-builder' )  );
    }

	if ( $special_merge_tags == 'admin_approval' ){
        $merge_tags[] = array( 'name' => 'approve_url', 'type' => 'ec_approve_url', 'label' => __( 'Approve User Url', 'profile-builder' )  );
        $merge_tags[] = array( 'name' => 'approve_link', 'type' => 'ec_approve_link', 'unescaped' => true, 'label' => __( 'Approve User Link', 'profile-builder' )  );
        $merge_tags[] = array( 'name' => 'unapprove_url', 'type' => 'ec_unapprove_url', 'label' => __( 'Unapprove User Url', 'profile-builder' )  );
        $merge_tags[] = array( 'name' => 'unapprove_link', 'type' => 'ec_unapprove_link', 'unescaped' => true, 'label' => __( 'Unapprove User Link', 'profile-builder' )  );
    }

    if ( $special_merge_tags == 'epaa_notification' ){
        $merge_tags[] = array( 'name' => 'approved_fields', 'type' => 'ec_epaa_approved_fields', 'label' => __( 'Approved Fields', 'profile-builder' )  );
        $merge_tags[] = array( 'name' => 'unapproved_fields', 'type' => 'ec_epaa_unapproved_fields',  'label' => __( 'Unapproved Fields', 'profile-builder' )  );
    }

    if ( $special_merge_tags == 'epaa_notification_admin' ){
        $merge_tags[] = array( 'name' => 'modified_fields', 'type' => 'ec_epaa_modified_fields', 'label' => __( 'Modified Fields', 'profile-builder' )  );
        $merge_tags[] = array( 'name' => 'approval_url', 'type' => 'ec_epaa_approval_url',  'label' => __( 'Approval URL', 'profile-builder' )  );
    }

	return $merge_tags;
}


function wppb_email_customizer_generate_meta_merge_tags(){
	$wppb_manage_fields = apply_filters( 'wppb_email_customizer_get_fields' , get_option( 'wppb_manage_fields', 'not_found' ) );
	$merge_tags = array();

	if ( $wppb_manage_fields != 'not_found' ){
		foreach( $wppb_manage_fields as $key => $value ){
			if( !empty( $value['meta-name'] ) ){

                // Skip all map fields
                if( $value['field'] == 'Map' || $value['field'] == 'HTML' )
                    continue;

				$merge_tags[] = array( 'name' => $value['meta-name'], 'type' => 'ec_user_meta', 'label' => $value['field-title'] );
                if( $value['field'] == 'Select' || $value['field'] == 'Select (Multiple)' || $value['field'] == 'Select (Country)' || $value['field'] == 'Select (Currency)' || $value['field'] == 'Checkbox' || $value['field'] == 'Radio' )
                    $merge_tags[] = array( 'name' => $value['meta-name'] . '_labels', 'type' => 'ec_user_meta_labels', 'label' => $value['field-title'] . ' Labels' );
            }
		}
	}

	return apply_filters( 'wppb_email_customizer_get_merge_tags', $merge_tags );
}

function wppb_email_customizer_admin_approval_new_user_signup_filter_handler( $default_string, $user_email, $user_password, $email_sent_from, $option_name ){
    $email_customizer_option = get_option( $option_name, 'not_found' );
    if ( $email_customizer_option != 'not_found' ){
        $user_data = get_user_by( 'email', $user_email );
        $special_merge_tags = ( current_filter() == 'wppb_register_admin_email_message_with_admin_approval' ) ?  'admin_approval' : '';
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags($special_merge_tags), $email_customizer_option, array( 'user_id' => $user_data->ID, 'user_login' => $user_data->user_login, 'user_email' => $user_email, 'user_password' => $user_password, 'user_data' => $user_data, 'email_sent_from' => $email_sent_from ) );
    }

    return $default_string;
}

function wppb_email_customizer_admin_approval_new_user_signup_subject_filter_handler( $default_string, $user_email, $user_password, $email_sent_from, $option_name ){
	if( current_user_can( 'delete_users') ) {
		$email_customizer_option = get_option( 'wppb_user_emailc_admin_approval_notif_approved_email_subject', 'not_found' );
	} else {
		$email_customizer_option = get_option( $option_name, 'not_found' );
	}

	if ( $email_customizer_option != 'not_found' ){
		$user_data = get_user_by( 'email', $user_email );
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_id' => $user_data->ID, 'user_login' => $user_data->user_login, 'user_email' => $user_email, 'user_password' => $user_password, 'user_data' => $user_data, 'email_sent_from' => $email_sent_from ) );
	}

	return $default_string;
}

function wppb_email_customizer_admin_approval_new_user_signup_message_filter_handler( $default_string, $user_email, $user_password, $email_sent_from, $option_name ){
	if( current_user_can( 'delete_users') ) {
		$email_customizer_option = get_option( 'wppb_user_emailc_admin_approval_notif_approved_email_content', 'not_found' );
	} else {
		$email_customizer_option = get_option( $option_name, 'not_found' );
	}

	if ( $email_customizer_option != 'not_found' ){
		$user_data = get_user_by( 'email', $user_email );
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_id' => $user_data->ID, 'user_login' => $user_data->user_login, 'user_email' => $user_email, 'user_password' => $user_password, 'user_data' => $user_data, 'email_sent_from' => $email_sent_from ) );
	}

	return $default_string;
}

function wppb_email_customizer_admin_approval_new_user_status_filter_handler( $default_string, $user_data, $new_status, $email_sent_from, $option_name ){
	$email_customizer_option = get_option( $option_name, 'not_found' );

	if ( $email_customizer_option != 'not_found' ){
        if( !empty( $user_data ) ){
			wppb_change_email_from_headers();
		    return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_id' => $user_data->ID, 'user_login' => $user_data->user_login, 'user_email' => $user_data->user_email, 'user_password' => $user_data->user_pass, 'user_data' => $user_data, 'email_sent_from' => $email_sent_from ) );
		}
	}

	return $default_string;
}

function wppb_email_customizer_email_confirmation_filter_handler( $default_string, $user_email, $username, $key, $activation_url, $serialized_data, $email_sent_from, $option_name ){
    $email_customizer_option = get_option( $option_name, 'not_found' );
	if ( $email_customizer_option != 'not_found' ){
        $unserialized_data = unserialize( $serialized_data );
        if( !empty( $unserialized_data ) ){
			wppb_change_email_from_headers();
		    return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'email_confirmation' ), $email_customizer_option, array( 'user_login' => $unserialized_data['user_login'], 'user_email' => $user_email, 'user_password' => $unserialized_data['user_pass'], 'email_confirmation_unserialized_data' => $unserialized_data, 'email_confirmation_key' => $key, 'email_confirmation_url' => $activation_url, 'email_sent_from' => $email_sent_from ) );
		}
	}

	return $default_string;
}

function wppb_email_customizer_password_reset_content_filter_handler( $default_string, $user_id, $user_login, $user_email ) {
	$email_customizer_option = get_option( 'wppb_user_emailc_reset_email_content', 'not_found' );
	$key = wppb_retrieve_activation_key( $user_login );
	$url = add_query_arg( array( 'key' => $key ), wppb_curpageurl() );

	if( $email_customizer_option != 'not_found' ) {
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'password_reset' ), $email_customizer_option, array( 'user_login' => $user_login, 'reset_key' => $key, 'reset_url' => $url, 'user_email' => $user_email, 'user_id' => $user_id ) );
	}

	return $default_string;
}

function wppb_email_customizer_password_reset_title_filter_handler( $default_string, $username ) {
	$email_customizer_option = get_option( 'wppb_user_emailc_reset_email_subject', 'not_found' );

	if( $email_customizer_option != 'not_found' ) {
		wppb_change_email_from_headers();
        $user_info = get_user_by( 'login', $username );
        if( !empty( $user_info->data->user_email ) )
            $user_email = $user_info->data->user_email;
        else
            $user_email = '';
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'password_reset' ), $email_customizer_option, array( 'user_login' => $username, 'user_email' => $user_email, 'user_id' => $user_info->data->ID ) );
	}

	return $default_string;
}

function wppb_email_customizer_password_reset_success_content_filter_handler( $default_string, $username, $new_password, $userID ) {
	$email_customizer_option = get_option( 'wppb_user_emailc_reset_success_email_content', 'not_found' );

	if( $email_customizer_option != 'not_found' ) {
        $user_info = get_userdata( $userID );
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_login' => $username, 'user_password' => $new_password, 'user_id' => $userID, 'user_email' => $user_info->user_email ) );
	}

	return $default_string;
}

function wppb_email_customizer_password_reset_success_title_filter_handler( $default_string, $username ) {
	$email_customizer_option = get_option( 'wppb_user_emailc_reset_success_email_subject', 'not_found' );

	if( $email_customizer_option != 'not_found' ) {
        $user_info = get_user_by( 'login', $username );
		/* we could have email instead of username in $username */
		if( !$user_info )
			$user_info = get_user_by( 'email', $username );

        if( !empty( $user_info->data->user_email ) )
            $user_email = $user_info->data->user_email;
        else
            $user_email = '';
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_login' => $username, 'user_email' => $user_email, 'user_id' => $user_info->data->ID ) );
	}

	return $default_string;
}

function wppb_admin_email_customizer_password_reset_content_filter_handler( $default_string, $username, $new_password, $userID ) {
	$email_customizer_option = get_option( 'wppb_admin_emailc_user_password_reset_email_content', 'not_found' );

	if( $email_customizer_option != 'not_found' ) {
        $user_info = get_userdata( $userID );
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_login' => $username, 'user_password' => $new_password, 'user_id' => $userID, 'user_email' => $user_info->user_email ) );
	}

	return $default_string;
}

function wppb_admin_email_customizer_password_reset_title_filter_handler( $default_string, $username ) {
	$email_customizer_option = get_option( 'wppb_admin_emailc_user_password_reset_email_subject', 'not_found' );

	if( $email_customizer_option != 'not_found' ) {
        $user_info = get_user_by( 'login', $username );
		/* we could have email instead of username in $username */
		if( !$user_info )
			$user_info = get_user_by( 'email', $username );

		if( !empty( $user_info->data->user_email ) )
            $user_email = $user_info->data->user_email;
        else
            $user_email = '';
		wppb_change_email_from_headers();
		return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_login' => $username, 'user_email' => $user_email, 'user_id' => $user_info->data->ID ) );
	}

	return $default_string;
}

function wppb_email_customizer_user_email_change_request_subject_filter_handler($default_string, $user) {
    $email_customizer_option = get_option( 'wppb_user_emailc_change_email_address_request_subject', 'not_found' );

    if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
        wppb_change_email_from_headers();
        return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'change_email_address_request' ), $email_customizer_option, array( 'user_login' => $user->user_login, 'user_email' => $user->user_email, 'user_id' => $user->ID ));
    }

    return $default_string;
}

function wppb_email_customizer_user_email_change_request_content_filter_handler($default_string, $user, $email_change_request_url) {
    $email_customizer_option = get_option( 'wppb_user_emailc_change_email_address_request_content', 'not_found' );

    if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
        wppb_change_email_from_headers();

        return (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'change_email_address_request' ), $email_customizer_option, array( 'user_login' => $user->user_login, 'user_email' => $user->user_email, 'user_id' => $user->ID, 'change_email_url' => $email_change_request_url ));
    }

    return $default_string;
}

function wppb_email_customizer_change_email_address_title_filter_handler ( $default, $old_user_data, $new_user_data ){
	$email_customizer_option = get_option( 'wppb_user_emailc_change_email_address_subject', 'not_found' );

	if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
		$email_change_email = $default;
		$subject = (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_login' => $old_user_data['user_login'], 'user_email' => $old_user_data['user_email'], 'user_id' => $old_user_data['ID'] ) );
		$email_change_email['subject'] = html_entity_decode( htmlspecialchars_decode( $subject, ENT_QUOTES ), ENT_QUOTES );

		wppb_change_email_from_headers();
		return $email_change_email;
	}

	return $default;
}

function wppb_email_customizer_change_email_address_header_filter_handler( $email_change_email, $user, $userdata ){
	$email_change_email['headers'] = array( 'Content-Type: text/html; charset=UTF-8' );
	return $email_change_email;
}

function wppb_email_customizer_change_email_address_content_filter_handler ( $default, $old_user_data, $new_user_data ){
	$email_customizer_option = get_option( 'wppb_user_emailc_change_email_address_content', 'not_found' );

	if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {

		$email_change_email = $default;
		$email_content = (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags(), $email_customizer_option, array( 'user_login' => $old_user_data['user_login'], 'user_email' => $old_user_data['user_email'], 'user_id' => $old_user_data['ID'] ) );
		$email_change_email['message'] = $email_content;

		wppb_change_email_from_headers();
		return $email_change_email;
	}

	return $default;
}

function wppb_email_customizer_epaa_title_filter_handler ( $default, $user_id, $approved_field_names, $unapproved_field_names ){
    $email_customizer_option = get_option( 'wppb_user_emailc_epaa_notification_subject', 'not_found' );

    if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
        $user_info = get_user_by( 'id', $user_id );
        $subject = (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'epaa_notification' ), $email_customizer_option, array( 'user_login' => $user_info->data->user_login, 'user_email' => $user_info->data->user_email, 'user_id' => $user_info->data->ID, 'approved_field_names' => $approved_field_names, 'unapproved_field_names' => $unapproved_field_names ) );

        wppb_change_email_from_headers();
        return $subject;
    }

    return $default;
}

function wppb_email_customizer_epaa_content_filter_handler ( $default, $user_id, $approved_field_names, $unapproved_field_names ){
    $email_customizer_option = get_option( 'wppb_user_emailc_epaa_notification_content', 'not_found' );

    if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
        $user_info = get_user_by( 'id', $user_id );
        $email_content = (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'epaa_notification' ), $email_customizer_option, array( 'user_login' => $user_info->data->user_login, 'user_email' => $user_info->data->user_email, 'user_id' => $user_info->data->ID, 'approved_field_names' => $approved_field_names, 'unapproved_field_names' => $unapproved_field_names ) );

        wppb_change_email_from_headers();
        return $email_content;
    }

    return $default;
}

function wppb_admin_email_customizer_epaa_title_filter_handler ( $default, $user_id, $fields, $approve_url ){
    $email_customizer_option = get_option( 'wppb_admin_emailc_epaa_notification_subject', 'not_found' );

    if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
        $user_info = get_user_by( 'id', $user_id );
        $subject = (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'epaa_notification_admin' ), $email_customizer_option, array( 'user_login' => $user_info->data->user_login, 'user_email' => $user_info->data->user_email, 'user_id' => $user_info->data->ID, 'modified_fields' => $fields, 'approval_url' => $approve_url ) );

        wppb_change_email_from_headers();
        return $subject;
    }

    return $default;
}

function wppb_admin_email_customizer_epaa_content_filter_handler ( $default, $user_id, $fields, $approve_url ){
    $email_customizer_option = get_option( 'wppb_admin_emailc_epaa_notification_content', 'not_found' );

    if( $email_customizer_option != 'not_found' && class_exists( 'PB_Mustache_Generate_Template' ) ) {
        $user_info = get_user_by( 'id', $user_id );
        $email_content = (string) new PB_Mustache_Generate_Template( wppb_email_customizer_generate_merge_tags( 'epaa_notification_admin' ), $email_customizer_option, array( 'user_login' => $user_info->data->user_login, 'user_email' => $user_info->data->user_email, 'user_id' => $user_info->data->ID, 'modified_fields' => $fields, 'approval_url' => $approve_url ) );

        wppb_change_email_from_headers();
        return html_entity_decode( htmlspecialchars_decode( $email_content, ENT_QUOTES ), ENT_QUOTES );
    }

    return $default;
}


$wppb_addonOptions = get_option( 'wppb_module_settings' );
$wppb_email_customizer_activate = 'hide';
if ( ( !empty( $wppb_addonOptions['wppb_emailCustomizer'] ) && $wppb_addonOptions['wppb_emailCustomizer'] == 'show' ) || ( !empty( $wppb_addonOptions['wppb_emailCustomizerAdmin'] ) && $wppb_addonOptions['wppb_emailCustomizerAdmin'] == 'show' ) ) {
	$wppb_email_customizer_activate = 'show';
}
// using filters, we overwrite the old content with the new one from the email customizer (for the user)
if ( $wppb_email_customizer_activate == 'show' ){
	add_filter ( 'wppb_register_user_email_subject_without_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_filter_handler', 10, 5 );
	add_filter ( 'wppb_register_user_email_message_without_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_filter_handler', 10, 5 );

	add_filter ( 'wppb_register_user_email_subject_with_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_subject_filter_handler', 10, 5 );
	add_filter ( 'wppb_register_user_email_message_with_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_message_filter_handler', 10, 5 );

	add_filter ( 'wppb_new_user_status_subject_approved', 'wppb_email_customizer_admin_approval_new_user_status_filter_handler', 10, 5 );
	add_filter ( 'wppb_new_user_status_message_approved', 'wppb_email_customizer_admin_approval_new_user_status_filter_handler', 10, 5 );

	add_filter ( 'wppb_new_user_status_subject_unapproved', 'wppb_email_customizer_admin_approval_new_user_status_filter_handler', 10, 5 );
	add_filter ( 'wppb_new_user_status_message_unapproved', 'wppb_email_customizer_admin_approval_new_user_status_filter_handler', 10, 5 );

	add_filter ( 'wppb_signup_user_notification_email_subject', 'wppb_email_customizer_email_confirmation_filter_handler', 10, 8 );
	add_filter ( 'wppb_signup_user_notification_email_content', 'wppb_email_customizer_email_confirmation_filter_handler', 10, 8 );

	add_filter ( 'wppb_recover_password_message_content_sent_to_user1', 'wppb_email_customizer_password_reset_content_filter_handler', 10, 4 );
	add_filter ( 'wppb_recover_password_message_title_sent_to_user1', 'wppb_email_customizer_password_reset_title_filter_handler', 10, 2 );

	add_filter ( 'wppb_recover_password_message_content_sent_to_user2', 'wppb_email_customizer_password_reset_success_content_filter_handler', 10, 4 );
	add_filter ( 'wppb_recover_password_message_title_sent_to_user2', 'wppb_email_customizer_password_reset_success_title_filter_handler', 10, 2 );

    add_filter ( 'wppb_user_email_change_request_notification_subject', 'wppb_email_customizer_user_email_change_request_subject_filter_handler', 10, 3);
    add_filter ( 'wppb_user_email_change_request_notification_content', 'wppb_email_customizer_user_email_change_request_content_filter_handler', 10, 3);

	add_filter ( 'email_change_email', 'wppb_email_customizer_change_email_address_content_filter_handler', 10, 3);
	add_filter ( 'email_change_email', 'wppb_email_customizer_change_email_address_title_filter_handler', 10, 3);
	add_filter ( 'email_change_email', 'wppb_email_customizer_change_email_address_header_filter_handler', 10, 3);

    if( function_exists( 'wppb_in_init_edit_profile_approval' ) ) {
        add_filter('wppb_epaa_user_email_content', 'wppb_email_customizer_epaa_content_filter_handler', 10, 4);
        add_filter('wppb_epaa_user_email_subject', 'wppb_email_customizer_epaa_title_filter_handler', 10, 4);
    }

}

// using filters, we overwrite the old content with the new one from the email customizer (for the admin)
if ( $wppb_email_customizer_activate == 'show' ){
	add_filter ( 'wppb_register_admin_email_subject_without_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_filter_handler', 10, 5 );
	add_filter ( 'wppb_register_admin_email_message_without_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_filter_handler', 10, 5 );

	add_filter ( 'wppb_register_admin_email_subject_with_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_filter_handler', 10, 5 );
	add_filter ( 'wppb_register_admin_email_message_with_admin_approval', 'wppb_email_customizer_admin_approval_new_user_signup_filter_handler', 10, 5 );

	add_filter ( 'wppb_recover_password_message_content_sent_to_admin', 'wppb_admin_email_customizer_password_reset_content_filter_handler', 10, 4 );
	add_filter ( 'wppb_recover_password_message_title_sent_to_admin', 'wppb_admin_email_customizer_password_reset_title_filter_handler', 10, 2 );

    if( function_exists( 'wppb_in_init_edit_profile_approval' ) ) {
		add_filter('wppb_epaa_admin_email_content', 'wppb_admin_email_customizer_epaa_content_filter_handler', 10, 4);
		add_filter('wppb_epaa_admin_email_subject', 'wppb_admin_email_customizer_epaa_title_filter_handler', 10, 4);
    }

}


// Mustache variables

/**
 * Function that overwrites the default reply-to with the one set in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $sender_email
 *
 * @return string
 */
function wppb_ec_website_email( $value, $merge_tag_name, $merge_tag, $extra_data ){
    $admin_email_address = get_bloginfo( 'admin_email' );

	$emailc_common_settings_from_reply_to_email = get_option( 'wppb_emailc_common_settings_from_reply_to_email', 'not_found' );
	if ( $emailc_common_settings_from_reply_to_email != 'not_found' ) {
        if( strpos( $emailc_common_settings_from_reply_to_email, '{{reply_to}}' ) === false ) {
            if( is_email( trim( $emailc_common_settings_from_reply_to_email ) ) )
                $admin_email_address = trim( $emailc_common_settings_from_reply_to_email );
        }
    }

    return apply_filters( 'wppb_ec_sender_email_filter', trim( $admin_email_address ) );
}
add_filter( 'mustache_variable_ec_reply_to', 'wppb_ec_website_email', 10, 4 );


/**
 * Function that filters and returns the site_url in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_site_url( $value, $merge_tag_name, $merge_tag, $extra_data ){
	return apply_filters( 'wppb_ec_site_url_filter', get_bloginfo( 'url' ) );
}
add_filter( 'mustache_variable_ec_site_url', 'wppb_ec_replace_site_url', 10, 4 );


/**
 * Function that filters and returns the site_name in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_site_name( $value, $merge_tag_name, $merge_tag, $extra_data ){
	return apply_filters( 'wppb_ec_site_name_filter', html_entity_decode( htmlspecialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), ENT_QUOTES ) );
}
add_filter( 'mustache_variable_ec_site_name', 'wppb_ec_replace_site_name', 10, 4 );


/**
 * Function that filters and returns the user_id in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_user_id( $value, $merge_tag_name, $merge_tag, $extra_data ){
    if( !empty( $extra_data['user_id'] ) )
	    return $extra_data['user_id'];
    else
        return '';
}
add_filter( 'mustache_variable_ec_user_id', 'wppb_ec_replace_user_id', 10, 4 );


/**
 * Function that filters and returns the username in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_username( $value, $merge_tag_name, $merge_tag, $extra_data ){
    $wppb_general_settings = get_option( 'wppb_general_settings' );

	if ( isset( $extra_data['email_confirmation_unserialized_data']['user_login'] ) ){
        if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) )
            return $extra_data['email_confirmation_unserialized_data']['user_email'];
        else
		    return $extra_data['email_confirmation_unserialized_data']['user_login'];
    }

    if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) )
        return $extra_data['user_email'];
    else
        return $extra_data['user_login'];

}
add_filter( 'mustache_variable_ec_username', 'wppb_ec_replace_username', 10, 4 );


/**
 * Function that filters and returns the user_email in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_user_email( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_unserialized_data']['user_email'] ) )
		return $extra_data['email_confirmation_unserialized_data']['user_email'];

    if( !empty( $extra_data['user_email'] ) )
	    return $extra_data['user_email'];
    else
        return '';
}
add_filter( 'mustache_variable_ec_user_email', 'wppb_ec_replace_user_email', 10, 4 );

/**
 * Function that filters and returns the users password in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_password( $value, $merge_tag_name, $merge_tag, $extra_data ){
    /* don't send the password to the admin */
    global $wp_current_filter;
    if( !empty( $wp_current_filter ) && is_array( $wp_current_filter ) ){
        foreach( $wp_current_filter as $filter ){
            if( $filter == 'wppb_register_admin_email_subject_without_admin_approval' ||
                $filter == 'wppb_register_admin_email_message_without_admin_approval' ||
                $filter == 'wppb_register_admin_email_subject_with_admin_approval' ||
                $filter == 'wppb_register_admin_email_message_with_admin_approval' ||
                $filter == 'wppb_recover_password_message_content_sent_to_admin' ||
                $filter == 'wppb_recover_password_message_title_sent_to_admin'
            ){
                return __( 'The users selected password at signup', 'profile-builder' );
            }
        }
    }

    $wppb_general_settings = get_option( 'wppb_general_settings' );

    if( empty( $extra_data['email_confirmation_unserialized_data']['user_pass'] ) && empty( $extra_data['user_password'] ) ){
        return __( 'Your selected password at signup', 'profile-builder' );
    }

    if ( isset( $extra_data['email_confirmation_unserialized_data']['user_pass'] ) ) {
        if( base64_encode( base64_decode($extra_data['email_confirmation_unserialized_data']['user_pass'] ) ) === $extra_data['email_confirmation_unserialized_data']['user_pass'] )
            return base64_decode($extra_data['email_confirmation_unserialized_data']['user_pass']);
        else
            return __( 'Your selected password at signup', 'profile-builder' );
    }

    if( wppb_get_admin_approval_option_value() === 'yes' ){
        if ( isset( $extra_data['user_password'] ) ) {
            global $wpdb;
            $search_for_password = $wpdb->get_results(
                "
                SELECT user_pass
                FROM $wpdb->users
                WHERE user_pass = '".$extra_data['user_password']."'
                "
            );
            if( !empty( $search_for_password ) )
                return __( 'Your selected password at signup', 'profile-builder' );
        }
    }

    return $extra_data['user_password'];
}
add_filter( 'mustache_variable_ec_password', 'wppb_ec_replace_password', 10, 4 );


/**
 * Function that filters and returns the users website in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_website( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_unserialized_data']['user_url'] ) )
		return $extra_data['email_confirmation_unserialized_data']['user_url'];
    if( !empty( $extra_data['user_data'] ) )
	    return $extra_data['user_data']->user_url;
}
add_filter( 'mustache_variable_ec_website', 'wppb_ec_replace_website', 10, 4 );


/**
 * Function that filters and returns the users blog url in the Email Customizer
 *
 * @since v.2.5.5
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_blog_url( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_unserialized_data']['wppb_create_new_site_checkbox'] ) && $extra_data['email_confirmation_unserialized_data']['wppb_create_new_site_checkbox'] == 'yes' ) {
			$blog_details = wpmu_validate_blog_signup( $extra_data['email_confirmation_unserialized_data']['wppb_blog_url'], $extra_data['email_confirmation_unserialized_data']['wppb_blog_title'] );
			if ( empty($blog_details['errors']->errors['blogname']) && empty($blog_details['errors']->errors['blog_title']))
				return $blog_details['domain'] . $blog_details['path'];
	}

	if( !empty( $extra_data['user_data'] ) ) {
		return wppb_get_blog_url_of_user_id($extra_data['user_data']->ID);
	}
}
add_filter( 'mustache_variable_ec_blog_url', 'wppb_ec_replace_blog_url', 10, 4 );


/**
 * Function that filters and returns the users role in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_role( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if( isset( $extra_data['email_confirmation_unserialized_data']['role'] ) ) {
		return $extra_data['email_confirmation_unserialized_data']['role'];
	}

	if( ! empty( $extra_data['user_data'] ) ) {
		$user_role = implode( ', ', $extra_data['user_data']->roles );
		return $user_role;
	}

	if( ! empty ( $extra_data['user_id'] ) ) {
		$user_data = get_user_by( 'id', $extra_data['user_id'] );
		$user_role = implode( ', ', $user_data->roles );
		return $user_role;
	}
}
add_filter( 'mustache_variable_ec_role', 'wppb_ec_replace_role', 10, 4 );

/**
 * Function that filters and returns the users role label in the Email Customizer
 *
 * @since v.2.7.1
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_role_label( $value, $merge_tag_name, $merge_tag, $extra_data ){
	$roles = explode(', ', wppb_ec_replace_role( $value, $merge_tag_name, $merge_tag, $extra_data ) );
	$role_labels = '';

	foreach ($roles as $role) {
		$role_labels .= wppb_get_role_name( $role ) . ', ';
	}

	return rtrim($role_labels, ', ');
}
add_filter( 'mustache_variable_ec_role_label', 'wppb_ec_replace_role_label', 10, 4 );

/**
 * Function that filters and returns the users activation_key in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_activation_key( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_key'] ) )
		return $extra_data['email_confirmation_key'];
}
add_filter( 'mustache_variable_ec_activation_key', 'wppb_ec_replace_activation_key', 10, 4 );


/**
 * Function that filters and returns the users activation_url in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_activation_url( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_url'] ) )
		return $extra_data['email_confirmation_url'];
}
add_filter( 'mustache_variable_ec_activation_url', 'wppb_ec_replace_activation_url', 10, 4 );


/**
 * Function that filters and returns the users activation_link in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_activation_link( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_url'] ) )
		return '<a href="'.$extra_data['email_confirmation_url'].'" target="_blank">'.$extra_data['email_confirmation_url'].'</a>';
}
add_filter( 'mustache_variable_ec_activation_link', 'wppb_ec_replace_activation_link', 10, 4 );


/**
 * Function that filters and returns the users reset_key in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_reset_key( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['reset_key'] ) )
		return $extra_data['reset_key'];
}
add_filter( 'mustache_variable_ec_reset_key', 'wppb_ec_replace_reset_key', 10, 4 );


/**
 * Function that filters and returns the users reset_url in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_reset_url( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['reset_url'] ) )
		return $extra_data['reset_url'];
}
add_filter( 'mustache_variable_ec_reset_url', 'wppb_ec_replace_reset_url', 10, 4 );


/**
 * Function that filters and returns the users reset_link in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_reset_link( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['reset_url'] ) )
		return '<a href="'.$extra_data['reset_url'].'" target="_blank">'.$extra_data['reset_url'].'</a>';
}
add_filter( 'mustache_variable_ec_reset_link', 'wppb_ec_replace_reset_link', 10, 4 );


/**
 * Function that filters and returns the users user_email_change_link in the Email Customizer
 *
 * @since v.
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_generate_user_email_change_link( $value, $merge_tag_name, $merge_tag, $extra_data ){
    if ( isset( $extra_data['change_email_url'] ) )
        return $extra_data['change_email_url'];
}
add_filter( 'mustache_variable_ec_user_email_change_link', 'wppb_ec_generate_user_email_change_link', 10, 4 );


/**
 * Function that filters and returns the approve user link in the (Admin) Email Customizer.
 * This allows the admin to approve directly from his email a newly registered user.
 *
 * @since v.2.7.3
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_approve_user_link( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['user_id'] ) ) {

		$approve_url = add_query_arg( 'pbaction', 'approve', wppb_ec_get_approve_url( $extra_data['user_id'] ) );

        if ( !empty($approve_url) )

            return '<a href="' . $approve_url . '" target="_blank">' . $approve_url . '</a>';
    }
}
add_filter( 'mustache_variable_ec_approve_link', 'wppb_ec_replace_approve_user_link', 10, 4 );


/**
 * Function that filters and returns the approve user url in the (Admin) Email Customizer.
 *
 * @since v.2.7.3
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_approve_user_url( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['user_id'] ) ) {

        $approve_url = add_query_arg( 'pbaction', 'approve', wppb_ec_get_approve_url( $extra_data['user_id'] ) );

        if ( !empty( $approve_url ) )
            return $approve_url;

    }
}
add_filter( 'mustache_variable_ec_approve_url', 'wppb_ec_replace_approve_user_url', 10, 4 );

/**
 * Function that filters and returns the approve user link in the (Admin) Email Customizer.
 * This allows the admin to approve directly from his email a newly registered user.
 *
 * @since v.3.4.7
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_unapprove_user_link( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['user_id'] ) ) {

        $approve_url = add_query_arg( 'pbaction', 'unapprove', wppb_ec_get_approve_url( $extra_data['user_id'] ) );

        if ( !empty($approve_url) )

            return '<a href="' . $approve_url . '" target="_blank">' . $approve_url . '</a>';
    }
}
add_filter( 'mustache_variable_ec_unapprove_link', 'wppb_ec_replace_unapprove_user_link', 10, 4 );


/**
 * Function that filters and returns the approve user url in the (Admin) Email Customizer.
 *
 * @since v.3.4.7
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_unapprove_user_url( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['user_id'] ) ) {

        $approve_url = add_query_arg( 'pbaction', 'unapprove', wppb_ec_get_approve_url( $extra_data['user_id'] ) );

        if ( !empty( $approve_url ) )
            return $approve_url;

    }
}
add_filter( 'mustache_variable_ec_unapprove_url', 'wppb_ec_replace_unapprove_user_url', 10, 4 );

/**
 * Function that returns the approve user url to include in the admin email
 *
 * @since v.2.7.3
 *
 * @param $user_id The ID of the user that is pending approval
 *
 * @return string Approve URL
 */
function wppb_ec_get_approve_url ( $user_id ){

    $approve_url = '';

    // If the user is "unaproved" the user meta below will contain a (hashed) value that we need to attach to the admin approval email link for validating it
    $approve_url_param = get_user_meta( $user_id, '_wppb_admin_approval_link_param', true );

    if ( !empty( $approve_url_param ) ) {
        $approve_url = esc_url( add_query_arg( 'pbapprove', $approve_url_param, get_site_url() ) );
    }

    return $approve_url;
}


/**
 * Function that filters and returns the approved fields for Edit Profile Approved by Admin.
 *
 * @since v.2.7.3
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_epaa_replace_approved_fields( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['approved_field_names'] ) ) {
        return $extra_data['approved_field_names'];
    }

    return '';
}
add_filter( 'mustache_variable_ec_epaa_approved_fields', 'wppb_epaa_replace_approved_fields', 10, 4 );


/**
 * Function that filters and returns the unapproved fields for Edit Profile Approved by Admin.
 *
 * @since v.2.7.3
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_epaa_replace_unapproved_fields( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['unapproved_field_names'] ) ) {
        return $extra_data['unapproved_field_names'];
    }

    return '';
}
add_filter( 'mustache_variable_ec_epaa_unapproved_fields', 'wppb_epaa_replace_unapproved_fields', 10, 4 );


/**
 * Function that filters and returns the modified fields for Edit Profile Approved by Admin.
 *
 * @since v.2.7.3
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_epaa_replace_modified_fields( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['modified_fields'] ) ) {
        return $extra_data['modified_fields'];
    }

    return '';
}
add_filter( 'mustache_variable_ec_epaa_modified_fields', 'wppb_epaa_replace_modified_fields', 10, 4 );


/**
 * Function that filters and returns the approval URL for Edit Profile Approved by Admin.
 *
 * @since v.2.7.3
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_epaa_replace_approval_url( $value, $merge_tag_name, $merge_tag, $extra_data ){

    if( ! empty ( $extra_data['approval_url'] ) ) {
        return $extra_data['approval_url'];
    }

    return '';
}
add_filter( 'mustache_variable_ec_epaa_approval_url', 'wppb_epaa_replace_approval_url', 10, 4 );


/**
 * Function that filters and returns the users user_meta values in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_user_meta( $value, $merge_tag_name, $merge_tag, $extra_data ){
	if ( isset( $extra_data['email_confirmation_unserialized_data'][$merge_tag_name] ) )
        $value = $extra_data['email_confirmation_unserialized_data'][$merge_tag_name];
    elseif( !empty( $extra_data['user_id'] ) )
        $value = get_user_meta( $extra_data['user_id'], $merge_tag_name, true );

	/* value should be a string, but in case it is not */
	if( is_array( $value ) )
		$value = implode( ' ', $value );

    return apply_filters( 'wppb_ec_filter_user_meta', $value, $merge_tag_name, $extra_data );
}
add_filter( 'mustache_variable_ec_user_meta', 'wppb_ec_replace_user_meta', 10, 4 );


add_filter( 'wppb_ec_filter_user_meta', 'wppb_ec_change_user_meta_by_type', 10, 3 );
function wppb_ec_change_user_meta_by_type( $value, $meta_name, $extra_data = '' ){
    $fields = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields', 'not_found' ), array( 'user_id' => ( empty( $extra_data['user_id'] ) ) ? '' : $extra_data['user_id'], 'meta' => ( empty( $extra_data['email_confirmation_unserialized_data'] ) ) ? '' : $extra_data['email_confirmation_unserialized_data'], 'context' => 'mustache_variable' ) );
    if( !empty( $fields ) ) {
        foreach ($fields as $field) {
            if ($field['meta-name'] == $meta_name && ($field['field'] == 'Avatar' || $field['field'] == 'Upload' ) ) {
                if( is_numeric( $value ) )
                    $value = wp_get_attachment_url( $value );
            }
        }
    }
    return $value;
}

/**
 * Function that filters and returns the users user_meta labels in the Email Customizer
 *
 * @since v.2.0
 *
 * @param string $value
 * @param string $merge_tag_name
 * @param string $merge_tag
 * @param array $extra_data
 *
 * @return string
 */
function wppb_ec_replace_user_meta_labels( $value, $merge_tag_name, $merge_tag, $extra_data ){
    /* we remove _labels from end so we get the meta name */
    $merge_tag_name = preg_replace( '/_labels$/', '', $merge_tag_name, 1 );

    if ( isset( $extra_data['email_confirmation_unserialized_data'][$merge_tag_name] ) )
        $value = $extra_data['email_confirmation_unserialized_data'][$merge_tag_name];
    if( !empty( $extra_data['user_id'] ) )
        $value = get_user_meta( $extra_data['user_id'], $merge_tag_name, true );

    /* get label from value */
    /* get manage fields */
	$fields = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields', 'not_found' ), array( 'user_id' => ( empty( $extra_data['user_id'] ) ) ? '' : $extra_data['user_id'], 'meta' => ( empty( $extra_data['email_confirmation_unserialized_data'] ) ) ? '' : $extra_data['email_confirmation_unserialized_data'], 'context' => 'mustache_variable' ) );
    if( !empty( $fields ) ) {
        foreach ($fields as $field) {
            if( $field['meta-name'] == $merge_tag_name ){
				if( $field['field'] == 'Select (Country)' ){
					if( function_exists( 'wppb_country_select_options' ) ) {
						$all_countries = wppb_country_select_options('email_customizer');
						if( !empty( $all_countries[$value] ) )
							return $all_countries[$value];
						else
							return $value;
					}
					else{
						return $value;
					}
				}
				else if( $field['field'] == 'Select (Currency)' ){
					if( function_exists( 'wppb_get_currencies' ) ) {
						$all_currencies = wppb_get_currencies('email_customizer');
						if( !empty( $all_currencies[$value] ) )
							return $all_currencies[$value];
						else
							return $value;
					}
					else{
						return $value;
					}
				}
				else {
					/* get label corresponding to value. the values and labels in the backend settings are comma separated so we assume that as well here ? */
					$saved_values = array_map('trim', explode(',', $value));
					$field['options'] = array_map('trim', explode(',', $field['options']));
					$field['labels'] = array_map('trim', explode(',', $field['labels']));
					/* get the position for each value */
					$key_array = array();
					if (!empty($field['options'])) {
						foreach ($field['options'] as $key => $option) {
							if (in_array($option, $saved_values))
								$key_array[] = $key;
						}
					}

					$show_values = array();
					if (!empty($key_array)) {
						foreach ($key_array as $key) {
							if (!empty($field['labels'][$key]))
								$show_values[] = $field['labels'][$key];
							else
								$show_values[] = $field['options'][$key];
						}
					}

					return implode(',', $show_values);
				}
            }
        }
    }
}
add_filter( 'mustache_variable_ec_user_meta_labels', 'wppb_ec_replace_user_meta_labels', 10, 4 );

// function that filters the From email address
function wppb_website_email($sender_email){
    $wppb_addonOptions = get_option( 'wppb_module_settings' );

    if ( ( $wppb_addonOptions['wppb_emailCustomizer'] == 'show' ) || ( isset( $wppb_addonOptions['wppb_emailCustomizerAdmin'] ) && $wppb_addonOptions['wppb_emailCustomizerAdmin'] == 'show' ) ){
        $reply_to_email = get_option( 'wppb_emailc_common_settings_from_reply_to_email', 'not_found' );
        if ( $reply_to_email != 'not_found' ) {
            $reply_to_email = str_replace('{{reply_to}}', get_bloginfo('admin_email'), $reply_to_email );
            if( is_email( $reply_to_email ) )
                $sender_email = $reply_to_email;
        }
    }

    return $sender_email;
}

// function that filters the From name
function wppb_website_name($site_name){
    $wppb_addonOptions = get_option( 'wppb_module_settings' );

    if ( ( $wppb_addonOptions['wppb_emailCustomizer'] == 'show' ) || ( isset( $wppb_addonOptions['wppb_emailCustomizerAdmin'] ) && $wppb_addonOptions['wppb_emailCustomizerAdmin'] == 'show' ) ){
        $email_from_name = get_option( 'wppb_emailc_common_settings_from_name', 'not_found' );
        if ( $email_from_name != 'not_found' ) {
            $email_from_name = str_replace('{{site_name}}', get_bloginfo('name'), $email_from_name );
            $site_name = $email_from_name;
        }
    }

    return html_entity_decode( htmlspecialchars_decode( $site_name, ENT_QUOTES ), ENT_QUOTES );
}

// Function that changes email headers only when they are needed.
function wppb_change_email_from_headers(){
	add_filter('wp_mail_from_name','wppb_website_name', 20, 1);
	add_filter('wp_mail_from','wppb_website_email', 20, 1);
}

add_filter( 'wppb_send_email', 'wppb_disable_emails', 20, 5 );
function wppb_disable_emails( $send_email, $to, $subject, $message, $context ) {
	$actions = array(
		'email_user_account_info'     => 'wppb_user_emailc_default_registration_email_enabled',
		'email_user_activate'         => 'wppb_user_emailc_registr_w_email_confirm_email_enabled',
		'email_user_need_approval'    => 'wppb_user_emailc_registration_with_admin_approval_email_enabled',
		'email_user_approved'         => 'wppb_user_emailc_admin_approval_notif_approved_email_enabled',
		'email_user_unapproved'       => 'wppb_user_emailc_admin_approval_notif_unapproved_email_enabled',
		'email_user_recover'          => 'wppb_user_emailc_reset_email_enabled',
		'email_user_recover_success'  => 'wppb_user_emailc_reset_success_email_enabled',
        'email_user_update_email'     => 'wppb_user_emailc_change_email_address_request_enabled',
        'wppb_epaa_user_email'        => 'wppb_user_emailc_epaa_notification_enabled',
		'email_admin_new_subscriber'  => 'wppb_admin_emailc_default_registration_email_enabled',
		'email_admin_approve'         => 'wppb_admin_emailc_registration_with_admin_approval_email_enabled',
		'email_admin_recover_success' => 'wppb_admin_emailc_user_password_reset_email_enabled',
        'wppb_epaa_admin_email'       => 'wppb_admin_emailc_epaa_notification_enabled',
	);

	if ( !empty( $actions[$context] ) )
		$option_name = $actions[$context];
	else
		return $send_email;

	$option = get_option( $option_name );

	if ( $option == 'off' )
		return false;

	return $send_email;
}

if ( get_option( 'wppb_user_emailc_change_email_address_enabled' ) == 'off' )
	add_filter( 'send_email_change_email', '__return_false' );
