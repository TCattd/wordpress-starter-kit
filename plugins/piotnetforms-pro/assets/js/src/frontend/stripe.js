jQuery(document).ready(function($) {	

	function initWidgetStripe($scope, $) {

	    var $elements = $scope.find('[data-piotnetforms-stripe]');

		if (!$elements.length) {
			return;
		}

		$.each($elements, function (i, $element) {

			// Create a Stripe client
			var stripPk = $('[data-piotnetforms-stripe-key]').data('piotnetforms-stripe-key');
			var stripe = Stripe(stripPk);

			// Create an instance of Elements
			var elements = stripe.elements();

			// Custom styling can be passed to options when creating an Element.
			// (Note that this demo uses a wider set of styles than the guide below.)
			var style = {
			  base: {
			    color: '#32325d',
			    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
			    fontSmoothing: 'antialiased',
			    fontSize: '16px',
			    '::placeholder': {
			      color: '#aab7c4'
			    }
			  },
			  invalid: {
			    color: '#fa755a',
			    iconColor: '#fa755a'
			  }
			};

			// Create an instance of the card Element
			var card = elements.create('card', { style: style });

			// Add an instance of the card Element into the `card-element` <div>
			card.mount($element);

			var formIdStripe = $($element).data('piotnetforms-id');

			$(document).on('click','[data-piotnetforms-submit-form-id][data-piotnetforms-stripe-submit]',function(){
				if ( $(this).data('piotnetforms-stripe-submit') != undefined ) {
			    	var formID = $(this).data('piotnetforms-submit-form-id'),
			    		$fields = $(document).find('[data-piotnetforms-id="'+ formID +'"]'),
			    		requiredText = $(this).data('piotnetforms-required-text'),
			    		fieldsOj = [],
			    		error = 0,
			    		formData = new FormData();

		    		var $submit = $(this);
					var $parent = $submit.closest('.piotnetforms-submit');

					$fields.each(function(){
						if ( $(this).data('piotnetforms-stripe') == undefined && $(this).data('piotnetforms-html') == undefined ) {

							if ($(this).attr('oninvalid') != undefined) {
								requiredText = $(this).attr('oninvalid').replace("this.setCustomValidity('","").replace("')","");
							}

							if ( !$(this)[0].checkValidity() && $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 && $(this).closest('[data-piotnetforms-conditional-logic]').css('display') != 'none' &&  $(this).closest('[data-piotnetforms-signature]').length == 0 ) {
								if ($(this).css('display') == 'none' || $(this).data('piotnetforms-image-select') != undefined) {
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

										if ($(this).val() == '' && $(this).attr('required') == 'required') {
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

											if ($(this).data('piotnetforms-remove-this-field-from-repeater') != undefined) {
					                    		fieldItem['repeater_remove_this_field'] = '1';
				                    		}

											if($(this).data('attach-files') != undefined) {
												fieldItem['attach-files'] = 1;
											}

											fieldsOj.push(fieldItem);

										}

									} else {
										if (fieldName.indexOf('[]') !== -1) {
						                    var fieldValueMultiple = [];

						                    if (fieldType == 'checkbox') {
						                        $(document).find('[name="'+ fieldName + '"]:checked').each(function () {
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
												fieldItem['repeater_index'] = repeaterIndex;
												fieldItem['repeater_label'] = repeaterLabel;
												fieldItem['repeater_length'] = repeaterLength;

												if ($(this).data('piotnetforms-remove-this-field-from-repeater') != undefined) {
						                    		fieldItem['repeater_remove_this_field'] = '1';
					                    		}

											    fieldsOj.push(fieldItem); 
											}
										}
									}
								}
							}
						}
					});

					if (error == 0) {

						stripe.createToken(card).then(function(result) {
							if (result.error) {
								// Inform the user if there was an error
								//var errorElement = document.getElementById('card-errors');
								//errorElement.textContent = result.error.message;
							} else {
								$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 0.45});
								$submit.closest('.piotnetforms-submit').css({'opacity' : 0.45});
								$submit.closest('.piotnetforms-submit').addClass('piotnetforms-waiting');

								$parent.find('.piotnetforms-message').removeClass('visible');

								var amount = 0;

								if ($submit.data('piotnetforms-stripe-amount') != undefined) {
									amount = $submit.data('piotnetforms-stripe-amount');
								} else {
									if ($submit.data('piotnetforms-stripe-amount-field') != undefined) {
										var stripeAmountFieldName = $submit.data('piotnetforms-stripe-amount-field').replace('[field id="','').replace('"]','');
										amount = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + stripeAmountFieldName + ']"]').val();
									}
								}

								var description = '';

								if ($submit.data('piotnetforms-stripe-customer-info-field') != undefined) {
									var customerInfoFieldName = $submit.data('piotnetforms-stripe-customer-info-field').replace('[field id="','').replace('"]','');
									description = $(document).find('[data-piotnetforms-id="' + formID + '"][name="form_fields[' + customerInfoFieldName + ']"]').val();
								}

								var data = {
									'action': 'piotnetforms_ajax_form_builder',
									'post_id': $(document).find('input[name="post_id"][data-piotnetforms-hidden-form-id="'+ formID +'"]').eq(0).closest('[data-piotnetforms-id]').data('piotnetforms-id'),
									'form_id': $(document).find('input[name="form_id"][data-piotnetforms-hidden-form-id="'+ formID +'"]').val(),
									'fields' : fieldsOj,
									'stripeToken': result.token.id,
									'amount' : amount,
									'description' : description,
								};

								formData.append("action", "piotnetforms_ajax_stripe_intents");
								formData.append("post_id", $parent.find('input[name="post_id"]').val());
								formData.append("form_id", $parent.find('input[name="form_id"]').val());
								formData.append("fields", JSON.stringify(fieldsOj));
								formData.append("referrer", window.location.href);
								formData.append("remote_ip",$(document).find('input[name="remote_ip"][data-piotnetforms-hidden-form-id="'+ formID +'"]').val());
								formData.append("stripeToken", result.token.id);
								formData.append("amount", amount);
								formData.append("description", description);

								if ($(this).data('piotnetforms-woocommerce-product-id') != undefined) {
									formData.append("product_id", $(this).data('piotnetforms-woocommerce-product-id'));
								}

								if ($submit.data('piotnetforms-submit-post-edit') != undefined) {
									formData.append("edit", $submit.data('piotnetforms-submit-post-edit'));
								}

								if ($submit.data('piotnetforms-submit-recaptcha') != undefined) {
									var recaptchaSiteKey = $submit.data('piotnetforms-submit-recaptcha');
									grecaptcha.ready(function() {
							            grecaptcha.execute(recaptchaSiteKey, {action: 'create_comment'}).then(function(token) {
							            	formData.append("recaptcha",token);

							            	stripe.createPaymentMethod('card', card, {
												// billing_details: {name: cardholderName.value}
											}).then(function(result) {
												if (result.error) {
													// Show error in payment form
												} else {

													formData.append("payment_method_id", result.paymentMethod.id);

													$.ajax({
														url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
														type: 'POST',
														data: formData,
														processData: false,
														contentType: false,
														success: function (response) {
															//console.log(response); 
															var post_id = $(document).find('input[name="post_id"][data-piotnetforms-hidden-form-id="'+ formID +'"]').eq(0).closest('[data-piotnetforms-id]').data('piotnetforms-id'),
																fields = JSON.stringify(fieldsOj);

													        var	json = JSON.parse(response);
													        //response.json().then(function(json) { 
															var payment_result = handleServerResponse(json, post_id, formID, fields, amount, stripe, formData, $parent, $(this));
															card.clear();
														},
														error: function () {
															$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
												        	$parent.css({'opacity' : 1});
															$parent.removeClass('piotnetforms-waiting');
															$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-danger').addClass('visible');
															$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
															card.clear();
														},

													});
												}
											});

							            });
							        });
						        } else {

									stripe.createPaymentMethod('card', card, {
										// billing_details: {name: cardholderName.value}
									}).then(function(result) {
										if (result.error) {
											// Show error in payment form
										} else {

											formData.append("payment_method_id", result.paymentMethod.id);

											$.ajax({
												url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
												type: 'POST',
												data: formData,
												processData: false,
												contentType: false,
												success: function (response) {
													//console.log(response); 
													var post_id = $(document).find('input[name="post_id"][data-piotnetforms-hidden-form-id="'+ formID +'"]').eq(0).closest('[data-piotnetforms-id]').data('piotnetforms-id'),
														fields = JSON.stringify(fieldsOj);

											        var	json = JSON.parse(response);
											        //response.json().then(function(json) { 
													var payment_result = handleServerResponse(json, post_id, formID, fields, amount, stripe, formData, $parent, $(this));
													card.clear();
												},
												error: function () {
													$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
										        	$parent.css({'opacity' : 1});
													$parent.removeClass('piotnetforms-waiting');
													$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-danger').addClass('visible');
													$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
													card.clear();
												},

											});
										}
									});
								}

							}
						});

					}
				}
		    });

		});

	};

	function handleServerResponse(response, post_id, form_id, fields, amount, stripe, formData, parent, $this) {  
	  if (response.error) { 
	    // Show error from server on payment form
	    submitStripeForm('failed', parent, form_id, formData, $this, response); 
	  } else if (response.requires_action) {
	    // Use Stripe.js to handle required card action
	    if (response.subscriptions) {
	    	stripe.handleCardPayment(response.payment_intent_client_secret).then(function(result) {
			  if (result.error) {
			    // Display error.message in your UI.
			  } else {
			    // The payment has succeeded. Display a success message. 
		        submitStripeForm('succeeded', parent, form_id, formData, $this, response); 
			  }
			});
	    } else {
		    stripe.handleCardAction(
		      response.payment_intent_client_secret
		    ).then(function(result) {
		      if (result.error) {
		        // Show error in payment form
		        submitStripeForm('failed', parent, form_id, formData, $this, response); 
		      } else {
		        // The card action has been handled
		        // The PaymentIntent can be confirmed again on the server

		        formData.delete('payment_method_id');
		        formData.append('payment_intent_id', result.paymentIntent.id);

		        $.ajax({
					url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function (confirmResult) {
						console.log(confirmResult); 
			        	handleServerResponse(JSON.parse(confirmResult), post_id, form_id, fields, amount, stripe, formData); 
			        }
		        }); 
		      }
		    });
	    }

	  } else {
	    // Show success message
	    submitStripeForm('succeeded', parent, form_id, formData, $this, response); 
	  }
	} 

	function submitStripeForm(paymentResult, parent, formID, formData, $this, responseIntent) {

    	var $parent = parent;

    	if (paymentResult == 'failed') {
    		$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
        	$parent.css({'opacity' : 1});
			$parent.removeClass('piotnetforms-waiting');
			$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-danger').addClass('visible');
			$(document).find('[data-piotnetforms-submit-form-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
			$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
		} else { 
    	
			formData.delete('stripeToken');
			formData.delete('action');
			formData.append('action', 'piotnetforms_ajax_form_builder');
			formData.append('payment_intent_id', responseIntent.payment_intent_id );

			$.ajax({
				url: $('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'),
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					//console.log(response);
					$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
		        	$parent.css({'opacity' : 1});
					$parent.removeClass('piotnetforms-waiting');
					var response = response.trim();

					if (response.indexOf(',') !== -1) {
						var responseArray = response.split(',');

						$parent.find('.piotnetforms-message').each(function(){
							if (responseArray[3] != '') {
				        		var html = $(this).html().replace('[post_url]','<a href="' + responseArray[3] + '">' + responseArray[3] + '</a>');
				        		$(this).html(html);
				        	}
						});

						if (paymentResult == 'succeeded' || paymentResult == 'active') {
			        		$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-success').addClass('visible');
			        		$parent.find('[data-piotnetforms-trigger-success]').trigger('click');
			        	} else {
			        		$parent.find('.piotnetforms-alert--stripe .piotnetforms-message-danger').addClass('visible');
			        		$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
							$(document).find('[data-piotnetforms-submit-form-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
							$parent.find('[data-piotnetforms-trigger-failed]').trigger('click');
			        	}

			        	if (responseArray[1] != '') {
			        		$parent.find('.piotnetforms-alert--mail .piotnetforms-message-success').addClass('visible');
			        	} else {
			        		$parent.find('.piotnetforms-alert--mail .piotnetforms-message-danger').addClass('visible');
			        		$(document).find('[data-piotnetforms-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});
							$(document).find('[data-piotnetforms-submit-form-id="' + formID + '"]').closest('.piotnetforms-fields-wrapper').css({'opacity' : 1});

							if (responseArray[5] != '') {
				        		$parent.find('.piotnetforms-alert--mail .piotnetforms-message-danger').html(responseArray[5].replace(/###/g, ','));
				        	}
			        	}
	 
			        	if ($parent.find('input[name="redirect"]').length != 0) {
		        			var href = $parent.find('input[name="redirect"]').val().trim();
		        			if (response.indexOf(',') !== -1) {
								if (responseArray[6] != '1') {
									if (responseArray[3] != '' && href=='[post_url]') {
										window.location.href = responseArray[3];
									} else {
										if (responseArray[4] != '') {
											window.location.href = responseArray[4];
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
		        }
	  		});
  		}
	}

	$(document).on('piotnet-widget-init-Piotnetforms_Field', '[data-piotnet-editor-widgets-item-root]', function(){
		initWidgetStripe($(this), $);
	});

	$(window).on('load', function(){
		initWidgetStripe($('[data-piotnet-widget-preview], #piotnetforms'), $);
	});
});