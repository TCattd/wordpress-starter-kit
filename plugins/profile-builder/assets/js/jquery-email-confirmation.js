function wppb_display_page_select( value ){
	if ( value == 'yes' ){
		jQuery ( '#wppb-settings-activation-page' ).show();
		jQuery ( '.dynamic1' ).show();
	
	}else{
		jQuery ( '#wppb-settings-activation-page' ).hide();
		jQuery ( '.dynamic1' ).hide();
	}
}


function wppb_display_page_select_aa( value ){
	if ( value == 'yes' )
		jQuery ( '.dynamic2' ).show();
	
	else
		jQuery ( '.dynamic2' ).hide();
}

function wppb_display_page_select_re( value ){
	if ( value == 'yes' )
		jQuery ( '.dynamic3' ).show();

	else
		jQuery ( '.dynamic3' ).hide();
}

function wppb_display_page_select_cr( value ){
	if ( value == 'yes' )
		jQuery ( '.dynamic4' ).show();

	else
		jQuery ( '.dynamic4' ).hide();
}


jQuery(function() {
	if ( ( jQuery( '#wppb_settings_email_confirmation' ).val() == 'yes' ) || ( jQuery( '#wppb_general_settings_hidden' ).val() == 'multisite' ) ){
		jQuery ( '#wppb-settings-activation-page' ).show();
		jQuery ( '.dynamic1' ).show();
	
	}else{
		jQuery ( '#wppb-settings-activation-page' ).hide();
		jQuery ( '.dynamic1' ).hide();
	}
	
	
	if ( jQuery( '#adminApprovalSelect' ).val() == 'yes' )
		jQuery ( '.dynamic2' ).show();
	
	else
		jQuery ( '.dynamic2' ).hide();


	if ( jQuery( '#rolesEditorSelect' ).val() == 'yes' )
		jQuery ( '.dynamic3' ).show();

	else
		jQuery ( '.dynamic3' ).hide();

	if ( jQuery( '#contentRestrictionSelect' ).val() == 'yes' )
		jQuery ( '.dynamic4' ).show();

	else
		jQuery ( '.dynamic4' ).hide();
});