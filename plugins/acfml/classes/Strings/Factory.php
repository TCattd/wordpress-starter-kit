<?php

namespace ACFML\Strings;

use ACFML\Strings\Transformer\Register;
use ACFML\Strings\Transformer\Translate;
use ACFML\Strings\Traversable\FieldGroup;
use ACFML\Strings\Traversable\Field;
use ACFML\Strings\Traversable\Layout;
use ACFML\Helper\FieldGroup as GroupHelper;

class Factory {

	/**
	 * @param array $fieldGroup
	 *
	 * @return FieldGroup
	 */
	public function createFieldGroup( $fieldGroup ) {
		return new FieldGroup( $fieldGroup );
	}

	/**
	 * @param array $field
	 *
	 * @return Field
	 */
	public function createField( $field ) {
		return new Field( $field );
	}

	/**
	 * @param array $layout
	 *
	 * @return Layout
	 */
	public function createLayout( $layout ) {
		return new Layout( $layout );
	}

	/**
	 * @param int $groupId
	 *
	 * @return Package
	 */
	public function createPackage( $groupId ) {
		return new Package( $groupId );
	}

	/**
	 * @param int $groupId
	 *
	 * @return Register
	 */
	public function createRegister( $groupId ) {
		return new Register(
			$this->createPackage( GroupHelper::getId( $groupId ) )
		);
	}

	/**
	 * @param int $groupId
	 *
	 * @return Translate
	 */
	public function createTranslate( $groupId ) {
		return new Translate(
			$this->createPackage( GroupHelper::getId( $groupId ) )
		);
	}

	/**
	 * @return TranslationJobFilter
	 */
	public function createTranslationJobFilter() {
		return new TranslationJobFilter( $this );
	}

	/**
	 * @param \stdClass|\WPML_Package|array|int $data
	 *
	 * @return \WPML_Package
	 */
	public static function createWpmlPackage( $data ) {
		return new \WPML_Package( $data );
	}
}
