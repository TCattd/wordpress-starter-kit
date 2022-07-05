var wppb_msf_ajax_request = false;

jQuery( document ).ready( function() {
    var form_id = wppb_msf_getUrlParameter( 'page' );
    if( form_id === undefined ) {
        form_id = wppb_msf_getUrlParameter( 'post' );
    }

    wppb_msf_break_points_buttons( form_id );

    var data = {
		'action'				: 'wppb_msf_check_break_points',
        'wppb_msf_form_id'		: form_id
	};

	jQuery.post( wppb_msf_data.ajaxUrl , data, function( response ) {
		wppb_msf_add_break_points( response, form_id );
	} );

	jQuery( document ).on( 'click', '.wppb-msf-break', function() {
	    if( typeof form_id !== 'undefined' && ! wppb_msf_ajax_request ) {
            var id = jQuery( this ).parents( 'tr' ).attr( 'id' );

            if( jQuery( this ).attr( 'data-break-point' ) == 'yes' ) {
                jQuery( '#'+ id +'_break_point' ).remove();
                jQuery( this ).attr( 'data-break-point', 'no' ).html( '<b class="wppb-msf-add-sign">+</b>' );

                jQuery( this ).parents( 'tr' ).find( 'td' ).each( function() {
                    jQuery( this ).css( 'border-bottom', '' );
                } );

                var data_remove = {
                    'action'				: 'wppb_msf_save_break_points',
                    'wppb_msf_ajax_nonce'   : wppb_msf_data.ajaxNonce,
                    'wppb_msf_action'		: 'remove',
                    'wppb_msf_field_id'		: jQuery( this ).parents( 'tr' ).find( '.wck-content li.row-id pre' ).text(),
                    'wppb_msf_form_id'		: form_id
                };

                wppb_msf_ajax_request = true;
                jQuery.post( wppb_msf_data.ajaxUrl , data_remove, function() {
                    check_tab_titles( form_id );

                    wppb_msf_ajax_request = false;
                } );
            } else {
                jQuery( this ).parents( 'tr' ).find( 'td' ).each( function() {
                    jQuery( this ).css( 'border-bottom', '2px dashed #9A9A9A' );
                } );

                jQuery( this ).attr( 'data-break-point', 'yes' ).css( 'display', 'block' ).html( '<b class="wppb-msf-remove-sign">-</b>' );

                var data_add = {
                    'action'				: 'wppb_msf_save_break_points',
                    'wppb_msf_ajax_nonce'   : wppb_msf_data.ajaxNonce,
                    'wppb_msf_action'		: 'add',
                    'wppb_msf_field_id'		: jQuery( this ).parents( 'tr' ).find( '.wck-content li.row-id pre' ).text(),
                    'wppb_msf_form_id'		: form_id
                };

                wppb_msf_ajax_request = true;
                jQuery.post( wppb_msf_data.ajaxUrl , data_add, function() {
                    check_tab_titles( form_id );

                    wppb_msf_ajax_request = false;
                } );
            }
        } else if( wppb_msf_ajax_request ) {
            alert( wppb_msf_data.alertAjaxRequestInProcess );
        } else {
            alert( wppb_msf_data.alertUnsavedForm );
        }
	} );

    jQuery( document ).on( 'click', '#msf-tabs', function() {
        check_tab_titles( form_id );
    } );

    jQuery( document ).on( 'click', '#wppb-msf-edit-tabs-title', function( e ) {
        e.preventDefault();
        if( jQuery( '.wppb-msf-tabs-title-container' ).children().length < 1 ) {
            jQuery( '.wppb-msf-tabs-title-container' ).append( '<p class="description wppb-msf-tabs-title-heading-desc wppb-msf-unsaved-form">' + wppb_msf_data.tabsTitleDescUnsavedForm + '</p>' );
        } else {
            jQuery( '.wppb-msf-unsaved-form' ).remove();
        }

        jQuery( '.wppb-msf-tabs-title-container' ).toggle();
    } );
} );

function wppb_msf_add_break_points( break_points, form_id ) {
	if( break_points != 'not_found' && break_points != 'NULL' ) {
		var break_points_array;

		if( typeof break_points != 'object' ) {
			break_points_array = JSON.parse( break_points );
		} else {
			break_points_array = break_points;
		}

		var break_points_count = 0;

		jQuery.each( break_points_array, function( key, value ) {
            break_points_count++;

			jQuery( '.mb-table-container tbody' ).find( 'tr' ).each( function() {
				if( jQuery( this ).find( '.wck-content li.row-id pre' ).text() == key ) {
					jQuery( this ).find( 'td' ).each( function() {
						jQuery( this ).css( 'border-bottom', '2px dashed #9A9A9A' );
					} );

					jQuery( this ).find( '.wppb-msf-break' ).attr( 'data-break-point', 'yes' ).css( 'display', 'block' ).html( '<b class="wppb-msf-remove-sign">-</b>' );
				}
			} );
        } );

        check_tab_titles( form_id );
	} else if( break_points == 'not_found' ) {
        check_tab_titles( form_id );
    }
}

