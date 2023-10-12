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

if (! defined('ABSPATH') ) { die('Forbidden'); }

// =====================================================================
// Display Profiles List tab.

echo code_profiler_display_tabs( 2 );

// Look for actions
$section = 0;
$section_list = [
	1 => esc_html__('Plugins & Theme Performance', 'code-profiler'),
	2 => esc_html__('File I/O Statistics', 'code-profiler'),
	3 => esc_html__('Disk I/O Statistics', 'code-profiler'),
	4 => esc_html__('Pro Features', 'code-profiler')
];
if (! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'view_profile' &&
	! empty( $_REQUEST['section'] ) && ! empty( $section_list[ $_REQUEST['section'] ] ) ) {
	// Make sure the profile exists and get its full path
	$profile_path = code_profiler_get_profile_path( $_REQUEST['id'] );
	if ( $profile_path !== false ) {
		$section = (int) $_REQUEST['section'];
		$id = sanitize_text_field( $_REQUEST['id'] );
	}
}

// Search query: get rid of slashes added by WordPress
if (! empty( $_REQUEST['s'] ) ) {
	$_REQUEST['s'] = sanitize_text_field ( stripslashes( $_REQUEST['s'] ) );
}

if (! empty( $section ) ) {
	// View selected profile
	require 'menu_view_profile.php';

} else {
	// Show profiles list table

	// Load WP_List_Table class
	if (! class_exists('WP_List_Table') ) {
		 require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}
	require 'class-table-profiles.php';
	$CPTableProfiles = new CodeProfiler_Table_Profiles();

	// Profile(s) deletion
	if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete_profiles' &&
		! empty( $_REQUEST['profiles'] ) ) {
		// Verify security nonce
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-'. $CPTableProfiles->_args['plural'] ) === false ) {
			wp_nonce_ays('bulk-' . $CPTableProfiles->_args['plural'] );
		}

		$error = $CPTableProfiles->delete_profiles( $_REQUEST['profiles'] );
		if ( $error === true ) {
			printf(
				CODE_PROFILER_ERROR_NOTICE,
				esc_html__('Some errors occured when trying to delete the selected profiles.', 'code-profiler')
			);
		} else {
			printf(
				CODE_PROFILER_UPDATE_NOTICE,
				esc_html__('The selected profiles were deleted.', 'code-profiler')
			);
		}
	}

	?>
	<br />
	<form id="profile-form" method="post"<?php
		if (! empty( $_SERVER['QUERY_STRING'] ) ) {
			echo ' action="?'. esc_attr( $_SERVER['QUERY_STRING'] ) .'"';
		}
		?> onsubmit="return cpjs_search_query();">

	<?php
		// Search query
		if (! empty( $_REQUEST['s'] ) ) {
			echo '<span class="subtitle">';
			printf( esc_html__('Filter: %s'), '<code>' . esc_html( $_REQUEST['s'] ) . '</code>');
			echo '</span>';
		}
		$CPTableProfiles->prepare_items();
		$CPTableProfiles->search_box( esc_attr__('Filter', 'code-profiler'), 'search_id');
		$CPTableProfiles->display();
		?>
	</form>
	<!-- Help -->
	<div class="tablenav bottom">
		<div id="cp-footer-help" style="display:none">
			<?php
			 $type = 'profiles_list';
			 include 'help.php';
			 ?>
			<br />
		</div>
		<div class="alignleft actions bulkactions">
			<input type="button" class="button-primary" style="min-width:100px" value="<?php esc_attr_e('Help', 'code-profiler')?>" onclick="jQuery('#cp-footer-help').slideToggle(500);"/>
		</div>
	</div>
<?php
}
// =====================================================================
// EOF
