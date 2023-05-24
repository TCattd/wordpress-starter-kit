<?php namespace CheckEmail\Core\UI\Page;

use CheckEmail\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Base class for all Check Email Log admin pages.
 */

abstract class Check_Email_BasePage implements Loadie {

	/**
	 * Current page.
	 *
	 * @var string
	 */
	protected $page;

	/**
	 * Current screen.
	 *
	 * @var \WP_Screen
	 */
	protected $screen;

	/**
	 * Register page.
	 *
	 * @return void
	 */
	abstract public function register_page();

	/**
	 * Setup hooks related to pages.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Return the WP_Screen object for the current page's handle.
	 *
	 * @return \WP_Screen Screen object.
	 */
	public function get_screen() {
		if ( ! isset( $this->screen ) ) {
			$this->screen = \WP_Screen::get( $this->page );
		}

		return $this->screen;
	}
}
