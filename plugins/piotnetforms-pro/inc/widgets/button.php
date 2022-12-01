<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Button extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'button';
	}

	public function get_class_name() {
		return 'piotnetforms_Button';
	}

	public function get_title() {
		return 'Button';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-button.svg',
		];
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'button' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'button_settings_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'button_styles_section', 'Style' );
		$this->add_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function add_setting_controls() {
		$this->add_control(
			'button_text',
			[
				'type'        => 'text',
				'label'       => __( 'Button text', 'piotnetforms' ),
				'value'       => 'Click here',
				'label_block' => false,
				'placeholder' => '',
			]
		);
		$this->add_control(
			'button_link',
			[
				'type'        => 'text',
				'label'       => __( 'Button Link', 'piotnetforms' ),
				'value'       => '',
				'value'		=> '#',
				'label_block' => false,
				'placeholder' => '',
			]
		);
		$this->add_control(
			'button_link_target',
			[
				'type'         => 'select',
				'label'        => __( 'Link Target', 'piotnetforms' ),
				'value'        => 'left',
				'options'      => [
					'_self'   => __( 'Self', 'piotnetforms' ),
					'_blank' => __( 'Blank', 'piotnetforms' ),
				],
				'prefix_class' => 'piotnetforms%s-align-',
				'conditions' => [
					[
						'name' => 'button_link',
						'operator' => '!=',
						'value' => '#'
					],
					[
						'name' => 'button_link',
						'operator' => '!=',
						'value' => ''
					]
				]
			]
		);
		$this->add_responsive_control(
			'button_text_align',
			[
				'type'         => 'select',
				'label'        => __( 'Alignment', 'piotnetforms' ),
				'label_block'  => true,
				'value'        => '',
				'options'      => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'prefix_class' => 'piotnetforms%s-align-',
				'selectors'    => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'button_icon',
			[
				'type'           => 'icon',
				'label'          => __( 'Button Icon', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => false,
				'placeholder'    => '',
				'options_source' => 'fontawesome',
			]
		);
		$this->add_control(
			'button_icon_position',
			[
				'type'       => 'select',
				'label'      => __( 'Icon Position', 'piotnetforms' ),
				'options'    => [
					'before' => __( 'Before', 'piotnetforms' ),
					'after'  => __( 'After', 'piotnetforms' ),
				],
				'conditions' => [
					[
						'name'     => 'button_icon',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'button_icon_spacing',
			[
				'type'        => 'slider',
				'label'       => __( 'Icon Spacing', 'piotnetforms' ),
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
					'{{WRAPPER}} .piotnet-button--icon-before i' => 'margin-right:{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .piotnet-button--icon-after i' => 'margin-left:{{SIZE}}{{UNIT}}',
				],
				'conditions' => [
					[
						'name'     => 'button_icon',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_control(
			'button_id',
			[
				'type'        => 'text',
				'label'       => __( 'Button ID', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'placeholder' => 'Enter your button id',
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
						'name'   => 'button_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'button_hover_tab',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_button_style_controls(
			'normal',
			[
				'selectors' => '{{WRAPPER}}.piotnetforms-btn .piotnet-button, {{WRAPPER}}.piotnetforms-btn a',
			]
		);
		$this->add_control(
			'button_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->add_button_style_controls(
			'hover',
			[
				'selectors' => '{{WRAPPER}}.piotnetforms-btn a:hover',
			]
		);
		$this->add_control(
			'button_hover_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_button_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();
		$this->add_control(
			$name.'_button_color',
			[
				'type'        => 'color',
				'label'       => __( 'Text Color', 'piotnetforms' ),
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_text_typography_controls(
			$name.'_button_text_typography',
			[
				'selectors' => $wrapper,
			]
		);
		$this->add_control(
			$name.'_button_background_color',
			[
				'type'        => 'color',
				'label'       => __( 'Background Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
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
			$name.'_button_border_radius',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Border Radius', 'piotnetforms' ),
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'label_block' => true,
				'size_units'  => [ 'px' ],
				'selectors'   => [
					$wrapper => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_button_padding',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Padding', 'piotnetforms' ),
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'label_block' => true,
				'size_units'  => [ 'px' ],
				'selectors'   => [
					$wrapper => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			$name.'_button_margin',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Margin', 'piotnetforms' ),
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'label_block' => true,
				'size_units'  => [ 'px' ],
				'selectors'   => [
					$wrapper => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		return $this->get_group_controls( $previous_controls );
	}

	public function render() {
		$settings = $this->settings;

		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-btn' );

		$button_icon = isset( $settings['button_icon'] ) ? $settings['button_icon'] : '';
		$button_text = isset( $settings['button_text'] ) ? $settings['button_text'] : '';
		$button_tag = 'span';

		$this->add_render_attribute( 'button', 'class', 'piotnet-button' );

		if ( !empty( $settings['button_link'] ) ) {
			$this->add_render_attribute( 'button', 'href', $settings['button_link'] );
			$button_tag = 'a';
		}

		if ( !empty( $settings['button_link_target'] ) ) {
			$this->add_render_attribute( 'button', 'target', $settings['button_link_target'] );
		}

		if ( !empty( $settings['button_id'] ) ) {
			$this->add_render_attribute( 'button', 'id', $settings['button_id'] );
		} ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php
			if ( !empty( $settings['button_icon'] ) ) {
				$this->add_render_attribute( 'button', 'class', 'piotnet-button--icon-' . $settings['button_icon_position'] );
				if ( $settings['button_icon_position'] == 'before' ) {
					?>
					<<?php echo $button_tag; ?> <?php echo $this->get_render_attribute_string( 'button' ); ?>><i class="<?php echo $button_icon; ?>"></i><?php echo $button_text; ?></<?php echo $button_tag; ?>>
				<?php
				} else { ?>
					<<?php echo $button_tag; ?> <?php echo $this->get_render_attribute_string( 'button' ); ?>><?php echo $button_text; ?> <i class="<?php echo $button_icon; ?>"></i></<?php echo $button_tag; ?>>
				<?php } ?>
			<?php
			} else { ?>
				<<?php echo $button_tag; ?> <?php echo $this->get_render_attribute_string( 'button' ); ?>><?php echo $button_text; ?></<?php echo $button_tag; ?>>
			<?php } ?>
		</div>	
		<?php
	}
	public function live_preview() {
		?>
		<%
			view.add_attribute('wrapper', 'class', 'piotnetforms-btn');
			view.add_attribute('button', 'class', 'piotnet-button');
			view.add_attribute('button_icon', 'class', data.widget_settings.button_icon);

			var target = data.widget_settings.button_link_target != undefined ? data.widget_settings.button_link_target : '';
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<% if(data.widget_settings.button_icon){
				view.add_attribute('button', 'class', 'piotnet-button--icon-' + data.widget_settings.button_icon_position );
				if(data.widget_settings.button_icon_position == 'before'){ %>
					<a href="<%= data.widget_settings.button_link %>" target="<%= target %>" <%= view.render_attributes('button') %> id="<%= data.widget_settings.button_id %>"><i <%= view.render_attributes('button_icon') %>></i> <%= data.widget_settings.button_text %></a>
				<% }else{ %>
					<a href="<%= data.widget_settings.button_link %>" target="<%= target %>" <%= view.render_attributes('button') %> id="<%= data.widget_settings.button_id %>"><%= data.widget_settings.button_text %> <i <%= view.render_attributes('button_icon') %>></i></a>
				<% } %>
			<% }else{ %>
				<a href="<%= data.widget_settings.button_link %>" target="<%= target %>" <%= view.render_attributes('button') %> id="<%= data.widget_settings.button_id %>"><%= data.widget_settings.button_text %></a>
			<% } %>
		</div>
		<?php
	}
}
