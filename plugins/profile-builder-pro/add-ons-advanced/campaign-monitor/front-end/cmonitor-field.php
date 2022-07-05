<?php
/* handle field output */
function wppb_in_campaign_monitor_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){

    if ( $field['field'] == 'Campaign Monitor Subscribe' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_campaign_monitor_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'] ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'] );

        $input_value = ( !empty( $field['campaign-monitor-lists'] ) ? $field['campaign-monitor-lists']  : '' );

        $wppb_cmi_settings = get_option('wppb_cmi_settings');
        $wppb_cmi_api_key_validated = get_option('wppb_cmi_api_key_validated', false);

        if ( $form_location != 'back_end' && isset( $wppb_cmi_settings['api_key'] ) && !empty($wppb_cmi_settings['api_key']) && $wppb_cmi_api_key_validated != false ) {

            $checked = '';

            // If we're on edit profile check if the user e-mail is subscribed in the Campaign Monitor list
            // associated with this field
            if( $form_location == 'edit_profile' ) {

                $user_data = get_userdata($user_id);

                if( isset( $user_data ) && !empty( $user_data ) ) {
                    $wppb_cmi_list_id = ( !empty( $field['campaign-monitor-lists'] ) ? trim( esc_attr( $field['campaign-monitor-lists'] ) ) : '' );

                    if( !empty( $wppb_cmi_list_id ) ) {

                        // Check to see if user is subscribed to this list
                        $user_subscribe_status = get_user_meta( $user_id, 'wppb_cmi_subscribe_status', true );

                        // If user meta is empty update it with the results from Campaign Monitor
                        if( empty( $user_subscribe_status ) ) {

                            // Connect to the API
                            $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
                            $wrap = new WPPB_IN_CS_REST_Subscribers( $wppb_cmi_list_id, $auth );

                            // Get results
                            $result = $wrap->get( $user_data->data->user_email );

                            // Update user meta
                            if( $result->was_successful() && $result->response->State == 'Active' )
                                update_user_meta( $user_id, 'wppb_cmi_subscribe_status', 'active' );
                            else
                                update_user_meta( $user_id, 'wppb_cmi_subscribe_status', 'inactive' );


                            // Retreive the new user meta
                            $user_subscribe_status = get_user_meta( $user_id, 'wppb_cmi_subscribe_status', true );

                        }

                        // If e-mail exists and is active check the checkbox
                        if( $user_subscribe_status == 'active' )
                            $checked = 'checked="checked"';

                    }

                }
            }


            if( $form_location == 'register' && isset( $request_data['custom_field_campaign_monitor_subscribe_' . $field['id']] ) && !empty( $request_data['custom_field_campaign_monitor_subscribe_' . $field['id']] ) )
                $checked = 'checked="checked"';

            // Add a hidden field if the e-mail is an active subscriber
            // We'll use this to make checks before subscribing/unsubscribing
            if( $form_location == 'edit_profile' && !empty( $checked ) )
                $output = '<input type="hidden" name="custom_field_campaign_monitor_subscribe_is_active_' . $field['id'] . '" value="1" />';

            $output .= '<label for="custom_field_campaign_monitor_subscribe_' . $field['id'] . '">';

            $output .= '<input name="custom_field_campaign_monitor_subscribe_' . $field['id'] . '" id="custom_field_campaign_monitor_subscribe_' . $field['id'] . '" class="extra_field_campaign_monitor" type="checkbox" value="' . $input_value . '" ' . $checked . ' />';

            $output .= $item_title . '</label>';

            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

        }

        return apply_filters( 'wppb_'.$form_location.'_campaign_monitor_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_campaign-monitor-subscribe', 'wppb_in_campaign_monitor_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_campaign-monitor-subscribe', 'wppb_in_campaign_monitor_handler', 10, 6 );


