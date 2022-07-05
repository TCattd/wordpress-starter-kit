<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_last_name_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_lastname_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	$input_value = '';
	if( $form_location == 'edit_profile' )
		$input_value = get_the_author_meta( 'last_name', $user_id );
	
	if ( trim( $input_value ) == '' )
		$input_value = $field['default-value'];
		
	$input_value = ( isset( $request_data['last_name'] ) ? trim( $request_data['last_name'] ) : $input_value );
	
	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="last_name">'.$item_title.$error_mark.'</label>
			<input class="text-input default_field_lastname '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="last_name" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="last_name" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';
	}
		
	return apply_filters( 'wppb_'.$form_location.'_lastname', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-last-name', 'wppb_last_name_handler', 10, 6 );


/* handle field validation */
function wppb_check_last_name_value( $message, $field, $request_data, $form_location ){
    if( $field['required'] == 'Yes' ){
        if( ( isset( $request_data['last_name'] ) && ( trim( $request_data['last_name'] ) == '' ) ) || !isset( $request_data['last_name'] ) ){
            return wppb_required_field_error($field["field-title"]);
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-last-name', 'wppb_check_last_name_value', 10, 4 );


/* handle field save */
function wppb_userdata_add_last_name( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - Last Name', $form_args ) ) {
        if (isset($global_request['last_name']))
            $userdata['last_name'] = sanitize_text_field(trim($global_request['last_name']));
    }

	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_last_name', 10, 3 );