<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Elementor extends \Elementor\Widget_Base {
	public function get_name() {
		return 'piotnetforms';
	}

	public function get_title() {
		return 'Piotnet Forms';
	}

	public function get_icon() {
		return 'piotnetforms-icon piotnetforms-icon--form';
	}

	public function get_categories() {
		return [ 'piotnetforms' ];
	}

	public function get_keywords() {
		return [ 'form', 'piotnetforms', 'piotnet' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'form_section',
			[
				'label' => __( 'Form', 'piotnetforms' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$form_id_query = new WP_Query(
			[
				'post_type' => 'piotnetforms',
				'posts_per_page' => -1,
			]
		);

		$forms = [];

		if ( $form_id_query->have_posts() ) : while ( $form_id_query->have_posts() ) : $form_id_query->the_post();
			$forms[ get_the_ID() ] = get_the_title();
		endwhile;
		endif;
		wp_reset_postdata();

		$this->add_control(
			'form_id',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label'       => __( 'Select a Form', 'piotnetforms' ),
				'options'     => $forms,
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		$form_id = !empty( $settings['form_id'] ) ? $settings['form_id'] : '';
		$shortcode = '[piotnetforms id=' . $form_id . ']';

		if ( ! empty( $form_id ) ) {
			echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';
			echo do_shortcode( $shortcode );
			echo '</div>';
		}
	}
}
