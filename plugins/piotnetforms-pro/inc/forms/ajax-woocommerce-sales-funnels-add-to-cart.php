<?php

	add_action( 'wp_ajax_piotnetforms_ajax_woocommerce_sales_funnels_add_to_cart', 'piotnetforms_ajax_woocommerce_sales_funnels_add_to_cart' );
	add_action( 'wp_ajax_nopriv_piotnetforms_ajax_woocommerce_sales_funnels_add_to_cart', 'piotnetforms_ajax_woocommerce_sales_funnels_add_to_cart' );

	function piotnetforms_ajax_woocommerce_sales_funnels_add_to_cart() {
		if ( !empty( $_POST['options'] ) ) {
			$options = $_POST['options'];
			$product_id = $options['product_id'];
			$quantity = $options['quantity'];
			$variation_id = $options['variation_id'];

			if ( empty( $variation_id ) ) {
				$variation_id = 0;
			}

			$status = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, [] );
			$status_return = [];

			if ( $status ) {
				$status_return = [
					'status' => 1,
					'message' => $options['message_success'],
				];
			} else {
				$status_return = [
					'status' => 0,
					'message' => $options['message_out_of_stock'],
				];
			}

			echo json_encode( $status_return );
		}

		wp_die();
	}
