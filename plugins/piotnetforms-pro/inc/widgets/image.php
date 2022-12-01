<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Image extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'image';
	}

	public function get_class_name() {
		return 'piotnetforms_Image';
	}

	public function get_title() {
		return 'Image';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-image.svg',
		];
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'image' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'image_settings_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'image_styles_section', 'Image Style' );
		$this->add_style_controls();
		$this->start_section( 'caption_styles_section', 'Caption Style' );
		$this->add_caption_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function add_setting_controls() {
		$this->add_control(
			'image_select',
			[
				'type'        => 'media',
				'label'       => __( 'Choose Image', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'placeholder' => '',
			]
		);
		$this->add_control(
			'image_link_type',
			[
				'type'    => 'select',
				'label'   => __( 'Link', 'piotnetforms' ),
				'value'   => '',
				'options' => [
					''       => 'None',
					'file'   => 'Media File',
					'custom' => 'Custom URL',
				],
			]
		);
		$this->add_control(
			'image_link',
			[
				'type'        => 'text',
				'label'       => __( 'Link URL', 'piotnetforms' ),
				'value'       => '#',
				'label_block' => false,
				'placeholder' => '',
				'conditions'  => [
					[
						'name'     => 'image_link_type',
						'operator' => '==',
						'value'    => 'custom',
					],
				],
			]
		);
		$this->add_control(
			'image_link_target',
			[
				'type'       => 'select',
				'label'      => __( 'Link', 'piotnetforms' ),
				'value'      => '',
				'options'    => [
					'_self'  => 'Self',
					'_blank' => 'Blank',
				],
				'conditions' => [
					[
						'name'     => 'image_link_type',
						'operator' => '==',
						'value'    => 'custom',
					],
				],
			]
		);
		$this->add_control(
			'image_caption',
			[
				'type'        => 'text',
				'label'       => __( 'Caption', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'placeholder' => '',
			]
		);
		$this->add_responsive_control(
			'image_text_align',
			[
				'type'         => 'select',
				'label'        => __( 'Alignment', 'piotnetforms' ),
				'value'        => 'left',
				'options'      => [
					''   => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'prefix_class' => 'piotnetforms%s-align-',
				'selectors'    => [
					'{{WRAPPER}} .piotnetforms-image__content' => 'text-align: {{VALUE}}',
				],
			]
		);
	}

	private function add_style_controls() {
		$this->add_control(
			'image_custom_size',
			[
				'type'         => 'switch',
				'label'        => __( 'Custom Size', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_responsive_control(
			'image_width',
			[
				'type'        => 'slider',
				'label'       => __( 'Width', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-image__content img' => 'width:{{SIZE}}{{UNIT}}',
				],
				'conditions'  => [
					[
						'name'     => 'image_custom_size',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'image_opacity',
			[
				'type'        => 'slider',
				'label'       => __( 'Opacity', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					],
					'%'  => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-image__content img' => 'opacity:{{SIZE}}',
				],
			]
		);
		$this->add_control(
			'image_border_style',
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
					'{{WRAPPER}}' => 'border-style:{{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'image_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					'{{WRAPPER}}' => 'border-color: {{VALUE}};',
				],
				'conditions'  => [
					[
						'name'     => 'image_border_style',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'image_border_width',
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
					'{{WRAPPER}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'image_border_style',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'image_border_radius',
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
				'size_units'  => [ 'px', '%', 'em' ],
				'selectors'   => [
					'{{WRAPPER}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}

	private function add_caption_style_controls() {
		$this->add_control(
			'image_caption_color',
			[
				'type'        => 'color',
				'label'       => __( 'Color', 'piotnetforms' ),
				'value'       => '#000',
				'label_block' => true,
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-image__caption' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_text_typography_controls(
			'image_caption_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-image__caption',
			]
		);
		$this->add_responsive_control(
			'image_caption_text_align',
			[
				'type'         => 'select',
				'label'        => __( 'Alignment', 'piotnetforms' ),
				'value'        => 'left',
				'options'      => [
					''   => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'prefix_class' => 'piotnetforms%s-align-',
				'selectors'    => [
					'{{WRAPPER}} .piotnetforms-image__caption' => 'text-align: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'caption_background_color',
			[
				'type'        => 'color',
				'label'       => __( 'Background Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-image__caption' => 'background-color: {{VALUE}};',
				],
			]
		);
	}

	public function render() {
		$settings = $this->settings;
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-image' );
		$default_url_image = plugins_url() . '/piotnetforms-pro/assets/images/piotnetforms.png';
		$url_image         = ! empty( $settings['image_select']['url'] ) ? $settings['image_select']['url'] : $default_url_image;
		// if ( $settings['link_to'] == 'file' ) {
		// 	$image_link  = $settings['image_select']['url'];
		// 	$link_target = '_blank';
		// } else {
		// 	$image_link  = ! empty( $settings['image_link'] ) ? $settings['image_link'] : '#';
		// 	$link_target = ! empty( $settings['link_target'] ) ? $settings['link_target'] : '';
		// }
		$image_link  = ! empty( $settings['image_link'] ) && ! empty( $settings['image_link_type'] ) ? $settings['image_link'] : '';
		$link_target = ! empty( $settings['image_link_target'] ) ? $settings['image_link_target'] : '';
		$image_caption = ! empty( $settings['image_caption'] ) ? $settings['image_caption'] : ''; ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( empty( $image_link ) ) { ?>
			<div class="piotnetforms-image__content"><img class="piotnetforms-image__tag" src="<?php echo $url_image; ?>" ></div>
			<?php if ( ! empty( $image_caption ) ) : ?>
			<div class="piotnetforms-image__caption"><?php echo $image_caption; ?></div>
			<?php endif; ?>
			<?php } else { ?>
				<div class="piotnetforms-image__content">
					<a target="<?php echo $link_target; ?>" href="<?php echo $image_link; ?>"><img class="piotnetforms-image__tag" src="<?php echo $url_image; ?>" ></a>
				</div>
				<?php if ( ! empty( $image_caption ) ) : ?>
				<div class="piotnetforms-image__caption"><?php echo $image_caption; ?></div>
				<?php endif; ?>
			<?php } ?>
		</div>
		<?php
	}
	public function live_preview() {
		?>
		<%
			view.add_attribute('wrapper', 'class', 'piotnetforms-image');
			const widget_settings = data.widget_settings;
			const url = widget_settings && widget_settings.image_select ? widget_settings.image_select.url : '';
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<% if(widget_settings.image_link_type){ %>
				<div class="piotnetforms-image__content"><a target="<%= widget_settings.image_link_target %>"><img class="piotnetforms-image__tag" src="<%= url %>"></a></div>
				<% if(widget_settings.image_caption){ %>
					<div class="piotnetforms-image__caption"><%= widget_settings.image_caption %></div>
				<% } %>
			<% }else{ %>
				<div class="piotnetforms-image__content"><img class="piotnetforms-image__tag" src="<%= url %>"></div>
				<% if(widget_settings.image_caption){ %>
					<div class="piotnetforms-image__caption"><%= widget_settings.image_caption %></div>
				<% } %>
			<% } %>
		</div>
		<?php
	}
}
