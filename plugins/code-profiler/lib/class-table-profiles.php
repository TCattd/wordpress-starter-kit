<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================

class CodeProfiler_Table_Profiles extends WP_List_Table {


	private $default_files = [
		'diskio',
		'iostats',
		'slugs',
		'summary',
		'composer'
	];
	private $abspath;
	private $row_count  = 0;

	/********************************************************************
	 * Initialize
	 */
	function __construct() {

		$this->abspath = rtrim( ABSPATH, '/\\');

		parent::__construct( array(
			'singular' => esc_html__('profile', 'code-profiler'),
			'plural'   => esc_html__('profiles', 'code-profiler'),
			'ajax'     => false
		));
    }

	/********************************************************************
	 * Empty list
	 */
	function no_items() {
		esc_html_e('No profile found.', 'code-profiler');
	}

	/********************************************************************
	 * Default
	 */
	function column_default( $item, $column_name ) {

		if ( $item[ $column_name ] == '-') {
			// Old profiles (CP <=1.2) must not call number_format()
			return $item[ $column_name ];
		}
		switch( $column_name ) {
			case 'date':
				return date('Y/m/d \@ H:i', $item[ $column_name ] );
				break;
			case 'time':
				return $item[ $column_name ] .' s';
				break;
			case 'mem':
				return number_format( $item[ $column_name ], 2 ) .' MB';
				break;
			case 'io':
			case 'queries':
			case 'items':
				return number_format( $item[ $column_name ] );
				break;
			default:
				return '';
		}
	}

	/********************************************************************
	 * Sortable columns
	 */
	function get_sortable_columns() {
		return array(
			'profile'	=> array( 'profile', true ),
			'date' 		=> array( 'date', true ),
			'items' 		=> array( 'items', true ),
			'time' 		=> array( 'time', true ),
			'mem' 		=> array( 'mem', true ),
			'io' 			=> array( 'io', true ),
			'queries'	=> array( 'queries', true )
		);
	}

	/********************************************************************
	 * Columns
	 */
	function get_columns(){
		return array(
			'cb'			=> '<input type="checkbox" />',
			'profile'	=> esc_html__( 'Profile', 'code-profiler' ),
			'date'		=> esc_html__( 'Date', 'code-profiler' ),
			'items'		=> esc_html__( 'Items', 'code-profiler' ),
			'time'		=> esc_html__( 'Time', 'code-profiler' ),
			'mem'			=> esc_html__( 'Memory', 'code-profiler' ),
			'io'			=> esc_html__( 'File I/O', 'code-profiler' ),
			'queries'	=> esc_html__( 'SQL', 'code-profiler' )
		);
    }

