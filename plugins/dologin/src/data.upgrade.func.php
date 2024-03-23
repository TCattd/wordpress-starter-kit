<?php
defined( 'WPINC' ) || exit;


function dologin_update_1_4_1() {
	global $wpdb;
	$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . "dologin_pswdless` ADD COLUMN `src` varchar(255) NOT NULL DEFAULT '' AFTER `hash`" );
}