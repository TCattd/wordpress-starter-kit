<?php

class WPPB_Two_Factor_Authenticator {

    public function __construct( ) {
        if ( ! class_exists('WPPB_Base32') ) {
            require_once( WPPB_PAID_PLUGIN_DIR.'/features/two-factor-authentication/assets/lib/class-WPPBBase32.php' );
        }

        add_action( 'admin_menu',                                       array( $this, 'add_settings_tab' ) );

	    $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );

	    $enabled = 'no';
	    if ( !empty( $wppb_two_factor_authentication_settings['enabled'] ) ) {
		    $enabled = $wppb_two_factor_authentication_settings['enabled'];
	    }

        if ( $enabled === 'yes' ) {

	        add_filter( 'wp_authenticate_user', array( $this, 'login_error_message_handler' ), 10, 2 );
	        add_filter( 'wp_login_errors', array( $this, 'back_end_errors_filter' ), 10, 2 );

	        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		        add_action( 'wp_ajax_WPPBAuth_new_secret', array( $this, 'ajax_new_secret' ) );
		        add_action( 'wp_ajax_WPPBAuth_check_code', array( $this, 'ajax_check_code' ) );
		        add_action( 'wp_ajax_nopriv_WPPBAuth_field_on_login_form', array(
			        $this,
			        'ajax_add_auth_field_to_login_form'
		        ) );
	        }

	        add_action( 'show_user_profile', array( $this, 'add_field_to_backend_edit_profile_form' ) );
	        add_action( 'edit_user_profile', array( $this, 'add_field_to_backend_edit_profile_form' ) );
	        add_action( 'wppb_backend_save_form_field', array( $this, 'handle_backend_edit_profile_update' ), 10, 4 );

	        add_filter( 'wppb_filter_form_args_before_output', array(
		        $this,
		        'add_field_to_frontend_edit_profile_form'
	        ) );
	        add_filter( 'wppb_output_form_field_two-factor-authentication', array(
		        $this,
		        'frontend_edit_profile_field'
	        ), 10, 6 );
	        add_action( 'wppb_after_saving_form_values', array( $this, 'handle_frontend_edit_profile_update' ), 10, 2 );

	        add_filter( 'wppb_form_fields', array( $this, 'add_field_to_pb_field_validation_sequence' ), 10, 2 );
	        add_filter( 'wppb_check_form_field_two-factor-authentication', array(
		        $this,
		        'form_field_validation'
	        ), 10, 4 );

