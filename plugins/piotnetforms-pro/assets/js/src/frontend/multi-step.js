jQuery(document).ready(function( $ ) {
	function multiStepFormSignature( $selector ) {
		var $piotnetformsSingature = $selector.find('[data-piotnetforms-signature] canvas.not-resize');

		if ($piotnetformsSingature.length > 0) {
			$piotnetformsSingature.each(function(){
	    		var width = parseInt( $(this).css('max-width').replace('px','') ),
	    			height = parseInt( $(this).css('height').replace('px','') ),
	    			widthOuter = parseInt( $(this).closest('.piotnetforms-fields-wrapper').width() ); 

				if(widthOuter > 0) {
					if (width > widthOuter) {
						$(this).attr('width',widthOuter);
					} else {
						$(this).attr('width',width);
					}

					$(this).removeClass('not-resize');
				}
	    	});

		}
    }

	$(document).on('click','[data-piotnetforms-nav="next"]',function(){
		var formID = $(this).data('piotnetforms-nav-form-id'),
			$wrapper = $(this).closest('.piotnetforms-multi-step-form__content-item'),
    		$fields = $wrapper.find('[data-piotnetforms-id="'+ formID +'"]'),
    		requiredText = $(this).data('piotnetforms-required-text'), 
    		error = 0;

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

				if ( ( !$(this)[0].checkValidity() || checked == 0 && $checkboxRequired.length > 0 ) && $(this).closest('.piotnetforms-conditional-logic-hidden').length == 0 && $(this).closest('[data-piotnetforms-conditional-logic]').css('display') != 'none' && $(this).data('piotnetforms-honeypot') == undefined &&  $(this).closest('[data-piotnetforms-signature]').length == 0 || checked == 0 && $checkboxRequired.length > 0 && $(this).closest('.piotnetforms-element').css('display') != 'none') {
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

		if (error == 0) {
			$wrapper.removeClass('active');
			$wrapper.next().addClass('active');
			var index = $wrapper.next().index(),
				$progressbarItem = $(this).closest('.piotnetforms-multi-step-form').find('.piotnetforms-multi-step-form__progressbar-item');
			$progressbarItem.eq(index).addClass('active');

			var $scrollToTop = $(this).closest('[data-piotnetforms-multi-step-form-scroll-to-top]');

			if ($scrollToTop.length > 0) {
				var breakPointMd = $('[data-piotnetforms-break-point-md]').data('piotnetforms-break-point-md'),
					breakPointLg = $('[data-piotnetforms-break-point-lg]').data('piotnetforms-break-point-lg'),
					windowWidth = window.innerWidth;

				if( windowWidth >= breakPointLg ) {
					$('html, body').animate({
						scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-desktop') }
					, 300);
				}

				if( windowWidth >= breakPointMd && windowWidth < breakPointLg ) {
					$('html, body').animate({
						scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-tablet') }
					, 300);
				}

				if( windowWidth < breakPointMd ) {
					$('html, body').animate({
						scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-mobile') }
					, 300);
				}
			}

			multiStepFormSignature( $wrapper.next() );
		}
	});

	function nextMultiStepForm($this) {
		var $wrapper = $this.closest('.piotnetforms-multi-step-form__content-item'),
			formID = $wrapper.find('[data-piotnetforms-id]').data('piotnetforms-id');

		$wrapper.removeClass('active');
		$wrapper.next().addClass('active');
		var index = $wrapper.next().index(),
			$progressbarItem = $this.closest('.piotnetforms-multi-step-form').find('.piotnetforms-multi-step-form__progressbar-item');
		$progressbarItem.eq(index).addClass('active');

		var $scrollToTop = $this.closest('[data-piotnetforms-multi-step-form-scroll-to-top]');

		if ($scrollToTop.length > 0) {
			var breakPointMd = $('[data-piotnetforms-break-point-md]').data('piotnetforms-break-point-md'),
				breakPointLg = $('[data-piotnetforms-break-point-lg]').data('piotnetforms-break-point-lg'),
				windowWidth = window.innerWidth;

			if( windowWidth >= breakPointLg ) {
				$('html, body').animate({
					scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-desktop') }
				, 300);
			}

			if( windowWidth >= breakPointMd && windowWidth < breakPointLg ) {
				$('html, body').animate({
					scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-tablet') }
				, 300);
			}

			if( windowWidth < breakPointMd ) {
				$('html, body').animate({
					scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-mobile') }
				, 300);
			}
		}

		multiStepFormSignature( $wrapper.next() );
	}

	$(document).on('change','[data-piotnetforms-multi-step-form-autonext]',function(){
		nextMultiStepForm($(this));
	});

	$(document).on('click','.image_picker_selector>li>div',function(){
		if ($(this).closest('.piotnetforms-field').find('[data-piotnetforms-multi-step-form-autonext]').length > 0) {
			nextMultiStepForm($(this));
		}
	});

	$(document).on('click','[data-piotnetforms-nav="prev"]',function(){
		var formID = $(this).data('piotnetforms-nav-form-id'),
			$wrapper = $(this).closest('.piotnetforms-multi-step-form__content-item');

		$wrapper.removeClass('active');
		$wrapper.prev().addClass('active');
		var index = $wrapper.index(),
			$progressbarItem = $(this).closest('.piotnetforms-multi-step-form').find('.piotnetforms-multi-step-form__progressbar-item');
		$progressbarItem.eq(index).removeClass('active');

		var $scrollToTop = $(this).closest('[data-piotnetforms-multi-step-form-scroll-to-top]');

		if ($scrollToTop.length > 0) {
			var breakPointMd = $('[data-piotnetforms-break-point-md]').data('piotnetforms-break-point-md'),
				breakPointLg = $('[data-piotnetforms-break-point-lg]').data('piotnetforms-break-point-lg'),
				windowWidth = window.innerWidth;

			if( windowWidth >= breakPointLg ) {
				$('html, body').animate({
					scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-desktop') }
				, 300);
			}

			if( windowWidth >= breakPointMd && windowWidth < breakPointLg ) {
				$('html, body').animate({
					scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-tablet') }
				, 300);
			}

			if( windowWidth < breakPointMd ) {
				$('html, body').animate({
					scrollTop: $scrollToTop.offset().top - $scrollToTop.data('piotnetforms-multi-step-form-scroll-to-top-offset-mobile') }
				, 300);
			}
		}
	});
});