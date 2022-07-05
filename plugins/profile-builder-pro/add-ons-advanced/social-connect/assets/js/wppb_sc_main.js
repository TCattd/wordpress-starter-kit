function wppbGetCookie( cname ) {
    var name = cname + "=";
    var ca = document.cookie.split( ';' );
    for( var i = 0; i < ca.length; i++ ) {
        var c = ca[i];
        while( c.charAt( 0 ) == ' ' ) c = c.substring( 1 );
        if( c.indexOf( name ) == 0 ) return c.substring( name.length, c.length );
    }
    return "";
}

function wppbSCLogin( data, platformSettings, platform ) {
    var login_selector = jQuery( '.wppb-sc-' + platform + '-login.wppb-sc-clicked' );

    jQuery( login_selector.children() )
        .removeClass()
        .addClass( 'wppb-sc-icon' )
        .addClass( 'wppb-sc-icon-spinner' );

    var form_location  = localStorage.getItem( 'wppb_form_location' );
    data['is_pb_login_form'] = 'false';

    if( form_location !== null && form_location !== 'undefined' && jQuery( 'input[name=wppb_login]' ).length ) {
        jQuery.each( jQuery( 'input[name=wppb_login]' ), function() {
            if( jQuery( this ).val() == 'true' && jQuery( this ).siblings( 'input[name=wppb_form_location]' ).val() == form_location ) {
                data['is_pb_login_form'] = 'true';
            }
        } );
    }

    // get redirect from login or registration forms
    data['redirect_to']  = jQuery( ".wppb-user-forms input[name$='redirect_to']" ).val();

    localStorage.removeItem( 'wppb_form_location' );

    jQuery.post( platformSettings.ajaxUrl, data, function( response ) {
        var message;

        var platform_icon = platform;
        if( platform == 'google' ) {
            platform_icon = 'google-plus';
        }

        jQuery( login_selector.children() )
            .removeClass( 'wppb-sc-icon-spinner' )
            .addClass( 'wppb-sc-icon-' + platform_icon );

        /* remove previous messages */
        jQuery( '.wppb-sc-message' ).remove();

        if( response == 'failed' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.error_message + '</div>' );
        } else if( response == 'linked_successful' ) {
            message = platformSettings.edit_profile_success_linked;
            var platform_name = platform[0].toUpperCase() + platform.slice(1);
            message = message.replace( "%%", platform_name );

            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + message + '</div>' );

            jQuery( login_selector.children() )
                .removeClass( 'wppb-sc-icon-spinner' )
                .addClass( 'wppb-sc-icon-' + platform_icon );

            jQuery( login_selector )
                .addClass( 'wppb-sc-disabled-btn' );

            location.reload();
        } else if( response == 'pb_login_form_no_register' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container').append( '<div class="wppb-error wppb-sc-message">' + platformSettings.pb_form_login_error + '</div>' );
        } else if( response.message == 'email_confirmation_on' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + platformSettings.email_confirmation_on + '</div>' );
            if( response.redirect != 'no_redirect' ) {
                message = platformSettings.redirect_message;
                message = message.replace( "%%", "<a href='" + response.redirect + "'>" + platformSettings.here_string + "</a>" );
                jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + message + '</div>' );
                window.setTimeout( function() {
                    window.location.href = response.redirect;
                }, 5000 );
            }
        } else if( response.message == 'admin_approval_on' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + platformSettings.admin_approval_on + '</div>' );
            if( response.redirect != 'no_redirect' ) {
                message = platformSettings.redirect_message;
                message = message.replace( "%%", "<a href='" + response.redirect + "'>" + platformSettings.here_string + "</a>" );
                jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + message + '</div>' );
                window.setTimeout( function() {
                    window.location.href = response.redirect;
                }, 5000 );
            }
        } else if( response == 'email_confirmation_error' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.email_confirmation_error + '</div>' );
        } else if( response == 'admin_approval_error' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.admin_approval_error + '</div>' );
        } else if( response == 'only_login' ) {
            jQuery( login_selector ).closest( '.wppb-sc-buttons-container').append( '<div class="wppb-error wppb-sc-message">' + platformSettings.only_login_error + '</div>' );
        } else {
            var clickresponse = JSON.parse( response );

            if( typeof( clickresponse.redirect_to ) !== 'undefined' ) {
                jQuery( login_selector.children() )
                    .removeClass()
                    .addClass( 'wppb-sc-icon' )
                    .addClass( 'wppb-sc-icon-spinner' );

                window.location.href = clickresponse.redirect_to;
            } else if( clickresponse.action == 'wppb_sc_existing_account_prompt') {
                jQuery( "#wppb_sc_account_exists" ).remove();
                jQuery( "body" ).append(
                    "<div id='wppb_sc_account_exists' style='display:none'>" +
                        "<p>" + platformSettings.account_exists_text + "</p>" +
                        "<input type='submit' id='wppb_sc_account_connect' value='" + platformSettings.account_exists_button_yes + "' />" +
                        "<input type='submit' id='wppb_sc_new_account' value='" + platformSettings.account_exists_button_no + "' />" +
                    "</div>"
                );

                tb_show( '', '#TB_inline?height=200&width=500&inlineId=wppb_sc_account_exists', '' );

                jQuery( 'input#wppb_sc_account_connect' ).on( 'click', function() {
                    wppbSCLogin_account_exists_connect( data, platformSettings, platform, clickresponse, login_selector );
                } );

                jQuery( 'input#wppb_sc_new_account' ).on( 'click', function() {
                    wppbSCLogin_account_exists_make_new( data, platformSettings, platform, clickresponse, login_selector );
                } );
            } else if( clickresponse.action == 'wppb_sc_gdpr' ) {
                wppbSCLogin_gdpr( data, platformSettings, platform, clickresponse, login_selector );
            }
        }

        jQuery( '.wppb-sc-clicked' ).removeClass( 'wppb-sc-clicked' );
    } );
}

