import $ from 'jquery';
import { ajax } from 'peepso';

$(function () {
	function initWidget(container) {
		let $container = $(container),
			$content = $container.find('.ps-js-widget-content'),
			hideEmpty = +$container.data('hideempty'),
			limit = +$container.data('limit'),
			totalmember = +$container.data('totalmember') ? 1 : 0,
			params = { limit, totalmember };

		ajax.get('widgetajax.latest_members', params).done(json => {
			if (json.success) {
				if (hideEmpty && +json.data.empty) {
					$content.empty();
					$container.parent('[class*="widget_"]').hide();
				} else {
					$content.html(json.data.html);
					$container.parent('[class*="widget_"]').show();
				}
			}
		});
	}

	let $widgets = $('.ps-js-widget-latest-members');
	if ($widgets.length) {
		$widgets.each((index, widget) => initWidget(widget));
	}
});
