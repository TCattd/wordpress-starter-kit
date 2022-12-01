(function (GF_Partial_Entries, $) {

    "use strict";
    $(document).ready(function(){
        $('.partial_entry_id').each( function(el) {
            var $this = $(this),
                formId = $this.data('form_id'),
                $heading = $('#gform_wrapper_' + formId + ' .gform_heading'),
                strings = typeof window['gf_partial_entries_strings_' + formId] !== 'undefined' ? window['gf_partial_entries_strings_' + formId] : {},
                warning = typeof strings.warningMessage !== 'undefined' ? strings.warningMessage : '',
                notice = warning ? '<div class="partial_entry_warning" style="margin-bottom: 10px;">' + warning + '</div>' : '',
                $anchorElement = $heading.length > 0 ? $heading : $this;
            if ( notice ) {
                $anchorElement. after( notice );
            }

        });
    });

    $(document).on('heartbeat-send', function(e, data) {

        var $forms = $('.gform_wrapper form');

        if ( $forms.length == 0 ) {
            return;
        }
        var formsData = {};
        $forms.each( function( i ) {
            var formCopy = $(this).clone(), formData;
            var $gformSubmit = formCopy.find('input[name=gform_submit]');
            var formId = $gformSubmit.val();
            $gformSubmit.remove();
            formData = formCopy.serializeArray();

            formsData[formId] = formData;
        });

        var formsJson = JSON.stringify( formsData );

        data['gf-partial_entries-heartbeat'] = formsJson;
    });


    $(document).on( 'heartbeat-tick', function(e, data) {

        if ( ! data['gf-partial-entries-ids'] ) {
            return;
        }

        $.each( data['gf-partial-entries-ids'], function ( formId, entryId ) {
            $('#partial_entry_id_' + formId ).val( entryId );
        });

    });

    // Textarea and select clone() bug workaround | Spencer Tipping
    // Licensed under the terms of the MIT source code license
    // Source: https://raw.githubusercontent.com/spencertipping/jquery.fix.clone/master/jquery.fix.clone.js
    (function (original) {
        jQuery.fn.clone = function () {
            var result           = original.apply(this, arguments),
                my_textareas     = this.find('textarea').add(this.filter('textarea')),
                result_textareas = result.find('textarea').add(result.filter('textarea')),
                my_selects       = this.find('select').add(this.filter('select')),
                result_selects   = result.find('select').add(result.filter('select'));

            for (var i = 0, l = my_textareas.length; i < l; ++i) $(result_textareas[i]).val($(my_textareas[i]).val());
            for (var i = 0, l = my_selects.length;   i < l; ++i) {
                for (var j = 0, m = my_selects[i].options.length; j < m; ++j) {
                    if (my_selects[i].options[j].selected === true) {
                        result_selects[i].options[j].selected = true;
                    }
                }
            }
            return result;
        };
    }) (jQuery.fn.clone);


}(window.GF_Partial_Entries = window.GF_Partial_Entries || {}, jQuery));
