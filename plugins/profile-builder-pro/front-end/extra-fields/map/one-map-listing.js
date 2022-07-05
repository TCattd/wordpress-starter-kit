(function($) {
	var poi_count = 0;
	// DEBUG: console.log(oneMapListing);

	// This function will render a Google Map onto the selected jQuery element.
	function new_map($el) {
		var $markers = $el.find('.marker');
		var args = {
			zoom: parseInt(oneMapListing.mapZoom),
			center: new google.maps.LatLng(oneMapListing.centerLat, oneMapListing.centerLng),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map($el[0], args);
		map.markers = [];
		$markers.each(function(){
			add_marker($(this), map);
		});
		center_map(map);
		return map;
	}

	// This function will append new pins to the current populated map.
	function append_to_map(map, $el) {
		var $new_markers = $el.find('.marker');
		// map.markers = [];
		$new_markers.each(function(){
			add_marker($(this), map);
		});
		center_map(map);
		return map;
	}

	// This function will add a marker to the selected Google Map.
	function add_marker($marker, map) {
		// DEBUG: console.log('loading poi', ++ poi_count);
		var latlng = new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng'));
		var iconzise = ( $marker.attr('data-icon') ) ? $marker.attr('data-icon'): null;
		var marker = new google.maps.Marker({
			position: latlng,
			// animation: google.maps.Animation.DROP,
			map: map,
			icon: iconzise
		});
		map.markers.push( marker );

		// If marker contains HTML, add it to an infoWindow.
		if($marker.html()) {
			var infowindow = new google.maps.InfoWindow({
				content: $marker.html()
			});

			// Show info window when marker is clicked
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map, marker);
			});
		}
	}

	// This function will center the map, showing all markers attached to this map.
	function center_map(map) {
		var bounds = new google.maps.LatLngBounds();
		$.each(map.markers, function(i, marker) {
			var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
			bounds.extend(latlng);
		});

		// Only 1 marker.
		if( map.markers.length == 1 ) {
			// Set center of the map.
			map.setCenter(bounds.getCenter());
			map.setZoom(parseInt(oneMapListing.mapZoom));
		} else {
			// Fit to bounds.
			map.fitBounds(bounds);
		}
	}

	// This function is making the AJAX calls for fetching markers for filters and place these on the map.
	function loadMapPins($ob, map, mid) {
		var p = $ob.data('page');
		// DEBUG: console.log('loading pois for page ' + p);
		var $map_wrap = $($ob.data('maphash'));
		$map_wrap.addClass('map-pins-loading');
		$.ajax({
			type: 'POST',
			url: oneMapListing.actionUrl,
			action: 'wppb_request_users_pins',
			dataType: 'json',
			data: $ob.data(),
			cache: false,
		}).done(function (response) {
			if (response.pins) {
				var wrap_id = 'wppb_request_users_pins_result' + p;
				$('body').append('<div id="' + wrap_id +'"></div>');
				var $wrap = $('body #' + wrap_id);
				$.each(response.pins, function() {
					$wrap.append(this.pin_markup);
				});
				append_to_map(map, $wrap);
				$('body #' + wrap_id).remove();
			}

			if (response.continue) {
				if('all' == $ob.data('type')) {
					$ob.data('page', ++ p);
					setTimeout(function(){
						loadMapPins($ob, map, mid);
					}, 500);
				} else {
					// DEBUG: console.log('done');
					$map_wrap.removeClass('map-pins-loading');
				}
			} else {
				// DEBUG: console.log('done');
				$map_wrap.removeClass('map-pins-loading');
			}
		});
	}

	// Initiate a map instance with specific attributes, then load the markers.
	function initOneMap($map_instance) {
		var mid = $map_instance.attr('id');
		map[mid] = new_map($map_instance);
		google.maps.event.trigger(map[mid], 'resize');
		if ($map_instance.data('loadmore')) {
			// This means that the script should iterate and populate more pins.
			var $lm = $($map_instance.data('loadmore'));
			if('all' == $lm.data('type') && $lm.data('paged') == 1) {
				$lm.data('page', parseInt( $lm.data('page') ) + 1 );
				loadMapPins($lm, map[mid], mid);
			}
		}

		// Maybe a manual trigger if the case.
		$('.wppb-acf-map-all-load-more.load-for-' + mid).on('click',function() {
			loadMapPins($map_instance, map[mid], mid);
		});
	}

	// The code below will render each map when the document is ready (page has loaded).
	var map = [];
	$(document).ready(function() {
		$('.wppb-acf-map-all').each(function() {
			initOneMap($(this));
		});
	});

})(jQuery);
