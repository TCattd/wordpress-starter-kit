<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Section extends Division_Base_Widget_Piotnetforms {
	public function get_type() {
		return 'section';
	}

	public function get_class_name() {
		return 'piotnetforms_Section';
	}

	public function get_title() {
		return 'Section';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-section.svg',
		];
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'section' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'section_settings', 'Layout' );
		$this->add_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'section_style_typography', 'Typography' );
		$this->add_style_typography_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function add_setting_controls() {
		$this->add_control(
			'section_content_width_type',
			[
				'type'        => 'select',
				'label'       => __( 'Content Width', 'piotnetforms' ),
				'value'       => 'boxed',
				'label_block' => true,
				'options'     => [
					'boxed'      => 'Boxed',
					'full-width' => 'Full Width',
				],
			]
		);
		$this->add_responsive_control(
			'section_content_width',
			[
				'type'        => 'slider',
				'label'       => __( 'Width', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
					'size' => 1140,
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
					'{{WRAPPER}}>.piotnet-section__container' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => 'section_content_width_type',
						'operator' => '==',
						'value'    => 'boxed',
					],
				],
			]
		); // FIXME conditions not working

		$this->add_responsive_control(
			'section_min_height',
			[
				'type'        => 'slider',
				'label'       => __( 'Min Height', 'piotnetforms' ),
				'value'       => [
					'unit' => 'px',
				],
				'label_block' => true,
				'size_units'  => [
					'px' => [
						'min'  => 0,
						'max'  => 2000,
						'step' => 1,
					],
					'vh' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}}>.piotnet-section__container' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);
	}

	private function add_style_typography_controls() {
		$this->add_responsive_control(
			'section_text_align',
			[
				'type'         => 'select',
				'label'        => 'Text Alignment',
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

		$this->add_control(
			'section_text_color',
			[
				'type'      => 'color',
				'label'     => 'Text Color',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_text_typography_controls(
			'section_text_typography',
			[
				'selectors' => '{{WRAPPER}}',
			]
		);
	}

	public function render_start( $editor = false ) {
		$settings = $this->settings; ?>
			<?php if ( $editor ) : ?>
				<div class="piotnet-section__controls">
					<div class="piotnet-section__controls-item piotnet-section__controls-item--edit" title="Edit" draggable="false" data-piotnet-control-edit>
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-control-edit.svg'; ?>" draggable="false">
					</div>
					<div class="piotnet-section__controls-item piotnet-section__controls-item--duplicate" title="Duplicate" draggable="false" data-piotnet-control-duplicate>
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-control-duplicate.svg'; ?>" draggable="false">
					</div>
					<div class="piotnet-section__controls-item piotnet-section__controls-item--remove" title="Delete" draggable="false" data-piotnet-control-remove>
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-control-remove.svg'; ?>" draggable="false">
					</div>
				</div>
			<?php endif; ?>
			<div class="piotnet-section__container<?php if ( $settings['section_content_width_type'] == 'full-width' ) {
				echo ' piotnet-section__container--full-width';
			} ?>"
		<?php
			if ( $editor ) :
				?>
				 data-piotnet-section-container data-piotnet-inner-html<?php endif; ?>>
		<?php
	}

	public function render_end() {
		?>
			</div>
		<?php
	}

	public function render() {
		?>
		<?php
	}

	public function live_preview() {
		?>
		<?php
	}
}
