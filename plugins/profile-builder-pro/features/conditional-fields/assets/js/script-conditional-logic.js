/**
 *  AJAX Conditional logic for fields scripts
 */
jQuery(document).ready(function($){
	var wppbConditionalAjaxTriggered = false;
	var wppbTypingTimer;                //timer identifier
	var wppbDoneTypingInterval =  500;  //time in ms

	wppb_conditional_logic();
	$(".wppb-user-forms").on( "change keyup", '.wppb-form-field select, .wppb-form-field input, .wppb-form-field input[type="checkbox"], .wppb-form-field textarea',  function(e){
		if( e.type === 'keyup' ){
			clearTimeout(wppbTypingTimer);
			wppbTypingTimer = setTimeout(wppb_conditional_logic, wppbDoneTypingInterval);
		}
		else {
			wppb_conditional_logic();
		}
		wppbConditionalAjaxTriggered = true;
	});

	//this is added for cascading conditionals ( if a field is triggered by another conditional field at page load )
	$("body").on('DOMSubtreeModified', ".wppb-user-forms ul", function() {
		if( !wppbConditionalAjaxTriggered ) {
			wppb_conditional_logic();
			wppbConditionalAjaxTriggered = true;
		}
	});

	function wppb_conditional_logic(){
		data = {};

		formType = '';
		formName = '';
		formID   = '';

		$('.wppb-form-field').each(function(){
			if(($(this).find('input').length || $(this).find('select').length || $(this).find('textarea').length) && typeof $(this).attr('id') != "undefined"){

				id = $(this).attr('id').replace('wppb-form-element-', '');


				value = $( 'input[type="text"], input[type="email"], input[type="number"], input[type="hidden"], textarea, select option:selected, input[type="checkbox"]:checked, input[type="radio"]:checked, input[type="url"]', $(this) ).map(function(){return jQuery(this).val(); }).get();
				data[id] = value;
			}

			formType = $(this).closest('form').find('#action').val();
			formName = $(this).closest('form').find('#form_name').val();
			formID = $(this).closest('form').find('#form_id').val();
		});

		var data = {
			'action'    : 'wppb_conditional_logic',
			'data'  	: JSON.stringify(data),
			'formType'  : formType,
			'formName'  : formName,
			'formID'  	: formID,

		};

		$.post( wppb_conditional_ajax.ajaxUrl, data, function(ret){
			res = JSON.parse(ret);
			for(i in res){
				if(res[i].type == 'show'){
					if($('#wppb-form-element-'+i).html() == '') $('#wppb-form-element-'+i).html(res[i].html);
					$('#wppb-form-element-'+i).show();
				}
				if(res[i].type == 'hide'){
					$('#wppb-form-element-'+i).html('');
					$('#wppb-form-element-'+i).hide();
				}
			}
		});

	}
});