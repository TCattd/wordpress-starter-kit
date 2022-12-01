jQuery( function($) {

    // identify which Theme Preview modal to display
    $('.wppb-ul-theme-preview').click(function (e) {
        let themeID = e.target.id.replace('-info', '');
        displayThemePreviewModal(themeID);
    });

    // identify which Theme to mark as Active
    $('.wppb-ul-theme-activate button.activate').click(function ( element ) {
        let themeID, ajaxURL, currentPost;
        themeID = $(element.target).data('theme-id');
        ajaxURL = $(element.target).data('ajax-url');
        currentPost = $(element.target).data('current-post');

        // disable Activate Theme and Update/Publish buttons until data is being set
        $('#activate-' + themeID).addClass('disabled');
        $('#publish').addClass('disabled');

        setNewTheme( ajaxURL, themeID, currentPost );
    });

    // open Theme Reset Modal
    $('.wppb-ul-theme-activate button.reset').click(function (e) {
        let themeID = e.target.id.replace('reset-', '');
        displayResetDialog( themeID );
    });

    // close Theme Reset Modal
    $('.wppb-ul-theme-reset-modal button.cancel-reset').click(function (e) {
        let modalID = '#' + e.target.value;
        closeResetModal( modalID );
    });

    // select/deselect options
    $('.wppb-ul-theme-reset-modal label').click(function (e) {
        e.preventDefault();
        let checkboxID ='#' + this.attributes['for'].value,
            parentID = '#' + $(this).parent().attr('id');

        // select and disable all options
        if ( checkboxID === '#all-theme-data' ) {
            if ($(parentID + ' input[name=reset_all_theme_data]').prop('checked')) {
                $(parentID + ' input[type=checkbox]').prop('checked', false);
                $(parentID + ' input[name=reset_theme_data]').removeAttr("disabled");
                $(parentID + ' input[name=reset_theme_data]').parent().css("pointer-events", 'auto');
            }
            else {
                $(parentID + ' input[type=checkbox]').prop('checked', true);
                $(parentID + ' input[name=reset_theme_data]').attr("disabled", true);
                $(parentID + ' input[name=reset_theme_data]').parent().css("pointer-events", 'none');
            }
        }
        // select/deselect an option
        else {
            if ($(parentID + ' input[name=reset_theme_data]' + checkboxID).prop('checked'))
                $(parentID + ' input[name=reset_theme_data]' + checkboxID).prop( 'checked', false);
            else $(parentID + ' input[name=reset_theme_data]' + checkboxID).prop('checked', true);
        }
    });

    // reset Theme data
    $('.wppb-ul-theme-reset-modal button.confirm-reset').click(function (e) {
        let themeID, ajaxURL, currentPost;
        themeID = $(e.target).data('theme-id');
        ajaxURL = $(e.target).data('ajax-url');
        currentPost = $(e.target).data('current-post');
        resetThemeData( ajaxURL, themeID, currentPost );
    });

    // delete the file loaded from WP Theme markers when resetting the UL Theme settings if the file is missing
    if ( sessionStorage.getItem('temporarily_disabled_all_users_wp_theme_file_marker') === 'yes' )
        sessionStorage.removeItem('temporarily_disabled_all_users_wp_theme_file_marker');

    if ( sessionStorage.getItem('temporarily_disabled_single_user_wp_theme_file_marker') === 'yes' )
        sessionStorage.removeItem('temporarily_disabled_single_user_wp_theme_file_marker');

    $('#publish').click(function() {
        if ( sessionStorage.getItem('temporarily_disabled_all_users_wp_theme_file_marker') === 'yes' ) {
            localStorage.removeItem('all_users_wp_theme_file');
            localStorage.removeItem('all_users_editor_overlay');
            sessionStorage.removeItem('temporarily_disabled_all_users_wp_theme_file_marker');
        }

        if ( sessionStorage.getItem('temporarily_disabled_single_user_wp_theme_file_marker') === 'yes' ) {
            localStorage.removeItem('single_user_wp_theme_file');
            localStorage.removeItem('single_user_editor_overlay');
            sessionStorage.removeItem('temporarily_disabled_single_user_wp_theme_file_marker');
        }
    });

    // set markers if the template was loaded from the WP active Theme files
    if ( localStorage.getItem('all_users_wp_theme_file') !== '' && localStorage.getItem('all_users_wp_theme_file') != null )
        jQuery('#wppb-ul-templates h2.hndle').after(localStorage.getItem('all_users_wp_theme_file'));

    if ( localStorage.getItem('single_user_wp_theme_file') !== '' && localStorage.getItem('single_user_wp_theme_file') != null )
        jQuery('#wppb-single-ul-templates h2.hndle').after(localStorage.getItem('single_user_wp_theme_file'));

    if ( localStorage.getItem('all_users_editor_overlay') !== '' && localStorage.getItem('all_users_editor_overlay') != null )
        jQuery('#wppb-ul-templates').prepend(localStorage.getItem('all_users_editor_overlay'));

    if ( localStorage.getItem('single_user_editor_overlay') !== '' && localStorage.getItem('single_user_editor_overlay') != null )
        jQuery('#wppb-single-ul-templates').prepend(localStorage.getItem('single_user_editor_overlay'));


    // reposition the Update/Publish button/section
    wppbRepositionUpdateButton();

});


