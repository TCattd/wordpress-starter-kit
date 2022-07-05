/**
 * Function that adds the visibility and user role visibility field properties to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function updateFieldsVisibility() {
    if (typeof fields == "undefined") {
        return false;
    }

    //The fields we want the new properties to show - they must match the ones in index.php
    var updateFields = [ 'Default - Name (Heading)', 'Default - Contact Info (Heading)', 'Default - About Yourself (Heading)', 'Default - Username', 'Default - First Name', 'Default - Last Name',
        'Default - Nickname', 'Default - E-mail', 'Default - Website', 'Default - Password', 'Default - Repeat Password', 'Default - Biographical Info', 'Default - Display name publicly as',
        'Checkbox', 'Checkbox (Terms and Conditions)', 'Radio', 'Datepicker', 'Timepicker', 'Colorpicker', 'Input', 'Input (Hidden)', 'Number', 'Textarea', 'Phone', 'Select', 'Select (Multiple)',
        'Select (Country)', 'Select (CPT)','Select (Timezone)', 'Select (Currency)', 'Select (User Role)', 'Upload', 'Avatar', 'WYSIWYG', 'Heading', 'HTML', 'Repeater', 'Email', 'URL', 'Map' ];

    for( var i = 0; i < updateFields.length; i++ ) {
        if (typeof fields[ updateFields[i] ] != "undefined" ){
            fields[ updateFields[i] ]['show_rows'].push( '.row-visibility' );
            fields[ updateFields[i] ]['show_rows'].push( '.row-user-role-visibility' );
            fields[ updateFields[i] ]['show_rows'].push( '.row-location-visibility' );
        }
    }
}

function updateFieldsToShow() {
    if( typeof fields_to_show !== "undefined" ) {
        fields_to_show.push('.row-visibility');
        fields_to_show.push('.row-user-role-visibility');
        fields_to_show.push('.row-location-visibility');
    }
}

jQuery(window).on('load', function() {
    updateFieldsVisibility();
    updateFieldsToShow();
    if( typeof wppb_hide_properties_for_already_added_fields !== "undefined" ) {
        wppb_hide_properties_for_already_added_fields('#container_wppb_manage_fields');
    }
});
