/**
 * Conditional Logic Functionality
 */
( function( $ ) {

	/*
	 * The input-specific fields will be added automatically by GF. Let's add our top level field id as an option as well.
	 */
	gform.addFilter( 'gform_conditional_logic_fields', function( options, form ) {

		$.each( form.fields, function( i, field ) {

			if( GetInputType( field ) == 'chainedselect' ) {

				// find the first option from our field
				var optionIndex = false;
				for( var j = 0; j < options.length; j++ ) {
					if( parseInt( options[j].value ) == field.id ) {
						optionIndex = j;
						break;
					}
				}

				options.splice( optionIndex, 0, {
					label: GetLabel( field ),
					value: field.id
				} );

			}

		} );

		return options;
	} );

	gform.addFilter( 'gform_conditional_logic_values_input', function( markup, objectType, ruleIndex, selectedFieldId, selectedValue ) {

		var field           = GetFieldById( selectedFieldId ),
			isInputSpecific = parseInt( selectedFieldId ) != selectedFieldId,
			value           = typeof selectedValue == 'undefined' ? '' : selectedValue;

		if( ! field || GetInputType( field ) != 'chainedselect' ) {
			return markup;
		}

		if( ! isInputSpecific ) {
			var placeholder = [];
			$.each( field.inputs, function( i, input ) {
				placeholder.push( GetLabel( field, input.id, true ) );
			} );
			markup = '<input id="' + objectType + '_rule_value_' + ruleIndex + '" class="gfield_rule_select" type="text" value="' + value + '" placeholder="' + placeholder.join( '/' ) + '" onchange="SetRuleProperty(&quot;' + objectType + '&quot;, ' + ruleIndex + ', &quot;value&quot;, jQuery(this).val());" onkeyup="SetRuleProperty(&quot;' + objectType + '&quot;, ' + ruleIndex + ', &quot;' + value + '&quot;, jQuery(this).val());" />';
		} else {
			markup = '';
			$.each( getAllChoicesByInputId( selectedFieldId, field ), function( i, choice ) {
				var selectedMarkup = choice.value == value ? 'selected="selected"' : '';
				markup += '<option value="' + choice.value + '" ' + selectedMarkup + '>' + choice.text + '</option>';
			} );
			markup = '<select id="' + objectType + '_rule_value_' + ruleIndex + '" class="gfield_rule_select gfield_rule_value_dropdown">' + markup + '</select>';
		}
		return markup;
	} );

	function getAllChoicesByInputId( inputId, obj, depth, inputChoices ) {

		var targetDepth   = parseInt( String( inputId ).split( '.' )[1] ); // converts "4.3" to 3

		if( typeof depth == 'undefined' ) {
			depth = 1;
		}

		if( typeof inputChoices == 'undefined' ) {
			inputChoices = [];
		}

		if( typeof obj.choices == 'undefined' || ! obj.choices ) {
			return inputChoices;
		}

		if( depth == targetDepth ) {
			inputChoices = inputChoices.concat( obj.choices );
		} else {
			$.each( obj.choices, function( i, choice ) {
				inputChoices = getAllChoicesByInputId( inputId, choice, depth + 1, inputChoices );
			} );
		}

		if( depth == 1 ) {
			var values = [];
			for( var i = inputChoices.length - 1; i >= 0; i-- ) {
				if( $.inArray( inputChoices[i].value, values ) == -1 ) {
					values.push( inputChoices[i].value );
				} else {
					inputChoices.splice( i, 1 );
				}
			}
		}

		if( depth == 1 ) {
			inputChoices.sort( function( a, b ) {
				var x = a.value.toString().toLowerCase(),
					y = b.value.toString().toLowerCase();
				return x < y ? -1 : x > y ? 1 : 0;
			} );
		}

		return inputChoices;
	}

} )( jQuery );