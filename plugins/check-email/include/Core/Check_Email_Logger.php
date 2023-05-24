<?php namespace CheckEmail\Core;

/**
 * Log's emails sent through `wp_mail`.
 */
class Check_Email_Logger implements Loadie {

	public function load() {
		add_filter( 'wp_mail', array( $this, 'log_email' ) );
		add_action( 'wp_mail_failed', array( $this, 'on_email_failed' ) );

		/**
		 * These actions are required for logging BuddyPress emails, since BuddyPress does
		 * not use wp_mail for sending emails.
		 */
		add_action( 'bp_send_email_success', array( $this, 'log_buddy_press_email' ), 10, 2 );
		add_action( 'bp_send_email_failure', array( $this, 'log_buddy_press_email' ), 10, 2 );
	}

	/**
	 * Logs email to database.
	 */
	public function log_email( $original_mail_info ) {
        $option = get_option( 'check-email-log-core' );
        
        if ( is_array( $option ) && array_key_exists( 'enable_logs', $option ) && 'true' === strtolower( $option['enable_logs'] ) ) {
            $original_mail_info = apply_filters( 'check_email_wp_mail_log', $original_mail_info );

            $mail_info = wp_parse_args(
                    $original_mail_info,
                    array(
                            'to'          => '',
                            'subject'     => '',
                            'message'     => '',
                            'headers'     => '',
                            'attachments' => array(),
                    )
            );

            $ip = '';
            if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            	$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
            }

            $log = array(
                'to_email'        => \CheckEmail\Util\wp_chill_check_email_stringify( $mail_info['to'] ),
                'subject'         => esc_html($mail_info['subject']),
                'message'         => wp_kses_post($mail_info['message']),
                'headers'         => \CheckEmail\Util\wp_chill_check_email_stringify( $mail_info['headers'], "\n" ),
                'attachment_name' => \CheckEmail\Util\wp_chill_check_email_stringify( $mail_info['attachments'] ),
                'sent_date'       => current_time( 'mysql' ),
                'ip_address'      => $ip,
                'result'          => 1,
            );

            if ( empty( $log['attachment_name'] ) ) {
                    $log['attachments'] = 'false';
            } else {
                    $log['attachments'] = 'true';
            }

            $log = apply_filters( 'check_email_email_log_before_insert', $log, $original_mail_info );

            $check_email = wpchill_check_email();
            $check_email->table_manager->insert_log( $log );

            do_action( 'check_email_log_inserted' );
        }
        
        return $original_mail_info;
	}

	public function on_email_failed( $wp_error ) {
		if ( ! is_wp_error( $wp_error ) ) {
			return;
		}

		$mail_error_data = $wp_error->get_error_data( 'wp_mail_failed' );
		$mail_error_message = $wp_error->get_error_message( 'wp_mail_failed' );

		$this->mark_email_log_as_failed( $mail_error_data, $mail_error_message );
	}

	public function log_buddy_press_email( $status, $bp_mail ) {
		if ( ! class_exists( '\\BP_Email' ) ) {
			return;
		}

		if ( $bp_mail instanceof \BP_Email ) {
			return;
		}

		$log = array(
			'to'      => array_shift( $bp_mail->get_to() )->get_address(),
			'subject' => $bp_mail->get_subject( 'replace-tokens' ),
			'message' => $bp_mail->get_content( 'replace-tokens' ),
			'headers' => $bp_mail->get_headers( 'replace-tokens ' ),
		);

		$this->log_email( $log );

		if ( ! $status ) {
			$this->mark_email_log_as_failed( $log );
		}
	}

	protected function mark_email_log_as_failed( $log, $error_message = '' ) {
		if ( ! is_array( $log ) ) {
			return;
		}

		if ( ! isset( $log['to'], $log['subject'] ) ) {
			return;
		}

		$check_email = wpchill_check_email();

		$log_item_id = $check_email->table_manager->fetch_log_id_by_data( $log );

		if ( empty( $log_item_id ) ) {
			return;
		}

		$check_email->table_manager->mark_log_as_failed( $log_item_id, $error_message );
	}
}
