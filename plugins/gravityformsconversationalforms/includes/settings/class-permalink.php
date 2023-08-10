<?php

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Settings;

use Gravity_Forms\Gravity_Forms_Conversational_Forms\GF_Conversational_Forms;
use Gravity_Forms\Gravity_Forms\Settings\Fields;
use \GFFormsModel;

defined( 'ABSPATH' ) || die();

class Permalink extends Fields\Base {

	/**
	 * Field type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $type = 'permalink';

	/**
	 * Input type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $input_type = 'text';

	public $input_prefix;

	public $input_suffix;

	public $action_button;

	public $action_button_icon = 'eye';

	public $action_button_icon_prefix = 'gform-common-icon';

	public $action_button_text = false;


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

		$html .= sprintf(
			'<span class="%1$s">%10$s %12$s<input data-js="permalink-input-value" class="gform-input gform-input--text" type="%2$s" name="%3$s_%4$s" value="%5$s" %6$s %7$s />%13$s %14$s %11$s %8$s %9$s</span>',
			esc_attr( $this->get_container_classes() ),
			esc_attr( $this->input_type ),
			esc_attr( $this->settings->get_input_name_prefix() ),
			esc_attr( $this->name ),
			$value ? esc_attr( htmlspecialchars( $value, ENT_QUOTES ) ) : '',
			$this->get_describer() ? sprintf( 'aria-describedby="%s"', $this->get_describer() ) : '',
			implode( ' ', $this->get_attributes() ),
			isset( $this->append ) ? sprintf( '<span class="gform-settings-field__text-append">%s</span>', esc_html( $this->append ) ) : '',
			$this->get_error_icon(),
			$this->get_addon_wrapper_open(),
			$this->get_addon_wrapper_close(),
			$this->get_prefix(),
			$this->get_suffix(),
			$this->get_action_button()
		);

		// Insert after input markup.

		$html .= isset( $this->after_input ) ? $this->after_input : '';

		return $html;

	}

	/**
	 * Determine if this field needs the addon wrapper markup.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function needs_addon_wrapper() {
		return $this->input_prefix || $this->input_suffix || $this->action_button;
	}

	/**
	 * Get the opening markup for the addon wrapper.
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function get_addon_wrapper_open() {
		if ( ! $this->needs_addon_wrapper() ) {
			return null;
		}

		return sprintf(
			'<div class="gform-input-add-on-wrapper %s %s %s">',
			$this->input_prefix ? 'gform-input-add-on-wrapper--prefix' : null,
			$this->input_suffix ? 'gform-input-add-on-wrapper--suffix' : null,
			$this->action_button ? 'gform-input-add-on-wrapper--action-button' : null
		);
	}

	/**
	 * Get the closing markup for the addon wrapper.
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function get_addon_wrapper_close() {
		if ( ! $this->needs_addon_wrapper() ) {
			return null;
		}

		return '</div>';
	}

	/**
	 * Get the markup for the input prefix.
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function get_prefix() {
		if ( ! $this->input_prefix ) {
			return null;
		}

		return sprintf( '<div class="gform-input__add-on gform-input__add-on--prefix">%s</div>', esc_html( $this->input_prefix ) );
	}

	/**
	 * Get the markup for the input suffix.
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function get_suffix() {
		if ( ! $this->input_suffix ) {
			return null;
		}

		return sprintf( '<div class="gform-input__add-on gform-input__add-on--suffix">%s</div>', esc_html( $this->input_suffix ) );
	}

	/**
	 * Get the markup for the action button. (By default used to open the permalink in a new tab).
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function get_action_button() {
		if ( ! $this->action_button ) {
			return null;
		}

		$current_value = $this->get_value();
		if ( empty( $current_value ) ) {
			return '';
		}

		return sprintf(
			'<button data-js="permalink-action-button" data-js-root="%3$s" class="gform-button gform-button--size-r gform-button--white gform-button--active-type-loader gform-button--icon-leading gform-input__add-on--action-button" data-saved-value="%5$s">
				<i class="gform-button__icon %1$s %1$s--%2$s" data-js="button-icon"></i>
				%4$s
			</button>',
			$this->action_button_icon_prefix,
			$this->action_button_icon,
			$this->input_prefix,
			$this->action_button_text ? sprintf( '<span class="gform-button__text gform-button__text--inactive" data-js="button-active-text">%s</span>', $this->action_button_text ) : null,
			$current_value
		);
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

		// Sanitize posted value.
		$sanitized_value = sanitize_text_field( $value );

		// If posted and sanitized values do not match, add field error.
		if ( $value !== $sanitized_value ) {

			// Prepare correction script.
			$double_encoded_safe_value = htmlspecialchars( htmlspecialchars( $sanitized_value, ENT_QUOTES ), ENT_QUOTES );
			$script                    = sprintf(
				'jQuery("input[name=\"%s_%s\"]").val(jQuery(this).data("safe"));',
				$this->settings->get_input_name_prefix(),
				$this->name
			);

			// Prepare message.
			$message = sprintf(
				"%s <a href='javascript:void(0);' onclick='%s' data-safe='%s'>%s</a>",
				esc_html__( 'The text you have entered is not valid. For security reasons, some characters are not allowed. ', 'gravityforms' ),
				htmlspecialchars( $script, ENT_QUOTES ),
				$double_encoded_safe_value,
				esc_html__( 'Fix it', 'gravityforms' )
			);

			// Set field error.
			$this->set_error( $message );

		}

		if ( ! $this->is_permalink_available( $value ) ) {
			// Prepare message.
			$message = esc_html__( 'The permalink you entered is already being used by another page on your site.  Please enter a different permalink.', 'gravityforms' );

			// Set field error.
			$this->set_error( $message );
		}

	}

	/**
	 * Check if the permalink is available.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_permalink_available( $slug ) {
		$form      = rgget( 'id' );
		$form_meta = \GFFormsModel::get_form_meta( $form );
		if ( rgars(  $form_meta, 'gf_theme_layers/form_full_screen_slug' ) ) {
			// if the current value is the same as the new value, then the permalink is available
			if ( $slug == $form_meta['gf_theme_layers']['form_full_screen_slug'] ) {
				return true;
			}
		}

		$url      = $this->input_prefix . $slug;
		$response = wp_remote_get( $url, array( 'sslverify' => false ) );
		if ( is_wp_error( $response ) ) {
			( new \Gravity_Forms\Gravity_Forms_Conversational_Forms\GF_Conversational_Forms )->log_debug( __METHOD__ . '(): Error checking if permalink is available. ' . $response->get_error_message() );
			return true;
		}
		if ( rgar( $response['response'], 'code' ) && '404' == $response['response']['code'] ) {
			return true;
		}
		return false;
	}

}
