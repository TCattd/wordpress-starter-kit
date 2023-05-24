<?php

namespace ACFML\Strings;

use ACFML\Helper\Fields;
use ACFML\Strings\Transformer\Transformer;

class Translator {

	/**
	 * @var Factory $factory
	 */
	private $factory;

	/**
	 * @param Factory $factory
	 */
	public function __construct( Factory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * @param array $fieldGroup
	 *
	 * @return void
	 */
	public function registerGroupAndFieldsAndLayouts( $fieldGroup ) {
		$register = $this->factory->createRegister( $fieldGroup['ID'] );

		$register->start();

		$this->factory->createFieldGroup( $fieldGroup )->traverse( $register );

		Fields::iterate(
			acf_get_fields( $fieldGroup ),
			$this->getFieldTraverser( $register ),
			$this->getLayoutTraverser( $register )
		);

		$register->end();
	}

	/**
	 * @param array $fieldGroup
	 *
	 * @return array
	 */
	public function translateGroup( $fieldGroup ) {
		return $this->factory->createFieldGroup( $fieldGroup )->traverse( $this->factory->createTranslate( $fieldGroup['ID'] ) );
	}

	/**
	 * @param array $field
	 *
	 * @return array
	 */
	public function translateField( $field ) {
		$translate = $this->factory->createTranslate( $field['parent'] );

		$wrappedField = Fields::iterate(
			[ $field ],
			$this->getFieldTraverser( $translate ),
			$this->getLayoutTraverser( $translate )
		);

		return $wrappedField[0];
	}

	/**
	 * @param Transformer $transformer
	 *
	 * @return \Closure
	 */
	private function getFieldTraverser( $transformer ) {
		/**
		 * @param array $field
		 *
		 * @return array
		 */
		return function( $field ) use ( $transformer ) {
			return $this->factory->createField( $field )->traverse( $transformer );
		};
	}

	/**
	 * @param Transformer $transformer
	 *
	 * @return \Closure
	 */
	private function getLayoutTraverser( $transformer ) {
		/**
		 * @param array $layout
		 *
		 * @return array
		 */
		return function( $layout ) use ( $transformer ) {
			return $this->factory->createLayout( $layout )->traverse( $transformer );
		};
	}
}
