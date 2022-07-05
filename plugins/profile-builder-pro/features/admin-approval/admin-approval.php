<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function wppb_add_header_script(){
    $term_unapproved = get_term_by('slug', 'unapproved', 'user_status' );
    if( is_object( $term_unapproved ) && !empty( $term_unapproved->count ) ) {
        $unapproved_users = $term_unapproved->count;
    } else {
        $unapproved_users = '0';
    }

    $term_pending = get_term_by('slug', 'pending', 'user_status' );
    if( is_object( $term_pending ) && !empty( $term_pending->count ) ) {
        $pending_users = $term_pending->count;
    } else {
        $pending_users = '0';
    }

	?>
    <style type='text/css'>
        body.users_page_admin_approval th#username{
            width: 20em;
        }
    </style>
	<script type="text/javascript">
		// script to add an extra link to the users page listing the unapproved users
		jQuery(document).ready(function() {
            jQuery('.wrap ul.subsubsub').append('<span id="separatorID2"> |</span> <li class="listAllUserForBulk"><a class="bulkActionUsers" href="?page=admin_approval&orderby=registered&order=desc"><?php echo esc_html( str_replace( "'", "&#39;", __( 'Admin Approval', 'profile-builder' ) ) ); ?> (<?php echo esc_html( $pending_users ) . ' pending | '. esc_html( $unapproved_users ) . ' unapproved'; ?>) </a> </li>');
		});

		function confirmAUActionBulk( URL, message, nonce, users, todo ) {
			if (confirm(message)) {
				jQuery.post( ajaxurl, { action:"wppb_handle_bulk_approve_unapprove_cases", URL:URL, todo:todo, users:users, _ajax_nonce:nonce}, function(response) {
					alert(response.trim());
					window.location=URL;
				})
			}
		}

		// script to create a confirmation box for the user upon approving/unapproving a user
		function confirmAUAction( URL, todo, userID, nonce, actionText ) {
			actionText = '<?php esc_html_e( 'Do you want to', 'profile-builder' );?>'+' '+actionText;

			if (confirm(actionText)) {
				jQuery.post( ajaxurl, { action:"wppb_handle_approve_unapprove_cases", URL:URL, todo:todo, userID:userID, _ajax_nonce:nonce}, function(response) {
					alert(response.trim());
					window.location=URL;
				});
			}
		}

	</script>
<?php
}

function wppb_handle_approve_unapprove_cases(){
	global $current_user;
	global $wpdb;

	$todo = isset( $_POST['todo'] ) ? sanitize_text_field( $_POST['todo'] ) : '';
	$userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : '';
	$nonce = isset( $_POST['_ajax_nonce'] ) ? sanitize_text_field( $_POST['_ajax_nonce'] ) : '';

	if (! wp_verify_nonce($nonce, '_nonce_'.$current_user->ID.$userID) )
		die( esc_html__( 'Your session has expired! Please refresh the page and try again.', 'profile-builder' ) );

	if ( current_user_can( apply_filters( 'wppb_admin_approval_user_capability', 'manage_options' ) ) ){
		if ( ( $todo != '' ) && ( $userID != '' ) ){

			if ( $todo == 'approve' ){
				wp_set_object_terms( $userID, apply_filters( 'wppb_admin_approval_update_user_status', NULL, $userID ), 'user_status' );
				clean_object_term_cache( $userID, 'user_status' );

                // now that the user is approved, remove approval link key from usermeta
                delete_user_meta( $userID, '_wppb_admin_approval_link_param');

				do_action( 'wppb_after_user_approval', $userID );

				wppb_send_new_user_status_email( $userID, 'approved' );

				die( esc_html__( "User successfully approved!", "profile-builder" ) );

			}elseif ( $todo == 'unapprove' ){
				wp_set_object_terms( $userID, apply_filters( 'wppb_admin_approval_update_user_status', array( 'unapproved' ), $userID ), 'user_status', false );
				clean_object_term_cache( $userID, 'user_status' );

				do_action( 'wppb_after_user_unapproval', $userID );

				wppb_send_new_user_status_email( $userID, 'unapproved' );

				die( esc_html__( "User successfully unapproved!", "profile-builder" ) );

			}elseif ( $todo == 'delete' ){
				require_once( ABSPATH.'wp-admin/includes/user.php' );
                wp_remove_object_terms( $userID, array('pending'), 'user_status' );
				wp_delete_user( $userID );

				die( esc_html__( "User successfully deleted!", "profile-builder" ) );
			}
		}

	}else
		die( esc_html__("You either don't have permission for that action or there was an error!", "profile-builder"));
}

