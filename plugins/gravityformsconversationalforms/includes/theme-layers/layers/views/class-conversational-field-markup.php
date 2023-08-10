<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Layers\Views;

use Gravity_Forms\Gravity_Forms\Theme_Layers\API\View;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\GF_Conversational_Forms;
use GFAPI;

/**
 * Used to override the Form output when a form has Conversational Forms enabled.
 *
 * @since 1.0
 */
class Conversational_Field_View extends View {

	/**
	 * Only override the markup if convo forms is enabled and we're viewing the conversational form slug.
	 *
	 * @param object $field          The field object.
	 * @param int    $form_id        The form ID.
	 * @param array  $block_settings The block settings.
	 *
	 * @return bool
	 * @since 1.0
	 */
	public function should_override( $field, $form_id, $block_settings = array() ) {
		global $wp;

		$full_screen_slug = $this->get_setting( 'form_full_screen_slug', $form_id );

		$slug = GF_Conversational_Forms::get_instance()->get_requested_slug();

		if ( ! $this->get_setting( 'enable', $form_id ) || ( $slug != $full_screen_slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the field header markup.
	 *
	 * @param int    $form_id The form ID.
	 * @param object $field   The field object.
	 *
	 * @return string
	 * @since 1.0
	 */
	private function get_field_header( $form_id, $field ) {
		$form        = GFAPI::get_form( $form_id );
		$first_field = reset( $form['fields'] );

		return $first_field['id'] === $field['id'] ? '<div class="gform-conversational__field-header" data-js="gform-conversational-field-header"></div>' : '';
	}

	/**
	 * Get the field footer markup.
	 *
	 * @param int    $form_id The form ID.
	 * @param object $field   The field object.
	 *
	 * @return string
	 * @since 1.0
	 */
	private function get_field_footer( $form_id, $field ) {
		$continue_button_settings = $this->get_setting( 'continue_button_text', $form_id );

		/* Translators: &#9166;: Symbol for enter key on keyboard. */
		$field_nav_text = esc_html__( 'Press Enter', 'gravityformsconversationalforms' );

		return '<div class="gform-conversational__field-footer"><div class="gform-conversational__field-nav"><button type="button" class="gform-conversational__nav-button gform-conversational__nav-button--next-field_' . $form_id . '_' . $field['id'] .' gform-button active" data-js="gform-conversational-nav-field-next">' . $continue_button_settings . '</button>' .
			'<span class="gform-conversational__field-nav-helper-text">' . $field_nav_text . '<span class="gform-conversational__field-nav-helper-icon gform-orbital-icon gform-orbital-icon--arrow-back" aria-hidden="true"></span></span></div></div>';
	}

	/**
	 * Get the modified markup for the fields view.
	 *
	 * @param string $content The field content.
	 * @param object $field   The field object.
	 * @param array  $value   The field value.
	 * @param int    $lead_id The lead ID.
	 * @param int    $form_id The form ID.
	 *
	 * @return array|string|string[]|null
	 * @since 1.0
	 */
	public function get_markup( $content, $field, $value, $lead_id, $form_id ) {
		$fieldHeader  = $this->get_field_header( $form_id, $field );
		$fieldContent = $content;
		$fieldFooter  = $this->get_field_footer( $form_id, $field );

		$content = $fieldHeader;
		$content .= $fieldContent;
		$content .= $fieldFooter;

		return $content;
	}
}
