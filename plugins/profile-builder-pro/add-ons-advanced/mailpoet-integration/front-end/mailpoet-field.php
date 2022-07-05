<?php
/* handle field output */
function wppb_in_mailpoet_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){

    if ( $field['field'] == 'MailPoet Subscribe' ){
        $item_title = apply_filters( 'wppb_'.$form_location.'_mailpoet_custom_field_'.esc_attr( $field['id'] ).'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'. esc_attr( $field['id'] ).'_title_translation', esc_attr( $field['field-title'] ) ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'. esc_attr( $field['id'] ).'_description_translation', wp_kses_post( $field['description'] ) );

        $input_value = ( !empty( $field['mailpoet-lists'] ) ? $field['mailpoet-lists']  : '' );

        if ( $form_location != 'back_end' && wppb_in_mailpoet_installed() ) {

            $checked = '';

            /* If we're on edit profile check if the user e-mail is subscribed in any of the mailpoet lists associated with this field */
            if( $form_location == 'edit_profile' ) {

                if( wppb_in_mpi_check_user_subscription( $field['mailpoet-lists'],  $user_id ) ) {
                    $checked = 'checked="checked"';
                }

            }

            // Check the checkbox if there is a value
            if( $form_location == 'register' && ( (isset( $request_data['custom_field_mailpoet_subscribe_' . $field['id']] ) && !empty( $request_data['custom_field_mailpoet_subscribe_' . $field['id']] )) || !empty( $field['mailpoet-default-checked'] ) ) )
                $checked = 'checked="checked"';

            $output = '<label for="custom_field_mailpoet_subscribe_' . esc_attr( $field['id'] ) . '">';

            $output .= '<input name="custom_field_mailpoet_subscribe_' . esc_attr( $field['id'] ) . '" id="custom_field_mailpoet_subscribe_' . esc_attr( $field['id'] ) . '" class="extra_field_mailpoet" type="checkbox" value="' . esc_attr( $input_value ) . '" ' . $checked . ' />';

            $output .= $item_title . '</label>';

            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">' . $item_description . '</span>';

        }

        return apply_filters( 'wppb_'.$form_location.'_mailpoet_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value );
    }
}
add_filter( 'wppb_output_form_field_mailpoet-subscribe', 'wppb_in_mailpoet_handler', 10, 6 );
add_filter( 'wppb_admin_output_form_field_mailpoet-subscribe', 'wppb_in_mailpoet_handler', 10, 6 );


/* handle field save */
function wppb_in_save_mailpoet_value( $field, $user_id, $request_data, $form_location ){
    if( $form_location == 'back_end' )
        return;

    if( $field['field'] == 'MailPoet Subscribe' ){

        // Get value from the subscribe checkbox
        if( isset( $request_data['custom_field_mailpoet_subscribe_' . $field['id']] ) && !empty( $request_data['custom_field_mailpoet_subscribe_' . $field['id']] ) ) {

            $mailpoet_subscribe_list_id = sanitize_text_field( $request_data[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] );
            if(!empty($mailpoet_subscribe_list_id) && !wppb_in_mpi_check_user_subscription($mailpoet_subscribe_list_id,$user_id) ){
                wppb_in_mpi_add_subscriber($mailpoet_subscribe_list_id, $user_id);
            }

        } elseif( isset( $field['mailpoet-lists'] ) && !empty( $field['mailpoet-lists'] ) && $form_location == 'edit_profile' && wppb_in_mpi_check_user_subscription($field['mailpoet-lists'],$user_id) ) {
            // As we have the same situation for both when the field is in the form, but not checked, and when
            // it is not present, we want to unsubscribe the user only when the field is present
            if( isset( $field['mailpoet-hide-field'] ) && $field['mailpoet-hide-field'] == 'yes' )
                return;
            wppb_in_mpi_remove_list_from_user($field['mailpoet-lists'], $user_id);
        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_in_save_mailpoet_value', 10, 4 );
add_action( 'wppb_backend_save_form_field', 'wppb_in_save_mailpoet_value', 10, 4 );


/*
 * For e-mail confirmation we need to store the list id until the user confirms the register
 */
function wppb_in_add_to_user_signup_form_meta_mailpoet( $meta, $global_request ) {
    $wppb_manage_fields = get_option( 'wppb_manage_fields', array() );

    if( !empty( $wppb_manage_fields ) ) {
        foreach( $wppb_manage_fields as $field ) {
            if( $field['field'] == 'MailPoet Subscribe' && isset( $global_request[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] ) && !empty( $global_request[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] ) ) {
                $meta['custom_field_mailpoet_subscribe_' . $field['id'] ] = sanitize_text_field( $global_request[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] );
            }
        }
    }

    return $meta;

}
add_filter( 'wppb_add_to_user_signup_form_meta', 'wppb_in_add_to_user_signup_form_meta_mailpoet', 10 , 2 );


/*
 * Subscribe user to the list when the user becomes active
 */
function wppb_in_activate_user_subscribe_mailpoet_list( $user_id, $password, $meta ) {
    $wppb_manage_fields = get_option( 'wppb_manage_fields', array() );

    if( !empty( $wppb_manage_fields ) ) {
        foreach( $wppb_manage_fields as $field ) {

            if( $field['field'] == 'MailPoet Subscribe' && isset( $meta[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] ) && !empty( $meta[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] ) ) {

                $mailpoet_subscribe_list_id = $meta[ 'custom_field_mailpoet_subscribe_' . $field['id'] ] ;
                if(  !empty( $mailpoet_subscribe_list_id ) ) {
                    wppb_in_mpi_add_subscriber( $mailpoet_subscribe_list_id, $user_id );
                }

            }

        }
    }
}
add_action( 'wppb_activate_user', 'wppb_in_activate_user_subscribe_mailpoet_list', 10, 3 );