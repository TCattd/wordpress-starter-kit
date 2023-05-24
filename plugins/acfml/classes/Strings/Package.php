<?php

namespace ACFML\Strings;

use WPML\Element\API\Languages;
use WPML\FP\Fns;
use WPML\FP\Obj;

class Package {

	const KIND_SLUG = 'acf-field-group';

	const STATUS_ST_INACTIVE          = 'st_inactive';
	const STATUS_NOT_REGISTERED       = 'not_registered';
	const STATUS_NOT_TRANSLATED       = 'not_translated';
	const STATUS_PARTIALLY_TRANSLATED = 'partially_translated';
	const STATUS_FULLY_TRANSLATED     = 'fully_translated';

	/**
	 * @var string $fieldGroupId
	 */
	private $fieldGroupId;

	public function __construct( $fieldGroupId ) {
		$this->fieldGroupId = $fieldGroupId;
	}

	/**
	 * @return array
	 */
	private function getPackageData() {
		return [
			'kind'      => 'ACF Field Group',
			'kind_slug' => self::KIND_SLUG,
			'name'      => $this->fieldGroupId,
			'title'     => 'Field Group Labels ' . $this->fieldGroupId,
		];
	}

	/**
	 * @param string $value
	 * @param array  $stringData
	 *
	 * @return void
	 */
	public function register( $value, $stringData ) {
		if ( $value ) {
			do_action( 'wpml_register_string', $value, self::getStringName( $value, $stringData ), $this->getPackageData(), Obj::prop( 'title', $stringData ), Obj::prop( 'type', $stringData ) );
		}
	}

	/**
	 * @return void
	 */
	public function recordRegisteredStrings() {
		do_action( 'wpml_start_string_package_registration', $this->getPackageData() );
	}

	/**
	 * @return void
	 */
	public function cleanupUnusedStrings() {
		do_action( 'wpml_delete_unused_package_strings', $this->getPackageData() );
	}

	/**
	 * @param string $value
	 * @param array  $stringData
	 *
	 * @return string
	 *
	 * phpcs:disable WordPress.WP.I18n
	 */
	public function translate( $value, $stringData ) {
		if ( $value ) {
			return apply_filters( 'wpml_translate_string', $value, self::getStringName( $value, $stringData ), $this->getPackageData() );
		}

		return $value;
	}

	/**
	 * @return void
	 */
	public function delete() {
		$packageData = $this->getPackageData();
		do_action( 'wpml_delete_package', $packageData['name'], $packageData['kind'] );
	}

	/**
	 * @param string $value
	 * @param array  $meta
	 *
	 * @return string
	 */
	private static function getStringName( $value, $meta ) {
		return $meta['namespace'] . '-' . $meta['id'] . '-' . $meta['key'] . '-' . md5( $value );
	}

	/**
	 * @return \WPML_Package
	 */
	private function getWpmlPackage() {
		return Factory::createWpmlPackage( $this->getPackageData() );
	}

	/**
	 * @param string $languageCode
	 *
	 * @return array
	 */
	public function getUntranslatedStrings( $languageCode ) {
		$package = $this->getWpmlPackage();
		$strings = $package->get_package_strings();
		if ( ! $strings ) {
			return [];
		}

		$translated = $package->get_translated_strings( [] );

		$results = [];

		foreach ( $strings as $string ) {
			if ( ! isset( $translated[ $string->name ][ $languageCode ] ) ) {
				$results[ $string->name ] = $string;
			}
		}

		return $results;
	}

	/**
	 * @param array $translations
	 *
	 * @return void
	 */
	public function setStringTranslations( $translations ) {
		do_action( 'wpml_set_translated_strings', $translations, $this->getPackageData() );
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		// $getPackageStringsCount :: void -> int
		$getPackageStringsCount = Fns::memorize( function() {
			$strings = $this->getWpmlPackage()->get_package_strings();
			return is_array( $strings ) ? count( $strings ) : 0;
		} );

		// $getTranslatedStrings :: void -> array
		$getTranslatedStrings = Fns::memorize( function() {
			return $this->getWpmlPackage()->get_translated_strings( [] );
		} );

		// $isPartiallyTranslated :: void -> bool
		$isPartiallyTranslated = function() use ( $getTranslatedStrings ) {
			$translatedStrings   = $getTranslatedStrings();
			$secondaryLangsCount = count( Languages::getSecondaries() );

			foreach ( $translatedStrings as $translatedStringGroup ) {
				if ( count( $translatedStringGroup ) < $secondaryLangsCount ) {
					return true;
				}
			}

			return false;
		};

		if ( ! defined( 'WPML_ST_VERSION' ) ) {
			return self::STATUS_ST_INACTIVE;
		} elseif ( ! $getPackageStringsCount() ) {
			return self::STATUS_NOT_REGISTERED;
		} elseif ( ! $getTranslatedStrings() ) {
			return self::STATUS_NOT_TRANSLATED;
		} elseif ( $isPartiallyTranslated() ) {
			return self::STATUS_PARTIALLY_TRANSLATED;
		}

		return self::STATUS_FULLY_TRANSLATED;
	}

	/**
	 * @param string|int $fieldGroupId
	 *
	 * @return Package
	 */
	public static function create( $fieldGroupId ) {
		return new self( $fieldGroupId );
	}
}
