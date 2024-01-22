import $ from 'jquery';
import { dialog, hooks } from 'peepso';
import { friends as friendsData } from 'peepsodata';
import friends from './friends';

const TEMPLATE_REMOVE_FRIEND = friendsData && friendsData.template_remove_friend;

/**
 * Handle profile buttons.
 *
 * NOTE: Events need to be attached to the container element since the buttons can be dynamically added/removed
 * by AJAX actions.
 */
$(function () {
	let $container = $('.ps-js-members');

	// @todo: Friend page should be handled in the separate file.
	$container = $container.add($('.ps-js-friends, .ps-js-friend-requests'));

	if (!$container.length) {
		return;
	}

	// Handle send friend request.
	$container.on('click', '.ps-js-friend-send-request', function (e) {
		let $btn = $(e.currentTarget),
			$loading = $btn.find('img'),
			userId = $btn.data('user-id');

		e.preventDefault();
		e.stopPropagation();

		$loading.show();
		friends.sendRequest(userId).then(function () {
			$loading.hide();
		});
	});

	// Handle cancel friend request.
	$container.on('click', '.ps-js-friend-cancel-request', function (e) {
		let $btn = $(e.currentTarget),
			$loading = $btn.find('img'),
			userId = $btn.data('user-id'),
			requestId = $btn.data('request-id');

		e.preventDefault();
		e.stopPropagation();

		$loading.show();
		friends.cancelRequest(userId, requestId).then(function () {
			$loading.hide();
		});
	});

	// Handle accept friend request.
	$container.on('click', '.ps-js-friend-accept-request', function (e) {
		let $btn = $(e.currentTarget),
			$loading = $btn.find('img'),
			userId = $btn.data('user-id'),
			requestId = $btn.data('request-id');

		e.preventDefault();
		e.stopPropagation();

		$loading.show();
		friends.acceptRequest(userId, requestId).then(function () {
			$loading.hide();
		});
	});

	// Handle reject friend request.
	$container.on('click', '.ps-js-friend-reject-request', function (e) {
		let $btn = $(e.currentTarget),
			$loading = $btn.find('img'),
			userId = $btn.data('user-id'),
			requestId = $btn.data('request-id');

		e.preventDefault();
		e.stopPropagation();

		$loading.show();
		friends.rejectRequest(userId, requestId).then(function () {
			$loading.hide();
		});
	});

	// Handle remove friend button.
	$container.on('click', '.ps-js-friend-remove', function (e) {
		let popup = dialog(TEMPLATE_REMOVE_FRIEND).show(),
			$btn = $(e.currentTarget),
			$loading = $btn.find('img'),
			userId = $btn.data('user-id');

		e.preventDefault();
		e.stopPropagation();

		popup.$el.on('click', '.ps-js-cancel', () => popup.hide());
		popup.$el.on('click', '.ps-js-submit', () => {
			popup.hide();
			$loading.show();
			friends.remove(userId).then(function () {
				$loading.hide();
			});
		});
	});
	$container.on('mouseenter', '.ps-js-friend-remove', function (e) {
		let $btn = $(e.currentTarget),
			$label = $btn.find('span').last();

		$label.data('html', $label.html());
		$label.html($btn.data('text-unfriend'));
	});
	$container.on('mouseleave', '.ps-js-friend-remove', function (e) {
		let $btn = $(e.currentTarget),
			$label = $btn.find('span').last();

		$label.html($label.data('html'));
	});

	// Handle update action buttons on profile page.
	hooks.addAction('friend_request_sent', 'page_members', updateActions);
	hooks.addAction('friend_request_canceled', 'page_members', updateActions);
	hooks.addAction('friend_request_accepted', 'page_members', updateActions);
	hooks.addAction('friend_request_rejected', 'page_members', updateActions);
	hooks.addAction('friend_removed', 'page_members', updateActions);

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
});
