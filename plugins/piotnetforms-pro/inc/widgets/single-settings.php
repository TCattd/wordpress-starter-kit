<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Single_Settings extends Base_Widget_piotnetforms {
	protected $is_add_conditional_logic = false;

	public $is_global = true;

	public function get_type() {
		return 'single-settings';
	}

	public function get_class_name() {
		return 'Piotnetforms_Single_Settings';
	}

	public function get_title() {
		return 'Single Settings';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/i-field.svg',
		];
	}

	public function get_categories() {
		return [ 'piotnetforms' ];
	}

	public function get_keywords() {
		return [ 'settings' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Form Settings' );
		$this->start_section( 'section_form_settings', 'Form Settings' );
		$this->add_form_settings_controls();

		$this->start_section( 'section_style_piotnet_form_label', 'Field Label' );
		$this->add_label_style_controls();

		$this->start_section( 'section_style_piotnet_form_field', 'Field' );
		$this->add_field_style_controls();

		$this->start_section( 'button_style_section', 'Submit Button' );
		$this->add_button_style_controls();

		$this->start_section( 'message_style_section', 'Submit Messages' );
		$this->add_message_style_controls();

		return $this->structure;
	}

	private function add_label_style_controls() {
		$post_id = !empty( $_GET['post'] ) ? $_GET['post'] : '';

		$this->add_control(
			'label_spacing',
			[
				'label'     => __( 'Spacing', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => '',
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'body.rtl .piotnetforms-' . $_GET['post'] . ' .piotnetforms-labels-inline .piotnetforms-field-group > label' => 'padding-left: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body:not(.rtl) .piotnetforms-' . $_GET['post'] . ' .piotnetforms-labels-inline .piotnetforms-field-group > label' => 'padding-right: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body .piotnetforms-' . $_GET['post'] . ' .piotnetforms-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
					// for the label position = above option
				],
			]
		);

		$this->add_responsive_control(
			'label_text_align',
			[
				'type'        => 'select',
				'label'       => __( 'Text Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					'.piotnetforms-' . $_GET['post'] . ' .piotnetforms-field-label' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'label_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'label_focus_tab',
						'title' => __( 'FOCUS', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->label_style_tab_controls(
			'',
			[
				'wrapper' => '.piotnetforms-' . $_GET['post'],
			]
		);
		$this->add_control(
			'label_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$focus_controls = $this->label_style_tab_controls(
			'focus',
			[
				'wrapper' => '.piotnetforms-' . $_GET['post'] . ' .piotnetforms-field-focus',
			]
		);
		$this->add_control(
			'label_focus_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Focus', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $focus_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function label_style_tab_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['wrapper'] ) ? $args['wrapper'] : '.piotnetforms-' . $_GET['post'];
		$name = !empty( $name ) ? '_' . $name : '';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'label_color' . $name,
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group > label, .piotnetforms-' . $_GET['post'] . ' .piotnetforms-field-subgroup label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mark_required_color' . $name,
			[
				'label'     => __( 'Mark Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' .piotnetforms-mark-required .piotnetforms-field-label:after' => 'color: {{COLOR}};',
				],
				'condition' => [
					'mark_required' => 'yes',
				],
			]
		);

		$this->add_text_typography_controls(
			'label_typography' . $name,
			[
				'selectors' => $wrapper . ' .piotnetforms-field-group > label',
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function field_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['wrapper'] ) ? $args['wrapper'] : '.piotnetforms-' . $_GET['post'];
		$name = !empty( $name ) ? '_' . $name : '';
		$previous_controls = $this->new_group_controls();
		$this->add_responsive_control(
			'field_text_align' . $name,
			[
				'type'        => 'select',
				'label'       => __( 'Text Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_text_color' . $name,
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'field_typography' . $name,
			[
				'selectors' => $wrapper . ' .piotnetforms-field-group .piotnetforms-field, ' . $wrapper . ' .piotnetforms-field-subgroup label, ' . $wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper select',
			]
		);

		$this->add_control(
			'field_background_color' . $name,
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '#ffffff',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'background-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'background-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper select' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'input_max_width' . $name,
			[
				'label'      => __( 'Input Max Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1500,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
				],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'max-width: {{SIZE}}{{UNIT}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field .piotnetforms-field-textual' => 'max-width: {{SIZE}}{{UNIT}}!important;',
				],
			]
		);

		$this->add_responsive_control(
			'input_height' . $name,
			[
				'label'      => __( 'Input Height', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
				],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field-container .mce-tinymce iframe' => 'height: {{SIZE}}{{UNIT}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field-textual' => 'height: {{SIZE}}{{UNIT}}!important;',
				],
				'condition'  => [
					'field_type' => 'tinymce',
				],
			]
		);

		$this->add_responsive_control(
			'input_padding' . $name,
			[
				'label'      => __( 'Input Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'field_type!' => 'checkbox',
				],
			]
		);

		$this->add_control(
			'input_placeholder_color' . $name,
			[
				'label'     => __( 'Input Placeholder Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)::placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)::-webkit-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)::-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper):-ms-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper):-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual::placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual::-webkit-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual::-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual:-ms-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual:-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
				],
			]
		);

		$this->add_control(
			'field_border_type' . $name,
			[
				'label'     => _x( 'Border Type', 'Border Control', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					''       => __( 'None', 'piotnetforms' ),
					'solid'  => _x( 'Solid', 'Border Control', 'piotnetforms' ),
					'double' => _x( 'Double', 'Border Control', 'piotnetforms' ),
					'dotted' => _x( 'Dotted', 'Border Control', 'piotnetforms' ),
					'dashed' => _x( 'Dashed', 'Border Control', 'piotnetforms' ),
					'groove' => _x( 'Groove', 'Border Control', 'piotnetforms' ),
				],
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-style: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'border-style: {{VALUE}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_width' . $name,
			[
				'label'     => _x( 'Width', 'Border Control', 'piotnetforms' ),
				'type'      => 'dimensions',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'field_border_type!' => '',
				],
			]
		);

		$this->add_control(
			'field_border_color' . $name,
			[
				'label'     => _x( 'Color', 'Border Control', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'border-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'field_border_type!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_radius' . $name,
			[
				'label'      => __( 'Border Radius', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'field_box_shadow' . $name,
			[
				'type'        => 'box-shadow',
				'label'       => __( 'Box Shadow', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'render_type' => 'none',
				'selectors'   => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'box-shadow: {{VALUE}};',
				],
			]
		);
		return $this->get_group_controls( $previous_controls );
	}

	private function add_field_style_controls() {
		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'field_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'field_focus_tab',
						'title' => __( 'FOCUS', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->field_style_controls(
			'',
			[
				'wrapper' => '.piotnetforms-' . $_GET['post'],
			]
		);
		$this->add_control(
			'field_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$focus_controls = $this->field_style_controls(
			'focus',
			[
				'wrapper' => '.piotnetforms-' . $_GET['post'] . ' .piotnetforms-field-focus',
			]
		);
		$this->add_control(
			'field_focus_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Focus', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $focus_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_button_style_controls() {
		$this->add_text_typography_controls(
			'typography',
			[
				'selectors' => '.piotnetforms-' . $_GET['post'] . ' a.piotnetforms-button, .piotnetforms-' . $_GET['post'] . ' .piotnetforms-button',
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
				'selectors' => '.piotnetforms-' . $_GET['post'] . ' a.piotnetforms-button, .piotnetforms-' . $_GET['post'] . ' .piotnetforms-button',
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
				'selectors' => '.piotnetforms-' . $_GET['post'] . ' a.piotnetforms-button:hover, .piotnetforms-' . $_GET['post'] . ' .piotnetforms-button:hover, .piotnetforms-' . $_GET['post'] . ' a.piotnetforms-button:focus, .piotnetforms-' . $_GET['post'] . ' .piotnetforms-button:focus',
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
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '.piotnetforms-' . $_GET['post'];
		$this->new_group_controls();
		$this->add_control(
			$name . 'button_text_color',
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'value'     => '',
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
				'size_units'  => [ 'px', 'em', '%' ],
				'selectors'   => [
					$wrapper => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		return $this->get_group_controls();
	}
	private function add_message_style_controls() {
		$this->add_text_typography_controls(
			'message_typography',
			[
				'selectors' => '.piotnetforms-' . $_GET['post'] . ' .piotnetforms-message',
			]
		);
		$this->add_control(
			'success_message_color',
			[
				'label'     => __( 'Success Message Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'.piotnetforms-' . $_GET['post'] . ' .piotnetforms-message.piotnetforms-message-success' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'error_message_color',
			[
				'label'     => __( 'Error Message Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'.piotnetforms-' . $_GET['post'] . ' .piotnetforms-message.piotnetforms-message-danger' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'inline_message_color',
			[
				'label'     => __( 'Inline Message Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'.piotnetforms-' . $_GET['post'] . ' .piotnetforms-message.piotnetforms-help-inline' => 'color: {{VALUE}};',
				],
			]
		);
	}

	private function add_form_settings_controls() {
		$post_id = !empty( $_GET['post'] ) ? $_GET['post'] : '';

		$this->add_control(
			'form_title',
			[
				'type'        => 'text',
				'label'       => __( 'Form Title', 'piotnetforms' ),
				'label_block' => true,
				'value'       => htmlspecialchars_decode( get_the_title( $post_id ) ),
			]
		);

		if ( empty( get_post_meta( $post_id, '_piotnetforms_version', true ) ) ) {
			$this->add_control(
				'form_id',
				[
					'type'        => 'text',
					'label'       => __( 'Form ID', 'piotnetforms' ),
					'description' => __( 'Used for forms created by Piotnet Forms Version 1.x.<br>Please duplicate this form to be able to apply new Form ID’s in Version 2.x.', 'piotnetforms' ),
					'label_block' => true,
					'value'       => !empty( get_post_meta( $post_id, '_piotnetforms_form_id', true ) ) ? get_post_meta( $post_id, '_piotnetforms_form_id', true ) : '',
				]
			);
		}

		$this->add_control(
			'form_width',
			[
				'type'        => 'slider',
				'label'       => __( 'Form Width', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 500,
						'max'  => 1600,
						'step' => 1,
					],
				],
				'selectors'   => [
					'.piotnetforms-' . $_GET['post'] => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);
	}

	public function render( $editor = false, $args_additional = [] ) {
	}

	public function live_preview() {
	}
}