function wppbSCLogin_gdpr( data, platformSettings, platform, clickresponse, login_selector ) {
    jQuery( "#wppb-sc-gdpr-container" ).remove();

    if( wppb_sc_data.users_can_register == 1 ) {
        jQuery( "body" ).append(
            '<div id="wppb-sc-gdpr-container" class="wppb-sc-gdpr-container" style="display:none">' +
                '<form class="wppb_sc_form">' +
                    '<p>' + platformSettings.gdpr_message + '</p>' +
                    '<label for="wppb_sc_user_consent_gdpr">' +
                        '<input id="wppb_sc_user_consent_gdpr" class="wppb_sc_custom_field_gdpr" value="agree" name="user_consent_gdpr" type="checkbox" style="margin-right: 10px;">' + platformSettings.gdpr_description + '<span class="wppb-required" title="This field is required">*</span>' +
                    '</label>' +
                    '<input type="submit" id="wppb_sc_gdpr_submit" value="' + platformSettings.pb_form_continue + '" />' +
                '</form>' +
            '</div>'
        );

        jQuery( "#TB_window" ).remove();

        tb_show( '', '#TB_inline?height=180&width=500&inlineId=wppb-sc-gdpr-container', '' );

        jQuery( 'input#wppb_sc_gdpr_submit' ).on( 'click', function( e ) {
            jQuery( login_selector.children() )
                .removeClass()
                .addClass( 'wppb-sc-icon' )
                .addClass( 'wppb-sc-icon-spinner' );

            e.preventDefault();
            if( jQuery( '#wppb_sc_user_consent_gdpr' ).length && !jQuery( '#wppb_sc_user_consent_gdpr' ).prop( 'checked' ) ) {
                tb_remove();

                var platform_icon = platform;
                if( platform == 'google' ) {
                    platform_icon = 'google-plus';
                }

                jQuery( login_selector.children() )
                    .removeClass( 'wppb-sc-icon-spinner' )
                    .addClass( 'wppb-sc-icon-' + platform_icon );

                jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.gdpr_error + '</div>' );
            } else {
                clickresponse.action = 'wppb_sc_handle_login_click';
                clickresponse.platform = platform;
                clickresponse.existing_user_id = null;
                clickresponse.wppb_sc_form_ID = data.wppb_sc_form_ID;
                clickresponse.is_pb_login_form = data.is_pb_login_form;
                clickresponse.wppb_sc_gdpr_checkbox = jQuery( '#wppb_sc_user_consent_gdpr' ).prop( 'checked' );

                jQuery.post(platformSettings.ajaxUrl, clickresponse, function (response) {

                    tb_remove();

                    var platform_icon = platform;
                    if (platform == 'google') {
                        platform_icon = 'google-plus';
                    }

                    jQuery(login_selector.children())
                        .removeClass('wppb-sc-icon-spinner')
                        .addClass('wppb-sc-icon-' + platform_icon);

                    if (response == 'pb_login_form_no_register') {
                        jQuery(login_selector).closest('.wppb-sc-buttons-container').append('<div class="wppb-error wppb-sc-message">' + platformSettings.pb_form_login_error + '</div>');
                    } else if( response.message == 'email_confirmation_on' ) {
                        jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + platformSettings.email_confirmation_on + '</div>' );
                        if( response.redirect != 'no_redirect' ) {
                            message = platformSettings.redirect_message;
                            message = message.replace( "%%", "<a href='" + response.redirect + "'>" + platformSettings.here_string + "</a>" );
                            jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + message + '</div>' );
                            window.setTimeout( function() {
                                window.location.href = response.redirect;
                            }, 5000 );
                        }
                    } else {
                        var anotherResponse = JSON.parse(response);

                        if (typeof (anotherResponse.redirect_to) !== 'undefined') {
                            window.location.href = anotherResponse.redirect_to;
                        }
                    }
                });
            }
        } );
    } else {
        tb_remove();
        jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.only_login_error + '</div>' );
    }
}

