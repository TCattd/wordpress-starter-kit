<?php
/*
    * Function that registers the settings for the WooCommerce Sync options page
    *
    * @since v1.2.0
    *
    */
function wppb_in_woosync_register_settings() {
    register_setting( 'wppb_woosync_settings', 'wppb_woosync_settings', 'wppb_in_woosync_settings_sanitize' );
}
if ( is_admin() ) {
    add_action('admin_init', 'wppb_in_woosync_register_settings');
}

/**
 * Function that creates the "WooCommerce Sync" submenu page
 *
 * @since v.1.2.0
 *
 * @return void
 */
function wppb_in_woosync_settings_submenu_page() {
    add_submenu_page( 'profile-builder', __( 'WooCommerce Sync', 'profile-builder' ), __( 'WooCommerce Sync', 'profile-builder' ), 'manage_options', 'profile-builder-woocommerce-sync', 'wppb_in_woosync_settings_content' );
}
add_action( 'admin_menu', 'wppb_in_woosync_settings_submenu_page', 20 );  // this priority adds WooCommerce Sync below the Addons tab in PB menu

// set default values for WooCommerce Sync settings page
function wppb_in_woosync_settings_defaults(){
    $wppb_woosync_settings = get_option( 'wppb_woosync_settings', 'not_found' );

    $edit_profile_form = 'wppb-default-edit-profile';

    // backwords compatibility with v1.1.0 (where in Multiple Edit Profile forms, under form settings, you had a metabox with a checkbox "Add this form to My Account page?" to select which EP form to include on My Account page )
    $args = array(
        'post_type' => 'wppb-epf-cpt',
        'post_status' => 'publish',
    );
    $epf_cpt_array = get_posts($args);

    foreach ( $epf_cpt_array as $post ) {
        $woosync_meta = get_post_meta($post->ID, 'wppb_epf_woosync_settings', true);
        if ( isset($woosync_meta[0]['woosync-checkbox']) && ($woosync_meta[0]['woosync-checkbox'] == 'yes')) {
            $edit_profile_form = $post->post_title;
            break;
        }
    }

    // set default values
    if ( $wppb_woosync_settings == 'not_found' )
        update_option( 'wppb_woosync_settings', array( 'RegisterForm' => '', 'EditProfileForm' => $edit_profile_form ) );

}


/**
 * Function that adds content to the "WooCommerce Sync" submenu page
 *
 * @since v.1.2.0
 *
 * @return string
 */
function wppb_in_woosync_settings_content() {
    wppb_in_woosync_settings_defaults();
    ?>
    <div class="wrap wppb-wrap">
        <form method="post" action="options.php">
            <?php $wppb_woosync_settings = get_option( 'wppb_woosync_settings' ); ?>
            <?php settings_fields( 'wppb_woosync_settings' ); ?>

            <h2>
                <?php esc_html_e( 'WooCommerce Sync', 'profile-builder' ); ?>
                <a href="https://www.cozmoslabs.com/docs/profile-builder-2/add-ons/woocommerce-sync/?utm_source=wpbackend&utm_medium=pb-documentation&utm_campaign=PBDocs" target="_blank" data-code="f223" class="wppb-docs-link dashicons dashicons-editor-help"></a>
            </h2>

            <table class="form-table">

                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Choose Register form to display on My Account page:', 'profile-builder' ); ?>
                    </th>
                    <td>
                        <select name="wppb_woosync_settings[RegisterForm]" class="wppb-select">
                            <option value=""> <?php esc_html_e( 'None', 'profile-builder' ); ?></option>
                            <option value="wppb-default-register" <?php if ( $wppb_woosync_settings['RegisterForm'] == 'wppb-default-register' ) echo 'selected'; ?>><?php esc_html_e( 'Default Register', 'profile-builder' ); ?></option>
                            <?php
                            $args = array(
                                'post_type' => 'wppb-rf-cpt',
                                'post_status' => 'publish',
                                'numberposts' => -1,
                                'orderby' => 'date',
                                'order' => 'DESC'
                            );
                            $register_forms = get_posts( apply_filters( 'wppb_woosync_register_forms_args', $args) );

                            foreach ( $register_forms as $key => $value ){
                                echo '<option value="'. esc_attr( $value->post_title ).'"';
                                if ( $wppb_woosync_settings['RegisterForm'] === $value->post_title )
                                    echo ' selected';

                                echo '>' . esc_html( $value->post_title ) . '</option>';
                            }
                            ?>

                        </select>

                        <p class="description">
                            <?php printf( esc_html__( 'Select which Profile Builder Register form to display on My Account page from WooCommerce. %s This will also add the Profile Builder Login form to MyAccount page.', 'profile-builder' ), '<br>' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Choose Edit Profile form to display on My Account page:', 'profile-builder' ); ?>
                    </th>
                    <td>
                        <select name="wppb_woosync_settings[EditProfileForm]" class="wppb-select">
                            <option value=""> <?php esc_html_e( 'None', 'profile-builder' ); ?></option>
                            <option value="wppb-default-edit-profile" <?php if ( $wppb_woosync_settings['EditProfileForm'] == 'wppb-default-edit-profile' ) echo 'selected'; ?>><?php esc_html_e( 'Default Edit Profile', 'profile-builder' ); ?></option>
                            <?php
                            $args = array(
                                'post_type' => 'wppb-epf-cpt',
                                'post_status' => 'publish',
                                'numberposts' => -1,
                                'orderby' => 'date',
                                'order' => 'DESC'
                            );
                            $edit_profile_forms = get_posts( apply_filters( 'wppb_woosync_edit_profile_forms_args', $args) );

                            foreach ( $edit_profile_forms as $key => $value ){
                                echo '<option value="'.esc_attr( $value->post_title ).'"';
                                if ( $wppb_woosync_settings['EditProfileForm'] == $value->post_title )
                                    echo ' selected';

                                echo '>' . esc_html( $value->post_title ) . '</option>';
                            }
                            ?>

                        </select>

                        <p class="description">
                            <?php esc_html_e( 'Select which Profile Builder Edit-profile form to display on My Account page from WooCommerce.', 'profile-builder' ); ?>
                        </p>
                    </td>
                </tr>


                <?php do_action( 'wppb_extra_woocommerce_sync_settings', $wppb_woosync_settings ); ?>
            </table>


            <p class="submit"><input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'profile-builder' ); ?>" /></p>
        </form>
    </div>

<?php
}

/*
 * Function that sanitizes the WooCommerce Sync settings
 *
 * @param array $wppb_woosync_settings
 *
 * @since v.1.2.0
 */
function wppb_in_woosync_settings_sanitize( $wppb_woosync_settings ) {

    $wppb_woosync_settings = apply_filters( 'wppb_woosync_settings_sanitize_extra', $wppb_woosync_settings );

    return $wppb_woosync_settings;
}


/*
 * Function that pushes settings errors to the user
 *
 * @since v.1.2.0
 */
function wppb_in_woosync_settings_admin_notices() {
    settings_errors( 'wppb_woosync_settings' );
}
add_action( 'admin_notices', 'wppb_in_woosync_settings_admin_notices' );

