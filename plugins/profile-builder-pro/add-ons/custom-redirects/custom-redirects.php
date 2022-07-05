<?php

/* include the admin UI for custom redirects */
if( wppb_check_if_add_on_is_active( 'wppb_customRedirect' ) ){
    if( file_exists( WPPB_PAID_PLUGIN_DIR .'/add-ons/custom-redirects/custom_redirects_admin.php' ) ) {
        include_once( WPPB_PAID_PLUGIN_DIR .'/add-ons/custom-redirects/custom_redirects_admin.php' );
    }
}



/**
 * Function that handles custom user redirects
 *
 * @param	string	            $type			- type of the redirect
 * @param	string	            $redirect_url   - redirect url or NULL
 * @param	null|string|object	$user			- username, user email or user data
 * @param   string              $user_role      - user role or NULL
 *
 * @return	string	$redirect_url   - custom redirect url from database (if found), otherwise is returned unchanged
 */
function wppb_custom_redirect_url( $type, $redirect_url = NULL, $user = NULL, $user_role = NULL ) {
	// variable used to skip redirects
	$skip_next_redirects = 0;

	// get custom redirects options from database
	$wppb_cr_options = array(
		'wppb_cr_user',
		'wppb_cr_role',
		'wppb_cr_global',
		'wppb_cr_default_wp_pages'
	);

	foreach( $wppb_cr_options as $option ) {
		$$option = get_option( $option, 'not_found' );
	}

	// get user data
    if( is_string( $user ) ) {
        $user_data = get_user_by( 'login', $user );
        if( isset( $user_data ) && isset( $user_data->ID ) ) {
            $user = $user_data;
        } else {
            $user_data = get_user_by( 'email', $user );
            if( isset( $user_data ) && isset( $user_data->ID ) ) {
                $user = $user_data;
            }
        }
    }

	// needed for tags
	$wppb_cr_username = NULL;

	// individual user redirects
	$wppb_current_user = wp_get_current_user();

	if( $wppb_cr_user != 'not_found' ) {
		foreach( $wppb_cr_user as $options ) {
			if( array_key_exists('idoruser', $options) && $options['idoruser'] == 'user' ) {
				if( $options['user'] == $wppb_current_user->user_login && $options['type'] == $type ) {
					$redirect_url = $options['url'];
					$skip_next_redirects = 1;
					break;
				} elseif( isset( $user ) ) {
					if( ( isset( $user->user_login ) && $options['user'] == $user->user_login && $options['type'] == $type )
						|| ( $user != NULL && $options['user'] == $user && $options['type'] == $type )
					) {
						$redirect_url = $options['url'];
						$wppb_cr_username = $user;
						$skip_next_redirects = 1;
						break;
					}
				}
			} elseif( array_key_exists('idoruser', $options) && $options['idoruser'] == 'userid' ) {
				if( (int) $options['user'] === $wppb_current_user->ID && $options['type'] == $type ) {
					$redirect_url = $options['url'];
					$skip_next_redirects = 1;
					break;
				} elseif( isset( $user ) ) {
					if( ( isset( $user->ID ) && (int) $options['user'] === $user->ID && $options['type'] == $type )
						|| ( $user != NULL && (int) $options['user'] === $user && $options['type'] == $type )
					) {
						$redirect_url = $options['url'];
						$wppb_cr_username = $user;
						$skip_next_redirects = 1;
						break;
					}
				}
			}
		}
	}

	// user role based redirects
	if( $wppb_cr_role != 'not_found' ) {
		if( $skip_next_redirects != 1 ) {
			foreach( $wppb_cr_role as $options ) {
				if( isset( $wppb_current_user ) && ! empty( $wppb_current_user->roles ) ) {
					foreach( $wppb_current_user->roles as $role => $value ) {
						if( $options['user_role'] == $value && $options['type'] == $type ) {
							$redirect_url = $options['url'];
							$skip_next_redirects = 1;
							break;
						}
					}
				}

				if( isset( $user ) ) {
					if( isset( $user->caps ) && ! empty( $user->caps ) ) {
						foreach( $user->caps as $role => $value ) {
							if( $options['user_role'] == $role && $options['type'] == $type ) {
								$redirect_url = $options['url'];
								$wppb_cr_username = $user;
								$skip_next_redirects = 1;
								break;
							}
						}
					}

					if( $user != NULL ) {
						// wp_signon return wp_error or wp_user object. So we're checking for that.
						if ( is_object($user) && !is_wp_error($user)){
							$user_data = $user;
						} else {
							$user_data = get_user_by( 'login', sanitize_user( $user ) );
						}

						if( ! isset( $user_data ) || empty( $user_data ) ) {
							$user_data = get_user_by( 'email', sanitize_email( $user ) );
						}

						if( isset( $user_data->caps ) && ! empty( $user_data->caps ) ) {
							foreach( $user_data->caps as $role => $value ) {
								if( $options['user_role'] == $role && $options['type'] == $type ) {
									$redirect_url = $options['url'];
									$wppb_cr_username = $user_data;
									$skip_next_redirects = 1;
									break;
								}
							}
						} elseif( $user_data === false && $options['type'] == 'after_registration' ) {
							if( isset( $user_role ) && $user_role !== NULL ) {
								$wppb_default_user_role = $user_role;
							} else {
								$wppb_default_user_role = get_option( 'default_role' );
							}

							if( $options['user_role'] == $wppb_default_user_role && $options['type'] == $type ) {
								$redirect_url = $options['url'];
								$wppb_cr_username = $user;
								$skip_next_redirects = 1;
								break;
							}
						}
					}
				}
			}
		}
	}

	// global redirects
	if( $wppb_cr_global != 'not_found' ) {
		if( $skip_next_redirects != 1 ) {
			if( ! empty( $wppb_cr_global ) && is_array( $wppb_cr_global ) ) {
				foreach( $wppb_cr_global as $options ) {
					if( $options['type'] == $type ) {
						$redirect_url = $options['url'];
						break;
					}
				}
			}
		}
	}

	// redirect default WordPress forms and pages
	if( $wppb_cr_default_wp_pages != 'not_found' ) {
		foreach( $wppb_cr_default_wp_pages as $options ) {
			if( $options['type'] == $type ) {
				$redirect_url = $options['url'];
				break;
			}
		}
	}

	if( ! empty( $redirect_url ) ) {
		if( wppb_check_missing_http( $redirect_url ) ) {
			$redirect_url = 'http://' . $redirect_url;
		}

		if( ! isset( $wppb_cr_username->ID ) ) {
            if( isset( $user ) && isset( $user->ID ) ) {
                $wppb_cr_username = $user;
            } else {
                $wppb_cr_username = $wppb_current_user;
            }
		}

		$redirect_url = wppb_cr_replace_tags( $redirect_url, $wppb_cr_username );
	}

	return $redirect_url;
}


