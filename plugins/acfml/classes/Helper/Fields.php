<?php

namespace ACFML\Helper;

use WPML\FP\Relation;
use WPML\FP\Obj;

class Fields {

	/**
	 * @param array    $fields
	 * @param callable $transformField
	 * @param callable $transformLayout
	 * @param string   $fieldBasePattern
	 *
	 * @return array
	 */
	public static function iterate( $fields, $transformField, $transformLayout, $fieldBasePattern = '' ) {
		foreach ( $fields as &$field ) {
			$fieldPattern = $fieldBasePattern . $field['name'];
			$field        = $transformField( $field, $fieldPattern );

			if ( isset( $field['sub_fields'] ) ) {
				$fieldPatternSuffix  = 'group' === Obj::prop( 'type', $field ) ? '_' : '_\d+_';
				$field['sub_fields'] = self::iterate( $field['sub_fields'], $transformField, $transformLayout, $fieldPattern . $fieldPatternSuffix );
			}

			if ( isset( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as &$layout ) {
					$layout = $transformLayout( $layout );

					if ( isset( $layout['sub_fields'] ) ) {
						$layout['sub_fields'] = self::iterate( $layout['sub_fields'], $transformField, $transformLayout, $fieldPattern . '_\d+_' );
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * @param array  $fields Array of fields.
	 * @param string $type   Field type.
	 *
	 * @return bool
	 */
	public static function containsType( $fields, $type ) {
		$isType = Relation::propEq( 'type', $type );
		return (bool) wpml_collect( $fields )
			->first( $isType );
	}
}
