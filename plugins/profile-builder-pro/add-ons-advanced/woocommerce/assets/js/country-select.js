jQuery( function( $ ) {

    /* State/Country select boxes */
    var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
        states = JSON.parse( states_json),
        locale_json = wc_country_select_params.locale;
        
        if( typeof locale_json != 'undefined' && locale_json != '' ){
            var locale = JSON.parse( locale_json );
        }

    $( 'body' ).on( 'change', 'select.country_to_state', function() {

        var country = $( this ).val(),
            $statebox = $( this ).closest( 'ul' ).find( '#billing_state, #shipping_state' ),
            input_name = $statebox.attr( 'name' ),
            input_id = $statebox.attr( 'id');
            $statebox.val("");

        if( typeof input_name != "undefined") {//this was added for compatibility with conditional logic. Our hide show function trigger a change event and since we might not have a name attribute but a condifional-name attribute input_name could be undefined

            if ($('.wppb-required', $statebox.parent()).length > 0) {
                $('.wppb-required', $statebox.parent()).show();
            } else {
                $('label', $statebox.parent()).append('<span class="wppb-required" title="This field is required">*</span>');
                $('.wppb-required', $statebox.parent()).show();
            }

            if (states[country]) {    //we have states in the selected country

                if (!($.isEmptyObject(states[country]))) {

                    var options = '',
                        state = states[country];

                    for (var index in state) {
                        if (state.hasOwnProperty(index)) {
                            options = options + '<option value="' + index + '">' + state[index] + '</option>';
                        }
                    }

                    if ($statebox.is('input')) {
                        // Change for select
                        $statebox.replaceWith('<select name="' + input_name + '" id="' + input_id + '" class="custom_field_state_select" value=""></select>');
                        $statebox = $(this).closest('ul').find('#billing_state, #shipping_state');
                    }

                    $statebox.html('<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options);

                    $statebox.parent().show();

                } else {  // states array empty for this country; this means we need to hide the states field to mimic WooCommerce behaviour
                    $statebox.parent().hide();
                }

            } else {  //states[country] is not defined

                $statebox.parent().show();
                // Remove asterisk if State field is not required
                if (locale && (typeof (locale[country]) != 'undefined')) {

                    if (typeof (locale[country].state) != 'undefined') {

                        if ((typeof (locale[country].state.required) != 'undefined') && (locale[country].state.required == false)) {

                            $('.wppb-required', $statebox.parent()).hide();
                        }

                    }

                }

                $statebox.replaceWith('<input type="text" class="extra_field_input" name="' + input_name + '" id="' + input_id + '" value="" />');

            }
        }
    });
});
