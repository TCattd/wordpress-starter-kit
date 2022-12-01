<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Lost_Password extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'lost-password';
	}

	public function get_class_name() {
		return 'Piotnetforms_Lost_Password';
	}

	public function get_title() {
		return 'Lost Password';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-lost-password.svg',
		];
	}

	public function get_categories() {
		return [ 'form' ];
	}

	public function get_keywords() {
		return [ 'lost password' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'lost_password_settings_section', 'Settings' );
		$this->lost_password_setting_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'lost_password_style_section', 'Style' );
		$this->lost_password_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function lost_password_setting_controls() {
		$this->add_control(
			'lost_password_text',
			[
				'label' => __( 'Text', 'piotnetforms' ),
				'type' => 'text',
				'label_block' => true,
				'default' => __( 'Lost your password?', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'lost_password_link_target',
			[
				'label' => __( 'Link Target', 'piotnetforms' ),
				'type' => 'select',
				'default' => '_self',
				'options' => [
					'_self'  => __( 'Self', 'piotnetforms' ),
					'_blank'  => __( 'Blank', 'piotnetforms' ),

				],
			]
		);
	}

	private function lost_password_style_controls() {
		$this->add_control(
			'lost_password_style_color',
			[
				'label' => __( 'Text Color', 'piotnetforms' ),
				'type' => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-lost-password__url' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'lost_password_style_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-lost-password__url',
			]
		);

		$this->add_responsive_control(
			'lost_password_style_align',
			[
				'label' => __( 'Alignment', 'piotnetforms' ),
				'type' => 'select',
				'options' => [
					'left'    => __( 'Left', 'piotnetforms' ),
					'center'  => __( 'Center', 'piotnetforms' ),
					'right'   => __( 'Right', 'piotnetforms' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);
	}

	public function render() {
		$settings = $this->settings;
		if ( !empty( $settings['lost_password_text'] ) ) {
			$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-lost-password' ); ?>	
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<a class="piotnetforms-lost-password__url" target="<?php echo $settings['lost_password_link_target']; ?>" href="<?php echo wp_lostpassword_url( get_permalink() ); ?>" title="<?php echo $settings['lost_password_text']; ?>"><?php echo $settings['lost_password_text']; ?></a>
			</div>
        <?php
		}
	}
	public function live_preview() {
		?>
		<%
			var s = data.widget_settings;
			view.add_attribute('wrapper', 'class', 'piotnetforms-lost-password');
		%>
			<div <%= view.render_attributes('wrapper') %>>
				<% if ( s['lost_password_text'] ) { %>
					<a class="piotnetforms-lost-password__url" target="<%= s['lost_password_link_target'] %>" href="<?php echo wp_lostpassword_url( get_permalink() ); ?>" title="<%= s['lost_password_text'] %>"><%= s['lost_password_text'] %></a>
				<% } %>
			</div>
		<?php
	}
}
