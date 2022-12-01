<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Multi_Step_Start extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'multi-step-start';
	}

	public function get_class_name() {
		return 'Piotnetforms_Multi_Step_Start';
	}

	public function get_title() {
		return 'Multi Step Start';
	}

	public function get_icon() {
		return [
			'type'  => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-multi-step-start.svg',
		];
	}

	public function get_categories() {
		return [ ];
	}

	public function get_keywords() {
		return [ 'text' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'settings_section', 'General' );
		$this->add_settings_controls();

		$this->start_section( 'scroll_to_top_setting_controls', 'Scroll To Top' );
		$this->scroll_to_top_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'progress_bar_style_section', 'Progress Bar' );
		$this->progress_bar_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function add_settings_controls() {
		$this->add_control(
			'disable_progressbar',
			[
				'label' => __( 'Disable Progressbar', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'step_title',
			[
				'type'        => 'text',
				'label'       => __( 'First Step Title', 'piotnetforms' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'steps_title',
			[
				'type'        => 'hidden',
				'label'       => __( 'Steps Title', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '["",""]',
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

	private function scroll_to_top_setting_controls() {
		$this->add_control(
			'piotnetforms_multi_step_form_scroll_to_top',
			[
				'label' => __( 'Enable', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'piotnetforms_multi_step_form_scroll_to_top_offset_desktop',
			[
				'label' => __( 'Desktop Negative Offset Top (px)', 'piotnetforms' ),
				'type' => 'number',
				'default' => 0,
				'condition' => [
					'piotnetforms_multi_step_form_scroll_to_top' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_multi_step_form_scroll_to_top_offset_tablet',
			[
				'label' => __( 'Tablet Negative Offset Top (px)', 'piotnetforms' ),
				'type' => 'number',
				'default' => 0,
				'condition' => [
					'piotnetforms_multi_step_form_scroll_to_top' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_multi_step_form_scroll_to_top_offset_mobile',
			[
				'label' => __( 'Mobile Negative Offset Top (px)', 'piotnetforms' ),
				'type' => 'number',
				'default' => 0,
				'condition' => [
					'piotnetforms_multi_step_form_scroll_to_top' => 'yes',
				],
			]
		);
	}

	private function progress_bar_style_controls() {
		$this->add_text_typography_controls(
			'typography_step_number',
			[
				'label' => 'Step Number',
				'selectors' => '{{WRAPPER}} .piotnetforms-multi-step-form__progressbar-item-step',
			]
		);

		$this->add_text_typography_controls(
			'typography_step_title',
			[
				'label' => 'Step Title',
				'selectors' => '{{WRAPPER}} .piotnetforms-multi-step-form__progressbar-item-title',
			]
		);

		$this->add_control(
			'progress_bar_step_title_hide_desktop',
			[
				'label' => __( 'Hide Step Title On Desktop', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => __( 'Hide', 'piotnetforms' ),
				'label_off' => __( 'Show', 'piotnetforms' ),
				'return_value' => 'piotnetforms-hidden-desktop',
			]
		);

		$this->add_control(
			'progress_bar_step_title_hide_tablet',
			[
				'label' => __( 'Hide Step Title On Tablet', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => __( 'Hide', 'piotnetforms' ),
				'label_off' => __( 'Show', 'piotnetforms' ),
				'return_value' => 'piotnetforms-hidden-tablet',
			]
		);

		$this->add_control(
			'progress_bar_step_title_hide_mobile',
			[
				'label' => __( 'Hide Step Title On Mobile', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => __( 'Hide', 'piotnetforms' ),
				'label_off' => __( 'Show', 'piotnetforms' ),
				'return_value' => 'piotnetforms-hidden-phone',
			]
		);

		$this->add_responsive_control(
			'progress_bar_step_width',
			[
				'label' => __( 'Step Number Width', 'piotnetforms' ),
				'type' => 'slider',
				'size_units' => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-multi-step-form__progressbar-item-step' => 'width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_bar_border_radius',
			[
				'label' => __( 'Border Radius', 'piotnetforms' ),
				'type' => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-multi-step-form__progressbar-item-step' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'tab_progress_bar_heading_tab',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'tab_progress_bar_normal',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'tab_progress_bar_active',
						'title' => __( 'ACTIVE', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->tab_progress_bar_style_controls(
			'normal'
		);
		$this->add_control(
			'tab_progress_bar_normal',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->tab_progress_bar_style_controls(
			'active'
		);
		$this->add_control(
			'tab_progress_bar_active',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Active', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function tab_progress_bar_style_controls( string $name, $args = [] ) {
		$wrapper           = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();
		$active = ( $name == 'active' ) ? '.active' : '';

		$this->add_control(
			'progress_bar_step_number_color_' . $name,
			[
				'label' => __( 'Step Number Color', 'piotnetforms' ),
				'type' => 'color',
				'default' => '',
				'render_type' => 'none',
				'selectors' => [
					'{{WRAPPER}} ' . $active . ' .piotnetforms-multi-step-form__progressbar-item-step' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_bar_step_number_background_color_' . $name,
			[
				'label' => __( 'Background Color', 'piotnetforms' ),
				'type' => 'color',
				'render_type' => 'none',
				'selectors' => [
					'{{WRAPPER}} ' . $active . ' .piotnetforms-multi-step-form__progressbar-item-step' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .piotnetforms-multi-step-form__progressbar-item' . $active . ' .piotnetforms-multi-step-form__progressbar-item-step-number::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	public function render() {
		$settings = $this->settings;
		$editor = ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) ? true : false;
		$form_post_id = $this->post_id;
		$form_version = empty( get_post_meta( $form_post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $form_post_id, '_piotnetforms_version', true );
		$form_id = $form_version == 1 ? get_post_meta( $form_post_id, '_piotnetforms_form_id', true ) : $form_post_id;
		$steps_title = json_decode( $settings['steps_title'], true );

		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-multi-step-form' );
		$this->add_render_attribute( 'wrapper', 'data-piotnetforms-multi-step-form-id', $form_id );

		if ( !empty( $settings['piotnetforms_multi_step_form_scroll_to_top'] ) ) {
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-multi-step-form-scroll-to-top', '' );
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-multi-step-form-scroll-to-top-offset-desktop', $settings['piotnetforms_multi_step_form_scroll_to_top_offset_desktop'] );
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-multi-step-form-scroll-to-top-offset-tablet', $settings['piotnetforms_multi_step_form_scroll_to_top_offset_tablet'] );
			$this->add_render_attribute( 'wrapper', 'data-piotnetforms-multi-step-form-scroll-to-top-offset-mobile', $settings['piotnetforms_multi_step_form_scroll_to_top_offset_mobile'] );
		}

		wp_enqueue_style( $this->slug . '-multi-step-style' ); ?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<?php if ( empty( $settings['disable_progressbar'] ) ) : ?>
				<div class="piotnetforms-multi-step-form__progressbar">
					<?php foreach ( $steps_title as $key => $steps_title_item ) : ?>
						<div class="piotnetforms-multi-step-form__progressbar-item<?php if ( $key == 0 ) : ?> active<?php endif; ?>">
							<div class="piotnetforms-multi-step-form__progressbar-item-step-number">
								<div class="piotnetforms-multi-step-form__progressbar-item-step"><?php echo $key + 1; ?></div>
							</div>
							<div class="piotnetforms-multi-step-form__progressbar-item-title <?php echo $settings['progress_bar_step_title_hide_desktop'] . ' ' . $settings['progress_bar_step_title_hide_tablet'] . ' ' . $settings['progress_bar_step_title_hide_mobile'] ; ?>"><?php echo $steps_title_item; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<?php if ( !$editor ) : ?>
				<div class="piotnetforms-multi-step-form__content">
					<div class="piotnetforms-multi-step-form__content-item active">
				<?php else : ?>
			</div>
				<?php endif; ?>
		<?php
	}

	public function live_preview() {
		?>
		<%
			let s = data.widget_settings;
			let	steps_title = JSON.parse(s.steps_title);
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<% if(!s.disable_progressbar){ %>
				<div class="piotnetforms-multi-step-form__progressbar">
					<% if(steps_title.length > 0){ %>
						<% _.each(steps_title, function(item, index){ %>
							<div class="piotnetforms-multi-step-form__progressbar-item<% if(index == 0){ %> active<% } %>">
								<div class="piotnetforms-multi-step-form__progressbar-item-step-number">
									<div class="piotnetforms-multi-step-form__progressbar-item-step"><%= index + 1 %></div>
								</div>
								<div class="piotnetforms-multi-step-form__progressbar-item-title"><%= item %></div>
							</div>
						<% }) %>
					<% } %>
				</div>
			<% } %>			
		</div>
		<?php
	}
}
