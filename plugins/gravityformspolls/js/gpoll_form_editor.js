function StartChangePollType(type) {
    field = GetSelectedField();

    field["poll_field_type"] = type;

    return StartChangeInputType(type, field);
}

function SetDefaultValues_poll(field) {
    var strings = gpoll_form_editor_js_strings;
    field.poll_field_type = "radio";
    field.label = "Untitled Poll Field";
    field.inputType = "radio";
    field.inputs = null;
    field.enableChoiceValue = true;
    field.enablePrice = false;
    field.enableRandomizeChoices = false;
    if (!field.choices) {
        field.choices = new Array(new Choice(strings.firstChoice, GeneratePollChoiceValue(field)), new Choice(strings.secondChoice, GeneratePollChoiceValue(field)), new Choice(strings.thirdChoice, GeneratePollChoiceValue(field)));
    }
    return field;
}

function GeneratePollChoiceValue(field) {
    return 'gpoll' + field.id + 'xxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : r & 0x3 | 0x8;
        return v.toString(16);
    });

}

function gform_new_choice_poll(field, choice) {
    if (field.type == "poll")
        choice["value"] = GeneratePollChoiceValue(field);

    return choice;
}

//binding to the load field settings event to initialize
jQuery(document).bind("gform_load_field_settings", function (event, field, form) {
    jQuery('#field_randomize_choices').prop('checked', field.enableRandomizeChoices ? true : false);
    jQuery("#poll_field_type").val(field["poll_field_type"]);
    jQuery("#poll_question").val(field["label"]);

    if (field.type == 'poll') {

        jQuery('li.label_setting').hide();

        if (has_entry(field.id)) {
            jQuery("#poll_field_type").attr("disabled", true);
        } else {
            jQuery("#poll_field_type").removeAttr("disabled");
        }

    }
});
