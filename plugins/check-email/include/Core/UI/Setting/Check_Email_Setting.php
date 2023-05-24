<?php namespace CheckEmail\Core\UI\Setting;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check Email Log Setting.
 */
abstract class Check_Email_Setting {

	protected $section;

	public function __construct() {
		$this->section = new Check_Email_Log_Setting_Section();

		$this->initialize();

		$this->section->fields            = $this->get_fields();
		$this->section->callback          = array( $this, 'render' );
		$this->section->sanitize_callback = array( $this, 'sanitize' );
	}

	public function load() {
		add_filter( 'check_email_setting_sections', array( $this, 'register' ) );
	}

	public function register( $sections ) {
		$sections[] = $this->section;

		return $sections;
	}

	public function get_value() {
		$value = get_option( $this->section->option_name );

		return wp_parse_args( $value, $this->section->default_value );
	}

	abstract protected function initialize();

	protected function get_fields() {
		return $this->build_fields();
	}

	public function render() {
		return;
	}

	public function sanitize( $values ) {
		if ( ! is_array( $values ) ) {
			return array();
		}

		$values           = wp_parse_args( $values, $this->section->default_value );
		$sanitized_values = array();

		foreach ( $this->section->field_labels as $field_id => $label ) {
			$callback = array( $this, 'sanitize_' . $field_id );

			if ( is_callable( $callback ) ) {
				$sanitized_values[ $field_id ] = call_user_func( $callback, $values[ $field_id ] );
			} else {
				$sanitized_values[ $field_id ] = $values[ $field_id ];
			}
		}

		return apply_filters('check_email_settings_sanitize', $values, $sanitized_values);
	}

	protected function build_fields() {
		$fields = array();

		foreach ( $this->section->field_labels as $field_id => $label ) {
			$field           = new Check_Email_Log_Setting_Field();
			$field->id       = $field_id;
			$field->title    = $label;
			$field->args     = array( 'id' => $field_id, 'class' => 'check_email_'.$field_id );
			$field->callback = array( $this, 'render_' . $field_id . '_settings' );

			$fields[] = $field;
		}

		return $fields;
	}
}
