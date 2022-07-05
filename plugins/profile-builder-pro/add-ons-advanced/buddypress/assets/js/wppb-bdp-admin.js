jQuery( function() {
    // We depend on these fields so if these are not present, do nothing
    if( typeof fields == 'undefined' )
        return false;

    // back-up
    var bdp_disabled_fields = [
        'Default - Username',
        'Default - Password',
        'Default - Repeat Password',
        'Default - Name (Heading)',
        'Default - Contact Info (Heading)',
        'Default - About Yourself (Heading)',
        'Default - Display name publicly as',
        'Heading',
        'Input (Hidden)',
        'Checkbox (Terms and Conditions)',
        'Avatar',
        'reCAPTCHA',
        'Validation',
        'HTML',
        'MailChimp Subscribe',
        'MailPoet Subscribe',
        'Campaign Monitor Subscribe',
        'Email Confirmation'
        ];

    if ( typeof wppb_bdp_visibility_disabled_field_list != 'undefined' ){
        try{
            bdp_disabled_fields = JSON.parse( wppb_bdp_visibility_disabled_field_list );
        }
        catch(e){
            console.log("PB BuddyPress Add-on: Cannot parse visibility disabled list of fields. Using default list.")
        }
    }

    // Add the buddypress visibility field options in the admin page
    for( var field_name in fields ) {
        if( bdp_disabled_fields.indexOf( field_name ) == -1 ) {
            fields[field_name]['show_rows'].push('.row-bdp-default-visibility');
            fields[field_name]['show_rows'].push('.row-bdp-allow-custom-visibility');
        }
    }
});


