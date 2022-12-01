/* global gform_signature_delete_signature_strings */

/**
 * Handle delete signature.
 *
 * @param leadId
 * @param fieldId
 */
function deleteSignature( leadId, fieldId ) {
	if ( ! confirm( gform_signature_delete_signature_strings.confirm_delete ) ) {
		return;
	}

	jQuery.post(
		ajaxurl,
		{
			lead_id: leadId,
			field_id: fieldId,
			action: 'gf_delete_signature',
			gf_delete_signature: gform_signature_delete_signature_strings.delete_nonce
		},
		function ( response ) {
			var formId = gform_signature_delete_signature_strings.form_id;
			jQuery( '#input_' + formId + '_' + fieldId + '_signature_filename' ).val( '' );
			jQuery( '#input_' + formId + '_' + fieldId + '_signature_image' ).hide();
			jQuery( '#input_' + formId + '_' + fieldId + '_Container' ).show();
			jQuery( '#input_' + formId + '_' + fieldId + '_resetbutton' ).show();
		}
	);
}
