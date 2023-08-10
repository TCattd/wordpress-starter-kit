<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings;

use Gravity_Forms\Gravity_Forms\Settings\Fields\Base;

defined( 'ABSPATH' ) || die();

class Range extends Base {

	/**
	 * Field type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $type = 'range';

	protected $min;

	protected $max;

	protected $step;

	protected $show_value;

	protected $value_input_position;

	protected $value_suffix;


	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function markup() {

		// Get value.
		$value = $this->get_value();

		// Prepare after_input.
		// Dynamic after_input content should use a callable to render.
		if ( isset( $this->after_input ) && is_callable( $this->after_input ) ) {
			$this->after_input = call_user_func( $this->after_input, $value, $this );
		}

		// Prepare markup.
		// Display description.
		$html = $this->get_description();

		$props = array(
			'id'                 => '',
			'max'                => $this->max,
			'min'                => $this->min,
			'name'               => esc_attr( $this->settings->get_input_name_prefix() ) . '_' . esc_attr( $this->name ),
			'showValueInput'     => $this->show_value,
			'step'               => $this->step,
			'value'              => empty( $value ) ? (int) $this->default_value : (int) $value,
			'valueInputPosition' => $this->value_input_position,
			'valueInputSuffix'   => $this->value_suffix,
			'customClasses'      => array( $this->class ),
		);

		$html .= sprintf(
			'<span data-js="gform-input--range" data-js-props="%s"></span>',
			esc_attr( json_encode( $props ) )
		);

		// Insert after input markup.

		$html .= isset( $this->after_input ) ? $this->after_input : '';

		return $html;
	}

	// # VALIDATION METHODS --------------------------------------------------------------------------------------------

	/**
	 * Validate posted field value.
	 *
	 * @since 1.0
	 *
	 * @param string $value Posted field value.
	 */
	public function do_validation( $value ) {

		// If field is required and value is missing, set field error.
		if ( $this->required && rgblank( $value ) ) {
			$this->set_error( rgobj( $this, 'error_message' ) );
		}

		if ( $value > $this->max ) {
			// @translators %s is a numeric value representing the max allowed value.
			$this->set_error( __( sprintf( 'Value must be less than %s', $this->max ), 'gravityforms' ) );
		}

		if ( $value < $this->min ) {
			// @translators %s is a numeric value representing the minimum allowed value.
			$this->set_error( __( sprintf( 'Value must be more than %s', $this->min ), 'gravityforms' ) );
		}
	}

}