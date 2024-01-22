mailster = (function (mailster, $, window, document) {
	'use strict';

	var exportstatus = $('.status');

	mailster.events.push('documentReady', update_export_count);
	mailster.events.push('documentReady', function () {
		$.fn.sortable &&
			$('.export-order')
				.sortable({
					connectWith: '.export-order',
					_placeholder: 'ui-state-highlight',
					containment: '.export-order-wrap',
					receive: function (event, ui) {
						ui.item
							.find('input')
							.prop(
								'checked',
								ui.item.closest('.export-order').is('.selected')
							);
					},
				})
				.on('change', 'input', function () {
					var _this = $(this);
					_this
						.parent()
						.appendTo(
							_this.is(':checked')
								? $('.export-order.selected')
								: $('.export-order.unselected')
						);
				});
	});

	mailster.$.document
		.on(
			'change',
			'#export-subscribers input,#export-subscribers select',
			update_export_count
		)
		.on('click', '.export-order-add', function () {
			$('.export-order.unselected')
				.find('li')
				.appendTo('.export-order.selected')
				.find('input')
				.prop('checked', true);
			return false;
		})
		.on('click', '.export-order-remove', function () {
			$('.export-order.selected')
				.find('li')
				.appendTo('.export-order.unselected')
				.find('input')
				.prop('checked', false);
			return false;
		})
		.on('change', 'select[name="outputformat"]', function () {
			$('#csv-separator')[$(this).val() == 'csv' ? 'show' : 'hide']();
		})
		.on('submit', '#export-subscribers', function () {
			var data = $(this).serialize();

			mailster.util.ajax(
				'export_contacts',
				{
					data: data,
				},
				function (response) {
					if (response.success) {
						window.onbeforeunload = function () {
							return mailster.l10n.manage.onbeforeunloadexport;
						};

						var limit = $('.performance').val();
						exportstatus.addClass('progress');
						do_export(0, limit, response.data.count, data);
					} else {
						alert(response.data.msg);
					}
				},
				function (jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				}
			);
			return false;
		});

	function do_export(offset, limit, count, data) {
		var t = new Date().getTime(),
			percentage = Math.min(1, (limit * offset) / count) * 100;

		exportstatus.html(
			mailster.util.sprintf(
				mailster.l10n.manage.prepare_download,
				count,
				''
			)
		);

		mailster.util.ajax(
			'do_export',
			{
				limit: limit,
				offset: offset,
				data: data,
			},
			function (response) {
				var finished = percentage >= 100 && response.data.finished;

				if (response.success) {
					if (!finished) do_export(offset + 1, limit, count, data);

					exportstatus.html(
						mailster.util.sprintf(
							mailster.l10n.manage.prepare_download,
							count,
							Math.ceil(percentage) + '%'
						)
					);

					if (finished) {
						window.onbeforeunload = null;

						exportstatus.html(mailster.l10n.manage.export_finished);

						exportstatus.html(
							mailster.util.sprintf(
								mailster.l10n.manage.downloading,
								count
							)
						);
						if (response.data.filename) {
							setTimeout(function () {
								exportstatus.removeClass('progress');
								document.location = response.data.filename;
							}, 2000);
						}
					} else {
						exportstatus.html(
							mailster.util.sprintf(
								mailster.l10n.manage.write_file,
								response.data.total,
								Math.ceil(percentage) + '%'
							)
						);
					}
				} else {
					window.onbeforeunload = null;
					exportstatus.html(mailster.l10n.manage.error_export);
					alert(response.data.msg);
				}
			},
			function (jqXHR, textStatus, errorThrown) {}
		);
	}

	function update_export_count() {
		setTimeout(function () {
			var data = $('#export-subscribers').serialize();
			$('#export-subscriber-button').prop('disabled', true);

			mailster.util.ajax(
				'get_subscriber_count',
				{
					data: data,
				},
				function (response) {
					if (response.success) {
						$('#export-subscriber-button')
							.val(
								mailster.util.sprintf(
									mailster.l10n.manage.export_n_subscribers,
									response.data.count_formated
								)
							)
							.prop('disabled', !response.data.count);
					}
				}
			);
		}, 10);
	}
	return mailster;
})(mailster || {}, jQuery, window, document);
