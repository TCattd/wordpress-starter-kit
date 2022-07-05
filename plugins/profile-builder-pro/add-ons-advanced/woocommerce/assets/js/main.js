/**
 * Function that adds the WooCommerce Billing and Shipping fields to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function addBillingShippingFields() {
    if (typeof fields == "undefined") {
        return false;
    }

    fields["WooCommerce Customer Billing Address"] = {
    	'show_rows'	:	[
            '.row-field-title',
            '.row-field',
            '.row-woo-billing-fields',
            '.row-woo-billing-fields-required',
            '.row-conditional-logic-enabled'
        ],
        'properties':	{
            'meta_name_value'	: 'wppbwoo_billing'
        }
    };

    fields["WooCommerce Customer Shipping Address"] = {
        'show_rows'	:	[
            '.row-field-title',
            '.row-field',
            '.row-woo-shipping-fields',
            '.row-woo-shipping-fields-required',
            '.row-conditional-logic-enabled'
        ],
        'properties':	{
            'meta_name_value'	: 'wppbwoo_shipping'
        }
    };
}

/*
* Function that chooses which custom fields from PB appear on WooCommerce Checkout page
*
 */
function WooCheckoutFields() {
    if (typeof fields == "undefined") {
        return false;
    }

    var checkoutFields = [
        'Default - First Name',
        'Default - Last Name',
        'Default - Nickname',
        'Default - Biographical Info',
        'Default - Website',
        'Default - About Yourself (Heading)',
        'Input',
        'Input (Hidden)',
        'Textarea',
        'Checkbox',
        'Checkbox (Terms and Conditions)',
        'Select' ,
        'Radio',
        'Heading',
        'Datepicker',
        'Phone',
        'Number',
        'Avatar',
        'Upload',
        'MailChimp Subscribe',
        'Select (Multiple)',
        'WYSIWYG',
        'Select (Country)',
        'Select (Timezone)',
        'Select (Currency)',
        'Select (CPT)',
        'Select (User Role)',
        'Timepicker',
        'Colorpicker',
        'Map',
        'HTML',
        'Repeater',
        'Validation'
    ];

    for( var i = 0; i < checkoutFields.length; i++ ) {
        if( fields[ checkoutFields[i] ] != undefined )
            fields[ checkoutFields[i] ]['show_rows'].push( '.row-woocommerce-checkout-field' );
    }
}

/*
 * Function that handles the sorting of the individual Shipping and Billing fields
 *
 */
function wppb_handle_woosync_billing_shipping_field( container_name , type ) {

    jQuery( container_name + ' ' + '.row-woo-' + type + '-fields .wck-checkboxes').sortable({

        //Assign a custom handle for the drag and drop
        handle: '.sortable-handle',

        create: function( event, ui ) {

            //Add the custom handle for drag and drop
            jQuery(this).find('div').each( function() {
                jQuery(this).prepend('<span class="sortable-handle"></span>');
            });

            $sortOrderInput = jQuery(this).parents('.row-woo-' + type + '-fields').siblings('.row-woo-' + type + '-fields-sort-order').find('input[type=text]');

            if( $sortOrderInput.val() == '' ) {
                jQuery(this).find('input[type=checkbox]').each( function() {
                    $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
                });
            } else {
                sortOrderElements = $sortOrderInput.val().split(', ');
                sortOrderElements.shift();

                for( var i=0; i < sortOrderElements.length; i++ ) {

                    /*
                     * As we have, in the hidden field, values saved for both the checkboxes representing the field and also for the one representing
                     * if the field is required, ( eg. shipping_country, required_shipping_country ), and given that the parent for these checkboxes is the same,
                     * we do not need to append their parent element twice into the container element, thus we skip all values that start with "required_"
                     *
                     */
                    if( sortOrderElements[i].indexOf('required_') == -1 ) {

                        var parent   = jQuery( container_name + ' ' + '.row-woo-' + type + '-fields .wck-checkboxes input[value="' + sortOrderElements[i] + '"]').parent().parent();
                        var toAppend = parent.get(0);

                        parent.remove();

                        jQuery( container_name + ' ' + '.row-woo-' + type + '-fields .wck-checkboxes').append( toAppend );

                    }
                }
            }

        },

        update: function( event, ui ) {
            $sortOrderInput = ui.item.parents('.row-woo-' + type + '-fields').siblings('.row-woo-' + type + '-fields-sort-order').find('input[type=text]');
            $sortOrderInput.val('');

            ui.item.parent().find('input[type=checkbox]').each( function() {
                $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
            });
        }
    });
}

