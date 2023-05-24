<?php namespace CheckEmail\Core\UI\Setting;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A setting field used in Check Email Log Settings page.
 *
 * @see add_settings_field()
 */
class Check_Email_Log_Setting_Field {

	public $id;
	public $title;
	public $callback;
	public $args = array(); 
}
