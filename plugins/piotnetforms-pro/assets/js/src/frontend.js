import './lib/jquery-throttle.js';
import IBAN from "iban";

jQuery(document).ready(function($) {

	var pluginURL = $('[data-piotnetforms-plugin-url]').attr('data-piotnetforms-plugin-url');

	function initWidgetSelectAutocomplete($scope, $) {
		var $elements = $scope.find('[data-piotnetforms-select-autocomplete]');

		if (!$elements.length) {
			return;
		}

		$.each($elements, function (i, $element) {
			$($element).selectize({
				dropdownParent: null,
			});
		});
    };

    function initWidgetTinymce($scope, $) {

	    var $elements = $scope.find('[data-piotnetforms-tinymce]');

	    var rtl=$elements.attr('data-piotnetforms-tinymce-rtl');

		if (!$elements.length) {
			return;
		}
		
		$.each($elements, function (i, $element) {
			$($element).tinymce({
				script_url : pluginURL + '/piotnetforms-pro/inc/forms/tinymce/tinymce.min.js',
				height: 500,
				directionality :rtl,
				menubar: false,
				plugins: [
					'advlist autolink lists link image charmap print preview anchor',
					'searchreplace visualblocks code fullscreen',
					'insertdatetime media table contextmenu paste code help youtube'
				],
				toolbar: 'bold italic link | alignleft aligncenter alignright alignjustify | bullist numlist | image youtube',
				image_title: true, 
				images_upload_url: pluginURL + '/piotnetforms-pro/inc/forms/tinymce/tinymce-upload.php',
				file_picker_types: 'image',
				convert_urls: false,
				setup: function (editor) {
					editor.on('change', function () {
						tinymce.triggerSave();
					});
				}
			});
		});

	};

	$(document).on('piotnet-widget-init-Piotnetforms_Field', '[data-piotnet-editor-widgets-item-root]', function(){
		initWidgetSelectAutocomplete($(this), $);
		initWidgetTinymce($(this), $);
	});

	$(window).on('load', function(){
		initWidgetSelectAutocomplete($('[data-piotnet-widget-preview], #piotnetforms'), $);
		initWidgetTinymce($('[data-piotnet-widget-preview], #piotnetforms'), $);
	});
	// Fix select autocomplete popup elementor	
	$(document).on( 'elementor/popup/show', function(event, id, instance){
		$(document).find('.dialog-widget [data-piotnetforms-select-autocomplete]').each(function(){
			$(this).selectize({
				create: true,
                sortField: "text",
			});
		});
	} );

	function resizeSignature() {
    	$(document).find('[data-piotnetforms-signature] canvas').each(function(){
    		var width = parseInt( $(this).css('max-width').replace('px','') ),
    			height = parseInt( $(this).css('height').replace('px','') ),
    			widthOuter = parseInt( $(this).closest('.piotnetforms-fields-wrapper').width() ); 

			if(widthOuter > 0) {
				if (width > widthOuter || Number.isNaN(width)) {
					$(this).attr('width',widthOuter);
				} else {
					$(this).attr('width',width);
				}
				$(this).attr('height',$(this).css('height'));
			}
			
    	});
    }

    resizeSignature();

 	// var windowWidth = $(window).width();
	// $(window).on('resize', function() {
	// 	if ($(this).width() !== windowWidth) {
	// 		resizeSignature();
	// 	}
	// });

    window.piotnetformsValidateFields = function ($fields) {
    	var error = 0;

    	$fields.each(function(){
			if ( $(this).data('piotnetforms-stripe') == undefined && $(this).data('piotnetforms-html') == undefined ) {
				var $checkboxRequired = $(this).closest('.piotnetforms-field-type-checkbox.piotnetforms-field-required');
				var checked = 0;
				if ($checkboxRequired.length > 0) {
					checked = $checkboxRequired.find("input[type=checkbox]:checked").length;
				} 

				if ($(this).attr('oninvalid') != undefined) {
					requiredText = $(this).attr('oninvalid').replace("this.setCustomValidity('","").replace("')","");
				}

				var isValid = $(this)[0].checkValidity();
				var next_ele = $($(this)[0]).next()[0];
				if ($(next_ele).hasClass('flatpickr-mobile')) {
					isValid = next_ele.checkValidity();
				}

				if ( !isValid && $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 && $(this).closest('[data-piotnetforms-conditional-logic]').css('display') != 'none' && $(this).data('piotnetforms-honeypot') == undefined &&  $(this).closest('[data-piotnetforms-signature]').length == 0 || checked == 0 && $checkboxRequired.length > 0 && $(this).closest('.piotnetforms-fields-wrapper').css('display') != 'none') {
					if ($(this).css('display') == 'none' || $(this).closest('div').css('display') == 'none' || $(this).data('piotnetforms-image-select') != undefined || $checkboxRequired.length > 0) {
						$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
						
					} else {
						if ($(this).data('piotnetforms-image-select') == undefined) {
							$(this)[0].reportValidity();
						} 
					}

					error++;
				} else {
					if ($(this).val()=='' && $(this).attr('aria-required') == "true" && $(this).attr('data-piotnetforms-select-autocomplete') !== undefined) {
						$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
						error++;
					} else {

						$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html('');

						if ($(this).closest('[data-piotnetforms-signature]').length > 0) {
							var $piotnetformsSingature = $(this).closest('[data-piotnetforms-signature]'),
								$exportButton = $piotnetformsSingature.find('[data-piotnetforms-signature-export]');

							$exportButton.trigger('click');

							if ($(this).val() == '' && $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 && $(this).attr('required') != undefined) {
								$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
								error++;
							} 
						}
					}
				}
			}
		});

		return error;
    }

    function clickSubmit(submitButton) {
    	var $this = submitButton;
    	if ( $this.attr('data-piotnetforms-stripe-submit') == undefined ) {
	    	var formID = $this.data('piotnetforms-submit-form-id'),
	    		$fields = $(document).find('[data-piotnetforms-id="'+ formID +'"]'),
	    		requiredText = $this.data('piotnetforms-required-text'),
	    		fieldsOj = [],
	    		error = 0,
	    		formData = new FormData();
            
            var isAlertRequired = false;
    		var $submit = $this;
			var $parent = $submit.closest('.piotnetforms-submit');

			$fields.each(function(){
				if ( $(this).data('piotnetforms-stripe') == undefined && $(this).data('piotnetforms-html') == undefined ) {
					var $checkboxRequired = $(this).closest('.piotnetforms-field-type-checkbox.piotnetforms-field-required');
					var checked = 0;
					if ($checkboxRequired.length > 0) {
						checked = $checkboxRequired.find("input[type=checkbox]:checked").length;
					} 

					if ($(this).attr('oninvalid') != undefined) {
						requiredText = $(this).attr('oninvalid').replace("this.setCustomValidity('","").replace("')","");
					}
					//Check password match
					if($(this).attr('data-piotnetforms-is-repassword') != undefined && $(this).attr('data-piotnetforms-is-repassword') != ''){
						var passwordFieldName = $(this).attr('data-piotnetforms-is-repassword');
						var passwordCompare = $(this).closest('#piotnetforms').find('[name="form_fields['+passwordFieldName+']"]').val();
						var passwordMsg = $(this).attr('data-piotnetforms-repassword-msg');
						if(String(passwordCompare) !== String($(this).val())){
							this.setCustomValidity(passwordMsg);
							$(this).keyup(function () {
								this.setCustomValidity('');
							  });
						}else{
							this.setCustomValidity('');
						}
					}
					//Iban
					if($(this).attr('data-piotnetforms-iban-field') != undefined){
						if(!IBAN.isValid($(this).val())){
							let iban_msg = $(this).attr('data-piotnetforms-iban-msg');
							this.setCustomValidity(iban_msg);
							error++;
							$(this).on('keyup', function(){
								this.setCustomValidity("");
							})
						}
					}
					var isValid = $(this)[0].checkValidity();
					var next_ele = $($(this)[0]).next()[0];
					if ($(next_ele).hasClass('flatpickr-mobile')) {
						isValid = next_ele.checkValidity();
					}

					if ( !isValid && $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 && $(this).closest('[data-piotnetforms-conditional-logic]').css('display') != 'none' && $(this).data('piotnetforms-honeypot') == undefined &&  $(this).closest('[data-piotnetforms-signature]').length == 0 || checked == 0 && $checkboxRequired.length > 0 && $(this).closest('.piotnetforms-fields-wrapper').css('display') != 'none') {
						if ($(this).css('display') == 'none' || $(this).closest('div').css('display') == 'none' || $(this).data('piotnetforms-image-select') != undefined || $checkboxRequired.length > 0) {
							$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
							
						} else {
							if (!isAlertRequired && $(this).data('piotnetforms-image-select') == undefined) {
								$(this)[0].reportValidity();
								isAlertRequired = true;
							} 
						}

						error++;
					} else {
						if ($(this).val()=='' && $(this).attr('aria-required') == "true" && $(this).attr('data-piotnetforms-select-autocomplete') !== undefined) {
							$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
							error++;
						} else {

							if ( $(this).data('piotnetforms-image-select') != undefined ) {
								if ( $(this).closest('.piotnetforms-image-select-field').find('.image_picker_selector').find('.selected').length < $(this).data('piotnetforms-image-select-min-select') ) {
									$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-image_select_min_select_check]').html($(this).data('piotnetforms-image-select-min-select-message'));
									error++;
								} else {
									$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-image_select_min_select_check]').remove();
								}
							}

							$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html('');

							if ($(this).closest('[data-piotnetforms-signature]').length > 0) {
								var $piotnetformsSingature = $(this).closest('[data-piotnetforms-signature]'),
									$exportButton = $piotnetformsSingature.find('[data-piotnetforms-signature-export]');

								$exportButton.trigger('click');

								if ($(this).val() == '' && $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 && $(this).attr('required') != undefined) {
									$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
									error++;
								} 
							}

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

							if (fieldType == 'file') { 
								if($(this).hasClass('error')) {
									error++;
								} else {

									fieldName = $(this).attr('id').replace('form-field-','');

									$.each($(this)[0].files, function(i, file){
										formData.append( fieldName + '[]', file);
									});

									var fieldItem = {};
									fieldItem['label'] = $(this).closest('.piotnetforms-field-group').find('.piotnetforms-field-label').html();
									fieldItem['name'] = fieldName;
									fieldItem['value'] = '';
									fieldItem['type'] = $(this).attr('type');
									fieldItem['upload'] = 1;
									fieldItem['repeater_id'] = repeaterID;
									fieldItem['repeater_id_one'] = repeaterIDOne;
									fieldItem['repeater_label'] = repeaterLabel;
									fieldItem['repeater_index'] = repeaterIndex;
									fieldItem['repeater_length'] = repeaterLength;

									if ($(this).data('piotnetforms-remove-this-field-from-repeater') != undefined) {
			                    		fieldItem['repeater_remove_this_field'] = '1';
		                    		}

									if($(this).data('attach-files') != undefined) {
										fieldItem['attach-files'] = 1;
									}
									
									fieldsOj.push(fieldItem);

								}

								// [ Fix alert
							} else {
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

				                    var fieldBooking = [];

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

			                    		if ($(this).attr('data-piotnetforms-booking-item-options') != undefined) {
				                			var fieldValueSelected = fieldValueMultiple[j];
				                			
				                			var $optionSelected = $(this).closest('.piotnetforms-fields-wrapper').find('[value="' + fieldValueSelected + '"]');
				                			if ($optionSelected.length > 0) {
				                				console.log($optionSelected.attr('data-piotnetforms-booking-item-options'));
			                					fieldBooking.push($optionSelected.attr('data-piotnetforms-booking-item-options'));  
				                			}
		                				}
				                    }

				                    fieldValue = fieldValue.replace(/,(\s+)?$/, '');
								} else {
									if (fieldType == 'radio' || fieldType == 'checkbox') {
										if ($(this).data('piotnetforms-send-data-by-label') != undefined) {
											var fieldValueByLabel = $(this).closest('.piotnetforms-fields-wrapper').find('[name="'+ fieldName +'"]:checked').data('piotnetforms-send-data-by-label');
										}

										var fieldValue = $(this).closest('.piotnetforms-fields-wrapper').find('[name="'+ fieldName +'"]:checked').val();
										fieldValue = fieldValue ? fieldValue : '';
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

					                		var fieldValue = $(this).val() ? $(this).val().trim() : '';
					                	}
					                }
								}
								
								if (fieldValue != undefined) {
									var fieldItem = {};
									fieldItem['label'] = $(this).closest('.piotnetforms-field-group').find('.piotnetforms-field-label').html();
									fieldItem['name'] = fieldName.replace('[]','').replace('form_fields[','').replace(']','');
									fieldItem['value'] = fieldValue;
									var pafeImageUpload = $(this).attr('data-pafe-field-type');
									fieldItem['image_upload'] = pafeImageUpload ? true : false;
									if (fieldValueMultiple != undefined) {
										fieldItem['value_multiple'] = fieldValueMultiple;
									}
									fieldItem['type'] = $(this).attr('type');

									if ($(this).attr('data-piotnetforms-address-autocomplete') !== undefined) {
										fieldItem['lat'] = $(this).attr('data-piotnetforms-google-maps-lat');
										fieldItem['lng'] = $(this).attr('data-piotnetforms-google-maps-lng');
										fieldItem['zoom'] = $(this).attr('data-piotnetforms-google-maps-zoom');
									}

									if (typeof fieldBooking !== 'undefined' && fieldBooking.length > 0) {
									    fieldItem['booking'] = fieldBooking;
									}

									if (fieldValueByLabel != '') { 
										fieldItem['value_label'] = fieldValueByLabel;
									}
									
									if ($(this).closest('.piotnetforms-field-type-calculated_fields').length > 0) {
										fieldItem['calculation_results'] = $(this).val().trim();
									}
									
									if ($(this).closest('.piotnetforms-conditional-logic-hidden').length == 0) {
										fieldItem['repeater_id'] = repeaterID;
										fieldItem['repeater_id_one'] = repeaterIDOne;
										fieldItem['repeater_label'] = repeaterLabel; 
										fieldItem['repeater_index'] = repeaterIndex; 
										fieldItem['repeater_length'] = repeaterLength;

										if ($(this).data('piotnetforms-remove-this-field-from-repeater') != undefined) {
				                    		fieldItem['repeater_remove_this_field'] = '1';
			                    		}									     
									}
									//Remove option value for number field
									if($(this).attr('data-piotnetforms-remove-value') != undefined && $(this).attr('data-piotnetforms-remove-value') != 'false'){
										if($(this).val() == $(this).attr('data-piotnetforms-remove-value')){
											fieldItem['value'] = '';
										}
									}
									fieldsOj.push(fieldItem);
								}
							}
						}
					}
				}
			});

			if (error == 0) {

				$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 0.45});
				$this.closest('.piotnetforms-submit').css({'opacity' : 0.45});
				$this.closest('.piotnetforms-submit').addClass('piotnetforms-waiting');

				formData.append("action", "piotnetforms_ajax_form_builder");
				formData.append("post_id", $parent.find('input[name="post_id"]').val());
				formData.append("form_id", $parent.find('input[name="form_id"]').val());
				formData.append("fields", JSON.stringify(fieldsOj)); 
				formData.append("referrer", window.location.href);
				formData.append("remote_ip",$(document).find('input[name="remote_ip"][data-piotnetforms-hidden-form-id="'+ formID +'"]').val());

				if ($this.data('piotnetforms-submit-post-edit') != undefined) {
					formData.append("edit", $this.data('piotnetforms-submit-post-edit'));
				}

				if ($this.data('piotnetforms-woocommerce-product-id') != undefined) {
					formData.append("product_id", $this.data('piotnetforms-woocommerce-product-id'));
				}

				if ($this.attr('data-piotnetforms-paypal-submit-transaction-id') != undefined) {
					formData.append("paypal_transaction_id", $this.attr('data-piotnetforms-paypal-submit-transaction-id'));
				}
				//Mollie payment
				if($this.data('piotnetforms-mollie-payment') != undefined){
					let mollie_redirect_url = new URL(window.location.href);
					let mollie_redirect_url_params = new URLSearchParams(mollie_redirect_url.search);
					if(window.location.href.indexOf('#') != -1){
						let mollie_redirect_url_start = window.location.href.slice(0,  window.location.href.indexOf("#"));
						let mollie_redirect_url_end = window.location.href.slice( window.location.href.indexOf("#"));
						let mollieUrl = new URL(mollie_redirect_url_start);
						let mollieUrlParams = new URLSearchParams(mollieUrl.search);
						if(mollieUrlParams.toString() == ''){
							mollie_redirect_url = mollie_redirect_url_start + '?piotnetforms_action=mollie_payment' + mollie_redirect_url_end;
						}else{
							mollie_redirect_url = mollie_redirect_url_start + '&piotnetforms_action=mollie_payment' + mollie_redirect_url_end;
						}
					}else{
						if(mollie_redirect_url_params.toString() == ''){
							mollie_redirect_url = window.location.href + '?piotnetforms_action=mollie_payment'
						}else{
							mollie_redirect_url = window.location.href + '&piotnetforms_action=mollie_payment'
						}
					}
					formData.append("mollie_payment", true);
					formData.append("mollie_redirect_url", mollie_redirect_url);
					$.ajax({
						url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
						type: 'POST',
						dataType: "json",
						data: formData,
						processData: false,
						contentType: false,
						success: function (response) {
							if(response.status == 'open'){
								let formDataObj = {
									mollie_payment_id: response.id,
									formName: formID,
								};
								formData.forEach(function(value, key){
									formDataObj[key] = value;
								});
								delete formDataObj.mollie_payment
								localStorage.setItem('piotnetforms_data_form_mollie_payment', JSON.stringify(formDataObj));
								window.location.href = response._links.checkout.href;
							}else{
								console.log(response.status);
							}
						},
						error: function(response){
							console.log('Mollie payment not success!');
						}
					});
					return;
				}
				if ($this.data('piotnetforms-submit-recaptcha') != undefined) {
					var recaptchaSiteKey = $this.data('piotnetforms-submit-recaptcha');

					grecaptcha.ready(function() {
			            grecaptcha.execute(recaptchaSiteKey, {action: 'create_comment'}).then(function(token) {
			                formData.append("recaptcha",token);

							$parent.find('.piotnetforms-message').removeClass('visible');

							$.ajax({
								url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
								type: 'POST',
								data: formData,
								processData: false,
								contentType: false,
								success: function (response) {
									$parent.css({'opacity' : 1});
									$parent.removeClass('piotnetforms-waiting');
									$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
									if (response.trim() != '') {
						        		$parent.find('.piotnetforms-message-success').addClass('visible');
						        		$parent.find('[data-piotnetforms-trigger-success]').trigger('click');
						        	} else {
						        		$parent.find('.piotnetforms-message-danger').addClass('visible');
						        		$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
					        		}

					        		if (response.indexOf(',') !== -1) {
										var responseArray = JSON.parse(response);
                                        $parent.find('.piotnetforms-message').each(function(){
											if (responseArray.post_url != '') {
								        		var html = $this.html().replace('[post_url]','<a href="' + responseArray.post_url + '">' + responseArray.post_url + '</a>');
								        		$this.html(html);
								        	}
										});
									}

					        		//console.log(response);
							  		// $( 'body' ).trigger( 'update_checkout' );
									// $( 'body' ).trigger( 'wc_update_cart' );

					        		if ($parent.find('input[name="redirect"]').length != 0) {
					        			var href = $parent.find('input[name="redirect"]').val().trim();
					        			var open_tab = $parent.find('input[name="redirect"]').attr('data-piotnetforms-open-new-tab');
					        			if (response.indexOf(',') !== -1) {
											if (responseArray.failed_status != '1') {
												if (responseArray.post_url != '' && href=='[post_url]') {
													window.location.href = responseArray.post_url;
												} else {
													if (responseArray.redirect != '') {
														if (open_tab =='yes') {
													       window.open(responseArray.redirect, "_blank");
													    } else {
														   window.location.href = responseArray.redirect;
														}
													}
												}
											}
										}
					        		}

					        		// Popup
					        		if ($(document).find('[data-piotnetforms-popup][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
					        			$(document).find('[data-piotnetforms-popup][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
	        						}

	        						if ($(document).find('[data-piotnetforms-popup-open][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
					        			$(document).find('[data-piotnetforms-popup-open][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
	        						}

	        						if ($(document).find('[data-piotnetforms-popup-close][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
					        			$(document).find('[data-piotnetforms-popup-close][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
	        						}
								}
							});
			            });
			        }); 
		        } else {

					$parent.find('.piotnetforms-message').removeClass('visible');
					$.ajax({
						url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function (response) {
							$parent.css({'opacity' : 1});
							$parent.removeClass('piotnetforms-waiting');
							$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
							if(response){
								var responseArray = JSON.parse(response);
								$parent.find('.piotnetforms-message').each(function(){
									if (responseArray.post_url != '') {
										var html = $this.html().replace('[post_url]','<a href="' + responseArray.post_url + '">' + responseArray.post_url + '</a>');
										$this.html(html);
									}
								});
							
								if (responseArray.payment_status == 'succeeded' || responseArray.payment_status == 'active') {
									$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-success').addClass('visible');
								}
							
								if (responseArray.payment_status == 'pending') {
									$parent.find('.piotnetforms-alert--stripe .piotnetforms-help-inline').addClass('visible');
								}
							
								if (responseArray.payment_status == 'failed' || responseArray.payment_status == 'incomplete') {
									$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-danger').addClass('visible');
									$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
									$this.closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
								}
								if (responseArray.status != '') {
									$parent.find('.piotnetforms-alert--mail .piotnetforms-message-success').addClass('visible');
									$parent.find('[data-piotnetforms-trigger-success]').trigger('click');
								} else {
									if (responseArray.register_message.error) {
										console.log($parent);
										console.log($parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]'));
										$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]')[0].setCustomValidity(responseArray.register_message.error);
										$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]')[0].reportValidity();
										$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]').on('keyup', function(){
											$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]')[0].setCustomValidity('');
										});
									}else{
										$parent.find('.piotnetforms-alert--mail .piotnetforms-message-danger').addClass('visible');
										$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
										$this.closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
										$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
									}
								}
							}
			        		

			        		//console.log(response);
					  		// $( 'body' ).trigger( 'update_checkout' );
							// $( 'body' ).trigger( 'wc_update_cart' );


							
			        		if ($parent.find('input[name="redirect"]').length != 0) {
			        			var href = $parent.find('input[name="redirect"]').val().trim();
                        		var open_tab = $parent.find('input[name="redirect"]').attr('data-piotnetforms-open-new-tab');

			        			if (response.indexOf(',') !== -1) {
									if (responseArray.failed_status != '1') {
										if (responseArray.post_url != '' && href=='[post_url]') {
											window.location.href = responseArray.post_url;
										} else {
											if (responseArray.redirect != '') {
											    if (open_tab =='yes') {
											       window.open(responseArray.redirect, "_blank");
											    } else {
												   window.location.href = responseArray.redirect;
												}
											
											}
										}
									}
								}
			        		}

			        		// Popup
			        		if ($(document).find('[data-piotnetforms-popup][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
			        			$(document).find('[data-piotnetforms-popup][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
    						}

    						if ($(document).find('[data-piotnetforms-popup-open][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
			        			$(document).find('[data-piotnetforms-popup-open][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
    						}

    						if ($(document).find('[data-piotnetforms-popup-close][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
			        			$(document).find('[data-piotnetforms-popup-close][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
    						}
						}
					});
				} // recaptcha

			}
		}
    }
	
	let piotnetformsCurrentUrlParams = new URLSearchParams(window.location.search);

	if(piotnetformsCurrentUrlParams.get('piotnetforms_action') === 'mollie_payment'){
		piotnetformsCurrentUrlParams.delete('piotnetforms_action')
		let piotnetformsCurrentUrl = window.location.href.replace(window.location.search, '?'+piotnetformsCurrentUrlParams.toString());
		piotnetformsCurrentUrl = new URL(piotnetformsCurrentUrl);
		let currentParams = new URLSearchParams(piotnetformsCurrentUrl.search);
		currentParams.toString() ? window.history.pushState({}, '',piotnetformsCurrentUrl.href) : window.history.pushState({}, '',piotnetformsCurrentUrl.href.replace('?', ''));
		let mollieFormData = localStorage.getItem('piotnetforms_data_form_mollie_payment');
		mollieFormData = JSON.parse(mollieFormData);
		let formID = mollieFormData.formName;
		let $this = $('[data-piotnetforms-mollie-payment="'+formID+'"]');
		let $parent = $this.closest('.piotnetforms-submit');
		let mollie_payment_status, mollieItems;
		if(mollieFormData){
			let formSubmitData = JSON.parse(mollieFormData.fields);
			$.each(formSubmitData, function(index, item){
				if(item.value){
					if (item.type == 'radio') {
						$('[data-piotnetforms-id="'+formID+'"][name="form_fields['+item.name+']"][value="'+item.value+'"]').prop('checked', true);
					} else if (item.type == 'checkbox') {
						mollieItems = item.value.split(',');
						$.each(mollieItems, function(key, value){
							$('[data-piotnetforms-id="'+formID+'"][name="form_fields['+item.name+'][]"][value="'+value+'"]').prop('checked', true);
						});
					} else {
						$('[data-piotnetforms-id="'+formID+'"][name="form_fields['+item.name+']"]').val(item.value);
					}
				}
			});
		}
		$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 0.45});
		$this.closest('.piotnetforms-submit').css({'opacity' : 0.45});
		$this.closest('.piotnetforms-submit').addClass('piotnetforms-waiting');
		$.ajax({
			type: "post",
			dataType: "json",
			url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
			data: {
				action: "piotnetforms_mollie_get_payment",
				payment_id: mollieFormData.mollie_payment_id
			},
			success: function(response){
				if(response.status){
					console.log(response);
					if(response.status == 'paid'){
						mollie_payment_status = "succeeded";
					}else if(response.status == 'pending'){
						mollie_payment_status = "pending";
					}else{
						mollie_payment_status = "failed";
					}
				}else{
					console.log('Mollie payment error!');
				}
			},
			error: function(response){
				console.log('Mollie payment error!');
			}
		});
		$.ajax({
			url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
			type: 'POST',
			data: mollieFormData,
			success: function (response) {
				$parent.css({'opacity' : 1});
				$parent.removeClass('piotnetforms-waiting');
				$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
				if(response){
					var responseArray = JSON.parse(response);
					responseArray.payment_status = mollie_payment_status;
					$parent.find('.piotnetforms-message').each(function(){
						if (responseArray.post_url != '') {
							var html = $this.html().replace('[post_url]','<a href="' + responseArray.post_url + '">' + responseArray.post_url + '</a>');
							$this.html(html);
						}
					});
				
					if (responseArray.payment_status == 'succeeded' || responseArray.payment_status == 'active') {
						$parent.find('.piotnetforms-alert--mollie .piotnetforms-message-success').addClass('visible');
					}
				
					if (responseArray.payment_status == 'pending') {
						$parent.find('.piotnetforms-alert--mollie .piotnetforms-help-inline').addClass('visible');
					}
				
					if (responseArray.payment_status == 'failed' || responseArray.payment_status == 'incomplete') {
						$parent.find('.piotnetforms-alert--mollie .piotnetforms-message-danger').addClass('visible');
						$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
						$this.closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
					}
					if (responseArray.status != '') {
						$parent.find('.piotnetforms-alert--mail .piotnetforms-message-success').addClass('visible');
						$parent.find('[data-piotnetforms-trigger-success]').trigger('click');
					} else {
						if (responseArray.register_message.error) {
							console.log($parent);
							console.log($parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]'));
							$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]')[0].setCustomValidity(responseArray.register_message.error);
							$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]')[0].reportValidity();
							$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]').on('keyup', function(){
								$parent.closest('#piotnetforms').find('[name="form_fields['+responseArray.register_message.field_existing+']"]')[0].setCustomValidity('');
							});
						}else{
							$parent.find('.piotnetforms-alert--mail .piotnetforms-message-danger').addClass('visible');
							$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
							$this.closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
							$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
						}
					}
				}
				

				//console.log(response);
				  // $( 'body' ).trigger( 'update_checkout' );
				// $( 'body' ).trigger( 'wc_update_cart' );


				
				if ($parent.find('input[name="redirect"]').length != 0) {
					var href = $parent.find('input[name="redirect"]').val().trim();
					var open_tab = $parent.find('input[name="redirect"]').attr('data-piotnetforms-open-new-tab');

					if (response.indexOf(',') !== -1) {
						if (responseArray.failed_status != '1') {
							if (responseArray.post_url != '' && href=='[post_url]') {
								window.location.href = responseArray.post_url;
							} else {
								if (responseArray.redirect != '') {
									if (open_tab =='yes') {
									   window.open(responseArray.redirect, "_blank");
									} else {
									   window.location.href = responseArray.redirect;
									}
								
								}
							}
						}
					}
				}

				// Popup
				if ($(document).find('[data-piotnetforms-popup][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
					$(document).find('[data-piotnetforms-popup][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
				}

				if ($(document).find('[data-piotnetforms-popup-open][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
					$(document).find('[data-piotnetforms-popup-open][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
				}

				if ($(document).find('[data-piotnetforms-popup-close][data-piotnetforms-hidden-form-id="'+ formID +'"]').length != 0) {
					$(document).find('[data-piotnetforms-popup-close][data-piotnetforms-hidden-form-id="'+ formID +'"]').trigger('click');
				}
			}
		});
		localStorage.removeItem('piotnetforms_data_form_mollie_payment');
	}

	$(document).on('click','[data-piotnetforms-submit-form-id]',function(e){
		e.preventDefault();
		clickSubmit($(this));
    });

	// Fix Oxygen Modal
    $('.ct-modal [data-piotnetforms-submit-form-id]').click(function(e){
		e.preventDefault();
		clickSubmit($(this));
    });

	$(document).on('click','[data-piotnetforms-trigger-success]',function(){
		if ($(this).closest('.piotnetforms-fields-wrapper').find('[data-piotnetforms-submit-update-user-profile]').length == 0) {
			var formId = $(this).attr('data-piotnetforms-trigger-success'),
				$fields = $(document).find('[data-piotnetforms-id="' + formId + '"]');

			$fields.each(function(){
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

	        	if ($fields.closest('.piotnetforms-label-animated').length > 0) {
	        		$fields.closest('.piotnetforms-label-animated').removeClass('piotnetforms-label-animated');
	        	}
			});
		}
	});
	function pfdelayForNumberFieldtype(callback,ms) {
		var timer = 0;
		return function() {
			var context = this;
			clearTimeout(timer);
			timer = setTimeout(function () {
				callback.apply(context);
			}, ms);
		};
	}

	$(document).on('change keyup paste', '[type="number"][data-piotnetforms-id]',pfdelayForNumberFieldtype(function(e){
		var val = $(this).val();
		var min = $(this).attr('min');
		var max = $(this).attr('max');
		var isChanged = false;
		if(parseInt(min) > parseInt(val)){
			isChanged = true;
			$(this).val(min);
		}else if(parseInt(val) > parseInt(max)){
			isChanged = true;
			$(this).val(max);
		}
		if (isChanged) {
			$(this).change();
		}
	}, 500));

	$('[type="number"][data-piotnetforms-id]').bind('paste', function(e){
		var self = this;
		var min = $(this).attr('min');
		var max = $(this).attr('max');
		setTimeout(function(e) {
			var isChanged = false;
			var val2 = $(self).val();
			if(parseInt(min) > parseInt(val2)){
				isChanged = true;
				$(self).val(min);
			}else if(parseInt(val2) > parseInt(max)){
				isChanged = true;
				$(self).val(max);
			}
			if (isChanged) {
				$(this).change();
			}
		}, 0);
	});

	$('[data-piotnetforms-delete-post]').click(function(e){
		e.preventDefault();
    	var data = {
			'action': 'piotnetforms_delete_post',
			'id': $(this).data('piotnetforms-delete-post'),
			'force_delete': $(this).data('piotnetforms-delete-post-force'),
		};

		var redirect = $(this).data('piotnetforms-delete-post-redirect');

        $.post($('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'), data, function(response) {
        	if (response.trim() != '') {
        		window.location.href = redirect;
        	}
		});
    });

    function paymentMethodsSelectField() {
		$('[data-piotnetforms-payment-methods-select-field]').each(function(){
			var fieldType = $(this).attr('type'),
				formID = $(this).attr('data-piotnetforms-id'),
				$submit = $(document).find('[data-piotnetforms-submit-form-id="' + formID + '"]');

			if (fieldType == 'radio' || fieldType == 'checkbox') {
				var fieldValue = $(this).closest('.piotnetforms-fields-wrapper').find('input:checked').val();
	        } else {
	        	var fieldValue = $(this).val().trim();
	        }

	        if (fieldValue != $(this).attr('data-piotnetforms-payment-methods-select-field-value-for-stripe')) {
	        	if ($submit.attr('data-piotnetforms-stripe-currency') !== undefined) {
	        		$submit.removeAttr('data-piotnetforms-stripe-submit');
	        	}
	        } else {
	        	if ($submit.attr('data-piotnetforms-stripe-currency') !== undefined) {
	        		$submit.attr('data-piotnetforms-stripe-submit', '');
	        	}
	        }

	        if (fieldValue != $(this).attr('data-piotnetforms-payment-methods-select-field-value-for-paypal')) {
	        	if ($submit.attr('data-piotnetforms-paypal-submit-enable') !== undefined) {
	        		$submit.removeAttr('data-piotnetforms-paypal-submit');
	        		$submit.closest('.piotnetforms-submit').find('.piotnetforms-paypal').hide();
	        	}
	        } else {
	        	if ($submit.attr('data-piotnetforms-paypal-submit-enable') !== undefined) {
	        		$submit.attr('data-piotnetforms-paypal-submit', '');
	        		$submit.closest('.piotnetforms-submit').find('.piotnetforms-paypal').show();
	        	}
	        }
		});
	}  

	paymentMethodsSelectField();

	$(document).on('change','[data-piotnetforms-payment-methods-select-field]', function(){
		paymentMethodsSelectField();
	});

	window.setFieldValue = function ($fieldCurrent, setValue) {
		var checkSelect = $fieldCurrent.find('option:first');

        var checkRadioCheckbox = false;

        if ($fieldCurrent.attr('type') == 'radio' || $fieldCurrent.attr('type') == 'checkbox') {
        	checkRadioCheckbox = true;
        }

        var checkImageUpload = $fieldCurrent.closest('.piotnetforms-fields-wrapper').find('[data-piotnetforms-image-upload]');
  
        var defaultValue = $fieldCurrent.data('piotnetforms-default-value');
		
		$fieldCurrent.each(function(){
			if (setValue != '' && checkRadioCheckbox ) {
				var splValue = setValue.split(",");
				for (var i = 0; i < splValue.length; i++) {
					if (splValue[i] == $(this).val()) {
						$(this).prop('checked', true);
						//$(this).change();
					}
				}
				// if (setValue == $(this).val()) {
				// 	$(this).prop('checked', true);
				// 	//$(this).change();
				// }

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

			if (setValue != '' && !checkRadioCheckbox && checkSelect.length == 0) {
				if ($(this).hasClass('piotnetforms-date-field')) {
					$(this).attr('data-piotnetforms-value',setValue);
				} else {
					$(this).val(setValue);
				}
			}

			if (checkSelect.length != 0) {
				var $options = $(this).find('option');
				var $fieldSelector = $(this);
				$options.each(function(){
					if ($(this).attr('value') == setValue || $(this).html() == setValue) {
						$fieldSelector.val($(this).attr('value'));
						if ($fieldSelector.data('picker') != undefined) {
							$fieldSelector.data('picker').sync_picker_with_select();
						}
						
					}
				}); 
			}

			if (checkImageUpload.length != 0) {
				if (setValue != '' && setValue != null) {
					var images = setValue.split(',');
					var $label = $(this).closest('.piotnetforms-fields-wrapper').find('[data-piotnetforms-image-upload-label]');

					if ($label.attr('multiple') == undefined) {
						$label.addClass('piotnetforms-image-upload-label-hidden');
					}
					
					for(var k in images) {
						$label.before('<div class="piotnetforms-image-upload-placeholder piotnetforms-image-upload-uploaded" style="background-image:url(' + images[k] + ')" data-piotnetforms-image-upload-placeholder=""><input type="text" style="display:none;" data-piotnetforms-image-upload-item value="' + images[k] + '"><span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--remove" data-piotnetforms-image-upload-button-remove><i class="fa fa-times" aria-hidden="true"></i></span><span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--uploading" data-piotnetforms-image-upload-button-uploading><i class="fa fa-spinner fa-spin"></i></span></div>');
					}
				}
			}

			$(this).change();
		}); 
	}

	$('[data-piotnetforms-submit-update-user-profile]').each(function(){
		var formID = $(this).attr('data-piotnetforms-submit-form-id'),
			userMeta = JSON.parse( $(this).attr('data-piotnetforms-submit-update-user-profile') );

		for (var i=0; i < userMeta.length; i++) {
			var $field = $(document).find('[data-piotnetforms-id="' + formID + '"][name^="form_fields[' + userMeta[i]['field_id'] + ']"]');
			setFieldValue($field, userMeta[i]['user_meta_value']);
		}
	});

	$(document).on('focus', '.piotnetforms-fields-wrapper [data-piotnetforms-id]', function(){
		var $parent = $(this).closest('.piotnetforms-fields-wrapper');
		$parent.addClass('piotnetforms-field-focus');
		$parent.addClass('piotnetforms-label-animated');
	});

	$(document).on('blur', '.piotnetforms-fields-wrapper [data-piotnetforms-id]', function(){
		var $parent = $(this).closest('.piotnetforms-fields-wrapper');
		$parent.removeClass('piotnetforms-field-focus');

		if($(this).val() === ''){
			$parent.removeClass('piotnetforms-label-animated');
		}
	});

	// Fix Oxygen not full width
	$('#piotnetforms').closest('span.ct-span').css({'display':'block'});

	setTimeout(piotnetformsLivePreview(), 1000);

	$(document).on('keyup change','[data-piotnetforms-id]', $.debounce( 250, function(){
			piotnetformsLivePreview($(this)); 
		})
	);

	function piotnetformsLivePreview(field) {
		if ($(document).find('[data-piotnetforms-live-preview]').length > 0) {
			var fieldValue = $(field).val(),
			    fieldType = $(field).attr('type'),
				fieldId = $(field).attr('id'),
				repeater = $(field).closest('.piotnet-section').attr('data-piotnetforms-repeater-form-id'),
				$livePreview;
			if (fieldId !== undefined) {
				var allVals = [];
				var fieldName = $(field).attr('name').replace('[]','').replace('form_fields[','').replace(']','');
				if (fieldType == 'checkbox') {
                    $.each($('input[name="'+ $(field).attr('name') +'"]:checked'), function() {
                      allVals.push($(this).val());
                    });
                    fieldValue = allVals.join(", ");
                } 
				if(repeater){
					$livePreview = $(field).closest('.piotnet-section').find('[data-piotnetforms-live-preview="' + fieldName + '"]');
				}else{
					$livePreview = $(document).find('[data-piotnetforms-live-preview="' + fieldName + '"]');
				}
				$livePreview.each(function(){
					$(this).html(fieldValue);
				});
			}
		}
	}
	//Show password for field password
	$(document).on('click', '[data-piotnetforms-show-password-icon="true"]', function(){
		let passwodName = $(this).attr('data-piotnetforms-field-name');
		let currentType = $(this).closest('.piotnetforms-field-container').find('[name="form_fields['+passwodName+']"]').attr('type');
		let showPasswordIcon = $(this).find('#eye-icon-'+passwodName+'');
		console.log('#eye-icon-'+passwodName+'');
		if(currentType == 'password'){
			$(this).closest('.piotnetforms-field-container').find('[name="form_fields['+passwodName+']"]').attr('type', 'text');
			showPasswordIcon.addClass("fa-eye-slash");
		}else{
			$(this).closest('.piotnetforms-field-container').find('[name="form_fields['+passwodName+']"]').attr('type', 'password');
			showPasswordIcon.removeClass("fa-eye-slash");
		}
	});

});
