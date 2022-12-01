<?php

namespace WPML\Media\Factories;

use WPML\Media\Classes\WPML_Media_Classic_Audio_Parser;
use WPML\Media\Classes\WPML_Media_Classic_Video_Parser;
use WPML\Media\Classes\WPML_Media_File_Parser;
use WPML\Media\Classes\WPML_Media_Href_Parser;
use WPML\Media\Classes\WPML_Media_Image_Parser;

class WPML_Media_Element_Parser_Factory {


	private $availableMediaParsers = [
		'img-block'     => [ 'class-name' => WPML_Media_Image_Parser::class ],
		'audio-block'   => [ 'class-name' => WPML_Media_Image_Parser::class ],
		'video-block'   => [ 'class-name' => WPML_Media_Image_Parser::class ],
		'file-block'    => [ 'class-name' => WPML_Media_File_Parser::class ],
		'classic-audio' => [ 'class-name' => WPML_Media_Classic_Audio_Parser::class ],
		'classic-Video' => [ 'class-name' => WPML_Media_Classic_Video_Parser::class ],
		'href'          => [ 'class-name' => WPML_Media_Href_Parser::class ]
	];

	/**
	 * Returns array of media parsers according to post content.
	 *
	 * @param $postContent
	 *
	 * @return array
	 */
	public function create( $postContent ) {
		$parsers = [];

		foreach ( $this->availableMediaParsers as $mediaParser ) {
			$parserInstance = new $mediaParser['class-name']( $postContent );
			if ( $parserInstance->validate() ) {
				$parsers[] = $parserInstance;
			}
		}

		return $parsers;
	}
}
