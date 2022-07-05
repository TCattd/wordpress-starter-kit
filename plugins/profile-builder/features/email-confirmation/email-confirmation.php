<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//functions needed for the email-confirmation on single-sites (to create the "_signups" table)
function wppb_signup_schema( $oldVal, $newVal ){
	// Declare these as global in case schema.php is included from a function.
	global $wpdb, $wp_queries, $charset_collate;

	if ($newVal['emailConfirmation'] == 'yes'){

		//The database character collate.
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET ".$wpdb->charset;
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE ".$wpdb->collate;
		$tableName = $wpdb->prefix.'signups';

		$sql = "
			CREATE TABLE $tableName (
				domain varchar(191) NOT NULL default '',
				path varchar(100) NOT NULL default '',
				title longtext NOT NULL,
				user_login varchar(60) NOT NULL default '',
				user_email varchar(100) NOT NULL default '',
				registered datetime NOT NULL default '0000-00-00 00:00:00',
				activated datetime NOT NULL default '0000-00-00 00:00:00',
				active tinyint(1) NOT NULL default '0',
				activation_key varchar(50) NOT NULL default '',
				meta longtext,
				KEY activation_key (activation_key),
				KEY domain (domain)
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$res = dbDelta($sql);
	}
}
add_action( 'update_option_wppb_general_settings', 'wppb_signup_schema', 10, 2 );


//function to add new tab in the default WP userlisting with all the users who didn't confirm their account yet
function wppb_add_pending_users_header_script(){
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery.post( ajaxurl ,  { action:"wppb_get_unconfirmed_email_number"}, function(response) {
				jQuery('.wrap ul.subsubsub').append('<span id="separatorID"> |</span> <li class="listUsersWithUncofirmedEmail"><a class="unconfirmedEmailUsers" href="?page=unconfirmed_emails"><?php esc_html_e('Users with Unconfirmed Email Address', 'profile-builder');?></a> <font id="unconfirmedEmailNo" color="grey">('+response.number+')</font></li>');
			});
		});

		function confirmECActionBulk( URL, message ) {
			if ( confirm(message) )
				window.location=URL;
		}

		// script to create a confirmation box for the user upon approving/unapproving a user
		function confirmECAction( URL, todo, user_email, actionText ) {
			actionText = '<?php esc_html_e( 'Do you want to', 'profile-builder' ); ?>' + ' ' + actionText;

			if (confirm(actionText)) {
				jQuery.post( ajaxurl ,  { action:"wppb_handle_email_confirmation_cases", URL:URL, todo:todo, user_email:user_email, nonce:"<?php echo esc_js( wp_create_nonce( 'wppb_handle_email_confirmation' ) ) ?>" }, function(response) {
					if (response.trim() == 'ok')
						window.location=URL;

					else
						alert( response.trim() );
				});
			}
		}
	</script>
<?php
}

function wppb_get_unconfirmed_email_number(){
	global $wpdb;

    /* since version 2.0.7 for multisite we add a 'registered_for_blog_id' meta in the registration process
    so we can count only the users registered on that blog. Also for backwards compatibility we display the users that don't have that meta at all */
    $number_of_users = 0;
    $results = $wpdb->get_results("SELECT * FROM ".$wpdb->base_prefix."signups WHERE active = 0");
    foreach ($results as $result){
        if( !empty(  $result->meta ) ){
            $user_meta = maybe_unserialize( $result->meta );
            if( empty( $user_meta['registered_for_blog_id'] ) || $user_meta['registered_for_blog_id'] == get_current_blog_id() ){
                $number_of_users++;
            }
        }
    }

    header( 'Content-type: application/json' );
	die( json_encode( array( 'number' => $number_of_users ) ) );
}


