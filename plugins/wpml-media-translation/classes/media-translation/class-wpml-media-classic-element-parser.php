<?php

namespace WPML\Media\Classes;

abstract class WPML_Media_Classic_Element_Parser extends WPML_Media_Element_Parser {

	/**
	 * Gets string out of the video element, this string should be ending with the video extension, then last 3 characters from string are returned.
	 *
	 * @return false|string
	 */
	abstract protected function extractExtension();

	/**
	 * Returns regular expression used to detect matches of the media element in a string.
	 *
	 * @return string
	 */
	abstract protected function getMediaElementRegex();

	/**
	 * Returns regular expression used to detect the extension of media element in a string.
	 *
	 * @return string
	 */
	abstract protected function getMediaExtensionExpression();

	public function getMediaElements() {
		return preg_match_all( $this->getMediaElementRegex(), $this->blockText, $matches )
			? $this->getAttachments( $matches ) : [];
	}

	/**
	 * Returns the source of the media element according to its extension in the attrs array (for example : mp3, mp4., ...).
	 *
	 * @param array $attrs
	 *
	 * @return string
	 */
	public function getMediaSrcFromAttributes( $attrs ) {
		$extension = $this->extractExtension();

		return ( $extension && isset( $attrs[ $extension ] ) ) ? $attrs[ $extension ] : '';
	}

	/**
	 * Applies regular expression match to get the media element extension and returns the matches.
	 *
	 * @return mixed
	 */
	protected function getExtensionMatches() {
		preg_match( $this->getMediaExtensionExpression(), $this->blockText, $matches );

		return $matches;
	}
}
