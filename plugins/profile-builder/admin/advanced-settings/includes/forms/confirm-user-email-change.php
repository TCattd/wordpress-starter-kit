<?php

if ( !current_user_can( 'manage_options' ) )
    add_filter( 'wppb_email_confirmation_on_user_email_change', '__return_true' );


// get transient check key
function wppb_toolbox_pending_email_change_request_transient_key() {
    $current_user = wp_get_current_user();
    $pending_change_request_data = get_user_meta($current_user->ID, '_wppb_pending_email_change_request', true);

    if (isset( $pending_change_request_data['new_email'] ) )
        $pending_new_email_address = $pending_change_request_data['new_email'];
    else $pending_new_email_address = '';

    $transient_check_key = Wordpress_Creation_Kit_PB::wck_generate_slug(sanitize_email($pending_new_email_address));

    return $transient_check_key;
}
add_filter('wppb_pending_email_change_transient_key','wppb_toolbox_pending_email_change_request_transient_key');


// get new email address
function wppb_toolbox_pending_new_email_address() {
    $current_user = wp_get_current_user();
    $pending_change_request_data = get_user_meta($current_user->ID, '_wppb_pending_email_change_request', true);

    if ( isset( $pending_change_request_data['new_email'] ))
        $pending_new_email_address = $pending_change_request_data['new_email'];
    else $pending_new_email_address = '';

    return $pending_new_email_address;
}
add_filter('wppb_new_email_address','wppb_toolbox_pending_new_email_address');


// set email input status
function wppb_toolbox_check_pending_email() {
    $current_user = wp_get_current_user();
    $unapproved_email_address = wppb_user_meta_exists( $current_user->ID, '_wppb_epaa_email' );

    if ( empty( $unapproved_email_address ) && !isset( $_GET['wppb_epaa_review_users'] )) {
        $transient_check_key = apply_filters('wppb_pending_email_change_transient_key', '');

        if ( !empty( $transient_check_key ) )
            $transient_check = get_transient( 'wppb_pending_email_change_request_exists_' . $transient_check_key );
        else $transient_check = false;

        if ( $transient_check !== false )
            $input_status = 'disabled';
        else $input_status = 'enabled';

    } else $input_status = 'needs_approval';

    return $input_status;
}
add_filter('wppb_set_input_status','wppb_toolbox_check_pending_email');


// check if approval is needed
function wppb_toolbox_check_approval() {
    $approval_needed = 'no';
    $all_fields = get_option( 'wppb_manage_fields' );

    foreach ($all_fields as $field) {
        if ($field['field'] === 'Default - E-mail') {
            if ( isset( $field['edit-profile-approved-by-admin'] ) && $field['edit-profile-approved-by-admin'] === 'yes') {
                $approval_needed = 'yes';
            }
        }
    }

    return $approval_needed;

}
add_filter('wppb_check_approval_activation','wppb_toolbox_check_approval');


// handle email change request notification (send if admin approval is not active)
function wppb_toolbox_handle_email_change_request( $input_email ) {
    if( apply_filters( 'wppb_email_confirmation_on_user_email_change', false ) && is_user_logged_in() && !isset( $_GET['wppb_epaa_review_users'] )) {
        $current_user = wp_get_current_user();
        $user_current_email = $current_user->user_email;

        // check if the email address entered into the input is different from the current email address
        if ( $input_email != $user_current_email  ) {

            $transient_check_key = Wordpress_Creation_Kit_PB::wck_generate_slug( sanitize_email( $input_email ));
            $transient_check = get_transient( 'wppb_pending_email_change_request_exists_'.$transient_check_key );
            $needs_approval = apply_filters( 'wppb_check_approval_activation','' );

            if ( !wppb_user_meta_exists( $current_user->ID, '_wppb_email_change_request_nonce' )) {
                $pending_request_nonce = wp_create_nonce('wppb_email_change_action_nonce');
                update_user_meta($current_user->ID, '_wppb_email_change_request_nonce', $pending_request_nonce);
            }

            // check if a pending change request exists
            if ( $transient_check === false && $needs_approval === 'no') {

                // send confirmation email to new email address
                do_action('wppb_send_mail_address_change_request', $input_email);

                $input_email = $user_current_email;

            } elseif ( $needs_approval === 'yes' ) {
                update_user_meta( $current_user->ID, '_wppb_pending_email_change_request_page_url', wppb_curpageurl() );
            }
        }
    }
    return $input_email;
}
add_filter( 'wppb_before_processing_email_from_forms', 'wppb_toolbox_handle_email_change_request' );


