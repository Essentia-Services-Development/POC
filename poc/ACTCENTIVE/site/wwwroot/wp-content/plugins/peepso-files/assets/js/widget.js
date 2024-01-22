import $ from 'jquery';
import { ajax, template } from 'peepso';
import { rest_url as REST_URL } from 'peepsodata';

$(function () {
	function initWidget(container, context) {
		let $container = $(container),
			$content = $container.find('.ps-js-widget-content'),
			itemTemplate = template($container.find('[data-name="item-template"]').text().trim()),
			limit = +$container.data('limit'),
			params = { limit, context };

		ajax.get(`${REST_URL}files`, params).done(json => {
			if (json.files instanceof Array && json.files.length) {
				let html = json.files.map(file => itemTemplate(file)).join('');

				$content.html(html);
			} else {
				$content.empty();
			}
		});
	}

	let $widgets = $('.ps-js-widget-my-files, .ps-js-widget-latest-files');
	if ($widgets.length) {
		$widgets.each((index, widget) => {
			let context =
				widget.className.indexOf('ps-js-widget-my-files') > -1
					? 'files_widget'
					: 'community_files_widget';

			initWidget(widget, context);
		});
	}
});