function wppb_handle_email_confirmation_cases() {
	global $wpdb;

	if( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wppb_handle_email_confirmation' ) )
        die( esc_html__("You either don't have permission for that action or there was an error!", "profile-builder") );

	$todo = isset( $_POST['todo'] ) ? sanitize_text_field( $_POST['todo'] ) : '';
	$user_email = isset( $_POST['user_email'] ) ? sanitize_email($_POST['user_email']) : '';

	if ( current_user_can( apply_filters( 'wppb_email_confirmation_user_capability', 'manage_options' ) ) )
		if ( ( $todo != '' ) && ( $user_email != '' ) ){

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->base_prefix . "signups WHERE active = 0 AND user_email = %s", $user_email ) );

			if ( count( $results ) != 1 )
				die( esc_html__( "There was an error performing that action!", "profile-builder" ) );

			elseif ( $todo == 'delete' ){
				$sql_result = $wpdb->delete( $wpdb->base_prefix.'signups', array( 'user_login' => $results[0]->user_login, 'user_email' => $results[0]->user_email ) );
				if ( $sql_result )
					die( 'ok' );

				else
					die( esc_html__( "The selected user couldn't be deleted", "profile-builder" ) );

			}elseif ( $todo == 'confirm' ){
				die( wppb_manual_activate_signup( $results[0]->activation_key ) );/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ /* escaped inside the function */

			}elseif ( $todo == 'resend' ){
				$sql_result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_login = %s AND user_email = %s", $results[0]->user_login, $results[0]->user_email ), ARRAY_A );

				if ( $sql_result ){
					wppb_signup_user_notification( trim( $sql_result['user_login'] ), trim( $sql_result['user_email'] ), $sql_result['activation_key'], $sql_result['meta'] );

					die( esc_html__( "Email notification resent to user", "profile-builder" ) );
				}

			}
		}

	die( esc_html__("You either don't have permission for that action or there was an error!", "profile-builder") );
}



// FUNCTIONS USED BOTH ON THE REGISTRATION PAGE AND THE EMAIL CONFIRMATION TABLE

// Hook to add AP user meta after signup autentification
add_action( 'wpmu_activate_user', 'wppb_add_meta_to_user_on_activation', 10, 3 );

