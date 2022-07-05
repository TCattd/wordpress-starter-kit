/*
 * Global variable that holds all our Google Maps
 *
 */
var wppb_maps = [];


/*
 * Initialize all map fields on document ready
 *
 */
jQuery( function() {

    wppb_initialize_map();

});


/*
 * Initializes all map fields
 *
 */
function wppb_initialize_map() {

    var $maps = jQuery('.wppb-map-container');

    var maps = [];

    /*
     * Render each map
     *
     */
    $maps.each( function() {

        render_map( jQuery(this) );

    });


    // After all maps are rendered save them also in the global variable
    wppb_maps = maps;


    /*
     * Renders the map with all elements
     *
     */
    function render_map( $elem ) {
        // Set default center of the map
        var center = {
            lat: ( $elem.data( 'default-lat' ) ? parseFloat( $elem.data( 'default-lat' ) ) : 12 ),
            lng: ( $elem.data( 'default-lng' ) ? parseFloat( $elem.data( 'default-lng' ) ) : 48 )
        };

        // Default center of the map
        var args = {
            zoom		: ( $elem.data('default-zoom') ) ? parseInt( $elem.data('default-zoom') ) : 4,
            center		: new google.maps.LatLng( center ),
            mapTypeId	: google.maps.MapTypeId.ROADMAP
        };

        // Init the map
        var map = new google.maps.Map( $elem[0], args );

        // Cache the jQuery object and get saved positions from the db
        map.jq_obj          = $elem;
        map.markers         = [];
        map.saved_positions = get_saved_positions( $elem );
        map.editable        = ( $elem.data('editable') == 1 ) ? true : false;

        // Add search box
        if( jQuery( '#' + $elem.attr('id') + '-search-box').length > 0 ) {

            var input = document.getElementById( $elem.attr('id') + '-search-box' );
            var searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            searchBox.addListener( 'places_changed', function() {

                var places = searchBox.getPlaces();

                if( places.length == 0 )
                    return;

                // Place markers for all locations found
                places.forEach( function(place) {
					map_markers_limit(map);
					add_marker(map, {lat: place.geometry.location.lat(), lng: place.geometry.location.lng()}, '');
                });

                // Center map after new markers have been placed
                center_map( map );

            });

        }

        // Cache map
        maps.push( map );

        // Add saved markers
        if( map.saved_positions.length > 0 ) {

			for (x in map.saved_positions) {
				if (map.saved_positions[x].content) {
					add_marker(map, map.saved_positions[x], map.saved_positions[x].content);
				}
			}

            center_map( map );

        }

        // Add marker when clicking the map
		if (map.editable) {
            map.addListener( 'click', function( event ) {
				map_markers_limit(map);

                add_marker( map, { lat: event.latLng.lat(), lng: event.latLng.lng() }, '' );
            });
        }

    }

	// If previously added other marker, remove these.
	function map_markers_limit(map) {
		if (map.markers) {
			// If previously added other marker, remove these.
			jQuery(map.markers).each(function(index,m) {
				remove_marker(map, index);
			});
		}
	}

    /*
     * Centers a map in correlation with the markers
     *
     */
    function center_map( map ) {

        var bounds = new google.maps.LatLngBounds();

        for( x in map.markers ) {
            var latlng = new google.maps.LatLng( map.markers[x].position.lat(), map.markers[x].position.lng() );
            bounds.extend( latlng );
        }

        if( map.markers.length == 1 ) {

            map.setCenter( bounds.getCenter() );
            map.setZoom( map.jq_obj.data('default-zoom') );

        } else {

            map.fitBounds( bounds );

        }

    }


    /*
     * Adds a new marker to the map and also adds it in the hidden fields
     * if it does not exist
     *
     */
	function add_marker(map, position, pin_content) {

        // Add marker to the map
        var marker = new google.maps.Marker({
            position : new google.maps.LatLng( position ),
            map      : map
        });

        // Cache marker
        if( map.markers.indexOf( marker ) == -1 ) {
            map.markers.push( marker );
        }

        // Add hidden marker field
        if( map.saved_positions.indexOf( position ) == -1 ) {
            map.jq_obj.parent().append( '<input name="' + map.jq_obj.attr('id') + '[]" type="hidden" class="wppb-map-marker" value="' + position.lat + ',' + position.lng + '" />' );
        }

        // if( map.jq_obj.parents('form').length > 0 ) {
		if (map.editable) {
            var infoWindow = new google.maps.InfoWindow({
                content : '<a class="wppb-map-remove-marker" data-map="' + maps.indexOf( map ) + '" data-marker="' + map.markers.indexOf( marker ) + '" href="#">' + wppb_maps_data.map_marker_text_remove + '</a>'
            });

			marker.addListener( 'click', function(){
				infoWindow.open( map, marker );
			});
        } else {
			// Perhaps check if the bubble should be available.
			if (pin_content) {
				var infoWindow = new google.maps.InfoWindow({
					content: pin_content
				});

				// Show info window when marker is clicked
				marker.addListener('click', function() {
					infoWindow.open(map, marker);
				});
			}
		}
    }


    /*
     * Removes a marker from the map and also the hidden field associated with the marker
     *
     */
    function remove_marker( map, index ) {

        var marker = map.markers[index];

        // Remove hidden marker field
        map.jq_obj.parent().find( 'input[value="' + marker.position.lat() + ',' + marker.position.lng() + '"]' ).remove();

        // Remove marker from map
        marker.setMap(null);
        marker = null;

    }


    /*
     * Returns the markers for a given map
     * Markers are represented by hidden field that are under the map
     *
     */
    function get_saved_positions( $map ) {

        var markers = [];

		var maybe_readable = $map.siblings('.marker.marker-content-info');
		if (maybe_readable.length) {
			jQuery(maybe_readable).each( function() {
				// add_marker_to_map(jQuery(this), $map, markers);
                if (jQuery(this)[0].previousElementSibling.id === $map.attr('id')) {
                    markers.push({
                        lat: parseFloat(jQuery(this).attr('data-lat')),
                        lng: parseFloat(jQuery(this).attr('data-lng')),
                        content: jQuery(this).html()
                    });
                }
			});
		} else {
            $map.siblings('.wppb-map-marker').each( function() {
                var val = jQuery(this).val();
                if( val != '' && val != undefined ) {
                    val = val.split(',');
                    markers.push( { lat : parseFloat( val[0] ), lng : parseFloat( val[1] ) } );
                }

            });
        }

        return markers;

    }

    /*
     * Remove marker on link click
     *
     */
    jQuery(document).on( 'click', '.wppb-map-remove-marker', function(e) {
        e.preventDefault();
        remove_marker( maps[ jQuery(this).data('map') ], jQuery(this).data('marker') );
    });


    /*
     * Disable form submit when focus is on the map search field
     *
     */
    jQuery('.wppb-user-forms').on( 'submit', function() {
        if( jQuery( '.wppb-map-search-box:focus' ).length > 0 ) {
            return false;
        }
    });

}


/*
 * Triggers a resize for all Google maps, this is needed because hidden maps don't render
 * normally if they are hidden on page load
 *
 */
function wppb_resize_maps() {

    for( var index in wppb_maps ) {
        google.maps.event.trigger( wppb_maps[index], "resize" );
    }

}

/*
 * Hook into conditional fields to refresh all maps when displaying map fields
 *
 */
jQuery(document).on( 'wppbAddRequiredAttributeEvent', function() {

    wppb_resize_maps();

});

