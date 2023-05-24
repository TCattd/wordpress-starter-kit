jQuery(document).ready(function() {

    jQuery('.wppb-form-field:not(.login-submit):not(.wppb-two-factor-authentication) input:disabled').parent().addClass('disabled-field');

    // mark Upload/Avatar Fields when a file is selected for upload
    jQuery('.wppb-avatar, .wppb-upload').on('DOMSubtreeModified', function(){
        let uploadField = jQuery(this);

        if (uploadField.find('.wppb_upload_button').is(':visible'))
            uploadField.removeClass('file-selected');
        else uploadField.addClass('file-selected');
    });

    // mark Upload/Avatar Fields on Edit Form, if a file is present
    if ( jQuery('.wppb-avatar').find('.upload-field-details').length > 0 )
        jQuery('.wppb-avatar').addClass('file-selected');
    if ( jQuery('.wppb-upload').find('.upload-field-details').length > 0 )
        jQuery('.wppb-upload').addClass('file-selected');

    // Disable HTML5 validation. It prevents form field error markers to be displayed for required fields.
    jQuery('.wppb-register-user').attr('novalidate', 'novalidate');

});