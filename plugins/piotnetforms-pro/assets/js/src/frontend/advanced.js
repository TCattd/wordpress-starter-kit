// Conditional Logic
// Calculated Fields
// Booking
// Repeater

jQuery(document).ready(function($) {

    function piotnetformsConditionalLogicFormCheck($conditionalsSeclector) {
        $conditionalsSeclector.each(function(){
            var $fieldGroup = $(this), 
				notField = $(this).data('piotnetforms-conditional-logic-not-field'),
				speed = $fieldGroup.data('piotnetforms-conditional-logic-speed'),
                easing = $fieldGroup.data('piotnetforms-conditional-logic-easing'),
                conditionals = $fieldGroup.data('piotnetforms-conditional-logic'),
                showAction = true,
                $repeater = $(this).closest('[data-piotnetforms-repeater-id]');

            if (conditionals != undefined) {
				if (notField != undefined) {
					var $fieldWidget = $(this),
		            	popupLength = $fieldWidget.closest('.piotnetforms-location-popup').length,
		            	$fieldCurrent = $(this),
		            	formID = $fieldCurrent.data('piotnetforms-conditional-logic-not-field-form-id');
				} else {
					var $fieldWidget = $(this).closest('.piotnetforms-fields-wrapper,.piotnetforms-button-wrapper'),
		            	popupLength = $fieldWidget.closest('.piotnetforms-location-popup').length,
		            	$fieldCurrent = $fieldGroup.find('[data-piotnetforms-id]'),
		            	formID = $fieldCurrent.data('piotnetforms-id');
	            }

	            if ($(this).closest('.piotnetforms-multi-step-form__content-item-button').length > 0) {
	            	$fieldWidget = $(this).closest('.piotnetforms-multi-step-form__content-item-button');
					$fieldWidget.find('[data-piotnetforms-conditional-logic]').css({'display': 'block'});
	            }

	            if (JSON.stringify(conditionals).indexOf('piotnetforms') !== -1 && JSON.stringify(conditionals).indexOf('show') == -1) {
	            	showAction = false;
	            }

	            if (notField != undefined) {
					showAction = true;
				}


				// if (!$(this).hasClass('piotnetforms-button')) {
	            // Loop qua tat ca field trong form
	            //$(document).find('[name^="form_fields"][data-piotnetforms-id="' + formID + '"]').each(function(){
	                //if ($(this).attr('id') != undefined) {

	                	if (notField != undefined) {
							var fieldName = 1;
						} else {
							var fieldName = $fieldCurrent.attr('name').replace('[]','').replace('form_fields[','').replace(']','');
	            		}

	                    var error = 0,
	                        conditionalsCount = 0,
	                        conditionalsAndOr = '',
	                        indexConditonalRight = -1,
	                        setValue = '';

	                    for (var i = 0; i < conditionals.length; i++) {
	                    	if (notField != undefined) {
								var show = 1;
							} else {
								var show = $fieldCurrent.attr('name').replace('form_fields[','').replace('[]','').replace(']','');
	            			}

	                        var fieldIf = conditionals[i]['piotnetforms_conditional_logic_form_if'].trim().replace('[field id="','').replace('"]',''),
	                            comparison = conditionals[i]['piotnetforms_conditional_logic_form_comparison_operators'],
	                            value = conditionals[i]['piotnetforms_conditional_logic_form_value'],
	                            type = conditionals[i]['piotnetforms_conditional_logic_form_type'],
	                            errorCurrent = error;
	                        if (type == 'number') {
	                            value = parseInt( value );
	                        }

	                        if(fieldName == show) {
	                            conditionalsCount++;
	                            conditionalsAndOr = conditionals[i]['piotnetforms_conditional_logic_form_and_or_operators'];
	                            if(fieldIf != '') {

	                            	if ($repeater.length > 0) { 
	                            		var $fieldIfSelector = $repeater.find('[name="form_fields[' + fieldIf + ']"][data-piotnetforms-id="' + formID + '"]');
	                            		if ($fieldIfSelector.length == 0) {
	                            			var $fieldIfSelector = $(document).find('[name="form_fields[' + fieldIf + ']"][data-piotnetforms-id="' + formID + '"]');
	                            		}
	                            	} else {
	                            		var $fieldIfSelector = $(document).find('[name="form_fields[' + fieldIf + ']"][data-piotnetforms-id="' + formID + '"]');
	                            	}

	                                var fieldIfType = $fieldIfSelector.attr('type'); 

	                                if($fieldIfSelector.length > 0) {

	                                    if (fieldIfType == 'radio' || fieldIfType == 'checkbox') {
	                                    	if ($repeater.length > 0) { 
			                            		var fieldIfValue = $repeater.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + ']"]:checked').val();
			                            	} else {
			                            		var fieldIfValue = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + ']"]:checked').val();
			                            	}
	                                        
	                                    } else {
	                                        var fieldIfValue = $fieldIfSelector.val().trim();
	                                    }
	                                    
	                                    if (fieldIfValue != undefined && fieldIfValue.indexOf(';') !== -1) {
	                                        fieldIfValue = fieldIfValue.split(';');
	                                        fieldIfValue = fieldIfValue[0];
	                                    }

	                                    if (type == 'number') {
	                                        if (fieldIfValue == undefined) {
	                                            fieldIfValue = 0;
	                                        } else {
	                                            fieldIfValue = parseInt( fieldIfValue );
	                                            if (isNaN(fieldIfValue)) {
	                                                fieldIfValue = 0;
	                                            }
	                                        }
	                                    }

	                                    if(comparison == 'not-empty') {
	                                        if (fieldIfValue == '' || fieldIfValue == 0) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == 'empty') {
	                                        if (fieldIfValue != '' || fieldIfValue != 0) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '=') {
	                                        if (fieldIfValue != value) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '!=') {
	                                        if (fieldIfValue == value) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '>') {
	                                        if (fieldIfValue <= value) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '>=') {
	                                        if (fieldIfValue < value) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '<') {
	                                        if (fieldIfValue >= value) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '<=') {
	                                        if (fieldIfValue > value) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == 'checked') {
	                                        if (!$fieldIfSelector.prop('checked')) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == 'unchecked') {
	                                        if ($fieldIfSelector.prop('checked')) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == 'contains') {
	                                    	if (fieldIfValue.indexOf(value) === -1) {
	                                            error += 1;
	                                        }
	                                	}
	                                }

	                                if ($repeater.length > 0) { 
	                            		var $fieldIfSelectorMultiple = $repeater.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]');
	                            		if ($fieldIfSelectorMultiple.length == 0) {
	                            			var $fieldIfSelectorMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]');
	                            		}
	                            	} else {
	                            		var $fieldIfSelectorMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]');
	                            	}
	                                
	                                if($fieldIfSelectorMultiple.length > 0) {
	                                	if ($repeater.length > 0) { 
		                            		var fieldIfTypeMultiple = $repeater.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]').attr('type');
		                            	} else {
		                            		var fieldIfTypeMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]').attr('type');
		                            	}
	                                    
	                                    var fieldIfValueMultiple = $fieldIfSelectorMultiple.val(),
	                                        fieldIfValueMultiple = [];

	                                    if (fieldIfTypeMultiple == 'checkbox') {
	                                    	if ($repeater.length > 0) { 
			                            		$repeater.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]:checked').each(function () {
		                                            fieldIfValueMultiple.push( $(this).val() );
		                                        });
			                            	} else {
			                            		$(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldIf + '][]"]:checked').each(function () {
		                                            fieldIfValueMultiple.push( $(this).val() ); 
		                                        });
			                            	}
	                                        
	                                    } else {
	                                        fieldIfValueMultiple = $fieldIfSelectorMultiple.val();
	                                        if (fieldIfValueMultiple == null) {
	                                            var fieldIfValueMultiple = [];
	                                        }
	                                    }

	                                    if(comparison == 'not-empty') {
	                                        if (fieldIfValueMultiple.length == 0) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == 'empty') {
	                                        if (fieldIfValueMultiple.length > 0) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '=' || comparison == '!=' || comparison == '>' || comparison == '>=' || comparison == '<' || comparison == '<=') {
	                                        if (fieldIfValueMultiple.length == 0) {
	                                            error += 1;
	                                        }
	                                    }
	                                    if(comparison == '=') {
	                                        for (var j = 0; j < fieldIfValueMultiple.length; j++) {
	                                            if (fieldIfValueMultiple[j] != value) {
	                                                error += 1;
	                                            }
	                                        }
	                                    }
	                                    if(comparison == '!=') {
	                                        for (var j = 0; j < fieldIfValueMultiple.length; j++) {
	                                            if (fieldIfValueMultiple[j] == value) {
	                                                error += 1;
	                                            }
	                                        }
	                                    }
	                                    if(comparison == '>') {
	                                        for (var j = 0; j < fieldIfValueMultiple.length; j++) {
	                                            if (fieldIfValueMultiple[j] <= value) {
	                                                error += 1;
	                                            }
	                                        }
	                                    }
	                                    if(comparison == '>=') {
	                                        for (var j = 0; j < fieldIfValueMultiple.length; j++) {
	                                            if (fieldIfValueMultiple[j] < value) {
	                                                error += 1;
	                                            }
	                                        }
	                                    }
	                                    if(comparison == '<') {
	                                        for (var j = 0; j < fieldIfValueMultiple.length; j++) {
	                                            if (fieldIfValueMultiple[j] >= value) {
	                                                error += 1;
	                                            }
	                                        }
	                                    }
	                                    if(comparison == '<=') {
	                                        for (var j = 0; j < fieldIfValueMultiple.length; j++) {
	                                            if (fieldIfValueMultiple[j] > value) {
	                                                error += 1;
	                                            }
	                                        }
	                                    }
	                                    if(comparison == 'contains') {
	                                    	if (fieldIfValueMultiple.join().indexOf(value) === -1) {
	                                        	error += 1;
	                                        }
	                                	}
	                                }
	                            }
	                        }

	                        var $setValueForSelector = $fieldCurrent;

	                        if (errorCurrent == error) {
	                        	if(conditionals[i] != undefined ) {
		                        	if (conditionals[i]['piotnetforms_conditional_logic_form_set_value'] != undefined && conditionals[i]['piotnetforms_conditional_logic_form_action'].indexOf('set_value') !== -1 ) {
		                        		setValue = conditionals[i]['piotnetforms_conditional_logic_form_set_value'];
		                        		
		                        		if (conditionals[i]['piotnetforms_conditional_logic_form_set_value_for'] != undefined && conditionals[i]['piotnetforms_conditional_logic_form_set_value_for'] != '') {
		                        			
		                        			if ($repeater.length > 0) { 
			                            		var $setValueForSelector = $repeater.find('[data-piotnetforms-id="' + formID + '"][name^="form_fields[' + conditionals[i]['piotnetforms_conditional_logic_form_set_value_for'] + ']"]');
			                            	} else {
			                            		var $setValueForSelector = $(document).find('[data-piotnetforms-id="' + formID + '"][name^="form_fields[' + conditionals[i]['piotnetforms_conditional_logic_form_set_value_for'] + ']"]');
			                            	}

		                        		} else {
		                        			var $setValueForSelector = $fieldCurrent;
		                        			var setValueForThis = true;
		                        		}
		                        	}
	                        	}
	                        }
	                    }

	                    var checkSelect = $fieldCurrent.find('option:first');

	                    var checkRadioCheckbox = false;

	                    if ($setValueForSelector.attr('type') == 'radio' || $setValueForSelector.attr('type') == 'checkbox') {
	                    	checkRadioCheckbox = true;
	                    }

	                    var defaultValue = $fieldCurrent.data('piotnetforms-default-value');

	                    if (conditionalsAndOr == 'or') {
	                        if (conditionalsCount > error) {
	                        	if (popupLength > 0) {
	                        		if($fieldCurrent.attr('type') != 'hidden') {
	                        			$fieldWidget.show();
	                        		} else {
	                        			if ($fieldCurrent.attr('data-date-format') !== undefined) {
	                        				$fieldWidget.show();
	                        			}
	                        		}
	                    			
	                    			$setValueForSelector.each(function(){
		                    			if (setValue != '' && checkRadioCheckbox ) {
	                    					if (setValue == $(this).val()) {
		                    					$(this).prop('checked', true);
		                    					//$(this).change();
		                    				}

		                    				if (setValue == 'unchecked' || setValue == 'checked') {
		                    					if (setValue == 'unchecked') {
			                    					$(this).prop('checked', false);
			                    					//$(this).change();
			                					} else {
			                						$(this).prop('checked', true);
			                						//$(this).change();
			                					}
		                					} else {
			                    				if (setValue == $(this).val()) {
			                    					$(this).prop('checked', true);
			                    					//$(this).change();
			                    				}
		                    				}
		                        		}

		                        		if (setValue != '' && !checkRadioCheckbox) {
		                        			$(this).val(setValue);
		                        			//$(this).change();
		                        		}

	                        		});
	                        	} else {

	                    			if($fieldCurrent.attr('type') != 'hidden') {
	                        			$fieldWidget.slideDown(speed,easing).removeClass('piotnetforms-conditional-logic-hidden');
	                        		} else {
	                        			if ($fieldCurrent.attr('data-date-format') !== undefined) {
	                        				$fieldWidget.slideDown(speed,easing).removeClass('piotnetforms-conditional-logic-hidden');
	                        			}
	                        		}
	                    			
	                    			$setValueForSelector.each(function(){
		                    			if (setValue != '' && checkRadioCheckbox ) {
	                    					if (setValue == $(this).val()) {
		                    					$(this).prop('checked', true);
		                    					//$(this).change();
		                    				}

		                    				if (setValue == 'unchecked' || setValue == 'checked') {
		                    					if (setValue == 'unchecked') {
			                    					$(this).prop('checked', false);
			                    					//$(this).change();
			                					} else {
			                						$(this).prop('checked', true);
			                						//$(this).change();
			                					}
		                					} else {
			                    				if (setValue == $(this).val()) {
			                    					$(this).prop('checked', true);
			                    					//$(this).change();
			                    				}
		                    				}
		                        		}

		                        		if (setValue != '' && !checkRadioCheckbox) {
		                        			$(this).val(setValue);
		                        			//$(this).change();
		                        		}

	                        		});
	                        	} 
	                        } else {
	                            if (popupLength > 0) {
	                            	if (showAction) {
	                            		$fieldWidget.hide();

	                            		if (notField != undefined) {
	                            			var repeaterID = $fieldGroup.data('piotnetforms-repeater-id');

	                            			if (repeaterID != undefined) {
	                            				$fieldGroup.siblings('[data-piotnetforms-repeater-id="'+ repeaterID +'"]').remove();
	                            			}

	                            			var $fieldsInside = $fieldWidget.find('[data-piotnetforms-id]');

	                            			$fieldsInside.each(function(){
	                            				var checkSelect = $(this).find('option:first');

												var checkRadioCheckbox = false;

												if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
													checkRadioCheckbox = true;
												}

												var defaultValue = $(this).data('piotnetforms-default-value');

												if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
				                            		if (checkRadioCheckbox) {
				                            			$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
				                            			if (defaultValue == $(this).val()) {
															$(this).prop('checked', true);
														}
				                    				} else {
				                    					$(this).val(defaultValue);
				                    				}
				                            	} else {
				                            		if (checkSelect.length != 0) {
					                    				$(this).val((checkSelect.val()));
					                    			} else {
														if (checkRadioCheckbox) {
					                    					$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
					                    				} else {
					                    					$(this).val('');
					                    				}
					                    			}
				                            	}
	                            			});
		                            	}
	                            	}

	                            	if (notField == undefined) {
		                            	if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
		                            		if (checkRadioCheckbox) {
		                            			$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
		                            			if (defaultValue == $fieldCurrent.val()) {
													$fieldCurrent.prop('checked', true);
												}
		                    				} else {
		                    					$fieldCurrent.val(defaultValue);
		                    				}
		                            	} else {
		                            		if (checkSelect.length != 0) {
			                    				$fieldCurrent.val((checkSelect.val()));
			                    			} else {
												if (checkRadioCheckbox) {
			                    					$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
			                    					//$fieldCurrent.change();
			                    				} else {
			                    					$fieldCurrent.val('');
			                    					//$fieldCurrent.change();
			                    				}
			                    			}
		                            	}
	                            	}
	                    			
	                        	} else {
	                        		if (showAction) {
	                    				$fieldWidget.slideUp(speed,easing).addClass('piotnetforms-conditional-logic-hidden');

	                    				if (notField != undefined) {
	                    					var repeaterID = $fieldGroup.data('piotnetforms-repeater-id');
	                            			if (repeaterID != undefined) {
	                            				$fieldGroup.siblings('[data-piotnetforms-repeater-id="'+ repeaterID +'"]').remove();
	                            			}
	                            			
	                            			var $fieldsInside = $fieldWidget.find('[data-piotnetforms-id]');

	                            			$fieldsInside.each(function(){
	                            				var checkSelect = $(this).find('option:first');

												var checkRadioCheckbox = false;

												if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
													checkRadioCheckbox = true;
												}

												var defaultValue = $(this).data('piotnetforms-default-value');

												if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
				                            		if (checkRadioCheckbox) {
				                            			$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
				                            			if (defaultValue == $(this).val()) {
															$(this).prop('checked', true);
														}
				                    				} else {
				                    					$(this).val(defaultValue);
				                    				}
				                            	} else {
				                            		if (checkSelect.length != 0) {
					                    				$(this).val((checkSelect.val()));
					                    			} else {
														if (checkRadioCheckbox) {
					                    					$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
					                    				} else {
					                    					$(this).val('');
					                    				}
					                    			}
				                            	}
	                            			});

		                            	}
	                				}
	                    			
	                    			if (notField == undefined) {
		                            	if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
		                            		if (checkRadioCheckbox) {
		                            			$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
		                            			if (defaultValue == $fieldCurrent.val()) {
													$fieldCurrent.prop('checked', true);
												}
		                    				} else {
		                    					$fieldCurrent.val(defaultValue);
		                    				}
		                            	} else {
		                            		if (checkSelect.length != 0) {
			                    				$fieldCurrent.val((checkSelect.val()));
			                    			} else {
			                    				if (checkRadioCheckbox) {
			                    					$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
			                    					//$fieldCurrent.change();
			                    				} else {
			                    					$fieldCurrent.val('');
			                    					//$fieldCurrent.change();
			                    				}
												
			                    			}
		                            	}
	                            	}
	                        	}
	                        }
	                    } 

	                    if (conditionalsAndOr == 'and') {
	                        if (error == 0) {
	                            if (popupLength > 0) {

	                        		if($fieldCurrent.attr('type') != 'hidden') {
	                        			$fieldWidget.show();
	                        		} else {
	                        			if ($fieldCurrent.attr('data-date-format') !== undefined) {
	                        				$fieldWidget.show();
	                        			}
	                        		}
	                    			
	                    			$setValueForSelector.each(function(){
		                    			if (setValue != '' && checkRadioCheckbox ) {
	                    					if (setValue == $(this).val()) {
		                    					$(this).prop('checked', true);
		                    					//$(this).change();
		                    				}

		                    				if (setValue == 'unchecked' || setValue == 'checked') {
		                    					if (setValue == 'unchecked') {
			                    					$(this).prop('checked', false);
			                    					//$(this).change();
			                					} else {
			                						$(this).prop('checked', true);
			                						//$(this).change();
			                					}
		                					} else {
			                    				if (setValue == $(this).val()) {
			                    					$(this).prop('checked', true);
			                    					//$(this).change();
			                    				}
		                    				}
		                        		}

		                        		if (setValue != '' && !checkRadioCheckbox) {
		                        			$(this).val(setValue);
		                        			//$(this).change();
		                        		}

	                        		});
	                        	} else {

	                        		if($fieldCurrent.attr('type') != 'hidden') {
	                        			$fieldWidget.slideDown(speed,easing).removeClass('piotnetforms-conditional-logic-hidden');
	                        		} else {
	                        			if ($fieldCurrent.attr('data-date-format') !== undefined) {
	                        				$fieldWidget.slideDown(speed,easing).removeClass('piotnetforms-conditional-logic-hidden');
	                        			}
	                        		}
	                    			
	                    			$setValueForSelector.each(function(){
		                    			if (setValue != '' && checkRadioCheckbox ) {
	                    					if (setValue == $(this).val()) {
		                    					$(this).prop('checked', true);
		                    					//$(this).change();
		                    				}

		                    				if (setValue == 'unchecked' || setValue == 'checked') {
		                    					if (setValue == 'unchecked') {
			                    					$(this).prop('checked', false);
			                    					//$(this).change();
			                					} else {
			                						$(this).prop('checked', true);
			                						//$(this).change();
			                					}
		                					} else {
			                    				if (setValue == $(this).val()) {
			                    					$(this).prop('checked', true);
			                    					//$(this).change();
			                    				}
		                    				}
		                        		}

		                        		if (setValue != '' && !checkRadioCheckbox) {
		                        			$(this).val(setValue);
		                        			//$(this).change();
		                        		}

	                        		});
	                        	} 
	                        } else {
	                            if (popupLength > 0) {
	                            	if (showAction) {
	                            		$fieldWidget.hide();

	                            		if (notField != undefined) {
	                            			var repeaterID = $fieldGroup.data('piotnetforms-repeater-id');
	                            			if (repeaterID != undefined) {
	                            				$fieldGroup.siblings('[data-piotnetforms-repeater-id="'+ repeaterID +'"]').remove();
	                            			}

	                            			var $fieldsInside = $fieldWidget.find('[data-piotnetforms-id]');

	                            			$fieldsInside.each(function(){
	                            				var checkSelect = $(this).find('option:first');

												var checkRadioCheckbox = false;

												if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
													checkRadioCheckbox = true;
												}

												var defaultValue = $(this).data('piotnetforms-default-value');

												if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
				                            		if (checkRadioCheckbox) {
				                            			$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
				                            			if (defaultValue == $(this).val()) {
															$(this).prop('checked', true);
														}
				                    				} else {
				                    					$(this).val(defaultValue);
				                    				}
				                            	} else {
				                            		if (checkSelect.length != 0) {
					                    				$(this).val((checkSelect.val()));
					                    			} else {
														if (checkRadioCheckbox) {
					                    					$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
					                    				} else {
					                    					$(this).val('');
					                    				}
					                    			}
				                            	}
	                            			});
		                            	}
	                            	}

	                            	if (notField == undefined) {
		                            	if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
		                            		if (checkRadioCheckbox) {
		                            			$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
		                            			if (defaultValue == $fieldCurrent.val()) {
													$fieldCurrent.prop('checked', true);
												}
		                    				} else {
		                    					$fieldCurrent.val(defaultValue);
		                    				}
		                            	} else {
		                            		if (checkSelect.length != 0) {
			                    				$fieldCurrent.val((checkSelect.val()));
			                    			} else {
												if (checkRadioCheckbox) {
			                    					$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
			                    					//$fieldCurrent.change();
			                    				} else {
			                    					$fieldCurrent.val('');
			                    					//$fieldCurrent.change();
			                    				}
			                    			}
		                            	}
	                            	}
	                    			
	                        	} else {
	                        		if (showAction) {
	                    				$fieldWidget.slideUp(speed,easing).addClass('piotnetforms-conditional-logic-hidden');

	                    				if (notField != undefined) {
	                    					var repeaterID = $fieldGroup.data('piotnetforms-repeater-id');
	                            			if (repeaterID != undefined) {
	                            				$fieldGroup.siblings('[data-piotnetforms-repeater-id="'+ repeaterID +'"]').remove();
	                            			}

	                            			var $fieldsInside = $fieldWidget.find('[data-piotnetforms-id]');

	                            			$fieldsInside.each(function(){
	                            				var checkSelect = $(this).find('option:first');

												var checkRadioCheckbox = false;

												if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
													checkRadioCheckbox = true;
												}

												var defaultValue = $(this).data('piotnetforms-default-value');

												if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
				                            		if (checkRadioCheckbox) {
				                            			$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
				                            			if (defaultValue == $(this).val()) {
															$(this).prop('checked', true);
														}
				                    				} else {
				                    					$(this).val(defaultValue);
				                    				}
				                            	} else {
				                            		if (checkSelect.length != 0) {
					                    				$(this).val((checkSelect.val()));
					                    			} else {
														if (checkRadioCheckbox) {
					                    					$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
					                    				} else {
					                    					$(this).val('');
					                    				}
					                    			}
				                            	}
	                            			});
		                            	}
	                				}
	                    			
	                    			if (notField == undefined) {
		                            	if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
		                            		if (checkRadioCheckbox) {
		                            			$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
		                            			if (defaultValue == $fieldCurrent.val()) {
													$fieldCurrent.prop('checked', true);
												}
		                    				} else {
		                    					$fieldCurrent.val(defaultValue);
		                    				}
		                            	} else {
		                            		if (checkSelect.length != 0) {
			                    				$fieldCurrent.val((checkSelect.val()));
			                    			} else {
			                    				if (checkRadioCheckbox) {
			                    					$fieldCurrent.prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
			                    					//$fieldCurrent.change();
			                    				} else {
			                    					$fieldCurrent.val('');
			                    					//$fieldCurrent.change();
			                    				}
												
			                    			}
		                            	}
	                            	}
	                        	}
	                        }
	                    }

	                    var $repeaterParent = $(this).closest('[data-piotnetforms-repeater-form-id]');

	                    if ($repeaterParent.length > 0) {
	                    	var $repeaterCalculatedFields = $repeaterParent.find('[data-piotnetforms-calculated-fields]');

	                    	$repeaterCalculatedFields.each(function(){
	                    		var fieldName = $(this).attr('name').replace('[]','').replace('form_fields[','').replace(']','');
	                    		piotnetformsCalculatedFieldsForm(fieldName);
	                    	});
	                    } else {
	                    	piotnetformsCalculatedFieldsForm(''); 
	                    }

	                //}
	            //});

	        	//} 
				
            }

        });
    }

    var $conditionals = $(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic]');
	if ($conditionals.length > 0) {
		piotnetformsConditionalLogicFormCheck($conditionals);
	}

	$(document).on('keyup change','[data-piotnetforms-id]', $.debounce( 200, function(){
		var $conditionals = $(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic]');
			piotnetformsConditionalLogicFormCheck($conditionals);
		})
	);

	function FormatNumberBy3(num, decpoint, sep) {
	  num = num.toLocaleString('en-US'); 
	  num = num.replace(/\./g, '|');
	  num = num.replace(/\,/g, sep);
	  num = num.replace(/\|/g, decpoint);
	  return num;
	}

	function round(value, decimals, decimalsSymbol, seperatorsSymbol, decimalsShow) {
		if (decimalsShow == '') {
			return FormatNumberBy3( Number(Math.round(value+'e'+decimals)+'e-'+decimals), decimalsSymbol, seperatorsSymbol );
		} else {
			return FormatNumberBy3( Number(Math.round(value+'e'+decimals)+'e-'+decimals).toFixed(decimals), decimalsSymbol, seperatorsSymbol );
		}
	}

	function roundValue(value, decimals, decimalsShow) {
		if (decimalsShow == '') {
			return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
		} else {
			return Number(Math.round(value+'e'+decimals)+'e-'+decimals).toFixed(decimals);
		}
	}

	function parseFloatWithRemoveSepChar(text, separator_char) {
		if (typeof text === 'string' && typeof separator_char === 'string') {
			text = text.replaceAll(separator_char, "");
		}
		return parseFloat(text);
	}

	function piotnetformsCalculatedFieldsForm(fieldNameElement) {
		var selector = '[data-piotnetforms-calculated-fields]';
		if (fieldNameElement != '') {
			fieldNameElement = '[field id="' + fieldNameElement + '"]';
			selector = "[data-piotnetforms-calculated-fields*='" + fieldNameElement + "'],[data-piotnetforms-calculated-fields-coupon-code='" + fieldNameElement + "']";
		}

        $(document).find(selector).each(function(){
            var $fieldWidget = $(this).closest('.piotnetforms-fields-wrapper'),
            	$fieldCurrent = $(this),
            	formID = $fieldCurrent.data('piotnetforms-id'),
                calculation = $fieldCurrent.data('piotnetforms-calculated-fields'),
                roundingDecimals = $fieldCurrent.data('piotnetforms-calculated-fields-rounding-decimals'),
                decimalsSymbol = $fieldCurrent.data('piotnetforms-calculated-fields-rounding-decimals-decimals-symbol'),
                decimalsShow = $fieldCurrent.data('piotnetforms-calculated-fields-rounding-decimals-show'),
                separatorsSymbol = $fieldCurrent.data('piotnetforms-calculated-fields-rounding-decimals-seperators-symbol'),
                $repeater = $(this).closest('[data-piotnetforms-repeater-form-id]');
            if (calculation.indexOf('field id') == -1) {

	            // Loop qua tat ca field trong form
	            $(document).find('[name^="form_fields"][data-piotnetforms-id="' + formID + '"]').each(function(){

	                if ($(this).attr('id') != undefined) {
	                    var fieldName = $(this).attr('name').replace('[]','').replace('form_fields[','').replace(']',''),
	                        $fieldSelector = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + ']"]'),
	                        fieldType = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + ']"]').attr('type'),
	                        $repeater_field = $(this).closest('[data-piotnetforms-repeater-form-id]');

	                    if($fieldSelector.length > 0) {
							var fieldValue = 0;
	                        if (fieldType === 'radio' || fieldType === 'checkbox') {
	                        	if ($repeater_field.length > 0) {
		                        	fieldValue = $repeater_field.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + ']"]:checked').val();
		                        } else {
		                        	fieldValue = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + ']"]:checked').val();
		                        }
	                        } else {
								if ($fieldSelector.val() !== null) {
									fieldValue = $fieldSelector.val().trim();
								}
	                        }

	                        if (!fieldValue) {
	                            fieldValue = 0;
	                        } else {
	                            fieldValue = parseFloatWithRemoveSepChar( fieldValue, separatorsSymbol );
	                            if (isNaN(fieldValue)) {
	                                fieldValue = 0;
	                            }
	                        }

	                        if ($repeater_field.length > 0) {
	                        	var $repeaterAll = $(document).find('[data-piotnetforms-repeater-form-id="' + $repeater.data('piotnetforms-repeater-form-id') + '"]'),
									repeaterIndex = $repeater.index() - $repeaterAll.index();

								window['piotnetforms_'+fieldName+'_piotnetpiotnetforms'+repeaterIndex+'x'] = fieldValue;
	                        } else {
	                        	window[fieldName] = fieldValue;
	                        }
	                    }

	                    if (fieldName.indexOf('[]') !== -1) {
	                        fieldName = fieldName.replace('[]','');

	                        if ($repeater_field.length > 0) {
	                        	var $fieldSelectorMultiple = $repeater_field.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]');
	                        } else {
	                        	var $fieldSelectorMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]');
	                        }

	                        if($fieldSelectorMultiple.length > 0) {
	                            fieldTypeMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]').attr('type');
	                            var fieldValueMultiple = [];

	                            if (fieldTypeMultiple == 'checkbox') {
	                            	if ($repeater_field.length > 0) {
			                        	$repeater_field.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]:checked').each(function (index,element) {
		                                    fieldValueMultiple.push($(this).val());
		                                });
			                        } else {
			                        	$(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]:checked').each(function (index,element) {
		                                    fieldValueMultiple.push($(this).val());
		                                });
			                        }
	                                
	                            } else {
	                                fieldValueMultiple = $fieldSelectorMultiple.val();
	                                if (fieldValueMultiple == null) {
	                                    var fieldValueMultiple = [];
	                                }
	                            }

	                            let fieldValueMultipleTotal = 0;

	                            for (var j = 0; j < fieldValueMultiple.length; j++) {
	                                fieldValue = parseFloatWithRemoveSepChar( fieldValueMultiple[j], separatorsSymbol );
	                                if (isNaN(fieldValue)) {
	                                    fieldValue = 0;
	                                }
	                                fieldValueMultipleTotal += fieldValue;
	                            }

	                            if ($repeater_field.length > 0) {
		                        	var $repeaterAll = $(document).find('[data-piotnetforms-repeater-form-id="' + $repeater.data('piotnetforms-repeater-form-id') + '"]'),
										repeaterIndex = $repeater.index() - $repeaterAll.index();

									window['piotnetforms_'+fieldName+'_piotnetpiotnetforms'+repeaterIndex+'x'] = fieldValueMultipleTotal;
		                        } else {
		                        	window[fieldName] = fieldValueMultipleTotal;
		                        }
	                            
	                        }
	                    }
	                }
	            });

            } else {
            	var fieldNameArray = calculation.match(/\"(.*?)\"/g);
            	if (fieldNameArray != null) {
	            	for (var jx = 0; jx<fieldNameArray.length; jx++) {
	            		var fieldNameSlug = fieldNameArray[jx].replace('"','').replace('"',''),
	            			$fieldSelectorExist = $(document).find('[data-piotnetforms-id="' + formID + '"][name^="form_fields[' + fieldNameSlug + ']"]'),
	                        $fieldSelector = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldNameSlug + ']"]');

	                    if($fieldSelectorExist.length > 0) {  

	                    	var fieldName = $fieldSelectorExist.attr('name').replace('form_fields[','').replace(']',''),
		                        fieldType = $fieldSelectorExist.attr('type');

		                        //console.log(fieldName);

		                    if($fieldSelector.length >= 1 && $fieldSelector.closest('[data-piotnetforms-repeater-id]').length == 0) {

		                        if (fieldType == 'radio' || fieldType == 'checkbox') {
		                            var fieldValue = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + ']"]:checked').val();
		                        } else {
									if ($fieldSelector.val() !== null) {
										var fieldValue = $fieldSelector.val().trim();
									}
		                        }

		                        if (fieldValue == undefined) {
		                            fieldValue = 0;
		                        } else {
		                            fieldValue = parseFloatWithRemoveSepChar( fieldValue, separatorsSymbol );
		                            if (isNaN(fieldValue)) {
		                                fieldValue = 0;
		                            }
		                        }

		                        if ($fieldSelector.attr('data-piotnetforms-date-range-days') != undefined) {
		                        	fieldValue = $fieldSelector.attr('data-piotnetforms-date-range-days');
		                        }

		                        if ($fieldSelector.attr('data-piotnetforms-date-calculate') != undefined) {
		                        	fieldValue = $fieldSelector.attr('data-piotnetforms-date-calculate');
		                        }

		                        if ($fieldSelector.attr('data-piotnetforms-booking-price') != undefined) {
		                        	fieldValue = $fieldSelector.attr('data-piotnetforms-booking-price');
		                        }

		                        window[fieldName] = parseFloatWithRemoveSepChar( fieldValue, separatorsSymbol );

		                        if ($fieldSelector.closest('[data-piotnetforms-conditional-logic]').length > 0 && $fieldSelector.closest('.piotnetforms-fields-wrapper').css('display') == 'none') {
		                        	window[fieldName] = 0;
		                        }
		                    }

		                    if($fieldSelector.length > 1 || $fieldSelector.length == 1 && $fieldSelector.closest('[data-piotnetforms-repeater-id]').length > 0) {
		                    	var $repeater_field = $fieldSelector.closest('[data-piotnetforms-repeater-id]');
		                    	if ($repeater_field.length > 0) {
			                    	$fieldSelector.each(function(){
			                    		if (fieldType == 'radio' || fieldType == 'checkbox') {
				                            var fieldValue = $repeater_field.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + ']"]:checked').val();
				                        } else {
				                            var fieldValue = $(this).val().trim();
				                        }

				                        if (fieldValue == undefined) {
				                            fieldValue = 0;
				                        } else {
				                            fieldValue = parseFloatWithRemoveSepChar( fieldValue, separatorsSymbol );
				                            if (isNaN(fieldValue)) {
				                                fieldValue = 0;
				                            }
				                        }

				                        if ($(this).attr('data-piotnetforms-date-range-days') != undefined) {
				                        	fieldValue = $(this).attr('data-piotnetforms-date-range-days');
				                        }

			                        	var $repeaterAll = $(document).find('[data-piotnetforms-repeater-form-id="' + $repeater.data('piotnetforms-repeater-form-id') + '"]'),
											repeaterIndex = $(this).closest('[data-piotnetforms-repeater-id]').index() - $repeaterAll.index();

										if ($(this).closest('[data-piotnetforms-conditional-logic]').length > 0 && $(this).closest('.piotnetforms-fields-wrapper').css('display') == 'none') {
				                        	fieldValue = 0;
				                        }

				                        window['piotnetforms_'+fieldName+'_piotnetpiotnetforms'+repeaterIndex+'x'] = parseFloatWithRemoveSepChar( fieldValue, separatorsSymbol );
			                    	});
		                    	}
		                    }

		                    if (fieldName.indexOf('[]') !== -1) {

		                        fieldName = fieldName.replace('[]','');
		                        var $fieldSelectorMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]'),
		                        	fieldTypeMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]').attr('type');

	                        	$repeater_field = $(this).closest('[data-piotnetforms-repeater-id]');

		                        if($fieldSelectorMultiple.length == 1 || $fieldSelectorMultiple.length > 1 && $repeater_field.length == 0) {
		                            var fieldValueMultiple = [];
		                            if (fieldTypeMultiple == 'checkbox') {
		                                $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]:checked').each(function (index,element) {
					                        if ($(this).attr('data-piotnetforms-booking-price') != undefined) {
					                        	fieldValueMultiple.push( $(this).attr('data-piotnetforms-booking-price') );
					                        } else {
					                        	fieldValueMultiple.push($(this).val());
					                        }
		                                });
		                            } else {
		                                fieldValueMultiple = $fieldSelectorMultiple.val();
		                                if (fieldValueMultiple == null) {
		                                    var fieldValueMultiple = [];
		                                }
		                            }

		                            let fieldValueMultipleTotal = 0;

		                            for (var j = 0; j < fieldValueMultiple.length; j++) {
		                                fieldValue = parseFloatWithRemoveSepChar( fieldValueMultiple[j], separatorsSymbol );
		                                if (isNaN(fieldValue)) {
		                                    fieldValue = 0;
		                                }
		                                fieldValueMultipleTotal += fieldValue;
		                            }

		                            if ($fieldSelectorMultiple.closest('[data-piotnetforms-conditional-logic]').length > 0 && $fieldSelectorMultiple.closest('.piotnetforms-fields-wrapper').css('display') == 'none') {
			                        	fieldValueMultipleTotal = 0;
			                        }

		                            window[fieldName] = fieldValueMultipleTotal;
		                        }

		                        if ($repeater_field.length > 0) {

		                        	if($fieldSelectorMultiple.length > 1 || $fieldSelectorMultiple.length == 1 && $repeater_field.length) {

		                        		$fieldSelectorMultiple = $repeater_field.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]');
 
			                    		$fieldSelectorMultiple.each(function(){
				                    		fieldTypeMultiple = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]').attr('type');
				                            var fieldValueMultiple = [];

				                            if (fieldTypeMultiple == 'checkbox') {
				                                $repeater_field.find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + fieldName + '][]"]:checked').each(function (index,element) {
				                                    fieldValueMultiple.push($(this).val());
				                                });
				                            } else {
				                                fieldValueMultiple = $fieldSelectorMultiple.val();
				                                if (fieldValueMultiple == null) {
				                                    var fieldValueMultiple = [];
				                                }
				                            }

				                            let fieldValueMultipleTotal = 0;

				                            for (var j = 0; j < fieldValueMultiple.length; j++) {
				                                fieldValue = parseFloatWithRemoveSepChar( fieldValueMultiple[j], separatorsSymbol );
				                                if (isNaN(fieldValue)) {
				                                    fieldValue = 0;
				                                }
				                                fieldValueMultipleTotal += fieldValue;
				                            }

				                        	var $repeaterAll = $(document).find('[data-piotnetforms-repeater-form-id="' + $repeater.data('piotnetforms-repeater-form-id') + '"]'),
												repeaterIndex = $(this).closest('[data-piotnetforms-repeater-id]').index() - $repeaterAll.index();

											if ($(this).closest('[data-piotnetforms-conditional-logic]').length > 0 && $(this).closest('.piotnetforms-fields-wrapper').css('display') == 'none') {
					                        	fieldValueMultipleTotal = 0;
					                        }

					                        window['piotnetforms_'+fieldName+'_piotnetpiotnetforms'+repeaterIndex+'x'] = fieldValueMultipleTotal;
				                    	});
			                    	}
			                    }
		                    }
	                    }
	            	}
            	}
            }

            var $repeaterAll = $(document).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'),
				repeaterIndex = $(this).closest('[data-piotnetforms-repeater-id]').index() - $repeaterAll.index();

            if ($repeaterAll.length > 0) {

	            var keyValues = [], global = window; // window for browser environments

	            for (var prop in global) {
					if (prop.indexOf('piotnet_') == 0) {
						window[prop] = 0;
					}
				}

				for (var prop in global) {
					if (prop.indexOf('piotnetforms_') == 0) {
						var propArray = prop.split('_piotnetpiotnetforms');
						var propNew = propArray[0].replace('piotnetforms_','piotnet_');
						window[propNew] += window[prop];
					}
				}

				for (var prop in global) {
					if (prop.indexOf('piotnet_') == 0) {
						var propNew = prop.replace('piotnet_','');
						window[propNew] = window[prop];
					}
				}
			}

			var fieldNameArray = calculation.match(/\"(.*?)\"/g);
			if (fieldNameArray != null) {
				for (var jx = 0; jx<fieldNameArray.length; jx++) {
					var fieldName = fieldNameArray[jx].replace('"','').replace('"',''); 
					
					var $fieldCalc = $(document).find('[data-piotnetforms-repeater-id] [data-piotnetforms-calculated-fields][name="form_fields[' + fieldName + ']"]');
					if ($fieldCalc.length > 0) {
						let fieldCalcTotal = 0;
						$fieldCalc.each(function(){
							fieldCalcTotal += parseFloatWithRemoveSepChar( $(this).val(), separatorsSymbol );
						});
						var find = fieldName;
						var re = new RegExp(find, 'g');
						var calculation = calculation.replace(re, fieldCalcTotal);
					}
				}
			}

			if ($repeaterAll.length > 0) {
				for (var prop in global) {
					if (prop.indexOf('piotnetforms_') == 0 && prop.indexOf('_piotnetpiotnetforms'+repeaterIndex+'x') !== -1 ) {
						var propArray = prop.split('_piotnetpiotnetforms');
						var fieldNameArraySplit = propArray[0].split('piotnetforms_');
						var find = fieldNameArraySplit[1];
						var re = new RegExp(find, 'g');
						var calculation = calculation.replace(re, prop);
					}
				}
			}

			if (fieldNameArray != null) {
				for (var jx = 0; jx<fieldNameArray.length; jx++) {
					var fieldName = fieldNameArray[jx].replace('"','').replace('"',''); 
					
					if (window[fieldName] == undefined) {
						window[fieldName] = 0;
					}
				}
			}

			var calculation = calculation
				.replace(/\[field id=/g, '')
				.replace(/\"]/g, '')
				.replace(/\"/g, '')
				.replace(/--/g, '+');

			var totalFieldContent = eval(calculation);

        	if ($(this).attr('data-piotnetforms-calculated-fields-coupon-code') != undefined) {
        		var $couponCodeFields = $(document).find('[name="form_fields[' + $(this).attr('data-piotnetforms-calculated-fields-coupon-code').replace('[field id="','').replace('"]','') + ']"]' );
        		if ($couponCodeFields.length > 0) {
        			var $couponCodeField = $couponCodeFields.eq(0);
        			var couponObj = JSON.parse( $couponCodeField.attr('data-piotnetforms-coupon-code-list') );

        			for (var couponIndex = 0; couponIndex<couponObj.length; couponIndex++) {
						var couponItem = couponObj[couponIndex];
						if ($couponCodeField.val() == couponItem.piotnetforms_coupon_code) {
							if (couponItem.piotnetforms_coupon_code_discount_type == 'percentage') {
								totalFieldContent = totalFieldContent*parseFloat((100-couponItem.piotnetforms_coupon_code_coupon_amount)/100);
							}
							if (couponItem.piotnetforms_coupon_code_discount_type == 'flat_amount') {
								totalFieldContent = totalFieldContent - parseFloat(couponItem.piotnetforms_coupon_code_coupon_amount);
							}
						}
					}
        		}
        	}

			if (!isNaN(totalFieldContent)) {
        		$fieldWidget.find('.piotnetforms-calculated-fields-form__value').html(round(totalFieldContent, roundingDecimals, decimalsSymbol, separatorsSymbol, decimalsShow).replace('NaN',0));
	        	$fieldCurrent.val(roundValue(totalFieldContent, roundingDecimals, decimalsShow));
	            	//$fieldCurrent.change();
	            
	            var fieldNameCalc = $(this).attr('name').replace('[]','').replace('form_fields[','').replace(']','');  
				piotnetformsCalculatedFieldsForm(fieldNameCalc);
        	}

        });
    }

    piotnetformsCalculatedFieldsForm('');

	$(document).on('keyup change','[data-piotnetforms-id]', $.debounce( 200, function(){
		var fieldName = $(this).attr('name').replace('[]','').replace('form_fields[','').replace(']','');
		piotnetformsCalculatedFieldsForm(fieldName);
	})
	);

	$('[data-piotnetforms-id][type="hidden"]').each(function(){
		$(this).closest('.piotnetforms-widget-piotnetforms-field').addClass('piotnetforms-widget-piotnetforms-field-hidden');
	});

	// Repeater

	$(document).on('click','[data-piotnetforms-repeater-form-id-trigger]', function(e){
		e.preventDefault();

		if ($(this).closest('[data-piotnetforms-widget-preview]').length == 0) {
			var formID = $(this).data('piotnetforms-repeater-form-id-trigger'),
				repeaterID = $(this).data('piotnetforms-repeater-id-trigger'),
				repeaterTriggerAction = $(this).data('piotnetforms-repeater-trigger-action'),
				$repeater = $(document).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + repeaterID + '"]');

			if (repeaterTriggerAction == 'add') {

				$(document).find('[data-piotnetforms-repeater-id="' + repeaterID + '"] [data-piotnetforms-repeater-trigger-action="remove"]').show();

				if ($repeater.length > 0) {

					if ($(this).closest('[data-piotnetforms-repeater-form-id]').length > 0) {
						$repeater = $(this).closest('[data-piotnetforms-repeater-form-id]').find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + repeaterID + '"]');
					}

					var repeaterLimit = $repeater.eq(0).attr('data-piotnetforms-repeater-limit');

					if (repeaterLimit == 0 || repeaterLimit > 0 && $repeater.eq($repeater.length - 1).siblings('[data-piotnetforms-repeater-id="' + repeaterID + '"]').length < repeaterLimit - 1) {

						$repeater.eq($repeater.length - 1).after($repeater.eq(0).prop('outerHTML'));
						var $repeaterNew = $repeater.eq($repeater.length - 1).next();

						var $radioField = $repeaterNew.find('input[type="radio"]');
						$radioField.each(function(index,element){
							var id = $(this).attr('id');
							$(this).attr('id',id+$repeater.length+index);
							$(this).siblings('label').attr('for',id+$repeater.length+index);
						});

						var $checkboxField = $repeaterNew.find('input[type="checkbox"]');
						$checkboxField.each(function(index,element){
							var id = $(this).attr('id');
							$(this).attr('id',id+$repeater.length+index);
							$(this).siblings('label').attr('for',id+$repeater.length+index);
						});

						var $conditionals = $repeaterNew.find('[data-piotnetforms-conditional-logic]');
						if ($conditionals.length > 0) {
							piotnetformsConditionalLogicFormCheck($conditionals);
						}

						$(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic][data-piotnetforms-id="'+ formID +'"]').change();

						var $imageUploadField = $repeaterNew.find('[data-piotnetforms-image-upload]');
						if ($imageUploadField.length > 0) {
							$imageUploadField.each(function(){
								const $imageUploadedFieldWidget = $(this).closest('.piotnetforms-fields-wrapper');
								$imageUploadedFieldWidget.find('[data-piotnetforms-image-upload-placeholder]').remove();
								$imageUploadedFieldWidget.find('[data-piotnetforms-image-upload-label]').show(0);
								$imageUploadedFieldWidget.find('[data-piotnetforms-id]').val('');
							});
						}

						$repeaterNew.find('[data-mask]').each(function(){
							$(this).mask($(this).attr('data-mask')); 
						});

						var $imageSelect = $repeaterNew.find('[data-piotnetforms-image-select]');
						if ($imageSelect.length > 0) {
							$imageSelect.each(function(){

								$(this).closest('.piotnetforms-field').find('.image_picker_selector').remove();
								
								var gallery = $(this).data('piotnetforms-image-select'),
					                $options = $(this).find('option');

					            $(this).closest('.piotnetforms-field').addClass('piotnetforms-image-select-field');
					            var $imageSelectField = $(this);
					            
					            $options.each(function(index,element){
					            	if ($options.eq(0).attr('value').trim() == '' && index != 0) {
					            		var indexGallery = index - 1;
						                var imageURL = gallery[indexGallery]['url'],
						                    optionsContent = $(this).html();

						                $(this).attr('data-img-src',imageURL);
						                $imageSelectField.imagepicker({show_label: true});
						            }

						            if ($options.eq(0).attr('value').trim() != '') {
						                var imageURL = gallery[index]['url'],
						                    optionsContent = $(this).html();

						                $(this).attr('data-img-src',imageURL);
						                $imageSelectField.imagepicker({show_label: true});
						            }
					            });

							});
						}

						var $rangeSlider = $repeaterNew.find('[data-piotnetforms-range-slider]');

						if ($rangeSlider.length > 0) {
							$rangeSlider.each(function(){
								$(this).closest('.piotnetforms-fields-wrapper').find('.irs').remove();

								var optionsString = $(this).data('piotnetforms-range-slider');
						        var options = {};
								var items = optionsString.split(',');
								for (var j = 0; j < items.length; j++) {
								    var current = items[j].trim().split(':');
								    if (current[0] != undefined && current[1] != undefined) {
								    	var current1 = current[1].trim().replace('"','').replace('"','');
								    	if (current1 == "false" || current1 == "true") {
								    		if (current1 == "false") {
								    			options[current[0]] = false;
								    		} else {
								    			options[current[0]] = true;
								    		}
								    	} else {
								    		options[current[0]] = current1;
								    	}
								    }
								}

								options.onStart = function (data) { 
						            //piotnetformsConditionalLogicFormCheck();
						            //piotnetformsCalculatedFieldsForm();
						        };

								$(this).ionRangeSlider(options);
								$(this).addClass('irs-hidden-input');
							});
						}

						var $niceNumber = $repeaterNew.find('[data-piotnetforms-spiner] .nice-number');
						if ($niceNumber.length > 0) {
							$niceNumber.each(function(){
								var $field = $(this).closest('[data-piotnetforms-spiner]'),
									$input = $(this).find('.piotnetforms-field'),
									inputHTML = $input.prop('outerHTML');
								$(this).after(inputHTML);
								$(this).remove();
								$field.find('.piotnetforms-field').niceNumber({
									autoSize: false,
								});
							});
						}

						var $repeaterSub = $repeaterNew.find('[data-piotnetforms-repeater-id]');
						if ($repeaterSub.length > 0) {
							piotnetformsConditionalLogicFormCheck($repeaterSub);
							$repeaterSub.each(function(){
								var repeaterID = $(this).data('piotnetforms-repeater-id');
								$(this).siblings('[data-piotnetforms-repeater-id="'+ repeaterID +'"]').remove();
							});
						}

						var $fieldsInside = $repeaterNew.find('[data-piotnetforms-id]');

		    			$fieldsInside.each(function(){
		    				var checkSelect = $(this).find('option:first');

							var checkRadioCheckbox = false;

							if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
								checkRadioCheckbox = true;
							}

							var defaultValue = $(this).data('piotnetforms-default-value');

							if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
								if (checkRadioCheckbox) {
									defaultValue = $(this).attr('data-value');
									$(this).val(defaultValue).change();
								} else {
									$(this).val(defaultValue).change();
								}
		                	} else {
		                		if (checkSelect.length != 0) {
		            				$(this).val((checkSelect.val())).change();
		            			} else {
									if (checkRadioCheckbox) {
		            					$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
		            				} else {
		            					$(this).val('');
		            				}
		            			}
		                	}
		    			});

						var $selectAutocomplete = $repeaterNew.find('[data-piotnetforms-select-autocomplete]');
						if ($selectAutocomplete.length > 0) {
							$selectAutocomplete.each(function(){
								var options = JSON.parse($(this).attr('data-options'));
								var optionsHTML = '';

								for (var optionsIndex = 0; optionsIndex < options.length; optionsIndex++) {
									var option = options[optionsIndex];
									if (option.indexOf('|') >= 0) {
										option = option.split('|');
										optionsHTML += '<option value="' + option[1] + '">' + option[0] + '</option>';
									} else {
										optionsHTML += '<option value="' + option + '">' + option + '</option>';
									}
								}

								$(this).html(optionsHTML);
								$(this).closest('.piotnetforms-field').find('.selectize-control').remove();

								$(this).selectize({
									dropdownParent: 'body',
								});
							}); 
						}

						var $dateField = $repeaterNew.find('.piotnetforms-date-field');
						if ($dateField.length > 0) {
							var addDatePicker = function addDatePicker($element) {
								if ($element.hasClass('piotnetforms-use-native') || $element.hasClass('flatpickr-custom-options')) { 
									return;
								}

								var minDate = $($element).attr('min') ? flatpickr.parseDate($($element).attr('min'), "Y-m-d") : null,
									maxDate = $($element).attr('max') ? flatpickr.parseDate($($element).attr('max'), "Y-m-d") : null;

								var options = {
									minDate: minDate,
									maxDate: maxDate,
									dateFormat: $element.attr('data-date-format') || null,
									defaultDate: $element.attr('data-piotnetforms-value') || null,
									allowInput: true,
									animate: false,
									onReady: function(date) { 
										var day = parseInt( date[0] / (1000 * 60 * 60 * 24), 10);
										$element.attr('data-piotnetforms-date-calculate', day);
									},
									onClose: function(date) { 
										var day = parseInt( date[0] / (1000 * 60 * 60 * 24), 10);
										$element.attr('data-piotnetforms-date-calculate', day);
									}
								};

								if ($element.data('piotnetforms-date-range') != undefined) {
									var options = {
										minDate: minDate,
										maxDate: maxDate,
										dateFormat: $element.attr('data-date-format') || null,
										defaultDate: $element.attr('data-piotnetforms-value') || null,
										allowInput: true,
										animate: false,
										onClose: function(date) { 
											var startDay = flatpickr.formatDate(date[0], "m/d/Y");
											var endDay = flatpickr.formatDate(date[1], "m/d/Y");

											var newStartDate = new Date(startDay).getTime();
											var newEndDate = new Date(endDay).getTime();

											var newStartDate = eval( newStartDate / 1000 + 3600 ); // for GMT+1 I had to add 3600 (seconds) [1 hour]
											var newEndDate = eval( newEndDate / 1000 + 3600 ); // for GMT+1 I had to add 3600 (seconds) [1 hour]

											var countDays = eval( newEndDate - newStartDate );
											var countDays = eval( countDays / 86400 + 1 );

											$element.attr('data-piotnetforms-date-range-days', countDays);
										}
									};

									options['mode'] = 'range';
								}

								if ($element.data('piotnetforms-date-language') != 'english') { 
									options['locale'] = $element.attr('data-piotnetforms-date-language');
								} 

								$element.flatpickr(options); 
							};

							$dateField.each(function(){
								addDatePicker($(this));
							});
						}

						var $timeField = $repeaterNew.find('.piotnetforms-time-field');
						if ($timeField.length > 0) {
							var addTimePicker = function addTimePicker($element) {
								if ($element.hasClass('piotnetforms-use-native')) { 
									return;
								}
								$element.flatpickr({
									noCalendar: true,
									enableTime: true,
									allowInput: true,
									defaultDate: $element.attr('data-piotnetforms-value') || null,
								});
							};

							$timeField.each(function(){
								addTimePicker($(this));
							});
						}
					}

				}
			} else {
				if ($repeater.length == 2) {
					$(document).find('[data-piotnetforms-repeater-id="' + repeaterID + '"] [data-piotnetforms-repeater-trigger-action="' + repeaterTriggerAction + '"]').hide();
				}

				if ($repeater.length > 1) {
					var $repeaterParent = $(this).closest('[data-piotnetforms-repeater-form-id="' + formID + '"]');
					if ($repeaterParent.siblings('[data-piotnetforms-repeater-form-id="' + formID + '"]').length > 0) {
						$(this).closest('[data-piotnetforms-repeater-form-id="' + formID + '"]').remove();
					}

					var $conditionals = $(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic]');
					if ($conditionals.length > 0) {
						piotnetformsConditionalLogicFormCheck($conditionals);
					}

					$(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic][data-piotnetforms-id="'+ formID +'"]').change();
					$(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-id="'+ formID +'"]').change();

					piotnetformsCalculatedFieldsForm('');  
				}
			}
		}
	});

	$('[data-piotnetforms-repeater-id]').each(function(){
		if ($(this).closest('[data-piotnetforms-widget-preview]').length == 0) {
			var formID = $(this).data('piotnetforms-repeater-form-id'),
				repeaterID = $(this).data('piotnetforms-repeater-id'),
				fieldEndRepeaterID = 'piotnetforms-end-repeater' + repeaterID;

			$(this).after('<div class="piotnetforms-fields-wrapper piotnetforms-fields-wrapper-7d558e4 piotnetforms-widget piotnetforms-widget-piotnetforms-field piotnetforms-widget-piotnetforms-field-hidden" data-element_type="widget" data-widget_type="piotnetforms-field.default" style="opacity: 0.45;"><div class="piotnetforms-widget-container"><div class="piotnetforms-fields-wrapper piotnetforms-labels-above"><div class="piotnetforms-field-type-hidden piotnetforms-field-group piotnetforms-column piotnetforms-field-group-' + fieldEndRepeaterID + ' piotnetforms-col-100"><div data-piotnetforms-required=""></div><input size="1" class="piotnetforms-field piotnetforms-size- " type="hidden" name="form_fields[' + fieldEndRepeaterID + ']" id="form-field-' + fieldEndRepeaterID + '" autocomplete="on" data-piotnetforms-default-value="" value="" data-piotnetforms-value="" data-piotnetforms-id="' + formID + '"></div></div></div></div>');
		}
	});

	function reInitFieldRepeater($repeaterNew) {
		var $imageUploadField = $repeaterNew.find('[data-piotnetforms-image-upload]');
		if ($imageUploadField.length > 0) {
			$imageUploadField.each(function(){
				const $imageUploadedFieldWidget = $(this).closest('.piotnetforms-fields-wrapper');
				$imageUploadedFieldWidget.find('[data-piotnetforms-image-upload-placeholder]').remove();
				$imageUploadedFieldWidget.find('[data-piotnetforms-image-upload-label]').show(0);
				$imageUploadedFieldWidget.find('[data-piotnetforms-id]').val('');
			});
		}

		var $imageSelect = $repeaterNew.find('[data-piotnetforms-image-select]');
		if ($imageSelect.length > 0) {
			$imageSelect.each(function(){

				$(this).closest('.piotnetforms-field').find('.image_picker_selector').remove();
				
				var gallery = $(this).data('piotnetforms-image-select'),
	                $options = $(this).find('option');

	            $(this).closest('.piotnetforms-field').addClass('piotnetforms-image-select-field');
	            var $imageSelectField = $(this);
	            
	            $options.each(function(index,element){
	            	if ($options.eq(0).attr('value').trim() == '' && index != 0) {
	            		var indexGallery = index - 1;
		                var imageURL = gallery[indexGallery]['url'],
		                    optionsContent = $(this).html();

		                $(this).attr('data-img-src',imageURL);
		                $imageSelectField.imagepicker({show_label: true});
		            }

		            if ($options.eq(0).attr('value').trim() != '') {
		                var imageURL = gallery[index]['url'],
		                    optionsContent = $(this).html();

		                $(this).attr('data-img-src',imageURL);
		                $imageSelectField.imagepicker({show_label: true});
		            }
	            });

			});
		}

		var $niceNumber = $repeaterNew.find('[data-piotnetforms-spiner] .nice-number');
		if ($niceNumber.length > 0) {
			$niceNumber.each(function(){
				var $field = $(this).closest('[data-piotnetforms-spiner]'),
					$input = $(this).find('.piotnetforms-field'),
					inputHTML = $input.prop('outerHTML');
				$(this).after(inputHTML);
				$(this).remove();
				$field.find('.piotnetforms-field').niceNumber({
					autoSize: false,
				});
			});
		}

		var $repeaterSub = $repeaterNew.find('[data-piotnetforms-repeater-id]');
		if ($repeaterSub.length > 0) {
			$repeaterSub.each(function(){
				var repeaterID = $(this).data('piotnetforms-repeater-id');
				$(this).siblings('[data-piotnetforms-repeater-id="'+ repeaterID +'"]').remove();
			});
		}

		$repeaterNew.find('[data-mask]').each(function(){
			$(this).mask($(this).attr('data-mask')); 
		}); 

		var $fieldsInside = $repeaterNew.find('[data-piotnetforms-id]');

		$fieldsInside.each(function(){
			var checkSelect = $(this).find('option:first');

			var checkRadioCheckbox = false;

			if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
				checkRadioCheckbox = true;
			}

			var defaultValue = $(this).data('piotnetforms-default-value');

			if (defaultValue != undefined && defaultValue != '' || Number.isInteger(defaultValue) ) {
        		$(this).val(defaultValue).change();
        	} else {
        		if (checkSelect.length != 0) {
    				$(this).val((checkSelect.val())).change();
    			} else {
					if (checkRadioCheckbox) {
    					$(this).prop('checked', false).removeClass('piotnetforms-checked').removeClass('piotnetforms-checked-setvalue');
    				} else {
    					$(this).val('');
    				}
    			}
        	}
		});

		var $selectAutocomplete = $repeaterNew.find('[data-piotnetforms-select-autocomplete]');
		if ($selectAutocomplete.length > 0) {
			$selectAutocomplete.each(function(){
				var options = JSON.parse($(this).attr('data-options'));
				var optionsHTML = '';

				for (var optionsIndex = 0; optionsIndex < options.length; optionsIndex++) {
					var option = options[optionsIndex];
					if (option.indexOf('|') >= 0) {
						option = option.split('|');
						optionsHTML += '<option value="' + option[0] + '">' + option[1] + '</option>';
					} else {
						optionsHTML += '<option value="' + option + '">' + option + '</option>';
					}
				}

				$(this).html(optionsHTML);
				$(this).closest('.piotnetforms-field').find('.selectize-control').remove();

				$(this).selectize({
					dropdownParent: 'body',
				});
			}); 
		}

		var $dateField = $repeaterNew.find('.piotnetforms-date-field');
		if ($dateField.length > 0) {
			var addDatePicker = function addDatePicker($element) {
				if ($element.hasClass('piotnetforms-use-native')) { 
					return;
				}
				var options = {
					minDate: $element.attr('min') || null,
					maxDate: $element.attr('max') || null,
					dateFormat: $element.attr('data-date-format') || null,
					defaultDate: $element.attr('data-piotnetforms-value') || null,
					allowInput: true
				};
				$element.flatpickr(options);
			};

			$dateField.each(function(){
				addDatePicker($(this));
			});
		}

		var $timeField = $repeaterNew.find('.piotnetforms-time-field');
		if ($timeField.length > 0) {
			var addTimePicker = function addTimePicker($element) {
				if ($element.hasClass('piotnetforms-use-native')) { 
					return;
				}
				$element.flatpickr({
					noCalendar: true,
					enableTime: true,
					allowInput: true,
					defaultDate: $element.attr('data-piotnetforms-value') || null,
				});
			};

			$timeField.each(function(){
				addTimePicker($(this));
			});
		}
	}

    $(window).on('load', function(){
    	$('[data-piotnetforms-repeater-form-id] [data-piotnetforms-repeater-form-id-trigger]').hide();
		$('[data-piotnetforms-repeater-value]').each(function(){
	    	var repeaterJson = JSON.parse($(this).html().trim()),
	    		formID = $(this).data('piotnetforms-repeater-value-form-id'),
				repeaterID = $(this).data('piotnetforms-repeater-value-id'),
				$repeater = $(document).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + repeaterID + '"]');

			if ($repeater.length > 0) {
				if ($(this).closest('[data-piotnetforms-repeater-form-id]').length > 0) {
					$repeater = $(this).closest('[data-piotnetforms-repeater-form-id]').find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + repeaterID + '"]');
				} 
	 
				for (var j = 0; j < (repeaterJson.length - 1); j++) {
					$repeater.eq(0).after($repeater.eq(0).prop('outerHTML')); 
				}

				$repeater = $(document).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + repeaterID + '"]');

				$repeater.each(function(index,element){
					if (index != 0) {
						reInitFieldRepeater($(this));
					}
				});				

				// Set value for Repeater

				$repeater = $(document).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + repeaterID + '"]');

				for(var k in repeaterJson) {
				    var repeaterItem = repeaterJson[k];
				    for(var key in repeaterItem) {
					    if (typeof repeaterItem[key] !== 'object') {
					    	var $fieldCurrent = $repeater.eq(k).find('[name^="form_fields[' + key + ']"]');
					    	setFieldValue($fieldCurrent,repeaterItem[key]); 
					    } else {
					    	var $repeaterSub = $repeater.eq(k).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + key + '"]');
					    	for (var j = 0; j < (repeaterItem[key].length - 1); j++) {
								$repeaterSub.eq(0).after($repeaterSub.eq(0).prop('outerHTML')); 
							}
					    	$repeaterSub = $repeater.eq(k).find('[data-piotnetforms-repeater-form-id="' + formID + '"]'+'[data-piotnetforms-repeater-id="' + key + '"]');

							$repeaterSub.each(function(index,element){
								if (index != 0) {
									reInitFieldRepeater($(this));
								}
							});	

							var repeaterItemSub = repeaterItem[key];
				    		for(var keySub in repeaterItemSub) {
				    			for(var keySubSecond in repeaterItemSub[keySub]) {
								    if (typeof repeaterItemSub[keySub][keySubSecond] !== 'object') {
								    	var $fieldCurrent = $repeaterSub.eq(keySub).find('[name^="form_fields[' + keySubSecond + ']"]');
								    	setFieldValue($fieldCurrent,repeaterItemSub[keySub][keySubSecond]); 
								    }
							    }
						    }
					    }
					    
					}
				}

				var $conditionals = $repeater.find('[data-piotnetforms-conditional-logic]');
				if ($conditionals.length > 0) {
					piotnetformsConditionalLogicFormCheck($conditionals);
				}

				$(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic][data-piotnetforms-id="'+ formID +'"]').change();
				
			}
		});
	});

	function bookingLoad($this) {
		var date = $this.val(),
			formID = $this.attr('data-piotnetforms-id'),
			$bookingItem = $(document).find('[data-piotnetforms-booking-item][data-piotnetforms-id="' + formID + '"]');

		if ($bookingItem.length > 0) {
			$bookingItem = $bookingItem.eq(0);
			var $bookingForm = $bookingItem.closest('[data-piotnetforms-booking]');
			$bookingForm.addClass('piotnetforms-booking--loading');

			var bookingOptions = JSON.parse( $bookingItem.attr('data-piotnetforms-booking-item-options') );

			if ($bookingItem.closest('[data-piotnetforms-step-item-id]').length > 0) {
				var post_id = $bookingItem.closest('[data-piotnetforms-step-item-id]').attr('data-piotnetforms-step-item-id');
			} else {
				if ($bookingItem.closest('[data-piotnetforms-shortcode-id]').length > 0) {
					var post_id = $bookingItem.closest('[data-piotnetforms-shortcode-id]').attr('data-piotnetforms-shortcode-id');
				} else {
					var post_id = bookingOptions.piotnetforms_booking_post_id;
				}
			}

			multi_step_form_id = '';
			if ( $this.closest('[data-piotnetforms-multi-step-form-id]').length > 0 ) {
				var multi_step_form_id = $this.closest('[data-piotnetforms-multi-step-form-id]').attr('data-piotnetforms-multi-step-form-id');
			}

			var data = {
				'action': 'piotnetforms_booking',
				'date': date,
				'post_id': post_id,
				'element_id': bookingOptions.piotnetforms_booking_element_id,
				'multi_step_form_id': multi_step_form_id,
			};

	        $.post($('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'), data, function(response) {
	        	$bookingForm.html(response);
	        	piotnetformsCalculatedFieldsForm('');

	        	$bookingForm.removeClass('piotnetforms-booking--loading');
			});
		}
	}

	function bookingItemLoad() {
		$(document).find('[data-piotnetforms-booking]').each(function(){
			var $bookingItems = $(this).find('[data-piotnetforms-booking-item]');
			var $bookingItem = $bookingItems.eq(0); 
			var options = JSON.parse( $bookingItem.attr('data-piotnetforms-booking-item-options') ); 

			if(typeof options['piotnetforms_booking_date_field'] !== 'undefined') {
			    var dateFieldID = '#form-field-' + options['piotnetforms_booking_date_field'].replace('[field id=\"', '').replace('\"]', '');
			    $(document).find(dateFieldID).addClass('piotnetforms-booking-date');
			    bookingLoad($(document).find(dateFieldID));
			} else {
				var date = options['piotnetforms_booking_date'],
					formID = $bookingItem.attr('data-piotnetforms-id'); 
					
				var $bookingForm = $bookingItem.closest('[data-piotnetforms-booking]'); 

				var bookingOptions = options;

				if ($bookingItem.closest('[data-piotnetforms-step-item-id]').length > 0) {
					var post_id = $bookingItem.closest('[data-piotnetforms-step-item-id]').attr('data-piotnetforms-step-item-id');
				} else {
					if ($bookingItem.closest('[data-piotnetforms-shortcode-id-]').length > 0) {
						var post_id = $bookingItem.closest('[data-piotnetforms-shortcode-id]').attr('data-piotnetforms-shortcode-id');
					} else {
						var post_id = bookingOptions.piotnetforms_booking_post_id;
					}
				}

				multi_step_form_id = '';
				if ( $bookingItem.closest('[data-piotnetforms-multi-step-form-id]').length > 0 ) {
					var multi_step_form_id = $bookingItem.closest('[data-piotnetforms-multi-step-form-id]').attr('data-piotnetforms-multi-step-form-id');
				}

				var data = {
					'action': 'piotnetforms_booking',
					'date': date,
					'post_id': post_id,
					'element_id': bookingOptions.piotnetforms_booking_element_id,
					'multi_step_form_id': multi_step_form_id,
				};

		        $.post($('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'), data, function(response) {
		        	$bookingForm.html(response);
		        	piotnetformsCalculatedFieldsForm('');

		        	$bookingForm.removeClass('piotnetforms-booking--loading');
				});
			}
		});
	}
	
	bookingItemLoad(); 

	$(document).on('change','.piotnetforms-booking-date',function(){
		bookingLoad($(this));
	});

	$(document).on('change','[data-piotnetforms-booking-item]',function(){
		if ($(this).is(":checked")) {
			$(this).closest('.piotnetforms-booking__item').addClass('active');

			if ($(this).attr('data-piotnetforms-booking-item-radio') != undefined) {
				var $bookingItemOther = $(this).closest('.piotnetforms-booking__item').siblings();
				$bookingItemOther.find('input').prop("checked", false);
				$bookingItemOther.removeClass('active');

				var options = JSON.parse( $(this).attr('data-piotnetforms-booking-item-options') ); 

				if(typeof options['piotnetforms_booking_slot_quantity_field'] !== 'undefined') {
					var quantityFieldID = '#form-field-' + options['piotnetforms_booking_slot_quantity_field'].replace('[field id=\"', '').replace('\"]', '');
					var quantityMax = $(this).attr('data-piotnetforms-booking-availble');
			    	$(quantityFieldID).attr('max', quantityMax);
		    	}
			}
		} else {
			$(this).closest('.piotnetforms-booking__item').removeClass('active');
		}
	});

	$(document).on('click','[data-piotnetforms-trigger-success],[data-piotnetforms-trigger-failed]',function(){
		bookingItemLoad();
	});
 
	$(document).on('click','[data-piotnetforms-trigger-success]',function(){
		if ($(this).closest('.piotnetforms-fields-wrapper').find('[data-piotnetforms-submit-update-user-profile]').length == 0) {
			var $conditionals = $(document).find('body:not(.piotnetforms-editor-active) [data-piotnetforms-conditional-logic]');
			if ($conditionals.length > 0) {
				piotnetformsConditionalLogicFormCheck($conditionals);
			}
		}
	});

});
