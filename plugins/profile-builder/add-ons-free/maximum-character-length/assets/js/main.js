/**
 * Function that adds the maximum-character-length field property to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 */
function wppb_max_char_length_update_fields() {
    if (typeof fields == "undefined") {
        return false;
    }

    var updateFields = ['Input', 'Textarea', 'Default - Biographical Info', 'URL', 'Email', 'Default - Website' ];

    for( var i = 0; i < updateFields.length; i++ ) {
        fields[ updateFields[i] ]['show_rows'].push( '.row-maximum-character-length' );
    }
}

jQuery( function() {
    wppb_max_char_length_update_fields();
});