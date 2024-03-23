document.addEventListener( 'DOMContentLoaded', function() { jQuery( document ).ready( function( $ ) {
	function dologin_keycode( num ) {
		var num = num || 13 ;
		var code = window.event ? event.keyCode : event.which ;
		if( num == code ) return true ;
		return false ;
	}

	function dologin_display_tab(tab) {
		jQuery('[data-dologin-tab]').removeClass('nav-tab-active');
		jQuery('[data-dologin-tab="'+tab+'"]').addClass('nav-tab-active');
		jQuery('[data-dologin-layout]').hide();
		jQuery('[data-dologin-layout="'+tab+'"]').show();
	}

	/*** Admin Panel JS ***/
	// page tab switch functionality
	if($('[data-dologin-tab]').length > 0){
		// display default tab
		var dologin_tab_current = document.cookie.replace(/(?:(?:^|.*;\s*)dologin_tab\s*\=\s*([^;]*).*$)|^.*$/, "$1") ;
		if(window.location.hash.substr(1)) {
			dologin_tab_current = window.location.hash.substr(1) ;
		}
		if(!dologin_tab_current || !$('[data-dologin-tab="'+dologin_tab_current+'"]').length) {
			dologin_tab_current = $('[data-dologin-tab]').first().data('dologin-tab') ;
		}
		dologin_display_tab(dologin_tab_current) ;
		// tab switch
		$('[data-dologin-tab]').click(function(event) {
			dologin_display_tab($(this).data('dologin-tab')) ;
			document.cookie = 'dologin_tab='+$(this).data('dologin-tab') ;
			$(this).blur() ;
		}) ;
	}

	/** Accesskey **/
	$( '[dologin-accesskey]' ).map( function() {
		var thiskey = $( this ).attr( 'dologin-accesskey' ) ;
		$( this ).attr( 'title', 'Shortcut : ' + thiskey.toLocaleUpperCase() ) ;
		var that = this ;
		$( document ).on( 'keydown', function( e ) {
			if( $(":input:focus").length > 0 ) return ;
			if( event.metaKey ) return ;
			if( event.ctrlKey ) return ;
			if( event.altKey ) return ;
			if( event.shiftKey ) return ;
			if( dologin_keycode( thiskey.charCodeAt( 0 ) ) ) $( that )[ 0 ].click() ;
		});
	});

	$( '#dologin_test_sms' ).click( function( e ) {
		$.ajax( {
			url: dologin_admin.url_test_sms,
			type: 'POST',
			dataType: 'json',
			data: {
				phone: dologin_admin.current_user_phone
			},
			success: function( res ) {
				if ( res._res !== 'ok' ) {
					$( '#dologin_test_sms_res' ).attr( 'class', 'dologin-err' ).html( res._msg );
				} else {
					$( '#dologin_test_sms_res' ).attr( 'class', 'dologin-success' ).html( res.info );
				}
			}
		} );
	} );

	$( '#dologin_get_ip' ).click( function( e ) {
		$.ajax( {
			url: dologin_admin.url_myip,
			dataType: 'json',
			success: function( data ) {
				var html = [];
				$.each( data, function( k, v ) {
					 html.push( k + ':' + v );
				});
				$( '#dologin_mygeolocation' ).html( html.join( ', ' ) );
			}
		} );
	} );

	function dologin_copyToClipboard(text) {
	    var $temp = $("<input>");
	    $("body").append($temp);
	    $temp.val(text).select();
	    document.execCommand("copy");
	    $temp.remove();
	}

	function dologin_copy() {
		var ori_data_title = $( this ).data( 'title' );
		// reset all data-title
		$( '.dologin_pswd_link' ).attr( 'data-title', ori_data_title );
		$( this ).attr( 'data-title', 'Copied!' );

		dologin_copyToClipboard( $( this ).text() );
	}

	$( '.dologin_pswd_link' ).click( dologin_copy );

} ); } );