/*
 *
 * Function that handles the saving of Individual Shipping and Billing field names edited by the user in the "woo-billing-fields-name" / "woo-shipping-fields-name" hidden inputs
 *
 */
function wppb_woo_save_individual_field_names( type ) {

    jQuery(document).on('change', '.row-woo-' + type + '-fields .wck-woocheckbox-field-label', function () {

        $fieldsNamesInput = jQuery(this).closest('li').siblings('.row-woo-' + type + '-fields-name').find('input[type=text]');

        var array = {};

        jQuery(this).closest('.wck-checkboxes').find('label:first-of-type input[type=checkbox]').each(function () {

            var field_name = jQuery(this).siblings('input[type=text]').val().trim();

            if (field_name != '')
                array[jQuery(this).val()] = field_name;

        });

        $fieldsNamesInput.attr( 'value', JSON.stringify(array) );

    });
}


/*
*
* Function that handles the "checked" / "unchecked" / "disabled" correlation between fields (e.g disable "required" when "field name" is unchecked)
*
*/
function wppb_woo_manage_checked_correlation(){

    jQuery(document).on('click', '.wck-woocheckboxes div label:first-of-type input[type=checkbox]', function(){
        if ( !jQuery(this).is(':checked') ) {
            jQuery(this).parent().next().find('input').attr('disabled',true);

            if( jQuery(this).attr('id') == 'woo-billing-fields_billing_country' ) {

                var $elem = jQuery(this).closest('.wck-woocheckboxes').find('input[id=woo-billing-fields_billing_state]');

                if( $elem.is(':checked') )
                    $elem.trigger('click');

            }

            if( jQuery(this).attr('id') == 'woo-shipping-fields_shipping_country' ) {

                var $elem = jQuery(this).closest('.wck-woocheckboxes').find('input[id=woo-shipping-fields_shipping_state]');

                if( $elem.is(':checked') )
                    $elem.trigger('click');

            }

        } else {
            jQuery(this).parent().next().find('input').attr('disabled',false);
        }
    });

}


// execute this after all the document.ready calls have finalized. This means we should have all the fields added in the field js variable.
jQuery(window).on('load', function() {
    addBillingShippingFields();
    WooCheckoutFields();

    // Save individual field names edited by the user for WooCommerce Billing and Shipping fields
    wppb_woo_save_individual_field_names('billing');
    wppb_woo_save_individual_field_names('shipping');

    // we need run this again after adding the Billing and Shipping fields to the global fields object
    wppb_hide_properties_for_already_added_fields( '#container_wppb_manage_fields' );

    jQuery(document).on( 'change', '#wppb_manage_fields .mb-list-entry-fields #field', function () {
            field = jQuery(this).val();
            if ( field == 'WooCommerce Customer Billing Address'){
                wppb_handle_woosync_billing_shipping_field ( '#wppb_manage_fields', 'billing' );
            }
        else if ( field == 'WooCommerce Customer Shipping Address'){
                wppb_handle_woosync_billing_shipping_field ( '#wppb_manage_fields', 'shipping' );
            }
        });

    jQuery(document).on( 'change', '#container_wppb_manage_fields .mb-list-entry-fields #field', function () {
        field = jQuery(this).val();
        if ( field == 'WooCommerce Customer Billing Address'){
            wppb_handle_woosync_billing_shipping_field ( '#container_wppb_manage_fields', 'billing' );
        }
        else if ( field == 'WooCommerce Customer Shipping Address'){
            wppb_handle_woosync_billing_shipping_field ( '#container_wppb_manage_fields', 'shipping' );
        }
    });

    wppb_woo_manage_checked_correlation();


});