<?php
/* handle field output */
function wppb_timepicker_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){

    if ( $field['field'] == 'Timepicker' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_timepicker_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );


        if( $form_location != 'register' )
            $input_value = ( ( wppb_user_meta_exists( $user_id, $field['meta-name'] ) != null ) ? stripslashes( get_user_meta( $user_id, $field['meta-name'], true ) ) : '' );
        else
            $input_value = '';

        $input_value = ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && !empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )]['hours'] ) && !empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )]['minutes'] )  ? $request_data[wppb_handle_meta_name( $field['meta-name'] )] : $input_value );

        // Get the hour and minutes saved for the field
        $value_hours    = '';
        $value_minutes  = '';

        if( is_array( $input_value ) ) {
            $value_hours    = $input_value['hours'];
            $value_minutes  = $input_value['minutes'];
        } elseif( !empty( $input_value ) ) {
            $time = explode( ':', $input_value );

            $value_hours = $time[0];
            $value_minutes = $time[1];
        }

        // Set hours for time
        $hours = array();

        for( $i = 0; $i <= 23; $i++ )
            array_push( $hours, ( strlen( $i ) == 1 ? '0' . $i : $i ) );

        // Set minutes
        $minutes = array();
        for( $i = 0; $i <= 59; $i++ )
            array_push( $minutes, ( strlen( $i ) == 1 ? '0' . $i : $i ) );


        if ( $form_location != 'back_end' ) {
            $error_mark = (($field['required'] == 'Yes') ? '<span class="wppb-required" title="' . wppb_required_field_error($field["field-title"]) . '">*</span>' : '');

            if (array_key_exists($field['id'], $field_check_errors))
                $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error($field["field-title"]) . '"/>';

            // Label
            $output = '
				<label for="' . $field['meta-name'] . '-hour">' . $item_title . $error_mark . '</label>';


            // Add hour select
            $output .= '<select name="' . $field['meta-name'] . '[hours]" id="' . $field['meta-name'] . '-hour" class="custom_field_timepicker_hours ' . apply_filters('wppb_fields_extra_css_class', '', $field) . '" ' . $extra_attr . '>';

            foreach ($hours as $hour) {
                $output .= '<option value="' . $hour . '"';

                if ($value_hours == $hour)
                    $output .= ' selected';

                if( $field['time-format'] == '12' ) {
                    if ($hour > 12) {
                        $hour -= 12;
                        $hour = (strlen($hour) == 1 ? '0' . $hour : $hour) . ' pm';
                    } elseif( $hour == 12 )
                        $hour .= ' pm';
                    elseif( $hour == '00' )
                        $hour = '12 am';
                    else
                        $hour .= ' am';
                }

                $output .= '>' . $hour . '</option>';
            }

            $output .= '</select>';

            // Add divider
            $output .= '<span class="wppb-timepicker-separator">:</span>';

            // Add minutes select
            $output .= '<select name="' . $field['meta-name'] . '[minutes]" id="' . $field['meta-name'] . '-minutes" class="custom_field_timepicker_minutes ' . apply_filters('wppb_fields_extra_css_class', '', $field) . '" ' . $extra_attr . '>';

            foreach ($minutes as $minute) {
                $output .= '<option value="' . $minute . '"';

                if ($value_minutes == $minute)
                    $output .= ' selected';


                $output .= '>' . $minute . '</option>';
            }

            $output .= '</select>';

            // Add description
            if (!empty($item_description))
                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

        } else {
            $output = '
				<table class="form-table">
					<tr>
						<th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
						<td>';

                        // Add hours select
                        $output .= '<select name="'.$field['meta-name'].'[hours]" class="custom_field_timepicker_hours" id="'.$field['meta-name'].'-minutes" '. $extra_attr .'>';

                        foreach ($hours as $hour) {
                            $output .= '<option value="' . $hour . '"';

                            if ($value_hours == $hour)
                                $output .= ' selected';

                            if( $field['time-format'] == '12' ) {
                                if ($hour > 12) {
                                    $hour -= 12;
                                    $hour = (strlen($hour) == 1 ? '0' . $hour : $hour) . ' pm';
                                } elseif( $hour == 12 )
                                    $hour .= ' pm';
                                elseif( $hour == '00' )
                                    $hour = '12 am';
                                else
                                    $hour .= ' am';
                            }

                            $output .= '>' . $hour . '</option>';
                        }

                        $output .= '</select>';

                        // Add divider
                        $output .= '<span class="wppb-timepicker-separator">:</span>';

                        // Add minutes select
                        $output .= '<select name="' . $field['meta-name'] . '[minutes]" class="custom_field_timepicker_minutes" id="' . $field['meta-name'] . '-minutes" ' . $extra_attr . '>';

                        foreach ($minutes as $minute) {
                            $output .= '<option value="' . $minute . '"';

                            if ($value_minutes == $minute)
                                $output .= ' selected';


                            $output .= '>' . $minute . '</option>';
                        }

                        $output .= '</select>';


							$output .= '<span class="description">'.$item_description.'</span>
						</td>
					</tr>
				</table>';
        }

        return apply_filters( 'wppb_'.$form_location.'_currency_select_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }

}
add_filter( 'wppb_output_form_field_timepicker', 'wppb_timepicker_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_timepicker', 'wppb_timepicker_handler', 10, 6 );


/*
 * Handle field save
 *
 * We're going to format the time in H:i and save it like this in the db
 *
 */
function wppb_save_timepicker_value( $field, $user_id, $request_data, $form_location ){

    if( $field['field'] == 'Timepicker' ){

        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {

            $time = $request_data[wppb_handle_meta_name( $field['meta-name'] )];

            if( !empty( $time['hours'] ) && !empty( $time['minutes'] ) ) {
                update_user_meta( $user_id, $field['meta-name'], $time['hours'] . ':' . $time['minutes'] );
            }

        }

    }
}
add_action( 'wppb_save_form_field', 'wppb_save_timepicker_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_timepicker_value', 10, 4 );


/* handle field validation */
function wppb_check_timepicker_value( $message, $field, $request_data, $form_location ){
    if( $field['field'] == 'Timepicker' ){
        if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )]['hours'] ) || empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )]['minutes'] ) ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_timepicker', 'wppb_check_timepicker_value', 10, 4 );