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
Plugin Name: Code Profiler (mu-plugin)
Plugin URI: https://code-profiler.com/
Description: Code Profiler's loader. Do not remove.
Version: 1.0
Author: Jerome Bruandet
Author URI: https://code-profiler.com/
License: GPLv3 or later
*/
define('CODE_PROFILER_MU_ON', true);
if ( isset( $_REQUEST['CODE_PROFILER_ON'] ) &&
	preg_match('/^\d{10}\.\d+$/', $_REQUEST['CODE_PROFILER_ON'] ) ) {

	include_once WP_PLUGIN_DIR .'/code-profiler/lib/helper.php';
	code_profiler_verify_key();
	code_profiler_disable_opcode();
	include_once WP_PLUGIN_DIR .'/code-profiler/lib/class-profiler.php';
}
// =====================================================================
// EOF
