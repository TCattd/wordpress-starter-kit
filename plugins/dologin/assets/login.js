document.addEventListener( 'DOMContentLoaded', function() { jQuery( document ).ready( function( $ ) {
	var dologin_can_submit_user = '';
	var dologin_can_submit_bypass = false;

	function dologin_cb( e )
	{
		var dologin_user_handler = '#user_login';
		if ( $( this ).find( '#username' ).length ) {
			dologin_user_handler = '#username';
		}

		if ( dologin_can_submit_user && dologin_can_submit_user == $( dologin_user_handler ).val() ) {
			return true;
		}

		if ( dologin_can_submit_bypass ) {
			return true;
		}

		e.preventDefault();

		$( '#dologin-process' ).show();
		$( '#dologin-process-msg' ).attr( 'class', 'dologin-spinner' ).html( '' );

		// Append the submit button for 2nd time submission
		var submit_btn = $( this ).find( '[type=submit]' ).first();
		if ( ! $( this ).find( '[type="hidden"][name="' + submit_btn.attr( 'name' ) + '"]' ).length ) {
			$( this ).append( '<input type="hidden" name="' + submit_btn.attr( 'name' ) + '" value="' + submit_btn.val() + '" />' );
		}

		var that = this;

		$.ajax( {
			url: dologin.login_url,
			type: 'POST',
			data: $( this ).serialize(),
			dataType: 'json',
			success: function( res ) {
				if ( res._res !== 'ok' ) {
					$( '#dologin-process-msg' ).attr( 'class', 'dologin-err' ).html( res._msg );
					$( '#dologin-two_factor_code' ).attr( 'required', false );
					$( '#dologin-dynamic_code' ).hide();
				} else {
					// If no phone set in profile
					if ( 'bypassed' in res ) {
						dologin_can_submit_bypass = true;
						$( that ).submit();
						return;
					}
					$( '#dologin-process-msg' ).attr( 'class', 'dologin-success' ).html( res.info );
					$( '#dologin-dynamic_code' ).show();
					$( '#dologin-two_factor_code' ).attr( 'required', true );
					dologin_can_submit_user = $( dologin_user_handler ).val();
				}

			}
		} );
	}

	$('#loginform').submit( dologin_cb );
	$('.woocommerce-form-login').submit( dologin_cb );
	// $('.tml-login form[name="loginform"], .tml-login form[name="login"], #wpmem_login form, form#ihc_login_form').submit( dologin_cb );

} ); } );