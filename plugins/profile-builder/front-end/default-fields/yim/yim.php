<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_yim_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_yim_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	$input_value = '';
	if( $form_location == 'edit_profile' )
		$input_value = get_the_author_meta( 'yim', $user_id );
	
	if ( trim( $input_value ) == '' )
		$input_value = $field['default-value'];
		
	$input_value = ( isset( $request_data['yim'] ) ? trim( $request_data['yim'] ) : $input_value );
	
	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="yim">'.$item_title.$error_mark.'</label>
			<input name="yim" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" class="text-input default_field_yim '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" id="yim" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';

	}
		
	return apply_filters( 'wppb_'.$form_location.'_yim', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-yahoo-im', 'wppb_yim_handler', 10, 6 );


/* handle field validation */
function wppb_check_yim_value( $message, $field, $request_data, $form_location ){
    if( $field['required'] == 'Yes' ){
        if( ( isset( $request_data['yim'] ) && ( trim( $request_data['yim'] ) == '' ) ) || !isset( $request_data['yim'] ) ){
            return wppb_required_field_error($field["field-title"]);
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-yahoo-im', 'wppb_check_yim_value', 10, 4 );

/* handle field save */
function wppb_userdata_add_yim( $userdata, $global_request ){
	if ( isset( $global_request['yim'] ) )
		$userdata['yim'] = sanitize_text_field( trim( $global_request['yim'] ) );
	
	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_yim', 10, 2 );