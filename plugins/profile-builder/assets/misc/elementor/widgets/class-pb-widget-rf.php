<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once "class-pb-widget-rf-epf.php";

/**
 * Elementor widget for our wppb-register shortcode
 */
class PB_Elementor_Register_Widget extends PB_Elementor_Register_Edit_Profile_Widget {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this -> register_pb_scripts_styles();
    }

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'wppb-register';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Register', 'profile-builder' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-price-list';
	}

	/**
	 * Register widget controls.
	 *
	 */
	protected function register_controls() {
        $this -> register_rf_epf_controls( 'rf' );
	}

    /**
	 * Render widget output in the front-end.
	 *
	 */
	protected function render() {
        $this->render_widget( 'rf');
	}

}
