<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

$option_name = 'ridwpa_version';

delete_option( $option_name );

delete_site_option( $option_name );
?>