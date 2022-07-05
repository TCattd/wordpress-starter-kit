var wppb_re_capabilities_group = [];
var wppb_re_new_capabilities = {};
var wppb_re_current_role_capabilities = jQuery.extend( {}, wppb_roles_editor_data.current_role_capabilities );
var wppb_re_unsaved_capabilities = {};
var wppb_re_capabilities_to_delete = {};

jQuery( document ).ready( function() {
    // Disable Enter key
    jQuery( window ).on( 'keydown', function( e ) {

        if( typeof e.target.id !== "undefined" && e.target.id === 'post-search-input' )
            return true;

        if( e.keyCode == 13 ) {
            event.preventDefault();
            return false;
        }
    } );

    // Disable the role title field when editing a role
    if( wppb_roles_editor_data.current_screen_action != 'add' ) {
        jQuery( '.post-type-wppb-roles-editor input#title' ).attr( 'disabled', 'disabled' );
    }

    var table_roles = jQuery( '.post-type-wppb-roles-editor .wp-list-table.posts tr .row-actions' );
    if( jQuery( table_roles ).find( '.default_role' ) ) {
        jQuery( '<span class="table-role-info"> — ' + wppb_roles_editor_data.default_role_text + '</span>' ).insertAfter( jQuery( table_roles ).find( '.default_role' ).parent().parent().find( 'strong .row-title' ) );
    }
    if( jQuery( table_roles ).find( '.delete_notify.your_role' ) ) {
        jQuery( '<span class="table-role-info"> — ' + wppb_roles_editor_data.your_role_text + '</span>' ).insertAfter( jQuery( table_roles ).find( '.delete_notify.your_role' ).parent().parent().find( 'strong .row-title' ) );
    }

    // Dynamically change value of the Role Slug field
    jQuery( '.post-type-wppb-roles-editor #titlewrap' ).find( '#title' ).on('change', function() {
        if( ! jQuery( '.post-type-wppb-roles-editor #wppb-role-slug' ).val() ) {
            jQuery( '.post-type-wppb-roles-editor #wppb-role-slug' ).val( jQuery( this ).val().toLowerCase() );
        }
    } );

    // Create an object with grouped capabilities for the Add Capability select2
    var counter = 1;
    jQuery.each( wppb_roles_editor_data.capabilities, function( key, value ) {
        var capabilities_single_group = {};
        if( key != 'post_types' ) {
            capabilities_single_group = wppb_re_create_capabilities_group( key, value, counter );
            wppb_re_capabilities_group.push( capabilities_single_group );
            counter++;
        } else if( key == 'post_types' ) {
            jQuery.each( value, function( key, value ) {
                capabilities_single_group = wppb_re_create_capabilities_group( key, value, counter );
                wppb_re_capabilities_group.push( capabilities_single_group );
                counter++;
            } );
        }
    } );

    // Display the current role capabilities (on single role page)
    wppb_re_display_capabilities( 'all' );

    // Check for already added capabilities and disable them before select2 initialization
    wppb_re_disable_select_capabilities( wppb_re_capabilities_group, wppb_roles_editor_data.current_role_capabilities, 'add' );

    if( wppb_re_getParameterByName( 'wppb_re_clone' ) ) {
        var data = {
            'action'    : 'get_role_capabilities',
            'security'  : jQuery( '.post-type-wppb-roles-editor #wppb-re-ajax-nonce' ).val(),
            'role'      : wppb_re_getParameterByName( 'wppb_re_clone' )
        };

        jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-no-cap' ).remove();

        jQuery.post( wppb_roles_editor_data.ajaxUrl, data, function( response ) {
            if( response != 'no_caps' ) {
                jQuery( '.post-type-wppb-roles-editor .wppb-re-spinner-container' ).hide();
                wppb_re_current_role_capabilities = jQuery.extend( wppb_re_current_role_capabilities, JSON.parse( response ) );
                wppb_re_display_capabilities( 'all' );
                wppb_re_disable_select_capabilities( wppb_re_capabilities_group, wppb_re_current_role_capabilities, 'add' );
            } else {
                jQuery( '.wppb-re-spinner-container' ).hide();
                wppb_re_no_capabilities_found();
            }
        } );
    }

    // Delete a capability
    jQuery( '.post-type-wppb-roles-editor #wppb-role-edit-table' ).on( 'click', 'a.wppb-delete-capability-link', function() {
        if( ( wppb_roles_editor_data.current_user_role && jQuery.inArray( jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text(), wppb_roles_editor_data.admin_capabilities ) === -1 ) || ! wppb_roles_editor_data.current_user_role ) {
            jQuery( this ).closest( 'div.wppb-role-edit-table-entry' ).remove();

            var deleted_capability = {};
            deleted_capability[jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text()] = jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text();
            wppb_re_capabilities_to_delete[jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text()] = jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text();

            delete wppb_re_current_role_capabilities[jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text()];
            delete wppb_re_new_capabilities[jQuery( this ).closest( 'span.wppb-delete-capability' ).siblings( 'span.wppb-capability' ).text()];

            if( jQuery( '.wppb-add-new-cap-input' ).is( ':visible' ) ) {
                wppb_re_change_select_to_input();
            }

            wppb_re_disable_select_capabilities( wppb_re_capabilities_group, deleted_capability, 'delete' );

            if( jQuery( '.wppb-role-edit-table-entry' ).length < 1 ) {
                wppb_re_no_capabilities_found();
            }

            wppb_re_number_of_capabilities();
        }
    } );

    // Change between select2 with all existing capabilities and input to add a new capability
    jQuery( '.post-type-wppb-roles-editor a.wppb-add-new-cap-link' ).on( 'click', function() {
        wppb_re_change_select_to_input();
    } );

    jQuery( '.post-type-wppb-roles-editor .wppb-role-editor-tab' ).on( 'click', function() {
        wppb_re_tabs_handler( jQuery( this ) );
    } );

    wppb_re_form_submit();

    wppb_re_number_of_capabilities();

    // Display number of users for current role
    if( wppb_roles_editor_data.current_role_users_count !== null ) {
        jQuery( '.post-type-wppb-roles-editor .misc-pub-section.misc-pub-section-users span' ).find( 'strong' ).text( wppb_roles_editor_data.current_role_users_count );
    }

    // Check if role has a title or return an error if not
    jQuery( 'body' ).on( 'submit.edit-post', '#post', function() {
        if( jQuery( '.post-type-wppb-roles-editor #title' ).val().replace( / /g, '' ).length === 0 ) {
            window.alert( wppb_roles_editor_data.role_name_required_error_text );
            jQuery( '.post-type-wppb-roles-editor #major-publishing-actions .spinner' ).hide();
            jQuery( '.post-type-wppb-roles-editor #major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );
            jQuery( '.post-type-wppb-roles-editor #title' ).trigger( 'focus' );

            wppb_re_form_submit();

            return false;
        } else {
            jQuery( '.post-type-wppb-roles-editor #major-publishing-actions .spinner' ).show();
        }
    } );
} );

function wppb_re_form_submit() {
    jQuery( '.post-type-wppb-roles-editor #publishing-action #publish' ).off( 'click' ).one( 'click', function( e ) {
        e.preventDefault();
        jQuery( this ).addClass( 'disabled' );
        jQuery( this ).siblings( '.spinner' ).addClass( 'is-active' );
        wppb_re_update_role_capabilities();
    } );
}

function wppb_re_no_capabilities_found() {
    jQuery( '.post-type-wppb-roles-editor #wppb-role-edit-table' ).find( '#wppb-role-edit-caps-clear' ).after(
        '<div class="wppb-role-edit-table-entry wppb-role-edit-no-cap">' +
            '<span class="wppb-capability wppb-role-edit-not-capability">' + wppb_roles_editor_data.no_capabilities_found_text + '</span>' +
        '</div>'
    );
}

function wppb_re_number_of_capabilities() {
    var count = 0;
    var i;

    for( i in wppb_re_current_role_capabilities ) {
        if( wppb_re_current_role_capabilities.hasOwnProperty( i ) ) {
            count++;
        }
    }

    jQuery( '.post-type-wppb-roles-editor .misc-pub-section.misc-pub-section-capabilities span' ).find( 'strong' ).text( count );
}

function wppb_re_tabs_handler( tab ) {
    wppb_re_display_capabilities( jQuery( tab ).data( 'wppb-re-tab' ) );

    jQuery( '.post-type-wppb-roles-editor .wppb-role-editor-tab-title.wppb-role-editor-tab-active' ).removeClass( 'wppb-role-editor-tab-active' );
    jQuery( tab ).closest( '.wppb-role-editor-tab-title' ).addClass( 'wppb-role-editor-tab-active' );
}

function wppb_re_disable_select_capabilities( wppb_re_capabilities_group, capabilities, action ) {
    if( capabilities != null ) {
        jQuery.each( wppb_re_capabilities_group, function( key, value ) {
            jQuery.each( value['children'], function( key, value ) {
                if( value['text'] in capabilities ) {
                    if( action == 'add' ) {
                        value['disabled'] = true;
                    } else if( action == 'delete' ) {
                        value['disabled'] = false;
                    }
                }
            } );
        } );
    }

    wppb_re_initialize_select2( wppb_re_capabilities_group );
}

function wppb_re_initialize_select2( wppb_re_capabilities_group ) {
    var capabilities_select = jQuery( '.wppb-capabilities-select' );

    capabilities_select.empty();
    capabilities_select.select2( {
        placeholder: wppb_roles_editor_data.select2_placeholder_text,
        allowClear: true,
        data: wppb_re_capabilities_group,
        templateResult: function( data ) {
            if( data.id == null || jQuery.inArray( data.text, wppb_roles_editor_data.capabilities['custom']['capabilities'] ) === -1 ) {
                return data.text;
            }

            var option = jQuery( '<span></span>' );
            var delete_cap = jQuery( '<a class="wppb-re-cap-perm-delete">' + wppb_roles_editor_data.delete_permanently_text + '</a>' );

            delete_cap.on( 'mouseup', function( event ) {
                event.stopPropagation();
            } );

            delete_cap.on( 'click', function( event ) {
                if( confirm( wppb_roles_editor_data.capability_text + ': ' + jQuery( this ).siblings( 'span' ).text() + '\n\n' + wppb_roles_editor_data.capability_perm_delete_text ) ) {
                    wppb_re_delete_capability_permanently( jQuery( this ).siblings( 'span' ).text() );
                }
            } );

            option.text( data.text );
            option = option.add( delete_cap );

            return option;
        }
    } );
}

function wppb_re_delete_capability_permanently( capability ) {
    var data = {
        'action'        : 'delete_capability_permanently',
        'security'      : jQuery( '.post-type-wppb-roles-editor #wppb-re-ajax-nonce' ).val(),
        'capability'    : capability
    };

    jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-table-entry' ).remove();
    jQuery( '.post-type-wppb-roles-editor .wppb-re-spinner-container' ).show();

    jQuery.post( wppb_roles_editor_data.ajaxUrl, data, function( response ) {
        window.location.reload();
    } );
}

function wppb_re_create_capabilities_group( key, value, counter ) {
    var capabilities_single_group_caps = {};
    var capabilities_single_group_caps_array = [];

    jQuery.each( value['capabilities'], function( key, value ) {
        capabilities_single_group_caps = {
            id: value + '_' + counter,
            text: value
        };

        capabilities_single_group_caps_array.push( capabilities_single_group_caps );
    } );

    return {
        category: key,
        text: value['label'],
        children: capabilities_single_group_caps_array
    };
}

function wppb_re_display_capabilities( action ) {
    jQuery( '.post-type-wppb-roles-editor .wppb-re-spinner-container' ).hide();
    jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-table-entry' ).remove();

    var capabilities;
    if( action == 'all' ) {
        capabilities = wppb_re_current_role_capabilities;
    } else {
        capabilities = wppb_re_capabilities_group;
    }

    jQuery.each( capabilities, function( key, value ) {
        var table = jQuery( '#wppb-role-edit-table' );

        if( action == 'all' ) {
            wppb_re_display_capability( key );
        } else {
            if( value['category'] == action ) {
                jQuery.each( value['children'], function( key, value ) {
                    if( wppb_re_current_role_capabilities != null && value['text'] in wppb_re_current_role_capabilities ) {
                        wppb_re_display_capability( value['text'] );
                    }
                } );
            }

            if( value['category'] == action && action == 'custom' ) {
                if( ! jQuery.isEmptyObject( wppb_re_new_capabilities ) ) {
                    jQuery.each( wppb_re_new_capabilities, function( key, value ) {
                        if( ! ( value in wppb_roles_editor_data.all_capabilities ) ) {
                            var new_capability_check = 0;
                            jQuery.each( wppb_roles_editor_data.capabilities, function( key2, value2 ) {
                                if( value2['label'] && value2['label'] != 'Custom' && jQuery.inArray( value, value2['capabilities'] ) !== -1 ) {
                                    new_capability_check++;
                                }
                            } );

                            if( new_capability_check == 0 ) {
                                wppb_re_display_capability( value );
                            }
                        }
                    } );
                }
            }
        }
    } );

    if( jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-table-entry' ).length ) {
        jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-no-cap' ).remove();
    } else {
        wppb_re_no_capabilities_found();
    }
}

function wppb_re_display_capability( capability ) {
    var title = '';
    var wppb_capability_class = 'wppb-capability';
    if( ! wppb_roles_editor_data.current_role_capabilities || ( wppb_roles_editor_data.current_role_capabilities && ! ( capability in wppb_roles_editor_data.current_role_capabilities ) ) ) {
        wppb_capability_class = wppb_capability_class + ' wppb-new-capability';
        title = 'title = "' + wppb_roles_editor_data.new_cap_update_title_text + '"';
    } else if( wppb_re_getParameterByName( 'wppb_re_clone' ) && ! wppb_roles_editor_data.current_role_capabilities ) {
        wppb_capability_class = wppb_capability_class + ' wppb-new-capability';
        title = 'title = "' + wppb_roles_editor_data.new_cap_publish_title_text + '"';
    }

    var delete_link = '<a class="wppb-delete-capability-link" href="javascript:void(0)">Delete</a>';
    if( wppb_roles_editor_data.current_user_role && jQuery.inArray( capability, wppb_roles_editor_data.admin_capabilities ) !== -1 ) {
        delete_link = '<span class="wppb-delete-capability-disabled" title="' + wppb_roles_editor_data.cap_no_delete_text + '">' + wppb_roles_editor_data.delete_text + '</span>';
    }

    jQuery( '.post-type-wppb-roles-editor #wppb-role-edit-table' ).find( '#wppb-role-edit-caps-clear' ).after(
        '<div class="wppb-role-edit-table-entry" ' + title + '>' +
            '<span class="' + wppb_capability_class + '">' + capability + '</span>' +
            '<span class="wppb-delete-capability">' + delete_link + '</span>' +
        '</div>'
    );
}

function wppb_re_add_capability() {
    var capabilities_select = jQuery( '.post-type-wppb-roles-editor .wppb-capabilities-select' );
    var new_capability_input = jQuery( '.post-type-wppb-roles-editor .wppb-add-new-cap-input' );
    var table = jQuery( '.post-type-wppb-roles-editor #wppb-role-edit-table' );
    var capabilities = {};
    var no_duplicates = {};

    if( jQuery( '.post-type-wppb-roles-editor .select2.select2-container' ).is( ':visible' ) && jQuery( capabilities_select ).val() != null ) {
        jQuery( capabilities_select ).find( 'option:selected' ).each( function() {
            if( ! no_duplicates[jQuery( this ).text()] ) {
                if( ! jQuery( '.post-type-wppb-roles-editor .wppb-role-editor-tab.wppb-role-editor-all' ).closest( 'li.wppb-role-editor-tab-title' ).hasClass( 'wppb-role-editor-tab-active' ) ) {
                    wppb_re_tabs_handler( jQuery( '.post-type-wppb-roles-editor .wppb-role-editor-tab.wppb-role-editor-all' ) );
                }

                var title = '';
                var wppb_capability_class = 'wppb-capability';
                if( ! wppb_roles_editor_data.current_role_capabilities || ( wppb_roles_editor_data.current_role_capabilities && ! ( jQuery( this ).text() in wppb_roles_editor_data.current_role_capabilities ) ) ) {
                    wppb_capability_class = wppb_capability_class + ' wppb-new-capability';
                    wppb_re_unsaved_capabilities[jQuery( this ).text()] = jQuery( this ).text();
                    title = 'title = "' + wppb_roles_editor_data.new_cap_update_title_text + '"';
                } else if( wppb_re_getParameterByName( 'wppb_re_clone' ) && ! wppb_roles_editor_data.current_role_capabilities ) {
                    wppb_capability_class = wppb_capability_class + ' wppb-new-capability';
                    title = 'title = "' + wppb_roles_editor_data.new_cap_publish_title_text + '"';
                }

                jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-no-cap' ).remove();

                jQuery( table ).find( '#wppb-role-edit-caps-clear' ).after(
                    '<div class="wppb-role-edit-table-entry wppb-new-capability-highlight" ' + title + '>' +
                        '<span class="' + wppb_capability_class + '">' + jQuery( this ).text() + '</span>' +
                        '<span class="wppb-delete-capability"><a class="wppb-delete-capability-link" href="javascript:void(0)">' + wppb_roles_editor_data.delete_text + '</a></span>' +
                    '</div>' );

                capabilities[jQuery( this ).text()] = jQuery( this ).text();
                no_duplicates[jQuery( this ).text()] = jQuery( this ).text();

                delete wppb_re_capabilities_to_delete[jQuery( this ).text()];
            }
        } );

        wppb_re_new_capability( capabilities );

        wppb_re_disable_select_capabilities( wppb_re_capabilities_group, capabilities, 'add' );

        jQuery( capabilities_select ).val( null ).trigger( 'change' );

        wppb_re_number_of_capabilities();

        setTimeout( function() {
            jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-table-entry' ).removeClass( 'wppb-new-capability-highlight' );
        }, 500 );
    } else if( jQuery( new_capability_input ).is( ':visible' ) && jQuery( new_capability_input ).val().length != 0 ) {
        var new_capability_value = jQuery( new_capability_input ).val();
        new_capability_value = new_capability_value.trim().replace( /<.*?>/g, '' ).replace( /\s/g, '_' ).replace( /[^a-zA-Z0-9_]/g, '' );

        if( new_capability_value && ( ! wppb_roles_editor_data.hidden_capabilities || ! ( new_capability_value in wppb_roles_editor_data.hidden_capabilities ) ) ) {
            if( ! ( new_capability_value in wppb_re_current_role_capabilities ) && ! ( new_capability_value in wppb_re_new_capabilities ) ) {
                wppb_re_tabs_handler( jQuery( '.post-type-wppb-roles-editor .wppb-role-editor-tab.wppb-role-editor-all' ) );

                var title = '';
                var wppb_capability_class = 'wppb-capability';
                if( ! wppb_roles_editor_data.current_role_capabilities || ( wppb_roles_editor_data.current_role_capabilities && ! ( new_capability_value in wppb_roles_editor_data.current_role_capabilities ) ) ) {
                    wppb_capability_class = wppb_capability_class + ' wppb-new-capability';
                    wppb_re_unsaved_capabilities[new_capability_value] = new_capability_value;
                    title = 'title = "' + wppb_roles_editor_data.new_cap_update_title_text + '"';
                } else if( wppb_re_getParameterByName( 'wppb_re_clone' ) && ! wppb_roles_editor_data.current_role_capabilities ) {
                    wppb_capability_class = wppb_capability_class + ' wppb-new-capability';
                    title = 'title = "' + wppb_roles_editor_data.new_cap_publish_title_text + '"';
                }

                jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-no-cap' ).remove();

                jQuery( table ).find( '#wppb-role-edit-caps-clear' ).after(
                    '<div class="wppb-role-edit-table-entry wppb-new-capability-highlight" ' + title + '>' +
                        '<span class="' + wppb_capability_class + '">' + new_capability_value + '</span>' +
                        '<span class="wppb-delete-capability"><a class="wppb-delete-capability-link" href="javascript:void(0)">' + wppb_roles_editor_data.delete_text + '</a></span>' +
                    '</div>' );

                capabilities[new_capability_value] = new_capability_value;
                delete wppb_re_capabilities_to_delete[new_capability_value];

                wppb_re_change_select_to_input();

                wppb_re_new_capability( capabilities );

                wppb_re_disable_select_capabilities( wppb_re_capabilities_group, capabilities, 'add' );

                jQuery( new_capability_input ).val( '' );

                wppb_re_number_of_capabilities();

                setTimeout( function() {
                    jQuery( '.post-type-wppb-roles-editor .wppb-role-edit-table-entry' ).removeClass( 'wppb-new-capability-highlight' );
                }, 500 );
            } else {
                jQuery( new_capability_input ).val( '' );

                jQuery( '.post-type-wppb-roles-editor #wppb-duplicate-capability-error' ).show().delay( 3000 ).fadeOut();
            }
        } else if( wppb_roles_editor_data.hidden_capabilities && new_capability_value in wppb_roles_editor_data.hidden_capabilities ) {
            jQuery( '.post-type-wppb-roles-editor #wppb-hidden-capability-error' ).show().delay( 3000 ).fadeOut();
        } else {
            jQuery( '.post-type-wppb-roles-editor #wppb-add-capability-error' ).show().delay( 3000 ).fadeOut();
        }
    } else {
        jQuery( '.post-type-wppb-roles-editor #wppb-add-capability-error' ).show().delay( 3000 ).fadeOut();
    }
}

function wppb_re_new_capability( capabilities ) {
    jQuery.each( capabilities, function( key, value ) {
        if( ! ( value in wppb_roles_editor_data.all_capabilities ) || ! ( value in wppb_re_current_role_capabilities ) ) {
            wppb_re_new_capabilities[value] = value;
        }
    } );

    jQuery.extend( wppb_re_current_role_capabilities, wppb_re_new_capabilities );
}

function wppb_re_update_role_capabilities() {
    jQuery( '.post-type-wppb-roles-editor #wppb-role-slug-hidden' ).val( jQuery( '#wppb-role-slug' ).val() );

    var data = {
        'action'                    : 'update_role_capabilities',
        'security'                  : jQuery( '.post-type-wppb-roles-editor #wppb-re-ajax-nonce' ).val(),
        'role_display_name'         : jQuery( '.post-type-wppb-roles-editor #titlediv' ).find( '#title' ).val(),
        'role'                      : jQuery( '.post-type-wppb-roles-editor #wppb-role-slug' ).val(),
        'new_capabilities'          : wppb_re_unsaved_capabilities,
        'all_capabilities'          : wppb_re_current_role_capabilities,
        'capabilities_to_delete'    : wppb_re_capabilities_to_delete
    };

    jQuery.post( wppb_roles_editor_data.ajaxUrl, data, function( response ) {
        jQuery( '.post-type-wppb-roles-editor #publishing-action #publish' ).removeClass( 'disabled' ).trigger( 'click' );
    } );
}

function wppb_re_change_select_to_input() {
    if( jQuery( '.post-type-wppb-roles-editor .select2.select2-container' ).is( ':visible' ) ) {
        jQuery( '.post-type-wppb-roles-editor .select2.select2-container' ).hide();
        jQuery( '.post-type-wppb-roles-editor .wppb-add-new-cap-input' ).show();
        jQuery( '.post-type-wppb-roles-editor a.wppb-add-new-cap-link' ).text( wppb_roles_editor_data.cancel_text );
    } else {
        jQuery( '.post-type-wppb-roles-editor .select2.select2-container' ).show();
        jQuery( '.post-type-wppb-roles-editor .wppb-add-new-cap-input' ).hide();
        jQuery( '.post-type-wppb-roles-editor a.wppb-add-new-cap-link' ).text( wppb_roles_editor_data.add_new_capability_text );
    }
}

function wppb_re_getParameterByName( name, url ) {
    if( ! url ) {
        url = window.location.href;
    }

    name = name.replace( /[\[\]]/g, "\\$&" );

    var regex = new RegExp( "[?&]" + name + "(=([^&#]*)|&|#|$)" ), results = regex.exec( url );

    if( ! results ) {
        return null;
    }

    if( ! results[2] ) {
        return '';
    }

    return decodeURIComponent( results[2].replace( /\+/g, " " ) );
}
