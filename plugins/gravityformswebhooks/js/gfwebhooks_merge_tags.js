/* global gform_webhooks_merge_tags_strings */

if ( window.gform ) {
	gform.addFilter( 'gform_merge_tags', 'gf_webhooks_merge_tags' );
}

/**
 * Add custom Webhook merge tags to Gravity Forms
 *
 * @param mergeTags
 * @param elementId
 * @param hideAllFields
 * @param excludeFieldTypes
 * @param isPrepop
 * @param option
 */
function gf_webhooks_merge_tags( mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option ) {
	mergeTags['other'].tags.push(
		{
			tag: '{admin_ajax_url}',
			label: gform_webhooks_merge_tags_strings.ajax_url_label
		},
		{
			tag: '{rest_api_url}',
			label: gform_webhooks_merge_tags_strings.rest_api_url_label
		}
	);

	return mergeTags;
}
