/* initialize here the select2 */
jQuery(function(){
    jQuery('.custom_field_cpt_select').each( function(){
        var currentCptSelect = this;
        jQuery( currentCptSelect ).select2().on('select2:open', function(){
            // compatibility with Divi Overlay
            if( jQuery(currentCptSelect).parents( '.overlay-container' ).length ){
                jQuery(currentCptSelect).data('select2').dropdown.$dropdownContainer.css( 'z-index', '99999999' );
            }
        });
    });
});
