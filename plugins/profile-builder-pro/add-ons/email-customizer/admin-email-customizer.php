<?php
/**
 * Function that creates the Admin Email Customizer menu
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_admin_email_customizer_submenu(){
	$args = array(
				'menu_title' 	=> __( 'Admin Email Customizer', 'profile-builder' ),
				'page_title' 	=> __( 'Admin Email Customizer Settings', 'profile-builder' ),
				'menu_slug'		=> 'admin-email-customizer',
				'page_type'		=> 'submenu_page',
				'capability'	=> 'manage_options',
				'priority'		=> 10,
				'parent_slug'	=> 'profile-builder'
			);

	new WCK_Page_Creator_PB( $args );
}
add_action( 'admin_menu', 'wppb_admin_email_customizer_submenu', 1 );

add_action( 'wck_before_meta_boxes', 'wppb_add_tabs_on_top_of_admin_email_page' );
function wppb_add_tabs_on_top_of_admin_email_page( $hookname ){
	if( $hookname == 'profile-builder_page_admin-email-customizer' ){

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
add_action( 'init', 'wppb_admin_email_customizer_add_mustache_in_backend', 11 );
/**
 * Function that ads the mustache boxes in the backend for admin email customizer
 *
 * @since v.2.0
 */
function wppb_admin_email_customizer_add_mustache_in_backend(){

	if( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' ) )
		require_once( WPPB_PAID_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' );
	elseif( file_exists( WPPB_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' ) )
		require_once( WPPB_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' );

	$fields = array(
				array(
					'id'	=> 'wppb_admin_emailc_common_settings_header', // field id and name
					'type'	=> 'header', // type of field
                    'default'	=> __( 'These settings are also replicated in the "User Email Customizer" settings-page upon save.', 'profile-builder' ).' '.__( 'Valid tags {{reply_to}} and {{site_name}}', 'profile-builder'), // type of field
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
	new PB_Mustache_Generate_Admin_Box( 'aec_common_settings', __( 'Common Settings', 'profile-builder' ), 'profile-builder_page_admin-email-customizer', 'core', '', '', $fields );


    $registration_default_email_content = __( "<p>New subscriber on {{site_name}}.</p>\n<p>Username:{{username}}</p>\n<p>Email:{{user_email}}</p>\n", 'profile-builder' );
    $mustache_vars = wppb_email_customizer_generate_merge_tags();

    $fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_admin_emailc_default_registration_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> '[{{site_name}}] '.__( 'A new subscriber has (been) registered!', 'profile-builder' ), // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_admin_emailc_default_registration_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_admin_emailc_default_registration_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $registration_default_email_content, // type of field
                )
			);

	new PB_Mustache_Generate_Admin_Box( 'aec_default_registration', __( 'Default Registration & Registration with Email Confirmation', 'profile-builder' ), 'profile-builder_page_admin-email-customizer', 'core', $mustache_vars, '', $fields);


    $registration_admin_approval_email_content = __( "<p>New subscriber on {{site_name}}.</p>\n<p>Username:{{username}}</p>\n<p>Email:{{user_email}}</p>\n<p>The Admin Approval feature was activated at the time of registration,\nso please remember that you need to approve this user before he/she can log in!</p>", 'profile-builder' );

    $mustache_vars = wppb_email_customizer_generate_merge_tags( 'admin_approval' );

    $fields = array(
				array(
					'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
					'desc'	=> '', // description
					'id'	=> 'wppb_admin_emailc_registration_with_admin_approval_email_subject', // field id and name
					'type'	=> 'text', // type of field
					'default'	=> '[{{site_name}}] A new subscriber has (been) registered!', // type of field
				),
				array(
					'label'   => __( 'Enable email', 'profile-builder' ), // <label>
					'desc'    => '', // description
					'id'      => 'wppb_admin_emailc_registration_with_admin_approval_email_enabled', // field id and name
					'type'    => 'checkbox', // type of field
					'default' => 'on',
				),
                array( // Textarea
                    'label'	=> '', // <label>
                    'desc'	=> '', // description
                    'id'	=> 'wppb_admin_emailc_registration_with_admin_approval_email_content', // field id and name
                    'type'	=> 'textarea', // type of field
                    'default'	=> $registration_admin_approval_email_content , // type of field
                )
			);

	new PB_Mustache_Generate_Admin_Box( 'aec_reg_with_admin_approval', __( 'Registration with Admin Approval', 'profile-builder' ), 'profile-builder_page_admin-email-customizer', 'core', $mustache_vars, '', $fields );


	$user_password_reset_email_content = __( "<p>{{username}} has requested a password change via the password reset feature.</p>\n", 'profile-builder' );

	$mustache_vars = wppb_email_customizer_generate_merge_tags();

	$fields = array(
		array(
			'label'	=> __( 'Email Subject', 'profile-builder' ), // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_admin_emailc_user_password_reset_email_subject', // field id and name
			'type'	=> 'text', // type of field
			'default'	=> '[{{site_name}}] Password Successfully Reset for {{username}}', // type of field
		),
		array(
			'label'   => __( 'Enable email', 'profile-builder' ), // <label>
			'desc'    => '', // description
			'id'      => 'wppb_admin_emailc_user_password_reset_email_enabled', // field id and name
			'type'    => 'checkbox', // type of field
			'default' => 'on',
		),
		array( // Textarea
			'label'	=> '', // <label>
			'desc'	=> '', // description
			'id'	=> 'wppb_admin_emailc_user_password_reset_email_content', // field id and name
			'type'	=> 'textarea', // type of field
			'default'	=> $user_password_reset_email_content , // type of field
		)
	);

	new PB_Mustache_Generate_Admin_Box( 'aec_user_password_reset', __( 'Admin Notification for User Password Reset', 'profile-builder' ), 'profile-builder_page_admin-email-customizer', 'core', $mustache_vars, '', $fields );


    /*
    * Admin Notification for Edit Profile Approved by Admin
    */

    // check if the Edit Profile Approved by Admin add-on is active to see if the email should be displayed
    if( function_exists( 'wppb_in_init_edit_profile_approval' ) ) {
		// we format the var like this for proper line breaks.
		$aec_epaa_notification = __("<p>The user {{username}} has updated their profile and some of the fields require admin approval:</p>\n<br>\n{{modified_fields}}\n<br>\n<p>Access this link to approve changes: {{approval_url}}</p>\n", 'profile-builder');
		$mustache_vars = wppb_email_customizer_generate_merge_tags('epaa_notification_admin');
		$fields = array(
			array(
				'label' => __('Email Subject', 'profile-builder'), // <label>
				'desc' => '', // description
				'id' => 'wppb_admin_emailc_epaa_notification_subject', // field id and name
				'type' => 'text', // type of field
				'default' => __('[{{site_name}}] A user has updated their profile. Some fields need approval', 'profile-builder'), // type of field
			),
			array(
				'label' => __('Enable email', 'profile-builder'), // <label>
				'desc' => '', // description
				'id' => 'wppb_admin_emailc_epaa_notification_enabled', // field id and name
				'type' => 'checkbox', // type of field
				'default' => 'on',
			),
			array( // Textarea
				'label' => '', // <label>
				'desc' => '', // description
				'id' => 'wppb_admin_emailc_epaa_notification_content', // field id and name
				'type' => 'textarea', // type of field
				'default' => $aec_epaa_notification, // type of field
			)
		);

		new PB_Mustache_Generate_Admin_Box('aec_epaa_notification', __('Admin Notification for Edit Profile Approved by Admin', 'profile-builder'), 'profile-builder_page_admin-email-customizer', 'core', $mustache_vars, '', $fields);
    }
}