//function that adds AP user meta after signup autentification and it is also used when editing the user uprofile
function wppb_add_meta_to_user_on_activation( $user_id, $password, $meta ){
	$user = new WP_User( $user_id );

	//copy the data from the meta field (default fields)
	if( !empty( $meta['first_name'] ) )
		update_user_meta( $user_id, 'first_name', $meta['first_name'] );
	if( !empty( $meta['last_name'] ) )
		update_user_meta( $user_id, 'last_name', $meta['last_name'] );
	if( !empty( $meta['nickname'] ) )
		update_user_meta( $user_id, 'nickname', $meta['nickname'] );
	if( !empty( $meta['user_url'] ) )
		wp_update_user(array('ID' => $user_id, 'user_url' => $meta['user_url']));
	if( !empty( $meta['aim'] ) )
		update_user_meta( $user_id, 'aim', $meta['aim'] );
	if( !empty( $meta['yim'] ) )
		update_user_meta( $user_id, 'yim', $meta['yim'] );
	if( !empty( $meta['jabber'] ) )
		update_user_meta( $user_id, 'jabber', $meta['jabber'] );
	if( !empty( $meta['description'] ) )
		update_user_meta( $user_id, 'description', $meta['description'] );

	if( !empty( $meta['role'] ) )
		$user->set_role( $meta['role'] ); //update the users role (s)he registered for

    if( !empty( $meta['first_name'] ) && !empty( $meta['last_name'] ) )
        wp_update_user(array('ID' => $user_id, 'display_name' => $meta['first_name'].' '.$meta['last_name'] ));
    else if( !empty( $meta['nickname'] ) )
        wp_update_user(array('ID' => $user_id, 'display_name' => $meta['nickname'] ));

	//copy the data from the meta fields (custom fields)
	$manage_fields = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields', 'not_set' ), array( 'meta' => $meta, 'user_id' => $user_id, 'context' => 'user_activation' ) );
	if ( $manage_fields != 'not_set' ){
		foreach ( $manage_fields as $key => $value){
			switch ( $value['field'] ) {
				case 'Input':
				case 'Input (Hidden)':
				case 'Checkbox':
				case 'Checkbox (Terms and Conditions)':
				case 'Radio':
				case 'Select':
				case 'Select (Country)':
				case 'Select (Multiple)':
				case 'Select (Timezone)':
				case 'Datepicker':
				case 'Textarea':{
					if ( isset( $meta[$value['meta-name']] ) )
						update_user_meta( $user_id, $value['meta-name'], trim( $meta[$value['meta-name']] ) );
					break;
				}
				case 'Timepicker':{
					if ( isset( $meta[$value['meta-name']] ) ) {
						$time = $meta[$value['meta-name']];
						if( !empty( $time['hours'] ) && !empty( $time['minutes'] ) ) {
							update_user_meta( $user_id, $value['meta-name'], $time['hours'] . ':' . $time['minutes'] );
						}

					}
					break;
				}
				case 'Map':{
					if ( isset( $meta[$value['meta-name']] ) ) {
						// Add new markers
						if( is_array( $meta[$value['meta-name']] ) ) {
							foreach( $meta[$value['meta-name']] as $key => $position )
								update_user_meta( $user_id, $value['meta-name'] . '_' . $key, $position );
						}
					}
					break;
				}
				case 'Upload':{
					if ( isset( $meta[$value['meta-name']] ) ){
                        if( !empty( $meta[$value['meta-name']] ) ) {
                            if (is_numeric($meta[$value['meta-name']])) {
                                update_user_meta($user_id, $value['meta-name'], trim($meta[$value['meta-name']]));

                                // use this to update the post author to the correct user
                                wp_update_post( array(
                                    'ID'            => trim( $meta[$value['meta-name']] ),
                                    'post_author'   => $user_id
                                ) );
                            } else {
                                $wp_upload_array = wp_upload_dir(); // Array of key => value pairs

                                $file = trim($meta[$value['meta-name']]);
                                $file_name = substr($file, strpos($file, '_attachment_') + 12);

                                $random_user_number = apply_filters('wppb_register_wpmu_upload_random_user_number2', substr(md5($user->data->user_email), 0, 12), $user, $meta);

                                $old_path_on_disk = $wp_upload_array['basedir'] . '/profile_builder/attachments/' . substr($file, strpos($file, 'wpmuRandomID_'));
                                if (PHP_OS == "WIN32" || PHP_OS == "WINNT")
                                    $old_path_on_disk = str_replace('\\', '/', $old_path_on_disk);

                                $new_path_on_disk = $wp_upload_array['basedir'] . '/profile_builder/attachments/userID_' . $user_id . '_attachment_' . $file_name;
                                if (PHP_OS == "WIN32" || PHP_OS == "WINNT")
                                    $new_path_on_disk = str_replace('\\', '/', $new_path_on_disk);

                                if (rename($old_path_on_disk, $new_path_on_disk))
                                    update_user_meta($user_id, $value['meta-name'], $wp_upload_array['baseurl'] . '/profile_builder/attachments/userID_' . $user_id . '_attachment_' . $file_name);
                            }
                        }
                        else{
                            update_user_meta( $user_id, $value['meta-name'], '' );
                        }
					}
					break;
				}
				case 'Avatar':{
					if ( isset( $meta[$value['meta-name']] ) ) {
                        if( !empty( $meta[$value['meta-name']] ) ) {
                            if (is_numeric($meta[$value['meta-name']])) {
                                update_user_meta($user_id, $value['meta-name'], trim($meta[$value['meta-name']]));

                                // use this to update the post author to the correct user
                                wp_update_post( array(
                                    'ID'            => trim( $meta[$value['meta-name']] ),
                                    'post_author'   => $user_id
                                ) );
                            } else {
                                $wp_upload_array = wp_upload_dir(); // Array of key => value pairs

                                $file = trim($meta[$value['meta-name']]);
                                $file_name = substr($file, strpos($file, '_originalAvatar_') + 16);

                                $random_user_number = apply_filters('wppb_register_wpmu_avatar_random_user_number2', substr(md5($user->data->user_email), 0, 12), $user, $meta);

                                $old_path_on_disk = $wp_upload_array['basedir'] . '/profile_builder/avatars/' . substr($file, strpos($file, 'wpmuRandomID_'));
                                if (PHP_OS == "WIN32" || PHP_OS == "WINNT")
                                    $old_path_on_disk = str_replace('\\', '/', $old_path_on_disk);

                                $new_path_on_disk = $wp_upload_array['basedir'] . '/profile_builder/avatars/userID_' . $user_id . '_originalAvatar_' . $file_name;
                                if (PHP_OS == "WIN32" || PHP_OS == "WINNT")
                                    $new_path_on_disk = str_replace('\\', '/', $new_path_on_disk);

                                if (rename($old_path_on_disk, $new_path_on_disk)) {
                                    $wp_filetype = wp_check_filetype(basename($file_name), null);
                                    $attachment = array('post_mime_type' => $wp_filetype['type'],
                                        'post_title' => $file_name,
                                        'post_content' => '',
                                        'post_status' => 'inherit'
                                    );

                                    $attach_id = wp_insert_attachment($attachment, $new_path_on_disk);

                                    $avatar = image_downsize($attach_id, 'thumbnail');

                                    update_user_meta($user_id, $value['meta-name'], $avatar[0]);
                                    update_user_meta($user_id, 'avatar_directory_path_' . $value['id'], $new_path_on_disk);
                                    update_user_meta($user_id, 'resized_avatar_' . $value['id'] . '_relative_path', $new_path_on_disk);
                                    wppb_resize_avatar($user_id);
                                }
                            }
                        }
                        else{
                            update_user_meta( $user_id, $value['meta-name'], '' );
                        }
                        break;
                    }
				}
				case 'Default - Blog Details':{
					if ( is_multisite() && isset( $meta['wppb_create_new_site_checkbox'] ) && $meta['wppb_create_new_site_checkbox'] == 'yes' ) {
						$blog_url = $meta['wppb_blog_url'];
						$blog_title = $meta['wppb_blog_title'];

						$privacy['public'] = ( isset( $meta['wppb_blog_privacy'] ) && 'Yes' == $meta['wppb_blog_privacy'] ) ? true : false;
						$blog_details = wpmu_validate_blog_signup( $blog_url, $blog_title );
						if ( empty($blog_details['errors']->errors['blogname']) && empty($blog_details['errors']->errors['blog_title'])) {
							wpmu_create_blog( $blog_details['domain'], $blog_details['path'], $blog_details['blog_title'], $user_id, $privacy );
						}
					}
				}
				case 'Select2 (Multiple)':{

                    $selected_values = '';
                    if (!empty($meta[wppb_handle_meta_name($value['meta-name'])]) && is_array($meta[wppb_handle_meta_name($value['meta-name'])])) {
                        foreach ($meta[wppb_handle_meta_name($value['meta-name'])] as $selected_key => $selected_value)
                            $selected_values .= sanitize_text_field($selected_value) . ',';
                    }

                    update_user_meta( $user_id, $value['meta-name'], trim( $selected_values, ',' ) );
                }
                default: {
					if ( isset( $meta[$value['meta-name']] ) ) {
						update_user_meta($user_id, $value['meta-name'], $meta[$value['meta-name']]);
					}
					do_action('wppb_add_meta_on_user_activation_' . Wordpress_Creation_Kit_PB::wck_generate_slug($value['field']), $user_id, $password, $meta, $value);
				}
			}
		}
	}
    do_action( 'wppb_add_other_meta_on_user_activation', $user_id, $meta );
}


