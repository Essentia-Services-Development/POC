import $ from 'jquery';
import { debounce } from 'underscore';
import { observer, modules, util } from 'peepso';
import { currentuserid as LOGIN_USER_ID } from 'peepsodata';

let watchList = [];

let watcher = debounce(function () {
	if (!watchList.length) {
		return;
	}

	for (let i = watchList.length - 1; i >= 0; i--) {
		let post = watchList[i];

		// Track unique views when element is partly visible.
		if (util.isElementPartlyInViewport(post)) {
			let $post = $(post).closest('.ps-js-activity');
			if ($post.length) {
				let actId = $post.data('id');
				modules.post.trackView(actId);
			}

			// Remove tracked element from watchlist.
			watchList.splice(i, 1);
		}
	}
}, 1000);

function initPost(postElement) {
	watchList.push(postElement);
	watcher();
}

function init() {
	// Only proceed for logged-in users.
	if (!+LOGIN_USER_ID) {
		return;
	}

	$(window).on('scroll', watcher);

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
}

export default { init };
