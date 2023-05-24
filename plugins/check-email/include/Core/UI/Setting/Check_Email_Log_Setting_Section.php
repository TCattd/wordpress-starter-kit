<?php namespace CheckEmail\Core\UI\Setting;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A Section used in Check Email Log Settings page.
 */
class Check_Email_Log_Setting_Section {

	public $id;
	public $title;
	public $callback;
	public $option_name;

	public $sanitize_callback;


	public $fields = array();

	public $default_value = array();

	public $field_labels = array();

	public function add_field( Check_Email_Log_Setting_Field $field ) {
		$this->fields[] = $field;
	}
}
