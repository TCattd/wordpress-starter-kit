<?php

$redirect_url = wppb_toolbox_get_settings( 'fields', 'redirect-if-empty-required-url' );

if ( !empty( $redirect_url ) )
    add_action( 'template_redirect', 'wppb_toolbox_redirect_if_empty_required' );

function wppb_toolbox_redirect_if_empty_required() {
    if ( current_user_can( 'manage_options' ) )
         return;

	$user_id = get_current_user_id();
    $current_url = wppb_curpageurl();
	$redirect_url = get_permalink( wppb_toolbox_get_settings( 'fields', 'redirect-if-empty-required-url' ) );

	if ( !empty( $user_id ) && ( $current_url != $redirect_url ) && apply_filters( 'wppb_toolbox_redirect_if_empty_required', true, $user_id, $current_url, $redirect_url ) ) {

		$fields = get_option( 'wppb_manage_fields', array() );
        $without_meta_name = array( 'user_url' => 'Default - Website', 'display_name' => 'Default - Display name publicly as' );

		foreach ( $fields as $field ){

			if ( $field['required'] == 'Yes' && !empty( $field['meta-name'] ) ){
                $value = get_user_meta( $user_id, $field['meta-name'], true );

                if ( empty( $value ) ){
                    wp_redirect( $redirect_url );
                    exit();
                }
            }

            if( $field['required'] == 'Yes' && ( $field['field'] == 'Default - Website' || $field['field'] == 'Default - Display name publicly as' ) ){
                $user = get_userdata( $user_id );

                $key = array_search( $field['field'], $without_meta_name );
                $value = $user->$key;

                if ( empty( $value ) ){
                    wp_redirect( $redirect_url );
                    exit();
                }
            }
		}
	}
}
