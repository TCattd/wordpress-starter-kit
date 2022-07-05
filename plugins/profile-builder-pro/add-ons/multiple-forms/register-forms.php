<?php
/**
 * Function that creates the "Registration Forms" post type
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_create_registration_forms_cpt(){
    $labels = array(
        'name' 					=> __( 'Registration Form', 'profile-builder'),
        'singular_name' 		=> __( 'Registration Form', 'profile-builder'),
        'add_new' 				=> __( 'Add New', 'profile-builder' ),
        'add_new_item' 			=> __( 'Add new Registration Form', 'profile-builder' ),
        'edit_item' 			=> __( 'Edit the Registration Forms', 'profile-builder' ) ,
        'new_item' 				=> __( 'New Registration Form', 'profile-builder' ),
        'all_items' 			=> __( 'Registration Forms', 'profile-builder' ),
        'view_item' 			=> __( 'View the Registration Form', 'profile-builder' ),
        'search_items' 			=> __( 'Search the Registration Forms', 'profile-builder' ),
        'not_found' 			=> __( 'No Registration Form found', 'profile-builder' ),
        'not_found_in_trash' 	=> __( 'No Registration Forms found in trash', 'profile-builder' ),
        'parent_item_colon' 	=> '',
        'menu_name' 			=> __( 'Registration Forms', 'profile-builder' )
    );

    $args = array(
        'labels' 				=> $labels,
        'public' 				=> false,
        'publicly_queryable' 	=> false,
        'show_ui' 				=> true,
        'query_var'          	=> true,
        'show_in_menu' 			=> 'profile-builder',
        'has_archive' 			=> false,
        'hierarchical' 			=> false,
        'capability_type' 		=> 'post',
        'supports' 				=> array( 'title' )
    );

	/* hide from admin bar for non administrators */
	if( !current_user_can( 'manage_options' ) )
		$args['show_in_admin_bar'] = false;

    $wppb_addonOptions = get_option('wppb_module_settings');
    if( !empty( $wppb_addonOptions['wppb_multipleRegistrationForms'] ) && $wppb_addonOptions['wppb_multipleRegistrationForms'] == 'show' )
        register_post_type( 'wppb-rf-cpt', $args );
}
add_action( 'init', 'wppb_create_registration_forms_cpt', 9);

/* Register Form change classes based on Redirect field start */
add_filter( 'wck_add_form_class_wppb_rf_page_settings', 'wppb_register_add_form_change_class_based_on_redirect_field', 10, 3 );
function wppb_register_add_form_change_class_based_on_redirect_field($wck_update_container_css_class, $meta, $results ) {
    if( !empty( $results ) ){
        $redirect = Wordpress_Creation_Kit_PB::wck_generate_slug( $results[0]["redirect"] );
        return "update_container_$meta update_container_$redirect redirect_$redirect";
    }
}
/* Register Form change classes based on Redirect field end */

/**
 * Remove certain actions from post list view
 *
 * @since v.2.0
 *
 * @param array $actions
 *
 * return array
 */
