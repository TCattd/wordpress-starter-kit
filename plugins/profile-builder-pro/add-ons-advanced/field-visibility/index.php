<?php
    /*
    Profile Builder - Field Visibility Add-On
    License: GPL2

    == Copyright ==
    Copyright 2014 Cozmoslabs (www.cozmoslabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */


    /*
     * Define plugin path and include dependencies
     *
     */
    define('WPPBFV_IN_PLUGIN_DIR', plugin_dir_path(__FILE__));
    define('WPPBFV_IN_PLUGIN_URL', plugin_dir_url(__FILE__));

    /*
     * Function that enqueues the necessary scripts
     *
     * @since v.1.0.0
     */
    function wppb_in_field_visibility_scripts_and_styles($hook) {
        if ( $hook == 'profile-builder_page_manage-fields' ) {
            wp_enqueue_script('wppb-field-visibility', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery', 'wppb-manage-fields-live-change'));
            wp_enqueue_style('wppb-field-visibility', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        }
    }
    add_action( 'admin_enqueue_scripts', 'wppb_in_field_visibility_scripts_and_styles' );


    /*
     * Function that returns the fields that will have visibility properties
     * must match the ones in assets/main.js
     *
     * @since v.1.0.0
     *
     * @return array
     */
    function wppb_in_field_visibility_get_extra_fields() {
        $extra_fields = array(
            'default-name-heading'             => 'Default - Name (Heading)',
            'default-contact-info-heading'     => 'Default - Contact Info (Heading)',
            'default-about-yourself-heading'   => 'Default - About Yourself (Heading)',
            'default-username'                 => 'Default - Username',
            'default-first-name'               => 'Default - First Name',
            'default-last-name'                => 'Default - Last Name',
            'default_nickname'                 => 'Default - Nickname',
            'default-e-mail'                   => 'Default - E-mail',
            'default-website'                  => 'Default - Website',
            'default-password'                 => 'Default - Password',
            'default-repeat-password'          => 'Default - Repeat Password',
            'default-biographical-info'        => 'Default - Biographical Info',
            'default-display-name-publicly-as' => 'Default - Display name publicly as',
            'checkbox'                         => 'Checkbox',
            'toa'                              => 'Checkbox (Terms and Conditions)',
            'radio'                            => 'Radio',
            'datepicker'                       => 'Datepicker',
            'timepicker'                       => 'Timepicker',
            'colorpicker'                      => 'Colorpicker',
            'input'                            => 'Input',
            'input_hidden'                     => 'Input (Hidden)',
            'number'                           => 'Number',
            'textarea'                         => 'Textarea',
            'phone'                            => 'Phone',
            'select'                           => 'Select',
            'multiple_select'                  => 'Select (Multiple)',
            'country_select'                   => 'Select (Country)',
            'cpt_select'                       => 'Select (CPT)',
            'timezone_select'                  => 'Select (Timezone)',
            'currency_select'                  => 'Select (Currency)',
            'select-user-role'                 => 'Select (User Role)',
            'upload'                           => 'Upload',
            'avatar'                           => 'Avatar',
            'wysiwyg'                          => 'WYSIWYG',
            'heading'                          => 'Heading',
            'html'                             => 'HTML',
            'select2'                          => 'Select2',
            'select2_multiple'                 => 'Select2 (Multiple)',
            'repeater'                         => 'Repeater',
            'email'                            => 'Email',
            'url'                              => 'URL',
            'map'                              => 'Map'
        );

        return apply_filters( 'wppb_field_visibility_extra_fields', $extra_fields );
    }

/*
* Function that maps default user locked fields to their hooks in Profile Builder
*
* @since v.1.1.6
*
* @return array
*/
function wppb_in_field_visibility_get_default_fields() {
    return array(
        'default-username'                 => 'username',
        'default-first-name'               => 'firstname',
        'default-last-name'                => 'lastname',
        'default_nickname'                 => 'nickname',
        'default-e-mail'                   => 'email',
        'default-website'                  => 'website',
        'default-biographical-info'        => 'description',
        'default-display-name-publicly-as' => 'display-name'
    );
}

/*
* Function that maps default user locked fields to their get_the_author_meta() alternatives
*
* @since v.1.1.6
*
* @return array
*/
function wppb_in_field_visibility_get_author_meta_fields() {
    return array(
        'Default - Username'                 => 'user_login',
        'Default - First Name'               => 'first_name',
        'Default - Last Name'                => 'last_name',
        'Default - Nickname'                 => 'nickname',
        'Default - E-mail'                   => 'user_email',
        'Default - Website'                  => 'user_url',
        'Default - Biographical Info'        => 'user_description',
        'Default - Display name publicly as' => 'display_name'
    );
}


    /*
     * Function adds the visibility and user role visibility radio and checkbox options in the field property from Manage Fields
     *
     * @since v.1.0.0
     *
     * @param array $fields - The current field properties
     *
     * @return array        - The field properties that now include the visibility and user role visibility properties
     */
    function wppb_in_field_visibility_properties_manage_field( $fields ) {
        global $wp_roles;

        $user_roles = array( '%All%all' );
        foreach( $wp_roles->roles as $user_role_slug => $user_role ) {
            if( function_exists( 'wppb_prepare_wck_labels' ) )
                $user_role_name = wppb_prepare_wck_labels($user_role['name']);
            else
                $user_role_name = trim( str_replace( '%', '&#37;', $user_role['name'] ) );

            $user_role_name = stripslashes($user_role_name);

            array_push($user_roles, '%' . $user_role_name . '%' . $user_role_slug);
        }

        $visibility_properties = array(
            array( 'type' => 'select', 'slug' => 'visibility', 'title' => __( 'Visibility', 'profile-builder' ), 'options' => array( '%All%all', '%Admin Only%admin_only', '%User Locked%user_locked' ), 'default' => 'all', 'description' => __( "<strong>Admin Only</strong> field is visible only for administrators. <strong>User Locked</strong> field is visible for both administrators and users, but only administrators have the capability to edit it.", 'profile-builder' ) ),
            array( 'type' => 'checkbox', 'slug' => 'user-role-visibility', 'title' => __( 'User Role Visibility', 'profile-builder' ), 'options' => $user_roles, 'default' => 'all', 'description' => __( "Select which user roles see this field", 'profile-builder' ) ),
            array( 'type' => 'checkbox', 'slug' => 'location-visibility', 'title' => __( 'Location Visibility', 'profile-builder' ), 'options' => array( '%All%all', '%WordPress Edit Profile Form (back-end)%back_end', '%Register Forms Front-End%register', '%Edit Profile Forms Front-End%edit_profile'), 'default' => 'all', 'description' => __( "Select the locations you wish the field to appear", 'profile-builder' ) )
        );

        foreach( $visibility_properties as $field_property )
            array_push( $fields, $field_property );

        return $fields;
    }
    add_filter( 'wppb_manage_fields', 'wppb_in_field_visibility_properties_manage_field' );


    /*
     * Function that adds a column to the manage fields header for the visibility option
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_manage_fields_header_add_visibility( $list_header ){
        return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( '<pre>Title</pre><pre>Type</pre><pre>Meta Name</pre><pre class="wppb-mb-head-required">Required</pre><pre class="wppb-mb-head-visibility"></pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
    }
    add_action( 'wck_metabox_content_header_wppb_manage_fields', 'wppb_in_manage_fields_header_add_visibility', 11 );


    /*
     * Function that changes the displayed value for the visibility property of the field
     * to a representative icon
     *
     * @since v.1.0.0
     *
     * @param string $display_value     - The saved value of the field in <pre> tag
     *
     */
    function wppb_in_change_display_value_to_icon_visibility( $display_value ) {
        $visibility = strtolower( str_replace( ' ', '_', str_replace( '<pre>', '', str_replace( '</pre>', '', $display_value ) )) );

        if( $visibility == 'all' )
            return;

        if( $visibility == 'admin_only' )
            return '<span title="' . __( 'This field is visible only for administrators.', 'profile-builder' ) . '" class="wppb-manage-fields-dashicon dashicons dashicons-visibility"></span>';

        if( $visibility == 'user_locked' )
            return '<span title="' . __( 'This field is visible for both administrators and users, but only administrators have the capability to edit it.', 'profile-builder' ) . '" class="wppb-manage-fields-dashicon dashicons dashicons-lock"></span>';

        return $display_value;
    }
    add_filter( 'wck_pre_displayed_value_wppb_manage_fields_element_visibility', 'wppb_in_change_display_value_to_icon_visibility' );


    /*
     * Function that changes the displayed value for the user role visibility property of the field
     * to a representative icon
     *
     * @since v.1.0.0
     *
     * @param string $display_value     - The saved value of the field in <pre> tag
     *
     */
    function wppb_in_change_display_value_to_icon_user_role_visibility( $display_value ) {
        $visibility_string = str_replace( '<pre>', '', str_replace( '</pre>', '', $display_value ) );
        $visibility = explode(', ', $visibility_string);

        if( in_array( 'all', $visibility ) )
            return;
        elseif( !empty( $visibility[0] ) )
            return '<span title="' . sprintf( __( 'This field is visible only for the following user roles: %1$s', 'profile-builder' ), $visibility_string ) . '" class="wppb-manage-fields-dashicon dashicons dashicons-admin-users"></span>';

        return $display_value;
    }
    add_filter( 'wck_pre_displayed_value_wppb_manage_fields_element_user-role-visibility', 'wppb_in_change_display_value_to_icon_user_role_visibility' );


    /*
     * Function that changes the displayed value for the location visibility property of the field
     * to a representative icon
     *
     * @since v.1.0.1
     *
     * @param string $display_value     - The saved value of the field in <pre> tag
     *
     */
    function wppb_in_change_display_value_to_icon_location_visibility( $display_value ) {
        $form_locations = array( 'register', 'edit_profile', 'back_end' );

        $is_visible_all_locations = true;

        $visibility_locations_string = str_replace( '<pre>', '', str_replace( '</pre>', '', $display_value ) );
        $visibility_locations = explode(', ', $visibility_locations_string);

        $form_locations_not_shown_in = array_diff( $form_locations, $visibility_locations );

        if( !empty($form_locations_not_shown_in) ) {
            $is_visible_all_locations = false;
        }

        foreach( $visibility_locations as $key => $visibility_location ) {

            if( $visibility_location == 'back_end' )
                $visibility_locations[$key] = 'WordPress Edit Profile Form (back-end)';

            if( $visibility_location == 'register' )
                $visibility_locations[$key] = 'Register Forms Front-End';

            if( $visibility_location == 'edit_profile' )
                $visibility_locations[$key] = 'Edit Profile Forms Front-End';
        }
        $visibility_locations_string = implode( ', ', $visibility_locations );

        if( in_array( 'all', $visibility_locations ) || $is_visible_all_locations == true )
            return;
        elseif( !empty( $visibility_locations[0] ) )
            return '<span title="' . sprintf( __( 'This field is visible only in the following locations: %1$s', 'profile-builder' ), $visibility_locations_string ) . '" class="wppb-manage-fields-dashicon dashicons dashicons-location"></span>';

        return $display_value;
    }
    add_filter( 'wck_pre_displayed_value_wppb_manage_fields_element_location-visibility', 'wppb_in_change_display_value_to_icon_location_visibility' );


    /*
     * Function that handles the visibility of the field
     *
     * @since v.1.0.0
     *
     * @param bool $display_field      - By default true, to continue displaying the field
     * @param array $field             - The current field
     * @param string $form_location    - The location of the form. It can be register, edit_profile and back_end
     * @param string $form_role        - The role that will be attributed by default to new users
     * @param int $user_id
     *
     * @return bool
     */
    function wppb_in_handle_output_display_state( $display_field, $field, $form_location, $form_role, $user_id ) {

        if( !in_array( $field['field'], wppb_in_field_visibility_get_extra_fields() ) )
            return $display_field;

        // Handle visibility by location
        if( isset( $field['location-visibility'] ) ) {
            $field_location_visibility = explode(', ', $field['location-visibility'] );

            if( !empty( $field['location-visibility'] ) && !in_array( 'all', $field_location_visibility ) && !in_array( $form_location, $field_location_visibility ) ) {
                return false;
            }
        }

        //Handle visibility for register form
        if( $form_location == 'register' ) {

            // Visibility for User Locked option
            if( !current_user_can( apply_filters( 'wppb_fv_capability_user_locked', 'manage_options' ) ) ) {
                if (isset($field['visibility']) && ($field['visibility'] == 'user_locked')) {
                    $display_field = false;
                }
            }

            if( isset( $field['user-role-visibility'] ) ) {
                $user_roles_visibility = explode(', ', $field['user-role-visibility']);

                if( !in_array( 'all', $user_roles_visibility ) && !empty($field['user-role-visibility']) ) {
                    if (!in_array($form_role, $user_roles_visibility)) {
                        $display_field = false;
                    }
                }
            }
        }

        //Handle visibility for edit profile form in front end
        if( $form_location == 'edit_profile' || $form_location == 'register' ) {

            // Visibility for Admin Only option
            if( !current_user_can( apply_filters( 'wppb_fv_capability_admin_only', 'manage_options' ) ) ) {
                if( isset( $field['visibility'] ) && ( $field['visibility'] == 'admin_only' ) ) {
                    $display_field = false;
                }
            }

        }

        //Handle visibility for edit profile form in back end
        if( $form_location == 'back_end' ) {

            // Visibility for Admin Only option
            if( !current_user_can( apply_filters( 'wppb_fv_capability_admin_only', 'manage_options' ) ) ) {
                if( isset( $field['visibility'] ) && ( $field['visibility'] == 'admin_only' ) ) {
                    $display_field = false;
                }
            }
        }

        //Handle visibility for edit profile form in front end and back end
        if( $form_location == 'edit_profile' || $form_location == 'back_end' ) {

            // Visibility for User Roles
            if( isset( $field['user-role-visibility'] ) ) {
                $user = get_user_by( 'id', $user_id );
                $user_user_roles = $user->roles;
                $user_roles_visibility = explode(', ', $field['user-role-visibility']);

                if( !in_array( 'all', $user_roles_visibility ) && !empty($field['user-role-visibility']) ) {
                    if( !array_intersect( $user_user_roles, $user_roles_visibility ) ) {
                        $display_field = false;
                    }
                }
            }

        }

        return $display_field;
    }

    /*
     * Function that modifies the default HTML of the field if the field is a user locked field
     *
     * @since v.1.0.0
     *
     * @param string $output            - The current HTML
     * @param string $form_location     - The location of the form
     * @param array $field              - The current field
     * @param int $user_id
     * @param $field_check_errors
     * @param $request_data
     * @param $input_value
     *
     * @return string
     */
    function wppb_in_handle_field_output( $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value = '' ) {

        // Heading fields
        if( strpos( strtolower($field['field']), 'heading' ) !== false )
            return $output;

        // Field output
        $field_output   = '';
        $initial_output = $output;

        $default_get_the_author_fields = wppb_in_field_visibility_get_author_meta_fields();
        if ( empty($input_value) ){
                if ( array_key_exists($field['field'], $default_get_the_author_fields) ){
                    $input_value = get_the_author_meta($default_get_the_author_fields[$field['field']], $user_id);
                }
        }

        // Fields display for User Locked feature
        if( !current_user_can( apply_filters( 'wppb_fv_capability_user_locked', 'manage_options' ) ) ) {
            if (isset($field['visibility']) && ($field['visibility'] == 'user_locked')) {

                // Upload field
                if( 'Upload' == $field['field'] ) {

                    if( !empty( $input_value ) ) {
                        if (is_numeric($input_value)) {
                            $input_value = wp_get_attachment_url($input_value);
                        }
                        $field_output = apply_filters('wppb_field_user_locked_' . $field['meta-name'], '<div><a target="_blank" href="' . esc_attr($input_value) . '">' . __('Get file', 'profile-builder') . '</a></div>', $input_value);
                    }

                // Textarea
                } elseif( 'WYSIWYG' == $field['field'] || 'Textarea' == $field['field'] ) {

                    $field_output = '<section>' . apply_filters('the_content', $input_value) . '</section>';

                // Select Currency
                } elseif( 'Select (Currency)' == $field['field'] && function_exists( 'wppb_get_currencies' ) ) {

                    $currencies = wppb_get_currencies();

                    if (!empty($currencies[$input_value]))
                        $field_output = '<div>' . esc_attr($currencies[$input_value]) . '</div>';

                // Select User Role
                } elseif( 'Select (CPT)' == $field['field'] ) {

                    $field_output = '<div>' . apply_filters( 'wppb_fields_cpt_select_label', esc_html( get_the_title( $input_value ) ), $input_value) . '</div>';

                } elseif( 'Select (User Role)' == $field['field'] ) {

                    global $wp_roles;

                    if( !empty( $wp_roles->roles[$input_value]['name'] ) )
                        $field_output = '<div>' . $wp_roles->roles[$input_value]['name'] . '</div>';

                // Avatar
                } elseif( 'Avatar' == $field['field'] ) {

                    if (!empty($input_value))
                        $field_output = get_avatar($user_id);

                // Checkboxes, selects, multiple selects and radios
                } elseif( 'Checkbox' == $field['field'] || 'Select (Multiple)' == $field['field'] || 'Select2 (Multiple)' == $field['field'] || 'Radio' == $field['field'] || 'Select' == $field['field'] || 'Select2' == $field['field']) {

                    // Set the options and labels as arrays
                    $field_options_arr = array_map( 'trim', explode( ',', $field['options'] ) );
                    $field_labels_arr  = array_map( 'trim', explode( ',', $field['labels'] ) );

                    // Radio has a string value, set is as array
                    if( !is_array( $input_value ) )
                        $input_value = array( $input_value );

                    // Check to see if there are labels for the option
                    // if not, use the option names
                    foreach( $input_value as $key => $single_value ) {
                        $indexes = array_keys( $field_options_arr, $single_value );

                        if( isset($indexes[0]) && !empty( $field_labels_arr[$indexes[0]] ) )
                            $input_value[$key] = $field_labels_arr[$indexes[0]];
                    }

                    // Implode the array for output
                    $input_value = implode( ', ', $input_value );

                    $field_output = '<div>' . esc_attr($input_value) . '</div>';

                } elseif ( 'Map' == $field[ 'field' ] ){

                    $field_output = '<div>' . substr( $initial_output, strpos( $initial_output, '</label>' ) + strlen( '</label>'), strlen( $initial_output ) ) . '</div>';

                } else {

                    $field_output = '<div>' . esc_attr($input_value) . '</div>';

                }

                $initial_output = preg_replace( '/(for|name|id)="[^"]*"/', '', $initial_output );

                $field_output .= '<span style="display: none;">' . $initial_output . '</span>';

            }
        }

        //Handle visibility for edit profile form in front end
        if( $form_location == 'edit_profile' ) {

            // Visibility for User Locked option
            if( !current_user_can( apply_filters( 'wppb_fv_capability_user_locked', 'manage_options' ) ) ) {
                if (isset($field['visibility']) && ($field['visibility'] == 'user_locked')) {

                    $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'] );

                    $output  = '<label>' . $field['field-title'] . '</label>';
                    $output .= $field_output;

                    if( ! empty( $item_description ) )
                        $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

                }
            }

        }

        //Handle visibility for edit profile form in back end
        if( $form_location == 'back_end' ) {

            if( !current_user_can( apply_filters( 'wppb_fv_capability_user_locked', 'manage_options' ) ) ) {
                if (isset($field['visibility']) && ($field['visibility'] == 'user_locked')) {

                    if( $field[ 'field' ] != 'Map' ){

                    $output = '
                        <table class="form-table">
                            <tr>
                                <th><label for="'.$field['meta-name'].'">'.$field['field-title'].'</label></th>
                                <td>
                                    ' . $field_output . '
                                </td>
                            </tr>
                        </table>';

                    }
                }
            }

        }

        return $output;
    }


    /**
     * Checks to see if a user_locked field has values set when saving the form. It should not, and
     * if it does an error is printed for that form, preventing the form values to be saved
     *
     */
    function wppb_in_fv_check_if_user_locked( $message, $field, $request_data, $form_location, $form_role = '', $user_id = 0 ) {
        if( empty( $field['visibility'] ) )
            return $message;

        if( $field['visibility'] != 'user_locked' )
            return $message;

        if( current_user_can( apply_filters( 'wppb_fv_capability_user_locked', 'manage_options' ) ) )
            return $message;

        if( ! empty( $request_data[ $field['meta-name'] ] ) )
            if( $field[ 'field' ] != 'Map' )
                $message = __( 'You do not have the capabilities necessary to edit this field.', 'profile-builder' );

        return $message;

    }


    function wppb_in_fv_check_field_value( $message, $field, $request_data, $form_location, $form_role = '', $user_id = 0 ) {

        if( $field['required'] != 'Yes' )
            return $message;


        /*
         * Skip field validation if field is not in the form
         */
        if( isset( $field['location-visibility'] ) ) {
            $field_location_visibility = explode(', ', $field['location-visibility'] );

            if( !empty( $field['location-visibility'] ) && !in_array( 'all', $field_location_visibility ) && !in_array( $form_location, $field_location_visibility ) ) {
                $message = '';
            }
        }

        /*
         * Skip field validation if field is visible only by admins or is user locked
         */
        if( isset( $field['visibility'] ) ) {

            if( !current_user_can( apply_filters( 'wppb_fv_capability_user_locked', 'manage_options' ) ) && $field['visibility'] == 'user_locked' ) {
                $message = '';
            }

            if( !current_user_can( apply_filters( 'wppb_fv_capability_admin_only', 'manage_options' ) ) && $field['visibility'] == 'admin_only' ) {
                $message = '';
            }

        }


        /*
         * Skip field validation for user roles
         */
        if( $form_location == 'register' ) {

            if( isset( $field['user-role-visibility'] ) ) {
                $user_roles_visibility = explode(', ', $field['user-role-visibility']);

                if( !in_array( 'all', $user_roles_visibility ) && !empty($field['user-role-visibility']) ) {
                    if (!in_array($form_role, $user_roles_visibility)) {
                        $message = '';
                    }
                }
            }

        }

        if( $form_location == 'edit_profile' || $form_location == 'back_end' ) {

            if( isset( $field['user-role-visibility'] ) ) {
                $user = get_user_by( 'id', $user_id );

                if( $user ) {
                    $user_user_roles = $user->roles;
                    $user_roles_visibility = explode(', ', $field['user-role-visibility']);

                    if( !in_array( 'all', $user_roles_visibility ) && !empty($field['user-role-visibility']) ) {
                        if( !array_intersect( $user_user_roles, $user_roles_visibility ) ) {
                            $message = '';
                        }
                    }
                }
            }

        }

        return $message;
    }


    /*
     * Function that adds the necessary filters in order to change the output of the fields
     *
     * @since v.1.0.0
     *
     */
    function wppb_in_init_field_visibility() {
        $manage_fields = get_option( 'wppb_manage_fields' );
        $filter_fields = wppb_in_field_visibility_get_extra_fields();
        $filter_default_fields = wppb_in_field_visibility_get_default_fields();
        // add filters for the fields

        if( is_array($manage_fields) ) {
            foreach ($manage_fields as $field) {
                foreach ($filter_fields as $filter_field_slug => $filter_field) {
                    if (array_key_exists($filter_field_slug, $filter_default_fields)) {

                        add_filter('wppb_register_' . $filter_default_fields[$filter_field_slug], 'wppb_in_handle_field_output', 10, 6);
                        add_filter('wppb_edit_profile_' . $filter_default_fields[$filter_field_slug], 'wppb_in_handle_field_output', 10, 6);
                        add_filter('wppb_back_end_' . $filter_default_fields[$filter_field_slug], 'wppb_in_handle_field_output', 10, 6);

                    } else {
                        add_filter('wppb_register_' . $filter_field_slug . '_custom_field_' . $field['id'], 'wppb_in_handle_field_output', 10, 6);
                        add_filter('wppb_edit_profile_' . $filter_field_slug . '_custom_field_' . $field['id'], 'wppb_in_handle_field_output', 10, 7);
                        add_filter('wppb_back_end_' . $filter_field_slug . '_custom_field_' . $field['id'], 'wppb_in_handle_field_output', 10, 7);
                    }
                }
            }
        }

        foreach( $filter_fields as $filter_field_slug => $filter_field ) {
            if( class_exists('Wordpress_Creation_Kit_PB') ) {
                add_filter('wppb_check_form_field_' . Wordpress_Creation_Kit_PB::wck_generate_slug( $filter_field ), 'wppb_in_fv_check_field_value', 11, 6);
                add_filter('wppb_check_form_field_' . Wordpress_Creation_Kit_PB::wck_generate_slug( $filter_field ), 'wppb_in_fv_check_if_user_locked', 11, 6);
            }
        }

        add_filter( 'wppb_output_display_form_field', 'wppb_in_handle_output_display_state', 10, 5 );

    }
    add_action( 'init', 'wppb_in_init_field_visibility' );
