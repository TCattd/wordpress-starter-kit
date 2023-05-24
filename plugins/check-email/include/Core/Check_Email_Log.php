<?php namespace CheckEmail\Core;

use CheckEmail\Core\DB\Check_Email_Table_Manager;
use CheckEmail\CheckEmailLogAutoloader;

/**
 * The main plugin class.
 */
class Check_Email_Log {

	const VERSION = '1.0.0';

	private $loaded = false;

	private $plugin_file;

	public $translations_path;

	public $loader;

	public $table_manager;

	private $loadies = array();

	public function __construct( $file, $loader, $table_manager ) {
		$this->plugin_file   = $file;
		$this->loader        = $loader;
		$this->table_manager = $table_manager;

		$this->add_loadie( $table_manager );

		$this->translations_path = dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' ;
	}

	public function add_loadie( $loadie ) {
		if ( $this->loaded ) {
			return false;
		}

		if ( ! $loadie instanceof Loadie ) {
			return false;
		}

		$this->loadies[] = $loadie;

		return true;
	}

	public function load() {
		if ( $this->loaded ) {
			return;
		}

		load_plugin_textdomain( 'check-email', false, $this->translations_path );

		$this->table_manager->load();

		foreach ( $this->loadies as $loadie ) {
			$loadie->load();
		}

		$this->loaded = true;

		do_action( 'check_email_loaded' );
	}

	public function get_version() {
		return self::VERSION;
	}

	public function get_plugin_path() {
		return plugin_dir_path( $this->plugin_file );
	}

	public function get_plugin_file() {
		return $this->plugin_file;
	}
}
