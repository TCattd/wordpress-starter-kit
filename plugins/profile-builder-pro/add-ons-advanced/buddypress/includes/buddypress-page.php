<?php

/**
 * Function that registers the settings for the BuddyPress options page
 *
 * @since v1.0.0
 *
 * @return null
 */
function wppb_in_buddypress_register_settings() {
    register_setting( 'wppb_buddypress_settings', 'wppb_buddypress_settings', 'wppb_in_buddypress_settings_sanitize' );
}
if ( is_admin() ) {
    add_action('admin_init', 'wppb_in_buddypress_register_settings');
}


/**
 * Function that creates the "BuddyPress" submenu page
 *
 * @since v.1.0.0
 *
 * @return null
 */
function wppb_in_bdp_settings_submenu_page() {
    add_submenu_page( 'profile-builder', __( 'BuddyPress', 'profile-builder' ), __( 'BuddyPress', 'profile-builder' ), 'manage_options', 'profile-builder-buddypress', 'wppb_in_buddypress_settings_content' );
}
add_action( 'admin_menu', 'wppb_in_bdp_settings_submenu_page', 19 );  // this priority adds BuddyPress Sync below the Addons tab in PB menu


/**
 * Function that adds content to the "BuddyPress" submenu page
 *
 * @since v.1.0.0
 *
 * @return string
 */
