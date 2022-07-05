jQuery( window ).on( 'load', function() {
    jQuery( '.wppb-sc-twitter-login' ).on( 'click', wppbTOnClick );

    jQuery( document ).on( 'elementor/popup/show', () => {
        jQuery( '.wppb-sc-twitter-login' ).on( "click.wppb_sc_t_elementor", wppbTOnClick );
    } );
    jQuery( document ).on( 'elementor/popup/hide', () => {
        jQuery( '.wppb-sc-twitter-login' ).off( "click.wppb_sc_t_elementor" );
    } );
} );

function wppbTOnClick( e ) {
    e.preventDefault();

    jQuery( this ).addClass( 'wppb-sc-clicked' );

    var wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'input[name=wppb_form_location]' ).val();
    if( wppb_form_location == '' || typeof wppb_form_location == 'undefined' ) {
        wppb_form_location = jQuery( this ).closest( 'div.wppb-sc-buttons-container' ).siblings( 'form' ).find( 'input[name=wppb_form_location]' ).val();
    }
    if( wppb_form_location != '' && typeof wppb_form_location != 'undefined' ) {
        localStorage.setItem( 'wppb_form_location', wppb_form_location );
    }

    var wppb_sc_form_ID_twitter = jQuery( this ).data( 'wppb_sc_form_id_twitter' );
    if( typeof wppb_sc_form_ID_twitter != 'undefined' ) {
        localStorage.setItem( 'wppb_sc_form_ID_twitter', wppb_sc_form_ID_twitter );
    }

    /* open custom popup */
    var tweetPopup = window.open( '', "popUpWindow", "title=Twitter,height=400,width=600,left=400,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes" );
    tweetPopup.document.open();
    tweetPopup.document.write( "<html><head><title>Twitter</title></head><body>" + wppb_sc_twitter_data.twitter_popup_text.toString() + "</body></html>" );
    tweetPopup.document.close();

    var data = {
        'action': 'wppb_sc_twitter_oauth_response'
    };

    jQuery.post( wppb_sc_twitter_data.ajaxUrl, data, function( response ) {
        var clickresponse = JSON.parse( response );

        /* save token */
        if( typeof( clickresponse.wppb_sc_security_token ) !== 'undefined' ) {
            var data = {
                'action'                    : 'wppb_sc_save_cookies',
                'wppb_sc_security_token'    : clickresponse.wppb_sc_security_token
            };

            jQuery.post( wppb_sc_twitter_data.ajaxUrl , data, function( response ) {
                /* redirect to twitter */
                if( typeof( clickresponse.redirect_to ) !== 'undefined' ) {
                    tweetPopup.location = clickresponse.redirect_to;
                }
            } );
        }
    } );
}

function wppbTwitterLogin() {
    if( wppbGetCookie( 'wppb_sc_twitter_data' ) != '' ) {
        platformData = JSON.parse( decodeURIComponent( wppbGetCookie( 'wppb_sc_twitter_data' ) ) );
        /* delete the cookie */
        document.cookie = 'wppb_sc_twitter_data' + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';

        if( typeof( platformData.name ) !== 'undefined' ) {
            
            // if we have the users email address perform the login
            if (typeof platformData.email !== 'undefined') {

                var wppb_sc_form_ID_twitter = localStorage.getItem('wppb_sc_form_ID_twitter');
                if (wppb_sc_form_ID_twitter === null || wppb_sc_form_ID_twitter === 'undefined') {
                    wppb_sc_form_ID_twitter = '';
                }

                var data = {
                    'platform': 'twitter',
                    'action': 'wppb_sc_handle_login_click',
                    'platform_response': platformData,
                    'wppb_sc_security_token': wppbGetCookie('wppb_sc_security_token'),
                    'wppb_sc_form_ID': wppb_sc_form_ID_twitter
                };

                wppbSCLogin(data, wppb_sc_twitter_data, 'twitter');

                localStorage.removeItem('wppb_sc_form_ID_twitter');

                // ask the user for his email address
            } else {

                jQuery("#wppb_sc_twitter_your_email_tb").remove();
                jQuery("body").append(
                    "<div id='wppb_sc_twitter_your_email_tb' style='display:none'>" +
                    "<p>" + wppb_sc_twitter_data.enter_twitter_email_text + "</p>" +
                    "<form class='wppb_sc_form'>" +
                    "<input type='text' id='wppb_sc_twitter_your_email' name='email'>" +
                    "<input type='submit' id='wppb_sc_submit_twitter_your_email' value='Ok' />" +
                    "</form>" +
                    "</div>"
                );

                tb_show('', '#TB_inline?height=150&width=500&inlineId=wppb_sc_twitter_your_email_tb', '');

                jQuery('input#wppb_sc_submit_twitter_your_email').on('click', function (e) {
                    e.preventDefault();
                    var yourEmail = jQuery('#wppb_sc_twitter_your_email').val();
                    tb_remove();
                    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                    if (yourEmail != null && yourEmail.length !== 0 && emailReg.test(yourEmail)) {
                        platformData.email = yourEmail;

                        var wppb_sc_form_ID_twitter = localStorage.getItem('wppb_sc_form_ID_twitter');
                        if (wppb_sc_form_ID_twitter === null || wppb_sc_form_ID_twitter === 'undefined') {
                            wppb_sc_form_ID_twitter = '';
                        }

                        var data = {
                            'platform': 'twitter',
                            'action': 'wppb_sc_handle_login_click',
                            'platform_response': platformData,
                            'wppb_sc_security_token': wppbGetCookie('wppb_sc_security_token'),
                            'wppb_sc_form_ID': wppb_sc_form_ID_twitter
                        };

                        wppbSCLogin(data, wppb_sc_twitter_data, 'twitter');
                    } else {
                        tb_remove();

                        jQuery('.wppb-sc-buttons-container').append('<div class="wppb-error wppb-sc-message">' + wppb_sc_twitter_data.twitter_invalid_email_text + '</div>');
                    }

                    localStorage.removeItem('wppb_sc_form_ID_twitter');
                });

            }

        } else {
            var wppb_sc_form_ID_twitter = localStorage.getItem( 'wppb_sc_form_ID_twitter' );
            if( wppb_sc_form_ID_twitter === null || wppb_sc_form_ID_twitter === 'undefined' ) {
                wppb_sc_form_ID_twitter = '';
            }

            var data = {
                'platform'                  : 'twitter',
                'action'                    : 'wppb_sc_handle_login_click',
                'platform_response'         : platformData,
                'wppb_sc_security_token'    : wppbGetCookie( 'wppb_sc_security_token' ),
                'wppb_sc_form_ID'           : wppb_sc_form_ID_twitter
            };

            wppbSCLogin( data, wppb_sc_twitter_data, 'twitter' );

            localStorage.removeItem( 'wppb_sc_form_ID_twitter' );
        }
    }
}