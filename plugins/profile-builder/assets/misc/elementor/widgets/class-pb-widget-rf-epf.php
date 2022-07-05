<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once "class-pb-widget-base.php";

/**
 * Base class for the Profile Builder Elementor Registration and Edit Profile widgets
 */
abstract class PB_Elementor_Register_Edit_Profile_Widget extends PB_Elementor_Widget {

    /**
     * Register scripts and styles needed in the Visual Editor.
     */
    protected function register_pb_scripts_styles() {
        //Select2
        wp_register_script('wppb_sl2_lib_js', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array('jquery'));

        wp_register_style('wppb_sl2_lib_css', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css');

        //SelectCPT
        wp_register_script( 'wppb_select2_js', WPPB_PLUGIN_URL .'assets/js/select2/select2.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
        wp_register_style( 'wppb_select2_css', WPPB_PLUGIN_URL .'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION );

        if( defined( 'WPPB_PAID_PLUGIN_URL' ) ){
            wp_register_style( 'wppb_sl2_css', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/select2/select2.css', false, PROFILE_BUILDER_VERSION );
            wp_register_style( 'wppb-select-cpt-style', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/select-cpt/style-front-end.css', array(), PROFILE_BUILDER_VERSION );

            //Upload
            wp_register_style( 'profile-builder-upload-css', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/upload/upload.css', false, PROFILE_BUILDER_VERSION );

            //Multi-Step Forms compatibility
            wp_register_style( 'wppb-msf-style-frontend', WPPB_PAID_PLUGIN_URL.'add-ons-advanced/multi-step-forms/assets/css/frontend-multi-step-forms.css', array(), PROFILE_BUILDER_VERSION );
        }
    }

    public function get_script_depends() {
        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists(WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/extra-fields.php') ) {
            return [
                'wppb_sl2_lib_js',
                'wppb_select2_js',
            ];
        }
        return [];
    }

    public function get_style_depends() {
        $styles = [];
        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists(WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/extra-fields.php') ) {
            $styles = [
                'wppb_sl2_lib_css',
                'wppb_sl2_css',
                'profile-builder-upload-css',
                'wppb_select2_css',
                'wppb-select-cpt-style',
            ];
        }

        if ( wppb_check_if_add_on_is_active( 'multi-step-forms' ) ) {
            $styles[] = 'wppb-msf-style-frontend';
        }

        return $styles;
    }

    /**
     * Add the controls for the Edit Profile and Registration widgets.
     * @param $form_type
     */
    protected function register_rf_epf_controls( $form_type ) {
        switch ( $form_type ){
            case 'rf':
                $section_id_prefix = 'pb_register';
                $post_type = 'wppb-rf-cpt';
                $fields_post_meta_key = 'wppb_rf_fields';
                break;
            case 'epf':
                $section_id_prefix = 'pb_edit_profile';
                $post_type = 'wppb-epf-cpt';
                $fields_post_meta_key = 'wppb_epf_fields';
                break;
            default:
                return;
        }

        $wppb_module_settings = get_option( 'wppb_module_settings', 'not_found' );

        $this->start_controls_section(
            sprintf( '%s_settings_section', $section_id_prefix ),
            array(
                'label' => __( 'Form Settings', 'profile-builder' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $edit_form_links = array(
            'default' => ''
        );
        $form_titles = array(
            '' => __( 'Default', 'profile-builder' )
        );
        $form_fields = array(
            'default' => get_option( 'wppb_manage_fields' )
        );
        $social_connect_settings = get_option( 'wppb_social_connect_settings' );
        if ( is_array($social_connect_settings) ) {
            $social_connect_settings = reset($social_connect_settings);
        }
        $social_connect = [];

        if ( !( ( $wppb_module_settings !== 'not_found' && ( (
                    $form_type === 'rf' && (
                        !isset( $wppb_module_settings['wppb_multipleRegistrationForms'] ) ||
                        $wppb_module_settings['wppb_multipleRegistrationForms'] !== 'show'
                        )
                    ) || (
                    $form_type === 'epf' && (
                        !isset( $wppb_module_settings['wppb_multipleEditProfileForms'] ) ||
                        $wppb_module_settings['wppb_multipleEditProfileForms'] !== 'show'
                        )
                    ) )
                ) ||
            $wppb_module_settings === 'not_found'
            )
        ){
            $args = array(
                'post_type'      => $post_type,
                'posts_per_page' => -1
            );

            $the_query = new WP_Query( $args );

            if ( $the_query->have_posts() ) {
                foreach ( $the_query->posts as $post ) {
                    $form_titles      ['-'.Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title )] = $post->post_title ;
                    $edit_form_links  [    Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title )] = get_edit_post_link($post->ID);
                    $form_fields      [    Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title )] = get_post_meta($post->ID, $fields_post_meta_key, true);
                    $social_connect   [    Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title )] = get_post_meta($post->ID, 'wppb_sc_rf_epf_active', true);
                    $msf_break_points [    Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title )] = get_post_meta($post->ID, 'wppb_msf_break_points', true);
                }
                wp_reset_postdata();
            }
        }

        $this->add_control(
            'pb_form_name',
            array(
                'label'   => __('Form', 'profile-builder' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => $form_titles,
                'default' => '',
            )
        );

        if ( $form_type === 'rf' ) {
            if (!function_exists('get_editable_roles')) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }
            $user_roles = get_editable_roles();
            foreach ($user_roles as $key => $role) {
                $user_roles[$key] = $role['name'];
            }

            $this->add_control(
                'pb_role',
                array(
                    'label' => __('Assigned Role', 'profile-builder'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $user_roles,
                    'default' => get_option('default_role'),
                    'condition' => [
                        'pb_form_name' => '',
                    ],
                )
            );

            $this->add_control(
                'pb_automatic_login',
                array(
                    'label' => __('Automatic Login', 'profile-builder'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Yes', 'profile-builder'),
                    'label_off' => __('No', 'profile-builder'),
                    'return_value' => 'yes',
                    'default' => '',
                    'condition' => [
                        'pb_form_name' => '',
                    ],
                )
            );
        }

        foreach ( $edit_form_links as $form_slug => $edit_form_link ){
            foreach ($form_fields[$form_slug] as $key_1 => $form_field) {
                if ($form_slug === 'default') {
                    $form_fields['default'][$key_1]['control_group_conditions']['pb_form_name'][] = '';
                    continue;
                }
                foreach ($form_fields['default'] as $key_2 => $form_field_default) {
                    if ($form_field_default['id'] === $form_field['id']) {
                        $form_fields['default'][$key_2]['control_group_conditions']['pb_form_name'][] = '-'.$form_slug;
                    }
                }
            }
            if( $form_slug === 'default' ){
                continue;
            }

            // Edit form links
            $this->add_control(
                'pb_form_'.$form_slug.'_edit_link' ,
                array(
                    'type'     => \Elementor\Controls_Manager::RAW_HTML,
                    'raw'      => sprintf( __( 'Edit the Settings for this form %1$shere%2$s' , 'profile-builder' ), '<a href="'.esc_url( $edit_form_link ).'" target="_blank">', '</a>'),
                    'condition'=> [
                        'pb_form_name' => [ '-'.$form_slug ],
                    ],
                )
            );
        }

        $this->end_controls_section();

        $params = [
            'label'     => __( 'Redirects', 'profile-builder' ),
            'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
        ];
        if ( $form_type === 'epf' ){
            $params['condition'] = [ 'pb_form_name' => '' ];
        }

        $this->start_controls_section( $section_id_prefix.'_redirects_section', $params );

        unset($params);
        $params = [
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'Enter URL', 'profile-builder' ),
            'condition'   => [
                'pb_form_name'=>'',
            ],
        ];
        if ( $form_type === 'rf' ){
            $params['label'] = __( 'Redirect after Registration', 'profile-builder' );
        } else {
            $params['label'] = __( 'Redirect after Edit Profile', 'profile-builder' );
        }

        $this->add_control( 'pb_redirect_url', $params );

        if ( $form_type === 'rf' ) {
            $this->add_control(
                'pb_logout_redirect_url',
                array(
                    'label' => __('Redirect after Logout', 'profile-builder'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => __('Enter URL', 'profile-builder'),
                )
            );
        }

        $this->end_controls_section();

        // Style controls for the form fields
        $this->add_fields_styling( $form_fields['default'], $form_type );

        // Style controls for the 'Send these credentials via email' checkbox
        if ( $form_type === 'rf' ) {
            $this->add_styling_control_group(
                'Send Credentials Checkbox',
                '',
                'pb_register_' . $form_slug . '_wppb_send_credentials',
                [
                    'checkbox' => [
                        'selector' => '.wppb-register-user .wppb-send-credentials-checkbox #send_credentials_via_email',
                        'section_name' => 'Checkbox',
                    ],
                    'label' => [
                        'selector' => '.wppb-register-user .wppb-send-credentials-checkbox label[for=send_credentials_via_email]',
                        'section_name' => 'Label',
                    ]
                ]
            );
        }

        // Style controls for the 'Two-Factor Authentication' field group
        if ( $form_type === 'epf' && $this->is_2fa_active() ) {
            $this->add_styling_control_group(
                'Two-Factor Authentication',
                '',
                'pb_edit_profile_' . $form_slug . '_2fa',
                [
                    '2fa_heading' => [
                        'selector' => '.wppb-2fa-fields .wppb_2fa_heading h4',
                        'section_name' => 'Heading',
                    ],
                    '2fa_activate_label' => [
                        'selector' => '.wppb-2fa-fields label[for=wppb_auth_enabled]',
                        'section_name' => 'Activate Label',
                    ],
                    '2fa_activate_checkbox' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_enabled',
                        'section_name' => 'Activate Checkbox',
                    ],
                    '2fa_relaxed_label' => [
                        'selector' => '.wppb-2fa-fields label[for=wppb_auth_relaxedmode]',
                        'section_name' => 'Relaxed Mode Label',
                    ],
                    '2fa_relaxed_checkbox' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_relaxedmode',
                        'section_name' => 'Relaxed Mode Checkbox',
                    ],
                    '2fa_description_label' => [
                        'selector' => '.wppb-2fa-fields label[for=wppb_auth_description]',
                        'section_name' => 'Description Label',
                    ],
                    '2fa_description_input' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_description',
                        'section_name' => 'Description Input',
                    ],
                    '2fa_description_description' => [
                        'selector' => '.wppb-2fa-fields .wppb-description-delimiter',
                        'section_name' => 'Description Description',
                    ],
                    '2fa_secret_label' => [
                        'selector' => '.wppb-2fa-fields label[for=wppb_auth_secret]',
                        'section_name' => 'Secret Label',
                    ],
                    '2fa_secret_input' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_secret',
                        'section_name' => 'Secret Input',
                    ],
                    '2fa_new_secret_button' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_secret_buttons #wppb_auth_newsecret',
                        'section_name' => 'New Secret Button',
                    ],
                    '2fa_qr_code_button' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_secret_buttons #wppb_show_qr',
                        'section_name' => 'QR Code Button',
                    ],
                    '2fa_verify_label' => [
                        'selector' => '.wppb-2fa-fields .wppb_auth_verify label[for=wppb_auth_passw]',
                        'section_name' => 'Verify TOTP Label',
                    ],
                    '2fa_verify_input' => [
                        'selector' => '.wppb-2fa-fields .wppb_auth_verify #wppb_auth_passw',
                        'section_name' => 'Verify TOTP Input',
                    ],
                    '2fa_check_button' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_verify_buttons #wppb_auth_verify_button',
                        'section_name' => 'Check Button',
                    ],
                    '2fa_check_indicator' => [
                        'selector' => '.wppb-2fa-fields #wppb_auth_verify_buttons #wppb_auth_verify_indicator',
                        'section_name' => 'Validity Indicator',
                    ],
                ]
            );
        }