// function to add the new user to the signup table if email confirmation is selected as active or it is a wpmu installation
function wppb_signup_user( $username, $user_email, $login_after_register, $meta = '' ) {
	global $wpdb;

    // check for automatic login
    // using case-insensitive string comparison to allow for both 'Yes' and 'yes'
    if( strcasecmp($login_after_register, 'Yes') === 0 ) {
        $login_after_register = true;
    } else {
        $login_after_register = false;
    }
    $meta [ 'wppb_login_after_register_'.$meta['user_login'] ] = $login_after_register;

	// Format data
	$user = sanitize_user( $username, true );
    if( is_multisite() )
	    $user = preg_replace( '/\s+/', '', $user );

	$user_email = sanitize_email( $user_email );
	$activation_key = substr( md5( time() . rand() . $user_email ), 0, 16 );
    $meta = serialize( $meta );

	// change User Registered date and time according to timezone selected in WordPress settings
	$wppb_get_date = wppb_get_register_date();

	if( isset( $wppb_get_date ) ) {
		$wppb_user_registered = $wppb_get_date;
	} else {
		$wppb_user_registered = current_time( 'mysql', true );
	}

	if ( is_multisite() )
		$wpdb->insert( $wpdb->signups, array('domain' => '', 'path' => '', 'title' => '', 'user_login' => $user, 'user_email' => $user_email, 'registered' => $wppb_user_registered, 'activation_key' => $activation_key, 'meta' => $meta ) );
	else
		$wpdb->insert( $wpdb->prefix.'signups', array('domain' => '', 'path' => '', 'title' => '', 'user_login' => $user, 'user_email' => $user_email, 'registered' => $wppb_user_registered, 'activation_key' => $activation_key, 'meta' => $meta ) );

	do_action ( 'wppb_signup_user', $username, $user_email, $activation_key, $meta );

	wppb_signup_user_notification( $username, $user_email, $activation_key, $meta );
}

