/******************************************************************************************
 *
 * This is the Conditional Logic App that manages the rest of the components
 *
 ******************************************************************************************/
function WPPB_Conditional_Logic() {

	var _this = this;

	/*
	 * The data of the manage fields as saved in the DB
	 *
	 */
	this.manage_fields = [];

    this.conditional_options = [];

    this.disabled_fields = [ 'Default - Username', 'Default - E-mail', 'Default - Password' ];


	/*
	 * Initialize the conditional logic
	 *
	 */
	this.init = function() {

		// We depend on these fields so if these are not present, do nothing
		if( typeof fields == 'undefined' )
			return false;

		// Add the conditional field options in the admin page
		for( field_name in fields ) {
            if( this.disabled_fields.indexOf( field_name ) == -1 )
                fields[field_name]['show_rows'].push( '.row-conditional-logic-enabled' );
        }

		// Add bindings
		add_event_handlers();

	};

	/*
	 * Sets the data of the manage fields
	 *
	 */
	this.set_manage_fields = function( manage_fields ) {

        this.manage_fields = [];

		for( field in manage_fields ) {
			if( null != manage_fields[field] )
				this.manage_fields[manage_fields[field].id] = manage_fields[field];
		}

        // Update the views
        for( var index in this.conditional_options )
            this.conditional_options[index].update_view( this.manage_fields );

	};


	/*
	 * Returns the ID of the field
	 *
	 */
	this.get_field_id = function( $cb ) {
		return parseInt( $cb.closest('li').siblings('.row-id').find('input[name=id]').val() );
	};


	/*
	 * Retuns the jQuery object for the conditional logic option wrapper
	 * in manage fields
	 *
	 */
	this.get_conditional_option_wrapper = function( $cb ) {
		return $cb.closest('li').siblings('.row-conditional-logic');
	};


	/*
	 * Shows the conditional option
	 *
	 */
	this.show_conditional_option = function( $cb ) {

		if( $cb.is(':checked') ) {

			$option = this.get_conditional_option_wrapper( $cb );

			$option.show();

            var field_id = this.get_field_id( $cb );

			if( !this.conditional_options[ field_id ] ) {
                this.conditional_options[ field_id ] = new WPPB_Conditional_Logic_Option( field_id );
                this.conditional_options[ field_id ].init_view( $option, this.manage_fields );
            } else {
                this.conditional_options[ field_id ].init_view( $option, this.manage_fields );
            }

		}

	};


	/*
	 * Hides the conditional option
	 *
	 */
	this.hide_conditional_option = function( $cb ) {
		this.get_conditional_option_wrapper( $cb ).hide();
	};


	/*
	 * Add event handlers
	 *
	 */
	add_event_handlers = function() {

		// Bind the click event to the "Enable Conditional Logic" checkbox
		jQuery(document).off( 'click', 'input[name=conditional-logic-enabled]' ).on( 'click', 'input[name=conditional-logic-enabled]', function() {

			$this = jQuery(this);

			if( $this.is(':checked') ) {
				_this.show_conditional_option( $this );
			} else
				_this.hide_conditional_option( $this );
		});

        // Show conditional option when the admin changes the field type when adding a new field
        jQuery(document).off( 'change', '#manage-fields select[name=field]' ).on( 'change', '#manage-fields select[name=field]', function() {

            $cb = jQuery(this).closest('li').siblings('.row-conditional-logic-enabled').find('input[name=conditional-logic-enabled]');

            if( $cb.is(':checked') ) {
                _this.show_conditional_option( $cb );
            } else
                _this.hide_conditional_option( $cb );

        });

	}

}



/******************************************************************************************
 *
 * This is the Conditional Logic Option Component
 *
 ******************************************************************************************/
