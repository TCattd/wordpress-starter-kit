<?php

namespace WPML\Media\Classes;

use WPML\FP\Str;

/**
 * Classic video parser
 */
class WPML_Media_Classic_Video_Parser extends WPML_Media_Classic_Element_Parser {

	const Media_Element_Expression = '/\[video ([^]]+)\]/s';
	const Media_Extension_Expression = '/\[video.+?(?=="http)/';

	/**
	 * Extracts the extension of the classic video media element, defaults to mp4.
	 *
	 * @return false|string
	 */
	protected function extractExtension() {
		$matches = $this->getExtensionMatches();

		return ! empty( $matches ) ? substr( $matches[0], - 3 ) : 'mp4';
	}

	protected function getMediaElementRegex() {
		return self::Media_Element_Expression;
	}

	protected function getMediaExtensionExpression() {
		return self::Media_Extension_Expression;
	}

	/**
	 * Checks if media element is classic video (video uploaded in classic editor).
	 *
	 * @return bool
	 */
	public function validate() {
		return Str::includes( '[video', $this->blockText );
	}
}
