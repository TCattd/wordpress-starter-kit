<?php
	function piotnetforms_dynamic_tags( $output ) {

		if ( stripos( $output, '{{' ) !== false && stripos( $output, '}}' ) !== false ) {
			$pattern = '~\{\{\s*(.*?)\s*\}\}~';
			preg_match_all( $pattern, $output, $matches );
			$dynamic_tags = [];

			if ( ! empty( $matches[1] ) ) {
				$matches = array_unique( $matches[1] );

				foreach ( $matches as $key => $match ) {
					if ( stripos( $match, '|' ) !== false ) {
						$match_attr = explode( '|', $match );
						$attr_array = [];
						foreach ( $match_attr as $key_attr => $value_attr ) {
							if ( $key_attr != 0 ) {
								$attr                           = explode( ':', $value_attr, 2 );
								$attr_array[ trim( $attr[0] ) ] = trim( $attr[1] );
							}
						}

						$dynamic_tags[] = [
							'dynamic_tag' => '{{' . $match . '}}',
							'name'        => trim( $match_attr[0] ),
							'attr'        => $attr_array,
						];
					} else {
						$dynamic_tags[] = [
							'dynamic_tag' => '{{' . $match . '}}',
							'name'        => trim( $match ),
						];
					}
				}
			}

			if ( ! empty( $dynamic_tags ) ) {
				foreach ( $dynamic_tags as $tag ) {
					$tag_value = '';

					if ( $tag['name'] == 'current_date_time' ) {
						if ( empty( $tag['attr']['date_format'] ) ) {
							$tag_value = date( 'Y-m-d H:i:s' );
						} else {
							$tag_value = date( $tag['attr']['date_format'] );
						}
					}

					if ( $tag['name'] == 'request' ) {
						if ( !empty( $tag['attr']['parameter'] ) ) {
							$tag_value = $_REQUEST[ $tag['attr']['parameter'] ];
						}
					}

					if ( $tag['name'] == 'user_info' ) {
						if (is_user_logged_in()) {
							if ( !empty( $tag['attr']['meta'] ) ) {
								$meta = $tag['attr']['meta'];
								$current_user = wp_get_current_user();

								switch ( $meta ) {
									case 'ID':
									case 'user_login':
									case 'user_nicename':
									case 'user_email':
									case 'user_url':
									case 'user_registered':
									case 'user_status':
									case 'display_name':
										$tag_value = $current_user->$meta;
										break;
									default:
										$tag_value = get_user_meta( get_current_user_id(), $tag['attr']['meta'], true );
								}
							}
						}
					}

					if ( $tag['name'] == 'post_id' ) {
						$tag_value = get_the_ID();
					}

					if ( $tag['name'] == 'post_title' ) {
						$tag_value = get_the_title();
					}

					if ( $tag['name'] == 'post_url' ) {
						$tag_value = get_permalink();
					}

					if ( $tag['name'] == 'post_url' ) {
						$tag_value = get_permalink();
					}

					if ( $tag['name'] == 'shortcode' ) {
						if ( !empty( $tag['attr']['shortcode'] ) ) {
							$tag_value = do_shortcode( $tag['attr']['shortcode'] );
						}
					}

					$output = str_replace( $tag['dynamic_tag'], $tag_value, $output );
				}
			}
		}

		return $output;
	}