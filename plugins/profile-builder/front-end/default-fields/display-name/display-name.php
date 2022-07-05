<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_display_name_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){	
	$item_title = apply_filters( 'wppb_'.$form_location.'_display-name_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
	$item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );

	if ( $form_location == 'edit_profile' ){
		$error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );
					
		if ( array_key_exists( $field['id'], $field_check_errors ) )
			$error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

        /*
         * Create the options for the display_name drop-down
         * They are created same as in user-edit.php of the WordPress core
         */
        $user_data = get_userdata( $user_id );
        $public_display = array();
        $public_display['display_nickname']  = $user_data->nickname;
        $public_display['display_username']  = $user_data->user_login;

        if ( !empty($user_data->first_name) )
            $public_display['display_firstname'] = $user_data->first_name;

        if ( !empty($user_data->last_name) )
            $public_display['display_lastname'] = $user_data->last_name;

        if ( !empty($user_data->first_name) && !empty($user_data->last_name) ) {
            $public_display['display_firstlast'] = $user_data->first_name . ' ' . $user_data->last_name;
            $public_display['display_lastfirst'] = $user_data->last_name . ' ' . $user_data->first_name;
        }

        if ( !in_array( $user_data->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
            $public_display = array( 'display_displayname' => $user_data->display_name ) + $public_display;

        $public_display = array_map( 'trim', $public_display );
        $public_display = array_unique( $public_display );

        $public_display = apply_filters( 'wppb_display_name_options', $public_display );

        $output = '<label for="display_name">'.$item_title.$error_mark.'</label>';
        $output .= '<select class="default_field_display-name '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" name="display_name" id="display-name">';

            foreach( $public_display as $display_name_option ) {
                $output .= '<option ' . selected( $user_data->display_name, $display_name_option, false ) . '>' . $display_name_option . '</option>';
            }

        $output .= '</select>';

        if( !empty( $item_description ) )
            $output .= '<span class="wppb-description-delimiter">'. $item_description .'</span>';

	}
		
	return apply_filters( 'wppb_'.$form_location.'_display-name', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
}
add_filter( 'wppb_output_form_field_default-display-name-publicly-as', 'wppb_display_name_handler', 10, 6 );


/* handle field validation */
function wppb_check_display_name_value( $message, $field, $request_data, $form_location ){
    if( $form_location != 'register' ){
        if ($field['required'] == 'Yes') {
            if ((isset($request_data['display_name']) && (trim($request_data['display_name']) == '')) || !isset($request_data['display_name'])) {
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_default-display-name-publicly-as', 'wppb_check_display_name_value', 10, 4 );


/* handle field save */
function wppb_userdata_add_display_name( $userdata, $global_request, $form_args ){
    if( wppb_field_exists_in_form( 'Default - Display name publicly as', $form_args ) ) {
        if (isset($global_request['display_name']))
            $userdata['display_name'] = trim(sanitize_text_field($global_request['display_name']));
    }

	return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_display_name', 10, 3 );