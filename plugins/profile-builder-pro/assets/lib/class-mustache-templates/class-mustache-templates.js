CodeMirror.defineMode("mustache", function(config, parserConfig) {
    var mustacheOverlay = {
        token: function(stream, state) {
            var ch;
            if (stream.match("{{")) {
                while ((ch = stream.next()) != null)
                    if (ch == "}" && stream.next() == "}") break;
                stream.eat("}");
                return "mustache";
            }
            while (stream.next() != null && !stream.match("{{", false)) {}
            return null;
        }
    };
    return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), mustacheOverlay);
});


jQuery(function(){
    var wck_stp_textareas = ["wppb_mustache_template"];
    var length = wck_stp_textareas.length;
    element = null;

    for ( var i=0; i < length; i++ ){
        element = wck_stp_textareas[i];

        if ( jQuery( 'textarea[class="' + element + '"]' ).length > 0 ){
            jQuery( 'textarea[class|="' + element + '"]' ).each( function(){
                var editor = CodeMirror.fromTextArea( this, {
                    mode: "mustache",
                    lineNumbers: true,
                    //lineWrapping:true,
                    extraKeys: {
                        "F11": function(cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                        },
                        "Esc": function(cm) {
                            if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                        }
                    }
                });
            });
        }
    }
})

jQuery(function() {
    jQuery( ".stp-extra" ).accordion();
});

//add hidden input with off value for checkboxes
jQuery(function() {

    jQuery('form .wck-post-body input[type="checkbox"]').each( function() {

        if ( !jQuery(this).is(':checked') ) {
            var wppb_mustache_checkbox_off = document.createElement('input');
                wppb_mustache_checkbox_off.type = 'hidden';
                wppb_mustache_checkbox_off.value = 'off';
                wppb_mustache_checkbox_off.name = jQuery(this).attr('name');

            jQuery(this).after(wppb_mustache_checkbox_off);
        }

    });

    jQuery('form .wck-post-body input[type="checkbox"]').on( 'change', function() {
        var wppb_mustache_checkbox_off = document.createElement('input');
            wppb_mustache_checkbox_off.type = 'hidden';
            wppb_mustache_checkbox_off.value = 'off';
            wppb_mustache_checkbox_off.name = jQuery(this).attr('name');

        if ( jQuery(this).is(':checked') )
            jQuery( '.' + jQuery(this).attr('name') + ' input[type="hidden"]' ).remove();
        else
            jQuery(this).after(wppb_mustache_checkbox_off);

    });

});
