jQuery(document).ready(function($) {

    setTimeout(piotnetformsPreviewSubmission(), 1000);

	$(document).on('keyup change','[data-piotnetforms-id]', $.debounce( 250, function(){
			piotnetformsPreviewSubmission(); 
		})
	);

	function piotnetformsPreviewSubmission() {
		var $previewSubmission = $(document).find('[data-piotnetforms-preview-submission]');

		if ($previewSubmission.length > 0) {
			$previewSubmission.each(function(){
		    	var formID = $(this).attr('data-piotnetforms-preview-submission'),
		    		$fields = $(document).find('[data-piotnetforms-id="'+ formID +'"]'),
		    		fieldsOj = [],
		    		formData = new FormData();

				var $submit = $(this);
				var $parent = $submit.closest('.piotnetforms-submit');

				$fields.each(function(){
					if ( $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 ) {
						if ( $(this).data('piotnetforms-stripe') == undefined && $(this).data('piotnetforms-html') == undefined ) {
							var $checkboxRequired = $(this).closest('.piotnetforms-field-type-checkbox.piotnetforms-field-required');
							var checked = 0;
							if ($checkboxRequired.length > 0) {
								checked = $checkboxRequired.find("input[type=checkbox]:checked").length;
							} 

							$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html('');

							var fieldType = $(this).attr('type'),
								fieldName = $(this).attr('name');

							var $repeater = $(this).closest('[data-piotnetforms-repeater-form-id]'),
								repeaterID = '',
								repeaterIDOne = '',
								repeaterLabel = '',
								repeaterIndex = -1,
								repeaterLength = 0;

							if ($repeater.length > 0) {
								var $repeaterParents = $(this).parents('[data-piotnetforms-repeater-form-id]');
								repeaterIDOne = $repeater.data('piotnetforms-repeater-id');
								$repeaterParents.each(function(){
									var repeaterParentID = $(this).data('piotnetforms-repeater-id'),
										$repeaterParentAll = $(document).find('[data-piotnetforms-repeater-form-id="' + formID + '"][data-piotnetforms-repeater-id="' + repeaterParentID + '"]');

									var repeaterParentIndex = $(this).index() - $repeaterParentAll.index();
									repeaterID += repeaterParentID + '|index' + repeaterParentIndex + '|' + fieldName.replace('[]','').replace('form_fields[','').replace(']','') + ',';
								});

								repeaterLabel = $repeater.data('piotnetforms-repeater-label');

								var $repeaterAll = $(document).find('[data-piotnetforms-repeater-id="' + $repeater.data('piotnetforms-repeater-id') + '"]');
								repeaterLength = $repeater.siblings('[data-piotnetforms-repeater-id="' + $repeater.data('piotnetforms-repeater-id') + '"]').length + 1; 

								repeaterIndex = $repeater.index() - $repeaterAll.index();
							}

							if (fieldName.indexOf('[]') !== -1) {
			                    var fieldValueMultiple = [];

			                    if (fieldType == 'checkbox') {
			                        $(this).closest('.piotnetforms-fields-wrapper').find('[name="'+ fieldName + '"]:checked').each(function () {
			                            fieldValueMultiple.push($(this).val());
			                        }); 
			                    } else {
			                        fieldValueMultiple = $(this).val();
			                        if (fieldValueMultiple == null) {
			                            var fieldValueMultiple = [];
			                        }
			                    }

			                    fieldValue = '';
			                    var fieldValueByLabel = '';

			                    for (var j = 0; j < fieldValueMultiple.length; j++) {
			                    	if ($(this).data('piotnetforms-send-data-by-label') != undefined) {
			                    		var fieldValueSelected = fieldValueMultiple[j];

			                    		if (fieldType == 'checkbox') {
				                    		var $optionSelected = $(this).closest('.piotnetforms-fields-wrapper').find('[value="' + fieldValueSelected + '"]');
				                			if ($optionSelected.length > 0) {
				                				fieldValueByLabel += $optionSelected.data('piotnetforms-send-data-by-label') + ',';
				                			}
			                			} else {
			                				var $optionSelected = $(this).find('[value="' + fieldValueSelected + '"]');
				                			if ($optionSelected.length > 0) {
				                				fieldValueByLabel += $optionSelected.html() + ',';
				                			}
			                			}
			                		}

			                		fieldValue += fieldValueMultiple[j] + ',';
			                    }

			                    fieldValue = fieldValue.replace(/,(\s+)?$/, '');
							} else {
								if (fieldType == 'radio' || fieldType == 'checkbox') {
									if ($(this).data('piotnetforms-send-data-by-label') != undefined) {
										var fieldValueByLabel = $(this).closest('.piotnetforms-fields-wrapper').find('[name="'+ fieldName +'"]:checked').data('piotnetforms-send-data-by-label');
									}

									var fieldValue = $(this).closest('.piotnetforms-fields-wrapper').find('[name="'+ fieldName +'"]:checked').val();
				                } else {
				                	if ($(this).data('piotnetforms-calculated-fields') != undefined) {
				                		var fieldValue = $(this).siblings('.piotnetforms-calculated-fields-form').text();
				                	} else {
				                		if ($(this).data('piotnetforms-send-data-by-label') != undefined) {
				                			var fieldValueSelected = $(this).val().trim();
				                			var $optionSelected = $(this).find('[value="' + fieldValueSelected + '"]');
				                			if ($optionSelected.length > 0) {
				                				fieldValueByLabel = $optionSelected.html();
				                			}
				                		}

				                		var fieldValue = $(this).val().trim();
				                	}
				                }
							}
							
							if (fieldValue != undefined) {
								var fieldItem = {};
								fieldItem['label'] = $(this).closest('.piotnetforms-field-group').find('.piotnetforms-field-label').html();
								fieldItem['name'] = fieldName.replace('[]','').replace('form_fields[','').replace(']','');
								fieldItem['value'] = fieldValue;
								if (fieldValueMultiple != undefined) {
									fieldItem['value_multiple'] = fieldValueMultiple;
								}
								fieldItem['type'] = $(this).attr('type');

								if ($(this).attr('data-piotnetforms-address-autocomplete') !== undefined) {
									fieldItem['lat'] = $(this).attr('data-piotnetforms-google-maps-lat');
									fieldItem['lng'] = $(this).attr('data-piotnetforms-google-maps-lng');
									fieldItem['zoom'] = $(this).attr('data-piotnetforms-google-maps-zoom');
								}
											
								if (fieldValueByLabel != '') { 
									fieldItem['value_label'] = fieldValueByLabel;
								}
								
								if ($(this).closest('.piotnetforms-field-type-calculated_fields').length > 0) {
									fieldItem['calculation_results'] = $(this).val().trim();
								}
								
								if (!$(this).closest('.piotnetforms-widget').hasClass('piotnetforms-conditional-logic-hidden')) {
									fieldItem['repeater_id'] = repeaterID;
									fieldItem['repeater_id_one'] = repeaterIDOne;
									fieldItem['repeater_label'] = repeaterLabel;
									fieldItem['repeater_index'] = repeaterIndex; 
									fieldItem['repeater_length'] = repeaterLength;

									if ($(this).data('piotnetforms-remove-this-field-from-repeater') != undefined) {
			                    		fieldItem['repeater_remove_this_field'] = '1';
		                    		}

								    fieldsOj.push(fieldItem); 
								}
							}						
						}

				 	}
				});

				formData.append("action", "piotnetforms_ajax_form_builder_preview_submission");
				formData.append("fields", JSON.stringify(fieldsOj)); 

				if ($submit.attr('data-piotnetforms-preview-submission-remove-empty-fields') != undefined) {
					formData.append("remove_empty_fields", "");
				}

				if ($submit.attr('data-piotnetforms-preview-submission-custom-list-fields') != undefined) {
					formData.append("custom_list_fields", $submit.attr('data-piotnetforms-preview-submission-custom-list-fields') );
				}

				$.ajax({
					url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function (response) {
						$submit.html(response);
					}
				});
			});
		}
		
	}
});