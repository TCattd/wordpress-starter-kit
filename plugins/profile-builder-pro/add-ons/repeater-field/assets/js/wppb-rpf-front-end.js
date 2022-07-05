/******************************************************************************************
 *
 * Controller Class for Repeater Fields functionality
 *
 ******************************************************************************************/
function WPPB_RepeatersApp(){

    var setRepeaterFields = function() {
        var allRepeaterFields = [];
        var htmlElements = jQuery('.wppb-repeater');
        var i;
        for (i = 0; i < htmlElements.length; i++) {
            var repeaterField = new WPPB_RepeaterField( htmlElements[i] );
            allRepeaterFields.push(repeaterField);
        }
        return allRepeaterFields;
    };

    var repeaterFields = setRepeaterFields();

    var remove_template_fields = function(){
        var i;
        for ( i = 0; i < repeaterFields.length; i++ ) {
            repeaterFields[i].removeTemplate();
        }
    };

    var addEventHandlers = function(){
        jQuery('.wppb-edit-user, .wppb-register-user').on('submit', remove_template_fields );
    };

    addEventHandlers();
}



/******************************************************************************************
 *
 * Class representing an individual Repeater Field with all its groups of fields
 *
 ******************************************************************************************/
function WPPB_RepeaterField( repeaterFieldHTML ){
    var _this = this;
    var DATA_RPF_META_NAME = 'data-wppb-rpf-meta-name';
    var DATA_RPF_SET = 'data-wppb-rpf-set';
    var DATA_RPF_LIMIT = 'data-wppb-rpf-limit';
    var DATA_RPF_REMOVE_GROUP_MESSAGE = 'data-wppb-rpf-remove-group-message';
    var DATA_RPF_LIMIT_RULES = 'data-wppb-rpf-limit-rules';
    var DATA_RPF_GENERAL_LIMIT = 'data-wppb-rpf-general-limit';
    var DATA_RPF_PMS_ROLE_SUBSCRIPTION_IDS = 'data-wppb-rpf-pms-role-subscription-ids';
    this.repeaterMetaName = jQuery( repeaterFieldHTML ).attr( DATA_RPF_META_NAME );
    this.uiObject = jQuery ( "[" + DATA_RPF_META_NAME + "='" + _this.repeaterMetaName + "']");
    var inputCount = _this.uiObject.children( 'input.wppb-rpf-extra-groups-count' );
    var template = _this.uiObject.children("[" + DATA_RPF_SET + "='template']");
    var limit = parseInt( _this.uiObject.attr( DATA_RPF_LIMIT ) );
    var generalLimit = parseInt( _this.uiObject.attr( DATA_RPF_GENERAL_LIMIT ) );
    var remove_repeater_group_message = _this.uiObject.attr( DATA_RPF_REMOVE_GROUP_MESSAGE );
    var popup = this.uiObject.find('.wppb-rpf-overlay');

    this.parse_json = function( json_to_parse ){
        if ( json_to_parse ){
            return JSON.parse( json_to_parse );
        }
        return {};
    };

    var limitRules = _this.parse_json( _this.uiObject.attr( DATA_RPF_LIMIT_RULES  ) );
    var pmsSubscriptions = _this.parse_json( _this.uiObject.attr( DATA_RPF_PMS_ROLE_SUBSCRIPTION_IDS ) );


    var updateCounter = function(){
        var number_of_groups = repeaterFields.length;
        var i;
        inputCount.val( number_of_groups - 1 );
        if ( number_of_groups == 1 ){
            repeaterFields[0].singleGroupPresent();
        }else if ( number_of_groups > 1 ) {
            for ( i = 0; i < number_of_groups; i++ ){
                repeaterFields[i].multipleGroupsPresent();
            }
        }
        if ( limit != 0 ){
            if ( number_of_groups >= limit ){
                for ( i = 0; i < number_of_groups; i++ ){
                    repeaterFields[i].limitReached();
                    if ( i + 1  > limit ){
                        repeaterFields[i].limitExceeded();
                    }else{
                        repeaterFields[i].limitNotExceeded();
                    }
                }
            }else{
                for ( i = 0; i < number_of_groups; i++ ){
                    repeaterFields[i].limitNotReached();
                }
            }
        }
    };

    var updateLimit = function () {
        if ( limitRules != null ) {
            var selectedRoles = [ getSelectedPbRole(), getSelectedPmsRole()];

            var arrayEmpty = true;
            for (var i = 0; i < selectedRoles.length; i++) {
                if ( selectedRoles[i] != '' ){
                    arrayEmpty = false;
                    break;
                }
            }
            if ( arrayEmpty ){
                return;
            }

            var new_limit = 'not_set';
            for ( var index in limitRules.rules ) {
                if ( jQuery.inArray ( limitRules.rules[index].role, selectedRoles ) !== -1 ){
                    var ruleLimit = parseInt(limitRules.rules[index].value);
                    if ( new_limit === 'not_set' || ( ( new_limit !== 'not_set' ) && ( new_limit < ruleLimit || ruleLimit == 0 ) ) ) {
                        new_limit = ruleLimit;
                        if (new_limit == 0) {
                            break;
                        }
                    }
                }
            }

            if ( new_limit != 'not_set' ){
                limit = new_limit;
            }else{
                limit = generalLimit
            }
            updateCounter();
        }
    };

    var getSelectedPbRole = function(){
        var selectRoleValue = jQuery( '.wppb-select-user-role select' ).val();
        if ( selectRoleValue )
            return selectRoleValue;
        else
            return '';
    };

    var getSelectedPmsRole = function(){
        if ( pmsSubscriptions == null ){
            return '';
        }

        var selectedSubscriptionId =  jQuery( 'input[name=subscription_plans]:checked' ).val();
        for ( var index in pmsSubscriptions ){
            if ( pmsSubscriptions[index].subscription_id == selectedSubscriptionId ){
                return pmsSubscriptions[index].role;
            }
        }
        // in case something goes wrong and no role is found
        return '';
    };

    var setRepeaterGroups = function (repeaterFieldHTML) {
        var allRepeaterGroups = [];
        var htmlElements = jQuery(repeaterFieldHTML).children('.wppb-rpf-group:not([data-wppb-rpf-set="template"])');
        var i;
        for (i = 0; i < htmlElements.length; i++) {
            var setNumber = jQuery( htmlElements[i] ).attr( DATA_RPF_SET );
            allRepeaterGroups[i] = new WPPB_RepeaterGroup( setNumber, setNumber, template, _this );
        }
        return allRepeaterGroups;
    };


    var removeRequiredAttributeForTemplates = function(){
        jQuery ( template ).find('[required]').each(function () {
            jQuery(this).removeAttr('required');
            jQuery(this).attr('wppb_temp_required', '');
        });

        // templates already hidden with conditional fields
        jQuery ( template ).find('[wppb_cf_temprequired]').each(function () {
            jQuery(this).removeAttr('wppb_cf_temprequired');
            jQuery(this).attr('wppb_temp_required', '');
        });

        // templates already hidden multistep forms
        jQuery ( template ).find('[wppb-msf-temp-required]').each(function () {
            jQuery(this).removeAttr('wppb-msf-temp-required');
            jQuery(this).attr('wppb_temp_required', '');
        });
    };

    var repeaterFields = setRepeaterGroups(repeaterFieldHTML);
    var repeaterFieldsUniqueSetIndex = repeaterFields.length;
    removeRequiredAttributeForTemplates();
    updateLimit();
    updateCounter();

    this.getRepeaterGroup = function( setOrder ){
        return repeaterFields[setOrder];
    };

    this.addRepeaterGroup = function ( groupClicked ){
        if ( ( limit != 0 ) && ( repeaterFields.length >= limit ) ){
            popup.addClass( 'wppb-rpf-popup-open' );
            return;
        }

        groupClicked = parseInt(groupClicked);
        var i;
        for ( i = groupClicked + 1; i < repeaterFields.length; i++ ) {
            repeaterFields[i].changeIndexTo( i + 1 );
        }

        var newRepeaterGroup = new WPPB_RepeaterGroup( repeaterFieldsUniqueSetIndex, groupClicked + 1, template, _this );
        repeaterFieldsUniqueSetIndex++;

        repeaterFields.splice( groupClicked + 1, 0, newRepeaterGroup );

        updateCounter();
    };

    this.removeRepeaterGroup = function ( groupClicked ) {
        if ( repeaterFields.length <= 1 ){
            return;
        }

        var confirmation = confirm(remove_repeater_group_message);
        if ( confirmation != true ){
            return;
        }

        groupClicked = parseInt(groupClicked);
        var i;
        for ( i = groupClicked + 1; i < repeaterFields.length; i++ ) {
            repeaterFields[i].changeIndexTo( i - 1 );
        }
        repeaterFields[groupClicked].removeHTML();

        repeaterFields.splice( groupClicked, 1 );

        updateCounter();
    };

    this.removeTemplate = function (){
        jQuery( template ).remove();
    };

    var closePopup = function(){
        popup.removeClass( 'wppb-rpf-popup-open' );
    };

    var addEventHandlers = function(){
        popup.on( 'click', closePopup );
        jQuery(document).on('change','.wppb-select-user-role select, input[name=subscription_plans]', updateLimit);
    };

    addEventHandlers();
}


