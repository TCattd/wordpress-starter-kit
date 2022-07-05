<?php
/**
 * Hookup for the custom actions related to the one map feature.
 *
 * @since 3.0
 * @package profile-builder
 */

add_action( 'wp_ajax_wppb_request_users_pins', 'wppb_request_users_pins_action_callback' );
add_action( 'wp_ajax_nopriv_wppb_request_users_pins', 'wppb_request_users_pins_action_callback' );

if ( ! function_exists( 'wppb_userlisting_one_map_configure' ) ) {
	/**
	 * Client-side configuration attributes.
	 *
	 * @return array
	 */
	function wppb_userlisting_one_map_configure() {
		$api_key  = wppb_options_get_map_api_key();
		$settings = wppb_options_get_map_settings();
		$map_lat  = ( ! empty( $settings['map-default-lat'] ) ) ? $settings['map-default-lat'] : '';
		$map_lng  = ( ! empty( $settings['map-default-lng'] ) ) ? $settings['map-default-lng'] : '';
		$map_zoom = ( ! empty( $settings['map-default-zoom'] ) ) ? $settings['map-default-zoom'] : '';

		return array(
			'apiKey'    => $api_key,
			'mapZoom'   => ( ! empty( $map_zoom ) ) ? (int) $map_zoom : WPPB_DEFAULTS_MAP_ZOOM,
			'centerLat' => ( empty( $map_lat ) ) ? WPPB_DEFAULTS_MAP_LAT : (float) $map_lat,
			'centerLng' => ( empty( $map_lng ) ) ? WPPB_DEFAULTS_MAP_LNG : (float) $map_lng,
			'actionUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
		);
	}
}

if ( ! function_exists( 'wppb_userlisting_one_map_scripts' ) ) {
	/**
	 * Enqueue the one map scripts.
	 *
	 * @return void
	 */
	function wppb_userlisting_one_map_scripts() {
		$api_key  = wppb_options_get_map_api_key();

		if ( ! empty( $api_key ) ) {
			// Attempt to enqueue the Google Maps script.
			wp_enqueue_script(
				'wppb-google-maps-api-script',
				'https://maps.googleapis.com/maps/api/js?key=' . $api_key,
				array( 'jquery' ),
				PROFILE_BUILDER_VERSION,
				true
			);

			/*
			// The piece below is not used currently, as the filtering is not handling the enqueue and dequeue properly.
			// See wppb_agregate_map_assets().

			wp_dequeue_script( 'wppb-one-map-listing' );

			// Register the custom one map listing script.
			wp_register_script(
				'wppb-one-map-listing',
				WPPB_PLUGIN_URL . 'front-end/extra-fields/map/one-map-listing.js',
				array( 'jquery', 'wppb-google-maps-api-script' ),
				PROFILE_BUILDER_VERSION . time(),
				false
			);

			// Localize the custom one map listing script.
			wp_localize_script(
				'wppb-one-map-listing',
				'oneMapListing',
				wppb_userlisting_one_map_configure()
			);

			// Enqueue the custom one map listing script.
			wp_enqueue_script( 'wppb-one-map-listing' );
			*/
		}
	}
}

if ( ! function_exists( 'wppb_before_field_customtype_checkbox' ) ) {
	/**
	 * Returns a custom markup to be exposed before the element.
	 *
	 * @param  string $html          The markup to be exposed before the element.
	 * @param  string $value         Field value.
	 * @param  array  $details       The field details.
	 * @param  string $single_prefix Meta field prefix.
	 * @return array
	 */
	function wppb_before_field_customtype_checkbox( $html, $value, $details, $single_prefix ) {
		if ( ! empty( $details['extra_attributes']['sortable_options'] ) ) {
			$html .= '<div class="wppb_sortable_checkboxes_wrap">';
		}

		return $html;
	}
}
add_filter( 'wck_output_form_field_before_customtype_checkbox', 'wppb_before_field_customtype_checkbox', 10, 4 );

