<?php

namespace ACFML\Notice;

class Links {

	const ACFML_MAIN_DOC = 'https://wpml.org/documentation/related-projects/translate-sites-built-with-acf/';

	/**
	 * @param string $link   Link.
	 * @param array  $params UTM parameters.
	 *
	 * @return string
	 */
	private static function generate( $link, $params = [] ) {
		$params = array_merge( [
			'utm_source'   => 'plugin',
			'utm_medium'   => 'gui',
			'utm_campaign' => 'acfml',
		], $params );

		return add_query_arg( $params, $link );
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function getAcfmlMainDoc( $params = [] ) {
		return self::generate( self::ACFML_MAIN_DOC, $params );
	}
}
