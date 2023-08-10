<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Layers\Views;

use Gravity_Forms\Gravity_Forms\Theme_Layers\API\View;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\GF_Conversational_Forms;

/**
 * Used to override the Form output when a form has Conversational Forms enabled.
 *
 * @since 1.0
 */
class Form_View extends View {

	/**
	 * Only override the markup if convo forms is enabled.
	 *
	 * @since 1.0
	 *
	 * @param $form
	 * @param $form_id
	 * @param $block_settings
	 *
	 * @return bool
	 */
	public function should_override( $form, $form_id, $block_settings = array() ) {
		if ( \GFCommon::is_preview() ) {
			return false;
		}

		global $wp;

		$full_screen_slug = $this->get_setting( 'form_full_screen_slug', $form_id );

		$slug = GF_Conversational_Forms::get_instance()->get_requested_slug();

		if ( ! $this->get_setting( 'enable', $form_id ) || ( $slug != $full_screen_slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the modified markup for the form view.
	 *
	 * @since 1.0
	 *
	 * @hook gform_get_form_filter
	 *
	 * @param $content
	 * @param $form
	 * @param $value
	 * @param $lead_id
	 * @param $form_id
	 *
	 * @return array|string|string[]|null
	 */
	public function get_markup( $content, $form, $value, $lead_id, $form_id ) {
		$content = $this->add_wrapper_class( $content );
		$content = $this->remove_pages( $content );

		return $content;
	}

	/**
	 * Add a custom string of classes to the wrapper.
	 *
	 * @since 1.0
	 *
	 * @param $content
	 *
	 * @return array|string|string[]
	 */
	private function add_wrapper_class( $content ) {
		$classes = 'gform-theme gform-theme--foundation gform-theme--framework gform-theme--orbital gform-theme--type-conversational';

		return str_replace( 'gravity-theme gform-theme--no-framework', $classes, $content );
	}

	/**
	 * Remove any Page output from the form.
	 *
	 * @since 1.0
	 *
	 * @param $content
	 *
	 * @return array|string|string[]|null
	 */
	private function remove_pages( $content ) {
		$content = preg_replace( '/(id=["\']gform_page_[^>]*)(style=["\'][^"\']+["\'])[^>]*>/', '$1>', $content );

		return $content;
	}

}
