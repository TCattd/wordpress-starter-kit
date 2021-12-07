<?php

class piotnetforms_Divider extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'divider';
	}

	public function get_class_name() {
		return 'piotnetforms_Divider';
	}

	public function get_title() {
		return 'Divider';
	}

	public function get_icon() {
		return 'fas fa-divide';
	}

	public function get_categories() {
		return [ 'pafe-form-builder' ];
	}

	public function get_keywords() {
		return [ 'text' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'divider_settings_section', 'Divider Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'divider_styles_section', 'Divider Style' );
		$this->add_style_controls();
		$this->start_section( 'text_styles_section', 'Text Settings' );
		$this->add_text_setting_controls();
		$this->start_section( 'icon_styles_section', 'Icon Settings' );
		$this->add_icon_setting_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function add_setting_controls() {
		$this->add_control(
			'divide_type',
			[
				'type'      => 'select',
				'label'     => __( 'Type', 'piotnetforms' ),
				'value'     => 'solid',
				'options'   => [
					'solid'  => __( 'Solid', 'piotnetforms' ),
					'dashed' => __( 'Dashed', 'piotnetforms' ),
					'dotted' => __( 'Dotted', 'piotnetforms' ),
					'double' => __( 'Double', 'piotnetforms' ),
					'none'   => __( 'None', 'piotnetforms' ),
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator::before, {{WRAPPER}} .piotnetforms-divider__inner-separator::after, {{WRAPPER}} .piotnetforms-divider__inner-separator-no-content' => 'border-top-style: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'divide_width',
			[
				'type'        => 'slider',
				'label'       => __( 'Width', 'piotnetforms' ),
				'value'       => [
					'unit' => '%',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'%' => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator, {{WRAPPER}} .piotnetforms-divider__inner-separator-no-content' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_responsive_control(
			'divide_border_width',
			[
				'type'        => 'slider',
				'label'       => __( 'Border Width', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator::before, {{WRAPPER}} .piotnetforms-divider__inner-separator::after, {{WRAPPER}} .piotnetforms-divider__inner-separator-no-content' => 'border-width: {{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_control(
			'divide_align',
			[
				'type'       => 'select',
				'label'      => __( 'Alignment', 'piotnetforms' ),
				'value'      => 'left',
				'options'    => [
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'conditions' => [
					[
						'name'     => 'divide_content',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			]
		);
		$this->add_control(
			'divide_has_content_align',
			[
				'type'       => 'select',
				'label'      => __( 'Alignment', 'piotnetforms' ),
				'value'      => 'left',
				'options'    => [
					'flex-start' => __( 'Left', 'piotnetforms' ),
					'center'     => __( 'Center', 'piotnetforms' ),
					'flex-end'   => __( 'Right', 'piotnetforms' ),
				],
				'conditions' => [
					[
						'name'     => 'divide_content',
						'operator' => '==',
						'value'    => 'none',
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-divider__inner-no-border' => 'justify-content: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'divide_content',
			[
				'type'    => 'select',
				'label'   => __( 'Content', 'piotnetforms' ),
				'value'   => 'none',
				'options' => [
					'none' => __( 'None', 'piotnetforms' ),
					'text' => __( 'Text', 'piotnetforms' ),
					'icon' => __( 'Icon', 'piotnetforms' ),
				],
			]
		);
		$this->add_control(
			'divide_text_content',
			[
				'type'        => 'text',
				'label'       => __( 'Content Text', 'piotnetforms' ),
				'value'       => 'Text',
				'label_block' => false,
				'placeholder' => 'Enter content divider',
				'conditions'  => [
					[
						'name'     => 'divide_content',
						'operator' => '==',
						'value'    => 'text',
					],
				],
			]
		);
		$this->add_control(
			'divide_icon_content',
			[
				'type'           => 'icon',
				'label'          => __( 'Select icon', 'piotnetforms' ),
				'value'          => 'fas fa-star',
				'options_source' => 'fontawesome',
				'conditions'     => [
					[
						'name'     => 'divide_content',
						'operator' => '==',
						'value'    => 'icon',
					],
				],
			]
		);
	}

	private function add_style_controls() {
		$this->add_control(
			'divider_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator::before, {{WRAPPER}} .piotnetforms-divider__inner-separator::after' => 'border-color: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'divide_gap',
			[
				'type'        => 'slider',
				'label'       => __( 'Gap', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator, {{WRAPPER}} .piotnetforms-divider__inner-no-border' => 'padding-top: {{SIZE}}{{UNIT}};padding-bottom:{{SIZE}}{{UNIT}}',
				],
			]
		);
	}

	private function add_text_setting_controls() {
		$this->add_control(
			'divider_text_color',
			[
				'type'        => 'color',
				'label'       => __( 'Color', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator-item' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_text_typography_controls(
			'divider_typography',
			[
				'selectors' => '{{WRAPPER}}',
			]
		);
		$this->add_responsive_control(
			'text_divide_spacing',
			[
				'type'        => 'slider',
				'label'       => __( 'Spacing', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator-item' => 'margin-left: {{SIZE}}{{UNIT}};margin-right:{{SIZE}}{{UNIT}}',
				],
			]
		);
	}

	private function add_icon_setting_controls() {
		$this->add_responsive_control(
			'divider_icon_size',
			[
				'type'        => 'slider',
				'label'       => __( 'Size', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 200,
						'step' => 1,
					],
					'em' => [
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator-item i' => 'font-size:{{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_control(
			'divider_icon_color',
			[
				'type'        => 'color',
				'label'       => __( 'Color', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator-item i' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'icon_divide_spacing',
			[
				'type'        => 'slider',
				'label'       => __( 'Spacing', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-divider__inner-separator-item' => 'margin-left: {{SIZE}}{{UNIT}};margin-right:{{SIZE}}{{UNIT}}',
				],
			]
		);
	}

	public function render() {
		$settings = $this->settings;
		if ( $settings['divider_item'] == 'text' ) {
			$content = ! empty( $settings['divide_text_content'] ) ? $settings['divide_text_content'] : 'Text';
		} elseif ( $settings['divider_item'] == 'icon' ) {
			$content = '<i class="' . $settings['divide_select_icon'] . '"></i>';
		} else {
			$content = '';
		}
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-divider' ); ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="piotnetforms-divider__inner <?php echo $settings['divide_align']; ?>">
			<?php if ( ! empty( $content ) ) { ?>
				<span class="piotnetforms-divider__inner-separator">
						<?php if ( ! empty( $settings['divide_text_content'] ) ) { ?>
						<span class="piotnetforms-divider__inner-separator-item">
						<span><?php echo $content; ?></span>
						</span>
							<?php
						} else {
							;
							?>
						<span class="piotnetforms-divider__inner-separator-item">
							<?php echo $content; ?>
						</span>
						<?php } ?>
				</span>
			<?php } else { ?>
				<span class="piotnetforms-divider__inner-no-border">
					<span class="piotnetforms-divider__inner-separator-no-content"></span>
				</span>
			<?php } ?>
			</div>
		</div>
		<?php
	}
	public function live_preview() {
		?>
		<%
			view.add_attribute('wrapper', 'class', 'piotnetforms-divider');
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<div class="piotnetforms-divider__inner <%= data.widget_settings.divide_align %>">
				<% if(data.widget_settings.divide_text_content != 'none'){ %>
					<span class="piotnetforms-divider__inner-separator">
						<% if(data.widget_settings.divide_content  == 'text'){ %>
							<span class="piotnetforms-divider__inner-separator-item">
								<span><%= data.widget_settings.divide_text_content %></span>
							</span>
						<% }else{ %>
							<span class="piotnetforms-divider__inner-separator-item">
								<i class="<%= data.widget_settings.divide_icon_content %>"></i>
							</span>
						<% } %>
					</span>
				<% }else{ %>
					<span class="piotnetforms-divider__inner-no-border">
						<span class="piotnetforms-divider__inner-separator-no-content"></span>
					</span>
				<% } %>
			</div>
		</div>
		<?php
	}
}
