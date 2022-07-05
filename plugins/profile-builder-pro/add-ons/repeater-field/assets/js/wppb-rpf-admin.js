/******************************************************************************************
 *
 * This is the Back-end Repeater Field functionality that helps set-up the field group
 *
 ******************************************************************************************/
function WPPB_Manage_Repeater_Field() {

    var _this = this;

    this.role_limit_options = [];

    this.role_list = null;

    this.init = function () {
        _this.set_role_list();
        _this.wppb_rpf_add_field();

        _this.add_event_handlers();
    };

    this.set_role_list = function () {
        _this.role_list = wppb_rpf_roles_list;
    };

    /**
     * Function that adds the Repeater field to the global fields object
     * declared in assets/js/jquery-manage-fields-live-change.js
     *
     */
    this.wppb_rpf_add_field = function () {
        if (typeof fields == "undefined") {
            return false;
        }
        fields["Repeater"] = {
            'show_rows': [
                '.row-field-title',
                '.row-field',
                '.row-rpf-button',
                '.row-rpf-enable-limit',
                '.row-conditional-logic-enabled'
            ],
            'properties': {
                'meta_name_value': ''
            }
        };
    };


    this.add_event_handlers = function () {

        // Bind the click event to the "Enable Conditional Logic" checkbox
        jQuery(document).off('click', 'input[name=rpf-enable-limit]').on('click', 'input[name=rpf-enable-limit]', function () {
            var enable_limit_checkbox = jQuery(this);
            if (enable_limit_checkbox.is(':checked')) {
                _this.show_rpf_limit_option(enable_limit_checkbox);
            } else
                _this.hide_rpf_limit_option(enable_limit_checkbox);
        });

        // Show conditional option when the admin changes the field type when adding a new field
        jQuery(document).off('change', '#manage-fields select[name=field]').on('change', '#manage-fields select[name=field]', function () {

            var enable_limit_checkbox = jQuery(this).closest('li').siblings('.row-rpf-enable-limit').find('input[name=rpf-enable-limit]');

            if (enable_limit_checkbox.is(':checked')) {
                _this.show_rpf_limit_option(enable_limit_checkbox);
            } else
                _this.hide_rpf_limit_option(enable_limit_checkbox);

        });

        jQuery(window).on( 'resize', function () {
            if (document.getElementById('TB_iframeContent')) {
                _this.wppb_rpf_resize_iframe();
            }
        });

    };


    /*
     * Returns the jQuery object for the limit option wrapper
     * in manage fields
     *
     */
    this.get_limit_option_wrapper = function (enable_limit_checkbox) {
        return enable_limit_checkbox.closest('li').siblings('.row-rpf-limit');
    };

    this.get_limit_reached_message_option_wrapper = function (enable_limit_checkbox) {
        return enable_limit_checkbox.closest('li').siblings('.row-rpf-limit-reached-message');
    };

    this.get_role_limit_option_wrapper = function (enable_limit_checkbox) {
        return enable_limit_checkbox.closest('li').siblings('.row-rpf-role-limit');
    };

    /*
     * Returns the ID of the field
     *
     */
    this.get_field_id = function (enable_limit_checkbox) {
        return parseInt(enable_limit_checkbox.closest('li').siblings('.row-id').find('input[name=id]').val());
    };


    this.show_rpf_limit_option = function (enable_limit_checkbox) {
        if (enable_limit_checkbox.is(':checked')) {
            var limit_option = this.get_limit_option_wrapper(enable_limit_checkbox);
            var limit_reached_message_option = this.get_limit_reached_message_option_wrapper(enable_limit_checkbox);
            var role_limit_option = this.get_role_limit_option_wrapper(enable_limit_checkbox);

            limit_option.show();
            limit_reached_message_option.show();
            role_limit_option.show();


            var field_id = this.get_field_id(enable_limit_checkbox);

            if (!this.role_limit_options[field_id]) {
                this.role_limit_options[field_id] = new WPPB_RPF_Role_Limit_Option(field_id, _this.role_list);
                this.role_limit_options[field_id].init_view(role_limit_option);
            } else {
                this.role_limit_options[field_id].init_view(role_limit_option);
            }

        }
    };

    this.hide_rpf_limit_option = function (enable_limit_checkbox) {
        this.get_limit_option_wrapper(enable_limit_checkbox).hide();
        this.get_limit_reached_message_option_wrapper(enable_limit_checkbox).hide();
        this.get_role_limit_option_wrapper(enable_limit_checkbox).hide();
    };


    this.wppb_rpf_open_repeater_fields_iframe = function (clicked_element) {

        var adding_new_field = false;
        if (jQuery("#wppb_manage_fields.wck-add-form #wppb_rpf_edit_field_group_button")[0] == clicked_element) {
            adding_new_field = true;
        }
        jQuery('#wppb_manage_fields').parent().css({
            'opacity': '0.4',
            'position': 'relative'
        }).append('<div id="mb-ajax-loading"></div>');

        var title = jQuery(clicked_element).parents('.mb-list-entry-fields').find("input#field-title").val();
        var meta_name = jQuery(clicked_element).parents('.mb-list-entry-fields').find("input#meta-name").val();

        jQuery.ajax({
            url: wppbWckAjaxurl,
            type: 'post',
            data: {
                action: 'wppb_rpf_check_repeater_unique_title',
                title: title,
                meta_name: meta_name
            },
            success: function (response) {
                jQuery('#wppb_manage_fields').parent().css('opacity', '1');
                jQuery('#mb-ajax-loading').remove();
                response = JSON.parse(response);
                if (response.is_unique == true) {
                    if (adding_new_field == true) {
                        jQuery(clicked_element).parents('.mb-list-entry-fields').find("#meta-name").val("wppb_repeater_field_" + response.title_slug);
                        jQuery("#wppb_manage_fields .add-entry-button .button-primary").trigger("click");
                    }
                    _this.wppb_rpf_open_iframe(jQuery(clicked_element).parents('.mb-list-entry-fields').find("#meta-name").val(), title, response.title_slug, adding_new_field);
                } else {
                    alert(response.error_message);
                }
            }
        });
    };

    var wppb_rpf_repeateable_fields_saved = function(){
        jQuery(".wppb-fields-saved").css("display","inline");
        jQuery(".wppb-fields-saved").delay(3000).fadeOut(400);
        jQuery("#mb-ajax-loading").off("remove", wppb_rpf_repeateable_fields_saved );
    };

    _this.wppb_rpf_open_iframe = function(repeater_field_meta_name, title, title_slug, adding_new_field ){
        var iframe_max_height = jQuery( window ).height() * 75 / 100;
        var iframe_max_width = jQuery( window ).width() * 80 / 100;

        tb_show(title, window.location.href + '&wppb_rpf_repeater_meta_name=' + repeater_field_meta_name + '&wppb_field_metaname_prefix=' + title_slug + '#TB_iframe=true&width=' + iframe_max_width + '&height=' + iframe_max_height, '');
        jQuery('#TB_window').append('<div id="wppb_rpf_background"></div>');
        jQuery('#TB_window').append('<div id="wppb_rpf_spinner"></div>');


        jQuery('#TB_iframeContent').on('load', function () {

            var iframe = jQuery("#TB_iframeContent").contents();
            iframe.find('#wpwrap').hide();
            var wck_post_body = iframe.find('.wck-post-body')
            wck_post_body.appendTo(iframe.find('body'));
            wck_post_body.css('margin', '1%');
            var width = wck_post_body.width();
            wck_post_body.css('width', '98%');
            iframe.find('h2').css('margin', '10px');

            jQuery('#wppb_rpf_spinner').remove();
            jQuery('#wppb_rpf_background').remove();
        });

        var jQueryBody = jQuery('body');
        jQueryBody.off( 'thickbox:removed', wppb_rpf_repeateable_fields_saved );
        jQueryBody.on( 'thickbox:removed', wppb_rpf_repeateable_fields_saved );

        if ( adding_new_field ) {
            jQueryBody.off( 'thickbox:removed', _this.wppb_rpf_edit_repeater );
            jQueryBody.on( 'thickbox:removed', _this.wppb_rpf_edit_repeater );
        }
    };

    _this.wppb_rpf_resize_iframe = function() {

        var iframe_max_height = jQuery( window ).height() * 75 / 100;
        var iframe_max_width = jQuery( window ).width() * 80 / 100;

        var iframe_selector = jQuery( '#TB_iframeContent');
        var iframe_wrap_selector = jQuery( '.wppb_cpm_iframe_wrap');
        var tb_window_selector = jQuery( '#TB_window');

        iframe_selector.height( iframe_max_height );
        iframe_wrap_selector.height ( iframe_max_height );
        tb_window_selector.css( "margin-top", parseInt( "-" + (iframe_max_height  / 2 ) ) );
        tb_window_selector.css( "margin-left", parseInt( "-" + ( iframe_max_width  / 2 ) ) );
        iframe_selector.width( iframe_max_width  );
        iframe_wrap_selector.width(  iframe_max_width );
        tb_window_selector.width( iframe_max_width  );

    };

    _this.wppb_rpf_edit_repeater = function () {

        var field_id_to_edit = jQuery("#container_wppb_manage_fields .button-secondary").length - 1;
        jQuery("#container_wppb_manage_fields #element_" + field_id_to_edit +  " .button-secondary").trigger("click");
        jQuery("#mb-ajax-loading").on("remove", wppb_rpf_repeateable_fields_saved );
    };

}

