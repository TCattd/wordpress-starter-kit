<?php

class piotnetforms_Social_Icon extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'social-icons';
	}

	public function get_class_name() {
		return 'piotnetforms_Social_Icon';
	}

	public function get_title() {
		return 'Social Icons';
	}

	public function get_icon() {
		return 'fab fa-facebook';
	}

	public function get_categories() {
		return [ 'pafe-form-builder' ];
	}

	public function get_keywords() {
		return [ 'social-icon' ];
	}

	public function get_style() {
		return [ 
			'piotnetforms-fontawesome-style'
		];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'icon_social_settings_section', 'Icon Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'icon_social_styles_section', 'Style' );
		$this->add_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function add_setting_controls() {
		$this->new_group_controls();
		$this->add_control(
			'repeater_social_icon',
			[
				'type'           => 'icon',
				'label'          => __( 'Select Icon', 'piotnetforms' ),
				'value'          => 'fab fa-facebook',
				'options_source' => 'fontawesome',
				'show_heading'   => true,
			]
		);
		$this->add_control(
			'repeater_custom_icon_color',
			[
				'type'         => 'switch',
				'label'        => __( 'Custom Color?', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'label_block'  => false,
			]
		);
		$this->add_control(
			'repeater_icon_color',
			[
				'type'        => 'color',
				'label'       => __( 'Icon Color', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
				'selectors'   => [
					'{{CURRENT_ITEM}}' => 'color: {{VALUE}}',
				],
				'conditions'  => [
					[
						'name'     => 'repeater_custom_icon_color',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
		$this->add_control(
			'repeater_icon_hover_color',
			[
				'type'        => 'color',
				'label'       => __( 'Hover Color', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}}.piotnetforms-icon__item default-circle i' => 'color: {{VALUE}}',
				],
				'conditions'  => [
					[
						'name'     => 'repeater_custom_icon_color',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
		$this->add_control(
			'repeater_icon_social_link',
			[
				'type'         => 'text',
				'label'        => __( 'Link', 'piotnetforms' ),
				'value'        => '',
				'show_heading' => true,
			]
		);
        $this->add_control(
            'repeater_id',
            [
                'type' => 'hidden',
            ],
            [
                'overwrite' => 'true',
            ]
        );
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'repeater_icon_social_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Icon List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);

		$this->add_control(
			'icon_social_shape',
			[
				'type'    => 'select',
				'label'   => __( 'Shape', 'piotnetforms' ),
				'value'   => 'left',
				'options' => [
					'rounded' => 'Rounded',
					'square'  => 'Square',
					'circle'  => 'Circle',
				],
			]
		);
		$this->add_control(
			'icon_social_list_align',
			[
				'type'      => 'select',
				'label'     => __( 'Alignment', 'piotnetforms' ),
				'value'     => 'left',
				'options'   => [
					'rounded' => 'Rounded',
					'square'  => 'Square',
					'circle'  => 'Circle',
				],
				'options'   => [
					'flex-start' => 'Left',
					'center'     => 'Center',
					'flex-end'   => 'Right',
				], // FIXME: ndha
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-social-icon__items' => 'justify-content: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'icon_social_link_target',
			[
				'type'    => 'select',
				'label'   => __( 'Link Target', 'piotnetforms' ),
				'value'   => '_self',
				'options' => [
					'_self'  => 'Self',
					'_blank' => 'Blank',
				],
			]
		);
	}
	private function add_style_controls() {
		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'social_normal_controls',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'social_hover_controls',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_tab_style_controls(
			'social_normal',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-social-icon__item i',
			]
		);
		$this->add_control(
			'social_normal_controls',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->add_tab_style_controls(
			'social_hover',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-social-icon__item:hover i',
			]
		);
		$this->add_control(
			'social_hover_controls',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}
	private function add_tab_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$this->new_group_controls();
		$this->add_responsive_control(
			$name.'_icon_social_size',
			[
				'type'        => 'slider',
				'label'       => __( 'Size', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px'  => [
						'min'  => 1,
						'max'  => 200,
						'step' => 1,
					],
					'em'  => [
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					],
					'vw'  => [
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors'   => [
					$wrapper => 'font-size:{{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_social_padding',
			[
				'type'        => 'slider',
				'label'       => __( 'Padding', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 60,
						'step' => 1,
					],
					'em' => [
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors'   => [
					$wrapper => 'padding:{{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_social_spacing',
			[
				'type'        => 'slider',
				'label'       => __( 'Spacing', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 60,
						'step' => 1,
					],
					'em' => [
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors'   => [
					$wrapper => 'margin-right:{{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_control(
			$name.'_icon_social_border_type',
			[
				'type'      => 'select',
				'label'     => __( 'Border Type', 'piotnetforms' ),
				'value'     => '_self',
				'options'   => [
					''       => 'None',
					'solid'  => 'Solid',
					'double' => 'Double',
					'dotted' => 'Dotted',
					'dashed' => 'Dashed',
					'groove' => 'Groove',
				],
				'selectors' => [
					$wrapper => 'border-style:{{VALUE}};',
				],
			]
		);
		$this->add_control(
			$name.'_icon_social_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'border-color: {{VALUE}};',
				],
				'conditions'  => [
					[
						'name'     => 'icon_social_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_social_border_width',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Border Width', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'size_units'  => [ 'px', '%', 'em' ],
				'selectors'   => [
					$wrapper => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'icon_social_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_social_border_radius',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Border Radius', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'size_units'  => [ 'px', '%', 'em' ],
				'selectors'   => [
					$wrapper => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		return $this->get_group_controls();
	}
	public function render() {
		$settings = $this->settings;
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-social-icon' ); ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="piotnetforms-social-icon__items">
				<?php if ( ! empty( $settings['repeater_icon_social_list'] ) ) : ?>
					<div class="piotnetforms-social-icon-items">
						<?php foreach ( $settings['repeater_icon_social_list'] as $item ) : ?>
							<?php if ( ! empty( $item['repeater_icon_social_link'] ) ) { ?>
							<div class="piotnetforms-social-icon__item <?php echo $settings['icon_social_shape']; ?>">
								<a target="<?php echo $settings['icon_social_link_target']; ?>" href="<?php echo $item['repeater_icon_social_link']; ?>"><i class="<?php echo $item['repeater_social_icon']; ?>"></i></a>
							</div>
						<?php } else { ?>
							<div class="piotnetforms-social-icon__item <?php echo $settings['icon_social_shape']; ?>">
								<i class="<?php echo $item['repeater_social_icon']; ?>"></i>
							</div>
						<?php } ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
	public function live_preview() {
		?>
	<%
		view.add_attribute('wrapper', 'class', 'piotnetforms-social-icon');
	%>
	<div <%= view.render_attributes('wrapper') %>>
		<% if(data.widget_settings.repeater_icon_social_list){ %>
			<div class="piotnetforms-social-icon__items">
			<% _.each(data.widget_settings.repeater_icon_social_list, function(item, index){ %>
				<% if(item.repeater_icon_social_link){ %>
					<div class="piotnetforms-social-icon__item <%= data.widget_settings.icon_social_shape %>">
						<a target="<%= data.widget_settings.icon_social_link_target %>" href="<%= item.repeater_icon_social_link %>"><i class="<%= item.repeater_social_icon %>"></i></a>
					</div>
				<% }else{ %>
					<div class="piotnetforms-social-icon__item <%= data.widget_settings.icon_social_shape %>">
						<i class="<%= item.repeater_social_icon %>"></i>
					</div>
				<% } %>
			<% }) %>
			</div>
		<% } %>
	</div>

		<?php
	}

}
