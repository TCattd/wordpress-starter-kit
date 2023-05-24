<?php namespace CheckEmail\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Status Page.
 */
class Check_Email_Status_Page extends Check_Email_BasePage {

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'check-email-status';

	/**
	 * Specify additional hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		parent::load();
                add_action( 'admin_enqueue_scripts', array( $this, 'checkemail_assets' ) );;
	}

	/**
	 * Register page.
	 */
	public function register_page() {

        add_menu_page(
            esc_html__( 'Check & Log Email', 'check-email' ),
            esc_html__( 'Check & Log Email', 'check-email' ),
            'manage_check_email',
            self::PAGE_SLUG,
            array( $this, 'render_page' ),
            'dashicons-email-alt',
            26
        );

		$this->page = add_submenu_page(
			Check_Email_Status_Page::PAGE_SLUG,
			esc_html__( 'Status', 'check-email' ),
			esc_html__( 'Status', 'check-email' ),
			'manage_check_email',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
            -10
		);
	}

	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Status', 'check-email' ); ?></h1>
            <?php
            global $current_user;
            global $phpmailer;

            $from_name = '';
            $from_email = apply_filters( 'wp_mail_from', $current_user->user_email );
            $from_name = apply_filters( 'wp_mail_from_name', $from_name );

            $headers = '';
            // phpcs:ignore
            if ( isset($_REQUEST['_wpnonce']) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'checkemail' ) && isset( $_POST['checkemail_to'] ) && isset( $_POST['checkemail_headers'] ) ) {
                $headers = $this->checkemail_send( sanitize_email( wp_unslash($_POST['checkemail_to']) ), sanitize_textarea_field(wp_unslash($_POST['checkemail_headers'])) );
            }
            ?>

            <div id="CKE_banner">
                <h2>
                    <img draggable="false" role="img" class="emoji" alt="👉" src="https://s.w.org/images/core/emoji/13.0.1/svg/1f449.svg">
                    <?php esc_html_e('Suggest a new feature!', 'check-email') ?>
                    <img draggable="false" role="img" class="emoji" alt="👈" src="https://s.w.org/images/core/emoji/13.0.1/svg/1f448.svg">
                </h2>
                <p><?php esc_html_e('Help us build the next set of features for Check & Log Email. Tell us what you think and we will make it happen!', 'check-email') ?></p>
                <a target="_blank" rel="noreferrer noopener" href="https://bit.ly/33QzqBU" class="button button-primary button-hero"><?php esc_html_e('Click here', 'check-email') ?></a>
            </div>

            <?php require_once 'partials/check-email-admin-status-display.php'; ?>
		</div>
		<?php
	}

    // send a test email
    private function checkemail_send( $to, $headers = "auto" ) {
            global $current_user;

            $from_name = '';
            $from_email = apply_filters( 'wp_mail_from', $current_user->user_email );
            $from_name = apply_filters( 'wp_mail_from_name', $from_name );

            if ( $headers == "auto" ) {
                    $headers = "MIME-Version: 1.0\r\n" .
                    "From: " . esc_html( $from_email ). "\r\n" .
                    "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n";
            } else {
                $break = chr( 10 );
                // nonce is validated in line 65.
                // phpcs:ignore
                if ( isset( $_POST['checkemail_break'] ) && '\r\n' == stripslashes( $_POST["checkemail_break"] ) ) {
                        $break = chr( 13 ) . chr( 10 );
                }

                $defaults = array(
                    'MIME-Version'  => '1.0',
                    'From'	        => esc_html( $from_email ),
                    'Cc'            => '',
                    'Content-Type'  => 'text/html; charset='.get_option('blog_charset')
                );

                $args = array();

                // nonce is validated in line 65.
                // phpcs:disable
                if ( isset( $_POST['checkemail_mime'] ) ) {
                    $args['MIME-Version'] = sanitize_text_field( wp_unslash($_POST['checkemail_mime']) );
                }

                if ( isset( $_POST['checkemail_from'] ) ) {
                    $args['From'] = sanitize_email( wp_unslash($_POST['checkemail_from']) );
                }

                if ( isset( $_POST['checkemail_cc'] ) ) {
                    $args['Cc'] = sanitize_email( wp_unslash($_POST['checkemail_cc']) );
                }

                if ( isset( $_POST['checkemail_type'] ) ) {
                    $args['Content-Type'] = sanitize_text_field( wp_unslash($_POST['checkemail_type']) );
                }
                // phpcs:enable

                $args = wp_parse_args( $args, $defaults );
                $headers = '';
                foreach ( $args as $key => $value ) {
                    $headers .= $key . ': ' . $value . $break;
                }
               
            }

            $title = sprintf( esc_html__( "Test email from %s ", "check-email"), esc_url( get_bloginfo( "url" ) ) );
            $body  = sprintf( esc_html__( 'This test email proves that your WordPress installation at %1$s can send emails.\n\nSent: %2$s', "check-email" ), esc_url( get_bloginfo( "url" ) ), date( "r" ) );
            wp_mail( $to, $title, $body, $headers );

            return $headers;
    }

    public function checkemail_assets() {
		$check_email    = wpchill_check_email();
		$plugin_dir_url = plugin_dir_url( $check_email->get_plugin_file() );
		wp_enqueue_style( 'checkemail-css', $plugin_dir_url . 'assets/css/admin/checkemail.css', array(), $check_email->get_version() );
		wp_enqueue_script( 'checkemail', $plugin_dir_url . 'assets/js/admin/checkemail.js', array( 'jquery', 'updates' ), $check_email->get_version(), true );
	}
}