/**
 * Notify user of signup success.
 *
 * Filter 'wppb_signup_user_notification_filter' to bypass this function or
 * replace it with your own notification behavior.
 *
 * Filter 'wppb_signup_user_notification_email' and
 * 'wppb_signup_user_notification_subject' to change the content
 * and subject line of the email sent to newly registered users.
 *
 * @param string $user The user's login name.
 * @param string $user_email The user's email address.
 * @param array $meta By default, an empty array.
 * @param string $activation_key The activation key created in wppb_signup_user()
 * @return bool
 */
function wppb_signup_user_notification( $user, $user_email, $activation_key, $meta = '' ) {
	if ( !apply_filters( 'wppb_signup_user_notification_filter', $user, $user_email, $activation_key, $meta ) )
		return false;

	$wppb_general_settings = get_option( 'wppb_general_settings' );
	$admin_email = get_site_option( 'admin_email' );

	$from_name = apply_filters ( 'wppb_signup_user_notification_email_from_field', get_bloginfo( 'name' ) );

	//we don't use this anymore do we ?
	/*$message_headers = apply_filters ( 'wppb_signup_user_notification_from', "From: \"{$from_name}\" <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n" );*/

	if( isset( $wppb_general_settings['activationLandingPage'] ) && ( trim( $wppb_general_settings['activationLandingPage'] ) != '' ) ) {
		$registration_page_url = add_query_arg( array('activation_key' => $activation_key), get_permalink( $wppb_general_settings['activationLandingPage'] ) );
	} else {
		$registration_page_url = 'not_set';
	}

	$registration_page_url = apply_filters( 'wppb_ec_landing_page', $registration_page_url, $user, $activation_key, $meta );

	if ( $registration_page_url == 'not_set' ){
		global $post;
        if( !empty( $post->ID ) )
            $permalink = get_permalink( $post->ID );
        else
            $permalink =  get_bloginfo( 'url' );

        if( !empty( $post->post_content ) )
            $post_content = $post->post_content;
        else
            $post_content = '';

		$registration_page_url = ( ( strpos( $post_content, '[wppb-register' ) !== false ) ? add_query_arg( array( 'activation_key' => $activation_key ), $permalink ) : add_query_arg( array( 'activation_key' => $activation_key ), get_bloginfo( 'url' ) ) );
	}

    $wppb_generalSettings = get_option( 'wppb_general_settings' );

    if( !empty( $wppb_generalSettings['loginWith'] ) && $wppb_generalSettings['loginWith'] == 'email' ) {
        $display_username_email = $user_email;
    }
    else {
        $display_username_email = $user;
    }

	$subject = sprintf( __( '[%1$s] Activate %2$s', 'profile-builder'), $from_name, $display_username_email );
	$subject = apply_filters( 'wppb_signup_user_notification_email_subject', $subject, $user_email, $user, $activation_key, $registration_page_url, $meta, $from_name, 'wppb_user_emailc_registr_w_email_confirm_email_subject' );

	$message = sprintf( __( "To activate your user, please click the following link:<br><br>%s%s%s<br><br>After you activate it you will receive yet *another email* with your login.", "profile-builder" ), '<a href="'.$registration_page_url.'">', $registration_page_url, '</a>' );
    $message = apply_filters( 'wppb_signup_user_notification_email_content', $message, $user_email, $user, $activation_key, $registration_page_url, $meta, $from_name, 'wppb_user_emailc_registr_w_email_confirm_email_content' );

	$message_context = 'email_user_activate';

	wppb_mail( $user_email, $subject, $message, $from_name, $message_context );

	return true;
}


