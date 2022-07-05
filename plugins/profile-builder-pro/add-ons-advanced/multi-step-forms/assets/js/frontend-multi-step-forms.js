jQuery( document ).ready( function() {

    wppb_msf_init();

} );

jQuery( document ).on( 'elementor/popup/show', () => {

    wppb_msf_init();

} );

function wppb_msf_init() {
    var forms = jQuery( "form.wppb-user-forms" );

    jQuery.each(forms, function( index, element ) {
        element = jQuery(element);
        var steps = jQuery( element ).find( "ul li.wppb-msf-step" );
        var count = steps.length;

        if( count > 1 ) {
            var form_id = element.attr( 'id' );

            jQuery( '#' + form_id ).on( 'keyup keypress', 'input:not([type="submit"]):not([type="button"])', function( e ) {
                var keyCode = e.keyCode || e.which;
                if( keyCode === 13 ) {
                    e.preventDefault();
                }
            } );

            steps.each( function( i ) {
                jQuery( this ).attr( 'id', 'wppb-msf-step-' + i ).attr( 'data-msf-step', i );
                jQuery( this ).wrapInner( "<fieldset>" );
            } );

            if( jQuery( element['selector'] + ' .wppb-field-error' ).length ) {
                jQuery( element['selector'] + ' .wppb-field-error' ).each( function() {
                    var error_step = jQuery( this ).closest( 'li.wppb-msf-step' ).attr( 'data-msf-step' );

                    jQuery( '#wppb-msf-tabs-' + error_step ).addClass( 'wppb-msf-commands-error' );
                    jQuery( '#wppb-msf-pagination-' + error_step ).addClass( 'wppb-msf-commands-error' );
                } );

                if( element.hasClass( 'wppb-register-user' ) ) {
                    var pass_error_step = jQuery( element['selector'] + ' .wppb-default-password' ).closest( 'li.wppb-msf-step' ).attr( 'data-msf-step' );
                    jQuery( '#wppb-msf-tabs-' + pass_error_step ).addClass( 'wppb-msf-commands-error' );
                    jQuery( '#wppb-msf-pagination-' + pass_error_step ).addClass( 'wppb-msf-commands-error' );

                    if( jQuery( element['selector'] + ' .wppb-default-repeat-password' ).length ) {
                        var pass_repeat_error_step = jQuery( element['selector'] + ' .wppb-default-repeat-password' ).closest( 'li.wppb-msf-step' ).attr( 'data-msf-step' );
                        jQuery( '#wppb-msf-tabs-' + pass_repeat_error_step, element ).addClass( 'wppb-msf-commands-error' );
                        jQuery( '#wppb-msf-pagination-' + pass_repeat_error_step, element ).addClass( 'wppb-msf-commands-error' );
                    }
                }

                jQuery( '.wppb-send-credentials-checkbox', element ).closest( 'ul' ).show();
                jQuery( '.form-submit', element ).show();
                jQuery( '.wppb-msf-tabs', element ).prop( 'disabled', false ).attr( 'data-msf-disabled-check', 'no' );
                jQuery( '.wppb-msf-pagination', element ).prop( 'disabled', false ).attr( 'data-msf-disabled-check', 'no' );

                if( ! jQuery( jQuery( '.wppb-field-error', element )[0] ).is( ':visible' ) ) {
                    jQuery( '#wppb-msf-step-0', element ).hide();
                    jQuery( jQuery( '.wppb-field-error', element )[0] ).closest( 'li.wppb-msf-step' ).show();
                    var step_number = jQuery( jQuery( '.wppb-field-error', element )[0] ).closest( 'li.wppb-msf-step' ).attr( 'data-msf-step' );

                    jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).removeClass( 'wppb-msf-active' );
                    jQuery( '#wppb-msf-pagination', element ).find( '#wppb-msf-pagination-' + step_number ).addClass( 'wppb-msf-active' );

                    jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).removeClass( 'wppb-msf-active' );
                    jQuery( '#wppb-msf-tabs', element ).find( '#wppb-msf-tabs-' + step_number ).addClass( 'wppb-msf-active' );

                    if( parseInt( step_number ) + 1 == count ) {
                        jQuery( '.wppb-msf-next', element ).prop( 'disabled', true );
                    }

                    jQuery( '.wppb-msf-prev', element ).prop( 'disabled', false );

                    wppb_msf_commands( step_number, count, element, form_id );
                } else {
                    wppb_msf_commands( 0, count, element, form_id );
                }
            } else {
                wppb_msf_commands( 0, count, element, form_id );
            }

            wppb_msf_tempRequired( element );
        }
    } );
}

