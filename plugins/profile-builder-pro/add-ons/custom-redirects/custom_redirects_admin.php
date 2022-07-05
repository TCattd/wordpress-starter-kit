<?php
/**
 * Add scripts to the back-end Custom Redirects style that page only.
 *
 * @since v.2.2.0
 *
 * @return void
 */
function wppb_print_custom_redirects_script( $hook ){

	if ( $hook == 'profile-builder_page_custom-redirects' ){
		wp_enqueue_style( 'wppb-custom-redirects-ui', WPPB_PAID_PLUGIN_URL . 'add-ons/custom-redirects/assets/wppb_custom_redirects_ui.css', false, PROFILE_BUILDER_VERSION );
	}

	if( $hook == 'profile-builder_page_custom-redirects' ){
		wp_enqueue_script( 'wppb-custom-redirects-ui', WPPB_PAID_PLUGIN_URL . 'add-ons/custom-redirects/assets/wppb_custom_redirects_ui.js', array(), PROFILE_BUILDER_VERSION, true );
	}

}
add_action( 'admin_enqueue_scripts', 'wppb_print_custom_redirects_script' );

/**
 * Function that creates the Custom Redirects 2 submenu and populates it with repeater fields
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_custom_redirects_submenu()
{
	// create a new sub_menu page which holds the data for the default + extra fields
	$args = array(
		'menu_title' => __('Custom Redirects', 'profile-builder'),
		'page_title' => __('Custom Redirects', 'profile-builder'),
		'menu_slug' => 'custom-redirects',
		'page_type' => 'submenu_page',
		'capability' => 'manage_options',
		'priority' => 5,
		'parent_slug' => 'profile-builder'
	);
	$redirects_page = new WCK_Page_Creator_PB($args);
}
add_action( 'admin_menu', 'wppb_custom_redirects_submenu', 1 );

function wppb_populate_custom_redirects_fields(){
	// set up the fields array
	// we'll re-use the same redirect types regardless
	$redirect_types = apply_filters('wppb_redirect_types', array(
		'%'.__('After Login','profile-builder').'%'.'after_login',
		'%'.__('After Logout','profile-builder').'%'.'after_logout',
		'%'.__('After Registration','profile-builder').'%'.'after_registration',
		'%'.__('After Edit Profile','profile-builder').'%'.'after_edit_profile',
		'%'.__('After Successful Email Confirmation','profile-builder').'%'.'after_success_email_confirmation',
		'%'.__('After Password Change Request','profile-builder').'%'.'after_password_change_request',
		'%'.__('After Successful Password Reset','profile-builder').'%'.'after_success_password_reset',
		'%'.__('Dashboard (redirect users from accessing the dashboard)','profile-builder').'%'.'dashboard_redirect',
	));

	$redirect_id_or_username = apply_filters('wppb_redirect_id_or_username', array(
		'%'.__('User ID','profile-builder').'%'.'userid',
		'%'.__('Username','profile-builder').'%'.'user',
	));

	// Individual User Redirects
	$fields = apply_filters( 'wppb_cr_user_fields', array(
		array( 'type' => 'radio', 'slug' => 'idoruser', 'title' => __( 'User ID or Username', 'profile-builder' ), 'default' => 'user', 'options' => $redirect_id_or_username, 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'user', 'title' => __( 'User ID / Username', 'profile-builder' ), 'description' => __( 'Please select and enter the ID or username of your user.', 'profile-builder' ), 'required' => 'Yes' ),
		array( 'type' => 'select', 'slug' => 'type', 'title' => __( 'Redirect Type', 'profile-builder' ), 'default-option' => true, 'description' => 'When do you want to redirect your user? Choose a redirect type.', 'options' => $redirect_types, 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'url', 'title' => __( 'Redirect URL', 'profile-builder' ), 'description' => __( 'Can contain the following dynamic tags:{{homeurl}}, {{siteurl}}, {{user_id}}, {{user_nicename}}, {{http_referer}}', 'profile-builder' ), 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'id', 'title' => __( 'ID', 'profile-builder' ), 'default' => 0 ),
	));

	// create the new submenu with the above options
	$args = array(
		'metabox_id' 	=> 'wppb_custom_redirects_user',
		'metabox_title' => __( 'Individual User Redirects', 'profile-builder' ) . '<span class="dashicons dashicons-admin-users"></span>',
		'post_type' 	=> 'custom-redirects',
		'meta_name' 	=> 'wppb_cr_user',
		'meta_array' 	=> $fields,
		'context'		=> 'option',
		'sortable'		=> false
	);
	new Wordpress_Creation_Kit_PB( $args );

	// User Role based Redirects
	//user roles
	global $wp_roles;

	$user_roles = array('%'. __( '... Choose', 'profile-builder' ).'%');
	foreach( $wp_roles->roles as $user_role_slug => $user_role ) {
		if( $user_role_slug !== 'administrator' ){
			array_push( $user_roles, '%' . wppb_prepare_wck_labels( $user_role['name'] ) . '%' . $user_role_slug );
		}
	}

	$fields = apply_filters( 'wppb_cr_role_fields', array(
		array( 'type' => 'select', 'slug' => 'user_role', 'title' => __( 'User Role', 'profile-builder' ), 'description' => __( 'Select a user role.', 'profile-builder' ), 'options' => $user_roles, 'required' => 'Yes' ),
		array( 'type' => 'select', 'slug' => 'type', 'title' => __( 'Redirect Type', 'profile-builder' ), 'default-option' => true, 'description' => 'When do you want to redirect your user? Choose a redirect type.', 'options' => $redirect_types, 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'url', 'title' => __( 'Redirect URL', 'profile-builder' ), 'description' => __( 'Can contain the following dynamic tags:{{homeurl}}, {{siteurl}}, {{user_id}}, {{user_nicename}}, {{http_referer}}', 'profile-builder' ), 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'id', 'title' => __( 'ID', 'profile-builder' ), 'default' => 0 ),
	));

	// create the new submenu with the above options
	$args = array(
		'metabox_id' 	=> 'wppb_custom_redirects_role',
		'metabox_title' => __( 'User Role based Redirects', 'profile-builder' ) . '<span class="dashicons dashicons-groups"></span>',
		'post_type' 	=> 'custom-redirects',
		'meta_name' 	=> 'wppb_cr_role',
		'meta_array' 	=> $fields,
		'context'		=> 'option',
		'sortable'		=> false
	);
	new Wordpress_Creation_Kit_PB( $args );

	// General Redirects. These apply to all users.
	$fields = apply_filters( 'wppb_cr_general_fields', array(
		array( 'type' => 'select', 'slug' => 'type', 'title' => __( 'Redirect Type', 'profile-builder' ), 'default-option' => true, 'description' => 'When do you want to redirect your user? Choose a redirect type.', 'options' => $redirect_types, 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'url', 'title' => __( 'Redirect URL', 'profile-builder' ), 'description' => __( 'Can contain the following dynamic tags:{{homeurl}}, {{siteurl}}, {{user_id}}, {{user_nicename}}, {{http_referer}}', 'profile-builder' ), 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'id', 'title' => __( 'ID', 'profile-builder' ), 'default' => 0 ),
	));

	// create the new submenu with the above options
	$args = array(
		'metabox_id' 	=> 'wppb_custom_redirects_global',
		'metabox_title' => __( 'Global Redirects', 'profile-builder' ) . '<span class="dashicons dashicons-admin-site"></span> ' ,
		'post_type' 	=> 'custom-redirects',
		'meta_name' 	=> 'wppb_cr_global',
		'meta_array' 	=> $fields,
		'context'		=> 'option',
		'sortable'		=> false
	);
	new Wordpress_Creation_Kit_PB( $args );

	// Redirect Default WordPress Pages.
	$fields = apply_filters( 'wppb_cr_default_wp_pages_fields', array(
		array( 'type' => 'select', 'slug' => 'type', 'title' => __( 'Redirect Type', 'profile-builder' ), 'default-option' => true, 'description' => 'When do you want to redirect your user? Choose a redirect type.', 'required' => 'Yes',
			'options' => array(
				'%'.__('Login ( wp-login.php )','profile-builder').'%'.'login',
				'%'.__('Register ( wp-login.php?action=register )','profile-builder').'%'.'register',
				'%'.__('Lost Password ( wp-login.php?action=lostpassword )','profile-builder').'%'.'lostpassword',
				'%'.__('Author Archive ( http://sitename.com/author/admin )','profile-builder').'%'.'authorarchive',
			) ),
		array( 'type' => 'text', 'slug' => 'url', 'title' => __( 'Redirect URL', 'profile-builder' ), 'description' => __( 'Can contain the following dynamic tags:{{homeurl}}, {{siteurl}}, {{user_id}}, {{user_nicename}}, {{http_referer}}', 'profile-builder' ), 'required' => 'Yes' ),
		array( 'type' => 'text', 'slug' => 'id', 'title' => __( 'ID', 'profile-builder' ), 'default' => 0 ),
	));

	// create the new submenu with the above options
	$args = array(
		'metabox_id' 	=> 'wppb_custom_redirects_default_wp_pages',
		'metabox_title' => __( 'Redirect Default WordPress Forms and Pages', 'profile-builder' ) . '<span class="dashicons dashicons-wordpress"></span> ',
		'post_type' 	=> 'custom-redirects',
		'meta_name' 	=> 'wppb_cr_default_wp_pages',
		'meta_array' 	=> $fields,
		'context'		=> 'option',
		'sortable'		=> false
	);
	new Wordpress_Creation_Kit_PB( $args );

	// create the info side meta-box
	$args = array(
		'metabox_id' 	=> 'custom-redirects-info',
		'metabox_title' => __( 'How does this work?', 'profile-builder' ),
		'post_type' 	=> 'custom-redirects',
		'meta_name' 	=> 'wppb_custom_redirects_info',
		'meta_array' 	=> '',
		'context'		=> 'option',
		'mb_context'    => 'side'
	);
	new Wordpress_Creation_Kit_PB( $args );

}
add_action( 'admin_init', 'wppb_populate_custom_redirects_fields', 1 );

/**
 * Function that modifies the table header in Custom Redirects to addField Name, Field Type, Meta Key, Required
 *
 * @since v.2.2.0
 *
 * @param $list, $id
 *
 * @return string
 */
