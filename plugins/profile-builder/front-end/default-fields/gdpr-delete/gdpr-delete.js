jQuery(document).ready(function() {
    jQuery(".wppb-delete-account").on("click", function (e) {
        e.preventDefault();

        var wppbDeleteUser = prompt(wppbGdpr.delete_text);
        if( wppbDeleteUser === "DELETE" ) {
            window.location.replace(wppbGdpr.delete_url);
        }
        else{
            alert( wppbGdpr.delete_error_text );
        }

    });
});