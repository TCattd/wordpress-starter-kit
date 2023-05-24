<?php namespace CheckEmail\Core\DB;

/**
 * Handle installation and db table creation.
 */
use CheckEmail\Core\Loadie;
use CheckEmail\Util;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Helper class to create table.
 */
class Check_Email_Table_Manager implements Loadie {

	/* Database table name */
	const LOG_TABLE_NAME = 'check_email_log';

	/* Database option name */
	const DB_OPTION_NAME = 'check_email-log-db';

	/* Database version */
	const DB_VERSION = '0.3';

	/**
	 * Setup hooks.
	 */
	public function load() {
		add_action( 'wpmu_new_blog', array( $this, 'create_table_for_new_blog' ) );

		add_filter( 'wpmu_drop_tables', array( $this, 'delete_table_from_deleted_blog' ) );

		// Do any DB upgrades.
		$this->update_table_if_needed();
	}

	public function on_activate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			// Note: if there are more than 10,000 blogs or
			// if `wp_is_large_network` filter is set, then this may fail.
			$sites = get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->create_table_if_needed();
				restore_current_blog();
			}
		} else {
			$this->create_table_if_needed();
		}
	}

	/**
	 * Create email log table when a new blog is created.
	 */
	public function create_table_for_new_blog( $blog_id ) {
		if ( is_plugin_active_for_network( 'check-email-log/check-email.php' ) ) {
			switch_to_blog( $blog_id );
			$this->create_table_if_needed();
			restore_current_blog();
		}
	}

	/**
	 * Add email log table to the list of tables deleted when a blog is deleted.
	 */
	public function delete_table_from_deleted_blog( $tables ) {
		$tables[] = $this->get_log_table_name();

		return $tables;
	}

	/**
	 * Get email log table name.
	 */
	public function get_log_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::LOG_TABLE_NAME;
	}

	public function insert_log( $data ) {
		global $wpdb;

		$table_name = $this->get_log_table_name();
		$wpdb->insert( $table_name, $data );
	}

	public function delete_logs( $ids ) {
		global $wpdb;

		$table_name = $this->get_log_table_name();
                
		$ids = esc_sql( $ids );

		return $wpdb->query( "DELETE FROM {$table_name} where id IN ( {$ids} )" ); 
	}

	public function delete_all_logs() {
		global $wpdb;

		$table_name = $this->get_log_table_name();

		return $wpdb->query( "DELETE FROM {$table_name}" );
	}

	public function delete_logs_older_than( $interval_in_days ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query              = $wpdb->prepare( "DELETE FROM {$table_name} WHERE sent_date < DATE_SUB( CURDATE(), INTERVAL %d DAY )", $interval_in_days );
		$deleted_rows_count = $wpdb->query( $query );

		return $deleted_rows_count;
	}

	public function fetch_log_items_by_id( $ids = array(), $additional_args = array() ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query = "SELECT * FROM {$table_name}";

		$date_column_format_key = 'date_column_format';
		if ( array_key_exists( $date_column_format_key, $additional_args ) && ! empty( $additional_args[ $date_column_format_key ] ) ) {
			$query = "SELECT DATE_FORMAT(sent_date, \"{$additional_args[ $date_column_format_key ]}\") as sent_date_custom, el.* FROM {$table_name} as el";
		}

		if ( ! empty( $ids ) ) {
			$ids = array_map( 'absint', $ids );

			// Can't use wpdb->prepare for the below query.
			$ids_list = esc_sql( implode( ',', $ids ) );

			$query .= " where id IN ( {$ids_list} )";
		}

		return $wpdb->get_results( $query, 'ARRAY_A' ); //@codingStandardsIgnoreLine
	}
        
	public function fetch_log_items( $request, $per_page, $current_page_no ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query       = 'SELECT * FROM ' . $table_name;
		$count_query = 'SELECT count(*) FROM ' . $table_name;
		$query_cond  = '';

		if ( isset( $request['s'] ) && is_string( $request['s'] ) && $request['s'] !== '' ) {
			$search_term = trim( esc_sql( $request['s'] ) );

			if ( Util\wp_chill_check_email_advanced_search_term( $search_term ) ) {
				$predicates = Util\wp_chill_check_email_get_advanced_search_term_predicates( $search_term );

				foreach ( $predicates as $column => $email ) {
					switch ( $column ) {
						case 'id':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= "id = '$email'";
							break;
						case 'to':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= "to_email LIKE '%$email%'";
							break;
						case 'email':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= " ( to_email LIKE '%$email%' OR subject LIKE '%$email%' ) "; /* Begin 2nd & End 2nd */
							$query_cond .= ' OR ';
							$query_cond .= ' ( '; /* Begin 3rd */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 4th */
							$query_cond .= "headers REGEXP '[F|f]rom:.*$email' OR ";
							$query_cond .= "headers REGEXP '[CC|Cc|cc]:.*$email' OR ";
							$query_cond .= "headers REGEXP '[BCC|Bcc|bcc]:.*$email' OR ";
							$query_cond .= "headers REGEXP '[R|r]eply-[T|t]o:.*$email'";
							$query_cond .= ' ) '; /* End 4th */
							$query_cond .= ' ) '; /* End 3rd */
							$query_cond .= ' ) '; /* End 1st */
							break;
						case 'cc':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 2nd */
							$query_cond .= "headers REGEXP '[CC|Cc|cc]:.*$email' ";
							$query_cond .= ' ) '; /* End 2nd */
							$query_cond .= ' ) '; /* End 1st */
							break;
						case 'bcc':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 2nd */
							$query_cond .= "headers REGEXP '[BCC|Bcc|bcc]:.*$email' ";
							$query_cond .= ' ) '; /* End 2nd */
							$query_cond .= ' ) '; /* End 1st */
							break;
						case 'reply-to':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 2nd */
							$query_cond .= "headers REGEXP '[R|r]eply-to:.*$email' ";
							$query_cond .= ' ) '; /* End 2nd */
							$query_cond .= ' ) '; /* End 1st */
							break;
					}
				}
			} else {
				$query_cond .= " WHERE ( to_email LIKE '%$search_term%' OR subject LIKE '%$search_term%' ) ";
			}
		}

		if ( isset( $request['d'] ) && $request['d'] !== '' ) {
			$search_date = trim( esc_sql( $request['d'] ) );
			if ( '' === $query_cond ) {
				$query_cond .= " WHERE sent_date BETWEEN '$search_date 00:00:00' AND '$search_date 23:59:59' ";
			} else {
				$query_cond .= " AND sent_date BETWEEN '$search_date 00:00:00' AND '$search_date 23:59:59' ";
			}
		}

		// Ordering parameters.
		$orderby = ! empty( $request['orderby'] ) ? sanitize_sql_orderby( $request['orderby'] ) : 'sent_date';
		if ( isset( $request['order'] ) ) {
			$order = in_array( strtoupper($request['order']), array( 'DESC', 'ASC' ) ) ? esc_sql( $request['order'] ) : 'DESC';
		}else{
			$order = 'DESC';
		}
		

		if ( ! empty( $orderby ) & ! empty( $order ) ) {
			$query_cond .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		// Find total number of items.
		$count_query = $count_query . $query_cond;
		$total_items = $wpdb->get_var( $count_query );

		// Adjust the query to take pagination into account.
		if ( ! empty( $current_page_no ) && ! empty( $per_page ) ) {
			$offset     = ( $current_page_no - 1 ) * $per_page;
			$query_cond .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
		}

		// Fetch the items.
		$query = $query . $query_cond;
		$items = $wpdb->get_results( $query );

		return array( $items, $total_items );
	}

	public function create_table_if_needed() {
		global $wpdb;

		$table_name = $this->get_log_table_name();

		if ( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {

			$sql = $this->get_create_table_query();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			add_option( self::DB_OPTION_NAME, self::DB_VERSION );
		}
	}

	public function get_logs_count() {
		global $wpdb;

		$query = 'SELECT count(*) FROM ' . $this->get_log_table_name();

		return $wpdb->get_var( $query );
	}

	public function fetch_log_id_by_data( $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return 0;
		}

		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query      = "SELECT ID FROM {$table_name}";
		$query_cond = '';
		$where      = array();

		// Execute the following `if` conditions only when $data is array.
		if ( array_key_exists( 'to', $data ) ) {
			// Since the value is stored as CSV in DB, convert the values from error data to CSV to compare.
			$to_email = Util\wp_chill_check_email_stringify( $data['to'] );

			$to_email = trim( esc_sql( $to_email ) );
			$where[]  = "to_email = '$to_email'";
		}

		if ( array_key_exists( 'subject', $data ) ) {
			$subject = trim( esc_sql( $data['subject'] ) );
			$where[] = "subject = '$subject'";
		}

		if ( array_key_exists( 'attachments', $data ) ) {
			if ( is_array( $data['attachments'] ) ) {
				$attachments = count( $data['attachments'] ) > 0 ? 'true' : 'false';
			} else {
				$attachments = empty( $data['attachments'] ) ? 'false' : 'true';
			}
			$attachments = trim( esc_sql( $attachments ) );
			$where[]     = "attachments = '$attachments'";
		}

		foreach ( $where as $index => $value ) {
			$query_cond .= 0 === $index ? ' WHERE ' : ' AND ';
			$query_cond .= $value;
		}

		// Get only the latest logged item when multiple rows match.
		$query_cond .= ' ORDER BY id DESC LIMIT 1';

		$query = $query . $query_cond;

		return absint( $wpdb->get_var( $query ) );
	}

	public function mark_log_as_failed( $log_item_id, $message ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$wpdb->update(
			$table_name,
			array(
				'result'        => '0',
				'error_message' => $message,
			),
			array( 'ID' => $log_item_id ),
			array(
				'%d', // `result` format.
				'%s', // `error_message` format.
			),
			array(
				'%d', // `ID` format.
			)
		);
	}

	private function update_table_if_needed() {
		if ( get_option( self::DB_OPTION_NAME, false ) === self::DB_VERSION ) {
			return;
		}

		$sql = $this->get_create_table_query();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_OPTION_NAME, self::DB_VERSION );
	}

	private function get_create_table_query() {
		global $wpdb;
		$table_name      = $this->get_log_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = 'CREATE TABLE ' . $table_name . ' (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				to_email VARCHAR(500) NOT NULL,
				subject VARCHAR(500) NOT NULL,
				message TEXT NOT NULL,
				headers TEXT NOT NULL,
				attachments TEXT NOT NULL,
				sent_date timestamp NOT NULL,
				attachment_name VARCHAR(1000),
				ip_address VARCHAR(15),
				result TINYINT(1),
				error_message VARCHAR(1000),
				PRIMARY KEY  (id)
			) ' . $charset_collate . ';';

		return $sql;
	}

	private function validate_columns( $column ) {
		return in_array( $column, array( 'to' ), true );
	}

	public function query_log_items_by_column( $columns ) {
		if ( ! is_array( $columns ) ) {
			return;
		}
                
		$columns_keys = array_keys( $columns );
		if ( ! array_filter( $columns_keys, array( $this, 'validate_columns' ) ) ) {
			return;
		}

		global $wpdb;

		$table_name = $this->get_log_table_name();
		$query      = "SELECT id, sent_date, to_email, subject FROM {$table_name}";
		$query_cond = '';
		$where      = array();

		// Execute the following `if` conditions only when $data is array.
		if ( array_key_exists( 'to', $columns ) ) {
			// Since the value is stored as CSV in DB, convert the values from error data to CSV to compare.
			$to_email = Util\wp_chill_check_email_stringify( $columns['to'] );

			$to_email = trim( esc_sql( $to_email ) );
			$where[]  = "to_email = '$to_email'";

			foreach ( $where as $index => $value ) {
				$query_cond .= 0 === $index ? ' WHERE ' : ' AND ';
				$query_cond .= $value;
			}

			// Get only the latest logged item when multiple rows match.
			$query_cond .= ' ORDER BY id DESC';

			$query = $query . $query_cond;

			return $wpdb->get_results( $query );
		}
	}
}
