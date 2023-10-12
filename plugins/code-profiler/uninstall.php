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

if (! defined('WP_UNINSTALL_PLUGIN') ) {
	exit('Not allowed');
}

// =====================================================================
// Code Profiler's uninstaller (database + files).

$cp_options = get_option('code-profiler');

global $wp_filesystem;
require_once ABSPATH .'wp-admin/includes/file.php';
WP_Filesystem();

define('CODE_PROFILER_VERSION', '1');
require_once 'lib/helper.php';

if ( $wp_filesystem->exists( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN ) ) {
	$wp_filesystem->delete( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_MUPLUGIN, false, 'f');
}

// Delete options in the DB
delete_option('code-profiler');

// Delete profiles on disk
$wp_filesystem->delete( CODE_PROFILER_UPLOAD_DIR, true, 'd');

return;
// =====================================================================
// EOF
