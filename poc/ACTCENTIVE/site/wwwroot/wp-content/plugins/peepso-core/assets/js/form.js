(function ($) {
	// privacy dropdown
	$(document).on('click', '.ps-privacy-dropdown ul li a', function () {
		var $a = $(this).closest('a'),
			$menu = $a.closest('ul'),
			$input = $menu.siblings('input'),
			$btn = $menu.siblings('.ps-btn,.ps-js-dropdown-toggle'),
			$icon = $btn.find('i'),
			$label = $btn.find('.ps-privacy-title');

		$input.val($a.attr('data-option-value'));
		$icon.attr('class', $a.find('i').attr('class'));
		$label.html($a.find('span').html());
		$menu.css('display', 'none');
	});

	var duplicatedCounter = 0;

	// init datepicker
	function initDatepicker($dp) {
		if (!$dp) {
			return;
		}

		$dp.each(function () {
			// Fix datepiicker issue due to a duplicated element.
			if (this.id && this !== document.getElementById(this.id)) {
				this.id = this.id + '_dupl' + ++duplicatedCounter;
			}

			var $input = $(this),
				value = $input.data('value'),
				yearMin = String($input.data('dateRangeMin')),
				yearMax = String($input.data('dateRangeMax')),
				yearCurrent = new Date().getFullYear(),
				yearRange,
				minDate,
				maxDate,
				defaultDate,
				date;

			// Fallback to an ordinary input text if the datepicker library is not available.
			if (!$.fn.psDatepicker) {
				$input.removeAttr('readonly');
				$input.val($input.data('value'));
				$input.on('blur', function () {
					var matches = this.value.match(/^\d{4}-\d{1,2}-\d{1,2}/),
						sanitized = (matches && matches[0]) || '';

					this.value = sanitized;
					$input.data('value', sanitized);
				});
				return true;
			}

			/**
			 * Since version 1.10.4, plus (+) or minus (-) sign is explicitly
			 * added to indicate year range relative to current year.
			 *
			 * @since 1.10.4
			 */
			yearMin = yearMin.match(/^[-+]\d+$/) ? +yearMin : -yearMin;
			yearMax = +yearMax;
			yearRange = _.map([yearMin, yearMax], function (year) {
				if (year === -999) {
					return 'c-100';
				} else if (year === 999) {
					return 'c+100';
				} else {
					year = Math.min(Math.max(year, -100), 100);
					if (year < 0) {
						return '' + year;
					} else {
						return '+' + year;
					}
				}
			}).join(':');

			// Make sure minimum date respects year range.
			minDate = new Date();
			minDate.setFullYear(yearCurrent + yearMin);

			// Make sure maximum date respects year range.
			maxDate = new Date();
			maxDate.setFullYear(yearCurrent + yearMax);

			// Make sure default date respects year range.
			defaultDate = new Date();
			if (yearMin > 0) {
				defaultDate.setFullYear(yearCurrent + yearMin);
			} else if (yearMax < 0) {
				defaultDate.setFullYear(yearCurrent + yearMax);
			}

			$input.psDatepicker({
				yearRange: yearRange,
				minDate: minDate,
				maxDate: maxDate,
				defaultDate: defaultDate,
				onSelect: function (dateText, inst) {
					var $input = $(this),
						date = $input.datepicker('getDate'),
						value = [];

					if (date) {
						value.push(date.getFullYear());
						value.push(date.getMonth() + 1);
						value.push(date.getDate());

						// Add zero padding.
						value[1] = (value[1] < 10 ? '0' : '') + value[1];
						value[2] = (value[2] < 10 ? '0' : '') + value[2];
					}

					$input.data('value', value.join('-'));
					$input.trigger('input');
				}
			});

			if (value) {
				value = value.split('-');
				date = new Date(+value[0], +value[1] - 1, +value[2]);
				$input.psDatepicker('setDate', date);
			}
		});

		$dp.addClass('datepickerInitialized');
	}

	ps_datepicker = {
		init: initDatepicker
	};

	$(function () {
		initDatepicker($('#peepso-wrap .datepicker').not('.datepickerInitialized'));
	});

	// Initialize password preview button.
	$(function () {
		function toggle(button, enable) {
			var $button = $(button),
				$input = $button.siblings('input[type=password], input[type=text]'),
				classOn = 'gci-eye',
				classOff = 'gci-eye-slash';

			if ('undefined' === typeof enable) {
				enable = 'password' === $input.attr('type');
			}

			if (enable) {
				$input.attr('type', 'text');
				$button.removeClass(classOn).addClass(classOff);
			} else {
				$input.attr('type', 'password');
				$button.removeClass(classOff).addClass(classOn);
			}
		}

		let initCounter = 0;
		function init() {
			$('input.ps-js-password-preview').each(function (index) {
				var $input = $(this).removeClass('ps-js-password-preview'),
					buttonClass = `ps-js-password-preview-btn-${initCounter}-${index}`,
					$button = $(`<i class="ps-password-preview ${buttonClass} gcis" />`);

				$button.insertAfter($input);
				toggle($button, false);
				$(document).on('click', `.${buttonClass}`, function () {
					toggle(this);
				});
			});

			initCounter++;
		}

		init();

		// Add action hook for on-the-fly initialization.
		if ('object' === typeof peepso && peepso.hooks) {
			peepso.hooks.addAction('init_password_preview', 'form', init);
		}
	});
})(jQuery);
