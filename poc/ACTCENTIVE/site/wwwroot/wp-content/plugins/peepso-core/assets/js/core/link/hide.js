import $ from 'jquery';
import { throttle } from 'underscore';
import { observer } from 'peepso';
import peepsodata from 'peepsodata';

const SITE_URL = peepsodata.site_url;
const HIDE_URL = +peepsodata.hide_url_only;

// Hide single links after they are added to the activity stream.
const hideUrlDebounce = throttle(() => {
	let $links = $('.ps-js-hide-url').not('[data-hidden]');

	$links.each(function () {
		let $link = $(this),
			$content = $link.closest('.ps-js-comment-content, .ps-js-activity-content'),
			$attachment = $content.siblings('.js-stream-attachments, .ps-comment-media');

		// Hide only if the attachment is not removed.
		if ($attachment.html().trim()) {
			$link.hide().attr('data-hidden', '1');
		}
	});
}, 500);

// Hide link if the post does not contain any other text.
const hideUrl = html => {
	let $tmp = $('<div>').append(html),
		$content;

	// Detect the post content.
	if ($tmp.find('.ps-js-activity').length) {
		$content = $tmp.find('.ps-js-activity-content');
	} else if ($tmp.find('.ps-js-comment-body').length) {
		$content = $tmp.find('.ps-js-comment-body').eq(0).find('.ps-js-comment-content');
	} else {
		$content = $tmp;
	}

	// Skip if content has more than one link.
	let $links = $content.find('a');
	if (1 !== $links.length) {
		return html;
	}

	// Skip if link href doesn't start with http/https.
	let href = $links.attr('href');
	if (!href.match(/^https?:\/\//i)) {
		return html;
	}

	// Skip non-media links pointed to the current site itself.
	if (0 === href.indexOf(SITE_URL) && !$links.hasClass('ps-media__link')) {
		return html;
	}

	// Finally, if conditions are met, add identifier class to the link for it to be
	// hidden later after html is added to the activity stream.
	let $placeholder = $('<a/>');
	$links.replaceWith($placeholder);
	if (!$content.text().trim()) {
		$links.addClass('ps-js-hide-url');
		$placeholder.replaceWith($links);
		html = $tmp.html();
		setTimeout(hideUrlDebounce, 1);
	}

	return html;
};

$(function () {
	if (!HIDE_URL) {
		return;
	}

	observer.addFilter('peepso_activity_content', hideUrl, 5, 1);
});