	/********************************************************************
	 * Sorting
	 */
	function usort_reorder( $a, $b ) {
		// Sort by date by default
		$orderby = (! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'ID';
		$order   = (! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'asc';
		$result  = $this->cmp_num_or_string( $a[$orderby], $b[$orderby] );
		return ( $order === 'asc') ? $result : -$result;
	}

	/********************************************************************
	 * Sort string and numeric values differently
	 */
	function cmp_num_or_string( $a, $b ) {
		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			return ($a-$b) ? ($a-$b)/abs($a-$b) : 0;
		} else {
			return strcmp( $a, $b );
		}
	}


	/********************************************************************
	 * Row action links
	 */
	function column_profile( $item ) {

		$this->row_count++;

		// Keep sorting order for the "delete" action link
		$orderby	= (! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'ID';
		$order	= (! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'asc';

		$actions = array(
			'view'   => sprintf(
				'<a href="?page=code-profiler&cptab=profiles_list&action=%s&id=%s&section=1">%s</a>',
				'view_profile',
				esc_attr( $item['ID'] ),
				esc_html__('View')
			),
			'edit'   => sprintf(
				'<a style="cursor:pointer" onClick="cpjs_toggle_name(\'%s\')">%s</a>',
				esc_attr( $this->row_count ),
				esc_html__('Quick Edit')
			),
			'delete' => sprintf(
				'<a href="?page=code-profiler&cptab=profiles_list&action=%s&profiles[]=%s&_wpnonce=%s&orderby=%s&order=%s" '.
				'onclick="return cpjs_delete_profile();">%s</a>',
				'delete_profiles',
				esc_attr( $item['ID'] ),
				wp_create_nonce('bulk-'. $this->_args['plural'] ),
				$orderby,
				$order,
				esc_html__('Delete')
			)
		);

		$profile_name = sprintf(
			'<div id="profile_name_%1$s">%2$s</div>'.
			'<div id="profile_div_%1$s" style="display:none">'.
				'<input type="text" id="edit-%1$s" name="edit_%3$s" value="" maxlength="100" />'.
				'<p>'.
					'<input type="button" class="button-primary button button-small" value="%4$s" onClick="cpjs_edit_name(\'%3$s\', \'%1$s\', \'%6$s\', \'%1$s\')"/>'.
					'&nbsp;'.
					'<input type="button" class="button-secondary button button-small" value="%5$s" onClick="cpjs_toggle_name(\'%1$s\')"/>'.
					'&nbsp;'.
					'<span id="profile_spinner_%1$s" class="spinner" style="float:none"></span>'.
				'</p>'.
			'</div>',
			esc_attr( $this->row_count ),
			esc_html( $item['profile'] ),
			esc_attr( $item['ID'] ),
			esc_attr__('Update'),
			esc_attr__('Cancel'),
			wp_create_nonce('rename-profile')
		);

		return sprintf('%1$s %2$s', $profile_name, $this->row_actions( $actions ) );
	}

	/********************************************************************
	 * Bulk action menu (delete)
	 */
	function get_bulk_actions() {
		return [
			'delete_profiles' => esc_html__('Delete')
		];
	}

	/********************************************************************
	 * Checkboxes
	 */
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="profiles[]" value="%s" />',
			esc_attr( $item['ID'] )
		);
	}

	/********************************************************************
	 * Prepare to display profiles
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Fetch our data
		$profile = $this->fetch_profiles();
		usort( $profile, array( &$this, 'usort_reorder') );

		$per_page = 30;

		// If we just delete some profiles, we go back to page #1:
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete_profiles') {
			$current_page = 1;
		} else {
			$current_page = $this->get_pagenum();
		}

		$total_items = count( $profile );
		$this->items = array_slice( $profile,( ( $current_page-1 )* $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		));
	}

	/********************************************************************
	 * Retrieve all profiles
	 */
	function fetch_profiles() {

		$profiles = [];
		$glob = glob( CODE_PROFILER_UPLOAD_DIR .'/*.slugs.profile');

		if ( is_array( $glob ) ) {
			$count = 0;
			foreach( $glob as $path ) {
				$file = basename( $path );
				if ( preg_match('`^(\d{10}\.\d+)\.(.+?)\.(?:slugs)\.profile$`', $file, $match ) ) {

					$error = 0; $fsize = 0;
					// Make sure we have all profile files
					foreach( $this->default_files as $pname ) {
						// Ignore these ones, there aren't mandatory
						if ( in_array( $pname, [ 'composer', 'summary' ] ) ) { continue; }
						if ( file_exists( CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.$pname.profile" ) ) {
							$fsize += filesize( CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.$pname.profile" );
						} else {
							$error = 1;
							break;
						}
					}
					// Delete if incomplete
					if ( $error ) {
						foreach( $this->default_files as $pname ) {
							if ( file_exists( CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.$pname.profile" ) ) {
								unlink( CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.$pname.profile" );
							}
						}
						continue;
					}

					// Search query
					$search = false;
					if (! empty( $_REQUEST['s'] ) ) {
						foreach( $this->default_files as $pname ) {
							// Ignore these ones, there aren't mandatory
							if ( in_array( $pname, [ 'composer', 'summary' ] ) ) { continue; }
							$search = $this->search_profile_file(
								CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.$pname.profile",
								sanitize_text_field( $_REQUEST['s'] )
							);
							if ( $search === true ) {
								break;
							}
						}
					}
					if (! empty( $_REQUEST['s'] ) && $search === false ) {
						continue;
					}

					// Fetch the summary file (CP >1.2)
					if ( file_exists( CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.summary.profile" ) ) {
						$summary = json_decode(
							file_get_contents( CODE_PROFILER_UPLOAD_DIR ."/{$match[1]}.{$match[2]}.summary.profile" ), true
						);
					}
					if ( empty( $summary['memory'] ) )	{ $summary['memory'] 	= '-'; }
					if ( empty( $summary['queries'] ) ) { $summary['queries']	= '-'; }
					if ( empty( $summary['time'] ) ) 	{ $summary['time']		= '-'; }
					if ( empty( $summary['items'] ) ) 	{ $summary['items']		= '-'; }
					if ( empty( $summary['io'] ) ) 		{ $summary['io']			= '-'; }

					$fsize 								+= filesize( $path );
					$profiles[$count]['ID'] 		= $match[1];
					$profiles[$count]['profile']	= esc_html( $match[2] );
					$profiles[$count]['date']		= filemtime( $path );
					$profiles[$count]['mem']		= $summary['memory'];
					$profiles[$count]['time']		= $summary['time'];
					$profiles[$count]['queries']	= $summary['queries'];
					$profiles[$count]['items']		= $summary['items'];
					$profiles[$count]['io']			= $summary['io'];
					$count++;
				}
			}
		}
		return $profiles;
	}

	/********************************************************************
	 * Search a profile file for a string.
	 */
	private function search_profile_file( $file, $string ) {

		// Search in the filename too
		if ( stripos( $file, $string ) !== false ) {
			return true;
		}

		$fh = fopen( $file, 'rb');
		if ( $fh === false ) {
			return false;
		}
		while(! feof( $fh ) ) {
			$line = fgets( $fh );
			// Remove the ABSPATH, we don't include it in the search
			$line = ltrim( str_replace( $this->abspath, '', $line ), '\\/');
			if ( stripos( $line, $string ) !== false ) {
				fclose( $fh );
				return true;
			}
		}

		fclose( $fh );
		return false;
	}

	/********************************************************************
	 * Delete one or more profiles.
	 */
	 public function delete_profiles( $profiles ) {

		$error = false;
		foreach( $profiles as $name ) {
			$profile_name = code_profiler_get_profile_path( $name );
			if ( $profile_name === false ) {
				$error = true;
				continue;
			}
			foreach( $this->default_files as $pname ) {
				if ( file_exists( "$profile_name.$pname.profile" ) ) {
					unlink( "$profile_name.$pname.profile" );
				}
			}
		}
		return $error;
	}

}

// =====================================================================
// EOF