/* the function needed to block access to the admin-panel (if requested) */
function wppb_restrict_dashboard_access() {
	$versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	if( in_array( PROFILE_BUILDER, $versions ) ) {
		if( is_admin() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
			$wppb_module_settings = get_option( 'wppb_module_settings' );

			if( isset( $wppb_module_settings['wppb_customRedirect'] ) && $wppb_module_settings['wppb_customRedirect'] == 'show' && function_exists( 'wppb_custom_redirect_url' ) ) {
				$redirect_url = wppb_custom_redirect_url( 'dashboard_redirect' );

				if( defined( 'DOING_AJAX' ) || ( ( isset( $_GET['action'] ) && $_GET['action'] == 'logout' ) && isset( $_GET['redirect_to'] ) ) ) {
					//let wp log out the user or pass ajax calls
				} elseif( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
					$redirect_url = apply_filters( 'wppb_dashboard_redirect_url', $redirect_url );

					if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
						wp_redirect( $redirect_url );

						exit;
					}
				}
			}
		}
	}
}
add_action( 'admin_init', 'wppb_restrict_dashboard_access' );


/* the function needed to redirect from default WordPress forms and pages (if requested) */
function wppb_redirect_default_wp_pages() {
	$versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	if( in_array( PROFILE_BUILDER, $versions ) ) {
		if( ! is_admin() ) {
			$wppb_module_settings = get_option( 'wppb_module_settings' );

			if( isset( $wppb_module_settings['wppb_customRedirect'] ) && $wppb_module_settings['wppb_customRedirect'] == 'show' && function_exists( 'wppb_custom_redirect_url' ) ) {
				global $pagenow;

                //force wp-login.php if you accidentally get locked out
                if( $pagenow === 'wp-login.php' && ( isset( $_GET['wppb_force_wp_login'] ) || ( isset( $_SERVER['HTTP_REFERER'] )  && strpos( esc_url_raw( $_SERVER['HTTP_REFERER'] ), 'wppb_force_wp_login=true' ) !== false ) || ( isset($_REQUEST['redirect_to'])  && strpos( sanitize_text_field( $_REQUEST['redirect_to'] ), 'wppb_force_wp_login=true' ) !== false ) ) )
                    return;

				// the part for the WP register page
				if( ( $pagenow == 'wp-login.php' ) && ( isset( $_GET['action'] ) ) && ( $_GET['action'] == 'register' ) ) {
					$redirect_url = wppb_custom_redirect_url( 'register' );

					if( ! current_user_can( 'manage_options' ) ) {
						$redirect_url = apply_filters( 'wppb_wp_default_register_redirect_url', $redirect_url );

						if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
							wp_redirect( $redirect_url );

							exit;
						}
					}

				// the part for the WP password recovery
				} elseif( ( $pagenow == 'wp-login.php' ) && ( isset( $_GET['action'] ) ) && ( $_GET['action'] == 'lostpassword' ) ) {
					$redirect_url = wppb_custom_redirect_url( 'lostpassword' );

					if( ! current_user_can( 'manage_options' ) ) {
						$redirect_url = apply_filters( 'wppb_wp_default_lost_password_redirect_url', $redirect_url );

						if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
							wp_redirect( $redirect_url );

							exit;
						}
					}

				// the part for WP login; BEFORE login; this part only covers when the user isn't logged in and NOT when he just logged out
                } elseif( ( ( $pagenow == 'wp-login.php' ) && ( ! isset( $_GET['action'] ) ) && ( ! isset( $_GET['loggedout'] ) ) && ! isset( $_POST['wppb_login'] ) && ! isset( $_POST['wppb_redirect_check'] ) ) || ( isset( $_GET['redirect_to'] ) && ( ( isset( $_GET['action'] ) && $_GET['action'] != 'logout' ) || !isset( $_GET['action'] ) ) ) ) {
					$redirect_url = wppb_custom_redirect_url( 'login' );

					if( ! current_user_can( 'manage_options' ) ) {
						$redirect_url = apply_filters( 'wppb_wp_default_login_redirect_url', $redirect_url );

						if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
							wp_redirect( $redirect_url );

							exit;
						}
					}

				// the part for WP login; AFTER logout; this part only covers when the user was logged in and has logged out
				} elseif( ( $pagenow == 'wp-login.php' ) && ( isset( $_GET['loggedout'] ) ) && ( $_GET['loggedout'] == 'true' ) ) {
                    $redirect_url = wppb_custom_redirect_url( 'after_logout' );

                    if( ! isset( $redirect_url ) || empty( $redirect_url ) ) {
                        $redirect_url = wppb_custom_redirect_url( 'login' );

                        if( ! current_user_can( 'manage_options' ) ) {
                            $redirect_url = apply_filters( 'wppb_wp_default_login_redirect_url', $redirect_url );

                            if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
                                wp_redirect( $redirect_url );

                                exit;
                            }
                        }
                    } else {
                        $redirect_url = apply_filters( 'wppb_after_logout_redirect_url', $redirect_url );

                        if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
                            wp_redirect( $redirect_url );

                            exit;
                        }
                    }
				}
			}
		}
	}
}
add_action( 'init', 'wppb_redirect_default_wp_pages' );


