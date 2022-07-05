<?php
/* handle field output */
function wppb_currency_select_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){

    if ( $field['field'] == 'Select (Currency)' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_currency_select_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $currencies = wppb_get_currencies( $form_location );
        array_unshift( $currencies, '' );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );


        if( $form_location != 'register' )
            $input_value = ( ( wppb_user_meta_exists( $user_id, $field['meta-name'] ) != null ) ? stripslashes( get_user_meta( $user_id, $field['meta-name'], true ) ) : $field['default-option-currency'] );
        else
            $input_value = ( ! empty( $field['default-option-currency'] ) ? trim( $field['default-option-currency'] ) : '' );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );


        if ( $form_location != 'back_end' ){
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<select name="'.$field['meta-name'].'" id="'.$field['meta-name'].'" class="custom_field_currency_select '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" '. $extra_attr .'>';

			$extra_select_option = apply_filters( 'wppb_extra_select_option', '', $field, $item_title );
			if( ! empty( $extra_select_option ) ) {
				$output .= $extra_select_option;
				$currencies = array_filter( $currencies );
			}

            // Check to see if the currency symbol should be displayed or not
            $show_currency_symbol = ( !empty( $field['show-currency-symbol'] ) && $field['show-currency-symbol'] == 'Yes' ? true : false );

            foreach( $currencies as $iso => $currency_name ){
                $output .= '<option value="' . ( empty($iso) ? '' : $iso ) . '"';

                if ( $input_value === $iso )
                    $output .= ' selected';

                if( $show_currency_symbol )
                    $currency_symbol = wppb_get_currency_symbol( $iso );

                $currency_name = !empty( $currency_symbol ) ? $currency_name . ' (' . $currency_symbol . ')' : $currency_name;

                $output .= '>'. $currency_name .'</option>';
            }

            $output .= '
				</select>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        }else{
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<select name="'.$field['meta-name'].'" class="custom_field_currency_select" id="'.$field['meta-name'].'" '. $extra_attr .'>';

            // Check to see if the currency symbol should be displayed or not
            $show_currency_symbol = ( !empty( $field['show-currency-symbol'] ) && $field['show-currency-symbol'] == 'Yes' ? true : false );

            foreach( $currencies as $iso => $currency_name ){
                $output .= '<option value="'.$iso.'"';

                if ( $input_value === $iso )
                    $output .= ' selected';

                if( $show_currency_symbol )
                    $currency_symbol = wppb_get_currency_symbol( $iso );

                $currency_name = !empty( $currency_symbol ) ? $currency_name . ' (' . $currency_symbol . ')' : $currency_name;

                $output .= '>'.$currency_name.'</option>';
            }

            $output .= '</select>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters( 'wppb_'.$form_location.'_currency_select_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_select-currency', 'wppb_currency_select_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_select-currency', 'wppb_currency_select_handler', 10, 6 );


/* handle field save */
function wppb_save_currency_select_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'Select (Currency)' ){
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
            update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_currency_select_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_currency_select_value', 10, 4 );


/* handle field validation */
function wppb_check_currency_select_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'Select (Currency)' ){
        if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_select-currency', 'wppb_check_currency_select_value', 10, 4 );