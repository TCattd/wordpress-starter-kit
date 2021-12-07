import jqueryValidation from 'jquery-validation';
import additional_methods from 'jquery-validation/dist/additional-methods';

jQuery(document).ready(function($) {

	$(document).on('change','[name="upload_field"]',function(){
		var $form = $(this).closest('form');

		var $input = $(this),
			extension = $input.data('accept');

		if (extension == undefined) {
			extension = 'jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx,odt,avi,ogg,m4a,mov,mp3,mp4,mpg,wav,wmv';
		}

		if (extension != undefined) {
			if (extension.trim() == '') {
				extension = 'jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx,odt,avi,ogg,m4a,mov,mp3,mp4,mpg,wav,wmv';
			}
		}

		$form.validate({
			rules: {
				'upload_field': {
					extension: extension,
					maxsize: parseInt( $input.data('maxsize') ) * 1048576,
				}
			},

		    messages:{
		        'upload_field': {
		        	extension: ($input.attr('data-types-message') !== undefined ) ? $input.data('types-message') : '',
		        	maxsize: $input.data('maxsize-message'),
		        }

		    }
	    });

		$form.submit(function (ev) {
    		ev.preventDefault();
	    });
		$form.trigger('submit');
	});
});
