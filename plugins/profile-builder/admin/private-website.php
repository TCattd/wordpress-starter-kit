<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Function that creates the "Private Website" submenu page
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_private_website_submenu_page() {
    add_submenu_page( null, __( 'Private Website', 'profile-builder' ), __( 'Private Website', 'profile-builder' ), 'manage_options', 'profile-builder-private-website', 'wppb_private_website_content' );
}
add_action( 'admin_menu', 'wppb_private_website_submenu_page' );


/**
 * Function that generates the default settings for private page
 */
function wppb_private_website_settings_defaults() {

    add_option( 'wppb_private_website_settings',
        array(
            'private_website'       =>  'no',
            'redirect_to'           =>  '',
            'allowed_pages'         =>  array(),
            'hide_menus'            =>  'no',
            'disable_rest_api'      =>  'yes',
        )
    );

}

/**
 * Function that generates the content for the settings page
 */
function wppb_private_website_content() {

    wppb_private_website_settings_defaults();

    $wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );

    $args = array(
        'post_type'         => 'page',
        'posts_per_page'    => -1
    );

    if( function_exists( 'wc_get_page_id' ) )
        $args['exclude'] = wc_get_page_id( 'shop' );

    $all_pages = get_posts( $args );
    ?>
    <div class="wrap wppb-wrap wppb-private-website">
        <h2><?php esc_html_e( 'Private Website Settings', 'profile-builder' );?></h2>

        <?php settings_errors(); ?>

        <?php wppb_generate_settings_tabs() ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'wppb_private_website_settings' ); ?>

            <table class="form-table">
                <tbody>

                    <tr>
                        <th><?php esc_html_e( 'Enable Private Website', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-enable" class="wppb-select" name="wppb_private_website_settings[private_website]">
                                <option value="no" <?php echo ( ( $wppb_private_website_settings != 'not_found' && $wppb_private_website_settings['private_website'] == 'no' ) ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'profile-builder' ); ?></option>
                                <option value="yes" <?php echo ( ( $wppb_private_website_settings != 'not_found' && $wppb_private_website_settings['private_website'] == 'yes' ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'profile-builder' ); ?></option>
                            </select>
                            <ul>
                                <li class="description"><?php esc_html_e( 'Activate Private Website. It will restrict the content, RSS and REST API for your website', 'profile-builder' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Redirect to', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-redirect-to-login" class="wppb-select" name="wppb_private_website_settings[redirect_to]">
                                <option value=""><?php esc_html_e( 'Default WordPress login page', 'profile-builder' ); ?></option>
                                <?php
                                if( !empty( $all_pages ) ){
                                    foreach ($all_pages as $page){
                                        ?>
                                        <option value="<?php echo esc_attr( $page->ID ) ?>" <?php echo ( ( $wppb_private_website_settings != 'not_found' && isset( $wppb_private_website_settings['redirect_to'] ) && $wppb_private_website_settings['redirect_to'] == $page->ID ) ? 'selected' : '' ); ?>><?php echo esc_html( $page->post_title ) ?></option>
                                        <?php
                                    }
                                }
                                ?>

                            </select>
                            <ul>
                                <li class="description"><?php esc_html_e( 'Redirects to this page if not logged in. We recommend this page contains the [wppb-login] shortcode.', 'profile-builder' ); ?></li>
                                <li class="description"><?php esc_html_e( 'You can force access to wp-login.php so you don\'t get locked out of the site by accessing the link:', 'profile-builder' ); ?> <a href="<?php echo esc_url( wp_login_url() ).'?wppb_force_wp_login=true' ?>"><?php echo esc_url( wp_login_url() ).'?wppb_force_wp_login=true' ?></a></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Allowed Pages', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-allowed-pages" class="wppb-select" name="wppb_private_website_settings[allowed_pages][]" multiple="multiple">
                                <?php
                                if( !empty( $all_pages ) ){
                                    foreach ($all_pages as $page){
                                        ?>
                                        <option value="<?php echo esc_attr( $page->ID ) ?>" <?php echo ( ( $wppb_private_website_settings != 'not_found' && isset( $wppb_private_website_settings['allowed_pages'] ) && in_array( $page->ID, $wppb_private_website_settings['allowed_pages'] ) ) ? 'selected' : '' ); ?>><?php echo esc_html( $page->post_title ) ?></option>
                                        <?php
                                    }
                                }
                                ?>

                            </select>
                            <ul>
                                <li class="description"><?php esc_html_e( 'Allow these pages to be accessed even if you are not logged in', 'profile-builder' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Allowed Paths', 'profile-builder' ); ?></th>
                        <td>
                            <textarea id="private-website-allowed-paths" class="wppb-textarea" name="wppb_private_website_settings[allowed_paths]"><?php echo ( ( $wppb_private_website_settings != 'not_found' && !empty($wppb_private_website_settings['allowed_paths']) ) ? esc_textarea( $wppb_private_website_settings['allowed_paths'] ) : '' ); ?></textarea>
                            <ul>
                                <li class="description"><?php esc_html_e( 'Allow these paths to be accessed even if you are not logged in (supports wildcard at the end of the path). For example to exclude https://example.com/some/path/ you can either use the rule /some/path/ or /some/* Enter each rule on it\'s own line', 'profile-builder' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Hide all Menus', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-menu-hide" class="wppb-select" name="wppb_private_website_settings[hide_menus]">
                                <option value="no" <?php echo ( ( $wppb_private_website_settings != 'not_found' && !empty($wppb_private_website_settings['hide_menus']) && $wppb_private_website_settings['hide_menus'] == 'no' ) ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'profile-builder' ); ?></option>
                                <option value="yes" <?php echo ( ( $wppb_private_website_settings != 'not_found' && !empty($wppb_private_website_settings['hide_menus']) && $wppb_private_website_settings['hide_menus'] == 'yes' ) ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'profile-builder' ); ?></option>
                            </select>
                            <ul>
                                <li class="description"><?php esc_html_e( 'Hide all menu items if you are not logged in.', 'profile-builder' ); ?></li>
                                <li class="description"><?php wp_kses_post( printf( __( 'We recommend "<a href="%s" target="_blank">Custom Profile Menus</a>" addon if you need different menu items for logged in / logged out users.', 'profile-builder' ), 'https://www.cozmoslabs.com/add-ons/custom-profile-menus/' ) )    //phpcs:ignore; ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Disable REST-API', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-disable-rest-api" class="wppb-select" name="wppb_private_website_settings[disable_rest_api]">
                                <option value="yes" <?php selected ( ( $wppb_private_website_settings != 'not_found' && ( !isset( $wppb_private_website_settings[ 'disable_rest_api' ] ) || ( isset( $wppb_private_website_settings[ 'disable_rest_api' ] ) && $wppb_private_website_settings[ 'disable_rest_api' ] == 'yes' ) ) ), true ); ?>><?php esc_html_e( 'Yes', 'profile-builder' ); ?></option>
                                <option value="no" <?php selected ( ( $wppb_private_website_settings != 'not_found' && isset( $wppb_private_website_settings[ 'disable_rest_api' ] ) && $wppb_private_website_settings[ 'disable_rest_api' ] == 'no' ), true ); ?>><?php esc_html_e( 'No', 'profile-builder' ); ?></option>
                            </select>
                            <ul>
                                <li class="description"><?php esc_html_e( 'Disable the WordPress REST-API for non-logged in users when Private Website is enabled', 'profile-builder' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                </tbody>
            </table>

            <?php submit_button( esc_html__( 'Save Changes', 'profile-builder' ) ); ?>
        </form>


    </div>
    <?php
}
