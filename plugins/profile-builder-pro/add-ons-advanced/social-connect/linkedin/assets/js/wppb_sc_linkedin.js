jQuery( document ).ready( function() {

    jQuery( '.wppb-sc-linkedin-login' ).on( 'click', wppbLOnClick );

    jQuery( document ).on( 'elementor/popup/show', () => {
        jQuery( '.wppb-sc-linkedin-login' ).on( "click.wppb_sc_l_elementor", wppbLOnClick );
    } );
    jQuery( document ).on( 'elementor/popup/hide', () => {
        jQuery( '.wppb-sc-linkedin-login' ).off( "click.wppb_sc_l_elementor" );
    } );

    //logout
    jQuery( '.wppb-logout-url, #wp-admin-bar-logout a' ).on( 'click', function() {
        if( ( wppbGetCookie( 'wppb_sc_security_token' ) != '' ) && ( wppbGetCookie( 'wppb_sc_platform_name' ) == 'linkedin' ) ) {
            document.cookie = 'wppb_sc_security_token' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            document.cookie = 'wppb_sc_platform_name' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
    } );

} );

function wppbLOnClick( e ) {
    e.preventDefault();

    jQuery( this ).addClass( 'wppb-sc-clicked' );

    var wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'input[name=wppb_form_location]' ).val();
    if( wppb_form_location == '' || typeof wppb_form_location == 'undefined' ) {
        wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'form' ).find( 'input[name=wppb_form_location]' ).val();
    }
    if( wppb_form_location != '' && typeof wppb_form_location != 'undefined' ) {
        localStorage.setItem( 'wppb_form_location', wppb_form_location );
    }

    var wppb_sc_form_ID_linkedin = jQuery( this ).data( 'wppb_sc_form_id_linkedin' );
    if( typeof wppb_sc_form_ID_linkedin != 'undefined' ) {
        localStorage.setItem( 'wppb_sc_form_ID_linkedin', wppb_sc_form_ID_linkedin );
    }

    var data = {
        'action'                    : 'wppb_sc_save_cookies',
        'wppb_sc_security_token'    : wppb_sc_linkedin_data.client_id,
        'wppb_sc_platform_name'     : 'linkedin'
    };
    jQuery.post( wppb_sc_linkedin_data.ajaxUrl, data, function() {});

    //open a popup-window
    window.open("https://www.linkedin.com/oauth/v2/authorization?response_type=code&scope=r_liteprofile%20r_emailaddress&state=J57asfJJJ21231PPnq4&client_id="+ wppb_sc_linkedin_data.client_id+"&redirect_uri="+ wppb_sc_data.homeUrl+"?wppb_sc_linkedin_login=true", "LinkedIn", 'width=600,height=600,scrollbars=yes');
}