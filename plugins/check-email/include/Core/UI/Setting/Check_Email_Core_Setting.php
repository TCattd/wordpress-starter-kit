<?php namespace CheckEmail\Core\UI\Setting;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * All Check Email Log Core settings.
 */
class Check_Email_Core_Setting extends Check_Email_Setting {

	protected function initialize() {

			$this->section->page_slug   = 'check-email-settings';
			$this->section->id          = 'check-email-log-core';
			$this->section->option_name = 'check-email-log-core';

			$this->section->field_labels = array(
				'allowed_user_roles'      => esc_html__( 'Allowed User Roles', 'check-email' ),
				'remove_on_uninstall'     => esc_html__( 'Remove Data on Uninstall?', 'check-email' ),
				'override_emails_from'    => esc_html__( 'Override Emails From', 'check-email' ),
				'email_from_name'         => esc_html__( 'Change the "from" name.', 'check-email' ),
				'email_from_email'        => esc_html__( 'Change the "from" email.', 'check-email' ),
				'enable_logs'             => esc_html__( 'Enable Logs', 'check-email' ),
				'enable_dashboard_widget' => esc_html__( 'Enable Dashboard Widget', 'check-email' ),
				'db_size_notification'    => esc_html__( 'Database Size Notification', 'check-email' ),
			);

			$this->section->default_value = array(
				'allowed_user_roles'      => array(),
				'remove_on_uninstall'     => '',
				'email_from_name'         => '',
				'email_from_email'        => '',
				'override_emails_from'    => false,
				'enable_logs'             => false,
				'enable_dashboard_widget' => false,
				'db_size_notification'    => array(
					'notify'                    => false,
					'admin_email'               => '',
					'logs_threshold'            => '',
					'log_threshold_met'         => false,
					'threshold_email_last_sent' => false,
				)
			);

		$this->load();
	
	}

	public function load() {
		add_filter( 'check_email_setting_sections', array( $this, 'register' ), 9 );

		add_action( 'add_option_' . $this->section->option_name, array( $this, 'allowed_user_roles_added' ), 10, 2 );
		add_action( 'update_option_' . $this->section->option_name, array( $this, 'allowed_user_roles_changed' ), 10, 2 );

		add_action( 'check_email_log_inserted', array( $this, 'verify_email_log_threshold' ) );
		add_action( 'check_email_trigger_notify_email_when_log_threshold_met', array( $this, 'trigger_threshold_met_notification_email' ) );
	}

	public function render_allowed_user_roles_settings( $args ) {

		$option         = $this->get_value();
		$selected_roles = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . '][]';

		$available_roles = get_editable_roles();
		unset( $available_roles['administrator'] );
		?>

		<p>
			<input type="checkbox" checked disabled><?php esc_html_e( 'Administrator', 'check-email' ); ?>
		</p>

		<?php foreach ( $available_roles as $role_id => $role ) : ?>
			<p>
				<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $role_id ); ?>"
					<?php \CheckEmail\Util\wp_chill_check_email_array_checked( $selected_roles, $role_id ); ?>>

				<?php echo esc_html( translate_user_role( $role['name'] ) ); ?>
			</p>
		<?php endforeach; ?>

		<p>
			<em>
				<?php echo wp_kses_post( __( '<strong>Note:</strong> Users with the above User Roles can view Status and Logs Page.', 'check-email' ) ); ?>
				<?php esc_html_e( 'Administrator always has access and cannot be disabled.', 'check-email' ); ?>
			</em>
		</p>

