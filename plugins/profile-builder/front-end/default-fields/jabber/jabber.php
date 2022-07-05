<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_jabber_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_jabber_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
	$input_value = '';
	if( $form_location == 'edit_profile' )
		$input_value = get_the_author_meta( 'jabber', $user_id );
	
	if ( trim( $input_value ) == '' )
		$input_value = $field['default-value'];
		
	$input_value = ( isset( $request_data['jabber'] ) ? trim( $request_data['jabber'] ) : $input_value );
	
	if ( $form_location != 'back_end' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        $output = '
			<label for="jabber">'.$item_title.$error_mark.'</label>
			<input name="jabber" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" class="text-input default_field_jabber '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" id="jabber" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';

	}
		
	return apply_filters( 'wppb_'.$form_location.'_jabber', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-jabber-google-talk', 'wppb_jabber_handler', 10, 6 );


/* handle field validation */
function wppb_check_jabber_value( $message, $field, $request_data, $form_location ){
    if( $field['required'] == 'Yes' ){
        if( ( isset( $request_data['jabber'] ) && ( trim( $request_data['jabber'] ) == '' ) ) || !isset( $request_data['jabber'] ) ){
            return wppb_required_field_error($field["field-title"]);
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-jabber-google-talk', 'wppb_check_jabber_value', 10, 4 );

/* handle field save */
function wppb_userdata_add_jabber( $userdata, $global_request ){
	if ( isset( $global_request['jabber'] ) )
		$userdata['jabber'] = sanitize_text_field( trim( $global_request['jabber'] ) );
	
	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_jabber', 10, 2 );