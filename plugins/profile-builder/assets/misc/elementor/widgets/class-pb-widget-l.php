<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once "class-pb-widget-base.php";

/**
 * Elementor widget for our wppb-login shortcode
 */
class PB_Elementor_Login_Widget extends PB_Elementor_Widget {

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'wppb-login';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Login', 'profile-builder' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-lock-user';
	}

	/**
	 * Register widget controls.
	 *
	 */
	protected function register_controls() {

        $this->start_controls_section(
            'pb_login_links',
            array(
                'label' => __( 'Form Settings', 'profile-builder' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'pb_register_url',
            array(
                'label'       => __( 'Registration', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter URL', 'profile-builder' ),
            )
        );

        $this->add_control(
            'pb_lostpassword_url',
            array(
                'label'       => __( 'Recover Password', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter URL', 'profile-builder' ),
            )
        );

        if ( $this->is_2fa_active() ) {
            $this->add_control(
                'pb_auth_field',
                array(
                    'label' => __('Show Authenticator Code Field', 'profile-builder'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Yes', 'profile-builder'),
                    'label_off' => __('No', 'profile-builder'),
                    'return_value' => 'yes',
                    'default' => '',
                )
            );
        }

        $this->end_controls_section();

        $this->start_controls_section(
            'pb_login_redirects',
            array(
                'label' => __( 'Redirects', 'profile-builder' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'pb_after_login_redirect_url',
            array(
                'label'       => __( 'After Login', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter URL', 'profile-builder' ),
            )
        );

        $this->add_control(
            'pb_after_logout_redirect_url',
            array(
                'label'       => __( 'After Logout', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter URL', 'profile-builder' ),
            )
        );

        $this->end_controls_section();

        // User Login Style tab
        if( !$this->is_placeholder_labels_active() ) {
            $sections['label'] = [
                'selector' => '#wppb-login-wrap .login-username label[for=user_login]',
                'section_name' => 'Label',
            ];
        }
        $sections['input'] = [
            'selector' => '#wppb-login-wrap .login-username input#user_login',
            'section_name' => 'Input',
        ];
        $this->add_styling_control_group(
            'User Login',
            '',
            'pb_user_login_username',
            $sections
        );
        unset($sections);

        // Password Style tab
        if( !$this->is_placeholder_labels_active() ) {
            $sections['label'] = [
                'selector' => '#wppb-login-wrap .login-password label[for=user_pass]',
                'section_name' => 'Label',
            ];
        }
        $sections['input'] = [
            'selector' => '#wppb-login-wrap .login-password input#user_pass',
            'section_name' => 'Input',
        ];
        $this->add_styling_control_group(
            'Password',
            '',
            'pb_user_login_password',
            $sections
        );
        unset($sections);

        if ( $this->is_2fa_active() ) {
            // Authenticator Code Style tab
            if (!$this->is_placeholder_labels_active()) {
                $sections['label'] = [
                    'selector' => '#wppb-login-wrap .login-auth label[for=login_auth]',
                    'section_name' => 'Label',
                ];
            }
            $sections['input'] = [
                'selector' => '#wppb-login-wrap .login-auth input#login_auth',
                'section_name' => 'Input',
            ];
            $this->add_styling_control_group(
                'Authenticator Code',
                '',
                'pb_user_auth_code',
                $sections
            );
            unset($sections);
        }

        // reCAPTCHA Style tab
        if( !$this->is_placeholder_labels_active() ) {
            include_once(WPPB_PLUGIN_DIR . '/front-end/default-fields/recaptcha/recaptcha.php');
            $field = wppb_get_recaptcha_field();
            if (!empty($field) && isset($field['captcha-pb-forms']) && (strpos($field['captcha-pb-forms'], 'pb_recover_password') !== false)) {
                $this->add_styling_control_group(
                    'reCAPTCHA',
                    '',
                    'pb_user_login_recaptcha',
                    [
                        'label' => [
                            'selector' => '#wppb-login-wrap .wppb-form-field.wppb-recaptcha label[for=recaptcha_response_field]',
                            'section_name' => 'Label',
                        ]
                    ]
                );
            }
        }

        // Remember Checkbox Style tab
        $this->add_styling_control_group(
            'Remember Me Checkbox',
            '',
            'pb_user_login_remember',
            [
                'label' => [
                    'selector' => '#wppb-login-wrap .login-remember label',
                    'section_name' => 'Label',
                ],
                'input' => [
                    'selector' => '#wppb-login-wrap .login-remember input',
                    'section_name' => 'Input',
                ]
            ]
        );

        // Submit Button Style tab
        $this->add_styling_control_group(
            'Login Button',
            '',
            'pb_user_login_button',
            [
                'input' => [
                    'selector' => '#wppb-login-wrap .login-submit input#wppb-submit',
                    'section_name' => 'Input',
                ]
            ]
        );

        // Social Connect Style tab
        $social_connect_settings = get_option( 'wppb_social_connect_settings' );
        if ( is_array($social_connect_settings) ) {
            $social_connect_settings = reset($social_connect_settings);
        }

        if ( $social_connect_settings && strpos($social_connect_settings['display-on-the-following-forms'], 'pb-login' ) !== false ) {
            $this->add_styling_control_group(
                'Social Connect',
                '',
                'pb_user_login_social_connect',
                [
                    'sc_heading' => [
                        'selector' => '.wppb-sc-buttons-container .wppb-sc-heading-before-reg-buttons h3',
                        'section_name' => 'Heading',
                    ],
                    'sc_buttons' => [
                        'selector' => '.wppb-sc-buttons-container a.wppb-sc-button',
                        'section_name' => 'Buttons',
                    ]
                ]
            );
        }
	}

	/**
	 * Render widget output in the front-end.
	 *
	 */
	protected function render() {
        $output = $this->render_widget( 'l' );
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // check if the form is being displayed in the Elementor editor
        $is_elementor_edit_mode = false;
        if( class_exists ( '\Elementor\Plugin' ) ){
            $is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
            $message= "";
        }

        if ($is_elementor_edit_mode && !empty($output) && $this->is_placeholder_labels_active()) {
            echo '
                <script id="wppb_elementor_login_pbpl_init">
                    jQuery(".login-username input, .login-password input").each( function ( index, elem ) {
                        var element_id = jQuery( elem ).attr( "id" );
                        if( element_id && ( label = jQuery( elem ).parents( "#wppb-login-wrap" ).find( "label[for=" + element_id + "]" ) ).length === 1 ) {
                            jQuery( elem ).attr( "placeholder", jQuery( label ).text() );
                        }
                    });
                </script>
            ';
        }
    }

}
