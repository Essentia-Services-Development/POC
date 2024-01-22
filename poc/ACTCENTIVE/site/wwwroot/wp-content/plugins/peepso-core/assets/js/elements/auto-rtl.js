import $ from 'jquery';
import { observer, hooks, util } from 'peepso';

function scanElement(element) {
	let $element = $(element);
	let $contents = $element.find('.ps-js-activity-content, .ps-js-comment-content');

	$contents.each(function () {
		let $content = $(this);

		if (util.isRTL($content.text().trim())) {
			$content.addClass('ps-text-rtl');
		} else {
			$content.removeClass('ps-text-rtl');
		}
	});

	// TODO: Handle comment box.
	$element.on('input', 'textarea.ps-comments__input', function () {
		adjustPostbox(this);
	});
}

function adjustPostbox(textarea) {
	let $textarea = $(textarea);
	let $beautifier = $textarea.prev('.ps-postbox__input-beautifier');

	if (util.isRTL($textarea.val().trim())) {
		$textarea.add($beautifier).addClass('ps-text-rtl');
	} else {
		$textarea.add($beautifier).removeClass('ps-text-rtl');
	}
}

function init() {
	// Scan added activities.
	observer.addFilter(
		'peepso_activity',
		$posts =>
			$posts.map(function () {
				if (this.nodeType === 1) {
					scanElement(this);
				}
				return this;
			}),
		10,
		1
	);

	// Scan updated activities.
	hooks.addAction('post_updated', 'auto-rtl', scanElement);

	// Listen to postbox content changes.
	observer.addFilter(
		'peepso_postbox_input_changed',
		(value, postbox) => {
			adjustPostbox(postbox.$textarea);
			return value;
		},
		10,
		2
	);

	// Listen to input changes on the Edit's postbox.
	observer.addAction('postbox_update', postbox => {
		postbox.$text.off('input.auto-rtl').on('input.auto-rtl', () => {
			adjustPostbox(postbox.$text);
		});

		setTimeout(() => postbox.$text.triggerHandler('input.auto-rtl'), 100);
	});
}

export default { init };
