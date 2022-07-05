/**
 * Custom Redirect UI
 */
jQuery().ready(function(){
	wppb_transform_description_in_tooltip();
});

jQuery(document).ajaxSuccess(function(){
	wppb_transform_description_in_tooltip()
});

function wppb_transform_description_in_tooltip(){
	jQuery('.profile-builder_page_custom-redirects .description').each( function(){
		jQuery(this).hide();
		text = jQuery(this).text().replace(/^\s+|\s+$/g, '');
		if (text != '' && jQuery(this).parent().children('.wppb-contextual-help').length == 0 ){
			something = jQuery(this).parent().append('<span title="' + text + '" class="wppb-contextual-help dashicons dashicons-editor-help"></span>')
		}
	})
}

jQuery().ready( function() {
    wppb_custom_redirects_user_radio();
} );

function wppb_custom_redirects_user_radio() {
    jQuery( '.wck-add-form .row-user label' ).html( jQuery('.wck-add-form .row-idoruser .mb-right-column').html() + '<span class="required">*</span>' );

    jQuery( '.update_container_wppb_cr_user' ).each( function() {
        jQuery( this ).find( '.row-user label' ).html( jQuery( this ).find( '.row-idoruser .mb-right-column' ).html() + '<span class="required">*</span>' )
    } );

    jQuery( '#container_wppb_cr_user').find( '.row-idoruser pre' ).each( function() {
        if( jQuery( this ).text() == 'user' ) {
            jQuery( this ).text( 'Username' );
        } else if( jQuery( this ).text() == 'userid' ) {
            jQuery( this ).text( 'User ID' );
        }
    } );
}