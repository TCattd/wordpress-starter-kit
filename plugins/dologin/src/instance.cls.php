<?php
/**
 * The abstract instance
 *
 * @since      	1.0
 */
namespace dologin;
defined( 'WPINC' ) || exit;

abstract class Instance {
	private static $_instances;

	/**
	 * Load an instance or create it if not existed
	 * @since  3.0
	 */
	public static function cls( $cls = false ) {
		if ( ! $cls ) {
			$cls = self::ori_cls();
		}
		$cls = __NAMESPACE__ . '\\' . $cls;

		$cls_tag = strtolower( $cls );
		if ( ! isset( self::$_instances[ $cls_tag ] ) ) {
			self::$_instances[ $cls_tag ] = new $cls();
		}

		return self::$_instances[ $cls_tag ];
	}

	/**
	 * Get called class short name
	 */
	public static function ori_cls() {
		$cls = new \ReflectionClass( get_called_class() );
		$shortname = $cls->getShortName();
		$namespace = str_replace( __NAMESPACE__ . '\\', '', $cls->getNamespaceName() . '\\' );
		if ( $namespace ) { // the left namespace after dropped root namespace
			$shortname = $namespace . $shortname;
		}

		return $shortname;
	}
}