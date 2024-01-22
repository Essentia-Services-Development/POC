import $ from 'jquery';
import { dialog, hooks } from 'peepso';
import { friends as friendsData, profile as profileData } from 'peepsodata';
import friends from './friends';

const PROFILE_ID = profileData && +profileData.id;
const TEMPLATE_REMOVE_FRIEND = friendsData && friendsData.template_remove_friend;

/**
 * Handle profile buttons.
 *
 * NOTE: Events need to be attached to the header element since the buttons can be dynamically added/removed
 * by AJAX actions.
 */
$(function () {
	let $header = $('.ps-js-focus--profile');
	if (!$header.length) {
		return;
	}

	// Handle send friend request.
	$header.on('click', '.ps-js-friend-send-request', function (e) {
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
	$header.on('click', '.ps-js-friend-cancel-request', function (e) {
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
	$header.on('click', '.ps-js-friend-accept-request', function (e) {
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
	$header.on('click', '.ps-js-friend-reject-request', function (e) {
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
	$header.on('click', '.ps-js-friend-remove', function (e) {
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
	$header.on('mouseenter', '.ps-js-friend-remove', function (e) {
		let $btn = $(e.currentTarget),
			$label = $btn.find('span').last();

		$label.data('html', $label.html());
		$label.html($btn.data('text-unfriend'));
	});
	$header.on('mouseleave', '.ps-js-friend-remove', function (e) {
		let $btn = $(e.currentTarget),
			$label = $btn.find('span').last();

		$label.html($label.data('html'));
	});

	// Handle update action buttons on profile page.
	hooks.addAction('friend_request_sent', 'page_profile', updateActions);
	hooks.addAction('friend_request_canceled', 'page_profile', updateActions);
	hooks.addAction('friend_request_accepted', 'page_profile', updateActions);
	hooks.addAction('friend_request_rejected', 'page_profile', updateActions);
	hooks.addAction('friend_removed', 'page_profile', updateActions);

	function updateActions(userId, data = {}) {
		if (+userId !== PROFILE_ID) {
			return;
		}

		let actions = data.actions;

		if ('string' === typeof actions) {
			$header.find('.ps-js-profile-actions-extra').html(actions);
		} else if ('object' === typeof actions) {
			if (actions.primary_profile) {
				$header.find('.ps-js-focus-actions').html(actions.primary_profile);
			}
			if (actions.secondary_profile) {
				$header.find('.ps-js-profile-actions-extra').html(actions.secondary_profile);
			}
		}
	}
});
