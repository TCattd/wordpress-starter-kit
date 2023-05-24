<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../class/piotnet-base.php';

abstract class Base_Widget_Piotnetforms extends piotnetforms_Base {
	abstract public function get_type();
	abstract public function get_class_name();
	abstract public function get_title();
	abstract public function get_icon();
	abstract public function get_categories();
	abstract public function get_keywords();
	abstract public function render();
	abstract public function live_preview();
	abstract public function register_controls();

	protected $is_add_conditional_logic = true;

	protected $structure       = [];
	private $current_tab       = null;
	private $current_section   = null;
	private $current_controls  = null;
	private $previous_controls = null;

	private $tab_names     = [];
	private $section_names = [];
	private $control_names = [];

	protected function start_tab( string $name, string $label ) {
		if ( in_array( $name, $this->tab_names ) ) {
			echo 'Warning: Tab name "' . esc_html( $name ) . '" has been declared.<br>';
			return;
		}
		array_push( $this->tab_names, $name );

		$tab = [
			'name'     => $name,
			'label'    => $label,
			'sections' => [],
		];

		if ( count( $this->structure ) === 0 ) {
			$tab['active'] = true;
		}

		$this->current_tab = &$tab;
		$this->structure[] = &$tab;
	}

	public function acf_get_field_key( $field_name, $post_id ) {
		global $wpdb;
		$acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_parent,post_name FROM $wpdb->posts WHERE post_excerpt=%s AND post_type=%s", $field_name, 'acf-field' ) );
		// get all fields with that name.
		switch ( count( $acf_fields ) ) {
			case 0: // no such field
				return false;
			case 1: // just one result.
				return $acf_fields[0]->post_name;
		}
		// result is ambiguous
		// get IDs of all field groups for this post
		$field_groups_ids = [];
		$field_groups = acf_get_field_groups( [
			'post_id' => $post_id,
		] );
		foreach ( $field_groups as $field_group ) {
			$field_groups_ids[] = $field_group['ID'];
		}

		// Check if field is part of one of the field groups
		// Return the first one.
		foreach ( $acf_fields as $acf_field ) {
			$acf_field_id = acf_get_field( $acf_field->post_parent );
			if ( in_array( $acf_field_id['parent'], $field_groups_ids ) ) {
				return $acf_field->post_name;
			}
		}
		return false;
	}

	public function jetengine_repeater_get_field_object( $field_name, $meta_field_id ) {
		$meta_objects = get_option( 'jet_engine_meta_boxes' );
		foreach ( $meta_objects as $meta_object ) {
			$meta_fields = $meta_object['meta_fields'];
			foreach ( $meta_fields as $meta_field ) {
				if ( ( $meta_field['name'] == $meta_field_id ) && ( $meta_field['type'] == 'repeater' ) ) {
					$meta_repeater_fields = $meta_field['repeater-fields'];
					foreach ( $meta_repeater_fields as $meta_repeater_field ) {
						if ( $meta_repeater_field['name'] == $field_name ) {
							return $meta_repeater_field;
						}
					}
				}
			}
		}
		return false;
	}

	public function metabox_group_get_field_object( $field_name, $meta_objects ) {
		foreach ( $meta_objects as $meta_object ) {
			$meta_fields = $meta_object['fields'];
			foreach ( $meta_fields as $meta_field ) {
				if ( ( $meta_field['type'] == 'group' ) && ( $meta_field['clone'] ) ) {
					$meta_repeater_fields = $meta_field['fields'];
					foreach ( $meta_repeater_fields as $meta_repeater_field ) {
						if ( $meta_repeater_field['id'] == $field_name ) {
							return $meta_repeater_field;
						}
					}
				}
			}
		}
		return false;
	}

	protected function start_section( string $name, string $label, $args = [] ) {
		if ( in_array( $name, $this->section_names ) ) {
			echo 'Warning: Section name "' . esc_html( $name ) . '" has been declared.<br>';
			return;
		}

		$controls = [];
		$section  = [
			'name'     => $name,
			'label'    => $label,
			'controls' => &$controls,
		];

		$args = $this->conditions_old_version( $args );

		if ( isset( $args['conditions'] ) ) {
			$section['conditions'] = $args['conditions'];
		}

		if ( count( $this->current_tab['sections'] ) === 0 ) {
			$section['active'] = true;
		}

		$this->current_section           = &$section;
		$this->current_controls          = &$controls;
		$this->current_tab['sections'][] = &$section;
	}

