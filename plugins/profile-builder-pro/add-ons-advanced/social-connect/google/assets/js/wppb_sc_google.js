( function() {
    var po = document.createElement( 'script' ); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/platform.js?onload=wppbOnLoadCallback';
    var s = document.getElementsByTagName( 'script' )[0]; s.parentNode.insertBefore( po, s );
} )();

jQuery( document ).ready( function() {
    jQuery( '.wppb-sc-google-login' ).on( 'click', wppbGPOnClick );
    jQuery( '.wppb-sc-google-login-new' ).on( 'click', wppbGPOnClick );

    jQuery( document ).on( 'elementor/popup/show', () => {
        jQuery( '.wppb-sc-google-login' ).on( "click.wppb_sc_g_elementor", wppbGPOnClick );
        jQuery( '.wppb-sc-google-login-new' ).on( "click.wppb_sc_g_elementor", wppbGPOnClick );
    } );
    jQuery( document ).on( 'elementor/popup/hide', () => {
        jQuery( '.wppb-sc-google-login' ).off( "click.wppb_sc_g_elementor" );
        jQuery( '.wppb-sc-google-login-new' ).off( "click.wppb_sc_g_elementor" );
    } );
} );

function wppbGPOnClick( e ) {
    e.preventDefault();

    jQuery( this ).addClass( 'wppb-sc-clicked' );

    var wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'input[name=wppb_form_location]' ).val();
    if( wppb_form_location == '' || typeof wppb_form_location == 'undefined' ) {
        wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'form' ).find( 'input[name=wppb_form_location]' ).val();
    }
    if( wppb_form_location != '' && typeof wppb_form_location != 'undefined' ) {
        localStorage.setItem( 'wppb_form_location', wppb_form_location );
    }

    var wppb_sc_form_ID_google = jQuery( this ).data( 'wppb_sc_form_id_google' );
    if( typeof wppb_sc_form_ID_google != 'undefined' ) {
        localStorage.setItem( 'wppb_sc_form_ID_google', wppb_sc_form_ID_google );
    }

    wppbGPLogin();
}

function wppbOnLoadCallback() {
    gapi.load( 'auth2', function() {

        if ( !wppb_sc_google_data.client_id || !wppb_sc_google_data.plugin_name) {
            console.log("Something went wrong initializing the google auth2 api: there is a problem with the Google Client ID or Name");
            return;
        }
        var config = {
            'client_id'       :   wppb_sc_google_data.client_id,
            'plugin_name'     :   wppb_sc_google_data.plugin_name,
            'cookie_policy'   :   'single_host_origin',
            'scope'           :   'profile email'
        };

        gapi.auth2.init( config).then( function (success) { auth2 = success }, function (error) { console.log("Something went wrong initializing the google auth2 api"); console.log(error); });
    });
}

function wppbGPLogin() {
    auth2.signIn().then( wppbLoginCallback );
    return false;
}

function wppbLoginCallback( user ) {
    basic_profile = user.getBasicProfile();
    auth_response = user.getAuthResponse();

    if ( auth_response.access_token ){

        var data = {
            'action': 'wppb_sc_save_cookies',
            'wppb_sc_security_token': auth_response.access_token,
            'wppb_sc_platform_name': 'google'
        };

        jQuery.post( wppb_sc_google_data.ajaxUrl, data, function() {

            //set up the user data
            var user_data = {};
            user_data.id = basic_profile.getId();
            user_data.first_name = basic_profile.getGivenName();
            user_data.last_name = basic_profile.getFamilyName();
            user_data.email = basic_profile.getEmail();

            var wppb_sc_form_ID_google = localStorage.getItem( 'wppb_sc_form_ID_google' );
            if( wppb_sc_form_ID_google === null || wppb_sc_form_ID_google === 'undefined' ) {
                wppb_sc_form_ID_google = '';
            }

            var data = {
                'platform'                  : 'google',
                'action'                    : 'wppb_sc_handle_login_click',
                'platform_response'         : user_data,
                'wppb_sc_security_token'    : auth_response.access_token,
                'wppb_sc_form_ID'           : wppb_sc_form_ID_google
            };

            wppbSCLogin( data, wppb_sc_google_data, 'google' );

            localStorage.removeItem( 'wppb_sc_form_ID_google' );

        } );
    }
}

jQuery( function() {

    jQuery( '.wppb-logout-url, #wp-admin-bar-logout a' ).on( 'click', function() {

        if( ( wppbGetCookie( 'wppb_sc_security_token' ) != '' ) && ( wppbGetCookie( 'wppb_sc_platform_name' ) == 'google' ) ) {

            document.cookie = 'wppb_sc_security_token' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            document.cookie = 'wppb_sc_platform_name' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';

            auth2.signOut();
        }
    } );
} );