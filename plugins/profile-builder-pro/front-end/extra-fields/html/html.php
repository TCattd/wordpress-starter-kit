<?php
/* handle field output */
function wppb_html_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ) {
    if ( $field['field'] == 'HTML' ) {
        $item_title = apply_filters( 'wppb_'.$form_location.'_html_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'. $field['id'] .'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'. $field['id'] .'_description_translation', $field['description'], true );
        $item_content = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_' . $field['id'] . '_html_content_translation', $field['html-content'] );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location != 'back_end' ) {
            $output = '
				<label>'. $item_title .'</label>
				<span class="custom_field_html '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" '. $extra_attr .'>'. do_shortcode( $item_content ) .'</span>';

            if( ! empty( $item_description ) ) {
                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';
            }
        } else {
            $output = '
				<table class="form-table">
					<tr>
						<th><label>'. $item_title .'</label></th>
						<td>
							<span class="custom_field_html" '. $extra_attr .'>'. do_shortcode( $item_content ) .'</span>
							<br><span class="description">'. $item_description .'</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters( 'wppb_'. $form_location .'_html_custom_field_'. $field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $item_content );
    }
}
add_filter( 'wppb_output_form_field_html', 'wppb_html_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_html', 'wppb_html_handler', 10, 6 );