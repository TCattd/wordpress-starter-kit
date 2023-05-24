// simple redirect
if ('undefined' == typeof hfcm_location ) {
    var hfcm_location = {url:''};
}
window.location.replace(hfcm_location.url);