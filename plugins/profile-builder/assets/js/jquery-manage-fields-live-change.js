var fields 	=	{
						'Default - Name (Heading)':				{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
																},

						'Default - Contact Info (Heading)':		{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
                                                                },

						'Default - About Yourself (Heading)':	{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
                                                                },

						'Default - Username':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: ''
																					},
																	'required'	:	[
																						true
																					]
																},

						'Default - First Name':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'first_name'
																					}
																},

						'Default - Last Name':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'last_name'
																					}
																},

						'Default - Nickname':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'nickname'
																					},
																	'required'	:	[
																						true
																					]
																},

						'Default - E-mail':						{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: ''
																					},
																	'required'	:	[
																						true
																					]
																},

						'Default - Website':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: ''
																					}
																},

						'Default - AIM':						{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'aim'
																					}
																},

						'Default - Yahoo IM':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'yim'
																					}
																},

						'Default - Jabber / Google Talk':		{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'jabber'
																					}
																},

						'Default - Password':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-required'
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    },
                                                                    'required'	:	[
																						true
																					]
																},

						'Default - Repeat Password':			{	'show_rows'	:	[
																					'.row-field-title',
																					'.row-description',
																					'.row-required'
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    },
                                                                    'required'	:	[
																						true
																					]
																},

						'Default - Biographical Info':			{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-row-count',
																						'.row-default-content',
																						'.row-required'
																					],
																	'properties':	{
																						'meta_name_value'	: 'description'
																					}
																},

						'Default - Display name publicly as':	{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-default-value',
																						'.row-required'
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
                                                                },

						'Default - Blog Details':				{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description'
																					],
																	'properties':	{
																		'meta_name_value'	: ''
																	}
																},

						'GDPR Checkbox':						{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-required',
																						'.row-overwrite-existing'
																					],
																	'properties':	{
																						'meta_name_value'	: 'user_consent_gdpr',
																						'field_title'		: wppb_fields_strings.gdpr_title,
																						'description'		: wppb_fields_strings.gdpr_description
																					},
																	'required'	:	[
																						true
																					]
																},
						'GDPR Delete Button':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																					],
																	'properties':	{
																						'meta_name_value'	: ''
																					}
																},

						'Heading':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
                                                                                        '.row-heading-tag'
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
                                                                },

						'Input':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},

						'Email':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},
						'Email Confirmation':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-field',
																						'.row-description',
																						'.row-required'
																					],
																	'required'	:	[
																		true
																	],
																	'properties':	{
																		'meta_name_value'	: ''
																	}
																},
						'URL':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},

                        'Number':								{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-default-value',
                                                                                        '.row-min-number-value',
                                                                                        '.row-max-number-value',
                                                                                        '.row-number-step-value',
                                                                                        '.row-required',
                                                                                        '.row-overwrite-existing'
                                                                                    ]
                        },
						'Input (Hidden)':						{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-overwrite-existing'
																					]
																},
						'Language':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-overwrite-existing'
																					]
																},

						'Textarea':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-content',
																						'.row-required',
																						'.row-row-count',
																						'.row-overwrite-existing'
																					]
																},
                        'WYSIWYG':								{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-default-content',
                                                                                        '.row-required',
                                                                                        '.row-overwrite-existing'
                                                                                    ]
                                                                },
                        'Phone':								{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-phone-format',
                                                                                        '.row-required',
                                                                                        '.row-overwrite-existing'
                                                                                    ]
                                                                },
						'Select':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-option',
																						'.row-required',
																						'.row-overwrite-existing',
																						'.row-options',
																						'.row-labels'
																					]
																},
						'Select (Multiple)':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-options',
																						'.row-required',
																						'.row-overwrite-existing',
																						'.row-options',
																						'.row-labels'
																					]
																},

						'Select (Country)':						{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
                                                                                        '.row-default-option-country',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},

                        'Select (Currency)':					{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-show-currency-symbol',
                                                                                        '.row-default-option-currency',
                                                                                        '.row-required',
                                                                                        '.row-overwrite-existing'
                                                                                    ]
                                                                },

						'Select (Timezone)':					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
                                                                                        '.row-default-option-timezone',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},
						'Select (CPT)':							{	'show_rows'	:	[
																					'.row-field-title',
																					'.row-meta-name',
																					'.row-description',
																					'.row-default-option',
																					'.row-cpt',
																					'.row-required',
																					'.row-overwrite-existing'
																					]
																},
						"Select2": 								{	'show_rows'	:	[
																					'.row-field-title',
																					'.row-meta-name',
																					'.row-description',
																					'.row-default-option',
																					'.row-required',
																					'.row-overwrite-existing',
																					'.row-options',
																					'.row-labels',
																					'.row-visibility',
																					'.row-user-role-visibility',
																					'.row-location-visibility'
																					]
																},
						"Select2 (Multiple)": 					{	'show_rows' :   [
																					'.row-field-title',
																					'.row-meta-name',
																					'.row-description',
																					'.row-default-options',
																					'.row-required',
																					'.row-overwrite-existing',
																					'.row-options',
																					'.row-labels',
																					'.row-select2-multiple-limit',
																					'.row-select2-multiple-tags',
																					'.row-visibility',
																					'.row-user-role-visibility',
																					'.row-location-visibility'
																					]
																},
						'Honeypot':								{	'show_rows'	:	[
																					'.row-field-title'
																					],
																	'properties':	{
																		'meta_name_value'	: 'honeypot',
																		'field_title'		: wppb_fields_strings.honeypot_title
																	}
						},
						'Checkbox':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-options',
																						'.row-required',
																						'.row-overwrite-existing',
																						'.row-options',
																						'.row-labels'
																					]
																},

						'Checkbox (Terms and Conditions)':		{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-required',
																						'.row-overwrite-existing'
																					],
																	'required'	:	[
																						true
																					]
																},

						'Radio':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-option',
																						'.row-required',
																						'.row-overwrite-existing',
																						'.row-options',
																						'.row-labels'
																					]
																},

						'Upload':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-simple-upload',
																						'.row-allowed-extensions',
																						'.row-required',
																						'.row-overwrite-existing',
																						'.row-allowed-upload-extensions'
																					]
																},

						'Avatar':								{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-simple-upload',
																						'.row-allowed-image-extensions',
																						'.row-avatar-size',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},

						'Datepicker':							{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-default-value',
																						'.row-required',
																						'.row-date-format',
																						'.row-overwrite-existing'
																					]
																},


                        'Timepicker':							{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-required',
                                                                                        '.row-time-format',
                                                                                        '.row-overwrite-existing'
                                                                                    ]
                                                                },

                        'Colorpicker':							{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-meta-name',
																						'.row-description',
																						'.row-required',
																						'.row-overwrite-existing'
																					]
																},


                        'Validation':							{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-validation-possible-values',
                                                                                        '.row-custom-error-message',
                                                                                        '.row-required'
                                                                                    ],
                                                                    'required'	:	[
                                                                        true
                                                                    ]
                                                                },

						'reCAPTCHA':							{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-recaptcha-type',
																						'.row-public-key',
																						'.row-private-key',
                                                                                        '.row-captcha-pb-forms',
                                                                                        '.row-captcha-wp-forms',
																						'.row-required'
																					],
																	'required'	:	[
																						true
																					],
                                                                    'properties':	{
                                                                       'meta_name_value'	: ''
                                                                    }
																},

                        'Select (User Role)':					{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-description',
                                                                                        '.row-user-roles',
                                                                                        '.row-user-roles-on-edit-profile',
                                                                                        '.row-required'
                                                                                    ],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
                                                                },

                        'Map':              					{	'show_rows'	:	[
                                                                                        '.row-field-title',
                                                                                        '.row-meta-name',
                                                                                        '.row-description',
                                                                                        '.row-map-api-key',
                                                                                        '.row-map-default-lat',
                                                                                        '.row-map-default-lng',
                                                                                        '.row-map-default-zoom',
                                                                                        '.row-map-height',
                                                                                        '.row-map-pins-load-type',
                                                                                        '.row-map-pagination-number',
                                                                                        '.row-map-bubble-fields',
                                                                                        '.row-required',
																						'.row-overwrite-existing'
                                                                                    ],
																	'properties':	{
																		'meta_name_value'	: 'map'
																	}
                                                                },
						'HTML':              					{	'show_rows'	:	[
																						'.row-field-title',
																						'.row-description',
																						'.row-html-content'
																					],
                                                                    'properties':	{
                                                                        'meta_name_value'	: ''
                                                                    }
																}
				}
