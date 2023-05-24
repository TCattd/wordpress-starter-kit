<?php

namespace ACFML\Repeater\Sync;

use ACFML\FieldGroup\Mode;
use ACFML\Helper\Fields;
use ACFML\Repeater\Shuffle\Strategy;
use WPML\FP\Obj;
use WPML\API\Sanitize;
use WPML\LIB\WP\Hooks;

class OptionPageHooks implements \IWPML_Backend_Action {

	const SCREEN_ID = 'acf_options_page';

	/**
	 * @var Strategy
	 */
	private $shuflled;

	public function __construct( Strategy $shuffled ) {
		$this->shuflled = $shuffled;
	}

	/**
	 * @return null|string
	 */
	private function getId() {
		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		$page = acf_get_options_page( Sanitize::stringProp( 'page', $_REQUEST ) );
		return is_array( $page ) ? Obj::prop( 'post_id', $page ) : null;
	}

	/**
	 * @return void
	 */
	public function add_hooks() {
		$id = $this->getId();
		if ( ! $id ) {
			return;
		}
		$fields = get_field_objects( $id );

		if ( $fields
			&& ( Fields::containsType( $fields, 'repeater' ) || Fields::containsType( $fields, 'flexible_content' ) )
			&& in_array( Mode::getForFieldableEntity( 'option' ), [ Mode::ADVANCED, Mode::MIXED ], true )
			&& $this->shuflled->isOriginal( $id )
		) {
			Hooks::onAction( 'admin_init' )
				->then( [ $this, 'displayCheckbox' ] );
		}
	}

	public function displayCheckbox() {
		CheckboxUI::addMetaBox(
			$this->shuflled->getTrid( $this->getId() ),
			self::SCREEN_ID
		);
	}
}
