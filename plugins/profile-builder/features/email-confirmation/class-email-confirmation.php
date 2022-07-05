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
class wpp_list_unfonfirmed_email_table extends PB_WP_List_Table {
    
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
        switch($column_name){
            case 'email':
                return $item[$column_name];
            case 'registered':
                return date_i18n( "Y-m-d G:i:s", wppb_add_gmt_offset( strtotime( $item[$column_name] ) ) );
            case 'user-meta':
                global $wpdb;
                $sql_result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", $item['email'] ), ARRAY_A );
                $user_meta = $sql_result['meta'];
                $user_meta_content = '';
                if( !empty( $user_meta ) ){
                    foreach( maybe_unserialize( $user_meta ) as $key => $value ){
                        if( $key != 'user_pass' ){
                            if ( is_array($value) ) $value = implode(',',$value);
                            $user_meta_content .= $key.':'.$value.'<br/>';
                        }
                    }
                }
                return '<a href="#" data-email="'. $item['email'] .'" onclick="'. esc_attr( 'jQuery(\'<div><pre>'. $user_meta_content .'</pre></div>\').dialog({title:\''. addslashes( __("User Meta", "profile-builder" ) ) .'\', width: 500 }) ;return false;') .'">'. __( 'show', 'profile-builder' ) .'</a>';
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
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
		
		$GRavatar = get_avatar( $item['email'], 32, '' );
		
        //Build row actions
        $actions = array(
            'delete'    => sprintf( '<a href="javascript:confirmECAction( \'%s\', \'%s\', \'%s\', \'' . addslashes( __( 'delete this user from the _signups table?', 'profile-builder' ) ) . '\' )">' . __( 'Delete', 'profile-builder' ) . '</a>', wppb_curpageurl(), 'delete', $item['ID'] ),
            'confirm'   => sprintf( '<a href="javascript:confirmECAction( \'%s\', \'%s\', \'%s\', \'' . addslashes( __( 'confirm this email yourself?', 'profile-builder' ) ) . '\' )">' . __( 'Confirm Email', 'profile-builder' ) . '</a>', wppb_curpageurl(), 'confirm', $item['ID'] ),
            'resend'    => sprintf( '<a href="javascript:confirmECAction( \'%s\', \'%s\', \'%s\', \'' . addslashes( __( 'resend the activation link?', 'profile-builder' ) ) . '\' )">' . __( 'Resend Activation Email', 'profile-builder' ) . '</a>', wppb_curpageurl(), 'resend', $item['ID'] )
        );
        
        //Return the user row
        return sprintf('%1$s <strong>%2$s</strong> %3$s',
            /*$1%s*/ $GRavatar,
            /*$2%s*/ $item['username'],
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
            'username'     	=> __( 'Username', 'profile-builder' ),
            'email'    		=> __( 'Email', 'profile-builder' ),
            'registered'  	=> __( 'Registered', 'profile-builder' ),
            'user-meta'  	=> __( 'User Meta', 'profile-builder' )
        );
		
        return $columns;
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
            'email'    		=> array('email',false),
            'registered'  	=> array('registered',false)
        );
		
        return $sortable_columns;
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
            'delete'    => __( 'Delete', 'profile-builder' ),
			'confirm'	=> __( 'Confirm Email', 'profile-builder' ),
			'resend'	=> __( 'Resend Activation Email', 'profile-builder' )
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
		
	function wppb_process_bulk_action_message( $message, $url ){
		
		echo '<script type=\'text/javascript\'>confirmECActionBulk( "'.esc_js( $url ).'", "'.esc_js( $message ).'" )</script>';
	}	

