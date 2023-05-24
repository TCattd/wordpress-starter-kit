<?php

namespace ACFML\Strings;

class HooksFactory implements \IWPML_Backend_Action_Loader, \IWPML_Frontend_Action_Loader {

	/**
	 * @return \IWPML_Action[]
	 */
	public function create() {
		$factory    = new Factory();
		$translator = new Translator( $factory );

		$hooks = [ new STPluginHooks( $translator ) ];

		if ( self::isStActivated() ) {
			$hooks[] = new BaseHooks( $factory, $translator );
			$hooks[] = new TranslationJobHooks( $factory );
		}

		return $hooks;
	}

	/**
	 * @return bool
	 */
	public static function isStActivated() {
		return defined( 'WPML_ST_VERSION' );
	}
}