/* handle field save */
function wppb_in_save_campaign_monitor_value( $field, $user_id, $request_data, $form_location ){
	if( $form_location == 'back_end' )
        return;

    if( $field['field'] == 'Campaign Monitor Subscribe' ){

        // Get value from the subscribe checkbox
        if( isset( $request_data['custom_field_campaign_monitor_subscribe_' . $field['id']] ) && !empty( $request_data['custom_field_campaign_monitor_subscribe_' . $field['id']] ) ) {

            // Get list id
            $wppb_cmi_list_id =  trim( esc_attr( $request_data[ 'custom_field_campaign_monitor_subscribe_' . $field['id']  ] ) );

            // Get Campaign Monitor settings
            $wppb_cmi_settings = get_option('wppb_cmi_settings');
            $wppb_cmi_api_key_validated = get_option('wppb_cmi_api_key_validated', false);

            if( isset( $wppb_cmi_settings['api_key'] ) && !empty( $wppb_cmi_settings['api_key'] ) && $wppb_cmi_api_key_validated != false && !empty($wppb_cmi_list_id) ) {

                // Get fields of this list
                $wppb_cmi_list_fields = $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list_id ]['fields'];

                // Set subscriber email
                if( $form_location == 'register' )
                    $subscriber_email = isset( $request_data[ $wppb_cmi_list_fields['email']['request_name'] ] ) ? trim( $request_data[ $wppb_cmi_list_fields['email']['request_name'] ] ) : '';
                elseif( $form_location == 'edit_profile' ) {
                    $user_data = get_userdata($user_id);
                    $subscriber_email = $user_data->data->user_email;
                }

                // Set subscriber name
                $subscriber_name = isset( $request_data[ $wppb_cmi_list_fields['fullname']['request_name'] ] ) ? trim( $request_data[ $wppb_cmi_list_fields['fullname']['request_name'] ] ) : '';

                // Set custom fields
                $subscriber_custom_fields = array();
                foreach( $wppb_cmi_list_fields as $wppb_cmi_field_key => $wppb_cmi_field_data ) {

                    if( $wppb_cmi_field_key != 'email' && $wppb_cmi_field_key != 'fullname' ) {

                        if( isset( $request_data[ $wppb_cmi_field_data['request_name'] ] ) ) {

                            if( is_array( $request_data[ $wppb_cmi_field_data['request_name'] ] ) )
                                $request_field_values = $request_data[ $wppb_cmi_field_data['request_name'] ];
                            else
                                $request_field_values[0] = $request_data[ $wppb_cmi_field_data['request_name'] ];


                            if( !empty( $request_field_values ) ) {
                                foreach( $request_field_values as $request_field_value ) {
                                    $subscriber_custom_fields[] = array(
                                        'Key'   => $wppb_cmi_field_key,
                                        'Value' => $request_field_value
                                    );
                                }
                            }
                        }

                    }

                }

                // Check resubscribe
                $subscriber_resubscribe = isset( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list_id ]['resubscribe'] ) ? true : false;

                // Add subscriber to the list
                $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
                $wrap = new WPPB_IN_CS_REST_Subscribers( $wppb_cmi_list_id, $auth );

				if ( !isset( $request_data[ 'custom_field_campaign_monitor_subscribe_is_active_' . $field['id'] ] ) ){
					$result = $wrap->add(
						array(
							'EmailAddress'  => $subscriber_email,
							'Name'          => $subscriber_name,
							'CustomFields'  => $subscriber_custom_fields,
							'Resubscribe'   => $subscriber_resubscribe
						)
					);

					// Update the user meta
					if( $result->was_successful() )
						update_user_meta( $user_id, 'wppb_cmi_subscribe_status', 'active' );

				}

				if( is_email( $request_data['email']) ){
					$new_subscriber_email = $request_data['email'];
				} else {
					$new_subscriber_email = $subscriber_email;
				}

				// Update CM Profile on edit profile
				if( $form_location == 'edit_profile' && isset( $request_data[ 'custom_field_campaign_monitor_subscribe_is_active_' . $field['id'] ] )){
					$result = $wrap->update(
						$subscriber_email,
						array(
							'EmailAddress'  => $new_subscriber_email,
							'Name'          => $subscriber_name,
							'CustomFields'  => $subscriber_custom_fields,
							'Resubscribe'   => $subscriber_resubscribe
						)
					);
				}
            }
        } elseif( isset( $field['campaign-monitor-lists'] ) && !empty( $field['campaign-monitor-lists'] ) && $form_location == 'edit_profile' && !isset( $request_data['custom_field_campaign_monitor_subscribe_' . $field['id']] ) && isset( $request_data[ 'custom_field_campaign_monitor_subscribe_is_active_' . $field['id'] ] ) ) {

            // Get API settings
            $wppb_cmi_settings = get_option('wppb_cmi_settings');
            $wppb_cmi_api_key_validated = get_option('wppb_cmi_api_key_validated', false);

            if( isset( $wppb_cmi_settings['api_key'] ) && !empty( $wppb_cmi_settings['api_key'] ) && $wppb_cmi_api_key_validated != false ) {

                // Get userdata
                $user_data = get_userdata($user_id);

                if (isset($user_data) && !empty($user_data)) {

                    $wppb_cmi_list_id = trim( $field['campaign-monitor-lists']);

                    // Unsubscribe e-mail
                    $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
                    $wrap = new WPPB_IN_CS_REST_Subscribers( $wppb_cmi_list_id, $auth );

                    $result = $wrap->unsubscribe( $user_data->data->user_email );

                    // Update the user meta
                    if( $result->was_successful() )
                        update_user_meta( $user_id, 'wppb_cmi_subscribe_status', 'inactive' );

                }
            }
        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_in_save_campaign_monitor_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_in_save_campaign_monitor_value', 10, 4 );