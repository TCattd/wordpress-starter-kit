<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_gdpr_delete_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'GDPR Delete Button' ){
        if ( $form_location === 'edit_profile' ){
            $item_title = apply_filters( 'wppb_'.$form_location.'_gdpr_delete_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
            $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

            $edited_user_id = get_current_user_id();
            if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
                if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) ){
                    $edited_user_id = absint( $_GET['edit_user'] );
                }
            }

            $output = '
			<label for="wppb-delete-account">'. wp_kses_post( $item_title ) .'</label>
			<input class="wppb-delete-account" type="submit" value="'. __( 'Delete', 'profile-builder' ) .'" />';
            $output .= '<span class="wppb-description-delimiter">'.trim( html_entity_decode ( $item_description ) ).'</span>';


            $delete_url = add_query_arg( array(
                'wppb_user' => $edited_user_id,
                'wppb_action' => 'wppb_delete_user',
                'wppb_nonce' => wp_create_nonce( 'wppb-user-own-account-deletion'),
            ), home_url());

            wp_enqueue_script( 'wppb-gdpr-delete-script', WPPB_PLUGIN_URL.'front-end/default-fields/gdpr-delete/gdpr-delete.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
            wp_localize_script('wppb-gdpr-delete-script', 'wppbGdpr', array(
                'delete_url'  => $delete_url,
                'delete_text' => sprintf(__('Type %s to confirm deleting your account and all data associated with it:', 'profile-builder'), 'DELETE' ),
                'delete_error_text' => sprintf(__('You did not type %s. Try again!', 'profile-builder'), 'DELETE' ),
            ));

            return apply_filters( 'wppb_'.$form_location.'_gdpr_delete_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
        }
    }
}
add_filter( 'wppb_output_form_field_gdpr-delete-button', 'wppb_gdpr_delete_handler', 10, 6 );

