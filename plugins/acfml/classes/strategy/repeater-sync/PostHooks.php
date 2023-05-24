<?php

namespace ACFML\Repeater\Sync;

use ACFML\FieldGroup\Mode;
use ACFML\Helper\FieldGroup;
use ACFML\Helper\Fields;
use ACFML\Repeater\Shuffle\Strategy;
use WPML\LIB\WP\Hooks;
use function WPML\FP\spreadArgs;

class PostHooks implements \IWPML_Backend_Action {

	/**
	 * @var Strategy
	 */
	private $shuffled;

	public function __construct( Strategy $shuffled ) {
		$this->shuffled = $shuffled;
	}

	/**
	 * @return void
	 */
	public function add_hooks() {
		Hooks::onAction( 'add_meta_boxes', 10, 2 )
			->then( spreadArgs( [ $this, 'displayCheckbox' ] ) );
	}

	/**
	 * @param string   $postType
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function displayCheckbox( $postType, $post ) {
		$postId = $post->ID;
		$fields = get_field_objects( $postId );

		if (
			$fields
			&& ( Fields::containsType( $fields, 'repeater' ) || Fields::containsType( $fields, 'flexible_content' ) )
			&& in_array( Mode::getForFieldableEntity( 'post' ), [ Mode::ADVANCED, Mode::MIXED ], true )
			&& $this->shuffled->isOriginal( $postId )
			&& $this->shuffled->hasTranslations( $postId )
		) {
			CheckboxUI::addMetaBox(
				$this->shuffled->getTrid( $postId ),
				get_post_type( $postId )
			);
		}

		$this->maybeResetFieldValuesWhenFieldGroupIsTranslated();
	}

	/**
	 * Resetting field values is far from optimal,
	 * but we cannot find a better solution for now.
	 *
	 * Also, this will be limited to sites with
	 * field groups set to be translated which
	 * is not recommended anymore.
	 *
	 * So it should impact always fewer users.
	 *
	 * @see https://onthegosystems.myjetbrains.com/youtrack/issue/acfml-746/
	 *
	 * @return void
	 */
	private function maybeResetFieldValuesWhenFieldGroupIsTranslated() {
		if ( FieldGroup::isTranslatable()) {
			acf_get_store( 'values' )->reset();
		}
	}
}