        // Style controls for the 'Register'/'Update' button
        if ( $form_type === 'rf' ) {
            $this->add_styling_control_group(
                'Register Button',
                '',
                'pb_register_' . $form_slug . '_register_button',
                [
                    'register_button' => [
                        'selector' => '.wppb-register-user .submit.button',
                        'section_name' => 'Register Button',
                    ]
                ]
            );
        } else {
            $this->add_styling_control_group(
                'Update Button',
                '',
                'pb_edit_profile_'.$form_slug.'_update_button',
                [
                    'update_button' => [
                        'selector'     => '.wppb-edit-user .submit.button',
                        'section_name' => 'Update Button',
                    ]
                ]
            );
        }

        // Style for the Social Connect section
        if ( $social_connect_settings && strpos($social_connect_settings['display-on-the-following-forms'],
            ( $form_type === 'rf' ? 'pb-register' : 'pb-edit-profile' )
            ) ) {
            $conditions = [];

            foreach ( $edit_form_links as $form_slug => $edit_form_link ) {
                if ( $form_slug === 'default' ) {
                    $conditions['pb_form_name'][] = '';
                } elseif ( $social_connect[$form_slug] === 'yes') {
                    $conditions['pb_form_name'][] = '-' . $form_slug;
                }
            }

            $this->add_styling_control_group(
                'Social Connect Section',
                $conditions,
                $section_id_prefix . '_' . $form_slug . '_social_connect',
                [
                    'sc_heading' => [
                        'selector' => '.wppb-sc-heading-before-reg-buttons h3',
                        'section_name' => 'Heading',
                    ],
                    'sc_buttons' => [
                        'selector' => 'a.wppb-sc-button',
                        'section_name' => 'Buttons',
                    ]
                ]
            );
        }

