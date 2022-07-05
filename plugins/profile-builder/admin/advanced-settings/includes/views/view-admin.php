<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php
    $settings = get_option( 'wppb_toolbox_admin_settings' );
?>

<form method="post" action="options.php">

    <?php settings_fields( 'wppb_toolbox_admin_settings' ); ?>

    <table class="form-table">

        <tr>
            <th><?php esc_html_e( 'Allow users with the \'delete_users\' capability to view the Admin Approval list', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_admin_settings[admin-approval-access]"<?php echo ( ( isset( $settings['admin-approval-access'] ) && ( $settings['admin-approval-access'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'By checking this option, you will allow users that have the \'delete_users\' capability to access and use the Admin Approval list.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Allow users with the \'delete_users\' capability to view the list of Unconfirmed Emails', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_admin_settings[email-confirmation-access]"<?php echo ( ( isset( $settings['email-confirmation-access'] ) && ( $settings['email-confirmation-access'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'By checking this option, you will allow users that have the \'delete_users\' capability to see the list of Unconfirmed Email Addresses.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Disable confirmation dialog for the {{approval_url}} or {{approval_link}} Email Customizer tags', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_admin_settings[admin-approval-confirmation]"<?php echo ( ( isset( $settings['admin-approval-confirmation'] ) && ( $settings['admin-approval-confirmation'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'By checking this option, you will disable the confirmation dialog, allowing you to approve new users simply by visiting the <strong>{{approval_url}}</strong> or <strong>{{approval_link}}</strong>.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Multiple Admin Emails', 'profile-builder' ); ?></th>

            <td>
                <label>
                    <?php //don't remove the hidden, we need it so after save there is a value in the database for this, or else it might get set to yes becasue of the compatibility with Multiple Admin Emails addon ?>
                    <input type="hidden" name="wppb_toolbox_admin_settings[multiple-admin-emails]" value="">
                    <input type="checkbox" name="wppb_toolbox_admin_settings[multiple-admin-emails]"<?php echo ( ( isset( $settings['multiple-admin-emails'] ) && ( $settings['multiple-admin-emails'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes" class="wppb-toolbox-switch">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>
                <ul>
                    <li class="description">
                        <?php echo esc_html_e( 'Set multiple admin e-mail addresses that will receive e-mail notifications sent by Profile Builder', 'profile-builder' ); ?>
                    </li>
                </ul>

                <div class="wppb-toolbox-accordion">

                    <ul class="wppb-toolbox-list">

                        <li class="toolbox-label">
                            <strong><?php esc_html_e( 'Admin Emails:', 'profile-builder' ); ?></strong>
                        </li>
                        <li class="wppb-toolbox-admin-emails">
                            <input class="wppb-text widefat" type="text" name="wppb_toolbox_admin_settings[admin-emails]" value="<?php echo ( ( isset( $settings['admin-emails'] ) ) ? esc_attr( $settings['admin-emails'] ) : '' ); ?>" />
                        </li>
                        <li class="description">
                            <?php echo wp_kses_post( sprintf( __( 'Add email addresses, separated by comma, for people you wish to receive notifications from Profile Builder. These addresses will overwrite the default Email Address from <a href="%s">Settings -> General</a>', 'profile-builder' ), esc_url( get_site_url() ) . "/wp-admin/options-general.php" ) ); ?>
                        </li>
                    </ul>

                </div>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Disable Multiple User Roles', 'profile-builder' ); ?></th>
            <td>
                <label><input type="checkbox" name="wppb_toolbox_admin_settings[multiple-user-roles]"<?php echo ( ( isset( $settings['multiple-user-roles'] ) && ( $settings['multiple-user-roles'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>
                <ul>
                    <li class="description">
                        <?php esc_html_e( 'Activating this option will disable the ability to select multiple roles for a user.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

    </table>

    <?php submit_button( __( 'Save Changes', 'profile-builder' ) ); ?>

</form>
