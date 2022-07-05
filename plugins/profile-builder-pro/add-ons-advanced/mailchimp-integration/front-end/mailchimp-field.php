<?php
/* handle field output */
function wppb_in_mailchimp_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){

    if ( $field['field'] == 'MailChimp Subscribe' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_mailchimp_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'] ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'] );

        $input_value = ( !empty( $field['mailchimp-lists'] ) ? $field['mailchimp-lists']  : '' );

        $settings  = get_option('wppb_mci_settings');
        $key_valid = get_option('wppb_mailchimp_api_key_validated', false);

        if ( isset( $settings['api_key'] ) && !empty($settings['api_key']) && $key_valid != false ) {

            $checked = '';
            $gdpr_checked = '';

            // If we're on edit profile check if the user e-mail is subscribed in any of the MailChimp lists
            // associated with this field
            if( $form_location != 'register' ) {

                $api = new WPPB_IN_MailChimp( $settings['api_key'] );

                $user_data = get_userdata($user_id);

                if( isset( $user_data ) && !empty( $user_data ) ) {
                    $mc_list_id = ( !empty( $field['mailchimp-lists'] ) ? trim( $field['mailchimp-lists'] ) : '' );

                    if( !empty( $mc_list_id ) ) {
                        // Get member
                        $subscriber_hash = $api->subscriberHash($user_data->data->user_email);

                        $response = $api->get( "lists/$mc_list_id/members/$subscriber_hash" );

                        if( $response['status'] == 'subscribed' ){
                            $checked = 'checked="checked"';
                        }

                        if( !empty( $response['marketing_permissions'] ) ){
                            $gdpr_checked = 'checked="checked"';
                            foreach( $response['marketing_permissions'] as $marketing_permission ){
                                //all must be enabled
                                if( !$marketing_permission['enabled'] ){
                                    $gdpr_checked = '';
                                    break;
                                }
                            }

                        }
                        
                    }
                }
            }

            // Check the checkbox if there is a value
            if( $form_location == 'register' && ( (isset( $request_data['custom_field_mailchimp_subscribe_' . $field['id']] ) && !empty( $request_data['custom_field_mailchimp_subscribe_' . $field['id']] )) || !empty( $field['mailchimp-default-checked'] ) ) )
                $checked = 'checked="checked"';

            // Display checkbox on register / edit profile forms
            if( $form_location != 'back_end' ) {

                $output = '<label for="custom_field_mailchimp_subscribe_' . $field['id'] . '">';

                $output .= '<input name="custom_field_mailchimp_subscribe_' . $field['id'] . '" id="custom_field_mailchimp_subscribe_' . $field['id'] . '" class="extra_field_mailchimp" type="checkbox" value="' . $input_value . '" ' . $checked . ' />';

                $output .= $item_title . '</label>';

                if( !empty( $item_description ) )
                    $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                $list_id = ( !empty( $field['mailchimp-lists'] ) ? trim( $field['mailchimp-lists'] ) : '' );
                if( isset( $settings['lists'][$list_id]['gdpr'] ) && $settings['lists'][$list_id]['gdpr'] == 'on' ){

                    // Check the gdpr checkbox if there is a value
                    if( $form_location == 'register' && ( (isset( $request_data['custom_field_mailchimp_gdpr_' . $field['id']] ) && !empty( $request_data['custom_field_mailchimp_gdpr_' . $field['id']] )) ) )
                        $gdpr_checked = 'checked="checked"';

                    $output .= '<label for="custom_field_mailchimp_gdpr_' . esc_attr( $field['id'] ) . '">';
                    $output .= '<input name="custom_field_mailchimp_gdpr_' . esc_attr( $field['id'] ) . '" id="custom_field_mailchimp_gdpr_' . esc_attr( $field['id'] ) . '" class="extra_field_mailchimp" type="checkbox" value="true" ' . $gdpr_checked . ' />';
                    $output .= esc_html( apply_filters( 'wppb_mci_gdpr_text', __(  'By checking this box I consent to the use of my information provided for email marketing purposes.', 'profile-builder' ) ) ) . '</label>';
                }

            // Display checkbox on back end edit profile forms
            } else {

                $output = '<table class="form-table">';

                    $output .= '<tr>';

                        $output .= '<th>';
                            $output .= '<label>' . esc_attr( $field['field'] ) . '</label>';
                        $output .= '</th>';

                        $output .= '<td>';

                            $output .= '<label for="custom_field_mailchimp_subscribe_' . $field['id'] . '">';
                            $output .= '<input name="custom_field_mailchimp_subscribe_' . $field['id'] . '" id="custom_field_mailchimp_subscribe_' . $field['id'] . '" class="extra_field_mailchimp" type="checkbox" value="' . $input_value . '" ' . $checked . ' />';
                            $output .= $item_title . '</label>';

                            if( !empty( $item_description ) )
                                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                            $list_id = ( !empty( $field['mailchimp-lists'] ) ? trim( $field['mailchimp-lists'] ) : '' );
                            if( isset( $settings['lists'][$list_id]['gdpr'] ) && $settings['lists'][$list_id]['gdpr'] == 'on' ){
                                $output .= '<label for="custom_field_mailchimp_gdpr_' . esc_attr( $field['id'] ) . '">';
                                $output .= '<input name="custom_field_mailchimp_gdpr_' . esc_attr( $field['id'] ) . '" id="custom_field_mailchimp_gdpr_' . esc_attr( $field['id'] ) . '" class="extra_field_mailchimp" type="checkbox" value="true" ' . $gdpr_checked . ' />';
                                $output .= esc_html( apply_filters( 'wppb_mci_gdpr_text', __( 'By checking this box I consent to the use of my information provided for email marketing purposes.', 'profile-builder' ) ) ) . '</label>';
                            }

                        $output .= '</td>';
                    $output .= '</tr>';

                $output .= '</table>';

            }


        }

        return apply_filters( 'wppb_'.$form_location.'_mailchimp_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_mailchimp-subscribe', 'wppb_in_mailchimp_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_mailchimp-subscribe', 'wppb_in_mailchimp_handler', 10, 6 );


