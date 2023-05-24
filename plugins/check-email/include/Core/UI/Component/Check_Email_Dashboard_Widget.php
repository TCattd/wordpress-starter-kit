<?php namespace CheckEmail\Core\UI\Component;
use CheckEmail\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Check_Email_Dashboard_Widget implements Loadie {

	public function load() {
		add_action( 'wp_dashboard_setup', array( $this, 'register' ) );
	}

	public function register() {
		wp_add_dashboard_widget(
			'check_email_dashboard_widget',
			esc_html__( 'Check Email Summary', 'check-email' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Outputs the contents on the Dashboard Widget.
	 */
	public function render() {
		$email_log  = wpchill_check_email();
		$logs_count = $email_log->table_manager->get_logs_count();
		?>

		<p>
			<?php esc_html_e( 'Total number of emails logged' , 'check-email' ); ?>: <strong><?php echo number_format( absint( $logs_count ), 0, ',', ',' ); ?></strong>
		</p>

		<?php
			do_action( 'el_inside_dashboard_widget' );
		?>

		<ul class="subsubsub" style="float: none">
			<li><?php printf( wp_kses_post( __( '<a href="%s">Status</a>', 'check-email' ) ), 'admin.php?page=check-email-status' ); ?> <span style="color: #ddd"> | </span></li>
			<li><?php printf( wp_kses_post( __( '<a href="%s">Email Logs</a>', 'check-email' ) ), 'admin.php?page=check-email-logs' ); ?> <span style="color: #ddd"> | </span></li>
			<li><?php printf( wp_kses_post( __( '<a href="%s">Settings</a>', 'check-email' ) ), 'admin.php?page=check-email-settings' ); ?> <span style="color: #ddd"> | </span></li>
		</ul>

		<?php
	}
}
