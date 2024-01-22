import $ from 'jquery';
import { hooks } from 'peepso';
import { friends as friendsData } from 'peepsodata';
import friends from './friends';

const FRIEND_REQUESTS_PAGE = friendsData && friendsData.friend_requests_page;
const TEMPLATE_NOTIFICATION_HEADER = friendsData && friendsData.template_notification_header;

$(function() {
	let $container = $('.ps-js-friends-notification'),
		$counter = $container.find('.ps-js-counter');

	// Initialize the friend notification popover
	if ($container.psnotification) {
		$container.psnotification({
			source: 'friendsajax.get_requests',
			view_all_link: FRIEND_REQUESTS_PAGE,
			header: TEMPLATE_NOTIFICATION_HEADER
		});
	}


	// Handle accept friend request.
	$container.on('click', '.ps-js-friend-accept-request', e => {
		let $btn = $(e.currentTarget),
			userId = $btn.data('user-id'),
			requestId = $btn.data('request-id');

		friends.acceptRequest(userId, requestId);
	});

	// Handle reject friend request.
	$container.on('click', '.ps-js-friend-reject-request', e => {
		let $btn = $(e.currentTarget),
			userId = $btn.data('user-id'),
			requestId = $btn.data('request-id');

		friends.rejectRequest(userId, requestId);
	});

	// Handle update action buttons on the notification popover.
	hooks.addAction('friend_request_accepted', 'friend_notification', updateActions);
	hooks.addAction('friend_request_rejected', 'friend_notification', updateActions);

	function updateActions(userId) {
		let count = $counter
			.eq(0)
			.html()
			.trim();

		// Manually decrease counter value.
		$counter.html(count - 1);
		if (count - 1 > 0) {
			$counter.show();
		} else {
			$counter.hide();
		}

		// Hide notification item.
		let $item = $container
			.find('.ps-js-notification')
			.filter(`[data-user-id="${userId}"]`)
			.parent();

		if ($item.length) {
			$item.fadeOut();
		}
	}
});
