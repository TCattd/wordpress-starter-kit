<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

// require all widgets
foreach ( glob( __DIR__ . '/../widgets/*.php' ) as $file ) {
	require_once $file;
}

require_once(__DIR__.'/../dynamic-tags.php');

function piotnetforms_shortcode( $args, $content ) {
	ob_start();
	if ( ! empty( $args['id'] ) ) {
		$post_id         = $args['id'];
		$raw_data = get_post_meta( $post_id, '_piotnetforms_data', true );
		$form_id = !empty($args['form_id']) ? $args['form_id'] : '';

		if ( ! empty( $raw_data ) ) {
			echo '<div id="piotnetforms" class="piotnetforms" data-piotnetforms-shortcode-id="' . esc_attr( $post_id ) . '">';
			$data = json_decode( $raw_data, true );
			$widget_content = $data['content'];
			@piotnetforms_render_loop( $widget_content, $post_id, $form_id );

			$upload = wp_upload_dir();
			$upload_dir = $upload['baseurl'];
			$upload_dir = $upload_dir . '/piotnetforms/css/';

			$css_file = $upload_dir . $post_id . ".css";
			echo '<link rel="stylesheet" href="' . $css_file . '?ver=' . get_post_meta( $post_id, '_piotnet-revision-version', true ) . '" media="all">';
			echo '</div>';

			enqueue_footer();

			wp_enqueue_script( 'piotnetforms-script' );
			wp_enqueue_style( 'piotnetforms-style' );
			wp_enqueue_style( 'piotnetforms-global-style' );
		}
	}
	return ob_get_clean();
}
add_shortcode( 'piotnetforms', 'piotnetforms_shortcode' );

function piotnetforms_render_loop( $loop, $post_id, $form_id='' ) {
	foreach ( $loop as $widget_item ) {
		$widget            = new $widget_item['class_name'];

		if (!empty($form_id)) {
			if (isset($widget_item['settings']['form_id'])) {
				$widget_item['settings']['form_id'] = $form_id;
			}
			if (isset($widget_item['settings']['piotnetforms_conditional_logic_form_form_id'])) {
				$widget_item['settings']['piotnetforms_conditional_logic_form_form_id'] = $form_id;
			}
			if (isset($widget_item['settings']['piotnetforms_booking_form_id'])) {
				$widget_item['settings']['piotnetforms_booking_form_id'] = $form_id;
			}
			if (isset($widget_item['settings']['piotnetforms_woocommerce_checkout_form_id'])) {
				$widget_item['settings']['piotnetforms_woocommerce_checkout_form_id'] = $form_id;
			}
		}

		$widget->settings  = $widget_item['settings'];
		$widget_id         = $widget_item['id'];
		$widget->widget_id = $widget_id;
		$widget->post_id   = $post_id;

		if ( ! empty( $widget_item['fonts'] ) ) {
			$fonts = $widget_item['fonts'];
			if ( ! empty( $fonts ) ) {
				echo '<script>jQuery(document).ready(function( $ ) {';
				foreach ( $fonts as $font ) :
					?>
					$('head').append('<link href="<?php echo $font; ?>" rel="stylesheet">');
					<?php
				endforeach;
				echo '})</script>';
			}
		}

		$widget_type = $widget->get_type();
		if ( $widget_type === 'section' || $widget_type === 'column' ) {
			$visibility = @$widget->widget_visibility();
			if ($visibility) {
				echo @$widget->output_wrapper_start( $widget_id );
				if ( isset( $widget_item['elements'] ) ) {
					echo @piotnetforms_render_loop( $widget_item['elements'], $post_id, $form_id );
				}
			}
		} else {
			$output = $widget->output( $widget_id );
			$output = piotnetforms_dynamic_tags( $output );
			echo @$output;
		}

		if ( $widget_type === 'section' || $widget_type === 'column' ) {
			echo @$widget->output_wrapper_end( $widget_id );
		}
	}
}

