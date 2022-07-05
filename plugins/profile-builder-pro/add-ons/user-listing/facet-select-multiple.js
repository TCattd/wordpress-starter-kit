jQuery(function(){ wppbFacetSelectMultipleInit() });
function wppbFacetSelectMultipleInit(){
    jQuery(".wppb-facet-select-multiple ").each( function(){
        var currentFacetSelectMultiple = this;
        jQuery( currentFacetSelectMultiple ).select2({
            placeholder: wppb_facet_select_multiple_obj.placeholder,
        }).on("select2:open", function(){
            if( jQuery(".wppb-user-to-edit").parents( ".overlay-container" ).length ){
                jQuery(".wppb-user-to-edit").data("select2").dropdown.$dropdownContainer.css( "z-index", "99999999" );
            }
        });
    });
}
