<?php namespace CheckEmail\Core\UI\list_table;

use CheckEmail\Util;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . WPINC . '/class-wp-list-table.php';
}

/**
 * Table to display Check Email Logs.
 */
class Check_Email_Log_List_Table extends \WP_List_Table {

	protected $page;

	public function __construct( $page, $args = array() ) {
		$this->page = $page;

		$args = wp_parse_args( $args, array(
			'singular' => 'check-email-log',     // singular name of the listed records
			'plural'   => 'check-email-logs',    // plural name of the listed records
			'ajax'     => false,           // does this table support ajax?
			'screen'   => $this->page->get_screen(),
		) );

		parent::__construct( $args );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
		);

		foreach ( array( 'sent_date', 'result', 'to_email', 'from_email', 'subject' ) as $column ) {
			$columns[ $column ] = Util\wp_chill_check_email_get_column_label( $column );
		}

		return apply_filters( 'check_email_manage_log_columns', $columns );
	}

	protected function get_sortable_columns() {
		$sortable_columns = array(
			'sent_date' => array( 'sent_date', true ), // true means it's already sorted.
			'to_email'  => array( 'to_email', false ),
			'from_email'=> array( 'from_email', false ),
			'subject'   => array( 'subject', false ),
		);

		return $sortable_columns;
	}

	protected function column_default( $item, $column_name ) {

		do_action( 'check_email_display_log_columns', $column_name, $item );
	}

	protected function column_sent_date( $item ) {
		$email_date = mysql2date(
			sprintf( esc_html__( '%s @ %s', 'check-email' ), get_option( 'date_format', 'F j, Y' ), 'g:i:s a' ),
			$item->sent_date
		);

		$actions = array();

		$content_ajax_url = add_query_arg(
			array(
				'action' => 'check-email-log-list-view-message',
				'log_id' => $item->id,
				'width'  => '800',
				'height' => '550',
			),
			'admin-ajax.php'
		);

		$actions['view-content'] = sprintf( '<a href="%1$s" class="thickbox" title="%2$s">%3$s</a>',
			esc_url( $content_ajax_url ),
			esc_html__( 'Email Content', 'check-email' ),
			esc_html__( 'View Content', 'check-email' )
		);

		$delete_url = add_query_arg(
			array(
				'page'                   => ( isset( $_REQUEST['page'] ) ) ? sanitize_text_field( wp_unslash($_REQUEST['page']) ) : '',
				'action'                 => 'check-email-log-list-delete',
				$this->_args['singular'] => $item->id,
			)
		);
		$delete_url = add_query_arg( $this->page->get_nonce_args(), $delete_url );

		$actions['delete'] = sprintf( '<a href="%s">%s</a>',
			esc_url( $delete_url ),
			esc_html__( 'Delete', 'check-email' )
		);

		$actions = apply_filters( 'check_email_row_actions', $actions, $item );

		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $email_date,
			/*$2%s*/ $item->id,
			/*$3%s*/ $this->row_actions( $actions )
		);
	}

	protected function column_to_email( $item ) {

		$email = apply_filters( 'check_email_log_list_column_to_email', esc_html( $item->to_email ) );

		return $email;
	}

	protected function column_from_email( $item ) {
		$from = '';
		if (isset($item->headers) && !empty($item->headers)) {

			if ( function_exists('imap_rfc822_parse_headers' ) ) {

				$headers = imap_rfc822_parse_headers($item->headers);
				if (isset($headers->fromaddress) && !empty($headers->fromaddress)) {
					$from = $headers->fromaddress;
				}
			}
			else {
				$find_from = substr($item->headers, strpos($item->headers, 'From') + 5 );
				echo esc_html( $find_from );

			}
		}
		$email = apply_filters( 'check_email_log_list_column_from_email', esc_html( $from ) );
		return $email;
	}

	protected function column_subject( $item ) {
		return esc_html( $item->subject );
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item->id
		);
	}

	protected function column_result( $item ) {

		if ( is_null( $item->result ) ) {
			return '';
		}

		$icon = \CheckEmail\Util\wp_chill_check_email_get_dismiss_icon();
		if ( $item->result ) {
			$icon = \CheckEmail\Util\wp_chill_check_email_get_confirm_icon();
		}

		if ( ! isset( $item->error_message ) ) {
			return $icon;
		}

		return sprintf(
			'<span class="%3$s" title="%2$s">%1$s</span>',
			$icon,
			esc_attr( $item->error_message ),
			'check-email-help'
		);
	}

	protected function get_bulk_actions() {
		$actions = array(
			'check-email-log-list-delete'     => esc_html__( 'Delete', 'check-email' ),
			'check-email-log-list-delete-all' => esc_html__( 'Delete All Logs', 'check-email' ),
		);
		$actions = apply_filters( 'el_bulk_actions', $actions );

		return $actions;
	}

	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		// Get current page number.
		$current_page_no = $this->get_pagenum();
		$per_page        = $this->page->get_per_page();

		list( $items, $total_items ) = $this->page->get_table_manager()->fetch_log_items( $_GET, $per_page, $current_page_no );

		$this->items = $items;

		// Register pagination options & calculations.
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	public function no_items() {
		esc_html_e( 'Your email log is empty', 'check-email' );
	}

	public function search_box( $text, $input_id ) {
		$input_text_id  = $input_id . '-search-input';
		$input_date_id  = $input_id . '-search-date-input';
		$input_date_val = ( ! empty( $_REQUEST['d'] ) ) ? sanitize_text_field( wp_unslash($_REQUEST['d']) ) : '';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash($_REQUEST['orderby']) ) ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash($_REQUEST['order']) ) ) . '" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( sanitize_text_field( wp_unslash($_REQUEST['post_mime_type']) ) ) . '" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( sanitize_text_field( wp_unslash($_REQUEST['detached']) ) ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_date_id ); ?>" name="d" value="<?php echo esc_attr( $input_date_val ); ?>" placeholder="<?php esc_attr_e( 'Search by date', 'check-email' ); ?>" />
			<input type="search" id="<?php echo esc_attr( $input_text_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search by term', 'check-email' ); ?>" />
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}
}
