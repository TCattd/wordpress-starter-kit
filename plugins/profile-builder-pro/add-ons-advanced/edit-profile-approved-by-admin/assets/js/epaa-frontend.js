jQuery(function(){

    if( jQuery( '#wppb-epaa-finish-review' ).length != 0 ) {

        jQuery('.wppb-user-forms.wppb-edit-user #wppb-epaa-finish-review').click(function (e) {
            e.preventDefault();

            $field_ids = jQuery(this).attr('data-wppb-epaa-approve-fields');
            $user_id = jQuery(this).attr('data-wppb-epaa-user-id');

            $unapproved_field_is = [];
            jQuery('.wppb-epaa-switch input').each(function(){
                if ( ! jQuery(this).is(":checked") ) {
                    $unapproved_field_is.push( jQuery(this).attr('data-wppb-epaa-field-id') );
                }
            });


            jQuery('.wppb-user-forms.wppb-edit-user').css('opacity', '0.6');

            jQuery.post(wppb_epaa.ajaxurl, {
                action: "wppb_epaa_approve_values",
                fieldIDS: $field_ids,
                unapprovedIDS: $unapproved_field_is,
                userID: $user_id
            }, function (response) {
                if( response == 'success' ){
                    approvedFieldIds = $field_ids.split(',');
                    for (var i = 0; i < approvedFieldIds.length; i++) {
                        //remove on each field the approve actions and the attribute
                        jQuery( '#wppb-form-element-' + approvedFieldIds[i] + ' *[data-wppb-epaa-approval-action="true"]' ).removeAttr('data-wppb-epaa-approval-action');
                        jQuery( '#wppb-form-element-' + approvedFieldIds[i] + ' .wppb-epaa-actions' ).remove();
                    }

                    /* remove the buttons it there are no more fields to approve */
                    if( jQuery('.wppb-user-forms.wppb-edit-user *[data-wppb-epaa-approval-action="true"]').length === 0 ){
                        jQuery('.wppb-user-forms.wppb-edit-user .wppb-epaa-admin-actions').remove();
                    }

                }
                jQuery('.wppb-user-forms.wppb-edit-user').css('opacity', '1.0');
            });

        });


        jQuery("#wppb-epaa-approve-all").click(function (e) {
            e.preventDefault();

            fieldIDS = [];

            jQuery('.wppb-epaa-switch input').prop('checked', true);

            jQuery('.wppb-user-forms.wppb-edit-user *[data-wppb-epaa-approval-action="true"]').each(function () {
                $field_id = jQuery(this).attr('data-wppb-epaa-field-id');
                fieldIDS.push($field_id);
            });

            fieldIDS = fieldIDS.join(',');

            jQuery('#wppb-epaa-finish-review').attr('data-wppb-epaa-approve-fields', fieldIDS);

            jQuery(this).prop('disabled', true);

        });


        jQuery('.wppb-epaa-switch input').click(function (e) {

            $field_id = jQuery(this).attr('data-wppb-epaa-field-id');
            $all_field_ids_to_be_approved = jQuery('#wppb-epaa-finish-review').attr('data-wppb-epaa-approve-fields');
            if ($all_field_ids_to_be_approved != '') {
                $all_field_ids_to_be_approved = $all_field_ids_to_be_approved.split(',');
            }
            else {
                $all_field_ids_to_be_approved = new Array();
            }


            if (jQuery(this).is(":checked")) {
                $all_field_ids_to_be_approved.push($field_id);
                if( jQuery('.wppb-epaa-switch input:checked').length ==jQuery('.wppb-epaa-switch input').length ){
                    jQuery("#wppb-epaa-approve-all").prop('disabled', true);
                }
            }
            else {
                var index = $all_field_ids_to_be_approved.indexOf($field_id);
                if (index > -1) {
                    $all_field_ids_to_be_approved.splice(index, 1);
                }

                jQuery("#wppb-epaa-approve-all").prop('disabled', false);

            }

            $all_field_ids_to_be_approved = $all_field_ids_to_be_approved.join(',');
            jQuery('#wppb-epaa-finish-review').attr('data-wppb-epaa-approve-fields', $all_field_ids_to_be_approved);

        });
    }

});