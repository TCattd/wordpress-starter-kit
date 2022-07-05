/**
 * Function that adds the css classes field to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function wppb_css_classes_field() {
    if (typeof fields == "undefined") {
        return false;
    }

    var updateFields = ['Default - Name (Heading)', 'Default - Contact Info (Heading)', 'Default - About Yourself (Heading)', 'Default - Username', 'Default - First Name', 'Default - Last Name', 'Default - Nickname', 'Default - E-mail', 'Default - Website', 'Default - AIM', 'Default - Yahoo IM', 'Default - Jabber / Google Talk', 'Default - Password', 'Default - Repeat Password', 'Default - Biographical Info', 'Default - Display name publicly as', 'Heading', 'Input', 'Textarea', 'WYSIWYG', 'Select', 'Datepicker', 'Select (Multiple)', 'Checkbox', 'Radio', 'Upload', 'Phone', 'Timepicker', 'Colorpicker', 'Validation', 'Select (User Role)', 'Select (CPT)', 'Select (Timezone)', 'Select (Country)', 'Select (Currency)', 'Email', 'URL', 'GDPR Checkbox', 'Map' ];

    for( var i = 0; i < updateFields.length; i++ ) {
        fields[ updateFields[i] ]['show_rows'].push( '.row-class-field' );
    }
}

jQuery( function() {
    wppb_css_classes_field();
});