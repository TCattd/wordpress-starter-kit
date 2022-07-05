<?php

namespace WCML\Attributes;

use Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore as ProductAttributesLookupDataStore;
use WPML\FP\Obj;
use WPML\LIB\WP\Hooks;
use function WPML\FP\spreadArgs;
use WPML\FP\Fns;

class LookupTable implements \IWPML_Action {

	/** @var \SitePress $sitepress */
	private $sitepress;

	/**
	 * @param \SitePress $sitepress
	 */
	public function __construct( \SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public function add_hooks() {
		Hooks::onAction( 'save_post' )
			->then( spreadArgs( [ $this, 'triggerUpdateForTranslations' ] ) );

		// For defered updates, we adjust terms filter just before the action scheduler.
		Hooks::onAction( 'woocommerce_run_product_attribute_lookup_update_callback', 5 )
			->then( [ $this, 'adjustTermsFilters' ] );

		// When regenerating the table we need all products and all terms.
		Hooks::onFilter( 'woocommerce_attribute_lookup_regeneration_step_size' )
			->then( spreadArgs( Fns::tap( [ $this, 'regenerateTable' ] ) ) );
	}

	/**
	 * @param int $productId
	 */
	public function triggerUpdateForTranslations( $productId ) {
		if (
			'product' === get_post_type( $productId )
			&& 'publish' === get_post_status( $productId )
			&& ! $this->sitepress->is_original_content_filter( false, $productId, 'post_product' )
		) {
			Hooks::onAction( 'shutdown' )
				->then( function() use ( $productId ) {
					// For direct updates, we adjust terms filters just before triggering the update.
					$hasTermsClausesFilter = $this->adjustTermsFilters();

					wc_get_container()->get( ProductAttributesLookupDataStore::class )->on_product_changed( $productId );

					$this->restoreTermsFilters( $hasTermsClausesFilter );
				} );
		}
	}

	/**
	 * @return bool
	 */
	public function adjustTermsFilters() {
		add_filter( 'woocommerce_product_get_attributes', [ $this, 'translateAttributeOptions' ], 10, 2 );

		return remove_filter( 'terms_clauses', [ $this->sitepress, 'terms_clauses' ] );
	}

	/**
	 * @param bool $hasTermsClausesFilter
	 */
	private function restoreTermsFilters( $hasTermsClausesFilter ) {
		if ( $hasTermsClausesFilter ) {
			add_filter( 'terms_clauses', [ $this->sitepress, 'terms_clauses' ], 10, 3 );
		}

		remove_filter( 'woocommerce_product_get_attributes', [ $this, 'translateAttributeOptions' ] );
	}

	/**
	 * @param WC_Product_Attribute[] $attrs
	 * @param WC_Product             $product
	 *
	 * @return WC_Product_Attribute[]
	 */
	public function translateAttributeOptions( $attrs, $product ) {
		$language = $this->sitepress->get_language_for_element(
			$product->get_id(),
			'post_product'
		);

		if ( $language && is_array( $attrs ) ) {
			foreach ( $attrs as $taxonomy => $attr ) {
				$options = $attr->get_options();
				foreach ( $options as $index => $option ) {
					$options[ $index ] = $this->sitepress->get_object_id( $option, $taxonomy, false, $language );
				}
				$attr->set_options( $options );
			}
		}

		return $attrs;
	}

	public function regenerateTable() {
		$this->adjustTermsFilters();

		add_filter( 'woocommerce_product_object_query_args', Obj::assoc( 'suppress_filters', true ) );
	}

}
