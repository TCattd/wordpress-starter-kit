jQuery( document ).ready( function( ) {
    wppb_initialize_colorpicker();
} );

function wppb_initialize_colorpicker(){
    jQuery( '.custom_field_colorpicker' ).each( function() {
        var $delimiter = jQuery(this).siblings( '.wppb-description-delimiter' );

        if( wppb_colorpicker_data.isFrontend == 1 ) {
            jQuery( this ).iris( {
                target: $delimiter
            } );
        } else {
            jQuery( this ).iris( {} );
        }
    } );

    jQuery( document ).on( "click", function( e ) {
        if( ! jQuery( e.target ).is( ".custom_field_colorpicker, .iris-picker, .iris-picker-inner, .iris-slider, .iris-slider-offset, .iris-square-handle, .iris-square-value" ) ) {
            jQuery( '.custom_field_colorpicker' ).iris( 'hide' );
        }
    } );

    jQuery( '.custom_field_colorpicker' ).on( "click", function( event ) {
        jQuery( '.custom_field_colorpicker' ).iris( 'hide' );
        jQuery( this ).iris( 'show' );
    } );
}