<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Piotnetforms_License_Service {

    private static function get_domain( $url ) {
        return preg_replace( '/^www\./', '', wp_parse_url( $url, PHP_URL_HOST ) );
    }

    public static function login( $credential ) {
        $body = [
            'domain'       => self::get_domain( get_option( 'siteurl' ) ),
            'pro_version'  => PIOTNETFORMS_PRO_VERSION,
            'free_version' => PIOTNETFORMS_VERSION,
            'wp_version' => get_bloginfo( 'version' ),
            'php_version' => PHP_VERSION,
        ] + $credential;

        $response = wp_remote_post(
            'https://piotnetforms.com/connect/v1/get_license.php',
            [
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
                'body'        => $body,
                'sslverify'   => false,
            ]
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return [
                'error' => "Can't connect to Piotnetforms: " . $error_message,
            ];
        }

        $response_body = wp_remote_retrieve_body( $response );
        if ( is_wp_error( $response_body ) ) {
            $error_message = $response_body->get_error_message();
            return [
                'error' => "Can't retrieve body from Piotnetforms: " . $error_message,
            ];
        }

        $response_data = json_decode( $response_body, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return [
                'error' => "Can't prase data from Piotnetforms.",
            ];
        }

        if ( $response_data['status'] === 'OK' ) {
            update_option( 'piotnetforms_license', $response_data['license'] );
        }

        return $response_data;
    }

    public static function remove_site( $credential ) {
        $body = [
            'domain'       => self::get_domain( get_option( 'siteurl' ) ),
            'pro_version'  => PIOTNETFORMS_PRO_VERSION,
            'free_version' => PIOTNETFORMS_VERSION,
            'wp_version' => get_bloginfo( 'version' ),
            'php_version' => PHP_VERSION,
        ] + $credential;

        $response = wp_remote_post(
            'https://piotnetforms.com/connect/v1/remove_site.php',
            [
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
                'body'        => $body,
                'sslverify'   => false,
            ]
        );

        delete_option('piotnetforms_license');

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return [
                'error' => "Can't connect to Piotnetforms: " . $error_message,
            ];
        }

        $response_body = wp_remote_retrieve_body( $response );
        if ( is_wp_error( $response_body ) ) {
            $error_message = $response_body->get_error_message();
            return [
                'error' => "Can't retrieve body from Piotnetforms: " . $error_message,
            ];
        }

        return null;
    }
}

function piotnetforms_do_login() {
    if (!isset($_POST['username'])) {
        return [ 'error' => 'Please fill username' ];
    } else if (!isset($_POST['password'])) {
        return [ 'error' => 'Please fill password' ];
    } else {
        $credential = [
            'username'     => $_POST['username'],
            'password'     => $_POST['password'],
        ];
        return Piotnetforms_License_Service::login($credential);
    }
}

