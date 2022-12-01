<?php

    /*
     * Function that creates the MailChimp submenu page
     *
     * @since v.1.0.0
     *
     * @return void
     */
    function wppb_in_mci_register_submenu_page() {
        add_submenu_page( 'profile-builder', __( 'MailChimp', 'profile-builder' ), __( 'MailChimp', 'profile-builder' ), 'manage_options', 'profile-builder-mailchimp', 'wppb_in_mci_page_content' );
    }
    add_action( 'admin_menu', 'wppb_in_mci_register_submenu_page', 20 );


    /**
     * Function that adds content to the MailChimp submenu page
     *
     * @since v.1.0.0
     *
     * @return string
     */
    function wppb_in_mci_page_content() {

        $wppb_mci_settings = get_option('wppb_mci_settings');

        $wppb_mci_api_key_validated = get_option( 'wppb_mailchimp_api_key_validated' );

        ?>
        <div class="wrap wppb-wrap wppb-wrap-mailchimp">
            <form method="post" action="options.php">

                <?php settings_fields( 'wppb_mci_settings' ); ?>

                <h2>
                    <?php esc_html_e( 'MailChimp Integration', 'profile-builder' ); ?>
                    <a href="https://www.cozmoslabs.com/docs/profile-builder-2/add-ons/mailchimp/?utm_source=wpbackend&utm_medium=pb-documentation&utm_campaign=PBDocs" target="_blank" data-code="f223" class="wppb-docs-link dashicons dashicons-editor-help"></a>
                </h2>

                <?php echo wppb_in_mci_get_mailchimp_api_key_content( $wppb_mci_settings ); //phpcs:ignore ?>

                <?php

                // Check to see if a API key has been set
                if( !isset( $wppb_mci_settings['api_key'] ) || empty( $wppb_mci_settings['api_key'] ) ) {

                    echo '<p>' . esc_html__('Before you can make any changes you will need to add a MailChimp API key.', 'profile-builder') . '</p>';

                // If everything goes well let's show some options
                } elseif( $wppb_mci_api_key_validated == true ) {

                    $wppb_mci_api_key = $wppb_mci_settings['api_key'];

                    $api = new WPPB_IN_MailChimp( $wppb_mci_api_key );

                    $api_lists['lists'] = array();
                    $api_lists = $api->get('lists', [ 'count' => 100, ] );
                    ?>

                    <table id="wppb-mci-list-table" class="wp-list-table widefat fixed posts">
                        <thead>
                        <tr>
                            <th scope="col" class="manage-column column-title" style="width: 85%;"><span><?php echo esc_html__( 'MailChimp List','profile-builder' ); ?></span></th>
                            <th scope="col" class="manage-column column-title"><span><?php echo esc_html__( 'Fields Count','profile-builder' ); ?></span></th>
                        </tr>
                        </thead>

                        <tfoot>
                        <tr>
                            <th scope="col" class="manage-column column-title"><span><?php echo esc_html__( 'MailChimp List','profile-builder' ); ?></span></th>
                            <th scope="col" class="manage-column column-title"><span><?php echo esc_html__( 'Fields Count','profile-builder' ); ?></span></th>
                        </tr>
                        </tfoot>

                        <tbody id="the-list">

                        <?php

                        // Output each list from MailChimp
                        if( !empty($api_lists['lists']) ) {
                            foreach( $api_lists['lists'] as $mci_list ) {
                                echo wppb_in_mci_get_mailchimp_list_content( $wppb_mci_api_key, $mci_list, $wppb_mci_settings ); //phpcs:ignore
                            }
                        } else {
                            echo '<tr><td>' . esc_html__( 'We couldn\'t find any lists', 'profile-builder') . '</td></tr>';
                        }

                        ?>

                        </tbody>
                    </table>

                <?php
                }
                ?>

                <input type="submit" class="button button-primary" value="<?php echo esc_html__( 'Save', 'profile-builder' ); ?>" />

            </form>
        </div>

    <?php
    }


    /*
     * Function that returns the content for the API key input
     *
     * @since v.1.0.0
     *
     * @param array $wppb_mci_settings  - The settings options saved for MailChimp
     *
     */
    function wppb_in_mci_get_mailchimp_api_key_content( $wppb_mci_settings ) {

        $wppb_mci_api_key_validated = get_option('wppb_mailchimp_api_key_validated', false);

        isset( $wppb_mci_settings['api_key'] ) ? $wppb_mci_api_key = $wppb_mci_settings['api_key'] : $wppb_mci_api_key = '';

        $output = '';
        $output .= '<div id="wppb-mailchimp-api-key">';
            $output .= '<label for="wppb-mailchimp-api-key-input"><strong>' . __( 'MailChimp API Key:', 'profile-builder' ) . '</strong></label>';

            $output .= '<input id="wppb-mailchimp-api-key-input" class="wppb-text" type="text" name="wppb_mci_settings[api_key]" value="' . $wppb_mci_api_key . '" />';

            if( !empty( $wppb_mci_api_key ) && $wppb_mci_api_key_validated == true ) {
                $output .= '<span class="validateStatus"><img src="' . WPPBMCI_IN_PLUGIN_URL . '/assets/img/icon_okay.png" title="' . __( 'The API key was successfully validated!', 'profile-builder' ) . '"/></span>';
            } elseif( !empty( $wppb_mci_api_key ) && $wppb_mci_api_key_validated == false ) {
                $output .= '<span class="validateStatus"><img src="' . WPPBMCI_IN_PLUGIN_URL . '/assets/img/icon_error.png" title="' . __( 'Either the API key is not valid or we could not connect to MailChimp to validate it!', 'profile-builder' ) . '"/></span>';
            }

            $output .= '<p class="description">' . __( 'Enter a MailChimp API key. You can create keys in your MailChimp account.', 'profile-builder' ) . '</p>';
        $output .= '</div>';

        return $output;
    }


    /*
     * Function that returns a MailChimp list in a
     * HTML formated table row
     *
     * @since v.1.0.0
     *
     * @param string $wppb_mci_api_key      - The MailChimp API key
     * @param array $mci_list               - The MailChimp list
     * @param array $wppb_mci_settings      - The settings option saved for MailChimp
     *
     * @return string
     *
     */
    function wppb_in_mci_get_mailchimp_list_content( $wppb_mci_api_key, $mci_list, $wppb_mci_settings ) {

        // Outputs the list name, number of fields the list contains
        $output = '<tr class="wppb-mci-list">';

        $output .= '<td class="post-title column-title">';
            $output .= '<strong>' . $mci_list['name'] . '</strong>';
            $output .= '<input name="wppb_mci_settings[lists][' . $mci_list['id'] . '][name]" type="hidden" value="' . $mci_list['name'] . '" />';
            $output .= '<div class="row-actions"><span class="edit"><a href="#" title="' . __( 'Edit this item', 'profile-builder' ) . '">Edit</a></span></div>';
        $output .= '</td>';

        $output .= '<td>' . ( $mci_list['stats']['merge_field_count'] + 1 ) . '</td>';

        $output .= '</tr>';

        // Outputs the fields merge vars of the list and the list extra options
        $output .= '<tr class="wppb-mci-list-settings">';
            $output .= '<td>';
                $output .= wppb_in_mci_get_mailchimp_list_merge_vars( $wppb_mci_api_key, $mci_list['id'], $wppb_mci_settings );
                $output .= wppb_in_mci_get_mailchimp_list_groups( $wppb_mci_api_key, $mci_list['id'], $wppb_mci_settings );
                $output .= wppb_in_mci_get_mailchimp_list_extra_options( $mci_list['id'], $wppb_mci_settings );

                $output .= '<a href="#" class="wppb-mci-list-settings-cancel button">' . __( 'Cancel', 'profile-builder' ) . '</a>';
            $output .= '</td>';
        $output .= '</tr>';

        return $output;
    }


    /*
     * Function that returns the MailChimp merge vars for a list
     *
     * @since v.1.0.0
     *
     * @param string $wppb_mci_api_key      - The MailChimp API key
     * @param array $mci_list_id            - The id of the list MailChimp
     * @param array $wppb_mci_settings      - The settings option saved for MailChimp
     *
     * @return string
     *
     */
    function wppb_in_mci_get_mailchimp_list_merge_vars( $wppb_mci_api_key, $mci_list_id, $wppb_mci_settings ) {

        // Connect to the API and return the merge vars of the list
        $api_list_merge_vars = wppb_in_mci_api_get_list_merge_vars( $wppb_mci_api_key, $mci_list_id );

        // Get all fields from manage fields
        $wppb_manage_fields = wppb_in_mci_get_manage_fields();

        $output = '<div class="wppb-mci-list-settings-section">';

            // Section title
            $output .= '<strong class="wppb-mci-list-settings-section-title">' . __( 'Field Associations:', 'profile-builder' ) . '</strong>';

            // Loop through the merge vars ( aka MailChimp fields )
            if( !empty( $api_list_merge_vars ) ) {
                foreach( $api_list_merge_vars as $merge_var ) {

                    $output .= '<div class="wppb-mci-merge-field-var">';

                        // Output the field title
                        $output .= '<label for="wppb-mci-merge-var-' . $mci_list_id . $merge_var['tag'] . '" ' . ( (isset( $merge_var['required'] ) && $merge_var['required'] ) ? 'title="' . __( 'This field is required in MailChimp', 'profile-builder' ) . '"' : '' ) . '>';
                        $output .= $merge_var['name'];

                        // Output required mark
                        $output .= ( (isset( $merge_var['required'] ) && $merge_var['required'] ) ? '<span class="wppb-mci-required">*</span>' : '' );
                        $output .= '</label>';

                        // Check if merge var is EMAIL and set disabled to true if it is
                        $disabled = '';

                        if( $merge_var['tag'] == 'EMAIL' ) {
                            $disabled = 'disabled="disabled"';

                            foreach( $wppb_manage_fields as $field ) {
                                if( $field['field'] == 'Default - E-mail' )
                                    $output .= '<input type="hidden" name="wppb_mci_settings[lists][' . $mci_list_id . '][merge_vars][' . $merge_var['tag'] . ']" value="' . wppb_in_mci_get_request_name( $field ) . '" />';
                            }
                        }

                        // Output a select with the manage fields
                        $output .= '<select name="wppb_mci_settings[lists][' . $mci_list_id . '][merge_vars][' . $merge_var['tag'] . ']" id="wppb-mci-merge-var-' . $mci_list_id . $merge_var['tag'] . '" class="wppb-list-data-item widefat"' . $disabled . '>';
                            $output .= '<option value="0">' . __( 'None', 'profile-builder' ) . '</option>';

                            // Add the manage fields to the drop down merge vars association select
                            foreach( $wppb_manage_fields as $field ) {

                                $request_name = wppb_in_mci_get_request_name( $field );

                                if( empty( $request_name ) )
                                    $request_name = 0;

                                if( $field['field'] == 'Default - E-mail' && $merge_var['tag'] == 'EMAIL' ) {
                                    $output .= '<option selected="selected" value="' . wppb_in_mci_get_request_name( $field ) . '">' . $field['field-title'] . ' ( ' . $field['field'] . ' )' . '</option>';
                                } else {

                                    // Check if value is selected
                                    $selected = '';
                                    if( isset( $wppb_mci_settings['lists'][$mci_list_id] ) && isset( $wppb_mci_settings['lists'][$mci_list_id]['merge_vars'][ $merge_var['tag'] ] ) ) {
                                        $selected = selected( $request_name, $wppb_mci_settings['lists'][$mci_list_id]['merge_vars'][ $merge_var['tag'] ], false );
                                    }

                                    $output .= '<option ' . $selected . ' value="' . wppb_in_mci_get_request_name( $field ) . '">' . $field['field-title'] . ' ( ' . $field['field'] . ' )' . '</option>';
                                }

                            }
                        $output .= '</select>';
                    $output .= '</div>';
                }
            }

        $output .= '<p class="description">' . __( 'Associate each MailChimp field with a Profile Builder field', 'profile-builder' ) . '</p>';
        $output .= '</div>';

        return $output;
    }


    /*
     * Function that returns the MailChimp groups for a list
     *
     * @since v.1.0.9
     *
     * @param string $wppb_mci_api_key      - The MailChimp API key
     * @param array $mci_list_id            - The id of the list MailChimp
     * @param array $wppb_mci_settings      - The settings option saved for MailChimp
     *
     * @return string
     *
     */
    function wppb_in_mci_get_mailchimp_list_groups( $wppb_mci_api_key, $mci_list_id, $wppb_mci_settings ) {

        // Connect to the API and return the merge vars of the list
        $api = new WPPB_IN_MailChimp( $wppb_mci_api_key );
        $api_list_groupings = $api->get( 'lists/'.$mci_list_id.'/interest-categories', [ 'count' => 100, ]);

        if( empty($api_list_groupings['categories']) || isset($api_list_groupings['error']) )
            return '';

        // Get all fields from manage fields
        $wppb_manage_fields = wppb_in_mci_get_manage_fields();

        $output = '<div class="wppb-mci-list-settings-section">';

            // Section title
            $output .= '<strong class="wppb-mci-list-settings-section-title">' . __( 'Group Associations:', 'profile-builder' ) . '</strong>';

            // Loop through the MailChimp groups
            foreach( $api_list_groupings['categories'] as $grouping ) {

                $output .= '<div class="wppb-mci-merge-field-var">';

                // Output the field title
                $output .= '<label for="wppb-mci-group-' . $mci_list_id . $grouping['id'] . '">' . $grouping['title'] . '</label>';

                // Output a select with the manage fields
                $output .= '<select name="wppb_mci_settings[lists][' . $mci_list_id . '][groups][' . $grouping['id'] . ']" id="wppb-mci-group-' . $mci_list_id . $grouping['id'] . '" class="wppb-list-data-item widefat">';
                    $output .= '<option value="0">' . __( 'None', 'profile-builder' ) . '</option>';

                    // Add the manage fields to the drop down merge vars association select
                    foreach( $wppb_manage_fields as $field ) {

                        if( $field['field'] != 'Radio' && $field['field'] != 'Checkbox' && $field['field'] != 'Select' && $field['field'] != 'Input (Hidden)' )
                            continue;

                        $request_name = wppb_in_mci_get_request_name( $field );

                        if( empty( $request_name ) )
                            $request_name = 0;

                        // Check if value is selected
                        $selected = '';
                        if( isset( $wppb_mci_settings['lists'][$mci_list_id] ) && isset( $wppb_mci_settings['lists'][$mci_list_id]['groups'][ $grouping['id'] ] ) ) {
                            $selected = selected( $request_name, $wppb_mci_settings['lists'][$mci_list_id]['groups'][ $grouping['id'] ], false );
                        }

                        $output .= '<option ' . $selected . ' value="' . wppb_in_mci_get_request_name( $field ) . '">' . $field['field-title'] . ' ( ' . $field['field'] . ' )' . '</option>';

                    }

                $output .= '</select>';
                $output .= '</div>';
            }

            $output .= '<p class="description">' . __( 'Associate each MailChimp group with a Profile Builder field', 'profile-builder' ) . '</p>';
        $output .= '</div>';

        return $output;
    }


    /*
     * Function that returns the MailChimp extra options for a list
     *
     * @since v.1.0.0
     *
     * @param array $mci_list_id            - The id of the list MailChimp
     * @param array $wppb_mci_settings      - The settings option saved for MailChimp
     *
     * @return string
     *
     */
    function wppb_in_mci_get_mailchimp_list_extra_options( $mci_list_id, $wppb_mci_settings ) {

        $output = '<div class="wppb-mci-list-settings-section">';

            // Section title
            $output .= '<strong class="wppb-mci-list-settings-section-title">' . __( 'Extra Options:', 'profile-builder' ) . '</strong>';

            // Output option for double opt in
            $checked_double_opt_int = '';
            if( isset( $wppb_mci_settings['lists'][$mci_list_id] ) )
                $checked_double_opt_int = checked( $wppb_mci_settings['lists'][$mci_list_id]['double_opt_in'], 'on', false );

            $output .= '<div class="wppb-mci-list-setting wppb-mci-list-setting-double-opt-in">';
                $output .= '<input id="wppb-list-data-item-double_opt_in-' . $mci_list_id . '" name="wppb_mci_settings[lists][' . $mci_list_id . '][double_opt_in]" ' . $checked_double_opt_int . ' class="wppb-list-data-item" type="checkbox" />';
                $output .= '<label for="wppb-list-data-item-double_opt_in-' . $mci_list_id . '">' . __( 'Double Opt-In', 'profile-builder' ) . '</label>';
                $output .= '<p class="description">' . __( 'If you select double opt-in, the user will receive an email to confirm the subscription', 'profile-builder' ) . '</p>';
            $output .= '</div>';

            // Output option for welcome e-mail
            $checked_gdpr = '';
            if( isset( $wppb_mci_settings['lists'][$mci_list_id]['gdpr'] ) )
                $checked_gdpr = checked( $wppb_mci_settings['lists'][$mci_list_id]['gdpr'], 'on', false );

            // Add hidden class
            $hidden_class = '';
            if( false )
                $hidden_class = 'hidden';

            $output .= '<div class="wppb-mci-list-setting wppb-mci-list-setting-gdpr ' . $hidden_class . '">';
                $output .= '<input id="wppb-list-data-item-gdpr-' . $mci_list_id . '" name="wppb_mci_settings[lists][' . $mci_list_id . '][gdpr]" ' . $checked_gdpr . ' class="wppb-list-data-item" type="checkbox" />';
                $output .= '<label for="wppb-list-data-item-gdpr-' . $mci_list_id . '">' . __( 'Enable GDPR', 'profile-builder' ) . '</label>';
                $output .= '<p class="description">' . sprintf( __( 'If checked will enable GDPR on this list. <a href="%s" target="_blank">You must also enable GDPR on the list from mailchimp</a>', 'profile-builder' ), 'https://mailchimp.com/help/collect-consent-with-gdpr-forms/#Set_Up_Your_GDPR-Friendly_Signup_Form' ) . '</p>';
            $output .= '</div>';
        $output .= '</div>';

        return $output;
    }


    /*
     * Function that is used to sanitize the values passed on saving the settings page
     *
     * @since v.1.0.0
     *
     * @param array $wppb_mci_settings      - The settings option array to save for MailChimp
     *
     * @return string
     *
     */
    function wppb_in_mci_settings_sanitize( $wppb_mci_settings ) {

        // Sanitize the API key
        // Let's consider the api is valid
        $wppb_mci_api_key_validated = true;

        // Get api key value that will be saved
        isset( $wppb_mci_settings['api_key'] ) ? $wppb_mci_api_key = $wppb_mci_settings['api_key'] : $wppb_mci_api_key = '';

        // The MailChimp api key has a dash in it, if we don't find it set the api as invalid
        if( strpos( $wppb_mci_api_key, '-' ) === false )
            $wppb_mci_api_key_validated = false;

        // Ping MailChimp for status
        $api = new WPPB_IN_MailChimp( $wppb_mci_api_key );
        $api_ping = $api->get('ping');

        // If MailChimp returns an error set the api as invalid
        if( isset( $api_ping['health_status'] ) && $api_ping['health_status'] === 'Everything’s Chimpy!' || !isset($api_ping['health_status']) )
            $wppb_mci_api_key_validated = false;

        // Throw error in case the api key is not valid and update the validated options
        // Throw error if for some reason the ping back returns false
        // Else update api key validated option to true
        if( $wppb_mci_api_key_validated == false ) {

            if( empty( $wppb_mci_settings['api_key'] ) ) {
                add_settings_error( 'wppb_mci_settings_error', 'mailchimp-api-key-empty', __( 'MailChimp API key is empty', 'profile-builder' ) );
            } else {
                add_settings_error( 'wppb_mci_settings_error', 'mailchimp-api-key-invalid', __( 'MailChimp API key is invalid', 'profile-builder' ) );
            }

            update_option( 'wppb_mailchimp_api_key_validated' , $wppb_mci_api_key_validated );

        } elseif( $wppb_mci_api_key_validated == true && $api_ping === false ) {

            add_settings_error( 'wppb_mci_settings_error', 'mailchimp-api-key-false', __( 'Something went wrong. Either the API key is invalid or we could not connect to MailChimp to validate the key.', 'profile-builder' ) );
            update_option( 'wppb_mailchimp_api_key_validated' , false );

        } else {
            update_option( 'wppb_mailchimp_api_key_validated' , $wppb_mci_api_key_validated );
        }

        // Get current settings
        $wppb_mci_settings_current = get_option( 'wppb_mci_settings' );
        wppb_in_mci_api_get_lists_settings( $wppb_mci_settings['api_key'] );

        // Get settings from db if the lists element is missing
        if( !isset( $wppb_mci_settings['lists'] ) ) {

            // If we still don't find any lists get lists from MailChimp
            if( !isset( $wppb_mci_settings_current['lists'] ) || empty( $wppb_mci_settings_current['lists'] ) ) {
                $wppb_mci_settings['lists'] = wppb_in_mci_api_get_lists_settings( $wppb_mci_settings['api_key'] );
            } else {
                $wppb_mci_settings['lists'] = $wppb_mci_settings_current['lists'];
            }

        }

        // Sanitize each list
        foreach( $wppb_mci_settings['lists'] as $key => $wppb_mci_field_settings ) {

            if( !isset( $wppb_mci_field_settings['double_opt_in'] ) ) {
                if( !isset( $wppb_mci_settings_current['lists'][$key]['double_opt_in'] ) )
                    $wppb_mci_settings['lists'][$key]['double_opt_in'] = 'on';
                else
                    $wppb_mci_settings['lists'][$key]['double_opt_in'] = 'off';
            }

            if( !isset( $wppb_mci_field_settings['gdpr'] ) )
                $wppb_mci_settings['lists'][$key]['gdpr'] = 'off';

        }

        return $wppb_mci_settings;
    }


    /*
    * Function that pushes settings errors to the user
    *
    * @since v.1.0.0
    */
    function wppb_in_mci_settings_admin_notices() {
        settings_errors( 'wppb_mci_settings_error' );
    }
    add_action( 'admin_notices', 'wppb_in_mci_settings_admin_notices' );