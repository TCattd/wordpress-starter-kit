jQuery( function() {
    jQuery('.wppb-table .wppb-sorting').each( function() {
        $sortLink = jQuery(this).children('.sortLink');

        if( $sortLink.length > 0 ) {
            jQuery(this).append('<span class="wppb-sorting-default"></span>');

            if( $sortLink.hasClass('sort-asc') ) {
                $sortLink.siblings('.wppb-sorting-default').addClass('wppb-sorting-ascending');
            }

            if( $sortLink.hasClass('sort-desc') ) {
                $sortLink.siblings('.wppb-sorting-default').addClass('wppb-sorting-descending');
            }
        }
    });

    /* js handle for the select facet filter */
    jQuery(document).on('change', '.wppb-facet-select, .wppb-facet-select-multiple', function() {
        if( jQuery(this).val() == '' ){
            return;
        }
        wppbHandleFacet(jQuery(this));
    });

    /* js handle for the checkbox facet filter */
    jQuery(document).on('click', '.wppb-facet-checkbox', function() {
        wppbHandleFacet(jQuery(this));
    });

    /* js handle for the search facet filter */
    jQuery(document).on( 'keydown', '.wppb-facet-search', function (e){
        if( jQuery(this).val() == '' ){
            return;
        }
        if(e.keyCode == 13){
            wppbHandleFacet(jQuery(this));
        }
    });
    jQuery(document).on( 'click', '.wppb-search-submit', function (){
        if( jQuery(this).siblings('.wppb-facet-search').val() == '' ){
           return;
        }

        wppbHandleFacet(jQuery(this).siblings('.wppb-facet-search'));
    });

    /* js handle for the range facet filter has it's own function */


    /* remove individual facet value */
    jQuery(document).on('click', '.wppb-remove-facet', function(e) {
        e.preventDefault();

        jQuery('.wppb-userlisting-container').addClass('wppb-spinner');

        paramName = 'ul_filter_' + jQuery( this).attr('data-meta-name');
        paramValue = jQuery( this).attr('data-meta-value');

        /* remove page arg first */
        currentPage = jQuery( this).attr('data-current-page');
        if( currentPage > 1 )
            url = wppbRemovePageFromUrl( window.location.href, currentPage );
        else
            url = window.location.href;

        //handle # in urls...they need to be at the end
        if( window.location.hash != '' ){
            url = url.replace( window.location.hash, '' );
        }

        url = wppbRemoveURLParameter( url, paramName, paramValue );
        /* WooSync handle that removes state when country is removed */
        url = wppbWooSyncHandleUrl( url, paramName );

        //handle # in urls...they need to be at the end
        if( window.location.hash != '' ){
            url = url + window.location.hash;
        }

        wppbGetFacetPage( url, false, paramName );
    });

    /* remove all filters */
    jQuery(document).on('click', '.wppb-remove-all-facets', function(e) {
        e.preventDefault();

        jQuery('.wppb-userlisting-container').addClass('wppb-spinner');

        allParams = jQuery( this).attr('data-all-filters');

        /* remove page arg first */
        currentPage = jQuery( this).attr('data-current-page');
        if( currentPage > 1 )
            url = wppbRemovePageFromUrl( window.location.href, currentPage );
        else
            url = window.location.href;
        
        //handle # in urls...they need to be at the end
        if( window.location.hash != '' ){
            url = url.replace( window.location.hash, '' );
        }

        var allParamsParts= allParams.split(',');
        url = wppbRemoveAllURLFacets( url, allParamsParts );

        //handle # in urls...they need to be at the end
        if( window.location.hash != '' ){
            url = url + window.location.hash;
        }

        wppbGetFacetPage( url, false, '' );

    });


    /* show all checkboxes in facet */
    jQuery(document).on('click', '.show-all-facets', function(e) {
        e.preventDefault();
        jQuery( '.hide-this', jQuery( this).closest('li') ).show();
        jQuery( '.hide-all-facets', jQuery( this).closest('li') ).show();
        jQuery( this ).hide();
    });

    /* hide checkboxes in a facet */
    jQuery(document).on('click', '.hide-all-facets', function(e) {
        e.preventDefault();
        jQuery( '.hide-this', jQuery( this).closest('li') ).hide();
        jQuery( this ).hide();
        jQuery( '.show-all-facets', jQuery( this).closest('li') ).show();
    });


    /* Show/Hide Filters for User-Listing Vergrid Theme (all users template)  */
    jQuery(document).on('click', '.wppb-ul-filter-button', function() {

        if ( jQuery( '.wppb-ul-filter-button' ).hasClass( 'active' ) ) {
            localStorage.removeItem('wppb_ul_filters_button_class');
            jQuery( '.wppb-ul-filter-button' ).removeClass( 'active' );
            jQuery( '.wppb-ul-filters' ).hide(300);
        }
        else {
            localStorage.setItem('wppb_ul_filters_button_class', 'active');
            jQuery( '.wppb-ul-filter-button' ).addClass( 'active' );
            jQuery( '.wppb-ul-filters' ).show(300);
        }

    });

    wppbMaybeShowFilters();

});


