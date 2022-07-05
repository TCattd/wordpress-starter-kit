<?php

    /*
     * Function that creates the Campaign Monitor submenu page
     *
     * @since v.1.0.0
     *
     * @return void
     */
    function wppb_in_cmi_register_submenu_page() {
        add_submenu_page( 'profile-builder', __( 'Campaign Monitor', 'profile-builder' ), __( 'Campaign Monitor', 'profile-builder' ), 'manage_options', 'profile-builder-campaign-monitor', 'wppb_in_cmi_page_content' );
    }
    add_action( 'admin_menu', 'wppb_in_cmi_register_submenu_page', 20 );


    /*
     * Function that adds content to the Campaign Monitor submenu page
     *
     * @since v.1.0.0
     *
     * @return string
     */
    function wppb_in_cmi_page_content() {

        $wppb_cmi_settings = get_option('wppb_cmi_settings', array() );

        ?>

        <div class="wrap wppb-wrap wppb-cmi-wrap">
            <form method="post" action="options.php">

                <?php

                    // Settings fields
                    settings_fields( 'wppb_cmi_settings' );

                    // Page title
                    echo '<h2>' . esc_html__( 'Campaign Monitor Integration', 'profile-builder' ) . '</h2>';

                    // Display the API key settings input
                    echo wppb_in_cmi_get_page_api_key_content( $wppb_cmi_settings ); //phpcs:ignore

                    // If API key is okay display the rest of the information
                    if( wppb_in_cmi_get_api_key_status( $wppb_cmi_settings ) ) {

                        // Display the client selector
                        if( !isset( $wppb_cmi_settings['client'] ) )
                            echo wppb_in_cmi_get_page_client_selector_content( $wppb_cmi_settings, false );//phpcs:ignore
                        else
                            echo wppb_in_cmi_get_page_client_selector_content( $wppb_cmi_settings ); //phpcs:ignore

                        // Display the clients and each of their lists
                        echo wppb_in_cmi_get_page_clients_settings_content( $wppb_cmi_settings ); //phpcs:ignore
                    }

                ?>

                <input id="wppb-cmi-page-submit" type="submit" class="button button-primary" value="<?php echo esc_html__( 'Save', 'profile-builder' ); ?>" />

            </form>
        </div>
        <?php
    }


    /*
     * Function that outputs the content for the API key input field
     *
     * @since v.1.0.0
     *
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     *
     */
    function wppb_in_cmi_get_page_api_key_content( $wppb_cmi_settings ) {

        // Gat API key validation status
        $wppb_cmi_api_key_validated = get_option('wppb_cmi_api_key_validated', false);

        // Get API key value
        isset( $wppb_cmi_settings['api_key'] ) ? $wppb_cmi_api_key = $wppb_cmi_settings['api_key'] : $wppb_cmi_api_key = '';

        $output = '<div id="wppb-cmi-page-api-key">';

            // Label for the API key input
            $output .= '<label for="wppb-cmi-page-api-key-input"><strong>' . __( 'Campaign Monitor API key:', 'profile-builder' ) . '</strong></label>';

            // The input for the API key
            $output .= '<input id="wppb-cmi-page-api-key-input" class="wppb-text" type="text" name="wppb_cmi_settings[api_key]" value="' . $wppb_cmi_api_key . '" />';

            // Display the okay icon if everything is good
            if( !empty( $wppb_cmi_api_key ) && $wppb_cmi_api_key_validated == true ) {
                $output .= '<img src="' . WPPBCMI_IN_PLUGIN_URL . '/assets/img/icon_okay.png" title="' . __( 'The API key was successfully validated!', 'profile-builder' ) . '"/>';

            // Display the error icon if the API key has not been validated
            } elseif( !empty( $wppb_cmi_api_key ) && $wppb_cmi_api_key_validated == false ) {
                $output .= '<img src="' . WPPBCMI_IN_PLUGIN_URL . '/assets/img/icon_error.png" title="' . __( 'Either the API key is not valid or we could not connect to Campaign Monitor to validate it!', 'profile-builder' ) . '"/>';
            }

            // Description for the API key input
            $output .= '<p class="description">' . __( 'Enter your Campaign Monitor account API key.', 'profile-builder' ) . '</p>';

        $output .= '</div>';

        return $output;
    }


    /*
     * Function that outputs the content for the select drop-down that has the
     * clients of the Campaign Monitor account
     *
     * @since v.1.0.0
     *
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     *
     */
    function wppb_in_cmi_get_page_client_selector_content( $wppb_cmi_settings, $is_client = true ) {

        $hidden = '';
        if( isset( $wppb_cmi_settings['client'] ) && !empty( $wppb_cmi_settings['client'] ) )
            $hidden = 'hidden';

        $output = '<div id="wppb-cmi-page-client-selector" class="' . $hidden . '">';

            // Label for the clients drop-down
            $output .= '<label for="wppb-cmi-page-client-selector-select"><strong>' . __( 'Select client:', 'profile-builder' ) . '</strong></label>';

            $output .= '<div class="wppb-cmi-page-client-selector-select-wrapper">';

                // Drop-down with all the clients for this account
                $output .= '<select id="wppb-cmi-page-client-selector-select" class="widefat">';

                    // If a client has already been saved in the db add a Loading clients... option
                    // All the options will be loaded via ajax when document is ready, so this is just a placeholder
                    if( $is_client ) {
                        $output .= '<option value="">' . __( 'Loading clients...', 'profile-builder' ) . '</option>';

                    // If we don't have a client saved in the db display all clients options
                    } else {
                        $output .= wppb_in_cmi_page_client_selector_options_content( $wppb_cmi_settings );
                    }

                $output .= '</select>';

            $output .= '</div>';

            // Description for the clients drop down
            $output .= '<p class="description">' . __( 'Select a client that you would like to edit.', 'profile-builder' ) . '</p>';

        $output .= '</div>';


        return $output;
    }


    /*
     * Function that outputs the client select drop-down options
     *
     * @since v.1.0.0
     *
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     *
     */
    function wppb_in_cmi_page_client_selector_options_content( $wppb_cmi_settings = array() ) {

        // Check if this is ajax fired or not
        if( empty( $wppb_cmi_settings ) ) {
            $is_ajax = true;
            $wppb_cmi_settings = get_option( 'wppb_cmi_settings' );
        } else {
            $is_ajax = false;
        }

        // Get clients from Campaign Monitor
        $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
        $wrap = new WPPB_IN_CS_REST_General($auth);
        $result = $wrap->get_clients();

        $output = '';

        // If all is good populate
        if( isset($result) && $result->was_successful() ) {

            if( empty( $result->response ) )
                $output .= '<option value="">' . esc_html__( 'No clients found', 'profile-builder' ) . '</option>';
            else
                $output .= '<option value="">' . esc_html__( 'Select a client...', 'profile-builder' ) . '</option>';

            $clients = $result->response;

            foreach ($clients as $client) {
                $output .= '<option value="' . esc_attr( $client->ClientID ) . '">' . esc_html( $client->Name ) . '</option>';
            }
        }

        // Echo if ajax, return if not
        if( !$is_ajax ) {
            return $output;
        } else {
            echo $output; //phpcs:ignore
            wp_die();
        }

    }
    add_action( 'wp_ajax_wppb_cmi_page_client_selector_options_content', 'wppb_in_cmi_page_client_selector_options_content' );


    /*
     * Function that outputs the wrapper for the client lists table
     * It calls the function that displays the table
     *
     * @since v.1.0.0
     *
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     *
     */
    function wppb_in_cmi_get_page_clients_settings_content( $wppb_cmi_settings ) {

        $output = '<div id="wppb-cmi-page-clients-settings-wrapper">';

            if( isset( $wppb_cmi_settings['client'] ) && !empty( $wppb_cmi_settings['client'] ) ) {

                // Get the table with the lists for the client
                $output .= wppb_in_cmi_get_page_client_lists_table_content( $wppb_cmi_settings['client']['id'], $wppb_cmi_settings );

            }

        $output .= '</div>';

        return $output;
    }


    /*
     * Function that outputs the content of the table that contains the lists of the
     * current client
     * It is also called through ajax
     *
     * @since v.1.0.0
     *
     * @param string $wppb_cmi_client_id   - The ID of the current client
     * @param array $wppb_cmi_settings     - The settings array of the add-on
     *
     */
    function wppb_in_cmi_get_page_client_lists_table_content( $wppb_cmi_client_id, $wppb_cmi_settings = array() ) {

        // Let's say this is not an ajax call
        $is_ajax = false;

        // If we have this it's an ajax call
        if( isset( $_POST['wppb_cmi_client_id'] ) ) {
            $is_ajax = true;
            $wppb_cmi_client_id = trim( sanitize_text_field( $_POST['wppb_cmi_client_id'] ) );
        }

        // Table
        $output = '<table id="wppb-cmi-list-table-client-data" class="wp-list-table widefat fixed posts wppb-cmi-list-table">';

            // Client name
            $output .= wppb_in_cmi_get_page_client_lists_caption_content( $wppb_cmi_client_id, $wppb_cmi_settings, $is_ajax );

            // Table head
            $output .= '<thead>';
                $output .= '<tr>';
                    $output .= '<th scope="col" class="manage-column column-title" style="width: 80%;"><span>' . esc_html__( 'Client List','profile-builder' ) . '</span></th>';
                    $output .= '<th scope="col" class="manage-column column-title"><span>' . esc_html__( 'Fields Count','profile-builder' ) . '</span></th>';
                $output .= '</tr>';
            $output .= '</thead>';

            // Table footer
            $output .= '<tfoot>';
                $output .= '<tr>';
                    $output .= '<th scope="col" class="manage-column column-title""><span>' . esc_html__( 'Client List','profile-builder' ) . '</span></th>';
                    $output .= '<th scope="col" class="manage-column column-title"><span>' . esc_html__( 'Fields Count','profile-builder' ) . '</span></th>';
                $output .= '</tr>';
            $output .= '</tfoot>';

            // Table body
            $output .= '<tbody>';
                $output .= wppb_in_cmi_get_page_client_lists_tbody_content( $wppb_cmi_client_id, $wppb_cmi_settings, $is_ajax );
            $output .= '</tbody>';

        $output .= '</table>';

        // Return
        if( !$is_ajax )
            return $output;
        else {
            echo $output; //phpcs:ignore
            wp_die();
        }

    }
    add_action( 'wp_ajax_wppb_cmi_get_page_client_lists_table_content', 'wppb_in_cmi_get_page_client_lists_table_content' );


    /*
     * Function that outputs the content for the table caption, which contains the name
     * of the client, a button to select another client and a button to syncronize the client data from
     * the settings with that from Campaign Monitor
     *
     * @since v.1.0.0
     *
     * @param string $wppb_cmi_client_id  - The ID of the current client
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     * @param bool $is_ajax               - True or false depending if the function is called via ajax
     *
     */
    function wppb_in_cmi_get_page_client_lists_caption_content( $wppb_cmi_client_id, $wppb_cmi_settings = array(), $is_ajax = false ) {

        $output = '';
        $wppb_cmi_client_name = '';

        // If it's an ajax call get client details from Campaign Monitor
        if( $is_ajax ) {

            // Get settings
            $wppb_cmi_settings = get_option( 'wppb_cmi_settings', array() );

            // Connect to Campaign Monitor
            $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
            $wrap = new WPPB_IN_CS_REST_Clients( $wppb_cmi_client_id, $auth );
            $result = $wrap->get();

            // Set name from returned data from Campaign Monitor
            if( $result->was_successful() ) {
                $wppb_cmi_client_name = $result->response->BasicDetails->CompanyName;
            }

        } else {

            // Set name from saved settings
            $wppb_cmi_client_name = $wppb_cmi_settings['client']['name'];
        }

        // Needed output
        $output .= '<caption class="wppb-cmi-table-caption">';

            // Client name
            $output .= $wppb_cmi_client_name;

            // Hidden input with the client id
            $output .= '<input class="wppb-cmi-client-id" type="hidden" name="wppb_cmi_settings[client][id]" value="' . esc_attr( $wppb_cmi_client_id ) . '" />';
            $output .= '<input class="wppb-cmi-client-id" type="hidden" name="wppb_cmi_settings[client][name]" value="' . esc_attr( $wppb_cmi_client_name ) . '" />';

            // Options buttons for client
            $output .= '<a id="wppb-cmi-sync-client-btn" href="#" class="add-new-h2 alignright" title="' . esc_html__( 'Retrieves changes made in your Campaign Monitor account and matches it with the saved data from the add-on. This does not save the new data, so you will have to manually save.', 'profile-builder' ) . '">' . __( 'Synchronize client data', 'profile-builder' ) . '</a>';
            $output .= '<a id="wppb-cmi-change-client-btn" href="#" class="add-new-h2 alignright">' . esc_html__( 'Change client', 'profile-builder' ) . '</a>';

        $output .= '</caption>';

        return $output;
    }


    /*
     * Function that outputs the lists of the current client in the form of HTML rows
     *
     * @since v.1.0.0
     *
     * @param string $wppb_cmi_client_id  - The ID of the current client
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     * @param bool $is_ajax               - True or false depending if the function is called via ajax
     *
     */
    function wppb_in_cmi_get_page_client_lists_tbody_content( $wppb_cmi_client_id, $wppb_cmi_settings = array(), $is_ajax = false ) {

        $output = '';
        $wppb_cmi_client_lists = array();

        // If it's an ajax call get client lists details from Campaign Monitor
        if( $is_ajax ) {

            // Get settings
            $wppb_cmi_settings = get_option( 'wppb_cmi_settings', array() );

            // Connect to Campaign Monitor
            $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
            $wrap = new WPPB_IN_CS_REST_Clients( $wppb_cmi_client_id, $auth );
            $result = $wrap->get_lists();

            if( $result->was_successful() )
                $wppb_cmi_client_lists = $result->response;

        // If it is not an ajax call compose array of list objects
        } else {

            if( !empty( $wppb_cmi_settings['client']['lists'] ) ) {

                $current_list = 0;

                // For each list from saved settings create an object with list id and name data
                foreach( $wppb_cmi_settings['client']['lists'] as $wppb_cmi_list_id => $wppb_cmi_list ) {
                    $wppb_cmi_client_lists[$current_list] = new stdClass();

                    $wppb_cmi_client_lists[$current_list]->ListID = $wppb_cmi_list_id;
                    $wppb_cmi_client_lists[$current_list]->Name = $wppb_cmi_list['name'];

                    $current_list++;
                }
            }
        }

        // Output a row of content for each list
        if( !empty( $wppb_cmi_client_lists ) ) {
            foreach( $wppb_cmi_client_lists as $wppb_cmi_client_list ) {
                $output .= wppb_in_cmi_get_page_client_list_row_content( $wppb_cmi_client_list, $wppb_cmi_settings, $is_ajax );
            }

        // If no lists found return message that no lists were found
        } else {
            $output .= '<tr><td>' . esc_html__( 'No lists were found.', 'profile-builder' ) . '</td></tr>';
        }

        return $output;
    }


    /*
     * Function that outputs the content for a list of the current client, in the form of a HTML row for the basic
     * information ( name ) and a hidden row with the field associations and extra options
     *
     * @since v.1.0.0
     *
     * @param object $wppb_cmi_list       - The list data, contains list id and list name
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     * @param bool $is_ajax               - True or false depending if the function is called via ajax
     *
     */
    function wppb_in_cmi_get_page_client_list_row_content( $wppb_cmi_list, $wppb_cmi_settings = array(), $is_ajax = false ) {

        // Row with the list name, field count
        $output = '<tr class="wppb-cmi-list">';

            $output .= '<td class="post-title column-title">';
                $output .= '<strong><a class="wppb-cmi-list-edit" href="#" title="' . esc_html__( 'Click to edit', 'profile-builder' ) . '">' . $wppb_cmi_list->Name . '</a></strong>';
                $output .= '<input type="hidden" name="wppb_cmi_settings[client][lists][' . esc_attr( $wppb_cmi_list->ListID ) . '][name]" value="' . esc_attr( $wppb_cmi_list->Name ) . '" />';
            $output .= '</td>';

            $output .= '<td class="wppb-cmi-fields-count"></td>';

        $output .= '</tr>';

        // Hidden row with the settings for each list, like field associations and extra options
        $output .= '<tr class="wppb-cmi-list-settings hidden">';
            $output .= '<td>';
                $output .= wppb_in_cmi_get_page_client_list_fields_content( $wppb_cmi_list, $wppb_cmi_settings, $is_ajax );
                $output .= wppb_in_cmi_get_page_client_list_extra_options_content( $wppb_cmi_list, $wppb_cmi_settings );

                $output .= '<a href="#" class="wppb-cmi-list-settings-cancel button">' . esc_html__( 'Cancel', 'profile-builder' ) . '</a>';
            $output .= '</td>';
        $output .= '</tr>';

        return $output;
    }


    /*
     * Function that outputs the content for each of the fields that a list has,
     * in order to associate it with a field from Profile Builder
     *
     * @since v.1.0.0
     *
     * @param object $wppb_cmi_list       - The list data, contains list id and list name
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     * @param bool $is_ajax               - True or false depending if the function is called via ajax
     *
     */
    function wppb_in_cmi_get_page_client_list_fields_content( $wppb_cmi_list, $wppb_cmi_settings, $is_ajax = false ) {

        $wppb_cmi_fields = array();
        $wppb_manage_fields = wppb_in_cmi_get_manage_fields();

        // If it is an ajax call connect to Campaign Monitor and get the fields from there and also populate it with default e-mail and name fields
        if( $is_ajax ) {

            // Connect to Campaign Monitor
            $auth = array( 'api_key' => $wppb_cmi_settings['api_key'] );
            $wrap = new WPPB_IN_CS_REST_Lists( $wppb_cmi_list->ListID, $auth );
            $result = $wrap->get_custom_fields();

            // Set the custom fields
            if( $result->was_successful() ) {
                $wppb_cmi_fields = $result->response;

                // Add the default fields at the beggining of the response array
                $wppb_cmi_default_fields = array( 'fullname' => 'Name', 'email' => 'Email address' );

                foreach( $wppb_cmi_default_fields as $wppb_cmi_default_field_key => $wppb_cmi_default_field_name ) {
                    $wppb_cmi_default_field = new stdClass();

                    $wppb_cmi_default_field->FieldName = $wppb_cmi_default_field_name;
                    $wppb_cmi_default_field->Key = $wppb_cmi_default_field_key;

                    array_unshift( $wppb_cmi_fields, $wppb_cmi_default_field );
                }
            }

        // If it is not an ajax call then create a list of objects based on the data saved in the database
        } else {

            if( isset( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'] ) && !empty( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'] ) ) {

                $current_list = 0;

                foreach( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'] as $wppb_cmi_field_key => $wppb_cmi_field ) {
                    $wppb_cmi_fields[$current_list] = new stdClass();

                    $wppb_cmi_fields[$current_list]->FieldName = $wppb_cmi_field['name'];
                    $wppb_cmi_fields[$current_list]->Key = $wppb_cmi_field_key;

                    $current_list++;
                }
            }

        }

        // Start outputing information
        $output = '<div class="wppb-cmi-list-settings-section">';

            // Section title
            $output .= '<strong class="wppb-cmi-list-settings-section-title">' . esc_html__( 'Field Associations:', 'profile-builder' ) . '</strong>';

            // Output the default fields and custom fields
            foreach( $wppb_cmi_fields as  $wppb_cmi_field ) {

                $output .= '<div class="wppb-cmi-list-field-wrapper">';

                // Replace the brackets from the field keys
                $wppb_cmi_field_key = str_replace('[', '', str_replace(']', '', $wppb_cmi_field->Key));

                // Output the field title
                $output .= '<label for="wppb-cmi-list-field-' . esc_attr( $wppb_cmi_list->ListID ) . '-' . esc_attr( $wppb_cmi_field_key ) . '">' . esc_html( $wppb_cmi_field->FieldName ) . '</label>';

                // Output a hidden field to save the field name
                $output .= '<input type="hidden" name="wppb_cmi_settings[client][lists][' . esc_attr( $wppb_cmi_list->ListID ) . '][fields][' . esc_attr( $wppb_cmi_field_key ) . '][name]" value="' . esc_attr( $wppb_cmi_field->FieldName ) . '" />';

                // Output the drop down with the PB manage fields
                $output .= '<select id="wppb-cmi-list-field-' . esc_attr( $wppb_cmi_list->ListID ) . '-' . esc_attr( $wppb_cmi_field_key ) . '" name="wppb_cmi_settings[client][lists][' . esc_attr( $wppb_cmi_list->ListID ) . '][fields][' . esc_attr( $wppb_cmi_field_key ) . '][request_name]">';

                if($wppb_cmi_field->Key == 'email'){
		            foreach ($wppb_manage_fields as $wppb_field) {
			            if($wppb_field['field'] == 'Default - E-mail' || ($wppb_field['field'] == 'Email') ){
				            // Get request name
				            $request_name = wppb_in_cmi_get_request_name($wppb_field);
				            // Check if value is selected
				            $selected = '';
				            if( isset( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'][ $wppb_cmi_field_key ]['request_name'] ))  {
					            $selected = selected( $request_name, $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'][ $wppb_cmi_field_key ]['request_name'], false );
				            }

				            $output .= '<option ' . $selected . ' value="' . esc_attr( $request_name ) . '">' . esc_html( $wppb_field['field-title'] ) . ' ( ' . esc_html( $wppb_field['field'] ) . ' )' . '</option>';
			            }
		            }
	            } else {
		            $output .= '<option value="">' . esc_html__('None', 'profile-builder') . '</option>';
		            foreach ($wppb_manage_fields as $wppb_field) {
			            // Get request name
			            $request_name = wppb_in_cmi_get_request_name($wppb_field);

			            // Check if value is selected
			            $selected = '';
			            if( isset( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'][ $wppb_cmi_field_key ]['request_name'] ))  {
				            $selected = selected( $request_name, $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['fields'][ $wppb_cmi_field_key ]['request_name'], false );
			            }

			            $output .= '<option ' . $selected . ' value="' . esc_attr( $request_name ) . '">' . esc_html( $wppb_field['field-title'] ) . ' ( ' . esc_html( $wppb_field['field'] ) . ' )' . '</option>';
		            }
	            }

                $output .= '</select>';

                $output .= '</div>';

            }

            // Ouput this section description
            $output .= '<p class="description">' . esc_html__( 'Associate each Campaign Monitor field with a Profile Builder field', 'profile-builder' ) . '</p>';
        $output .= '</div>';

        return $output;
    }


    /*
     * Function that outputs the content for the list extra options
     *
     * @since v.1.0.0
     *
     * @param object $wppb_cmi_list       - The list data, contains list id and list name
     * @param array $wppb_cmi_settings    - The settings array of the add-on
     *
     */
    function wppb_in_cmi_get_page_client_list_extra_options_content( $wppb_cmi_list, $wppb_cmi_settings ) {

        // Start outputing information
        $output = '<div class="wppb-cmi-list-settings-section">';

            // Section title
            $output .= '<strong class="wppb-cmi-list-settings-section-title">' . esc_html__( 'Extra Options:', 'profile-builder' ) . '</strong>';

            // Check if checked
            $checked = '';
            if( isset( $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['resubscribe'] ) && $wppb_cmi_settings['client']['lists'][ $wppb_cmi_list->ListID ]['resubscribe'] == 'on' )
                $checked = 'checked="checked"';

            // Output option input
            $output .= '<label><input type="checkbox" name="wppb_cmi_settings[client][lists][' . esc_html( $wppb_cmi_list->ListID ) . '][resubscribe]" ' . $checked . ' />' . esc_html__( 'Resubscribe', 'profile-builder' ) . '</label>';

            // Ouput option description
            $output .= '<p class="description">' . esc_html__( 'If the subscriber is in an inactive state or has previously been unsubscribed and you check the Resubscribe option, they will be re-added to the list. Therefore, this method should be used with caution and only where suitable.', 'profile-builder' ) . '</p>';
        $output .= '</div>';

        return $output;
    }


    /*
     * Function that sanitizes the data of the Campaign Monitor option before saving it
     *
     * @since v.1.0.0
     *
     * @param array $wppb_cmi_settings_new    - The settings array of the add-on
     *
     */
    function wppb_in_cmi_settings_sanitize( $wppb_cmi_settings_new ) {

        // Get saved settings
        $wppb_cmi_settings_old = get_option('wppb_cmi_settings', array() );

        // Sanitize the API key
        // Let's consider the api is valid
        $wppb_cmi_api_key_validated = true;

        // Get api key value that will be saved
        isset( $wppb_cmi_settings_new['api_key'] ) ? $wppb_cmi_api_key = $wppb_cmi_settings_new['api_key'] : $wppb_cmi_api_key = '';


        // Check response from Campaign Monitor
        $auth = array( 'api_key' => $wppb_cmi_api_key );
        $wrap = new WPPB_IN_CS_REST_General($auth);
        $result = $wrap->get_clients();

        // If it wasn't succesfull invalidate the key
        if( !$result->was_successful() )
            $wppb_cmi_api_key_validated = false;


        // Throw error in case the api key is not valid and update the validated options
        // Throw error if for some reason the ping back returns false
        // Else update api key validated option to true
        if( $wppb_cmi_api_key_validated == false ) {

            if( empty( $wppb_cmi_settings_new['api_key'] ) ) {
                add_settings_error( 'wppb_cmi_settings_error', 'cmi-api-key-empty', __( 'Campaign Monitor API key is empty', 'profile-builder' ) );
            } else {
                add_settings_error( 'wppb_cmi_settings_error', 'cmi-api-key-invalid', __( 'Campaign Monitor API key is invalid', 'profile-builder' ) );
            }

            update_option( 'wppb_cmi_api_key_validated' , $wppb_cmi_api_key_validated );

        } else {
            update_option( 'wppb_cmi_api_key_validated' , $wppb_cmi_api_key_validated );
        }

        return $wppb_cmi_settings_new;
    }



    /*
    * Function that pushes settings errors to the user
    *
    * @since v.1.0.0
    */
    function wppb_in_cmi_settings_admin_notices() {
        settings_errors( 'wppb_cmi_settings_error' );
    }
    add_action( 'admin_notices', 'wppb_in_cmi_settings_admin_notices' );