function enqueue_footer() {
	echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
	echo '<div data-piotnetforms-plugin-url="' . plugins_url() . '"></div>';
	echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/forms/tinymce/tinymce-upload.php"></div>';
	echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option('piotnetforms-stripe-publishable-key') ) . '"></div>';
	echo '<div class="piotnetforms-break-point" data-piotnetforms-break-point-md="1025" data-piotnetforms-break-point-lg="767"></div>';
	?>
		<script type="text/javascript">
			function clearValidity($this){
			    const $parent = jQuery($this).closest('.piotnetforms-field-subgroup');
			    const $firstOption = $parent.find('.piotnetforms-field-option input');
			    $firstOption.each(function(){
			    	jQuery(this)[0].setCustomValidity('');
			    }); 
			}
			
			function piotnetformsAddressAutocompleteInitMap() {
			    var inputs = document.querySelectorAll('[data-piotnetforms-address-autocomplete]');

			    inputs.forEach(function(el, index, array){
			        var autocomplete = new google.maps.places.Autocomplete(el);
			        var country = el.getAttribute('data-piotnetforms-address-autocomplete-country');
			        var map_lat = el.getAttribute('data-piotnetforms-google-maps-lat');
			        var map_lng = el.getAttribute('data-piotnetforms-google-maps-lng');
			        var zoom = el.getAttribute('data-piotnetforms-google-maps-zoom');

			        if(country == 'All') {
			          autocomplete.setComponentRestrictions({'country': []});
			        } else {
			          autocomplete.setComponentRestrictions({'country': country});
			        }

			        var $mapSelector = el.closest('.piotnetforms-fields-wrapper').querySelectorAll('[data-piotnetforms-address-autocomplete-map]');
			        if($mapSelector.length>0) {
			            var myLatLng = { lat: parseFloat(map_lat), lng: parseFloat(map_lng) };
			            var map_zoom = parseInt(zoom);
			            var map = new google.maps.Map($mapSelector[0], {
			                center: myLatLng,
			                // center: {lat: -33.8688, lng: 151.2195},
			                zoom: map_zoom
			            });

			            var infowindow = new google.maps.InfoWindow();
			            var infowindowContent = el.closest('.piotnetforms-fields-wrapper').querySelectorAll('.infowindow-content')[0];
			            infowindow.setContent(infowindowContent);
			            var marker = new google.maps.Marker({
			              map: map,
			              anchorPoint: new google.maps.Point(0, -29)
			            });

			            autocomplete.addListener('place_changed', function() {
			              infowindow.close();
			              marker.setVisible(false);
			              var place = autocomplete.getPlace();
			              if (!place.geometry) {
			                // User entered the name of a Place that was not suggested and
			                // pressed the Enter key, or the Place Details request failed.
			                window.alert("No details available for input: '" + place.name + "'");
			                return;
			              }

			              // If the place has a geometry, then present it on a map.
			              if (place.geometry.viewport) {
			                map.fitBounds(place.geometry.viewport);
			              } else {
			                map.setCenter(place.geometry.location);
			                map.setZoom(17);  // Why 17? Because it looks good.
			              }
			              marker.setPosition(place.geometry.location);
			              marker.setVisible(true);

			              var address = '';
			              if (place.address_components) {
			                address = [
			                  (place.address_components[0] && place.address_components[0].short_name || ''),
			                  (place.address_components[1] && place.address_components[1].short_name || ''),
			                  (place.address_components[2] && place.address_components[2].short_name || '')
			                ].join(' ');
			              }

			              infowindowContent.children['place-icon'].src = place.icon;
			              infowindowContent.children['place-name'].textContent = place.name;
			              infowindowContent.children['place-address'].textContent = address;
			              infowindow.open(map, marker);
			            });
			        }

			        autocomplete.addListener('place_changed', function() {
			          var place = autocomplete.getPlace();
			          el.setAttribute('data-piotnetforms-google-maps-lat', place.geometry.location.lat());
			          el.setAttribute('data-piotnetforms-google-maps-lng', place.geometry.location.lng());
			          el.setAttribute('data-piotnetforms-google-maps-formatted-address', place.formatted_address);
			          el.setAttribute('data-piotnetforms-google-maps-zoom', '17');

			          var $distanceCalculation = document.querySelectorAll('[data-piotnetforms-calculated-fields-distance-calculation]');

			          $distanceCalculation.forEach(function(el, index, array){

			            if (el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-from') !== null) {
			              var origin = el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-from');
			            } else {
			              var $origin = document.getElementById( el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-from-field-shortcode').replace('[field id="', 'form-field-').replace('"]','') );
			              var origin = $origin.getAttribute('data-piotnetforms-google-maps-formatted-address');
			            }

			            if (el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-to') !== null) {
			              var destination = el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-to');
			            } else {
			              $destination = document.getElementById( el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-to-field-shortcode').replace('[field id="', 'form-field-').replace('"]','') );
			              var destination = $destination.getAttribute('data-piotnetforms-google-maps-formatted-address');
			            }

			            if (origin != '' && destination != '') {
			              var distanceUnit = el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-unit');
			              calculateDistance(origin, destination, el.closest('.piotnetforms-field-container').querySelector('.piotnetforms-calculated-fields-form__value'), distanceUnit, el);
			            }

			          });

			        });
			    });
			}

			// calculate distance
			function calculateDistance(origin, destination, $el, distanceUnit, $input) {

			    if (origin != '' && destination != '') {
			      var service = new google.maps.DistanceMatrixService();
			      service.getDistanceMatrix(
			          {
			              origins: [origin],
			              destinations: [destination],
			              travelMode: google.maps.TravelMode.DRIVING,
			              unitSystem: google.maps.UnitSystem.IMPERIAL, // miles and feet.
			              // unitSystem: google.maps.UnitSystem.metric, // kilometers and meters.
			              avoidHighways: false,
			              avoidTolls: false
			          }, function (response, status) {
			            if (status != google.maps.DistanceMatrixStatus.OK) {
			                // console.log(err);
			            } else {
			                var origin = response.originAddresses[0];
			                var destination = response.destinationAddresses[0];
			                if (response.rows[0].elements[0].status === "ZERO_RESULTS") {
			                    // console.log("Better get on a plane. There are no roads between "  + origin + " and " + destination);
			                } else {
			                    var distance = response.rows[0].elements[0].distance;
			                    var duration = response.rows[0].elements[0].duration;
			                    // console.log(response.rows[0].elements[0].distance);
			                    var distance_in_kilo = distance.value / 1000; // the kilom
			                    var distance_in_mile = distance.value / 1609.34; // the mile
			                    var duration_text = duration.text;
			                    var duration_value = duration.value;

			                    var event = new Event("change");

			                    if (distanceUnit == 'km') {
			                      $el.innerHTML = distance_in_kilo.toFixed(2);
			                      $input.value = distance_in_kilo.toFixed(2);
			                      jQuery($input).change();
			                    } else {
			                      $el.innerHTML = distance_in_mile.toFixed(2);
			                      $input.value = distance_in_mile.toFixed(2);
			                      jQuery($input).change();
			                    }
			                }
			            }
			        });
			    }
			}

			// get distance results
			function callback(response, status) {
			    if (status != google.maps.DistanceMatrixStatus.OK) {
			        console.log(err);
			    } else {
			        var origin = response.originAddresses[0];
			        var destination = response.destinationAddresses[0];
			        if (response.rows[0].elements[0].status === "ZERO_RESULTS") {
			            console.log("Better get on a plane. There are no roads between "  + origin + " and " + destination);
			        } else {
			            var distance = response.rows[0].elements[0].distance;
			            var duration = response.rows[0].elements[0].duration;
			            console.log(response.rows[0].elements[0].distance);
			            var distance_in_kilo = distance.value / 1000; // the kilom
			            var distance_in_mile = distance.value / 1609.34; // the mile
			            var duration_text = duration.text;
			            var duration_value = duration.value;

			            console.log(distance_in_mile.toFixed(2) + 'miles');
			            return distance_in_kilo.toFixed(2);
			            // $('#duration_text').text(duration_text);
			            // $('#duration_value').text(duration_value);
			            // $('#from').text(origin);
			            // $('#to').text(destination);
			        }
			    }
			}

			document.addEventListener( 'elementor/popup/show', function(event, id, instance){
			  piotnetformsAddressAutocompleteInitMap();
			} );
		</script>
	<?php
}