if ( ! function_exists( 'wppb_after_field_customtype_checkbox' ) ) {
	/**
	 * Returns a custom markup to be exposed after the element.
	 *
	 * @param  string $html          The markup to be exposed after the element.
	 * @param  string $value         Field value.
	 * @param  array  $details       The field details.
	 * @param  string $single_prefix Meta field prefix.
	 * @return array
	 */
	function wppb_after_field_customtype_checkbox( $html, $value, $details, $single_prefix ) {
		if ( ! empty( $details['extra_attributes']['sortable_options'] ) ) {
			$html .= '</div>';
		}

		return $html;
	}
}
add_filter( 'wck_output_form_field_after_customtype_checkbox', 'wppb_after_field_customtype_checkbox', 10, 4 );

if ( ! function_exists( 'wppb_make_sortable_attributes' ) ) {
	/**
	 * Alter the options to be visible in the desired order, in a very simplified script.
	 *
	 * @param  array  $details       Field details.
	 * @param  string $slug          Field slug.
	 * @param  string $value         Meta value.
	 * @param  string $single_prefix Meta field prefix.
	 * @return array
	 */
	function wppb_make_sortable_attributes( $details, $slug, $value, $single_prefix ) {
		if ( ! empty( $details['extra_attributes']['sortable_options'] ) ) {
			$options = $details['options'];
			$sel = array();
			$all = $options;
			if ( ! empty( $value ) ) {
				$val = explode( ',', str_replace( ' ', '', $value ) );
				foreach ( $val as $key ) {
					foreach ( $options as $i => $option ) {
						if ( substr_count( $option, '%' . $key ) ) {
							$sel[] = $option;
							unset( $all[ $i ] );
							break;
						}
					}
				}
			}

			if ( ! empty( $details['extra_attributes']['dropdown_options'] ) ) {
				// Split the options, the dropdown is using the selectable options.
				$details['options'] = $sel;
				$details['selectable_options'] = $all;
			} else {
				// Merge the options, put the selected on top for clarity and usability.
				$details['options'] = array_merge( $sel, $all );
			}
		}

		return $details;
	}
}
add_filter( 'wck_output_form_field_options_by_slug', 'wppb_make_sortable_attributes', 10, 4 );

if ( ! function_exists( 'wppb_make_sortable_attributes_dropdown' ) ) {
	/**
	 * For selectable sortable checkboxes this will append the drowpdown of unused option
	 * from which can be created new sortable attributes when an option is selected.
	 *
	 * @param  string $element       The element output to be adjusted.
	 * @param  string $value         Field value.
	 * @param  array  $details       The field details.
	 * @param  string $single_prefix Meta field prefix.
	 * @return array
	 */
	function wppb_make_sortable_attributes_dropdown( $element, $value, $details, $single_prefix ) {
		if ( ! empty( $details['selectable_options'] ) ) {
			$element .= '
			<select name="selector-' . esc_attr( $details['slug'] ) . '"
				id="selector-' . esc_attr( $details['slug'] ) . '"
				class="wppb_selector_for_sortable_checkbox"
				data-list="' . esc_attr( $details['slug'] ) . '">
				<option value="">' . esc_attr( 'Select', 'profile-builder' ) . '</option>
			';
			foreach ( $details['selectable_options'] as $item ) {
				$parts = explode( '%', $item );
				$title = ( ! empty( $parts[1] ) ) ? trim( $parts[1] ) : '';
				$value = ( ! empty( $parts[2] ) ) ? trim( $parts[2] ) : '';
				$element .= '<option value="' . esc_attr( $value ) . '">' . esc_attr( $title ) . '</option>';
			}
			$element .= '</select>';
		}

		return $element;
	}
}
add_filter( 'wck_output_form_field_customtype_checkbox', 'wppb_make_sortable_attributes_dropdown', 10, 4 );

