<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Booking extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'booking';
	}

	public function get_class_name() {
		return 'Piotnetforms_Booking';
	}

	public function get_title() {
		return 'Booking';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-booking.svg',
		];
	}

	public function get_categories() {
		return [ 'form' ];
	}

	public function get_keywords() {
		return [ 'booking', 'appointment' ];
	}

	public function get_script() {
		return [
			'piotnetforms-advanced-script',
		];
	}

	public function get_style() {
		return [
			'piotnetforms-form-booking-style'
		];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'button_setting_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'label_style_section', 'Label Style' );
		$this->label_style_controls();

		$this->start_section( 'item_style_section', 'Item Style' );
		$this->item_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function add_setting_controls() {
		$this->add_control(
			'piotnetforms_booking_form_id',
			[
				'label' => __( 'Form ID* (Required)', 'piotnetforms' ),
				'type' => 'hidden',
				'description' => __( 'Enter the same form id for all fields in a form', 'piotnetforms' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_id',
			[
				'label' => __( 'Booking ID* (Required)', 'piotnetforms' ),
				'type' => 'text',
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'shortcode',
			[
				'label' => __( 'Shortcode', 'piotnetforms' ),
				'type'    => 'text',
				'classes' => 'piotnetforms-field-shortcode',
				'attr'    => 'readonly',
				'copy'    => true,
			]
		);

		$this->add_control(
			'piotnetforms_booking_field_label',
			[
				'label' => __( 'Label', 'piotnetforms' ),
				'type' => 'text',
				'default' => '',
			]
		);

		$this->add_control(
			'piotnetforms_booking_field_label_show',
			[
				'label' => __( 'Show Label', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Show', 'piotnetforms' ),
				'label_off' => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'true',
				'default' => 'true',
			]
		);

		$this->add_control(
			'piotnetforms_booking_field_required',
			[
				'label' => __( 'Required', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Yes', 'piotnetforms' ),
				'label_off' => __( 'No', 'piotnetforms' ),
				'return_value' => 'true',
				'default' => '',
			]
		);

		$this->add_control(
			'piotnetforms_booking_mark_required',
			[
				'label' => __( 'Required Mark', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Show', 'piotnetforms' ),
				'label_off' => __( 'Hide', 'piotnetforms' ),
				'default' => '',
				'condition' => [
					'piotnetforms_booking_field_label!' => '',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_date_type',
			[
				'label' => __( 'Date Type', 'piotnetforms' ),
				'type' => 'select',
				'default' => 'date_picker',
				'options' => [
					'date_picker'  => __( 'Date Picker', 'piotnetforms' ),
					'specify_date' => __( 'Specify Date', 'piotnetforms' ),
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_date_field',
			[
				'label' => __( 'Date Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( '[field id="date"]', 'piotnetforms' ),
				'condition' => [
					'piotnetforms_booking_date_type' => 'date_picker'
				]
			]
		);

		$this->add_control(
			'piotnetforms_booking_date',
			[
				'label' => __( 'Date', 'piotnetforms' ),
				'type' => 'date',
				'label_block' => false,
				'picker_options' => [
					'enableTime' => false,
				],
				'condition' => [
					'piotnetforms_booking_date_type' => 'specify_date'
				]
			]
		);

		$this->add_control(
			'piotnetforms_booking_field_allow_multiple',
			[
				'label' => __( 'Multiple Selection', 'piotnetforms' ),
				'type' => 'switch',
				'return_value' => 'true',
				'default' => 'true',
			]
		);

		$this->add_control(
			'piotnetforms_booking_slot_quantity_field',
			[
				'label' => __( 'Slot Quantity Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( '[field id="quantity"]', 'piotnetforms' ),
				'conditions' => [
					'terms' => [
						[
							'name'     => 'piotnetforms_booking_field_allow_multiple',
							'operator' => '!=',
							'value'    => 'true',
						],
					],
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_before_number_of_slot',
			[
				'label' => __( 'Before Number Of Slot', 'piotnetforms' ),
				'type' => 'text',
				'default' => '',
			]
		);

		$this->add_control(
			'piotnetforms_booking_after_number_of_slot',
			[
				'label' => __( 'After Number Of Slot', 'piotnetforms' ),
				'type' => 'text',
				'default' => '',
			]
		);

		$this->add_control(
			'piotnetforms_booking_sold_out_text',
			[
				'label' => __( 'Sold Out Text', 'piotnetforms' ),
				'type' => 'text',
				'default' => '',
			]
		);

		$this->add_control(
			'piotnetforms_booking_field_slot_show',
			[
				'label' => __( 'Show Slot', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Show', 'piotnetforms' ),
				'label_off' => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'true',
				'default' => 'true',
			]
		);

		$this->add_control(
			'piotnetforms_booking_field_price_show',
			[
				'label' => __( 'Show Price', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Show', 'piotnetforms' ),
				'label_off' => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'true',
				'default' => 'true',
			]
		);

		$this->new_group_controls();

		$this->add_control(
			'piotnetforms_booking_slot_id',
			[
				'label' => __( 'Slot ID* (Required)', 'piotnetforms' ),
				'type' => 'text',
			]
		);

		$this->add_control(
			'piotnetforms_booking_slot',
			[
				'label' => __( 'Number of Slot', 'piotnetforms' ),
				'type' => 'number',
				'default' => 1,
			]
		);
		$this->add_control(
			'piotnetforms_booking_title',
			[
				'label' => __( 'Title', 'piotnetforms' ),
				'type' => 'text',
			]
		);

		$this->add_control(
			'piotnetforms_booking_price',
			[
				'label' => __( 'Price', 'piotnetforms' ),
				'type' => 'number',
			]
		);

		$this->add_control(
			'piotnetforms_booking_price_text',
			[
				'label' => __( 'Price Text', 'piotnetforms' ),
				'type' => 'text',
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
			'piotnetforms_booking',
			[
				'type'           => 'repeater',
				'label'          => __( 'Slot List', 'piotnetforms' ),
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}

	private function label_style_controls() {
		$this->add_control(
			'label_spacing',
			[
				'label' => __( 'Spacing', 'piotnetforms' ),
				'type' => 'slider',
				'default' => [
					'size' => 0,
					'unit' => 'px'
				],
				'size_units' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'body.rtl {{WRAPPER}} .piotnetforms-field-group > label' => 'padding-left: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body:not(.rtl) {{WRAPPER}} .piotnetforms-field-group > label' => 'padding-right: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body {{WRAPPER}} .piotnetforms-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
					// for the label position = above option
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => __( 'Text Color', 'piotnetforms' ),
				'type' => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-field-group > label, {{WRAPPER}} .piotnetforms-field-subgroup label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mark_required_color',
			[
				'label' => __( 'Mark Color', 'piotnetforms' ),
				'type' => 'color',
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-mark-required .piotnetforms-field-label:after' => 'color: {{COLOR}};',
				],
				'condition' => [
					'mark_required' => 'yes',
				],
			]
		);

		$this->add_text_typography_controls(
			'label_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-field-group > label',
			]
		);
	}


	private function item_style_controls() {
		$this->add_responsive_control(
			'piotnetforms_booking_item_width',
			[
				'label' => __( 'Item Width', 'piotnetforms' ),
				'type' => 'slider',
				'size_units' => [
					'px' => [
						'min' => 0,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 25,
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-booking__item' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_align',
			[
				'label' => __( 'Alignment', 'piotnetforms' ),
				'type' => 'select',
				'options'      => [
					''        => __( 'Default', 'piotnetforms' ),
					'left'    => __( 'Left', 'piotnetforms' ),
					'center'  => __( 'Center', 'piotnetforms' ),
					'right'   => __( 'Right', 'piotnetforms' ),
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-booking__item' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_booking_item_padding',
			[
				'label' => __( 'Padding', 'piotnetforms' ),
				'type' => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-booking__item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_booking_item_margin',
			[
				'label' => __( 'Margin', 'piotnetforms' ),
				'type' => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-booking__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .piotnetforms-booking__inner' => 'margin: 0 -{{RIGHT}}{{UNIT}} 0 -{{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_booking_item_border_radius',
			[
				'label' => __( 'Border Radius', 'piotnetforms' ),
				'type' => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-booking__item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'title_typography',
			[
				'label' => __( 'Title Typography', 'piotnetforms' ),
				'selector' => '{{WRAPPER}} .piotnetforms-booking__title',
			]
		);

		$this->add_text_typography_controls(
			'slot_typography',
			[
				'label' => __( 'Slot Typography', 'piotnetforms' ),
				'selector' => '{{WRAPPER}} .piotnetforms-booking__slot',
			]
		);

		$this->add_text_typography_controls(
			'price_typography',
			[
				'label' => __( 'Price Typography', 'piotnetforms' ),
				'selector' => '{{WRAPPER}} .piotnetforms-booking__price',
			]
		);

		//

		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'booking_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'booking_selected_tab',
						'title' => __( 'SELECTED', 'piotnetforms' ),
					],
					[
						'name'  => 'booking_sold_out_tab',
						'title' => __( 'SOLD OUT', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_item_style_controls(
			'normal',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-booking__item .piotnetforms-booking__item-inner',
			]
		);
		$this->add_control(
			'booking_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$selected_controls = $this->add_item_style_controls_selected(
			'selected',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-booking__item.active .piotnetforms-booking__item-inner',
			]
		);
		$this->add_control(
			'booking_selected_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $selected_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$sold_out_controls = $this->add_item_style_controls_sold_out(
			'sold_out',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-booking__item.piotnetforms-booking__item--disabled .piotnetforms-booking__item-inner',
			]
		);
		$this->add_control(
			'booking_sold_out_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $sold_out_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_item_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'piotnetforms_booking_item_background_' . $name,
			[
				'label' => __( 'Background', 'piotnetforms' ),
				'type' => 'color',
				'default' => '#D53440',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_item_text_' . $name,
			[
				'label' => __( 'Text Color', 'piotnetforms' ),
				'type' => 'color',
				'default' => '#fff',
				'selectors' => [
					$wrapper => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_item_border_type_' . $name,
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
			'piotnetforms_booking_item_border_color_' . $name,
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
						'name'     => 'button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'piotnetforms_booking_item_border_width_' . $name,
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
						'name'     => 'button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function add_item_style_controls_selected( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'piotnetforms_booking_item_background_' . $name,
			[
				'label' => __( 'Background', 'piotnetforms' ),
				'type' => 'color',
				'default' => '#931b23',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_item_text_' . $name,
			[
				'label' => __( 'Text Color', 'piotnetforms' ),
				'type' => 'color',
				'default' => '#fff',
				'selectors' => [
					$wrapper => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_item_border_type_' . $name,
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
			'piotnetforms_booking_item_border_color_' . $name,
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
						'name'     => 'button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'piotnetforms_booking_item_border_width_' . $name,
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
						'name'     => 'button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function add_item_style_controls_sold_out( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'piotnetforms_booking_item_background_' . $name,
			[
				'label' => __( 'Background', 'piotnetforms' ),
				'type' => 'color',
				'default' => '#ccc',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_item_text_' . $name,
			[
				'label' => __( 'Text Color', 'piotnetforms' ),
				'type' => 'color',
				'default' => '#000',
				'selectors' => [
					$wrapper => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'piotnetforms_booking_item_border_type_' . $name,
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
			'piotnetforms_booking_item_border_color_' . $name,
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
						'name'     => 'button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			'piotnetforms_booking_item_border_width_' . $name,
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
						'name'     => 'button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	public function render() {
		$settings = $this->settings;
		$form_post_id = $this->post_id;
		$form_version = empty( get_post_meta( $form_post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $form_post_id, '_piotnetforms_version', true );
		$form_id = $form_version == 1 ? $settings['piotnetforms_booking_id'] : $form_post_id;
		$settings['piotnetforms_booking_id'] = $form_id;

		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-fields-wrapper piotnetforms-labels-above' );

		if ( ! empty( $settings['piotnetforms_booking_field_required'] ) ) {
			$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-field-required piotnetforms-field-type-checkbox' );
			if ( ! empty( $settings['piotnetforms_booking_mark_required'] ) ) {
				$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-mark-required' );
			}
		} ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="piotnetforms-field-group piotnetforms-booking piotnetforms-booking--loading" data-piotnetforms-booking>
				<?php
					require_once( __DIR__ . '/../forms/templates/template-form-booking.php' );

		piotnetforms_template_form_booking( $settings, $this->get_id(), $this->post_id ); ?>
			</div>
		</div>
		<?php
	}
	public function live_preview() {
		?>
		<%
			var s = data.widget_settings;
			view.add_attribute('wrapper', 'class', 'piotnetforms-fields-wrapper piotnetforms-labels-above');
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<% if (s['piotnetforms_booking_field_label_show']) { %>
				<label class="piotnetforms-field-label"><%= s['piotnetforms_booking_field_label'] %></label>
			<% } %>
			<form class="piotnetforms-booking__inner">
				<% if ( s['piotnetforms_booking'] ) { for ( var i = 0; i < s['piotnetforms_booking'].length; i++ ) { var item = s['piotnetforms_booking'][i]; %>
					<div class="piotnetforms-booking__item">
						<div class="piotnetforms-booking__item-inner">
							<input type="checkbox" value="<%= item['piotnetforms_booking_title'] %>" data-value="<%= item['piotnetforms_booking_title'] %>" id="form-field-<%= item['piotnetforms_booking_id'] %>-<%= i %>" name="form_fields[<%= item['piotnetforms_booking_id'] %>][]" data-piotnetforms-builder-default-value="<%= item['piotnetforms_booking_title'] %>" data-piotnetforms-builder-form-booking-price="<%= item['piotnetforms_booking_price'] %>" data-piotnetforms-builder-form-id="<%= s['piotnetforms_booking_form_id'] %>" data-piotnetforms-booking-item data-piotnetforms-booking-item-options='<%=  JSON.stringify( item ) %>'<% if( s['piotnetforms_booking_field_allow_multiple']) { %> data-piotnetforms-booking-item-radio<% } %>>
							<% if ( item['piotnetforms_booking_title'] ) { %>
								<div class="piotnetforms-booking__title"><%= item['piotnetforms_booking_title'] %></div>
							<% } %>
							<% if ( s['piotnetforms_booking_field_slot_show'] ) { %>
								<div class="piotnetforms-booking__slot" data-piotnetforms-booking-slot>
									<span class="piotnetforms-booking__slot-before"><%= s['piotnetforms_booking_before_number_of_slot'] %></span>
									<span class="piotnetforms-booking__slot-number"><%= item['piotnetforms_booking_slot'] %></span>
									<span class="piotnetforms-booking__slot-after"><%= s['piotnetforms_booking_after_number_of_slot'] %></span>
								</div>
							<% } %>
							<% if ( s['piotnetforms_booking_field_price_show'] && item['piotnetforms_booking_price_text'] ) { %>
								<div class="piotnetforms-booking__price">
									<%= item['piotnetforms_booking_price_text'] %>
								</div>
							<% } %>
						</div>
					</div>
				<% } } %>	
			</form>
		</div>
		<?php
	}
}
