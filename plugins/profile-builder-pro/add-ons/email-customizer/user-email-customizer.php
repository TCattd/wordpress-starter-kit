<?php
/**
 * Function that creates the User Email Customizer menu
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_user_email_customizer_submenu(){
	$args = array(
				'menu_title' 	=> __( 'User Email Customizer', 'profile-builder' ),
				'page_title' 	=> __( 'User Email Customizer Settings', 'profile-builder' ),
				'menu_slug'		=> 'user-email-customizer',
				'page_type'		=> 'submenu_page',
				'capability'	=> 'manage_options',
				'priority'		=> 10,
				'parent_slug'	=> 'profile-builder'
			);

	new WCK_Page_Creator_PB( $args );
}
add_action( 'admin_menu', 'wppb_user_email_customizer_submenu', 1 );

add_action( 'wck_before_meta_boxes', 'wppb_add_tabs_on_top_of_user_email_page' );
function wppb_add_tabs_on_top_of_user_email_page( $hookname ){
	if( $hookname == 'profile-builder_page_user-email-customizer' ){

        if( isset( $_GET['mustache_action'] ) && $_GET['mustache_action'] == 'save' ) { ?>
            <div id="setting-error-settings_updated" class="updated settings-error notice">
                <p><strong><?php esc_html_e( "Settings saved.", 'profile-builder' ); ?></strong></p>
            </div>
            <?php
        }

		wppb_generate_settings_tabs();
	}
}

/* on the init hook add the mustache boxes */
add_action( 'init', 'wppb_user_email_customizer_add_mustache_in_backend', 11 );
/**
 * Function that ads the mustache boxes in the backend for user email customizer
 *
 * @since v.2.0
 */
