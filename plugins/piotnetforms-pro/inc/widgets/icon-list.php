<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Icon_List extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'icon-list';
	}

	public function get_class_name() {
		return 'piotnetforms_Icon_List';
	}

	public function get_title() {
		return 'Icon List';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-icon-list.svg',
		];
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'icon' ];
	}

	public function get_style() {
		return [
			'piotnetforms-fontawesome-style',
			'piotnetforms-icon-list-style'
		];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'icon_list_settings_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'icon_list_styles_section', 'Style' );
		$this->add_style_controls();
		$this->start_section( 'icon_list_style_icon_section', 'Icon Style' );
		$this->add_style_icon_controls();
		$this->start_section( 'icon_list_style_text_section', 'Text Style' );
		$this->add_style_text_controls();

		$this->add_advanced_tab();
		return $this->structure;
	}
	private function add_setting_controls() {
		$this->add_control(
			'icon_list_style',
			[
				'type'    => 'select',
				'label'   => __( 'Style', 'piotnetforms' ),
				'value'   => 'vertical',
				'options' => [
					'vertical'   => 'Vertical',
					'horizontal' => 'Horizontal',
				],
			]
		);

		$this->new_group_controls();
		$this->add_control(
			'icon_list_text',
			[
				'type'         => 'text',
				'label'        => __( 'Content', 'piotnetforms' ),
				'value'        => 'Text',
				'show_heading' => true,
			]
		);
		$this->add_control(
			'icon_list_icon',
			[
				'type'           => 'icon',
				'label'          => __( 'Select Icon', 'piotnetforms' ),
				'value'          => 'fas fa-star',
				'options_source' => 'fontawesome',
			]
		);
		$this->add_control(
			'icon_list_link',
			[
				'type'         => 'text',
				'label'        => __( 'Link', 'piotnetforms' ),
				'value'        => 'Text',
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
			'icon_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Select Icon', 'piotnetforms' ),
				'value'          => 'Text',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
		$this->add_control(
			'icon_list_link_target',
			[
				'type'    => 'select',
				'label'   => __( 'Link Target', 'piotnetforms' ),
				'options' => [
					'_self'  => 'Self',
					'_blank' => 'Blank',
				],
			]
		);
	}

	private function add_style_controls() {
		$this->add_control(
			'icon_list_vertical_text_align',
			[
				'type'      => 'select',
				'label'     => __( 'Alignment', 'piotnetforms' ),
				'value'     => 'left',
				'options'   => [
					'flex-start' => 'Left',
					'center'     => 'Center',
					'flex-end'   => 'Right',
				],
				'selectors' => [
					'{{WRAPPER}}.piotnetforms-icon-list' => 'justify-content: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'icon_list_vertical_space',
			[
				'type'        => 'slider',
				'label'       => __( 'Space', 'piotnetforms' ),
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
					'{{WRAPPER}} li.piotnetforms-icon-list__item' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'conditions'  => [
					[
						'name'     => 'icon_list_style',
						'operator' => '==',
						'value'    => 'vertical',
					],
				],
			]
		);
		$this->add_responsive_control(
			'icon_list_horizontal_space',
			[
				'type'        => 'slider',
				'label'       => __( 'Space', 'piotnetforms' ),
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
					'{{WRAPPER}} li.piotnetforms-icon-list__item' => 'margin-right: {{SIZE}}{{UNIT}}',
				],
				'conditions'  => [
					[
						'name'     => 'icon_list_style',
						'operator' => '==',
						'value'    => 'horizontal',
					],
				],
			]
		);
	}

	private function add_style_icon_controls() {
		$this->add_control(
			'icon_list_style_icon_section_color',
			[
				'type'      => 'color',
				'label'     => __( 'Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-icon-list__item-icon i' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'icon_list_style_icon_hover_section_color',
			[
				'type'      => 'color',
				'label'     => __( 'Hover Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-icon-list__item-icon i:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'icon_list_style_icon_section_size',
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
					'{{WRAPPER}} .piotnetforms-icon-list__item-icon i' => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);
	}

	private function add_style_text_controls() {
		$this->add_control(
			'icon_list_style_text_section_color',
			[
				'type'      => 'color',
				'label'     => __( 'Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-icon-list__item-text' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'icon_list_style_text_hover_color',
			[
				'type'      => 'color',
				'label'     => __( 'Hover Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-icon-list__item-text:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_text_typography_controls(
			'icon_list_text_typography',
			[
				'selectors' => '{{WRAPPER}}',
			]
		);
		$this->add_responsive_control(
			'icon_list_style_text_space_section_size',
			[
				'type'        => 'slider',
				'label'       => __( 'Space width Icon', 'piotnetforms' ),
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
					'{{WRAPPER}} .piotnetforms-icon-list__item-text' => 'margin-left: {{SIZE}}{{UNIT}}',
				],
			]
		);
	}
	public function render() {
		$settings = $this->settings;
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-icon-list ' . $settings['icon_list_style'] . '' ); ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<ul class="piotnetforms-icon-list__inner <?php echo $settings['icon_list_style']; ?>">
		<?php if ( ! empty( $settings['icon_list'] ) ) : ?>
			<?php foreach ( $settings['icon_list'] as $item ) : ?>
				<?php
				if ( ! empty( $item['icon_list_link'] ) ) {
					$icon_html = '<a class="piotnetforms-icon-list__item--align" target="' . $settings['icon_list_link_target'] . '" href="' . $item['icon_list_link'] . '">
                        <span class="piotnetforms-icon-list__item-icon"><i class="' . $item['icon_list_icon'] . '"></i></span>
                        <span class="piotnetforms-icon-list__item-text">' . $item['icon_list_text'] . '</span>
                        </a>';
				} else {
					$icon_html = '<span class="piotnetforms-icon-list__item-icon"><i class="' . $item['icon_list_icon'] . '"></i></span><span class="piotnetforms-icon-list__item-text">' . $item['icon_list_text'] . '</span>';
				} ?>
				<li class="piotnetforms-icon-list__item piotnetforms-icon-list__item--align">
					<?php echo $icon_html; ?>
				</li>
				<?php
			endforeach;
		endif; ?>
		</div>
		</ul>
		<?php
	}
	public function live_preview() {
		?>
		<%
			view.add_attribute('wrapper', 'class', 'piotnetforms-icon-list');
			view.add_attribute('wrapper', 'class', data.widget_settings.icon_list_style);
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<% if(data.widget_settings.icon_list){ %>
			<ul class="piotnetforms-icon-list__inner <%= data.widget_settings.icon_list_style %>">
				<% _.each(data.widget_settings.icon_list, function(item, index){ %>
					<% if(item.icon_list_link){ %>
						<li class="piotnetforms-icon-list__item piotnetforms-icon-list__item--align">
							<a class="piotnetforms-icon-list__item--align" target="<%= data.widget_settings.icon_list_link_target %>" href="<%= item.icon_list_link %>">
								<span class="piotnetforms-icon-list__item-icon"><i class="<%= item.icon_list_icon %>"></i></span> 
								<span class="piotnetforms-icon-list__item-text"><%= item.icon_list_text %></span>
							</a>
						</li>
					<% }else{ %>
						<li class="piotnetforms-icon-list__item piotnetforms-icon-list__item--align">
							<span class="piotnetforms-icon-list__item-icon"><i class="<%= item.icon_list_icon %>"></i></span>
							<span class="piotnetforms-icon-list__item-text"><%= item.icon_list_text %></span>
						</li>
					<% } %>
				<% }) %>
			</ul>
			<% } %>
		</div>
		<?php
	}
}