function wppb_handle_bulk_approve_unapprove_cases(){
	global $current_user;

	$nonce = isset( $_POST['_ajax_nonce'] ) ? sanitize_text_field($_POST['_ajax_nonce']) : '';

	if (! wp_verify_nonce($nonce, '_nonce_'.$current_user->ID.'_bulk') )
		die( esc_html__( "Your session has expired! Please refresh the page and try again.", "profile-builder" ));

    $todo = isset($_POST['todo']) ? sanitize_text_field($_POST['todo']) : '';
    $users = !empty( $_POST['users'] ) ? array_map( 'absint', explode(',', sanitize_text_field( $_POST['users'] ) ) ) : array();

	if (current_user_can( apply_filters( 'wppb_admin_approval_user_capability', 'manage_options' ) )){
		if (($todo != '') && (is_array($users)) && !empty( $users ) ){
			if( $todo === 'bulkApprove' ){
				foreach( $users as $user ){
                    if ($current_user->ID != $user){
                        wp_set_object_terms( $user, apply_filters( 'wppb_admin_approval_update_user_status', NULL, $user ), 'user_status' );
                        clean_object_term_cache( $user, 'user_status' );
                        wppb_send_new_user_status_email( $user, 'approved' );
						do_action('wppb_after_user_approval', $user );
                    }
                }
                die( esc_html__( "Users successfully approved!", "profile-builder" ) );
			}elseif ($todo === 'bulkUnapprove'){
				foreach( $users as $user ){
                    if ($current_user->ID != $user ){
                        wp_set_object_terms( $user, apply_filters( 'wppb_admin_approval_update_user_status', array( 'unapproved' ), $user ), 'user_status', false);
                        clean_object_term_cache( $user, 'user_status' );
                        wppb_send_new_user_status_email( $user, 'unapproved' );
						do_action('wppb_after_user_unapproval', $user );
                    }
				}
				die( esc_html__("Users successfully unapproved!", "profile-builder"));
			}elseif( $todo === 'bulkDelete' ){
				require_once(ABSPATH.'wp-admin/includes/user.php');
				foreach( $users as $user ){
					if ($current_user->ID != $user ){
                        wp_remove_object_terms( $user, array('pending'), 'user_status' );
						wp_delete_user( $user );
					}
				}
				die( esc_html__("Users successfully deleted!", "profile-builder"));
			}
		}
    }else
        die( esc_html__("You either don't have permission for that action or there was an error!", "profile-builder"));
}

function wppb_send_new_user_status_email($userID, $newStatus){
	$wppb_general_settings = get_option( 'wppb_general_settings' );
	$user_info = get_userdata($userID);

	if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) ) {
		$user_login = $user_info->user_email;
	} else {
		$user_login = $user_info->user_login;
	}

	$userMessageFrom = apply_filters( 'wppb_new_user_status_from_email_content', get_bloginfo( 'name' ), $userID, $newStatus );

	if ( $newStatus == 'approved' ){
		$userMessageSubject = sprintf( __( 'Your account on %1$s has been approved!', 'profile-builder' ), get_bloginfo( 'name' ) );
		$userMessageSubject = apply_filters( 'wppb_new_user_status_subject_approved', $userMessageSubject, $user_info, __( 'approved', 'profile-builder' ), $userMessageFrom, 'wppb_user_emailc_admin_approval_notif_approved_email_subject' );

		$userMessageContent = sprintf( __( 'An administrator has just approved your account on %1$s (%2$s).', 'profile-builder' ), get_bloginfo( 'name' ), $user_login );
		$userMessageContent = apply_filters('wppb_new_user_status_message_approved', $userMessageContent, $user_info, __( 'approved', 'profile-builder' ), $userMessageFrom, 'wppb_user_emailc_admin_approval_notif_approved_email_content' );

		$userMessage_context = 'email_user_approved';
	}elseif ( $newStatus == 'unapproved' ){
		$userMessageSubject = sprintf( __( 'Your account on %1$s has been unapproved!', 'profile-builder'), get_bloginfo( 'name' ));
		$userMessageSubject = apply_filters( 'wppb_new_user_status_subject_unapproved', $userMessageSubject, $user_info, __( 'unapproved', 'profile-builder' ), $userMessageFrom, 'wppb_user_emailc_admin_approval_notif_unapproved_email_subject' );

		$userMessageContent = sprintf( __( 'An administrator has just unapproved your account on %1$s (%2$s).', 'profile-builder' ), get_bloginfo( 'name' ), $user_login );
		$userMessageContent = apply_filters( 'wppb_new_user_status_message_unapproved', $userMessageContent, $user_info, __( 'unapproved', 'profile-builder' ), $userMessageFrom, 'wppb_user_emailc_admin_approval_notif_unapproved_email_content' );

		$userMessage_context = 'email_user_unapproved';
	}

	wppb_mail( $user_info->user_email, $userMessageSubject, $userMessageContent, $userMessageFrom, $userMessage_context );
}