	protected function add_control( string $name, $args, $options = [] ) {
		$default_options = [
			'overwrite' => false,
			'index'     => null,
		];

		$options = array_merge( $default_options, $options );

		if ( ! $options['overwrite'] && ! empty( $name ) && $name !== '' && in_array( $name, $this->control_names ) ) {
			echo 'Warning: Control name "' . esc_html( $name ) . '" has been declared.<br>';
			return;
		}
		if ( ! $options['overwrite'] && ! empty( $name ) && $name !== '' ) {
			array_push( $this->control_names, $name );

			if ( stripos( $name, '__dynamic__' ) !== false ) {
				$this->control_dynamic_css[$name] = $args['selectors'];
			}
		}

		$args['name'] = $name;

		// Fix Old Version

		$args = $this->conditions_old_version( $args );

		if ( $args['type'] == 'slider' ) {
			$args['label_block'] = true;

			if ( ! empty( $args['range'] ) ) {
				if ( ! empty( $args['size_units'] ) ) {
					unset( $args['size_units'] );
				}
				$args['size_units'] = $args['range'];
			}
		}

		if ( ! empty( $args['default'] ) ) {
			$args['value'] = $args['default'];
		}

		if ( $args['type'] == 'dimensions' ) {
			$args['label_block'] = true;
			if ( empty( $args['size_units'] ) ) {
				$args['size_units'] = [ 'px' ];
			}
			if ( empty( $args['value'] ) ) {
				$args['value'] = [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				];
			}
		}

		if ( $args['type'] == 'gallery' ) {
			$args['label_block'] = true;
		}

		// End Fix Old Version

		$this->current_controls[] = $args;
	}

	public function get_info() {
		return [
			'type'       => $this->get_type(),
			'class_name' => $this->get_class_name(),
			'title'      => $this->get_title(),
			'icon'       => $this->get_icon(),
			'structure'  => $this->register_controls(),
		];
	}

	protected function add_responsive_control( string $name, array $args ) {
		$responsive_device = [ 'desktop', 'tablet', 'mobile' ];
		foreach ( $responsive_device as $device ) {
			$responsive_args               = $args;
			$control_name                  = $name . '_responsive_' . $device;
			$responsive_args['responsive'] = $device;
			$this->add_control( $control_name, $responsive_args );
		}
	}

	protected function new_group_controls() {
		$this->previous_controls = $this->current_controls;
		$this->current_controls  = [];
		return $this->previous_controls;
	}

	protected function get_group_controls( $previous_controls = null ) {
		$controls               = $this->current_controls;
		$this->current_controls = $previous_controls === null ? $this->previous_controls : $previous_controls;
		return $controls;
	}



