<?php

/**
 * Class WPML_Media_Translated_Images_Update
 * Translates images in a given text
 */
class WPML_Media_Translated_Images_Update {

	/**
	 * @var \WPML\Media\Factories\WPML_Media_Element_Parser_Factory
	 */
	private $mediaParserFactory;

	/**
	 * @var \WPML_Media_Image_Translate
	 */
	private $image_translator;
	/**
	 * @var \WPML_Media_Sizes
	 */
	private $media_sizes;

	/**
	 * WPML_Media_Translated_Images_Update constructor.
	 *
	 * @param WPML_Media_Image_Translate $image_translator
	 * @param WPML_Media_Sizes $media_sizes
	 */
	public function __construct( \WPML\Media\Factories\WPML_Media_Element_Parser_Factory $mediaParserFactory, WPML_Media_Image_Translate $image_translator, WPML_Media_Sizes $media_sizes ) {
		$this->mediaParserFactory = $mediaParserFactory;
		$this->image_translator   = $image_translator;
		$this->media_sizes        = $media_sizes;
	}

	/**
	 * @param string $text
	 * @param string $source_language
	 * @param string $target_language
	 *
	 * @return string
	 */
	public function replace_images_with_translations( $text, $target_language, $source_language = null ) {
		$mediaParsers = $this->mediaParserFactory->create( $text );

		// We have original and translated attachment IDs already saved in the $_POST variable
		// So I started using them instead of grabbing them again
		$attachment_id = isset( $_POST['original-attachment-id'] ) ? $_POST['original-attachment-id'] : null;
		$translated_id = isset( $_POST['translated-attachment-id'] ) ? $_POST['translated-attachment-id'] : null;

		$pre_update_translated_media_guid = isset( $_POST['pre-update-translated-media-guid'] ) ? $_POST['pre-update-translated-media-guid'] : '';


		/**
		 * Checks if media src in post content (not updated yet) is equal to old media that was uploaded but now its guid replaced in database.
		 *
		 * @param $mediaSrc
		 *
		 * @return bool
		 */
		$mediaSrcSameAsPreUpdate = function ( $mediaSrc ) use ( $pre_update_translated_media_guid ) {
			return $mediaSrc === $pre_update_translated_media_guid;
		};

		/**
		 * Checks if media src in post content (not updated yet) contains old media that was uploaded but now its guid replaced in database.
		 *
		 * @param $mediaSrc
		 *
		 * @return bool
		 */
		$mediaSrcContainsPreUpdate = function ( $mediaSrc ) use ( $pre_update_translated_media_guid ) {
			$thumb_file_name                   = basename( $pre_update_translated_media_guid );
			$pre_update_translated_media_parts = explode( '.', $thumb_file_name );

			return $pre_update_translated_media_parts[0] && false !== strpos( $mediaSrc, $pre_update_translated_media_parts[0] );
		};

		if ( ! empty( $mediaParsers ) ) {
			foreach ( $mediaParsers as $mediaParser ) {
				$mediaItems = $mediaParser->getMediaElements();

				foreach ( $mediaItems as $media ) {
					$mediaSrc          = $mediaParser->getMediaSrcFromAttributes( $media['attributes'] );
					$originalMediaGuid = isset( $attachment_id ) ? $this->getSizedOriginalMediaGuid( $attachment_id, $source_language ) : $mediaSrc;

					// This if condition checks that the value for media GUID saved in $_POST is same as media subject to get updated ..
					// OR if the media src is equal to original src (in case of translated post contains same already uploaded media) so media that exists will be replaced with the translated one
					if (
						( $mediaSrcSameAsPreUpdate( $mediaSrc ) || $mediaSrcContainsPreUpdate( $mediaSrc ) ) || ( $mediaSrc === $originalMediaGuid )
					) {

						if ( isset( $attachment_id ) && $attachment_id ) {
							$size           = $this->media_sizes->get_attachment_size( $media );
							$translated_src = $this->image_translator->get_translated_image( $attachment_id, $target_language, $size );
						} else {
							$translated_src = $this->get_translated_image_by_url( $mediaParser, $target_language, $source_language, $media );
						}

						if ( $translated_src ) {
							if ( $translated_src !== $mediaSrc ) {
								$text = $this->replace_image_src( $text, $mediaSrc, $translated_src );
							}

							// to replace value in href if it couldn't be replaced in replace_image_src
							$text = $this->replaceAttributeInHref( $text, $mediaSrc, $translated_src, $source_language );
						}
						if ( $attachment_id && $attachment_id !== $translated_id ) {
							$text = $this->replace_att_class( $text, $attachment_id, $translated_id );
							$text = $this->replace_att_in_block( $text, $attachment_id, $translated_id );
						}
					} else { // to handle reverting media to original
						if ( empty( $pre_update_translated_media_guid ) && $this->mediaSrcContainsMediaFileName( $translated_id, $mediaSrc ) ) {
							$text = $this->replace_image_src( $text, $mediaSrc, $originalMediaGuid );
							$text = $this->replace_att_class( $text, $translated_id, $attachment_id );
							$text = $this->replace_att_in_block( $text, $translated_id, $attachment_id );
						}
					}
				}
			}
		}

		return $text;
	}

