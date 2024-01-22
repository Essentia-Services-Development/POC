mailster = (function (mailster, $, window, document) {
	'use strict';

	var deletestatus = $('.status');

	mailster.events.push('documentReady', update_delete_count);

	mailster.$.document
		.on(
			'change',
			'#delete-subscribers input,#delete-subscribers select',
			update_delete_count
		)
		.on('submit', '#delete-subscribers', function () {
			var input = prompt(mailster.l10n.manage.confirm_delete);

			if (!input) return false;

			if ('delete' == input.toLowerCase()) {
				var data = $('#delete-subscribers').serialize();

				deletestatus.addClass('progress spinner');

				mailster.util.ajax(
					'delete_contacts',
					{
						data: data,
					},
					function (response) {
						if (response.success) {
							deletestatus.html(response.data.msg);
						} else {
							deletestatus.html(response.data.msg);
						}
						deletestatus.removeClass('spinner');
						update_delete_count();
					},
					function (jqXHR, textStatus, errorThrown) {
						deletestatus.html(
							'[' + jqXHR.status + '] ' + errorThrown
						);
					}
				);
			}

			return false;
		})
		.on('click', '.remove-job', function () {
			if (!confirm(mailster.l10n.manage.confirm_job_delete)) return false;

			var job = $(this).closest('.manage-job');

			mailster.util.ajax(
				'delete_delete_job',
				{
					id: job.data('id'),
				},
				function (response) {
					if (response.success) {
						job.slideUp(function () {
							job.remove();
						});
					}
				},
				function (jqXHR, textStatus, errorThrown) {}
			);

			return false;
		})
		.on('click', '#schedule-delete-subscriber-button', function () {
			var data = $('#delete-subscribers').serialize();

			if (!/&lists%5B%5D/.test(data) && !/&nolists=1/.test(data)) {
				alert(mailster.l10n.manage.list_required);
				return false;
			}
			if (!/&status%5B%5D/.test(data)) {
				alert(mailster.l10n.manage.status_required);
				return false;
			}

			var name = prompt(
				mailster.l10n.manage.confirm_job,
				mailster.util.sprintf(
					mailster.l10n.manage.confirm_job_default,
					$('.manage-job').length + 1
				)
			);

			if (!name) return false;

			deletestatus.addClass('progress spinner');

			mailster.util.ajax(
				'delete_contacts',
				{
					schedule: true,
					name: name,
					data: data,
				},
				function (response) {
					deletestatus.removeClass('spinner');
					if (response.success) {
						deletestatus.html(response.data.msg);
						window.location.reload();
					} else {
						deletestatus.html(response.data.msg);
					}
				},
				function (jqXHR, textStatus, errorThrown) {
					deletestatus.html('[' + jqXHR.status + '] ' + errorThrown);
				}
			);

			return false;
		});

	function update_delete_count() {
		setTimeout(function () {
			var data = $('#delete-subscribers').serialize();
			$('#delete-subscriber-button').prop('disabled', true);

			mailster.util.ajax(
				'get_subscriber_count',
				{
					data: data,
				},
				function (response) {
					if (response.success) {
						$('#delete-subscriber-button')
							.val(
								mailster.util.sprintf(
									mailster.l10n.manage.delete_n_subscribers,
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
