<?php namespace CheckEmail\Core\Request;

use CheckEmail\Core\Loadie;
use CheckEmail\Core\UI\Page\Check_Email_Log_List_Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check nonce for all Check Email Log requests.
 */
class Check_Email_Nonce_Checker implements Loadie {

	public function load() {
		add_action( 'admin_init', array( $this, 'check_nonce' ) );
	}

	public function check_nonce() {
		if ( ! isset( $_POST['check-email-action'] ) && ! isset( $_REQUEST['action'] ) && ! isset( $_REQUEST['action2'] ) ) {
			return;
		}

		if ( isset( $_POST['check-email-action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_POST['check-email-action'] ) );

			// $action is sanitize on line 23
			// phpcs:ignore
			if ( ! isset( $_POST[ $action . '_nonce' ] ) ) {
				return;
			}

			// $action is sanitize on line 23
			// phpcs:ignore
			if ( ! wp_verify_nonce( $_POST[ $action . '_nonce' ], $action ) ) {
				return;
			}
		}

		if ( isset( $_REQUEST['action'] ) || isset( $_REQUEST['action2'] ) ) {
			$action = sanitize_text_field( wp_unslash($_REQUEST['action']) );

			if ( '-1' === $action ) {
				if ( ! isset( $_REQUEST['action2'] ) ) {
					return;
				}

				$action = sanitize_text_field( wp_unslash($_REQUEST['action2']) );
			}

			// $action is sanitize on line 39 or 46
			// phpcs:ignore
			if ( strpos( $action, 'check-email-log-list-' ) !== 0 ) {
				return;
			}

			if ( ! isset( $_REQUEST[ Check_Email_Log_List_Page::LOG_LIST_ACTION_NONCE_FIELD ] ) ) {
				return;
			}
			
			// phpcs:ignore
			if ( ! wp_verify_nonce( $_REQUEST[ Check_Email_Log_List_Page::LOG_LIST_ACTION_NONCE_FIELD ], Check_Email_Log_List_Page::LOG_LIST_ACTION_NONCE ) ) {
				return;
			}
		}

		do_action( 'check_email_action', $action, $_REQUEST );
		do_action( $action, $_REQUEST );
	}
}
