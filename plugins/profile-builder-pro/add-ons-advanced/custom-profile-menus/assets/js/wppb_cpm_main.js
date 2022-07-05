var wppb_cpm_global_height = "";
var wppb_cpm_global_width = "";

jQuery( document ).ready( function() {
    var url;

    jQuery( 'body' ).click( function( e ) {
        if( ( jQuery( e.target ).is( 'span.ubermenu-target-title' ) && jQuery( e.target ).parents( '.ubermenu-item-type-wppb_cpm_login_iframe, .ubermenu-item-type-wppb_cpm_register_iframe, .ubermenu-item-type-wppb_cpm_edit_profile_iframe' ).length )
        || ( jQuery( e.target ).is( 'a.ubermenu-target' ) && jQuery( e.target ).parents( '.ubermenu-item-type-wppb_cpm_login_iframe, .ubermenu-item-type-wppb_cpm_register_iframe, .ubermenu-item-type-wppb_cpm_edit_profile_iframe' ).length ) ) {
            e.preventDefault();

            if( jQuery( e.target ).is( 'span.ubermenu-target-title' ) ) {
                url = jQuery( e.target ).parent().attr( 'href' );
            } else {
                url = jQuery( e.target ).attr( 'href' );
            }

            if( url !== undefined ) {
                wppb_cpm_open_iframe( url );
            }
        }
    } );

    jQuery( '.menu-item-type-wppb_cpm_login_iframe, .menu-item-type-wppb_cpm_edit_profile_iframe, .menu-item-type-wppb_cpm_register_iframe' ).click( function( e ) {
        e.preventDefault();

        url = jQuery( this ).children().attr( 'href' );

        if( url !== undefined ) {
            wppb_cpm_open_iframe( url );
        }
    } );
} );

function wppb_cpm_open_iframe( url ) {
    var title = wppb_getParameterByName( 'wppb_cpm_iframe_title', url );
    wppb_cpm_global_height = wppb_getParameterByName( 'wppb_cpm_iframe_height', url );
    wppb_cpm_global_width = wppb_getParameterByName( 'wppb_cpm_iframe_width', url );

    tb_show( title, url + 'TB_iframe=true&width=' + wppb_cpm_global_width + '&height=' + wppb_cpm_global_height, '' );

    jQuery( '#TB_window').append( '<div id="wppb_cpm_spinner"></div>' );

    wppb_cpm_resize_iframe();

    if( wppb_getMobileOperatingSystem() == 'iOS' ) {
        jQuery( '#TB_iframeContent' ).wrap( "<div class='wppb_cpm_iframe_wrap'></div>" );
    }

    jQuery( '#TB_iframeContent' ).load( function() {
        jQuery( '#wppb_cpm_spinner' ).remove();
        wppb_cpm_resize_iframe();
    } );
}

function wppb_cpm_check_iframe() {
    var current_url = jQuery( document )[0]['URL'];
    var iFrame_check = ( window.location != window.parent.location );

    if( iFrame_check ) {
        if( current_url.indexOf( "wppb_cpm_redirect=yes" ) !== -1 && current_url.indexOf( "wppb_cpm_iframe=yes" ) === -1 ) {
            window.parent.location.href = current_url;
            parent.wppb_tb_remove();
        } else {
            jQuery( window ).on("unload", function() {
                if( jQuery( '.wppb-cpm-logged-in' ).length !== 0 && current_url.indexOf( "wppb_cpm_form=login" ) !== -1 ) {
                    window.parent.location.reload();
                }
            } );
        }
    }

    removeQueryVariable( 'wppb_cpm_redirect', current_url );
}
wppb_cpm_check_iframe();

function removeQueryVariable( variable, current_url ) {

    var url_parts       = current_url.split('?');
    var urlSearchParams = new URLSearchParams( url_parts[1] );

    if ( urlSearchParams.has( variable ) ){
        urlSearchParams.delete( variable );

        var url = url_parts[0] + urlSearchParams.toString();

        window.history.replaceState({}, document.title, url);
    }
}

// edited tb_remove() function
function wppb_tb_remove() {
    jQuery( "#TB_imageOff" ).unbind( "click" );
    jQuery( "#TB_closeWindowButton" ).unbind( "click" );
    jQuery( '#TB_window, #TB_overlay, #TB_HideSelect' ).trigger( 'tb_unload' ).unbind().remove();
    jQuery( 'body' ).removeClass( 'modal-open' );
    jQuery( "#TB_load" ).remove();
    if( typeof document.body.style.maxHeight == "undefined" ) { // if IE 6
        jQuery( "body", "html" ).css( { height: "auto", width: "auto" } );
        jQuery( "html" ).css( "overflow", "" );
    }
    jQuery( document ).unbind( '.thickbox' );
    return false;
}

jQuery( window ).resize( function() {
    if( document.getElementById( 'TB_iframeContent' ) && document.getElementById( 'TB_iframeContent' )['src'].indexOf( "wppb_cpm_iframe=yes" ) !== -1 ) {
        wppb_cpm_resize_iframe();
    }
} );

function wppb_cpm_resize_iframe() {
    var max_height = wppb_cpm_global_height;
    var max_width = wppb_cpm_global_width;

    var iframe_max_height = jQuery( window ).height() * 75 / 100;
    var iframe_max_width = jQuery( window ).width() * 80 / 100;

    var iframe_selector = jQuery( '#TB_iframeContent');
    var iframe_wrap_selector = jQuery( '.wppb_cpm_iframe_wrap');
    var tb_window_selector = jQuery( '#TB_window');

    iframe_selector.height( ( iframe_max_height < max_height ? iframe_max_height : max_height ) );
    iframe_wrap_selector.height( ( iframe_max_height < max_height ? iframe_max_height : max_height ) );
    tb_window_selector.css( "margin-top", parseInt( "-" + ( iframe_max_height < max_height ? iframe_max_height : max_height ) / 2 ) );
    tb_window_selector.css( "margin-left", parseInt( "-" + ( iframe_max_width < max_width ? iframe_max_width : max_width ) / 2 ) );
    iframe_selector.width( ( iframe_max_width < max_width ? iframe_max_width : max_width ) );
    iframe_wrap_selector.width( ( iframe_max_width < max_width ? iframe_max_width : max_width ) );
    tb_window_selector.width( ( iframe_max_width < max_width ? iframe_max_width : max_width ) );
}

/**
 * Determine the mobile operating system.
 * This function either returns 'iOS' or 'unknown'
 *
 * @returns {String}
 */
function wppb_getMobileOperatingSystem() {
    var userAgent = navigator.userAgent || navigator.vendor || window.opera;

    if( userAgent.match( /iPad/i ) || userAgent.match( /iPhone/i ) || userAgent.match( /iPod/i ) ) {
        return 'iOS';
    } else {
        return 'unknown';
    }
}

/**
 * Get parameters from url (by name)
 *
 * @returns {String}
 */
function wppb_getParameterByName( name, url ) {
    name = name.replace( /[\[\]]/g, "\\$&" );

    var regex = new RegExp( "[?&]" + name + "(=([^&#]*)|&|#|$)" ), results = regex.exec( url );

    if( ! results)
        return null;

    if( ! results[2])
        return '';

    return decodeURIComponent( results[2].replace( /\+/g, " " ) );
}
