<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Woocommerce_Checkout extends Base_Widget_Piotnetforms {
	public function get_type() {
		return 'woocommerce-checkout';
	}

	public function get_class_name() {
		return 'Piotnetforms_Woocommerce_Checkout';
	}

	public function get_title() {
		return 'Woo Checkout';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-woocommerce-checkout.svg',
		];
	}

	public function get_categories() {
		return [ 'form' ];
	}

	public function get_keywords() {
		return [ 'woocommerce checkout' ];
	}

	public function get_script() {
		return [
			'piotnetforms-woocommerce-sales-funnels-script',
		];
	}

	public function get_style() {
		return [
			'piotnetforms-woocommerce-sales-funnels-style'
		];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'settings_section', 'Settings' );
		$this->setting_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}
	private function setting_controls() {
		$this->add_control(
			'piotnetforms_woocommerce_checkout_note',
			[
				'type' => 'html',
				'label_block' => true,
				'raw' => __( 'Note: You have to enter Regular price of Woocommerce Product. If your form has Repeater Fields, you have to enable Custom Order Item Meta.', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'piotnetforms_woocommerce_checkout_form_id',
			[
				'label' => __( 'Form ID* (Required)', 'piotnetforms' ),
				'type' => 'hidden',
				'description' => __( 'Enter the same form id for all fields in a form', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'remove_empty_form_input_fields',
			[
				'label' => __( 'Remove Empty Form Input Fields', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'piotnetforms_woocommerce_checkout_remove_fields',
			[
				'label' => __( 'Remove fields from WooCommerce Checkout Form', 'piotnetforms' ),
				'label_block' => true,
				'type' => 'select2',
				'multiple' => true,
				'options' => [
					'billing_first_name' => __( 'Billing First Name', 'piotnetforms' ),
					'billing_last_name' => __( 'Billing Last Name', 'piotnetforms' ),
					'billing_company' => __( 'Billing Company', 'piotnetforms' ),
					'billing_address_1' => __( 'Billing Address 1', 'piotnetforms' ),
					'billing_address_2' => __( 'Billing Address 2', 'piotnetforms' ),
					'billing_city' => __( 'Billing City', 'piotnetforms' ),
					'billing_postcode' => __( 'Billing Post Code', 'piotnetforms' ),
					'billing_country' => __( 'Billing Country', 'piotnetforms' ),
					'billing_state' => __( 'Billing State', 'piotnetforms' ),
					'billing_phone' => __( 'Billing Phone', 'piotnetforms' ),
					'billing_email' => __( 'Billing Email', 'piotnetforms' ),
					'order_comments' => __( 'Order Comments', 'piotnetforms' ),
					'shipping_first_name' => __( 'Shipping First Name', 'piotnetforms' ),
					'shipping_last_name' => __( 'Shipping Last Name', 'piotnetforms' ),
					'shipping_company' => __( 'Shipping Company', 'piotnetforms' ),
					'shipping_address_1' => __( 'Shipping Address 1', 'piotnetforms' ),
					'shipping_address_2' => __( 'Shipping Address 2', 'piotnetforms' ),
					'shipping_city' => __( 'Shipping City', 'piotnetforms' ),
					'shipping_postcode' => __( 'Shipping Post Code', 'piotnetforms' ),
					'shipping_country' => __( 'Shipping Country', 'piotnetforms' ),
					'shipping_state' => __( 'Shipping State', 'piotnetforms' ),
				],
			]
		);

		$this->add_control(
			'piotnetforms_woocommerce_checkout_product_id',
			[
				'label' => __( 'Product ID* (Required)', 'piotnetforms' ),
				'type' => 'text',
				'dynamic' => [
					'active' => true,
				],
			]
		);

        $this->add_control(
			'woocommerce_quantity_option',
			[
				'label' => __( 'Quantity', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

        $this->add_control(
			'woocommerce_quantity',
			[
				'label' => __( 'Quantity Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'Field Shortcode. E.g [field id="total"]', 'piotnetforms' ),
                'condition' => [
                    'woocommerce_quantity_option' => 'yes'
                ]
			]
		);

		$this->add_control(
			'piotnetforms_woocommerce_checkout_redirect',
			[
				'label' => __( 'Redirect', 'piotnetforms' ),
				'type' => 'text',
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'woocommerce_add_to_cart_price',
			[
				'label' => __( 'Price Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'Field Shortcode. E.g [field id="total"]', 'piotnetforms' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_meta_enable',
			[
				'label' => __( 'Custom Order Item Meta', 'piotnetforms' ),
				'type' => 'switch',
				'description' => __( 'If your form has Repeater Fields, you have to enable it and enter Repeater Shortcode', 'piotnetforms' ),
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->new_group_controls();
		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_field_shortcode',
			[
				'label' => __( 'Field Shortcode, Repeater Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
			]
		);

		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_remove_if_field_empty',
			[
				'label' => __( 'Remove If Field Empty', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
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
			'woocommerce_add_to_cart_custom_order_item_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Order Item List', 'piotnetforms' ),
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition' => [
					'woocommerce_add_to_cart_custom_order_item_meta_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'booking_enable',
			[
				'label' => __( 'Booking', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'booking_shortcode',
			[
				'label' => __( 'Booking Shortcode', 'piotnetforms' ),
				'type' => 'text',
				'placeholder' => __( '[field id="booking"]', 'piotnetforms' ),
				'label_block' => true,
				'condition' => [
					'booking_enable' => 'yes',
				],
			]
		);
	}

	public function render() {
		$settings = $this->settings;
		global $woocommerce;
		if ( is_null( $woocommerce->cart ) ) {
			wc_load_cart();
		}
		$product_cart_id = WC()->cart->generate_cart_id( $settings['piotnetforms_woocommerce_checkout_product_id'] );
		$cart_item_key = $woocommerce->cart->find_product_in_cart( $product_cart_id );
		if ( empty( $cart_item_key ) ) {
			$woocommerce->cart->add_to_cart( $settings['piotnetforms_woocommerce_checkout_product_id'] );
		}
		$form_post_id = $this->post_id;
		$form_version = empty( get_post_meta( $form_post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $form_post_id, '_piotnetforms_version', true );
		$form_id = $form_version == 1 ? $settings['piotnetforms_woocommerce_checkout_form_id'] : $form_post_id;

		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-btn' );

		if ( !empty( $form_id ) ) :

			?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div data-piotnetforms-woocommerce-checkout-form-id="<?php echo $form_id; ?>" data-piotnetforms-woocommerce-checkout-product-id="<?php echo $settings['piotnetforms_woocommerce_checkout_product_id']; ?>" data-piotnetforms-woocommerce-checkout-post-id="<?php echo $this->post_id; ?>" data-piotnetforms-woocommerce-checkout-id="<?php echo $this->get_id(); ?>" >
				<?php echo do_shortcode( '[woocommerce_checkout]' ); ?>
			</div>
		</div>	
		<?php
		endif;
	}

	public function live_preview() {
	}
}
