<?php

namespace WPML\Media\Classes;

use WPML\FP\Obj;
use WPML\FP\Str;

/**
 * Media in href parser (basically for files included in classic editor)
 */
class WPML_Media_Href_Parser extends WPML_Media_Element_Parser {

	private static $allAnchorsRegex = '/<a.*?>.*?<\/a>/s'; // gets any anchor tag
	private static $hrefElementRegex = '/<a ([^>]+)/s'; // to get matches for anchors after filtering them (filtering means that we get only anchors without nested tags)

	public function getMediaElements() {
		return $this->getFromTags();
	}

	public function getMediaSrcFromAttributes( $attrs ) {
		return Obj::propOr('', 'href', $attrs);
	}

	protected function getFromTags() {
		$anchorsWithoutTags = $this->getAnchorsWithoutNestedTags();

		return preg_match_all( self::$hrefElementRegex, implode( '', $anchorsWithoutTags ), $matches ) ? $this->getAttachments( $matches ) : [];
	}

	/**
	 * Checks if media element is only anchor with href (basically for files uploaded in classic editor).
	 *
	 * @return bool
	 */
	public function validate() {
		return Str::includes( '<a href=', $this->blockText ) && ! empty( $this->getAnchorsWithoutNestedTags() );
	}

	/**
	 * Gets anchor tags from WP editor that contain neither nested tags not 'wp-block' string in it.
	 *
	 * @return array
	 */
	public function getAnchorsWithoutNestedTags() {
		$anchorHasNestedTags = function ( $anchorTag ) {
			$pattern = '/<a .*?>.*?<.*?<\/a>/s';

			preg_match( $pattern, $anchorTag, $matches );

			return ! empty( $matches );
		};

		$isBlockAnchor = Str::includes( 'wp-block' );

		preg_match_all( self::$allAnchorsRegex, $this->blockText, $allAnchorTags );

		return wpml_collect( current( $allAnchorTags ) )
			->reject( $anchorHasNestedTags )
			->reject( $isBlockAnchor )
			->toArray();
	}
}