/**
 * Function that displays the User-Listing Theme Filters if activated
 */
function wppbMaybeShowFilters() {
    if ( jQuery( '.wppb-faceted-list' ).length === 0 )
        jQuery( '.wppb-ul-filter-button' ).hide();

    let filter_button_state = localStorage.getItem('wppb_ul_filters_button_class');

    if ( filter_button_state === 'active' ) {
        jQuery( '.wppb-ul-filter-button' ).addClass( filter_button_state );
        jQuery( '.wppb-ul-filters' ).show();
    }
}

/**
 * Function that activates Tab and shows content for User-Listing Glimplist Theme (single user template)
 */
function activateTab( activeTab, activeContent ) {
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("wppb-ul-tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("wppb-ul-tab-title");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(activeContent).style.display = "block";
    document.getElementById(activeTab).className += " active";
}

/**
 * Function for scrolling User-Listing Glimplist Theme (single user template) Tabs
 *
 */
jQuery(function($) {
    let scrollContainer = $('#wppb-ul-theme-glimplist .wppb-ul-headers'),
        items = scrollContainer.children();

    if ( typeof scrollContainer == 'undefined' || !(scrollContainer.length > 0) )
        return;

    if ( scrollContainer[0].offsetWidth < scrollContainer[0].scrollWidth) {
        scrollContainer.before('<div id="wppb-ul-themes-left-button" style="display: none"><a href="#"><</a></div>');
        scrollContainer.after('<div id="wppb-ul-themes-right-button"><a href="#">></a></div>');
    }

    $('#wppb-ul-themes-right-button').click(function(event) {
        event.preventDefault();

        items.each(function (){
            let left = Math.round( $(this).position().left ),
                width = Math.round( $(this).width() ),
                right = left + width,
                nextElement = this.nextElementSibling;

            if ( nextElement != null && left <= 0 && right > 0 ) {
                nextElement.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'});

                $('#wppb-ul-themes-left-button').show();

                if ( left < 0 && right > 0 )
                    $('#wppb-ul-themes-right-button').hide();
            }

        });

    });

    $('#wppb-ul-themes-left-button').click(function(event) {
        event.preventDefault();

        items.each(function (){
            let left = Math.round( $(this).position().left ),
                width = Math.round( $(this).width() ),
                right = left + width,
                prevElement = this.previousElementSibling;

            if ( prevElement != null && left <= 0 && right > 0 ) {
                prevElement.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'});

                $('#wppb-ul-themes-right-button').show();

                if ( prevElement.previousElementSibling == null )
                    $('#wppb-ul-themes-left-button').hide();
            }

        });

    });

    return false;

});




function wppbHandleFacet( facetObj ){
    console.log(facetObj);
    jQuery('.wppb-userlisting-container').addClass('wppb-spinner');
    var setParam = true;

    if( facetObj.hasClass('wppb-facet-checkbox') )
        setParam= facetObj.is(':checked');


    paramName = 'ul_filter_' + facetObj.attr('data-meta-name');
    paramBehaviour = facetObj.attr('data-filter-behaviour');
    
    /* we need to handle select multiple and range differently */
    if( facetObj.hasClass('wppb-facet-select-multiple') ){
        /* if the val is an object then we have an expand functionality and we need to treat it differently: set the behaviour to narrow and combine the value object as an expand value
          *so we basically fake the behaviour. This is because of the way the multiple select behaves when clicking 
        **/
        if( typeof facetObj.val() == 'object' ){
            paramBehaviour = 'narrow';
            if( facetObj.val().length > 1 ){
                paramValue = encodeURIComponent( facetObj.val().join('||') );
            }
            else{
                paramValue = encodeURIComponent( facetObj.val() );
            }
        }
        else{
            paramValue = encodeURIComponent( facetObj.val() );
        }
    }
    else if( facetObj.hasClass('wppb-ul-slider-range') ){
        paramValue = encodeURIComponent( jQuery( facetObj ).slider( "values" ).join('-') );
    }
    else{
        paramValue = encodeURIComponent( facetObj.val() );
    }



    currentPage = facetObj.attr('data-current-page');
    if( currentPage > 1 )
        url = wppbRemovePageFromUrl( window.location.href, currentPage );
    else
        url = window.location.href;

    //handle # in urls...they need to be at the end
    if( window.location.hash != '' ){
        url = url.replace( window.location.hash, '' );
    }

    searchForAttr = facetObj.closest('.wppb-faceted-list').attr('data-search-for');
    if( searchForAttr != '' )
        url = wppbSetUrlParam( url, 'searchFor', searchForAttr, 'narrow' );

    if( setParam )
        url = wppbSetUrlParam( url, paramName, paramValue, paramBehaviour );
    else {
        url = wppbRemoveURLParameter(url, paramName, paramValue);
        /* WooSync handle that removes state when country is removed */
        url = wppbWooSyncHandleUrl( url, paramName );
    }

    //handle # in urls...they need to be at the end
    if( window.location.hash != '' ){
        url = url + window.location.hash;
    }

    wppbGetFacetPage( url, setParam, paramName );
}

/**
 * Function that retrieves a GET parameter from the current url or from a given url
 * @param name the name of the parameter
 * @param link optional url
 * @returns the value of the GET parameter
 */
function wppbGetUrlParam( name, link ){
    link = (typeof link === 'undefined') ? window.location.href : link;
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(link);
    if (results==null){
        return null;
    }
    else{
        return results[1] || 0;
    }
}


/**
 * Function that changes the value of a GET parameter
 * @param link the url in which we make the change
 * @param name the name of the parameter we want to change
 * @param value the new value
 * @param behaviour facet behaviour( expand, narrow ) for narrow we change it for expand we add it to the existing one
 * @returns {*}
 */
function wppbSetUrlParam( link, name, value, behaviour ){
    /* check if wh have any get parameters */
    if( link.search( "\\?" ) === -1 ){
        link = link + '?'+ name + '=' + value;
    }
    else{
        /* check to see if the parameter is already there */
        thisParamValue = wppbGetUrlParam( name, link );
        if( thisParamValue !== null ){
            if( behaviour == 'narrow' )
                link = link.replace( name+'='+thisParamValue, name+'='+value );
            else if( behaviour == 'expand' )
                link = link.replace( name+'='+thisParamValue, name+'='+thisParamValue+'||'+value );
        }
        else{
            link = link + '&'+ name + '=' + value;
        }
    }
    return link;
}

/* Function that removes the page arg from the url */
function wppbRemovePageFromUrl( link, page ){
    link = link.replace( '/'+page+'/', '/' );
    link = link.replace( '/page/', '/' );//this was added for frontpage where the query var page ads /page/ to the url
    link = link.replace( '/'+wppb_userlisting_obj.pageSlug+'/', '/' );//this was added for wp 5.5
    link = wppbRemoveURLParameter( link, 'page' );
    link = wppbRemoveURLParameter( link, wppb_userlisting_obj.pageSlug );//this was added for wp 5.5
    return link;
}

/**
 * Function that removes a GET parameter from the url
 * @param url the url from which we remove
 * @param parameter the name of the parameter
 * @param parameterValue the value of the parameter
 * @returns {*}
 */
function wppbRemoveURLParameter( url, parameter, parameterValue ){

    if( typeof parameterValue !== 'undefined' ){
        allParamValue = wppbGetUrlParam( parameter, url );
        decodedAllParamValue = decodeURIComponent( allParamValue );
        if( decodedAllParamValue.search( "\\|\\|" + parameterValue ) !== -1 ) {
            changedAllParamValue = encodeURIComponent( decodedAllParamValue.replace('||' + parameterValue, '') );
            url = url.replace( allParamValue, changedAllParamValue );
            return url;
        }
        if( decodedAllParamValue.search( parameterValue + "\\|\\|" ) !== -1 ) {
            changedAllParamValue = encodeURIComponent( decodedAllParamValue.replace( parameterValue + '||', '') );
            url = url.replace( allParamValue, changedAllParamValue );
            return url;
        }
    }

    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');
    if (urlparts.length>=2) {
        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}

/**
 * Function that removes all the facet parameters
 * @param url the url from which we remove
 * @param allParams an array with all the facet parameters
 * @returns {*} the link without facet parameters
 */
function wppbRemoveAllURLFacets( url, allParams ){
    for (var index = 0; index < allParams.length; ++index ) {
        url = wppbRemoveURLParameter( url, 'ul_filter_'+ allParams[index] );
    }
    return url;
}

/**
 * Function for handling the range filter
 * @param facetMeta the mata name of the filter
 * @param firstValue first value of the interval
 * @param lastValue last value of the interval
 * @param firstCurrentValue first selected value
 * @param lastCurrentValue last selected value
 */
function wppbRangeFacet( facetMeta, firstValue, lastValue, firstCurrentValue, lastCurrentValue ){
    jQuery( ".wppb-ul-slider-range."+ facetMeta ).slider({
        range: true,
        min: firstValue,
        max: lastValue,
        values: [ firstCurrentValue, lastCurrentValue ],
        slide: function( event, ui ) {
            jQuery( ".wppb-ul-range-values."+facetMeta ).text( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
            jQuery( this ).attr("value", ui.values[ 0 ] + "-" + ui.values[ 1 ]);
        },
        stop: function( event, ui ){
            wppbHandleFacet( jQuery(this) );
        }
    });
}

/**
 *
 * @param url the url from which we get the result
 * @param setParam bool if we added a parameter in the url or not
 * @param paramName the parameter that was added
 */
function wppbGetFacetPage( url, setParam, paramName ) {
    jQuery.get(url, function (response) {
        found = jQuery('.wppb-userlisting-container', jQuery(response));
        jQuery('.wppb-userlisting-container').html(found.html());
        window.history.pushState({}, '', url);
        jQuery('.wppb-userlisting-container').removeClass('wppb-spinner');

        if (setParam)
            jQuery('.wppb-faceted-list').trigger("wppbFacetSetGetCompleted", [url, paramName]);
        else
            jQuery('.wppb-faceted-list').trigger("wppbFacetRemoveGetCompleted", [url, paramName]);

        wppbMaybeShowFilters();
    });
}

/**
 * Function that removes the state if the country is removed on WooSync facets
 * @param url
 * @param paramName
 * @returns {*}
 */
function wppbWooSyncHandleUrl( url, paramName ){
    if (paramName == 'ul_filter_billing_country')
        url = wppbRemoveURLParameter(url, 'ul_filter_billing_state');
    else if (paramName == 'ul_filter_shipping_country')
        url = wppbRemoveURLParameter(url, 'ul_filter_shipping_state');

    return url;
}