<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_description_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_description_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	$input_value = '';
	if( $form_location == 'edit_profile' ) {
		$profileuser = get_userdata( $user_id );
		$input_value =	$profileuser->description;
	}
	
	if ( trim( $input_value ) == '' && !empty( $field['default-content'] ) )
		$input_value = $field['default-content'];
		
	$input_value = ( isset( $request_data['description'] ) ? trim( $request_data['description'] ) : $input_value );

	$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

        $output = '
			<label for="description">'.$item_title.$error_mark.'</label>
			<textarea rows="'.$field['row-count'].'" name="description" maxlength="'. apply_filters( 'wppb_maximum_character_length', '', $field ) .'" class="default_field_description '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" id="description" wrap="virtual" '. $extra_attr .'>'. esc_textarea( wp_unslash( $input_value ) ).'</textarea>';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';

	}
		
	return apply_filters( 'wppb_'.$form_location.'_description', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-biographical-info', 'wppb_description_handler', 10, 6 );


/* handle field validation */
function wppb_check_description_value( $message, $field, $request_data, $form_location ){
    if( $field['required'] == 'Yes' ){
        if( ( isset( $request_data['description'] ) && ( trim( $request_data['description'] ) == '' ) ) || !isset( $request_data['description'] ) ){
            return wppb_required_field_error($field["field-title"]);
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-biographical-info', 'wppb_check_description_value', 10, 4 );

/* handle field save */
function wppb_userdata_add_description( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - Biographical Info', $form_args ) ) {
        if (isset($global_request['description'])) {
            $description = apply_filters('pre_user_description', trim($global_request['description']));
            $userdata['description'] = $description;
        }
    }
	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_description', 10, 3 );