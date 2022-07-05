/**
 * Function that adds the GDPR CP field to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function wppb_gdprcp_add_field() {
    if (typeof fields == "undefined") {
        return false;
    }
    fields["GDPR Communication Preferences"] = {
        'show_rows'	:	[
            '.row-field-title',
            '.row-meta-name',
            '.row-description',
            '.row-gdpr-communication-preferences',
            '.row-required',
            '.row-overwrite-existing'
        ],
        'properties':	{
            'meta_name_value'	: 'gdpr_communication_preferences'
        }
    };
}


jQuery( function() {
    wppb_gdprcp_add_field();

    // we need run this again after adding the Email Confirmation field to the global fields object
    wppb_hide_properties_for_already_added_fields( '#container_wppb_manage_fields' );

    wppb_handle_gdprcp_field( '#wppb_manage_fields' );
});

/*
 * Function that handles the sorting of the user roles from the Select (User Role)
 * extra field
 *
 */
function wppb_handle_gdprcp_field( container_name ) {

    jQuery( container_name + ' ' + '.row-gdpr-communication-preferences .wck-checkboxes').sortable({

        //Assign a custom handle for the drag and drop
        handle: '.sortable-handle',

        create: function( event, ui ) {

            //Add the custom handle for drag and drop
            jQuery(this).find('div').each( function() {
                jQuery(this).prepend('<span class="sortable-handle"></span>');
            });

            $sortOrderInput = jQuery(this).parents('.row-gdpr-communication-preferences').siblings('.row-gdpr-communication-preferences-sort-order').find('input[type=text]');

            if( $sortOrderInput.val() == '' ) {
                jQuery(this).find('input[type=checkbox]').each( function() {
                    $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
                });
            } else {
                sortOrderElements = $sortOrderInput.val().split(', ');
                sortOrderElements.shift();

                for( var i=0; i < sortOrderElements.length; i++ ) {
                    jQuery( container_name + ' ' + '.row-gdpr-communication-preferences .wck-checkboxes').append( jQuery( container_name + ' ' + '.row-gdpr-communication-preferences .wck-checkboxes input[value="' + sortOrderElements[i] + '"]').parent().parent().get(0) );
                }
            }
        },

        update: function( event, ui ) {
            $sortOrderInput = ui.item.parents('.row-gdpr-communication-preferences').siblings('.row-gdpr-communication-preferences-sort-order').find('input[type=text]');
            $sortOrderInput.val('');

            ui.item.parent().find('input[type=checkbox]').each( function() {
                $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
            });
        }
    });
}
