<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Piotnetforms_Preview_Submissions extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'preview-submissions';
	}

	public function get_class_name() {
		return 'Piotnetforms_Preview_Submissions';
	}

	public function get_title() {
		return 'Preview Submissions';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/i-preview-submissions.svg',
		];
	}

	public function get_categories() {
		return [ 'piotnetforms' ];
	}

	public function get_keywords() {
		return [ 'button' ];
	}

	public function get_script() {
		return [ 
			'piotnetforms-preview-submission-script',
		];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'settings_section', 'Settings' );
		$this->setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'label_style_section', 'Style' );
		$this->label_style_controls();
		$this->start_section( 'value_style_section', 'Style' );
		$this->value_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function setting_controls() {
		$this->add_control(
			'form_id',
			[
				'label' => __( 'Form ID* (Required)', 'pafe' ),
				'type' => 'text',
				'description' => __( 'Enter the same form id for all fields in a form', 'pafe' ),
			]
		);

		$this->add_control(
			'remove_empty_form_input_fields',
			[
				'label' => __( 'Remove Empty Form Input Fields', 'pafe' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'preview_submission_custom_list_fields',
			[
				'label' => __( 'Custom List Fields', 'pafe' ),
				'type' => 'switch',
				'description' => __( 'If your form has Repeater Fields, you have to enable it and enter Repeater Shortcode', 'pafe' ),
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->new_group_controls();
		$this->add_control(
			'field_shortcode',
			[
				'label' => __( 'Field Shortcode, Repeater Shortcode', 'pafe' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
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
			'preview_submission_custom_list_fields_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'List Fields', 'piotnetforms' ),
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition' => [
					'preview_submission_custom_list_fields' => 'yes',
				],
			]
		);
	}

	private function label_style_controls() {
		$this->add_control(
			'preview_submission_style_label_color',
			[
				'label' => __( 'Text Color', 'elementor' ),
				'type' => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-preview-submission__item-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'preview_submission_style_label_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-preview-submission__item-label',
			]
		);
	}

	private function value_style_controls() {
		$this->add_control(
			'preview_submission_style_value_color',
			[
				'label' => __( 'Text Color', 'elementor' ),
				'type' => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-preview-submission__item-value' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'preview_submission_style_value_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-preview-submission__item-value',
			]
		);
	}

	public function render() {

		$settings = $this->settings;

		$this->add_render_attribute( 'wrapper', 'class', 'pafe-form-builder-preview-submission' );
		$this->add_render_attribute( 'wrapper', 'data-piotnetforms-preview-submission', $settings['form_id'] );

		if (!empty( $settings['remove_empty_form_input_fields'])) {
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-preview-submission-remove-empty-fields', '' );
		}
		
		if (!empty($settings['preview_submission_custom_list_fields_list']) && !empty($settings['preview_submission_custom_list_fields'])) {
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-preview-submission-custom-list-fields', json_encode($settings['preview_submission_custom_list_fields_list']) );
		}

		if ( !empty( $settings['form_id'] ) ) {

		?>	
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			</div>
        <?php

		}
	}
	public function live_preview() {
		?>
		<%	
			var s = data.widget_settings;
			view.add_attribute('wrapper', 'class', 'pafe-form-builder-preview-submission');
			view.add_attribute('wrapper', 'data-piotnetforms-preview-submission', s['form_id']);

			if ( s['remove_empty_form_input_fields'] ) {
				view.add_attribute('wrapper', 'data-piotnetforms-preview-submission-remove-empty-fields', '' );
			}

			if ( s['preview_submission_custom_list_fields_list'] && s['preview_submission_custom_list_fields'] ) {
				view.add_attribute('wrapper', 'data-piotnetforms-preview-submission-custom-list-fields', JSON.stringify( s['preview_submission_custom_list_fields_list']) );
			}
		%>
			<div <%= view.render_attributes('wrapper') %>>
				<% if ( s['form_id'] ) { %>
					<div class="piotnetforms-preview-submission__item"><label class="piotnetforms-preview-submission__item-label">Preview Submissions: </label><span class="piotnetforms-preview-submission__item-value">This feature only works on the frontend</span></div>
				<% } %>
			</div>
		<?php
	}

}
