<?php
if( !defined( 'ABSPATH' ) ) exit;

spl_autoload_register(function($class) {

	$namespace = 'LLAR\\';

	$len = strlen( $namespace );
	if (strncmp( $namespace, $class, $len) !== 0) {
		return;
	}

	$relative_class = str_replace('\\', '/', substr( $class, $len ) );
	$relative_class = explode( '/', $relative_class );
	$class_name = array_pop( $relative_class );
	$relative_class = implode( '/', $relative_class );
	$file = LLA_PLUGIN_DIR . strtolower( $relative_class ) . '/' . $class_name . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
});