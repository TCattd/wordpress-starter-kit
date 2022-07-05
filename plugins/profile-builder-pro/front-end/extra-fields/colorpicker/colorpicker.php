<?php
/* handle field output */
function wppb_colorpicker_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Colorpicker' ){

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'profile-builder-colorpicker', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/colorpicker/colorpicker-style.css', false, PROFILE_BUILDER_VERSION );
		wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
		wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris', 'wp-i18n' ), false, true );
		wp_enqueue_script( 'wppb-colorpicker-script', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/colorpicker/script-colorpicker.js', array( 'wp-color-picker' ), PROFILE_BUILDER_VERSION, true );

		if( ! is_admin() ) {
			$is_frontend = array( 'isFrontend'	=>	1 );
		} else {
			$is_frontend = array( 'isFrontend'	=>	0 );
		}
		wp_localize_script( 'wppb-colorpicker-script', 'wppb_colorpicker_data', $is_frontend );

		$item_title = apply_filters( 'wppb_'.$form_location.'_colorpicker_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

		$field['default-value'] = apply_filters( 'wppb_colorpicker_default_color_'. $field['meta-name'], '' );

		if( $form_location != 'register' )
			$input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-value'] );
		else
			$input_value = ( !empty( $field['default-value'] ) ? trim( $field['default-value'] ) : '' );

		$input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

		if ( $form_location != 'back_end' ){
			$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

			if ( array_key_exists( $field['id'], $field_check_errors ) )
				$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

			$output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<input name="'.$field['meta-name'].'" class="custom_field_colorpicker '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" type="text" value="'. esc_attr( wp_unslash( $input_value ) ) .'" data-default-color="'. $field['default-value'] . '" '. $extra_attr .' />
				<span class="wppb-description-delimiter">'.$item_description.'</span>';

		}else{
			$item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
			$output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<input size="45" id="'.$field['meta-name'].'" class="custom_field_colorpicker" name="'.$field['meta-name'].'" type="text" value="'. esc_attr( $input_value ) .' " '. $extra_attr .'/>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';

		}

		return apply_filters( 'wppb_'.$form_location.'_colorpicker_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
	}
}
add_filter( 'wppb_output_form_field_colorpicker', 'wppb_colorpicker_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_colorpicker', 'wppb_colorpicker_handler', 10, 6 );


/* handle field save */
function wppb_save_colorpicker_value( $field, $user_id, $request_data, $form_location ){
	if( $field['field'] == 'Colorpicker' ){
		if ( isset( $request_data[str_replace( ' ', '_', $field['meta-name'])] ) )
			update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
	}
}
add_action( 'wppb_save_form_field', 'wppb_save_colorpicker_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_colorpicker_value', 10, 4 );


/* handle field validation */
function wppb_check_colorpicker_value( $message, $field, $request_data, $form_location ){
	if( $field['field'] == 'Colorpicker' ){
		if( $field['required'] == 'Yes' ){
			if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
				return wppb_required_field_error($field["field-title"]);
			}
		}
	}

	return $message;
}
add_filter( 'wppb_check_form_field_colorpicker', 'wppb_check_colorpicker_value', 10, 4 );