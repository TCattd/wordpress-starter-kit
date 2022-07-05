
/**
 * Function that adds the MailPoet field to the global fields object
 * declared in assets/js/jquery-manage-fields-live-change.js
 *
 * @since v.1.0.0
 */
function wppb_mpi_add_field() {
    if (typeof fields == "undefined") {
        return false;
    }
    fields["MailPoet Subscribe"] = {
        'show_rows'	:	[
            '.row-field-title',
            '.row-field',
            '.row-mailpoet-lists',
            '.row-mailpoet-hide-field',
            '.row-mailpoet-default-checked'
        ],
        'properties':	{
            'meta_name_value' : ''
        }
    };
}


jQuery( function() {
    wppb_mpi_add_field();
});
