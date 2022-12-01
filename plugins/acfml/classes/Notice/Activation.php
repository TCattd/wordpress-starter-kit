<?php

namespace ACFML\Notice;

class Activation {

	public static function activate() {
		if ( function_exists( 'wpml_get_admin_notices' ) ) {
			$text  = '<h2>' . esc_html__( 'ACF Multilingual Activated', 'acfml' ) . '</h2>';
			$text .= '<p>' . esc_html__( 'Different ACF fields require different translation settings and workflows. Make sure to read our documentation for more information.', 'acfml' ) . '</p>';
			$text .= '<a href="' . Links::getAcfmlMainDoc( [ 'utm_term' => 'activation' ] ) . '" class="button-primary" target="_blank">' . esc_html__( 'Read the documentation', 'acfml' ) . '</a>';

			$notices = wpml_get_admin_notices();
			$notice  = $notices->create_notice( 'acfml-activation-notice', $text, 'acfml' );
			$notice->set_hideable( true );
			$notice->set_css_class_types( [ 'notice-success' ] );
			$notices->add_notice( $notice );
		}
	}
}
