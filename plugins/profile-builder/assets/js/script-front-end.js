jQuery(document).ready(function(){

    if( jQuery("#wppb-register-user").length ) {
        jQuery('#wppb-register-user').on('submit', function (e) {

            //disable the submit button
            jQuery('.form-submit #register').attr('disabled', true);
            
        })
    }

    //scroll to top on success message
    if( jQuery("#wppb_form_general_message").length ){
        jQuery([document.documentElement, document.body]).animate({ scrollTop: jQuery("#wppb_form_general_message").offset().top }, 500);
    }

    // Fix for Select2 search not focusing
    jQuery(document).on('select2:open', function() {
        let allSelect2Found = document.querySelectorAll('.select2-container--open .select2-search__field');
        allSelect2Found[allSelect2Found.length - 1].focus();
    })

})