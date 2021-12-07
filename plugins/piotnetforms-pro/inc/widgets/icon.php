<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class piotnetforms_Icon extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'icon';
	}

	public function get_class_name() {
		return 'piotnetforms_Icon';
	}

	public function get_title() {
		return 'Icon';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/i-icon.svg',
		];
	}

	public function get_categories() {
		return [ 'pafe-form-builder' ];
	}

	public function get_keywords() {
		return [ 'icon' ];
	}

	public function get_style() {
		return [ 
			'piotnetforms-fontawesome-style'
		];
	}

	private function add_style_controls() {
		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'icon_style_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'icon_style_hover_tab',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_tab_style_controls(
			'style_normal',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-icon__item i',
			]
		);
		$this->add_control(
			'icon_style_normal_tab',
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
			'style_hover',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-icon__item:hover i',
			]
		);
		$this->add_control(
			'icon_style_hover_tab',
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
		$this->add_control(
			$name.'_icon_color',
			[
				'type'      => 'color',
				'label'     => 'Icon Color',
				'selectors' => [
					$wrapper => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_size',
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
		$this->add_control(
			$name.'_icon_border_style',
			[
				'type'      => 'select',
				'label'     => __( 'Border Type', 'piotnetforms' ),
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
		$this->add_responsive_control(
			$name.'_icon_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'border-color: {{VALUE}};',
				],
				'conditions'  => [
					[
						'name'     => $name.'_icon_border_style',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_border_width',
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
						'name'     => $name.'_icon_border_style',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_control(
			$name.'_icon_background_color',
			[
				'type'      => 'color',
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_padding',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Padding', 'piotnetforms' ),
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
					$wrapper => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_icon_border_radius',
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
	private function add_setting_controls() {
		$this->add_control(
			'icon',
			[
				'type'           => 'icon',
				'label'          => __( 'Select icon', 'piotnetforms' ),
				'value'          => 'fas fa-star',
				'options_source' => 'fontawesome',
			]
		);
		$this->add_control(
			'icon_type',
			[
				'type'    => 'select',
				'label'   => __( 'Icon Type', 'piotnetforms' ),
				'value'   => 'default',
				'options' => [
					'default' => 'Default',
					'stacked' => 'Stacked',
					'framed'  => 'Framed',
				],
			]
		);
		$this->add_control(
			'icon_shape',
			[
				'type'       => 'select',
				'label'      => __( 'Icon Shape', 'piotnetforms' ),
				'value'      => 'circle',
				'options'    => [
					'circle' => 'Circle',
					'square' => 'Square',
				],
				'conditions' => [
					[
						'name'     => 'icon_type',
						'operator' => '!=',
						'value'    => 'default',
					],
				],
			]
		);
		$this->add_control(
			'icon_link',
			[
				'type'        => 'text',
				'label'       => __( 'Icon Link', 'piotnetforms' ),
				'placeholder' => 'Enter your link here',
			]
		);
		$this->add_control(
			'icon_link_target',
			[
				'type'       => 'select',
				'label'      => __( 'Link Target', 'piotnetforms' ),
				'options'    => [
					'_self'  => 'Self',
					'_blank' => 'Blank',
				],
				'conditions' => [
					[
						'name'     => 'icon_link',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'icon_alignment',
			[
				'type'         => 'select',
				'label'        => __( 'Alignment', 'piotnetforms' ),
				'value'        => 'left',
				'label_block'  => true,
				'options'      => [
					'left'    => __( 'Left', 'piotnetforms' ),
					'center'  => __( 'Center', 'piotnetforms' ),
					'right'   => __( 'Right', 'piotnetforms' ),
					'justify' => __( 'Justified', 'piotnetforms' ),
				],
				'prefix_class' => 'piotnetforms%s-align-',
				'selectors'    => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);
	}
	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'icon_settings_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'icon_styles_section', 'Style' );
		$this->add_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	public function render() {
		$settings         = $this->settings;
		$icon             = $settings['icon'] ? $settings['icon'] : '';
		$icon_type        = $settings['icon_type'] ? $settings['icon_type'] : 'default';
		$icon_shape       = ! empty( $settings['icon_shape'] ) ? $settings['icon_shape'] : '';
		$icon_link        = $settings['icon_link'] ? $settings['icon_link'] : '';
		$icon_link_target = ! empty( $settings['icon_link_target'] ) ? $settings['icon_link_target'] : '_self';
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-icon' );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( empty( $icon_link ) ) { ?>
			<div class="piotnetforms-icon__item <?php echo $icon_type . '-' . $icon_shape; ?>"><i class="<?php echo $icon; ?>"></i></div>
			<?php } else { ?>
			<a target="<?php echo $icon_link_target; ?>" href="<?php echo $icon_link; ?>"><div class="piotnetforms-icon__item <?php echo $icon_type . '-' . $icon_shape; ?>"><i class="<?php echo $icon; ?>"></i></div></a>
			<?php } ?>
		</div>
		<?php
	}
	public function live_preview() {
		?>
		<%
			view.add_attribute('wrapper', 'class', 'piotnetforms-icon');
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<% if(data.widget_settings.icon_link){ %>
				<a target="<%= data.widget_settings.icon_link_target %>" href="<%= data.widget_settings.icon_link %>"><div class="piotnetforms-icon__item <%= data.widget_settings.icon_type %>-<%= data.widget_settings.icon_shape %>"><i class="<%= data['widget_settings']['icon'] %>"></i></div></a>
			<% }else{ %>
				<div class="piotnetforms-icon__item <%= data.widget_settings.icon_type %>-<%= data.widget_settings.icon_shape %>"><i class="<%= data['widget_settings']['icon'] %>"></i></div>
			<% } %>
		</div>
		<?php
	}

}
