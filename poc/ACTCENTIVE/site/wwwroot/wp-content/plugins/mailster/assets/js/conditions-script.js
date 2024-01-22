mailster = (function (mailster, $, window, document) {
	'use strict';

	mailster.conditions = mailster.conditions || {};

	$.each($('.mailster-conditions'), function () {
		var _self = $(this),
			conditions = _self.find('.mailster-conditions-wrap'),
			groups = _self.find('.mailster-condition-group'),
			cond = _self.find('.mailster-condition');

		groups.eq(0).appendTo(_self.find('.mailster-condition-container'));

		!mailster.util.trim(conditions.html()) && conditions.empty();

		datepicker();

		_self
			.on('click', '.add-condition', function () {
				var id = groups.length,
					clone = groups.eq(0).clone();

				clone
					.removeAttr('id')
					.appendTo(conditions)
					.data('id', id)
					.show();
				$.each(clone.find('input, select'), function () {
					var _this = $(this),
						name = _this.attr('name');
					name &&
						_this
							.attr(
								'name',
								name.replace(/\[\d+\]/, '[' + id + ']')
							)
							.prop('disabled', false);
				});
				clone.find('.condition-field').val('').focus();
				datepicker();
				groups = _self.find('.mailster-condition-group');
				cond = _self.find('.mailster-condition');
			})
			.on('click', '.add-or-condition', function () {
				var cont = $(this).parent(),
					id = cont.find('.mailster-condition').last().data('id'),
					clone = cond.eq(0).clone();

				clone
					.removeAttr('id')
					.appendTo(cont)
					.data('id', ++id);
				$.each(clone.find('input, select'), function () {
					var _this = $(this),
						name = _this.attr('name');
					name &&
						_this
							.attr(
								'name',
								name.replace(
									/\[\d+\]\[\d+\]/,
									'[' + cont.data('id') + '][' + id + ']'
								)
							)
							.prop('disabled', false);
				});
				clone.find('.condition-field').val('').focus();
				datepicker();
				cond = _self.find('.mailster-condition');
			});

		conditions
			.on('click', '.remove-condition', function () {
				var c = $(this).parent();
				if (c.parent().find('.mailster-condition').length == 1) {
					c = c.parent();
				}
				c.slideUp(100, function () {
					$(this).remove();
					mailster.trigger('updateCount');
				});
			})
			.on('change', '.condition-field', function () {
				var condition = $(this).closest('.mailster-condition'),
					field = $(this).val(),
					operator_field,
					value_field;

				condition
					.find('div.mailster-conditions-value-field')
					.removeClass('active')
					.find('.condition-value')
					.prop('disabled', true);
				condition
					.find('div.mailster-conditions-operator-field')
					.removeClass('active')
					.find('.condition-operator')
					.prop('disabled', true);

				value_field = condition
					.find(
						'div.mailster-conditions-value-field[data-fields*=",' +
							field +
							',"]'
					)
					.addClass('active')
					.find('.condition-value')
					.prop('disabled', false);
				operator_field = condition
					.find(
						'div.mailster-conditions-operator-field[data-fields*=",' +
							field +
							',"]'
					)
					.addClass('active')
					.find('.condition-operator')
					.prop('disabled', false);

				if (!value_field.length) {
					value_field = condition
						.find('div.mailster-conditions-value-field-default')
						.addClass('active')
						.find('.condition-value')
						.prop('disabled', false);
				}
				if (!operator_field.length) {
					operator_field = condition
						.find('div.mailster-conditions-operator-field-default')
						.addClass('active')
						.find('.condition-operator')
						.prop('disabled', false);
				}

				if (!value_field.val()) {
					if (value_field.is('.hasDatepicker')) {
						value_field.datepicker('setDate', 'yy-mm-dd');
					}
				}

				mailster.trigger('updateCount');
			})
			.on('change', '.relative-datepicker', function () {
				var field = $(this).closest('.mailster-conditions-value-field');
				var count = field.find('input.relative-datepicker').val() * -1;
				var multi = field.find('select.relative-datepicker').val();
				field.find('input.datepicker').val(count + ' ' + multi);
				mailster.trigger('updateCount');
			})
			.on('change', '.condition-operator', function () {
				mailster.trigger('updateCount');
			})
			.on('change', '.condition-operator-time', function () {
				var operator = $(this).val(),
					is_relative = /(is_older|is_younger)/.test(operator),
					input = $(this)
						.closest('.mailster-condition')
						.toggleClass('is-relative', is_relative)
						.find('.mailster-conditions-value-field.active')
						.find('.condition-value'),
					val = input.val();

				if (is_relative) {
					var values = get_relative_values(val);
					input
						.next('input.relative-datepicker')
						.val(values[0])
						.next('select.relative-datepicker')
						.val(values[1])
						.trigger('change');
				} else {
					if (!val.match(/^(\d{4})-(\d{2})-(\d{2})$/))
						input.val(new Date().toISOString().slice(0, 10));
				}
			})
			.on('change', '.condition-value', function () {
				mailster.trigger('updateCount');
			})
			.on('click', '.mailster-condition-add-multiselect', function () {
				$(this)
					.parent()
					.clone()
					.insertAfter($(this).parent())
					.find('.condition-value')
					.select()
					.focus();
				return false;
			})
			.on('click', '.mailster-condition-remove-multiselect', function () {
				$(this).parent().remove();
				mailster.trigger('updateCount');
				return false;
			})
			.on(
				'change',
				'.mailster-conditions-value-field-multiselect > .condition-value',
				function () {
					if (
						0 == $(this).val() &&
						$(this)
							.parent()
							.parent()
							.find('.condition-value')
							.size() > 1
					)
						$(this).parent().remove();
				}
			)
			.on('click', '.mailster-rating > span', function (event) {
				var _this = $(this),
					_prev = _this.prevAll(),
					_all = _this.siblings();
				_all.removeClass('enabled');
				_prev.add(_this).addClass('enabled');
				_this
					.parent()
					.parent()
					.find('.condition-value')
					.val((_prev.length + 1) / 5)
					.trigger('change');
			})
			.find('.condition-field')
			.prop('disabled', false)
			.trigger('change');

		mailster.trigger('updateCount');

		conditions.find('.is-relative').each(function () {
			var values = get_relative_values(
				$(this)
					.find(
						'.mailster-conditions-value-field.active .condition-value'
					)
					.val()
			);
			$(this)
				.find('input.relative-datepicker')
				.val(values[0])
				.next('select.relative-datepicker')
				.val(values[1]);
		});

		function datepicker() {
			conditions.find('.datepicker').datepicker({
				dateFormat: 'yy-mm-dd',
				firstDay: mailster.l10n.conditions.start_of_week,
				showWeek: true,
				dayNames: mailster.l10n.conditions.day_names,
				dayNamesMin: mailster.l10n.conditions.day_names_min,
				monthNames: mailster.l10n.conditions.month_names,
				prevText: mailster.l10n.conditions.prev,
				nextText: mailster.l10n.conditions.next,
				showAnim: 'fadeIn',
			});
		}

		function get_conditions(args) {
			var lists = [],
				conditions = [],
				inputs = $('#list-checkboxes').find('input, select'),
				listinputs = $('#list-checkboxes').find('input.list'),
				extra = $('#list_extra'),
				data = {},
				groups = $(
					'.mailster-conditions-wrap > .mailster-condition-group'
				),
				i = 0;

			$.each(listinputs, function () {
				var id = $(this).val();
				if ($(this).is(':checked')) lists.push(id);
			});

			data.id = mailster.campaign_id;
			data.lists = lists;
			data.ignore_lists = $('#ignore_lists').is(':checked');

			$.each(groups, function () {
				var c = $(this).find('.mailster-condition');
				$.each(c, function () {
					var _this = $(this),
						value,
						field = _this.find('.condition-field').val(),
						operator = _this
							.find('.mailster-conditions-operator-field.active')
							.find('.condition-operator')
							.val();

					if (!operator || !field) return;

					value = _this
						.find('.mailster-conditions-value-field.active')
						.find('.condition-value')
						.map(function () {
							return $(this).val();
						})
						.toArray();
					if (value.length == 1) {
						value = value[0];
					}
					if (!conditions[i]) {
						conditions[i] = [];
					}

					conditions[i].push({
						field: field,
						operator: operator,
						value: value,
					});
				});
				i++;
			});

			data.operator = $('select.mailster-list-operator').val();
			data.conditions = conditions;

			if (args) {
				data = $.extend(args, data);
			}

			return data;
		}
	});

	function get_conditions(args) {
		var lists = [],
			conditions = [],
			inputs = $('#list-checkboxes').find('input, select'),
			listinputs = $('#list-checkboxes').find('input.list'),
			extra = $('#list_extra'),
			data = {},
			groups = $('.mailster-conditions-wrap > .mailster-condition-group'),
			i = 0;

		$.each(listinputs, function () {
			var id = $(this).val();
			if ($(this).is(':checked')) lists.push(id);
		});

		data.id = mailster.campaign_id;
		data.lists = lists;
		data.ignore_lists = $('#ignore_lists').is(':checked');

		$.each(groups, function () {
			var c = $(this).find('.mailster-condition');
			$.each(c, function () {
				var _this = $(this),
					value,
					field = _this.find('.condition-field').val(),
					operator = _this
						.find('.mailster-conditions-operator-field.active')
						.find('.condition-operator')
						.val();

				if (!operator || !field) return;

				value = _this
					.find('.mailster-conditions-value-field.active')
					.find('.condition-value')
					.map(function () {
						return $(this).val();
					})
					.toArray();
				if (value.length == 1) {
					value = value[0];
				}
				if (!conditions[i]) {
					conditions[i] = [];
				}

				conditions[i].push({
					field: field,
					operator: operator,
					value: value,
				});
			});
			i++;
		});

		data.operator = $('select.mailster-list-operator').val();
		data.conditions = conditions;

		if (args) {
			data = $.extend(args, data);
		}

		return data;
	}

	function serialize(prefix) {
		var params = get_conditions();

		return _map_object(params.conditions, prefix || 'conditions');
	}

	function _map_object(obj, prefix) {
		var str = [],
			p;
		for (p in obj) {
			if (obj.hasOwnProperty(p)) {
				var k = prefix ? prefix + '[' + p + ']' : p,
					v = obj[p];
				str.push(
					v !== null && typeof v === 'object'
						? _map_object(v, k)
						: encodeURIComponent(k) + '=' + encodeURIComponent(v)
				);
			}
		}
		return str.join('&');
	}

	function get_relative_values(val) {
		if (!isNaN(parseFloat(val)) && isFinite(val)) {
			return val;
		}
		var m;

		if ((m = val.match(/^(\d{4})-(\d{2})-(\d{2})$/))) {
			var midnight = new Date();
			midnight.setHours(0, 0, 0, 0);
			val = midnight.getTime() - new Date(m[1], m[2] - 1, m[3]).getTime();
			val = Math.abs(val) / 1000;
			val = Math.round(val);
			if (!val) {
				return [1, 'days'];
			}

			if (!((val / 2628000) % 1)) {
				val = [val / 2628000, 'months'];
			} else if (!((val / 604800) % 1)) {
				val = [val / 604800, 'weeks'];
			} else if (!((val / 86400) % 1)) {
				val = [val / 86400, 'days'];
			} else if (!((val / 3600) % 1)) {
				val = [val / 3600, 'hours'];
			} else if (!((val / 60) % 1)) {
				val = [Math.ceil(val / 60), 'minutes'];
			}
		} else {
			val = val.split(' ');
		}
		val[0] = Math.abs(val[0]);

		return val;
	}

	mailster.conditions.get = get_conditions;
	mailster.conditions.serialize = serialize;

	return mailster;
})(mailster || {}, jQuery, window, document);
