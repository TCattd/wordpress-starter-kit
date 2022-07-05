<?php
/*
Extends the capabilites of Profile Builder by adding more settings under the Advanced Settings tab.
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('WPPB_toolbox') ){

    class WPPB_Advanced_Settings {

        public $tabs;
        public $advanced_settings_dir;
        protected $active_tab = 'forms';

        public function __construct() {

            $this->tabs = array(
                'forms'       => __( 'Forms', 'profile-builder' ),
                'fields'      => __( 'Fields', 'profile-builder' ),
                'userlisting' => __( 'Userlisting', 'profile-builder' ),
                'shortcodes'  => __( 'Shortcodes', 'profile-builder' ),
                'admin'       => __( 'Admin', 'profile-builder' ),
            );

            $this->advanced_settings_dir = plugin_dir_path( __FILE__ );

            $this->generate_settings();

            add_action( 'admin_menu',            array( &$this, 'register_submenu_page' ) );
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 9 );
            add_action( 'admin_init',            array( &$this, 'register_settings' ) );

            $this->setup_functions();
        }

        public function register_submenu_page() {
            add_submenu_page( 'profile-builder', __( 'Advanced Settings', 'profile-builder' ), __( 'Toolbox', 'profile-builder' ), 'manage_options', 'profile-builder-toolbox-settings', array( &$this, 'submenu_page_callback' ) );
        }

        public function submenu_page_callback() {
            reset( $this->tabs );

            if ( isset( $_GET['tab'] ) && array_key_exists( sanitize_text_field( $_GET['tab'] ), $this->tabs) )
                $this->active_tab = sanitize_text_field( $_GET['tab'] );
            ?>
            <div class="wrap wppb-wrap wppb-toolbox-wrap">
                <h2><?php esc_html_e( 'Advanced Settings', 'profile-builder'); ?></h2>

                <?php settings_errors(); ?>

                <?php wppb_generate_settings_tabs() ?>

                <?php
                    if ( file_exists( $this->advanced_settings_dir . 'includes/views/view-' . $this->active_tab . '.php' ) ) {
                        include_once 'includes/views/view-' . $this->active_tab . '.php';
                    }
                ?>
            </div>

            <?php
        }

        private function generate_settings() {
            add_option( 'wppb_toolbox_forms_settings',
                array(
                    'ec-bypass'                        => array(),
                    'restricted-email-domains-data'    => array(),
                    'restricted-email-domains-message' => __( 'The email address you are trying to register with is not allowed on this website.', 'profile-builder' ),
                )
            );

            add_option( 'wppb_toolbox_fields_settings',
                array(
                    'restricted-words-fields'  => array(),
                    'restricted-words-data'    => array(),
                    'restricted-words-message' => __( 'Your submission contains banned words.','profile-builder' ),
                )
            );

            add_option( 'wppb_toolbox_userlisting_settings', array() );
            add_option( 'wppb_toolbox_shortcodes_settings', array() );

            if( wppb_was_addon_active_as_plugin('pd-add-on-multiple-admin-e-mails/index.php') )
                $multiple_admin_emails = 'yes';
            else
                $multiple_admin_emails = '';

            //admin emails comes from the old addon Multiple Admin Emails
            $wppb_generalSettings = get_option('wppb_general_settings');
            if( isset( $wppb_generalSettings['admin_emails'] ) )
                $admin_emails = $wppb_generalSettings['admin_emails'];
            else
                $admin_emails = sanitize_email( get_option('admin_email') );


            add_option( 'wppb_toolbox_admin_settings',
                array(
                        'multiple-admin-emails' => $multiple_admin_emails,
                        'admin-emails' => $admin_emails
                )
            );

            //update cases here

            //this is for the migration of Multiple Admin Emails Add-on
            $wppb_toolbox_admin_settings = get_option('wppb_toolbox_admin_settings', array() );
            //make sure it's an array
            if( empty($wppb_toolbox_admin_settings) || !is_array($wppb_toolbox_admin_settings) )
                $wppb_toolbox_admin_settings = array();

            if( !isset( $wppb_toolbox_admin_settings['multiple-admin-emails'] ) || !isset( $wppb_toolbox_admin_settings['admin-emails'] ) ){

                if( !isset( $wppb_toolbox_admin_settings['multiple-admin-emails'] ) )
                    $wppb_toolbox_admin_settings['multiple-admin-emails'] = $multiple_admin_emails;

                if( !isset( $wppb_toolbox_admin_settings['admin-emails'] ) )
                    $wppb_toolbox_admin_settings['admin-emails'] = $admin_emails;

                update_option( 'wppb_toolbox_admin_settings', $wppb_toolbox_admin_settings );
            }

            //migrate Placeholder Labels Here
            if( wppb_was_addon_active_as_plugin('pb-add-on-placeholder-labels/pbpl.php') )
                $placeholder_labels = 'yes';
            else
                $placeholder_labels = '';

            $wppb_toolbox_form_settings = get_option('wppb_toolbox_forms_settings');
            if( !empty( $wppb_toolbox_form_settings ) ){

                if( !isset( $wppb_toolbox_form_settings['placeholder-labels'] ) )
                    $wppb_toolbox_form_settings['placeholder-labels'] = $placeholder_labels;

                update_option( 'wppb_toolbox_forms_settings', $wppb_toolbox_form_settings );
            }

        }

        public function register_settings() {
            register_setting( 'wppb_toolbox_forms_settings',       'wppb_toolbox_forms_settings', array( $this, 'sanitize_forms_settings' ) );
            register_setting( 'wppb_toolbox_fields_settings',      'wppb_toolbox_fields_settings' );
            register_setting( 'wppb_toolbox_userlisting_settings', 'wppb_toolbox_userlisting_settings' );
            register_setting( 'wppb_toolbox_shortcodes_settings',  'wppb_toolbox_shortcodes_settings' );
            register_setting( 'wppb_toolbox_admin_settings',       'wppb_toolbox_admin_settings', array( $this, 'sanitize_forms_settings' ) );
        }

        public function enqueue_scripts( $hook ) {
            if ( $hook == 'profile-builder_page_profile-builder-toolbox-settings' ) {
                wp_enqueue_script( 'wppb-select2', WPPB_PLUGIN_URL . 'assets/js/select2/select2.min.js', array(), PROFILE_BUILDER_VERSION );
                wp_enqueue_script( 'wppb-select2-compat', WPPB_PLUGIN_URL . 'assets/js/select2-compat.js', array(), PROFILE_BUILDER_VERSION );
                wp_enqueue_style( 'wppb-select2-style', WPPB_PLUGIN_URL . 'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION );
            }
        }


        public function sanitize_forms_settings( $settings ) {

            if( !empty( $settings['restricted-email-domains-data'] ) ){
                foreach( $settings['restricted-email-domains-data'] as $key => $email )
                    $settings['restricted-email-domains-data'][$key] = strtolower( $email );
            }

            /* Multiple Admin emails */
            if( isset( $settings['admin-emails'] ) && !empty( $settings['admin-emails'] ) ) {
                $invalid_email = false;
                $invalid_email_count = 0;

                $admin_emails = explode(',', $settings['admin-emails']);

                foreach( $admin_emails as $key => $admin_email ) {
                    if( !is_email( trim( $admin_email ) ) ) {
                        $invalid_email = true;
                        $invalid_email_count++;

                        unset( $admin_emails[$key] );
                    }
                }

                if( $invalid_email ) {
                    $settings['admin-emails'] = implode(',', $admin_emails );

                    if( $invalid_email_count === 1 ) {
                        $invalid_email_is_are = __('is', 'profile-builder');
                        $invalid_email_has_have = __('has', 'profile-builder');
                    } else {
                        $invalid_email_is_are = __('are', 'profile-builder');
                        $invalid_email_has_have = __('have', 'profile-builder');
                    }

                    add_settings_error( 'wppb_toolbox_admin_settings', 'invalid-email', sprintf( __( '%1$s of the emails provided in the Admin Emails field %2$s invalid and %3$s been removed from the list', 'profile-builder' ), $invalid_email_count, $invalid_email_is_are, $invalid_email_has_have ) );
                }
            }

            if( isset( $settings['redirect-delay-timer'] ) ){
                $settings['redirect-delay-timer'] = abs((int)$settings['redirect-delay-timer']);
            }

            if( isset( $settings['modify-permalinks-single'] ) )
                $settings['modify-permalinks-single'] = sanitize_text_field( $settings['modify-permalinks-single'] );

            if( empty( $settings['admin-emails'] ) )
                $settings['admin-emails'] = sanitize_email( get_option('admin_email') );

            return $settings;

        }

        private function setup_functions() {
            foreach( $this->tabs as $slug => $label ) {
                $settings = get_option( 'wppb_toolbox_' . $slug . '_settings', array() );

                if ( is_array( $settings ) ) {
                    foreach ( $settings as $key => $value ) {
                        if ( !empty( $value ) || ( $key == 'redirect-delay-timer' && $value == 0 ) ) {
                            $path = 'includes/' . $slug . '/' . $key . '.php';

                            if ( file_exists( $this->advanced_settings_dir . $path ) )
                                include_once $path;
                        }
                    }
                }
            }
        }

    }

    function wppb_advanced_settings_init() {
        if ( function_exists( 'wppb_return_bytes' ) ) {
            include 'includes/functions.php';

            new WPPB_Advanced_Settings;
        }
    }
    add_action( 'plugins_loaded', 'wppb_advanced_settings_init', 14 );
}
