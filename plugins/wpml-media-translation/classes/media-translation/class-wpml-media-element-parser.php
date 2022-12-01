<?php

namespace WPML\Media\Classes;

use WPML\LIB\WP\Attachment;

abstract class WPML_Media_Element_Parser {

	private static $getAttachmentsRegex = '/(\S+)\\s*=\\s*["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/';

	protected $blockText;

	public function __construct( $blockText ) {
		$this->blockText = $blockText;
	}

	abstract public function getMediaElements();

	abstract public function getMediaSrcFromAttributes( $attrs );

	abstract public function validate();

	protected function getAttachments( $matches ) {
		$attachments = [];

		foreach ( $matches[1] as $i => $match ) {
			if ( preg_match_all( self::$getAttachmentsRegex, $match, $attribute_matches ) ) {
				$attributes = [];
				foreach ( $attribute_matches[1] as $k => $key ) {
					$attributes[ $key ] = $attribute_matches[2][ $k ];
				}

				$attachments[ $i ]['attributes'] = $attributes;
			}
		}

		return $attachments;
	}
}
