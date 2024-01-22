import $ from 'jquery';
import { observer, hooks, modules } from 'peepso';
import { elements as elementsData } from 'peepsodata';

let postData = (elementsData && elementsData.post) || {};

const TEXT_SUBSCRIBE = postData.text_subscribe;
const TEXT_UNSUBSCRIBE = postData.text_unsubscribe;
const HTML_SUBSCRIBED_NOTICE = postData.html_subscribed_notice;

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
}

function toggle(button, follow) {
	let icon = button.querySelector('i'),
		label = button.querySelector('span');

	if (follow) {
		button.setAttribute('data-follow', 1);
		icon.className = 'gcis gci-square-check';
		label.innerHTML = TEXT_UNSUBSCRIBE;
	} else {
		button.setAttribute('data-follow', 0);
		icon.className = 'gcir gci-square-check';
		label.innerHTML = TEXT_SUBSCRIBE;
	}
}

function reload(button) {
	let id = +button.getAttribute('data-post-id');

	disable(button);
	modules.post.follow(id).then(function (json) {
		enable(button, json);
		toggle(button, !!json.follow);
	});
}

function toggleHandler(e) {
	let button = e.currentTarget,
		follow = +button.getAttribute('data-follow'),
		id = +button.getAttribute('data-post-id');

	e.preventDefault();
	e.stopPropagation();

	disable(button);
	toggle(button, !follow);

	// Update state.
	let state = !follow ? 1 : 0;
	modules.post.follow(id, state).then(function (json) {
		let notice = false;

		enable(button, json);

		// Display a message in a popup once a post is follow.
		if (json.follow == 1) {
			notice = observer.applyFilters('post_subscribe_notice', true);
		}

		if (notice) {
			let offset = $(button).offset(),
				height = $(button).height();

			if (!$tooltip) {
				$tooltip = $('<div/>').html(HTML_SUBSCRIBED_NOTICE);
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
	});
}

function initActions(actions) {
	let button = actions.querySelector('.ps-js-follow-toggle');
	if (!button) {
		return;
	}

	// Start with disabled button.
	disable(button);

	let id = +button.getAttribute('data-post-id');
	if (!id) {
		return;
	}

	// Check initial state.
	modules.post.follow(id).then(function (json) {
		// Enable the button when done.
		enable(button, json);
		toggle(button, !!json.follow);
	});
}

function initPost(postElement) {
	let actions = postElement.querySelector('.js-stream-actions');
	if (!actions) {
		return;
	}

	let button = actions.querySelector('.ps-js-follow-toggle');
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

	// Initialize activity actions.
	hooks.addAction('comment_added', 'post_follow', comment => {
		let postElement = comment.closest('.ps-js-activity');
		if (postElement) {
			initPost(postElement);
		}
	});

	function reloadButtonState(id) {
		let button = document.querySelector(`.ps-js-follow-toggle[data-stream-id="${id}"]`);
		if (button) {
			reload(button);
		}
	}

	// Refresh following state when post is saved/unsaved.
	hooks.addAction('post_saved', 'post_follow', reloadButtonState);
	hooks.addAction('reaction_added', 'post_follow', reloadButtonState);
	hooks.addAction('reaction_deleted', 'post_follow', reloadButtonState);
	hooks.addAction('comment_added', 'post_follow', el => {
		let postElement = el.closest('.ps-js-activity');
		if (postElement) {
			reloadButtonState(postElement.getAttribute('data-post-id'));
		}
	});
}

export default { init };
