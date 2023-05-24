<?php

namespace LLAR\Core;

use LLAR\Core\Http\Http;

if( !defined( 'ABSPATH' ) ) exit;

class Ajax {

	/**
	 * Register all ajax requests & handlers
	 */
	public function register() {

		add_action( 'wp_ajax_limit-login-unlock', array( $this, 'ajax_unlock' ) );
		add_action( 'wp_ajax_dismiss_review_notice', array( $this, 'dismiss_review_notice_callback' ) );
		add_action( 'wp_ajax_dismiss_notify_notice', array( $this, 'dismiss_notify_notice_callback' ) );
		add_action( 'wp_ajax_enable_notify', array( $this, 'enable_notify_callback' ) );
		add_action( 'wp_ajax_app_config_save', array( $this, 'app_config_save_callback' ) );
		add_action( 'wp_ajax_app_setup', array( $this, 'app_setup_callback' ) );
		add_action( 'wp_ajax_app_log_action', array( $this, 'app_log_action_callback' ) );
		add_action( 'wp_ajax_app_load_log', array( $this, 'app_load_log_callback' ) );
		add_action( 'wp_ajax_app_load_lockouts', array( $this, 'app_load_lockouts_callback' ) );
		add_action( 'wp_ajax_app_load_acl_rules', array( $this, 'app_load_acl_rules_callback' ) );
		add_action( 'wp_ajax_app_load_country_access_rules', array( $this, 'app_load_country_access_rules_callback' ) );
		add_action( 'wp_ajax_app_toggle_country', array( $this, 'app_toggle_country_callback' ) );
		add_action( 'wp_ajax_app_country_rule', array( $this, 'app_country_rule_callback' ) );
		add_action( 'wp_ajax_app_acl_add_rule', array( $this, 'app_acl_add_rule_callback' ) );
		add_action( 'wp_ajax_app_acl_remove_rule', array( $this, 'app_acl_remove_rule_callback' ) );
		add_action( 'wp_ajax_nopriv_get_remaining_attempts_message', array(
			$this,
			'get_remaining_attempts_message_callback'
		) );
		add_action( 'wp_ajax_subscribe_email', array( $this, 'subscribe_email_callback' ) );
		add_action( 'wp_ajax_dismiss_onboarding_popup', array( $this, 'dismiss_onboarding_popup_callback' ) );
		add_action( 'wp_ajax_toggle_auto_update', array( $this, 'toggle_auto_update_callback' ) );
	}

	public function ajax_unlock() {
		check_ajax_referer( 'limit-login-unlock', 'sec' );
		$ip = (string) @$_POST['ip'];

		$lockouts = (array) Config::get( 'lockouts' );

		if ( isset( $lockouts[ $ip ] ) ) {
			unset( $lockouts[ $ip ] );
			Config::update( 'lockouts', $lockouts );
		}

		//save to log
		$user_login = @(string) $_POST['username'];
		$log        = Config::get( 'logged' );

		if ( @$log[ $ip ][ $user_login ] ) {
			if ( ! is_array( $log[ $ip ][ $user_login ] ) ) {
				$log[ $ip ][ $user_login ] = array(
					'counter' => $log[ $ip ][ $user_login ],
				);
			}
			$log[ $ip ][ $user_login ]['unlocked'] = true;

			Config::update( 'logged', $log );
		}

		header( 'Content-Type: application/json' );
		echo 'true';
		exit;
	}

	public function dismiss_review_notice_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false;

		if ( $type === 'dismiss' ) {

			Config::update( 'review_notice_shown', true );
		}

		if ( $type === 'later' ) {

			Config::update( 'activation_timestamp', time() );
		}

