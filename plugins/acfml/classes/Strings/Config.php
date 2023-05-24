<?php

namespace ACFML\Strings;

use WPML\FP\Fns;
use WPML\FP\Obj;
use WPML\FP\Relation;

class Config {

	const DATA = [
		[
			'namespace' => 'group',
			'key'       => 'title',
			'title'     => 'Field group title',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'group',
			'key'       => 'description',
			'title'     => 'Field group description',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'field',
			'key'       => 'label',
			'title'     => 'Field label',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'field',
			'key'       => 'instructions',
			'title'     => 'Field instructions',
			'type'      => 'AREA',
		],
		[
			'namespace' => 'field',
			'key'       => 'placeholder',
			'title'     => 'Field placeholder',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'field',
			'key'       => 'prepend',
			'title'     => 'Field prepend',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'field',
			'key'       => 'append',
			'title'     => 'Field append',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'field',
			'key'       => 'choices',
			'title'     => 'Field choices',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'field',
			'key'       => 'message',
			'title'     => 'Field message',
			'type'      => 'LINE',
		],
		[
			'namespace' => 'layout',
			'key'       => 'label',
			'title'     => 'Layout label',
			'type'      => 'LINE',
		],
	];

	/**
	 *
	 * @param string $namespace
	 * @param string $key
	 *
	 * @return array
	 */
	public static function get( $namespace, $key ) {
		return Obj::propOr( [], 0, Fns::filter( Relation::propEq( 'key', $key ), self::getFor( $namespace ) ) );
	}

	/**
	 * @param string $namespace
	 *
	 * @return array
	 */
	private static function getFor( $namespace ) {
		return Fns::filter( Relation::propEq( 'namespace', $namespace ), self::DATA );
	}

	/**
	 * @return array
	 */
	public static function getForGroup() {
		return self::getFor( 'group' );
	}

	/**
	 * @return array
	 */
	public static function getForField() {
		return self::getFor( 'field' );
	}

	/**
	 * @return array
	 */
	public static function getForLayout() {
		return self::getFor( 'layout' );
	}
}
