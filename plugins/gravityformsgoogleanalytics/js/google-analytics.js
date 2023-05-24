( function ( GF_Google_Analytics, $ ) {
	/* global localStorage, */
	jQuery( document ).ready( function() {
		/**
		 * Send events to Google Analytics
		 *
		 * Send events to Google Analytics. Use Ajax call so no duplicate entries are recorded.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @class
		 * @memberof jQuery
		 * @global  jQuery
		 * @fires   onload
		 * @param {number} entryId       The form entry ID to submit conversions to.
		 * @param {number} eventValue    Event value to send to Google Analytics.
		 * @param {string} eventCategory Event category to send go Google Analytics.
		 * @param {string} eventAction   Event action to send to Google Analytics.
		 * @param {string} eventLabel    Event label to send to Google Analytics.
		 */
		$.gf_send_to_ga = function send_to_ga( entryId, eventValue, eventCategory, eventAction, eventLabel ) {

			$.post( gforms_google_analytics_frontend_strings.ajaxurl, { action: 'get_entry_meta', entry_id: entryId, nonce: gforms_google_analytics_frontend_strings.nonce }, function( response ) {
				if ( false === response.data.event_sent ) {
					gfga_send_to_ga( eventValue, eventCategory, eventAction, eventLabel );

					// Now send event to GF to make sure we don't submit duplicate events
					$.post( gforms_google_analytics_frontend_strings.ajaxurl, { action: 'save_entry_meta', entry_id: entryId, nonce: gforms_google_analytics_frontend_strings.nonce } );
				}
			}, 'json' );
		};
		/**
		 * Send pagination events to Google Analytics
		 *
		 * Send pagination events to Google Analytics.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @class
		 *
		 * @memberof jQuery
		 * @global  jQuery
		 * @fires   onload
		 *
		 * @param {number} eventValue    Event value to send to Google Analytics.
		 * @param {string} eventCategory Event category to send go Google Analytics.
		 * @param {string} eventAction   Event action to send to Google Analytics.
		 * @param {string} eventLabel    Event label to send to Google Analytics.
		 */
		$.gf_send_pagination_to_ga = function paginate_send_to_ga( eventValue, eventCategory, eventAction, eventLabel ) {
			gfga_send_to_ga( eventValue, eventCategory, eventAction, eventLabel );
			$.gf_remove_local_pagination_storage();
		};
		/**
		 * Send pagination events to Google Tag Manager
		 *
		 * Send pagination events to Google Tag Manager.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @class
		 *
		 * @memberof jQuery
		 * @global  jQuery
		 * @fires   onload
		 *
		 * @param {number} eventValue    Event value to send to Google Tag Manager.
		 * @param {string} eventCategory Event category to send go Google Tag Manager.
		 * @param {string} eventAction   Event action to send to Google Tag Manager.
		 * @param {string} eventLabel    Event label to send to Google Tag Manager.
		 */
		$.gf_send_pagination_to_gtm = function paginate_send_to_gtm( eventValue, eventCategory, eventAction, eventLabel, utmSource, utmMedium, utmCampaign, utmTerm, utmContent ) {
			gfga_send_to_gtm( eventValue, eventCategory, eventAction, eventLabel, utmSource, utmMedium, utmCampaign, utmTerm, utmContent );
			$.gf_remove_local_pagination_storage();
		};
		/**
		 * Send events to Google Tag Manager.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @class
		 * @memberof jQuery
		 * @global  jQuery
		 * @fires   onload
		 *
		 * @param {number} eventId       Entry ID to send to Tag Manager.
		 * @param {number} eventValue    Event value to send to Google Tag Manager.
		 * @param {string} eventCategory Event category to send go Google Tag Manager.
		 * @param {string} eventAction   Event action to send to Google Tag Manager.
		 * @param {string} eventLabel    Event label to send to Google Tag Manager.
		 */
		$.gf_send_to_gtm = function send_to_gtm( eventId, eventValue, eventCategory, eventAction, eventLabel, utmSource, utmMedium, utmCampaign, utmTerm, utmContent ) {
			$.post( gforms_google_analytics_frontend_strings.ajaxurl, { action: 'get_entry_meta', entry_id: eventId, nonce: gforms_google_analytics_frontend_strings.nonce }, function( response ) {
				if ( false === response.data.event_sent ) {
					gfga_send_to_gtm( eventValue, eventCategory, eventAction, eventLabel, utmSource, utmMedium, utmCampaign, utmTerm, utmContent );
					// Now send event to GF to make sure we don't submit duplicate events
					$.post( gforms_google_analytics_frontend_strings.ajaxurl, { action: 'save_entry_meta', entry_id: eventId, nonce: gforms_google_analytics_frontend_strings.nonce } );
				}
			} );
		}
		/**
		 * Send events to Google Tag Manager.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @class
		 *
		 * @memberof jQuery
		 * @global  jQuery
		 * @fires   onload
		 *
		 * @param {number} eventValue    Event value to send to Google Tag Manager.
		 * @param {string} eventCategory Event category to send go Google Tag Manager.
		 * @param {string} eventAction   Event action to send to Google Tag Manager.
		 * @param {string} eventLabel    Event label to send to Google Tag Manager.
		 */
		function gfga_send_to_gtm( eventValue, eventCategory, eventAction, eventLabel, utmSource, utmMedium, utmCampaign, utmTerm, utmContent ) {
			if ( typeof ( window.parent.dataLayer ) != 'undefined' ) {
				if ( typeof ( window.parent.gfgaTagManagerEventSent ) == 'undefined' ) {
					window.parent.dataLayer.push( {
						'event': 'GFTrackEvent',
						'GFTrackCategory': eventCategory,
						'GFTrackAction': eventAction,
						'GFTrackLabel': eventLabel,
						'GFTrackValue': eventValue,
						'GFTrackSource': utmSource,
						'GFTrackMedium': utmMedium,
						'GFTrackCampaign': utmCampaign,
						'GFTrackTerm': utmTerm,
						'GFTrackContent': utmContent,
					} );
				}
				window.parent.gfgaTagManagerEventSent = true;
				if ( gforms_google_analytics_data.loggingEnabled ) {
					console.log( 'Sent GFTrackEvent using GTM with the following parameters: Category: ' + eventCategory + ', Action: ' + eventAction + ', Label: ' + eventLabel + ', Value: ' + eventValue );
				}
			}
		}

		/**
		 * Send events to Google Analytics
		 *
		 * Send events to Google Analytics. Use Ajax call so no duplicate entries are recorded.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @class
		 *
		 * @memberof jQuery
		 * @global  jQuery
		 * @fires   onload
		 *
		 * @param {number} eventValue    Event value to send to Google Analytics.
		 * @param {string} eventCategory Event category to send go Google Analytics.
		 * @param {string} eventAction   Event action to send to Google Analytics.
		 * @param {string} eventLabel    Event label to send to Google Analytics.
		 */
		function gfga_send_to_ga( eventValue, eventCategory, eventAction, eventLabel ) {
			var eventTracker = gforms_google_analytics_frontend_strings.ua_tracker;
			if ( typeof ( window.parent.gfgaAnalyticsEventSent ) == 'undefined' ) {
				// Check for gtab implementation
				if ( typeof window.parent.gtag != 'undefined' ) {
					window.parent.gtag( 'event', eventAction, {
							'event_category': eventCategory,
							'event_label': eventLabel,
							'value': eventValue,
						}
					);
				} else {
					// Check for GA from Monster Insights Plugin
					if ( typeof window.parent.ga == 'undefined' ) {
						if ( typeof window.parent.__gaTracker != 'undefined' ) {
							window.parent.ga = window.parent.__gaTracker;
						}
					}
					if ( typeof window.parent.ga != 'undefined' ) {

						var ga_send = 'send';
						// Try to get original UA code from third-party plugins or tag manager
						if ( eventTracker.length > 0 ) {
							ga_send = eventTracker + '.' + ga_send;
						}

						// Use that tracker
						window.parent.ga( ga_send, 'event', eventCategory, eventAction, eventLabel, eventValue );
					}

				}
			}
			window.parent.gfgaAnalyticsEventSent = true;
			if ( gforms_google_analytics_data.loggingEnabled ) {
				console.log( 'Sent event using GA with the following parameters: Category: ' + eventCategory + ', Action: ' + eventAction + ', Label: ' + eventLabel + ', Value: ' + eventValue );
			}
		}

		/**
		 * Clear pagination local storage
		 *
		 * Clear pagination storage when no longer used.
		 *
		 * @since      1.0.0
		 * @access     public
		 * @memberof jQuery
		 * @global  jQuery
		 */
		$.gf_remove_local_pagination_storage = function remove_pagination_storage() {
			localStorage.removeItem( 'gfpaginatetype' );
			localStorage.removeItem( 'gfpaginateid' );
			localStorage.removeItem( 'gfpaginatevalue' );
			localStorage.removeItem( 'gfpaginatecategory' );
			localStorage.removeItem( 'gfpaginateaction' );
			localStorage.removeItem( 'gfpaginatelabel' );
			localStorage.removeItem( 'gfpaginateutmsource' );
			localStorage.removeItem( 'gfpaginateutmmedium' );
			localStorage.removeItem( 'gfpaginateutmcampaign' );
			localStorage.removeItem( 'gfentrytracker' );
		};
		// Test for local storage for JS events
		var entryType = localStorage.getItem( 'googleAnalyticsFeeds' );
		if ( null != entryType ) {
			entryType = JSON.parse( entryType );
			$.each( entryType, function( id, data ) {
				if ( 'ga' === gforms_google_analytics_frontend_strings.gfgamode ) {
					$.gf_send_to_ga( data.entryId, data.entryValue, data.entryCategory, data.entryAction, data.entryLabel );
				}
				var utmVariables = localStorage.getItem('googleAnalyticsUTM');
				var utmSource = '',
					utmMedium = '',
					utmCampaign = '',
					utmTerm = '',
					utmContent = '';
				if ( null != utmVariables ) {
					utmVariables = JSON.parse( utmVariables );
					utmSource = utmVariables.source;
					utmMedium = utmVariables.medium;
					utmCampaign = utmVariables.campaign;
					utmTerm = utmVariables.term;
					utmContent = utmVariables.content;
				}
				if ( 'gtm' === gforms_google_analytics_frontend_strings.gfgamode ) {
					$.gf_send_to_gtm( data.entryId, data.entryValue, data.entryCategory, data.entryAction, data.entryLabel, utmSource, utmMedium, utmCampaign, utmTerm, utmContent );
				}
			});
			localStorage.removeItem( 'googleAnalyticsFeeds' );
		}


		// Test for paginated events
		var paginationType = localStorage.getItem( 'gfpaginatetype' );
		if ( null !== paginationType && 'pagination' === paginationType ) {
			if ( 'ga' === gforms_google_analytics_frontend_strings.gfgamode ) {
				$.gf_send_pagination_to_ga( localStorage.getItem( 'gfpaginatevalue' ), localStorage.getItem( 'gfpaginatecategory' ), localStorage.getItem('gfpaginateaction'), localStorage.getItem('gfpaginatelabel'));
			}
			if ( 'gtm' === gforms_google_analytics_frontend_strings.gfgamode ) {
				var utmVariables = localStorage.getItem( 'googleAnalyticsUTM' );
				var utmSource = '',
					utmMedium = '',
					utmCampaign = '',
					utmTerm = '',
					utmContent = '';
				if ( null != utmVariables ) {
					utmVariables = JSON.parse( utmVariables );
					utmSource = utmVariables.source;
					utmMedium = utmVariables.medium;
					utmCampaign = utmVariables.campaign;
					utmTerm = utmVariables.term;
					utmContent = utmVariables.content;
				}
				$.gf_send_pagination_to_gtm(
					localStorage.getItem( 'gfpaginatevalue' ),
					localStorage.getItem( 'gfpaginatecategory' ),
					localStorage.getItem( 'gfpaginateaction' ),
					localStorage.getItem( 'gfpaginatelabel' ),
					utmSource,
					utmMedium,
					utmCampaign,
					utmTerm,
					utmContent
				);
			}
		}
		// Test for URl appendage
		var url = wpAjax.unserialize( window.location.href );
		var actionExists = url.hasOwnProperty( 'gfaction' );
		if ( actionExists ) {
			var category = url.category;
			var label = url.label;
			var value = url.value;
			var action = url.action;

			// Format entry ID so that #gf-1 etc isn't appended to entry ID
			var matches = url.entryid.match( /^[\d]+/ );
			var entryId = matches[ 0 ];

			// Do an ajax check to make sure we're not sending an event twice
			$.post( gforms_google_analytics_frontend_strings.ajaxurl, { action: 'get_entry_meta', entry_id: entryId, nonce: gforms_google_analytics_frontend_strings.nonce }, function( response ) {
				if ( false === response.data.event_sent ) {
					if ( 'ga' === gforms_google_analytics_frontend_strings.gfgamode ) {
						gfga_send_to_ga( value, category, action, label );
					}

					if ( 'gtm' === gforms_google_analytics_frontend_strings.gfgamode ) {
						gfga_send_to_gtm( value, category, action, label, '', '', '', '', '' );
					}
					// Now send event to GF to make sure we don't submit duplicate events
					$.post( gforms_google_analytics_frontend_strings.ajaxurl, { action: 'save_entry_meta', entry_id: entryId, nonce: gforms_google_analytics_frontend_strings.nonce } );
				}
			}, 'json' );
		}
	} );
}( window.GF_Google_Analytics = window.GF_Google_Analytics || {}, jQuery ) );
