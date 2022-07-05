/* hide Social Connect custom button text fields */
function wppb_sc_buttons_text_fields( element ) {
    if( jQuery( element ).val() == 'text' ) {
        jQuery( '.row-facebook-button-text, .row-google-button-text, .row-twitter-button-text, .row-linkedin-button-text, .row-facebook-button-text-ep, .row-google-button-text-ep, .row-twitter-button-text-ep, .row-linkedin-button-text-ep' ).show();
        jQuery( '.row-heading-before-reg-buttons, .row-heading-before-ep-buttons' ).hide();
        // hide Google text fields if new Google Sign In button is used
        if ( jQuery( '#google-sign-in-button' ).val() == 'new' ){
            jQuery( ' .row-google-button-text, .row-google-button-text-ep' ).hide();
        }
    } else {
        jQuery( '.row-facebook-button-text, .row-google-button-text, .row-twitter-button-text, .row-linkedin-button-text, .row-facebook-button-text-ep, .row-google-button-text-ep, .row-twitter-button-text-ep, .row-linkedin-button-text-ep' ).hide();
        jQuery( '.row-heading-before-reg-buttons, .row-heading-before-ep-buttons' ).show();
    }
}

/* display custom text fields according to the Google Sign In button selected */
function wppb_sc_google_sign_in_button_text_fields( element ){
    if( jQuery( element ).val() == 'new' ) {
        if ( jQuery( '#buttons-style' ).val() == 'text' ){
            jQuery( '.row-facebook-button-text, .row-twitter-button-text, .row-linkedin-button-text, .row-facebook-button-text-ep, .row-twitter-button-text-ep, .row-linkedin-button-text-ep' ).show();
            jQuery( '.row-heading-before-reg-buttons, .row-heading-before-ep-buttons, .row-google-button-text, .row-google-button-text-ep' ).hide();
        }
    }
    else{
        if ( jQuery( '#buttons-style' ).val() == 'text' ){
            jQuery( '.row-facebook-button-text, .row-google-button-text, .row-twitter-button-text, .row-linkedin-button-text, .row-facebook-button-text-ep, .row-google-button-text-ep, .row-twitter-button-text-ep, .row-linkedin-button-text-ep' ).show();
            jQuery( '.row-heading-before-reg-buttons, .row-heading-before-ep-buttons' ).hide();
        }
    }
}

jQuery( document ).ready( function() {
    wppb_sc_handle_buttons_order_field();

    wppb_sc_buttons_text_fields( '#wppb_social_connect_settings ' + '#buttons-style' );
    jQuery( '#wppb_social_connect_settings ' + '#buttons-style' ).on('change', function() {
        wppb_sc_buttons_text_fields( this );
    } );
    jQuery( '#wppb_social_connect_settings ' + '#google-sign-in-button' ).on('change', function() {
        wppb_sc_google_sign_in_button_text_fields( this );
    } );
} );

/* function that handles the sorting of the buttons */
function wppb_sc_handle_buttons_order_field() {
    jQuery( '#wppb_social_connect_settings ' + '.row-buttons-order .wck-checkboxes' ).sortable( {

        // assign a custom handle for the drag and drop
        handle: '.sortable-handle',

        create: function( event, ui ) {

            // add the custom handle for drag and drop
            jQuery( this ).find( 'div' ).each( function() {
                jQuery( this ).prepend( '<span class="sortable-handle"></span>' );
            } );

            $sortOrderInput = jQuery( this ).parents( '.row-buttons-order' ).siblings( '.row-buttons-re-order' ).find( 'input[type=text]' );

            if( $sortOrderInput.val() != '' ) {
                sortOrderElements = $sortOrderInput.val().split( ', ' );
                sortOrderElements.shift();

                for( var i=0; i < sortOrderElements.length; i++ ) {
                    jQuery( '#wppb_social_connect_settings ' + '.row-buttons-order .wck-checkboxes' ).append( jQuery( '#wppb_social_connect_settings ' + '.row-buttons-order .wck-checkboxes input[value=' + sortOrderElements[i] + ']' ).parent().parent().get( 0 ) );
                }
            }

            $sortOrderInput.val( '' );
            jQuery( this ).find( 'input[type=checkbox]' ).each( function() {
                $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery( this ).val() );
            } );
        },

        update: function( event, ui ) {
            $sortOrderInput = ui.item.parents( '.row-buttons-order' ).siblings( '.row-buttons-re-order' ).find( 'input[type=text]' );
            $sortOrderInput.val( '' );

            ui.item.parent().find( 'input[type=checkbox]' ).each( function() {
                $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery( this ).val() );
            } );
        }
    } );
}