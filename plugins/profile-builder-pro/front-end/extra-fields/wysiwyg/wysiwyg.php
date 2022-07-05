<?php
/* handle field output */
function wppb_wysiwyg_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'WYSIWYG' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_wysiwyg_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $html_extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location != 'register' )
            $input_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-content'] );
        else
            $input_value = ( isset( $field['default-content'] ) ? trim( $field['default-content'] ) : '' );

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $input_value );

        if ( $form_location != 'back_end' ){
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>';

            ob_start();
            wp_editor( stripcslashes($input_value), $field['meta-name'], array('textarea_rows' => $field['row-count'], 'textarea_name' => $field['meta-name'], 'editor_class' => 'custom_field_wysiwyg' ));
            $out1 = ob_get_clean();

            $output .= $out1;
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

            if( !empty( $html_extra_attr ) ) {
                $split_extra_attr = explode(" ", $html_extra_attr);
                foreach ($split_extra_attr as $key => $value) {
                    if (!empty($value)) {
                        $attr_pair = explode("=", $value);
                        if ( count( $attr_pair ) === 2 ) {
                            $extra_attr [$attr_pair [0]] = trim($attr_pair [1], "\"");
                        }
                    }
                }
                $output .= "<script type='text/javascript'>
                               jQuery(document).ready(function(){";

                foreach ($extra_attr as $key => $value) {
                    $output .= "    jQuery('.custom_field_wysiwyg') . attr('" . $key . "', '" . $value . "');";
                }

                $output .= "    });
                            </script>";
            }

        }else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>';

            ob_start();
            wp_editor( $input_value, $field['meta-name'], array('textarea_rows' => $field['row-count'], 'textarea_name' => $field['meta-name'], 'editor_class' => 'custom_field_wysiwyg' ));
            $out1 = ob_get_clean();

            $output .= $out1;
							$output .= '<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters( 'wppb_'.$form_location.'_wysiwyg_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_wysiwyg', 'wppb_wysiwyg_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_wysiwyg', 'wppb_wysiwyg_handler', 10, 6 );


/* handle field save */
function wppb_save_wysiwyg_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'WYSIWYG' ){
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
            update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_wysiwyg_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_wysiwyg_value', 10, 4 );


/* handle field validation */
function wppb_check_wysiwyg_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'WYSIWYG' ){
        if( $field['required'] == 'Yes' ){
            if( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_wysiwyg', 'wppb_check_wysiwyg_value', 10, 4 );