function wppbSCLogin_account_exists_connect( data, platformSettings, platform, clickresponse, login_selector ) {
    jQuery( "#wppb_sc_account_password_tb" ).remove();
    jQuery( "body" ).append(
        "<div id='wppb_sc_account_password_tb' style='display:none'>" +
            "<p>" + platformSettings.password_text + "</p>" +
            "<form class='wppb_sc_form'>" +
                "<input type='password' id='wppb_sc_account_password' name='password'>" +
                "<input type='submit' id='wppb_sc_submit_account_password' value='" + platformSettings.social_connect_text_ok + "' />" +
            "</form>" +
        "</div>"
    );

    jQuery( "#TB_window" ).remove();
    jQuery( "body" ).append( "<div id='TB_window'></div>" );

    tb_show( '', '#TB_inline?height=150&width=500&inlineId=wppb_sc_account_password_tb', '' );

    jQuery( 'input#wppb_sc_submit_account_password' ).on( 'click', function( e ) {
        jQuery( login_selector.children() )
            .removeClass()
            .addClass( 'wppb-sc-icon' )
            .addClass( 'wppb-sc-icon-spinner' );

        e.preventDefault();
        var password = jQuery( '#wppb_sc_account_password' ).val();
        tb_remove();
        if( password != null ) {
            clickresponse.action = 'wppb_sc_handle_login_click';
            clickresponse.platform = platform;
            clickresponse.password = password;
            clickresponse.wppb_sc_form_ID = data.wppb_sc_form_ID;

            jQuery.post( platformSettings.ajaxUrl, clickresponse, function( response ) {
                var anotherResponse = JSON.parse( response );
                if( typeof( anotherResponse.error ) !== 'undefined' ) {
                    var platform_icon = platform;
                    if( platform == 'google' ) {
                        platform_icon = 'google-plus';
                    }

                    jQuery( login_selector.children() )
                        .removeClass( 'wppb-sc-icon-spinner' )
                        .addClass( 'wppb-sc-icon-' + platform_icon );

                    jQuery( login_selector ).closest( '.wppb-sc-buttons-container').append( '<div class="wppb-error wppb-sc-message">' + platformSettings.wrong_password_error + '</div>' );
                } else {
                    if( typeof( anotherResponse.redirect_to ) !== 'undefined' ) {
                        window.location.href = anotherResponse.redirect_to;
                    }
                }
            } );
        }
    } );
}