        // Style for the MSF buttons
        if ( wppb_check_if_add_on_is_active( 'multi-step-forms' ) ) {
            $conditions = [];

            foreach ( $edit_form_links as $form_slug => $edit_form_link ) {
                if ( $form_slug === 'default' && !empty( get_option( 'wppb_msf_break_points', false ) ) ) {
                    $conditions['pb_form_name'][] = '';
                } elseif ( !empty( $msf_break_points[$form_slug] ) ) {
                    $conditions['pb_form_name'][] = '-' . $form_slug;
                }
            }

            $sections['msf_default'] = [
                    'selector' => '.wppb-msf-button',
                    'section_name' => 'Default'
            ];
            $sections['msf_pagination'] = [
                'selector' => '.wppb-msf-pagination',
                'section_name' => 'Pagination'
            ];
            $sections['msf_tabs'] = [
                'selector' => '.wppb-msf-tabs',
                'section_name' => 'Tabs'
            ];

            $this->add_styling_control_group(
                'Multi Step Forms Buttons',
                $conditions,
                $section_id_prefix . '_' . $form_slug . '_msf',
                $sections
            );
        }

    }

    /**
     * Deal with special cases. Add the targets for each field. Add the styling control groups.
     * @param $form_fields
     * @param $form_type
     */
    protected function add_fields_styling($form_fields, $form_type ){
        switch ( $form_type ){
            case 'rf':
                $section_id_prefix = 'pb_register';
                $form_class = '.wppb-register-user';
                break;
            case 'epf':
                $section_id_prefix = 'pb_edit_profile';
                $form_class = '.wppb-edit-user';
                break;
            default:
                return;
        }

        foreach ( $form_fields as $form_field ) {
            $targets = [];
            if ($form_field['field-title'] !== '') {
                $targets['label'] = '';
            }
            if ($form_field['description'] !== '') {
                $targets['description'] = '';
            }
            switch ($form_field['field']) {
                case 'Default - Name (Heading)':
                    $field_meta = 'default_name_heading';
                    $targets = $this->replace_label_with_heading( $targets );
                    break;
                case 'Default - Contact Info (Heading)':
                    $field_meta = 'default_contact_info_heading';
                    $targets = $this->replace_label_with_heading( $targets );
                    break;
                case 'Default - About Yourself (Heading)':
                    $field_meta = 'default_about_yourself_heading';
                    $targets = $this->replace_label_with_heading( $targets );
                    break;
                case 'Default - Username':
                    $field_meta = 'default_username';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['username'] = '';
                    break;
                case 'Default - E-mail':
                    $field_meta = 'email';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['input'] = '';
                    break;
                case 'Default - Password':
                    $field_meta = 'passw1';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['input'] = '';
                    break;
                case 'Default - Repeat Password':
                    $field_meta = 'passw2';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['input'] = '';
                    break;
                case 'Default - Display name publicly as':
                    $field_meta = 'default_field_display-name';
                    if ( $form_type === 'rf' ){
                        // this field is only shown on the Edit Profile form
                        unset($targets);
                    } else {
                        $targets['default_field_display'] = '';
                    }
                    break;
                case 'Default - Website':
                    $field_meta = 'website';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['input'] = '';
                    break;
                case 'Heading':
                    $field_meta = 'heading';
                    $targets = $this->replace_label_with_heading( $targets );
                    break;
                case 'HTML':
                    $field_meta = 'html';
                    if ($form_field['html-content'] !== '') {
                        $targets['html'] = '';
                    }
                    break;
                case 'reCAPTCHA':
                    $field_meta = 'recaptcha';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    if ( $form_type === 'rf' ){
                        // this field is only shown on the Registration form
                        unset($targets);
                    }
                    break;
                case 'Select (User Role)':
                    $field_meta = 'select_user_role';
                    if ( $form_type === 'rf' ){
                        $targets['select_user_role'] = '';
                    } else {
                        $targets['select_user_role_notice'] = '';
                    }
                    break;
                case 'GDPR Delete Button':
                    $field_meta = 'gdpr_delete';
                    if ( $form_type === 'rf' ){
                        // this field is only shown on the Edit Profile form
                        unset($targets);
                    } else {
                        $targets['gdpr_delete_button'] = '';
                    }
                    break;
                case 'Email Confirmation':
                    $field_meta = 'wppb_email_confirmation';
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['input'] = '';
                    break;
                case 'Select2':
                    $field_meta = $form_field['meta-name'];
                    $targets['select2'] = '';
                    break;
                case 'Select (CPT)':
                    $field_meta = $form_field['meta-name'];
                    $targets['select_cpt'] = '';
                    break;
                case 'Timepicker':
                    $field_meta = $form_field['meta-name'];
                    $targets['timepicker_hours'] = '';
                    $targets['timepicker_minutes'] = '';
                    $targets['timepicker_separator'] = '';
                    break;
                case 'Checkbox':
                    $field_meta = $form_field['meta-name'];
                    $targets['checkbox'] = '';
                    $targets['checkbox_labels'] = '';
                    break;
                case 'Radio':
                    $field_meta = $form_field['meta-name'];
                    $targets['radio'] = '';
                    $targets['radio_labels'] = '';
                    break;
                case 'Upload':
                    $field_meta = $form_field['meta-name'];
                    $targets['upload'] = '';
                    break;
                case 'Avatar':
                    $field_meta = $form_field['meta-name'];
                    $targets['avatar'] = '';
                    break;
                case 'Checkbox (Terms and Conditions)':
                    $field_meta = $form_field['meta-name'];
                    if ( $form_type === 'rf' ){
                        $targets['input'] = '';
                    } else {
                        // this field is only shown on the Registration form
                        unset($targets);
                    }
                    break;
                case 'Subscription Plans':
                    $field_meta = 'pms_subscription_plans';
                    if ( $form_type === 'rf' ){
                        $targets['heading'] = '';
                        $targets['label'] = '';
                    } else {
                        // this field can only be styled on the Registration form
                        unset($targets);
                    }
                    break;
                case 'Repeater':
                    $field_meta = $form_field['meta-name'];
                    $repeater_fields = get_option( $form_field['meta-name'], 'not_set' );
                    if ( $repeater_fields !== 'not_set' ){
                        foreach ( $repeater_fields as $repeater_key => $repeater_field){
                            $repeater_fields[$repeater_key]['control_group_conditions'] = $form_field['control_group_conditions'];
                        }
                        $this->add_fields_styling( $repeater_fields, $form_type );
                    }
                    unset($targets);
                    break;
                case 'WooCommerce Customer Billing Address':
                    $field_meta = $form_field['meta-name'];
                    $targets = $this->replace_label_with_heading( $targets );
                    $targets['woo_billing_label'] = ['billing_country','billing_first_name','billing_last_name','billing_company','billing_address_1','billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone'];
                    $targets['woo_billing_select'] = ['billing_country',];
                    $targets['woo_billing_input'] = ['billing_first_name','billing_last_name','billing_company','billing_address_1','billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone'];
                    if ( $form_type === 'rf' ){
                        $targets['woo_billing_checkbox'] = ['woo_different_shipping_address'];
                        $targets['woo_billing_checkbox_label'] = ['woo_different_shipping_address'];
                    }
                    break;
                case 'WooCommerce Customer Shipping Address':
                    $field_meta = $form_field['meta-name'];
                    $targets = $this->replace_label_with_heading( $targets );
                    $targets['woo_shipping_label'] = ['shipping_country','shipping_first_name','shipping_last_name','shipping_company','shipping_address_1','shipping_address_2','shipping_city','shipping_state','shipping_postcode'];
                    $targets['woo_shipping_select'] = ['shipping_country',];
                    $targets['woo_shipping_input'] = ['shipping_first_name','shipping_last_name','shipping_company','shipping_address_1','shipping_address_2','shipping_city','shipping_state','shipping_postcode',];
                    break;
                case 'WYSIWYG':
                    $field_meta = $form_field['meta-name'];
                    $targets['wysiwyg'] = '';
                    break;
                case 'Default - First Name':
                case 'Default - Last Name':
                case 'Default - Nickname':
                case 'Default - Biographical Info':
                case 'Input':
                case 'Textarea':
                case 'Phone':
                case 'Colorpicker':
                case 'Datepicker':
                case 'Number':
                case 'Validation':
                case 'Email':
                    $field_meta = $form_field['meta-name'];
                    $targets = $this->handle_placeholder_labels_active( $targets );
                    $targets['input'] = '';
                    break;
                default:
                    $field_meta = $form_field['meta-name'];
                    $targets['input'] = '';
            }

            if (!empty($targets) && is_array($targets)) {
                $sections = [];
                foreach ($targets as $target => $ids) {
                    switch ($target) {
                        case 'label':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' label',
                                'section_name' => 'Label',
                            ];
                            break;
                        case 'input':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' #' . $field_meta,
                                'section_name' => 'Input',
                            ];
                            break;
                        case 'username':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' #username',
                                'section_name' => 'Input',
                            ];
                            break;
                        case 'description':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-description-delimiter',
                                'section_name' => 'Description',
                            ];
                            break;
                        case 'heading':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' h4',
                                'section_name' => 'Heading',
                            ];
                            break;
                        case 'html':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .custom_field_html',
                                'section_name' => 'HTML',
                            ];
                            break;
                        case 'select_user_role':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .custom_field_user_role',
                                'section_name' => 'Select User Role',
                            ];
                            break;
                        case 'select_user_role_notice':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' p',
                                'section_name' => 'Select User Role Notice',
                            ];
                            break;
                        case 'select2':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .select2.select2-container',
                                'section_name' => 'Select2',
                            ];
                            break;
                        case 'select_cpt':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .select2.select2-container',
                                'section_name' => 'Select (CPT)',
                            ];
                            break;
                        case 'timepicker_hours':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .custom_field_timepicker_hours',
                                'section_name' => 'Timepicker Hours',
                            ];
                            break;
                        case 'timepicker_minutes':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .custom_field_timepicker_minutes',
                                'section_name' => 'Timepicker Minutes',
                            ];
                            break;
                        case 'timepicker_separator':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-timepicker-separator',
                                'section_name' => 'Timepicker Separator',
                            ];
                            break;
                        case 'checkbox':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .custom_field_checkbox',
                                'section_name' => 'Checkbox',
                            ];
                            break;
                        case 'checkbox_labels':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-rc-value',
                                'section_name' => 'Checkbox Labels',
                            ];
                            break;
                        case 'radio':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .custom_field_radio',
                                'section_name' => 'Radio',
                            ];
                            break;
                        case 'radio_labels':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-rc-value',
                                'section_name' => 'Radio Labels',
                            ];
                            break;
                        case 'upload':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb_upload_button',
                                'section_name' => 'Upload Button',
                            ];
                            break;
                        case 'avatar':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb_upload_button',
                                'section_name' => 'Avatar Button',
                            ];
                            break;
                        case 'gdpr_delete_button':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-delete-account',
                                'section_name' => 'GDPR Delete Button',
                            ];
                            break;
                        case 'default_field_display':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .' . $field_meta,
                                'section_name' => 'Select Name',
                            ];
                            break;
                        case 'wysiwyg':
                            $sections[$target] = [
                                'selector' => $form_class . ' #wppb-form-element-' . $form_field['id'] . ' div#wp-' . $field_meta . '-wrap',
                                'section_name' => 'Input',
                            ];
                            break;
                        case 'woo_billing_label' :
                        case 'woo_shipping_label' :
                            $sections[$target] = [
                                'selector' => $this->add_ids_to_selector( $form_class . ' #wppb-form-element-' . $form_field['id'] . ' label[for=', $ids, ']' ),
                                'section_name' => 'Labels',
                            ];
                            break;
                        case 'woo_billing_select' :
                        case 'woo_shipping_select' :
                            $sections[$target] = [
                                'selector' => $this->add_ids_to_selector( $form_class . ' #wppb-form-element-' . $form_field['id'] . ' select#', $ids ),
                                'section_name' => 'Select',
                            ];
                            break;
                        case 'woo_billing_input' :
                        case 'woo_shipping_input' :
                            $sections[$target] = [
                                'selector' => $this->add_ids_to_selector( $form_class . ' #wppb-form-element-' . $form_field['id'] . ' input#', $ids ),
                                'section_name' => 'Inputs',
                            ];
                            break;
                        case 'woo_billing_checkbox' :
                            $sections[$target] = [
                                'selector' => $this->add_ids_to_selector( $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-shipping-different-address input#', $ids ),
                                'section_name' => 'Checkbox',
                            ];
                            break;
                        case 'woo_billing_checkbox_label' :
                            $sections[$target] = [
                                'selector' => $this->add_ids_to_selector( $form_class . ' #wppb-form-element-' . $form_field['id'] . ' .wppb-shipping-different-address label[for=', $ids, ']' ),
                                'section_name' => 'Checkbox Label',
                            ];
                            break;
                        default:
                            return;
                    }
                }

                $this->add_styling_control_group(
                    $form_field['field-title'],
                    $form_field['control_group_conditions'],
                    $section_id_prefix . '_' .  $field_meta . '_' . $form_field['id'],
                    $sections
                );
            }
        }
    }

    /**
     * Add a selector for each id.
     * @param $ids
     * @param $prefix
     * @param string $suffix
     * @return array
     */
    protected function add_ids_to_selector( $prefix, $ids, $suffix = '' ){
        if ( is_array($ids) ) {
            $selectors = [];
            foreach ($ids as $key => $id) {
                $selectors[] = $prefix . $id . $suffix;
            }
            return $selectors;
        }
        return $prefix . $ids . $suffix;
    }

    /**
     * Replace label with heading.
     * @param $targets
     * @return array
     */
    protected function replace_label_with_heading( $targets ){
        if (array_key_exists('label', $targets)) {
            $targets['heading'] = $targets['label'];
            unset($targets['label']);
        }
        return $targets;
    }

    /**
     * Remove control group targeting the field label if Placeholder Labels is active.
     * @param $targets
     * @return array
     */
    protected function handle_placeholder_labels_active( $targets ){
        if( $this->is_placeholder_labels_active() && array_key_exists('label', $targets)) {
            unset($targets['label']);
        }
        return $targets;
    }

    /**
     * Render the two widget types.
     * @param $form_type
     * @return mixed|Profile_Builder_Form_Creator|string|void
     */
    protected function render_widget($form_type ) {

        $output = parent::render_widget( $form_type );
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // check if the form is being displayed in the Elementor editor
        $is_elementor_edit_mode = false;
        if( class_exists ( '\Elementor\Plugin' ) ){
            $is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
            $message= "";
        }

        if ( $is_elementor_edit_mode && $output->args !== null ) {

            if( defined( 'WPPB_PAID_PLUGIN_URL' ) ){
                //add the scripts for various fields
                foreach ( $output->args['form_fields'] as $form_field ){
                    switch ( $form_field['field'] ){
                        case 'Select2':
                            echo '<script src="'.esc_url( WPPB_PAID_PLUGIN_URL ).'front-end/extra-fields/select2/select2.js?ver='.esc_attr( PROFILE_BUILDER_VERSION ).'" id="wppb_sl2_js"></script>';
                            break;
                        case 'WYSIWYG':
                            echo '<script>jQuery(document.body).off( "click.add-media-button", ".insert-media" );</script>';
                            break;
                        case 'Select (CPT)':
                            echo '<script src="'.esc_url( WPPB_PAID_PLUGIN_URL ).'front-end/extra-fields/select-cpt/select-cpt.js?ver='.esc_attr( PROFILE_BUILDER_VERSION ).'" id="wppb-select-cpt-script"></script>';
                            break;
                        case 'Phone':
                            echo '<script src="'.esc_url( WPPB_PAID_PLUGIN_URL ).'front-end/extra-fields/phone/jquery.inputmask.bundle.min.js?ver='.esc_attr( PROFILE_BUILDER_VERSION ).'" id="wppb-jquery-inputmask"></script>';
                            echo '<script src="'.esc_url( WPPB_PAID_PLUGIN_URL ).'front-end/extra-fields/phone/script-phone.js?ver='.esc_attr( PROFILE_BUILDER_VERSION ).'" id="wppb-phone-script"></script>';
                            break;
                        default:
                            break;
                    }
                }
            }

            //Multi-Step Forms compatibility
            if ( wppb_check_if_add_on_is_active( 'multi-step-forms' ) ) {

                $ajaxUrl = admin_url( 'admin-ajax.php' );
                $ajaxNonce = wp_create_nonce( 'wppb_msf_frontend_nonce' );
                echo '
                    <script id="wppb-msf-script-frontend-extra">
                        var wppb_msf_data_frontend = {"ajaxUrl":"'.esc_url( $ajaxUrl ).'","ajaxNonce":"'.esc_attr( $ajaxNonce ).'"};
                    </script>
                ';
                echo '
                    <script src="'.esc_url( WPPB_PAID_PLUGIN_URL ).'add-ons-advanced/multi-step-forms/assets/js/frontend-multi-step-forms.js?ver='.esc_attr( PROFILE_BUILDER_VERSION ).'" id="wppb-msf-script-frontend">
                    </script>
                ';

            }
        }
    }
}