// function to register the new user_status taxonomy for the admin approval
function wppb_register_user_status_taxonomy() {

	register_taxonomy('user_status','user',array('public' => false));
}

// function to create a new wp error in case the admin approval feature is active and the given user is still unapproved
function wppb_unapproved_user_admin_error_message_handler($userdata, $password){

	if (wp_get_object_terms( $userdata->ID, 'user_status' )){
		$errorMessage = __('<strong>ERROR</strong>: Your account has to be confirmed by an administrator before you can log in.', 'profile-builder');

		return new WP_Error('wppb_unapproved_user_admin_error_message', $errorMessage);
	}else

		return $userdata;
}

// function to prohibit user from using the default wp password recovery feature
function wppb_unapproved_user_password_recovery( $allow, $userID ){

	if (wp_get_object_terms( $userID, 'user_status' ))
		return new WP_Error( 'wppb_no_password_reset', __( 'Your account has to be confirmed by an administrator before you can use the "Password Recovery" feature.', 'profile-builder' ) );
	else
		return true;
}

// function to add the "pending" status for the user who just registered using the WP registration form (only if the admin approval feature is active)
function wppb_update_user_status_on_admin_registration( $user_id ){
    if( ! current_user_can( apply_filters( 'wppb_admin_approval_user_capability', 'manage_options' ) ) ) {
        $wppb_generalSettings = get_option('wppb_general_settings', 'not_found');
        wppb_update_user_status_to_pending( $user_id, $wppb_generalSettings );
    }
}

function wppb_update_user_status_to_pending( $user_id, $wppb_generalSettings ){
    $user_data = get_userdata($user_id);

    if ($wppb_generalSettings !== 'not_found' && !empty($wppb_generalSettings['adminApprovalOnUserRole'])) {
        foreach ($user_data->roles as $role) {
            if (in_array($role, $wppb_generalSettings['adminApprovalOnUserRole'])) {
                wp_set_object_terms($user_id, apply_filters( 'wppb_admin_approval_update_user_status', array('pending'), $user_id ), 'user_status', false);
                clean_object_term_cache($user_id, 'user_status');
                // save admin approval email link unique parameter ( used for outputting Admin Email Customizer {{{approve_link}}} or {{approve_url}} tags )
                add_user_meta( $user_id, '_wppb_admin_approval_link_param', wppb_get_admin_approval_email_link_key($user_id) );
                do_action('wppb_new_user_pending_approval', $user_id );

            } else {
                add_filter('wppb_register_success_message', 'wppb_noAdminApproval_successMessage');
            }
        }
    } else {
        wp_set_object_terms($user_id, apply_filters( 'wppb_admin_approval_update_user_status', array('pending'), $user_id ), 'user_status', false);
        clean_object_term_cache($user_id, 'user_status');
        // save admin approval email link unique parameter ( used for outputting Admin Email Customizer {{{approve_link}}} or {{approve_url}} tags )
        add_user_meta( $user_id, '_wppb_admin_approval_link_param', wppb_get_admin_approval_email_link_key($user_id) );;
        do_action('wppb_new_user_pending_approval', $user_id );
    }
}

function wppb_noAdminApproval_successMessage() {
	return __( "Your account has been successfully created!", 'profile-builder' );
}

/**
 * Function that returns the key (hash value) used for enabling user approval by the admin directly from email, by clicking a specifically formed link (which contains the hash value)
 *
 * @param int $userID The ID of the user pending approval
 *
 * @return string $key to be appended to the admin approval email link created by using Admin Email Customizer {{approve_url}} or {{{approve_link}}} tags
 */
function wppb_get_admin_approval_email_link_key( $userID ){

    $user_info = get_userdata($userID);

    $data = $userID . $user_info->user_email . get_site_url() . time() ;

    $key = hash_hmac( 'sha256' , $data, $user_info->user_email . time() );

    return $key;
}