if ( ! function_exists( 'wppb_request_users_pins_action_callback' ) ) {
	/**
	 * AJAX handler for computing a filter pins batches.
	 *
	 * @return void
	 */
	function wppb_request_users_pins_action_callback() {
		$form_id = filter_input( INPUT_POST, 'formid', FILTER_VALIDATE_INT );
		$page    = filter_input( INPUT_POST, 'page', FILTER_VALIDATE_INT );
		$args    = filter_input( INPUT_POST, 'args', FILTER_DEFAULT );
		$args    = maybe_unserialize( $args );
		$total_p = filter_input( INPUT_POST, 'totalpages', FILTER_VALIDATE_INT );
		$ititems = filter_input( INPUT_POST, 'ititems', FILTER_VALIDATE_INT );
		$result  = wppb_get_users_pins( $form_id, $page, $args, $ititems );

		if ( ! empty( $result['pins'] ) || $page <= $total_p ) {
			// This indicates to the client-side script to trigger a new iteration.
			$result['continue'] = true;
		} else {
			// This indicates to the client-side script that all pins were fetched.
			$result['continue'] = false;
		}

		// Output the response as a JSON object.
		wp_send_json( $result );

		// Nothing to render further.
		wp_die();
	}
}

if ( ! function_exists( 'wppb_users_listing_current_query_arguments_fct' ) ) {
	/**
	 * Collect the users query arguments for the specified form.
	 *
	 * @param  integer $form_id Form id.
	 * @param  object  $args    Users search query resource.
	 * @return void
	 */
	function wppb_users_listing_current_query_arguments_fct( $form_id, $args ) {
		global $wppb_current_users_listing_arguments;
		if ( ! isset( $wppb_current_users_listing_arguments[ $form_id ] ) ) {
			$wppb_current_users_listing_arguments[ $form_id ] = array();
		}
		$wppb_current_users_listing_arguments[ $form_id ] = $args;
	}
}
add_action( 'wppb_users_listing_current_query_arguments', 'wppb_users_listing_current_query_arguments_fct', 10, 2 );

if ( ! function_exists( 'wppb_options_get_map_pin_meta' ) ) {
	/**
	 * Get map pin user meta.
	 *
	 * @return string
	 */
	function wppb_options_get_map_pin_meta() {
		$manage_fields = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields', 'not_set' ), array(
			'context' => 'map_api_key',
		) );
		$k = wp_list_pluck( $manage_fields, 'meta-name', 'field' );
		if ( ! empty( $k['Map'] ) ) {
			// This is the meta we are interested in.
			$pin_meta = $k['Map'];
            return $pin_meta;
		}

		return false;
	}
}

if ( ! function_exists( 'wppb_options_get_map_api_key' ) ) {
	/**
	 * Get map API key. This seems redundant, but it's not, the legacy function
	 * is not included in all the cases.
	 *
	 * @return string
	 */
	function wppb_options_get_map_api_key() {
		$api_key       = '';
		$manage_fields = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields', 'not_set' ), array(
			'context' => 'map_api_key',
		) );
		if ( 'not_set' != $manage_fields ) {
			foreach ( $manage_fields as $field ) {
				if ( ! empty( $field['map-api-key'] ) ) {
					$api_key = $field['map-api-key'];
					break;
				}
			}
		}

		return $api_key;
	}
}





