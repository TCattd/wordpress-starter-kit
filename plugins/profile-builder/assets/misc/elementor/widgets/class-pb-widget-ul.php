<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once "class-pb-widget-base.php";

/**
 * Elementor widget for our wppb-list-users shortcode
 */
class PB_Elementor_User_Listing_Widget extends PB_Elementor_Widget {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        if( defined( 'WPPB_PAID_PLUGIN_URL' ) ){
            wp_register_script('wppb-userlisting-js', WPPB_PAID_PLUGIN_URL . 'add-ons/user-listing/userlisting.js', array('jquery', 'jquery-touch-punch'), PROFILE_BUILDER_VERSION, true);
            wp_localize_script( 'wppb-userlisting-js', 'wppb_userlisting_obj', array( 'pageSlug' => wppb_get_users_pagination_slug() ) );
            wp_register_style('wppb-ul-slider-css', WPPB_PAID_PLUGIN_URL . 'add-ons/user-listing/jquery-ui-slider.min.css', array(), PROFILE_BUILDER_VERSION );
            //wp_register_script('jquery-ui-slider');
        }

    }

    public function get_script_depends() {
        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists(WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/userlisting.php') ) {
            return [
                'wppb-userlisting-js',
                'jquery-ui-slider',
            ];
        }
        return [];
    }

    public function get_style_depends() {
        $styles = [];
        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists(WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/userlisting.php') ) {
            $styles = [
                'wppb-ul-slider-css',
            ];
        }
        return $styles;
    }

    /**
     * Get widget name.
     *
     */
    public function get_name() {
        return 'wppb-list-users';
    }

    /**
     * Get widget title.
     *
     */
    public function get_title() {
        return __( 'User Listing', 'profile-builder' );
    }

    /**
     * Get widget icon.
     * to-do
     */
    public function get_icon() {
        return 'eicon-post-list';
    }

    /**
     * Register widget controls.
     *
     */
    protected function register_controls() {

        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
        $meta_names = array(
            '' => ''
        );
        foreach( $wppb_manage_fields as $value ){
            if( $value['meta-name'] ) {
                $meta_names[$value['meta-name']] = $value['meta-name'];
            }
        }

        $this->start_controls_section(
            'pb_user_listing_settings',
            array(
                'label' => __( 'Listing Settings', 'profile-builder' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

	    $ul_names = array();
	    $userlisting_posts = get_posts( array( 'posts_per_page' => -1, 'post_status' =>'publish', 'post_type' => 'wppb-ul-cpt', 'orderby' => 'post_date', 'order' => 'ASC' ) );
	    if( !empty( $userlisting_posts ) ){
		    foreach ( $userlisting_posts as $post ){
			    $ul_names[ $post->post_name ] = $post->post_title;
		    }
		}

	    reset($ul_names);
		$default_key = is_null( key($ul_names) ) ? '' : key($ul_names);

        $this->add_control(
            'pb_name',
            array(
                'label'       => __( 'User Listing', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $ul_names,
	            'default'     => $default_key,
            )
        );

        $this->add_control(
            'pb_single',
            array(
                'label'        => __('Single', 'profile-builder'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'profile-builder'),
                'label_off'    => __('No', 'profile-builder'),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->add_control(
            'pb_meta_key',
            array(
                'label'       => __( 'Meta Key', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $meta_names,
                'default'     => '',
                'condition'   => [
                    'pb_single' => '',
                ],
            )
        );

        $this->add_control(
            'pb_meta_value',
            array(
                'label'       => __( 'Meta Value', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter Meta Value', 'profile-builder' ),
                'default'     => '',
                'condition'   => [
                    'pb_single'    => '',
                    'pb_meta_key!' => '',
                ],
            )
        );

        $this->add_control(
            'pb_include',
            array(
                'label'       => __( 'Include', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter User IDs', 'profile-builder' ),
                'default'     => '',
                'condition'   => [
                    'pb_single' => '',
                ],
            )
        );

        $this->add_control(
            'pb_exclude',
            array(
                'label'       => __( 'Exclude', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter User IDs', 'profile-builder' ),
                'default'     => '',
                'condition'   => [
                    'pb_single' => '',
                ],
            )
        );

        $this->add_control(
            'pb_id',
            array(
                'label'       => __( 'ID', 'profile-builder' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter User ID', 'profile-builder' ),
                'default'     => '',
                'condition'   => [
                    'pb_single' => 'yes',
                ],
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output in the front-end.
     *
     */
    protected function render() {
        $output = $this->render_widget( 'ul' );
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