function WPPB_Conditional_Logic_Option( field_id ) {

	var _this = this;

	// Public properties defaults
	this.action_type = 'show';
	this.logic_type  = 'all';
	this.rules	 	 = [];

	// Private properties
    var fields      = {};
	var $input_wrapper	= {};
	var $input 			= {};

	// Counter to track how many rules were added. Does not take into account removed rules
	var rules_added = 0;

	// Available operators
	var operators   = [ 'is', 'is not' ];


	/*
	 * Initializes the conditional logic option for the given element if
	 * it isn't already initialized
	 *
	 */
	this.init_view = function( $elem, manage_fields ) {

        $input_wrapper  = $elem;
        $input 			= $elem.find('input.mb-field');

        fields = manage_fields;

        if( $input.attr( 'type' ) != 'hidden' ) {

            this.set_up_data( fields );

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
                this.action_type = ( obj.action_type ? obj.action_type : this.action_type );
                this.logic_type  = ( obj.logic_type ? obj.logic_type : this.logic_type );
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

		output += '<div class="wppb-cf-wrapper" data-field-id="' + field_id + '" data-rules-count="' + ( this.rules.length != 0 ? this.rules.length : 1 ) + '">';

			/*
			 * Field action type
			 *
			 */
			output += '<select class="wppb-cf-select wppb-cf-select-action_type" data-property="action_type">';
				output += '<option ' + ( this.action_type == 'show' ? 'selected' : '' ) + ' value="show">Show</option>';
				output += '<option ' + ( this.action_type == 'hide' ? 'selected' : '' ) + ' value="hide">Hide</option>';
			output += '</select>';

			output += '<span class="wppb-cf-separator">this field if</span>';

			/*
			 * Field logic type
			 *
			 */
			output += '<select class="wppb-cf-select wppb-cf-select-logic_type" data-property="logic_type">';
				output += '<option ' + ( this.logic_type == 'all' ? 'selected' : '' ) + ' value="all">All</option>';
				output += '<option ' + ( this.logic_type == 'any' ? 'selected' : '' ) + ' value="any">Any</option>';
			output += '</select>';

			output += '<span class="wppb-cf-separator">of the following match:</span>';


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
	 * @param obj rule 	  - the object representation of the rule, containing the field if, the operator and matching value
	 *
	 */
	this.get_view_rule = function( rule_id, rule ) {

		// Set some defaults if the rule does not have them
		rule.field    = rule.field ? rule.field : '';
		rule.operator = rule.operator ? rule.operator : '';
		rule.value    = rule.value ? rule.value : '';

		var output = '<div class="wppb-cf-rule" data-index="' + rule_id + '" data-field-id="' + field_id + '_' + rule_id + '">';

			output += this.get_view_rule_field_field( rule.field );
			output += this.get_view_rule_field_operator( rule.operator );
			output += this.get_view_rule_field_value( rule.value, rule.field );
			output += this.get_view_rule_actions();

		output += '</div>';

		return output;

	};


	/*
	 * Returns the HTML for the Select drop-down that contains all the fields from manage fields
	 *
	 * @param int value - the ID of the manage field that should be selected
	 *
	 */
	this.get_view_rule_field_field = function( value ) {

		var output = '<select class="wppb-cf-rule-field" data-property="field">';

			output += '<option value="-1">Choose...</option>';

			for( index in fields ) {
				output += '<option ' + ( value == fields[index]['id'] ? 'selected' : '' ) + ' value="' + fields[index]['id'] + '">' + fields[index]['field-title'] + '</option>';
			}

		output += '</select>';

		return output;

	};


	/*
	 * Returns the HTML for the Select drop-down that contains all possible operators
	 *
	 * @param string value - the operator that should be selected in the drop-down
	 *
	 */
	this.get_view_rule_field_operator = function( value ) {

		var output = '<select data-property="operator">';

			for( index in operators ) {
				output += '<option ' + ( value == operators[index] ? 'selected' : '' ) + ' value="' + operators[index] + '">' + operators[index] + '</option>';
			}

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
	this.get_view_rule_field_value = function( value, field_id ) {

		// The options for the drop-down
		var options = [],
			labels = [],
			output  = '',
			select_values = '',
			select_labels = '',
			field   = ( fields[field_id] ? fields[field_id] : null );



		// Check to see if there are any options and populate the options var
		if( field_id != -1 && field != null ) {

			if( field['options'] != '' ) {
				select_values = field['options'];
			}else if ( field['user-roles'] ) {
				select_values = field['user-roles'];
				select_labels = field['user-role-names'];
			}else if ( field['subscription-plan-ids'] ){
				select_values = field['subscription-plan-ids'];
				select_labels = field['subscription-plan-names'];
			}

			if ( select_values != '' ) {
				options = select_values.split(',').map(function (val) {	return val.trim() });
				if ( select_labels != '') {
					labels = select_labels.split(',').map(function (val) { return val.trim() });
				}else{
					labels = options;
				}
			}

		}

		// If no options are available return an input field
		if( options.length == 0 ) {

			output += '<input type="text" class="wppb-cf-rule-value" data-property="value" value="' + value + '" />';

		// If there are options return a select drop-down
		} else {
			output += '<select class="wppb-cf-rule-value" data-property="value">';

				output += '<option value="">Choose...</option>';

				for( index in options ) {
					output += '<option ' + ( value == options[index] ? 'selected' : '' ) + ' value="' + options[index] + '">' + labels[index] + '</option>';
				}
			output += '</select>';
		}

		return output;

	};


	/*
	 * Returns the HTML for the possible actions an admin can do with a rule
	 * 
	 */
	this.get_view_rule_actions = function() {

		var output = '';

		output += '<span class="wppb-cf-rule-action wppb-cf-add">+</span>';
		output += '<span class="wppb-cf-rule-action wppb-cf-remove">-</span>';

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
        $input_wrapper.find('.wppb-cf-rule').each( function() {

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

            // Check to see if the field still exists in the manage_fields
            var exists = false;
            for( var index in fields ) {
                if( fields[index].id == rule.field )
                    exists = true;
            }

            // If the field exists update the view of the rule
            if( exists )
                $this.replaceWith( _this.get_view_rule( rule_id, rule ) );

            // If the field doesn't exist remove the view of the rule and if it is the last one add
            // an empty rule view
            else {

                if( $input_wrapper.find('.wppb-cf-wrapper[data-field-id="' + field_id + '"]').data('rules-count') == 1 )
                    $this.find('.wppb-cf-rule-action.wppb-cf-add').trigger('click');

                $this.find('.wppb-cf-rule-action.wppb-cf-remove').trigger('click');

            }

        });

    };


	/*
	 * Add event handlers
	 *
	 */
	function add_event_handlers() {

		var wrapper_selector = '.wppb-cf-wrapper[data-field-id=' + field_id + ']';

		// Remove rule
		jQuery(document)
            .off( 'click', wrapper_selector + ' .wppb-cf-rule-action.wppb-cf-remove' )
            .on( 'click', wrapper_selector + ' .wppb-cf-rule-action.wppb-cf-remove', function() {

			$rule = jQuery(this).closest('.wppb-cf-rule');
			$rule.remove();

			$input_wrapper.find('.wppb-cf-wrapper[data-field-id="' + field_id + '"]').attr('data-rules-count', jQuery( wrapper_selector + ' .wppb-cf-rule' ).length );

			// Update value to be saved in the DB
			_this.update_option_value();

		});


		// Add rule
		jQuery(document)
            .off( 'click', wrapper_selector + ' .wppb-cf-rule-action.wppb-cf-add' )
            .on( 'click', wrapper_selector + ' .wppb-cf-rule-action.wppb-cf-add', function() {

			$rule = jQuery(this).closest('.wppb-cf-rule');
			$rule.after( _this.get_view_rule( rules_added++, {} ) );

			$input_wrapper.find('.wppb-cf-wrapper[data-field-id="' + field_id + '"]').attr('data-rules-count', jQuery( wrapper_selector + ' .wppb-cf-rule' ).length );

		});


		// Change the Value field when changing the Field field
		jQuery(document).on( 'change', wrapper_selector + ' .wppb-cf-rule-field', function() {

			$this = jQuery(this);

			$this.parent().find('.wppb-cf-rule-value').remove();
			$this.parent().append( _this.get_view_rule_field_value( '', $this.val() ) );

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

		var wrapper_selector = '.wppb-cf-wrapper[data-field-id=' + field_id + ']';

		var obj = {};
		var rules = {};

		jQuery( wrapper_selector + ' .wppb-cf-rule' ).each( function( index ) {

			rules[index] = ( typeof rules[index] == 'undefined' ? {} : rules[index] );

			jQuery(this).find('select, input').each( function() {

				var property = jQuery(this).data('property');
				var value 	 = jQuery(this).val();

				rules[index][property] = value;

			});

		});

		obj.action_type = jQuery( wrapper_selector + ' select[data-property="action_type"]' ).val();
		obj.logic_type = jQuery( wrapper_selector + ' select[data-property="logic_type"]' ).val();
		obj.rules = rules;

		$input.val( JSON.stringify( obj ) );

	};

}

/*
 * Make the Conditional Logic App available for usage
 *
 */
var wppb_conditional_logic = new WPPB_Conditional_Logic();

// Initialize the Conditional Logic App after jQuery is ready
jQuery( function($) { wppb_conditional_logic.init(); });