if ( ! function_exists( 'wppb_get_users_pins' ) ) {
	/**
	 * Get paginated and filtered users pins.
	 *
	 * @param  integer $form_id   Form id.
	 * @param  integer $page      Page number.
	 * @param  array   $args      Users query arguments.
	 * @param  integer $per_page  Number of users per iteration.
	 * @param  boolean $use_paged True to use the arguments paged value.
	 * @return array
	 */
	function wppb_get_users_pins( $form_id, $page, $args = array(), $per_page = 10, $use_paged = false ) {
		$return = array(
			'pins'        => array(),
			'page'        => $page,
			'total_pages' => 1,
		);

		if ( empty( $args ) ) {
			global $wppb_current_users_listing_arguments;
			$args = $wppb_current_users_listing_arguments[ $form_id ];
		}
		if ( empty( $form_id ) || empty( $page ) || empty( $args ) ) {
			// Fail-fast, no need to continue. These are not the druids you are looking for.
			return $return;
		}

		// If we got this far, let's compute the pins by limiting the result to a specific range.
		if ( true === $use_paged ) {
			$page           = $args['paged'];
			$args['number'] = $per_page;
		} else {
			$args['paged'] = abs( (int) $page );
		}
		$args['number']      = $per_page;
		$args['count_total'] = true;

		$result = new WP_User_Query( $args );
		$rows   = $result->get_results();
		$total  = $result->get_total();

		// This tells us the maximum iterations for loading the pins for all the users.
		$return['total_pages'] = ceil( $total / $per_page );

		if ( ! empty( $rows ) ) {
			// Get one time the settings.
			$settings     = wppb_options_get_map_settings();

			// Compute one time the bubble template.
			$pin_template = ( ! empty( $settings['map-bubble-fields'] ) ) ? trim( str_replace( ', ', ',', $settings['map-bubble-fields'] ) ) . ',' : '';
			$pin_template = preg_replace( '#\w+(?=[,])#', '<p class="marker-info-$0">{{{$0}}}</p>', $pin_template );
			$pin_template = str_replace( ',', ' ', $pin_template );

			// Get the pin meta.
			$pin_meta = wppb_options_get_map_pin_meta();

			// Set the pins folder (if using custom pins). I used this to show the iterations with different colors.
			$pin_path = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/images/pins/';

			foreach ( $rows as $row ) {
				// Iterate through the users IDs.
				$user_info = get_userdata( $row->ID );

				// Identify the user's pins.
				$u_pins = wppb_get_user_map_markers( $row->ID, $pin_meta );
				if ( ! empty( $u_pins ) ) {
					// Compute only one time the bubble content for this user.
					$pin_info = apply_filters( 'wppb_single_userlisting_template',
						(string) new PB_Mustache_Generate_Template(
							wppb_generate_mustache_array_for_single_user_list(),
							$pin_template,
							array(
								'userlisting_form_id' => $form_id,
								'user_id'             => $row->ID,
								'single'              => true,
							)
						), $row->ID
					);

					$pin_info = apply_filters( 'wppb_filter_map_user_pin_bubble_contents', $pin_info, $row->ID );

					// The user has at least one pin.
					foreach ( $u_pins as $coord ) {
						$loc  = explode( ',', $coord );
						$lat  = ( ! empty( $loc[0] ) ) ? (float) $loc[0] : 0;
						$lng  = ( ! empty( $loc[1] ) ) ? (float) $loc[1] : 0;
						$icon = ''; // Set it to something like this for customizing: $pin_path . 'pin' . $page % 10 . '.png'.

						// Also compute the marker bubble content.
						$bubble = wppb_get_user_pin_bubble( $row->ID, $lat, $lng, $pin_info, $icon );

						// Push the pin to the list.
						$return['pins'][] = array(
							'lat'        => $lat,
							'lng'        => $lng,
							'icon'       => $icon,
							'pin_markup' => $bubble,
						);
					}
				}
			}
		}

		return $return;
	}
}


