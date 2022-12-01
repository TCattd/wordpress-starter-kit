<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;

/**
 * Base class for the Profile Builder Elementor widgets
 */
abstract class PB_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget categories.
     *
     */
    public function get_categories() {
        return array( 'profile-builder' );
    }

    /**
     * Add styling control group and populate it.
     * @param $section_label
     * @param $condition
     * @param $id_prefix
     * @param $sections
     */
    protected function add_styling_control_group($section_label, $condition, $id_prefix, $sections ){

        $this->start_controls_section(
            $id_prefix.'_style_section',
            [
                'label'     => $section_label === '' ? __( 'Unlabelled Field', 'profile-builder' ) : $section_label,
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );

        if ( count($sections) > 1 ) {
            foreach ( $sections as $target => $section ) {
                $this->add_control(
                    $id_prefix.'_section_'.$target.'_div1',
                    [
                        'type' => \Elementor\Controls_Manager::DIVIDER,
                    ]
                );

                $this->add_control(
                    $id_prefix.'_target_'.$target,
                    [
                        'label' => $section['section_name'],
                        'type'  => Controls_Manager::HEADING,
                    ]
                );

                $this->add_control(
                    $id_prefix.'_section_'.$target.'_div2',
                    [
                        'type' => \Elementor\Controls_Manager::DIVIDER,
                    ]
                );

                $this->add_styling_control_element($id_prefix . '_' . $target, $section['selector']);
            }
        } else {
            foreach ( $sections as $target => $section ) {
                $this->add_styling_control_element($id_prefix . '_' . $target, $section['selector']);
            }
        }

        $this->end_controls_section();
    }

    /**
     * Populate the control groups.
     * @param $id_prefix
     * @param $selector
     * @param array $condition
     */
    private function add_styling_control_element($id_prefix, $selector, $condition = [] ){

        $wrapped_selector = '';
        if ( is_array($selector )) {
            end($selector);
            $final_key = key($selector);
            reset($selector);
            foreach ($selector as $key => $individual_selector) {
                $wrapped_selector .= '{{WRAPPER}} ' . $individual_selector;
                if ($key !== $final_key) {
                    $wrapped_selector .= ', ';
                }
            }
        } else {
            $wrapped_selector .= '{{WRAPPER}} ' . $selector;
        }

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => $id_prefix.'_typography',
                'selector'  => $wrapped_selector,
                'condition' => $condition,
            ]
        );

        $this->add_control(
            $id_prefix.'_background',
            [
                'label'     => __( 'Background', 'profile-builder' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $wrapped_selector => 'background-color: {{VALUE}};',
                ],
                'condition' => $condition,
            ]
        );

        $this->add_control(
            $id_prefix.'_text_color',
            [
                'label'     => __( 'Color', 'profile-builder' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $wrapped_selector => 'color: {{VALUE}};',
                ],
                'separator' => 'after',
                'condition' => $condition,
            ]
        );

        $this->add_responsive_control(
            $id_prefix.'_width',
            [
                'label'      => __( 'Width', 'profile-builder' ),
                'type'       => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $wrapped_selector => 'width: {{SIZE}}{{UNIT}}',
                ],
                'condition'  => $condition,
            ]
        );

        $this->add_responsive_control(
            $id_prefix.'_height',
            [
                'label'      => __( 'Height', 'profile-builder' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $wrapped_selector => 'height: {{SIZE}}{{UNIT}}',
                ],
                'separator'  => 'after',
                'condition'  => $condition,

            ]
        );

        $this->add_responsive_control(
            $id_prefix.'_padding',
            [
                'label'      => __( 'Padding', 'profile-builder' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    $wrapped_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition'  => $condition,
            ]
        );

        $this->add_responsive_control(
            $id_prefix.'_margin',
            [
                'label'      => __( 'Margin', 'profile-builder' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    $wrapped_selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator'  => 'after',
                'condition'  => $condition,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => $id_prefix.'_border',
                'label'     => __( 'Border', 'profile-builder' ),
                'selector'  => $wrapped_selector,
                'condition' => $condition,
            ]
        );

        $this->add_responsive_control(
            $id_prefix.'_border_radius',
            [
                'label'     => esc_html__( 'Radius', 'profile-builder' ),
                'type'      => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    $wrapped_selector => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                ],
                'condition' => [$id_prefix.'_border_border!' => ''],
            ]
        );
    }

    /**
     * Check if Placeholder Labels is active.
     * @return bool
     */
    protected function is_placeholder_labels_active(){
        $toolbox_forms_settings = get_option( 'wppb_toolbox_forms_settings', 'not_found' );

        if ( $toolbox_forms_settings !== 'not_found' && $toolbox_forms_settings['placeholder-labels'] === 'yes' ){
            return true;
        }

        return false;
    }

    /**
     * Check if 2FA is active.
     * @return bool
     */
    protected function is_2fa_active(){
        return wppb_is_2fa_active();
    }

    /**
     * Get an aray of page names.
     * @return array
     */
    protected function get_all_pages(){
        $args = array(
            'post_type'         => 'page',
            'posts_per_page'    => -1
        );

        $page_titles = array(
            '' => ''
        );

        if( function_exists( 'wc_get_page_id' ) )
            $args['exclude'] = wc_get_page_id( 'shop' );

        $all_pages = get_posts( $args );
        if( !empty( $all_pages ) ){
            foreach ( $all_pages as $page ){
                $page_titles[$page->ID] = $page->post_title;
            }
        }
        return $page_titles;
    }

    /**
     * Render the four widget types.
     * @param $form_type
     * @return mixed|Profile_Builder_Form_Creator|string|void
     */
    protected function render_widget( $form_type ){

        if (!($form_type === 'rf' || $form_type === 'epf' || $form_type === 'l' || $form_type === 'rp' || $form_type === 'ul')) {
            return;
        }

        $settings = $this->get_settings_for_display();

        switch ( $form_type ){
            case 'rf':
                include_once(WPPB_PLUGIN_DIR . '/front-end/register.php');
                include_once(WPPB_PLUGIN_DIR . '/front-end/class-formbuilder.php');
                $form_name = '';
                if (array_key_exists('pb_form_name', $settings)) {
                    $form_name = substr($settings['pb_form_name'], 1);
                }
                if (!$form_name || $form_name === ''){
                    $form_name = 'unspecified';
                }
                $atts = [
                    'role' => $settings['pb_role'],
                    'form_name' => $form_name,
                    'redirect_url' => get_page_link( $settings['pb_redirect_url'] ),
                    'logout_redirect_url' => get_page_link( $settings['pb_logout_redirect_url'] ),
                    'automatic_login' => $settings['pb_automatic_login'],
                ];
                return wppb_front_end_register( $atts );
            case 'epf':
                include_once(WPPB_PLUGIN_DIR . '/front-end/edit-profile.php');
                include_once(WPPB_PLUGIN_DIR . '/front-end/class-formbuilder.php');
                $form_name = '';
                if (array_key_exists('pb_form_name', $settings)) {
                    $form_name = substr($settings['pb_form_name'], 1);
                }
                if (!$form_name || $form_name === ''){
                    $form_name = 'unspecified';
                }
                $atts = [
                    'form_name' => $form_name,
                    'redirect_url' => get_page_link( $settings['pb_redirect_url'] ),
                ];
                return wppb_front_end_profile_info( $atts );
            case 'l':
                include_once( WPPB_PLUGIN_DIR.'/front-end/login.php' );
                $atts = [
                    'redirect_url'        => get_page_link( $settings['pb_after_login_redirect_url'] ),
                    'logout_redirect_url' => get_page_link( $settings['pb_after_logout_redirect_url'] ),
                    'register_url'        => get_page_link( $settings['pb_register_url'] ),
                    'lostpassword_url'    => get_page_link( $settings['pb_lostpassword_url'] ),
                    'show_2fa_field'      => isset( $settings['pb_auth_field'] ) ? $settings['pb_auth_field'] : false,
                ];
                return wppb_front_end_login( $atts );
            case 'rp':
                include_once( WPPB_PLUGIN_DIR.'/front-end/recover.php' );
                return wppb_front_end_password_recovery( [] );
            case 'ul':
                if( defined( 'WPPB_PAID_PLUGIN_DIR' ) ){
                    include_once( WPPB_PAID_PLUGIN_DIR.'/add-ons/user-listing/userlisting.php' );
                    $atts = [
                        'name'       => $settings['pb_name'],
                        'single'     => $settings['pb_single'] === 'yes',
                        'meta_key'   => $settings['pb_meta_key'],
                        'meta_value' => $settings['pb_meta_key'] ? $settings['pb_meta_value'] : '',
                        'include'    => $settings['pb_include'],
                        'exclude'    => $settings['pb_exclude'],
                        'id'         => $settings['pb_id'],
                    ];
                    return wppb_user_listing_shortcode( $atts );
                }
        }
    }
}