// edit content and send email change request notification
function wppb_toolbox_send_change_request_mail( $new_email_address ) {

    $needs_approval = apply_filters( 'wppb_check_approval_activation','' );

    if ( $needs_approval === 'no') {
        $new_email = $new_email_address;
        $current_user = wp_get_current_user();
        $current_url = wppb_curpageurl();
    }
    elseif ( $needs_approval === 'yes' ) {
        $new_email = $new_email_address['email'];
        $current_user = get_user_by( 'id',$new_email_address['user_id'] );
        $current_url = get_user_meta( $new_email_address['user_id'], '_wppb_pending_email_change_request_page_url', true );
    }


    $hash           = md5( $new_email );
    $new_user_email = array(
        'hash'     => $hash,
        'new_email' => $new_email,
    );

    update_user_meta( $current_user->ID, '_wppb_pending_email_change_request', $new_user_email );

    $pending_request_nonce = get_user_meta($current_user->ID,'_wppb_email_change_request_nonce',true);

    $arr_params = array( 'wppb_email_change_action' => 'update_email_address' ,'wppb_new_user_email' => $hash, '_wpnonce' => $pending_request_nonce );

    $confirmation_url = add_query_arg($arr_params, $current_url);

    $change_request_subject = sprintf( __('Email address change request for %s', 'profile-builder'), $current_user->user_email );
    $change_request_subject = apply_filters( 'wppb_user_email_change_request_notification_subject', $change_request_subject, $current_user );

    $change_request_message = sprintf( __('Someone requested to change the email address for your account.<br/>If this was a mistake, just ignore this email and nothing will happen.<br/>To update your account email address to the one requested (%1$s), visit the following link: %2$s', 'profile-builder'),  $new_user_email['new_email'],'<a href="'.$confirmation_url.'">Change email address!</a>' );
    $change_request_message = apply_filters( 'wppb_user_email_change_request_notification_content', $change_request_message, $current_user, $confirmation_url );

    wppb_mail($new_email,$change_request_subject,$change_request_message);

    // set transient
    $transient_key = Wordpress_Creation_Kit_PB::wck_generate_slug( sanitize_email( $new_email ));
    set_transient('wppb_pending_email_change_request_exists_' . $transient_key, true, WEEK_IN_SECONDS);
}
add_action('wppb_send_mail_address_change_request','wppb_toolbox_send_change_request_mail');


// update user email | delete transient | delete change request user meta
function wppb_toolbox_change_user_email_address() {

    if( !isset( $_GET['wppb_new_user_email'] ) )
        return;
        
    $current_user = wp_get_current_user();
    $new_email = get_user_meta( $current_user->ID, '_wppb_pending_email_change_request', true );

    if ( $new_email && hash_equals( $new_email['hash'], $_GET['wppb_new_user_email'] ) ) {
        $transient_check_key = apply_filters('wppb_pending_email_change_transient_key', '');
        $update_user_args = array(
            'ID'         => $current_user->ID,
            'user_email' => esc_html( trim( $new_email['new_email'] ) )
        );

        wp_update_user( $update_user_args );

        $remove_change_request_metas = array('_wppb_pending_email_change_request','_wppb_pending_email_change_request_page_url','_wppb_email_change_request_nonce');
        foreach ( $remove_change_request_metas as $meta ) {
            delete_user_meta( $current_user->ID, $meta );
        }

        delete_transient('wppb_pending_email_change_request_exists_' . $transient_check_key );
    }

}


// cancel pending request (delete transient | delete change request user meta)
function wppb_toolbox_cancel_pending_user_email_change_request() {
    $current_user = wp_get_current_user();
    $transient_check_key = apply_filters('wppb_pending_email_change_transient_key', '');

    $unapproved_email_address = wppb_user_meta_exists($current_user->ID, '_wppb_epaa_email' );
    if (!empty($unapproved_email_address)) {
        delete_user_meta( $current_user->ID, '_wppb_epaa_email' );
    }

    $remove_change_request_metas = array( '_wppb_pending_email_change_request' , '_wppb_pending_email_change_request_page_url' , '_wppb_email_change_request_nonce' );
    foreach ( $remove_change_request_metas as $meta ) {
        delete_user_meta( $current_user->ID, $meta );
    }

    delete_transient('wppb_pending_email_change_request_exists_' . $transient_check_key );
}


function wppb_toolbox_handle_email_change() {
    if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce(sanitize_text_field( $_GET['_wpnonce'] ), 'wppb_email_change_action_nonce' ) && isset( $_GET['wppb_email_change_action'] ) ) {
        if ( $_GET['wppb_email_change_action'] == 'update_email_address' && isset( $_GET['wppb_new_user_email'] ) )
            wppb_toolbox_change_user_email_address();
        else if ( $_GET['wppb_email_change_action'] == 'cancel_pending_email_address_change' )
            wppb_toolbox_cancel_pending_user_email_change_request();
    }
}
add_action('init', 'wppb_toolbox_handle_email_change');


// if there is an email change request we don't update the email address until the confirmation link, sent to user by email, is clicked
function wppb_toolbox_remove_email_from_userdata_update( $userdata ) {
    $transient_check_key = apply_filters('wppb_pending_email_change_transient_key', '');
    $transient_check = get_transient('wppb_pending_email_change_request_exists_' . $transient_check_key);

    if ($transient_check !== false && ( !isset( $_POST['action'] ) || $_POST['action'] != 'register' ) )
        unset( $userdata['user_email'] );

    return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_toolbox_remove_email_from_userdata_update', 20, 3 );