/**
 * Modal for displaying user notifications
 */
function userNotification( message, title ) {
    if ( !title )
        title = 'User Listing';

    jQuery( "body" ).append( "<div id='wppb-user-notification' title='"+ title +"'>" + message +  "</div>" );

    jQuery('#wppb-user-notification').dialog({
        resizable: false,
        width: 450,
        height: 'auto',
        modal: true,
        closeOnEscape: true,
        close: function( event, ui ) {
            jQuery('#wppb-user-notification').remove();
        },
        'buttons': {
            'Close': function () {
                jQuery('#wppb-user-notification').dialog('close');
            }
        }
    });

    return false;
}


/**
 * Display Theme Preview Modal
 */
function displayThemePreviewModal( themeID ) {
    jQuery('#modal-' + themeID).dialog({
        resizable: false,
        height: 'auto',
        width: 1200,
        modal: true,
        closeOnEscape: true,
        open: function () {
            jQuery('.ui-widget-overlay').bind('click',function () {
                jQuery('#modal-' + themeID).dialog('close');
            })
        }
    });
    return false;
}

/**
 * Display Theme Reset Modal
 */
function displayResetDialog( themeID ) {
    jQuery('#modal-reset-' + themeID).dialog({
        resizable: false,
        width: 550,
        height: 400,
        modal: true,
        closeOnEscape: true
    });
    return false;
}

/**
 * Close Theme Reset Modal with "close" button
 */
function closeResetModal( modalID ) {
    jQuery(modalID + ' input[type=checkbox]').prop( 'checked', false );
    jQuery(modalID + ' input[type=checkbox]').removeAttr('disabled');
    jQuery(modalID + ' input[type=checkbox]').parent().css('pointer-events', 'auto');
    jQuery(modalID).dialog('close');
}

/**
 * Reset Theme Data
 */