function wppbSCLogin_account_exists_make_new( data, platformSettings, platform, clickresponse, login_selector ) {
    jQuery( "#wppb_sc_account_email_tb" ).remove();

    if( wppb_sc_data.users_can_register == 1 ) {
        jQuery( "body" ).append(
            "<div id='wppb_sc_account_email_tb' style='display:none'>" +
                "<p>" + platformSettings.new_email_text + "</p>" +
                "<form class='wppb_sc_form'>" +
                    "<input type='text' id='wppb_sc_account_email' name='email'>" +
                    "<input type='submit' id='wppb_sc_submit_account_email' value='" + platformSettings.social_connect_text_ok + "' />" +
                "</form>" +
            "</div>"
        );

        jQuery( "#TB_window" ).remove();
        jQuery( "body" ).append( "<div id='TB_window'></div>" );

        tb_show( '', '#TB_inline?height=150&width=500&inlineId=wppb_sc_account_email_tb', '' );

        jQuery( 'input#wppb_sc_submit_account_email' ).on( 'click', function( e ) {
            jQuery( login_selector.children() )
                .removeClass()
                .addClass( 'wppb-sc-icon' )
                .addClass( 'wppb-sc-icon-spinner' );

            e.preventDefault();
            var newEmail = jQuery( '#wppb_sc_account_email' ).val();
            tb_remove();
            var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            if( newEmail != null && newEmail.length !== 0 && emailReg.test( newEmail ) ) {
                clickresponse.action = 'wppb_sc_handle_login_click';
                clickresponse.platform = platform;
                clickresponse.new_email = newEmail;
                clickresponse.existing_user_id = null;
                clickresponse.wppb_sc_form_ID = data.wppb_sc_form_ID;
                clickresponse.is_pb_login_form = data.is_pb_login_form;

                jQuery.post( platformSettings.ajaxUrl, clickresponse, function( response ) {
                    if( response == 'pb_login_form_no_register' ) {
                        tb_remove();

                        var platform_icon = platform;
                        if( platform == 'google' ) {
                            platform_icon = 'google-plus';
                        }

                        jQuery( login_selector.children() )
                            .removeClass( 'wppb-sc-icon-spinner' )
                            .addClass( 'wppb-sc-icon-' + platform_icon );

                        jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.pb_form_login_error + '</div>' );
                    } else {
                        var anotherResponse = JSON.parse( response );

                        if( typeof( anotherResponse.redirect_to ) !== 'undefined' ) {
                            window.location.href = anotherResponse.redirect_to;
                        }
                    }
                } );
            } else {
                tb_remove();

                var platform_icon = platform;
                if( platform == 'google' ) {
                    platform_icon = 'google-plus';
                }

                jQuery( login_selector.children() )
                    .removeClass( 'wppb-sc-icon-spinner' )
                    .addClass( 'wppb-sc-icon-' + platform_icon );

                jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.valid_email_error + '</div>' );
            }
        } );
    } else {
        tb_remove();
        jQuery( login_selector ).closest( '.wppb-sc-buttons-container' ).append( '<div class="wppb-error wppb-sc-message">' + platformSettings.only_login_error + '</div>' );
    }
}

// unlink social accounts url
jQuery( window ).on( 'load', function() {
    jQuery( '.wppb_sc_unlink_account' ).on( 'click', function( e ) {
        e.preventDefault();

        var data = {
            'action'                        : 'wppb_sc_unlink_account',
            'wppb_sc_unlink_platform_id'    : e.currentTarget.id
        };

        jQuery.post( wppb_sc_data.ajaxUrl , data, function( response ) {
            if( response == 'successful_unlink' ) {
                if( jQuery( e.currentTarget.parentNode ).prev( '.wppb-sc-separator').length == 0 ) {
                    jQuery( e.currentTarget.parentNode ).next( '.wppb-sc-separator' ).remove();
                }
                jQuery( e.currentTarget.parentNode ).prev( '.wppb-sc-separator' ).remove();
                jQuery( e.currentTarget.parentNode ).remove();
                jQuery( '.wppb-sc-buttons-container .wppb-sc-message' ).remove();

                var message = wppb_sc_data.edit_profile_success_unlink;

                switch( e.currentTarget.id ) {
                    case 'wppb_sc_unlink_facebook':
                        message = message.replace( "%%", "Facebook" );
                        jQuery( '.wppb-sc-facebook-login' ).removeClass( 'wppb-sc-disabled-btn' );
                        break;
                    case 'wppb_sc_unlink_google':
                        message = message.replace( "%%", "Google" );
                        jQuery( '.wppb-sc-google-login' ).removeClass( 'wppb-sc-disabled-btn' );
                        break;
                    case 'wppb_sc_unlink_twitter':
                        message = message.replace( "%%", "Twitter" );
                        jQuery( '.wppb-sc-twitter-login' ).removeClass( 'wppb-sc-disabled-btn' );
                        break;
                    case 'wppb_sc_unlink_linkedin':
                        message = message.replace( "%%", "LinkedIn" );
                        jQuery( '.wppb-sc-linkedin-login' ).removeClass( 'wppb-sc-disabled-btn' );
                        break;
                }

                jQuery( '.wppb-sc-buttons-container' ).append( '<div class="wppb-success wppb-sc-message">' + message + '</div>' );

                if( jQuery( '.wppb-sc-linked-accounts-text' ).find( 'span' ).length == 0 ) {
                    jQuery( '.wppb-sc-linked-accounts-text' ).remove();
                }
            }
        } );
    } );
} );