function wppb_custom_redirects_user_header( $list_header ){
	return '<thead><tr><th class="wck-number">#</th><th class="wck-content"><pre class="idorusername">&nbsp;</pre>'. __( '<pre>User ID / Username</pre><pre>Redirect</pre><pre>URL</pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
}
add_action( 'wck_metabox_content_header_wppb_cr_user', 'wppb_custom_redirects_user_header' );

/**
 * Function that modifies the table header in Custom Redirects to addField Name, Field Type, Meta Key, Required
 *
 * @since v.2.2.0
 *
 * @param $list, $id
 *
 * @return string
 */
function wppb_custom_redirects_role_header( $list_header ){
	return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( '<pre>User Role</pre><pre>Redirect</pre><pre>URL</pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
}
add_action( 'wck_metabox_content_header_wppb_cr_role', 'wppb_custom_redirects_role_header' );

/**
 * Function that modifies the table header in Custom Redirects to addField Name, Field Type, Meta Key, Required
 *
 * @since v.2.2.0
 *
 * @param $list, $id
 *
 * @return string
 */
function wppb_custom_redirects_global_header( $list_header ){
	return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( '<pre>Redirect</pre><pre>URL</pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
}
add_action( 'wck_metabox_content_header_wppb_cr_global', 'wppb_custom_redirects_global_header' );

/**
 * Function that modifies the table header in Custom Redirects to addField Name, Field Type, Meta Key, Required
 *
 * @since v.2.2.0
 *
 * @param $list, $id
 *
 * @return string
 */
function wppb_custom_redirects_default_pages_header( $list_header ){
	return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( '<pre>Redirect</pre><pre>URL</pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
}
add_action( 'wck_metabox_content_header_wppb_cr_default_wp_pages', 'wppb_custom_redirects_default_pages_header' );

/**
 * Add contextual help to the side of custom redirects page to understand the priority
 *
 * @since v.2.2.0
 *
 * @param $hook
 *
 * @return string
 */
function wppb_add_content_before_custom_redirects_info(){
	?>
	<p><?php esc_html_e('These redirects happen after a successful action, like registration or after a successful login.', 'profile-builder'); ?></p>
	<hr/>
	<h4><?php esc_html_e("Which redirect happens depends on the following priority:", 'profile-builder'); ?></h4>
	<ol>
		<li><?php esc_html_e("Multiple Registration and Edit Profile form settings Redirects", 'profile-builder'); ?></li>
		<li><?php esc_html_e("Individual User Redirects", 'profile-builder'); ?></li>
		<li><?php esc_html_e("User Role based Redirects", 'profile-builder'); ?></li>
		<li><?php esc_html_e("Global Redirects", 'profile-builder'); ?></li>
		<li><?php echo wp_kses_post( __("Individual redirects defined in shortcodes; <strong><em>redirect_priority=\"top\"</em></strong> parameter can be added in any shortcode, then that shortcode redirect will have priority over all other redirects.", 'profile-builder') ); ?></li>
	</ol>
	<hr/>
	<h4><?php esc_html_e("Redirect Default WordPress forms and pages", 'profile-builder'); ?></h4>
	<p><?php esc_html_e("With these you can redirect various WordPress forms and pages to pages created with profile builder.", 'profile-builder'); ?></p>
	<p><?php esc_html_e( 'You can force access to wp-login.php so you don\'t get locked out of the site by accessing the link:', 'profile-builder' ); ?> <a href="<?php echo esc_url( wp_login_url() ).'?wppb_force_wp_login=true' ?>"><?php echo esc_url( wp_login_url() ).'?wppb_force_wp_login=true' ?></a></p>
	<hr/>
	<h4><?php esc_html_e("Available tags for dynamic URLs", 'profile-builder'); ?></h4>
	<p><?php esc_html_e("You use the following tags in your URLs to redirect users to various pages.", 'profile-builder'); ?></p>
	<ol>
		<li><strong>{{homeurl}}</strong> - <?php esc_html_e("generates a url of the current website homepage.", 'profile-builder'); ?></li>
		<li><strong>{{siteurl}}</strong> - <?php echo wp_kses_post( __("in WordPress the <a target='_blank' href='https://codex.wordpress.org/Function_Reference/site_url'>site url</a> can be different then the home url", 'profile-builder') ); ?></li>
		<li><strong>{{user_id}}</strong> - <?php esc_html_e("the ID of the user", 'profile-builder'); ?></li>
		<li><strong>{{user_nicename}}</strong> - <?php esc_html_e("the URL sanitized version of the username, the user nicename can be safely used in URLs since it can't contain special characters or spaces.", 'profile-builder'); ?></li>
		<li><strong>{{http_referer}}</strong> - <?php esc_html_e("the URL of the previously visited page", 'profile-builder'); ?></li>
        <li><strong>{{redirect_to}}</strong> - <?php esc_html_e("the default URL set by WordPress after login", 'profile-builder'); ?></li>
	</ol>

<?php
}
add_action('wck_metabox_content_wppb_custom_redirects_info', 'wppb_add_content_before_custom_redirects_info');


/* the function that adds the jQuery script after form actions */
function wppb_add_script_after_form_actions() {
	echo '<script type="text/javascript">wppb_custom_redirects_user_radio();</script>';
}
add_action( 'wck_ajax_add_form_wppb_cr_user', 'wppb_add_script_after_form_actions' );
add_action( 'wck_after_adding_form_wppb_cr_user', 'wppb_add_script_after_form_actions' );
add_action( 'wck_refresh_list_wppb_cr_user', 'wppb_add_script_after_form_actions' );
add_action( 'wck_refresh_entry_wppb_cr_user', 'wppb_add_script_after_form_actions' );


/* the function needed to check Individual User Redirects duplicate entries */
function wppb_cr_user_check_duplicate_entries( $values ) {
	if( empty( $values['id'] ) || $values['id'] == 0 ) {
		$values['id'] = 1;
	}

	return $values;
}
add_action( 'wck_update_meta_filter_values_wppb_cr_user', 'wppb_cr_user_check_duplicate_entries' );

/* the function needed to check User Role based Redirects duplicate entries */
function wppb_cr_role_check_duplicate_entries( $values ) {
	if( empty( $values['id'] ) || $values['id'] == 0 ) {
		$values['id'] = 1;
	}

	return $values;
}
add_action( 'wck_update_meta_filter_values_wppb_cr_role', 'wppb_cr_role_check_duplicate_entries' );

/* the function needed to check Global Redirects duplicate entries */
function wppb_cr_global_check_duplicate_entries( $values ) {
	if( empty( $values['id'] ) || $values['id'] == 0 ) {
		$values['id'] = 1;
	}

	return $values;
}
add_action( 'wck_update_meta_filter_values_wppb_cr_global', 'wppb_cr_global_check_duplicate_entries' );

/* the function needed to check Default WordPress Forms and Pages Redirect duplicate entries */
function wppb_cr_default_wp_pages_check_duplicate_entries( $values ) {
	if( empty( $values['id'] ) || $values['id'] == 0 ) {
		$values['id'] = 1;
	}

	return $values;
}
add_action( 'wck_update_meta_filter_values_wppb_cr_default_wp_pages', 'wppb_cr_default_wp_pages_check_duplicate_entries' );


/**
 * Function that checks several things when adding/editing the Custom Redirects fields
 *
 * @param	string		$message			- the message to be displayed
 * @param	array		$fields				- the added fields
 * @param	array		$required_fields	- the required fields
 * @param	string		$meta_name			- the meta-name of the option
 * @param	string		$pv					- the values entered for each option
 * @param	integer		$post_id			- the post id
 *
 * @return	boolean
 */
function wppb_cr_check_field_on_edit_add( $message, $fields, $required_fields, $meta_name, $pv, $post_id ) {
	if( $meta_name == 'wppb_cr_user' || $meta_name == 'wppb_cr_role' || $meta_name == 'wppb_cr_global' || $meta_name == 'wppb_cr_default_wp_pages' ) {
		$$meta_name = get_option( $meta_name, 'not_found' );

		if ( $$meta_name != 'not_found' ) {
			foreach( $$meta_name as $opt ) {
				if( empty( $pv['id'] ) || $pv['id'] == 0 ) {
					if( ( ! empty( $pv['user'] ) && ! empty( $pv['type'] ) && ! empty( $pv['idoruser'] ) && $pv['user'] == $opt['user'] && $pv['type'] == $opt['type'] && $pv['idoruser'] == $opt['idoruser'] )
						|| ( ! empty( $pv['user_role'] ) && ! empty( $pv['type'] ) && $pv['user_role'] == $opt['user_role'] && $pv['type'] == $opt['type'] )
						|| ( ! empty( $pv['type'] ) && $meta_name == 'wppb_cr_global' && $pv['type'] == $opt['type'] )
						|| ( ! empty( $pv['type'] ) && $meta_name == 'wppb_cr_default_wp_pages' && $pv['type'] == $opt['type'] ) ) {

						$message = "\n" . __( "You can't add duplicate redirects!", 'profile-builder' ) . "\n";
					} elseif( ! empty( $pv['user'] ) && ! empty( $pv['type'] ) && ! empty( $pv['idoruser'] ) ) {
						if( $pv['idoruser'] == 'user' ) {
							$wppb_cr_userdata = get_user_by( 'login', $pv['user'] );

							if( isset( $wppb_cr_userdata ) && $wppb_cr_userdata->ID == $opt['user'] && $pv['type'] == $opt['type'] && $opt['idoruser'] == 'userid' ) {
								$message = "\n" . __( "You can't add duplicate redirects!", 'profile-builder' ) . "\n";
							}
						} elseif( $pv['idoruser'] == 'userid' ) {
							$wppb_cr_userdata = get_user_by( 'id', $pv['user'] );

							if( isset( $wppb_cr_userdata ) && $wppb_cr_userdata->user_login == $opt['user'] && $pv['type'] == $opt['type'] && $opt['idoruser'] == 'user' ) {
								$message = "\n" . __( "You can't add duplicate redirects!", 'profile-builder' ) . "\n";
							}
						}
					}
				}
			}
		}
	}

	return $message;
}
add_filter( 'wck_extra_message', 'wppb_cr_check_field_on_edit_add', 10, 6 );