/**
 * Function that listens and handles the admin approval of users from email, which is done by clicking a specifically formed link
 *
 */
function wppb_approve_user_from_email_url_listener(){

    if( !isset( $_GET['pbapprove'] ) ){
        return;
    }

    //Doing it like this for backwards compatibility
    $action = isset( $_GET['pbaction'] ) ? sanitize_text_field( $_GET['pbaction'] ) : 'approve';

    global $wpdb;

    //search db to see if there's any identical key saved in _usermeta and get that user id
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='_wppb_admin_approval_link_param' AND meta_value =%s", sanitize_text_field( $_GET['pbapprove'] ) ), ARRAY_N );

    // check if we got a match
    if ( !empty($results[0][0]) ) {
        $userID = intval($results[0][0]);

        // add extra confirmation step
        if( apply_filters( 'wppb_enable_admin_approval_confirmation', true ) ) {

            if ( !isset( $_GET['approval_confirmation'] ) ) {

                $form_style = apply_filters('wppb_approval_confirmation_form_style', '
                    <style>
                        #wppb-confirm-admin-approval{
                            padding: 0px 15px 0px px;
                            overflow: auto;
                            text-align: center;
                        }
                        #wppb-approval-confirmation-button{
                            margin: 0% 5%;
                            width: 10%;
                        }
                    </style>');

                if( $action == 'approve' )
                    $approval_notification = apply_filters('wppb_approval_notification_message', __('Do you wish to approve the registration?', 'profile-builder'), $userID);
                else
                    $approval_notification = apply_filters('wppb_approval_notification_message', __('Do you wish to unapprove the registration?', 'profile-builder'), $userID);

                // phpcs:disable
                echo $form_style;
                echo '<form method="get" id="wppb-confirm-admin-approval" class="wppb-user-forms">' . '
                        <p>' . esc_html( $approval_notification ) . '</p>
                        <input type="hidden" id="pbapprove" name="pbapprove" value="' . esc_attr( $_GET['pbapprove'] ) . '">
                        <input type="hidden" id="pbaction" name="pbaction" value="' . esc_attr( $_GET['pbaction'] ) . '">
                        <input type="hidden" name="wppb_nonce" value="'. esc_attr( wp_create_nonce( 'wppb_approval_request' ) ) . '" />
                        <p class="form-submit">
                            <input name="approval_confirmation" type="submit" id="wppb-approval-confirmation-button" class="submit button" value="Yes">
                            <input name="approval_confirmation" type="submit" id="wppb-approval-confirmation-button" class="submit button" value="No">
                        </p>
                    </form>';
                // phpcs:enable

                wp_die('', 'Admin Approval');

            } elseif ( isset( $_GET['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_GET['wppb_nonce'] ), 'wppb_approval_request' ) ){

                if($_GET['approval_confirmation'] === 'Yes') {
                    wppb_approve_unapprove_user_from_email_url_listener( $userID, $action );
                } elseif ($_GET['approval_confirmation'] === 'No') {
                    $message = apply_filters('wppb_approve_user_from_email_decline_message', __('User status not modified!', 'profile-builder'), $userID);

                    wp_die( esc_html( $message ), esc_html__( 'Admin Approval Declined', 'profile-builder' ) );
                }

            } else {
                $message = apply_filters('wppb_approve_user_from_email_decline_message', __('Something went wrong!', 'profile-builder'), $userID);

                wp_die( esc_html( $message ), esc_html__( 'Admin Approval Error', 'profile-builder' ) );
            }

        } else {
            wppb_approve_unapprove_user_from_email_url_listener( $userID, $action );
        }

    }

    // no user was found that has the same hash key

    // build the admin approval from backend link (when admin is logged in)
    $admin_approval_url = add_query_arg(
                         array(
                                'page'      => 'admin_approval',
                                'orderby'   => 'registered',
                                'order'     => 'desc'
                         ),
                        admin_url('users.php')
    );
    $message = sprintf( __( 'The approval link is not valid! Please <a href="%s"> log in </a> to approve the user manually. ', 'profile-builder' ), esc_url( $admin_approval_url ) );

    $message = apply_filters('wppb_approve_user_from_email_error_message', $message);

    wp_die( wp_kses_post( $message ), esc_html__( 'Admin Approval Unsuccessful', 'profile-builder' ) );

}
add_action('wp_loaded', 'wppb_approve_user_from_email_url_listener');

function wppb_approve_unapprove_user_from_email_url_listener( $userID, $action ){

    if( $action == 'approve' ){

        //approve user by removing 'unnaprove' term
        wp_set_object_terms($userID, apply_filters( 'wppb_admin_approval_update_user_status', NULL, $userID ), 'user_status');
        clean_object_term_cache($userID, 'user_status');

        do_action('wppb_after_user_approval', $userID);

        // send email notifying the user
        wppb_send_new_user_status_email($userID, 'approved');

        // now that the user is approved, remove approval link key from usermeta
        delete_user_meta($userID, '_wppb_admin_approval_link_param');

        $message = apply_filters('wppb_approve_user_from_email_success_message', __('User successfully approved!', 'profile-builder'), $userID);

        wp_die( esc_html( $message ), esc_html__('Admin Approval', 'profile-builder' ) );

    } else if( $action == 'unapprove' ){

        wp_set_object_terms( $userID, apply_filters( 'wppb_admin_approval_update_user_status', array( 'unapproved' ), $userID ), 'user_status', false );
        clean_object_term_cache( $userID, 'user_status' );

        do_action( 'wppb_after_user_unapproval', $userID );

        wppb_send_new_user_status_email( $userID, 'unapproved' );

        // now that the user is approved, remove approval link key from usermeta
        delete_user_meta($userID, '_wppb_admin_approval_link_param');

        $message = apply_filters('wppb_unapprove_user_from_email_success_message', __('User successfully unapproved!', 'profile-builder'), $userID);

        wp_die( esc_html( $message ), esc_html__('Admin Approval', 'profile-builder' ) );

    }

}


// Set up the AJAX hooks
add_action( 'wp_ajax_wppb_handle_approve_unapprove_cases', 'wppb_handle_approve_unapprove_cases' );
add_action( 'wp_ajax_wppb_handle_bulk_approve_unapprove_cases', 'wppb_handle_bulk_approve_unapprove_cases' );


$wppb_generalSettings = get_option('wppb_general_settings', 'not_found');
if( $wppb_generalSettings != 'not_found' )
	if( wppb_get_admin_approval_option_value() === 'yes' ){
		if ( is_multisite() ){
			if ( isset( $_SERVER['SCRIPT_NAME'] ) && strpos( sanitize_text_field( $_SERVER['SCRIPT_NAME'] ), 'users.php' ) ){  //global $pagenow doesn't seem to work
				add_action( 'admin_head', 'wppb_add_header_script' );
			}
		}else{
			global $pagenow;
            // the Admin Approval submenu page is added to profile.php if the user does not have the
            // list_users capability so we also check for it
			if ( $pagenow == 'users.php' || $pagenow == 'profile.php' ){
				add_action( 'admin_head', 'wppb_add_header_script' );
			}
		}

		add_action( 'init', 'wppb_register_user_status_taxonomy', 1 );
		add_filter( 'wp_authenticate_user', 'wppb_unapproved_user_admin_error_message_handler', 10, 2 );
		add_filter( 'allow_password_reset', 'wppb_unapproved_user_password_recovery', 10, 2 );
		add_action( 'user_register', 'wppb_update_user_status_on_admin_registration' );

		/* when deleting a user delete the taxonomy as well */
		add_action( 'deleted_user', 'wppb_remove_unapproved_term_from_db_when_deleting_user' );
		function wppb_remove_unapproved_term_from_db_when_deleting_user( $id )
		{
			wp_remove_object_terms( $id, 'unapproved', 'user_status' );
		}

		add_action( 'load-users.php', 'wppb_delete_user_status_scraps_from_db' );
		function wppb_delete_user_status_scraps_from_db() {
			$cleaned_up = get_option( 'wppb_cleaned_up_user_status_taxonomy_from_db' );
			if( !$cleaned_up ) {
				global $wpdb;

				$all_user_ids = $wpdb->get_col("SELECT ID FROM $wpdb->users");
				if (!empty($all_user_ids)) {
					$all_user_ids = implode(',', $all_user_ids);

					$term = get_term_by('name', 'unapproved', 'user_status');
					if (!empty($term->term_taxonomy_id)) {
						$deleted = $wpdb->query("DELETE $wpdb->term_relationships FROM $wpdb->term_relationships WHERE $wpdb->term_relationships.term_taxonomy_id = $term->term_taxonomy_id AND $wpdb->term_relationships.object_id NOT IN ($all_user_ids)");
						wp_update_term_count_now(array($term->term_taxonomy_id), 'user_status');
						update_option('wppb_cleaned_up_user_status_taxonomy_from_db', 'done');
					}
				}
			}
		}
	}
