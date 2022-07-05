<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_website_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_website_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	$input_value = '';
	if( $form_location == 'edit_profile' )
		$input_value = get_the_author_meta( 'user_url', $user_id );
	
	if ( trim( $input_value ) == '' )
		$input_value = $field['default-value'];
		
	$input_value = ( isset( $request_data['website'] ) ? trim( $request_data['website'] ) : $input_value );
	
	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="website">'.$item_title.$error_mark.'</label>
			<input class="text-input default_field_website '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="website" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="website" value="'.esc_url( wp_unslash( $input_value ) ).'" '. $extra_attr .'/>';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';

	}
		
	return apply_filters( 'wppb_'.$form_location.'_website', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-website', 'wppb_website_handler', 10, 6 );


/* handle field validation */
function wppb_check_website_value( $message, $field, $request_data, $form_location ){
    if( $field['required'] == 'Yes' ){
        if( ( isset( $request_data['website'] ) && ( trim( $request_data['website'] ) == '' ) ) || !isset( $request_data['website'] ) ){
            return wppb_required_field_error($field["field-title"]);
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-website', 'wppb_check_website_value', 10, 4 );


/* handle field save */
function wppb_userdata_add_website( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - Website', $form_args ) ) {
        if (isset($global_request['website']))
            $userdata['user_url'] = esc_url_raw(trim($global_request['website']));
    }

	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_website', 10, 3 );