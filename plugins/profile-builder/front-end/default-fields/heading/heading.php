<?php
/* handle field output */
function wppb_heading_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Heading' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_heading_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        if( isset( $field['heading-tag'] ) ) {
            $heading_tag = $field['heading-tag'];
        } else {
            $heading_tag = 'h4';
        }

		$heading_element1 = ( ( $form_location == 'back_end' ) ? '<h3>' : '<'. $heading_tag .' class="extra_field_heading">' );
		$heading_element2 = ( ( $form_location == 'back_end' ) ? '</h3>' : '</'. $heading_tag .'>' );

        $output = $heading_element1 . $item_title . $heading_element2 . '<span class="wppb-description-delimiter">'.$item_description.'</span>';

		return apply_filters( 'wppb_'.$form_location.'_heading_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
	}
}
add_filter( 'wppb_output_form_field_heading', 'wppb_heading_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_heading', 'wppb_heading_handler', 10, 6 );