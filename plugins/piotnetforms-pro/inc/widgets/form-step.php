<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Form_Step extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'form-step';
	}

	public function get_class_name() {
		return 'Piotnetforms_Form_Step';
	}

	public function get_title() {
		return 'Form Step';
	}

	public function get_icon() {
		return [
			'type'  => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-form-step.svg',
		];
	}

	public function get_categories() {
		return [ 'form' ];
	}

	public function get_keywords() {
		return [ 'multi step form' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'settings_section', 'General' );
		$this->add_settings_controls();

		$this->start_section( 'button_settings_section', 'Buttons' );
		$this->add_button_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'button_style_section', 'Button' );
		$this->add_button_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function add_settings_controls() {
		$this->add_control(
			'disable_buttons',
			[
				'label' => __( 'Disable Buttons', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'press_enter_to_next',
			[
				'label' => __( 'Press enter to go to the next step', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'step_title',
			[
				'type'        => 'text',
				'label'       => __( 'Next Step Title', 'piotnetforms' ),
				'label_block' => true,
			]
		);
	}

	private function add_button_setting_controls() {
		$this->add_control(
			'prev_button',
			[
				'label' => __( 'Previous Button', 'piotnetforms' ),
				'type' => 'switch',
				'default' => 'yes',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
				'description' => __( 'The Previous button is not visible in the first step on the frontend', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'prev_button_text',
			[
				'type'        => 'text',
				'label'       => __( 'Previous Button Text', 'piotnetforms' ),
				'label_block' => true,
				'value'       => __( 'Previous', 'piotnetforms' ),
				'condition'   => [
					'prev_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'next_button',
			[
				'label' => __( 'Next Button', 'piotnetforms' ),
				'type' => 'switch',
				'default' => 'yes',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'next_button_text',
			[
				'type'        => 'text',
				'label'       => __( 'Next Button Text', 'piotnetforms' ),
				'label_block' => true,
				'value'       => __( 'Next', 'piotnetforms' ),
				'condition'   => [
					'prev_button' => 'yes',
				],
			]
		);
	}

	private function add_button_style_controls() {
		$this->add_text_typography_controls(
			'typography',
			[
				'selectors' => '{{WRAPPER}} a.piotnetforms-button, {{WRAPPER}} .piotnetforms-button',
			]
		);

		$this->add_responsive_control(
			'buttons_justify_content',
			[
				'type'         => 'select',
				'label'        => __( 'Justify Content', 'piotnetforms' ),
				'label_block'  => true,
				'value'        => '',
				'options'      => [
					''       => __( 'Default', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'flex-end' => __( 'Flex End', 'piotnetforms' ),
					'space-between' => __( 'Space Between', 'piotnetforms' ),
					'space-evenly' => __( 'Space Evenly', 'piotnetforms' ),
					'space-around' => __( 'Space Around', 'piotnetforms' ),
				],
				'selectors'    => [
					'{{WRAPPER}}' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'submit_button_style_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'submit_button_style_hover_tab',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->tab_button_style_controls(
			'style_normal',
			[
				'selectors' => '{{WRAPPER}} a.piotnetforms-button, {{WRAPPER}} .piotnetforms-button',
			]
		);
		$this->add_control(
			'submit_button_style_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->tab_button_style_controls(
			'style_hover',
			[
				'selectors' => '{{WRAPPER}} a.piotnetforms-button:hover, {{WRAPPER}} .piotnetforms-button:hover, {{WRAPPER}} a.piotnetforms-button:focus, {{WRAPPER}} .piotnetforms-button:focus',
			]
		);
		$this->add_control(
			'submit_button_style_hover_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function tab_button_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$this->new_group_controls();
		$this->add_control(
			$name . 'button_text_color',
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'value'     => '',
				'render_type' => 'none',
				'selectors' => [
					$wrapper => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			$name . 'background_color',
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'render_type' => 'none',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$name.'_button_border_type',
			[
				'type'      => 'select',
				'label'     => __( 'Border Type', 'piotnetforms' ),
				'value'     => '',
				'options'   => [
					''       => 'None',
					'solid'  => 'Solid',
					'double' => 'Double',
					'dotted' => 'Dotted',
					'dashed' => 'Dashed',
					'groove' => 'Groove',
				],
				'render_type' => 'none',
				'selectors' => [
					$wrapper => 'border-style:{{VALUE}};',
				],
			]
		);
		$this->add_control(
			$name.'_button_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'border-color: {{VALUE}};',
				],
				'render_type' => 'none',
				'conditions'  => [
					[
						'name'     => $name.'_button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name.'_button_border_width',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Border Width', 'piotnetforms' ),
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'label_block' => true,
				'size_units'  => [ 'px', '%', 'em' ],
				'selectors'   => [
					$wrapper => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'render_type' => 'none',
				'conditions'  => [
					[
						'name'     => $name.'_button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);

		$this->add_control(
			$name . 'border_radius',
			[
				'label'       => __( 'Border Radius', 'piotnetforms' ),
				'type'        => 'dimensions',
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'render_type' => 'none',
				'label_block' => true,
				'size_units'  => [ 'px', '%' ],
				'selectors'   => [
					$wrapper => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			$name . 'button_box_shadow',
			[
				'type'        => 'box-shadow',
				'label'       => __( 'Box Shadow', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'render_type' => 'none',
				'selectors'   => [
					$wrapper => 'box-shadow: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			$name . 'text_padding',
			[
				'label'       => __( 'Padding', 'piotnetforms' ),
				'type'        => 'dimensions',
				'label_block' => false,
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'render_type' => 'none',
				'size_units'  => [ 'px', 'em', '%' ],
				'selectors'   => [
					$wrapper => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		return $this->get_group_controls();
	}

	public function render() {
		$settings = $this->settings;
		$editor = ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) ? true : false;
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-multi-step-form__content-item-buttons' );

		if ( $editor ) {
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-step-title', $settings['step_title'] );
		}

		if ( !empty( $settings['press_enter_to_next'] ) ) {
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-press-enter-next', '' );
		}

		$this->add_render_attribute( 'button', 'class', 'piotnetforms-button' );
		$this->add_render_attribute( 'button', 'role', 'button' );

		if ( !empty( $settings['disable_buttons'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'piotnet-hidden' );
		} ?>
        	<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<?php if ( !empty( $settings['prev_button'] ) ) : ?>
					<div class="piotnetforms-multi-step-form__content-item-button">
						<button <?php echo $this->get_render_attribute_string( 'button' ); ?> data-piotnetforms-nav="prev">
							<span class="piotnetforms-button-content-wrapper">
								<span class="piotnetforms-button-text"><?php echo $settings['prev_button_text']; ?></span>
							</span>
						</button>
					</div>
				<?php endif; ?>
				<?php if ( !empty( $settings['next_button'] ) ) : ?>
					<div class="piotnetforms-multi-step-form__content-item-button">
						<button <?php echo $this->get_render_attribute_string( 'button' ); ?> data-piotnetforms-nav="next">
							<span class="piotnetforms-button-content-wrapper">
								<span class="piotnetforms-button-text"><?php echo $settings['next_button_text']; ?></span>
							</span>
						</button>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( !$editor ) : ?>
			</div>
			<div class="piotnetforms-multi-step-form__content-item">
			<?php endif; ?>
        <?php
	}

	public function live_preview() {
		?>
		<%
			const s = data.widget_settings;
			view.add_attribute('wrapper', 'class', 'piotnetforms-multi-step-form__content-item-buttons');
			view.add_attribute('wrapper', 'data-piotnetforms-step-title', s['step_title']);
			view.add_attribute('button', 'class', 'piotnetforms-button');
			view.add_attribute('button', 'role', 'button');
		%>
			<div <%= view.render_attributes('wrapper') %>>
				<% if(!s['disable_buttons']){ %>
					<% if(s['prev_button'] === 'yes'){ %>
						<div class="piotnetforms-multi-step-form__content-item-button">
							<button <%= view.render_attributes('button') %> data-piotnetforms-nav="prev">
								<span class="piotnetforms-button-content-wrapper">
									<span class="piotnetforms-button-text"><%= s['prev_button_text'] %></span>
								</span>
							</button>
						</div>
					<% } %>
					<% if(s['next_button'] === 'yes'){ %>
						<div class="piotnetforms-multi-step-form__content-item-button">
							<button <%= view.render_attributes('button') %> data-piotnetforms-nav="next">
								<span class="piotnetforms-button-content-wrapper">
									<span class="piotnetforms-button-text"><%= s['next_button_text'] %></span>
								</span>
							</button>
						</div>
					<% } %>
				<% } %>
			</div>
		<?php
	}
}
