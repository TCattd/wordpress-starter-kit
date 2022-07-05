<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php $settings = get_option( 'wppb_toolbox_userlisting_settings' ); ?>

<form method="post" action="options.php">

    <?php settings_fields( 'wppb_toolbox_userlisting_settings' ); ?>

    <table class="form-table">

        <tr>
            <th><?php esc_html_e( 'Change placeholder text for Search box', 'profile-builder' ); ?></th>

            <td>
                <input type="text" name="wppb_toolbox_userlisting_settings[search-placeholder-text]" value="<?php echo ( !empty( $settings['search-placeholder-text']) ? esc_attr( $settings['search-placeholder-text'] ) : '' ); ?>">

                <ul>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'This refers to the placeholder text from the <strong>{{{extra_search_all_fields}}}</strong> tag.', 'profile-builder' ) ); ?>
                    </li>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'Default text is <strong>Search Users by All Fields</strong>, use this option to change it to something else. Leave empty if you do not want to change it.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Modify base URL for Single Userlisting', 'profile-builder' ); ?></th>

            <td>
                <input type="text" name="wppb_toolbox_userlisting_settings[modify-permalinks-single]" value="<?php echo ( !empty( $settings['modify-permalinks-single']) ? esc_attr( $settings['modify-permalinks-single'] ) : '' ); ?>">

                <ul>
                    <li class="description">
                        <?php
                            echo wp_kses_post( __( 'By default Single Userlisting URLs contain the word <strong>user</strong>. eg.: ', 'profile-builder' ) );
                            echo esc_url( home_url( 'userlisting/user/123' ) );
                        ?>
                    </li>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'Using this option, you can change the word <strong>user</strong> to something else. Leave empty if you do not want to change it.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Make the Single Userlisting URLs work with user nicename', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_userlisting_settings[use-nicename-single]"<?php echo ( ( isset( $settings['use-nicename-single'] ) && ( $settings['use-nicename-single'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php
                            esc_html_e( 'By default Single Userlisting URLs are generated using the users ID. eg.: ', 'profile-builder' );
                            echo esc_url( home_url( 'userlisting/user/123' ) );
                        ?>
                    </li>
                    <li class="description">
                        <?php echo wp_kses_post( __( 'With this option activated, the URLs will be generated using the users <strong>nicename</strong>.', 'profile-builder' ) ); ?>
                    </li>
                </ul>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Remove repetition counts from Faceted Menus', 'profile-builder' ); ?></th>

            <td>
                <label><input type="checkbox" name="wppb_toolbox_userlisting_settings[remove-repetitions]"<?php echo ( ( isset( $settings['remove-repetitions'] ) && ( $settings['remove-repetitions'] == 'yes' ) ) ? ' checked' : '' ); ?> value="yes">
                    <?php esc_html_e( 'Yes', 'profile-builder' ); ?>
                </label>

                <ul>
                    <li class="description">
                        <?php esc_html_e( 'The number of users that share a particular value is shown for the Select and Checkbox facet types.', 'profile-builder' ); ?>
                    </li>
                    <li class="description">
                        <?php esc_html_e( 'If you enable this option they will be hidden.', 'profile-builder' ); ?>
                    </li>
                </ul>
            </td>
        </tr>

    </table>

    <?php submit_button( __( 'Save Changes', 'profile-builder' ) ); ?>

</form>
