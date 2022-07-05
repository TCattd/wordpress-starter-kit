/**
 * Function that adds the visibility and user role visibility field properties to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function updateEditProfileApproval() {
    if (typeof fields == "undefined") {
        return false;
    }

    //The fields we want the new properties to show - they must match the ones in index.php
    var excludeFields = [ 'Default - Name (Heading)', 'Default - Contact Info (Heading)', 'Default - About Yourself (Heading)', 'Default - Username', 'Default - Password', 'Default - Repeat Password', 'Default - Display name publicly as',
        'Checkbox (Terms and Conditions)', 'Input (Hidden)', 'Heading', 'HTML', 'Repeater', 'Validation', 'GDPR Checkbox', 'Subscription Plans',
        'GDPR Communication Preferences', 'MailChimp Subscribe', 'reCAPTCHA', 'Campaign Monitor Subscribe', 'MailPoet Subscribe', 'WooCommerce Customer Billing Address',
        'WooCommerce Customer Shipping Address' ];


    for (var key in fields){
        if( !( excludeFields.indexOf(key) > -1 ) ){
            if (typeof fields[ key ] != "undefined" ){
                fields[ key ]['show_rows'].push( '.row-edit-profile-approved-by-admin' );
            }
        }
    }

}

function updateEPAAFieldsToShow() {
    if( typeof fields_to_show !== "undefined" ) {
        fields_to_show.push('.row-edit-profile-approved-by-admin');
    }
}

jQuery(window).load( function() {
    updateEditProfileApproval();
    updateEPAAFieldsToShow();
    
    if( typeof wppb_hide_properties_for_already_added_fields !== "undefined" ) {
        wppb_hide_properties_for_already_added_fields('#container_wppb_manage_fields');
    }
});
