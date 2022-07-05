jQuery(function(){
	//hover states on the static widgets
    jQuery('#dialog_link, ul#icons li').on('mouseenter', function() { jQuery(this).addClass('ui-state-hover'); });
    jQuery('#dialog_link, ul#icons li').on('mouseleave', function() { jQuery(this).removeClass('ui-state-hover'); });
});

/* initialize datepicker */
jQuery(wppb_initialize_datepicker);

function wppb_initialize_datepicker(){
    // Datepicker
    jQuery('.custom_field_datepicker').each( function(){
        var currentDatepicker = this;
        jQuery( currentDatepicker ).datepicker({
            inline: true,
            changeMonth: true,
            changeYear: true,
            yearRange: 'c-100:c+30',
            dateFormat: jQuery( currentDatepicker ).data('dateformat')
        });

        //js validation of entered date format
        var pbDpPreviousDate;
        jQuery( currentDatepicker ).on( 'focus', function(){
            previousDate= jQuery(this).val(); ;
        });

        jQuery( currentDatepicker ).on( 'blur', function(){
            try {
                var dateParse = jQuery.datepicker.parseDate(  jQuery( currentDatepicker ).data('dateformat') , jQuery(this).val());
            } catch (e) {}
            if (!dateParse) {
                jQuery(this).val(pbDpPreviousDate);
            }
        });
    });
}