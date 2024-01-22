import $ from 'jquery';
import { observer, hooks, modules } from 'peepso';
import { elements as elementsData } from 'peepsodata';

let postData = (elementsData && elementsData.post) || {};

const TEXT_SAVE = postData.text_save;
const TEXT_SAVED = postData.text_saved;
const HTML_SAVED_NOTICE = postData.html_saved_notice;

let $tooltip = null;

/**
 * Disable button.
 *
 * @param {Element} button
 */
function disable(button) {
	let label = button.querySelector('span');

	button.removeEventListener('click', toggleHandler);
	$(button).addClass('ps-loading-pulse').css('cursor', 'not-allowed');
	label.style.display = 'none';
}

/**
 * Enable button.
 *
 * @param {Element} button
 * @param {Object} data
 */
function enable(button, data = {}) {
	let label = button.querySelector('span');

	button.addEventListener('click', toggleHandler);
	$(button).removeClass('ps-loading-pulse').css('cursor', '');
	label.style.display = '';

	if (+data.saved) {
		button.setAttribute('data-object-id', data.id);
	}
}

function toggle(button, saved) {
	let icon = button.querySelector('i'),
		label = button.querySelector('span');

	if (saved) {
		button.setAttribute('data-saved', '1');
		icon.className = 'gcis gci-bookmark';
		label.innerHTML = TEXT_SAVED;
	} else {
		button.removeAttribute('data-saved');
		icon.className = 'gcir gci-bookmark';
		label.innerHTML = TEXT_SAVE;
	}
}

function toggleHandler(e) {
	let button = e.currentTarget,
		saved = +button.getAttribute('data-saved'),
		id = +button.getAttribute(saved ? 'data-object-id' : 'data-stream-id');

	e.preventDefault();
	e.stopPropagation();

	disable(button);
	toggle(button, !saved);

	// Update state.
	modules.post.save(id, !saved).then(function (json) {
		let notice = false;

		enable(button, json);

		// Display a message in a popup once a post is saved.
		if (!!json.saved) {
			notice = observer.applyFilters('post_saved_notice', true);
		}

		if (notice) {
			let offset = $(button).offset(),
				height = $(button).height();

			if (!$tooltip) {
				$tooltip = $('<div/>').html(HTML_SAVED_NOTICE);
				$tooltip.css({ position: 'absolute' });
				$tooltip.appendTo(document.body);
			}

			$tooltip.stop().show();
			$tooltip.css({
				top: Math.round(offset.top - $tooltip.height() / 2 + height / 2),
				left: Math.round(offset.left - $tooltip.width() - 10)
			});

			// Fade out after 2 seconds.
			$tooltip.delay(2000).fadeOut();
		} else {
			$tooltip && $tooltip.stop().hide();
		}

		hooks.doAction('post_saved', +button.getAttribute('data-stream-id'), !!json.saved);
	});
}

function initActions(actions) {
	let button = actions.querySelector('.ps-js-save-toggle');
	if (!button) {
		return;
	}

	// Start with disabled button.
	disable(button);

	let id = +button.getAttribute('data-stream-id');
	if (!id) {
		return;
	}

	// Check initial state.
	modules.post.save(id).then(function (json) {
		// Enable the button when done.
		enable(button, json);
		toggle(button, !!json.saved);
	});
}

function initPost(postElement) {
	let actions = postElement.querySelector('.js-stream-actions');
	if (!actions) {
		return;
	}

	let button = actions.querySelector('.ps-js-save-toggle');
	if (!button) {
		return;
	}

	// Remove the button if its not in the post view.
	if (button.closest && !button.closest('.ps-js-activity')) {
		button.remove();
		return;
	}

	// Initialize action buttons.
	initActions(actions);
}

function init() {
	// Initialize on each activity item added.
	observer.addFilter(
		'peepso_activity',
		$posts =>
			$posts.map(function () {
				if (this.nodeType === 1) {
					initPost(this);
				}
				return this;
			}),
		10,
		1
	);

	// Initialize activity actions.
	observer.addAction(
		'peepso_activity_actions',
		$actions => {
			$actions.map(function () {
				if (this.nodeType === 1) {
					initActions(this);
				}
				return this;
			});
		},
		10,
		1
	);
}

export default { init };