function wppb_msf_commands( i, count, element, form_id ) {
    var form_type;
    /* make sure this is an integer */
    i = parseInt(i);

    if( element.hasClass( 'wppb-register-user' ) ) {
        form_type = 'register';
    } else if( element.hasClass( 'wppb-edit-user' ) ) {
        form_type = 'edit_profile';
    }

    jQuery( ".wppb-msf-prev", element ).on( "click", function( e ) {
        e.preventDefault();

        hideGeneralMessage();

        jQuery( '.wppb-msf-next', element ).prop( 'disabled', true );
        jQuery( '.wppb-msf-prev', element ).prop( 'disabled', true );
        jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).prop( 'disabled', true );
        jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).prop( 'disabled', true );

        var stepName = "wppb-msf-step-" + i;

        jQuery( '#' + form_id ).find( '.wppb-msf-spinner-container' )
            .css( 'height', jQuery( '#' + stepName ).outerHeight() + 'px' )
            .css( 'line-height', jQuery( '#' + stepName ).outerHeight() + 'px' );

        jQuery( "#" + stepName, element ).fadeOut( 'fast', function() {
            jQuery( '#' + form_id ).find( ".wppb-msf-spinner-container" ).fadeIn( 'fast', function() {
                if( i - 1 == 0 ) {
                    jQuery( '.wppb-msf-prev', element ).prop( 'disabled', true );
                }
                jQuery( '#' + form_id ).find( ".wppb-msf-spinner-container" ).fadeOut( 'fast', function() {
                    if( i < count ) {
                        jQuery( '.wppb-msf-next', element ).prop( 'disabled', false );
                    }

                    i--;

                    if( i != 0 ) {
                        jQuery( '.wppb-msf-prev', element ).prop( 'disabled', false );
                    }

                    jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).each( function() {
                        if( jQuery( this ).attr( 'data-msf-disabled-check' ) == 'no' ) {
                            jQuery( this ).prop( 'disabled', false );
                        }
                    } );

                    jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).each( function() {
                        if( jQuery( this ).attr( 'data-msf-disabled-check' ) == 'no' ) {
                            jQuery( this ).prop( 'disabled', false );
                        }
                    } );

                    jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).removeClass( 'wppb-msf-active' );
                    jQuery( '#wppb-msf-pagination', element ).find( '#wppb-msf-pagination-' + i ).addClass( 'wppb-msf-active' );

                    jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).removeClass( 'wppb-msf-active' );
                    jQuery( '#wppb-msf-tabs', element ).find( '#wppb-msf-tabs-' + i ).addClass( 'wppb-msf-active' );

                    jQuery( "#wppb-msf-step-" + i, element ).fadeIn( 'fast', function() {
                        if( typeof wppb_resize_maps !== 'undefined' ) {
                            wppb_resize_maps();
                        }
                    } );

                    wppb_msf_tempRequired( element );

                    jQuery(document).trigger('wppb_msf_previous_step')
                    
                } );
            } );
        } );

    } );


    jQuery( ".wppb-msf-next", element ).on( "click", function( e ) {
        e.preventDefault();

        hideGeneralMessage();

        jQuery( '.wppb-msf-next', element ).prop( 'disabled', true );
        jQuery( '.wppb-msf-prev', element ).prop( 'disabled', true );
        jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).prop( 'disabled', true );
        jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).prop( 'disabled', true );

        var stepName = "wppb-msf-step-" + i;

        jQuery( '#' + form_id ).find( '.wppb-msf-spinner-container' )
            .css( 'height', jQuery( '#' + stepName ).outerHeight() + 'px' )
            .css( 'line-height', jQuery( '#' + stepName ).outerHeight() + 'px' );

        var fields = {};
        var request_data = {};

        request_data = getRequestData( element );

        fields[stepName] = getFields( stepName, element );

        var data = {
            'action'				: 'wppb_msf_check_required_fields',
            'wppb_msf_ajax_nonce'   : wppb_msf_data_frontend.ajaxNonce,
            'wppb_msf_fields'       : fields,
            'request_data'          : request_data,
            'form_type'             : form_type
        };

        jQuery( "#" + stepName, element ).fadeOut( 'fast', function() {
            jQuery( '#' + form_id ).find( ".wppb-msf-spinner-container" ).fadeIn( 'fast', function() {
                jQuery.post( wppb_msf_data_frontend.ajaxUrl , data, function( response ) {
                    if( response.includes('no_errors') ) {
                        if( i + 2 == count ) {
                            jQuery( '.wppb-msf-next', element ).prop( 'disabled', true );
                        }

                        jQuery( '#' + stepName + ' fieldset li.wppb-form-field', element ).removeClass( 'wppb-field-error' ).children().remove( '.wppb-form-error' );

                        jQuery( '#' + form_id ).find( ".wppb-msf-spinner-container" ).fadeOut( 'fast', function() {
                            jQuery( "#" + stepName, element ).fadeOut( 'fast', function() {
                                jQuery( "#wppb-msf-step-" + ( i + 1 ), element ).fadeIn( 'fast' );

                                if( i + 2 == count && form_type != 'edit_profile' ) {
                                    jQuery( "p.form-submit", element ).fadeIn( 'fast' );
                                    jQuery( ".wppb-send-credentials-checkbox", element ).closest( 'ul' ).fadeIn( 'fast' );
                                }

                                if( typeof wppb_resize_maps !== 'undefined' ) {
                                    wppb_resize_maps();
                                }

                                jQuery( '#wppb-msf-tabs-' + i, element ).removeClass( 'wppb-msf-commands-error' );
                                jQuery( '#wppb-msf-pagination-' + i, element ).removeClass( 'wppb-msf-commands-error' );

                                i++;

                                if( i > 0 ) {
                                    jQuery( '.wppb-msf-prev', element ).prop( 'disabled', false );
                                }

                                if( i != count - 1 ) {
                                    jQuery( '.wppb-msf-next', element ).prop( 'disabled', false );
                                }

                                jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).removeClass( 'wppb-msf-active' );
                                jQuery( '#wppb-msf-pagination', element ).find( '#wppb-msf-pagination-' + i ).prop( 'disabled', false ).addClass( 'wppb-msf-active' ).attr( 'data-msf-disabled-check', 'no' );

                                jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).each( function() {
                                    if( jQuery( this ).attr( 'data-msf-disabled-check' ) == 'no' ) {
                                        jQuery( this ).prop( 'disabled', false );
                                    }
                                } );

                                jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).removeClass( 'wppb-msf-active' );
                                jQuery( '#wppb-msf-tabs', element ).find( '#wppb-msf-tabs-' + i ).prop( 'disabled', false ).addClass( 'wppb-msf-active' ).attr( 'data-msf-disabled-check', 'no' );

                                jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).each( function() {
                                    if( jQuery( this ).attr( 'data-msf-disabled-check' ) == 'no' ) {
                                        jQuery( this ).prop( 'disabled', false );
                                    }
                                } );

                                wppb_msf_tempRequired( element );

                                jQuery( 'html, body' ).animate( {
                                    scrollTop: jQuery( element ).offset().top - 150
                                }, 200 );

                                jQuery(document).trigger('wppb_msf_next_step')
                            } );
                        } );
                    } else {
                        jQuery( '#' + stepName + ' fieldset li.wppb-form-field', element ).removeClass( 'wppb-field-error' ).children().remove( '.wppb-form-error' );

                        var fields_errors = JSON.parse( response );

                        jQuery.each( fields_errors, function( stepName, errors ) {
                            jQuery.each(errors, function ( key, value ) {
                                var meta_name;

                                if( value['type'] !== undefined && value['type'] == 'woocommerce' ) {
                                    meta_name = jQuery( '#' + form_id ).find( '.wppb_' +  value['field'] );
                                } else {
                                    meta_name = jQuery( '#' + form_id ).find( '.wppb-' +  value['field'] );
                                }

                                if( meta_name.length > 1 ) {
                                    jQuery.each( meta_name, function( key2, value2 ) {
                                        if( jQuery( value2, element ).find( 'label' ).attr( 'for' ) == key ) {
                                            jQuery( jQuery( value2, element ) ).addClass( 'wppb-field-error' ).append( value['error'] );
                                        }
                                    } );
                                } else {
                                    meta_name.addClass( 'wppb-field-error' ).append( value['error'] );
                                }
                            } );
                        } );

                        jQuery( '#' + form_id ).find( ".wppb-msf-spinner-container" ).fadeOut( 'fast', function() {
                            jQuery( "#" + stepName, element ).fadeIn( 'fast' );

                            jQuery( '.wppb-msf-next', element ).prop( 'disabled', false );

                            if( i > 0 ) {
                                jQuery( '.wppb-msf-prev', element ).prop( 'disabled', false );
                            }

                            jQuery( '#wppb-msf-pagination', element ).find( '.wppb-msf-pagination' ).each( function() {
                                if( jQuery( this ).attr( 'data-msf-disabled-check' ) == 'no' ) {
                                    jQuery( this ).prop( 'disabled', false );
                                }
                            } );

                            jQuery( '#wppb-msf-tabs', element ).find( '.wppb-msf-tabs' ).each( function() {
                                if( jQuery( this ).attr( 'data-msf-disabled-check' ) == 'no' ) {
                                    jQuery( this ).prop( 'disabled', false );
                                }
                            } );

                            jQuery( 'html, body' ).animate( {
                                scrollTop: jQuery( '.wppb-field-error', element ).first().closest( 'li' ).offset().top - 100
                            }, 200 );
                        } );
                    }
                } );
            } );
        } );

    } );

    jQuery( ".wppb-msf-pagination", element ).on( "click", tabsAndPaginationHandler );

    jQuery( ".wppb-msf-tabs", element ).on( "click", tabsAndPaginationHandler );

    function tabsAndPaginationHandler( e ) {
        e.preventDefault();

        hideGeneralMessage();

        var page = jQuery(this);

        if (page.data('msf-step') != i) {
            var stepName = "wppb-msf-step-" + i;
            var pageNum = ( page.data( 'msf-step' ) + 1 );

            jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').removeClass('wppb-msf-active').prop('disabled', true);
            jQuery(page).addClass('wppb-msf-active');

            jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').removeClass('wppb-msf-active').prop('disabled', true);
            jQuery('#wppb-msf-tabs', element).find('#wppb-msf-tabs-' + page.data('msf-step')).addClass('wppb-msf-active');

            jQuery('.wppb-msf-next', element).prop('disabled', true);
            jQuery('.wppb-msf-prev', element).prop('disabled', true);

            jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').each(function () {
                jQuery(this).prop('disabled', true);
            });

            jQuery('#' + form_id).find('.wppb-msf-spinner-container')
                .css('height', jQuery('#' + stepName).outerHeight() + 'px')
                .css('line-height', jQuery('#' + stepName).outerHeight() + 'px');

            if (page.data('msf-step') > i) {
                var fields = {};
                var request_data = {};

                request_data = getRequestData(element);

                for (var j = 0; j < page.data('msf-step'); j++) {
                    fields["wppb-msf-step-" + j] = getFields("wppb-msf-step-" + j, element);
                }

                var data = {
                    'action': 'wppb_msf_check_required_fields',
                    'wppb_msf_ajax_nonce': wppb_msf_data_frontend.ajaxNonce,
                    'wppb_msf_fields': fields,
                    'request_data': request_data,
                    'form_type': form_type
                };

                jQuery("#" + stepName, element).fadeOut('fast', function () {
                    jQuery('#' + form_id).find(".wppb-msf-spinner-container").fadeIn('fast', function () {
                        jQuery.post(wppb_msf_data_frontend.ajaxUrl, data, function (response) {
                            if (response.includes('no_errors')) {
                                if (pageNum == count) {
                                    jQuery('.wppb-msf-next', element).prop('disabled', true);
                                }

                                jQuery('#' + stepName + ' fieldset li.wppb-form-field', element).removeClass('wppb-field-error').children().remove('.wppb-form-error');

                                jQuery('#' + form_id).find(".wppb-msf-spinner-container").fadeOut('fast', function () {
                                    jQuery("#" + stepName, element).fadeOut('fast', function () {
                                        i = page.data('msf-step');

                                        jQuery("#wppb-msf-step-" + i, element).fadeIn('fast');

                                        if (typeof wppb_resize_maps !== 'undefined') {
                                            wppb_resize_maps();
                                        }

                                        if (pageNum > 1) {
                                            jQuery('.wppb-msf-prev', element).prop('disabled', false);
                                        }

                                        if (pageNum != count) {
                                            jQuery('.wppb-msf-next', element).prop('disabled', false);
                                        }

                                        jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').removeClass('wppb-msf-active');
                                        jQuery('#wppb-msf-pagination', element).find('#wppb-msf-pagination-' + i).prop('disabled', false).addClass('wppb-msf-active').attr('data-msf-disabled-check', 'no');

                                        jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').each(function () {
                                            if (jQuery(this).attr('data-msf-disabled-check') == 'no') {
                                                jQuery(this).prop('disabled', false);
                                            }
                                        });

                                        jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').removeClass('wppb-msf-active');
                                        jQuery('#wppb-msf-tabs', element).find('#wppb-msf-tabs-' + i).prop('disabled', false).addClass('wppb-msf-active').attr('data-msf-disabled-check', 'no');

                                        jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').each(function () {
                                            if (jQuery(this).attr('data-msf-disabled-check') == 'no') {
                                                jQuery(this).prop('disabled', false);
                                            }
                                        });

                                        wppb_msf_tempRequired(element);
                                    });
                                });
                            } else {
                                jQuery('#' + stepName + ' fieldset li.wppb-form-field', element).removeClass('wppb-field-error').children().remove('.wppb-form-error');

                                var fields_errors = JSON.parse(response);
                                var stepNames = [];

                                jQuery.each(fields_errors, function (stepName, errors) {
                                    stepNames.push(stepName);

                                    jQuery.each(errors, function (key, value) {
                                        var meta_name;

                                        if (value['type'] !== undefined && value['type'] == 'woocommerce') {
                                            meta_name = jQuery('#' + form_id).find('.wppb_' + value['field']);
                                        } else {
                                            meta_name = jQuery('#' + form_id).find('.wppb-' + value['field']);
                                        }

                                        if (meta_name.length > 1) {
                                            jQuery.each(meta_name, function (key2, value2) {
                                                if (jQuery(value2, element).find('label').attr('for') == key) {
                                                    jQuery(jQuery(value2, element)).addClass('wppb-field-error').append(value['error']);
                                                }
                                            });
                                        } else {
                                            meta_name.addClass('wppb-field-error').append(value['error']);
                                        }
                                    });
                                });

                                jQuery('#' + form_id).find(".wppb-msf-spinner-container").fadeOut('fast', function () {
                                    i = jQuery('#' + stepNames[0]).attr('data-msf-step');

                                    jQuery("#" + stepNames[0], element).fadeIn('fast');

                                    if (typeof wppb_resize_maps !== 'undefined') {
                                        wppb_resize_maps();
                                    }

                                    if (i > 0) {
                                        jQuery('.wppb-msf-prev', element).prop('disabled', false);
                                    }

                                    if (i != count) {
                                        jQuery('.wppb-msf-next', element).prop('disabled', false);
                                    }

                                    jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').removeClass('wppb-msf-active');
                                    jQuery('#wppb-msf-pagination', element).find('#wppb-msf-pagination-' + i).prop('disabled', false).addClass('wppb-msf-active').attr('data-msf-disabled-check', 'no');

                                    jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').each(function () {
                                        if (jQuery(this).attr('data-msf-disabled-check') == 'no') {
                                            jQuery(this).prop('disabled', false);
                                        }
                                    });

                                    jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').removeClass('wppb-msf-active');
                                    jQuery('#wppb-msf-tabs', element).find('#wppb-msf-tabs-' + i).prop('disabled', false).addClass('wppb-msf-active').attr('data-msf-disabled-check', 'no');

                                    jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').each(function () {
                                        if (jQuery(this).attr('data-msf-disabled-check') == 'no') {
                                            jQuery(this).prop('disabled', false);
                                        }
                                    });

                                    jQuery('html, body').animate({
                                        scrollTop: jQuery('.wppb-field-error', element).first().closest('li').offset().top - 100
                                    }, 200);
                                });
                            }
                        });
                    });
                });
            } else {
                jQuery("#" + stepName, element).fadeOut('fast', function () {
                    jQuery('#' + form_id).find(".wppb-msf-spinner-container").fadeIn('fast', function () {
                        jQuery('#' + form_id).find(".wppb-msf-spinner-container").fadeOut('fast', function () {
                            jQuery("#" + stepName, element).fadeOut('fast', function () {
                                i = page.data('msf-step');

                                jQuery("#wppb-msf-step-" + i, element).fadeIn('fast');

                                if (typeof wppb_resize_maps !== 'undefined') {
                                    wppb_resize_maps();
                                }

                                if (pageNum > 1) {
                                    jQuery('.wppb-msf-prev', element).prop('disabled', false);
                                }

                                if (pageNum != count) {
                                    jQuery('.wppb-msf-next', element).prop('disabled', false);
                                }

                                jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').removeClass('wppb-msf-active');
                                jQuery('#wppb-msf-pagination', element).find('#wppb-msf-pagination-' + i).prop('disabled', false).addClass('wppb-msf-active').attr('data-msf-disabled-check', 'no');

                                jQuery('#wppb-msf-pagination', element).find('.wppb-msf-pagination').each(function () {
                                    if (jQuery(this).attr('data-msf-disabled-check') == 'no') {
                                        jQuery(this).prop('disabled', false);
                                    }
                                });

                                jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').removeClass('wppb-msf-active');
                                jQuery('#wppb-msf-tabs', element).find('#wppb-msf-tabs-' + i).prop('disabled', false).addClass('wppb-msf-active').attr('data-msf-disabled-check', 'no');

                                jQuery('#wppb-msf-tabs', element).find('.wppb-msf-tabs').each(function () {
                                    if (jQuery(this).attr('data-msf-disabled-check') == 'no') {
                                        jQuery(this).prop('disabled', false);
                                    }
                                });

                                wppb_msf_tempRequired(element);
                            });
                        });
                    });
                });
            }
        }
    }

    function getRequestData( element ) {
        var request_data = {};
        jQuery('.wppb-msf-step fieldset li.wppb-form-field', element).each(function (e) {
            if (jQuery(this).attr('class').indexOf('heading') == -1 && jQuery(this).attr('class').indexOf('wppb_billing') == -1
                && jQuery(this).attr('class').indexOf('wppb_shipping') == -1 && jQuery(this).attr('class').indexOf('wppb-shipping') == -1) {

                if (jQuery(this).hasClass('wppb-repeater') || jQuery(this).parent().attr('data-wppb-rpf-set') == 'template' || jQuery(this).hasClass('wppb-recaptcha')) {
                    return true;
                }

                /* exclude conditional required fields */
                if (jQuery(this).find('[conditional-value]').length !== 0) {
                    return true;
                }

                if (!jQuery(this).hasClass('wppb-wysiwyg') && !jQuery(this).hasClass('wppb-checkbox') && !jQuery(this).hasClass('wppb-checkbox-terms-and-conditions') && !jQuery(this).hasClass('wppb-radio')
                    && !jQuery(this).hasClass('wppb-map') && !jQuery(this).hasClass('wppb-woocommerce-customer-billing-address') && !jQuery(this).hasClass('wppb-woocommerce-customer-shipping-address')) {

                    if (jQuery(this).find('input').val() && jQuery(this).find('input').val().length != 0) {
                        request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input').val();
                    }
                }
                if (jQuery(this).hasClass('wppb-map') && jQuery(this).find('input.wppb-map-marker').length != 0) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input.wppb-map-marker').map(function () {
                        return this.value
                    }).get();
                }
                if ((jQuery(this).hasClass('wppb-default-biographical-info') || jQuery(this).hasClass('wppb-textarea')) && jQuery(this).find('textarea').val() && jQuery(this).find('textarea').val().length != 0) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('textarea').val();
                }
                if (jQuery(this).hasClass('wppb-wysiwyg')) {
                    if (jQuery('#wp-' + jQuery(this).find('label').attr('for') + '-wrap').hasClass('tmce-active') && tinyMCE.get(jQuery(this).find('label').attr('for'))) {
                        if (tinyMCE.get(jQuery(this).find('label').attr('for')).getContent().length != 0) {
                            request_data[jQuery(this).find('label').attr('for')] = tinyMCE.get(jQuery(this).find('label').attr('for')).getContent();
                        }
                    }
                }
                if ((jQuery(this).is('[class*="wppb-select"]') || jQuery(this).hasClass('wppb-select-user-role') || jQuery(this).hasClass('wppb-default-display-name-publicly-as')) && jQuery(this).find('option:selected').length != 0) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('option:selected').map(function () {
                        return this.value
                    }).get().join(',');
                }
                if ((jQuery(this).hasClass('wppb-select-multiple') || jQuery(this).hasClass('wppb-select2-multiple')) && jQuery(this).find('option:selected').length != 0) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('option:selected').map(function () {
                        return this.value
                    }).get();
                }
                if (jQuery(this).hasClass('wppb-checkbox') && jQuery(this).find('input:checked').length != 0) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input:checked').map(function () {
                        return this.value
                    }).get();
                }
                if (jQuery(this).hasClass('wppb-checkbox-terms-and-conditions') && jQuery(this).find('input:checked').length != 0) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input:checked').map(function () {
                        return this.value
                    }).get().join(',');
                }
                if (jQuery(this).hasClass('wppb-radio')) {
                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input:checked').val();
                }
                if (jQuery(this).hasClass('wppb-timepicker') && jQuery(this).find('option:selected').length != 0) {
                    var timepicker_values = {};
                    var timepicker_meta_name = jQuery(this).find('label').attr('for');
                    timepicker_meta_name = timepicker_meta_name.replace('-hour', '');
                    timepicker_values['hours'] = jQuery(this).find('#' + timepicker_meta_name + '-hour option:selected').val();
                    timepicker_values['minutes'] = jQuery(this).find('#' + timepicker_meta_name + '-minutes option:selected').val();
                    request_data[timepicker_meta_name] = timepicker_values;
                }
                if ((jQuery(this).hasClass('wppb-upload') || jQuery(this).hasClass('wppb-avatar')) && jQuery(this).find('input') && jQuery(this).find('input').length >= 3) {
                    if (!(jQuery(this).find('label').attr('for') in request_data)) {
                        name = jQuery(this).find('label').attr('for');
                        jQuery(this).find('input').each(function (i) {
                            if (jQuery(this).attr('id') == name.split('-').join('_')) {
                                request_data[name] = jQuery('#' + jQuery(this).attr('id'))[0]['value'];
                            }
                        });
                    }
                }
                if (!(jQuery(this).find('label').attr('for') in request_data) && !jQuery(this).hasClass('wppb-timepicker') && !jQuery(this).hasClass('wppb-checkbox')
                    && !jQuery(this).hasClass('wppb-woocommerce-customer-billing-address') && !jQuery(this).hasClass('wppb-woocommerce-customer-shipping-address')) {

                    request_data[jQuery(this).find('label').attr('for')] = '';
                }

                if (jQuery(this).hasClass('wppb-woocommerce-customer-billing-address')) {
                    jQuery('ul.wppb-woo-billing-fields li.wppb-form-field', element).each(function () {
                        if (!jQuery(this).hasClass('wppb_billing_heading')) {
                            if (!jQuery(this).hasClass('wppb_billing_country')) {
                                if (jQuery(this).find('input').val() && jQuery(this).find('input').val().length != 0) {
                                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input').val();
                                } else {
                                    request_data[jQuery(this).find('label').attr('for')] = '';
                                }
                            }

                            if ((jQuery(this).hasClass('wppb_billing_state') || jQuery(this).hasClass('wppb_billing_country')) && jQuery(this).find('option:selected').length != 0) {
                                request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('option:selected').map(function () {
                                    return this.value
                                }).get().join(',');
                            }
                        }
                    });
                }

                if (jQuery(this).hasClass('wppb-woocommerce-customer-shipping-address')) {
                    jQuery('ul.wppb-woo-shipping-fields li.wppb-form-field', element).each(function () {
                        if (!jQuery(this).hasClass('wppb_shipping_heading')) {
                            if (!jQuery(this).hasClass('wppb_shipping_country')) {
                                if (jQuery(this).find('input').val() && jQuery(this).find('input').val().length != 0) {
                                    request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('input').val();
                                } else {
                                    request_data[jQuery(this).find('label').attr('for')] = '';
                                }
                            }

                            if ((jQuery(this).hasClass('wppb_shipping_state') || jQuery(this).hasClass('wppb_shipping_country')) && jQuery(this).find('option:selected').length != 0) {
                                request_data[jQuery(this).find('label').attr('for')] = jQuery(this).find('option:selected').map(function () {
                                    return this.value
                                }).get().join(',');
                            }
                        }
                    });
                }
            }
        });

        return request_data;
    }

    function getFields( stepName, element ) {
        var fields = {};
        jQuery('#' + stepName + ' fieldset li.wppb-form-field', element).each(function () {
            if (jQuery(this).attr('class').indexOf('heading') == -1 && jQuery(this).attr('class').indexOf('wppb_billing') == -1
                && jQuery(this).attr('class').indexOf('wppb_shipping') == -1 && jQuery(this).attr('class').indexOf('wppb-shipping') == -1) {

                var meta_name;

                if (jQuery(this).hasClass('wppb-repeater') || jQuery(this).parent().attr('data-wppb-rpf-set') == 'template' || jQuery(this).hasClass('wppb-recaptcha')) {
                    return true;
                }

                /* exclude conditional required fields */
                if (jQuery(this).find('[conditional-value]').length !== 0) {
                    return true;
                }

                fields[jQuery(this).attr('id')] = {};
                fields[jQuery(this).attr('id')]['class'] = jQuery(this).attr('class');

                if (jQuery(this).hasClass('wppb-woocommerce-customer-billing-address')) {
                    meta_name = 'woocommerce-customer-billing-address';
                } else if (jQuery(this).hasClass('wppb-woocommerce-customer-shipping-address')) {
                    meta_name = 'woocommerce-customer-shipping-address';

                    if (!jQuery('#' + form_id + ' .wppb-woocommerce-customer-billing-address #woo_different_shipping_address').is(':checked')) {
                        return true;
                    }
                } else {
                    meta_name = jQuery(this).find('label').attr('for');

                    //fields[jQuery( this ).attr( 'id' )]['required'] = jQuery( this ).find( 'label' ).find( 'span' ).attr( 'class' );
                    fields[jQuery(this).attr('id')]['title'] = jQuery(this).find('label').first().text().trim();
                }

                fields[jQuery(this).attr('id')]['meta-name'] = meta_name;

                if (jQuery(this).parent().parent().attr('data-wppb-rpf-meta-name')) {
                    var repeater_group = jQuery(this).parent().parent();

                    fields[jQuery(this).attr('id')]['extra_groups_count'] = jQuery(repeater_group).find('#' + jQuery(repeater_group).attr('data-wppb-rpf-meta-name') + '_extra_groups_count').val();
                }

                if (jQuery(this).hasClass('wppb-woocommerce-customer-billing-address')) {
                    var woo_billing_fields_fields = {};

                    jQuery('ul.wppb-woo-billing-fields li.wppb-form-field', element).each(function () {
                        if (!jQuery(this).hasClass('wppb_billing_heading')) {
                            woo_billing_fields_fields[jQuery(this).find('label').attr('for')] = jQuery(this).find('label').text();
                        }
                    });

                    fields[jQuery(this).attr('id')]['fields'] = woo_billing_fields_fields;
                }

                if (jQuery(this).hasClass('wppb-woocommerce-customer-shipping-address')) {
                    var woo_shipping_fields_fields = {};

                    jQuery('ul.wppb-woo-shipping-fields li.wppb-form-field', element).each(function () {
                        if (!jQuery(this).hasClass('wppb_shipping_heading')) {
                            woo_shipping_fields_fields[jQuery(this).find('label').attr('for')] = jQuery(this).find('label').text();
                        }
                    });

                    fields[jQuery(this).attr('id')]['fields'] = woo_shipping_fields_fields;
                }
            }
        });

        return fields;
    }

    function hideGeneralMessage(){
        jQuery( "#wppb_form_general_message.wppb-success" ).fadeOut( 'fast' );
    }
}

function wppb_msf_tempRequired( element ) {
    jQuery( element['selector'] + ' .wppb-form-field:hidden' ).find( '[required]' ).each( function() {
        if( jQuery( this ).closest( 'ul' ).attr( 'data-wppb-rpf-set' ) != 'template' ) {
            jQuery( this ).removeAttr( 'required' ).attr( 'wppb-msf-temp-required', '' );
        }
    } );

    jQuery( element['selector'] + ' .wppb-form-field:visible' ).find( '[wppb-msf-temp-required]' ).each( function() {
        if( jQuery( this ).closest( 'ul' ).attr( 'data-wppb-rpf-set' ) != 'template' ) {
            jQuery( this ).removeAttr( 'wppb-msf-temp-required' ).attr( 'required', '' );
        }
    } );
}
