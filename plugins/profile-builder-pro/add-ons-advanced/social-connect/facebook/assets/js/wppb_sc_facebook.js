window.fbAsyncInit = function() {
    FB.init( {
        appId      : wppb_sc_facebook_data.appId,
        cookie     : true,  // enable cookies to allow the server to access
                            // the session
        xfbml      : true,  // parse social plugins on this page
        version    : 'v2.2' // use version 2.2
    } );
};

jQuery( document ).ready( function() {

    jQuery('.wppb-sc-facebook-login').on( 'click', wppbFBOnClick );

    jQuery( document ).on( 'elementor/popup/show', () => {
        jQuery( '.wppb-sc-facebook-login' ).on( "click.wppb_sc_fb_elementor", wppbFBOnClick );
    } );
    jQuery( document ).on( 'elementor/popup/hide', () => {
        jQuery( '.wppb-sc-facebook-login' ).off( "click.wppb_sc_fb_elementor" );
    } );
} );

function wppbFBOnClick( e ) {
    e.preventDefault();

    jQuery( this ).addClass( 'wppb-sc-clicked' );

    var wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'input[name=wppb_form_location]' ).val();
    if( wppb_form_location == '' || typeof wppb_form_location == 'undefined' ) {
        wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'form' ).find( 'input[name=wppb_form_location]' ).val();
    }
    if( wppb_form_location != '' && typeof wppb_form_location != 'undefined' ) {
        localStorage.setItem( 'wppb_form_location', wppb_form_location );
    }

    var wppb_sc_form_ID_facebook = jQuery( this ).data( 'wppb_sc_form_id_facebook' );
    if( typeof wppb_sc_form_ID_facebook != 'undefined' ) {
        localStorage.setItem( 'wppb_sc_form_ID_facebook', wppb_sc_form_ID_facebook );
    }

    wppbFBLogIn();
}

function wppbFBLogIn() {
    FB.login( function( response ) {
        if( response.authResponse ) {
            checkLoginState();
        }
    }, {
        scope: 'public_profile,email'
    } );

    return false;
}

// Load the SDK asynchronously
( function( d, s, id ) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if( d.getElementById( id ) ) return;
    js = d.createElement( s ); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore( js, fjs );
} ( document, 'script', 'facebook-jssdk' ) );

function statusChangeCallback( response ) {
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if( response.status === 'connected' ) {
        // Logged into your app and Facebook.
        var data = {
            'action'                    : 'wppb_sc_save_cookies',
            'wppb_sc_security_token'    : response.authResponse.accessToken,
            'wppb_sc_platform_name'     : 'facebook'
        };

        jQuery.post( wppb_sc_facebook_data.ajaxUrl , data, function() {
            wppbLoginIn( response.authResponse.accessToken );
        } );
    } else if( response.status === 'not_authorized' ) {
        // The person is logged into Facebook, but not your app.
        //document.getElementById('status').innerHTML = 'Please log ' + 'into this app.';
    } else {
        // The person is not logged into Facebook, so we're not sure if
        // they are logged into this app or not.
        //document.getElementById('status').innerHTML = 'Please log ' + 'into Facebook.';
    }
}

function checkLoginState() {
    FB.getLoginStatus( function( response ) {
        statusChangeCallback( response );
    } );
}

function wppbLoginIn( token ) {
    FB.api( '/me?fields=first_name,last_name,email', function( response ) {
        var wppb_sc_form_ID_facebook = localStorage.getItem( 'wppb_sc_form_ID_facebook' );
        if( wppb_sc_form_ID_facebook === null || wppb_sc_form_ID_facebook === 'undefined' ) {
            wppb_sc_form_ID_facebook = '';
        }

        var data = {
            'platform'                  : 'facebook',
            'action'                    : 'wppb_sc_handle_login_click',
            'platform_response'         : response,
            'wppb_sc_security_token'    : token,
            'wppb_sc_form_ID'           : wppb_sc_form_ID_facebook
        };

        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

        if( 'email' in response && response.email && emailReg.test( response.email ) ) {
            wppbSCLogin( data, wppb_sc_facebook_data, 'facebook' );
        } else {
            var data_id_check = {
                'platform'      : 'facebook',
                'action'        : 'wppb_sc_handle_platform_id_check',
                'platform_id'   : response.id
            };

            jQuery.post( wppb_sc_facebook_data.ajaxUrl, data_id_check, function( response ) {
                if( response == 'new_account' ) {
                    jQuery( "#wppb_sc_facebook_your_email_tb" ).remove();
                    jQuery( "body" ).append(
                        "<div id='wppb_sc_facebook_your_email_tb' style='display:none'>" +
                            "<p>" + wppb_sc_facebook_data.enter_facebook_email_text + "</p>" +
                            "<form class='wppb_sc_form'>" +
                                "<input type='text' id='wppb_sc_facebook_your_email' name='email'>" +
                                "<input type='submit' id='wppb_sc_submit_facebook_your_email' value='" + wppb_sc_facebook_data.facebook_text_ok + "' />" +
                            "</form>" +
                        "</div>"
                    );

                    tb_show( '', '#TB_inline?height=150&width=500&inlineId=wppb_sc_facebook_your_email_tb', '' );

                    jQuery( 'input#wppb_sc_submit_facebook_your_email' ).on( 'click', function( e ) {
                        e.preventDefault();
                        var yourEmail = jQuery( '#wppb_sc_facebook_your_email' ).val();
                        tb_remove();
                        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                        if( yourEmail != null && yourEmail.length !== 0 && emailReg.test( yourEmail ) ) {
                            data.platform_response.email = yourEmail;

                            wppbSCLogin( data, wppb_sc_facebook_data, 'facebook' );
                        } else {
                            jQuery( "#TB_window" ).remove();
                            jQuery( "body" ).append( "<div id='TB_window'></div>" );

                            setTimeout( function() {
                                jQuery( "body" ).append(
                                    "<div id='wppb_sc_wrong_email' style='display:none'>" +
                                        "<p>" + wppb_sc_facebook_data.facebook_invalid_email_text + "</p>" +
                                    "</div>"
                                );

                                tb_show( '', '#TB_inline?height=100&width=300&inlineId=wppb_sc_wrong_email', '' );
                            }, 500);
                        }
                    } );
                } else {
                    wppbSCLogin( data, wppb_sc_facebook_data, 'facebook' );
                }
            } );
        }

        localStorage.removeItem( 'wppb_sc_form_ID_facebook' );
    } );
}

jQuery( function() {
    jQuery( '.wppb-logout-url, #wp-admin-bar-logout a' ).on( 'click', function() {
        if( ( wppbGetCookie( 'wppb_sc_security_token' ) != '' ) && ( wppbGetCookie( 'wppb_sc_platform_name' ) == 'facebook' ) ) {

            document.cookie = 'wppb_sc_security_token' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            document.cookie = 'wppb_sc_platform_name' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';

            FB.getLoginStatus( function( response ) {
                FB.logout( function( response ) {
                    // Person is now logged out
                } );
            } );
        }
    } );
} );