		wp_send_json_success( array() );
	}

	public function dismiss_notify_notice_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false;

		if ( $type === 'dismiss' ) {

			Config::update( 'enable_notify_notice_shown', true );
		}

		if ( $type === 'later' ) {

			Config::update( 'notice_enable_notify_timestamp', time() );
		}

		wp_send_json_success( array() );
	}

	public function enable_notify_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$notify_methods = explode( ',', Config::get( 'lockout_notify' ) );

		if ( ! in_array( 'email', $notify_methods ) ) {

			$notify_methods[] = 'email';
		}

		Config::update( 'lockout_notify', implode( ',', $notify_methods ) );
		Config::update( 'enable_notify_notice_shown', true );

		wp_send_json_success( array() );
	}

	public function app_setup_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		if ( ! empty( $_POST['code'] ) ) {

			$setup_code = sanitize_text_field( $_POST['code'] );
			$link       = strrev( $setup_code );

			if ( $setup_result = CloudApp::setup( $link ) ) {

				if ( $setup_result['success'] ) {

					if ( $setup_result['app_config'] ) {

						Helpers::cloud_app_update_config( $setup_result['app_config'], true );

						Config::update( 'active_app', 'custom' );
						Config::update( 'app_setup_code', $setup_code );

						wp_send_json_success( array(
							'msg' => ( ! empty( $setup_result['app_config']['messages']['setup_success'] ) )
								? $setup_result['app_config']['messages']['setup_success']
								: __( 'The app has been successfully imported.', 'limit-login-attempts-reloaded' )
						) );
					}

				} else {

					wp_send_json_error( array(
						'msg' => $setup_result['error']
					) );
				}
			}
		}

		wp_send_json_error( array(
			'msg' => __( 'Please specify the Setup Code', 'limit-login-attempts-reloaded' )
		) );
	}

	public function app_log_action_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		if ( ! empty( $_POST['method'] ) && ! empty( $_POST['params'] ) ) {

			$method = sanitize_text_field( $_POST['method'] );
			$params = (array) $_POST['params'];

			if ( ! in_array( $method, array( 'lockout/delete', 'acl/create', 'acl/delete' ) ) ) {

				wp_send_json_error( array(
					'msg' => 'Wrong method.'
				) );
			}

			if ( $response = LimitLoginAttempts::$cloud_app->request( $method, 'post', $params ) ) {

				wp_send_json_success( array(
					'msg' => $response['message']
				) );

			} else {

				wp_send_json_error( array(
					'msg' => 'The endpoint is not responding. Please contact your app provider to settle that.'
				) );
			}
		}

		wp_send_json_error( array(
			'msg' => 'Wrong App id.'
		) );
	}

	public function app_acl_add_rule_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		if ( ! empty( $_POST['pattern'] ) && ! empty( $_POST['rule'] ) && ! empty( $_POST['type'] ) ) {

			$pattern = sanitize_text_field( $_POST['pattern'] );
			$rule    = sanitize_text_field( $_POST['rule'] );
			$type    = sanitize_text_field( $_POST['type'] );

			if ( ! in_array( $rule, array( 'pass', 'allow', 'deny' ) ) ) {

				wp_send_json_error( array(
					'msg' => 'Wrong rule.'
				) );
			}

			if ( $response = LimitLoginAttempts::$cloud_app->acl_create( array(
				'pattern' => $pattern,
				'rule'    => $rule,
				'type'    => ( $type === 'ip' ) ? 'ip' : 'login',
			) ) ) {

				wp_send_json_success( array(
					'msg' => $response['message']
				) );

			} else {

				wp_send_json_error( array(
					'msg' => 'The endpoint is not responding. Please contact your app provider to settle that.'
				) );
			}
		}

		wp_send_json_error( array(
			'msg' => 'Wrong input data.'
		) );
	}

	public function app_acl_remove_rule_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		if ( ! empty( $_POST['pattern'] ) && ! empty( $_POST['type'] ) ) {

			$pattern = sanitize_text_field( $_POST['pattern'] );
			$type    = sanitize_text_field( $_POST['type'] );

			if ( $response = LimitLoginAttempts::$cloud_app->acl_delete( array(
				'pattern' => $pattern,
				'type'    => ( $type === 'ip' ) ? 'ip' : 'login',
			) ) ) {

				wp_send_json_success( array(
					'msg' => $response['message']
				) );

			} else {

				wp_send_json_error( array(
					'msg' => 'The endpoint is not responding. Please contact your app provider to settle that.'
				) );
			}
		}

		wp_send_json_error( array(
			'msg' => 'Wrong input data.'
		) );
	}

	public function app_load_log_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$offset = sanitize_text_field( $_POST['offset'] );
		$limit  = sanitize_text_field( $_POST['limit'] );

		$log = LimitLoginAttempts::$cloud_app->log( $limit, $offset );

		if ( $log ) {

			$date_format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$countries_list = Helpers::get_countries_list();

			ob_start();
			if ( empty( $log['items'] ) && ! empty( $log['offset'] ) ) : ?>
			<?php elseif ( $log['items'] ) : ?>

				<?php foreach ( $log['items'] as $item ) :
					$country_name = ! empty( $countries_list[ $item['country_code'] ] ) ? $countries_list[ $item['country_code'] ] : '';
					?>
                    <tr>
                        <td class="llar-col-nowrap"><?php echo get_date_from_gmt( date( 'Y-m-d H:i:s', $item['created_at'] ), $date_format ); ?></td>
                        <td>
                            <div class="llar-log-country-flag">
                                <span class="llar-tooltip" data-text="<?php echo esc_attr( $country_name ); ?>">
                                    <img src="<?php echo LLA_PLUGIN_URL . 'assets/img/flags/' . esc_attr( $item['country_code'] ) . '.png' ?>">
                                </span>&nbsp;<span><?php echo esc_html( $item['ip'] ); ?></span></div>
                        </td>
                        <td><?php echo esc_html( $item['gateway'] ); ?></td>
                        <td><?php echo ( is_null( $item['login'] ) ) ? '-' : esc_html( $item['login'] ); ?></td>
                        <td><?php echo ( is_null( $item['result'] ) ) ? '-' : esc_html( $item['result'] ); ?></td>
                        <td><?php echo ( is_null( $item['reason'] ) ) ? '-' : esc_html( $item['reason'] ); ?></td>
                        <td><?php echo ( is_null( $item['pattern'] ) ) ? '-' : esc_html( $item['pattern'] ); ?></td>
                        <td><?php echo ( is_null( $item['attempts_left'] ) ) ? '-' : esc_html( $item['attempts_left'] ); ?></td>
                        <td><?php echo ( is_null( $item['time_left'] ) ) ? '-' : esc_html( $item['time_left'] ) ?></td>
                        <td class="llar-app-log-actions">
							<?php
							if ( $item['actions'] ) {

								foreach ( $item['actions'] as $action ) {

									echo '<button class="button llar-app-log-action-btn js-app-log-action" style="color:' . esc_attr( $action['color'] ) . ';border-color:' . esc_attr( $action['color'] ) . '" 
                                    data-method="' . esc_attr( $action['method'] ) . '" 
                                    data-params="' . esc_attr( json_encode( $action['data'], JSON_FORCE_OBJECT ) ) . '" 
                                    href="#" title="' . $action['label'] . '"><i class="dashicons dashicons-' . esc_attr( $action['icon'] ) . '"></i></button>';
								}
							} else {
								echo '-';
							}
							?>
                        </td>
                    </tr>
				<?php endforeach; ?>
			<?php else : ?>
				<?php if ( empty( $offset ) ) : ?>
                    <tr class="empty-row">
                        <td colspan="100%"
                            style="text-align: center"><?php _e( 'No events yet.', 'limit-login-attempts-reloaded' ); ?></td>
                    </tr>
				<?php endif; ?>
			<?php endif; ?>
			<?php

			wp_send_json_success( array(
				'html'        => ob_get_clean(),
				'offset'      => $log['offset'],
				'total_items' => count( $log['items'] )
			) );

		} else {

			wp_send_json_error( array(
				'msg' => 'The endpoint is not responding. Please contact your app provider to settle that.'
			) );
		}
	}

	public function app_load_lockouts_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$offset = sanitize_text_field( $_POST['offset'] );
		$limit  = sanitize_text_field( $_POST['limit'] );

		$lockouts = LimitLoginAttempts::$cloud_app->get_lockouts( $limit, $offset );

		if ( $lockouts ) {

			ob_start(); ?>

			<?php if ( $lockouts['items'] ) : ?>
				<?php foreach ( $lockouts['items'] as $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item['ip'] ); ?></td>
                        <td><?php echo ( is_null( $item['login'] ) ) ? '-' : esc_html( implode( ',', $item['login'] ) ); ?></td>
                        <td><?php echo ( is_null( $item['count'] ) ) ? '-' : esc_html( $item['count'] ); ?></td>
                        <td><?php echo ( is_null( $item['ttl'] ) ) ? '-' : esc_html( round( ( $item['ttl'] - time() ) / 60 ) ); ?></td>
                    </tr>
				<?php endforeach; ?>

			<?php else: ?>
				<?php if ( empty( $offset ) ) : ?>
                    <tr class="empty-row">
                        <td colspan="4"
                            style="text-align: center"><?php _e( 'No lockouts yet.', 'limit-login-attempts-reloaded' ); ?></td>
                    </tr>
				<?php endif; ?>
			<?php endif; ?>
			<?php

			wp_send_json_success( array(
				'html'   => ob_get_clean(),
				'offset' => $lockouts['offset']
			) );

		} elseif ( intval( LimitLoginAttempts::$cloud_app->last_response_code ) >= 400 && intval( LimitLoginAttempts::$cloud_app->last_response_code ) < 500 ) {

			$app_config = Config::get( 'app_config' );

			wp_send_json_error( array(
				'error_notice' => '<div class="llar-app-notice">
                                        <p>' . $app_config['messages']['sync_error'] . '<br><br>' . sprintf( __( 'Meanwhile, the app falls over to the <a href="%s">default functionality</a>.', 'limit-login-attempts-reloaded' ), admin_url( 'options-general.php?page=limit-login-attempts&tab=logs-local' ) ) . '</p>
                                    </div>'
			) );
		} else {

			wp_send_json_error( array(
				'msg' => 'The endpoint is not responding. Please contact your app provider to settle that.'
			) );
		}
	}

	public function app_load_acl_rules_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$type   = sanitize_text_field( $_POST['type'] );
		$limit  = sanitize_text_field( $_POST['limit'] );
		$offset = sanitize_text_field( $_POST['offset'] );

		$acl_list = LimitLoginAttempts::$cloud_app->acl( array(
			'type'   => $type,
			'limit'  => $limit,
			'offset' => $offset
		) );

		if ( $acl_list ) {

			ob_start(); ?>

			<?php if ( $acl_list['items'] ) : ?>
				<?php foreach ( $acl_list['items'] as $item ) : ?>
                    <tr class="llar-app-rule-<?php echo esc_attr( $item['rule'] ); ?>">
                        <td class="rule-pattern" scope="col"><?php echo esc_html( $item['pattern'] ); ?></td>
                        <td scope="col"><?php echo esc_html( $item['rule'] ); ?><?php echo ( $type === 'ip' ) ? '<span class="origin">' . esc_html( $item['origin'] ) . '</span>' : ''; ?></td>
                        <td class="llar-app-acl-action-col" scope="col">
                            <button class="button llar-app-acl-remove" data-type="<?php echo esc_attr( $type ); ?>"
                                    data-pattern="<?php echo esc_attr( $item['pattern'] ); ?>"><span
                                        class="dashicons dashicons-no"></span></button>
                        </td>
                    </tr>
				<?php endforeach; ?>
			<?php else : ?>
                <tr class="empty-row">
                    <td colspan="3"
                        style="text-align: center"><?php _e( 'No rules yet.', 'limit-login-attempts-reloaded' ); ?></td>
                </tr>
			<?php endif; ?>
			<?php

			wp_send_json_success( array(
				'html'   => ob_get_clean(),
				'offset' => $acl_list['offset']
			) );

		} else {

			wp_send_json_error( array(
				'msg' => 'The endpoint is not responding. Please contact your app provider to settle that.'
			) );
		}
	}

	public function app_load_country_access_rules_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$country_rules = LimitLoginAttempts::$cloud_app->country();

		if ( $country_rules ) {

			wp_send_json_success( $country_rules );
		} else {

			wp_send_json_error( array(
				'msg' => 'Something wrong.'
			) );
		}
	}

	public function app_toggle_country_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$code        = sanitize_text_field( $_POST['code'] );
		$action_type = sanitize_text_field( $_POST['type'] );

		if ( ! $code ) {

			wp_send_json_error( array(
				'msg' => 'Wrong country code.'
			) );
		}

		$result = false;

		if ( $action_type === 'add' ) {

			$result = LimitLoginAttempts::$cloud_app->country_add( array(
				'code' => $code
			) );

		} else if ( $action_type === 'remove' ) {

			$result = LimitLoginAttempts::$cloud_app->country_remove( array(
				'code' => $code
			) );
		}

		if ( $result ) {

			wp_send_json_success( array() );
		} else {

			wp_send_json_error( array(
				'msg' => 'Something wrong.'
			) );
		}
	}

	public function app_country_rule_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		$rule = sanitize_text_field( $_POST['rule'] );

		if ( empty( $rule ) || ! in_array( $rule, array( 'allow', 'deny' ) ) ) {

			wp_send_json_error( array(
				'msg' => 'Wrong rule.'
			) );
		}

		$result = LimitLoginAttempts::$cloud_app->country_rule( array(
			'rule' => $rule
		) );

		if ( $result ) {

			wp_send_json_success( array() );
		} else {

			wp_send_json_error( array(
				'msg' => 'Something wrong.'
			) );
		}
	}

	public function subscribe_email_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		Config::update( 'onboarding_popup_shown', true );

		$email            = sanitize_text_field( trim( $_POST['email'] ) );
		$is_subscribe_yes = sanitize_text_field( $_POST['is_subscribe_yes'] ) === 'true';

		$admin_email   = ( ! is_multisite() ) ? get_option( 'admin_email' ) : get_site_option( 'admin_email' );

		if ( ! empty( $email ) && is_email( $email ) ) {

			Config::update( 'admin_notify_email', $email );
			Config::update( 'lockout_notify', 'email' );

			if ( $is_subscribe_yes ) {
				$response = Http::post( 'https://api.limitloginattempts.com/my/key', array(
					'data' => array(
						'email' => $email
					)
				) );

				if ( !empty( $response['error'] ) ) {

					wp_send_json_error( $response['error'] );
				} else {

					$response_body = json_decode( $response['data'], true );

					if ( ! empty( $response_body['key'] ) ) {
						Config::update( 'cloud_key', $response_body['key'] );
					}

					wp_send_json_success();
				}
			}
		} else if ( empty( $email ) ) {
			Config::update( 'admin_notify_email', $admin_email );
			Config::update( 'lockout_notify', '' );
		}

		wp_send_json_error( array( 'email' => $email, 'is_subscribe_yes' => $is_subscribe_yes ) );
		exit();
	}

	public function dismiss_onboarding_popup_callback() {

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error( array() );
		}

		check_ajax_referer( 'llar-action', 'sec' );

		Config::update( 'onboarding_popup_shown', true );

		wp_send_json_success();
	}

	public function get_remaining_attempts_message_callback() {

		check_ajax_referer( 'llar-action', 'sec' );

		if ( ! session_id() ) {
			session_start();
		}

		$remaining = ! empty( $_SESSION['login_attempts_left'] ) ? intval( $_SESSION['login_attempts_left'] ) : 0;
		$message   = ( ! $remaining ) ? '' : sprintf( _n( "<strong>%d</strong> attempt remaining.", "<strong>%d</strong> attempts remaining.", $remaining, 'limit-login-attempts-reloaded' ), $remaining );
		wp_send_json_success( $message );
	}

	public function toggle_auto_update_callback() {

		check_ajax_referer('llar-action', 'sec');

		$value = sanitize_text_field( $_POST['value'] );
		$auto_update_plugins = get_site_option( 'auto_update_plugins', array() );

		if( $value === 'yes' ) {
			$auto_update_plugins[] = LLA_PLUGIN_BASENAME;
			Config::update( 'auto_update_choice', 1 );

		} else if ( $value === 'no' ) {
			if ( ( $key = array_search( LLA_PLUGIN_BASENAME, $auto_update_plugins ) ) !== false ) {
				unset($auto_update_plugins[$key]);
			}
			Config::update( 'auto_update_choice', 0 );
		}

		update_site_option( 'auto_update_plugins', $auto_update_plugins );

		wp_send_json_success();
	}
}
