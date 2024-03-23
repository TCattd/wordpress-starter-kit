<?php
/**
 * Auto registration
 *
 * @since      	1.0
 */
defined( 'WPINC' ) || exit;

if ( ! function_exists( 'dologin_autoload' ) ) {
	function dologin_autoload( $cls ) {
		if ( strpos( $cls, 'dologin' ) !== 0 ) {
			return;
		}

		$file = explode( '\\', $cls );
		array_shift( $file );
		$file = implode( '/', $file );
		$file = str_replace( '_', '-', strtolower( $file ) );

		if ( strpos( $file, 'lib/' ) === 0 || strpos( $file, 'thirdparty/' ) === 0 ) {
			$file = DOLOGIN_DIR . $file . '.cls.php';
		}
		else {
			$file = DOLOGIN_DIR . 'src/' . $file . '.cls.php';
		}

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

spl_autoload_register( 'dologin_autoload' );

