jQuery( function(){

    /* Display custom redirect URL section if type of restriction is "Redirect" */
    jQuery( 'input[type=radio][name=wppb-content-restrict-type]' ).on( "click", function() {
        if( jQuery( this ).is(':checked') && jQuery( this ).val() == 'redirect' )
            jQuery( '#wppb-meta-box-fields-wrapper-restriction-redirect-url' ).addClass( 'wppb-content-restriction-enabled' );
        else
            jQuery( '#wppb-meta-box-fields-wrapper-restriction-redirect-url' ).removeClass( 'wppb-content-restriction-enabled' );
    } );

    /* Display custom redirect URL field */
    jQuery( '#wppb-content-restrict-custom-redirect-url-enabled' ).on( "click", function() {
        if( jQuery( this ).is( ':checked' ) )
            jQuery( '.wppb-meta-box-field-wrapper-custom-redirect-url' ).addClass( 'wppb-content-restriction-enabled' );
        else
            jQuery( '.wppb-meta-box-field-wrapper-custom-redirect-url' ).removeClass( 'wppb-content-restriction-enabled' );
    } );

    /* Display custom messages editors */
    jQuery( '#wppb-content-restrict-messages-enabled' ).on( "click", function() {
        if( jQuery( this ).is( ':checked' ) )
            jQuery( '.wppb-meta-box-field-wrapper-custom-messages' ).addClass( 'wppb-content-restriction-enabled' );
        else
            jQuery( '.wppb-meta-box-field-wrapper-custom-messages' ).removeClass( 'wppb-content-restriction-enabled' );
    } );


    /* Disable / Enable the user roles from the "Display for" field if the "Logged in Users" option is checked or not */
    jQuery( document ).on( 'click', 'input[name="wppb-content-restrict-user-status"]', function() {
        wppb_disable_enable_user_roles( jQuery( this ) );
    } );

    jQuery( 'input[name="wppb-content-restrict-user-status"]' ).each( function() {
        wppb_disable_enable_user_roles( jQuery( this ) );
    } );


    function wppb_disable_enable_user_roles( $element ) {
        $wrapper = $element.closest( '.wppb-meta-box-field-wrapper' );

        if( $element.is( ':checked' ) ) {
            $wrapper.find( 'input[name="wppb-content-restrict-user-role[]"]' ).attr( 'disabled', false );
        } else {
            $wrapper.find( 'input[name="wppb-content-restrict-user-role[]"]' ).attr( 'disabled', true );
        }
    }

} );