<?php

namespace WPML\Media\Classes;

use WPML\FP\Str;

/**
 * Classic audio parser
 */
class WPML_Media_Classic_Audio_Parser extends WPML_Media_Classic_Element_Parser {

	const Media_Element_Expression = '/\[audio ([^]]+)\]/s';
	const Media_Extension_Expression = '/\[audio.+?(?=="http)/';

	/**
	 * Extracts the extension of the classic audio media element, defaults to mp3.
	 *
	 * @return false|string
	 */
	protected function extractExtension() {
		$matches = $this->getExtensionMatches();

		return ! empty( $matches ) ? substr( $matches[0], - 3 ) : 'mp3';
	}

	protected function getMediaElementRegex() {
		return self::Media_Element_Expression;
	}

	protected function getMediaExtensionExpression() {
		return self::Media_Extension_Expression;
	}

	/**
	 * Checks if media element is classic audio (audio uploaded in classic editor).
	 *
	 * @return bool
	 */
	public function validate() {
		return Str::includes( '[audio', $this->blockText );
	}
}
