<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Shortcode extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'shortcode';
	}

	public function get_class_name() {
		return 'piotnetforms_Shortcode';
	}

	public function get_title() {
		return 'Shortcode';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-shortcode.svg',
		];
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'shortcode' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'shortcode_settings_section', 'Shortcode Settings' );
		$this->add_setting_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function add_setting_controls() {
		$this->add_control(
			'shortcode',
			[
				'type'        => 'textarea',
				'label'       => __( 'Shortcode', 'piotnetforms' ),
				'value'       => '',
				'description' => __( 'Enter your short code', 'piotnetforms' ),
			]
		);
	}

	public function render() {
		$settings = $this->settings;
		if ( ! empty( $settings['shortcode'] ) ) {
			echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';
			echo do_shortcode( $settings['shortcode'] );
			echo '</div>';
		}
	}
	public function live_preview() {
		?>
		<%
		
		%>
		<?php
	}
}
