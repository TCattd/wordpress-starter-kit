jQuery(document).ready(function(){

    jQuery("select").filter(function() {
        if ( this.id.startsWith( "wppb-" ) && this.id.endsWith( "user-to-edit" ) ) {
            return this;
        }
    }).on("change", function () {
        window.location.href = jQuery(this).val();
    });
    jQuery(function(){
        jQuery("select").filter(function() {
            if ( this.id.startsWith( "wppb-" ) && this.id.endsWith( "user-to-edit" ) ) {
                return this;
            }
        }).select2().on("select2:open", function(){
            if( jQuery(".wppb-user-to-edit").parents( ".overlay-container" ).length ){
                jQuery(".wppb-user-to-edit").data("select2").dropdown.$dropdownContainer.css( "z-index", "99999999" );
            }
        });
    })

})