function wppb_user_email_customizer_add_mustache_in_backend(){
	require_once( WPPB_PAID_PLUGIN_DIR.'/assets/lib/class-mustache-templates/class-mustache-templates.php' );

	$fields = array(
				array(
					'id'	=> 'wppb_admin_emailc_common_settings_header', // field id and name
					'type'	=> 'header', // type of field
					'default'	=> __( 'These settings are also replicated in the "Admin Email Customizer" settings-page upon save.', 'profile-builder' ).' '.__( 'Valid tags {{reply_to}} and {{site_name}}', 'profile-builder'), // type of field
				),
				array(
					'label'	=> __( 'From (name)', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_emailc_common_settings_from_name', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> '{{site_name}}', // type of field
					'desc' => '',
				),
				array(
					'label'	=> __( 'From (reply-to email)', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_emailc_common_settings_from_reply_to_email', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> '{{reply_to}}', // type of field
                    'desc' => __( 'Must be a valid email address or the tag {{reply_to}} which defaults to the administrator email', 'profile-builder' ),
				),
			);
	new PB_Mustache_Generate_Admin_Box( 'uec_common_settings', __( 'Common Settings', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', '', '', $fields );

	/*
	 * Default Registration
	 */

    // we format the var like this for proper line breaks.
    $uec_default_registration = __("<h3>Welcome to {{site_name}}!</h3>\n<p>Your username is: {{username}}</p>\n", 'profile-builder' );
    $mustache_vars = wppb_email_customizer_generate_merge_tags();
    $fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_user_emailc_default_registration_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> 'A new account has been created for you on {{site_name}}', // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_user_emailc_default_registration_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_user_emailc_default_registration_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $uec_default_registration, // type of field
                )
			);
	new PB_Mustache_Generate_Admin_Box( 'uec_default_registration', __( 'Default Registration', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

    /*
     * Registration with Email Confirmation
     */
    // we format the var like this for proper line breaks.
    $uec_reg_with_email_confirm = __( "<p>To activate your user, please click the following link:<br/>\n{{{activation_link}}}</p>\n<p>After you activate, you will receive another email with your credentials.</p>\n", 'profile-builder' );
    $mustache_vars = wppb_email_customizer_generate_merge_tags( 'email_confirmation' );
    $fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_user_emailc_registr_w_email_confirm_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> __( '[{{site_name}}] Activate {{username}}', 'profile-builder' ), // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_user_emailc_registr_w_email_confirm_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_user_emailc_registr_w_email_confirm_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $uec_reg_with_email_confirm, // type of field
                )
			);

	new PB_Mustache_Generate_Admin_Box( 'uec_reg_with_email_confirmation', __( 'Registration with Email Confirmation', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

	/*
	 * Registration with Admin Approval
	 */

    $uec_reg_with_admin_approval = __( "<h3>Welcome to {{site_name}}!</h3>\n<p>Your username is: {{username}}</p>\n<p>Before you can access your account, an administrator needs to approve it. You will be notified via email.</p>\n", 'profile-builder' );
    $mustache_vars = wppb_email_customizer_generate_merge_tags();
	$fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_user_emailc_registration_with_admin_approval_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> __( 'A new account has been created for you on {{site_name}}', 'profile-builder' ), // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_user_emailc_registration_with_admin_approval_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_user_emailc_registration_with_admin_approval_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $uec_reg_with_admin_approval, // type of field
                )
			);

	new PB_Mustache_Generate_Admin_Box( 'uec_reg_with_admin_approval', __( 'Registration with Admin Approval', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

	/*
	 * Admin Approval Notifications ( on user approval )
	 */
    $uec_notif_approved_email = __( "<h3>Good News!</h3>\n<p>An administrator has just approved your account: {{username}} on {{site_name}}.</p>\n", 'profile-builder' );
    $mustache_vars = wppb_email_customizer_generate_merge_tags();
    $fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_user_emailc_admin_approval_notif_approved_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> __( 'Your account on {{site_name}} has been approved!', 'profile-builder' ), // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_user_emailc_admin_approval_notif_approved_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_user_emailc_admin_approval_notif_approved_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $uec_notif_approved_email, // type of field
                )
			);

	new PB_Mustache_Generate_Admin_Box( 'uec_notif_approved_email', __( 'User Approval Notification', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

	/*
	 * Admin Approval Notifications ( on user unapproval )
	 */
    $uec_notif_unapproved_email = __( "<h3>Hello,</h3>\n<p>Unfortunatelly an administrator has just unapproved your account: {{username}} on {{site_name}}.</p>\n", 'profile-builder' );
    $mustache_vars = wppb_email_customizer_generate_merge_tags();

    $fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_user_emailc_admin_approval_notif_unapproved_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> __( 'Your account on {{site_name}} has been unapproved!', 'profile-builder' ), // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_user_emailc_admin_approval_notif_unapproved_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_user_emailc_admin_approval_notif_unapproved_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $uec_notif_unapproved_email , // type of field
                )
			);

	new PB_Mustache_Generate_Admin_Box( 'uec_notif_unapproved_email', __( 'Unapproved User Notification', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

	/*
	 * Password Reset Email
	 */
	// we format the var like this for proper line breaks.
	$uec_reset = __( "<p>Someone requested that the password be reset for the following account: {{site_name}}<br/>\nUsername: {{username}}</p>\n<p>If this was a mistake, just ignore this email and nothing will happen.</p>\n<p>To reset your password, visit the following address:<br/>\n{{{reset_link}}}</p>\n", 'profile-builder' );
	$mustache_vars = wppb_email_customizer_generate_merge_tags( 'password_reset' );
	$fields = array(
		array(
			'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_user_emailc_reset_email_subject', // field id and name
			'type'	=> 'text', // type of field
			'default'	=> __( '[{{site_name}}] Password Reset', 'profile-builder' ), // type of field
		),
		array(
			'label'   => __( 'Enable email', 'profile-builder' ), // <label>
			'desc'    => '', // description
			'id'      => 'wppb_user_emailc_reset_email_enabled', // field id and name
			'type'    => 'checkbox', // type of field
			'default' => 'on',
		),
		array( // Textarea
			'label'	=> '', // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_user_emailc_reset_email_content', // field id and name
			'type'	=> 'textarea', // type of field
			'default'	=> $uec_reset, // type of field
		)
	);

	new PB_Mustache_Generate_Admin_Box( 'uec_reset', __( 'Password Reset Email', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

	/*
 * Password Reset Success Email
 */
	// we format the var like this for proper line breaks.
	$uec_reset_success = __( "<p>You have successfully reset your password.</p>\n", 'profile-builder' );
	$mustache_vars = wppb_email_customizer_generate_merge_tags( 'password_reset_success' );
	$fields = array(
		array(
			'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_user_emailc_reset_success_email_subject', // field id and name
			'type'	=> 'text', // type of field
			'default'	=> __( '[{{site_name}}] Password Reset Successfully', 'profile-builder' ), // type of field
		),
		array(
			'label'   => __( 'Enable email', 'profile-builder' ), // <label>
			'desc'    => '', // description
			'id'      => 'wppb_user_emailc_reset_success_email_enabled', // field id and name
			'type'    => 'checkbox', // type of field
			'default' => 'on',
		),
		array( // Textarea
			'label'	=> '', // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_user_emailc_reset_success_email_content', // field id and name
			'type'	=> 'textarea', // type of field
			'default'	=> $uec_reset_success, // type of field
		)
	);

	new PB_Mustache_Generate_Admin_Box( 'uec_reset_success', __( 'Password Reset Success Email', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

    /*
    * Change Email Address Request Notification
    */
    $admin_email = get_option('admin_email');

    // we format the var like this for proper line breaks.
    $uec_change_email_request = sprintf( __( "<h3>Hi {{username}},</h3>\n<p>There is an email address change request on {{site_name}}.</p>\n<p>To change your email address click on: {{{user_email_change_link}}}</p>\n<p>Regards,<br>\nAll at {{site_name}}<br>\n<a href=\"{{site_url}}\">{{site_url}}</a></p>", 'profile-builder' ), $admin_email );
    $mustache_vars = wppb_email_customizer_generate_merge_tags( 'change_email_address_request' );
    $fields = array(
        array(
            'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
            'desc'	=> '', // description
            'id'	=> 'wppb_user_emailc_change_email_address_request_subject', // field id and name
            'type'	=> 'text', // type of field
            'default'	=> __( '[{{site_name}}] Notice of Email Change Request', 'profile-builder' ), // type of field
        ),
        array(
            'label'   => __( 'Enable email', 'profile-builder' ), // <label>
            'desc'    => '', // description
            'id'      => 'wppb_user_emailc_change_email_address_request_enabled', // field id and name
            'type'    => 'checkbox', // type of field
            'default' => 'on',
        ),
        array( // Textarea
            'label'	=> '', // <label>
            'desc'	=> '', // description
            'id'	=> 'wppb_user_emailc_change_email_address_request_content', // field id and name
            'type'	=> 'textarea', // type of field
            'default'	=> $uec_change_email_request, // type of field
        )
    );

    new PB_Mustache_Generate_Admin_Box( 'uec_change_email_request', __( 'Change Email Address Request Notification', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

    /*
    * Change Email Address Notification
    */
	$admin_email = get_option('admin_email');

	// we format the var like this for proper line breaks.
	$uec_change_email = sprintf( __( "<h3>Hi {{username}},</h3>\n<p>This notice confirms that your email was changed on {{site_name}}.</p>\n<p>If you did not change your email, please contact the Site Administrator at %s</p>\n<p>This email has been sent to {{user_email}}</p>\n<p>Regards,<br>\nAll at {{site_name}}<br>\n<a href=\"{{site_url}}\">{{site_url}}</a></p>", 'profile-builder' ), $admin_email );
	$mustache_vars = wppb_email_customizer_generate_merge_tags( 'change_email_address' );
	$fields = array(
		array(
			'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_user_emailc_change_email_address_subject', // field id and name
			'type'	=> 'text', // type of field
			'default'	=> __( '[{{site_name}}] Notice of Email Change', 'profile-builder' ), // type of field
		),
		array(
			'label'   => __( 'Enable email', 'profile-builder' ), // <label>
			'desc'    => '', // description
			'id'      => 'wppb_user_emailc_change_email_address_enabled', // field id and name
			'type'    => 'checkbox', // type of field
			'default' => 'on',
		),
		array( // Textarea
			'label'	=> '', // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_user_emailc_change_email_address_content', // field id and name
			'type'	=> 'textarea', // type of field
			'default'	=> $uec_change_email, // type of field
		)
	);

	new PB_Mustache_Generate_Admin_Box( 'uec_change_email', __( 'Changed Email Address Notification', 'profile-builder' ), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields );

    /*
    * User Notification for Edit Profile Approved by Admin
    */

    // check if the Edit Profile Approved by Admin add-on is active to see if the email should be displayed
    if( function_exists( 'wppb_in_init_edit_profile_approval' ) ) {

		// we format the var like this for proper line breaks.
		$uec_epaa_notification = __("<p>Your profile has been reviewed by an administrator:</p>\n<br>\n<p>Approved Fields: {{approved_fields}}</p>\n<p>Unapproved Fields: {{unapproved_fields}}</p>\n", 'profile-builder');
		$mustache_vars = wppb_email_customizer_generate_merge_tags('epaa_notification');
		$fields = array(
			array(
				'label' => __('Email Subject', 'profile-builder'), // <label>
				'desc' => '', // description
				'id' => 'wppb_user_emailc_epaa_notification_subject', // field id and name
				'type' => 'text', // type of field
				'default' => __('[{{site_name}}] Your profile has been reviewed by an administrator', 'profile-builder'), // type of field
			),
			array(
				'label' => __('Enable email', 'profile-builder'), // <label>
				'desc' => '', // description
				'id' => 'wppb_user_emailc_epaa_notification_enabled', // field id and name
				'type' => 'checkbox', // type of field
				'default' => 'on',
			),
			array( // Textarea
				'label' => '', // <label>
				'desc' => '', // description
				'id' => 'wppb_user_emailc_epaa_notification_content', // field id and name
				'type' => 'textarea', // type of field
				'default' => $uec_epaa_notification, // type of field
			)
		);

		new PB_Mustache_Generate_Admin_Box('uec_epaa_notification', __('User Notification for Edit Profile Approved by Admin', 'profile-builder'), 'profile-builder_page_user-email-customizer', 'core', $mustache_vars, '', $fields);

    }
}