function resetThemeData( ajaxURL, themeID, currentPost ) {

    let options = jQuery('input[name=reset_theme_data]:checkbox:checked').map(function(){
        return jQuery(this).val();
    }).get();

    jQuery.ajax({
        type: 'get',
        dataType: 'json',
        url: ajaxURL,
        data: {
            action:'get_selected_theme_default_data',
            theme_id: themeID,
            current_post: currentPost,
            options_to_reset : options
        },
        success: function( response ) {
            setNewData(response);
            let all_users_wp_theme_file_loaded = '',
                single_users_wp_theme_file_loaded = '',
                settings_loaded_message = '';


            // delete the file loaded from WP Theme markers when resetting the UL Theme settings if the file is missing
            if ( response['all_users'] && response['all_users_wp_theme_file'] === 'no' ) {
                setTemplate('all_users', response['all_users']);
                jQuery('#wppb-all-users-file-path').remove();
                jQuery('#wppb-all-users-file-label').remove();
                jQuery('.wppb-all-users-editor-overlay').remove();
                sessionStorage.setItem('temporarily_disabled_all_users_wp_theme_file_marker', 'yes');
            }
            else if ( response['all_users'] && response['all_users_wp_theme_file'] === 'yes' )
                all_users_wp_theme_file_loaded = '<p><span style="color: #e76054; font-weight: bold">Note:</span> <span style="font-style: italic;">All-userlisting Template</span> is loaded from your currently active Wordpress Theme and was not overwritten.</p>';

            if ( response['single_user'] && response['single_user_wp_theme_file'] === 'no' ) {
                setTemplate('single_user', response['single_user']);
                jQuery('#wppb-single-users-file-path').remove();
                jQuery('#wppb-single-users-file-label').remove();
                jQuery('.wppb-single-user-editor-overlay').remove();
                sessionStorage.setItem('temporarily_disabled_single_user_wp_theme_file_marker', 'yes');
            }
            else if ( response['single_user'] && response['single_user_wp_theme_file'] === 'yes' )
                single_users_wp_theme_file_loaded = '<p><span style="color: #e76054; font-weight: bold">Note:</span> <span style="font-style: italic;">Single-userlisting Template</span> is loaded from your currently active Wordpress Theme and was not overwritten.</p>';


            settings_loaded_message = '<p><strong>' + themeID.toUpperCase() + '</strong> Theme selected Default Settings were successfully loaded and will be saved when the Post is Updated.</p>';

            if ( all_users_wp_theme_file_loaded !== '' )
                settings_loaded_message = settings_loaded_message + all_users_wp_theme_file_loaded;

            if ( single_users_wp_theme_file_loaded !== '' )
                settings_loaded_message = settings_loaded_message + single_users_wp_theme_file_loaded;

            userNotification(settings_loaded_message, 'User Listing');

        },
        error: function(message) {
            userNotification('<p>'+ message.responseText +'</p>', 'User Listing');
        }
    });

    closeResetModal('#modal-reset-' + themeID);
}

/**
 * Set new Theme Data and Templates
 */
function setNewTheme( ajaxURL, themeID, currentPost ) {

    jQuery.ajax({
        type: 'get',
        dataType: 'json',
        url: ajaxURL,
        data: {
            action:'get_new_templates_data',
            theme_id: themeID,
            current_post: currentPost
        },
        success: function( response ) {

            setNewData( response );

            if ( response['single_user']  )
                setTemplate('single_user',response['single_user']);

            if ( response['all_users'] )
                setTemplate('all_users',response['all_users']);

            // add loaded file path to template editor title and add code editor overlay (template loaded from WP Theme files)
            if (response['all_users_wp_theme_file']) {
                localStorage.setItem('all_users_wp_theme_file','<span id="wppb-all-users-file-label">Loaded from:</span> <span id="wppb-all-users-file-path">' + response['all_users_wp_theme_file'] + '</span>');
                localStorage.setItem('all_users_editor_overlay','<div class="wppb-all-users-editor-overlay" onclick="userNotification(\'<p>You can make changes for this template by editing the corresponding file within your active WordPress Theme.</p>\');"></div>');
                jQuery('#wppb-ul-templates h2.hndle').after(localStorage.getItem('all_users_wp_theme_file'));
                jQuery('#wppb-ul-templates').prepend(localStorage.getItem('all_users_editor_overlay'));
            }
            else {
                jQuery('#wppb-all-users-file-path').remove();
                jQuery('#wppb-all-users-file-label').remove();
                jQuery('.wppb-all-users-editor-overlay').remove();
                localStorage.removeItem('all_users_wp_theme_file');
                localStorage.removeItem('all_users_editor_overlay');
            }

            // add loaded file path to template editor title and add code editor overlay (template loaded from WP Theme files)
            if (response['single_user_wp_theme_file']) {
                localStorage.setItem('single_user_wp_theme_file','<span id="wppb-single-users-file-label">Loaded from:</span> <span id="wppb-single-users-file-path">' + response['single_user_wp_theme_file'] + '</span>');
                localStorage.setItem('single_user_editor_overlay','<div class="wppb-single-user-editor-overlay" onclick="userNotification(\'<p>You can make changes for this template by editing the corresponding file within your active WordPress Theme.</p>\');"></div>');
                jQuery('#wppb-single-ul-templates h2.hndle').after(localStorage.getItem('single_user_wp_theme_file'));
                jQuery('#wppb-single-ul-templates').prepend(localStorage.getItem('single_user_editor_overlay'));
            }
            else {
                jQuery('#wppb-single-users-file-path').remove();
                jQuery('#wppb-single-users-file-label').remove();
                jQuery('.wppb-single-user-editor-overlay').remove();
                localStorage.removeItem('single_user_wp_theme_file');
                localStorage.removeItem('single_user_editor_overlay');
            }

            let i, allTemplates;
            allTemplates = jQuery('.wppb-ul-theme');
            for (i = 0; i < allTemplates.length; i++) {
                if ( jQuery(allTemplates[i]).hasClass('active')) {
                    jQuery('.wppb-ul-theme-title strong', allTemplates[i] ).hide();
                    jQuery(allTemplates[i]).removeClass('active');
                }
            }
            jQuery('#' + themeID).addClass('active');

            // remove disabled class from buttons
            jQuery('#activate-' + themeID).removeClass('disabled');
            jQuery('#publish').removeClass('disabled');

        },
        error: function(message) {
            userNotification('<p>'+ message.responseText +'</p>', 'User Listing');
        }
    });

}

