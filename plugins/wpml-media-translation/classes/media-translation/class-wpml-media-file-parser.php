<?php

namespace WPML\Media\Classes;

use WPML\FP\Obj;
use WPML\FP\Str;

/**
 * Media file block parser
 */
class WPML_Media_File_Parser extends WPML_Media_Element_Parser {

	private static $objectElementExpression = '/<object ([^>]+)>/s';

	public function getMediaElements() {
		return $this->getFromTags();
	}

	public function getMediaSrcFromAttributes( $attrs ) {
		return Obj::propOr( '', 'data', $attrs );
	}

	protected function getFromTags() {
		return preg_match_all( self::$objectElementExpression, $this->blockText, $matches ) ?
			$this->getAttachments( $matches ) : [];
	}

	/**
	 * Checks if media element is File Block and 'parse_blocks' function exists.
	 *
	 * @return bool
	 */
	public function validate() {
		return Str::includes( '<!-- wp:file', $this->blockText ) && function_exists( 'parse_blocks' );
	}
}