    function wppb_process_bulk_action() {
		global $current_user;
		global $wpdb;
		
		if ( current_user_can( apply_filters( 'wppb_email_confirmation_user_capability', 'manage_options' ) ) ){
			if( 'delete' === $this->current_action() ) {
			    if( !empty( $_GET['user'] ) && is_array( $_GET['user'] ) ) {
                    foreach (  array_map( 'sanitize_email', $_GET['user'] ) as $user) {
                        $sql_result = $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", $user));

                        if (!$sql_result)
                            $this->wppb_process_bulk_action_message(sprintf(__("%s couldn't be deleted", "profile-builder"), $result->user_login), get_bloginfo('url') . '/wp-admin/users.php?page=unconfirmed_emails');

                    }
                }
				
				$this->wppb_process_bulk_action_message( __( 'All users have been successfully deleted', 'profile-builder' ), get_bloginfo('url').'/wp-admin/users.php?page=unconfirmed_emails' );
			
			}elseif( 'confirm' === $this->current_action() ) {
                if( !empty( $_GET['user'] ) && is_array( $_GET['user'] ) ) {
                    foreach ( array_map( 'sanitize_email', $_GET['user'] ) as $user) {
                        $sql_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", $user), ARRAY_A);

                        if ($sql_result)
                            wppb_manual_activate_signup($sql_result['activation_key']);
                    }
                }

				$this->wppb_process_bulk_action_message( __( 'The selected users have been activated', 'profile-builder' ), get_bloginfo('url').'/wp-admin/users.php?page=unconfirmed_emails' );
				
			}elseif( 'resend' === $this->current_action() ) {
                if( !empty( $_GET['user'] ) && is_array( $_GET['user'] ) ) {
                    foreach ( array_map( 'sanitize_email', $_GET['user'] ) as $user ) {
                        $sql_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_email = %s", $user), ARRAY_A);

                        if ($sql_result)
                            wppb_signup_user_notification(esc_sql($sql_result['user_login']), esc_sql($sql_result['user_email']), $sql_result['activation_key'], $sql_result['meta']);

                    }
                }

				$this->wppb_process_bulk_action_message( __( 'The selected users have had their activation emails resent', 'profile-builder' ), get_bloginfo('url').'/wp-admin/users.php?page=unconfirmed_emails' );
			}

		}else
			$this->wppb_process_bulk_action_message( __( "Sorry, but you don't have permission to do that!", "profile-builder" ), get_bloginfo('url').'/wp-admin/' );
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
        global $wpdb;
		
		$this->dataArray = array();

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = get_user_meta( get_current_user_id(), 'users_per_page', true );
        if( !empty($per_page) )
            $per_page = apply_filters('wppb_email_confirmation_user_per_page_number', $per_page);
        else
            $per_page = apply_filters('wppb_email_confirmation_user_per_page_number', 20);

        /* determine offset */
        if( !empty( $_REQUEST['paged'] ) ){
            $offset = ( sanitize_text_field( $_REQUEST['paged'] ) -1 ) * $per_page;
        }
        else
            $offset = 0;

        /* handle order and orderby attr */
        if( !empty( $_REQUEST['orderby'] ) ){
            $orderby = sanitize_sql_orderby( $_REQUEST['orderby'] );
            if( $orderby == 'username' )
                $orderby = 'user_login';
            elseif ( $orderby == 'email' )
                $orderby = 'user_email';
        }
        else
            $orderby = 'user_login';
        if( !empty( $_REQUEST['order'] ) && $_REQUEST['order'] === 'desc' )
            $order = "DESC";
        else
            $order = 'ASC';

        /* handle the WHERE clause */
        $where = "active = 0";
        if( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) ){
            $where .= $wpdb->prepare( " AND ( user_login LIKE %s OR user_email LIKE %s OR registered LIKE %s )", '%' . sanitize_text_field( $_REQUEST['s'] ) . '%', '%' . sanitize_text_field( $_REQUEST['s'] ) . '%' , '%' . sanitize_text_field( $_REQUEST['s'] ) . '%' );
        }
        /* since version 2.0.7 for multisite we add a 'registered_for_blog_id' meta in the registration process
            so we can display only the users registered on that blog. Also for backwards compatibility we display the users that don't have that meta at all */
        if( is_multisite() ){
            $where .= " AND ( meta NOT LIKE '%\"registered_for_blog_id\"%' OR meta LIKE '%\"registered_for_blog_id\";i:".get_current_blog_id()."%' )";
        }
		
		$results = $wpdb->get_results("SELECT * FROM ".$wpdb->base_prefix."signups WHERE $where ORDER BY $orderby $order LIMIT $offset, $per_page");

		foreach ($results as $result){
            $tempArray = array('ID' => $result->user_email, 'username' => $result->user_login, 'email' => $result->user_email, 'registered'  => $result->registered);
            array_push($this->dataArray, $tempArray);
		}

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix."signups WHERE $where");
        
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
        $this->wppb_process_bulk_action();
        
        
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
function wppb_add_ec_submenu_page() {
    $wppb_generalSettings = get_option('wppb_general_settings', 'not_found');
    if($wppb_generalSettings != 'not_found') {
        if( !empty($wppb_generalSettings['emailConfirmation']) && ($wppb_generalSettings['emailConfirmation'] == 'yes') ){
            $hook = add_submenu_page('users.php', 'Unconfirmed Email Address', 'Unconfirmed Email Address', 'manage_options', 'unconfirmed_emails', 'wppb_unconfirmed_email_address_custom_menu_page');
            add_action( "load-$hook", 'wppb_ec_screen_options' ); //add screen options to Unconfirmed Email Users page
            remove_submenu_page('users.php', 'unconfirmed_emails'); //hide the page in the admin menu
        }
    }
}
add_action('admin_menu', 'wppb_add_ec_submenu_page');



/***************************** RENDER PAGE ********************************
 *******************************************************************************
 * This function renders the admin page. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function wppb_unconfirmed_email_address_custom_menu_page(){
    
    //Create an instance of our package class...
    $listTable = new wpp_list_unfonfirmed_email_table();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div class="wrap"><div id="icon-users" class="icon32"></div><h2><?php esc_html_e('Users with Unconfirmed Email Address', 'profile-builder');?></h2></div>
		
		<ul class="subsubsub">
			<li class="all"><a href="users.php"><?php esc_html_e('All Users', 'profile-builder');?></a></li>
		</ul>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <?php $listTable->search_box( __( 'Search Users', 'profile-builder' ), 'user' ); ?>
            <input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( $_REQUEST['page'] ) ) : ''; ?>" />
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
        
    </div>
    <?php
}

/**
 * Function that handles the screen options on Unconfirmed Email Users page
 */
function wppb_ec_screen_options(){
    $option = 'per_page';

    $args = array(
        'label' => __( 'Number of items per page:' , 'profile-builder' ),//use the default wordpress domain
        'default' => 20,
        'option' => 'users_per_page' //this is the same option as the one on the normal User screen
    );

    add_screen_option( $option, $args );
}

/**
 * Fix the page title of the admin page, since using remove_sumbenu_page() breaks the page title
 */
add_filter('admin_title', 'wppb_ec_admin_title', 10 );
function wppb_ec_admin_title($admin_title){
    if( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'unconfirmed_emails' ) {
        $admin_title = __('Users with Unconfirmed Email Address', 'profile-builder') . $admin_title;
    }

    return $admin_title;
}