/**
 * Replace User-Listing settings
 */
function setNewData( response ) {
    // set roles to display
    if ( response['roles_to_display'] ) {
        let allPossibleRoles = jQuery('.row-roles-to-display input[type="checkbox"]');

        allPossibleRoles.each(function() {
            let role = this.value;
            this.checked = response['roles_to_display'].includes(role) ? true : false;
        })
    }

    // set number of users per page
    if ( response['users_per_page']  )
        jQuery('#number-of-userspage').val(response['users_per_page']);

    // set sorting options
    if ( response['sorting_criteria']  )
        jQuery('#default-sorting-criteria').val(response['sorting_criteria']);
    if ( response['sorting_order']  )
        jQuery('#default-sorting-order').val(response['sorting_order']);

    // set avatar sizes
    if ( response['all_users_avatar_size']  )
        jQuery('#avatar-size-all-userlisting').val(response['all_users_avatar_size']);
    if ( response['single_user_avatar_size']  )
        jQuery('#avatar-size-single-userlisting').val(response['single_user_avatar_size']);
}

/**
 * Replace templates in Code Mirror Editor
 */
function setTemplate( key, value ) {
    let textarea, codeMirrorObj, codeMirrorEditor;

    if (key==='all_users')
        textarea = jQuery('#wppb-ul-templates.wppb_mustache_template');
    else if (key==='single_user')
        textarea = jQuery('#wppb-single-ul-templates.wppb_mustache_template');

    codeMirrorObj = textarea.next('.CodeMirror');
    codeMirrorEditor = codeMirrorObj.get(0).CodeMirror;
    codeMirrorEditor.setValue(value);
}

/**
 * Reposition the Update/Publish button/section on scroll in Admin Dashboard
 */
function wppbRepositionUpdateButton() {
    if ( jQuery( '#wppb-ul-themes-settings' ).length ) {
        let elementOffset = jQuery('#side-sortables').offset().top,
            mobileScreen  = window.matchMedia("(max-width: 850px)"),
            breakPoint    = 0;

        if (mobileScreen.matches)
            breakPoint = elementOffset + 115; // 115px buttons container height
        else breakPoint = elementOffset - 32; // 32px admin bar height

        jQuery(window).on('scroll', function() {
            if ( jQuery(window).scrollTop() >= (breakPoint) )
                jQuery('#side-sortables').addClass('wppb-fixed-position');
            else jQuery('#side-sortables').removeClass('wppb-fixed-position');
        });
    }
}