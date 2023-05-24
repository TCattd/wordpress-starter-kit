<?php

namespace ACFML\Strings;

use ACFML\Helper\Fields;
use ACFML\Strings\Transformer\Transformer;
use WPML\FP\Fns;

class BaseHooks implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_DIC_Action {

	/**
	 * @var Factory $factory
	 */
	private $factory;

	/**
	 * @var Translator $translator
	 */
	private $translator;

	/**
	 * @param Factory    $factory
	 * @param Translator $translator
	 */
	public function __construct( Factory $factory, Translator $translator ) {
		$this->factory    = $factory;
		$this->translator = $translator;
	}

	/**
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'acf/update_field_group', [ $this, 'registerGroupAndFieldsAndLayouts' ] );

		add_filter( 'acf/load_field_group', Fns::withoutRecursion( Fns::identity(), [ $this, 'translateGroup' ] ) );
		add_filter( 'acf/load_field', [ $this, 'translateField' ] );

		add_action( 'acf/delete_field_group', [ $this, 'deleteFieldGroupPackage' ] );
	}

	/**
	 * @param array $fieldGroup
	 *
	 * @return void
	 */
	public function registerGroupAndFieldsAndLayouts( $fieldGroup ) {
		$this->translator->registerGroupAndFieldsAndLayouts( $fieldGroup );
	}

	/**
	 * @param array $fieldGroup
	 *
	 * @return array
	 */
	public function translateGroup( $fieldGroup ) {
		if ( self::isAcfFieldGroupScreen() ) {
			return $fieldGroup;
		}

		return $this->translator->translateGroup( $fieldGroup );
	}

	/**
	 * @param array $field
	 *
	 * @return array
	 */
	public function translateField( $field ) {
		if ( self::isAcfFieldGroupScreen() ) {
			return $field;
		}

		return $this->translator->translateField( $field );
	}

	/**
	 * @param array $fieldGroup
	 *
	 * @return void
	 */
	public function deleteFieldGroupPackage( $fieldGroup ) {
		$this->factory->createPackage( $fieldGroup['ID'] )->delete();
	}

	/**
	 * @return bool
	 */
	private static function isAcfFieldGroupScreen() {
		return ! function_exists( 'acf_is_screen' ) || acf_is_screen( 'acf-field-group' );
	}
}
