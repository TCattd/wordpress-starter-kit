
const jsmspmBlockEditor = ( function(){

	const pluginId          = 'jsmspm';
	const adminPageL10n     = 'jsmspmAdminPageL10n';
	const postId            = wp.data.select( 'core/editor' ).getCurrentPostId;
	const isSavingMetaBoxes = wp.data.select( 'core/edit-post' ).isSavingMetaBoxes;

	var wasSavingMb = false;

	return {

		refreshPostbox: function(){	// Called by wp.data.subscribe().

			var isSavingMb = isSavingMetaBoxes();	// Check if we're saving metaboxes.

			if ( wasSavingMb && ! isSavingMb ) {	// Check if done saving metaboxes.

				sucomEditorPostbox( pluginId, adminPageL10n, postId );	// Refresh our metabox(es).
			}

			wasSavingMb = isSavingMb;
		},
	}
})();

wp.data.subscribe( jsmspmBlockEditor.refreshPostbox );