	        add_action( 'login_footer', array( $this, 'add_field_to_backend_login_form' ) );
	        add_action( 'login_form_bottom', array( $this, 'add_field_to_frontend_login_form' ), 10, 2 );
        }
    }

    /**
     * Handle 2FA scripts
     */
    function enqueue_2fa_scripts( ) {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wppb_qrcode_script', plugin_dir_url( __FILE__ ) .'assets/js/jquery.qrcode.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
        wp_enqueue_script( 'wppb_2fa_script', plugin_dir_url( __FILE__ ) .'assets/js/wppb-2fa.js', array( 'jquery', 'wppb_qrcode_script' ), PROFILE_BUILDER_VERSION );
        wp_localize_script( 'wppb_2fa_script', 'wppb_2fa_script_vars', array(
                'WPPBAuthNonce' => wp_create_nonce( 'WPPBAuthaction' ),
                'ajaxurl'       => admin_url( 'admin-ajax.php' ),
                'valid'         => __('Valid', 'profile-builder' ),
                'invalid'       => __('Invalid', 'profile-builder' ),
            )
        );
    }

    /**
     * Add the Settings page tab for 2FA
     */
    function add_settings_tab( ) {
        add_submenu_page( 'profile-builder', __( 'Two-Factor Authentication', 'profile-builder' ), __( 'Two-Factor Authentication', 'profile-builder' ), 'manage_options', 'profile-builder-two-factor-authentication', array( $this, 'settings_tab_content' ) );
    }

    /**
     * Populate the Settings page tab for 2FA
     */
    function settings_tab_content( ) {
        add_option( 'wppb_two_factor_authentication_settings',
            array(
                'enabled'       => 'no',
                'roles'         => array( ),
            )
        );

        $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );

        $enabled = 'no';
        if ( !empty( $wppb_two_factor_authentication_settings['enabled'] ) ) {
            $enabled = $wppb_two_factor_authentication_settings['enabled'];
        }

        $roles = get_editable_roles( );
        $network_roles = array( );
        if ( !empty( $wppb_two_factor_authentication_settings['roles'] ) ) {
            $network_roles = is_array( $wppb_two_factor_authentication_settings['roles'] ) ?
                $wppb_two_factor_authentication_settings['roles'] :
                array( $wppb_two_factor_authentication_settings['roles'] );
        }

        ?>
        <div class="wrap wppb-wrap wppb-two-factor-authentication">
            <h2><?php esc_html_e( 'Two-Factor Authentication Settings', 'profile-builder' );?></h2>

            <?php settings_errors( ); ?>

            <?php wppb_generate_settings_tabs( ) ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'wppb_two_factor_authentication_settings' ); ?>

                <table class="form-table">
                <tbody>
                <tr>
                    <th><?php esc_html_e( 'Enable Two-Factor Authentication', 'profile-builder' ); ?></th>
                    <td>
                        <select id="wppb-auth-enable" class="wppb-select" name="wppb_two_factor_authentication_settings[enabled]">
                            <option value="no" <?php if( $enabled === 'no' ) echo 'selected'; ?>><?php esc_html_e( 'No', 'profile-builder' ); ?></option>
                            <option value="yes" <?php if( $enabled === 'yes' ) echo 'selected'; ?>><?php esc_html_e( 'Yes', 'profile-builder' ); ?></option>
                        </select>
                        <ul>
                            <li class="description"><?php esc_html_e( 'Activate the Authenticator functionality', 'profile-builder' ); ?></li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Enable Authenticator for these user roles', 'profile-builder' ); ?></th>
                    <td>
                        <label>
                            <input name="wppb_two_factor_authentication_settings[roles][]" type="checkbox" <?php echo checked( in_array( '*', $network_roles, true ), true, false ); ?>value="*">
                            *
                        </label>
                        <br>
                        <?php foreach ( $roles as $role_key => $role ) {
                                ?><label>
                                    <input name="wppb_two_factor_authentication_settings[roles][]" type="checkbox" <?php echo checked( in_array( $role_key, $network_roles, true ), true, false ); ?>value="<?php echo esc_attr( $role_key ); ?>">
                                    <?php echo esc_html( $role[ 'name' ] ); ?>
                                </label>
                                <br>
                                <?php
                            }
                        ?>
                        <ul>
                            <li class="description"><?php esc_html_e( '"*" - Two-Factor Authentication will be enabled for all user roles.', 'profile-builder' ); ?></li>
                        </ul>
                    </td>
                </tr>
                </tbody>
                </table>
                <?php submit_button( __( 'Save Changes', 'profile-builder' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Add 2FA settings field to the frontend Edit Profile form
     */
    function add_field_to_frontend_edit_profile_form($args ) {
        $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );
        if( isset( $wppb_two_factor_authentication_settings['enabled'] ) && $wppb_two_factor_authentication_settings['enabled'] === 'yes' ) {
            $args['form_fields'] = $this->add_field_info($args['form_fields']);
        }
        return $args;
    }

    function add_field_info( $array ){
        $array[] = array(
            'field-title' => __( 'Two-Factor Authentication', 'profile-builder' ),
            'field' => __( 'Two-Factor Authentication', 'profile-builder' ),
            'meta-name' => __( '2fa_settings', 'profile-builder' ),
            'id' => __( '2fa_settings', 'profile-builder' ),
        );
        return $array;
    }

    /**
     * Add 2FA settings field to frontend profile page
     */

    function frontend_edit_profile_field($output, $form_location, $field, $user_id ){
        if ( $field['field'] === 'Two-Factor Authentication' ){
            if ( $form_location === 'edit_profile' ){

                $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );
                if( isset( $wppb_two_factor_authentication_settings['enabled'], $wppb_two_factor_authentication_settings['roles'] ) && $wppb_two_factor_authentication_settings['enabled'] === 'yes' ) {
                    if (isset($_REQUEST['edit_user'])) {
                        $user_id = sanitize_text_field( $_REQUEST['edit_user'] );
                    } else {
                        $user = wp_get_current_user();
                        $user_id = $user->ID;
                    }
                    $user_meta = get_userdata($user_id);

                    if (is_super_admin($user_id)) {
                        $user_meta->roles[] = 'administrator';
                    }

                    if ( $this->should_user_see_field( $wppb_two_factor_authentication_settings['roles'], $user_meta ) ) {

                        $secret = trim( get_user_option( 'wppb_auth_secret', $user_id ) );
                        $enabled = trim( get_user_option( 'wppb_auth_enabled', $user_id ) );
                        $relaxedmode = trim( get_user_option( 'wppb_auth_relaxedmode', $user_id ) );
                        $description = trim( get_user_option( 'wppb_auth_description', $user_id ) );

                        // In case the user has no secret ready ( new install ), we create one. or use the one they just posted
                        if ( '' === $secret ) {
                            $secret = array_key_exists( 'wppb_auth_secret', $_REQUEST ) ? sanitize_text_field( $_REQUEST['wppb_auth_secret'] ) : $this->create_secret( );
                        }

                        if ( '' === $description ) {
                            if ( is_multisite( ) && ( 1 < count( get_blogs_of_user( $user_id ) ) || is_super_admin( ) ) ) {
                                $description = sanitize_text_field( get_blog_details( get_network( )->id )->blogname );
                            } else {
                                $description = sanitize_text_field( get_bloginfo( 'name' ) );
                            }
                        }

                        $this->enqueue_2fa_scripts( );

                        $output .= '
                            <ul class="wppb-2fa-fields">
                                <li class="wppb-form-field wppb_2fa_heading"><h4>' . esc_html__( 'Two-Factor Authentication', 'profile-builder' ) . '</h4></li>
                                <li class="wppb-form-field wppb_auth_enabled">
                                    <label for="wppb_auth_enabled">' . esc_html__( 'Activate', 'profile-builder' ) . '</label>
                                    <input name="wppb_auth_enabled" id="wppb_auth_enabled" type="checkbox" ' . checked( $enabled, 'enabled', false ) . '/>
                                </li>
                                <div id="wppb_auth_active">
                                    <li class="wppb-form-field wppb_auth_relaxedmode">
                                        <label for="wppb_auth_relaxedmode">' . esc_html__( 'Relaxed Mode', 'profile-builder' ) . '</label>
                                        <input name="wppb_auth_relaxedmode" id="wppb_auth_relaxedmode" type="checkbox" ' . checked( $relaxedmode, 'enabled', false ) . '/>
                                        <span class="wppb-description-delimiter">' . esc_html__( "Allow for more time drift on your phone clock ( &#177;4 min ).", "profile-builder" ) . '</span>
                                    </li>
                                    <li class="wppb-form-field wppb_auth_description">
                                        <label for="wppb_auth_description">' . esc_html__( 'Description', 'profile-builder' ) . '</label>
                                        <input name="wppb_auth_description" id="wppb_auth_description" type="text" value="' . $description . '"/>
                                        <span class="wppb-description-delimiter">' . esc_html__( 'Description that you\'ll see in the Authenticator app.', 'profile-builder' ) . '</span>
                                    </li>
                                    <li class="wppb-form-field wppb_auth_secret">
                                        <label for="wppb_auth_secret">' . esc_html__( 'Secret', 'profile-builder' ) . '</label>
                                        <input name="wppb_auth_secret" id="wppb_auth_secret" type="text" readonly="readonly" size="25" value="' . $secret . '"/>
                                    </li>
                                    <li id="wppb_auth_secret_buttons" style="">
                                        <input name="wppb_auth_newsecret" id="wppb_auth_newsecret" value="' . esc_html__( 'New Secret', 'profile-builder' ) . '" type="button" class="button wppb_auth_button wppb_auth_new_button" />
                                        <input name="wppb_show_qr" id="wppb_show_qr" value="' . esc_html__( 'QR Code', 'profile-builder' ) . '" type="button" class="button wppb_auth_button wppb_auth_qr_button" onclick="ShowOrHideQRCode( )" />
                                    </li>
                                    <li id="wppb_auth_QR_INFO" style="display: none">
                                        <span class="wppb-description-delimiter">' . esc_html__( 'Scan this with the Authenticator app:', 'profile-builder' ) . '</span>
                                        <div id="wppb_auth_QRCODE"></div>
                                    </li>
                                    <li class="wppb-form-field wppb_auth_verify">
                                        <label for="wppb_auth_passw">' . esc_html__( 'Verify TOTP', 'profile-builder' ) . '</label>
                                        <input name="wppb_auth_passw" id="wppb_auth_passw" type="text"/>
                                    <li id="wppb_auth_verify_buttons" style="">
                                        <input name="wppb_auth_verify_button" id="wppb_auth_verify_button" value="' . esc_html__( 'Check', 'profile-builder' ) . '" type="button" class="button wppb_auth_button wppb_auth_verify_button" />
                                        <input name="wppb_auth_verify_indicator" id="wppb_auth_verify_indicator" value="&nbsp" type="button" class="button wppb_auth_button wppb_auth_verify_indicator" disabled />
                                        <input type="hidden" value="" name="wppb_auth_verify_result" id="wppb_auth_verify_result"/>
                                    </li>
                                    </li>
                                </div>
                            </ul>
                            ';
                    }
                }
            }
        }
        return $output;
    }

    function handle_frontend_edit_profile_update($global_request, $args ) {
        if( $args['form_type'] === 'edit_profile' ) {
            if( isset( $global_request['edit_user'] ) ) {
                $user_id = $global_request['edit_user'];
            } else {
                $user = wp_get_current_user( );
                $user_id = $user->ID;
            }

            if ( isset( $global_request['wppb_auth_enabled'] ) ) {
	            update_user_option( $user_id, 'wppb_auth_enabled',
		            empty( $global_request['wppb_auth_enabled'] ) ? 'disabled' : 'enabled', true );
            }
	        if ( isset( $global_request['wppb_auth_relaxedmode'] ) ) {
            update_user_option( $user_id, 'wppb_auth_relaxedmode',
                empty( $global_request['wppb_auth_relaxedmode'] ) ? 'disabled' : 'enabled', true );
	        }
	        if ( isset( $global_request['wppb_auth_description'] ) ) {
            update_user_option( $user_id, 'wppb_auth_description',
                trim( sanitize_text_field( $global_request['wppb_auth_description'] ) ),             true );
	        }
	        if ( isset( $global_request['wppb_auth_secret'] ) ) {
            update_user_option( $user_id, 'wppb_auth_secret',
                trim( $global_request['wppb_auth_secret'] ),                                         true );
	        }
        }
    }

    /**
     * Add 2FA settings field to backend profile page
     */
    function add_field_to_backend_edit_profile_form($user ) {
        $user_id = $user->ID;
        $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );
        if( ( isset( $wppb_two_factor_authentication_settings['enabled'], $wppb_two_factor_authentication_settings['roles'] ) && $wppb_two_factor_authentication_settings['enabled'] === 'yes' ) ) {
            $user_meta = get_userdata( $user_id );
            if ( is_super_admin( $user_id ) ) {
                $user_meta->roles[] = 'administrator';
            }

            if ( $this->should_user_see_field( $wppb_two_factor_authentication_settings['roles'], $user_meta ) ) {
                $secret		     	= trim( get_user_option( 'wppb_auth_secret', $user_id ) );
                $enabled			= trim( get_user_option( 'wppb_auth_enabled', $user_id ) );
                $relaxedmode		= trim( get_user_option( 'wppb_auth_relaxedmode', $user_id ) );
                $description		= trim( get_user_option( 'wppb_auth_description', $user_id ) );

                // In case the user has no secret ready ( new install ), we create one. or use the one they just posted
                if ( '' === $secret ) {
                    $secret = array_key_exists( 'wppb_auth_secret', $_REQUEST ) ? sanitize_text_field( $_REQUEST[ 'wppb_auth_secret' ] ) : $this->create_secret( );
                }

                if ( '' === $description ) {
                    if ( is_multisite( ) && ( 1 < count( get_blogs_of_user( $user_id ) )  || is_super_admin( ) ) ) {
                        $description = sanitize_text_field( get_blog_details( get_network( )->id )->blogname );
                    } else {
                        $description = sanitize_text_field( get_bloginfo( 'name' ) );
                    }
                }

                $this->enqueue_2fa_scripts( );
                wp_enqueue_style( 'wppb-back-end-style', WPPB_PLUGIN_URL . 'assets/css/style-back-end.css', false, PROFILE_BUILDER_VERSION );

                ?>
                    <h3><?php echo esc_html__( 'Two-Factor Authentication Settings', 'profile-builder' ); ?></h3>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><?php echo esc_html__( 'Activate', 'profile-builder' ); ?></th>
                                <td>
                                    <input name="wppb_auth_enabled" id="wppb_auth_enabled" class="tog" type="checkbox"<?php echo checked( $enabled, 'enabled', false ); ?>/>
                                </td>
                            </tr>
                            <tr class="wppb_auth_active wppb_auth_relaxedmode">
                                <th scope="row"><?php echo esc_html__( "Relaxed Mode", "profile-builder" ); ?></th>
                                <td>
                                    <input name="wppb_auth_relaxedmode" id="wppb_auth_relaxedmode" class="tog" type="checkbox"<?php echo checked( $relaxedmode, "enabled", false ); ?>/>
                                    <span class="description"><?php echo esc_html__( "Allow for more time drift on your phone clock ( &#177;4 min ).", "profile-builder" ); ?></span>
                                </td>
                            </tr>
                            <tr class="wppb_auth_active wppb_auth_description">
                                <th><label for="wppb_auth_description"><?php echo esc_html__( 'Description', 'profile-builder' ); ?></label></th>
                                <td>
                                    <input name="wppb_auth_description" id="wppb_auth_description" value="<?php echo esc_html( $description ); ?>"  type="text" size="25" />
                                    <span class="description"><?php echo esc_html__( 'Description that you\'ll see in the Authenticator app on your phone.', 'profile-builder' ); ?></span><br/>
                                </td>
                            </tr>
                            <tr class="wppb_auth_active wppb_auth_secret">
                                <th><label for="wppb_auth_secret"><?php echo esc_html__( 'Secret', 'profile-builder' ); ?></label></th>
                                <td>
                                    <input name="wppb_auth_secret" id="wppb_auth_secret" value="<?php echo esc_attr( $secret ); ?>" readonly="readonly"  type="text" size="25" />
                                    <input name="wppb_auth_newsecret" id="wppb_auth_newsecret" value="<?php echo esc_html__( 'Create new secret', 'profile-builder' ); ?>" type="button" class="button" />
                                    <input name="wppb_show_qr" id="wppb_show_qr" value="<?php echo esc_html__( 'Show/Hide QR code', 'profile-builder' ); ?>" type="button" class="button" onclick="ShowOrHideQRCode( )" />
                                </td>
                            </tr>
                            <tr id="wppb_auth_QR_INFO" style="display: none">
                                <th></th>
                                <td>
                                    <span class="description"><br/><?php echo esc_html__( 'Scan this with the Authenticator app:', 'profile-builder' ); ?></span>
                                    <br/>
                                    <div id="wppb_auth_QRCODE"></div>
                                </td>
                            </tr>
                            <tr class="wppb_auth_active wppb_auth_verify">
                                <th><label for="wppb_auth_passw"><?php echo esc_html__( 'Verify TOTP', 'profile-builder' ); ?></label></th>
                                <td>
                                    <input name="wppb_auth_passw" id="wppb_auth_passw" type="text" size="25" />
                                    <input name="wppb_auth_verify_button" id="wppb_auth_verify_button" value="<?php echo esc_html__( 'Check', 'profile-builder' ); ?>" type="button" class="button" />
                                    <input name="wppb_auth_verify_indicator" id="wppb_auth_verify_indicator" value="&nbsp" type="button" class="button" disabled />
                                    <input type="hidden" value="" name="wppb_auth_verify_result" id="wppb_auth_verify_result" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php
                return;
            }
        }
    }

    function handle_backend_edit_profile_update($field, $user_id, $request_data, $form_location ) {

        if( $field['field'] === 'Two-Factor Authentication' ) {
            update_user_option($user_id, 'wppb_auth_enabled',
                empty($request_data['wppb_auth_enabled']) ? 'disabled' : 'enabled', true);
            update_user_option($user_id, 'wppb_auth_relaxedmode',
                empty($request_data['wppb_auth_relaxedmode']) ? 'disabled' : 'enabled', true);
            update_user_option($user_id, 'wppb_auth_description',
                trim(sanitize_text_field($request_data['wppb_auth_description'])), true);
            update_user_option($user_id, 'wppb_auth_secret',
                trim($request_data['wppb_auth_secret']), true);
        }
    }

    /**
     * Add 2FA settings field to the PB field validation sequence
     */
    function add_field_to_pb_field_validation_sequence($manage_fields, $args ){
        if( ( isset( $args['form_type'] ) && $args['form_type'] === 'edit_profile' ) || ( isset( $args['context'] ) && $args['context'] === 'validate_backend' ) ) {
            $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );
            if( isset( $wppb_two_factor_authentication_settings['enabled'] ) && $wppb_two_factor_authentication_settings['enabled'] === 'yes' ) {
                return $this->add_field_info($manage_fields);
            }
        }

        return $manage_fields;
    }

    /**
     * 2FA settings field validation
     */
    function form_field_validation($message, $field, $request_data, $form_location ){

        if(($field['field'] === 'Two-Factor Authentication') && isset($request_data['wppb_auth_enabled']) && $request_data['wppb_auth_enabled'] === 'on') {
            $user = get_user_by( 'email', $request_data[ 'email' ] );

            if( $request_data[ 'wppb_auth_verify_result' ] !== 'valid' &&
                ( ( empty( $request_data['wppb_auth_enabled'] ) ? 'disabled' : 'enabled' ) !== get_user_option( 'wppb_auth_relaxedmode', $user->ID ) ||
                    ( trim( sanitize_text_field( $request_data['wppb_auth_description'] ) ) ) !== get_user_option( 'wppb_auth_description', $user->ID ) ||
                    ( trim( $request_data['wppb_auth_secret'] ) ) !== get_user_option( 'wppb_auth_secret', $user->ID ) ) ) {
                return __( 'Please verify TOTP to change Two-Factor Authentication settings', 'profile-builder' );
            }
        }
        return $message;
    }

    /**
     * Create a new random secret
     */
    function create_secret( ) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // allowed characters in Base32
        $secret = '';
        for ( $i = 0; $i < 16; $i++ ) {
            $secret .= $chars[wp_rand(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    /**
     * AJAX callback function used to add field to the login form when necessary
     */
    function ajax_add_auth_field_to_login_form() {
        // Some AJAX security.
        check_ajax_referer( 'WPPBAuth_field_on_login_form', 'nonce' );

        if ( isset( $_REQUEST['user'] )) {

            $username = sanitize_text_field( $_REQUEST['user'] );

            if ( is_email( $username ) ) {
                $userdata = get_user_by('email', $username);
            } else {
                $userdata = get_user_by('login', $username);
            }

            $wppb_two_factor_authentication_settings = get_option('wppb_two_factor_authentication_settings', 'not_found');

            header('Content-Type: application/json');

            if ($userdata instanceof WP_User &&
                isset($wppb_two_factor_authentication_settings['enabled'], $wppb_two_factor_authentication_settings['roles']) &&
                $wppb_two_factor_authentication_settings['enabled'] === 'yes' &&
                get_user_option('wppb_auth_enabled', $userdata->ID) === 'enabled') {

                if ($this->should_user_see_field($wppb_two_factor_authentication_settings['roles'], $userdata)) {
                    $result = array(
                        'field'  => $this->auth_code_field(),
                        'notice' => isset( $_REQUEST['location'] ) && $_REQUEST['location'] === 'backend' ? $this->input_TOTP_alert_back() : $this->input_TOTP_alert_front()
                    );
                    echo json_encode($result);
                    die();
                }
            }
        }


        echo json_encode( false );
        die();
    }

    /**
     * AJAX callback function used to generate new secret
     */
    function ajax_new_secret() {
        // Some AJAX security.
        check_ajax_referer( 'WPPBAuthaction', 'nonce' );

        // Create new secret.
        $secret = $this->create_secret( );

        $result = array( 'new-secret' => $secret );
        header( 'Content-Type: application/json' );
        echo json_encode( $result );

        die( );
    }

    /**
     * AJAX callback function used to validate TOTP
     */
    function ajax_check_code() {
        global $user_id;
        $valid = false;

        // AJAX security.
        check_ajax_referer( 'WPPBAuthaction', 'nonce' );

        if ( isset( $_REQUEST['secret'] ) ) {

            // Get the user's secret
            $secret = sanitize_text_field($_REQUEST['secret']);

            // Figure out if user is using relaxed mode
            $relaxedmode = '';
            if ( isset( $_REQUEST['relaxedmode'] ) ){
                $relaxedmode = sanitize_text_field($_REQUEST['relaxedmode']);
            }

            // Get the verification code entered by the user trying to login
            if (!empty($_REQUEST['otp'])) { // Prevent PHP notices when using app password login
                $otp = trim(sanitize_text_field($_REQUEST['otp']));
            } else {
                $otp = '';
            }
            // When was the last successful login performed ?
            $lasttimeslot = trim(get_user_option('wppb_auth_lasttimeslot', $user_id));
            // Valid code ?
            if ($timeslot = $this->verify($secret, $otp, $relaxedmode, $lasttimeslot)) {
                // Store the timeslot in which login was successful.
                update_user_option($user_id, 'wppb_auth_lasttimeslot', $timeslot, true);
                $valid = true;
            }
        }

        $result = array( 'valid-otp' => $valid );
        header( 'Content-Type: application/json' );
        echo json_encode( $result );

        die();
    }


    /**
     * Handle error for the frontend Login form
     */
    function login_error_message_handler( $userdata, $password )
    {
        $wppb_two_factor_authentication_settings = get_option( 'wppb_two_factor_authentication_settings', 'not_found' );

        if ( !is_wp_error( $userdata ) &&
            isset( $wppb_two_factor_authentication_settings['enabled'], $wppb_two_factor_authentication_settings['roles'] ) && $wppb_two_factor_authentication_settings['enabled'] === 'yes' &&
            get_user_option( 'wppb_auth_enabled', $userdata->ID ) === 'enabled' ) {

            if ( $this->should_user_see_field( $wppb_two_factor_authentication_settings['roles'], $userdata ) ) {
                if ( !isset( $_POST['auth'] ) || empty( $_POST['auth'] ) ) {
                    $errorMessage = __( 'Please enter the code from your Authenticator app.', 'profile-builder' );
                    return new WP_Error( 'wppb_login_auth', $errorMessage );
                }
                if ( !$this->check_otp( $userdata, $userdata->data->user_login, $password ) ) {
                    $errorMessage = '<strong>' . __( 'ERROR', 'profile-builder' ) . '</strong>: ' . __( 'Your Authenticator code was incorrect. Please try again.', 'profile-builder' );
                    return new WP_Error( 'wppb_login_auth', $errorMessage );
                }
            }
        }
        return $userdata;
    }

    function should_user_see_field( $two_factor_authentication_roles, $userdata ) {
        if (in_array('*', $two_factor_authentication_roles, true) ){
            return true;
        }

        foreach ($two_factor_authentication_roles as $key => $value) {
            if ( in_array($value, $userdata->roles, true) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add script that dynamically adds field to frontend Login form
     */
    function add_field_to_frontend_login_form($form_part, $args ) {
        if( !wp_script_is('jquery', 'done') && !is_admin() ){
            wp_print_scripts('jquery');
        }

        return '
            <script type="text/javascript">
                jQuery( document ).ready(function() {
                    var WPPBAuthNonce = "' . wp_create_nonce( 'WPPBAuth_field_on_login_form' ) . '";
                    var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";
    
                    if ( !jQuery(".login-auth").length ){
                        jQuery("#wppb-loginform").one("submit", function(event) {
                            var thisForm = this;
                            event.preventDefault();
    
                            var data = new Object();
                            data["action"]	= "WPPBAuth_field_on_login_form";
                            data["nonce"]	= WPPBAuthNonce;
                            data["location"]= "frontend";
                            data["user"]	= jQuery("#user_login.input").val();
                            jQuery.post(ajaxurl, data, function(response) {
                                if ( response ){
                                    jQuery("#wppb-login-wrap").before(response["notice"]);
                                    jQuery(".login-password").after(response["field"]);
                                } else {
                                    thisForm.submit();
                                }
                            });
                        });
                    }
                });
            </script>
            ';
    }

	/**
	 * Add script that dynamically adds field to frontend Login form
	 */
	function add_field_to_backend_login_form( ) {
		if( !wp_script_is('jquery', 'done') && !is_admin() ){
			wp_print_scripts('jquery');
		}

		echo '
            <script type="text/javascript">
                jQuery( document ).ready(function() {
                    var WPPBAuthNonce = "' . esc_html( wp_create_nonce( 'WPPBAuth_field_on_login_form' ) ) . '";
                    var ajaxurl = "' . esc_html( admin_url( 'admin-ajax.php' ) ) . '";
    
                    if ( !jQuery(".login-auth").length ){
                        jQuery("#loginform").one("submit", function(event) {
                            var thisForm = this;
                            event.preventDefault();
    
                            var data = new Object();
                            data["action"]	= "WPPBAuth_field_on_login_form";
                            data["nonce"]	= WPPBAuthNonce;
                            data["location"]= "backend";
                            data["user"]	= jQuery("#user_login.input").val();
                            jQuery.post(ajaxurl, data, function(response) {
                                if ( response ){
                                    jQuery("#loginform").before(response["notice"]);
                                    jQuery("#loginform .user-pass-wrap").after(response["field"]);
                                } else {
                                    thisForm.submit();
                                }
                            });
                        });
                    }
                });
            </script>
            ';
	}

    /**
     * Handle error for the backend Login form
     */
    function back_end_errors_filter( $errors, $redirect_to ) {
        if ( isset( $errors->errors['wppb_login_auth'] ) ) {
            add_action( 'login_form', array( $this, 'echo_auth_code_field' ) );
        }
        return $errors;
    }

    function input_TOTP_alert_front() {
        return '<p class="wppb-notice">' . __( 'Please enter the code from your Authenticator app.', 'profile-builder' ) . '</p>';
    }

	function input_TOTP_alert_back() {
		return '<div id="login_error">' . __( 'Please enter the code from your Authenticator app.', 'profile-builder' ) . '<br></div>';
	}

    /**
     * Echo field HTML for the backend Login form
     */
    function echo_auth_code_field() {
        echo $this->auth_code_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Field HTML for Login forms
     */
    function auth_code_field( ) {
        return '
			<p class="login-auth">' . $this->auth_code_field_inner() . '
			</p>';
    }

    /**
     * Field inner HTML for Login forms
     */
    function auth_code_field_inner( ) {
        return '
				<label for="login_auth">' . __( 'Authenticator Code', 'profile-builder' ) . '</label>
				<input type="text" name="auth" id="login_auth" class="input" value="" size="20" autocomplete="off" />';
    }

    /**
     * TOTP check on login attempt
     */
    function check_otp ( $user, $username = '', $password = '' ) {
        // Get the user's secret
        $secret = trim( get_user_option( 'wppb_auth_secret', $user->ID ) );

        // Get relaxed mode setting
        $relaxedmode = trim( get_user_option( 'wppb_auth_relaxedmode', $user->ID ) );

        // Get the verification code
        if ( !empty( $_POST['auth'] ) ) {
            $otp = trim( sanitize_text_field($_POST['auth']) );
        } else {
            $otp = '';
        }

        $lasttimeslot = trim( get_user_option( 'wppb_auth_lasttimeslot', $user->ID ) );
        if ( $timeslot = $this->verify( $secret, $otp, $relaxedmode, $lasttimeslot ) ) {
            // Save the timeslot in which login was successful
            update_user_option( $user->ID, 'wppb_auth_lasttimeslot', $timeslot, true );
            return true;
        }
        return false;
    }

    /**
     * Verification code check
     */
    function verify( $secretkey, $thistry, $relaxedmode, $lasttimeslot ) {
        if ( strlen( $thistry ) !== 6 ) {
            return false;
        } else {
            $thistry = (int)$thistry;
        }
        // account for time drift
        if ( $relaxedmode === 'enabled' ) {
            $firstcount = -8;
            $lastcount  =  8;
        } else {
            $firstcount = -1;
            $lastcount  =  1;
        }

        $tm = floor( time( ) / 30 );

        $secretkey = WPPB_Base32::decode( $secretkey );
        for ( $i = $firstcount; $i <= $lastcount; $i++ ) {
            $time=chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 ).pack( 'N*', $tm+$i );
            $hm = hash_hmac( 'SHA1', $time, $secretkey, true );
            $offset = ord( substr( $hm, -1 ) ) & 0x0F;
            $hashpart=substr( $hm, $offset, 4 );
            $value=unpack( "N", $hashpart );
            $value=$value[1];
            $value &= 0x7FFFFFFF;
            $value %= 1000000;
            if ( $value === $thistry ) {
                // Current login attempt must not happen before the last successful login
                if ( $lasttimeslot >= ( $tm+$i ) ) {
                    return false;
                }
                // Return timeslot in which login happened.
                return $tm+$i;
            }
        }
        return false;
    }
}