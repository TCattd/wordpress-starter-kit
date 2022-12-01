<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_RememberMe extends GF_Field_Checkbox {

	/**
	 * @var string $type The field type.
	 */
	public $type = 'remember_me';

	/**
	 * Prevent the field appearing in the form editor add fields panels.
	 *
	 * @since 5.0
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array();
	}

	/**
	 * Returns the HTML tag for the field container.
	 *
	 * @since 4.9
	 *
	 * @param array $form The current Form object.
	 *
	 * @return string
	 */
	public function get_field_container_tag( $form ) {
		return gf_user_registration()->is_gravityforms_supported( '2.5' ) ? 'div' : 'ul';
	}

	/**
	 * Returns the field inner markup.
	 *
	 * @since  4.9
	 *
	 * @param array        $form  The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array   $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$field_id            = "input_$this->id";
		$choices_markup      = $this->get_checkbox_choices( $value, '', 0 );
		$field_container_tag = $this->get_field_container_tag( $form );

		return sprintf(
			"<div class='ginput_container ginput_container_checkbox'><{$field_container_tag} class='gfield_checkbox' id='%s'>%s</{$field_container_tag}></div>",
			esc_attr( $field_id ),
			$choices_markup
		);
	}

	/**
	 * Get checkbox choice inputs for field.
	 *
	 * @since 4.9
	 *
	 * @param string|array $value         The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param string       $disabled_text The HTML disabled attribute.
	 * @param int          $form_id       The current form ID.
	 *
	 * @return string
	 */
	public function get_checkbox_choices( $value, $disabled_text, $form_id = 0 ) {

		$choices = '';

		if ( ! is_array( $this->choices ) ) {
			return '';
		}



		$input_id = $this->id . '.' . 1;
		$choice   = $this->choices[0];
		if ( ( ! isset( $_GET['gf_token'] ) && empty( $_POST ) ) && rgar( $choice, 'isSelected' ) ) {
			$checked = "checked='checked'";
		} elseif ( is_array( $value ) && GFFormsModel::choice_value_match( $this, $choice, rgget( $input_id, $value ) ) ) {
			$checked = "checked='checked'";
		} elseif ( ! is_array( $value ) && GFFormsModel::choice_value_match( $this, $choice, $value ) ) {
			$checked = "checked='checked'";
		} else {
			$checked = '';
		}

		$tabindex      = $this->get_tabindex();
		$choice_tag    = gf_user_registration()->is_gravityforms_supported( '2.5' ) ? 'div' : 'li';
		$choice_value  = esc_attr( $choice['value'] );
		$choice_markup = "<{$choice_tag} class='gchoice gchoice_{$this->id}'>
						<input class='gfield-choice-input' name='input_{$input_id}' type='checkbox'  value='{$choice_value}' {$checked} id='choice_{$this->id}' {$tabindex} {$disabled_text} {$this->get_aria_describedby()}/>
						<label for='choice_{$this->id}' id='label_{$this->id}'>{$choice['text']}</label>
					</{$choice_tag}>";

		/**
		 * Override the default choice markup used when rendering radio button, checkbox and drop down type fields.
		 *
		 * @since 4.9
		 *
		 * @param string $choice_markup The string containing the choice markup to be filtered.
		 * @param array  $choice        An associative array containing the choice properties.
		 * @param object $field         The field currently being processed.
		 * @param string $value         The value to be selected if the field is being populated.
		 */
		$choices .= gf_apply_filters( array( 'gform_field_choice_markup_pre_render', $this->formId, $this->id ), $choice_markup, $choice, $this, $value );

		/**
		 * Modify the checkbox items before they are added to the checkbox list.
		 *
		 * @since 4.9
		 *
		 * @param string $choices The string containing the choices to be filtered.
		 * @param object $field   Ahe field currently being processed.
		 */
		return gf_apply_filters( array( 'gform_field_choices', $this->formId, $this->id ), $choices, $this );
	}


}

GF_Fields::register( new GF_Field_RememberMe() );
