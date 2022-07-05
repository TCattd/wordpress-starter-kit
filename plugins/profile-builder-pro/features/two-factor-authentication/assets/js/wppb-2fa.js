var WPPBAuthNonce = wppb_2fa_script_vars.WPPBAuthNonce;
var ajaxurl       = wppb_2fa_script_vars.ajaxurl;
var valid         = wppb_2fa_script_vars.valid;
var invalid       = wppb_2fa_script_vars.invalid;

jQuery( document ).ready( ShowOrHideFields );

jQuery( '#wppb_auth_enabled' ).on( 'change', ShowOrHideFields );

//Create new secret and display it
jQuery( '#wppb_auth_newsecret' ).on( 'click', function() {
    var data = new Object();
    data['action']	= 'WPPBAuth_new_secret';
    data['nonce']	= WPPBAuthNonce;
    jQuery.post( ajaxurl, data, function( response ) {
        jQuery( '#wppb_auth_secret' ).val( response['new-secret'] );
        var qrcode = 'otpauth://totp/'+escape( jQuery( '#wppb_auth_description' ).val() )+'?secret='+jQuery( '#wppb_auth_secret' ).val();
        // Replace QRCode if one is shown
        if ( jQuery( '#wppb_auth_QR_INFO' ).is( ':visible' ) ) {
            jQuery( '#wppb_auth_QRCODE' ).html( '' );
            jQuery( '#wppb_auth_QRCODE' ).qrcode( qrcode );
        }
    } );
} );

// If the user starts modifying the description, hide the qrcode
jQuery( '#wppb_auth_description' ).on( 'focus blur change keyup', function() {
    // Only remove QR Code if it's visible
    if ( jQuery( '#wppb_auth_QR_INFO' ).is( ':visible' ) ) {
        jQuery( '#wppb_auth_QR_INFO' ).hide();
        jQuery( '#wppb_auth_QRCODE' ).html( '' );
    }
} );

function ShowOrHideFields() {
    if( jQuery( '#wppb_auth_enabled' ).is( ':checked' ) ){
        jQuery( '#wppb_auth_active, .wppb_auth_active' ).show();
    } else {
        jQuery( '#wppb_auth_active, .wppb_auth_active' ).hide();
    }
}

function ShowOrHideQRCode() {
    if ( jQuery( '#wppb_auth_QR_INFO' ).is( ':hidden' ) ) {
        var qrcode = 'otpauth://totp/'+escape( jQuery( '#wppb_auth_description' ).val() )+'?secret='+jQuery( '#wppb_auth_secret' ).val();
        jQuery( '#wppb_auth_QRCODE' ).qrcode( qrcode );
        jQuery( '#wppb_auth_QR_INFO' ).show();
    } else {
        jQuery( '#wppb_auth_QR_INFO' ).hide();
        jQuery( '#wppb_auth_QRCODE' ).html( '' );
    }
}

//Verify TOTP
jQuery( '#wppb_auth_verify_button' ).on( 'click', function() {
    var data = new Object();
    data['action']      = 'WPPBAuth_check_code';
    data['nonce']       = WPPBAuthNonce;
    data['secret']      = jQuery( '#wppb_auth_secret' ).val();
    data['relaxedmode'] = jQuery( '#wppb_auth_relaxedmode' ).val();;
    data['otp']         = jQuery( '#wppb_auth_passw' ).val();;
    jQuery.post( ajaxurl, data, function( response ) {
        if ( response['valid-otp'] ) {
            jQuery( '#wppb_auth_verify_indicator' ).prop('value', valid);
            jQuery( '#wppb_auth_verify_indicator' ).removeClass( 'invalid' ).addClass( 'valid' );
            jQuery( '#wppb_auth_verify_result' ).val( 'valid' );
        } else {
            jQuery( '#wppb_auth_verify_indicator' ).prop('value', invalid);
            jQuery( '#wppb_auth_verify_indicator' ).removeClass( 'valid' ).addClass( 'invalid' );
            jQuery( '#wppb_auth_verify_result' ).val( 'invalid' );
        }
    } );
} );