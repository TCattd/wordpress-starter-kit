<?php

/**
 * Function that change Login/Logout menu item label
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_login_logout_title( $ID ) {
	$wppb_cpm_login_label = get_post_meta( $ID, 'wppb-cpm-login-label', true );
	$wppb_cpm_logout_label = get_post_meta( $ID, 'wppb-cpm-logout-label', true );

	if ( ! is_user_logged_in() ) {
		$title = ( ! empty( $wppb_cpm_login_label ) ? $wppb_cpm_login_label : __( 'Login', 'profile-builder' ) );
		return esc_html( $title );
	} else {
		$title = ( ! empty( $wppb_cpm_logout_label ) ? $wppb_cpm_logout_label : __( 'Logout', 'profile-builder' ) );
		return esc_html( $title );
	}
}

/**
 * Function that adds the menu item url
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_setup_nav_menu_item( $item ) {
	global $pagenow;

	$redirect_after_logout_url = '';
	$versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	if( defined( 'PROFILE_BUILDER' ) && in_array( PROFILE_BUILDER, $versions ) ) {
		$wppb_module_settings = get_option( 'wppb_module_settings' );

		if( isset( $wppb_module_settings['wppb_customRedirect'] ) && $wppb_module_settings['wppb_customRedirect'] == 'show' && function_exists( 'wppb_custom_redirect_url' ) ) {
			$redirect_after_logout_url = wppb_custom_redirect_url( 'after_logout', $redirect_after_logout_url );
		}
	}
	if( empty( $redirect_after_logout_url ) && function_exists( 'wppb_curpageurl' ) ) {
		$redirect_after_logout_url = wppb_curpageurl();
	}
	$redirect_after_logout_url = apply_filters( 'wppb_after_logout_redirect_url', $redirect_after_logout_url );

	$wppb_cpm_form_page_url = get_post_meta( $item->ID, 'wppb-cpm-form-page-url', true );
	$wppb_cpm_iframe_title = get_post_meta( $item->ID, 'wppb-cpm-iframe-title', true );
	$wppb_cpm_iframe_height = get_post_meta( $item->ID, 'wppb-cpm-iframe-height', true );
	$wppb_cpm_iframe_width = get_post_meta( $item->ID, 'wppb-cpm-iframe-width', true );

	if( $pagenow != 'nav-menus.php' && strstr( $item->type, 'wppb_cpm' ) != '' ) {
		if( ! empty( $wppb_cpm_form_page_url ) ) {
			switch( $item->type ) {
				case 'wppb_cpm_login_logout' :
					$item->url = ( is_user_logged_in() ? wp_logout_url( $redirect_after_logout_url ) : $wppb_cpm_form_page_url );
					$item->title = wppb_in_cpm_login_logout_title( $item->ID );
					break;
				case 'wppb_cpm_login_iframe' :
					( parse_url( $wppb_cpm_form_page_url, PHP_URL_QUERY ) ? $wppb_cpm_form_page_url .= '&wppb_cpm_iframe=yes&wppb_cpm_form=login' : $wppb_cpm_form_page_url .= '?wppb_cpm_iframe=yes&wppb_cpm_form=login' );
					$item->url = $wppb_cpm_form_page_url;
					( ! empty( $wppb_cpm_iframe_height ) ? $item->url .= '&wppb_cpm_iframe_height='. $wppb_cpm_iframe_height : $item->url .= '&wppb_cpm_iframe_height=300' );
					( ! empty( $wppb_cpm_iframe_width ) ? $item->url .= '&wppb_cpm_iframe_width='. $wppb_cpm_iframe_width : $item->url .= '&wppb_cpm_iframe_width=600' );
					( ! empty( $wppb_cpm_iframe_title ) ? $item->url .= '&wppb_cpm_iframe_title='. $wppb_cpm_iframe_title : $item->url .= '&wppb_cpm_iframe_title='. __( "Login", 'profile-builder' ) );
					break;
				case 'wppb_cpm_edit_profile_iframe' :
					( parse_url( $wppb_cpm_form_page_url, PHP_URL_QUERY ) ? $wppb_cpm_form_page_url .= '&wppb_cpm_iframe=yes&wppb_cpm_form=ep' : $wppb_cpm_form_page_url .= '?wppb_cpm_iframe=yes&wppb_cpm_form=ep' );
					$item->url = $wppb_cpm_form_page_url;
					( ! empty( $wppb_cpm_iframe_height ) ? $item->url .= '&wppb_cpm_iframe_height='. $wppb_cpm_iframe_height : $item->url .= '&wppb_cpm_iframe_height=600' );
					( ! empty( $wppb_cpm_iframe_width ) ? $item->url .= '&wppb_cpm_iframe_width='. $wppb_cpm_iframe_width : $item->url .= '&wppb_cpm_iframe_width=600' );
					( ! empty( $wppb_cpm_iframe_title ) ? $item->url .= '&wppb_cpm_iframe_title='. $wppb_cpm_iframe_title : $item->url .= '&wppb_cpm_iframe_title='. __( "Edit Profile", 'profile-builder' ) );
					break;
				case 'wppb_cpm_register_iframe' :
					( parse_url( $wppb_cpm_form_page_url, PHP_URL_QUERY ) ? $wppb_cpm_form_page_url .= '&wppb_cpm_iframe=yes&wppb_cpm_form=reg' : $wppb_cpm_form_page_url .= '?wppb_cpm_iframe=yes&wppb_cpm_form=reg' );
					$item->url = $wppb_cpm_form_page_url;
					( ! empty( $wppb_cpm_iframe_height ) ? $item->url .= '&wppb_cpm_iframe_height='. $wppb_cpm_iframe_height : $item->url .= '&wppb_cpm_iframe_height=600' );
					( ! empty( $wppb_cpm_iframe_width ) ? $item->url .= '&wppb_cpm_iframe_width='. $wppb_cpm_iframe_width : $item->url .= '&wppb_cpm_iframe_width=600' );
					( ! empty( $wppb_cpm_iframe_title ) ? $item->url .= '&wppb_cpm_iframe_title='. $wppb_cpm_iframe_title : $item->url .= '&wppb_cpm_iframe_title='. __( "Register", 'profile-builder' ) );
					break;
			}
		}

		if( $item->type == 'wppb_cpm_logout' ) {
			$item->url = wp_logout_url( $redirect_after_logout_url );
		}

		if( ! empty( $item->url ) ) {
			$item->url = esc_url( $item->url );
		}
	}

	return $item;
}
add_filter( 'wp_setup_nav_menu_item', 'wppb_in_cpm_setup_nav_menu_item' );