		<?php
	}

	public function sanitize_allowed_user_roles( $roles ) {
		if ( ! is_array( $roles ) ) {
			return array();
		}

		return array_map( 'sanitize_text_field', $roles );
	}

	public function render_enable_logs_settings( $args ) {
		$option      = $this->get_value();
		$enable_logs = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		?>
            <input id="check-email-enable-logs" type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $enable_logs ); ?>>
            <?php esc_html_e( 'Check this box if you would like to log your emails.', 'check-email' ) ?>
            <?php
	}

        public function sanitize_enable_logs( $value ) {
		return sanitize_text_field( $value );
	}

	public function render_remove_on_uninstall_settings( $args ) {
		$option      = $this->get_value();
		$remove_data = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		?>

		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $remove_data ); ?>>
		<?php esc_html_e( 'Check this box if you would like to completely remove all of its data when the plugin is deleted.', 'check-email' ); ?>

		<?php
	}

	public function sanitize_remove_on_uninstall( $value ) {
		return sanitize_text_field( $value );
	}

	public function allowed_user_roles_added( $option, $value ) {
		$this->allowed_user_roles_changed( array(), $value );
	}

	public function allowed_user_roles_changed( $old_value, $new_value ) {
		$old_roles = $this->get_user_roles( $old_value );
		$new_roles = $this->get_user_roles( $new_value );

		do_action( 'check-email-log-list-manage-user-roles-changed', $old_roles, $new_roles );
	}

	protected function get_user_roles( $option ) {
		if ( ! array_key_exists( 'allowed_user_roles', $option ) ) {
			return array();
		}

		$user_roles = $option['allowed_user_roles'];
		if ( ! is_array( $user_roles ) ) {
			$user_roles = array( $user_roles );
		}

		return $user_roles;
	}

	public function render_enable_dashboard_widget_settings( $args ) {
		$option                  = $this->get_value();
		$enable_dashboard_widget = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		?>

		<input id="check-email-enable-widget" type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $enable_dashboard_widget ); ?>>
		<?php esc_html_e( 'Check this box if you would like to enable dashboard widget.', 'check-email' ); ?>

		<?php
	}

	public function render_db_size_notification_settings( $args ) {
		$option                    = $this->get_value();

		$db_size_notification_data = $option[ $args['id'] ];
		$defaults = array(
			'notify' => false,
			'logs_threshold' => 5000,
			'admin_email' => 'dev@email.com',
		);
		$db_size_notification_data = wp_parse_args( $db_size_notification_data, $defaults );
		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		// Since we store three different fields, give each field a unique name.
		$db_size_notification_field_name = $field_name . '[notify]';
		$admin_email_field_name          = $field_name . '[admin_email]';
		$logs_threshold_field_name       = $field_name . '[logs_threshold]';


		$check_email  = wpchill_check_email();
		$logs_count = $check_email->table_manager->get_logs_count();

		$admin_email_input_field = sprintf(
			'<input type="email" name="%1$s" value="%2$s" size="35" />',
			esc_attr( $admin_email_field_name ),
			empty( $db_size_notification_data['admin_email'] ) ? get_option( 'admin_email', '' ) : esc_attr( $db_size_notification_data['admin_email'] )
		);

		$logs_threshold_input_field = sprintf(
			'<input type="number" name="%1$s" placeholder="5000" value="%2$s" min="0" max="99999999" />',
			esc_attr( $logs_threshold_field_name ),
			empty( $db_size_notification_data['logs_threshold'] ) ? '' : esc_attr( $db_size_notification_data['logs_threshold'] )
		);
		?>

        <input id="check-email-enable-db-notifications" type="checkbox" name="<?php echo esc_attr( $db_size_notification_field_name ); ?>" value="true" <?php !isset( $db_size_notification_data['notify'] ) ? false :
		checked( 'true', $db_size_notification_data['notify'] ); ?> />
		<?php
		// The values within each field are already escaped.
		// phpcs:disable
		printf(
			esc_html__( 'Notify %1$s if there are more than %2$s logs.', 'check-email' ),
			$admin_email_input_field,
			$logs_threshold_input_field
		);
		// phpcs:enable
		?>
		<p>
			<em>
				<?php
				printf(
					esc_html__( '%1$s There are %2$s email logs currently logged in the database.', 'check-email' ),
					'<strong>' . esc_html__( 'Note', 'check-email' ) . ':</strong>',
					'<strong>' . esc_html( $logs_count ) . '</strong>'
				);
				?>
			</em>
		</p>
		<?php if ( ! empty( $db_size_notification_data['threshold_email_last_sent'] ) ) : ?>
			<p>
				<?php
				printf(
					esc_html__( 'Last notification email was sent on %1$s. Click %2$s button to reset sending the notification.', 'check-email' ),
					'<strong>' . esc_html( get_date_from_gmt( date( 'Y-m-d H:i:s', $db_size_notification_data['threshold_email_last_sent'] ), \CheckEmail\Util\wp_chill_check_email_get_user_defined_date_format() ) ) . '</strong>',
					'<b>Save</b>'
				);
				?>
			</p>
			<?php
		endif;
	}

	protected function restrict_array_to_db_size_notification_setting_keys( $arr ) {
		$allowed_keys = array_keys( $this->section->default_value['db_size_notification'] );
		$arr_keys     = array_keys( $arr );

		// Return the array when only the allowed keys exist.
		$intersecting_keys = array_intersect( $allowed_keys, $arr_keys );
		if ( count( $allowed_keys ) === count( $intersecting_keys ) ) {
			return $arr;
		}

		// Otherwise remove keys that aren't expected.
		$diff_keys = array_diff_key( $arr, $allowed_keys );
		foreach ( $diff_keys as $key ) {
			unset( $arr[ $key ] );
		}

		return $arr;
	}

	public function sanitize_db_size_notification( $db_size_notification_data ) {
		$db_size_notification_data = $this->restrict_array_to_db_size_notification_setting_keys( $db_size_notification_data );

		foreach ( $db_size_notification_data as $setting => $value ) {
			if ( 'notify' === $setting ) {
				$db_size_notification_data[ $setting ] = \filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( 'admin_email' === $setting ) {
				$db_size_notification_data[ $setting ] = \sanitize_email( $value );
			} elseif ( 'logs_threshold' === $setting ) {
				$db_size_notification_data[ $setting ] = absint( \sanitize_text_field( $value ) );
			}
		}

		// wp_parse_args won't merge nested array keys. So add the key here if it is not set.
		if ( ! array_key_exists( 'notify', $db_size_notification_data ) ) {
			$db_size_notification_data['notify'] = false;
		}
		if ( ! array_key_exists( 'log_threshold_met', $db_size_notification_data ) ) {
			$db_size_notification_data['log_threshold_met'] = false;
		}
		if ( ! array_key_exists( 'threshold_email_last_sent', $db_size_notification_data ) ) {
			$db_size_notification_data['threshold_email_last_sent'] = false;
		}

		return $db_size_notification_data;
	}

	public function verify_email_log_threshold() {
		$cron_hook = 'check_email_trigger_notify_email_when_log_threshold_met';
		if ( ! wp_next_scheduled( $cron_hook ) ) {
			wp_schedule_event( time(), 'hourly', $cron_hook );
		}
	}

	protected function has_array_contains_required_keys( $arr, $keys ) {
		$has_keys = true;

		if ( ! is_array( $arr ) || ! is_array( $keys ) ) {
			return false;
		}

		foreach ( $arr as $key => $value ) {
			$has_keys = $has_keys && in_array( $key, $keys, true );
		}

		return $has_keys;
	}

	public function trigger_threshold_met_notification_email() {
		$check_email = wpchill_check_email();
		$logs_count  = absint( $check_email->table_manager->get_logs_count() );

		$setting_data = $this->get_value();

		// Return early.
		if ( ! array_key_exists( 'db_size_notification', $setting_data ) ) {
			return;
		}

		$db_size_notification_key  = 'db_size_notification';
		$db_size_notification_data = $setting_data[ $db_size_notification_key ];

		// Return early.
		$keys = array_keys( $this->section->default_value['db_size_notification'] );
		if ( ! $this->has_array_contains_required_keys( $db_size_notification_data, $keys ) ) {
			return;
		}

		$is_notification_enabled = $db_size_notification_data['notify'];
		$admin_email             = $db_size_notification_data['admin_email'];
		$logs_threshold          = absint( $db_size_notification_data['logs_threshold'] );
		$logs_threshold_met      = $db_size_notification_data['log_threshold_met'];

		// Ideally threshold cannot be 0. Also, skip sending email if it is already sent.
		if ( 0 === $logs_threshold || true === $logs_threshold_met ) {
			return;
		}

		if ( $logs_count < $logs_threshold ) {
			return;
		}

		$this->register_threshold_met_admin_notice();

		if ( $is_notification_enabled && is_email( $admin_email ) ) {
			$subject = sprintf( esc_html__( 'Check & Log Email: Your log threshold of %s has been met', 'check-email' ), $logs_threshold );
			$message = <<<EOT
<p>This email is generated by the Check & Log Email plugin.</p>
<p>Your log threshold of $logs_threshold has been met. You may manually delete the logs to keep your database table in size.</p>
EOT;
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			$subject = apply_filters( 'check_email_log_threshold_met_notification_email_subject', $subject );

			$message = apply_filters( 'check_email_log_threshold_met_notification_email_body', $message, $logs_threshold );

			wp_mail( $admin_email, $subject, $message, $headers );

			$setting_data[ $db_size_notification_key ]['log_threshold_met']         = true;
			$setting_data[ $db_size_notification_key ]['threshold_email_last_sent'] = time();
			\update_option( $this->section->option_name, $setting_data );
		}
	}

	public function register_threshold_met_admin_notice() {
		add_action( 'admin_notices', array( $this, 'render_log_threshold_met_notice' ) );
	}

	public function render_log_threshold_met_notice() {
		$check_email    = wpchill_check_email();
		$logs_count     = absint( $check_email->table_manager->get_logs_count() );
		$notice_message = sprintf(
			esc_html__( 'Currently there are %1$s logged, which is more than the threshold. You can delete some logs or increase the threshold.', 'check-email' ),
			$logs_count . esc_html(_n( ' email log', ' email logs', $logs_count, 'check-email' ))
		);
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php echo wp_kses_post( $notice_message ); ?></p>
		</div>
		<?php
	}

	public function render_override_emails_from_settings( $args ){

		$option      = $this->get_value();
		$field_value = $option[ $args['id'] ];
		$field_name  = $this->section->option_name . '[' . $args['id'] . ']';
		?>
            <input id="check-email-overdide-from" type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $field_value ); ?>>
            <?php esc_html_e( 'Check this box if you would like override wordpress default from email and name.', 'check-email' ) ?>
		<?php

	}

	public function render_email_from_name_settings( $args ){

		$option      = $this->get_value();
		$field_value = $option[ $args['id'] ];
		$field_name  = $this->section->option_name . '[' . $args['id'] . ']';

		echo sprintf(
			'<input id="check-email-from_name" type="text" name="%s" value="%s" size="35" />',
			esc_attr( $field_name ),
			esc_attr( $field_value )
		);

	}
	

	public function render_email_from_email_settings( $args ){

		$option      = $this->get_value();
		$field_value = $option[ $args['id'] ];
		$field_name  = $this->section->option_name . '[' . $args['id'] . ']';

		echo sprintf(
			'<input id="check-email-from_email" type="email" name="%s" value="%s" size="35" />',
			esc_attr( $field_name ),
			esc_attr( $field_value )
		);
	}
}