/**
 * Activate a signup.
 *
 *
 * @param string $activation_key The activation key provided to the user.
 * @return array An array containing information about the activated user and/or blog
 */
function wppb_manual_activate_signup( $activation_key ) {
	global $wpdb;

	if ( is_multisite() )
		$signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE activation_key = %s", $activation_key ) );
	else
		$signup = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."signups WHERE activation_key = %s", $activation_key) );

	if ( !empty( $signup ) && !$signup->active ){
		$meta = unserialize( $signup->meta );
		$user_login = esc_sql( $signup->user_login );
		$user_email = esc_sql( $signup->user_email );
        /* the password is in hashed form in the signup table and we will copy it later to the user */
		$password = NULL;

		$user_id = username_exists($user_login);

		if ( ! $user_id )
			$user_id = wppb_create_user( $user_login, $password, $user_email );
		else
			$user_already_exists = true;

		if ( !$user_id )
			return __( 'Could not create user!', 'profile-builder' );

		elseif ( isset( $user_already_exists ) && ( $user_already_exists == true ) )
			return __( 'That username is already activated!', 'profile-builder' );

		else{
			$now = current_time('mysql', true);

			$retVal = ( is_multisite() ? $wpdb->update( $wpdb->signups, array('active' => 1, 'activated' => $now), array('activation_key' => $activation_key) ) : $wpdb->update( $wpdb->base_prefix.'signups', array('active' => 1, 'activated' => $now), array('activation_key' => $activation_key) ) );

			wppb_add_meta_to_user_on_activation( $user_id, '', $meta );

            /* copy the hashed password from signup meta to wp user table */
            if( !empty( $meta['user_pass'] ) ){
                /* we might still have the base64 encoded password in signups and not the hash */
                if( base64_encode(base64_decode($meta['user_pass'], true)) === $meta['user_pass'] )
                    $meta['user_pass'] = wp_hash_password( $meta['user_pass'] );

                $wpdb->update( $wpdb->users, array('user_pass' => $meta['user_pass'] ), array('ID' => $user_id) );
				//This is required so that the APC cached data is updated with the new password. Thanks to @foliovision
				wp_cache_delete( $user_id, 'users' );
            }

			wppb_notify_user_registration_email( get_bloginfo( 'name' ), $user_login, $user_email, 'sending', $password, ( wppb_get_admin_approval_option_value() === 'yes' ? 'yes' : 'no' ) );

			do_action('wppb_activate_user', $user_id, $password, $meta);
			return ( $retVal ? 'ok' : __( 'There was an error while trying to activate the user', 'profile-builder' ) );
		}
	}
}

/**
 * Create a user.
 *
 * @param string $user_name The new user's login name.
 * @param string $password The new user's password.
 * @param string $email The new user's email address.
 * @return mixed Returns false on failure, or int $user_id on success
 */
function wppb_create_user( $user_name, $password, $email) {
    if( is_email( $user_name ) )
        $user_name = apply_filters( 'wppb_generated_random_username', Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $user_name ) ), $user_name );
    else {
        $user_name = sanitize_user($user_name, true);
        if( is_multisite() )
            $user_name = preg_replace( '/\s+/', '', $user_name );
    }


	$user_id = wp_create_user( $user_name, $password, $email );
	if ( is_wp_error($user_id) )
		return false;

	// Newly created users have no roles or caps until they are added to a blog.
	delete_user_option( $user_id, 'capabilities' );
	delete_user_option( $user_id, 'user_level' );

	do_action( 'wppb_new_user', $user_id );

	return $user_id;
}

