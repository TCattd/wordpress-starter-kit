<?php

class piotnetforms_Space extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'space';
	}

	public function get_class_name() {
		return 'piotnetforms_Space';
	}

	public function get_title() {
		return 'Space';
	}

	public function get_icon() {
		return 'fas fa-arrows-alt-v';
	}

	public function get_categories() {
		return [ 'pafe-form-builder' ];
	}

	public function get_keywords() {
		return [ 'text' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'space_settings_section', 'Divider Settings' );
		$this->add_setting_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function add_setting_controls() {
		$this->add_responsive_control(
			'space',
			[
				'type'        => 'slider',
				'label'       => __( 'Space', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px'  => [
						'min'  => 1,
						'max'  => 700,
						'step' => 1,
					],
					'em'  => [
						'min'  => 1,
						'max'  => 50,
						'step' => 1,
					],
					'rem' => [
						'min'  => 1,
						'max'  => 50,
						'step' => 1,
					],
					'vw'  => [
						'min'  => 1,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-spacer' => 'height:{{SIZE}}{{UNIT}}',
				],
			]
		);
	}

	public function render() {
		$settings = $this->settings;
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-spacer' ); ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="piotnetforms-space"></div>
		</div>
		<?php
	}
	public function live_preview() {
		?>
		<div <%= view.render_attributes('wrapper') %>>
			<div class="piotnetforms-spacer"></div>
		</div>
		<?php
	}

}
