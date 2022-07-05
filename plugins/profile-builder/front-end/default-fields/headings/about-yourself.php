<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wppb_default_about_yourself_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	if ( $field['field'] == 'Default - About Yourself (Heading)' ){
		$item_title = apply_filters( 'wppb_'.$form_location.'_default_heading_about_yourself_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
		$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );

        $output = '<h4>'.$item_title.'</h4><span class="wppb-description-delimiter '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'">'.$item_description.'</span>';
		
		return apply_filters( 'wppb_'.$form_location.'_default_heading_about_yourself_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
	}
}
add_filter( 'wppb_output_form_field_default-about-yourself-heading', 'wppb_default_about_yourself_handler', 10, 6 );