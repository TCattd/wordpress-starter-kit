<?php

	function piotnetforms_dynamic_tags( $output, $post_id = '' ) {
		if ( stripos( $output, '{{' ) !== false && stripos( $output, '}}' ) !== false ) {
			$pattern = '~\{\{\s*(.*?)\s*\}\}~';
			preg_match_all( $pattern, $output, $matches );
			$dynamic_tags = [];
			$post_id = !empty( $post_id ) ? $post_id : get_the_ID();

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

					if ( $tag['name'] == 'request_post' ) {
						if ( !empty( $tag['attr']['parameter'] ) ) {
							$tag_value = $_POST[ $tag['attr']['parameter'] ];
						}
					}

					if ( $tag['name'] == 'remote_ip' ) {
						if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
							$tag_value = $_SERVER['HTTP_CLIENT_IP'];
						} elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
							$tag_value = $_SERVER['HTTP_X_FORWARDED_FOR'];
						} else {
							$tag_value = $_SERVER['REMOTE_ADDR'];
						}
					}

					if ( $tag['name'] == 'user_info' ) {
						if ( is_user_logged_in() ) {
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

					if ( $tag['name'] == 'wc_product_short_description' ) {
						global $product;
						if ( !$product ) {
							$product = wc_get_product( $post_id );
						}
						if ( $product ) {
							$length = $tag['attr']['length'];
							if ( !empty( $length ) && is_numeric( $length ) && $length > 0 ) {
								$tag_value = wp_trim_words( $product->get_short_description(), $length, '...' );
							} else {
								$tag_value = $product->get_short_description();
							}
						}
					}

					if ( $tag['name'] == 'wc_product_price' ) {
						global $product;
						if ( !$product ) {
							$product = wc_get_product( $post_id );
						}
						if ( $product ) {
							//Full price
							if ( $tag['attr']['format'] == 'full' ) {
								$tag_value = $product->get_price();
							//Sale price
							} elseif ( $tag['attr']['format'] == 'sale' ) {
								if ( $product->is_type( 'simple' ) ) {
									if ( $product->is_on_sale() ) {
										$tag_value = wc_price( $product->get_sale_price() );
									} else {
										$tag_value = wc_price( $product->get_price() );
									}
								} elseif ( $product->is_type( 'variable' ) ) {
									if ( $product->is_on_sale() ) {
										$tag_value = wc_price( $product->get_variation_sale_price( 'min' ) ).' - '.wc_price( $product->get_variation_sale_price( 'max' ) );
									} else {
										$tag_value = wc_price( $product->get_variation_price( 'min' ) ).' - '.wc_price( $product->get_variation_price( 'max' ) );
									}
								} else {
									$tag_value = $product->get_price();
								}
							//Regular price
							} else {
								if ( $product->is_type( 'simple' ) ) {
									$tag_value = $product->get_regular_price();
								} elseif ( $product->is_type( 'variable' ) ) {
									$tag_value = wc_price( $product->get_variation_regular_price( 'min' ) ).' - '.wc_price( $product->get_variation_regular_price( 'max' ) );
								} else {
									$tag_value = $product->get_price();
								}
							}
						}

						if ( !empty( $tag_value ) ) {
							$tag_value = strip_tags( $tag_value );
						}
					}

					if ( $tag['name'] == 'wc_product_discount_percentage' ) {
						global $product;
						if ( !$product ) {
							$product = wc_get_product( $post_id );
						}
						if ( $product ) {
							if ( $product->is_type( 'variable' ) ) {
								$discount_percentages = [];

								// Get all variation prices
								$prices = $product->get_variation_prices();

								// Loop through variation prices
								foreach ( $prices['price'] as $key => $price ) {
									// Only on sale variations
									if ( $prices['regular_price'][$key] !== $price ) {
										// Calculate and set in the array the percentage for each variation on sale
										$discount_percentages[] = round( 100 - ( floatval( $prices['sale_price'][$key] ) / floatval( $prices['regular_price'][$key] ) * 100 ) );
									}
								}
								// We keep the highest value
								if ( count( $discount_percentages ) > 0 ) {
									$discount_percentage = max( $discount_percentages ) . '%';
								}
							} elseif ( $product->is_type( 'grouped' ) ) {
								$discount_percentages = [];

								// Get all variation prices
								$children_ids = $product->get_children();

								// Loop through variation prices
								foreach ( $children_ids as $child_id ) {
									$child_product = wc_get_product( $child_id );

									$regular_price = (float) $child_product->get_regular_price();
									$sale_price    = (float) $child_product->get_sale_price();

									if ( $sale_price != 0 || ! empty( $sale_price ) ) {
										// Calculate and set in the array the percentage for each child on sale
										$discount_percentages[] = round( 100 - ( $sale_price / $regular_price * 100 ) );
									}
								}
								// We keep the highest value
								if ( count( $discount_percentages ) > 0 ) {
									$discount_percentage = max( $discount_percentages ) . '%';
								}
							} else {
								$regular_price = (float) $product->get_regular_price();
								$sale_price    = (float) $product->get_sale_price();

								if ( $sale_price != 0 || ! empty( $sale_price ) ) {
									$discount_percentage = round( 100 - ( $sale_price / $regular_price * 100 ) ) . '%';
								}
							}
							if ( isset( $discount_percentage ) && $discount_percentage > 0 ) {
								$tag_value = $discount_percentage;
							}
						}
					}

					if ( $tag['name'] == 'wc_category_thumbnail' ) {
						global $term;
						if ( !empty( $term ) ) {
							$term_image_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
							if ( !empty( $term_image_id ) ) {
								$tag_value = wp_get_attachment_image_src( $term_image_id, 'full' )[0];
							}
						}
					}

					if ( $tag['name'] == 'wc_product_title' ) {
						$tag_value = get_the_title( $post_id );
					}

					if ( $tag['name'] == 'wc_product_rating' ) {
						global $product;
						if ( !$product ) {
							$product = wc_get_product( $post_id );
						}
						if ( $product ) {
							$rating_count = $product->get_rating_count();
							$average_rating  = $product->get_average_rating();
							if ( $rating_count >= 1 ) {
								$star_rating = wc_get_star_rating_html( $average_rating );
								$tag_value = strip_tags( $star_rating );
							}
						}
					}

					if ( $tag['name'] == 'author_info' ) {
						$tag_value = get_the_author_meta( $tag['attr']['meta'] );
					}

					if ( $tag['name'] == 'post_id' ) {
						$tag_value = $post_id;
					}

					if ( $tag['name'] == 'post_title' ) {
						if ( !empty( $tag['attr']['length'] ) && is_numeric( $tag['attr']['length'] ) && $tag['attr']['length'] > 0 ) {
							$tag_value = wp_trim_words( get_the_title( $post_id ), $tag['attr']['length'], '...' );
						} else {
							$tag_value = get_the_title( $post_id );
						}
					}

					if ( $tag['name'] == 'post_url' ) {
						$tag_value = get_permalink( $post_id );
					}

					if ( $tag['name'] == 'post_time' ) {
						if ( !empty( $tag['attr']['format'] ) ) {
							$tag_value = get_post_time( $tag['attr']['format'], true, $post_id, true );
						}
					}

					if ( $tag['name'] == 'post_modified_time' ) {
						if ( !empty( $tag['attr']['format'] ) ) {
							$tag_value = get_the_modified_date( $tag['attr']['format'], $post_id );
						}
					}

					if ( $tag['name'] == 'post_content' ) {
						if ( !empty( $tag['attr']['length'] ) && is_numeric( $tag['attr']['length'] ) && $tag['attr']['length'] > 0 ) {
							$tag_value = wp_trim_words( get_the_content( $post_id ), $tag['attr']['length'], '...' );
						} else {
							$tag_value = get_the_content( $post_id );
						}
					}

					if ( $tag['name'] == 'post_excerpt' && has_excerpt( $post_id ) ) {
						if ( !empty( $tag['attr']['length'] ) && is_numeric( $tag['attr']['length'] ) && $tag['attr']['length'] > 0 ) {
							$tag_value = wp_trim_words( get_the_excerpt( $post_id ), $tag['attr']['length'], '...' );
						} else {
							$tag_value = get_the_excerpt( $post_id );
						}
					}

					if ( $tag['name'] == 'post_comments_number' ) {
						$num_comments = get_comments_number( $post_id );
						if ( $num_comments >= 1 ) {
							$tag_value = $num_comments;
						}
					}

					if ( $tag['name'] == 'post_featured_image' ) {
						$post_featured_image_size = !empty( $tag['attr']['size'] ) ? $tag['attr']['size'] : 'full';
						$tag_value = get_the_post_thumbnail( $post_id, $post_featured_image_size );
					}


					if ( $tag['name'] == 'post_terms' ) {
						$terms = get_the_terms( $post_id, $tag['attr']['taxonomy'] );
						$post_terms = [];
						foreach ( $terms as $key => $val ) {
							if ( $tag['attr']['link'] =='true' ) {
								$term_link = get_term_link( $val->slug, $tag['attr']['taxonomy'] );
								$post_terms[$key] = '<a href="'. $term_link .'">' . $val->name . '</a>';
							} else {
								$post_terms[$key] = $val->name;
							}
						}
						$tag_value = implode( $tag['attr']['separator'], $post_terms );
					}

					if ( $tag['name'] == 'shortcode' ) {
						if ( !empty( $tag['attr']['shortcode'] ) ) {
							$tag_value = do_shortcode( $tag['attr']['shortcode'] );
						}
					}

					if ( $tag['name'] == 'post_custom_field' ) {
						$post_id = $post_id;
						if ( !empty( $tag['attr']['name'] ) ) {
							$tag_value = get_post_meta( $post_id, $tag['attr']['name'], true );
						}
					}

					if ( function_exists( 'get_field' ) && $tag['name'] == 'acf_field' ) {
						$post_id = $post_id;
						if ( !empty( $tag['attr']['name'] ) ) {
							$tag_value = get_field( $tag['attr']['name'], $post_id );
							if ( $tag['attr']['name'] == 'date' ) {
								$tag_value = get_post_meta( $post_id, $tag['attr']['name'], true );
								$time  = strtotime( $tag_value );
								$tag_value = date( get_option( 'date_format' ), $time );
							}
							if (is_array($tag_value)) {
	                            if ( !empty($tag['attr']['key'])) {
	                                $tag_value = $tag_value[$tag['attr']['key']];
	                            } else {
	                            	$tag_value = '';
	                            }
	                        }
						}
					}

					if ( function_exists( 'rwmb_get_value' ) && $tag['name'] == 'metabox_field' ) {
						$post_id = $post_id;
						if ( !empty( $tag['attr']['name'] ) ) {
							$tag_value = rwmb_get_value( $tag['attr']['name'], [], $post_id );
							if ( $tag['attr']['name'] == 'date' ) {
								$tag_value = get_post_meta( $post_id, $tag['attr']['name'], true );
								$time  = strtotime( $tag_value );
								$tag_value = date( get_option( 'date_format' ), $time );
							}
							if (is_array($tag_value)) {
	                            if ( !empty($tag['attr']['key'])) {
	                                $tag_value = $tag_value[$tag['attr']['key']];
	                            } else {
	                            	$tag_value = '';
	                            }
	                        }
						}
					}

					if ( function_exists( 'pods_field' ) && $tag['name'] == 'pods_field' ) {
						$post_id = $post_id;
						$sp_post_type = get_post_type();
						if ( !empty( $tag['attr']['name'] ) ) {
							$tag_value = pods_field( $sp_post_type, $post_id, $tag['attr']['name'], true );
							if (is_array($tag_value)) {
	                            if ( !empty($tag['attr']['key'])) {
	                                $tag_value = $tag_value[$tag['attr']['key']];
	                            } else {
	                            	$tag_value = '';
	                            }
	                        }
						}
					}

					if ( $tag['name'] == 'toolset_field' ) {
						$post_id = $post_id;
						if ( !empty( $tag['attr']['name'] ) ) {
							$meta_key = 'wpcf-' . $tag['attr']['name'];
							$tag_value = get_post_meta( $post_id, $meta_key, true );
							if (is_array($tag_value)) {
	                            if ( !empty($tag['attr']['key'])) {
	                                $tag_value = $tag_value[$tag['attr']['key']];
	                            } else {
	                            	$tag_value = '';
	                            }
	                        }
						}
					}

					if ( $tag['name'] == 'jetengine_field' ) {
						$post_id = $post_id;
						if ( !empty( $tag['attr']['name'] ) ) {
							$tag_value = get_post_meta( $post_id, $tag['attr']['name'], true );
							if (is_array($tag_value)) {
	                            if ( !empty($tag['attr']['key'])) {
	                                $tag_value = $tag_value[$tag['attr']['key']];
	                            } else {
	                            	$tag_value = '';
	                            }
	                        }
						}
					}

					if ( $tag['name'] == 'archive_title' ) {
						if ( is_post_type_archive() ) {
							$tag_value = post_type_archive_title( '', false );
						} elseif ( is_tax() ) {
							$tag_value = single_term_title( '', false );
						} elseif ( is_category() ) {
							$tag_value = single_cat_title( '', false );
						}
					}

					if ( $tag['name'] == 'archive_description' ) {
						if ( !empty( $tag['attr']['length'] ) && is_numeric( $tag['attr']['length'] ) && $tag['attr']['length'] > 0 ) {
							$tag_value = wp_trim_words( get_the_archive_description(), $tag['attr']['length'], '...' );
						} else {
							$tag_value = get_the_archive_description();
						}
					}

					if ( $tag['name'] == 'archive_meta' ) {
						$tag_value = get_term_meta( $tag['attr']['term_id'], $tag['attr']['meta_key'], true );
					}

					global $term;

					if ( !empty( $term ) ) {
						if ( $tag['name'] == 'term_id' ) {
							$tag_value = $term->term_id;
						}

						if ( $tag['name'] == 'term_name' ) {
							$tag_value = $term->name;
						}

						if ( $tag['name'] == 'term_description' ) {
							$tag_value = $term->description;
						}

						if ( $tag['name'] == 'term_url' ) {
							$tag_value = get_term_link( $term );
						}

						if ( $tag['name'] == 'term_count' ) {
							$tag_value = $term->count;
						}

						if ( $tag['name'] == 'term_color' ) {
							$tag_value = '#' . get_term_meta( $term->term_id, 'piotnetgrid_term_color', true );
						}

						if ( $tag['name'] == 'term_image' ) {
							$term_image_id = get_term_meta( $term->term_id, 'piotnetgrid_term_image', true );
							if ( !empty( $term_image_id ) ) {
								$tag_value = wp_get_attachment_image_src( $term_image_id, 'full' )[0];
							}
						}

						if ( $tag['name'] == 'term_meta' ) {
							$tag_value = get_term_meta( $term->term_id, $tag['attr']['meta_key'], true );
						}
					}

					$output = str_replace( $tag['dynamic_tag'], $tag_value, $output );
				}
			}
		}

		return $output;
	}
