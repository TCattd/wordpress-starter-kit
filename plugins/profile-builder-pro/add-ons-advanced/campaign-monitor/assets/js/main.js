
/*
 * Function that adds the field count for each list
 *
 */
jQuery(document).on( 'add_fields_count', '#wppb-cmi-page-clients-settings-wrapper', function() {
    $this = jQuery(this);

    if( $this.find('.wppb-cmi-list-settings').length > 0 ) {
        $this.find('.wppb-cmi-list-settings').each( function() {
            $listSettings = jQuery(this);

            var fieldCount = $listSettings.find('.wppb-cmi-list-field-wrapper').length;

            $listSettings.prev().find('.wppb-cmi-fields-count').html( fieldCount );
        });

    }
});


/*
 * Function that loads the table with the list of client lists based on the selected client
 *
 */
jQuery(document).on( 'change', '#wppb-cmi-page-client-selector-select', function() {

    $this = jQuery(this);

    // Get the client id from the select input
    var clientID = $this.val();

    // If client id is not empty make an ajax call to refresh / add the table
    if( clientID != '' ) {

        // Add the spinner
        $this.after('<div class="spinner">');
        $this.parent().find('.spinner').css('float', 'none').css('display', 'inline-block').css('opacity', 0).animate({
            opacity: 1
        }, 200);

        jQuery.post( ajaxurl, { action: 'wppb_cmi_get_page_client_lists_table_content', wppb_cmi_client_id: clientID }, function( response ) {

            // Change html of the wrapper
            jQuery('#wppb-cmi-page-clients-settings-wrapper').html( response ).trigger('add_fields_count');

            // Remove the spinner
            $this.parent().find('.spinner').remove();

            jQuery('#wppb-cmi-page-client-selector').hide();
        });
    }
});


/*
 * Function that shows / hides the client selector drop-down when clicking on the
 * change client button
 *
 */
jQuery(document).on( 'click', '#wppb-cmi-change-client-btn', function(e) {
    e.preventDefault();

    $pageClientSelector = jQuery('#wppb-cmi-page-client-selector');

    if( !$pageClientSelector.is(':visible') ) {
        $pageClientSelector.slideDown( 250, 'linear' );
    } else {
        $pageClientSelector.slideUp( 250, 'linear' );
    }

});


/*
 * Function that loads the table with the list of client lists after an ajax call
 * is made that gets data from Campaign Monitor and compares it with the values saved in
 * the settings option
 *
 */
jQuery(document).on( 'click', '#wppb-cmi-sync-client-btn', function(e) {
    e.preventDefault();

    $this = jQuery(this);

    // Get the client id from the hidden input
    var clientID = $this.siblings('.wppb-cmi-client-id').val();

    // If client id is not empty make an ajax call to refresh the table
    if( clientID != 'undefined' ) {

        $pageClientWrapper = jQuery('#wppb-cmi-page-clients-settings-wrapper');

        // Add an overlay over the existing table
        $pageClientWrapper.find('td').append('<div class="wppb-cmi-overlay"></div>');
        $pageClientWrapper.find('.wppb-cmi-overlay').animate({
            opacity: 0.65
        }, 200 );

        // Add the spinner
        $this.after('<div class="spinner">');
        $this.parent().find('.spinner').css('float', 'right').css('display', 'inline-block').css('opacity', 0).animate({
            opacity: 1
        }, 200);

        // Disable the save button
        jQuery('#wppb-cmi-page-submit').attr('disabled', true );

        // Get the index of the opened settings tab if there is one
        var openedSettingsRowIndex = $pageClientWrapper.find('.wppb-cmi-list-settings').not('.hidden').index('.wppb-cmi-list-settings');

        jQuery.post( ajaxurl, { action: 'wppb_cmi_get_page_client_lists_table_content', wppb_cmi_client_id: clientID }, function( response ) {

            // Change the html of the wrapper
            $pageClientWrapper.html( response ).trigger('add_fields_count');

            // Remove the spinner
            $this.parent().find('.spinner').remove();

            // Reopen the settings tab that was opened
            if( openedSettingsRowIndex != -1 )
                $pageClientWrapper.find('.wppb-cmi-list-settings').eq( openedSettingsRowIndex ).removeClass('hidden');


            // Enable the submit button
            jQuery('#wppb-cmi-page-submit').attr('disabled', false );
        });

    }

});


/*
 * Function that opens the settings options for a list in the settings page
 *
 */
jQuery(document).on( 'click', '.wppb-cmi-list-edit', function(e) {
    e.preventDefault();

    jQuery('#wppb-cmi-page-clients-settings-wrapper .wppb-cmi-list-settings').addClass('hidden');
    jQuery(this).parents('tr').next().removeClass('hidden');
    jQuery(this).blur();

    jQuery('html, body').animate({
        scrollTop: jQuery(this).parents('tr').offset().top
    }, 400)
});


/*
 * Function that closes the settings options for a list in the settings page
 *
 */
jQuery(document).on( 'click', '.wppb-cmi-list-settings-cancel', function(e) {
    e.preventDefault();

    jQuery(this).parents('.wppb-cmi-list-settings').addClass('hidden');
});


/**
 * Function that adds the Campaign Monitor field to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function wppb_cmi_add_field() {
    if (typeof fields == "undefined") {
        return false;
    }

    fields["Campaign Monitor Subscribe"] = {
        'show_rows'	:	[
            '.row-field-title',
            '.row-field',
            '.row-campaign-monitor-lists',
            '.row-campaign-monitor-hide-field'
        ],
        'properties':	{
            'meta_name_value' : ''
        }
    };
}

jQuery( function() {
    wppb_cmi_add_field();
});


/*
 * Function that adds the field count for each list on document ready
 *
 */
jQuery(document).ready( function() {
    jQuery('#wppb-cmi-page-clients-settings-wrapper').trigger('add_fields_count');
});


/*
 * Function that makes an ajax call to populate the client selector
 *
 */
jQuery(document).ready( function() {

    $pageClientSelect = jQuery('#wppb-cmi-page-client-selector-select');

    // Get number of options in the list
    var pageClientSelectOptionCount = $pageClientSelect.find('option').length;

    // Repopulate only if there is one option
    if( pageClientSelectOptionCount == 1 ) {

        // Disable the drop-down
        $pageClientSelect.attr('disabled', true );

        // Add the spinner
        $pageClientSelect.after('<div class="spinner">');
        $pageClientSelect.parent().find('.spinner').css('float', 'none').css('display', 'inline-block').css('opacity', 0).animate({
            opacity: 1
        }, 200);

        jQuery.post( ajaxurl, { action: 'wppb_cmi_page_client_selector_options_content' }, function( response ) {

            $pageClientSelect.html( response );
            $pageClientSelect.attr('disabled', false );

            // Remove the spinner
            $pageClientSelect.parent().find('.spinner').remove();

        });
    }

});