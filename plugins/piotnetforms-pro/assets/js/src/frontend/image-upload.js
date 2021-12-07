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

	$(document).on('keyup change','[data-piotnetforms-image-upload]', function(){

		var $label = $(this).closest('label'),
			$widget = $(this).closest('.piotnetforms-fields-wrapper'),
			maxFiles = 1000;

		if ($label.attr('data-piotnetforms-image-upload-max-files') !== undefined) {
			maxFiles = parseInt($label.attr('data-piotnetforms-image-upload-max-files'));
			var currentFiles = $widget.find('.piotnetforms-image-upload-placeholder.piotnetforms-image-upload-uploaded:not(.piotnetforms-image-upload-delete)').length;
			maxFiles = maxFiles - currentFiles;
		}

		$.each($(this)[0].files, function(i, file){
			if (maxFiles > 0) {
				maxFiles = maxFiles - 1;

				var imgPath = file.name,
					extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
				if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
					if (typeof(FileReader) != "undefined") {

						if($label.attr('multiple') != 'multiple') {
							$label.hide(0);
						}

						var unique = new IDGenerator(),
							uniqueID = unique.generate();
					    var reader = new FileReader();
					    reader.onload = function(e) {
							var image = new Image();
							image.src = e.target.result;
							$label.before('<div class="piotnetforms-image-upload-placeholder piotnetforms-image-upload-uploading" style="background-image:url('+e.target.result+')" data-piotnetforms-image-upload-placeholder="'+ uniqueID +'"><input type="text" style="display:none;" data-piotnetforms-image-upload-item><span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--remove" data-piotnetforms-image-upload-button-remove><i class="fa fa-times" aria-hidden="true"></i></span><span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--uploading" data-piotnetforms-image-upload-button-uploading><i class="fa fa-spinner fa-spin"></i></span></div>');
					    }
					    reader.readAsDataURL(file);

						var formData = new FormData();
						formData.append( 'upload', file);

						$.ajax({
						    url: $('[data-piotnetforms-tinymce-upload]').data('piotnetforms-tinymce-upload'),
						    type: "POST",
						    data: formData,
						    processData: false,
						    contentType: false,
						    success: function (response) {
								var obj = JSON.parse(response);
								var imageItem = $(document).find('[data-piotnetforms-image-upload-placeholder="' + uniqueID + '"]');
								if (imageItem.length == 1) {
									imageItem.removeClass('piotnetforms-image-upload-uploading').addClass('piotnetforms-image-upload-uploaded');
						    		imageItem.find('input').attr('value',obj.location);

						    		var imageUploadedURL = '';
						    		var $imageUploaded = $widget.find('[data-piotnetforms-image-upload-placeholder]:not(.piotnetforms-image-upload-delete) [data-piotnetforms-image-upload-item]');

									$imageUploaded.each(function(){
										imageUploadedURL += $(this).val() + ',';
									});

									imageUploadedURL = imageUploadedURL.replace(/.$/,"");

									$widget.find('[data-piotnetforms-id]').attr('value',imageUploadedURL);
									$widget.find('[data-piotnetforms-id]').val(imageUploadedURL); 
									$widget.find('[data-piotnetforms-id]').change(); 
								}
						    }
						});
					} else {
					  	alert("Your browser does not support");
					}
				}

				if (maxFiles==0) {
					$label.hide(0);
				}
			} else {
				$label.hide(0);
			}
		});
	});

	$(document).on('click','[data-piotnetforms-image-upload-button-remove]', function(){
		var $placeholder = $(this).closest('.piotnetforms-image-upload-placeholder');
		$placeholder.css({'display':'none'});
		$placeholder.addClass('piotnetforms-image-upload-delete');

		var $widget = $(this).closest('.piotnetforms-fields-wrapper');
		var $imageUploaded = $widget.find('[data-piotnetforms-image-upload-placeholder]:not(.piotnetforms-image-upload-delete) [data-piotnetforms-image-upload-item]');
		var imageUploadedURL = '';
		var $label = $widget.find('[data-piotnetforms-image-upload-label]');

		if ($imageUploaded.length == 0) {
			$label.show(0);
		}

		if($label.attr('multiple') == 'multiple') {
			$label.show(0);
		}

		$imageUploaded.each(function(){
			imageUploadedURL += $(this).val() + ',';
		});

		imageUploadedURL = imageUploadedURL.replace(/.$/,"");

		$widget.find('[data-piotnetforms-id]').attr('value',imageUploadedURL);
	});

});