var wppb_manage_repeater_field = new WPPB_Manage_Repeater_Field();



/**
 *  Role limit option functionality
 */

function WPPB_RPF_Role_Limit_Option( field_id, role_list ){

    var _this = this;

    // Public properties defaults
    this.rules	 	 = [];

    // Private properties
    var $input_wrapper	= {};
    var $input 			= {};

    // Counter to track how many rules were added. Does not take into account removed rules
    var rules_added = 0;

    // Available operators
    var _this = this;


    this.init_view = function( limit_option ) {

        $input_wrapper  = limit_option;
        $input 			= limit_option.find('input.mb-field');

        if( $input.attr( 'type' ) != 'hidden' ) {

            this.set_up_data( );

            // Make the default text field hidden
            $input.attr('type', 'hidden' );

            // Insert the conditional logic option component view before the field
            $input.before( this.get_view() );

            // Add event handlers
            add_event_handlers();

        }

    };


    /*
     * Setup object data that will be used for the interface rendering
     *
     */
    this.set_up_data = function() {

        if( $input.val() != '' ) {

            var obj = JSON.parse( $input.val() );

            // Convert the rules from object to array
            if( obj.rules ) {
                obj.rules = Object.keys( obj.rules ).map( function( key ) {
                    return obj.rules[key];
                });
            }

            if( typeof obj == 'object' ) {
                this.rules 		 = ( obj.rules ? obj.rules : this.rules );
            }

        }

    };


    /*
     * Returns the view
     *
     */
    this.get_view = function() {

        var output = '';

        output += '<div class="wppb-rpf-wrapper" data-field-id="' + field_id + '" data-rules-count="' + ( this.rules.length != 0 ? this.rules.length : 1 ) + '">';
        output += '<span class="wppb-rpf-separator">Override the limit set in General Limit for the following roles:</span>';

        /*
         * If the field has saved conditional rules then display them
         *
         */
        if( this.rules.length != 0 ) {

            for( var index in this.rules ) {

                output += this.get_view_rule( index, this.rules[index] );
                rules_added++;

            }

            /*
             * If the field Does Not have any conditional rules saved in the DB, or is a new field,
             * add an empty rule to start it off
             *
             */
        } else {

            output += this.get_view_rule( 0, {} );
            rules_added++;

        }

        output += '</div>';

        return output;

    };


    /*
     * Returns the HTML for a given rule_id and object
     *
     * @param int rule_id - the numeric ID the rule should have
     * @param obj rule 	  - the object representation of the rule, containing the role and value
     *
     */
    this.get_view_rule = function( rule_id, rule ) {

        // Set some defaults if the rule does not have them
        rule.role    = rule.role ? rule.role : '';
        rule.value    = rule.value ? rule.value : '';

        var output = '<div class="wppb-rpf-rule" data-index="' + rule_id + '" data-field-id="' + field_id + '_' + rule_id + '">';

        output += this.get_view_rule_field_role( rule.role );
        output += this.get_view_rule_field_value( rule.value );
        output += this.get_view_rule_actions();

        output += '</div>';

        return output;

    };


    /*
     * Returns the HTML for the Select drop-down that contains all the roles from manage fields
     *
     * @param int value - the ID of the manage field that should be selected
     *
     */
    this.get_view_rule_field_role = function( value ) {

        var output = '<select class="wppb-rpf-rule-role" data-property="role">';

        output += '<option value="-1">Choose...</option>';

        jQuery.each(role_list, function(slug, role_name) {

                   output += '<option ' + ( value == slug ? 'selected' : '' ) + ' value="' + slug + '">' + role_name + '</option>';
        });

        output += '</select>';

        return output;

    };


    /*
     * Returns the HTML for the Value field of a rule
     *
     * The returned value can be - the HTML for a Select drop-down that is populated with the default options
     *							   of the selected Field from the rule if the Field is a "select, checkbox, radio"
     *							 - the HTML for an input[type=text] if the rule's Field does not have any options
     *
     * @param string value - the value that should be displayed
     * @param int field_id - the ID of the field selected in the rule's Field drop-down
     *
     */
    this.get_view_rule_field_value = function( value ) {
        if ( value == '' ){
            value = 0;
        }
        var output = '<input type="number" min="0" class="wppb-rpf-rule-value" data-property="value" value="' + parseInt(value) + '" />';

        return output;

    };


    /*
     * Returns the HTML for the possible actions an admin can do with a rule
     *
     */
    this.get_view_rule_actions = function() {

        var output = '';

        output += '<span class="wppb-rpf-rule-action wppb-rpf-add">+</span>';
        output += '<span class="wppb-rpf-rule-action wppb-rpf-remove">-</span>';

        return output;

    };


    /*
     * Updates the view with new data. It maintains the values already set by the user in the fields.
     *
     * @param array manage_fields
     *
     */
    this.update_view = function( manage_fields ) {

        fields = manage_fields;

        // Update view for all the rules
        $input_wrapper.find('.wppb-rpf-rule').each( function() {

            $this = jQuery(this);

            var rule_id = $this.data('index');
            var rule    = {};

            // Create the rule object
            $this.children().each( function() {
                $child = jQuery(this);

                if( $child[0].hasAttribute('data-property') ) {
                    rule[ $child.data('property') ] = $child.val();
                }
            });

        });

    };


    /*
     * Add event handlers
     *
     */
    function add_event_handlers() {

        var wrapper_selector = '.wppb-rpf-wrapper[data-field-id=' + field_id + ']';

        // Remove rule
        jQuery(document)
            .off( 'click', wrapper_selector + ' .wppb-rpf-rule-action.wppb-rpf-remove' )
            .on( 'click', wrapper_selector + ' .wppb-rpf-rule-action.wppb-rpf-remove', function() {

                $rule = jQuery(this).closest('.wppb-rpf-rule');
                $rule.remove();

                $input_wrapper.find('.wppb-rpf-wrapper[data-field-id="' + field_id + '"]').attr('data-rules-count', jQuery( wrapper_selector + ' .wppb-rpf-rule' ).length );

                // Update value to be saved in the DB
                _this.update_option_value();

            });


        // Add rule
        jQuery(document)
            .off( 'click', wrapper_selector + ' .wppb-rpf-rule-action.wppb-rpf-add' )
            .on( 'click', wrapper_selector + ' .wppb-rpf-rule-action.wppb-rpf-add', function() {

                $rule = jQuery(this).closest('.wppb-rpf-rule');
                $rule.after( _this.get_view_rule( rules_added++, {} ) );

                $input_wrapper.find('.wppb-rpf-wrapper[data-field-id="' + field_id + '"]').attr('data-rules-count', jQuery( wrapper_selector + ' .wppb-rpf-rule' ).length );

            });


        // Detect any changes in the fields and update the option value
        jQuery(document)
            .off( 'change', wrapper_selector + ' select, ' + wrapper_selector + ' input' )
            .on( 'change', wrapper_selector + ' select, ' + wrapper_selector + ' input', function() {

                _this.update_option_value();

            });

    }


    /*
     * Updates the value that will be saved in the DB for the whole conditional rules Option of the Manage Field
     *
     */
    this.update_option_value = function() {

        var wrapper_selector = '.wppb-rpf-wrapper[data-field-id=' + field_id + ']';

        var obj = {};
        var rules = {};

        jQuery( wrapper_selector + ' .wppb-rpf-rule' ).each( function( index ) {

            rules[index] = ( typeof rules[index] == 'undefined' ? {} : rules[index] );

            jQuery(this).find('select, input').each( function() {

                var property = jQuery(this).data('property');
                var value 	 = jQuery(this).val();

                rules[index][property] = value;

            });

        });

        obj.rules = rules;

        $input.val( JSON.stringify( obj ) );

    };


}


jQuery( function() {

    wppb_manage_repeater_field.init();

    // we need run this again after adding the Email Confirmation field to the global fields object
    wppb_hide_properties_for_already_added_fields( '#container_wppb_manage_fields' );
});

