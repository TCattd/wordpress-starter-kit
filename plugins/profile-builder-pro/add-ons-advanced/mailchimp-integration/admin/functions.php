<?php

    /*
     * Function that sets the list settings of the settings option
     *
     * @since v.1.0.0
     *
     * @param string $wppb_mci_api_key
     *
     * @return array
     *
     */
    function wppb_in_mci_api_get_lists_settings( $api_key ) {

        $wppb_mci_lists = array();

        $api = new WPPB_IN_MailChimp( $api_key );

        $api_lists['lists'] = array();
        $api_lists = $api->get('lists', [ 'count' => 100, ] );

        // If we have list data go ahead and populate the lists array
        if( !empty( $api_lists['lists'] ) ) {

            // Go through each list
            foreach( $api_lists['lists'] as $mci_list ) {

                // Set the name of the list
                $wppb_mci_lists[ $mci_list['id'] ]['name'] = $mci_list['name'];

                // Get merge vars of the list
                $api_list_merge_vars = wppb_in_mci_api_get_list_merge_vars( $api_key, $mci_list['id'] );

                // Go through each merge var
                foreach( $api_list_merge_vars as $merge_var ) {

                    $wppb_mci_lists[ $mci_list['id'] ]['merge_vars'][ $merge_var['tag'] ] = '';

                    // Set the default e-mail
                    if( $merge_var['tag'] == 'EMAIL' ) {
                        $wppb_mci_lists[ $mci_list['id'] ]['merge_vars'][ $merge_var['tag'] ] = 'email';
                    }

                    // Set the first name
                    if( $merge_var['tag'] == 'FNAME' ) {
                        $wppb_mci_lists[ $mci_list['id'] ]['merge_vars'][ $merge_var['tag'] ] = 'first_name';
                    }

                    // Set the last name
                    if( $merge_var['tag'] == 'LNAME' ) {
                        $wppb_mci_lists[ $mci_list['id'] ]['merge_vars'][ $merge_var['tag'] ] = 'last_name';
                    }

                }
            }
        }

        return $wppb_mci_lists;
    }


    /*
     * Function that returns the merge vars for a given list
     *
     * @since v.1.0.0
     *
     * @param string $api_key
     * @param string $list_id
     *
     * @return array or false
     *
     */
    function wppb_in_mci_api_get_list_merge_vars( $api_key, $list_id ) {

        $api = new WPPB_IN_MailChimp( $api_key );
        $merge_vars = $api->get('lists/'.$list_id.'/merge-fields', [ 'count' => 100, ]);

        if ($api->success()) {
            /* we need to add the email field as it is not returned in API v.3 */
            $merge_vars['total_items'] = $merge_vars['total_items'] + 1;
            array_unshift( $merge_vars['merge_fields'], array( 'merge_id' => 0, 'tag' => 'EMAIL', 'name' => 'Email Address', 'type' => 'email' ) );

            return $merge_vars['merge_fields'];
        } else {
            return $api->getLastError();
        }

    }


    /*
     * Function that returns the lists settings saved in the option settings
     *
     * @since v.1.0.0
     *
     * @return array or false
     *
     */
    function wppb_in_mci_get_lists() {
        $settings = get_option( 'wppb_mci_settings' );

        if( isset( $settings['lists'] ) && !empty( $settings['lists'] ) ) {
            return $settings['lists'];
        } else {
            return false;
        }
    }


    /*
     * Function that return the API key
     *
     * @since v.1.0.0
     *
     * @return string or false
     *
     */
    function wppb_in_mci_get_api_key() {
        $settings = get_option( 'wppb_mci_settings' );

        if( isset( $settings['api_key'] ) && !empty( $settings['api_key'] ) ) {
            return $settings['api_key'];
        } else {
            return false;
        }
    }


    /*
     * Function that subscribes an e-mail address
     *
     * @since v.1.0.0
     *
     * @param string $api_key
     * @param array $args
     *
     */
    function wppb_in_mci_api_subscribe( $api_key, $list_id, $args ) {

        $api = new WPPB_IN_MailChimp( $api_key );

        // If all is good we receive the user ids
        $response = $api->post("lists/". $list_id ."/members", $args );

        if ($api->success()) {
            return $response;
        } else {
            return $api->getLastError();
        }

    }


    /*
     * Function that unsubscribes an e-mail address
     *
     * @since v.1.0.0
     *
     * @param string $api_key
     * @param array $args
     *
     */
    function wppb_in_mci_api_unsubscribe( $api_key, $args ) {

        $api = new WPPB_IN_MailChimp( $api_key );

        $subscriber_hash = $api->subscriberHash($args['email']);
        $response = $api->delete("lists/". $args['id'] ."/members/$subscriber_hash");

        return $response;

    }


    /*
     * Updates a subscribed member's data
     *
     */
    function wppb_in_mci_api_update_member( $api_key, $list_id, $args ) {

        $api = new WPPB_IN_MailChimp( $api_key );
        $subscriber_hash = $api->subscriberHash( $args['email_address'] );
        $response = $api->patch("lists/".$list_id."/members/$subscriber_hash", $args );

        return $response;

    }


    /*
     * Verifies if a user's e-mail address is subscribed to MailChimp
     *
     */
    function wppb_in_mci_api_member_is_subscribed( $api_key, $args ) {

        $api = new WPPB_IN_MailChimp( $api_key );

        $subscriber_hash = $api->subscriberHash($args['emails'][0]['email']);

        $response = $api->get( "lists/". $args['id'] ."/members/$subscriber_hash" );

        if( $response['status'] == 'subscribed' || $response['status'] == 'pending')
            return true;
        else
            return false;

    }


    /*
     * Returns the arguments needed to subscribe/update a member
     *
     * @param array $request_data
     * @param string $list_id
     * @param int $user_id
     * @param string $form_location
     * @param mixed $old_user_email - bool false / string
     *
     * @return array
     *
     */
    function wppb_in_mci_api_get_args( $call, $request_data, $list_id, $user_id, $form_location = '', $old_user_email = false ) {

        // Get settings
        $settings      = get_option( 'wppb_mci_settings' );
        $manage_fields = get_option( 'wppb_manage_fields' );

        // We need the Avatar and Upload fields below, when adding the merge tags. Instead of going through each field
        // in the merge tags loop, we'll just do a loop here and leave only the Avatar and Upload fields in these
        // manage fields
        if( !empty( $manage_fields ) ) {
            foreach( $manage_fields as $key => $field ) {
                if( $field['field'] === 'Avatar' || $field['field'] === 'Upload' || $field['field'] === 'Subscription Plans' )
                    continue;
                unset( $manage_fields[$key] );
            }
        }


        // Set email, merge vars array and groupings array
        $email      = '';
        $merge_vars = array();
        $groupings  = array();

	    $email = wppb_in_mci_api_get_args_email( $request_data, $user_id, $form_location, $old_user_email );

        // Compatibility issues for username, website field and user role field
        if( !isset( $request_data['custom_field_user_role'] ) && isset( $request_data['role'] ) )
            $request_data['custom_field_user_role'] = $request_data['role'];

        if( !isset( $request_data['website'] ) && isset( $request_data['user_url'] ) )
            $request_data['website'] = $request_data['user_url'];

        if( !isset( $request_data['username'] ) && isset( $request_data['user_login'] ) )
            $request_data['username'] = $request_data['user_login'];


        // Set the merge vars for the rest of the fields
        foreach( $settings['lists'][$list_id]['merge_vars'] as $merge_var => $merge_var_assoc ) {

            if( $merge_var == 'EMAIL' )
                continue;;

            if( $merge_var == 'EMAIL' && empty( $form_location ) )
                $merge_var_assoc = 'user_email';

            if( isset( $request_data[ $merge_var_assoc ] ) && $merge_var_assoc !== 0 ) {
                if( is_array( $request_data[ $merge_var_assoc ] ) ) {
                    $merge_vars[ $merge_var ] = implode( ',', $request_data[ $merge_var_assoc ] );
                } else {

                    if( !empty( $manage_fields ) ) {
                        foreach( $manage_fields as $field ) {

                            // If the field is an Avatar or an Upload field, get the URL of the image, instead of the
                            // attachment ID
                            if( ( $field['field'] == 'Avatar' || $field['field'] == 'Upload' ) && $field['meta-name'] == $merge_var_assoc ) {

                                // Get the size of the image coresponding to the avatar field settings,
                                // but default to 'thumbnail' if the size doesn't exist for the image
                                $attachment_meta = wp_get_attachment_metadata( $request_data[$merge_var_assoc] );

                                //check that the attachment is an image
                                if( wp_attachment_is_image( $request_data[$merge_var_assoc] ) ) {

                                    if( !empty( $attachment_meta['sizes']['wppb-avatar-size-' . $field['avatar-size'] ] ) )
                                        $size = 'wppb-avatar-size-' . $field['avatar-size'];
                                    else
                                        $size = 'thumbnail';
                                    
                                    // Swap the ID with the Image url
                                    $request_data[ $merge_var_assoc ] = wp_get_attachment_image_url( $request_data[$merge_var_assoc], $size );
                                }
                                else
                                    $request_data[ $merge_var_assoc ] = '';
                            }

                            // If the field is a PMS Subscription Plans field get the actual subscription plan name,
                            // rather than its ID
                            if( $field['field'] === 'Subscription Plans' && $merge_var_assoc === 'subscription_plans' ) {
                                $user_subscription_plans = explode( ',', $request_data[ $merge_var_assoc ] );
                                $request_data[ $merge_var_assoc ] = '';
                                $i = 0;
                                foreach ( $user_subscription_plans as $user_subscription_plan) {
                                    $request_data[$merge_var_assoc] .= pms_get_subscription_plan( $user_subscription_plan )->name;
                                    $i++;
                                    if ( $i !== count( $user_subscription_plans ) ) {
                                        $request_data[$merge_var_assoc] .= ', ';
                                    }
                                }
                            }

                        }
                    }

                    $merge_vars[ $merge_var ] = trim( $request_data[ $merge_var_assoc ] );
                }
            }
        }


        // Set the groupings in the merge vars
        if( isset( $settings['lists'][$list_id]['groups'] ) ) {

            foreach( $settings['lists'][$list_id]['groups'] as $grouping_id => $field_meta_name ) {

                $interests = wppb_in_mci_get_group_interests( $settings['api_key'], $list_id, $grouping_id );

                // Skip to next one if no value is in the request for this field
                if( empty( $request_data[$field_meta_name] ) || empty( $interests['interests'] ) )
                    continue;

                if( !is_array( $request_data[$field_meta_name] ) ) {
                    $field_values = ( array_map( 'trim', explode( ',', $request_data[$field_meta_name] ) ) );
                } else {
                    $field_values = $request_data[$field_meta_name];
                }

                foreach ($interests['interests'] as $interest){
                    if( in_array($interest['name'], $field_values) ){
                        $groupings[$interest['id']] = true;
                    }
                    else{
                        $groupings[$interest['id']] = false;
                    }
                }

            }

        }

        // Check double opt in value
        if( $call == 'subscribe' && $settings['lists'][$list_id]['double_opt_in'] == 'on' )
            $status = 'pending';
        else
            $status = 'subscribed';


        // Subscribe users arguments
        $args = array(
            'email_address'     => $email,
            'status'            => $status,
            'merge_fields'      => (object)$merge_vars,
            'interests'         => (object)$groupings,
        );

        return $args;

    }

	/* Set the correct e-mail */
	function wppb_in_mci_api_get_args_email( $request_data, $user_id, $form_location = '', $old_user_email = false ){
		$email = '';

		if( empty( $form_location ) || $form_location == 'register' ) {

			// Comes from wppb_activate_user
			if( empty( $form_location ))
				$email = $request_data['user_email'];
			elseif( $form_location == 'register' )
				$email = $request_data['email'];


		} else {

			// Get userdata for the user
			$user_data = get_userdata($user_id);

			// If there's an old e-mail
			if( $old_user_email !== false ) {

				$email = trim( $old_user_email );
				$merge_vars['new-email'] = trim( $user_data->data->user_email );

			} else {

				$email = $user_data->data->user_email;

			}

		}

		return $email;

	}

    /* get's the interest from an interest group */
    function wppb_in_mci_get_group_interests( $api_key, $listId, $groupId ){
        $api = new WPPB_IN_MailChimp( $api_key );
        $result = $api->get("/lists/".$listId."/interest-categories/".$groupId."/interests", [ 'count' => 100, ]);
        return $result;
    }


    /* function that opts in gdpr */
    function wppb_in_mci_gdpr_opt_in( $api_key, $listId, $memberHash ){

        $api = new WPPB_IN_MailChimp( $api_key );

        $result = $api->get("lists/$listId/members/$memberHash");

        if(isset($result['marketing_permissions'])) {
            $perms = $result['marketing_permissions'];

            $data = array( 'marketing_permissions' => array() );
            foreach($perms as $perm) {
                $data['marketing_permissions'][] = array(
                    'marketing_permission_id' => $perm['marketing_permission_id'],
                    'enabled' => true
                );
            }

            $api->patch('lists/' . $listId . '/members/' . $memberHash, $data);
        }
    }

    /* function that opts out from gdpr */
    function wppb_in_mci_gdpr_opt_out( $api_key, $listId, $memberHash ){

        $api = new WPPB_IN_MailChimp( $api_key );

        $result = $api->get("lists/$listId/members/$memberHash");

        if(isset($result['marketing_permissions'])) {
            $perms = $result['marketing_permissions'];

            $data = array( 'marketing_permissions' => array() );
            foreach($perms as $perm) {
                $data['marketing_permissions'][] = array(
                    'marketing_permission_id' => $perm['marketing_permission_id'],
                    'enabled' => true
                );
            }

            $api->patch('lists/' . $listId . '/members/' . $memberHash, $data);
        }
    }