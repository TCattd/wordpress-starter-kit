import SignaturePad from 'signature_pad';

jQuery(document).ready(function($) {

    function initWidgetSignature($scope, $) {

        var $elements = $scope.find('[data-piotnetforms-signature] canvas');

		if (!$elements.length) {
			return;
		}

		$.each($elements, function (i, $element) {
			let cachedImage;

			const signaturePad = new SignaturePad($element, {
				'onEnd': function () {
					cachedImage = signaturePad.toDataURL("image/png");
				}
			});

			const $piotnetformsSignature = $($element).closest('[data-piotnetforms-signature]'),
				$clearButton = $piotnetformsSignature.find('[data-piotnetforms-signature-clear]'),
				$exportButton = $piotnetformsSignature.find('[data-piotnetforms-signature-export]');

			$clearButton.click(function(){
				signaturePad.clear();
				cachedImage = signaturePad.toDataURL("image/png");
			});

			$exportButton.click(function(){
				if (signaturePad.isEmpty()) {
					$piotnetformsSignature.find('.piotnetforms-field').val('');
				} else {
					var url = signaturePad.toDataURL();
					$piotnetformsSignature.find('.piotnetforms-field').val(url);
				}
			});

			function resizeCanvas() {
				const ratio = Math.max(window.devicePixelRatio || 1, 1);
				$element.width = $element.offsetWidth * ratio;
				$element.height = $element.offsetHeight * ratio;
				$element.getContext("2d").scale(ratio, ratio);
				signaturePad.fromDataURL(cachedImage);
			}

			window.addEventListener("resize", resizeCanvas);
		});
    }

    $(document).on('piotnet-widget-init-Piotnetforms_Field', '[data-piotnet-editor-widgets-item-root]', function(){
		initWidgetSignature($(this), $);
	});

	$(window).on('load', function(){
		initWidgetSignature($('[data-piotnet-widget-preview], #piotnetforms'), $);
	});

});
