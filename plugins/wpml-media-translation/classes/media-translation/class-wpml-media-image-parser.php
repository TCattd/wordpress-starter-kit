<?php

namespace WPML\Media\Classes;

use WPML\FP\Obj;
use WPML\FP\Str;

/**
 * Image block parser
 */
class WPML_Media_Image_Parser extends WPML_Media_Element_Parser {

	protected static $getFromCssBackgroundImagesRegex = '/<\w+[^>]+style\s?=\s?"[^"]*?background-image:url\(\s?([^\s\)]+)\s?\)/';
	protected static $mediaElementsRegex = [
		'/<img ([^>]+)>/s',
		'/<video ([^>]+)>/s',
		'/<audio ([^>]+)>/s',
	];

	public function getMediaElements() {
		$mediaElements = $this->getFromTags();

		$blocks = parse_blocks( $this->blockText );

		return $blocks ? array_merge( $mediaElements, $this->getFromCssBackgroundImagesInBlocks( $blocks ) )
			: array_merge( $mediaElements, $this->getFromCssBackgroundImages( $this->blockText ) );
	}

	public function getMediaSrcFromAttributes( $attrs ) {
		return Obj::propOr( '', 'src', $attrs );
	}

	protected function getFromTags() {
		$mediaElements = wpml_collect( [] );

		foreach ( self::$mediaElementsRegex as $mediaElementExpression ) {
			if ( preg_match_all( $mediaElementExpression, $this->blockText, $matches ) ) {
				$mediaElements = $mediaElements->merge( $this->getAttachments( $matches ) );
			}
		}

		return $mediaElements->toArray();
	}

	/**
	 * Checks if media element is Image Block and 'parse_blocks' function exists.
	 *
	 * @return bool
	 */
	public function validate() {
		return (
			Str::includes( '<!-- wp:image', $this->blockText )
			&& function_exists( 'parse_blocks' ) || Str::includes( '<img', $this->blockText )
		);
	}

	/**
	 * `parse_blocks` does not specify which kind of collection it should return
	 * (not always an array of `WP_Block_Parser_Block`) and the block parser can be filtered,
	 *  so we'll cast it to a standard object for now.
	 *
	 * @param mixed $block
	 *
	 * @return \stdClass|\WP_Block_Parser_Block
	 */
	protected function sanitizeBlock( $block ) {
		$block = (object) $block;

		if ( isset( $block->attrs ) ) {
			/** Sometimes `$block->attrs` is an object or an array, so we'll use an object */
			$block->attrs = (object) $block->attrs;
		}

		return $block;
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	protected function getFromCssBackgroundImages( $text ) {
		$images = [];

		if ( preg_match_all( self::$getFromCssBackgroundImagesRegex, $text, $matches ) ) {
			foreach ( $matches[1] as $src ) {
				$images[] = [
					'attributes' => [ 'src' => $src ]
				];
			}
		}

		return $images;
	}

	/**
	 * @param array $blocks
	 *
	 * @return array
	 */
	protected function getFromCssBackgroundImagesInBlocks( $blocks ) {
		$images = [];

		foreach ( $blocks as $block ) {
			$block = $this->sanitizeBlock( $block );

			if ( ! empty( $block->innerBlocks ) ) {
				$inner_images = $this->getFromCssBackgroundImagesInBlocks( $block->innerBlocks );
				$images       = array_merge( $images, $inner_images );
				continue;
			}

			if ( ! isset( $block->innerHTML, $block->attrs->id ) ) {
				continue;
			}

			$background_images = $this->getFromCssBackgroundImages( $block->innerHTML );
			$image             = reset( $background_images );

			if ( $image ) {
				$images[] = $image;
			}
		}

		return $images;
	}
}
