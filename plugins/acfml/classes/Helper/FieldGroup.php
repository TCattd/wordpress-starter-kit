<?php

namespace ACFML\Helper;

use WPML\API\Sanitize;
use WPML\FP\Obj;

class FieldGroup {

	const CPT         = 'acf-field-group';
	const SCREEN_SLUG = 'acf-field-group';

	/**
	 * @return bool
	 */
	public static function isScreen() {
		return acf_is_screen( self::SCREEN_SLUG );
	}

	/**
	 * @return bool
	 */
	public static function isListScreen() {
		global $pagenow;

		return 'edit.php' === $pagenow
			&& self::SCREEN_SLUG === Sanitize::stringProp( 'post_type', $_GET ); // phpcs:ignore
	}

	/**
	 * @param  int $id
	 *
	 * @return int|null
	 */
	public static function getId( $id ) {
		$group = acf_get_field_group( $id );

		if ( $group ) {
			return (int) Obj::prop( 'ID', $group );
		} else {
			$parentId = (int) Obj::prop( 'ID', get_post_parent( $id ) );

			if ( $parentId ) {
				return self::getId( $parentId );
			}
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public static function isTranslatable() {
		return is_post_type_translated( self::CPT );
	}
}
