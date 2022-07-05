/*
 * Field Visibility settings
 */
function WPPB_BDP_Visibility(){

    var wppb_bdp_toggle_visibility_settings = function( event ){
        var visibilityToggle = jQuery( event.target ).parent();
        visibilityToggle.toggle();
        visibilityToggle.siblings().toggle();
    };

    var wppb_bdp_toggle_visibility_settings_label = function( event ) {
        var selectedLabel = jQuery( 'label[for=\'' + event.target.id + '\']').text();
        var visibilityToggle = jQuery( event.target ).closest( '.wppb-field-visibility-settings' );
        var siblings = visibilityToggle.siblings();

        siblings.find( '.wppb-current-visibility-level' ).text( selectedLabel );
        visibilityToggle.toggle();
        siblings.toggle();
    };

    var addEventHandlers = function(){
        jQuery( '.wppb-visibility-toggle-link' ).click( wppb_bdp_toggle_visibility_settings );
        jQuery( '.wppb-field-visibility-settings input' ).click( wppb_bdp_toggle_visibility_settings_label );
    };

    addEventHandlers();
}

/*
 * Make the Buddypress Field Visibility functionality available for usage
 *
 */
var wppbBdpVisibilityApp;


// Initialize the Field Visibility App after jQuery is ready
jQuery( function() { wppbBdpVisibilityApp = new WPPB_BDP_Visibility(); });
