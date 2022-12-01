/* global gform_user_registration_merge_tags_strings */

if ( window.gform ) {
	gform.addFilter( 'gform_merge_tags', 'gf_user_registration_merge_tags' );
}

/**
 * Add custom User Registration merge tags to Gravity Forms
 *
 * @param mergeTags
 * @param elementId
 * @param hideAllFields
 * @param excludeFieldTypes
 * @param isPrepop
 * @param option
 */
function gf_user_registration_merge_tags( mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option ) {
	mergeTags['other'].tags.push(
		{
			tag: '{activation_url}',
			label: gform_user_registration_merge_tags_strings.user_activation_url
		},
		{
			tag: '{set_password_url}',
			label: gform_user_registration_merge_tags_strings.set_password_url
		}
	);

	return mergeTags;
}
