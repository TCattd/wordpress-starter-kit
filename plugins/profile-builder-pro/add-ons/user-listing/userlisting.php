<?php
/**
 * Function that creates the "Userlisting" custom post type
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_create_userlisting_forms_cpt(){
    $labels = array(
        'name' 					=> __( 'User Listing', 'profile-builder'),
        'singular_name' 		=> __( 'User Listing', 'profile-builder'),
        'add_new' 				=> __( 'Add New', 'profile-builder'),
        'add_new_item' 			=> __( 'Add new User Listing', 'profile-builder' ),
        'edit_item' 			=> __( 'Edit the User Listing', 'profile-builder' ) ,
        'new_item' 				=> __( 'New User Listing', 'profile-builder' ),
        'all_items' 			=> __( 'User Listing', 'profile-builder' ),
        'view_item' 			=> __( 'View the User Listing', 'profile-builder' ),
        'search_items' 			=> __( 'Search the User Listing', 'profile-builder' ),
        'not_found' 			=> __( 'No User Listing found', 'profile-builder' ),
        'not_found_in_trash' 	=> __( 'No User Listing found in trash', 'profile-builder' ),
        'parent_item_colon' 	=> '',
        'menu_name' 			=> __( 'User Listing', 'profile-builder' )
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
    if( $wppb_addonOptions && isset( $wppb_addonOptions[ 'wppb_userListing' ] ) && $wppb_addonOptions['wppb_userListing'] == 'show' )
        register_post_type( 'wppb-ul-cpt', $args );
}
add_action( 'init', 'wppb_create_userlisting_forms_cpt');

/* Userlisting change classes based on Visible only to logged in users field start */
add_filter( 'wck_add_form_class_wppb_ul_page_settings', 'wppb_userlisting_add_form_change_class_based_on_visible_field', 10, 3 );
function wppb_userlisting_add_form_change_class_based_on_visible_field( $wck_update_container_css_class, $meta, $results ){
    if( !empty( $results ) ){
        if (!empty($results[0]["visible-only-to-logged-in-users"]))
            $votliu_val = $results[0]["visible-only-to-logged-in-users"];
        else
            $votliu_val = '';
        $votliu = Wordpress_Creation_Kit_PB::wck_generate_slug($votliu_val);
        return "update_container_$meta update_container_$votliu visible_to_logged_$votliu";
    }
}
/* Userlisting change classes based on Visible only to logged in users field end */


function wppb_userlisting_scripts() {
    global $wppb_userlisting_shortcode;
    if( isset( $wppb_userlisting_shortcode ) && $wppb_userlisting_shortcode === true ){
        wp_enqueue_script('wppb-userlisting-js', WPPB_PAID_PLUGIN_URL . 'add-ons/user-listing/userlisting.js', array('jquery', 'jquery-touch-punch'), PROFILE_BUILDER_VERSION, true);
        wp_localize_script( 'wppb-userlisting-js', 'wppb_userlisting_obj', array( 'pageSlug' => wppb_get_users_pagination_slug() ) );
        wp_enqueue_style('wppb-ul-slider-css', WPPB_PAID_PLUGIN_URL . 'add-ons/user-listing/jquery-ui-slider.min.css', array(), PROFILE_BUILDER_VERSION );
        wp_enqueue_script('jquery-ui-slider');
    }
}
add_action( 'wp_footer', 'wppb_userlisting_scripts' );

/**
 * Function that generates the merge tags for userlisting
 *
 * @since v.2.0
 *
 * @param string $type The type of merge tags which we want to generate. It can be meta or sort, meaning the actual data or the links with which we can sort the data
 * @return array $merge_tags the array of merge tags and their details
 */
