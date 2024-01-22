mailster = (function (mailster, $, window, document) {
	'use strict';

	var bulk_update_info,
		form_submitted = false,
		count,
		per_page;

	mailster.$.document
		.on('click', '#filter, .mailster-condition-render-group', function () {
			mailster.util.tb_show(
				mailster.l10n.subscribers.filters,
				'#TB_inline?x=1&width=720&height=520&inlineId=mailster-subscriber-conditions',
				null
			);
			return false;
		})
		.on('click', '#apply-filter', function () {
			var query = mailster.conditions.serialize();
			var search = new URLSearchParams(window.location.search);
			var params = Object.fromEntries(search.entries());
			var filtererd = getFilteredParams(params, 'conditions');
			var queryString = Object.keys(filtererd)
				.map(function (key) {
					return key + '=' + filtererd[key];
				})
				.join('&');

			if (search.toString() != queryString + '&' + query) {
				window.location.search = queryString + '&' + query;
			}
			tb_remove();
		})
		.on('click', '#close-filter', tb_remove)
		.on('change', '#cb-select-all-1, #cb-select-all-2', function () {
			var $input = $('#all_subscribers'),
				label = $input.data('label'),
				subscriber_cb = $('.subscriber_cb');

			count = $input.data('count');
			per_page = subscriber_cb.length;
			if (
				$(this).is(':checked') &&
				count > $('#the-list').find('tr').length &&
				confirm(label)
			) {
				subscriber_cb.prop('disabled', true);
				$input.val(1);
			} else {
				$input.val(0);
				subscriber_cb.prop('disabled', false);
			}
		})
		.on('submit', '#subscribers-overview-form', function (event) {
			var $this = $(this),
				$input = $('#all_subscribers');

			if (1 == $input.val()) {
				event.preventDefault();

				if (form_submitted) return;
				form_submitted = true;

				window.onbeforeunload = function () {
					return mailster.l10n.subscribers.onbeforeunload;
				};

				bulk_update_info = $(
					'<div class="alignright bulk-update-info spinner">' +
						mailster.l10n.subscribers.initprogess +
						'</div>'
				).prependTo('.bulkactions');

				do_batch($this.serialize(), 0, function () {
					bulk_update_info.removeClass('spinner');
					window.onbeforeunload = null;
					setTimeout(function () {
						location.reload();
					}, 1000);
				});
			}
		});

	function getFilteredParams(params, filteredString) {
		var obj = {};
		for (var key in params) {
			if (key.indexOf(filteredString) == -1) {
				obj[key] = params[key];
			}
		}
		return obj;
	}

	function removeURLParameter(param, url) {
		url = url.split('?');
		var path = url.length == 1 ? '' : url[1];
		path = path.replace(
			new RegExp('&?' + param + '\\[\\d*\\]=[\\w]+', 'g'),
			''
		);
		path = path.replace(new RegExp('&?' + param + '=[\\w]+', 'g'), '');
		path = path.replace(/^&/, '');
		return url[0] + (path.length ? '?' + path : '');
	}

	function do_batch(data, page, cb) {
		if (!page) page = 0;

		$.post(
			location.href,
			{
				all_subscribers: true,
				post_data: data,
				page: page,
				per_page: per_page,
				count: count,
			},
			function (response) {
				bulk_update_info.html(response.data.message);
				if (response.success_message)
					mailster.log(response.success_message);
				if (response.data.error_message)
					mailster.log(response.data.error_message, 'error');

				if (!response.data.finished) {
					setTimeout(
						function () {
							do_batch(data, response.data.page, cb);
						},
						response.data.delay ? response.data.delay : 300
					);
				} else {
					cb && cb();
				}
			}
		);
	}

	return mailster;
})(mailster || {}, jQuery, window, document);