/******************************************************************************************
 *
 * Class representing an individual group of fields, part of the Repeater Field
 *
 ******************************************************************************************/
function WPPB_RepeaterGroup( setNumber, setOrder, template, repeaterField ){
    var DATA_RPF_SET = 'data-wppb-rpf-set';
    var DATA_RPF_SET_ORDER = 'data-wppb-rpf-set-order';
    var _template = template;
    var _templateFields = _template.find('.wppb-form-field');
    var _repeaterField = repeaterField;
    var _setNumber = setNumber;
    var _setOrder = setOrder;

    var configureNewGroup = function(){
        var precedentRepeaterGroup = _repeaterField.getRepeaterGroup(_setOrder - 1);
        var clone = _template.clone();
        clone.attr(DATA_RPF_SET, _setNumber);
        precedentRepeaterGroup.getUiObject().after(clone);
        return jQuery( _repeaterField.uiObject ).find( ".wppb-rpf-group[" + DATA_RPF_SET + "='" + _setNumber + "']");
    };

    /*
     * Removes the _0 index from the template_id and adds the new_set_number index.
     *
     */
    var add_index_to_string = function (template_id, new_set_number) {
        var replacement;
        if (new_set_number == 0) {
            replacement = '';
        } else {
            replacement = "_" + new_set_number;
        }
        return template_id.replace(/_0([^0]*)$/, replacement + '$1');
    };

    var changeHTMLIndexes = function( uiObjectToIndex, newIndex){
        uiObjectToIndex.attr( DATA_RPF_SET_ORDER, newIndex );

        uiObjectToIndex.attr('id', 'wppb-rpf-set-' + _repeaterField.repeaterMetaName + '_' +  newIndex );

        uiObjectToIndex.find('.wppb-form-field').each(function( iterator ) {
            var current_element = this;
            var template_element = _templateFields[iterator];
            var allowed_upload_extensions = '';
            //handle allowed upload extensions for simple upload field in repeater
            var parent = jQuery(template_element).attr('id').substring(0, jQuery(template_element).attr('id').lastIndexOf("_"));
            jQuery('#' + parent).children('input[type="hidden"]').each(function(i) {
                if (jQuery(this).attr('id') !== undefined && jQuery(this).attr('id').startsWith('allowed_extensions_')){
                    allowed_upload_extensions = jQuery(this).val();
                }
            });
            var attributes_array = ['name', 'id', 'for', 'data-upload_mn', 'data-upload_input'];

            var old_id = jQuery(template_element).attr('id');
            var jqueryCurrentElement = jQuery(current_element)
            jqueryCurrentElement.attr('id', add_index_to_string(old_id, newIndex));
            //reproduce simple upload extension constraints in newly added simple upload field in repeater
            if (allowed_upload_extensions != '') {
                jQuery('#' + jqueryCurrentElement.attr('id')).children('input[type="hidden"]').each(function (i) {
                    if (jQuery(this).attr('id').startsWith('allowed_extensions_')) {
                        jQuery(this).val(allowed_upload_extensions);
                    }
                });
            }
            attributes_array.forEach(function (attribute) {
                var template_attributes = jQuery(template_element).find('[' + attribute + ']');
                jqueryCurrentElement.find('[' + attribute + ']').each(function (iterator) {
                    var old_id = jQuery(template_attributes[iterator]).attr(attribute);
                    if (old_id) {
                        jQuery(this).attr(attribute, add_index_to_string(old_id, newIndex));
                    }
                });
            });

            if ( jqueryCurrentElement.hasClass( 'wppb-map') ){
                // Map field dynamically adds input fields which are not present in the template. The name attributes for these inputs need updated indexes too
                var correctMetaName = jqueryCurrentElement.find('label').first().attr('for');
                jqueryCurrentElement.find('input.wppb-map-marker').each(function(){
                    jQuery(this).attr('name', correctMetaName + '[]');
                });
            }

        });
    };

    var setUiObject = function(){
        var newUIObject = jQuery( _repeaterField.uiObject ).find( ".wppb-rpf-group[" + DATA_RPF_SET + "='" + _setNumber + "']");
        if ( newUIObject.length == 0 ){
            newUIObject = configureNewGroup();
            changeHTMLIndexes( newUIObject, _setOrder );

            // conditional fields
            newUIObject.find('[wppb_temp_required]').each(function () {
                jQuery(this).removeAttr('wppb_temp_required');
                jQuery(this).attr('required', '');
            });
        }
        return newUIObject;
    };

    var uiObject = setUiObject(_setNumber);

    this.getUiObject = function (){
        return uiObject;
    };


    var bindSpecificFieldEvents = function( ){
        if ( uiObject.find('.wppb-datepicker').length > 0 ) {
            jQuery('.wppb-user-forms *').removeClass('hasDatepicker');
            wppb_initialize_datepicker();
        }
        if ( uiObject.find('.wppb-colorpicker').length > 0 ){
            wppb_initialize_colorpicker();
        }
        if ( uiObject.find('.wppb-phone').length > 0 ){
            wppb_initialize_phone_field();
        }
        if ( uiObject.find('.wppb-map').length > 0){
            wppb_initialize_map();
        }

        var select2Fields = uiObject.find('.wppb-select2, .wppb-select2-multiple');
        if ( select2Fields.length > 0 && ( typeof wppb_select2_initialize === "function" ) ){
            select2Fields.each(function(){
                jQuery( '.select2-container' ).remove();
            });
            wppb_select2_initialize();
        }

        var selectCPTFields = uiObject.find('.wppb-select-cpt');
        if ( selectCPTFields.length > 0 ){
            selectCPTFields.each(function(){
                jQuery( '.wppb-select-cpt .select2-container' ).remove();
            });
            jQuery('.custom_field_cpt_select').each( function(){
                var currentCptSelect = this;
                jQuery( currentCptSelect ).select2();
            });
        }
    };

    var clickAddGroup = function(){
        _repeaterField.addRepeaterGroup( _setOrder );
        bindSpecificFieldEvents();
    };

    var clickRemoveGroup = function(){
        _repeaterField.removeRepeaterGroup( _setOrder );
        bindSpecificFieldEvents();
    };

    var bindEvents = function (){
        uiObject.find( '.wppb-rpf-add' ).on("click", clickAddGroup );
        uiObject.find( '.wppb-rpf-remove' ).on("click", clickRemoveGroup);
    };

    this.changeIndexTo = function( newIndex ){
        changeHTMLIndexes( uiObject, newIndex );
        _setOrder = newIndex;
    };

    this.removeHTML = function( ){
        uiObject.remove();
    };

    this.singleGroupPresent = function() {
        uiObject.addClass('wppb-rpf-singular-set');
    };

    this.multipleGroupsPresent = function() {
        uiObject.removeClass('wppb-rpf-singular-set');
    };

    this.limitReached = function() {
        uiObject.addClass('wppb-rpf-limit-reached');
    };

    this.limitNotReached = function() {
        uiObject.removeClass('wppb-rpf-limit-reached');
    };

    this.limitExceeded = function() {
        uiObject.addClass('wppb-rpf-limit-exceeded');
    };

    this.limitNotExceeded = function() {
        uiObject.removeClass('wppb-rpf-limit-exceeded');
    };

    bindEvents();
}


/*
 * Make the Repeater Field functionality available for usage
 *
 */
var wppbRepeaterApp;

// Initialize the Repeater Field App after jQuery is ready
jQuery( function() { wppbRepeaterApp = new WPPB_RepeatersApp(); });