/*
 * Caches the user's e-mail address into a transient upon profile update, if the new e-mail
 * is different from the previous one
 *
 */
function wppb_in_mci_cache_user_email( $user_id, $old_user_data ) {

    if( (int)$user_id === 0 )
        return;

    $user_data = get_userdata( $user_id );

    if( $user_data->data->user_email != $old_user_data->data->user_email )
        set_transient( 'wppb_mci_user_email_' . $user_id, $old_user_data->data->user_email, 12 * HOUR_IN_SECONDS );

}
add_action( 'profile_update', 'wppb_in_mci_cache_user_email', 10, 2 );


/*
 * Remove the cached user e-mail after the profile has been edited successfully
 *
 */
function wppb_in_mci_clear_cache_user_email( $request_data, $form_name, $user_id ) {

    if( (int)$user_id === 0 )
        return;

    delete_transient( 'wppb_mci_user_email_' . $user_id );

}
add_action( 'wppb_edit_profile_success', 'wppb_in_mci_clear_cache_user_email', 10, 3 );


/*
 * Handle field save
 *
 */
function wppb_in_save_mailchimp_value( $field, $user_id, $request_data, $form_location ){

    if( $field['field'] == 'MailChimp Subscribe' ) {

        // Get MailChimp settings
        $settings  = get_option('wppb_mci_settings');
        $key_valid = get_option('wppb_mailchimp_api_key_validated', false);


        // Get old user e-mail
        $old_user_email = get_transient( 'wppb_mci_user_email_' . $user_id );

        // Get value from the subscribe checkbox
        if( !empty( $request_data['custom_field_mailchimp_subscribe_' . $field['id']] ) ) {

            // Grab the list id and sanitize it a bit
            $mc_list_id = trim( $request_data[ 'custom_field_mailchimp_subscribe_' . $field['id'] ] );

            if( isset( $settings['api_key'] ) && !empty( $settings['api_key'] ) && $key_valid != false && !empty($mc_list_id) ) {

                // Subscribe user on the register page
                if( empty( $form_location ) || $form_location == 'register' ) {
	                wppb_in_mci_api_unsubscribe( $settings['api_key'], array( 'id' => $mc_list_id, 'email' => wppb_in_mci_api_get_args_email( $request_data, $user_id, $form_location ) ) );
                    $response = wppb_in_mci_api_subscribe($settings['api_key'], $mc_list_id, wppb_in_mci_api_get_args('subscribe', $request_data, $mc_list_id, $user_id, $form_location));
                    if ( !empty( $request_data['custom_field_mailchimp_gdpr_' . $field['id']] ) && $response['id'])
                        wppb_in_mci_gdpr_opt_in($settings['api_key'], $mc_list_id, $response['id']);
                }

                // Handle subscription / updating member on edit profile
                else {

                    if( $old_user_email !== false )
                        $is_subscribed = wppb_in_mci_api_member_is_subscribed( $settings['api_key'], array( 'id' => $mc_list_id, 'emails' => array( array( 'email' => $old_user_email ) ) ) );

                    else {

                        // Because the e-mail field may not be present in the form we're going to default to
                        // the user_data
                        $user_data = get_userdata($user_id);
                        $is_subscribed = wppb_in_mci_api_member_is_subscribed( $settings['api_key'], array( 'id' => $mc_list_id, 'emails' => array( array( 'email' => $user_data->data->user_email ) ) ) );
                    }

                    // Update member data if the user is subscribed to the list
                    if( $is_subscribed ) {

                        if( $old_user_email !== false ) {

                            // Because update doesn't work if the e-mail was present in MailChimp's db
                            // we'll unsubscribe the old e-mail address and add the new one
                            wppb_in_mci_api_unsubscribe( $settings['api_key'], array( 'id' => $mc_list_id, 'email' => $old_user_email ) );
                            $response = wppb_in_mci_api_subscribe( $settings['api_key'], $mc_list_id, wppb_in_mci_api_get_args( 'update-member', $request_data, $mc_list_id, $user_id, $form_location ) );
                            if ( !empty( $request_data['custom_field_mailchimp_gdpr_' . $field['id']] ) && $response['id'])
                                wppb_in_mci_gdpr_opt_in($settings['api_key'], $mc_list_id, $response['id']);
                            else
                                wppb_in_mci_gdpr_opt_out($settings['api_key'], $mc_list_id, $response['id']);

                        } else {
                            $response = wppb_in_mci_api_update_member($settings['api_key'], $mc_list_id, wppb_in_mci_api_get_args('update-member', $request_data, $mc_list_id, $user_id, $form_location));
                            if ( !empty( $request_data['custom_field_mailchimp_gdpr_' . $field['id']] ) && $response['id'])
                                wppb_in_mci_gdpr_opt_in($settings['api_key'], $mc_list_id, $response['id']);
                            else
                                wppb_in_mci_gdpr_opt_out($settings['api_key'], $mc_list_id, $response['id']);
                        }

                    }

                    // Subscribe the member if he's not subscribed
                    else {
                        $response = wppb_in_mci_api_subscribe($settings['api_key'], $mc_list_id, wppb_in_mci_api_get_args('subscribe', $request_data, $mc_list_id, $user_id, $form_location));
                        if ( !empty( $request_data['custom_field_mailchimp_gdpr_' . $field['id']] ) && ( is_array($response) && $response['id']) ) {
                            wppb_in_mci_gdpr_opt_in($settings['api_key'], $mc_list_id, $response['id']);
                        }
                    }


                }
                
            }

        } elseif( isset( $field['mailchimp-lists'] ) && !empty( $field['mailchimp-lists'] ) && $form_location != 'register' ) {

            // As we have the same situation for both when the field is in the form, but not checked, and when
            // it is not present, we want to unsubscribe the user only when the field is present
            if( isset( $field['mailchimp-hide-field'] ) && $field['mailchimp-hide-field'] == 'yes' )
                return;


            if( isset( $settings['api_key'] ) && !empty( $settings['api_key'] ) && $key_valid != false ) {

                // Get userdata
                $user_data = get_userdata($user_id);

                if (isset($user_data) && !empty($user_data)) {

                    $mc_list_id        = trim( $field['mailchimp-lists'] );
                    $unsubscribe_email = ( $old_user_email !== false ? $old_user_email : $user_data->data->user_email );

                    wppb_in_mci_api_unsubscribe( $settings['api_key'], array( 'id' => $mc_list_id, 'email' => $unsubscribe_email ) );

                }
            }
        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_in_save_mailchimp_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_in_save_mailchimp_value', 10, 4 );


/*
 * For e-mail confirmation we need to store the list id until the
 * user confirms the register
 *
 */
function wppb_in_add_to_user_signup_form_meta_mailchimp( $meta, $global_request ) {
    $wppb_manage_fields = get_option( 'wppb_manage_fields', array() );

    if( !empty( $wppb_manage_fields ) ) {
        foreach( $wppb_manage_fields as $field ) {
            if( $field['field'] == 'MailChimp Subscribe' && isset( $global_request[ 'custom_field_mailchimp_subscribe_' . $field['id'] ] ) && !empty( $global_request[ 'custom_field_mailchimp_subscribe_' . $field['id'] ] ) ) {
                $meta['custom_field_mailchimp_subscribe_' . $field['id'] ] = trim($global_request[ 'custom_field_mailchimp_subscribe_' . $field['id'] ]);
            }
            if( $field['field'] == 'MailChimp Subscribe' && isset( $global_request[ 'custom_field_mailchimp_gdpr_' . $field['id'] ] ) && !empty( $global_request[ 'custom_field_mailchimp_gdpr_' . $field['id'] ] ) ) {
                $meta['custom_field_mailchimp_gdpr_' . $field['id'] ] = trim($global_request[ 'custom_field_mailchimp_gdpr_' . $field['id'] ]);
            }
        }
    }

    return $meta;

}
add_filter( 'wppb_add_to_user_signup_form_meta', 'wppb_in_add_to_user_signup_form_meta_mailchimp', 10 , 2 );


/*
 * Subscribe user to the list when the user becomes active
 *
 */
function wppb_in_activate_user_subscribe_mailchimp_list( $user_id, $password, $meta ) {

    // Get all fields in manage fields
    $wppb_manage_fields = get_option( 'wppb_manage_fields', array() );

    if( !empty( $wppb_manage_fields ) ) {
        foreach( $wppb_manage_fields as $field ) {

            if( $field['field'] == 'MailChimp Subscribe' && isset( $meta[ 'custom_field_mailchimp_subscribe_' . $field['id'] ] ) && !empty( $meta[ 'custom_field_mailchimp_subscribe_' . $field['id'] ] ) ) {

                $mc_list_id = trim( $meta[ 'custom_field_mailchimp_subscribe_' . $field['id'] ] );

                // Get MailChimp settings
                $settings  = get_option('wppb_mci_settings');
                $key_valid = get_option('wppb_mailchimp_api_key_validated', false);

                if( isset( $settings['api_key'] ) && !empty( $settings['api_key'] ) && $key_valid != false && !empty($mc_list_id) ) {

                    // Get the args
                    $args = wppb_in_mci_api_get_args( 'subscribe', $meta, $mc_list_id, $user_id );

                    // Subscribe new
                    $response = wppb_in_mci_api_subscribe( $settings['api_key'], $mc_list_id, $args );
                    if ( !empty( $meta['custom_field_mailchimp_gdpr_' . $field['id']] ) && $response['id'])
                        wppb_in_mci_gdpr_opt_in($settings['api_key'], $mc_list_id, $response['id']);
                }

            }

        }
    }
}
add_action( 'wppb_activate_user', 'wppb_in_activate_user_subscribe_mailchimp_list', 10, 3 );