var fields_to_show = [
	'.row-field-title',
	'.row-field',
	'.row-meta-name',
	'.row-required'
]

function wppb_hide_properties_for_already_added_fields( container_name ){

	jQuery( container_name + ' tr:not(.update_container_wppb_manage_fields)' ).each(function() {

		field = jQuery('.row-field pre', this).text();

		jQuery( 'li', this ).each(function() {
			var class_name = '';

			class_name = jQuery(this).attr('class');
			jQuery(this).hide();

			if ( ( field in fields ) ){
				var to_show = fields[field]['show_rows'];
				for (var key in to_show) {
					if ('.'+class_name == fields_to_show[key]){
						jQuery(this).show();
					}
				}
			}

		});
	});

    /* hide the delete button for username,password and email fields */
    jQuery( container_name + ' ' + '.element_type_default-e-mail .mbdelete,' + ' ' + container_name + ' ' + '.element_type_default-password .mbdelete,' + ' ' + container_name + ' ' + '.element_type_default-username .mbdelete'  ).hide();	// PB specific line
}



function wppb_hide_all ( container_name ){
	jQuery( container_name + ' ' + '.mb-list-entry-fields > li' ).each(function() {
		if ( !( ( jQuery(this).hasClass('row-field') ) || ( jQuery(this).children().hasClass('button-primary') ) ) ){
			jQuery(this).hide();
		}
	});

	jQuery( container_name + ' ' + '.mb-list-entry-fields .button-primary' ).attr( 'disabled', true );

	jQuery( container_name + ' ' + '.element_type_default-e-mail .mbdelete,' + ' ' + container_name + ' ' + '.element_type_default-password .mbdelete,' + ' ' + container_name + ' ' + '.element_type_default-username .mbdelete'  ).hide();	// PB specific line
	jQuery( container_name + ' ' + '.element_type_default-e-mail #field' + ', ' + container_name + ' ' + '.element_type_default-password #field' + ',  ' + container_name + ' ' + '.element_type_default-username #field' + ', ' + container_name + ' ' + '.element_type_default-e-mail #required' + ', ' + container_name + ' ' + '.element_type_default-password #required,'  + container_name + ' ' + '.element_type_default-username #required,'  + container_name + ' ' + '.element_type_checkbox-terms-and-conditions #required,'  + container_name + ' ' + '.element_type_recaptcha #required,' + container_name + ' ' + '.element_type_woocommerce-customer-billing-address #field, ' + container_name + ' ' + '.element_type_woocommerce-customer-shipping-address #field').attr( 'disabled', true );		// PB specific line

}


