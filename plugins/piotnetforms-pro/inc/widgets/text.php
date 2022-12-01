<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Text extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'text';
	}

	public function get_class_name() {
		return 'piotnetforms_Text';
	}

	public function get_title() {
		return 'Text';
	}

	public function get_icon() {
		return [
			'type'  => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-text.svg',
		];
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'text' ];
	}

	private function add_setting_controls() {
		$this->add_control(
			'text_content',
			[
				'type'        => 'textarea',
				'value'       => 'Text',
				'label_block' => true,
				'placeholder' => __( 'Content', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'text_html_tag',
			[
				'type'    => 'select',
				'label'   => 'HTML Tag',
				'value'   => 'h2',
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
			]
		);
		$this->add_control(
			'text_link',
			[
				'type'        => 'text',
				'label'       => __( 'Link URL', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'text_link_target',
			[
				'type'       => 'select',
				'label'      => __( 'Link Target', 'piotnetforms' ),
				'value'      => '_self',
				'options'    => [
					'_self'  => 'Self',
					'_blank' => 'Blank',
				],
			]
		);
		$this->add_responsive_control(
			'text_align',
			[
				'type'         => 'select',
				'label'        => 'Alignment',
				'label_block'  => true,
				'options'      => [
					''        => __( 'Default', 'piotnetforms' ),
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

	private function add_style_controls() {
		$this->add_control(
			'text_color',
			[
				'type'      => 'color',
				'label'     => 'Text Color',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_text_typography_controls(
			'text_typography',
			[
				'selectors' => '{{WRAPPER}}',
			]
		);
	}

	private function add_form_messages_controls() {
		$this->add_control(
			'form_messages',
			[
				'type'         => 'switch',
				'label'        => __( 'Enable', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'form_messages_status',
			[
				'type'    => 'select',
				'label'   => 'Type Message',
				'options' => [
					'success' => 'Success Message',
					'danger' => 'Error Message',
				],
				'conditions'  => [
					[
						'name'     => 'form_messages',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'text_settings_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_section( 'form_messages_section', 'Form Messages' );
		$this->add_form_messages_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'text_styles_section', 'Style' );
		$this->add_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	public function render() {
		$settings = $this->settings;
		$editor = ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) ? true : false;
		$text_link  = ! empty( $settings['text_link'] ) ? $settings['text_link'] : '';
		$text_link_target = ! empty( $settings['text_link_target'] ) ? $settings['text_link_target'] : '';

		if ( !empty( $settings['form_messages'] ) ) {
			$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-alert piotnetforms-alert--mail' );
			$this->add_render_attribute( 'widget', 'class', 'piotnetforms-message piotnetforms-message-' . $settings['form_messages_status'] . ' piotnetforms-message-custom' );
			if ( $editor ) {
				$this->add_render_attribute( 'widget', 'class', 'visible' );
			}
		}

		if ( ! empty( $settings['text_content'] ) ) {
			$text_content = !empty( $text_link ) ? '<a href="' . $text_link . '" target="' . $text_link_target . '">' . $settings['text_content'] . '</a>' : $settings['text_content'];

			if ( !empty( $settings['form_messages'] ) ) {
				$text_content = '<span ' . $this->get_render_attribute_string( 'widget' ) . '>' . $text_content . '</span>';
			}

			echo '<' . $settings['text_html_tag'] . ' ' . $this->get_render_attribute_string( 'wrapper' ) . '>' . $text_content . '</' . $settings['text_html_tag'] . '>';
		}
	}

	public function live_preview() {
		?>
		<<%=data['widget_settings']['text_html_tag']%> <%= view.render_attributes('wrapper') %>><%= data['widget_settings']['text_content'] %></<%=data['widget_settings']['text_html_tag'] %>>
		<?php
	}
}
