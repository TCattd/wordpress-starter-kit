<?php

namespace LLAR\Core;

if( !defined( 'ABSPATH' ) ) exit();

class Shortcodes {

	/**
	 * Register all shortcodes
	 */
	public function register() {

		add_shortcode( 'llar-link', array( $this, 'llar_link_callback' ) );
	}

	/**
	 * [llar-link url="" text=""] callback
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function llar_link_callback( $attr ) {

		$attr = shortcode_atts( array(
			'url' 	=> '#',
			'text' 	=> 'Link'
		), $attr );

		return '<a href="' . esc_attr( $attr['url'] ) . '" target="_blank">' . esc_html( $attr['text'] ) . '</a>';
	}

}