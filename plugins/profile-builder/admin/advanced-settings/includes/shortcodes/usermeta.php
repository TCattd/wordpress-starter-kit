<?php

add_shortcode('user_meta', 'wppb_toolbox_usermeta_handler');
function wppb_toolbox_usermeta_handler( $atts, $content=null){

	if ( !isset( $atts['user_id'] ) ){
		$user = wp_get_current_user();
		$atts['user_id'] = $user->ID;
	}
	if ( !isset( $atts['size'] ) ){
		$atts['size'] = '50';
	}
	if ( !isset( $atts['pre'] ) ) {
		$atts['pre'] = '';
	}
	if ( !isset( $atts['post'] ) ) {
		$atts['post'] = '';
	}
	if ( !isset( $atts['wpautop'] ) ) {
		$atts['wpautop'] = '';
	}


	$user = new WP_User($atts['user_id']);

	if ( !$user->exists() ) return;

	if ( !array_key_exists( 'key', $atts ) ) return;

	if( $atts['key'] == 'avatar' ){
		return $atts['pre'] . get_avatar( $user->ID, $atts['size']) . $atts['post'] ;
	}

    if( $atts['key'] === 'id' ){
        $atts['key'] = 'ID';
    }

	if ( $user->has_prop( $atts['key'] ) ){
		if ($atts['wpautop'] == 'on'){
			$value = wpautop( $user->get( $atts['key'] ) );
		} else {
			$value = $user->get( $atts['key'] );
		}
	}

	if (!empty( $value )){
		return $atts['pre'] . $value . $atts['post'] ;
	}

	return;
}
