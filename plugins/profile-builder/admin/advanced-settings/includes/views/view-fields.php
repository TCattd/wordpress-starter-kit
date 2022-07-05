<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php
    $settings = get_option( 'wppb_toolbox_fields_settings' );
?>

<form method="post" action="options.php">

    <?php settings_fields( 'wppb_toolbox_fields_settings' ); ?>

    <table class="form-table">

        <tr>
            <th><?php esc_html_e( 'Automatically generate password for users', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_fields_settings[automatically-generate-password]"<?php echo ( ( isset( $settings['automatically-generate-password'] ) && ( $settings['automatically-generate-password'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'By checking this option, the password will be automatically generated and emailed to the user.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Modify \'Send Credentials\' checkbox', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" id="wppb-toolbox-send-credentials-hide" name="wppb_toolbox_fields_settings[send-credentials-hide]"<?php echo ( ( isset( $settings['send-credentials-hide'] ) && ( $settings['send-credentials-hide'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Hidden and checked', 'profile-builder' ); ?>
                </label>

                <ul class="wppb-toolbox-list wppb-toolbox-list--margin">
                    <li>
                        <strong><?php esc_html_e( 'Field text:', 'profile-builder' ); ?></strong>
                    </li>

                    <li id="toolbox-send-credentials-text">
                        <input type="text" name="wppb_toolbox_fields_settings[send-credentials-text]" value="<?php echo ( !empty( $settings['send-credentials-text']) ? esc_attr( $settings['send-credentials-text'] ) : '' ); ?>">
                    </li>
                </ul>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'By default, the user needs to choose if he wants to receive a registration email.', 'profile-builder' ); ?>
                    </li>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'By checking the <strong>Hidden and checked</strong> option, the field won\'t be shown and the message will always be sent to the user.', 'profile-builder' ) ); ?>
                    </li>
                    <li class="description">
                        <?php esc_html_e( 'If you choose to show the field, you can modify the default text by entering something in the field from above.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Redirect users to a page if they have empty required fields', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" id="wppb-toolbox-redirect-users-hide" name="wppb_toolbox_fields_settings[redirect-if-empty-required]"<?php echo ( ( isset( $settings['redirect-if-empty-required'] ) && ( $settings['redirect-if-empty-required'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul class="wppb-toolbox-list wppb-toolbox-list--margin" style="display:none">
                    <li>
                        <strong><?php esc_html_e( 'Redirect Page:', 'profile-builder' ); ?></strong>
                    </li>


                    <li id="toolbox-redirect-users-url">

                        <select name="wppb_toolbox_fields_settings[redirect-if-empty-required-url]">
                            <option value="-1"><?php esc_html_e( 'Choose...', 'profile-builder' ) ?></option>

                            <?php
                            foreach( get_pages() as $page )
                                echo '<option value="' . esc_attr( $page->ID ) . '"' . ( isset( $settings['redirect-if-empty-required-url'] ) ? selected( $settings['redirect-if-empty-required-url'], $page->ID, false ) : '') . '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                            ?>
                        </select>

                    </li>
                </ul>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'By activating this option, logged in users which have empty required fields on their profile will be redirected to the page you added above.', 'profile-builder' ); ?>
                    </li>
                    <li class="description">
                        <?php esc_html_e( 'For example, you can redirect these users to the Edit Profile form so they can add the missing info.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Ban certain words from being used in fields', 'profile-builder' ); ?></th>

            <td>
                <ul class="wppb-toolbox-list">
                    <li>
                        <label>
                            <input type="checkbox" name="wppb_toolbox_fields_settings[restricted-words]"<?php echo ( ( isset( $settings['restricted-words'] ) && ( $settings['restricted-words'] == 'on' ) ) ? ' checked' : '' ); ?> value="on" class="wppb-toolbox-switch">
                            <?php esc_html_e( 'On', 'profile-builder' ); ?>
                        </label>
                    </li>
                </ul>

                <div class="wppb-toolbox-accordion">
                    <ul class="wppb-toolbox-list">
                        <li class="toolbox-label">
                            <strong><?php esc_html_e( 'Affected fields:', 'profile-builder' ); ?></strong>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="wppb_toolbox_fields_settings[restricted-words-fields][]"<?php echo ( ( isset( $settings['restricted-words-fields'] ) && in_array( 'username', $settings['restricted-words-fields'] ) ) ? ' checked' : '' ); ?> value="username">
                                <?php esc_html_e( 'Username', 'profile-builder' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="wppb_toolbox_fields_settings[restricted-words-fields][]"<?php echo ( ( isset( $settings['restricted-words-fields'] ) && in_array( 'first-name', $settings['restricted-words-fields'] ) ) ? ' checked' : '' ); ?> value="first-name">
                                <?php esc_html_e( 'First Name', 'profile-builder' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="wppb_toolbox_fields_settings[restricted-words-fields][]"<?php echo ( ( isset( $settings['restricted-words-fields'] ) && in_array( 'last-name', $settings['restricted-words-fields'] ) ) ? ' checked' : '' ); ?> value="last-name">
                                <?php esc_html_e( 'Last Name', 'profile-builder' ); ?>
                            </label>
                        </li>
                    </ul>

                    <ul class="wppb-toolbox-list">
                        <li class="toolbox-label">
                            <strong><?php esc_html_e( 'Banned words:', 'profile-builder' ); ?></strong>
                        </li>
                        <li class="toolbox-select2-container">
                            <select id="toolbox-restricted-emails" class="wppb-select" name="wppb_toolbox_fields_settings[restricted-words-data][]" multiple="multiple">

                                <?php
                                if ( !empty( $settings['restricted-words-data'] ) ) {
                                    foreach( $settings['restricted-words-data'] as $domain ) {
                                        echo '<option value="'.esc_attr( $domain ).'" selected>'.esc_html( $domain ).'</option>';
                                    }
                                }
                                ?>

                            </select>
                        </li>
                    </ul>

                    <ul class="wppb-toolbox-list">
                        <li class="toolbox-label">
                            <strong><?php esc_html_e( 'Error message:', 'profile-builder' ); ?></strong>
                        </li>
                        <li id="toolbox-restricted-email-domains-message">
                            <input type="text" name="wppb_toolbox_fields_settings[restricted-words-message]" value="<?php echo ( !empty( $settings['restricted-words-message']) ? esc_attr( $settings['restricted-words-message'] ) : '' ); ?>">
                        </li>
                    </ul>

                </div>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'Allows you to define some words which users cannot use in their Username, First Name or Last Name when registering.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Unique \'Display Name\' for users', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_fields_settings[unique-display-name]"<?php echo ( ( isset( $settings['unique-display-name'] ) && ( $settings['unique-display-name'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'By checking this option, users will not be able to choose a <strong>Display Name</strong> that is already used by another account.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Always capitalize \'First Name\' and \'Last Name\' default fields', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_fields_settings[capitalize-first-last]"<?php echo ( ( isset( $settings['capitalize-first-last'] ) && ( $settings['capitalize-first-last'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'If you have these fields in your forms, they will be always saved with the first letter as uppercase.', 'profile-builder' ); ?>
                    </li>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'eg.: <strong>John Doe</strong> instead of <strong>john doe</strong>', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <?php if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/extra-fields.php' ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'Datepicker starts on Monday', 'profile-builder' ); ?></th>

                <td>
                    <label><input type="checkbox" name="wppb_toolbox_fields_settings[datepicker-starts-monday]"<?php echo ( ( isset( $settings['datepicker-starts-monday'] ) && ( $settings['datepicker-starts-monday'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                        <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                    </label>

                    <ul>
                        <li class="description">
                            <?php esc_html_e( 'Will make all Datepickers use Monday as the first day of the week.', 'profile-builder' ); ?>
                        </li>
                    </ul>
                </td>
            </tr>
        <?php endif; ?>


        <?php
        $wppb_module_settings = get_option( 'wppb_module_settings' );

        if ( $wppb_module_settings != false && isset( $wppb_module_settings['wppb_repeaterFields']) && $wppb_module_settings['wppb_repeaterFields'] == 'show' ) :
        ?>
            <tr>
                <th><?php esc_html_e( 'Hide Repeater Fields from the back-end profile page', 'profile-builder' ); ?></th>

                <td>
                    <label><input type="checkbox" name="wppb_toolbox_fields_settings[remove-repeater-fields]"<?php echo ( ( isset( $settings['remove-repeater-fields'] ) && ( $settings['remove-repeater-fields'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                        <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                    </label>

                    <ul>
                        <li class="description">
                            <?php esc_html_e( 'Repeater Fields from Profile Builder do not work on the back-end user profile page, they are just displayed. If you want to remove them completely, you can use this option.', 'profile-builder' ); ?>
                        </li>
                        <li class="description">
                            <?php esc_html_e( 'You will still be able to use them from a front-end edit profile form.', 'profile-builder' ); ?>
                        </li>
                    </ul>
                </td>
            </tr>
        <?php endif; ?>

            <tr>
                <th><?php esc_html_e( 'Show the Password field visibility toggle', 'profile-builder' ); ?></th>

                <td>
                    <label><input type="checkbox" name="wppb_toolbox_fields_settings[password-visibility-hide]"<?php echo ( ( isset( $settings['password-visibility-hide'] ) && ( $settings['password-visibility-hide'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                        <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                    </label>

                    <ul>
                        <li class="description">
                            <?php esc_html_e( 'Activating this option will show a visibility toggle button for all Password fields.', 'profile-builder' ); ?>
                        </li>
                    </ul>
                </td>
            </tr>

	    <?php if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/extra-fields.php' ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'Remove All Extra Fields from Backend edit profile page.', 'profile-builder' ); ?></th>

                <td>
                    <label><input type="checkbox" name="wppb_toolbox_fields_settings[remove-all-fields-from-backend]"<?php echo ( ( isset( $settings['remove-all-fields-from-backend'] ) && ( $settings['remove-all-fields-from-backend'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
					    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                    </label>

                    <ul>
                        <li class="description">
						    <?php esc_html_e( 'If you activate this option, it will remove all custom fields from the backend profile page created with Profile Builder.', 'profile-builder' ); ?>
                        </li>
                    </ul>
                </td>
            </tr>
	    <?php endif; ?>

        <?php if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/front-end/extra-fields/extra-fields.php' ) ) : ?>
            <tr>
                <th><?php esc_html_e( 'Update database entries when changing meta key', 'profile-builder' ); ?></th>

                <td>
                    <label><input type="checkbox" name="wppb_toolbox_fields_settings[update-db-meta-keys]"<?php echo ( ( isset( $settings['update-db-meta-keys'] ) && ( $settings['update-db-meta-keys'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                        <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                    </label>

                    <ul>
                        <li class="description">
                            <?php esc_html_e( 'If you activate this option, when changing the meta key of a field, existing entries from the database will be updated as well.', 'profile-builder' ); ?>
                        </li>
                    </ul>
                </td>
            </tr>
        <?php endif; ?>

    </table>

    <?php submit_button( __( 'Save Changes', 'profile-builder' ) ); ?>

</form>