//send an email to the admin regarding each and every new subscriber, and - if selected - to the user himself
function wppb_notify_user_registration_email( $bloginfo, $user_name, $email, $send_credentials_via_email, $password, $adminApproval ){

    /* if login with email is enabled user_name gets the value of the users email */
    $wppb_general_settings = get_option( 'wppb_general_settings' );
    if ( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) ) {
        $user_name = $email;
    }

	//send email to the admin
	$message_from = apply_filters( 'wppb_register_from_email_message_admin_email', $bloginfo );

	$message_subject = '['.$message_from.'] '.__( 'A new subscriber has (been) registered!', 'profile-builder' );
	$message_subject = apply_filters ('wppb_register_admin_email_subject_without_admin_approval', $message_subject, $email, $password, $message_from, 'wppb_admin_emailc_default_registration_email_subject' );

	$message_content = sprintf( __( 'New subscriber on %1$s.<br/><br/>Username:%2$s<br/>Email:%3$s<br/>', 'profile-builder'), $message_from, $user_name, $email );

	$message_context = 'email_admin_new_subscriber';

	if ( $adminApproval == 'yes' ) {
		$user_data = get_user_by( 'email', $email );

		$wppb_generalSettings = get_option( 'wppb_general_settings', 'not_found' );

		$adminApproval_mailAdmin = 0;

		if( $wppb_generalSettings != 'not_found' && ! empty( $wppb_generalSettings['adminApprovalOnUserRole'] ) ) {
			foreach( $user_data->roles as $role ) {
				if( in_array( $role, $wppb_generalSettings['adminApprovalOnUserRole'] ) ) {
					if( ! current_user_can( 'delete_users' ) ) {
						$adminApproval_mailAdmin = 1;
					}
				}
			}
		} else {
			if( ! current_user_can( 'delete_users' ) ) {
				$adminApproval_mailAdmin = 1;
			}
		}

		if( $adminApproval_mailAdmin == 1 ) {
			$message_subject = apply_filters( 'wppb_register_admin_email_subject_with_admin_approval', $message_subject, $email, $password, $message_from, 'wppb_admin_emailc_registration_with_admin_approval_email_subject' );

			$message_content .= wppb_adminApproval_adminEmailContent();
			$message_content = apply_filters( 'wppb_register_admin_email_message_with_admin_approval', $message_content, $email, $password, $message_from, 'wppb_admin_emailc_registration_with_admin_approval_email_content' );

			$message_context = 'email_admin_approve';
		}
        else{
            $message_content = apply_filters( 'wppb_register_admin_email_message_without_admin_approval', $message_content, $email, $password, $message_from, 'wppb_admin_emailc_default_registration_email_content' );
        }
	} else {
		$message_content = apply_filters( 'wppb_register_admin_email_message_without_admin_approval', $message_content, $email, $password, $message_from, 'wppb_admin_emailc_default_registration_email_content' );
	}

	if ( trim( $message_content ) != '' ){
		$admin_email = apply_filters('wppb_send_to_admin_email', get_option('admin_email'), get_user_by( 'email', $email ), $message_context);
		wppb_mail( $admin_email, $message_subject, $message_content, $message_from, $message_context );
	}



	//send an email to the newly registered user, if this option was selected
	if ( isset( $send_credentials_via_email ) && ( $send_credentials_via_email == 'sending' ) ){
		$user_message_from = apply_filters( 'wppb_register_from_email_message_user_email', $bloginfo );

		$user_message_subject = sprintf( __( '[%1$s] Your new account information', 'profile-builder' ), $user_message_from, $user_name, $password );
		$user_message_subject = apply_filters( 'wppb_register_user_email_subject_without_admin_approval', $user_message_subject, $email, $password, $user_message_from, 'wppb_user_emailc_default_registration_email_subject' );

        if ( $password === NULL ) {
            $password = __( 'Your selected password at signup', 'profile-builder' );
        }

		$send_password = apply_filters( 'wppb_send_password_in_default_email_message', false );
        $site_url = '<a href="'. home_url() .'"> ' . home_url() . ' </a>';
		if( !$send_password )
			$user_message_content = sprintf( __( 'Welcome to %1$s!<br/><br/><br/>Your username is: %2$s and your password is the one that you have selected during registration.<br/><br/>Access your account: %3$s ', 'profile-builder' ), $user_message_from, $user_name, $site_url );
		else
			$user_message_content = sprintf( __( 'Welcome to %1$s!<br/><br/><br/>Your username is: %2$s and the password: %3$s<br/><br/>Access your account: %4$s ', 'profile-builder' ), $user_message_from, $user_name, $password, $site_url );

        if ( $password === __( 'Your selected password at signup', 'profile-builder' ) ) {
            $password = NULL;
        }

		$user_message_context = 'email_user_account_info';

		if ( $adminApproval == 'yes' ){
			$user_data = get_user_by( 'email', $email );

			$wppb_generalSettings = get_option( 'wppb_general_settings', 'not_found' );

			$adminApproval_mailUser = 0;

			if( $wppb_generalSettings != 'not_found' && ! empty( $wppb_generalSettings['adminApprovalOnUserRole'] ) ) {
				foreach( $user_data->roles as $role ) {
					if( in_array( $role, $wppb_generalSettings['adminApprovalOnUserRole'] ) ) {
						if( ! current_user_can( 'delete_users' ) ) {
							$adminApproval_mailUser = 1;
						}
					}
				}
			} else {
				if( ! current_user_can( 'delete_users' ) ) {
					$adminApproval_mailUser = 1;
				}
			}

			if( $adminApproval_mailUser == 1 ) {
				$user_message_subject = apply_filters( 'wppb_register_user_email_subject_with_admin_approval', $user_message_subject, $email, $password, $user_message_subject, 'wppb_user_emailc_registration_with_admin_approval_email_subject' );

				$user_message_content .= wppb_adminApproval_userEmailContent();

				$user_message_context = 'email_user_need_approval';

				$user_message_content = apply_filters( 'wppb_register_user_email_message_with_admin_approval', $user_message_content, $email, $password, $user_message_subject, 'wppb_user_emailc_registration_with_admin_approval_email_content' );
			}
            else{
                $user_message_content = apply_filters( 'wppb_register_user_email_message_without_admin_approval', $user_message_content, $email, $password, $user_message_subject, 'wppb_user_emailc_default_registration_email_content' );
            }
		} else
			$user_message_content = apply_filters( 'wppb_register_user_email_message_without_admin_approval', $user_message_content, $email, $password, $user_message_subject, 'wppb_user_emailc_default_registration_email_content' );

		$message_sent = wppb_mail( $email, $user_message_subject, $user_message_content, $user_message_from, $user_message_context );

		return ( ( $message_sent ) ? 2 : 1 );
	}
}


