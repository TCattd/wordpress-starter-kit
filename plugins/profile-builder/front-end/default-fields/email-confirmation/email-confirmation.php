<?php
/* handle field output */
function wppb_email_confirmation_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Email Confirmation' ) {
        $item_title = apply_filters( 'wppb_' .$form_location.'_email_confirmation_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location == 'edit_profile' )
            $input_value = get_the_author_meta( 'user_email', $user_id );
        else
            $input_value = '';

        $input_value = ( isset( $request_data['wppb_email_confirmation'] ) ? esc_attr( $request_data['wppb_email_confirmation'] ) : $input_value );

        $error_mark = (($field['required'] == 'Yes') ? '<span class="wppb-required" title="' . wppb_required_field_error($field["field-title"]) . '">*</span>' : '');
        if (array_key_exists($field['id'], $field_check_errors))
            $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error($field["field-title"]) . '"/>';

        $output = '
		    <label for="wppb_email_confirmation">'.$item_title.$error_mark.'</label>
			<input class="extra_field_email_confirmation" name="wppb_email_confirmation" type="email" id="wppb_email_confirmation" value="'. esc_attr( wp_unslash( $input_value ) ) .'" '. $extra_attr .'/>';
        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        return apply_filters( 'wppb_'.$form_location.'_email_confirmation_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_email-confirmation', 'wppb_email_confirmation_handler', 10, 6 );


/* handle field validation */
function wppb_check_email_confirmation_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'Email Confirmation' ) {
        if ((isset($request_data['wppb_email_confirmation']) && (trim($request_data['wppb_email_confirmation']) == '')) && ($field['required'] == 'Yes'))
            return wppb_required_field_error($field["field-title"]);

        if ( (isset($request_data['wppb_email_confirmation'])) && ($field['required'] == 'Yes') && (strcasecmp($request_data['email'], $request_data['wppb_email_confirmation']) != 0) ) {
            return __('The email confirmation does not match your email address.', 'profile-builder');
        }
    }
    return $message;
}
add_filter( 'wppb_check_form_field_email-confirmation', 'wppb_check_email_confirmation_value', 10, 4 );


//Remove Email Confirmation field from UserListing merge tags (available Meta and Sort Variables list)
function wppb_remove_email_confirmation_from_userlisting($manage_fields){
    foreach ($manage_fields as $key => $value){
        if ($value['field'] == 'Email Confirmation') unset($manage_fields[$key]);
    }
    return array_values($manage_fields);
}
add_filter('wppb_userlisting_merge_tags', 'wppb_remove_email_confirmation_from_userlisting');