function wppb_disable_add_entry_button( container_name ){
	jQuery( container_name + ' ' + '.mb-list-entry-fields .button-primary' ).each( function(){

		//jQuery(this).data('myclick', this.onclick );
		this.onclick = function(event) {
			if ( jQuery(this).attr( 'disabled' ) ) {
				return false;
			}
			/* changed this in version 2.5.0 because the commented line generated stack exceeded error when multiple fields were opened with edit */
			if ( typeof( event.currentTarget ) == 'undefined' ){
				// Repeater field triggered the click event of the "Add Field" / "Save changes" buttons, so the onclick attribute is in the target, not currentTarget
				eval(event.target.getAttribute('onclick'));
			}else {
				// normal Manage Fields Add Field button press
				eval(event.currentTarget.getAttribute('onclick'));
			}

			//jQuery(this).data('myclick').call(this, event || window.event);
		};
	});

}


function wppb_edit_form_properties( container_name, element_id ){
	wppb_hide_all ( container_name );
	wppb_disable_add_entry_button ( container_name );

	field = jQuery( container_name + ' #' + element_id + ' ' + '#field' ).val();

	if ( ( field in fields ) ){
		var to_show = fields[ field.trim() ]['show_rows'];
		for (var key in to_show)
			jQuery( container_name + ' #' + element_id + ' ' + to_show[key] ).show();

        var properties = fields[ field.trim() ]['properties'];
        if( typeof properties !== 'undefined' && properties ) {
            for( var key in properties ) {
                if( typeof properties['meta_name_value'] !== 'undefined' ) {
                    jQuery( container_name + ' ' + '#meta-name').attr( 'readonly', true );
                }
            }
        }

		jQuery( container_name + ' ' + '.mb-list-entry-fields .button-primary' ).removeAttr( 'disabled' );

        //Handle user role sorting
        wppb_handle_user_role_field( container_name );
	}
}