	private function getMediaGuid( $id ) {
		return get_post_field( 'guid', $id );
	}

	private function mediaSrcContainsMediaFileName( $attachmentId, $mediaSrc ) {
		$guid           = $this->getMediaGuid( $attachmentId );
		$mediaExtension = substr( $guid, - 4 );
		$mediaFilename  = explode( $mediaExtension, basename( $guid ) )[0];

		return \WPML\FP\Str::includes( $mediaFilename, $mediaSrc );
	}

	private function getSizedOriginalMediaGuid( $attachmentId, $sourceLang ) {
		$originalMediaGuid = $this->getMediaGuid( $attachmentId );
		$originalMediaSize = $this->media_sizes->get_image_size_from_url( $originalMediaGuid, $attachmentId );

		return $this->image_translator->get_translated_image( $attachmentId, $sourceLang, $originalMediaSize );
	}

	/**
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	private function replace_image_src( $text, $from, $to ) {
		return str_replace( $from, $to, $text );
	}

	/**
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	private function replace_att_class( $text, $from, $to ) {
		$pattern     = '/\bwp-image-' . $from . '\b/u';
		$replacement = 'wp-image-' . $to;

		return preg_replace( $pattern, $replacement, $text );
	}

	/**
	 * @param string $text
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	private function replace_att_in_block( $text, $from, $to ) {
		$pattern = '';

		$blocks = [ 'wp:image', 'wp:file', 'wp:audio', 'wp:video' ];
		foreach ( $blocks as $block ) {
			if ( \WPML\FP\Str::startsWith( '<!-- ' . $block, $text ) ) {
				$pattern = '/<!-- ' . $block . ' ' . '{.*?"id":(' . $from . '),.*?-->/u';
			}
		}

		$replacement = function ( $matches ) use ( $to ) {
			return str_replace( '"id":' . $matches[1], '"id":' . $to, $matches[0] );
		};

		return (bool) strlen( $pattern ) ? preg_replace_callback( $pattern, $replacement, $text ) : $text;
	}

	/**
	 * Replaces value in href for classic images and files added in classic editor
	 *
	 * @param $text
	 * @param $to
	 *
	 * @return array|string|string[]|null
	 */
	private function replaceAttributeInHref( $text, $from, $to, $sourceLang ) {
		$pattern = '/<a.*?href="(.*?)".*?>/u';

		$attachId = $this->image_translator->get_attachment_id_by_url( $from, $sourceLang );
		$from     = get_post_field( 'guid', $attachId );

		$replacement = function ( $matches ) use ( $from, $to ) {
			return str_replace( 'href="' . $from . '"', 'href="' . $to . '"', $matches[0] );
		};

		return preg_replace_callback( $pattern, $replacement, $text );
	}

	/**
	 * @param $mediaParser
	 * @param $target_language
	 * @param $source_language
	 * @param $img
	 *
	 * @return bool|string
	 */
	private function get_translated_image_by_url( $mediaParser, $target_language, $source_language, $img ) {
		if ( null === $source_language ) {
			$source_language = wpml_get_current_language();
		}
		$translated_src = $this->image_translator->get_translated_image_by_url(
			$mediaParser->getMediaSrcFromAttributes( $img['attributes'] ),
			$source_language,
			$target_language
		);

		return $translated_src;
	}

}
