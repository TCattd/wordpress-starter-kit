<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPPB_Roles_Editor {

    function __construct() {

        // Create Roles Editor CPT
        add_action( 'init', array( $this, 'create_roles_editor_cpt' ) );

        // Add User Roles entry under PB menu as well
        add_action( 'init', array( $this, 'submenu_page' ), 25 );

        add_action( 'admin_init', array( $this, 'redirect_to_roles_editor' ), 25 );

        // Create a Roles Editor CPT post for every existing role
        add_action( 'current_screen', array( $this, 'create_post_for_role' ) );

        // Edit CPT page
        add_filter( 'manage_wppb-roles-editor_posts_columns', array( $this, 'add_extra_column_for_roles_editor_cpt' ) );
        add_action( 'manage_wppb-roles-editor_posts_custom_column', array( $this, 'custom_column_content_for_roles_editor_cpt' ), 10, 2 );

        // Add and remove meta boxes
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ), 1 );

        // Edit Publish meta box
        add_action( 'post_submitbox_misc_actions', array( $this, 'edit_publish_meta_box' ) );

        // Enqueue scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts_admin' ) );

        // Add role slug to the created post
        add_action( 'save_post', array( $this, 'add_post_meta' ), 10, 2 );

        add_filter( 'wp_insert_post_data', array( $this, 'modify_post_title'), '99', 1 );

        add_action( 'wp_ajax_delete_capability_permanently', array( $this, 'delete_capability_permanently' ) );
        add_action( 'wp_ajax_update_role_capabilities', array( $this, 'update_role_capabilities' ) );
        add_action( 'wp_ajax_get_role_capabilities', array( $this, 'get_role_capabilities' ) );

        add_filter( 'months_dropdown_results', array( $this, 'remove_filter_by_month_dropdown' ), 10, 2 );
        add_filter( 'post_row_actions',  array( $this, 'modify_list_row_actions' ), 10, 2 );

        add_action( 'before_delete_post', array( $this, 'delete_role_permanently' ), 10 );

        add_filter( 'bulk_actions-edit-wppb-roles-editor', '__return_empty_array' );
        add_filter( 'views_edit-wppb-roles-editor', array( $this, 'edit_cpt_quick_links' ) );

        add_filter( 'enter_title_here', array( $this, 'change_title_text' ) );
        add_filter( 'post_updated_messages', array( $this, 'change_post_updated_messages' ) );

        // Add multiple roles checkbox to back-end Add / Edit User (as admin)
        add_action( 'load-user-new.php', array( $this, 'actions_on_user_new' ) );
        add_action( 'load-user-edit.php', array( $this, 'actions_on_user_edit' ) );

    }

    function submenu_page(){

        $args = array(
			'menu_title' 	=> __( 'Roles Editor', 'profile-builder' ),
			'page_title' 	=> __( 'Roles Editor', 'profile-builder' ),
			'menu_slug'		=> 'wppb-roles-editor',
			'page_type'		=> 'submenu_page',
			'capability'	=> 'manage_options',
			'priority'		=> 12,
			'parent_slug'	=> 'profile-builder'
		);

		if( class_exists( 'WCK_Page_Creator_PB' ) ) {
			new WCK_Page_Creator_PB( $args );
		}

    }

    function redirect_to_roles_editor(){
        if( isset( $_GET['page'] ) && $_GET['page'] == 'wppb-roles-editor' ){
            wp_redirect( admin_url( 'edit.php?post_type=wppb-roles-editor' ) );
            die();
        }
    }

    function scripts_admin() {

        global $post_type;
        global $current_screen;
        global $post;
        global $wp_scripts;
        global $wp_styles;

        if( $post_type == 'wppb-roles-editor' ) {
            $wp_default_scripts = $this->wp_default_scripts();
            $scripts_exceptions = array( 'wppb-sitewide', 'acf-field-group', 'acf-pro-field-group', 'acf-input', 'acf-pro-input', 'query-monitor' );
            foreach( $wp_scripts->registered as $key => $value ) {
                if( ! in_array( $key, $wp_default_scripts ) && ! in_array( $key, $scripts_exceptions ) ) {
                    wp_deregister_script( $key );
                }
            }

            $wp_default_styles = $this->wp_default_styles();
            $styles_exceptions = array( 'wppb-serial-notice-css', 'acf-global', 'wppb-back-end-style', 'query-monitor' );
            foreach( $wp_styles->registered as $key => $value ) {
                if( ! in_array( $key, $wp_default_styles ) && ! in_array( $key, $styles_exceptions ) ) {
                    wp_deregister_style( $key );
                }
            }

            wp_enqueue_script( 'wppb_select2_js', WPPB_PLUGIN_URL .'assets/js/select2/select2.min.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
            wp_enqueue_style( 'wppb_select2_css', WPPB_PLUGIN_URL .'assets/css/select2/select2.min.css', array(), PROFILE_BUILDER_VERSION );

            wp_enqueue_script( 'wppb_roles_editor_js', plugin_dir_url( __FILE__ ) .'assets/js/roles-editor.js', array( 'jquery', 'wppb_select2_js' ), PROFILE_BUILDER_VERSION );
            wp_enqueue_style( 'wppb_roles_editor_css', plugin_dir_url( __FILE__ ) .'assets/css/roles-editor.css', array(), PROFILE_BUILDER_VERSION );

            if( $current_screen->id == 'wppb-roles-editor' ) {
                $role_slug = $this->sanitize_role( get_post_meta( $post->ID, 'wppb_role_slug', true ) );
                $current_role = get_role( $role_slug );
                $current_user = wp_get_current_user();

                if( isset( $current_role ) && is_array( $current_role->capabilities ) ) {
                    $role_capabilities = $current_role->capabilities;

                    // True if current user got this role
                    if( isset( $role_slug ) && in_array( $role_slug, $current_user->roles ) ) {
                        $current_user_role = TRUE;
                    } else {
                        $current_user_role = FALSE;
                    }

                    // Get current role users count
                    $current_role_users_count = $this->count_role_users( $current_role->name );
                } else {
                    $role_capabilities = NULL;
                    $current_role_users_count = NULL;
                    $current_user_role = FALSE;
                }
            } else {
                $role_capabilities = NULL;
                $current_role_users_count = NULL;
                $current_user_role = FALSE;
            }

            // Remove old WordPress levels system
            // Use filter and return FALSE if you need the old levels capability system
            $remove_old_levels = apply_filters( 'wppb_remove_old_levels', TRUE );
            if( $remove_old_levels === TRUE ) {
                $role_capabilities = $this->remove_old_labels( $role_capabilities );
            }

            $admin_capabilities = array(
                'manage_options',
                'activate_plugins',
                'delete_plugins',
                'install_plugins',
                'manage_network_options',
                'manage_network',
                'manage_network_plugins',
                'upload_plugins'
            );

            $group_capabilities = $this->group_capabilities();
            $hidden_capabilities = NULL;

            $remove_hidden_capabilities = apply_filters( 'wppb_re_remove_hidden_caps', TRUE );
            if( $remove_hidden_capabilities === TRUE ) {
                $group_capabilities['general']['capabilities'] = array_diff( $group_capabilities['general']['capabilities'], $this->get_hidden_capabilities() );
                $group_capabilities['appearance']['capabilities'] = array_diff( $group_capabilities['appearance']['capabilities'], $this->get_hidden_capabilities() );
                $group_capabilities['plugins']['capabilities'] = array_diff( $group_capabilities['plugins']['capabilities'], $this->get_hidden_capabilities() );
                $group_capabilities['post_types']['attachment']['capabilities'] = array_diff( $group_capabilities['post_types']['attachment']['capabilities'], $this->get_hidden_capabilities() );

                if( $role_capabilities !== NULL ) {
                    $role_capabilities = array_diff_key( $role_capabilities, $this->get_hidden_capabilities() );
                }

                $hidden_capabilities = $this->get_hidden_capabilities();
                if( empty( $hidden_capabilities ) ) {
                    $hidden_capabilities = NULL;
                }
            }

            $all_capabilities = $this->get_all_capabilities();
            
            $custom_capabilities = get_option( 'wppb_roles_editor_capabilities', 'not_set' );
            if( $custom_capabilities != 'not_set' && ! empty( $custom_capabilities['custom']['capabilities'] ) ) {
                foreach( $custom_capabilities['custom']['capabilities'] as $custom_capability_key => $custom_capability ) {
                    if( ! in_array( $custom_capability, $all_capabilities ) ) {
                        $all_capabilities[$custom_capability] = $custom_capability;
                    }
                }
            }

            $vars_array = array(
                'ajaxUrl'                       =>	admin_url( 'admin-ajax.php' ),
                'current_screen_action'         =>  $current_screen->action,
                'capabilities'                  =>  $group_capabilities,
                'current_role_capabilities'     =>  $role_capabilities,
                'current_role_users_count'      =>  $current_role_users_count,
                'all_capabilities'              =>  $all_capabilities,
                'current_user_role'             =>  $current_user_role,
                'admin_capabilities'            =>  $admin_capabilities,
                'hidden_capabilities'           =>  $hidden_capabilities,
                'default_role_text'             =>  esc_html__( 'Default Role', 'profile-builder' ),
                'your_role_text'                =>  esc_html__( 'Your Role', 'profile-builder' ),
                'role_name_required_error_text' =>  esc_html__( 'Role name is required.', 'profile-builder' ),
                'no_capabilities_found_text'    =>  esc_html__( 'No capabilities found.', 'profile-builder' ),
                'select2_placeholder_text'      =>  esc_html__( 'Select capabilities', 'profile-builder' ),
                'delete_permanently_text'       =>  esc_html__( 'Delete Permanently', 'profile-builder' ),
                'capability_perm_delete_text'   =>  esc_html__( "This will permanently delete the capability from your site and from every user role.\n\nIt can't be undone!", 'profile-builder' ),
                'new_cap_update_title_text'     =>  esc_html__( 'This capability is not saved until you click Update!', 'profile-builder' ),
                'new_cap_publish_title_text'    =>  esc_html__( 'This capability is not saved until you click Publish!', 'profile-builder' ),
                'delete_text'                   =>  esc_html__( 'Delete', 'profile-builder' ),
                'cancel_text'                   =>  esc_html__( 'Cancel', 'profile-builder' ),
                'add_new_capability_text'       =>  esc_html__( 'Add New Capability', 'profile-builder' ),
                'capability_text'               =>  esc_html__( 'Capability', 'profile-builder' ),
                'cap_no_delete_text'            =>  esc_html__( 'You can\'t delete this capability from your role.', 'profile-builder' )
            );

            wp_localize_script( 'wppb_roles_editor_js', 'wppb_roles_editor_data', $vars_array );
        }

    }

    function count_role_users( $current_role_name ) {

        // Get current role users count
        $user_count = count_users();

        if( isset( $user_count['avail_roles'][$current_role_name] ) ) {
            $current_role_users_count = $user_count['avail_roles'][$current_role_name];
        } else {
            $current_role_users_count = NULL;
        }

        return $current_role_users_count;
    }

    function get_role_capabilities() {

        if( ! current_user_can( 'manage_options' ) ) {
            die();
        }

        check_ajax_referer( 'wppb-re-ajax-nonce', 'security' );

        $role = isset( $_POST['role'] ) ? get_role( sanitize_text_field( $_POST['role'] ) ) : null;

        if( isset( $role ) && ! empty( $role->capabilities ) ) {
            $role_capabilities = $role->capabilities;

            // Remove old WordPress levels system
            // Use filter and return FALSE if you need the old levels capability system
            $remove_old_levels = apply_filters( 'wppb_remove_old_levels', TRUE );
            if( $remove_old_levels === TRUE ) {
                $role_capabilities = $this->remove_old_labels( $role_capabilities );
            }

            die( json_encode( $role_capabilities ) );
        }

        die( 'no_caps' );
    }

    function edit_cpt_quick_links( $views ) {

        $edited_views = array();
        $edited_views['all'] = $views['all'];

        return $edited_views;

    }

    function create_roles_editor_cpt(){

        if( is_admin() && current_user_can( 'manage_options' ) ) {
            $labels = array(
                'name' => esc_html__( 'Roles Editor', 'profile-builder' ),
                'singular_name' => esc_html__( 'Roles Editor', 'profile-builder' ),
                'add_new' => esc_html__( 'Add New Role', 'profile-builder' ),
                'add_new_item' => esc_html__( 'Add New Role', 'profile-builder' ),
                'edit_item' => esc_html__( 'Edit Role', 'profile-builder' ),
                'new_item' => esc_html__( 'New Role', 'profile-builder' ),
                'all_items' => esc_html__( 'Roles Editor', 'profile-builder' ),
                'view_item' => esc_html__( 'View Role', 'profile-builder' ),
                'search_items' => esc_html__( 'Search the Roles Editor', 'profile-builder' ),
                'not_found' => esc_html__( 'No roles found', 'profile-builder' ),
                'not_found_in_trash' => esc_html__( 'No roles found in trash', 'profile-builder' ),
                'name_admin_bar' => esc_html__( 'Role', 'profile-builder' ),
                'parent_item_colon' => '',
                'menu_name' => esc_html__( 'Roles Editor', 'profile-builder' )
            );

            $args = array(
                'labels' => $labels,
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'query_var' => true,
                'show_in_menu' => 'users.php',
                'has_archive' => false,
                'hierarchical' => false,
                'capability_type' => 'post',
                'supports' => array( 'title' )
            );

            register_post_type( 'wppb-roles-editor', $args );
        }

    }

    function change_title_text( $title ) {

        $screen = get_current_screen();

         if( $screen->post_type == 'wppb-roles-editor' ) {
              $title = esc_html__( 'Enter role name here', 'profile-builder' );
         }

         return $title;

    }

    function change_post_updated_messages( $messages ) {

        global $post;

        $messages['wppb-roles-editor'] = array(
            0  => '',
            1  => esc_html__( 'Role updated.', 'profile-builder' ),
            2  => esc_html__( 'Custom field updated.', 'profile-builder' ),
            3  => esc_html__( 'Custom field deleted.', 'profile-builder' ),
            4  => esc_html__( 'Role updated.', 'profile-builder' ),
            5  => isset( $_GET['revision'] ) ? sprintf( esc_html__( 'Role restored to revision from %s', 'profile-builder' ), wp_post_revision_title( (int) sanitize_text_field( $_GET['revision'] ), false ) ) : false,
            6  => esc_html__( 'Role created.', 'profile-builder' ),
            7  => esc_html__( 'Role saved.', 'profile-builder' ),
            8  => esc_html__( 'Role submitted.', 'profile-builder' ),
            9  => sprintf( esc_html__( 'Role scheduled for: <strong>%1$s</strong>', 'profile-builder' ), date_i18n( __( 'M j, Y @ G:i', 'profile-builder' ), strtotime( $post->post_date ) ) ),
            10 => esc_html__( 'Role draft updated.', 'profile-builder' ),
        );

        return $messages;

    }

    function create_post_for_role() {

        $screen = get_current_screen();

        if( $screen->id == 'edit-wppb-roles-editor' ) {
            global $wpdb;
            global $wp_roles;

            $added_posts = array();

            $args = array(
                'numberposts' => -1,
                'post_type'   => 'wppb-roles-editor'
            );
            $posts = get_posts( $args );

            foreach( $posts as $key => $value ) {
                $post_id = intval( $value->ID );
                $role_slug_meta = $this->sanitize_role( get_post_meta( $post_id, 'wppb_role_slug', true ) );
                if( ! empty( $role_slug_meta ) ) {
                    if( ! array_key_exists( $role_slug_meta, $wp_roles->role_names ) ) {
                        $post_meta = get_post_meta( $post_id );
                        foreach( $post_meta as $post_meta_key => $post_meta_value ) {
                            delete_post_meta( $post_id, $post_meta_key );
                        }

                        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s AND ID = %d", "wppb-roles-editor", $post_id ) );
                    } else {
                        $added_posts[] = $role_slug_meta;
                    }
                }
            }

            foreach( $wp_roles->role_names as $role_slug => $role_display_name ) {
                if( ! in_array( $role_slug, $added_posts ) ) {
                    $id = wp_insert_post( array(
                        'post_title'    => $role_display_name,
                        'post_type'     => 'wppb-roles-editor',
                        'post_content'  => '',
                        'post_status'   => 'publish'
                    ) );

                    update_post_meta( $id, 'wppb_role_slug', $role_slug );
                }
            }
        }

    }

    function add_extra_column_for_roles_editor_cpt( $columns ) {

        $columns = array(
            'title'         => esc_html__( 'Role Name', 'profile-builder' ),
            'role'          => esc_html__( 'Role Slug', 'profile-builder' ),
            'capabilities'  => esc_html__( 'Capabilities', 'profile-builder' ),
            'users'         => esc_html__( 'Users', 'profile-builder' )
        );

        return apply_filters( 'wppb_manage_roles_columns', $columns );

    }

    function custom_column_content_for_roles_editor_cpt( $column_name, $post_id ) {

        $role_slug = $this->sanitize_role( get_post_meta( $post_id, 'wppb_role_slug', true ) );

        if( isset( $role_slug ) ) {
            $role = get_role( $role_slug );

            if( $column_name == 'role' ) {
                echo '<input readonly spellcheck="false" type="text" class="wppb-role-slug-input input" value="'. esc_attr( $role_slug ) .'" />';
            }

            if( $column_name == 'capabilities' && isset( $role ) ) {
                // Remove old WordPress levels system
                // Use filter and return FALSE if you need the old levels capability system
                $remove_old_levels = apply_filters( 'wppb_remove_old_levels', TRUE );
                if( $remove_old_levels === TRUE ) {
                    $role_capabilities = $this->remove_old_labels( $role->capabilities );
                } else {
                    $role_capabilities = $role->capabilities;
                }

                echo count( $role_capabilities );
            }

            if( $column_name == 'users' && isset( $role ) ) {
                $role_users_count = $this->count_role_users( $role->name );

                if( ! isset( $role_users_count ) ) {
                    $role_users_count = 0;
                }

                echo esc_html( $role_users_count );
            }
        }
    }

    function register_meta_boxes() {

        remove_meta_box( 'slugdiv', 'wppb-roles-editor', 'normal' );
        add_meta_box( 'wppb_edit_role_capabilities', esc_html__( 'Edit Role Capabilities', 'profile-builder' ), array( $this, 'edit_role_capabilities_meta_box_callback' ), 'wppb-roles-editor', 'normal', 'high' );

    }

    function edit_role_capabilities_meta_box_callback() {

        ?>
        <div id="wppb-role-edit-caps-div" style="margin: 15px 0 5px; 0;">
            <div id="wppb-role-edit-add-caps">
                <select style="width: 40%; display: none;" class="wppb-capabilities-select" multiple="multiple"></select>

                <input class="wppb-add-new-cap-input" type="text" placeholder="<?php esc_html_e( 'Add a new capability', 'profile-builder' ) ?>">

                <a href="javascript:void(0)" class="button-primary" onclick="wppb_re_add_capability()">
                    <span><?php esc_html_e( 'Add Capability', 'profile-builder' ) ?></span>
                </a>

                <div id="wppb-add-new-cap-link">
                    <a class="wppb-add-new-cap-link" href="javascript:void(0)"><?php esc_html_e( 'Add New Capability', 'profile-builder' ) ?></a>
                </div>

                <span id="wppb-add-capability-error"><?php esc_html_e( 'Please select an existing capability or add a new one!', 'profile-builder' ) ?></span>
                <span id="wppb-hidden-capability-error"><?php esc_html_e( 'You can\'t add a hidden capability!', 'profile-builder' ) ?></span>
                <span id="wppb-duplicate-capability-error"><?php esc_html_e( 'This capability already exists!', 'profile-builder' ) ?></span>
            </div>

            <div class="wppb-role-edit-caps">
                <ul id="wppb-capabilities-tabs">
                    <li class="wppb-role-editor-tab-title wppb-role-editor-tab-active">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-all" data-wppb-re-tab="all"><i class="dashicons dashicons-plus"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'All', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-general" data-wppb-re-tab="general"><i class="dashicons dashicons-wordpress"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'General', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-posts" data-wppb-re-tab="post"><i class="dashicons dashicons-admin-post"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Posts', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-pages" data-wppb-re-tab="page"><i class="dashicons dashicons-admin-page"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Pages', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-media" data-wppb-re-tab="attachment"><i class="dashicons dashicons-admin-media"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Media', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-taxonomies" data-wppb-re-tab="taxonomies"><i class="dashicons dashicons-tag"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Taxonomies', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-appearance" data-wppb-re-tab="appearance"><i class="dashicons dashicons-admin-appearance"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Appearance', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-plugins" data-wppb-re-tab="plugins"><i class="dashicons dashicons-admin-plugins"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Plugins', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-users" data-wppb-re-tab="users"><i class="dashicons dashicons-admin-users"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Users', 'profile-builder' ) ?></span></a>
                    </li>

                    <li class="wppb-role-editor-tab-title">
                        <a href="javascript:void(0)" class="wppb-role-editor-tab wppb-role-editor-custom" data-wppb-re-tab="custom"><i class="dashicons dashicons-admin-generic"></i> <span class="wppb-role-editor-tab-label"><?php esc_html_e( 'Custom', 'profile-builder' ) ?></span></a>
                    </li>
                </ul>

                <div id="wppb-role-edit-table">
                    <div class="wppb-re-spinner-container"><i class="icon-wppb-re-spinner wppb-re-spin"></i></div>
                    <div id="wppb-role-edit-caps-clear"></div>
                </div>
                <div id="wppb-role-edit-divs-clear"></div>
            </div>

            <input type="hidden" id="wppb-role-slug-hidden" name="wppb-role-slug-hidden" value="">
            <input type="hidden" name="wppb-re-ajax-nonce" id="wppb-re-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppb-re-ajax-nonce' ) ) ?>" />
        </div>
        <?php

    }

    function edit_publish_meta_box( $post ) {

        global $current_screen;

        $post_type = 'wppb-roles-editor';

        if( $post->post_type == $post_type ) {
            $role_slug = $this->sanitize_role( get_post_meta( $post->ID, 'wppb_role_slug', true ) );

            ?>
            <style type="text/css">
                .misc-pub-section.misc-pub-post-status,
                .misc-pub-section.misc-pub-visibility,
                .misc-pub-section.curtime.misc-pub-curtime,
                #minor-publishing-actions,
                #major-publishing-actions #delete-action {
                    display: none;
                }
            </style>

            <div class="misc-pub-section misc-pub-section-users">
                <i class="icon-wppb-re-users"></i>
                <span><?php esc_html_e( 'Users', 'profile-builder' ) ?>: <strong>0</strong></span>
            </div>

            <div class="misc-pub-section misc-pub-section-capabilities">
                <i class="icon-wppb-re-caps"></i>
                <span><?php esc_html_e( 'Capabilities', 'profile-builder' ) ?>: <strong>0</strong></span>
            </div>

            <div class="misc-pub-section misc-pub-section-edit-slug">
                <i class="icon-wppb-re-slug"></i>
                <span>
                    <label for="wppb-role-slug"><?php esc_html_e( 'Role Slug', 'profile-builder' ) ?>: </label>
                    <input type="text" id="wppb-role-slug" value="<?php echo $current_screen->action == 'add' ? '' : esc_attr( $role_slug ) ?>" <?php echo $current_screen->action == 'add' ? '' : 'disabled'; ?>>
                </span>
            </div>
        <?php
        }

    }

    function remove_old_labels( $capabilities ) {

        $old_levels = array( 'level_0', 'level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'level_7', 'level_8', 'level_9', 'level_10' );

        foreach( $old_levels as $key => $value ) {
            unset( $capabilities[$value] );
        }

        return $capabilities;

    }

    function modify_post_title( $data ) {

        if( 'wppb-roles-editor' != $data['post_type'] || $data['post_status'] == 'auto-draft' ) {
            return $data;
        }

        if( ! current_user_can( 'manage_options' ) ) {
            return $data;
        }

        if( isset( $data['post_title'] ) ) {
            $data['post_title'] =  wp_strip_all_tags( $data['post_title'] );
        }

        return $data;

    }

    function add_post_meta( $post_id, $post ) {

        $post_type = get_post_type( $post_id );

        if( 'wppb-roles-editor' != $post_type || $post->post_status == 'auto-draft' ) {
            return;
        }

        if( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if( !isset( $_POST['wppb-re-ajax-nonce'] ) || !wp_verify_nonce( sanitize_text_field( $_POST['wppb-re-ajax-nonce'] ), 'wppb-re-ajax-nonce' ) )
            return;

        if( isset( $_POST['wppb-role-slug-hidden'] ) ) {
            $role_slug = $this->sanitize_role( trim( $_POST['wppb-role-slug-hidden'] ) ); /* phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */

            update_post_meta( $post_id, 'wppb_role_slug', $role_slug );
        }

    }

    function update_role_capabilities() {

        if( ! current_user_can( 'manage_options' ) ) {
            die();
        }

        check_ajax_referer( 'wppb-re-ajax-nonce', 'security' );

        $role_slug = isset( $_POST['role'] ) ? $this->sanitize_role( $_POST['role'] ) : ''; /* phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */

        $role = get_role( $role_slug );

        if( $role ) {
            if( isset( $_POST['new_capabilities'] ) ) {
                foreach( $_POST['new_capabilities'] as $key => $value ) { /* phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
                    $role->add_cap( sanitize_text_field( $key ) );
                }
            }

            if( isset( $_POST['capabilities_to_delete'] ) ) {
                foreach( $_POST['capabilities_to_delete'] as $key => $value ) { /* phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
                    $role->remove_cap( sanitize_text_field( $key ) );
                }
            }
        } else {
            $capabilities = array();

            if( isset( $_POST['all_capabilities'] ) ) {
                foreach( $_POST['all_capabilities'] as $key => $value ) { /* phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
                    $capabilities[sanitize_text_field( $key )] = true;
                };
            }

            $role_display_name = isset( $_POST['role_display_name'] ) ? sanitize_text_field( $_POST['role_display_name'] ) : '';

            add_role( $role_slug, $role_display_name, $capabilities );
        }

        die( 'role_capabilities_updated' );

    }

    function group_capabilities() {

        $capabilities = get_option( 'wppb_roles_editor_capabilities', 'not_set' );

        if( $capabilities == 'not_set' ) {
            // We remove non-custom capabilities from this array later on
            $custom_capabilities = $this->get_all_capabilities();
            $custom_capabilities = $this->remove_old_labels( $custom_capabilities );

            // General capabilities
            $general_capabilities = array(
                'label'         =>  'General',
                'icon'          =>  'dashicons-wordpress',
                'capabilities'  =>  array( 'edit_dashboard', 'edit_files', 'export', 'import', 'manage_links', 'manage_options', 'moderate_comments', 'read', 'unfiltered_html', 'update_core' )
            );

            // Themes management capabilities
            $appearance_capabilities = array(
                'label'         =>  'Appearance',
                'icon'          =>  'dashicons-admin-appearance',
                'capabilities'  =>  array( 'delete_themes', 'edit_theme_options', 'edit_themes', 'install_themes', 'switch_themes', 'update_themes' )
            );

            // Plugins management capabilities
            $plugins_capabilities = array(
                'label'         =>  'Plugins',
                'icon'          =>  'dashicons-admin-plugins',
                'capabilities'  =>  array( 'activate_plugins', 'delete_plugins', 'edit_plugins', 'install_plugins', 'update_plugins' )
            );

            // Users management capabilities
            $users_capabilities = array(
                'label'         =>  'Users',
                'icon'          =>  'dashicons-admin-users',
                'capabilities'  =>  array( 'add_users', 'create_roles', 'create_users', 'delete_roles', 'delete_users', 'edit_roles', 'edit_users', 'list_roles', 'list_users', 'promote_users', 'remove_users' )
            );

            // Taxonomies capabilities - part 1
            $taxonomies_capabilities = array();
            $taxonomies = get_taxonomies( array(), 'objects' );
            foreach( $taxonomies as $taxonomy ) {
                $taxonomies_capabilities = array_merge( $taxonomies_capabilities, array_values( (array) $taxonomy->cap ) );
            }

            // Post types capabilities
            $post_types_capabilities = array();
            foreach( get_post_types( array(), 'objects' ) as $type ) {

                if( in_array( $type->name, array( 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'wppb-rf-cpt', 'wppb-epf-cpt', 'wppb-roles-editor' ) ) ) {
                    continue;
                }

                $post_type_capabilities = $this->post_type_group_capabilities( $type->name );
                if( empty( $post_type_capabilities ) ) {
                    continue;
                }

                $post_type_icon = $type->hierarchical ? 'dashicons-admin-page' : 'dashicons-admin-post';
                if( is_string( $type->menu_icon ) && preg_match( '/dashicons-/i', $type->menu_icon ) ) {
                    $post_type_icon = $type->menu_icon;
                } else if( 'attachment' === $type->name ) {
                    $post_type_icon = 'dashicons-admin-media';
                } else if( 'download' === $type->name ) {
                    $post_type_icon = 'dashicons-download';
                } else if( 'product' === $type->name ) {
                    $post_type_icon = 'dashicons-cart';
                }

                $post_types_capabilities[$type->name] = array(
                    'label'         => $type->labels->name,
                    'icon'          => $post_type_icon,
                    'capabilities'  => $post_type_capabilities
                );

                $taxonomies_capabilities = array_diff( $taxonomies_capabilities, $post_type_capabilities );
                $custom_capabilities = array_diff( $custom_capabilities, $post_type_capabilities );
            }

            // Taxonomies capabilities - part 2
            $taxonomies_capabilities = array_diff( $taxonomies_capabilities, $general_capabilities['capabilities'], $appearance_capabilities['capabilities'], $plugins_capabilities['capabilities'], $users_capabilities['capabilities'] );
            $taxonomies_capabilities = array(
                'label'         =>  'Taxonomies',
                'icon'          =>  '',
                'capabilities'  =>  array_unique( $taxonomies_capabilities )
            );

            // Custom capabilities
            $custom_capabilities = array_diff( $custom_capabilities, $general_capabilities['capabilities'], $appearance_capabilities['capabilities'], $appearance_capabilities['capabilities'], $plugins_capabilities['capabilities'], $users_capabilities['capabilities'], $taxonomies_capabilities['capabilities'] );
            $custom_capabilities = array_values( $custom_capabilities );
            $custom_capabilities = array(
                'label'         =>  'Custom',
                'icon'          =>  '',
                'capabilities'  =>  array_unique( $custom_capabilities )
            );

            // Create capabilities array
            $capabilities = array(
                'general'       => $general_capabilities,
                'post_types'    => $post_types_capabilities,
                'taxonomies'    => $taxonomies_capabilities,
                'appearance'    => $appearance_capabilities,
                'plugins'       => $plugins_capabilities,
                'users'         => $users_capabilities,
                'custom'        => $custom_capabilities
            );

            update_option( 'wppb_roles_editor_capabilities', $capabilities );
        } else {
            $custom_capabilities = $this->get_all_capabilities();
            $custom_capabilities = $this->remove_old_labels( $custom_capabilities );

            foreach( $capabilities['post_types'] as $key => $value ) {
                $custom_capabilities = array_diff( $custom_capabilities, $value['capabilities'] );
            }

            foreach( $capabilities as $key => $value ) {
                if( $key != 'post_types' && $key != 'custom' ) {
                    $custom_capabilities = array_diff( $custom_capabilities, $value['capabilities'] );
                }
            }

            $custom_capabilities = array_values( $custom_capabilities );
            $custom_capabilities = array_unique( $custom_capabilities );
            $custom_capabilities = array_diff( $custom_capabilities, $capabilities['custom']['capabilities'] );

            if( ! empty( $custom_capabilities ) ) {
                $capabilities['custom']['capabilities'] = array_merge( $capabilities['custom']['capabilities'], $custom_capabilities );

                update_option( 'wppb_roles_editor_capabilities', $capabilities );
            }
        }

        return $capabilities;

    }

    function post_type_group_capabilities( $post_type = 'post' ) {

        $capabilities = (array) get_post_type_object( $post_type )->cap;

        unset( $capabilities['edit_post'] );
        unset( $capabilities['read_post'] );
        unset( $capabilities['delete_post'] );

        $capabilities = array_values( $capabilities );

        if( ! in_array( $post_type, array( 'post', 'page' ) ) ) {
            // Get the post and page capabilities
            $post_caps = array_values( (array) get_post_type_object( 'post' )->cap );
            $page_caps = array_values( (array) get_post_type_object( 'page' )->cap );

            // Remove post/page capabilities from the current post type capabilities
            $capabilities = array_diff( $capabilities, $post_caps, $page_caps );
        }

        if( 'attachment' === $post_type ) {
            $capabilities[] = 'unfiltered_upload';
        }

        return array_unique( $capabilities );

    }

    function get_all_capabilities() {

        global $wp_roles;

        $capabilities = array();

        foreach( $wp_roles->role_objects as $key => $role ) {
            if( is_array( $role->capabilities ) ) {
                foreach( $role->capabilities as $capability => $granted ) {
                    $capabilities[$capability] = $capability;
                }
            }
        }

        return array_unique( $capabilities );

    }

    function delete_capability_permanently() {

        if( ! current_user_can( 'manage_options' ) ) {
            die();
        }

        check_ajax_referer( 'wppb-re-ajax-nonce', 'security' );

        global $wp_roles;

        if( !empty( $_POST['capability'] ) ) {

            foreach ($wp_roles->role_names as $role_slug => $role_display_name) {
                $role = get_role($role_slug);
                $role->remove_cap(sanitize_text_field($_POST['capability']));
            }

            $capabilities = get_option('wppb_roles_editor_capabilities', 'not_set');

            if ($capabilities != 'not_set' && ($key = array_search(sanitize_text_field($_POST['capability']), $capabilities['custom']['capabilities'])) !== false) {
                unset($capabilities['custom']['capabilities'][$key]);
                $capabilities['custom']['capabilities'] = array_values($capabilities['custom']['capabilities']);

                update_option('wppb_roles_editor_capabilities', $capabilities);
            }
        }

        die();

    }

    function remove_filter_by_month_dropdown( $months, $post_type = NULL ) {

        if( isset( $post_type ) && $post_type == 'wppb-roles-editor' ) {
            return __return_empty_array();
        } else {
            return $months;
        }

    }

    function modify_list_row_actions( $actions, $post ) {
        global $wp_roles;

        if( $post->post_type == 'wppb-roles-editor' ) {
            $current_user = wp_get_current_user();
            $default_role = get_option( 'default_role' );
            $role_slug = get_post_meta( $post->ID, 'wppb_role_slug', true );

            $url = admin_url( 'post.php?post=' . $post->ID );

            $edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

            $actions = array(
                'edit' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url( $edit_link ),
                    esc_html__( 'Edit', 'profile-builder' )
                )
            );

            $clone_url = admin_url( 'post-new.php?post_type=wppb-roles-editor' );
            $clone_link = add_query_arg( array( 'action' => 'wppb_re_clone', 'wppb_re_clone' => $this->sanitize_role( $role_slug ) ), $clone_url );

            $actions = array_merge( $actions, array(
                    'clone' => sprintf(
                        '<a href="%1$s">%2$s</a>',
                        esc_url( $clone_link ),
                        esc_html__( 'Clone', 'profile-builder' )
                    )
                )
            );

            if( in_array( $role_slug, $current_user->roles ) && ( ! is_multisite() || ( is_multisite() && ! is_super_admin() ) ) && ( !empty( $wp_roles->roles[$role_slug]['capabilities'] ) && array_key_exists( 'manage_options', $wp_roles->roles[$role_slug]['capabilities'] ) ) ) {
                $actions = array_merge( $actions, array(
                        'delete_notify your_role' => '<span title="'. esc_html__( 'You can\'t delete your role.', 'profile-builder' ) .'">'. esc_html__( 'Delete', 'profile-builder' ) .'</span>'
                    )
                );
            } elseif( $role_slug == $default_role  ) {
                $actions = array_merge( $actions, array(
                        'default_role'  => sprintf(
                            '<a href="%s">%s</a>',
                            esc_url( admin_url( 'options-general.php#default_role' ) ),
                            esc_html__( 'Change Default', 'profile-builder' ) ),
                        'delete_notify' => '<span title="'. esc_html__( 'You can\'t delete the default role. Change it first.', 'profile-builder' ) .'">'. esc_html__( 'Delete', 'profile-builder' ) .'</span>'
                    )
                );
            } else {
                $delete_link = wp_nonce_url( add_query_arg( array( 'action' => 'delete' ), $url ), 'delete-post_'. $post->ID );

                $actions = array_merge( $actions, array(
                        'delete' => sprintf(
                            '<a href="%1$s" onclick="return confirm( \'%2$s\' );">%3$s</a>',
                            esc_url( $delete_link ),
                            esc_html__( 'Are you sure?\nThis will permanently delete the role and cannot be undone!\nUsers assigned only on this role will be moved to the default role.', 'profile-builder' ),
                            esc_html__( 'Delete', 'profile-builder' )
                        )
                    )
                );
            }
        }

        return $actions;

    }

    function sanitize_role( $role ) {

        $role = strtolower( $role );
        $role = wp_strip_all_tags( $role );
        $role = preg_replace( '/[^a-z0-9_\-\s]/', '', $role );
        $role = str_replace( ' ', '_', $role );
        $role = sanitize_text_field( $role );

        return $role;

    }

    function delete_role_permanently( $post_id ) {

        global $post_type;
        if( $post_type != 'wppb-roles-editor' ) {
            return;
        }

        check_admin_referer( 'delete-post_'. $post_id );

        $role_slug = get_post_meta( $post_id, 'wppb_role_slug', true );
        $role_slug = $this->sanitize_role( $role_slug );

        $default_role = get_option( 'default_role' );

        if( $role_slug == $default_role ) {
            return;
        }

        $users = get_users( array( 'role' => $role_slug ) );

        if( is_array( $users ) ) {
            foreach( $users as $user ) {
                if( $user->has_cap( $role_slug ) && count( $user->roles ) <= 1 ) {
                    $user->set_role( $default_role );
                } elseif( $user->has_cap( $role_slug ) ) {
                    $user->remove_role( $role_slug );
                }
            }
        }

        remove_role( $role_slug );

    }

    function wp_default_scripts() {

        $wp_default_scripts = array(
            'jquery', 'jquery-core', 'jquery-migrate', 'jquery-ui-core', 'jquery-ui-accordion',
            'jquery-ui-autocomplete', 'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog',
            'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-menu', 'jquery-ui-mouse',
            'jquery-ui-position', 'jquery-ui-progressbar', 'jquery-ui-resizable', 'jquery-ui-selectable',
            'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-spinner', 'jquery-ui-tabs',
            'jquery-ui-tooltip', 'jquery-ui-widget', 'underscore', 'backbone', 'utils', 'common',
            'wp-a11y', 'sack', 'quicktags', 'colorpicker', 'editor', 'wp-fullscreen-stub', 'wp-ajax-response',
            'wp-pointer', 'heartbeat', 'wp-auth-check', 'wp-lists', 'prototype', 'scriptaculous-root',
            'scriptaculous-builder', 'scriptaculous-dragdrop', 'scriptaculous-effects', 'scriptaculous-slider',
            'scriptaculous-sound', 'scriptaculous-controls', 'scriptaculous', 'cropper', 'jquery-effects-core',
            'jquery-effects-blind', 'jquery-effects-bounce', 'jquery-effects-clip', 'jquery-effects-drop',
            'jquery-effects-explode', 'jquery-effects-fade', 'jquery-effects-fold', 'jquery-effects-highlight',
            'jquery-effects-puff', 'jquery-effects-pulsate', 'jquery-effects-scale', 'jquery-effects-shake',
            'jquery-effects-size', 'jquery-effects-slide', 'jquery-effects-transfer', 'jquery-ui-selectmenu',
            'jquery-form', 'jquery-color', 'schedule', 'jquery-query', 'jquery-serialize-object', 'jquery-hotkeys',
            'jquery-table-hotkeys', 'jquery-touch-punch', 'suggest', 'imagesloaded', 'masonry', 'jquery-masonry',
            'thickbox', 'jcrop', 'swfobject', 'plupload', 'plupload-all', 'plupload-html5', 'plupload-flash',
            'plupload-silverlight', 'plupload-html4', 'plupload-handlers', 'wp-plupload', 'swfupload', 'swfupload-swfobject',
            'swfupload-queue', 'swfupload-speed', 'swfupload-all', 'swfupload-handlers', 'comment-reply', 'json2',
            'underscore', 'backbone', 'wp-util', 'wp-backbone', 'revisions', 'imgareaselect', 'mediaelement',
            'wp-mediaelement', 'froogaloop', 'wp-playlist', 'zxcvbn-async', 'password-strength-meter', 'user-profile',
            'language-chooser', 'user-suggest', 'admin-bar', 'wplink', 'wpdialogs', 'word-count', 'media-upload',
            'hoverIntent', 'customize-base', 'customize-loader', 'customize-preview', 'customize-models', 'customize-views',
            'customize-controls', 'customize-selective-refresh', 'customize-widgets', 'customize-preview-widgets',
            'customize-preview-nav-menus', 'wp-custom-header', 'accordion', 'shortcode', 'media-models', 'wp-embed',
            'media-views', 'media-editor', 'media-audiovideo', 'mce-view', 'wp-api', 'admin-tags', 'admin-comments', 'xfn',
            'postbox', 'tags-box', 'tags-suggest', 'post', 'press-this', 'editor-expand', 'link', 'comment', 'admin-gallery',
            'admin-widgets', 'theme', 'inline-edit-post', 'inline-edit-tax', 'plugin-install', 'updates', 'farbtastic', 'iris',
            'wp-color-picker', 'dashboard', 'list-revisions', 'media-grid', 'media', 'image-edit', 'set-post-thumbnail',
            'nav-menu', 'custom-header', 'custom-background', 'media-gallery', 'svg-painter', 'customize-nav-menus',
        );

        return $wp_default_scripts;

    }

    function wp_default_styles() {

        $wp_default_styles = array(
            'admin-bar', 'colors', 'ie', 'wp-auth-check', 'wp-jquery-ui-dialog', 'wppb-serial-notice-css',
            'common', 'forms', 'admin-menu', 'dashboard', 'list-tables', 'edit', 'revisions', 'media',
            'themes', 'about', 'nav-menus', 'widgets', 'site-icon', 'l10n', 'wp-admin', 'login', 'install',
            'wp-color-picker', 'customize-controls', 'customize-widgets', 'customize-nav-menus', 'press-this',
            'buttons', 'dashicons', 'editor-buttons', 'media-views', 'wp-pointer', 'customize-preview',
            'wp-embed-template-ie', 'imgareaselect', 'mediaelement', 'wp-mediaelement', 'thickbox',
            'deprecated-media', 'farbtastic', 'jcrop', 'colors-fresh', 'open-sans',
        );

        return $wp_default_styles;

    }

    function get_hidden_capabilities() {

        $capabilities = array();

        if( is_multisite() || ! defined( 'ALLOW_UNFILTERED_UPLOADS' ) || ! ALLOW_UNFILTERED_UPLOADS ) {
            $capabilities['unfiltered_upload'] = 'unfiltered_upload';
        }

        if( is_multisite() || ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) ) {
            $capabilities['unfiltered_html'] = 'unfiltered_html';
        }

        if( is_multisite() || ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ) {
            $capabilities['edit_files'] = 'edit_files';
            $capabilities['edit_plugins'] = 'edit_plugins';
            $capabilities['edit_themes'] = 'edit_themes';
        }

        if( is_multisite() || ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) ) {
            $capabilities['edit_files'] = 'edit_files';
            $capabilities['edit_plugins'] = 'edit_plugins';
            $capabilities['edit_themes'] = 'edit_themes';
            $capabilities['update_plugins'] = 'update_plugins';
            $capabilities['delete_plugins'] = 'delete_plugins';
            $capabilities['install_plugins'] = 'install_plugins';
            $capabilities['upload_plugins'] = 'upload_plugins';
            $capabilities['update_themes'] = 'update_themes';
            $capabilities['delete_themes'] = 'delete_themes';
            $capabilities['install_themes'] = 'install_themes';
            $capabilities['upload_themes'] = 'upload_themes';
            $capabilities['update_core'] = 'update_core';
        }

        return array_unique( $capabilities );

    }

    // Add actions on Add User back-end page
    function actions_on_user_new() {

        $this->scripts_and_styles_actions( 'user_new' );

        add_action( 'user_new_form', array( $this, 'roles_field_user_new' ) );

        add_action( 'user_register', array( $this, 'roles_update_user_new' ) );

    }

    // Add actions on Edit User back-end page
    function actions_on_user_edit() {

        $this->scripts_and_styles_actions( 'user_edit' );

        add_action( 'personal_options', array( $this, 'roles_field_user_edit' ) );

        add_action( 'profile_update', array( $this, 'roles_update_user_edit' ), 10, 2 );

    }

    // Roles Edit checkboxes for Add User back-end page
    function roles_field_user_new() {

        if( ! current_user_can( 'promote_users' ) ) {
            return;
        }

        $user_roles = apply_filters( 'wppb_default_user_roles', array( get_option( 'default_role' ) ) );

        if( isset( $_POST['createuser'] ) && ! empty( $_POST['wppb_re_user_roles'] ) ) {
            $user_roles = array_map( array( $this, 'sanitize_role' ), $_POST['wppb_re_user_roles'] );// phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }

        wp_nonce_field( 'new_user_roles', 'wppb_re_new_user_roles_nonce' );

        if( apply_filters( 'wppb_backend_allow_multiple_user_roles_selection', true ) ) {
            $this->roles_field_display( $user_roles );
        }

    }

    // Roles Edit checkboxes for Edit User back-end page
    function roles_field_user_edit( $user ) {

        if( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user->ID ) ) {
            return;
        }

        $user_roles = (array) $user->roles;

        wp_nonce_field( 'new_user_roles', 'wppb_re_new_user_roles_nonce' );

        if( apply_filters( 'wppb_backend_allow_multiple_user_roles_selection', true ) ) {
            $this->roles_field_display($user_roles);
        }
    }

    // Output roles edit checkboxes
    function roles_field_display( $user_roles ) {

        $wppb_roles = get_editable_roles();

        ?>
        <table class="form-table">
            <tr class="wppb-re-edit-user">
                <th><?php esc_html_e( 'Edit User Roles', 'profile-builder' ); ?></th>

                <td>
                    <div>
                        <ul style="margin: 5px 0;">
                            <?php foreach( $wppb_roles as $role_slug => $role_details ) { ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="wppb_re_user_roles[]" value="<?php echo esc_attr( $role_slug ); ?>" <?php checked( in_array( $role_slug, $user_roles ) ); ?> />
                                        <?php echo esc_html( translate_user_role( $role_details['name'] ) ); ?>
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>

    <?php
    }

    function roles_update_user_edit( $user_id, $old_user_data ) {

        if( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        if( ! isset( $_POST['wppb_re_new_user_roles_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wppb_re_new_user_roles_nonce'] ), 'new_user_roles' ) ) {
            return;
        }

        $this->roles_update_user_new_and_edit( $old_user_data );

    }

    function roles_update_user_new( $user_id ) {

        if( ! current_user_can( 'promote_users' ) ) {
            return;
        }

        if( ! isset( $_POST['wppb_re_new_user_roles_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wppb_re_new_user_roles_nonce'] ), 'new_user_roles' ) ) {
            return;
        }

        $user = new \WP_User( $user_id );

        $this->roles_update_user_new_and_edit( $user );

    }

    function roles_update_user_new_and_edit( $user ) {

        if( ! empty( $_POST['wppb_re_user_roles'] ) ) {

            $old_roles = (array) $user->roles;

            $new_roles = array_map( array( $this, 'sanitize_role' ), $_POST['wppb_re_user_roles'] );//phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            foreach( $new_roles as $new_role ) {
                if( ! in_array( $new_role, (array) $user->roles ) ) {
                    $user->add_role( $new_role );
                }
            }

            foreach( $old_roles as $old_role ) {
                if( ! in_array( $old_role, $new_roles ) ) {
                    $user->remove_role( $old_role );
                }
            }
        } else {
            foreach( (array) $user->roles as $old_role ) {
                $user->remove_role( $old_role );
            }
        }

    }

    function scripts_and_styles_actions( $location ) {

        // Enqueue jQuery on both Add User and Edit User back-end pages
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_jquery' ) );

        // Actions for Add User back-end page
        if( $location == 'user_new' && apply_filters( 'wppb_backend_allow_multiple_user_roles_selection', true ) ) {
            add_action( 'admin_footer', array( $this, 'print_scripts_user_new' ), 25 );
        }

        // Actions for Edit User back-end page
        if( $location == 'user_edit' && apply_filters( 'wppb_backend_allow_multiple_user_roles_selection', true ) ) {
            add_action( 'admin_head', array( $this, 'print_styles_user_edit' ) );
            add_action( 'admin_footer', array( $this, 'print_scripts_user_edit' ), 25 );
        }

    }

    // Enqueue jQuery where needed (use action)
    function enqueue_jquery() {

        wp_enqueue_script( 'jquery' );

    }

    // Print scripts on Add User back-end page
    function print_scripts_user_new() {

        ?>
        <script>
            jQuery( document ).ready( function() {
                // Remove WordPress default Role Select
                var roles_dropdown = jQuery( 'select#role' );
                roles_dropdown.closest( 'tr' ).remove();
            } );
        </script>

    <?php
    }

    // Print scripts on Edit User back-end page
    function print_scripts_user_edit() {

        ?>
        <script>
            jQuery( document ).ready(
                // Remove WordPress default Role Select
                function() {
                    jQuery( '.user-role-wrap' ).remove();
                }
            );
        </script>

    <?php
    }

    // Print scripts on Edit User back-end page
    function print_styles_user_edit() {

        ?>
        <style type="text/css">
            /* Hide WordPress default Role Select */
            .user-role-wrap {
                display: none !important;
            }
        </style>

    <?php
    }

}

$wppb_generalSettings = get_option( 'wppb_general_settings', 'not_found' );
if( $wppb_generalSettings != 'not_found' ) {
    if( ! empty( $wppb_generalSettings['rolesEditor'] ) && ( $wppb_generalSettings['rolesEditor'] == 'yes' ) ) {
        $wppb_role_editor_instance = new WPPB_Roles_Editor();
    }
}
