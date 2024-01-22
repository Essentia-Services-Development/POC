import $ from 'jquery';
import { observer, modules } from 'peepso';

function setLinkTarget(html) {
	let $wrapper = $('<div/>').append(html),
		$links;

	// Only filter post's content.
	if ($wrapper.find('.ps-js-post-body').length) {
		$links = $wrapper.find('.ps-js-post-body a[href]');
	}
	// Only filter comment's content.
	else if ($wrapper.find('.ps-js-comment-body').length) {
		$links = $wrapper
			.find('.ps-js-comment-content a[href]')
			.add($wrapper.find('.ps-js-comment-attachment a[href]'));
	}
	// Filter all content for arbitrary html.
	else {
		$links = $wrapper.find('a[href]');
	}

	$links.each(function () {
		let $link = $(this),
			target = modules.url.getTarget($link.attr('href'), false);

		if (!target) {
			$link.removeAttr('target');
		} else {
			$link.attr('target', target);
		}
	});

	return $wrapper.html();
}

function init() {
	// Initialize on each activity item added.
	observer.addFilter('peepso_activity_content', html => setLinkTarget(html), 10, 1);
}

export default { init, setLinkTarget };
