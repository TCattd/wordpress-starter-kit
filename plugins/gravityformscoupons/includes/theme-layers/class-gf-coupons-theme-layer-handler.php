<?php

use Gravity_Forms\Gravity_Forms\Theme_Layers\API\Fluent\Theme_Layer_Builder;

/**
 * Handler for registering the Theme Layer responsible for registering all of the
 * assets and modifications needed to leverage the theme framework.
 *
 * Uses the Fluent API of Theme Layers.
 *
 * @since 3.1
 */
class GF_Coupons_Theme_Layer_Handler {

	private $addon;

	/**
	 * Plugin_Settings constructor.
	 *
	 * @since 1.0
	 *
	 * @param GF_Google_Analytics $addon GF_Google_Analytics instance.
	 */
	public function __construct( $addon ) {
		$this->addon = $addon;
	}

	/**
	 * Main method called when this Theme Layer is registered.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	public function handle() {

		if ( ! class_exists( 'Gravity_Forms\Gravity_Forms\Theme_Layers\API\Fluent\Theme_Layer_Builder' ) ) {
			return;
		}

		$layer = new Theme_Layer_Builder();
		$layer->set_name( 'coupons' )
			  ->set_styles( array( $this, 'get_styles' ) )
			  ->register();
	}

	/**
	 * An array of styles to enqueue.
	 *
	 * @since 3.1
	 *
	 * @param $form
	 * @param $ajax
	 * @param $settings
	 * @param $block_settings
	 *
	 * @return array|\string[][]
	 */
	public function get_styles( $form, $ajax, $settings, $block_settings ) {
		$theme_slug = \GFFormDisplay::get_form_theme_slug( $form );

		if ( $theme_slug !== 'orbital' ) {
			return array();
		}

		$base_url = $this->addon->get_base_url();

		return array(
			'foundation' => array(
				array( 'gravity_forms_coupon_theme_foundation', "$base_url/assets/css/dist/theme-foundation.css" ),
			),
			'framework' => array(
				array( 'gravity_forms_coupon_theme_framework', "$base_url/assets/css/dist/theme-framework.css" ),
			),
		);
	}

}