function wppb_remove_rf_view_link( $actions ){
	global $post;

	if ( $post->post_type == 'wppb-rf-cpt' ){
		unset( $actions['view'] );

		if ( wppb_get_post_number ( $post->post_type, 'singular_action' ) )
			unset( $actions['trash'] );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'wppb_remove_rf_view_link', 10, 1 );


/**
 * Remove certain bulk actions from post list view
 *
 * @since v.2.0
 *
 * @param array $actions
 *
 * return array
 */
function wppb_remove_trash_bulk_option_rf( $actions ){
	global $post;
	if( !empty( $post ) ){
        if ( $post->post_type == 'wppb-rf-cpt' ){
            unset( $actions['view'] );

            if ( wppb_get_post_number ( $post->post_type, 'bulk_action' ) )
                unset( $actions['trash'] );
        }
    }

	return $actions;
}
add_filter( 'bulk_actions-edit-wppb-rf-cpt', 'wppb_remove_trash_bulk_option_rf' );


/**
 * Function to hide certain publishing options
 *
 * @since v.2.0
 *
 */
function wppb_hide_rf_publishing_actions(){
	global $post;

	if ( $post->post_type == 'wppb-rf-cpt' ){
		echo '<style type="text/css">#misc-publishing-actions, #minor-publishing-actions{display:none;}</style>';

		$rf = get_posts( array( 'posts_per_page' => -1, 'post_status' => apply_filters ( 'wppb_check_singular_rf_form_publishing_options', array( 'publish' ) ) , 'post_type' => 'wppb-rf-cpt' ) );
		if ( count( $rf ) == 1 )
			echo '<style type="text/css">#major-publishing-actions #delete-action{display:none;}</style>';
	}
}
add_action('admin_head-post.php', 'wppb_hide_rf_publishing_actions');
add_action('admin_head-post-new.php', 'wppb_hide_rf_publishing_actions');


/**
 * Add custom columns to listing
 *
 * @since v.2.0
 *
 * @param array $columns
 * @return array $columns
 */
function wppb_add_extra_column_for_rf( $columns ){
	$columns['rf-shortcode'] = __( 'Shortcode', 'profile-builder' );

	return $columns;
}
add_filter( 'manage_wppb-rf-cpt_posts_columns', 'wppb_add_extra_column_for_rf' );


/**
 * Add content to the displayed column
 *
 * @since v.2.0
 *
 * @param string $column_name
 * @param integer $post_id
 * @return void
 */
function wppb_rf_custom_column_content( $column_name, $post_id ){
	if( $column_name == 'rf-shortcode' ){
		$post = get_post( $post_id );

		if( empty( $post->post_title ) )
			$post->post_title = __( '(no title)', 'profile-builder' );

        echo "<input readonly spellcheck='false' type='text' class='wppb-shortcode input' value='[wppb-register form_name=\"" . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) ) . "\"]' />";
	}
}
add_action("manage_wppb-rf-cpt_posts_custom_column",  "wppb_rf_custom_column_content", 10, 2);

