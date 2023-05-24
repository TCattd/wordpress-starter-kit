<?php
// If uninstall is not called from WordPress, exit
if (! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

$option_name = 'hfcm_db_version';
delete_option($option_name);

// Drop a custom db table
global $wpdb;
$table_name = $wpdb->prefix . 'hfcm_scripts';

$wpdb->query("DROP TABLE IF EXISTS $table_name");
