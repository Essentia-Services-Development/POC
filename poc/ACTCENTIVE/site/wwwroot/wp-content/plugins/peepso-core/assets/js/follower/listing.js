import $ from 'jquery';
import { hooks } from 'peepso';
import { follow, unfollow } from './follower';

const $container = $('.ps-js-members, .ps-js-friends, .ps-js-friend-requests');
const btnSelector = '.ps-js-friend-follow, .ps-js-friend-unfollow';

// Handle follow/unfollow button.
$container.on('click', btnSelector, function (e) {
	let $btn = $(e.currentTarget),
		$loading = $btn.find('img'),
		userId = $btn.data('user-id'),
		following = !$btn.hasClass('ps-js-friend-follow');

	e.preventDefault();
	e.stopPropagation();

	if (!following) {
		$loading.show();
		follow(userId).then(function () {
			$loading.hide();
		});
	} else {
		$loading.show();
		unfollow(userId).then(function () {
			$loading.hide();
		});
	}
});

$container.on('mouseenter', btnSelector, function (e) {
	let $btn = $(e.currentTarget),
		$label = $btn.find('span').last();

	$label.data('html', $label.html());
	$label.html($btn.data('text-hover'));
});

$container.on('mouseleave', btnSelector, function (e) {
	let $btn = $(e.currentTarget),
		$label = $btn.find('span').last();

	$label.html($label.data('html'));
});

hooks.addAction('user_followed', 'user_listing', updateActions);
hooks.addAction('user_unfollowed', 'user_listing', updateActions);

function updateActions(userId, data = {}) {
	let $item = $container.find('.ps-js-member').filter(`[data-user-id="${userId}"]`),
		actions = data.actions;

	if ($item.length) {
		// Remove item if it is currently on the friend request page.
		if ($container.hasClass('ps-js-friend-requests')) {
			$item.fadeOut().remove();
			return;
		}

		if ('string' === typeof actions) {
			$item.find('.ps-js-member-actions-extra').html(data.actions);
		} else if ('object' === typeof actions) {
			if (actions.primary) {
				$item.find('.ps-js-member-actions').replaceWith(actions.primary);
			}
			if (actions.secondary) {
				$item.find('.ps-js-member-actions-extra').replaceWith(actions.secondary);
			}
			if (actions.dropdown) {
				$item.find('.ps-js-dropdown').replaceWith(actions.dropdown);
			}
		}
	}
}
