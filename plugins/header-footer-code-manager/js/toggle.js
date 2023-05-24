// Toggle switch
jQuery('.nnr-switch input').on(
    'click', function () {
        var t = jQuery(this),
        togvalue = t.is(':checked') ? 'on' : 'off',
        scriptid = t.data('id'),
        data = {
            action: 'hfcm-request',
            toggle: true,
            id: scriptid,
            togvalue: togvalue,
            security: hfcm_ajax.security
        };

        jQuery.post(
            ajaxurl,
            data
        );
    }
);

// Delete confirmation
jQuery('.snippets .delete > a').on(
    'click', function () {
        var name = jQuery(this).parents('.name').find('> strong').text();
        return confirm('Snippet name: ' + name + '\n\nAre you sure you want to delete this snippet?');
    }
);

function hfcmCopyToClipboard(elem)
{
    // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;

    // must use a temporary form element for the selection and copy
    target = document.getElementById(targetId);
    if (!target) {
        var target = document.createElement("textarea");
        target.style.position = "absolute";
        target.style.left = "-9999px";
        target.style.top = "0";
        target.id = targetId;
        document.body.appendChild(target);
    }
    target.textContent = elem.getAttribute('data-shortcode');
    elem.textContent = "Copied!";

    setTimeout(
        function () {
            elem.textContent = "Copy Shortcode";
        }, 2000
    );
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);

    // copy the selection
    var succeed;
    try {
        succeed = document.execCommand("copy");
    } catch (e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }

    if (isInput) {
        // restore prior selection
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }
    target.remove();
    return succeed;
}

jQuery(
    function ($) {
        var elemsCopyBtn = document.getElementsByClassName('hfcm_copy_shortcode');

        for (var i = 0; i < elemsCopyBtn.length; i++) {
            elemsCopyBtn[i].addEventListener(
                "click", function () {
                    hfcmCopyToClipboard(document.getElementById(this.id));
                }
            );
        }
    }
);