<?php

function wppb_toolbox_userlisting_search_placeholder_text( $default ) {
    $text = wppb_toolbox_get_settings( 'userlisting', 'search-placeholder-text' );

    if ( $text == false )
        return $default;

    return esc_html( $text );
}
add_filter('wppb_userlisting_search_field_text', 'wppb_toolbox_userlisting_search_placeholder_text', 20 );