function check_tab_titles( form_id ) {
    if( jQuery( '#msf-tabs' ).is( ':checked' ) ) {
        jQuery( '.wppb-msf-edit-tabs-title' ).show();

        var data = {
            'action' : 'wppb_msf_check_break_points',
            'wppb_msf_form_id' : form_id
        };

        jQuery.post( wppb_msf_data.ajaxUrl, data, function( response ) {
            if( response != 'not_found' && response != 'NULL' ) {
                var break_points_object;

                if( typeof response != 'object' ) {
                    break_points_object = JSON.parse( response );
                } else {
                    break_points_object = response;
                }

                var data_tab_title = {
                    'action' : 'wppb_msf_check_tab_titles',
                    'wppb_msf_form_id' : form_id
                };

                if( jQuery( break_points_object ).length ) {
                    jQuery( '.wppb-msf-tabs-title-heading-desc' ).remove();

                    jQuery.post( wppb_msf_data.ajaxUrl, data_tab_title, function( response ) {
                        var tab_titles_array;

                        if( typeof tab_titles_array != 'object' && response != 'not_found' ) {
                            tab_titles_array = JSON.parse( response );
                        } else {
                            tab_titles_array = response;
                        }

                        var tab_title;
                        var break_points_array = Object.values( break_points_object );

                        for( var i = 0; i <= break_points_array.length; i ++ ) {
                            if( tab_titles_array != 'not_found' && i in tab_titles_array ) {
                                tab_title = tab_titles_array[i];
                            } else {
                                tab_title = wppb_msf_data.tabTitle + ' ' + ( i + 1 )
                            }

                            if( jQuery( '#wppb-msf-tab-title-' + ( i + 1 ) ).length == 0 ) {
                                jQuery( '.wppb-msf-tabs-title-container' ).append( '<label class="wppb-msf-tabs-title-label" style="display: block;"><input type="text" name="msf-tab-title[]" class="wppb-msf-tabs-title" id="wppb-msf-tab-title-' + ( i + 1 ) + '" data-msf-count="' + i + '" placeholder="' + wppb_msf_data.tabTitlePlaceholder + ' ' + ( i + 1 ) + '" value="' + tab_title + '"></label>' );
                            }
                        }

                        jQuery( '.wppb-msf-tabs-title-label' ).each( function() {
                            if( jQuery( this ).find( 'input' ).data( 'msf-count' ) > break_points_array.length || break_points_array.length == 0 ) {
                                jQuery( this ).remove();
                            }
                        } );
                    } );
                } else {
                    jQuery( '.wppb-msf-tabs-title-label' ).remove();
                    jQuery( '.wppb-msf-tabs-title-container' ).append( '<p class="description wppb-msf-tabs-title-heading-desc">' + wppb_msf_data.tabsTitleDesc + '</p>' );
                }
            } else if( response == 'NULL' ) {
                jQuery( '.wppb-msf-tabs-title-heading-desc' ).remove();
            } else {
                jQuery( '.wppb-msf-tabs-title-heading-desc' ).remove();
                jQuery( '.wppb-msf-tabs-title-container' ).append( '<p class="description wppb-msf-tabs-title-heading-desc">' + wppb_msf_data.tabsTitleDesc + '</p>' );
            }
        } );
    } else {
        jQuery( '.wppb-msf-tabs-title-label' ).remove();
        jQuery( '.wppb-msf-tabs-title-heading-desc' ).remove();
        jQuery( '.wppb-msf-edit-tabs-title' ).hide();
    }
}

function wppb_msf_break_points_buttons( form_id ) {
    var table_elements = jQuery( 'span.wppb-msf-break' );
    var table_elements_length = table_elements.length;

    table_elements.each( function( index ) {
        if( index != table_elements_length - 1 && jQuery( this ).closest( 'tr' ).find( 'td.wck-content #wppb-login-email-nag, td.wck-content #wppb-display-name-nag' ).length !== 1 ) {
            jQuery( this ).appendTo( jQuery( this ).closest( 'tr' ).find( 'td.wck-number' ) );
        } else if( jQuery( this ).attr( 'data-break-point' ) == 'yes' ) {
            jQuery( this ).attr( 'data-break-point', 'no' );

            jQuery( this ).parents( 'tr' ).find( 'td' ).each( function() {
                jQuery( this ).css( 'border-bottom', '' );
            } );

            var data_remove = {
                'action'				: 'wppb_msf_save_break_points',
                'wppb_msf_ajax_nonce'   : wppb_msf_data.ajaxNonce,
                'wppb_msf_action'		: 'remove',
                'wppb_msf_field_id'		: jQuery( this ).parents( 'tr' ).find( '.wck-content li.row-id pre' ).text(),
                'wppb_msf_form_id'		: form_id
            };

            jQuery.post( wppb_msf_data.ajaxUrl , data_remove, function() {
                check_tab_titles( form_id );
            } );
        }
    } );

    jQuery( 'tr.added_fields_list' ).on('mouseenter', function() {
            jQuery( this ).find( 'span.wppb-msf-break' ).fadeIn( 0 );
        }
    );
    jQuery( 'tr.added_fields_list' ).on('mouseleave', function() {
            if( jQuery( this ).find( 'span.wppb-msf-break' ).attr( 'data-break-point' ) != 'yes' ) {
                jQuery( this ).find( 'span.wppb-msf-break' ).fadeOut( 0 );
            }
        }
    );
}

function wppb_msf_getUrlParameter( wppbParam ) {
	var wppbPageURL = decodeURIComponent( window.location.search.substring( 1 ) ),
		wppbURLVariables = wppbPageURL.split( '&' ),
		wppbParameterName,
		i;

	for( i = 0; i < wppbURLVariables.length; i++ ) {
		wppbParameterName = wppbURLVariables[i].split( '=' );

		if( wppbParameterName[0] === wppbParam ) {
			return wppbParameterName[1] === undefined ? true : wppbParameterName[1];
		}
	}
}