function wppb_display_needed_fields( index, container_name, current_field_select ){
	var show_rows = fields[index.trim()]['show_rows'];
	for (var key in show_rows) {
		jQuery(  show_rows[key], jQuery( current_field_select ).parents( '.mb-list-entry-fields' ) ).show();
	}

	var properties = fields[index.trim()]['properties'];
	if ( ( ( typeof properties !== 'undefined' ) && ( properties ) ) ) { //the extra (second) condition is a particular case since only the username is defined in our global array that has no meta-name
		for (var key in properties) {
			if ( ( typeof properties['meta_name_value'] !== 'undefined' ) ){
				jQuery( container_name + ' ' + '#meta-name' ).val( properties['meta_name_value'] );
				jQuery( container_name + ' ' + '#meta-name' ).attr( 'readonly', true );
			}
			if ( ( typeof properties['field_title'] !== 'undefined' ) ){
				jQuery( container_name + ' ' + '#field-title' ).val( properties['field_title'] );
			}
			if ( ( typeof properties['description'] !== 'undefined' ) ){
				jQuery( container_name + ' ' + '#description' ).val( properties['description'] );
			}
		}

	}else{
        /* meta value when editing a field shouldn't change so we take it from the current entered value which is displayed above the edit form */
        if( jQuery( current_field_select).parents('.update_container_wppb_manage_fields').length != 0 ){
            meta_value = jQuery( '.row-meta-name pre', jQuery( current_field_select).parents( '.update_container_wppb_manage_fields' ).prev() ).text();
        }
        /* for the add form it should change */
        else{

			// Repeater fields have different meta name prefixes, stored in the GET parameter 'wppb_field_metaname_prefix'.
			var get_parameter_prefix = wppb_get_parameter_by_name( 'wppb_field_metaname_prefix' );
			var field_metaname_prefix = ( get_parameter_prefix == null ) ? 'custom_field' : get_parameter_prefix;

            numbers = new Array();
            jQuery( '#container_wppb_manage_fields .row-meta-name pre').each(function(){
                meta_name = jQuery(this).text();
                if( meta_name.indexOf( field_metaname_prefix ) !== -1 ){
                    var meta_name = meta_name.replace(field_metaname_prefix, '' );
                    /* we should have an underscore present in custom_field_# so remove it */
                    meta_name = meta_name.replace('_', '' );

                    if( isNaN( meta_name ) ){
                        meta_name = Math.floor((Math.random() * 200) + 100);
                    }
                    numbers.push( parseInt(meta_name) );
                }

            });
            if( numbers.length > 0 ){
                numbers.sort( function(a, b){return a-b} );
                numbers.reverse();
                meta_number = parseInt(numbers[0])+1;
            }
            else
                meta_number = 1;

            meta_value = field_metaname_prefix + '_' + meta_number;
        }

		jQuery( container_name + ' ' + '#meta-name' ).val( meta_value );
		jQuery( container_name + ' ' + '#meta-name' ).attr( 'readonly', false );
	}

    //Handle user role sorting
    wppb_handle_user_role_field( container_name );

	var set_required = fields[index.trim()]['required'];
	if ( ( typeof set_required !== 'undefined' ) && ( set_required ) ){
		jQuery( container_name + ' ' + '#required' ).val( 'Yes' );
		jQuery( container_name + ' ' + '#required' ).attr( 'disabled', true );

	}else{
		jQuery( container_name + ' ' + '#required' ).val( 'No' );
		jQuery( container_name + ' ' + '#required' ).attr( 'disabled', false );
	}

	jQuery( container_name + ' ' + '.mb-list-entry-fields .button-primary' ).removeAttr( 'disabled' );
}


function wppb_get_parameter_by_name(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, "\\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return null;
	return decodeURIComponent(results[2].replace(/\+/g, " "));
}

/*
* Function that handles the sorting of the user roles from the Select (User Role)
* extra field
*
 */
function wppb_handle_user_role_field( container_name ) {

    jQuery( container_name + ' ' + '.row-user-roles .wck-checkboxes').sortable({

        //Assign a custom handle for the drag and drop
        handle: '.sortable-handle',

        create: function( event, ui ) {

            //Add the custom handle for drag and drop
            jQuery(this).find('div').each( function() {
                jQuery(this).prepend('<span class="sortable-handle"></span>');
            });

            $sortOrderInput = jQuery(this).parents('.row-user-roles').siblings('.row-user-roles-sort-order').find('input[type=text]');

            if( $sortOrderInput.val() == '' ) {
                jQuery(this).find('input[type=checkbox]').each( function() {
                    $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
                });
            } else {
                sortOrderElements = $sortOrderInput.val().split(', ');
                sortOrderElements.shift();

                for( var i=0; i < sortOrderElements.length; i++ ) {
                    jQuery( container_name + ' ' + '.row-user-roles .wck-checkboxes').append( jQuery( container_name + ' ' + '.row-user-roles .wck-checkboxes input[value="' + sortOrderElements[i] + '"]').parent().parent().get(0) );
                }
            }
        },

        update: function( event, ui ) {
            $sortOrderInput = ui.item.parents('.row-user-roles').siblings('.row-user-roles-sort-order').find('input[type=text]');
            $sortOrderInput.val('');

            ui.item.parent().find('input[type=checkbox]').each( function() {
                $sortOrderInput.val( $sortOrderInput.val() + ', ' + jQuery(this).val() );
            });
        }
    });
}

