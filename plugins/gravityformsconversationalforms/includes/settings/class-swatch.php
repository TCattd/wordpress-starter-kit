<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings;

use Gravity_Forms\Gravity_Forms\Settings\Fields\Base;

defined( 'ABSPATH' ) || die();

class Swatch extends Base {

	/**
	 * Field type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $type = 'swatch';

	protected $allow_new;

	protected $palette = array();


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
		$value          = $this->get_value();
		$saved_swatches = $this->settings->get_value( $this->name . '-all-swatches', array() );

		// Prepare after_input.
		// Dynamic after_input content should use a callable to render.
		if ( isset( $this->after_input ) && is_callable( $this->after_input ) ) {
			$this->after_input = call_user_func( $this->after_input, $value, $this );
		}

		// Prepare markup.
		// Display description.
		$html = $this->get_description();

		$props = array(
			'id'            => '',
			'name'          => esc_attr( $this->settings->get_input_name_prefix() ) . '_' . esc_attr( $this->name ),
			'allowNew'      => $this->allow_new,
			'palette'       => $this->palette,
			'paletteCustom' => array_values( array_filter( $saved_swatches ) ),
			'value'         => empty( $value ) ? $this->default_value : $value,
			'customClasses' => array( $this->class ),
			'i18n'          => array(
				'swatch'      => __( 'swatch', 'gravityforms' ),
				'colorPicker' => array(
					'apply' => __( 'Apply', 'gravityforms' ),
					'hex'   => __( 'Hex', 'gravityforms' ),
				),
			),
		);

		$html .= sprintf(
			'<span data-js="gform-input--swatch" data-js-props="%s"></span>',
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
	}

}