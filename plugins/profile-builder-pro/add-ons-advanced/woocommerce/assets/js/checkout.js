jQuery( 'form.checkout' ).on( 'submit', function(){
    if ( typeof tinyMCEPreInit != 'undefined' ) {
        for ( id in tinyMCEPreInit.mceInit ) {
            var content = tinymce.get(id).getContent()
            jQuery("textarea[name='" + id + "']").text(content)
        }
    }
});

jQuery(document).on("checkout_error", function(){
    var $error_class = [];
    jQuery("span.wppb-err strong").each(function(){
        $error_class.push( jQuery(this).attr("class") );
    })
    jQuery(".wppb-form-field").removeClass("woocommerce-invalid")
    jQuery.each($error_class, function(i, val){
        jQuery("#"+val).addClass("woocommerce-invalid");
        jQuery("#"+ val + " > input:text").addClass("input-text")
    });
})