function wppb_initialize_live_select( container_name ){
	wppb_hide_all( container_name );
    jQuery(document).on( 'change', container_name + ' ' + '.mb-list-entry-fields #field', function () {
		field = jQuery(this).val();

		if ( field != ''){
			wppb_hide_all( container_name );
			wppb_display_needed_fields( field, container_name, this );
		}else{
			wppb_hide_all( container_name );
		}
	});
}


function wppb_enable_select2(container_name){
    // Select2 initialization on manage fields.

	wppbSelect2.call( jQuery( container_name + ' #field'), {
        placeholder: 'Select an option',
        allowClear: true
    });

    var $eventSelect = jQuery( container_name + ' #field');
    $eventSelect.on("select2:open", function (e) {
        jQuery('#wppb_select2_overlay').fadeIn('100')
    });
    $eventSelect.on("select2:close", function (e) {
        jQuery('#wppb_select2_overlay').hide();
    });
}


jQuery(function(){
 	wppb_initialize_live_select ( '#wppb_manage_fields' );
	wppb_initialize_live_select ( '#container_wppb_manage_fields' );

	wppb_hide_properties_for_already_added_fields( '#container_wppb_manage_fields' );
	wppb_disable_add_entry_button ( '#wppb_manage_fields' );

    var overlay = jQuery('<div id="wppb_select2_overlay"> </div>')
    overlay.appendTo(document.body)

    wppb_enable_select2('#wppb_manage_fields');

});

// Custom functionality for sorting options (see the Map POIs attributes).
(function ($) {
	window.SortSelCheck = {
		// Initiate the events and triggers.
		init: function () {
			SortSelCheck.assess();
			SortSelCheck.listen();
		},

		// Listen for events.
		listen: function () {
			// Listen for the new element setup and trigger the expected custom event.
			$('.wck-add-form').on('change', function() {
				SortSelCheck.assess('wck-add-form');
			});
		},

		// Assess the potential elements and configure these when possible.
		assess: function (elem) {
			var $elem = $('.wppb_sortable_checkboxes_wrap');
			// console.log('assess triggerer ' + elem, $elem);
			$elem.each(function() {
				$rows = SortSelCheck.remake($(this));
				SortSelCheck.attach($(this), $rows);
			});
		},

		// Configure the elements that form the sortable checkboxes options.
		remake: function ($field) {
			var $rows = $field.children('.wck-checkboxes');
			if (!$rows.length) {
				$field.prepend('<div class="wck-checkboxes"> </div>');
				var $rows = $field.children('.wck-checkboxes');
			}
			if ($rows.length) {
				SortSelCheck.sortable($rows);
			}
			return $rows;
		},

		// Attach the custom dropdown options as sortable checkboxes options.
		attach: function ($field, $rows) {
			var $ddwn = $field.children('.wppb_selector_for_sortable_checkbox');
			if ($ddwn.length) {
				$ddwn.on('change', function() {
					var val = $(this).val();
					if ('' !== val) {
						$rows.append('<div><label><input type="checkbox" name="' + $(this).data('list') + '" id="' + $(this).data('list') + '_' + val + '" value="' + val + '" checked="checked" class="mb-checkbox mb-field">' + $(this).children('option').filter(':selected').text() + '</label></div>');
						$(this).children('option').filter(':selected').remove();
						SortSelCheck.sortable($rows);
					}
				});
			}
		},

		// Make sortable items from the list.
		sortable: function ($list) {
			var $items = $list.children('div');
			$items.addClass('wppb_manage_fields_sortables');
			$items.remove('em');
			$items.prepend('<em class="dashicons dashicons-menu"></em> ');
			$list.sortable({
				items: '> div',
				classes: {'ui-sortable': 'highlight'}
			});
		},

	};

	$(document).ready(function () {
		// Initialize the custom functionality.
		SortSelCheck.init('init');

		// Bind the event to the custom functionality.
		$('html').on('wpbFormMetaLoaded', function(e, elem) {
			SortSelCheck.assess(elem);
		});
	});

})(jQuery);