/**
 * Add side metaboxes
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_rf_content(){
	global $post;

    $form_shortcode = trim( Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) );
    if ( $form_shortcode == '' ) {
        echo '<p><em>' . esc_html__( 'The shortcode will be available after you publish this form.', 'profile-builder' ) . '</em></p>';
    } else {
        echo '<p>' . esc_html__( 'Use this shortcode on the page you want the form to be displayed:', 'profile-builder' );
        echo '<br/>';
        echo "<textarea readonly spellcheck='false' class='wppb-shortcode textarea'>[wppb-register form_name=\"" . esc_attr( $form_shortcode ) . "\"]</textarea>";
        echo '</p><p>';
        echo wp_kses_post( __( '<span style="color:red;">Note:</span> changing the form title also changes the shortcode!', 'profile-builder' ) );
        echo '</p>';
    }
}

function wppb_rf_side_box(){
	add_meta_box( 'wppb-rf-side', __( 'Form Shortcode', 'profile-builder' ), 'wppb_rf_content', 'wppb-rf-cpt', 'side', 'low' );
}
add_action( 'add_meta_boxes', 'wppb_rf_side_box' );

/**
 * Function that manages the Register CPT
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_manage_rf_cpt(){
	global $wp_roles;

	$available_roles = $available_time = array();

	foreach ( $wp_roles->roles as $slug => $role )
		$available_roles[$slug] = '%'. wppb_prepare_wck_labels( $role['name'] ).'%'.$slug;

    /* put the roles subscriber contributor author editor administrator in this order at the start of the options */
    $desired_order_reversed = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
    foreach( $desired_order_reversed as $key ){
        if( !empty( $available_roles[$key] ) ){
            $value = $available_roles[$key];
            unset( $available_roles[$key] );
            array_unshift( $available_roles , $value );
        }
    }
    /* add the default role at the start of the options */
    array_unshift( $available_roles , '%'.__( 'Default Role', 'profile-builder' ).'%'.'default role' );



	for( $i=0; $i<=250; $i++ )
		$available_time[] = $i;

	// set up the fields array
	$settings_fields = array(
		array( 'type' => 'select', 'slug' => 'set-role', 'title' => __( 'Set Role', 'profile-builder' ), 'options' => $available_roles, 'description' => __( 'Choose what role the user will have after (s)he registered<br/>If not specified, defaults to the role set in the WordPress settings', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'automatically-log-in', 'title' => __( 'Automatically Log In', 'profile-builder' ), 'options' => array( '%'.__('No', 'profile-builder').'%No', '%'.__('Yes', 'profile-builder').'%Yes' ), 'default' => 'No', 'description' => __( 'Whether to automatically log in the newly registered user or not<br/>Only works on single-sites without "Admin Approval" feature activated<br/>WARNING: Caching the registration form will make automatic login not work', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'redirect', 'title' => __( 'Redirect', 'profile-builder' ), 'options' => array( '%'.__('Default', 'profile-builder').'%-', '%'.__('No', 'profile-builder').'%No', '%'.__('Yes', 'profile-builder').'%Yes' ), 'default' => '-', 'description' => __( 'Whether to redirect the user to a specific page or not', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'display-messages', 'title' => __( 'Display Messages', 'profile-builder' ), 'options' => $available_time, 'default' => 1, 'description' => __( 'Allowed time to display any success messages (in seconds)', 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'url', 'title' => __( 'URL', 'profile-builder' ), 'description' => __( 'Specify the URL of the page users will be redirected once registered using this form<br/>Use the following format: http://www.mysite.com', 'profile-builder' ) ),
	);

	// set up the box arguments
	$args = array(
		'metabox_id' => 'wppb-rf-settings-args',
		'metabox_title' => __( 'After Registration...', 'profile-builder' ),
		'post_type' => 'wppb-rf-cpt',
		'meta_name' => 'wppb_rf_page_settings',
		'meta_array' => $settings_fields,
		'sortable' => false,
		'single' => true
	);
	new Wordpress_Creation_Kit_PB( $args );

	$rf_fields = array ();

	$all_fields = get_option ( 'wppb_manage_fields', 'not_set' );
	if ( ( $all_fields != 'not_set' ) && ( ( is_array( $all_fields ) ) && ( !empty( $all_fields ) ) ) ){
		foreach ( $all_fields as $key => $value ) {
            if (  $value['field'] != 'Default - Display name publicly as' )
                array_push( $rf_fields, wppb_field_format( $value['field-title'], $value['field'] ) );
        }
	}

	$rf_fields = apply_filters( 'wppb_rf_fields_types', $rf_fields );

	// set up the box arguments for the register forms and create them
	$args = array(
		'metabox_id' => 'wppb-rf-fields',
		'metabox_title' => __( 'Add New Field to the List', 'profile-builder' ),
		'post_type' => 'wppb-rf-cpt',
		'meta_name' => 'wppb_rf_fields',
		'meta_array' => $rf_fields = apply_filters	( 'wppb_rf_fields', array(
																				array( 'type' => 'select', 'slug' => 'field', 'title' => __( 'Field', 'profile-builder' ), 'options' => $rf_fields, 'default-option' => true, 'description' => sprintf( __( 'Choose one of the supported fields you manage <a href="%s">here</a>', 'profile-builder' ), admin_url( 'admin.php?page=manage-fields' ) ) ),
																				array( 'type' => 'text', 'slug' => 'id', 'title' => __( 'ID', 'profile-builder' ), 'default' =>  '', 'description' => __( "A unique, auto-generated ID for this particular field<br/>You can use this in conjuction with filters to target this element if needed<br/>Can't be edited", 'profile-builder' ), 'readonly' => true )
																		)
													)
	);
	new Wordpress_Creation_Kit_PB( $args );


}
add_action( 'admin_init', 'wppb_manage_rf_cpt', 1 );


add_filter( "wck_before_listed_wppb_rf_fields_element_0", 'wppb_manage_fields_display_field_title_slug', 10, 3 );
add_filter( 'wck_update_container_class_wppb_rf_fields', 'wppb_update_container_class', 10, 4 );
add_filter( 'wck_element_class_wppb_rf_fields', 'wppb_element_class', 10, 4 );


/**
 * Function that calls a js function to hide the delete buttons on username, email and password fields even after reordering/refreshing list
 *
 * @since v.2.0
 *
 * @param void
 *
 * @return string
 */
function wppb_rf_after_refresh_list( $id ){
    echo "<script type=\"text/javascript\">wppb_disable_delete_on_default_mandatory_fields();</script>";
}
add_action( "wck_refresh_list_wppb_rf_fields", "wppb_rf_after_refresh_list" );