let isLoaded = false;
jQuery(window).on('load', function(){
	isLoaded = true;
});
jQuery(document).ready(function($) {

	var pluginURL = $('[data-piotnetforms-plugin-url]').attr('data-piotnetforms-plugin-url');

    function initWidgetDate($scope, $) {

        var $elements = $scope.find('.piotnetforms-date-field');

		if (!$elements.length) {
			return;
		}

		var addDatePicker = function addDatePicker($element) {
			if ($($element).hasClass('piotnetforms-use-native') || $($element).hasClass('flatpickr-custom-options')) { 
				return;
			}

			var minDate = $($element).attr('min') ? flatpickr.parseDate($($element).attr('min'), "Y-m-d") : null,
				maxDate = $($element).attr('max') ? flatpickr.parseDate($($element).attr('max'), "Y-m-d") : null;

			var options = {
				minDate: minDate,
				maxDate: maxDate,
				dateFormat: $($element).attr('data-date-format') || null,
				defaultDate: $($element).attr('data-piotnetforms-value') || null,
				allowInput: true,
				animate: false,
				onReady: function(date) { 
					var day = parseInt( date[0] / (1000 * 60 * 60 * 24), 10);
					$($element).attr('data-piotnetforms-date-calculate', day);
				},
				onClose: function(date) { 
					var day = parseInt( date[0] / (1000 * 60 * 60 * 24), 10);
					$($element).attr('data-piotnetforms-date-calculate', day);
				}
			};

			if ($($element).data('piotnetforms-date-range') != undefined) {

				var options = {
					minDate: minDate,
					maxDate: maxDate,
					dateFormat: $($element).attr('data-date-format') || null,
					defaultDate: $($element).attr('data-piotnetforms-value') || null,
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

						$($element).attr('data-piotnetforms-date-range-days', countDays);
					}
				};

				options['mode'] = 'range';
			}

			if ($($element).data('piotnetforms-date-language') != 'english') { 
				options['locale'] = $($element).attr('data-piotnetforms-date-language');
			}

			$element.flatpickr(options); 
		};

		$.each($elements, function (i, $element) {
			addDatePicker($element);
		});

    };

    function initWidgetTime($scope, $) {

	    var $elements = $scope.find('.piotnetforms-time-field');
	    var minute_increment = $elements.attr('data-time-minute-increment');

		if (!$elements.length) {
			return;
		}

		var addTimePicker = function addTimePicker($element) {
			if ($($element).hasClass('piotnetforms-use-native')) {
				return;
			}

			var time_24hr = false;

			if ($($element).attr('data-piotnetforms-time-24hr') != undefined) {
				time_24hr = true;
			}

			$element.flatpickr({
				noCalendar: true,
				enableTime: true,
				allowInput: true,
				minuteIncrement: minute_increment,
				time_24hr: time_24hr,
				dateFormat: $($element).attr('data-time-format') || null,
				defaultDate: $($element).attr('data-piotnetforms-value') || null,
			});
		};
		$.each($elements, function (i, $element) {
			addTimePicker($element);
		});

	};

	$(document).on('piotnet-widget-init-Piotnetforms_Field', '[data-piotnet-editor-widgets-item-root]', function(){
		initWidgetDate($(this), $);
		initWidgetTime($(this), $);
	});

	if (isLoaded) {
		setTimeout(function () {
			initWidgetDate($('[data-piotnet-widget-preview], #piotnetforms'), $);
			initWidgetTime($('[data-piotnet-widget-preview], #piotnetforms'), $);
		}, 2000);
	} else {
		$(window).on('load', function(){
			initWidgetDate($('[data-piotnet-widget-preview], #piotnetforms'), $);
			initWidgetTime($('[data-piotnet-widget-preview], #piotnetforms'), $);
		});
	}
});