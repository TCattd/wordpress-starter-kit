jQuery( function( ) {
    wppb_initialize_phone_field();
} );

function wppb_initialize_phone_field(){
    jQuery( ".extra_field_phone, .custom_field_phone" ).each( function() {
        var wppb_mask_data = jQuery( this ).attr( 'data-phone-format' );
        var wppb_mask = '';

        jQuery.each( JSON.parse( wppb_mask_data ).phone_data, function( key, value ) {
            if( value == '#' ) {
                value = '9';
            }
            wppb_mask += value;
        } );

        jQuery( this ).inputmask( wppb_mask );
    } );
}