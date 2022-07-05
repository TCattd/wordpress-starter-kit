<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once "class-pb-widget-base.php";

/**
 * Elementor widget for our wppb-recover-password shortcode
 */
class PB_Elementor_Recover_Password_Widget extends PB_Elementor_Widget {

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'wppb-recover-password';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Recover Password', 'profile-builder' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-shortcode';
	}

	/**
	 * Register widget controls.
	 *
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'pb_content_section',
			array(
				'label' => __( 'Form Settings', 'profile-builder' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'pb_recovery_no_controls_text',
			array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw'  => __( 'There are no available controls for the Password Recovery form', 'profile-builder' ),
			)
		);

		$this->end_controls_section();

        // Instructions Paragraph tab
        $this->add_styling_control_group(
            'Instructions Paragraph',
            '',
            'pb_recover_password_instructions',
            [
                'paragraph' => [
                    'selector' => '#wppb-recover-password p',
                    'section_name' => 'Paragraph',
                ]
            ]
        );

        // User Login Style tab
        if( !$this->is_placeholder_labels_active() ) {
            $sections['label'] = [
                'selector' => '.wppb-username-email label',
                'section_name' => 'Label',
            ];
        }
        $sections['input'] = [
            'selector' => '.wppb-username-email input',
            'section_name' => 'Input',
        ];
        $this->add_styling_control_group(
            'User Login',
            '',
            'pb_recover_password_username',
            $sections
        );
        unset($sections);

        // reCAPTCHA Style tab
        if( !$this->is_placeholder_labels_active() ) {
            include_once(WPPB_PLUGIN_DIR . '/front-end/default-fields/recaptcha/recaptcha.php');
            $field = wppb_get_recaptcha_field();
            if (!empty($field) && isset($field['captcha-pb-forms']) && (strpos($field['captcha-pb-forms'], 'pb_recover_password') !== false)) {
                $this->add_styling_control_group(
                    'reCAPTCHA',
                    '',
                    'pb_recover_password_recaptcha',
                    [
                        'label' => [
                            'selector' => '.wppb-form-field.wppb-recaptcha label',
                            'section_name' => 'Label',
                        ]
                    ]
                );
            }
        }

        // Submit Button Style tab
        $this->add_styling_control_group(
            'Submit Button',
            '',
            'pb_recover_password_button',
            [
                'input' => [
                    'selector' => '.form-submit input#wppb-recover-password-button',
                    'section_name' => 'Input',
                ]
            ]
        );
	}

	/**
	 * Render widget output in the front-end.
	 *
	 */
	protected function render() {
        echo $this->render_widget( 'rp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}
