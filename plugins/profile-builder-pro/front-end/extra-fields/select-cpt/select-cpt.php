<?php
/* handle CPT Select output */
function wppb_select_cpt_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Select (CPT)' ){

        /* turn it in a select2 */
        wp_enqueue_script( 'wppb_select2_js', WPPB_PLUGIN_URL .'assets/js/select2/select2.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
        wp_enqueue_style( 'wppb_select2_css', WPPB_PLUGIN_URL .'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION );
        wp_enqueue_script( 'wppb-select-cpt-script', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/select-cpt/select-cpt.js', array('wppb_select2_js'), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb-select-cpt-style', WPPB_PAID_PLUGIN_URL.'front-end/extra-fields/select-cpt/style-front-end.css', array(), PROFILE_BUILDER_VERSION );

        $item_title = apply_filters( 'wppb_'.$form_location.'_cpt_select_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location != 'register' )
            $cpt_value = ( ( wppb_user_meta_exists ( $user_id, $field['meta-name'] ) != null ) ? get_user_meta( $user_id, $field['meta-name'], true ) : $field['default-option'] );
        else
            $cpt_value = ( isset( $field['default-option'] ) ? trim( $field['default-option'] ) : '' );

        $cpt_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ? trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) : $cpt_value );

        $args = apply_filters( 'wppb_cpt_select_args', array( 'post_type' => $field['cpt'], 'orderby' => 'menu_order title', 'order' => 'ASC', 'posts_per_page' => '200', 'post_status' => 'publish' ), $field );
        $wppb_cpt_query = new WP_Query($args);


        if ( $form_location != 'back_end' ){
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>
				<select name="'.$field['meta-name'].'" id="'.$field['meta-name'].'" class="custom_field_cpt_select '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" '. $extra_attr .'>';

            if( ! empty( $wppb_cpt_query->posts ) ) {
                $extra_select_option = apply_filters( 'wppb_extra_select_option', '', $field, $item_title );
                if( ! empty( $extra_select_option ) ) {
                    $output .= $extra_select_option;
                } else {
                    $output .= '<option value="">'. __( '...Choose', 'profile-builder' ) .'</option>';
                }

                foreach( $wppb_cpt_query->posts as $cpt ) {
                    if ( $cpt->post_title == '' )
                        $cpt->post_title = 'No title. ID: ' . $cpt->ID;

                    $output .= '<option value="'. esc_attr( $cpt->ID ) .'"  '. selected( $cpt->ID, $cpt_value, false ) .' >'. apply_filters( 'wppb_fields_cpt_select_label', esc_html( $cpt->post_title ), $cpt->ID) .'</option>';
                }
            }

            $output .= '</select>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        }else{
            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>
							<select class="custom_field_cpt_select" name="'.$field['meta-name'].'" id="'.$field['meta-name'].'"  '. $extra_attr .'/>';
                            if( !empty( $wppb_cpt_query->posts ) ){
                                $output .= apply_filters( 'wppb_cpt_select_field_first_option', '<option value="">'. __('...Choose', 'profile-builder') .'</option>', $field, $form_location );
                                foreach( $wppb_cpt_query->posts as $cpt ){
                                    if ( $cpt->post_title == '' )
                                        $cpt->post_title = 'No title. ID: ' . $cpt->ID;

                                    $output .= '<option value="'. esc_attr( $cpt->ID ) .'"  '. selected( $cpt->ID, $cpt_value, false ) .' >'. esc_html( $cpt->post_title ) .'</option>';
                                }
                            }

            $output .=      '</select>
							<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters( 'wppb_'.$form_location.'_cpt_select_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $cpt_value );
    }
}
add_filter( 'wppb_output_form_field_select-cpt', 'wppb_select_cpt_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_select-cpt', 'wppb_select_cpt_handler', 10, 6 );

/* handle field save */
function wppb_save_select_cpt_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'Select (CPT)' ){
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) )
            update_user_meta( $user_id, $field['meta-name'], $request_data[wppb_handle_meta_name( $field['meta-name'] )] );
    }
}
add_action( 'wppb_save_form_field', 'wppb_save_select_cpt_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_select_cpt_value', 10, 4 );

/* handle field validation */
function wppb_check_select_cpt_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'Select (CPT)' ){
        if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_select-cpt', 'wppb_check_select_cpt_value', 10, 4 );
