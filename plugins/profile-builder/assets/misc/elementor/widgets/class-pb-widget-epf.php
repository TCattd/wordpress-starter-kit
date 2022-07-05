<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once "class-pb-widget-rf-epf.php";

/**
 * Elementor widget for our wppb-edit-profile shortcode
 */
class PB_Elementor_Edit_Profile_Widget extends PB_Elementor_Register_Edit_Profile_Widget {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        $this -> register_pb_scripts_styles();
    }

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'wppb-edit-profile';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Edit Profile', 'profile-builder' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Register widget controls.
	 *
	 */
	protected function register_controls() {
        $this->register_rf_epf_controls( 'epf' );
	}

	/**
	 * Render widget output in the front-end.
	 *
	 */
	protected function render() {
        $this->render_widget( 'epf');
	}

}
