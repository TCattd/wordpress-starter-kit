<?php

/**
 * The code that runs during plugin activation.
 */

if( !function_exists( 'wppb_in_field_visibility_activation' ) ){

    function wppb_in_field_visibility_activation( $addon ) {
        if( $addon == 'field-visibility' ){

            $manage_fields = get_option( 'wppb_manage_fields' );
            $filter_fields = array(
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

            foreach( $manage_fields as $key => $field ) {
                if( in_array( $field['field'], $filter_fields ) ) {
                    if( !isset( $field['visibility'] ) ) {
                        $manage_fields[$key]['visibility'] = 'all';
                    }

                    if( !isset( $field['user-role-visibility'] ) ) {
                        $manage_fields[$key]['user-role-visibility'] = 'all';
                    }

                    if( !isset( $field['location-visibility'] ) ) {
                        $manage_fields[$key]['location-visibility'] = 'all';
                    }
                }
            }

            update_option( 'wppb_manage_fields', $manage_fields);

        }
    }
    add_action( 'wppb_add_ons_activate', 'wppb_in_field_visibility_activation', 10, 1);

}
