<?php

namespace ACFML\Upgrade;

use ACFML\Upgrade\Commands\MigrateToV2;

class CommandsProvider {

	/**
	 * @return \WPML\Collect\Support\Collection
	 */
	public static function get() {
		return wpml_collect( [
			MigrateToV2::class,
		] );
	}

	/**
	 * @return string
	 */
	public static function getHash() {
		return md5( self::get()->implode( ',' ) );
	}
}
