<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Form_Builder_Data extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'piotnetforms-display-form-submissions';
	}

	public function get_class_name() {
		return 'Piotnetforms_Form_Builder_Data';
	}

	public function get_title() {
		return 'Form Entries';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-form-entries.svg',
		];
	}

	public function get_categories() {
		return [ 'form' ];
	}

	public function get_keywords() {
		return [ 'form entries', 'database' ];
	}

	public function get_style() {
		return [
			'piotnetforms-fontawesome-style'
		];
	}

	private function add_table_style_controls() {
		$this->add_control(
			'pf_form_data_table_background_color',
			[
				'type'      => 'color',
				'label' => __( 'Background Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-form-database-table__inner table' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'pf_form_data_table_width',
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
					'{{WRAPPER}} .piotnetforms-form-database-table__inner' => 'width:{{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'pf_form_data_column_width',
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
					'{{WRAPPER}} .piotnetforms-form-database-table__inner .piotnetforms-form-data__field--name' => 'width:{{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_control(
			'pf_form_data_table_button_border_type',
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
					'{{WRAPPER}} .piotnetforms-form-database-table__inner table' => 'border-style:{{VALUE}};',
				],
			]
		);
		$this->add_control(
			'pf_form_data_table_button_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-form-database-table__inner table' => 'border-color: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'pf_form_data_table_button_border_width',
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
					'{{WRAPPER}} .piotnetforms-form-database-table__inner table' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}

	private function add_field_value_style_controls() {
		$this->add_text_typography_controls(
			'pf_form_data_field_value_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-form-data__field--value',
			]
		);

		$this->add_control(
			'pf_form_data_field_value_color',
			[
				'type'      => 'color',
				'label'     => 'Text Color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-form-data__field--value' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pf_form_data_field_value_background_color',
			[
				'type'      => 'color',
				'label' => __( 'Background Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-form-data__field--value' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'pf_form_data_field_value_button_padding',
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
					'{{WRAPPER}} .piotnetforms-form-data__field--value' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'pf_form_data_field_value_text_align',
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
				'selectors'    => [
					'{{WRAPPER}} .piotnetforms-form-data__field--value' => 'text-align: {{VALUE}}',
				],
			]
		);
	}
	private function add_field_name_style_controls() {
		$this->add_text_typography_controls(
			'pf_form_data_field_name_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-form-data__field--name',
			]
		);

		$this->add_control(
			'pf_form_data_field_name_color',
			[
				'type'      => 'color',
				'label'     => 'Text Color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-form-data__field--name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pf_form_data_field_name_background_color',
			[
				'type'      => 'color',
				'label' => __( 'Background Color', 'piotnetforms' ),
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-form-data__field--name' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'pf_form_data_field_name_button_padding',
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
					'{{WRAPPER}} .piotnetforms-form-data__field--name' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'pf_form_data_field_name_text_align',
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
				'selectors'    => [
					'{{WRAPPER}} .piotnetforms-form-data__field--name' => 'text-align: {{VALUE}}',
				],
			]
		);
	}

	private function add_setting_controls() {
		$args = [
			'post_type' => 'piotnetforms-data',
			'posts_per_page' => -1,
		];
		$query = new WP_Query( $args );
		$field_id = [];
		$forms_id = [];
		$th = [];
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();

			$fields_database = get_post_meta( get_the_ID(), '_piotnetforms_fields_database', true );
			if ( $fields_database ) {
				$fields_database = json_decode( $fields_database, true );
			}
			$metas = get_post_meta( get_the_ID() );

			foreach ( $metas as $key => $value ) {
				if ( $key === 'form_id' ) {
					foreach ( $value as $formid ) {
						$forms_id[$formid] = $formid;
					}
				}
				if ( !in_array( $key, $field_id ) ) {
					if ( is_array( $fields_database ) ) {
						if ( isset( $fields_database[$key] ) ) {
							$field_id[] = $key;
						}
					} else {
						$field_id[] = $key;
					}
				}
			}
			foreach ( $field_id as $id ) {
				if ( $id != 'form_id' && $id != '_elementor_controls_usage' && $id != '_edit_lock' && $id != 'form_id_piotnetforms' && $id != 'post_id' && $id != '_piotnetforms_fields_database' ) {
					if ( is_array( $fields_database ) ) {
						if ( isset( $fields_database[$id] ) ) {
							$th[$fields_database[$id]['label']] = !empty( $fields_database[$id]['label'] ) ? $fields_database[$id]['label'] : $id;
						}
					} else {
						$th[] = $id;
					}
				}
			}
			$th = array_unique( $th );
		endwhile;
		endif;

		$this->add_control(
			'piotnetforms_form_data_id',
			[
				'label'       => __( 'Form ID* (Required)', 'piotnetforms' ),
				'type'        => 'select',
				'options' => $forms_id,
			]
		);
		$this->new_group_controls();

		$this->add_control(
			'piotnetforms_form_data_field_label',
			[
				'label' => __( 'Field Label* (Required)', 'piotnetforms' ),
				'type'        => 'select',
				'options' => $th,
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
				'label_block'    => true,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);

		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'piotnetforms_form_data_repeater',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Label List', 'piotnetforms' ),
				'add_label'      => __( 'Add Field Label', 'piotnetforms' ),
				'label_block'    => true,
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );
		$this->start_section( 'piotnetforms_data_settings_section', 'Settings' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'pf_field_name_styles_section', 'Field Name' );
		$this->add_field_name_style_controls();

		$this->start_section( 'pf_field_value_styles_section', 'Field Value' );
		$this->add_field_value_style_controls();

		$this->start_section( 'pf_table_styles_section', 'Table Value' );
		$this->add_table_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function getColumnsNeedToShow( $all_columns, $show_columns ) {
		$columnIndexes = [];
		foreach ( $show_columns as $item ) {
			$columnIndexes[] =  array_search( $item['piotnetforms_form_data_field_label'], $all_columns );
		}
		return $columnIndexes;
	}

	public function render() {
		$settings = $this->settings;
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-form-database-table' );
		$fields = $this->get_fields_data( $settings );
		$columnIndexes = $this->getColumnsNeedToShow( $fields[0], $settings['piotnetforms_form_data_repeater'] ); ?>
            <div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?> class="">
                <div class="piotnetforms-form-database-table__inner">
                <table>
                    <tr>
                        <?php foreach ( $fields[0] as $key => $fields_th ) : if ( in_array( $key, $columnIndexes ) ) : ?>
                            <th class="piotnetforms-form-data__field--name"><?php echo $fields_th; ?></th>
                        <?php endif;
                        endforeach; ?>
                    </tr>

                <?php
                        		$field = array_shift( $fields );
		foreach ( $fields as $fields_td ) :
			?>
                    <tr>
                        <?php foreach ( $fields_td as $key => $fields_tds ) : if ( in_array( $key, $columnIndexes ) ) : ?>
                            <td class="piotnetforms-form-data__field--value"><?php echo $fields_tds; ?></td>
                        <?php endif;
                        endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
            </div>
        </div>
        <?php
	}

	private function get_fields_data( $settings=[] ) {
		$args = [
			'post_type' => 'piotnetforms-data',
			'posts_per_page' => -1,
		];

		if ( !empty( $settings['piotnetforms_form_data_id'] ) ) {
			$form_id = $settings['piotnetforms_form_data_id'];
			$args['meta_value'] = str_replace( '+', ' ', $form_id );
			$args['meta_key'] = 'form_id';
		}

		$query = new WP_Query( $args );
		$field_id = [];
		$fields = [];
		$th = [];
		$index = 0;

		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
			$index++;
			if ( $index == 1 ) {
				$fields_database = get_post_meta( get_the_ID(), '_piotnetforms_fields_database', true );
				if ( $fields_database ) {
					$fields_database = json_decode( $fields_database, true );
				}
			}
			$metas = get_post_meta( get_the_ID() );
			foreach ( $metas as $key => $value ) {
				if ( !in_array( $key, $field_id ) ) {
					if ( is_array( $fields_database ) ) {
						if ( isset( $fields_database[$key] ) ) {
							$field_id[] = $key;
						}
					} else {
						$field_id[] = $key;
					}
				}
			}
		endwhile;
		endif;
		foreach ( $field_id as $id ) {
			if ( $id != 'form_id' && $id != 'form_id_piotnetforms' && $id != '_elementor_controls_usage' && $id != '_edit_lock' && $id != 'form_id_elementor' && $id != 'post_id' && $id != '_piotnetforms_form_builder_fields_database' ) {
				if ( is_array( $fields_database ) ) {
					if ( isset( $fields_database[$id] ) ) {
						$th[] = !empty( $fields_database[$id]['label'] ) ? $fields_database[$id]['label'] : $id;
					}
				} else {
					$th[] = $id;
				}
			}
		}

		$fields[] = $th;

		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
			$tr = [];
			foreach ( $field_id as $id ) {
				if ( $id != 'form_id_piotnetforms' && $id != 'form_id' && $id != '_elementor_controls_usage' && $id != '_edit_lock' && $id != 'form_id_elementor' && $id != 'post_id' && $id != '_piotnetforms_form_builder_fields_database' ) {
					$meta_value = get_post_meta( get_the_ID(), $id, true );
					$tr[] = $meta_value;
				}
			}

			$fields[] = $tr;
		endwhile;
		endif;
		return $fields;
	}

	public function live_preview() {
		?>
        <%
        if ( s['piotnetforms_form_data_id'] && !in_array( '', $columnIndexes )) {
        %>
        <div class="piotnetforms-form-database-table">
            <table>
                <tr>
                    <?php foreach ( $fields[0] as $key => $fields_th ) : if ( in_array( $key, $columnIndexes ) ) : ?>
                        <th class="piotnetforms-form-data__field--name"><?php echo $fields_th; ?></th>
                    <?php endif;
                    endforeach; ?>
                </tr>

                <?php
                    		$field = array_shift( $fields );
		foreach ( $fields as $fields_td ) :
			?>
                    <tr>
                        <?php foreach ( $fields_td as $key => $fields_tds ) : if ( in_array( $key, $columnIndexes ) ) : ?>
                            <td class="piotnetforms-form-data__field--value"><?php echo $fields_tds; ?></td>
                        <?php endif;
                        endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <%
        }
        %>
        <?php
	}
}