	protected function add_text_typography_controls( string $name, $args = [] ) {
		$this->new_group_controls();

		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';

		$this->add_control(
			$name . '_font_family',
			[
				'type'           => 'select',
				'label'          => __( 'Family', 'piotnetforms' ),
				'options_source' => 'google-fonts-new',
				'selectors'      => [
					$wrapper => 'font-family:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			$name . '_font_custom',
			[
				'type'           => 'text',
				'label'          => __( 'Custom Font', 'piotnetgrid' ),
				'label_block'    => true,
				'selectors'      => [
					$wrapper => 'font-family:{{VALUE}}',
				],
				'conditions'     => [
					[
						'name'     => $name . '_font_family',
						'operator' => '==',
						'value'    => 'custom',
					],
				],
			]
		);


		$this->add_responsive_control(
			$name . '_font_size',
			[
				'type'        => 'slider',
				'label'       => __( 'Size', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
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
			$name . '_font_weight',
			[
				'type'      => 'select',
				'label'     => __( 'Weight', 'piotnetforms' ),
				'value'     => '',
				'options'   => [
					''    => 'Default',
					'100' => '100',
					'200' => '200',
					'300' => '300',
					'400' => '400 Normal',
					'500' => '500',
					'600' => '600',
					'700' => '700 Bold',
					'800' => '800',
					'900' => '900',
				],
				'selectors' => [
					$wrapper => 'font-weight:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			$name . '_transform',
			[
				'type'      => 'select',
				'label'     => __( 'Transform', 'piotnetforms' ),
				'options'   => [
					''           => 'Default',
					'uppercase'  => 'Uppercase',
					'lowercase'  => 'Lowercase',
					'capitalize' => 'Capitalize',
					'none'       => 'Normal',
				],
				'selectors' => [
					$wrapper => 'text-transform:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			$name . '_font_style',
			[
				'type'      => 'select',
				'label'     => __( 'Style', 'piotnetforms' ),
				'options'   => [
					''        => 'Default',
					'normal'  => 'Normal',
					'italic'  => 'Italic',
					'oblique' => 'Oblique',
				],
				'selectors' => [
					$wrapper => 'font-style:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			$name . '_decoration',
			[
				'type'      => 'select',
				'label'     => __( 'Decoration', 'piotnetforms' ),
				'options'   => [
					''             => 'Default',
					'underline'    => 'Underline',
					'overline'     => 'Overline',
					'line-through' => 'Line Through',
					'none'         => 'None',
				],
				'selectors' => [
					$wrapper => 'text-decoration:{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			$name . '_line_height',
			[
				'type'        => 'slider',
				'label'       => __( 'Line-Height', 'piotnetforms' ),
				'value'       => [
					'unit' => 'em',
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
					$wrapper => 'line-height:{{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			$name . '_letter_spacing',
			[
				'type'        => 'slider',
				'label'       => __( 'Letter Spacing', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => -5,
						'max'  => 10,
						'step' => 1,
					],
				],
				'selectors'   => [
					$wrapper => 'letter-spacing:{{SIZE}}{{UNIT}}',
				],
			]
		);

		$typography_controls = $this->get_group_controls();

		$typography_args = [
			'type'           => 'typography',
			'label'          => isset( $args['label'] ) ? $args['label'] : __( 'Typography', 'piotnetforms' ),
			'label_block'    => false,
			'controls'       => $typography_controls,
			'controls_query' => '.piotnet-tooltip__body',
			'icon'           => 'fas fa-pencil-alt',
		];

		$args = $this->conditions_old_version( $args );

		if ( isset( $args['conditions'] ) ) {
			$typography_args['conditions'] = $args['conditions'];
		}

		$this->add_control(
			$name,
			$typography_args
		);
	}

	protected function add_advanced_tab() {
		$this->start_tab( 'advanced', 'Advanced' );

		$this->start_section( 'margin_section', 'Margin' );
		$this->add_margin_controls();

		$this->start_section( 'padding_section', 'Padding' );
		$this->add_padding_controls();

		$this->start_section( 'width_section', 'Size' );
		$this->add_size_controls();

		$this->start_section( 'box_shadow_section', 'Box Shadow' );
		$this->add_box_shadow_controls();

		$this->start_section( 'custom_class_id_section', 'Custom Class/ID' );
		$this->add_custom_class_id_controls();

		$this->start_section( 'border_section', 'Border' );
		$this->add_border_controls();

		$this->start_section( 'background_section', 'Background' );
		$this->add_background_controls();

		$this->start_section( 'responsive_section', 'Responsive' );
		$this->add_responsive_controls();

		$this->start_section( 'position_section', 'Position' );
		$this->add_position_controls();

		$this->add_attributes_settings();

		$this->start_section( 'custom_css_section', 'Custom CSS' );
		$this->add_custom_css_controls();

		$this->add_repeater_trigger_controls();

		$this->add_next_prev_multi_step_form_controls();

		$this->add_multi_step_form_animation_controls();

		if ( $this->is_add_conditional_logic ) {
			$this->start_section( 'section_conditional_logic_new', 'Conditional Logic' );
			$this->add_conditional_logic_controls();
		}

		$this->start_section( 'conditional_visibility', 'Conditional Visibility' );
		$this->add_conditional_visibility_controls();
	}

	protected function add_margin_controls() {
		$this->add_responsive_control(
			'advanced_margin',
			[
				'type'        => 'dimensions',
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
					'{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}

	protected function add_padding_controls() {
		$this->add_responsive_control(
			'advanced_padding',
			[
				'type'        => 'dimensions',
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
					'{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}

	protected function add_size_controls() {
		$this->add_responsive_control(
			'advanced_width',
			[
				'type'        => 'number',
				'label'       => __( 'Width (%)', 'piotnetforms' ),
				'label_block' => true,
				'selectors'   => [
					'{{WRAPPER}}' => 'width: {{VALUE}}%;',
					'{{WRAPPER_EDITOR}}' => 'width: {{VALUE}}%;',
				],
			]
		);

		$this->add_responsive_control(
			'advanced_width_custom',
			[
				'type'        => 'text',
				'label'       => __( 'Custom Width', 'piotnetforms' ),
				'label_block' => true,
				'description' => __( 'Examples: 50%, 150px, calc(50% - 30px), auto', 'piotnetforms' ),
				'selectors'   => [
					'{{WRAPPER}}' => 'width: {{VALUE}};',
					'{{WRAPPER_EDITOR}}' => 'width: {{VALUE}};',
				],
			]
		);
	}

	protected function add_box_shadow_controls() {
		$this->add_control(
			'advanced_box_shadow',
			[
				'type'        => 'box-shadow',
				'label'       => __( 'Box Shadow', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'render_type' => 'none',
				'selectors'   => [
					'{{WRAPPER}}' => 'box-shadow: {{VALUE}};',
				],
			]
		);
	}

	protected function add_custom_class_id_controls() {
		$this->add_control(
			'advanced_custom_id',
			[
				'type'        => 'text',
				'label'       => __( 'Custom ID', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
			]
		);

		$this->add_control(
			'advanced_custom_classes',
			[
				'type'        => 'text',
				'label'       => __( 'Custom Classes', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
			]
		);
	}

	private function add_border_controls() {
		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'advanced_border_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'advanced_border_hover_tab',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_tab_border_controls(
			'advanced_border_normal',
			[
				'selectors' => '{{WRAPPER}}',
			]
		);
		$this->add_control(
			'advanced_border_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->add_tab_border_controls(
			'advanced_border_hover',
			[
				'selectors' => '{{WRAPPER}}:hover',
			]
		);
		$this->add_control(
			'advanced_border_hover_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_tab_border_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';

		$this->new_group_controls();
		$this->add_control(
			$name . '_style',
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

		$this->add_responsive_control(
			$name . '_color',
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
						'name'     => $name . '_style',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name . '_width',
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
						'name'     => $name . '_style',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name . '_radius',
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
					$wrapper => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		return $this->get_group_controls();
	}

	protected function add_background_controls() {
		$this->add_control(
			'advanced_background_color',
			[
				'type'        => 'color',
				'label'       => __( 'Color', 'piotnetforms' ),
				'value'       => '',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}}' => 'background-color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'advanced_background_image',
			[
				'type'      => 'media',
				'label'     => __( 'Choose Image', 'piotnetforms' ),
				'value'     => '',
				'selectors' => [
					'{{WRAPPER}}' => 'background-image: url({{VALUE}})',
				],
			]
		);
		$this->add_responsive_control(
			'advanced_background_position',
			[
				'type'        => 'select',
				'label'       => __( 'Position', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'options'     => [
					''              => 'Default',
					'center center' => 'Center Center',
					'center left'   => 'Center Left',
					'center right'  => 'Center Right',
					'top center'    => 'Top Center',
					'top left'      => 'Top Left',
					'top right'     => 'Top Right',
					'bottom center' => 'Bottom Center',
					'bottom left'   => 'Bottom Left',
					'bottom right'  => 'Bottom Right',
					'initial'       => 'Custom',
				],
				'selectors'   => [
					'{{WRAPPER}}' => 'background-position: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'advanced_background_attachment',
			[
				'type'        => 'select',
				'label'       => __( 'Attachment', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'options'     => [
					''       => 'Default',
					'scroll' => 'scroll',
					'fixed'  => 'Fixed',
				],
				'selectors'   => [
					'{{WRAPPER}}' => 'background-attachment: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'advanced_background_repeat',
			[
				'type'        => 'select',
				'label'       => __( 'Repeat', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'options'     => [
					''          => 'Default',
					'no-repeat' => 'No-repeat',
					'repeat'    => 'Repeat',
					'repeat-x'  => 'Repeat-x',
					'repeat-y'  => 'Repeat-y',
				],
				'selectors'   => [
					'{{WRAPPER}}' => 'background-repeat: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'advanced_background_size',
			[
				'type'        => 'select',
				'label'       => __( 'Size', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'options'     => [
					''        => 'Default',
					'auto'    => 'Auto',
					'cover'   => 'Cover',
					'contain' => 'Contain',
					'initial' => 'Custom',
				],
				'selectors'   => [
					'{{WRAPPER}}' => 'background-size: {{VALUE}}',
				],
			]
		);
		$this->add_responsive_control(
			'advanced_background_size_custom',
			[
				'type'        => 'text',
				'label'       => __( 'Custom Background Size', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					'{{WRAPPER}}' => 'background-size: {{VALUE}}',
				],
				'conditions'  => [
					[
						'name'     => 'advanced_background_size_responsive_desktop',
						'operator' => '=',
						'value'    => 'initial',
					],
				],
			]
		);
	}

	protected function add_responsive_controls() {
		
		$this->add_control(
			'advanced_hide_desktop',
			[
				'label' => __( 'Hide On Desktop', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => __( 'Hide', 'piotnetforms' ),
				'label_off' => __( 'Show', 'piotnetforms' ),
				'return_value' => 'piotnetforms-hidden-desktop',
			]
		);

		$this->add_control(
			'advanced_hide_tablet',
			[
				'label' => __( 'Hide On Tablet', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => __( 'Hide', 'piotnetforms' ),
				'label_off' => __( 'Show', 'piotnetforms' ),
				'return_value' => 'piotnetforms-hidden-tablet',
			]
		);

		$this->add_control(
			'advanced_hide_mobile',
			[
				'label' => __( 'Hide On Mobile', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => __( 'Hide', 'piotnetforms' ),
				'label_off' => __( 'Show', 'piotnetforms' ),
				'return_value' => 'piotnetforms-hidden-phone',
			]
		);
	}

	protected function add_custom_css_controls() {
		$this->add_control(
			'advanced_custom_css',
			[
				'type'        => 'textarea',
				'label'       => __( 'Add your own custom CSS here', 'piotnetforms' ),
				'description' => 'Use "{{WRAPPER}}" to target wrapper element. Examples:<br>
					{{WRAPPER}} {color: red;} // For main element<br>
					{{WRAPPER}} .child-element {margin: 10px;} // For child element<br>
					.my-class {text-align: center;} // Or use any custom selector',
				'label_block' => true,
			]
		);
	}

	protected function add_position_controls() {
		$this->add_control(
			'advanced_position',
			[
				'type'      => 'select',
				'label'     => __( 'Position', 'piotnetforms' ),
				'value'     => '',
				'options'   => [
					''       => 'Default',
					'absolute'  => 'Absolute',
				],
				'selectors' => [
					'{{WRAPPER_FRONTEND}}' => 'position:{{VALUE}};',
					'{{WRAPPER_EDITOR}}' => 'position:{{VALUE}};',
				],
			]
		);

		$this->add_control(
			'advanced_z_index',
			[
				'type'      => 'number',
				'label'     => __( 'Z-index', 'piotnetforms' ),
				'value'     => '',
				'selectors' => [
					'{{WRAPPER_FRONTEND}}' => 'z-index:{{VALUE}};',
					'{{WRAPPER_EDITOR}}' => 'z-index:{{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'advanced_position_absolute_top',
			[
				'type'      => 'slider',
				'label'     => __( 'Top', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min'  => -200,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER_FRONTEND}}' => 'top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER_EDITOR}}' => 'top: {{SIZE}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'advanced_position',
						'operator' => '=',
						'value'    => 'absolute',
					],
				],
			]
		);

		$this->add_responsive_control(
			'advanced_position_absolute_right',
			[
				'type'      => 'slider',
				'label'     => __( 'Right', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min'  => -200,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER_FRONTEND}}' => 'right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER_EDITOR}}' => 'right: {{SIZE}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'advanced_position',
						'operator' => '=',
						'value'    => 'absolute',
					],
				],
			]
		);

		$this->add_responsive_control(
			'advanced_position_absolute_bottom',
			[
				'type'      => 'slider',
				'label'     => __( 'Bottom', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min'  => -200,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER_FRONTEND}}' => 'bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER_EDITOR}}' => 'bottom: {{SIZE}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'advanced_position',
						'operator' => '=',
						'value'    => 'absolute',
					],
				],
			]
		);

		$this->add_responsive_control(
			'advanced_position_absolute_left',
			[
				'type'      => 'slider',
				'label'     => __( 'Left', 'piotnetforms' ),
				'label_block' => true,
				'value'       => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min'  => -200,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER_FRONTEND}}' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER_EDITOR}}' => 'left: {{SIZE}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'advanced_position',
						'operator' => '=',
						'value'    => 'absolute',
					],
				],
			]
		);
	}

	protected function add_attributes_settings() {
		$this->start_section( 'attributes_section', 'Attributes' );

		$this->new_group_controls();
		$this->add_control(
			'attribute_name',
			[
				'type'        => 'text',
				'get_fields'  => true,
				'label'       => __( 'Name', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
			]
		);
		$this->add_control(
			'attribute_value',
			[
				'type'        => 'text',
				'get_fields'  => true,
				'label'       => __( 'Value', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
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
			'attributes',
			[
				'type'           => 'repeater',
				'label'          => __( 'Attributes List', 'piotnetforms' ),
				'value'          => 'or',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}

	protected function add_repeater_trigger_controls() {
		$this->start_section( 'repeater_trigger_section', 'Repeater Trigger' );
		$this->add_control(
			'piotnetforms_repeater_enable_trigger',
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
			'piotnetforms_repeater_form_id_trigger',
			[
				'type'        => 'hidden',
				'label'       => __( 'Form ID* (Required)', 'piotnetforms' ),
				'value'       => '',
				'description' => __( 'Enter the same form id for all fields in a form, with latin character and no space. E.g order_form', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'piotnetforms_repeater_enable_trigger',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
		$this->add_control(
			'piotnetforms_repeater_id_trigger',
			[
				'type'        => 'text',
				'label'       => __( 'Repeater ID* (Required)', 'piotnetforms' ),
				'value'       => '',
				'description' => __( 'Enter Repeater ID with latin character and no space. E.g products_repeater', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'piotnetforms_repeater_enable_trigger',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
		$this->add_control(
			'piotnetforms_repeater_trigger_action',
			[
				'type'        => 'select',
				'label'       => __( 'Action', 'piotnetforms' ),
				'value'       => 'add',
				'options'     => [
					'add'    => __( 'Add', 'piotnetforms' ),
					'remove' => __( 'Remove', 'piotnetforms' ),
				],
				'description' => __( 'Enter Repeater ID with latin character and no space. E.g products_repeater', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'piotnetforms_repeater_enable_trigger',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
	}

	protected function add_next_prev_multi_step_form_controls() {
		$this->start_section( 'next_prev_multi_step_form_section', 'Next/Previous Multi Step Form' );

		$this->add_control(
			'next_prev_multi_step_form',
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
			'next_prev_multi_step_form_action',
			[
				'type'        => 'select',
				'label'       => __( 'Action', 'piotnetforms' ),
				'options'     => [
					'next'    => __( 'Next', 'piotnetforms' ),
					'prev'    => __( 'Previous', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'next_prev_multi_step_form',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
	}

	protected function add_multi_step_form_animation_controls() {
		$this->start_section( 'multi_step_form_animation_section', 'Multi Step Form Animation' );

		$this->add_control(
			'multi_step_form_animation',
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
			'multi_step_form_animation_animate',
			[
				'type'        => 'select',
				'label'       => __( 'Animation', 'piotnetforms' ),
				'options'     => [
					'fadeIn' => __( 'Fade In', 'piotnetforms' ),
					'fadeInUp' => __( 'Fade In Up', 'piotnetforms' ),
					'fadeInDown' => __( 'Fade In Down', 'piotnetforms' ),
					'fadeInLeft' => __( 'Fade In Left', 'piotnetforms' ),
					'fadeInRight' => __( 'Fade In Right', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'multi_step_form_animation',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$this->add_control(
			'multi_step_form_animation_delay',
			[
				'type'        => 'number',
				'label'       => __( 'Delay (ms)', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'multi_step_form_animation',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
	}

	private function add_conditional_logic_controls() {
		$this->add_control(
			'piotnetforms_conditional_logic_form_enable_new',
			[
				'type'         => 'switch',
				'label'        => __( 'Enable', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'description'  => __( 'This feature only works on the frontend.', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_form_id',
			[
				'type'       => 'hidden',
				'label'      => __( 'Form ID', 'piotnetforms' ),
				'value'      => '',
				'conditions' => [
					[
						'name'     => 'piotnetforms_conditional_logic_form_enable_new',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_speed_new',
			[
				'type'        => 'text',
				'label'       => __( 'Speed', 'piotnetforms' ),
				'value'       => 400,
				'conditions'  => [
					[
						'name'     => 'piotnetforms_conditional_logic_form_enable_new',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
				'description' => __( 'E.g 100, 1000, slow, fast', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_easing_new',
			[
				'type'        => 'text',
				'label'       => __( 'Easing', 'piotnetforms' ),
				'value'       => 'swing',
				'conditions'  => [
					[
						'name'     => 'piotnetforms_conditional_logic_form_enable_new',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
				'description' => __( 'E.g swing, linear', 'piotnetforms' ),
			]
		);

		$this->add_conditional_logic_repeater_controls();
	}

	private function add_conditional_logic_repeater_controls() {
		$this->new_group_controls();
		$this->add_control(
			'piotnetforms_conditional_logic_form_if',
			[
				'type'        => 'select',
				'get_fields'  => true,
				'label'       => __( 'If', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'placeholder' => __( 'Field ID', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_comparison_operators',
			[
				'type'        => 'select',
				'label'       => __( 'Comparison Operators', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'options'     => [
					'not-empty' => __( 'not empty', 'piotnetforms' ),
					'empty'     => __( 'empty', 'piotnetforms' ),
					'='         => __( 'equals', 'piotnetforms' ),
					'!='        => __( 'not equals', 'piotnetforms' ),
					'>'         => __( '>', 'piotnetforms' ),
					'>='        => __( '>=', 'piotnetforms' ),
					'<'         => __( '<', 'piotnetforms' ),
					'<='        => __( '<=', 'piotnetforms' ),
					'checked'   => __( 'checked', 'piotnetforms' ),
					'unchecked' => __( 'unchecked', 'piotnetforms' ),
					'contains'  => __( 'contains', 'piotnetforms' ),
				],
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_type',
			[
				'type'        => 'select',
				'label'       => __( 'Type Value', 'piotnetforms' ),
				'value'       => 'string',
				'label_block' => true,
				'options'     => [
					'string' => __( 'String', 'piotnetforms' ),
					'number' => __( 'Number', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'piotnetforms_conditional_logic_form_comparison_operators',
						'operator' => 'in',
						'value'    => [ '=', '!=', '>', '>=', '<', '<=' ],
					],
				],
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_value',
			[
				'type'        => 'text',
				'label'       => __( 'Value', 'piotnetforms' ),
				'value'       => '50',
				'label_block' => true,
				'conditions'  => [
					[
						'name'     => 'piotnetforms_conditional_logic_form_comparison_operators',
						'operator' => 'in',
						'value'    => [ '=', '!=', '>', '>=', '<', '<=', 'contains' ],
					],
				],
			]
		);
		$this->add_control(
			'piotnetforms_conditional_logic_form_and_or_operators',
			[
				'type'        => 'select',
				'label'       => __( 'OR, AND Operators', 'piotnetforms' ),
				'value'       => 'or',
				'label_block' => true,
				'options' => [
					'or'  => __( 'OR', 'piotnetforms' ),
					'and' => __( 'AND', 'piotnetforms' ),
				],
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
			'piotnetforms_conditional_logic_form_list_new',
			[
				'type'           => 'repeater',
				'label'          => __( 'Conditional List', 'piotnetforms' ),
				'value'          => 'or',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'conditions'     => [
					[
						'name'     => 'piotnetforms_conditional_logic_form_enable_new',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
	}

	private function add_conditional_visibility_controls() {
		$this->add_control(
			'conditional_visibility_enable',
			[
				'type'         => 'switch',
				'label'        => __( 'Enable', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$roles_array = [
			'all'           => 'All',
			'non_logged_in' => 'Non Logged',
			'logged_in'     => 'Logged In',
		];
		global $wp_roles;
		$roles = $wp_roles->roles;
		foreach ( $roles as $key => $value ) {
			$roles_array[ $key ] = $value['name'];
		}

		$this->add_control(
			'conditional_visibility_by_roles',
			[
				'type'         => 'switch',
				'label'        => __( 'Visibility For User', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$this->add_control(
			'conditional_visibility_roles',
			[
				'type'         => 'select2',
				'label'        => __( 'Set Roles', 'piotnetforms' ),
				'value'        => [
					'all',
				],
				'multiple'     => true,
				'options'      => $roles_array,
				'label_block'  => true,
				'return_value' => 'yes',
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'conditional_visibility_by_roles',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$days_of_week = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday' ];

		$this->add_control(
			'conditional_visibility_by_date_and_time',
			[
				'type'         => 'switch',
				'label'        => __( 'Visibility By Date And Time', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$this->add_control(
			'conditional_visibility_date_and_time_operators',
			[
				'type'        => 'select',
				'label'       => __( 'OR, AND Operators', 'piotnetforms' ),
				'value'       => 'or',
				'label_block' => true,
				'options'     => [
					'or'  => __( 'OR', 'piotnetforms' ),
					'and' => __( 'AND', 'piotnetforms' ),
				],
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'conditional_visibility_by_date_and_time',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$this->add_control(
			'conditional_visibility_action_for_date_and_time',
			[
				'type'         => 'select',
				'label'        => __( 'Action', 'piotnetforms' ),
				'value'        => 'show',
				'label_block'  => true,
				'options'      => [
					'show' => __( 'Show', 'piotnetforms' ),
					'hide' => __( 'Hide', 'piotnetforms' ),
				],
				'multiple'     => true,
				'return_value' => 'yes',
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'conditional_visibility_by_date_and_time',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
		$this->add_conditional_visibility_time_repeater_controls( $days_of_week );

		$this->add_control(
			'conditional_visibility_by_backend',
			[
				'type'         => 'switch',
				'label'        => __( 'Conditional Visibility By Custom Fields and URL Parameters, URL Contains', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$this->add_control(
			'conditional_visibility_action',
			[
				'type'         => 'select',
				'label'        => __( 'Action', 'piotnetforms' ),
				'value'        => 'show',
				'label_block'  => true,
				'options'      => [
					'show' => __( 'Show', 'piotnetforms' ),
					'hide' => __( 'Hide', 'piotnetforms' ),
				],
				'multiple'     => true,
				'return_value' => 'yes',
				'conditions'   => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'conditional_visibility_by_backend',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);

		$this->add_conditional_visibility_repeater_controls();
	}

	private function add_conditional_visibility_time_repeater_controls( $days_of_week ) {
		$this->new_group_controls();
		$this->add_control(
			'conditional_visibility_set_days_of_week',
			[
				'label'       => __( 'Choose Day', 'piotnetforms' ),
				'type'        => 'select2',
				'multiple'    => true,
				'options'     => $days_of_week,
				'label_block' => true,

			]
		);

		$this->add_control(
			'conditional_visibility_start_date',
			[
				'label' => __( 'Start Date', 'piotnetforms' ),
				'type' => 'date',
				'label_block' => false,
				'picker_options' => [
					'enableTime' => false,
				],
			]
		);

		$this->add_control(
			'conditional_visibility_end_date',
			[
				'label' => __( 'End Date', 'piotnetforms' ),
				'type' => 'date',
				'label_block' => false,
				'picker_options' => [
					'enableTime' => false,
				],
			]
		);

		$this->add_control(
			'conditional_visibility_time_start',
			[
				'type'        => 'text',
				'label'       => __( 'Time Start', 'piotnetforms' ),
				'default' => __( '', 'piotnetforms' ),
				'placeholder' => __( 'HH:mm', 'piotnetforms' ),
				'description' => __( 'It was setted in HH:mm format', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'conditional_visibility_time_end',
			[
				'type'        => 'text',
				'label'       => __( 'Time End', 'piotnetforms' ),
				'default' => __( '', 'piotnetforms' ),
				'placeholder' => __( 'HH:mm', 'piotnetforms' ),
				'description' => __( 'It was setted in HH:mm format', 'piotnetforms' ),
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
			'conditional_visibility_time_repeater',
			[
				'type'           => 'repeater',
				'label'          => __( 'Conditional List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'conditions'     => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'conditional_visibility_by_date_and_time',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
	}


	private function add_conditional_visibility_repeater_controls() {
		$this->new_group_controls();
		$this->add_control(
			'conditional_visibility_by_backend_select',
			[
				'type'        => 'select',
				'label'       => __( 'Custom Fields or URL Parameters, URL Contains', 'piotnetforms' ),
				'value'       => 'custom_field',
				'label_block' => true,
				'options'     => [
					'custom_field'  => __( 'Custom Field', 'piotnetforms' ),
					'url_parameter' => __( 'URL Parameter', 'piotnetforms' ),
					'url_contains'  => __( 'URL Contains', 'piotnetforms' ),
				],
			]
		);

		$this->add_control(
			'conditional_visibility_custom_field_source',
			[
				'type'        => 'select',
				'label'       => __( 'Custom Fields', 'piotnetforms' ),
				'value'       => 'post_custom_field',
				'label_block' => true,
				'options'     => [
					'post_custom_field' => __( 'Post Custom Field', 'piotnetforms' ),
					'acf_field'         => __( 'ACF Field', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_by_backend_select',
						'operator' => '==',
						'value'    => 'custom_field',
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_url_parameter',
			[
				'type'        => 'text',
				'label'       => __( 'URL Parameter', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'description' => __( 'E.g ref, yourparam', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_by_backend_select',
						'operator' => '==',
						'value'    => 'url_parameter',
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_custom_field_key',
			[
				'type'        => 'text',
				'label'       => __( 'Custom Field Key', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_by_backend_select',
						'operator' => '==',
						'value'    => 'custom_field',
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_custom_field_comparison_operators',
			[
				'type'        => 'select',
				'label'       => __( 'Comparison Operators', 'piotnetforms' ),
				'value'       => 'not-empty',
				'label_block' => true,
				'options'     => [
					'not-empty' => __( 'not empty', 'piotnetforms' ),
					'empty'     => __( 'empty', 'piotnetforms' ),
					'='         => __( 'equals', 'piotnetforms' ),
					'!='        => __( 'not equals', 'piotnetforms' ),
					'>'         => __( '>', 'piotnetforms' ),
					'>='        => __( '>=', 'piotnetforms' ),
					'<'         => __( '<', 'piotnetforms' ),
					'<='        => __( '<=', 'piotnetforms' ),
					'true'      => __( 'true', 'piotnetforms' ),
					'false'     => __( 'false', 'piotnetforms' ),
					'contains'  => __( 'contains (ACF Checkbox)', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_by_backend_select',
						'operator' => '!=',
						'value'    => 'url_contains',
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_custom_field_type',
			[
				'type'        => 'select',
				'label'       => __( 'Type Value', 'piotnetforms' ),
				'value'       => 'string',
				'label_block' => true,
				'options'     => [
					'string' => __( 'String', 'piotnetforms' ),
					'number' => __( 'Number', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_custom_field_comparison_operators',
						'operator' => 'in',
						'value'    => [ '=', '!=', '>', '>=', '<', '<=', 'contains' ],
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_custom_field_value',
			[
				'type'        => 'text',
				'label'       => __( 'Value', 'piotnetforms' ),
				'value'       => '50',
				'label_block' => true,
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_by_backend_select',
						'operator' => '!=',
						'value'    => 'url_contains',
					],
					[
						'name'     => 'conditional_visibility_custom_field_comparison_operators',
						'operator' => 'in',
						'value'    => [ '=', '!=', '>', '>=', '<', '<=', 'contains' ],
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_custom_field_value_url_contains',
			[
				'type'        => 'text',
				'label'       => __( 'Value', 'piotnetforms' ),
				'value'       => '50',
				'label_block' => true,
				'placeholder' => __( '/page-slug/', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_by_backend_select',
						'operator' => '==',
						'value'    => 'url_contains',
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_and_or_operators',
			[
				'type'        => 'select',
				'label'       => __( 'OR, AND Operators', 'piotnetforms' ),
				'value'       => 'or',
				'label_block' => true,
				'options'     => [
					'or'  => __( 'OR', 'piotnetforms' ),
					'and' => __( 'AND', 'piotnetforms' ),
				],
			]
		);
		// $this->add_control(
		// 	'conditional_visibility_comparison_operators',
		// 	[
		// 		'type'        => 'select',
		// 		'label'       => __( 'Comparison Operators', 'piotnetforms' ),
		// 		'value'       => '',
		// 		'label_block' => true,
		// 		'options'     => [
		// 			'not-empty' => __( 'not empty', 'piotnetforms' ),
		// 			'empty'     => __( 'empty', 'piotnetforms' ),
		// 			'='         => __( 'equals', 'piotnetforms' ),
		// 			'!='        => __( 'not equals', 'piotnetforms' ),
		// 			'>'         => __( '>', 'piotnetforms' ),
		// 			'>='        => __( '>=', 'piotnetforms' ),
		// 			'<'         => __( '<', 'piotnetforms' ),
		// 			'<='        => __( '<=', 'piotnetforms' ),
		// 			'checked'   => __( 'checked', 'piotnetforms' ),
		// 			'unchecked' => __( 'unchecked', 'piotnetforms' ),
		// 			'contains'  => __( 'contains', 'piotnetforms' ),
		// 		],
		// 	]
		// );
		$this->add_control(
			'conditional_visibility_type',
			[
				'type'        => 'select',
				'label'       => __( 'Type Value', 'piotnetforms' ),
				'value'       => 'string',
				'label_block' => true,
				'options'     => [
					'string' => __( 'String', 'piotnetforms' ),
					'number' => __( 'Number', 'piotnetforms' ),
				],
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_comparison_operators',
						'operator' => 'in',
						'value'    => [ '=', '!=', '>', '>=', '<', '<=' ],
					],
				],
			]
		);
		$this->add_control(
			'conditional_visibility_value',
			[
				'type'        => 'text',
				'label'       => __( 'Value', 'piotnetforms' ),
				'value'       => '50',
				'label_block' => true,
				'conditions'  => [
					[
						'name'     => 'conditional_visibility_comparison_operators',
						'operator' => 'in',
						'value'    => [ '=', '!=', '>', '>=', '<', '<=', 'contains' ],
					],
				],
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
			'conditional_visibility_by_backend_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Conditional List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'conditions'     => [
					[
						'name'     => 'conditional_visibility_enable',
						'operator' => '==',
						'value'    => 'yes',
					],
					[
						'name'     => 'conditional_visibility_by_backend',
						'operator' => '==',
						'value'    => 'yes',
					],
				],
			]
		);
	}

	private function conditions_old_version( $args = [] ) {
		if ( ! empty( $args['condition'] ) ) {
			if ( empty( $args['conditions'] ) ) {
				$args['conditions'] = [];
			}

			foreach ( $args['condition'] as $condition_key => $condition_value ) {
				$condition_operator = '==';

				if ( stripos( $condition_key, '!' ) !== false ) {
					$condition_operator = '!=';
					$condition_key      = str_replace( '!', '', $condition_key );
				}

				if ( is_array( $condition_value ) ) {
					$condition_operator = 'in';
					if ( stripos( $condition_key, '!' ) !== false ) {
						$condition_operator = '!in';
						$condition_key      = str_replace( '!', '', $condition_key );
					}
				}

				$args['conditions'][] = [
					'name'     => $condition_key,
					'operator' => $condition_operator,
					'value'    => $condition_value,
				];
			}
			unset( $args['condition'] );
		}

		if ( ! empty( $args['conditions'] ) ) {
			if ( ! empty( $args['conditions']['terms'] ) ) {
				$args['conditions'] = $args['conditions']['terms'];
				unset( $args['conditions']['terms'] );
			}
		}

		return $args;
	}
}