function wppb_generate_userlisting_merge_tags( $type, $template = '' ){
    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    $template_tags = array();
    if ( !empty($template) ){
        preg_match_all("/{{[^{}]+}}/", $template, $template_tags );
        foreach( $template_tags[0] as $key => $value){
            $template_tags[0][$key] = trim( $value, " {}/#&?^!>");
        }
    }

    $wppb_manage_fields = apply_filters('wppb_userlisting_merge_tags' , $wppb_manage_fields, $type);
	$merge_tags = array();

	if( $type == 'meta' ){
		$default_field_type = 'default_user_field';
		$user_meta = 'user_meta';
		$user_id = 'user_id';
		$number_of_posts = 'number_of_posts';
	}
	else if( $type == 'sort' ){
		$default_field_type = $user_meta = $user_id = $number_of_posts = 'sort_tag';
	}

	if ( $wppb_manage_fields != 'not_found' )
		foreach( $wppb_manage_fields as $key => $value ){
			if ( ( $value['field'] == 'Default - Name (Heading)' ) || ( $value['field'] == 'Default - Contact Info (Heading)' ) || ( $value['field'] == 'Default - About Yourself (Heading)' ) || ( $value['field'] == 'Heading' ) || ( $value['field'] == 'Default - Password' ) || ( $value['field'] == 'Default - Repeat Password' ) || ( $value['field'] == 'Select (User Role)' ) ){
				//do nothing for the headers and the password fields

			}elseif ( $value['field'] == 'Default - Username' )
				$merge_tags[] = array( 'name' => $type.'_user_name', 'type' => $default_field_type, 'label' => __( 'Username', 'profile-builder' ) );

			elseif ( $value['field'] == 'Default - Display name publicly as' )
				$merge_tags[] = array( 'name' => $type.'_display_name', 'type' => $default_field_type, 'label' => __( 'Display name as', 'profile-builder' ) );

			elseif ( $value['field'] == 'Default - E-mail' )
				$merge_tags[] = array( 'name' => $type.'_email', 'type' => $default_field_type, 'label' => __( 'Email', 'profile-builder' ) );

			elseif ( $value['field'] == 'Default - Website' )
				$merge_tags[] = array( 'name' => $type.'_website', 'type' => $default_field_type, 'label' => __( 'Website', 'profile-builder' ) );

            elseif ( $value['field'] == 'Default - Biographical Info' )
                $merge_tags[] = array( 'name' => $type.'_biographical_info', 'type' => $default_field_type, 'unescaped' => true, 'label' => __( 'Biographical Info', 'profile-builder' ) );

            elseif ( ( $value['field'] == 'Default - Blog Details' ) ) {
                if ( $type == 'meta' ) {
                    $merge_tags[] = array('name' => $type . '_blog_url', 'type' => $default_field_type, 'label' => __('Blog URL', 'profile-builder'));
                }
            }

            elseif ( $value['field'] == 'Upload' ){
				$merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
                if ( $type == 'meta' ) {
                    $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'].'_id', 'type' => 'user_meta_custom_upload_id', 'label' => $value['field-title'] . ' ID' );
                }
			}
            elseif ( $value['field'] == 'Textarea' ){
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'unescaped' => true, 'label' => $value['field-title'] );
            }
            elseif ( $value['field'] == 'WYSIWYG' ){
                if( $user_meta == 'user_meta' )
                    $wysiwyg_user_meta = 'user_meta_wysiwyg';
                else
                    $wysiwyg_user_meta = $user_meta;

                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $wysiwyg_user_meta, 'unescaped' => true, 'label' => $value['field-title'] );
            }
            elseif( ( $value['field'] == 'Checkbox' || $value['field'] == 'Radio' || $value['field'] == 'Select' || $value['field'] == 'Select (Multiple)' || $value['field'] == 'Select2' || $value['field'] == 'Select2 (Multiple)' ) && ( $type == 'meta' ) ){
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'].'_labels', 'type' => $user_meta.'_labels', 'label' => $value['field-title']. ' Labels' );
            }
            elseif( ( $value['field'] == 'Select (CPT)' ) && ( $type == 'meta' ) ){
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'].'_cpt_title_link', 'type' => 'user_meta_select_cpt', 'unescaped' => true, 'label' => $value['field-title'] );
            }
            elseif( $value['field'] == 'Map' ) {
                if( $type == 'meta' )
                    $merge_tags[] = array( 'name' => $type . '_' . $value['meta-name'], 'type' => $user_meta . '_map', 'unescaped' => true, 'label' => $value['field-title'] );
            }
            else
				$merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
		}



    $merge_tags[] = array( 'name' => $type.'_user_id', 'type' => $user_id, 'label' => __( 'User ID', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => $type.'_role', 'type' => $default_field_type, 'label' => __( 'Role', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => $type.'_role_slug', 'type' => $default_field_type, 'label' => __( 'Role Slug', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => $type.'_registration_date', 'type' => $default_field_type, 'label' => __( 'Registration Date', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => $type.'_number_of_posts', 'type' => $number_of_posts, 'unescaped' => true, 'label' => __( 'Number of Posts', 'profile-builder' ) );

	// we can't sort by these fields so only generate the meta tags
	if( $type == 'meta' ){
		$merge_tags[] = array( 'name' => 'more_info', 'type' => 'more_info', 'unescaped' => true, 'label' => __( 'More Info', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'more_info_url', 'type' => 'more_info_url', 'unescaped' => true, 'label' => __( 'More Info Url', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'avatar_or_gravatar', 'type' => 'avatar_or_gravatar', 'unescaped' => true, 'label' => __( 'Avatar or Gravatar', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'user_nicename', 'type' => 'user_nicename', 'unescaped' => true, 'label' => __( 'User Nicename', 'profile-builder' ) );
	}

	// for sort tags add unescaped true
	if( !empty( $merge_tags ) ){
		foreach( $merge_tags as $key => $merge_tag ){
			if( $merge_tag['type'] == 'sort_tag' )
				$merge_tags[$key]['unescaped'] = true;
		}
	}

    $merge_tags = apply_filters( 'wppb_userlisting_get_merge_tags', $merge_tags, $type );

	// return only the merge tags that are found inside the template
    if (!empty( $merge_tags ) && !empty( $template )){
        $merge_tags_based_on_template = array();
        foreach ( $merge_tags as $key => $merge_tag ) {
            if ( in_array($merge_tag['name'], $template_tags[0]) ) {
                $merge_tags_based_on_template[] = $merge_tag;
            }
        }
        return $merge_tags_based_on_template;
    }

    return $merge_tags;
}

/**
 * Function that generates the variable array that we give to mustache classes for the multiple user listing
 *
 * @since v.2.0
 *
 * @return array $mustache_vars the array of variable groups and their details
 */
function wppb_generate_mustache_array_for_user_list($userlisting_template = ''){


	$meta_tags = wppb_generate_userlisting_merge_tags( 'meta', $userlisting_template );
	$sort_tags = wppb_generate_userlisting_merge_tags( 'sort', $userlisting_template );

	$extra_tags = apply_filters( 'wppb_ul_extra_functions',
        array(
            array( 'name' => 'pagination', 'type' => 'pagination', 'unescaped' => true, 'label' => __( 'Pagination', 'profile-builder' ) ),
            array( 'name' => 'extra_search_all_fields', 'type' => 'extra_search_all_fields', 'unescaped' => true, 'label' => __( 'Search all Fields', 'profile-builder' ) ),
            array( 'name' => 'faceted_menus', 'type' => 'faceted_menus', 'unescaped' => true, 'label' => __( 'Faceted Menus', 'profile-builder' ) ),
            array( 'name' => 'user_count', 'type' => 'user_count', 'unescaped' => true, 'label' => __( 'User Count', 'profile-builder' ) ),

            // Added the new extra function definition for listing users on one map.
            array(
                'name'      => 'users_one_map',
                'type'      => 'users_one_map',
                'unescaped' => true,
                'label'     => __( 'Map of listed users', 'profile-builder' ),
            ),
        )
    );

    //remove Extra Functions tags that are not found in the current template
    if( !empty($userlisting_template) ){
        $template_tags = array();
        preg_match_all("/{{[^{}]+}}/", $userlisting_template, $template_tags );
        foreach( $template_tags[0] as $key => $value){
            $template_tags[0][$key] = trim( $value, " {}/#&?^!>");
        }
        foreach( $extra_tags as $key => $extra_tag ){
            if( !in_array($extra_tag['name'], $template_tags[0]) ){
                unset( $extra_tags[$key] );
            }
        }
    }

	$mustache_vars = array(
						array(
							'group-title' => __( 'User Fields Tags', 'profile-builder' ),
							'variables' => array(
												array( 'name' => 'users', 'type' => 'loop_tag', 'children' => $meta_tags  ),
											)
						),
						array(
							'group-title' => __( 'Sort Tags', 'profile-builder' ),
							'variables' => $sort_tags
						),
                        array(
                            'group-title' => __( 'Extra Functions', 'profile-builder' ),
                            'variables' => $extra_tags
                        )
					);

	return $mustache_vars;
}

/**
 * Function that generates the variable array that we give to mustache classes for the single user listing
 *
 * @since v.2.0
 *
 * @return array $mustache_vars the array of variable groups and theyr details
 */
function wppb_generate_mustache_array_for_single_user_list(){
	$meta_tags = wppb_generate_userlisting_merge_tags( 'meta' );

	$mustache_vars = array(
						array(
							'group-title' => 'User Fields Tags',
							'variables' => $meta_tags
						),
						array(
							'group-title' => __('Extra Functions', 'profile-builder'),
							'variables' => array(
												array( 'name' => 'extra_go_back_link', 'type' => 'go_back_link', 'unescaped' => true, 'label' => __( 'Go Back Link', 'profile-builder' ) ),
											)
						)
					);
	return $mustache_vars;
}



/**
 * Function that ads the mustache boxes in the backend for userlisting
 *
 * @since v.2.0
 */
function wppb_userlisting_add_mustache_in_backend(){
	if( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists( WPPB_PAID_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' ) )
		require_once( WPPB_PAID_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' );
	elseif( file_exists( WPPB_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' ) )
		require_once( WPPB_PLUGIN_DIR . '/assets/lib/class-mustache-templates/class-mustache-templates.php' );

	// initiate box for multiple users listing
	new PB_Mustache_Generate_Admin_Box( 'wppb-ul-templates', __( 'All-userlisting Template', 'profile-builder' ), 'wppb-ul-cpt', 'core', wppb_generate_mustache_array_for_user_list(), wppb_generate_allUserlisting_content() );

	// initiate box for single user listing
	new PB_Mustache_Generate_Admin_Box( 'wppb-single-ul-templates', __( 'Single-userlisting Template', 'profile-builder' ), 'wppb-ul-cpt', 'core', wppb_generate_mustache_array_for_single_user_list(), wppb_generate_singleUserlisting_content() );
}
add_action( 'init', 'wppb_userlisting_add_mustache_in_backend' );

/**
 * Function that generates the default template for all user listing
 *
 * @since v.2.0
 *
 */
function wppb_generate_allUserlisting_content(){
return '
<table class="wppb-table">
	<thead>
		<tr>
		  <th scope="col" colspan="2" class="wppb-sorting">{{{sort_user_name}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_first_name}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_role}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_number_of_posts}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_registration_date}}}</th>
		  <th scope="col">More</th>
		</tr>
	</thead>
	<tbody>
		{{#users}}
		<tr>
		  <td data-label="' . __( 'Avatar', 'profile-builder' ) . '" class="wppb-avatar">{{{avatar_or_gravatar}}}</td>
		  <td data-label="' . __( 'Username', 'profile-builder' ) . '" class="wppb-login">{{meta_user_name}}</td>
		  <td data-label="' . __( 'Firstname', 'profile-builder' ) . '" class="wppb-name">{{meta_first_name}} {{meta_last_name}}</td>
		  <td data-label="' . __( 'Role', 'profile-builder' ) . '" class="wppb-role">{{meta_role}}</td>
		  <td data-label="' . __( 'Posts', 'profile-builder' ) . '" class="wppb-posts">{{{meta_number_of_posts}}}</td>
		  <td data-label="' . __( 'Sign-up Date', 'profile-builder' ) . '" class="wppb-signup">{{meta_registration_date}}</td>
		  <td data-label="' . __( 'More', 'profile-builder' ) . '" class="wppb-moreinfo">{{{more_info}}}</td>
		</tr>
		{{/users}}
	</tbody>
</table>
{{{pagination}}}';
}

/**
 * Function that generates the default template for single user listing
 *
 * @since v.2.0
 *
 */
function wppb_generate_singleUserlisting_content(){
	return '
{{{extra_go_back_link}}}
<ul class="wppb-profile">
  <li>
    <h3>Name</h3>
  </li>
  <li class="wppb-avatar">
    {{{avatar_or_gravatar}}}
  </li>
  <li>
    <label>Username:</label>
    <span>{{meta_user_name}}</span>
  </li>
  <li>
    <label>First Name:</label>
    <span>{{meta_first_name}}</span>
  </li>
  <li>
    <label>Last Name:</label>
    <span>{{meta_last_name}}</span>
  </li>
  <li>
    <label>Nickname:</label>
    <span>{{meta_nickname}}</span>
  </li>
  <li>
    <label>Display name:</label>
	<span>{{meta_display_name}}</span>
  </li>
  <li>
    <h3>Contact Info</h3>
  </li>
  <li>
  	<label>Website:</label>
	<span>{{meta_website}}</span>
  </li>
  <li>
    <h3>About Yourself</h3>
  </li>
  <li>
	<label>Biographical Info:</label>
	<span>{{{meta_biographical_info}}}</span>
  </li>
</ul>
{{{extra_go_back_link}}}';
}


/**
 * Function that handles the userlisting shortcode
 *
 * @since v.2.0
 *
 * @param array $atts the shortcode attributs
 * @return the shortcode output
 */
function wppb_user_listing_shortcode( $atts ){
	global $roles;
    global $wppb_userlisting_shortcode;
    global $wppb_single_userlisting_loaded;
    $wppb_userlisting_shortcode = true;

	//get value set in the shortcode as parameter, default to "public" if not set
	extract( shortcode_atts( array('meta_key' => '', 'meta_value' => '', 'name' => 'userlisting', 'include' => '', 'exclude' => '', 'single' => false, 'id' => '' ), $atts, 'wppb-list-users' ) );

    // so we can have [wppb-list-users single] without a value for single. Also works with value for single.
    if( !empty($atts) ) {
        foreach ($atts as $key => $value) {
            if ($value === 'single' && is_int($key)) $single = true;
        }
    }

	$userlisting_posts = get_posts( array( 'posts_per_page' => -1, 'post_status' =>'publish', 'post_type' => 'wppb-ul-cpt', 'orderby' => 'post_date', 'order' => 'ASC' ) );
	foreach ( $userlisting_posts as $key => $value ){
		if ( trim( Wordpress_Creation_Kit_PB::wck_generate_slug( $value->post_title ) ) == $name ){

            /* check here the visibility and roles for which to display the userlisting */
            $userlisting_args = get_post_meta( $value->ID, 'wppb_ul_page_settings', true );
            if( !empty( $userlisting_args[0]['visible-only-to-logged-in-users'] ) && $userlisting_args[0]['visible-only-to-logged-in-users'] == 'yes' ){
                if( !is_user_logged_in() )
                    return apply_filters( 'wppb_userlisting_no_permission_to_view', '<p>'. __( 'You do not have permission to view this user list.', 'profile-builder' ) .'</p>' );

                if( !empty( $userlisting_args[0]['visible-to-following-roles'] ) ){
                    if( strpos( $userlisting_args[0]['visible-to-following-roles'], '*' ) === false ){
                        $current_user = wp_get_current_user();
                        $roles = $current_user->roles;
                        if( empty( $roles ) )
                            $roles = array();

                        $visibility_for_roles = explode( ', ',$userlisting_args[0]['visible-to-following-roles'] );
                        $check_intersect_roles = array_intersect( $visibility_for_roles, $roles );

                        if( empty( $check_intersect_roles ) )
                            return apply_filters( 'wppb_userlisting_no_role_to_view', '<p>'. __( 'You do not have the required user role to view this user list.', 'profile-builder' ) .'</p>' );
                    }
                }
            }

			$userID = wppb_get_query_var( 'username' );

            // generate a single user template if "single" shortcode argument is set.
            if ( $single !== false ){
                if ( is_numeric( $id ) ){
                    $userID = $id;
                } else {
                    $userID = get_current_user_id();
                }
                $single = true;
            }

			if( !empty( $userID ) ){
                $user_object = new WP_User( $userID );
                $list_display_roles = explode( ', ', $userlisting_args[0]["roles-to-display"] );
                $role_present = array_intersect( $list_display_roles, $user_object->roles );

                $single_user_queryvar = wppb_get_query_var( 'username' );
                if( ( !empty( $exclude ) && in_array( $userID, wp_parse_id_list( $exclude ) ) ) || ( !empty( $include ) && !in_array( $userID, wp_parse_id_list( $include ) ) ) || ( !in_array( '*', $list_display_roles ) && empty( $role_present ) ) || (!empty( $single_user_queryvar ) && $single ) ) {
                    return __( 'User not found', 'profile-builder' );
                }
                else {
                    $single_userlisting_template = get_post_meta( $value->ID, 'wppb-single-ul-templates', true );
                    // apply active User Listing Theme styles
                    $single_userlisting_template = apply_filters( 'wppb_apply_active_ul_theme_style', $single_userlisting_template, $value->ID, true );
                    if( empty( $single_userlisting_template ) )
                        $single_userlisting_template = wppb_generate_singleUserlisting_content();
                    // prevent loading more than one template for single user listing
                    if( !$wppb_single_userlisting_loaded ){
                        $wppb_single_userlisting_loaded = true;
                        return apply_filters( 'wppb_single_userlisting_template', (string) new PB_Mustache_Generate_Template( wppb_generate_mustache_array_for_single_user_list(), $single_userlisting_template, array( 'userlisting_form_id' => $value->ID, 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'include' => $include, 'exclude' => $exclude, 'user_id' => $userID, 'single' => true ) ), $userID );
                    }
                }
            }elseif( $single == true){
                // don't show anything for non-logged in users.
                return;
            }else{
                $userlisting_template = get_post_meta( $value->ID, 'wppb-ul-templates', true );
                // apply active User Listing Theme styles
                $userlisting_template = apply_filters( 'wppb_apply_active_ul_theme_style', $userlisting_template, $value->ID, false );
                if( empty( $userlisting_template ) )
                    $userlisting_template = wppb_generate_allUserlisting_content();
				return apply_filters( 'wppb_all_userlisting_template', '<div class="wppb-userlisting-container">'.(string) new PB_Mustache_Generate_Template( wppb_generate_mustache_array_for_user_list($userlisting_template), $userlisting_template, array( 'userlisting_form_id' => $value->ID, 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'include' => $include, 'exclude' => $exclude, 'single' => false ) ) . '</div>' ) ;
            }
		}
	}
}



/**
 * Function that returns the meta-values for the default fields
 *
 * @since v.2.0
 *
 * @param string $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string the value for the meta-field
 */
function wppb_userlisting_show_default_user_fields( $value, $name, $children, $extra_info ){
	$userID = wppb_get_query_var( 'username' );

    if( !empty( $extra_info['user_id'] ) )
		$user_id = $extra_info['user_id'];
	else
		$user_id = '';

	if( empty( $userID ) )
		$user_info = get_userdata($user_id);
	else
		$user_info = get_userdata($userID);

	if( !$user_info )
	    return $value;

    $returned_value = '';
	if( $name == 'meta_user_name' ){
        $wppb_general_settings = get_option( 'wppb_general_settings' );
        if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) )
            $returned_value = apply_filters('wppb_userlisting_extra_meta_email', $user_info->user_email, new WP_User( $user_info->ID ) );
        else
		    $returned_value = apply_filters('wppb_userlisting_extra_meta_user_name', $user_info->user_login, new WP_User( $user_info->ID ) );
    }
    else if( $name == 'meta_email' )
        $returned_value = apply_filters('wppb_userlisting_extra_meta_email', $user_info->user_email, new WP_User( $user_info->ID ) );
	else if( $name == 'meta_display_name' )
		$returned_value = $user_info->display_name;
	else if( $name == 'meta_first_name' )
		$returned_value = $user_info->user_firstname;
	else if( $name == 'meta_last_name' )
		$returned_value = $user_info->user_lastname;
	else if( $name == 'meta_nickname' )
		$returned_value = $user_info->nickname;
	else if( $name == 'meta_website' )
		$returned_value = $user_info->user_url;
    else if( $name == 'meta_biographical_info' )
        $returned_value = apply_filters('wppb_userlisting_autop_biographical_info', wpautop($user_info->description), $user_info->description);
    else if ( $name == 'meta_blog_url' ){
            $returned_value = wppb_get_blog_url_of_user_id( $user_info->ID, false );
    }
	else if( $name == 'meta_role' ){
        if( !empty( $user_info->roles ) ){
            include_once(ABSPATH . 'wp-admin/includes/user.php');
            $editable_roles = array_keys( get_editable_roles() );
            $WP_Roles = new WP_Roles();
            $role_names = '';

            if( $roles = array_intersect( array_values( $user_info->roles ), $editable_roles ) )
                foreach( $roles as $key=>$role )
                    if( $key >= '1' )
                        $role_names .= ', '.(isset( $WP_Roles->role_names[$role] ) ? translate_user_role( $WP_Roles->role_names[$role] ) : __( 'None', 'profile-builder' ));
                    else
                        $role_names .= isset( $WP_Roles->role_names[$role] ) ? translate_user_role( $WP_Roles->role_names[$role] ) : __( 'None', 'profile-builder' );
            else {
                $role = reset($user_info->roles);
                $role_names .= isset($WP_Roles->role_names[$role]) ? translate_user_role($WP_Roles->role_names[$role]) : __( 'None', 'profile-builder' );
            }

            $returned_value = apply_filters('wppb_userlisting_extra_meta_role', $role_names, $user_info );
        }

	}
    else if( $name == 'meta_role_slug' ){
        if( !empty( $user_info->roles ) ){
            include_once(ABSPATH . 'wp-admin/includes/user.php');
            $editable_roles = array_keys( get_editable_roles() );
            $WP_Roles = new WP_Roles();
            $role_slugs = '';

            if( $roles = array_intersect( array_values( $user_info->roles ), $editable_roles ) )
                foreach( $roles as $key=>$role )
                    if( $key >= '1' )
                        $role_slugs .= ', '.(isset( $WP_Roles->roles[$role] ) ? $role : __( 'None', 'profile-builder' ));
                    else
                        $role_slugs .= isset( $WP_Roles->roles[$role] ) ? $role : __( 'None', 'profile-builder' );
            else {
                $role = reset($user_info->roles);
                $role_slugs .= isset( $WP_Roles->roles[$role] ) ? $role : __( 'None', 'profile-builder' );
            }

            $returned_value = apply_filters('wppb_userlisting_extra_meta_role_slug', $role_slugs, $user_info );
        }

    }
	else if( $name == 'meta_registration_date' ){
        $register_timestamp = strtotime( $user_info->user_registered );
        /* convert to local timezone as date */
        $time = date_i18n( 'Y-m-d', wppb_add_gmt_offset( $register_timestamp ) );
		$returned_value = apply_filters('wppb_userlisting_extra_meta_registration_date', $time, $user_info );
	}

    /* mustache escapes the values by default when the meta is just with {{ so if it comes escaped from the database it won't show properly in the table
    so we need to send the raw value to mustache */
    $returned_value = wp_specialchars_decode( $returned_value );

    return apply_filters('wppb_userlisting_default_user_field_value', $returned_value, $name, $userID );
}
add_filter( 'mustache_variable_default_user_field', 'wppb_userlisting_show_default_user_fields', 10, 4 );



/**
 * Function that returns the number of posts related to each user
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return the value for the meta-field
 */
function wppb_userlisting_show_number_of_posts( $value, $name, $children, $extra_info ){
	$userID = wppb_get_query_var( 'username' );

	$user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );

	$allPosts = get_posts( array( 'author'=> $user_info->ID, 'numberposts'=> -1 ) );
	$number_of_posts = count( $allPosts );

	return apply_filters('wppb_userlisting_extra_meta_number_of_posts', '<a href="'.get_author_posts_url($user_info->ID).'" id="postNumberLink" class="postNumberLink">'.$number_of_posts.'</a>', $user_info, $number_of_posts);
}
add_filter( 'mustache_variable_number_of_posts', 'wppb_userlisting_show_number_of_posts', 10, 4 );



/**
 * Function that returns the meta-value for the respectiv meta-field
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return the value for the meta-field
 */
function wppb_userlisting_show_user_meta( $value, $name, $children, $extra_info ){
	$userID = wppb_get_query_var( 'username' );

	$user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );

	if( empty( $userID ) )
		$userID = $user_id;

	// strip first meta_ from $name
	$name = preg_replace('/meta_/', '', $name, 1);
    $value = get_user_meta( $userID, $name, true );

    /* mustache escapes the values by default when the meta is just with {{ so if it comes escaped from the database it won't show properly in the table
    so we need to send the raw value to mustache */
    if( !is_array( $value ) )
        $value = wp_specialchars_decode( $value );
    else
        array_walk_recursive( $value, 'wp_specialchars_decode' );

	return apply_filters('wppb_userlisting_user_meta_value', $value, $name, $userID);

}
add_filter( 'mustache_variable_user_meta', 'wppb_userlisting_show_user_meta', 10, 4 );

function wppb_userlisting_show_user_meta_wysiwyg( $value, $name, $children, $extra_info ){

    $allowed_tags = array_merge(
                        wp_kses_allowed_html( 'post' ),
                        array( 'iframe' => array( 'src' => true, 'class' => true, 'height' => true, 'width' => true, 'frameborder' =>true, 'scrolling' => true ) )
                    );
    $value = wp_kses( do_shortcode( wppb_userlisting_show_user_meta( $value, $name, $children, $extra_info ) ), $allowed_tags );

    $wpautop = apply_filters( 'wppb_userlisting_wysiwyg_wpautop', true );
    if( $wpautop )
        return wpautop( $value );
    else
        return $value;
}
add_filter( 'mustache_variable_user_meta_wysiwyg', 'wppb_userlisting_show_user_meta_wysiwyg', 10, 4 );

// generate post title and link for Select (CPT)
function wppb_userlisting_show_user_meta_select_cpt( $value, $name, $children, $extra_info ){

    $userID = wppb_get_query_var( 'username' );

    $user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );

    if( empty( $userID ) )
        $userID = $user_id;

    // strip first meta_ & _tile_link from $name
    $name = preg_replace('/meta_/', '', $name, 1);
    $name = preg_replace('/_cpt_title_link/', '', $name, 1);

    $value = get_user_meta( $userID, $name, true );
    if ( !empty( $value ) ) {
        return '<a href="' . get_permalink($value) . '">' . get_the_title($value) . '</a>';
    }
}
add_filter( 'mustache_variable_user_meta_select_cpt', 'wppb_userlisting_show_user_meta_select_cpt', 10, 4 );

// retrieve the ID of the uploaded file
function wppb_userlisting_show_user_meta_custom_upload_id( $value, $name, $children, $extra_info ){

    $userID = wppb_get_query_var( 'username' );

    $user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );

    if( empty( $userID ) )
        $userID = $user_id;

    // strip first meta_ & _id from $name
    $name = preg_replace('/meta_/', '', $name, 1);
    $name = preg_replace('/_id/', '', $name, 1);

    $value = get_user_meta( $userID, $name, true );
    if ( !empty( $value ) ) {
        return $value;
    }
}
add_filter( 'mustache_variable_user_meta_custom_upload_id', 'wppb_userlisting_show_user_meta_custom_upload_id', 10, 4 );

/* select, checkbox and radio can have their labels displayed */
function wppb_userlisting_show_user_meta_labels( $value, $name, $children, $extra_info ){
    $userID = wppb_get_query_var( 'username' );

    $user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );

    if( empty( $userID ) )
        $userID = $user_id;

    // strip first meta_ from $name
    $name = preg_replace( '/meta_/', '', $name, 1 );
    $name = preg_replace( '/_labels$/', '', $name, 1 );

    $value = get_user_meta( $userID, $name, true );
    /* get manage fields */
    global $wppb_manage_fields;
    if( !isset( $wppb_manage_fields ) )
        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    $wppb_manage_fields = apply_filters( 'wppb_form_fields', $wppb_manage_fields, array( 'user_id' => $userID, 'context' => 'mustache_variable' ) );
    if( !empty( $wppb_manage_fields ) ) {
        foreach ($wppb_manage_fields as $field) {
            if( $field['meta-name'] == $name ){
                /* get label corresponding to value. the values and labels in the backend settings are comma separated so we assume that as well here ? */
                $saved_values = array_map( 'trim', explode( ',', $value ) );
                $field['options'] = array_map( 'trim', explode( ',', $field['options'] ) );
                $field['labels'] = array_map( 'trim', explode( ',', $field['labels'] ) );
                /* get the position for each value */
                $key_array = array();
                if( !empty( $field['options'] ) ){
                    foreach( $field['options'] as $key => $option ){
                        if( in_array( $option, $saved_values ) )
                            $key_array[] = $key;
                    }
                }

                $show_values = array();
                if( !empty( $key_array ) ){
                    foreach( $key_array as $key ){
                        if( !empty( $field['labels'][$key] ) )
                            $show_values[] = $field['labels'][$key];
                        else
                            $show_values[] = $field['options'][$key];
                    }
                }

                return apply_filters( 'wppb_userlisting_user_meta_value_label', implode( ', ', $show_values ), $name, $show_values, $userID );
            }
        }
    }
}
add_filter( 'mustache_variable_user_meta_labels', 'wppb_userlisting_show_user_meta_labels', 10, 4 );

function wppb_modify_userlisting_user_meta_value($value, $name, $userID = ''){
    global $wppb_manage_fields;
    if( !isset( $wppb_manage_fields ) )
        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    $wppb_manage_fields = apply_filters( 'wppb_form_fields', $wppb_manage_fields, array( 'user_id' => $userID, 'context' => 'mustache_variable' ) );
    if( !empty( $wppb_manage_fields ) ){
        foreach ($wppb_manage_fields as $field){
            if ( ($field['field'] == 'Textarea')&& ($field['meta-name'] == $name)) {
                return wpautop($value);
            }
            if( ( $field['field'] == 'Avatar' || $field['field'] == 'Upload' ) && $field['meta-name'] == $name ){
                if( is_numeric($value) ){
                    $img_attr = wp_get_attachment_url( $value );
                    if( !empty( $img_attr ) )
                        return $img_attr;
                }
                else
                    return $value;
            }
            if( $field['field'] == 'Select (Country)' && $field['meta-name'] == $name ) {
                $country_array = wppb_country_select_options( 'front_end' );

                if( ! empty( $country_array[$value] ) )
                    return $country_array[$value];
            }
            if( $field['field'] == 'Select (Currency)' && $field['meta-name'] == $name ) {
                $currency_array = wppb_get_currencies();

                if( ! empty( $currency_array[$value] ) ) {
                    $currency_symbol = wppb_get_currency_symbol( $value );
                    return $currency_array[$value] . ( !empty( $field['show-currency-symbol'] ) && $field['show-currency-symbol'] == 'Yes' && !empty($currency_symbol) ? ' (' . html_entity_decode($currency_symbol) . ')' : '' ) ;
                }
            }

            if( $field['field'] == 'Timepicker' && $field['meta-name'] == $name ) {

                if( !empty( $field['time-format'] ) && $field['time-format'] == '12' ) {

                    if( strpos( $value, ':' ) !== false ) {
                        $time = explode( ':', $value );

                        $hour    = $time[0];
                        $minutes = $time[1];

                        if ($hour > 12) {
                            $hour -= 12;
                            $value = (strlen($hour) == 1 ? '0' . $hour : $hour) . ':' . $minutes . ' pm';
                        } elseif( $hour == 12 )
                            $value = $hour . ':' . $minutes . ' pm';
                        elseif( $hour == '00' )
                            $value = '12' . ':' . $minutes . ' am';
                        else
                            $value = $hour . ':' . $minutes . ' am';

                        return $value;

                    }

                }
            }
            if( ( $field['field'] == 'Checkbox' || $field['field'] == 'Select (Multiple)' ) && $field['meta-name'] == $name ) {
                $value = implode( ', ', explode( ',', $value ) );
                return $value;
            }
        }
    }
    return $value;
}
add_filter('wppb_userlisting_user_meta_value', 'wppb_modify_userlisting_user_meta_value', 10, 3);

/**
 * Function that creates the sort-link for the various fields
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return sort-link
 */
function wppb_userlisting_sort_tags( $value, $name, $children, $extra_info ){

	if ( $name == 'sort_user_name' )
        return '<a href="'.wppb_get_new_url( 'login', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'login' ) . '" id="sortLink1">'.apply_filters( 'sort_user_name_filter', __( 'Username', 'profile-builder' ) ).'</a>';

	elseif ($name == 'sort_first_last_name')
		return apply_filters( 'sort_first_last_name_filter', __( 'First/Lastname', 'profile-builder' ) );

	elseif ( $name == 'sort_email' )
        return '<a href="'.wppb_get_new_url( 'email', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'email' ) . '" id="sortLink2">'.apply_filters( 'sort_email_filter', __( 'Email', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_registration_date' )
        return '<a href="'.wppb_get_new_url( 'registered', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'registered' ) . '" id="sortLink3">'.apply_filters( 'sort_registration_date_filter', __( 'Sign-up Date', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_first_name' )
        return '<a href="'.wppb_get_new_url( 'firstname', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'firstname' ) . '" id="sortLink4">'.apply_filters( 'sort_first_name_filter', __( 'First Name', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_last_name' )
        return '<a href="'.wppb_get_new_url( 'lastname', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'lastname' ) . '" id="sortLink5">'.apply_filters( 'sort_last_name_filter', __( 'Last Name', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_display_name' )
        return '<a href="'.wppb_get_new_url( 'nicename', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'nicename' ) . '" id="sortLink6">'.apply_filters( 'sort_display_name_filter', __( 'Display Name', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_website' )
		return '<a href="'.wppb_get_new_url( 'url', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'website' ) . '" id="sortLink7">'.apply_filters('sort_website_filter', __( 'Website', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_biographical_info' )
        return '<a href="'.wppb_get_new_url( 'bio', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'bio' ) . '" id="sortLink8">'.apply_filters( 'sort_biographical_info_filter', __( 'Biographical Info', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_number_of_posts' )
        return '<a href="'.wppb_get_new_url( 'post_count', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'post_count' ) . '" id="sortLink9">'.apply_filters( 'sort_number_of_posts_filter', __( 'Posts', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_aim' )
        return '<a href="'.wppb_get_new_url( 'aim', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'aim' ) . '" id="sortLink10">'.apply_filters( 'sort_aim_filter', __( 'Aim', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_yim' )
        return '<a href="'.wppb_get_new_url( 'yim', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'yim' ) . '" id="sortLink11">'.apply_filters( 'sort_yim_filter', __( 'Yim', 'profile-builder' ) ).'</a>';

	elseif ( $name == 'sort_jabber' )
        return '<a href="'.wppb_get_new_url( 'jabber', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'jabber' ) . '" id="sortLink12">'.apply_filters( 'sort_jabber_filter', __( 'Jabber', 'profile-builder' ) ).'</a>';

    elseif ( $name == 'sort_nickname' )
        return '<a href="'.wppb_get_new_url( 'nickname', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'nickname' ) . '" id="sortLink13">'.apply_filters( 'sort_nickname_filter', __( 'Nickname', 'profile-builder' ) ).'</a>';

    elseif ( $name == 'sort_role' )
        return '<a href="'.wppb_get_new_url( 'role', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'role' ) . '" id="sortLink14">'.apply_filters( 'sort_role_filter', __( 'Role', 'profile-builder' ) ).'</a>';

    elseif ( $name == 'sort_user_id' )
        return '<a href="'.wppb_get_new_url( 'user_id', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'user_id' ) . '" id="sortLink15">'.apply_filters( 'sort_user_id_filter', __( 'ID', 'profile-builder' ) ).'</a>';

    else{
        global $wppb_manage_fields;
        if( !isset( $wppb_manage_fields ) )
            $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

		$wppb_manage_fields = apply_filters( 'wppb_sort_change_form_fields', $wppb_manage_fields );

		if ( $wppb_manage_fields != 'not_found' ){
			$i = 14;

			foreach( $wppb_manage_fields as $key => $field_value ){
				if ( $name == 'sort_'.$field_value['meta-name'] ){
					$i++;

					return '<a href="'.wppb_get_new_url( $field_value['meta-name'], $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( $field_value['meta-name'] ) . '" id="sortLink'.$i.'">'.$field_value['field-title'].'</a>';
				}
			}
		}
	}

    return $value;

}
add_filter( 'mustache_variable_sort_tag', 'wppb_userlisting_sort_tags', 10, 4 );



/**
 * Function that handles the user queryes for display and facets
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return sort-link
 */
function wppb_userlisting_users_loop( $value, $name, $children, $extra_values ){
	if( $name == 'users' ){
        global $userlisting_args;
        global $wpdb;
		$userlisting_form_id = $extra_values['userlisting_form_id'];
		$userlisting_args = get_post_meta( $userlisting_form_id, 'wppb_ul_page_settings', true );

        if( !empty( $userlisting_args[0] ) ){
            $paged = (wppb_get_query_var('wppb_page')) ? wppb_get_query_var('wppb_page') : 1;
            if( !is_int( (int)$userlisting_args[0]['number-of-userspage'] ) || (int)$userlisting_args[0]['number-of-userspage'] == 0 )
                $userlisting_args[0]['number-of-userspage'] = 5;

            // Check if some of the listing parameters have changed
            if ( isset( $_REQUEST['setSortingOrder'] ) && sanitize_text_field( $_REQUEST['setSortingOrder'] ) !== '' )
                $sorting_order = sanitize_text_field( $_REQUEST['setSortingOrder'] );
            else
                $sorting_order = $userlisting_args[0]['default-sorting-order'];

            // if we have admin approval on we don't want to show users that have the unapproved or pending status in
            // the userlisting so we need to exclude them
            if( wppb_get_admin_approval_option_value() === 'yes' ){
                $excluded_ids = array();
                $user_status_unapproved = get_term_by( 'name', 'unapproved', 'user_status' );
                if( $user_status_unapproved != false ){
                    $term_taxonomy_id = $user_status_unapproved->term_taxonomy_id;
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT wppb_t1.ID FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->term_relationships AS wppb_t0 ON wppb_t1.ID = wppb_t0.object_id WHERE wppb_t0.term_taxonomy_id = %d", $term_taxonomy_id ) );

                    foreach ( $results as $result )
                        array_push( $excluded_ids, $result->ID );
                }
                $user_status_pending = get_term_by( 'name', 'pending', 'user_status' );
                if( $user_status_pending != false ){
                    $term_taxonomy_id = $user_status_pending->term_taxonomy_id;
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT wppb_t1.ID FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->term_relationships AS wppb_t0 ON wppb_t1.ID = wppb_t0.object_id WHERE wppb_t0.term_taxonomy_id = %d", $term_taxonomy_id ) );

                    foreach ( $results as $result )
                        array_push( $excluded_ids, $result->ID );
                }
                $excluded_ids = implode( ',', $excluded_ids );
            }
            if( !empty($excluded_ids) )
                $extra_values['exclude'] .= ','. $excluded_ids;
			//set query args
			$args = array(
				'order'					        => $sorting_order,
                'include'                       => $extra_values['include'],
                'exclude'                       => $extra_values['exclude'],
                'fields'                        => array( 'ID' )
			);

            /* get all field options here, we will need it bellow */
            global $wppb_manage_fields;
            if( !isset( $wppb_manage_fields ) )
                $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

            // Check if some of the listing parameters have changed
            if ( isset( $_REQUEST['setSortingCriteria'] ) && sanitize_text_field( $_REQUEST['setSortingCriteria'] ) !== '' )
                $sorting_criteria = sanitize_text_field( $_REQUEST['setSortingCriteria'] );
            else
                $sorting_criteria = $userlisting_args[0]['default-sorting-criteria'];

            if( in_array( $sorting_criteria, array( 'login', 'email', 'url', 'registered', 'post_count', 'nicename', 'user_id' ) ) ){
                if( $sorting_criteria == 'nicename' )
                    $args['orderby']  = 'display_name';
                else
                    $args['orderby']  = $sorting_criteria;
            }
            else{

                $args['orderby']  = apply_filters( 'wppb_ul_sorting_type', 'meta_value', $sorting_criteria );

                if ($wppb_manage_fields != 'not_found') {
                    foreach ($wppb_manage_fields as $wppb_field) {
                        if( $wppb_field['meta-name'] == $sorting_criteria ){
                            if( $wppb_field['field'] == 'Number' || $wppb_field['field'] == 'Phone' ){
                                $args['orderby']  = apply_filters( 'wppb_ul_sorting_type', 'meta_value_num', $sorting_criteria );
                            }

                            //this doesn't really seem to work properly so it is off by default. maybe it can help someone
                            if( $wppb_field['field'] == 'Datepicker' && apply_filters( 'wppb_ul_try_date_sorting', false ) ){
                                $args['orderby']  = 'meta_value_date';
                                $args['meta_type']  = 'DATE';
                            }
                        }
                    }
                }

                switch( $sorting_criteria ){
                    case "bio":
                        $args['meta_key'] = 'description';
                        break;
                    case "firstname":
                        $args['meta_key'] = 'first_name';
                        break;
                    case "lastname":
                        $args['meta_key'] = 'last_name';
                        break;
                    case "nickname":
                        $args['meta_key'] = 'nickname';
                        break;
                    case "role":
                        $args['meta_key'] = $wpdb->get_blog_prefix().'capabilities';
                        break;
                    case "RAND()":
                        break;
                    default:
                        $args['meta_key']  = $sorting_criteria;
                }
            }

            /* the relationship between meta query is AND because we need to narrow the result  */
            $args['meta_query'] = array('relation' => 'AND');

            /* we check if we have a meta_value and meta_key in the shortcode and add a meta query */
            if( !empty( $extra_values['meta_value'] ) && !empty( $extra_values['meta_key'] ) ){
                $args['meta_query'][0] = array( 'relation' => 'AND' ); //insert relation here
                $args['meta_query'][0][] = array(
                    'key' => $extra_values['meta_key'],
                    'value' => $extra_values['meta_value'],
                    'compare' => apply_filters( 'wppb_ul_meta_att_in_shortcode_compare', '=', $extra_values )
                );
            }


            /* add facet meta query here */
            $all_user_listing_template = get_post_meta( $userlisting_form_id, 'wppb-ul-templates', true );
            if( !empty( $all_user_listing_template ) && strpos( $all_user_listing_template, '{{{faceted_menus}}}' ) !== false ) {
                $faceted_settings = get_post_meta( $userlisting_form_id, 'wppb_ul_faceted_settings', true );
                if( !empty( $faceted_settings ) ){
					if( empty( $args['meta_query'][0] ) )
                    	$args['meta_query'][0] = array( 'relation' => 'AND' );

                    foreach( $faceted_settings as $faceted_setting ){

                        //for custom meta keys or repeaters we use LIKE instead of =
                        $compare_key = wppb_ul_determine_compare_key_arg( $faceted_setting['facet-meta'] );

                        if( isset( $_GET['ul_filter_'.$faceted_setting['facet-meta']] ) ){
                            if( $faceted_setting['facet-type'] == 'range' ){
                                $args['meta_query'][0][$faceted_setting['facet-meta']] = array(
                                    'key' => $faceted_setting['facet-meta'],
                                    'compare_key' => $compare_key,
                                    'value' => explode( '-', sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] ) ),
                                    'compare' => 'BETWEEN',
                                    'type'    => 'NUMERIC'
                                );
                            }
                            else if( $faceted_setting['facet-type'] == 'search' ){
	                            $value = apply_filters( 'wppb_ul_search_meta_value', stripslashes( sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] ) ), $faceted_setting['facet-meta'], $wppb_manage_fields );
	                            /* handle roles facet differently */
	                            if( $wpdb->get_blog_prefix().'capabilities' === $faceted_setting['facet-meta'] ) {
									$value = wppb_get_role_slug( $value );
	                            }
                                $args['meta_query'][0][$faceted_setting['facet-meta']] = array(
                                    'key' => $faceted_setting['facet-meta'],
                                    'compare_key' => $compare_key,
                                    'value' => $value,
                                    'compare' => 'LIKE'
                                );
                            }
                            else if( $faceted_setting['facet-behaviour'] == 'narrow' ){

                                /* for fields types that have multiple values (checkbox..) we check for the options in the fields settings and not what is stored in the database  */
                                if( wppb_check_if_field_is_multiple_value_from_meta_name( $faceted_setting['facet-meta'], $wppb_manage_fields ) ){
                                    $compare = 'REGEXP';
                                    $val = '('.preg_quote( sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] ) ).'$)|('. preg_quote( sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] ) ).',)';
                                }
                                else{
                                    /* handle roles facet differently */
                                    if( $wpdb->get_blog_prefix().'capabilities' == $faceted_setting['facet-meta'] ) {
                                        $compare = 'LIKE';
                                        $val = '"'. wppb_get_role_slug(sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] )) . '"';
                                    }
                                    else{
                                        $compare = '=';
                                        $val = sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] );
                                    }
                                }

                                $args['meta_query'][0][$faceted_setting['facet-meta']] = array(
                                    'key' => $faceted_setting['facet-meta'],
                                    'compare_key' => $compare_key,
                                    'value' => $val,
                                    'compare' => $compare
                                );
                            }
                            else if( $faceted_setting['facet-behaviour'] == 'expand' ){

                                $values = explode( '||', sanitize_text_field( $_GET['ul_filter_'.$faceted_setting['facet-meta']] ) );
                                if( !empty( $values ) ) {
                                    /* for fields types that have multiple values (checkbox..) we check for the options in the fields settings and not what is stored in the database  */
                                    /* we need a new nested meta query for this */
                                    if( wppb_check_if_field_is_multiple_value_from_meta_name( $faceted_setting['facet-meta'], $wppb_manage_fields ) ) {
                                        $args['meta_query'][0][$faceted_setting['facet-meta']] = array('relation' => 'OR');
                                        foreach ($values as $key => $val) {
                                            $args['meta_query'][0][$faceted_setting['facet-meta']][] = array(
                                                'key' => $faceted_setting['facet-meta'],
                                                'compare_key' => $compare_key,
                                                'value' => '(' . preg_quote( $val ) . '$)|(' . preg_quote( $val ) . ',)',
                                                'compare' => 'REGEXP'
                                            );
                                        }
                                    }/* handle roles facet differently */
                                    else if( $wpdb->get_blog_prefix().'capabilities' == $faceted_setting['facet-meta'] ){
                                        $args['meta_query'][0][$faceted_setting['facet-meta']] = array('relation' => 'OR');
                                        foreach ($values as $key => $val) {
                                            $args['meta_query'][0][$faceted_setting['facet-meta']][] = array(
                                                'key' => $faceted_setting['facet-meta'],
                                                'compare_key' => $compare_key,
                                                'value' => '"'.wppb_get_role_slug($val).'"',
                                                'compare' => 'LIKE'
                                            );
                                        }
                                    }
                                    else{
                                        $args['meta_query'][0][$faceted_setting['facet-meta']] = array(
                                            'key' => $faceted_setting['facet-meta'],
                                            'compare_key' => $compare_key,
                                            'value' => $values,
                                            'compare' => 'IN'
                                        );
                                    }
                                }


                            }
                        }
                    }
                }
            }

            /* handle the roles to display setting  it need to be before search*/
            if( !empty( $userlisting_args[0]['roles-to-display'] ) )
                $roles = explode( ', ', $userlisting_args[0]['roles-to-display'] );
            if( empty( $roles[0] ) || in_array( '*', $roles ) )
                $roles = array();

			if( !empty( $roles ) ){
				$args['meta_query'][1] = array('relation' => 'OR');
				foreach ($roles as $role) {
					$args['meta_query'][1][] = array(
						'key' => $wpdb->get_blog_prefix().'capabilities',
						'value' => '"'.$role.'"',
						'compare' => 'LIKE'
					);
				}
			}

            /* set the search here, we have a combination with search arg for columns in user table and meta query for user_meta table */
			if ( isset( $_REQUEST['searchFor'] ) ) {
                $search_for = stripslashes( sanitize_text_field( $_REQUEST['searchFor'] ) );
                //was a valid string enterd in the search form?
                $searchText = apply_filters('wppb_userlisting_search_field_text', __('Search Users by All Fields', 'profile-builder'));
                if (trim($search_for) !== $searchText){
                    $args['search'] = '*' . $search_for . '*';

                    /* filter used to exclude fields from search */
                    $wppb_exclude_search_fields = apply_filters('wppb_exclude_search_fields', array(), $userlisting_form_id );

                    $args['search_columns'] = array('ID', 'user_login', 'user_email', 'user_url', 'user_nicename' );
                    foreach( $args['search_columns'] as $key => $search_column ){
                        if( in_array( $search_column, $wppb_exclude_search_fields ) ){
                            unset( $args['search_columns'][$key] );
                        }
                    }

                    /* the meta query relationship in the search is or because we need all the results */
                    $args['meta_query'][2] = array('relation' => 'OR');
                    $user_meta_keys = array('first_name', 'last_name', 'nickname', 'description', $wpdb->get_blog_prefix().'capabilities');

                    if ($wppb_manage_fields != 'not_found') {
                        foreach ($wppb_manage_fields as $wppb_manage_field) {
                            $user_meta_keys[] = $wppb_manage_field['meta-name'];
                        }
                        $user_meta_keys = apply_filters( 'wppb_userlisting_search_in_user_meta_keys', $user_meta_keys, $wppb_manage_fields, $wppb_exclude_search_fields, $searchText, $args );
                    }

                    foreach ($user_meta_keys as $user_meta_key) {
                        if( !in_array($user_meta_key, $wppb_exclude_search_fields ) ) {
	                        $value = apply_filters( 'wppb_ul_search_meta_value', stripslashes($search_for), $user_meta_key, $wppb_manage_fields );
							/* handle roles differently */
	                        if( $user_meta_key === 'wp_capabilities' ) {
		                        $value = wppb_get_role_slug( $value );
	                        }
                            $args['meta_query'][2][] = array(
                                'key' => $user_meta_key,
                                'value' => $value,
                                'compare' => apply_filters( 'wppb_ul_search_all_meta_compare', 'LIKE' )
                            );
                        }
                    }
                }
			}



			$args = apply_filters( 'wppb_userlisting_user_query_args', $args );

            global $totalUsers;

			//query users
            //echo microtime(true).'<br/>';
            /* check if we have faceted menus, if we have we need to query for all users so we can have dynamic facet values */
            $all_user_listing_template = get_post_meta( $userlisting_form_id, 'wppb-ul-templates', true );
            if( strpos( $all_user_listing_template, '{{{faceted_menus}}}' ) !== false ) {
                $args['count_total'] = false;
                $wp_all_user = new WP_User_Query($args);
                $all_user_ids = array_unique( $wp_all_user->get_results(), SORT_REGULAR );//array_unique was introduced since we introduced the compare_key arg for repeaters ..and the query can return the same user multiple times
                $totalUsers = count( $all_user_ids );
                $all_user_ids_array = array();
                if( !empty($all_user_ids) ){
                    foreach( $all_user_ids as $all_user_id ){
                        $all_user_ids_array[] = $all_user_id->ID;
                    }
                }
                global $all_queried_user_ids_string;
                $all_queried_user_ids_string = implode(',', $all_user_ids_array );

                $faceted_settings = get_post_meta( $userlisting_form_id, 'wppb_ul_faceted_settings', true );
                if( !empty( $faceted_settings ) ) {
                    foreach ($faceted_settings as $faceted_setting) {
                        if (isset($_GET['ul_filter_' . $faceted_setting['facet-meta']])){
                            $args_temp = $args;
                            unset( $args_temp['meta_query'][0][$faceted_setting['facet-meta']] );
                            $wp_users = new WP_User_Query($args_temp);
                            $user_ids = array_unique( $wp_users->get_results(), SORT_REGULAR );//array_unique was introduced since we introduced the compare_key arg for repeaters ..and the query can return the same user multiple times
                            $user_ids_array = array();
                            if( !empty($user_ids) ){
                                foreach( $user_ids as $user_id ){
                                    $user_ids_array[] = $user_id->ID;
                                }
                            }

                            $gloabl_filter_ids_name = $faceted_setting['facet-meta'].'_user_ids';
                            global ${$gloabl_filter_ids_name};
                            $$gloabl_filter_ids_name = implode(',', $user_ids_array );
                        }
                    }
                }


            }

            $args['number']	= (int)$userlisting_args[0]['number-of-userspage'];
            $args['paged']  = $paged;
            $wp_user_search = new WP_User_Query( $args );


			// Expose to other sections the arguments used for listing the users.
			do_action( 'wppb_users_listing_current_query_arguments', $userlisting_form_id, $args );


	        if( strpos( $all_user_listing_template, '{{{faceted_menus}}}' ) !== false ) {
		        //array_unique was introduced since we introduced the compare_key arg for repeaters ..and the query can return the same user multiple times
		        $thisPageOnly = apply_filters('wppb_ul_users_faceted', array_unique( $wp_user_search->get_results(), SORT_REGULAR ) );
	        }
	        else {
		        $thisPageOnly = apply_filters('wppb_ul_users', $wp_user_search->get_results() );
	        }

            if( empty( $totalUsers ) )
			    $totalUsers = $wp_user_search->get_total();

			$children_vals = array();

			if( !empty( $thisPageOnly ) ){
				$i = 0;
				foreach( $thisPageOnly as $user ){
					foreach( $children as $child ){

						$children_vals[$i][ $child['name'] ] = apply_filters( 'mustache_variable_'. $child['type'], '', $child['name'], empty( $child['children']) ? array() : $child['children'], array( 'user_id' => $user->ID, 'userlisting_form_id' => $userlisting_form_id ) );
					}
					$i++;
				}
			}

			return $children_vals;
		}
	}
}
add_filter( 'mustache_variable_loop_tag', 'wppb_userlisting_users_loop', 10, 4 );

/**
 * Function that determines if a field has the type of Checkbox or Select Multiple from the meta name
 * @param $meta_name the meta name of the field
 * @param $wppb_manage_fields the mange fields array stored in the database
 * @return bool|mixed|void
 */
function wppb_check_if_field_is_multiple_value_from_meta_name( $meta_name, $wppb_manage_fields ){
    if( !empty( $meta_name ) ) {
        if (!empty($wppb_manage_fields) || $wppb_manage_fields != 'not_found') {
            foreach ($wppb_manage_fields as $field) {
                if ($field['meta-name'] == $meta_name && ($field['field'] == "Checkbox" || $field['field'] == "Select (Multiple)" || $field['field'] == 'Select2 (Multiple)')) {
                    return apply_filters('wppb_is_multiple_value_type', true, $meta_name);
                    break;
                }
            }
        }
    }
    return false;
}

/**
 * We need to modify the query string in certain cases
 * @param $query the query performed on the DB
 */
function wppb_user_query_modifications($query) {
    global $userlisting_args;
    global $wpdb;

    /* hopefully it won't get applied to other user queries */
    if( !empty( $userlisting_args ) ){
        if ( isset( $_REQUEST['setSortingCriteria'] ) && sanitize_text_field( $_REQUEST['setSortingCriteria'] ) !== '' )
            $sorting_criteria = sanitize_text_field( $_REQUEST['setSortingCriteria'] );
        else
            $sorting_criteria = $userlisting_args[0]['default-sorting-criteria'];

        if ( isset( $_REQUEST['setSortingOrder'] ) && sanitize_text_field( $_REQUEST['setSortingOrder'] ) !== '' )
            $sorting_order = sanitize_text_field( $_REQUEST['setSortingOrder'] );
        else
            $sorting_order = $userlisting_args[0]['default-sorting-order'];

        switch( $sorting_criteria ){
            case "role":
                $query->query_orderby = 'ORDER by REPLACE( '.$wpdb->prefix.'usermeta.meta_value, SUBSTRING_INDEX( '.$wpdb->prefix.'usermeta.meta_value, \'"\', 1 ), \'\' ) '.$sorting_order;
                break;
            case "user_id":
                $query->query_orderby = 'ORDER by ID '.$sorting_order;
                break;
            case "RAND()":
                $seed = apply_filters( 'wppb_userlisting_random_seed', '' );
                $query->query_orderby = 'ORDER by RAND('.$seed.')';
                break;
        }

        /* when searching in user listing we have to change the operator from AND to OR and move the search expression by changing some ')' around in the relationship between users table and user_meta table */
        if ( isset( $_REQUEST['searchFor'] ) ) {
            $search_for = $wpdb->prepare( "%s", '%'.$wpdb->esc_like( stripslashes( sanitize_text_field( $_REQUEST['searchFor'] ) ) ).'%' );
            remove_all_filters( 'user_search_columns' );//I am not sure that this works in any case but I will leave it here just in case. Implemented the pre_get_users hook for the correct way
            /* when we have sorting by a user meta then there are extra parenthesis which we have to rearange*/
            if( strpos( preg_replace( '/\s+/', ' ', $query->query_where ), ") ) ) AND (ID = " ) !== false ){
                $query->query_where = str_replace( ") ) ) AND (ID = ", "OR (ID = ", preg_replace( '/\s+/', ' ', $query->query_where ) );
                /* we add the user_registered column here as well */
                $query->query_where = str_replace( "user_nicename LIKE ".$search_for.")", "user_nicename LIKE ".$search_for."  OR user_registered LIKE ". $search_for ." OR display_name LIKE ". $search_for ." ) ) ) )", $query->query_where );
            }
            else{
                $query->query_where = str_replace( ") ) AND (ID = ", "OR (ID = ", preg_replace( '/\s+/', ' ', $query->query_where ) );
                /* we add the user_registered column here as well */
                $query->query_where = str_replace( "user_nicename LIKE ".$search_for.")", "user_nicename LIKE ".$search_for."  OR user_registered LIKE ". $search_for ." OR display_name LIKE ". $search_for ." ) ) )", $query->query_where );
            }
        }
    }
}
add_filter( 'pre_user_query', 'wppb_user_query_modifications' );

/* Remove all filters from the user_search_columns so it doesn't interfere with our own alteration of the query syntax for search in the wppb_user_query_modifications function */
add_action( 'pre_get_users', 'wppb_remove_user_search_columns_filters' );
function wppb_remove_user_search_columns_filters(){
    global $userlisting_args;
    /* hopefully it won't get applied to other user queries */
    if( !empty( $userlisting_args ) ) {
        if ( isset( $_REQUEST['searchFor'] ) ) {
            remove_all_filters('user_search_columns');
        }
    }
}

/**
 * Function that returns the user_id for the currently displayed user
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return ID
 */
function wppb_userlisting_user_id( $value, $name, $children, $extra_info ){
	$user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
	$userID = wppb_get_query_var( 'username' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );

	if( ! empty( $user_info ) )
		return $user_info->ID;
}
add_filter( 'mustache_variable_user_id', 'wppb_userlisting_user_id', 10, 4 );


/**
 * Function that formats the date as selected in WordPress Settings
 * @param $date date we need to format
 */
function wppb_change_date_to_wp_format( $date ) {

    if ( isset( $date )) {
        $wp_date_format = get_option('date_format');
        $date = date( $wp_date_format, strtotime( $date ));
    }

    return $date;
}
add_filter( 'mustache_variable_subscription_start_date', 'wppb_change_date_to_wp_format' );
add_filter( 'mustache_variable_subscription_expiration_date', 'wppb_change_date_to_wp_format' );



/**
 * Function that returns the user_nicename for the currently displayed user
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return user_nicename
 */
function wppb_userlisting_user_nicename( $value, $name, $children, $extra_info ){
	$user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
	$userID = wppb_get_query_var( 'username' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );

	if( ! empty( $user_info ) )
		return $user_info->user_nicename;
}
add_filter( 'mustache_variable_user_nicename', 'wppb_userlisting_user_nicename', 10, 4 );



/**
 * Function that returns the link for the more_info link in html form
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_more_info( $value, $name, $children, $extra_info ){
	$more_url = wppb_userlisting_more_info_url( $value, $name, $children, $extra_info );

	if ( apply_filters( 'wbb_userlisting_extra_more_info_link_type', true ) )
		return apply_filters( 'wppb_userlisting_more_info_link', '<span id="wppb-more-span" class="wppb-more-span"><a href="'.$more_url.'" class="wppb-more" id="wppb-more" title="'.__( 'Click here to see more information about this user', 'profile-builder' ) .'" alt="'.__( 'More...', 'profile-builder' ).'">'.__( 'More...', 'profile-builder').'</a></span>', $more_url );

	else
		return apply_filters( 'wppb_userlisting_more_info_link_with_arrow', '<a href="'.$more_url.'" class="wppb-more"><img src="'.WPPB_PLUGIN_URL.'assets/images/arrow_right.png" title="'.__( 'Click here to see more information about this user.', 'profile-builder' ).'" alt=">"></a>' );
}
add_filter( 'mustache_variable_more_info', 'wppb_userlisting_more_info', 10, 4 );


/**
 * Function that returns the map in html form
 *
 * @since v.2.3
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_show_user_meta_map( $value, $name, $children, $extra_info ){

    $userID = ( !empty( $extra_info['user_id'] ) && !empty( $extra_info['single'] ) )  ? $extra_info['user_id'] : wppb_get_query_var( 'username' ) ;
    $output_map = '';

    // Output for all user-listing
    if( empty( $userID ) ) {

        $more_url = wppb_userlisting_more_info_url( $value, $name, $children, $extra_info );
        $output_map .= '<a href="' . $more_url . '" class="wppb-view-map">' . __( 'View Map', 'profile-builder' ) . '</a>';

    // Output for single user-listing
    } else {

        global $wppb_manage_fields;
        if( !isset( $wppb_manage_fields ) )
            $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

        $wppb_manage_fields = apply_filters( 'wppb_form_fields', $wppb_manage_fields, array( 'user_id' => $userID, 'context' => 'mustache_variable' ) );

        if( !empty( $wppb_manage_fields ) ) {
            foreach ($wppb_manage_fields as $field) {
                if ($field['meta-name'] == str_replace('meta_', '', $name)) {

                    wp_enqueue_script('wppb-google-maps-api-script', 'https://maps.googleapis.com/maps/api/js?key=' . $field['map-api-key'], array('jquery'), PROFILE_BUILDER_VERSION, true);
                    wp_enqueue_script('wppb-google-maps-script', WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/map/map.js', array('jquery'), PROFILE_BUILDER_VERSION, true);

                    $map_data_vars_array['map_marker_text_remove'] = __( "Remove Marker", 'profile-builder' );
                    wp_localize_script( 'wppb-google-maps-script', 'wppb_maps_data', $map_data_vars_array );

                    $map_markers = wppb_get_user_map_markers($userID, $field['meta-name']);

					$output_map .= wppb_get_map_output( $field, array(
						'markers'     => $map_markers,
						'show_search' => false,
						'editable'    => false,
						'user_id'     => $userID,
					) );
                }
            }
        }
    }

    return apply_filters( 'wppb_userlisting_map', $output_map );

}
add_filter( 'mustache_variable_user_meta_map', 'wppb_userlisting_show_user_meta_map', 10, 4 );



/**
 * Function that returns the URL only for the more_info
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_more_info_url( $value, $name, $children, $extra_info ){
	$user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
	$userID = wppb_get_query_var( 'username' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );

	//filter to get current user by either username or id(default);
	$get_user_by_ID = apply_filters( 'wppb_userlisting_get_user_by_id', true );
	$url = apply_filters( 'wppb_userlisting_more_base_url', get_permalink() );
    if( !$url && isset( $_SERVER['HTTP_REFERER'] ) ){
        $url = esc_url( esc_url_raw( $_SERVER['HTTP_REFERER'] ) );
    }

	$user_data = get_the_author_meta( 'user_nicename', $user_info->ID );

	if ( isset( $_GET['page_id'] ) )
		return apply_filters ( 'wppb_userlisting_more_info_link_structure1', $url.'&userID='.$user_info->ID, $url, $user_info );

	else{
		if ( $get_user_by_ID === true ) {
            if( is_single() || is_front_page() )//use a simple GET var wppb_username in this cases as registering them causes weird behaviour on frontpage
                return add_query_arg( array( 'wppb_username' => $user_info->ID ), $url );
            else
                return apply_filters('wppb_userlisting_more_info_link_structure2', trailingslashit($url) . 'user/' . $user_info->ID, $url, $user_info);
        }
		else {
            if( is_single() || is_front_page() )//use a simple GET var wppb_username in this cases as registering them causes weird behaviour on frontpage
                return add_query_arg( array( 'wppb_username' => $user_data ), $url );
            else
                return apply_filters('wppb_userlisting_more_info_link_structure3', trailingslashit($url) . 'user/' . $user_data, $url, $user_data);
        }
	}
}
add_filter( 'mustache_variable_more_info_url', 'wppb_userlisting_more_info_url', 10, 4 );


/* we need to check if we have the filter that turns the link for the single user from /id/ to /username/
   if we have then the wppb_get_query_var needs to return the user id becuse that's what we expect in our functions that output the data
 */
add_action('init', 'wppb_check_userlisting_get_user_by');
function wppb_check_userlisting_get_user_by(){
    if ( has_filter( 'wppb_userlisting_get_user_by_id' ) ){
        add_filter( 'wppb_get_query_var_username', 'wppb_change_returned_username_query_var' );
        function wppb_change_returned_username_query_var( $var ){
            /* $var should be username and we want to change it into user id */
            if( !is_numeric($var) && !empty( $var ) ){
                $args= array(
                    'search' => $var,
                    'search_fields' => array( 'user_nicename' )
                );
                $user = new WP_User_Query($args);
                if( !empty( $user->results ) )
                    $var = $user->results[0]->ID;
            }

            return $var;
        }
    }
}

/* when we are on default permalinks we need to return $_GET['userID'] */
add_filter( 'wppb_get_query_var_username', 'wppb_change_returned_username_var_on_default_permalinks' );
function wppb_change_returned_username_var_on_default_permalinks( $var ){
    if( empty( $var ) && isset( $_GET['userID'] ) )
        return sanitize_user( $_GET['userID'] );

    return $var;
}

/* when we are on default permalinks we need to return $_GET['wppb_page'] */
add_filter( 'wppb_get_query_var_wppb_page', 'wppb_change_returned_wppb_page_var_on_default_permalinks' );
function wppb_change_returned_wppb_page_var_on_default_permalinks( $var ){
    if( empty( $var ) && isset( $_GET['wppb_page'] ) )
        return (int)$_GET['wppb_page'];

    return $var;
}

/**
 * Function that returns the link for the previous page
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_go_back_link( $value, $name, $children, $extra_values ){
	if ( apply_filters( 'wppb_userlisting_go_back_link_type', true ) )
		return apply_filters( 'wppb_userlisting_go_back_link', '<div id="wppb-back-span" class="wppb-back-span"><a href=\'javascript:history.go(-1)\' class="wppb-back" id="wppb-back" title="'. __( 'Click here to go back', 'profile-builder' ) .'" alt="'. __( 'Back', 'profile-builder' ) .'">'. __( 'Back', 'profile-builder' ) .'</a></div>' );

	else
		return apply_filters( 'wppb_userlisting_go_back_link_with_arrow', '<a href=\'javascript:history.go(-1)\' class="wppb-back"><img src="'.WPPB_PLUGIN_URL.'assets/images/arrow_left.png" title="'. __( 'Click here to go back', 'profile-builder' ) .'" alt="<"/></a>' );
}
add_filter( 'mustache_variable_go_back_link', 'wppb_userlisting_go_back_link', 10, 4 );



/**
 * Function that returns the pagination created
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_pagination( $value, $name, $children, $extra_info ){
	global $totalUsers;

	require_once ( 'class-userlisting-pagination.php' );

	$this_form_settings = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_page_settings', true );

	if( !empty( $this_form_settings ) ){
		if ( ( $totalUsers != '0' ) || ( $totalUsers != 0 ) ){
			$pagination = new WPPB_Pagination;

			$first = __( '&laquo;&laquo; First', 'profile-builder' );
			$prev = __( '&laquo; Prev', 'profile-builder' );
			$next = __( 'Next &raquo; ', 'profile-builder' );
			$last = __( 'Last &raquo;&raquo;', 'profile-builder' );

            if( !is_int( (int)$this_form_settings[0]['number-of-userspage'] ) || (int)$this_form_settings[0]['number-of-userspage'] == 0 )
                $this_form_settings[0]['number-of-userspage'] = 5;

			$currentPage = wppb_get_query_var( 'wppb_page' );
			if ( empty( $currentPage ) )
				$currentPage = 1;

			if ( isset( $_POST['searchFor'] ) ){
				$searchtext_label = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );

				if ( ( sanitize_text_field( $_POST['searchFor'] ) == $searchtext_label ) || ( sanitize_text_field( $_POST['searchFor'] ) == '' ) )
					$pagination->generate($totalUsers, '', $first, $prev, $next, $last, $currentPage, $this_form_settings[0]['number-of-userspage']);

				else
					$pagination->generate($totalUsers, sanitize_text_field($_POST['searchFor']), $first, $prev, $next, $last, $currentPage, $this_form_settings[0]['number-of-userspage']);

			}elseif ( isset( $_GET['searchFor'] ) ){
				$pagination->generate($totalUsers, sanitize_text_field($_GET['searchFor']), $first, $prev, $next, $last, $currentPage, $this_form_settings[0]['number-of-userspage']);

			}else{
				$pagination->generate($totalUsers, '', $first, $prev, $next, $last, $currentPage, $this_form_settings[0]['number-of-userspage']);
			}

			return apply_filters( 'wppb_userlisting_userlisting_table_pagination', '<div class="userlisting_pagination" id="userlisting_pagination" align="right">'.$pagination->links().'</div>' );
		}
	}
	else
		return apply_filters( 'wppb_userlisting_no_pagination_settings', '<p class="error">'.__( 'You don\'t have any pagination settings on this userlisting!', 'profile-builder' ). '</p>' );

	return;
}
add_filter( 'mustache_variable_pagination', 'wppb_userlisting_pagination', 10, 4 );

/**
 * Function that returns the faceted filters
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_faceted_menus( $value, $name, $children, $extra_info ){
    $this_faceted_filters = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_faceted_settings', true );
    global $wppb_manage_fields;
    if( !isset( $wppb_manage_fields ) )
        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
    if( !empty( $this_faceted_filters ) ){

        /* we need to know if we have a search string and if we do then set the attribute so we can add it in the url when adding a facet in the url later */
        if( !empty( $_REQUEST['searchFor'] ) )
            $search_for = sanitize_text_field( $_REQUEST['searchFor'] );
        else
            $search_for = '';

        $faceted = '<ul class="wppb-faceted-list" data-search-for="'. esc_attr( $search_for ) .'">';

        $faceted .= '<li>'. wppb_ul_faceted_remove( $this_faceted_filters, $wppb_manage_fields ) . '</li>';

        foreach( $this_faceted_filters as $this_faceted_filter ){
            $faceted .= '<li class="wppb-facet-filter wppb-facet-'.$this_faceted_filter['facet-type'].'" id="wppb-facet-'. Wordpress_Creation_Kit_PB::wck_generate_slug( $this_faceted_filter['facet-meta'] ) .'">';
            if( !empty( $this_faceted_filter['facet-name'] ) )
                $faceted .= '<h5>'. $this_faceted_filter['facet-name'] .'</h5>';

            $meta_values = apply_filters( 'wppb_get_all_values_for_user_meta', wppb_get_all_values_for_user_meta( $this_faceted_filter['facet-meta'], $wppb_manage_fields ), $this_faceted_filter['facet-meta'], $this_faceted_filters, $wppb_manage_fields);

            $function_name = 'wppb_ul_faceted_'.$this_faceted_filter['facet-type'];
            if( function_exists( $function_name ) )
                $faceted .= $function_name( $this_faceted_filter, $meta_values, $wppb_manage_fields );

            if( $this_faceted_filter['facet-type'] == 'checkboxes' ) {
                if ( !empty($this_faceted_filter['facet-limit']) && is_numeric( trim( $this_faceted_filter['facet-limit'] ) ) && count( $meta_values ) >  intval( trim( $this_faceted_filter['facet-limit'] ) ) ) {
                    $faceted .= '<a href="#" class="show-all-facets">' . __('Show All', 'profile-builder') . '</a>';
                    $faceted .= '<a href="#" class="hide-all-facets" style="display:none;">' . __('Hide', 'profile-builder') . '</a>';
                }
            }

            $faceted .= '</li>';
        }
        $faceted .= '</ul><!-- wppb-faceted-list -->';
        return $faceted;
    }

    return;
}
add_filter( 'mustache_variable_faceted_menus', 'wppb_userlisting_faceted_menus', 10, 4 );

/**
 * Function that creates the filter for checkboxes
 * @param $faceted_filter_options the options for the current filter
 * @return string
 */
function wppb_ul_faceted_checkboxes( $faceted_filter_options, $meta_values, $wppb_manage_fields ){
    $current_value = wppb_ul_get_current_filter_value( $faceted_filter_options['facet-meta'] );

    //sort by country name not country code
    $meta_values = wppb_sort_country_values_by_name( $meta_values, $wppb_manage_fields, $faceted_filter_options );

    //filter meta values before displaying
    $meta_values = apply_filters( 'wppb_filter_meta_values_before_output', $meta_values, $faceted_filter_options );

    if( !empty( $meta_values ) ){
        $filter = '';

        $i = 1;
        foreach( $meta_values as $meta_value => $repetitions ){
            if( !empty( $faceted_filter_options['facet-limit'] ) && is_numeric( trim( $faceted_filter_options['facet-limit'] ) ) && (int)$faceted_filter_options['facet-limit'] < $i )
                $filter .= '<div class="hide-this">';
            else
                $filter .= '<div>';

            $filter .= '<label for="wppb-facet-value-'. Wordpress_Creation_Kit_PB::wck_generate_slug($meta_value) .'"><input type="checkbox" id="wppb-facet-value-'. Wordpress_Creation_Kit_PB::wck_generate_slug($meta_value) .'" class="wppb-facet-checkbox" value="'. esc_attr( $meta_value ) .'" data-current-page="'. esc_attr( wppb_get_query_var('wppb_page') ) .'" data-filter-behaviour="'. esc_attr( $faceted_filter_options['facet-behaviour'] ) .'" data-meta-name="'. esc_attr( $faceted_filter_options['facet-meta'] ) .'" '. wppb_ul_checked( $meta_value, $current_value ) .'>';
            $filter .= esc_html( wppb_ul_facet_value_or_label( $meta_value, $faceted_filter_options, $wppb_manage_fields ) );
            $filter .= '<span class="wppb-facet-checkbox-repetitions">';
            if( apply_filters( 'wppb_ul_show_filter_count', true ) )
                $filter .= ' ('. $repetitions .')';
            $filter .= '</span>';
            $filter .= '</label>';
            $filter .= '</div>';

            $i++;
        }

        return $filter;
    }
    else
        return wppb_get_facet_no_options_message( $faceted_filter_options );
}

/**
 * Function that creates the filter for selects
 * @param $faceted_filter_options the options for the current filter
 * @return string
 */
function wppb_ul_faceted_select($faceted_filter_options, $meta_values, $wppb_manage_fields, $multiple = false ){
    $current_value = wppb_ul_get_current_filter_value( $faceted_filter_options['facet-meta'] );

    //sort by country name not country code
    $meta_values = wppb_sort_country_values_by_name( $meta_values, $wppb_manage_fields, $faceted_filter_options );

    //filter meta values before displaying
    $meta_values = apply_filters( 'wppb_filter_meta_values_before_output', $meta_values, $faceted_filter_options );

    if( !empty( $meta_values ) ){
        $filter = '<select class="wppb-facet-select';
        if( $multiple )
            $filter .= '-multiple';
        $filter .= '" data-filter-behaviour="'. esc_attr( $faceted_filter_options['facet-behaviour'] ) .'" data-current-page="'. esc_attr( wppb_get_query_var('wppb_page') ) .'" data-meta-name="'. esc_attr( $faceted_filter_options['facet-meta'] ) .'"';
        /* only add multiple attr for the expand behaviour. for narrow just have a normal select with a size attribute so it fakes a multiple select. this means we will handle it differently in js */
        if( $multiple && $faceted_filter_options['facet-behaviour'] == 'expand' )
            $filter .= ' multiple ';
        if( $multiple && !empty( $faceted_filter_options['facet-limit'] ) && is_numeric( trim( $faceted_filter_options['facet-limit'] ) ) )
            $filter .= ' size="'.$faceted_filter_options['facet-limit'].'" ';
        $filter .= '>';
        $filter .= '<option value="">'. __( 'Choose...', 'profile-builder' ) .'</option>';
        foreach( $meta_values as $meta_value => $repetitions ){
            $filter .= '<option value="'.esc_attr( $meta_value ).'" '. wppb_ul_selected( $meta_value, $current_value ) .'>'.esc_html( wppb_ul_facet_value_or_label( $meta_value, $faceted_filter_options, $wppb_manage_fields ) );
            if( apply_filters( 'wppb_ul_show_filter_count', true ) )
                $filter .= ' ('. $repetitions .')';
            $filter .= '</option>';
        }
        $filter .= '</select>';

        return $filter;
    }
    else
        return wppb_get_facet_no_options_message( $faceted_filter_options );
}

/**
 * Function that creates the filter for the select multiple facet
 * @param $faceted_filter_options the options for the current filter
 * @return string
 */
function wppb_ul_faceted_select_multiple($faceted_filter_options, $meta_values, $wppb_manage_fields ){
    if( $faceted_filter_options['facet-behaviour'] !== 'expand' ) {
        return wppb_ul_faceted_select($faceted_filter_options, $meta_values, $wppb_manage_fields);
    }

    $current_value = wppb_ul_get_current_filter_value( $faceted_filter_options['facet-meta'] );

    //sort by country name not country code
    $meta_values = wppb_sort_country_values_by_name( $meta_values, $wppb_manage_fields, $faceted_filter_options );

    //filter meta values before displaying
    $meta_values = apply_filters( 'wppb_filter_meta_values_before_output', $meta_values, $faceted_filter_options );

    if( !empty( $meta_values ) ){
        /* initialize the select2 */
        wp_enqueue_script( 'wppb_select2_js', WPPB_PLUGIN_URL .'assets/js/select2/select2.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
        wp_enqueue_style( 'wppb_select2_css', WPPB_PLUGIN_URL .'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION );
        wp_enqueue_script( 'wppb-facet-select-multiple', WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/facet-select-multiple.js', array('wppb_select2_js'), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb-facet-select-multiple-style', WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/facet-select-multiple.css', array(), PROFILE_BUILDER_VERSION );
        wp_localize_script( 'wppb-facet-select-multiple', 'wppb_facet_select_multiple_obj', array( 'placeholder' => __( 'Choose or type in an option...', 'profile-builder' ) ) );
        
        $filter = '<select class="wppb-facet-select-multiple" data-filter-behaviour="'. esc_attr( $faceted_filter_options['facet-behaviour'] ) .'" data-current-page="'. esc_attr( wppb_get_query_var('wppb_page') ) .'" data-meta-name="'. esc_attr( $faceted_filter_options['facet-meta'] ) .'" multiple ';
        if( !empty( $faceted_filter_options['facet-limit'] ) && is_numeric( trim( $faceted_filter_options['facet-limit'] ) ) )
            $filter .= ' size="'.$faceted_filter_options['facet-limit'].'" ';
        $filter .= '>';
        foreach( $meta_values as $meta_value => $repetitions ){
            $filter .= '<option value="'.esc_attr( $meta_value ).'" '. wppb_ul_selected( $meta_value, $current_value ) .'>'.esc_html( wppb_ul_facet_value_or_label( $meta_value, $faceted_filter_options, $wppb_manage_fields ) );
            if( apply_filters( 'wppb_ul_show_filter_count', true ) )
                $filter .= ' ('. $repetitions .')';
            $filter .= '</option>';
        }
        $filter .= '</select>';

        $filter .= '<script type="text/javascript">
            if (window.jQuery) {
                jQuery(function(){ wppbFacetSelectMultipleInit() });
                jQuery(".wppb-facet-select-multiple").on("select2:unselect", function (evt) {
                    if (!evt.params.originalEvent) {
                        return;
                    }
                    evt.params.originalEvent.stopPropagation();
                });
            }
        </script>';

        return $filter;
    }
    else
        return wppb_get_facet_no_options_message( $faceted_filter_options );
}


/**
 * Function that creates the filter for range
 * @param $faceted_filter_options the options for the current filter
 * @return string
 */
function wppb_ul_faceted_range( $faceted_filter_options, $meta_values, $wppb_manage_fields ){
    $filter = '';
    if( !empty( $meta_values ) ) {
        foreach ($meta_values as $value => $count) {
            if (!is_numeric($value))
                unset($meta_values[$value]);
        }

        /* we might have nothing left */
        if( !empty( $meta_values ) ) {
            ksort($meta_values, SORT_NUMERIC);

            $i = 1;
            foreach ($meta_values as $value => $count) {
                if ($i == 1) $first_value = $value;
                if ($i == count($meta_values)) $last_value = $value;
                $i++;
            }

            $first_current_value = $first_value;
            $last_current_value = $last_value;


            $current_value = wppb_ul_get_current_filter_value($faceted_filter_options['facet-meta']);
            if (!empty($current_value)) {
                $current_value = explode('-', $current_value);
                $first_current_value = $current_value[0];
                $last_current_value = $current_value[1];
            }

            if (!isset($first_value) || !isset($last_value) || !isset($first_current_value) || !isset($last_current_value))
                return '';

            //check if jquery has been loaded yet because we need it at this point
            // we're checking if it's not admin because it brakes elementor otherwise.
            if( !wp_script_is('jquery', 'done') && !is_admin() ){
                wp_print_scripts('jquery');
            }


            $filter .= '<div class="wppb-ul-range-values ' . esc_attr($faceted_filter_options['facet-meta']) . '">' . $first_current_value . '-' . $last_current_value . '</div>';
            $filter .= '<div class="wppb-ul-slider-range ' . esc_attr($faceted_filter_options['facet-meta']) . '" value="" data-meta-name="' . esc_attr($faceted_filter_options['facet-meta']) . '" data-filter-behaviour="' . esc_attr($faceted_filter_options['facet-behaviour']) . '" data-current-page="' . esc_attr(wppb_get_query_var('wppb_page')) . '"></div>
            <script type="text/javascript">
                jQuery(function(){
                    wppbRangeFacet( "' . esc_attr($faceted_filter_options['facet-meta']) . '", ' . $first_value . ', ' . $last_value . ', ' . $first_current_value . ', ' . $last_current_value . ' );
                });
            </script>';
        }
    }

    if( $filter == '' )
        $filter = wppb_get_facet_no_options_message( $faceted_filter_options );

    return $filter;

}


/**
 * Function that returns and filters the facet "No options available" message
 * @param $faceted_filter_options the options for the current filter
 * @return string
 */
function wppb_get_facet_no_options_message( $faceted_filter_options ){

    return apply_filters('wppb_facet_no_options_message', __( 'No options available', 'profile-builder' ), $faceted_filter_options );

}


/**
 * Function that creates the filter for search
 * @param $faceted_filter_options the options for the current filter
 * @return string
 */
function wppb_ul_faceted_search( $faceted_filter_options, $meta_values, $wppb_manage_fields ){
    $current_value = wppb_ul_get_current_filter_value( $faceted_filter_options['facet-meta'] );

    $filter  = '<div class="wppb-facet-search-wrap"><label><input type="text" value="'. $current_value .'" class="wppb-facet-search" data-filter-behaviour="'. esc_attr( $faceted_filter_options['facet-behaviour'] ) .'" data-current-page="'. esc_attr( wppb_get_query_var('wppb_page') ) .'" data-meta-name="'. esc_attr( $faceted_filter_options['facet-meta'] ) .'">';
    $filter .= '<button type="submit" class="wppb-search-submit"><span class="screen-reader-text">Search</span></button></label></div>';

    return $filter;

}

/**
 * Function that displays the Label from the Manage fields instead of the database value if we have one
 * @param $meta_value the database value
 * @param $faceted_filter_options the current filter options
 * @param $wppb_manage_fields the Manage Fields options
 * @return string the label if we have any else the database value
 */
function wppb_ul_facet_value_or_label( $meta_value, $faceted_filter_options, $wppb_manage_fields ){
    //cast to string
    $meta_value = (string)$meta_value;
    $returned_value = $meta_value;
    if( !empty( $wppb_manage_fields ) ){
        foreach( $wppb_manage_fields as $field ){
            if( $field['meta-name'] == $faceted_filter_options['facet-meta'] ){
                if( !empty( $field['labels'] ) ){
                    $field_values = array_map('trim', explode(',', $field['options']));
                    $field_labels = array_map('trim', explode(',', $field['labels']));

                    if( $field['field'] == 'Checkbox' || $field['field'] == 'Select (Multiple)' ){
                        $meta_values = array_map( 'trim', explode(',', $meta_value ) );
                    }

                    if ( !empty($field_values) ) {
                        foreach ($field_values as $key => $value) {
                            if ($value === $meta_value) {
                                if (isset($field_labels[$key])) {
                                    $returned_value = $field_labels[$key];
                                    break;
                                }
                            }

                            if( !empty( $meta_values ) ){
                                if( in_array( $value, $meta_values ) ){
                                    $returned_values[] = $field_labels[$key];
                                }
                            }
                        }

                        if( !empty( $returned_values ) ){
                            $returned_value = implode( ',', $returned_values );
                        }
                    }
                } else {
                    if( $field['field'] == 'Select (Country)' ){

                        $country_array = wppb_country_select_options( 'userlisting' );

                        if ( array_key_exists( $meta_value, $country_array ) ){
                            $returned_value = $country_array[$meta_value];
                        }

                    } else if ($field['field'] == 'Select (CPT)')
                        $returned_value = get_the_title($meta_value);
                }
            }
        }
    }

    /* for user role grab the labels from the wp_roles global */
    global $wpdb;
    if( $faceted_filter_options['facet-meta'] ==  $wpdb->get_blog_prefix().'capabilities' ){
        global $wp_roles;
        if( !empty( $wp_roles->roles[$meta_value]['name'] ) ){
            $returned_value = $wp_roles->roles[$meta_value]['name'];
        }
    }

    return apply_filters('wppb_ul_facet_value_or_label', $returned_value, $meta_value, $faceted_filter_options, $wppb_manage_fields);
}

/**
 * Function that gets the value for a filter from the url
 * @param $filter_name the neame for the filter
 * @return string
 */
function wppb_ul_get_current_filter_value( $filter_name ){
    if( !empty( $_GET['ul_filter_'. $filter_name] ) )
        $current_value = stripslashes( sanitize_text_field( $_GET['ul_filter_'. $filter_name] ) );
    else
        $current_value = '';

    return $current_value;
}

/**
 * Function that chacks if the current value is checked
 * @param $value current value
 * @param $compare compared against
 * @return string
 */
function wppb_ul_checked( $value, $compare ){
    if( !empty( $compare ) ) {
        $compare = explode('||', $compare);
        if (in_array($value, $compare))
            return 'checked';
        else
            return '';
    }
}

/**
 * Function that chacks if the current value is selected
 * @param $value current value
 * @param $compare compared against
 * @return string
 */
function wppb_ul_selected( $value, $compare ){
    if( !empty( $compare ) ) {
        $compare = explode('||', $compare);
        if (in_array($value, $compare))
            return 'selected';
        else
            return '';
    }
}

function wppb_ul_faceted_remove( $faceted_filters_options, $wppb_manage_fields ){
    $filter = '';
    if( !empty( $faceted_filters_options ) ){
        $filter .= '<ul id="wppb-remove-facets-container">';
        $have_filters = array();
        foreach( $faceted_filters_options as $faceted_filter_options ){
            if( isset( $_GET['ul_filter_'.$faceted_filter_options['facet-meta']]  ) ) {
                $have_filters[] = $faceted_filter_options['facet-meta'];
                $filter_values = explode( '||', stripslashes( sanitize_text_field( $_GET['ul_filter_'.$faceted_filter_options['facet-meta']] ) ) );
                foreach( $filter_values as $filter_value ) {
                    $filter .= '<li>';
                    $filter .= '<a href="#" class="wppb-remove-facet" data-meta-name="' . esc_attr($faceted_filter_options['facet-meta']) . '" data-meta-value="' . esc_attr($filter_value) . '" data-current-page="' . esc_attr(wppb_get_query_var('wppb_page')) . '">' . $faceted_filter_options['facet-name'] . ': ' . esc_html(  wppb_ul_facet_value_or_label( $filter_value, $faceted_filter_options, $wppb_manage_fields ) ) . '</a>';
                    $filter .= '</li>';
                }
            }
        }

        if( $have_filters ){
            $filter .= '<li>';
            $filter .= '<a href="#" class="wppb-remove-all-facets" data-all-filters="'. implode(',', $have_filters ) .'" data-current-page="' . esc_attr(wppb_get_query_var('wppb_page')) . '">' . __( 'Remove All Filters', 'profile-builder' ) . '</a>';
            $filter .= '</li>';
        }

        $filter .= '</ul>';
    }
    return $filter;
}

/**
 * Function that returns all the meta values for a meta key in the usermeta table sorted and unique
 * @param $meta_key
 * @return array
 */
function wppb_get_all_values_for_user_meta( $meta_key, $wppb_manage_fields ){
    $results = array();
    if( !empty( $meta_key ) ) {
        global $wpdb;

        // handle the user roles separately
        if ( $meta_key == $wpdb->get_blog_prefix().'capabilities' ){
            $user_count = count_users('time');
            $results = $user_count["avail_roles"];
            foreach ($results as $key => $value){
                if ($value == 0){
                    unset($results[$key]);
                }
            }
            uksort($results, "strcasecmp");
            return $results;
        }

        global $all_queried_user_ids_string;

        $gloabl_filter_ids_name = $meta_key.'_user_ids';
        global ${$gloabl_filter_ids_name};

        //for custom meta keys or repeaters we use LIKE instead
        $compare_key = wppb_ul_determine_compare_key_arg( $meta_key );

        if( !empty( $all_queried_user_ids_string ) ){
            $query_string = "
                    SELECT meta_value FROM {$wpdb->usermeta}
                    WHERE meta_key ".$compare_key." '%s'
                    AND meta_value != ''
                ";

            if( !empty( $$gloabl_filter_ids_name ) ) {
                $partial_ids = $$gloabl_filter_ids_name;
                $query_string .= " AND user_id IN ($partial_ids)";
            }
            else
                $query_string .= " AND user_id IN ($all_queried_user_ids_string)";

            if( $compare_key === 'LIKE' )
                $meta_key .= '%';

            $results = $wpdb->get_col($wpdb->prepare( $query_string, $meta_key ));

            /* separate values in database for checkboxes */
            if( wppb_check_if_field_is_multiple_value_from_meta_name( $meta_key, $wppb_manage_fields ) ) {
                if( !empty( $results ) ){
                    $new_keys = array();
                    foreach( $results as $key => $value ){
                        if( strpos( $value, ',' ) !== false ){
                            $value = explode( ',', $value );
                            unset( $results[$key] );
                            $new_keys = array_merge( $new_keys, $value );
                        }
                    }
                    $results = array_merge( $results, $new_keys);
                }
            }

            $results = array_count_values($results);
            uksort($results, "strcasecmp");
        }
    }
    return $results;
}

/**
 * Function that returns the search field
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_extra_search_all_fields( $value, $name, $children, $extra_info ){
	$userlisting_settings = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_page_settings', true );
	$set_new_sorting_order = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );

	$searchText = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );

	if ( isset($_REQUEST['searchFor'] ) )
		if ( sanitize_text_field( $_REQUEST['searchFor'] ) != $searchText )
			$searchText = sanitize_text_field( $_REQUEST['searchFor'] );

	$setSortingCriteria = ( isset( $userlisting_settings[0]['default-sorting-criteria'] ) ? $userlisting_settings[0]['default-sorting-criteria'] : 'login' );
	$setSortingCriteria = ( isset( $_REQUEST['setSortingCriteria'] ) ? sanitize_text_field( $_REQUEST['setSortingCriteria'] ) : $setSortingCriteria );

	$setSortingOrder = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );
	$setSortingOrder = ( isset( $_REQUEST['setSortingOrder'] ) ? sanitize_text_field( $_REQUEST['setSortingOrder'] ) : $setSortingOrder );

	return '
		<form method="post" action="'.esc_url( add_query_arg( array( 'wppb_page' => 1, 'setSortingCriteria' => $setSortingCriteria, 'setSortingOrder' => $setSortingOrder ), wppb_remove_query_arg( 'wppb_page', wppb_curpageurl() ) ) ).'" class="wppb-search-users wppb-user-forms">
            <div class="wppb-search-users-wrap">
                <input onfocus="if(this.value == \''.esc_attr( $searchText ).'\'){this.value = \'\';}" type="text" onblur="if(this.value == \'\'){this.value=\''.esc_attr( $searchText ).'\';}" id="wppb-search-fields" name="searchFor" title="'. esc_attr( $searchText ) .'" value="'. esc_attr( $searchText ).'" />
		        <input type="hidden" name="action" value="searchAllFields" />
		        <input type="submit" name="searchButton" class="wppb-search-button" value="'.__( 'Search', 'profile-builder' ).'" />
			    <a class="wppb-clear-results" href="'.wppb_clear_results().'">'.__( 'Clear Results', 'profile-builder' ).'</a>
		    </div>
		</form>';
}
add_filter( 'mustache_variable_extra_search_all_fields', 'wppb_userlisting_extra_search_all_fields', 10, 4 );

/**
 * Function that returns the number of users
 *
 * @since v.2.3.3
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_user_count( $value, $name, $children, $extra_values ){
	global $totalUsers;
	return $totalUsers;
}
add_filter('mustache_variable_user_count','wppb_userlisting_user_count', 10,4);

/**
 * Function that returns the avatar or gravatar (based on what is set)
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_avatar_or_gravatar( $value, $name, $children, $extra_information ){
    // Handles the display of {{{bp_avatar}}} when buddypress addon is enabled
    // because when a user creates a new custom userlisting the template still displays {{{avatar_or_gravatar}}} by default.
    if ( function_exists( 'wppb_bdp_handle_tag_bp_avatar' ) ){
        $bp_avatar = apply_filters( 'mustache_variable_bp_avatar', $value, $name, $children, $extra_information );
        $meta_display_name = wppb_userlisting_show_default_user_fields( $value, 'meta_display_name', $children, $extra_information );
        return '<img src="' . $bp_avatar . '" class="avatar" width="50" height="50" alt="Profile picture of ' . $meta_display_name . '">';
    }

	$form_id = ( ! empty( $extra_information['userlisting_form_id'] ) )
		? $extra_information['userlisting_form_id']
		: 0;
	$this_form_settings = get_post_meta( $form_id, 'wppb_ul_page_settings', true );

	$all_userlisting_avatar_size = apply_filters( 'all_userlisting_avatar_size', ( isset( $this_form_settings[0]['avatar-size-all-userlisting'] ) ? (int)$this_form_settings[0]['avatar-size-all-userlisting'] : 100 ) );
	$single_userlisting_avatar_size = apply_filters( 'single_userlisting_avatar_size', ( isset( $this_form_settings[0]['avatar-size-single-userlisting'] ) ? (int)$this_form_settings[0]['avatar-size-single-userlisting'] : 100 ) );

	$userID = wppb_get_query_var( 'username' );

	$user_info = ( empty( $userID ) ? get_userdata( $extra_information['user_id'] ) : get_userdata( $userID ) );
	$avatar_size = ( empty( $userID ) ? $all_userlisting_avatar_size : $single_userlisting_avatar_size );
	$avatar_crop = apply_filters( 'all_userlisting_avatar_crop', true, $userID );

	$avatar_or_gravatar = get_avatar( (int)$user_info->data->ID, $avatar_size );

	$wp_upload_array = wp_upload_dir();

	if ( strpos( $avatar_or_gravatar, $wp_upload_array['baseurl'] ) ){
		wppb_resize_avatar( (int)$user_info->data->ID, $avatar_size, $avatar_crop );
		$avatar_or_gravatar = get_avatar( (int)$user_info->data->ID, $avatar_size );
	}

	return apply_filters( 'wppb_userlisting_extra_avatar_or_gravatar', $avatar_or_gravatar, $user_info, $avatar_size, $userID );
}
add_filter( 'mustache_variable_avatar_or_gravatar', 'wppb_userlisting_avatar_or_gravatar', 10, 4 );



/**
 * Remove certain actions from post list view
 *
 * @since v.2.0
 *
 * @param array $actions
 *
 * return array
 */
function wppb_remove_ul_view_link( $actions ){
	global $post;

	if ( $post->post_type == 'wppb-ul-cpt' ){
		unset( $actions['view'] );

		if ( wppb_get_post_number ( $post->post_type, 'singular_action' ) )
			unset( $actions['trash'] );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'wppb_remove_ul_view_link', 10, 1 );


/**
 * Remove certain bulk actions from post list view
 *
 * @since v.2.0
 *
 * @param array $actions
 *
 * return array
 */
function wppb_remove_trash_bulk_option_ul( $actions ){
	global $post;
	if( !empty( $post ) ){
		if ( $post->post_type == 'wppb-ul-cpt' ){
			unset( $actions['view'] );

			if ( wppb_get_post_number ( $post->post_type, 'bulk_action' ) )
				unset( $actions['trash'] );
		}
	}

	return $actions;
}
add_filter( 'bulk_actions-edit-wppb-ul-cpt', 'wppb_remove_trash_bulk_option_ul' );


/**
 * Function to hide certain publishing options
 *
 * @since v.2.0
 *
 */
function wppb_hide_ul_publishing_actions(){
	global $post;

	if ( $post->post_type == 'wppb-ul-cpt' ){
		echo '<style type="text/css">#misc-publishing-actions, #minor-publishing-actions{display:none;}</style>';

		$ul = get_posts( array( 'posts_per_page' => -1, 'post_status' => apply_filters ( 'wppb_check_singular_ul_form_publishing_options', array( 'publish' ) ) , 'post_type' => 'wppb-ul-cpt' ) );
		if ( count( $ul ) == 1 )
			echo '<style type="text/css">#major-publishing-actions #delete-action{display:none;}</style>';
	}
}
add_action('admin_head-post.php', 'wppb_hide_ul_publishing_actions');
add_action('admin_head-post-new.php', 'wppb_hide_ul_publishing_actions');


/**
 * Add custom columns to listing
 *
 * @since v.2.0
 *
 * @param array $columns
 * @return array $columns
 */
function wppb_add_extra_column_for_ul( $columns ){
	$columns['ul-shortcode'] = __( 'Shortcode', 'profile-builder' );

	return $columns;
}
add_filter( 'manage_wppb-ul-cpt_posts_columns', 'wppb_add_extra_column_for_ul' );


/**
 * Add content to the displayed column
 *
 * @since v.2.0
 *
 * @param string $column_name
 * @param integer $post_id
 * @return void
 */
function wppb_ul_custom_column_content( $column_name, $post_id ){
	if( $column_name == 'ul-shortcode' ){
		$post = get_post( $post_id );

		if( empty( $post->post_title ) )
			$post->post_title = __( '(no title)', 'profile-builder' );

        echo "<input readonly spellcheck='false' type='text' class='wppb-shortcode input' value='[wppb-list-users name=\"" . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) ) . "\"]' />";
	}
}
add_action("manage_wppb-ul-cpt_posts_custom_column",  "wppb_ul_custom_column_content", 10, 2);


/**
 * Add side metaboxes
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_ul_content(){
	global $post;

	$form_shortcode = trim( Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) );
	if ( $form_shortcode == '' )
		echo '<p><em>' . esc_html__( 'The shortcode will be available after you publish this form.', 'profile-builder' ) . '</em></p>';
	else{
        echo '<p>' . esc_html__( 'Use this shortcode on the page you want the form to be displayed:', 'profile-builder' );
        echo '<br/>';
        echo "<textarea readonly spellcheck='false' class='wppb-shortcode textarea'>[wppb-list-users name=\"" . esc_attr( $form_shortcode ) . "\"]</textarea>";
        echo '</p><p>';
        echo wp_kses_post( __( '<span style="color:red;">Note:</span> changing the form title also changes the shortcode!', 'profile-builder' ) );
        echo '</p>';

        echo '<h4>'. esc_html__('Extra shortcode parameters', 'profile-builder') .'</h4>';

        echo '<a href="wppb-extra-shortcode-parameters" class="wppb-open-modal-box">' . esc_html__( "View all extra shortcode parameters", "profile-builder" ) . '</a>';

        echo '<div id="wppb-extra-shortcode-parameters" title="' . esc_html__( "Extra shortcode parameters", "profile-builder" ) . '" class="wppb-modal-box">';

        	echo '<p>';
	        echo '<strong>meta_key="key_here"<br /> meta_value="value_here"</strong> - '. esc_html__( 'displays users having a certain meta-value within a certain (extra) meta-field', 'profile-builder' );
	        echo '<br/><br/>'.esc_html__( 'Example:', 'profile-builder' ).'<br/>';
	        echo '<strong>[wppb-list-users name="' . esc_attr( $form_shortcode ) . '" meta_key="skill" meta_value="Photography"]</strong><br/><br/>';
	        echo esc_html__( 'Remember though, that the field-value combination must exist in the database.', 'profile-builder' );
	        echo '</p>';

	        echo '<hr />';

	        echo '<p>';
	        echo '<strong>include="user_id_1, user_id_2"</strong> - '. esc_html__( 'displays only the users that you specified the user_id for', 'profile-builder' );
	        echo '</p>';

	        echo '<hr />';

	        echo '<p>';
	        echo '<strong>exclude="user_id_1, user_id_2"</strong> - '. esc_html__( 'displays all users except the ones you specified the user_id for', 'profile-builder' );
	        echo '</p>';

        echo '</div>';
    }
}

function wppb_ul_side_box(){
	add_meta_box( 'wppb-ul-side', __( 'Form Shortcode', 'profile-builder' ), 'wppb_ul_content', 'wppb-ul-cpt', 'side', 'low' );
}
add_action( 'add_meta_boxes', 'wppb_ul_side_box' );



/**
 * Function that manages the Userlisting CPT
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_manage_ul_cpt(){
	global $wp_roles;
	//$default_wp_role = trim( get_option( 'default_role' ) );
	$available_roles = $sorting_order = $sorting_criteria = array();

	// Set role
	$available_roles[] = '%*%*';
	foreach ( $wp_roles->roles as $slug => $role )
		$available_roles[] = '%'.wppb_prepare_wck_labels( $role['name'] ) .'%'.$slug;

	// Set sorting criteria
	$sorting_criteria[] = '%'.__( 'Username', 'profile-builder' ).'%login';
	$sorting_criteria[] = '%'.__( 'Email', 'profile-builder' ).'%email';
	$sorting_criteria[] = '%'.__( 'User ID', 'profile-builder' ).'%user_id';
	$sorting_criteria[] = '%'.__( 'Website', 'profile-builder' ).'%url';
	$sorting_criteria[] = '%'.__( 'Biographical Info', 'profile-builder' ).'%bio';
	$sorting_criteria[] = '%'.__( 'Registration Date', 'profile-builder' ).'%registered';
	$sorting_criteria[] = '%'.__( 'Firstname', 'profile-builder' ).'%firstname';
	$sorting_criteria[] = '%'.__( 'Lastname', 'profile-builder' ).'%lastname';
	$sorting_criteria[] = '%'.__( 'Display Name', 'profile-builder' ).'%nicename';
    $sorting_criteria[] = '%'.__( 'Nickname', 'profile-builder' ).'%nickname';
	$sorting_criteria[] = '%'.__( 'Number of Posts', 'profile-builder' ).'%post_count';
    $sorting_criteria[] = '%'.__( 'Role', 'profile-builder' ).'%role';

	// Default contact methods were removed in WP 3.6. A filter dictates contact methods.
	if ( apply_filters( 'wppb_remove_default_contact_methods', get_site_option( 'initial_db_version' ) < 23588 ) ){
		$sorting_criteria[] = '%'.__( 'Aim', 'profile-builder' ).'%aim';
		$sorting_criteria[] = '%'.__( 'Yim', 'profile-builder' ).'%yim';
		$sorting_criteria[] = '%'.__( 'Jabber', 'profile-builder' ).'%jabber';
	}

	$exclude_fields_from_settings = apply_filters( 'wppb_exclude_field_list_userlisting_settings', array( 'Default - Name (Heading)', 'Default - Contact Info (Heading)', 'Default - About Yourself (Heading)', 'Default - Username', 'Default - First Name', 'Default - Last Name', 'Default - Nickname', 'Default - E-mail', 'Default - Website', 'Default - AIM', 'Default - Yahoo IM', 'Default - Jabber / Google Talk', 'Default - Password', 'Default - Repeat Password', 'Default - Biographical Info', 'Default - Blog Details', 'Default - Display name publicly as', 'Heading' ) );

    global $wppb_manage_fields;
    if( !isset( $wppb_manage_fields ) )
        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
    if( !empty( $wppb_manage_fields ) && is_array( $wppb_manage_fields ) ) {
        foreach ($wppb_manage_fields as $key => $value) {
            if (!in_array($value['field'], $exclude_fields_from_settings) && !empty($value['meta-name']))
                $sorting_criteria[] = '%' . wppb_prepare_wck_labels( $value['field-title'] ) . '%' . $value['meta-name'];
        }
    }

	$sorting_criteria[] = '%'.__( 'Random (very slow on large databases > 10K user)', 'profile-builder' ).'%RAND()';

	// Set sorting order
	$sorting_order[] = '%'.__( 'Ascending', 'profile-builder' ).'%asc';
	$sorting_order[] = '%'.__( 'Descending', 'profile-builder' ).'%desc';


	// set up the fields array
	$settings_fields = array(
		array( 'type' => 'checkbox', 'slug' => 'roles-to-display', 'title' => __( 'Roles to Display', 'profile-builder' ), 'options' => $available_roles, 'default' => '*', 'description' => __( 'Restrict the userlisting to these selected roles only<br/>If not specified, defaults to all existing roles', 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'number-of-userspage', 'title' => __( 'Number of Users/Page', 'profile-builder' ), 'default' => '5', 'description' => __( 'Set the number of users to be displayed on every paginated part of the all-userlisting', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'default-sorting-criteria', 'title' => __( 'Default Sorting Criteria', 'profile-builder' ), 'options' => apply_filters( 'wppb_default_sorting_criteria', $sorting_criteria ), 'default' => 'login', 'description' => __( 'Set the default sorting criteria<br/>This can temporarily be changed for each new session', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'default-sorting-order', 'title' => __( 'Default Sorting Order', 'profile-builder' ), 'options' => $sorting_order, 'default' => 'asc', 'description' => __( 'Set the default sorting order<br/>This can temporarily be changed for each new session', 'profile-builder' ) ),
		array( 'type' => 'number', 'slug' => 'avatar-size-all-userlisting', 'title' => __( 'Avatar Size (All-userlisting)', 'profile-builder' ), 'default' => '40', 'min-number-value' => '10', 'max-number-value' => '400', 'description' => __( 'Set the avatar size on the all-userlisting only', 'profile-builder' ) ),
		array( 'type' => 'number', 'slug' => 'avatar-size-single-userlisting', 'title' => __( 'Avatar Size (Single-userlisting)', 'profile-builder' ), 'default' => '60', 'min-number-value' => '10', 'max-number-value' => '800',  'description' => __( 'Set the avatar size on the single-userlisting only', 'profile-builder' ) ),
		array( 'type' => 'checkbox', 'slug' => 'visible-only-to-logged-in-users', 'title' => __( 'Visible only to logged in users?', 'profile-builder' ), 'options' => array( '%'.__( 'Yes', 'profile-builder' ).'%yes' ), 'description' => __( 'The userlisting will only be visible only to the logged in users', 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'visible-to-following-roles', 'title' => __( 'Visible to following Roles', 'profile-builder' ), 'options' => $available_roles, 'default' => '*', 'description' => __( 'The userlisting will only be visible to the following roles', 'profile-builder' ) ),
	);

	// set up the box arguments
	$args = array(
		'metabox_id' => 'wppb-ul-settings-args',
		'metabox_title' => __( 'Userlisting Settings', 'profile-builder' ),
		'post_type' => 'wppb-ul-cpt',
		'meta_name' => 'wppb_ul_page_settings',
		'meta_array' => $settings_fields,
		'sortable' => false,
		'single' => true
	);
	new Wordpress_Creation_Kit_PB( $args );

    $facet_types = array( '%Checkboxes%checkboxes', '%Select%select', '%Select Multiple%select_multiple', '%Range%range', '%Search%search' );
    $facet_meta = array();
    $exclude_fields_from_facet_menus = apply_filters( 'wppb_exclude_field_list_userlisting_facet_menu_settings', array() );
    if( !empty( $wppb_manage_fields ) && is_array( $wppb_manage_fields ) ) {
        foreach ($wppb_manage_fields as $key => $value) {
            if (!in_array($value['field'], $exclude_fields_from_facet_menus) && !empty($value['meta-name']))
                $facet_meta[] = '%' . wppb_prepare_wck_labels( $value['field-title'] ) . '%' . $value['meta-name'];
        }
    }

    /* add roles to facets options */
    global $wpdb;
    $facet_meta[] = '%Role%'.$wpdb->get_blog_prefix().'capabilities';

    // set up the fields array for faceted
    $settings_fields = array(
        array( 'type' => 'text', 'slug' => 'facet-name', 'title' => __( 'Label', 'profile-builder' ), 'required' => true, 'description' => __( 'Choose the facet name that appears on the frontend', 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'facet-type', 'title' => __( 'Facet Type', 'profile-builder' ), 'options' => $facet_types, 'default' => 'checkboxes', 'description' => __( 'Choose the facet menu type', 'profile-builder' ) ),
        array( 'type' => 'select-2', 'slug' => 'facet-meta', 'title' => __( 'Facet Meta', 'profile-builder' ), 'options' => apply_filters( 'wppb_userlisting_facet_meta', $facet_meta, $wppb_manage_fields ), 'default-option' => true, 'description' => __( 'Choose the meta field for the facet menu. If you want to use a repeater meta or a meta outisde Profile Builder just type the value and press enter.', 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'facet-behaviour', 'title' => __( 'Behaviour', 'profile-builder' ), 'options' => array( '%'. __('Narrow the results', 'profile-builder') .'%narrow', '%'. __('Expand the results', 'profile-builder') .'%expand' ), 'description' => __( 'Choose how multiple selections affect the results', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'facet-limit', 'title' => __( 'Visible choices', 'profile-builder' ), 'description' => __( 'Show a toggle link after this many choices. Leave blank for all', 'profile-builder' ) ),
    );

    // set up the box arguments
    $args = array(
        'metabox_id' => 'wppb-ul-faceted-args',
        'metabox_title' => __( 'Faceted Menus', 'profile-builder' ),
        'post_type' => 'wppb-ul-cpt',
        'meta_name' => 'wppb_ul_faceted_settings',
        'meta_array' => $settings_fields
    );
    new Wordpress_Creation_Kit_PB( $args );

    /* start search field setting box */
    $search_fields = array( '%User Login%user_login', '%User Email%user_email', '%User Website%user_url' );
    $search_defaults = array( 'user_login', 'user_email', 'user_url' );
    if( !empty( $wppb_manage_fields ) && is_array( $wppb_manage_fields ) ) {
        foreach ($wppb_manage_fields as $key => $value) {
            if (!empty($value['meta-name'])) {
                $search_fields[] = '%' . wppb_prepare_wck_labels( $value['field-title'] ) . '%' . $value['meta-name'];
                $search_defaults[] = $value['meta-name'];
            }
        }
    }
    $settings_fields = array(
        array( 'type' => 'checkbox', 'slug' => 'search-fields', 'options' => apply_filters('wppb_userlisting_search_all_fields', $search_fields, $wppb_manage_fields), 'default' => $search_defaults,  'title' => __( 'Search Fields', 'profile-builder' ), 'description' => __( 'Choose the fields in which the Search Field will look in', 'profile-builder' ) ),
    );
    // set up the box arguments
    $args = array(
        'metabox_id' => 'wppb-ul-search-settings',
        'metabox_title' => __( 'Search Settings', 'profile-builder' ),
        'post_type' => 'wppb-ul-cpt',
        'meta_name' => 'wppb_ul_search_settings',
        'meta_array' => $settings_fields,
        'single'  => true
    );
    new Wordpress_Creation_Kit_PB( $args );
    /* end search field setting box */


    /* Userlisting Themes Selection Metabox */
    $userlisting_themes = wppb_get_ul_themes_data();

    // set up the box arguments
    $args = array(
        'metabox_id' => 'wppb-ul-themes-settings',
        'metabox_title' => __( 'Themes: add style to your user listing section', 'profile-builder' ),
        'post_type' => 'wppb-ul-cpt',
        'meta_name' => 'wppb_ul_themes_settings',
        'meta_array' => wppb_render_themes_metabox_content( $userlisting_themes ),
        'single' => true
    );
    new Wordpress_Creation_Kit_PB( $args );

}
add_action( 'admin_init', 'wppb_manage_ul_cpt', 1 );


/**
 * Function that returns the Themes data
 *
 */
function wppb_get_ul_themes_data() {

    $userlisting_themes= array(
        array(
            'id' => 'default',
            'name' => 'DEFAULT',
            'status' => wppb_set_ul_theme_status( 'default'),
            'image_url' => WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/images/default-theme-preview.png',
            'theme_templates' => array(
                'all_users' =>  ( isset( $_GET['post'] )) ? get_post_meta( sanitize_text_field( $_GET['post'] ) , 'wppb-ul-default-all-users-template', true ) : wppb_generate_allUserlisting_content(),
                'single_user' =>  ( isset( $_GET['post'] )) ? get_post_meta( sanitize_text_field( $_GET['post'] ) , 'wppb-ul-default-single-user-template', true ) : wppb_generate_singleUserlisting_content()
            ),
            'users_per_page' => '10',
            'all_users_avatar_size' => '40',
            'single_user_avatar_size' => '60'
        ),
        array(
            'id' => 'tablesi',
            'name' => 'TABLESI',
            'status' => wppb_set_ul_theme_status( 'tablesi' ),
            'image_url' => WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/images/tablesi-theme-preview.png',
            'theme_templates' => array(
                'all_users' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/all-userlisting-tablesi.php'),
                'single_user' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/single-userlisting-tablesi.php')
            ),
            'users_per_page' => '10',
            'all_users_avatar_size' => '60',
            'single_user_avatar_size' => '220'
        ),
        array(
            'id' => 'vergrid',
            'name' => 'VERGRID',
            'status' => wppb_set_ul_theme_status( 'vergrid' ),
            'image_url' => WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/images/vergrid-theme-preview.png',
            'theme_templates' => array(
                'all_users' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/all-userlisting-vergrid.php'),
                'single_user' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/single-userlisting-vergrid.php')
            ),
            'users_per_page' => '6',
            'all_users_avatar_size' => '230',
            'single_user_avatar_size' => '270'
        ),
        array(
            'id' => 'boxomo',
            'name' => 'BOXOMO',
            'status' => wppb_set_ul_theme_status( 'boxomo' ),
            'image_url' => WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/images/boxomo-theme-preview.png',
            'theme_templates' => array(
                'all_users' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/all-userlisting-boxomo.php'),
                'single_user' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/single-userlisting-boxomo.php')
            ),
            'users_per_page' => '6',
            'all_users_avatar_size' => '270',
            'single_user_avatar_size' => '270'
        ),
        array(
            'id' => 'glimplist',
            'name' => 'GLIMPLIST',
            'status' => wppb_set_ul_theme_status( 'glimplist' ),
            'image_url' => WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/images/glimplist-theme-preview.png',
            'theme_templates' => array(
                'all_users' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/all-userlisting-glimplist.php'),
                'single_user' => file_get_contents( WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/templates/single-userlisting-glimplist.php')
            ),
            'users_per_page' => '8',
            'all_users_avatar_size' => '400',
            'single_user_avatar_size' => '500'
        ),
    );

    return apply_filters( 'wppb_userlisting_themes', $userlisting_themes );
}


/**
 * Function that returns the Themes Metabox Content
 *
 */
function wppb_render_themes_metabox_content( $userlisting_themes ) {

    if ( empty( $userlisting_themes ) )
        return;

    $current_post = ( isset( $_GET['post'] ) ) ? sanitize_text_field( $_GET['post'] ) : 'new-post';

    wp_register_style( 'wppb_userlisting_themes_settings_style', WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/css/userlisting-themes-settings-style.css', array(),PROFILE_BUILDER_VERSION );
    wp_enqueue_style( 'wppb_userlisting_themes_settings_style' );

    wp_enqueue_script( 'wppb_userlisting_themes_settings_js', WPPB_PAID_PLUGIN_URL.'add-ons/user-listing/ul-themes/js/userlisting-themes-settings.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );

    $output = '<div class="ul-themes-browser">';

    foreach ( $userlisting_themes as $theme ) {

        if ( $theme['status'] == 'active') {
            $status = ' active';
            $title = '<strong>Active: </strong> '. $theme['name'];
        }
        else {
            $status = '';
            $title = $theme['name'];
        }

        $output .= '                    
                        <div class="wppb-ul-theme'. $status .'" id="'. $theme['id'] .'">
                            <div class="wppb-ul-theme-screenshot">
                                <img src="' . $theme['image_url'] . '">
                                 <div class="wppb-ul-theme-preview" id="'. $theme['id'] .'-info">Preview</div>
                            </div>                                                     
                            
                            <div class="wppb-ul-theme-details">
                            
                                <div class="wppb-ul-theme-title">
                                    <h2>'. $title .'</h2>
                                </div>
                                
                                <div class="wppb-ul-theme-activate">
                                    <button 
                                        type="button" 
                                        class="button activate button-small" 
                                        id="activate-'. $theme['id'] .'" 
                                        data-ajax-url="'. admin_url( 'admin-ajax.php' ) .'" 
                                        data-theme-id="'. $theme['id'] .'"
                                        data-current-post="'. $current_post .'">
                                        Activate
                                    </button>
                                    
                                    <button 
                                        type="button" 
                                        class="button reset button-small" 
                                        id="reset-'. $theme['id'] .'">
                                        Reset Data
                                    </button>
                                </div>
                                

                                
                            </div>
                        </div>                    
                ';

        $output .= '<div id="modal-'. $theme['id'] .'" class="wppb-ul-theme-modal" title="'. $theme['name'] .'">
                        <img class="wppb-ul-theme-preview-image" src="'. $theme['image_url'] .'">
                    </div>';

        $output .= '<div id="modal-reset-'. $theme['id'] .'" class="wppb-ul-theme-reset-modal" title="Reset '. $theme['name'] .' Theme Settings">
                            <div class="wppb-reset-modal-content">
                                <p class="wppb-options-message">Select which settings you want to reset:</p>
                                <div class="wppb-reset-options">
                                    <div class="wppb-options-wrapper" id="'. $theme['id'] .'-options">
                                        <label for="settings-data"><input type="checkbox" name="reset_theme_data" id="settings-data" value="settings_data"> User-Listing Settings</label>
                                        <label for="all-users-template"><input type="checkbox" name="reset_theme_data" id="all-users-template" value="all_users_template"> All-userlisting Template</label>
                                        <label for="single-user-template"><input type="checkbox" name="reset_theme_data" id="single-user-template" value="single_user_template"> Single-userlisting Template</label>
                                        <label for="all-theme-data"><input type="checkbox" name="reset_all_theme_data" id="all-theme-data" value="'. $theme['id'] .'"> All Theme Data</label>
                                    </div>
                                </div>
                                <p class="notice-content"><strong>Note:</strong> The settings on the page will be replaced with your active User-Listing Theme\'s Default settings, according to your choice from the options above.</p>
                            </div>
                            <div class="wppb-reset-buttons">
                                <button type="button" class="button cancel-reset" value="modal-reset-'. $theme['id'] .'" >Cancel</button>
                                <button 
                                    type="button" 
                                    class="button button-primary confirm-reset" 
                                    data-ajax-url="'. admin_url( 'admin-ajax.php' ) .'" 
                                    data-theme-id="'. $theme['id'] .'"
                                    data-current-post="'. $current_post .'">
                                    Confirm
                                </button>
                            </div>

                    </div>';

    }

    $output .= '</div>';

    return $output;
}


/**
 * Function that sends an Ajax response with the selected data to fill in the necessary fields
 *
 */
function get_new_templates_data() {

    if ( empty( $_GET['theme_id'] ) || empty( $_GET['current_post'] ) )
        die("Something went wrong!");

    $new_theme_id = sanitize_text_field( $_GET['theme_id'] );
    $current_post = sanitize_text_field( $_GET['current_post'] );

    $new_theme_data = wppb_get_newly_activated_theme_data( $current_post, $new_theme_id );

    update_option( 'wppb_ul_active_theme', $new_theme_id );
    update_option( 'all_users_wp_theme_file', $new_theme_data['all_users_wp_theme_file'] );
    update_option( 'single_user_wp_theme_file', $new_theme_data['single_user_wp_theme_file'] );

    echo json_encode(  !empty( $new_theme_data ) ? $new_theme_data : '' );

    // update db metadata with newly activated theme data
    if ( !empty( $current_post ) && $current_post != 'new-post' )
        wppb_save_ul_theme_data_in_db( $current_post, $new_theme_data );

    die();
}
add_action( 'wp_ajax_get_new_templates_data', 'get_new_templates_data' );


/**
 * Function that sends an Ajax response with the selected Theme default data to fill in the necessary fields
 *
 */
function get_selected_theme_default_data() {

    if ( empty( $_GET['theme_id'] ) || empty( $_GET['current_post'] ) || empty( $_GET['options_to_reset'] ) )
        die("Something went wrong!");

    $theme_id = sanitize_text_field( $_GET['theme_id'] );
    $current_post = sanitize_text_field( $_GET['current_post'] );
    $options_to_reset = $_GET['options_to_reset'];//phpcs:ignore

    $default_data = wppb_get_theme_defaults( $current_post, $theme_id, $options_to_reset );

    echo json_encode(  !empty( $default_data ) ? $default_data : '' );

//    // update db metadata with newly activated theme data
//    if ( !empty( $current_post ) && $current_post != 'new-post' )
//        wppb_save_ul_theme_data_in_db( $current_post, $default_data );

    die();
}
add_action( 'wp_ajax_get_selected_theme_default_data', 'get_selected_theme_default_data' );


function wppb_get_theme_defaults( $current_post, $theme_id, $options_to_reset ) {

    $default_data = array();

    $userlisting_themes = wppb_get_ul_themes_data();
    foreach ( $userlisting_themes as $ul_theme ) {
        if ( $ul_theme['id'] == $theme_id ) {

            if ( in_array( 'settings_data', $options_to_reset ) ) {
                $default_data=array(
                    "users_per_page" => $ul_theme['users_per_page'],
                    "all_users_avatar_size" => $ul_theme['all_users_avatar_size'],
                    "single_user_avatar_size" => $ul_theme['single_user_avatar_size'],
                    "sorting_order" => 'asc',
                    "sorting_criteria" => 'login',
                    "roles_to_display" => '*',
                );
            }

            if ( in_array( 'single_user_template', $options_to_reset ) )
                $default_data['single_user'] = $ul_theme['theme_templates']['single_user'];

            if ( in_array( 'all_users_template', $options_to_reset ) )
                $default_data['all_users'] = $ul_theme['theme_templates']['all_users'];

        }
    }

    $active_wp_theme = wp_get_theme();
    $all_users_wp_theme_file = get_theme_root().'/'. $active_wp_theme->stylesheet .'/profile-builder/userlisting/all-userlisting-'. $theme_id .'-ul-'. $current_post .'.php';
    $single_user_wp_theme_file = get_theme_root().'/'. $active_wp_theme->stylesheet .'/profile-builder/userlisting/single-userlisting-'. $theme_id .'-ul-'. $current_post .'.php';

    if( !empty( $active_wp_theme->stylesheet ) && file_exists( $all_users_wp_theme_file ) )
        $default_data['all_users_wp_theme_file'] = 'yes';
    else $default_data['all_users_wp_theme_file'] = 'no';

    if( !empty( $active_wp_theme->stylesheet ) && file_exists( $single_user_wp_theme_file ) )
        $default_data['single_user_wp_theme_file'] = 'yes';
    else $default_data['single_user_wp_theme_file'] = 'no';

    return $default_data;
}


/**
 * Function that saves newly activated theme data into DB
 * - on Add New View the Post ID is missing therefore it only works on Edit View
 *
 */
function wppb_save_ul_theme_data_in_db( $post_id, $new_theme_data ) {

    $active_theme = get_option('wppb_ul_active_theme');

    if ( empty( $active_theme ) )
        return;

    $all_users_wp_theme_file = get_option('all_users_wp_theme_file');
    $single_user_wp_theme_file = get_option('single_user_wp_theme_file');

    // save User Listing Theme number of users per page
    if ( !empty( $new_theme_data['users_per_page'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-number-of-userspage', $new_theme_data['users_per_page'] );

    // save User Listing Theme sorting data
    if ( !empty( $new_theme_data['sorting_order'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-default-sorting-order', $new_theme_data['sorting_order']);
    if ( !empty( $new_theme_data['sorting_criteria'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-default-sorting-criteria', $new_theme_data['sorting_criteria']);

     // save User Listing Theme roles to display
    if ( !empty( $new_theme_data['roles_to_display'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-roles-to-display', $new_theme_data['roles_to_display'] );

    // save User Listing Theme avatar sizes
    if ( !empty( $new_theme_data['all_users_avatar_size'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-all-users-avatar-size', $new_theme_data['all_users_avatar_size'] );
    if ( !empty( $new_theme_data['single_user_avatar_size'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-single-user-avatar-size', $new_theme_data['single_user_avatar_size'] );

    // save User Listing Theme template (maybe the template was modified by the user)
    if ( !empty( $new_theme_data['all_users'] ) && empty( $all_users_wp_theme_file ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-all-users-template', $new_theme_data['all_users'] );//phpcs:ignore
    if ( !empty( $new_theme_data['single_user'] ) && empty( $single_user_wp_theme_file ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-single-user-template', $new_theme_data['single_user'] );//phpcs:ignore

    update_post_meta( $post_id, 'wppb_ul_page_settings', array( array(
        'number-of-userspage' => $new_theme_data['users_per_page'],
        'avatar-size-all-userlisting' => $new_theme_data['all_users_avatar_size'],
        'avatar-size-single-userlisting' => $new_theme_data['single_user_avatar_size'],
        'roles-to-display' => $new_theme_data['roles_to_display'],
        'default-sorting-order' => $new_theme_data['sorting_order'],
        'default-sorting-criteria' => $new_theme_data['sorting_criteria'],
    ) ) );

    // update UL Templates in DB
    update_post_meta( $post_id, 'wppb-ul-templates', $new_theme_data['all_users'] );
    update_post_meta( $post_id, 'wppb-single-ul-templates', $new_theme_data['single_user'] );

    // update active theme in DB
    update_post_meta( $post_id, 'wppb-ul-active-theme', $active_theme );

    // delete options set on Ajax call
    delete_option('all_users_wp_theme_file');
    delete_option('single_user_wp_theme_file');
    delete_option('wppb_ul_active_theme');
}


/**
 * Function that search and returns the new templates:
 *
 * 1st - from within active WP Theme files (if any present)
 * 2nd - from DB (if there is a saved template)
 * 3rd - from UL Theme files (default template for selected theme)
 *
 */
function wppb_get_newly_activated_theme_data( $post_id, $theme_id ) {
    
    $userlisting_settings = get_post_meta( $post_id, 'wppb_ul_page_settings', true );
    $new_theme_data = array(
        'all_users' => '',
        'single_user' => '',
        'all_users_wp_theme_file' => '',
        'single_user_wp_theme_file' => '',
        'users_per_page' => get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-number-of-userspage', true ),
        'all_users_avatar_size' => get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-all-users-avatar-size', true ),
        'single_user_avatar_size' => get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-single-user-avatar-size', true ),
        'roles_to_display' => get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-roles-to-display', true),
        'sorting_order' => get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-default-sorting-order', true),
        'sorting_criteria' => get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-default-sorting-criteria', true),
    );

    if ( is_array( $new_theme_data['roles_to_display'] ) )
        $new_theme_data['roles_to_display'] = implode(", ", $new_theme_data['roles_to_display'] );

    $active_wp_theme = wp_get_theme();
    $all_users_wp_theme_file = get_theme_root().'/'. $active_wp_theme->stylesheet .'/profile-builder/userlisting/all-userlisting-'. $theme_id .'-ul-'. $post_id .'.php';
    $single_user_wp_theme_file = get_theme_root().'/'. $active_wp_theme->stylesheet .'/profile-builder/userlisting/single-userlisting-'. $theme_id .'-ul-'. $post_id .'.php';

    // load all_users template from the currently active WP Theme (if there is any template file present)
    if( !empty( $active_wp_theme->stylesheet ) && file_exists( $all_users_wp_theme_file ) ) {
        $new_theme_data['all_users'] = file_get_contents( $all_users_wp_theme_file );
        $new_theme_data['all_users_wp_theme_file'] = $all_users_wp_theme_file;
    }
    else { // load all_users template from DB (if saved)
        $modified_all_users_template = get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-all-users-template', true );
        if ( !empty( $modified_all_users_template ) ) {
            $new_theme_data['all_users'] = $modified_all_users_template;
        }
        else { // load all_users default theme template from theme files
            $userlisting_themes = wppb_get_ul_themes_data();
            foreach ( $userlisting_themes as $ul_theme ) {
                if ( $ul_theme['id'] == $theme_id ) {
                    $new_theme_data['all_users'] = $ul_theme['theme_templates']['all_users'];
                }
            }
        }
    }

    // load single_user template from the currently active WP Theme (if there is any template file present)
    if( !empty( $active_wp_theme->stylesheet ) && file_exists( $single_user_wp_theme_file ) ) {
        $new_theme_data['single_user'] = file_get_contents( $single_user_wp_theme_file );
        $new_theme_data['single_user_wp_theme_file'] = $single_user_wp_theme_file;
    }
    else { // load single_user template from DB (if saved)
        $modified_single_user_template = get_post_meta( $post_id, 'wppb-ul-'. $theme_id .'-single-user-template', true );
        if ( !empty( $modified_single_user_template ) ) {
            $new_theme_data['single_user'] = $modified_single_user_template;
        }
        else { // load single_user default theme template from theme files
            $userlisting_themes = wppb_get_ul_themes_data();
            foreach ( $userlisting_themes as $ul_theme ) {
                if ( $ul_theme['id'] == $theme_id ) {
                    $new_theme_data['single_user'] = $ul_theme['theme_templates']['single_user'];
                }
            }
        }
    }

    if ( empty( $new_theme_data['users_per_page'] ) ) {
        if ( $theme_id == 'glimplist' )
            $new_theme_data['users_per_page'] = '8';
        elseif ( $theme_id == 'vergrid' || $theme_id == 'boxomo' )
            $new_theme_data['users_per_page'] = '6';
        elseif ( $theme_id == 'default' || $theme_id == 'tablesi' )
            $new_theme_data['users_per_page'] = '10';
    }

    if ( empty( $new_theme_data['sorting_order'] ) )
        $new_theme_data['sorting_order'] = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );

    if ( empty( $new_theme_data['sorting_criteria'] ) )
        $new_theme_data['sorting_criteria'] = ( isset( $userlisting_settings[0]['default-sorting-criteria'] ) ? $userlisting_settings[0]['default-sorting-criteria'] : 'login' );

    if ( empty( $new_theme_data['roles_to_display'] ) )
        $new_theme_data['roles_to_display'] = ( isset( $userlisting_settings[0]['roles-to-display'] ) ? $userlisting_settings[0]['roles-to-display'] : '*' );

    if ( empty( $new_theme_data['all_users_avatar_size'] ) ) {
        if ( $theme_id == 'default' )
            $new_theme_data['all_users_avatar_size'] = '40';
        elseif ( $theme_id == 'tablesi' )
            $new_theme_data['all_users_avatar_size'] = '60';
        elseif ( $theme_id == 'vergrid' )
            $new_theme_data['all_users_avatar_size'] = '230';
        elseif ( $theme_id == 'boxomo' )
            $new_theme_data['all_users_avatar_size'] = '270';
        elseif ( $theme_id == 'glimplist' )
            $new_theme_data['all_users_avatar_size'] = '400';
    }

    if ( empty( $new_theme_data['single_user_avatar_size'] ) ) {
        if ( $theme_id == 'default' )
            $new_theme_data['single_user_avatar_size'] = '60';
        elseif ( $theme_id == 'tablesi' )
            $new_theme_data['single_user_avatar_size'] = '220';
        elseif ( $theme_id == 'vergrid' || $theme_id == 'boxomo' )
            $new_theme_data['single_user_avatar_size'] = '270';
        elseif ( $theme_id == 'glimplist' )
            $new_theme_data['single_user_avatar_size'] = '500';
    }

    return $new_theme_data;
}


/**
 * Function that returns the theme status
 *
 */
function wppb_set_ul_theme_status( $ul_theme_id ) {

    if ( isset( $_GET['post'] ) ) {
        $post_id = sanitize_text_field( $_GET['post'] );
        $active_theme = get_post_meta( $post_id , 'wppb-ul-active-theme', true );
    }

    // activate Default Theme if no other theme is active
    if ( empty( $active_theme ) && $ul_theme_id == 'default' ) {
        $status = 'active';

        if ( isset( $post_id ) ) {
            $current_all_users_template = get_post_meta( $post_id, 'wppb-ul-templates', true );
            $current_single_users_template = get_post_meta( $post_id, 'wppb-single-ul-templates', true );
            update_post_meta( $post_id, 'wppb-ul-default-all-users-template', $current_all_users_template );
            update_post_meta( $post_id, 'wppb-ul-default-single-user-template', $current_single_users_template );
            update_post_meta( $post_id, 'wppb-ul-active-theme', 'default' );
        }

    }
    elseif ( !empty( $active_theme ) && $ul_theme_id == $active_theme ) {
        $status = 'active';
    }
    else $status = '';

    return $status;
}


/**
 * Function that returns the style for the selected theme
 *
 */
function wppb_apply_userlisting_theme_style( $ul_template, $post_id, $single ) {

    if ( empty( $ul_template ) || empty( $post_id ) || !isset( $single ) )
        return;

    $active_ul_theme = get_post_meta( $post_id, 'wppb-ul-active-theme', true );

    if ( empty( $active_ul_theme ))
        return $ul_template;

    $template_type = ( $single ) ? 'single' : 'all';
    $file_path = WPPB_PAID_PLUGIN_DIR . '/add-ons/user-listing/ul-themes/css/userlisting-'. $template_type .'-'. $active_ul_theme .'-theme-style.css';

    if ( file_exists( $file_path ) )
        $template_styling = '<style>' . file_get_contents( $file_path ) . '</style>';
    else $template_styling = '';

    return $template_styling . $ul_template;
}
add_filter( 'wppb_apply_active_ul_theme_style', 'wppb_apply_userlisting_theme_style', 10, 3 );


/**
 * Function that replaces First-Name with UserName (user_login) if both First and Last names are empty
 *
 */
function wppb_maybe_replace_first_name( $value, $name, $children, $extra_info ) {

    if ( $name == 'meta_first_name' && empty( $value )) {

        $active_ul_theme = get_post_meta( $extra_info['userlisting_form_id'], 'wppb-ul-active-theme', true );

        if ( empty( $active_ul_theme ) || $active_ul_theme == 'default' )
            return $value;

        $user_info = get_userdata( $extra_info['user_id'] );
        $username = $user_info->user_login;
        $last_name = get_user_meta( $extra_info['user_id'], 'last_name', true );

        if ( empty( $last_name ))
            $value = $username;

    }

    return $value;
}
add_filter( 'mustache_variable_user_meta', 'wppb_maybe_replace_first_name', 10, 4 );


/**
 * Function that generates missing image size for user's profile picture (avatar_or_gravatar)
 *
 */
function wppb_resize_avatar_or_gravatar( $avatar_or_gravatar, $user_info, $avatar_size, $userID ) {

    global $wpdb;

    $user_id = !empty( $userID ) ? $userID : (int)$user_info->data->ID;
    $avatar_url = get_avatar_url( $user_id );
    $image_name = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', basename( $avatar_url ));

    $sql = $wpdb->prepare( "SELECT * FROM  $wpdb->posts WHERE  post_type = 'attachment' and guid like %s order by post_date desc", "%$image_name" );
    $attachments = $wpdb->get_results( $sql, OBJECT );
    $attachment_id = isset( $attachments[0]->ID ) ? $attachments[0]->ID : false;

    $attachment_meta = wp_get_attachment_metadata( $attachment_id );

    $image_path = wp_get_original_image_path( $attachment_id );

    if( !empty( $image_path ) ){
        $image_data = getimagesize( $image_path );

        if ( !empty( $image_data['0'] ) && $image_data['0'] < $avatar_size )
            $avatar_size = $image_data['0'];

        if ( !empty( $attachment_meta ) && empty( $attachment_meta['sizes']['wppb-avatar-size-' . $avatar_size ] ) ) {

            $resized_img = image_make_intermediate_size( $image_path, $avatar_size, $avatar_size, true );

            if ($resized_img && !is_wp_error( $resized_img )) {

                // Save the new size in meta data
                $key = sprintf( 'wppb-avatar-size-%d', $avatar_size );
                $attachment_meta['sizes'][$key] = $resized_img;
                $resized_img_url = str_replace( basename( $avatar_url ), $resized_img['file'], $avatar_url );
                wp_update_attachment_metadata( $attachment_id, $attachment_meta );

                // Save size in backup sizes so it's deleted when original attachment is deleted.
                $backup_sizes = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
                if ( !is_array( $backup_sizes )) {
                    $backup_sizes = array();
                }
                $backup_sizes[$key] = $resized_img;
                update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backup_sizes );

                $avatar_or_gravatar = sprintf( '<img alt="" src="%1$s" class="avatar avatar-%2$s photo avatar-default" height="%2$s" width="%2$s" />', $resized_img_url, $avatar_size );
            }

        }
        elseif ( !empty( $attachment_meta['sizes']['wppb-avatar-size-' . $avatar_size ] ) ) {
            $sized_avatar_url = get_avatar_url( $user_id, ['size' => $avatar_size] );
            $avatar_or_gravatar = sprintf( '<img alt="" src="%1$s" class="avatar avatar-%2$s photo avatar-default" height="%2$s" width="%2$s" />', $sized_avatar_url, $avatar_size );
        }
    }

    return $avatar_or_gravatar;

}
add_filter( 'wppb_userlisting_extra_avatar_or_gravatar', 'wppb_resize_avatar_or_gravatar', 10, 4 );


/**
 * Function that filters User Roles (Faceted Menus -> wp_capabilities) according to the "Roles to Display" Option from User-Listing Settings
 *
 */
function wppb_filter_faceted_menus_wp_capabilities( $meta_values, $faceted_filter_options ) {

    if ( $faceted_filter_options['facet-meta'] != 'wp_capabilities' )
        return $meta_values;

    global $userlisting_args;

    $selected_user_roles  = explode( ', ', $userlisting_args[0]['roles-to-display'] );

    if ( empty( $selected_user_roles ) || in_array( '*', $selected_user_roles ) )
        return $meta_values;

    foreach ( $meta_values as $role => $user_count ) {
        if ( !in_array( $role, $selected_user_roles ) )
            unset( $meta_values[$role] );
    }

    return $meta_values;
}
add_filter( 'wppb_filter_meta_values_before_output', 'wppb_filter_faceted_menus_wp_capabilities', 10, 2 );


/**
 * Function that handles User Listing Theme activation
 *
 */
function wppb_handle_userlisting_theme_templates( $post_id ) {

    if ( ( !isset( $_POST['publish'] ) || $_POST['publish'] != 'Publish' ) && ( !isset( $_POST['save'] ) || $_POST['save'] != 'Update' ) ) {
        return;
    }

    // check if a new theme was selected or get the active theme from DB
    $activate_new_theme = get_option('wppb_ul_active_theme');
    if ( !empty( $activate_new_theme ) ) {
        delete_option('wppb_ul_active_theme');
        $active_theme = $activate_new_theme;
    }
    else $active_theme = get_post_meta( $post_id, 'wppb-ul-active-theme', true );

    // save User Listing Theme number of users per page
    if ( isset( $_POST['wppb_ul_page_settings_number-of-userspage'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-number-of-userspage', sanitize_text_field( $_POST['wppb_ul_page_settings_number-of-userspage'] ));

    // save User Listing Theme sorting data
    if ( isset( $_POST['wppb_ul_page_settings_default-sorting-order'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-default-sorting-order', sanitize_text_field( $_POST['wppb_ul_page_settings_default-sorting-order'] ));
    if ( isset( $_POST['wppb_ul_page_settings_default-sorting-criteria'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-default-sorting-criteria', sanitize_text_field( $_POST['wppb_ul_page_settings_default-sorting-criteria'] ));

    // save User Listing Theme role to display
    if ( isset( $_POST['wppb_ul_page_settings_roles-to-display'] ) )
        update_post_meta($post_id, 'wppb-ul-' . $active_theme . '-roles-to-display', $_POST['wppb_ul_page_settings_roles-to-display']  );//phpcs:ignore


    // save User Listing Theme avatar sizes
    if ( isset( $_POST['wppb_ul_page_settings_avatar-size-all-userlisting'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-all-users-avatar-size', sanitize_text_field( $_POST['wppb_ul_page_settings_avatar-size-all-userlisting'] ));
    if ( isset( $_POST['wppb_ul_page_settings_avatar-size-single-userlisting'] ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-single-user-avatar-size', sanitize_text_field( $_POST['wppb_ul_page_settings_avatar-size-single-userlisting'] ));

    $all_users_wp_theme_file = get_option('all_users_wp_theme_file');
    $single_user_wp_theme_file = get_option('single_user_wp_theme_file');

    // save User Listing Theme template (maybe the template was modified by the user)
    if ( isset( $_POST['wppb-ul-templates'] ) && empty( $all_users_wp_theme_file ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-all-users-template', $_POST['wppb-ul-templates'] );//phpcs:ignore
    if ( isset( $_POST['wppb-single-ul-templates'] ) && empty( $single_user_wp_theme_file ) )
        update_post_meta( $post_id, 'wppb-ul-'. $active_theme .'-single-user-template', $_POST['wppb-single-ul-templates'] );//phpcs:ignore

    delete_option('all_users_wp_theme_file');
    delete_option('single_user_wp_theme_file');


    // update UL Templates in DB
    if ( isset( $_POST['wppb-ul-templates'] ) )
        update_post_meta( $post_id, 'wppb-ul-templates', sanitize_text_field( $_POST['wppb-ul-templates'] ));
    if ( isset( $_POST['wppb-single-ul-templates'] ) )
        update_post_meta( $post_id, 'wppb-single-ul-templates', sanitize_text_field( $_POST['wppb-single-ul-templates'] ));

    // update active theme in DB
    update_post_meta( $post_id, 'wppb-ul-active-theme', $active_theme );

}
add_action( 'save_post', 'wppb_handle_userlisting_theme_templates' );


/**
 * Function that adds an admin notification about the UL Themes feature
 *
 */
function wppb_ul_themes_new_feature_notification() {
    /* initiate the plugin notifications class */
    $notifications = WPPB_Plugin_Notifications::get_instance();
    /* this must be unique */
    $notification_id = 'wppb_ul_themes_new_feature';

    $ul_icon_url = ( file_exists( WPPB_PLUGIN_DIR . 'assets/images/pro_user_listing.png' )) ? WPPB_PLUGIN_URL . 'assets/images/pro_user_listing.png' : '';
    $ul_icon = ( !empty($ul_icon_url)) ? '<img src="'. $ul_icon_url .'" width="64" height="64" style="float: left; margin: 10px 12px 10px 0; max-width: 100px;" alt="User Listing">' : '';

    $message = $ul_icon;
    $message .= '<h3 style="margin-bottom: 0px;">Profile Builder PRO - User Listing</h3>';
    $message .= '<p style="font-size: 15px;margin-top:4px;">' . sprintf( __( 'You can now add beautifully pre-designed templates to showcase the user base on your website using one of the <strong>user listing templates</strong> available in the %sUser Listing add-on%s.', 'profile-builder' ), '<a href="https://www.cozmoslabs.com/docs/profile-builder-2/modules/user-listing/" target="_blank">', '</a>') . '</p>';
    $message .= '<a href="' . add_query_arg( array( 'wppb_dismiss_admin_notification' => $notification_id ) ) . '" type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'profile-builder' ) . '</span></a>';

    $notifications->add_notification( $notification_id, $message, 'wppb-notice notice notice-info', false );
}
add_action( 'admin_init', 'wppb_ul_themes_new_feature_notification' );

/* hook to filter to exclude fields from the search field */
add_filter('wppb_exclude_search_fields', 'wppb_ul_exclude_fields_from_search',10, 2 );
/**
 * @param $fields array of fields to exclude from search
 * @param $userlisting_form_id the id of the userlisting cpt
 * @return array
 *
 */
function wppb_ul_exclude_fields_from_search( $fields, $userlisting_form_id ){
    $search_settings = get_post_meta( $userlisting_form_id, 'wppb_ul_search_settings', true );
    if( !empty( $search_settings ) ){
        $default_fields = array( 'user_login', 'user_email', 'user_url' );
        global $wppb_manage_fields;
        if( !isset( $wppb_manage_fields ) )
            $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
        $search_in_these_fields = array_map( 'trim', explode( ',', $search_settings[0]['search-fields'] ) );

        foreach ( $default_fields as $key => $value ){
            if( !in_array( $value, $search_in_these_fields ) )
                $fields[] = $value;

        }

        foreach ( $wppb_manage_fields as $key => $value ){
            if( !empty( $value['meta-name'] ) ) {
                if( !in_array( $value['meta-name'], $search_in_these_fields ) )
                    $fields[] = $value['meta-name'];
            }
        }

    }

    return $fields;
}


add_filter( "wck_before_listed_wppb_ul_fields_element_0", 'wppb_manage_fields_display_field_title_slug', 10, 3 );
add_filter( 'wck_update_container_class_wppb_ul_fields', 'wppb_update_container_class', 10, 4 );
add_filter( 'wck_element_class_wppb_ul_fields', 'wppb_element_class', 10, 4 );



/* Facet Settings Form change classes based on Facet Type field start */
add_filter( 'wck_update_container_class_wppb_ul_faceted_settings', 'wppb_ul_faceted_form_change_class_based_on_field_type', 10, 4 );
function wppb_ul_faceted_form_change_class_based_on_field_type($wck_update_container_css_class, $meta, $results, $counter ) {
    if( !empty( $results ) ){
        $ftype = Wordpress_Creation_Kit_PB::wck_generate_slug( $results[$counter]["facet-type"] );
        return 'class="update_container_'.$meta.' update_container_'.$ftype.' facet_'.$ftype.'"';
    }
}

add_filter( 'wck_element_class_wppb_ul_faceted_settings', 'wppb_ul_faceted_settings_element_type', 10, 4 );
function wppb_ul_faceted_settings_element_type( $element_class, $meta, $results, $element_id ){
    $wppb_element_type = Wordpress_Creation_Kit_PB::wck_generate_slug( $results[$element_id]["facet-type"] );
    return "class='facet_type_$wppb_element_type'";
}

/* Facet Settings Form change classes based on Facet Type field end */

// function to display an error message in the front end in case the shortcode was used but the userlisting wasn't activated
function wppb_list_all_users_display_error($atts){
	return apply_filters( 'wppb_not_addon_not_activated', '<p class="error">'.__( 'You need to activate the Userlisting feature from within the "Add-ons" page!', 'profile-builder' ).'<br/>'.__( 'You can find it in the Profile Builder menu.', 'profile-builder' ).'</p>' );
}



//function to return to the userlisting page without the search parameters
function wppb_clear_results(){
	$args = array( 'searchFor', 'setSortingOrder', 'setSortingCriteria', 'wppb_page' );

	return wppb_remove_query_arg( $args );
}



//function to return the links for the sortable headers
function wppb_get_new_url( $criteria, $extra_info ){
	$set_new_sorting_criteria = ( ( isset( $_REQUEST['setSortingCriteria'] ) && ( $_REQUEST['setSortingCriteria'] == $criteria ) ) ? sanitize_text_field( $_REQUEST['setSortingCriteria'] ) : $criteria );

	$userlisting_settings = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_page_settings', true );
	$set_new_sorting_order = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );
	$set_new_sorting_order = ( ( isset( $_REQUEST['setSortingOrder'] ) && ( $_REQUEST['setSortingOrder'] == 'desc' ) ) ? 'asc' : 'desc' );

	$args = array( 'setSortingCriteria' => $set_new_sorting_criteria, 'setSortingOrder' => $set_new_sorting_order );

	$searchText = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );

	if ( ( isset( $_REQUEST['searchFor'] ) ) && ( sanitize_text_field( $_REQUEST['searchFor'] ) != $searchText ) )
		$args['searchFor'] = stripslashes( sanitize_text_field( $_REQUEST['searchFor'] ) );

	return add_query_arg( $args );
}

//function that returns a class for the sort link depending on what sorting is selected
function wppb_get_sorting_class( $criteria ) {
    $output = '';

    if( isset( $_REQUEST['setSortingCriteria'] ) && ( $_REQUEST['setSortingCriteria'] == $criteria ) ) {
        if( isset( $_REQUEST['setSortingOrder'] ) && $_REQUEST['setSortingOrder'] == 'asc' ) {
            $output = 'sort-asc';
        } elseif( $_REQUEST['setSortingOrder'] == 'desc' ) {
            $output = 'sort-desc';
        }
    }

    return $output;
}

//function to render 404 page in case a user doesn't exist
function wppb_set404(){
	global $wp_query;
	global $wpdb;

    /* we should only do this if we are on a userlisting single page username query arg or $_GET['userID'] is set */
    $username_query_var = wppb_get_query_var( 'username' );
    if( isset($_GET['userID']) || ( !empty( $username_query_var ) && !isset( $_POST['username'] ) ) ){
        $arrayID = array();
        $nrOfIDs = 0;

        //check if certain users want their profile hidden
        $extraField_meta_key = apply_filters( 'wppb_display_profile_meta_field_name', '' );	//meta-name of the extra-field which checks if the user wants his profile hidden
        $extraField_meta_value = apply_filters( 'wppb_display_profile_meta_field_value', '' );	//the value of the above parameter; the users with these 2 combinations will be excluded

        if ( ( trim($extraField_meta_key) != '' ) && ( trim( $extraField_meta_value) != '' ) ){
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT wppb_t1.ID FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = %s WHERE wppb_t2.meta_value LIKE %s ORDER BY wppb_t1.ID", $extraField_meta_key, '%'. $wpdb->esc_like(trim($extraField_meta_value)).'%' ) );
            if( !empty( $results ) ){
                foreach ($results as $result){
                    array_push($arrayID, $result->ID);
                }
            }
        }

        //if admin approval is activated, then give 404 if an unapproved or pending user was manually requested
        $wppb_generalSettings = get_option('wppb_general_settings', 'not_found');
        if( $wppb_generalSettings != 'not_found' )
            if( wppb_get_admin_approval_option_value() === 'yes' ){

                // Get term by the name 'unapproved' in user_status taxonomy.
                $user_status_unapproved = get_term_by('name', 'unapproved', 'user_status');
                if( $user_status_unapproved != false ){
                    $term_taxonomy_id = $user_status_unapproved->term_taxonomy_id;

                    $results = $wpdb->get_results( $wpdb->prepare ( "SELECT wppb_t3.ID FROM $wpdb->users AS wppb_t3 LEFT OUTER JOIN $wpdb->term_relationships AS wppb_t4 ON wppb_t3.ID = wppb_t4.object_id WHERE wppb_t4.term_taxonomy_id = %d ORDER BY wppb_t3.ID", $term_taxonomy_id ) );
                    if( !empty( $results ) ){
                        foreach ($results as $result){
                            array_push($arrayID, $result->ID);
                        }
                    }
                }
                // Get term by the name 'pending' in user_status taxonomy.
                $user_status_pending = get_term_by('name', 'pending', 'user_status');
                if( $user_status_pending != false ){
                    $term_taxonomy_id = $user_status_pending->term_taxonomy_id;

                    $results = $wpdb->get_results( $wpdb->prepare ( "SELECT wppb_t3.ID FROM $wpdb->users AS wppb_t3 LEFT OUTER JOIN $wpdb->term_relationships AS wppb_t4 ON wppb_t3.ID = wppb_t4.object_id WHERE wppb_t4.term_taxonomy_id = %d ORDER BY wppb_t3.ID", $term_taxonomy_id ) );
                    if( !empty( $results ) ){
                        foreach ($results as $result){
                            array_push($arrayID, $result->ID);
                        }
                    }
                }
            }

        $nrOfIDs=count($arrayID);

        //filter to get current user by either username or id(default); get user by username?
        $get_user_by_ID = apply_filters('wppb_userlisting_get_user_by_id', true);

        $invoke404 = false;

        //get user ID
        if (isset($_GET['userID'])){
            $userID = get_userdata( absint( $_GET['userID'] ) );
            if ( is_object( $userID ) ){
                if ( $nrOfIDs ){
                    if ( in_array( $userID->ID, $arrayID ) )
                        $invoke404 = true;
                }else{
                    $username = $userID->user_login;
                    $user = get_user_by('login', $username);
                    if ( ( $user === false ) || ( $user == null ) )
                        $invoke404 = true;
                }
            }
        }else{
            if ( $get_user_by_ID === true ){
                $userID = $username_query_var;
                if ($nrOfIDs){
                    if ( in_array( $userID, $arrayID ) )
                        $invoke404 = true;
                }else{
                    $user = get_userdata($userID);
                    if ( is_object( $user ) ){
                        $username = $user->user_login;
                        $user = get_user_by( 'login', $username );
                        if ( ( $userID !== '' ) && ( $user === false ) )
                            $invoke404 = true;
                    }
                    else
                        $invoke404 = true;
                }

            }else{
                $username = $username_query_var;
                $user = get_userdata($username);
                if ( is_object( $user ) ){
                    if ( $nrOfIDs ){
                        if ( in_array($user->ID, $arrayID ) )
                            $invoke404 = true;
                    }else{
                        if ( ( $username !== '' ) && ( $user === false ) )
                            $invoke404 = true;
                    }
                }
                else
                    $invoke404 = true;
            }
        }

        if ( $invoke404 )
            $wp_query->set_404();
    }
}
add_action('template_redirect', 'wppb_set404');


//function to handle the case when a search was requested but there were no results
function no_results_found_handler($content){

	$retContent = '';
	$formEnd = strpos( (string)$content, '</form>' );

	for ($i=0; $i<$formEnd+7; $i++){
		$retContent .= $content[$i];
	}

	return apply_filters( 'wppb_no_results_found_message', '<p class="noResults" id="noResults">'.__( 'No results found!', 'profile-builder' ) .'</p>' );
}


// flush_rules() if our rules are not yet included
function wppb_flush_rewrite_rules(){
    $wppb_addonOptions = get_option('wppb_module_settings');
    if( $wppb_addonOptions['wppb_userListing'] == 'show' ) {
        $rules = get_option('rewrite_rules');
        $frontpage_id = get_option('page_on_front');

        if (!isset($rules['(.+?)/user/([^/]+)']) || !isset($rules['(.?.+?)/' . wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$']) || (!empty($frontpage_id) && !isset($rules[wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$']))) {
            global $wp_rewrite;

            $wp_rewrite->flush_rules();
        }
    }
}
add_action( 'wp_loaded', 'wppb_flush_rewrite_rules' );


// Adding a new rule
function wppb_insert_userlisting_rule( $rules ){
    $wppb_addonOptions = get_option('wppb_module_settings');
    if( $wppb_addonOptions['wppb_userListing'] == 'show' ) {
        $new_rules = array();

        //user rule
        $new_rules['(.+?)/user/([^/]+)'] = 'index.php?pagename=$matches[1]&username=$matches[2]';

        //users-page rule
        $frontpage_id = get_option('page_on_front');
        if (!empty($frontpage_id)) {
            $new_rules[wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$'] = 'index.php?&page_id=' . $frontpage_id . '&wppb_page=$matches[1]';
        }

        $new_rules['(.?.+?)/' . wppb_get_users_pagination_slug() . '/?([0-9]{1,})/?$'] = 'index.php?pagename=$matches[1]&wppb_page=$matches[2]';

        $rules = $new_rules + $rules;
    }
	return $rules;
}
add_filter( 'rewrite_rules_array', 'wppb_insert_userlisting_rule' );

add_filter( 'redirect_canonical', 'wppb_allow_userlisting_pagination_on_front_page', 10, 2);
function wppb_allow_userlisting_pagination_on_front_page( $redirect_url, $requested_url ){
    $wppb_addonOptions = get_option('wppb_module_settings');
    if( $wppb_addonOptions['wppb_userListing'] == 'show' ) {
        if (is_front_page() && !empty( wppb_get_query_var('wppb_page') ) ) {
            return $requested_url;
        }
    }

    return $redirect_url;
}


// Adding the username var so that WP recognizes it
function wppb_insert_query_vars( $vars ){
    global $wp;
    /**
     * only add this query var if we are not on the frontpage (when we have a form on a page that is set to static frontpage the page will redirect to the post archive
     * because it contains the username field) Having a post variable in the form that is also a registered query arg it will not work
     */
    if( $wp->did_permalink || apply_filters( 'wppb_force_add_username_queryarg', false ) )//added a filter in version 2.9.9 for a client that reported issues on nginx and the username query var was not registering on a clean install
        array_push( $vars, 'username' );

    if( $wp->did_permalink )
        array_push( $vars, 'wppb_page' );

    return $vars;
}
add_filter( 'query_vars', 'wppb_insert_query_vars' );


/**
 * Added in version 3.1.4 since wp_title seems not to work anymore. removed the wp_title filter
 */
add_filter( 'document_title_parts', 'wppb_single_user_list_filter_document_title_parts' );
function wppb_single_user_list_filter_document_title_parts($title_parts) {
    $userID = wppb_get_query_var('username');

    if ( empty( $userID ) )
        return $title_parts;

    $user_object = new WP_User( $userID );

    if( !empty( $user_object->first_name ) || !empty( $user_object->last_name ) ) {
        $title_parts['title'] .= ' | ';
        $title_parts['title'] .= $user_object->first_name;

        if( !empty( $user_object->last_name ) ) {
            $title_parts['title'] .= ' ';
            $title_parts['title'] .= $user_object->last_name;
        }
    }

    return $title_parts;
}

// Filter canonical url so profiles are indexed by google
add_filter( 'get_canonical_url', 'wppb_single_user_list_canonical_url', 99, 2 );
function wppb_single_user_list_canonical_url( $canonical_url, $post ) {
    $userID = wppb_get_query_var('username');

    if ( !empty( $userID ) )
        $canonical_url .= 'user/' . $userID;

    return $canonical_url;
}

//add description for google
add_filter( 'wpseo_metadesc', 'wppb_single_user_description_meta' );
if( !has_filter( 'wpseo_metadesc' ) )
    add_action( 'wp_head', 'wppb_single_user_description_meta' );
function wppb_single_user_description_meta( $description ) {
    $userID = wppb_get_query_var('username');

    if ( empty( $userID ) ){
        if( !empty( $description ) )
            return $description;
        else
            return;
    }

    $user_object = new WP_User( $userID );

    if( !empty( $user_object->description ) ) {
        if( current_filter() == 'wpseo_metadesc' )
            return $user_object->description;
        else
            echo '<meta property="og:description" content="' . esc_attr( $user_object->description ) . '" />';
    }
}

// Add body classes for userlisting when search or faceted are present
add_filter('body_class', 'ul_search_faceted_body_classes');
function ul_search_faceted_body_classes( $classes ){
    if( isset( $_REQUEST['searchFor'] ) )
        $classes[] = 'ul-search';
    if( !empty( $_REQUEST ) ){
        foreach( $_REQUEST as $request_key => $request_value ){
            if( strpos( $request_key, 'ul_filter_' ) === 0 ){
                $classes[] = 'ul-facet-filter';
            }
        }
    }

    return $classes;
}

function wppb_sort_country_values_by_name( $meta_values, $wppb_manage_fields, $faceted_filter_options ){
    if( !empty( $wppb_manage_fields ) ) {
        foreach ($wppb_manage_fields as $field) {
            if ($field['meta-name'] == $faceted_filter_options['facet-meta']) {
                if ( $field['field'] == 'Select (Country)' ){
                    if( !empty( $meta_values ) ){
                        $sort_array = array();
                        foreach( $meta_values as $meta_value => $repetitions ){
                            $sort_array[$meta_value] =  wppb_ul_facet_value_or_label( $meta_value, $faceted_filter_options, $wppb_manage_fields );
                        }
                        asort($sort_array);
                        $meta_values = array_replace($sort_array, $meta_values);
                    }
                }
            }
        }
    }
    return $meta_values;
}

// Include the custom functionality for listing the map with all users.
require_once 'one-map-listing.php';

//in userlisting search if we search for country name we need to replace it with the code that is actually stored in the db
add_filter( 'wppb_ul_search_meta_value', 'wppb_ul_change_country_name_to_code_in_search', 10, 3 );
function wppb_ul_change_country_name_to_code_in_search($search_for, $user_meta_key, $wppb_manage_fields){
    if (!empty($wppb_manage_fields)) {
        foreach ($wppb_manage_fields as $field ){
            if( !empty( $field['meta-name'] ) && $field['meta-name'] === $user_meta_key && $field['field'] === 'Select (Country)' ){

                $country_array = wppb_country_select_options( 'userlisting' );
                $country_code = array_search( strtolower( $search_for ), array_map('strtolower', $country_array ) );
                if( $country_code )
                    return $country_code;
            }
        }
    }
    return $search_for;
}

/**
 * Function used to determine what meta_key compare we use. = or LIKE.
 * For custom meta_keys (outside of pb manage fields) or repeater meta we use LIKE
 * @param $meta_key
 * @return string
 */
function wppb_ul_determine_compare_key_arg( $meta_key ){
    //get all the fields from manage fields and they do not include repeaters
    global $wppb_fields;
    if( !isset( $wppb_fields ) )
        $wppb_fields = get_option( 'wppb_manage_fields', 'not_found' );

    //construct the posible meta_keys here
    $facet_metas = array();
    $exclude_fields_from_facet_menus = apply_filters( 'wppb_exclude_field_list_userlisting_facet_menu_settings', array() );
    if( !empty( $wppb_fields ) && is_array( $wppb_fields ) ) {
        foreach ($wppb_fields as $key => $value) {
            if (!in_array($value['field'], $exclude_fields_from_facet_menus) && !empty($value['meta-name']))
                $facet_metas[] = $value['meta-name'];
        }
    }
    /* add roles to facets options */
    global $wpdb;
    $facet_metas[] = $wpdb->get_blog_prefix().'capabilities';

    $compare_key = '=';
    if( !in_array($meta_key, $facet_metas) )//if the meta_key is not present in the posible meta_keys generated from manage fields then we use LIKE because it is a repeater or a custom meta key
        $compare_key = 'LIKE';


    return $compare_key;
}