function wppb_in_buddypress_settings_content() {
    ?>
    <div class="wrap wppb-wrap">
        <form method="post" action="options.php">
            <?php $wppb_buddypress_settings = get_option( 'wppb_buddypress_settings' ); ?>
            <?php settings_fields( 'wppb_buddypress_settings' ); ?>

            <h2>
                <?php esc_html_e( 'BuddyPress Integration', 'profile-builder' ); ?>
                <a href="https://www.cozmoslabs.com/docs/profile-builder-2/add-ons/buddypress/?utm_source=wpbackend&utm_medium=pb-documentation&utm_campaign=PBDocs" target="_blank" data-code="f223" class="wppb-docs-link dashicons dashicons-editor-help"></a>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Import BuddyPress Fields to Profile Builder', 'profile-builder' ); ?>
                    </th>
                    <td>
                        <a href="<?php echo esc_url( site_url( 'wp-admin/admin.php?page=profile-builder-bp-import-fields' ) ); ?>"> <input type="button" name="wppb_buddypress_import" value="<?php esc_html_e( 'Import BuddyPress Fields', 'profile-builder' ); ?>" class="button-primary"></a>
                        <p class="description"><?php //TODO link to documentation ?>
                            <?php _e( 'Create fields in Profile Builder that match the existing ones in BuddyPress and import all the user field entries.<br><a href="https://www.cozmoslabs.com/docs/profile-builder-2/add-ons/buddypress/#Import_BuddyPress_Fields_to_Profile_Builder">Learn more about importing BuddyPress fields</a>.', 'profile-builder' ); //phpcs:ignore ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Registration form:', 'profile-builder' ); ?>
                    </th>
                    <td>
                        <select name="wppb_buddypress_settings[RegistrationForm]" class="wppb-select wppb-bdp-settings">
                            <option value="wppb-default-register" <?php if ( $wppb_buddypress_settings['RegistrationForm'] == 'wppb-default-register' ) echo 'selected'; ?>><?php esc_html_e( 'Default Registration', 'profile-builder' ); ?></option>
                            <?php
                            $args = array(
                                'post_type' => 'wppb-rf-cpt',
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'posts_per_page' => '100'
                            );
                            $edit_profile_forms = get_posts( apply_filters( 'wppb_buddypress_registration_forms_args', $args) );
                            foreach ( $edit_profile_forms as $key => $value ){
                                echo '<option value="'. esc_attr( $value->post_title ).'"';
                                if ( $wppb_buddypress_settings['RegistrationForm'] == $value->post_title )
                                    echo ' selected';

                                echo '>' . esc_attr( $value->post_title ) . '</option>';
                            }
                            ?>

                        </select>

                        <p class="description">
                            <?php _e( 'Select Profile Builder Registration form to replace the BuddyPress Registration form.<br>Registration emails will now be managed in Profile Builder -> User Email Customizer', 'profile-builder' ); //phpcs:ignore ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Edit Profile form:', 'profile-builder' ); ?>
                    </th>
                    <td>
                        <select name="wppb_buddypress_settings[EditProfileForm]" class="wppb-select wppb-bdp-settings">
                            <option value="wppb-default-edit-profile" <?php if ( $wppb_buddypress_settings['EditProfileForm'] == 'wppb-default-edit-profile' ) echo 'selected'; ?>><?php esc_html_e( 'Default Edit Profile', 'profile-builder' ); ?></option>
                            <?php
                            $args = array(
                                'post_type' => 'wppb-epf-cpt',
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'posts_per_page' => '100'
                            );
                            $edit_profile_forms = get_posts( apply_filters( 'wppb_buddypress_edit_profile_forms_args', $args) );
                            foreach ( $edit_profile_forms as $key => $value ){
                                echo '<option value="'. esc_attr( $value->post_title ).'"';
                                if ( $wppb_buddypress_settings['EditProfileForm'] == $value->post_title )
                                    echo ' selected';

                                echo '>' . esc_attr( $value->post_title ) . '</option>';
                            }
                            ?>

                        </select>

                        <p class="description">
                            <?php esc_html_e( 'Select Profile Builder Edit Profile form to replace the BuddyPress Profile Edit tab.', 'profile-builder' ); ?>
                        </p>
                    </td>
                </tr>


                <?php // Check Profile Builder version, display User Listing only for Pro
                $versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

                if ( defined('PROFILE_BUILDER') && in_array( PROFILE_BUILDER, $versions ) ) { ?>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'User Listing template:', 'profile-builder' ); ?>
                        </th>
                        <td>
                            <select name="wppb_buddypress_settings[UserListing]" class="wppb-select wppb-bdp-settings">
                                <?php
                                $args = array(
                                    'post_type' => 'wppb-ul-cpt',
                                    'post_status' => 'publish',
                                    'orderby' => 'date',
                                    'order' => 'ASC',
                                    'posts_per_page' => '100'
                                );
                                $user_listings = get_posts( apply_filters( 'wppb_buddypress_user_listings_args', $args) );

                                foreach ( $user_listings as $key => $value ){
                                    echo '<option value="'. esc_attr( $value->post_title ) .'"';
                                    if ( isset($wppb_buddypress_settings['UserListing']) && ( $wppb_buddypress_settings['UserListing'] == $value->post_title ) )
                                        echo ' selected';

                                    echo '>' . esc_attr( $value->post_title ) . '</option>';
                                }
                                ?>

                            </select>

                            <p class="description">
                                <?php esc_html_e( 'Select which User Listing template managed by Profile Builder should replace the default BuddyPress user profile view.', 'profile-builder' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Use All-User Listing?', 'profile-builder' ); ?>
                        </th>
                        <td>
                            <input type="checkbox" name="wppb_buddypress_settings[AllUserListing]" value="yes" class="wppb-checkbox"
                                <?php  if ( isset($wppb_buddypress_settings['AllUserListing']) && ( $wppb_buddypress_settings['AllUserListing'] == 'yes' ) ){ echo 'checked'; }  ?>
                            >
                            <?php esc_html_e( 'Enable PB User Listing on BuddyPress Members page', 'profile-builder' ); ?>
                            <p class="description">
                                <?php _e( 'Check this if All User-listing managed via Profile Builder should replace the default BuddyPress Members page.<br>The template used will be the one selected above.', 'profile-builder' ); //phpcs:ignore ?>
                            </p>
                        </td>
                    </tr>
                <?php } ?>

                <?php do_action( 'wppb_extra_buddypress_settings', $wppb_buddypress_settings ); ?>
            </table>


            <p class="submit"><input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes' ); //phpcs:ignore ?>" /></p>
        </form>
    </div>

<?php
}


/**
 * Function that sanitizes the BuddyPress settings
 *
 * @param array $wppb_buddypress_settings
 *
 * @since v.1.0.0
 *
 * @return array $wppb_buddypress_settings sanitized
 */
function wppb_in_buddypress_settings_sanitize( $wppb_buddypress_settings ) {
    $wppb_buddypress_settings = apply_filters( 'wppb_buddypress_settings_sanitize_extra', array_map( 'sanitize_text_field', $wppb_buddypress_settings ) );
    return $wppb_buddypress_settings;
}


/**
 * Function that pushes settings errors to the user
 *
 * @since v.1.0.0
 *
 * #return null
 */
function wppb_in_buddypress_settings_admin_notices() {
    settings_errors( 'wppb_buddypress_settings' );
}
add_action( 'admin_notices', 'wppb_in_buddypress_settings_admin_notices' );
