<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php $settings = get_option( 'wppb_toolbox_shortcodes_settings' ); ?>

<form method="post" action="options.php">

    <?php settings_fields( 'wppb_toolbox_shortcodes_settings' ); ?>

    <table class="form-table">

        <tr>
            <th><?php esc_html_e( 'Enable Compare shortcode', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_shortcodes_settings[compare]"<?php echo ( ( isset( $settings['compare'] ) && ( $settings['compare'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'You can read more info about this shortcode by following <a href="https://www.cozmoslabs.com/docs/profile-builder-2/developers-knowledge-base/shortcodes/compare-shortcode/">this url</a>.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Enable Usermeta shortcode', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_shortcodes_settings[usermeta]"<?php echo ( ( isset( $settings['usermeta'] ) && ( $settings['usermeta'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'You can read more info about this shortcode by following <a href="https://www.cozmoslabs.com/docs/profile-builder-2/developers-knowledge-base/shortcodes/display-user-meta/">this url</a>.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Enable Resend Activation Email shortcode', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_shortcodes_settings[resend-activation]"<?php echo ( ( isset( $settings['resend-activation'] ) && ( $settings['resend-activation'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'You can read more info about this shortcode by following <a href="https://www.cozmoslabs.com/docs/profile-builder-2/developers-knowledge-base/shortcodes/resend-confirmation-email/">this url</a>.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Enable Format Date shortcode', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_shortcodes_settings[format-date]"<?php echo ( ( isset( $settings['format-date'] ) && ( $settings['format-date'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'You can read more info about this shortcode by following <a href="https://www.cozmoslabs.com/docs/profile-builder-2/developers-knowledge-base/shortcodes/format-date-shortcode/">this url</a>.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

    </table>

    <?php submit_button( __( 'Save Changes', 'profile-builder' ) ); ?>

</form>
