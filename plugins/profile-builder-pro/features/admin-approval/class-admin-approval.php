<?php
 /*
Code taken from: Custom List Table Example (plugin)
Author: Matt Van Andel
Author URI: http://www.mattvanandel.com
*/

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The PB_WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if( !class_exists( 'PB_WP_List_Table' ) ){
    require_once( WPPB_PLUGIN_DIR.'/assets/lib/class-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core PB_WP_List_Table class.
 * PB_WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 */
class wpp_list_approved_unapproved_users extends PB_WP_List_Table {
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
		global $wpdb;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'user',     //singular name of the listed records
            'plural'    => 'users',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'username', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'username'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * PB_WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        $default = '';
        if( !empty( $item[$column_name] ) )
            $default = $item[$column_name];

        return $default;
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'username'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see PB_WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td>
     **************************************************************************/
    function column_username($item){
		global $current_user;

		$GRavatar = get_avatar( $item['email'], 32, '' );
		$user = apply_filters( 'wppb_admin_approval_user_listing_user_object', get_user_by( 'id', $item['ID'] ), $item );
		$currentUser =  wp_get_current_user();
		$wppb_nonce = wp_create_nonce( '_nonce_'.$current_user->ID.$user->ID);

		if( isset( $_SERVER['REQUEST_URI'] ) )
		    $edit_link = add_query_arg( 'wp_http_referer', urlencode( stripslashes( esc_url_raw( $_SERVER['REQUEST_URI'] ) ) ), get_edit_user_link( $user->ID ) );
		else
            $edit_link = '';
		
		$actions['remove'] = sprintf('<a class=\'edit_view\' href="%s">'. __('Edit', 'profile-builder') .'</a>', $edit_link);
		
        //Build row actions (approve/unapprove), but only for the users different from the currently logged in one
		if ($current_user->ID != $user->ID){
			if ( user_can( $current_user->ID, apply_filters( 'wppb_admin_approval_user_capability', 'manage_options' ) ) )
				$actions['delete'] = sprintf('<a class=\'submitdelete\' href="javascript:confirmAUAction(\'%s\',\'%s\',\'%s\',\'%s\',\''.esc_js(__('delete this user?', 'profile-builder')).'\')">'. esc_js(__('Delete', 'profile-builder')) .'</a>', wppb_curpageurl(), 'delete', $user->ID, $wppb_nonce);

            $user_status = wp_get_object_terms( $user->ID, 'user_status' );
            if ( !$user_status ) {
                $actions['unapproved'] = sprintf('<a href="javascript:confirmAUAction(\'%s\',\'%s\',\'%s\',\'%s\',\'' . esc_js(__('unapprove this user?', 'profile-builder')) . '\')">' . esc_js(__('Unapprove', 'profile-builder')) . '</a>', wppb_curpageurl(), 'unapprove', $user->ID, $wppb_nonce);
            } else if ( $user_status[0]->slug === 'unapproved' ) {
                $actions['approved'] = sprintf('<a href="javascript:confirmAUAction(\'%s\',\'%s\',\'%s\',\'%s\',\'' . esc_js(__('approve this user?', 'profile-builder')) . '\')">' . esc_js(__('Approve', 'profile-builder')) . '</a>', wppb_curpageurl(), 'approve', $user->ID, $wppb_nonce);
            } else {
                $actions['approved'] = sprintf('<a href="javascript:confirmAUAction(\'%s\',\'%s\',\'%s\',\'%s\',\'' . esc_js(__('approve this user?', 'profile-builder')) . '\')">' . esc_js(__('Approve', 'profile-builder')) . '</a>', wppb_curpageurl(), 'approve', $user->ID, $wppb_nonce);
                $actions['unapproved'] = sprintf('<a href="javascript:confirmAUAction(\'%s\',\'%s\',\'%s\',\'%s\',\'' . esc_js(__('unapprove this user?', 'profile-builder')) . '\')">' . esc_js(__('Unapprove', 'profile-builder')) . '</a>', wppb_curpageurl(), 'unapprove', $user->ID, $wppb_nonce);
            }
		}
			
		

		
        //Return the user row
        return sprintf('%1$s <strong>%2$s</strong> %3$s',
            /*$1%s*/ $GRavatar,
            /*$2%s*/ '<a href="'.$edit_link.'">'.$item['username'].'</a>',
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see PB_WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td>
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see PB_WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        	=> '<input type="checkbox" />', //Render a checkbox instead of text
            'username'     	=> __('Username', 'profile-builder'),
            'first_name'    => __('Firstname', 'profile-builder'),
            'last_name'     => __('Lastname', 'profile-builder'),
            'email'    		=> __('Email', 'profile-builder'),
            'role'    		=> __('Role', 'profile-builder'),
            'registered'  	=> __('Registered', 'profile-builder'),
			'user_status'	=> __('User-status', 'profile-builder')
        );
        return apply_filters( 'wppb_admin_approval_page_columns', $columns );
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'username'     	=> array('username',false),     //true means it's already sorted
            'first_name'	=> array('first_name',false),
            'last_name'		=> array('last_name',false),
            'email'    		=> array('email',false),
            'role'    		=> array('role',false),
            'registered'  	=> array('registered',false),
            'user_status'  	=> array('user_status',false)
        );
		
        return apply_filters( 'wppb_admin_approval_page_sortable_columns', $sortable_columns );
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'approved'		=> __( 'Approve', 'profile-builder' ),
			'unapproved'	=> __( 'Unapprove', 'profile-builder' ),
			'delete' => __( 'Delete', 'profile-builder' )
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
		global $current_user;
		global $wpdb;
		
		if (current_user_can( apply_filters( 'wppb_admin_approval_user_capability', 'manage_options' ) )){
			$wppb_nonce = wp_create_nonce( '_nonce_'.$current_user->ID.'_bulk');
			if (isset($_GET['user']))
				$users = implode(',', array_map( 'absint', $_GET['user'] ) );

			$extra_args = apply_filters('wppb_conditional_redirect', '&orderby=registered&order=desc');	
				
			//Detect when a bulk action is being triggered...
			if( 'approved'===$this->current_action() ) {
				?>
				<script type="text/javascript">
					confirmAUActionBulk('<?php echo esc_js( get_bloginfo('wpurl') ).'/wp-admin/users.php?page=admin_approval'.esc_js( $extra_args );?>', '<?php esc_html_e("Do you want to bulk approve the selected users?", "profile-builder");?>', '<?php echo esc_js( $wppb_nonce );?>', '<?php echo esc_attr( $users );?>', 'bulkApprove');
				</script>
				<?php
	
			
			}elseif( 'unapproved'===$this->current_action() ) {
				?>
				<script type="text/javascript">
					confirmAUActionBulk('<?php echo esc_js(get_bloginfo('wpurl') ).'/wp-admin/users.php?page=admin_approval'.esc_js( $extra_args );?>', '<?php esc_html_e("Do you want to bulk unapprove the selected users?", "profile-builder");?>', '<?php echo esc_js( $wppb_nonce );?>', '<?php echo esc_attr( $users );?>', 'bulkUnapprove');
				</script>
				<?php
			}elseif( 'delete'===$this->current_action() ) {
				?>
				<script type="text/javascript">
					confirmAUActionBulk('<?php echo esc_js( get_bloginfo('wpurl') ).'/wp-admin/users.php?page=admin_approval'. esc_js( $extra_args );?>', '<?php esc_html_e("Do you want to bulk delete the selected users?", "profile-builder");?>', '<?php echo esc_js( $wppb_nonce );?>', '<?php echo esc_attr( $users );?>', 'bulkDelete');
				</script>
				<?php
			}
			
		}else{
			?>
			<script type="text/javascript">
				alert('<?php esc_html_e("Sorry, but you don't have permission to do that!", "profile-builder");?>')
			</script>
			<?php
		}
        
    }
    
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
		$this->dataArray = array();

        $per_page = get_user_meta( get_current_user_id(), 'users_per_page', true );
        if( !empty($per_page) )
            $per_page = apply_filters('wppb_admin_approval_user_per_page_number', $per_page);
        else
            $per_page = apply_filters('wppb_admin_approval_user_per_page_number', 20);

        $current_page = $this->get_pagenum();

        /* set up user query args */
        $args = array(
            'number' => $per_page,
            'offset' => ( $current_page-1 ) * $per_page,
            'fields' => 'all_with_meta'
        );

        if ( isset( $_REQUEST['s'] ) ) {
            $search_for = trim( sanitize_text_field($_REQUEST['s']) );
            $args['search'] = '*' . $search_for . '*';
        }

        if( is_network_admin() )
            $args['blog_id'] = 0;
        else
            $args['blog_id'] = get_current_blog_id();

        if ( isset( $_REQUEST['orderby'] ) )
            $args['orderby'] = sanitize_sql_orderby( $_REQUEST['orderby'] );
        else
            $args['orderby'] = 'username';

        if ( isset( $_REQUEST['order'] ) )
            $args['order'] = sanitize_text_field( $_REQUEST['order'] );
        else
            $args['order'] = 'asc';

        // Query the user IDs for this page
        $wp_user_search = new WP_User_Query( $args );

        $queried_users = $wp_user_search->get_results();
        /* set up our data array from the results */
        foreach( $queried_users as $user ){

            /* get user status */
            $user_status = wp_get_object_terms( $user->ID, 'user_status' );
            if ( !$user_status || is_wp_error($user_status) ) {
                $status = '<span class="dashicons dashicons-yes" style="color: mediumseagreen;"></span>' . __('Approved', 'profile-builder');
            } else if ( $user_status[0]->slug === 'unapproved' ) {
                $status = '<span class="dashicons dashicons-warning" style="color: red;"></span> ' . __('Unapproved', 'profile-builder');
            } else {
                $status = '<span class="dashicons dashicons-plus" style="color: darkgoldenrod;"></span> ' . __('Pending', 'profile-builder');
            }

            $first_name = get_user_meta ( $user->ID, 'first_name', true );
            $last_name = get_user_meta ( $user->ID, 'last_name', true );
            $user_role = ( ! empty( $user->roles ) ? $user->roles[0] : '' );

            $tempArray = array('ID' => $user->ID, 'username' => $user->data->user_login, 'first_name' => trim( $first_name ), 'last_name' => trim( $last_name ), 'email' => $user->data->user_email, 'role'	=> $user_role, 'registered'  => date_i18n( "Y-m-d G:i:s", wppb_add_gmt_offset( strtotime( $user->data->user_registered ) ) ), 'user_status' => $status);
            $tempArray = apply_filters( 'wppb_admin_approval_page_manage_column_data', $tempArray, $user );

            array_push( $this->dataArray, $tempArray );
        }
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->dataArray;

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = $wp_user_search->get_total();

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}





/** ************************ REGISTER THE PAGE ****************************
 *******************************************************************************
 * Now we just need to define an admin page.
 */
function wppb_add_au_submenu_page() {
    $hook = add_submenu_page( 'users.php', 'Admin Approval', 'Admin Approval', apply_filters( 'wppb_admin_approval_user_capability', 'manage_options' ), 'admin_approval', 'wppb_approved_unapproved_users_custom_menu_page' );
    add_action( "load-$hook", 'wppb_au_screen_options' ); //add screen options to Unconfirmed Email Users page
    remove_submenu_page( 'users.php', 'admin_approval' ); //hide the page in the admin menu
}
add_action('admin_menu', 'wppb_add_au_submenu_page');
add_action('network_admin_menu', 'wppb_add_au_submenu_page');



/***************************** RENDER PAGE ********************************
 *******************************************************************************
 * This function renders the admin page. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function wppb_approved_unapproved_users_custom_menu_page(){
    
    //Create an instance of our package class...
    $listTable = new wpp_list_approved_unapproved_users();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div class="wrap"><div id="icon-users" class="icon32"></div><h2><?php esc_html_e('Admin Approval', 'profile-builder');?></h2></div>
		
		<ul class="subsubsub">
			<li class="all"><a href="users.php"><?php esc_html_e('All Users', 'profile-builder');?></a></li>
		</ul>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <?php $listTable->search_box( esc_html__( 'Search Users', 'profile-builder' ), 'user' ); ?>
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( $_REQUEST['page'] ) ) : ''; ?>" />
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
        
    </div>
    <?php
}

/**
 * Function that overwrites the query for user_status sorting (approved / unapproved)
 *
 * Adds several LEFT JOINs to the query, in order to have access to user_status term
 *
 * @since v.2.2.5
 *
 * @param WP_User_Query $query
 *
 */

function wppb_sort_by_admin_approval( $query ){
    global $wpdb;
    if ( is_admin() && isset($_REQUEST['page']) && ( 'admin_approval' == $_REQUEST['page'] ) ) {
        if ( isset( $_REQUEST['orderby']) && ( $_REQUEST['orderby'] == 'user_status' ) ) {
            $order = 'DESC';
            if ( isset($_REQUEST['order']) && $_REQUEST['order'] === 'asc' ) {
                $order = 'ASC';
            }
            $taxonomy1 = get_term_by( 'name', 'unapproved', 'user_status' );
            $taxonomy2 = get_term_by( 'name', 'pending',    'user_status' );

            if ( isset($taxonomy1->term_taxonomy_id, $taxonomy2->term_taxonomy_id) ) {
                $term_taxonomy_id1 = $taxonomy1->term_taxonomy_id;
                $term_taxonomy_id2 = $taxonomy2->term_taxonomy_id;
                $query->query_from .= ' LEFT JOIN (SELECT * FROM ' . $wpdb->term_relationships . ' WHERE term_taxonomy_id = ' . $term_taxonomy_id1 . ' OR term_taxonomy_id = ' . $term_taxonomy_id2 . ') AS tr ON ' . $wpdb->users . '.ID = tr.object_id LEFT JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN ' . $wpdb->terms . ' AS t ON tt.term_id = t.term_id';
                $query->query_orderby = 'ORDER BY t.slug ' . $order . ', user_registered DESC';
            }
        }
    }
}
add_action('pre_user_query', 'wppb_sort_by_admin_approval');

/**
 * Function that handles the screen options on Admin Approval Users page
 */
function wppb_au_screen_options(){
    $option = 'per_page';

    $args = array(
        'label' => __( 'Number of items per page:', 'profile-builder' ),//use the default wordpress domain
        'default' => 20,
        'option' => 'users_per_page' //this is the same option as the one on the normal User screen
    );

    add_screen_option( $option, $args );
}

/**
 * Fix the page title of the admin page, since using remove_sumbenu_page() breaks the page title
 */
add_filter('admin_title', 'wppb_au_admin_title', 10 );
function wppb_au_admin_title($admin_title){
    if( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'admin_approval' ) {
        $admin_title = __('Admin Approval', 'profile-builder') . $admin_title;
    }

    return $admin_title;
}