/* the function needed to redirect from default WordPress Author Archive (if requested) */
function wppb_redirect_default_wp_author_archive() {
	$versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	if( in_array( PROFILE_BUILDER, $versions ) ) {
		if( ! is_admin() ) {
			$wppb_module_settings = get_option( 'wppb_module_settings' );

			if( isset( $wppb_module_settings['wppb_customRedirect'] ) && $wppb_module_settings['wppb_customRedirect'] == 'show' && function_exists( 'wppb_custom_redirect_url' ) ) {
				if( is_author() ) {
					$redirect_url = wppb_custom_redirect_url( 'authorarchive' );

					if( ! current_user_can( 'manage_options' ) ) {
						$redirect_url = apply_filters( 'wppb_wp_default_author_archive_redirect_url', $redirect_url );

						if( isset( $redirect_url ) && ! empty( $redirect_url ) ) {
							wp_redirect( $redirect_url );

							exit;
						}
					}
				}
			}
		}
	}
}
add_action( 'get_header', 'wppb_redirect_default_wp_author_archive' );


/* function used to replace Custom Redirects tags in URLs */
function wppb_cr_replace_tags( $redirect_url, $wppb_cr_username = NULL ) {
	$wppb_cr_tags = apply_filters( 'wppb_cr_tags', array(
		'{{homeurl}}' => untrailingslashit( home_url() ),//this should not return a trailing slash
		'{{siteurl}}' => untrailingslashit( site_url() ),//this should not return a trailing slash
		'{{user_id}}' => ( isset( $wppb_cr_username->ID ) ? $wppb_cr_username->ID : ( $wppb_cr_username = NULL ) ),
		'{{user_nicename}}' => ( isset( $wppb_cr_username->user_nicename ) ? $wppb_cr_username->user_nicename : ( $wppb_cr_username = NULL ) ),
		'{{http_referer}}' => ( isset( $_POST['wppb_referer_url'] ) ? esc_url_raw( $_POST['wppb_referer_url'] ) : NULL ),
        '{{redirect_to}}' => ( isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : NULL ),
	) );

	foreach( $wppb_cr_tags as $key => $value ) {
		if( strpos( $redirect_url, $key ) !== false ) {
			if( ( $key == '{{user_id}}' ) || ( $key == '{{user_nicename}}' ) ) {
				if( isset( $wppb_cr_username ) ) {
					$redirect_url = str_replace( $key, $value, $redirect_url );
				} else {
					$redirect_url = '';
				}
			} elseif( $key == '{{http_referer}}' && $value === NULL ) {
				if( isset( $_SERVER['HTTP_REFERER'], $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( $_SERVER['REQUEST_URI'] ), '/wp-login.php' ) === false ) {
					$redirect_url = str_replace( $key, esc_url_raw( $_SERVER['HTTP_REFERER'] ), $redirect_url );
                    $redirect_url = remove_query_arg( 'reauth', $redirect_url );
				} else {
					$redirect_url = '';
				}
			} else {
				$redirect_url = str_replace( $key, $value, $redirect_url );
			}
		}
	}

	return $redirect_url;
}


/* function used to redirect users after requesting a password change (if such e redirect is set in Custom Redirects) */
function wppb_redirect_after_password_change_request( $message, $user_email ){

    $user_data = get_user_by( 'email', $user_email );
    $redirect_url = wppb_get_redirect_url( 'normal', 'after_password_change_request', '', $user_data->user_login );

    if ( !empty( $redirect_url ))
        $message = '<script type="text/javascript"> window.location.href = "' . $redirect_url .'" </script>';

    return $message;
}
add_filter( 'wppb_recover_password_sent_message1', 'wppb_redirect_after_password_change_request', 10, 2 );