// function with content to be added in email sent to admin if "Admin Approval" is on
function wppb_adminApproval_adminEmailContent() {
	$emailContent = '<br/>' . __( 'The "Admin Approval" feature was activated at the time of registration, so please remember that you need to approve this user before he/she can log in!', 'profile-builder') ."\r\n";

	return $emailContent;
}


// function with content to be added in email sent to user if "Admin Approval" is on
function wppb_adminApproval_userEmailContent() {
	$emailContent = '<br/><br/>' . __( 'Before you can access your account, an administrator needs to approve it. You will be notified via email.', 'profile-builder' );

	return $emailContent;
}
// END FUNCTIONS USED BOTH ON THE REGISTRATION PAGE AND THE EMAIL CONFIRMATION TABLE


// Set up the AJAX hooks
add_action( 'wp_ajax_wppb_get_unconfirmed_email_number', 'wppb_get_unconfirmed_email_number' );
add_action( 'wp_ajax_wppb_handle_email_confirmation_cases', 'wppb_handle_email_confirmation_cases' );


$wppb_general_settings = get_option( 'wppb_general_settings', 'not_found' );
if( $wppb_general_settings != 'not_found' ){

    if( !empty($wppb_general_settings['emailConfirmation'] ) && ( $wppb_general_settings['emailConfirmation'] == 'yes' ) ){
        if ( is_multisite() ){
            /* don't display on network admin */
            if( isset($_SERVER['SCRIPT_NAME']) && strpos( sanitize_text_field( $_SERVER['SCRIPT_NAME'] ), 'users.php') && !is_network_admin() ){  //global $pagenow doesn't seem to work
                add_action( 'admin_head', 'wppb_add_pending_users_header_script' );
            }
        }else{
            global $pagenow;
            // the Unconfirmed Email Address submenu page is added to profile.php if the user does not have the
            // list_users capability so we also check for it
            if ( $pagenow == 'users.php' || $pagenow == 'profile.php' ){
                add_action( 'admin_head', 'wppb_add_pending_users_header_script' );
            }
        }

        if ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists ( WPPB_PAID_PLUGIN_DIR . '/features/admin-approval/admin-approval.php' ) )
            add_action( 'user_register', 'wppb_update_user_status_on_admin_registration' );
    }

}
