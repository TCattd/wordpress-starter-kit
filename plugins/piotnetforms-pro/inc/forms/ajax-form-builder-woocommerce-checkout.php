<?php

	add_action( 'wp_ajax_piotnetforms_ajax_form_builder_woocommerce_checkout', 'piotnetforms_ajax_form_builder_woocommerce_checkout' );
	add_action( 'wp_ajax_nopriv_piotnetforms_ajax_form_builder_woocommerce_checkout', 'piotnetforms_ajax_form_builder_woocommerce_checkout' );

	function set_val_woocommerce_checkout_piotnetforms( &$array, $path, $val ) {
		for ( $i=&$array; $key=array_shift( $path ); $i=&$i[$key] ) {
			if ( !isset( $i[$key] ) ) {
				$i[$key] = [];
			}
		}
		$i = $val;
	}

	function piotnetforms_merge_string_woocommerce_checkout( &$string, $string_add ) {
		$string = $string . $string_add;
	}

	function piotnetforms_unset_string_woocommerce_checkout( &$string ) {
		$string = '';
	}

	function piotnetforms_set_string_woocommerce_checkout( &$string, $string_set ) {
		$string = $string_set;
	}

	function replace_email_woocommerce_checkout_piotnetforms( $content, $fields, $payment_status = 'succeeded', $payment_id = '', $succeeded = 'succeeded', $pending = 'pending', $failed = 'failed', $submit_id = 0 ) {
		$message = $content;

		$message_all_fields = '';

		if ( !empty( $fields ) ) {

			// all fields
			foreach ( $fields as $field ) {
				$field_value = $field['value'];
				$field_label = isset( $field['label'] ) ? $field['label'] : '';
				if ( isset( $field['value_label'] ) ) {
					$field_value = $field['value_label'];
				}

				$repeater_id = $field['repeater_id'];
				$repeater_id_string = '';
				$repeater_id_array = array_reverse( explode( ',', rtrim( $repeater_id, ',' ) ) );
				foreach ( $repeater_id_array as $repeater ) {
					$repeater_array = explode( '|', $repeater );
					array_pop( $repeater_array );
					$repeater_id_string .= join( ',', $repeater_array );
				}
				$repeater_index = $field['repeater_index'];
				$repeater_index_1 = $repeater_index + 1;
				$repeater_label = '<span data-id="' . esc_attr( $repeater_id_string ) . '"><strong>' . $field['repeater_label'] . ' ' . $repeater_index_1 . ': </strong></span><br>';

				$repeater_remove_this_field = false;
				if ( isset( $field['repeater_remove_this_field'] ) ) {
					$repeater_remove_this_field = true;
				}

				if ( !empty( $repeater_id ) && !empty( $repeater_label ) && $repeater_remove_this_field == false ) {
					if ( strpos( $message_all_fields, $repeater_label ) !== false ) {
						$message_all_fields .= '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
					} else {
						$message_all_fields .= $repeater_label;
						$message_all_fields .= '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
					}
				// if ($field['repeater_index'] != ($field['repeater_length'] - 1)) {
				// 	$message .=  '</div>';
				// }
				} else {
					if ( strpos( $field['name'], 'piotnetforms-end-repeater' ) === false ) {
						$message_all_fields .= '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
					}
				}
			}

			$message = str_replace( '[all-fields]', $message_all_fields, $message );

			// each field

			$repeater_content = '';
			$repeater_id_one = '';
			foreach ( $fields as $field ) {
				$field_value = $field['value'];
				$field_label = isset( $field['label'] ) ? $field['label'] : '';
				if ( isset( $field['value_label'] ) ) {
					$field_value = $field['value_label'];
				}

				$search_remove_line_if_field_empty = '[field id="' . $field['name'] . '"]' . '[remove_line_if_field_empty]';

				if ( empty( $field_value ) ) {
					$lines = explode( "\n", $message );
					$lines_found = [];

					foreach ( $lines as $num => $line ) {
						$pos = strpos( $line, $search_remove_line_if_field_empty );
						if ( $pos !== false ) {
							$lines_found[] = $line;
						}
					}

					if ( !empty( $lines_found ) ) {
						foreach ( $lines_found as $line ) {
							$message = str_replace( [ $line . "\n", "\n" . $line ], '', $message );
						}
					}
				}

				$search = '[field id="' . $field['name'] . '"]';
				$message = str_replace( $search, $field_value, $message );

				$repeater_id = $field['repeater_id'];
				$repeater_id_string = '';
				$repeater_id_array = array_reverse( explode( ',', rtrim( $repeater_id, ',' ) ) );
				foreach ( $repeater_id_array as $repeater ) {
					$repeater_array = explode( '|', $repeater );
					array_pop( $repeater_array );
					$repeater_id_string .= join( ',', $repeater_array );
				}
				$repeater_index = $field['repeater_index'];
				$repeater_index_1 = $repeater_index + 1;
				$repeater_label = '<span data-id="' . esc_attr( $repeater_id_string ) . '"><strong>' . $field['repeater_label'] . ' ' . $repeater_index_1 . ': </strong></span><br>';

				$repeater_remove_this_field = false;
				if ( isset( $field['repeater_remove_this_field'] ) ) {
					$repeater_remove_this_field = true;
				}

				if ( !empty( $repeater_id ) && !empty( $repeater_label ) && $repeater_remove_this_field == false ) {
					if ( strpos( $repeater_content, $repeater_label ) !== false ) {
						$string_add = '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
						piotnetforms_merge_string_woocommerce_checkout( $repeater_content, $string_add );
					} else {
						$string_add = $repeater_label . '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
						piotnetforms_merge_string_woocommerce_checkout( $repeater_content, $string_add );
					}
					if ( substr_count( $field['repeater_id'], '|' ) == 2 ) {
						piotnetforms_set_string_woocommerce_checkout( $repeater_id_one, $field['repeater_id_one'] );
					}
				}

				if ( empty( $repeater_id ) ) {
					if ( !empty( $repeater_id_one ) && !empty( $repeater_content ) ) {
						$search_repeater = '[repeater id="' . $repeater_id_one . '"]';
						$message = str_replace( $search_repeater, $repeater_content, $message );
						piotnetforms_unset_string_woocommerce_checkout( $repeater_content );
						piotnetforms_unset_string_woocommerce_checkout( $repeater_id_one );
					}
				}
			}
		}

		$search_remove_line_if_field_empty = '"]' . '[remove_line_if_field_empty]'; // fix alert [

		$lines = explode( "\n", $message );
		$lines_found = [];

		foreach ( $lines as $num => $line ) {
			$pos = strpos( $line, $search_remove_line_if_field_empty );
			if ( $pos !== false ) {
				$lines_found[] = $line;
			}
		}

		if ( !empty( $lines_found ) ) {
			foreach ( $lines_found as $line ) {
				$message = str_replace( [ $line . "\n", "\n" . $line ], '', $message );
			}
		}

		$message = str_replace( [ '[remove_line_if_field_empty]' ], '', $message );

		$message = str_replace( [ "\r\n", "\n", "\r", '[remove_line_if_field_empty]' ], '<br />', $message );

		if ( $payment_status == 'succeeded' ) {
			$message = str_replace( '[payment_status]', $succeeded, $message );
		}

		if ( $payment_status == 'pending' ) {
			$message = str_replace( '[payment_status]', $pending, $message );
		}

		if ( $payment_status == 'failed' ) {
			$message = str_replace( '[payment_status]', $failed, $message );
		}

		if ( !empty( $payment_id ) ) {
			$message = str_replace( '[payment_id]', $payment_id, $message );
		}

		if ( !empty( $submit_id ) ) {
			$message = str_replace( '[submit_id]', $submit_id, $message );
		}

		return $message;
	}

	function get_field_name_shortcode_woocommerce_checkout_piotnetforms( $content ) {
		$field_name = str_replace( '[field id="', '', $content );
		$field_name = str_replace( '[repeater id="', '', $field_name ); // fix alert ]
		$field_name = str_replace( '"]', '', $field_name );
		return trim( $field_name );
	}

	function piotnetforms_get_field_value_woocommerce_checkout( $field_name, $fields, $payment_status = 'succeeded', $payment_id = '', $succeeded = 'succeeded', $pending = 'pending', $failed = 'failed' ) {
		$field_name_first = $field_name;

		if ( strpos( $field_name, '[repeater id' ) !== false ) { // ] [ [ fix alert
			$field_name = str_replace( 'id="', "id='", $field_name );
			$field_name = str_replace( '"]', "']", $field_name );
			$message = $field_name;
			$repeater_content = '';
			$repeater_id_one = '';
			foreach ( $fields as $field ) {
				$field_value = $field['value'];
				$field_label = isset( $field['label'] ) ? $field['label'] : '';
				if ( isset( $field['value_label'] ) ) {
					$field_value = $field['value_label'];
				}

				$search = '[field id="' . $field['name'] . '"]';
				$message = str_replace( $search, $field_value, $message );

				$repeater_id = $field['repeater_id'];
				$repeater_id_string = '';
				$repeater_id_array = array_reverse( explode( ',', rtrim( $repeater_id, ',' ) ) );
				foreach ( $repeater_id_array as $repeater ) {
					$repeater_array = explode( '|', $repeater );
					array_pop( $repeater_array );
					$repeater_id_string .= join( ',', $repeater_array );
				}
				$repeater_index = $field['repeater_index'];
				$repeater_index_1 = $repeater_index + 1;
				$repeater_label = $field['repeater_label'] . ' ' . $repeater_index_1 . '</div>';

				$repeater_remove_this_field = false;
				if ( isset( $field['repeater_remove_this_field'] ) ) {
					$repeater_remove_this_field = true;
				}

				if ( !empty( $repeater_id ) && !empty( $repeater_label ) && $repeater_remove_this_field == false ) {
					if ( strpos( $repeater_content, $repeater_label ) !== false ) {
						$string_add = '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
						piotnetforms_merge_string_woocommerce_checkout( $repeater_content, $string_add );
					} else {
						$string_add = $repeater_label . '<div class="piotnetforms-woocommerce_checkout-submission__item"><label class="piotnetforms-woocommerce_checkout-submission__item-label">' . $field_label . ': </label>' . '<span class="piotnetforms-woocommerce_checkout-submission__item-value">' . $field_value . '</span>' . '</div>';
						piotnetforms_merge_string_woocommerce_checkout( $repeater_content, $string_add );
					}
					if ( substr_count( $field['repeater_id'], '|' ) == 2 ) {
						piotnetforms_set_string_woocommerce_checkout( $repeater_id_one, $field['repeater_id_one'] );
					}
				}

				if ( empty( $repeater_id ) ) {
					if ( !empty( $repeater_id_one ) && !empty( $repeater_content ) ) {
						$search_repeater = "[repeater id='" . $repeater_id_one . "']";
						$message = str_replace( $search_repeater, $repeater_content, $message );

						piotnetforms_unset_string_woocommerce_checkout( $repeater_content );
						piotnetforms_unset_string_woocommerce_checkout( $repeater_id_one );
					}
				}
			}

			$field_value = $message;
		} else {
			$field_name = get_field_name_shortcode_woocommerce_checkout_piotnetforms( $field_name );
			$field_value = '';
			foreach ( $fields as $key_field=>$field ) {
				if ( $fields[$key_field]['name'] == $field_name ) {
					// if (!empty($fields[$key_field]['value'])) {
					// 	$field_value = $fields[$key_field]['value'];
					// }

					if ( isset( $fields[$key_field]['calculation_results'] ) ) {
						$field_value = $fields[$key_field]['calculation_results'];
					} else {
						$field_value = $fields[$key_field]['value'];
					}
				}
			}
		}

		if ( strpos( $field_name_first, '[payment_status]' ) !== false || strpos( $field_name_first, '[payment_id]' ) !== false ) {
			if ( $payment_status == 'succeeded' ) {
				$field_value = str_replace( '[payment_status]', $succeeded, $field_name_first );
			}

			if ( $payment_status == 'pending' ) {
				$field_value = str_replace( '[payment_status]', $pending, $field_name_first );
			}

			if ( $payment_status == 'failed' ) {
				$field_value = str_replace( '[payment_status]', $failed, $field_name_first );
			}

			if ( !empty( $payment_id ) && strpos( $field_name_first, '[payment_id]' ) !== false ) {
				$field_value = str_replace( '[payment_id]', $payment_id, $field_name_first );
			}
		}

		return trim( $field_value );
	}

	function piotnetforms_ajax_form_builder_woocommerce_checkout() {
		if ( !empty( $_POST['fields'] ) && !empty( $_POST['form_id'] ) && !empty( $_POST['post_id'] ) && !empty( $_POST['product_id'] ) ) {
			$post_id = $_POST['post_id'];
			$form_id = $_POST['form_id'];
			$fields = stripslashes( $_POST['fields'] );
			$fields = json_decode( $fields, true );
			$fields = array_unique( $fields, SORT_REGULAR );

			$form = [];

			$data     = json_decode( get_post_meta( $post_id, '_piotnetforms_data', true ), true );
			$form['settings'] = $data['widgets'][ $form_id ]['settings'];

			if ( !empty( $form['settings']['remove_empty_form_input_fields'] ) ) {
				$fields_new = [];
				foreach ( $fields as $field ) {
					if ( !empty( $field['value'] )  && $field['type'] != 'file') {
						$fields_new[] = $field;
					}elseif($field['type'] == 'file' && !empty($field['file_name'])){
                        $fields_new[] = $field;
                    }
				}
				$fields = $fields_new;
			}

			// Filter Hook

			$fields = apply_filters( 'piotnetforms/form_builder/fields', $fields );

            $attachment = [];

			$not_allowed_extensions = [ 'php', 'phpt', 'php5', 'php7', 'exe' ];
            
			if ( !empty( $_FILES ) ) {
				foreach ( $_FILES as $key=>$file ) {
					for ( $i=0; $i < count( $file['name'] ); $i++ ) {
						$file_extension = pathinfo( $file['name'][$i], PATHINFO_EXTENSION );

						if ( in_array( strtolower( $file_extension ), $not_allowed_extensions ) ) {
							wp_die();
						}

                        $upload = wp_upload_dir();
                        $upload_dir = $upload['basedir'];
                        $upload_dir = $upload_dir . '/piotnetforms/files';
						$filename_goc = str_replace( '.' . $file_extension, '', $file['name'][$i] );
						$filename = $filename_goc . '-' . uniqid() . '.' . $file_extension;
						$filename = wp_unique_filename( $upload_dir, $filename );
						$filename = apply_filters( 'piotnetforms/form_builder/upload_dir/file_name', $filename );
						$new_file = trailingslashit( $upload_dir ) . $filename;

						if ( is_dir( $upload_dir ) && is_writable( $upload_dir ) ) {
							$move_new_file = @ move_uploaded_file( $file['tmp_name'][$i], $new_file );
							if ( false !== $move_new_file ) {
								// Set correct file permissions.
								$perms = 0644;
								@ chmod( $new_file, $perms );

								$file_url = $upload['baseurl'] . '/piotnetforms/files/' . $filename;
								foreach ( $fields as $key_field=>$field ) {
									if ( $key == $field['name'] ) {
										if ( $fields[$key_field]['attach-files'] == 1 ) {
											$attachment[] = WP_CONTENT_DIR . '/uploads/piotnetforms/files/' . $filename;
                                            $fields = piotnetforms_set_file_upload_field($fields, $key_field, $file, $i, $field, $file_url);
										} else {
                                            $fields = piotnetforms_set_file_upload_field($fields, $key_field, $file, $i, $field, $file_url);
										}
									}
								}
							}
						}
					}
				}
			}

			if ( !empty( $form['settings']['woocommerce_add_to_cart_price'] ) ) {
				if ( strpos( $_POST['product_id'], 'field id' ) !== false ) {
					$product_id = intval( piotnetforms_get_field_value_woocommerce_checkout( str_replace( '\"', '"', $_POST['product_id'] ), $fields ) );
				} else {
					$product_id = intval( $_POST['product_id'] );
				}

				$cart_item_data = [];
				$cart_item_data['fields'] = [];

				$fields_cart = $fields;

				if ( !empty( $form['settings']['woocommerce_add_to_cart_custom_order_item_meta_enable'] ) ) {
					$fields_cart = [];
					foreach ( $form['settings']['woocommerce_add_to_cart_custom_order_item_list'] as $item ) {
						if ( !empty( $item['woocommerce_add_to_cart_custom_order_item_field_shortcode'] ) ) {
							foreach ( $fields as $key_field=>$field ) {
								if ( strpos( $item['woocommerce_add_to_cart_custom_order_item_field_shortcode'], '[repeater id' ) !== false ) { // fix alert ]
									if ( $fields[$key_field]['repeater_id_one'] == get_field_name_shortcode_woocommerce_checkout_piotnetforms( $item['woocommerce_add_to_cart_custom_order_item_field_shortcode'] ) ) {
										if ( !isset( $fields_cart[$fields[$key_field]['repeater_id_one']] ) ) {
											$fields_cart[$fields[$key_field]['repeater_id_one']] = [
												'label' => $fields[$key_field]['repeater_label'],
												'name' => $fields[$key_field]['repeater_id_one'],
												'value' => str_replace( '\n', '<br>', piotnetforms_get_field_value_woocommerce_checkout( '[repeater id="' . $fields[$key_field]['repeater_id_one'] . '"]', $fields, $payment_status, $payment_id ) ),
											];
										}
									}
								} else {
									if ( $fields[$key_field]['name'] == get_field_name_shortcode_woocommerce_checkout_piotnetforms( $item['woocommerce_add_to_cart_custom_order_item_field_shortcode'] ) ) {
										if ( empty( $item['woocommerce_add_to_cart_custom_order_item_remove_if_field_empty'] ) ) {
                                            $fields_cart[] = piotnetforms_get_order_item_meta($field);
										} else {
											if ( !empty( $field['value'] ) ) {
                                                $fields_cart[] = piotnetforms_get_order_item_meta($field);
											}
										}
									}
								}
							}
						}
					}
				}

				foreach ( $fields as $key_field=>$field ) {
					if ( $fields[$key_field]['name'] == get_field_name_shortcode_woocommerce_checkout_piotnetforms( $form['settings']['woocommerce_add_to_cart_price'] ) ) {
						if ( isset( $fields[$key_field]['calculation_results'] ) ) {
							$cart_item_data['piotnetforms_custom_price'] = $fields[$key_field]['calculation_results'];
						} else {
							$cart_item_data['piotnetforms_custom_price'] = $fields[$key_field]['value'];
						}
					}
				}

				$piotnetforms_booking = [];

				foreach ( $fields_cart as $key_field=>$field ) {
					$field_value = $fields_cart[$key_field]['value'];
					if ( isset( $fields_cart[$key_field]['value_label'] ) ) {
						$field_value = $fields_cart[$key_field]['value_label'];
					}

					if ( strpos( $fields_cart[$key_field]['name'], 'piotnetforms-end-repeater' ) === false ) {
						$cart_item_data['fields'][] = [
							'label' => !empty($fields_cart[$key_field]['label']) ? $fields_cart[$key_field]['label'] : $fields_cart[$key_field]['name'],
							'name' => $fields_cart[$key_field]['name'],
							'value' => $field_value,
						];
					}


					if ( !empty( $form['settings']['booking_enable'] ) ) {
						if ( $fields_cart[$key_field]['name'] == get_field_name_shortcode_woocommerce_checkout_piotnetforms( $form['settings']['booking_shortcode'] ) ) {
							if ( !empty( $fields_cart[$key_field]['booking'] ) ) {
								$booking = $fields_cart[$key_field]['booking'];
								foreach ( $booking as $booking_key => $booking_item ) {
									$booking_item = json_decode( $booking_item, true );
									if ( !empty( $booking_item['piotnetforms_booking_date_field'] ) ) {
										$date = date( 'Y-m-d', strtotime( replace_email_woocommerce_checkout_piotnetforms( $booking_item['piotnetforms_booking_date_field'], $fields ) ) );
										$booking_item['piotnetforms_booking_date'] = $date;
									}
									$piotnetforms_booking = array_merge( $piotnetforms_booking, [ $booking_item ] );
								}
							}
						}
					}
				}

				if ( !empty( $piotnetforms_booking ) ) {
					$cart_item_data['fields'][] = [
						'label' => 'piotnetforms_booking',
						'name' => 'piotnetforms_booking',
						'value' => json_encode( $piotnetforms_booking ),
					];

					$cart_item_data['fields'][] = [
						'label' => 'piotnetforms_booking_fields',
						'name' => 'piotnetforms_booking_fields',
						'value' => json_encode( $fields ),
					];
				}

				if ( !empty( $form['settings']['piotnetforms_woocommerce_checkout_redirect'] ) ) {
					$redirect_url = piotnetforms_dynamic_tags( $form['settings']['piotnetforms_woocommerce_checkout_redirect'] );
					$cart_item_data['fields'][] = [
						'label' => 'piotnetforms_woocommerce_checkout_redirect',
						'name' => 'piotnetforms_woocommerce_checkout_redirect',
						'value' => $redirect_url,
					];
				}

				$cart_item_data['fields'] = array_unique( $cart_item_data['fields'], SORT_REGULAR );

				global $woocommerce;

				//$woocommerce->cart->empty_cart();

				$product_cart_id = $woocommerce->cart->generate_cart_id( $product_id, 0, [], $cart_item_data );
				$cart_item_key = $woocommerce->cart->find_product_in_cart( $product_cart_id );

				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $product_id == $cart_item['product_id'] ) {
						$woocommerce->cart->remove_cart_item( $cart_item['key'] );
					}
				}

                if(!empty($form['settings']['woocommerce_quantity_option']) && !empty($form['settings']['woocommerce_quantity'])){
                    $quantity =  piotnetforms_get_field_value_woocommerce_checkout($form['settings']['woocommerce_quantity'], $fields);
                    $quantity = is_numeric($quantity) ? $quantity : 1;
                }else{
                    $quantity = 1;
                }
				$woocommerce->cart->add_to_cart( $product_id, $quantity, 0, [], $cart_item_data );

				echo '1';
			}
		}

		wp_die();
	}
    function piotnetforms_get_order_item_meta($field){
        if(!empty($field['type']) && $field['type'] == 'file'){
            $value = '';
            foreach($field['file_name'] as $key => $name){
                $value .= '<a rel="nofollow" href="' . $field['new_name'][$key] . '">'.$name.'</a>,&nbsp;';
            }
            $field['value'] = rtrim($value, ',&nbsp;');
        }else{
            if(!empty($field['value'])){
                $values = explode(',', $field['value']);
                if(wp_http_validate_url($values[0]) && file_is_valid_image($values[0])){
                    $url = '';
                    foreach($values as $image_url){
                        $url .= '<a rel="nofollow" target="_blank" href="' . $image_url . '">'.basename($image_url).'</a>,&nbsp;';
                    };
                    $field['value'] = rtrim($url, ',&nbsp;');
                }
            }
        }
        return $field;
    }
    function piotnetforms_set_file_upload_field($fields, $key_field, $file, $i, $field, $file_url){
        if ( $fields[$key_field]['value'] == '' && in_array( $file['name'][$i], $field['file_name'] ) ) {
            $fields[$key_field]['value'] = $file_url;
            $fields[$key_field]['new_name'] = [$file_url];
        } else {
            if ( in_array( $file['name'][$i], $field['file_name'] ) && $i != ( count( $file['name'] ) - 1 ) ) {
                $fields[$key_field]['value'] .= ', ' . $file_url;
            }
            array_push($fields[$key_field]['new_name'], $file_url);
        }
        return $fields;
    }
