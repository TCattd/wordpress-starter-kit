<?php
/* handle field output */
function wppb_map_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Map' ){
        $map_api_key = wppb_get_map_api_key( $field );
        // Enqueue scripts only if the API key is present
        if( !empty( $map_api_key ) ) {
            // Enqueue needed scripts
            wp_enqueue_script( 'wppb-google-maps-api-script', 'https://maps.googleapis.com/maps/api/js?key=' . $map_api_key . '&libraries=places', array('jquery'), PROFILE_BUILDER_VERSION, true );
            wp_enqueue_script( 'wppb-google-maps-script', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/map/map.js', array('jquery'), PROFILE_BUILDER_VERSION, true );

            $map_data_vars_array['map_marker_text_remove'] = __( "Remove Marker", 'profile-builder' );
            wp_localize_script( 'wppb-google-maps-script', 'wppb_maps_data', $map_data_vars_array );

            if( $form_location == 'back_end' )
                wp_enqueue_style( 'wppb-google-maps-style', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/map/map.css', array(), PROFILE_BUILDER_VERSION );
        }

        $item_title = apply_filters( 'wppb_'.$form_location.'_map_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        // Get saved map markers
        $user_map_markers = ( $form_location != 'register' ? wppb_get_user_map_markers( $user_id, $field['meta-name'] ) : array() );
        $map_markers      = ( !empty( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) ? $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] : $user_map_markers );


        if ( $form_location != 'back_end' ){

            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '<label for="'.$field['meta-name'].'">'.$item_title.$error_mark.'</label>';

            if( !empty( $map_api_key ) ) {

                // Map container that will be initialized through JS
                if ( defined( 'WPPBFV_IN_PLUGIN_DIR' ) && isset( $field[ 'visibility' ] ) && $field[ 'visibility' ] == 'user_locked' && !current_user_can( 'manage_options' ) )
                    $output .= wppb_get_map_output( $field, array( 'markers' => $map_markers, 'editable' => false, 'extra_attr' => $extra_attr, 'user_id' => $user_id ) );
                else
                    $output .= wppb_get_map_output( $field, array( 'markers' => $map_markers, 'extra_attr' => $extra_attr, 'user_id' => $user_id ) );

                if( !empty( $item_description ) )
                    $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

            } else {

                if( current_user_can( 'manage_options' ) )
                    $output .= '<div class="wppb-warning">' . __( 'Please add the Google Maps API key for this field.', 'profile-builder' ) . '</div>';
            }

        } else {

            $item_title = ( ( $field['required'] == 'Yes' ) ? $item_title .' <span class="description">('. __( 'required', 'profile-builder' ) .')</span>' : $item_title );
            $output = '
            <table class="form-table">
                <tr>
                    <th><label for="'.$field['meta-name'].'">'.$item_title.'</label></th>
                    <td>';

            if( !empty( $map_api_key ) ) {
                // Map container that will be initialized through JS
                $output .= '<div style="max-width: 600px;">';
                if ( defined( 'WPPBFV_IN_PLUGIN_DIR' ) && isset( $field[ 'visibility' ] ) && $field[ 'visibility' ] == 'user_locked' && !current_user_can( 'manage_options' ) )
                    $output .= wppb_get_map_output( $field, array( 'markers' => $map_markers, 'editable' => false, 'extra_attr' => $extra_attr, 'user_id' => $user_id ) );
                else
                    $output .= wppb_get_map_output( $field, array( 'markers' => $map_markers, 'extra_attr' => $extra_attr, 'user_id' => $user_id ) );
                $output .= '</div>';

                $output .= '<span class="description">' . $item_description . '</span>';

            } else {

                if( current_user_can( 'manage_options' ) )
                    $output .= '<div class="wppb-warning">' . __( 'Please add the Google Maps API key for this field.', 'profile-builder' ) . '</div>';
            }

                $output .= '</td>
                </tr>
            </table>';
        }

        return apply_filters( 'wppb_'.$form_location.'_map_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $map_markers );

    }
}
add_filter( 'wppb_output_form_field_map', 'wppb_map_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_map', 'wppb_map_handler', 10, 6 );

/* handle field save */
function wppb_save_map_value( $field, $user_id, $request_data, $form_location ){

    if( $field['field'] == 'Map' && $field['meta-name'] == wppb_handle_meta_name( $field['meta-name'] ) ){

        // Remove all existing markers
        wppb_delete_user_map_markers( $user_id, wppb_handle_meta_name( $field['meta-name'] ) );

        // Add markers if they exist
        if ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {

            // Add new markers
            if( is_array( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) {
                foreach( $request_data[wppb_handle_meta_name( $field['meta-name'] )] as $key => $position )
                    update_user_meta( $user_id, $field['meta-name'] . '_' . $key, $position );
            }

        }

    }

}
add_action( 'wppb_save_form_field', 'wppb_save_map_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_save_map_value', 10, 4 );


/* handle field validation */
function wppb_check_map_value( $message, $field, $request_data, $form_location ){

    if( $field['field'] == 'Map' ){

        // Validate the coordinates
        $error = false;

        if( !empty( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] ) ) {
            foreach( $request_data[ wppb_handle_meta_name( $field['meta-name'] ) ] as $position ) {

                $position = explode( ',', $position );

                // We should only have a latitude and longitude
                if( count( $position ) != 2 )
                    $error = true;

                if( strpos( $position[0], '.' ) === false || strpos( $position[1], '.' ) === false )
                    $error = true;

            }
        }

        if( $error )
            return __( 'Something went wrong. Please try again.', 'profile-builder' );

        // Check to see if required
        if( $field['required'] == 'Yes' ){
            if ( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( empty( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }
    }

    return $message;
}
add_filter( 'wppb_check_form_field_map', 'wppb_check_map_value', 10, 4 );

function wppb_get_map_api_key( $field ){
    if( ! empty ( $field['map-api-key'] ) ){
        return $field['map-api-key'];
    }else{
        $manage_fields = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields', 'not_set' ), array( 'context' => 'map_api_key' ) );
        if ( $manage_fields != 'not_set' ){
            foreach( $manage_fields as $field ){
                if( ! empty ( $field['map-api-key'] ) ) {
                    return $field['map-api-key'];
                }
            }
        }
    }

    return '';
}
