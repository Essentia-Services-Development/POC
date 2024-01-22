(function ($) {
	var datepickerConfig = {},
		defaultOptions = {},
		dayNamesMin;

	// Read datepicker configuration.
	if (window.peepsodatepickerdata && peepsodatepickerdata.config) {
		datepickerConfig = peepsodatepickerdata.config;
		dayNamesMin = _.map(datepickerConfig.daysShort, function (str) {
			return str.replace(/^([a-z]{2}).+$/i, '$1');
		});

		_.extend(defaultOptions, {
			monthNames: datepickerConfig.months,
			monthNamesShort: datepickerConfig.monthsShort,
			dayNames: datepickerConfig.days,
			dayNamesShort: datepickerConfig.daysShort,
			dayNamesMin: dayNamesMin,
			currentText: datepickerConfig.today,
			dateFormat: datepickerConfig.format,
			isRTL: datepickerConfig.rtl,
			showButtonPanel: true,
			currentText: datepickerConfig.today,
			closeText: datepickerConfig.apply,
			changeMonth: true,
			changeYear: true,
			yearRange: '-100:+0',
			firstDay: 0
		});
	}

	// Attach accessibility improvement on show datepicker.
	_.extend(defaultOptions, {
		beforeShow: function (input, inst) {
			var zIndex = 0;
			if (window.getComputedStyle) {
				var $prev = inst.dpDiv.prev();
				zIndex = getComputedStyle($prev[0]).getPropertyValue('z-index');
				zIndex = parseInt(zIndex);
			}

			var html = [
				'<div class="peepso-datepicker"',
				zIndex ? ' style="z-index: ' + (zIndex + 1) + '"' : '',
				' />'
			].join('');

			inst.dpDiv.wrap(html);
			setTimeout(function () {
				var $wrapper = inst.dpDiv.closest('.peepso-datepicker'),
					cssPosition = inst.dpDiv.css('position');

				if ('fixed' === cssPosition) {
					$wrapper.css('position', cssPosition);
				}

				$wrapper.on('click', function (e) {
					e.stopPropagation();
				});
			}, 1);
		},
		beforeShowDay: function () {
			var inst = $(this).data('datepicker');
			attachAccessibilityImprovement(inst);
			return [true, ''];
		},
		onClose: function (dateText, inst) {
			removeAccessibilityImprovement(inst);
			_.delay(function () {
				inst.dpDiv.unwrap('.peepso-datepicker');
			}, 500);
		}
	});

	// Cache jQuery datepicker in case of override.
	$.jQueryDatepicker = $.datepicker;
	$.fn.jQueryDatepicker = $.fn.datepicker;

	if (window.peepso_datepicker_noconflict) {
		$.fn.datepicker = window.peepso_datepicker_noconflict;
		window.peepso_datepicker_noconflict = undefined;
	}

	$.fn.psDatepicker = function (options) {
		return this.each(function () {
			var $input = $(this),
				$btn = $input.next('button'),
				evtName = 'click.psDatepicker';

			$btn.off(evtName).on(evtName, function (e) {
				var inst = $input.data('datepicker'),
					$div = inst.dpDiv;

				e.preventDefault();
				e.stopPropagation();

				if ($div.is(':visible')) {
					$input.jQueryDatepicker('hide');
				} else {
					$input.jQueryDatepicker('show');
				}
			});

			options = _.extend({}, defaultOptions, options || {});
			return $input.jQueryDatepicker(options);
		});
	};

	var attachAccessibilityImprovement = _.debounce(function (inst) {
		var $div = inst.dpDiv,
			today;

		attachEvents(inst);
		renderLabel(inst);

		today = $div.find('.ui-state-highlight')[0];
		if (!today) {
			today =
				$div.find('.ui-state-active')[0] ||
				$div.find('.ui-datepicker-today a')[0] ||
				$div.find('.ui-state-default')[0];
			$(today).addClass('ui-state-highlight');
		}

		today.focus();
	}, 100);

	function removeAccessibilityImprovement(inst) {
		detachEvents(inst);
	}

	function attachEvents(inst) {
		var $div = inst.dpDiv,
			$prev = $div.find('.ui-datepicker-prev'),
			$next = $div.find('.ui-datepicker-next'),
			$month = $div.find('.ui-datepicker-month'),
			$year = $div.find('.ui-datepicker-year'),
			$apply = $div.find('.ui-datepicker-close');

		detachEvents(inst);

		$div.on('keydown.ps-datepicker', { inst: inst }, keyboardHandler);
		$prev.on('click.ps-datepicker', { inst: inst }, changeMonthHandler);
		$next.on('click.ps-datepicker', { inst: inst }, changeMonthHandler);
		$month
			.on('change.ps-datepicker', { inst: inst }, changeMonthHandler)
			.on('click.ps-datepicker', { inst: inst }, function (e) {
				e.stopPropagation();
			});
		$year
			.on('change.ps-datepicker', { inst: inst }, changeMonthHandler)
			.on('click.ps-datepicker', { inst: inst }, function (e) {
				e.stopPropagation();
			});
		$apply.on('click.ps-datepicker', { inst: inst }, applyHandler);
	}

	function detachEvents(inst) {
		var $div = inst.dpDiv,
			$prev = $div.find('.ui-datepicker-prev'),
			$next = $div.find('.ui-datepicker-next'),
			$month = $div.find('.ui-datepicker-month'),
			$year = $div.find('.ui-datepicker-year'),
			$apply = $div.find('.ui-datepicker-close');

		$div.off('keydown.ps-datepicker');
		$prev.off('click.ps-datepicker');
		$next.off('click.ps-datepicker');
		$month.off('change.ps-datepicker').off('click.ps-datepicker');
		$year.off('change.ps-datepicker').off('click.ps-datepicker');
		$apply.off('click.ps-datepicker');
	}

	function keyboardHandler(e) {
		var inst = e.data.inst,
			target = e.target,
			which = e.which,
			altKey = e.altKey;

		if (which === 37) {
			// LEFT
			e.preventDefault();
			e.stopPropagation();
			goPreviousDay(inst, target);
		} else if (which === 36) {
			// HOME
			e.preventDefault();
			e.stopPropagation();
			goFirstWeekDay(inst, target);
		} else if (which === 39) {
			// RIGHT
			e.preventDefault();
			e.stopPropagation();
			goNextDay(inst, target);
		} else if (which === 35) {
			// END
			e.preventDefault();
			e.stopPropagation();
			goLastWeekDay(inst, target);
		} else if (which === 38) {
			// UP
			e.preventDefault();
			e.stopPropagation();
			goPreviousWeek(inst, target);
		} else if (which === 40) {
			// DOWN
			e.preventDefault();
			e.stopPropagation();
			goNextWeek(inst, target);
		} else if (which === 33) {
			// PAGEUP
			e.preventDefault();
			e.stopPropagation();
			if (altKey) {
				goNextYear(inst, target);
			} else {
				goNextMonth(inst, target);
			}
		} else if (which === 34) {
			// PAGEDOWN
			e.preventDefault();
			e.stopPropagation();
			if (altKey) {
				goPreviousYear(inst, target);
			} else {
				goPreviousMonth(inst, target);
			}
		} else if (which === 13) {
			// ENTER
			selectDate(inst, target);
		} else if (which === 27) {
			// ESCAPE
			closeDatepicker(inst);
		}
	}

	function goPreviousDay(inst, target) {
		var $currCell = $(target).closest('td'),
			$prevCell,
			$newTarget;

		if ($currCell.length) {
			$prevCell = $currCell.prev('td');
			if (!$prevCell.length) {
				$prevCell = $currCell.closest('tr').prev('tr').find('td').last();
			}
		}

		if ($prevCell && $prevCell.length) {
			$newTarget = $prevCell.find('.ui-state-default');
		}

		if ($newTarget && $newTarget.length) {
			highlightDate(inst, $newTarget);
		} else {
			goPreviousMonth(inst, target, 1);
		}
	}

	function goNextDay(inst, target) {
		var $currCell = $(target).closest('td'),
			$nextCell,
			$newTarget;

		if ($currCell.length) {
			$nextCell = $currCell.next('td');
			if (!$nextCell.length) {
				$nextCell = $currCell.closest('tr').next('tr').find('td').first();
			}
		}

		if ($nextCell && $nextCell.length) {
			$newTarget = $nextCell.find('.ui-state-default');
		}

		if ($newTarget && $newTarget.length) {
			highlightDate(inst, $newTarget);
		} else {
			goNextMonth(inst, target, 1);
		}
	}

	function goFirstWeekDay(inst, target) {
		var $currRow = $(target).closest('tr'),
			$newTarget = $currRow.find('.ui-state-default').first();

		if ($newTarget && $newTarget.length) {
			highlightDate(inst, $newTarget);
		}
	}

	function goLastWeekDay(inst, target) {
		var $currRow = $(target).closest('tr'),
			$newTarget = $currRow.find('.ui-state-default').last();

		if ($newTarget && $newTarget.length) {
			highlightDate(inst, $newTarget);
		}
	}

	function goPreviousWeek(inst, target) {
		var $currCell = $(target).closest('td'),
			$currRow = $currCell.closest('tr'),
			$prevRow = $currRow.prev('tr'),
			$prevCell = $prevRow.find('td').eq($currCell.prevAll().length),
			$newTarget;

		if ($prevCell && $prevCell.length) {
			$newTarget = $prevCell.find('.ui-state-default');
		}

		if ($newTarget && $newTarget.length) {
			highlightDate(inst, $newTarget);
		} else {
			goPreviousMonth(inst, target, 7);
		}
	}

	function goNextWeek(inst, target) {
		var $currCell = $(target).closest('td'),
			$currRow = $currCell.closest('tr'),
			$nextRow = $currRow.next('tr'),
			$nextCell = $nextRow.find('td').eq($currCell.prevAll().length),
			$newTarget;

		if ($nextCell && $nextCell.length) {
			$newTarget = $nextCell.find('.ui-state-default');
		}

		if ($newTarget && $newTarget.length) {
			highlightDate(inst, $newTarget);
		} else {
			goNextMonth(inst, target, 7);
		}
	}

	function goPreviousMonth(inst, target, index) {
		var $div = inst.dpDiv,
			date = $(target).data('psDate'),
			id = '#' + inst.id.replace(/\\\\/g, '\\');

		if (typeof index === 'undefined') {
			date = new Date(inst.drawYear, inst.drawMonth, +date);
		} else if (typeof index === 'number') {
			date = new Date(inst.drawYear, inst.drawMonth, +date - index);
		}

		$.jQueryDatepicker._adjustDate(id, -1, 'M');

		_.defer(function () {
			var $target;

			renderLabel(inst);

			$target = $div.find('a.ui-state-default');
			$target = $target.filter('[data-ps-date=' + date.getDate() + ']');
			highlightDate(inst, $target);
		});
	}

	function goNextMonth(inst, target, index) {
		var $div = inst.dpDiv,
			date = $(target).data('psDate'),
			id = '#' + inst.id.replace(/\\\\/g, '\\');

		if (typeof index === 'undefined') {
			date = new Date(inst.drawYear, inst.drawMonth, +date);
		} else if (typeof index === 'number') {
			date = new Date(inst.drawYear, inst.drawMonth, +date + index);
		}

		$.jQueryDatepicker._adjustDate(id, 1, 'M');

		_.defer(function () {
			var $target;

			renderLabel(inst);

			$target = $div.find('a.ui-state-default');
			$target = $target.filter('[data-ps-date=' + date.getDate() + ']');
			highlightDate(inst, $target);
		});
	}

	function goPreviousYear(inst, target) {
		var $div = inst.dpDiv,
			date = $(target).data('psDate'),
			id = '#' + inst.id.replace(/\\\\/g, '\\');

		$.jQueryDatepicker._adjustDate(id, -12, 'M');

		_.defer(function () {
			var $target;

			renderLabel(inst);

			$target = $div.find('a.ui-state-default');
			$target = $target.filter('[data-ps-date=' + date + ']');
			highlightDate(inst, $target);
		});
	}

	function goNextYear(inst, target) {
		var $div = inst.dpDiv,
			date = $(target).data('psDate'),
			id = '#' + inst.id.replace(/\\\\/g, '\\');

		$.jQueryDatepicker._adjustDate(id, 12, 'M');

		_.defer(function () {
			var $target;

			renderLabel(inst);

			$target = $div.find('a.ui-state-default');
			$target = $target.filter('[data-ps-date=' + date + ']');
			highlightDate(inst, $target);
		});
	}

	function selectDate(inst, target) {
		$(target).click();
	}

	function closeDatepicker(inst) {
		var $input = inst.input;
		$input.jQueryDatepicker('hide');
	}

	function renderLabel(inst) {
		var $div = inst.dpDiv,
			$table = $div.find('.ui-datepicker-calendar'),
			settings = inst.settings,
			days = settings.dayNames,
			month = settings.monthNames[inst.drawMonth],
			year = inst.drawYear;

		$table.attr('role', 'application');
		$table
			.find('tbody a.ui-state-default')
			.attr('role', 'button')
			.each(function () {
				var $date = $(this),
					index = $date.closest('td').prevAll().length,
					date = $date.text().trim(),
					day = days[index],
					label;

				label = date + ', ' + day + ' ' + month + ' ' + year;
				$date.removeClass('ui-state-hover');
				$date.attr('aria-label', label);
				$date.attr('data-ps-date', date);
			});
	}

	function getCurrentDate(inst) {
		var $div = inst.dpDiv,
			$current = $div.find('.ui-state-highlight');

		return $current;
	}

	function highlightDate(inst, target) {
		var $div = inst.dpDiv,
			$current = getCurrentDate(inst),
			$target = $(target);

		_.defer(function () {
			$current.removeClass('ui-state-highlight');
			$target.focus();
			$target.addClass('ui-state-highlight');
		});
	}

	function changeMonthHandler(e) {
		var inst = e.data.inst,
			date = new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay),
			$input = inst.input;

		e.stopPropagation();

		_.delay(function () {
			$input.jQueryDatepicker('setDate', date);
		}, 100);
	}

	function applyHandler(e) {
		var inst = e.data.inst;

		selectDate(inst, getCurrentDate(inst));
	}
})(jQuery);
