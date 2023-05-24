<?php
// we have a nonce but we don't process the form here
// phpcs:ignoreFile

$email_headers   = isset( $_POST["checkemail_headers"] ) ? sanitize_text_field( wp_unslash($_POST["checkemail_headers"]) ) : 'auto';
$checkemail_to   = isset( $_POST["checkemail_to"] ) ? sanitize_email( wp_unslash( $_POST["checkemail_to"] ) ) : '';
$checkemail_mime = isset( $_POST['checkemail_mime'] ) ? sanitize_text_field( wp_unslash($_POST['checkemail_mime']) ) : '1.0';
$checkemail_type = isset( $_POST['checkemail_type'] ) ? sanitize_text_field( wp_unslash($_POST['checkemail_type']) ) : 'text/html; charset=iso-8859-1';
$checkemail_from = isset( $_POST['checkemail_from'] ) ? sanitize_email( wp_unslash( $_POST['checkemail_from'] ) ) : $from_email;
$checkemail_cc   = isset( $_POST['checkemail_cc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['checkemail_cc'] ) ) : '';
$checkemail_break = isset( $_POST['checkemail_break'] ) ? sanitize_text_field( wp_unslash( $_POST['checkemail_break'] ) ) : '';

?>

<div id="checkemail" class="wrap">
    <?php if ( isset( $_POST["checkemail_to"]) && ! empty( $_POST["checkemail_to"] ) ): ?>
            <div class="updated">
                <?php if ( ! empty( $headers ) ): ?>
                    <p><?php esc_html_e( 'The test email has been sent by WordPress. Please note this does NOT mean it has been delivered. See', 'check_email' );?>
                    <a href=<? echo esc_url( "http://codex.wordpress.org/Function_Reference/wp_mail")?>> <?php esc_html_e( "wp_mail in the Codex", "check-email") ?></a> <?php esc_html_e( "for more information. The headers sent were :", 'check-email' ) ?>
                    </p>

                    <pre><?php echo esc_textarea( str_replace( chr( 10 ), '\n' . "\n", str_replace( chr( 13 ), '\r', $headers ) ) ); ?></pre>
                <?php else: ?>
                    <p><?php esc_html_e( 'Security check failed', 'check-email' ) ?></p>
                <?php endif; ?>
            </div>
    <?php endif; ?>
    <h2><?php esc_html_e( 'Check & Log Email', 'check-email' ) ?></h2><hr />

    <?php if ( $phpmailer ) { ?>
    
    <h3><?php esc_html_e( 'Current mail settings', 'check-email' ) ?></h3>
    <ul>
        <?php if (isset($phpmailer->Mailer) && !empty($phpmailer->Mailer)): ?>
        <li><?php esc_html_e( 'Mailer:', 'check-email' ) ?> <strong><?php echo esc_html( $phpmailer->Mailer ) ?></strong></li>
        <?php endif; ?>
        <?php if (isset($phpmailer->Sendmail) && !empty($phpmailer->Sendmail)): ?>
        <li><?php esc_html_e( 'SendMail path:', 'check-email' ) ?> <strong><?php echo esc_html( $phpmailer->Sendmail ) ?></strong></li>
        <?php endif; ?>
        <?php if (isset($phpmailer->Host) && !empty($phpmailer->Host)): ?>
        <li><?php esc_html_e( 'Host:', 'check-email' ) ?> <strong><?php echo esc_html( $phpmailer->Host ) ?></strong></li>
        <?php endif; ?>
        <?php if (isset($phpmailer->Port) && !empty($phpmailer->Port)): ?>
        <li><?php esc_html_e( 'Port:', 'check-email' ) ?> <strong><?php echo esc_html( $phpmailer->Port ) ?></strong></li>
        <?php endif; ?>
        <?php if (isset($phpmailer->CharSet) && !empty($phpmailer->CharSet)): ?>
        <li><?php esc_html_e( 'CharSet:', 'check-email' ) ?> <strong><?php echo esc_html( $phpmailer->CharSet ) ?></strong></li>
        <?php endif; ?>
        <?php if (isset($phpmailer->ContentType) && !empty($phpmailer->ContentType)): ?>
        <li><?php esc_html_e( 'Content-Type:', 'check-email' ) ?> <strong><?php echo esc_html( $phpmailer->ContentType ) ?></strong></li>
        <?php endif; ?>
    </ul>

    <?php } ?>

    <h3><?php esc_html_e( 'Send a test email', 'check-email' ) ?></h3><hr />
    <form action="<?php echo esc_url( get_admin_url() ) . 'admin.php?page=check-email-status' ?>" method="post">
        <p>
            <label for="checkemail_to"><?php esc_html_e( 'Send test email to', 'check-email' ); ?></label>
            <input type="text" name="checkemail_to" id="checkemail_to" class="text" value="<?php echo esc_attr( $checkemail_to ) ?>"/>
        </p>
        <p>
            <label for="checkemail_autoheaders"><?php esc_html_e( 'Use standard headers', 'check-email' ) ?></label>
            <?php  ?>
            <input type="radio" id="checkemail_autoheaders" name="checkemail_headers" value="auto" <?php checked( $email_headers, 'auto' ) ?>/>
        </p>
        <div id="autoheaders" class="<?php echo ( $email_headers == 'custom' ? 'checkemail-hide' : '' ) ?>">
            <?php printf( esc_html__( 'MIME-Version: %s', 'check-email'), '1.0' ) ?><br />
            <?php printf( esc_html__( 'From: %s', 'check-email'), esc_html( $from_email ) ) ?><br />
            <?php printf( esc_html__( 'Content-Type: %s', 'check-email'), 'text/plain; charset="' . esc_html( get_option( 'blog_charset' ) ) . '"' ) ?>
        </div>
        <p>
            <label for='checkemail_customheaders'><?php esc_html_e( 'Use custom headers', 'check-email' ) ?></label>
            <input type="radio" id="checkemail_customheaders" name="checkemail_headers" value="custom" <?php echo (isset($_POST['checkemail_headers']) && $_POST['checkemail_headers'] == 'custom' ? 'checked="checked"' : '') ?>/>
        </p>
        <div id="customheaders" class="<?php echo ( !isset($_POST['checkemail_headers']) || $_POST['checkemail_headers'] == 'auto' ? 'checkemail-hide' : '' ) ?>"><br />
            <h3><?php esc_html_e( 'Set your custom headers below', 'check-email' ) ?></h3><hr />
            <p>
                <label for="checkemail_mime"><?php esc_html_e( 'MIME Version', 'check-email' ) ?></label>
                <input type="text" name="checkemail_mime" id="checkemail_mime" value="<?php echo esc_attr( $checkemail_mime ) ?>"/>
            </p>
            <p>
                <label for="checkemail_type"><?php esc_html_e( 'Content type', 'check-email' ) ?></label> 
                <input type="text" name="checkemail_type" id="checkemail_type" value="<?php echo esc_attr( $checkemail_type ) ?>"/>
            </p>
            <p>
                <label for="checkemail_from"><?php esc_html_e( 'From', 'check-email' ) ?></label>
                <input type="text" name="checkemail_from" id="checkemail_from" value="<?php echo esc_attr( $checkemail_from ) ?>" class="text" />
            </p>
            <p>
                <label for="checkemail_cc"><?php esc_html_e( 'CC', 'check-email' ) ?></label> 
                <textarea name="checkemail_cc" id="checkemail_cc" cols="30" rows="4" class="text"><?php echo esc_textarea( $checkemail_cc ) ?></textarea>
            </p>
            <p>
                <label for="checkemail_break_n"><?php esc_html_e( 'Header line break type', 'check-email' ) ?></label>
                <input type="radio" name="checkemail_break" id="checkemail_break_n" value="\n" <?php checked( $checkemail_break, '\n' ) ?>/><?php esc_html_e('\n', 'check-email') ?>
                <input type="radio" name="checkemail_break" id="checkemail_break_rn" value="\r\n" <?php checked( $checkemail_break, '\r\n' ) ?>/><?php esc_html_e('\r\n', 'check-email') ?>
            </p>
        </div>

        <?php wp_nonce_field( 'checkemail' ); ?>

        <p>
            <label for="checkemail_go" class="checkemail-hide"><?php esc_html_e( 'Send', 'check-email' ) ?></label>
            <input type="submit" name="checkemail_go" id="checkemail_go" class="button-primary" value="<?php esc_attr_e( 'Send test email', 'check-email' ) ?>" />
        </p>
    </form>
</div>