if ( ! function_exists( 'wppb_agregate_map_assets' ) ) {
	/**
	 * Returns the custom Google Maps main script, styles and settings. This is not standard,
	 * but it is currently the only way to make this work for all cases, as the inline
	 * filtering is not handled with standard AJAX, hence the enqueue and dequeue of scripts is not working
	 * properly, and the content of the filtering has to trigger the map reinitialization.
	 *
	 * @param  string  $api_key    The Google Api Key.
	 * @param  float   $map_lat    Center latitude.
	 * @param  float   $map_lng    Center longitude.
	 * @param  integer $map_zoom   Map initial zoom.
	 * @param  integer $map_height Map height.
	 * @return string
	 */
	function wppb_agregate_map_assets( $api_key, $map_lat = 0, $map_lng = 0, $map_zoom = 16, $map_height = 460 ) {
		if ( ! empty( $api_key ) ) {
			$conf = wppb_userlisting_one_map_configure();

			//check if jquery has been loaded yet because we need it at this point
			// we're checking if it's not admin because it brakes elementor otherwise.
			if( !wp_script_is('jquery', 'done') && !is_admin() ){
				wp_print_scripts('jquery');
			}

			ob_start();
			?>
			<style type="text/css">
			.wppb-acf-map-all { width: 100%; height: <?php echo (int) $map_height; ?>px; border: #ccc solid 1px; margin: 0px 0;}
			.wppb-acf-map-all img { max-width: inherit !important; /* fixes potential theme css conflict */}
			.wppb-acf-map-all-load-more {display: none;}
			.wppb-acf-map-all .marker {position: absolute; top: -400px; left: -400px;}
			.wppb-acf-map-all .marker-content {min-width: 320px; max-width: 100%;}
			.wppb-acf-map-all .marker-content .marker-info-avatar_or_gravatar {float: left; margin-right: 10px;}
			.map-pins-loading {position: relative;}
			.map-pins-loading:after {position: absolute; content:' '; width: 100%; height: 32px; top: -32px; left: 0; display: block; z-index: 100; text-align: center; line-height: 32px; font-size: 11px color: #000; content:'<?php esc_attr_e( 'Please wait while the pins are loading...', 'profile-builder' ); ?>';}
			.map-pins-loading:before {position: absolute; content:' '; width: 100%; height: 100%; top: 0; left: 0; display: block; z-index: 100; background: url('<?php echo esc_url( admin_url('/images/loading.gif') )?>') no-repeat 50% 50% rgba(255,255,255,0.4);}
			</style>
			<script>/* <![CDATA[ */
			var oneMapListing = <?php echo json_encode( $conf ); ?>;
			/* ]]> */</script>
			<script src="<?php echo esc_url( WPPB_PAID_PLUGIN_URL . 'front-end/extra-fields/map/one-map-listing.js?v=' . PROFILE_BUILDER_VERSION ); ?>"></script>
			<?php
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'wppb_userlisting_users_one_map' ) ) {
	/**
	 * Function that returns map with all users for the current filters.
	 *
	 * @param string $value      Undefined value.
	 * @param string $name       The name of the field.
	 * @param array  $children   An array containing all other fields.
	 * @param array  $extra_info Various extra information about the user.
	 *
	 * @return string
	 */
	function wppb_userlisting_users_one_map( $value, $name, $children, $extra_info ) {
		global $wppb_current_users_listing_arguments;
		$frid = (int) $extra_info['userlisting_form_id'];
		$args = ( ! empty( $wppb_current_users_listing_ids[ $frid ] ) ) ? $wppb_current_users_listing_ids[ $frid ] : array();
        // maybe enque maps.google.com script with the api key.
		add_action( 'wp_footer', 'wppb_userlisting_one_map_scripts' );

		return wppb_aggregate_map_pins( $frid, $args );
	}
}
add_filter( 'mustache_variable_users_one_map', 'wppb_userlisting_users_one_map', 10, 4 );

if ( ! function_exists( 'wppb_map_max_pagination_fct' ) ) {
	/**
	 * Function that returns the maximum number of users per map iteration.
	 *
	 * @param integer $max Per page.
	 * @return integer
	 */
	function wppb_map_max_pagination_fct( $max ) {
		$max = abs( (int) $max );
		if ( empty( $max ) || $max > 500 ) {
			$max = 50;
		}
		return $max;
	}
}
add_filter( 'wppb_map_max_pagination', 'wppb_map_max_pagination_fct' );

if ( ! function_exists( 'wppb_aggregate_map_pins' ) ) {
	/**
	 * Return the content generated by a shortcode with the specific arguments.
	 *
	 * @param  integer $form_id Form id.
	 * @param  array   $args    Array of shortcode arguments.
	 * @return string
	 */
	function wppb_aggregate_map_pins( $form_id, $args = array() ) {
		global $wpdb, $wppb_current_users_listing_arguments;

		$pin_meta = wppb_options_get_map_pin_meta();
		$api_key  = wppb_options_get_map_api_key();

		if ( ! empty( $api_key ) ) {
			// Attempt to enqueue the Google Maps script.
			wp_enqueue_script(
				'wppb-google-maps-api-script',
				'https://maps.googleapis.com/maps/api/js?key=' . $api_key,
				array( 'jquery' ),
				PROFILE_BUILDER_VERSION,
				true
			);
		}

		$defaults   = array();
		$args       = wp_parse_args( $args, $defaults );
		$args       = $wppb_current_users_listing_arguments[ $form_id ];
		$item_hash  = $form_id . '_' . md5( serialize( $args ) );
		$settings   = wppb_options_get_map_settings();
		$map_lat    = ( ! empty( $settings['map-default-lat'] ) ) ? $settings['map-default-lat'] : '';
		$map_lng    = ( ! empty( $settings['map-default-lng'] ) ) ? $settings['map-default-lng'] : '';
		$map_zoom   = ( ! empty( $settings['map-default-zoom'] ) ) ? $settings['map-default-zoom'] : '';
		$map_height = ( ! empty( $settings['map-height'] ) ) ? $settings['map-height'] : 460;
		$map_load   = ( ! empty( $settings['map-pins-load-type'] ) ) ? $settings['map-pins-load-type'] : '';
		$ititems    = ( ! empty( $settings['map-pagination-number'] ) ) ? $settings['map-pagination-number'] : $args['number'];
		$ititems    = apply_filters( 'wppb_map_max_pagination-number', $ititems );

		$args['number'] = $ititems;

        $use_paged = ( 'all' !== $map_load ) ? true : false;
		$current_list   = wppb_get_users_pins( $form_id, 1, $args, $ititems, $use_paged  );

		ob_start();
		?>

		<?php echo wppb_agregate_map_assets( $api_key, $map_lat, $map_lng, $map_zoom, $map_height ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php if ( 'all' === $map_load ) : ?>

			<a class="wppb-acf-map-all-load-more load-for-<?php echo esc_attr( $item_hash ); ?>-map" id="<?php echo esc_attr( $item_hash ); ?>_more"
				data-page="1"
				data-paged="<?php echo (int) $args['paged']; ?>"
				data-totalpages="<?php echo (int) $current_list['total_pages']; ?>"
				data-ititems="<?php echo (int) $ititems; ?>"
				data-type="<?php echo esc_attr( $map_load ); ?>"
				data-maphash="#<?php echo esc_attr( $item_hash ); ?>"
				data-action="wppb_request_users_pins"
				data-formid="<?php echo (int) $form_id; ?>"
				data-args="<?php echo esc_js( serialize( $wppb_current_users_listing_arguments[ $form_id ] ) ); ?>">Load All</a>
		<?php endif; ?>
		<div id="<?php echo esc_attr( $item_hash ); ?>">
			<?php if ( empty( $api_key ) ) : ?>
				<?php esc_html_e( 'The API Key was not provided.', 'profile-builder' ); ?>
			<?php else : ?>
				<?php if ( ! empty( $args ) ) : ?>
					<div class="wppb-acf-map-all" id="<?php echo esc_attr( $item_hash ); ?>-map" data-loadmore="#<?php echo esc_attr( $item_hash ); ?>_more" data-status="">
						<?php
						if ( ! empty( $current_list['pins'] ) ) {
							$pins = wp_list_pluck( $current_list['pins'], 'pin_markup' );
							echo implode( ' ', $pins ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}
}
