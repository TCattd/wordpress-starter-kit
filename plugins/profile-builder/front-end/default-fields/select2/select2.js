
/**
 * Function that initializes select2 on fields
 *
 * @since v.3.3.4
 */
function wppb_select2_initialize() {
    jQuery ( '.custom_field_select2' ).each( function(){
        var selectElement = jQuery( this );
        var arguments = selectElement.attr('data-wppb-select2-arguments');
        arguments = JSON.parse( arguments );
        selectElement.select2( arguments ).on('select2:open', function(){
            // compatibility with Divi Overlay
            if( jQuery(selectElement).parents( '.overlay-container' ).length ){
                jQuery(selectElement).data('select2').dropdown.$dropdownContainer.css( 'z-index', '99999999' );
            }
        });
    });
}

jQuery( document ).ready(function() {
    wppb_select2_initialize();
});