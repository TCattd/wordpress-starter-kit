<?php

$affected_fields = wppb_toolbox_get_settings( 'fields', 'restricted-words-fields' );

if ( $affected_fields != false ) {
    foreach ( $affected_fields as $field )
        add_filter( 'wppb_check_form_field_default-' . $field, 'wppb_toolbox_check_banned_words', 20, 4 );
}

function wppb_toolbox_check_banned_words( $message, $field, $request_data, $form_location ){
    $meta_name = str_replace( 'wppb_check_form_field_default-', '', current_filter() );
    $meta_name = str_replace( '-', '_', $meta_name );

	if( empty( $request_data[ $meta_name ] ) ) return $message;

	$banned_words = wppb_toolbox_get_settings( 'fields', 'restricted-words-data' );

    if ( $banned_words == false ) return $message;

    $validation_message = wppb_toolbox_get_settings( 'fields', 'restricted-words-message' );

	foreach ( $banned_words as $banned ) {
		if ( strpos( $request_data[ $meta_name ], $banned ) !== false) {
			return $validation_message;
		}
	}

    return $message;
}
