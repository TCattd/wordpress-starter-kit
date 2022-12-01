<?php

// Bail if accessed directly.
if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Class GF_Field_Poll
 *
 * Handles the Poll field using the field framework.
 *
 * @since Unknown
 */
class GF_Field_Poll extends GF_Field {

	/**
	 * Defines the field type to be created.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @var string $type Contains the field type string to be created.
	 */
	public $type = 'poll';

	// # FORM EDITOR & FIELD MARKUP -------------------------------------------------------------------------------------

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GF_Field_Poll::get_form_editor_button()
	 * @used-by GFCommon::get_field_type_title()
	 *
	 * @return string The field title to be use. Escaped.
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Poll', 'gravityformspolls' );
	}

	/**
	 * Assign the Poll button to the Advanced Fields group.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GF_Field::add_button()
	 * @uses    GF_Field_Poll::get_form_editor_field_title()
	 *
	 * @return array The button group and text to display within it.
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title()
		);
	}

	/**
	 * Returns the field's form editor icon dashicon class.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return 'gform-icon--poll';
	}

	/**
	 * Return the settings which should be available on the field in the form editor.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFFormDetail::inline_scripts()
	 *
	 * @return array Contains strings identifying which settings are available.
	 */
	function get_form_editor_field_settings() {
		return array(
			'poll_field_type_setting',
			'poll_question_setting',
			'randomize_choices_setting',
		);
	}

}

// Register the field with the field framework.
GF_Fields::register( new GF_Field_Poll() );
