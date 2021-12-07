jQuery(document).ready(function( $ ) {

	function IDGenerator() {
		this.length = 8;
		this.timestamp = +new Date;

		var _getRandomInt = function( min, max ) {
			return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
		}

		this.generate = function() {
			var ts = this.timestamp.toString();
			var parts = ts.split( "" ).reverse();
			var id = "";

			for( var i = 0; i < this.length; ++i ) {
				var index = _getRandomInt( 0, parts.length - 1 );
				id += parts[index];	 
			}

			return id;
		}
	}

	$(document).on('keyup change', '[data-piotnetforms-id]', function() {
		var formId = $(this).attr('data-piotnetforms-id');

		if ($('[data-piotnetforms-abandonment] [data-piotnetforms-submit-form-id="' + formId + '"]').length > 0) {
			var	fieldValue = $(this).val(),
				fieldID = $(this).attr('name').replace('form_fields[','').replace(']','').replace('[]',''),
				abandonmentID = 'piotnetforms-abandonment-' + formId;

			if ($(this).attr('type') == 'checkbox') {
				if ($(this).attr('name').includes('[]')) {
					fieldValue = [];
					var $form = $(this).closest('form');
					$form.find('[name="form_fields[' + fieldID + '][]"]').each(function(){
						if ($(this).is(':checked')) {
							fieldValue.push($(this).val());
						}
					});
				} else {
					if (!$(this).is(':checked')) {
						fieldValue = '';
					}
				}
			}

			if (localStorage[abandonmentID]) {
				var piotnetformsFormAbandonmentData = JSON.parse(localStorage.getItem(abandonmentID));
				piotnetformsFormAbandonmentData[fieldID] = fieldValue;
			} else {
				var userIdObject = new IDGenerator(),
					userId = userIdObject.generate();

				var piotnetformsFormAbandonmentData = { userId : userId, form_id : formId };
				piotnetformsFormAbandonmentData[fieldID] = fieldValue;
			}

			localStorage.setItem(abandonmentID, JSON.stringify(piotnetformsFormAbandonmentData));
		}
	});

	$('[data-piotnetforms-abandonment] [data-piotnetforms-submit-form-id]').each(function() {
		var formId = $(this).attr('data-piotnetforms-submit-form-id'),
			abandonmentID = 'piotnetforms-abandonment-' + formId,
			$fields = $('[data-piotnetforms-id][name^="form_fields"]');

		if (localStorage[abandonmentID]) {
			var piotnetformsFormAbandonmentData = JSON.parse(localStorage.getItem(abandonmentID));

			$fields.each(function(){
				var fieldType = $(this).attr('type');
				var fieldID = $(this).attr('name').replace('form_fields[','').replace(']','').replace('[]','');

				if (fieldType == 'radio') {
					if (piotnetformsFormAbandonmentData[fieldID] !== undefined && $(this).attr('value') == piotnetformsFormAbandonmentData[fieldID]) {
						$(this).prop('checked', true);
					}
				} else if (fieldType == 'checkbox') {
					if (piotnetformsFormAbandonmentData[fieldID] !== undefined) {
						if ($(this).attr('name').includes('[]')) {
							if (piotnetformsFormAbandonmentData[fieldID].includes($(this).attr('value'))) {
								$(this).prop('checked', true);
							}
						} else {
							if ($(this).attr('value') == piotnetformsFormAbandonmentData[fieldID]) {
								$(this).prop('checked', true);
							}
						}
					}
				} else {
					if (piotnetformsFormAbandonmentData[fieldID] !== undefined) {
						$(this).val(piotnetformsFormAbandonmentData[fieldID]);
					}
				}
				
			});
		}
	});

	$(document).on('click','[data-piotnetforms-trigger-success]',function(){
		var formId = $(this).attr('data-piotnetforms-trigger-success'),
			abandonmentID = 'piotnetforms-abandonment-' + formId;
		let $webhook_attr = $('[data-piotnetforms-abandonment-webhook]');
		let webhook_url = $webhook_attr.length > 0 ? $webhook_attr.attr('data-piotnetforms-abandonment-webhook') : false;
		if (localStorage[abandonmentID]) {

			var piotnetformsFormAbandonmentData = JSON.parse(localStorage.getItem(abandonmentID));

			var data = new FormData();

			data.append('action', 'piotnetforms_form_abandonment');
			data.append('fields', JSON.stringify(piotnetformsFormAbandonmentData));
			data.append('form_type', 'piotnetforms Form');
			data.append('function', 'success');
			data.append('webhook', webhook_url);

			navigator.sendBeacon($('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'), data);

			localStorage.removeItem(abandonmentID);
		}
	});	

	function piotnetformsAbandonment(event) {
		$('[data-piotnetforms-abandonment] [data-piotnetforms-submit-form-id]').each(function() {
			var formId = $(this).attr('data-piotnetforms-submit-form-id'),
			abandonmentID = 'piotnetforms-abandonment-' + formId;
			let $webhook_attr = $('[data-piotnetforms-abandonment-webhook]');
			let webhook_url = $webhook_attr.length > 0 ? $webhook_attr.attr('data-piotnetforms-abandonment-webhook') : false;
			event == 'blur' ? webhook_url = false : '';

			if (localStorage[abandonmentID]) { 

				var piotnetformsFormAbandonmentData = JSON.parse(localStorage.getItem(abandonmentID));

				var data = new FormData();

				data.append('action', 'piotnetforms_form_abandonment');
				data.append('fields', JSON.stringify(piotnetformsFormAbandonmentData));
				data.append('form_type', 'piotnetforms');
				data.append('function', 'abandonment');
				data.append('webhook', webhook_url);

				navigator.sendBeacon($('[data-piotnetforms-ajax-url]').data('piotnetforms-ajax-url'), data);
			}
		});
	}

	$(window).on('blur beforeunload', function(e) {
		piotnetformsAbandonment(e.type);
	});
});