function piotnetforms_do_remove_license() {
    $license = get_option( 'piotnetforms_license' );
    if(empty($license)) {
        return null;
    }

    $credential = [
        'license_key' => $license['license_key']
    ];
    return Piotnetforms_License_Service::remove_site($credential);
}

    if ( !isset($_POST['action']) ) {
        $license = get_option( 'piotnetforms_license' );
        if(!empty($license)) {
            $credential = [
                'license_key' => $license['license_key']
            ];
            $login_response = Piotnetforms_License_Service::login($credential);
        }
    } else if ($_POST['action'] == 'active_license'){
        $login_response = piotnetforms_do_login();
    } else if ($_POST['action'] == 'remove_license'){
        $login_response = piotnetforms_do_remove_license();
    }

    $message = '';
    if (!empty($login_response) && !empty($login_response['error'])) {
        $message = $login_response['error'];
    }

    $license = get_option( 'piotnetforms_license' );
    $has_license = !empty($license);

	if ( isset($_GET['post']) ) {

		echo '<div class="piotnetforms-builder">';
		echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
		echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/tinymce/tinymce-upload.php"></div>';

		if ( current_user_can( 'edit_others_posts' ) ) {

			$post_id = $_GET['post'];
			$piotnetforms_data = get_post_meta( $post_id, '_piotnetforms_data', true );

			$widget_infos = [];
			$global_settings = [];

			$editor = new piotnetforms_Editor();
			$widget_object                              = new piotnetforms_Section();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );

			$widget_object                              = new piotnetforms_Column();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();

			$widget_object                              = new piotnetforms_Text();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new piotnetforms_Button();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new piotnetforms_Image();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new piotnetforms_Icon();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			// $widget_object                              = new piotnetforms_Space();
			// $widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			// $editor->register_widget( $widget_object );
			// echo $editor->register_script( $widget_object );

			// $widget_object                              = new piotnetforms_Divider();
			// $widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			// $editor->register_widget( $widget_object );
			// echo $editor->register_script( $widget_object );

			$widget_object                              = new piotnetforms_Shortcode();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );

			$widget_object                              = new Piotnetforms_Field();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new Piotnetforms_Form_Global();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );
			$global_settings_structure[$widget_object->get_type()] = array(
				'type' => $widget_object->get_type(),
				'fields' => [],
			);

			// $widget_object                              = new piotnetforms_Social_Icon();
			// $widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			// $editor->register_widget( $widget_object );
			// echo $editor->register_script( $widget_object );

			$widget_object                              = new Piotnetforms_Submit();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new Piotnetforms_Preview_Submissions();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new Piotnetforms_Lost_Password();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new Piotnetforms_Multi_Step_Form();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );

			$widget_object                              = new Piotnetforms_Booking();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			$widget_object                              = new Piotnetforms_Woocommerce_Checkout();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );

			$widget_object                              = new piotnetforms_Icon_List();
			$widget_infos[ $widget_object->get_type() ] = $widget_object->get_info();
			$editor->register_widget( $widget_object );
			echo $editor->register_script( $widget_object );

			echo '<div class="piotnetforms-editor__collapse-button-open" title="Open Editor" data-piotnetforms-editor-collapse-button-open><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-collapse-open.svg"></div>';

			echo '<div class="piotnetforms-editor">';

			echo '<div class="piotnetforms-settings">';
				$editor->editor_panel();
			echo '</div>';

			// echo '<div class="piotnet-widget-preview piotnetforms" id="piotnetforms" data-piotnet-widget-preview data-piotnet-sortable>';
			// 	$editor->editor_preview( $widgets_settings );
			// echo '</div>';

			echo '<div class="piotnetforms-editor__bottom">';
				echo '<div class="piotnetforms-editor__tools">';
					echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__device-desktop" data-piotnet-control-responsive="desktop"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-desktop.svg"></div>';
					echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__device-tablet" data-piotnet-control-responsive="tablet"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-tablet.svg"></div>';
					echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__device-mobile" data-piotnet-control-responsive="mobile"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-mobile.svg"></div>';
					
					echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__view"><a href="' . get_permalink($post_id) . '" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-view.svg"></a></div>';
					echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__tools-item--global-settings" data-piotnet-global-settings="form-global"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-global-settings.svg" title="Global Settings"></div>';
					echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__wp-dashboard"><a href="' . admin_url( 'edit.php?post_type=piotnetforms' ) . '"><i class="fab fa-wordpress-simple"></i></a></div>';
				echo '</div>';

				echo '<div class="piotnetforms-editor__save" data-piotnetforms-editor-save><i class="far fa-save"></i><i class="icon-spinner-of-dots"></i> Save</div>';
			echo '</div>';

			echo '</div>';

			// echo '<br>';

			echo "<div><input type='hidden' name='piotnet-widget-post-id' value='{$post_id}' data-piotnet-widget-post-id></div>";
			echo "<div><input type='hidden' name='piotnet-widget-breakpoint-tablet' value='1025px' data-piotnet-widget-breakpoint-tablet></div>";
			echo "<div><input type='hidden' name='piotnet-widget-breakpoint-mobile' value='767px' data-piotnet-widget-breakpoint-mobile></div>";

			$htmlspecialchars_data = htmlspecialchars($piotnetforms_data, ENT_QUOTES, 'UTF-8');
			echo "<div><textarea style='display:none' name='piotnetforms-data' data-piotnetforms-data>{$htmlspecialchars_data}</textarea></div>";
			$piotnetforms_global_settings = !empty(get_option( 'piotnetforms_global_settings' )) ? stripslashes( get_option( 'piotnetforms_global_settings' ) ) : json_encode($global_settings_structure);
			$htmlspecialchars_global_settings = htmlspecialchars($piotnetforms_global_settings, ENT_QUOTES, 'UTF-8');

			if (!empty($htmlspecialchars_global_settings)) {
				$htmlspecialchars_global_settings_array = json_decode($piotnetforms_global_settings, true);
				foreach ($htmlspecialchars_global_settings_array as $key => $value) {
					if (!isset($global_settings_structure[$key])) {
						$htmlspecialchars_global_settings_array[$key] = $global_settings_structure[$key];
					}
				}
			}

			$htmlspecialchars_global_settings = json_encode($htmlspecialchars_global_settings_array);

			echo "<div><textarea style='display:none' name='piotnetforms-global-style' data-piotnetforms-global-style>{$htmlspecialchars_global_settings}</textarea></div>";

			// Collape Menu
			// echo "<script>jQuery(document).ready(function( $ ) { $('body').addClass('folded'); })</script>";
			$form_title = ! empty( get_the_title($post_id) ) ? get_the_title($post_id) : ( 'Piotnet Forms #' . $post_id );
			echo '<div data-piotnetforms-form-title="' . $form_title . '"></div>';
			echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
			echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/tinymce/tinymce-upload.php"></div>';
			echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option( 'piotnetforms-stripe-publishable-key' ) ) . '"></div>';

			?>
			<style>
				html.wp-toolbar {
				    padding-top: 0;
				    box-sizing: border-box;
				}

				html, body {
					height: 100%;
					overflow: hidden;
				}

				#wpadminbar {
					display: none;
				}

				#post-body {
					display: flex;
					flex-wrap: wrap;
					margin-right: 0 !important;
				}

				#postbox-container-1 {
					float:right !important;
					margin-right: 0 !important;
					order: 3;
					width: 100% !important;
				}

				#side-sortables {
					width: 100% !important;
				}
			</style>
			<?php

			$controls_manager = new Controls_Manager_Piotnetforms();
			$controls_manager->render();

			echo '<script type="text/json" id="widget_infos">' . json_encode( $widget_infos ) . '</script>';
			echo $this->tab_widget_template();
			echo $this->output_template();
			echo $this->division_output_template();

			$post_url = get_permalink( $post_id );

			if (is_ssl()) {
				$post_url = str_replace('http://', 'https://', $post_url);
			}

			$request_parameter = (strpos($post_url, '?') !== false) ? '&' : '?';

				echo '<div class="piotnetforms-preview">';
					echo '<div class="piotnetforms-preview__inner" data-piotnetforms-preview-inner>';
						echo '<iframe class="piotnetforms-preview__iframe" data-piotnetforms-preview-iframe="' . $post_url . $request_parameter. 'action=piotnetforms"></iframe>';
					echo '</div>';
				echo '</div>';
				echo '<div class="piotnetforms-editor__loading active" data-piotnetforms-editor-loading><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
			echo '</div>';
		}
	} else {
?>
	<div class="wrap">
		<div class="piotnetforms-header">
			<div class="piotnetforms-header__left">
				<div class="piotnetforms-header__logo">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/images/piotnet-logo.png'; ?>" alt="">
				</div>
				<h2 class="piotnetforms-header__headline"><?php esc_html_e( 'Piotnet Forms Settings', 'piotnetforms' ); ?></h2>
			</div>
			<div class="piotnetforms-header__right">
					<a class="piotnetforms-header__button piotnetforms-header__button--gradient" href="https://piotnetforms.com/?wpam_id=1" target="_blank">
					<?php
					if ( !$has_license ) {
						esc_html_e( 'GO PRO NOW', 'piotnetforms' );
					} else {
						esc_html_e( 'Go to Piotnet Forms', 'piotnetforms' ); }
					?>
					</a>
			</div>
		</div>
		<div class="piotnetforms-wrap">
            <div class="piotnetforms-bottom">
                <div class="piotnetforms-bottom__left">
                    <h3><?php _e('Tutorials','piotnetforms'); ?></h3>
                    <a href="https://piotnetforms.com/?wpam_id=1" target="_blank">https://piotnetforms.com</a>
                    <h3><?php _e('Support','piotnetforms'); ?></h3>
                    <a href="mailto:support-piotnetforms@piotnet.com">support-piotnetforms@piotnet.com</a>
                    <h3><?php _e('Reviews','piotnetforms'); ?></h3>
                    <a href="https://wordpress.org/plugins/piotnetforms/#reviews" target="_blank">https://wordpress.org/plugins/piotnetforms/#reviews</a>
                </div>
                <div class="piotnetforms-bottom__right">
                    <div class="piotnetforms-license">
                        <h3><?php _e('License','piotnetforms'); ?></h3>
                        <div class="piotnetforms-license__description">
                            <?php
                            if (!empty($message)) {
                                ?>
                                <div class="piotnetforms-license__description">Status: <?php echo $message; ?></div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        if (!$has_license) {
                            ?>
                            <?php _e('Enter Your Account at ','piotnetforms'); ?><a href="https://piotnetforms.com" target="_blank">https://piotnetforms.com</a> <?php _e('to enable all features and receive new updates.','piotnetforms'); ?>
                            <form method="post" action="#">
                                <table class="form-table">
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Username','piotnetforms'); ?></th>
                                        <td><input type="text" name="username" value="" class="regular-text"/></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Password','piotnetforms'); ?></th>
                                        <td><input type="password" name="password" value="" class="regular-text"/></td>
                                    </tr>
                                </table>
                                <input type="hidden" name="action" value="active_license">
                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Login & Active">
                                </p>
                                <br>
                            </form>
                            <?php
                        } else {
                            $status = $license['status'];
                            $license_key = $license['license_key'];
                            $mask_license_key = '**********' . substr( $license_key, -10 );

                            $lifetime = $license['lifetime'];
                            $expired_at = $license['expired_at'];
                            $expired_at_str = gmdate("Y-m-d\TH:i:s\Z", $expired_at);

                            if ($status == 'A' && !$lifetime && $expired_at < time()) {
                                $status = 'E';
                            }

                            if ($status === 'A') {
                                ?>
                                <div class="piotnetforms-license__description">Email: <?php echo $license['email']; ?><br>License key: <?php echo $mask_license_key; ?><br>Type: <strong><?php echo $license['license_name']; ?></strong><br>Activated sites: <?php echo $license['activated_site_total']; ?><br>Total sites: <?php echo $license['unlimited_site'] ? "Unlimited" : $license['site_total']; ?> sites<br>Expired at: <?php echo $lifetime ? "Lifetime" : $expired_at_str; ?></div>
                                <?php
                            } else if ($status === 'E') {
                                ?>
                                <div class="piotnetforms-license__description">Email: <?php echo $license['email']; ?><br>License key: <?php echo $mask_license_key; ?><br>Status: Your license has <strong>Expired</strong> at <?php echo $expired_at_str;?>.<br>Please renew your license today, to keep getting new updates and use full features.</div>
                                <?php
                            } else if ($status === 'D') {
                                ?>
                                <div class="piotnetforms-license__description">Email: <?php echo $license['email']; ?><br>License key: <?php echo $mask_license_key; ?><br>Status: Your license is <strong>Disabled</strong>.<br>Please change to a valid license, to keep getting new updates and use full features.</div>
                                <?php
                            } else if ($status === 'I') {
                                ?>
                                <div class="piotnetforms-license__description">License key: <?php echo $mask_license_key; ?><br>Status: Your license is <strong>Invalid</strong>.<br>Please change to a valid license, to keep getting new updates and use full features.</div>
                                <?php
                            } else if ($status === 'F') {
                                ?>
                                <div class="piotnetforms-license__description">Email: <?php echo $license['email']; ?><br>License key: <?php echo $mask_license_key; ?><br>Status: Your license is <strong>Full</strong> (<?php echo $license['activated_site_total']; ?> of <?php echo $license['site_total']; ?> sites).<br>Please extend your license or deactivate other site, to keep getting new updates and use full features.</div>
                                <?php
                            } else if ($status === 'L') {
                                ?>
                                <div class="piotnetforms-license__description">Email: <?php echo $license['email']; ?><br>License key: <?php echo $mask_license_key; ?><br>Status: Your license is <strong>Locked</strong> due has changed IP address.<br>Please remove and re-active your license.<br>If it still occurs, please connect to support.</div>
                                <?php
                            } else {
                                ?>
                                <div class="piotnetforms-license__description">License key: <?php echo $mask_license_key; ?><br>Unknown status: <?php echo $license['status']; ?>.<br>Please remove and re-active your license.</div>
                                <?php
                            }
                            ?>
                            <form method="post" action="#">
                                <input type="hidden" name="action" value="remove_license">
                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Remove license">
                                </p>
                                <br>
                            </form>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'Google Sheets Integration', 'piotnetforms' ); ?></h3>
					<iframe width="100%" height="250" src="https://www.youtube.com/embed/NidLGA0k8mI" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-google-sheets-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-google-sheets-group' ); ?>
							<?php
								$redirect      =  get_admin_url(null,'admin.php?page=piotnetforms&connect_type=google_sheet');
								$client_id     = esc_attr( get_option( 'piotnetforms-google-sheets-client-id' ) );
								$client_secret = esc_attr( get_option( 'piotnetforms-google-sheets-client-secret' ) );

                            // $redirect =  get_admin_url(null,'admin.php?page=piotnetforms'); For PAFE
                            // if ( empty( $_GET['connect_type']) && ! empty( $_GET['code'] ) ) {  For PAFE
                            if ( ! empty( $_GET['connect_type'] ) && $_GET['connect_type'] == 'google_sheet' && ! empty( $_GET['code'] ) ) {
                                // Authorization
								$code = $_GET['code'];
								// Token
								$url  = 'https://accounts.google.com/o/oauth2/token';
                                $curl = curl_init();
                                $data = "code=$code&client_id=$client_id&client_secret=$client_secret&redirect_uri=" . urlencode($redirect) . "&grant_type=authorization_code";

                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => "https://accounts.google.com/o/oauth2/token",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_CUSTOMREQUEST => "POST",
                                    CURLOPT_POSTFIELDS => $data,
                                    CURLOPT_SSL_VERIFYPEER => false,
                                    CURLOPT_HTTPHEADER => array(
                                        "Content-Type: application/x-www-form-urlencoded"
                                    ),
                                ));

                                $response = curl_exec($curl);
                                curl_close($curl);
                                //echo $response;
                                $array = json_decode( $response );

                                if ( ! empty( $array->access_token ) && ! empty( $array->refresh_token ) && ! empty( $array->expires_in ) ) {
                                    $piotnetforms_ggsheets_expired_at = time() + $array->expires_in;
                                    update_option( 'piotnetforms-google-sheets-exprires', $array->expires_in );
                                    update_option( 'piotnetforms-google-sheets-expired-token', $piotnetforms_ggsheets_expired_at );
									update_option( 'piotnetforms-google-sheets-access-token', $array->access_token );
									update_option( 'piotnetforms-google-sheets-refresh-token', $array->refresh_token );
								}
							}
							?>
							<div style="padding-top: 30px;">
								<b><a href="https://console.developers.google.com/flows/enableapi?apiid=sheets.googleapis.com" target="_blank"><?php esc_html_e( 'Click here to Sign into your Gmail account and access Google Sheets’s application registration', 'piotnetforms' ); ?></a></b>
							</div>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Client ID', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-google-sheets-client-id" value="<?php echo $client_id; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Client Secret', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-google-sheets-client-secret" value="<?php echo $client_secret; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Authorized redirect URI', 'piotnetforms' ); ?></th>
								<td><input type="text" readonly="readonly" value="<?php echo $redirect; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Authorization', 'piotnetforms' ); ?></th>
								<td>
									<?php if ( ! empty( $client_id ) && ! empty( $client_secret ) ) : ?>
										<a class="piotnetforms-toggle-features__button" href="https://accounts.google.com/o/oauth2/auth?redirect_uri=<?php echo urlencode( $redirect ); ?>&client_id=<?php echo $client_id; ?>&response_type=code&scope=https://www.googleapis.com/auth/spreadsheets&approval_prompt=force&access_type=offline">Authorization</a>
									<?php else : ?>
										<?php esc_html_e( 'To setup Gmail integration properly you should save Client ID and Client Secret.', 'piotnetforms' ); ?>
									<?php endif; ?>
								</td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

            <!--Google Calendar-->
            <hr>
            <div class="piotnetforms-bottom">
                <div class="piotnetforms-bottom__left">
                    <h3><?php esc_html_e( 'Google Calendar Integration', 'piotnetforms' ); ?></h3>
                </div>
                <div class="piotnetforms-bottom__right">
                    <div class="piotnetforms-license">
                        <form method="post" action="options.php">
                            <?php settings_fields( 'piotnetforms-google-calendar-group' ); ?>
                            <?php do_settings_sections( 'piotnetforms-google-calendar-group' ); ?>
                            <?php
                            $redirect      =  get_admin_url(null,'admin.php?page=piotnetforms&connect_type=google_calendar');
                            $gg_cld_client_id     = esc_attr( get_option( 'piotnetforms-google-calendar-client-id' ) );
                            $gg_cld_client_secret = esc_attr( get_option( 'piotnetforms-google-calendar-client-secret' ) );
                            $client_api_key = esc_attr( get_option( 'piotnetforms-google-calendar-client-api-key' ) );

                            if ( ! empty( $_GET['connect_type'] ) && $_GET['connect_type'] == 'google_calendar' && ! empty( $_GET['code'] ) ) {

                                // Authorization
                                $code = $_GET['code'];

                                // Token
	                            $curl = curl_init();
	                            $data = "code=$code&client_id=$gg_cld_client_id&client_secret=$gg_cld_client_secret&redirect_uri=" . urlencode($redirect) . "&grant_type=authorization_code";

	                            curl_setopt_array($curl, array(
		                            CURLOPT_URL => "https://accounts.google.com/o/oauth2/token",
		                            CURLOPT_RETURNTRANSFER => true,
		                            CURLOPT_TIMEOUT => 30,
		                            CURLOPT_CUSTOMREQUEST => "POST",
		                            CURLOPT_POSTFIELDS => $data,
		                            CURLOPT_SSL_VERIFYPEER => false,
		                            CURLOPT_HTTPHEADER => array(
			                            "Content-Type: application/x-www-form-urlencoded"
		                            ),
	                            ));

	                            $response = curl_exec($curl);
	                            curl_close($curl);
                                //echo $response;
                                $array = json_decode( $response );

                                if ( ! empty( $array->access_token ) && ! empty( $array->refresh_token ) && ! empty( $array->expires_in ) ) {
	                                $piotnetforms_ggcalendar_expired_at = time() + $array->expires_in;
	                                update_option( 'piotnetforms-google-calendar-exprires', $array->expires_in );
	                                update_option( 'piotnetforms-google-calendar-expired-token', $piotnetforms_ggcalendar_expired_at );
	                                update_option( 'piotnetforms-google-calendar-access-token', $array->access_token );
	                                update_option( 'piotnetforms-google-calendar-refresh-token', $array->refresh_token );

                                    function piotnetforms_google_calendar_get_calendar_id($access_token, $client_api_key) {
                                        $curl = curl_init();

                                        curl_setopt_array( $curl, array(
                                            CURLOPT_URL            => "https://www.googleapis.com/calendar/v3/users/me/calendarList?minAccessRole=writer&key=$client_api_key",
                                            CURLOPT_RETURNTRANSFER => true,
                                            CURLOPT_TIMEOUT        => 30,
                                            CURLOPT_CUSTOMREQUEST  => "GET",
                                            CURLOPT_SSL_VERIFYPEER => false,
                                            CURLOPT_HTTPHEADER     => array(
                                                "Authorization: Bearer $access_token",
                                                "Accept: application/json"
                                            ),
                                        ));

                                        $response = curl_exec( $curl );
                                        curl_close( $curl );

                                        $response = json_decode($response);
                                        //print_r($response);
                                        $gg_calendar_items = $response->items;
                                        $gg_calendar_id = null;
                                        foreach ( $gg_calendar_items as $gg_calendar_item ) {
                                            $gg_calendar_item_id = $gg_calendar_item->id;
                                            if (empty($gg_calendar_id)) {
                                                $gg_calendar_id = $gg_calendar_item_id;
                                            }
                                            if ( !empty($gg_calendar_item->primary) && $gg_calendar_item->primary == 1 ) {
                                                $gg_calendar_id = $gg_calendar_item_id;
                                                break;
                                            }
                                        }
                                        return $gg_calendar_id;
                                    }

                                    $gg_calendar_id = piotnetforms_google_calendar_get_calendar_id($array->access_token, $client_api_key);
	                                update_option('piotnetforms-google-calendar-id', $gg_calendar_id);
                                }
                            }
                            ?>
                            <div style="padding-top: 30px;">
                                <b><a href="https://console.developers.google.com/" target="_blank"><?php esc_html_e( 'Click here to Sign into your Gmail account and access Google Calendar’s application registration', 'piotnetforms' ); ?></a></b>
                            </div>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Client ID', 'piotnetforms' ); ?></th>
                                    <td><input type="text" name="piotnetforms-google-calendar-client-id" value="<?php echo $gg_cld_client_id; ?>" class="regular-text"/></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Client Secret', 'piotnetforms' ); ?></th>
                                    <td><input type="text" name="piotnetforms-google-calendar-client-secret" value="<?php echo $gg_cld_client_secret; ?>" class="regular-text"/></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'API Key', 'piotnetforms' ); ?></th>
                                    <td><input type="text" name="piotnetforms-google-calendar-client-api-key" value="<?php echo $client_api_key; ?>" class="regular-text"/></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Authorized redirect URI', 'piotnetforms' ); ?></th>
                                    <td><input type="text" readonly="readonly" value="<?php echo $redirect; ?>" class="regular-text"/></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Authorization', 'piotnetforms' ); ?></th>
                                    <td>
                                        <?php if ( ! empty( $gg_cld_client_id ) && ! empty( $gg_cld_client_secret ) ) : ?>
                                            <a class="piotnetforms-toggle-features__button" href="https://accounts.google.com/o/oauth2/auth?redirect_uri=<?php echo urlencode($redirect); ?>&client_id=<?php echo $gg_cld_client_id; ?>&response_type=code&scope=https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events&approval_prompt=force&access_type=offline">Authorization</a>
                                        <?php else : ?>
                                            <?php esc_html_e( 'To setup Gmail integration properly you should save Client ID and Client Secret.', 'piotnetforms' ); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            <?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
                        </form>
                    </div>
                </div>
            </div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'Google Maps Integration', 'piotnetforms' ); ?></h3>
					<iframe width="100%" height="250" src="https://www.youtube.com/embed/_YhQWreCZwA" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-google-maps-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-google-maps-group' ); ?>
							<?php
								$google_maps_api_key = esc_attr( get_option( 'piotnetforms-google-maps-api-key' ) );
							?>
							<div style="padding-top: 30px;">
								<b><a href="https://cloud.google.com/maps-platform/?apis=maps,places" target="_blank"><?php esc_html_e( 'Click here to get Google Maps API Key', 'piotnetforms' ); ?></a></b>
							</div>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Google Maps API Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-google-maps-api-key" value="<?php echo $google_maps_api_key; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>
			<br>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'Stripe Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-stripe-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-stripe-group' ); ?>
							<?php
								$publishable_key = esc_attr( get_option( 'piotnetforms-stripe-publishable-key' ) );
								$secret_key      = esc_attr( get_option( 'piotnetforms-stripe-secret-key' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Publishable Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-stripe-publishable-key" value="<?php echo $publishable_key; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Secret Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-stripe-secret-key" value="<?php echo $secret_key; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php _e('Paypal Integration','piotnetforms'); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
						    <?php settings_fields( 'piotnetforms-paypal-group' ); ?>
						    <?php do_settings_sections( 'piotnetforms-paypal-group' ); ?>
						    <?php
						    	$client_id = esc_attr( get_option('piotnetforms-paypal-client-id') );
						    ?>
						    <table class="form-table">
						    	<div style="padding-top: 30px;">
							    	<b><a href="https://developer.paypal.com/developer/applications/" target="_blank"><?php _e('Click here to Create app and get the Client ID','piotnetforms'); ?></a></b>
							    </div>
						        <tr valign="top">
						        <th scope="row"><?php _e('Client ID','piotnetforms'); ?></th>
						        <td><input type="text" name="piotnetforms-paypal-client-id" value="<?php echo $client_id; ?>" class="regular-text"/></td>
						        </tr>
						    </table>
						    <?php submit_button(__('Save Settings','piotnetforms')); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'MailChimp Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-mailchimp-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-mailchimp-group' ); ?>
							<?php
								$api_key = esc_attr( get_option( 'piotnetforms-mailchimp-api-key' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'API Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-mailchimp-api-key" value="<?php echo $api_key; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'MailerLite Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-mailerlite-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-mailerlite-group' ); ?>
							<?php
								$api_key = esc_attr( get_option( 'piotnetforms-mailerlite-api-key' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'API Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-mailerlite-api-key" value="<?php echo $api_key; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'ActiveCampaign Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-activecampaign-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-activecampaign-group' ); ?>
							<?php
								$api_key = esc_attr( get_option( 'piotnetforms-activecampaign-api-key' ) );
								$api_url = esc_attr( get_option( 'piotnetforms-activecampaign-api-url' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'API Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-activecampaign-api-key" value="<?php echo $api_key; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'API URL', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-activecampaign-api-url" value="<?php echo $api_url; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'reCAPTCHA (v3) Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-recaptcha-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-recaptcha-group' ); ?>
							<?php
								$site_key   = esc_attr( get_option( 'piotnetforms-recaptcha-site-key' ) );
								$secret_key = esc_attr( get_option( 'piotnetforms-recaptcha-secret-key' ) );
							?>
							<div style="padding-top: 30px;" data-piotnetforms-dropdown>
								<b><a href="#" data-piotnetforms-dropdown-trigger><?php esc_html_e( 'Click here to view tutorial', 'piotnetforms' ); ?></a></b>
								<div data-piotnetforms-dropdown-content>
									<p>Very first thing you need to do is register your website on Google reCAPTCHA to do that click <a href="https://www.google.com/recaptcha/admin" target="_blank">here</a>.</p>

									<p>Login to your Google account and create the app by filling the form. Select the reCAPTCHA v3 and in that select “I am not a robot” checkbox option.</p>
									<div>
									<img src="<?php echo plugin_dir_url( __FILE__ ); ?>../forms/google-recaptcha-1.jpg">
									</div>

									<p>Once submitted, Google will provide you with the following two information: Site key, Secret key.</p>
									<div>
									<img src="<?php echo plugin_dir_url( __FILE__ ); ?>../forms/google-recaptcha-2.jpg">
									</div>
								</div>
							</div>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Site Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-recaptcha-site-key" value="<?php echo $site_key; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Secret Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-recaptcha-secret-key" value="<?php echo $secret_key; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>
			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'Getresponse Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-getresponse-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-getresponse-group' ); ?>
							<?php
								$getresponse_api_key   = esc_attr( get_option( 'piotnetforms-getresponse-api-key' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'API Key', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-getresponse-api-key" value="<?php echo $getresponse_api_key; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'Twilio Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-twilio-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-twilio-group' ); ?>
							<?php
								$account_sid = esc_attr( get_option( 'piotnetforms-twilio-account-sid' ) );
								$author_token = esc_attr( get_option( 'piotnetforms-twilio-author-token' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Account SID', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-twilio-account-sid" value="<?php echo $account_sid; ?>" class="regular-text"/></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Author Token', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-twilio-author-token" value="<?php echo $author_token; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>

			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php esc_html_e( 'SendFox Integration', 'piotnetforms' ); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
							<?php settings_fields( 'piotnetforms-sendfox-group' ); ?>
							<?php do_settings_sections( 'piotnetforms-sendfox-group' ); ?>
							<?php
								$sendfox_access_token   = esc_attr( get_option( 'piotnetforms-sendfox-access-token' ) );
							?>
							<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'SendFox Personal Aceess Token', 'piotnetforms' ); ?></th>
								<td><input type="text" name="piotnetforms-sendfox-access-token" value="<?php echo $sendfox_access_token; ?>" class="regular-text"/></td>
								</tr>
							</table>
							<?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
						</form>
					</div>
				</div>
			</div>
			
			<hr>
			<div class="piotnetforms-bottom">
				<div class="piotnetforms-bottom__left">
					<h3><?php _e('Zoho Integration','piotnetforms'); ?></h3>
				</div>
				<div class="piotnetforms-bottom__right">
					<div class="piotnetforms-license">
						<form method="post" action="options.php">
						    <?php settings_fields( 'piotnetforms-zoho-group' ); ?>
						    <?php do_settings_sections( 'piotnetforms-zoho-group' ); ?>
						    <?php
								$zoho_domain = esc_attr( get_option('piotnetforms-zoho-domain') );
								$client_id = esc_attr( get_option('piotnetforms-zoho-client-id') );
								$redirect_url = admin_url('admin.php?page=piotnetforms');
								$client_secret = esc_attr( get_option('piotnetforms-zoho-client-secret') );
								$token = esc_attr( get_option('piotnetforms-zoho-token') );
								$refresh_token = esc_attr( get_option('piotnetforms-zoho-refresh-token') );
								$zoho_domains = ["accounts.zoho.com", "accounts.zoho.com.au", "accounts.zoho.eu", "accounts.zoho.in", "accounts.zoho.com.cn"]
						    ?>
						    <table class="form-table">
							<tr valign="top">
						        <th scope="row"><?php _e('Domain','piotnetforms'); ?></th>
						        <td>
									<select name="piotnetforms-zoho-domain">
										<?php foreach($zoho_domains as $zoho){
												if($zoho_domain == $zoho){
													echo '<option value="'.$zoho.'" selected>'.$zoho.'</option>';
												}else{
													echo '<option value="'.$zoho.'">'.$zoho.'</option>';
												}
											}
										?>
									</select>
								</td>
						        </tr>
						        <tr valign="top">
						        <th scope="row"><?php _e('Client ID','piotnetforms'); ?></th>
						        <td>
									<input type="text" name="piotnetforms-zoho-client-id" value="<?php echo $client_id; ?>" class="regular-text"/>
									<a target="_blank" href="https://accounts.zoho.com/developerconsole">How to create client id and Screct key</a>
								</td>
						        </tr>
						        <tr valign="top">
						        <th scope="row"><?php _e('Client Secret','piotnetforms'); ?></th>
						        <td><input type="text" name="piotnetforms-zoho-client-secret" value="<?php echo $client_secret; ?>" class="regular-text"/></td>
						        </tr>
								<tr valign="top">
						        <th scope="row"><?php _e('Authorization Redirect URI','piotnetforms'); ?></th>
						        <td><input type="text" name="piotnetforms-zoho-redirect-url" value="<?php echo $redirect_url; ?>" class="regular-text" readonly/></td>
						        </tr>
						    </table>
							<div class="piotnetforms-zoho-admin-api">
						    <?php submit_button(__('Save Settings','piotnetforms')); ?>
							<?php
								$scope_module = 'ZohoCRM.modules.all,ZohoCRM.settings.all';
								$oauth = 'https://'.$zoho_domain.'/oauth/v2/auth?scope='.$scope_module.'&client_id='.$client_id.'&response_type=code&access_type=offline&redirect_uri='.$redirect_url.'';
								echo '<p class="piotnetforms-zoho-admin-api-authenticate submit"><a class="button button-primary" href="'.$oauth.'" authenticate-zoho-crm disabled>Authenticate Zoho CRM</a></p>';
							?>
							<?php if(!empty($_REQUEST['code']) && !empty($_REQUEST['accounts-server'])):
								$url_get_token = 'https://'.$zoho_domain.'/oauth/v2/token?client_id='.$client_id.'&grant_type=authorization_code&client_secret='.$client_secret.'&redirect_uri='.$redirect_url.'&code='.$_REQUEST['code'].'';
								$zoho_response = wp_remote_post($url_get_token, array());
								if(!empty($zoho_response['body'])){
									$zoho_response = json_decode($zoho_response['body']);
									if(empty($zoho_response->error)){
										update_option('piotnetforms_zoho_access_token', $zoho_response->access_token);
										update_option('piotnetforms_zoho_refresh_token', $zoho_response->refresh_token);
										update_option('piotnetforms_zoho_api_domain', $zoho_response->api_domain);
										echo "Success";
									}else{
										echo $zoho_response->error;
									}
								}else{
									echo "Cannot verify zoho account";
								}
							?>
							</div>
							<?php endif; ?>
						</form>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
<?php } ?>
