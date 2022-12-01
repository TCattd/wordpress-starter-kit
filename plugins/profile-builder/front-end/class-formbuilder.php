<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Profile_Builder_Form_Creator{
	private $defaults = array(
							'form_type' 			=> '',
							'form_fields' 			=> array(),
							'form_name' 			=> '',
							'role' 					=> '', //used only for the register-form settings
                            'redirect_url'          => '',
                            'logout_redirect_url'   => '', //used only for the register-form settings
                            'automatic_login'       => '', //used only for the register-form
							'redirect_priority'		=> 'normal',
                            'ID'                    => null
						);
	public $args;


	// Constructor method for the class
	function __construct( $args ) {

        /* we should stop the execution of the forms if they are in the wp_head hook because it should not be there.
        SEO plugins can execute shortcodes in the auto generated descriptions */
		if( apply_filters( 'wppb_dont_render_form_in_wp_head_hook', true ) ){
			global $wp_current_filter;
			if( !empty( $wp_current_filter ) && is_array( $wp_current_filter ) ){
				foreach( $wp_current_filter as $filter ){
					if( $filter == 'wp_head' )
					return;
				}
			}
		}

		// Merge the input arguments and the defaults
		$this->args = wp_parse_args( $args, $this->defaults );

        /* set up the ID here if it is a multi form */
        if( $this->args['form_name'] != 'unspecified' ){
            $this->args['ID'] = Profile_Builder_Form_Creator::wppb_get_form_id_from_form_name( $this->args['form_name'], $this->args['form_type'] );
        }

        global $wppb_shortcode_on_front;
        $wppb_shortcode_on_front = true;

        global $wppb_register_edit_profile_shortcode_on_front;
        $wppb_register_edit_profile_shortcode_on_front = true;

		if( empty( $this->args['form_fields'] ) )
			$this->args['form_fields'] = apply_filters( 'wppb_change_form_fields', get_option( 'wppb_manage_fields' ), $this->args );

		if ( file_exists ( WPPB_PLUGIN_DIR.'/front-end/default-fields/default-fields.php' ) )
			require_once( WPPB_PLUGIN_DIR.'/front-end/default-fields/default-fields.php' );

		if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists ( WPPB_PAID_PLUGIN_DIR.'/front-end/extra-fields/extra-fields.php' ) )
			require_once( WPPB_PAID_PLUGIN_DIR.'/front-end/extra-fields/extra-fields.php' );

		$this->wppb_retrieve_custom_settings();

        if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) )
            add_action( 'wppb_before_edit_profile_fields', array( 'Profile_Builder_Form_Creator', 'wppb_edit_profile_select_user_to_edit' ), 10, 4 );

        //enqueue frontend scripts for forms
        add_action( 'wp_footer', array( $this, 'wppb_frontend_scripts' ), 9999 );
	}

    /**
     * @param $form_name The "slug" generated from the current Form Title
     * @param $form_type the form type of the form: register, edit_profile
     * @return null
     */
    static function wppb_get_form_id_from_form_name( $form_name, $form_type ){
        global $wpdb;

        if( $form_type == 'edit_profile' ){
            $post_type = 'wppb-epf-cpt';
        }elseif( $form_type == 'register' ){
            $post_type = 'wppb-rf-cpt';
        }

        $all_forms = $wpdb->get_results(
                            "
                    SELECT ID, post_title
                    FROM $wpdb->posts
                    WHERE post_status = 'publish'
                        AND post_type = '$post_type'
                    "
                    );

        if( !empty( $all_forms ) ) {
            foreach ($all_forms as $form) {
                if( empty( $form->post_title ) )
                    $form->post_title = '(no title)';

                if ($form_name == Wordpress_Creation_Kit_PB::wck_generate_slug($form->post_title)) {
                    return $form->ID;
                }
            }
        }

        return null;
    }

	function wppb_retrieve_custom_settings(){
		$this->args['login_after_register'] = apply_filters( 'wppb_automatically_login_after_register', 'No' );
		$this->args['redirect_activated'] = apply_filters( 'wppb_redirect_default_setting', '-' );
		$this->args['redirect_url'] = apply_filters( 'wppb_redirect_default_location', ( $this->args['redirect_url'] != '' ) ? $this->args['redirect_url'] : '' );
		$this->args['logout_redirect_url'] = apply_filters( 'wppb_logout_redirect_default_location', ( $this->args['logout_redirect_url'] != '' ) ? $this->args['logout_redirect_url'] : '' );
		$this->args['redirect_delay'] = apply_filters( 'wppb_redirect_default_duration', 3 );

        $wppb_general_settings = get_option( 'wppb_general_settings' );
        $this->args['login_after_register'] = ( isset( $wppb_general_settings['automaticallyLogIn'] ) ? $wppb_general_settings['automaticallyLogIn'] : $this->args['login_after_register'] );

		if ( !is_null( $this->args['ID'] ) ){
			$meta_name = ( ( $this->args['form_type'] == 'register' ) ? 'wppb_rf_page_settings' : 'wppb_epf_page_settings' );

            $page_settings = get_post_meta( $this->args['ID'], $meta_name, true );

            if( !empty( $page_settings[0]['set-role'] ) ){
                if( $page_settings[0]['set-role'] == 'default role' ){
                    $selected_role = trim( get_option( 'default_role' ) );
                }
                else
                    $selected_role = $page_settings[0]['set-role'];
            }

            $this->args['role'] = ( isset( $selected_role ) ? $selected_role : $this->args['role'] );
            $this->args['login_after_register'] = ( isset( $page_settings[0]['automatically-log-in'] ) ? $page_settings[0]['automatically-log-in'] : $this->args['login_after_register'] );
            $this->args['redirect_activated'] = ( isset( $page_settings[0]['redirect'] ) ? $page_settings[0]['redirect'] : $this->args['redirect_activated'] );
            $this->args['redirect_url'] = ( ! empty( $page_settings[0]['url'] ) && $this->args['redirect_activated'] == 'Yes' && $this->args['redirect_priority'] != 'top' ? $page_settings[0]['url'] : $this->args['redirect_url'] );
            $this->args['redirect_delay'] = ( isset( $page_settings[0]['display-messages'] ) && $this->args['redirect_activated'] == 'Yes' ? $page_settings[0]['display-messages'] : $this->args['redirect_delay'] );
		}

		// the 'automatic_login' shortcode parameter overwrites all other settings
        $this->args['login_after_register'] = ( $this->args['automatic_login'] != '' ) ? $this->args['automatic_login'] : $this->args['login_after_register'];

        if( !empty( $this->args['role'] ) ){
            $role_in_arg = get_role( $this->args['role'] );
            if( !empty( $role_in_arg->capabilities['manage_options'] ) || !empty( $role_in_arg->capabilities['remove_users'] ) ){
                if( !current_user_can( 'manage_options' ) || !current_user_can( 'remove_users' ) ){
                    $this->args['role'] = get_option('default_role');
                    echo wp_kses_post( apply_filters( 'wppb_register_pre_form_user_role_message', '<p class="alert wppb-error" id="wppb_form_general_message">'.__( 'The role of the created user set to the default role. Only an administrator can register a user with the role assigned to this form.', 'profile-builder').'</p>' ) );
                }
            }
        }
	}

    function wppb_form_logic() {
        if( isset( $this->args['form_type'] ) ) {
            if( $this->args['form_type'] == 'register' ){
                $registration = apply_filters ( 'wppb_register_setting_override', true );//used to be get_option( 'users_can_register' )

                if ( !is_user_logged_in() ){
                    if ( !$registration )
                        echo wp_kses_post( apply_filters( 'wppb_register_pre_form_message', '<p class="alert" id="wppb_register_pre_form_message">'.esc_html(__( 'Only an administrator can add new users.', 'profile-builder')).'</p>' ) );

                    elseif ( $registration ){
                        $this->wppb_form_content( apply_filters( 'wppb_register_pre_form_message', '' ) );
                    }

                }else{
                    $current_user_capability = apply_filters ( 'wppb_registration_user_capability', 'create_users' );

                    if ( current_user_can( $current_user_capability ) && $registration )
                        $this->wppb_form_content( apply_filters( 'wppb_register_pre_form_message', '<p class="alert" id="wppb_register_pre_form_message">'.esc_html(__( 'Users can register themselves or you can manually create users here.', 'profile-builder')). '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.esc_attr(__( 'This message is only visible by administrators', 'profile-builder' )).'"/>' . '</p>' ) );

                    elseif ( current_user_can( $current_user_capability ) && !$registration )
                        $this->wppb_form_content( apply_filters( 'wppb_register_pre_form_message', '<p class="alert" id="wppb_register_pre_form_message">'.esc_html(__( 'Users cannot currently register themselves, but you can manually create users here.', 'profile-builder')). '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.esc_attr(__( 'This message is only visible by administrators', 'profile-builder' )).'"/>' . '</p>' ) );

                    elseif ( !current_user_can( $current_user_capability ) ){
                        global $user_ID;

                        $userdata = get_userdata( $user_ID );
                        $display_name = ( ( $userdata->data->display_name == '' ) ? $userdata->data->user_login : $userdata->data->display_name );

                        $wppb_general_settings = get_option( 'wppb_general_settings' );
                        if ( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) )
                            $display_name = $userdata->data->user_email;

                        if( empty( $this->args['logout_redirect_url'] ) ) {
                            $this->args['logout_redirect_url'] = get_permalink();
                        }

                        // CHECK FOR REDIRECT
                        $this->args['logout_redirect_url'] = wppb_get_redirect_url( $this->args['redirect_priority'], 'after_logout', $this->args['logout_redirect_url'], $userdata );
                        $this->args['logout_redirect_url'] = apply_filters( 'wppb_after_logout_redirect_url', $this->args['logout_redirect_url'] );

                        echo wp_kses_post( apply_filters( 'wppb_register_pre_form_message', '<p class="alert" id="wppb_register_pre_form_message">'.sprintf( __( "You are currently logged in as %1s. You don't need another account. %2s", 'profile-builder' ), '<a href="'.get_author_posts_url( $user_ID ).'" title="'.$display_name.'">'.$display_name.'</a>', '<a href="'.wp_logout_url( $this->args['logout_redirect_url'] ).'" title="'.__( 'Log out of this account.', 'profile-builder' ).'">'.__( 'Logout', 'profile-builder' ).'  &raquo;</a>' ).'</p>', $user_ID ) );
                    }
                }

            }elseif ( $this->args['form_type'] == 'edit_profile' ){
                if ( !is_user_logged_in() )
                    echo wp_kses_post( apply_filters( 'wppb_edit_profile_user_not_logged_in_message', '<p class="warning" id="wppb_edit_profile_user_not_logged_in_message">'.esc_html(__( 'You must be logged in to edit your profile.', 'profile-builder' )) .'</p>' ) );

                elseif ( is_user_logged_in() )
                    $this->wppb_form_content( apply_filters( 'wppb_edit_profile_logged_in_user_message', '' ) );

            }
        }
    }

    // Function used to automatically log in a user after register if that option is set on yes in register form settings
	function wppb_log_in_user( $redirect, $redirect_old ) {
        if( is_user_logged_in() ) {
            return;
        }

        $wppb_general_settings = get_option( 'wppb_general_settings' );

        if ( isset( $wppb_general_settings['emailConfirmation'] ) && ( $wppb_general_settings['emailConfirmation'] == 'yes' ) ) {
            return $redirect_old;
        }

        /* get user id */
        if( empty( $_POST['email'] ) )
            return;

        $user = get_user_by( 'email', trim( sanitize_email( $_POST['email'] ) ) );

        if( !$user )
            return;

        $nonce = wp_create_nonce( 'autologin-'. $user->ID .'-'. (int)( time() / 60 ) );

        if ( wppb_get_admin_approval_option_value() === 'yes' ) {
            if( !empty( $wppb_general_settings['adminApprovalOnUserRole'] ) ) {
                foreach ($user->roles as $role) {
                    if ( in_array( $role, $wppb_general_settings['adminApprovalOnUserRole'] ) ) {
                        return $redirect_old;
                    }
                }
            }
            else {
                return $redirect_old;
            }
        }

        /* define redirect location */
        if( $this->args['redirect_activated'] == 'No' ) {
            if( isset( $_POST['_wp_http_referer'] ) ) {
                $redirect = esc_url_raw($_POST['_wp_http_referer']);
			} else {
                $redirect = home_url();
			}
        }

		if( empty( $redirect ) )
			$redirect = wppb_curpageurl();

        $redirect = apply_filters( 'wppb_login_after_reg_redirect_url', $redirect, $this );

        $redirect = add_query_arg( array( 'autologin' => 'true', 'uid' => $user->ID, '_wpnonce' => $nonce ), $redirect );

        // CHECK FOR REDIRECT
		if( $this->args['redirect_activated'] == 'No' || ( empty( $this->args['redirect_delay'] ) || $this->args['redirect_delay'] == '0' ) ) {
            $redirect = wppb_build_redirect( $redirect, 0, 'register', $this->args );
		} else {
            $redirect = wppb_build_redirect( $redirect, $this->args['redirect_delay'], 'register', $this->args );
		}
		return $redirect;
	}

    /**
     * Function to get redirect for Register and Edit Profile forms
     *
     * @param   string      $form_type      - type of the form
     * @param   string      $redirect_type  - type of the redirect
     * @param   string      $user           - username or user email
     * @param   string      $user_role      - user Role
     *
     * @return  string  $redirect
     */
	function wppb_get_redirect( $form_type, $redirect_type, $user, $user_role ) {
        $this->args['redirect_delay'] = apply_filters( 'wppb_'. $form_type .'_redirect_delay', $this->args['redirect_delay'], $user, $this->args );
        if( $this->args['redirect_activated'] == '-' ) {
            $this->args['redirect_url'] = wppb_get_redirect_url( $this->args['redirect_priority'], $redirect_type, $this->args['redirect_url'], $user, $user_role );
            $redirect = wppb_build_redirect( $this->args['redirect_url'], $this->args['redirect_delay'], $form_type, $this->args );
        } elseif( $this->args['redirect_activated'] == 'Yes' ) {
            $redirect = wppb_build_redirect( $this->args['redirect_url'], $this->args['redirect_delay'], $form_type, $this->args );
        } else {
            $redirect = '';
        }

        return $redirect;
    }

	function wppb_form_content( $message ) {
		$field_check_errors = array();

		ob_start();

		// check if the form is being displayed in the Elementor editor
        // if true remove any messages
        $is_elementor_edit_mode = false;
        if( class_exists ( '\Elementor\Plugin' ) ){
            $is_elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
            $message= "";
        }

		if( !$is_elementor_edit_mode && isset( $_REQUEST['action'], $_REQUEST['form_name'], $this->args['form_name'] ) && $_REQUEST['form_name'] === $this->args['form_name'] ) {
            if( ! isset( $_POST[$this->args['form_type'].'_'. $this->args['form_name'] .'_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST[$this->args['form_type'].'_'. $this->args['form_name'] .'_nonce_field'] ), 'wppb_verify_form_submission' ) ) {
                echo '<span class="wppb-form-error wppb-error">'. esc_html(__( 'You are not allowed to do this.', 'profile-builder' )) . '</span>';
                return;
            }

            $_REQUEST = apply_filters( 'wppb_filter_form_request_data', $_REQUEST, $this->args );

			$field_check_errors = $this->wppb_test_required_form_values( $_REQUEST );
			if( empty( $field_check_errors ) ) {

                do_action( 'wppb_before_saving_form_values',$_REQUEST, $this->args );

				// we only have a $user_id on default registration (no email confirmation, no multisite)
				$user_id = $this->wppb_save_form_values( $_REQUEST );

                do_action( 'wppb_after_saving_form_values',$_REQUEST, $this->args );

				if( ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) && ( isset( $_POST['action'] ) && $_POST['action'] === $this->args['form_type'] ) ) {

                    $form_message_tpl_start = apply_filters( 'wppb_form_message_tpl_start', '<p class="alert wppb-success" id="wppb_form_general_message">' );
                    $form_message_tpl_end = apply_filters( 'wppb_form_message_tpl_end', '</p>' );

                    if( ! current_user_can( 'manage_options' ) && $this->args['form_type'] != 'edit_profile' && isset( $_POST['custom_field_user_role'] ) ) {
                        $user_role = sanitize_text_field($_POST['custom_field_user_role']);
                    } elseif( ! current_user_can( 'manage_options' ) && $this->args['form_type'] != 'edit_profile' && isset( $this->args['role'] ) ) {
                        $user_role = $this->args['role'];
                    } else {
                        $user_role = NULL;
                    }

                    if( isset( $_POST['username'] ) && sanitize_user( $_POST['username'] ) != '' ) {
                        $account_name = sanitize_user( $_POST['username'] );
                    } elseif( isset( $_POST['email'] ) && ( sanitize_email( $_POST['email'] ) != '' ) ) {
                        $account_name = sanitize_email( $_POST['email'] );
                    }else{
                        /* we are in the edit form with no username or email field */
                        $current_user = wp_get_current_user();
                        if( !empty( $current_user ) )
                            $account_name = $current_user->user_login;
                    }

                    if( $this->args['form_type'] == 'register' ) {
                        // ec = email confirmation setting
                        // aa = admin approval setting
                        $wppb_general_settings = get_option( 'wppb_general_settings', 'false' );
                        if ( $wppb_general_settings ) {
                            if( !empty( $wppb_general_settings['emailConfirmation'] ) && apply_filters( 'wppb_email_confirmation_on_register', $wppb_general_settings['emailConfirmation'], $_POST ) == 'yes' )
								$wppb_email_confirmation = $wppb_general_settings['emailConfirmation'];
                            else
								$wppb_email_confirmation = 'no';


                            $wppb_admin_approval = wppb_get_admin_approval_option_value();

                            $account_management_settings = 'ec-' . $wppb_email_confirmation . '_' . 'aa-' . $wppb_admin_approval;
                        } else {
                            $account_management_settings = 'ec-no_aa-no';
                        }

                        switch( $account_management_settings ) {
                            case 'ec-no_aa-no':
                                $wppb_register_success_message = apply_filters( 'wppb_register_success_message', sprintf( __( "The account %1s has been successfully created!", 'profile-builder' ), $account_name ), $account_name ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
                                break;
                            case 'ec-yes_aa-no':
                                $wppb_register_success_message = apply_filters( 'wppb_register_success_message', sprintf( __( "Before you can access your account %1s, you need to confirm your email address. Please check your inbox and click the activation link.", 'profile-builder' ), $account_name ), $account_name ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
                                break;
                            case 'ec-no_aa-yes':
								if( current_user_can( 'delete_users' ) ) {
									$wppb_register_success_message = apply_filters( 'wppb_register_success_message', sprintf( __( "The account %1s has been successfully created!", 'profile-builder' ), $account_name ), $account_name ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
								} else { 
									$wppb_register_success_message = apply_filters( 'wppb_register_success_message', sprintf( __( "Before you can access your account %1s, an administrator has to approve it. You will be notified via email.", 'profile-builder' ), $account_name ), $account_name ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
								}
								break;
                            case 'ec-yes_aa-yes':
                                $wppb_register_success_message = apply_filters( 'wppb_register_success_message', sprintf( __( "Before you can access your account %1s, you need to confirm your email address. Please check your inbox and click the activation link.", 'profile-builder' ), $account_name ), $account_name ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
                                break;
                        }

                        // CHECK FOR REDIRECT
                        $redirect = $this->wppb_get_redirect( 'register', 'after_registration', $account_name, $user_role );

                        // using case-insensitive string comparison to allow for both 'Yes' and 'yes'
                        if( strcasecmp($this->args['login_after_register'], 'Yes') == 0 ) {
                            $redirect = $this->wppb_log_in_user( $this->args['redirect_url'], $redirect );
                        }

						echo $form_message_tpl_start . wp_kses_post( $wppb_register_success_message )  . $form_message_tpl_end . $redirect; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped above */
						//action hook after registration success
	                    do_action( 'wppb_register_success', $_REQUEST, $this->args['form_name'], $user_id );
                        return;
                    } elseif( $this->args['form_type'] == 'edit_profile' ) {
                        // CHECK FOR REDIRECT
                        $redirect = $this->wppb_get_redirect( 'edit_profile', 'after_edit_profile', $account_name, $user_role );

						echo $form_message_tpl_start  . apply_filters( 'wppb_edit_profile_success_message', esc_html(__( 'Your profile has been successfully updated!', 'profile-builder' )) ) . $form_message_tpl_end . $redirect; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped above */

                        //action hook after edit profile success
	                    do_action( 'wppb_edit_profile_success', $_REQUEST, $this->args['form_name'], $user_id );
                        if( apply_filters( 'wppb_no_form_after_profile_update', false ) )
	                        return;
					}

				}

			}else
				echo $message. wp_kses_post( apply_filters( 'wppb_general_top_error_message', '<p id="wppb_form_general_message" class="wppb-error">'.esc_html(__( 'There was an error in the submitted form', 'profile-builder' )).'</p>' ) ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped above */

		}else
			echo $message; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped when created */

			// use this action hook to add extra content before the register form
		do_action( 'wppb_before_'.$this->args['form_type'].'_fields', $this->args['form_name'], $this->args['ID'], $this->args['form_type'], $is_elementor_edit_mode );

		$wppb_user_role_class = '';
		if( is_user_logged_in() ) {
			$wppb_user = wp_get_current_user();

			if( $wppb_user && isset( $wppb_user->roles ) ) {
				foreach( $wppb_user->roles as $wppb_user_role ) {
					$wppb_user_role_class .= ' wppb-user-role-'. $wppb_user_role;
				}
			}
		} else {
			$wppb_user_role_class = ' wppb-user-logged-out';
		}
		$wppb_user_role_class = apply_filters( 'wppb_user_role_form_class', $wppb_user_role_class );

        /* set up form id */
        $wppb_form_id = '';
        if( $this->args['form_type'] == 'register' )
            $wppb_form_id = 'wppb-register-user';
        elseif( $this->args['form_type'] == 'edit_profile' )
            $wppb_form_id = 'wppb-edit-user';
        if( isset($this->args['form_name']) && $this->args['form_name'] != "unspecified" )
            $wppb_form_id .= '-' . $this->args['form_name'];

        /* set up form class */
        $wppb_form_class = 'wppb-user-forms';
        if( $this->args['form_type'] == 'register' )
            $wppb_form_class .= ' wppb-register-user';
        elseif( $this->args['form_type'] == 'edit_profile' )
            $wppb_form_class .= ' wppb-edit-user';
        $wppb_form_class .= $wppb_user_role_class;

        ?>
        <form enctype="multipart/form-data" method="post" id="<?php echo esc_attr( apply_filters( 'wppb_form_id', $wppb_form_id, $this ) ); ?>" class="<?php echo esc_attr( apply_filters( 'wppb_form_class', $wppb_form_class, $this ) ); ?>" action="<?php echo esc_url( apply_filters( 'wppb_form_action', wppb_curpageurl() ) ); ?>">
			<?php
            do_action( 'wppb_form_args_before_output', $this->args );
            $this->args = apply_filters( 'wppb_filter_form_args_before_output', $this->args );

			echo apply_filters( 'wppb_before_form_fields', '<ul>', $this->args['form_type'], $this->args['ID'] ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
			echo $this->wppb_output_form_fields( $_REQUEST, $field_check_errors, $this->args['form_fields'] ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped when created */
			echo apply_filters( 'wppb_after_form_fields', '</ul>', $this->args['form_type'], $this->args['ID'], $_REQUEST ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */

			echo apply_filters( 'wppb_before_send_credentials_checkbox', '<ul>', $this->args['form_type'], $this->args['ID'] ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
			$this->wppb_add_send_credentials_checkbox( $_REQUEST, $this->args['form_type'] );
			echo apply_filters( 'wppb_after_send_credentials_checkbox', '</ul>', $this->args['form_type'] ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */

            $wppb_form_submit_extra_attr = apply_filters( 'wppb_form_submit_extra_attr', '', $this->args['form_type'], $this->args['ID'] );
			?>
			<p class="form-submit" <?php echo $wppb_form_submit_extra_attr; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped when created */ ?> >
				<?php
				if( $this->args['form_type'] == 'register' )
					$button_name = ( current_user_can( 'create_users' ) ? __( 'Add User', 'profile-builder' ) : __( 'Register', 'profile-builder' ) );

				elseif( $this->args['form_type'] == 'edit_profile' )
					$button_name = __( 'Update', 'profile-builder' );
				?>
                <?php do_action( 'wppb_form_before_submit_button', $this->args ); ?>
				<input name="<?php echo esc_attr( $this->args['form_type'] ); ?>" type="submit" id="<?php echo esc_attr( $this->args['form_type'] ); ?>" class="<?php echo esc_attr( apply_filters( 'wppb_'. $this->args['form_type'] .'_submit_class', "submit button" ) );?>" value="<?php echo esc_attr( apply_filters( 'wppb_'. $this->args['form_type'] .'_button_name', $button_name, $this->args['form_name'] ) ); ?>" <?php echo apply_filters( 'wppb_form_submit_button_extra_attributes', '', $this->args['form_type'] ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped when created */?>/>
                <input name="redirect_to" type="hidden" value="<?php echo esc_attr( $this->args['redirect_url'] ); ?>" />
                <?php do_action( 'wppb_form_after_submit_button', $this->args ); ?>
				<input name="action" type="hidden" id="action" value="<?php echo esc_attr( $this->args['form_type'] ); ?>" />
				<input name="form_name" type="hidden" id="form_name" value="<?php echo esc_attr( $this->args['form_name'] ); ?>" />
				<input name="form_id" type="hidden" id="form_id" value="<?php echo esc_attr( $this->args['ID'] ); ?>" />
				<?php
				$wppb_module_settings = get_option( 'wppb_module_settings' );

				if( isset( $wppb_module_settings['wppb_customRedirect'] ) && $wppb_module_settings['wppb_customRedirect'] == 'show' ) {
                    if( isset( $_POST['wppb_referer_url'] ) )
                        $referer = esc_url_raw( $_POST['wppb_referer_url'] );
                    elseif( isset( $_SERVER['HTTP_REFERER'] ) )
                        $referer =  esc_url_raw( $_SERVER['HTTP_REFERER'] );
                    else
                        $referer = '';

					echo '<input type="hidden" name="wppb_referer_url" value="'. esc_attr( $referer ).'"/>';
				}
				?>
			</p><!-- .form-submit -->
			<?php wp_nonce_field( 'wppb_verify_form_submission', $this->args['form_type'].'_'. $this->args['form_name'] .'_nonce_field' ); ?>
		</form>
		<?php
		// use this action hook to add extra content after the register form
		do_action( 'wppb_after_'. $this->args['form_type'] .'_fields', $this->args['form_name'], $this->args['ID'], $this->args['form_type'] );

		$form_content = ob_get_clean();

		echo apply_filters( 'wppb_' . $this->args['form_type'] . '_form_content', $form_content ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped when created */
	}

	function wppb_output_form_fields( $global_request, $field_check_errors, $form_fields, $called_from = NULL, $is_repeater_group = false ){
		$wppb_generalSettings = get_option( 'wppb_general_settings' );
		$output_fields = '';

		if( !empty( $form_fields ) ){
		    $output_fields .= apply_filters( 'wppb_output_before_first_form_field', '', $this->args['ID'], $this->args['form_type'], $form_fields, $called_from );
			foreach( $form_fields as $field ){
				$error_var = ( ( array_key_exists( $field['id'], $field_check_errors ) ) ? ' wppb-field-error' : '' );
				$specific_message = ( ( array_key_exists( $field['id'], $field_check_errors ) ) ? $field_check_errors[$field['id']] : '' );

                $display_field = apply_filters( 'wppb_output_display_form_field', true, $field, $this->args['form_type'], $this->args['role'], $this->wppb_get_desired_user_id() );

                if( $display_field == false )
                    continue;

                $css_class = apply_filters( 'wppb_field_css_class', 'wppb-form-field wppb-'. Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ) .$error_var, $field, $error_var );
				$output_fields .= apply_filters( 'wppb_output_before_form_field', '<li class="'. $css_class .'" id="wppb-form-element-'. $field['id'] .'">', $field, $error_var, $this->args['role'] );

				$render_field = true;
				if( wppb_conditional_fields_exists() && isset( $wppb_generalSettings['conditional_fields_ajax'] ) ){
                    if($wppb_generalSettings['conditional_fields_ajax'] === 'yes' && isset($field['conditional-logic-enabled']) && $field['conditional-logic-enabled'] === 'yes') {
                        $render_field = false;
                    }
                }

				if( $render_field ){
                    $output_fields .= apply_filters('wppb_output_form_field_' . Wordpress_Creation_Kit_PB::wck_generate_slug($field['field']), '', $this->args['form_type'], $field, $this->wppb_get_desired_user_id(), $field_check_errors, $global_request, $this->args['role'], $this);
                    $output_fields .= apply_filters('wppb_output_specific_error_message', $specific_message);
                }

				$output_fields .= apply_filters( 'wppb_output_after_form_field', '</li>', $field, $this->args['ID'], $this->args['form_type'], $called_from );
			}

			if ( !$is_repeater_group ) {
                $output_fields .= apply_filters('wppb_output_after_last_form_field', '', $this->args['ID'], $this->args['form_type'], $called_from);
            }
		}

		return apply_filters( 'wppb_output_fields_filter', $output_fields );
	}


	function wppb_add_send_credentials_checkbox ( $request_data, $form ){
		if ( $form == 'edit_profile' )
			echo '';

		else{
			$checkbox = apply_filters( 'wppb_send_credentials_checkbox_logic', '<li class="wppb-form-field wppb-send-credentials-checkbox"><label for="send_credentials_via_email"><input id="send_credentials_via_email" type="checkbox" name="send_credentials_via_email" value="sending"'.( ( isset( $request_data['send_credentials_via_email'] ) && ( $request_data['send_credentials_via_email'] == 'sending' ) ) ? ' checked' : '' ).'/>'.esc_html__( 'Send these credentials via email.', 'profile-builder').'</label></li>', $request_data, $form );

			$wppb_general_settings = get_option( 'wppb_general_settings' );
			echo ( isset( $wppb_general_settings['emailConfirmation'] ) && ( $wppb_general_settings['emailConfirmation'] == 'yes' ) ? '' : $checkbox ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */  /* properly escaped when created */
		}
	}


	function wppb_test_required_form_values( $global_request ){
		$output_field_errors = array();
        $form_fields = apply_filters( 'wppb_form_fields', $this->args['form_fields'], array( 'global_request' => $global_request, 'context' => 'validate_frontend', 'form_type' => $this->args['form_type'], 'role' => $this->args['role'], 'user_id' => $this->wppb_get_desired_user_id()  ) );
		if( !empty( $form_fields ) ){
			foreach( $form_fields as $field ){
				$error_for_field = apply_filters( 'wppb_check_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ), '', $field, $global_request, $this->args['form_type'], $this->args['role'], $this->wppb_get_desired_user_id() );

				if( !empty( $error_for_field ) )
					$output_field_errors[$field['id']] = '<span class="wppb-form-error">' . $error_for_field  . '</span>';
			}
		}

		return apply_filters( 'wppb_output_field_errors_filter', $output_field_errors, $this->args['form_fields'], $global_request, $this->args['form_type'] );
	}

	function wppb_save_form_values( $global_request ){
		$user_id = $this->wppb_get_desired_user_id();
		$userdata = apply_filters( 'wppb_build_userdata', array(), $global_request, $this->args );
		$new_user_signup = false;

        $wppb_general_settings = get_option( 'wppb_general_settings' );

		if( $this->args['form_type'] == 'register' ){

            $result = $this->wppb_register_user( $global_request, $userdata );
            $user_id = $result['user_id'];
            $userdata = $result['userdata'];
            $new_user_signup = $result['new_user_signup'];

		}elseif( $this->args['form_type'] == 'edit_profile' ){
			if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) ){
                $user_info = get_userdata( $user_id );
                $userdata['user_login'] = $user_info->user_login;
            }

			$userdata['ID'] = $this->wppb_get_desired_user_id();
            $userdata = wp_unslash( $userdata );
            /* if the user changes his password then we can't send it to the wp_update_user() function or
            the user will be logged out and won't be logged in again because we call wp_update_user() after
            the headers were sent( in the content as a shortcode ) */
            if( isset( $userdata['user_pass'] ) && !empty( $userdata['user_pass'] ) ){
                unset($userdata['user_pass']);
            }

            if( isset( $userdata['role'] ) && is_array( $userdata['role'] ) ) {
                $user_data = get_userdata( $user_id );
                if( $user_data ) {
                    $user_data->remove_all_caps();

                    foreach ($userdata['role'] as $role) {
                        if ($role !== 'administrator' || $role !== 'super-admin')//make sure this doesn't happen for any reason
                            $user_data->add_role($role);
                    }
                }

                unset( $userdata['role'] );
            }

			wp_update_user( $userdata );
		}

		if( !empty( $this->args['form_fields'] ) && !$new_user_signup ){
			foreach( $this->args['form_fields'] as $field ){
                if( apply_filters( 'wppb_pre_save_form_field', true, $field, $user_id, $global_request, $this->args['form_type'] ) )
				    do_action( 'wppb_save_form_field', $field, $user_id, $global_request, $this->args['form_type'] );
			}

			if ( $this->args['form_type'] == 'register' ){
				if ( !is_wp_error( $user_id ) ){
					$wppb_general_settings = get_option( 'wppb_general_settings' );
                    if( isset( $global_request['send_credentials_via_email'] ) && ( $global_request['send_credentials_via_email'] == 'sending' ) )
                        $send_credentials_via_email = 'sending';
                    else
                        $send_credentials_via_email = '';
					wppb_notify_user_registration_email( get_bloginfo( 'name' ), ( isset( $userdata['user_login'] ) ? trim( $userdata['user_login'] ) : trim( $userdata['user_email'] ) ), trim( $userdata['user_email'] ), $send_credentials_via_email, trim( $userdata['user_pass'] ), ( wppb_get_admin_approval_option_value() === 'yes' ? 'yes' : 'no' ) );
				}
            }
		}
		return $user_id;
	}

    function wppb_register_user( $global_request, $userdata ){
        $wppb_module_settings = get_option( 'wppb_module_settings' );
        $wppb_general_settings = get_option( 'wppb_general_settings' );
        $user_id = null;
        $new_user_signup = false;

        if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) ){
            $userdata['user_login'] = apply_filters( 'wppb_generated_random_username', Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $userdata['user_email'] ) ), $userdata['user_email'] );
        }

		/* filter so we can bypass Email Confirmation on register */
		$wppb_general_settings['emailConfirmation'] = apply_filters( 'wppb_email_confirmation_on_register', $wppb_general_settings['emailConfirmation'], $global_request );

        if ( isset( $wppb_general_settings['emailConfirmation'] ) && ( $wppb_general_settings['emailConfirmation'] == 'yes' ) ){
            $new_user_signup = true;

            $userdata = $this->wppb_add_custom_field_values( $global_request, $userdata, $this->args['form_fields'] );

			if( ! isset( $userdata['role'] ) ) {
				$userdata['role'] = $this->args['role'];
			}

            $userdata['user_pass'] = wp_hash_password( $userdata['user_pass'] );

            if( is_multisite() ){
                /* since version 2.0.7 add this meta so we know on what blog the user registered */
                $userdata['registered_for_blog_id'] = get_current_blog_id();
                $userdata = wp_unslash( $userdata );
            }

            wppb_signup_user( $userdata['user_login'], $userdata['user_email'], $this->args['login_after_register'], $userdata );
        }else{
			if( ! isset( $userdata['role'] ) ) {
				$userdata['role'] = $this->args['role'];
			}

            $userdata = wp_unslash( $userdata );

            // change User Registered date and time according to timezone selected in WordPress settings
            $wppb_get_date = wppb_get_register_date();

            if( isset( $wppb_get_date ) ) {
                $userdata['user_registered'] = $wppb_get_date;
            }

            // insert user to database
            $user_id = wp_insert_user( $userdata );
        }

        return array( 'userdata' => $userdata, 'user_id' => $user_id, 'new_user_signup' => $new_user_signup );
    }

    function wppb_add_custom_field_values( $global_request, $meta, $form_properties ){
        $form_fields = apply_filters( 'wppb_form_fields', $this->args['form_fields'], array( 'meta' => $meta, 'global_request' => $global_request, 'context' => 'user_signup' ) );
        if( !empty( $form_fields ) ){
            foreach( $form_fields as $field ){
                if( !empty( $field['meta-name'] ) ){
                    $posted_value = ( !empty( $global_request[$field['meta-name']] )  ? $global_request[$field['meta-name']] : '' );
                    $meta[$field['meta-name']] = apply_filters( 'wppb_add_to_user_signup_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ), $posted_value, $field, $global_request );
                }
            }
        }

        return apply_filters( 'wppb_add_to_user_signup_form_meta', $meta, $global_request, $this->args['role'] );
    }

    /**
     * Function that returns the id for the current logged in user or for edit profile forms for administrator it can return the id of a selected user
     */
	function wppb_get_desired_user_id(){
		if( $this->args['form_type'] == 'edit_profile' ){
			//only admins
			if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
				if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) ){
					return absint( $_GET['edit_user'] );
				}
			}
		}

		return get_current_user_id();
	}

    static function wppb_edit_profile_select_user_to_edit( $form_name, $id, $form_type, $is_elementor_edit_mode ){

        $display_edit_users_dropdown = apply_filters( 'wppb_display_edit_other_users_dropdown', true, $form_name );
        if( !$display_edit_users_dropdown || $is_elementor_edit_mode )
            return;

        /* add a hard cap: if we have more than 5000 users don't display the dropdown for performance considerations */
       $user_count = count_users();
        if( $user_count['total_users'] > apply_filters( 'wppb_edit_other_users_count_limit', 5000 ) )
            return;

        if( isset( $_GET['edit_user'] ) && ! empty( $_GET['edit_user'] ) )
            $selected = absint( $_GET['edit_user'] );
        else
            $selected = get_current_user_id();

        $query_args = array(
            'fields'  => array( 'ID', 'user_login', 'display_name' ),
            'role'    => apply_filters( 'wppb_edit_profile_user_dropdown_role', '', $form_name ),
            'orderby' => array( 'display_name', 'user_login' ),
        );

        $users = get_users( apply_filters( 'wppb_edit_other_users_dropdown_query_args', $query_args ) );
        
        if( !empty( $users ) ) {

            /* turn it in a select2 */
            wp_enqueue_script( 'wppb_select2_js', WPPB_PLUGIN_URL .'assets/js/select2/select2.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
            wp_enqueue_style( 'wppb_select2_css', WPPB_PLUGIN_URL .'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION );
            ?>
            <form method="GET" action="" id="select_user_to_edit_form">
                <p class="wppb-form-field">
                    <label for="edit_user"><?php esc_html_e('User to edit:', 'profile-builder') ?></label>
                    <select id="wppb-<?php echo !empty( $form_name ) ? esc_attr( $form_name ).'-' : ''; ?>user-to-edit" class="wppb-user-to-edit" name="edit_user">
                        <option value=""><?php echo esc_html__( 'Select User', 'profile-builder' ); ?></option>
                        <?php
						foreach( $users as $user ){
							?>
							<option value="<?php echo  esc_url_raw( add_query_arg( array( 'edit_user' => $user->ID ) ) ); ?>" <?php selected( $selected, $user->ID ); ?>>
								<?php echo esc_html( apply_filters( 'wppb_edit_other_users_display_name', $user->display_name, $user ) ); ?>
							</option>
							<?php
						}
                        ?>
                    </select>
                </p>
            </form>
        <?php
        }
        else{
            echo '<p id="wppb-no-other-users-to-edit">'. esc_html( apply_filters( 'wppb_no_users_to_edit_message', __( 'There are no other users to edit', 'profile-builder' ) ) ).'</p>';
        }
    }

    static function wppb_frontend_scripts(){
        $wppb_toolbox_forms_settings = get_option( 'wppb_toolbox_forms_settings' );
	    if( !isset( $wppb_toolbox_forms_settings[ 'disable-automatic-scrolling' ] ) ){
            wp_enqueue_script( 'wppb_front_end_script', WPPB_PLUGIN_URL. 'assets/js/script-front-end.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
            wp_print_scripts( 'wppb_front_end_script' );
        }

        if ( current_user_can( 'manage_options' ) ) {
            wp_enqueue_script( 'wppb_select_user_to_edit_js', WPPB_PLUGIN_URL. 'assets/js/select-user-to-edit.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
            wp_print_scripts( 'wppb_select_user_to_edit_js' );
        }

    }

    /**
     * Handle toString method
     *
     * @since 2.0
     *
     * @return string $html html for the form.
     */
    public function __toString() {
        try {
            ob_start();
            $this->wppb_form_logic();
            $html = ob_get_clean();
            return "{$html}";
        } catch (Exception $exception) {
            return __( 'Something went wrong. Please try again!', 'profile-builder');
        }
    }
}

/* set action for automatic login after registration */
add_action( 'init', 'wppb_autologin_after_registration' );
function wppb_autologin_after_registration(){
    if( isset( $_GET['autologin'] ) && isset( $_GET['uid'] ) && isset( $_REQUEST['_wpnonce'] ) ){
        $uid = absint( $_GET['uid'] );

        $arr_params = array( 'autologin', 'uid', '_wpnonce' );
        $current_page_url = remove_query_arg( $arr_params, wppb_curpageurl() );

        if ( ! ( wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ) , 'autologin-'.$uid.'-'.(int)( time() / 60 ) ) || wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ) , 'autologin-'.$uid.'-'.(int)( time() / 60 - 1 ) ) ) ){
            wp_redirect( $current_page_url );
            exit;
        } else {
            wp_set_auth_cookie( $uid );
            wp_redirect( $current_page_url );
            exit;
        }
    }
}
