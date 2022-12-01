(function (GF_Partial_Submissions_Feed_Settings, $) {

    "use strict";

	var strings = gf_partial_entries_feed_settings_strings;

    $(document).ready(function(){
        gform.addFilter( 'gform_conditional_logic_fields', function( options, form, selectedFieldId ){
            var partialEntriesOptions = [], fieldOptions, newOptions = [];
			fieldOptions = $.grep(options, function(option) {
                return option.value != 'partial_entry_percent' && option.value != 'required_fields_percent_complete';
            });

            partialEntriesOptions.push( {
                label: strings.allFields,
                value: 'partial_entry_percent',
                isSelected: selectedFieldId == 'partial_entry_percent' ? "selected='selected'" : ""
            } );
            partialEntriesOptions.push( {
                label: strings.requiredFields,
                value: 'required_fields_percent_complete',
                isSelected: selectedFieldId == 'required_fields_percent_complete' ? "selected='selected'" : ""
            } );
			newOptions.push({
				'options' :  fieldOptions,
				'label' : strings.fields
			});
			newOptions.push({
                'options' :  partialEntriesOptions,
                'label' : strings.progress
            });
            return newOptions;
        })
    });

}(window.GF_Partial_Submissions_Feed_Settings = window.GF_Partial_Submissions_Feed_Settings || {}, jQuery));
