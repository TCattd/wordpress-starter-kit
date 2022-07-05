
/*
 * Function that opens the settings options for a list in the settings page
 *
 */
jQuery(document).on( 'click', '.wppb-mci-list .row-actions .edit a', function(e) {
    e.preventDefault();

    jQuery('#wppb-mci-list-table .wppb-mci-list-settings').hide();
    jQuery(this).parents('tr').next().show();
    jQuery(this).blur();

    jQuery('html, body').animate({
        scrollTop: jQuery(this).parents('tr').offset().top
    }, 400)
});


/*
 * Function that closes the settings options for a list in the settings page
 *
 */
jQuery(document).on( 'click', '.wppb-mci-list-settings-cancel', function(e) {
    e.preventDefault();

    jQuery(this).parents('.wppb-mci-list-settings').hide();
});


/*
 * Function that hides the send welcome e-mail feature from the settings area
 *
 */
jQuery(document).on( 'click', '.wppb-mci-list-setting-double-opt-in input[type="checkbox"], .wppb-mci-widget-setting-double-opt-in input[type="checkbox"]', function() {
    $this = jQuery(this);

    if( $this.parent().prop('tagName') == 'LABEL' )
        $parent = $this.parent().parent();
    else
        $parent = $this.parent();

    if( $this.is(':checked') )
        $parent.siblings('div').addClass('hidden');
    else
        $parent.siblings('div').removeClass('hidden');
});


/*
 * Function makes an ajax call to retreive the merge var fields for a list
 *
 */
jQuery(document).on('change', '#wppb_mci_widget_list_select', function(e) {
    var list_id = jQuery(this).val();
    var data_number = jQuery(this).attr('data-number');

    $container = jQuery('.wppb_mci_widget_list_fields[data-number="' + data_number + '"]');

    $container.html('');
    $container.append( '<div class="spinner"></div>' );
    $container.find('.spinner').css('float', 'left').css('display', 'block').css('opacity', 0).animate({
        opacity: 1
    }, 200);
    $container.parents('form').find('input[type=submit]').attr('disabled', true);

    jQuery.post( ajaxurl, { action: 'display_list_fields', wppb_mci_list_id: list_id, wppb_widget_data_number: data_number }, function( response ) {

        $container.html( response );
        $container.find('.spinner').remove();
        $container.parents('form').find('input[type=submit]').attr('disabled', false);

    });
});
jQuery(document).on( 'html', '.wppb_mci_widget_list_fields', function() {});

/**
 * Function that adds the MailChimp field to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function wppb_mci_add_field() {
    if (typeof fields == "undefined") {
        return false;
    }

    fields["MailChimp Subscribe"] = {
        'show_rows'	:	[
            '.row-field-title',
            '.row-field',
            '.row-mailchimp-lists',
            '.row-mailchimp-hide-field',
            '.row-mailchimp-default-checked'
        ],
        'properties':	{
            'meta_name_value' : ''
        }
    };
}

jQuery( function() {
    wppb_mci_add_field();
});