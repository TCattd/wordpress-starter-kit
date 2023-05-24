function validate_simple_upload(){
    jQuery("input:file").on('change', function(e){
        //handle simple upload
        var uploadInputName = jQuery(this).attr('name');
        var uploadButton = jQuery(this);
        var oFReader = new FileReader();
        var attachment = e.target.files[0];
        oFReader.readAsDataURL(attachment);
        oFReader.onload = function (oFREvent) {
            var src = oFREvent.target.result;
            var error = '';
            jQuery("#p_" + uploadInputName).text(error);
            uploadInputId = uploadInputName.split('-').join('_');
            //Check allowed upload type by wordpress
            allowed_mime_types = wppb_allowed_wordpress_formats.allowed_wordpress_formats;
            allowed_by_wordpress = false;
            for (var key in allowed_mime_types){
                allowed_type = allowed_mime_types[key];
                if (attachment.type === allowed_type){
                    allowed_by_wordpress = true;
                    possible_extensions = key.split('|');
                }
            }
            if (allowed_by_wordpress) {
                //Check allowed extensions
                allowed_extensions = jQuery("#allowed_extensions_" + uploadInputId).val();
                if (allowed_extensions !== '') {
                    var allowed = false;
                    allowed_extensions = allowed_extensions.split(',');
                    for (var i = 0; i < allowed_extensions.length; i++) {
                        if (possible_extensions.includes(allowed_extensions[i])) {
                            allowed = true;
                        }
                    }
                    if (allowed == false) {
                        error = wppb_error_messages.upload_type_error_message;
                    }
                }
                //Check size limit
                allowed_size_limit = wppb_limit.size_limit;
                if (attachment.size > allowed_size_limit) {
                    var limit = allowed_size_limit / (1024 * 1024) + 1;
                    error = wppb_error_messages.limit_error_message + limit + 'MB.';
                }
            }
            else{
                error = wppb_error_messages.upload_type_error_message;
            }
            if (error !== '') {
                jQuery("#p_" + uploadInputName).text(error);
                uploadButton.val('');
            } else {
                var fieldName = uploadInputName.replace(/^(simple_upload_)/, '').replace(/(-)/, '_');;
                var formData = new FormData();
                if (uploadButton.closest('.wppb-upload').length > 0) {
                    formData.append('action', 'wppb_ajax_simple_upload');
                    formData.append(fieldName, jQuery(e.target).prop('files')[0]);
                } else {
                    formData.append('action', 'wppb_ajax_simple_avatar');
                    formData.append(fieldName, jQuery(e.target).prop('files')[0]);
                }
                formData.append('nonce', wppb_upload_script_vars.nonce);
                formData.append('name', fieldName);

                var alreadyDisabled = false;
                if ( jQuery( 'p.form-submit .submit.button' ).prop( 'disabled' ) ) {
                    alreadyDisabled = true;
                } else {
                    jQuery( "p.form-submit .submit.button" ).prop( 'disabled', true );
                }

                jQuery.ajax({
                    url: wppb_upload_script_vars.ajaxUrl,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response){
                        
                        var response = JSON.parse(response)

                        if( response.errors ){
                            jQuery("input#" + fieldName).val(null);
                            jQuery('.wppb_simple_upload', jQuery("input#" + fieldName).parent()).val(null);
                        } else {
                            jQuery("input#" + fieldName).val(response);
                        }

                        if ( !alreadyDisabled ) {
                            jQuery("p.form-submit .submit.button").prop('disabled', false);
                        }
                        
                    },
                });
            }
        };
    });
}
jQuery(document).ready(function(){
    validate_simple_upload();

    jQuery(document).on( 'click', '.wppb-rpf-add', function( event ){
        validate_simple_upload();
    });

    if( typeof wp.media != "undefined" ){
        // Uploading files
        var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

        jQuery(document).on( 'click', '.wppb_upload_button', function( event ){
            event.preventDefault();

            var set_to_post_id = ''; // Set this

            var file_frame;
            var uploadInputId = jQuery( this ).data( 'upload_input' );
            var uploadButton = jQuery( this );

            /* set default tab to upload file */
            wp.media.controller.Library.prototype.defaults.contentUserSetting = false;
            wp.media.controller.Library.prototype.defaults.router = false;
            wp.media.controller.Library.prototype.defaults.searchable = true;
            wp.media.controller.Library.prototype.defaults.sortable = false;
            wp.media.controller.Library.prototype.defaults.date = false;

            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                // Set the post ID to what we want
                file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                // Open frame
                file_frame.open();
                return;
            } else {
                // Set the wp.media post id so the uploader grabs the ID we want when initialised
                wp.media.model.settings.post.id = set_to_post_id;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' )
                },
                multiple: false // Set to true to allow multiple files to be selected
            });

            /* send the meta_name of the field */
            file_frame.uploader.options.uploader['params']['wppb_upload'] = 'true';
            file_frame.uploader.options.uploader['params']['meta_name'] = jQuery( this ).data( 'upload_mn' );

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachments = file_frame.state().get('selection').toJSON();
                var attids = [];

                for( var i=0;i < attachments.length; i++ ){
                    // Do something with attachment.id and/or attachment.url here
                    attids.push( attachments[i].id );
                    result = '<div class="upload-field-details" id="'+ uploadInputId +'_info_container" data-attachment_id="'+ attachments[i].id +'">';
                    if( attachments[i].sizes != undefined ){
                        if( attachments[i].sizes.thumbnail != undefined )
                            thumb = attachments[i].sizes.thumbnail;
                        else
                            thumb = attachments[i].sizes.full;
                        thumbnailUrl = thumb.url;
                    }
                    else{
                        thumbnailUrl = attachments[i].icon;
                    }

                    result += '<div class="file-thumb">';
                    result += '<a href="'+ attachments[i].url +'" target="_blank" class="wppb-attachment-link">';
                    result += '<img width="80" height="80" src="'+ thumbnailUrl +'"/>';
                    result += '</a>';
                    result += '</div>';
                    result += '<p><span class="file-name">'+attachments[i].filename+'</span><span class="file-type">'+attachments[i].mime +'</span><span class="wppb-remove-upload" tabindex="0">'+wppb_upload_script_vars.remove_link_text+'</span></p></div>';

                    // if multiple upload false remove previous upload details
                    if( uploadButton.data( 'multiple_upload' ) == false ){
                        jQuery( '.upload-field-details', uploadButton.parent() ).remove();
                    }

                    uploadButton.before( result );
                    uploadButton.hide();

                }
                // turn into comma separated string
                attids = attids.join(',');
                jQuery( 'input[id="'+uploadInputId+'"]', uploadButton.parent() ).val( attids );

                // Restore the main post ID
                wp.media.model.settings.post.id = wp_media_post_id;

                jQuery(document).trigger( 'wppb_file_uploaded' )

            });

            // Finally, open the modal
            file_frame.open();
            // remove tabs from the top ( this is done higher in the code when setting router to false )
            //jQuery('.media-frame-router').remove();

            jQuery('.media-frame-title').append('<style type="text/css">label.setting, .edit-attachment{display:none !important;}</style>');
        });

        // Restore the main ID when the add media button is pressed
        jQuery('a.add_media').on('click', function() {
            wp.media.model.settings.post.id = wp_media_post_id;
        });

        jQuery(document).on('keypress', '.wppb-remove-upload', function(e){
            if(e.which == 13) {
                jQuery(this).trigger('click');
            }
        });

        jQuery(document).on('click', '.wppb-remove-upload', function(e){
            if( confirm( 'Are you sure ?' ) ){
                /* update hidden input */
                simple_upload_button = jQuery(this).closest('li, td, p.form-row, div.form-row').find('input[type="file"]');
                if (simple_upload_button.attr('name')) {
                    upload_input_id = simple_upload_button.attr('name').split('_').slice(2).join('_');
                    upload_input_id = upload_input_id.split('-').join('_');
                    upload_input = jQuery("#" + upload_input_id);
                }
                else{
                    upload_input = jQuery(this).closest('li, td, p.form-row, div.form-row').find('input[type="hidden"]');
                }
                removedAttachement = jQuery(this).parent().parent('.upload-field-details').data('attachment_id');
                uploadAttachemnts = upload_input.val();
                uploadAttachemntsArray = uploadAttachemnts.split(',');
                newuploadAttachments = [];
                for (var i = 0; i < uploadAttachemntsArray.length; i++) {
                    if (uploadAttachemntsArray[i] != removedAttachement)
                        newuploadAttachments.push(uploadAttachemntsArray[i]);
                }
                newuploadAttachments = newuploadAttachments.join(',');
                upload_input.val(newuploadAttachments);

                /* remove the attachment details */
                jQuery(this).parent().parent('.upload-field-details').next('a').show();
                simple_upload_button.show();
                simple_upload_button.val('');
                jQuery(this).parent().parent('.upload-field-details').remove();

                jQuery(document).trigger( 'wppb_removed_uploaded_file' )
                
            }
        });
    }
});
